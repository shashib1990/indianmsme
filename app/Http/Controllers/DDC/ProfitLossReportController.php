<?php

namespace App\Http\Controllers\DDC;

use App\Http\Controllers\Controller;
use App\Models\DdcPosOrder;
use App\Models\DdcPosOrderItem;
use App\Models\AdcOrder;
use App\Models\PurchaseOrder;
use App\Models\DdcLocalPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProfitLossReportController extends Controller
{
    public function index(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $source = strtolower($request->input('source', 'all')); // all, msme, local/regional
        if ($source === 'regional') {
            $source = 'local';
        }
        if (!in_array($source, ['all', 'msme', 'local'])) {
            $source = 'all';
        }

        $pnlData = $this->calculateProfitLossData($ddcId, $startDate, $endDate, $source);

        return view('ddc.reports.pnl', compact('pnlData', 'startDate', 'endDate', 'source'));
    }

    public function exportCsv(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $source = strtolower($request->input('source', 'all'));
        if ($source === 'regional') {
            $source = 'local';
        }

        if (!in_array($source, ['all', 'msme', 'local'])) {
            $source = 'all';
        }

        $pnlData = $this->calculateProfitLossData($ddcId, $startDate, $endDate, $source);

        $sourceLabel = strtoupper($source);
        $filename = 'ddc_pnl_report_' . strtolower($sourceLabel) . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($pnlData, $startDate, $endDate, $sourceLabel) {
            $file = fopen('php://output', 'w');
            
            fputcsv($file, ["PROFIT & LOSS STATEMENT ({$sourceLabel} P&L REPORT)"]);
            fputcsv($file, ['Period:', $startDate . ' to ' . $endDate]);
            fputcsv($file, ['Sourcing Scope:', $sourceLabel]);
            fputcsv($file, ['Generated Date:', date('d-m-Y H:i:s')]);
            fputcsv($file, []);

            fputcsv($file, ['1. REVENUE / INCOME', 'AMOUNT (₹)']);
            fputcsv($file, ['Retail POS Sales (CSP)', number_format($pnlData['revenue']['retail_pos'], 2, '.', '')]);
            fputcsv($file, ['Wholesale POS Sales (MSP)', number_format($pnlData['revenue']['wholesale_pos'], 2, '.', '')]);
            fputcsv($file, ['ADC Canteen Direct Orders', number_format($pnlData['revenue']['adc_canteen'], 2, '.', '')]);
            fputcsv($file, ['TOTAL GROSS REVENUE', number_format($pnlData['revenue']['total'], 2, '.', '')]);
            fputcsv($file, []);

            fputcsv($file, ['2. COST OF GOODS SOLD (COGS)', 'AMOUNT (₹)']);
            fputcsv($file, ['Retail POS COGS', number_format($pnlData['cogs']['retail_pos'], 2, '.', '')]);
            fputcsv($file, ['Wholesale POS COGS', number_format($pnlData['cogs']['wholesale_pos'], 2, '.', '')]);
            fputcsv($file, ['ADC Canteen Orders COGS', number_format($pnlData['cogs']['adc_canteen'], 2, '.', '')]);
            fputcsv($file, ['TOTAL COST OF GOODS SOLD', number_format($pnlData['cogs']['total'], 2, '.', '')]);
            fputcsv($file, []);

            fputcsv($file, ['3. PROFITABILITY SUMMARY', 'AMOUNT (₹) / %']);
            fputcsv($file, ['GROSS PROFIT', number_format($pnlData['gross_profit'], 2, '.', '')]);
            fputcsv($file, ['GROSS PROFIT MARGIN (%)', number_format($pnlData['gross_margin_pct'], 2, '.', '') . '%']);
            fputcsv($file, ['NET PROFIT / (LOSS)', number_format($pnlData['net_profit'], 2, '.', '')]);
            fputcsv($file, []);

            fputcsv($file, ['4. PURCHASES & STOCK-IN (PERIOD EXPENSES)', 'AMOUNT (₹)']);
            fputcsv($file, ['Central Vendor PO Purchases', number_format($pnlData['purchases']['central_po'], 2, '.', '')]);
            fputcsv($file, ['Regional Stock Purchases', number_format($pnlData['purchases']['regional'], 2, '.', '')]);
            fputcsv($file, ['TOTAL STOCK PURCHASES', number_format($pnlData['purchases']['total'], 2, '.', '')]);
            fputcsv($file, []);

            fputcsv($file, ['COMPARATIVE BREAKDOWN (MSME VS REGIONAL)']);
            fputcsv($file, ['Component', 'MSME (Central)', 'Regional (Local)', 'Combined Total']);
            fputcsv($file, ['Sales Revenue', number_format($pnlData['msme']['revenue']['total'], 2, '.', ''), number_format($pnlData['local']['revenue']['total'], 2, '.', ''), number_format($pnlData['combined']['revenue']['total'], 2, '.', '')]);
            fputcsv($file, ['COGS', number_format($pnlData['msme']['cogs']['total'], 2, '.', ''), number_format($pnlData['local']['cogs']['total'], 2, '.', ''), number_format($pnlData['combined']['cogs']['total'], 2, '.', '')]);
            fputcsv($file, ['Gross Profit', number_format($pnlData['msme']['gross_profit'], 2, '.', ''), number_format($pnlData['local']['gross_profit'], 2, '.', ''), number_format($pnlData['combined']['gross_profit'], 2, '.', '')]);
            fputcsv($file, ['Gross Margin %', number_format($pnlData['msme']['gross_margin_pct'], 2, '.', '') . '%', number_format($pnlData['local']['gross_margin_pct'], 2, '.', '') . '%', number_format($pnlData['combined']['gross_margin_pct'], 2, '.', '') . '%']);
            fputcsv($file, ['Stock Purchases', number_format($pnlData['msme']['purchases']['total'], 2, '.', ''), number_format($pnlData['local']['purchases']['total'], 2, '.', ''), number_format($pnlData['combined']['purchases']['total'], 2, '.', '')]);
            fputcsv($file, []);

            fputcsv($file, ['MONTHLY BREAKDOWN']);
            fputcsv($file, ['Month / Period', 'Orders Count', 'Total Revenue (₹)', 'COGS (₹)', 'Gross Profit (₹)', 'Gross Margin (%)', 'Purchases (₹)', 'Net Profit (₹)']);

            foreach ($pnlData['monthly_breakdown'] as $row) {
                fputcsv($file, [
                    $row['period'],
                    $row['orders_count'],
                    number_format($row['revenue'], 2, '.', ''),
                    number_format($row['cogs'], 2, '.', ''),
                    number_format($row['gross_profit'], 2, '.', ''),
                    number_format($row['margin_pct'], 2, '.', '') . '%',
                    number_format($row['purchases'], 2, '.', ''),
                    number_format($row['net_profit'], 2, '.', ''),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function print(Request $request)
    {
        $ddcId = Auth::guard('ddc')->id();
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->format('Y-m-d'));
        $source = strtolower($request->input('source', 'all'));
        if ($source === 'regional') {
            $source = 'local';
        }

        if (!in_array($source, ['all', 'msme', 'local'])) {
            $source = 'all';
        }

        $pnlData = $this->calculateProfitLossData($ddcId, $startDate, $endDate, $source);

        return view('ddc.reports.pnl-print', compact('pnlData', 'startDate', 'endDate', 'source'));
    }

    private function calculateProfitLossData($ddcId, $startDate, $endDate, $sourceFilter = 'all')
    {
        $start = Carbon::parse($startDate)->startOfDay();
        $end = Carbon::parse($endDate)->endOfDay();

        // Linked ADC Order IDs to avoid double counting
        $linkedAdcOrderIds = DdcPosOrder::where('ddc_id', $ddcId)
            ->whereNotNull('adc_order_id')
            ->pluck('adc_order_id')
            ->filter()
            ->toArray();

        // 1. RETAIL POS ORDERS
        $retailOrders = DdcPosOrder::where('ddc_id', $ddcId)
            ->where('mode', 'retail')
            ->whereBetween('created_at', [$start, $end])
            ->with(['items.inventory', 'items.product', 'items.localProduct'])
            ->get();

        // 2. WHOLESALE POS ORDERS
        $wholesaleOrders = DdcPosOrder::where('ddc_id', $ddcId)
            ->where('mode', 'wholesale')
            ->whereBetween('created_at', [$start, $end])
            ->with(['items.inventory', 'items.product', 'items.localProduct'])
            ->get();

        // 3. DIRECT ADC CANTEEN ORDERS
        $adcCanteenOrders = AdcOrder::where(function($q) use ($ddcId) {
                $q->where('assigned_ddc_id', $ddcId)
                  ->orWhere('selected_ddc_id', $ddcId);
            })
            ->whereNotIn('id', $linkedAdcOrderIds)
            ->whereBetween('created_at', [$start, $end])
            ->with(['items.product'])
            ->get();

        // 4. PURCHASES
        $excessPurchases = (float) \App\Models\Invoice::where('type', 'excess_stock_ddc')
            ->whereHas('purchaseOrder', function($q) use ($ddcId) {
                $q->where('ddc_id', $ddcId);
            })
            ->whereBetween('created_at', [$start, $end])
            ->get()
            ->sum(function($inv) {
                return $inv->purchaseOrder ? $inv->purchaseOrder->items->sum(fn($i) => $i->excess_qty * (float)$i->price) : 0;
            });

        $centralPurchases = ((float) PurchaseOrder::where('ddc_id', $ddcId)
            ->whereBetween('created_at', [$start, $end])
            ->sum('total_amount')) + $excessPurchases;

        $regionalPurchases = (float) DdcLocalPurchase::where('ddc_id', $ddcId)
            ->whereBetween('purchase_date', [$start, $end])
            ->sum('total_amount');

        // SOURCING ITEM-LEVEL SPLIT
        // MSME Bucket
        $msmeRev = ['retail' => 0, 'wholesale' => 0, 'adc' => 0];
        $msmeCogs = ['retail' => 0, 'wholesale' => 0, 'adc' => 0];
        $msmeOrdersCount = 0;

        // Local (Regional) Bucket
        $localRev = ['retail' => 0, 'wholesale' => 0, 'adc' => 0];
        $localCogs = ['retail' => 0, 'wholesale' => 0, 'adc' => 0];
        $localOrdersCount = 0;

        // Process Retail POS Orders
        foreach ($retailOrders as $ord) {
            $hasMsme = false;
            $hasLocal = false;
            $ordSpecialDisc = (float) ($ord->special_discount_amount ?? 0);

            $ordGrossLineTotal = 0;
            foreach ($ord->items as $item) {
                $netQty = max(0, (float) $item->quantity - (float) ($item->returned_quantity ?? 0));
                if ($netQty <= 0) continue;
                $unitSellPrice = (float) ($item->unit_price > 0 ? $item->unit_price : ($item->quantity > 0 ? $item->total_price / $item->quantity : 0));
                $ordGrossLineTotal += ($netQty * $unitSellPrice);
            }

            foreach ($ord->items as $item) {
                $netQty = max(0, (float) $item->quantity - (float) ($item->returned_quantity ?? 0));
                if ($netQty <= 0) continue;
                
                $unitSellPrice = (float) ($item->unit_price > 0 ? $item->unit_price : ($item->quantity > 0 ? $item->total_price / $item->quantity : 0));
                $grossLinePrice = $netQty * $unitSellPrice;
                $itemDiscount = ($ordGrossLineTotal > 0 && $ordSpecialDisc > 0)
                    ? ($grossLinePrice / $ordGrossLineTotal) * min($ordSpecialDisc, $ordGrossLineTotal)
                    : 0;
                $linePrice = max(0, $grossLinePrice - $itemDiscount);
                
                $inv = $item->inventory;
                $unitCost = $inv ? ((float)($inv->landing_cost > 0 ? $inv->landing_cost : ($inv->purchase_price > 0 ? $inv->purchase_price : $unitSellPrice))) : $unitSellPrice;
                $lineCogs = $netQty * $unitCost;

                $isLocal = false;
                if ($inv) {
                    $isLocal = strtoupper($inv->inventory_source ?? 'MSME') === 'LOCAL';
                } elseif ($item->localProduct || str_contains(strtolower($item->product_name ?? ''), 'local')) {
                    $isLocal = true;
                }

                if ($isLocal) {
                    $localRev['retail'] += $linePrice;
                    $localCogs['retail'] += $lineCogs;
                    $hasLocal = true;
                } else {
                    $msmeRev['retail'] += $linePrice;
                    $msmeCogs['retail'] += $lineCogs;
                    $hasMsme = true;
                }
            }
            if ($hasMsme) $msmeOrdersCount++;
            if ($hasLocal) $localOrdersCount++;
        }

        // Process Wholesale POS Orders
        foreach ($wholesaleOrders as $ord) {
            $hasMsme = false;
            $hasLocal = false;
            $ordSpecialDisc = (float) ($ord->special_discount_amount ?? 0);

            $ordGrossLineTotal = 0;
            foreach ($ord->items as $item) {
                $netQty = max(0, (float) $item->quantity - (float) ($item->returned_quantity ?? 0));
                if ($netQty <= 0) continue;
                $unitSellPrice = (float) ($item->unit_price > 0 ? $item->unit_price : ($item->quantity > 0 ? $item->total_price / $item->quantity : 0));
                $ordGrossLineTotal += ($netQty * $unitSellPrice);
            }

            foreach ($ord->items as $item) {
                $netQty = max(0, (float) $item->quantity - (float) ($item->returned_quantity ?? 0));
                if ($netQty <= 0) continue;
                
                $unitSellPrice = (float) ($item->unit_price > 0 ? $item->unit_price : ($item->quantity > 0 ? $item->total_price / $item->quantity : 0));
                $grossLinePrice = $netQty * $unitSellPrice;
                $itemDiscount = ($ordGrossLineTotal > 0 && $ordSpecialDisc > 0)
                    ? ($grossLinePrice / $ordGrossLineTotal) * min($ordSpecialDisc, $ordGrossLineTotal)
                    : 0;
                $linePrice = max(0, $grossLinePrice - $itemDiscount);
                
                $inv = $item->inventory;
                $unitCost = $inv ? ((float)($inv->landing_cost > 0 ? $inv->landing_cost : ($inv->purchase_price > 0 ? $inv->purchase_price : $unitSellPrice))) : $unitSellPrice;
                $lineCogs = $netQty * $unitCost;

                $isLocal = false;
                if ($inv) {
                    $isLocal = strtoupper($inv->inventory_source ?? 'MSME') === 'LOCAL';
                } elseif ($item->localProduct || str_contains(strtolower($item->product_name ?? ''), 'local')) {
                    $isLocal = true;
                }

                if ($isLocal) {
                    $localRev['wholesale'] += $linePrice;
                    $localCogs['wholesale'] += $lineCogs;
                    $hasLocal = true;
                } else {
                    $msmeRev['wholesale'] += $linePrice;
                    $msmeCogs['wholesale'] += $lineCogs;
                    $hasMsme = true;
                }
            }
            if ($hasMsme) $msmeOrdersCount++;
            if ($hasLocal) $localOrdersCount++;
        }

        // Process Direct ADC Canteen Orders
        foreach ($adcCanteenOrders as $ord) {
            $hasMsme = false;
            $hasLocal = false;
            foreach ($ord->items as $item) {
                $qty = (float) $item->quantity;
                $linePrice = (float) ($item->total > 0 ? $item->total : ($qty * $item->price));
                $prod = $item->product;
                $unitCost = $prod ? ((float)($prod->landing_cost > 0 ? $prod->landing_cost : ($prod->purchase_price > 0 ? $prod->purchase_price : $item->price))) : (float) $item->price;
                $lineCogs = $qty * $unitCost;

                $isLocal = !empty($item->ddc_local_product_id);

                if ($isLocal) {
                    $localRev['adc'] += $linePrice;
                    $localCogs['adc'] += $lineCogs;
                    $hasLocal = true;
                } else {
                    $msmeRev['adc'] += $linePrice;
                    $msmeCogs['adc'] += $lineCogs;
                    $hasMsme = true;
                }
            }
            if ($hasMsme) $msmeOrdersCount++;
            if ($hasLocal) $localOrdersCount++;
        }

        // Totals for MSME
        $msmeTotalRev = array_sum($msmeRev);
        $msmeTotalCogs = array_sum($msmeCogs);
        $msmeGrossProfit = $msmeTotalRev - $msmeTotalCogs;
        $msmeGrossMarginPct = $msmeTotalRev > 0 ? round(($msmeGrossProfit / $msmeTotalRev) * 100, 2) : 0;

        // Totals for Local (Regional)
        $localTotalRev = array_sum($localRev);
        $localTotalCogs = array_sum($localCogs);
        $localGrossProfit = $localTotalRev - $localTotalCogs;
        $localGrossMarginPct = $localTotalRev > 0 ? round(($localGrossProfit / $localTotalRev) * 100, 2) : 0;

        // Combined Totals
        $combinedRev = [
            'retail_pos'    => $msmeRev['retail'] + $localRev['retail'],
            'wholesale_pos' => $msmeRev['wholesale'] + $localRev['wholesale'],
            'adc_canteen'   => $msmeRev['adc'] + $localRev['adc'],
            'total'         => $msmeTotalRev + $localTotalRev,
        ];
        $combinedCogs = [
            'retail_pos'    => $msmeCogs['retail'] + $localCogs['retail'],
            'wholesale_pos' => $msmeCogs['wholesale'] + $localCogs['wholesale'],
            'adc_canteen'   => $msmeCogs['adc'] + $localCogs['adc'],
            'total'         => $msmeTotalCogs + $localTotalCogs,
        ];
        $combinedGrossProfit = $combinedRev['total'] - $combinedCogs['total'];
        $combinedGrossMarginPct = $combinedRev['total'] > 0 ? round(($combinedGrossProfit / $combinedRev['total']) * 100, 2) : 0;
        $combinedOrdersCount = $retailOrders->count() + $wholesaleOrders->count() + $adcCanteenOrders->count();

        // Data Structure for MSME
        $msmeData = [
            'revenue' => [
                'retail_pos'    => $msmeRev['retail'],
                'wholesale_pos' => $msmeRev['wholesale'],
                'adc_canteen'   => $msmeRev['adc'],
                'total'         => $msmeTotalRev,
            ],
            'cogs' => [
                'retail_pos'    => $msmeCogs['retail'],
                'wholesale_pos' => $msmeCogs['wholesale'],
                'adc_canteen'   => $msmeCogs['adc'],
                'total'         => $msmeTotalCogs,
            ],
            'purchases' => [
                'central_po' => $centralPurchases,
                'regional'   => 0,
                'total'      => $centralPurchases,
            ],
            'gross_profit'      => $msmeGrossProfit,
            'gross_margin_pct'  => $msmeGrossMarginPct,
            'net_profit'        => $msmeGrossProfit,
            'orders_count'      => $msmeOrdersCount,
        ];

        // Data Structure for Regional (Local)
        $localData = [
            'revenue' => [
                'retail_pos'    => $localRev['retail'],
                'wholesale_pos' => $localRev['wholesale'],
                'adc_canteen'   => $localRev['adc'],
                'total'         => $localTotalRev,
            ],
            'cogs' => [
                'retail_pos'    => $localCogs['retail'],
                'wholesale_pos' => $localCogs['wholesale'],
                'adc_canteen'   => $localCogs['adc'],
                'total'         => $localTotalCogs,
            ],
            'purchases' => [
                'central_po' => 0,
                'regional'   => $regionalPurchases,
                'total'      => $regionalPurchases,
            ],
            'gross_profit'      => $localGrossProfit,
            'gross_margin_pct'  => $localGrossMarginPct,
            'net_profit'        => $localGrossProfit,
            'orders_count'      => $localOrdersCount,
        ];

        // Data Structure for Combined
        $combinedData = [
            'revenue'           => $combinedRev,
            'cogs'              => $combinedCogs,
            'purchases'         => [
                'central_po' => $centralPurchases,
                'regional'   => $regionalPurchases,
                'total'      => $centralPurchases + $regionalPurchases,
            ],
            'gross_profit'      => $combinedGrossProfit,
            'gross_margin_pct'  => $combinedGrossMarginPct,
            'net_profit'        => $combinedGrossProfit,
            'orders_count'      => $combinedOrdersCount,
        ];

        // Pick active dataset based on source filter
        if ($sourceFilter === 'msme') {
            $activeData = $msmeData;
        } elseif ($sourceFilter === 'local') {
            $activeData = $localData;
        } else {
            $activeData = $combinedData;
        }

        // MONTHLY BREAKDOWN
        $monthlyBreakdown = [];
        $periodStart = Carbon::parse($startDate)->startOfMonth();
        $periodEnd = Carbon::parse($endDate)->endOfMonth();

        $current = $periodStart->copy();
        while ($current->lte($periodEnd)) {
            $mStart = $current->copy()->startOfMonth();
            $mEnd = $current->copy()->endOfMonth();
            $mLabel = $current->format('M Y');

            $mRetail = DdcPosOrder::where('ddc_id', $ddcId)->where('mode', 'retail')->whereBetween('created_at', [$mStart, $mEnd])->with('items.inventory')->get();
            $mWholesale = DdcPosOrder::where('ddc_id', $ddcId)->where('mode', 'wholesale')->whereBetween('created_at', [$mStart, $mEnd])->with('items.inventory')->get();
            $mAdc = AdcOrder::where(function($q) use ($ddcId) { $q->where('assigned_ddc_id', $ddcId)->orWhere('selected_ddc_id', $ddcId); })
                ->whereNotIn('id', $linkedAdcOrderIds)->whereBetween('created_at', [$mStart, $mEnd])->with('items.product')->get();

            $mRev = 0; $mCogs = 0; $mOrders = 0;

            foreach ([$mRetail, $mWholesale] as $orderList) {
                foreach ($orderList as $o) {
                    $hasMatchingItem = false;
                    $ordSpecialDisc = (float) ($o->special_discount_amount ?? 0);

                    $ordGrossLineTotal = 0;
                    foreach ($o->items as $it) {
                        $qty = max(0, (float) $it->quantity - (float) ($it->returned_quantity ?? 0));
                        if ($qty <= 0) continue;
                        $uPrice = (float) ($it->unit_price > 0 ? $it->unit_price : ($it->quantity > 0 ? $it->total_price / $it->quantity : 0));
                        $ordGrossLineTotal += ($qty * $uPrice);
                    }

                    foreach ($o->items as $it) {
                        $inv = $it->inventory;
                        $isLocal = $inv ? (strtoupper($inv->inventory_source ?? 'MSME') === 'LOCAL') : ($it->localProduct ? true : false);
                        if ($sourceFilter === 'msme' && $isLocal) continue;
                        if ($sourceFilter === 'local' && !$isLocal) continue;

                        $qty = max(0, (float) $it->quantity - (float) ($it->returned_quantity ?? 0));
                        if ($qty <= 0) continue;

                        $uPrice = (float) ($it->unit_price > 0 ? $it->unit_price : ($it->quantity > 0 ? $it->total_price / $it->quantity : 0));
                        $grossLinePrice = $qty * $uPrice;
                        $itemDiscount = ($ordGrossLineTotal > 0 && $ordSpecialDisc > 0)
                            ? ($grossLinePrice / $ordGrossLineTotal) * min($ordSpecialDisc, $ordGrossLineTotal)
                            : 0;
                        $linePrice = max(0, $grossLinePrice - $itemDiscount);
                        $uCost = $inv ? ($inv->landing_cost > 0 ? $inv->landing_cost : ($inv->purchase_price > 0 ? $inv->purchase_price : $it->unit_price)) : $it->unit_price;

                        $mRev += $linePrice;
                        $mCogs += ($qty * $uCost);
                        $hasMatchingItem = true;
                    }
                    if ($hasMatchingItem) $mOrders++;
                }
            }

            foreach ($mAdc as $o) {
                $hasMatchingItem = false;
                foreach ($o->items as $it) {
                    $isLocal = !empty($it->ddc_local_product_id);
                    if ($sourceFilter === 'msme' && $isLocal) continue;
                    if ($sourceFilter === 'local' && !$isLocal) continue;

                    $qty = (float) $it->quantity;
                    $linePrice = (float) ($it->total > 0 ? $it->total : ($qty * $it->price));
                    $prod = $it->product;
                    $uCost = $prod ? ($prod->landing_cost > 0 ? $prod->landing_cost : ($prod->purchase_price > 0 ? $prod->purchase_price : $it->price)) : $it->price;

                    $mRev += $linePrice;
                    $mCogs += ($qty * $uCost);
                    $hasMatchingItem = true;
                }
                if ($hasMatchingItem) $mOrders++;
            }

            $mGrossProfit = $mRev - $mCogs;
            $mMarginPct = $mRev > 0 ? round(($mGrossProfit / $mRev) * 100, 2) : 0;

            $mPurchasesCentral = ($sourceFilter === 'local') ? 0 : PurchaseOrder::where('ddc_id', $ddcId)->whereBetween('created_at', [$mStart, $mEnd])->sum('total_amount');
            $mPurchasesLocal   = ($sourceFilter === 'msme') ? 0 : DdcLocalPurchase::where('ddc_id', $ddcId)->whereBetween('purchase_date', [$mStart, $mEnd])->sum('total_amount');
            $mPurchasesTotal   = $mPurchasesCentral + $mPurchasesLocal;

            $monthlyBreakdown[] = [
                'period'       => $mLabel,
                'orders_count' => $mOrders,
                'revenue'      => (float) $mRev,
                'cogs'         => (float) $mCogs,
                'gross_profit' => (float) $mGrossProfit,
                'margin_pct'   => (float) $mMarginPct,
                'purchases'    => (float) $mPurchasesTotal,
                'net_profit'   => (float) $mGrossProfit,
            ];

            $current->addMonth();
        }

        return array_merge($activeData, [
            'msme'               => $msmeData,
            'local'              => $localData,
            'combined'           => $combinedData,
            'source'             => $sourceFilter,
            'monthly_breakdown'  => $monthlyBreakdown,
            'total_orders_count' => $activeData['orders_count'],
        ]);
    }
}
