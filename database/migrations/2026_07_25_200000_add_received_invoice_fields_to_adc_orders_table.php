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
        Schema::table('adc_orders', function (Blueprint $table) {
            if (!Schema::hasColumn('adc_orders', 'received_invoice_number')) {
                $table->string('received_invoice_number')->nullable()->after('notes');
            }
            if (!Schema::hasColumn('adc_orders', 'received_invoice_date')) {
                $table->date('received_invoice_date')->nullable()->after('received_invoice_number');
            }
            if (!Schema::hasColumn('adc_orders', 'received_invoice_file')) {
                $table->string('received_invoice_file')->nullable()->after('received_invoice_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('adc_orders', function (Blueprint $table) {
            if (Schema::hasColumn('adc_orders', 'received_invoice_file')) {
                $table->dropColumn('received_invoice_file');
            }
            if (Schema::hasColumn('adc_orders', 'received_invoice_date')) {
                $table->dropColumn('received_invoice_date');
            }
            if (Schema::hasColumn('adc_orders', 'received_invoice_number')) {
                $table->dropColumn('received_invoice_number');
            }
        });
    }
};
