<?php

use App\Http\Controllers\Api\AdminAboutController;
use App\Http\Controllers\Api\AdminAdminController;
use App\Http\Controllers\Api\AdminAuthController;
use App\Http\Controllers\Api\AdminCategoryController;
use App\Http\Controllers\Api\AdminClientController;
use App\Http\Controllers\Api\AdminDashboardController;
use App\Http\Controllers\Api\AdminDataExplorerController;
use App\Http\Controllers\Api\AdminFaqController;
use App\Http\Controllers\Api\AdminFeatureController;
use App\Http\Controllers\Api\AdminFeatureNameController;
use App\Http\Controllers\Api\AdminNavAlertsController;
use App\Http\Controllers\Api\AdminOrderController;
use App\Http\Controllers\Api\AdminSendEmailController;
use App\Http\Controllers\Api\AdminPackController;
use App\Http\Controllers\Api\AdminPersonalizedSolutionController;
use App\Http\Controllers\Api\AdminProductController;
use App\Http\Controllers\Api\AdminProductReviewController;
use App\Http\Controllers\Api\AdminShopSettingsController;
use App\Http\Controllers\Api\AdminVariantGroupController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\ProductReviewController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ClientPasswordResetController;
use App\Http\Controllers\Api\ClientVerificationController;
use App\Http\Controllers\Api\FavoriteController;
use App\Http\Controllers\Api\FeatureController;
use App\Http\Controllers\Api\FaqController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\PackController;
use App\Http\Controllers\Api\PackReviewController;
use App\Http\Controllers\Api\PaymentConfigController;
use App\Http\Controllers\Api\PaymentWebhookController;
use App\Http\Controllers\Api\PayPalPaymentController;
use App\Http\Controllers\Api\PersonalizedSolutionController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\PublicPersonalizedSolutionController;
use App\Http\Controllers\Api\PurchasedProductsController;
use App\Http\Controllers\Api\AdminReturnRequestController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\ReturnRequestController;
use App\Http\Controllers\Api\ShopPublicSettingsController;
use App\Http\Controllers\Api\StripeCheckoutConfirmController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/* ------------------------ Public ------------------------ */
Route::post('login', [AuthController::class, 'login']);
Route::post('register', [AuthController::class, 'register']);
Route::get('user', [AuthController::class, 'user']);

Route::post('forgot-password', [ClientPasswordResetController::class, 'forgot'])
    ->middleware('throttle:6,1');
Route::post('reset-password', [ClientPasswordResetController::class, 'reset'])
    ->middleware('throttle:6,1');

Route::get('email/verify/{id}/{hash}', [ClientVerificationController::class, 'verify'])
    ->middleware(['signed', 'throttle:6,1'])
    ->name('verification.verify');

Route::get('categories', [CategoryController::class, 'index']);
Route::get('categories/with-first-product', [CategoryController::class, 'withFirstProduct']);
Route::get('features', [FeatureController::class, 'index']);
Route::get('products', [ProductController::class, 'index']);
Route::get('products/featured', [ProductController::class, 'featured']);
Route::get('products/price-range', [ProductController::class, 'priceRange']);
Route::get('products/search', [ProductController::class, 'search'])
    ->middleware('throttle:60,1');
Route::get('products/{product}', [ProductController::class, 'show']);
Route::get('products/{product}/reviews', [ProductReviewController::class, 'index']);
Route::get('packs', [PackController::class, 'index']);
Route::get('packs/{pack}', [PackController::class, 'show']);
Route::get('packs/{pack}/reviews', [PackReviewController::class, 'index']);

Route::get('payments/config', [PaymentConfigController::class, 'show']);

Route::get('shop/public-settings', [ShopPublicSettingsController::class, 'show']);

Route::get('faqs', [FaqController::class, 'index']);

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

/* ------------------------ Client auth (unverified may logout / resend / read profile) ------------------------ */
Route::middleware('auth')->group(function () {
    Route::post('logout', [AuthController::class, 'logout']);
    Route::post('email/resend', [ClientVerificationController::class, 'resend'])
        ->middleware('throttle:6,1');

    Route::get('profile', [UserController::class, 'show']);
    Route::get('profile/export', [UserController::class, 'export'])->middleware('throttle:3,1');
    Route::get('profile/consents', [UserController::class, 'consents']);
    Route::delete('profile', [UserController::class, 'destroy']);
});

/* ------------------------ Client auth + verified email ------------------------ */
Route::middleware(['auth', 'client.verified'])->group(function () {
    Route::put('profile', [UserController::class, 'update']);
    Route::post('profile/addresses', [UserController::class, 'storeAddress']);
    Route::put('profile/addresses/{id}', [UserController::class, 'updateAddress']);
    Route::delete('profile/addresses/{id}', [UserController::class, 'destroyAddress']);
    Route::post('profile/contacts', [UserController::class, 'storeContact']);
    Route::put('profile/contacts/{id}', [UserController::class, 'updateContact']);
    Route::delete('profile/contacts/{id}', [UserController::class, 'destroyContact']);

    Route::post('cart/merge', [CartController::class, 'merge']); // merge session cart into DB on login
    Route::post('cart/cancel-pending-checkout', [CartController::class, 'cancelPendingCheckout']);

    Route::get('favorites/ids', [FavoriteController::class, 'ids']);
    Route::get('favorites', [FavoriteController::class, 'index']);
    Route::post('favorites/toggle', [FavoriteController::class, 'toggle']);
    Route::delete('favorites/lines/{orderLine}', [FavoriteController::class, 'destroyLine']);

    Route::post('payments/paypal/capture', [PayPalPaymentController::class, 'capture']);
    Route::post('payments/stripe/checkout/confirm', [StripeCheckoutConfirmController::class, 'store'])
        ->middleware('throttle:12,1');

    Route::post('orders/checkout', [OrderController::class, 'checkout']);
    Route::post('orders/{order}/pay', [OrderController::class, 'pay']);
    Route::post('orders/{order}/waive-installation', [OrderController::class, 'waiveInstallation']);
    Route::get('orders', [OrderController::class, 'index']);
    Route::get('purchases', [PurchasedProductsController::class, 'index']);
    Route::get('orders/{order}', [OrderController::class, 'show']);
    Route::get('orders/{order}/invoice', [OrderController::class, 'invoice']);
    Route::get('orders/{order}/delivery-note', [OrderController::class, 'deliveryNote']);

    Route::post('products/{product}/reviews', [ProductReviewController::class, 'store']);
    Route::get('products/{product}/reviews/mine', [ProductReviewController::class, 'mine']);

    Route::post('packs/{pack}/reviews', [PackReviewController::class, 'store']);
    Route::get('packs/{pack}/reviews/mine', [PackReviewController::class, 'mine']);

    Route::get('return-requests', [ReturnRequestController::class, 'index']);
    Route::post('orders/{order}/return-requests', [ReturnRequestController::class, 'store']);
});

Route::middleware(['auth.client_or_admin'])->group(function () {
    Route::get('reports/summary', [ReportController::class, 'summary']);
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
    Route::apiResource('faqs', AdminFaqController::class);
    Route::apiResource('categories', AdminCategoryController::class);
    Route::get('feature-names', [AdminFeatureNameController::class, 'index']);
    Route::get('feature-names-with-features', [AdminFeatureNameController::class, 'indexWithFeatures']);
    Route::post('feature-names', [AdminFeatureNameController::class, 'store']);
    Route::get('feature-names/{featureName}', [AdminFeatureNameController::class, 'show']);
    Route::put('feature-names/{featureName}', [AdminFeatureNameController::class, 'update']);
    Route::patch('feature-names/{featureName}/toggle', [AdminFeatureNameController::class, 'toggle']);
    Route::apiResource('features', AdminFeatureController::class);
    Route::patch('features/{feature}/toggle', [AdminFeatureController::class, 'toggle']);
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
    Route::post('orders/{order}/notify-in-transit-mail', [AdminOrderController::class, 'sendInTransitCustomerMail'])
        ->middleware('throttle:30,1');
    Route::get('orders/{order}/invoice', [AdminOrderController::class, 'invoice']);
    Route::get('orders/{order}/delivery-note', [AdminOrderController::class, 'deliveryNote']);
    Route::get('return-requests', [AdminReturnRequestController::class, 'index']);
    Route::get('return-requests/{rma}', [AdminReturnRequestController::class, 'show']);
    Route::put('return-requests/{rma}', [AdminReturnRequestController::class, 'update']);
    Route::post('return-requests/{rma}/refund', [AdminReturnRequestController::class, 'refund']);
    Route::apiResource('admins', AdminAdminController::class);
    Route::get('reviews', [AdminProductReviewController::class, 'index']);
    Route::get('reviews/{review}', [AdminProductReviewController::class, 'show']);
    Route::patch('reviews/{review}/toggle-visibility', [AdminProductReviewController::class, 'toggleVisibility']);
    Route::delete('reviews/{review}', [AdminProductReviewController::class, 'destroy']);
    Route::get('personalized-solutions', [AdminPersonalizedSolutionController::class, 'index']);
    Route::get('personalized-solutions/{personalized_solution}', [AdminPersonalizedSolutionController::class, 'show']);
    Route::post('personalized-solutions/{personalized_solution}/notify-resolution', [AdminPersonalizedSolutionController::class, 'notifyResolution']);
    Route::patch('personalized-solutions/{personalized_solution}/resolution', [AdminPersonalizedSolutionController::class, 'patchResolution']);
    Route::put('personalized-solutions/{personalized_solution}', [AdminPersonalizedSolutionController::class, 'update']);
    Route::delete('personalized-solutions/{personalized_solution}', [AdminPersonalizedSolutionController::class, 'destroy']);
    Route::post('send-email', AdminSendEmailController::class)->middleware('throttle:10,1');
});
