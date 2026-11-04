<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdcOrderItem extends Model
{
    use HasFactory;

    protected $table = 'adc_order_items';

    protected $fillable = [
        'adc_order_id',
        'seller_id',
        'product_id',
        'product_name',
        'product_code',
        'hsn_code',
        'category_name',
        'brand_name',
        'unit_of_measure',
        'quantity',
        'mrp',
        'seller_price',
        'price',
        'msp',
        'csp',
        'taxable_amount',
        'gst_rate',
        'gst_amount',
        'total',
        'ddc_margin',
        'adc_margin',
        'csp_margin',
        'mbp_percentage',
        'mbp_amount',
        'mbp_90_share',
        'mbp_10_share',
        'mbp_38_share',
        'mbp_62_share',
        'fulfillment_type',
        'ddc_id',
        'virtual_ddc_id',
        'purchase_order_id',
        'is_stocked_in',
        'stocked_in_at',
        'received_mrp',
        'supply_qty',
        'damage_qty',
        'shortage_qty',
        'excess_qty',
        'batch_number',
        'mfg_date',
        'expire_date',
        'status',
        'remarks',
    ];

    protected $casts = [
        'is_stocked_in'  => 'boolean',
        'stocked_in_at'  => 'datetime',
        'received_mrp'   => 'decimal:2',
        'mfg_date'       => 'date',
        'expire_date'    => 'date',
        'mrp'            => 'decimal:2',
        'seller_price'   => 'decimal:2',
        'price'          => 'decimal:2',
        'msp'            => 'decimal:2',
        'csp'            => 'decimal:2',
        'taxable_amount' => 'decimal:2',
        'gst_rate'       => 'decimal:2',
        'gst_amount'     => 'decimal:2',
        'total'          => 'decimal:2',
        'ddc_margin'     => 'decimal:2',
        'adc_margin'     => 'decimal:2',
        'csp_margin'     => 'decimal:2',
        'mbp_percentage' => 'decimal:2',
        'mbp_amount'     => 'decimal:2',
        'mbp_90_share'   => 'decimal:2',
        'mbp_10_share'   => 'decimal:2',
        'mbp_38_share'   => 'decimal:2',
        'mbp_62_share'   => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(AdcOrder::class, 'adc_order_id');
    }

    public function adcOrder()
    {
        return $this->belongsTo(AdcOrder::class, 'adc_order_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function ddc()
    {
        return $this->belongsTo(User::class, 'ddc_id');
    }

    public function virtualDdc()
    {
        return $this->belongsTo(User::class, 'virtual_ddc_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    /**
     * Dynamically resolve HSN Code if missing in database
     */
    public function getHsnCodeAttribute($value)
    {
        if (!empty($value) && strtoupper(trim($value)) !== 'N/A') {
            return trim($value);
        }

        // 1. Check Product relationship if product_id is set
        if ($this->product && !empty($this->product->hsn_code)) {
            return trim($this->product->hsn_code);
        }

        // 2. Lookup by product_code in products table
        if (!empty($this->product_code)) {
            $prod = Product::where('product_code', $this->product_code)->first();
            if ($prod && !empty($prod->hsn_code)) {
                return trim($prod->hsn_code);
            }

            // 3. Lookup by local_product_code in ddc_local_products
            $ddcLocal = DdcLocalProduct::where('local_product_code', $this->product_code)->first();
            if ($ddcLocal && !empty($ddcLocal->hsn_code)) {
                return trim($ddcLocal->hsn_code);
            }

            // 4. Lookup by local_product_code in adc_local_products
            $adcLocal = AdcLocalProduct::where('local_product_code', $this->product_code)->first();
            if ($adcLocal && !empty($adcLocal->hsn_code)) {
                return trim($adcLocal->hsn_code);
            }

            // 5. Lookup in ddc_inventories by product_code
            $ddcInv = DdcInventory::with(['product', 'localProduct'])->where('product_code', $this->product_code)->first();
            if ($ddcInv) {
                if ($ddcInv->product && !empty($ddcInv->product->hsn_code)) {
                    return trim($ddcInv->product->hsn_code);
                }
                if ($ddcInv->localProduct && !empty($ddcInv->localProduct->hsn_code)) {
                    return trim($ddcInv->localProduct->hsn_code);
                }
                if (!empty($ddcInv->barcode)) {
                    $prodByBc = Product::where('barcode', $ddcInv->barcode)->whereNotNull('hsn_code')->first();
                    if ($prodByBc && !empty($prodByBc->hsn_code)) {
                        return trim($prodByBc->hsn_code);
                    }
                }
            }
        }

        // 6. Lookup by product_name
        if (!empty($this->product_name)) {
            $prodByName = Product::where('product_name', $this->product_name)->whereNotNull('hsn_code')->first();
            if ($prodByName && !empty($prodByName->hsn_code)) {
                return trim($prodByName->hsn_code);
            }

            $localByName = DdcLocalProduct::where('product_name', $this->product_name)->whereNotNull('hsn_code')->first();
            if ($localByName && !empty($localByName->hsn_code)) {
                return trim($localByName->hsn_code);
            }
        }

        return $value ?: 'N/A';
    }

    /**
     * Dynamically resolve product model even if product_id is null
     */
    public function getResolvedProductAttribute()
    {
        if ($this->relationLoaded('product') && $this->getRelation('product')) {
            return $this->getRelation('product');
        }
        if ($this->product_id) {
            $p = Product::find($this->product_id);
            if ($p) return $p;
        }
        if (!empty($this->product_code)) {
            $p = Product::where('product_code', $this->product_code)->first();
            if ($p) return $p;
        }
        if (!empty($this->product_name)) {
            $p = Product::where('product_name', $this->product_name)->first();
            if ($p) return $p;
        }
        return null;
    }
}
