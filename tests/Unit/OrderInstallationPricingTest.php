<?php

namespace Tests\Unit;

use App\Models\Order;
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
        $fee = Order::automaticInstallationFeeFromMerchandiseSubtotal($merchandise);
        $this->assertNotNull($fee);
        $this->assertSame($expectedFee, $fee);
    }

    public function test_over_max_returns_null(): void
    {
        $this->assertNull(Order::automaticInstallationFeeFromMerchandiseSubtotal(1000.01));
    }

    public function test_non_positive_returns_null(): void
    {
        $this->assertNull(Order::automaticInstallationFeeFromMerchandiseSubtotal(0));
    }
}
