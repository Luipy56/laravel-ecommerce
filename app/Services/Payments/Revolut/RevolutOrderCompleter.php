<?php

namespace App\Services\Payments\Revolut;

use App\Models\Payment;
use App\Services\Payments\PaymentCompletionService;
use Illuminate\Support\Facades\Log;

class RevolutOrderCompleter
{
    public function __construct(
        private readonly PaymentCompletionService $completion,
    ) {}

    /**
     * Mark our payment succeeded when Revolut order state is completed (or authorised with automatic capture).
     *
     * @param  array<string, mixed>  $revolutOrder
     */
    public function completeFromOrderPayload(array $revolutOrder, ?Payment $payment = null): ?Payment
    {
        $orderId = $revolutOrder['id'] ?? null;
        if (! is_string($orderId) || $orderId === '') {
            return null;
        }

        $payment ??= $this->findPayment($orderId, $revolutOrder);
        if ($payment === null) {
            Log::info('revolut.completer.payment_not_found', ['revolut_order_id' => $orderId]);

            return null;
        }

        $state = strtolower((string) ($revolutOrder['state'] ?? ''));
        if (! in_array($state, ['completed', 'authorised'], true)) {
            return null;
        }

        $this->completion->markSucceeded($payment, [
            'gateway' => Payment::GATEWAY_REVOLUT,
            'gateway_reference' => $orderId,
        ]);

        return $payment->fresh();
    }

    /**
     * @param  array<string, mixed>  $revolutOrder
     */
    public function findPayment(string $revolutOrderId, array $revolutOrder = []): ?Payment
    {
        $byRef = Payment::query()
            ->where('gateway', Payment::GATEWAY_REVOLUT)
            ->where('gateway_reference', $revolutOrderId)
            ->first();
        if ($byRef) {
            return $byRef;
        }

        $ext = $revolutOrder['merchant_order_ext_ref']
            ?? data_get($revolutOrder, 'merchant_order_data.reference');
        if (is_string($ext) && preg_match('/^payment_(\d+)$/', $ext, $m)) {
            $p = Payment::query()->find((int) $m[1]);
            if ($p && ($p->gateway === null || $p->gateway === Payment::GATEWAY_REVOLUT
                || $p->payment_method === Payment::METHOD_REVOLUT)) {
                return $p;
            }
        }

        $metaPaymentId = data_get($revolutOrder, 'metadata.payment_id');
        if (is_string($metaPaymentId) && $metaPaymentId !== '') {
            $p = Payment::query()->find((int) $metaPaymentId);
            if ($p && ($p->gateway === null || $p->gateway === Payment::GATEWAY_REVOLUT
                || $p->payment_method === Payment::METHOD_REVOLUT)) {
                return $p;
            }
        }

        return null;
    }
}
