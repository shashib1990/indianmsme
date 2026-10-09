<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ddc_inventories', function (Blueprint $table) {
            $table->decimal('margin', 12, 2)->nullable()->default(null)->after('csp');
        });
    }

    public function down(): void
    {
        Schema::table('ddc_inventories', function (Blueprint $table) {
            $table->dropColumn('margin');
        });
    }
};
