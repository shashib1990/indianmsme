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
        Schema::table('pickup_stores', function (Blueprint $table) {
            $table->dropUnique('pickup_stores_mobile_unique');
            $table->dropUnique('pickup_stores_email_unique');
            $table->string('mobile')->nullable()->change();
            $table->string('email')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pickup_stores', function (Blueprint $table) {
            $table->unique('mobile', 'pickup_stores_mobile_unique');
            $table->unique('email', 'pickup_stores_email_unique');
            $table->string('mobile')->nullable(false)->change();
            $table->string('email')->nullable()->change();
        });
    }
};
