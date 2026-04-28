<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Laravel\Scout\Jobs\MakeSearchable;
use Laravel\Scout\Jobs\RemoveFromSearch;
use Tests\TestCase;

class ProductScoutIndexingTest extends TestCase
{
    use RefreshDatabase;

    public function test_saving_active_product_dispatches_make_searchable_when_scout_queue_is_enabled(): void
    {
        Bus::fake();
        config(['scout.queue' => true, 'scout.driver' => 'null']);

        $category = ProductCategory::create([
            'code' => 'scout-cat',
            'name' => 'Scout category',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'code' => 'SCOUT-1',
            'name' => 'Scout product',
            'description' => null,
            'price' => 10.00,
            'stock' => 1,
            'is_active' => true,
        ]);

        Bus::assertDispatched(MakeSearchable::class);
    }

    public function test_saving_active_product_does_not_dispatch_make_searchable_when_scout_queue_is_disabled(): void
    {
        Bus::fake();
        config(['scout.queue' => false, 'scout.driver' => 'null']);

        $category = ProductCategory::create([
            'code' => 'scout-cat-2',
            'name' => 'Scout category 2',
            'is_active' => true,
        ]);

        Product::create([
            'category_id' => $category->id,
            'code' => 'SCOUT-2',
            'name' => 'Scout product 2',
            'description' => null,
            'price' => 10.00,
            'stock' => 1,
            'is_active' => true,
        ]);

        Bus::assertNothingDispatched();
    }

    public function test_deactivating_product_dispatches_remove_from_search_when_scout_queue_is_enabled(): void
    {
        config(['scout.queue' => false, 'scout.driver' => 'null']);

        $category = ProductCategory::create([
            'code' => 'scout-cat-3',
            'name' => 'Scout category 3',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'SCOUT-3',
            'name' => 'Scout product 3',
            'description' => null,
            'price' => 10.00,
            'stock' => 1,
            'is_active' => true,
        ]);

        Bus::fake();
        config(['scout.queue' => true]);

        $product->update(['is_active' => false]);

        Bus::assertDispatched(RemoveFromSearch::class);
    }
}
