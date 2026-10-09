<?php

namespace App\Services;

use App\Models\IncentiveHold;
use App\Models\IncentiveRelease;
use App\Models\PurchaseOrder;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;

/**
 * IncentiveHoldService
 *
 * Encapsulates the SSI / FSI hold lifecycle:
 *
 *   1. createHoldOnPayment($purchaseOrder)
 *      Called when DDC successfully pays for a PO.
 *      - Creates item-wise IncentiveHold records.
 *
 *   2. mapInventoryToHold($purchaseOrderId, $itemId, $inventoryId)
 *      Called during stock-in of each PO item.
 *      - Updates inventory_id for the Hold record, status remains 'Hold'.
 */
class IncentiveHoldService
{
    /**
     * Create the SSI or FSI Hold entries for each accepted Purchase Order Item.
     *
     * Must be called inside an active DB transaction when the DDC successfully pays for the PO.
     *
     * @param  PurchaseOrder $purchaseOrder  (must have seller relationship loaded)
     * @return array                         Returns array of created/existing holds
     */
    public static function createHoldOnPayment(PurchaseOrder $purchaseOrder): array
    {
        // Only for paid POs with a positive accepted seller amount
        if (
            $purchaseOrder->payment_status !== 'Paid' ||
            (float) $purchaseOrder->seller_accepted_amount <= 0
        ) {
            return [];
        }

        // Resolve the seller's SSI subscription status
        $seller        = $purchaseOrder->seller ?? $purchaseOrder->seller()->with('sellerProfile')->first();
        $sellerProfile = $seller?->sellerProfile;
        $isDdcSubscribed = (bool) ($sellerProfile?->is_ddc_subscribed ?? false);

        if ($isDdcSubscribed) {
            // Smart Sales Incentive — resolve slab-wise percentage based on purchase order total
            $poTotal    = (float) ($purchaseOrder->seller_accepted_amount > 0 ? $purchaseOrder->seller_accepted_amount : ($purchaseOrder->seller_total_amount > 0 ? $purchaseOrder->seller_total_amount : $purchaseOrder->total_amount));
            $percentage = \App\Models\SellerSsiSlab::resolvePercentage($seller?->id, $poTotal);
            $type       = IncentiveHold::TYPE_SSI;
        } else {
            // Field Sales Incentive — use company-wide system setting (default 0.50%)
            $percentage = (float) SystemSetting::getVal('fsi_percentage', 0.50);
            $type       = IncentiveHold::TYPE_FSI;
        }

        // Lock in SSI metadata on the PO if not already set
        if (!$purchaseOrder->ssi_applicable && !$purchaseOrder->ssi_type) {
            $purchaseOrder->ssi_applicable  = $isDdcSubscribed;
            $purchaseOrder->ssi_type        = $type;
            $purchaseOrder->ssi_percentage  = $percentage;
            $purchaseOrder->save();
        }

        $createdHolds = [];

        // For each accepted Purchase Order Item, create a Hold record
        $acceptedItems = $purchaseOrder->items()->where('status', 'Accepted')->get();

        foreach ($acceptedItems as $item) {
            // Check if Hold record already exists for this PO Item
            $existing = IncentiveHold::where('purchase_order_id', $purchaseOrder->id)
                ->where('purchase_order_item_id', $item->id)
                ->where('status', IncentiveHold::STATUS_HOLD)
                ->first();

            if ($existing) {
                $createdHolds[] = $existing;
                continue;
            }

            // Hold Amount for this item = Taxable Amount (Excluding GST) * percentage / 100
            $itemBase   = (float) ($item->taxable_amount ?? ($item->gst_rate > 0 ? ((float) $item->seller_total / (1.0 + ($item->gst_rate / 100.0))) : (float) $item->seller_total));
            $holdAmount = round($itemBase * ($percentage / 100.0), 2);

            if ($holdAmount <= 0) {
                continue;
            }

            $notes = sprintf(
                '%s Hold created on DDC payment for item #%d (Product ID: %d, Qty: %d). ' .
                'seller_total: %.2f × %.2f%% = %.2f',
                $type,
                $item->id,
                $item->product_id,
                $item->quantity,
                (float) $item->seller_total,
                $percentage,
                $holdAmount
            );

            $createdHolds[] = IncentiveHold::create([
                'purchase_order_id'       => $purchaseOrder->id,
                'purchase_order_item_id'  => $item->id,
                'product_id'              => $item->product_id,
                'quantity'                => $item->quantity,
                'released_quantity'       => 0,
                'remaining_quantity'      => $item->quantity,
                'amount'                  => $holdAmount,
                'released_amount'         => 0.00,
                'balance_amount'          => $holdAmount,
                'percentage'              => $percentage,
                'type'                    => $type,
                'status'                  => IncentiveHold::STATUS_HOLD,
                'inventory_id'            => null,
                'notes'                   => $notes,
            ]);
        }

        return $createdHolds;
    }

    /**
     * Map the inventory ID to the corresponding item-wise Hold record during Stock-In.
     *
     * Do not change the status; keep it as HOLD.
     *
     * @param int $purchaseOrderId
     * @param int $itemId
     * @param int|null $inventoryId
     * @return void
     */
    public static function mapInventoryToHold(int $purchaseOrderId, int $itemId, ?int $inventoryId): void
    {
        IncentiveHold::where('purchase_order_id', $purchaseOrderId)
            ->where('purchase_order_item_id', $itemId)
            ->where('status', IncentiveHold::STATUS_HOLD)
            ->update([
                'inventory_id' => $inventoryId,
                'notes' => DB::raw("CONCAT(COALESCE(notes, ''), '\n[Stock-In] Inventory ID mapped: " . ($inventoryId ?? 'N/A') . " at " . now()->toDateTimeString() . "')")
            ]);
    }

    /**
     * Consume DDC inventory for a delivered ADC Order Item and partially or fully release the corresponding SSI/FSI Hold.
     *
     * @param  \App\Models\AdcOrderItem $orderItem
     * @param  int $deliveryQty
     * @return float Amount released from the hold
     */
    public static function releaseHoldOnAdcDelivery(\App\Models\AdcOrderItem $orderItem, int $deliveryQty): float
    {
        if ($deliveryQty <= 0) {
            return 0.00;
        }

        // Resolve DDC ID via ADC profile's pincode
        $adc = $orderItem->order->adc;
        $pincode = $adc?->profile?->pincode ?: session('user_pincode');
        $ddcId = null;
        if ($pincode) {
            $pincodeRecord = \App\Models\Pincode::where('pincode', $pincode)->first();
            if ($pincodeRecord) {
                $ddcId = \App\Models\DdcPincode::where('pincode_id', $pincodeRecord->id)->value('ddc_id');
            }
        }

        // Find available inventories of this product, prioritizing local DDC
        $inventoryQuery = \App\Models\DdcInventory::where('product_id', $orderItem->product_id)
            ->where('quantity', '>', 0);

        if ($ddcId) {
            $inventoryQuery->orderByRaw('ddc_id = ? desc', [$ddcId]);
        }
        $inventories = $inventoryQuery->orderBy('id', 'asc')->get();

        $qtyToConsume = $deliveryQty;
        $totalReleasedAmount = 0.00;

        foreach ($inventories as $inventory) {
            if ($qtyToConsume <= 0) {
                break;
            }

            $availableQty = $inventory->quantity;
            $consumeQty = min($qtyToConsume, $availableQty);

            // 1. Deduct from DdcInventory & track sold quantity
            $inventory->quantity      -= $consumeQty;
            $inventory->sold_quantity += $consumeQty;
            $inventory->save();

            $qtyToConsume -= $consumeQty;

            // 2. Find the corresponding hold transaction(s) for this inventory
            $hold = IncentiveHold::where('inventory_id', $inventory->id)
                ->whereIn('status', [IncentiveHold::STATUS_HOLD, IncentiveHold::STATUS_PARTIAL_RELEASED])
                ->first();

            if ($hold) {
                $origQty = (int) $hold->quantity;
                if ($origQty <= 0) {
                    $origQty = $consumeQty;
                }

                // Proportional release calculation
                $releaseAmt = round(($consumeQty / $origQty) * (float) $hold->amount, 2);

                $newReleasedQty = (int) $hold->released_quantity + $consumeQty;
                $newRemainingQty = max(0, $origQty - $newReleasedQty);

                $newReleasedAmt = (float) $hold->released_amount + $releaseAmt;
                if ($newRemainingQty <= 0) {
                    $newReleasedAmt = (float) $hold->amount;
                }
                $newBalanceAmt = max(0.00, (float) $hold->amount - $newReleasedAmt);

                $newStatus = $newRemainingQty <= 0 
                    ? IncentiveHold::STATUS_FULLY_RELEASED 
                    : IncentiveHold::STATUS_PARTIAL_RELEASED;

                $notes = sprintf(
                    "\n[Partial Release] Delivered %d units via ADC Order %s. " .
                    "Released: %.2f, Remaining Qty: %d, Balance: %.2f",
                    $consumeQty,
                    $orderItem->order->order_number,
                    $releaseAmt,
                    $newRemainingQty,
                    $newBalanceAmt
                );

                $hold->update([
                    'released_quantity'  => $newReleasedQty,
                    'remaining_quantity' => $newRemainingQty,
                    'released_amount'    => $newReleasedAmt,
                    'balance_amount'     => $newBalanceAmt,
                    'status'             => $newStatus,
                    'notes'              => $hold->notes . $notes,
                ]);

                // Create the AMA Release Transaction record
                $amaCode = $orderItem->order?->advisor_code ?? $orderItem->purchaseOrder?->ama_code ?? $orderItem->order?->adc?->advisor_code ?? 'UNKNOWN';
                IncentiveRelease::create([
                    'hold_id'           => $hold->id,
                    'ama_code'          => $amaCode,
                    'adc_order_id'      => $orderItem->adc_order_id,
                    'adc_order_item_id' => $orderItem->id,
                    'product_id'        => $orderItem->product_id,
                    'released_quantity' => $consumeQty,
                    'released_amount'   => $releaseAmt,
                    'release_date'      => now(),
                    'status'            => 'Released',
                ]);

                // Notify external Abhhyam API of AMA incentive release
                $orderNum = $orderItem->order?->order_number ?? ("ID #" . $orderItem->adc_order_id);
                $poNum    = $orderItem->purchaseOrder?->po_number;
                if ($orderNum && $poNum) {
                    $remarks = "Incentive released against Order #{$orderNum} (PO #{$poNum})";
                } elseif ($orderNum) {
                    $remarks = "Incentive released against Order #{$orderNum}";
                } elseif ($poNum) {
                    $remarks = "Incentive released against PO #{$poNum}";
                } else {
                    $remarks = "Incentive released for ADC Order Item #{$orderItem->id}";
                }

                \App\Services\AbhhyamService::updateAmaIncentive($amaCode, $releaseAmt, $remarks);

                $totalReleasedAmount += $releaseAmt;
            }
        }

        return $totalReleasedAmount;
    }

    /**
     * Trigger immediate AMA Incentive Release upon Invoice Generation
     * (whether generated by Seller for virtual PO or by DDC for physical ADC order).
     *
     * @param  \App\Models\Invoice $invoice
     * @return float Amount of incentive released
     */
    /**
     * Create IncentiveHold records for a Physical ADC Order upon checkout.
     *
     * @param  \App\Models\AdcOrder $adcOrder
     * @return array
     */
    public static function createHoldOnAdcOrder(\App\Models\AdcOrder $adcOrder): array
    {
        $createdHolds = [];

        // Pre-calculate total shopping amount per seller in this ADC Order
        $sellerTotals = [];
        foreach ($adcOrder->items as $item) {
            $sId = $item->seller_id;
            if ($sId) {
                $itemTotal = (float) ($item->total ?? 0);
                $sellerTotals[$sId] = ($sellerTotals[$sId] ?? 0) + $itemTotal;
            }
        }

        foreach ($adcOrder->items as $item) {
            $existing = IncentiveHold::where('adc_order_id', $adcOrder->id)
                ->where('adc_order_item_id', $item->id)
                ->where('status', IncentiveHold::STATUS_HOLD)
                ->first();

            if ($existing) {
                $createdHolds[] = $existing;
                continue;
            }

            // Resolve Seller SSI vs FSI percentage
            $seller        = $item->seller ?? \App\Models\User::with('sellerProfile')->find($item->seller_id);
            $sellerProfile = $seller?->sellerProfile;
            $isDdcSubscribed = (bool) ($sellerProfile?->is_ddc_subscribed ?? false);

            if ($isDdcSubscribed) {
                $sellerShoppingTotal = $sellerTotals[$item->seller_id] ?? (float) ($adcOrder->total_amount ?? 0);
                $percentage = \App\Models\SellerSsiSlab::resolvePercentage($item->seller_id, $sellerShoppingTotal);
                $type       = IncentiveHold::TYPE_SSI;
            } else {
                $percentage = (float) SystemSetting::getVal('fsi_percentage', 0.50);
                $type       = IncentiveHold::TYPE_FSI;
            }

            // Hold Amount calculated on Taxable Amount (Excluding GST)
            $itemBase   = (float) ($item->taxable_amount ?? ($item->gst_rate > 0 ? round((float) $item->total / (1 + ($item->gst_rate / 100)), 2) : $item->total));
            $holdAmount = round($itemBase * ($percentage / 100), 2);

            if ($holdAmount <= 0) {
                continue;
            }

            $notes = sprintf(
                '%s Hold created for Physical ADC Order #%s item #%d. Taxable: %.2f × %.2f%% = %.2f (Slab Shopping Total: %.2f)',
                $type,
                $adcOrder->order_number,
                $item->id,
                $itemBase,
                $percentage,
                $holdAmount,
                $sellerTotals[$item->seller_id] ?? (float) ($adcOrder->total_amount ?? 0)
            );

            $createdHolds[] = IncentiveHold::create([
                'purchase_order_id'       => null,
                'purchase_order_item_id'  => null,
                'adc_order_id'            => $adcOrder->id,
                'adc_order_item_id'       => $item->id,
                'product_id'              => $item->product_id,
                'quantity'                => $item->quantity,
                'released_quantity'       => 0,
                'remaining_quantity'      => $item->quantity,
                'amount'                  => $holdAmount,
                'released_amount'         => 0.00,
                'balance_amount'          => $holdAmount,
                'percentage'              => $percentage,
                'type'                    => $type,
                'status'                  => IncentiveHold::STATUS_HOLD,
                'inventory_id'            => null,
                'notes'                   => $notes,
            ]);
        }

        return $createdHolds;
    }

    /**
     * Trigger immediate AMA Incentive Release upon Invoice Generation
     * (releasing strictly from existing IncentiveHold records based on quantity).
     *
     * @param  \App\Models\Invoice $invoice
     * @return float Amount of incentive released
     */
    public static function releaseHoldOnInvoice(\App\Models\Invoice $invoice): float
    {
        $totalReleased = 0.00;

        // 1. If invoice is linked to a PurchaseOrder (Virtual Flow)
        if ($invoice->purchase_order_id) {
            $po = $invoice->purchaseOrder ?? PurchaseOrder::with(['seller.sellerProfile', 'adcOrder.adc'])->find($invoice->purchase_order_id);
            if ($po) {
                $amaCode = $po->ama_code ?? $po->adcOrder?->advisor_code ?? $po->adcOrder?->adc?->advisor_code ?? 'UNKNOWN';

                $holds = IncentiveHold::where('purchase_order_id', $po->id)
                    ->whereIn('status', [IncentiveHold::STATUS_HOLD, IncentiveHold::STATUS_PARTIAL_RELEASED])
                    ->get();

                foreach ($holds as $hold) {
                    $consumeQty = $hold->remaining_quantity > 0 ? $hold->remaining_quantity : $hold->quantity;
                    if ($consumeQty <= 0) {
                        continue;
                    }

                    // Calculate release amount strictly based on quantity proportion from Hold amount
                    $releaseAmt = ($consumeQty >= $hold->remaining_quantity) 
                        ? (float) $hold->balance_amount 
                        : round(($consumeQty / (int) $hold->quantity) * (float) $hold->amount, 2);

                    if ($releaseAmt <= 0) {
                        continue;
                    }

                    $alreadyReleased = IncentiveRelease::where('hold_id', $hold->id)
                        ->where('status', 'Released')
                        ->exists();

                    if ($alreadyReleased) {
                        continue;
                    }

                    $newReleasedQty = (int) $hold->released_quantity + $consumeQty;
                    $newRemainingQty = max(0, (int) $hold->quantity - $newReleasedQty);
                    $newReleasedAmt = (float) $hold->released_amount + $releaseAmt;
                    $newBalanceAmt = max(0.00, (float) $hold->amount - $newReleasedAmt);
                    $newStatus = $newRemainingQty <= 0 ? IncentiveHold::STATUS_FULLY_RELEASED : IncentiveHold::STATUS_PARTIAL_RELEASED;

                    $hold->update([
                        'released_quantity'  => $newReleasedQty,
                        'remaining_quantity' => $newRemainingQty,
                        'released_amount'    => $newReleasedAmt,
                        'balance_amount'     => $newBalanceAmt,
                        'status'             => $newStatus,
                        'notes'              => $hold->notes . sprintf("\n[Invoice Release] Invoice %s generated on %s. Released Qty: %d, Amt: %.2f", $invoice->invoice_number, now()->toDateTimeString(), $consumeQty, $releaseAmt),
                    ]);

                    IncentiveRelease::create([
                        'hold_id'           => $hold->id,
                        'ama_code'          => $amaCode,
                        'adc_order_id'      => $po->adc_order_id,
                        'adc_order_item_id' => $hold->purchase_order_item_id,
                        'product_id'        => $hold->product_id,
                        'released_quantity' => $consumeQty,
                        'released_amount'   => $releaseAmt,
                        'release_date'      => now(),
                        'status'            => 'Released',
                    ]);

                    // Notify external Abhhyam API of AMA incentive release
                    $orderNum   = $po->adcOrder?->order_number;
                    $poNum      = $po->po_number;
                    $invoiceNum = $invoice->invoice_number;

                    if ($orderNum && $poNum) {
                        $remarks = "Incentive released against Order #{$orderNum} (PO #{$poNum}, Invoice #{$invoiceNum})";
                    } elseif ($poNum) {
                        $remarks = "Incentive released against PO #{$poNum} (Invoice #{$invoiceNum})";
                    } elseif ($orderNum) {
                        $remarks = "Incentive released against Order #{$orderNum} (Invoice #{$invoiceNum})";
                    } else {
                        $remarks = "Incentive released for Invoice #{$invoiceNum}";
                    }

                    \App\Services\AbhhyamService::updateAmaIncentive($amaCode, $releaseAmt, $remarks);

                    $totalReleased += $releaseAmt;
                }
            }
        }

        // 2. If invoice is linked directly to a Physical AdcOrder (DDC to ADC)
        if ($invoice->adc_order_id) {
            $adcOrder = $invoice->adcOrder ?? \App\Models\AdcOrder::with(['items.seller.sellerProfile', 'adc'])->find($invoice->adc_order_id);
            if ($adcOrder) {
                $amaCode = $adcOrder->advisor_code ?? $adcOrder->adc?->advisor_code ?? 'UNKNOWN';

                // Ensure holds exist for this physical order
                self::createHoldOnAdcOrder($adcOrder);

                $holds = IncentiveHold::where('adc_order_id', $adcOrder->id)
                    ->whereIn('status', [IncentiveHold::STATUS_HOLD, IncentiveHold::STATUS_PARTIAL_RELEASED])
                    ->get();

                foreach ($holds as $hold) {
                    $consumeQty = $hold->remaining_quantity > 0 ? $hold->remaining_quantity : $hold->quantity;
                    if ($consumeQty <= 0) {
                        continue;
                    }

                    // Calculate release amount strictly based on quantity proportion from Hold amount
                    $releaseAmt = ($consumeQty >= $hold->remaining_quantity) 
                        ? (float) $hold->balance_amount 
                        : round(($consumeQty / (int) $hold->quantity) * (float) $hold->amount, 2);

                    if ($releaseAmt <= 0) {
                        continue;
                    }

                    $alreadyReleased = IncentiveRelease::where('hold_id', $hold->id)
                        ->where('status', 'Released')
                        ->exists();

                    if ($alreadyReleased) {
                        continue;
                    }

                    $newReleasedQty = (int) $hold->released_quantity + $consumeQty;
                    $newRemainingQty = max(0, (int) $hold->quantity - $newReleasedQty);
                    $newReleasedAmt = (float) $hold->released_amount + $releaseAmt;
                    $newBalanceAmt = max(0.00, (float) $hold->amount - $newReleasedAmt);
                    $newStatus = $newRemainingQty <= 0 ? IncentiveHold::STATUS_FULLY_RELEASED : IncentiveHold::STATUS_PARTIAL_RELEASED;

                    $hold->update([
                        'released_quantity'  => $newReleasedQty,
                        'remaining_quantity' => $newRemainingQty,
                        'released_amount'    => $newReleasedAmt,
                        'balance_amount'     => $newBalanceAmt,
                        'status'             => $newStatus,
                        'notes'              => $hold->notes . sprintf("\n[Invoice Release] Invoice %s generated on %s. Released Qty: %d, Amt: %.2f", $invoice->invoice_number, now()->toDateTimeString(), $consumeQty, $releaseAmt),
                    ]);

                    IncentiveRelease::create([
                        'hold_id'           => $hold->id,
                        'ama_code'          => $amaCode,
                        'adc_order_id'      => $adcOrder->id,
                        'adc_order_item_id' => $hold->adc_order_item_id,
                        'product_id'        => $hold->product_id,
                        'released_quantity' => $consumeQty,
                        'released_amount'   => $releaseAmt,
                        'release_date'      => now(),
                        'status'            => 'Released',
                    ]);

                    // Notify external Abhhyam API of AMA incentive release
                    $orderNum   = $adcOrder->order_number ?? ("ID #" . $adcOrder->id);
                    $invoiceNum = $invoice->invoice_number;
                    $remarks    = "Incentive released against Order #{$orderNum} (Invoice #{$invoiceNum})";

                    \App\Services\AbhhyamService::updateAmaIncentive($amaCode, $releaseAmt, $remarks);

                    $totalReleased += $releaseAmt;
                }
            }
        }

        return $totalReleased;
    }

    /**
     * Reverse inventory consumption and hold release for a returned/cancelled ADC Order Item.
     *
     * @param  \App\Models\AdcOrderItem $orderItem
     * @param  int $returnQty Quantity being returned
     * @return float Amount put back on hold
     */
    public static function reverseReleaseOnAdcReturn(\App\Models\AdcOrderItem $orderItem, int $returnQty): float
    {
        if ($returnQty <= 0) {
            return 0.00;
        }

        $adc = $orderItem->order->adc;
        $pincode = $adc?->profile?->pincode ?: session('user_pincode');
        $ddcId = null;
        if ($pincode) {
            $pincodeRecord = \App\Models\Pincode::where('pincode', $pincode)->first();
            if ($pincodeRecord) {
                $ddcId = \App\Models\DdcPincode::where('pincode_id', $pincodeRecord->id)->value('ddc_id');
            }
        }

        $inventoryQuery = \App\Models\DdcInventory::where('product_id', $orderItem->product_id);
        if ($ddcId) {
            $inventoryQuery->orderByRaw('ddc_id = ? desc', [$ddcId]);
        }
        $inventory = $inventoryQuery->orderBy('id', 'desc')->first();

        if (!$inventory) {
            return 0.00;
        }

        // 1. Put back to inventory & track returned quantity
        $inventory->quantity          += $returnQty;
        $inventory->returned_quantity += $returnQty;
        $inventory->save();

        // 2. Once an incentive is released, return and refund do NOT reverse the released incentive.
        return 0.00;
    }
}
