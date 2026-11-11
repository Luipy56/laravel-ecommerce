<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Public sitemap / robots canonical hosts
    |--------------------------------------------------------------------------
    |
    | Shop URLs in sitemap.xml / robots.txt use sitemap_base_url (apex shop).
    | Marketing site is listed via marketing_base_url (+ marketing_pages).
    | Independent of APP_URL so temporary aliases do not leak into indexes.
    |
    */

    'sitemap_base_url' => rtrim(env('SITEMAP_BASE_URL', 'https://serralleriasolidaria.cat'), '/'),

    'marketing_base_url' => rtrim(env('MARKETING_BASE_URL', 'https://serralleriasolidaria.cat/web'), '/'),

    /*
    | Canonical marketing paths (no .php). Mapped to static-site/www/{file}.php
    | for real lastmod via filemtime. Exclude thin/non-landing pages (e.g. modal).
    */
    'marketing_pages' => [
        ['path' => '/', 'file' => 'index.php'],
        ['path' => '/nosaltres', 'file' => 'nosaltres.php'],
        ['path' => '/serveis', 'file' => 'serveis.php'],
        ['path' => '/solidaritat', 'file' => 'solidaritat.php'],
        ['path' => '/webs', 'file' => 'webs.php'],
        ['path' => '/contacte', 'file' => 'contacte.php'],
        ['path' => '/blog', 'file' => 'blog.php'],
        ['path' => '/blog-mestrejaments', 'file' => 'blog-mestrejaments.php'],
        ['path' => '/blog-tipus-de-claus', 'file' => 'blog-tipus-de-claus.php'],
        ['path' => '/blog-parts-d-un-pany', 'file' => 'blog-parts-d-un-pany.php'],
        ['path' => '/avislegal', 'file' => 'avislegal.php'],
        ['path' => '/privacitat', 'file' => 'privacitat.php'],
        ['path' => '/cookies', 'file' => 'cookies.php'],
    ],

];
