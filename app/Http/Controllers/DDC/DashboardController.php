<?php

namespace App\Http\Controllers\DDC;

use App\Http\Controllers\Controller;
use App\Models\AdcOrder;
use App\Models\DdcInventory;
use App\Models\DdcPosCustomer;
use App\Models\DdcPosOrder;
use App\Models\PurchaseOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::guard('ddc')->user();
        $ddcId = $user->id;

        // 1. Wallet Balance & DDC Profile
        $ddcProfile = DB::table('ddc_profiles')->where('user_id', $ddcId)->first();
        $walletBalance = $ddcProfile->wallet_balance ?? 0;

        // 2. Purchases (MSME POs Received + Local Purchases)
        $msmePurchaseAmount = PurchaseOrder::where('ddc_id', $ddcId)
            ->whereIn('status', ['Received', 'Completed'])
            ->sum('total_amount');

        $localPurchaseAmount = DB::table('ddc_local_purchases')
            ->where('ddc_id', $ddcId)
            ->sum('total_amount');

        $totalPurchaseAmount = $msmePurchaseAmount + $localPurchaseAmount;
        $totalPurchaseOrdersCount = PurchaseOrder::where('ddc_id', $ddcId)->count();

        // 3. Sales (POS Sales + ADC Wholesale Orders)
        $posSalesAmount = DdcPosOrder::where('ddc_id', $ddcId)->sum('total_amount');
        $adcSalesAmount = AdcOrder::where(function ($q) use ($ddcId) {
            $q->where('assigned_ddc_id', $ddcId)
              ->orWhere('selected_ddc_id', $ddcId)
              ->orWhere('virtual_ddc_id', $ddcId);
        })
        ->whereIn('status', ['Completed', 'Dispatched', 'Processing', 'Delivered', 'Paid'])
        ->sum('total_amount');

        $totalSalesAmount = $posSalesAmount + $adcSalesAmount;
        $totalPosSalesCount = DdcPosOrder::where('ddc_id', $ddcId)->count();

        // 4. Total Stock / Inventory
        $totalStockItems = DdcInventory::where('ddc_id', $ddcId)
            ->where('quantity', '>', 0)
            ->count();

        $totalStockQuantity = DdcInventory::where('ddc_id', $ddcId)
            ->where('quantity', '>', 0)
            ->sum('quantity');

        $totalStockValue = DdcInventory::where('ddc_id', $ddcId)
            ->where('quantity', '>', 0)
            ->sum(DB::raw('quantity * mrp'));

        // 5. Total Canteens (ADC Retail Stores mapped to this DDC via pincode only)
        $pincodeIds = DB::table('ddc_pincodes')->where('ddc_id', $ddcId)->pluck('pincode_id')->toArray();
        $mappedPincodes = DB::table('pincodes')->whereIn('id', $pincodeIds)->pluck('pincode')->toArray();
        $mappedPincodes = array_unique(array_filter($mappedPincodes));

        $allAdcUserIds = User::where('role', 'adc')
            ->whereHas('adcProfile', function ($apq) use ($mappedPincodes) {
                $apq->whereIn('pincode', $mappedPincodes);
            })
            ->pluck('id');

        $totalCanteensCount = $allAdcUserIds->count();
        $activeCanteensCount = User::whereIn('id', $allAdcUserIds)->where('status', '1')->count();

        // 6. Total Customers
        $totalCustomersCount = DdcPosCustomer::where(function ($q) use ($ddcId) {
            $q->where('ddc_id', $ddcId)
              ->orWhereHas('orders', function ($oq) use ($ddcId) {
                  $oq->where('ddc_id', $ddcId);
              });
        })->count();

        if ($totalCustomersCount === 0) {
            $totalCustomersCount = DdcPosCustomer::count();
        }

        // 7. Expiry Analytics (Near Expiry 1M, 3M, 6M & Expired)
        $today = Carbon::today();

        $expiredCount = DdcInventory::where('ddc_id', $ddcId)
            ->whereNotNull('expire_date')
            ->where('expire_date', '<', $today)
            ->count();

        $nearExpiry1MonthCount = DdcInventory::where('ddc_id', $ddcId)
            ->whereNotNull('expire_date')
            ->where('expire_date', '>=', $today)
            ->where('expire_date', '<=', $today->copy()->addDays(30))
            ->count();

        $nearExpiry3MonthsCount = DdcInventory::where('ddc_id', $ddcId)
            ->whereNotNull('expire_date')
            ->where('expire_date', '>', $today->copy()->addDays(30))
            ->where('expire_date', '<=', $today->copy()->addDays(90))
            ->count();

        $nearExpiry6MonthsCount = DdcInventory::where('ddc_id', $ddcId)
            ->whereNotNull('expire_date')
            ->where('expire_date', '>', $today->copy()->addDays(90))
            ->where('expire_date', '<=', $today->copy()->addDays(180))
            ->count();

        // 8. Purchase Orders Breakdown
        $pendingPosCount = PurchaseOrder::where('ddc_id', $ddcId)
            ->whereIn('status', ['Pending', 'Accepted', 'Processing', 'Dispatched'])
            ->count();

        $completedPosCount = PurchaseOrder::where('ddc_id', $ddcId)
            ->whereIn('status', ['Received', 'Completed'])
            ->count();

        $recentPos = PurchaseOrder::where('ddc_id', $ddcId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $recentSales = DdcPosOrder::where('ddc_id', $ddcId)
            ->orderBy('created_at', 'desc')
            ->take(5)
            ->get();

        $ddcCode = $ddcProfile?->ddc_code ?: ($user->ddcProfile?->ddc_code ?: ($user->profile?->ddc_code ?: ('DDB' . str_pad($ddcId, 3, '0', STR_PAD_LEFT))));

        return view('ddc.dashboard', compact(
            'user',
            'ddcProfile',
            'ddcCode',
            'walletBalance',
            'totalPurchaseAmount',
            'msmePurchaseAmount',
            'localPurchaseAmount',
            'totalPurchaseOrdersCount',
            'totalSalesAmount',
            'posSalesAmount',
            'adcSalesAmount',
            'totalPosSalesCount',
            'totalStockItems',
            'totalStockQuantity',
            'totalStockValue',
            'totalCanteensCount',
            'activeCanteensCount',
            'totalCustomersCount',
            'expiredCount',
            'nearExpiry1MonthCount',
            'nearExpiry3MonthsCount',
            'nearExpiry6MonthsCount',
            'pendingPosCount',
            'completedPosCount',
            'recentPos',
            'recentSales'
        ));
    }

    public function salesProfitChartData(Request $request)
    {
        $ddc = Auth::guard('ddc')->user();
        $ddcId = $ddc->id;
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

        // Linked ADC Order IDs to avoid double counting
        $linkedAdcOrderIds = DdcPosOrder::where('ddc_id', $ddcId)
            ->whereNotNull('adc_order_id')
            ->pluck('adc_order_id')
            ->filter()
            ->toArray();

        $labels = [];
        $salesData = [];
        $profitData = [];

        $current = $startDate->copy();
        while ($current->lte($endDate)) {
            $mStart = $current->copy()->startOfMonth();
            $mEnd = $current->copy()->endOfMonth();
            $mLabel = $current->format('M Y');

            // Retail & Wholesale POS Orders
            $posOrders = DdcPosOrder::where('ddc_id', $ddcId)
                ->whereBetween('created_at', [$mStart, $mEnd])
                ->with('items.inventory')
                ->get();

            // Direct ADC Canteen Orders
            $adcOrders = AdcOrder::where(function ($q) use ($ddcId) {
                    $q->where('assigned_ddc_id', $ddcId)
                      ->orWhere('selected_ddc_id', $ddcId);
                })
                ->whereNotIn('id', $linkedAdcOrderIds)
                ->whereBetween('created_at', [$mStart, $mEnd])
                ->with('items.product')
                ->get();

            $monthlySales = 0;
            $monthlyCogs = 0;

            foreach ($posOrders as $o) {
                $monthlySales -= (float) ($o->special_discount_amount ?? 0);
                foreach ($o->items as $it) {
                    $netQty = max(0, (float) $it->quantity - (float) ($it->returned_quantity ?? 0));
                    if ($netQty <= 0) continue;

                    $unitSellPrice = (float) ($it->unit_price > 0 ? $it->unit_price : ($it->quantity > 0 ? $it->total_price / $it->quantity : 0));
                    $linePrice = $netQty * $unitSellPrice;
                    $inv = $it->inventory;
                    $unitCost = $inv ? ((float)($inv->landing_cost > 0 ? $inv->landing_cost : ($inv->purchase_price > 0 ? $inv->purchase_price : $unitSellPrice))) : $unitSellPrice;

                    $monthlySales += $linePrice;
                    $monthlyCogs += ($netQty * $unitCost);
                }
            }

            foreach ($adcOrders as $o) {
                foreach ($o->items as $it) {
                    $qty = (float) $it->quantity;
                    $linePrice = (float) ($it->total > 0 ? $it->total : ($qty * $it->price));
                    $prod = $it->product;
                    $unitCost = $prod ? ((float)($prod->landing_cost > 0 ? $prod->landing_cost : ($prod->purchase_price > 0 ? $prod->purchase_price : $it->price))) : (float) $it->price;

                    $monthlySales += $linePrice;
                    $monthlyCogs += ($qty * $unitCost);
                }
            }

            $monthlyNetProfit = $monthlySales - $monthlyCogs;

            $labels[] = $mLabel;
            $salesData[] = round($monthlySales, 2);
            $profitData[] = round($monthlyNetProfit, 2);

            $current->addMonth();
        }

        return response()->json([
            'status'       => 'success',
            'period'       => $period,
            'labels'       => $labels,
            'sales'        => $salesData,
            'net_profit'   => $profitData,
            'total_sales'  => array_sum($salesData),
            'total_profit' => array_sum($profitData),
        ]);
    }
}
