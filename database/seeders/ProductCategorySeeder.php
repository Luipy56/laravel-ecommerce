<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductCategorySeeder extends Seeder
{
    /**
     * Demo categories in Spanish (Castilian).
     */
    public function run(): void
    {
        DB::table('product_categories')->insert([
            ['code' => 'cilindros', 'name' => 'Cilindros', 'is_active' => true],
            ['code' => 'escudo', 'name' => 'Escudo', 'is_active' => true],
            ['code' => 'segundo-cerrojo', 'name' => 'Segundo cerrojo', 'is_active' => true],
        ]);
    }
}
