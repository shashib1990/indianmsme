<?php

namespace App\Http\Controllers\DDC;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\DdcLocalPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class PurchaseReportController extends Controller
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

        $filters = $request->only(['start_date', 'end_date', 'source', 'status', 'supplier', 'search']);
        $filters['source'] = strtolower($request->input('source', 'all'));

        $combinedData = $this->getCombinedPurchaseRecords($ddcId, $request);
        $records = $combinedData['records'];

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

        // Summary Stats
        $summaryStats = [
            'total_count'          => $records->count(),
            'total_items_qty'      => $records->sum('total_items'),
            'total_amount'         => $records->sum('total_amount'),
            'total_central_amount' => $records->where('source_type', 'Central PO')->sum('total_amount'),
            'total_local_amount'   => $records->where('source_type', 'Regional Purchase')->sum('total_amount'),
        ];

        // Fetch Supplier Names for filter dropdown
        $centralSuppliers = PurchaseOrder::where('ddc_id', $ddcId)
            ->whereHas('seller', function($q) { $q->whereNotNull('name'); })
            ->with('seller')
            ->get()
            ->pluck('seller.name')
            ->unique();

        $localSuppliers = DdcLocalPurchase::where('ddc_id', $ddcId)
            ->whereNotNull('supplier_name')
            ->pluck('supplier_name')
            ->unique();

        $suppliers = $centralSuppliers->merge($localSuppliers)->unique()->filter()->values();

        return view('ddc.reports.purchases', compact('paginatedRecords', 'summaryStats', 'suppliers', 'filters'));
    }

    public function exportCsv(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $filters = $request->only(['start_date', 'end_date', 'source', 'status', 'supplier', 'search']);
        $combinedData = $this->getCombinedPurchaseRecords($ddcId, $request);
        $records = $combinedData['records'];

        $sourceLabel = strtoupper($request->input('source', 'ALL'));
        $filename = 'ddc_purchase_report_' . strtolower($sourceLabel) . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($records, $sourceLabel) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['', "PURCHASE REPORT ({$sourceLabel} SCOPE)"]);
            fputcsv($file, []);

            // Add CSV Header Row
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
                    $row['total_items'],
                    number_format($row['subtotal'], 2, '.', ''),
                    number_format($row['tax_amount'], 2, '.', ''),
                    number_format($row['total_amount'], 2, '.', ''),
                    $row['status'],
                    $row['payment_status'],
                ]);
            }

            fputcsv($file, []);
            fputcsv($file, ['SUMMARY TOTALS (' . $sourceLabel . ')']);
            fputcsv($file, ['Total Purchase Records', $records->count()]);
            fputcsv($file, ['Total Quantity Purchased', number_format($records->sum('total_items'), 3, '.', '')]);
            fputcsv($file, ['Total Purchase Value (₹)', number_format($records->sum('total_amount'), 2, '.', '')]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function print(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $combinedData = $this->getCombinedPurchaseRecords($ddcId, $request);
        $records = $combinedData['records'];
        $filters = $request->only(['start_date', 'end_date', 'source', 'status', 'supplier', 'search']);

        $summaryStats = [
            'total_count'          => $records->count(),
            'total_items_qty'      => $records->sum('total_items'),
            'total_amount'         => $records->sum('total_amount'),
            'total_central_amount' => $records->where('source_type', 'Central PO')->sum('total_amount'),
            'total_local_amount'   => $records->where('source_type', 'Regional Purchase')->sum('total_amount'),
        ];

        return view('ddc.reports.purchases-print', compact('records', 'summaryStats', 'filters'));
    }

    protected function getCombinedPurchaseRecords($ddcId, Request $request): array
    {
        $startDate = $request->get('start_date');
        $endDate   = $request->get('end_date');
        $source    = strtolower($request->get('source', 'all'));
        $status    = $request->get('status');
        $supplier  = $request->get('supplier');
        $search    = $request->get('search');

        if ($source === 'msme') $source = 'central';
        if ($source === 'regional') $source = 'local';

        $records = collect();

        // 1. Central Purchase Orders (Vendor POs / MSME)
        if ($source === 'all' || $source === 'central') {
            $poQuery = PurchaseOrder::where('ddc_id', $ddcId)->with(['seller', 'items', 'invoice']);

            if ($startDate) {
                $poQuery->whereDate('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $poQuery->whereDate('created_at', '<=', $endDate);
            }
            if ($status) {
                $poQuery->where('status', $status);
            }
            if ($supplier) {
                $poQuery->whereHas('seller', function($q) use ($supplier) {
                    $q->where('name', 'like', "%{$supplier}%");
                });
            }
            if ($search) {
                $poQuery->where(function($q) use ($search) {
                    $q->where('po_number', 'like', "%{$search}%")
                      ->orWhereHas('seller', function($sq) use ($search) {
                          $sq->where('name', 'like', "%{$search}%");
                      })
                      ->orWhereHas('items', function($iq) use ($search) {
                          $iq->where('product_name', 'like', "%{$search}%")
                             ->orWhere('product_code', 'like', "%{$search}%");
                      });
                });
            }

            $pos = $poQuery->get();
            foreach ($pos as $po) {
                $itemCount = $po->items->sum('quantity');
                $records->push([
                    'id'             => $po->id,
                    'type'           => 'central',
                    'source_type'    => 'Central PO',
                    'reference_no'   => $po->po_number,
                    'date'           => $po->created_at ? $po->created_at->format('Y-m-d') : '—',
                    'created_at'     => $po->created_at,
                    'supplier_name'  => $po->seller?->name ?: 'Vendor',
                    'invoice_no'     => $po->invoice?->invoice_number ?: 'N/A',
                    'total_items'    => $itemCount,
                    'subtotal'       => (float) $po->total_amount,
                    'tax_amount'     => 0.00,
                    'total_amount'   => (float) $po->total_amount,
                    'status'         => (string) $po->status,
                    'payment_status' => (string) $po->payment_status,
                    'view_url'       => route('ddc.purchase-orders.show', $po->id),
                ]);
            }
        }

        // 2. Regional / Local Purchases
        if ($source === 'all' || $source === 'local') {
            $localQuery = DdcLocalPurchase::where('ddc_id', $ddcId)->with(['items.localProduct']);

            if ($startDate) {
                $localQuery->whereDate('purchase_date', '>=', $startDate);
            }
            if ($endDate) {
                $localQuery->whereDate('purchase_date', '<=', $endDate);
            }
            if ($supplier) {
                $localQuery->where('supplier_name', 'like', "%{$supplier}%");
            }
            if ($search) {
                $localQuery->where(function($q) use ($search) {
                    $q->where('purchase_no', 'like', "%{$search}%")
                      ->orWhere('invoice_number', 'like', "%{$search}%")
                      ->orWhere('supplier_name', 'like', "%{$search}%")
                      ->orWhereHas('items.localProduct', function($lq) use ($search) {
                          $lq->where('product_name', 'like', "%{$search}%")
                             ->orWhere('local_product_code', 'like', "%{$search}%");
                      });
                });
            }

            $localPurchases = $localQuery->get();
            foreach ($localPurchases as $lp) {
                $itemCount = $lp->items->sum('quantity');
                $records->push([
                    'id'             => $lp->id,
                    'type'           => 'local',
                    'source_type'    => 'Regional Purchase',
                    'reference_no'   => $lp->purchase_no,
                    'date'           => $lp->purchase_date ? $lp->purchase_date->format('Y-m-d') : ($lp->created_at ? $lp->created_at->format('Y-m-d') : '—'),
                    'created_at'     => $lp->created_at ?: $lp->purchase_date,
                    'supplier_name'  => $lp->supplier_name ?: 'Local Supplier',
                    'invoice_no'     => $lp->invoice_number ?: 'N/A',
                    'total_items'    => $itemCount,
                    'subtotal'       => (float) $lp->total_amount,
                    'tax_amount'     => 0.00,
                    'total_amount'   => (float) $lp->total_amount,
                    'status'         => 'Stock In',
                    'payment_status' => 'Paid',
                    'view_url'       => route('ddc.local-purchases.show', $lp->id),
                ]);
            }
        }

        // 3. Excess Stock Invoices
        if ($source === 'all' || $source === 'central' || $source === 'excess') {
            $excessInvQuery = \App\Models\Invoice::where('type', 'excess_stock_ddc')
                ->whereHas('purchaseOrder', function($q) use ($ddcId) {
                    $q->where('ddc_id', $ddcId);
                })
                ->with(['purchaseOrder.seller', 'purchaseOrder.items.product']);

            if ($startDate) {
                $excessInvQuery->whereDate('created_at', '>=', $startDate);
            }
            if ($endDate) {
                $excessInvQuery->whereDate('created_at', '<=', $endDate);
            }
            if ($supplier) {
                $excessInvQuery->whereHas('purchaseOrder.seller', function($q) use ($supplier) {
                    $q->where('name', 'like', "%{$supplier}%")->orWhere('company_name', 'like', "%{$supplier}%");
                });
            }
            if ($search) {
                $excessInvQuery->where(function($q) use ($search) {
                    $q->where('invoice_number', 'like', "%{$search}%")
                      ->orWhere('ddc_invoice_number', 'like', "%{$search}%")
                      ->orWhereHas('purchaseOrder', function($poQ) use ($search) {
                          $poQ->where('po_number', 'like', "%{$search}%");
                      });
                });
            }

            $excessInvoices = $excessInvQuery->get();
            foreach ($excessInvoices as $eInv) {
                $po = $eInv->purchaseOrder;
                if (!$po) continue;
                $excessItems = $po->items->filter(fn($i) => $i->excess_qty > 0);
                $totalItemsQty = $excessItems->sum('excess_qty');
                $totalAmount = $excessItems->sum(fn($i) => $i->excess_qty * (float)$i->price);

                $records->push([
                    'id'             => 'exc_' . $eInv->id,
                    'type'           => 'excess',
                    'source_type'    => 'Excess Stock Invoice',
                    'reference_no'   => $eInv->invoice_number ?: ($eInv->ddc_invoice_number ?: 'INV-EXC'),
                    'date'           => $eInv->created_at ? $eInv->created_at->format('Y-m-d') : '—',
                    'created_at'     => $eInv->created_at,
                    'supplier_name'  => $po->seller ? ($po->seller->company_name ?: $po->seller->name) : 'Vendor',
                    'invoice_no'     => $eInv->invoice_number ?: 'N/A',
                    'total_items'    => $totalItemsQty,
                    'subtotal'       => (float) $totalAmount,
                    'tax_amount'     => 0.00,
                    'total_amount'   => (float) $totalAmount,
                    'status'         => 'Completed',
                    'payment_status' => 'Paid (Wallet)',
                    'view_url'       => route('ddc.excess-invoices.show', $eInv->id),
                ]);
            }
        }

        // Sort descending by date
        $sortedRecords = $records->sortByDesc(function ($item) {
            return $item['created_at'] ? $item['created_at']->timestamp : 0;
        })->values();

        return ['records' => $sortedRecords];
    }
}
