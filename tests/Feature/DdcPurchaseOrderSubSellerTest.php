<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\DDC;
use App\Models\User;
use App\Models\Vendor;
use App\Models\DdcProfile;
use App\Models\SellerProfile;
use App\Models\PickupStore;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use App\Models\SubSellerProduct;
use App\Models\State;
use App\Models\District;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DdcPurchaseOrderSubSellerTest extends TestCase
{
    use RefreshDatabase;

    private DDC $ddc;
    private User $parentSeller;
    private Product $product;
    private State $state;
    private District $district;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup State & District
        $this->state = State::create([
            'name' => 'Uttar Pradesh',
            'code' => 'UP',
            'status' => '1'
        ]);

        $this->district = District::create([
            'state_id' => $this->state->id,
            'name' => 'Noida',
            'status' => '1'
        ]);

        // 1. Setup DDC user (role = 'ddc', type = 'physical')
        $this->ddc = DDC::create([
            'name' => 'DDC Test User',
            'email' => 'ddc@test.com',
            'password' => bcrypt('password'),
            'role' => 'ddc',
            'type' => 'physical',
            'status' => '1',
        ]);

        // Update the automatically created DdcProfile
        $this->ddc->profile->update([
            'type' => 'physical',
            'ddc_code' => 'DDB001',
            'wallet_balance' => 50000.00,
            'kyc_status' => 'APPROVED',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'pincode' => '201301',
        ]);

        // 2. Setup Parent Seller
        $this->parentSeller = User::create([
            'name' => 'Parent Seller User',
            'email' => 'parent@test.com',
            'password' => bcrypt('password'),
            'role' => 'seller',
            'status' => '1',
        ]);

        // Update the automatically created SellerProfile
        $this->parentSeller->sellerProfile->update([
            'seller_id' => 'IMV1001',
            'parent_id' => null,
            'kyc_status' => 'APPROVED',
            'allow_own_products' => 'Yes',
        ]);

        // 3. Create active pickup store for Parent Seller (pan_india)
        PickupStore::create([
            'owner_type' => 'Seller',
            'owner_id' => $this->parentSeller->id,
            'pickup_store_code' => 'PSC000001',
            'pickup_store_name' => 'Parent Seller Store',
            'contact_person' => 'Store Contact',
            'mobile' => '9876543210',
            'email' => 'store1@test.com',
            'address' => 'Sector 62 Noida',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'pincode' => '201301',
            'status' => 'Active',
            'delivery_coverage_type' => 'pan_india',
            'can_receive_payment_and_dispatch' => false
        ]);

        // 4. Setup Brand & Category
        $brand = Brand::create(['name' => 'Brand Test', 'slug' => 'brand-test']);
        $category = Category::create(['name' => 'Category Test', 'slug' => 'category-test']);

        // 5. Setup Product for Parent Seller
        $this->product = Product::create([
            'product_name' => 'Test Seller Product',
            'barcode' => '111111111111',
            'seller_id' => $this->parentSeller->id,
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'mrp' => 200.00,
            'seller_price_incl_gst' => 150.00,
            'gst_rate' => 18.00,
            'status' => 'Approved',
            'is_new' => false,
            'created_by' => $this->parentSeller->id,
            'description' => 'Test Product Description',
            'product_specification' => 'Test Spec',
            'storage_instructions' => 'Keep cool',
            'featured_image' => 'http://example.com/image.jpg',
            'gallery' => [],
            'hsn_code' => '123456',
            'unit_of_measure' => 'pcs',
            'net_quantity' => 1,
            'case_pack_quantity' => 5,
            'shelf_life' => '12 months',
            'country_of_origin' => 'India',
            'length' => 10.0,
            'width' => 10.0,
            'height' => 10.0,
            'dimension_unit' => 'cm',
            'weight' => 1.0,
            'weight_unit' => 'kg'
        ]);
    }

    public function test_ddc_purchase_order_creation_displays_products_when_parent_has_no_sub_sellers(): void
    {
        // Access with parent_seller_id; parent has no sub-sellers
        $response = $this->actingAs($this->ddc, 'ddc')
            ->get(route('ddc.purchase-orders.create', [
                'parent_seller_id' => $this->parentSeller->id,
            ]));

        $response->assertStatus(200);
        $response->assertSee($this->product->product_name);
        $response->assertDontSee('Please Select a Sub-Seller');
    }

    public function test_ddc_purchase_order_creation_hides_products_and_requires_sub_seller_selection_when_parent_has_sub_sellers(): void
    {
        // 1. Create a sub-seller for the parent seller
        $subSeller = User::create([
            'name' => 'Sub Seller User',
            'email' => 'subseller@test.com',
            'password' => bcrypt('password'),
            'role' => 'seller',
            'status' => '1',
        ]);

        // Update the automatically created SellerProfile
        $subSeller->sellerProfile->update([
            'seller_id' => 'IMV1002',
            'parent_id' => $this->parentSeller->id,
            'kyc_status' => 'APPROVED',
            'allow_own_products' => 'Yes',
        ]);

        // Create active pickup store for sub-seller
        PickupStore::create([
            'owner_type' => 'Sub Seller',
            'owner_id' => $subSeller->id,
            'pickup_store_code' => 'PSC000002',
            'pickup_store_name' => 'Sub Seller Store',
            'contact_person' => 'Sub Contact',
            'mobile' => '9876543211',
            'email' => 'store2@test.com',
            'address' => 'Sector 62 Noida',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'pincode' => '201301',
            'status' => 'Active',
            'delivery_coverage_type' => 'pan_india',
            'can_receive_payment_and_dispatch' => false
        ]);

        // 2. Access with parent_seller_id only
        $response = $this->actingAs($this->ddc, 'ddc')
            ->get(route('ddc.purchase-orders.create', [
                'parent_seller_id' => $this->parentSeller->id,
            ]));

        $response->assertStatus(200);
        $response->assertDontSee($this->product->product_name);
        $response->assertSee('Please Select a Sub-Seller');

        // 3. Access with parent_seller_id and seller_id (sub-seller selected)
        $responseSub = $this->actingAs($this->ddc, 'ddc')
            ->get(route('ddc.purchase-orders.create', [
                'parent_seller_id' => $this->parentSeller->id,
                'seller_id' => $subSeller->id,
            ]));

        $responseSub->assertStatus(200);
        $responseSub->assertSee($this->product->product_name);
        $responseSub->assertDontSee('Please Select a Sub-Seller');
    }
}
