<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\SellerProfile;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Enums\KycStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ShopSearchAutocompleteTest extends TestCase
{
    use RefreshDatabase;

    private User $seller;
    private Category $category;
    private Brand $brand;

    protected function setUp(): void
    {
        parent::setUp();

        // Create active seller user
        $this->seller = User::create([
            'name' => 'Test Seller',
            'email' => 'seller@test.com',
            'password' => bcrypt('password'),
            'role' => 'seller',
            'status' => '1',
        ]);

        // Create approved seller profile
        SellerProfile::create([
            'user_id' => $this->seller->id,
            'seller_id' => 'IMV1001',
            'authorized_rep_name' => 'Rep Name',
            'company_name' => 'Seller Co',
            'kyc_status' => KycStatus::APPROVED,
        ]);

        // Create active category
        $this->category = Category::create([
            'name' => 'Organic Foods',
            'slug' => 'organic-foods',
            'status' => '1',
        ]);

        // Create brand
        $this->brand = Brand::create([
            'name' => 'Himalayan Organics',
            'slug' => 'himalayan-organics',
        ]);
    }

    public function test_autocomplete_returns_matching_results(): void
    {
        // Create product
        Product::create([
            'product_name' => 'Organic Raw Honey',
            'slug' => 'organic-raw-honey',
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'seller_id' => $this->seller->id,
            'status' => 'Approved',
            'mrp' => 500,
            'adc_purchase_price' => 400,
            'featured_image' => 'products/honey.png',
            'barcode' => '123456789012',
            'seller_price_incl_gst' => 350.00,
            'gst_rate' => 18.00,
            'net_quantity' => 1,
            'case_pack_quantity' => 10,
            'country_of_origin' => 'India',
            'hsn_code' => '123456',
            'unit_of_measure' => 'pcs',
            'description' => 'Organic raw honey description',
            'product_specification' => 'Organic honey specs',
            'storage_instructions' => 'Keep in a cool dry place',
            'gallery' => [],
            'shelf_life' => '24 months',
            'length' => 10.0,
            'width' => 10.0,
            'height' => 10.0,
            'dimension_unit' => 'cm',
            'weight' => 1.0,
            'weight_unit' => 'kg',
            'created_by' => $this->seller->id,
        ]);

        $response = $this->getJson(route('api.search-autocomplete', ['query' => 'organic']));

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'products' => [
                '*' => ['name', 'url', 'price', 'mrp', 'discount', 'image']
            ],
            'categories' => [
                '*' => ['name', 'url']
            ],
            'brands' => [
                '*' => ['name', 'url']
            ]
        ]);

        $response->assertJsonFragment(['name' => 'Organic Raw Honey']);
        $response->assertJsonFragment(['name' => 'Organic Foods']);
        $response->assertJsonFragment(['name' => 'Himalayan Organics']);
    }

    public function test_autocomplete_ignores_short_queries(): void
    {
        $response = $this->getJson(route('api.search-autocomplete', ['query' => 'o']));

        $response->assertStatus(200);
        $response->assertExactJson([
            'products' => [],
            'categories' => [],
            'brands' => []
        ]);
    }
}
