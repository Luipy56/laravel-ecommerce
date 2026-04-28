<?php

use App\Http\Controllers\Api\AdminAboutController;
use App\Http\Controllers\Api\AdminAdminController;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\AdminCategoryController;
use App\Http\Controllers\Api\AdminClientController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdminDataExplorerController;
use App\Http\Controllers\Api\AdminFeatureController;
use App\Http\Controllers\Api\AdminFeatureNameController;
use App\Http\Controllers\Api\AdminNavAlertsController;
use App\Http\Controllers\Api\AdminOrderController;
use App\Http\Controllers\Api\AdminPackController;
use App\Http\Controllers\Api\AdminPersonalizedSolutionController;
use App\Http\Controllers\Api\AdminProductController;
use App\Http\Controllers\Api\AdminShopSettingsController;
use App\Http\Controllers\Api\AdminVariantGroupController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\FeatureController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PackController;
use App\Http\Controllers\Api\PaymentConfigController;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\PayPalPaymentController;
use App\Http\Controllers\Api\PersonalizedSolutionController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PublicPersonalizedSolutionController;
use App\Http\Controllers\Api\PurchasedProductsController;
use App\Http\Controllers\Api\ShopPublicSettingsController;
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
Route::get('products/search', [ProductController::class, 'search'])
    ->middleware('throttle:60,1');
Route::get('products/{product}', [ProductController::class, 'show']);
Route::get('packs', [PackController::class, 'index']);
Route::get('packs/{pack}', [PackController::class, 'show']);

Route::get('payments/config', [PaymentConfigController::class, 'show']);

Route::get('shop/public-settings', [ShopPublicSettingsController::class, 'show']);

Route::post('personalized-solutions', [PersonalizedSolutionController::class, 'store']);

Route::get('public/personalized-solutions/{token}', [PublicPersonalizedSolutionController::class, 'show'])
    ->where('token', '[a-f0-9]{64}');
Route::patch('public/personalized-solutions/{token}', [PublicPersonalizedSolutionController::class, 'update'])
    ->where('token', '[a-f0-9]{64}');
Route::delete('public/personalized-solutions/{token}', [PublicPersonalizedSolutionController::class, 'destroy'])
    ->where('token', '[a-f0-9]{64}');
Route::post('public/personalized-solutions/{token}/request-improvements', [PublicPersonalizedSolutionController::class, 'requestImprovements'])
    ->where('token', '[a-f0-9]{64}');

Route::post('payments/webhooks/stripe', [PaymentWebhookController::class, 'stripe']);

/* Cart: guest uses session, auth uses DB; controller branches */
Route::get('cart', [CartController::class, 'show']);
Route::put('cart/installation', [CartController::class, 'updateInstallation']);
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

    Route::post('payments/paypal/capture', [PayPalPaymentController::class, 'capture']);

    Route::post('orders/checkout', [OrderController::class, 'checkout']);
    Route::post('orders/{order}/pay', [OrderController::class, 'pay']);
    Route::post('orders/{order}/waive-installation', [OrderController::class, 'waiveInstallation']);
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('purchases', [PurchasedProductsController::class, 'index']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::get('orders/{order}/invoice', [OrderController::class, 'invoice']);
});

/* ------------------------ Admin ------------------------ */
Route::post('admin/login', [AdminAuthController::class, 'login']);
Route::middleware(['auth:admin'])->prefix('admin')->group(function () {
    Route::get('changelog', [AdminAboutController::class, 'changelog']);
    Route::get('nav-alerts', [AdminNavAlertsController::class, 'show']);
    Route::post('logout', [AdminAuthController::class, 'logout']);
    Route::get('settings', [AdminShopSettingsController::class, 'show']);
    Route::put('settings', [AdminShopSettingsController::class, 'update']);
    Route::post('settings/recalculate-trending', [AdminShopSettingsController::class, 'recalculateTrending'])
        ->middleware('throttle:6,1');
    Route::get('stats/postal-codes', [AdminDashboardController::class, 'postalCodes']);
    Route::get('stats/sales-by-period', [AdminDashboardController::class, 'salesByPeriod']);
    Route::get('stats/top-products', [AdminDashboardController::class, 'topProducts']);
    Route::get('stats/low-stock', [AdminDashboardController::class, 'lowStock']);
    Route::get('data-explorer/schema', [AdminDataExplorerController::class, 'schema']);
    Route::post('data-explorer/query', [AdminDataExplorerController::class, 'query'])->middleware('throttle:30,1');
    Route::post('data-explorer/export', [AdminDataExplorerController::class, 'export'])->middleware('throttle:10,1');
    Route::post('data-explorer/aggregate', [AdminDataExplorerController::class, 'aggregate'])->middleware('throttle:20,1');
    Route::apiResource('categories', AdminCategoryController::class);
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
    Route::post('personalized-solutions/{personalized_solution}/notify-resolution', [AdminPersonalizedSolutionController::class, 'notifyResolution']);
    Route::patch('personalized-solutions/{personalized_solution}/resolution', [AdminPersonalizedSolutionController::class, 'patchResolution']);
    Route::put('personalized-solutions/{personalized_solution}', [AdminPersonalizedSolutionController::class, 'update']);
    Route::delete('personalized-solutions/{personalized_solution}', [AdminPersonalizedSolutionController::class, 'destroy']);
});
