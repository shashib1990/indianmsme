<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('debit_note_items', function (Blueprint $table) {
            if (!Schema::hasColumn('debit_note_items', 'seller_unit_price')) {
                $table->decimal('seller_unit_price', 12, 2)->default(0.00)->after('unit_price');
            }
        });

        Schema::table('debit_notes', function (Blueprint $table) {
            if (!Schema::hasColumn('debit_notes', 'seller_amount')) {
                $table->decimal('seller_amount', 12, 2)->nullable()->after('total_discrepancy_amount');
            }
        });
    }

    public function down(): void
    {
        Schema::table('debit_note_items', function (Blueprint $table) {
            $table->dropColumn('seller_unit_price');
        });

        Schema::table('debit_notes', function (Blueprint $table) {
            $table->dropColumn('seller_amount');
        });
    }
};
