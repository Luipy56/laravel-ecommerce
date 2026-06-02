<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Cache;

class GoogleMerchantFeedController extends Controller
{
    public const CACHE_KEY = 'feeds.google-merchant.xml';

    public function index(): Response
    {
        $xml = Cache::remember(self::CACHE_KEY, now()->addHours(6), function (): string {
            $products = Product::query()
                ->active()
                ->with(['translations', 'images'])
                ->orderBy('id')
                ->get();

            return view('feeds.google-merchant', [
                'products' => $products,
                'storeName' => config('app.name'),
                'storeUrl' => rtrim((string) config('app.url'), '/'),
            ])->render();
        });

        return response($xml, 200)->header('Content-Type', 'application/xml; charset=UTF-8');
    }
}
