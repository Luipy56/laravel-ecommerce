<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Search / indexing locale strategy (extension only)
    |--------------------------------------------------------------------------
    |
    | Storefront UI locales are ca, es, and en (see .cursor/rules/i18n.mdc). Product
    | data today uses a single name/description; search_text merges them into
    | one normalized blob (see Product::normalizeSearchText).
    |
    | When the schema evolves, localized search can follow one of:
    | - per-column fields (e.g. name_ca, name_es) folded into search_text, or
    | - a JSON map of locale => string merged at index time.
    |
    | Do not add DB columns here; follow project-standards (edit existing
    | product migration + trash/diagramZero.dbml if columns are introduced).
    |
    */

    'product_label_strategy' => env('SEARCH_LOCALE_PRODUCT_STRATEGY', 'single_blob'),

    /*
    | Locales the storefront is expected to expose; used for future index fields.
    */
    'supported_ui_locales' => ['ca', 'es', 'en'],
];
