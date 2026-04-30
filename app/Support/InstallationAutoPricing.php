<?php

namespace App\Support;

use App\Models\ShopSetting;

/**
 * Installation fee tiers from shop_settings (merchandise subtotal only).
 */
final class InstallationAutoPricing
{
    /**
     * @param  list<array{max: float, fee: float}>  $sortedTiers  ascending by max
     */
    private function __construct(
        private readonly float $quoteAboveMerchandiseEur,
        private readonly array $sortedTiers,
    ) {}

    /**
     * @param  array<string, mixed>  $merged  ShopSetting::allMerged()
     */
    public static function fromMerged(array $merged): self
    {
        $raw = $merged[ShopSetting::KEY_INSTALLATION_AUTO_PRICING] ?? null;
        if (! is_array($raw)) {
            $raw = ShopSetting::DEFAULTS[ShopSetting::KEY_INSTALLATION_AUTO_PRICING];
        }

        return self::fromArray($raw);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $quoteAbove = round((float) ($data['quote_when_merchandise_above_eur'] ?? 0), 2);
        $tiersIn = $data['tiers'] ?? [];
        $pairs = [];
        if (is_array($tiersIn)) {
            foreach ($tiersIn as $row) {
                if (! is_array($row)) {
                    continue;
                }
                $max = round((float) ($row['max_merchandise_eur'] ?? 0), 2);
                $fee = round((float) ($row['fee_eur'] ?? 0), 2);
                if ($max > 0) {
                    $pairs[] = ['max' => $max, 'fee' => $fee];
                }
            }
        }
        usort($pairs, fn (array $a, array $b) => $a['max'] <=> $b['max']);

        return new self($quoteAbove, $pairs);
    }

    public function quoteWhenMerchandiseAboveEur(): float
    {
        return $this->quoteAboveMerchandiseEur;
    }

    public function quoteRequired(float $merchandiseSubtotal): bool
    {
        return $merchandiseSubtotal > $this->quoteAboveMerchandiseEur;
    }

    public function automaticFee(float $merchandiseSubtotal): ?float
    {
        if ($merchandiseSubtotal <= 0) {
            return null;
        }
        if ($this->quoteRequired($merchandiseSubtotal)) {
            return null;
        }
        foreach ($this->sortedTiers as $tier) {
            if ($merchandiseSubtotal <= $tier['max']) {
                return $tier['fee'];
            }
        }

        return null;
    }

    /**
     * @return list<array{max_merchandise_eur: float, fee_eur: float}>
     */
    public function tiersForStorage(): array
    {
        $out = [];
        foreach ($this->sortedTiers as $t) {
            $out[] = [
                'max_merchandise_eur' => $t['max'],
                'fee_eur' => $t['fee'],
            ];
        }

        return $out;
    }

    /**
     * @return array{quote_when_merchandise_above_eur: float, tiers: list<array{max_merchandise_eur: float, fee_eur: float}>}
     */
    public function toStorageArray(): array
    {
        return [
            'quote_when_merchandise_above_eur' => $this->quoteAboveMerchandiseEur,
            'tiers' => $this->tiersForStorage(),
        ];
    }
}
