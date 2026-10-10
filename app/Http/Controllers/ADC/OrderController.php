<?php

namespace App\Http\Controllers\ADC;

use App\Http\Controllers\Controller;
use App\Models\AdcOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Models\AdcInventory;
use App\Models\AdcOrderItem;
use App\Models\PurchaseOrder;
use App\Models\Invoice;
use App\Services\NotificationService;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    private function adc()
    {
        return Auth::guard('adc')->user();
    }

    public function index(Request $request)
    {
        $adc = $this->adc();
        $query = AdcOrder::with([
            'items.product',
            'items.seller.sellerProfile',
            'purchaseOrders.seller.sellerProfile',
            'selectedDdc',
            'virtualDdc'
        ])->where('adc_id', $adc->id);

        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhere('shipping_address', 'like', "%{$search}%")
                  ->orWhereHas('items.seller', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                         ->orWhereHas('sellerProfile', fn($sp) => $sp->where('company_name', 'like', "%{$search}%"));
                  })
                  ->orWhereHas('purchaseOrders.seller', function ($sq) use ($search) {
                      $sq->where('name', 'like', "%{$search}%")
                         ->orWhereHas('sellerProfile', fn($sp) => $sp->where('company_name', 'like', "%{$search}%"));
                  });
            });
        }

        if ($request->has('status') && !empty($request->status)) {
            $query->where('status', $request->status);
        }

        if ($request->has('fulfillment_type') && !empty($request->fulfillment_type)) {
            $query->where('fulfillment_type', $request->fulfillment_type);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(15)->withQueryString();

        return view('adc.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $adc = $this->adc();
        $order = AdcOrder::with([
            'items.product',
            'items.seller.sellerProfile',
            'items.purchaseOrder.items',
            'items.purchaseOrder.invoice',
            'items.purchaseOrder.pickupStore.districtRelation',
            'items.purchaseOrder.pickupStore.stateRelation',
            'items.purchaseOrder.seller.sellerProfile',
            'purchaseOrders.seller.sellerProfile',
            'purchaseOrders.pickupStore.districtRelation',
            'purchaseOrders.pickupStore.stateRelation',
            'purchaseOrders.invoice',
            'purchaseOrders.items',
            'selectedDdc.profile',
            'selectedDdc.ddcProfile',
            'virtualDdc.profile',
            'virtualDdc.ddcProfile',
            'assignedDdc.profile',
            'assignedDdc.ddcProfile'
        ])
            ->where('adc_id', $adc->id)
            ->findOrFail($id);

        return view('adc.orders.show', compact('order'));
    }

    public function showReceivePage($id)
    {
        $adc = $this->adc();
        $order = AdcOrder::with([
            'items.product', 
            'items.seller', 
            'items.purchaseOrder.items', 
            'items.purchaseOrder.invoice', 
            'selectedDdc', 
            'virtualDdc'
        ])
            ->where('adc_id', $adc->id)
            ->findOrFail($id);

        if (!in_array($order->status, ['Dispatched', 'Partially Dispatched', 'Received', 'Delivered'])) {
            return redirect()->route('adc.orders.show', $id)->with('error', 'Invoice can only be uploaded after the order status is Dispatched.');
        }

        $items = $order->items->filter(function ($item) {
            if (in_array($item->status, ['Rejected', 'Cancelled'])) {
                return false;
            }
            if ($item->purchaseOrder && in_array($item->purchaseOrder->status, ['Cancelled', 'Rejected'])) {
                return false;
            }
            if ($item->purchaseOrder) {
                $poItem = $item->purchaseOrder->items->firstWhere('product_id', $item->product_id);
                if ($poItem && in_array($poItem->status, ['Cancelled', 'Rejected'])) {
                    return false;
                }
            }
            return in_array($item->status, ['Dispatched', 'Received']);
        });

        $rejectedItems = $order->items->filter(function ($item) {
            if (in_array($item->status, ['Rejected', 'Cancelled'])) {
                return true;
            }
            if ($item->purchaseOrder && in_array($item->purchaseOrder->status, ['Cancelled', 'Rejected'])) {
                return true;
            }
            if ($item->purchaseOrder) {
                $poItem = $item->purchaseOrder->items->firstWhere('product_id', $item->product_id);
                if ($poItem && in_array($poItem->status, ['Cancelled', 'Rejected'])) {
                    return true;
                }
            }
            return false;
        });

        return view('adc.orders.receive', compact('order', 'items', 'rejectedItems'));
    }

    public function uploadReceivedInvoice(Request $request, $id)
    {
        $adc = $this->adc();
        $order = AdcOrder::where('adc_id', $adc->id)->findOrFail($id);

        if (!in_array($order->status, ['Dispatched', 'Partially Dispatched', 'Received', 'Delivered'])) {
            return redirect()->back()->with('error', 'Invoice can only be uploaded after the order status is Dispatched.');
        }

        $request->validate([
            'received_invoice_number' => 'required|string|max:255',
            'received_invoice_date'   => 'required|date',
            'received_invoice_file'   => $order->received_invoice_file ? 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120' : 'required|file|mimes:jpg,jpeg,png,pdf|max:5120',
        ]);

        $updateData = [
            'received_invoice_number' => $request->input('received_invoice_number'),
            'received_invoice_date'   => $request->input('received_invoice_date'),
        ];

        if ($request->hasFile('received_invoice_file')) {
            $invoiceFileUrl = $this->uploadFileToService($request->file('received_invoice_file'), 'received_invoices');
            $updateData['received_invoice_file'] = $invoiceFileUrl;
        }

        $order->update($updateData);

        // Sync with invoices table
        $invoice = Invoice::where('adc_order_id', $order->id)->first();
        if ($invoice) {
            $invoiceSyncData = [
                'adc_invoice_number' => $request->input('received_invoice_number'),
                'adc_invoice_date'   => $request->input('received_invoice_date'),
            ];
            if (isset($updateData['received_invoice_file'])) {
                $invoiceSyncData['adc_invoice_file'] = $updateData['received_invoice_file'];
            }
            $invoice->update($invoiceSyncData);
        }

        return redirect()->back()->with('success', 'Received Transport Invoice uploaded successfully!');
    }

    public function printInvoice($id)
    {
        $adc = $this->adc();
        $order = AdcOrder::with([
            'items.product', 
            'items.seller.sellerProfile', 
            'items.purchaseOrder.invoice',
            'items.purchaseOrder.pickupStore.districtRelation',
            'items.purchaseOrder.pickupStore.stateRelation',
            'items.purchaseOrder.seller.sellerProfile',
            'purchaseOrders.seller.sellerProfile',
            'purchaseOrders.pickupStore.districtRelation',
            'purchaseOrders.pickupStore.stateRelation',
            'virtualDdc.profile', 
            'virtualDdc.ddcProfile', 
            'selectedDdc.profile', 
            'selectedDdc.ddcProfile', 
            'assignedDdc.profile', 
            'assignedDdc.ddcProfile', 
            'adc.profile', 
            'adc.adcProfile'
        ])
            ->where('adc_id', $adc->id)
            ->findOrFail($id);

        $invoice = Invoice::where('adc_order_id', $order->id)->whereNotNull('invoice_number')->latest()->first();

        if (!$invoice) {
            // Check if purchase order has invoice
            $poId = $order->items->pluck('purchase_order_id')->filter()->first();
            if ($poId) {
                $invoice = Invoice::where('purchase_order_id', $poId)->whereNotNull('invoice_number')->latest()->first();
            }
        }

        $extractedDetails = Invoice::extractAdcInvoiceDetails($order);

        if (!$invoice && in_array($order->status, ['Pending', 'Processing', 'Dispatched', 'Partially Dispatched', 'Received', 'Delivered', 'Completed'])) {
            $supplierGstin = $extractedDetails['supplier_gstin'] ?? 'N/A';
            $invoiceDetails = Invoice::generateNextInvoiceNumber(now(), $supplierGstin, 'DL');
            $invoice = Invoice::create(array_merge([
                'adc_order_id' => $order->id,
                'invoice_number' => $invoiceDetails['invoice_number'],
                'financial_year' => $invoiceDetails['financial_year'],
                'serial_number' => $invoiceDetails['serial_number'],
                'invoice_date' => $invoiceDetails['invoice_date'],
            ], $extractedDetails));
        }

        if (!$invoice || !$invoice->invoice_number) {
            return redirect()->back()->with('error', 'Tax Invoice has not been generated for this order yet.');
        }

        // Always ensure dispatch_from details on the invoice match the correct current pickup store / seller
        if (empty($invoice->dispatch_from_name) 
            || $invoice->dispatch_from_name === 'ADPL (Virtual DDC Hub)' 
            || empty($invoice->dispatch_from_state) 
            || empty($invoice->dispatch_from_address)
            || (!empty($extractedDetails['dispatch_from_name']) && $invoice->dispatch_from_name !== $extractedDetails['dispatch_from_name'])
            || (!empty($extractedDetails['dispatch_from_address']) && $invoice->dispatch_from_address !== $extractedDetails['dispatch_from_address'])
            || (!empty($extractedDetails['dispatch_from_pincode']) && $invoice->dispatch_from_pincode !== $extractedDetails['dispatch_from_pincode'])
            || (!empty($extractedDetails['dispatch_from_gstin']) && $invoice->dispatch_from_gstin !== $extractedDetails['dispatch_from_gstin'])) {
            $invoice->update([
                'dispatch_from_name'     => $extractedDetails['dispatch_from_name'] ?? $invoice->dispatch_from_name,
                'dispatch_from_address'  => $extractedDetails['dispatch_from_address'] ?? $invoice->dispatch_from_address,
                'dispatch_from_district' => $extractedDetails['dispatch_from_district'] ?? $invoice->dispatch_from_district,
                'dispatch_from_state'    => $extractedDetails['dispatch_from_state'] ?? $invoice->dispatch_from_state,
                'dispatch_from_pincode'  => $extractedDetails['dispatch_from_pincode'] ?? $invoice->dispatch_from_pincode,
                'dispatch_from_phone'    => $extractedDetails['dispatch_from_phone'] ?? $invoice->dispatch_from_phone,
                'dispatch_from_gstin'    => $extractedDetails['dispatch_from_gstin'] ?? $invoice->dispatch_from_gstin,
            ]);
            $invoice->refresh();
        }

        $ddc = $order->virtualDdc ?? $order->selectedDdc ?? $order->assignedDdc;
        if (!$ddc) {
            $firstDdcId = $order->items()->pluck('virtual_ddc_id')->filter()->first() ?? $order->items()->pluck('ddc_id')->filter()->first();
            if ($firstDdcId) {
                $ddc = \App\Models\User::with(['profile', 'ddcProfile'])->find($firstDdcId);
            }
        }

        $dispatchFromState = $invoice->dispatch_from_state ?: ($extractedDetails['dispatch_from_state'] ?? null);
        if (!$dispatchFromState) {
            $ddcProfile = $ddc?->ddcProfile ?? $ddc?->profile ?? null;
            $dispatchFromState = $ddcProfile?->state ?? ($ddc?->state ?? 'Delhi');
        }
        $billToState = $invoice->bill_to_state ?: ($extractedDetails['bill_to_state'] ?? ($order->delivery_state ?? null));

        $isIntraState = false;
        if ($dispatchFromState && $billToState) {
            $isIntraState = (strtolower(trim($dispatchFromState)) === strtolower(trim($billToState)));
        }

        $processedItems = [];
        $taxSummary = [];
        $totalQty = 0;
        $totalTaxableAmount = 0.0;
        $totalTaxAmount = 0.0;
        $grandTotal = 0.0;

        foreach ($order->items as $item) {
            if (in_array($item->status, ['Cancelled', 'Rejected'])) {
                continue;
            }
            if ($item->purchaseOrder && in_array($item->purchaseOrder->status, ['Cancelled', 'Rejected'])) {
                continue;
            }
            if ($item->purchaseOrder) {
                $poItem = $item->purchaseOrder->items->firstWhere('product_id', $item->product_id);
                if ($poItem && in_array($poItem->status, ['Cancelled', 'Rejected'])) {
                    continue;
                }
            }
            $product = $item->product;
            $qty = (int) $item->quantity;
            $totalQty += $qty;

            $gstRate = (float) ($item->gst_rate ?? $product?->gst_rate ?? 0);
            $totalPriceInclGst = (float) $item->total;
            $unitPriceInclGst = (float) $item->price;

            $rawTaxableValue = $gstRate > 0 ? ($totalPriceInclGst / (1.0 + ($gstRate / 100.0))) : $totalPriceInclGst;
            $rawRatePerItemTaxable = $gstRate > 0 ? ($unitPriceInclGst / (1.0 + ($gstRate / 100.0))) : $unitPriceInclGst;

            if ($isIntraState) {
                $cgstRate = $gstRate / 2.0;
                $sgstRate = $gstRate / 2.0;
                $cgstAmt = round($rawTaxableValue * ($cgstRate / 100.0), 2);
                $sgstAmt = round($rawTaxableValue * ($sgstRate / 100.0), 2);
                $igstRate = 0.0;
                $igstAmt = 0.0;
                $taxAmount = $cgstAmt + $sgstAmt;
            } else {
                $cgstRate = 0.0;
                $sgstRate = 0.0;
                $cgstAmt = 0.0;
                $sgstAmt = 0.0;
                $igstRate = $gstRate;
                $igstAmt = round($rawTaxableValue * ($igstRate / 100.0), 2);
                $taxAmount = $igstAmt;
            }

            $taxableValue = round($rawTaxableValue, 2);
            $ratePerItemTaxable = round($rawRatePerItemTaxable, 2);

            $processedItems[] = [
                'name' => $item->product_name ?? $product?->product_name ?? 'Product',
                'barcode' => $item->product_code ?? $product?->barcode ?? 'N/A',
                'hsn_code' => $item->hsn_code ?? $product?->hsn_code ?? 'N/A',
                'qty' => $qty,
                'rate_per_item' => $ratePerItemTaxable,
                'unit_price_excl_gst' => $ratePerItemTaxable,
                'taxable_value' => $taxableValue,
                'gst_rate' => $gstRate,
                'cgst_rate' => $cgstRate,
                'cgst_amount' => $cgstAmt,
                'sgst_rate' => $sgstRate,
                'sgst_amount' => $sgstAmt,
                'igst_rate' => $igstRate,
                'igst_amount' => $igstAmt,
                'tax_value' => $taxAmount,
                'total_amount' => $totalPriceInclGst,
                'amount' => $totalPriceInclGst,
            ];

            $totalTaxableAmount += $taxableValue;
            $totalTaxAmount += $taxAmount;
            $grandTotal += $totalPriceInclGst;

            $taxKey = number_format($gstRate, 1);

            if (!isset($taxSummary[$taxKey])) {
                $taxSummary[$taxKey] = [
                    'rate' => $gstRate,
                    'cgst_rate' => $cgstRate,
                    'sgst_rate' => $sgstRate,
                    'igst_rate' => $igstRate,
                    'qty' => 0,
                    'taxable_value' => 0.0,
                    'cgst_amount' => 0.0,
                    'sgst_amount' => 0.0,
                    'igst_amount' => 0.0,
                    'tax_value' => 0.0,
                    'amount' => 0.0
                ];
            }
            $taxSummary[$taxKey]['qty'] += $qty;
            $taxSummary[$taxKey]['taxable_value'] += $taxableValue;
            $taxSummary[$taxKey]['cgst_amount'] += $cgstAmt;
            $taxSummary[$taxKey]['sgst_amount'] += $sgstAmt;
            $taxSummary[$taxKey]['igst_amount'] += $igstAmt;
            $taxSummary[$taxKey]['tax_value'] += $taxAmount;
            $taxSummary[$taxKey]['amount'] += $totalPriceInclGst;
        }

        ksort($taxSummary);

        $roundedGrandTotal = round($grandTotal);
        $roundOff = round($roundedGrandTotal - $grandTotal, 2);
        $amountInWords = $this->numberToWords($roundedGrandTotal) . ' Rupees Only';

        return view('invoices.adc-order-print', compact(
            'order', 'invoice', 'ddc', 'processedItems', 'taxSummary', 'isIntraState',
            'totalQty', 'totalTaxableAmount', 'totalTaxAmount', 'grandTotal',
            'roundedGrandTotal', 'roundOff', 'amountInWords'
        ));
    }

    protected function numberToWords($number): string
    {
        $no = (int) round($number);
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
        $digits_length = strlen($no);
        
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
        
        return trim(implode('', array_reverse(array_filter($str))));
    }

    public function stockInItem(Request $request, $itemId)
    {
        $orderItem = AdcOrderItem::with(['product', 'order'])->findOrFail($itemId);
        $adc = $this->adc();

        if ($orderItem->order->adc_id !== $adc->id) {
            abort(403, 'Unauthorized.');
        }

        $order = $orderItem->order;

        if (empty($order->received_invoice_number) || empty($order->received_invoice_file)) {
            $msg = 'Please upload the Received Transport/Supplier Invoice first before stocking in items.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        if (in_array($orderItem->status, ['Rejected', 'Cancelled'])) {
            $msg = 'This product was rejected by the seller and refunded to your wallet.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        if ($orderItem->status !== 'Dispatched' && !$orderItem->is_stocked_in) {
            $msg = 'Only dispatched items can be stocked in.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        if ($orderItem->is_stocked_in) {
            $msg = 'This item is already stocked in.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        $request->validate([
            'received_mrp' => 'required|numeric|min:0',
            'damage_qty'   => 'required|integer|min:0',
            'shortage_qty' => 'required|integer|min:0',
            'excess_qty'   => 'required|integer|min:0',
            'mfg_date'     => 'nullable|date',
            'expire_date'  => 'nullable|date|after_or_equal:mfg_date',
            'batch_number' => 'nullable|string|max:255',
        ]);

        $supplyQty   = (int) $orderItem->quantity;
        $damageQty   = (int) $request->input('damage_qty');
        $shortageQty = (int) $request->input('shortage_qty');
        $excessQty   = (int) $request->input('excess_qty');
        $receivedMrp = (float) $request->input('received_mrp');

        if (($damageQty + $shortageQty) > $supplyQty) {
            $msg = "Validation failed: Damage Qty ({$damageQty}) + Shortage Qty ({$shortageQty}) cannot be greater than Supply Qty ({$supplyQty}).";
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->withInput()->with('error', $msg);
        }

        $netQty = $supplyQty - $damageQty - $shortageQty + $excessQty;
        if ($netQty < 0) {
            $msg = 'Net stock-in quantity cannot be negative.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return redirect()->back()->with('error', $msg);
        }

        DB::beginTransaction();
        try {
            $purchasePrice = (float) $orderItem->price;
            // Formula for MSME product in ADC: CSP = ADC + (MRP - ADC) / 2 where ADC = purchasePrice
            $cspPrice = $receivedMrp >= $purchasePrice
                ? round($purchasePrice + (($receivedMrp - $purchasePrice) / 2), 2)
                : $purchasePrice;
            $calculatedMargin = $receivedMrp > 0 ? round((($receivedMrp - $cspPrice) / $receivedMrp) * 100, 2) : 0;

            // Create AdcInventory record
            AdcInventory::create([
                'adc_id'            => $adc->id,
                'product_id'        => $orderItem->product_id,
                'product_name'      => $orderItem->product_name ?? $orderItem->product?->product_name,
                'product_code'      => $orderItem->product_code ?? $orderItem->product?->product_code,
                'barcode'           => $orderItem->product?->barcode,
                'unit_of_measure'   => $orderItem->unit_of_measure ?? $orderItem->product?->unit_of_measure,
                'quantity'          => $netQty,
                'reserved_quantity' => 0,
                'sold_quantity'     => 0,
                'returned_quantity' => 0,
                'mrp'               => $receivedMrp,
                'purchase_price'    => $purchasePrice,
                'landing_cost'      => $purchasePrice,
                'msp'               => $purchasePrice,
                'adc_price'         => $purchasePrice,
                'csp'               => $cspPrice,
                'gst_rate'          => $orderItem->gst_rate,
                'adc_margin'        => $calculatedMargin,
                'csp_margin'        => $calculatedMargin,
                'mfg_date'          => $request->input('mfg_date'),
                'expire_date'       => $request->input('expire_date'),
                'batch_number'      => $request->input('batch_number'),
                'adc_order_id'      => $orderItem->adc_order_id,
                'adc_order_item_id' => $orderItem->id,
            ]);

            // Update item details
            $orderItem->update([
                'is_stocked_in' => true,
                'stocked_in_at' => now(),
                'received_mrp'  => $receivedMrp,
                'supply_qty'    => $supplyQty,
                'damage_qty'    => $damageQty,
                'shortage_qty'  => $shortageQty,
                'excess_qty'    => $excessQty,
                'batch_number'  => $request->input('batch_number'),
                'mfg_date'      => $request->input('mfg_date'),
                'expire_date'   => $request->input('expire_date'),
                'status'        => 'Received',
            ]);

            // Release hold on delivery if applicable
            try {
                \App\Services\IncentiveHoldService::releaseHoldOnAdcDelivery($orderItem, $netQty);
            } catch (\Throwable $th) {
                // Ignore if no hold record
            }

            // Automatically mark Order as Received if all items in this order are stocked in
            $pendingStockIn = AdcOrderItem::where('adc_order_id', $order->id)
                ->where('is_stocked_in', false)
                ->whereNotIn('status', ['Cancelled', 'Rejected'])
                ->count();

            $allStockedIn = false;
            if ($pendingStockIn === 0) {
                if (!in_array($order->status, ['Received', 'Completed'])) {
                    $order->status = 'Received';
                    $order->save();

                    // Update linked PurchaseOrders to Received if all items stocked in
                    PurchaseOrder::where('adc_order_id', $order->id)
                        ->whereIn('status', ['Dispatched', 'Accepted', 'Processing', 'Partially Dispatched', 'Pending'])
                        ->update(['status' => 'Received']);

                    NotificationService::notifyAdmin(
                        'Order Received by ADC',
                        "ADC {$adc->name} completed stock-in for all items and marked Order #{$order->order_number} as Received.",
                        route('admin.adc-orders.show', $order->id)
                    );

                    // Process ADC Cashback if eligible
                    \App\Services\AdcCashbackService::processAdcCashback($order);
                }
                $allStockedIn = true;
            }

            DB::commit();

            $allItems = AdcOrderItem::where('adc_order_id', $order->id)->where('is_stocked_in', true)->get();
            $hasDiscrepancy = $allItems->contains(function($i) { return ((int)$i->shortage_qty > 0 || (int)$i->damage_qty > 0); });
            $hasExcess = $allItems->contains(function($i) { return ((int)$i->excess_qty > 0); });

            $targetLabel = 'Supplier';
            if ($order->fulfillment_type === 'physical' || !empty($order->selected_ddc_id) || !empty($order->assigned_ddc_id)) {
                $targetLabel = 'DDC';
            } else {
                $targetLabel = 'Seller';
            }

            $existingDebitNote = \App\Models\AdcDebitNote::where('adc_order_id', $order->id)->where('discrepancy_type', '!=', 'EXCESS')->first();
            $existingExcessNote = \App\Models\AdcExcessInvoice::where('adc_order_id', $order->id)->first();

            if ($request->ajax() || $request->wantsJson()) {
                $formattedMfgDate = $orderItem->mfg_date ? (is_string($orderItem->mfg_date) ? \Carbon\Carbon::parse($orderItem->mfg_date)->format('d M Y') : $orderItem->mfg_date->format('d M Y')) : '—';
                $formattedExpDate = $orderItem->expire_date ? (is_string($orderItem->expire_date) ? \Carbon\Carbon::parse($orderItem->expire_date)->format('d M Y') : $orderItem->expire_date->format('d M Y')) : '—';

                return response()->json([
                    'success'               => true,
                    'message'               => $allStockedIn
                        ? 'Item stocked in successfully. All items stocked in! Order #' . $order->order_number . ' is marked as Received.'
                        : 'Item stocked in successfully and added to your Inventory.',
                    'item_id'               => $orderItem->id,
                    'received_mrp'          => number_format($receivedMrp, 2),
                    'csp'                   => number_format($cspPrice, 2),
                    'margin'                => number_format($calculatedMargin, 2),
                    'damage_qty'            => $damageQty,
                    'shortage_qty'          => $shortageQty,
                    'excess_qty'            => $excessQty,
                    'batch_number'          => $request->input('batch_number') ?: '—',
                    'mfg_date'              => $formattedMfgDate,
                    'expire_date'           => $formattedExpDate,
                    'all_stocked_in'        => $allStockedIn,
                    'order_status'          => $order->status,
                    'order_number'          => $order->order_number,
                    'has_discrepancy'       => $hasDiscrepancy,
                    'has_excess'            => $hasExcess,
                    'target_label'          => $targetLabel,
                    'debit_note_url'        => $existingDebitNote ? route('adc.debit-notes.show', $existingDebitNote->id) : route('adc.debit-notes.create', $order->id),
                    'debit_note_label'      => $existingDebitNote ? "View Debit Note #{$existingDebitNote->debit_note_number}" : "Generate Consolidated Debit Note",
                    'debit_note_exists'     => (bool)$existingDebitNote,
                    'excess_invoice_url'    => $existingExcessNote ? route('adc.excess-invoices.show', $existingExcessNote->id) : route('adc.excess-invoices.create', $order->id),
                    'excess_invoice_label' => $existingExcessNote ? "View Excess Invoice #{$existingExcessNote->invoice_number}" : "Generate Excess Stock Invoice",
                    'excess_invoice_exists' => (bool)$existingExcessNote,
                ]);
            }

            if ($allStockedIn) {
                return redirect()->back()->with('success', 'Item stocked in successfully. All items stocked in! Order #' . $order->order_number . ' is marked as Received.');
            }

            return redirect()->back()->with('success', 'Item stocked in successfully and added to your Inventory.');
        } catch (\Exception $e) {
            DB::rollBack();
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => 'Failed to stock in item: ' . $e->getMessage()], 422);
            }
            return redirect()->back()->with('error', 'Failed to stock in item: ' . $e->getMessage());
        }
    }

    public function markReceived($id)
    {
        $adc = $this->adc();
        $order = AdcOrder::where('adc_id', $adc->id)->findOrFail($id);

        // Verify all items in this order are stocked in
        $pendingStockIn = AdcOrderItem::where('adc_order_id', $order->id)
            ->where('is_stocked_in', false)
            ->whereNotIn('status', ['Cancelled', 'Rejected'])
            ->count();

        if ($pendingStockIn > 0) {
            return redirect()->back()->with('error', 'Cannot complete order. Some dispatched items have not been stocked in yet.');
        }

        DB::beginTransaction();
        try {
            $order->status = 'Received';
            $order->save();

            // Update linked PurchaseOrders to Received if all items stocked in
            PurchaseOrder::where('adc_order_id', $order->id)
                ->whereIn('status', ['Dispatched', 'Accepted', 'Processing'])
                ->update(['status' => 'Received']);

            NotificationService::notifyAdmin(
                'Order Received by ADC',
                "ADC {$adc->name} completed stock-in and marked Order #{$order->order_number} as Received.",
                route('admin.adc-orders.show', $order->id)
            );

            // Process ADC Cashback if eligible
            \App\Services\AdcCashbackService::processAdcCashback($order);

            DB::commit();
            return redirect()->route('adc.orders.index')->with('success', 'Order completed and marked as Received successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to mark order as received: ' . $e->getMessage());
        }
    }
}
