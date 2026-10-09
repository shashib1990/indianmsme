<?php

namespace Database\Seeders;

use App\Models\Admin;
use App\Models\Vendor;
use App\Models\DDC;
use App\Models\ADC;
use App\Models\Role;
use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\Department;
use App\Models\AdminProfile;
use App\Models\AdcProfile;
use App\Models\DdcProfile;
use App\Models\SellerProfile;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Add a department first
        $dept = Department::create([
            'name' => 'Main IT Department',
            'is_active' => '1',
        ]);

        // Seed default states and districts for dropdowns
        $stateMaha = \App\Models\State::create(['name' => 'Maharashtra', 'code' => 'MH', 'status' => '1']);
        $stateDelhi = \App\Models\State::create(['name' => 'Delhi', 'code' => 'DL', 'status' => '1']);
        $stateUP = \App\Models\State::create(['name' => 'Uttar Pradesh', 'code' => 'UP', 'status' => '1']);

        $distMumbai = \App\Models\District::create(['state_id' => $stateMaha->id, 'name' => 'Mumbai', 'status' => '1']);
        $distPune = \App\Models\District::create(['state_id' => $stateMaha->id, 'name' => 'Pune', 'status' => '1']);
        $distNewDelhi = \App\Models\District::create(['state_id' => $stateDelhi->id, 'name' => 'New Delhi', 'status' => '1']);
        $distNoida = \App\Models\District::create(['state_id' => $stateUP->id, 'name' => 'Noida', 'status' => '1']);

        // Create Super Admin User
        $superAdmin = Admin::create([
            'name' => 'Super Administrator',
            'email' => 'admin@indianmsme.org',
            'password' => Hash::make('Admin@12345'),
            'mobile' => '+91-9000000000',
            'status' => '1',
        ]);

        $superAdmin->profile->update([
            'is_super_admin' => '1',
            'department_id' => $dept->id,
        ]);

        // Create permissions
        $permissionsData = [
            ['name' => 'View Users', 'slug' => 'users.read', 'module' => 'Users', 'submodule' => 'View Users', 'type' => 'read'],
            ['name' => 'Add Users', 'slug' => 'users.write', 'module' => 'Users', 'submodule' => 'Add Users', 'type' => 'write'],
            ['name' => 'Edit Users', 'slug' => 'users.edit', 'module' => 'Users', 'submodule' => 'Edit Users', 'type' => 'write'],
            ['name' => 'Delete Users', 'slug' => 'users.delete', 'module' => 'Users', 'submodule' => 'Delete Users', 'type' => 'write'],
            ['name' => 'View Roles', 'slug' => 'roles.read', 'module' => 'Roles & Permissions', 'submodule' => 'View Roles', 'type' => 'read'],
            ['name' => 'Manage Roles', 'slug' => 'roles.write', 'module' => 'Roles & Permissions', 'submodule' => 'Manage Roles', 'type' => 'write'],
            ['name' => 'View Vendors', 'slug' => 'vendors.read', 'module' => 'Vendors', 'submodule' => 'View Vendors', 'type' => 'read'],
            ['name' => 'Manage Vendors', 'slug' => 'vendors.write', 'module' => 'Vendors', 'submodule' => 'Manage Vendors', 'type' => 'write'],
            ['name' => 'View Districts', 'slug' => 'districts.read', 'module' => 'Districts', 'submodule' => 'View Districts', 'type' => 'read'],
            ['name' => 'Manage Districts', 'slug' => 'districts.write', 'module' => 'Districts', 'submodule' => 'Manage Districts', 'type' => 'write'],
        ];

        foreach ($permissionsData as $perm) {
            Permission::create($perm);
        }

        // Create default roles
        $adminRole = Role::create([
            'name' => 'Administrator',
            'slug' => 'administrator',
            'description' => 'Full administrative access',
        ]);

        $adminRole->permissions()->attach(Permission::all()->pluck('id'));
        $superAdmin->roles()->attach($adminRole);

        // Create sample Vendor/Seller
        $vendorUser = Vendor::create([
            'name' => 'Sample Vendor',
            'email' => 'vendor@indianmsme.org',
            'password' => Hash::make('Vendor@12345'),
            'mobile' => '+91-9111111111',
            'status' => '1',
        ]);

        $vendorUser->profile->update([
            'company_name' => 'Sample Enterprises Pvt. Ltd.',
            'gst_number' => '27AAAAA1111A1Z1',
            'seller_id' => SellerProfile::generateSellerId(),
        ]);

        // Create sample DDC
        $ddcUser = DDC::create([
            'name' => 'District Development Officer',
            'email' => 'ddc@indianmsme.org',
            'password' => Hash::make('Ddc@12345'),
            'mobile' => '+91-9222222222',
            'status' => '1',
        ]);

        $ddcUser->profile->update([
            'district' => 'Mumbai',
            'state' => 'Maharashtra',
            'state_id' => $stateMaha->id,
            'district_id' => $distMumbai->id,
            'department_id' => $dept->id,
            'wallet_balance' => 5000.00,
        ]);

        // Create sample ADC
        $adcUser = ADC::create([
            'name' => 'Additional District Collector',
            'email' => 'adc@indianmsme.org',
            'password' => Hash::make('Adc@12345'),
            'mobile' => '+91-9333333333',
            'status' => '1',
        ]);

        $adcUser->profile->update([
            'district' => 'Mumbai',
            'state' => 'Maharashtra',
            'designation' => 'ADC Operations',
            'department_id' => $dept->id,
            'wallet_balance' => 2000.00,
        ]);

        $this->call(DummySellersAndProductsSeeder::class);

        $this->command->info('✅ Database seeded successfully!');
        $this->command->table(
            ['Panel', 'Email', 'Password'],
            [
                ['Admin', 'admin@indianmsme.org', 'Admin@12345'],
                ['Vendor', 'vendor@indianmsme.org', 'Vendor@12345'],
                ['DDC', 'ddc@indianmsme.org', 'Ddc@12345'],
                ['ADC', 'adc@indianmsme.org', 'Adc@12345'],
            ]
        );
    }
}
