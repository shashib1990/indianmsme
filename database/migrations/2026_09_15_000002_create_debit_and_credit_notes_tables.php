<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('debit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('debit_note_number')->unique();
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('purchase_order_item_id');
            $table->unsignedBigInteger('ddc_id');
            $table->unsignedBigInteger('seller_id');
            $table->enum('discrepancy_type', ['SHORTAGE', 'DAMAGE', 'EXCESS', 'MIXED']);
            $table->integer('order_qty');
            $table->integer('received_qty');
            $table->integer('shortage_qty')->default(0);
            $table->integer('damage_qty')->default(0);
            $table->integer('excess_qty')->default(0);
            $table->decimal('unit_price', 12, 2)->default(0.00);
            $table->decimal('total_discrepancy_amount', 12, 2)->default(0.00);
            $table->json('proof_images')->nullable();
            $table->integer('seller_agreed_qty')->nullable();
            $table->decimal('final_amount', 12, 2)->nullable();
            $table->string('seller_invoice_file')->nullable();
            $table->enum('status', ['PENDING', 'SELLER_APPROVED', 'SELLER_REJECTED', 'ADPL_VERIFIED', 'SETTLED', 'CANCELLED'])->default('PENDING');
            $table->text('seller_remarks')->nullable();
            $table->text('adpl_remarks')->nullable();
            $table->timestamps();

            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
            $table->foreign('purchase_order_item_id')->references('id')->on('purchase_order_items')->onDelete('cascade');
            $table->foreign('ddc_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('credit_notes', function (Blueprint $table) {
            $table->id();
            $table->string('credit_note_number')->unique();
            $table->unsignedBigInteger('debit_note_id');
            $table->unsignedBigInteger('purchase_order_id');
            $table->unsignedBigInteger('ddc_id');
            $table->unsignedBigInteger('seller_id');
            $table->decimal('amount', 12, 2)->default(0.00);
            $table->enum('type', ['SHORTAGE_DAMAGE_CLAIM', 'EXCESS_STOCK_BILL']);
            $table->enum('status', ['GENERATED', 'SETTLED'])->default('GENERATED');
            $table->timestamps();

            $table->foreign('debit_note_id')->references('id')->on('debit_notes')->onDelete('cascade');
            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
            $table->foreign('ddc_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');
        });

        Schema::create('adpl_wallets', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('debit_note_id')->nullable();
            $table->decimal('amount', 12, 2);
            $table->enum('type', ['credit', 'debit']);
            $table->text('description');
            $table->timestamps();

            $table->foreign('debit_note_id')->references('id')->on('debit_notes')->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adpl_wallets');
        Schema::dropIfExists('credit_notes');
        Schema::dropIfExists('debit_notes');
    }
};
