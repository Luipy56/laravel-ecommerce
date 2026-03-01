<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('products')->insert([
            [
                'category_id' => 1,
                'code' => 'PB-80',
                'name' => 'Porta blindada 80cm',
                'description' => 'Porta d\'entrada blindada, 80 cm d\'amplada.',
                'price' => 450.00,
                'stock' => 10,
                'is_installable' => true,
                'installation_price' => 85.00,
                'is_extra_keys_available' => true,
                'extra_key_unit_price' => 25.00,
                'is_featured' => true,
                'is_trending' => false,
                'is_active' => true,
            ],
            [
                'category_id' => 1,
                'code' => 'PF-90',
                'name' => 'Porta de fusta 90cm',
                'description' => 'Porta de fusta massissa, 90 cm.',
                'price' => 320.00,
                'stock' => 5,
                'is_installable' => true,
                'installation_price' => 75.00,
                'is_extra_keys_available' => false,
                'extra_key_unit_price' => null,
                'is_featured' => false,
                'is_trending' => false,
                'is_active' => true,
            ],
            [
                'category_id' => 2,
                'code' => 'FC-120x100',
                'name' => 'Finestra corredissa 120x100',
                'description' => 'Finestra corredissa d\'alumini, 120x100 cm.',
                'price' => 280.00,
                'stock' => 15,
                'is_installable' => true,
                'installation_price' => 65.00,
                'is_extra_keys_available' => false,
                'extra_key_unit_price' => null,
                'is_featured' => false,
                'is_trending' => true,
                'is_active' => true,
            ],
            [
                'category_id' => 3,
                'code' => 'PE-EST',
                'name' => 'Persiana enrollable',
                'description' => 'Persiana enrollable aluminio, mida estàndard.',
                'price' => 95.00,
                'stock' => 30,
                'is_installable' => false,
                'installation_price' => null,
                'is_extra_keys_available' => false,
                'extra_key_unit_price' => null,
                'is_featured' => false,
                'is_trending' => false,
                'is_active' => true,
            ],
        ]);
    }
}
