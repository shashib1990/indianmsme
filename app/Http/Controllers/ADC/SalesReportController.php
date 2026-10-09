<?php

namespace App\Http\Controllers\ADC;

use App\Http\Controllers\Controller;
use App\Models\AdcPosOrder;
use App\Models\AdcPosOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class SalesReportController extends Controller
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

        $filters = $request->only(['start_date', 'end_date', 'source', 'sourcing', 'payment_mode', 'search']);
        $filters['sourcing'] = strtolower($request->input('sourcing', 'all'));
        if (!in_array($filters['sourcing'], ['all', 'msme', 'regional'])) {
            $filters['sourcing'] = 'all';
        }

        $combinedData = $this->getCombinedSalesRecords($adcId, $request);
        $records = $combinedData['records'];

        if (!empty($request->payment_mode) && $request->payment_mode !== 'all') {
            $records = $records->filter(function($row) use ($request) {
                return strtolower($row['payment_mode']) === strtolower($request->payment_mode);
            })->values();
        }

        $page = Paginator::resolveCurrentPage() ?: 1;
        $perPage = 20;
        $paginatedRecords = new LengthAwarePaginator(
            $records->forPage($page, $perPage),
            $records->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $summaryStats = $this->getSummaryStats($adcId, $records, $filters);

        return view('adc.reports.sales', compact('paginatedRecords', 'summaryStats', 'filters'));
    }

    public function exportCsv(Request $request)
    {
        $adcId = $this->getAdcId();
        $filters = $request->only(['start_date', 'end_date', 'source', 'sourcing', 'payment_mode', 'search']);
        $filters['sourcing'] = strtolower($request->input('sourcing', 'all'));
        if (!in_array($filters['sourcing'], ['all', 'msme', 'regional'])) {
            $filters['sourcing'] = 'all';
        }

        $combinedData = $this->getCombinedSalesRecords($adcId, $request);
        $records = $combinedData['records'];

        if (!empty($request->payment_mode) && $request->payment_mode !== 'all') {
            $records = $records->filter(function($row) use ($request) {
                return strtolower($row['payment_mode']) === strtolower($request->payment_mode);
            })->values();
        }

        $sourcingLabel = strtoupper($filters['sourcing']);
        $filename = 'adc_sales_report_' . strtolower($sourcingLabel) . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($records, $sourcingLabel) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['', "ADC SALES REPORT ({$sourcingLabel} SCOPE)"]);
            fputcsv($file, []);

            fputcsv($file, [
                '#',
                'Reference / Invoice No',
                'Order No',
                'HSN Code',
                'Date & Time',
                'Sales Source',
                'Sourcing Type',
                'Customer Name',
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
                    $row['invoice_no'],
                    $row['order_no'],
                    $row['hsn_code'],
                    $row['date'],
                    $row['sales_source'],
                    strtoupper($row['sourcing_type']),
                    $row['customer_name'],
                    $row['customer_mobile'],
                    number_format($row['total_items'], 3, '.', ''),
                    number_format($row['subtotal'], 2, '.', ''),
                    number_format($row['tax_amount'], 2, '.', ''),
                    number_format($row['total_amount'], 2, '.', ''),
                    strtoupper($row['payment_mode']),
                    strtoupper($row['payment_status']),
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['', 'SUMMARY TOTALS (' . $sourcingLabel . ')']);
            fputcsv($file, ['Total Sales Transactions', $records->count()]);
            fputcsv($file, ['Total Items Sold Qty', number_format($records->sum('total_items'), 3, '.', '')]);
            fputcsv($file, ['Total Taxable Subtotal (₹)', number_format($records->sum('subtotal'), 2, '.', '')]);
            fputcsv($file, ['Total GST Collected (₹)', number_format($records->sum('tax_amount'), 2, '.', '')]);
            fputcsv($file, ['Grand Total Sales Revenue (₹)', number_format($records->sum('total_amount'), 2, '.', '')]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function print(Request $request)
    {
        $adcId = $this->getAdcId();
        $filters = $request->only(['start_date', 'end_date', 'source', 'sourcing', 'payment_mode', 'search']);
        $filters['sourcing'] = strtolower($request->input('sourcing', 'all'));
        if (!in_array($filters['sourcing'], ['all', 'msme', 'regional'])) {
            $filters['sourcing'] = 'all';
        }

        $combinedData = $this->getCombinedSalesRecords($adcId, $request);
        $records = $combinedData['records'];

        if (!empty($request->payment_mode) && $request->payment_mode !== 'all') {
            $records = $records->filter(function($row) use ($request) {
                return strtolower($row['payment_mode']) === strtolower($request->payment_mode);
            })->values();
        }

        $summaryStats = $this->getSummaryStats($adcId, $records, $filters);

        return view('adc.reports.sales-print', compact('records', 'summaryStats', 'filters'));
    }

    private function getCombinedSalesRecords($adcId, Request $request)
    {
        $startDate     = $request->input('start_date');
        $endDate       = $request->input('end_date');
        $sourcingScope = strtolower($request->input('sourcing', 'all')); // all, msme, regional
        $search        = $request->input('search');

        $query = AdcPosOrder::where('adc_id', $adcId)
            ->with(['invoice', 'items.inventory', 'items.product', 'items.localProduct', 'customer']);

        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);

        $posOrders = $query->latest()->get();
        $records = collect();

        foreach ($posOrders as $order) {
            $netTotal = max(0, (float) $order->total_amount - (float) ($order->total_returned_amount ?? 0));
            $orderNo = $order->order_no ?: '—';
            $invNo   = $order->invoice?->invoice_number ?: ($order->received_invoice_number ?: 'N/A');
            $customerName   = $order->customer_name ?: ($order->customer?->name ?: 'Walk-in Customer');
            $customerMobile = $order->customer_mobile ?: ($order->customer?->phone ?: '—');

            $msmeTotal = 0;
            $regionalTotal = 0;
            $hsnList = [];
            $netTotalItems = 0;

            foreach ($order->items as $item) {
                $netQty = max(0, (float) $item->quantity - (float) ($item->returned_quantity ?? 0));
                if ($netQty <= 0) continue;

                $netTotalItems += $netQty;
                $unitPrice = (float) ($item->unit_price > 0 ? $item->unit_price : ($item->quantity > 0 ? $item->total_price / $item->quantity : 0));
                $itemLineTotal = $netQty * $unitPrice;

                $inv = $item->inventory;
                $isRegional = ($inv && (strtoupper($inv->inventory_source ?? '') === 'LOCAL' || !empty($inv->adc_local_product_id)))
                    || !empty($item->localProduct)
                    || str_contains(strtolower($item->product_name ?? ''), 'local');

                $itemHsn = trim($item->hsn_code ?: ($item->product?->hsn_code ?: ($item->localProduct?->hsn_code ?: ($inv?->product?->hsn_code ?: ''))));
                if ($itemHsn) $hsnList[$itemHsn] = true;

                if ($isRegional) {
                    $regionalTotal += $itemLineTotal;
                } else {
                    $msmeTotal += $itemLineTotal;
                }
            }

            if ($netTotalItems <= 0 || $netTotal <= 0) {
                // Fully returned order — skip from active Sales Report net sales
                continue;
            }

            $origTotalAmount = (float) $order->total_amount;
            if ($origTotalAmount > 0 && $netTotal < $origTotalAmount) {
                $subtotal  = round((float) $order->subtotal * ($netTotal / $origTotalAmount), 2);
                $taxAmount = round((float) $order->tax_amount * ($netTotal / $origTotalAmount), 2);
            } else {
                $subtotal  = (float) $order->subtotal;
                $taxAmount = (float) $order->tax_amount;
            }

            $orderSourcingType = ($msmeTotal > 0 && $regionalTotal > 0) ? 'Mixed' : ($regionalTotal > 0 ? 'Regional' : 'MSME');

            if ($sourcingScope === 'msme' && $orderSourcingType === 'Regional') continue;
            if ($sourcingScope === 'regional' && $orderSourcingType === 'MSME') continue;

            $hsnStr = implode(', ', array_keys($hsnList)) ?: 'N/A';

            if ($search && !str_contains(strtolower($orderNo), strtolower($search)) && !str_contains(strtolower($invNo), strtolower($search)) && !str_contains(strtolower($customerName), strtolower($search)) && !str_contains(strtolower($hsnStr), strtolower($search))) {
                continue;
            }

            $salesSource = 'Retail POS (CSP)';
            if (strtolower($order->mode ?? '') === 'wholesale') {
                $salesSource = 'Wholesale POS (MSP)';
            }

            $records->push([
                'id'              => $order->id,
                'type'            => 'pos',
                'invoice_no'      => $invNo,
                'order_no'        => $orderNo,
                'hsn_code'        => $hsnStr,
                'date'            => $order->created_at ? $order->created_at->format('d M Y, h:i A') : '—',
                'sales_source'    => $salesSource,
                'sourcing_type'   => $orderSourcingType,
                'customer_name'   => $customerName,
                'customer_mobile' => $customerMobile,
                'total_items'     => (float) $netTotalItems,
                'subtotal'        => (float) $subtotal,
                'tax_amount'      => (float) $taxAmount,
                'total_amount'    => (float) $netTotal,
                'payment_mode'    => $order->payment_mode ?: 'Cash',
                'payment_status'  => $order->payment_status ?: 'Paid',
            ]);
        }

        return ['records' => $records];
    }

    private function getSummaryStats($adcId, $records, $filters)
    {
        return [
            'total_sales_count' => $records->count(),
            'total_items_sold'  => (float) $records->sum('total_items'),
            'total_subtotal'    => (float) $records->sum('subtotal'),
            'total_tax'         => (float) $records->sum('tax_amount'),
            'total_revenue'     => (float) $records->sum('total_amount'),
        ];
    }
}
