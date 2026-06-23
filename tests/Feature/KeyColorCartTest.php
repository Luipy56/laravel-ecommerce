<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\KeyColor;
use App\Models\Order;
use App\Support\CatalogTranslationSync;
use App\Support\KeyColorSnapshot;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class KeyColorCartTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_public_key_colors_lists_active_colors(): void
    {
        $color = KeyColor::query()->create([
            'rgb_code' => '#FFD700',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        CatalogTranslationSync::syncKeyColorTranslations($color, [
            'ca' => ['name' => 'Or'],
            'es' => ['name' => 'Oro'],
            'en' => ['name' => 'Gold'],
        ]);

        KeyColor::query()->create([
            'rgb_code' => '#000000',
            'sort_order' => 99,
            'is_active' => false,
        ]);

        $response = $this->getJson('/api/v1/key-colors');

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.rgb_code', '#FFD700')
            ->assertJsonPath('data.0.name', 'Gold');
    }

    public function test_cart_line_stores_key_color_for_product_with_keys(): void
    {
        $client = Client::query()->create([
            'type' => 'person',
            'identification' => null,
            'login_email' => 'keycolor_'.uniqid('', true).'@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $category = $this->createProductCategoryForTests('cat-keys', 'Keys');
        $product = $this->createProductForTests($category->id, 'KEY-PROD-1', 'Lock with keys', null, [
            'is_extra_keys_available' => true,
            'extra_key_unit_price' => 15.00,
        ]);

        $color = KeyColor::query()->create([
            'rgb_code' => '#C0C0C0',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        CatalogTranslationSync::syncKeyColorTranslations($color, [
            'ca' => ['name' => 'Plata'],
            'es' => ['name' => 'Plata'],
            'en' => ['name' => 'Silver'],
        ]);

        $response = $this->actingAs($client, 'web')->postJson('/api/v1/cart/lines', [
            'product_id' => $product->id,
            'quantity' => 1,
            'key_color_id' => $color->id,
        ]);

        $response->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.lines.0.key_color_id', $color->id);

        $cart = Order::query()->where('client_id', $client->id)->where('kind', Order::KIND_CART)->first();
        $this->assertNotNull($cart);
        $line = $cart->lines()->first();
        $this->assertSame($color->id, $line->key_color_id);
    }

    public function test_checkout_snapshots_key_color_on_order_line(): void
    {
        $client = Client::query()->create([
            'type' => 'person',
            'identification' => null,
            'login_email' => 'snap_'.uniqid('', true).'@example.com',
            'password' => bcrypt('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $category = $this->createProductCategoryForTests('cat-keys-2', 'Keys 2');
        $product = $this->createProductForTests($category->id, 'KEY-PROD-2', 'Lock', null, [
            'is_extra_keys_available' => true,
        ]);

        $color = KeyColor::query()->create([
            'rgb_code' => '#CD7F32',
            'sort_order' => 1,
            'is_active' => true,
        ]);
        CatalogTranslationSync::syncKeyColorTranslations($color, [
            'ca' => ['name' => 'Bronze'],
            'es' => ['name' => 'Bronce'],
            'en' => ['name' => 'Bronze'],
        ]);

        $cart = Order::query()->create([
            'client_id' => $client->id,
            'kind' => Order::KIND_CART,
            'status' => null,
        ]);
        $cart->lines()->create([
            'product_id' => $product->id,
            'quantity' => 1,
            'unit_price' => $product->effectivePrice(),
            'key_color_id' => $color->id,
        ]);

        KeyColorSnapshot::freezeOnOrderLines($cart->fresh(['lines.keyColor.translations']));

        $line = $cart->lines()->first()->fresh();
        $this->assertSame('#CD7F32', $line->key_color_rgb);
        $this->assertSame('Bronze', $line->key_color_name);
    }

    public function test_admin_can_create_key_color(): void
    {
        $this->seed(\Database\Seeders\AdminSeeder::class);
        $this->withCredentials();
        $this->postJson('/api/v1/admin/login', [
            'username' => 'manager',
            'password' => 'admin',
        ])->assertOk();

        $response = $this->postJson('/api/v1/admin/key-colors', [
            'rgb_code' => '#1A1A1A',
            'sort_order' => 5,
            'is_active' => true,
            'name' => 'Negre',
            'translations' => [
                'es' => ['name' => 'Negro'],
                'en' => ['name' => 'Black'],
            ],
        ]);

        $response->assertCreated()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.rgb_code', '#1A1A1A')
            ->assertJsonPath('data.name', 'Black');

        $this->assertDatabaseHas('key_colors', ['rgb_code' => '#1A1A1A']);
        $this->assertDatabaseHas('key_color_translations', [
            'locale' => 'en',
            'name' => 'Black',
        ]);
    }
}
