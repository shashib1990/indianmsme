<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Vendor;
use App\Models\DDC;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\DdcInventory;
use App\Models\DdcPincode;
use App\Models\Pincode;
use App\Models\AdcOrder;
use App\Models\AdcOrderItem;
use App\Models\IncentiveHold;
use App\Models\IncentiveRelease;
use App\Services\IncentiveHoldService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class IncentivePartialReleaseTest extends TestCase
{
    use RefreshDatabase;

    private DDC $ddc;
    private Vendor $seller;
    private Product $productA;
    private Product $productB;
    private PurchaseOrder $po;
    private Pincode $pincode;

    protected function setUp(): void
    {
        parent::setUp();

        // Create DDC
        $this->ddc = DDC::create([
            'name' => 'DDC Coordinator',
            'email' => 'ddc@test.com',
            'password' => bcrypt('password'),
            'role' => 'ddc',
            'status' => '1',
        ]);

        // Create Seller
        $this->seller = Vendor::create([
            'name' => 'Vendor User',
            'email' => 'vendor@test.com',
            'password' => bcrypt('password'),
            'role' => 'seller',
            'status' => '1',
        ]);

        // Update profiles
        $this->ddc->profile->update([
            'kyc_status' => 'APPROVED',
        ]);

        $this->seller->sellerProfile->update([
            'is_ddc_subscribed' => true,
            'ddc_ama_commission_percentage' => 10.00,
            'kyc_status' => 'APPROVED',
        ]);

        // Setup Brand & Category
        $brand = \App\Models\Brand::create(['name' => 'Brand Test', 'slug' => 'brand-test']);
        $category = \App\Models\Category::create(['name' => 'Category Test', 'slug' => 'category-test']);

        // Create Products
        $this->productA = Product::create([
            'seller_id' => $this->seller->id,
            'product_name' => 'Product A',
            'product_code' => 'PROD-A',
            'barcode' => '123456789',
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'mrp' => 100.00,
            'seller_price_incl_gst' => 80.00,
            'gst_rate' => 18.00,
            'status' => 'Approved',
            'is_new' => false,
            'created_by' => $this->seller->id,
            'description' => 'Test Product Description A',
            'product_specification' => 'Test Spec A',
            'storage_instructions' => 'Keep cool',
            'featured_image' => 'http://example.com/image.jpg',
            'gallery' => [],
            'hsn_code' => '123456',
            'unit_of_measure' => 'pcs',
            'net_quantity' => 1,
            'case_pack_quantity' => 1,
            'shelf_life' => '12 months',
            'country_of_origin' => 'India',
            'length' => 10.0,
            'width' => 10.0,
            'height' => 10.0,
            'dimension_unit' => 'cm',
            'weight' => 1.0,
            'weight_unit' => 'kg'
        ]);

        $this->productB = Product::create([
            'seller_id' => $this->seller->id,
            'product_name' => 'Product B',
            'product_code' => 'PROD-B',
            'barcode' => '987654321',
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'mrp' => 200.00,
            'seller_price_incl_gst' => 160.00,
            'gst_rate' => 18.00,
            'status' => 'Approved',
            'is_new' => false,
            'created_by' => $this->seller->id,
            'description' => 'Test Product Description B',
            'product_specification' => 'Test Spec B',
            'storage_instructions' => 'Keep cool',
            'featured_image' => 'http://example.com/image.jpg',
            'gallery' => [],
            'hsn_code' => '123456',
            'unit_of_measure' => 'pcs',
            'net_quantity' => 1,
            'case_pack_quantity' => 1,
            'shelf_life' => '12 months',
            'country_of_origin' => 'India',
            'length' => 10.0,
            'width' => 10.0,
            'height' => 10.0,
            'dimension_unit' => 'cm',
            'weight' => 1.0,
            'weight_unit' => 'kg'
        ]);

        // Create Purchase Order
        $this->po = PurchaseOrder::create([
            'po_number' => 'PO-26-27-0001',
            'ddc_id' => $this->ddc->id,
            'seller_id' => $this->seller->id,
            'total_amount' => 5000.00,
            'status' => 'Processing',
            'payment_status' => 'Paid',
            'seller_accepted_amount' => 4000.00,
        ]);

        // Create PO Items
        PurchaseOrderItem::create([
            'purchase_order_id' => $this->po->id,
            'seller_id' => $this->seller->id,
            'product_id' => $this->productA->id,
            'product_name' => $this->productA->product_name,
            'product_code' => $this->productA->product_code,
            'barcode' => $this->productA->barcode,
            'quantity' => 100,
            'price' => 80.00,
            'total' => 8000.00,
            'seller_price' => 80.00,
            'seller_total' => 8000.00,
            'status' => 'Accepted',
        ]);

        PurchaseOrderItem::create([
            'purchase_order_id' => $this->po->id,
            'seller_id' => $this->seller->id,
            'product_id' => $this->productB->id,
            'product_name' => $this->productB->product_name,
            'product_code' => $this->productB->product_code,
            'barcode' => $this->productB->barcode,
            'quantity' => 50,
            'price' => 160.00,
            'total' => 8000.00,
            'seller_price' => 160.00,
            'seller_total' => 8000.00,
            'status' => 'Accepted',
        ]);

        // Setup State & District
        $state = \App\Models\State::create([
            'name' => 'Uttar Pradesh',
            'code' => 'UP',
            'status' => '1'
        ]);

        $district = \App\Models\District::create([
            'state_id' => $state->id,
            'name' => 'Noida',
            'status' => '1'
        ]);

        // Setup pincode and DDC assignment for ADC orders
        $this->pincode = Pincode::create([
            'pincode' => '400001',
            'state_id' => $state->id,
            'district_id' => $district->id,
            'status' => '1',
        ]);

        DdcPincode::create([
            'ddc_id' => $this->ddc->id,
            'pincode_id' => $this->pincode->id,
        ]);
    }

    public function test_incentive_hold_creation_is_item_wise_and_does_not_release_on_stock_in(): void
    {
        // 1. Create holds on payment
        $holds = IncentiveHoldService::createHoldOnPayment($this->po);

        $this->assertCount(2, $holds); // 2 items x 1 type (SSI or FSI)

        foreach ($holds as $hold) {
            $this->assertEquals(IncentiveHold::STATUS_HOLD, $hold->status);
            $this->assertNull($hold->ama_code); // The Hold table must NOT store ama_code
            $this->assertEquals(0, $hold->released_quantity);
            $this->assertEquals($hold->quantity, $hold->remaining_quantity);
            $this->assertEquals(0.00, (float) $hold->released_amount);
            $this->assertEquals($hold->amount, $hold->balance_amount);
            $this->assertNull($hold->inventory_id);
        }

        // Simulate Stock-In for Item 1 (Product A)
        $itemA = $this->po->items()->where('product_id', $this->productA->id)->first();
        $inventoryA = DdcInventory::create([
            'ddc_id' => $this->ddc->id,
            'product_id' => $this->productA->id,
            'product_name' => $this->productA->product_name,
            'product_code' => $this->productA->product_code,
            'barcode' => $this->productA->barcode,
            'quantity' => 100,
            'mrp' => 100.00,
            'purchase_price' => 80.00,
            'purchase_order_id' => $this->po->id,
            'purchase_order_item_id' => $itemA->id,
        ]);

        IncentiveHoldService::mapInventoryToHold($this->po->id, $itemA->id, $inventoryA->id);

        // Verify status remains HOLD but inventory_id is mapped
        $mappedHolds = IncentiveHold::where('purchase_order_id', $this->po->id)
            ->where('purchase_order_item_id', $itemA->id)
            ->get();

        $this->assertCount(1, $mappedHolds);
        foreach ($mappedHolds as $hold) {
            $this->assertEquals(IncentiveHold::STATUS_HOLD, $hold->status);
            $this->assertEquals($inventoryA->id, $hold->inventory_id);
            $this->assertEquals(0, $hold->released_quantity);
            $this->assertEquals($hold->quantity, $hold->remaining_quantity);
        }
    }

    public function test_proportional_hold_release_and_multiple_partial_deliveries(): void
    {
        // 1. Create holds
        IncentiveHoldService::createHoldOnPayment($this->po);

        $itemA = $this->po->items()->where('product_id', $this->productA->id)->first();
        $inventoryA = DdcInventory::create([
            'ddc_id' => $this->ddc->id,
            'product_id' => $this->productA->id,
            'product_name' => $this->productA->product_name,
            'product_code' => $this->productA->product_code,
            'barcode' => $this->productA->barcode,
            'quantity' => 100,
            'mrp' => 100.00,
            'purchase_price' => 80.00,
            'purchase_order_id' => $this->po->id,
            'purchase_order_item_id' => $itemA->id,
        ]);

        IncentiveHoldService::mapInventoryToHold($this->po->id, $itemA->id, $inventoryA->id);

        // We have one hold for Product A with inventory_id = $inventoryA->id
        $holds = IncentiveHold::where('inventory_id', $inventoryA->id)->get();
        $this->assertCount(1, $holds);

        // Let's set specific test hold amounts
        foreach ($holds as $hold) {
            $hold->update([
                'quantity' => 100,
                'remaining_quantity' => 100,
                'amount' => 1000.00,
                'balance_amount' => 1000.00,
            ]);
        }

        // Create ADC User
        $adc = User::create([
            'name' => 'ADC User',
            'email' => 'adc@test.com',
            'password' => bcrypt('password'),
            'role' => 'adc',
            'status' => '1',
            'state_id' => $inventoryA->ddc->profile->state_id,
            'district_id' => $inventoryA->ddc->profile->district_id,
            'pincode' => '400001',
            'advisor_code' => 'AMA-A',
        ]);

        // Create ADC Order 1
        $adcOrder = AdcOrder::create([
            'order_number' => 'ADC-1001',
            'adc_id' => $adc->id,
            'total_amount' => 1600.00,
            'status' => 'Pending',
        ]);

        $orderItem = AdcOrderItem::create([
            'adc_order_id' => $adcOrder->id,
            'seller_id' => $this->seller->id,
            'product_id' => $this->productA->id,
            'quantity' => 20,
            'price' => 80.00,
            'total' => 1600.00,
            'status' => 'Pending',
        ]);

        // Act: Deliver 20 units
        $released = IncentiveHoldService::releaseHoldOnAdcDelivery($orderItem, 20);

        // Assert: Proportional release = (20 / 100) * 1000 = 200 per hold. Since there is 1 hold, total released = 200.
        $this->assertEquals(200.00, $released);

        // Refresh and check holds
        $updatedHolds = IncentiveHold::where('inventory_id', $inventoryA->id)->get();
        foreach ($updatedHolds as $hold) {
            $this->assertEquals(IncentiveHold::STATUS_PARTIAL_RELEASED, $hold->status);
            $this->assertEquals(20, $hold->released_quantity);
            $this->assertEquals(80, $hold->remaining_quantity);
            $this->assertEquals(200.00, (float) $hold->released_amount);
            $this->assertEquals(800.00, (float) $hold->balance_amount);
        }

        // Verify the IncentiveRelease record was created
        $this->assertDatabaseHas('incentive_releases', [
            'hold_id' => $holds->first()->id,
            'ama_code' => 'AMA-A',
            'adc_order_id' => $adcOrder->id,
            'adc_order_item_id' => $orderItem->id,
            'released_quantity' => 20,
            'released_amount' => 200.00,
            'status' => 'Released',
        ]);

        // Verify inventory Qty is decremented: 100 - 20 = 80
        $inventoryA->refresh();
        $this->assertEquals(80, $inventoryA->quantity);

        // Create ADC Order 2 for remaining quantity (e.g., 80)
        $adcOrder2 = AdcOrder::create([
            'order_number' => 'ADC-1002',
            'adc_id' => $adc->id,
            'total_amount' => 6400.00,
            'status' => 'Pending',
        ]);

        $orderItem2 = AdcOrderItem::create([
            'adc_order_id' => $adcOrder2->id,
            'seller_id' => $this->seller->id,
            'product_id' => $this->productA->id,
            'quantity' => 80,
            'price' => 80.00,
            'total' => 6400.00,
            'status' => 'Pending',
        ]);

        // Act: Deliver 80 units
        $released2 = IncentiveHoldService::releaseHoldOnAdcDelivery($orderItem2, 80);

        // Assert: Proportional release = (80 / 100) * 1000 = 800 per hold. Total = 800.
        $this->assertEquals(800.00, $released2);

        // Refresh and check holds - they should be fully released now
        $finalHolds = IncentiveHold::where('inventory_id', $inventoryA->id)->get();
        foreach ($finalHolds as $hold) {
            $this->assertEquals(IncentiveHold::STATUS_FULLY_RELEASED, $hold->status);
            $this->assertEquals(100, $hold->released_quantity);
            $this->assertEquals(0, $hold->remaining_quantity);
            $this->assertEquals(1000.00, (float) $hold->released_amount);
            $this->assertEquals(0.00, (float) $hold->balance_amount);
        }

        // Verify the second IncentiveRelease record was created
        $this->assertDatabaseHas('incentive_releases', [
            'hold_id' => $holds->first()->id,
            'ama_code' => 'AMA-A',
            'adc_order_id' => $adcOrder2->id,
            'adc_order_item_id' => $orderItem2->id,
            'released_quantity' => 80,
            'released_amount' => 800.00,
            'status' => 'Released',
        ]);

        // Verify inventory Qty is 0
        $inventoryA->refresh();
        $this->assertEquals(0, $inventoryA->quantity);
    }

    public function test_return_reversal_logic(): void
    {
        // 1. Setup hold and inventory
        $itemA = $this->po->items()->where('product_id', $this->productA->id)->first();
        $inventoryA = DdcInventory::create([
            'ddc_id' => $this->ddc->id,
            'product_id' => $this->productA->id,
            'product_name' => $this->productA->product_name,
            'product_code' => $this->productA->product_code,
            'barcode' => $this->productA->barcode,
            'quantity' => 100,
            'mrp' => 100.00,
            'purchase_price' => 80.00,
            'purchase_order_id' => $this->po->id,
            'purchase_order_item_id' => $itemA->id,
        ]);

        $hold = IncentiveHold::create([
            'purchase_order_id' => $this->po->id,
            'purchase_order_item_id' => $itemA->id,
            'product_id' => $this->productA->id,
            'inventory_id' => $inventoryA->id,
            'quantity' => 100,
            'released_quantity' => 20,
            'remaining_quantity' => 80,
            'ama_code' => null,
            'amount' => 1000.00,
            'released_amount' => 200.00,
            'balance_amount' => 800.00,
            'percentage' => 10.00,
            'type' => 'SSI',
            'status' => IncentiveHold::STATUS_PARTIAL_RELEASED,
        ]);

        // Create ADC User
        $adc = User::create([
            'name' => 'ADC User',
            'email' => 'adc@test.com',
            'password' => bcrypt('password'),
            'role' => 'adc',
            'status' => '1',
            'state_id' => $inventoryA->ddc->profile->state_id,
            'district_id' => $inventoryA->ddc->profile->district_id,
            'pincode' => '400001',
            'advisor_code' => 'AMA-A',
        ]);

        $adcOrder = AdcOrder::create([
            'order_number' => 'ADC-1001',
            'adc_id' => $adc->id,
            'total_amount' => 1600.00,
            'status' => 'Delivered',
        ]);

        $orderItem = AdcOrderItem::create([
            'adc_order_id' => $adcOrder->id,
            'seller_id' => $this->seller->id,
            'product_id' => $this->productA->id,
            'quantity' => 20,
            'price' => 80.00,
            'total' => 1600.00,
            'status' => 'Delivered',
        ]);

        // Act: Reverse 20 units return
        $reverted = IncentiveHoldService::reverseReleaseOnAdcReturn($orderItem, 20);

        $this->assertEquals(200.00, $reverted);

        // Verify hold is back to HOLD status
        $hold->refresh();
        $this->assertEquals(IncentiveHold::STATUS_HOLD, $hold->status);
        $this->assertEquals(0, $hold->released_quantity);
        $this->assertEquals(100, $hold->remaining_quantity);
        $this->assertEquals(0.00, (float) $hold->released_amount);
        $this->assertEquals(1000.00, (float) $hold->balance_amount);

        // Verify the IncentiveRelease record was created with negative values
        $this->assertDatabaseHas('incentive_releases', [
            'hold_id' => $hold->id,
            'ama_code' => 'AMA-A',
            'adc_order_id' => $adcOrder->id,
            'adc_order_item_id' => $orderItem->id,
            'released_quantity' => -20,
            'released_amount' => -200.00,
            'status' => 'Returned',
        ]);

        // Verify inventory is restored to 120
        $inventoryA->refresh();
        $this->assertEquals(120, $inventoryA->quantity);
    }
}
