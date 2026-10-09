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
        Schema::create('order_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('financial_year', 10)->unique();
            $table->unsignedBigInteger('last_sequence')->default(0);
            $table->string('last_order_number', 50)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('order_sequences');
    }
};
