<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\State;
use App\Models\District;
use App\Models\SellerProfile;
use App\Models\PickupStore;
use App\Http\Controllers\Admin\BulkImportController;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PickupStoreImportLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_pickup_store_import_allows_same_psc_code_across_different_sellers()
    {
        // 1. Create State & District
        $state = State::create(['name' => 'Delhi', 'code' => 'DL', 'status' => '1']);
        $district = District::create(['state_id' => $state->id, 'name' => 'New Delhi', 'status' => '1']);

        // 2. Create Seller 1 & Seller 2
        $seller1 = User::create([
            'name' => 'Seller One',
            'email' => 'seller1@example.com',
            'mobile' => '9800000001',
            'role' => 'seller',
            'status' => '1',
            'password' => bcrypt('password'),
        ]);
        SellerProfile::create(['user_id' => $seller1->id, 'seller_id' => 'SEL1001']);

        $seller2 = User::create([
            'name' => 'Seller Two',
            'email' => 'seller2@example.com',
            'mobile' => '9800000002',
            'role' => 'seller',
            'status' => '1',
            'password' => bcrypt('password'),
        ]);
        SellerProfile::create(['user_id' => $seller2->id, 'seller_id' => 'SEL1002']);

        // 3. Seller 1 already has a store with PSC1001 in database
        PickupStore::create([
            'owner_id' => $seller1->id,
            'owner_type' => 'Seller',
            'pickup_store_code' => 'PSC1001',
            'pickup_store_name' => 'Seller 1 Main Store',
            'contact_person' => 'John Doe',
            'mobile' => '9800000001',
            'email' => 'seller1@example.com',
            'address' => 'Store Address 1',
            'state_id' => $state->id,
            'district_id' => $district->id,
            'pincode' => '110001',
            'can_receive_payment_and_dispatch' => 1,
            'status' => 'Active',
            'password' => bcrypt('password'),
        ]);

        $controller = new BulkImportController();

        // 4. Test preview for Seller 2 using PSC1001 (should be valid, not duplicate!)
        $records = [
            [
                'row_number' => 2,
                'seller_code' => 'SEL1002',
                'seller_user_id' => $seller2->id,
                'owner_type' => 'Seller',
                'pickup_store_code' => 'PSC1001', // Same code as Seller 1
                'pickup_store_name' => 'Seller 2 Main Store',
                'contact_person' => 'Jane Smith',
                'mobile' => '9800000002',
                'email' => 'seller2@example.com',
                'gst_number' => '',
                'address' => 'Store Address 2',
                'landmark' => '',
                'state_id' => $state->id,
                'district_id' => $district->id,
                'pincode' => '110001',
                'can_receive_payment_and_dispatch' => 'Yes',
                'account_holder_name' => '',
                'bank_account_number' => '',
                'ifsc_code' => '',
                'bank_name' => '',
                'branch_name' => '',
                'status' => 'Active',
                'password' => 'Password123',
                'is_valid' => true,
                'errors' => [],
            ]
        ];

        // 5. Test pickupStoreProcess for Seller 2
        $request = Request::create('/admin/import/pickup-store/process', 'POST', ['records' => $records]);
        $response = $controller->pickupStoreProcess($request);

        $json = $response->getData(true);
        $this->assertTrue($json['success']);
        $this->assertEquals(1, $json['imported']);

        // 6. Verify Seller 2 store saved with PSC1001
        $store2 = PickupStore::where('owner_id', $seller2->id)->where('pickup_store_code', 'PSC1001')->first();
        $this->assertNotNull($store2);
        $this->assertEquals('Seller 2 Main Store', $store2->pickup_store_name);
    }

    public function test_pickup_store_import_allows_missing_or_unmatched_district()
    {
        $seller = User::create([
            'name' => 'Seller Three',
            'email' => 'seller3@example.com',
            'mobile' => '9800000003',
            'role' => 'seller',
            'status' => '1',
            'password' => bcrypt('password'),
        ]);
        SellerProfile::create(['user_id' => $seller->id, 'seller_id' => 'SEL1003']);

        $controller = new BulkImportController();

        $records = [
            [
                'row_number' => 2,
                'seller_code' => 'SEL1003',
                'seller_user_id' => $seller->id,
                'owner_type' => 'Seller',
                'pickup_store_code' => 'PSC2001',
                'pickup_store_name' => 'Seller 3 Store Empty District',
                'contact_person' => 'Bob Builder',
                'mobile' => '9800000003',
                'email' => 'seller3@example.com',
                'gst_number' => '',
                'address' => 'Store Address 3',
                'landmark' => '',
                'state_id' => null,
                'district_id' => null, // Empty district
                'pincode' => '110001',
                'can_receive_payment_and_dispatch' => 'Yes',
                'account_holder_name' => '',
                'bank_account_number' => '',
                'ifsc_code' => '',
                'bank_name' => '',
                'branch_name' => '',
                'status' => 'Active',
                'password' => 'Password123',
                'is_valid' => true,
                'errors' => [],
            ]
        ];

        $request = Request::create('/admin/import/pickup-store/process', 'POST', ['records' => $records]);
        $response = $controller->pickupStoreProcess($request);

        $json = $response->getData(true);
        $this->assertTrue($json['success']);
        $this->assertEquals(1, $json['imported']);

        $store3 = PickupStore::where('owner_id', $seller->id)->where('pickup_store_code', 'PSC2001')->first();
        $this->assertNotNull($store3);
        $this->assertNull($store3->district_id);
    }
}
