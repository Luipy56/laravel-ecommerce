<?php

declare(strict_types=1);

namespace App\Support;

use App\Models\Feature;
use App\Models\FeatureName;
use App\Models\FeatureTranslation;
use App\Models\FeatureNameTranslation;
use App\Models\Pack;
use App\Models\PackTranslation;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ProductCategoryTranslation;
use App\Models\ProductTranslation;

/**
 * Upserts catalog translation rows from nested locale payloads (admin / imports).
 */
final class CatalogTranslationSync
{
    /**
     * @param  array<string, array{name?: mixed, description?: mixed}>  $byLocale
     */
    public static function syncProductTranslations(Product $product, array $byLocale): void
    {
        foreach (CatalogLocale::SUPPORTED as $locale) {
            if (! isset($byLocale[$locale]) || ! is_array($byLocale[$locale])) {
                continue;
            }
            $name = $byLocale[$locale]['name'] ?? null;
            $desc = $byLocale[$locale]['description'] ?? null;
            $nameStr = is_string($name) ? trim($name) : (is_numeric($name) ? (string) $name : null);
            $descStr = is_string($desc) ? $desc : null;
            if (($nameStr === null || $nameStr === '') && ($descStr === null || trim((string) $descStr) === '')) {
                ProductTranslation::query()
                    ->where('product_id', $product->id)
                    ->where('locale', $locale)
                    ->delete();

                continue;
            }
            ProductTranslation::query()->updateOrCreate(
                ['product_id' => $product->id, 'locale' => $locale],
                [
                    'name' => $nameStr !== '' ? $nameStr : null,
                    'description' => $descStr !== null && trim((string) $descStr) !== '' ? $descStr : null,
                ],
            );
        }
    }

    /**
     * @param  array<string, array{name?: mixed, description?: mixed}>  $byLocale
     */
    public static function syncPackTranslations(Pack $pack, array $byLocale): void
    {
        foreach (CatalogLocale::SUPPORTED as $locale) {
            if (! isset($byLocale[$locale]) || ! is_array($byLocale[$locale])) {
                continue;
            }
            $name = $byLocale[$locale]['name'] ?? null;
            $desc = $byLocale[$locale]['description'] ?? null;
            $nameStr = is_string($name) ? trim($name) : (is_numeric($name) ? (string) $name : null);
            $descStr = is_string($desc) ? $desc : null;
            if (($nameStr === null || $nameStr === '') && ($descStr === null || trim((string) $descStr) === '')) {
                PackTranslation::query()->where('pack_id', $pack->id)->where('locale', $locale)->delete();

                continue;
            }
            PackTranslation::query()->updateOrCreate(
                ['pack_id' => $pack->id, 'locale' => $locale],
                [
                    'name' => $nameStr !== '' ? $nameStr : null,
                    'description' => $descStr !== null && trim((string) $descStr) !== '' ? $descStr : null,
                ],
            );
        }
    }

    /**
     * @param  array<string, array{name?: mixed}>  $byLocale
     */
    public static function syncCategoryTranslations(ProductCategory $category, array $byLocale): void
    {
        foreach (CatalogLocale::SUPPORTED as $locale) {
            if (! isset($byLocale[$locale]) || ! is_array($byLocale[$locale])) {
                continue;
            }
            $name = $byLocale[$locale]['name'] ?? null;
            $nameStr = is_string($name) ? trim($name) : (is_numeric($name) ? (string) $name : null);
            if ($nameStr === null || $nameStr === '') {
                ProductCategoryTranslation::query()
                    ->where('product_category_id', $category->id)
                    ->where('locale', $locale)
                    ->delete();

                continue;
            }
            ProductCategoryTranslation::query()->updateOrCreate(
                ['product_category_id' => $category->id, 'locale' => $locale],
                ['name' => $nameStr],
            );
        }
    }

    /**
     * @param  array<string, array{name?: mixed}>  $byLocale
     */
    public static function syncFeatureNameTranslations(FeatureName $featureName, array $byLocale): void
    {
        foreach (CatalogLocale::SUPPORTED as $locale) {
            if (! isset($byLocale[$locale]) || ! is_array($byLocale[$locale])) {
                continue;
            }
            $name = $byLocale[$locale]['name'] ?? null;
            $nameStr = is_string($name) ? trim($name) : (is_numeric($name) ? (string) $name : null);
            if ($nameStr === null || $nameStr === '') {
                FeatureNameTranslation::query()
                    ->where('feature_name_id', $featureName->id)
                    ->where('locale', $locale)
                    ->delete();

                continue;
            }
            FeatureNameTranslation::query()->updateOrCreate(
                ['feature_name_id' => $featureName->id, 'locale' => $locale],
                ['name' => $nameStr],
            );
        }
    }

    /**
     * @param  array<string, array{value?: mixed}>  $byLocale
     */
    public static function syncFeatureTranslations(Feature $feature, array $byLocale): void
    {
        foreach (CatalogLocale::SUPPORTED as $locale) {
            if (! isset($byLocale[$locale]) || ! is_array($byLocale[$locale])) {
                continue;
            }
            $value = $byLocale[$locale]['value'] ?? null;
            $valueStr = is_string($value) ? trim($value) : (is_numeric($value) ? (string) $value : null);
            if ($valueStr === null || $valueStr === '') {
                FeatureTranslation::query()->where('feature_id', $feature->id)->where('locale', $locale)->delete();

                continue;
            }
            FeatureTranslation::query()->updateOrCreate(
                ['feature_id' => $feature->id, 'locale' => $locale],
                ['value' => $valueStr],
            );
        }
    }
}
