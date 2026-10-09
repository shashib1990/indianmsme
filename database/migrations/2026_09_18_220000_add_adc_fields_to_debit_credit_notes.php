<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('debit_notes', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_order_id')->nullable()->change();
            $table->unsignedBigInteger('purchase_order_item_id')->nullable()->change();
            $table->unsignedBigInteger('ddc_id')->nullable()->change();
            $table->unsignedBigInteger('seller_id')->nullable()->change();

            if (!Schema::hasColumn('debit_notes', 'adc_order_id')) {
                $table->foreignId('adc_order_id')->nullable()->after('purchase_order_item_id')->constrained('adc_orders')->onDelete('cascade');
            }
            if (!Schema::hasColumn('debit_notes', 'adc_order_item_id')) {
                $table->foreignId('adc_order_item_id')->nullable()->after('adc_order_id')->constrained('adc_order_items')->onDelete('cascade');
            }
            if (!Schema::hasColumn('debit_notes', 'adc_id')) {
                $table->foreignId('adc_id')->nullable()->after('seller_id')->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('debit_notes', 'target_type')) {
                $table->enum('target_type', ['DDC', 'SELLER'])->default('DDC')->after('adc_id');
            }
        });

        Schema::table('debit_note_items', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_order_item_id')->nullable()->change();

            if (!Schema::hasColumn('debit_note_items', 'adc_order_item_id')) {
                $table->foreignId('adc_order_item_id')->nullable()->after('purchase_order_item_id')->constrained('adc_order_items')->onDelete('cascade');
            }
        });

        Schema::table('credit_notes', function (Blueprint $table) {
            $table->unsignedBigInteger('purchase_order_id')->nullable()->change();
            $table->unsignedBigInteger('ddc_id')->nullable()->change();
            $table->unsignedBigInteger('seller_id')->nullable()->change();

            if (!Schema::hasColumn('credit_notes', 'adc_order_id')) {
                $table->foreignId('adc_order_id')->nullable()->after('purchase_order_id')->constrained('adc_orders')->onDelete('cascade');
            }
            if (!Schema::hasColumn('credit_notes', 'adc_id')) {
                $table->foreignId('adc_id')->nullable()->after('seller_id')->constrained('users')->onDelete('cascade');
            }
            if (!Schema::hasColumn('credit_notes', 'target_type')) {
                $table->enum('target_type', ['DDC', 'SELLER'])->default('DDC')->after('adc_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('debit_notes', function (Blueprint $table) {
            if (Schema::hasColumn('debit_notes', 'adc_order_id')) {
                $table->dropForeign(['adc_order_id']);
                $table->dropColumn('adc_order_id');
            }
            if (Schema::hasColumn('debit_notes', 'adc_order_item_id')) {
                $table->dropForeign(['adc_order_item_id']);
                $table->dropColumn('adc_order_item_id');
            }
            if (Schema::hasColumn('debit_notes', 'adc_id')) {
                $table->dropForeign(['adc_id']);
                $table->dropColumn('adc_id');
            }
            if (Schema::hasColumn('debit_notes', 'target_type')) {
                $table->dropColumn('target_type');
            }
        });

        Schema::table('debit_note_items', function (Blueprint $table) {
            if (Schema::hasColumn('debit_note_items', 'adc_order_item_id')) {
                $table->dropForeign(['adc_order_item_id']);
                $table->dropColumn('adc_order_item_id');
            }
        });

        Schema::table('credit_notes', function (Blueprint $table) {
            if (Schema::hasColumn('credit_notes', 'adc_order_id')) {
                $table->dropForeign(['adc_order_id']);
                $table->dropColumn('adc_order_id');
            }
            if (Schema::hasColumn('credit_notes', 'adc_id')) {
                $table->dropForeign(['adc_id']);
                $table->dropColumn('adc_id');
            }
            if (Schema::hasColumn('credit_notes', 'target_type')) {
                $table->dropColumn('target_type');
            }
        });
    }
};
