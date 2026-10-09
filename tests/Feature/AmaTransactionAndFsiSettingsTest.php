<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\SystemSetting;
use App\Models\IncentiveHold;
use App\Models\IncentiveRelease;
use App\Models\PurchaseOrder;
use App\Models\Product;
use App\Models\User;
use App\Models\AdcOrder;
use App\Models\AdcOrderItem;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AmaTransactionAndFsiSettingsTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an Admin user with super_admin privileges
        $this->admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => '1',
            'force_change_password' => '0',
            'is_super_admin' => '1',
        ]);
    }

    public function test_guest_cannot_access_pricing_settings_or_ama_transactions(): void
    {
        $response = $this->get(route('admin.settings.pricing.index'));
        $response->assertRedirect(route('admin.login'));

        $response = $this->get(route('admin.ama-transactions.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_view_pricing_margins_page_with_fsi(): void
    {
        SystemSetting::setVal('fsi_percentage', 0.50);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.settings.pricing.index'));

        $response->assertStatus(200);
        $response->assertSee('FSI Percentage');
        $response->assertSee('0.5');
    }

    public function test_admin_can_update_fsi_percentage(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.settings.pricing.update'), [
                'admin_margin_percentage' => 8.0,
                'ddc_margin_percentage' => 5.0,
                'mbp_percentage' => 2.0,
                'fsi_percentage' => 1.25,
            ]);

        $response->assertRedirect(route('admin.settings.pricing.index'));
        $response->assertSessionHas('success');

        $this->assertEquals(1.25, (float) SystemSetting::getVal('fsi_percentage'));
    }

    public function test_admin_can_view_ama_transactions_page(): void
    {
        $ddc = \App\Models\DDC::create([
            'name' => 'DDC Coordinator',
            'email' => 'ddc@test.com',
            'password' => bcrypt('password'),
            'status' => '1',
        ]);

        $seller = \App\Models\Vendor::create([
            'name' => 'Vendor User',
            'email' => 'vendor@test.com',
            'password' => bcrypt('password'),
            'status' => '1',
        ]);

        $brand = \App\Models\Brand::create(['name' => 'Brand Test', 'slug' => 'brand-test']);
        $category = \App\Models\Category::create(['name' => 'Category Test', 'slug' => 'category-test']);

        $product = Product::create([
            'seller_id' => $seller->id,
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
            'created_by' => $seller->id,
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

        // Seed an IncentiveHold
        $po = PurchaseOrder::create([
            'po_number' => 'PO-1234567890',
            'ddc_id' => $ddc->id,
            'seller_id' => $seller->id,
            'total_amount' => 1000.00,
            'status' => 'Pending',
        ]);

        $hold = IncentiveHold::create([
            'purchase_order_id' => $po->id,
            'product_id' => $product->id,
            'quantity' => 10,
            'released_quantity' => 2,
            'remaining_quantity' => 8,
            'amount' => 50.00,
            'released_amount' => 10.00,
            'balance_amount' => 40.00,
            'percentage' => 0.50,
            'type' => 'FSI',
            'status' => 'Hold',
            'notes' => 'Test hold transaction',
        ]);

        $adc = User::create([
            'name' => 'ADC User',
            'email' => 'adc@test.com',
            'password' => bcrypt('password'),
            'role' => 'adc',
            'status' => '1',
            'advisor_code' => 'AMA-9999',
        ]);

        $adcOrder = AdcOrder::create([
            'order_number' => 'ADC-1001',
            'adc_id' => $adc->id,
            'total_amount' => 160.00,
            'status' => 'Delivered',
        ]);

        $orderItem = AdcOrderItem::create([
            'adc_order_id' => $adcOrder->id,
            'seller_id' => $seller->id,
            'product_id' => $product->id,
            'quantity' => 2,
            'price' => 80.00,
            'total' => 160.00,
            'status' => 'Delivered',
        ]);

        IncentiveRelease::create([
            'hold_id' => $hold->id,
            'ama_code' => 'AMA-9999',
            'adc_order_id' => $adcOrder->id,
            'adc_order_item_id' => $orderItem->id,
            'product_id' => $product->id,
            'released_quantity' => 2,
            'released_amount' => 10.00,
            'release_date' => now(),
            'status' => 'Released',
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.ama-transactions.index'));

        $response->assertStatus(200);
        $response->assertSee('AMA Incentive Transactions');
        $response->assertSee('AMA-9999');
        $response->assertSee('Test hold transaction');
        $response->assertSee('Hold');
        $response->assertSee('PO-1234567890');
    }
}
