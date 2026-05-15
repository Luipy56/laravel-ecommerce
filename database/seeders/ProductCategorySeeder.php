<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductCategorySeeder extends Seeder
{
    /**
     * Demo categories with translations (ca, es, en).
     */
    public function run(): void
    {
        $now = now();
        $defs = [
            'cilindros' => [
                'ca' => 'Cilindres',
                'es' => 'Cilindros',
                'en' => 'Cylinders',
            ],
            'escudo' => [
                'ca' => 'Escut',
                'es' => 'Escudo',
                'en' => 'Shield',
            ],
            'segundo-cerrojo' => [
                'ca' => 'Segon pestell',
                'es' => 'Segundo cerrojo',
                'en' => 'Secondary lock',
            ],
        ];

        foreach ($defs as $code => $labels) {
            $id = DB::table('product_categories')->insertGetId([
                'code' => $code,
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            foreach (['ca', 'es', 'en'] as $loc) {
                DB::table('product_category_translations')->insert([
                    'product_category_id' => $id,
                    'locale' => $loc,
                    'name' => $labels[$loc],
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
