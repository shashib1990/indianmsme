<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdcOrder;
use Illuminate\Http\Request;

class AdcOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = AdcOrder::with(['adc.profile', 'selectedDdc.ddcProfile', 'virtualDdc.ddcProfile', 'assignedDdc.ddcProfile', 'items.product', 'purchaseOrders']);

        // Search Filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('shipping_address', 'like', "%{$search}%")
                    ->orWhere('delivery_district', 'like', "%{$search}%")
                    ->orWhere('delivery_pincode', 'like', "%{$search}%")
                    ->orWhereHas('adc', function ($sq) use ($search) {
                        $sq->where('name', 'like', "%{$search}%")
                            ->orWhere('mobile', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Status Filter
        if ($request->filled('status')) {
            $st = strtoupper($request->status);
            if (in_array($st, ['PENDING', 'PLACED', 'CREATED', 'CONFIRMED'])) {
                $query->whereIn('status', ['PENDING', 'PLACED', 'CREATED', 'CONFIRMED']);
            } elseif (in_array($st, ['DISPATCHED', 'SHIPPED', 'IN_TRANSIT'])) {
                $query->whereIn('status', ['DISPATCHED', 'SHIPPED', 'IN_TRANSIT']);
            } elseif (in_array($st, ['COMPLETED', 'DELIVERED', 'RECEIVED'])) {
                $query->whereIn('status', ['COMPLETED', 'DELIVERED', 'RECEIVED']);
            } else {
                $query->where('status', $request->status);
            }
        }

        // Fulfillment Type Filter
        if ($request->filled('fulfillment_type')) {
            $query->where('fulfillment_type', $request->fulfillment_type);
        }

        // Payment Status Filter
        if ($request->filled('payment_status')) {
            $query->where('payment_status', $request->payment_status);
        }

        $sort = $request->get('sort', 'created_at');
        $order = $request->get('order', 'desc');
        $validSortColumns = ['order_number', 'created_at', 'total_amount', 'status', 'payment_status', 'fulfillment_type'];
        if (!in_array($sort, $validSortColumns)) {
            $sort = 'created_at';
        }
        if (!in_array(strtolower($order), ['asc', 'desc'])) {
            $order = 'desc';
        }

        $orders = $query->orderBy($sort, $order)->paginate(20)->withQueryString();

        // Statistics
        $totalOrdersCount = AdcOrder::count();
        $totalOrdersAmount = AdcOrder::sum('total_amount');
        $physicalOrdersCount = AdcOrder::where('fulfillment_type', 'physical')->count();
        $virtualOrdersCount = AdcOrder::where('fulfillment_type', 'virtual')->count();

        return view('admin.adc-orders.index', compact(
            'orders',
            'sort',
            'order',
            'totalOrdersCount',
            'totalOrdersAmount',
            'physicalOrdersCount',
            'virtualOrdersCount'
        ));
    }

    public function show($id)
    {
        $order = AdcOrder::with([
            'adc',
            'selectedDdc',
            'virtualDdc',
            'assignedDdc',
            'items.product',
            'items.seller',
            'items.ddc',
            'items.purchaseOrder.invoice',
            'purchaseOrders.seller',
            'purchaseOrders.invoice'
        ])->findOrFail($id);

        // Calculate MBP 38% & 62% shares for this order
        $mbp38Total = $order->items->sum(function ($item) {
            return (float) $item->mbp_38_share * (int) $item->quantity;
        });

        $mbp62Total = $order->items->sum(function ($item) {
            return (float) $item->mbp_62_share * (int) $item->quantity;
        });

        $mbp90Total = $mbp38Total + $mbp62Total;

        // Fetch AMA Incentive Holds and Releases for this order
        $poIds = $order->purchaseOrders->pluck('id');
        $incentiveHolds = \App\Models\IncentiveHold::whereIn('purchase_order_id', $poIds)->get();
        $incentiveReleases = \App\Models\IncentiveRelease::where('adc_order_id', $order->id)->get();

        return view('admin.adc-orders.show', compact(
            'order',
            'mbp38Total',
            'mbp62Total',
            'mbp90Total',
            'incentiveHolds',
            'incentiveReleases'
        ));
    }

    public function printInvoice($id)
    {
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
        ])->findOrFail($id);

        $invoice = \App\Models\Invoice::where('adc_order_id', $order->id)->whereNotNull('invoice_number')->latest()->first();

        if (!$invoice) {
            $poId = $order->items->pluck('purchase_order_id')->filter()->first();
            if ($poId) {
                $invoice = \App\Models\Invoice::where('purchase_order_id', $poId)->whereNotNull('invoice_number')->latest()->first();
            }
        }

        $extractedDetails = \App\Models\Invoice::extractAdcInvoiceDetails($order);

        if (!$invoice && in_array($order->status, ['Pending', 'Processing'])) {
            return redirect()->back()->with('error', 'Tax Invoice will be available once the order has been dispatched.');
        }

        if (!$invoice && in_array($order->status, ['Dispatched', 'Partially Dispatched', 'Received', 'Delivered', 'Completed'])) {
            $isPhysicalDdcOrder = ($order->fulfillment_type === 'physical') || !empty($order->selected_ddc_id) || !empty($order->assigned_ddc_id);

            if ($isPhysicalDdcOrder) {
                // Category 2: Physical DDC order -> INV/26-27/XXXX series under DDC's GSTIN
                $ddc = $order->selectedDdc ?? $order->assignedDdc ?? $order->virtualDdc;
                $ddcGstin = $extractedDetails['supplier_gstin'] ?? ($ddc?->profile?->ddc_gst_number ?: ($ddc?->profile?->gst_number ?: 'N/A'));
                $invoiceDetails = \App\Models\Invoice::generateNextInvoiceNumber(now(), $ddcGstin, 'INV', 'wholesale');
            } else {
                // Category 1: Virtual Order routed to Seller -> DL/26-27/XXXX series under ADPL Central DL Sequence
                $adplGstin = \App\Models\Invoice::getAdplGstin();
                $invoiceDetails = \App\Models\Invoice::generateNextInvoiceNumber(now(), $adplGstin, 'DL');
            }

            $invoice = \App\Models\Invoice::create(array_merge([
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
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen',
            20 => 'Twenty', 30 => 'Thirty', 40 => 'Forty', 50 => 'Fifty',
            60 => 'Sixty', 70 => 'Seventy', 80 => 'Eighty', 90 => 'Ninety'
        ];
        $digits = ['', 'Hundred', 'Thousand', 'Lakh', 'Crore'];

        while ($i < count($digits) && $no > 0) {
            $divider = ($i == 1) ? 10 : 100;
            $number = floor($no % $divider);
            $no = floor($no / $divider);
            $i += ($divider == 10) ? 1 : 2;

            if ($number) {
                $plural = (($counter = count($str)) && $number > 9) ? 's' : '';
                $hundred = ($counter == 1 && $str[0]) ? ' and ' : '';
                $str[] = ($number < 21)
                    ? $words[$number] . ' ' . $digits[$counter] . $plural . ' ' . $hundred
                    : $words[floor($number / 10) * 10] . ' ' . $words[$number % 10] . ' ' . $digits[$counter] . $plural . ' ' . $hundred;
            } else {
                $str[] = null;
            }
        }

        $rupees = implode('', array_reverse(array_filter($str)));
        return trim($rupees) ?: 'Zero';
    }
}
