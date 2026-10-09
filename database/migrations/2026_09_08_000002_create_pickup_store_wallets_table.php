<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('pickup_store_wallets')) {
            Schema::create('pickup_store_wallets', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('pickup_store_id');
                $table->decimal('amount', 12, 2);
                $table->string('type')->default('credit'); // credit, debit
                $table->text('description')->nullable();
                $table->unsignedBigInteger('reference_id')->nullable();
                $table->string('reference_type')->nullable();
                $table->timestamps();

                $table->foreign('pickup_store_id')->references('id')->on('pickup_stores')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('pickup_store_wallets');
    }
};
