<?php

namespace App\Http\Controllers\DDC;

use App\Http\Controllers\Controller;
use App\Models\AdcOrder;
use App\Models\AdcOrderItem;
use App\Models\DdcInventory;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdcOrderController extends Controller
{
    private function ddc()
    {
        return Auth::guard('ddc')->user();
    }

    /**
     * Get IDs of all ADCs belonging to this DDC's area (assigned, pincode-mapped, or district-mapped)
     */
    private function getAreaAdcUserIds($ddcId)
    {
        $explicitAdcUserIds = DB::table('adc_profiles')
            ->where('assigned_ddc_id', $ddcId)
            ->pluck('user_id')
            ->toArray();

        $pincodeIds = DB::table('ddc_pincodes')->where('ddc_id', $ddcId)->pluck('pincode_id')->toArray();
        $mappedPincodes = DB::table('pincodes')->whereIn('id', $pincodeIds)->pluck('pincode')->toArray();

        $ddcProfile = DB::table('ddc_profiles')->where('user_id', $ddcId)->first();
        if ($ddcProfile) {
            if (!empty($ddcProfile->pincode)) $mappedPincodes[] = $ddcProfile->pincode;
            if (!empty($ddcProfile->ddc_pincode)) $mappedPincodes[] = $ddcProfile->ddc_pincode;
            if (!empty($ddcProfile->adpl_pincode)) $mappedPincodes[] = $ddcProfile->adpl_pincode;
        }
        $mappedPincodes = array_unique(array_filter($mappedPincodes));
        $districtId = $ddcProfile?->district_id;

        if (empty($explicitAdcUserIds) && empty($mappedPincodes) && empty($districtId)) {
            return [];
        }

        return \App\Models\User::where('role', 'adc')
            ->where(function ($q) use ($explicitAdcUserIds, $mappedPincodes, $districtId) {
                $hasCond = false;
                if (!empty($explicitAdcUserIds)) {
                    $q->whereIn('id', $explicitAdcUserIds);
                    $hasCond = true;
                }
                if (!empty($mappedPincodes)) {
                    if ($hasCond) {
                        $q->orWhereHas('adcProfile', function ($apq) use ($mappedPincodes) {
                            $apq->whereIn('pincode', $mappedPincodes);
                        });
                    } else {
                        $q->whereHas('adcProfile', function ($apq) use ($mappedPincodes) {
                            $apq->whereIn('pincode', $mappedPincodes);
                        });
                        $hasCond = true;
                    }
                }
                if ($districtId) {
                    if ($hasCond) {
                        $q->orWhereHas('adcProfile', function ($apq) use ($districtId) {
                            $apq->where('district_id', $districtId);
                        });
                    } else {
                        $q->whereHas('adcProfile', function ($apq) use ($districtId) {
                            $apq->where('district_id', $districtId);
                        });
                    }
                }
            })
            ->pluck('id')
            ->toArray();
    }

    public function index(Request $request)
    {
        $ddc = $this->ddc();
        $ddcId = $ddc->id;
        $areaAdcUserIds = $this->getAreaAdcUserIds($ddcId);

        $areaAdcList = \App\Models\User::where(function ($q) use ($ddcId, $areaAdcUserIds) {
            if (!empty($areaAdcUserIds)) {
                $q->whereIn('id', $areaAdcUserIds);
            }
            $q->orWhereHas('adcOrders', function ($oq) use ($ddcId) {
                $oq->where('selected_ddc_id', $ddcId)
                  ->orWhere('virtual_ddc_id', $ddcId)
                  ->orWhere('assigned_ddc_id', $ddcId);
            });
        })
        ->with(['adcProfile'])
        ->orderBy('name', 'asc')
        ->get();

        // Orders List Query
        $ordersQuery = AdcOrder::with(['adc.adcProfile', 'items.product', 'selectedDdc', 'virtualDdc', 'assignedDdc'])
            ->forDdcVisibility()
            ->where(function ($q) use ($ddcId, $areaAdcUserIds) {
                $q->where('selected_ddc_id', $ddcId)
                  ->orWhere('virtual_ddc_id', $ddcId)
                  ->orWhere('assigned_ddc_id', $ddcId)
                  ->orWhereHas('items', function ($iq) use ($ddcId) {
                      $iq->where('ddc_id', $ddcId)
                        ->orWhere('virtual_ddc_id', $ddcId);
                  });
                if (!empty($areaAdcUserIds)) {
                    $q->orWhereIn('adc_id', $areaAdcUserIds);
                }
            });

        if ($request->filled('status')) {
            $ordersQuery->where('status', $request->status);
        }

        if ($request->filled('adc_id')) {
            $ordersQuery->where('adc_id', $request->adc_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $ordersQuery->where(function ($q) use ($search) {
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

        $orders = (clone $ordersQuery)->orderBy('created_at', 'desc')->paginate(15, ['*'], 'orders_page')->withQueryString();

        // Summary Aggregation Query for Product Summaries
        $summaryQuery = DB::table('adc_order_items')
            ->join('adc_orders', 'adc_order_items.adc_order_id', '=', 'adc_orders.id')
            ->leftJoin('users as adc_users', 'adc_orders.adc_id', '=', 'adc_users.id')
            ->leftJoin('adc_profiles', 'adc_users.id', '=', 'adc_profiles.user_id')
            ->leftJoin('products', 'adc_order_items.product_id', '=', 'products.id')
            ->where(function ($q) {
                $q->where('adc_orders.status', '!=', 'Processing')
                  ->orWhere(function ($sq) {
                      $sq->whereNull('adc_profiles.show_order')
                         ->orWhere('adc_profiles.show_order', '!=', 'No');
                  });
            })
            ->where(function ($q) use ($ddcId, $areaAdcUserIds) {
                $q->where('adc_orders.selected_ddc_id', $ddcId)
                  ->orWhere('adc_orders.virtual_ddc_id', $ddcId)
                  ->orWhere('adc_orders.assigned_ddc_id', $ddcId)
                  ->orWhere('adc_order_items.ddc_id', $ddcId)
                  ->orWhere('adc_order_items.virtual_ddc_id', $ddcId);
                if (!empty($areaAdcUserIds)) {
                    $q->orWhereIn('adc_orders.adc_id', $areaAdcUserIds);
                }
            });

        if ($request->filled('status')) {
            $summaryQuery->where('adc_orders.status', $request->status);
        } else {
            $summaryQuery->where('adc_orders.status', '!=', 'Cancelled');
        }

        if ($request->filled('adc_id')) {
            $summaryQuery->where('adc_orders.adc_id', $request->adc_id);
        }

        if ($request->filled('search')) {
            $search = trim($request->search);
            $summaryQuery->where(function ($q) use ($search) {
                $q->where('adc_order_items.product_name', 'like', "%{$search}%")
                  ->orWhere('adc_order_items.category_name', 'like', "%{$search}%")
                  ->orWhere('adc_order_items.brand_name', 'like', "%{$search}%")
                  ->orWhere('adc_users.name', 'like', "%{$search}%")
                  ->orWhere('adc_profiles.company_name', 'like', "%{$search}%");
            });
        }

        $productSummaries = $summaryQuery->select(
                'adc_orders.adc_id',
                'adc_users.name as adc_name',
                'adc_users.mobile as adc_mobile',
                'adc_profiles.company_name as adc_company_name',
                'adc_order_items.product_id',
                'adc_order_items.product_name',
                'adc_order_items.product_code',
                'adc_order_items.category_name',
                'adc_order_items.brand_name',
                'adc_order_items.unit_of_measure',
                'products.featured_image',
                DB::raw('SUM(adc_order_items.quantity) as total_qty'),
                DB::raw('SUM(adc_order_items.total) as total_amount'),
                DB::raw('COUNT(DISTINCT adc_orders.id) as total_orders_count'),
                DB::raw('MAX(adc_orders.created_at) as last_ordered_at')
            )
            ->groupBy(
                'adc_orders.adc_id',
                'adc_users.name',
                'adc_users.mobile',
                'adc_profiles.company_name',
                'adc_order_items.product_id',
                'adc_order_items.product_name',
                'adc_order_items.product_code',
                'adc_order_items.category_name',
                'adc_order_items.brand_name',
                'adc_order_items.unit_of_measure',
                'products.featured_image'
            )
            ->orderBy('total_qty', 'desc')
            ->paginate(20, ['*'], 'products_page')
            ->withQueryString();

        // Statistics
        $allBaseQuery = (clone $ordersQuery);
        $totalOrdersCount = $allBaseQuery->count();
        $totalOrdersAmount = $allBaseQuery->sum('total_amount');
        $pendingOrdersCount = (clone $ordersQuery)->where('status', 'Pending')->count();
        $dispatchedOrdersCount = (clone $ordersQuery)->whereIn('status', ['Dispatched', 'Partially Dispatched'])->count();

        return view('ddc.adc-orders.index', compact(
            'orders',
            'productSummaries',
            'areaAdcList',
            'totalOrdersCount',
            'totalOrdersAmount',
            'pendingOrdersCount',
            'dispatchedOrdersCount'
        ));
    }

    public function show($id)
    {
        $ddc = $this->ddc();
        $ddcId = $ddc->id;
        $areaAdcUserIds = $this->getAreaAdcUserIds($ddcId);

        $order = AdcOrder::where(function ($q) use ($ddcId, $areaAdcUserIds) {
            $q->where('selected_ddc_id', $ddcId)
              ->orWhere('virtual_ddc_id', $ddcId)
              ->orWhere('assigned_ddc_id', $ddcId)
              ->orWhereHas('items', function ($iq) use ($ddcId) {
                  $iq->where('ddc_id', $ddcId)
                    ->orWhere('virtual_ddc_id', $ddcId);
              });
            if (!empty($areaAdcUserIds)) {
                $q->orWhereIn('adc_id', $areaAdcUserIds);
            }
        })->with(['adc', 'adc.adcProfile', 'items.product', 'items.seller', 'items.ddc'])->findOrFail($id);

        $invoice = \App\Models\Invoice::where('adc_order_id', $order->id)->latest()->first();

        return view('ddc.adc-orders.show', compact('order', 'invoice'));
    }

    public function printInvoice($id)
    {
        $ddc = $this->ddc();
        $order = AdcOrder::where(function ($q) use ($ddc) {
            $q->where('selected_ddc_id', $ddc->id)
              ->orWhere('virtual_ddc_id', $ddc->id)
              ->orWhere('assigned_ddc_id', $ddc->id)
              ->orWhereHas('items', function ($iq) use ($ddc) {
                  $iq->where('ddc_id', $ddc->id)
                    ->orWhere('virtual_ddc_id', $ddc->id);
              });
        })->with(['adc', 'items' => function ($q) use ($ddc) {
            $q->where(function ($iq) use ($ddc) {
                $iq->where('ddc_id', $ddc->id)
                  ->orWhere('virtual_ddc_id', $ddc->id);
            })->with(['product', 'seller']);
        }])->findOrFail($id);

        $invoice = \App\Models\Invoice::where('adc_order_id', $order->id)->latest()->first();

        $extractedDetails = \App\Models\Invoice::extractAdcInvoiceDetails($order);

        if (!$invoice && in_array($order->status, ['Pending', 'Processing'])) {
            return redirect()->back()->with('error', 'Tax Invoice will be available once the order has been dispatched.');
        }

        if (!$invoice && in_array($order->status, ['Dispatched', 'Partially Dispatched', 'Received', 'Delivered', 'Completed'])) {
            $ddcProfile = $ddc->profile ?? $ddc->ddcProfile ?? null;
            $ddcGstin = $extractedDetails['supplier_gstin'] ?? ($ddcProfile?->ddc_gst_number ?: ($ddcProfile?->gst_number ?: 'N/A'));
            $invoiceDetails = \App\Models\Invoice::generateNextInvoiceNumber(now(), $ddcGstin, 'INV', 'wholesale');

            $invoice = \App\Models\Invoice::create(array_merge([
                'adc_order_id'       => $order->id,
                'invoice_number'     => $invoiceDetails['invoice_number'],
                'financial_year'     => $invoiceDetails['financial_year'],
                'serial_number'      => $invoiceDetails['serial_number'],
                'invoice_date'       => $invoiceDetails['invoice_date'],
                'ddc_invoice_number' => $invoiceDetails['invoice_number'],
                'ddc_invoice_date'   => now(),
                'type'               => 'ddc_to_adc',
            ], $extractedDetails));
        }

        if (!$invoice) {
            return redirect()->back()->with('error', 'Tax Invoice has not been generated for this order yet.');
        }

        // Determine intra-state vs inter-state

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

            $taxableValue = round($totalPriceInclGst / (1 + ($gstRate / 100)), 2);
            $taxAmount = round($totalPriceInclGst - $taxableValue, 2);
            $ratePerItemTaxable = round($unitPriceInclGst / (1 + ($gstRate / 100)), 2);

            $totalTaxableAmount += $taxableValue;
            $totalTaxAmount += $taxAmount;
            $grandTotal += $totalPriceInclGst;

            $processedItems[] = [
                'name' => $item->product_name ?? $product?->product_name ?? 'Product',
                'barcode' => $item->product_code ?? $product?->barcode ?? 'N/A',
                'hsn_code' => $item->hsn_code ?? $product?->hsn_code ?? 'N/A',
                'qty' => $qty,
                'rate_per_item' => $ratePerItemTaxable,
                'taxable_value' => $taxableValue,
                'gst_rate' => $gstRate,
                'tax_value' => $taxAmount,
                'amount' => $totalPriceInclGst
            ];

            $taxKey = number_format($gstRate, 1);

            $cgstAmt = $isIntraState ? ($taxAmount / 2.0) : 0.0;
            $sgstAmt = $isIntraState ? ($taxAmount / 2.0) : 0.0;
            $igstAmt = !$isIntraState ? $taxAmount : 0.0;

            if (!isset($taxSummary[$taxKey])) {
                $taxSummary[$taxKey] = [
                    'rate' => $gstRate,
                    'cgst_rate' => $isIntraState ? ($gstRate / 2.0) : 0.0,
                    'sgst_rate' => $isIntraState ? ($gstRate / 2.0) : 0.0,
                    'igst_rate' => !$isIntraState ? $gstRate : 0.0,
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
            'order',
            'invoice',
            'ddc',
            'processedItems',
            'taxSummary',
            'totalQty',
            'totalTaxableAmount',
            'totalTaxAmount',
            'grandTotal',
            'roundedGrandTotal',
            'roundOff',
            'amountInWords',
            'isIntraState'
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

    public function dispatch(Request $request, $id)
    {
        $ddc = $this->ddc();
        $order = AdcOrder::where(function ($q) use ($ddc) {
            $q->where('selected_ddc_id', $ddc->id)
              ->orWhere('virtual_ddc_id', $ddc->id)
              ->orWhere('assigned_ddc_id', $ddc->id)
              ->orWhereHas('items', function ($iq) use ($ddc) {
                  $iq->where('ddc_id', $ddc->id)
                    ->orWhere('virtual_ddc_id', $ddc->id);
              });
        })->findOrFail($id);

        $request->validate([
            'items' => 'required|array',
            'items.*.item_id' => 'required|exists:adc_order_items,id',
            'items.*.dispatch_qty' => 'required|numeric|min:0.001',
        ]);

        DB::beginTransaction();
        try {
            foreach ($request->items as $itemData) {
                $orderItem = AdcOrderItem::where('adc_order_id', $order->id)
                    ->where('id', $itemData['item_id'])
                    ->where(function ($iq) use ($ddc) {
                        $iq->where('ddc_id', $ddc->id)
                          ->orWhere('virtual_ddc_id', $ddc->id)
                          ->orWhereNull('ddc_id');
                    })
                    ->first();

                if (!$orderItem) {
                    continue;
                }

                $dispatchedQty = (float) $itemData['dispatch_qty'];
                $physicalInv = DdcInventory::where('ddc_id', $ddc->id)
                    ->where('product_id', $orderItem->product_id)
                    ->where(function($q) {
                        $q->where('quantity', '>', 0)
                          ->orWhere('reserved_quantity', '>', 0);
                    })
                    ->orderBy('created_at', 'asc')
                    ->first();

                if (!$physicalInv) {
                    $physicalInv = DdcInventory::where('ddc_id', $ddc->id)
                        ->where('product_id', $orderItem->product_id)
                        ->orderBy('created_at', 'desc')
                        ->first();
                }

                $itemUpdateData = ['status' => 'Dispatched'];

                if ($physicalInv) {
                    // Release reserved stock, reduce physical stock, increase sold
                    $physicalInv->confirmDispatch($dispatchedQty);

                    $itemUpdateData['batch_number'] = $physicalInv->batch_number ?: $orderItem->batch_number;
                    $itemUpdateData['mfg_date']     = $physicalInv->mfg_date ?: $orderItem->mfg_date;
                    $itemUpdateData['expire_date']  = $physicalInv->expire_date ?: $orderItem->expire_date;
                    $itemUpdateData['received_mrp'] = $physicalInv->mrp ?: ($orderItem->received_mrp ?: $orderItem->mrp);
                }

                $orderItem->update($itemUpdateData);
            }

            // If all items dispatched, update order status
            $allDispatched = !AdcOrderItem::where('adc_order_id', $order->id)
                ->where('status', '!=', 'Dispatched')
                ->where('status', '!=', 'Cancelled')
                ->exists();

            if ($allDispatched) {
                $order->update(['status' => 'Dispatched']);
            } else {
                $order->update(['status' => 'Processing']);
            }

            // Check or Create Invoice record for physical DDC dispatch
            $invoice = \App\Models\Invoice::where('adc_order_id', $order->id)->first();
            if (!$invoice) {
                $ddcGstin = $ddc->profile?->ddc_gst_number ?: ($ddc->profile?->adpl_gst_number ?: ($ddc->profile?->gst_number ?: ($ddc->gst_number ?: 'N/A')));
                $invoiceDetails = \App\Models\Invoice::generateNextInvoiceNumber(now(), $ddcGstin, 'INV', 'wholesale');

                $invoice = \App\Models\Invoice::create([
                    'adc_order_id'          => $order->id,
                    'purchase_order_id'     => null,
                    'type'                  => 'ddc_to_adc',
                    'invoice_number'        => $invoiceDetails['invoice_number'],
                    'financial_year'        => $invoiceDetails['financial_year'],
                    'serial_number'         => $invoiceDetails['serial_number'],
                    'invoice_date'          => now()->format('Y-m-d'),
                    'supplier_gstin'        => $ddcGstin,
                    'ddc_invoice_number'    => $invoiceDetails['invoice_number'],
                    'ddc_invoice_date'      => now(),
                    'bill_to_name'          => $order->adc?->name ?? 'ADC',
                    'bill_to_address'       => $order->shipping_address,
                    'bill_to_district'      => $order->delivery_district,
                    'bill_to_state'         => $order->delivery_state,
                    'bill_to_pincode'       => $order->delivery_pincode,
                    'bill_to_phone'         => $order->adc?->mobile,
                    'bill_to_gstin'         => $order->adc?->profile?->gst_number ?: ($order->adc?->adcProfile?->gst_number ?: ($order->adc?->gst_number ?: 'N/A')),
                    'dispatch_from_name'    => $ddc->name,
                    'dispatch_from_address' => $ddc->profile?->address ?? 'DDC Hub',
                    'dispatch_from_district'=> $ddc->profile?->district,
                    'dispatch_from_state'   => $ddc->profile?->state,
                    'dispatch_from_pincode' => $ddc->profile?->pincode,
                    'dispatch_from_phone'   => $ddc->mobile,
                    'dispatch_from_gstin'   => $ddcGstin,
                ]);
            }
            $invoiceNo = $invoice->invoice_number;

            NotificationService::notifyAdc(
                $order->adc_id,
                'Order Dispatched by DDC #' . $order->order_number,
                'Items from your order #' . $order->order_number . ' have been dispatched by DDC. Invoice No: ' . $invoiceNo,
                route('adc.orders.show', $order->id)
            );

            DB::commit();
            return redirect()->back()->with('success', 'Dispatched quantity processed. Tax Invoice generated: ' . $invoiceNo);
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to process dispatch: ' . $e->getMessage());
        }
    }

    public function cancel(Request $request, $id)
    {
        $ddc = $this->ddc();
        $order = AdcOrder::where(function ($q) use ($ddc) {
            $q->where('selected_ddc_id', $ddc->id)
              ->orWhere('virtual_ddc_id', $ddc->id)
              ->orWhere('assigned_ddc_id', $ddc->id)
              ->orWhereHas('items', function ($iq) use ($ddc) {
                  $iq->where('ddc_id', $ddc->id)
                    ->orWhere('virtual_ddc_id', $ddc->id);
              });
        })->findOrFail($id);

        DB::beginTransaction();
        try {
            $refundAmount = 0;
            $physicalItems = AdcOrderItem::where('adc_order_id', $order->id)
                ->where(function ($iq) use ($ddc) {
                    $iq->where('ddc_id', $ddc->id)
                      ->orWhere('virtual_ddc_id', $ddc->id)
                      ->orWhereNull('ddc_id');
                })
                ->where('status', '!=', 'Dispatched')
                ->where('status', '!=', 'Cancelled')
                ->get();

            foreach ($physicalItems as $orderItem) {
                $physicalInv = DdcInventory::where('ddc_id', $ddc->id)
                    ->where('product_id', $orderItem->product_id)
                    ->first();

                if ($physicalInv) {
                    // Release reserved stock without touching physical stock
                    $physicalInv->releaseReservedStock($orderItem->quantity);
                }

                $orderItem->update(['status' => 'Cancelled']);
                $refundAmount += $orderItem->total;
            }

            // Refund ADC Wallet
            if ($refundAmount > 0) {
                $adc = $order->adc;
                $adcProfile = $adc?->adcProfile ?? $adc?->profile;
                if ($adcProfile) {
                    $adcProfile->wallet_balance += $refundAmount;
                    $adcProfile->save();

                    \App\Models\AdcWallet::create([
                        'adc_id'      => $adc->id,
                        'amount'      => $refundAmount,
                        'type'        => 'credit',
                        'description' => 'Refund for cancelled Physical DDC items in Order #' . $order->order_number,
                    ]);
                }
            }

            $order->update(['status' => 'Cancelled']);

            NotificationService::notifyAdc(
                $order->adc_id,
                'Order Cancelled #' . $order->order_number,
                'Physical DDC items for order #' . $order->order_number . ' have been cancelled and reserved stock released. ₹' . number_format($refundAmount, 2) . ' refunded to your wallet.',
                route('adc.orders.show', $order->id)
            );

            DB::commit();
            return redirect()->back()->with('success', 'Order cancelled. Reserved stock released and refund credited to ADC wallet.');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Failed to cancel order: ' . $e->getMessage());
        }
    }
}
