<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TAX INVOICE - {{ $invoiceNo }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            font-size: 11px;
            color: #0f172a;
            background: #f1f5f9;
            margin: 0;
            padding: 20px 10px;
        }
        .action-bar {
            max-width: 820px;
            margin: 0 auto 14px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .btn {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 16px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            border: none;
        }
        .btn-print { background: #0f766e; color: #ffffff; }
        .btn-print:hover { background: #115e59; }
        .btn-close { background: #e2e8f0; color: #334155; }
        .btn-close:hover { background: #cbd5e1; }

        .invoice-box {
            max-width: 820px;
            margin: 0 auto;
            background: #ffffff;
            border: 1.5px solid #000;
            padding: 0;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.08);
        }
        .invoice-title {
            text-align: center;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 1px;
            padding: 8px;
            border-bottom: 1.5px solid #000;
            background: #f8fafc;
        }
        .two-col-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            border-bottom: 1.5px solid #000;
        }
        .col-left {
            padding: 8px 12px;
            border-right: 1.5px solid #000;
        }
        .col-right {
            padding: 8px 12px;
        }
        .section-header {
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 6px;
            color: #0f172a;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 3px;
        }
        .field-row {
            display: flex;
            margin-bottom: 3px;
            line-height: 1.35;
        }
        .field-label {
            width: 140px;
            color: #475569;
            font-weight: 500;
            flex-shrink: 0;
        }
        .field-value {
            flex-grow: 1;
            font-weight: 600;
            color: #0f172a;
        }
        .full-width-section {
            padding: 8px 12px;
            border-bottom: 1.5px solid #000;
        }
        table.service-table {
            width: 100%;
            border-collapse: collapse;
        }
        table.service-table th, table.service-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            font-size: 10px;
        }
        table.service-table th {
            background-color: #f1f5f9;
            font-weight: 700;
            text-transform: uppercase;
            text-align: center;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-start { text-align: left; }
        .fw-bold { font-weight: bold; }
        
        .totals-section {
            border-bottom: 1.5px solid #000;
        }
        .totals-table {
            width: 100%;
            border-collapse: collapse;
        }
        .totals-table td {
            padding: 5px 12px;
            font-size: 11px;
            border-bottom: 1px solid #e2e8f0;
        }
        .totals-table tr.grand-total td {
            border-top: 1.5px solid #000;
            border-bottom: none;
            background: #f8fafc;
            font-size: 12px;
            font-weight: 700;
        }
        .words-section {
            padding: 8px 12px;
            border-bottom: 1.5px solid #000;
            font-size: 10.5px;
            background: #fafafa;
        }
        .tax-note {
            padding: 6px 12px;
            font-size: 9.5px;
            font-style: italic;
            color: #475569;
            border-bottom: 1.5px solid #000;
            background: #fff;
        }
        .bottom-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            padding: 10px 12px;
            border-bottom: 1.5px solid #000;
            font-size: 10px;
            gap: 16px;
        }
        .signature-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            padding: 30px 12px 14px 12px;
            font-size: 11px;
        }
        .sig-left {
            text-align: left;
            vertical-align: bottom;
        }
        .sig-right {
            text-align: right;
            vertical-align: bottom;
        }
        @media print {
            body { background: #ffffff; padding: 0; }
            .action-bar { display: none !important; }
            .invoice-box { box-shadow: none; border: 1.5px solid #000; max-width: 100%; }
        }
    </style>
</head>
<body>

    @php
        $adcCode = $profile?->adc_code ?: ($user?->adc_code ?: ('ADC' . str_pad($user->id, 3, '0', STR_PAD_LEFT)));
        $ascCode = $profile?->code ?: ($user?->code ?: ('ASC' . str_pad($user->id, 3, '0', STR_PAD_LEFT)));
        $customerName = $profile?->company_name ?: ($profile?->trade_name ?: $user->name);
        $fullAddress = trim(($profile?->address ? $profile->address . ', ' : '') . ($profile?->district ? $profile->district . ', ' : '') . ($profile?->state ? $profile->state : ''));
        if ($profile?->pincode) {
            $fullAddress .= ' - ' . $profile->pincode;
        }
        $customerGstin = $profile?->gst_number ?: 'URP (Unregistered Person)';
    @endphp

    <div class="action-bar">
        <a href="javascript:window.close()" class="btn btn-close">← Close Window</a>
        <button onclick="window.print()" class="btn btn-print">🖨️ Print Tax Invoice</button>
    </div>

    <div class="invoice-box">
        <!-- 1. Header Title -->
        <div class="invoice-title">TAX INVOICE</div>

        <!-- 2. Supplier / Service Provider Details & Invoice Details -->
        <div class="two-col-grid">
            <div class="col-left">
                <div class="section-header">SUPPLIER / SERVICE PROVIDER DETAILS</div>
                <div class="field-row">
                    <span class="field-label">Legal Name:</span>
                    <span class="field-value">ASC DISTRIBUTOR PRIVATE LIMITED</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Address:</span>
                    <span class="field-value">House No. B2, Block -C, Pocket -7, Sector 8, Rohini, New Delhi - 110085</span>
                </div>
                <div class="field-row">
                    <span class="field-label">GSTIN:</span>
                    <span class="field-value">07AAZCA4502F1ZM</span>
                </div>
                <div class="field-row">
                    <span class="field-label">State / State Code:</span>
                    <span class="field-value">{{ $supplierState }} / {{ $supplierStateCode }}</span>
                </div>
                <!-- <div class="field-row">
                    <span class="field-label">Email / Phone:</span>
                    <span class="field-value">home@ardhsainik.com / +91 8800001240</span>
                </div> -->
            </div>

            <div class="col-right">
                <div class="section-header">INVOICE DETAILS</div>
                <div class="field-row">
                    <span class="field-label">Invoice No.:</span>
                    <span class="field-value font-monospace" style="color: #0f766e;">{{ $invoiceNo }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Invoice Date:</span>
                    <span class="field-value">{{ $invoiceDate }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Billing Month:</span>
                    <span class="field-value">{{ $monthName }}</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Place of Supply:</span>
                    <span class="field-value">{{ $customerState }} (Code: {{ $customerStateCode }})</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Reverse Charge (Y/N):</span>
                    <span class="field-value">No</span>
                </div>
                <div class="field-row">
                    <span class="field-label">Payment Due Date:</span>
                    <span class="field-value" style="color: #166534;">Settled (Auto-Debited from Wallet)</span>
                </div>
            </div>
        </div>

        <!-- 3. Bill To / Recipient Details (Full-width Left to Right) -->
        <div class="full-width-section">
            <div class="section-header">BILL TO / RECIPIENT DETAILS</div>
            <div class="field-row">
                <span class="field-label" style="width: 180px;">Customer / Business Name:</span>
                <span class="field-value">{{ $customerName }}</span>
            </div>
            <div class="field-row">
                <span class="field-label" style="width: 180px;">ADC Code / ASC Code:</span>
                <span class="field-value"><strong>{{ $adcCode }}</strong> &nbsp;/&nbsp; <strong>{{ $ascCode }}</strong></span>
            </div>
            <div class="field-row">
                <span class="field-label" style="width: 180px;">Billing Address:</span>
                <span class="field-value">{{ $fullAddress ?: '—' }}</span>
            </div>
            <div class="field-row">
                <span class="field-label" style="width: 180px;">GSTIN (if registered):</span>
                <span class="field-value">{{ $customerGstin }}</span>
            </div>
            <div class="field-row">
                <span class="field-label" style="width: 180px;">State / State Code:</span>
                <span class="field-value">{{ $customerState }} / {{ $customerStateCode }}</span>
            </div>
            <div class="field-row">
                <span class="field-label" style="width: 180px;">Contact Mobile / Email:</span>
                <span class="field-value">{{ $user->mobile ?: ($profile?->official_phone ?: '—') }} &nbsp;/&nbsp; {{ $user->email ?: ($profile?->official_email ?: '—') }}</span>
            </div>
        </div>

        <!-- 4. Service Details Table -->
        <table class="service-table">
            <thead>
                <tr>
                    <th style="width: 5%;">Sr. No.</th>
                    <th style="width: 38%;" class="text-start">Description of Service</th>
                    <th style="width: 8%;">SAC</th>
                    <th style="width: 6%;">Qty</th>
                    <th style="width: 10%;" class="text-end">Rate (₹)</th>
                    <th style="width: 7%;" class="text-end">Discount (₹)</th>
                    <th style="width: 12%;" class="text-end">Taxable Value (₹)</th>
                    <th style="width: 6%;">GST (%)</th>
                    <th style="width: 10%;" class="text-end">GST Amount (₹)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="text-center">1</td>
                    <td class="text-start">
                        <strong>Software platform access / subscription / platform charges</strong>
                        <div style="font-size: 9px; color: #475569; margin-top: 2px;">
                            For the month of {{ $monthName }} ({{ $totalDays }} days @ ₹{{ number_format($dailyRate, 2) }}/day)
                        </div>
                    </td>
                    <td class="text-center font-monospace">997331</td>
                    <td class="text-center">1</td>
                    <td class="text-end">{{ number_format($taxableAmount, 2) }}</td>
                    <td class="text-end">0.00</td>
                    <td class="text-end fw-bold">{{ number_format($taxableAmount, 2) }}</td>
                    <td class="text-center">
                        @if($isSameState)
                            18%<br><span style="font-size: 8px; color: #64748b;">(CGST 9% + SGST 9%)</span>
                        @else
                            18%<br><span style="font-size: 8px; color: #64748b;">(IGST 18%)</span>
                        @endif
                    </td>
                    <td class="text-end fw-bold">{{ number_format($taxAmount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <!-- 5. Totals Section (Always Display All 3 GST Rows: CGST, SGST, IGST) -->
        <div class="totals-section">
            <table class="totals-table">
                <tr>
                    <td class="text-end" style="width: 75%;"><strong>Total Taxable Value (₹):</strong></td>
                    <td class="text-end" style="width: 25%; font-weight: 600;">₹{{ number_format($taxableAmount, 2) }}</td>
                </tr>
                <tr>
                    <td class="text-end">Add: CGST (9%):</td>
                    <td class="text-end fw-semibold">{!! $isSameState ? ('₹' . number_format($cgstAmount, 2)) : '&nbsp;' !!}</td>
                </tr>
                <tr>
                    <td class="text-end">Add: SGST (9%):</td>
                    <td class="text-end fw-semibold">{!! $isSameState ? ('₹' . number_format($sgstAmount, 2)) : '&nbsp;' !!}</td>
                </tr>
                <tr>
                    <td class="text-end">Add: IGST (18%):</td>
                    <td class="text-end fw-semibold">{!! !$isSameState ? ('₹' . number_format($igstAmount, 2)) : '&nbsp;' !!}</td>
                </tr>
                <tr class="grand-total">
                    <td class="text-end"><strong>Grand Total (₹):</strong></td>
                    <td class="text-end" style="color: #0f766e; font-size: 13px;"><strong>₹{{ number_format($totalAmount, 2) }}</strong></td>
                </tr>
            </table>
        </div>

        <!-- 6. Amount in Words -->
        <div class="words-section">
            <strong>Amount in Words:</strong> {{ $amountInWords }}
        </div>

        <!-- 7. Tax Treatment Note -->
        <div class="tax-note">
            @if($isSameState)
                "Tax treatment: Intra-State supply — CGST ({{ number_format($cgstRate, 0) }}%) + SGST ({{ number_format($sgstRate, 0) }}%) applied as Supplier ({{ $supplierState }}) and Recipient ({{ $customerState }}) are in the same state."
            @else
                "Tax treatment: Inter-State supply — IGST ({{ number_format($igstRate, 0) }}%) applied as Supplier ({{ $supplierState }}, Code: {{ $supplierStateCode }}) and Recipient ({{ $customerState }}, Code: {{ $customerStateCode }}) are in different states."
            @endif
        </div>

        <!-- 8. Bank Details & Terms -->
        <div class="bottom-grid">
            <div>
                <strong>Bank / UPI Details:</strong><br>
                Ledger Settlement: ADC Virtual Wallet Ledger<br>
                Beneficiary: ABHHYAM DISTRIBUTORS PRIVATE LIMITED<br>
                Mode: Internal Daily Automated Debit
            </div>
            <div>
                <strong>Terms & Conditions:</strong><br>
                1. Monthly platform subscription charges are automated and non-refundable.<br>
                2. Billed as per daily active catalog & POS system usage agreement.<br>
                3. Subject to Delhi jurisdiction.
            </div>
        </div>

        <!-- 9. Signatures -->
        <div class="signature-grid">
            <div class="sig-left">
                __________________________________<br>
                <strong>Customer Signature</strong> (if required)
            </div>
            <div class="sig-right">
                For <strong>ASC DISTRIBUTOR PRIVATE LIMITED</strong><br><br><br>
                __________________________________<br>
                <strong>Authorised Signatory</strong>
            </div>
        </div>
    </div>

</body>
</html>
