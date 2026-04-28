<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Pack;
use App\Models\PackItem;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogPacksOnlyTest extends TestCase
{
    use RefreshDatabase;

    public function test_packs_only_returns_only_pack_rows_with_pagination(): void
    {
        $category = ProductCategory::create([
            'code' => 'cat-packs-only',
            'name' => 'Cat packs only',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'PROD-PACK',
            'name' => 'Product in pack',
            'description' => null,
            'price' => 10.00,
            'stock' => 5,
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'code' => 'PROD-SOLO',
            'name' => 'Product solo',
            'description' => null,
            'price' => 5.00,
            'stock' => 2,
            'is_active' => true,
        ]);

        for ($i = 1; $i <= 12; $i++) {
            $pack = Pack::create([
                'name' => sprintf('Pack %02d', $i),
                'description' => null,
                'price' => 20.00,
                'is_active' => true,
            ]);
            PackItem::create([
                'pack_id' => $pack->id,
                'product_id' => $product->id,
                'is_active' => true,
            ]);
        }

        $r1 = $this->getJson('/api/v1/products?packs_only=1&per_page=5&page=1');
        $r1->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.total', 12)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.last_page', 3);

        $data1 = $r1->json('data');
        $this->assertCount(5, $data1);
        foreach ($data1 as $row) {
            $this->assertSame('pack', $row['type']);
            $this->assertArrayHasKey('data', $row);
        }

        $r2 = $this->getJson('/api/v1/products?packs_only=1&include_packs=0&per_page=5&page=2');
        $r2->assertOk();
        $this->assertCount(5, $r2->json('data'));
    }
}
