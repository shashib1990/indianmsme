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
        Schema::table('ama_transactions', function (Blueprint $table) {
            $table->integer('released_quantity')->default(0)->after('quantity');
            $table->integer('remaining_quantity')->default(0)->after('released_quantity');
            $table->string('status', 50)->default('Hold')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ama_transactions', function (Blueprint $table) {
            $table->dropColumn(['released_quantity', 'remaining_quantity']);
            // Reverting back to enum is database specific, so we keep it as string or change back to enum if needed.
            $table->enum('status', ['Hold', 'Paid', 'Released', 'Cancelled'])->default('Hold')->change();
        });
    }
};
