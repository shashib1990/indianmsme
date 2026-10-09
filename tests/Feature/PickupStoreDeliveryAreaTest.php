<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Vendor;
use App\Models\PickupStore;
use App\Models\State;
use App\Models\District;
use App\Models\PickupStoreDeliveryArea;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

class PickupStoreDeliveryAreaTest extends TestCase
{
    use RefreshDatabase;

    private Vendor $vendor;
    private PickupStore $store;
    private State $state;
    private District $district;

    protected function setUp(): void
    {
        parent::setUp();

        // 1. Create a Vendor
        $this->vendor = Vendor::create([
            'name' => 'Test Vendor',
            'email' => 'vendor@test.com',
            'password' => bcrypt('password'),
            'status' => '1',
            'allow_own_products' => 'Yes'
        ]);

        // 2. Create State & District
        $this->state = State::create([
            'name' => 'Uttar Pradesh',
            'code' => 'UP',
            'status' => '1'
        ]);

        $this->district = District::create([
            'state_id' => $this->state->id,
            'name' => 'Noida',
            'status' => '1'
        ]);

        // 3. Create Pickup Store
        $this->store = PickupStore::create([
            'owner_type' => 'Seller',
            'owner_id' => $this->vendor->id,
            'pickup_store_code' => 'PSC000001',
            'pickup_store_name' => 'Noida Central Store',
            'contact_person' => 'John Doe',
            'mobile' => '9876543210',
            'email' => 'noida@store.com',
            'address' => 'Sector 62, Noida',
            'state_id' => $this->state->id,
            'district_id' => $this->district->id,
            'pincode' => '201301',
            'status' => 'Active',
            'can_receive_payment_and_dispatch' => false
        ]);
    }

    public function test_vendor_can_view_delivery_areas_page(): void
    {
        $response = $this->actingAs($this->vendor, 'vendor')
            ->get(route('vendor.pickup-stores.delivery-areas.index', $this->store->id));

        $response->assertStatus(200);
        $response->assertViewIs('vendor.pickup-stores.delivery-areas');
        $response->assertSee('Noida Central Store');
        $response->assertSee('Active Delivery Coverage Type');
    }

    public function test_vendor_can_update_active_coverage_type(): void
    {
        // First add a pan_india record
        PickupStoreDeliveryArea::create([
            'pickup_store_id' => $this->store->id,
            'coverage_type' => 'pan_india',
            'status' => 'Active'
        ]);

        $response = $this->actingAs($this->vendor, 'vendor')
            ->post(route('vendor.pickup-stores.delivery-areas.update-coverage', $this->store->id), [
                'coverage_type' => 'state'
            ]);

        $response->assertRedirect();
        
        // Assert database has inactive coverage configuration
        $this->assertDatabaseHas('pickup_store_delivery_areas', [
            'pickup_store_id' => $this->store->id,
            'coverage_type' => 'pan_india',
            'status' => 'Inactive'
        ]);
    }

    public function test_vendor_can_add_delivery_area_state(): void
    {
        $this->store->update(['delivery_coverage_type' => 'state']);

        $response = $this->actingAs($this->vendor, 'vendor')
            ->post(route('vendor.pickup-stores.delivery-areas.store', $this->store->id), [
                'coverage_type' => 'state',
                'state_id' => $this->state->id
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pickup_store_delivery_areas', [
            'pickup_store_id' => $this->store->id,
            'coverage_type' => 'state',
            'state_id' => $this->state->id,
            'status' => 'Active'
        ]);
    }

    public function test_vendor_cannot_add_duplicate_delivery_state(): void
    {
        $this->store->update(['delivery_coverage_type' => 'state']);

        // First add state
        PickupStoreDeliveryArea::create([
            'pickup_store_id' => $this->store->id,
            'coverage_type' => 'state',
            'state_id' => $this->state->id,
            'status' => 'Active'
        ]);

        // Attempt duplicate addition
        $response = $this->actingAs($this->vendor, 'vendor')
            ->post(route('vendor.pickup-stores.delivery-areas.store', $this->store->id), [
                'coverage_type' => 'state',
                'state_id' => $this->state->id
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error', 'This State is already active for this store.');
    }

    public function test_vendor_can_add_delivery_area_district(): void
    {
        $this->store->update(['delivery_coverage_type' => 'district']);

        $response = $this->actingAs($this->vendor, 'vendor')
            ->post(route('vendor.pickup-stores.delivery-areas.store', $this->store->id), [
                'coverage_type' => 'district',
                'state_id' => $this->state->id,
                'district_id' => $this->district->id
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pickup_store_delivery_areas', [
            'pickup_store_id' => $this->store->id,
            'coverage_type' => 'district',
            'district_id' => $this->district->id,
            'status' => 'Active'
        ]);
    }

    public function test_vendor_can_add_delivery_area_pincode(): void
    {
        $this->store->update(['delivery_coverage_type' => 'pincode']);

        $response = $this->actingAs($this->vendor, 'vendor')
            ->post(route('vendor.pickup-stores.delivery-areas.store', $this->store->id), [
                'coverage_type' => 'pincode',
                'pincode' => '110001'
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pickup_store_delivery_areas', [
            'pickup_store_id' => $this->store->id,
            'coverage_type' => 'pincode',
            'pincode' => '110001',
            'status' => 'Active'
        ]);
    }

    public function test_vendor_cannot_add_invalid_pincode(): void
    {
        $this->store->update(['delivery_coverage_type' => 'pincode']);

        $response = $this->actingAs($this->vendor, 'vendor')
            ->from(route('vendor.pickup-stores.delivery-areas.index', $this->store->id))
            ->post(route('vendor.pickup-stores.delivery-areas.store', $this->store->id), [
                'coverage_type' => 'pincode',
                'pincode' => '1100' // Invalid: not 6 digits
            ]);

        $response->assertRedirect(route('vendor.pickup-stores.delivery-areas.index', $this->store->id));
        $response->assertSessionHasErrors(['pincode']);
    }

    public function test_vendor_can_toggle_delivery_area_status(): void
    {
        $this->store->update(['delivery_coverage_type' => 'state']);

        $area = PickupStoreDeliveryArea::create([
            'pickup_store_id' => $this->store->id,
            'coverage_type' => 'state',
            'state_id' => $this->state->id,
            'status' => 'Active'
        ]);

        $response = $this->actingAs($this->vendor, 'vendor')
            ->patch(route('vendor.pickup-stores.delivery-areas.status', [$this->store->id, $area->id]), [
                'status' => 'Inactive'
            ]);

        $response->assertJson(['success' => true, 'status' => 'Inactive']);
        $this->assertEquals('Inactive', $area->refresh()->status);
    }

    public function test_vendor_can_delete_delivery_area(): void
    {
        $this->store->update(['delivery_coverage_type' => 'state']);

        $area = PickupStoreDeliveryArea::create([
            'pickup_store_id' => $this->store->id,
            'coverage_type' => 'state',
            'state_id' => $this->state->id,
            'status' => 'Active'
        ]);

        $response = $this->actingAs($this->vendor, 'vendor')
            ->delete(route('vendor.pickup-stores.delivery-areas.destroy', [$this->store->id, $area->id]));

        $response->assertRedirect();
        $this->assertDatabaseMissing('pickup_store_delivery_areas', ['id' => $area->id]);
    }

    public function test_vendor_can_bulk_import_pincodes(): void
    {
        $this->store->update(['delivery_coverage_type' => 'pincode']);

        $csvContent = "Pincode\n110001\n110002\n110003\n110004\n";
        $file = UploadedFile::fake()->createWithContent('pincodes.csv', $csvContent);

        $response = $this->actingAs($this->vendor, 'vendor')
            ->post(route('vendor.pickup-stores.delivery-areas.bulk-import', $this->store->id), [
                'file' => $file
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pickup_store_delivery_areas', [
            'pickup_store_id' => $this->store->id,
            'coverage_type' => 'pincode',
            'pincode' => '110001'
        ]);
        $this->assertDatabaseHas('pickup_store_delivery_areas', [
            'pickup_store_id' => $this->store->id,
            'coverage_type' => 'pincode',
            'pincode' => '110004'
        ]);
    }

    public function test_vendor_can_bulk_import_districts(): void
    {
        $this->store->update(['delivery_coverage_type' => 'district']);

        $csvContent = "District Name,State Name\nNoida,Uttar Pradesh\n";
        $file = UploadedFile::fake()->createWithContent('districts.csv', $csvContent);

        $response = $this->actingAs($this->vendor, 'vendor')
            ->post(route('vendor.pickup-stores.delivery-areas.bulk-import', $this->store->id), [
                'file' => $file
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('pickup_store_delivery_areas', [
            'pickup_store_id' => $this->store->id,
            'coverage_type' => 'district',
            'district_id' => $this->district->id,
            'status' => 'Active'
        ]);
    }

    public function test_vendor_can_bulk_import_states(): void
    {
        $this->store->update(['delivery_coverage_type' => 'state']);

        $csvContent = "State Name,State Code\nUttar Pradesh,UP\n";
        $file = UploadedFile::fake()->createWithContent('states.csv', $csvContent);

        $response = $this->actingAs($this->vendor, 'vendor')
            ->post(route('vendor.pickup-stores.delivery-areas.bulk-import', $this->store->id), [
                'file' => $file
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertDatabaseHas('pickup_store_delivery_areas', [
            'pickup_store_id' => $this->store->id,
            'coverage_type' => 'state',
            'state_id' => $this->state->id,
            'status' => 'Active'
        ]);
    }

    public function test_vendor_can_download_sample_template(): void
    {
        $response = $this->actingAs($this->vendor, 'vendor')
            ->get(route('vendor.pickup-stores.delivery-areas.sample', ['id' => $this->store->id, 'type' => 'district']));

        $response->assertStatus(200);
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('District Name,State Name', $response->getContent());
    }
}
