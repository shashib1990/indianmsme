<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pickup_store_delivery_areas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pickup_store_id');
            $table->string('coverage_type'); // pan_india, state, district, pincode
            $table->unsignedBigInteger('state_id')->nullable();
            $table->unsignedBigInteger('district_id')->nullable();
            $table->string('pincode')->nullable();
            $table->string('status')->default('Active'); // Active, Inactive
            $table->timestamps();

            $table->foreign('pickup_store_id')->references('id')->on('pickup_stores')->onDelete('cascade');
            $table->foreign('state_id')->references('id')->on('states')->onDelete('cascade');
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickup_store_delivery_areas');
    }
};
