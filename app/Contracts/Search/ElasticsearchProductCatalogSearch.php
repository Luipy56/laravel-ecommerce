<?php

declare(strict_types=1);

namespace App\Contracts\Search;

use Illuminate\Support\Collection;

/**
 * Elasticsearch-backed catalog search for products. Implementations return null on failure so
 * callers can fall back to {@see \App\Services\Search\ProductSearchService}.
 */
interface ElasticsearchProductCatalogSearch
{
    /**
     * @return Collection<int, \App\Models\Product>|null
     */
    public function search(string $normalizedQuery, int $limit): ?Collection;

    /**
     * Lightweight autocomplete hits (product id + display text).
     *
     * @return list<array{text: string, product_id: int}>|null
     */
    public function suggest(string $normalizedPrefix, int $limit): ?array;
}
