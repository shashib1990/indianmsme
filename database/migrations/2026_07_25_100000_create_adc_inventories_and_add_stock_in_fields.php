<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Create adc_inventories table
        if (!Schema::hasTable('adc_inventories')) {
            Schema::create('adc_inventories', function (Blueprint $table) {
                $table->id();
                $table->foreignId('adc_id')->constrained('users')->onDelete('cascade');
                $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
                $table->string('product_name')->nullable();
                $table->string('product_code')->nullable();
                $table->string('barcode')->nullable();
                $table->string('unit_of_measure')->nullable();
                $table->integer('quantity')->default(0);
                $table->integer('reserved_quantity')->default(0);
                $table->integer('sold_quantity')->default(0);
                $table->integer('returned_quantity')->default(0);
                $table->decimal('mrp', 12, 2)->default(0.00);
                $table->decimal('purchase_price', 12, 2)->default(0.00);
                $table->decimal('msp', 12, 2)->default(0.00);
                $table->decimal('adc_price', 12, 2)->default(0.00);
                $table->decimal('csp', 12, 2)->default(0.00);
                $table->decimal('gst_rate', 5, 2)->default(0.00);
                $table->decimal('adc_margin', 12, 2)->default(0.00);
                $table->decimal('csp_margin', 12, 2)->default(0.00);
                $table->date('mfg_date')->nullable();
                $table->date('expire_date')->nullable();
                $table->string('batch_number')->nullable();
                $table->foreignId('adc_order_id')->nullable()->constrained('adc_orders')->onDelete('set null');
                $table->foreignId('adc_order_item_id')->nullable()->constrained('adc_order_items')->onDelete('set null');
                $table->timestamps();
            });
        }

        // 2. Add Stock-In tracking fields to adc_order_items
        Schema::table('adc_order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('adc_order_items', 'is_stocked_in')) {
                $table->boolean('is_stocked_in')->default(false)->after('purchase_order_id');
            }
            if (!Schema::hasColumn('adc_order_items', 'stocked_in_at')) {
                $table->timestamp('stocked_in_at')->nullable()->after('is_stocked_in');
            }
            if (!Schema::hasColumn('adc_order_items', 'received_mrp')) {
                $table->decimal('received_mrp', 12, 2)->nullable()->after('stocked_in_at');
            }
            if (!Schema::hasColumn('adc_order_items', 'supply_qty')) {
                $table->integer('supply_qty')->nullable()->after('received_mrp');
            }
            if (!Schema::hasColumn('adc_order_items', 'damage_qty')) {
                $table->integer('damage_qty')->default(0)->after('supply_qty');
            }
            if (!Schema::hasColumn('adc_order_items', 'shortage_qty')) {
                $table->integer('shortage_qty')->default(0)->after('damage_qty');
            }
            if (!Schema::hasColumn('adc_order_items', 'excess_qty')) {
                $table->integer('excess_qty')->default(0)->after('shortage_qty');
            }
            if (!Schema::hasColumn('adc_order_items', 'batch_number')) {
                $table->string('batch_number')->nullable()->after('excess_qty');
            }
            if (!Schema::hasColumn('adc_order_items', 'mfg_date')) {
                $table->date('mfg_date')->nullable()->after('batch_number');
            }
            if (!Schema::hasColumn('adc_order_items', 'expire_date')) {
                $table->date('expire_date')->nullable()->after('mfg_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('adc_order_items', function (Blueprint $table) {
            $table->dropColumn([
                'is_stocked_in', 'stocked_in_at', 'received_mrp', 'supply_qty',
                'damage_qty', 'shortage_qty', 'excess_qty', 'batch_number', 'mfg_date', 'expire_date'
            ]);
        });

        Schema::dropIfExists('adc_inventories');
    }
};
