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
            $table->dropUnique('pickup_stores_pickup_store_code_unique');
            $table->unique(['owner_id', 'pickup_store_code'], 'pickup_stores_owner_id_code_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pickup_stores', function (Blueprint $table) {
            $table->dropUnique('pickup_stores_owner_id_code_unique');
            $table->unique('pickup_store_code', 'pickup_stores_pickup_store_code_unique');
        });
    }
};
