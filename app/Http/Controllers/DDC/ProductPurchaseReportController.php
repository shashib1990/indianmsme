<?php

namespace App\Http\Controllers\DDC;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\DdcLocalPurchase;
use App\Models\DdcLocalPurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;

class ProductPurchaseReportController extends Controller
{
    public function index(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $filters = $request->only(['start_date', 'end_date', 'source', 'supplier', 'search']);

        $combinedData = $this->getCombinedProductPurchaseRecords($ddcId, $request);
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
            'total_items_qty'      => $records->sum('quantity'),
            'total_amount'         => $records->sum('total_amount'),
            'total_central_amount' => $records->where('type', 'central')->sum('total_amount'),
            'total_local_amount'   => $records->where('type', 'local')->sum('total_amount'),
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

        return view('ddc.reports.product-purchases', compact('paginatedRecords', 'summaryStats', 'suppliers', 'filters'));
    }

    public function exportCsv(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $combinedData = $this->getCombinedProductPurchaseRecords($ddcId, $request);
        $records = $combinedData['records'];

        $filename = 'ddc_product_purchase_report_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($records) {
            $file = fopen('php://output', 'w');
            fputcsv($file, [
                '#',
                'Product Name',
                'Product Code',
                'HSN Code',
                'Source Type',
                'Purchase / PO No',
                'Date',
                'Supplier / Vendor',
                'Invoice No',
                'Qty Purchased',
                'Unit of Measure',
                'Purchase Price (₹)',
                'Landing Cost (₹)',
                'MRP (₹)',
                'Total Purchase Cost (₹)',
            ]);

            foreach ($records as $idx => $row) {
                fputcsv($file, [
                    $idx + 1,
                    $row['product_name'],
                    $row['product_code'],
                    $row['hsn_code'],
                    $row['source_type'],
                    $row['reference_no'],
                    $row['date'],
                    $row['supplier_name'],
                    $row['invoice_no'],
                    $row['quantity'],
                    $row['unit_of_measure'],
                    number_format($row['unit_price'], 2, '.', ''),
                    number_format($row['landing_cost'], 2, '.', ''),
                    number_format($row['mrp'], 2, '.', ''),
                    number_format($row['total_amount'], 2, '.', ''),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function print(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $combinedData = $this->getCombinedProductPurchaseRecords($ddcId, $request);
        $records = $combinedData['records'];

        $summaryStats = [
            'total_count'          => $records->count(),
            'total_items_qty'      => $records->sum('quantity'),
            'total_amount'         => $records->sum('total_amount'),
            'total_central_amount' => $records->where('type', 'central')->sum('total_amount'),
            'total_local_amount'   => $records->where('type', 'local')->sum('total_amount'),
        ];

        $filters = $request->all();

        return view('ddc.reports.product-purchases-print', compact('records', 'summaryStats', 'filters'));
    }

    private function getCombinedProductPurchaseRecords($ddcId, Request $request)
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $source    = $request->input('source', 'all'); // all, central, local
        $supplier  = $request->input('supplier');
        $search    = $request->input('search');

        $records = collect();

        // 1. Central Vendor PO Items
        if (in_array($source, ['all', 'central'])) {
            $poQuery = PurchaseOrderItem::whereHas('purchaseOrder', function($q) use ($ddcId, $startDate, $endDate) {
                $q->where('ddc_id', $ddcId);
                if ($startDate) {
                    $q->whereDate('created_at', '>=', $startDate);
                }
                if ($endDate) {
                    $q->whereDate('created_at', '<=', $endDate);
                }
            })->with(['purchaseOrder.seller', 'product']);

            if ($supplier) {
                $poQuery->whereHas('purchaseOrder.seller', function($sq) use ($supplier) {
                    $sq->where('name', $supplier)
                       ->orWhereHas('sellerProfile', function($spq) use ($supplier) {
                           $spq->where('company_name', $supplier);
                       });
                });
            }

            if ($search) {
                $poQuery->where(function($q) use ($search) {
                    $q->where('product_name', 'like', "%{$search}%")
                      ->orWhere('product_code', 'like', "%{$search}%")
                      ->orWhereHas('product', function($pQ) use ($search) {
                          $pQ->where('hsn_code', 'like', "%{$search}%");
                      })
                      ->orWhereHas('purchaseOrder', function($poQ) use ($search) {
                          $poQ->where('po_number', 'like', "%{$search}%");
                      });
                });
            }

            $poItems = $poQuery->get();

            foreach ($poItems as $item) {
                $po = $item->purchaseOrder;
                if (!$po) continue;

                $supplierName = $po->seller ? ($po->seller->company_name ?: $po->seller->name) : 'Central Vendor';
                $hsnCode = $item->product ? ($item->product->hsn_code ?: '—') : '—';
                $qty = (float) ($item->supply_qty > 0 ? $item->supply_qty : $item->quantity);
                $unitPrice = (float) $item->price;
                $totalAmount = (float) ($item->total > 0 ? $item->total : ($qty * $unitPrice));

                $records->push([
                    'id'               => 'cpo_' . $item->id,
                    'type'             => 'central',
                    'source_type'      => 'Central PO',
                    'reference_no'     => $po->po_number,
                    'invoice_no'       => $po->po_number,
                    'date'             => $po->created_at->format('d M Y'),
                    'created_at'       => $po->created_at,
                    'supplier_name'    => $supplierName,
                    'product_name'     => $item->product_name ?: ($item->product?->product_name ?? 'N/A'),
                    'product_code'     => $item->product_code ?: ($item->product?->product_code ?? '—'),
                    'hsn_code'         => $hsnCode,
                    'unit_of_measure'  => $item->unit_of_measure ?: 'Pcs',
                    'quantity'         => $qty,
                    'unit_price'       => $unitPrice,
                    'landing_cost'     => $unitPrice,
                    'mrp'              => (float) ($item->actual_mrp ?: ($item->received_mrp ?: 0)),
                    'csp'              => 0,
                    'msp'              => 0,
                    'total_amount'     => $totalAmount,
                    'view_url'         => route('ddc.purchase-orders.show', $po->id),
                ]);
            }
        }

        // 2. Regional / Local Purchase Items
        if (in_array($source, ['all', 'local'])) {
            $localQuery = DdcLocalPurchaseItem::whereHas('purchase', function($q) use ($ddcId, $startDate, $endDate) {
                $q->where('ddc_id', $ddcId);
                if ($startDate) {
                    $q->whereDate('purchase_date', '>=', $startDate);
                }
                if ($endDate) {
                    $q->whereDate('purchase_date', '<=', $endDate);
                }
            })->with(['purchase', 'localProduct']);

            if ($supplier) {
                $localQuery->whereHas('purchase', function($pq) use ($supplier) {
                    $pq->where('supplier_name', $supplier);
                });
            }

            if ($search) {
                $localQuery->where(function($q) use ($search) {
                    $q->whereHas('localProduct', function($lpQ) use ($search) {
                        $lpQ->where('product_name', 'like', "%{$search}%")
                            ->orWhere('local_product_code', 'like', "%{$search}%")
                            ->orWhere('hsn_code', 'like', "%{$search}%");
                    })->orWhereHas('purchase', function($purQ) use ($search) {
                        $purQ->where('purchase_no', 'like', "%{$search}%")
                             ->orWhere('invoice_number', 'like', "%{$search}%")
                             ->orWhere('supplier_name', 'like', "%{$search}%");
                    });
                });
            }

            $localItems = $localQuery->get();

            foreach ($localItems as $item) {
                $purchase = $item->purchase;
                if (!$purchase) continue;

                $lp = $item->localProduct;
                $supplierName = $purchase->supplier_name ?: 'Local Supplier';
                $hsnCode = $lp ? ($lp->hsn_code ?: '—') : '—';
                $qty = (float) $item->quantity;
                $unitPrice = (float) $item->purchase_price;
                $landingCost = (float) $item->landing_cost;
                $totalCost = ($unitPrice * $qty) + (float)$item->freight_share + (float)$item->loading_share + (float)$item->other_share;

                $records->push([
                    'id'               => 'loc_' . $item->id,
                    'type'             => 'local',
                    'source_type'      => 'Regional Purchase',
                    'reference_no'     => $purchase->purchase_no ?: '—',
                    'invoice_no'       => $purchase->invoice_number ?: '—',
                    'date'             => $purchase->purchase_date ? $purchase->purchase_date->format('d M Y') : $purchase->created_at->format('d M Y'),
                    'created_at'       => $purchase->purchase_date ?: $purchase->created_at,
                    'supplier_name'    => $supplierName,
                    'product_name'     => $lp ? $lp->product_name : 'N/A',
                    'product_code'     => $lp ? $lp->local_product_code : '—',
                    'hsn_code'         => $hsnCode,
                    'unit_of_measure'  => $lp ? ($lp->unit_of_measure ?: 'Pcs') : 'Pcs',
                    'quantity'         => $qty,
                    'unit_price'       => $unitPrice,
                    'landing_cost'     => $landingCost,
                    'mrp'              => (float) $item->mrp,
                    'csp'              => (float) $item->csp,
                    'msp'              => (float) $item->msp,
                    'total_amount'     => $totalCost,
                    'view_url'         => route('ddc.local-purchases.show', $purchase->id),
                ]);
            }
        }

        // 3. Excess Stock Items
        if (in_array($source, ['all', 'central', 'excess'])) {
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

            $excessInvoices = $excessInvQuery->get();
            foreach ($excessInvoices as $eInv) {
                $po = $eInv->purchaseOrder;
                if (!$po) continue;
                $supplierName = $po->seller ? ($po->seller->company_name ?: $po->seller->name) : 'Vendor';

                foreach ($po->items as $item) {
                    if ($item->excess_qty <= 0) continue;

                    $hsnCode = $item->product ? ($item->product->hsn_code ?: '—') : '—';
                    $qty = (float) $item->excess_qty;
                    $unitPrice = (float) $item->price;
                    $totalAmount = $qty * $unitPrice;

                    if ($supplier && !str_contains(strtolower($supplierName), strtolower($supplier))) {
                        continue;
                    }
                    if ($search) {
                        $pName = $item->product_name ?: ($item->product?->product_name ?? '');
                        $pCode = $item->product_code ?: ($item->product?->product_code ?? '');
                        if (!str_contains(strtolower($pName), strtolower($search)) && 
                            !str_contains(strtolower($pCode), strtolower($search)) && 
                            !str_contains(strtolower($hsnCode), strtolower($search))) {
                            continue;
                        }
                    }

                    $records->push([
                        'id'               => 'exc_item_' . $item->id,
                        'type'             => 'central',
                        'source_type'      => 'Excess Stock Invoice',
                        'reference_no'     => $eInv->invoice_number ?: $po->po_number,
                        'invoice_no'       => $eInv->invoice_number ?: 'N/A',
                        'date'             => $eInv->created_at->format('d M Y'),
                        'created_at'       => $eInv->created_at,
                        'supplier_name'    => $supplierName,
                        'product_name'     => ($item->product_name ?: ($item->product?->product_name ?? 'N/A')) . ' (Excess)',
                        'product_code'     => $item->product_code ?: ($item->product?->product_code ?? '—'),
                        'hsn_code'         => $hsnCode,
                        'unit_of_measure'  => $item->unit_of_measure ?: 'Pcs',
                        'quantity'         => $qty,
                        'unit_price'       => $unitPrice,
                        'landing_cost'     => $unitPrice,
                        'mrp'              => (float) ($item->actual_mrp ?: ($item->received_mrp ?: 0)),
                        'csp'              => 0,
                        'msp'              => 0,
                        'total_amount'     => $totalAmount,
                        'view_url'         => route('ddc.excess-invoices.show', $eInv->id),
                    ]);
                }
            }
        }

        // Sort descending by date
        $sortedRecords = $records->sortByDesc(function ($item) {
            return $item['created_at'] ? $item['created_at']->timestamp : 0;
        })->values();

        return [
            'records' => $sortedRecords,
        ];
    }
}
