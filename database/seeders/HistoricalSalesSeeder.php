<?php

namespace Database\Seeders;

use App\Models\Payment;
use App\Models\Product;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HistoricalSalesSeeder extends Seeder
{
    /**
     * Minimal fake sales from start of last year until end of 2026 (date + amount).
     * Each sale = one order (kind=order) + one payment + one order_line.
     */
    public function run(): void
    {
        $product = Product::first();
        if (! $product) {
            return;
        }

        $start = Carbon::now()->subYear()->startOfYear();
        $end = Carbon::create(2026, 12, 31)->endOfDay();
        $clients = [1, 2, 3];
        $paymentMethods = ['card', 'card', 'card', 'bizum', 'paypal'];
        $addresses = [
            ['street' => 'Calle Mayor 12', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08001'],
            ['street' => 'Plaza de Cataluña 5', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08002'],
            ['street' => 'Calle Balmes 45', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08007'],
            ['street' => 'Paseo de Gracia 88', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08008'],
            ['street' => 'Rambla de Cataluña 22', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08009'],
            ['street' => 'Avenida Diagonal 100', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08018'],
        ];

        // 500 sales spread over the period for demo charts
        $sales = [];
        for ($i = 0; $i < 500; $i++) {
            $sales[] = [
                'order_date' => Carbon::createFromTimestamp(mt_rand($start->timestamp, $end->timestamp)),
                'amount' => (float) round(mt_rand(2500, 40000) / 100, 2), // 25.00 - 400.00
            ];
        }

        usort($sales, fn ($a, $b) => $a['order_date']->timestamp <=> $b['order_date']->timestamp);

        $now = now();
        foreach ($sales as $idx => $sale) {
            // Avoid non-existent wall-clock times on DST change days (MySQL rejects them).
            $orderDate = $sale['order_date']->copy()->setTime(mt_rand(8, 16), mt_rand(0, 59), 0);
            $amount = $sale['amount'];
            $clientId = $clients[$idx % count($clients)];
            $shippingDate = $orderDate->copy()->addDays(mt_rand(1, 3))->setTime(mt_rand(8, 16), mt_rand(0, 59), 0);
            $shippingPrice = 9.00;

            $orderId = DB::table('orders')->insertGetId([
                'client_id' => $clientId,
                'kind' => 'order',
                'status' => 'sent',
                'order_date' => $orderDate,
                'shipping_date' => $shippingDate,
                'shipping_price' => $shippingPrice,
                'installation_requested' => false,
                'installation_price' => null,
                'installation_status' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('order_lines')->insert([
                'order_id' => $orderId,
                'product_id' => $product->id,
                'pack_id' => null,
                'quantity' => 1,
                'unit_price' => $amount,
                'offer' => null,
                'keys_all_same' => false,
                'extra_keys_qty' => 0,
                'extra_key_unit_price' => null,
                'is_included' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $method = $paymentMethods[$idx % count($paymentMethods)];
            $gateway = $method === 'bizum' ? Payment::GATEWAY_REDSYS : Payment::GATEWAY_STRIPE;
            DB::table('payments')->insert([
                'order_id' => $orderId,
                'amount' => round($amount + $shippingPrice, 2),
                'payment_method' => $method,
                'status' => Payment::STATUS_SUCCEEDED,
                'gateway' => $gateway,
                'currency' => 'EUR',
                'gateway_reference' => 'hist_'.$orderId,
                'paid_at' => $orderDate,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $addr = $addresses[$idx % count($addresses)];
            DB::table('order_addresses')->insert([
                'order_id' => $orderId,
                'type' => 'shipping',
                'street' => $addr['street'],
                'city' => $addr['city'],
                'province' => $addr['province'],
                'postal_code' => $addr['postal_code'],
                'note' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }
}
