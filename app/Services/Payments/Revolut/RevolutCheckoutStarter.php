<?php

namespace App\Services\Payments\Revolut;

use App\Contracts\Payments\PaymentCheckoutStarter;
use App\Exceptions\PaymentProviderNotConfiguredException;
use App\Models\Order;
use App\Models\Payment;
use RuntimeException;

class RevolutCheckoutStarter implements PaymentCheckoutStarter
{
    public function __construct(
        private readonly RevolutClient $client,
    ) {}

    public function gateway(): string
    {
        return Payment::GATEWAY_REVOLUT;
    }

    public function start(Payment $payment): array
    {
        if (! RevolutCredentials::areConfigured()) {
            throw new PaymentProviderNotConfiguredException(
                'Revolut is not configured (REVOLUT_MERCHANT_API_KEY).',
            );
        }

        $payment->loadMissing('order.client');
        $order = $payment->order;
        if ($order === null) {
            throw new RuntimeException('Payment has no order.');
        }

        if ($payment->gateway === Payment::GATEWAY_REVOLUT
            && is_string($payment->gateway_reference)
            && $payment->gateway_reference !== '') {
            $existingUrl = $payment->metadata['revolut_checkout_url'] ?? null;
            if (is_string($existingUrl) && $existingUrl !== '') {
                return [
                    'gateway' => Payment::GATEWAY_REVOLUT,
                    'checkout_url' => $existingUrl,
                    'revolut_order_id' => $payment->gateway_reference,
                    'public_key' => RevolutCredentials::publicKey(),
                    'sandbox' => RevolutCredentials::sandbox(),
                ];
            }
        }

        $baseUrl = rtrim((string) config('app.url'), '/');
        $returnPath = $order->kind === Order::KIND_CART
            ? '/checkout'
            : '/orders/'.$order->id;

        $amountMinor = (int) round((float) $payment->amount * 100);
        $currency = strtoupper((string) ($payment->currency ?? 'EUR'));
        $extRef = 'payment_'.$payment->id;

        $body = [
            'amount' => $amountMinor,
            'currency' => $currency,
            'capture_mode' => 'automatic',
            'merchant_order_ext_ref' => $extRef,
            'merchant_order_data' => [
                'reference' => $extRef,
            ],
            'metadata' => [
                'payment_id' => (string) $payment->id,
                'order_id' => (string) $payment->order_id,
            ],
            // Known before create (unlike Revolut order id). Confirm + webhook both resolve the payment.
            'redirect_url' => $baseUrl.$returnPath.'?payment=ok&revolut_payment='.$payment->id,
        ];

        $email = $order->client?->login_email;
        if (is_string($email) && $email !== '') {
            $body['customer'] = ['email' => $email];
        }

        $created = $this->client->createOrder($body);
        $orderId = $created['id'] ?? null;
        $checkoutUrl = $created['checkout_url'] ?? null;

        if (! is_string($orderId) || $orderId === '') {
            throw new RuntimeException('Revolut response missing order id.');
        }
        if (! is_string($checkoutUrl) || $checkoutUrl === '') {
            throw new RuntimeException('Revolut response missing checkout_url.');
        }

        $payment->update([
            'gateway' => Payment::GATEWAY_REVOLUT,
            'gateway_reference' => $orderId,
            'status' => Payment::STATUS_REQUIRES_ACTION,
            'metadata' => array_merge($payment->metadata ?? [], [
                'revolut_checkout_url' => $checkoutUrl,
                'revolut_token' => is_string($created['token'] ?? null) ? $created['token'] : null,
            ]),
        ]);

        return [
            'gateway' => Payment::GATEWAY_REVOLUT,
            'checkout_url' => $checkoutUrl,
            'revolut_order_id' => $orderId,
            'public_key' => RevolutCredentials::publicKey(),
            'sandbox' => RevolutCredentials::sandbox(),
        ];
    }
}
