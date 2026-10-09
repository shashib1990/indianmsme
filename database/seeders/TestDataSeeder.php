<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;

use Illuminate\Support\Facades\Hash;

class TestDataSeeder extends Seeder
{
    public function run()
    {
        // 1. Get or Create Category
        $category = Category::firstOrCreate(
            ['slug' => 'electronics'],
            [
                'name' => 'Electronics',
                'status' => '1'
            ]
        );

        // 2. Get or Create Brand
        $brand = Brand::firstOrCreate(
            ['slug' => 'tata'],
            [
                'name' => 'TATA',
                'status' => '1'
            ]
        );



        // 4. Get Main Vendor (Sample Vendor, id = 2)
        $parentVendor = User::where('role', 'vendor')->first() ?? User::find(2);

        if (!$parentVendor) {
            $parentVendor = User::create([
                'name' => 'Sample Vendor',
                'email' => 'vendor@indianmsme.org',
                'mobile' => '9999999999',
                'password' => Hash::make('password'),
                'role' => 'vendor',
                'status' => '1',
                'company_name' => 'Sample Vendor Ltd',
                'company_type' => 'Company',
                'seller_id' => \App\Models\SellerProfile::generateSellerId(),
                'authorized_rep_name' => 'Main Vendor Rep',
                'kyc_status' => 'APPROVED',
                'allow_own_products' => 'Yes'
            ]);
        }

        // 5. Create Sub-Seller
        $subSeller = User::where('email', 'subseller@indianmsme.org')->first();
        if (!$subSeller) {
            $subSeller = User::create([
                'name' => 'Mini TATA Store',
                'email' => 'subseller@indianmsme.org',
                'mobile' => '9876543210',
                'password' => Hash::make('password'),
                'role' => 'seller',
                'status' => '1',
                'parent_id' => $parentVendor->id,
                'company_name' => 'Mini TATA Store',
                'company_type' => 'Proprietorship Firm',
                'seller_id' => \App\Models\SellerProfile::generateSellerId(),
                'authorized_rep_name' => 'Sub Seller Rep',
                'alternate_mobile' => '9876543211',
                'category_id' => $category->id,
                'kyc_status' => 'APPROVED',
                'allow_own_products' => 'No'
            ]);


        }

        // Remove existing products to avoid duplicate barcode errors
        Product::whereIn('barcode', ['890123456789', '890123456790', '890123456791', '890123456792'])->delete();

        // 6. Create Product 1: New Submission (Pending Approval, is_new = true)
        Product::create([
            'product_name' => 'TATA Smart LED TV',
            'barcode' => '890123456789',
            'seller_id' => $parentVendor->id,
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'mrp' => 25000.00,
            'seller_price_incl_gst' => 18000.00,
            'gst_rate' => 18.00,
            'status' => 'Pending Approval',
            'is_new' => true,
            'created_by' => $parentVendor->id,
            'description' => 'A smart LED television from TATA featuring crystal clear 4K display and smart assistant integration.',
            'product_specification' => '43 Inch, 4K UHD, HDR10+, 3x HDMI, 2x USB',
            'storage_instructions' => 'Handle with care. Keep in dry place.',
            'featured_image' => 'https://images.unsplash.com/photo-1593305841991-05c297ba4575?w=500',
            'gallery' => [],
            'hsn_code' => '852872',
            'unit_of_measure' => 'pcs',
            'net_quantity' => 1,
            'case_pack_quantity' => 1,
            'shelf_life' => '5 years',
            'country_of_origin' => 'India',
            'length' => 97.0,
            'width' => 10.0,
            'height' => 57.0,
            'dimension_unit' => 'cm',
            'weight' => 8.5,
            'weight_unit' => 'kg'
        ]);

        // 7. Create Product 2: Update Submission (Pending Approval, is_new = false)
        Product::create([
            'product_name' => 'TATA Air Conditioner (Updated Spec)',
            'barcode' => '890123456790',
            'seller_id' => $parentVendor->id,
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'mrp' => 45000.00,
            'seller_price_incl_gst' => 32000.00,
            'gst_rate' => 18.00,
            'status' => 'Pending Approval',
            'is_new' => false,
            'created_by' => $parentVendor->id,
            'description' => 'High efficiency 1.5 Ton Split AC with copper condenser and auto cooling technology.',
            'product_specification' => '1.5 Ton, 5 Star, Copper Condenser, 3-in-1 Filter',
            'storage_instructions' => 'Store upright in a cool, dry warehouse.',
            'featured_image' => 'https://images.unsplash.com/photo-1621905251189-08b45d6a269e?w=500',
            'gallery' => [],
            'hsn_code' => '841510',
            'unit_of_measure' => 'pcs',
            'net_quantity' => 1,
            'case_pack_quantity' => 1,
            'shelf_life' => '10 years',
            'country_of_origin' => 'India',
            'length' => 100.0,
            'width' => 30.0,
            'height' => 40.0,
            'dimension_unit' => 'cm',
            'weight' => 42.0,
            'weight_unit' => 'kg'
        ]);

        // 8. Create Product 3: Approved Catalog (Approved, is_new = false)
        $pricing = \App\Services\PricingService::calculate(2200.00, 18.00, 3500.00, 10.00, 5.00, 3.00);
        Product::create(array_merge([
            'product_name' => 'TATA Induction Cooktop',
            'barcode' => '890123456791',
            'seller_id' => $parentVendor->id,
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'mrp' => 3500.00,
            'seller_price_incl_gst' => 2200.00,
            'gst_rate' => 18.00,
            'status' => 'Approved',
            'is_new' => false,
            'created_by' => $parentVendor->id,
            'description' => '2000 Watt induction cooktop with feather touch control and 8 pre-programmed menus.',
            'product_specification' => '2000W, Touch Controls, Auto shut-off, Pan detection',
            'storage_instructions' => 'Keep away from water when stored.',
            'featured_image' => 'https://images.unsplash.com/photo-1574269661431-52a6cd0347de?w=500',
            'gallery' => [],
            'hsn_code' => '851660',
            'unit_of_measure' => 'pcs',
            'net_quantity' => 1,
            'case_pack_quantity' => 4,
            'shelf_life' => '3 years',
            'country_of_origin' => 'India',
            'length' => 35.0,
            'width' => 28.0,
            'height' => 6.0,
            'dimension_unit' => 'cm',
            'weight' => 2.1,
            'weight_unit' => 'kg'
        ], $pricing));

        // 9. Create Product 4: Rejected Catalog (Rejected, is_new = true)
        Product::create([
            'product_name' => 'TATA Microwave Oven',
            'barcode' => '890123456792',
            'seller_id' => $parentVendor->id,
            'brand_id' => $brand->id,
            'category_id' => $category->id,
            'mrp' => 8500.00,
            'seller_price_incl_gst' => 6000.00,
            'gst_rate' => 18.00,
            'status' => 'Rejected',
            'is_new' => true,
            'created_by' => $parentVendor->id,
            'description' => '20 Liter solo microwave oven perfect for reheating and quick cooking.',
            'product_specification' => '20L, Solo, Jog Dials, 5 Power Levels',
            'storage_instructions' => 'Do not stack heavy items on top.',
            'featured_image' => 'https://images.unsplash.com/photo-1574269661431-52a6cd0347de?w=500',
            'gallery' => [],
            'hsn_code' => '851650',
            'unit_of_measure' => 'pcs',
            'net_quantity' => 1,
            'case_pack_quantity' => 1,
            'shelf_life' => '7 years',
            'country_of_origin' => 'India',
            'length' => 45.0,
            'width' => 35.0,
            'height' => 26.0,
            'dimension_unit' => 'cm',
            'weight' => 10.5,
            'weight_unit' => 'kg',
            'admin_remarks' => 'Image quality is poor, please upload high resolution images.'
        ]);
    }
}
