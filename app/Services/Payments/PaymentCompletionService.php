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
            // Pessimistic lock: prevents concurrent webhook + confirm-endpoint calls from
            // both reading status=pending and both dispatching the payment-succeeded email.
            $locked = Payment::query()->lockForUpdate()->find($payment->id);
            if (! $locked || $locked->status === Payment::STATUS_SUCCEEDED) {
                return;
            }
            $shouldNotify = true;
            $locked->loadMissing('order');
            $order = $locked->order;
            if ($order && $order->kind === Order::KIND_CART) {
                $order->update([
                    'kind' => Order::KIND_ORDER,
                    'order_date' => now(),
                    'status' => Order::STATUS_PENDING,
                ]);
            }

            $locked->update(array_merge([
                'status' => Payment::STATUS_SUCCEEDED,
                'paid_at' => now(),
                'failure_code' => null,
                'failure_message' => null,
            ], $extraAttributes));

            $locked->loadMissing('order');
            $order = $locked->order;
            if ($order && $order->kind === Order::KIND_ORDER && $order->status === Order::STATUS_AWAITING_PAYMENT) {
                $order->update(['status' => Order::STATUS_PENDING]);
            }
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
            $payment->loadMissing('order');
            $order = $payment->order;
            $payment->update([
                'status' => Payment::STATUS_FAILED,
                'failure_code' => $code,
                'failure_message' => $message,
            ]);
            if ($order && $order->kind === Order::KIND_CART) {
                $this->revertDeferredCheckoutOnCart($order);
            }
        });
    }

    public function markCanceled(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $payment->refresh();
            if ($payment->status === Payment::STATUS_SUCCEEDED) {
                return;
            }
            $payment->loadMissing('order');
            $order = $payment->order;
            $payment->update([
                'status' => Payment::STATUS_CANCELED,
            ]);
            if ($order && $order->kind === Order::KIND_CART) {
                $this->revertDeferredCheckoutOnCart($order);
            }
        });
    }

    /**
     * After an aborted PSP checkout, restore the cart so the client can retry from /checkout.
     */
    public function revertDeferredCheckoutOnCart(Order $cart): void
    {
        if ($cart->kind !== Order::KIND_CART) {
            return;
        }
        $cart->addresses()->delete();
        $cart->payments()->where('status', '!=', Payment::STATUS_SUCCEEDED)->delete();
        $cart->update([
            'shipping_price' => null,
            'installation_status' => null,
            'installation_price' => null,
        ]);
    }

    public function markRefunded(Payment $payment): void
    {
        DB::transaction(function () use ($payment) {
            $payment->refresh();
            if ($payment->status === Payment::STATUS_REFUNDED) {
                return;
            }
            $payment->update([
                'status' => Payment::STATUS_REFUNDED,
            ]);
        });
    }
}
