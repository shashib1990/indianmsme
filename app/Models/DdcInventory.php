<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DdcInventory extends Model
{
    use HasFactory;

    protected $table = 'ddc_inventories';

    protected $fillable = [
        'ddc_id',
        'inventory_source',
        'product_id',
        'ddc_local_product_id',
        'product_name',
        'product_code',
        'barcode',
        'unit_of_measure',
        'quantity',
        'reserved_quantity',
        'sold_quantity',
        'returned_quantity',
        'mrp',
        'purchase_price',
        'landing_cost',
        'msp',
        'csp',
        'margin',
        'adc_margin',
        'csp_margin',
        'gst_rate',
        'mfg_date',
        'expire_date',
        'batch_number',
        'purchase_order_id',
        'purchase_order_item_id',
        'reference_type',
        'reference_id',
        'pos_status',
    ];

    protected $casts = [
        'quantity'          => 'decimal:3',
        'reserved_quantity' => 'decimal:3',
        'sold_quantity'     => 'decimal:3',
        'returned_quantity' => 'decimal:3',
        'mrp'               => 'decimal:2',
        'purchase_price'    => 'decimal:2',
        'msp'               => 'decimal:2',
        'adc_price'         => 'decimal:2',
        'gst_rate'          => 'decimal:2',
        'mbp_percentage'    => 'decimal:2',
        'mbp_amount'        => 'decimal:2',
        'mbp_90_share'      => 'decimal:2',
        'mbp_10_share'      => 'decimal:2',
        'mbp_38_share'      => 'decimal:2',
        'mbp_62_share'      => 'decimal:2',
        'csp'               => 'decimal:2',
        'margin'            => 'decimal:2',
        'adc_margin'        => 'decimal:2',
        'csp_margin'        => 'decimal:2',
        'mfg_date'          => 'date',
        'expire_date'       => 'date',
    ];

    public function getMbp90ShareAttribute(): float
    {
        if (isset($this->attributes['mbp_90_share']) && (float)$this->attributes['mbp_90_share'] > 0) {
            return (float)$this->attributes['mbp_90_share'];
        }
        return (float)($this->product?->mbp_90_share ?? $this->localProduct?->msmeProduct?->mbp_90_share ?? 0);
    }

    public function getMbpPercentageAttribute(): float
    {
        if (isset($this->attributes['mbp_percentage']) && (float)$this->attributes['mbp_percentage'] > 0) {
            return (float)$this->attributes['mbp_percentage'];
        }
        return (float)($this->product?->mbp_percentage ?? $this->localProduct?->msmeProduct?->mbp_percentage ?? 0);
    }

    public function getMbpAmountAttribute(): float
    {
        if (isset($this->attributes['mbp_amount']) && (float)$this->attributes['mbp_amount'] > 0) {
            return (float)$this->attributes['mbp_amount'];
        }
        return (float)($this->product?->mbp_amount ?? $this->localProduct?->msmeProduct?->mbp_amount ?? 0);
    }

    public function getMbp10ShareAttribute(): float
    {
        if (isset($this->attributes['mbp_10_share']) && (float)$this->attributes['mbp_10_share'] > 0) {
            return (float)$this->attributes['mbp_10_share'];
        }
        return (float)($this->product?->mbp_10_share ?? $this->localProduct?->msmeProduct?->mbp_10_share ?? 0);
    }

    public function getMbp38ShareAttribute(): float
    {
        if (isset($this->attributes['mbp_38_share']) && (float)$this->attributes['mbp_38_share'] > 0) {
            return (float)$this->attributes['mbp_38_share'];
        }
        return (float)($this->product?->mbp_38_share ?? $this->localProduct?->msmeProduct?->mbp_38_share ?? 0);
    }

    public function getMbp62ShareAttribute(): float
    {
        if (isset($this->attributes['mbp_62_share']) && (float)$this->attributes['mbp_62_share'] > 0) {
            return (float)$this->attributes['mbp_62_share'];
        }
        return (float)($this->product?->mbp_62_share ?? $this->localProduct?->msmeProduct?->mbp_62_share ?? 0);
    }

    /**
     * Available Stock = Physical Stock (quantity) - Reserved Stock (reserved_quantity)
     */
    public function getAvailableQuantityAttribute(): float
    {
        return max(0, (float) $this->quantity - ((float) ($this->reserved_quantity ?? 0)));
    }

    /**
     * Reserve stock upon Order Placement (does not reduce physical quantity).
     * Supports multi-batch FIFO reservation if requested quantity exceeds a single batch.
     */
    public function reserveStock(float $qty): void
    {
        $avail = $this->available_quantity;
        if ($avail >= $qty) {
            $this->reserved_quantity = ($this->reserved_quantity ?? 0) + $qty;
            $this->save();
        } else {
            // Reserve available in current batch
            $this->reserved_quantity = ($this->reserved_quantity ?? 0) + $avail;
            $this->save();

            $remainingToReserve = $qty - $avail;
            $nextBatches = self::where('ddc_id', $this->ddc_id)
                ->where('product_id', $this->product_id)
                ->where('id', '>', $this->id)
                ->whereRaw('(quantity - COALESCE(reserved_quantity, 0)) > 0')
                ->orderBy('id', 'asc')
                ->get();

            foreach ($nextBatches as $nextBatch) {
                if ($remainingToReserve <= 0) {
                    break;
                }
                $nextAvail = $nextBatch->available_quantity;
                $reserveAmount = min($remainingToReserve, $nextAvail);
                $nextBatch->reserved_quantity = ($nextBatch->reserved_quantity ?? 0) + $reserveAmount;
                $nextBatch->save();
                $remainingToReserve -= $reserveAmount;
            }
        }
    }

    /**
     * Release reserved stock upon Order Cancellation before dispatch.
     */
    public function releaseReservedStock(float $qty): void
    {
        $this->reserved_quantity = max(0, ($this->reserved_quantity ?? 0) - $qty);
        $this->save();
    }

    /**
     * Confirm dispatch: release reserved stock, reduce physical stock, increase sold_quantity.
     * Supports multi-batch FIFO dispatch if dispatched quantity spans multiple batches.
     */
    public function confirmDispatch(float $dispatchedQty): void
    {
        $qtyToProcess = $dispatchedQty;
        $availReserved = min($qtyToProcess, $this->reserved_quantity ?? $this->quantity);

        $this->reserved_quantity = max(0, ($this->reserved_quantity ?? 0) - $availReserved);
        $this->quantity          = max(0, $this->quantity - $availReserved);
        $this->sold_quantity     = ($this->sold_quantity ?? 0) + $availReserved;
        $this->save();

        $qtyToProcess -= $availReserved;

        if ($qtyToProcess > 0) {
            $nextBatches = self::where('ddc_id', $this->ddc_id)
                ->where('product_id', $this->product_id)
                ->where('id', '>', $this->id)
                ->orderBy('id', 'asc')
                ->get();

            foreach ($nextBatches as $nextBatch) {
                if ($qtyToProcess <= 0) {
                    break;
                }
                $res = min($qtyToProcess, $nextBatch->reserved_quantity ?? $nextBatch->quantity);
                $nextBatch->reserved_quantity = max(0, ($nextBatch->reserved_quantity ?? 0) - $res);
                $nextBatch->quantity          = max(0, $nextBatch->quantity - $res);
                $nextBatch->sold_quantity     = ($nextBatch->sold_quantity ?? 0) + $res;
                $nextBatch->save();
                $qtyToProcess -= $res;
            }
        }
    }

    /**
     * Total stock received (quantity + sold - returned is the balance).
     * balance = quantity (current stock on hand)
     * total_received = quantity + sold_quantity - returned_quantity
     */
    public function getTotalReceivedAttribute(): float
    {
        return (float)$this->quantity + (float)$this->sold_quantity - (float)$this->returned_quantity;
    }

    public function ddc()
    {
        return $this->belongsTo(DDC::class, 'ddc_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id');
    }

    public function localProduct()
    {
        return $this->belongsTo(DdcLocalProduct::class, 'ddc_local_product_id');
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class, 'purchase_order_id');
    }

    public function purchaseOrderItem()
    {
        return $this->belongsTo(PurchaseOrderItem::class, 'purchase_order_item_id');
    }
}
