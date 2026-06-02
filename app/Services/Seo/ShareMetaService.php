<?php

declare(strict_types=1);

namespace App\Services\Seo;

use App\Models\Pack;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Support\ShareMeta;
use Illuminate\Support\Str;

class ShareMetaService
{
    public function forHome(string $locale): ShareMeta
    {
        $siteName = config('app.name');
        $canonical = url('/');

        return new ShareMeta(
            title: (string) __('seo.default_title', ['store' => $siteName], $locale),
            description: (string) __('seo.default_description', ['store' => $siteName], $locale),
            canonicalUrl: $canonical,
            ogType: 'website',
            imageUrl: $this->brandImageUrl(),
            imageAlt: $siteName,
        );
    }

    public function forProducts(string $locale): ShareMeta
    {
        $siteName = config('app.name');
        $canonical = url('/products');

        return new ShareMeta(
            title: (string) __('seo.products_title', ['store' => $siteName], $locale),
            description: (string) __('seo.products_description', ['store' => $siteName], $locale),
            canonicalUrl: $canonical,
            ogType: 'website',
            imageUrl: $this->brandImageUrl(),
            imageAlt: $siteName,
        );
    }

    public function forProduct(int $id, string $locale): ShareMeta
    {
        $product = Product::query()
            ->active()
            ->with(['translations', 'images'])
            ->findOrFail($id);

        $name = $product->translatedName($locale) ?? (string) $product->code ?? (string) $product->getKey();
        $description = $this->truncateDescription(
            $product->translatedDescription($locale) ?? (string) __('seo.product_fallback_description', ['name' => $name, 'store' => config('app.name')], $locale)
        );
        $canonical = url('/products/'.$product->getKey());
        $imageUrl = $this->catalogImageUrl($product->images->first()?->url);

        return new ShareMeta(
            title: $name,
            description: $description,
            canonicalUrl: $canonical,
            ogType: 'product',
            imageUrl: $imageUrl,
            imageAlt: $name,
        );
    }

    public function forPack(int $id, string $locale): ShareMeta
    {
        $pack = Pack::query()
            ->active()
            ->with(['translations', 'images'])
            ->findOrFail($id);

        $name = $pack->translatedName($locale) ?? (string) $pack->getKey();
        $description = $this->truncateDescription(
            $pack->translatedDescription($locale) ?? (string) __('seo.pack_fallback_description', ['name' => $name, 'store' => config('app.name')], $locale)
        );
        $canonical = url('/packs/'.$pack->getKey());
        $imageUrl = $this->catalogImageUrl($pack->images->first()?->url);

        return new ShareMeta(
            title: $name,
            description: $description,
            canonicalUrl: $canonical,
            ogType: 'product',
            imageUrl: $imageUrl,
            imageAlt: $name,
        );
    }

    public function forCategory(int $id, string $locale): ShareMeta
    {
        $category = ProductCategory::query()
            ->active()
            ->with('translations')
            ->findOrFail($id);

        $name = $category->translatedName($locale) ?? (string) $category->code ?? (string) $category->getKey();
        $siteName = config('app.name');
        $description = (string) __('seo.category_description', ['category' => $name, 'store' => $siteName], $locale);
        $canonical = url('/categories/'.$category->getKey().'/products');

        return new ShareMeta(
            title: (string) __('seo.category_title', ['category' => $name, 'store' => $siteName], $locale),
            description: $description,
            canonicalUrl: $canonical,
            ogType: 'website',
            imageUrl: $this->brandImageUrl(),
            imageAlt: $name,
        );
    }

    public function forFallback(string $locale): ShareMeta
    {
        return $this->forHome($locale);
    }

    private function brandImageUrl(): string
    {
        $path = (string) config('mail.brand.default_logo', 'images/serraller_solidaria_logo_key.png');

        return $this->absoluteUrl('/'.ltrim($path, '/'));
    }

    private function catalogImageUrl(?string $relativePath): string
    {
        if ($relativePath === null || trim($relativePath) === '') {
            return $this->brandImageUrl();
        }

        return $this->absoluteUrl($relativePath);
    }

    private function absoluteUrl(string $path): string
    {
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) {
            return $path;
        }

        return url($path);
    }

    private function truncateDescription(?string $text, int $max = 200): string
    {
        $plain = trim(strip_tags((string) $text));
        if ($plain === '') {
            return '';
        }

        return Str::limit($plain, $max, '…');
    }
}
