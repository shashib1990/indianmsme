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
        Schema::create('ddc_brands', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ddc_id')->nullable()->index();
            $table->string('name');
            $table->enum('status', ['Active', 'Inactive'])->default('Active');
            $table->timestamps();

            $table->foreign('ddc_id')->references('id')->on('users')->onDelete('cascade');
        });

        // Copy existing brands from brands table into ddc_brands for initial fallback
        if (Schema::hasTable('brands')) {
            $existingBrands = DB::table('brands')->get();
            foreach ($existingBrands as $b) {
                DB::table('ddc_brands')->insertOrIgnore([
                    'id'         => $b->id,
                    'ddc_id'     => null,
                    'name'       => $b->name,
                    'status'     => $b->status ?? 'Active',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        // Drop foreign key on ddc_local_products and link to ddc_brands
        if (Schema::hasTable('ddc_local_products')) {
            try {
                Schema::table('ddc_local_products', function (Blueprint $table) {
                    $table->dropForeign(['brand_id']);
                });
            } catch (\Throwable $e) {
                // Ignore if foreign key was already dropped or absent
            }

            try {
                Schema::table('ddc_local_products', function (Blueprint $table) {
                    $table->foreign('brand_id')->references('id')->on('ddc_brands')->onDelete('set null');
                });
            } catch (\Throwable $e) {
                // Ignore if foreign key exists or fails
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('ddc_local_products')) {
            try {
                Schema::table('ddc_local_products', function (Blueprint $table) {
                    $table->dropForeign(['brand_id']);
                });
            } catch (\Throwable $e) {
                // Ignore if foreign key absent
            }
        }
        Schema::dropIfExists('ddc_brands');
    }
};
