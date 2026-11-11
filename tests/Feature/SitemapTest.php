<?php

declare(strict_types=1);

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class SitemapTest extends TestCase
{
    use DatabaseMigrations;

    public function test_sitemap_lists_marketing_canonicals_without_priority_or_changefreq(): void
    {
        Cache::forget('sitemap.xml');

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $xml = $response->getContent();

        $this->assertStringNotContainsString('<priority>', $xml);
        $this->assertStringNotContainsString('<changefreq>', $xml);
        $this->assertStringNotContainsString('/web/modal', $xml);
        $this->assertStringNotContainsString('.php</loc>', $xml);

        $marketingBase = rtrim((string) config('seo.marketing_base_url'), '/');
        $this->assertStringContainsString('<loc>'.$marketingBase.'/</loc>', $xml);
        $this->assertStringContainsString('<loc>'.$marketingBase.'/nosaltres</loc>', $xml);
        $this->assertStringContainsString('<loc>'.$marketingBase.'/serveis</loc>', $xml);
        $this->assertStringContainsString('<loc>'.$marketingBase.'/contacte</loc>', $xml);
        $this->assertStringContainsString('<loc>'.$marketingBase.'/cookies</loc>', $xml);

        $shopBase = rtrim((string) config('seo.sitemap_base_url'), '/');
        $this->assertStringContainsString('<loc>'.$shopBase.'/</loc>', $xml);
        $this->assertStringContainsString('<loc>'.$shopBase.'/products</loc>', $xml);
    }
}
