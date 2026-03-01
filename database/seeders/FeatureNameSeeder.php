<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeatureNameSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('feature_names')->insert([
            ['name' => 'Material', 'is_active' => true],
            ['name' => 'Color', 'is_active' => true],
            ['name' => 'Mida', 'is_active' => true],
            ['name' => 'Acabament', 'is_active' => true],
            ['name' => 'Tipus obertura', 'is_active' => true],
            ['name' => 'Resistència foc', 'is_active' => true],
            ['name' => 'Aïllament tèrmic', 'is_active' => true],
            ['name' => 'Aïllament acústic', 'is_active' => true],
            ['name' => 'Seguretat', 'is_active' => true],
            ['name' => 'Normativa', 'is_active' => true],
            ['name' => 'Orientació', 'is_active' => true],
            ['name' => 'Tipus muntatge', 'is_active' => true],
            ['name' => 'Vidre', 'is_active' => true],
            ['name' => 'Marc', 'is_active' => true],
            ['name' => 'Tipus persiana', 'is_active' => true],
        ]);
    }
}
