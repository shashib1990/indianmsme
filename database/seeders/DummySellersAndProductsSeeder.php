<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Brand;
use App\Models\Category;
use App\Models\SubCategory;
use App\Models\ChildCategory;
use App\Models\Product;
use App\Models\SubSellerProduct;
use App\Models\SellerProfile;
use App\Models\State;
use App\Models\District;
use Illuminate\Support\Facades\Hash;
use App\Services\PricingService;

class DummySellersAndProductsSeeder extends Seeder
{
    public function run()
    {
        // 1. Get or Create States and Districts
        $state = State::firstOrCreate(
            ['name' => 'Maharashtra'],
            ['code' => 'MH', 'status' => '1']
        );

        $district = District::firstOrCreate(
            ['name' => 'Mumbai'],
            ['state_id' => $state->id, 'status' => '1']
        );

        // 2. Create Categories
        $categoriesData = [
            ['slug' => 'electronics', 'name' => 'Electronics', 'image' => 'https://images.unsplash.com/photo-1498049794561-7780e7231661?w=400&auto=format&fit=crop', 'status' => '1'],
            ['slug' => 'apparel-textiles', 'name' => 'Apparel & Textiles', 'image' => 'https://images.unsplash.com/photo-1544816155-12df9643f363?w=400&auto=format&fit=crop', 'status' => '1'],
            ['slug' => 'organic-foods', 'name' => 'Organic & Foods', 'image' => 'https://images.unsplash.com/photo-1540420773420-3366772f4999?w=400&auto=format&fit=crop', 'status' => '1'],
            ['slug' => 'handicrafts', 'name' => 'Handicrafts & Decor', 'image' => 'https://images.unsplash.com/photo-1513519245088-0e12902e5a38?w=400&auto=format&fit=crop', 'status' => '1'],
            ['slug' => 'ayurveda-wellness', 'name' => 'Ayurveda & Wellness', 'image' => 'https://images.unsplash.com/photo-1608571423902-eed4a5ad8108?w=400&auto=format&fit=crop', 'status' => '1'],
            ['slug' => 'leather-goods', 'name' => 'Leather Goods', 'image' => 'https://images.unsplash.com/photo-1473187983305-f615310e7daa?w=400&auto=format&fit=crop', 'status' => '1'],
            ['slug' => 'coir-eco-friendly', 'name' => 'Coir & Eco-Friendly', 'image' => 'https://images.unsplash.com/photo-1595273670150-bd0c3c392e46?w=400&auto=format&fit=crop', 'status' => '1'],
            ['slug' => 'khadi-handloom', 'name' => 'Khadi & Handloom', 'image' => 'https://images.unsplash.com/photo-1606744824163-985d376605aa?w=400&auto=format&fit=crop', 'status' => '1'],
            ['slug' => 'spices-herbs', 'name' => 'Spices & Herbs', 'image' => 'https://images.unsplash.com/photo-1596040033229-a9821ebd058d?w=400&auto=format&fit=crop', 'status' => '1'],
            ['slug' => 'metal-crafts', 'name' => 'Metal Crafts', 'image' => 'https://images.unsplash.com/photo-1618220179428-22790b461013?w=400&auto=format&fit=crop', 'status' => '1'],
            ['slug' => 'wooden-toys', 'name' => 'Wooden Toys', 'image' => 'https://images.unsplash.com/photo-1515488042361-404e9250afef?w=400&auto=format&fit=crop', 'status' => '1'],
            ['slug' => 'pottery-ceramics', 'name' => 'Pottery & Ceramics', 'image' => 'https://images.unsplash.com/photo-1565192647048-f997ded87ab7?w=400&auto=format&fit=crop', 'status' => '1'],
            ['slug' => 'jewelry-ornaments', 'name' => 'Jewelry & Ornaments', 'image' => 'https://images.unsplash.com/photo-1599643478518-a784e5dc4c8f?w=400&auto=format&fit=crop', 'status' => '1'],
            ['slug' => 'sports-fitness', 'name' => 'Sports & Fitness', 'image' => 'https://images.unsplash.com/photo-1476480862126-209bfaa8edc8?w=400&auto=format&fit=crop', 'status' => '1'],
            ['slug' => 'home-appliances', 'name' => 'Home Appliances', 'image' => 'https://images.unsplash.com/photo-1584622650111-993a426fbf0a?w=400&auto=format&fit=crop', 'status' => '1'],
            ['slug' => 'stationery-office', 'name' => 'Stationery & Office', 'image' => 'https://images.unsplash.com/photo-1456513080510-7bf3a84b82f8?w=400&auto=format&fit=crop', 'status' => '1'],
            ['slug' => 'footwear-shoes', 'name' => 'Footwear & Shoes', 'image' => 'https://images.unsplash.com/photo-1549298916-b41d501d3772?w=400&auto=format&fit=crop', 'status' => '1'],
        ];

        $seededCategories = [];
        foreach ($categoriesData as $catData) {
            $seededCategories[$catData['slug']] = Category::updateOrCreate(
                ['slug' => $catData['slug']],
                ['name' => $catData['name'], 'image' => $catData['image'], 'status' => $catData['status']]
            );
        }

        $catApparel = $seededCategories['apparel-textiles'];
        $catOrganic = $seededCategories['organic-foods'];
        $catHandicraft = $seededCategories['handicrafts'];

        // 3. Create Sub-Categories
        $subSarees = SubCategory::firstOrCreate(
            ['category_id' => $catApparel->id, 'slug' => 'sarees'],
            ['name' => 'Sarees', 'status' => '1']
        );

        $subMenswear = SubCategory::firstOrCreate(
            ['category_id' => $catApparel->id, 'slug' => 'menswear'],
            ['name' => 'Menswear', 'status' => '1']
        );

        $subHoney = SubCategory::firstOrCreate(
            ['category_id' => $catOrganic->id, 'slug' => 'honey-sweeteners'],
            ['name' => 'Honey & Sweeteners', 'status' => '1']
        );

        $subOils = SubCategory::firstOrCreate(
            ['category_id' => $catOrganic->id, 'slug' => 'edible-oils'],
            ['name' => 'Edible Oils', 'status' => '1']
        );

        // 4. Create Child-Categories
        $childKhadiSarees = ChildCategory::firstOrCreate(
            ['category_id' => $catApparel->id, 'sub_category_id' => $subSarees->id, 'slug' => 'khadi-sarees'],
            ['name' => 'Khadi Sarees', 'status' => '1']
        );

        $childKurtas = ChildCategory::firstOrCreate(
            ['category_id' => $catApparel->id, 'sub_category_id' => $subMenswear->id, 'slug' => 'kurtas'],
            ['name' => 'Kurtas', 'status' => '1']
        );

        $childOrgHoney = ChildCategory::firstOrCreate(
            ['category_id' => $catOrganic->id, 'sub_category_id' => $subHoney->id, 'slug' => 'organic-honey'],
            ['name' => 'Organic Honey', 'status' => '1']
        );

        $childMustardOil = ChildCategory::firstOrCreate(
            ['category_id' => $catOrganic->id, 'sub_category_id' => $subOils->id, 'slug' => 'mustard-oil'],
            ['name' => 'Mustard Oil', 'status' => '1']
        );

        // 5. Create Brands
        $brandKhadi = Brand::firstOrCreate(
            ['slug' => 'khadi-india'],
            ['name' => 'Khadi India', 'status' => '1']
        );

        $brandFabIndia = Brand::firstOrCreate(
            ['slug' => 'fabindia'],
            ['name' => 'FabIndia', 'status' => '1']
        );

        $brandHimalayan = Brand::firstOrCreate(
            ['slug' => 'himalayan-organics'],
            ['name' => 'Himalayan Organics', 'status' => '1']
        );

        // 6. Create Master Seller 1 (Aravind MSME Handlooms)
        $masterSeller1 = User::where('email', 'aravind@indianmsme.org')->first();
        if (!$masterSeller1) {
            $masterSeller1 = User::create([
                'name' => 'Aravind MSME Handlooms',
                'email' => 'aravind@indianmsme.org',
                'mobile' => '9811111111',
                'password' => Hash::make('password'),
                'role' => 'seller',
                'status' => '1',
                'company_name' => 'Aravind Handlooms Pvt Ltd',
                'company_type' => 'Company',
                'seller_id' => SellerProfile::generateSellerId(),
                'authorized_rep_name' => 'Aravind Kumar',
                'kyc_status' => 'APPROVED',
                'allow_own_products' => 'Yes',
                'state_id' => $state->id,
                'district_id' => $district->id,
                'address' => '102, Handloom Tower, Gandhi Nagar',
                'pincode' => '400001',
            ]);
        }

        // Create Sub-Seller 1a (Aravind Handlooms - Pune Store)
        $subSeller1a = User::where('email', 'subseller.pune@aravindmsme.org')->first();
        if (!$subSeller1a) {
            $subSeller1a = User::create([
                'name' => 'Aravind Handlooms - Pune Store',
                'email' => 'subseller.pune@aravindmsme.org',
                'mobile' => '9811111112',
                'password' => Hash::make('password'),
                'role' => 'seller',
                'status' => '1',
                'parent_id' => $masterSeller1->id,
                'company_name' => 'Aravind Handlooms Pune Outlet',
                'company_type' => 'Proprietorship Firm',
                'seller_id' => SellerProfile::generateSellerId(),
                'authorized_rep_name' => 'Pune Rep',
                'kyc_status' => 'APPROVED',
                'allow_own_products' => 'No',
                'state_id' => $state->id,
                'district_id' => $district->id,
                'address' => 'Shop 5, M.G. Road, Pune',
                'pincode' => '411001',
            ]);
        }

        // 7. Create Master Seller 2 (Himalayan Organic Farms)
        $masterSeller2 = User::where('email', 'himalayan@indianmsme.org')->first();
        if (!$masterSeller2) {
            $masterSeller2 = User::create([
                'name' => 'Himalayan Organic Farms',
                'email' => 'himalayan@indianmsme.org',
                'mobile' => '9822222222',
                'password' => Hash::make('password'),
                'role' => 'seller',
                'status' => '1',
                'company_name' => 'Himalayan Organic Farms',
                'company_type' => 'Partnership Firm',
                'seller_id' => SellerProfile::generateSellerId(),
                'authorized_rep_name' => 'Rajesh Sharma',
                'kyc_status' => 'APPROVED',
                'allow_own_products' => 'Yes',
                'state_id' => $state->id,
                'district_id' => $district->id,
                'address' => 'Plot 44, Industrial Area, Mumbai',
                'pincode' => '400012',
            ]);
        }

        // Create Sub-Seller 2a (Himalayan Organic - Delhi Outlet)
        $subSeller2a = User::where('email', 'subseller.delhi@himalayanorganic.org')->first();
        if (!$subSeller2a) {
            $subSeller2a = User::create([
                'name' => 'Himalayan Organic - Delhi Outlet',
                'email' => 'subseller.delhi@himalayanorganic.org',
                'mobile' => '9822222223',
                'password' => Hash::make('password'),
                'role' => 'seller',
                'status' => '1',
                'parent_id' => $masterSeller2->id,
                'company_name' => 'Himalayan Delhi Franchise',
                'company_type' => 'Proprietorship Firm',
                'seller_id' => SellerProfile::generateSellerId(),
                'authorized_rep_name' => 'Delhi Rep',
                'kyc_status' => 'APPROVED',
                'allow_own_products' => 'No',
                'state_id' => $state->id,
                'district_id' => $district->id,
                'address' => 'Connaught Place, New Delhi',
                'pincode' => '110001',
            ]);
        }

        // 8. Create Products
        $productsData = [
            [
                'product_name' => 'Handwoven Khadi Cotton Saree',
                'barcode' => '890123456801',
                'seller_id' => $masterSeller1->id,
                'brand_id' => $brandKhadi->id,
                'category_id' => $catApparel->id,
                'sub_category_id' => $subSarees->id,
                'child_category_id' => $childKhadiSarees->id,
                'mrp' => 2499.00,
                'seller_price_incl_gst' => 1500.00,
                'gst_rate' => 5.00,
                'status' => 'Approved',
                'is_new' => true,
                'created_by' => $masterSeller1->id,
                'description' => 'Elegant handwoven Khadi cotton saree with traditional border, perfect for formal and festive occasions.',
                'product_specification' => 'Material: 100% Khadi Cotton, Length: 6.5 meters, Color: Ivory and Gold',
                'featured_image' => 'https://images.unsplash.com/photo-1610030469983-98e550d6193c?w=500',
                'gallery' => [],
                'hsn_code' => '520811',
                'unit_of_measure' => 'pcs',
                'net_quantity' => 1,
                'case_pack_quantity' => 1,
                'country_of_origin' => 'India',
                'storage_instructions' => 'Store in a dry, cool place away from moisture.',
                'shelf_life' => 'N/A',
                'length' => 100.0,
                'width' => 10.0,
                'height' => 100.0,
                'dimension_unit' => 'cm',
                'weight' => 0.5,
                'weight_unit' => 'kg'
            ],
            [
                'product_name' => 'Traditional Lucknowi Chikankari Kurta',
                'barcode' => '890123456802',
                'seller_id' => $masterSeller1->id,
                'brand_id' => $brandFabIndia->id,
                'category_id' => $catApparel->id,
                'sub_category_id' => $subMenswear->id,
                'child_category_id' => $childKurtas->id,
                'mrp' => 1899.00,
                'seller_price_incl_gst' => 1200.00,
                'gst_rate' => 5.00,
                'status' => 'Approved',
                'is_new' => true,
                'created_by' => $masterSeller1->id,
                'description' => 'Hand-embroidered Lucknowi Chikankari kurta, made from premium linen for maximum breathability.',
                'product_specification' => 'Fabric: Pure Linen, Embroidery: Chikankari, Fit: Regular',
                'featured_image' => 'https://images.unsplash.com/photo-1583391733956-3750e0ff4e8b?w=500',
                'gallery' => [],
                'hsn_code' => '620520',
                'unit_of_measure' => 'pcs',
                'net_quantity' => 1,
                'case_pack_quantity' => 1,
                'country_of_origin' => 'India',
                'storage_instructions' => 'Store in a dry place. Dry clean recommended.',
                'shelf_life' => 'N/A',
                'length' => 50.0,
                'width' => 5.0,
                'height' => 80.0,
                'dimension_unit' => 'cm',
                'weight' => 0.3,
                'weight_unit' => 'kg'
            ],
            [
                'product_name' => 'Pure Himalayan Organic Honey',
                'barcode' => '890123456803',
                'seller_id' => $masterSeller2->id,
                'brand_id' => $brandHimalayan->id,
                'category_id' => $catOrganic->id,
                'sub_category_id' => $subHoney->id,
                'child_category_id' => $childOrgHoney->id,
                'mrp' => 499.00,
                'seller_price_incl_gst' => 350.00,
                'gst_rate' => 5.00,
                'status' => 'Approved',
                'is_new' => true,
                'created_by' => $masterSeller2->id,
                'description' => '100% pure, raw, and unprocessed honey sourced directly from the pristine forests of the Himalayas.',
                'product_specification' => 'Weight: 500g, Grade: A, Shelf Life: 24 Months',
                'featured_image' => 'https://images.unsplash.com/photo-1587049352846-4a222e784d38?w=500',
                'gallery' => [],
                'hsn_code' => '040900',
                'unit_of_measure' => 'pcs',
                'net_quantity' => 1,
                'case_pack_quantity' => 1,
                'country_of_origin' => 'India',
                'storage_instructions' => 'Store in a cool, dry place. Do not refrigerate.',
                'shelf_life' => '24 Months',
                'length' => 8.0,
                'width' => 8.0,
                'height' => 15.0,
                'dimension_unit' => 'cm',
                'weight' => 0.5,
                'weight_unit' => 'kg'
            ],
            [
                'product_name' => 'Organic Cold Pressed Mustard Oil',
                'barcode' => '890123456804',
                'seller_id' => $masterSeller2->id,
                'brand_id' => $brandHimalayan->id,
                'category_id' => $catOrganic->id,
                'sub_category_id' => $subOils->id,
                'child_category_id' => $childMustardOil->id,
                'mrp' => 299.00,
                'seller_price_incl_gst' => 199.00,
                'gst_rate' => 5.00,
                'status' => 'Approved',
                'is_new' => false,
                'created_by' => $masterSeller2->id,
                'description' => 'Traditional wood-pressed (Kachi Ghani) mustard oil, pure, healthy, and containing natural essential nutrients.',
                'product_specification' => 'Volume: 1 Liter, Type: Cold Pressed, Pack Type: Bottle',
                'featured_image' => 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=500',
                'gallery' => [],
                'hsn_code' => '151411',
                'unit_of_measure' => 'pcs',
                'net_quantity' => 1,
                'case_pack_quantity' => 1,
                'country_of_origin' => 'India',
                'storage_instructions' => 'Store in a cool, dry place away from direct sunlight.',
                'shelf_life' => '12 Months',
                'length' => 10.0,
                'width' => 10.0,
                'height' => 30.0,
                'dimension_unit' => 'cm',
                'weight' => 1.0,
                'weight_unit' => 'kg'
            ]
        ];

        foreach ($productsData as $prodData) {
            // Clean up existing to avoid barcode conflict
            Product::where('barcode', $prodData['barcode'])->delete();

            // Calculate pricing
            $pricing = PricingService::calculate(
                (float) $prodData['seller_price_incl_gst'],
                (float) $prodData['gst_rate'],
                (float) $prodData['mrp']
            );

            // Merge pricing into product data
            $product = Product::create(array_merge($prodData, $pricing));

            // Assign to appropriate sub-seller
            $subSellerId = ($prodData['seller_id'] === $masterSeller1->id) ? $subSeller1a->id : $subSeller2a->id;

            // Remove existing sub seller assignment to prevent duplicate errors
            SubSellerProduct::where([
                'sub_seller_id' => $subSellerId,
                'product_id' => $product->id
            ])->delete();

            $subSellerProd = SubSellerProduct::create([
                'sub_seller_id' => $subSellerId,
                'product_id' => $product->id,
                'final_price' => $product->seller_price_incl_gst,
                'status' => 'Active',
            ]);

            // Automatically recalculate pricing fields on the assignment table
            $subSellerProd->recalculatePricing();
        }
    }
}
