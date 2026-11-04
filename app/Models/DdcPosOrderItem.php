<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DdcPosOrderItem extends Model
{
    use HasFactory;

    protected $table = 'ddc_pos_order_items';

    protected $fillable = [
        'pos_order_id',
        'ddc_inventory_id',
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

    protected $casts = [
        'unit_price' => 'decimal:2',
        'mrp'        => 'decimal:2',
        'quantity'   => 'decimal:3',
        'returned_quantity' => 'decimal:3',
        'total_price'=> 'decimal:2',
        'mfg_date'   => 'date',
        'expire_date'=> 'date',
    ];

    public function order()
    {
        return $this->belongsTo(DdcPosOrder::class, 'pos_order_id');
    }

    public function inventory()
    {
        return $this->belongsTo(DdcInventory::class, 'ddc_inventory_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function localProduct()
    {
        return $this->belongsTo(DdcLocalProduct::class, 'product_id');
    }

    /**
     * Dynamically resolve HSN Code
     */
    public function getHsnCodeAttribute($value)
    {
        if (!empty($value) && strtoupper(trim($value)) !== 'N/A') {
            return trim($value);
        }

        if ($this->product && !empty($this->product->hsn_code)) {
            return trim($this->product->hsn_code);
        }

        if (!empty($this->product_code)) {
            $prod = Product::where('product_code', $this->product_code)->first();
            if ($prod && !empty($prod->hsn_code)) {
                return trim($prod->hsn_code);
            }
            $local = DdcLocalProduct::where('local_product_code', $this->product_code)->first();
            if ($local && !empty($local->hsn_code)) {
                return trim($local->hsn_code);
            }
        }

        if ($this->inventory) {
            if ($this->inventory->product && !empty($this->inventory->product->hsn_code)) {
                return trim($this->inventory->product->hsn_code);
            }
            if ($this->inventory->localProduct && !empty($this->inventory->localProduct->hsn_code)) {
                return trim($this->inventory->localProduct->hsn_code);
            }
        }

        return $value ?: 'N/A';
    }
}
