<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\ClientContact;
use Illuminate\Database\Seeder;

class ClientContactSeeder extends Seeder
{
    /**
     * Seeds client_contacts (names, phones, email per client).
     * Uses Eloquent so the encrypted cast on `phone`/`phone2` is applied correctly.
     */
    public function run(): void
    {
        $byEmail = [
            'maria.garcia@example.com' => ['name' => 'Maria', 'surname' => 'García', 'phone' => '612345678', 'phone2' => '934567890', 'email' => 'maria.garcia@example.com', 'is_primary' => true, 'is_active' => true],
            'joan.martinez@example.com' => ['name' => 'Juan', 'surname' => 'Martínez', 'phone' => '698765432', 'phone2' => null, 'email' => 'joan.martinez@example.com', 'is_primary' => true, 'is_active' => true],
            'anna.lopez@example.com' => ['name' => 'Ana', 'surname' => 'López', 'phone' => '655443322', 'phone2' => '931122334', 'email' => 'anna.lopez@example.com', 'is_primary' => true, 'is_active' => true],
        ];

        foreach ($byEmail as $loginEmail => $row) {
            $client = Client::query()->where('login_email', $loginEmail)->first();
            if ($client === null) {
                throw new \RuntimeException(
                    "ClientContactSeeder: no client with login_email {$loginEmail}; ensure UserSeeder runs before this seeder.",
                );
            }
            ClientContact::create(array_merge(['client_id' => $client->id], $row));
        }
    }
}
