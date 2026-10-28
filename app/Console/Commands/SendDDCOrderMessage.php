<?php

namespace App\Console\Commands;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\DDC;
use App\Services\TheekHaiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendDDCOrderMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ddc:send-order-message {po_ids : Comma-separated Purchase Order IDs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send TheekHai notification message to DDC Coordinator for new Purchase Orders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $poIdsArg = (string) $this->argument('po_ids');
        $poIds = array_filter(array_map('trim', explode(',', $poIdsArg)));

        if (empty($poIds)) {
            $this->error('No Purchase Order IDs provided.');
            return 1;
        }

        $purchaseOrders = PurchaseOrder::with([
            'ddc.profile',
            'seller.sellerProfile',
            'adcOrder.adc.profile',
            'adcOrder.adc.adcProfile',
            'items'
        ])->whereIn('id', $poIds)->get();

        if ($purchaseOrders->isEmpty()) {
            $this->error('No Purchase Orders found for IDs: ' . implode(', ', $poIds));
            return 1;
        }

        // Group POs by DDC
        $byDdc = $purchaseOrders->groupBy('ddc_id');

        foreach ($byDdc as $ddcId => $ddcPos) {
            $firstPo = $ddcPos->first();
            $ddc = $firstPo->ddc;

            if (!$ddc) {
                $ddc = DDC::with('profile')->find($ddcId);
            }

            if (!$ddc) {
                $this->warn("DDC not found for ID: {$ddcId}");
                continue;
            }

            $ddcProfile = $ddc->profile;
            $ddcPhone = $ddc->mobile 
                ?: ($ddcProfile?->official_phone 
                ?: ($ddcProfile?->phone ?? null));

            if (empty($ddcPhone)) {
                $this->warn("DDC ID {$ddcId} has no mobile phone recorded.");
                continue;
            }

            $ddcName = $ddc->name 
                ?: ($ddcProfile?->ddc_owner_name 
                ?: ($ddcProfile?->company_name ?: 'DDC Coordinator'));

            $ddcCode = $ddcProfile?->ddc_code 
                ?: ($ddc->code ?: ('DDC' . str_pad($ddc->id ?? 0, 3, '0', STR_PAD_LEFT)));

            foreach ($ddcPos as $po) {
                $poAmount = (float) $po->total_amount;
                $formattedAmount = number_format($poAmount, 2);

                $seller = $po->seller;
                $sellerProfile = $seller?->sellerProfile;
                $sellerName = $seller?->name 
                    ?: ($sellerProfile?->company_name ?: ($seller?->company_name ?: 'Seller Partner'));

                // ADC / ASC details
                $adcCodeStr = 'N/A';
                $canteenNameStr = 'Direct DDC Order';
                $canteenMobileStr = '';

                if ($po->adcOrder?->adc) {
                    $adc = $po->adcOrder->adc;
                    $adcProfile = $adc->profile ?: $adc->adcProfile;

                    $adcCodeVal = $adcProfile?->adc_code 
                        ?: ($adc->adc_code 
                        ?: ($adcProfile?->code 
                        ?: ('ASC' . str_pad($adc->id ?? 0, 3, '0', STR_PAD_LEFT))));

                    $adcCodeStr = $adcCodeVal;

                    $canteenNameStr = $adcProfile?->company_name 
                        ?: ($adcProfile?->trade_name ?: ($adc->name ?: 'ADC Canteen'));

                    $canteenMobileStr = $adc->mobile ?: ($adcProfile?->official_phone ?? '');
                }

                $msg = "Dear DDC Partner,\n"
                    . "A new Purchase Order has been placed in your DDC area through IndianMSME.org.\n"
                    . "━━━━━━━━━━━━━━━━━━━━\n"
                    . "🏢 DDC: {$ddcName} ({$ddcCode})\n"
                    . "🧾 PO No: {$po->po_number}\n"
                    . "🏪 Canteen Name: {$canteenNameStr}\n"
                    . "🆔 ADC / ASC No: {$adcCodeStr}\n";

                if (!empty($canteenMobileStr)) {
                    $msg .= "📞 ADC Contact: {$canteenMobileStr}\n";
                }

                $msg .= "📅 Date: " . now()->format('d-M-Y h:i A') . "\n"
                    . "💰 Order Value: ₹{$formattedAmount}\n"
                    . "━━━━━━━━━━━━━━━━━━━━\n"
                    . "📋 Please check the DDC Portal for complete order routing, stock and delivery.\n"
                    . "🙏 Thank you for partnering with IndianMSME.org.\n"
                    . "🇮🇳 करेंगे तो होगा!";

                try {
                    // 1. Ensure DDC phone is registered on TheekHai
                    TheekHaiService::registerUser($ddcName, $ddcPhone);

                    // 2. Send Message via TheekHai Message API
                    $sent = TheekHaiService::sendMessage($ddcPhone, $msg);

                    if ($sent) {
                        $this->info("✓ TheekHai order message sent to DDC {$ddcPhone} for PO: {$po->po_number}");
                        Log::info("TheekHai order message sent to DDC {$ddcPhone} for PO: {$po->po_number}");
                    } else {
                        $this->warn("✗ Failed to send TheekHai order message to DDC {$ddcPhone} for PO: {$po->po_number}");
                        Log::warning("TheekHai order message send failed to DDC {$ddcPhone} for PO: {$po->po_number}");
                    }
                } catch (\Throwable $e) {
                    $this->error("TheekHai exception for DDC {$ddcPhone}: " . $e->getMessage());
                    Log::error("SendDDCOrderMessage command exception for PO {$po->po_number}: " . $e->getMessage());
                }
            }
        }

        return 0;
    }
}
