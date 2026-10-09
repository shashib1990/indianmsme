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
        if (!Schema::hasTable('seller_ssi_slabs')) {
            Schema::create('seller_ssi_slabs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('seller_id')->index();
                $table->decimal('min_amount', 12, 2)->default(0.00);
                $table->decimal('max_amount', 12, 2)->nullable();
                $table->decimal('percentage', 5, 2)->default(0.00);
                $table->timestamps();

                $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('seller_ssi_slabs');
    }
};
