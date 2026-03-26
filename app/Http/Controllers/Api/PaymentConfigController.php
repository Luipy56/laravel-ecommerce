<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Payments\PaymentCheckoutService;
use Illuminate\Http\JsonResponse;

/**
 * Public read-only: which payment methods the server can start (based on .env).
 */
class PaymentConfigController extends Controller
{
    public function show(): JsonResponse
    {
        $m = PaymentCheckoutService::paymentMethodsAvailability();
        $anyMethod = $m['card'] || $m['paypal'] || $m['bizum'] || $m['revolut'];

        return response()->json([
            'success' => true,
            'data' => [
                'methods' => [
                    'card' => $m['card'],
                    'paypal' => $m['paypal'],
                    'bizum' => $m['bizum'],
                    'revolut' => $m['revolut'],
                ],
                'simulated' => $m['simulated'],
                /** True when local env, no PSP keys, and simulated payments are off (usually APP_DEBUG=false). */
                'local_checkout_needs_debug' => app()->environment('local') && ! $m['simulated'] && ! $anyMethod,
                'paypal_missing_credentials' => PaymentCheckoutService::paypalMissingCredentialsForStorefront(),
            ],
        ]);
    }
}
