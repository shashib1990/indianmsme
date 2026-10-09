<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdcPosSalesReturn extends Model
{
    use HasFactory;

    protected $table = 'adc_pos_sales_returns';

    protected $fillable = [
        'return_number',
        'pos_order_id',
        'invoice_id',
        'adc_id',
        'customer_id',
        'customer_name',
        'customer_mobile',
        'total_items',
        'total_qty',
        'subtotal',
        'tax_amount',
        'discount_amount',
        'total_return_amount',
        'refund_mode',
        'return_reason',
    ];

    protected $casts = [
        'total_items' => 'decimal:3',
        'total_qty' => 'decimal:3',
        'subtotal' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'total_return_amount' => 'decimal:2',
    ];

    public function order()
    {
        return $this->belongsTo(AdcPosOrder::class, 'pos_order_id');
    }

    public function invoice()
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

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
        return $this->hasMany(AdcPosSalesReturnItem::class, 'sales_return_id');
    }

    /**
     * Generate unique Return Number: SR-ADC-YYYYMMDD-XXXX
     */
    public static function generateReturnNumber(): string
    {
        $prefix = 'SR-ADC-' . date('Ymd') . '-';
        $last = self::where('return_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($last) {
            $lastNum = (int) substr($last->return_number, strlen($prefix));
            $nextNum = $lastNum + 1;
        } else {
            $nextNum = 1;
        }

        return $prefix . str_pad($nextNum, 4, '0', STR_PAD_LEFT);
    }
}
