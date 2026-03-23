<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderDetailSeeder extends Seeder
{
    /**
     * Seeds order_lines. Orders 1-6 = kind order; 7-9 = kind cart.
     * Carts have unit_price null (price not fixed until checkout).
     */
    public function run(): void
    {
        $cilStd = Product::where('code', '192 evoK1C 3030 N')->first();
        $cilSeg = Product::where('code', '192 evoK1D 3040 N')->first();
        $escEst = Product::where('code', 'ESC-ABUS-PLATA')->first();
        $spEst = Product::where('code', 'SP-MC-EZC-OFR')->first();

        $lines = [];

        if ($cilStd) {
            $lines[] = [
                'order_id' => 1,
                'product_id' => $cilStd->id,
                'pack_id' => null,
                'quantity' => 1,
                'unit_price' => (float) $cilStd->price,
                'offer' => null,
                'keys_all_same' => false,
                'extra_keys_qty' => 0,
                'extra_key_unit_price' => null,
                'is_included' => true,
            ];
        }

        if ($escEst) {
            $lines[] = [
                'order_id' => 2,
                'product_id' => $escEst->id,
                'pack_id' => null,
                'quantity' => 1,
                'unit_price' => (float) $escEst->price,
                'offer' => null,
                'keys_all_same' => false,
                'extra_keys_qty' => 0,
                'extra_key_unit_price' => null,
                'is_included' => true,
            ];
        }

        if ($cilStd && $escEst) {
            $lines[] = [
                'order_id' => 3,
                'product_id' => $cilStd->id,
                'pack_id' => null,
                'quantity' => 2,
                'unit_price' => (float) $cilStd->price,
                'offer' => null,
                'keys_all_same' => false,
                'extra_keys_qty' => 1,
                'extra_key_unit_price' => $cilStd->extra_key_unit_price ? (float) $cilStd->extra_key_unit_price : null,
                'is_included' => true,
            ];
            $lines[] = [
                'order_id' => 3,
                'product_id' => $escEst->id,
                'pack_id' => null,
                'quantity' => 2,
                'unit_price' => (float) $escEst->price,
                'offer' => 5.00,
                'keys_all_same' => false,
                'extra_keys_qty' => 0,
                'extra_key_unit_price' => null,
                'is_included' => true,
            ];
        }

        if ($cilSeg) {
            $lines[] = [
                'order_id' => 4,
                'product_id' => $cilSeg->id,
                'pack_id' => null,
                'quantity' => 1,
                'unit_price' => (float) $cilSeg->price,
                'offer' => null,
                'keys_all_same' => false,
                'extra_keys_qty' => 0,
                'extra_key_unit_price' => null,
                'is_included' => true,
            ];
        }

        if ($spEst) {
            $lines[] = [
                'order_id' => 5,
                'product_id' => $spEst->id,
                'pack_id' => null,
                'quantity' => 1,
                'unit_price' => (float) $spEst->price,
                'offer' => null,
                'keys_all_same' => false,
                'extra_keys_qty' => 2,
                'extra_key_unit_price' => $spEst->extra_key_unit_price ? (float) $spEst->extra_key_unit_price : null,
                'is_included' => true,
            ];
        }

        $lines[] = [
            'order_id' => 6,
            'product_id' => null,
            'pack_id' => 1,
            'quantity' => 1,
            'unit_price' => 89.00,
            'offer' => null,
            'keys_all_same' => false,
            'extra_keys_qty' => 0,
            'extra_key_unit_price' => null,
            'is_included' => true,
        ];

        if ($cilStd && $escEst) {
            $lines[] = [
                'order_id' => 7,
                'product_id' => $cilStd->id,
                'pack_id' => null,
                'quantity' => 2,
                'unit_price' => null,
                'offer' => null,
                'keys_all_same' => false,
                'extra_keys_qty' => 1,
                'extra_key_unit_price' => null,
                'is_included' => true,
            ];
            $lines[] = [
                'order_id' => 7,
                'product_id' => $escEst->id,
                'pack_id' => null,
                'quantity' => 1,
                'unit_price' => null,
                'offer' => null,
                'keys_all_same' => false,
                'extra_keys_qty' => 0,
                'extra_key_unit_price' => null,
                'is_included' => true,
            ];
        }

        $lines[] = [
            'order_id' => 8,
            'product_id' => null,
            'pack_id' => 2,
            'quantity' => 1,
            'unit_price' => null,
            'offer' => null,
            'keys_all_same' => false,
            'extra_keys_qty' => 0,
            'extra_key_unit_price' => null,
            'is_included' => true,
        ];

        if ($spEst) {
            $lines[] = [
                'order_id' => 9,
                'product_id' => $spEst->id,
                'pack_id' => null,
                'quantity' => 1,
                'unit_price' => null,
                'offer' => null,
                'keys_all_same' => false,
                'extra_keys_qty' => 0,
                'extra_key_unit_price' => null,
                'is_included' => true,
            ];
        }

        if (! empty($lines)) {
            DB::table('order_lines')->insert($lines);
        }
    }
}
