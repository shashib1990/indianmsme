<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Return Receipt - {{ $salesReturn->return_number ?? ($return->return_number ?? '') }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 0;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, Helvetica, sans-serif;
            font-size: 10pt;
            color: #000;
            background: #f8f9fa;
            margin: 0;
            padding: 0;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            -webkit-font-smoothing: antialiased;
        }
        .receipt-container {
            width: 80mm;
            max-width: 80mm;
            background: #fff;
            margin: 15px auto;
            padding: 10px 8px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            box-sizing: border-box;
            color: #000;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-start { text-align: left; }
        .fw-bold { font-weight: 700; }
        .fs-8 { font-size: 8.5pt; }
        .fs-9 { font-size: 9pt; }
        .fs-10 { font-size: 10pt; }
        .fs-11 { font-size: 11pt; }
        .fs-12 { font-size: 12pt; }
        .fs-13 { font-size: 13pt; }
        .fs-14 { font-size: 14pt; }
        .fs-16 { font-size: 16pt; }

        .shop-title {
            font-size: 15pt;
            font-weight: 800;
            letter-spacing: 0.5px;
            line-height: 1.2;
            color: #000;
        }
        .depot-title {
            font-size: 11pt;
            font-weight: 700;
            text-transform: uppercase;
            margin-top: 3px;
            color: #000;
        }
        .depot-address {
            font-size: 9pt;
            line-height: 1.35;
            color: #000;
            margin-top: 2px;
        }
        .doc-title {
            font-size: 13pt;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #000;
        }
        .meta-section {
            font-size: 10pt;
            color: #000;
            line-height: 1.4;
        }

        .dashed-line {
            border-top: 1.5px dashed #000;
            margin: 6px 0;
        }
        .double-line {
            border-top: 2px solid #000;
            margin: 6px 0;
        }

        table.items-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 9.5pt;
            margin: 4px 0;
            table-layout: fixed;
            color: #000;
        }
        table.items-table th, table.items-table td {
            padding: 3px 1px;
            vertical-align: top;
            box-sizing: border-box;
            font-size: 9.5pt;
            color: #000;
        }
        table.items-table th {
            border-bottom: 1.5px dashed #000;
            font-weight: 700;
            font-size: 9.5pt;
            color: #000;
        }
        table.items-table .item-name {
            font-size: 9.5pt;
            font-weight: 700;
            line-height: 1.2;
            color: #000;
        }
        table.items-table .item-meta {
            font-size: 8pt;
            color: #000;
            line-height: 1.15;
            font-weight: 500;
        }
        table.items-table .item-price {
            font-size: 9.5pt;
            font-weight: 600;
            white-space: nowrap;
            color: #000;
        }
        .totals-section {
            font-size: 10pt;
            color: #000;
            line-height: 1.4;
        }
        .grand-total-row {
            font-size: 13pt;
            font-weight: 800;
            color: #000;
            margin: 3px 0;
        }
        .footer-section {
            font-size: 9pt;
            color: #000;
            line-height: 1.35;
            margin-top: 8px;
        }
        .footer-headoffice {
            font-size: 8.5pt;
            color: #000;
            margin-bottom: 6px;
            line-height: 1.3;
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

    @php
        $salesReturn = $salesReturn ?? ($return ?? null);
        $adcUser = $adcUser ?? ($salesReturn?->adc ?? null);
        $adcProfile = $adcProfile ?? ($adcUser?->adcProfile ?? ($adcUser?->profile ?? null));

        $invoice = $salesReturn?->order?->invoice 
            ?: ($salesReturn?->invoice 
                ?: \App\Models\Invoice::where('pos_order_id', $salesReturn?->pos_order_id)->where('type', 'adc_pos_retail')->first());

        $origInvNo = $invoice?->invoice_number 
            ?: ($salesReturn?->order?->invoice?->invoice_number 
                ?: ($salesReturn?->order?->order_no ?: '—'));

        $adcAddress = $adcProfile?->address ?: ($adcUser?->name ?: 'ADC Canteen Store');
        $adcDistrict = $adcProfile?->district ?: '';
        $adcState = $adcProfile?->state ?: '';
        $adcPincode = $adcProfile?->pincode ?: '';
        $adcGstin = $invoice?->supplier_gstin ?: ($adcProfile?->gst_number ?: 'N/A');
    @endphp

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
                $depotName = $adcProfile?->company_name ?: ($adcProfile?->trade_name ?: ($adcUser?->name ?: 'ADC Canteen'));
            @endphp
            <img src="{{ $siteHeaderLogo }}" style="max-height: 52px; max-width: 180px; object-fit: contain; margin-bottom: 3px;" alt="Logo" onerror="this.style.display='none'">
            
            <div class="shop-title">Ardh Sainik Mall</div>
            <div class="depot-title">{{ $depotName }}</div>
            <div class="depot-address">
                {{ $adcAddress }}
                @if($adcDistrict || $adcState)
                    <br>{{ $adcDistrict }}{{ $adcDistrict && $adcState ? ', ' : '' }}{{ $adcState }} - {{ $adcPincode }}
                @endif
                @if($adcGstin && $adcGstin !== 'N/A')
                    <br>GSTIN: <strong>{{ $adcGstin }}</strong>
                @endif
            </div>
        </div>

        <div class="dashed-line"></div>

        <!-- Sales Return Receipt Title Only -->
        <div class="text-center">
            <div class="doc-title">SALES RETURN RECEIPT</div>
        </div>

        <div class="dashed-line"></div>

        <!-- Return Meta Details -->
        <div class="meta-section">
            <div class="summary-row">
                <span><strong>Return No:</strong> {{ $salesReturn->return_number }}</span>
                <span><strong>Date:</strong> {{ $salesReturn->created_at->format('d/m/Y H:i') }}</span>
            </div>
            <div class="summary-row">
                <span><strong>Orig Invoice:</strong> {{ $origInvNo }}</span>
                <span><strong>Refund:</strong> {{ $salesReturn->refund_mode }}</span>
            </div>
            <div class="summary-row">
                <span><strong>Customer:</strong> {{ $salesReturn->customer_name ?: 'Walk-in Customer' }}</span>
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
                        <td class="text-center" style="padding: 3px 1px;">{{ $idx + 1 }}</td>
                        <td style="word-break: break-word; padding: 3px 1px;">
                            <div class="item-name">{{ $item->product_name }}</div>
                            <div class="item-meta">
                                Code: {{ $item->product_code ?: '—' }} | GST: {{ number_format($item->gst_rate, 0) }}%
                            </div>
                        </td>
                        <td class="text-center" style="padding: 3px 1px;"><span class="fw-bold">{{ (float)$item->returned_quantity }}</span> <br><span class="item-meta">{{ $item->unit_of_measure }}</span></td>
                        <td class="text-end item-price" style="padding: 3px 1px;">₹{{ number_format($item->unit_price, 2) }}</td>
                        <td class="text-end fw-bold item-price" style="padding: 3px 1px;">₹{{ number_format($item->total_amount, 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="dashed-line"></div>

        <!-- Totals Summary -->
        <div class="totals-section">
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

        <div class="summary-row grand-total-row">
            <span>TOTAL REFUND AMOUNT:</span>
            <span>₹{{ number_format($salesReturn->total_return_amount, 2) }}</span>
        </div>

        <div class="double-line"></div>

        <!-- Permanent Footer -->
        <div class="text-center footer-section">
            <div class="footer-headoffice">
                <strong>Head Office:</strong> No. B2, Block -C, Pocket -7,<br>
                Sector 8, Rohini, New Delhi - 110085<br>
                Call: 8800001240/50/60 & Owner No: 9450668010<br>
                GSTIN: 09AXFPT1807Q1Z5<br>
                Email: home@ardhsainik.com
            </div>
            <div class="dashed-line" style="margin-bottom: 6px;"></div>
            <div class="fw-bold">*** REFUND / SALES RETURN RECEIPT ***</div>
            <div class="fw-bold" style="margin-top: 2px;">Thank you for your business!</div>
            <div>Initiative of Ardh Sainik Welfare Trust (Regd)</div>
            <div>www.ArdhsainikCanteen.in</div>
            <div class="fw-bold" style="margin-top: 2px;">|| जय हिन्द || वन्दे मातरम ||</div>
            <div style="font-size: 8pt; color: #000; margin-top: 2px;">This is computer generated sales return receipt</div>
        </div>

    </div>

    <!-- Auto Print Trigger -->
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 300);
        };
    </script>
</body>
</html>
