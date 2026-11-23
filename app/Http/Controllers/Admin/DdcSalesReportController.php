<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DdcPosOrder;
use App\Models\DdcPosOrderItem;
use App\Models\Product;
use App\Models\DdcLocalProduct;
use App\Models\User;
use Illuminate\Http\Request;
use Carbon\Carbon;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class DdcSalesReportController extends Controller
{
    /**
     * Display product-wise sales report for DDC (Paid transactions only)
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 25);
        if (!in_array($perPage, [15, 25, 50, 100])) {
            $perPage = 25;
        }

        // Base query strictly filtered for paid orders
        $query = $this->buildFilteredQuery($request);

        // Calculate Summary Statistics from filtered query
        $totalItemsCount = (clone $query)->sum('ddc_pos_order_items.quantity');
        $totalSalesAmount = (clone $query)->sum('ddc_pos_order_items.total_price');

        // Distinct orders & distinct DDCs count
        $orderStatsQuery = clone $query;
        $orderStatsQuery->getQuery()->columns = null;
        $orderStats = $orderStatsQuery
            ->selectRaw('COUNT(DISTINCT ddc_pos_order_items.pos_order_id) as total_orders, COUNT(DISTINCT ddc_pos_orders.ddc_id) as total_ddcs')
            ->first();

        $totalOrdersCount = $orderStats->total_orders ?? 0;
        $activeDdcsCount = $orderStats->total_ddcs ?? 0;

        // Eager load relationships for paginated items
        $items = $query
            ->with([
                'order.ddc.ddcProfile',
                'order.invoice',
                'order.customer',
                'product.brand',
                'localProduct.brand',
                'inventory.product.brand',
                'inventory.localProduct.brand',
            ])
            ->orderBy('ddc_pos_order_items.id', 'desc')
            ->paginate($perPage)
            ->withQueryString();

        // Build batch lookup for fast resolution (No N+1 queries)
        $lookup = $this->buildProductLookup($items->getCollection());

        // Transform item rows with computed tax and profile fields
        $items->getCollection()->transform(function ($item) use ($lookup) {
            return $this->formatItemRow($item, $lookup);
        });

        // List of DDCs for filter dropdown
        $ddcList = User::where('role', 'ddc')
            ->whereHas('ddcProfile')
            ->with('ddcProfile')
            ->orderBy('name')
            ->get();

        return view('admin.reports.ddc-sales', compact(
            'items',
            'totalSalesAmount',
            'totalOrdersCount',
            'totalItemsCount',
            'activeDdcsCount',
            'ddcList'
        ));
    }

    /**
     * Export Product-wise Sales Report (Excel .xlsx or CSV) matching product_wise_retail_sale_reports format
     */
    public function export(Request $request)
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $format = strtolower($request->input('format', 'xlsx'));
        $query = $this->buildFilteredQuery($request);

        $items = $query
            ->with([
                'order.ddc.ddcProfile',
                'order.invoice',
                'order.customer',
                'product.brand',
                'localProduct.brand',
            ])
            ->orderBy('ddc_pos_order_items.id', 'desc')
            ->get();

        // Preload all product codes in 1 single bulk query to avoid thousands of individual queries
        $lookup = $this->buildProductLookup($items);

        $headersList = [
            'S.No',
            'Invoice Date',
            'State',
            'Gst Number',
            'Invoice No',
            'Depot Code',
            'Customer Name',
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
            $formatted = $this->formatItemRow($item, $lookup);

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

        $filenameBase = 'product_wise_retail_sale_reports_' . date('Ymd_His');

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
        $sheet->setTitle('Product Wise Sales');

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

        // Standard column widths (much faster and more reliable than setAutoSize on large datasets)
        $columnWidths = [
            'A' => 8,   // S.No
            'B' => 14,  // Invoice Date
            'C' => 18,  // State
            'D' => 20,  // Gst Number
            'E' => 18,  // Invoice No
            'F' => 14,  // Depot Code
            'G' => 22,  // Customer Name
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
     * Build base query on DdcPosOrderItem strictly filtering for Paid orders
     */
    private function buildFilteredQuery(Request $request)
    {
        $query = DdcPosOrderItem::query()
            ->join('ddc_pos_orders', 'ddc_pos_order_items.pos_order_id', '=', 'ddc_pos_orders.id')
            ->whereRaw('LOWER(ddc_pos_orders.payment_status) = ?', ['paid'])
            ->select('ddc_pos_order_items.*');

        // Search filter (Product name, Product code, barcode, batch, order_no, customer, DDC info)
        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('ddc_pos_order_items.product_name', 'like', "%{$search}%")
                  ->orWhere('ddc_pos_order_items.product_code', 'like', "%{$search}%")
                  ->orWhere('ddc_pos_order_items.barcode', 'like', "%{$search}%")
                  ->orWhere('ddc_pos_order_items.batch_number', 'like', "%{$search}%")
                  ->orWhere('ddc_pos_orders.order_no', 'like', "%{$search}%")
                  ->orWhere('ddc_pos_orders.customer_name', 'like', "%{$search}%")
                  ->orWhere('ddc_pos_orders.customer_mobile', 'like', "%{$search}%")
                  ->orWhereHas('order.ddc', function ($ddcQ) use ($search) {
                      $ddcQ->where('name', 'like', "%{$search}%")
                           ->orWhere('email', 'like', "%{$search}%")
                           ->orWhereHas('ddcProfile', function ($dpQ) use ($search) {
                               $dpQ->where('ddc_code', 'like', "%{$search}%")
                                   ->orWhere('company_name', 'like', "%{$search}%")
                                   ->orWhere('trade_name', 'like', "%{$search}%");
                           });
                  });
            });
        }

        // DDC Depot filter
        if ($ddcId = $request->input('ddc_id')) {
            if ($ddcId !== 'all') {
                $query->where('ddc_pos_orders.ddc_id', $ddcId);
            }
        }

        // Mode filter (retail / wholesale)
        if ($mode = $request->input('mode')) {
            if ($mode !== 'all') {
                $query->where('ddc_pos_orders.mode', $mode);
            }
        }

        // Payment Mode filter (cash, upi, card, wallet)
        if ($paymentMode = $request->input('payment_mode')) {
            if ($paymentMode !== 'all') {
                $query->whereRaw('LOWER(ddc_pos_orders.payment_mode) = ?', [strtolower($paymentMode)]);
            }
        }

        // Date range filter
        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('ddc_pos_orders.created_at', '>=', $dateFrom);
        }
        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('ddc_pos_orders.created_at', '<=', $dateTo);
        }

        return $query;
    }

    /**
     * Preload product and local product info in bulk to eliminate N+1 queries during iteration
     */
    private function buildProductLookup($items): array
    {
        $codes = $items->pluck('product_code')->filter()->unique()->values()->all();
        if (empty($codes)) {
            return ['msme' => collect(), 'local' => collect()];
        }

        $msme = Product::whereIn('product_code', $codes)
            ->with('brand')
            ->get()
            ->keyBy('product_code');

        $local = DdcLocalProduct::whereIn('local_product_code', $codes)
            ->with('brand')
            ->get()
            ->keyBy('local_product_code');

        return [
            'msme'  => $msme,
            'local' => $local,
        ];
    }

    /**
     * Compute and format all 23 fields for an item row matching product_wise_retail_sale_reports format
     * Ultra fast: uses raw attributes and preloaded lookup map to avoid any runtime DB queries
     */
    private function formatItemRow($item, array $lookup = []): object
    {
        $order = $item->order;
        $ddc = $order?->ddc;
        $profile = $ddc?->ddcProfile ?: $ddc?->profile;

        // Invoice date
        $invoiceDate = $order?->created_at ? Carbon::parse($order->created_at)->format('d-m-Y') : '';

        // State (uppercase)
        $state = strtoupper(trim($profile?->state ?: ($profile?->adpl_state ?: 'UTTAR PRADESH')));

        // GST Number
        $gstNumber = trim($profile?->ddc_gst_number ?: ($profile?->adpl_gst_number ?: 'N/A'));

        // Invoice Number
        $invoiceNo = $order?->invoice?->invoice_number ?: ($order?->order_no ?: 'N/A');

        // Depot Code
        $depotCode = $profile?->ddc_code ?: 'N/A';

        // Customer Name
        $customerName = trim($order?->customer_name ?: ($order?->customer?->name ?: ($order?->customer_mobile ?: 'N/A')));

        // Product Name & Code
        $productName = trim($item->product_name ?: 'N/A');
        $productCode = trim($item->product_code ?: '');

        // HSN Code (resolved without N+1 query: check raw original first, then preloaded lookup)
        $rawHsn = $item->getRawOriginal('hsn_code');
        $hsnCode = 'N/A';
        if (!empty($rawHsn) && strtoupper(trim($rawHsn)) !== 'N/A') {
            $hsnCode = trim($rawHsn);
        } elseif ($item->relationLoaded('product') && $item->product && !empty($item->product->hsn_code)) {
            $hsnCode = trim($item->product->hsn_code);
        } elseif (isset($lookup['msme'][$productCode]) && !empty($lookup['msme'][$productCode]->hsn_code)) {
            $hsnCode = trim($lookup['msme'][$productCode]->hsn_code);
        } elseif (isset($lookup['local'][$productCode]) && !empty($lookup['local'][$productCode]->hsn_code)) {
            $hsnCode = trim($lookup['local'][$productCode]->hsn_code);
        }

        // Brand Name (resolved without N+1 query: check relation first, then preloaded lookup)
        $brandName = 'NA';
        if ($item->relationLoaded('product') && $item->product?->brand?->name) {
            $brandName = $item->product->brand->name;
        } elseif (isset($lookup['msme'][$productCode]) && $lookup['msme'][$productCode]->brand?->name) {
            $brandName = $lookup['msme'][$productCode]->brand->name;
        } elseif (isset($lookup['local'][$productCode]) && $lookup['local'][$productCode]->brand?->name) {
            $brandName = $lookup['local'][$productCode]->brand->name;
        }

        // Qty, MRP, CSP
        $qty = (float) $item->quantity;
        $mrp = (float) ($item->mrp ?: $item->unit_price);
        $csp = (float) $item->unit_price;

        // Tax Rate
        $taxRate = 5.0;
        if (isset($lookup['msme'][$productCode]) && isset($lookup['msme'][$productCode]->gst_rate)) {
            $taxRate = (float) $lookup['msme'][$productCode]->gst_rate;
        } elseif (isset($lookup['local'][$productCode]) && isset($lookup['local'][$productCode]->gst_rate)) {
            $taxRate = (float) $lookup['local'][$productCode]->gst_rate;
        } elseif ($item->relationLoaded('product') && isset($item->product?->gst_rate)) {
            $taxRate = (float) $item->product->gst_rate;
        }

        // Total Amount
        $totalAmount = (float) ($item->total_price ?: round($qty * $csp, 2));

        // Tax calculations (CSP is retail inclusive of GST)
        if ($taxRate > 0) {
            $taxableAmount = round($totalAmount / (1 + ($taxRate / 100)), 2);
            $totalTax = round($totalAmount - $taxableAmount, 2);
        } else {
            $taxableAmount = $totalAmount;
            $totalTax = 0.0;
        }

        // Intra-state POS retail: 50% SGST, 50% CGST, 0 IGST
        $sgst = round($totalTax / 2, 2);
        $cgst = round($totalTax / 2, 2);
        $igst = 0.0;

        // Mfg Date & Exp Date
        $mfgDate = $item->mfg_date ? Carbon::parse($item->mfg_date)->format('d-m-Y') : '';
        $expDate = $item->expire_date ? Carbon::parse($item->expire_date)->format('d-m-Y') : '';

        // Payment Mode
        $paymentMode = strtolower($order?->payment_mode ?: 'cash');

        return (object) [
            'id'             => $item->id,
            'pos_order_id'   => $item->pos_order_id,
            'invoice_date'   => $invoiceDate,
            'state'          => $state,
            'gst_number'     => $gstNumber,
            'invoice_no'     => $invoiceNo,
            'depot_code'     => $depotCode,
            'depot_name'     => $ddc?->name ?: ($profile?->company_name ?: 'DDC Depot'),
            'customer_name'  => $customerName,
            'product_name'   => $productName,
            'product_code'   => $productCode,
            'barcode'        => $item->barcode,
            'batch_number'   => $item->batch_number,
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
            'mode'           => $order?->mode ?: 'retail',
        ];
    }
}
