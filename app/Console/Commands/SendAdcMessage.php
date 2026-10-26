<?php

namespace App\Console\Commands;

use App\Models\AdcOrder;
use App\Models\AdcOrderItem;
use App\Services\TheekHaiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class SendAdcMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'adc:send-message {order_ids : Comma-separated ADC order IDs} {--advisor-code= : Advisor AMA code}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send WhatsApp / TheekHai order confirmation notification to ADC';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $orderIdsArg = (string) $this->argument('order_ids');
        $orderIds = array_filter(array_map('trim', explode(',', $orderIdsArg)));

        if (empty($orderIds)) {
            $this->error('No order IDs provided.');
            return 1;
        }

        $orders = AdcOrder::with(['adc.adcProfile', 'adc.profile', 'items'])->whereIn('id', $orderIds)->get();

        if ($orders->isEmpty()) {
            $this->error('No ADC Orders found for IDs: ' . implode(', ', $orderIds));
            return 1;
        }

        $firstOrder = $orders->first();
        $adc = $firstOrder->adc;

        if (!$adc) {
            $this->error('ADC User not found for Order ID: ' . $firstOrder->id);
            return 1;
        }

        $adcProfile = $adc->adcProfile ?: $adc->profile;
        $adcPhone = $adc->mobile ?: ($adcProfile?->official_phone ?? null);

        if (empty($adcPhone)) {
            $this->warn('ADC has no mobile or official phone recorded.');
            return 0;
        }

        $adcName = $adc->name ?: 'ADC Partner';
        $adcCodeStr = $adcProfile?->adc_code 
            ?: ($adc->adc_code 
            ?: ($adcProfile?->code 
            ?: ('ADC' . str_pad($adc->id ?? 0, 3, '0', STR_PAD_LEFT))));

        $orderNumbers = $orders->pluck('order_number')->filter()->toArray();
        $orderNumsStr = implode(', ', $orderNumbers);

        $totalPayable = (float) $orders->sum('total_amount');
        $totalMbpCoins = (float) $orders->sum('total_mbp_coins');

        // Calculate total MRP and item details
        $totalMrp = 0.0;
        $totalItemsCount = 0;
        $itemLines = [];

        foreach ($orders as $ord) {
            $items = $ord->items;
            if ($items->isEmpty()) {
                $items = AdcOrderItem::where('adc_order_id', $ord->id)->get();
            }

            foreach ($items as $oi) {
                $qty = (int) ($oi->quantity ?? 1);
                $itemMrp = (float) ($oi->mrp ?? $oi->price);
                $totalMrp += ($itemMrp * $qty);
                $totalItemsCount += $qty;

                $pName = trim((string) ($oi->product_name ?? 'Product'));
                $pMrpStr = number_format($itemMrp, 2);
                $pBuyPriceStr = number_format((float) ($oi->price ?? 0), 2);
                $itemLines[] = "• {$pName} x{$qty} (MRP: ₹{$pMrpStr} | ADC: ₹{$pBuyPriceStr})";
            }
        }

        if ($totalMrp <= 0) {
            $totalMrp = $totalPayable;
        }

        $totalSavings = max(0, $totalMrp - $totalPayable);
        $newBalance = number_format((float) ($adcProfile?->wallet_balance ?? 0), 2);
        $formattedMrp = number_format((float) $totalMrp, 2);
        $formattedPayable = number_format((float) $totalPayable, 2);
        $formattedSavings = number_format((float) $totalSavings, 2);
        $formattedMbp = number_format((float) $totalMbpCoins, 2);

        $advisorCode = $this->option('advisor-code') ?: $firstOrder->advisor_code;

        $msg = "🎉 *Order Placed Successfully!*\n"
            . "━━━━━━━━━━━━━━━━━━━━\n"
            . "👤 *ADC:* {$adcName} ({$adcCodeStr})\n"
            . "🧾 *Order No:* {$orderNumsStr}\n"
            . "📅 *Date:* " . now()->format('d-M-Y h:i A') . "\n"
            . "📦 *Total Quantity:* {$totalItemsCount} pcs\n"
            . "━━━━━━━━━━━━━━━━━━━━\n"
            . "🏷️ *Total MRP:* ₹{$formattedMrp}\n"
            . "💳 *ADC Paid Amount:* ₹{$formattedPayable}\n";

        if ($totalSavings > 0) {
            $msg .= "💰 *Your Instant Margin/Savings:* ₹{$formattedSavings}\n";
        }

        if ($totalMbpCoins > 0) {
            $msg .= "🪙 *MBP Coins Earned:* {$formattedMbp}\n";
        }

        $msg .= "💼 *Wallet Balance Left:* ₹{$newBalance}\n";

        if (!empty($itemLines)) {
            $msg .= "━━━━━━━━━━━━━━━━━━━━\n"
                . "📋 *Item Details:*\n"
                . implode("\n", $itemLines) . "\n";
        }

        if (!empty($advisorCode)) {
            $msg .= "━━━━━━━━━━━━━━━━━━━━\n"
                . "🤝 *Advisor (AMA):* {$advisorCode}\n";
        }

        $msg .= "━━━━━━━━━━━━━━━━━━━━\n"
            . "🙏 Thank you for ordering with Indian MSME & Abhhyam!\n"
            . "करोगे तो होगा! 🇮🇳";

        try {
            // 1. Ensure ADC is registered on TheekHai
            TheekHaiService::registerUser($adcName, $adcPhone);

            // 2. Send Message
            $sent = TheekHaiService::sendMessage($adcPhone, $msg);

            if ($sent) {
                $this->info("✓ TheekHai order message sent successfully to {$adcPhone}.");
                Log::info("TheekHai order message sent to ADC {$adcPhone} for orders: {$orderNumsStr}");
                return 0;
            } else {
                $this->warn("✗ Failed to send TheekHai order message to {$adcPhone}.");
                return 1;
            }
        } catch (\Throwable $e) {
            $this->error("TheekHai exception: " . $e->getMessage());
            Log::error("SendAdcMessage command exception: " . $e->getMessage());
            return 1;
        }
    }
}
