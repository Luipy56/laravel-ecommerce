<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PurchasedProductsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_guest_receives_401_for_purchases_index(): void
    {
        $response = $this->getJson('/api/v1/purchases');

        $response->assertUnauthorized();
    }

    public function test_authenticated_client_sees_purchased_product_lines(): void
    {
        [$client] = $this->makeClientWithOrderLine('Alpha SKU', now()->subDay());

        $response = $this->actingAs($client, 'web')->getJson('/api/v1/purchases');

        $response->assertOk()
            ->assertJsonPath('success', true);
        $data = $response->json('data');
        $this->assertCount(1, $data);
        $this->assertSame('product', $data[0]['kind']);
        $this->assertSame('Alpha SKU', $data[0]['name']);
        $this->assertArrayHasKey('order_id', $data[0]);
        $this->assertArrayHasKey('line_total', $data[0]);
    }

    public function test_date_filters_exclude_lines_outside_range(): void
    {
        [$client] = $this->makeClientWithOrderLine('Old', '2020-03-15 10:00:00');
        $this->makeSecondOrderLineForClient($client, 'New', '2025-06-10 12:00:00');

        $response = $this->actingAs($client, 'web')->getJson('/api/v1/purchases?date_from=2025-01-01&date_to=2025-12-31');

        $response->assertOk();
        $names = collect($response->json('data'))->pluck('name')->all();
        $this->assertSame(['New'], $names);
    }

    public function test_invalid_date_range_returns_422(): void
    {
        $client = Client::query()->create([
            'type' => 'person',
            'identification' => null,
            'login_email' => 'u_'.uniqid('', true).'@ietf.org',
            'password' => bcrypt('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($client, 'web')->getJson('/api/v1/purchases?date_from=2025-06-01&date_to=2025-01-01');

        $response->assertStatus(422);
    }

    /**
     * @return array{0: Client}
     */
    private function makeClientWithOrderLine(string $productName, \DateTimeInterface|string $orderDate): array
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
            'name' => $productName,
            'price' => 10.00,
            'stock' => 5,
            'is_active' => true,
        ]);

        $order = Order::query()->create([
            'client_id' => $client->id,
            'kind' => Order::KIND_ORDER,
            'status' => Order::STATUS_PENDING,
            'order_date' => $orderDate,
            'shipping_price' => Order::SHIPPING_FLAT_EUR,
            'installation_requested' => false,
        ]);

        OrderLine::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'pack_id' => null,
            'quantity' => 1,
            'unit_price' => 10.00,
            'offer' => null,
            'keys_all_same' => false,
            'extra_keys_qty' => 0,
            'extra_key_unit_price' => null,
            'is_included' => true,
        ]);

        return [$client];
    }

    private function makeSecondOrderLineForClient(Client $client, string $productName, \DateTimeInterface|string $orderDate): void
    {
        $category = ProductCategory::query()->firstOrFail();
        $product = Product::query()->create([
            'category_id' => $category->id,
            'code' => 'p2_'.uniqid(),
            'name' => $productName,
            'price' => 12.00,
            'stock' => 3,
            'is_active' => true,
        ]);

        $order = Order::query()->create([
            'client_id' => $client->id,
            'kind' => Order::KIND_ORDER,
            'status' => Order::STATUS_PENDING,
            'order_date' => $orderDate,
            'shipping_price' => Order::SHIPPING_FLAT_EUR,
            'installation_requested' => false,
        ]);

        OrderLine::query()->create([
            'order_id' => $order->id,
            'product_id' => $product->id,
            'pack_id' => null,
            'quantity' => 1,
            'unit_price' => 12.00,
            'offer' => null,
            'keys_all_same' => false,
            'extra_keys_qty' => 0,
            'extra_key_unit_price' => null,
            'is_included' => true,
        ]);
    }
}
