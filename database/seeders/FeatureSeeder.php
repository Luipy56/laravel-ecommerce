<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeatureSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('features')->insert([
            ['feature_name_id' => 1, 'value' => 'Fusta', 'is_active' => true],
            ['feature_name_id' => 1, 'value' => 'Alumini', 'is_active' => true],
            ['feature_name_id' => 1, 'value' => 'PVC', 'is_active' => true],
            ['feature_name_id' => 2, 'value' => 'Blanc', 'is_active' => true],
            ['feature_name_id' => 2, 'value' => 'Maró', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => '80cm', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => '120x100', 'is_active' => true],
        ]);
    }
}
