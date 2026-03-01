<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    /**
     * Seeds payments (one or more per order).
     */
    public function run(): void
    {
        $now = now();
        DB::table('payments')->insert([
            ['order_id' => 1, 'amount' => 557.00, 'payment_method' => 'card', 'gateway_reference' => 'ch_xxx1', 'paid_at' => $now->copy()->subDays(10)],
            ['order_id' => 2, 'amount' => 699.00, 'payment_method' => 'card', 'gateway_reference' => null, 'paid_at' => null],
            ['order_id' => 3, 'amount' => 575.00, 'payment_method' => 'bizum', 'gateway_reference' => 'biz_xxx1', 'paid_at' => $now->copy()->subDays(1)],
        ]);
    }
}
