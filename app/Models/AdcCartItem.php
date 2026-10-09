<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdcCartItem extends Model
{
    use HasFactory;

    protected $table = 'adc_cart_items';

    protected $fillable = [
        'adc_id',
        'seller_id',
        'product_id',
        'pincode',
        'quantity',
    ];

    public function adc()
    {
        return $this->belongsTo(User::class, 'adc_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
