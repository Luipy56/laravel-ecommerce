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
        DB::table('client_contacts')->insert([
            ['client_id' => 1, 'name' => 'Maria', 'surname' => 'García', 'phone' => '612345678', 'phone2' => '934567890', 'email' => 'maria.garcia@example.com', 'is_primary' => true, 'is_active' => true],
            ['client_id' => 2, 'name' => 'Joan', 'surname' => 'Martínez', 'phone' => '698765432', 'phone2' => null, 'email' => 'joan.martinez@example.com', 'is_primary' => true, 'is_active' => true],
            ['client_id' => 3, 'name' => 'Anna', 'surname' => 'López', 'phone' => '655443322', 'phone2' => '931122334', 'email' => 'anna.lopez@example.com', 'is_primary' => true, 'is_active' => true],
        ]);
    }
}
