<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdcInventory extends Model
{
    use HasFactory;

    protected $table = 'adc_inventories';

    protected $fillable = [
        'adc_id',
        'inventory_source',
        'product_id',
        'adc_local_product_id',
        'product_name',
        'product_code',
        'barcode',
        'unit_of_measure',
        'quantity',
        'reserved_quantity',
        'sold_quantity',
        'returned_quantity',
        'mrp',
        'purchase_price',
        'landing_cost',
        'msp',
        'adc_price',
        'csp',
        'gst_rate',
        'adc_margin',
        'csp_margin',
        'mfg_date',
        'expire_date',
        'batch_number',
        'adc_order_id',
        'adc_order_item_id',
        'reference_type',
        'reference_id',
        'pos_status',
    ];

    protected $casts = [
        'quantity'          => 'decimal:3',
        'reserved_quantity' => 'decimal:3',
        'sold_quantity'     => 'decimal:3',
        'returned_quantity' => 'decimal:3',
        'mrp'               => 'decimal:2',
        'purchase_price'    => 'decimal:2',
        'landing_cost'      => 'decimal:2',
        'msp'               => 'decimal:2',
        'adc_price'         => 'decimal:2',
        'csp'               => 'decimal:2',
        'gst_rate'          => 'decimal:2',
        'adc_margin'        => 'decimal:2',
        'csp_margin'        => 'decimal:2',
        'mfg_date'          => 'date',
        'expire_date'       => 'date',
    ];

    public function getAvailableQuantityAttribute(): float
    {
        return max(0, (float) $this->quantity - ((float) ($this->reserved_quantity ?? 0)));
    }

    public function getTotalReceivedAttribute(): float
    {
        return (float) $this->quantity + ((float) ($this->sold_quantity ?? 0)) - ((float) ($this->returned_quantity ?? 0));
    }

    public function adc()
    {
        return $this->belongsTo(User::class, 'adc_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function localProduct()
    {
        return $this->belongsTo(AdcLocalProduct::class, 'adc_local_product_id');
    }

    public function adcOrder()
    {
        return $this->belongsTo(AdcOrder::class, 'adc_order_id');
    }

    public function adcOrderItem()
    {
        return $this->belongsTo(AdcOrderItem::class, 'adc_order_item_id');
    }
}
