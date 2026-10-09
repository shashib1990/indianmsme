<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncentiveRelease extends Model
{
    use HasFactory;

    protected $table = 'incentive_releases';

    protected $fillable = [
        'hold_id',
        'ama_code',
        'adc_order_id',
        'adc_order_item_id',
        'product_id',
        'released_quantity',
        'released_amount',
        'release_date',
        'status',
    ];

    protected $casts = [
        'released_quantity' => 'integer',
        'released_amount'   => 'decimal:2',
        'release_date'      => 'datetime',
    ];

    public function hold()
    {
        return $this->belongsTo(IncentiveHold::class, 'hold_id');
    }

    public function adcOrder()
    {
        return $this->belongsTo(AdcOrder::class, 'adc_order_id');
    }

    public function adcOrderItem()
    {
        return $this->belongsTo(AdcOrderItem::class, 'adc_order_item_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }
}
