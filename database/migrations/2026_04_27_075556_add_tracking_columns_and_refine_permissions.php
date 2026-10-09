<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $tables = [
            'departments',
            'admin_roles',
            'admin_permissions',
            'admins',
            'vendors',
            'ddcs',
            'adcs'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                if (!Schema::hasColumn($table->getTable(), 'created_by')) {
                    $table->unsignedBigInteger('created_by')->nullable()->after('created_at');
                }
                if (!Schema::hasColumn($table->getTable(), 'updated_by')) {
                    $table->unsignedBigInteger('updated_by')->nullable()->after('updated_at');
                }
                if (!Schema::hasColumn($table->getTable(), 'deleted_by')) {
                    $table->unsignedBigInteger('deleted_by')->nullable();
                }
                if (!Schema::hasColumn($table->getTable(), 'deleted_at')) {
                    $table->softDeletes();
                }
            });
        }
    }

    public function down(): void
    {
        $tables = [
            'departments',
            'admin_roles',
            'admin_permissions',
            'admins',
            'vendors',
            'ddcs',
            'adcs'
        ];

        foreach ($tables as $tableName) {
            Schema::table($tableName, function (Blueprint $table) {
                $table->dropColumn(['created_by', 'updated_by', 'deleted_by', 'deleted_at']);
            });
        }
    }
};
