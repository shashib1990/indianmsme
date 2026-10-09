<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DdcPosCart extends Model
{
    use HasFactory;

    protected $table = 'ddc_pos_carts';

    protected $fillable = [
        'ddc_id',
        'mode',
        'status',
        'adc_id',
        'customer_id',
        'customer_name',
        'customer_mobile',
        'cart_data',
    ];

    protected $casts = [
        'cart_data' => 'array',
    ];

    public function ddcUser()
    {
        return $this->belongsTo(User::class, 'ddc_id');
    }

    public function adcUser()
    {
        return $this->belongsTo(User::class, 'adc_id');
    }

    public function customer()
    {
        return $this->belongsTo(DdcPosCustomer::class, 'customer_id');
    }
}
