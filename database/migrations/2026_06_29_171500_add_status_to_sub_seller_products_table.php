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
        if (!Schema::hasColumn('sub_seller_products', 'status')) {
            Schema::table('sub_seller_products', function (Blueprint $table) {
                $table->enum('status', ['Active', 'Inactive'])->default('Active')->after('product_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('sub_seller_products', 'status')) {
            Schema::table('sub_seller_products', function (Blueprint $table) {
                $table->dropColumn('status');
            });
        }
    }
};
