<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Product;
use App\Support\CatalogTranslationSync;
use App\Services\Search\ProductSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductCatalogSearchByLocaleTest extends TestCase
{
    use RefreshDatabase;

    public function test_text_search_only_hits_active_locale_content(): void
    {
        $category = $this->createProductCategoryForTests('loc-cat', 'Locale cat');
        $p = Product::create([
            'category_id' => $category->id,
            'code' => 'LOC-ONLY-ES',
            'price' => 1,
            'stock' => 1,
            'is_active' => true,
        ]);
        CatalogTranslationSync::syncProductTranslations($p, [
            'es' => ['name' => 'PalabraunicaEsDemo999', 'description' => null],
        ]);

        $svc = new ProductSearchService;
        $this->assertCount(0, $svc->search('PalabraunicaEsDemo999', 'ca'));
        $this->assertGreaterThanOrEqual(1, $svc->search('PalabraunicaEsDemo999', 'es')->count());
    }
}
