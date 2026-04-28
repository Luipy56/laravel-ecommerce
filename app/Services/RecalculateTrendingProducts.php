<?php

namespace App\Services;

use App\Models\Product;
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

        $low = TrendingStockRuleQueries::activeLowStockRuleQuery();
        if ($low !== null) {
            $merged = array_merge($merged, $low->pluck('id')->all());
        }

        $over = TrendingStockRuleQueries::activeOverstockRuleQuery();
        if ($over !== null) {
            $merged = array_merge($merged, $over->pluck('id')->all());
        }

        $merged = array_values(array_unique(array_map('intval', $merged)));

        sort($merged);

        return $merged;
    }
}
