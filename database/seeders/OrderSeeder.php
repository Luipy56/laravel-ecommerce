<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    /**
     * Seeds orders. kind: cart | order; status only when kind=order.
     * Covers all kinds (cart, order) and all statuses (pending, sent, installation_pending, installation_confirmed).
     */
    public function run(): void
    {
        $now = now();
        DB::table('orders')->insert([
            // --- kind=order (confirmed orders) ---
            [
                'client_id' => 1,
                'kind' => 'order',
                'status' => 'pending',
                'order_date' => $now->copy()->subDays(5),
                'shipping_date' => null,
                'shipping_price' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'client_id' => 1,
                'kind' => 'order',
                'status' => 'sent',
                'order_date' => $now->copy()->subDays(10),
                'shipping_date' => $now->copy()->subDays(5),
                'shipping_price' => 12.00,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'client_id' => 2,
                'kind' => 'order',
                'status' => 'installation_pending',
                'order_date' => $now->copy()->subDays(3),
                'shipping_date' => $now->copy()->subDays(2),
                'shipping_price' => 18.00,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'client_id' => 2,
                'kind' => 'order',
                'status' => 'installation_confirmed',
                'order_date' => $now->copy()->subDays(1),
                'shipping_date' => $now->copy()->subDays(1),
                'shipping_price' => 15.00,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'client_id' => 3,
                'kind' => 'order',
                'status' => 'installation_confirmed',
                'order_date' => $now->copy()->subDays(20),
                'shipping_date' => $now->copy()->subDays(18),
                'shipping_price' => 22.00,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            // --- kind=cart (shopping carts, not yet confirmed) ---
            [
                'client_id' => 1,
                'kind' => 'cart',
                'status' => null,
                'order_date' => null,
                'shipping_date' => null,
                'shipping_price' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'client_id' => 2,
                'kind' => 'cart',
                'status' => null,
                'order_date' => null,
                'shipping_date' => null,
                'shipping_price' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'client_id' => 3,
                'kind' => 'cart',
                'status' => null,
                'order_date' => null,
                'shipping_date' => null,
                'shipping_price' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }
}
