<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderDetailSeeder extends Seeder
{
    /**
     * Seeds order_lines. Productes segons enunciat: Cilindres, Escut, Segon pany.
     */
    public function run(): void
    {
        $cil30 = Product::where('code', 'CIL-30')->first();
        $escEst = Product::where('code', 'ESC-EST')->first();
        $spEst = Product::where('code', 'SP-EST')->first();

        $lines = [];

        if ($cil30) {
            $lines[] = [
                'order_id' => 1,
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
        }
        if ($escEst) {
            $lines[] = [
                'order_id' => 1,
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

        $lines[] = [
            'order_id' => 2,
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

        if ($spEst) {
            $lines[] = [
                'order_id' => 3,
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

        if (! empty($lines)) {
            DB::table('order_lines')->insert($lines);
        }
    }
}
