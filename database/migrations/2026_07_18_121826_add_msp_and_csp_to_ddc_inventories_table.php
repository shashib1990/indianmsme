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
            $table->decimal('msp', 12, 2)->nullable()->after('purchase_price');
            $table->decimal('csp', 12, 2)->nullable()->after('msp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ddc_inventories', function (Blueprint $table) {
            $table->dropColumn(['msp', 'csp']);
        });
    }
};
