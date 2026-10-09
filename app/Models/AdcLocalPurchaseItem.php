<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdcLocalPurchaseItem extends Model
{
    use HasFactory;

    protected $table = 'adc_local_purchase_items';

    protected $fillable = [
        'adc_local_purchase_id',
        'adc_local_product_id',
        'quantity',
        'purchase_price',
        'freight_share',
        'loading_share',
        'other_share',
        'landing_cost',
        'mrp',
        'csp',
        'msp',
        'gst_rate',
        'batch_number',
        'mfg_date',
        'expire_date',
        'adc_inventory_id',
    ];

    protected $casts = [
        'quantity'       => 'integer',
        'purchase_price' => 'decimal:2',
        'freight_share'  => 'decimal:2',
        'loading_share'  => 'decimal:2',
        'other_share'    => 'decimal:2',
        'landing_cost'   => 'decimal:2',
        'mrp'            => 'decimal:2',
        'csp'            => 'decimal:2',
        'msp'            => 'decimal:2',
        'gst_rate'       => 'decimal:2',
        'mfg_date'       => 'date',
        'expire_date'    => 'date',
    ];

    public function purchase()
    {
        return $this->belongsTo(AdcLocalPurchase::class, 'adc_local_purchase_id');
    }

    public function localProduct()
    {
        return $this->belongsTo(AdcLocalProduct::class, 'adc_local_product_id');
    }

    public function inventory()
    {
        return $this->belongsTo(AdcInventory::class, 'adc_inventory_id');
    }
}
