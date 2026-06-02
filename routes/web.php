<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\GoogleMerchantFeedController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\SpaShellController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware('web')->group(base_path('routes/api.php'));

Route::get('/csrf-cookie', function () {
    return response()->json(['token' => csrf_token()]);
})->middleware('web')->name('csrf-cookie');

Route::middleware('web')->group(function () {
    Route::post('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/feeds/google-merchant.xml', [GoogleMerchantFeedController::class, 'index'])->name('feeds.google-merchant');

Route::get('/robots.txt', function () {
    $content = "User-agent: *\nDisallow:\nSitemap: " . url('/sitemap.xml') . "\n";

    return response($content, 200)->header('Content-Type', 'text/plain');
})->name('robots');

Route::middleware('web')->group(function () {
    Route::get('/', [SpaShellController::class, 'home'])->name('spa.home');
    Route::get('/products', [SpaShellController::class, 'products'])->name('spa.products');
    Route::get('/products/{id}', [SpaShellController::class, 'product'])->whereNumber('id')->name('spa.product');
    Route::get('/packs/{id}', [SpaShellController::class, 'pack'])->whereNumber('id')->name('spa.pack');
    Route::get('/categories/{id}/products', [SpaShellController::class, 'categoryProducts'])->whereNumber('id')->name('spa.category');
});

Route::get('/{any?}', [SpaShellController::class, 'fallback'])
    ->where('any', '^(?!api|sanctum|up|storage|uploads|auth|feeds|csrf-cookie).*$')
    ->name('spa');
