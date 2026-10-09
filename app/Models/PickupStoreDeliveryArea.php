<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PickupStoreDeliveryArea extends Model
{
    protected $table = 'pickup_store_delivery_areas';

    protected $fillable = [
        'pickup_store_id',
        'coverage_type',
        'state_id',
        'district_id',
        'pincode',
        'status',
    ];

    protected $casts = [
        'coverage_type' => \App\Casts\EnumStringCast::class . ':' . \App\Enums\DeliveryCoverageType::class,
        'status' => \App\Casts\EnumStringCast::class . ':' . \App\Enums\StoreStatus::class,
    ];

    public function pickupStore()
    {
        return $this->belongsTo(PickupStore::class, 'pickup_store_id');
    }

    public function state()
    {
        return $this->belongsTo(State::class, 'state_id');
    }

    public function district()
    {
        return $this->belongsTo(District::class, 'district_id');
    }
}
