<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Tax Invoice - {{ $order->invoice ? $order->invoice->invoice_number : $order->order_no }}</title>
    <style>
        @page {
            size: 80mm auto;
            margin: 5mm;
        }
        body {
            font-family: DejaVu Sans, -apple-system, BlinkMacSystemFont, "Segoe UI", Arial, sans-serif;
            font-size: 8pt;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 0;
        }
        .text-center { text-align: center; }
        .text-end { text-align: right; }
        .text-start { text-align: left; }
        .fw-bold { font-weight: bold; }
        
        .dashed-line {
            border-top: 1px dashed #000;
            margin: 5px 0;
        }
        .double-line {
            border-top: 2px solid #000;
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        .items-table th, .items-table td {
            padding: 3px 1px;
            font-size: 7.5pt;
            vertical-align: top;
        }
        .items-table th {
            border-bottom: 1px dashed #000;
            border-top: 1px dashed #000;
            font-weight: bold;
        }
        .meta-table td {
            padding: 1px 0;
            font-size: 8pt;
        }
        .totals-table td {
            padding: 1px 0;
            font-size: 8pt;
        }
        .footer-text {
            font-size: 7pt;
            line-height: 1.3;
            text-align: center;
        }
    </style>
</head>
<body>
    @php
        $adcProf = $order->adc?->adcProfile ?: $order->adc?->profile;
        $depotName = $adcProf?->company_name ?: ($adcProf?->trade_name ?: ($order->adc?->name ?: 'ADC Canteen'));
        $adcAddress = $adcProf?->address ?: '';
        $adcDistrict = $adcProf?->district ?: '';
        $adcState = $adcProf?->state ?: '';
        $adcPincode = $adcProf?->pincode ?: '';
        $adcGstin = $order->invoice?->supplier_gstin ?: ($adcProf?->gst_number ?: '');
        $adcCode = $adcProf?->adc_code ?: ($order->adc?->adc_code ?: ($adcProf?->code ?: ('ADC' . str_pad($order->adc_id, 3, '0', STR_PAD_LEFT))));
        
        $logoPath = public_path('admin-assets/images/IndianMSME.png');
        $logoBase64 = '';
        if (file_exists($logoPath)) {
            $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoPath));
        }

        $jaiHindPath = public_path('admin-assets/images/jai-hind.png');
        $jaiHindBase64 = '';
        if (file_exists($jaiHindPath)) {
            $jaiHindBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($jaiHindPath));
        }
    @endphp

    <div class="text-center">
        @if($logoBase64)
            <img src="{{ $logoBase64 }}" style="max-height: 44px; max-width: 160px; margin-bottom: 3px;" alt="Logo">
        @endif
        <div style="font-size: 11pt; font-weight: bold;">Ardh Sainik Mall</div>
        <div style="font-size: 9pt; font-weight: bold;">{{ $depotName }}</div>
        <div style="font-size: 7.5pt;">
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
    <div class="text-center" style="font-size: 10pt; font-weight: bold; letter-spacing: 0.5px;">TAX INVOICE</div>
    <div class="dashed-line"></div>

    <table class="meta-table">
        <tr>
            <td class="text-start">Invoice No:</td>
            <td class="text-end fw-bold">{{ $order->invoice ? $order->invoice->invoice_number : $order->order_no }}</td>
        </tr>
        <tr>
            <td class="text-start">Date & Time:</td>
            <td class="text-end">{{ $order->created_at ? $order->created_at->format('d/m/Y h:i A') : now()->format('d/m/Y h:i A') }}</td>
        </tr>
        <tr>
            <td class="text-start">Payment Mode:</td>
            <td class="text-end fw-bold">{{ strtoupper($order->payment_mode ?: 'CASH') }}</td>
        </tr>
        <tr>
            <td class="text-start">ADC Code:</td>
            <td class="text-end fw-bold">{{ $adcCode }}</td>
        </tr>
        <tr>
            <td class="text-start">Customer:</td>
            <td class="text-end fw-bold">{{ strtoupper($order->customer_name ?: 'WALK-IN CUSTOMER') }}</td>
        </tr>
        @if($order->customer_mobile)
        <tr>
            <td class="text-start">Mobile:</td>
            <td class="text-end fw-bold">{{ $order->customer_mobile }}</td>
        </tr>
        @endif
    </table>

    <table class="items-table" style="margin-top: 4px;">
        <thead>
            <tr>
                <th style="width: 8%;" class="text-center">#</th>
                <th style="width: 44%;" class="text-start">Item</th>
                <th style="width: 12%;" class="text-center">Qty</th>
                <th style="width: 16%;" class="text-end">Rate</th>
                <th style="width: 20%;" class="text-end">Amount</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalQtySum = 0;
                $totalMrpSum = 0;
                $totalDiscSum = 0;
                $totalTaxableSum = 0;
                $totalTaxSum = 0;
            @endphp
            @foreach($order->items as $idx => $item)
                @php
                    $itemMrp = (float) ($item->mrp > 0 ? $item->mrp : $item->unit_price);
                    $itemPrice = (float) $item->unit_price;
                    $itemQty = (float) $item->quantity;
                    $itemTotal = (float) $item->total_price;
                    $lineDisc = max(0, ($itemMrp - $itemPrice) * $itemQty);

                    $totalQtySum += $itemQty;
                    $totalMrpSum += ($itemMrp * $itemQty);
                    $totalDiscSum += $lineDisc;

                    $gstRate = (float) (
                        $item->gst_rate
                        ?? $item->inventory?->gst_rate
                        ?? $item->product?->gst_rate
                        ?? $item->localProduct?->gst_rate
                        ?? 0.00
                    );

                    $subtotalVal = (float) $order->subtotal;
                    $specialDiscVal = (float) ($order->special_discount_amount ?? 0);
                    $itemSpecialDisc = $subtotalVal > 0 ? ($specialDiscVal * ($itemTotal / $subtotalVal)) : 0;
                    $netItemTotal = max(0, $itemTotal - $itemSpecialDisc);

                    if ($gstRate > 0) {
                        $itemTaxable = round($netItemTotal / (1 + ($gstRate / 100)), 2);
                        $itemTax = round($netItemTotal - $itemTaxable, 2);
                    } else {
                        $itemTaxable = $netItemTotal;
                        $itemTax = 0.00;
                    }

                    $totalTaxableSum += $itemTaxable;
                    $totalTaxSum += $itemTax;
                @endphp
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="text-start">
                        <div class="fw-bold">{{ $item->product_name }}</div>
                        <div style="font-size: 6.5pt; color: #333;">
                            @if($item->product_code)[{{ $item->product_code }}]@endif GST: {{ number_format($gstRate, 0) }}%
                        </div>
                    </td>
                    <td class="text-center">{{ $itemQty }}</td>
                    <td class="text-end">{{ number_format($itemPrice, 2) }}</td>
                    <td class="text-end fw-bold">{{ number_format($itemTotal, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="dashed-line"></div>

    @php
        $productDisc = max(0, $totalMrpSum - (float) $order->subtotal);
        $specialDisc = (float) ($order->special_discount_amount ?? 0);
        $totalDisc = $productDisc + $specialDisc;
        $taxTotal = $totalTaxSum > 0 ? $totalTaxSum : (float) $order->tax_amount;
        $taxableAmount = $totalTaxableSum > 0 ? $totalTaxableSum : max(0, (float)$order->subtotal - $taxTotal);
        
        $isInterState = false;
        if ($order->invoice && $order->invoice->bill_to_state && $order->invoice->dispatch_from_state) {
            $isInterState = strtolower(trim($order->invoice->bill_to_state)) !== strtolower(trim($order->invoice->dispatch_from_state));
        }
    @endphp

    <table class="totals-table">
        <tr>
            <td class="text-start">Total Items / Qty:</td>
            <td class="text-end fw-bold">{{ count($order->items) }} / {{ $totalQtySum }}</td>
        </tr>
        <tr>
            <td class="text-start">Total MRP:</td>
            <td class="text-end">₹{{ number_format($totalMrpSum, 2) }}</td>
        </tr>
        @if($totalDisc > 0)
        <tr>
            <td class="text-start">Total Discount:</td>
            <td class="text-end" style="color: #000;">-₹{{ number_format($totalDisc, 2) }}</td>
        </tr>
        @endif
        <tr>
            <td class="text-start">Taxable Amount:</td>
            <td class="text-end">₹{{ number_format($taxableAmount, 2) }}</td>
        </tr>
        @if($taxTotal > 0)
            @if($isInterState)
            <tr>
                <td class="text-start">IGST:</td>
                <td class="text-end">+₹{{ number_format($taxTotal, 2) }}</td>
            </tr>
            @else
            <tr>
                <td class="text-start">CGST:</td>
                <td class="text-end">+₹{{ number_format($taxTotal / 2, 2) }}</td>
            </tr>
            <tr>
                <td class="text-start">SGST:</td>
                <td class="text-end">+₹{{ number_format($taxTotal / 2, 2) }}</td>
            </tr>
            @endif
        @endif
    </table>

    <div class="double-line"></div>

    <table class="totals-table" style="font-size: 10pt; font-weight: bold;">
        <tr>
            <td class="text-start">Grand Total:</td>
            <td class="text-end">₹{{ number_format($order->total_amount, 2) }}</td>
        </tr>
        <tr>
            <td class="text-start" style="font-size: 8pt;">Paid By:</td>
            <td class="text-end" style="font-size: 8pt;">{{ strtoupper($order->payment_mode ?: 'CASH') }}</td>
        </tr>
    </table>

    <div class="double-line"></div>

    <div class="footer-text" style="margin-top: 6px;">
        <strong>Head Office:</strong> No. B2, Block -C, Pocket -7,<br>
        Sector 8, Rohini, New Delhi - 110085<br>
        Call: 8800001240/50/60 & Owner No: 9450668010<br>
        GSTIN: 09AXFPT1807Q1Z5 | Email: home@ardhsainik.com<br>
        <div class="dashed-line"></div>
        <strong>Thank you for your business!</strong><br>
        Initiative of Ardh Sainik Welfare Trust (Regd)<br>
        www.ArdhsainikCanteen.in<br>
        <div style="margin-top: 3px; margin-bottom: 2px;">
            @if($jaiHindBase64)
                <img src="{{ $jaiHindBase64 }}" style="height: 14px; max-width: 180px; display: inline-block;" alt="|| जय हिन्द || वन्दे मातरम् ||">
            @else
                <strong>|| जय हिन्द || वन्दे मातरम् ||</strong>
            @endif
        </div>
        <span style="font-size: 6pt; color: #555;">This is a computer generated invoice</span>
    </div>
</body>
</html>