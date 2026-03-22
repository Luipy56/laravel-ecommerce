<?php

namespace App\Services\Payments\Revolut;

use App\Contracts\Payments\PaymentCheckoutStarter;
use App\Models\Payment;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class RevolutCheckoutStarter implements PaymentCheckoutStarter
{
    public function gateway(): string
    {
        return Payment::GATEWAY_REVOLUT;
    }

    public function start(Payment $payment): array
    {
        $apiKey = config('services.revolut.api_key');
        if (! is_string($apiKey) || $apiKey === '') {
            throw new RuntimeException('Revolut is not configured (REVOLUT_MERCHANT_API_KEY).');
        }

        $sandbox = (bool) config('services.revolut.sandbox', true);
        $base = $sandbox
            ? 'https://sandbox-merchant.revolut.com'
            : 'https://merchant.revolut.com';

        $version = config('services.revolut.api_version', '2023-09-01');

        $amountMinor = (int) round((float) $payment->amount * 100);

        $response = Http::withHeaders([
            'Authorization' => 'Bearer '.$apiKey,
            'Revolut-Api-Version' => $version,
            'Content-Type' => 'application/json',
        ])->post($base.'/api/orders', [
            'amount' => $amountMinor,
            'currency' => strtoupper($payment->currency ?? 'EUR'),
            'capture_mode' => 'automatic',
            'merchant_order_ext_ref' => 'payment_'.$payment->id,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException('Revolut order creation failed: '.$response->body());
        }

        $body = $response->json();
        $orderId = $body['id'] ?? null;
        $checkoutUrl = $body['checkout_url'] ?? ($body['public_id'] ?? null);

        if (! is_string($orderId)) {
            throw new RuntimeException('Revolut response missing order id.');
        }

        $payment->update([
            'gateway' => Payment::GATEWAY_REVOLUT,
            'gateway_reference' => $orderId,
            'status' => Payment::STATUS_REQUIRES_ACTION,
            'metadata' => array_merge($payment->metadata ?? [], [
                'revolut_checkout_url' => is_string($checkoutUrl) ? $checkoutUrl : null,
            ]),
        ]);

        if (! is_string($checkoutUrl) || $checkoutUrl === '') {
            throw new RuntimeException('Revolut response missing checkout_url.');
        }

        return [
            'gateway' => Payment::GATEWAY_REVOLUT,
            'checkout_url' => $checkoutUrl,
        ];
    }
}
