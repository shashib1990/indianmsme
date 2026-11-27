<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdcProfile;
use App\Models\AdcWallet;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class AdcPlatformFeeReportController extends Controller
{
    /**
     * Display Monthly Platform Deduction Fee report for ADC
     */
    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 25);
        if (!in_array($perPage, [15, 25, 50, 100])) {
            $perPage = 25;
        }

        $currentPage = (int) $request->input('page', 1);

        // Build monthly grouped query
        $baseQuery = $this->buildMonthlyGroupedQuery($request);

        // Total count of monthly groups for pagination
        $totalMonthlyGroups = $this->getMonthlyGroupsCount($request);

        // Total Fee Deducted across all filtered rows
        $totalDeductedAmount = (float) (clone $this->buildBaseWalletQuery($request))->sum('adc_wallets.amount');

        // Total Unique ADCs charged
        $totalAdcsCharged = (clone $this->buildBaseWalletQuery($request))->distinct()->count('adc_wallets.adc_id');

        // Active ADCs count
        $activeAdcsCount = User::where('role', 'adc')->where('status', '1')->count();

        // Fetch paginated monthly groups
        $items = (clone $baseQuery)
            ->offset(($currentPage - 1) * $perPage)
            ->limit($perPage)
            ->get();

        // Attach User, Profile, and Running Balance
        $this->attachDetailsToMonthlyRows($items);

        $deductions = new LengthAwarePaginator(
            $items,
            $totalMonthlyGroups,
            $perPage,
            $currentPage,
            [
                'path' => $request->url(),
                'query' => $request->query(),
            ]
        );

        // Available Months list for filter dropdown
        $availableMonths = DB::table('adc_wallets')
            ->where('type', 'debit')
            ->where(function ($q) {
                $q->where('description', 'like', '%platform%')
                  ->orWhere('description', 'like', '%Daily platform fee%')
                  ->orWhere('description', 'like', '%daily fee%');
            })
            ->selectRaw("DISTINCT DATE_FORMAT(created_at, '%Y-%m') as m")
            ->orderBy('m', 'desc')
            ->pluck('m');

        // ADC List for filter dropdown
        $adcList = User::where('role', 'adc')
            ->with('adcProfile')
            ->orderBy('name')
            ->get();

        return view('admin.reports.adc-platform-fee', compact(
            'deductions',
            'totalDeductedAmount',
            'totalMonthlyGroups',
            'totalAdcsCharged',
            'activeAdcsCount',
            'adcList',
            'availableMonths'
        ));
    }

    /**
     * View/Print Monthly GST Tax Invoice for ADC Platform Deduction Fee
     */
    public function monthlyInvoice(Request $request, $adc_id, $month)
    {
        $user = User::with('adcProfile')->findOrFail($adc_id);
        $profile = $user->adcProfile;

        // Fetch all deductions for this ADC in this month
        $deductions = AdcWallet::where('adc_id', $adc_id)
            ->where('type', 'debit')
            ->where(function ($q) {
                $q->where('description', 'like', '%platform%')
                  ->orWhere('description', 'like', '%Daily platform fee%')
                  ->orWhere('description', 'like', '%daily fee%');
            })
            ->whereRaw("DATE_FORMAT(created_at, '%Y-%m') = ?", [$month])
            ->orderBy('created_at', 'asc')
            ->get();

        if ($deductions->isEmpty()) {
            abort(404, 'No platform fee deductions found for this month.');
        }

        $totalAmount = (float) $deductions->sum('amount');
        $totalDays = $deductions->count();
        $dailyRate = (float) ($profile?->per_user_daily_charge ?? 10.00);

        // Tax Calculations (18% GST inclusive)
        $taxRate = 18.0;
        $taxableAmount = round($totalAmount / (1 + ($taxRate / 100)), 2);
        $taxAmount = round($totalAmount - $taxableAmount, 2);

        // Supplier details (Abhhyam Distribution Private Limited)
        // Registered in Uttar Pradesh (GSTIN starts with 09)
        $supplierGstin = '09AXFPT1807Q1Z5';
        $supplierStateCode = substr($supplierGstin, 0, 2); // '09'
        $supplierState = 'Uttar Pradesh';

        // Customer State & State Code detection
        $customerGstin = trim($profile?->gst_number ?? '');
        $customerState = trim($profile?->state ?? '');
        $customerStateCode = '';

        if (!empty($customerGstin) && strlen($customerGstin) >= 2 && ctype_digit(substr($customerGstin, 0, 2))) {
            $customerStateCode = substr($customerGstin, 0, 2);
            if (empty($customerState)) {
                $customerState = $this->getStateNameFromCode($customerStateCode);
            }
        }

        if (empty($customerStateCode) && !empty($customerState)) {
            $customerStateCode = $this->getStateCode($customerState);
        }

        if (empty($customerStateCode)) {
            $customerStateCode = '03'; // default Punjab if unspecified
            $customerState = $customerState ?: 'Punjab';
        }

        // Automatic GST detection based on seller and customer states
        // Same State -> Intra-State (CGST + SGST)
        // Different States -> Inter-State (IGST)
        $isSameState = ($supplierStateCode === $customerStateCode);
        $isInterState = !$isSameState;

        $cgstRate = $isSameState ? ($taxRate / 2) : 0.0; // 9.0%
        $sgstRate = $isSameState ? ($taxRate / 2) : 0.0; // 9.0%
        $igstRate = $isInterState ? $taxRate : 0.0;      // 18.0%

        $cgstAmount = 0.00;
        $sgstAmount = 0.00;
        $igstAmount = 0.00;

        if ($isInterState) {
            $igstAmount = $taxAmount;
        } else {
            $cgstAmount = round($taxAmount / 2, 2);
            $sgstAmount = round($taxAmount - $cgstAmount, 2);
        }

        $monthCarbon = Carbon::createFromFormat('Y-m', $month);
        $monthName = $monthCarbon->format('F Y');
        $invoiceDate = $deductions->max('created_at')->format('d M Y');

        // Indian Financial Year calculation (April 1 to March 31)
        $calYear = (int) $monthCarbon->format('Y');
        $calMonth = (int) $monthCarbon->format('n');
        if ($calMonth >= 4) {
            $fy = $calYear . '-' . substr((string) ($calYear + 1), -2);
            $fyStart = $calYear . '-04-01 00:00:00';
            $fyEnd = ($calYear + 1) . '-03-31 23:59:59';
        } else {
            $fy = ($calYear - 1) . '-' . substr((string) $calYear, -2);
            $fyStart = ($calYear - 1) . '-04-01 00:00:00';
            $fyEnd = $calYear . '-03-31 23:59:59';
        }

        // Sequence number starting from 001:
        // 1. If passed via ?seq=1 from the report row, use it directly.
        // 2. Otherwise calculate its 1-based index in the ordered monthly list for this FY.
        $seq = $request->input('seq');
        if ($seq && is_numeric($seq) && (int) $seq > 0) {
            $serialNumber = (int) $seq;
        } else {
            $groupsInFy = DB::table('adc_wallets')
                ->where('adc_wallets.type', 'debit')
                ->where(function ($q) {
                    $q->where('adc_wallets.description', 'like', '%platform%')
                      ->orWhere('adc_wallets.description', 'like', '%Daily platform fee%')
                      ->orWhere('adc_wallets.description', 'like', '%daily fee%');
                })
                ->whereBetween('adc_wallets.created_at', [$fyStart, $fyEnd])
                ->selectRaw("
                    adc_wallets.adc_id,
                    DATE_FORMAT(adc_wallets.created_at, '%Y-%m') as month_year
                ")
                ->groupBy('adc_wallets.adc_id', 'month_year')
                ->orderBy('month_year', 'desc')
                ->orderByRaw('COUNT(adc_wallets.id) DESC')
                ->orderBy('adc_wallets.adc_id', 'asc')
                ->get();

            $foundIndex = 1;
            foreach ($groupsInFy as $idx => $grp) {
                if ((int) $grp->adc_id === (int) $adc_id && $grp->month_year === $month) {
                    $foundIndex = $idx + 1;
                    break;
                }
            }

            $serialNumber = $foundIndex;
        }

        $invoiceNo = 'PF/' . $fy . '/' . str_pad($serialNumber, 3, '0', STR_PAD_LEFT);

        $amountInWords = $this->numberToWords($totalAmount);

        return view('admin.reports.adc-platform-fee-invoice', compact(
            'user',
            'profile',
            'deductions',
            'month',
            'monthName',
            'invoiceDate',
            'invoiceNo',
            'totalAmount',
            'totalDays',
            'dailyRate',
            'taxRate',
            'cgstRate',
            'sgstRate',
            'igstRate',
            'taxableAmount',
            'taxAmount',
            'isSameState',
            'isInterState',
            'cgstAmount',
            'sgstAmount',
            'igstAmount',
            'supplierState',
            'supplierStateCode',
            'customerState',
            'customerStateCode',
            'amountInWords'
        ));
    }

    /**
     * Backward-compatible route by wallet ID: redirects or displays that month's invoice
     */
    public function invoice($id)
    {
        $wallet = AdcWallet::findOrFail($id);
        $month = Carbon::parse($wallet->created_at)->format('Y-m');

        return $this->monthlyInvoice(request(), $wallet->adc_id, $month);
    }

    /**
     * Export Monthly Platform Deduction Fee Report (Excel .xlsx or CSV)
     */
    public function export(Request $request)
    {
        @set_time_limit(300);
        @ini_set('memory_limit', '512M');

        $format = strtolower($request->input('format', 'xlsx'));
        $baseQuery = $this->buildMonthlyGroupedQuery($request);

        $items = $baseQuery->get();
        $this->attachDetailsToMonthlyRows($items);

        $headersList = [
            'S.No',
            'Invoice No.',
            'Month & Year',
            'ADC Name',
            'Adc Code',
            'Asc Code',
            'Company / Trade Name',
            'Phone',
            'Email',
            'District',
            'State',
            'Days Charged',
            'Daily Fee Rate (₹)',
            'Taxable Value (₹)',
            'IGST (₹)',
            'CGST (₹)',
            'SGST (₹)',
            'Monthly Amount (₹)',
            'Wallet Balance (₹)',
            'Status',
        ];

        $serial = 1;
        $rowsData = [];
        $sumMonthlyAmount = 0;
        $sumDays = 0;
        $sumTaxable = 0;
        $sumIgst = 0;
        $sumCgst = 0;
        $sumSgst = 0;

        foreach ($items as $item) {
            $user = $item->user;
            $profile = $item->profile;

            $amount = (float) $item->monthly_amount;
            $days = (int) $item->total_days;
            $sumMonthlyAmount += $amount;
            $sumDays += $days;
            $sumTaxable += (float) ($item->taxable_value ?? 0);
            $sumIgst += (float) ($item->igst_amount ?? 0);
            $sumCgst += (float) ($item->cgst_amount ?? 0);
            $sumSgst += (float) ($item->sgst_amount ?? 0);

            $calYear = (int) substr($item->month_year, 0, 4);
            $calMonth = (int) substr($item->month_year, 5, 2);
            $fyStr = ($calMonth >= 4) ? ($calYear . '-' . substr((string)($calYear + 1), -2)) : (($calYear - 1) . '-' . substr((string)$calYear, -2));
            $invNo = 'PF/' . $fyStr . '/' . str_pad($serial, 3, '0', STR_PAD_LEFT);

            $adcCode = $profile?->adc_code ?: ($user?->adc_code ?: ('ADC' . str_pad($item->adc_id, 3, '0', STR_PAD_LEFT)));
            $ascCode = $profile?->code ?: ($user?->code ?: ('ASC' . str_pad($item->adc_id, 3, '0', STR_PAD_LEFT)));
            $dailyRate = (float) ($profile?->per_user_daily_charge ?? 10.00);
            $runningBalance = (float) ($item->running_balance ?? ($profile?->wallet_balance ?? 0.00));
            $isActive = $user ? ($user->status === '1' ? 'Active' : 'Inactive / Exhausted') : 'N/A';
            $monthFormatted = Carbon::createFromFormat('Y-m', $item->month_year)->format('F Y');

            $rowsData[] = [
                $serial++,
                $invNo,
                $monthFormatted,
                $user?->name ?? ('ADC #' . $item->adc_id),
                $adcCode,
                $ascCode,
                $profile?->company_name ?? $profile?->trade_name ?? '—',
                $user?->mobile ?? $profile?->official_phone ?? '—',
                $user?->email ?? $profile?->official_email ?? '—',
                $profile?->district ?? '—',
                $item->customer_state ?? $profile?->state ?? '—',
                $days,
                $dailyRate,
                (float) ($item->taxable_value ?? 0),
                (float) ($item->igst_amount ?? 0),
                (float) ($item->cgst_amount ?? 0),
                (float) ($item->sgst_amount ?? 0),
                $amount,
                $runningBalance,
                $isActive,
            ];
        }

        $filenameBase = 'adc_monthly_platform_deduction_fee_report_' . date('Ymd_His');

        if ($format === 'csv') {
            return response()->streamDownload(function () use ($headersList, $rowsData) {
                $output = fopen('php://output', 'w');
                fputs($output, "\xEF\xBB\xBF"); // UTF-8 BOM
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

        // Excel .xlsx
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Monthly ADC Platform Fee');

        // Headers
        $sheet->fromArray($headersList, null, 'A1');

        // Style Header (A1 to T1)
        $sheet->getStyle('A1:T1')->getFont()->setBold(true)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color(\PhpOffice\PhpSpreadsheet\Style\Color::COLOR_WHITE));
        $sheet->getStyle('A1:T1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FF0D9488'); // teal-600
        $sheet->getStyle('A1:T1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // Data Rows
        if (!empty($rowsData)) {
            $sheet->fromArray($rowsData, null, 'A2');
            $lastRow = count($rowsData) + 1;

            // Numeric Formats
            $sheet->getStyle('L2:L' . $lastRow)->getNumberFormat()->setFormatCode('#,##0');
            $sheet->getStyle('M2:S' . $lastRow)->getNumberFormat()->setFormatCode('#,##0.00');

            // Summary Totals Row
            $summaryRow = $lastRow + 2;
            $sheet->setCellValue('A' . $summaryRow, 'TOTAL');
            $sheet->setCellValue('B' . $summaryRow, count($rowsData) . ' Invoices');
            $sheet->setCellValue('L' . $summaryRow, $sumDays);
            $sheet->setCellValue('N' . $summaryRow, $sumTaxable);
            $sheet->setCellValue('O' . $summaryRow, $sumIgst);
            $sheet->setCellValue('P' . $summaryRow, $sumCgst);
            $sheet->setCellValue('Q' . $summaryRow, $sumSgst);
            $sheet->setCellValue('R' . $summaryRow, $sumMonthlyAmount);

            $sheet->getStyle('A' . $summaryRow . ':T' . $summaryRow)->getFont()->setBold(true);
            $sheet->getStyle('A' . $summaryRow . ':T' . $summaryRow)->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setARGB('FFF1F5F9');
            $sheet->getStyle('M' . $summaryRow . ':S' . $summaryRow)->getNumberFormat()->setFormatCode('#,##0.00');
        }

        $columnWidths = [
            'A' => 8,   // S.No
            'B' => 18,  // Invoice No.
            'C' => 16,  // Month & Year
            'D' => 24,  // ADC Name
            'E' => 15,  // Adc Code
            'F' => 15,  // Asc Code
            'G' => 26,  // Company
            'H' => 16,  // Phone
            'I' => 26,  // Email
            'J' => 18,  // District
            'K' => 18,  // State
            'L' => 14,  // Days Charged
            'M' => 18,  // Daily Rate
            'N' => 18,  // Taxable Value
            'O' => 15,  // IGST
            'P' => 15,  // CGST
            'Q' => 15,  // SGST
            'R' => 20,  // Monthly Amount
            'S' => 20,  // Wallet Balance
            'T' => 15,  // Status
        ];

        foreach ($columnWidths as $col => $w) {
            $sheet->getColumnDimension($col)->setWidth($w);
        }

        return response()->streamDownload(function () use ($spreadsheet) {
            $writer = IOFactory::createWriter($spreadsheet, 'Xlsx');
            $writer->save('php://output');
        }, $filenameBase . '.xlsx', [
            'Content-Type'        => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filenameBase . '.xlsx"',
            'Pragma'              => 'no-cache',
            'Expires'             => '0',
        ]);
    }

    /**
     * Build base query for monthly grouped ADC deductions
     */
    private function buildMonthlyGroupedQuery(Request $request)
    {
        $base = $this->buildBaseWalletQuery($request);

        return $base->selectRaw("
            adc_wallets.adc_id,
            DATE_FORMAT(adc_wallets.created_at, '%Y-%m') as month_year,
            SUM(adc_wallets.amount) as monthly_amount,
            COUNT(*) as total_days,
            MIN(adc_wallets.created_at) as first_date,
            MAX(adc_wallets.created_at) as last_date,
            MAX(adc_wallets.id) as latest_wallet_id
        ")
        ->groupBy('adc_wallets.adc_id', DB::raw("DATE_FORMAT(adc_wallets.created_at, '%Y-%m')"))
        ->orderBy('month_year', 'desc')
        ->orderBy('monthly_amount', 'desc');
    }

    /**
     * Count total monthly groups for pagination
     */
    private function getMonthlyGroupsCount(Request $request)
    {
        $base = $this->buildBaseWalletQuery($request);

        return $base->distinct()->count(DB::raw("CONCAT(adc_wallets.adc_id, '_', DATE_FORMAT(adc_wallets.created_at, '%Y-%m'))"));
    }

    /**
     * Base query on adc_wallets with filters applied
     */
    private function buildBaseWalletQuery(Request $request)
    {
        $query = DB::table('adc_wallets')
            ->where('adc_wallets.type', 'debit')
            ->where(function ($q) {
                $q->where('adc_wallets.description', 'like', '%platform%')
                  ->orWhere('adc_wallets.description', 'like', '%Daily platform fee%')
                  ->orWhere('adc_wallets.description', 'like', '%daily fee%');
            });

        // Filter: Specific ADC
        if ($request->filled('adc_id')) {
            $query->where('adc_wallets.adc_id', $request->input('adc_id'));
        }

        // Filter: Month (e.g. 2026-11)
        if ($request->filled('month')) {
            $query->whereRaw("DATE_FORMAT(adc_wallets.created_at, '%Y-%m') = ?", [$request->input('month')]);
        }

        // Filter: Search Keyword
        if ($request->filled('search')) {
            $term = trim($request->input('search'));
            $numOnly = preg_replace('/\D/', '', $term);

            // Fetch matched ADC IDs
            $matchedIds = User::where('role', 'adc')
                ->where(function ($userQ) use ($term) {
                    $userQ->where('name', 'like', "%{$term}%")
                          ->orWhere('email', 'like', "%{$term}%")
                          ->orWhere('mobile', 'like', "%{$term}%")
                          ->orWhereHas('adcProfile', function ($profQ) use ($term) {
                              $profQ->where('adc_code', 'like', "%{$term}%")
                                    ->orWhere('code', 'like', "%{$term}%")
                                    ->orWhere('company_name', 'like', "%{$term}%")
                                    ->orWhere('trade_name', 'like', "%{$term}%")
                                    ->orWhere('official_phone', 'like', "%{$term}%");
                          });
                })
                ->pluck('id')
                ->all();

            if (!empty($numOnly)) {
                $matchedIds[] = (int) $numOnly;
            }

            $matchedIds = array_unique($matchedIds);

            $query->where(function ($q) use ($matchedIds, $term) {
                $q->where('adc_wallets.description', 'like', "%{$term}%");
                if (!empty($matchedIds)) {
                    $q->orWhereIn('adc_wallets.adc_id', $matchedIds);
                }
            });
        }

        return $query;
    }

    /**
     * Attach User, ADC Profile and Running Balance to each row
     */
    private function attachDetailsToMonthlyRows($items)
    {
        if ($items->isEmpty()) {
            return;
        }

        $adcIds = $items->pluck('adc_id')->unique()->filter()->values()->all();
        $users = User::with('adcProfile')->whereIn('id', $adcIds)->get()->keyBy('id');

        // Current live balances
        $currentBalances = AdcProfile::whereIn('user_id', $adcIds)
            ->pluck('wallet_balance', 'user_id')
            ->map(fn($v) => (float)$v)
            ->all();

        $minWalletId = $items->min('latest_wallet_id');

        // Fetch subsequent transactions to calculate running balance
        $subsequent = AdcWallet::whereIn('adc_id', $adcIds)
            ->where('id', '>', $minWalletId)
            ->get(['id', 'adc_id', 'amount', 'type'])
            ->groupBy('adc_id');

        $taxRate = 18.0;

        foreach ($items as $item) {
            $item->user = $users[$item->adc_id] ?? null;
            $item->profile = $item->user?->adcProfile ?? null;

            $adcId = $item->adc_id;
            $currentBal = $currentBalances[$adcId] ?? 0.00;
            $subForAdc = $subsequent->get($adcId);

            if (!$subForAdc) {
                $item->running_balance = round($currentBal, 2);
            } else {
                $delta = 0.00;
                foreach ($subForAdc as $sub) {
                    if ($sub->id > $item->latest_wallet_id) {
                        if ($sub->type === 'debit') {
                            $delta += (float) $sub->amount;
                        } elseif ($sub->type === 'credit') {
                            $delta -= (float) $sub->amount;
                        }
                    }
                }
                $item->running_balance = round($currentBal + $delta, 2);
            }

            // GST Calculations (18% inclusive)
            $monthlyAmount = (float) $item->monthly_amount;
            $taxableValue = round($monthlyAmount / (1 + ($taxRate / 100)), 2);
            $taxAmount = round($monthlyAmount - $taxableValue, 2);

            $profile = $item->profile;
            $customerGstin = trim($profile?->gst_number ?? '');
            $customerState = trim($profile?->state ?? '');
            $customerStateCode = '';

            if (!empty($customerGstin) && strlen($customerGstin) >= 2 && ctype_digit(substr($customerGstin, 0, 2))) {
                $customerStateCode = substr($customerGstin, 0, 2);
                if (empty($customerState)) {
                    $customerState = $this->getStateNameFromCode($customerStateCode);
                }
            }

            if (empty($customerStateCode) && !empty($customerState)) {
                $customerStateCode = $this->getStateCode($customerState);
            }

            if (empty($customerStateCode)) {
                $customerStateCode = '03'; // default
                $customerState = $customerState ?: 'Punjab';
            }

            // Supplier State is Uttar Pradesh ('09')
            $isSameState = ($customerStateCode === '09');

            $cgst = $isSameState ? round($taxAmount / 2, 2) : 0.00;
            $sgst = $isSameState ? round($taxAmount - $cgst, 2) : 0.00;
            $igst = !$isSameState ? $taxAmount : 0.00;

            $item->taxable_value = $taxableValue;
            $item->igst_amount = $igst;
            $item->cgst_amount = $cgst;
            $item->sgst_amount = $sgst;
            $item->is_same_state = $isSameState;
            $item->customer_state = $customerState;
            $item->customer_state_code = $customerStateCode;
        }
    }

    /**
     * Helper: Convert number to English words
     */
    private function numberToWords($num)
    {
        $num = round($num, 2);
        $ones = [
            0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
            6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
            11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
            16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen'
        ];
        $tens = [
            2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
            6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'
        ];

        $rupees = (int) $num;
        $paise = (int) round(($num - $rupees) * 100);

        $convertChunk = function ($n) use ($ones, $tens, &$convertChunk) {
            $str = '';
            if ($n >= 10000000) {
                $str .= $convertChunk((int) ($n / 10000000)) . ' Crore ';
                $n %= 10000000;
            }
            if ($n >= 100000) {
                $str .= $convertChunk((int) ($n / 100000)) . ' Lakh ';
                $n %= 100000;
            }
            if ($n >= 1000) {
                $str .= $convertChunk((int) ($n / 1000)) . ' Thousand ';
                $n %= 1000;
            }
            if ($n >= 100) {
                $str .= $ones[(int) ($n / 100)] . ' Hundred ';
                $n %= 100;
            }
            if ($n > 0) {
                if ($n < 20) {
                    $str .= $ones[$n] . ' ';
                } else {
                    $str .= $tens[(int) ($n / 10)] . ' ';
                    if ($n % 10 > 0) {
                        $str .= $ones[$n % 10] . ' ';
                    }
                }
            }
            return trim($str);
        };

        $result = $rupees > 0 ? $convertChunk($rupees) . ' Rupees' : 'Zero Rupees';
        if ($paise > 0) {
            $result .= ' and ' . $convertChunk($paise) . ' Paise';
        }
        return $result . ' Only';
    }

    /**
     * Helper: GST State Code
     */
    private function getStateCode($stateName)
    {
        $stateMap = [
            'jammu' => '01', 'himachal' => '02', 'punjab' => '03', 'chandigarh' => '04',
            'uttarakhand' => '05', 'haryana' => '06', 'delhi' => '07', 'rajasthan' => '08',
            'uttar pradesh' => '09', 'up' => '09', 'bihar' => '10', 'sikkim' => '11',
            'arunachal' => '12', 'nagaland' => '13', 'manipur' => '14', 'mizoram' => '15',
            'tripura' => '16', 'meghalaya' => '17', 'assam' => '18', 'west bengal' => '19',
            'jharkhand' => '20', 'odisha' => '21', 'orissa' => '21', 'chhattisgarh' => '22',
            'madhya pradesh' => '23', 'mp' => '23', 'gujarat' => '24', 'maharashtra' => '27',
            'andhra' => '37', 'karnataka' => '29', 'goa' => '30', 'kerala' => '32',
            'tamil nadu' => '33', 'telangana' => '36',
        ];

        $clean = strtolower(trim($stateName ?? ''));
        foreach ($stateMap as $key => $code) {
            if (str_contains($clean, $key)) {
                return $code;
            }
        }
        return '03'; // default
    }

    /**
     * Helper: GST State Name from 2-digit Code
     */
    private function getStateNameFromCode($code)
    {
        $codeMap = [
            '01' => 'Jammu and Kashmir', '02' => 'Himachal Pradesh', '03' => 'Punjab',
            '04' => 'Chandigarh', '05' => 'Uttarakhand', '06' => 'Haryana',
            '07' => 'Delhi', '08' => 'Rajasthan', '09' => 'Uttar Pradesh',
            '10' => 'Bihar', '11' => 'Sikkim', '12' => 'Arunachal Pradesh',
            '13' => 'Nagaland', '14' => 'Manipur', '15' => 'Mizoram',
            '16' => 'Tripura', '17' => 'Meghalaya', '18' => 'Assam',
            '19' => 'West Bengal', '20' => 'Jharkhand', '21' => 'Odisha',
            '22' => 'Chhattisgarh', '23' => 'Madhya Pradesh', '24' => 'Gujarat',
            '27' => 'Maharashtra', '29' => 'Karnataka', '30' => 'Goa',
            '32' => 'Kerala', '33' => 'Tamil Nadu', '36' => 'Telangana',
            '37' => 'Andhra Pradesh',
        ];
        return $codeMap[$code] ?? 'Other';
    }
}
