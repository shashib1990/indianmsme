<?php

namespace App\Http\Controllers\ADC;

use App\Http\Controllers\Controller;
use App\Models\AdcPosSalesReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class SalesReturnReportController extends Controller
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

        $filters = $request->only(['start_date', 'end_date', 'sourcing', 'refund_mode', 'search']);
        $filters['sourcing'] = strtolower($request->input('sourcing', 'all'));
        if (!in_array($filters['sourcing'], ['all', 'msme', 'regional'])) {
            $filters['sourcing'] = 'all';
        }

        $records = $this->getSalesReturnRecords($adcId, $request);

        $page = Paginator::resolveCurrentPage() ?: 1;
        $perPage = 20;
        $paginatedRecords = new LengthAwarePaginator(
            $records->forPage($page, $perPage),
            $records->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $summaryStats = [
            'total_returns' => $records->count(),
            'total_qty'     => (float) $records->sum('total_qty'),
            'total_taxable' => (float) $records->sum('subtotal'),
            'total_tax'     => (float) $records->sum('tax_amount'),
            'total_refund'  => (float) $records->sum('total_return_amount'),
        ];

        return view('adc.reports.sales-returns', compact('paginatedRecords', 'summaryStats', 'filters'));
    }

    public function exportCsv(Request $request)
    {
        $adcId = $this->getAdcId();
        $filters = $request->only(['start_date', 'end_date', 'sourcing', 'refund_mode', 'search']);
        $filters['sourcing'] = strtolower($request->input('sourcing', 'all'));
        if (!in_array($filters['sourcing'], ['all', 'msme', 'regional'])) {
            $filters['sourcing'] = 'all';
        }

        $records = $this->getSalesReturnRecords($adcId, $request);
        $sourcingLabel = strtoupper($filters['sourcing']);
        $filename = 'adc_sales_return_report_' . strtolower($sourcingLabel) . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($records, $sourcingLabel) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['', "ADC SALE RETURN REPORT ({$sourcingLabel} SCOPE)"]);
            fputcsv($file, []);

            fputcsv($file, [
                '#',
                'Return Number',
                'Original Order No',
                'Original Invoice No',
                'Sales Channel',
                'Date & Time',
                'Sourcing Scope',
                'Customer Name',
                'Customer Mobile',
                'Returned Items Qty',
                'Taxable Refund Subtotal (₹)',
                'GST Tax Reversal (₹)',
                'Total Refund Amount (₹)',
                'Refund Mode',
            ]);

            foreach ($records as $idx => $row) {
                fputcsv($file, [
                    $idx + 1,
                    $row['return_number'],
                    $row['original_order_no'],
                    $row['original_invoice_no'],
                    $row['sales_channel'],
                    $row['date'],
                    strtoupper($row['sourcing_type']),
                    $row['customer_name'],
                    $row['customer_mobile'],
                    number_format($row['total_qty'], 3, '.', ''),
                    number_format($row['subtotal'], 2, '.', ''),
                    number_format($row['tax_amount'], 2, '.', ''),
                    number_format($row['total_return_amount'], 2, '.', ''),
                    strtoupper($row['refund_mode']),
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['', 'SUMMARY TOTALS (' . $sourcingLabel . ')']);
            fputcsv($file, ['Total Sales Return Receipts', $records->count()]);
            fputcsv($file, ['Total Returned Items Qty', number_format($records->sum('total_qty'), 3, '.', '')]);
            fputcsv($file, ['Taxable Refund Subtotal (₹)', number_format($records->sum('subtotal'), 2, '.', '')]);
            fputcsv($file, ['GST Tax Reversal (₹)', number_format($records->sum('tax_amount'), 2, '.', '')]);
            fputcsv($file, ['Net Refund Amount (₹)', number_format($records->sum('total_return_amount'), 2, '.', '')]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function print(Request $request)
    {
        $adcId = $this->getAdcId();
        $filters = $request->only(['start_date', 'end_date', 'sourcing', 'refund_mode', 'search']);
        $filters['sourcing'] = strtolower($request->input('sourcing', 'all'));
        if (!in_array($filters['sourcing'], ['all', 'msme', 'regional'])) {
            $filters['sourcing'] = 'all';
        }

        $records = $this->getSalesReturnRecords($adcId, $request);

        $summaryStats = [
            'total_returns' => $records->count(),
            'total_qty'     => (float) $records->sum('total_qty'),
            'total_taxable' => (float) $records->sum('subtotal'),
            'total_tax'     => (float) $records->sum('tax_amount'),
            'total_refund'  => (float) $records->sum('total_return_amount'),
        ];

        return view('adc.reports.sales-returns-print', compact('records', 'summaryStats', 'filters'));
    }

    private function getSalesReturnRecords($adcId, Request $request)
    {
        $startDate     = $request->input('start_date');
        $endDate       = $request->input('end_date');
        $sourcingScope = strtolower($request->input('sourcing', 'all'));
        $refundMode    = strtolower($request->input('refund_mode', 'all'));
        $search        = $request->input('search');

        $query = AdcPosSalesReturn::where('adc_id', $adcId)
            ->with(['order', 'customer', 'items.inventory', 'items.product', 'items.localProduct']);

        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);
        if ($refundMode && $refundMode !== 'all') $query->where('refund_mode', $refundMode);

        $returns = $query->latest()->get();
        $records = collect();

        foreach ($returns as $ret) {
            $retNo = $ret->return_number ?: '—';
            $origOrderNo = $ret->order?->order_no ?: '—';
            $origInvNo   = $ret->order?->received_invoice_number ?: ($origOrderNo ?: '—');
            $customerName   = $ret->customer_name ?: ($ret->customer?->name ?: 'Walk-in Customer');
            $customerMobile = $ret->customer_mobile ?: ($ret->customer?->phone ?: '—');

            $msmeTotal = 0;
            $regionalTotal = 0;

            foreach ($ret->items as $item) {
                $inv = $item->inventory;
                $isRegional = ($inv && (strtoupper($inv->inventory_source ?? '') === 'LOCAL' || !empty($inv->adc_local_product_id)))
                    || !empty($item->localProduct)
                    || str_contains(strtolower($item->product_name ?? ''), 'local');

                $itemAmt = (float) $item->total_amount;
                if ($isRegional) {
                    $regionalTotal += $itemAmt;
                } else {
                    $msmeTotal += $itemAmt;
                }
            }

            $sourcingType = ($msmeTotal > 0 && $regionalTotal > 0) ? 'Mixed' : ($regionalTotal > 0 ? 'Regional' : 'MSME');

            if ($sourcingScope === 'msme' && $sourcingType === 'Regional') continue;
            if ($sourcingScope === 'regional' && $sourcingType === 'MSME') continue;

            if ($search && !str_contains(strtolower($retNo), strtolower($search)) && !str_contains(strtolower($origOrderNo), strtolower($search)) && !str_contains(strtolower($origInvNo), strtolower($search)) && !str_contains(strtolower($customerName), strtolower($search))) {
                continue;
            }

            $salesChannel = 'Retail POS (CSP)';
            if (strtolower($ret->order?->mode ?? '') === 'wholesale') {
                $salesChannel = 'Wholesale POS (MSP)';
            }

            $records->push([
                'id'                  => $ret->id,
                'return_number'       => $retNo,
                'original_order_no'   => $origOrderNo,
                'original_invoice_no' => $origInvNo,
                'sales_channel'       => $salesChannel,
                'date'                => $ret->created_at ? $ret->created_at->format('d M Y, h:i A') : '—',
                'sourcing_type'       => $sourcingType,
                'customer_name'       => $customerName,
                'customer_mobile'     => $customerMobile,
                'total_qty'           => (float) $ret->total_qty,
                'subtotal'            => (float) $ret->subtotal,
                'tax_amount'          => (float) $ret->tax_amount,
                'total_return_amount' => (float) $ret->total_return_amount,
                'refund_mode'         => $ret->refund_mode ?: 'Cash',
                'return_reason'       => $ret->return_reason ?: 'N/A',
            ]);
        }

        return $records;
    }
}
