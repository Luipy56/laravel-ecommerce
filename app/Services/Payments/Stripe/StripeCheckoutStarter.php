<?php

namespace App\Services\Payments\Stripe;

use App\Contracts\Payments\PaymentCheckoutStarter;
use App\Exceptions\PaymentProviderNotConfiguredException;
use App\Models\Payment;
use RuntimeException;
use Stripe\PaymentIntent as StripePaymentIntent;
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

        if ($payment->gateway === Payment::GATEWAY_STRIPE && $payment->gateway_reference) {
            $intent = $stripe->paymentIntents->retrieve($payment->gateway_reference);
            if ($intent instanceof StripePaymentIntent
                && in_array($intent->status, ['requires_payment_method', 'requires_confirmation', 'requires_action', 'requires_capture'], true)) {
                return [
                    'gateway' => Payment::GATEWAY_STRIPE,
                    'client_secret' => $intent->client_secret,
                    'publishable_key' => $publishable,
                ];
            }
        }

        $intent = $stripe->paymentIntents->create([
            'amount' => (int) round((float) $payment->amount * 100),
            'currency' => strtolower($payment->currency ?? 'EUR'),
            'metadata' => [
                'payment_id' => (string) $payment->id,
                'order_id' => (string) $payment->order_id,
            ],
            'automatic_payment_methods' => ['enabled' => true],
        ]);

        if (! $intent instanceof StripePaymentIntent) {
            throw new RuntimeException('Invalid Stripe PaymentIntent response.');
        }

        $payment->update([
            'gateway' => Payment::GATEWAY_STRIPE,
            'gateway_reference' => $intent->id,
            'status' => Payment::STATUS_REQUIRES_ACTION,
        ]);

        return [
            'gateway' => Payment::GATEWAY_STRIPE,
            'client_secret' => $intent->client_secret,
            'publishable_key' => $publishable,
        ];
    }
}
