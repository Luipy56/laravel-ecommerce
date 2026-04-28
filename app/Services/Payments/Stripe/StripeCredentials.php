<?php

namespace App\Services\Payments\Stripe;

/**
 * Same rules as StripeCheckoutStarter: non-empty string keys only.
 */
final class StripeCredentials
{
    public static function areConfigured(): bool
    {
        $secret = config('services.stripe.secret');
        $key = config('services.stripe.key');

        return is_string($secret) && $secret !== ''
            && is_string($key) && $key !== '';
    }
}
