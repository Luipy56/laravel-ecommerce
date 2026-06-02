<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeatureNameSeeder extends Seeder
{
    /**
     * Stable feature dimension codes with labels per locale (ca, es, en).
     */
    public function run(): void
    {
        $now = now();
        $rows = [
            [
                'code' => 'brand',
                'labels' => ['ca' => 'Marca', 'es' => 'Marca', 'en' => 'Brand'],
            ],
            [
                'code' => 'color',
                'labels' => ['ca' => 'Color', 'es' => 'Color', 'en' => 'Colour'],
            ],
            [
                'code' => 'key_type',
                'labels' => ['ca' => 'Tipus de clau', 'es' => 'Tipo de llave', 'en' => 'Key type'],
            ],
            [
                'code' => 'measure',
                'labels' => ['ca' => 'Mesura', 'es' => 'Medida', 'en' => 'Size'],
            ],
            [
                'code' => 'inner_measure',
                'labels' => ['ca' => 'Mesura interna', 'es' => 'Medida interna', 'en' => 'Inner size'],
            ],
            [
                'code' => 'outer_measure',
                'labels' => ['ca' => 'Mesura externa', 'es' => 'Medida externa', 'en' => 'Outer size'],
            ],
        ];

        foreach ($rows as $row) {
            $id = DB::table('feature_names')->insertGetId([
                'code' => $row['code'],
                'is_active' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
            foreach (['ca', 'es', 'en'] as $loc) {
                $label = $row['labels'][$loc] ?? $row['labels']['es'];
                DB::table('feature_name_translations')->insert([
                    'feature_name_id' => $id,
                    'locale' => $loc,
                    'name' => $label,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
