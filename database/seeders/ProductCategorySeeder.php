<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductCategorySeeder extends Seeder
{
    /**
     * Categories per enunciat: només Cilindres, Escut, Segon pany.
     */
    public function run(): void
    {
        DB::table('product_categories')->insert([
            ['code' => 'cilindres', 'name' => 'Cilindres', 'is_active' => true],
            ['code' => 'escut', 'name' => 'Escut', 'is_active' => true],
            ['code' => 'segon-pany', 'name' => 'Segon pany', 'is_active' => true],
        ]);
    }
}
