<?php

namespace Tests\Feature;

use App\Models\Admin;
use App\Models\UnitOfMeasure;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class UnitOfMeasureCrudTest extends TestCase
{
    use DatabaseTransactions;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        config(['database.connections.mysql.database' => 'asm_msme']);
        \DB::purge('mysql');
        \DB::reconnect('mysql');

        $this->admin = Admin::first() ?? Admin::create([
            'name' => 'Admin User',
            'email' => 'admin_uom_' . uniqid() . '@example.com',
            'password' => bcrypt('password'),
            'role' => 'admin',
            'status' => '1',
            'force_change_password' => '0',
        ]);
    }

    public function test_admin_can_view_and_create_unit_of_measure(): void
    {
        $this->withoutExceptionHandling();
        $response = $this->actingAs($this->admin, 'admin')
            ->get(route('admin.units-of-measure.index'));

        $response->assertStatus(200);
        $response->assertSee('Units of Measure');

        $storeResponse = $this->actingAs($this->admin, 'admin')
            ->post(route('admin.units-of-measure.store'), [
                'name' => 'Custom Roll',
                'short_code' => 'roll',
                'sort_order' => 15,
                'status' => '1',
            ]);

        $storeResponse->assertRedirect(route('admin.units-of-measure.index'));
        $storeResponse->assertSessionHas('success');

        $this->assertDatabaseHas('unit_of_measures', [
            'name' => 'Custom Roll',
            'short_code' => 'roll',
        ]);

        $activeUnits = UnitOfMeasure::getUnitsList();
        $this->assertContains('Custom Roll', $activeUnits);
    }
}
