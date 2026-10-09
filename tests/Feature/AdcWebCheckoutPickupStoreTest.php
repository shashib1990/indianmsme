<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\SellerProfile;
use App\Models\PickupStore;
use App\Models\State;
use App\Models\District;
use App\Models\Pincode;
use App\Services\OrderRoutingService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AdcWebCheckoutPickupStoreTest extends TestCase
{
    use RefreshDatabase;

    public function test_pickup_store_is_resolved_for_seller_po()
    {
        $state = State::create(['name' => 'Uttar Pradesh', 'code' => 'UP', 'status' => '1']);
        $district = District::create(['state_id' => $state->id, 'name' => 'Noida', 'status' => '1']);
        Pincode::create(['state_id' => $state->id, 'district_id' => $district->id, 'pincode' => '201301', 'status' => '1']);

        $seller = User::create([
            'name' => 'Seller Test',
            'email' => 'seller@test.com',
            'password' => bcrypt('password'),
            'role' => 'seller',
            'status' => '1',
            'kyc_status' => 'APPROVED',
        ]);
        SellerProfile::create(['user_id' => $seller->id, 'status' => 'APPROVED']);

        $store = PickupStore::create([
            'owner_type' => 'Seller',
            'owner_id' => $seller->id,
            'pickup_store_code' => 'PSC000001',
            'pickup_store_name' => 'Noida Pickup Store',
            'contact_person' => 'Test Person',
            'mobile' => '9999999999',
            'address' => 'Test Address',
            'state_id' => $state->id,
            'district_id' => $district->id,
            'pincode' => '201301',
            'status' => 'Active',
            'delivery_coverage_type' => 'pan_india',
        ]);

        $resolvedStore = OrderRoutingService::resolvePickupStoreForSeller($seller->id, '201301');

        $this->assertNotNull($resolvedStore);
        $this->assertEquals($store->id, $resolvedStore->id);
    }
}
