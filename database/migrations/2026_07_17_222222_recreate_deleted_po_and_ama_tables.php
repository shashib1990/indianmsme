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
        Schema::disableForeignKeyConstraints();

        if (!Schema::hasTable('purchase_orders')) {
            Schema::create('purchase_orders', function (Blueprint $table) {
                $table->id();
                $table->string('po_number')->unique();
                $table->unsignedBigInteger('ddc_id');
                $table->unsignedBigInteger('seller_id')->nullable();
                $table->unsignedBigInteger('pickup_store_id')->nullable();
                $table->string('ama_code')->nullable();
                $table->boolean('ssi_applicable')->default(false)
                    ->comment('True if seller had active DDC subscription at PO creation time');
                $table->enum('ssi_type', ['SSI', 'FSI'])->nullable()
                    ->comment('Type of incentive: SSI (Smart Sales Incentive) or FSI (Field Sales Incentive)');
                $table->decimal('ssi_percentage', 5, 2)->nullable()
                    ->comment('Locked-in incentive % at time of PO creation (seller setting or default 0.50)');
                $table->decimal('total_amount', 12, 2);
                $table->decimal('seller_total_amount', 12, 2)->default(0.00);
                $table->decimal('accepted_amount', 12, 2)->default(0.00);
                $table->decimal('seller_accepted_amount', 12, 2)->default(0.00);
                $table->enum('status', ['Pending', 'Accepted', 'Processing', 'Dispatched', 'Received', 'Cancelled', 'Rejected'])->default('Pending');
                $table->enum('payment_status', ['Unpaid', 'Paid'])->default('Unpaid');
                $table->enum('seller_payment_status', ['Unpaid', 'Paid'])->default('Unpaid');
                $table->string('payment_reference')->nullable();
                $table->text('seller_payment_reference')->nullable();
                $table->enum('payment_verification_status', ['Pending', 'Verified', 'Rejected'])->default('Pending');
                $table->enum('seller_payment_verification_status', ['Pending', 'Verified', 'Rejected'])->default('Pending');
                $table->enum('dispatch_status', ['Pending', 'Dispatched', 'Delivered'])->default('Pending');
                $table->text('rejection_reason')->nullable();
                $table->timestamps();

                $table->foreign('ddc_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('seller_id')->references('id')->on('users')->onDelete('set null');
                $table->foreign('pickup_store_id')->references('id')->on('pickup_stores')->onDelete('set null');
            });
        }

        if (!Schema::hasTable('purchase_order_items')) {
            Schema::create('purchase_order_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('purchase_order_id');
                $table->unsignedBigInteger('seller_id');
                $table->unsignedBigInteger('product_id');
                $table->string('product_name')->nullable();
                $table->string('product_code')->nullable();
                $table->string('barcode')->nullable();
                $table->integer('quantity');
                $table->decimal('price', 12, 2);
                $table->decimal('actual_mrp', 12, 2)->nullable();
                $table->decimal('received_mrp', 12, 2)->nullable();
                $table->decimal('seller_price', 12, 2)->default(0.00);
                $table->decimal('total', 12, 2);
                $table->decimal('seller_total', 12, 2)->default(0.00);
                $table->integer('supply_qty')->nullable();
                $table->integer('damage_qty')->default(0);
                $table->integer('shortage_qty')->default(0);
                $table->integer('excess_qty')->default(0);
                $table->enum('status', ['Pending', 'Accepted', 'Rejected'])->default('Pending');
                $table->string('remarks')->nullable();
                $table->date('mfg_date')->nullable();
                $table->date('expire_date')->nullable();
                $table->string('batch_number')->nullable();
                $table->boolean('is_stocked_in')->default(false);
                $table->timestamps();

                $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
                $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('product_id')->references('id')->on('products')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('ama_transactions')) {
            Schema::create('ama_transactions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('purchase_order_id')->nullable();
                $table->string('ama_code');
                $table->decimal('amount', 10, 2);
                $table->decimal('percentage', 5, 2);
                $table->enum('type', ['SSI', 'FSI']);
                $table->enum('status', ['Hold', 'Paid', 'Released', 'Cancelled'])->default('Hold');
                $table->unsignedBigInteger('inventory_id')->nullable()
                    ->comment('FK to ddc_inventories — set when incentive is released on stock-in');
                $table->text('notes')->nullable()
                    ->comment('Audit notes, e.g. calculation breakdown');
                $table->timestamps();

                $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('cascade');
                $table->foreign('inventory_id')->references('id')->on('ddc_inventories')->onDelete('set null');
            });
        }

        Schema::enableForeignKeyConstraints();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists('ama_transactions');
        Schema::dropIfExists('purchase_order_items');
        Schema::dropIfExists('purchase_orders');
        Schema::enableForeignKeyConstraints();
    }
};
