<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Models\StripeWebhookEvent;
use App\Services\Payments\PaymentCompletionService;
use App\Services\Payments\Stripe\StripeCheckoutSessionCompleter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Stripe\Charge;
use Stripe\Checkout\Session as StripeCheckoutSession;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent as StripePaymentIntent;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class PaymentWebhookController extends Controller
{
    public function __construct(
        private readonly PaymentCompletionService $completion,
        private readonly StripeCheckoutSessionCompleter $stripeCheckoutSessionCompleter,
    ) {}

    public function stripe(Request $request): SymfonyResponse
    {
        $secret = config('services.stripe.webhook_secret');
        if (! is_string($secret) || $secret === '') {
            return response('Webhook not configured', 503);
        }

        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature', '');

        try {
            $event = Webhook::constructEvent($payload, $sigHeader, $secret);
        } catch (\UnexpectedValueException|SignatureVerificationException) {
            return response('Invalid payload', 400);
        }

        if (! $event instanceof Event) {
            return response('Invalid event', 400);
        }

        match ($event->type) {
            'checkout.session.completed' => $this->handleCheckoutSessionCompleted($event),
            'payment_intent.succeeded' => $this->handleStripeIntentSucceeded($event),
            'payment_intent.payment_failed' => $this->handleStripeIntentFailed($event),
            'payment_intent.canceled' => $this->handleStripeIntentCanceled($event),
            'charge.refunded' => $this->handleChargeRefunded($event),
            default => null,
        };

        return response('ok', 200);
    }

    private function alreadyProcessedStripeEvent(string $eventId): bool
    {
        return $eventId !== '' && StripeWebhookEvent::query()->where('stripe_event_id', $eventId)->exists();
    }

    private function recordStripeEventProcessed(string $eventId): void
    {
        if ($eventId === '') {
            return;
        }
        try {
            StripeWebhookEvent::query()->create(['stripe_event_id' => $eventId]);
        } catch (\Illuminate\Database\QueryException) {
            // Concurrent duplicate delivery; safe to ignore.
        }
    }

    private function handleCheckoutSessionCompleted(Event $event): void
    {
        if ($this->alreadyProcessedStripeEvent($event->id)) {
            return;
        }

        $session = $event->data->object;
        if (! $session instanceof StripeCheckoutSession) {
            Log::info('stripe.webhook.unexpected_session_shape', ['event_id' => $event->id]);

            return;
        }

        $payment = $this->stripeCheckoutSessionCompleter->completePaidCheckoutSession($session);
        if ($payment === null) {
            return;
        }

        $this->recordStripeEventProcessed($event->id);

        Log::info('stripe.webhook.checkout_session_completed', [
            'event_id' => $event->id,
            'payment_id' => $payment->id,
            'order_id' => $payment->order_id,
        ]);
    }

    private function handleStripeIntentSucceeded(Event $event): void
    {
        if ($this->alreadyProcessedStripeEvent($event->id)) {
            return;
        }

        $intent = $event->data->object;
        if (! $intent instanceof StripePaymentIntent) {
            return;
        }
        $payment = $this->findStripePayment($intent);
        if ($payment) {
            DB::transaction(function () use ($payment, $event): void {
                $this->completion->markSucceeded($payment);
                $this->recordStripeEventProcessed($event->id);
            });
            Log::info('stripe.webhook.payment_intent_succeeded', [
                'event_id' => $event->id,
                'payment_id' => $payment->id,
            ]);
        }
    }

    private function handleStripeIntentFailed(Event $event): void
    {
        if ($this->alreadyProcessedStripeEvent($event->id)) {
            return;
        }

        $intent = $event->data->object;
        if (! $intent instanceof StripePaymentIntent) {
            return;
        }
        $payment = $this->findStripePayment($intent);
        if ($payment) {
            $lastError = $intent->last_payment_error;
            DB::transaction(function () use ($payment, $intent, $event, $lastError): void {
                $this->completion->markFailed(
                    $payment,
                    is_object($lastError) ? ($lastError->code ?? 'failed') : 'failed',
                    is_object($lastError) ? ($lastError->message ?? null) : null,
                );
                $this->recordStripeEventProcessed($event->id);
            });
        }
    }

    private function handleStripeIntentCanceled(Event $event): void
    {
        if ($this->alreadyProcessedStripeEvent($event->id)) {
            return;
        }

        $intent = $event->data->object;
        if (! $intent instanceof StripePaymentIntent) {
            return;
        }
        $payment = $this->findStripePayment($intent);
        if ($payment) {
            DB::transaction(function () use ($payment, $event): void {
                $this->completion->markCanceled($payment);
                $this->recordStripeEventProcessed($event->id);
            });
        }
    }

    private function handleChargeRefunded(Event $event): void
    {
        if ($this->alreadyProcessedStripeEvent($event->id)) {
            return;
        }

        $charge = $event->data->object;
        if (! $charge instanceof Charge) {
            return;
        }

        $intentId = is_string($charge->payment_intent) ? $charge->payment_intent : null;
        if ($intentId === null || $intentId === '') {
            return;
        }

        $payment = Payment::query()
            ->where('gateway', Payment::GATEWAY_STRIPE)
            ->where(function ($q) use ($intentId) {
                $q->where('gateway_reference', $intentId)
                    ->orWhere('metadata->stripe_payment_intent_id', $intentId);
            })
            ->first();

        if ($payment) {
            DB::transaction(function () use ($payment, $event): void {
                $this->completion->markRefunded($payment);
                $this->recordStripeEventProcessed($event->id);
            });
            Log::info('stripe.webhook.charge_refunded', [
                'event_id' => $event->id,
                'payment_id' => $payment->id,
            ]);
        }
    }

    private function findStripePayment(StripePaymentIntent $intent): ?Payment
    {
        $paymentId = $intent->metadata['payment_id'] ?? null;
        if ($paymentId !== null && $paymentId !== '') {
            $p = Payment::query()->find((int) $paymentId);
            if ($p && $p->gateway === Payment::GATEWAY_STRIPE) {
                if ($p->gateway_reference === $intent->id) {
                    return $p;
                }
                if (is_string($p->gateway_reference) && str_starts_with($p->gateway_reference, 'cs_')) {
                    return $p;
                }
            }
        }

        return Payment::query()
            ->where('gateway', Payment::GATEWAY_STRIPE)
            ->where('gateway_reference', $intent->id)
            ->first();
    }
}
