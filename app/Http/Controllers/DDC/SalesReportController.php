<?php

namespace App\Http\Controllers\DDC;

use App\Http\Controllers\Controller;
use App\Models\DdcPosOrder;
use App\Models\AdcOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class SalesReportController extends Controller
{
    public function index(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $startDate = $request->input('start_date', \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->input('end_date', \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d'));

        $request->merge([
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ]);

        $filters = $request->only(['start_date', 'end_date', 'source', 'sourcing', 'payment_mode', 'search']);
        $filters['sourcing'] = strtolower($request->input('sourcing', 'all'));
        if (!in_array($filters['sourcing'], ['all', 'msme', 'regional'])) {
            $filters['sourcing'] = 'all';
        }

        $combinedData = $this->getCombinedSalesRecords($ddcId, $request);
        $records = $combinedData['records'];

        // Apply Payment Mode filter if provided
        if (!empty($filters['payment_mode']) && $filters['payment_mode'] !== 'all') {
            $records = $records->filter(function($row) use ($filters) {
                return strtolower($row['payment_mode']) === strtolower($filters['payment_mode']);
            })->values();
        }

        // Pagination
        $page = Paginator::resolveCurrentPage() ?: 1;
        $perPage = 20;
        $paginatedRecords = new LengthAwarePaginator(
            $records->forPage($page, $perPage),
            $records->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        // Calculate Summary Stats
        $summaryStats = $this->getSummaryStats($ddcId, $records, $filters);

        return view('ddc.reports.sales', compact('paginatedRecords', 'summaryStats', 'filters'));
    }

    public function exportCsv(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $filters = $request->only(['start_date', 'end_date', 'source', 'sourcing', 'payment_mode', 'search']);
        $filters['sourcing'] = strtolower($request->input('sourcing', 'all'));
        if (!in_array($filters['sourcing'], ['all', 'msme', 'regional'])) {
            $filters['sourcing'] = 'all';
        }

        $combinedData = $this->getCombinedSalesRecords($ddcId, $request);
        $records = $combinedData['records'];

        if (!empty($request->payment_mode) && $request->payment_mode !== 'all') {
            $records = $records->filter(function($row) use ($request) {
                return strtolower($row['payment_mode']) === strtolower($request->payment_mode);
            })->values();
        }

        $summaryStats = $this->getSummaryStats($ddcId, $records, $filters);
        $sourcingLabel = strtoupper($filters['sourcing']);
        $filename = 'ddc_sales_report_' . strtolower($sourcingLabel) . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($records, $summaryStats, $sourcingLabel) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['', "SALES REPORT ({$sourcingLabel} SCOPE)"]);
            fputcsv($file, []);

            fputcsv($file, [
                '#',
                'Reference / Invoice No',
                'Order No',
                'HSN Code',
                'Date & Time',
                'Sales Source',
                'Sourcing Type',
                'Customer / Canteen Name',
                'Customer Mobile',
                'Total Items Qty',
                'Subtotal (excl. tax)',
                'Tax / GST Amount',
                'Total Amount (₹)',
                'Payment Mode',
                'Payment Status',
            ]);

            foreach ($records as $idx => $row) {
                fputcsv($file, [
                    $idx + 1,
                    $row['reference_no'],
                    $row['order_no'],
                    $row['hsn_code'],
                    $row['date'],
                    $row['source_type'],
                    strtoupper($row['sourcing_type']),
                    $row['customer_name'],
                    $row['customer_mobile'],
                    $row['total_items'],
                    number_format($row['subtotal'], 2, '.', ''),
                    number_format($row['tax_amount'], 2, '.', ''),
                    number_format($row['total_amount'], 2, '.', ''),
                    $row['payment_mode'],
                    $row['payment_status'],
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['SUMMARY METRICS BREAKDOWN (' . $sourcingLabel . ')']);
            fputcsv($file, ['Gross Sales Value (₹)', number_format($summaryStats['gross_sales_amount'], 2, '.', '')]);
            fputcsv($file, ['Sales Returns (Retail POS Refund) (₹)', '-' . number_format($summaryStats['sales_return_amount'], 2, '.', '')]);
            fputcsv($file, ['Net Sales Value (₹)', number_format($summaryStats['net_sales_amount'], 2, '.', '')]);
            fputcsv($file, ['Gross Quantity Sold', number_format($summaryStats['gross_qty_sold'], 3, '.', '')]);
            fputcsv($file, ['Retail POS Returned Quantity', number_format($summaryStats['returned_qty'], 3, '.', '')]);
            fputcsv($file, ['Net Quantity Sold', number_format($summaryStats['net_qty_sold'], 3, '.', '')]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function print(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $filters = $request->only(['start_date', 'end_date', 'source', 'sourcing', 'payment_mode', 'search']);
        $filters['sourcing'] = strtolower($request->input('sourcing', 'all'));
        if (!in_array($filters['sourcing'], ['all', 'msme', 'regional'])) {
            $filters['sourcing'] = 'all';
        }

        $combinedData = $this->getCombinedSalesRecords($ddcId, $request);
        $records = $combinedData['records'];

        if (!empty($request->payment_mode) && $request->payment_mode !== 'all') {
            $records = $records->filter(function($row) use ($request) {
                return strtolower($row['payment_mode']) === strtolower($request->payment_mode);
            })->values();
        }

        $summaryStats = $this->getSummaryStats($ddcId, $records, $filters);

        return view('ddc.reports.sales-print', compact('records', 'summaryStats', 'filters'));
    }

    private function getSummaryStats($ddcId, Collection $records, array $filters = [])
    {
        $sourcingScope = $filters['sourcing'] ?? 'all';

        $returnsQuery = \App\Models\DdcPosSalesReturn::where('ddc_id', $ddcId)
            ->where(function ($q) {
                $q->whereHas('order', function ($oq) {
                    $oq->where('mode', 'retail');
                })->orWhereNull('pos_order_id');
            })
            ->with(['items.inventory.product', 'items.inventory.localProduct', 'items.product', 'items.localProduct']);

        if (!empty($filters['start_date'])) {
            $returnsQuery->whereDate('created_at', '>=', $filters['start_date']);
        }
        if (!empty($filters['end_date'])) {
            $returnsQuery->whereDate('created_at', '<=', $filters['end_date']);
        }

        $returns = $returnsQuery->get();

        $totalSalesReturnAmount = 0.0;
        $returnedQty = 0.0;

        foreach ($returns as $ret) {
            foreach ($ret->items as $rItem) {
                $inv = $rItem->inventory;
                $isRegional = ($inv && (strtoupper($inv->inventory_source ?? '') === 'LOCAL' || !empty($inv->ddc_local_product_id)))
                    || !empty($rItem->localProduct)
                    || str_contains(strtolower($rItem->product_name ?? ''), 'local');

                if ($sourcingScope === 'msme' && $isRegional) continue;
                if ($sourcingScope === 'regional' && !$isRegional) continue;

                $totalSalesReturnAmount += (float) $rItem->total_amount;
                $returnedQty += (float) $rItem->returned_quantity;
            }
        }

        $grossSalesAmount = (float) $records->sum('total_amount');
        $netSalesAmount = $grossSalesAmount - $totalSalesReturnAmount;

        $grossQtySold = (float) $records->sum('total_items');
        $netQtySold = max(0, $grossQtySold - $returnedQty);

        return [
            'total_count'            => $records->count(),
            'gross_qty_sold'         => $grossQtySold,
            'returned_qty'           => $returnedQty,
            'net_qty_sold'           => $netQtySold,
            'total_items_qty'        => $netQtySold,
            'gross_sales_amount'     => $grossSalesAmount,
            'sales_return_amount'    => $totalSalesReturnAmount,
            'net_sales_amount'       => $netSalesAmount,
            'total_amount'           => $netSalesAmount,
            'total_retail_amount'    => $records->where('source_key', 'retail')->sum('total_amount'),
            'total_wholesale_amount' => $records->where('source_key', 'wholesale')->sum('total_amount'),
            'total_adc_amount'       => $records->where('source_key', 'adc')->sum('total_amount'),
        ];
    }

    private function getCombinedSalesRecords($ddcId, Request $request)
    {
        $startDate     = $request->input('start_date');
        $endDate       = $request->input('end_date');
        $source        = $request->input('source', 'all'); // all, retail, wholesale, adc
        $sourcingScope = strtolower($request->input('sourcing', 'all')); // all, msme, regional
        $search        = $request->input('search');

        $records = collect();

        // 1. POS Orders (Retail & Wholesale)
        if (in_array($source, ['all', 'retail', 'wholesale'])) {
            $posQuery = DdcPosOrder::where('ddc_id', $ddcId)
                ->with(['customer', 'adcUser', 'invoice', 'items.product', 'items.localProduct', 'items.inventory.product', 'items.inventory.localProduct']);

            if ($source === 'retail') {
                $posQuery->where('mode', 'retail');
            } elseif ($source === 'wholesale') {
                $posQuery->where('mode', 'wholesale');
            }

            if ($startDate) {
                $posQuery->whereDate('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $posQuery->whereDate('created_at', '<=', $endDate);
            }
            if ($search) {
                $posQuery->where(function($q) use ($search) {
                    $q->where('order_no', 'like', "%{$search}%")
                      ->orWhere('customer_name', 'like', "%{$search}%")
                      ->orWhere('customer_mobile', 'like', "%{$search}%")
                      ->orWhereHas('invoice', function($invQ) use ($search) {
                          $invQ->where('invoice_number', 'like', "%{$search}%");
                      })
                      ->orWhereHas('items', function($itemQ) use ($search) {
                          $itemQ->whereHas('product', function($pQ) use ($search) {
                              $pQ->where('hsn_code', 'like', "%{$search}%");
                          })->orWhereHas('localProduct', function($lpQ) use ($search) {
                              $lpQ->where('hsn_code', 'like', "%{$search}%");
                          });
                      });
                });
            }

            $posOrders = $posQuery->get();

            foreach ($posOrders as $order) {
                $isWholesale = strtolower($order->mode) === 'wholesale';
                $sourceLabel = $isWholesale ? 'Wholesale POS' : 'Retail POS';
                $sourceKey   = $isWholesale ? 'wholesale' : 'retail';

                $custName = $order->customer_name;
                if ($isWholesale && $order->adcUser) {
                    $custName = $order->adcUser->name . ($order->adcUser->company_name ? ' (' . $order->adcUser->company_name . ')' : '');
                } elseif (empty($custName)) {
                    $custName = 'Walk-in Customer';
                }

                // Analyze item sourcing types
                $msmeItemsCount = 0;
                $regionalItemsCount = 0;
                $calcTotalItems = 0.0;
                $calcSubtotal = 0.0;
                $calcTaxAmount = 0.0;
                $calcTotalAmount = 0.0;
                $hsnList = [];

                foreach ($order->items as $item) {
                    $inv = $item->inventory;
                    $isRegional = ($inv && (strtoupper($inv->inventory_source ?? '') === 'LOCAL' || !empty($inv->ddc_local_product_id)))
                        || !empty($item->localProduct)
                        || str_contains(strtolower($item->product_name ?? ''), 'local');

                    if ($isRegional) {
                        $regionalItemsCount++;
                    } else {
                        $msmeItemsCount++;
                    }

                    if ($sourcingScope === 'msme' && $isRegional) continue;
                    if ($sourcingScope === 'regional' && !$isRegional) continue;

                    $qty = (float) $item->quantity;
                    $unitPrice = (float) ($item->unit_price > 0 ? $item->unit_price : ($qty > 0 ? $item->total_price / $qty : 0));
                    $lineTotal = $qty * $unitPrice;
                    $gstRate = (float) ($item->gst_rate ?: ($inv?->gst_rate ?: ($item->product?->gst_rate ?: ($item->localProduct?->gst_rate ?: 0))));
                    
                    $taxable = $gstRate > 0 ? ($lineTotal / (1 + ($gstRate / 100))) : $lineTotal;
                    $tax = $lineTotal - $taxable;

                    $calcTotalItems += $qty;
                    $calcSubtotal += $taxable;
                    $calcTaxAmount += $tax;
                    $calcTotalAmount += $lineTotal;

                    // HSN Code
                    $hsn = $item->hsn_code ?: ($item->product?->hsn_code ?: ($item->localProduct?->hsn_code ?: ($inv?->product?->hsn_code ?: ($inv?->localProduct?->hsn_code ?: null))));
                    if ($hsn) $hsnList[] = $hsn;
                }

                if ($sourcingScope === 'msme' && $msmeItemsCount === 0) continue;
                if ($sourcingScope === 'regional' && $regionalItemsCount === 0) continue;

                $sourcingType = ($msmeItemsCount > 0 && $regionalItemsCount > 0) ? 'Mixed' : ($regionalItemsCount > 0 ? 'Regional' : 'MSME');
                $hsnDisplay = !empty($hsnList) ? implode(', ', array_unique($hsnList)) : '—';

                // If scope is 'all', show full order total
                if ($sourcingScope === 'all') {
                    $calcTotalItems  = (float) $order->total_items;
                    $calcSubtotal    = (float) $order->subtotal;
                    $calcTaxAmount   = (float) $order->tax_amount;
                    $calcTotalAmount = (float) $order->total_amount;
                }

                $records->push([
                    'id'               => 'pos_' . $order->id,
                    'raw_id'           => $order->id,
                    'reference_no'     => $order->invoice ? $order->invoice->invoice_number : $order->order_no,
                    'order_no'         => $order->order_no,
                    'invoice_no'       => $order->invoice ? $order->invoice->invoice_number : '—',
                    'hsn_code'         => $hsnDisplay,
                    'date'             => $order->created_at->format('d M Y, h:i A'),
                    'created_at'       => $order->created_at,
                    'source_type'      => $sourceLabel,
                    'source_key'       => $sourceKey,
                    'sourcing_type'    => $sourcingType,
                    'customer_name'    => $custName,
                    'customer_mobile'  => $order->customer_mobile ?: '—',
                    'total_items'      => $calcTotalItems,
                    'subtotal'         => $calcSubtotal,
                    'tax_amount'       => $calcTaxAmount,
                    'total_amount'     => $calcTotalAmount,
                    'payment_mode'     => strtoupper($order->payment_mode ?: 'CASH'),
                    'payment_status'   => ucfirst($order->payment_status ?: 'Paid'),
                    'view_url'         => route('ddc.pos.orders.print', $order->id),
                    'adc_invoice_url'  => ($isWholesale && $order->adc_order_id) ? route('ddc.adc-orders.invoice', $order->adc_order_id) : null,
                ]);
            }
        }

        // 2. Direct ADC Canteen Orders fulfilled by DDC
        if (in_array($source, ['all', 'adc'])) {
            $linkedAdcOrderIds = DdcPosOrder::where('ddc_id', $ddcId)
                ->whereNotNull('adc_order_id')
                ->pluck('adc_order_id')
                ->filter()
                ->toArray();

            $adcQuery = AdcOrder::where(function($q) use ($ddcId) {
                    $q->where('assigned_ddc_id', $ddcId)
                      ->orWhere('selected_ddc_id', $ddcId);
                })
                ->whereNotIn('id', $linkedAdcOrderIds)
                ->with(['adc', 'items.product']);

            if ($startDate) {
                $adcQuery->whereDate('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $adcQuery->whereDate('created_at', '<=', $endDate);
            }
            if ($search) {
                $adcQuery->where(function($q) use ($search) {
                    $q->where('order_number', 'like', "%{$search}%")
                      ->orWhere('advisor_code', 'like', "%{$search}%")
                      ->orWhereHas('adc', function($adcQ) use ($search) {
                          $adcQ->where('name', 'like', "%{$search}%")
                               ->orWhere('mobile', 'like', "%{$search}%");
                      })
                      ->orWhereHas('items', function($itemQ) use ($search) {
                          $itemQ->where('hsn_code', 'like', "%{$search}%")
                                ->orWhereHas('product', function($pQ) use ($search) {
                                    $pQ->where('hsn_code', 'like', "%{$search}%");
                                });
                      });
                });
            }

            $adcOrders = $adcQuery->get();

            foreach ($adcOrders as $order) {
                $custName = $order->adc ? $order->adc->name : 'ADC Canteen';
                if ($order->adc && $order->adc->company_name) {
                    $custName .= ' (' . $order->adc->company_name . ')';
                }

                $msmeItemsCount = 0;
                $regionalItemsCount = 0;
                $calcTotalItems = 0.0;
                $calcSubtotal = 0.0;
                $calcTaxAmount = 0.0;
                $calcTotalAmount = 0.0;
                $hsnList = [];

                foreach ($order->items as $item) {
                    $isRegional = !empty($item->ddc_local_product_id);

                    if ($isRegional) {
                        $regionalItemsCount++;
                    } else {
                        $msmeItemsCount++;
                    }

                    if ($sourcingScope === 'msme' && $isRegional) continue;
                    if ($sourcingScope === 'regional' && !$isRegional) continue;

                    $qty = (float) $item->quantity;
                    $lineTotal = (float) ($item->total > 0 ? $item->total : ($qty * $item->price));
                    $tax = (float) $item->gst_amount;
                    $subtotal = (float) ($item->taxable_amount > 0 ? $item->taxable_amount : ($lineTotal - $tax));

                    $calcTotalItems += $qty;
                    $calcSubtotal += $subtotal;
                    $calcTaxAmount += $tax;
                    $calcTotalAmount += $lineTotal;

                    $hsn = $item->hsn_code ?: ($item->product?->hsn_code ?: null);
                    if ($hsn) $hsnList[] = $hsn;
                }

                if ($sourcingScope === 'msme' && $msmeItemsCount === 0) continue;
                if ($sourcingScope === 'regional' && $regionalItemsCount === 0) continue;

                $sourcingType = ($msmeItemsCount > 0 && $regionalItemsCount > 0) ? 'Mixed' : ($regionalItemsCount > 0 ? 'Regional' : 'MSME');
                $hsnDisplay = !empty($hsnList) ? implode(', ', array_unique($hsnList)) : '—';

                if ($sourcingScope === 'all') {
                    $calcTotalItems  = (float) $order->items()->sum('quantity');
                    $calcSubtotal    = (float) $order->subtotal_excl_gst;
                    $calcTaxAmount   = (float) $order->total_gst;
                    $calcTotalAmount = (float) $order->total_amount;
                }

                $records->push([
                    'id'               => 'adc_' . $order->id,
                    'raw_id'           => $order->id,
                    'reference_no'     => $order->order_number,
                    'order_no'         => $order->order_number,
                    'invoice_no'       => $order->received_invoice_number ?: '—',
                    'hsn_code'         => $hsnDisplay,
                    'date'             => $order->created_at->format('d M Y, h:i A'),
                    'created_at'       => $order->created_at,
                    'source_type'      => 'ADC Canteen Order',
                    'source_key'       => 'adc',
                    'sourcing_type'    => $sourcingType,
                    'customer_name'    => $custName,
                    'customer_mobile'  => $order->adc ? $order->adc->mobile : '—',
                    'total_items'      => $calcTotalItems,
                    'subtotal'         => $calcSubtotal,
                    'tax_amount'       => $calcTaxAmount,
                    'total_amount'     => $calcTotalAmount,
                    'payment_mode'     => strtoupper($order->payment_method ?: 'WALLET'),
                    'payment_status'   => ucfirst($order->payment_status ?: 'Paid'),
                    'view_url'         => route('ddc.adc-orders.show', $order->id),
                    'adc_invoice_url'  => route('ddc.adc-orders.invoice', $order->id),
                ]);
            }
        }

        // Sort records descending by created_at
        $sortedRecords = $records->sortByDesc('created_at')->values();

        return [
            'records' => $sortedRecords,
        ];
    }
}
