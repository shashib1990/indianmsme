<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use App\Models\Invoice;
use App\Models\State;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    /**
     * Display the printable tax invoice for a purchase order.
     */
    public function printInvoice($id)
    {
        $purchaseOrder = PurchaseOrder::with([
            'invoice',
            'items.product',
            'seller.sellerProfile',
            'ddc.ddcProfile',
            'adcOrder.adc.adcProfile',
            'adcOrder.adc.profile',
            'pickupStore'
        ])->findOrFail($id);

        // Authorization check across active panels/guards
        $this->authorizeInvoiceAccess($purchaseOrder);

        // Dynamic invoice generation safety net
        $invoice = $purchaseOrder->invoice;
        $extractedDetails = Invoice::extractInvoiceDetails($purchaseOrder);

        if (!$invoice && in_array($purchaseOrder->dispatch_status, ['Dispatched', 'Received', 'Completed'])) {
            $supplierGstin = $extractedDetails['supplier_gstin'] ?? 'N/A';
            $invoiceDetails = Invoice::generateNextInvoiceNumber(now(), $supplierGstin, 'INV');
            $invoice = Invoice::create(array_merge([
                'purchase_order_id' => $purchaseOrder->id,
                'invoice_number' => $invoiceDetails['invoice_number'],
                'financial_year' => $invoiceDetails['financial_year'],
                'serial_number' => $invoiceDetails['serial_number'],
                'invoice_date' => $invoiceDetails['invoice_date'],
            ], $extractedDetails));
            $purchaseOrder->setRelation('invoice', $invoice);
        } elseif ($invoice) {
            $isVirtualPo = ($purchaseOrder->fulfillment_type === 'virtual') || !empty($purchaseOrder->adc_order_id);
            $needsPickupStoreUpdate = $purchaseOrder->pickupStore && ($invoice->dispatch_from_name !== $purchaseOrder->pickupStore->pickup_store_name);
            if ($isVirtualPo && ($invoice->type !== 'adc_virtual_po' || str_contains(strtolower($invoice->bill_to_name ?? ''), 'asc distributor'))) {
                $invoice->update($extractedDetails);
            } elseif ($needsPickupStoreUpdate) {
                $invoice->update($extractedDetails);
            }
        }

        if (!$invoice) {
            abort(404, 'Invoice has not been generated for this order yet. Order must be Dispatched first.');
        }

        // Fetch state IDs & state names
        $sellerStateId = $purchaseOrder->seller->sellerProfile->state_id ?? $purchaseOrder->seller->state_id ?? null;
        $ddcStateId = $purchaseOrder->ddc->ddcProfile->state_id ?? $purchaseOrder->ddc->state_id ?? null;

        $dispatchFromState = $invoice->dispatch_from_state ?? $purchaseOrder->pickupStore?->stateRelation?->name ?? $purchaseOrder->pickupStore?->state ?? $purchaseOrder->seller?->sellerProfile?->state ?? $purchaseOrder->seller?->state;
        $ddcStateName = $purchaseOrder->ddc?->ddcProfile?->state ?? $purchaseOrder->ddc?->state ?? null;
        $billToStateName = $invoice->bill_to_state ?? $ddcStateName;
        $invoiceFromState = 'Delhi';

        // Determine if intra-state or inter-state for GST split (CGST + SGST vs IGST)
        // Intra-state (CGST+SGST) applies ONLY when Supplier (Dispatch From), DDC Hub, and Bill To states match.
        // If Seller is in Rajasthan and DDC Hub is in Delhi (or Header Invoice From is Delhi and Bill To is Rajasthan), it is Inter-State (IGST).
        $isIntraState = false;
        if ($dispatchFromState && $billToStateName) {
            if ($ddcStateName && (strtolower(trim($dispatchFromState)) !== strtolower(trim($ddcStateName)))) {
                $isIntraState = false;
            } elseif (strtolower(trim($invoiceFromState)) !== strtolower(trim($billToStateName))) {
                $isIntraState = false;
            } else {
                $isIntraState = (strtolower(trim($dispatchFromState)) === strtolower(trim($billToStateName)));
            }
        } else {
            if ($sellerStateId && $ddcStateId) {
                $isIntraState = ($sellerStateId == $ddcStateId);
            }
        }

        // Calculate item pricing breakdowns
        $processedItems = [];
        $rateTaxSummary = [];
        $hsnTaxSummary = [];
        $totalQty = 0;
        $totalTaxableAmount = 0.0;
        $totalTaxAmount = 0.0;
        $grandTotal = 0.0;

        foreach ($purchaseOrder->items as $item) {
            if ($item->status !== 'Accepted') {
                continue;
            }
            $product = $item->product;
            $qty = (int) $item->quantity;
            $totalQty += $qty;

            $gstRate = (float) ($item->gst_rate ?? $product?->gst_rate ?? 0);
            
            // Total price is DDC purchase price (inclusive of GST)
            $totalPriceInclGst = (float) $item->total;
            $unitPriceInclGst = (float) $item->price;

            // Full precision intermediate calculations (Excel-style)
            $rawTaxableValue = $gstRate > 0 ? ($totalPriceInclGst / (1.0 + ($gstRate / 100.0))) : $totalPriceInclGst;
            $rawRatePerItemTaxable = $gstRate > 0 ? ($unitPriceInclGst / (1.0 + ($gstRate / 100.0))) : $unitPriceInclGst;

            if ($isIntraState) {
                $cgstRate = $gstRate / 2.0;
                $sgstRate = $gstRate / 2.0;
                $rawCgst = $rawTaxableValue * ($cgstRate / 100.0);
                $rawSgst = $rawTaxableValue * ($sgstRate / 100.0);
                $cgstAmt = round($rawCgst, 2);
                $sgstAmt = round($rawSgst, 2);
                $igstRate = 0.0;
                $igstAmt = 0.0;
                $rawTaxAmount = $rawCgst + $rawSgst;
                $itemTaxValue = $cgstAmt + $sgstAmt;
            } else {
                $cgstRate = 0.0;
                $sgstRate = 0.0;
                $cgstAmt = 0.0;
                $sgstAmt = 0.0;
                $igstRate = $gstRate;
                $rawIgst = $rawTaxableValue * ($igstRate / 100.0);
                $igstAmt = round($rawIgst, 2);
                $rawTaxAmount = $rawIgst;
                $itemTaxValue = $igstAmt;
            }

            $totalTaxableAmount += $rawTaxableValue;
            $totalTaxAmount += $rawTaxAmount;
            $grandTotal += $totalPriceInclGst;

            $processedItems[] = [
                'name' => $item->product_name ?? $product?->product_name ?? 'Product',
                'barcode' => $item->product_code ?? $item->barcode ?? $product?->barcode ?? 'N/A',
                'hsn_code' => $item->hsn_code ?? $product?->hsn_code ?? 'N/A',
                'qty' => $qty,
                'rate_per_item' => round($rawRatePerItemTaxable, 2),
                'taxable_value' => round($rawTaxableValue, 2),
                'gst_rate' => $gstRate,
                'cgst_rate' => $cgstRate,
                'cgst_amount' => $cgstAmt,
                'sgst_rate' => $sgstRate,
                'sgst_amount' => $sgstAmt,
                'igst_rate' => $igstRate,
                'igst_amount' => $igstAmt,
                'tax_value' => round($itemTaxValue, 2),
                'amount' => round($totalPriceInclGst, 2)
            ];

            // 1. Build Rate-Wise Tax Summary Breakdown (without HSN)
            $rateKey = number_format($gstRate, 1);
            if (!isset($rateTaxSummary[$rateKey])) {
                $rateTaxSummary[$rateKey] = [
                    'rate' => $gstRate,
                    'cgst_rate' => $cgstRate,
                    'sgst_rate' => $sgstRate,
                    'igst_rate' => $igstRate,
                    'qty' => 0,
                    'raw_taxable_value' => 0.0,
                    'raw_cgst' => 0.0,
                    'raw_sgst' => 0.0,
                    'raw_igst' => 0.0,
                    'amount' => 0.0
                ];
            }
            $rateTaxSummary[$rateKey]['qty'] += $qty;
            $rateTaxSummary[$rateKey]['raw_taxable_value'] += $rawTaxableValue;
            if ($isIntraState) {
                $rateTaxSummary[$rateKey]['raw_cgst'] += $rawTaxableValue * (($gstRate / 2.0) / 100.0);
                $rateTaxSummary[$rateKey]['raw_sgst'] += $rawTaxableValue * (($gstRate / 2.0) / 100.0);
            } else {
                $rateTaxSummary[$rateKey]['raw_igst'] += $rawTaxableValue * ($gstRate / 100.0);
            }
            $rateTaxSummary[$rateKey]['amount'] += $totalPriceInclGst;

            // 2. Build HSN-Wise Tax Summary
            $hsnCode = trim((string) ($item->hsn_code ?? $product?->hsn_code ?? 'N/A'));
            if (empty($hsnCode)) {
                $hsnCode = 'N/A';
            }
            $hsnKey = $hsnCode . '_' . number_format($gstRate, 1);
            if (!isset($hsnTaxSummary[$hsnKey])) {
                $hsnTaxSummary[$hsnKey] = [
                    'hsn_code' => $hsnCode,
                    'rate' => $gstRate,
                    'qty' => 0,
                    'raw_taxable_value' => 0.0,
                    'raw_tax_amount' => 0.0,
                    'amount' => 0.0
                ];
            }
            $hsnTaxSummary[$hsnKey]['qty'] += $qty;
            $hsnTaxSummary[$hsnKey]['raw_taxable_value'] += $rawTaxableValue;
            $hsnTaxSummary[$hsnKey]['raw_tax_amount'] += $rawTaxAmount;
            $hsnTaxSummary[$hsnKey]['amount'] += $totalPriceInclGst;
        }

        // Round final rateTaxSummary figures to 2 decimal places
        foreach ($rateTaxSummary as $key => $summary) {
            $taxableVal = round($summary['raw_taxable_value'], 2);
            $cgstVal = $isIntraState ? round($summary['raw_cgst'], 2) : 0.0;
            $sgstVal = $isIntraState ? round($summary['raw_sgst'], 2) : 0.0;
            $igstVal = !$isIntraState ? round($summary['raw_igst'], 2) : 0.0;
            $taxVal = $isIntraState ? ($cgstVal + $sgstVal) : $igstVal;

            $rateTaxSummary[$key]['taxable_value'] = $taxableVal;
            $rateTaxSummary[$key]['cgst_amount'] = $cgstVal;
            $rateTaxSummary[$key]['sgst_amount'] = $sgstVal;
            $rateTaxSummary[$key]['igst_amount'] = $igstVal;
            $rateTaxSummary[$key]['tax_value'] = $taxVal;
            $rateTaxSummary[$key]['amount'] = round($summary['amount'], 2);
        }
        ksort($rateTaxSummary);

        // Round final hsnTaxSummary figures to 2 decimal places
        foreach ($hsnTaxSummary as $key => $summary) {
            $taxableVal = round($summary['raw_taxable_value'], 2);
            $taxVal = round($summary['raw_tax_amount'], 2);

            $hsnTaxSummary[$key]['taxable_value'] = $taxableVal;
            $hsnTaxSummary[$key]['tax_value'] = $taxVal;
            $hsnTaxSummary[$key]['amount'] = round($summary['amount'], 2);
        }
        ksort($hsnTaxSummary);

        $taxSummary = $rateTaxSummary;

        // Final monetary values rounded to 2 decimals
        $totalTaxableAmount = round($totalTaxableAmount, 2);
        $totalTaxAmount = round($totalTaxAmount, 2);
        $grandTotal = round($grandTotal, 2);

        // Grand total round off calculation
        $roundedGrandTotal = round($grandTotal);
        $roundOff = round($roundedGrandTotal - $grandTotal, 2);

        // Convert amount to words
        $amountInWords = $this->numberToWords($roundedGrandTotal) . ' Rupees Only';

        // Fetch display names for states
        $sellerState = $sellerStateId ? State::find($sellerStateId) : null;
        $ddcState = $ddcStateId ? State::find($ddcStateId) : null;

        return view('invoices.print', compact(
            'purchaseOrder',
            'invoice',
            'processedItems',
            'taxSummary',
            'rateTaxSummary',
            'hsnTaxSummary',
            'totalQty',
            'totalTaxableAmount',
            'totalTaxAmount',
            'grandTotal',
            'roundedGrandTotal',
            'roundOff',
            'amountInWords',
            'isIntraState',
            'sellerState',
            'ddcState'
        ));
    }

    /**
     * Display printable tax invoice directly by Invoice ID.
     */
    public function printInvoiceById($invoiceId)
    {
        $invoice = Invoice::findOrFail($invoiceId);
        $purchaseOrder = PurchaseOrder::with([
            'invoice',
            'items.product',
            'seller.sellerProfile',
            'ddc.ddcProfile',
            'adcOrder.adc.adcProfile',
            'adcOrder.adc.profile',
            'pickupStore'
        ])->findOrFail($invoice->purchase_order_id);

        $this->authorizeInvoiceAccess($purchaseOrder);

        $isVirtualPo = ($purchaseOrder->fulfillment_type === 'virtual') || !empty($purchaseOrder->adc_order_id);
        if ($isVirtualPo && ($invoice->type !== 'adc_virtual_po' || str_contains(strtolower($invoice->bill_to_name ?? ''), 'asc distributor'))) {
            $extractedDetails = Invoice::extractInvoiceDetails($purchaseOrder);
            $invoice->update($extractedDetails);
        }

        $sellerStateId = $purchaseOrder->seller->sellerProfile->state_id ?? $purchaseOrder->seller->state_id ?? null;
        $ddcStateId = $purchaseOrder->ddc->ddcProfile->state_id ?? $purchaseOrder->ddc->state_id ?? null;

        $isIntraState = false;
        if ($invoice && $invoice->dispatch_from_state && $invoice->bill_to_state) {
            $isIntraState = (strtolower(trim($invoice->dispatch_from_state)) === strtolower(trim($invoice->bill_to_state)));
        } else {
            if ($sellerStateId && $ddcStateId) {
                $isIntraState = ($sellerStateId == $ddcStateId);
            }
        }

        $processedItems = [];
        $taxSummary = [];
        $totalQty = 0;
        $totalTaxableAmount = 0.0;
        $totalTaxAmount = 0.0;
        $grandTotal = 0.0;

        $isExcessInvoice = ($invoice->type === 'excess_stock_ddc');

        foreach ($purchaseOrder->items as $item) {
            if (!$isExcessInvoice && $item->status !== 'Accepted') {
                continue;
            }

            $qty = $isExcessInvoice ? (int) $item->excess_qty : (int) $item->quantity;
            if ($qty <= 0) {
                continue;
            }

            $product = $item->product;
            $totalQty += $qty;
            $gstRate = (float) ($item->gst_rate ?? $product?->gst_rate ?? 0);

            $unitPriceInclGst = (float) $item->price;
            $totalPriceInclGst = $unitPriceInclGst * $qty;

            $rawTaxableValue = $gstRate > 0 ? ($totalPriceInclGst / (1.0 + ($gstRate / 100.0))) : $totalPriceInclGst;
            $rawRatePerItemTaxable = $gstRate > 0 ? ($unitPriceInclGst / (1.0 + ($gstRate / 100.0))) : $unitPriceInclGst;

            if ($isIntraState) {
                $cgstRate = $gstRate / 2.0;
                $sgstRate = $gstRate / 2.0;
                $rawCgst = $rawTaxableValue * ($cgstRate / 100.0);
                $rawSgst = $rawTaxableValue * ($sgstRate / 100.0);
                $cgstAmt = round($rawCgst, 2);
                $sgstAmt = round($rawSgst, 2);
                $igstRate = 0.0;
                $igstAmt = 0.0;
                $rawTaxAmount = $rawCgst + $rawSgst;
                $itemTaxValue = $cgstAmt + $sgstAmt;
            } else {
                $cgstRate = 0.0;
                $sgstRate = 0.0;
                $cgstAmt = 0.0;
                $sgstAmt = 0.0;
                $igstRate = $gstRate;
                $rawIgst = $rawTaxableValue * ($igstRate / 100.0);
                $igstAmt = round($rawIgst, 2);
                $rawTaxAmount = $rawIgst;
                $itemTaxValue = $igstAmt;
            }

            $totalTaxableAmount += $rawTaxableValue;
            $totalTaxAmount += $rawTaxAmount;
            $grandTotal += $totalPriceInclGst;

            $processedItems[] = [
                'name' => $item->product_name ?? $product?->product_name ?? 'Product',
                'barcode' => $item->product_code ?? $item->barcode ?? $product?->barcode ?? 'N/A',
                'hsn_code' => $item->hsn_code ?? $product?->hsn_code ?? 'N/A',
                'qty' => $qty,
                'rate_per_item' => round($rawRatePerItemTaxable, 2),
                'taxable_value' => round($rawTaxableValue, 2),
                'gst_rate' => $gstRate,
                'cgst_rate' => $cgstRate,
                'cgst_amount' => $cgstAmt,
                'sgst_rate' => $sgstRate,
                'sgst_amount' => $sgstAmt,
                'igst_rate' => $igstRate,
                'igst_amount' => $igstAmt,
                'tax_value' => round($itemTaxValue, 2),
                'amount' => round($totalPriceInclGst, 2)
            ];

            $hsnCode = trim((string) ($item->hsn_code ?? $product?->hsn_code ?? 'N/A'));
            if (empty($hsnCode)) {
                $hsnCode = 'N/A';
            }
            $taxKey = $hsnCode . '_' . number_format($gstRate, 1);

            if (!isset($taxSummary[$taxKey])) {
                $taxSummary[$taxKey] = [
                    'hsn_code' => $hsnCode,
                    'rate' => $gstRate,
                    'cgst_rate' => $cgstRate,
                    'sgst_rate' => $sgstRate,
                    'igst_rate' => $igstRate,
                    'qty' => 0,
                    'raw_taxable_value' => 0.0,
                    'raw_cgst' => 0.0,
                    'raw_sgst' => 0.0,
                    'raw_igst' => 0.0,
                    'amount' => 0.0
                ];
            }
            $taxSummary[$taxKey]['qty'] += $qty;
            $taxSummary[$taxKey]['raw_taxable_value'] += $rawTaxableValue;
            if ($isIntraState) {
                $taxSummary[$taxKey]['raw_cgst'] += $rawTaxableValue * (($gstRate / 2.0) / 100.0);
                $taxSummary[$taxKey]['raw_sgst'] += $rawTaxableValue * (($gstRate / 2.0) / 100.0);
            } else {
                $taxSummary[$taxKey]['raw_igst'] += $rawTaxableValue * ($gstRate / 100.0);
            }
            $taxSummary[$taxKey]['amount'] += $totalPriceInclGst;
        }

        foreach ($taxSummary as $key => $summary) {
            $taxableVal = round($summary['raw_taxable_value'], 2);
            $cgstVal = $isIntraState ? round($summary['raw_cgst'], 2) : 0.0;
            $sgstVal = $isIntraState ? round($summary['raw_sgst'], 2) : 0.0;
            $igstVal = !$isIntraState ? round($summary['raw_igst'], 2) : 0.0;
            $taxVal = $isIntraState ? ($cgstVal + $sgstVal) : $igstVal;

            $taxSummary[$key]['taxable_value'] = $taxableVal;
            $taxSummary[$key]['cgst_amount'] = $cgstVal;
            $taxSummary[$key]['sgst_amount'] = $sgstVal;
            $taxSummary[$key]['igst_amount'] = $igstVal;
            $taxSummary[$key]['tax_value'] = $taxVal;
            $taxSummary[$key]['amount'] = round($summary['amount'], 2);
        }

        ksort($taxSummary);

        $totalTaxableAmount = round($totalTaxableAmount, 2);
        $totalTaxAmount = round($totalTaxAmount, 2);
        $grandTotal = round($grandTotal, 2);

        $roundedGrandTotal = round($grandTotal);
        $roundOff = round($roundedGrandTotal - $grandTotal, 2);

        $amountInWords = $this->numberToWords($roundedGrandTotal) . ' Rupees Only';

        $sellerState = $sellerStateId ? State::find($sellerStateId) : null;
        $ddcState = $ddcStateId ? State::find($ddcStateId) : null;

        return view('invoices.print', compact(
            'purchaseOrder',
            'invoice',
            'processedItems',
            'taxSummary',
            'totalQty',
            'totalTaxableAmount',
            'totalTaxAmount',
            'grandTotal',
            'roundedGrandTotal',
            'roundOff',
            'amountInWords',
            'isIntraState',
            'sellerState',
            'ddcState'
        ));
    }

    /**
     * Update transporter/delivery details on the invoice.
     */
    public function updateTransporter(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::with(['invoice'])->findOrFail($id);

        $this->authorizeInvoiceAccess($purchaseOrder);

        $invoice = $purchaseOrder->invoice;
        if (!$invoice) {
            return response()->json(['success' => false, 'message' => 'Invoice has not been generated yet.'], 400);
        }

        $request->validate([
            'transporter_name' => 'nullable|string|max:255',
            'vehicle_number' => 'nullable|string|max:255',
            'transporter_gst' => 'nullable|string|max:255',
            'eway_bill_number' => 'nullable|string|max:255',
        ]);

        $invoice->update([
            'transporter_name' => $request->input('transporter_name'),
            'vehicle_number' => $request->input('vehicle_number'),
            'transporter_gst' => $request->input('transporter_gst'),
            'eway_bill_number' => $request->input('eway_bill_number'),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Transporter details updated successfully.',
            'data' => $invoice
        ]);
    }

    /**
     * Upload / Update the invoice file document.
     */
    public function uploadInvoiceFile(Request $request, $id)
    {
        $purchaseOrder = PurchaseOrder::with(['invoice'])->findOrFail($id);

        $this->authorizeInvoiceAccess($purchaseOrder);

        // Security check: Only the designated dispatch authority can upload/edit the invoice.
        $hasStoreDispatch = $purchaseOrder->pickupStore && $purchaseOrder->pickupStore->can_receive_payment_and_dispatch;
        
        if ($hasStoreDispatch && !auth('pickup_store')->check()) {
            return redirect()->back()->with('error', 'Only the assigned Authorized Pickup Store can manage the invoice for this order.');
        }
        
        if (!$hasStoreDispatch && !auth('vendor')->check()) {
            return redirect()->back()->with('error', 'Only the Vendor/Seller can manage the invoice for this order.');
        }

        $request->validate([
            'invoice_file' => 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $invoiceFileUrl = $this->uploadFileToService($request->file('invoice_file'), 'invoices');

        $invoice = $purchaseOrder->invoice;
        if (!$invoice) {
            $extractedDetails = Invoice::extractInvoiceDetails($purchaseOrder);
            $supplierGstin = $extractedDetails['supplier_gstin'] ?? 'N/A';
            $invoiceDetails = Invoice::generateNextInvoiceNumber(now(), $supplierGstin, 'INV');
            $invoice = Invoice::create(array_merge([
                'purchase_order_id' => $purchaseOrder->id,
                'invoice_number' => $invoiceDetails['invoice_number'],
                'financial_year' => $invoiceDetails['financial_year'],
                'serial_number' => $invoiceDetails['serial_number'],
                'invoice_date' => $invoiceDetails['invoice_date'],
                'invoice_file' => $invoiceFileUrl,
            ], $extractedDetails));
        } else {
            $invoice->update([
                'invoice_file' => $invoiceFileUrl,
            ]);
        }

        return redirect()->back()->with('success', 'Invoice document uploaded/updated successfully.');
    }

    /**
     * Verify if the currently authenticated user session has access to view/edit this invoice.
     */
    protected function authorizeInvoiceAccess(PurchaseOrder $purchaseOrder): void
    {
        if (auth('admin')->check()) {
            return;
        }

        if (auth('vendor')->check()) {
            $vendorId = auth('vendor')->id();
            // Vendor owner or Sub-seller parent
            if ($vendorId == $purchaseOrder->seller_id || $vendorId == ($purchaseOrder->seller->parent_id ?? null)) {
                return;
            }
        }

        if (auth('pickup_store')->check()) {
            if (auth('pickup_store')->id() == $purchaseOrder->pickup_store_id) {
                return;
            }
        }

        if (auth('ddc')->check()) {
            if (auth('ddc')->id() == $purchaseOrder->ddc_id) {
                return;
            }
        }

        abort(403, 'Unauthorized access to this invoice.');
    }

    /**
     * Helper: Convert number to Indian currency words format.
     */
    protected function numberToWords($number): string
    {
        $no = (int) round($number);
        $decimal = '';
        $hundred = null;
        $digits_length = strlen($no);
        $i = 0;
        $str = [];
        $words = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen', 20 => 'Twenty',
            30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty', 60 => 'Sixty', 70 => 'Seventy',
            80 => 'Eighty', 90 => 'Ninety'
        ];
        $digits = ['', 'Hundred', 'Thousand', 'Lakh', 'Crore'];
        
        while ($i < $digits_length) {
            $divider = ($i == 2) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;
            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : null;
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : null;
                $str [] = ($number < 21) ? $words[$number] . ' ' . $digits[$counter] . $plural . ' ' . $hundred
                    : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
            } else {
                $str[] = null;
            }
        }
        
        $Rupees = array_reverse(array_filter($str));
        $result = implode('', $Rupees);
        
        return trim($result);
    }
}
