<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ShopSetting;
use Illuminate\Support\Facades\DB;

class RecalculateTrendingProducts
{
    public function run(): int
    {
        return (int) DB::transaction(function (): int {
            Product::query()->active()->update(['is_trending' => false]);

            $ids = $this->collectMatchingProductIds();
            if ($ids === []) {
                return 0;
            }

            Product::query()->active()->whereIn('id', $ids)->update(['is_trending' => true]);

            return count($ids);
        });
    }

    /**
     * @return list<int>
     */
    private function collectMatchingProductIds(): array
    {
        $merged = [];

        if (ShopSetting::get(ShopSetting::KEY_LOW_STOCK_ENABLED, false)) {
            $threshold = (int) ShopSetting::get(ShopSetting::KEY_LOW_STOCK_THRESHOLD, 0);
            $q = Product::query()->active()->where('stock', '<=', $threshold);
            $this->applyBlacklist($q, ShopSetting::KEY_LOW_STOCK_BLACKLIST_ENABLED, ShopSetting::KEY_LOW_STOCK_BLACKLIST_PRODUCT_IDS);
            $merged = array_merge($merged, $q->pluck('id')->all());
        }

        if (ShopSetting::get(ShopSetting::KEY_OVERSTOCK_ENABLED, false)) {
            $threshold = (int) ShopSetting::get(ShopSetting::KEY_OVERSTOCK_THRESHOLD, 0);
            $q = Product::query()->active()->where('stock', '>=', $threshold);
            $this->applyBlacklist($q, ShopSetting::KEY_OVERSTOCK_BLACKLIST_ENABLED, ShopSetting::KEY_OVERSTOCK_BLACKLIST_PRODUCT_IDS);
            $merged = array_merge($merged, $q->pluck('id')->all());
        }

        $merged = array_values(array_unique(array_map('intval', $merged)));

        sort($merged);

        return $merged;
    }

    private function applyBlacklist(\Illuminate\Database\Eloquent\Builder $query, string $enabledKey, string $idsKey): void
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
