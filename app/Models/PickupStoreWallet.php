<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PickupStoreWallet extends Model
{
    use HasFactory;

    protected $table = 'pickup_store_wallets';

    protected $fillable = [
        'pickup_store_id',
        'amount',
        'type',
        'description',
        'reference_id',
        'reference_type',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function pickupStore()
    {
        return $this->belongsTo(PickupStore::class, 'pickup_store_id');
    }
}
