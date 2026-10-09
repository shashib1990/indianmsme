<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\User;
use App\Models\Brand;
use App\Models\Hsn;
use App\Models\Product;
use App\Models\SellerProfile;
use App\Http\Controllers\Admin\BulkImportController;
use Illuminate\Http\Request;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductImportLogicTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_import_allows_empty_category()
    {
        // 1. Create Seller
        $sellerUser = User::create([
            'name' => 'Test Seller',
            'email' => 'seller@example.com',
            'mobile' => '9876543210',
            'role' => 'seller',
            'status' => '1',
            'password' => bcrypt('password'),
        ]);

        $profile = SellerProfile::firstOrCreate(['user_id' => $sellerUser->id]);
        $profile->update([
            'seller_id' => 'IMV2001',
            'company_name' => 'Test Seller Company',
        ]);

        // 2. Create Brand & HSN Code
        $brand = Brand::create([
            'name' => 'Sample Brand',
            'status' => '1',
        ]);

        $hsn = Hsn::create([
            'hsn_code' => '123456',
            'gst_rate' => 18.00,
            'gst_percentage' => 18.00,
        ]);

        // 3. Test productProcess with category_id = null (empty category)
        $controller = new BulkImportController();

        $records = [
            [
                'row_number' => 2,
                'seller_code' => 'IMV2001',
                'seller_user_id' => $sellerUser->id,
                'product_code' => 'PROD-EMPTY-CAT',
                'product_name' => 'Sample Product No Category',
                'category_id' => null,
                'sub_category_id' => null,
                'child_category_id' => null,
                'brand_id' => $brand->id,
                'hsn_code' => '123456',
                'gst_rate' => 18.00,
                'mrp' => 100.00,
                'seller_price_incl_gst' => 80.00,
                'unit_of_measure' => 'Piece',
                'case_pack_quantity' => 1,
                'product_keywords' => [],
                'description' => 'Test product description',
                'product_specification' => null,
                'storage_instructions' => null,
                'shelf_life' => null,
                'country_of_origin' => 'India',
                'height' => null,
                'width' => null,
                'length' => null,
                'weight' => null,
                'dimension_unit' => 'cm',
                'weight_unit' => 'kg',
                'featured_image' => null,
                'status' => 'Approved',
                'is_valid' => true,
                'errors' => [],
            ]
        ];

        $request = Request::create('/admin/import/product/process', 'POST', ['records' => $records]);
        $response = $controller->productProcess($request);

        $json = $response->getData(true);
        $this->assertTrue($json['success']);
        $this->assertEquals(1, $json['imported']);

        // 4. Verify product was saved in database with null category_id
        $product = Product::where('product_code', 'PROD-EMPTY-CAT')->first();
        $this->assertNotNull($product);
        $this->assertNull($product->category_id);
        $this->assertEquals('Sample Product No Category', $product->product_name);
    }
}
