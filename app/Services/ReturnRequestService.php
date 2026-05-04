<?php

namespace App\Services;

use App\Exceptions\PaymentProviderNotConfiguredException;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ReturnRequest;
use App\Services\Payments\PaymentCompletionService;
use App\Services\Payments\Stripe\StripeCredentials;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;

class ReturnRequestService
{
    public function __construct(
        private readonly PaymentCompletionService $paymentCompletion,
    ) {}

    /**
     * Create a new return request for the given order.
     * Caller must verify eligibility (status, payment, no open RMA) before calling.
     */
    public function create(Order $order, string $reason): ReturnRequest
    {
        $payment = $order->payments()->successful()->latest('paid_at')->first();

        return ReturnRequest::create([
            'order_id' => $order->id,
            'client_id' => $order->client_id,
            'payment_id' => $payment?->id,
            'status' => ReturnRequest::STATUS_PENDING_REVIEW,
            'reason' => $reason,
        ]);
    }

    public function approve(ReturnRequest $rma, ?string $adminNotes): ReturnRequest
    {
        $rma->update([
            'status' => ReturnRequest::STATUS_APPROVED,
            'admin_notes' => $adminNotes,
        ]);

        return $rma->fresh();
    }

    public function reject(ReturnRequest $rma, string $adminNotes): ReturnRequest
    {
        $rma->update([
            'status' => ReturnRequest::STATUS_REJECTED,
            'admin_notes' => $adminNotes,
        ]);

        return $rma->fresh();
    }

    /**
     * Issue a refund via Stripe and mark the order as returned.
     *
     * @throws PaymentProviderNotConfiguredException when Stripe is not configured
     * @throws \RuntimeException when the payment cannot be identified for refund
     */
    public function issueRefund(ReturnRequest $rma, float $amount): ReturnRequest
    {
        $payment = $rma->payment;
        if (! $payment) {
            throw new \RuntimeException('No payment associated with this return request.');
        }

        if ($payment->gateway === Payment::GATEWAY_STRIPE) {
            return $this->issueStripeRefund($rma, $payment, $amount);
        }

        // PayPal and other gateways: mark as refunded without PSP call (manual refund workflow).
        DB::transaction(function () use ($rma, $payment, $amount): void {
            $this->paymentCompletion->markRefunded($payment);
            $rma->order()->update(['status' => Order::STATUS_RETURNED]);
            $rma->update([
                'status' => ReturnRequest::STATUS_REFUNDED,
                'refund_amount' => $amount,
                'refunded_at' => now(),
            ]);
        });

        return $rma->fresh();
    }

    private function issueStripeRefund(ReturnRequest $rma, Payment $payment, float $amount): ReturnRequest
    {
        if (! StripeCredentials::areConfigured()) {
            throw new PaymentProviderNotConfiguredException('Stripe is not configured (STRIPE_SECRET/STRIPE_KEY).');
        }

        $intentId = $this->resolveStripePaymentIntentId($payment);
        if ($intentId === null) {
            throw new \RuntimeException('Cannot resolve Stripe PaymentIntent ID from payment record.');
        }

        /** @var string $secret */
        $secret = config('services.stripe.secret');
        $stripe = new StripeClient($secret);

        $amountCents = (int) round($amount * 100);
        $refund = $stripe->refunds->create([
            'payment_intent' => $intentId,
            'amount' => $amountCents,
        ]);

        DB::transaction(function () use ($rma, $payment, $amount, $refund): void {
            $this->paymentCompletion->markRefunded($payment);
            $rma->order()->update(['status' => Order::STATUS_RETURNED]);
            $rma->update([
                'status' => ReturnRequest::STATUS_REFUNDED,
                'refund_amount' => $amount,
                'refunded_at' => now(),
                'gateway_refund_reference' => $refund->id,
            ]);
        });

        return $rma->fresh();
    }

    private function resolveStripePaymentIntentId(Payment $payment): ?string
    {
        $ref = $payment->gateway_reference;
        if (is_string($ref) && str_starts_with($ref, 'pi_')) {
            return $ref;
        }

        $meta = $payment->metadata;
        if (is_array($meta) && isset($meta['stripe_payment_intent_id']) && is_string($meta['stripe_payment_intent_id'])) {
            return $meta['stripe_payment_intent_id'];
        }

        return null;
    }
}
