<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PackImageSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('pack_images')->insert([
            ['pack_id' => 1, 'storage_path' => 'packs/pack-complet-1.jpg', 'filename' => 'pack-complet-1.jpg', 'size_bytes' => 130000, 'checksum' => null, 'content_type' => 'image/jpeg', 'sort_order' => 1, 'is_active' => true],
            ['pack_id' => 1, 'storage_path' => 'packs/pack-complet-2.jpg', 'filename' => 'pack-complet-2.jpg', 'size_bytes' => 105000, 'checksum' => null, 'content_type' => 'image/jpeg', 'sort_order' => 2, 'is_active' => true],
            ['pack_id' => 2, 'storage_path' => 'packs/pack-premium-1.jpg', 'filename' => 'pack-premium-1.jpg', 'size_bytes' => 115000, 'checksum' => null, 'content_type' => 'image/jpeg', 'sort_order' => 1, 'is_active' => true],
            ['pack_id' => 2, 'storage_path' => 'packs/pack-premium-2.jpg', 'filename' => 'pack-premium-2.jpg', 'size_bytes' => 99000, 'checksum' => null, 'content_type' => 'image/jpeg', 'sort_order' => 2, 'is_active' => true],
            ['pack_id' => 3, 'storage_path' => 'packs/pack-basic-1.jpg', 'filename' => 'pack-basic-1.jpg', 'size_bytes' => 92000, 'checksum' => null, 'content_type' => 'image/jpeg', 'sort_order' => 1, 'is_active' => true],
        ]);
    }
}
