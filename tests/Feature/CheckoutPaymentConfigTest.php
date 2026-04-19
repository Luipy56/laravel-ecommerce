<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\Payments\PayPal\PayPalClient;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class CheckoutPaymentConfigTest extends TestCase
{
    use RefreshDatabase;

    private const FAKE_PAYPAL_CLIENT_ID = 'AW_fake_test_client_id';

    private const FAKE_PAYPAL_SECRET = 'fake_test_secret';

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    private function makeClientWithCart(): Client
    {
        $client = Client::query()->create([
            'type' => 'person',
            'identification' => null,
            'login_email' => 'buyer_'.uniqid('', true).'@example.test',
            'password' => bcrypt('password'),
            'is_active' => true,
        ]);

        $category = ProductCategory::query()->create([
            'code' => 'cat_'.uniqid(),
            'name' => 'Category',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'code' => 'p_'.uniqid(),
            'name' => 'Product',
            'price' => 25.00,
            'stock' => 10,
            'is_active' => true,
        ]);

        $cart = Order::query()->create([
            'client_id' => $client->id,
            'kind' => Order::KIND_CART,
            'status' => null,
            'installation_requested' => false,
        ]);

        OrderLine::query()->create([
            'order_id' => $cart->id,
            'product_id' => $product->id,
            'pack_id' => null,
            'quantity' => 1,
            'unit_price' => 25.00,
            'offer' => null,
            'keys_all_same' => false,
            'extra_keys_qty' => 0,
            'extra_key_unit_price' => null,
            'is_included' => true,
        ]);

        return $client;
    }

    public function test_paypal_client_env_credentials_look_valid_matches_present(): void
    {
        config([
            'services.paypal.client_id' => self::FAKE_PAYPAL_CLIENT_ID,
            'services.paypal.secret' => self::FAKE_PAYPAL_SECRET,
        ]);
        $this->assertSame(
            PayPalClient::envCredentialsPresent(),
            PayPalClient::envCredentialsLookValid(),
        );
        $this->assertTrue(PayPalClient::envCredentialsLookValid());

        config([
            'services.paypal.client_id' => '',
            'services.paypal.secret' => self::FAKE_PAYPAL_SECRET,
        ]);
        $this->assertFalse(PayPalClient::envCredentialsLookValid());
    }

    public function test_payments_config_exposes_card_and_paypal_when_both_configured_and_whitelisted(): void
    {
        config([
            'payments.checkout_method_keys' => ['card', 'paypal'],
            'payments.allow_simulated' => false,
            'app.debug' => true,
            'services.stripe.key' => 'pk_test_xxx',
            'services.stripe.secret' => 'sk_test_xxx',
            'services.paypal.client_id' => self::FAKE_PAYPAL_CLIENT_ID,
            'services.paypal.secret' => self::FAKE_PAYPAL_SECRET,
        ]);

        $response = $this->getJson('/api/v1/payments/config');
        $response->assertOk();
        $response->assertJsonPath('data.methods.card', true);
        $response->assertJsonPath('data.methods.paypal', true);
        $response->assertJsonPath('data.simulated', false);
        $response->assertJsonPath('data.paypal_missing_credentials', false);
        $response->assertJsonPath('data.stripe_missing_credentials', false);
        $response->assertJsonPath('data.paypal_mode', 'sandbox');
    }

    public function test_payments_config_respects_checkout_method_whitelist(): void
    {
        config([
            'payments.checkout_method_keys' => ['paypal'],
            'app.debug' => false,
            'payments.allow_simulated' => false,
            'services.stripe.key' => 'pk',
            'services.stripe.secret' => 'sk',
            'services.paypal.client_id' => self::FAKE_PAYPAL_CLIENT_ID,
            'services.paypal.secret' => self::FAKE_PAYPAL_SECRET,
        ]);

        $response = $this->getJson('/api/v1/payments/config');
        $response->assertOk();
        $response->assertJsonPath('data.methods.card', false);
        $response->assertJsonPath('data.methods.paypal', true);
        $response->assertJsonPath('data.paypal_mode', 'sandbox');
    }

    public function test_payments_config_exposes_paypal_mode_live(): void
    {
        config([
            'payments.checkout_method_keys' => ['paypal'],
            'services.paypal.client_id' => self::FAKE_PAYPAL_CLIENT_ID,
            'services.paypal.secret' => self::FAKE_PAYPAL_SECRET,
            'services.paypal.mode' => 'live',
        ]);

        $response = $this->getJson('/api/v1/payments/config');
        $response->assertOk();
        $response->assertJsonPath('data.paypal_mode', 'live');
    }

    public function test_payments_config_normalizes_unknown_paypal_mode_to_sandbox(): void
    {
        config([
            'payments.checkout_method_keys' => ['paypal'],
            'services.paypal.client_id' => self::FAKE_PAYPAL_CLIENT_ID,
            'services.paypal.secret' => self::FAKE_PAYPAL_SECRET,
            'services.paypal.mode' => 'staging',
        ]);

        $response = $this->getJson('/api/v1/payments/config');
        $response->assertOk();
        $response->assertJsonPath('data.paypal_mode', 'sandbox');
    }

    public function test_checkout_uses_paypal_gateway_when_simulated_but_paypal_credentials_exist(): void
    {
        Http::fake([
            'api-m.sandbox.paypal.com/v1/oauth2/token' => Http::response([
                'access_token' => 'fake_access',
                'expires_in' => 3600,
            ], 200),
            'api-m.sandbox.paypal.com/v2/checkout/orders' => Http::response([
                'id' => 'SANDBOX_ORDER_X',
                'status' => 'CREATED',
                'links' => [
                    [
                        'href' => 'https://www.sandbox.paypal.com/checkoutnow?token=SANDBOX_ORDER_X',
                        'rel' => 'approve',
                        'method' => 'GET',
                    ],
                ],
            ], 201),
        ]);

        config([
            'services.paypal.mode' => 'sandbox',
            'services.paypal.client_id' => self::FAKE_PAYPAL_CLIENT_ID,
            'services.paypal.secret' => self::FAKE_PAYPAL_SECRET,
            'services.stripe.key' => '',
            'services.stripe.secret' => '',
            'app.debug' => true,
            'payments.allow_simulated' => true,
        ]);

        $client = $this->makeClientWithCart();

        $payload = [
            'payment_method' => 'paypal',
            'shipping_street' => 'Carrer 1',
            'shipping_city' => 'Barcelona',
            'shipping_province' => '',
            'shipping_postal_code' => '08001',
            'shipping_note' => '',
            'installation_street' => '',
            'installation_city' => '',
            'installation_postal_code' => '',
            'installation_note' => '',
        ];

        $this->actingAs($client, 'web');
        $response = $this->postJson('/api/v1/orders/checkout', $payload);

        $response->assertCreated();
        $response->assertJsonPath('data.status', 'awaiting_payment');
        $response->assertJsonPath('data.payment_checkout.gateway', 'paypal');
        $response->assertJsonPath('data.payment_checkout.paypal_order_id', 'SANDBOX_ORDER_X');
        $response->assertJsonPath('data.has_payment', false);
    }

    public function test_checkout_rejects_paypal_when_paypal_not_configured_even_if_simulated_mode(): void
    {
        config([
            'services.paypal.client_id' => '',
            'services.paypal.secret' => '',
            'services.stripe.key' => '',
            'services.stripe.secret' => '',
            'app.debug' => true,
            'payments.allow_simulated' => true,
        ]);

        $client = $this->makeClientWithCart();

        $payload = [
            'payment_method' => 'paypal',
            'shipping_street' => 'Carrer 1',
            'shipping_city' => 'Barcelona',
            'shipping_province' => '',
            'shipping_postal_code' => '08001',
            'shipping_note' => '',
            'installation_street' => '',
            'installation_city' => '',
            'installation_postal_code' => '',
            'installation_note' => '',
        ];

        $this->actingAs($client, 'web');
        $response = $this->postJson('/api/v1/orders/checkout', $payload);

        $response->assertStatus(422);
        $response->assertJsonPath('code', 'payment_method_not_configured');
        Http::assertNothingSent();
    }

    public function test_payments_config_paypal_false_without_credentials_even_when_simulated(): void
    {
        config([
            'services.paypal.client_id' => '',
            'services.paypal.secret' => '',
            'app.debug' => true,
            'payments.allow_simulated' => true,
        ]);

        $response = $this->getJson('/api/v1/payments/config');
        $response->assertOk();
        $response->assertJsonPath('data.methods.paypal', false);
        $response->assertJsonPath('data.paypal_missing_credentials', true);
        $response->assertJsonPath('data.paypal_mode', 'sandbox');
    }

    public function test_payments_config_paypal_missing_credentials_false_when_paypal_not_in_checkout_methods(): void
    {
        config([
            'payments.checkout_method_keys' => ['card'],
            'services.paypal.client_id' => '',
            'services.paypal.secret' => '',
        ]);

        $response = $this->getJson('/api/v1/payments/config');
        $response->assertOk();
        $response->assertJsonPath('data.paypal_missing_credentials', false);
        $response->assertJsonPath('data.paypal_mode', 'sandbox');
    }

    /** Operator scenario: PayPal-only checkout, real credentials required, no simulated blanket. */
    public function test_payments_config_paypal_only_with_credentials_and_no_simulation(): void
    {
        config([
            'payments.checkout_method_keys' => ['paypal'],
            'payments.allow_simulated' => false,
            'app.debug' => true,
            'services.stripe.key' => '',
            'services.stripe.secret' => '',
            'services.paypal.client_id' => self::FAKE_PAYPAL_CLIENT_ID,
            'services.paypal.secret' => self::FAKE_PAYPAL_SECRET,
            'services.paypal.mode' => 'sandbox',
        ]);

        $response = $this->getJson('/api/v1/payments/config');
        $response->assertOk();
        $response->assertJsonPath('data.methods.paypal', true);
        $response->assertJsonPath('data.methods.card', false);
        $response->assertJsonPath('data.simulated', false);
        $response->assertJsonPath('data.paypal_missing_credentials', false);
        $response->assertJsonPath('data.paypal_mode', 'sandbox');
    }

    public function test_payments_config_stripe_missing_credentials_when_whitelisted_without_keys_and_no_simulation(): void
    {
        config([
            'payments.checkout_method_keys' => ['card', 'paypal'],
            'payments.allow_simulated' => false,
            'app.debug' => true,
            'services.stripe.key' => '',
            'services.stripe.secret' => '',
            'services.paypal.client_id' => self::FAKE_PAYPAL_CLIENT_ID,
            'services.paypal.secret' => self::FAKE_PAYPAL_SECRET,
        ]);

        $response = $this->getJson('/api/v1/payments/config');
        $response->assertOk();
        $response->assertJsonPath('data.methods.card', false);
        $response->assertJsonPath('data.stripe_missing_credentials', true);
        $response->assertJsonPath('data.paypal_mode', 'sandbox');
    }

    public function test_payments_config_stripe_missing_credentials_false_when_simulated_without_keys(): void
    {
        config([
            'payments.checkout_method_keys' => ['card'],
            'payments.allow_simulated' => true,
            'app.debug' => true,
            'services.stripe.key' => '',
            'services.stripe.secret' => '',
        ]);

        $response = $this->getJson('/api/v1/payments/config');
        $response->assertOk();
        $response->assertJsonPath('data.methods.card', true);
        $response->assertJsonPath('data.stripe_missing_credentials', false);
        $response->assertJsonPath('data.paypal_mode', 'sandbox');
    }

    public function test_payments_config_stripe_missing_credentials_false_when_card_not_whitelisted(): void
    {
        config([
            'payments.checkout_method_keys' => ['paypal'],
            'payments.allow_simulated' => false,
            'app.debug' => true,
            'services.stripe.key' => '',
            'services.stripe.secret' => '',
            'services.paypal.client_id' => self::FAKE_PAYPAL_CLIENT_ID,
            'services.paypal.secret' => self::FAKE_PAYPAL_SECRET,
        ]);

        $response = $this->getJson('/api/v1/payments/config');
        $response->assertOk();
        $response->assertJsonPath('data.stripe_missing_credentials', false);
        $response->assertJsonPath('data.paypal_mode', 'sandbox');
    }
}
