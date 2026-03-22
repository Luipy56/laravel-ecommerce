<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackDetailSeeder extends Seeder
{
    /**
     * Seeds pack_items (demo catalog in Spanish).
     */
    public function run(): void
    {
        $items = [
            ['pack_id' => 1, 'product_code' => '192 evoK1C 3030 N'],
            ['pack_id' => 1, 'product_code' => 'ESC-ABUS-PLATA'],
            ['pack_id' => 2, 'product_code' => '192 evoK1D 3040 N'],
            ['pack_id' => 2, 'product_code' => 'ESC-DISEC-BD280-PLATA'],
            ['pack_id' => 3, 'product_code' => 'SP-MC-EZC-OFR'],
            ['pack_id' => 3, 'product_code' => '192 evoK1C 3040 N'],
        ];

        $rows = [];
        foreach ($items as $item) {
            $productId = Product::where('code', $item['product_code'])->value('id');
            if ($productId) {
                $rows[] = ['pack_id' => $item['pack_id'], 'product_id' => $productId, 'is_active' => true];
            }
        }

        if (! empty($rows)) {
            DB::table('pack_items')->insert($rows);
        }
    }
}
