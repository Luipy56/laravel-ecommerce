<?php

namespace Database\Seeders;

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
            ['street' => 'Carrer Major 12', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08001'],
            ['street' => 'Plaça Catalunya 5', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08002'],
            ['street' => 'Carrer Balmes 45', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08007'],
            ['street' => 'Pg. de Gràcia 88', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08008'],
            ['street' => 'Rambla Catalunya 22', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08009'],
            ['street' => 'Avinguda Diagonal 100', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08018'],
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
            $orderDate = $sale['order_date'];
            $amount = $sale['amount'];
            $clientId = $clients[$idx % count($clients)];
            $shippingDate = $orderDate->copy()->addDays(mt_rand(1, 3));
            $shippingPrice = (float) round(mt_rand(500, 1500) / 100, 2);

            $orderId = DB::table('orders')->insertGetId([
                'client_id' => $clientId,
                'kind' => 'order',
                'status' => 'sent',
                'order_date' => $orderDate,
                'shipping_date' => $shippingDate,
                'shipping_price' => $shippingPrice,
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
                'is_installation_requested' => false,
                'installation_price' => null,
                'extra_keys_qty' => 0,
                'extra_key_unit_price' => null,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $method = $paymentMethods[$idx % count($paymentMethods)];
            DB::table('payments')->insert([
                'order_id' => $orderId,
                'amount' => $amount,
                'payment_method' => $method,
                'gateway_reference' => 'hist_' . $orderId,
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
