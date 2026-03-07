<?php

namespace Database\Seeders;

use App\Models\Admin;
use Illuminate\Database\Seeder;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $admins = [
            ['username' => 'admin', 'password' => 'admin', 'is_active' => true],
            ['username' => 'luipy', 'password' => 'admin', 'is_active' => true],
            ['username' => 'manager', 'password' => 'admin', 'is_active' => true],
            ['username' => 'support', 'password' => 'admin', 'is_active' => true],
        ];

        foreach ($admins as $data) {
            Admin::create($data);
        }
    }
}
