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
        // 1. Create Brands Table
        Schema::create('brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->unique();
            $table->string('logo')->nullable();
            $table->enum('status', ['0', '1'])->default('1'); // 1 = Active, 0 = Inactive
            $table->foreignId('created_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('updated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });

        // 2. Create Products Table
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->string('product_name');
            $table->string('barcode')->index();
            $table->text('description');
            $table->text('product_specification')->nullable();
            $table->foreignId('brand_id')->constrained('brands')->onDelete('restrict');
            $table->string('hsn_code');
            $table->foreignId('category_id')->constrained('categories')->onDelete('restrict');
            $table->foreignId('sub_category_id')->nullable()->constrained('sub_categories')->onDelete('restrict');
            $table->foreignId('child_category_id')->nullable()->constrained('child_categories')->onDelete('restrict');
            $table->text('product_keywords')->nullable(); // stored as json/comma-separated text
            $table->decimal('mrp', 10, 2);
            $table->decimal('ex_factory_price', 10, 2);
            $table->decimal('gst_rate', 5, 2);
            $table->string('unit_of_measure'); // Piece, Box, Pack, Bottle, Kg, Gram, Litre, ML, Other
            $table->string('net_quantity')->nullable(); // Net Quantity / Pack Size
            $table->integer('case_pack_quantity')->nullable();
            $table->decimal('height', 8, 2)->nullable();
            $table->decimal('width', 8, 2)->nullable();
            $table->decimal('length', 8, 2)->nullable();
            $table->decimal('weight', 8, 2)->nullable();
            $table->enum('dimension_unit', ['CM', 'Inch'])->default('CM');
            $table->enum('weight_unit', ['Gram', 'KG'])->default('Gram');
            $table->string('shelf_life')->nullable();
            $table->string('country_of_origin');
            $table->text('storage_instructions')->nullable();
            $table->string('featured_image');
            $table->text('gallery')->nullable(); // JSON array
            $table->string('brochure')->nullable(); // PDF
            $table->enum('status', ['Draft', 'Pending Approval', 'Approved', 'Rejected'])->default('Draft');
            $table->text('admin_remarks')->nullable();
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });

        // 3. Create Product Audit Logs Table
        Schema::create('product_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->string('product_name');
            $table->foreignId('modified_by')->constrained('users')->onDelete('cascade');
            $table->text('previous_values')->nullable(); // JSON string
            $table->text('updated_values')->nullable();  // JSON string
            $table->string('approval_status');
            $table->text('admin_remarks')->nullable();
            $table->timestamps();
        });

        // 4. Create Seller Media Table
        Schema::create('seller_media', function (Blueprint $table) {
            $table->id();
            $table->foreignId('seller_id')->constrained('users')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('file_url');
            $table->string('file_type'); // e.g. image, pdf
            $table->bigInteger('file_size');
            $table->timestamps();
        });

        // 5. Create Sub Seller Products Table (Assigned Products with Margin)
        Schema::create('sub_seller_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sub_seller_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            $table->decimal('margin', 10, 2)->default(0.00);
            $table->decimal('final_price', 10, 2);
            $table->timestamps();

            $table->unique(['sub_seller_id', 'product_id']);
        });

        // 6. Add allow_own_products to seller_profiles
        Schema::table('seller_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('seller_profiles', 'allow_own_products')) {
                $table->enum('allow_own_products', ['Yes', 'No'])->default('No')->after('kyc_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_profiles', function (Blueprint $table) {
            if (Schema::hasColumn('seller_profiles', 'allow_own_products')) {
                $table->dropColumn('allow_own_products');
            }
        });

        Schema::dropIfExists('sub_seller_products');
        Schema::dropIfExists('seller_media');
        Schema::dropIfExists('product_audit_logs');
        Schema::dropIfExists('products');
        Schema::dropIfExists('brands');
    }
};
