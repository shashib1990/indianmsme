<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('adc_debit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('debit_note_number')->unique();
            $table->unsignedBigInteger('adc_order_id');
            $table->unsignedBigInteger('adc_id');
            $table->enum('target_type', ['DDC', 'SELLER'])->default('DDC');
            $table->unsignedBigInteger('ddc_id')->nullable();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->enum('discrepancy_type', ['SHORTAGE', 'DAMAGE', 'EXCESS', 'MIXED']);
            $table->integer('order_qty');
            $table->integer('received_qty');
            $table->integer('shortage_qty')->default(0);
            $table->integer('damage_qty')->default(0);
            $table->integer('excess_qty')->default(0);
            $table->decimal('unit_price', 12, 2)->default(0.00);
            $table->decimal('total_discrepancy_amount', 12, 2)->default(0.00);
            $table->decimal('seller_amount', 12, 2)->default(0.00);
            $table->decimal('final_amount', 12, 2)->nullable();
            $table->json('proof_images')->nullable();
            $table->integer('seller_agreed_qty')->nullable();
            $table->string('seller_invoice_file')->nullable();
            $table->enum('status', ['PENDING', 'PENDING_SELLER_APPROVAL', 'SELLER_APPROVED', 'SELLER_REJECTED', 'ADPL_VERIFIED', 'SETTLED', 'CANCELLED'])->default('PENDING');
            $table->text('seller_remarks')->nullable();
            $table->text('adpl_remarks')->nullable();
            $table->timestamps();

            $table->foreign('adc_order_id')->references('id')->on('adc_orders')->onDelete('cascade');
            $table->foreign('adc_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('ddc_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('seller_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('adc_debit_note_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('adc_debit_note_id');
            $table->unsignedBigInteger('adc_order_item_id');
            $table->string('product_name');
            $table->integer('order_qty');
            $table->integer('received_qty');
            $table->integer('shortage_qty')->default(0);
            $table->integer('damage_qty')->default(0);
            $table->integer('excess_qty')->default(0);
            $table->decimal('unit_price', 12, 2)->default(0.00);
            $table->decimal('item_discrepancy_amount', 12, 2)->default(0.00);
            $table->integer('seller_agreed_qty')->nullable();
            $table->decimal('seller_agreed_amount', 12, 2)->nullable();
            $table->timestamps();

            $table->foreign('adc_debit_note_id')->references('id')->on('adc_debit_notes')->onDelete('cascade');
            $table->foreign('adc_order_item_id')->references('id')->on('adc_order_items')->onDelete('cascade');
        });

        Schema::create('adc_credit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('credit_note_number')->unique();
            $table->unsignedBigInteger('adc_debit_note_id');
            $table->unsignedBigInteger('adc_order_id');
            $table->unsignedBigInteger('adc_id');
            $table->enum('target_type', ['DDC', 'SELLER'])->default('DDC');
            $table->unsignedBigInteger('ddc_id')->nullable();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->enum('type', ['SHORTAGE_DAMAGE_CLAIM', 'EXCESS_STOCK_BILL']);
            $table->enum('status', ['GENERATED', 'SETTLED'])->default('GENERATED');
            $table->timestamps();

            $table->foreign('adc_debit_note_id')->references('id')->on('adc_debit_notes')->onDelete('cascade');
            $table->foreign('adc_order_id')->references('id')->on('adc_orders')->onDelete('cascade');
            $table->foreign('adc_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('ddc_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('seller_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('adc_excess_invoices', function (Blueprint $table) {
            $table->id();
            $table->string('invoice_number')->unique();
            $table->unsignedBigInteger('adc_order_id');
            $table->unsignedBigInteger('adc_id');
            $table->enum('target_type', ['DDC', 'SELLER'])->default('DDC');
            $table->unsignedBigInteger('ddc_id')->nullable();
            $table->unsignedBigInteger('seller_id')->nullable();
            $table->integer('total_excess_qty')->default(0);
            $table->decimal('total_excess_amount', 12, 2)->default(0.00);
            $table->json('proof_images')->nullable();
            $table->enum('status', ['PENDING', 'PAID', 'SETTLED', 'CANCELLED'])->default('PENDING');
            $table->text('remarks')->nullable();
            $table->timestamps();

            $table->foreign('adc_order_id')->references('id')->on('adc_orders')->onDelete('cascade');
            $table->foreign('adc_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('ddc_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('seller_id')->references('id')->on('users')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adc_excess_invoices');
        Schema::dropIfExists('adc_credit_notes');
        Schema::dropIfExists('adc_debit_note_items');
        Schema::dropIfExists('adc_debit_notes');
    }
};
