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
        // 1. Add state_id and district_id to seller_profiles
        Schema::table('seller_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('state_id')->nullable()->after('pincode');
            $table->unsignedBigInteger('district_id')->nullable()->after('state_id');

            $table->foreign('state_id')->references('id')->on('states')->onDelete('set null');
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('set null');
        });

        // 2. Add state_id, district_id, adpl_state_id, adpl_district_id to ddc_profiles
        Schema::table('ddc_profiles', function (Blueprint $table) {
            $table->unsignedBigInteger('state_id')->nullable()->after('pincode');
            $table->unsignedBigInteger('district_id')->nullable()->after('state_id');
            $table->unsignedBigInteger('adpl_state_id')->nullable()->after('adpl_pincode');
            $table->unsignedBigInteger('adpl_district_id')->nullable()->after('adpl_state_id');

            $table->foreign('state_id')->references('id')->on('states')->onDelete('set null');
            $table->foreign('district_id')->references('id')->on('districts')->onDelete('set null');
            $table->foreign('adpl_state_id')->references('id')->on('states')->onDelete('set null');
            $table->foreign('adpl_district_id')->references('id')->on('districts')->onDelete('set null');
        });

        // 3. Migrate existing data for seller_profiles
        $sellerProfiles = DB::table('seller_profiles')->get();
        foreach ($sellerProfiles as $profile) {
            $stateId = null;
            $districtId = null;

            if ($profile->state) {
                if (is_numeric($profile->state)) {
                    $stateId = (int) $profile->state;
                } else {
                    $state = DB::table('states')->where('name', $profile->state)->first();
                    $stateId = $state ? $state->id : null;
                }
            }

            if ($profile->district) {
                if (is_numeric($profile->district)) {
                    $districtId = (int) $profile->district;
                } else {
                    $query = DB::table('districts')->where('name', $profile->district);
                    if ($stateId) {
                        $query->where('state_id', $stateId);
                    }
                    $district = $query->first();
                    $districtId = $district ? $district->id : null;
                }
            }

            DB::table('seller_profiles')
                ->where('id', $profile->id)
                ->update([
                    'state_id' => $stateId,
                    'district_id' => $districtId,
                ]);
        }

        // 4. Migrate existing data for ddc_profiles
        $ddcProfiles = DB::table('ddc_profiles')->get();
        foreach ($ddcProfiles as $profile) {
            $stateId = null;
            $districtId = null;
            $adplStateId = null;
            $adplDistrictId = null;

            // Resolve standard location
            if ($profile->state) {
                if (is_numeric($profile->state)) {
                    $stateId = (int) $profile->state;
                } else {
                    $state = DB::table('states')->where('name', $profile->state)->first();
                    $stateId = $state ? $state->id : null;
                }
            }

            if ($profile->district) {
                if (is_numeric($profile->district)) {
                    $districtId = (int) $profile->district;
                } else {
                    $query = DB::table('districts')->where('name', $profile->district);
                    if ($stateId) {
                        $query->where('state_id', $stateId);
                    }
                    $district = $query->first();
                    $districtId = $district ? $district->id : null;
                }
            }

            // Resolve ADPL location
            if ($profile->adpl_state) {
                if (is_numeric($profile->adpl_state)) {
                    $adplStateId = (int) $profile->adpl_state;
                } else {
                    $state = DB::table('states')->where('name', $profile->adpl_state)->first();
                    $adplStateId = $state ? $state->id : null;
                }
            }

            if ($profile->adpl_district) {
                if (is_numeric($profile->adpl_district)) {
                    $adplDistrictId = (int) $profile->adpl_district;
                } else {
                    $query = DB::table('districts')->where('name', $profile->adpl_district);
                    if ($adplStateId) {
                        $query->where('state_id', $adplStateId);
                    }
                    $district = $query->first();
                    $adplDistrictId = $district ? $district->id : null;
                }
            }

            DB::table('ddc_profiles')
                ->where('id', $profile->id)
                ->update([
                    'state_id' => $stateId,
                    'district_id' => $districtId,
                    'adpl_state_id' => $adplStateId,
                    'adpl_district_id' => $adplDistrictId,
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('seller_profiles', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
            $table->dropForeign(['district_id']);
            $table->dropColumn(['state_id', 'district_id']);
        });

        Schema::table('ddc_profiles', function (Blueprint $table) {
            $table->dropForeign(['state_id']);
            $table->dropForeign(['district_id']);
            $table->dropForeign(['adpl_state_id']);
            $table->dropForeign(['adpl_district_id']);
            $table->dropColumn(['state_id', 'district_id', 'adpl_state_id', 'adpl_district_id']);
        });
    }
};
