<?php

namespace App\Services\Payments\Stripe;

use App\Contracts\Payments\PaymentCheckoutStarter;
use App\Exceptions\PaymentProviderNotConfiguredException;
use App\Models\Payment;
use RuntimeException;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Stripe\StripeClient;

class StripeCheckoutStarter implements PaymentCheckoutStarter
{
    public function gateway(): string
    {
        return Payment::GATEWAY_STRIPE;
    }

    public function start(Payment $payment): array
    {
        if (! StripeCredentials::areConfigured()) {
            throw new PaymentProviderNotConfiguredException(
                'Stripe is not configured (STRIPE_SECRET/STRIPE_KEY).',
            );
        }

        /** @var string $secret */
        $secret = config('services.stripe.secret');
        /** @var string $publishable */
        $publishable = config('services.stripe.key');

        $stripe = new StripeClient($secret);
        $payment->loadMissing('order.client');
        $order = $payment->order;
        if ($order === null) {
            throw new RuntimeException('Payment has no order.');
        }

        $baseUrl = rtrim((string) config('app.url'), '/');
        $successUrl = $baseUrl.'/orders/'.$order->id.'?payment=ok&session_id={CHECKOUT_SESSION_ID}';
        $cancelUrl = $baseUrl.'/orders/'.$order->id.'?payment=ko';

        if ($payment->gateway === Payment::GATEWAY_STRIPE
            && is_string($payment->gateway_reference)
            && str_starts_with($payment->gateway_reference, 'cs_')) {
            try {
                $existing = $stripe->checkout->sessions->retrieve($payment->gateway_reference);
                if ($existing instanceof StripeCheckoutSession
                    && $existing->status === 'open'
                    && is_string($existing->url) && $existing->url !== '') {
                    return [
                        'gateway' => Payment::GATEWAY_STRIPE,
                        'checkout_url' => $existing->url,
                        'publishable_key' => $publishable,
                        'session_id' => $existing->id,
                    ];
                }
            } catch (\Throwable) {
                // Create a new session below.
            }
        }

        $amountCents = (int) round((float) $payment->amount * 100);
        $currency = strtolower((string) ($payment->currency ?? 'EUR'));
        $pmTypes = config('payments.stripe_checkout_payment_method_types');
        if (! is_array($pmTypes) || $pmTypes === []) {
            $pmTypes = ['card', 'bizum'];
        }

        $productName = __('shop.checkout_stripe_product', ['id' => $order->id]);

        $params = [
            'mode' => 'payment',
            'line_items' => [
                [
                    'price_data' => [
                        'currency' => $currency,
                        'product_data' => [
                            'name' => $productName,
                        ],
                        'unit_amount' => $amountCents,
                    ],
                    'quantity' => 1,
                ],
            ],
            'success_url' => $successUrl,
            'cancel_url' => $cancelUrl,
            'client_reference_id' => (string) $payment->id,
            'metadata' => [
                'payment_id' => (string) $payment->id,
                'order_id' => (string) $payment->order_id,
            ],
            'payment_intent_data' => [
                'metadata' => [
                    'payment_id' => (string) $payment->id,
                    'order_id' => (string) $payment->order_id,
                ],
            ],
            'payment_method_types' => $pmTypes,
            'locale' => match (app()->getLocale()) {
                'ca', 'es' => 'es',
                'en' => 'en',
                default => 'auto',
            },
        ];

        $email = $order->client?->login_email;
        if (is_string($email) && $email !== '') {
            $params['customer_email'] = $email;
        }

        $session = $stripe->checkout->sessions->create($params);

        if (! $session instanceof StripeCheckoutSession || ! is_string($session->url) || $session->url === '') {
            throw new RuntimeException('Invalid Stripe Checkout Session response.');
        }

        $payment->update([
            'gateway' => Payment::GATEWAY_STRIPE,
            'gateway_reference' => $session->id,
            'status' => Payment::STATUS_REQUIRES_ACTION,
        ]);

        return [
            'gateway' => Payment::GATEWAY_STRIPE,
            'checkout_url' => $session->url,
            'publishable_key' => $publishable,
            'session_id' => $session->id,
        ];
    }
}
