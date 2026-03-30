<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductStorefrontSearchTest extends TestCase
{
    use RefreshDatabase;

    private function seedCategoryAndProduct(string $name, string $code = 't_code'): Product
    {
        $category = ProductCategory::query()->create([
            'code' => 'cat_'.uniqid(),
            'name' => 'Category',
            'is_active' => true,
        ]);

        return Product::query()->create([
            'category_id' => $category->id,
            'code' => $code,
            'name' => $name,
            'description' => '',
            'price' => 10.00,
            'stock' => 5,
            'is_active' => true,
        ]);
    }

    public function test_synonym_matches_spanish_query_against_catalan_product_title(): void
    {
        $product = $this->seedCategoryAndProduct('Tornillo M6 acer', 'syn_torn');

        $response = $this->getJson('/api/v1/products?search='.rawurlencode('cargol'));

        $response->assertOk()
            ->assertJsonPath('success', true);
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($product->id, $ids);
    }

    public function test_fuzzy_fallback_matches_typo_when_strict_misses(): void
    {
        $product = $this->seedCategoryAndProduct('SpecialGadgetWidget', 'fuzzy_w');

        $response = $this->getJson('/api/v1/products?search='.rawurlencode('SpecialGadgetWidgit'));

        $response->assertOk()
            ->assertJsonPath('success', true);
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($product->id, $ids);
    }

    public function test_products_search_endpoint_uses_same_matching(): void
    {
        $product = $this->seedCategoryAndProduct('Kit martell groc', 'q_mart');

        $response = $this->getJson('/api/v1/products/search?q='.rawurlencode('martillo'));

        $response->assertOk()
            ->assertJsonPath('success', true);
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertContains($product->id, $ids);
    }
}
