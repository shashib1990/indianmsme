<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\DDC;
use App\Models\Product;
use App\Models\SubSellerProduct;
use Illuminate\Support\Facades\Hash;

class PurchaseOrderTestDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Get State & District
        $state = \App\Models\State::firstOrCreate(
            ['code' => 'MH'],
            ['name' => 'Maharashtra', 'status' => '1']
        );

        $district = \App\Models\District::firstOrCreate(
            ['state_id' => $state->id, 'name' => 'Mumbai'],
            ['status' => '1']
        );

        // 2. Set DDC to Physical DDC in Mumbai
        $ddc = DDC::where('email', 'ddc@indianmsme.org')->first();
        if ($ddc) {
            $ddc->type = 'physical';
            $ddc->save();

            $ddcProfile = $ddc->profile;
            if ($ddcProfile) {
                $ddcProfile->state_id = $state->id;
                $ddcProfile->district_id = $district->id;
                $ddcProfile->state = $state->name;
                $ddcProfile->district = $district->name;
                $ddcProfile->wallet_balance = 250000.00; // Give high wallet balance for testing
                $ddcProfile->save();
            }
        }

        // 3. Set Vendor to Mumbai
        $vendor = User::where('email', 'vendor@indianmsme.org')->first();
        if ($vendor) {
            $vendorProfile = $vendor->sellerProfile;
            if ($vendorProfile) {
                $vendorProfile->state_id = $state->id;
                $vendorProfile->district_id = $district->id;
                $vendorProfile->state = $state->name;
                $vendorProfile->district = $district->name;
                $vendorProfile->save();
            }
        }

        // 4. Set Sub-seller to Mumbai
        $subseller = User::where('email', 'subseller@indianmsme.org')->first();
        if ($subseller) {
            $subsellerProfile = $subseller->sellerProfile;
            if ($subsellerProfile) {
                $subsellerProfile->state_id = $state->id;
                $subsellerProfile->district_id = $district->id;
                $subsellerProfile->state = $state->name;
                $subsellerProfile->district = $district->name;
                $subsellerProfile->parent_id = $vendor->id;
                $subsellerProfile->save();
            }
        }

        // 5. Ensure at least one approved product exists for Vendor
        $product = Product::where('seller_id', $vendor->id)->where('status', 'Approved')->first();
        if (!$product) {
            $category = \App\Models\Category::first();
            $brand = \App\Models\Brand::first();
            $pricing = \App\Services\PricingService::calculate(1500.00, 18.00, 2500.00, 10.00, 5.00, 3.00);
            
            $product = Product::create(array_merge([
                'product_name' => 'TATA Premium Mixer Grinder',
                'barcode' => '890123456799',
                'seller_id' => $vendor->id,
                'brand_id' => $brand ? $brand->id : null,
                'category_id' => $category ? $category->id : null,
                'mrp' => 2500.00,
                'seller_price_incl_gst' => 1500.00,
                'gst_rate' => 18.00,
                'status' => 'Approved',
                'is_new' => false,
                'created_by' => $vendor->id,
                'description' => 'TATA Premium Mixer Grinder with 3 stainless steel jars.',
                'hsn_code' => '850940',
                'unit_of_measure' => 'pcs',
                'net_quantity' => 1,
            ], $pricing));
        }

        // 6. Assign product to Sub-seller
        if ($subseller && $product) {
            $subProduct = SubSellerProduct::where('sub_seller_id', $subseller->id)
                ->where('product_id', $product->id)
                ->first();

            if (!$subProduct) {
                $pricing = \App\Services\PricingService::calculate(1600.00, 18.00, 2500.00, 10.00, 5.00, 3.00);
                $fillableKeys = [
                    'base_cost', 'company_margin_percentage', 'company_margin_amount',
                    'admin_margin_percentage', 'admin_margin_amount', 'ddc_margin_percentage',
                    'ddc_margin_amount', 'price_before_gst', 'company_gst_amount',
                    'seller_gst_amount', 'adc_purchase_price', 'ddc_purchase_price', 'available_margin',
                    'mbp_percentage', 'mbp_amount', 'mbp_90_share', 'mbp_10_share',
                    'mbp_38_share', 'mbp_62_share', 'adc_final_margin'
                ];
                $filteredPricing = array_intersect_key($pricing, array_flip($fillableKeys));

                SubSellerProduct::create(array_merge([
                    'sub_seller_id' => $subseller->id,
                    'product_id' => $product->id,
                    'margin' => 100.00,
                    'final_price' => 1600.00,
                ], $filteredPricing));
            }
        }
    }
}
