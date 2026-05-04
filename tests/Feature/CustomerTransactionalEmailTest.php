<?php

namespace Tests\Feature;

use App\Contracts\Payments\PaymentCheckoutStarter;
use App\Mail\InstallationPriceAssignedMail;
use App\Mail\OrderInstallationQuoteRequestedAdminMail;
use App\Mail\OrderInstallationQuoteRequestedMail;
use App\Mail\OrderPaymentConfirmedMail;
use App\Mail\OrderPaymentPendingAdminMail;
use App\Mail\OrderPaymentPendingMail;
use App\Mail\OrderShippedMail;
use App\Mail\PersonalizedSolutionReceivedMail;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderAddress;
use App\Models\OrderLine;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\Payments\Stripe\StripeCheckoutStarter;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class CustomerTransactionalEmailTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    private function makeClientWithCart(bool $installationRequested = false, int $lineQuantity = 1, float $unitPrice = 25.00): Client
    {
        $client = Client::query()->create([
            'type' => 'person',
            'identification' => null,
            'login_email' => 'buyer_'.uniqid('', true).'@ietf.org',
            'password' => bcrypt('password'),
            'is_active' => true,
            'email_verified_at' => now(),
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
            'installation_requested' => $installationRequested,
        ]);

        OrderLine::query()->create([
            'order_id' => $cart->id,
            'product_id' => $product->id,
            'pack_id' => null,
            'quantity' => $lineQuantity,
            'unit_price' => $unitPrice,
            'offer' => null,
            'keys_all_same' => false,
            'extra_keys_qty' => 0,
            'extra_key_unit_price' => null,
            'is_included' => true,
        ]);

        return $client;
    }

    /**
     * @return array{0: Client, 1: Order}
     */
    private function makeClientWithPendingUnpaidOrder(): array
    {
        $client = $this->makeClientWithCart(false);
        $order = Order::query()
            ->where('client_id', $client->id)
            ->where('kind', Order::KIND_CART)
            ->first();
        $this->assertNotNull($order);

        $order->update([
            'kind' => Order::KIND_ORDER,
            'status' => Order::STATUS_PENDING,
            'order_date' => now(),
            'shipping_price' => Order::SHIPPING_FLAT_EUR,
        ]);

        $order->addresses()->create([
            'type' => OrderAddress::TYPE_SHIPPING,
            'street' => 'Carrer Prova',
            'city' => 'Barcelona',
            'province' => null,
            'postal_code' => '08001',
            'note' => null,
        ]);

        return [$client, $order->fresh()];
    }

    public function test_order_pay_with_simulated_card_sends_payment_confirmation_mail(): void
    {
        Mail::fake();

        config([
            'services.stripe.key' => '',
            'services.stripe.secret' => '',
            'app.debug' => true,
            'payments.allow_simulated' => true,
            'payments.checkout_method_keys' => ['card', 'paypal'],
        ]);

        [$client, $order] = $this->makeClientWithPendingUnpaidOrder();

        $this->actingAs($client, 'web');
        $response = $this->postJson('/api/v1/orders/'.$order->id.'/pay', [
            'payment_method' => 'card',
        ]);

        $response->assertOk();
        $response->assertJsonPath('data.has_payment', true);

        Mail::assertSent(OrderPaymentConfirmedMail::class, function (OrderPaymentConfirmedMail $mail) use ($client) {
            return $mail->hasTo($client->login_email);
        });
    }

    public function test_checkout_with_stripe_not_simulated_sends_payment_pending_mails(): void
    {
        Mail::fake();

        $this->app->bind(StripeCheckoutStarter::class, function () {
            return new class implements PaymentCheckoutStarter
            {
                public function gateway(): string
                {
                    return Payment::GATEWAY_STRIPE;
                }

                public function start(Payment $payment): array
                {
                    return [
                        'gateway' => Payment::GATEWAY_STRIPE,
                        'checkout_url' => 'https://checkout.stripe.test/c',
                        'publishable_key' => 'pk_test_fake',
                        'session_id' => 'cs_test_fake',
                    ];
                }
            };
        });

        $admin = 'ops_'.uniqid('', true).'@ietf.org';
        config([
            'services.stripe.key' => 'pk_test_valid_length_fake',
            'services.stripe.secret' => 'sk_test_valid_length_fake',
            'app.debug' => true,
            'payments.allow_simulated' => false,
            'payments.checkout_method_keys' => ['card', 'paypal'],
            'mail.admin_notification_address' => $admin,
        ]);

        $client = $this->makeClientWithCart(false);

        $payload = [
            'payment_method' => 'card',
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
        $response->assertJsonPath('data.has_payment', false);

        Mail::assertSent(OrderPaymentPendingMail::class, function (OrderPaymentPendingMail $mail) use ($client) {
            return $mail->hasTo($client->login_email);
        });
        Mail::assertNotSent(OrderPaymentConfirmedMail::class);
        Mail::assertSent(OrderPaymentPendingAdminMail::class, function (OrderPaymentPendingAdminMail $mail) use ($admin) {
            return $mail->hasTo($admin);
        });
    }

    public function test_checkout_with_simulated_card_sends_payment_confirmation_mail(): void
    {
        Mail::fake();

        config([
            'services.stripe.key' => '',
            'services.stripe.secret' => '',
            'app.debug' => true,
            'payments.allow_simulated' => true,
            'payments.checkout_method_keys' => ['card', 'paypal'],
        ]);

        $client = $this->makeClientWithCart(false);

        $payload = [
            'payment_method' => 'card',
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
        $response->assertJsonPath('data.has_payment', true);

        Mail::assertSent(OrderPaymentConfirmedMail::class, function (OrderPaymentConfirmedMail $mail) use ($client) {
            return $mail->hasTo($client->login_email);
        });
    }

    public function test_checkout_with_installation_request_sends_quote_request_mail(): void
    {
        Mail::fake();

        config([
            'services.stripe.key' => '',
            'services.stripe.secret' => '',
            'app.debug' => true,
            'payments.allow_simulated' => true,
            'mail.admin_notification_address' => null,
        ]);

        $client = $this->makeClientWithCart(true, 41, 25.00);

        $payload = [
            'payment_method' => null,
            'shipping_street' => 'Carrer 1',
            'shipping_city' => 'Barcelona',
            'shipping_province' => '',
            'shipping_postal_code' => '08001',
            'shipping_note' => '',
            'installation_street' => 'Av. Diagonal 1',
            'installation_city' => 'Barcelona',
            'installation_postal_code' => '08028',
            'installation_note' => '',
        ];

        $this->actingAs($client, 'web');
        $response = $this->postJson('/api/v1/orders/checkout', $payload);

        $response->assertCreated();
        $response->assertJsonPath('data.awaiting_installation_quote', true);

        Mail::assertSent(OrderInstallationQuoteRequestedMail::class, function (OrderInstallationQuoteRequestedMail $mail) use ($client) {
            return $mail->hasTo($client->login_email);
        });
        Mail::assertNotSent(OrderInstallationQuoteRequestedAdminMail::class);
    }

    public function test_checkout_with_installation_request_sends_quote_mails_to_admin_when_configured(): void
    {
        Mail::fake();

        $admin = 'ops_'.uniqid('', true).'@ietf.org';
        config([
            'services.stripe.key' => '',
            'services.stripe.secret' => '',
            'app.debug' => true,
            'payments.allow_simulated' => true,
            'mail.admin_notification_address' => $admin,
        ]);

        $client = $this->makeClientWithCart(true, 41, 25.00);

        $payload = [
            'payment_method' => null,
            'shipping_street' => 'Carrer 1',
            'shipping_city' => 'Barcelona',
            'shipping_province' => '',
            'shipping_postal_code' => '08001',
            'shipping_note' => '',
            'installation_street' => 'Av. Diagonal 1',
            'installation_city' => 'Barcelona',
            'installation_postal_code' => '08028',
            'installation_note' => '',
        ];

        $this->actingAs($client, 'web');
        $response = $this->postJson('/api/v1/orders/checkout', $payload, ['Accept-Language' => 'es']);

        $response->assertCreated();
        $response->assertJsonPath('data.awaiting_installation_quote', true);

        Mail::assertSent(OrderInstallationQuoteRequestedMail::class, function (OrderInstallationQuoteRequestedMail $mail) use ($client) {
            return $mail->hasTo($client->login_email);
        });
        Mail::assertSent(OrderInstallationQuoteRequestedAdminMail::class, function (OrderInstallationQuoteRequestedAdminMail $mail) use ($admin) {
            return $mail->hasTo($admin);
        });
    }

    public function test_checkout_with_installation_automatic_tier_skips_quote_mail_and_confirms_payment(): void
    {
        Mail::fake();

        config([
            'services.stripe.key' => '',
            'services.stripe.secret' => '',
            'app.debug' => true,
            'payments.allow_simulated' => true,
            'payments.checkout_method_keys' => ['card', 'paypal'],
        ]);

        $client = $this->makeClientWithCart(true, 8, 25.00);

        $payload = [
            'payment_method' => 'card',
            'shipping_street' => 'Carrer 1',
            'shipping_city' => 'Barcelona',
            'shipping_province' => '',
            'shipping_postal_code' => '08001',
            'shipping_note' => '',
            'installation_street' => 'Av. Diagonal 1',
            'installation_city' => 'Barcelona',
            'installation_postal_code' => '08028',
            'installation_note' => '',
        ];

        $this->actingAs($client, 'web');
        $response = $this->postJson('/api/v1/orders/checkout', $payload);

        $response->assertCreated();
        $response->assertJsonPath('data.awaiting_installation_quote', false);
        $response->assertJsonPath('data.installation_price', 90);
        $response->assertJsonPath('data.installation_status', Order::INSTALLATION_PRICED);
        $response->assertJsonPath('data.has_payment', true);

        Mail::assertNotSent(OrderInstallationQuoteRequestedMail::class);
        Mail::assertSent(OrderPaymentConfirmedMail::class, function (OrderPaymentConfirmedMail $mail) use ($client) {
            return $mail->hasTo($client->login_email);
        });
    }

    public function test_admin_assigns_installation_price_sends_installation_price_mail(): void
    {
        Mail::fake();

        $this->seed(DatabaseSeeder::class);

        config([
            'services.stripe.key' => '',
            'services.stripe.secret' => '',
            'app.debug' => true,
            'payments.allow_simulated' => true,
        ]);

        $client = $this->makeClientWithCart(true, 41, 25.00);

        $checkoutPayload = [
            'payment_method' => null,
            'shipping_street' => 'Carrer 1',
            'shipping_city' => 'Barcelona',
            'shipping_province' => '',
            'shipping_postal_code' => '08001',
            'shipping_note' => '',
            'installation_street' => 'Av. Diagonal 1',
            'installation_city' => 'Barcelona',
            'installation_postal_code' => '08028',
            'installation_note' => '',
        ];

        $this->actingAs($client, 'web');
        $checkoutResponse = $this->postJson('/api/v1/orders/checkout', $checkoutPayload);
        $checkoutResponse->assertCreated();
        $orderId = (int) $checkoutResponse->json('data.id');

        Mail::assertSent(OrderInstallationQuoteRequestedMail::class);

        $this->withCredentials();
        $this->postJson('/api/v1/admin/login', [
            'username' => 'manager',
            'password' => 'admin',
        ])->assertOk();

        $this->putJson('/api/v1/admin/orders/'.$orderId, [
            'status' => Order::STATUS_AWAITING_INSTALLATION_PRICE,
            'installation_price' => 55.5,
        ])->assertOk();

        Mail::assertSent(InstallationPriceAssignedMail::class, function (InstallationPriceAssignedMail $mail) use ($client) {
            return $mail->hasTo($client->login_email);
        });
    }

    public function test_personalized_solution_store_sends_acknowledgement_mail(): void
    {
        Mail::fake();

        $email = 'sol_'.uniqid('', true).'@ietf.org';

        $this->postJson('/api/v1/personalized-solutions', [
            'email' => $email,
            'phone' => null,
            'problem_description' => 'Need a custom lock setup',
            'address_street' => null,
            'address_city' => null,
            'address_province' => null,
            'address_postal_code' => '08001',
            'address_note' => null,
        ], ['Accept-Language' => 'es'])->assertCreated();

        Mail::assertSent(PersonalizedSolutionReceivedMail::class, function (PersonalizedSolutionReceivedMail $mail) use ($email) {
            if (! $mail->hasTo($email)) {
                return false;
            }
            Config::set('mail.from', [
                'address' => 'noreply@ietf.org',
                'name' => 'Test Shop',
            ]);
            $html = $mail->render();

            return str_contains($html, 'Need a custom lock setup')
                && str_contains($html, '08001')
                && str_contains($html, 'Resumen de su solicitud');
        });
    }

    public function test_admin_sets_order_in_transit_sends_shipped_mail(): void
    {
        Mail::fake();

        $this->seed(DatabaseSeeder::class);

        $this->withCredentials();
        $this->postJson('/api/v1/admin/login', [
            'username' => 'manager',
            'password' => 'admin',
        ])->assertOk();

        $this->putJson('/api/v1/admin/orders/1', [
            'status' => 'in_transit',
            'shipping_date' => null,
        ])->assertOk();

        Mail::assertSent(OrderShippedMail::class);
    }

    public function test_admin_can_send_in_transit_customer_mail_with_delivery_eta(): void
    {
        Mail::fake();

        $this->seed(DatabaseSeeder::class);

        $this->withCredentials();
        $this->postJson('/api/v1/admin/login', [
            'username' => 'manager',
            'password' => 'admin',
        ])->assertOk();

        $response = $this->postJson('/api/v1/admin/orders/2/notify-in-transit-mail', [
            'delivery_eta' => 'unknown',
        ]);
        $response->assertOk();

        Mail::assertSent(OrderShippedMail::class, function (OrderShippedMail $mail) {
            return $mail->deliveryEstimateKey === 'soon';
        });

        $sent = Mail::sent(OrderShippedMail::class, fn (OrderShippedMail $m) => $m->deliveryEstimateKey === 'soon')->first();
        $this->assertNotNull($sent);
        $html = $sent->render();
        $this->assertTrue(
            str_contains($html, 'le llegará pronto')
                || str_contains($html, 'it will arrive soon')
                || str_contains($html, 'Arribarà aviat'),
            'Expected friendly “unknown ETA” copy in the email body.'
        );
        $this->assertStringNotContainsString('No se sabe', $html);
    }

    public function test_admin_cannot_send_in_transit_customer_mail_when_order_not_in_transit(): void
    {
        Mail::fake();

        $this->seed(DatabaseSeeder::class);

        $this->withCredentials();
        $this->postJson('/api/v1/admin/login', [
            'username' => 'manager',
            'password' => 'admin',
        ])->assertOk();

        $this->postJson('/api/v1/admin/orders/1/notify-in-transit-mail', [
            'delivery_eta' => 'today',
        ])->assertStatus(422);

        Mail::assertNotSent(OrderShippedMail::class);
    }
}
