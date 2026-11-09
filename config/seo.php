<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public sitemap / robots canonical hosts
    |--------------------------------------------------------------------------
    |
    | Shop URLs in sitemap.xml / robots.txt use sitemap_base_url (tienda host).
    | Marketing apex (classic PHP site) is listed via marketing_base_url.
    | Independent of APP_URL so temporary aliases (serra.ldeluipy.es) do not
    | leak into search indexes.
    |
    */

    'sitemap_base_url' => rtrim(env('SITEMAP_BASE_URL', 'https://tienda.serralleriasolidaria.cat'), '/'),

    'marketing_base_url' => rtrim(env('MARKETING_BASE_URL', 'https://serralleriasolidaria.cat'), '/'),

];
