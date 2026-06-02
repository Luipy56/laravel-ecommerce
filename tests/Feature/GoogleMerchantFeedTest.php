<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Http\Controllers\GoogleMerchantFeedController;
use App\Support\CatalogTranslationSync;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class GoogleMerchantFeedTest extends TestCase
{
    use DatabaseMigrations;

    protected function setUp(): void
    {
        parent::setUp();
        Cache::forget(GoogleMerchantFeedController::CACHE_KEY);
    }

    public function test_feed_returns_valid_xml_with_required_fields_for_active_products(): void
    {
        $category = $this->createProductCategoryForTests('feed-cat', 'Feed category');
        $active = $this->createProductForTests($category->id, 'FEED-001', 'Active feed product', 'Description for GMC', [
            'price' => 100,
            'discount_percent' => 10,
            'stock' => 5,
        ]);
        $this->createProductForTests($category->id, 'FEED-OFF', 'Inactive feed product', null, [
            'is_active' => false,
        ]);

        $response = $this->get('/feeds/google-merchant.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml; charset=UTF-8');
        $body = $response->getContent();
        $this->assertStringContainsString('<rss', $body);
        $this->assertStringContainsString('<g:id>'.$active->id.'</g:id>', $body);
        $this->assertStringContainsString('<g:title>Active feed product</g:title>', $body);
        $this->assertStringContainsString('<g:link>'.url('/products/'.$active->id).'</g:link>', $body);
        $this->assertStringContainsString('<g:image_link>', $body);
        $this->assertStringContainsString('<g:availability>in stock</g:availability>', $body);
        $this->assertStringContainsString('<g:price>90.00 EUR</g:price>', $body);
        $this->assertStringContainsString('<g:mpn>FEED-001</g:mpn>', $body);
        $this->assertStringNotContainsString('Inactive feed product', $body);
    }

    public function test_out_of_stock_product_is_marked_in_feed(): void
    {
        $category = $this->createProductCategoryForTests('oos-cat', 'OOS');
        $product = $this->createProductForTests($category->id, 'OOS-1', 'Out of stock item', null, [
            'stock' => 0,
        ]);

        $body = $this->get('/feeds/google-merchant.xml')->getContent();

        $this->assertStringContainsString('<g:id>'.$product->id.'</g:id>', $body);
        $this->assertStringContainsString('<g:availability>out of stock</g:availability>', $body);
    }

    public function test_product_save_invalidates_feed_cache(): void
    {
        $category = $this->createProductCategoryForTests('cache-cat', 'Cache');
        $product = $this->createProductForTests($category->id, 'CACHE-1', 'Cached name', null);

        $first = $this->get('/feeds/google-merchant.xml')->getContent();
        $this->assertStringContainsString('Cached name', $first);

        CatalogTranslationSync::syncProductTranslations($product->fresh(), [
            'ca' => ['name' => 'Updated name', 'description' => null],
            'es' => ['name' => 'Updated name', 'description' => null],
            'en' => ['name' => 'Updated name', 'description' => null],
        ]);
        $product->touch();

        $second = $this->get('/feeds/google-merchant.xml')->getContent();
        $this->assertStringContainsString('Updated name', $second);
    }
}
