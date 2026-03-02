<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductImageSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [];
        $sortOrder = 1;
        $placeholderSize = 120000;
        $contentType = 'image/jpeg';

        $fixturePath = database_path('seeders/fixtures/images.jpeg');

        foreach (Product::orderBy('id')->get() as $product) {
            if ($product->id === 1 && is_file($fixturePath)) {
                $storagePath = 'products/images.jpeg';
                Storage::disk('public')->put($storagePath, file_get_contents($fixturePath));
                $sizeBytes = (int) filesize($fixturePath);
                $rows[] = [
                    'product_id' => $product->id,
                    'storage_path' => $storagePath,
                    'filename' => 'images.jpeg',
                    'size_bytes' => $sizeBytes,
                    'checksum' => null,
                    'content_type' => $contentType,
                    'sort_order' => $sortOrder,
                    'is_active' => true,
                ];
                continue;
            }

            $baseName = 'product-' . $product->id;
            $filename = $baseName . '.jpg';
            $storagePath = 'products/' . $filename;
            $rows[] = [
                'product_id' => $product->id,
                'storage_path' => $storagePath,
                'filename' => $filename,
                'size_bytes' => $placeholderSize,
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
