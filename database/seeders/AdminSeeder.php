<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('admins')->insert([
            ['username' => 'admin', 'password' => Hash::make('password'), 'is_active' => true],
            ['username' => 'manager', 'password' => Hash::make('password'), 'is_active' => true],
            ['username' => 'support', 'password' => Hash::make('password'), 'is_active' => true],
        ]);
    }
}
