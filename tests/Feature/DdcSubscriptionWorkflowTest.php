<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\Vendor;
use App\Models\SellerProfile;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class DdcSubscriptionWorkflowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    private function getOrCreateProfile(Vendor $vendor): SellerProfile
    {
        $profile = $vendor->sellerProfile ?: $vendor->profile;
        if (!$profile) {
            $profile = SellerProfile::create([
                'user_id' => $vendor->id,
                'seller_id' => SellerProfile::generateSellerId(),
                'company_name' => 'Test Company',
                'ddc_subscription_status' => 'none',
            ]);
        } else {
            $profile->update([
                'company_name' => 'Test Company',
                'ddc_subscription_status' => 'none',
            ]);
        }
        return $profile;
    }

    public function test_vendor_can_submit_ddc_subscription_request(): void
    {
        // 1. Setup Vendor
        $vendor = Vendor::create([
            'name' => 'Test Vendor',
            'email' => 'vendor@test.com',
            'password' => bcrypt('password'),
            'status' => '1',
            'force_change_password' => 0,
        ]);

        $profile = $this->getOrCreateProfile($vendor);

        // Fake payment slip upload
        $slip = UploadedFile::fake()->image('payment_slip.png');

        // Submit request
        $response = $this->actingAs($vendor, 'vendor')
            ->post(route('vendor.profile.ddc-subscription'), [
                'ddc_subscription_utr' => 'UTR123456789',
                'ddc_subscription_slip' => $slip,
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // Check DB state
        $profile->refresh();
        $this->assertEquals('pending', $profile->ddc_subscription_status);
        $this->assertEquals('UTR123456789', $profile->ddc_subscription_utr);
        $this->assertNotNull($profile->ddc_subscription_slip);
        $this->assertNotNull($profile->ddc_subscription_submitted_at);
        $this->assertNull($profile->ddc_subscription_reject_reason);
    }

    public function test_admin_can_approve_ddc_subscription(): void
    {
        // 1. Setup Admin
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => '1',
            'force_change_password' => '0',
            'is_super_admin' => '1',
        ]);

        // 2. Setup Vendor
        $vendor = Vendor::create([
            'name' => 'Test Vendor',
            'email' => 'vendor@test.com',
            'password' => bcrypt('password'),
            'status' => '1',
            'force_change_password' => 0,
        ]);

        $profile = $this->getOrCreateProfile($vendor);
        $profile->update([
            'ddc_subscription_status' => 'pending',
            'ddc_subscription_utr' => 'UTR123456789',
            'ddc_subscription_slip' => 'https://example.com/slip.png',
            'ddc_subscription_submitted_at' => now(),
        ]);

        // Approve request
        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.external.vendors.ddc-approve', $vendor->id));

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // Check DB state
        $profile->refresh();
        $this->assertEquals('approved', $profile->ddc_subscription_status);
        $this->assertTrue((bool)$profile->is_ddc_subscribed);
    }

    public function test_admin_can_reject_ddc_subscription(): void
    {
        // 1. Setup Admin
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => '1',
            'force_change_password' => '0',
            'is_super_admin' => '1',
        ]);

        // 2. Setup Vendor
        $vendor = Vendor::create([
            'name' => 'Test Vendor',
            'email' => 'vendor@test.com',
            'password' => bcrypt('password'),
            'status' => '1',
            'force_change_password' => 0,
        ]);

        $profile = $this->getOrCreateProfile($vendor);
        $profile->update([
            'ddc_subscription_status' => 'pending',
            'ddc_subscription_utr' => 'UTR123456789',
            'ddc_subscription_slip' => 'https://example.com/slip.png',
            'ddc_subscription_submitted_at' => now(),
        ]);

        // Reject request
        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.external.vendors.ddc-reject', $vendor->id), [
                'ddc_subscription_reject_reason' => 'Invalid transaction reference UTR.',
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // Check DB state
        $profile->refresh();
        $this->assertEquals('rejected', $profile->ddc_subscription_status);
        $this->assertFalse((bool)$profile->is_ddc_subscribed);
        $this->assertEquals('Invalid transaction reference UTR.', $profile->ddc_subscription_reject_reason);
    }

    public function test_admin_can_cancel_ddc_subscription(): void
    {
        // 1. Setup Admin
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => '1',
            'force_change_password' => '0',
            'is_super_admin' => '1',
        ]);

        // 2. Setup Vendor
        $vendor = Vendor::create([
            'name' => 'Test Vendor',
            'email' => 'vendor@test.com',
            'password' => bcrypt('password'),
            'status' => '1',
            'force_change_password' => 0,
        ]);

        $profile = $this->getOrCreateProfile($vendor);
        $profile->update([
            'ddc_subscription_status' => 'approved',
            'is_ddc_subscribed' => true,
            'ddc_subscription_utr' => 'UTR123456789',
            'ddc_subscription_slip' => 'https://example.com/slip.png',
            'ddc_subscription_submitted_at' => now(),
        ]);

        // Cancel request
        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.external.vendors.ddc-cancel', $vendor->id));

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // Check DB state
        $profile->refresh();
        $this->assertEquals('none', $profile->ddc_subscription_status);
        $this->assertFalse((bool)$profile->is_ddc_subscribed);
    }

    public function test_admin_can_view_ddc_requests_page(): void
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => '1',
            'force_change_password' => '0',
            'is_super_admin' => '1',
        ]);

        $vendor = Vendor::create([
            'name' => 'Pending Vendor',
            'email' => 'vendor@test.com',
            'password' => bcrypt('password'),
            'status' => '1',
            'force_change_password' => 0,
        ]);

        $profile = $this->getOrCreateProfile($vendor);
        $profile->update([
            'ddc_subscription_status' => 'pending',
            'ddc_subscription_utr' => 'UTR123',
            'ddc_subscription_slip' => 'https://example.com/slip.png',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.external.vendors.ddc-requests'));

        $response->assertStatus(200);
        $response->assertSee('Pending Vendor');
        $response->assertSee('UTR123');
    }

    public function test_admin_can_view_ddc_approved_page(): void
    {
        $admin = Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => '1',
            'force_change_password' => '0',
            'is_super_admin' => '1',
        ]);

        $vendor = Vendor::create([
            'name' => 'Approved Vendor',
            'email' => 'vendor@test.com',
            'password' => bcrypt('password'),
            'status' => '1',
            'force_change_password' => 0,
        ]);

        $profile = $this->getOrCreateProfile($vendor);
        $profile->update([
            'ddc_subscription_status' => 'approved',
            'is_ddc_subscribed' => true,
            'ddc_subscription_utr' => 'UTR456',
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get(route('admin.external.vendors.ddc-approved'));

        $response->assertStatus(200);
        $response->assertSee('Approved Vendor');
        $response->assertSee('UTR456');
    }

    public function test_unsubscribed_seller_cannot_access_smart_sales_incentive(): void
    {
        $vendor = Vendor::create([
            'name' => 'Unsubscribed Vendor',
            'email' => 'unsubscribed@test.com',
            'password' => bcrypt('password'),
            'status' => '1',
            'force_change_password' => 0,
        ]);

        $profile = $this->getOrCreateProfile($vendor);
        $profile->update([
            'ddc_subscription_status' => 'none',
            'is_ddc_subscribed' => false,
        ]);

        $response = $this->actingAs($vendor, 'vendor')
            ->get(route('vendor.smart-sales-incentive'));

        $response->assertRedirect(route('vendor.dashboard'));
        $response->assertSessionHas('error');
    }

    public function test_subscribed_seller_can_manage_smart_sales_incentive(): void
    {
        $vendor = Vendor::create([
            'name' => 'Subscribed Vendor',
            'email' => 'subscribed@test.com',
            'password' => bcrypt('password'),
            'status' => '1',
            'force_change_password' => 0,
        ]);

        $profile = $this->getOrCreateProfile($vendor);
        $profile->update([
            'ddc_subscription_status' => 'approved',
            'is_ddc_subscribed' => true,
            'ddc_ama_commission_percentage' => 0.00,
        ]);

        // Can view the page
        $response = $this->actingAs($vendor, 'vendor')
            ->get(route('vendor.smart-sales-incentive'));

        $response->assertStatus(200);
        $response->assertSee('Smart Sales Incentive');

        // Can save the percentage
        $response = $this->actingAs($vendor, 'vendor')
            ->post(route('vendor.smart-sales-incentive.post'), [
                'ddc_ama_commission_percentage' => 12.50,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $profile->refresh();
        $this->assertEquals(12.50, (float)$profile->ddc_ama_commission_percentage);
    }
}
