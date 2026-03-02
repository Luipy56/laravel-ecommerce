<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderDetailSeeder extends Seeder
{
    /**
     * Seeds order_lines. Orders 1-5 = kind order; 6-8 = kind cart.
     * Carts have unit_price null (price not fixed until checkout).
     */
    public function run(): void
    {
        $cil30 = Product::where('code', 'CIL-30')->first();
        $cilSeg = Product::where('code', 'CIL-SEG')->first();
        $escEst = Product::where('code', 'ESC-EST')->first();
        $spEst = Product::where('code', 'SP-EST')->first();

        $lines = [];

        // Order 1 (pending)
        if ($cil30) {
            $lines[] = [
                'order_id' => 1,
                'product_id' => $cil30->id,
                'pack_id' => null,
                'quantity' => 1,
                'unit_price' => (float) $cil30->price,
                'offer' => null,
                'is_installation_requested' => false,
                'installation_price' => null,
                'extra_keys_qty' => 0,
                'extra_key_unit_price' => null,
            ];
        }

        // Order 2 (sent)
        if ($cil30 && $escEst) {
            $lines[] = [
                'order_id' => 2,
                'product_id' => $cil30->id,
                'pack_id' => null,
                'quantity' => 2,
                'unit_price' => (float) $cil30->price,
                'offer' => null,
                'is_installation_requested' => false,
                'installation_price' => null,
                'extra_keys_qty' => 1,
                'extra_key_unit_price' => $cil30->extra_key_unit_price ? (float) $cil30->extra_key_unit_price : null,
            ];
            $lines[] = [
                'order_id' => 2,
                'product_id' => $escEst->id,
                'pack_id' => null,
                'quantity' => 2,
                'unit_price' => (float) $escEst->price,
                'offer' => 5.00,
                'is_installation_requested' => false,
                'installation_price' => null,
                'extra_keys_qty' => 0,
                'extra_key_unit_price' => null,
            ];
        }

        // Order 3 (installation_pending) - product with installation
        if ($cilSeg) {
            $lines[] = [
                'order_id' => 3,
                'product_id' => $cilSeg->id,
                'pack_id' => null,
                'quantity' => 1,
                'unit_price' => (float) $cilSeg->price,
                'offer' => null,
                'is_installation_requested' => true,
                'installation_price' => (float) $cilSeg->installation_price,
                'extra_keys_qty' => 0,
                'extra_key_unit_price' => null,
            ];
        }

        // Order 4 (installation_confirmed)
        if ($spEst) {
            $lines[] = [
                'order_id' => 4,
                'product_id' => $spEst->id,
                'pack_id' => null,
                'quantity' => 1,
                'unit_price' => (float) $spEst->price,
                'offer' => null,
                'is_installation_requested' => false,
                'installation_price' => null,
                'extra_keys_qty' => 2,
                'extra_key_unit_price' => $spEst->extra_key_unit_price ? (float) $spEst->extra_key_unit_price : null,
            ];
        }

        // Order 5 (installation_confirmed) - pack
        $lines[] = [
            'order_id' => 5,
            'product_id' => null,
            'pack_id' => 1,
            'quantity' => 1,
            'unit_price' => 89.00,
            'offer' => null,
            'is_installation_requested' => false,
            'installation_price' => null,
            'extra_keys_qty' => 0,
            'extra_key_unit_price' => null,
        ];

        // Cart 6 (client 1) - unit_price null
        if ($cil30 && $escEst) {
            $lines[] = [
                'order_id' => 6,
                'product_id' => $cil30->id,
                'pack_id' => null,
                'quantity' => 2,
                'unit_price' => null,
                'offer' => null,
                'is_installation_requested' => false,
                'installation_price' => null,
                'extra_keys_qty' => 1,
                'extra_key_unit_price' => null,
            ];
            $lines[] = [
                'order_id' => 6,
                'product_id' => $escEst->id,
                'pack_id' => null,
                'quantity' => 1,
                'unit_price' => null,
                'offer' => null,
                'is_installation_requested' => false,
                'installation_price' => null,
                'extra_keys_qty' => 0,
                'extra_key_unit_price' => null,
            ];
        }

        // Cart 7 (client 2) - pack in cart
        $lines[] = [
            'order_id' => 7,
            'product_id' => null,
            'pack_id' => 2,
            'quantity' => 1,
            'unit_price' => null,
            'offer' => null,
            'is_installation_requested' => false,
            'installation_price' => null,
            'extra_keys_qty' => 0,
            'extra_key_unit_price' => null,
        ];

        // Cart 8 (client 3) - single product
        if ($spEst) {
            $lines[] = [
                'order_id' => 8,
                'product_id' => $spEst->id,
                'pack_id' => null,
                'quantity' => 1,
                'unit_price' => null,
                'offer' => null,
                'is_installation_requested' => false,
                'installation_price' => null,
                'extra_keys_qty' => 0,
                'extra_key_unit_price' => null,
            ];
        }

        if (! empty($lines)) {
            DB::table('order_lines')->insert($lines);
        }
    }
}
