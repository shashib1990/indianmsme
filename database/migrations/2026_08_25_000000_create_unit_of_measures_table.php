<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('unit_of_measures', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('short_code')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });

        // Pre-populate standard units
        $defaultUnits = [
            ['name' => 'Piece', 'short_code' => 'pc', 'sort_order' => 1],
            ['name' => 'Box', 'short_code' => 'box', 'sort_order' => 2],
            ['name' => 'Pack', 'short_code' => 'pk', 'sort_order' => 3],
            ['name' => 'Bottle', 'short_code' => 'btl', 'sort_order' => 4],
            ['name' => 'Kg', 'short_code' => 'kg', 'sort_order' => 5],
            ['name' => 'Gram', 'short_code' => 'gm', 'sort_order' => 6],
            ['name' => 'Litre', 'short_code' => 'ltr', 'sort_order' => 7],
            ['name' => 'ML', 'short_code' => 'ml', 'sort_order' => 8],
            ['name' => 'Pair', 'short_code' => 'pr', 'sort_order' => 9],
            ['name' => 'Set', 'short_code' => 'set', 'sort_order' => 10],
            ['name' => 'Meter', 'short_code' => 'm', 'sort_order' => 11],
            ['name' => 'Dozen', 'short_code' => 'dz', 'sort_order' => 12],
            ['name' => 'Container', 'short_code' => 'cnt', 'sort_order' => 13],
            ['name' => 'Other', 'short_code' => 'oth', 'sort_order' => 14],
        ];

        $now = now();
        foreach ($defaultUnits as $unit) {
            DB::table('unit_of_measures')->insert([
                'name' => $unit['name'],
                'short_code' => $unit['short_code'],
                'status' => true,
                'sort_order' => $unit['sort_order'],
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('unit_of_measures');
    }
};
