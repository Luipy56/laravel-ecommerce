<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonalizedSolutionSeeder extends Seeder
{
    /**
     * Seeds personalized_solutions. Can be independent of client and order (client_id, order_id null).
     * status: pending_review | reviewed | client_contacted.
     */
    public function run(): void
    {
        DB::table('personalized_solutions')->insert([
            [
                'client_id' => 1,
                'order_id' => null,
                'email' => 'maria.garcia@example.com',
                'phone' => '612345678',
                'address_street' => 'Carrer Major 12',
                'address_city' => 'Barcelona',
                'address_province' => 'Barcelona',
                'address_postal_code' => '08001',
                'address_note' => null,
                'problem_description' => 'Necessito una porta que s\'adapti a un marc irregular.',
                'resolution' => null,
                'status' => 'pending_review',
                'is_active' => true,
            ],
            [
                'client_id' => 2,
                'order_id' => null,
                'email' => 'joan.martinez@example.com',
                'phone' => '698765432',
                'address_street' => 'Plaça Catalunya 5',
                'address_city' => 'Barcelona',
                'address_province' => 'Barcelona',
                'address_postal_code' => '08002',
                'address_note' => null,
                'problem_description' => 'Consulta sobre finestres per a un balcó corbat.',
                'resolution' => null,
                'status' => 'pending_review',
                'is_active' => true,
            ],
            [
                'client_id' => null,
                'order_id' => null,
                'email' => 'visitant@example.com',
                'phone' => '666111222',
                'address_street' => null,
                'address_city' => null,
                'address_province' => null,
                'address_postal_code' => null,
                'address_note' => null,
                'problem_description' => 'Mida especial per persiana a terrassa.',
                'resolution' => 'S\'ha recomanat el model X amb mida a mida.',
                'status' => 'client_contacted',
                'is_active' => true,
            ],
        ]);
    }
}
