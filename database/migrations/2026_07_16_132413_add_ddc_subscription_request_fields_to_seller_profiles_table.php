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
        Schema::table('seller_profiles', function (Blueprint $table) {
            if (!Schema::hasColumn('seller_profiles', 'ddc_subscription_status')) {
                $table->string('ddc_subscription_status')->default('none')->after('is_ddc_subscribed');
            }
            if (!Schema::hasColumn('seller_profiles', 'ddc_subscription_utr')) {
                $table->string('ddc_subscription_utr')->nullable()->after('ddc_subscription_status');
            }
            if (!Schema::hasColumn('seller_profiles', 'ddc_subscription_slip')) {
                $table->string('ddc_subscription_slip')->nullable()->after('ddc_subscription_utr');
            }
            if (!Schema::hasColumn('seller_profiles', 'ddc_subscription_reject_reason')) {
                $table->text('ddc_subscription_reject_reason')->nullable()->after('ddc_subscription_slip');
            }
            if (!Schema::hasColumn('seller_profiles', 'ddc_subscription_submitted_at')) {
                $table->timestamp('ddc_subscription_submitted_at')->nullable()->after('ddc_subscription_reject_reason');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_profiles', function (Blueprint $table) {
            $table->dropColumn([
                'ddc_subscription_status',
                'ddc_subscription_utr',
                'ddc_subscription_slip',
                'ddc_subscription_reject_reason',
                'ddc_subscription_submitted_at'
            ]);
        });
    }
};
