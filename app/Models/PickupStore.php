<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Notifications\Notifiable;

class PickupStore extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $table = 'pickup_stores';

    protected $fillable = [
        'owner_type',
        'owner_id',
        'pickup_store_code',
        'pickup_store_name',
        'contact_person',
        'mobile',
        'email',
        'password',
        'gst_number',
        'address',
        'landmark',
        'state_id',
        'district_id',
        'pincode',
        'status',
        'can_receive_payment_and_dispatch',
        'account_holder_name',
        'bank_account_number',
        'ifsc_code',
        'bank_name',
        'branch_name',
        'delivery_coverage_type',
        'wallet_balance',
    ];

    protected $casts = [
        'can_receive_payment_and_dispatch' => 'boolean',
        'wallet_balance' => 'decimal:2',
        'owner_type' => \App\Casts\EnumStringCast::class . ':' . \App\Enums\StoreOwnerType::class,
        'status' => \App\Casts\EnumStringCast::class . ':' . \App\Enums\StoreStatus::class,
        'delivery_coverage_type' => \App\Casts\EnumStringCast::class . ':' . \App\Enums\DeliveryCoverageType::class,
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function stateRelation()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function districtRelation()
    {
        return $this->belongsTo(District::class, 'district_id');
    }

    public function getStateAttribute($value)
    {
        return $this->stateRelation?->name ?? $value;
    }

    public function getDistrictAttribute($value)
    {
        return $this->districtRelation?->name ?? $value;
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function deliveryAreas()
    {
        return $this->hasMany(PickupStoreDeliveryArea::class, 'pickup_store_id');
    }

    public function walletTransactions()
    {
        return $this->hasMany(PickupStoreWallet::class, 'pickup_store_id');
    }

    public function walletRequests()
    {
        return $this->hasMany(PickupStoreWalletRequest::class, 'pickup_store_id');
    }

    /**
     * Generate unique Pickup Store Code like PSC1001
     */
    public static function generatePickupStoreCode(?int $ownerId = null): string
    {
        $query = self::query();
        if ($ownerId) {
            $query->where('owner_id', $ownerId);
        }

        $latestStore = $query->orderByRaw('CAST(SUBSTRING(pickup_store_code, 4) AS UNSIGNED) DESC')
            ->first();

        if ($latestStore && preg_match('/^PSC(\d+)$/', $latestStore->pickup_store_code, $matches)) {
            $nextNumber = ((int) $matches[1]) + 1;
            if ($nextNumber < 1001) {
                $nextNumber = 1001;
            }
        } else {
            $nextNumber = 1001;
        }

        $code = 'PSC' . $nextNumber;

        $existsCheck = function ($c) use ($ownerId) {
            $q = self::where('pickup_store_code', $c);
            if ($ownerId) {
                $q->where('owner_id', $ownerId);
            }
            return $q->exists();
        };

        while ($existsCheck($code)) {
            $nextNumber++;
            $code = 'PSC' . $nextNumber;
        }

        return $code;
    }
}
