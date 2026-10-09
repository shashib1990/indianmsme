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
        Schema::table('seller_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('seller_profiles', 'seller_type')) {
                $table->string('seller_type')->nullable()->change();
            } else {
                $table->string('seller_type')->nullable()->after('company_type');
            }

            if (!Schema::hasColumn('seller_profiles', 'brand_name')) {
                $table->text('brand_name')->nullable()->after('seller_type');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('seller_profiles', 'brand_name')) {
                $table->dropColumn('brand_name');
            }
        });
    }
};
