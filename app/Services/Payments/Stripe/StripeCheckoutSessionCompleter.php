<?php

namespace App\Services\Payments\Stripe;

use App\Models\Payment;
use App\Services\Payments\PaymentCompletionService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Checkout\Session as StripeCheckoutSession;

/**
 * Completes a {@see Payment} when a Stripe Checkout Session is paid (webhook or client return confirm).
 */
class StripeCheckoutSessionCompleter
{
    public function __construct(
        private readonly PaymentCompletionService $completion,
    ) {}

    /**
     * Marks the linked payment succeeded when the session is paid and amounts match.
     *
     * @return Payment|null The payment row after completion attempt (refresh), or null if nothing to do.
     */
    public function completePaidCheckoutSession(StripeCheckoutSession $session): ?Payment
    {
        if (($session->payment_status ?? '') !== 'paid') {
            return null;
        }

        $payment = $this->findPaymentForStripeSession($session);
        if (! $payment) {
            Log::warning('stripe.checkout.session_payment_not_found', [
                'session_id' => $session->id ?? null,
            ]);

            return null;
        }

        $expectedCents = (int) round((float) $payment->amount * 100);
        $total = (int) ($session->amount_total ?? 0);
        if ($total > 0 && $total !== $expectedCents) {
            Log::warning('stripe.checkout.session_amount_mismatch', [
                'payment_id' => $payment->id,
                'expected_cents' => $expectedCents,
                'session_total_cents' => $total,
            ]);

            return null;
        }

        DB::transaction(function () use ($payment, $session): void {
            $this->completion->markSucceeded($payment);
            $payment->refresh();
            $pi = $session->payment_intent;
            if (is_string($pi) && $pi !== '') {
                $meta = $payment->metadata ?? [];
                $meta['stripe_payment_intent_id'] = $pi;
                $payment->update(['metadata' => $meta]);
            }
        });

        return $payment->fresh();
    }

    private function findPaymentForStripeSession(StripeCheckoutSession $session): ?Payment
    {
        $paymentId = $session->metadata['payment_id'] ?? null;
        if (is_string($paymentId) && $paymentId !== '') {
            $p = Payment::query()->find((int) $paymentId);
            if ($p && $p->gateway === Payment::GATEWAY_STRIPE && $p->gateway_reference === $session->id) {
                return $p;
            }
        }

        return Payment::query()
            ->where('gateway', Payment::GATEWAY_STRIPE)
            ->where('gateway_reference', $session->id)
            ->first();
    }
}
