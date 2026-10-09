<?php

namespace App\Http\Controllers\ADC;

use App\Http\Controllers\Controller;
use App\Models\AdcPosOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class HsnSalesReportController extends Controller
{
    private function getAdcId()
    {
        return Auth::guard('adc')->id() ?: (Auth::id() ?: 0);
    }

    public function index(Request $request)
    {
        $adcId = $this->getAdcId();
        $startDate = $request->input('start_date', \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->input('end_date', \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d'));

        $request->merge([
            'start_date' => $startDate,
            'end_date'   => $endDate,
        ]);

        $filters = $request->only(['start_date', 'end_date', 'source', 'sourcing', 'search', 'view_mode']);
        $filters['sourcing'] = strtolower($request->input('sourcing', 'all'));
        if (!in_array($filters['sourcing'], ['all', 'msme', 'regional'])) {
            $filters['sourcing'] = 'all';
        }

        $filters['view_mode'] = strtolower($request->input('view_mode', 'summary'));
        if (!in_array($filters['view_mode'], ['summary', 'detailed'])) {
            $filters['view_mode'] = 'summary';
        }

        $hsnRecords = $this->getHsnSalesRecords($adcId, $request);

        $page = Paginator::resolveCurrentPage() ?: 1;
        $perPage = 20;
        $paginatedRecords = new LengthAwarePaginator(
            $hsnRecords->forPage($page, $perPage),
            $hsnRecords->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $summaryStats = [
            'total_hsns'    => $hsnRecords->count(),
            'total_qty'     => (float) $hsnRecords->sum('total_qty'),
            'total_taxable' => (float) $hsnRecords->sum('taxable_amount'),
            'total_tax'     => (float) $hsnRecords->sum('tax_amount'),
            'total_amount'  => (float) $hsnRecords->sum('total_amount'),
        ];

        return view('adc.reports.hsn-sales', compact('paginatedRecords', 'summaryStats', 'filters'));
    }

    public function exportCsv(Request $request)
    {
        $adcId = $this->getAdcId();
        $filters = $request->only(['start_date', 'end_date', 'source', 'sourcing', 'search', 'view_mode']);
        $filters['sourcing'] = strtolower($request->input('sourcing', 'all'));
        if (!in_array($filters['sourcing'], ['all', 'msme', 'regional'])) {
            $filters['sourcing'] = 'all';
        }

        $filters['view_mode'] = strtolower($request->input('view_mode', 'summary'));
        if (!in_array($filters['view_mode'], ['summary', 'detailed'])) {
            $filters['view_mode'] = 'summary';
        }

        $hsnRecords = $this->getHsnSalesRecords($adcId, $request);
        $sourcingLabel = strtoupper($filters['sourcing']);
        $viewLabel     = strtoupper($filters['view_mode']);
        $filename = 'adc_hsn_sales_report_' . strtolower($sourcingLabel) . '_' . strtolower($viewLabel) . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($hsnRecords, $sourcingLabel, $viewLabel, $filters) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['', "ADC HSN-WISE SALES REPORT ({$sourcingLabel} SCOPE - {$viewLabel} VIEW)"]);
            fputcsv($file, []);

            if ($filters['view_mode'] === 'detailed') {
                fputcsv($file, [
                    '#',
                    'HSN Code',
                    'Order No',
                    'Invoice No',
                    'Date & Time',
                    'Product Name',
                    'Sourcing Type',
                    'Qty Sold',
                    'GST Rate (%)',
                    'Taxable Value (₹)',
                    'CGST Amount (₹)',
                    'SGST Amount (₹)',
                    'Total Tax Amount (₹)',
                    'Total Sales Value (₹)',
                ]);

                foreach ($hsnRecords as $idx => $row) {
                    fputcsv($file, [
                        $idx + 1,
                        $row['hsn_code'],
                        $row['order_no'],
                        $row['invoice_no'],
                        $row['date'] ?? '—',
                        $row['description'],
                        strtoupper($row['sourcing_type']),
                        number_format($row['total_qty'], 3, '.', ''),
                        number_format($row['gst_rate'], 2, '.', ''),
                        number_format($row['taxable_amount'], 2, '.', ''),
                        number_format($row['cgst_amount'], 2, '.', ''),
                        number_format($row['sgst_amount'], 2, '.', ''),
                        number_format($row['tax_amount'], 2, '.', ''),
                        number_format($row['total_amount'], 2, '.', ''),
                    ]);
                }
            } else {
                fputcsv($file, [
                    '#',
                    'HSN Code',
                    'Order Number(s)',
                    'Invoice Number(s)',
                    'Product / Category Description',
                    'Sourcing Type',
                    'Total Qty Sold',
                    'Avg GST Rate (%)',
                    'Taxable Value (₹)',
                    'CGST Amount (₹)',
                    'SGST Amount (₹)',
                    'Total Tax Amount (₹)',
                    'Total Sales Value (₹)',
                ]);

                foreach ($hsnRecords as $idx => $row) {
                    fputcsv($file, [
                        $idx + 1,
                        $row['hsn_code'],
                        $row['order_no_excel'] ?? $row['order_no_full'],
                        $row['invoice_no_excel'] ?? $row['invoice_no_full'],
                        $row['description'],
                        strtoupper($row['sourcing_type']),
                        number_format($row['total_qty'], 3, '.', ''),
                        number_format($row['gst_rate'], 2, '.', ''),
                        number_format($row['taxable_amount'], 2, '.', ''),
                        number_format($row['cgst_amount'], 2, '.', ''),
                        number_format($row['sgst_amount'], 2, '.', ''),
                        number_format($row['tax_amount'], 2, '.', ''),
                        number_format($row['total_amount'], 2, '.', ''),
                    ]);
                }
            }

            fputcsv($file, []);
            fputcsv($file, ['', 'SUMMARY TOTALS (' . $sourcingLabel . ')']);
            fputcsv($file, ['Total Entries', $hsnRecords->count()]);
            fputcsv($file, ['Total Quantity Sold', number_format($hsnRecords->sum('total_qty'), 3, '.', '')]);
            fputcsv($file, ['Total Taxable Value (₹)', number_format($hsnRecords->sum('taxable_amount'), 2, '.', '')]);
            fputcsv($file, ['Total GST Amount (₹)', number_format($hsnRecords->sum('tax_amount'), 2, '.', '')]);
            fputcsv($file, ['Grand Total Sales (₹)', number_format($hsnRecords->sum('total_amount'), 2, '.', '')]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function print(Request $request)
    {
        $adcId = $this->getAdcId();
        $filters = $request->only(['start_date', 'end_date', 'source', 'sourcing', 'search', 'view_mode']);
        $filters['sourcing'] = strtolower($request->input('sourcing', 'all'));
        if (!in_array($filters['sourcing'], ['all', 'msme', 'regional'])) {
            $filters['sourcing'] = 'all';
        }

        $filters['view_mode'] = strtolower($request->input('view_mode', 'summary'));
        if (!in_array($filters['view_mode'], ['summary', 'detailed'])) {
            $filters['view_mode'] = 'summary';
        }

        $hsnRecords = $this->getHsnSalesRecords($adcId, $request);

        $summaryStats = [
            'total_hsns'    => $hsnRecords->count(),
            'total_qty'     => (float) $hsnRecords->sum('total_qty'),
            'total_taxable' => (float) $hsnRecords->sum('taxable_amount'),
            'total_tax'     => (float) $hsnRecords->sum('tax_amount'),
            'total_amount'  => (float) $hsnRecords->sum('total_amount'),
        ];

        return view('adc.reports.hsn-sales-print', compact('hsnRecords', 'summaryStats', 'filters'));
    }

    private function getHsnSalesRecords($adcId, Request $request): Collection
    {
        $startDate     = $request->input('start_date');
        $endDate       = $request->input('end_date');
        $sourcingScope = strtolower($request->input('sourcing', 'all'));
        $viewMode      = strtolower($request->input('view_mode', 'summary'));
        $search        = $request->input('search');

        $aggregated = [];
        $detailedRecords = collect();

        $posQuery = AdcPosOrder::where('adc_id', $adcId)
            ->with(['items.inventory', 'items.product', 'items.localProduct']);

        if ($startDate) $posQuery->whereDate('created_at', '>=', $startDate);
        if ($endDate) $posQuery->whereDate('created_at', '<=', $endDate);

        $posOrders = $posQuery->get();

        foreach ($posOrders as $order) {
            $posOrderNo = $order->order_no ?: '—';
            $posInvNo   = $order->received_invoice_number ?: ($order->order_no ?: '—');
            $orderDate  = $order->created_at ? $order->created_at->format('d M Y, h:i A') : '—';

            foreach ($order->items as $item) {
                $inv = $item->inventory;
                $isRegional = ($inv && (strtoupper($inv->inventory_source ?? '') === 'LOCAL' || !empty($inv->adc_local_product_id)))
                    || !empty($item->localProduct)
                    || str_contains(strtolower($item->product_name ?? ''), 'local');

                if ($sourcingScope === 'msme' && $isRegional) continue;
                if ($sourcingScope === 'regional' && !$isRegional) continue;

                $hsn = trim($item->hsn_code ?: ($item->product?->hsn_code ?: ($item->localProduct?->hsn_code ?: 'N/A')));
                $prodName = $item->product_name ?: ($item->product?->product_name ?: ($item->localProduct?->product_name ?: 'General Item'));

                if ($search && !str_contains(strtolower($hsn), strtolower($search)) && !str_contains(strtolower($prodName), strtolower($search)) && !str_contains(strtolower($posOrderNo), strtolower($search)) && !str_contains(strtolower($posInvNo), strtolower($search))) {
                    continue;
                }

                $qty = (float) $item->quantity;
                $unitPrice = (float) ($item->unit_price > 0 ? $item->unit_price : ($qty > 0 ? $item->total_price / $qty : 0));
                $lineTotal = $qty * $unitPrice;
                $gstRate = (float) ($item->gst_rate ?: ($item->product?->gst_rate ?: ($item->localProduct?->gst_rate ?: 0)));
                
                $taxable = $gstRate > 0 ? ($lineTotal / (1.0 + ($gstRate / 100.0))) : $lineTotal;
                $cgst = $gstRate > 0 ? ($taxable * (($gstRate / 2.0) / 100.0)) : 0.0;
                $sgst = $gstRate > 0 ? ($taxable * (($gstRate / 2.0) / 100.0)) : 0.0;
                $tax = $cgst + $sgst;
                $sourcingType = $isRegional ? 'Regional' : 'MSME';

                if ($viewMode === 'detailed') {
                    $detailedRecords->push([
                        'hsn_code'         => $hsn,
                        'order_no'         => $posOrderNo,
                        'invoice_no'       => $posInvNo,
                        'order_no_full'    => $posOrderNo,
                        'invoice_no_full'  => $posInvNo,
                        'order_list'       => [$posOrderNo],
                        'invoice_list'     => [$posInvNo],
                        'description'      => $prodName,
                        'date'             => $orderDate,
                        'created_at'       => $order->created_at,
                        'sourcing_type'    => $sourcingType,
                        'total_qty'        => $qty,
                        'gst_rate'         => $gstRate,
                        'taxable_amount'   => $taxable,
                        'tax_amount'       => $tax,
                        'cgst_amount'      => $cgst,
                        'sgst_amount'      => $sgst,
                        'total_amount'     => $lineTotal,
                    ]);
                } else {
                    if (!isset($aggregated[$hsn])) {
                        $aggregated[$hsn] = [
                            'hsn_code'         => $hsn,
                            'names'            => [],
                            'orders'           => [],
                            'invoices'         => [],
                            'msme_count'       => 0,
                            'regional_count'   => 0,
                            'total_qty'        => 0.0,
                            'taxable_amount'   => 0.0,
                            'tax_amount'       => 0.0,
                            'cgst_amount'      => 0.0,
                            'sgst_amount'      => 0.0,
                            'total_amount'     => 0.0,
                            'gst_rates'        => [],
                        ];
                    }

                    $aggregated[$hsn]['names'][$prodName] = true;
                    if ($posOrderNo !== '—') $aggregated[$hsn]['orders'][$posOrderNo] = true;
                    if ($posInvNo !== '—') $aggregated[$hsn]['invoices'][$posInvNo] = true;

                    if ($isRegional) {
                        $aggregated[$hsn]['regional_count']++;
                    } else {
                        $aggregated[$hsn]['msme_count']++;
                    }

                    $aggregated[$hsn]['total_qty']      += $qty;
                    $aggregated[$hsn]['taxable_amount'] += $taxable;
                    $aggregated[$hsn]['tax_amount']     += $tax;
                    $aggregated[$hsn]['cgst_amount']    += $cgst;
                    $aggregated[$hsn]['sgst_amount']    += $sgst;
                    $aggregated[$hsn]['total_amount']   += $lineTotal;
                    if ($gstRate > 0) $aggregated[$hsn]['gst_rates'][] = $gstRate;
                }
            }
        }

        if ($viewMode === 'detailed') {
            return $detailedRecords->sortByDesc('created_at')->values();
        }

        $records = collect();
        foreach ($aggregated as $hsn => $data) {
            $nameList = array_keys($data['names']);
            $desc = count($nameList) > 3
                ? implode(', ', array_slice($nameList, 0, 3)) . '... (+' . (count($nameList) - 3) . ' more)'
                : implode(', ', $nameList);

            $orderList   = array_filter(array_keys($data['orders'] ?? []), fn($val) => $val !== '—');
            $invoiceList = array_filter(array_keys($data['invoices'] ?? []), fn($val) => $val !== '—');

            $orderDisplay = count($orderList) > 0
                ? (count($orderList) > 2 ? implode(', ', array_slice($orderList, 0, 2)) . '... (+' . (count($orderList) - 2) . ')' : implode(', ', $orderList))
                : '—';

            $invoiceDisplay = count($invoiceList) > 0
                ? (count($invoiceList) > 2 ? implode(', ', array_slice($invoiceList, 0, 2)) . '... (+' . (count($invoiceList) - 2) . ')' : implode(', ', $invoiceList))
                : '—';

            $sourcingType = ($data['msme_count'] > 0 && $data['regional_count'] > 0) ? 'Mixed' : ($data['regional_count'] > 0 ? 'Regional' : 'MSME');
            $avgGst = count($data['gst_rates']) > 0 ? (array_sum($data['gst_rates']) / count($data['gst_rates'])) : 0;

            $records->push([
                'hsn_code'         => $hsn,
                'description'      => $desc ?: 'N/A',
                'order_no'         => $orderDisplay,
                'invoice_no'       => $invoiceDisplay,
                'order_no_full'    => !empty($orderList) ? implode(', ', $orderList) : '—',
                'invoice_no_full'  => !empty($invoiceList) ? implode(', ', $invoiceList) : '—',
                'order_no_excel'   => !empty($orderList) ? implode("\n", $orderList) : '—',
                'invoice_no_excel' => !empty($invoiceList) ? implode("\n", $invoiceList) : '—',
                'order_list'       => array_values($orderList),
                'invoice_list'     => array_values($invoiceList),
                'sourcing_type'    => $sourcingType,
                'total_qty'        => $data['total_qty'],
                'gst_rate'         => $avgGst,
                'taxable_amount'   => $data['taxable_amount'],
                'tax_amount'       => $data['tax_amount'],
                'cgst_amount'      => $data['cgst_amount'],
                'sgst_amount'      => $data['sgst_amount'],
                'total_amount'     => $data['total_amount'],
            ]);
        }

        return $records->sortByDesc('total_amount')->values();
    }
}
