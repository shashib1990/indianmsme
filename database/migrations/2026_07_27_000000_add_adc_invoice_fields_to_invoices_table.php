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
        Schema::table('invoices', function (Blueprint $table) {
            if (!Schema::hasColumn('invoices', 'adc_invoice_number')) {
                $table->string('adc_invoice_number')->nullable()->after('ddc_invoice_date');
            }
            if (!Schema::hasColumn('invoices', 'adc_invoice_date')) {
                $table->date('adc_invoice_date')->nullable()->after('adc_invoice_number');
            }
            if (!Schema::hasColumn('invoices', 'adc_invoice_file')) {
                $table->string('adc_invoice_file')->nullable()->after('adc_invoice_date');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $colsToDrop = [];
            if (Schema::hasColumn('invoices', 'adc_invoice_file')) {
                $colsToDrop[] = 'adc_invoice_file';
            }
            if (Schema::hasColumn('invoices', 'adc_invoice_date')) {
                $colsToDrop[] = 'adc_invoice_date';
            }
            if (Schema::hasColumn('invoices', 'adc_invoice_number')) {
                $colsToDrop[] = 'adc_invoice_number';
            }
            if (!empty($colsToDrop)) {
                $table->dropColumn($colsToDrop);
            }
        });
    }
};
