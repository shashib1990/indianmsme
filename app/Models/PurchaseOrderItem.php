<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrderItem extends Model
{
    use HasFactory;

    protected $table = 'purchase_order_items';

    protected $fillable = [
        'purchase_order_id',
        'seller_id',
        'product_id',
        'product_name',
        'product_code',
        'barcode',
        'unit_of_measure',
        'quantity',
        'price',
        'actual_mrp',
        'received_mrp',
        'supply_qty',
        'damage_qty',
        'shortage_qty',
        'excess_qty',
        'total',
        'seller_price',
        'seller_total',
        'status',
        'remarks',
        'mfg_date',
        'expire_date',
        'batch_number',
        'is_stocked_in',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'actual_mrp' => 'decimal:2',
        'received_mrp' => 'decimal:2',
        'supply_qty' => 'integer',
        'damage_qty' => 'integer',
        'shortage_qty' => 'integer',
        'excess_qty' => 'integer',
        'total' => 'decimal:2',
        'seller_price' => 'decimal:2',
        'seller_total' => 'decimal:2',
        'status' => \App\Casts\EnumStringCast::class . ':' . \App\Enums\PurchaseOrderStatus::class,
        'mfg_date' => 'date',
        'expire_date' => 'date',
        'is_stocked_in' => 'boolean',
    ];

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function seller()
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function debitNotes()
    {
        return $this->hasMany(DebitNote::class, 'purchase_order_item_id');
    }

    public function getReceivedQtyAttribute()
    {
        return max(0, ($this->supply_qty ?: $this->quantity) - ($this->shortage_qty ?? 0) - ($this->damage_qty ?? 0));
    }
}
