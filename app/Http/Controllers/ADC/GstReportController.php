<?php

namespace App\Http\Controllers\ADC;

use App\Http\Controllers\Controller;
use App\Models\AdcPosOrder;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class GstReportController extends Controller
{
    private function getAdcId()
    {
        return Auth::guard('adc')->id() ?: (Auth::id() ?: 0);
    }

    /**
     * Display GST Outward Supplies Report
     */
    public function index(Request $request)
    {
        $adcId = $this->getAdcId();
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        $filters = $request->only(['start_date', 'end_date', 'tax_rate', 'tax_type', 'search']);
        $filters['start_date'] = $startDate;
        $filters['end_date']   = $endDate;
        $filters['tax_rate'] = $request->input('tax_rate', 'all');
        $filters['tax_type'] = $request->input('tax_type', 'all'); // all, intra, inter

        $gstRecords = $this->getGstReportRecords($adcId, $request);

        $page = Paginator::resolveCurrentPage() ?: 1;
        $perPage = 25;
        $paginatedRecords = new LengthAwarePaginator(
            $gstRecords->forPage($page, $perPage),
            $gstRecords->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $summaryStats = [
            'total_rows'     => $gstRecords->count(),
            'total_quantity' => (float) $gstRecords->sum('quantity'),
            'total_taxable'  => (float) $gstRecords->sum('taxable_amount'),
            'total_cgst'     => (float) $gstRecords->sum('cgst_amount'),
            'total_sgst'     => (float) $gstRecords->sum('sgst_amount'),
            'total_igst'     => (float) $gstRecords->sum('igst_amount'),
            'total_tax'      => (float) ($gstRecords->sum('cgst_amount') + $gstRecords->sum('sgst_amount') + $gstRecords->sum('igst_amount')),
            'total_amount'   => (float) $gstRecords->sum('total_amount'),
        ];

        $adcUser = User::with(['adcProfile', 'profile'])->find($adcId);
        $adcCode = $adcUser?->adcProfile?->adc_code ?: ($adcUser?->profile?->adc_code ?: 'ADC');

        return view('adc.reports.gst', compact('paginatedRecords', 'summaryStats', 'filters', 'adcUser', 'adcCode'));
    }

    /**
     * Export GST Report matching the exact Excel format
     */
    public function exportCsv(Request $request)
    {
        $adcId = $this->getAdcId();
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');

        $gstRecords = $this->getGstReportRecords($adcId, $request);
        $filename = 'ADC_GST_Report_' . date('Ymd_His') . '.csv';

        $headers = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
            'Pragma'              => 'no-cache',
            'Cache-Control'       => 'must-revalidate, post-check=0, pre-check=0',
            'Expires'             => '0',
        ];

        $callback = function () use ($gstRecords) {
            $file = fopen('php://output', 'w');
            // Add UTF-8 BOM for Microsoft Excel compatibility
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));

            // Exact column headers matching the user's Excel image
            fputcsv($file, [
                'Invoice Date',
                'Invoice No',
                'Product Name',
                'GSTIN OF RECEIPIENT',
                'RECEIVER NAME',
                'PLACE OF SUPPLY',
                'Quantity',
                'Tax Rate',
                'HSN CODE',
                'Taxable Amount',
                'SGST',
                'CGST',
                'IGST',
                'Total AMOUNT'
            ]);

            foreach ($gstRecords as $row) {
                fputcsv($file, [
                    $row['invoice_date'],
                    $row['invoice_no'],
                    $row['product_name'] ?? '',
                    $row['recipient_gstin'],
                    $row['receiver_name'],
                    $row['place_of_supply'],
                    (float) $row['quantity'] == (int) $row['quantity'] ? (int) $row['quantity'] : number_format($row['quantity'], 2, '.', ''),
                    $row['tax_rate'],
                    $row['hsn_code'],
                    number_format($row['taxable_amount'], 2, '.', ''),
                    number_format($row['sgst_amount'], 2, '.', ''),
                    number_format($row['cgst_amount'], 2, '.', ''),
                    number_format($row['igst_amount'], 2, '.', ''),
                    number_format($row['total_amount'], 2, '.', ''),
                ]);
            }

            // Total summary row at the bottom
            fputcsv($file, [
                'TOTAL',
                '',
                '',
                '',
                '',
                '',
                (float) $gstRecords->sum('quantity') == (int) $gstRecords->sum('quantity') ? (int) $gstRecords->sum('quantity') : number_format($gstRecords->sum('quantity'), 2, '.', ''),
                '',
                '',
                number_format($gstRecords->sum('taxable_amount'), 2, '.', ''),
                number_format($gstRecords->sum('sgst_amount'), 2, '.', ''),
                number_format($gstRecords->sum('cgst_amount'), 2, '.', ''),
                number_format($gstRecords->sum('igst_amount'), 2, '.', ''),
                number_format($gstRecords->sum('total_amount'), 2, '.', ''),
            ]);

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Send GST Report via Email with uploaded Excel/CSV attachment
     */
    public function sendEmail(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'recipient_email' => 'required|email:filter',
            'report_file'     => 'required|file|max:10240|mimes:csv,txt,xls,xlsx',
            'remarks'         => 'nullable|string|max:500',
        ], [
            'recipient_email.required' => 'Recipient email is required.',
            'recipient_email.email'    => 'Please enter a valid email address.',
            'report_file.required'     => 'Please attach the GST report file (Excel/CSV).',
            'report_file.mimes'        => 'The report must be a file of type: CSV, XLS, XLSX.',
            'report_file.max'          => 'The report file size cannot exceed 10MB.',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => $validator->errors()->first(),
                'errors'  => $validator->errors(),
            ], 422);
        }

        try {
            $adcId = $this->getAdcId();
            $adcUser = User::with(['adcProfile', 'profile'])->find($adcId);
            $adcCode = $adcUser?->adcProfile?->adc_code ?: ($adcUser?->profile?->adc_code ?: 'ADC');
            $adcName = $adcUser?->name ?: 'ADC Depot';
            $recipientEmail = trim($request->input('recipient_email'));
            $remarks = trim($request->input('remarks', ''));

            $file = $request->file('report_file');
            $fileName = $file->getClientOriginalName();
            $fileData = file_get_contents($file->getRealPath());
            $mimeType = $file->getClientMimeType() ?: 'application/octet-stream';

            $subject = "GST Outward Supplies Report - {$adcCode} ({$adcName})";

            $htmlBody = "
                <div style='font-family: Arial, sans-serif; line-height: 1.6; color: #333;'>
                    <h3 style='color: #2563eb; margin-bottom: 5px;'>Indian MSME - GST Outward Supplies Report</h3>
                    <p style='color: #666; font-size: 13px; margin-top: 0;'>Automated Report Dispatch</p>
                    <p>Hello,</p>
                    <p>Please find attached the GST Outward Supplies Report for <strong>" . e($adcName) . "</strong> (<strong>" . e($adcCode) . "</strong>).</p>
                    <div style='background: #f8fafc; border-left: 4px solid #2563eb; padding: 12px 15px; margin: 15px 0;'>
                        <p style='margin: 3px 0;'><strong>ADC Name:</strong> " . e($adcName) . "</p>
                        <p style='margin: 3px 0;'><strong>ADC Code:</strong> " . e($adcCode) . "</p>
                        <p style='margin: 3px 0;'><strong>Attached File:</strong> " . e($fileName) . "</p>
                        <p style='margin: 3px 0;'><strong>Date:</strong> " . now()->format('d M Y, h:i A') . "</p>
                    </div>
                    " . (!empty($remarks) ? "<div style='background: #fffbeb; border-left: 4px solid #f59e0b; padding: 10px 15px; margin: 15px 0; font-size: 13px;'><strong>Note from sender:</strong><br>" . nl2br(e($remarks)) . "</div>" : "") . "
                    <p style='color: #888; font-size: 12px; margin-top: 25px;'>Indian MSME — From Makers to Markets</p>
                </div>
            ";

            Mail::html($htmlBody, function ($message) use ($recipientEmail, $subject, $fileData, $fileName, $mimeType) {
                $message->to($recipientEmail)
                    ->subject($subject)
                    ->attachData($fileData, $fileName, [
                        'mime' => $mimeType,
                    ]);
            });

            return response()->json([
                'success' => true,
                'message' => "GST Report has been sent successfully to {$recipientEmail}!",
            ]);
        } catch (\Throwable $e) {
            Log::error('GST Report Email Send Failed: ' . $e->getMessage(), [
                'exception' => $e,
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Failed to send email: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Aggregate and build GST Outward Supplies records grouped by Invoice No, HSN Code, Tax Rate
     */
    private function getGstReportRecords($adcId, Request $request): Collection
    {
        $startDate = $request->input('start_date');
        $endDate   = $request->input('end_date');
        $taxRateFilter = $request->input('tax_rate', 'all');
        $taxTypeFilter = $request->input('tax_type', 'all');
        $search    = trim($request->input('search', ''));

        $adcUser = User::with(['adcProfile', 'profile'])->find($adcId);
        $adcProf = $adcUser?->adcProfile ?: $adcUser?->profile;
        $adcState = strtoupper(trim($adcProf?->state ?: ($adcUser?->state ?: 'PUNJAB')));

        $posQuery = AdcPosOrder::where('adc_id', $adcId)
            ->with([
                'invoice', 
                'items.inventory.localProduct', 
                'items.inventory.product', 
                'items.product', 
                'items.localProduct', 
                'customer'
            ]);

        if ($startDate) $posQuery->whereDate('created_at', '>=', $startDate);
        if ($endDate)   $posQuery->whereDate('created_at', '<=', $endDate);

        $orders = $posQuery->orderBy('created_at', 'desc')->orderBy('id', 'desc')->get();
        $groupedLines = [];

        foreach ($orders as $order) {
            $invoice = $order->invoice;
            $invoiceNo = $invoice?->invoice_number ?: $order->order_no;
            
            // Format Invoice Date as seen in sample image: 03-09-2026 12:00
            $orderDate = $order->created_at ?: now();
            $invoiceDate = $orderDate->format('d-m-Y H:i');

            // Receiver details
            $rawGstin = trim($invoice?->bill_to_gstin ?? ($order->customer?->gst_number ?? ''));
            $recipientGstin = (!empty($rawGstin) && strtoupper($rawGstin) !== 'N/A') ? $rawGstin : 'REGISTERED/URP';
            
            $receiverName = trim($invoice?->bill_to_name ?? ($order->customer_name ?: ($order->customer?->name ?? '')));
            if (empty($receiverName)) {
                $receiverName = 'CASH/RETAIL/ AS PER GSTIN';
            }

            // Place of Supply (State Name)
            $customerState = trim($invoice?->bill_to_state ?? ($order->customer?->state ?? ''));
            $placeOfSupply = !empty($customerState) ? strtoupper($customerState) : $adcState;
            if (empty($placeOfSupply)) {
                $placeOfSupply = 'STATE NAME';
            }

            // Determine if Intra-State (CGST + SGST) or Inter-State (IGST)
            $isInterState = (strcasecmp($adcState, $placeOfSupply) !== 0);
            if ($taxTypeFilter === 'inter') {
                $isInterState = true;
            } elseif ($taxTypeFilter === 'intra') {
                $isInterState = false;
            }

            // Order special discount calculation
            $orderSubtotal = (float) ($order->subtotal > 0 ? $order->subtotal : $order->items->sum('total_price'));
            $orderSpecialDisc = (float) ($order->special_discount_amount ?? 0);

            foreach ($order->items as $item) {
                $netQty = max(0, (float) $item->quantity - (float) ($item->returned_quantity ?? 0));
                if ($netQty <= 0) {
                    continue; // Skip if fully returned
                }

                $grossPrice = (float) ($item->unit_price > 0 ? $item->unit_price : ($item->quantity > 0 ? $item->total_price / $item->quantity : 0));
                $itemSubtotal = $netQty * $grossPrice;
                $itemDisc = ($orderSubtotal > 0 && $orderSpecialDisc > 0) ? ($orderSpecialDisc * ($itemSubtotal / $orderSubtotal)) : 0;
                $lineTotal = max(0, $itemSubtotal - $itemDisc);

                $inv = $item->inventory;
                $localProd = $item->localProduct ?: ($inv ? $inv->localProduct : null);
                $prod = $item->product ?: ($inv ? $inv->product : null);

                $productName = trim($item->product_name ?: ($prod?->product_name ?: ($localProd?->product_name ?: ($inv?->product_name ?: 'General Item'))));
                $hsnCode = trim($localProd?->hsn_code ?: ($prod?->hsn_code ?: ($item->hsn_code ?: 'N/A')));
                $gstRate = (float) ($inv?->gst_rate ?? ($localProd?->gst_rate ?? ($prod?->gst_rate ?? ($item->gst_rate ?? 0))));

                if ($gstRate > 0) {
                    $taxable = round($lineTotal / (1.0 + ($gstRate / 100.0)), 2);
                    $totalTax = round($lineTotal - $taxable, 2);
                } else {
                    $taxable = round($lineTotal, 2);
                    $totalTax = 0.00;
                }

                if ($isInterState) {
                    $cgst = 0.00;
                    $sgst = 0.00;
                    $igst = $totalTax;
                } else {
                    $cgst = round($totalTax / 2.0, 2);
                    $sgst = round($totalTax - $cgst, 2);
                    $igst = 0.00;
                }

                // Group by (Order ID, Product Name, HSN Code, Tax Rate)
                $groupKey = "{$order->id}|{$productName}|{$hsnCode}|{$gstRate}";

                if (!isset($groupedLines[$groupKey])) {
                    $groupedLines[$groupKey] = [
                        'order_id'         => $order->id,
                        'invoice_date'     => $invoiceDate,
                        'invoice_no'       => $invoiceNo,
                        'product_name'     => $productName,
                        'recipient_gstin'  => $recipientGstin,
                        'receiver_name'    => $receiverName,
                        'place_of_supply'  => $placeOfSupply,
                        'quantity'         => 0,
                        'tax_rate'         => $gstRate,
                        'hsn_code'         => $hsnCode,
                        'taxable_amount'   => 0.00,
                        'sgst_amount'      => 0.00,
                        'cgst_amount'      => 0.00,
                        'igst_amount'      => 0.00,
                        'total_amount'     => 0.00,
                        'created_at'       => $order->created_at,
                    ];
                }

                $groupedLines[$groupKey]['quantity']       += $netQty;
                $groupedLines[$groupKey]['taxable_amount'] += $taxable;
                $groupedLines[$groupKey]['sgst_amount']    += $sgst;
                $groupedLines[$groupKey]['cgst_amount']    += $cgst;
                $groupedLines[$groupKey]['igst_amount']    += $igst;
                $groupedLines[$groupKey]['total_amount']   += $lineTotal;
            }
        }

        $collection = collect(array_values($groupedLines));

        // Filter by Tax Rate if selected
        if ($taxRateFilter !== 'all' && $taxRateFilter !== '') {
            $selectedRate = (float) $taxRateFilter;
            $collection = $collection->filter(function ($row) use ($selectedRate) {
                return (float) $row['tax_rate'] === $selectedRate;
            });
        }

        // Filter by Search Keywords (Invoice No, Product Name, HSN Code, Receiver Name, GSTIN, Place of Supply)
        if (!empty($search)) {
            $searchLower = strtolower($search);
            $collection = $collection->filter(function ($row) use ($searchLower) {
                return str_contains(strtolower($row['invoice_no']), $searchLower)
                    || str_contains(strtolower($row['product_name'] ?? ''), $searchLower)
                    || str_contains(strtolower($row['hsn_code']), $searchLower)
                    || str_contains(strtolower($row['receiver_name']), $searchLower)
                    || str_contains(strtolower($row['recipient_gstin']), $searchLower)
                    || str_contains(strtolower($row['place_of_supply']), $searchLower);
            });
        }

        return $collection->values();
    }
}

