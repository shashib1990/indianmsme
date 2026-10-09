<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // 1. Modify users table
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'mobile')) {
                $table->string('mobile')->nullable()->unique()->after('email');
            }
            if (!Schema::hasColumn('users', 'role')) {
                $table->string('role')->nullable()->index()->after('mobile');
            }
            if (!Schema::hasColumn('users', 'status')) {
                $table->enum('status', ['0', '1'])->default('1')->after('password');
            }
        });

        // 2. Drop dependent tables to recreate their foreign keys
        Schema::dropIfExists('admin_user_roles');
        Schema::dropIfExists('ddc_user_roles');
        Schema::dropIfExists('adc_user_roles');
        Schema::dropIfExists('vendor_user_roles');
        Schema::dropIfExists('ddc_wallets');
        Schema::dropIfExists('adc_wallets');

        // 3. Drop primary legacy tables
        Schema::dropIfExists('admins');
        Schema::dropIfExists('ddcs');
        Schema::dropIfExists('adcs');
        Schema::dropIfExists('vendors');

        // 4. Create separate profile tables
        // Admin Profile Table
        Schema::create('admin_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('profile_image')->nullable();
            $table->text('address')->nullable();
            $table->string('state')->nullable();
            $table->string('district')->nullable();
            $table->string('pincode')->nullable();
            $table->string('aadhaar_no')->nullable();
            $table->string('pan_no')->nullable();

            // Admin role-specific fields
            $table->enum('is_super_admin', ['0', '1'])->default('0');
            $table->enum('force_change_password', ['0', '1'])->default('0');
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('department_id')->references('id')->on('departments')->onDelete('restrict');
        });

        // ADC Profile Table
        Schema::create('adc_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('profile_image')->nullable();
            $table->text('address')->nullable();
            $table->string('state')->nullable();
            $table->string('district')->nullable();
            $table->string('pincode')->nullable();
            $table->string('aadhaar_no')->nullable();
            $table->string('pan_no')->nullable();

            // ADC role-specific fields
            $table->string('designation')->nullable();
            $table->decimal('wallet_balance', 10, 2)->default(0);
            $table->decimal('per_user_daily_charge', 8, 2)->default(10.00);
            $table->enum('force_change_password', ['0', '1'])->default('0');
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('department_id')->references('id')->on('departments')->onDelete('restrict');
        });

        // DDC Profile Table
        Schema::create('ddc_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('profile_image')->nullable();
            $table->text('address')->nullable();
            $table->string('state')->nullable();
            $table->string('district')->nullable();
            $table->string('pincode')->nullable();
            $table->string('aadhaar_no')->nullable();
            $table->string('pan_no')->nullable();

            // DDC role-specific fields
            $table->decimal('wallet_balance', 10, 2)->default(0);
            $table->decimal('per_user_daily_charge', 8, 2)->default(100.00);
            $table->enum('force_change_password', ['0', '1'])->default('0');
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('department_id')->references('id')->on('departments')->onDelete('restrict');
        });

        // Seller/Vendor Profile Table
        Schema::create('seller_profiles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->string('profile_image')->nullable();
            $table->text('address')->nullable();
            $table->string('state')->nullable();
            $table->string('district')->nullable();
            $table->string('pincode')->nullable();
            $table->string('aadhaar_no')->nullable();
            $table->string('pan_no')->nullable();

            // Seller/Vendor role-specific fields
            $table->string('company_name')->nullable();
            $table->string('gst_number')->nullable();
            $table->enum('force_change_password', ['0', '1'])->default('0');
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->timestamps();

            $table->foreign('department_id')->references('id')->on('departments')->onDelete('restrict');
        });

        // 5. Recreate role assignment tables pointing to users table
        Schema::create('admin_user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('admin_roles')->onDelete('cascade');
            $table->unique(['admin_id', 'role_id']);
        });

        // 6. Recreate wallets pointing to users table
        Schema::create('ddc_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ddc_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('type', ['credit', 'debit'])->index();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('adc_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adc_id')->constrained('users')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('type', ['credit', 'debit'])->index();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        // 7. Create Impersonation/Audit Logs Table
        Schema::create('impersonation_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('target_user_id')->constrained('users')->onDelete('cascade');
            $table->string('target_role');
            $table->timestamp('login_time');
            $table->string('ip_address')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('impersonation_logs');
        Schema::dropIfExists('adc_wallets');
        Schema::dropIfExists('ddc_wallets');
        Schema::dropIfExists('vendor_user_roles');
        Schema::dropIfExists('adc_user_roles');
        Schema::dropIfExists('ddc_user_roles');
        Schema::dropIfExists('admin_user_roles');

        Schema::dropIfExists('seller_profiles');
        Schema::dropIfExists('ddc_profiles');
        Schema::dropIfExists('adc_profiles');
        Schema::dropIfExists('admin_profiles');

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['mobile', 'role', 'status']);
        });
    }
};
