<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\PaymentCompletionService;
use App\Services\Payments\PayPal\PayPalClient;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class PayPalPaymentController extends Controller
{
    public function capture(Request $request, PayPalClient $client, PaymentCompletionService $completion): JsonResponse
    {
        $validated = $request->validate([
            'paypal_order_id' => ['required', 'string', 'max:255'],
            'payment_id' => ['required', 'integer', 'exists:payments,id'],
        ]);

        $user = $request->user();
        $payment = Payment::query()->with('order')->find((int) $validated['payment_id']);
        if ($payment === null) {
            return response()->json([
                'success' => false,
                'message' => __('shop.payment.paypal_payment_not_found'),
            ], 404);
        }

        if ($payment->order === null || $payment->order->client_id !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => __('shop.payment.paypal_payment_not_found'),
            ], 403);
        }

        if ($payment->gateway !== Payment::GATEWAY_PAYPAL
            || $payment->gateway_reference !== $validated['paypal_order_id']) {
            return response()->json([
                'success' => false,
                'message' => __('shop.payment.paypal_order_mismatch'),
            ], 422);
        }

        if ($payment->isSuccessful()) {
            return response()->json([
                'success' => true,
                'data' => ['already_paid' => true],
            ]);
        }

        try {
            $data = $client->captureOrder($validated['paypal_order_id']);
        } catch (Throwable $e) {
            report($e);

            return response()->json([
                'success' => false,
                'message' => config('app.debug') ? $e->getMessage() : __('shop.payment.paypal_capture_failed'),
            ], 422);
        }

        if (($data['status'] ?? '') !== 'COMPLETED') {
            return response()->json([
                'success' => false,
                'message' => __('shop.payment.paypal_capture_failed'),
            ], 422);
        }

        $captureId = $data['purchase_units'][0]['payments']['captures'][0]['id'] ?? null;
        if (is_string($captureId) && $captureId !== '') {
            $payment->update([
                'metadata' => array_merge((array) $payment->metadata, ['paypal_capture_id' => $captureId]),
            ]);
        }

        $completion->markSucceeded($payment->fresh());

        return response()->json([
            'success' => true,
            'data' => [
                'payment_id' => $payment->id,
            ],
        ]);
    }
}
