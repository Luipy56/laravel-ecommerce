<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        DB::table('product_images')->insert([
            ['product_id' => 1, 'storage_path' => 'products/porta-blindada-1.jpg', 'filename' => 'porta-blindada-1.jpg', 'size_bytes' => 120000, 'checksum' => null, 'content_type' => 'image/jpeg', 'sort_order' => 1, 'is_active' => true],
            ['product_id' => 1, 'storage_path' => 'products/porta-blindada-2.jpg', 'filename' => 'porta-blindada-2.jpg', 'size_bytes' => 98000, 'checksum' => null, 'content_type' => 'image/jpeg', 'sort_order' => 2, 'is_active' => true],
            ['product_id' => 2, 'storage_path' => 'products/porta-fusta-1.jpg', 'filename' => 'porta-fusta-1.jpg', 'size_bytes' => 110000, 'checksum' => null, 'content_type' => 'image/jpeg', 'sort_order' => 1, 'is_active' => true],
            ['product_id' => 3, 'storage_path' => 'products/finestra-1.jpg', 'filename' => 'finestra-1.jpg', 'size_bytes' => 95000, 'checksum' => null, 'content_type' => 'image/jpeg', 'sort_order' => 1, 'is_active' => true],
            ['product_id' => 4, 'storage_path' => 'products/persiana-1.jpg', 'filename' => 'persiana-1.jpg', 'size_bytes' => 88000, 'checksum' => null, 'content_type' => 'image/jpeg', 'sort_order' => 1, 'is_active' => true],
        ]);
    }
}
