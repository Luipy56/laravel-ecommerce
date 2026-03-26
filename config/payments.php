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
    | Comma-separated payment_method keys exposed on the storefront and accepted by checkout/pay.
    | Valid: card, paypal, bizum, revolut. Example: PAYMENTS_CHECKOUT_METHODS=paypal
    | Invalid tokens are ignored; if the list is empty after parsing, all four are allowed.
    */
    'checkout_method_keys' => (function () {
        $valid = ['card', 'paypal', 'bizum', 'revolut'];
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

];
