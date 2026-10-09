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
        if (Schema::hasTable('ddc_inventories') && !Schema::hasColumn('ddc_inventories', 'reserved_quantity')) {
            Schema::table('ddc_inventories', function (Blueprint $table) {
                $table->integer('reserved_quantity')->default(0)->after('quantity');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ddc_inventories') && Schema::hasColumn('ddc_inventories', 'reserved_quantity')) {
            Schema::table('ddc_inventories', function (Blueprint $table) {
                $table->dropColumn('reserved_quantity');
            });
        }
    }
};
