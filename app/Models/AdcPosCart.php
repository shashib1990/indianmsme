<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdcPosCart extends Model
{
    use HasFactory;

    protected $table = 'adc_pos_carts';

    protected $fillable = [
        'adc_id',
        'mode',
        'customer_id',
        'customer_name',
        'customer_mobile',
        'status',
        'cart_data',
    ];

    protected $casts = [
        'cart_data' => 'array',
    ];

    public function adc()
    {
        return $this->belongsTo(User::class, 'adc_id');
    }

    public function customer()
    {
        return $this->belongsTo(AdcPosCustomer::class, 'customer_id');
    }
}
