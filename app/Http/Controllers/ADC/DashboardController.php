<?php

namespace App\Http\Controllers\ADC;

use App\Http\Controllers\Controller;
use App\Models\AdcPosOrder;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('adc')->user();
        if (!$user) {
            return redirect()->route('adc.login');
        }
        $adcId = $user->id;

        // 1. All POS Orders for this ADC with items & inventory loaded
        $orders = AdcPosOrder::where('adc_id', $adcId)
            ->with(['items.inventory', 'customer'])
            ->get();

        // ── Helper closure to calculate profit for a collection of AdcPosOrders ──
        $calculateProfit = function ($ordersCollection) {
            $totalProfit = 0.00;
            foreach ($ordersCollection as $order) {
                $orderSpecialDisc = (float) ($order->special_discount_amount ?? 0);

                $ordGrossLineTotal = 0;
                foreach ($order->items as $item) {
                    $itemQty = max(0, (float) $item->quantity - (float) ($item->returned_quantity ?? 0));
                    if ($itemQty <= 0) continue;
                    $uPrice = (float) ($item->unit_price > 0 ? $item->unit_price : ($item->quantity > 0 ? $item->total_price / $item->quantity : 0));
                    $ordGrossLineTotal += ($itemQty * $uPrice);
                }

                if ($ordGrossLineTotal <= 0) {
                    continue; // Fully returned order — net profit is 0.00
                }

                $effectiveSpecialDisc = min($orderSpecialDisc, $ordGrossLineTotal);

                $orderItemProfit = 0.00;
                foreach ($order->items as $item) {
                    $netQty = max(0, (float) $item->quantity - (float) ($item->returned_quantity ?? 0));
                    if ($netQty <= 0)
                        continue;

                    $unitSellPrice = (float) ($item->unit_price > 0
                        ? $item->unit_price
                        : ($item->quantity > 0 ? $item->total_price / $item->quantity : 0));

                    $inv = $item->inventory;
                    $unitCost = $inv
                        ? ((float) ($inv->purchase_price > 0 ? $inv->purchase_price : ($inv->landing_cost > 0 ? $inv->landing_cost : ($inv->adc_price > 0 ? $inv->adc_price : $unitSellPrice))))
                        : $unitSellPrice;

                    $orderItemProfit += $netQty * ($unitSellPrice - $unitCost);
                }
                $totalProfit += ($orderItemProfit - $effectiveSpecialDisc);
            }
            return round($totalProfit, 2);
        };

        // Date bounds
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();
        $todayStart = Carbon::today()->startOfDay();
        $todayEnd = Carbon::today()->endOfDay();

        // ── 1. Total POS Sales ──
        $totalPosSaleAmount = (float) $orders->sum(function ($ord) {
            return (float) $ord->total_amount - (float) ($ord->total_returned_amount ?? 0);
        });
        $totalPosSaleCount = $orders->count();
        $totalProfit = $calculateProfit($orders);

        // ── 2. Monthly POS Sales ──
        $monthlyOrders = $orders->filter(function ($ord) use ($startOfMonth, $endOfMonth) {
            return $ord->created_at >= $startOfMonth && $ord->created_at <= $endOfMonth;
        });
        $monthlyPosSaleAmount = (float) $monthlyOrders->sum(function ($ord) {
            return (float) $ord->total_amount - (float) ($ord->total_returned_amount ?? 0);
        });
        $monthlyPosSaleCount = $monthlyOrders->count();
        $monthlyProfit = $calculateProfit($monthlyOrders);

        // ── 3. Today POS Sales ──
        $todayOrders = $orders->filter(function ($ord) use ($todayStart, $todayEnd) {
            return $ord->created_at >= $todayStart && $ord->created_at <= $todayEnd;
        });
        $todayPosSaleAmount = (float) $todayOrders->sum(function ($ord) {
            return (float) $ord->total_amount - (float) ($ord->total_returned_amount ?? 0);
        });
        $todayPosSaleCount = $todayOrders->count();
        $todayProfit = $calculateProfit($todayOrders);

        // ── 4. Wallet Balance ──
        $walletBalance = (float) ($user->adcProfile?->wallet_balance ?? 0.00);

        // ── 5. Monthly Sales & Profit Bar Chart Data (Last 6 Months) ──
        $chartMonths = [];
        $chartSalesData = [];
        $chartProfitData = [];

        for ($i = 5; $i >= 0; $i--) {
            $mStart = Carbon::now()->subMonths($i)->startOfMonth();
            $mEnd = Carbon::now()->subMonths($i)->endOfMonth();

            $mOrders = $orders->filter(function ($ord) use ($mStart, $mEnd) {
                return $ord->created_at >= $mStart && $ord->created_at <= $mEnd;
            });

            $mSales = (float) $mOrders->sum(function ($ord) {
                return (float) $ord->total_amount - (float) ($ord->total_returned_amount ?? 0);
            });
            $mProfit = $calculateProfit($mOrders);

            $chartMonths[] = $mStart->format('M Y');
            $chartSalesData[] = round($mSales, 2);
            $chartProfitData[] = round($mProfit, 2);
        }

        // ── 6. Summary Totals for Chart ──
        $sixMonthTotalSales = round(array_sum($chartSalesData), 2);
        $sixMonthTotalProfit = round(array_sum($chartProfitData), 2);

        // ── 7. Mini Sparkline Datasets ──
        $totalProfitChartLabels   = $chartMonths;
        $totalProfitChartData     = $chartProfitData;
        $monthlyProfitChartLabels = $chartMonths;
        $monthlyProfitChartData   = $chartProfitData;
        $todayProfitChartLabels   = $chartMonths;
        $todayProfitChartData     = $chartProfitData;

        $totalSalesChartLabels    = $chartMonths;
        $totalSalesChartData      = $chartSalesData;
        $monthlySalesChartLabels  = $chartMonths;
        $monthlySalesChartData    = $chartSalesData;
        $todaySalesChartLabels    = $chartMonths;
        $todaySalesChartData      = $chartSalesData;

        // ── 9. Recent Sales Bills (Top 5) ──
        $recentSales = $orders->sortByDesc('created_at')->take(5);

        // ── 10. Expiry Analytics Data ──
        $today = Carbon::today();
        $todayStr = $today->format('Y-m-d');
        $oneMonthStr = $today->copy()->addDays(30)->format('Y-m-d');
        $threeMonthsStr = $today->copy()->addDays(90)->format('Y-m-d');
        $sixMonthsStr = $today->copy()->addDays(180)->format('Y-m-d');

        $totalStockItems = \App\Models\AdcInventory::where('adc_id', $adcId)->count();
        $totalStockQuantity = (float) \App\Models\AdcInventory::where('adc_id', $adcId)->sum('quantity');
        $totalStockValue = (float) \App\Models\AdcInventory::where('adc_id', $adcId)->get()->sum(function ($i) {
            return (float) $i->quantity * (float) ($i->mrp > 0 ? $i->mrp : ($i->csp > 0 ? $i->csp : 0));
        });

        $expiredCount = \App\Models\AdcInventory::where('adc_id', $adcId)
            ->whereNotNull('expire_date')
            ->where('expire_date', '<', $todayStr)
            ->count();

        $nearExpiry1MonthCount = \App\Models\AdcInventory::where('adc_id', $adcId)
            ->whereNotNull('expire_date')
            ->where('expire_date', '>=', $todayStr)
            ->where('expire_date', '<=', $oneMonthStr)
            ->count();

        $nearExpiry3MonthsCount = \App\Models\AdcInventory::where('adc_id', $adcId)
            ->whereNotNull('expire_date')
            ->where('expire_date', '>', $oneMonthStr)
            ->where('expire_date', '<=', $threeMonthsStr)
            ->count();

        $nearExpiry6MonthsCount = \App\Models\AdcInventory::where('adc_id', $adcId)
            ->whereNotNull('expire_date')
            ->where('expire_date', '>', $threeMonthsStr)
            ->where('expire_date', '<=', $sixMonthsStr)
            ->count();

        $totalCustomersCount = \App\Models\AdcPosCustomer::where('adc_id', $adcId)->count();
        $adcCode = $user->adcProfile?->adc_code ?: ($user->adc_code ?: ('ADC' . str_pad($user->id, 4, '0', STR_PAD_LEFT)));
        $adcProfile = $user->adcProfile;

        // 11. Sales Returns Analytics Data
        $totalSalesReturnAmount = (float) \App\Models\AdcPosSalesReturn::where('adc_id', $adcId)->sum('total_return_amount');
        $totalSalesReturnCount = \App\Models\AdcPosSalesReturn::where('adc_id', $adcId)->count();
        $recentSalesReturns = \App\Models\AdcPosSalesReturn::where('adc_id', $adcId)
            ->with('order')
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        return view('adc.dashboard', compact(
            'user',
            'adcCode',
            'adcProfile',
            'totalPosSaleAmount',
            'totalPosSaleCount',
            'monthlyPosSaleAmount',
            'monthlyPosSaleCount',
            'todayPosSaleAmount',
            'todayPosSaleCount',
            'totalProfit',
            'monthlyProfit',
            'todayProfit',
            'walletBalance',
            'chartMonths',
            'chartSalesData',
            'chartProfitData',
            'sixMonthTotalSales',
            'sixMonthTotalProfit',
            'totalProfitChartLabels',
            'totalProfitChartData',
            'monthlyProfitChartLabels',
            'monthlyProfitChartData',
            'todayProfitChartLabels',
            'todayProfitChartData',
            'recentSales',
            'totalSalesChartLabels',
            'totalSalesChartData',
            'monthlySalesChartLabels',
            'monthlySalesChartData',
            'todaySalesChartLabels',
            'todaySalesChartData',
            'totalStockItems',
            'totalStockQuantity',
            'totalStockValue',
            'totalCustomersCount',
            'expiredCount',
            'nearExpiry1MonthCount',
            'nearExpiry3MonthsCount',
            'nearExpiry6MonthsCount',
            'totalSalesReturnAmount',
            'totalSalesReturnCount',
            'recentSalesReturns'
        ));
    }

    public function salesProfitChartData(\Illuminate\Http\Request $request)
    {
        $user = Auth::guard('adc')->user();
        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 401);
        }
        $adcId = $user->id;
        $period = $request->get('period', 'this_year');

        $now = Carbon::now();
        if ($period === 'last_year') {
            $startDate = $now->copy()->subYear()->startOfYear();
            $endDate = $now->copy()->subYear()->endOfYear();
        } elseif ($period === 'last_6_months') {
            $startDate = $now->copy()->subMonths(5)->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
        } elseif ($period === 'last_12_months') {
            $startDate = $now->copy()->subMonths(11)->startOfMonth();
            $endDate = $now->copy()->endOfMonth();
        } else {
            // this_year (Current Financial Year April to March)
            $fyStartYear = $now->month >= 4 ? $now->year : $now->year - 1;
            $startDate = Carbon::createFromDate($fyStartYear, 4, 1)->startOfDay();
            $endDate = Carbon::createFromDate($fyStartYear + 1, 3, 31)->endOfDay();
        }

        $labels = [];
        $salesData = [];
        $profitData = [];

        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $mStart = $current->copy()->startOfMonth();
            $mEnd = $current->copy()->endOfMonth();
            $mLabel = $current->format('M Y');

            $posOrders = AdcPosOrder::where('adc_id', $adcId)
                ->whereBetween('created_at', [$mStart, $mEnd])
                ->with('items.inventory')
                ->get();

            $monthlySales = 0;
            $monthlyCogs = 0;

            foreach ($posOrders as $o) {
                $ordGrossLineTotal = 0;
                foreach ($o->items as $it) {
                    $itemQty = max(0, (float) $it->quantity - (float) ($it->returned_quantity ?? 0));
                    if ($itemQty <= 0) continue;
                    $uPrice = (float) ($it->unit_price > 0 ? $it->unit_price : ($it->quantity > 0 ? $it->total_price / $it->quantity : 0));
                    $ordGrossLineTotal += ($itemQty * $uPrice);
                }

                if ($ordGrossLineTotal <= 0) continue;

                $orderSpecialDisc = min((float) ($o->special_discount_amount ?? 0), $ordGrossLineTotal);
                $monthlySales -= $orderSpecialDisc;

                foreach ($o->items as $it) {
                    $netQty = max(0, (float) $it->quantity - (float) ($it->returned_quantity ?? 0));
                    if ($netQty <= 0) continue;

                    $unitSellPrice = (float) ($it->unit_price > 0 ? $it->unit_price : ($it->quantity > 0 ? $it->total_price / $it->quantity : 0));
                    $linePrice = $netQty * $unitSellPrice;
                    $inv = $it->inventory;
                    $unitCost = $inv ? ((float)($inv->purchase_price > 0 ? $inv->purchase_price : ($inv->landing_cost > 0 ? $inv->landing_cost : ($inv->adc_price > 0 ? $inv->adc_price : $unitSellPrice)))) : $unitSellPrice;

                    $monthlySales += $linePrice;
                    $monthlyCogs += ($netQty * $unitCost);
                }
            }

            $monthlyProfit = max(0, $monthlySales - $monthlyCogs);

            $labels[] = $mLabel;
            $salesData[] = round($monthlySales, 2);
            $profitData[] = round($monthlyProfit, 2);

            $current->addMonth();
        }

        return response()->json([
            'status' => 'success',
            'period' => $period,
            'labels' => $labels,
            'sales' => $salesData,
            'net_profit' => $profitData,
            'total_sales' => round(array_sum($salesData), 2),
            'total_profit' => round(array_sum($profitData), 2),
        ]);
    }
}
