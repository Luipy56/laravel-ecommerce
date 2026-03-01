<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductCategorySeeder extends Seeder
{
    public function run(): void
    {
        DB::table('product_categories')->insert([
            ['code' => 'portes', 'name' => 'Portes', 'is_active' => true],
            ['code' => 'finestres', 'name' => 'Finestres', 'is_active' => true],
            ['code' => 'persianes', 'name' => 'Persianes', 'is_active' => true],
        ]);
    }
}
