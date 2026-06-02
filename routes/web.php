<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware('web')->group(base_path('routes/api.php'));

Route::middleware('web')->group(function () {
    Route::post('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/robots.txt', function () {
    $content = "User-agent: *\nDisallow:\nSitemap: " . url('/sitemap.xml') . "\n";

    return response($content, 200)->header('Content-Type', 'text/plain');
})->name('robots');

Route::get('/{any?}', function () {
    return view('welcome');
})->where('any', '^(?!api|sanctum|up|storage|uploads|auth).*$')->name('spa');
