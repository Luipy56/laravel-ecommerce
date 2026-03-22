<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PaymentSeeder extends Seeder
{
    /**
     * Seeds payments for demo orders. Amounts follow Order::grand_total (lines + flat shipping + installation when priced).
     * Runs after OrderDetailSeeder.
     */
    public function run(): void
    {
        $now = now();
        $rows = [
            1 => ['payment_method' => 'card', 'gateway_reference' => 'ch_ord1', 'paid_at' => $now->copy()->subDays(5)],
            2 => ['payment_method' => 'card', 'gateway_reference' => 'ch_ord2', 'paid_at' => $now->copy()->subDays(7)],
            3 => ['payment_method' => 'card', 'gateway_reference' => 'ch_xxx1', 'paid_at' => $now->copy()->subDays(10)],
            5 => ['payment_method' => 'bizum', 'gateway_reference' => 'biz_xxx1', 'paid_at' => $now->copy()->subDays(2)],
            6 => ['payment_method' => 'paypal', 'gateway_reference' => 'pay_ord6', 'paid_at' => $now->copy()->subDays(20)],
        ];

        foreach ($rows as $orderId => $meta) {
            $order = Order::with('lines')->find($orderId);
            if (! $order) {
                continue;
            }
            $gateway = match ($meta['payment_method']) {
                'bizum' => Payment::GATEWAY_REDSYS,
                'paypal', 'card' => Payment::GATEWAY_STRIPE,
                default => Payment::GATEWAY_STRIPE,
            };
            DB::table('payments')->insert([
                'order_id' => $orderId,
                'amount' => $order->grand_total,
                'payment_method' => $meta['payment_method'],
                'status' => Payment::STATUS_SUCCEEDED,
                'gateway' => $gateway,
                'currency' => 'EUR',
                'gateway_reference' => $meta['gateway_reference'],
                'paid_at' => $meta['paid_at'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
