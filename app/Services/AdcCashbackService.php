<?php

namespace App\Services;

use App\Models\AdcOrder;
use App\Models\AdcProfile;
use App\Models\AdcWallet;
use App\Models\SystemSetting;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class AdcCashbackService
{
    /**
     * Process ADC Cashback after Stock-In / Received status.
     * Guaranteed to run only once per AdcOrder (idempotent).
     *
     * @param AdcOrder $adcOrder
     * @return bool Returns true if cashback was calculated and credited, false otherwise.
     */
    public static function processAdcCashback(AdcOrder $adcOrder): bool
    {
        try {
            return DB::transaction(function () use ($adcOrder) {
                // Lock row to prevent parallel duplicate execution
                $order = AdcOrder::where('id', $adcOrder->id)->lockForUpdate()->first();
                if (!$order) {
                    return false;
                }

                // Check if cashback was already credited
                if ($order->is_cashback_credited) {
                    return false;
                }

                // Retrieve configured ADC cashback percentage from SystemSetting
                $cashbackPercentage = (float) SystemSetting::getVal('adc_cashback_percentage', 0.0);
                if ($cashbackPercentage <= 0) {
                    return false;
                }

                // Load items and ADC profile
                $order->loadMissing(['items', 'adc.adcProfile']);

                // Calculate total received stock value
                $receivedStockValue = 0.0;
                foreach ($order->items as $item) {
                    if (in_array($item->status, ['Cancelled', 'Rejected'])) {
                        continue;
                    }
                    $qty = $item->is_stocked_in
                        ? max(0, ($item->supply_qty ?: $item->quantity) - ($item->shortage_qty ?? 0) - ($item->damage_qty ?? 0) + ($item->excess_qty ?? 0))
                        : $item->quantity;
                    $receivedStockValue += ((float) $item->price * $qty);
                }

                // Fallback to order total_amount if stock-in calculation evaluates to 0
                if ($receivedStockValue <= 0) {
                    $receivedStockValue = (float) $order->total_amount;
                }

                if ($receivedStockValue <= 0) {
                    return false;
                }

                $cashbackAmount = round($receivedStockValue * ($cashbackPercentage / 100.0), 2);
                if ($cashbackAmount <= 0) {
                    return false;
                }

                $adcId = $order->adc_id;
                if (!$adcId) {
                    return false;
                }

                $profile = AdcProfile::where('user_id', $adcId)->firstOrCreate(['user_id' => $adcId]);

                // Credit ADC wallet balance
                $profile->increment('wallet_balance', $cashbackAmount);

                // Create Wallet Transaction Record
                AdcWallet::create([
                    'adc_id'      => $adcId,
                    'amount'      => $cashbackAmount,
                    'type'        => 'credit',
                    'description' => "Shopping Cashback ({$cashbackPercentage}%) on Order #{$order->order_number}",
                ]);

                // Mark AdcOrder as cashback credited to prevent duplicate credit
                $order->is_cashback_credited = true;
                $order->cashback_percentage = $cashbackPercentage;
                $order->cashback_amount = $cashbackAmount;
                $order->save();

                // Notify ADC user
                NotificationService::notifyAdc(
                    $adcId,
                    'Cashback Credited',
                    "Shopping Cashback of ₹" . number_format($cashbackAmount, 2) . " ({$cashbackPercentage}%) credited to your wallet for Order #{$order->order_number}.",
                    route('adc.wallet.index')
                );

                return true;
            });
        } catch (\Exception $e) {
            Log::error("Failed to process ADC cashback for Order #{$adcOrder->order_number}: " . $e->getMessage());
            return false;
        }
    }
}
