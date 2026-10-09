<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\DdcWallet;
use App\Models\SystemSetting;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class DdcCashbackService
{
    /**
     * Process DDC Cashback after Stock-In / Received status.
     * Guaranteed to run only once per PO (idempotent).
     *
     * @param PurchaseOrder $purchaseOrder
     * @return bool Returns true if cashback was calculated and credited, false otherwise.
     */
    public static function processDdcCashback(PurchaseOrder $purchaseOrder): bool
    {
        try {
            return DB::transaction(function () use ($purchaseOrder) {
                // Lock row to prevent parallel duplicate execution
                $po = PurchaseOrder::where('id', $purchaseOrder->id)->lockForUpdate()->first();
                if (!$po) {
                    return false;
                }

                // Check if cashback was already credited
                if ($po->is_cashback_credited) {
                    return false;
                }

                // Retrieve configured cashback percentage
                $cashbackPercentage = (float) SystemSetting::getVal('ddc_cashback_percentage', 0.0);
                if ($cashbackPercentage <= 0) {
                    return false;
                }

                // Load items and DDC user with profile
                $po->loadMissing(['items', 'ddc.profile']);

                // Calculate total accepted/received stock value
                $acceptedItems = $po->items->where('status', 'Accepted');

                $receivedStockValue = 0.0;
                foreach ($acceptedItems as $item) {
                    $qty = $item->is_stocked_in
                        ? max(0, ($item->supply_qty ?: $item->quantity) - ($item->shortage_qty ?? 0) - ($item->damage_qty ?? 0) + ($item->excess_qty ?? 0))
                        : $item->quantity;
                    $receivedStockValue += ((float) $item->price * $qty);
                }

                // Fallback to accepted_amount or total_amount if stock-in calculation evaluates to 0
                if ($receivedStockValue <= 0) {
                    $receivedStockValue = (float) ($po->accepted_amount > 0 ? $po->accepted_amount : $po->total_amount);
                }

                if ($receivedStockValue <= 0) {
                    return false;
                }

                $cashbackAmount = round($receivedStockValue * ($cashbackPercentage / 100.0), 2);
                if ($cashbackAmount <= 0) {
                    return false;
                }

                $ddc = $po->ddc;
                if (!$ddc) {
                    return false;
                }

                $profile = $ddc->profile;
                if (!$profile) {
                    $profile = $ddc->ddcProfile()->firstOrCreate([]);
                }

                // Credit DDC wallet balance
                $profile->increment('wallet_balance', $cashbackAmount);

                // Create Wallet Transaction Record
                DdcWallet::create([
                    'ddc_id' => $po->ddc_id,
                    'amount' => $cashbackAmount,
                    'type' => 'credit',
                    'description' => "Shopping Cashback ({$cashbackPercentage}%) on PO #{$po->po_number}",
                ]);

                // Mark PO as cashback credited to prevent duplicate credit
                $po->is_cashback_credited = true;
                $po->cashback_percentage = $cashbackPercentage;
                $po->cashback_amount = $cashbackAmount;
                $po->save();

                // Notify DDC user
                NotificationService::notifyDdc(
                    $po->ddc_id,
                    'Cashback Credited',
                    "Shopping Cashback of ₹" . number_format($cashbackAmount, 2) . " ({$cashbackPercentage}%) credited to your wallet for PO #{$po->po_number}.",
                    route('ddc.wallet.index')
                );

                return true;
            });
        } catch (\Exception $e) {
            Log::error("Failed to process DDC cashback for PO #{$purchaseOrder->po_number}: " . $e->getMessage());
            return false;
        }
    }
}
