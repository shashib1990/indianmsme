<?php

namespace Tests\Feature;

use App\Models\AdcOrder;
use App\Models\DdcPosOrder;
use App\Models\User;
use App\Services\OrderNumberService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderNumberServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_number_format_and_sequence()
    {
        // Set date in FY 26-27 (e.g., August 17, 2026)
        $date = '2026-08-17';

        $user = User::factory()->create();

        $orderNo1 = OrderNumberService::generateOrderNumber($date);
        $this->assertEquals('ORD/26-27/000001', $orderNo1);

        // Create an AdcOrder using that number
        AdcOrder::create([
            'order_number' => $orderNo1,
            'adc_id' => $user->id,
            'total_amount' => 1000.00,
        ]);

        // Next number should be 000002
        $orderNo2 = OrderNumberService::generateOrderNumber($date);
        $this->assertEquals('ORD/26-27/000002', $orderNo2);

        // Create a DdcPosOrder using that number
        DdcPosOrder::create([
            'order_no' => $orderNo2,
            'ddc_id' => $user->id,
            'mode' => 'retail',
            'financial_year' => '26-27',
            'serial_number' => 2,
            'total_amount' => 500.00,
        ]);

        // Next number should be 000003
        $orderNo3 = OrderNumberService::generateOrderNumber($date);
        $this->assertEquals('ORD/26-27/000003', $orderNo3);
    }

    public function test_financial_year_reset()
    {
        $user = User::factory()->create();

        // Current FY: 2026-08-17 (FY 26-27)
        $dateCurrent = '2026-08-17';
        $orderNo1 = OrderNumberService::generateOrderNumber($dateCurrent);
        $this->assertEquals('ORD/26-27/000001', $orderNo1);

        AdcOrder::create([
            'order_number' => $orderNo1,
            'adc_id' => $user->id,
            'total_amount' => 1000.00,
        ]);

        // Next FY: 2027-04-01 (FY 27-28) -> should reset sequence to 000001
        $dateNextFy = '2027-04-01';
        $orderNoNextFy = OrderNumberService::generateOrderNumber($dateNextFy);
        $this->assertEquals('ORD/27-28/000001', $orderNoNextFy);
    }

    public function test_model_helper_integration()
    {
        $date = '2026-08-17';

        $adcOrderNo = AdcOrder::generateOrderNumber($date);
        $this->assertStringStartsWith('ORD/26-27/', $adcOrderNo);

        $retailPosDetails = DdcPosOrder::generateNextPosInvoiceNumber(1, 'retail', $date);
        $this->assertArrayHasKey('order_no', $retailPosDetails);
        $this->assertStringStartsWith('ORD/26-27/', $retailPosDetails['order_no']);

        $retailInvoice = \App\Models\Invoice::generateNextInvoiceNumber($date, '07AAAAA0000A1Z5', 'DDC003', 'retail');
        $this->assertStringContainsString('DDC003/R/26-27/', $retailInvoice['invoice_number']);
    }
}
