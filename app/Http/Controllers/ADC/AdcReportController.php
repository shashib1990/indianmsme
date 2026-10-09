<?php

namespace App\Http\Controllers\ADC;

use App\Http\Controllers\Controller;
use App\Models\AdcPosOrder;
use App\Models\AdcPosOrderItem;
use App\Models\AdcLocalPurchase;
use App\Models\AdcLocalPurchaseItem;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class AdcReportController extends Controller
{
    /**
     * Helper to get active ADC user ID
     */
    private function getAdcId()
    {
        return Auth::guard('adc')->id() ?: (Auth::id() ?: 0);
    }

    /**
     * Helper to get date filters safely
     */
    private function parseDates(Request $request)
    {
        try {
            $fromDate = $request->filled('from_date') 
                ? Carbon::parse($request->from_date)->startOfDay() 
                : Carbon::now()->startOfMonth()->startOfDay();
        } catch (\Throwable $e) {
            $fromDate = Carbon::now()->startOfMonth()->startOfDay();
        }

        try {
            $toDate = $request->filled('to_date') 
                ? Carbon::parse($request->to_date)->endOfDay() 
                : Carbon::now()->endOfDay();
        } catch (\Throwable $e) {
            $toDate = Carbon::now()->endOfDay();
        }

        return [$fromDate, $toDate];
    }

    /**
     * 1. Invoice Wise Report
     */
    public function invoiceWise(Request $request)
    {
        $adcId = $this->getAdcId();
        [$fromDate, $toDate] = $this->parseDates($request);

        $query = AdcPosOrder::with(['invoice', 'customer', 'items'])
            ->where('adc_id', $adcId)
            ->whereBetween('created_at', [$fromDate, $toDate]);

        if ($request->filled('payment_mode')) {
            $query->where('payment_mode', strtolower($request->payment_mode));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_no', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_mobile', 'like', "%{$search}%");
            });
        }

        $allOrders = (clone $query)->get();

        $totalRevenue = $allOrders->sum(function ($ord) {
            return (float) $ord->total_amount - (float) ($ord->total_returned_amount ?? 0);
        });

        $cashSales = $allOrders->where('payment_mode', 'cash')->sum(function ($ord) {
            return (float) $ord->total_amount - (float) ($ord->total_returned_amount ?? 0);
        });

        $creditSales = $allOrders->where('payment_mode', 'credit')->sum(function ($ord) {
            return (float) $ord->total_amount - (float) ($ord->total_returned_amount ?? 0);
        });

        $totalInvoicesCount = $allOrders->count();

        $orders = $query->latest()->paginate(20)->withQueryString();

        return view('adc.reports.invoice_wise', compact(
            'orders', 'totalRevenue', 'cashSales', 'creditSales', 'totalInvoicesCount', 'fromDate', 'toDate'
        ));
    }

    public function printInvoiceWise(Request $request)
    {
        $adcId = $this->getAdcId();
        [$fromDate, $toDate] = $this->parseDates($request);

        $query = AdcPosOrder::with(['customer', 'items'])
            ->where('adc_id', $adcId)
            ->whereBetween('created_at', [$fromDate, $toDate]);

        if ($request->filled('payment_mode')) {
            $query->where('payment_mode', strtolower($request->payment_mode));
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('order_no', 'like', "%{$search}%")
                  ->orWhere('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_mobile', 'like', "%{$search}%");
            });
        }

        $orders = $query->latest()->get();

        $totalRevenue = $orders->sum(function ($ord) {
            return (float) $ord->total_amount - (float) ($ord->total_returned_amount ?? 0);
        });
        $cashSales = $orders->where('payment_mode', 'cash')->sum(function ($ord) {
            return (float) $ord->total_amount - (float) ($ord->total_returned_amount ?? 0);
        });
        $creditSales = $orders->where('payment_mode', 'credit')->sum(function ($ord) {
            return (float) $ord->total_amount - (float) ($ord->total_returned_amount ?? 0);
        });

        return view('adc.reports.invoice_wise_print', compact(
            'orders', 'totalRevenue', 'cashSales', 'creditSales', 'fromDate', 'toDate'
        ));
    }

    public function exportInvoiceWiseCsv(Request $request)
    {
        $adcId = $this->getAdcId();
        [$fromDate, $toDate] = $this->parseDates($request);

        $orders = AdcPosOrder::with(['customer', 'items'])
            ->where('adc_id', $adcId)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->latest()
            ->get();

        $filename = "Invoice_Wise_Report_" . date('Y-m-d') . ".csv";

        return response()->streamDownload(function () use ($orders) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Invoice No', 'Customer Name', 'Mobile', 'Date & Time', 'Payment Mode', 'Subtotal (INR)', 'Discount (INR)', 'Net Total (INR)']);

            foreach ($orders as $ord) {
                $net = (float) $ord->total_amount - (float) ($ord->total_returned_amount ?? 0);
                fputcsv($handle, [
                    $ord->order_no,
                    $ord->customer_name ?: ($ord->customer?->name ?: 'Walk-in Customer'),
                    $ord->customer_mobile ?: ($ord->customer?->mobile ?: 'N/A'),
                    $ord->created_at->format('Y-m-d H:i:s'),
                    strtoupper($ord->payment_mode ?? 'CASH'),
                    number_format($ord->subtotal ?? 0, 2, '.', ''),
                    number_format($ord->discount_amount ?? 0, 2, '.', ''),
                    number_format($net, 2, '.', '')
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * 2. Product Wise Report
     */
    public function productWise(Request $request)
    {
        $adcId = $this->getAdcId();
        [$fromDate, $toDate] = $this->parseDates($request);

        $orders = AdcPosOrder::with(['items.inventory.product.category', 'items.inventory.localProduct.category'])
            ->where('adc_id', $adcId)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->get();

        $itemGroups = [];
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $barcode = $item->barcode ?: ($item->product_code ?: ('ITEM-' . $item->id));
                
                if (!isset($itemGroups[$barcode])) {
                    $catName = 'General';
                    if (!empty($item->inventory?->product?->category?->name)) {
                        $catName = $item->inventory->product->category->name;
                    } elseif (!empty($item->inventory?->localProduct?->category?->name)) {
                        $catName = $item->inventory->localProduct->category->name;
                    }

                    $itemGroups[$barcode] = (object) [
                        'barcode' => $barcode,
                        'product_name' => $item->product_name ?: 'Unnamed Product',
                        'category' => $catName,
                        'total_qty' => 0,
                        'returned_qty' => 0,
                        'total_sales' => 0.00,
                        'total_cost' => 0.00,
                    ];
                }

                $qty = (int) $item->quantity;
                $retQty = (int) ($item->returned_quantity ?? 0);
                $netQty = max(0, $qty - $retQty);

                $unitPrice = (float) ($item->unit_price > 0 ? $item->unit_price : ($qty > 0 ? $item->total_price / $qty : 0));
                $inv = $item->inventory;
                $unitCost = $inv ? ((float) ($inv->purchase_price > 0 ? $inv->purchase_price : ($inv->landing_cost > 0 ? $inv->landing_cost : ($inv->adc_price > 0 ? $inv->adc_price : $unitPrice)))) : $unitPrice;

                $itemGroups[$barcode]->total_qty += $qty;
                $itemGroups[$barcode]->returned_qty += $retQty;
                $itemGroups[$barcode]->total_sales += round($netQty * $unitPrice, 2);
                $itemGroups[$barcode]->total_cost += round($netQty * $unitCost, 2);
            }
        }

        $groupedProducts = collect($itemGroups)->map(function ($g) {
            $g->net_qty = max(0, $g->total_qty - $g->returned_qty);
            $g->total_profit = round($g->total_sales - $g->total_cost, 2);
            return $g;
        })->values();

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $groupedProducts = $groupedProducts->filter(function ($g) use ($search) {
                return str_contains(strtolower($g->product_name), $search) || str_contains(strtolower($g->barcode), $search);
            })->values();
        }

        $totalProductsSold = $groupedProducts->count();
        $totalUnitsSold = $groupedProducts->sum('net_qty');
        $totalProductSales = $groupedProducts->sum('total_sales');
        $totalProductProfit = $groupedProducts->sum('total_profit');

        return view('adc.reports.product_wise', compact(
            'groupedProducts', 'totalProductsSold', 'totalUnitsSold', 'totalProductSales', 'totalProductProfit', 'fromDate', 'toDate'
        ));
    }

    public function printProductWise(Request $request)
    {
        $adcId = $this->getAdcId();
        [$fromDate, $toDate] = $this->parseDates($request);

        $orders = AdcPosOrder::with(['items.inventory.product.category', 'items.inventory.localProduct.category'])
            ->where('adc_id', $adcId)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->get();

        $itemGroups = [];
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $barcode = $item->barcode ?: ($item->product_code ?: ('ITEM-' . $item->id));
                
                if (!isset($itemGroups[$barcode])) {
                    $catName = 'General';
                    if (!empty($item->inventory?->product?->category?->name)) {
                        $catName = $item->inventory->product->category->name;
                    } elseif (!empty($item->inventory?->localProduct?->category?->name)) {
                        $catName = $item->inventory->localProduct->category->name;
                    }

                    $itemGroups[$barcode] = (object) [
                        'barcode' => $barcode,
                        'product_name' => $item->product_name ?: 'Unnamed Product',
                        'category' => $catName,
                        'total_qty' => 0,
                        'returned_qty' => 0,
                        'total_sales' => 0.00,
                        'total_cost' => 0.00,
                    ];
                }

                $qty = (int) $item->quantity;
                $retQty = (int) ($item->returned_quantity ?? 0);
                $netQty = max(0, $qty - $retQty);

                $unitPrice = (float) ($item->unit_price > 0 ? $item->unit_price : ($qty > 0 ? $item->total_price / $qty : 0));
                $inv = $item->inventory;
                $unitCost = $inv ? ((float) ($inv->purchase_price > 0 ? $inv->purchase_price : ($inv->landing_cost > 0 ? $inv->landing_cost : ($inv->adc_price > 0 ? $inv->adc_price : $unitPrice)))) : $unitPrice;

                $itemGroups[$barcode]->total_qty += $qty;
                $itemGroups[$barcode]->returned_qty += $retQty;
                $itemGroups[$barcode]->total_sales += round($netQty * $unitPrice, 2);
                $itemGroups[$barcode]->total_cost += round($netQty * $unitCost, 2);
            }
        }

        $groupedProducts = collect($itemGroups)->map(function ($g) {
            $g->net_qty = max(0, $g->total_qty - $g->returned_qty);
            $g->total_profit = round($g->total_sales - $g->total_cost, 2);
            return $g;
        })->values();

        if ($request->filled('search')) {
            $search = strtolower($request->search);
            $groupedProducts = $groupedProducts->filter(function ($g) use ($search) {
                return str_contains(strtolower($g->product_name), $search) || str_contains(strtolower($g->barcode), $search);
            })->values();
        }

        $totalProductsSold = $groupedProducts->count();
        $totalUnitsSold = $groupedProducts->sum('net_qty');
        $totalProductSales = $groupedProducts->sum('total_sales');
        $totalProductProfit = $groupedProducts->sum('total_profit');

        return view('adc.reports.product_wise_print', compact(
            'groupedProducts', 'totalProductsSold', 'totalUnitsSold', 'totalProductSales', 'totalProductProfit', 'fromDate', 'toDate'
        ));
    }

    public function exportProductWiseCsv(Request $request)
    {
        $adcId = $this->getAdcId();
        [$fromDate, $toDate] = $this->parseDates($request);

        $orders = AdcPosOrder::with(['items.inventory.product.category', 'items.inventory.localProduct.category'])
            ->where('adc_id', $adcId)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->get();

        $itemGroups = [];
        foreach ($orders as $order) {
            foreach ($order->items as $item) {
                $barcode = $item->barcode ?: ($item->product_code ?: ('ITEM-' . $item->id));
                
                if (!isset($itemGroups[$barcode])) {
                    $catName = 'General';
                    if (!empty($item->inventory?->product?->category?->name)) {
                        $catName = $item->inventory->product->category->name;
                    } elseif (!empty($item->inventory?->localProduct?->category?->name)) {
                        $catName = $item->inventory->localProduct->category->name;
                    }

                    $itemGroups[$barcode] = (object) [
                        'barcode' => $barcode,
                        'product_name' => $item->product_name ?: 'Unnamed Product',
                        'category' => $catName,
                        'total_qty' => 0,
                        'returned_qty' => 0,
                        'total_sales' => 0.00,
                        'total_cost' => 0.00,
                    ];
                }

                $qty = (int) $item->quantity;
                $retQty = (int) ($item->returned_quantity ?? 0);
                $netQty = max(0, $qty - $retQty);

                $unitPrice = (float) ($item->unit_price > 0 ? $item->unit_price : ($qty > 0 ? $item->total_price / $qty : 0));
                $inv = $item->inventory;
                $unitCost = $inv ? ((float) ($inv->purchase_price > 0 ? $inv->purchase_price : ($inv->landing_cost > 0 ? $inv->landing_cost : ($inv->adc_price > 0 ? $inv->adc_price : $unitPrice)))) : $unitPrice;

                $itemGroups[$barcode]->total_qty += $qty;
                $itemGroups[$barcode]->returned_qty += $retQty;
                $itemGroups[$barcode]->total_sales += round($netQty * $unitPrice, 2);
                $itemGroups[$barcode]->total_cost += round($netQty * $unitCost, 2);
            }
        }

        $items = collect($itemGroups)->map(function ($g) {
            $g->net_qty = max(0, $g->total_qty - $g->returned_qty);
            $g->total_profit = round($g->total_sales - $g->total_cost, 2);
            return $g;
        })->values();

        $filename = "Product_Wise_Report_" . date('Y-m-d') . ".csv";

        return response()->streamDownload(function () use ($items) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Barcode / SKU', 'Product Name', 'Category', 'Total Qty Sold', 'Returned Qty', 'Net Qty Sold', 'Total Revenue (INR)', 'Est. Net Profit (INR)']);

            foreach ($items as $item) {
                fputcsv($handle, [
                    $item->barcode,
                    $item->product_name,
                    $item->category,
                    $item->total_qty,
                    $item->returned_qty,
                    $item->net_qty,
                    number_format($item->total_sales, 2, '.', ''),
                    number_format($item->total_profit, 2, '.', '')
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * 3. Sales Report
     */
    public function salesReport(Request $request)
    {
        $adcId = $this->getAdcId();
        [$fromDate, $toDate] = $this->parseDates($request);

        $orders = AdcPosOrder::where('adc_id', $adcId)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->get();

        $grossSales = $orders->sum('total_amount');
        $returnedAmount = $orders->sum('total_returned_amount');
        $netSales = $grossSales - $returnedAmount;
        $orderCount = $orders->count();
        $totalDiscount = $orders->sum('discount_amount');

        $dailySales = DB::table('adc_pos_orders')
            ->where('adc_id', $adcId)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(id) as orders_count'),
                DB::raw('SUM(total_amount) as gross_sales'),
                DB::raw('SUM(COALESCE(total_returned_amount, 0)) as returned_amt'),
                DB::raw('SUM(CASE WHEN LOWER(payment_mode) = "cash" THEN total_amount ELSE 0 END) as cash_sales'),
                DB::raw('SUM(CASE WHEN LOWER(payment_mode) = "credit" THEN total_amount ELSE 0 END) as credit_sales')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'desc')
            ->get()
            ->map(function($day) {
                $day->net_sales = $day->gross_sales - $day->returned_amt;
                return $day;
            });

        return view('adc.reports.sales', compact(
            'grossSales', 'returnedAmount', 'netSales', 'orderCount', 'totalDiscount', 'dailySales', 'fromDate', 'toDate'
        ));
    }

    public function printSalesReport(Request $request)
    {
        $adcId = $this->getAdcId();
        [$fromDate, $toDate] = $this->parseDates($request);

        $orders = AdcPosOrder::where('adc_id', $adcId)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->get();

        $grossSales = $orders->sum('total_amount');
        $returnedAmount = $orders->sum('total_returned_amount');
        $netSales = $grossSales - $returnedAmount;
        $orderCount = $orders->count();
        $totalDiscount = $orders->sum('discount_amount');

        $dailySales = DB::table('adc_pos_orders')
            ->where('adc_id', $adcId)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(id) as orders_count'),
                DB::raw('SUM(total_amount) as gross_sales'),
                DB::raw('SUM(COALESCE(total_returned_amount, 0)) as returned_amt'),
                DB::raw('SUM(CASE WHEN LOWER(payment_mode) = "cash" THEN total_amount ELSE 0 END) as cash_sales'),
                DB::raw('SUM(CASE WHEN LOWER(payment_mode) = "credit" THEN total_amount ELSE 0 END) as credit_sales')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'desc')
            ->get()
            ->map(function($day) {
                $day->net_sales = $day->gross_sales - $day->returned_amt;
                return $day;
            });

        return view('adc.reports.sales_print', compact(
            'grossSales', 'returnedAmount', 'netSales', 'orderCount', 'totalDiscount', 'dailySales', 'fromDate', 'toDate'
        ));
    }

    public function exportSalesReportCsv(Request $request)
    {
        $adcId = $this->getAdcId();
        [$fromDate, $toDate] = $this->parseDates($request);

        $dailySales = DB::table('adc_pos_orders')
            ->where('adc_id', $adcId)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(id) as orders_count'),
                DB::raw('SUM(total_amount) as gross_sales'),
                DB::raw('SUM(COALESCE(total_returned_amount, 0)) as returned_amt'),
                DB::raw('SUM(CASE WHEN LOWER(payment_mode) = "cash" THEN total_amount ELSE 0 END) as cash_sales'),
                DB::raw('SUM(CASE WHEN LOWER(payment_mode) = "credit" THEN total_amount ELSE 0 END) as credit_sales')
            )
            ->groupBy(DB::raw('DATE(created_at)'))
            ->orderBy('date', 'desc')
            ->get();

        $filename = "Sales_Performance_Report_" . date('Y-m-d') . ".csv";

        return response()->streamDownload(function () use ($dailySales) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Date', 'Invoices Count', 'Gross Sales (INR)', 'Returns (INR)', 'Net Sales (INR)', 'Cash Sales (INR)', 'Credit Sales (INR)']);

            foreach ($dailySales as $day) {
                $net = $day->gross_sales - $day->returned_amt;
                fputcsv($handle, [
                    $day->date,
                    $day->orders_count,
                    number_format($day->gross_sales, 2, '.', ''),
                    number_format($day->returned_amt, 2, '.', ''),
                    number_format($net, 2, '.', ''),
                    number_format($day->cash_sales, 2, '.', ''),
                    number_format($day->credit_sales, 2, '.', '')
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * 4. Purchase Report
     */
    public function purchaseReport(Request $request)
    {
        $adcId = $this->getAdcId();
        [$fromDate, $toDate] = $this->parseDates($request);

        $query = AdcLocalPurchase::with('items')
            ->where('adc_id', $adcId)
            ->whereBetween('created_at', [$fromDate, $toDate]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('purchase_no', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%");
            });
        }

        $allPurchases = (clone $query)->get();

        $totalPurchaseAmount = $allPurchases->sum('total_amount');
        $totalPurchaseOrders = $allPurchases->count();
        $totalItemsPurchased = $allPurchases->sum(function($p) { return $p->items->sum('quantity'); });

        $purchases = $query->latest()->paginate(20)->withQueryString();

        return view('adc.reports.purchases', compact(
            'purchases', 'totalPurchaseAmount', 'totalPurchaseOrders', 'totalItemsPurchased', 'fromDate', 'toDate'
        ));
    }

    public function printPurchaseReport(Request $request)
    {
        $adcId = $this->getAdcId();
        [$fromDate, $toDate] = $this->parseDates($request);

        $query = AdcLocalPurchase::with('items')
            ->where('adc_id', $adcId)
            ->whereBetween('created_at', [$fromDate, $toDate]);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('purchase_no', 'like', "%{$search}%")
                  ->orWhere('supplier_name', 'like', "%{$search}%");
            });
        }

        $purchases = $query->latest()->get();

        $totalPurchaseAmount = $purchases->sum('total_amount');
        $totalPurchaseOrders = $purchases->count();
        $totalItemsPurchased = $purchases->sum(function($p) { return $p->items->sum('quantity'); });

        return view('adc.reports.purchases_print', compact(
            'purchases', 'totalPurchaseAmount', 'totalPurchaseOrders', 'totalItemsPurchased', 'fromDate', 'toDate'
        ));
    }

    public function exportPurchaseReportCsv(Request $request)
    {
        $adcId = $this->getAdcId();
        [$fromDate, $toDate] = $this->parseDates($request);

        $purchases = AdcLocalPurchase::with('items')
            ->where('adc_id', $adcId)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->latest()
            ->get();

        $filename = "Purchase_Report_" . date('Y-m-d') . ".csv";

        return response()->streamDownload(function () use ($purchases) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Purchase No', 'Supplier Name', 'Date', 'Items Count', 'Subtotal (INR)', 'Tax Amount (INR)', 'Total Amount (INR)']);

            foreach ($purchases as $p) {
                fputcsv($handle, [
                    $p->purchase_no,
                    $p->supplier_name ?: 'N/A',
                    $p->created_at->format('Y-m-d H:i:s'),
                    $p->items->count(),
                    number_format($p->subtotal ?? 0, 2, '.', ''),
                    number_format($p->tax_amount ?? 0, 2, '.', ''),
                    number_format($p->total_amount ?? 0, 2, '.', '')
                ]);
            }
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    /**
     * 5. P&L (Profit & Loss) Report
     */
    public function pnlReport(Request $request)
    {
        $adcId = $this->getAdcId();
        [$fromDate, $toDate] = $this->parseDates($request);

        $salesOrders = AdcPosOrder::with('items.inventory')
            ->where('adc_id', $adcId)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->get();

        $totalSalesIncome = $salesOrders->sum(function ($ord) {
            return (float) $ord->total_amount - (float) ($ord->total_returned_amount ?? 0);
        });

        $cogs = 0.00;
        foreach ($salesOrders as $ord) {
            foreach ($ord->items as $item) {
                $netQty = max(0, (float) $item->quantity - (float) ($item->returned_quantity ?? 0));
                if ($netQty <= 0) continue;

                $unitPrice = (float) ($item->unit_price > 0 ? $item->unit_price : ($item->quantity > 0 ? $item->total_price / $item->quantity : 0));
                $inv = $item->inventory;
                $unitCost = $inv ? ((float) ($inv->purchase_price > 0 ? $inv->purchase_price : ($inv->landing_cost > 0 ? $inv->landing_cost : ($inv->adc_price > 0 ? $inv->adc_price : $unitPrice)))) : $unitPrice;

                $cogs += $netQty * $unitCost;
            }
        }
        $cogs = round($cogs, 2);

        $grossProfit = max(0, $totalSalesIncome - $cogs);
        $grossMarginPct = $totalSalesIncome > 0 ? round(($grossProfit / $totalSalesIncome) * 100, 1) : 0;

        $operatingPurchases = AdcLocalPurchase::where('adc_id', $adcId)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->sum('total_amount');

        $netProfit = $grossProfit - $operatingPurchases;
        $netMarginPct = $totalSalesIncome > 0 ? round(($netProfit / $totalSalesIncome) * 100, 1) : 0;

        $monthlyPnl = DB::table('adc_pos_orders')
            ->where('adc_id', $adcId)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%b %Y') as month"),
                DB::raw('SUM(total_amount - COALESCE(total_returned_amount, 0)) as sales_income')
            )
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%b %Y')"), DB::raw("YEAR(created_at)"), DB::raw("MONTH(created_at)"))
            ->orderBy(DB::raw("YEAR(created_at)"), 'desc')
            ->orderBy(DB::raw("MONTH(created_at)"), 'desc')
            ->get()
            ->map(function ($m) {
                $m->gross_profit = round($m->sales_income * 0.25, 2);
                return $m;
            });

        return view('adc.reports.pnl', compact(
            'totalSalesIncome', 'cogs', 'grossProfit', 'grossMarginPct', 'operatingPurchases', 'netProfit', 'netMarginPct', 'monthlyPnl', 'fromDate', 'toDate'
        ));
    }

    public function printPnlReport(Request $request)
    {
        $adcId = $this->getAdcId();
        [$fromDate, $toDate] = $this->parseDates($request);

        $salesOrders = AdcPosOrder::with('items.inventory')
            ->where('adc_id', $adcId)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->get();

        $totalSalesIncome = $salesOrders->sum(function ($ord) {
            return (float) $ord->total_amount - (float) ($ord->total_returned_amount ?? 0);
        });

        $cogs = 0.00;
        foreach ($salesOrders as $ord) {
            foreach ($ord->items as $item) {
                $netQty = max(0, (float) $item->quantity - (float) ($item->returned_quantity ?? 0));
                if ($netQty <= 0) continue;

                $unitPrice = (float) ($item->unit_price > 0 ? $item->unit_price : ($item->quantity > 0 ? $item->total_price / $item->quantity : 0));
                $inv = $item->inventory;
                $unitCost = $inv ? ((float) ($inv->purchase_price > 0 ? $inv->purchase_price : ($inv->landing_cost > 0 ? $inv->landing_cost : ($inv->adc_price > 0 ? $inv->adc_price : $unitPrice)))) : $unitPrice;

                $cogs += $netQty * $unitCost;
            }
        }
        $cogs = round($cogs, 2);

        $grossProfit = max(0, $totalSalesIncome - $cogs);
        $grossMarginPct = $totalSalesIncome > 0 ? round(($grossProfit / $totalSalesIncome) * 100, 1) : 0;

        $operatingPurchases = AdcLocalPurchase::where('adc_id', $adcId)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->sum('total_amount');

        $netProfit = $grossProfit - $operatingPurchases;
        $netMarginPct = $totalSalesIncome > 0 ? round(($netProfit / $totalSalesIncome) * 100, 1) : 0;

        $monthlyPnl = DB::table('adc_pos_orders')
            ->where('adc_id', $adcId)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->select(
                DB::raw("DATE_FORMAT(created_at, '%b %Y') as month"),
                DB::raw('SUM(total_amount - COALESCE(total_returned_amount, 0)) as sales_income')
            )
            ->groupBy(DB::raw("DATE_FORMAT(created_at, '%b %Y')"), DB::raw("YEAR(created_at)"), DB::raw("MONTH(created_at)"))
            ->orderBy(DB::raw("YEAR(created_at)"), 'desc')
            ->orderBy(DB::raw("MONTH(created_at)"), 'desc')
            ->get()
            ->map(function ($m) {
                $m->gross_profit = round($m->sales_income * 0.25, 2);
                return $m;
            });

        return view('adc.reports.pnl_print', compact(
            'totalSalesIncome', 'cogs', 'grossProfit', 'grossMarginPct', 'operatingPurchases', 'netProfit', 'netMarginPct', 'monthlyPnl', 'fromDate', 'toDate'
        ));
    }

    public function exportPnlReportCsv(Request $request)
    {
        $adcId = $this->getAdcId();
        [$fromDate, $toDate] = $this->parseDates($request);

        $salesOrders = AdcPosOrder::with('items.inventory')
            ->where('adc_id', $adcId)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->get();

        $totalSalesIncome = $salesOrders->sum(function ($ord) {
            return (float) $ord->total_amount - (float) ($ord->total_returned_amount ?? 0);
        });

        $cogs = 0.00;
        foreach ($salesOrders as $ord) {
            foreach ($ord->items as $item) {
                $netQty = max(0, (float) $item->quantity - (float) ($item->returned_quantity ?? 0));
                if ($netQty <= 0) continue;

                $unitPrice = (float) ($item->unit_price > 0 ? $item->unit_price : ($item->quantity > 0 ? $item->total_price / $item->quantity : 0));
                $inv = $item->inventory;
                $unitCost = $inv ? ((float) ($inv->purchase_price > 0 ? $inv->purchase_price : ($inv->landing_cost > 0 ? $inv->landing_cost : ($inv->adc_price > 0 ? $inv->adc_price : $unitPrice)))) : $unitPrice;

                $cogs += $netQty * $unitCost;
            }
        }
        $cogs = round($cogs, 2);

        $grossProfit = max(0, $totalSalesIncome - $cogs);
        $operatingPurchases = AdcLocalPurchase::where('adc_id', $adcId)
            ->whereBetween('created_at', [$fromDate, $toDate])
            ->sum('total_amount');

        $netProfit = $grossProfit - $operatingPurchases;

        $filename = "P_and_L_Statement_" . date('Y-m-d') . ".csv";

        return response()->streamDownload(function () use ($fromDate, $toDate, $totalSalesIncome, $cogs, $grossProfit, $operatingPurchases, $netProfit) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Financial Line Item', 'Amount (INR)']);
            fputcsv($handle, ['Statement Period From', $fromDate->format('Y-m-d')]);
            fputcsv($handle, ['Statement Period To', $toDate->format('Y-m-d')]);
            fputcsv($handle, ['Total Sales Income (Revenue)', number_format($totalSalesIncome, 2, '.', '')]);
            fputcsv($handle, ['Less: Cost of Goods Sold (COGS)', number_format($cogs, 2, '.', '')]);
            fputcsv($handle, ['GROSS PROFIT', number_format($grossProfit, 2, '.', '')]);
            fputcsv($handle, ['Less: Local Purchases / Operating Expenses', number_format($operatingPurchases, 2, '.', '')]);
            fputcsv($handle, ['NET PROFIT / (LOSS)', number_format($netProfit, 2, '.', '')]);
            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }
}
