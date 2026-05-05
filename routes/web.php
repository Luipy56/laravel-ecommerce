<?php

use App\Http\Controllers\SitemapController;
use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware('web')->group(base_path('routes/api.php'));

Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');

Route::get('/robots.txt', function () {
    $content = "User-agent: *\nDisallow:\nSitemap: " . url('/sitemap.xml') . "\n";

    return response($content, 200)->header('Content-Type', 'text/plain');
})->name('robots');

Route::get('/{any?}', function () {
    return view('welcome');
})->where('any', '^(?!api|sanctum|up|storage|uploads).*$')->name('spa');
