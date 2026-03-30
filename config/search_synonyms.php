<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Catalog search synonyms (PostgreSQL + Elasticsearch)
    |--------------------------------------------------------------------------
    |
    | Single source of truth for equivalent terms. Each inner list is one
    | synonym set: every term matches every other term in that set.
    |
    | Terms are normalized the same way as product search_text (lowercase,
    | whitespace collapse, optional intl diacritic folding) when the dictionary
    | is built. Prefer plain ASCII or already-folded words in config.
    |
    | After changing groups, rebuild the Elasticsearch index (mappings/settings)
    | and re-import products. See docs/elasticsearch.md.
    |
    */

    'enabled' => filter_var(env('SEARCH_SYNONYMS_ENABLED', true), FILTER_VALIDATE_BOOL),

    'max_expansions_per_token' => max(1, min(30, (int) env('SEARCH_SYNONYMS_MAX_EXPANSIONS', 10))),

    'groups' => [
        // Example (uncomment and adjust for your catalog):
        // ['notebook', 'laptop'],
    ],
];
