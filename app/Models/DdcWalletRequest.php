<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DdcWalletRequest extends Model
{
    use HasFactory;

    protected $table = 'ddc_wallet_requests';

    protected $fillable = [
        'ddc_id',
        'amount',
        'utr_number',
        'payment_receipt',
        'status',
        'remarks'
    ];

    public function getStatusAttribute($value)
    {
        return strtoupper($value ?? 'PENDING');
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = strtolower($value ?? 'pending');
    }

    public function ddc()
    {
        return $this->belongsTo(DDC::class, 'ddc_id');
    }
}
