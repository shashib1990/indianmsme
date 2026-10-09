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
        // Pivot table for DDC to Pincode mapping
        Schema::create('ddc_pincodes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ddc_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('pincode_id')->unique()->constrained('pincodes')->onDelete('cascade');
            $table->timestamps();
        });

        // Audit logs for DDC Pincode assignment updates
        Schema::create('ddc_pincode_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ddc_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('pincode_id')->nullable()->constrained('pincodes')->onDelete('set null');
            $table->string('pincode');
            $table->string('action'); // 'ASSIGNED', 'REMOVED'
            $table->foreignId('modified_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ddc_pincode_audit_logs');
        Schema::dropIfExists('ddc_pincodes');
    }
};
