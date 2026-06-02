<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\GoogleMerchantFeedController;
use App\Models\Pack;
use App\Support\CatalogTranslationSync;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ShareMetaSeoTest extends TestCase
{
    use DatabaseMigrations;

    public function test_product_page_includes_open_graph_meta_for_active_product(): void
    {
        $category = $this->createProductCategoryForTests('locks', 'Panys');
        $product = $this->createProductForTests(
            $category->id,
            'SKU-OG-1',
            'Pany Test',
            'Descripcio del pany per compartir en xarxes socials.',
        );

        $response = $this->get('/products/'.$product->id, ['Accept-Language' => 'ca']);

        $response->assertOk();
        $response->assertSee('<meta property="og:title" content="Pany Test"', false);
        $response->assertSee('<meta property="og:type" content="product"', false);
        $response->assertSee('<meta property="og:url" content="'.url('/products/'.$product->id).'"', false);
        $response->assertSee('<link rel="canonical" href="'.url('/products/'.$product->id).'"', false);
        $response->assertSee('<meta property="og:image" content="', false);
        $response->assertSee('<meta name="twitter:card" content="summary_large_image"', false);
    }

    public function test_product_meta_respects_accept_language(): void
    {
        $category = $this->createProductCategoryForTests('cat-i18n', 'Cat');
        $product = $this->createProductForTests(
            $category->id,
            'SKU-I18N',
            'Fallback',
            null,
            [],
            [
                'ca' => ['name' => 'Nom CA', 'description' => 'Desc CA'],
                'es' => ['name' => 'Nombre ES', 'description' => 'Desc ES'],
                'en' => ['name' => 'Name EN', 'description' => 'Desc EN'],
            ],
        );

        $this->get('/products/'.$product->id, ['Accept-Language' => 'es'])
            ->assertOk()
            ->assertSee('<meta property="og:title" content="Nombre ES"', false);

        $this->get('/products/'.$product->id, ['Accept-Language' => 'en'])
            ->assertOk()
            ->assertSee('<meta property="og:title" content="Name EN"', false);
    }

    public function test_inactive_product_shell_returns_not_found(): void
    {
        $category = $this->createProductCategoryForTests('inactive-cat', 'Inactive');
        $product = $this->createProductForTests($category->id, 'SKU-OFF', 'Inactive product', null, [
            'is_active' => false,
        ]);

        $this->get('/products/'.$product->id)->assertNotFound();
    }

    public function test_pack_page_includes_open_graph_meta(): void
    {
        $pack = Pack::create([
            'price' => 49.99,
            'is_active' => true,
        ]);
        CatalogTranslationSync::syncPackTranslations($pack, [
            'ca' => ['name' => 'Pack prova', 'description' => 'Contingut del pack'],
            'es' => ['name' => 'Pack prueba', 'description' => 'Contenido del pack'],
            'en' => ['name' => 'Sample pack', 'description' => 'Pack contents'],
        ]);

        $this->get('/packs/'.$pack->id, ['Accept-Language' => 'ca'])
            ->assertOk()
            ->assertSee('<meta property="og:title" content="Pack prova"', false)
            ->assertSee('<link rel="canonical" href="'.url('/packs/'.$pack->id).'"', false);
    }

    public function test_category_products_page_includes_category_title_in_meta(): void
    {
        $category = $this->createProductCategoryForTests('cat-share', 'Panys intel·ligents');

        $this->get('/categories/'.$category->id.'/products', ['Accept-Language' => 'ca'])
            ->assertOk()
            ->assertSee('Panys intel·ligents', false)
            ->assertSee('<link rel="canonical" href="'.url('/categories/'.$category->id.'/products').'"', false);
    }
}
