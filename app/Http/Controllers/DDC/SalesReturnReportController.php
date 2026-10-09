<?php

namespace App\Http\Controllers\DDC;

use App\Http\Controllers\Controller;
use App\Models\DdcPosSalesReturn;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class SalesReturnReportController extends Controller
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

        $filters = $request->only(['start_date', 'end_date', 'sourcing', 'refund_mode', 'search']);
        $filters['sourcing'] = strtolower($request->input('sourcing', 'all'));
        if (!in_array($filters['sourcing'], ['all', 'msme', 'regional'])) {
            $filters['sourcing'] = 'all';
        }

        $records = $this->getSalesReturnRecords($ddcId, $request);

        // Apply Refund Mode filter if provided
        if (!empty($filters['refund_mode']) && $filters['refund_mode'] !== 'all') {
            $records = $records->filter(function($row) use ($filters) {
                return strtolower($row['refund_mode']) === strtolower($filters['refund_mode']);
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

        $summaryStats = [
            'total_count'        => $records->count(),
            'total_returned_qty' => (float) $records->sum('total_qty'),
            'subtotal_refund'    => (float) $records->sum('subtotal'),
            'tax_reversal'       => (float) $records->sum('tax_amount'),
            'total_refund'       => (float) $records->sum('total_return_amount'),
        ];

        return view('ddc.reports.sales-returns', compact('paginatedRecords', 'summaryStats', 'filters'));
    }

    public function exportCsv(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $filters = $request->only(['start_date', 'end_date', 'sourcing', 'refund_mode', 'search']);
        $filters['sourcing'] = strtolower($request->input('sourcing', 'all'));
        if (!in_array($filters['sourcing'], ['all', 'msme', 'regional'])) {
            $filters['sourcing'] = 'all';
        }

        $records = $this->getSalesReturnRecords($ddcId, $request);

        if (!empty($request->refund_mode) && $request->refund_mode !== 'all') {
            $records = $records->filter(function($row) use ($request) {
                return strtolower($row['refund_mode']) === strtolower($request->refund_mode);
            })->values();
        }

        $sourcingLabel = strtoupper($filters['sourcing']);
        $filename = 'ddc_sales_returns_report_' . strtolower($sourcingLabel) . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($records, $sourcingLabel) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['', "SALE RETURN REPORT ({$sourcingLabel} SCOPE)"]);
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
                    $row['refund_mode'],
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['SUMMARY TOTALS (' . $sourcingLabel . ')']);
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
        $ddcId = Auth::guard('ddc')->id();
        $filters = $request->only(['start_date', 'end_date', 'sourcing', 'refund_mode', 'search']);
        $filters['sourcing'] = strtolower($request->input('sourcing', 'all'));
        if (!in_array($filters['sourcing'], ['all', 'msme', 'regional'])) {
            $filters['sourcing'] = 'all';
        }

        $records = $this->getSalesReturnRecords($ddcId, $request);

        if (!empty($request->refund_mode) && $request->refund_mode !== 'all') {
            $records = $records->filter(function($row) use ($request) {
                return strtolower($row['refund_mode']) === strtolower($request->refund_mode);
            })->values();
        }

        $summaryStats = [
            'total_count'        => $records->count(),
            'total_returned_qty' => (float) $records->sum('total_qty'),
            'subtotal_refund'    => (float) $records->sum('subtotal'),
            'tax_reversal'       => (float) $records->sum('tax_amount'),
            'total_refund'       => (float) $records->sum('total_return_amount'),
        ];

        return view('ddc.reports.sales-returns-print', compact('records', 'summaryStats', 'filters'));
    }

    private function getSalesReturnRecords($ddcId, Request $request): Collection
    {
        $startDate     = $request->input('start_date');
        $endDate       = $request->input('end_date');
        $sourcingScope = strtolower($request->input('sourcing', 'all')); // all, msme, regional
        $search        = $request->input('search');

        $query = DdcPosSalesReturn::where('ddc_id', $ddcId)
            ->with(['order.invoice', 'customer', 'items.inventory.product', 'items.inventory.localProduct', 'items.product', 'items.localProduct']);

        if ($startDate) $query->whereDate('created_at', '>=', $startDate);
        if ($endDate) $query->whereDate('created_at', '<=', $endDate);
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('return_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('customer_mobile', 'like', "%{$search}%")
                    ->orWhereHas('order', function ($oq) use ($search) {
                        $oq->where('order_no', 'like', "%{$search}%");
                    });
            });
        }

        $returns = $query->latest()->get();
        $records = collect();

        foreach ($returns as $ret) {
            $msmeCount = 0;
            $regionalCount = 0;
            $calcQty = 0.0;
            $calcSubtotal = 0.0;
            $calcTax = 0.0;
            $calcTotal = 0.0;
            $itemsPayload = [];

            foreach ($ret->items as $rItem) {
                $inv = $rItem->inventory;
                $isRegional = ($inv && (strtoupper($inv->inventory_source ?? '') === 'LOCAL' || !empty($inv->ddc_local_product_id)))
                    || !empty($rItem->localProduct)
                    || str_contains(strtolower($rItem->product_name ?? ''), 'local');

                if ($isRegional) {
                    $regionalCount++;
                } else {
                    $msmeCount++;
                }

                if ($sourcingScope === 'msme' && $isRegional) continue;
                if ($sourcingScope === 'regional' && !$isRegional) continue;

                $qty = (float) $rItem->returned_quantity;
                $lineTotal = (float) $rItem->total_amount;
                $gstRate = (float) $rItem->gst_rate;
                $taxable = $gstRate > 0 ? ($lineTotal / (1 + ($gstRate / 100))) : $lineTotal;
                $tax = $lineTotal - $taxable;

                $calcQty += $qty;
                $calcSubtotal += $taxable;
                $calcTax += $tax;
                $calcTotal += $lineTotal;

                $itemsPayload[] = [
                    'id'                => $rItem->id,
                    'product_name'      => $rItem->product_name ?: 'Product',
                    'product_code'      => $rItem->product_code ?: '—',
                    'returned_quantity' => $qty,
                    'unit_of_measure'   => $rItem->unit_of_measure ?: 'Units',
                    'unit_price'        => (float) $rItem->unit_price,
                    'gst_rate'          => $gstRate,
                    'total_amount'      => $lineTotal,
                ];
            }

            if ($sourcingScope === 'msme' && $msmeCount === 0) continue;
            if ($sourcingScope === 'regional' && $regionalCount === 0) continue;

            $sourcingType = ($msmeCount > 0 && $regionalCount > 0) ? 'Mixed' : ($regionalCount > 0 ? 'Regional' : 'MSME');

            if ($sourcingScope === 'all') {
                $calcQty      = (float) $ret->total_qty;
                $calcSubtotal = (float) $ret->subtotal;
                $calcTax      = (float) $ret->tax_amount;
                $calcTotal    = (float) $ret->total_return_amount;
            }

            $origOrderNo = $ret->order?->order_no ?: '—';
            $origInvoiceNo = $ret->order?->invoice?->invoice_number ?: '—';
            $salesChannel = $ret->order ? (strtolower($ret->order->mode) === 'wholesale' ? 'Wholesale POS' : 'Retail POS') : 'Retail POS';

            $records->push([
                'id'                  => $ret->id,
                'return_number'       => $ret->return_number,
                'original_order_no'   => $origOrderNo,
                'original_invoice_no' => $origInvoiceNo,
                'original_invoice'    => ($origInvoiceNo !== '—' ? $origInvoiceNo : $origOrderNo),
                'sales_channel'       => $salesChannel,
                'date'                => $ret->created_at->format('d M Y, h:i A'),
                'created_at'          => $ret->created_at,
                'sourcing_type'       => $sourcingType,
                'customer_name'       => $ret->customer_name ?: 'Walk-in Customer',
                'customer_mobile'     => $ret->customer_mobile ?: '—',
                'total_items'         => (int) $ret->total_items,
                'total_qty'           => $calcQty,
                'subtotal'            => $calcSubtotal,
                'tax_amount'          => $calcTax,
                'total_return_amount' => $calcTotal,
                'refund_mode'         => strtoupper($ret->refund_mode ?: 'CASH'),
                'items_json'          => $itemsPayload,
                'print_url'           => route('ddc.pos.returns.print', $ret->id),
            ]);
        }

        return $records->sortByDesc('created_at')->values();
    }
}
