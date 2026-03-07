<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderAddressSeeder extends Seeder
{
    /**
     * Seeds order_addresses (one row per address type per order: shipping, installation).
     * Orders 1-6 = kind order; 7-9 = kind cart.
     */
    public function run(): void
    {
        DB::table('order_addresses')->insert([
            ['order_id' => 1, 'type' => 'shipping', 'street' => 'Carrer Major 12', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08001', 'note' => null],
            ['order_id' => 1, 'type' => 'installation', 'street' => 'Carrer Major 12', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08001', 'note' => null],
            ['order_id' => 2, 'type' => 'shipping', 'street' => 'Carrer Major 12', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08001', 'note' => null],
            ['order_id' => 3, 'type' => 'shipping', 'street' => 'Plaça Catalunya 5', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08002', 'note' => null],
            ['order_id' => 3, 'type' => 'installation', 'street' => 'Plaça Catalunya 5', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08002', 'note' => 'Tercer pis'],
            ['order_id' => 4, 'type' => 'shipping', 'street' => 'Plaça Catalunya 5', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08002', 'note' => null],
            ['order_id' => 4, 'type' => 'installation', 'street' => 'Plaça Catalunya 5', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08002', 'note' => null],
            ['order_id' => 5, 'type' => 'shipping', 'street' => 'Avinguda Diagonal 100', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08018', 'note' => null],
            ['order_id' => 5, 'type' => 'installation', 'street' => 'Avinguda Diagonal 100', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08018', 'note' => 'Disponible a partir de les 15h'],
            ['order_id' => 6, 'type' => 'shipping', 'street' => 'Carrer Balmes 45', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08007', 'note' => null],
            ['order_id' => 7, 'type' => 'shipping', 'street' => 'Rambla Catalunya 22', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08009', 'note' => null],
            ['order_id' => 8, 'type' => 'shipping', 'street' => 'Pg. de Gràcia 88', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08008', 'note' => 'Porta principal'],
            ['order_id' => 9, 'type' => 'shipping', 'street' => 'Carrer Provença 50', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08029', 'note' => null],
        ]);
    }
}
