<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerWallet extends Model
{
    use HasFactory;

    protected $table = 'seller_wallets';

    protected $fillable = [
        'seller_id',
        'amount',
        'type',
        'description',
        'reference_id',
        'reference_type',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
