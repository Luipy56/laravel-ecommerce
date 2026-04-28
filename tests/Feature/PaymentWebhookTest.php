<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class PaymentWebhookTest extends TestCase
{
    use RefreshDatabase;

    private function makeStripeSignedPayload(string $payload, string $secret): string
    {
        $timestamp = time();
        $signedPayload = $timestamp.'.'.$payload;
        $signature = hash_hmac('sha256', $signedPayload, $secret);

        return 't='.$timestamp.',v1='.$signature;
    }

    /**
     * Raw JSON body (required for Stripe signature verification). Laravel has no TestCase::withBody().
     */
    private function postRawWebhook(string $uri, string $rawBody, array $headers = []): TestResponse
    {
        $server = [
            'CONTENT_TYPE' => 'application/json',
            'HTTP_ACCEPT' => 'application/json',
        ];
        foreach ($headers as $name => $value) {
            $server['HTTP_'.strtoupper(str_replace('-', '_', $name))] = $value;
        }

        return $this->call('POST', $uri, [], [], [], $server, $rawBody);
    }

    public function test_stripe_webhook_returns_503_when_secret_not_configured(): void
    {
        config(['services.stripe.webhook_secret' => '']);

        $response = $this->postJson('/api/v1/payments/webhooks/stripe', []);

        $response->assertStatus(503);
    }

    public function test_stripe_webhook_rejects_invalid_signature(): void
    {
        config(['services.stripe.webhook_secret' => 'test_stripe_wh_secret']);

        $payload = '{"id":"evt_1","type":"payment_intent.succeeded","data":{"object":{"id":"pi_x","object":"payment_intent"}}}';

        $response = $this->postRawWebhook(
            '/api/v1/payments/webhooks/stripe',
            $payload,
            ['Stripe-Signature' => 't='.time().',v1=deadbeef']
        );

        $response->assertStatus(400);
    }

    public function test_stripe_webhook_marks_payment_succeeded_idempotently(): void
    {
        $secret = 'test_stripe_wh_secret';
        config(['services.stripe.webhook_secret' => $secret]);

        $client = Client::query()->create([
            'type' => 'person',
            'identification' => null,
            'login_email' => 'buyer@example.test',
            'password' => bcrypt('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $order = Order::query()->create([
            'client_id' => $client->id,
            'kind' => Order::KIND_ORDER,
            'status' => Order::STATUS_PENDING,
            'order_date' => now(),
            'shipping_price' => Order::SHIPPING_FLAT_EUR,
            'installation_requested' => false,
        ]);

        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'amount' => 12.34,
            'payment_method' => Payment::METHOD_CARD,
            'status' => Payment::STATUS_REQUIRES_ACTION,
            'gateway' => Payment::GATEWAY_STRIPE,
            'currency' => 'EUR',
            'gateway_reference' => 'pi_test_webhook_1',
        ]);

        $payload = json_encode([
            'id' => 'evt_test_webhook_1',
            'object' => 'event',
            'api_version' => '2024-11-20.acacia',
            'created' => time(),
            'data' => [
                'object' => [
                    'id' => 'pi_test_webhook_1',
                    'object' => 'payment_intent',
                    'amount' => 1234,
                    'metadata' => [
                        'payment_id' => (string) $payment->id,
                    ],
                ],
            ],
            'livemode' => false,
            'pending_webhooks' => 0,
            'request' => ['id' => null, 'idempotency_key' => null],
            'type' => 'payment_intent.succeeded',
        ], JSON_UNESCAPED_SLASHES);

        $header = $this->makeStripeSignedPayload($payload, $secret);

        $this->postRawWebhook('/api/v1/payments/webhooks/stripe', $payload, ['Stripe-Signature' => $header])
            ->assertOk();

        $payment->refresh();
        $this->assertSame(Payment::STATUS_SUCCEEDED, $payment->status);
        $this->assertNotNull($payment->paid_at);
        $this->assertTrue($order->fresh()->hasSuccessfulPayment());

        $this->postRawWebhook('/api/v1/payments/webhooks/stripe', $payload, ['Stripe-Signature' => $header])
            ->assertOk();

        $this->assertSame(1, Payment::query()->where('order_id', $order->id)->where('status', Payment::STATUS_SUCCEEDED)->count());
        $this->assertSame(1, \App\Models\StripeWebhookEvent::query()->where('stripe_event_id', 'evt_test_webhook_1')->count());
    }

    public function test_stripe_checkout_session_completed_marks_payment_succeeded(): void
    {
        $secret = 'test_stripe_wh_secret_cs';
        config(['services.stripe.webhook_secret' => $secret]);

        $client = Client::query()->create([
            'type' => 'person',
            'identification' => null,
            'login_email' => 'buyer_cs@example.test',
            'password' => bcrypt('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $order = Order::query()->create([
            'client_id' => $client->id,
            'kind' => Order::KIND_ORDER,
            'status' => Order::STATUS_PENDING,
            'order_date' => now(),
            'shipping_price' => Order::SHIPPING_FLAT_EUR,
            'installation_requested' => false,
        ]);

        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'amount' => 99.50,
            'payment_method' => Payment::METHOD_CARD,
            'status' => Payment::STATUS_REQUIRES_ACTION,
            'gateway' => Payment::GATEWAY_STRIPE,
            'currency' => 'EUR',
            'gateway_reference' => 'cs_test_session_1',
        ]);

        $payload = json_encode([
            'id' => 'evt_cs_completed_1',
            'object' => 'event',
            'api_version' => '2024-11-20.acacia',
            'created' => time(),
            'data' => [
                'object' => [
                    'id' => 'cs_test_session_1',
                    'object' => 'checkout.session',
                    'amount_total' => 9950,
                    'currency' => 'eur',
                    'payment_status' => 'paid',
                    'metadata' => [
                        'payment_id' => (string) $payment->id,
                        'order_id' => (string) $order->id,
                    ],
                    'payment_intent' => 'pi_from_cs_1',
                ],
            ],
            'livemode' => false,
            'pending_webhooks' => 0,
            'type' => 'checkout.session.completed',
        ], JSON_UNESCAPED_SLASHES);

        $header = $this->makeStripeSignedPayload($payload, $secret);

        $this->postRawWebhook('/api/v1/payments/webhooks/stripe', $payload, ['Stripe-Signature' => $header])
            ->assertOk();

        $payment->refresh();
        $this->assertSame(Payment::STATUS_SUCCEEDED, $payment->status);
        $this->assertSame('pi_from_cs_1', $payment->metadata['stripe_payment_intent_id'] ?? null);
        $this->assertTrue($order->fresh()->hasSuccessfulPayment());
    }

    public function test_pending_payment_order_has_no_successful_payment_until_webhook(): void
    {
        $client = Client::query()->create([
            'type' => 'person',
            'identification' => null,
            'login_email' => 'buyer4@example.test',
            'password' => bcrypt('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $order = Order::query()->create([
            'client_id' => $client->id,
            'kind' => Order::KIND_ORDER,
            'status' => Order::STATUS_PENDING,
            'order_date' => now(),
            'shipping_price' => Order::SHIPPING_FLAT_EUR,
            'installation_requested' => false,
        ]);

        Payment::query()->create([
            'order_id' => $order->id,
            'amount' => 10.00,
            'payment_method' => Payment::METHOD_CARD,
            'status' => Payment::STATUS_PENDING,
            'gateway' => null,
            'currency' => 'EUR',
            'gateway_reference' => null,
        ]);

        $this->assertFalse($order->fresh()->hasSuccessfulPayment());
    }
}
