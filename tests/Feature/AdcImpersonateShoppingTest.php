<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\AdcProfile;
use App\Models\Pincode;
use App\Models\State;
use App\Models\District;

class AdcImpersonateShoppingTest extends TestCase
{
    public function test_impersonate_and_visit_shopping_via_bridge()
    {
        $admin = User::firstOrCreate(['email' => 'admin_test@test.com'], [
            'name' => 'Admin Test',
            'role' => 'admin',
            'password' => bcrypt('password'),
            'status' => '1',
        ]);
        $adc = User::where('role', 'adc')->whereHas('adcProfile', function ($q) {
            $q->whereNotNull('pincode');
        })->first();
        if (!$adc) {
            $adc = User::firstOrCreate(['email' => 'adc_test@test.com'], [
                'name' => 'ADC Test User',
                'role' => 'adc',
                'password' => bcrypt('password'),
                'status' => '1',
            ]);
            AdcProfile::updateOrCreate(['user_id' => $adc->id], [
                'adc_code' => 'ADC999',
                'type' => 'ASC',
                'status' => 'APPROVED',
                'pincode' => '110001',
            ]);
        }
        $expectedPincode = $adc->adcProfile->pincode;

        // 1. Act as admin and impersonate ADC
        $response = $this->actingAs($admin, 'admin')
            ->post("/admin/impersonate/adc/{$adc->id}");

        $targetUrl = $response->headers->get('Location');
        $this->assertNotEmpty($targetUrl);

        // 2. Follow redirect to auth (simulates opening new tab)
        $response2 = $this->get($targetUrl);
        $dashboardUrl = $response2->headers->get('Location');
        $this->assertNotEmpty($dashboardUrl);

        // 3. Follow redirect to ADC dashboard
        $response3 = $this->get($dashboardUrl);
        $response3->assertStatus(200);
        $this->assertTrue(auth('adc')->check());
        $this->assertEquals($adc->id, auth('adc')->id());

        // 4. Click the "Shopping" button (hits route('adc.shopping'))
        $shoppingBridgeUrl = route('adc.shopping');
        $responseBridge = $this->get($shoppingBridgeUrl);
        $responseBridge->assertStatus(302);

        $authHandoffUrl = $responseBridge->headers->get('Location');
        $this->assertStringContainsString('shopping/auth?token=', $authHandoffUrl);

        // 5. Follow the SSO handoff redirect to the main website
        $responseShopAuth = $this->get($authHandoffUrl);
        $responseShopAuth->assertStatus(302);
        $responseShopAuth->assertSessionHas('user_pincode', $expectedPincode);
        $destination = $responseShopAuth->headers->get('Location');
        $this->assertStringContainsString(route('shop', [], false), $destination);

        // 6. Arrive on shop page and verify ADC session is active
        $responseShop = $this->get($destination);
        $responseShop->assertStatus(200);

        $this->assertTrue(auth('adc')->check());
        $this->assertEquals($adc->id, auth('adc')->id());
        $this->assertNotEmpty(session('impersonating'));

        // 7. Verify ADC name and impersonation banner appear in HTML
        $content = $responseShop->getContent();
        $this->assertStringContainsString($adc->name, $content);
        $this->assertStringContainsString('Return to Admin', $content);

        // 8. Test "Return to Admin" from storefront
        $responseStop = $this->post('/impersonate/stop');
        $responseStop->assertStatus(302);
        $returnUrl = $responseStop->headers->get('Location');
        $this->assertStringContainsString('admin', $returnUrl);

        // Follow return to admin
        $responseAdminReturn = $this->get($returnUrl);
        $this->assertTrue(auth('admin')->check());
        $this->assertEquals($admin->id, auth('admin')->id());
    }
}
