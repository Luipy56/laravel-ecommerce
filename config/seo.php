<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public sitemap / robots canonical host
    |--------------------------------------------------------------------------
    |
    | Apex storefront host used in sitemap.xml and robots.txt. Independent of
    | APP_URL so temporary aliases (serra.ldeluipy.es, tienda.*) do not leak
    | into search indexes when the real domain is serralleriasolidaria.cat.
    |
    */

    'sitemap_base_url' => rtrim(env('SITEMAP_BASE_URL', 'https://serralleriasolidaria.cat'), '/'),

];
