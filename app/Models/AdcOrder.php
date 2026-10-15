<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdcOrder extends Model
{
    use HasFactory;

    protected $table = 'adc_orders';

    protected $fillable = [
        'order_number',
        'adc_id',
        'advisor_code',
        'total_amount',
        'subtotal_excl_gst',
        'total_gst',
        'total_mbp_coins',
        'status',
        'payment_status',
        'payment_method',
        'fulfillment_type',
        'selected_ddc_id',
        'virtual_ddc_id',
        'assigned_ddc_id',
        'routing_pincode',
        'delivery_pincode',
        'delivery_state',
        'delivery_district',
        'routing_logic',
        'shipping_address',
        'notes',
        'received_invoice_number',
        'received_invoice_date',
        'received_invoice_file',
        'is_cashback_credited',
        'cashback_percentage',
        'cashback_amount',
    ];

    protected $casts = [
        'total_amount'          => 'decimal:2',
        'subtotal_excl_gst'     => 'decimal:2',
        'total_gst'             => 'decimal:2',
        'total_mbp_coins'       => 'decimal:2',
        'received_invoice_date' => 'date',
        'is_cashback_credited'  => 'boolean',
        'cashback_percentage'   => 'decimal:2',
        'cashback_amount'       => 'decimal:2',
    ];

    public function adc()
    {
        return $this->belongsTo(ADC::class, 'adc_id');
    }

    public function selectedDdc()
    {
        return $this->belongsTo(User::class, 'selected_ddc_id');
    }

    public function virtualDdc()
    {
        return $this->belongsTo(User::class, 'virtual_ddc_id');
    }

    public function assignedDdc()
    {
        return $this->belongsTo(User::class, 'assigned_ddc_id');
    }

    public function items()
    {
        return $this->hasMany(AdcOrderItem::class, 'adc_order_id');
    }

    public function purchaseOrders()
    {
        return $this->hasMany(PurchaseOrder::class, 'adc_order_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'adc_order_id')->latestOfMany();
    }

    public function debitNote()
    {
        return $this->hasOne(AdcDebitNote::class, 'adc_order_id')->latestOfMany();
    }

    public function excessInvoice()
    {
        return $this->hasOne(AdcExcessInvoice::class, 'adc_order_id')->latestOfMany();
    }

    /**
     * Get unique seller company/business names for this ADC order.
     */
    public function getSellerNamesAttribute(): array
    {
        $sellers = collect();
        if ($this->relationLoaded('purchaseOrders')) {
            foreach ($this->purchaseOrders as $po) {
                if ($po->seller) {
                    $sellers->push($po->seller->company_name ?: ($po->seller->sellerProfile?->company_name ?: $po->seller->name));
                }
            }
        } else {
            $pos = $this->purchaseOrders()->with(['seller.sellerProfile'])->get();
            foreach ($pos as $po) {
                if ($po->seller) {
                    $sellers->push($po->seller->company_name ?: ($po->seller->sellerProfile?->company_name ?: $po->seller->name));
                }
            }
        }

        if ($sellers->isEmpty()) {
            if ($this->relationLoaded('items')) {
                foreach ($this->items as $item) {
                    if ($item->seller) {
                        $sellers->push($item->seller->company_name ?: ($item->seller->sellerProfile?->company_name ?: $item->seller->name));
                    }
                }
            } else {
                $items = $this->items()->with(['seller.sellerProfile'])->get();
                foreach ($items as $item) {
                    if ($item->seller) {
                        $sellers->push($item->seller->company_name ?: ($item->seller->sellerProfile?->company_name ?: $item->seller->name));
                    }
                }
            }
        }

        return $sellers->filter()->unique()->values()->all();
    }

    /**
     * Get formatted display string of seller names.
     */
    public function getSellerNameDisplayAttribute(): string
    {
        $names = $this->seller_names;
        return !empty($names) ? implode(', ', $names) : '—';
    }

    /**
     * Scope to filter ADC orders visible to DDC according to Admin Approval Toggle:
     * Legacy ADC Profile Yes/No conditions removed. Visibility controlled by Admin Approval Toggle.
     */
    public function scopeForDdcVisibility($query)
    {
        return $query->where(function ($q) {
            // Hide if order has purchase orders and none of them are approved by admin
            $q->whereDoesntHave('purchaseOrders')
              ->orWhereHas('purchaseOrders', function ($poq) {
                  $poq->where('show_order', 'Yes')
                      ->orWhere('admin_approved', true);
              });
        });
    }

    /**
     * Generate a unique Order number in ORD/26-27/000001 format using global OrderNumberService.
     */
    public static function generateOrderNumber($date = null): string
    {
        return \App\Services\OrderNumberService::generateOrderNumber($date);
    }
}
