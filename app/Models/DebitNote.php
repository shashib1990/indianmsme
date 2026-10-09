<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DebitNote extends Model
{
    use HasFactory;

    protected $table = 'debit_notes';

    protected $fillable = [
        'debit_note_number',
        'purchase_order_id',
        'purchase_order_item_id',
        'adc_order_id',
        'adc_order_item_id',
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
        'proof_images',
        'seller_agreed_qty',
        'final_amount',
        'seller_invoice_file',
        'status',
        'seller_remarks',
        'adpl_remarks',
    ];

    protected $casts = [
        'proof_images' => 'array',
        'unit_price' => 'decimal:2',
        'total_discrepancy_amount' => 'decimal:2',
        'seller_amount' => 'decimal:2',
        'final_amount' => 'decimal:2',
        'order_qty' => 'integer',
        'received_qty' => 'integer',
        'shortage_qty' => 'integer',
        'damage_qty' => 'integer',
        'excess_qty' => 'integer',
        'seller_agreed_qty' => 'integer',
    ];

    public static function generateNumber(): string
    {
        $year = date('y');
        $nextYear = date('y', strtotime('+1 year'));
        $fy = "{$year}-{$nextYear}";
        $prefix = "DN/{$fy}/";

        $lastNote = self::where('debit_note_number', 'like', "{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        if ($lastNote) {
            $parts = explode('/', $lastNote->debit_note_number);
            $lastSeq = (int) end($parts);
            $nextSeq = str_pad($lastSeq + 1, 4, '0', STR_PAD_LEFT);
        } else {
            $nextSeq = '0001';
        }

        return $prefix . $nextSeq;
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    }

    public function adcOrder()
    {
        return $this->belongsTo(AdcOrder::class, 'adc_order_id');
    }

    public function adcOrderItem()
    {
        return $this->belongsTo(AdcOrderItem::class, 'adc_order_item_id');
    }

    public function adc()
    {
        return $this->belongsTo(User::class, 'adc_id');
    }

    public function items()
    {
        return $this->hasMany(DebitNoteItem::class, 'debit_note_id');
    }

    public function ddc()
    {
        return $this->belongsTo(User::class, 'ddc_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function creditNote()
    {
        return $this->hasOne(CreditNote::class, 'debit_note_id');
    }

    public function creditNotes()
    {
        return $this->hasMany(CreditNote::class, 'debit_note_id');
    }
}
