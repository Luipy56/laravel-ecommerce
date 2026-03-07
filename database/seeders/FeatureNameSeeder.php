<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeatureNameSeeder extends Seeder
{
    /**
     * Feature type names in Catalan (admin typically uploads data in Catalan).
     * Storefront search will support both Catalan and Spanish for client-facing queries.
     */
    public function run(): void
    {
        DB::table('feature_names')->insert([
            ['name' => 'Color', 'is_active' => true],
            ['name' => 'Tipus de clau', 'is_active' => true],
            ['name' => 'Mida', 'is_active' => true],
            ['name' => 'Mida interna', 'is_active' => true],
            ['name' => 'Mida externa', 'is_active' => true],
        ]);
    }
}
