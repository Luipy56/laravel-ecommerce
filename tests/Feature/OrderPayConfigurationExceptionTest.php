<?php

namespace Tests\Feature;

use App\Exceptions\PaymentProviderNotConfiguredException;
use App\Models\Client;
use App\Models\Order;
use App\Models\OrderLine;
use App\Contracts\Payments\PaymentCheckoutStarter;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\Payments\Stripe\StripeCheckoutStarter;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class OrderPayConfigurationExceptionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    /** @return array{0: Client, 1: Order} */
    private function makeClientWithPendingOrder(): array
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

        $order = Order::query()->create([
            'client_id' => $client->id,
            'kind' => Order::KIND_ORDER,
            'status' => Order::STATUS_PENDING,
            'order_date' => now(),
            'shipping_price' => Order::SHIPPING_FLAT_EUR,
            'installation_requested' => false,
        ]);

        OrderLine::query()->create([
            'order_id' => $order->id,
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

        return [$client, $order];
    }

    public function test_order_pay_does_not_report_log_when_stripe_reports_not_configured_via_mock(): void
    {
        [$client, $order] = $this->makeClientWithPendingOrder();

        config([
            'payments.checkout_method_keys' => ['card'],
            'payments.allow_simulated' => false,
            'app.debug' => true,
            'services.stripe.secret' => 'sk_test_valid_length_fake',
            'services.stripe.key' => 'pk_test_valid_length_fake',
        ]);

        $this->app->bind(StripeCheckoutStarter::class, function () {
            return new class implements PaymentCheckoutStarter
            {
                public function gateway(): string
                {
                    return Payment::GATEWAY_STRIPE;
                }

                public function start(Payment $payment): array
                {
                    throw new PaymentProviderNotConfiguredException('Stripe is not configured (STRIPE_SECRET).');
                }
            };
        });

        Event::fake([MessageLogged::class]);

        $response = $this->actingAs($client, 'web')
            ->postJson('/api/v1/orders/'.$order->id.'/pay', ['payment_method' => 'card']);

        $response->assertStatus(422)
            ->assertJsonPath('success', false)
            ->assertJsonPath('code', 'payment_method_not_configured');

        Event::assertNotDispatched(MessageLogged::class, function (MessageLogged $e): bool {
            return in_array($e->level, ['emergency', 'alert', 'critical', 'error'], true);
        });
    }
}
