<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Support\CatalogTranslationSync;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Queue;
use Laravel\Scout\Jobs\MakeSearchable;
use Laravel\Scout\Jobs\RemoveFromSearch;
use Tests\TestCase;

class ProductScoutIndexingTest extends TestCase
{
    use DatabaseMigrations;

    public function test_saving_active_product_dispatches_make_searchable_when_scout_queue_is_enabled(): void
    {
        config(['scout.queue' => true, 'scout.driver' => 'null']);

        $category = $this->createProductCategoryForTests('scout-cat', 'Scout category');
        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'SCOUT-1',
            'price' => 10.00,
            'stock' => 1,
            'is_active' => true,
        ]);
        CatalogTranslationSync::syncProductTranslations($product, [
            'ca' => ['name' => 'Scout product', 'description' => null],
            'es' => ['name' => 'Scout product', 'description' => null],
            'en' => ['name' => 'Scout product', 'description' => null],
        ]);
        $product->refresh();

        Queue::fake();
        config(['scout.queue' => true, 'scout.driver' => 'null']);

        $product->searchable();

        Queue::assertPushed(MakeSearchable::class);
    }

    public function test_saving_active_product_does_not_dispatch_make_searchable_when_scout_queue_is_disabled(): void
    {
        Queue::fake();
        config(['scout.queue' => false, 'scout.driver' => 'null']);

        $category = $this->createProductCategoryForTests('scout-cat-2', 'Scout category 2');
        Product::create([
            'category_id' => $category->id,
            'code' => 'SCOUT-2',
            'price' => 10.00,
            'stock' => 1,
            'is_active' => true,
        ]);

        Queue::assertNothingPushed();
    }

    public function test_deactivating_product_dispatches_remove_from_search_when_scout_queue_is_enabled(): void
    {
        config(['scout.queue' => false, 'scout.driver' => 'null']);

        $category = $this->createProductCategoryForTests('scout-cat-3', 'Scout category 3');
        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'SCOUT-3',
            'price' => 10.00,
            'stock' => 1,
            'is_active' => true,
        ]);
        CatalogTranslationSync::syncProductTranslations($product, [
            'ca' => ['name' => 'Scout product 3', 'description' => null],
            'es' => ['name' => 'Scout product 3', 'description' => null],
            'en' => ['name' => 'Scout product 3', 'description' => null],
        ]);
        $product->refresh();

        Queue::fake();
        config(['scout.queue' => true, 'scout.driver' => 'null']);

        $product->unsearchable();

        Queue::assertPushed(RemoveFromSearch::class);
    }
}
