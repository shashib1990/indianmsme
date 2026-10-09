<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\HomeBanner;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;

class HomePageBannerTest extends TestCase
{
    use RefreshDatabase;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Create an Admin user
        $this->admin = Admin::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => '1',
            'force_change_password' => '0',
            'is_super_admin' => '1',
        ]);
    }

    public function test_guest_cannot_access_banner_settings(): void
    {
        $response = $this->get(route('admin.banners.index'));
        $response->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_access_banner_settings(): void
    {
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.banners.index'));

        $response->assertStatus(200);
        $response->assertSee('Home Page Banners');
        $response->assertSee('Add New Banner');
        $response->assertSee('Hero Left Zone Banners');
        $response->assertSee('Hero Right Zone Banners');
        $response->assertSee('Middle Section - 3 Column Banners');
        $response->assertSee('Bottom Section - 2 Column Banners');
    }

    public function test_admin_can_upload_left_right_middle_and_bottom_banners(): void
    {
        Storage::fake('public');
        Http::fake([
            'upload.sainikcanteen.com/*' => Http::response([
                'status' => true,
                'file_url' => 'https://upload.sainikcanteen.com/storage/uploads/banners/fake_banner_middle.png'
            ], 200)
        ]);

        $middleImage = UploadedFile::fake()->image('middle_banner.png', 800, 400);

        // Upload Middle 3-Col Banner with Link URL
        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.banners.store'), [
                'position' => 'middle_3_col',
                'image' => $middleImage,
                'link_url' => 'https://example.com/special-offer',
                'is_active' => '1',
                'sort_order' => 1,
            ]);

        $response->assertRedirect(route('admin.banners.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('home_banners', [
            'position' => 'middle_3_col',
            'image_url' => 'https://upload.sainikcanteen.com/storage/uploads/banners/fake_banner_middle.png',
            'link_url' => 'https://example.com/special-offer',
            'is_active' => true,
            'sort_order' => 1
        ]);
    }

    public function test_admin_can_toggle_banner_status(): void
    {
        $banner = HomeBanner::create([
            'position' => 'left',
            'image_url' => 'http://example.com/left.png',
            'is_active' => true
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.banners.toggle-status', $banner->id));

        $response->assertRedirect(route('admin.banners.index'));
        $response->assertSessionHas('success');

        $banner->refresh();
        $this->assertFalse($banner->is_active);
    }

    public function test_admin_can_delete_banner(): void
    {
        $banner = HomeBanner::create([
            'position' => 'left',
            'image_url' => 'http://example.com/left.png',
            'is_active' => true
        ]);

        $response = $this->actingAs($this->admin, 'admin')
            ->delete(route('admin.banners.destroy', $banner->id));

        $response->assertRedirect(route('admin.banners.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('home_banners', [
            'id' => $banner->id
        ]);
    }

    public function test_banners_displayed_on_homepage(): void
    {
        // 1. Initially empty/no image - banners should not be displayed
        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertDontSee('Left Banner');
        $response->assertDontSee('Right Banner');

        // 2. Set active images - they should be visible on homepage
        HomeBanner::create([
            'position' => 'left',
            'image_url' => 'http://example.com/left_banner_1.png',
            'is_active' => true,
            'sort_order' => 1
        ]);
        HomeBanner::create([
            'position' => 'right',
            'image_url' => 'http://example.com/right_banner_1.png',
            'is_active' => true,
            'sort_order' => 1
        ]);
        HomeBanner::create([
            'position' => 'middle_3_col',
            'image_url' => 'http://example.com/middle_banner_1.png',
            'link_url' => 'http://example.com/cat1',
            'is_active' => true,
            'sort_order' => 1
        ]);
        HomeBanner::create([
            'position' => 'above_newsletter',
            'image_url' => 'http://example.com/bottom_banner_1.png',
            'link_url' => 'http://example.com/cat2',
            'is_active' => true,
            'sort_order' => 1
        ]);

        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertSee('http://example.com/left_banner_1.png');
        $response->assertSee('http://example.com/right_banner_1.png');
        $response->assertSee('http://example.com/middle_banner_1.png');
        $response->assertSee('http://example.com/cat1');
        $response->assertSee('http://example.com/bottom_banner_1.png');
        $response->assertSee('http://example.com/cat2');

        // 3. Disable Left Banner - only right should show, layout overrides should be set
        HomeBanner::where('position', 'left')->update(['is_active' => false]);

        $response = $this->get(route('home'));
        $response->assertStatus(200);
        $response->assertDontSee('http://example.com/left_banner_1.png');
        $response->assertSee('http://example.com/right_banner_1.png');
        // Check for 100% width on right wrapper
        $response->assertSee('display: block !important; width: 100% !important;');
    }
}
