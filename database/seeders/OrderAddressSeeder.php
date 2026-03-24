<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrderAddressSeeder extends Seeder
{
    /**
     * Seeds order_addresses (demo copy in Spanish).
     */
    public function run(): void
    {
        DB::table('order_addresses')->insert([
            ['order_id' => 1, 'type' => 'shipping', 'street' => 'Calle Mayor 12', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08001', 'note' => null],
            ['order_id' => 1, 'type' => 'installation', 'street' => 'Calle Mayor 12', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08001', 'note' => null],
            ['order_id' => 2, 'type' => 'shipping', 'street' => 'Calle Mayor 12', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08001', 'note' => null],
            ['order_id' => 3, 'type' => 'shipping', 'street' => 'Plaza de Cataluña 5', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08002', 'note' => null],
            ['order_id' => 3, 'type' => 'installation', 'street' => 'Plaza de Cataluña 5', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08002', 'note' => 'Tercer piso'],
            ['order_id' => 4, 'type' => 'shipping', 'street' => 'Plaza de Cataluña 5', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08002', 'note' => null],
            ['order_id' => 4, 'type' => 'installation', 'street' => 'Plaza de Cataluña 5', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08002', 'note' => null],
            ['order_id' => 5, 'type' => 'shipping', 'street' => 'Avenida Diagonal 100', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08018', 'note' => null],
            ['order_id' => 5, 'type' => 'installation', 'street' => 'Avenida Diagonal 100', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08018', 'note' => 'Disponible a partir de las 15:00'],
            ['order_id' => 6, 'type' => 'shipping', 'street' => 'Calle Balmes 45', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08007', 'note' => null],
            ['order_id' => 7, 'type' => 'shipping', 'street' => 'Rambla de Cataluña 22', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08009', 'note' => null],
            ['order_id' => 8, 'type' => 'shipping', 'street' => 'Paseo de Gracia 88', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08008', 'note' => 'Puerta principal'],
            ['order_id' => 9, 'type' => 'shipping', 'street' => 'Calle Provenza 50', 'city' => 'Barcelona', 'province' => 'Barcelona', 'postal_code' => '08029', 'note' => null],
        ]);
    }
}
