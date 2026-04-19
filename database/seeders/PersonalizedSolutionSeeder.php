<?php

namespace Database\Seeders;

use App\Models\PersonalizedSolution;
use Illuminate\Database\Seeder;

class PersonalizedSolutionSeeder extends Seeder
{
    /**
     * Seeds personalized_solutions (demo copy in Spanish).
     */
    public function run(): void
    {
        PersonalizedSolution::query()->create([
            'client_id' => 1,
            'order_id' => null,
            'email' => 'maria.garcia@example.com',
            'phone' => '612345678',
            'address_street' => 'Calle Mayor 12',
            'address_city' => 'Barcelona',
            'address_province' => 'Barcelona',
            'address_postal_code' => '08001',
            'address_note' => null,
            'problem_description' => 'Necesito una puerta que se adapte a un marco irregular.',
            'resolution' => null,
            'status' => PersonalizedSolution::STATUS_PENDING_REVIEW,
            'is_active' => true,
        ]);

        PersonalizedSolution::query()->create([
            'client_id' => 2,
            'order_id' => null,
            'email' => 'joan.martinez@example.com',
            'phone' => '698765432',
            'address_street' => 'Plaza de Cataluña 5',
            'address_city' => 'Barcelona',
            'address_province' => 'Barcelona',
            'address_postal_code' => '08002',
            'address_note' => null,
            'problem_description' => 'Consulta sobre ventanas para un balcón en curva.',
            'resolution' => null,
            'status' => PersonalizedSolution::STATUS_PENDING_REVIEW,
            'is_active' => true,
        ]);

        PersonalizedSolution::query()->create([
            'client_id' => null,
            'order_id' => null,
            'email' => 'visitant@example.com',
            'phone' => '666111222',
            'address_street' => null,
            'address_city' => null,
            'address_province' => null,
            'address_postal_code' => null,
            'address_note' => null,
            'problem_description' => 'Medida especial para persiana en terraza.',
            'resolution' => 'Se ha recomendado el modelo X a medida.',
            'status' => PersonalizedSolution::STATUS_CLIENT_CONTACTED,
            'is_active' => true,
        ]);
    }
}
