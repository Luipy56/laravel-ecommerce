<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Fuzzy fallback (catalog search)
    |--------------------------------------------------------------------------
    |
    | When strict LIKE + synonym search returns no rows, a bounded second pass
    | loads candidate rows sharing a short prefix with the normalized query and
    | filters by edit distance in PHP. Disable on very large catalogs if needed.
    |
    */
    'fuzzy_fallback_enabled' => env('PRODUCT_SEARCH_FUZZY_FALLBACK', true),

    'fuzzy_candidate_limit' => (int) env('PRODUCT_SEARCH_FUZZY_CANDIDATES', 400),

    'max_variants' => (int) env('PRODUCT_SEARCH_MAX_VARIANTS', 32),

    /*
    |--------------------------------------------------------------------------
    | Catalan ↔ Spanish term groups (normalized, lowercase, ASCII-folded)
    |--------------------------------------------------------------------------
    |
    | Each inner list groups equivalent storefront terms. Matching uses the same
    | normalization as queries (accents removed, case folded).
    |
    */
    'synonym_groups' => [
        ['cargol', 'cargols', 'tornillo', 'tornillos'],
        ['martell', 'martillo', 'martillos'],
        ['clau', 'claus', 'llave', 'llaves'],
        ['cadenat', 'cadenats', 'candado', 'candados'],
        ['flexometre', 'flexometro', 'cinta', 'cintes'],
        ['taladre', 'taladro', 'berbiqui'],
        ['sierra', 'serra', 'sierras', 'serres'],
        ['tornavis', 'destornillador', 'destornilladores'],
        ['pala', 'pales'],
        ['aixada', 'aixades', 'azada', 'azadas'],
    ],

];
