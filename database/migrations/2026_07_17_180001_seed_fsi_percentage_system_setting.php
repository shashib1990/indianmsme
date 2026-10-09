<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seed the default FSI percentage system setting.
     * FSI = Field Sales Incentive (applied when seller is NOT DDC subscribed).
     * Default: 0.50% of seller_accepted_amount (matches SSI default).
     */
    public function up(): void
    {
        // Only insert if not already present to avoid overwriting admin-configured values
        $exists = DB::table('system_settings')->where('key', 'fsi_percentage')->exists();
        if (!$exists) {
            DB::table('system_settings')->insert([
                'key'        => 'fsi_percentage',
                'value'      => '0.50',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('system_settings')->where('key', 'fsi_percentage')->delete();
    }
};
