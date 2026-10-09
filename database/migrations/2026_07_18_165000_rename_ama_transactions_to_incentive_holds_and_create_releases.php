<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Rename ama_transactions to incentive_holds
        Schema::rename('ama_transactions', 'incentive_holds');

        // 2. Make ama_code nullable on incentive_holds
        Schema::table('incentive_holds', function (Blueprint $table) {
            $table->string('ama_code')->nullable()->change();
        });

        // 3. Create incentive_releases table
        Schema::create('incentive_releases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hold_id')->constrained('incentive_holds')->onDelete('cascade');
            $table->string('ama_code');
            $table->foreignId('adc_order_id')->constrained('adc_orders')->onDelete('cascade');
            $table->foreignId('adc_order_item_id')->constrained('adc_order_items')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->integer('released_quantity');
            $table->decimal('released_amount', 12, 2);
            $table->dateTime('release_date');
            $table->string('status')->default('Released');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('incentive_releases');

        Schema::table('incentive_holds', function (Blueprint $table) {
            $table->string('ama_code')->nullable(false)->change();
        });

        Schema::rename('incentive_holds', 'ama_transactions');
    }
};
