<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\Stripe\StripeCheckoutSessionCompleter;
use App\Services\Payments\Stripe\StripeCredentials;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class StripeCheckoutConfirmController extends Controller
{
    public function __construct(
        private readonly StripeCheckoutSessionCompleter $stripeCheckoutSessionCompleter,
    ) {}

    public function store(Request $request): JsonResponse
    {
        if (! StripeCredentials::areConfigured()) {
            return response()->json([
                'success' => false,
                'message' => __('shop.payment.method_unavailable'),
            ], 422);
        }

        $validated = $request->validate([
            'session_id' => ['required', 'string', 'max:255'],
        ]);

        /** @var string $secret */
        $secret = config('services.stripe.secret');
        $stripe = new StripeClient($secret);

        try {
            $session = $stripe->checkout->sessions->retrieve($validated['session_id'], []);
        } catch (ApiErrorException) {
            return response()->json([
                'success' => false,
                'message' => __('shop.payment.stripe_session_invalid'),
            ], 422);
        }

        $paymentId = $session->metadata['payment_id'] ?? null;
        if (! is_string($paymentId) || $paymentId === '') {
            return response()->json([
                'success' => false,
                'message' => __('shop.payment.stripe_session_invalid'),
            ], 422);
        }

        $payment = Payment::query()->with('order')->find((int) $paymentId);
        $client = $request->user();
        if (! $payment || ! $payment->order || (int) $payment->order->client_id !== (int) $client->id) {
            return response()->json([
                'success' => false,
                'message' => __('shop.payment.stripe_session_forbidden'),
            ], 403);
        }

        $completed = $this->stripeCheckoutSessionCompleter->completePaidCheckoutSession($session);

        if ($completed === null) {
            return response()->json([
                'success' => false,
                'message' => __('shop.payment.stripe_confirm_pending'),
                'data' => [
                    'payment_status' => $session->payment_status ?? null,
                ],
            ], 422);
        }

        $order = $completed->order()->with(['lines.product', 'lines.pack', 'addresses', 'payments'])->first();

        return response()->json([
            'success' => true,
            'data' => [
                'payment_status' => $session->payment_status ?? null,
                'has_payment' => $order?->hasSuccessfulPayment() ?? false,
                'order' => $order,
            ],
        ]);
    }
}
