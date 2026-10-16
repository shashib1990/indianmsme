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
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->enum('show_order', ['Yes', 'No'])->default('No')->change();
            if (!Schema::hasColumn('purchase_orders', 'admin_approved')) {
                $table->boolean('admin_approved')->default(false)->after('show_order');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('purchase_orders', function (Blueprint $table) {
            $table->enum('show_order', ['Yes', 'No'])->default('Yes')->change();
            if (Schema::hasColumn('purchase_orders', 'admin_approved')) {
                $table->dropColumn('admin_approved');
            }
        });
    }
};
