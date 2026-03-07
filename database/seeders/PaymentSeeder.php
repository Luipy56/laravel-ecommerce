<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    /**
     * Seeds payments (one or more per order). Only for kind=order (not carts).
     */
    public function run(): void
    {
        $now = now();
        DB::table('payments')->insert([
            ['order_id' => 1, 'amount' => 36.00, 'payment_method' => 'card', 'gateway_reference' => 'ch_ord1', 'paid_at' => $now->copy()->subDays(5), 'created_at' => $now, 'updated_at' => $now],
            ['order_id' => 2, 'amount' => 18.00, 'payment_method' => 'card', 'gateway_reference' => 'ch_ord2', 'paid_at' => $now->copy()->subDays(7), 'created_at' => $now, 'updated_at' => $now],
            ['order_id' => 3, 'amount' => 557.00, 'payment_method' => 'card', 'gateway_reference' => 'ch_xxx1', 'paid_at' => $now->copy()->subDays(10), 'created_at' => $now, 'updated_at' => $now],
            ['order_id' => 4, 'amount' => 130.00, 'payment_method' => 'card', 'gateway_reference' => 'ch_ord4', 'paid_at' => $now->copy()->subDays(3), 'created_at' => $now, 'updated_at' => $now],
            ['order_id' => 5, 'amount' => 575.00, 'payment_method' => 'bizum', 'gateway_reference' => 'biz_xxx1', 'paid_at' => $now->copy()->subDays(2), 'created_at' => $now, 'updated_at' => $now],
            ['order_id' => 6, 'amount' => 89.00, 'payment_method' => 'paypal', 'gateway_reference' => 'pay_ord6', 'paid_at' => $now->copy()->subDays(20), 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
