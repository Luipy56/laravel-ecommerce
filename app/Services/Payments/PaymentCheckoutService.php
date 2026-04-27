<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentCheckoutStarter;
use App\Models\Payment;
use App\Services\Payments\PayPal\PayPalCheckoutStarter;
use App\Services\Payments\PayPal\PayPalClient;
use App\Services\Payments\Stripe\StripeCredentials;
use InvalidArgumentException;
use RuntimeException;

/**
 * Orchestrates starting a checkout session for a {@see Payment} using the configured gateway (Stripe or PayPal).
 *
 * Also exposes helpers for storefront configuration: which methods are available, simulated checkout in debug,
 * and credential hints for the UI.
 */
class PaymentCheckoutService
{
    public function __construct(
        private readonly PaymentCheckoutStarter $stripe,
        private readonly PayPalCheckoutStarter $paypal,
        private readonly PaymentCompletionService $completion,
    ) {}

    /**
     * Starts the remote checkout flow for the given payment and returns gateway-specific payload for the client.
     *
     * @param  Payment  $payment  Persisted payment row including payment_method (e.g. card, paypal).
     * @return array{type: string}&array<string, mixed> Gateway type plus fields from the selected starter (e.g. client_secret, approval URL).
     *
     * @throws InvalidArgumentException When payment_method is not supported by a registered starter.
     */
    public function start(Payment $payment): array
    {
        $starter = $this->starterForPaymentMethod($payment->payment_method);

        return array_merge(['type' => $starter->gateway()], $starter->start($payment));
    }

    private function starterForPaymentMethod(string $method): PaymentCheckoutStarter
    {
        return match ($method) {
            Payment::METHOD_CARD => $this->stripe,
            Payment::METHOD_PAYPAL => $this->paypal,
            default => throw new InvalidArgumentException('Unsupported payment_method: '.$method),
        };
    }

    /** Used when STRIPE_* is missing in local dev: complete payment without PSP (never use in production). */
    public static function allowSimulatedPayments(): bool
    {
        return (bool) config('app.debug') && (bool) config('payments.allow_simulated');
    }

    /** @return list<string> */
    public static function checkoutMethodKeysFromConfig(): array
    {
        $keys = config('payments.checkout_method_keys');

        return is_array($keys) ? $keys : ['card', 'paypal'];
    }

    /** True when the PSP for this method has real credentials (ignores simulated blanket availability). */
    public static function methodHasRealProviderCredentials(string $method): bool
    {
        return match ($method) {
            Payment::METHOD_CARD => StripeCredentials::areConfigured(),
            Payment::METHOD_PAYPAL => PayPalClient::envCredentialsPresent(),
            default => false,
        };
    }

    /** PayPal is allowed by PAYMENTS_CHECKOUT_METHODS but .env has no client id/secret (so it is hidden from the select). */
    public static function paypalMissingCredentialsForStorefront(): bool
    {
        if (! in_array(Payment::METHOD_PAYPAL, self::checkoutMethodKeysFromConfig(), true)) {
            return false;
        }

        return ! self::methodHasRealProviderCredentials(Payment::METHOD_PAYPAL);
    }

    /**
     * Normalised PayPal REST environment for public config (`PAYPAL_MODE` / services.paypal.mode).
     *
     * @return 'live'|'sandbox'
     */
    public static function paypalModeLabelForStorefront(): string
    {
        $mode = strtolower(trim((string) config('services.paypal.mode', 'sandbox')));

        return $mode === 'live' ? 'live' : 'sandbox';
    }

    /**
     * Card (Stripe) is whitelisted but STRIPE_KEY/STRIPE_SECRET are missing and simulated checkout is off.
     * When simulated checkout is on, missing Stripe keys still expose "card" as simulated — no hint needed.
     */
    public static function stripeMissingCredentialsForStorefront(): bool
    {
        if (! in_array(Payment::METHOD_CARD, self::checkoutMethodKeysFromConfig(), true)) {
            return false;
        }
        if (self::allowSimulatedPayments()) {
            return false;
        }

        return ! self::methodHasRealProviderCredentials(Payment::METHOD_CARD);
    }

    /**
     * When simulated mode is on, skip the PSP only if that method has no real credentials.
     * PayPal is never simulated: without credentials it stays unavailable; with credentials the SDK must run.
     */
    public static function shouldSimulateCheckoutForPayment(Payment $payment): bool
    {
        if ($payment->payment_method === Payment::METHOD_PAYPAL) {
            return false;
        }

        return self::allowSimulatedPayments()
            && ! self::methodHasRealProviderCredentials($payment->payment_method);
    }

    /**
     * @return array{card: bool, paypal: bool, simulated: bool}
     */
    private static function paymentMethodsBaseAvailability(): array
    {
        $simulated = self::allowSimulatedPayments();
        $stripeOk = $simulated || StripeCredentials::areConfigured();
        $paypalOk = PayPalClient::envCredentialsPresent();

        return [
            'card' => $stripeOk,
            'paypal' => $paypalOk,
            'simulated' => $simulated,
        ];
    }

    /**
     * @param  array{card: bool, paypal: bool, simulated: bool}  $base
     * @return array{card: bool, paypal: bool, simulated: bool}
     */
    private static function applyCheckoutMethodWhitelist(array $base): array
    {
        $allowed = self::checkoutMethodKeysFromConfig();
        foreach (['card', 'paypal'] as $k) {
            if (! in_array($k, $allowed, true)) {
                $base[$k] = false;
            }
        }

        return $base;
    }

    /**
     * Storefront + API: credential/simulated availability intersected with PAYMENTS_CHECKOUT_METHODS.
     *
     * @return array{card: bool, paypal: bool, simulated: bool}
     */
    public static function paymentMethodsAvailability(): array
    {
        return self::applyCheckoutMethodWhitelist(self::paymentMethodsBaseAvailability());
    }

    public static function isPaymentMethodAvailable(string $method): bool
    {
        $a = self::paymentMethodsAvailability();

        return match ($method) {
            Payment::METHOD_CARD => $a['card'],
            Payment::METHOD_PAYPAL => $a['paypal'],
            default => false,
        };
    }

    /**
     * Marks a payment as succeeded without calling an external PSP (local/debug only).
     *
     * @param  Payment  $payment  Payment to complete in simulated mode.
     *
     * @throws RuntimeException When simulated payments are not allowed by configuration.
     */
    public function simulateSuccess(Payment $payment): void
    {
        if (! self::allowSimulatedPayments()) {
            throw new RuntimeException('Simulated payments are disabled.');
        }
        $this->completion->markSucceeded($payment, [
            'gateway' => 'simulated',
            'gateway_reference' => 'sim_'.$payment->id.'_'.uniqid(),
        ]);
    }
}
