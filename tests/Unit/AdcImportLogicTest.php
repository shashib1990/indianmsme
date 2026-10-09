<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\State;
use App\Models\District;
use App\Models\AdcProfile;
use App\Http\Controllers\Admin\BulkImportController;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;

class AdcImportLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_adc_import_saves_state_id_and_district_id_regardless_of_hierarchy()
    {
        // 1. Create State 1 (Gujarat) & District 1 (Ahmedabad)
        $state1 = State::create([
            'name' => 'Gujarat',
            'code' => 'GJ',
            'status' => '1',
        ]);
        $district1 = District::create([
            'state_id' => $state1->id,
            'name' => 'Ahmedabad',
            'status' => '1',
        ]);

        // 2. Create State 2 (Karnataka) & District 2 (Bengaluru)
        $state2 = State::create([
            'name' => 'Karnataka',
            'code' => 'KA',
            'status' => '1',
        ]);
        $district2 = District::create([
            'state_id' => $state2->id,
            'name' => 'Bengaluru',
            'status' => '1',
        ]);

        // 3. Test adcProcess with State = Gujarat, District = Bengaluru (cross-hierarchy)
        $controller = new BulkImportController();

        $records = [
            [
                'row_number' => 2,
                'adc_code' => 'ADC9001',
                'name' => 'ADC Test User',
                'email' => 'adc9001@example.com',
                'mobile' => '9123456789',
                'company_name' => 'ADC Depot Pvt Ltd',
                'trade_name' => 'ADC Depot',
                'designation' => 'Manager',
                'type' => 'ASC',
                'state' => 'Gujarat',
                'state_id' => null, // Left null to test resolution by name
                'district' => 'Bengaluru', // Mismatched state-district hierarchy
                'district_id' => null, // Left null to test resolution by name
                'pincode' => '380001',
                'address' => 'Sample Depot Address',
                'wallet_balance' => '0.00',
                'per_user_daily_charge' => '10.00',
                'kyc_status' => 'APPROVED',
                'status' => '1',
                'password' => 'Password123',
                'is_valid' => true,
                'errors' => [],
            ]
        ];

        $request = Request::create('/admin/import/adc/process', 'POST', ['records' => $records]);
        $response = $controller->adcProcess($request);

        $json = $response->getData(true);
        $this->assertTrue($json['success']);
        $this->assertEquals(1, $json['imported']);

        // 4. Verify created ADC Profile
        $adcUser = User::where('email', 'adc9001@example.com')->first();
        $this->assertNotNull($adcUser);

        $adcProfile = DB::table('adc_profiles')->where('user_id', $adcUser->id)->first();
        $this->assertNotNull($adcProfile);

        // State ID should be matched from 'Gujarat' -> $state1->id
        $this->assertEquals($state1->id, $adcProfile->state_id);

        // District ID should be matched from 'Bengaluru' -> $district2->id even though state is Gujarat
        $this->assertEquals($district2->id, $adcProfile->district_id);
    }

    public function test_adc_import_handles_na_district_and_flexible_mobile_number()
    {
        $state1 = State::create([
            'name' => 'Rajasthan',
            'code' => 'RJ',
            'status' => '1',
        ]);

        $controller = new BulkImportController();

        $records = [
            [
                'row_number' => 2,
                'adc_code' => 'ADC9002',
                'name' => 'ADC NA District User',
                'email' => 'adc9002@example.com',
                'mobile' => '', // Empty mobile allowed
                'company_name' => 'NA Depot Ltd',
                'trade_name' => 'NA Depot',
                'designation' => 'Executive',
                'type' => 'ASC',
                'state' => 'Rajasthan',
                'state_id' => null,
                'district' => 'Unknown District XYZ', // Unmatched district name
                'district_id' => null,
                'pincode' => '302001',
                'address' => 'Test Address',
                'wallet_balance' => '0.00',
                'per_user_daily_charge' => '10.00',
                'kyc_status' => 'APPROVED',
                'status' => '1',
                'password' => 'Password123',
                'is_valid' => true,
                'errors' => [],
            ]
        ];

        $request = Request::create('/admin/import/adc/process', 'POST', ['records' => $records]);
        $response = $controller->adcProcess($request);

        $json = $response->getData(true);
        $this->assertTrue($json['success']);
        $this->assertEquals(1, $json['imported']);

        $adcUser = User::where('email', 'adc9002@example.com')->first();
        $this->assertNotNull($adcUser);
        $this->assertNull($adcUser->mobile);

        $adcProfile = DB::table('adc_profiles')->where('user_id', $adcUser->id)->first();
        $this->assertNotNull($adcProfile);
        $this->assertNull($adcProfile->district_id);
        $this->assertEquals('Unknown District XYZ', $adcProfile->district);
    }
}
