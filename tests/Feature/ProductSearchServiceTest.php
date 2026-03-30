<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Services\Search\ProductSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductSearchServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_partial_k1_matches_relevant_sku_style_field(): void
    {
        $category = ProductCategory::create([
            'code' => 'sc1',
            'name' => 'Cylinders',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'code' => '192 evoK1C 3030 N',
            'name' => 'Cilindro 30x30 mm níquel Securemme K1',
            'description' => null,
            'price' => 26.50,
            'stock' => 1,
            'is_active' => true,
        ]);

        $service = new ProductSearchService;
        $hits = $service->search('k1');

        $this->assertGreaterThanOrEqual(1, $hits->count());
        $this->assertTrue($hits->contains(fn (Product $p) => str_contains((string) $p->code, 'K1')));
    }

    public function test_typo_cilimdro_matches_cilindro(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Trigram typo tolerance is implemented for PostgreSQL only.');
        }

        $category = ProductCategory::create([
            'code' => 'sc2',
            'name' => 'Cylinders',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'code' => 'TYPO-1',
            'name' => 'Cilindro de prueba',
            'description' => null,
            'price' => 10.00,
            'stock' => 1,
            'is_active' => true,
        ]);

        $service = new ProductSearchService;
        $hits = $service->search('cilimdro');

        $this->assertGreaterThanOrEqual(1, $hits->count());
        $match = $hits->first(fn (Product $p) => str_contains(mb_strtolower($p->name, 'UTF-8'), 'cilindro'));
        $this->assertNotNull($match);
    }

    public function test_mixed_cilindro_3030_k1_returns_sensible_ranked_results(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Ranking smoke test targets PostgreSQL trigram + ILIKE scoring.');
        }

        $category = ProductCategory::create([
            'code' => 'sc3',
            'name' => 'Cylinders',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'code' => '192 evoK1C 3030 L',
            'name' => 'Cilindro 30x30 mm latón Securemme K1',
            'description' => null,
            'price' => 26.50,
            'stock' => 1,
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'code' => '192 evoK1C 3030 N',
            'name' => 'Cilindro 30x30 mm níquel Securemme K1',
            'description' => null,
            'price' => 26.50,
            'stock' => 2,
            'is_active' => true,
        ]);

        $service = new ProductSearchService;
        $hits = $service->search('cilindro 3030 k1');

        $this->assertGreaterThanOrEqual(2, $hits->count());
        $codes = $hits->pluck('code')->map(fn ($c) => (string) $c)->all();
        $this->assertContains('192 evoK1C 3030 N', $codes);
        $this->assertContains('192 evoK1C 3030 L', $codes);
        $first = $hits->first();
        $this->assertNotNull($first);
        $this->assertStringContainsString('3030', (string) $first->code);
        $this->assertStringContainsString('k1', mb_strtolower((string) $first->search_text, 'UTF-8'));
    }

    public function test_empty_query_returns_no_results(): void
    {
        $service = new ProductSearchService;

        $this->assertCount(0, $service->search(''));
        $this->assertCount(0, $service->search('   '));
    }
}
