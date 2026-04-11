<?php

namespace App\Services\Payments\PayPal;

use App\Contracts\Payments\PaymentCheckoutStarter;
use App\Models\Payment;
use RuntimeException;

class PayPalCheckoutStarter implements PaymentCheckoutStarter
{
    public function __construct(
        private readonly PayPalClient $client,
    ) {}

    public function gateway(): string
    {
        return Payment::GATEWAY_PAYPAL;
    }

    public function start(Payment $payment): array
    {
        $this->client->assertConfigured();
        $clientId = (string) config('services.paypal.client_id');

        if ($payment->gateway === Payment::GATEWAY_PAYPAL && is_string($payment->gateway_reference) && $payment->gateway_reference !== '') {
            $order = $this->client->getOrder($payment->gateway_reference);
            $status = is_array($order) ? ($order['status'] ?? null) : null;
            if ($status === 'CREATED') {
                $order = $this->client->getOrder($payment->gateway_reference);
                $approvalUrl = is_array($order) ? PayPalClient::approvalUrlFromOrderResponse($order) : null;

                return $this->checkoutPayload($payment, $clientId, $payment->gateway_reference, $approvalUrl);
            }
        }

        $payment->loadMissing('order');
        $order = $payment->order;
        if ($order === null) {
            throw new RuntimeException('Payment has no order.');
        }

        $amount = number_format((float) $payment->amount, 2, '.', '');
        $currency = strtoupper((string) ($payment->currency ?? 'EUR'));
        $referenceId = 'payment_'.$payment->id;
        $customId = (string) $payment->id;
        $invoiceId = 'ORD-'.$order->id;

        $base = rtrim((string) config('app.url'), '/');
        $cancelUrl = $base.'/orders/'.$order->id.'?payment=ko';
        $returnUrl = $base.'/orders/'.$order->id.'?payment=paypal_return';

        $created = $this->client->createOrder(
            $amount,
            $currency,
            $referenceId,
            $customId,
            $invoiceId,
            $returnUrl,
            $cancelUrl,
        );

        $paypalOrderId = $created['id'] ?? null;
        if (! is_string($paypalOrderId) || $paypalOrderId === '') {
            throw new RuntimeException('PayPal create order returned no id.');
        }

        $approvalUrl = PayPalClient::approvalUrlFromOrderResponse($created);

        $payment->update([
            'gateway' => Payment::GATEWAY_PAYPAL,
            'gateway_reference' => $paypalOrderId,
            'status' => Payment::STATUS_REQUIRES_ACTION,
        ]);

        return $this->checkoutPayload($payment->fresh(), $clientId, $paypalOrderId, $approvalUrl);
    }

    /**
     * @return array{
     *     gateway: string,
     *     client_id: string,
     *     paypal_order_id: string,
     *     payment_id: int,
     *     approval_url: string|null
     * }
     */
    private function checkoutPayload(Payment $payment, string $clientId, string $paypalOrderId, ?string $approvalUrl = null): array
    {
        return [
            'gateway' => Payment::GATEWAY_PAYPAL,
            'client_id' => $clientId,
            'paypal_order_id' => $paypalOrderId,
            'payment_id' => (int) $payment->id,
            'approval_url' => $approvalUrl,
        ];
    }
}
