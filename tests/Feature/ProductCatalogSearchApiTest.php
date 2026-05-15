<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Contracts\Search\ElasticsearchProductCatalogSearch;
use App\Models\Product;
use App\Support\CatalogTranslationSync;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class ProductCatalogSearchApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_short_query_returns_empty_payload(): void
    {
        $response = $this->getJson('/api/v1/products/search?q=a');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data', [])
            ->assertJsonPath('meta.engine', 'none');
    }

    public function test_database_engine_finds_product_when_scout_is_not_elasticsearch(): void
    {
        config(['scout.driver' => 'null']);

        $category = $this->createProductCategoryForTests('cat-api', 'API category');
        $this->createProductForTests($category->id, 'API-SEARCH-1', 'Martillo demo product');

        $response = $this->getJson('/api/v1/products/search?q=martillo');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('meta.engine', 'database')
            ->assertJsonPath('meta.suggest', false);

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertSame('Martillo demo product', $data[0]['name'] ?? null);
    }

    public function test_elasticsearch_engine_when_configured_and_adapter_returns_hits(): void
    {
        config([
            'scout.driver' => 'elasticsearch',
            'scout.elasticsearch.hosts' => ['http://127.0.0.1:9200'],
        ]);

        $category = $this->createProductCategoryForTests('cat-es', 'ES category');
        $product = Product::withoutSyncingToSearch(function () use ($category) {
            $p = Product::create([
                'category_id' => $category->id,
                'code' => 'ES-1',
                'price' => 5.00,
                'stock' => 1,
                'is_active' => true,
            ]);
            CatalogTranslationSync::syncProductTranslations($p, [
                'ca' => ['name' => 'Elasticsearch listed product', 'description' => null],
                'es' => ['name' => 'Elasticsearch listed product', 'description' => null],
                'en' => ['name' => 'Elasticsearch listed product', 'description' => null],
            ]);

            return $p->fresh(['translations']);
        });

        $this->mock(ElasticsearchProductCatalogSearch::class, function ($mock) use ($product) {
            $mock->shouldReceive('search')
                ->once()
                ->andReturn(new Collection([$product]));
            $mock->shouldNotReceive('suggest');
        });

        $response = $this->getJson('/api/v1/products/search?q=elastic');

        $response->assertOk()
            ->assertJsonPath('meta.engine', 'elasticsearch')
            ->assertJsonPath('data.0.name', 'Elasticsearch listed product');
    }

    public function test_falls_back_to_database_when_elasticsearch_adapter_returns_null(): void
    {
        config([
            'scout.driver' => 'elasticsearch',
            'scout.elasticsearch.hosts' => ['http://127.0.0.1:9200'],
        ]);

        $category = $this->createProductCategoryForTests('cat-fb', 'Fallback category');
        Product::withoutSyncingToSearch(function () use ($category) {
            $p = Product::create([
                'category_id' => $category->id,
                'code' => 'FB-1',
                'price' => 3.00,
                'stock' => 1,
                'is_active' => true,
            ]);
            CatalogTranslationSync::syncProductTranslations($p, [
                'ca' => ['name' => 'Fallback hammer unique', 'description' => null],
                'es' => ['name' => 'Fallback hammer unique', 'description' => null],
                'en' => ['name' => 'Fallback hammer unique', 'description' => null],
            ]);

            return $p;
        });

        $this->mock(ElasticsearchProductCatalogSearch::class, function ($mock) {
            $mock->shouldReceive('search')
                ->once()
                ->andReturn(null);
        });

        $response = $this->getJson('/api/v1/products/search?q=hammer');

        $response->assertOk()
            ->assertJsonPath('meta.engine', 'database');

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
    }

    public function test_suggest_mode_uses_database_when_elasticsearch_not_used(): void
    {
        config(['scout.driver' => 'null']);

        $category = $this->createProductCategoryForTests('cat-sg', 'Suggest category');
        $this->createProductForTests($category->id, 'SG-1', 'Suggestable wrench', null, ['price' => 2, 'stock' => 1]);

        $response = $this->getJson('/api/v1/products/search?q=suggestable&suggest=1');

        $response->assertOk()
            ->assertJsonPath('meta.engine', 'database')
            ->assertJsonPath('meta.suggest', true);

        $data = $response->json('data');
        $this->assertIsArray($data);
        $this->assertNotEmpty($data);
        $this->assertArrayHasKey('text', $data[0]);
        $this->assertArrayHasKey('product_id', $data[0]);
    }

    public function test_suggest_mode_uses_elasticsearch_when_adapter_returns_suggestions(): void
    {
        config([
            'scout.driver' => 'elasticsearch',
            'scout.elasticsearch.hosts' => ['http://127.0.0.1:9200'],
        ]);

        $this->mock(ElasticsearchProductCatalogSearch::class, function ($mock) {
            $mock->shouldReceive('suggest')
                ->once()
                ->andReturn([
                    ['text' => 'AC suggestion', 'product_id' => 99],
                ]);
            $mock->shouldNotReceive('search');
        });

        $response = $this->getJson('/api/v1/products/search?q=ac&suggest=1');

        $response->assertOk()
            ->assertJsonPath('meta.engine', 'elasticsearch')
            ->assertJsonPath('data.0.text', 'AC suggestion')
            ->assertJsonPath('data.0.product_id', 99);
    }
}
