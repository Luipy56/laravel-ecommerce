<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AddressSeeder extends Seeder
{
    /**
     * Seeds client_addresses (demo addresses in Spanish).
     */
    public function run(): void
    {
        DB::table('client_addresses')->insert([
            ['client_id' => 1, 'type' => 'shipping', 'label' => 'Casa', 'street' => 'Calle Mayor 12', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08001', 'is_active' => true],
            ['client_id' => 2, 'type' => 'shipping', 'label' => null, 'street' => 'Plaza de Cataluña 5', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08002', 'is_active' => true],
            ['client_id' => 3, 'type' => 'shipping', 'label' => null, 'street' => 'Avenida Diagonal 100', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08018', 'is_active' => true],
        ]);
    }
}
