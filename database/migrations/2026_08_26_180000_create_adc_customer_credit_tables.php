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
        // 1. Add credit tracking columns to adc_pos_customers table if missing
        if (Schema::hasTable('adc_pos_customers')) {
            Schema::table('adc_pos_customers', function (Blueprint $table) {
                if (!Schema::hasColumn('adc_pos_customers', 'total_credit_given')) {
                    $table->decimal('total_credit_given', 12, 2)->default(0.00)->after('gst_number');
                }
                if (!Schema::hasColumn('adc_pos_customers', 'total_payment_received')) {
                    $table->decimal('total_payment_received', 12, 2)->default(0.00)->after('total_credit_given');
                }
                if (!Schema::hasColumn('adc_pos_customers', 'outstanding_balance')) {
                    $table->decimal('outstanding_balance', 12, 2)->default(0.00)->after('total_payment_received');
                }
            });
        }

        // 2. Create adc_customer_payments table
        if (!Schema::hasTable('adc_customer_payments')) {
            Schema::create('adc_customer_payments', function (Blueprint $table) {
                $table->id();
                $table->string('payment_number', 50)->unique();
                $table->unsignedBigInteger('adc_id')->index();
                $table->unsignedBigInteger('customer_id')->index();
                $table->decimal('amount', 12, 2)->default(0.00);
                $table->string('payment_mode', 20)->default('Cash'); // Cash, Card, UPI
                $table->dateTime('payment_date');
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->foreign('customer_id')->references('id')->on('adc_pos_customers')->onDelete('cascade');
            });
        }

        // 3. Create adc_customer_credit_ledgers table
        if (!Schema::hasTable('adc_customer_credit_ledgers')) {
            Schema::create('adc_customer_credit_ledgers', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('adc_id')->index();
                $table->unsignedBigInteger('customer_id')->index();
                $table->unsignedBigInteger('pos_order_id')->nullable()->index();
                $table->unsignedBigInteger('payment_id')->nullable()->index();
                $table->unsignedBigInteger('sales_return_id')->nullable()->index();
                $table->string('reference_no', 100);
                $table->string('transaction_type', 50); // 'Credit Sale', 'Payment Received', 'Sales Return'
                $table->decimal('debit', 12, 2)->default(0.00); // Credit given to customer
                $table->decimal('credit', 12, 2)->default(0.00); // Payment received from customer or return
                $table->string('payment_mode', 50)->default('Credit');
                $table->decimal('balance', 12, 2)->default(0.00); // Running outstanding balance
                $table->text('remarks')->nullable();
                $table->unsignedBigInteger('created_by')->nullable();
                $table->timestamps();

                $table->foreign('customer_id')->references('id')->on('adc_pos_customers')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('adc_customer_credit_ledgers');
        Schema::dropIfExists('adc_customer_payments');

        if (Schema::hasTable('adc_pos_customers')) {
            Schema::table('adc_pos_customers', function (Blueprint $table) {
                if (Schema::hasColumn('adc_pos_customers', 'total_credit_given')) {
                    $table->dropColumn(['total_credit_given', 'total_payment_received', 'outstanding_balance']);
                }
            });
        }
    }
};
