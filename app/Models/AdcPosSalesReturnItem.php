<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdcPosSalesReturnItem extends Model
{
    use HasFactory;

    protected $table = 'adc_pos_sales_return_items';

    protected $fillable = [
        'sales_return_id',
        'pos_order_item_id',
        'adc_inventory_id',
        'product_id',
        'product_name',
        'product_code',
        'unit_price',
        'mrp',
        'returned_quantity',
        'unit_of_measure',
        'gst_rate',
        'taxable_amount',
        'tax_amount',
        'total_amount',
    ];

    protected $casts = [
        'unit_price'        => 'decimal:2',
        'mrp'               => 'decimal:2',
        'returned_quantity' => 'decimal:3',
        'gst_rate'          => 'decimal:2',
        'taxable_amount'    => 'decimal:2',
        'tax_amount'        => 'decimal:2',
        'total_amount'      => 'decimal:2',
    ];

    public function salesReturn()
    {
        return $this->belongsTo(AdcPosSalesReturn::class, 'sales_return_id');
    }

    public function orderItem()
    {
        return $this->belongsTo(AdcPosOrderItem::class, 'pos_order_item_id');
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
}
