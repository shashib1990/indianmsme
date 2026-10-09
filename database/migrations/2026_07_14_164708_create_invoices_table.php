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
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('purchase_order_id');
            $table->string('invoice_number');
            $table->string('financial_year', 10);
            $table->unsignedInteger('serial_number');
            $table->date('invoice_date');
            
            // Transporter details
            $table->string('transporter_name')->nullable();
            $table->string('vehicle_number')->nullable();
            $table->string('transporter_gst')->nullable();
            $table->string('eway_bill_number')->nullable();

            $table->timestamps();

            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoices');
    }
};
