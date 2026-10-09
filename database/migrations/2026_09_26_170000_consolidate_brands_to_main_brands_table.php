<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Copy any unique brands from ddc_brands to brands table
        if (Schema::hasTable('ddc_brands')) {
            $ddcBrands = DB::table('ddc_brands')->get();
            foreach ($ddcBrands as $db) {
                $name = trim($db->name);
                if (empty($name)) continue;

                $existing = DB::table('brands')->whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
                if (!$existing) {
                    $brandId = DB::table('brands')->insertGetId([
                        'name'       => $name,
                        'slug'       => Str::slug($name) ?: ('brand-' . Str::random(5)),
                        'status'     => '1',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $brandId = $existing->id;
                }

                // Update ddc_local_products brand_id to new brands id
                if (Schema::hasTable('ddc_local_products')) {
                    DB::table('ddc_local_products')
                        ->where('brand_id', $db->id)
                        ->update(['brand_id' => $brandId]);
                }
            }
        }

        // 2. Copy any unique brands from adc_brands to brands table
        if (Schema::hasTable('adc_brands')) {
            $adcBrands = DB::table('adc_brands')->get();
            foreach ($adcBrands as $ab) {
                $name = trim($ab->name);
                if (empty($name)) continue;

                $existing = DB::table('brands')->whereRaw('LOWER(name) = ?', [strtolower($name)])->first();
                if (!$existing) {
                    $brandId = DB::table('brands')->insertGetId([
                        'name'       => $name,
                        'slug'       => Str::slug($name) ?: ('brand-' . Str::random(5)),
                        'status'     => '1',
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    $brandId = $existing->id;
                }

                // Update adc_local_products brand_id to new brands id
                if (Schema::hasTable('adc_local_products')) {
                    DB::table('adc_local_products')
                        ->where('brand_id', $ab->id)
                        ->update(['brand_id' => $brandId]);
                }
            }
        }

        // 3. Drop foreign keys referencing adc_brands and ddc_brands
        Schema::table('ddc_local_products', function (Blueprint $table) {
            $table->dropForeign('ddc_local_products_brand_id_foreign');
        });

        Schema::table('adc_local_products', function (Blueprint $table) {
            $table->dropForeign('adc_local_products_brand_id_foreign');
        });

        // 4. Drop tables ddc_brands and adc_brands
        Schema::dropIfExists('ddc_brands');
        Schema::dropIfExists('adc_brands');

        // 5. Add foreign key constraints on ddc_local_products and adc_local_products pointing to brands
        Schema::table('ddc_local_products', function (Blueprint $table) {
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
        });

        Schema::table('adc_local_products', function (Blueprint $table) {
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ddc_local_products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
        });

        Schema::table('adc_local_products', function (Blueprint $table) {
            $table->dropForeign(['brand_id']);
        });

        Schema::create('ddc_brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ddc_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::create('adc_brands', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adc_id')->nullable()->constrained('users')->onDelete('cascade');
            $table->string('name');
            $table->string('status')->default('Active');
            $table->timestamps();
        });

        Schema::table('ddc_local_products', function (Blueprint $table) {
            $table->foreign('brand_id')->references('id')->on('ddc_brands')->onDelete('set null');
        });

        Schema::table('adc_local_products', function (Blueprint $table) {
            $table->foreign('brand_id')->references('id')->on('adc_brands')->onDelete('set null');
        });
    }
};
