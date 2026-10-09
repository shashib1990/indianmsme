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
        // 1. Create ddc_pos_sales_returns table
        if (!Schema::hasTable('ddc_pos_sales_returns')) {
            Schema::create('ddc_pos_sales_returns', function (Blueprint $table) {
                $table->id();
                $table->string('return_number', 50)->unique();
                $table->unsignedBigInteger('pos_order_id');
                $table->unsignedBigInteger('invoice_id')->nullable();
                $table->unsignedBigInteger('ddc_id');
                $table->unsignedBigInteger('customer_id')->nullable();
                $table->string('customer_name')->nullable();
                $table->string('customer_mobile')->nullable();
                $table->decimal('total_items', 8, 3)->default(0);
                $table->decimal('total_qty', 10, 3)->default(0);
                $table->decimal('subtotal', 12, 2)->default(0);
                $table->decimal('tax_amount', 12, 2)->default(0);
                $table->decimal('discount_amount', 12, 2)->default(0);
                $table->decimal('total_return_amount', 12, 2)->default(0);
                $table->string('refund_mode', 50)->default('Cash'); // Cash, UPI, Card, Store Credit, Bank Transfer
                $table->text('return_reason')->nullable();
                $table->timestamps();

                $table->foreign('pos_order_id')->references('id')->on('ddc_pos_orders')->onDelete('cascade');
                $table->foreign('ddc_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // 2. Create ddc_pos_sales_return_items table
        if (!Schema::hasTable('ddc_pos_sales_return_items')) {
            Schema::create('ddc_pos_sales_return_items', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('sales_return_id');
                $table->unsignedBigInteger('pos_order_item_id');
                $table->unsignedBigInteger('ddc_inventory_id')->nullable();
                $table->unsignedBigInteger('product_id')->nullable();
                $table->string('product_name');
                $table->string('product_code')->nullable();
                $table->decimal('unit_price', 12, 2)->default(0);
                $table->decimal('mrp', 12, 2)->default(0);
                $table->decimal('returned_quantity', 10, 3)->default(0);
                $table->string('unit_of_measure', 30)->default('Units');
                $table->decimal('gst_rate', 5, 2)->default(0);
                $table->decimal('taxable_amount', 12, 2)->default(0);
                $table->decimal('tax_amount', 12, 2)->default(0);
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->timestamps();

                $table->foreign('sales_return_id')->references('id')->on('ddc_pos_sales_returns')->onDelete('cascade');
                $table->foreign('pos_order_item_id')->references('id')->on('ddc_pos_order_items')->onDelete('cascade');
            });
        }

        // 3. Add columns to ddc_pos_orders table
        if (Schema::hasTable('ddc_pos_orders')) {
            Schema::table('ddc_pos_orders', function (Blueprint $table) {
                if (!Schema::hasColumn('ddc_pos_orders', 'return_status')) {
                    $table->enum('return_status', ['None', 'Partially Returned', 'Fully Returned'])->default('None')->after('payment_status');
                }
                if (!Schema::hasColumn('ddc_pos_orders', 'total_returned_amount')) {
                    $table->decimal('total_returned_amount', 12, 2)->default(0.00)->after('return_status');
                }
            });
        }

        // 4. Add columns to ddc_pos_order_items table
        if (Schema::hasTable('ddc_pos_order_items')) {
            Schema::table('ddc_pos_order_items', function (Blueprint $table) {
                if (!Schema::hasColumn('ddc_pos_order_items', 'returned_quantity')) {
                    $table->decimal('returned_quantity', 10, 3)->default(0.000)->after('quantity');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ddc_pos_sales_return_items');
        Schema::dropIfExists('ddc_pos_sales_returns');

        if (Schema::hasTable('ddc_pos_orders')) {
            Schema::table('ddc_pos_orders', function (Blueprint $table) {
                if (Schema::hasColumn('ddc_pos_orders', 'return_status')) {
                    $table->dropColumn('return_status');
                }
                if (Schema::hasColumn('ddc_pos_orders', 'total_returned_amount')) {
                    $table->dropColumn('total_returned_amount');
                }
            });
        }

        if (Schema::hasTable('ddc_pos_order_items')) {
            Schema::table('ddc_pos_order_items', function (Blueprint $table) {
                if (Schema::hasColumn('ddc_pos_order_items', 'returned_quantity')) {
                    $table->dropColumn('returned_quantity');
                }
            });
        }
    }
};
