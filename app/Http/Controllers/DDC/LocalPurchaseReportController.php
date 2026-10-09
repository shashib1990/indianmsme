<?php

namespace App\Http\Controllers\DDC;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\DdcLocalProduct;
use App\Models\DdcLocalPurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocalPurchaseReportController extends Controller
{
    public function index(Request $request)
    {
        $data = $this->getReportQuery($request);
        $items = $data['query']->paginate(20)->withQueryString();

        $ddcId = Auth::guard('ddc')->id();
        $categories = Category::active()->orderBy('name')->get();
        $brands = Brand::active()->orderBy('name')->get();
        $localProducts = DdcLocalProduct::where('ddc_id', $ddcId)->orderBy('product_name')->get();
        $suppliers = \App\Models\DdcLocalPurchase::where('ddc_id', $ddcId)
            ->whereNotNull('supplier_name')
            ->distinct()
            ->pluck('supplier_name');

        $filters = $request->only(['start_date', 'end_date', 'supplier_name', 'local_product_id', 'category_id', 'brand_id']);

        return view('ddc.reports.local-purchases', compact('items', 'categories', 'brands', 'localProducts', 'suppliers', 'filters'));
    }

    public function exportCsv(Request $request)
    {
        $data = $this->getReportQuery($request);
        $items = $data['query']->get();

        $filename = 'local_purchase_report_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($items) {
            $file = fopen('php://output', 'w');

            // Add Header Row
            fputcsv($file, [
                'Purchase No',
                'Purchase Date',
                'Supplier Name',
                'Invoice No',
                'Invoice Date',
                'Product Name',
                'Product Code',
                'Category',
                'Brand',
                'Quantity',
                'Unit Price (₹)',
                'Landing Cost (₹)',
                'MRP (₹)',
                'CSP (₹)',
                'MSP (₹)',
                'GST Rate (%)',
                'Total Amount (₹)',
                'Current Available Stock',
                'Inventory Source',
                'Batch Number',
            ]);

            foreach ($items as $item) {
                $purchase = $item->purchase;
                $product = $item->localProduct;
                $inventory = $item->inventory;

                fputcsv($file, [
                    $purchase->purchase_no ?? '—',
                    $purchase->purchase_date ? $purchase->purchase_date->format('Y-m-d') : '—',
                    $purchase->supplier_name ?? '—',
                    $purchase->invoice_number ?? '—',
                    $purchase->invoice_date ? $purchase->invoice_date->format('Y-m-d') : '—',
                    $product->product_name ?? '—',
                    $product->local_product_code ?? '—',
                    $product?->category?->name ?? '—',
                    $product?->brand?->name ?? '—',
                    $item->quantity,
                    $item->purchase_price,
                    $item->landing_cost,
                    $item->mrp,
                    $item->csp,
                    $item->msp,
                    $item->gst_rate . '%',
                    ($item->purchase_price * $item->quantity) + $item->freight_share + $item->loading_share + $item->other_share,
                    $inventory->quantity ?? 0,
                    'LOCAL',
                    $item->batch_number ?? '—',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function print(Request $request)
    {
        $data = $this->getReportQuery($request);
        $items = $data['query']->get();
        $filters = $request->only(['start_date', 'end_date', 'supplier_name', 'local_product_id', 'category_id', 'brand_id']);

        return view('ddc.reports.local-purchases-print', compact('items', 'filters'));
    }

    protected function getReportQuery(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();

        $query = DdcLocalPurchaseItem::whereHas('purchase', function ($q) use ($ddcId, $request) {
            $q->where('ddc_id', $ddcId);

            if ($startDate = $request->get('start_date')) {
                $q->whereDate('purchase_date', '>=', $startDate);
            }
            if ($endDate = $request->get('end_date')) {
                $q->whereDate('purchase_date', '<=', $endDate);
            }
            if ($supplier = $request->get('supplier_name')) {
                $q->where('supplier_name', $supplier);
            }
        })->with(['purchase', 'localProduct.category', 'localProduct.brand', 'inventory']);

        if ($productId = $request->get('local_product_id')) {
            $query->where('ddc_local_product_id', $productId);
        }

        if ($categoryId = $request->get('category_id')) {
            $query->whereHas('localProduct', function ($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            });
        }

        if ($brandId = $request->get('brand_id')) {
            $query->whereHas('localProduct', function ($q) use ($brandId) {
                $q->where('brand_id', $brandId);
            });
        }

        $query->orderBy('id', 'desc');

        return ['query' => $query];
    }
}
