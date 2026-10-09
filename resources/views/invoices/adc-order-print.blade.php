<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Tax Invoice - {{ $invoice->invoice_number }}</title>
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Iconify -->
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>

    <style>
        :root {
            --primary: #1e3a8a;
            --primary-light: #eff6ff;
            --slate-800: #1e293b;
            --slate-600: #475569;
            --slate-400: #94a3b8;
            --slate-100: #f1f5f9;
            --border-color: #cbd5e1;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--slate-800);
            background-color: #f8fafc;
            line-height: 1.5;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            padding: 40px 20px;
        }

        .invoice-wrapper {
            max-width: 900px;
            margin: 0 auto;
            background: #ffffff;
            border: 1px solid var(--border-color);
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            padding: 40px;
        }

        .toolbar {
            max-width: 900px;
            margin: 0 auto 24px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            padding: 10px 20px;
            font-size: 14px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid transparent;
        }

        .btn-secondary {
            background-color: #ffffff;
            color: var(--slate-600);
            border-color: var(--border-color);
        }

        .btn-secondary:hover {
            background-color: var(--slate-100);
        }

        .btn-primary {
            background-color: var(--primary);
            color: #ffffff;
        }

        .btn-primary:hover {
            background-color: #1e40af;
        }

        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--primary);
            padding-bottom: 20px;
            margin-bottom: 24px;
        }

        .company-header-info {
            text-align: right;
        }

        .company-header-info h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 24px;
            font-weight: 700;
            color: var(--primary);
            margin-bottom: 4px;
        }

        .company-header-info p {
            font-size: 12px;
            color: var(--slate-600);
        }

        .title-badge-container {
            text-align: center;
            margin-bottom: 24px;
        }

        .title-badge {
            background-color: var(--primary);
            color: #ffffff;
            padding: 6px 24px;
            border-radius: 20px;
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 16px;
            background-color: var(--slate-100);
            border-radius: 8px;
            padding: 16px;
            margin-bottom: 24px;
        }

        .info-box h3 {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--slate-600);
            margin-bottom: 8px;
            font-weight: 700;
        }

        .info-box p {
            font-size: 13px;
            color: var(--slate-800);
            margin-bottom: 4px;
        }

        .billing-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 24px;
        }

        .billing-card {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 16px;
            background-color: #ffffff;
        }

        .billing-card h3 {
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--primary);
            margin-bottom: 10px;
            font-weight: 700;
            border-bottom: 1px solid var(--slate-100);
            padding-bottom: 6px;
        }

        .billing-card p {
            font-size: 13px;
            color: var(--slate-800);
            margin-bottom: 4px;
        }

        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 24px;
        }

        .invoice-table th {
            background-color: var(--primary-light);
            color: var(--primary);
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            padding: 10px 12px;
            border: 1px solid var(--border-color);
            text-align: left;
        }

        .invoice-table td {
            font-size: 13px;
            padding: 12px;
            border: 1px solid var(--border-color);
        }

        .text-center { text-align: center; }
        .text-right { text-align: right; }

        .summary-wrapper {
            display: flex;
            gap: 20px;
            margin-bottom: 24px;
        }

        .words-and-tax {
            flex: 1;
        }

        .amount-in-words-box {
            background-color: var(--slate-100);
            border-radius: 8px;
            padding: 12px 16px;
            font-size: 13px;
            margin-bottom: 16px;
        }

        .tax-summary-title {
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--primary);
            margin-bottom: 8px;
        }

        .tax-summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
        }

        .tax-summary-table th {
            background-color: var(--slate-100);
            padding: 6px 8px;
            border: 1px solid var(--border-color);
            text-align: left;
            font-weight: 600;
        }

        .tax-summary-table td {
            padding: 6px 8px;
            border: 1px solid var(--border-color);
        }

        .totals-table-container {
            width: 320px;
            background-color: var(--slate-100);
            border-radius: 8px;
            padding: 16px;
            align-self: flex-start;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .totals-table td {
            padding: 6px 0;
        }

        .totals-table tr.grand-total td {
            font-size: 16px;
            font-weight: 700;
            color: var(--primary);
            border-top: 2px solid var(--primary);
            padding-top: 10px;
            margin-top: 6px;
        }

        .bottom-section {
            display: flex;
            justify-content: space-between;
            border-top: 1px solid var(--border-color);
            padding-top: 20px;
        }

        .terms-box {
            font-size: 11px;
            color: var(--slate-600);
            max-width: 500px;
        }

        .terms-box h4 {
            font-size: 12px;
            color: var(--slate-800);
            margin-bottom: 4px;
        }

        .terms-box ol {
            padding-left: 16px;
        }

        .signature-box {
            text-align: center;
            align-self: flex-end;
        }

        .signature-box p {
            font-size: 12px;
            font-weight: 600;
            margin-bottom: 40px;
        }

        .signature-line {
            border-top: 1px dashed var(--slate-400);
            padding-top: 4px;
            font-size: 11px;
            color: var(--slate-600);
        }

        @media print {
            body {
                background-color: white;
                padding: 0;
                color: black;
            }

            .invoice-wrapper {
                border: none;
                box-shadow: none;
                padding: 0;
                max-width: 100%;
            }

            .toolbar {
                display: none !important;
            }
        }
    </style>
</head>
<body>

    <!-- Toolbar for actions in browser view -->
    <div class="toolbar">
        <button onclick="if(window.history.length > 1 && document.referrer !== ''){ window.history.back(); } else { window.close(); }" class="btn btn-secondary">
            <iconify-icon icon="solar:arrow-left-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
            Back
        </button>
        <div>
            <button onclick="window.print()" class="btn btn-primary">
                <iconify-icon icon="solar:printer-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                Print Tax Invoice
            </button>
        </div>
    </div>

    <div class="invoice-wrapper">
        
        <!-- Header -->
        <div class="header-section" style="display: flex; justify-content: center; align-items: center; text-align: center; border-bottom: 2px solid var(--primary); padding-bottom: 20px; margin-bottom: 20px;">
            <div class="logo-container" style="display: flex; justify-content: center; align-items: center; width: 100%;">
                <img src="{{ \App\Models\SystemSetting::getVal('website_header_logo', \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png'))) }}" alt="Logo" class="logo-img" style="width: auto; height: 90px; object-fit: contain; margin: 0 auto;">
            </div>
        </div>

        <!-- TAX INVOICE Badge -->
        <div class="title-badge-container">
            <span class="title-badge">Tax Invoice</span>
        </div>

        @php
            $adcUser = $order->adc;
            $adcProfile = $adcUser?->adcProfile ?? $adcUser?->profile ?? null;
            $canteenName = $adcProfile?->company_name ?? ($adcProfile?->trade_name ?? ($adcUser?->name ?? ($invoice->bill_to_name ?: 'ADC Canteen')));
            $canteenAddress = $order->shipping_address ?: ($adcProfile?->address ?: ($invoice->bill_to_address ?: 'Address not available'));
            $canteenDistrict = $order->delivery_district ?: ($adcProfile?->district ?: ($invoice->bill_to_district ?: ''));
            $canteenState = $order->delivery_state ?: ($adcProfile?->state ?: ($invoice->bill_to_state ?: ''));
            $canteenPincode = $order->delivery_pincode ?: ($adcProfile?->pincode ?: ($invoice->bill_to_pincode ?: ''));
            $canteenPhone = $adcProfile?->official_phone ?? ($adcProfile?->phone ?? ($adcUser?->mobile ?? ($invoice->bill_to_phone ?: 'N/A')));
            $canteenGstin = $adcProfile?->gst_number ?? ($invoice->bill_to_gstin ?: 'N/A');

            $isPhysicalDdcOrder = ($order->fulfillment_type === 'physical') || !empty($order->selected_ddc_id) || !empty($order->assigned_ddc_id);

            $dispatchName = null;
            $dispatchAddress = null;
            $dispatchDistrict = null;
            $dispatchState = null;
            $dispatchPincode = null;
            $dispatchPhone = null;
            $dispatchGstin = null;

            if ($isPhysicalDdcOrder) {
                $physicalDdc = $order->selectedDdc ?? $order->assignedDdc ?? $order->virtualDdc ?? $ddc;
                $physicalDdcProfile = $physicalDdc?->ddcProfile ?? $physicalDdc?->profile ?? null;
                $dispatchName = $physicalDdcProfile?->company_name ?? ($physicalDdcProfile?->trade_name ?? ($physicalDdc?->name ?? 'Physical DDC'));
                $dispatchAddress = $physicalDdcProfile?->address ?? ($physicalDdcProfile?->ddc_address ?? 'Address not available');
                $dispatchDistrict = $physicalDdcProfile?->district ?? ($physicalDdc?->district ?? '');
                $dispatchState = $physicalDdcProfile?->state ?? ($physicalDdc?->state ?? '');
                $dispatchPincode = $physicalDdcProfile?->pincode ?? ($physicalDdc?->pincode ?? '');
                $dispatchPhone = $physicalDdcProfile?->official_phone ?? ($physicalDdcProfile?->phone ?? ($physicalDdc?->mobile ?? 'N/A'));
                $dispatchGstin = $physicalDdcProfile?->ddc_gst_number ?? ($physicalDdcProfile?->adpl_gst_number ?? ($physicalDdcProfile?->gst_number ?? 'N/A'));
            } else {
                // Virtual DDC Order -> Dispatch From is the Seller / Vendor or Pickup Store
                $poId = $order->items->pluck('purchase_order_id')->filter()->first();
                $po = $poId ? \App\Models\PurchaseOrder::with(['seller.sellerProfile', 'pickupStore.districtRelation', 'pickupStore.stateRelation', 'ddc.profile', 'ddc.ddcProfile'])->find($poId) : null;

                if ($po) {
                    if ($po->pickupStore) {
                        $ps = $po->pickupStore;
                        $dispatchName = $ps->pickup_store_name ?? ($ps->contact_person ?? 'Store Outlet');
                        $dispatchAddress = $ps->address ?? 'Address not available';
                        $dispatchDistrict = $ps->districtRelation?->name ?? ($ps->district ?? '');
                        $dispatchState = $ps->stateRelation?->name ?? ($ps->state ?? '');
                        $dispatchPincode = $ps->pincode ?? '';
                        $dispatchPhone = $ps->mobile ?? ($ps->contact_person ?? 'N/A');
                        $dispatchGstin = $ps->gst_number ?? 'N/A';
                    } elseif ($po->seller) {
                        $sel = $po->seller;
                        $selProfile = $sel->sellerProfile ?? null;
                        $dispatchName = $selProfile?->company_name ?? ($sel->name ?? 'Seller');
                        $dispatchAddress = $selProfile?->address ?? 'Address not available';
                        $dispatchDistrict = $selProfile?->district ?? ($sel->district ?? '');
                        $dispatchState = $selProfile?->state ?? ($sel->state ?? '');
                        $dispatchPincode = $selProfile?->pincode ?? ($sel->pincode ?? '');
                        $dispatchPhone = $selProfile?->alternate_mobile ?? ($sel->mobile ?? 'N/A');
                        $dispatchGstin = $selProfile?->gst_number ?? 'N/A';
                    }
                }

                if (!$dispatchName) {
                    $sellerId = $order->items->pluck('seller_id')->filter()->first();
                    if ($sellerId) {
                        $sel = \App\Models\User::with('sellerProfile')->find($sellerId);
                        $selProfile = $sel?->sellerProfile;
                        $dispatchName = $selProfile?->company_name ?? ($sel?->name ?? 'Seller');
                        $dispatchAddress = $selProfile?->address ?? 'Address not available';
                        $dispatchDistrict = $selProfile?->district ?? '';
                        $dispatchState = $selProfile?->state ?? '';
                        $dispatchPincode = $selProfile?->pincode ?? '';
                        $dispatchPhone = $selProfile?->alternate_mobile ?? ($sel?->mobile ?? 'N/A');
                        $dispatchGstin = $selProfile?->gst_number ?? 'N/A';
                    }
                }

                if (!$dispatchName) {
                    $dispatchName = $invoice->dispatch_from_name;
                    $dispatchAddress = $invoice->dispatch_from_address;
                    $dispatchDistrict = $invoice->dispatch_from_district;
                    $dispatchState = $invoice->dispatch_from_state;
                    $dispatchPincode = $invoice->dispatch_from_pincode;
                    $dispatchPhone = $invoice->dispatch_from_phone;
                    $dispatchGstin = $invoice->dispatch_from_gstin;
                }

                if (!$dispatchName) {
                    $ddcProfile = $ddc?->profile ?? $ddc?->ddcProfile ?? null;
                    $dispatchName = $ddcProfile?->company_name ?? ($ddc?->name ?? 'DDC Hub');
                    $dispatchAddress = $ddcProfile?->address ?? 'Address not available';
                    $dispatchDistrict = $ddcProfile?->district ?? '';
                    $dispatchState = $ddcProfile?->state ?? '';
                    $dispatchPincode = $ddcProfile?->pincode ?? '';
                    $dispatchPhone = $ddcProfile?->official_phone ?? ($ddcProfile?->phone ?? ($ddc?->mobile ?? 'N/A'));
                    $dispatchGstin = $ddcProfile?->ddc_gst_number ?? ($ddcProfile?->adpl_gst_number ?? ($ddcProfile?->gst_number ?? 'N/A'));
                }
            }
        @endphp

        <!-- Info Grid -->
        <div class="info-grid">
            <div class="info-box">
                <h3>Invoice From</h3>
                <p><strong>ASC Distributor Pvt. Ltd.</strong></p>
                <p><strong>Head Office:</strong> No. 82, Block C, Pocket - 7, Sector 8, Rohini, New Delhi, Delhi, India 110085</p>
                <p><strong>Gst No.:</strong> 07AAZCA4502F1ZM</p>
                <p><strong>Call Us:</strong> 8800001240/50/60</p>
                <p><strong>Mail Us:</strong> home@ardhsainik.com</p>
                <p><strong>Website:</strong> www.ardhsainikmall.com</p>
            </div>
            
            <div class="info-box">
                <h3>Invoice Details</h3>
                <p><strong>Invoice No:</strong> {{ $invoice->invoice_number }}</p>
                <p><strong>Invoice Date:</strong> {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M, Y') }}</p>
                <p><strong>Place of Supply:</strong> {{ strtoupper($invoice->bill_to_state ?? ($order->delivery_state ?? 'Delhi')) }}</p>
                <p><strong>Order No:</strong> {{ $order->order_number }}</p>
            </div>

            <div class="info-box">
                <h3>Transporter Details</h3>
                <p><strong>Transporter:</strong> {{ $invoice->transporter_name ?? 'N/A' }}</p>
                <p><strong>Vehicle No:</strong> {{ $invoice->vehicle_number ?? 'N/A' }}</p>
                <p><strong>Transporter GST:</strong> {{ $invoice->transporter_gst ?? 'N/A' }}</p>
                <p><strong>E-Way Bill No:</strong> {{ $invoice->eway_bill_number ?? 'N/A' }}</p>
            </div>
        </div>

        <!-- Billing & Shipping Details Row -->
        <div class="billing-row">
            <div class="billing-card">
                <h3>Bill / Ship To</h3>
                <p><strong>{{ $canteenName }}</strong></p>
                <p>{{ $canteenAddress }}</p>
                <p>
                    {{ $canteenDistrict }}{{ ($canteenDistrict && $canteenState) ? ', ' : '' }}{{ $canteenState }}{{ $canteenPincode ? ' - ' . $canteenPincode : '' }}
                </p>
                <p><strong>Phone:</strong> {{ $canteenPhone }}</p>
                <p><strong>GSTIN:</strong> {{ $canteenGstin }}</p>
            </div>
            
            <div class="billing-card">
                <h3>Dispatch From</h3>
                <p><strong>{{ $dispatchName }}</strong></p>
                <p>{{ $dispatchAddress }}</p>
                <p>
                    {{ $dispatchDistrict }}{{ ($dispatchDistrict && $dispatchState) ? ', ' : '' }}{{ $dispatchState }}{{ $dispatchPincode ? ' - ' . $dispatchPincode : '' }}
                </p>
                <p><strong>Phone:</strong> {{ $dispatchPhone }}</p>
                <p><strong>GSTIN:</strong> {{ $dispatchGstin }}</p>
            </div>
        </div>

        <!-- Invoice Table -->
        <table class="invoice-table">
            <thead>
                <tr>
                    <th style="width: 5%;" class="text-center">S.N.</th>
                    <th style="width: 35%;">Description of Goods</th>
                    <th style="width: 10%;" class="text-center">HSN Code</th>
                    <th style="width: 10%;" class="text-right">Rate/Item</th>
                    <th style="width: 7%;" class="text-center">Qty</th>
                    <th style="width: 10%;" class="text-right">Taxable Value</th>
                    <th style="width: 8%;" class="text-center">Tax Rate</th>
                    <th style="width: 15%;" class="text-right">Tax Value</th>
                    <th style="width: 15%;" class="text-right">Amount</th>
                </tr>
            </thead>
            <tbody>
                @foreach($processedItems as $index => $item)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td>
                            <strong>{{ $item['name'] }}</strong><br>
                            <span style="color: var(--slate-600); font-size: 10px;">Product Code: {{ $item['barcode'] }}</span>
                        </td>
                        <td class="text-center">{{ $item['hsn_code'] }}</td>
                        <td class="text-right">₹{{ number_format($item['rate_per_item'], 2) }}</td>
                        <td class="text-center">{{ $item['qty'] }}</td>
                        <td class="text-right">₹{{ number_format($item['taxable_value'], 2) }}</td>
                        <td class="text-center">{{ number_format($item['gst_rate'], 1) }}%</td>
                        <td class="text-right">
                            ₹{{ number_format($item['tax_value'], 2) }}
                            @if($item['tax_value'] > 0)
                                <br>
                                <span style="font-size: 9px; color: var(--slate-600);">
                                    @if($isIntraState)
                                        (CGST: {{ number_format($item['gst_rate'] / 2, 2) }}%, SGST: {{ number_format($item['gst_rate'] / 2, 2) }}%)
                                    @else
                                        (IGST: {{ number_format($item['gst_rate'], 1) }}%)
                                    @endif
                                </span>
                            @endif
                        </td>
                        <td class="text-right">₹{{ number_format($item['amount'], 2) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Summary Wrapper -->
        <div class="summary-wrapper">
            <div class="words-and-tax">
                <div class="amount-in-words-box">
                    <strong>Total Amount (in words):</strong><br>
                    {{ $amountInWords }}
                </div>
                
                <!-- Tax Summary -->
                <div class="tax-summary-title">Tax Summary Breakdown</div>
                <table class="tax-summary-table" style="width: 100%; border-collapse: collapse; font-size: 11px;">
                    @if(!$isIntraState)
                        <thead>
                            <tr>
                                <th rowspan="2" class="text-center" style="vertical-align: middle; border: 1px solid var(--border-color); font-weight: 700;">GST Rate (%)</th>
                                <th rowspan="2" class="text-center" style="vertical-align: middle; border: 1px solid var(--border-color); font-weight: 700;">Qty</th>
                                <th rowspan="2" class="text-right" style="vertical-align: middle; border: 1px solid var(--border-color); font-weight: 700;">Taxable Amount (₹)</th>
                                <th colspan="2" class="text-center" style="border: 1px solid var(--border-color); font-weight: 700;">IGST</th>
                                <th rowspan="2" class="text-right" style="vertical-align: middle; border: 1px solid var(--border-color); font-weight: 700;">Total Tax (₹)</th>
                                <th rowspan="2" class="text-right" style="vertical-align: middle; border: 1px solid var(--border-color); font-weight: 700;">Total Amount (₹)</th>
                            </tr>
                            <tr>
                                <th class="text-center" style="border: 1px solid var(--border-color); font-weight: 700;">Rate (%)</th>
                                <th class="text-right" style="border: 1px solid var(--border-color); font-weight: 700;">Amt (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($taxSummary as $summary)
                                <tr>
                                    <td class="text-center" style="border: 1px solid var(--border-color);">{{ number_format($summary['rate'], 1) }}%</td>
                                    <td class="text-center" style="border: 1px solid var(--border-color);">{{ $summary['qty'] ?? 0 }}</td>
                                    <td class="text-right" style="border: 1px solid var(--border-color);">{{ number_format($summary['taxable_value'], 2) }}</td>
                                    <td class="text-center" style="border: 1px solid var(--border-color);">{{ number_format($summary['igst_rate'] ?? $summary['rate'], 1) }}%</td>
                                    <td class="text-right" style="border: 1px solid var(--border-color);">{{ number_format($summary['igst_amount'] ?? $summary['tax_value'], 2) }}</td>
                                    <td class="text-right" style="border: 1px solid var(--border-color);">{{ number_format($summary['tax_value'], 2) }}</td>
                                    <td class="text-right" style="border: 1px solid var(--border-color);">{{ number_format($summary['amount'] ?? ($summary['taxable_value'] + $summary['tax_value']), 2) }}</td>
                                </tr>
                            @endforeach
                            <tr style="font-weight: 700; background-color: var(--slate-100);">
                                <td class="text-center" style="border: 1px solid var(--border-color);">TOTAL</td>
                                <td class="text-center" style="border: 1px solid var(--border-color);">{{ collect($taxSummary)->sum('qty') }}</td>
                                <td class="text-right" style="border: 1px solid var(--border-color);">{{ number_format(collect($taxSummary)->sum('taxable_value'), 2) }}</td>
                                <td class="text-center" style="border: 1px solid var(--border-color);"></td>
                                <td class="text-right" style="border: 1px solid var(--border-color);">{{ number_format(collect($taxSummary)->sum('igst_amount'), 2) }}</td>
                                <td class="text-right" style="border: 1px solid var(--border-color);">{{ number_format(collect($taxSummary)->sum('tax_value'), 2) }}</td>
                                <td class="text-right" style="border: 1px solid var(--border-color);">{{ number_format(collect($taxSummary)->sum('amount'), 2) }}</td>
                            </tr>
                        </tbody>
                    @else
                        <thead>
                            <tr>
                                <th rowspan="2" class="text-center" style="vertical-align: middle; border: 1px solid var(--border-color); font-weight: 700;">GST Rate (%)</th>
                                <th rowspan="2" class="text-center" style="vertical-align: middle; border: 1px solid var(--border-color); font-weight: 700;">Qty</th>
                                <th rowspan="2" class="text-right" style="vertical-align: middle; border: 1px solid var(--border-color); font-weight: 700;">Taxable Amount (₹)</th>
                                <th colspan="2" class="text-center" style="border: 1px solid var(--border-color); font-weight: 700;">CGST</th>
                                <th colspan="2" class="text-center" style="border: 1px solid var(--border-color); font-weight: 700;">SGST</th>
                                <th rowspan="2" class="text-right" style="vertical-align: middle; border: 1px solid var(--border-color); font-weight: 700;">Total Tax (₹)</th>
                                <th rowspan="2" class="text-right" style="vertical-align: middle; border: 1px solid var(--border-color); font-weight: 700;">Total Amount (₹)</th>
                            </tr>
                            <tr>
                                <th class="text-center" style="border: 1px solid var(--border-color); font-weight: 700;">Rate (%)</th>
                                <th class="text-right" style="border: 1px solid var(--border-color); font-weight: 700;">Amt (₹)</th>
                                <th class="text-center" style="border: 1px solid var(--border-color); font-weight: 700;">Rate (%)</th>
                                <th class="text-right" style="border: 1px solid var(--border-color); font-weight: 700;">Amt (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($taxSummary as $summary)
                                <tr>
                                    <td class="text-center" style="border: 1px solid var(--border-color);">{{ number_format($summary['rate'], 1) }}%</td>
                                    <td class="text-center" style="border: 1px solid var(--border-color);">{{ $summary['qty'] ?? 0 }}</td>
                                    <td class="text-right" style="border: 1px solid var(--border-color);">{{ number_format($summary['taxable_value'], 2) }}</td>
                                    <td class="text-center" style="border: 1px solid var(--border-color);">{{ number_format($summary['cgst_rate'] ?? ($summary['rate']/2), 1) }}%</td>
                                    <td class="text-right" style="border: 1px solid var(--border-color);">{{ number_format($summary['cgst_amount'] ?? ($summary['tax_value']/2), 2) }}</td>
                                    <td class="text-center" style="border: 1px solid var(--border-color);">{{ number_format($summary['sgst_rate'] ?? ($summary['rate']/2), 1) }}%</td>
                                    <td class="text-right" style="border: 1px solid var(--border-color);">{{ number_format($summary['sgst_amount'] ?? ($summary['tax_value']/2), 2) }}</td>
                                    <td class="text-right" style="border: 1px solid var(--border-color);">{{ number_format($summary['tax_value'], 2) }}</td>
                                    <td class="text-right" style="border: 1px solid var(--border-color);">{{ number_format($summary['amount'] ?? ($summary['taxable_value'] + $summary['tax_value']), 2) }}</td>
                                </tr>
                            @endforeach
                            <tr style="font-weight: 700; background-color: var(--slate-100);">
                                <td class="text-center" style="border: 1px solid var(--border-color);">TOTAL</td>
                                <td class="text-center" style="border: 1px solid var(--border-color);">{{ collect($taxSummary)->sum('qty') }}</td>
                                <td class="text-right" style="border: 1px solid var(--border-color);">{{ number_format(collect($taxSummary)->sum('taxable_value'), 2) }}</td>
                                <td class="text-center" style="border: 1px solid var(--border-color);"></td>
                                <td class="text-right" style="border: 1px solid var(--border-color);">{{ number_format(collect($taxSummary)->sum('cgst_amount'), 2) }}</td>
                                <td class="text-center" style="border: 1px solid var(--border-color);"></td>
                                <td class="text-right" style="border: 1px solid var(--border-color);">{{ number_format(collect($taxSummary)->sum('sgst_amount'), 2) }}</td>
                                <td class="text-right" style="border: 1px solid var(--border-color);">{{ number_format(collect($taxSummary)->sum('tax_value'), 2) }}</td>
                                <td class="text-right" style="border: 1px solid var(--border-color);">{{ number_format(collect($taxSummary)->sum('amount'), 2) }}</td>
                            </tr>
                        </tbody>
                    @endif
                </table>
            </div>

            <!-- Grand Totals Box -->
            <div class="totals-table-container">
                <table class="totals-table">
                    <tr>
                        <td>Total Taxable Value</td>
                        <td class="text-right">₹{{ number_format($totalTaxableAmount, 2) }}</td>
                    </tr>
                    @if($isIntraState)
                        <tr>
                            <td>CGST Total</td>
                            <td class="text-right">₹{{ number_format($totalTaxAmount / 2, 2) }}</td>
                        </tr>
                        <tr>
                            <td>SGST Total</td>
                            <td class="text-right">₹{{ number_format($totalTaxAmount / 2, 2) }}</td>
                        </tr>
                    @else
                        <tr>
                            <td>IGST Total</td>
                            <td class="text-right">₹{{ number_format($totalTaxAmount, 2) }}</td>
                        </tr>
                    @endif
                    <tr>
                        <td>Total GST Amount</td>
                        <td class="text-right">₹{{ number_format($totalTaxAmount, 2) }}</td>
                    </tr>
                    <tr>
                        <td>Round Off Difference</td>
                        <td class="text-right">₹{{ number_format($roundOff, 2) }}</td>
                    </tr>
                    <tr class="grand-total">
                        <td>Grand Total</td>
                        <td class="text-right">₹{{ number_format($roundedGrandTotal, 2) }}</td>
                    </tr>
                </table>
            </div>
        </div>

        <!-- Terms and Conditions -->
        <div class="bottom-section">
            <div class="terms-box">
                <h4>Notes & Declaration:</h4>
                <p style="margin-bottom: 8px; color: var(--slate-600);">Thank you for your business!</p>
                <h4>Terms & Conditions:</h4>
                <ol>
                    <li>Goods once sold cannot be taken back or exchanged.</li>
                    <li>All disputes are subject to local Jurisdiction.</li>
                    <li>This is a computer-generated invoice and requires no physical signature.</li>
                </ol>
            </div>
            
            <div class="signature-box">
                <p>For <strong>ASC Distributor Pvt. Ltd.</strong></p>
                <div class="signature-line">
                    Authorized Signatory
                </div>
            </div>
        </div>

    </div>
</body>
</html>
