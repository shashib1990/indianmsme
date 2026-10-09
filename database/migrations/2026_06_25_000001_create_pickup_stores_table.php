<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pickup_stores', function (Blueprint $table) {
            $table->id();
            $table->string('owner_type'); // Seller, Sub Seller
            $table->unsignedBigInteger('owner_id');
            $table->string('pickup_store_code')->unique();
            $table->string('pickup_store_name');
            $table->string('contact_person');
            $table->string('mobile')->unique();
            $table->string('email')->unique()->nullable();
            $table->string('password')->nullable();
            $table->string('gst_number')->nullable();
            $table->text('address');
            $table->string('landmark')->nullable();
            $table->unsignedBigInteger('state_id');
            $table->unsignedBigInteger('district_id');
            $table->string('pincode');
            $table->boolean('can_receive_payment_and_dispatch')->default(false);
            $table->string('account_holder_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('ifsc_code')->nullable();
            $table->string('bank_name')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('status')->default('Active'); // Active, Inactive
            $table->timestamps();

            $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('state_id')->references('id')->on('states')->onDelete('restrict');
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pickup_stores');
    }
};
