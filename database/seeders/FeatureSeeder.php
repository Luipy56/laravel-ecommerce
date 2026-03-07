<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeatureSeeder extends Seeder
{
    /**
     * Feature values in Catalan (admin data). Storefront search will support ca/es.
     */
    public function run(): void
    {
        $features = [
            // 1 Color
            ['feature_name_id' => 1, 'value' => 'Plata', 'is_active' => true],
            ['feature_name_id' => 1, 'value' => 'Daurat', 'is_active' => true],
            ['feature_name_id' => 1, 'value' => 'Negre', 'is_active' => true],
            // 2 Tipus de clau
            ['feature_name_id' => 2, 'value' => 'General', 'is_active' => true],
            ['feature_name_id' => 2, 'value' => 'Seguretat', 'is_active' => true],
            ['feature_name_id' => 2, 'value' => 'Alta seguretat', 'is_active' => true],
            // 3 Mida (general measure, e.g. 30mm, 80x10mm)
            ['feature_name_id' => 3, 'value' => '30mm', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => '35mm', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => '40mm', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => '45mm', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => '50mm', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => '60mm', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => '70mm', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => '80mm', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => '90mm', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => '100mm', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => '80x10mm', 'is_active' => true],
            // 4 Mida interna (e.g. inside door for cylinder; format NNmm or NNxNNmm)
            ['feature_name_id' => 4, 'value' => '10mm', 'is_active' => true],
            ['feature_name_id' => 4, 'value' => '35mm', 'is_active' => true],
            ['feature_name_id' => 4, 'value' => '40mm', 'is_active' => true],
            ['feature_name_id' => 4, 'value' => '45mm', 'is_active' => true],
            ['feature_name_id' => 4, 'value' => '50mm', 'is_active' => true],
            ['feature_name_id' => 4, 'value' => '60mm', 'is_active' => true],
            ['feature_name_id' => 4, 'value' => '70mm', 'is_active' => true],
            ['feature_name_id' => 4, 'value' => '80mm', 'is_active' => true],
            ['feature_name_id' => 4, 'value' => '90mm', 'is_active' => true],
            ['feature_name_id' => 4, 'value' => '100mm', 'is_active' => true],
            ['feature_name_id' => 4, 'value' => '80x10mm', 'is_active' => true],
            // 5 Mida externa (e.g. outside door for cylinder; format NNmm or NNxNNmm)
            ['feature_name_id' => 5, 'value' => '10mm', 'is_active' => true],
            ['feature_name_id' => 5, 'value' => '35mm', 'is_active' => true],
            ['feature_name_id' => 5, 'value' => '40mm', 'is_active' => true],
            ['feature_name_id' => 5, 'value' => '45mm', 'is_active' => true],
            ['feature_name_id' => 5, 'value' => '50mm', 'is_active' => true],
            ['feature_name_id' => 5, 'value' => '60mm', 'is_active' => true],
            ['feature_name_id' => 5, 'value' => '70mm', 'is_active' => true],
            ['feature_name_id' => 5, 'value' => '80mm', 'is_active' => true],
            ['feature_name_id' => 5, 'value' => '90mm', 'is_active' => true],
            ['feature_name_id' => 5, 'value' => '100mm', 'is_active' => true],
            ['feature_name_id' => 5, 'value' => '80x10mm', 'is_active' => true],
        ];

        DB::table('features')->insert($features);
    }
}
