<?php

namespace Database\Seeders;

use App\Models\Client;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seeds clients table (account: person or company).
     * Uses Eloquent so the encrypted cast on `identification` is applied correctly.
     */
    public function run(): void
    {
        $clients = [
            [
                'type' => 'person',
                'identification' => '12345678A',
                'login_email' => 'maria.garcia@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ],
            [
                'type' => 'person',
                'identification' => '87654321B',
                'login_email' => 'joan.martinez@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ],
            [
                'type' => 'person',
                'identification' => '11223344C',
                'login_email' => 'anna.lopez@example.com',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
                'is_active' => true,
            ],
        ];

        foreach ($clients as $data) {
            Client::create($data);
        }
    }
}
