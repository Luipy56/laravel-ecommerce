<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        DB::table('packs')->insert([
            ['name' => 'Pack cilindro + escudo', 'description' => 'Cilindro estándar y escudo. Ideal para cambiar el bombín.', 'price' => 46.00, 'is_trending' => true, 'is_active' => true, 'contains_keys' => false, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Pack seguridad', 'description' => 'Cilindro alta seguridad y escudo reforzado.', 'price' => 130.00, 'is_trending' => true, 'is_active' => true, 'contains_keys' => false, 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Pack segundo cerrojo', 'description' => 'Segundo cerrojo estándar y cilindro 40 mm.', 'price' => 83.00, 'is_trending' => false, 'is_active' => true, 'contains_keys' => false, 'created_at' => $now, 'updated_at' => $now],
        ]);
    }
}
