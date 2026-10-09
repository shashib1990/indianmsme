<?php

namespace App\Http\Controllers\ADC;

use App\Http\Controllers\Controller;
use App\Models\AdcOrder;
use App\Models\AdcLocalPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class PurchaseReportController extends Controller
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

        $filters = $request->only(['start_date', 'end_date', 'source', 'sourcing', 'search']);
        $filters['sourcing'] = strtolower($request->input('sourcing', 'all'));
        if (!in_array($filters['sourcing'], ['all', 'msme', 'regional'])) {
            $filters['sourcing'] = 'all';
        }

        $records = $this->getPurchaseRecords($adcId, $request);

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
            'total_purchases' => $records->count(),
            'total_items'     => (float) $records->sum('total_items'),
            'total_subtotal'  => (float) $records->sum('subtotal'),
            'total_tax'       => (float) $records->sum('tax_amount'),
            'total_amount'    => (float) $records->sum('total_amount'),
        ];

        return view('adc.reports.purchases', compact('paginatedRecords', 'summaryStats', 'filters'));
    }

    public function exportCsv(Request $request)
    {
        $adcId = $this->getAdcId();
        $filters = $request->only(['start_date', 'end_date', 'source', 'sourcing', 'search']);
        $filters['sourcing'] = strtolower($request->input('sourcing', 'all'));
        if (!in_array($filters['sourcing'], ['all', 'msme', 'regional'])) {
            $filters['sourcing'] = 'all';
        }

        $records = $this->getPurchaseRecords($adcId, $request);
        $sourceLabel = strtoupper($filters['sourcing']);
        $filename = 'adc_purchase_report_' . strtolower($sourceLabel) . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($records, $sourceLabel) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['', "ADC PURCHASE REPORT ({$sourceLabel} SCOPE)"]);
            fputcsv($file, []);

            fputcsv($file, [
                '#',
                'Purchase / PO No',
                'Date',
                'Source Type',
                'Supplier / Vendor',
                'Invoice No',
                'Total Items / Qty',
                'Subtotal (₹)',
                'Tax Amount (₹)',
                'Total Amount (₹)',
                'Order Status',
                'Payment Status',
            ]);

            foreach ($records as $idx => $row) {
                fputcsv($file, [
                    $idx + 1,
                    $row['reference_no'],
                    $row['date'],
                    $row['source_type'],
                    $row['supplier_name'],
                    $row['invoice_no'],
                    number_format($row['total_items'], 3, '.', ''),
                    number_format($row['subtotal'], 2, '.', ''),
                    number_format($row['tax_amount'], 2, '.', ''),
                    number_format($row['total_amount'], 2, '.', ''),
                    strtoupper($row['status']),
                    strtoupper($row['payment_status']),
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['', 'SUMMARY TOTALS (' . $sourceLabel . ')']);
            fputcsv($file, ['Total Purchase Records', $records->count()]);
            fputcsv($file, ['Total Quantity Purchased', number_format($records->sum('total_items'), 3, '.', '')]);
            fputcsv($file, ['Total Purchase Value (₹)', number_format($records->sum('total_amount'), 2, '.', '')]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function print(Request $request)
    {
        $adcId = $this->getAdcId();
        $filters = $request->only(['start_date', 'end_date', 'source', 'sourcing', 'search']);
        $filters['sourcing'] = strtolower($request->input('sourcing', 'all'));
        if (!in_array($filters['sourcing'], ['all', 'msme', 'regional'])) {
            $filters['sourcing'] = 'all';
        }

        $records = $this->getPurchaseRecords($adcId, $request);

        $summaryStats = [
            'total_purchases' => $records->count(),
            'total_items'     => (float) $records->sum('total_items'),
            'total_subtotal'  => (float) $records->sum('subtotal'),
            'total_tax'       => (float) $records->sum('tax_amount'),
            'total_amount'    => (float) $records->sum('total_amount'),
        ];

        return view('adc.reports.purchases-print', compact('records', 'summaryStats', 'filters'));
    }

    private function getPurchaseRecords($adcId, Request $request)
    {
        $startDate     = $request->input('start_date');
        $endDate       = $request->input('end_date');
        $sourcingScope = strtolower($request->input('sourcing', 'all')); // all, msme, regional
        $search        = $request->input('search');

        $records = collect();

        // 1. Central Vendor PO Orders (AdcOrder) -> MSME
        if ($sourcingScope === 'all' || $sourcingScope === 'msme') {
            $adcOrdersQuery = AdcOrder::where('adc_id', $adcId)->with(['items', 'invoice', 'purchaseOrders.invoice']);

            if ($startDate) $adcOrdersQuery->whereDate('created_at', '>=', $startDate);
            if ($endDate) $adcOrdersQuery->whereDate('created_at', '<=', $endDate);

            $adcOrders = $adcOrdersQuery->latest()->get();

            foreach ($adcOrders as $po) {
                $refNo = $po->order_number ?: '—';
                
                // Fetch Invoice Number directly from invoices table
                $invNo = $po->invoice?->invoice_number;
                if (!$invNo) {
                    foreach ($po->purchaseOrders as $purchOrd) {
                        if ($purchOrd->invoice?->invoice_number) {
                            $invNo = $purchOrd->invoice->invoice_number;
                            break;
                        }
                    }
                }
                if (!$invNo) {
                    $invNo = '—';
                }

                $vendorName = 'MSME Central Vendor';

                if ($search && !str_contains(strtolower($refNo), strtolower($search)) && !str_contains(strtolower($invNo), strtolower($search)) && !str_contains(strtolower($vendorName), strtolower($search))) {
                    continue;
                }

                $totalQty = $po->items->sum('quantity');
                $subtotal = $po->items->sum('taxable_amount') ?: ($po->total_amount - $po->gst_amount);

                $records->push([
                    'id'             => $po->id,
                    'type'           => 'central_po',
                    'reference_no'   => $refNo,
                    'invoice_no'     => $invNo,
                    'date'           => $po->created_at ? $po->created_at->format('d M Y, h:i A') : '—',
                    'created_at'     => $po->created_at,
                    'source_type'    => 'MSME Central Vendor',
                    'supplier_name'  => $vendorName,
                    'total_items'    => (float) $totalQty,
                    'subtotal'       => (float) $subtotal,
                    'tax_amount'     => (float) $po->gst_amount,
                    'total_amount'   => (float) $po->total_amount,
                    'status'         => $po->status ?: 'Completed',
                    'payment_status' => $po->payment_status ?: 'Paid',
                ]);
            }
        }

        // 2. Regional Local Stock Purchases (AdcLocalPurchase) -> Regional
        if ($sourcingScope === 'all' || $sourcingScope === 'regional') {
            $localQuery = AdcLocalPurchase::where('adc_id', $adcId)->with(['items']);

            if ($startDate) $localQuery->whereDate('purchase_date', '>=', $startDate);
            if ($endDate) $localQuery->whereDate('purchase_date', '<=', $endDate);

            $localPurchases = $localQuery->latest()->get();

            foreach ($localPurchases as $purchase) {
                $refNo = $purchase->purchase_no ?: ('LP-' . $purchase->id);
                $invNo = $purchase->invoice_number ?: ($purchase->invoice_no ?: '—');
                $supplierName = $purchase->supplier_name ?: 'Local Supplier';

                if ($search && !str_contains(strtolower($refNo), strtolower($search)) && !str_contains(strtolower($invNo), strtolower($search)) && !str_contains(strtolower($supplierName), strtolower($search))) {
                    continue;
                }

                $totalQty = $purchase->items->sum('quantity');
                $subtotal = $purchase->items->sum('taxable_amount') ?: ($purchase->items->sum('subtotal') ?: $purchase->total_amount);
                $taxAmount = $purchase->items->sum('tax_amount');

                $records->push([
                    'id'             => $purchase->id,
                    'type'           => 'local_purchase',
                    'reference_no'   => $refNo,
                    'invoice_no'     => $invNo,
                    'date'           => $purchase->purchase_date ? \Carbon\Carbon::parse($purchase->purchase_date)->format('d M Y') : '—',
                    'created_at'     => $purchase->created_at ?: $purchase->purchase_date,
                    'source_type'    => 'Regional Local Supplier',
                    'supplier_name'  => $supplierName,
                    'total_items'    => (float) $totalQty,
                    'subtotal'       => (float) $subtotal,
                    'tax_amount'     => (float) $taxAmount,
                    'total_amount'   => (float) $purchase->total_amount,
                    'status'         => 'Completed',
                    'payment_status' => 'Paid',
                ]);
            }
        }

        return $records->sortByDesc('created_at')->values();
    }
}
