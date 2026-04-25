<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ShopSetting;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ShopSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_shop_public_settings_returns_accept_flag(): void
    {
        $this->getJson('/api/v1/shop/public-settings')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.accept_personalized_solutions', true);

        ShopSetting::set(ShopSetting::KEY_ACCEPT_PERSONALIZED_SOLUTIONS, false);

        $this->getJson('/api/v1/shop/public-settings')
            ->assertOk()
            ->assertJsonPath('data.accept_personalized_solutions', false);
    }

    public function test_personalized_solution_store_rejected_when_disabled(): void
    {
        $this->seed(DatabaseSeeder::class);
        ShopSetting::set(ShopSetting::KEY_ACCEPT_PERSONALIZED_SOLUTIONS, false);

        $this->postJson('/api/v1/personalized-solutions', [
            'email' => 'test@example.com',
            'phone' => null,
            'problem_description' => 'Need help',
            'address_street' => null,
            'address_city' => null,
            'address_province' => null,
            'address_postal_code' => '08001',
            'address_note' => null,
        ])->assertStatus(422)
            ->assertJsonPath('success', false);
    }

    public function test_admin_settings_crud_and_recalculate_trending(): void
    {
        $this->seed(DatabaseSeeder::class);

        $this->getJson('/api/v1/admin/settings')->assertStatus(401);

        $this->withCredentials();
        $this->postJson('/api/v1/admin/login', [
            'username' => 'manager',
            'password' => 'admin',
        ])->assertOk();

        $this->getJson('/api/v1/admin/settings')
            ->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.low_stock_enabled', false);

        $this->putJson('/api/v1/admin/settings', [
            'low_stock_enabled' => true,
            'low_stock_threshold' => 20,
            'low_stock_blacklist_enabled' => false,
            'low_stock_blacklist_product_ids' => [],
            'overstock_enabled' => false,
            'overstock_threshold' => 100,
            'overstock_blacklist_enabled' => false,
            'overstock_blacklist_product_ids' => [],
            'accept_personalized_solutions' => true,
        ])->assertOk()
            ->assertJsonPath('data.low_stock_threshold', 20);

        $product = Product::query()->active()->first();
        $this->assertNotNull($product);
        $product->update([
            'stock' => 5,
            'is_featured' => false,
            'is_trending' => false,
        ]);

        $this->postJson('/api/v1/admin/settings/recalculate-trending')
            ->assertOk()
            ->assertJsonPath('success', true);

        $this->assertTrue($product->fresh()->is_trending);

        ShopSetting::set(ShopSetting::KEY_LOW_STOCK_BLACKLIST_ENABLED, true);
        ShopSetting::set(ShopSetting::KEY_LOW_STOCK_BLACKLIST_PRODUCT_IDS, [$product->id]);

        $this->postJson('/api/v1/admin/settings/recalculate-trending')->assertOk();

        $this->assertFalse($product->fresh()->is_trending);
    }

    public function test_featured_endpoint_combines_featured_and_trending_without_duplicate_ids(): void
    {
        $this->seed(DatabaseSeeder::class);

        $a = Product::query()->active()->orderBy('id')->first();
        $b = Product::query()->active()->where('id', '!=', $a->id)->orderBy('id')->first();
        $this->assertNotNull($b);

        $a->update(['is_featured' => true, 'is_trending' => true, 'stock' => 99]);
        $b->update(['is_featured' => false, 'is_trending' => true, 'stock' => 1]);

        $response = $this->getJson('/api/v1/products/featured');
        $response->assertOk();
        $ids = collect($response->json('data'))->pluck('id')->all();
        $this->assertSame(count($ids), count(array_unique($ids)));
        $this->assertContains($a->id, $ids);
        $this->assertContains($b->id, $ids);
    }
}
