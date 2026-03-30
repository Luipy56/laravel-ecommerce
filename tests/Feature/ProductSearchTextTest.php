<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductSearchTextTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_saving_sets_normalized_search_text(): void
    {
        $category = ProductCategory::create([
            'code' => 't',
            'name' => 'Test category',
            'is_active' => true,
        ]);

        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'X-ÁB',
            'name' => 'Cilindro  Café',
            'description' => 'Niño',
            'price' => 10.00,
            'stock' => 1,
            'is_active' => true,
        ]);

        $product->refresh();
        $expected = Product::normalizeSearchText('Cilindro  Café', 'X-ÁB', 'Niño');
        $this->assertSame($expected, $product->search_text);
        $this->assertStringContainsString('cilindro', $product->search_text);
        $this->assertStringContainsString('x-ab', $product->search_text);
        if (extension_loaded('intl')) {
            $this->assertStringContainsString('cafe', $product->search_text);
            $this->assertStringContainsString('nino', $product->search_text);
        }
    }

    public function test_rebuild_search_text_command_updates_rows(): void
    {
        $category = ProductCategory::create([
            'code' => 't2',
            'name' => 'Cat 2',
            'is_active' => true,
        ]);

        DB::table('products')->insert([
            'category_id' => $category->id,
            'variant_group_id' => null,
            'code' => 'RAW-1',
            'name' => 'Raw Name É',
            'description' => null,
            'price' => 1.00,
            'discount_percent' => null,
            'purchase_price' => null,
            'stock' => 0,
            'weight_kg' => null,
            'is_double_clutch' => false,
            'has_card' => false,
            'security_level' => null,
            'competitor_url' => null,
            'is_extra_keys_available' => false,
            'extra_key_unit_price' => null,
            'is_featured' => false,
            'is_trending' => false,
            'is_active' => true,
            'search_text' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Artisan::call('products:rebuild-search-text');

        $row = DB::table('products')->where('code', 'RAW-1')->first();
        $this->assertNotNull($row);
        $this->assertSame(Product::normalizeSearchText('Raw Name É', 'RAW-1', null), $row->search_text);
    }

    public function test_rebuild_search_text_stale_only_updates_mismatched_rows(): void
    {
        $category = ProductCategory::create([
            'code' => 't3',
            'name' => 'Cat 3',
            'is_active' => true,
        ]);

        $ok = Product::create([
            'category_id' => $category->id,
            'code' => 'STALE-OK',
            'name' => 'Already normalized',
            'description' => null,
            'price' => 1.00,
            'stock' => 1,
            'is_active' => true,
        ]);

        DB::table('products')->insert([
            'category_id' => $category->id,
            'variant_group_id' => null,
            'code' => 'STALE-BAD',
            'name' => 'Needs rebuild',
            'description' => null,
            'price' => 2.00,
            'discount_percent' => null,
            'purchase_price' => null,
            'stock' => 1,
            'weight_kg' => null,
            'is_double_clutch' => false,
            'has_card' => false,
            'security_level' => null,
            'competitor_url' => null,
            'is_extra_keys_available' => false,
            'extra_key_unit_price' => null,
            'is_featured' => false,
            'is_trending' => false,
            'is_active' => true,
            'search_text' => 'wrong',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Artisan::call('products:rebuild-search-text', ['--stale' => true]);

        $ok->refresh();
        $bad = DB::table('products')->where('code', 'STALE-BAD')->first();
        $this->assertNotNull($bad);
        $this->assertSame(Product::normalizeSearchText('Needs rebuild', 'STALE-BAD', null), $bad->search_text);
        $this->assertSame(
            Product::normalizeSearchText('Already normalized', 'STALE-OK', null),
            $ok->search_text
        );
    }

    public function test_postgresql_search_text_trgm_index_exists_when_using_pgsql(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Requires DB_CONNECTION=pgsql.');
        }

        $row = DB::selectOne(
            'select 1 as ok from pg_indexes where schemaname = current_schema() and tablename = ? and indexname = ?',
            ['products', 'idx_products_search_text_trgm']
        );
        $this->assertNotNull($row, 'GIN(trgm) index on products.search_text should exist');
    }

    public function test_postgresql_extensions_exist_when_using_pgsql(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Requires DB_CONNECTION=pgsql (e.g. DB_TESTING_DATABASE set for a Postgres test database).');
        }

        $names = ['pg_trgm', 'citext', 'unaccent'];
        foreach ($names as $ext) {
            $found = DB::selectOne(
                'select 1 as ok from pg_extension where extname = ? limit 1',
                [$ext]
            );
            $this->assertNotNull($found, "Extension {$ext} should exist");
        }
    }
}
