<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackDetailSeeder extends Seeder
{
    /**
     * Seeds pack_items. Productes segons enunciat: Cilindres, Escut, Segon pany.
     */
    public function run(): void
    {
        $items = [
            ['pack_id' => 1, 'product_code' => 'CIL-30'],
            ['pack_id' => 1, 'product_code' => 'ESC-EST'],
            ['pack_id' => 2, 'product_code' => 'CIL-SEG'],
            ['pack_id' => 2, 'product_code' => 'ESC-SEG'],
            ['pack_id' => 3, 'product_code' => 'SP-EST'],
            ['pack_id' => 3, 'product_code' => 'CIL-40'],
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
