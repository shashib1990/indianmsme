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
        Schema::create('ddc_pos_carts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ddc_id');
            $table->enum('mode', ['retail', 'wholesale'])->default('retail');
            $table->unsignedBigInteger('adc_id')->nullable();
            $table->unsignedBigInteger('customer_id')->nullable();
            $table->longText('cart_data')->nullable(); // JSON stored [{id, qty}, ...]
            $table->timestamps();

            $table->unique(['ddc_id', 'mode']);
            $table->foreign('ddc_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ddc_pos_carts');
    }
};
