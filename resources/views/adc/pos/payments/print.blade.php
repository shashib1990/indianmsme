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

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin: 3px 0;
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

        $adcAddress = $adcProfile?->address ?: ($adcUser?->name ?: 'ADC Canteen Store');
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
        
        <!-- Permanent Branding -->
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

        <!-- Receipt Header Title -->
        <div class="text-center">
            <div class="fw-bold fs-13" style="letter-spacing: 0.5px;">UDHAAR PAYMENT RECEIPT</div>
            <div class="fs-10" style="margin-top: 2px;">
                {{ $adcAddress }}
                @if($adcDistrict || $adcState)
                    <br>{{ $adcDistrict }}{{ $adcDistrict && $adcState ? ', ' : '' }}{{ $adcState }} - {{ $adcPincode }}
                @endif
                @if($adcGstin && $adcGstin !== 'N/A')
                    <br>ADC GSTIN: <strong>{{ $adcGstin }}</strong>
                @endif
            </div>
        </div>

        <div class="dashed-line"></div>

        <!-- Payment Meta Details -->
        <div class="fs-10">
            <div class="summary-row">
                <span><strong>Receipt No:</strong> {{ $payment->payment_number }}</span>
                <span><strong>Date:</strong> {{ $payment->payment_date->format('d/m/Y H:i') }}</span>
            </div>
            <div class="summary-row">
                <span><strong>Customer:</strong> {{ $customer?->name ?: 'Walk-in Customer' }}</span>
                <span>Mob: {{ $customer?->mobile ?: '—' }}</span>
            </div>
            <div class="summary-row">
                <span><strong>Payment Mode:</strong> {{ $payment->payment_mode }}</span>
            </div>
            @if($payment->remarks)
                <div class="summary-row" style="margin-top: 2px;">
                    <span><strong>Remarks:</strong> {{ $payment->remarks }}</span>
                </div>
            @endif
        </div>

        <div class="double-line"></div>

        <div class="summary-row fw-bold fs-14 text-center" style="display: block; margin: 8px 0;">
            <div>AMOUNT RECEIVED</div>
            <div class="fs-18" style="margin-top: 2px;">₹{{ number_format($payment->amount, 2) }}</div>
        </div>

        <div class="double-line"></div>

        <!-- Remaining Outstanding Summary -->
        <div class="fs-10">
            <div class="summary-row">
                <span>Total Credit Given:</span>
                <span>₹{{ number_format($customer?->total_credit_given ?: 0, 2) }}</span>
            </div>
            <div class="summary-row">
                <span>Total Payment Received:</span>
                <span>₹{{ number_format($customer?->total_payment_received ?: 0, 2) }}</span>
            </div>
            <div class="dashed-line"></div>
            <div class="summary-row fw-bold fs-12">
                <span>REMAINING OUTSTANDING:</span>
                <span class="{{ ($customer?->outstanding_balance ?: 0) > 0 ? 'text-danger' : 'text-success' }}">
                    ₹{{ number_format($customer?->outstanding_balance ?: 0, 2) }}
                </span>
            </div>
        </div>

        <div class="dashed-line"></div>

        <!-- Permanent Footer -->
        <div class="text-center fs-9" style="margin-top: 10px; line-height: 1.4;">
            <div class="fw-bold">*** PAYMENT RECEIVED ACKNOWLEDGEMENT ***</div>
            <div class="fw-bold" style="margin-top: 2px;">Thank you for your payment!</div>
            <div style="color: #444; font-size: 8.5px;">Initiative of Ardh Sainik Welfare Trust (Regd)</div>
            <div style="font-size: 8.5px; text-decoration: underline;">www.ArdhsainikCanteen.in</div>
            <div style="font-size: 9px; font-weight: bold; margin-top: 2px;">|| जय हिन्द || वन्दे मातरम ||</div>
            <div style="font-size: 8px; color: #666; margin-top: 2px;">This is a computer generated payment receipt</div>
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
