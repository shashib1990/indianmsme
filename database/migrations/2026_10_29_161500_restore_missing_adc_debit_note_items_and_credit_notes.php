<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use App\Models\AdcDebitNote;
use App\Models\AdcDebitNoteItem;
use App\Models\AdcCreditNote;
use App\Models\AdcOrder;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ensure auto_increment is strictly set on adc_debit_note_items and adc_credit_notes
        if (Schema::hasTable('adc_debit_note_items')) {
            DB::statement("ALTER TABLE `adc_debit_note_items` MODIFY `id` bigint unsigned NOT NULL AUTO_INCREMENT");
        }
        if (Schema::hasTable('adc_credit_notes')) {
            DB::statement("ALTER TABLE `adc_credit_notes` MODIFY `id` bigint unsigned NOT NULL AUTO_INCREMENT");
        }

        // 2. Populate missing AdcDebitNoteItem rows for all existing AdcDebitNote records
        $allDns = AdcDebitNote::all();

        foreach ($allDns as $dn) {
            $order = AdcOrder::with('items.product')->find($dn->adc_order_id);
            if (!$order) {
                continue;
            }

            $discrepancyItems = $order->items->filter(function ($i) {
                return ((float)$i->shortage_qty > 0 || (float)$i->damage_qty > 0);
            });

            foreach ($discrepancyItems as $item) {
                $alreadyExists = AdcDebitNoteItem::where('adc_debit_note_id', $dn->id)
                    ->where('adc_order_item_id', $item->id)
                    ->exists();

                if ($alreadyExists) {
                    continue;
                }

                $shortage = (int)$item->shortage_qty;
                $damage = (int)$item->damage_qty;
                $itemDiscrepancyQty = $shortage + $damage;
                $unitPrice = (float)$item->price;
                $itemDiscrepancyAmount = round($itemDiscrepancyQty * $unitPrice, 2);

                $sellerAgreedQty = null;
                $sellerAgreedAmount = null;

                if ($dn->status === 'SETTLED') {
                    if ($dn->seller_agreed_qty !== null && $dn->shortage_qty + $dn->damage_qty > 0) {
                        // Proportionate calculation for multi-item or exact for single item
                        $totalClaimed = $dn->shortage_qty + $dn->damage_qty;
                        if ($totalClaimed == $dn->seller_agreed_qty) {
                            $sellerAgreedQty = $itemDiscrepancyQty;
                            $sellerAgreedAmount = $itemDiscrepancyAmount;
                        } else {
                            $ratio = (float)$dn->final_amount / max(1, (float)$dn->total_discrepancy_amount);
                            $sellerAgreedQty = min($itemDiscrepancyQty, $dn->seller_agreed_qty);
                            $sellerAgreedAmount = round($itemDiscrepancyAmount * $ratio, 2);
                        }
                    } else {
                        $sellerAgreedQty = $itemDiscrepancyQty;
                        $sellerAgreedAmount = $itemDiscrepancyAmount;
                    }
                }

                AdcDebitNoteItem::create([
                    'adc_debit_note_id'       => $dn->id,
                    'adc_order_item_id'       => $item->id,
                    'product_name'            => $item->product_name ?? ($item->product?->product_name ?: 'Product #' . $item->product_id),
                    'order_qty'               => (int)$item->quantity,
                    'received_qty'            => max(0, (int)$item->quantity - $itemDiscrepancyQty),
                    'shortage_qty'            => $shortage,
                    'damage_qty'              => $damage,
                    'excess_qty'              => 0,
                    'unit_price'              => $unitPrice,
                    'item_discrepancy_amount' => $itemDiscrepancyAmount,
                    'seller_agreed_qty'       => $sellerAgreedQty,
                    'seller_agreed_amount'    => $sellerAgreedAmount,
                    'created_at'              => $dn->created_at,
                    'updated_at'              => $dn->updated_at,
                ]);
            }
        }

        // 3. Clear any temporary test credit notes and generate proper settled credit notes
        DB::table('adc_credit_notes')->delete();

        $settledNotes = AdcDebitNote::where('status', 'SETTLED')->orderBy('id', 'asc')->get();
        $cnIndex = 1;

        foreach ($settledNotes as $sdn) {
            $cnNumber = sprintf("ACN/26-27/%04d", $cnIndex++);

            AdcCreditNote::create([
                'credit_note_number' => $cnNumber,
                'adc_debit_note_id'  => $sdn->id,
                'adc_order_id'       => $sdn->adc_order_id,
                'adc_id'             => $sdn->adc_id,
                'target_type'        => $sdn->target_type ?: 'SELLER',
                'ddc_id'             => $sdn->ddc_id,
                'seller_id'          => $sdn->seller_id,
                'amount'             => (float)$sdn->final_amount ?: (float)$sdn->total_discrepancy_amount,
                'type'               => 'SHORTAGE_DAMAGE_CLAIM',
                'status'             => 'SETTLED',
                'created_at'         => $sdn->updated_at ?: $sdn->created_at,
                'updated_at'         => $sdn->updated_at ?: $sdn->created_at,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No destructive reverse
    }
};
