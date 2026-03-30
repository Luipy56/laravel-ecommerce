<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Product catalog search (PostgreSQL + fallbacks)
    |--------------------------------------------------------------------------
    |
    | Query strings are normalized the same way as Product::search_text: trim,
    | whitespace collapse, optional intl diacritic folding, lowercase (see
    | Product::normalizeSearchText). PostgreSQL uses ILIKE plus pg_trgm
    | word_similarity/similarity with GIN on products.search_text when the
    | driver is pgsql. Other drivers use token-wise LIKE (no typo tolerance).
    |
    */

    'limit' => max(1, min(200, (int) env('PRODUCT_SEARCH_LIMIT', 50))),

    /*
    |--------------------------------------------------------------------------
    | HTTP catalog search API (`GET /api/v1/products/search`)
    |--------------------------------------------------------------------------
    */

    'api_min_query_length' => max(1, min(10, (int) env('CATALOG_SEARCH_API_MIN_LENGTH', 2))),

    'api_limit' => max(1, min(100, (int) env('CATALOG_SEARCH_API_LIMIT', 20))),

    'word_similarity_threshold' => (float) env('PRODUCT_SEARCH_WORD_SIMILARITY', 0.35),

    'similarity_threshold' => (float) env('PRODUCT_SEARCH_SIMILARITY', 0.12),
];
