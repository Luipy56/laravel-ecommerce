<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ShopSetting;
use Illuminate\Database\Eloquent\Builder;

/**
 * Stock low / overstock rule queries shared by RecalculateTrendingProducts and home featured sampling.
 */
class TrendingStockRuleQueries
{
    /**
     * Active products matching the low-stock trending rule (when the rule is enabled).
     */
    public static function activeLowStockRuleQuery(): ?Builder
    {
        if (! ShopSetting::get(ShopSetting::KEY_LOW_STOCK_ENABLED, false)) {
            return null;
        }

        $threshold = (int) ShopSetting::get(ShopSetting::KEY_LOW_STOCK_THRESHOLD, 0);
        $q = Product::query()->active()->where('stock', '<=', $threshold);
        self::applyBlacklist($q, ShopSetting::KEY_LOW_STOCK_BLACKLIST_ENABLED, ShopSetting::KEY_LOW_STOCK_BLACKLIST_PRODUCT_IDS);

        return $q;
    }

    /**
     * Active products matching the overstock trending rule (when the rule is enabled).
     */
    public static function activeOverstockRuleQuery(): ?Builder
    {
        if (! ShopSetting::get(ShopSetting::KEY_OVERSTOCK_ENABLED, false)) {
            return null;
        }

        $threshold = (int) ShopSetting::get(ShopSetting::KEY_OVERSTOCK_THRESHOLD, 0);
        $q = Product::query()->active()->where('stock', '>=', $threshold);
        self::applyBlacklist($q, ShopSetting::KEY_OVERSTOCK_BLACKLIST_ENABLED, ShopSetting::KEY_OVERSTOCK_BLACKLIST_PRODUCT_IDS);

        return $q;
    }

    private static function applyBlacklist(Builder $query, string $enabledKey, string $idsKey): void
    {
        if (! ShopSetting::get($enabledKey, false)) {
            return;
        }
        $ids = ShopSetting::get($idsKey, []);
        if (! is_array($ids) || $ids === []) {
            return;
        }
        $ids = array_values(array_unique(array_filter(array_map('intval', $ids), fn (int $id) => $id > 0)));
        if ($ids === []) {
            return;
        }
        $query->whereNotIn('id', $ids);
    }
}
