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
                'locales' => [
                    'ca' => [
                        'name' => 'Pack cilindre + escut',
                        'description' => 'Cilindre estàndard i escut. Ideal per canviar el bombí de la porta.',
                    ],
                    'es' => [
                        'name' => 'Pack cilindro + escudo',
                        'description' => 'Cilindro estándar y escudo. Ideal para cambiar el bombín de la puerta.',
                    ],
                    'en' => [
                        'name' => 'Cylinder + shield pack',
                        'description' => 'Standard cylinder and door shield. Ideal for replacing your door lock.',
                    ],
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
                'locales' => [
                    'ca' => [
                        'name' => 'Pack seguretat',
                        'description' => 'Cilindre d\'alta seguretat i escut reforçat. Màxima protecció per a la teva porta.',
                    ],
                    'es' => [
                        'name' => 'Pack seguridad',
                        'description' => 'Cilindro alta seguridad y escudo reforzado. Máxima protección para tu puerta.',
                    ],
                    'en' => [
                        'name' => 'Security pack',
                        'description' => 'High-security cylinder and reinforced shield. Maximum protection for your door.',
                    ],
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
                'locales' => [
                    'ca' => [
                        'name' => 'Pack segon pestell',
                        'description' => 'Segon pestell estàndard i cilindre de 40 mm. Doble bloqueig per a la màxima seguretat.',
                    ],
                    'es' => [
                        'name' => 'Pack segundo cerrojo',
                        'description' => 'Segundo cerrojo estándar y cilindro de 40 mm. Doble cierre para máxima seguridad.',
                    ],
                    'en' => [
                        'name' => 'Second deadbolt pack',
                        'description' => 'Standard second deadbolt and 40 mm cylinder. Double locking for maximum security.',
                    ],
                ],
            ],
        ];

        foreach ($packs as $entry) {
            $id = DB::table('packs')->insertGetId($entry['row']);

            foreach (['ca', 'es', 'en'] as $loc) {
                $t = $entry['locales'][$loc];
                DB::table('pack_translations')->insert([
                    'pack_id' => $id,
                    'locale' => $loc,
                    'name' => $t['name'],
                    'description' => $t['description'],
                    'search_text' => Product::normalizeSearchText($t['name'], '', $t['description']),
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }
}
