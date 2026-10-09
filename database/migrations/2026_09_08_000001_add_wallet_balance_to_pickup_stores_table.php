<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('pickup_stores', 'wallet_balance')) {
            Schema::table('pickup_stores', function (Blueprint $table) {
                $table->decimal('wallet_balance', 12, 2)->default(0.00)->after('branch_name');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('pickup_stores', 'wallet_balance')) {
            Schema::table('pickup_stores', function (Blueprint $table) {
                $table->dropColumn('wallet_balance');
            });
        }
    }
};
