<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Services\PricingService;

class PricingServiceTest extends TestCase
{
    /**
     * Test calculation logic matching user example specification.
     */
    public function test_pricing_calculation_with_user_example_values(): void
    {
        $sellerPrice = 1500.00;
        $gstRate = 5.00;
        $mrp = 2000.00;
        $adminMargin = 8.00;
        $ddcMargin = 5.00;
        $mbpPercent = 2.00;

        $result = PricingService::calculate(
            $sellerPrice,
            $gstRate,
            $mrp,
            $adminMargin,
            $ddcMargin,
            $mbpPercent
        );

        $this->assertEquals(1428.57, $result['base_cost']); // 1500 / 1.05 = 1428.57
        $this->assertEquals(114.29, $result['admin_margin_amount']); // 1428.57 * 8% = 114.29
        $this->assertEquals(1620.00, $result['ddc_purchase_price']); // 1542.86 * 1.05 = 1620.00
        $this->assertEquals(77.14, $result['ddc_margin_amount']); // 1542.86 * 5% = 77.14
        $this->assertEquals(1620.00, $result['price_before_gst']); // 1542.86 + 77.14 = 1620.00
        $this->assertEquals(81.00, $result['seller_gst_amount']); // 1620.00 * 5% = 81.00
        $this->assertEquals(1701.00, $result['adc_purchase_price']); // 1620.00 + 81.00 = 1701.00
    }

    /**
     * Test standard 18% GST explicit calculation.
     */
    public function test_pricing_calculation_with_explicit_percentages(): void
    {
        $sellerPrice = 100.00;
        $gstRate = 18.00;
        $mrp = 150.00;
        $adminMargin = 8.00;
        $ddcMargin = 5.00;
        $mbpPercent = 2.00;

        $result = PricingService::calculate(
            $sellerPrice,
            $gstRate,
            $mrp,
            $adminMargin,
            $ddcMargin,
            $mbpPercent
        );

        $this->assertEquals(84.75, $result['base_cost']); // 100 / 1.18 = 84.75
        $this->assertEquals(15.25, $result['company_gst_amount']); // 100 - 84.75 = 15.25
        $this->assertEquals(6.78, $result['admin_margin_amount']); // 84.75 * 8% = 6.78
        $this->assertEquals(4.58, $result['ddc_margin_amount']); // (84.75 + 6.78) * 5% = 91.53 * 5% = 4.58
        $this->assertEquals(96.11, $result['price_before_gst']); // 91.53 + 4.58 = 96.11
        $this->assertEquals(17.30, $result['seller_gst_amount']); // 96.11 * 18% = 17.30
        $this->assertEquals(113.41, $result['adc_purchase_price']); // 96.11 + 17.30 = 113.41

        // Verify mathematical invariants
        $this->assertEquals($result['mbp_amount'], round($result['mbp_90_share'] + $result['mbp_10_share'], 2));
        $this->assertEquals($result['mbp_90_share'], round($result['mbp_38_share'] + $result['mbp_62_share'], 2));
    }
}
