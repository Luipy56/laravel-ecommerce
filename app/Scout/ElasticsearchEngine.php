<?php

declare(strict_types=1);

namespace App\Scout;

use Elastic\Elasticsearch\Client;
use Elastic\Elasticsearch\Exception\ClientResponseException;
use Elastic\Elasticsearch\Exception\ServerResponseException;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection as BaseCollection;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\LazyCollection;
use Laravel\Scout\Builder;
use Laravel\Scout\Engines\Engine;
use Laravel\Scout\Jobs\RemoveableScoutCollection;

final class ElasticsearchEngine extends Engine
{
    public function __construct(
        private readonly Client $client,
        private readonly bool $softDelete = false,
    ) {}

    public function update($models): void
    {
        if ($models->isEmpty()) {
            return;
        }

        /** @var Model $first */
        $first = $models->first();

        if ($this->usesSoftDelete($first) && $this->softDelete) {
            $models->each->pushSoftDeleteMetadata();
        }

        $index = $first->indexableAs();
        $keyName = $first->getScoutKeyName();

        $lines = [];
        foreach ($models as $model) {
            if (empty($searchable = $model->toSearchableArray())) {
                continue;
            }

            $payload = array_merge(
                $searchable,
                $model->scoutMetadata(),
                [$keyName => $model->getScoutKey()],
            );

            $id = (string) $model->getScoutKey();
            $lines[] = ['index' => ['_index' => $index, '_id' => $id]];
            $lines[] = $payload;
        }

        if ($lines === []) {
            return;
        }

        try {
            $this->client->bulk([
                'body' => $lines,
                'refresh' => (bool) config('scout.elasticsearch.refresh_on_write', false),
            ]);
        } catch (ClientResponseException|ServerResponseException $e) {
            Log::error('elasticsearch.scout.update_failed', [
                'index' => $index,
                'message' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    public function delete($models): void
    {
        if ($models->isEmpty()) {
            return;
        }

        $index = $models->first()->indexableAs();

        $keys = $models instanceof RemoveableScoutCollection
            ? $models->pluck($models->first()->getScoutKeyName())
            : $models->map->getScoutKey();

        foreach ($keys as $id) {
            try {
                $this->client->delete([
                    'index' => $index,
                    'id' => (string) $id,
                    'refresh' => (bool) config('scout.elasticsearch.refresh_on_write', false),
                ]);
            } catch (ClientResponseException $e) {
                if ($e->getCode() === 404) {
                    continue;
                }
                throw $e;
            }
        }
    }

    public function search(Builder $builder): mixed
    {
        return $this->performSearch($builder, max(1, (int) $builder->limit));
    }

    public function paginate(Builder $builder, $perPage, $page): mixed
    {
        $perPage = max(1, (int) $perPage);
        $page = max(1, (int) $page);

        return $this->performSearch($builder, $perPage, ($page - 1) * $perPage);
    }

    /**
     * @return array{hits: list<array<string, mixed>>, total: int}
     */
    private function performSearch(Builder $builder, int $size, int $from = 0): array
    {
        $index = $builder->index ?: $builder->model->searchableAs();

        $body = [
            'from' => $from,
            'size' => $size,
            'query' => $this->queryClause($builder),
        ];

        if ($builder->callback !== null) {
            $raw = call_user_func($builder->callback, $this->client, $builder->query, [
                'index' => $index,
                'body' => $body,
            ]);

            return is_array($raw) ? $raw : $this->normalizeSearchResponse($raw->asArray());
        }

        $response = $this->client->search([
            'index' => $index,
            'body' => array_merge($body, $builder->options),
        ]);

        return $this->normalizeSearchResponse($response->asArray());
    }

    /**
     * @param  array<string, mixed>  $raw
     * @return array{hits: list<array<string, mixed>>, total: int}
     */
    private function normalizeSearchResponse(array $raw): array
    {
        $hitsOut = [];
        foreach ($raw['hits']['hits'] ?? [] as $hit) {
            $source = $hit['_source'] ?? [];
            if (! is_array($source)) {
                $source = [];
            }
            $hitsOut[] = $source;
        }

        $total = $raw['hits']['total'] ?? 0;
        $totalValue = is_array($total) ? (int) ($total['value'] ?? 0) : (int) $total;

        return [
            'hits' => $hitsOut,
            'total' => $totalValue,
        ];
    }

    private function queryClause(Builder $builder): array
    {
        $must = [];
        $filter = [];

        $q = trim((string) $builder->query);
        if ($q !== '') {
            $must[] = [
                'multi_match' => [
                    'query' => $q,
                    'type' => 'best_fields',
                    'fields' => [
                        'name^2',
                        'code^2',
                        'search_text',
                        'description',
                    ],
                ],
            ];
        } else {
            $must[] = ['match_all' => new \stdClass];
        }

        foreach ($builder->wheres as $where) {
            if (($where['field'] ?? '') === '__soft_deleted') {
                continue;
            }
            $field = $where['field'];
            $value = $where['value'];
            $filter[] = ['term' => [$field => $value]];
        }

        foreach ($builder->whereIns as $field => $values) {
            $filter[] = ['terms' => [$field => array_values($values)]];
        }

        foreach ($builder->whereNotIns as $field => $values) {
            $filter[] = ['bool' => ['must_not' => [['terms' => [$field => array_values($values)]]]]];
        }

        $bool = ['must' => $must];
        if ($filter !== []) {
            $bool['filter'] = $filter;
        }

        return ['bool' => $bool];
    }

    public function mapIds($results): BaseCollection
    {
        if (! is_array($results) || ($results['hits'] ?? []) === []) {
            return collect();
        }

        $hits = collect($results['hits']);
        $first = $hits->first();
        if (! is_array($first) || $first === []) {
            return collect();
        }

        $key = key($first);

        return $hits->pluck($key)->values();
    }

    public function map(Builder $builder, $results, $model): EloquentCollection
    {
        /** @var Model $model */
        if (! is_array($results) || ($results['hits'] ?? []) === []) {
            return $model->newCollection();
        }

        $objectIds = collect($results['hits'])->pluck($model->getScoutKeyName())->values()->all();
        $positions = array_flip($objectIds);

        return $model->getScoutModelsByIds($builder, $objectIds)
            ->filter(fn (Model $m) => in_array($m->getScoutKey(), $objectIds, false))
            ->map(function (Model $m) use ($results, $positions) {
                $row = $results['hits'][$positions[$m->getScoutKey()]] ?? [];
                foreach ($row as $key => $value) {
                    if (is_string($key) && str_starts_with($key, '_')) {
                        $m->withScoutMetadata($key, $value);
                    }
                }

                return $m;
            })
            ->sortBy(fn (Model $m) => $positions[$m->getScoutKey()])
            ->values();
    }

    public function lazyMap(Builder $builder, $results, $model): LazyCollection
    {
        /** @var Model $model */
        if (! is_array($results) || ($results['hits'] ?? []) === []) {
            return LazyCollection::make($model->newCollection());
        }

        $objectIds = collect($results['hits'])->pluck($model->getScoutKeyName())->values()->all();
        $positions = array_flip($objectIds);

        return $model->queryScoutModelsByIds($builder, $objectIds)
            ->cursor()
            ->filter(fn (Model $m) => in_array($m->getScoutKey(), $objectIds, false))
            ->map(function (Model $m) use ($results, $positions) {
                $row = $results['hits'][$positions[$m->getScoutKey()]] ?? [];
                foreach ($row as $key => $value) {
                    if (is_string($key) && str_starts_with($key, '_')) {
                        $m->withScoutMetadata($key, $value);
                    }
                }

                return $m;
            });
    }

    public function getTotalCount($results): int
    {
        if (! is_array($results)) {
            return 0;
        }

        return (int) ($results['total'] ?? 0);
    }

    public function flush($model): void
    {
        $index = $model->indexableAs();

        try {
            $this->client->deleteByQuery([
                'index' => $index,
                'body' => [
                    'query' => ['match_all' => new \stdClass],
                ],
                'refresh' => (bool) config('scout.elasticsearch.refresh_on_write', false),
                'conflicts' => 'proceed',
            ]);
        } catch (ClientResponseException $e) {
            if ($e->getCode() === 404) {
                return;
            }
            throw $e;
        }
    }

    public function createIndex($name, array $options = []): void
    {
        $suffix = $this->indexTableSuffix((string) $name);
        $definition = config('scout.elasticsearch.index_definitions.'.$suffix);

        $body = [];
        if (is_array($definition)) {
            $body = $definition;
        }

        $params = ['index' => $name];
        if ($body !== []) {
            $params['body'] = $body;
        }

        try {
            $this->client->indices()->create($params);
        } catch (ClientResponseException $e) {
            if ($e->getCode() === 400 && str_contains($e->getMessage(), 'resource_already_exists_exception')) {
                return;
            }
            throw $e;
        }
    }

    public function deleteIndex($name): void
    {
        try {
            $this->client->indices()->delete(['index' => $name]);
        } catch (ClientResponseException $e) {
            if ($e->getCode() === 404) {
                return;
            }
            throw $e;
        }
    }

    private function indexTableSuffix(string $fullIndexName): string
    {
        $prefix = (string) config('scout.prefix');
        if ($prefix !== '' && str_starts_with($fullIndexName, $prefix)) {
            return substr($fullIndexName, strlen($prefix));
        }

        return $fullIndexName;
    }

    private function usesSoftDelete(Model $model): bool
    {
        return in_array(SoftDeletes::class, class_uses_recursive($model), true);
    }
}
