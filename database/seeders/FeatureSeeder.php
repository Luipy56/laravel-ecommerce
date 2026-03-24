<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeatureSeeder extends Seeder
{
    /**
     * Feature values in Spanish (Castilian) for the catalog from the product list spreadsheet.
     * feature_name_id: 1 Marca, 2 Color, 3 Tipo de llave, 4 Medida, 5 Medida interna, 6 Medida externa.
     */
    public function run(): void
    {
        $features = [
            // 1 Marca
            ['feature_name_id' => 1, 'value' => 'Securemme', 'is_active' => true],
            ['feature_name_id' => 1, 'value' => 'M&C', 'is_active' => true],
            ['feature_name_id' => 1, 'value' => 'Keso', 'is_active' => true],
            ['feature_name_id' => 1, 'value' => 'Abus', 'is_active' => true],
            ['feature_name_id' => 1, 'value' => 'DMC', 'is_active' => true],
            ['feature_name_id' => 1, 'value' => 'Disec', 'is_active' => true],
            // 2 Color
            ['feature_name_id' => 2, 'value' => 'Plata', 'is_active' => true],
            ['feature_name_id' => 2, 'value' => 'Dorado', 'is_active' => true],
            // 3 Tipo de llave
            ['feature_name_id' => 3, 'value' => 'Puntos copiables', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => 'Puntos no copiables', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => 'Elemento móvil', 'is_active' => true],
            ['feature_name_id' => 3, 'value' => 'Codificación magnética', 'is_active' => true],
            // 5 Medida interna
            ['feature_name_id' => 5, 'value' => '30mm', 'is_active' => true],
            ['feature_name_id' => 5, 'value' => '32mm', 'is_active' => true],
            ['feature_name_id' => 5, 'value' => '40mm', 'is_active' => true],
            // 6 Medida externa
            ['feature_name_id' => 6, 'value' => '30mm', 'is_active' => true],
            ['feature_name_id' => 6, 'value' => '32mm', 'is_active' => true],
            ['feature_name_id' => 6, 'value' => '40mm', 'is_active' => true],
        ];

        DB::table('features')->insert($features);
    }
}
