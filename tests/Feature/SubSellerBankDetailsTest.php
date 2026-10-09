<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Vendor;
use App\Models\User;
use App\Models\Category;
use App\Models\State;
use App\Models\District;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class SubSellerBankDetailsTest extends TestCase
{
    use RefreshDatabase;

    public function test_vendor_can_create_sub_seller_with_bank_details(): void
    {
        // 1. Setup Vendor
        $vendor = Vendor::create([
            'name' => 'Parent Vendor',
            'email' => 'vendor@test.com',
            'password' => bcrypt('password'),
            'status' => '1',
        ]);

        // Setup Location and Category
        $state = State::create(['name' => 'Maharashtra', 'code' => 'MH', 'status' => '1']);
        $district = District::create(['state_id' => $state->id, 'name' => 'Mumbai', 'status' => '1']);
        $category = Category::create(['name' => 'Test Category', 'slug' => 'test-category', 'status' => '1']);

        // 2. Submit Sub-seller registration request
        $response = $this->actingAs($vendor, 'vendor')
            ->post(route('vendor.sub-sellers.store'), [
                'company_name' => 'Sub-Seller Company',
                'company_type' => 'Company',
                'category_id' => $category->id,
                'authorized_rep_name' => 'Sub Rep',
                'email' => 'subseller@test.com',
                'mobile' => '9876543210',
                'state' => $state->id,
                'district' => $district->id,
                'password' => 'password123',
                'password_confirmation' => 'password123',
                'account_holder_name' => 'Sub Seller Account Holder',
                'bank_account_number' => '1234567890',
                'ifsc_code' => 'HDFC0000123',
                'bank_name' => 'HDFC Bank',
                'branch_name' => 'Mumbai Branch',
            ]);

        $response->assertRedirect(route('vendor.sub-sellers.index'));
        $response->assertSessionHasNoErrors();

        // 3. Verify user and profile records are created in DB with correct values
        $subSeller = User::where('email', 'subseller@test.com')->first();
        $this->assertNotNull($subSeller);
        $this->assertEquals('Sub-Seller Company', $subSeller->name);
        $this->assertEquals('seller', $subSeller->role);

        $profile = $subSeller->sellerProfile;
        $this->assertNotNull($profile);
        $this->assertEquals('Sub-Seller Company', $profile->company_name);
        $this->assertEquals('Sub Seller Account Holder', $profile->account_holder_name);
        $this->assertEquals('1234567890', $profile->bank_account_number);
        $this->assertEquals('HDFC0000123', $profile->ifsc_code);
        $this->assertEquals('HDFC Bank', $profile->bank_name);
        $this->assertEquals('Mumbai Branch', $profile->branch_name);
    }

    public function test_vendor_can_update_sub_seller_bank_details(): void
    {
        // 1. Setup Vendor
        $vendor = Vendor::create([
            'name' => 'Parent Vendor',
            'email' => 'vendor@test.com',
            'password' => bcrypt('password'),
            'status' => '1',
        ]);

        // Setup Location and Category
        $state = State::create(['name' => 'Maharashtra', 'code' => 'MH', 'status' => '1']);
        $district = District::create(['state_id' => $state->id, 'name' => 'Mumbai', 'status' => '1']);
        $category = Category::create(['name' => 'Test Category', 'slug' => 'test-category', 'status' => '1']);

        // Create sub seller user first
        $subSeller = User::create([
            'name' => 'Initial Sub-Seller',
            'email' => 'subseller@test.com',
            'password' => bcrypt('password'),
            'role' => 'seller',
            'status' => '1',
            'parent_id' => $vendor->id,
        ]);

        $subSeller->sellerProfile()->create([
            'company_name' => 'Initial Sub-Seller',
            'company_type' => 'Company',
            'category_id' => $category->id,
            'authorized_rep_name' => 'Initial Rep',
            'state_id' => $state->id,
            'district_id' => $district->id,
            'parent_id' => $vendor->id,
            'account_holder_name' => 'Old Holder',
            'bank_account_number' => '1111111111',
            'ifsc_code' => 'ICIC0000111',
            'bank_name' => 'ICICI Bank',
            'branch_name' => 'Old Branch',
        ]);

        // 2. Submit Sub-seller update request
        $response = $this->actingAs($vendor, 'vendor')
            ->put(route('vendor.sub-sellers.update', $subSeller->id), [
                'company_name' => 'Updated Sub-Seller Company',
                'company_type' => 'LLP',
                'category_id' => $category->id,
                'authorized_rep_name' => 'Updated Rep',
                'email' => 'subseller@test.com',
                'mobile' => '9876543210',
                'state' => $state->id,
                'district' => $district->id,
                'account_holder_name' => 'Updated Holder',
                'bank_account_number' => '9999999999',
                'ifsc_code' => 'SBI0000999',
                'bank_name' => 'State Bank of India',
                'branch_name' => 'New Branch',
            ]);

        $response->assertRedirect(route('vendor.sub-sellers.index'));
        $response->assertSessionHasNoErrors();

        // 3. Verify sub seller profile is updated
        $subSeller->refresh();
        $profile = $subSeller->sellerProfile;
        $this->assertEquals('Updated Sub-Seller Company', $profile->company_name);
        $this->assertEquals('LLP', $profile->company_type);
        $this->assertEquals('Updated Holder', $profile->account_holder_name);
        $this->assertEquals('9999999999', $profile->bank_account_number);
        $this->assertEquals('SBI0000999', $profile->ifsc_code);
        $this->assertEquals('State Bank of India', $profile->bank_name);
        $this->assertEquals('New Branch', $profile->branch_name);
    }
}
