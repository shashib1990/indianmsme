<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DdcCartItem extends Model
{
    use HasFactory;

    protected $table = 'ddc_cart_items';

    protected $fillable = [
        'ddc_id',
        'seller_id',
        'product_id',
        'quantity',
    ];

    public function ddc()
    {
        return $this->belongsTo(DDC::class, 'ddc_id');
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
