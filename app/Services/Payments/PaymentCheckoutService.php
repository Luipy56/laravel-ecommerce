<?php

namespace App\Services\Payments;

use App\Contracts\Payments\PaymentCheckoutStarter;
use App\Models\Payment;
use App\Services\Payments\Redsys\RedsysCheckoutStarter;
use App\Services\Payments\Revolut\RevolutCheckoutStarter;
use App\Services\Payments\Stripe\StripeCheckoutStarter;
use InvalidArgumentException;
use RuntimeException;

class PaymentCheckoutService
{
    public function __construct(
        private readonly StripeCheckoutStarter $stripe,
        private readonly RedsysCheckoutStarter $redsys,
        private readonly RevolutCheckoutStarter $revolut,
    ) {}

    /** @return array{type: string}&array<string, mixed> */
    public function start(Payment $payment): array
    {
        $starter = $this->starterForPaymentMethod($payment->payment_method);

        return array_merge(['type' => $starter->gateway()], $starter->start($payment));
    }

    private function starterForPaymentMethod(string $method): PaymentCheckoutStarter
    {
        return match ($method) {
            Payment::METHOD_CARD, Payment::METHOD_PAYPAL => $this->stripe,
            Payment::METHOD_BIZUM => $this->redsys,
            Payment::METHOD_REVOLUT => $this->revolut,
            default => throw new InvalidArgumentException('Unsupported payment_method: '.$method),
        };
    }

    /** Used when STRIPE_* is missing in local dev: complete payment without PSP (never use in production). */
    public static function allowSimulatedPayments(): bool
    {
        return (bool) config('app.debug') && (bool) config('payments.allow_simulated');
    }

    public function simulateSuccess(Payment $payment): void
    {
        if (! self::allowSimulatedPayments()) {
            throw new RuntimeException('Simulated payments are disabled.');
        }
        $payment->update([
            'gateway' => 'simulated',
            'status' => Payment::STATUS_SUCCEEDED,
            'paid_at' => now(),
            'gateway_reference' => 'sim_'.$payment->id.'_'.uniqid(),
            'failure_code' => null,
            'failure_message' => null,
        ]);
    }
}
