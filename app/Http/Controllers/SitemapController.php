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
            $categories = ProductCategory::active()->orderBy('id')->get(['id', 'updated_at']);
            $products = Product::active()->orderBy('id')->get(['id', 'updated_at']);
            $packs = Pack::active()->orderBy('id')->get(['id', 'updated_at']);

            return view('sitemap', compact('baseUrl', 'categories', 'products', 'packs'))->render();
        });

        return response($xml, 200)->header('Content-Type', 'application/xml');
    }
}
