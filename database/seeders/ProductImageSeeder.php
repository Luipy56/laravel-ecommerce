<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [];
        $sortOrder = 1;
        $sizeBytes = 120000;
        $contentType = 'image/jpeg';

        foreach (Product::orderBy('id')->get() as $product) {
            $baseName = 'product-' . $product->id;
            $filename = $baseName . '.jpg';
            $storagePath = 'products/' . $filename;
            $rows[] = [
                'product_id' => $product->id,
                'storage_path' => $storagePath,
                'filename' => $filename,
                'size_bytes' => $sizeBytes,
                'checksum' => null,
                'content_type' => $contentType,
                'sort_order' => $sortOrder,
                'is_active' => true,
            ];
        }

        if (! empty($rows)) {
            DB::table('product_images')->insert($rows);
        }
    }
}
