<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdcExcessInvoice extends Model
{
    use HasFactory;

    protected $table = 'adc_excess_invoices';

    protected $fillable = [
        'invoice_number',
        'adc_order_id',
        'adc_id',
        'target_type',
        'ddc_id',
        'seller_id',
        'total_excess_qty',
        'total_excess_amount',
        'proof_images',
        'status',
        'remarks',
    ];

    protected $casts = [
        'proof_images' => 'array',
        'total_excess_qty' => 'integer',
        'total_excess_amount' => 'decimal:2',
    ];

    public static function generateNumber(): string
    {
        $prefix = 'AEI/' . date('y') . '-' . (date('y') + 1) . '/';
        $latest = self::where('invoice_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastNum = (int) substr($latest->invoice_number, -4);
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        return $prefix . $nextNum;
    }

    public function order()
    {
        return $this->belongsTo(AdcOrder::class, 'adc_order_id');
    }

    public function adcOrder()
    {
        return $this->belongsTo(AdcOrder::class, 'adc_order_id');
    }

    public function adc()
    {
        return $this->belongsTo(User::class, 'adc_id');
    }

    public function ddc()
    {
        return $this->belongsTo(User::class, 'ddc_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }
}
