<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tax Invoice - {{ $order->invoice ? $order->invoice->invoice_number : $order->order_no }}</title>
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
        <button class="btn-print" onclick="window.print()">🖨️ Print Invoice (80mm)</button>
        <button class="btn-close-win" onclick="window.close()">✖ Close Window</button>
    </div>

    <!-- 80mm Thermal Receipt Container -->
    <div class="receipt-container">
        
        <!-- Top Permanent Branding Header -->
        <div class="text-center">
            <!-- Top Logo -->
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

        <!-- Tax Invoice Title & DDC / Depot Details -->
        <div class="text-center">
            <div class="fw-bold fs-13">TAX INVOICE</div>
            @php
                $ddcProf = $order->ddc?->ddcProfile ?: $order->ddc?->profile;
                $ddcAddress = $ddcProf?->address ?: ($ddcProf?->ddc_address ?: ($order->ddc?->name ?: 'DDC Store'));
                $ddcDistrict = $ddcProf?->district ?: ($ddcProf?->ddc_district ?: '');
                $ddcState = $ddcProf?->state ?: ($ddcProf?->ddc_state ?: '');
                $ddcPincode = $ddcProf?->pincode ?: ($ddcProf?->ddc_pincode ?: '');
                $ddcGstin = $order->invoice?->supplier_gstin ?: ($ddcProf?->ddc_gst_number ?: ($ddcProf?->adpl_gst_number ?: 'N/A'));
                $ddcCode = $ddcProf?->ddc_code ?: 'DDB001';
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

        <!-- Invoice Meta Section -->
        <div class="fs-10">
            <div class="summary-row">
                <span>Invoice No:</span>
                <span class="fw-bold">{{ $order->invoice ? $order->invoice->invoice_number : $order->order_no }}</span>
            </div>
            <div class="summary-row">
                <span>Date & Time:</span>
                <span>{{ $order->created_at->format('d/m/Y h:i A') }}</span>
            </div>
            <div class="summary-row">
                <span>Payment Mode:</span>
                <span class="fw-bold text-uppercase" style="text-transform: uppercase;">{{ $order->payment_mode ?: 'CASH' }}</span>
            </div>
            <div class="summary-row">
                <span>Depot Code:</span>
                <span class="fw-bold">{{ $ddcCode }}</span>
            </div>
            <div class="summary-row">
                <span>Customer:</span>
                <span class="fw-bold">{{ strtoupper($order->customer_name ?: 'WALK-IN CUSTOMER') }}</span>
            </div>
            @if($order->customer_mobile)
            <div class="summary-row">
                <span>Mobile:</span>
                <span>{{ $order->customer_mobile }}</span>
            </div>
            @endif
            @php
                $custGstin = $order->customer?->gst_number ?: ($order->invoice?->bill_to_gstin && $order->invoice?->bill_to_gstin !== 'N/A' ? $order->invoice?->bill_to_gstin : null);
            @endphp
            @if($custGstin)
            <div class="summary-row">
                <span>Customer GSTIN:</span>
                <span class="fw-bold">{{ strtoupper($custGstin) }}</span>
            </div>
            @endif
        </div>

        <div class="dashed-line"></div>

        <!-- Items Table (Single Item & Code column) -->
        <table class="items-table">
            <thead>
                <tr>
                    <th style="width: 4%;" class="text-center">#</th>
                    <th style="width: 33%;" class="text-start">Item</th>
                    <th style="width: 10%;" class="text-center">Qty</th>
                    <th style="width: 13%;" class="text-end">MRP</th>
                    <th style="width: 12%;" class="text-end">Disc</th>
                    <th style="width: 13%;" class="text-end">Rate</th>
                    <th style="width: 15%;" class="text-end">Amount</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $totalQtySum = 0;
                    $totalMrpSum = 0;
                    $totalDiscSum = 0;
                    $totalTaxableSum = 0;
                    $totalTaxSum = 0;
                    $gstSummary = [];
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

                        $fmtMrp = fmod($itemMrp, 1) == 0 ? number_format($itemMrp, 0) : number_format($itemMrp, 2);
                        $fmtDisc = fmod($lineDisc, 1) == 0 ? number_format($lineDisc, 0) : number_format($lineDisc, 2);
                        $fmtRate = fmod($itemPrice, 1) == 0 ? number_format($itemPrice, 0) : number_format($itemPrice, 2);
                        $fmtAmt = number_format($itemTotal, 2);

                        // Resolve product-wise GST Rate
                        $gstRate = (float) (
                            $item->gst_rate
                            ?? $item->inventory?->gst_rate
                            ?? $item->product?->gst_rate
                            ?? $item->localProduct?->gst_rate
                            ?? $item->inventory?->product?->gst_rate
                            ?? $item->inventory?->localProduct?->gst_rate
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

                        $rateKey = (string) $gstRate;
                        if (!isset($gstSummary[$rateKey])) {
                            $gstSummary[$rateKey] = [
                                'rate' => $gstRate,
                                'taxable' => 0.00,
                                'tax' => 0.00,
                                'total' => 0.00,
                            ];
                        }
                        $gstSummary[$rateKey]['taxable'] += $itemTaxable;
                        $gstSummary[$rateKey]['tax'] += $itemTax;
                        $gstSummary[$rateKey]['total'] += $itemTotal;
                    @endphp
                    <tr>
                        <td class="text-center fs-9" style="padding: 2px 1px;">{{ $idx + 1 }}</td>
                        <td class="text-start" style="padding: 2px 1px; word-break: break-word;">
                            <div class="fw-bold fs-9" style="line-height: 1.15;">{{ $item->product_name }}</div>
                            <div class="fs-8 text-muted" style="font-size: 8px; color: #555;">
                                @if($item->product_code)Code: {{ $item->product_code }} | @endif GST: {{ number_format($gstRate, 0) }}%
                            </div>
                        </td>
                        <td class="text-center fs-9" style="padding: 2px 1px; line-height: 1.15;">
                            <span class="fw-bold">{{ $itemQty }}</span>
                            @if($item->unit_of_measure)
                                <br><span style="font-size: 8px; color: #444;">{{ $item->unit_of_measure }}</span>
                            @endif
                        </td>
                        <td class="text-end fs-9" style="padding: 2px 1px; white-space: nowrap;">
                            {{ $fmtMrp }}
                        </td>
                        <td class="text-end fs-9" style="padding: 2px 1px; white-space: nowrap;">
                            {{ $lineDisc > 0 ? $fmtDisc : '0' }}
                        </td>
                        <td class="text-end fs-9" style="padding: 2px 1px; white-space: nowrap;">
                            {{ $fmtRate }}
                        </td>
                        <td class="text-end fw-bold fs-9" style="padding: 2px 1px; white-space: nowrap;">
                            {{ $fmtAmt }}
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <div class="dashed-line"></div>

        @php
            $productDisc = max(0, $totalMrpSum - (float) $order->subtotal);
            $specialDisc = (float) ($order->special_discount_amount ?? 0);
            $totalDisc = $productDisc + $specialDisc;
        @endphp

        <!-- Summary & Totals -->
        <div class="fs-10">
            <div class="summary-row">
                <span>Total Quantity:</span>
                <span class="fw-bold">{{ $totalQtySum }}</span>
            </div>
            <div class="summary-row">
                <span>Total MRP:</span>
                <span>₹{{ number_format($totalMrpSum, 2) }}</span>
            </div>
            @if($productDisc > 0)
                <div class="summary-row">
                    <span>Product Discount:</span>
                    <span>-₹{{ number_format($productDisc, 2) }}</span>
                </div>
            @endif
            @if($specialDisc > 0)
                <div class="summary-row">
                    <span>Special Discount:</span>
                    <span>-₹{{ number_format($specialDisc, 2) }}</span>
                </div>
            @endif
            @if($productDisc > 0 && $specialDisc > 0)
                <div class="summary-row fw-bold">
                    <span>Total Discount:</span>
                    <span>-₹{{ number_format($totalDisc, 2) }}</span>
                </div>
            @endif

            @php
                $taxTotal = $totalTaxSum > 0 ? $totalTaxSum : (float) $order->tax_amount;
                $taxableAmount = $totalTaxableSum > 0 ? $totalTaxableSum : max(0, (float)$order->subtotal - $taxTotal);
                
                $isInterState = false;
                if ($order->invoice && $order->invoice->bill_to_state && $order->invoice->dispatch_from_state) {
                    $isInterState = strtolower(trim($order->invoice->bill_to_state)) !== strtolower(trim($order->invoice->dispatch_from_state));
                }
            @endphp

            <div class="summary-row">
                <span>Taxable Amount:</span>
                <span>₹{{ number_format($taxableAmount, 2) }}</span>
            </div>

            @if($taxTotal > 0)
                @if($isInterState)
                    <div class="summary-row">
                        <span>IGST:</span>
                        <span>+₹{{ number_format($taxTotal, 2) }}</span>
                    </div>
                @else
                    <div class="summary-row">
                        <span>CGST:</span>
                        <span>+₹{{ number_format($taxTotal / 2, 2) }}</span>
                    </div>
                    <div class="summary-row">
                        <span>SGST:</span>
                        <span>+₹{{ number_format($taxTotal / 2, 2) }}</span>
                    </div>
                @endif
            @else
                <div class="summary-row">
                    <span>CGST:</span>
                    <span>₹0.00</span>
                </div>
                <div class="summary-row">
                    <span>SGST:</span>
                    <span>₹0.00</span>
                </div>
            @endif

            <div class="double-line"></div>

            <div class="summary-row fw-bold fs-14">
                <span>Grand Total:</span>
                <span>₹{{ number_format($order->total_amount, 2) }}</span>
            </div>

            <div class="summary-row fw-bold fs-11">
                <span>Payment Mode:</span>
                <span class="text-uppercase" style="text-transform: uppercase;">{{ $order->payment_mode ?: 'CASH' }}</span>
            </div>

            <div class="double-line"></div>
        </div>

        <!-- GST Summary Table -->
        @if(count($gstSummary) > 0)
            <div class="text-center fw-bold fs-9" style="margin-top: 4px; margin-bottom: 2px;">GST SUMMARY BREAKDOWN</div>
            <table class="items-table" style="font-size: 8.5px;">
                <thead>
                    <tr>
                        <th style="width: 18%;" class="text-center">GST %</th>
                        <th style="width: 27%;" class="text-end">Taxable</th>
                        @if($isInterState)
                            <th style="width: 27%;" class="text-end">IGST</th>
                        @else
                            <th style="width: 27%;" class="text-end">CGST</th>
                            <th style="width: 27%;" class="text-end">SGST</th>
                        @endif
                        <th style="width: 28%;" class="text-end">Total Tax</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($gstSummary as $gData)
                        <tr>
                            <td class="text-center fs-8 fw-bold">{{ number_format($gData['rate'], 0) }}%</td>
                            <td class="text-end fs-8">₹{{ number_format($gData['taxable'], 2) }}</td>
                            @if($isInterState)
                                <td class="text-end fs-8">₹{{ number_format($gData['tax'], 2) }}</td>
                            @else
                                <td class="text-end fs-8">₹{{ number_format($gData['tax'] / 2, 2) }}</td>
                                <td class="text-end fs-8">₹{{ number_format($gData['tax'] / 2, 2) }}</td>
                            @endif
                            <td class="text-end fs-8 fw-bold">₹{{ number_format($gData['tax'], 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="dashed-line"></div>
        @endif

        <!-- Footer Branding & Taglines -->
        <div class="text-center fs-9" style="margin-top: 10px; line-height: 1.4;">
            <div class="fw-bold">Thank you for your business!</div>
            <div>Initiative of Ardh Sainik Welfare Trust (Regd)</div>
            <div>www.ArdhsainikCanteen.in</div>
            <div class="fw-bold" style="margin-top: 2px;">|| जय हिन्द || वन्दे मातरम् ||</div>
            <div class="text-muted fs-8" style="margin-top: 2px;">This is computer generated invoice</div>
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
