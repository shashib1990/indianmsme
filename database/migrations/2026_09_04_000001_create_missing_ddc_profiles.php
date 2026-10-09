<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\DdcProfile;

return new class extends Migration
{
    public function up(): void
    {
        $unprofiledDdcs = User::where('role', 'ddc')->whereDoesntHave('profile')->get();

        foreach ($unprofiledDdcs as $user) {
            DdcProfile::create([
                'user_id' => $user->id,
                'ddc_code' => 'DDC-' . strtoupper(Str::random(6)),
                'ddc_owner_name' => $user->name,
                'official_email' => $user->email,
                'phone' => $user->mobile,
                'email' => $user->email,
                'kyc_status' => 'APPROVED',
                'wallet_balance' => 0,
                'type' => 'physical',
            ]);
        }
    }

    public function down(): void
    {
        // Data repair migration down action
    }
};
