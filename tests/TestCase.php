<?php

namespace Tests;

use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\CatalogTranslationSync;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Laravel clears defaultHeaders in tearDown but not cookies / withCredentials (MakesHttpRequests).
        $this->defaultCookies = [];
        $this->unencryptedCookies = [];
        $this->withCredentials = false;
    }

    /** Category with ca/es/en names (same string by default). */
    protected function createProductCategoryForTests(string $code, string $name, bool $isActive = true): ProductCategory
    {
        $c = ProductCategory::create([
            'code' => $code,
            'is_active' => $isActive,
        ]);
        CatalogTranslationSync::syncCategoryTranslations($c, [
            'ca' => ['name' => $name],
            'es' => ['name' => $name],
            'en' => ['name' => $name],
        ]);

        return $c->fresh()->load('translations');
    }

    /**
     * @param  array<string, array{name?: string|null, description?: string|null}>|null  $perLocale  If null, copies $name/$description to ca, es, en.
     */
    protected function createProductForTests(
        int $categoryId,
        string $code,
        string $name,
        ?string $description = null,
        array $attrs = [],
        ?array $perLocale = null
    ): Product {
        $p = Product::create(array_merge([
            'category_id' => $categoryId,
            'code' => $code,
            'price' => 10,
            'stock' => 1,
            'is_active' => true,
        ], $attrs));
        if ($perLocale === null) {
            $perLocale = [
                'ca' => ['name' => $name, 'description' => $description],
                'es' => ['name' => $name, 'description' => $description],
                'en' => ['name' => $name, 'description' => $description],
            ];
        }
        CatalogTranslationSync::syncProductTranslations($p, $perLocale);

        return $p->fresh()->load('translations');
    }
}
