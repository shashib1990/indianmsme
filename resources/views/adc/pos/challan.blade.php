<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delivery Challan - {{ $challanNo }}</title>

    <style>
        @page {
            size: A4 portrait;
            margin: 10mm 12mm;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: Arial, "Helvetica Neue", Helvetica, sans-serif;
            font-size: 11pt;
            color: #000;
            background: #f1f5f9;
            line-height: 1.4;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* Screen Toolbar */
        .no-print-bar {
            background: #1e293b;
            color: #fff;
            padding: 10px 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            position: sticky;
            top: 0;
            z-index: 9999;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }

        .btn-print {
            background: #16a34a;
            color: #fff;
            border: none;
            padding: 8px 22px;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            font-size: 14px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .btn-print:hover {
            background: #15803d;
        }

        .btn-close-win {
            background: #ef4444;
            color: #fff;
            border: none;
            padding: 8px 20px;
            border-radius: 6px;
            font-weight: 700;
            cursor: pointer;
            font-size: 14px;
        }

        .btn-close-win:hover {
            background: #dc2626;
        }

        /* A4 Container */
        .challan-wrapper {
            width: 190mm;
            max-width: 100%;
            margin: 20px auto;
            background: #fff;
            padding: 8mm;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.08);
            box-sizing: border-box;
        }

        /* Challan Box with crisp borders */
        .challan-main-box {
            border: 2px solid #000;
            width: 100%;
            background: #fff;
        }

        .challan-title-block {
            text-align: center;
            padding: 8px 10px 6px;
            background: #eaf2fa;
            border-bottom: 2px solid #000;
        }

        .challan-title {
            font-size: 17pt;
            font-weight: 800;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            line-height: 1.2;
            color: #000;
        }

        .challan-subtitle {
            font-size: 9.5pt;
            font-weight: 500;
            color: #111;
            margin-top: 2px;
        }

        /* Header Details: Company & Challan No */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #000;
        }

        .header-table td {
            vertical-align: top;
            padding: 8px 12px;
            font-size: 10pt;
        }

        .header-company-col {
            width: 58%;
            border-right: 2px solid #000;
        }

        .header-challan-col {
            width: 42%;
        }

        .company-label {
            font-size: 10.5pt;
            font-weight: 700;
            color: #000;
            margin-bottom: 3px;
        }

        .company-name {
            font-size: 11pt;
            font-weight: 800;
            color: #000;
        }

        .company-meta {
            font-size: 9pt;
            line-height: 1.35;
            color: #111;
            margin-top: 3px;
        }

        .customer-meta {
            margin-top: 8px;
            padding-top: 6px;
            border-top: 1px dashed #666;
            font-size: 9.5pt;
        }

        .meta-line {
            display: flex;
            align-items: baseline;
            margin-bottom: 4px;
            font-size: 10pt;
        }

        .meta-line .lbl {
            font-weight: 700;
            min-width: 95px;
            color: #000;
        }

        .meta-line .val {
            font-weight: 700;
            border-bottom: 1px solid #000;
            flex: 1;
            padding-bottom: 1px;
            padding-left: 4px;
        }

        /* Items Grid */
        table.items-grid {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #000;
            font-size: 9.5pt;
            table-layout: fixed;
        }

        table.items-grid th {
            background: #eaf2fa;
            color: #000;
            font-weight: 800;
            font-size: 9.5pt;
            padding: 6px 4px;
            border-bottom: 2px solid #000;
            border-right: 1px solid #000;
            text-align: center;
        }

        table.items-grid th:last-child {
            border-right: none;
        }

        table.items-grid td {
            padding: 5px 6px;
            border-bottom: 1px solid #000;
            border-right: 1px solid #000;
            vertical-align: middle;
            color: #000;
            height: 28px;
            box-sizing: border-box;
        }

        table.items-grid td:last-child {
            border-right: none;
        }

        .col-sno { width: 6.5%; text-align: center; }
        .col-desc { width: 39%; text-align: left; }
        .col-hsn { width: 13%; text-align: center; }
        .col-qty { width: 9.5%; text-align: center; }
        .col-unit { width: 8.5%; text-align: center; }
        .col-rate { width: 11%; text-align: right; }
        .col-amount { width: 12.5%; text-align: right; }

        /* Total Row */
        .total-row td {
            background: #f8fafc;
            border-top: 2px solid #000 !important;
            border-bottom: none !important;
            font-size: 11pt;
            font-weight: 800;
            padding: 6px 10px;
        }

        /* Middle Note Section */
        .middle-notes-block {
            padding: 8px 12px;
            border-bottom: 2px solid #000;
            font-size: 9.5pt;
            line-height: 1.6;
        }

        .note-row {
            margin-bottom: 5px;
        }

        .note-row strong {
            font-weight: 700;
            color: #000;
        }

        .note-underline {
            border-bottom: 1px solid #000;
            padding-bottom: 1px;
            display: inline;
        }

        .declaration-text {
            margin-top: 6px;
            font-size: 9pt;
            line-height: 1.35;
        }

        /* Signature Section */
        table.signature-grid {
            width: 100%;
            border-collapse: collapse;
            border-bottom: 2px solid #000;
        }

        table.signature-grid th {
            width: 33.33%;
            text-align: center;
            font-size: 10pt;
            font-weight: 800;
            padding: 6px;
            border-bottom: 1px solid #000;
            border-right: 1px solid #000;
            background: #f8fafc;
        }

        table.signature-grid th:last-child {
            border-right: none;
        }

        table.signature-grid td {
            width: 33.33%;
            height: 70px;
            vertical-align: bottom;
            text-align: center;
            padding: 8px 12px;
            border-right: 1px solid #000;
        }

        table.signature-grid td:last-child {
            border-right: none;
        }

        .sign-field {
            font-size: 9pt;
            margin-bottom: 4px;
            font-weight: 600;
        }

        .sign-line {
            border-bottom: 1px solid #000;
            width: 85%;
            margin: 0 auto;
        }

        /* Bottom Statutory Note */
        .bottom-statutory-note {
            padding: 6px 12px;
            font-size: 8.5pt;
            color: #111;
            line-height: 1.35;
        }

        /* Print Media Query */
        @media print {
            html, body {
                width: 100%;
                margin: 0 !important;
                padding: 0 !important;
                background: #fff !important;
            }
            .no-print-bar {
                display: none !important;
            }
            .challan-wrapper {
                width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
                box-shadow: none !important;
                background: #fff !important;
            }
            .challan-main-box {
                border: 2px solid #000 !important;
            }
            .challan-title-block {
                background: #eaf2fa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            table.items-grid th {
                background: #eaf2fa !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
            .total-row td {
                background: #f8fafc !important;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
</head>
<body>

    <!-- On-screen Action Toolbar -->
    <div class="no-print-bar">
        <button class="btn-print" onclick="window.print()">🖨️ Print Delivery Challan (A4)</button>
        <button class="btn-close-win" onclick="window.close()">✖ Close Window</button>
    </div>

    @php
        $adcProf = $order->adc?->adcProfile ?: $order->adc?->profile;
        $companyName = $adcProf?->company_name ?: ($adcProf?->trade_name ?: ($order->adc?->name ?: 'ADC Store'));
        $companyAddress = $adcProf?->address ?: '';
        $companyDistrict = $adcProf?->district ?: '';
        $companyState = $adcProf?->state ?: '';
        $companyPincode = $adcProf?->pincode ?: '';
        $companyGstin = $order->invoice?->supplier_gstin ?: ($adcProf?->gst_number ?: ($order->adc?->gst_number ?: 'N/A'));
        $companyPhone = $order->adc?->mobile ?: ($adcProf?->mobile ?? '');

        $items = $order->items;
        $itemCount = $items->count();
        $minRows = 8; // Match the exact 8-row design from image 2
        $rowsToPad = max(0, $minRows - $itemCount);
    @endphp

    <div class="challan-wrapper">
        <div class="challan-main-box">

            <!-- Title Header -->
            <div class="challan-title-block">
                <div class="challan-title">DELIVERY CHALLAN</div>
                <div class="challan-subtitle">(Under GST Rules, where applicable)</div>
            </div>

            <!-- Header: Company & Challan Details -->
            <table class="header-table">
                <tr>
                    <td class="header-company-col">
                        <div class="company-label">Company Name:</div>
                        <div class="company-name">{{ $companyName }}</div>
                        <div class="company-meta">
                            @if($companyAddress)
                                {{ $companyAddress }}<br>
                            @endif
                            @if($companyDistrict || $companyState)
                                {{ $companyDistrict }}{{ $companyDistrict && $companyState ? ', ' : '' }}{{ $companyState }} - {{ $companyPincode }}<br>
                            @endif
                            @if($companyPhone)
                                Phone: {{ $companyPhone }}
                            @endif
                            @if($companyGstin && $companyGstin !== 'N/A')
                                {{ $companyPhone ? ' | ' : '' }}GSTIN: <strong>{{ $companyGstin }}</strong>
                            @endif
                        </div>

                        <div class="customer-meta">
                            <strong>Consignee / Customer:</strong> {{ $order->customer_name ?: 'Walk-in Customer' }}
                            @if($order->customer_mobile)
                                <br>Mobile: {{ $order->customer_mobile }}
                            @endif
                        </div>
                    </td>
                    <td class="header-challan-col">
                        <div class="meta-line">
                            <span class="lbl">Challan No.:</span>
                            <span class="val">{{ $challanNo }}</span>
                        </div>
                        <div class="meta-line">
                            <span class="lbl">Date:</span>
                            <span class="val">{{ $order->created_at->format('d-m-Y h:i A') }}</span>
                        </div>
                        <div class="meta-line">
                            <span class="lbl">Order No.:</span>
                            <span class="val">{{ $order->order_no }}</span>
                        </div>
                        <div class="meta-line">
                            <span class="lbl">Payment:</span>
                            <span class="val">{{ strtoupper($order->payment_mode ?: 'Cash') }}</span>
                        </div>
                    </td>
                </tr>
            </table>

            <!-- Items Grid -->
            <table class="items-grid">
                <thead>
                    <tr>
                        <th class="col-sno">S. No.</th>
                        <th class="col-desc">Description of Goods</th>
                        <th class="col-hsn">HSN Code</th>
                        <th class="col-qty">Quantity</th>
                        <th class="col-unit">Unit</th>
                        <th class="col-rate">Rate (₹)</th>
                        <th class="col-amount">Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $sno = 1; @endphp
                    @foreach($items as $item)
                        <tr>
                            <td class="col-sno">{{ $sno++ }}</td>
                            <td class="col-desc" style="font-weight: 600;">{{ $item->product_name }}</td>
                            <td class="col-hsn">{{ $item->hsn_code ?: '—' }}</td>
                            <td class="col-qty">{{ (float) $item->quantity }}</td>
                            <td class="col-unit">{{ $item->unit_of_measure ?: 'Pcs' }}</td>
                            <td class="col-rate">{{ number_format($item->effective_rate ?? $item->unit_price, 2) }}</td>
                            <td class="col-amount">{{ number_format($item->line_amount ?? $item->total_price, 2) }}</td>
                        </tr>
                    @endforeach

                    {{-- Pad empty rows to maintain exactly the 8-row voucher layout in Image 2 --}}
                    @for($i = 0; $i < $rowsToPad; $i++)
                        <tr>
                            <td class="col-sno">{{ $sno++ }}</td>
                            <td class="col-desc">&nbsp;</td>
                            <td class="col-hsn">&nbsp;</td>
                            <td class="col-qty">&nbsp;</td>
                            <td class="col-unit">&nbsp;</td>
                            <td class="col-rate">&nbsp;</td>
                            <td class="col-amount">&nbsp;</td>
                        </tr>
                    @endfor

                    <!-- Total Row -->
                    <tr class="total-row">
                        <td colspan="6" style="text-align: right; font-weight: 800;">TOTAL</td>
                        <td style="text-align: right; font-weight: 800;">₹{{ number_format($order->total_amount, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            <!-- Middle Notes & Declaration -->
            <div class="middle-notes-block">
                <div class="note-row">
                    <strong>Total Amount in Words:</strong>
                    <span class="note-underline">{{ $amountInWords }}</span>
                </div>
                <div class="note-row">
                    <strong>Reason / Remarks:</strong>
                    <span class="note-underline">{{ $order->remarks ?: 'Supply / Delivery of Goods for Retail Order' }}</span>
                </div>
                <div class="declaration-text">
                    <strong>Declaration:</strong> Goods mentioned above are being sent for the purpose stated in this challan. This document is not a tax invoice.
                </div>
            </div>

            <!-- Signature Section -->
            <table class="signature-grid">
                <thead>
                    <tr>
                        <th>Prepared By</th>
                        <th>Received By</th>
                        <th>Authorised Signatory</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <div class="sign-field">Name / Signature:</div>
                            <div class="sign-line"></div>
                        </td>
                        <td>
                            <div class="sign-field">Name / Signature:</div>
                            <div class="sign-line"></div>
                        </td>
                        <td>
                            <div class="sign-field">Signature & Stamp:</div>
                            <div class="sign-line"></div>
                        </td>
                    </tr>
                </tbody>
            </table>

            <!-- Bottom Note -->
            <div class="bottom-statutory-note">
                <strong>Note:</strong> Use this challan only where permitted under applicable GST rules; issue a tax invoice for taxable sales where required.
            </div>

        </div>
    </div>

</body>
</html>

