<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Copy ex_factory_price values to seller_price_incl_gst if they are null
        \DB::statement("UPDATE products SET seller_price_incl_gst = ex_factory_price WHERE seller_price_incl_gst IS NULL");

        // 2. Drop ex_factory_price column
        if (Schema::hasColumn('products', 'ex_factory_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->dropColumn('ex_factory_price');
            });
        }
    }

    public function down(): void
    {
        if (!Schema::hasColumn('products', 'ex_factory_price')) {
            Schema::table('products', function (Blueprint $table) {
                $table->decimal('ex_factory_price', 10, 2)->nullable();
            });
            \DB::statement("UPDATE products SET ex_factory_price = seller_price_incl_gst");
        }
    }
};
