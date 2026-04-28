<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ShopSetting;
use Illuminate\Database\Eloquent\Builder;

/**
 * Builds ordered product IDs for GET products/featured: manual featured, then low-stock trending, then overstock trending.
 * Respects per-group caps from shop settings; 0 = unlimited. Random order within each capped group.
 */
class HomeFeaturedProductIds
{
    /**
     * @return list<int>
     */
    public function orderedIds(): array
    {
        $maxManual = (int) ShopSetting::get(ShopSetting::KEY_FEATURED_MAX_MANUAL, 0);
        $maxLow = (int) ShopSetting::get(ShopSetting::KEY_FEATURED_MAX_LOW_STOCK, 0);
        $maxOver = (int) ShopSetting::get(ShopSetting::KEY_FEATURED_MAX_OVERSTOCK, 0);

        $manualIds = $this->sampleIds(
            Product::query()->active()->where('is_featured', true),
            $maxManual
        );

        $lowIds = [];
        $lowRule = TrendingStockRuleQueries::activeLowStockRuleQuery();
        if ($lowRule !== null) {
            $lowIds = $this->sampleIds(
                (clone $lowRule)->where('is_trending', true),
                $maxLow
            );
        }

        $overIds = [];
        $overRule = TrendingStockRuleQueries::activeOverstockRuleQuery();
        if ($overRule !== null) {
            $q = clone $overRule;
            $lowForExclude = TrendingStockRuleQueries::activeLowStockRuleQuery();
            if ($lowForExclude !== null) {
                $excludeIds = $lowForExclude->pluck('id')->all();
                if ($excludeIds !== []) {
                    $q->whereNotIn('id', $excludeIds);
                }
            }
            $overIds = $this->sampleIds(
                $q->where('is_trending', true),
                $maxOver
            );
        }

        $seen = [];
        $order = [];
        foreach (array_merge($manualIds, $lowIds, $overIds) as $id) {
            $id = (int) $id;
            if ($id <= 0 || isset($seen[$id])) {
                continue;
            }
            $seen[$id] = true;
            $order[] = $id;
        }

        return $order;
    }

    /**
     * @return list<int>
     */
    private function sampleIds(Builder $query, int $max): array
    {
        if ($max <= 0) {
            return array_map('intval', $query->pluck('id')->all());
        }

        return array_map('intval', $query->inRandomOrder()->limit($max)->pluck('id')->all());
    }
}
