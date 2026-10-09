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
        Schema::dropIfExists('adc_role_permissions');
        Schema::dropIfExists('adc_user_roles');
        Schema::dropIfExists('vendor_role_permissions');
        Schema::dropIfExists('vendor_user_roles');
        Schema::dropIfExists('ddc_role_permissions');
        Schema::dropIfExists('ddc_user_roles');

        Schema::dropIfExists('adc_permissions');
        Schema::dropIfExists('adc_roles');
        Schema::dropIfExists('vendor_permissions');
        Schema::dropIfExists('vendor_roles');
        Schema::dropIfExists('ddc_permissions');
        Schema::dropIfExists('ddc_roles');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No rollback is needed or feasible as these are deprecated tables being removed.
    }
};
