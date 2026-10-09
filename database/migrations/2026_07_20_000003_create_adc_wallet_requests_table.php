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
        Schema::create('adc_wallet_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('adc_id');
            $table->string('pincode', 10)->nullable();
            $table->unsignedBigInteger('assigned_ddc_id')->nullable();
            $table->enum('request_type', ['PHYSICAL', 'VIRTUAL'])->default('VIRTUAL');
            $table->decimal('amount', 12, 2);
            $table->string('utr_number')->unique();
            $table->string('payment_receipt');
            $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING');
            $table->text('remarks')->nullable();
            $table->text('admin_remarks')->nullable();
            $table->unsignedBigInteger('processed_by')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->foreign('adc_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_ddc_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('processed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adc_wallet_requests');
    }
};
