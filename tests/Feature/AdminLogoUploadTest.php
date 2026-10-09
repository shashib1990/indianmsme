<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\SystemSetting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminLogoUploadTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = Admin::first() ?? Admin::create([
            'name' => 'Test Admin',
            'email' => 'admin_test_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => '1',
            'force_change_password' => '0',
        ]);
    }

    public function test_admin_can_upload_logos_to_remote_upload_service(): void
    {
        Storage::fake('public');
        Http::fake([
            'upload.sainikcanteen.com/*' => Http::response([
                'status' => true,
                'file_url' => 'https://upload.sainikcanteen.com/uploads/logos/fake_logo.png'
            ], 200)
        ]);

        $logoFile = UploadedFile::fake()->image('new_logo.png', 300, 100);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.settings.logos.update'), [
                'website_header_logo' => $logoFile,
                'website_footer_logo' => $logoFile,
                'panel_logo' => $logoFile,
            ]);

        $response->assertRedirect(route('admin.settings.logos.index'));
        $response->assertSessionHas('success');

        $this->assertEquals(
            'https://upload.sainikcanteen.com/uploads/logos/fake_logo.png',
            SystemSetting::getVal('website_header_logo')
        );
        $this->assertEquals(
            'https://upload.sainikcanteen.com/uploads/logos/fake_logo.png',
            SystemSetting::getVal('website_footer_logo')
        );
        $this->assertEquals(
            'https://upload.sainikcanteen.com/uploads/logos/fake_logo.png',
            SystemSetting::getVal('panel_logo')
        );
    }
}
