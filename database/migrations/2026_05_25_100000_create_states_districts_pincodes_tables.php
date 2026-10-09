<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // States Table
        Schema::create('states', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 10)->nullable();
            $table->enum('status', ['0', '1'])->default('1')->index();
            $table->timestamps();
        });

        // Districts Table (renamed from existing if needed)
        if (!Schema::hasTable('districts')) {
            Schema::create('districts', function (Blueprint $table) {
                $table->id();
                $table->foreignId('state_id')->constrained('states')->onDelete('cascade');
                $table->string('name');
                $table->enum('status', ['0', '1'])->default('1')->index();
                $table->timestamps();

                $table->unique(['state_id', 'name']);
                $table->index('state_id');
            });
        }

        // Pincodes Table
        Schema::create('pincodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('state_id')->constrained('states')->onDelete('cascade');
            $table->foreignId('district_id')->constrained('districts')->onDelete('cascade');
            $table->string('pincode', 10);
            $table->enum('status', ['0', '1'])->default('1')->index();
            $table->timestamps();

            $table->unique(['district_id', 'pincode']);
            $table->index('state_id');
            $table->index('district_id');
            $table->index('pincode');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pincodes');
        Schema::dropIfExists('districts');
        Schema::dropIfExists('states');
    }
};
