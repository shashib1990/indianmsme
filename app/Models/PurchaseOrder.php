<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $table = 'purchase_orders';

    protected $fillable = [
        'po_number',
        'ddc_id',
        'seller_id',
        'pickup_store_id',
        'ama_code',
        'ssi_applicable',
        'ssi_type',
        'ssi_percentage',
        'fulfillment_type',
        'adc_order_id',
        'total_amount',
        'accepted_amount',
        'seller_total_amount',
        'seller_accepted_amount',
        'status',
        'is_cashback_credited',
        'cashback_percentage',
        'cashback_amount',
        'payment_status',
        'payment_reference',
        'payment_verification_status',
        'seller_payment_status',
        'seller_payment_reference',
        'seller_payment_verification_status',
        'dispatch_status',
        'rejection_reason',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
        'accepted_amount' => 'decimal:2',
        'seller_total_amount' => 'decimal:2',
        'seller_accepted_amount' => 'decimal:2',
        'is_cashback_credited' => 'boolean',
        'cashback_percentage' => 'decimal:2',
        'cashback_amount' => 'decimal:2',
        'status' => \App\Casts\EnumStringCast::class . ':' . \App\Enums\PurchaseOrderStatus::class,
        'payment_status' => \App\Casts\EnumStringCast::class . ':' . \App\Enums\PaymentStatus::class,
        'payment_verification_status' => \App\Casts\EnumStringCast::class . ':' . \App\Enums\VerificationStatus::class,
        'seller_payment_status' => \App\Casts\EnumStringCast::class . ':' . \App\Enums\PaymentStatus::class,
        'seller_payment_verification_status' => \App\Casts\EnumStringCast::class . ':' . \App\Enums\VerificationStatus::class,
        'dispatch_status' => \App\Casts\EnumStringCast::class . ':' . \App\Enums\DispatchStatus::class,
    ];

    public function ddc()
    {
        return $this->belongsTo(DDC::class, 'ddc_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function adcOrder()
    {
        return $this->belongsTo(AdcOrder::class, 'adc_order_id');
    }

    public function pickupStore()
    {
        return $this->belongsTo(PickupStore::class, 'pickup_store_id');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class, 'purchase_order_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'purchase_order_id');
    }

    public function incentiveHolds()
    {
        return $this->hasMany(IncentiveHold::class, 'purchase_order_id');
    }

    public function debitNotes()
    {
        return $this->hasMany(DebitNote::class, 'purchase_order_id');
    }

    public function debitNote()
    {
        return $this->hasOne(DebitNote::class, 'purchase_order_id');
    }

    public function creditNotes()
    {
        return $this->hasMany(CreditNote::class, 'purchase_order_id');
    }

    /**
     * The single SSI/FSI Hold transaction created when the seller accepts the PO.
     * Status stays 'Hold' until stock-in releases it.
     */
    public function holdTransaction()
    {
        return $this->hasOne(IncentiveHold::class, 'purchase_order_id')
            ->where('status', IncentiveHold::STATUS_HOLD);
    }

    /**
     * Calculate the SSI/FSI hold amount based on the locked-in percentage
     * and the seller-accepted amount.
     *
     * @return float
     */
    public function getSsiAmountAttribute(): float
    {
        // If PO is explicitly FSI or not ssi_applicable, return 0.00 for SSI
        if ($this->ssi_type === \App\Models\IncentiveHold::TYPE_FSI || (isset($this->attributes['ssi_applicable']) && !$this->attributes['ssi_applicable'])) {
            return 0.00;
        }

        $holdsSum = (float) \App\Models\IncentiveHold::where('purchase_order_id', $this->id)
            ->where('type', \App\Models\IncentiveHold::TYPE_SSI)
            ->sum('amount');
        if ($holdsSum > 0) {
            return round($holdsSum, 2);
        }

        $seller = $this->seller ?? \App\Models\User::find($this->seller_id);
        if (!$seller || !$seller->sellerProfile?->is_ddc_subscribed) {
            return 0.00;
        }

        $amount = (float) ($this->seller_accepted_amount > 0 ? $this->seller_accepted_amount : $this->seller_total_amount);
        if ($amount <= 0) {
            $amount = (float) ($this->total_amount ?? 0.00);
        }
        if ($amount <= 0) {
            return 0.00;
        }

        $pct = (float) $this->ssi_percentage;
        if ($pct <= 0) {
            $pct = \App\Models\SellerSsiSlab::resolvePercentage($seller->id, $amount);
        }

        if ($pct > 0) {
            return round($amount * ($pct / 100.0), 2);
        }

        return 0.00;
    }

    /**
     * Net amount payable to the seller after deducting SSI/FSI.
     * During HOLD phase this is informational only — the admin pays seller_accepted_amount
     * and the SSI/FSI hold is managed separately via IncentiveHold.
     *
     * @return float
     */
    public function getSellerNetPayableAttribute(): float
    {
        return round((float) $this->seller_accepted_amount - $this->ssi_amount, 2);
    }

    /**
     * Get effective seller amount (seller_accepted_amount if > 0, else seller_total_amount, else fallback to items sum).
     *
     * @return float
     */
    public function getEffectiveSellerAmountAttribute(): float
    {
        if ((float) $this->seller_accepted_amount > 0) {
            return (float) $this->seller_accepted_amount;
        }
        if ((float) $this->seller_total_amount > 0) {
            return (float) $this->seller_total_amount;
        }
        if ($this->relationLoaded('items') && $this->items->count() > 0) {
            $acceptedSum = (float) $this->items->where('status', 'Accepted')->sum('seller_total');
            if ($acceptedSum > 0) {
                return $acceptedSum;
            }
            return (float) $this->items->sum('seller_total');
        }
        return (float) ($this->total_amount ?? 0.00);
    }

    /**
     * Scope to filter purchase orders visible to seller according to ADC Profile show_order setting:
     * Hide if:
     * 1. PO is from ADC via Virtual DDC (fulfillment_type = 'virtual' OR adc_order_id IS NOT NULL with virtual_ddc_id IS NOT NULL)
     * 2. Related ADC Profile show_order = 'No' (or pos_status = 'Inactive')
     * 3. Purchase Order status = 'Pending'
     */
    public function scopeForSellerVisibility($query)
    {
        return $query->where(function ($q) {
            // Keep all orders that are NOT (ADC via Virtual DDC with Pending status and ADC show_order = 'No')
            $q->where('purchase_orders.status', '!=', 'Pending')
              ->orWhereNull('purchase_orders.adc_order_id')
              ->orWhereDoesntHave('adcOrder', function ($aoq) {
                  $aoq->where(function ($vq) {
                      $vq->whereNotNull('virtual_ddc_id')
                         ->orWhere('fulfillment_type', 'virtual');
                  })->whereHas('adc.adcProfile', function ($apq) {
                      $apq->where('show_order', 'No')
                          ->orWhere(function ($spq) {
                              $spq->whereNull('show_order')
                                  ->where('pos_status', 'Inactive');
                          });
                  });
              });
        });
    }

    /**
     * Generate a unique Purchase Order number.
     */
    public static function generatePoNumber(): string
    {
        $month = (int) now()->format('n');
        $year = (int) now()->format('Y');

        if ($month >= 4) {
            $fyStart = $year;
            $fyEnd = $year + 1;
        } else {
            $fyStart = $year - 1;
            $fyEnd = $year;
        }

        $fyStartShort = substr((string)$fyStart, -2);
        $fyEndShort = substr((string)$fyEnd, -2);
        $fyStr = $fyStartShort . '-' . $fyEndShort;
        $prefix = 'PO-' . $fyStr . '-';

        $lastPo = self::where('po_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        $nextSeq = 1;
        if ($lastPo) {
            $parts = explode('-', $lastPo->po_number);
            $lastSeq = (int) end($parts);
            if ($lastSeq > 0) {
                $nextSeq = $lastSeq + 1;
            }
        }

        return $prefix . str_pad((string)$nextSeq, 4, '0', STR_PAD_LEFT);
    }
}
