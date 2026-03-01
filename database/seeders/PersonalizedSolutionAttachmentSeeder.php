<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PersonalizedSolutionAttachmentSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('personalized_solution_attachments')->insert([
            ['personalized_solution_id' => 1, 'storage_path' => 'solutions/1/foto-marc.jpg', 'original_filename' => 'foto-marc.jpg', 'size_bytes' => 256000, 'checksum' => null, 'content_type' => 'image/jpeg', 'description' => null, 'is_active' => true],
            ['personalized_solution_id' => 1, 'storage_path' => 'solutions/1/pla.pdf', 'original_filename' => 'plà.pdf', 'size_bytes' => 120000, 'checksum' => null, 'content_type' => 'application/pdf', 'description' => null, 'is_active' => true],
            ['personalized_solution_id' => 2, 'storage_path' => 'solutions/2/balco.jpg', 'original_filename' => 'balco.jpg', 'size_bytes' => 310000, 'checksum' => null, 'content_type' => 'image/jpeg', 'description' => null, 'is_active' => true],
            ['personalized_solution_id' => 3, 'storage_path' => 'solutions/3/terrassa.pdf', 'original_filename' => 'terrassa.pdf', 'size_bytes' => 98000, 'checksum' => null, 'content_type' => 'application/pdf', 'description' => null, 'is_active' => true],
        ]);
    }
}
