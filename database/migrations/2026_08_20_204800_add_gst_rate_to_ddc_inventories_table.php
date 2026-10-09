<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('ddc_inventories') && !Schema::hasColumn('ddc_inventories', 'gst_rate')) {
            Schema::table('ddc_inventories', function (Blueprint $table) {
                $table->decimal('gst_rate', 5, 2)->default(0.00)->after('csp_margin');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ddc_inventories') && Schema::hasColumn('ddc_inventories', 'gst_rate')) {
            Schema::table('ddc_inventories', function (Blueprint $table) {
                $table->dropColumn('gst_rate');
            });
        }
    }
};
