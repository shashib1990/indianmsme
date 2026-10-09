<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use App\Models\Admin;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MasterPermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Clear existing permissions to ensure a clean state (as requested by user)
        // We use truncate to bypass soft deletes and hard-reset the table
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('admin_role_permissions')->truncate();
        DB::table('admin_permissions')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // 1. Define clean permissions based on current Admin Panel structure
        $permissions = [
            // Module: Departments
            ['name' => 'View Departments', 'slug' => 'departments.view', 'module' => 'Departments', 'type' => 'read'],
            ['name' => 'Create Departments', 'slug' => 'departments.create', 'module' => 'Departments', 'type' => 'write'],
            ['name' => 'Edit Departments', 'slug' => 'departments.edit', 'module' => 'Departments', 'type' => 'write'],
            ['name' => 'Delete Departments', 'slug' => 'departments.delete', 'module' => 'Departments', 'type' => 'write'],

            // Module: Staff Management
            ['name' => 'View Staff', 'slug' => 'staff.view', 'module' => 'Staff Management', 'type' => 'read'],
            ['name' => 'Create Staff', 'slug' => 'staff.create', 'module' => 'Staff Management', 'type' => 'write'],
            ['name' => 'Edit Staff', 'slug' => 'staff.edit', 'module' => 'Staff Management', 'type' => 'write'],
            ['name' => 'Delete Staff', 'slug' => 'staff.delete', 'module' => 'Staff Management', 'type' => 'write'],

            // Module: Access Control (Permissions)
            ['name' => 'View Permissions', 'slug' => 'permissions.view', 'module' => 'Access Control', 'type' => 'read'],
            ['name' => 'Manage Permissions', 'slug' => 'permissions.manage', 'module' => 'Access Control', 'type' => 'write'],

            // Module: External Users (DDC, ADC, Vendors)
            ['name' => 'View External Users', 'slug' => 'users.view', 'module' => 'External Users', 'type' => 'read'],
            ['name' => 'Manage External Users', 'slug' => 'users.manage', 'module' => 'External Users', 'type' => 'write'],
        ];

        // 2. Insert Permissions using low-level DB to bypass any model logic/soft deletes
        foreach ($permissions as $perm) {
            try {
                DB::table('admin_permissions')->updateOrInsert(
                    ['slug' => $perm['slug']],
                    array_merge($perm, ['created_at' => now(), 'updated_at' => now()])
                );
            } catch (\Exception $e) {
                // If updateOrInsert still fails, it's very likely a database-level weirdness
                // We'll log it and continue if possible, or throw a more descriptive error
                if (strpos($e->getMessage(), 'Duplicate entry') !== false) {
                    continue; // Skip duplicates if they somehow persist
                }
                throw $e;
            }
        }

        // 3. Ensure Super Admin role has all these permissions
        $adminRole = Role::where('slug', 'administrator')->first();
        if ($adminRole) {
            $adminRole->permissions()->sync(Permission::pluck('id'));
        }

        // 4. Ensure Super Admin user is linked to Administrator role if not already
        $superAdmin = Admin::where('email', 'admin@indianmsme.org')->first();
        if ($superAdmin && $adminRole) {
            $superAdmin->roles()->syncWithoutDetaching([$adminRole->id]);
        }
    }
}
