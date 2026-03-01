<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderAddressSeeder extends Seeder
{
    /**
     * Seeds order_addresses (one row per address type per order: shipping, installation).
     */
    public function run(): void
    {
        DB::table('order_addresses')->insert([
            ['order_id' => 1, 'type' => 'shipping', 'street' => 'Carrer Major 12', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08001', 'note' => null],
            ['order_id' => 1, 'type' => 'installation', 'street' => 'Carrer Major 12', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08001', 'note' => null],
            ['order_id' => 2, 'type' => 'shipping', 'street' => 'Plaça Catalunya 5', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08002', 'note' => null],
            ['order_id' => 3, 'type' => 'shipping', 'street' => 'Avinguda Diagonal 100', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08018', 'note' => null],
            ['order_id' => 3, 'type' => 'installation', 'street' => 'Avinguda Diagonal 100', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08018', 'note' => 'Disponible a partir de les 15h'],
        ]);
    }
}
