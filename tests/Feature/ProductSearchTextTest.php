<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductTranslation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class ProductSearchTextTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_translation_saving_sets_normalized_search_text(): void
    {
        $category = $this->createProductCategoryForTests('t', 'Test category');
        $product = Product::create([
            'category_id' => $category->id,
            'code' => 'X-ÁB',
            'price' => 10.00,
            'stock' => 1,
            'is_active' => true,
        ]);
        $t = new ProductTranslation([
            'locale' => 'ca',
            'name' => 'Cilindro  Café',
            'description' => 'Niño',
        ]);
        $t->product()->associate($product);
        $t->save();

        $t->refresh();
        $expected = Product::normalizeSearchText('Cilindro  Café', 'X-ÁB', 'Niño');
        $this->assertSame($expected, $t->search_text);
        $this->assertStringContainsString('cilindro', (string) $t->search_text);
        $this->assertStringContainsString('x-ab', (string) $t->search_text);
        if (extension_loaded('intl')) {
            $this->assertStringContainsString('cafe', (string) $t->search_text);
            $this->assertStringContainsString('nino', (string) $t->search_text);
        }
    }

    public function test_rebuild_search_text_command_updates_rows(): void
    {
        $category = $this->createProductCategoryForTests('t2', 'Cat 2');
        $pid = DB::table('products')->insertGetId([
            'category_id' => $category->id,
            'variant_group_id' => null,
            'code' => 'RAW-1',
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
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('product_translations')->insert([
            'product_id' => $pid,
            'locale' => 'ca',
            'name' => 'Raw Name É',
            'description' => null,
            'search_text' => 'stale-wrong',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Artisan::call('products:rebuild-search-text');

        $st = DB::table('product_translations')->where('product_id', $pid)->where('locale', 'ca')->value('search_text');
        $this->assertSame(Product::normalizeSearchText('Raw Name É', 'RAW-1', null), $st);
    }

    public function test_rebuild_search_text_stale_only_updates_mismatched_rows(): void
    {
        $category = $this->createProductCategoryForTests('t3', 'Cat 3');
        $ok = $this->createProductForTests($category->id, 'STALE-OK', 'Already normalized', null, ['price' => 1, 'stock' => 1]);
        $okCa = $ok->translations->firstWhere('locale', 'ca');
        $this->assertNotNull($okCa);
        $expectedOk = Product::normalizeSearchText('Already normalized', 'STALE-OK', null);
        $this->assertSame($expectedOk, $okCa->search_text);

        $badPid = DB::table('products')->insertGetId([
            'category_id' => $category->id,
            'variant_group_id' => null,
            'code' => 'STALE-BAD',
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
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('product_translations')->insert([
            'product_id' => $badPid,
            'locale' => 'ca',
            'name' => 'Needs rebuild',
            'description' => null,
            'search_text' => 'wrong',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Artisan::call('products:rebuild-search-text', ['--stale' => true]);

        $okCa2 = $ok->fresh()->translations->firstWhere('locale', 'ca');
        $this->assertSame($expectedOk, $okCa2?->search_text);
        $badSt = DB::table('product_translations')->where('product_id', $badPid)->where('locale', 'ca')->value('search_text');
        $this->assertSame(Product::normalizeSearchText('Needs rebuild', 'STALE-BAD', null), $badSt);
    }

    public function test_postgresql_search_text_trgm_index_exists_when_using_pgsql(): void
    {
        if (DB::getDriverName() !== 'pgsql') {
            $this->markTestSkipped('Requires DB_CONNECTION=pgsql.');
        }

        $row = DB::selectOne(
            'select 1 as ok from pg_indexes where schemaname = current_schema() and tablename = ? and indexname = ?',
            ['product_translations', 'idx_product_translations_search_text_trgm']
        );
        $this->assertNotNull($row, 'GIN(trgm) index on product_translations.search_text should exist');
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
