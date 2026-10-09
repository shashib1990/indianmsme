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
        Schema::create('ddc_local_products', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ddc_id')->index();
            $table->string('local_product_code')->unique();
            $table->string('product_name');
            $table->unsignedBigInteger('category_id')->index();
            $table->string('hsn_code')->nullable();
            $table->decimal('gst_rate', 5, 2)->default(0.00);
            $table->unsignedBigInteger('brand_id')->nullable()->index();
            $table->string('unit_of_measure');
            $table->string('barcode')->nullable()->index();
            $table->string('product_image')->nullable();
            $table->enum('status', ['Draft', 'Active'])->default('Draft');
            $table->unsignedBigInteger('msme_product_id')->nullable()->index()->comment('For future MSME vs Local comparison mapping');
            $table->timestamps();

            $table->foreign('ddc_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('restrict');
            $table->foreign('brand_id')->references('id')->on('brands')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ddc_local_products');
    }
};
