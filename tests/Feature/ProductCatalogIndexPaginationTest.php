<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogIndexPaginationTest extends TestCase
{
    use RefreshDatabase;

    public function test_mixed_catalog_supports_page_parameter_for_infinite_scroll_clients(): void
    {
        $category = $this->createProductCategoryForTests('cat-page', 'Pagination category');

        for ($i = 1; $i <= 18; $i++) {
            $this->createProductForTests(
                $category->id,
                sprintf('PAGE-%02d', $i),
                sprintf('Catalog item %02d', $i),
                null,
                ['price' => 1, 'stock' => 1]
            );
        }

        $r1 = $this->getJson('/api/v1/products?include_packs=1&per_page=10&page=1');
        $r1->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.current_page', 1)
            ->assertJsonPath('meta.last_page', 2)
            ->assertJsonPath('meta.per_page', 10)
            ->assertJsonPath('meta.total', 18);

        $data1 = $r1->json('data');
        $this->assertIsArray($data1);
        $this->assertCount(10, $data1);

        $r2 = $this->getJson('/api/v1/products?include_packs=1&per_page=10&page=2');
        $r2->assertOk()
            ->assertJsonPath('meta.current_page', 2)
            ->assertJsonPath('meta.last_page', 2);

        $data2 = $r2->json('data');
        $this->assertIsArray($data2);
        $this->assertCount(8, $data2);
    }
}
