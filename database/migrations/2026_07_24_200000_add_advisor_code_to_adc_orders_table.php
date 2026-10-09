<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('adc_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('adc_orders', 'advisor_code')) {
                $table->string('advisor_code')->nullable()->after('adc_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('adc_orders', function (Blueprint $table) {
            if (Schema::hasColumn('adc_orders', 'advisor_code')) {
                $table->dropColumn('advisor_code');
            }
        });
    }
};
