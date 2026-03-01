<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Seeds clients table (account: person or company).
     */
    public function run(): void
    {
        DB::table('clients')->insert([
            [
                'type' => 'person',
                'identification' => '12345678A',
                'login_email' => 'maria.garcia@example.com',
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
            [
                'type' => 'person',
                'identification' => '87654321B',
                'login_email' => 'joan.martinez@example.com',
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
            [
                'type' => 'person',
                'identification' => '11223344C',
                'login_email' => 'anna.lopez@example.com',
                'password' => Hash::make('password'),
                'is_active' => true,
            ],
        ]);
    }
}
