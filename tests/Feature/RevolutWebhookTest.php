<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class RevolutWebhookTest extends TestCase
{
    use RefreshDatabase;

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

    public function test_revolut_webhook_returns_503_when_secret_not_configured(): void
    {
        config(['services.revolut.webhook_secret' => '']);

        $response = $this->postJson('/api/v1/payments/webhooks/revolut', []);

        $response->assertStatus(503);
    }

    public function test_revolut_webhook_rejects_invalid_signature(): void
    {
        config(['services.revolut.webhook_secret' => 'wsk_test']);
        $timestamp = (string) (int) floor(microtime(true) * 1000);
        $payload = '{"event":"ORDER_COMPLETED","order_id":"ord_x"}';

        $response = $this->postRawWebhook(
            '/api/v1/payments/webhooks/revolut',
            $payload,
            [
                'Revolut-Request-Timestamp' => $timestamp,
                'Revolut-Signature' => 'v1=deadbeef',
            ]
        );

        $response->assertStatus(400);
    }

    public function test_revolut_webhook_marks_payment_succeeded(): void
    {
        $secret = 'wsk_test_secret';
        config([
            'services.revolut.webhook_secret' => $secret,
            'services.revolut.api_key' => 'sk_test_unused_for_local_match',
        ]);

        $client = Client::query()->create([
            'type' => 'person',
            'identification' => null,
            'login_email' => 'buyer_revolut@ietf.org',
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
            'payment_method' => Payment::METHOD_REVOLUT,
            'status' => Payment::STATUS_REQUIRES_ACTION,
            'gateway' => Payment::GATEWAY_REVOLUT,
            'currency' => 'EUR',
            'gateway_reference' => '6634c172-3398-ac93-aee9-50de0282e3ac',
        ]);

        $payload = json_encode([
            'event' => 'ORDER_COMPLETED',
            'order_id' => '6634c172-3398-ac93-aee9-50de0282e3ac',
            'merchant_order_ext_ref' => 'payment_'.$payment->id,
        ], JSON_THROW_ON_ERROR);

        $timestamp = (string) (int) floor(microtime(true) * 1000);
        $sig = 'v1='.hash_hmac('sha256', 'v1.'.$timestamp.'.'.$payload, $secret);

        $response = $this->postRawWebhook(
            '/api/v1/payments/webhooks/revolut',
            $payload,
            [
                'Revolut-Request-Timestamp' => $timestamp,
                'Revolut-Signature' => $sig,
            ]
        );

        $response->assertNoContent();
        $payment->refresh();
        $this->assertSame(Payment::STATUS_SUCCEEDED, $payment->status);
        $this->assertNotNull($payment->paid_at);
    }
}
