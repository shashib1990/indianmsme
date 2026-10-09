<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IncentiveHold extends Model
{
    use HasFactory;

    protected $table = 'incentive_holds';

    // Status constants
    const STATUS_HOLD = 'Hold';
    const STATUS_PAID = 'Paid';
    const STATUS_RELEASED = 'Released';
    const STATUS_CANCELLED = 'Cancelled';
    const STATUS_PARTIAL_RELEASED = 'Partial_Released';
    const STATUS_FULLY_RELEASED = 'Fully_Released';

    // Type constants
    const TYPE_SSI = 'SSI';
    const TYPE_FSI = 'FSI';

    protected $fillable = [
        'purchase_order_id',
        'purchase_order_item_id',
        'adc_order_id',
        'adc_order_item_id',
        'product_id',
        'quantity',
        'released_quantity',
        'remaining_quantity',
        'amount',
        'released_amount',
        'balance_amount',
        'percentage',
        'type',
        'status',
        'inventory_id',
        'notes',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'released_quantity' => 'integer',
        'remaining_quantity' => 'integer',
        'amount' => 'decimal:2',
        'released_amount' => 'decimal:2',
        'balance_amount' => 'decimal:2',
        'percentage' => 'decimal:2',
    ];

    // ─── Relationships ───────────────────────────────────────────────────────

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function inventory()
    {
        return $this->belongsTo(DdcInventory::class, 'inventory_id');
    }

    public function releases()
    {
        return $this->hasMany(IncentiveRelease::class, 'hold_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function isOnHold(): bool
    {
        return $this->status === self::STATUS_HOLD;
    }

    public function isReleased(): bool
    {
        return $this->status === self::STATUS_RELEASED;
    }
}
