<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderDetailSeeder extends Seeder
{
    /**
     * Seeds order_lines (line items: product or pack, quantity, price).
     */
    public function run(): void
    {
        DB::table('order_lines')->insert([
            ['order_id' => 1, 'product_id' => 1, 'pack_id' => null, 'quantity' => 1, 'unit_price' => 450.00, 'offer' => null, 'is_installation_requested' => true, 'installation_price' => 85.00, 'extra_keys_qty' => 0, 'extra_key_unit_price' => null],
            ['order_id' => 1, 'product_id' => 4, 'pack_id' => null, 'quantity' => 2, 'unit_price' => 95.00, 'offer' => 10.00, 'is_installation_requested' => false, 'installation_price' => null, 'extra_keys_qty' => 0, 'extra_key_unit_price' => null],
            ['order_id' => 2, 'product_id' => null, 'pack_id' => 1, 'quantity' => 1, 'unit_price' => 699.00, 'offer' => null, 'is_installation_requested' => true, 'installation_price' => 120.00, 'extra_keys_qty' => 0, 'extra_key_unit_price' => null],
            ['order_id' => 3, 'product_id' => 3, 'pack_id' => null, 'quantity' => 2, 'unit_price' => 280.00, 'offer' => null, 'is_installation_requested' => true, 'installation_price' => 65.00, 'extra_keys_qty' => 0, 'extra_key_unit_price' => null],
        ]);
    }
}
