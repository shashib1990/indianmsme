<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('adc_brands', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('adc_id')->nullable()->index();
            $table->string('name');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();

            $table->foreign('adc_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Copy existing brands from brands table into adc_brands for initial fallback
        if (Schema::hasTable('brands')) {
            $existingBrands = DB::table('brands')->get();
            foreach ($existingBrands as $b) {
                DB::table('adc_brands')->insertOrIgnore([
                    'id'         => $b->id,
                    'adc_id'     => null,
                    'name'       => $b->name,
                    'status'     => $b->status ?? 'Active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Link foreign key on adc_local_products
        if (Schema::hasTable('adc_local_products')) {
            try {
                Schema::table('adc_local_products', function (Blueprint $table) {
                    $table->dropForeign(['brand_id']);
                });
            } catch (\Throwable $e) {
                // Ignore if absent
            }

            try {
                Schema::table('adc_local_products', function (Blueprint $table) {
                    $table->foreign('brand_id')->references('id')->on('adc_brands')->onDelete('set null');
                });
            } catch (\Throwable $e) {
                // Ignore
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('adc_local_products')) {
            try {
                Schema::table('adc_local_products', function (Blueprint $table) {
                    $table->dropForeign(['brand_id']);
                });
            } catch (\Throwable $e) {
                // Ignore
            }
        }
        Schema::dropIfExists('adc_brands');
    }
};
