<?php

namespace App\Http\Controllers\ADC;

use App\Http\Controllers\Controller;
use App\Models\AdcPosOrder;
use App\Models\AdcPosOrderItem;
use App\Models\AdcOrder;
use App\Models\AdcLocalPurchase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ProfitLossReportController extends Controller
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
        $sourcingScope = strtolower($request->input('sourcing', $request->input('regional', 'all')));
        if (!in_array($sourcingScope, ['all', 'msme', 'regional'])) {
            $sourcingScope = 'all';
        }

        $filters = [
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'sourcing'   => $sourcingScope,
        ];

        $pnlData = $this->calculateProfitAndLoss($adcId, $startDate, $endDate, $sourcingScope);

        return view('adc.reports.pnl', compact('pnlData', 'filters'));
    }

    public function exportCsv(Request $request)
    {
        $adcId = $this->getAdcId();
        $startDate = $request->input('start_date', \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->input('end_date', \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d'));
        $sourcingScope = strtolower($request->input('sourcing', $request->input('regional', 'all')));
        if (!in_array($sourcingScope, ['all', 'msme', 'regional'])) {
            $sourcingScope = 'all';
        }

        $filters = [
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'sourcing'   => $sourcingScope,
        ];

        $pnlData = $this->calculateProfitAndLoss($adcId, $startDate, $endDate, $sourcingScope);
        $sourceLabel = strtoupper($sourcingScope);
        $filename = 'adc_pnl_report_' . strtolower($sourceLabel) . '_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function () use ($pnlData, $sourceLabel, $startDate, $endDate) {
            $file = fopen('php://output', 'w');

            fputcsv($file, ['', "ADC PROFIT & LOSS STATEMENT ({$sourceLabel} SCOPE)"]);
            fputcsv($file, ['Period:', $startDate . ' to ' . $endDate]);
            fputcsv($file, ['Sourcing Scope:', $sourceLabel]);
            fputcsv($file, ['Generated Date:', date('d-m-Y H:i:s')]);
            fputcsv($file, []);

            fputcsv($file, ['1. REVENUE / INCOME', 'AMOUNT (₹)']);
            fputcsv($file, ['Retail POS Sales (CSP)', number_format($pnlData['revenue']['retail_pos'], 2, '.', '')]);
            fputcsv($file, ['Wholesale POS Sales (MSP)', number_format($pnlData['revenue']['wholesale_pos'], 2, '.', '')]);
            fputcsv($file, ['TOTAL GROSS REVENUE', number_format($pnlData['revenue']['total'], 2, '.', '')]);
            fputcsv($file, []);

            fputcsv($file, ['2. COST OF GOODS SOLD (COGS)', 'AMOUNT (₹)']);
            fputcsv($file, ['Retail POS COGS', number_format($pnlData['cogs']['retail_pos'], 2, '.', '')]);
            fputcsv($file, ['Wholesale POS COGS', number_format($pnlData['cogs']['wholesale_pos'], 2, '.', '')]);
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

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function print(Request $request)
    {
        $adcId = $this->getAdcId();
        $startDate = $request->input('start_date', \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d'));
        $endDate   = $request->input('end_date', \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d'));
        $sourcingScope = strtolower($request->input('sourcing', $request->input('regional', 'all')));
        if (!in_array($sourcingScope, ['all', 'msme', 'regional'])) {
            $sourcingScope = 'all';
        }

        $filters = [
            'start_date' => $startDate,
            'end_date'   => $endDate,
            'sourcing'   => $sourcingScope,
        ];

        $pnlData = $this->calculateProfitAndLoss($adcId, $startDate, $endDate, $sourcingScope);

        return view('adc.reports.pnl-print', compact('pnlData', 'filters'));
    }

    private function calculateProfitAndLoss($adcId, $startDate, $endDate, $sourcingScope)
    {
        $retailRevenue = 0;
        $wholesaleRevenue = 0;
        $retailCogs = 0;
        $wholesaleCogs = 0;

        // POS Orders Query
        $posQuery = AdcPosOrder::where('adc_id', $adcId)
            ->with(['items.inventory', 'items.product', 'items.localProduct']);

        if ($startDate) $posQuery->whereDate('created_at', '>=', $startDate);
        if ($endDate) $posQuery->whereDate('created_at', '<=', $endDate);

        $posOrders = $posQuery->get();

        foreach ($posOrders as $order) {
            $isWholesale = strtolower($order->mode ?? '') === 'wholesale';
            $ordSpecialDisc = (float) ($order->special_discount_amount ?? 0);

            $ordGrossLineTotal = 0;
            foreach ($order->items as $item) {
                $itemQty = max(0, (float) $item->quantity - (float) ($item->returned_quantity ?? 0));
                if ($itemQty <= 0) continue;
                $uPrice = (float) ($item->unit_price > 0 ? $item->unit_price : ($item->quantity > 0 ? $item->total_price / $item->quantity : 0));
                $ordGrossLineTotal += ($itemQty * $uPrice);
            }

            foreach ($order->items as $item) {
                $inv = $item->inventory;
                $isRegional = ($inv && (strtoupper($inv->inventory_source ?? '') === 'LOCAL' || !empty($inv->adc_local_product_id)))
                    || !empty($item->localProduct)
                    || str_contains(strtolower($item->product_name ?? ''), 'local');

                if ($sourcingScope === 'msme' && $isRegional) continue;
                if ($sourcingScope === 'regional' && !$isRegional) continue;

                $itemQty = max(0, (float) $item->quantity - (float) ($item->returned_quantity ?? 0));
                if ($itemQty <= 0) continue;

                $uPrice = (float) ($item->unit_price > 0 ? $item->unit_price : ($item->quantity > 0 ? $item->total_price / $item->quantity : 0));
                $grossItemRevenue = $itemQty * $uPrice;
                $itemDiscount = ($ordGrossLineTotal > 0 && $ordSpecialDisc > 0)
                    ? ($grossItemRevenue / $ordGrossLineTotal) * min($ordSpecialDisc, $ordGrossLineTotal)
                    : 0;
                $itemRevenue = max(0, $grossItemRevenue - $itemDiscount);

                $costPrice = (float) ($inv?->purchase_price ?: ($item->cost_price ?: ($grossItemRevenue * 0.75 / max(1, $itemQty))));
                $itemCogs = $itemQty * $costPrice;

                if ($isWholesale) {
                    $wholesaleRevenue += $itemRevenue;
                    $wholesaleCogs += $itemCogs;
                } else {
                    $retailRevenue += $itemRevenue;
                    $retailCogs += $itemCogs;
                }
            }
        }

        $totalRevenue = $retailRevenue + $wholesaleRevenue;
        $totalCogs = $retailCogs + $wholesaleCogs;

        $grossProfit = $totalRevenue - $totalCogs;
        $grossMarginPct = $totalRevenue > 0 ? ($grossProfit / $totalRevenue) * 100 : 0;
        $netProfit = $grossProfit;

        // Central PO Purchases (MSME)
        $centralPoTotal = 0;
        if ($sourcingScope === 'all' || $sourcingScope === 'msme') {
            $adcPoQuery = AdcOrder::where('adc_id', $adcId);
            if ($startDate) $adcPoQuery->whereDate('created_at', '>=', $startDate);
            if ($endDate) $adcPoQuery->whereDate('created_at', '<=', $endDate);

            $centralPoTotal = (float) $adcPoQuery->sum('total_amount');
        }

        // Regional Local Stock Purchases (Regional)
        $regionalPurchaseTotal = 0;
        if ($sourcingScope === 'all' || $sourcingScope === 'regional') {
            $localPurchQuery = AdcLocalPurchase::where('adc_id', $adcId);
            if ($startDate) $localPurchQuery->whereDate('purchase_date', '>=', $startDate);
            if ($endDate) $localPurchQuery->whereDate('purchase_date', '<=', $endDate);

            $regionalPurchaseTotal = (float) $localPurchQuery->sum('total_amount');
        }

        $totalPurchases = $centralPoTotal + $regionalPurchaseTotal;

        return [
            'revenue' => [
                'retail_pos'    => $retailRevenue,
                'wholesale_pos' => $wholesaleRevenue,
                'total'         => $totalRevenue,
            ],
            'cogs' => [
                'retail_pos'    => $retailCogs,
                'wholesale_pos' => $wholesaleCogs,
                'total'         => $totalCogs,
            ],
            'gross_profit'     => $grossProfit,
            'gross_margin_pct' => $grossMarginPct,
            'net_profit'        => $netProfit,
            'purchases' => [
                'central_po' => $centralPoTotal,
                'regional'   => $regionalPurchaseTotal,
                'total'      => $totalPurchases,
            ]
        ];
    }
}
