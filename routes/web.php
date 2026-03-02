<?php

use Illuminate\Support\Facades\Route;

Route::prefix('api/v1')->middleware('web')->group(base_path('routes/api.php'));

Route::get('/{any?}', function () {
    return view('welcome');
})->where('any', '^(?!api|sanctum|up|storage).*$')->name('spa');
