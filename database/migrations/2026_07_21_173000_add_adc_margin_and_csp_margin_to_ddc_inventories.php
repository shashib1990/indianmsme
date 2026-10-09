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
        Schema::table('ddc_inventories', function (Blueprint $table) {
            if (!Schema::hasColumn('ddc_inventories', 'adc_margin')) {
                $table->decimal('adc_margin', 8, 2)->nullable()->after('margin');
            }
            if (!Schema::hasColumn('ddc_inventories', 'csp_margin')) {
                $table->decimal('csp_margin', 8, 2)->nullable()->after('adc_margin');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ddc_inventories', function (Blueprint $table) {
            if (Schema::hasColumn('ddc_inventories', 'adc_margin')) {
                $table->dropColumn('adc_margin');
            }
            if (Schema::hasColumn('ddc_inventories', 'csp_margin')) {
                $table->dropColumn('csp_margin');
            }
        });
    }
};
