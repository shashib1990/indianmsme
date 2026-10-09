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
        if (Schema::hasTable('seller_profiles') && !Schema::hasColumn('seller_profiles', 'alternate_email')) {
            Schema::table('seller_profiles', function (Blueprint $table) {
                $table->string('alternate_email')->nullable()->after('alternate_mobile');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('seller_profiles') && Schema::hasColumn('seller_profiles', 'alternate_email')) {
            Schema::table('seller_profiles', function (Blueprint $table) {
                $table->dropColumn('alternate_email');
            });
        }
    }
};
