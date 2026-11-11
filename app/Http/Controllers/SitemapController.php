<?php

namespace App\Http\Controllers;

use App\Models\Pack;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $xml = Cache::remember('sitemap.xml', now()->addHours(6), function (): string {
            $baseUrl = rtrim((string) config('seo.sitemap_base_url'), '/');
            $marketingUrl = rtrim((string) config('seo.marketing_base_url'), '/');
            $categories = ProductCategory::active()->orderBy('id')->get(['id', 'updated_at']);
            $products = Product::active()->orderBy('id')->get(['id', 'updated_at']);
            $packs = Pack::active()->orderBy('id')->get(['id', 'updated_at']);

            $marketingPages = [];
            foreach ((array) config('seo.marketing_pages', []) as $page) {
                $path = (string) ($page['path'] ?? '');
                $file = (string) ($page['file'] ?? '');
                if ($path === '' || $file === '') {
                    continue;
                }

                $lastmod = null;
                $absolute = base_path('static-site/www/'.$file);
                if (is_file($absolute)) {
                    $mtime = filemtime($absolute);
                    if ($mtime !== false) {
                        $lastmod = gmdate('c', $mtime);
                    }
                }

                $loc = $path === '/'
                    ? $marketingUrl.'/'
                    : $marketingUrl.$path;

                $marketingPages[] = [
                    'loc' => $loc,
                    'lastmod' => $lastmod,
                ];
            }

            return view('sitemap', compact(
                'baseUrl',
                'marketingPages',
                'categories',
                'products',
                'packs',
            ))->render();
        });

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
