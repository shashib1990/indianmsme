<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;

class ResetStaffRoles extends Command
{
    protected $signature = 'staff:reset-roles';
    protected $description = 'Remove roles and permissions from all existing non-super-admin staff members';

    public function handle()
    {
        $this->info('Resetting roles for staff members...');

        $staff = Admin::where('is_super_admin', '0')->get();

        foreach ($staff as $member) {
            $member->roles()->detach();
            $this->line("Reset roles for: {$member->name} ({$member->email})");
        }

        $this->info('All staff roles have been detached. Permissions are linked to roles, so they are effectively removed as well.');
        
        return Command::SUCCESS;
    }
}
