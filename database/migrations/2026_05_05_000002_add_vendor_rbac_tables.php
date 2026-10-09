<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Update Vendors table
        Schema::table('vendors', function (Blueprint $table) {
            if (!Schema::hasColumn('vendors', 'parent_id')) {
                $table->unsignedBigInteger('parent_id')->nullable()->index()->after('is_active');
            }
            if (!Schema::hasColumn('vendors', 'department_id')) {
                $table->unsignedBigInteger('department_id')->nullable()->index()->after('parent_id');
                $table->foreign('department_id')->references('id')->on('departments')->onDelete('restrict');
            }
        });

        // 2. Vendor Role System Removed
    }

    public function down(): void
    {
        Schema::table('vendors', function (Blueprint $table) {
            $table->dropForeign(['department_id']);
            $table->dropColumn(['parent_id', 'department_id']);
        });
    }
};
