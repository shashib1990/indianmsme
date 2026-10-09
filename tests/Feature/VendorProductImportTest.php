<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Vendor;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Hsn;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class VendorProductImportTest extends TestCase
{
    use RefreshDatabase;

    private Vendor $vendor;
    private Category $category;
    private Brand $brand;
    private Hsn $hsn;

    protected function setUp(): void
    {
        parent::setUp();

        $this->vendor = Vendor::create([
            'name' => 'Test Product Vendor',
            'email' => 'productvendor@test.com',
            'password' => bcrypt('password'),
            'status' => '1',
            'allow_own_products' => 'Yes'
        ]);

        $this->category = Category::create([
            'name' => 'Electronics',
            'slug' => 'electronics',
            'status' => '1'
        ]);

        $this->brand = Brand::create([
            'name' => 'Samsung',
            'slug' => 'samsung',
            'status' => '1'
        ]);

        $this->hsn = Hsn::create([
            'hsn_code' => '851712',
            'gst_percentage' => '18.00',
            'status' => '1'
        ]);
    }

    public function test_vendor_can_import_products_with_asterisk_headers_and_formatted_prices(): void
    {
        $csvHeader = "Product Name*,Category*,Brand*,HSN Code*,MRP (Incl. GST)*,Seller Price (Incl. GST)*,Barcode,Search Keywords / Tags\n";
        $csvRow = "Smart Phone,Electronics,Samsung,851712,\"1,500.00\",\"1,200.00\",890123456789,\"phone, smart, mobile\"\n";
        $csvContent = $csvHeader . $csvRow;

        $file = UploadedFile::fake()->createWithContent('products.csv', $csvContent);

        $response = $this->actingAs($this->vendor, 'vendor')
            ->post(route('vendor.products.bulk-import'), [
                'file' => $file
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'seller_id' => $this->vendor->id,
            'product_name' => 'Smart Phone',
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id,
            'hsn_code' => '851712',
            'mrp' => 1500.00,
            'seller_price_incl_gst' => 1200.00
        ]);
    }

    public function test_vendor_product_import_fallback_to_request_category_and_brand(): void
    {
        $csvHeader = "Product Name,Category,Brand,HSN Code,MRP,Seller Price\n";
        $csvRow = "Wireless Earbuds,\"\",\"\",851712,500,400\n";
        $csvContent = $csvHeader . $csvRow;

        $file = UploadedFile::fake()->createWithContent('products_fallback.csv', $csvContent);

        $response = $this->actingAs($this->vendor, 'vendor')
            ->post(route('vendor.products.bulk-import'), [
                'file' => $file,
                'category_id' => $this->category->id,
                'brand_id' => $this->brand->id
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('products', [
            'seller_id' => $this->vendor->id,
            'product_name' => 'Wireless Earbuds',
            'category_id' => $this->category->id,
            'brand_id' => $this->brand->id
        ]);
    }
}
