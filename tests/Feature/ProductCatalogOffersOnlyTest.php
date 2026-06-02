<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Pack;
use App\Models\PackItem;
use App\Support\CatalogTranslationSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogOffersOnlyTest extends TestCase
{
    use RefreshDatabase;

    public function test_offers_only_returns_only_discounted_products_and_packs(): void
    {
        $category = $this->createProductCategoryForTests('cat-offers', 'Offers Cat');

        $discounted = $this->createProductForTests($category->id, 'PROD-DISC', 'Discounted Product', null, [
            'price' => 100,
            'discount_percent' => 10,
            'stock' => 5,
        ]);
        $this->createProductForTests($category->id, 'PROD-FULL', 'Full Price Product', null, [
            'price' => 50,
            'stock' => 3,
        ]);

        $packWithDiscount = Pack::create([
            'price' => 80.00,
            'discount_percent' => 15,
            'is_active' => true,
            'is_trending' => false,
            'contains_keys' => false,
        ]);
        CatalogTranslationSync::syncPackTranslations($packWithDiscount, [
            'ca' => ['name' => 'Pack amb descompte', 'description' => null],
            'es' => ['name' => 'Pack con descuento', 'description' => null],
            'en' => ['name' => 'Pack with discount', 'description' => null],
        ]);
        PackItem::create(['pack_id' => $packWithDiscount->id, 'product_id' => $discounted->id, 'is_active' => true]);

        $packNoDiscount = Pack::create([
            'price' => 60.00,
            'discount_percent' => null,
            'is_active' => true,
            'is_trending' => false,
            'contains_keys' => false,
        ]);
        CatalogTranslationSync::syncPackTranslations($packNoDiscount, [
            'ca' => ['name' => 'Pack sense descompte', 'description' => null],
            'es' => ['name' => 'Pack sin descuento', 'description' => null],
            'en' => ['name' => 'Pack without discount', 'description' => null],
        ]);
        PackItem::create(['pack_id' => $packNoDiscount->id, 'product_id' => $discounted->id, 'is_active' => true]);

        $r = $this->getJson('/api/v1/products?offers_only=1&include_packs=1');
        $r->assertOk()->assertJsonPath('success', true);

        $data = $r->json('data');
        $this->assertCount(2, $data, 'Should return exactly one discounted product and one discounted pack.');

        $types = array_column($data, 'type');
        $this->assertContains('product', $types);
        $this->assertContains('pack', $types);

        $productRow = collect($data)->firstWhere('type', 'product');
        $this->assertEquals($discounted->id, $productRow['data']['id']);

        $packRow = collect($data)->firstWhere('type', 'pack');
        $this->assertEquals($packWithDiscount->id, $packRow['data']['id']);
    }

    public function test_offers_only_without_discounted_items_returns_empty(): void
    {
        $category = $this->createProductCategoryForTests('cat-offers-empty', 'Empty Offers Cat');
        $this->createProductForTests($category->id, 'PROD-NDISC', 'No Discount', null, ['price' => 20, 'stock' => 1]);

        $r = $this->getJson('/api/v1/products?offers_only=1&include_packs=1');
        $r->assertOk()->assertJsonPath('success', true)->assertJsonPath('meta.total', 0);
        $this->assertCount(0, $r->json('data'));
    }

    public function test_offers_only_pack_discount_fields_in_response(): void
    {
        $category = $this->createProductCategoryForTests('cat-offers-fields', 'Fields Cat');
        $prod = $this->createProductForTests($category->id, 'PROD-F1', 'Field Product', null, [
            'price' => 100,
            'discount_percent' => 20,
            'stock' => 2,
        ]);

        $pack = Pack::create([
            'price' => 50.00,
            'discount_percent' => 25,
            'is_active' => true,
            'is_trending' => false,
            'contains_keys' => false,
        ]);
        CatalogTranslationSync::syncPackTranslations($pack, [
            'ca' => ['name' => 'Pack Fields', 'description' => null],
            'es' => ['name' => 'Pack Fields', 'description' => null],
            'en' => ['name' => 'Pack Fields', 'description' => null],
        ]);
        PackItem::create(['pack_id' => $pack->id, 'product_id' => $prod->id, 'is_active' => true]);

        $r = $this->getJson('/api/v1/products?offers_only=1&include_packs=1');
        $r->assertOk();

        $packRow = collect($r->json('data'))->firstWhere('type', 'pack');
        $this->assertNotNull($packRow);
        $this->assertEquals(25.0, $packRow['data']['discount_percent']);
        $this->assertEquals(50.0, $packRow['data']['list_price']);
        $this->assertEquals(37.5, $packRow['data']['price']); // 50 * (1 - 0.25)

        $productRow = collect($r->json('data'))->firstWhere('type', 'product');
        $this->assertNotNull($productRow);
        $this->assertEquals(20.0, $productRow['data']['discount_percent']);
        $this->assertEquals(80.0, $productRow['data']['price']); // 100 * (1 - 0.20)
    }
}
