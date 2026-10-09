<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\State;
use App\Models\District;
use App\Models\SellerProfile;
use App\Http\Controllers\Admin\BulkImportController;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SellerImportLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_seller_import_saves_state_id_district_id_and_parent_id_by_imvcode()
    {
        // 1. Create State 1 (West Bengal) and District 1 (Kolkata under West Bengal)
        $state1 = State::create([
            'name' => 'West Bengal',
            'code' => 'WB',
            'status' => '1',
        ]);
        $district1 = District::create([
            'state_id' => $state1->id,
            'name' => 'Kolkata',
            'status' => '1',
        ]);

        // 2. Create State 2 (Maharashtra) and District 2 (Mumbai under Maharashtra)
        $state2 = State::create([
            'name' => 'Maharashtra',
            'code' => 'MH',
            'status' => '1',
        ]);
        $district2 = District::create([
            'state_id' => $state2->id,
            'name' => 'Mumbai',
            'status' => '1',
        ]);

        // 3. Create Parent Seller with IMV code IMV1001
        $parentUser = User::create([
            'name' => 'Parent Vendor',
            'email' => 'parent@example.com',
            'mobile' => '9999999999',
            'role' => 'seller',
            'status' => '1',
            'password' => bcrypt('password'),
        ]);

        $parentProfile = SellerProfile::firstOrCreate(['user_id' => $parentUser->id]);
        $parentProfile->update([
            'seller_id' => 'IMV1001',
            'company_name' => 'Parent Company',
            'state_id' => $state1->id,
            'district_id' => $district1->id,
            'state' => 'West Bengal',
            'district' => 'Kolkata',
        ]);

        // 4. Test sellerProcess with State = West Bengal, District = Mumbai (which is under Maharashtra in DB), Parent Seller ID = IMV1001
        $controller = new BulkImportController();

        $records = [
            [
                'row_number' => 2,
                'seller_id' => 'IMV1002',
                'name' => 'New Seller',
                'email' => 'newseller@example.com',
                'mobile' => '9876543210',
                'company_name' => 'New Company Pvt Ltd',
                'company_type' => 'Private Limited Company',
                'seller_type' => 'Manufacturer',
                'parent_seller_id' => 'IMV1001',
                'state' => 'West Bengal',
                'state_id' => null, // Left null to test resolution by name
                'district' => 'Mumbai', // Mismatched state-district hierarchy intentional
                'district_id' => null, // Left null to test resolution by name
                'pincode' => '400001',
                'address' => 'Test Address',
                'wallet_balance' => '0.00',
                'kyc_status' => 'APPROVED',
                'allow_own_products' => 'Yes',
                'status' => '1',
                'password' => 'Password123',
                'is_valid' => true,
                'errors' => [],
            ]
        ];

        $request = Request::create('/admin/import/seller/process', 'POST', ['records' => $records]);
        $response = $controller->sellerProcess($request);

        $json = $response->getData(true);
        $this->assertTrue($json['success']);
        $this->assertEquals(1, $json['imported']);

        // 5. Verify the created seller profile
        $newSellerUser = User::where('email', 'newseller@example.com')->first();
        $this->assertNotNull($newSellerUser);

        $newProfile = SellerProfile::where('user_id', $newSellerUser->id)->first();
        $this->assertNotNull($newProfile);

        // State ID should be matched from 'West Bengal' -> $state1->id
        $this->assertEquals($state1->id, $newProfile->state_id);

        // District ID should be matched from 'Mumbai' -> $district2->id even though state is West Bengal
        $this->assertEquals($district2->id, $newProfile->district_id);

        // Parent ID should be matched from 'IMV1001' -> $parentUser->id
        $this->assertEquals($parentUser->id, $newProfile->parent_id);
    }
}
