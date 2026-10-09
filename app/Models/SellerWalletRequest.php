<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SellerWalletRequest extends Model
{
    use HasFactory;

    protected $table = 'seller_wallet_requests';

    protected $fillable = [
        'seller_id',
        'type',
        'amount',
        'utr_number',
        'payment_receipt',
        'account_holder_name',
        'bank_account_number',
        'bank_name',
        'ifsc_code',
        'branch_name',
        'status',
        'remarks',
        'processed_by',
        'processed_at',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    public function getTypeAttribute($value)
    {
        $val = strtolower($value ?? 'topup');
        if (in_array($val, ['top-up', 'top_up', 'topup', 'credit'])) {
            return 'topup';
        }
        return 'withdrawal';
    }

    public function setTypeAttribute($value)
    {
        $val = strtolower($value ?? 'topup');
        if (in_array($val, ['top-up', 'top_up', 'topup', 'credit'])) {
            $val = 'topup';
        } else {
            $val = 'withdrawal';
        }
        $this->attributes['type'] = $val;
    }

    public function getStatusAttribute($value)
    {
        return strtoupper($value ?? 'PENDING');
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = strtolower($value ?? 'pending');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function processor()
    {
        return $this->belongsTo(Admin::class, 'processed_by');
    }
}
