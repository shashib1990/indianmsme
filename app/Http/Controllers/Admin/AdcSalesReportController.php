<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdcOrder;
use App\Models\AdcOrderItem;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AdcSalesReportController extends Controller
{
    /**
     * Display product-wise sales report for ADC (Paid transactions from adc_orders)
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 25);
        if (!in_array($perPage, [15, 25, 50, 100])) {
            $perPage = 25;
        }

        // Base query strictly filtered for paid adc_orders
        $query = $this->buildFilteredQuery($request);

        // Calculate Summary Statistics from filtered query
        $totalItemsCount = (clone $query)->sum('adc_order_items.quantity');
        $totalSalesAmount = (clone $query)->sum('adc_order_items.total');

        // Distinct orders & distinct ADCs count
        $orderStatsQuery = clone $query;
        $orderStatsQuery->getQuery()->columns = null;
        $orderStats = $orderStatsQuery
            ->selectRaw('COUNT(DISTINCT adc_order_items.adc_order_id) as total_orders, COUNT(DISTINCT adc_orders.adc_id) as total_adcs')
            ->first();

        $totalOrdersCount = $orderStats->total_orders ?? 0;
        $activeAdcsCount = $orderStats->total_adcs ?? 0;

        // Eager load relationships for paginated items
        $items = $query
            ->with([
                'adcOrder.adc.adcProfile',
                'adcOrder.adc.profile',
                'adcOrder.invoice',
                'adcOrder.selectedDdc.ddcProfile',
            ])
            ->orderBy('adc_order_items.id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        // Build PO Invoice map for fast resolution without N+1 queries
        $poInvoiceMap = $this->buildPoInvoiceMap($items->getCollection());

        // Transform item rows with computed tax and profile fields
        $items->getCollection()->transform(function ($item) use ($poInvoiceMap) {
            return $this->formatItemRow($item, $poInvoiceMap);
        });

        // List of ADCs for filter dropdown
        $adcList = User::where('role', 'adc')
            ->whereHas('adcProfile')
            ->with('adcProfile')
            ->orderBy('name')
            ->get();

        return view('admin.reports.adc-sales', compact(
            'items',
            'totalSalesAmount',
            'totalOrdersCount',
            'totalItemsCount',
            'activeAdcsCount',
            'adcList'
        ));
    }

    /**
     * Export Product-wise Sales Report for ADC (Excel .xlsx or CSV)
     */
    public function export(Request $request)
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $format = strtolower($request->input('format', 'xlsx'));
        $query = $this->buildFilteredQuery($request);

        $items = $query
            ->with([
                'adcOrder.adc.adcProfile',
                'adcOrder.adc.profile',
                'adcOrder.invoice',
                'adcOrder.selectedDdc.ddcProfile',
            ])
            ->orderBy('adc_order_items.id', 'desc')
            ->get();

        // Build PO Invoice map for fast resolution without N+1 queries
        $poInvoiceMap = $this->buildPoInvoiceMap($items);

        $headersList = [
            'S.No',
            'Invoice Date',
            'State',
            'Gst Number',
            'Invoice No',
            'Depot Code',
            'Canteen Name',
            'Product Name',
            'Product Code',
            'HSN Code',
            'Brand Name',
            'Qty',
            'MRP',
            'CSP',
            'Tax Rate',
            'Taxable Amount',
            'SGST',
            'CGST',
            'IGST',
            'Total Amount',
            'Mfg Date',
            'Exp Date',
            'Payment Mode',
        ];

        $serial = 1;
        $rowsData = [];
        $sumQty = 0;
        $sumTaxable = 0;
        $sumSgst = 0;
        $sumCgst = 0;
        $sumIgst = 0;
        $sumTotal = 0;

        foreach ($items as $item) {
            $formatted = $this->formatItemRow($item, $poInvoiceMap);

            $sumQty += $formatted->qty;
            $sumTaxable += $formatted->taxable_amount;
            $sumSgst += $formatted->sgst;
            $sumCgst += $formatted->cgst;
            $sumIgst += $formatted->igst;
            $sumTotal += $formatted->total_amount;

            $rowsData[] = [
                $serial++,
                $formatted->invoice_date,
                $formatted->state,
                $formatted->gst_number,
                $formatted->invoice_no,
                $formatted->depot_code,
                $formatted->customer_name,
                $formatted->product_name,
                $formatted->product_code,
                $formatted->hsn_code,
                $formatted->brand_name,
                $formatted->qty,
                $formatted->mrp,
                $formatted->csp,
                $formatted->tax_rate,
                $formatted->taxable_amount,
                $formatted->sgst,
                $formatted->cgst,
                $formatted->igst,
                $formatted->total_amount,
                $formatted->mfg_date,
                $formatted->exp_date,
                $formatted->payment_mode,
            ];
        }

        $filenameBase = 'adc_product_wise_sale_reports_' . date('Ymd_His');

        if ($format === 'csv') {
            return response()->streamDownload(function () use ($headersList, $rowsData) {
                $output = fopen('php://output', 'w');
                // UTF-8 BOM for proper Excel encoding support
                fputs($output, "\xEF\xBB\xBF");
                fputcsv($output, $headersList);

                foreach ($rowsData as $row) {
                    fputcsv($output, $row);
                }

                fclose($output);
            }, $filenameBase . '.csv', [
                'Content-Type'        => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filenameBase . '.csv"',
                'Pragma'              => 'no-cache',
                'Expires'             => '0',
            ]);
        }

        // Default: Excel .xlsx export
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('ADC Product Wise Sales');

        // Set Headers
        $sheet->fromArray($headersList, null, 'A1');

        // Style Header Row
        $sheet->getStyle('A1:W1')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $sheet->getStyle('A1:W1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF059669'); // emerald-600
        $sheet->getStyle('A1:W1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Populate Data Rows
        if (!empty($rowsData)) {
            $sheet->fromArray($rowsData, null, 'A2');
            $lastRow = count($rowsData) + 1;

            // Numeric Column Formatting
            $sheet->getStyle('L2:L' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.000'); // Qty
            $sheet->getStyle('M2:T' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');   // Prices & Tax

            // Summary Totals Row
            $summaryRow = $lastRow + 2;
            $sheet->setCellValue('A' . $summaryRow, 'TOTAL');
            $sheet->setCellValue('B' . $summaryRow, count($rowsData) . ' Items Sold');
            $sheet->setCellValue('L' . $summaryRow, $sumQty);
            $sheet->setCellValue('P' . $summaryRow, $sumTaxable);
            $sheet->setCellValue('Q' . $summaryRow, $sumSgst);
            $sheet->setCellValue('R' . $summaryRow, $sumCgst);
            $sheet->setCellValue('S' . $summaryRow, $sumIgst);
            $sheet->setCellValue('T' . $summaryRow, $sumTotal);
            $sheet->setCellValue('W' . $summaryRow, 'PAID ONLY');

            $sheet->getStyle('A' . $summaryRow . ':W' . $summaryRow)->getFont()->setBold(true);
            $sheet->getStyle('A' . $summaryRow . ':W' . $summaryRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');
            $sheet->getStyle('L' . $summaryRow)->getNumberFormat()->setFormatCode('#,##0.000');
            $sheet->getStyle('P' . $summaryRow . ':T' . $summaryRow)->getNumberFormat()->setFormatCode('#,##0.00');
        }

        // Standard column widths
        $columnWidths = [
            'A' => 8,   // S.No
            'B' => 14,  // Invoice Date
            'C' => 18,  // State
            'D' => 20,  // Gst Number
            'E' => 18,  // Invoice No
            'F' => 14,  // Depot Code
            'G' => 24,  // Canteen Name
            'H' => 32,  // Product Name
            'I' => 16,  // Product Code
            'J' => 14,  // HSN Code
            'K' => 16,  // Brand Name
            'L' => 10,  // Qty
            'M' => 12,  // MRP
            'N' => 12,  // CSP
            'O' => 10,  // Tax Rate
            'P' => 14,  // Taxable Amount
            'Q' => 12,  // SGST
            'R' => 12,  // CGST
            'S' => 12,  // IGST
            'T' => 14,  // Total Amount
            'U' => 14,  // Mfg Date
            'V' => 14,  // Exp Date
            'W' => 14,  // Payment Mode
        ];
        foreach ($columnWidths as $col => $width) {
            $sheet->getColumnDimension($col)->setWidth($width);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, $filenameBase . '.xlsx', [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Build base query on AdcOrderItem strictly filtering for Paid adc_orders
     */
    private function buildFilteredQuery(Request $request)
    {
        $query = AdcOrderItem::query()
            ->join('adc_orders', 'adc_order_items.adc_order_id', '=', 'adc_orders.id')
            ->whereRaw('LOWER(adc_orders.payment_status) = ?', ['paid'])
            ->select('adc_order_items.*');

        // Search filter (Product name, Product code, HSN, batch, order_number, invoice_number, ADC info)
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('adc_order_items.product_name', 'like', "%{$search}%")
                  ->orWhere('adc_order_items.product_code', 'like', "%{$search}%")
                  ->orWhere('adc_order_items.hsn_code', 'like', "%{$search}%")
                  ->orWhere('adc_order_items.batch_number', 'like', "%{$search}%")
                  ->orWhere('adc_orders.order_number', 'like', "%{$search}%")
                  ->orWhere('adc_orders.advisor_code', 'like', "%{$search}%")
                  ->orWhere('adc_orders.received_invoice_number', 'like', "%{$search}%")
                  ->orWhereHas('adcOrder.adc', function ($adcQ) use ($search) {
                      $adcQ->where('name', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%")
                           ->orWhereHas('adcProfile', function ($apQ) use ($search) {
                               $apQ->where('adc_code', 'like', "%{$search}%")
                                   ->orWhere('company_name', 'like', "%{$search}%")
                                   ->orWhere('trade_name', 'like', "%{$search}%");
                           });
                  });
            });
        }

        // ADC Canteen filter
        if ($adcId = $request->input('adc_id')) {
            if ($adcId !== 'all') {
                $query->where('adc_orders.adc_id', $adcId);
            }
        }

        // Payment Method filter (wallet, online, cash, etc.)
        if ($paymentMethod = $request->input('payment_mode')) {
            if ($paymentMethod !== 'all') {
                $query->whereRaw('LOWER(adc_orders.payment_method) = ?', [strtolower($paymentMethod)]);
            }
        }

        // Date range filter
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('adc_orders.created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('adc_orders.created_at', '<=', $dateTo);
        }

        return $query;
    }

    /**
     * Preload PO invoices map to eliminate N+1 queries when looking up invoice numbers
     */
    private function buildPoInvoiceMap($items): array
    {
        $poIds = $items->pluck('purchase_order_id')->filter()->unique()->values()->all();
        if (empty($poIds)) {
            return [];
        }
        return \App\Models\Invoice::whereIn('purchase_order_id', $poIds)
            ->whereNotNull('invoice_number')
            ->pluck('invoice_number', 'purchase_order_id')
            ->all();
    }

    /**
     * Compute and format all 23 fields for an ADC item row matching product_wise_retail_sale_reports format
     */
    private function formatItemRow($item, array $poInvoiceMap = []): object
    {
        $order = $item->adcOrder;
        $adc = $order?->adc;
        $profile = $adc?->adcProfile ?: $adc?->profile;

        // Invoice date
        $invoiceDate = $order?->created_at ? Carbon::parse($order->created_at)->format('d-m-Y') : '';

        // State (uppercase)
        $state = strtoupper(trim($order?->delivery_state ?: ($profile?->state ?: 'UTTAR PRADESH')));

        // GST Number
        $gstNumber = trim($profile?->gst_number ?: ($adc?->gst_number ?: 'N/A'));

        // Invoice Number (fast lookup without N+1 query)
        $invoiceNo = $order?->invoice?->invoice_number 
            ?: ($order?->received_invoice_number 
            ?: ($poInvoiceMap[$item->purchase_order_id] ?? ($order?->order_number ?: 'N/A')));

        // Depot Code / ADC Code
        $depotCode = $profile?->adc_code ?: ($profile?->code ?: ($order?->advisor_code ?: ($adc ? 'ADC' . $adc->id : 'N/A')));

        // Customer Name / Canteen Name
        $canteenName = trim($profile?->company_name ?: ($profile?->trade_name ?: ($adc?->name ?: 'ADC Partner')));

        // Product Name & Code
        $productName = trim($item->product_name ?: 'N/A');
        $productCode = trim($item->product_code ?: 'N/A');

        // HSN Code
        $hsnCode = trim($item->hsn_code ?: 'N/A');

        // Brand Name
        $brandName = trim($item->brand_name ?: 'NA');

        // Qty, MRP, CSP (Price)
        $qty = (float) $item->quantity;
        $mrp = (float) ($item->mrp ?: ($item->price ?: 0));
        $csp = (float) ($item->price ?: ($item->csp ?: $item->seller_price ?: 0));

        // Tax Rate
        $taxRate = (float) ($item->gst_rate ?: 5.0);

        // Total Amount
        $totalAmount = (float) ($item->total ?: round($qty * $csp, 2));

        // Taxable Amount & Taxes
        if (!empty($item->taxable_amount) && (float) $item->taxable_amount > 0) {
            $taxableAmount = (float) $item->taxable_amount;
            $totalTax = max(0, round($totalAmount - $taxableAmount, 2));
        } elseif ($taxRate > 0) {
            $taxableAmount = round($totalAmount / (1 + ($taxRate / 100)), 2);
            $totalTax = max(0, round($totalAmount - $taxableAmount, 2));
        } else {
            $taxableAmount = $totalAmount;
            $totalTax = 0.0;
        }

        // Intra-state / Inter-state tax split
        $sgst = round($totalTax / 2, 2);
        $cgst = round($totalTax / 2, 2);
        $igst = 0.0;

        // Mfg Date & Exp Date
        $mfgDate = $item->mfg_date ? Carbon::parse($item->mfg_date)->format('d-m-Y') : '';
        $expDate = $item->expire_date ? Carbon::parse($item->expire_date)->format('d-m-Y') : '';

        // Payment Mode
        $paymentMode = strtolower($order?->payment_method ?: 'wallet');

        return (object) [
            'id'             => $item->id,
            'adc_order_id'   => $item->adc_order_id,
            'invoice_date'   => $invoiceDate,
            'state'          => $state,
            'gst_number'     => $gstNumber,
            'invoice_no'     => $invoiceNo,
            'depot_code'     => $depotCode,
            'depot_name'     => $canteenName,
            'customer_name'  => $canteenName,
            'product_name'   => $productName,
            'product_code'   => $productCode,
            'hsn_code'       => $hsnCode,
            'brand_name'     => $brandName,
            'qty'            => $qty,
            'mrp'            => $mrp,
            'csp'            => $csp,
            'tax_rate'       => $taxRate,
            'taxable_amount' => $taxableAmount,
            'sgst'           => $sgst,
            'cgst'           => $cgst,
            'igst'           => $igst,
            'total_amount'   => $totalAmount,
            'mfg_date'       => $mfgDate,
            'exp_date'       => $expDate,
            'payment_mode'   => $paymentMode,
            'status'         => $order?->status ?: 'Completed',
        ];
    }
}
