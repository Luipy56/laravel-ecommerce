<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Feature;
use App\Models\FeatureName;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogFeatureFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_characteristic_values_are_ored(): void
    {
        $category = ProductCategory::create([
            'code' => 'cat-ff1',
            'name' => 'FF category',
            'is_active' => true,
        ]);
        $marca = FeatureName::create(['name' => 'Marca', 'is_active' => true]);
        $fAbus = Feature::create(['feature_name_id' => $marca->id, 'value' => 'Abus', 'is_active' => true]);
        $fDisec = Feature::create(['feature_name_id' => $marca->id, 'value' => 'Disec', 'is_active' => true]);

        $p1 = Product::create([
            'category_id' => $category->id,
            'code' => 'P-ABUS',
            'name' => 'Only Abus',
            'description' => null,
            'price' => 1.0,
            'stock' => 1,
            'is_active' => true,
        ]);
        $p1->features()->sync([$fAbus->id]);
        $p2 = Product::create([
            'category_id' => $category->id,
            'code' => 'P-DISEC',
            'name' => 'Only Disec',
            'description' => null,
            'price' => 1.0,
            'stock' => 1,
            'is_active' => true,
        ]);
        $p2->features()->sync([$fDisec->id]);

        $response = $this->getJson(
            '/api/v1/products?include_packs=0&feature_ids[]='.$fAbus->id.'&feature_ids[]='.$fDisec->id
        );
        $response->assertOk()->assertJsonPath('success', true);
        $ids = collect($response->json('data'))->pluck('id')->sort()->values()->all();
        $this->assertEqualsCanonicalizing([$p1->id, $p2->id], $ids);
    }

    public function test_different_characteristic_groups_use_and(): void
    {
        $category = ProductCategory::create([
            'code' => 'cat-ff2',
            'name' => 'FF2 category',
            'is_active' => true,
        ]);
        $marca = FeatureName::create(['name' => 'Marca', 'is_active' => true]);
        $color = FeatureName::create(['name' => 'Color', 'is_active' => true]);
        $fAbus = Feature::create(['feature_name_id' => $marca->id, 'value' => 'Abus', 'is_active' => true]);
        $fPlata = Feature::create(['feature_name_id' => $color->id, 'value' => 'Plata', 'is_active' => true]);

        $pAbusOnly = Product::create([
            'category_id' => $category->id,
            'code' => 'P-A',
            'name' => 'Abus no Plata',
            'description' => null,
            'price' => 1.0,
            'stock' => 1,
            'is_active' => true,
        ]);
        $pAbusOnly->features()->sync([$fAbus->id]);

        $pBoth = Product::create([
            'category_id' => $category->id,
            'code' => 'P-AP',
            'name' => 'Abus y Plata',
            'description' => null,
            'price' => 1.0,
            'stock' => 1,
            'is_active' => true,
        ]);
        $pBoth->features()->sync([$fAbus->id, $fPlata->id]);

        $response = $this->getJson(
            '/api/v1/products?include_packs=0&feature_ids[]='.$fAbus->id.'&feature_ids[]='.$fPlata->id
        );
        $response->assertOk()->assertJsonPath('success', true);
        $this->assertCount(1, $response->json('data'));
        $this->assertSame($pBoth->id, $response->json('data.0.id'));
    }
}
