<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SubSellerProduct extends Model
{
    use HasFactory;

    protected $table = 'sub_seller_products';

    protected $fillable = [
        'sub_seller_id',
        'product_id',
        'margin',
        'final_price',
        'base_cost',
        'company_margin_percentage',
        'company_margin_amount',
        'admin_margin_percentage',
        'admin_margin_amount',
        'ddc_margin_percentage',
        'ddc_margin_amount',
        'price_before_gst',
        'company_gst_amount',
        'seller_gst_amount',
        'adc_purchase_price',
        'ddc_purchase_price',
        'available_margin',
        'mbp_percentage',
        'mbp_amount',
        'mbp_90_share',
        'mbp_10_share',
        'mbp_38_share',
        'mbp_62_share',
        'adc_final_margin',
        'status',
    ];

    protected $casts = [
        'margin' => 'decimal:2',
        'final_price' => 'decimal:2',
        'base_cost' => 'decimal:2',
        'company_margin_percentage' => 'decimal:2',
        'company_margin_amount' => 'decimal:2',
        'admin_margin_percentage' => 'decimal:2',
        'admin_margin_amount' => 'decimal:2',
        'ddc_margin_percentage' => 'decimal:2',
        'ddc_margin_amount' => 'decimal:2',
        'price_before_gst' => 'decimal:2',
        'company_gst_amount' => 'decimal:2',
        'seller_gst_amount' => 'decimal:2',
        'adc_purchase_price' => 'decimal:2',
        'ddc_purchase_price' => 'decimal:2',
        'available_margin' => 'decimal:2',
        'mbp_percentage' => 'decimal:2',
        'mbp_amount' => 'decimal:2',
        'mbp_90_share' => 'decimal:2',
        'mbp_10_share' => 'decimal:2',
        'mbp_38_share' => 'decimal:2',
        'mbp_62_share' => 'decimal:2',
        'adc_final_margin' => 'decimal:2',
    ];

    public function subSeller()
    {
        return $this->belongsTo(User::class, 'sub_seller_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    /**
     * Recalculate pricing for this sub-seller product assignment
     */
    public function recalculatePricing()
    {
        $product = $this->product;
        if (!$product) {
            return;
        }

        $pricing = \App\Services\PricingService::calculate(
            (float) $this->final_price,
            (float) $product->gst_rate,
            (float) $product->mrp,
            $product->admin_margin_percentage,
            $product->ddc_margin_percentage,
            $product->mbp_percentage
        );

        // Only update fillable properties to prevent column-not-found errors in unguarded contexts
        $fillablePricing = array_intersect_key($pricing, array_flip($this->getFillable()));

        $this->update($fillablePricing);
    }
}
