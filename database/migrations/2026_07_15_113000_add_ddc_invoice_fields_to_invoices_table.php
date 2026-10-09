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
            $table->string('ddc_invoice_number')->nullable()->after('invoice_file');
            $table->string('ddc_invoice_file')->nullable()->after('ddc_invoice_number');
            $table->date('ddc_invoice_date')->nullable()->after('ddc_invoice_file');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['ddc_invoice_number', 'ddc_invoice_file', 'ddc_invoice_date']);
        });
    }
};
