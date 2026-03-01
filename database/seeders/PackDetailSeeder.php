<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackDetailSeeder extends Seeder
{
    /**
     * Seeds pack_items (products that belong to a pack).
     */
    public function run(): void
    {
        DB::table('pack_items')->insert([
            ['pack_id' => 1, 'product_id' => 1, 'is_active' => true],
            ['pack_id' => 1, 'product_id' => 4, 'is_active' => true],
            ['pack_id' => 2, 'product_id' => 1, 'is_active' => true],
            ['pack_id' => 2, 'product_id' => 3, 'is_active' => true],
            ['pack_id' => 3, 'product_id' => 2, 'is_active' => true],
        ]);
    }
}
