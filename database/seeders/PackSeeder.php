<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $packs = [
            [
                'row' => [
                    'price' => 46.00,
                    'is_trending' => true,
                    'is_active' => true,
                    'contains_keys' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                'es' => [
                    'name' => 'Pack cilindro + escudo',
                    'description' => 'Cilindro estándar y escudo. Ideal para cambiar el bombín.',
                ],
            ],
            [
                'row' => [
                    'price' => 130.00,
                    'is_trending' => true,
                    'is_active' => true,
                    'contains_keys' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                'es' => [
                    'name' => 'Pack seguridad',
                    'description' => 'Cilindro alta seguridad y escudo reforzado.',
                ],
            ],
            [
                'row' => [
                    'price' => 83.00,
                    'is_trending' => false,
                    'is_active' => true,
                    'contains_keys' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                'es' => [
                    'name' => 'Pack segundo cerrojo',
                    'description' => 'Segundo cerrojo estándar y cilindro 40 mm.',
                ],
            ],
        ];

        foreach ($packs as $entry) {
            $id = DB::table('packs')->insertGetId($entry['row']);
            $es = $entry['es'];
            foreach (['ca', 'es', 'en'] as $loc) {
                $pName = $es['name'];
                $pDesc = $es['description'];
                $st = Product::normalizeSearchText($pName, '', $pDesc);
                DB::table('pack_translations')->insert([
                    'pack_id' => $id,
                    'locale' => $loc,
                    'name' => $pName,
                    'description' => $pDesc,
                    'search_text' => $st,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
