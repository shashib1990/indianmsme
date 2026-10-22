<?php

namespace Tests\Feature;

use App\Models\AdcInventory;
use App\Models\AdcProfile;
use App\Models\Category;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdcDetailsApiTest extends TestCase
{
    use DatabaseTransactions;

    public function test_fails_when_no_identifier_provided(): void
    {
        $response = $this->postJson('/api/adc/details', []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'Please provide at least one identifier parameter: adc_id, adc_code, mobile, or email.',
            ]);
    }

    public function test_returns_404_for_non_existent_adc(): void
    {
        $response = $this->postJson('/api/adc/details', [
            'adc_code' => 'NON_EXISTENT_ADC_CODE_999999',
        ]);

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'ADC not found with provided identifier.',
            ]);
    }

    public function test_can_fetch_complete_adc_data_with_products_and_summary(): void
    {
        // 1. Create test DDC user
        $ddcUser = User::create([
            'name'     => 'Test DDC Hub',
            'email'    => 'test_ddc_' . uniqid() . '@example.com',
            'mobile'   => '8888' . rand(100000, 999999),
            'role'     => 'ddc',
            'status'   => '1',
            'password' => bcrypt('password123'),
        ]);
        $ddcProfile = \App\Models\DdcProfile::create([
            'user_id'  => $ddcUser->id,
            'ddc_code' => 'DDC-TEST-' . rand(1000, 9999),
        ]);

        // 2. Create test ADC user
        $adcCode = 'ADC-TEST-' . rand(1000, 9999);
        $adcUser = User::create([
            'name'         => 'Test Canteen Owner',
            'email'        => 'test_adc_' . uniqid() . '@example.com',
            'mobile'       => '9999' . rand(100000, 999999),
            'role'         => 'adc',
            'status'       => '1',
            'company_name' => 'Apex MSME Canteen',
            'trade_name'   => 'Apex Store',
            'adc_code'     => $adcCode,
            'address'      => '123 Main Bazaar',
            'district'     => 'Varanasi',
            'state'        => 'Uttar Pradesh',
            'pincode'      => '221001',
            'password'     => bcrypt('password123'),
        ]);

        $adcProfile = $adcUser->adcProfile;
        $adcProfile->assigned_ddc_id = $ddcUser->id;
        $adcProfile->save();

        // 3. Create Seller, Category and Product
        $seller = User::create([
            'name'     => 'Test Seller',
            'email'    => 'test_seller_' . uniqid() . '@example.com',
            'mobile'   => '9998' . rand(100000, 999999),
            'role'     => 'seller',
            'status'   => '1',
            'password' => bcrypt('password123'),
        ]);

        $category = Category::create([
            'name'   => 'Groceries Test',
            'slug'   => 'groceries-test-' . uniqid(),
            'status' => '1',
        ]);

        $brand = \App\Models\Brand::create([
            'name'   => 'Organic Best',
            'slug'   => 'organic-best-' . uniqid(),
            'status' => '1',
        ]);

        $product = Product::create([
            'seller_id'             => $seller->id,
            'product_name'          => 'Organic Wheat Flour 5kg',
            'product_code'          => 'PROD-WHEAT-' . uniqid(),
            'barcode'               => '8901234567890',
            'description'           => 'Fresh organic whole wheat flour',
            'hsn_code'              => '11010000',
            'category_id'           => $category->id,
            'brand_id'              => $brand->id,
            'country_of_origin'     => 'India',
            'created_by'            => $seller->id,
            'mrp'                   => 250.00,
            'seller_price_incl_gst' => 200.00,
            'gst_rate'              => 5.00,
            'status'                => 'Approved',
            'unit_of_measure'       => 'Bag',
        ]);

        // 4. Create Inventory record
        AdcInventory::create([
            'adc_id'            => $adcUser->id,
            'inventory_source'  => 'MSME',
            'product_id'        => $product->id,
            'product_name'      => $product->product_name,
            'product_code'      => $product->product_code,
            'barcode'           => $product->barcode,
            'unit_of_measure'   => 'Bag',
            'quantity'          => 20,
            'reserved_quantity' => 2,
            'sold_quantity'     => 5,
            'mrp'               => 250.00,
            'purchase_price'    => 200.00,
            'landing_cost'      => 205.00,
            'msp'               => 220.00,
            'adc_price'         => 210.00,
            'csp'               => 240.00,
            'gst_rate'          => 5.00,
            'pos_status'        => 'Active',
        ]);

        // 5. Call API with adc_id
        $response = $this->postJson('/api/adc/details', [
            'adc_id' => $adcUser->id,
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'adc' => [
                        'id'                => $adcUser->id,
                        'canteen_id'        => $adcUser->id,
                        'name'              => 'Test Canteen Owner',
                        'canteen_name'      => 'Apex MSME Canteen',
                        'adc_code'          => $adcProfile->adc_code,
                        'canteen_code'      => $adcProfile->adc_code,
                        'assigned_ddc_id'   => $ddcUser->id,
                        'assigned_ddc_code' => $ddcProfile->ddc_code,
                        'ddc_code'          => $ddcProfile->ddc_code,
                    ],
                    'summary' => [
                        'total_products_count'     => 1,
                        'in_stock_products_count'  => 1,
                        'total_quantity'           => 20,
                        'total_available_quantity' => 18,
                    ],
                ],
            ]);

        // Assert products list structure
        $json = $response->json();
        $this->assertNotEmpty($json['data']['products']);
        $firstItem = $json['data']['products'][0];
        $this->assertEquals('Organic Wheat Flour 5kg', $firstItem['product_name']);
        $this->assertEquals('Groceries Test', $firstItem['category']);
        $this->assertEquals(240.00, $firstItem['pricing']['selling_price']);
        $this->assertEquals(18.0, $firstItem['stock']['available_quantity']);
        $this->assertTrue($firstItem['stock']['is_in_stock']);

        // 6. Test with adc_code identifier
        $responseByCode = $this->postJson('/api/adc/details', [
            'adc_code' => $adcProfile->adc_code,
        ]);
        $responseByCode->assertStatus(200);
        $this->assertEquals($adcUser->id, $responseByCode->json('data.adc.id'));
    }
}
