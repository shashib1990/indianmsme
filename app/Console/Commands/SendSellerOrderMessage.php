<?php

namespace App\Console\Commands;

use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\User;
use App\Services\TheekHaiService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendSellerOrderMessage extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'seller:send-order-message {po_ids : Comma-separated Purchase Order IDs}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send TheekHai notification message to Seller for new Purchase Orders';

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
            'seller.sellerProfile',
            'ddc.profile',
            'adcOrder.adc',
            'items'
        ])->whereIn('id', $poIds)->get();

        if ($purchaseOrders->isEmpty()) {
            $this->error('No Purchase Orders found for IDs: ' . implode(', ', $poIds));
            return 1;
        }

        // Group purchase orders by seller to send consolidated or per-seller notifications
        $bySeller = $purchaseOrders->groupBy('seller_id');

        foreach ($bySeller as $sellerId => $sellerPos) {
            $seller = $sellerPos->first()->seller;
            if (!$seller) {
                $seller = User::with('sellerProfile')->find($sellerId);
            }

            if (!$seller) {
                $this->warn("Seller not found for ID: {$sellerId}");
                continue;
            }

            $sellerProfile = $seller->sellerProfile;
            $sellerPhone = $seller->mobile 
                ?: ($seller->official_phone 
                ?: ($sellerProfile?->alternate_mobile ?? null));

            if (empty($sellerPhone)) {
                $this->warn("Seller ID {$sellerId} has no mobile phone recorded.");
                continue;
            }

            $sellerName = $seller->name 
                ?: ($sellerProfile?->authorized_rep_name 
                ?: ($seller->company_name 
                ?: ($sellerProfile?->company_name ?: 'Seller Partner')));

            $sellerCode = $sellerProfile?->seller_id 
                ?: ($seller->seller_id ?: ('IMV' . $seller->id));

            foreach ($sellerPos as $po) {
                $items = $po->items;
                if ($items->isEmpty()) {
                    $items = PurchaseOrderItem::where('purchase_order_id', $po->id)->get();
                }

                $totalQty = 0;
                $itemLines = [];

                foreach ($items as $item) {
                    $qty = (int) ($item->quantity ?? 1);
                    $totalQty += $qty;

                    $prodName = trim((string) ($item->product_name ?? 'Product'));
                    $itemPrice = (float) ($item->seller_price ?: $item->price);
                    $itemPriceStr = number_format($itemPrice, 2);
                    $itemLines[] = "• {$prodName} x{$qty} (Rate: ₹{$itemPriceStr})";
                }

                $poAmount = (float) ($po->seller_total_amount ?: $po->total_amount);
                $formattedAmount = number_format($poAmount, 2);

                // Source details (DDC / ADC)
                $sourceName = 'Indian MSME';
                if ($po->adcOrder?->adc) {
                    $adc = $po->adcOrder->adc;
                    $adcName = $adc->name ?: 'ADC Partner';
                    $sourceName = "ADC: {$adcName}";
                } elseif ($po->ddc) {
                    $ddcName = $po->ddc->name ?: 'DDC Depot';
                    $sourceName = "DDC: {$ddcName}";
                }

                $msg = "Dear Seller,\n"
                    . "You have received a new Purchase Order through IndianMSME.org.\n"
                    . "━━━━━━━━━━━━━━━━━━━━\n"
                    . "PO No: {$po->po_number}\n"
                    . "Date: " . now()->format('d-M-Y h:i A') . "\n"
                    . "Order Value: ₹{$formattedAmount}\n"
                    . "━━━━━━━━━━━━━━━━━━━━\n"
                    . "📋 Please log in to your Seller Portal to review the order details and accept the Purchase Order.\n"
                    . "✅ Kindly verify the ordered items, quantities, rates, and availability before accepting the order.\n"
                    . "🙏 Thank you for partnering with IndianMSME.org.\n"
                    . "🇮🇳 करेंगे तो होगा!";

                try {
                    // 1. Ensure Seller is registered on TheekHai
                    TheekHaiService::registerUser($sellerName, $sellerPhone);

                    // 2. Send Message via TheekHai Message API
                    $sent = TheekHaiService::sendMessage($sellerPhone, $msg);

                    if ($sent) {
                        $this->info("✓ TheekHai order message sent to Seller {$sellerPhone} for PO: {$po->po_number}");
                        Log::info("TheekHai order message sent to Seller {$sellerPhone} for PO: {$po->po_number}");
                    } else {
                        $this->warn("✗ Failed to send TheekHai order message to Seller {$sellerPhone} for PO: {$po->po_number}");
                        Log::warning("TheekHai order message send failed to Seller {$sellerPhone} for PO: {$po->po_number}");
                    }
                } catch (\Throwable $e) {
                    $this->error("TheekHai exception for Seller {$sellerPhone}: " . $e->getMessage());
                    Log::error("SendSellerOrderMessage command exception for PO {$po->po_number}: " . $e->getMessage());
                }
            }
        }

        return 0;
    }
}
