<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\Revolut\RevolutClient;
use App\Services\Payments\Revolut\RevolutCredentials;
use App\Services\Payments\Revolut\RevolutOrderCompleter;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Throwable;

class RevolutCheckoutConfirmController extends Controller
{
    public function __construct(
        private readonly RevolutClient $client,
        private readonly RevolutOrderCompleter $completer,
    ) {}

    public function store(Request $request): JsonResponse
    {
        if (! RevolutCredentials::areConfigured()) {
            return response()->json([
                'success' => false,
                'message' => __('shop.payment.method_unavailable'),
            ], 422);
        }

        $validated = $request->validate([
            'revolut_payment' => ['nullable', 'integer', 'min:1'],
            'revolut_order' => ['nullable', 'string', 'max:64'],
        ]);

        if (empty($validated['revolut_payment']) && empty($validated['revolut_order'])) {
            return response()->json([
                'success' => false,
                'message' => __('shop.payment.revolut_confirm_invalid'),
            ], 422);
        }

        $payment = null;
        if (! empty($validated['revolut_payment'])) {
            $payment = Payment::query()->with('order')->find((int) $validated['revolut_payment']);
        } elseif (! empty($validated['revolut_order'])) {
            $payment = Payment::query()
                ->with('order')
                ->where('gateway', Payment::GATEWAY_REVOLUT)
                ->where('gateway_reference', $validated['revolut_order'])
                ->first();
        }

        $client = $request->user();
        if (! $payment || ! $payment->order || (int) $payment->order->client_id !== (int) $client->id) {
            return response()->json([
                'success' => false,
                'message' => __('shop.payment.revolut_confirm_forbidden'),
            ], 403);
        }

        if ($payment->gateway !== Payment::GATEWAY_REVOLUT
            || ! is_string($payment->gateway_reference)
            || $payment->gateway_reference === '') {
            return response()->json([
                'success' => false,
                'message' => __('shop.payment.revolut_confirm_invalid'),
            ], 422);
        }

        try {
            $revolutOrder = $this->client->retrieveOrder($payment->gateway_reference);
        } catch (Throwable) {
            return response()->json([
                'success' => false,
                'message' => __('shop.payment.revolut_confirm_invalid'),
            ], 422);
        }

        $completed = $this->completer->completeFromOrderPayload($revolutOrder, $payment);

        if ($completed === null) {
            return response()->json([
                'success' => false,
                'message' => __('shop.payment.revolut_confirm_pending'),
                'data' => [
                    'revolut_state' => $revolutOrder['state'] ?? null,
                ],
            ], 422);
        }

        $order = $completed->order()->with(['lines.product', 'lines.pack', 'addresses', 'payments'])->first();

        return response()->json([
            'success' => true,
            'data' => [
                'revolut_state' => $revolutOrder['state'] ?? null,
                'has_payment' => $order?->hasSuccessfulPayment() ?? false,
                'order' => $order,
            ],
        ]);
    }
}
