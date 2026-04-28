<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ClientContactSeeder extends Seeder
{
    /**
     * Seeds client_contacts (names, phones, email per client).
     */
    public function run(): void
    {
        $byEmail = [
            'maria.garcia@example.com' => ['name' => 'Maria', 'surname' => 'García', 'phone' => '612345678', 'phone2' => '934567890', 'email' => 'maria.garcia@example.com', 'is_primary' => true, 'is_active' => true],
            'joan.martinez@example.com' => ['name' => 'Juan', 'surname' => 'Martínez', 'phone' => '698765432', 'phone2' => null, 'email' => 'joan.martinez@example.com', 'is_primary' => true, 'is_active' => true],
            'anna.lopez@example.com' => ['name' => 'Ana', 'surname' => 'López', 'phone' => '655443322', 'phone2' => '931122334', 'email' => 'anna.lopez@example.com', 'is_primary' => true, 'is_active' => true],
        ];

        foreach ($byEmail as $loginEmail => $row) {
            $clientId = DB::table('clients')->where('login_email', $loginEmail)->value('id');
            if ($clientId === null) {
                throw new \RuntimeException(
                    "ClientContactSeeder: no client with login_email {$loginEmail}; ensure UserSeeder runs before this seeder.",
                );
            }
            DB::table('client_contacts')->insert(array_merge(['client_id' => $clientId], $row));
        }
    }
}
