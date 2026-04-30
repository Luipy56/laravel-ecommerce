<?php

namespace Tests\Feature;

use App\Models\Client;
use App\Models\Order;
use App\Models\OrderLine;
use App\Models\Pack;
use App\Models\Product;
use App\Models\ProductCategory;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteControllerTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutMiddleware(VerifyCsrfToken::class);
    }

    public function test_guest_receives_401_for_favorites_ids(): void
    {
        $this->getJson('/api/v1/favorites/ids')->assertUnauthorized();
    }

    public function test_unverified_client_receives_403_for_favorites_ids(): void
    {
        $client = Client::query()->create([
            'type' => 'person',
            'identification' => null,
            'login_email' => 'fav_u_'.uniqid('', true).'@ietf.org',
            'password' => bcrypt('password'),
            'is_active' => true,
            'email_verified_at' => null,
        ]);

        $this->actingAs($client, 'web')->getJson('/api/v1/favorites/ids')
            ->assertForbidden()
            ->assertJsonPath('code', 'email_not_verified');
    }

    public function test_verified_client_toggle_product_add_and_remove(): void
    {
        [$client, $product] = $this->makeVerifiedClientWithActiveProduct();

        $this->actingAs($client, 'web')->postJson('/api/v1/favorites/toggle', [
            'product_id' => $product->id,
        ])->assertOk()
            ->assertJsonPath('success', true)
            ->assertJsonPath('data.liked', true);

        $this->actingAs($client, 'web')->getJson('/api/v1/favorites/ids')
            ->assertOk()
            ->assertJsonPath('data.product_ids.0', $product->id);

        $this->actingAs($client, 'web')->postJson('/api/v1/favorites/toggle', [
            'product_id' => $product->id,
        ])->assertOk()
            ->assertJsonPath('data.liked', false);

        $this->actingAs($client, 'web')->getJson('/api/v1/favorites/ids')
            ->assertOk()
            ->assertJsonPath('data.product_ids', []);
    }

    public function test_toggle_inactive_product_returns_422(): void
    {
        [$client, $product] = $this->makeVerifiedClientWithActiveProduct();
        $product->update(['is_active' => false]);

        $this->actingAs($client, 'web')->postJson('/api/v1/favorites/toggle', [
            'product_id' => $product->id,
        ])->assertStatus(422);
    }

    public function test_toggle_pack_add(): void
    {
        [$client,, $pack] = $this->makeVerifiedClientWithActiveProductAndPack();

        $this->actingAs($client, 'web')->postJson('/api/v1/favorites/toggle', [
            'pack_id' => $pack->id,
        ])->assertOk()
            ->assertJsonPath('data.liked', true);

        $this->actingAs($client, 'web')->getJson('/api/v1/favorites/ids')
            ->assertOk()
            ->assertJsonPath('data.pack_ids.0', $pack->id);
    }

    public function test_toggle_both_ids_returns_422(): void
    {
        [$client, $product, $pack] = $this->makeVerifiedClientWithActiveProductAndPack();

        $this->actingAs($client, 'web')->postJson('/api/v1/favorites/toggle', [
            'product_id' => $product->id,
            'pack_id' => $pack->id,
        ])->assertStatus(422);
    }

    public function test_favorites_index_includes_orphan_line(): void
    {
        [$client] = $this->makeVerifiedClientWithActiveProduct();
        $likeOrder = Order::query()->create([
            'client_id' => $client->id,
            'kind' => Order::KIND_LIKE,
            'status' => null,
        ]);
        OrderLine::query()->create([
            'order_id' => $likeOrder->id,
            'product_id' => null,
            'pack_id' => null,
            'quantity' => 1,
            'unit_price' => null,
            'is_included' => true,
            'extra_keys_qty' => 0,
            'keys_all_same' => false,
        ]);

        $response = $this->actingAs($client, 'web')->getJson('/api/v1/favorites');
        $response->assertOk();
        $items = $response->json('data.items');
        $this->assertCount(1, $items);
        $this->assertTrue($items[0]['unavailable']);
        $this->assertSame('unknown', $items[0]['kind']);
    }

    /**
     * @return array{0: Client, 1: Product}
     */
    private function makeVerifiedClientWithActiveProduct(): array
    {
        $client = Client::query()->create([
            'type' => 'person',
            'identification' => null,
            'login_email' => 'fav_p_'.uniqid('', true).'@ietf.org',
            'password' => bcrypt('password'),
            'is_active' => true,
            'email_verified_at' => now(),
        ]);

        $category = ProductCategory::query()->create([
            'code' => 'cat_'.uniqid(),
            'name' => 'Category',
            'is_active' => true,
        ]);

        $product = Product::query()->create([
            'category_id' => $category->id,
            'code' => 'p_'.uniqid(),
            'name' => 'Fav product',
            'price' => 12.00,
            'stock' => 3,
            'is_active' => true,
        ]);

        return [$client, $product];
    }

    /**
     * @return array{0: Client, 1: Product, 2: Pack}
     */
    private function makeVerifiedClientWithActiveProductAndPack(): array
    {
        [$client, $product] = $this->makeVerifiedClientWithActiveProduct();
        $pack = Pack::query()->create([
            'name' => 'Fav pack',
            'description' => null,
            'price' => 20.00,
            'is_trending' => false,
            'is_active' => true,
            'contains_keys' => false,
        ]);

        return [$client, $product, $pack];
    }
}
