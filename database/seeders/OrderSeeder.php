<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderSeeder extends Seeder
{
    /**
     * Seeds orders. kind: cart | order; status only when kind=order (Catalan values).
     */
    public function run(): void
    {
        $now = now();
        DB::table('orders')->insert([
            [
                'client_id' => 1,
                'kind' => 'order',
                'status' => 'sent',
                'order_date' => $now->copy()->subDays(10),
                'shipping_date' => $now->copy()->subDays(5),
                'shipping_price' => 12.00,
            ],
            [
                'client_id' => 2,
                'kind' => 'order',
                'status' => 'pending',
                'order_date' => $now->copy()->subDays(3),
                'shipping_date' => null,
                'shipping_price' => null,
            ],
            [
                'client_id' => 3,
                'kind' => 'order',
                'status' => 'installation_pending',
                'order_date' => $now->copy()->subDays(1),
                'shipping_date' => null,
                'shipping_price' => 15.00,
            ],
        ]);
    }
}
