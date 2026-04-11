<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\PaymentCheckoutService;
use App\Services\Payments\PayPal\PayPalCheckoutStarter;
use App\Services\Payments\PayPal\PayPalClient;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class PayPalPaymentTest extends TestCase
{
    use RefreshDatabase;

    private const FAKE_PAYPAL_CLIENT_ID = 'AW_fake_test_client_id';

    private const FAKE_PAYPAL_SECRET = 'fake_test_secret';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
        config([
            'services.paypal.mode' => 'sandbox',
            'services.paypal.client_id' => self::FAKE_PAYPAL_CLIENT_ID,
            'services.paypal.secret' => self::FAKE_PAYPAL_SECRET,
            'app.debug' => false,
            'payments.allow_simulated' => false,
        ]);
    }

    private function makePayPalPaymentForClient(Client $client, string $paypalOrderId = 'PAYPAL_ORDER_TEST'): Payment
    {
        $order = Order::query()->create([
            'client_id' => $client->id,
            'kind' => Order::KIND_ORDER,
            'status' => Order::STATUS_PENDING,
            'order_date' => now(),
            'shipping_price' => Order::SHIPPING_FLAT_EUR,
            'installation_requested' => false,
        ]);

        return Payment::query()->create([
            'order_id' => $order->id,
            'amount' => 10.00,
            'payment_method' => Payment::METHOD_PAYPAL,
            'status' => Payment::STATUS_REQUIRES_ACTION,
            'gateway' => Payment::GATEWAY_PAYPAL,
            'currency' => 'EUR',
            'gateway_reference' => $paypalOrderId,
        ]);
    }

    private function makeClient(): Client
    {
        return Client::query()->create([
            'type' => 'person',
            'identification' => null,
            'login_email' => 'paypal_buyer_'.uniqid('', true).'@example.test',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);
    }

    public function test_paypal_unavailable_without_credentials(): void
    {
        config([
            'services.paypal.client_id' => '',
            'services.paypal.secret' => '',
        ]);

        $a = PaymentCheckoutService::paymentMethodsAvailability();
        $this->assertFalse($a['paypal']);
        $this->assertFalse(PaymentCheckoutService::isPaymentMethodAvailable(Payment::METHOD_PAYPAL));
    }

    public function test_paypal_available_when_any_non_empty_credentials(): void
    {
        config([
            'services.paypal.client_id' => 'short_id',
            'services.paypal.secret' => 'short_secret',
        ]);
        $this->assertTrue(PaymentCheckoutService::isPaymentMethodAvailable(Payment::METHOD_PAYPAL));
    }

    public function test_paypal_available_without_stripe_when_configured(): void
    {
        config([
            'services.stripe.key' => '',
            'services.stripe.secret' => '',
            'services.paypal.client_id' => self::FAKE_PAYPAL_CLIENT_ID,
            'services.paypal.secret' => self::FAKE_PAYPAL_SECRET,
        ]);

        $a = PaymentCheckoutService::paymentMethodsAvailability();
        $this->assertTrue($a['paypal']);
        $this->assertFalse($a['card']);
        $this->assertArrayNotHasKey('revolut', $a);
        $this->assertTrue(PaymentCheckoutService::isPaymentMethodAvailable(Payment::METHOD_PAYPAL));
        $this->assertFalse(PaymentCheckoutService::isPaymentMethodAvailable(Payment::METHOD_CARD));
    }

    public function test_paypal_checkout_starter_creates_order_and_updates_payment(): void
    {
        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'fake_access',
                'expires_in' => 3600,
            ], 200),
            'api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response([
                'id' => 'NEW_ORDER_ID',
                'status' => 'CREATED',
                'links' => [
                    [
                        'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=NEW_ORDER_ID',
                        'rel' => 'approve',
                        'method' => 'GET',
                    ],
                ],
            ], 201),
        ]);

        $client = $this->makeClient();
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
            'amount' => 25.50,
            'payment_method' => Payment::METHOD_PAYPAL,
            'status' => Payment::STATUS_PENDING,
            'currency' => 'EUR',
        ]);

        $starter = new PayPalCheckoutStarter(new PayPalClient);
        $payload = $starter->start($payment);

        $payment->refresh();
        $this->assertSame(Payment::GATEWAY_PAYPAL, $payment->gateway);
        $this->assertSame('NEW_ORDER_ID', $payment->gateway_reference);
        $this->assertSame(Payment::STATUS_REQUIRES_ACTION, $payment->status);
        $this->assertSame('paypal', $payload['gateway']);
        $this->assertSame('NEW_ORDER_ID', $payload['paypal_order_id']);
        $this->assertSame((int) $payment->id, $payload['payment_id']);
        $this->assertSame(self::FAKE_PAYPAL_CLIENT_ID, $payload['client_id']);
        $this->assertSame('https://www.sandbox.paypal.com/checkoutnow?token=NEW_ORDER_ID', $payload['approval_url']);
    }

    public function test_paypal_checkout_starter_reuses_created_order(): void
    {
        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'fake_access',
                'expires_in' => 3600,
            ], 200),
            'api-m.sandbox.paypal.com/v2/checkout/orders/EXISTING_ID' => Http::response([
                'id' => 'EXISTING_ID',
                'status' => 'CREATED',
                'links' => [
                    [
                        'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=EXISTING_ID',
                        'rel' => 'approve',
                        'method' => 'GET',
                    ],
                ],
            ], 200),
        ]);

        $client = $this->makeClient();
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
            'amount' => 10.00,
            'payment_method' => Payment::METHOD_PAYPAL,
            'status' => Payment::STATUS_REQUIRES_ACTION,
            'gateway' => Payment::GATEWAY_PAYPAL,
            'currency' => 'EUR',
            'gateway_reference' => 'EXISTING_ID',
        ]);

        $starter = new PayPalCheckoutStarter(new PayPalClient);
        $payload = $starter->start($payment);

        $this->assertSame('EXISTING_ID', $payload['paypal_order_id']);
        Http::assertNotSent(function ($request) {
            if ($request->method() !== 'POST') {
                return false;
            }
            $url = $request->url();

            return str_contains($url, '/v2/checkout/orders') && ! str_contains($url, '/capture');
        });
    }

    public function test_paypal_capture_marks_payment_succeeded(): void
    {
        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'fake_access',
                'expires_in' => 3600,
            ], 200),
            'api-m.sandbox.paypal.com/v2/checkout/orders/PAYPAL_ORDER_TEST/capture' => Http::response([
                'id' => 'PAYPAL_ORDER_TEST',
                'status' => 'COMPLETED',
            ], 201),
        ]);

        $client = $this->makeClient();
        $payment = $this->makePayPalPaymentForClient($client);

        $this->actingAs($client, 'web');
        $response = $this->postJson('/api/v1/payments/paypal/capture', [
            'paypal_order_id' => 'PAYPAL_ORDER_TEST',
            'payment_id' => $payment->id,
        ]);

        $response->assertOk()->assertJson(['success' => true]);
        $this->assertTrue($payment->fresh()->isSuccessful());

        // Regression: capture must POST an empty JSON object (stdClass), not only arrays; array-only typing on the client breaks this path.
        Http::assertSent(function ($request) {
            if ($request->method() !== 'POST') {
                return false;
            }

            return str_contains($request->url(), '/v2/checkout/orders/PAYPAL_ORDER_TEST/capture')
                && $request->body() === '{}';
        });
    }

    public function test_paypal_capture_returns_success_when_already_paid(): void
    {
        $client = $this->makeClient();
        $payment = $this->makePayPalPaymentForClient($client);
        $payment->update([
            'status' => Payment::STATUS_SUCCEEDED,
            'paid_at' => now(),
        ]);

        $this->actingAs($client, 'web');
        $response = $this->postJson('/api/v1/payments/paypal/capture', [
            'paypal_order_id' => 'PAYPAL_ORDER_TEST',
            'payment_id' => $payment->id,
        ]);

        $response->assertOk()->assertJsonPath('data.already_paid', true);
        Http::assertNothingSent();
    }

    public function test_paypal_capture_forbidden_for_other_clients_payment(): void
    {
        $owner = $this->makeClient();
        $other = $this->makeClient();
        $payment = $this->makePayPalPaymentForClient($owner);

        $this->actingAs($other, 'web');
        $response = $this->postJson('/api/v1/payments/paypal/capture', [
            'paypal_order_id' => 'PAYPAL_ORDER_TEST',
            'payment_id' => $payment->id,
        ]);

        $response->assertStatus(403);
    }

    public function test_paypal_capture_rejects_mismatched_paypal_order_id(): void
    {
        $client = $this->makeClient();
        $payment = $this->makePayPalPaymentForClient($client);

        $this->actingAs($client, 'web');
        $response = $this->postJson('/api/v1/payments/paypal/capture', [
            'paypal_order_id' => 'WRONG_ID',
            'payment_id' => $payment->id,
        ]);

        $response->assertStatus(422);
    }

    public function test_paypal_test_credentials_command_fails_when_unconfigured(): void
    {
        config([
            'services.paypal.client_id' => '',
            'services.paypal.secret' => '',
        ]);

        $this->artisan('paypal:test-credentials')->assertFailed();
    }

    public function test_paypal_test_credentials_command_succeeds_with_fake_http(): void
    {
        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'fake_access',
                'expires_in' => 3600,
            ], 200),
        ]);

        $this->artisan('paypal:test-credentials')->assertSuccessful();
    }
}
