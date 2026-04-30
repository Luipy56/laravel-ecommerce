<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Payment;
use App\Models\Product;
use App\Models\ProductCategory;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminOrderManualSettlementTest extends TestCase
{
    use RefreshDatabase;

    private function adminLogin(): void
    {
        $this->seed(AdminSeeder::class);
        $this->withCredentials();
        $this->postJson('/api/v1/admin/login', [
            'username' => 'manager',
            'password' => 'admin',
        ])->assertOk();
    }

    /** @return array{0: Order, 1: Payment} */
    private function createAwaitingOrderWithOfflinePayment(): array
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

        $order = Order::query()->create([
            'client_id' => $client->id,
            'kind' => Order::KIND_ORDER,
            'status' => Order::STATUS_AWAITING_PAYMENT,
            'order_date' => now(),
            'shipping_price' => 9.00,
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

        $payment = Payment::query()->create([
            'order_id' => $order->id,
            'amount' => $order->fresh()->grand_total,
            'payment_method' => Payment::METHOD_BANK_TRANSFER,
            'status' => Payment::STATUS_PENDING,
            'currency' => 'EUR',
        ]);

        return [$order->fresh(), $payment];
    }

    public function test_guest_cannot_record_manual_settlement(): void
    {
        [$order, $payment] = $this->createAwaitingOrderWithOfflinePayment();

        $this->postJson("/api/v1/admin/orders/{$order->id}/payments/{$payment->id}/record-manual-settlement", [])
            ->assertUnauthorized();
    }

    public function test_admin_can_record_manual_settlement_for_offline_pending(): void
    {
        [$order, $payment] = $this->createAwaitingOrderWithOfflinePayment();
        $this->adminLogin();

        $response = $this->postJson(
            "/api/v1/admin/orders/{$order->id}/payments/{$payment->id}/record-manual-settlement",
            ['note' => 'Bank receipt verified']
        );

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('data.has_payment', true);
        $response->assertJsonPath('data.status', Order::STATUS_PENDING);

        $payment->refresh();
        $this->assertSame(Payment::STATUS_SUCCEEDED, $payment->status);
        $this->assertSame(Payment::GATEWAY_MANUAL, $payment->gateway);
    }
}
