<?php

return [

    /*
    | When true with APP_DEBUG, checkout/pay may complete without a real PSP (tests / local only).
    | Must be false in production (use APP_DEBUG=false; do not rely on this flag alone).
    |
    | Default: if PAYMENTS_ALLOW_SIMULATED is omitted from .env, it is true only when APP_ENV=local,
    | so `php artisan serve` checkout works without Stripe/PayPal keys. Set PAYMENTS_ALLOW_SIMULATED=false
    | in .env to require real PSP credentials on your machine. PHPUnit uses APP_ENV=testing → default false.
    */
    'allow_simulated' => (function () {
        $explicit = env('PAYMENTS_ALLOW_SIMULATED');
        if ($explicit !== null && $explicit !== '') {
            return filter_var($explicit, FILTER_VALIDATE_BOOLEAN);
        }

        return env('APP_ENV', 'production') === 'local';
    })(),

    /*
    | When true, checkout may accept checkout_demo_skip_payment=true and complete the order without a PSP.
    | Temporary demo / QA only — keep false in production unless you intentionally run a throwaway demo stack.
    */
    'checkout_demo_skip_payment' => filter_var(env('CHECKOUT_DEMO_SKIP_PAYMENT', false), FILTER_VALIDATE_BOOLEAN),

    /*
    | Comma-separated payment_method keys exposed on the storefront and accepted by checkout/pay.
    | Valid: card (Stripe Checkout: cards, wallets, Bizum when enabled in Stripe), paypal.
    |
    | Omitted or empty string in .env → both card and paypal are allowed (then filtered by credentials).
    | To expose Stripe (card) together with PayPal, use e.g. PAYMENTS_CHECKOUT_METHODS=card,paypal or leave empty.
    | If you set PAYMENTS_CHECKOUT_METHODS=paypal only, methods.card stays false in the API even when STRIPE_* are set.
    |
    | Invalid tokens are ignored; if the list is empty after parsing, card and paypal are allowed.
    */
    'checkout_method_keys' => (function () {
        $valid = ['card', 'paypal'];
        $raw = env('PAYMENTS_CHECKOUT_METHODS');
        if ($raw === null || trim((string) $raw) === '') {
            return $valid;
        }
        $out = [];
        foreach (explode(',', (string) $raw) as $part) {
            $k = trim($part);
            if ($k !== '' && in_array($k, $valid, true)) {
                $out[] = $k;
            }
        }

        return $out !== [] ? array_values(array_unique($out)) : $valid;
    })(),

    /*
    | Stripe Checkout Session payment_method_types (e.g. card, bizum for Spain). Comma-separated in .env:
    | STRIPE_CHECKOUT_PAYMENT_METHOD_TYPES=card,bizum
    */
    'stripe_checkout_payment_method_types' => (function () {
        $raw = env('STRIPE_CHECKOUT_PAYMENT_METHOD_TYPES', 'card,bizum');
        $out = [];
        foreach (explode(',', (string) $raw) as $part) {
            $k = trim($part);
            if ($k !== '') {
                $out[] = $k;
            }
        }

        return $out !== [] ? array_values(array_unique($out)) : ['card', 'bizum'];
    })(),

];
