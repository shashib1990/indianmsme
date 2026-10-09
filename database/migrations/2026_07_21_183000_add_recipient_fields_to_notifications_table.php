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
        Schema::table('notifications', function (Blueprint $table) {
            if (!Schema::hasColumn('notifications', 'recipient_type')) {
                $table->string('recipient_type')->nullable()->after('id')->index();
            }
            if (!Schema::hasColumn('notifications', 'recipient_id')) {
                $table->unsignedBigInteger('recipient_id')->nullable()->after('recipient_type')->index();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('notifications', function (Blueprint $table) {
            if (Schema::hasColumn('notifications', 'recipient_type')) {
                $table->dropColumn('recipient_type');
            }
            if (Schema::hasColumn('notifications', 'recipient_id')) {
                $table->dropColumn('recipient_id');
            }
        });
    }
};
