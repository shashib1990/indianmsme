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
        // 1. Depot Racks Table (for both DDC & ADC)
        if (!Schema::hasTable('depot_racks')) {
            Schema::create('depot_racks', function (Blueprint $table) {
                $table->id();
                $table->string('owner_type')->default('ddc'); // ddc / adc
                $table->unsignedBigInteger('owner_id'); // foreign key to users table
                $table->string('depot_code')->nullable(); // e.g. DDB036, DDB003
                $table->string('unit_type')->default('RACK'); // RACK / SHELF
                $table->integer('total_units')->default(0);
                $table->integer('available_units')->default(0);
                $table->integer('allotted_units')->default(0);
                $table->decimal('amount_per_unit', 12, 2)->default(0);
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->string('status')->default('active'); // active / inactive
                $table->text('description')->nullable();
                $table->timestamps();

                $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // 2. Depot Rack Images Table (Depot-Level Store Photos)
        if (!Schema::hasTable('depot_rack_images')) {
            Schema::create('depot_rack_images', function (Blueprint $table) {
                $table->id();
                $table->string('owner_type')->default('ddc'); // ddc / adc
                $table->unsignedBigInteger('owner_id');
                $table->unsignedBigInteger('depot_rack_id')->nullable();
                $table->string('image_key')->nullable(); // e.g. ddc_front_day, cash_counter, etc.
                $table->string('image_label')->nullable(); // Front photo, Store inside, etc.
                $table->string('image_url');
                $table->string('approval_status')->default('pending'); // pending / approved / rejected
                $table->text('rejection_reason')->nullable();
                $table->timestamps();

                $table->foreign('depot_rack_id')->references('id')->on('depot_racks')->onDelete('set null');
                $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        // 3. Depot Rack Bookings Table (for Seller Bookings & Admin Payment Verification)
        if (!Schema::hasTable('depot_rack_bookings')) {
            Schema::create('depot_rack_bookings', function (Blueprint $table) {
                $table->id();
                $table->string('booking_no')->unique();
                $table->unsignedBigInteger('seller_id');
                $table->string('owner_type')->default('ddc'); // ddc / adc
                $table->unsignedBigInteger('owner_id'); // target DDC/ADC user id
                $table->unsignedBigInteger('depot_rack_id');
                $table->string('unit_type')->default('RACK'); // RACK / SHELF
                $table->integer('quantity')->default(1);
                $table->integer('duration_months')->default(3); // 3, 6, 12
                $table->date('start_date')->nullable();
                $table->date('expiry_date')->nullable();
                $table->decimal('monthly_rate', 12, 2)->default(0);
                $table->decimal('total_amount', 12, 2)->default(0);
                $table->decimal('admin_share', 12, 2)->default(0);
                $table->decimal('depot_share', 12, 2)->default(0);
                $table->string('payment_method')->nullable(); // QR, Bank Transfer, UPI
                $table->string('payment_utr')->nullable();
                $table->string('payment_slip')->nullable();
                $table->string('status')->default('Pending Payment Verification'); // Pending Payment Verification, Approved, Rejected, Expired
                $table->text('seller_remarks')->nullable();
                $table->text('admin_remarks')->nullable();
                $table->timestamp('request_date')->nullable();
                $table->timestamp('action_date')->nullable();
                $table->timestamps();

                $table->foreign('seller_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('owner_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('depot_rack_id')->references('id')->on('depot_racks')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depot_rack_bookings');
        Schema::dropIfExists('depot_rack_images');
        Schema::dropIfExists('depot_racks');
    }
};
