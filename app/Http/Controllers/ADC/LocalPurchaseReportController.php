<?php

namespace App\Http\Controllers\ADC;

use App\Http\Controllers\Controller;
use App\Models\AdcLocalPurchase;
use App\Models\AdcLocalPurchaseItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocalPurchaseReportController extends Controller
{
    public function index(Request $request)
    {
        $adcId = Auth::guard('adc')->id();
        $search = trim($request->get('search', ''));
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = AdcLocalPurchase::where('adc_id', $adcId)
            ->with(['items.localProduct.category', 'items.localProduct.brand']);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('purchase_no', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%")
                  ->orWhere('invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('items.localProduct', function ($lq) use ($search) {
                      $lq->where('product_name', 'like', "%{$search}%")
                        ->orWhere('local_product_code', 'like', "%{$search}%");
                  });
            });
        }

        if ($startDate) {
            $query->whereDate('purchase_date', '>=', $startDate);
        }

        if ($endDate) {
            $query->whereDate('purchase_date', '<=', $endDate);
        }

        $purchases = $query->latest('purchase_date')->latest('id')->paginate(15)->withQueryString();

        $totalPurchasesCount = (clone $query)->count();
        $totalAmountSum = (clone $query)->sum('total_amount');
        $totalItemsCount = AdcLocalPurchaseItem::whereHas('purchase', function ($pq) use ($adcId) {
            $pq->where('adc_id', $adcId);
        })->sum('quantity');

        return view('adc.reports.local-purchases', compact(
            'purchases',
            'search',
            'startDate',
            'endDate',
            'totalPurchasesCount',
            'totalAmountSum',
            'totalItemsCount'
        ));
    }

    public function exportCsv(Request $request)
    {
        $adcId = Auth::guard('adc')->id();
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = AdcLocalPurchase::where('adc_id', $adcId)
            ->with(['items.localProduct']);

        if ($startDate) $query->whereDate('purchase_date', '>=', $startDate);
        if ($endDate) $query->whereDate('purchase_date', '<=', $endDate);

        $purchases = $query->latest('purchase_date')->get();

        $filename = 'ADC_Local_Purchases_Report_' . date('Y-m-d') . '.csv';

        $headers = [
            'Content-type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($purchases) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Purchase No', 'Purchase Date', 'Supplier Name', 'Supplier Mobile', 'Invoice No', 'Items Count', 'Total Amount (Rs)', 'Remarks']);

            foreach ($purchases as $p) {
                fputcsv($file, [
                    $p->purchase_no,
                    $p->purchase_date ? $p->purchase_date->format('d/m/Y') : '—',
                    $p->supplier_name,
                    $p->supplier_mobile ?: '—',
                    $p->invoice_number ?: '—',
                    $p->items->count(),
                    number_format($p->total_amount, 2),
                    $p->remarks ?: '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function print(Request $request)
    {
        $adcId = Auth::guard('adc')->id();
        $startDate = $request->get('start_date');
        $endDate = $request->get('end_date');

        $query = AdcLocalPurchase::where('adc_id', $adcId)
            ->with(['items.localProduct']);

        if ($startDate) $query->whereDate('purchase_date', '>=', $startDate);
        if ($endDate) $query->whereDate('purchase_date', '<=', $endDate);

        $purchases = $query->latest('purchase_date')->get();

        return view('adc.reports.local-purchases-print', compact('purchases', 'startDate', 'endDate'));
    }
}
