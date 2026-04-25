<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShopSetting;
use App\Services\RecalculateTrendingProducts;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AdminShopSettingsController extends Controller
{
    public function show(): JsonResponse
    {
        $data = ShopSetting::allMerged();

        return response()->json([
            'success' => true,
            'data' => $this->shapeForClient($data),
        ]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'low_stock_enabled' => ['boolean'],
            'low_stock_threshold' => ['integer', 'min:0'],
            'low_stock_blacklist_enabled' => ['boolean'],
            'low_stock_blacklist_product_ids' => ['array'],
            'low_stock_blacklist_product_ids.*' => ['integer', 'min:1'],
            'overstock_enabled' => ['boolean'],
            'overstock_threshold' => ['integer', 'min:0'],
            'overstock_blacklist_enabled' => ['boolean'],
            'overstock_blacklist_product_ids' => ['array'],
            'overstock_blacklist_product_ids.*' => ['integer', 'min:1'],
            'accept_personalized_solutions' => ['boolean'],
        ]);

        foreach ($this->mapRequestToKeys($validated) as $key => $value) {
            ShopSetting::set($key, $value);
        }

        return response()->json([
            'success' => true,
            'data' => $this->shapeForClient(ShopSetting::allMerged()),
        ]);
    }

    public function recalculateTrending(RecalculateTrendingProducts $service): JsonResponse
    {
        $count = $service->run();

        return response()->json([
            'success' => true,
            'data' => [
                'updated_count' => $count,
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $merged
     * @return array<string, mixed>
     */
    private function shapeForClient(array $merged): array
    {
        return [
            'low_stock_enabled' => (bool) ($merged[ShopSetting::KEY_LOW_STOCK_ENABLED] ?? false),
            'low_stock_threshold' => (int) ($merged[ShopSetting::KEY_LOW_STOCK_THRESHOLD] ?? 0),
            'low_stock_blacklist_enabled' => (bool) ($merged[ShopSetting::KEY_LOW_STOCK_BLACKLIST_ENABLED] ?? false),
            'low_stock_blacklist_product_ids' => $this->intArray($merged[ShopSetting::KEY_LOW_STOCK_BLACKLIST_PRODUCT_IDS] ?? []),
            'overstock_enabled' => (bool) ($merged[ShopSetting::KEY_OVERSTOCK_ENABLED] ?? false),
            'overstock_threshold' => (int) ($merged[ShopSetting::KEY_OVERSTOCK_THRESHOLD] ?? 0),
            'overstock_blacklist_enabled' => (bool) ($merged[ShopSetting::KEY_OVERSTOCK_BLACKLIST_ENABLED] ?? false),
            'overstock_blacklist_product_ids' => $this->intArray($merged[ShopSetting::KEY_OVERSTOCK_BLACKLIST_PRODUCT_IDS] ?? []),
            'accept_personalized_solutions' => (bool) ($merged[ShopSetting::KEY_ACCEPT_PERSONALIZED_SOLUTIONS] ?? true),
        ];
    }

    /**
     * @param  array<string, mixed>  $validated
     * @return array<string, mixed>
     */
    private function mapRequestToKeys(array $validated): array
    {
        $map = [
            'low_stock_enabled' => ShopSetting::KEY_LOW_STOCK_ENABLED,
            'low_stock_threshold' => ShopSetting::KEY_LOW_STOCK_THRESHOLD,
            'low_stock_blacklist_enabled' => ShopSetting::KEY_LOW_STOCK_BLACKLIST_ENABLED,
            'low_stock_blacklist_product_ids' => ShopSetting::KEY_LOW_STOCK_BLACKLIST_PRODUCT_IDS,
            'overstock_enabled' => ShopSetting::KEY_OVERSTOCK_ENABLED,
            'overstock_threshold' => ShopSetting::KEY_OVERSTOCK_THRESHOLD,
            'overstock_blacklist_enabled' => ShopSetting::KEY_OVERSTOCK_BLACKLIST_ENABLED,
            'overstock_blacklist_product_ids' => ShopSetting::KEY_OVERSTOCK_BLACKLIST_PRODUCT_IDS,
            'accept_personalized_solutions' => ShopSetting::KEY_ACCEPT_PERSONALIZED_SOLUTIONS,
        ];
        $out = [];
        foreach ($map as $requestKey => $dbKey) {
            if (array_key_exists($requestKey, $validated)) {
                $out[$dbKey] = $validated[$requestKey];
            }
        }

        return $out;
    }

    /**
     * @return list<int>
     */
    private function intArray(mixed $value): array
    {
        if (! is_array($value)) {
            return [];
        }

        return array_values(array_unique(array_filter(array_map('intval', $value), fn (int $id) => $id > 0)));
    }
}
