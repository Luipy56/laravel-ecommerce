<?php

use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\AdminCategoryController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PackController;
use App\Http\Controllers\Api\PersonalizedSolutionController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/* ------------------------ Public ------------------------ */
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::get('user', [AuthController::class, 'user']);

Route::get('categories', [CategoryController::class, 'index']);
Route::get('products', [ProductController::class, 'index']);
Route::get('products/featured', [ProductController::class, 'featured']);
Route::get('products/search', [ProductController::class, 'search']);
Route::get('products/{product}', [ProductController::class, 'show']);
Route::get('packs', [PackController::class, 'index']);
Route::get('packs/{pack}', [PackController::class, 'show']);

Route::post('personalized-solutions', [PersonalizedSolutionController::class, 'store']);

/* Cart: guest uses session, auth uses DB; controller branches */
Route::get('cart', [CartController::class, 'show']);
Route::post('cart/lines', [CartController::class, 'addLine']);
Route::put('cart/lines/{line}', [CartController::class, 'updateLine']);
Route::delete('cart/lines/{line}', [CartController::class, 'removeLine']);

/* ------------------------ Client auth required ------------------------ */
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);

    Route::get('profile', [UserController::class, 'show']);
    Route::put('profile', [UserController::class, 'update']);
    Route::post('profile/addresses', [UserController::class, 'storeAddress']);
    Route::put('profile/addresses/{id}', [UserController::class, 'updateAddress']);
    Route::delete('profile/addresses/{id}', [UserController::class, 'destroyAddress']);
    Route::post('profile/contacts', [UserController::class, 'storeContact']);
    Route::put('profile/contacts/{id}', [UserController::class, 'updateContact']);
    Route::delete('profile/contacts/{id}', [UserController::class, 'destroyContact']);

    Route::post('cart/merge', [CartController::class, 'merge']); // merge session cart into DB on login

    Route::post('orders/checkout', [OrderController::class, 'checkout']);
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::get('orders/{order}/invoice', [OrderController::class, 'invoice']);
});

/* ------------------------ Admin ------------------------ */
Route::post('admin/login', [AdminAuthController::class, 'login']);
Route::middleware(['auth:admin'])->prefix('admin')->group(function () {
    Route::post('logout', [AdminAuthController::class, 'logout']);
    Route::get('stats/sales-by-period', [AdminDashboardController::class, 'salesByPeriod']);
    Route::get('stats/top-products', [AdminDashboardController::class, 'topProducts']);
    Route::get('stats/low-stock', [AdminDashboardController::class, 'lowStock']);
    Route::apiResource('categories', AdminCategoryController::class)->only(['index', 'store', 'update', 'destroy']);
});
