<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Return Receipt - {{ $salesReturn->return_number }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        body {
            font-family: 'Courier New', Courier, monospace, monospace;
            font-size: 11px;
            color: #000;
            background: #f8f9fa;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
        }
        .receipt-container {
            width: 80mm;
            max-width: 80mm;
            background: #fff;
            margin: 15px auto;
            padding: 10px 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-start { text-align: left; }
        .fw-bold { font-weight: bold; }
        .fs-8 { font-size: 8px; }
        .fs-9 { font-size: 9px; }
        .fs-10 { font-size: 10px; }
        .fs-11 { font-size: 11px; }
        .fs-12 { font-size: 12px; }
        .fs-13 { font-size: 13px; }
        .fs-14 { font-size: 14px; }
        .fs-16 { font-size: 16px; }

        .dashed-line {
            border-top: 1px dashed #000;
            margin: 6px 0;
        }
        .double-line {
            border-top: 2px solid #000;
            margin: 6px 0;
        }

        table.items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px;
            margin: 4px 0;
            table-layout: fixed;
        }
        table.items-table th, table.items-table td {
            padding: 3px 1px;
            vertical-align: top;
            box-sizing: border-box;
        }
        table.items-table th {
            border-bottom: 1px dashed #000;
            border-top: 1px dashed #000;
            font-weight: bold;
            font-size: 9.5px;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 2px 0;
        }

        /* Screen Action Bar (Hidden during printing) */
        .no-print-bar {
            background: #1e293b;
            color: #fff;
            padding: 10px;
            text-align: center;
            position: sticky;
            top: 0;
            z-index: 9999;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
        }
        .btn-print {
            background: #22c55e;
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-size: 13px;
            margin-right: 10px;
        }
        .btn-close-win {
            background: #ef4444;
            color: #fff;
            border: none;
            padding: 8px 18px;
            border-radius: 6px;
            font-weight: bold;
            cursor: pointer;
            font-size: 13px;
        }

        table.gst-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9px;
            margin-top: 4px;
        }
        table.gst-table th, table.gst-table td {
            padding: 2px 1px;
            text-align: right;
        }
        table.gst-table th:first-child, table.gst-table td:first-child {
            text-align: left;
        }

        @media print {
            .no-print-bar { display: none !important; }
            body { background: #fff; }
            .receipt-container {
                margin: 0 auto;
                box-shadow: none;
                padding: 2px 4px;
                width: 100%;
            }
        }
    </style>
</head>
<body>

    <!-- On-screen Action Toolbar -->
    <div class="no-print-bar">
        <button class="btn-print" onclick="window.print()">🖨️ Print Sales Return Receipt (80mm)</button>
        <button class="btn-close-win" onclick="window.close()">✖ Close Window</button>
    </div>

    <!-- 80mm Thermal Receipt Container -->
    <div class="receipt-container">
        
        <!-- Top Permanent Branding Header -->
        <div class="text-center">
            @php
                $siteHeaderLogo = \App\Models\SystemSetting::getVal('website_header_logo', \App\Models\SystemSetting::getVal('panel_logo', asset('ecommerce-assets/images/logo.png')));
            @endphp
            <img src="{{ $siteHeaderLogo }}" style="max-height: 52px; max-width: 180px; object-fit: contain; margin-bottom: 3px;" alt="Logo" onerror="this.style.display='none'">
            
            <div class="fw-bold fs-16" style="letter-spacing: 0.5px;">DDC India Mall</div>
            <div class="fs-9" style="line-height: 1.3; color: #111;">
                Office: No. B2, Block -C, Pocket -7,<br>
                Sector 8, Rohini, New Delhi - 110085<br>
                Call: 8800001240/50/60 & Owner No. 9450668010<br>
                Gstin No: 09AXFPT1807Q1Z5<br>
                Email: home@ardhsainik.com
            </div>
        </div>

        <div class="dashed-line"></div>

        <!-- Sales Return Receipt Title & DDC / Depot Details -->
        <div class="text-center">
            <div class="fw-bold fs-13" style="letter-spacing: 0.5px;">SALES RETURN RECEIPT</div>
            @php
                $ddcProf = $ddcUser?->ddcProfile ?: $ddcUser?->profile;
                $ddcAddress = $ddcProf?->address ?: ($ddcProf?->ddc_address ?: ($ddcUser?->name ?: 'DDC Store'));
                $ddcDistrict = $ddcProf?->district ?: ($ddcProf?->ddc_district ?: '');
                $ddcState = $ddcProf?->state ?: ($ddcProf?->ddc_state ?: '');
                $ddcPincode = $ddcProf?->pincode ?: ($ddcProf?->ddc_pincode ?: '');
                $ddcGstin = $salesReturn->order?->invoice?->supplier_gstin ?: ($ddcProf?->ddc_gst_number ?: ($ddcProf?->adpl_gst_number ?: 'N/A'));
                $origInvNo = $salesReturn->order?->invoice?->invoice_number ?: ($salesReturn->order?->order_no ?: '—');
            @endphp
            <div class="fs-10" style="margin-top: 2px;">
                {{ $ddcAddress }}
                @if($ddcDistrict || $ddcState)
                    <br>{{ $ddcDistrict }}{{ $ddcDistrict && $ddcState ? ', ' : '' }}{{ $ddcState }} - {{ $ddcPincode }}
                @endif
                @if($ddcGstin && $ddcGstin !== 'N/A')
                    <br>Depot GSTIN: <strong>{{ $ddcGstin }}</strong>
                @endif
            </div>
        </div>

        <div class="dashed-line"></div>

        <!-- Return Meta Details -->
        <div class="fs-10">
            <div class="summary-row">
                <span><strong>Return No:</strong> {{ $salesReturn->return_number }}</span>
                <span><strong>Date:</strong> {{ $salesReturn->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="summary-row">
                <span><strong>Orig Invoice:</strong> {{ $origInvNo }}</span>
                <span><strong>Refund:</strong> {{ $salesReturn->refund_mode }}</span>
            </div>
            <div class="summary-row">
                <span><strong>Customer:</strong> {{ $salesReturn->customer_name }}</span>
                <span>{{ $salesReturn->customer_mobile ? 'Mob: '.$salesReturn->customer_mobile : '' }}</span>
            </div>
            @if($salesReturn->return_reason)
            <div class="summary-row" style="margin-top: 2px;">
                <span><strong>Reason:</strong> {{ $salesReturn->return_reason }}</span>
            </div>
            @endif
        </div>

        <div class="dashed-line"></div>

        <!-- Returned Items Table -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 5%;">#</th>
                    <th style="width: 45%;">Item</th>
                    <th class="text-center" style="width: 15%;">Qty</th>
                    <th class="text-end" style="width: 17%;">Rate</th>
                    <th class="text-end" style="width: 18%;">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($salesReturn->items as $idx => $item)
                    <tr>
                        <td>{{ $idx + 1 }}</td>
                        <td>
                            <div class="fw-bold" style="word-break: break-word;">{{ $item->product_name }}</div>
                            <div class="fs-8" style="color: #444;">
                                Code: {{ $item->product_code ?: '—' }} | GST: {{ number_format($item->gst_rate, 0) }}%
                            </div>
                        </td>
                        <td class="text-center">{{ (float)$item->returned_quantity }} <br><span class="fs-8 text-muted">{{ $item->unit_of_measure }}</span></td>
                        <td class="text-end">₹{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end fw-bold">₹{{ number_format($item->total_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="dashed-line"></div>

        <!-- Totals Summary -->
        <div class="fs-10">
            <div class="summary-row">
                <span>Returned Qty:</span>
                <span class="fw-bold">{{ (float)$salesReturn->total_qty }}</span>
            </div>
            <div class="summary-row">
                <span>Returned Taxable Amount:</span>
                <span>₹{{ number_format($salesReturn->subtotal, 2) }}</span>
            </div>
            <div class="summary-row">
                <span>Returned GST Amount:</span>
                <span>+₹{{ number_format($salesReturn->tax_amount, 2) }}</span>
            </div>
        </div>

        <div class="double-line"></div>

        <div class="summary-row fw-bold fs-13">
            <span>TOTAL REFUND AMOUNT:</span>
            <span>₹{{ number_format($salesReturn->total_return_amount, 2) }}</span>
        </div>

        <div class="double-line"></div>

        {{-- GST Summary Breakdown --}}
        @php
            $gstSummary = [];
            foreach($salesReturn->items as $item) {
                $r = (float)$item->gst_rate;
                if(!isset($gstSummary[$r])) {
                    $gstSummary[$r] = ['taxable' => 0, 'tax' => 0];
                }
                $gstSummary[$r]['taxable'] += (float)$item->taxable_amount;
                $gstSummary[$r]['tax'] += (float)$item->tax_amount;
            }
            ksort($gstSummary);
        @endphp

        @if(count($gstSummary) > 0)
            <div class="text-center fw-bold fs-9" style="margin-top: 4px;">GST SUMMARY BREAKDOWN</div>
            <table class="gst-table">
                <thead>
                    <tr style="border-bottom: 1px dashed #000; border-top: 1px dashed #000;">
                        <th>GST %</th>
                        <th>Taxable</th>
                        <th>CGST</th>
                        <th>SGST</th>
                        <th>Total Tax</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($gstSummary as $rate => $vals)
                        @php
                            $halfTax = round($vals['tax'] / 2, 2);
                        @endphp
                        <tr>
                            <td>{{ $rate }}%</td>
                            <td>₹{{ number_format($vals['taxable'], 2) }}</td>
                            <td>₹{{ number_format($halfTax, 2) }}</td>
                            <td>₹{{ number_format($halfTax, 2) }}</td>
                            <td>₹{{ number_format($vals['tax'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="dashed-line"></div>
        @endif

        <!-- Permanent Footer -->
        <div class="text-center fs-9" style="margin-top: 8px; line-height: 1.4;">
            <div class="fw-bold">*** REFUND / CREDIT NOTE RECEIPT ***</div>
            <div class="fw-bold" style="margin-top: 2px;">Thank you for your business!</div>
            <div style="color: #444; font-size: 8.5px;">Initiative of Ardh Sainik Welfare Trust (Regd)</div>
            <div style="font-size: 8.5px; text-decoration: underline;">www.ArdhsainikCanteen.in</div>
            <div style="font-size: 9px; font-weight: bold; margin-top: 2px;">|| जय हिन्द || वन्दे मातरम ||</div>
            <div style="font-size: 8px; color: #666; margin-top: 2px;">This is computer generated sales return receipt</div>
        </div>

    </div>

</body>
</html>
