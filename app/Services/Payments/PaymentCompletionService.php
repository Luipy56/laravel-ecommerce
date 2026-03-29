<?php

namespace App\Services\Payments;

use App\Events\OrderPaymentSucceeded;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentCompletionService
{
    /**
     * @param  array<string, mixed>  $extraAttributes  Merged into the payment update (e.g. gateway for simulated checkout).
     */
    public function markSucceeded(Payment $payment, array $extraAttributes = []): void
    {
        $shouldNotify = false;
        DB::transaction(function () use ($payment, &$shouldNotify, $extraAttributes) {
            $payment->refresh();
            if ($payment->status === Payment::STATUS_SUCCEEDED) {
                return;
            }
            $shouldNotify = true;
            $payment->update(array_merge([
                'status' => Payment::STATUS_SUCCEEDED,
                'paid_at' => now(),
                'failure_code' => null,
                'failure_message' => null,
            ], $extraAttributes));
        });

        if ($shouldNotify) {
            $this->dispatchOrderPaymentSucceeded($payment->fresh(['order']));
        }
    }

    private function dispatchOrderPaymentSucceeded(Payment $payment): void
    {
        $order = $payment->order;
        if (! $order || $order->kind !== Order::KIND_ORDER) {
            return;
        }

        OrderPaymentSucceeded::dispatch($order->fresh(['client', 'lines.product', 'lines.pack', 'addresses']));
    }

    public function markFailed(Payment $payment, ?string $code, ?string $message): void
    {
        DB::transaction(function () use ($payment, $code, $message) {
            $payment->refresh();
            if ($payment->status === Payment::STATUS_SUCCEEDED) {
                return;
            }
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'failure_code' => $code,
                'failure_message' => $message,
            ]);
        });
    }

    public function markCanceled(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $payment->refresh();
            if ($payment->status === Payment::STATUS_SUCCEEDED) {
                return;
            }
            $payment->update([
                'status' => Payment::STATUS_CANCELED,
            ]);
        });
    }
}
