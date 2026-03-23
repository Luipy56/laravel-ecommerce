<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeatureNameSeeder extends Seeder
{
    /**
     * Feature type names in Spanish (Castilian) for seeded catalog data.
     */
    public function run(): void
    {
        DB::table('feature_names')->insert([
            ['name' => 'Marca', 'is_active' => true],
            ['name' => 'Color', 'is_active' => true],
            ['name' => 'Tipo de llave', 'is_active' => true],
            ['name' => 'Medida', 'is_active' => true],
            ['name' => 'Medida interna', 'is_active' => true],
            ['name' => 'Medida externa', 'is_active' => true],
        ]);
    }
}
