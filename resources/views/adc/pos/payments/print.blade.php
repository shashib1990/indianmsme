<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Receipt - {{ $payment->payment_number }}</title>
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
        .fs-18 { font-size: 18pt; }

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

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
            font-size: 10pt;
            color: #000;
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

        /* Screen Action Bar */
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
        $adcUser = $adcUser ?? $payment->adc;
        $adcProfile = $adcUser?->adcProfile ?? $adcUser?->profile;
        $customer = $payment->customer;

        $depotName = $adcProfile?->company_name ?: ($adcProfile?->trade_name ?: ($adcUser?->name ?: 'ADC Canteen'));
        $adcAddress = $adcProfile?->address ?: '';
        $adcDistrict = $adcProfile?->district ?: '';
        $adcState = $adcProfile?->state ?: '';
        $adcPincode = $adcProfile?->pincode ?: '';
        $adcGstin = $adcProfile?->gst_number ?: 'N/A';
    @endphp

    <!-- Action Toolbar -->
    <div class="no-print-bar">
        <button class="btn-print" onclick="window.print()">🖨️ Print Receipt (80mm)</button>
        <button class="btn-close-win" onclick="window.close()">✖ Close Window</button>
    </div>

    <!-- 80mm Receipt -->
    <div class="receipt-container">
        
        <!-- Permanent Branding Header -->
        <div class="text-center">
            @php
                $siteHeaderLogo = \App\Models\SystemSetting::getVal('website_header_logo', \App\Models\SystemSetting::getVal('panel_logo', asset('ecommerce-assets/images/logo.png')));
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

        <!-- Receipt Header Title -->
        <div class="text-center">
            <div class="doc-title">UDHAAR PAYMENT RECEIPT</div>
        </div>

        <div class="dashed-line"></div>

        <!-- Payment Meta Details -->
        <div class="meta-section">
            <div class="summary-row">
                <span>Receipt No:</span>
                <span class="fw-bold">{{ $payment->payment_number }}</span>
            </div>
            <div class="summary-row">
                <span>Date:</span>
                <span>{{ $payment->payment_date->format('d/m/Y H:i') }}</span>
            </div>
            <div class="summary-row">
                <span>Customer:</span>
                <span class="fw-bold">{{ $customer?->name ?: 'Walk-in Customer' }}</span>
            </div>
            @if($customer?->mobile)
                <div class="summary-row">
                    <span>Mobile:</span>
                    <span>{{ $customer->mobile }}</span>
                </div>
            @endif
            <div class="summary-row">
                <span>Payment Mode:</span>
                <span class="fw-bold">{{ $payment->payment_mode }}</span>
            </div>
            @if($payment->remarks)
                <div class="summary-row">
                    <span>Remarks:</span>
                    <span>{{ $payment->remarks }}</span>
                </div>
            @endif
        </div>

        <div class="double-line"></div>

        <div class="text-center" style="margin: 6px 0;">
            <div class="fw-bold" style="font-size: 11pt;">AMOUNT RECEIVED</div>
            <div class="fw-bold" style="font-size: 16pt; margin-top: 2px;">₹{{ number_format($payment->amount, 2) }}</div>
        </div>

        <div class="double-line"></div>

        <!-- Remaining Outstanding Summary -->
        <div class="meta-section">
            <div class="summary-row">
                <span>Total Credit Given:</span>
                <span>₹{{ number_format($customer?->total_credit_given ?: 0, 2) }}</span>
            </div>
            <div class="summary-row">
                <span>Total Payment Received:</span>
                <span>₹{{ number_format($customer?->total_payment_received ?: 0, 2) }}</span>
            </div>
            <div class="dashed-line"></div>
            <div class="summary-row fw-bold" style="font-size: 11pt;">
                <span>REMAINING OUTSTANDING:</span>
                <span class="{{ ($customer?->outstanding_balance ?: 0) > 0 ? 'text-danger' : 'text-success' }}">
                    ₹{{ number_format($customer?->outstanding_balance ?: 0, 2) }}
                </span>
            </div>
        </div>

        <div class="dashed-line"></div>

        <!-- Permanent Footer -->
        <div class="text-center footer-section">
            <div class="footer-headoffice">
                Head Office: No. B2, Block -C, Pocket -7, Sector 8, Rohini, New Delhi - 110085<br>
                Call: 8800001240/50/60 & Owner No. 9450668010<br>
                GSTIN: 09AXFPT1807Q1Z5 | Email: home@ardhsainik.com
            </div>
            <div class="dashed-line"></div>
            <div class="fw-bold" style="font-size: 9.5pt;">*** PAYMENT RECEIVED ACKNOWLEDGEMENT ***</div>
            <div class="fw-bold" style="margin-top: 2px;">Thank you for your payment!</div>
            <div style="font-size: 8.5pt;">Initiative of Ardh Sainik Welfare Trust (Regd)</div>
            <div style="font-size: 8.5pt; text-decoration: underline;">www.ArdhsainikCanteen.in</div>
            <div style="font-size: 9.5pt; font-weight: bold; margin-top: 2px;">|| जय हिन्द || वन्दे मातरम ||</div>
            <div style="font-size: 8pt; margin-top: 2px;">This is a computer generated payment receipt</div>
        </div>

    </div>

    <!-- Auto Print -->
    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 300);
        };
    </script>
</body>
</html>
