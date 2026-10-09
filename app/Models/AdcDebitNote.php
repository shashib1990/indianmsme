<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdcDebitNote extends Model
{
    use HasFactory;

    protected $table = 'adc_debit_notes';

    protected $fillable = [
        'debit_note_number',
        'adc_order_id',
        'adc_id',
        'target_type',
        'ddc_id',
        'seller_id',
        'discrepancy_type',
        'order_qty',
        'received_qty',
        'shortage_qty',
        'damage_qty',
        'excess_qty',
        'unit_price',
        'total_discrepancy_amount',
        'seller_amount',
        'final_amount',
        'proof_images',
        'seller_agreed_qty',
        'seller_invoice_file',
        'status',
        'seller_remarks',
        'adpl_remarks',
    ];

    protected $casts = [
        'proof_images' => 'array',
        'total_discrepancy_amount' => 'decimal:2',
        'seller_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'unit_price' => 'decimal:2',
    ];

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

    public function items()
    {
        return $this->hasMany(AdcDebitNoteItem::class, 'adc_debit_note_id');
    }

    public function creditNote()
    {
        return $this->hasOne(AdcCreditNote::class, 'adc_debit_note_id');
    }

    public static function generateNumber(): string
    {
        $prefix = 'ADN/' . date('y') . '-' . (date('y') + 1) . '/';
        $latest = self::where('debit_note_number', 'like', $prefix . '%')
            ->orderBy('id', 'desc')
            ->first();

        if ($latest) {
            $lastNum = (int) substr($latest->debit_note_number, -4);
            $nextNum = str_pad($lastNum + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextNum = '0001';
        }

        return $prefix . $nextNum;
    }
}
