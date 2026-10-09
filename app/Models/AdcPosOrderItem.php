<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdcPosOrderItem extends Model
{
    use HasFactory;

    protected $table = 'adc_pos_order_items';

    protected $fillable = [
        'pos_order_id',
        'adc_inventory_id',
        'product_id',
        'product_name',
        'product_code',
        'barcode',
        'unit_price',
        'mrp',
        'quantity',
        'returned_quantity',
        'total_price',
        'batch_number',
        'mfg_date',
        'expire_date',
    ];

    public function posOrder()
    {
        return $this->belongsTo(AdcPosOrder::class, 'pos_order_id');
    }

    public function inventory()
    {
        return $this->belongsTo(AdcInventory::class, 'adc_inventory_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function localProduct()
    {
        return $this->belongsTo(AdcLocalProduct::class, 'product_id');
    }

    public function returnItems()
    {
        return $this->hasMany(AdcPosSalesReturnItem::class, 'pos_order_item_id');
    }
}
