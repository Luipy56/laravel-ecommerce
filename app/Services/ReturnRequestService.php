<?php

namespace App\Services;

use App\Exceptions\PaymentProviderNotConfiguredException;
use App\Models\Order;
use App\Models\Payment;
use App\Models\ReturnRequest;
use App\Services\Payments\PaymentCompletionService;
use App\Services\Payments\PayPal\PayPalClient;
use App\Services\Payments\Stripe\StripeCredentials;
use Illuminate\Support\Facades\DB;
use Stripe\StripeClient;

class ReturnRequestService
{
    public function __construct(
        private readonly PaymentCompletionService $paymentCompletion,
        private readonly PayPalClient $paypalClient,
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
     * Issue a refund via the payment gateway and mark the order as returned.
     *
     * @throws PaymentProviderNotConfiguredException when the gateway is not configured
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

        if ($payment->gateway === Payment::GATEWAY_PAYPAL) {
            return $this->issuePayPalRefund($rma, $payment, $amount);
        }

        // Other gateways: mark as refunded without PSP call (manual refund workflow).
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

    private function issuePayPalRefund(ReturnRequest $rma, Payment $payment, float $amount): ReturnRequest
    {
        if (! PayPalClient::envCredentialsPresent()) {
            throw new PaymentProviderNotConfiguredException('PayPal is not configured (PAYPAL_CLIENT_ID, PAYPAL_SECRET).');
        }

        $meta = $payment->metadata;
        $captureId = is_array($meta) && isset($meta['paypal_capture_id']) && is_string($meta['paypal_capture_id'])
            ? $meta['paypal_capture_id']
            : null;

        if ($captureId === null || $captureId === '') {
            throw new \RuntimeException('Cannot refund via PayPal: capture ID not found. Refund this payment manually via the PayPal dashboard.');
        }

        $currency = is_string($payment->currency) && $payment->currency !== '' ? $payment->currency : 'EUR';
        $refundData = $this->paypalClient->refundCapture($captureId, $amount, $currency);

        $refundId = $refundData['id'] ?? null;

        DB::transaction(function () use ($rma, $payment, $amount, $refundId): void {
            $this->paymentCompletion->markRefunded($payment);
            $rma->order()->update(['status' => Order::STATUS_RETURNED]);
            $rma->update([
                'status' => ReturnRequest::STATUS_REFUNDED,
                'refund_amount' => $amount,
                'refunded_at' => now(),
                'gateway_refund_reference' => $refundId,
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
