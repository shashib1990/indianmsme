<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Setup Enum definitions with comments
        $activeInactiveComment = "0=Inactive, 1=Active";
        $yesNoComment = "0=No, 1=Yes";
        $activeInactiveValues = ['0', '1'];

        // Departments
        Schema::create('departments', function (Blueprint $table) use ($activeInactiveValues, $activeInactiveComment) {
            $table->id();
            $table->string('name')->index();
            $table->text('description')->nullable();
            $table->enum('is_active', $activeInactiveValues)->default('1')->comment($activeInactiveComment);
            $table->unsignedBigInteger('owner_id')->nullable()->index();
            $table->string('owner_type')->nullable()->index();
            $table->timestamps();
        });

        // ================= ADMIN ROLE SYSTEM =================
        Schema::create('admin_roles', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index();
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        Schema::create('admin_permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->string('module')->index();
            $table->string('submodule')->nullable();
            $table->enum('type', ['read', 'write'])->default('read');
            $table->timestamps();
        });

        Schema::create('admin_role_permissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('role_id')->constrained('admin_roles')->onDelete('cascade');
            $table->foreignId('permission_id')->constrained('admin_permissions')->onDelete('cascade');
            $table->unique(['role_id', 'permission_id']);
        });

        // ================= DDC & ADC ROLE SYSTEM REMOVED PER USER_REQUEST =================

        // Admins table
        Schema::create('admins', function (Blueprint $table) use ($activeInactiveValues, $activeInactiveComment, $yesNoComment) {
            $table->id();
            $table->string('name')->index();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable()->index();
            $table->string('avatar')->nullable();
            $table->enum('is_super_admin', $activeInactiveValues)->default('0')->comment($yesNoComment);
            $table->enum('force_change_password', $activeInactiveValues)->default('0')->comment($yesNoComment);
            $table->enum('is_active', $activeInactiveValues)->default('1')->comment($activeInactiveComment);
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('department_id')->references('id')->on('departments')->onDelete('restrict');
        });

        Schema::create('admin_user_roles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->constrained('admins')->onDelete('cascade');
            $table->foreignId('role_id')->constrained('admin_roles')->onDelete('cascade');
            $table->unique(['admin_id', 'role_id']);
        });

        // Vendors table
        Schema::create('vendors', function (Blueprint $table) use ($activeInactiveValues, $activeInactiveComment, $yesNoComment) {
            $table->id();
            $table->string('name')->index();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable()->index();
            $table->string('company_name')->nullable()->index();
            $table->string('gst_number')->nullable()->index();
            $table->string('avatar')->nullable();
            $table->enum('force_change_password', $activeInactiveValues)->default('0')->comment($yesNoComment);
            $table->enum('is_active', $activeInactiveValues)->default('1')->comment($activeInactiveComment);
            $table->rememberToken();
            $table->timestamps();
        });

        // DDC table
        Schema::create('ddcs', function (Blueprint $table) use ($activeInactiveValues, $activeInactiveComment, $yesNoComment) {
            $table->id();
            $table->string('name')->index();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable()->index();
            $table->string('district')->nullable()->index();
            $table->string('state')->nullable()->index();
            $table->string('avatar')->nullable();
            $table->decimal('wallet_balance', 10, 2)->default(0)->index();
            $table->decimal('per_user_daily_charge', 8, 2)->default(100.00);
            $table->enum('force_change_password', $activeInactiveValues)->default('0')->comment($yesNoComment);
            $table->enum('is_active', $activeInactiveValues)->default('1')->comment($activeInactiveComment);
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('department_id')->references('id')->on('departments')->onDelete('restrict');
        });

        // ADC table
        Schema::create('adcs', function (Blueprint $table) use ($activeInactiveValues, $activeInactiveComment, $yesNoComment) {
            $table->id();
            $table->string('name')->index();
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable()->index();
            $table->string('district')->nullable()->index();
            $table->string('state')->nullable()->index();
            $table->string('designation')->nullable();
            $table->string('avatar')->nullable();
            $table->decimal('wallet_balance', 10, 2)->default(0)->index();
            $table->decimal('per_user_daily_charge', 8, 2)->default(10.00);
            $table->enum('force_change_password', $activeInactiveValues)->default('0')->comment($yesNoComment);
            $table->enum('is_active', $activeInactiveValues)->default('1')->comment($activeInactiveComment);
            $table->unsignedBigInteger('parent_id')->nullable()->index();
            $table->unsignedBigInteger('department_id')->nullable()->index();
            $table->rememberToken();
            $table->timestamps();

            $table->foreign('department_id')->references('id')->on('departments')->onDelete('restrict');
        });

        // Wallets
        Schema::create('ddc_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ddc_id')->constrained('ddcs')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('type', ['credit', 'debit'])->index();
            $table->string('description')->nullable();
            $table->timestamps();
        });

        Schema::create('adc_wallets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('adc_id')->constrained('adcs')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->enum('type', ['credit', 'debit'])->index();
            $table->string('description')->nullable();
            $table->timestamps();
        });

    }

    public function down(): void
    {
        Schema::dropIfExists('adc_wallets');
        Schema::dropIfExists('ddc_wallets');
        Schema::dropIfExists('adcs');
        Schema::dropIfExists('ddcs');
        Schema::dropIfExists('vendors');
        Schema::dropIfExists('admin_user_roles');
        Schema::dropIfExists('admins');
        
        Schema::dropIfExists('admin_role_permissions');
        Schema::dropIfExists('admin_permissions');
        Schema::dropIfExists('admin_roles');
        
        Schema::dropIfExists('departments');
    }
};
