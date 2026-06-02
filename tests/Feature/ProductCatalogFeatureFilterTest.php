<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Feature;
use App\Models\FeatureName;
use App\Support\CatalogTranslationSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogFeatureFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_same_characteristic_values_are_ored(): void
    {
        $category = $this->createProductCategoryForTests('cat-ff1', 'FF category');
        $marca = FeatureName::create(['code' => 't_brand', 'is_active' => true]);
        CatalogTranslationSync::syncFeatureNameTranslations($marca, [
            'ca' => ['name' => 'Marca'],
            'es' => ['name' => 'Marca'],
            'en' => ['name' => 'Brand'],
        ]);
        $fAbus = Feature::create(['feature_name_id' => $marca->id, 'is_active' => true]);
        CatalogTranslationSync::syncFeatureTranslations($fAbus, [
            'ca' => ['value' => 'Abus'], 'es' => ['value' => 'Abus'], 'en' => ['value' => 'Abus'],
        ]);
        $fDisec = Feature::create(['feature_name_id' => $marca->id, 'is_active' => true]);
        CatalogTranslationSync::syncFeatureTranslations($fDisec, [
            'ca' => ['value' => 'Disec'], 'es' => ['value' => 'Disec'], 'en' => ['value' => 'Disec'],
        ]);

        $p1 = $this->createProductForTests($category->id, 'P-ABUS', 'Only Abus');
        $p1->features()->sync([$fAbus->id]);
        $p2 = $this->createProductForTests($category->id, 'P-DISEC', 'Only Disec');
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
        $category = $this->createProductCategoryForTests('cat-ff2', 'FF2 category');
        $marca = FeatureName::create(['code' => 't_brand2', 'is_active' => true]);
        $color = FeatureName::create(['code' => 't_color', 'is_active' => true]);
        CatalogTranslationSync::syncFeatureNameTranslations($marca, [
            'ca' => ['name' => 'Marca'], 'es' => ['name' => 'Marca'], 'en' => ['name' => 'Brand'],
        ]);
        CatalogTranslationSync::syncFeatureNameTranslations($color, [
            'ca' => ['name' => 'Color'], 'es' => ['name' => 'Color'], 'en' => ['name' => 'Colour'],
        ]);
        $fAbus = Feature::create(['feature_name_id' => $marca->id, 'is_active' => true]);
        CatalogTranslationSync::syncFeatureTranslations($fAbus, [
            'ca' => ['value' => 'Abus'], 'es' => ['value' => 'Abus'], 'en' => ['value' => 'Abus'],
        ]);
        $fPlata = Feature::create(['feature_name_id' => $color->id, 'is_active' => true]);
        CatalogTranslationSync::syncFeatureTranslations($fPlata, [
            'ca' => ['value' => 'Plata'], 'es' => ['value' => 'Plata'], 'en' => ['value' => 'Silver'],
        ]);

        $pAbusOnly = $this->createProductForTests($category->id, 'P-A', 'Abus no Plata');
        $pAbusOnly->features()->sync([$fAbus->id]);

        $pBoth = $this->createProductForTests($category->id, 'P-AP', 'Abus y Plata');
        $pBoth->features()->sync([$fAbus->id, $fPlata->id]);

        $response = $this->getJson(
            '/api/v1/products?include_packs=0&feature_ids[]='.$fAbus->id.'&feature_ids[]='.$fPlata->id
        );
        $response->assertOk()->assertJsonPath('success', true);
        $this->assertCount(1, $response->json('data'));
        $this->assertSame($pBoth->id, $response->json('data.0.id'));
    }
}
