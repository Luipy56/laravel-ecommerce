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
        ]);
    }
}
