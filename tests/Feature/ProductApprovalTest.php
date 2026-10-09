<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Admin;
use App\Models\Vendor;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductApprovalTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_approve_product_with_margin_overrides(): void
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
            'password' => bcrypt('password')
        ]);

        // 3. Setup Brand and Category
        $brand = Brand::create(['name' => 'Test Brand', 'slug' => 'test-brand']);
        $category = Category::create(['name' => 'Test Category', 'slug' => 'test-category']);

        // 4. Create a product pending approval
        $product = Product::create([
            'product_name' => 'Test Product',
            'barcode' => '123456789012',
            'seller_id' => $vendor->id,
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'mrp' => 150.00,
            'seller_price_incl_gst' => 100.00,
            'gst_rate' => 18.00,
            'status' => 'Pending Approval',
            'is_new' => true,
            'created_by' => $vendor->id,
            'description' => 'Test Product Description',
            'product_specification' => 'Test Spec',
            'storage_instructions' => 'Keep cool',
            'featured_image' => 'http://example.com/image.jpg',
            'gallery' => [],
            'hsn_code' => '123456',
            'unit_of_measure' => 'pcs',
            'net_quantity' => 1,
            'case_pack_quantity' => 10,
            'shelf_life' => '12 months',
            'country_of_origin' => 'India',
            'length' => 10.0,
            'width' => 10.0,
            'height' => 10.0,
            'dimension_unit' => 'cm',
            'weight' => 1.0,
            'weight_unit' => 'kg'
        ]);

        // 5. Submit approval with overrides
        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.products.approvals.action', $product->id), [
                'action' => 'approve',
                'admin_margin_percentage' => 12.00,
                'ddc_margin_percentage' => 7.00,
                'mbp_percentage' => 4.00,
            ]);

        $response->assertRedirect(route('admin.products.approvals.new'));

        // 6. Assert product was updated
        $product->refresh();
        $this->assertEquals('Approved', $product->status);
        $this->assertFalse($product->is_new);
        $this->assertEquals(12.00, $product->admin_margin_percentage);
        $this->assertEquals(7.00, $product->ddc_margin_percentage);
        $this->assertEquals(4.00, $product->mbp_percentage);

        // Verify that math was calculated correctly
        // Base Cost = 100 / 1.18 = 84.75
        // Admin Margin Amt = 84.75 * 12% = 10.17
        // DDC Margin Amt = (84.75 + 10.17) * 7% = 94.92 * 7% = 6.64
        $this->assertEquals(84.75, $product->base_cost);
        $this->assertEquals(10.17, $product->admin_margin_amount);
        $this->assertEquals(6.64, $product->ddc_margin_amount);
    }

    public function test_admin_can_reject_product_with_remarks_keeping_it_new(): void
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
            'password' => bcrypt('password')
        ]);

        // 3. Setup Brand and Category
        $brand = Brand::create(['name' => 'Test Brand', 'slug' => 'test-brand']);
        $category = Category::create(['name' => 'Test Category', 'slug' => 'test-category']);

        // 4. Create a product pending approval
        $product = Product::create([
            'product_name' => 'Test Product',
            'barcode' => '123456789012',
            'seller_id' => $vendor->id,
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'mrp' => 150.00,
            'seller_price_incl_gst' => 100.00,
            'gst_rate' => 18.00,
            'status' => 'Pending Approval',
            'is_new' => true,
            'created_by' => $vendor->id,
            'description' => 'Test Product Description',
            'product_specification' => 'Test Spec',
            'storage_instructions' => 'Keep cool',
            'featured_image' => 'http://example.com/image.jpg',
            'gallery' => [],
            'hsn_code' => '123456',
            'unit_of_measure' => 'pcs',
            'net_quantity' => 1,
            'case_pack_quantity' => 10,
            'shelf_life' => '12 months',
            'country_of_origin' => 'India',
            'length' => 10.0,
            'width' => 10.0,
            'height' => 10.0,
            'dimension_unit' => 'cm',
            'weight' => 1.0,
            'weight_unit' => 'kg'
        ]);

        // 5. Submit rejection with remarks
        $response = $this->actingAs($admin, 'admin')
            ->post(route('admin.products.approvals.action', $product->id), [
                'action' => 'reject',
                'admin_remarks' => 'Requires better quality images.',
            ]);

        $response->assertRedirect(route('admin.products.approvals.new'));

        // 6. Assert product was updated
        $product->refresh();
        $this->assertEquals('Rejected', $product->status);
        $this->assertTrue($product->is_new); // Remains new!
        $this->assertEquals('Requires better quality images.', $product->admin_remarks);

        // Verify audit log has the remarks
        $this->assertDatabaseHas('product_audit_logs', [
            'product_id' => $product->id,
            'approval_status' => 'Rejected',
            'admin_remarks' => 'Requires better quality images.',
            'modified_by' => $admin->id
        ]);
    }

    public function test_vendor_can_bulk_import_products(): void
    {
        // 1. Setup Vendor with allowance configuration
        $vendor = Vendor::create([
            'name' => 'Test Vendor',
            'email' => 'vendor@test.com',
            'password' => bcrypt('password'),
            'status' => '1',
            'allow_own_products' => 'Yes'
        ]);

        // 2. Setup Brand and Category
        $brand = Brand::create(['name' => 'Test Brand', 'slug' => 'test-brand']);
        $category = Category::create(['name' => 'Test Category', 'slug' => 'test-category']);

        // 3. Setup HSN in DB
        \DB::table('hsns')->insert([
            'hsn_code' => '998877',
            'description' => 'Test HSN Code',
            'gst_percentage' => 12.00,
            'status' => '1',
            'created_at' => now(),
            'updated_at' => now()
        ]);

        // 4. Create a fake CSV file
        $csvContent = "product_name,barcode,brand_id,hsn_code,seller_price_incl_gst,mrp,unit_of_measure,description,country_of_origin\n" .
            "Bulk Product 1,BAR-111222,{$brand->id},998877,100.00,150.00,pcs,Awesome bulk product,India\n" .
            "Bulk Product 2,BAR-333444,{$brand->id},998877,200.00,300.00,pcs,Another bulk product,India\n";

        $file = \Illuminate\Http\UploadedFile::fake()->createWithContent('products.csv', $csvContent);

        // 5. Submit bulk import request
        $response = $this->actingAs($vendor, 'vendor')
            ->post(route('vendor.products.bulk-import'), [
                'category_id' => $category->id,
                'file' => $file
            ]);

        $response->assertRedirect();
        $response->assertSessionHasNoErrors();

        // 6. Assert products were created with correct margins and pricing calculated
        $this->assertDatabaseHas('products', [
            'product_name' => 'Bulk Product 1',
            'barcode' => 'BAR-111222',
            'seller_price_incl_gst' => 100.00,
            'mrp' => 150.00,
            'gst_rate' => 12.00,
            'status' => 'Draft',
            'category_id' => $category->id,
            'brand_id' => $brand->id
        ]);

        $this->assertDatabaseHas('products', [
            'product_name' => 'Bulk Product 2',
            'barcode' => 'BAR-333444',
            'seller_price_incl_gst' => 200.00,
            'mrp' => 300.00,
            'gst_rate' => 12.00,
            'status' => 'Draft',
            'category_id' => $category->id,
            'brand_id' => $brand->id
        ]);

        // Verify audit logs exist
        $p1 = Product::where('barcode', 'BAR-111222')->first();
        $this->assertNotNull($p1);
        $this->assertDatabaseHas('product_audit_logs', [
            'product_id' => $p1->id,
            'approval_status' => 'Draft',
            'modified_by' => $vendor->id
        ]);
    }

    public function test_vendor_can_download_bulk_sample_xlsx(): void
    {
        $vendor = Vendor::create([
            'name' => 'Test Vendor',
            'email' => 'vendor@test.com',
            'password' => bcrypt('password'),
            'status' => '1',
            'allow_own_products' => 'Yes'
        ]);

        $response = $this->actingAs($vendor, 'vendor')
            ->get(route('vendor.products.bulk-sample'));

        $response->assertStatus(200);
        $response->assertHeader('Content-Disposition', 'attachment; filename=product_bulk_sample.xlsx');

        $file = $response->getFile();
        $this->assertFileExists($file->getPathname());
    }
}
