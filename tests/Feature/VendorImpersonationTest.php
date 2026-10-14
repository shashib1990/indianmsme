<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\SellerProfile;
use Tests\TestCase;

class VendorImpersonationTest extends TestCase
{
    public function test_admin_can_impersonate_vendor_and_access_dashboard()
    {
        $admin = User::firstOrCreate(['email' => 'admin_vtest@test.com'], [
            'name' => 'Admin Test',
            'role' => 'admin',
            'password' => bcrypt('password'),
            'status' => '1',
        ]);

        $vendor = User::whereIn('role', ['vendor', 'seller'])->first();
        if (!$vendor) {
            $vendor = User::firstOrCreate(['email' => 'vendor_test@test.com'], [
                'name' => 'Test Vendor',
                'role' => 'seller',
                'password' => bcrypt('password'),
                'status' => '1',
            ]);
            SellerProfile::firstOrCreate(['user_id' => $vendor->id], [
                'business_name' => 'Test Business',
                'pincode' => '110001',
                'wallet_balance' => 0,
            ]);
        }

        // 1. Admin logs into admin panel and posts impersonate request
        $response = $this->actingAs($admin, 'admin')
            ->post("/admin/impersonate/vendor/{$vendor->id}");

        $response->assertStatus(302);
        $targetUrl = $response->headers->get('Location');
        $this->assertStringContainsString('/vendor/impersonate/auth?token=', $targetUrl);

        // 2. Follow SSO bridge redirect
        $authResponse = $this->get($targetUrl);
        $authResponse->assertStatus(302);
        $authResponse->assertRedirect('/vendor');

        // 3. Follow redirect to vendor dashboard
        $dashboardResponse = $this->get('/vendor');
        $dashboardResponse->assertStatus(200);
        $dashboardResponse->assertSee($vendor->name);
        $dashboardResponse->assertSee('Return to Admin');
        $dashboardResponse->assertSee('You are impersonating');

        // 4. Verify auth guard
        $this->assertTrue(auth('vendor')->check());
        $this->assertEquals($vendor->id, auth('vendor')->id());

        // 5. Verify stop impersonation
        $stopResponse = $this->post('/impersonate/stop');
        $stopResponse->assertStatus(302);
    }
}
