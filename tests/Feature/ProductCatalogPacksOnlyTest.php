<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Pack;
use App\Models\PackItem;
use App\Support\CatalogTranslationSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogPacksOnlyTest extends TestCase
{
    use RefreshDatabase;

    public function test_packs_only_returns_only_pack_rows_with_pagination(): void
    {
        $category = $this->createProductCategoryForTests('cat-packs-only', 'Cat packs only');

        $product = $this->createProductForTests($category->id, 'PROD-PACK', 'Product in pack', null, ['price' => 10, 'stock' => 5]);
        $this->createProductForTests($category->id, 'PROD-SOLO', 'Product solo', null, ['price' => 5, 'stock' => 2]);

        for ($i = 1; $i <= 12; $i++) {
            $name = sprintf('Pack %02d', $i);
            $pack = Pack::create([
                'price' => 20.00,
                'is_active' => true,
                'is_trending' => false,
                'contains_keys' => false,
            ]);
            CatalogTranslationSync::syncPackTranslations($pack, [
                'ca' => ['name' => $name, 'description' => null],
                'es' => ['name' => $name, 'description' => null],
                'en' => ['name' => $name, 'description' => null],
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
