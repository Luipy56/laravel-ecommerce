<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('packs')->insert([
            ['name' => 'Pack entrada completa', 'description' => 'Porta blindada + persiana. Ideal per a reformes.', 'price' => 699.00, 'is_trending' => true, 'is_active' => true],
            ['name' => 'Pack premium', 'description' => 'Porta blindada + finestra. Qualitat màxima.', 'price' => 899.00, 'is_trending' => true, 'is_active' => true],
            ['name' => 'Pack bàsic', 'description' => 'Porta de fusta. Bon preu.', 'price' => 549.00, 'is_trending' => false, 'is_active' => true],
        ]);
    }
}
