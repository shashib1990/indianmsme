<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AdcDebitNoteItem extends Model
{
    use HasFactory;

    protected $table = 'adc_debit_note_items';

    protected $fillable = [
        'adc_debit_note_id',
        'adc_order_item_id',
        'product_name',
        'order_qty',
        'received_qty',
        'shortage_qty',
        'damage_qty',
        'excess_qty',
        'unit_price',
        'item_discrepancy_amount',
        'seller_agreed_qty',
        'seller_agreed_amount',
    ];

    protected $casts = [
        'order_qty' => 'integer',
        'received_qty' => 'integer',
        'shortage_qty' => 'integer',
        'damage_qty' => 'integer',
        'excess_qty' => 'integer',
        'seller_agreed_qty' => 'integer',
        'unit_price' => 'decimal:2',
        'item_discrepancy_amount' => 'decimal:2',
        'seller_agreed_amount' => 'decimal:2',
    ];

    public function debitNote()
    {
        return $this->belongsTo(AdcDebitNote::class, 'adc_debit_note_id');
    }

    public function adcOrderItem()
    {
        return $this->belongsTo(AdcOrderItem::class, 'adc_order_item_id');
    }

    public function product()
    {
        return $this->hasOneThrough(
            Product::class,
            AdcOrderItem::class,
            'id',                // Foreign key on adc_order_items table
            'id',                // Foreign key on products table
            'adc_order_item_id', // Local key on adc_debit_note_items table
            'product_id'         // Local key on adc_order_items table
        );
    }
}
