<?php

declare(strict_types=1);

namespace App\Services\Search;

use App\Contracts\Search\ElasticsearchProductCatalogSearch;
use App\Models\Product;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Orchestrates catalog search: Elasticsearch when configured, otherwise the SQL {@see ProductSearchService}.
 */
final class CatalogProductSearchService
{
    public function __construct(
        private readonly ProductSearchService $databaseSearch,
        private readonly ElasticsearchProductCatalogSearch $elasticsearch,
    ) {}

    /**
     * @return array{
     *     products: EloquentCollection<int, Product>,
     *     suggestions: list<array{text: string, product_id: int}>,
     *     engine: string
     * }
     */
    public function search(string $rawQuery, bool $suggest, int $limit): array
    {
        $normalized = $this->databaseSearch->normalizeQuery($rawQuery);
        $minLen = max(1, (int) config('product_search.api_min_query_length', 2));
        if (mb_strlen($normalized, 'UTF-8') < $minLen) {
            return [
                'products' => (new Product)->newCollection([]),
                'suggestions' => [],
                'engine' => 'none',
            ];
        }

        $limit = max(1, $limit);

        if ($suggest) {
            return $this->searchSuggest($normalized, $limit);
        }

        return $this->searchFullText($normalized, $limit);
    }

    private function useElasticsearch(): bool
    {
        if (config('scout.driver') !== 'elasticsearch') {
            return false;
        }

        $hosts = config('scout.elasticsearch.hosts', []);

        return is_array($hosts) && $hosts !== [] && trim((string) ($hosts[0] ?? '')) !== '';
    }

    /**
     * @return array{products: EloquentCollection<int, Product>, suggestions: list<array{text: string, product_id: int}>, engine: string}
     */
    private function searchFullText(string $normalized, int $limit): array
    {
        if ($this->useElasticsearch()) {
            $fromEs = $this->elasticsearch->search($normalized, $limit);
            if ($fromEs !== null) {
                $items = $fromEs->all();

                return [
                    'products' => (new Product)->newCollection($items),
                    'suggestions' => [],
                    'engine' => 'elasticsearch',
                ];
            }

            Log::info('catalog_search.fallback_to_database', [
                'mode' => 'full_text',
                'reason' => 'elasticsearch_unavailable',
                'db_driver' => config('database.default'),
            ]);
        }

        $slice = $this->databaseSearch->search($normalized)->take($limit)->values()->all();

        return [
            'products' => (new Product)->newCollection($slice),
            'suggestions' => [],
            'engine' => 'database',
        ];
    }

    /**
     * @return array{products: EloquentCollection<int, Product>, suggestions: list<array{text: string, product_id: int}>, engine: string}
     */
    private function searchSuggest(string $normalized, int $limit): array
    {
        if ($this->useElasticsearch()) {
            $fromEs = $this->elasticsearch->suggest($normalized, $limit);
            if ($fromEs !== null) {
                return [
                    'products' => (new Product)->newCollection([]),
                    'suggestions' => $fromEs,
                    'engine' => 'elasticsearch',
                ];
            }

            Log::info('catalog_search.fallback_to_database', [
                'mode' => 'suggest',
                'reason' => 'elasticsearch_unavailable',
                'db_driver' => config('database.default'),
            ]);
        }

        $products = $this->databaseSearch->search($normalized)->take($limit)->values();
        $suggestions = [];
        foreach ($products as $product) {
            $text = trim((string) $product->name);
            if ($text === '') {
                $text = trim((string) $product->code);
            }
            if ($text === '') {
                continue;
            }
            $suggestions[] = ['text' => $text, 'product_id' => (int) $product->id];
        }

        return [
            'products' => (new Product)->newCollection([]),
            'suggestions' => $suggestions,
            'engine' => 'database',
        ];
    }
}
