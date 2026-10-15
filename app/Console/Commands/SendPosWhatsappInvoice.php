<?php

namespace App\Console\Commands;

use App\Models\AdcPosCustomer;
use App\Models\AdcPosOrder;
use App\Models\User;
use App\Services\AbhhyamService;
use App\Services\TheekHaiService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SendPosWhatsappInvoice extends Command
{
    protected $signature = 'pos:send-whatsapp {order_id} {--base-url=}';
    protected $description = 'Send TheekHai WhatsApp/chat invoice notification (both text message and PDF) in background';

    public function handle()
    {
        $orderId = $this->argument('order_id');
        $posOrder = AdcPosOrder::with([
            'items.product',
            'items.localProduct',
            'items.inventory.product',
            'items.inventory.localProduct',
            'customer',
            'adc.adcProfile',
            'adc.profile',
            'invoice'
        ])->find($orderId);

        if (!$posOrder) {
            $this->error("POS Order ID {$orderId} not found.");
            return 1;
        }

        $custMobile = $posOrder->customer_mobile;
        if (empty($custMobile)) {
            $this->info("No customer mobile for order {$orderId}.");
            return 0;
        }

        try {
            $custName = $posOrder->customer_name ?: 'Customer';
            $receiverPhone = preg_replace('/[^0-9]/', '', $custMobile);
            if (strlen($receiverPhone) > 10) {
                $receiverPhone = substr($receiverPhone, -10);
            }

            // 1. Register Customer on Abhhyam Ecosystem (Background)
            $customerModel = $posOrder->customer ?: AdcPosCustomer::where('mobile', $receiverPhone)->latest()->first();
            if ($customerModel) {
                try {
                    AbhhyamService::registerCustomer($customerModel, 'ADC');
                } catch (\Throwable $e) {
                }
            } else {
                try {
                    AbhhyamService::registerCustomer([
                        'name' => $custName,
                        'mobile' => $receiverPhone,
                        'email' => '',
                    ], 'ADC');
                } catch (\Throwable $e) {
                }
            }

            // 2. Register Customer on TheekHai (Background)
            try {
                TheekHaiService::registerUser($custName, $receiverPhone);
            } catch (\Throwable $e) {
            }

            // 3. Prepare Store & Invoice Details
            $adcUser = $posOrder->adc;
            $senderPhone = config('theekhai.sender_number', '8800001259');
            $canteenCode = $adcUser?->adcProfile?->adc_code
                ?: ($adcUser?->adc_code
                    ?: ('ADC' . str_pad($adcUser?->id ?? 0, 3, '0', STR_PAD_LEFT)));

            $billAmount = number_format((float) $posOrder->total_amount, 2);
            $numberInvoice = $posOrder->invoice?->invoice_number ?? $posOrder->order_no;

            // 4. Build WhatsApp / TheekHai Text Message
            $textMsg = "🛍️ *Thank You for Shopping!*\n"
                . "Dear {$custName},\n"
                . "Thank you for shopping with Abhhyam India Ecosystem.\n"
                . "🏪 ADC Store: {$canteenCode}\n"
                . "🧾 Invoice: {$numberInvoice}\n"
                . "💰 Shopping Amount: ₹{$billAmount}\n"
                . "📅 Date: " . $posOrder->created_at->format('d/m/Y h:i A') . "\n"
                . "📎 Your Tax Invoice is attached.\n"
                . "Abhhyam Business Community (ABC)\n"
                . "Your Shopping • Your Savings • Your Future\n"
                . "करोगे तो होगा! 🇮🇳";

            // 5. Generate PDF Invoice File
            $pdfFileName = str_replace(['/', '\\'], '_', $numberInvoice) . '.pdf';
            $pdfDirectory = public_path('invoices');
            if (!file_exists($pdfDirectory)) {
                @mkdir($pdfDirectory, 0755, true);
            }
            $pdfPath = $pdfDirectory . DIRECTORY_SEPARATOR . $pdfFileName;

            $baseUrl = $this->option('base-url') ?: config('app.url', 'http://localhost');
            $pdfUrl = rtrim($baseUrl, '/') . '/invoices/' . $pdfFileName;

            $pdfGenerated = false;
            try {
                if (class_exists(Pdf::class)) {
                    $pdf = Pdf::loadView('adc.pos.invoice-pdf', ['order' => $posOrder]);
                    $pdf->setPaper([0, 0, 226.77, 600], 'portrait');
                    $pdf->save($pdfPath);
                    $pdfGenerated = file_exists($pdfPath) && filesize($pdfPath) > 0;
                }
            } catch (\Throwable $pdfEx) {

            }

            // 6. Send BOTH Text Message AND PDF to Customer
            // Step 6a: Send Text Message
            $msgSent = TheekHaiService::sendMessage($receiverPhone, $textMsg, $senderPhone);

            // Step 6b: Send PDF Document
            $pdfSent = false;
            if ($pdfGenerated) {
                $pdfSent = TheekHaiService::sendPdf($receiverPhone, $pdfUrl, $pdfFileName, $senderPhone, $textMsg);
            }

            // Display in console for clarity
            $this->line("==================================================");
            $this->info("✓ Order ID:        " . $orderId);
            $this->info("✓ Invoice No:      " . $numberInvoice);
            $this->info("✓ Customer Phone:  " . $receiverPhone);
            $this->info("✓ Generated PDF:   " . ($pdfGenerated ? 'SUCCESS' : 'FAILED'));
            $this->info("✓ PDF URL:         " . $pdfUrl);
            $this->info("✓ Message Sent:    " . ($msgSent ? 'SUCCESS' : 'FAILED'));
            $this->info("✓ PDF Sent:        " . ($pdfSent ? 'SUCCESS' : 'FAILED'));
            $this->line("==================================================");

            return 0;
        } catch (\Throwable $thEx) {
            $this->error("Error in background worker: " . $thEx->getMessage());
            return 1;
        }
    }
}
