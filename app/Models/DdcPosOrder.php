<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DdcPosOrder extends Model
{
    use HasFactory;

    protected $table = 'ddc_pos_orders';

    protected $fillable = [
        'order_no',
        'ddc_id',
        'mode',
        'financial_year',
        'serial_number',
        'customer_id',
        'adc_user_id',
        'adc_order_id',
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

    public function salesReturns()
    {
        return $this->hasMany(DdcPosSalesReturn::class, 'pos_order_id');
    }

    public function adcUser()
    {
        return $this->belongsTo(User::class, 'adc_user_id');
    }

    public function adcOrder()
    {
        return $this->belongsTo(AdcOrder::class, 'adc_order_id');
    }

    public function invoice()
    {
        return $this->hasOne(Invoice::class, 'pos_order_id')->whereIn('type', ['ddc_pos_retail', 'pos_retail']);
    }

    /**
     * Generate POS Order Number using global Financial Year Order Number series (ORD/26-27/000001).
     */
    public static function generateNextPosInvoiceNumber($ddcId, string $mode = 'retail', $date = null): array
    {
        $carbonDate = $date ? \Carbon\Carbon::parse($date) : now();
        $year = $carbonDate->year;
        
        // Indian financial year: April 1st to March 31st
        if ($carbonDate->month < 4) {
            $startYear = $year - 1;
            $endYear = $year;
        } else {
            $startYear = $year;
            $endYear = $year + 1;
        }
        
        $fyStr = sprintf('%02d-%02d', $startYear % 100, $endYear % 100);

        $ddc = User::find($ddcId);
        $ddcCode = $ddc?->profile?->ddc_code ?: 'DDB001';
        $gstin = $ddc?->profile?->ddc_gst_number ?: ($ddc?->profile?->adpl_gst_number ?: ($ddc?->gst_number ?? 'N/A'));

        $normalizedMode = strtolower($mode) === 'wholesale' ? 'wholesale' : 'retail';

        // Order Number in ddc_pos_orders (order_no) ALWAYS uses global Order Number series (ORD/26-27/000001)
        $orderNo = \App\Services\OrderNumberService::generateOrderNumber($date);

        $nextSerial = 0;
        if (preg_match('/\/(\d+)$/', $orderNo, $matches)) {
            $nextSerial = (int) $matches[1];
        }

        return [
            'order_no'       => $orderNo,
            'financial_year' => $fyStr,
            'serial_number'  => $nextSerial,
            'mode_code'      => ($normalizedMode === 'wholesale' ? 'W' : 'R'),
            'ddc_code'       => $ddcCode,
            'gstin'          => $gstin,
        ];
    }

    protected $casts = [
        'total_items'     => 'decimal:3',
        'subtotal'                => 'decimal:2',
        'discount_amount'         => 'decimal:2',
        'special_discount_amount' => 'decimal:2',
        'tax_amount'              => 'decimal:2',
        'total_amount'    => 'decimal:2',
    ];

    public function ddc()
    {
        return $this->belongsTo(User::class, 'ddc_id');
    }

    public function customer()
    {
        return $this->belongsTo(DdcPosCustomer::class, 'customer_id');
    }

    public function items()
    {
        return $this->hasMany(DdcPosOrderItem::class, 'pos_order_id');
    }
}
