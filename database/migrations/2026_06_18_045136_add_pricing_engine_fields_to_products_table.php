<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('admin_margin_percentage', 5, 2)->nullable()->after('base_cost');
            $table->decimal('admin_margin_amount', 12, 2)->nullable()->after('admin_margin_percentage');
            $table->decimal('price_before_gst', 12, 2)->nullable()->after('ddc_margin_amount');
            $table->decimal('mbp_90_share', 12, 2)->nullable()->after('mbp_amount');
            $table->decimal('mbp_10_share', 12, 2)->nullable()->after('mbp_90_share');
            $table->decimal('mbp_38_share', 12, 2)->nullable()->after('mbp_10_share');
            $table->decimal('mbp_62_share', 12, 2)->nullable()->after('mbp_38_share');
        });

        // Add or update system settings
        $hasCompany = DB::table('system_settings')->where('key', 'company_margin_percentage')->first();
        if ($hasCompany) {
            DB::table('system_settings')->where('key', 'company_margin_percentage')->update([
                'key' => 'admin_margin_percentage',
                'value' => '8.00',
                'updated_at' => now(),
            ]);
        } else {
            DB::table('system_settings')->updateOrInsert(
                ['key' => 'admin_margin_percentage'],
                ['value' => '8.00', 'created_at' => now(), 'updated_at' => now()]
            );
        }

        DB::table('system_settings')->updateOrInsert(
            ['key' => 'ddc_margin_percentage'],
            ['value' => '5.00', 'updated_at' => now()]
        );

        DB::table('system_settings')->updateOrInsert(
            ['key' => 'mbp_percentage'],
            ['value' => '2.00', 'updated_at' => now()]
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn([
                'admin_margin_percentage',
                'admin_margin_amount',
                'price_before_gst',
                'mbp_90_share',
                'mbp_10_share',
                'mbp_38_share',
                'mbp_62_share',
            ]);
        });
    }
};
