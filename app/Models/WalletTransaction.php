<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WalletTransaction extends Model
{
    use HasFactory;

    protected $table = 'seller_wallets';

    protected $fillable = [
        'seller_id',
        'amount',
        'type',
        'description',
        'reference_id'
    ];


    public function owner()
    {
        return $this->morphTo();
    }
}
