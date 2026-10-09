<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdcPosOrder extends Model
{
    use HasFactory;

    protected $table = 'adc_pos_orders';

    protected $fillable = [
        'order_no',
        'adc_id',
        'mode',
        'customer_id',
        'customer_name',
        'customer_mobile',
        'total_items',
        'subtotal',
        'discount_amount',
        'special_discount_amount',
        'tax_amount',
        'total_amount',
        'payment_mode',
        'payment_status',
        'return_status',
        'total_returned_amount',
        'remarks',
    ];

    public function adc()
    {
        return $this->belongsTo(User::class, 'adc_id');
    }

    public function customer()
    {
        return $this->belongsTo(AdcPosCustomer::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(AdcPosOrderItem::class, 'pos_order_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'pos_order_id')->whereIn('type', ['adc_pos_retail', 'adc_pos_wholesale']);
    }

    public function salesReturns()
    {
        return $this->hasMany(AdcPosSalesReturn::class, 'pos_order_id');
    }

    /**
     * Generate POS Order Number for ADC.
     */
    public static function generateNextPosInvoiceNumber($adcId, $date = null): array
    {
        $orderNo = \App\Services\OrderNumberService::generateOrderNumber($date);
        return [
            'order_no' => $orderNo,
        ];
    }
}
