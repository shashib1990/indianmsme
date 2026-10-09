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
            try {
                $table->index(['ddc_id', 'mode'], 'ddc_pos_carts_ddc_mode_index');
            } catch (\Throwable $e) {}

            try {
                $table->dropUnique('ddc_pos_carts_ddc_id_mode_unique');
            } catch (\Throwable $e) {}
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ddc_pos_carts', function (Blueprint $table) {
            $table->unique(['ddc_id', 'mode']);
        });
    }
};
