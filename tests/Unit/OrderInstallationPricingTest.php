<?php

namespace Tests\Unit;

use App\Models\Order;
use App\Models\ShopSetting;
use PHPUnit\Framework\TestCase;

class OrderInstallationPricingTest extends TestCase
{
    public static function tierProvider(): array
    {
        return [
            'low tier' => [100.0, 90.0],
            'at_250' => [250.0, 90.0],
            'mid tier' => [300.0, 120.0],
            'at_500' => [500.0, 120.0],
            'high tier' => [750.0, 180.0],
            'max automatic' => [1000.0, 180.0],
        ];
    }

    /**
     * @dataProvider tierProvider
     */
    public function test_automatic_fee_matches_merchandise_brackets(float $merchandise, float $expectedFee): void
    {
        $fee = Order::automaticInstallationFeeFromMerchandiseSubtotal($merchandise, ShopSetting::DEFAULTS);
        $this->assertNotNull($fee);
        $this->assertSame($expectedFee, $fee);
    }

    public function test_over_max_returns_null(): void
    {
        $this->assertNull(Order::automaticInstallationFeeFromMerchandiseSubtotal(1000.01, ShopSetting::DEFAULTS));
    }

    public function test_non_positive_returns_null(): void
    {
        $this->assertNull(Order::automaticInstallationFeeFromMerchandiseSubtotal(0, ShopSetting::DEFAULTS));
    }

    public function test_custom_tier_from_merged_settings(): void
    {
        $merged = array_merge(ShopSetting::DEFAULTS, [
            ShopSetting::KEY_INSTALLATION_AUTO_PRICING => [
                'quote_when_merchandise_above_eur' => 400,
                'tiers' => [
                    ['max_merchandise_eur' => 200, 'fee_eur' => 50],
                    ['max_merchandise_eur' => 400, 'fee_eur' => 75],
                ],
            ],
        ]);
        $this->assertSame(50.0, Order::automaticInstallationFeeFromMerchandiseSubtotal(100, $merged));
        $this->assertNull(Order::automaticInstallationFeeFromMerchandiseSubtotal(401, $merged));
    }
}
