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
}
