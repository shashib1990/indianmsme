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
        Schema::table('ddc_pos_carts', function (Blueprint $table) {
            if (!Schema::hasColumn('ddc_pos_carts', 'status')) {
                $table->string('status')->default('active')->after('mode');
            }
            if (!Schema::hasColumn('ddc_pos_carts', 'customer_name')) {
                $table->string('customer_name')->nullable()->after('customer_id');
            }
            if (!Schema::hasColumn('ddc_pos_carts', 'customer_mobile')) {
                $table->string('customer_mobile')->nullable()->after('customer_name');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ddc_pos_carts', function (Blueprint $table) {
            if (Schema::hasColumn('ddc_pos_carts', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('ddc_pos_carts', 'customer_name')) {
                $table->dropColumn('customer_name');
            }
            if (Schema::hasColumn('ddc_pos_carts', 'customer_mobile')) {
                $table->dropColumn('customer_mobile');
            }
        });
    }
};
