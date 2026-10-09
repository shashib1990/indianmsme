<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ddc_inventories', function (Blueprint $table) {
            $table->integer('sold_quantity')->default(0)->after('quantity');
            $table->integer('returned_quantity')->default(0)->after('sold_quantity');
        });
    }

    public function down(): void
    {
        Schema::table('ddc_inventories', function (Blueprint $table) {
            $table->dropColumn(['sold_quantity', 'returned_quantity']);
        });
    }
};
