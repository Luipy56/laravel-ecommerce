<?php

declare(strict_types=1);

namespace App\Services\Search;

use App\Contracts\Search\ElasticsearchProductCatalogSearch;
use App\Models\Product;
use App\Scout\ElasticsearchClientFactory;
use Elastic\Elasticsearch\Client;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use Throwable;

final class ScoutElasticsearchProductCatalogSearch implements ElasticsearchProductCatalogSearch
{
    private readonly Client $client;

    public function __construct(?Client $client = null)
    {
        $this->client = $client ?? ElasticsearchClientFactory::make(config('scout.elasticsearch', []));
    }

    public function search(string $normalizedQuery, int $limit): ?Collection
    {
        try {
            /** @var Collection<int, Product> $collection */
            $collection = Product::search($normalizedQuery)
                ->where('is_active', true)
                ->take(max(1, $limit))
                ->get();

            return $collection;
        } catch (Throwable $e) {
            Log::warning('catalog_search.elasticsearch_search_failed', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function suggest(string $normalizedPrefix, int $limit): ?array
    {
        if ($normalizedPrefix === '') {
            return [];
        }

        try {
            $index = (new Product)->searchableAs();
            $response = $this->client->search([
                'index' => $index,
                'body' => [
                    'size' => max(1, $limit),
                    'query' => [
                        'bool' => [
                            'must' => [
                                [
                                    'match_bool_prefix' => [
                                        'search_text' => [
                                            'query' => $normalizedPrefix,
                                            'max_expansions' => 50,
                                        ],
                                    ],
                                ],
                            ],
                            'filter' => [
                                ['term' => ['is_active' => true]],
                            ],
                        ],
                    ],
                    '_source' => ['id', 'name', 'code'],
                    'sort' => [
                        ['_score' => 'desc'],
                        ['id' => 'asc'],
                    ],
                ],
            ]);

            $raw = $response->asArray();
            $hits = $raw['hits']['hits'] ?? [];
            if (! is_array($hits)) {
                return [];
            }

            $out = [];
            $seen = [];
            foreach ($hits as $hit) {
                if (! is_array($hit)) {
                    continue;
                }
                $src = $hit['_source'] ?? [];
                if (! is_array($src)) {
                    continue;
                }
                $id = isset($src['id']) ? (int) $src['id'] : 0;
                if ($id <= 0 || isset($seen[$id])) {
                    continue;
                }
                $seen[$id] = true;
                $text = isset($src['name']) && is_string($src['name']) ? trim($src['name']) : '';
                if ($text === '' && isset($src['code']) && is_string($src['code'])) {
                    $text = trim($src['code']);
                }
                if ($text === '') {
                    continue;
                }
                $out[] = ['text' => $text, 'product_id' => $id];
                if (count($out) >= $limit) {
                    break;
                }
            }

            return $out;
        } catch (Throwable $e) {
            Log::warning('catalog_search.elasticsearch_suggest_failed', [
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
