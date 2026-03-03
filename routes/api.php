<?php

use App\Http\Controllers\Api\AdminAdminController;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\AdminClientController;
use App\Http\Controllers\Api\AdminCategoryController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdminFeatureController;
use App\Http\Controllers\Api\AdminFeatureNameController;
use App\Http\Controllers\Api\AdminPackController;
use App\Http\Controllers\Api\AdminOrderController;
use App\Http\Controllers\Api\AdminPersonalizedSolutionController;
use App\Http\Controllers\Api\AdminProductController;
use App\Http\Controllers\Api\AdminVariantGroupController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PackController;
use App\Http\Controllers\Api\PersonalizedSolutionController;
use App\Http\Controllers\Api\FeatureController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/* ------------------------ Public ------------------------ */
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::get('user', [AuthController::class, 'user']);

Route::get('categories', [CategoryController::class, 'index']);
Route::get('features', [FeatureController::class, 'index']);
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
    Route::get('feature-names', [AdminFeatureNameController::class, 'index']);
    Route::post('feature-names', [AdminFeatureNameController::class, 'store']);
    Route::get('feature-names/{featureName}', [AdminFeatureNameController::class, 'show']);
    Route::put('feature-names/{featureName}', [AdminFeatureNameController::class, 'update']);
    Route::apiResource('features', AdminFeatureController::class);
    Route::apiResource('products', AdminProductController::class);
    Route::post('products/{product}/images', [AdminProductController::class, 'storeImages']);
    Route::delete('products/{product}/images/{productImage}', [AdminProductController::class, 'destroyImage']);
    Route::apiResource('packs', AdminPackController::class);
    Route::post('packs/{pack}/images', [AdminPackController::class, 'storeImages']);
    Route::delete('packs/{pack}/images/{packImage}', [AdminPackController::class, 'destroyImage']);
    Route::apiResource('variant-groups', AdminVariantGroupController::class);
    Route::get('clients', [AdminClientController::class, 'index']);
    Route::get('clients/{client}', [AdminClientController::class, 'show']);
    Route::get('orders', [AdminOrderController::class, 'index']);
    Route::get('orders/{order}', [AdminOrderController::class, 'show']);
    Route::put('orders/{order}', [AdminOrderController::class, 'update']);
    Route::apiResource('admins', AdminAdminController::class);
    Route::get('personalized-solutions', [AdminPersonalizedSolutionController::class, 'index']);
    Route::get('personalized-solutions/{personalized_solution}', [AdminPersonalizedSolutionController::class, 'show']);
    Route::put('personalized-solutions/{personalized_solution}', [AdminPersonalizedSolutionController::class, 'update']);
    Route::delete('personalized-solutions/{personalized_solution}', [AdminPersonalizedSolutionController::class, 'destroy']);
});
