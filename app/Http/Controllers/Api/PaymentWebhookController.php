<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Payment;
use App\Services\Payments\PaymentCompletionService;
use App\Services\Payments\Redsys\RedsysSignature;
use Illuminate\Http\Request;
use Stripe\Event;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent as StripePaymentIntent;
use Stripe\Webhook;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class PaymentWebhookController extends Controller
{
    public function __construct(
        private readonly PaymentCompletionService $completion,
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
            'payment_intent.succeeded' => $this->handleStripeIntentSucceeded($event),
            'payment_intent.payment_failed' => $this->handleStripeIntentFailed($event),
            'payment_intent.canceled' => $this->handleStripeIntentCanceled($event),
            default => null,
        };

        return response('ok', 200);
    }

    private function handleStripeIntentSucceeded(Event $event): void
    {
        $intent = $event->data->object;
        if (! $intent instanceof StripePaymentIntent) {
            return;
        }
        $payment = $this->findStripePayment($intent);
        if ($payment) {
            $this->completion->markSucceeded($payment);
        }
    }

    private function handleStripeIntentFailed(Event $event): void
    {
        $intent = $event->data->object;
        if (! $intent instanceof StripePaymentIntent) {
            return;
        }
        $payment = $this->findStripePayment($intent);
        if ($payment) {
            $lastError = $intent->last_payment_error;
            $this->completion->markFailed(
                $payment,
                is_object($lastError) ? ($lastError->code ?? 'failed') : 'failed',
                is_object($lastError) ? ($lastError->message ?? null) : null,
            );
        }
    }

    private function handleStripeIntentCanceled(Event $event): void
    {
        $intent = $event->data->object;
        if (! $intent instanceof StripePaymentIntent) {
            return;
        }
        $payment = $this->findStripePayment($intent);
        if ($payment) {
            $this->completion->markCanceled($payment);
        }
    }

    private function findStripePayment(StripePaymentIntent $intent): ?Payment
    {
        $paymentId = $intent->metadata['payment_id'] ?? null;
        if ($paymentId !== null && $paymentId !== '') {
            $p = Payment::query()->find((int) $paymentId);
            if ($p && $p->gateway_reference === $intent->id) {
                return $p;
            }
        }

        return Payment::query()
            ->where('gateway', Payment::GATEWAY_STRIPE)
            ->where('gateway_reference', $intent->id)
            ->first();
    }

    /**
     * Redsys server-to-server notification (POST with Ds_* fields).
     */
    public function redsysNotify(Request $request): SymfonyResponse
    {
        $secretKey = config('services.redsys.secret_key');
        if (! is_string($secretKey) || $secretKey === '') {
            return response('Redsys not configured', 503);
        }

        $version = $request->input('Ds_SignatureVersion');
        $parameters = $request->input('Ds_MerchantParameters');
        $signature = $request->input('Ds_Signature');

        if (! is_string($parameters) || ! is_string($signature)) {
            return response('Bad request', 400);
        }

        if ($version !== 'HMAC_SHA256_V1') {
            return response('Unsupported signature version', 400);
        }

        if (! RedsysSignature::verifyResponse($parameters, $signature, $secretKey)) {
            return response('Invalid signature', 400);
        }

        $data = RedsysSignature::decodeMerchantParameters($parameters);
        $responseCode = $data['Ds_Response'] ?? $data['DS_RESPONSE'] ?? $data['ds_Response'] ?? null;
        $order = $data['Ds_Order'] ?? $data['DS_ORDER'] ?? $data['ds_Order'] ?? $data['DS_MERCHANT_ORDER'] ?? null;

        if (! is_string($order)) {
            return response('Missing order', 400);
        }

        $payment = Payment::query()
            ->where('gateway', Payment::GATEWAY_REDSYS)
            ->where('gateway_reference', $order)
            ->first();

        if (! $payment) {
            return response('Payment not found', 404);
        }

        if (is_string($responseCode) && strlen($responseCode) >= 4) {
            $codeNum = (int) substr($responseCode, 0, 4);
            if ($codeNum <= 99) {
                $this->completion->markSucceeded($payment);
            } else {
                $this->completion->markFailed($payment, $responseCode, 'Redsys declined');
            }
        }

        return response('OK', 200);
    }

    /**
     * Revolut Merchant webhook (simplified: JSON body with order_id and state).
     */
    public function revolut(Request $request): SymfonyResponse
    {
        $whSecret = config('services.revolut.webhook_secret');
        if (! is_string($whSecret) || $whSecret === '') {
            return response('Webhook not configured', 503);
        }

        $payload = $request->getContent();
        $sigHeader = $request->header('Revolut-Signature', '');
        $provided = null;
        if (preg_match('/v1=([^,]+)/', $sigHeader, $m)) {
            $provided = trim($m[1]);
        }
        $expected = hash_hmac('sha256', $payload, $whSecret);
        if ($provided === null || ! hash_equals($expected, $provided)) {
            return response('Invalid signature', 401);
        }

        $body = $request->json()->all();
        $orderId = $body['order_id'] ?? $body['id'] ?? null;
        $state = $body['state'] ?? $body['status'] ?? null;

        if (! is_string($orderId)) {
            return response('ok', 200);
        }

        $payment = Payment::query()
            ->where('gateway', Payment::GATEWAY_REVOLUT)
            ->where('gateway_reference', $orderId)
            ->first();

        if ($payment && ($state === 'completed' || $state === 'COMPLETED')) {
            $this->completion->markSucceeded($payment);
        }
        if ($payment && ($state === 'failed' || $state === 'FAILED' || $state === 'cancelled')) {
            $this->completion->markFailed($payment, (string) $state, null);
        }

        return response('ok', 200);
    }
}
