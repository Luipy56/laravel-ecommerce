<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductFeatureSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('product_features')->insert([
            ['product_id' => 1, 'feature_id' => 1],
            ['product_id' => 1, 'feature_id' => 4],
            ['product_id' => 2, 'feature_id' => 1],
            ['product_id' => 2, 'feature_id' => 5],
            ['product_id' => 3, 'feature_id' => 2],
            ['product_id' => 4, 'feature_id' => 2],
        ]);
    }
}
