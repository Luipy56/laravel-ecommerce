<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ShopSetting;
use App\Services\RecalculateTrendingProducts;
use App\Support\AdminIndexColumns;
use App\Support\InstallationAutoPricing;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

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
        $validated = $request->validate(array_merge([
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
            'featured_max_manual' => ['integer', 'min:0'],
            'featured_max_low_stock' => ['integer', 'min:0'],
            'featured_max_overstock' => ['integer', 'min:0'],
            'shipping_flat_eur' => ['sometimes', 'numeric', 'min:0', 'max:99999.99'],
            'bank_transfer_iban' => ['nullable', 'string', 'max:64'],
            'bank_transfer_beneficiary' => ['nullable', 'string', 'max:255'],
            'bank_transfer_reference_hint' => ['nullable', 'string', 'max:500'],
            'bizum_manual_phone' => ['nullable', 'string', 'max:80'],
            'bizum_manual_instructions' => ['nullable', 'string', 'max:2000'],
            'installation_auto_pricing' => ['sometimes', 'array'],
            'installation_auto_pricing.quote_when_merchandise_above_eur' => ['required_with:installation_auto_pricing', 'numeric', 'min:0', 'max:999999'],
            'installation_auto_pricing.tiers' => ['required_with:installation_auto_pricing', 'array', 'min:1'],
            'installation_auto_pricing.tiers.*.max_merchandise_eur' => ['required', 'numeric', 'min:0.01', 'max:999999'],
            'installation_auto_pricing.tiers.*.fee_eur' => ['required', 'numeric', 'min:0', 'max:999999'],
        ], $this->adminIndexColumnsValidationRules()));

        if (array_key_exists('installation_auto_pricing', $validated) && is_array($validated['installation_auto_pricing'])) {
            $validated['installation_auto_pricing'] = $this->normalizeInstallationAutoPricingOrFail(
                $validated['installation_auto_pricing']
            );
        }

        foreach ($this->mapRequestToKeys($validated) as $key => $value) {
            ShopSetting::set($key, $value);
        }

        if (array_key_exists('admin_index_columns', $validated)) {
            ShopSetting::set(
                ShopSetting::KEY_ADMIN_INDEX_COLUMNS,
                AdminIndexColumns::normalize($validated['admin_index_columns'])
            );
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
        $storedColumns = $merged[ShopSetting::KEY_ADMIN_INDEX_COLUMNS] ?? null;

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
            'featured_max_manual' => (int) ($merged[ShopSetting::KEY_FEATURED_MAX_MANUAL] ?? 0),
            'featured_max_low_stock' => (int) ($merged[ShopSetting::KEY_FEATURED_MAX_LOW_STOCK] ?? 0),
            'featured_max_overstock' => (int) ($merged[ShopSetting::KEY_FEATURED_MAX_OVERSTOCK] ?? 0),
            'shipping_flat_eur' => round((float) ($merged[ShopSetting::KEY_SHIPPING_FLAT_EUR] ?? ShopSetting::DEFAULTS[ShopSetting::KEY_SHIPPING_FLAT_EUR]), 2),
            'installation_auto_pricing' => InstallationAutoPricing::fromMerged($merged)->toStorageArray(),
            'bank_transfer_iban' => (string) ($merged[ShopSetting::KEY_BANK_TRANSFER_IBAN] ?? ''),
            'bank_transfer_beneficiary' => (string) ($merged[ShopSetting::KEY_BANK_TRANSFER_BENEFICIARY] ?? ''),
            'bank_transfer_reference_hint' => (string) ($merged[ShopSetting::KEY_BANK_TRANSFER_REFERENCE_HINT] ?? ''),
            'bizum_manual_phone' => (string) ($merged[ShopSetting::KEY_BIZUM_MANUAL_PHONE] ?? ''),
            'bizum_manual_instructions' => (string) ($merged[ShopSetting::KEY_BIZUM_MANUAL_INSTRUCTIONS] ?? ''),
            'admin_index_columns' => AdminIndexColumns::normalize(is_array($storedColumns) ? $storedColumns : null),
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     * @return array{quote_when_merchandise_above_eur: float, tiers: list<array{max_merchandise_eur: float, fee_eur: float}>}
     */
    private function normalizeInstallationAutoPricingOrFail(array $input): array
    {
        $pricing = InstallationAutoPricing::fromArray($input);
        $storage = $pricing->toStorageArray();
        $tiers = $storage['tiers'];
        if ($tiers === []) {
            throw ValidationException::withMessages([
                'installation_auto_pricing' => [__('validation.custom.installation_auto_pricing.tiers_required')],
            ]);
        }
        $quote = $storage['quote_when_merchandise_above_eur'];
        $lastMax = $tiers[count($tiers) - 1]['max_merchandise_eur'];
        if (abs($lastMax - $quote) > 0.02) {
            throw ValidationException::withMessages([
                'installation_auto_pricing' => [__('validation.custom.installation_auto_pricing.last_tier_max_must_match_quote')],
            ]);
        }
        for ($i = 1, $n = count($tiers); $i < $n; $i++) {
            if ($tiers[$i]['max_merchandise_eur'] <= $tiers[$i - 1]['max_merchandise_eur']) {
                throw ValidationException::withMessages([
                    'installation_auto_pricing' => [__('validation.custom.installation_auto_pricing.tiers_must_increase')],
                ]);
            }
        }

        return $storage;
    }

    /**
     * @return array<string, mixed>
     */
    private function adminIndexColumnsValidationRules(): array
    {
        $rules = [
            'admin_index_columns' => ['sometimes', 'nullable', 'array'],
        ];
        foreach (AdminIndexColumns::registry() as $tableId => $allowed) {
            $rules['admin_index_columns.'.$tableId] = ['nullable', 'array'];
            $rules['admin_index_columns.'.$tableId.'.*'] = ['string', Rule::in($allowed)];
        }

        return $rules;
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
            'featured_max_manual' => ShopSetting::KEY_FEATURED_MAX_MANUAL,
            'featured_max_low_stock' => ShopSetting::KEY_FEATURED_MAX_LOW_STOCK,
            'featured_max_overstock' => ShopSetting::KEY_FEATURED_MAX_OVERSTOCK,
            'shipping_flat_eur' => ShopSetting::KEY_SHIPPING_FLAT_EUR,
            'installation_auto_pricing' => ShopSetting::KEY_INSTALLATION_AUTO_PRICING,
            'bank_transfer_iban' => ShopSetting::KEY_BANK_TRANSFER_IBAN,
            'bank_transfer_beneficiary' => ShopSetting::KEY_BANK_TRANSFER_BENEFICIARY,
            'bank_transfer_reference_hint' => ShopSetting::KEY_BANK_TRANSFER_REFERENCE_HINT,
            'bizum_manual_phone' => ShopSetting::KEY_BIZUM_MANUAL_PHONE,
            'bizum_manual_instructions' => ShopSetting::KEY_BIZUM_MANUAL_INSTRUCTIONS,
        ];
        $out = [];
        foreach ($map as $requestKey => $dbKey) {
            if (array_key_exists($requestKey, $validated)) {
                $value = $validated[$requestKey];
                if ($requestKey === 'shipping_flat_eur') {
                    $value = round((float) $value, 2);
                }
                $out[$dbKey] = $value;
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
