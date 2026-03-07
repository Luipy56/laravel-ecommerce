<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('packs')->insert([
            ['name' => 'Pack cilindre + escut', 'description' => 'Cilindre estàndard i escut. Ideal per canviar cana.', 'price' => 46.00, 'is_trending' => true, 'is_active' => true, 'is_installable' => true, 'installation_price' => 25.00],
            ['name' => 'Pack seguretat', 'description' => 'Cilindre alta seguretat i escut reforçat.', 'price' => 130.00, 'is_trending' => true, 'is_active' => true, 'is_installable' => true, 'installation_price' => 45.00],
            ['name' => 'Pack segon pany', 'description' => 'Segon pany estàndard i cilindre 40mm.', 'price' => 83.00, 'is_trending' => false, 'is_active' => true, 'is_installable' => false, 'installation_price' => null],
        ]);
    }
}
