<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdcCreditNote extends Model
{
    use HasFactory;

    protected $table = 'adc_credit_notes';

    protected $fillable = [
        'credit_note_number',
        'adc_debit_note_id',
        'adc_order_id',
        'adc_id',
        'target_type',
        'ddc_id',
        'seller_id',
        'amount',
        'type',
        'status',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public static function generateNumber(): string
    {
        $year = date('y');
        $nextYear = date('y', strtotime('+1 year'));
        $fy = "{$year}-{$nextYear}";
        $prefix = "ACN/{$fy}/";

        $lastNote = self::where('credit_note_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastNote) {
            $parts = explode('/', $lastNote->credit_note_number);
            $lastSeq = (int) end($parts);
            $nextSeq = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '0001';
        }

        return $prefix . $nextSeq;
    }

    public function debitNote()
    {
        return $this->belongsTo(AdcDebitNote::class, 'adc_debit_note_id');
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
