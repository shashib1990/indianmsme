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
    <link
        href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Outfit:wght@400;500;600;700&display=swap"
        rel="stylesheet">

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
            position: relative;
        }

        /* Toolbar Actions */
        .toolbar {
            max-width: 900px;
            margin: 0 auto 20px auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            text-decoration: none;
            border: none;
        }

        .btn-primary {
            background-color: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
            transform: translateY(-1px);
        }

        .btn-secondary {
            background-color: white;
            color: var(--slate-600);
            border: 1px solid var(--border-color);
        }

        .btn-secondary:hover {
            background-color: var(--slate-100);
        }

        .btn-success {
            background-color: #10b981;
            color: white;
        }

        .btn-success:hover {
            background-color: #0d9488;
            transform: translateY(-1px);
        }

        /* Invoice Layout styling */
        .header-section {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            border-bottom: 2px solid var(--primary);
            padding-bottom: 20px;
            margin-bottom: 20px;
        }

        .logo-container {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .logo-icon {
            width: 48px;
            height: 48px;
            background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 700;
            font-size: 20px;
            box-shadow: 0 4px 10px rgba(30, 58, 138, 0.2);
        }

        .logo-text h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: var(--primary);
            letter-spacing: -0.5px;
        }

        .logo-text p {
            font-size: 11px;
            color: var(--slate-600);
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 1px;
        }

        .company-header-info {
            text-align: right;
            font-size: 13px;
            color: var(--slate-600);
        }

        .company-header-info h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 4px;
        }

        .title-badge-container {
            text-align: center;
            margin-bottom: 25px;
        }

        .title-badge {
            display: inline-block;
            background-color: var(--primary);
            color: white;
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            font-weight: 700;
            padding: 6px 20px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
        }

        /* Info Grid */
        .info-grid {
            display: grid;
            grid-template-columns: 1.2fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
            background-color: var(--slate-100);
            border-radius: 8px;
            padding: 15px;
            border: 1px solid var(--border-color);
        }

        .info-box {
            font-size: 12px;
        }

        .info-box h3 {
            font-size: 13px;
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 8px;
            border-bottom: 1px dashed var(--border-color);
            padding-bottom: 4px;
        }

        .info-box p {
            margin-bottom: 4px;
            color: var(--slate-600);
        }

        .info-box strong {
            color: var(--slate-800);
        }

        /* Billing / Shipping Details Row */
        .billing-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 25px;
        }

        .billing-card {
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 15px;
        }

        .billing-card h3 {
            font-family: 'Outfit', sans-serif;
            font-size: 14px;
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 10px;
            padding-bottom: 6px;
            border-bottom: 1px solid var(--border-color);
        }

        .billing-card p {
            font-size: 12px;
            color: var(--slate-600);
            margin-bottom: 4px;
        }

        .billing-card strong {
            color: var(--slate-800);
        }

        /* Table Style */
        .invoice-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
            font-size: 12px;
        }

        .invoice-table th {
            background-color: var(--primary-light);
            color: var(--primary);
            font-weight: 700;
            text-align: left;
            padding: 10px 8px;
            border: 1px solid var(--border-color);
        }

        .invoice-table td {
            padding: 10px 8px;
            border: 1px solid var(--border-color);
            vertical-align: top;
        }

        .invoice-table tr:nth-child(even) {
            background-color: #fafbfc;
        }

        .text-right {
            text-align: right !important;
        }

        .text-center {
            text-align: center !important;
        }

        /* Summary Sections */
        .summary-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            gap: 20px;
            margin-bottom: 25px;
        }

        .words-and-tax {
            flex: 1.2;
        }

        .totals-table-container {
            flex: 0.8;
            background-color: var(--slate-100);
            border: 1px solid var(--border-color);
            border-radius: 8px;
            padding: 15px;
        }

        .totals-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
        }

        .totals-table td {
            padding: 6px 0;
        }

        .totals-table tr:not(:last-child) td {
            border-bottom: 1px solid var(--border-color);
        }

        .totals-table tr.grand-total td {
            font-weight: 700;
            color: var(--primary);
            font-size: 14px;
            padding-top: 10px;
        }

        .amount-in-words-box {
            background-color: var(--primary-light);
            border: 1px solid rgba(30, 58, 138, 0.15);
            border-radius: 6px;
            padding: 10px;
            font-size: 12px;
            color: var(--primary);
            margin-bottom: 20px;
        }

        .amount-in-words-box strong {
            font-weight: 700;
        }

        /* Tax Summary Table */
        .tax-summary-title {
            font-family: 'Outfit', sans-serif;
            font-size: 13px;
            color: var(--primary);
            font-weight: 700;
            margin-bottom: 8px;
        }

        .tax-summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 20px;
        }

        .tax-summary-table th {
            background-color: var(--slate-100);
            font-weight: 700;
            padding: 6px;
            border: 1px solid var(--border-color);
            text-align: left;
        }

        .tax-summary-table td {
            padding: 6px;
            border: 1px solid var(--border-color);
        }

        /* Terms & Signature */
        .bottom-section {
            display: grid;
            grid-template-columns: 1.2fr 1fr;
            gap: 20px;
            border-top: 1px solid var(--border-color);
            padding-top: 20px;
            font-size: 11px;
        }

        .terms-box h4 {
            font-weight: 700;
            color: var(--slate-800);
            margin-bottom: 6px;
        }

        .terms-box ol {
            padding-left: 15px;
            color: var(--slate-600);
        }

        .terms-box ol li {
            margin-bottom: 3px;
        }

        .signature-box {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            align-items: flex-end;
            text-align: right;
            height: 120px;
        }

        .signature-line {
            width: 200px;
            border-top: 1px solid var(--slate-800);
            margin-top: 50px;
            padding-top: 4px;
            font-weight: 600;
            color: var(--slate-800);
        }

        /* Transporter Editing Styling (Browser only) */
        .transporter-edit-form {
            display: flex;
            flex-direction: column;
            gap: 8px;
            background: #fff;
            padding: 10px;
            border-radius: 6px;
            border: 1px solid var(--border-color);
            margin-top: 8px;
        }

        .form-group {
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .form-group label {
            font-size: 10px;
            font-weight: 600;
            color: var(--slate-600);
        }

        .form-group input {
            padding: 4px 8px;
            font-size: 11px;
            border: 1px solid var(--border-color);
            border-radius: 4px;
        }

        .form-group input:focus {
            outline: none;
            border-color: var(--primary);
        }

        .edit-actions {
            display: flex;
            justify-content: flex-end;
            gap: 6px;
            margin-top: 6px;
        }

        .btn-xs {
            padding: 3px 8px;
            font-size: 10px;
            border-radius: 4px;
        }

        .edit-trigger-btn {
            color: var(--primary);
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            font-weight: 600;
            margin-top: 8px;
        }

        .edit-trigger-btn:hover {
            text-decoration: underline;
        }

        .toast-notification {
            position: fixed;
            bottom: 20px;
            right: 20px;
            background-color: #10b981;
            color: white;
            padding: 12px 24px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
            z-index: 10000;
            font-weight: 600;
            font-size: 14px;
            display: none;
            align-items: center;
            gap: 8px;
            animation: slideUp 0.3s ease;
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }

            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Print Settings */
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

            .toolbar,
            .edit-trigger-btn,
            .transporter-edit-form {
                display: none !important;
            }

            .info-grid {
                background-color: #f8fafc !important;
                border: 1px solid #000 !important;
            }

            .billing-card {
                border: 1px solid #000 !important;
            }

            .invoice-table th {
                background-color: #eff6ff !important;
                border: 1px solid #000 !important;
                color: black !important;
            }

            .invoice-table td {
                border: 1px solid #000 !important;
            }

            .tax-summary-table th {
                background-color: #f1f5f9 !important;
                border: 1px solid #000 !important;
            }

            .tax-summary-table td {
                border: 1px solid #000 !important;
            }

            .totals-table-container {
                background-color: #f8fafc !important;
                border: 1px solid #000 !important;
            }
        }
    </style>
</head>

<body>

    <!-- Toolbar for actions in browser view -->
    <div class="toolbar">
        <button
            onclick="if(window.history.length > 1 && document.referrer !== ''){ window.history.back(); } else { window.close(); }"
            class="btn btn-secondary">
            <iconify-icon icon="solar:arrow-left-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
            Back
        </button>
        <div style="display: flex; gap: 10px;">
            @if($invoice->invoice_file && !auth('ddc')->check())
                <a href="{{ $invoice->invoice_file }}" target="_blank" class="btn btn-success">
                    <iconify-icon icon="solar:eye-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                    View Uploaded Invoice Copy
                </a>
            @endif
            <button onclick="window.print()" class="btn btn-primary">
                <iconify-icon icon="solar:printer-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                Print Tax Invoice
            </button>
        </div>
    </div>

    <div class="invoice-wrapper">

        <!-- Header -->
        <div class="header-section"
            style="display: flex; justify-content: center; align-items: center; text-align: center; border-bottom: 2px solid var(--primary); padding-bottom: 20px; margin-bottom: 20px;">
            <div class="logo-container"
                style="display: flex; justify-content: center; align-items: center; width: 100%;">
                <img src="{{ \App\Models\SystemSetting::getVal('website_header_logo', \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png'))) }}"
                    alt="Logo" class="logo-img"
                    style="width: auto; height: 100px; object-fit: contain; margin: 0 auto;">
            </div>
        </div>

        <!-- TAX INVOICE Badge -->
        <div class="title-badge-container">
            <span class="title-badge">Tax Invoice</span>
        </div>

        <!-- Info Grid -->
        <div class="info-grid">
            <div class="info-box">
                <h3>Invoice From</h3>
                <p><strong>ASC Distributor Pvt. Ltd.</strong></p>
                <p><strong>Head Office:</strong> No. 82, Block C, Pocket - 7, Sector 8, Rohini, New Delhi, Delhi, India
                    110085</p>
                <p><strong>Gst No.:</strong> 07AAZCA4502F1ZM</p>
                <p><strong>Call Us:</strong> 8800001240/50/60</p>
                <p><strong>Mail Us:</strong> home@ardhsainik.com</p>
            </div>

            <div class="info-box">
                <h3>Invoice Details</h3>
                <p><strong>Invoice No:</strong> {{ $invoice->invoice_number }}</p>
                <p><strong>Invoice Date:</strong> {{ $invoice->invoice_date->format('d M, Y') }}</p>
                <p><strong>Place of Supply:</strong>
                    {{ strtoupper($invoice->bill_to_state ?? ($ddcState->name ?? 'Delhi')) }}</p>
            </div>

            <div class="info-box">
                <h3>Transporter Details</h3>
                <div id="transporter-display-mode">
                    <p><strong>Transporter:</strong> <span
                            id="lbl-transporter_name">{{ $invoice->transporter_name ?? 'N/A' }}</span></p>
                    <p><strong>Vehicle No:</strong> <span
                            id="lbl-vehicle_number">{{ $invoice->vehicle_number ?? 'N/A' }}</span></p>
                    <p><strong>Transporter GST:</strong> <span
                            id="lbl-transporter_gst">{{ $invoice->transporter_gst ?? 'N/A' }}</span></p>
                    <p><strong>E-Way Bill No:</strong> <span
                            id="lbl-eway_bill_number">{{ $invoice->eway_bill_number ?? 'N/A' }}</span></p>

                    @if(!auth('ddc')->check())
                        <a href="javascript:void(0)" onclick="toggleTransporterEdit(true)" class="edit-trigger-btn">
                            <iconify-icon icon="solar:pen-bold-duotone" class="align-middle"></iconify-icon>
                            Edit Details
                        </a>
                    @endif
                </div>

                @if(!auth('ddc')->check())
                    <form id="transporter-edit-form" class="transporter-edit-form" style="display: none;">
                        <div class="form-group">
                            <label>Transporter Name</label>
                            <input type="text" id="input-transporter_name" value="{{ $invoice->transporter_name }}">
                        </div>
                        <div class="form-group">
                            <label>Vehicle No.</label>
                            <input type="text" id="input-vehicle_number" value="{{ $invoice->vehicle_number }}">
                        </div>
                        <div class="form-group">
                            <label>Transporter GST</label>
                            <input type="text" id="input-transporter_gst" value="{{ $invoice->transporter_gst }}">
                        </div>
                        <div class="form-group">
                            <label>E-Way Bill No.</label>
                            <input type="text" id="input-eway_bill_number" value="{{ $invoice->eway_bill_number }}">
                        </div>
                        <div class="edit-actions">
                            <button type="button" onclick="toggleTransporterEdit(false)"
                                class="btn btn-secondary btn-xs">Cancel</button>
                            <button type="button" onclick="saveTransporterDetails()"
                                class="btn btn-primary btn-xs">Save</button>
                        </div>
                    </form>
                @endif
            </div>
        </div>

        @php
            $isVirtualPo = ($purchaseOrder->fulfillment_type === 'virtual') || !empty($purchaseOrder->adc_order_id) || ($invoice->type === 'adc_virtual_po');
            $adcOrder = $purchaseOrder->adcOrder;
            if (!$adcOrder && !empty($purchaseOrder->adc_order_id)) {
                $adcOrder = \App\Models\AdcOrder::with(['adc.adcProfile', 'adc.profile'])->find($purchaseOrder->adc_order_id);
            }
            $adcUser = $adcOrder?->adc;
            $adcProfile = $adcUser?->adcProfile ?? $adcUser?->profile ?? null;
            $ddcProfile = $purchaseOrder->ddc?->profile ?? $purchaseOrder->ddc?->ddcProfile ?? null;

            if ($isVirtualPo) {
                $canteenName = $adcProfile?->company_name ?? ($adcProfile?->trade_name ?? ($adcUser?->name ?? 'ADC Canteen'));
                if (empty($canteenName) || str_contains(strtolower($canteenName), 'asc distributor')) {
                    $canteenName = $invoice->bill_to_name && !str_contains(strtolower($invoice->bill_to_name), 'asc distributor') ? $invoice->bill_to_name : 'ADC Canteen';
                }
                $canteenAddress = $adcOrder?->shipping_address ?: ($adcProfile?->address ?: ($invoice->bill_to_address ?: 'Address not available'));
                $canteenDistrict = $adcOrder?->delivery_district ?: ($adcProfile?->district ?: ($invoice->bill_to_district ?: ''));
                $canteenState = $adcOrder?->delivery_state ?: ($adcProfile?->state ?: ($invoice->bill_to_state ?: ''));
                $canteenPincode = $adcOrder?->delivery_pincode ?: ($adcProfile?->pincode ?: ($invoice->bill_to_pincode ?: ''));
                $canteenPhone = $adcProfile?->official_phone ?? ($adcProfile?->phone ?? ($adcUser?->mobile ?? ($invoice->bill_to_phone ?: 'N/A')));
                $canteenGstin = $adcProfile?->gst_number ?? ($invoice->bill_to_gstin ?: 'N/A');
            }
        @endphp

        <!-- Billing & Shipping Details Row -->
        <div class="billing-row">
            <div class="billing-card">
                <h3>Bill / Ship To</h3>
                @if($isVirtualPo)
                    <p><strong>{{ $canteenName }}</strong></p>
                    <p>{{ $canteenAddress }}</p>
                    <p>
                        {{ $canteenDistrict }}{{ ($canteenDistrict && $canteenState) ? ', ' : '' }}{{ $canteenState }}{{ $canteenPincode ? ' - ' . $canteenPincode : '' }}
                    </p>
                    <p><strong>Phone:</strong> {{ $canteenPhone }}</p>
                    <p><strong>GSTIN:</strong> {{ $canteenGstin }}</p>
                @else
                    <p><strong>{{ $invoice->bill_to_name ?? ($ddcProfile?->company_name ?? ($ddcProfile?->trade_name ?? ($purchaseOrder->ddc?->name ?? 'DDC Hub'))) }}</strong>
                    </p>
                    <p>{{ $invoice->bill_to_address ?? ($ddcProfile?->address ?? 'Address not available') }}</p>
                    <p>
                        @if($invoice->bill_to_district || $invoice->bill_to_state || $invoice->bill_to_pincode)
                            {{ $invoice->bill_to_district ?? '' }}{{ $invoice->bill_to_district ? ', ' : '' }}{{ $invoice->bill_to_state ?? '' }}
                            - {{ $invoice->bill_to_pincode ?? '' }}
                        @else
                            {{ $ddcProfile?->district ?? '' }}{{ $ddcProfile?->district ? ', ' : '' }}{{ $ddcProfile?->state ?? '' }}
                            - {{ $ddcProfile?->pincode ?? '' }}
                        @endif
                    </p>
                    <p><strong>Phone:</strong>
                        {{ $invoice->bill_to_phone ?? ($ddcProfile?->official_phone ?? ($ddcProfile?->phone ?? ($purchaseOrder->ddc?->mobile ?? 'N/A'))) }}
                    </p>
                    <p><strong>GSTIN:</strong>
                        {{ $invoice->bill_to_gstin ?? ($ddcProfile?->ddc_gst_number ?? ($ddcProfile?->adpl_gst_number ?? ($ddcProfile?->gst_number ?? 'N/A'))) }}
                    </p>
                @endif
            </div>

            <div class="billing-card">
                <h3>Dispatch From</h3>
                <p><strong>{{ (!empty($invoice->dispatch_from_name) && strtolower(trim($invoice->dispatch_from_name)) !== 'pickup store') ? $invoice->dispatch_from_name : ($purchaseOrder->pickupStore?->pickup_store_name ?? ($purchaseOrder->seller?->sellerProfile?->company_name ?? ($purchaseOrder->seller?->name ?? 'N/A'))) }}</strong>
                </p>
                <p>{{ $invoice->dispatch_from_address ?? ($purchaseOrder->pickupStore?->address ?? ($purchaseOrder->seller?->sellerProfile?->address ?? 'Address not available')) }}
                </p>
                <p>
                    @if($invoice->dispatch_from_district || $invoice->dispatch_from_state || $invoice->dispatch_from_pincode)
                        {{ $invoice->dispatch_from_district ?? '' }}{{ $invoice->dispatch_from_district ? ', ' : '' }}{{ $invoice->dispatch_from_state ?? '' }}
                        - {{ $invoice->dispatch_from_pincode ?? '' }}
                    @elseif($purchaseOrder->pickupStore)
                        {{ $purchaseOrder->pickupStore->districtRelation?->name ?? ($purchaseOrder->pickupStore->district ?? '') }}{{ $purchaseOrder->pickupStore->district ? ', ' : '' }}{{ $purchaseOrder->pickupStore->stateRelation?->name ?? ($purchaseOrder->pickupStore->state ?? '') }}
                        - {{ $purchaseOrder->pickupStore->pincode ?? '' }}
                    @else
                        {{ $purchaseOrder->seller?->sellerProfile?->district ?? '' }}{{ $purchaseOrder->seller?->sellerProfile?->district ? ', ' : '' }}{{ $purchaseOrder->seller?->sellerProfile?->state ?? '' }}
                        - {{ $purchaseOrder->seller?->sellerProfile?->pincode ?? '' }}
                    @endif
                </p>
                <p><strong>Phone:</strong>
                    {{ $invoice->dispatch_from_phone ?? ($purchaseOrder->pickupStore?->mobile ?? ($purchaseOrder->seller?->sellerProfile?->alternate_mobile ?? ($purchaseOrder->seller?->mobile ?? 'N/A'))) }}
                </p>
                <p><strong>GSTIN:</strong>
                    {{ $invoice->dispatch_from_gstin ?? ($purchaseOrder->pickupStore?->gst_number ?? ($purchaseOrder->seller?->sellerProfile?->gst_number ?? 'N/A')) }}
                </p>
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
                            <span style="color: var(--slate-600); font-size: 10px;">Product Code:
                                {{ $item['barcode'] }}</span>
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
                                        (CGST: {{ number_format($item['gst_rate'] / 2, 2) }}%, SGST:
                                        {{ number_format($item['gst_rate'] / 2, 2) }}%)
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

                <!-- Tax Summary Breakdown (Rate-Wise) -->
                <div class="tax-summary-title">Tax Summary Breakdown</div>
                <table class="tax-summary-table" style="width: 100%; border-collapse: collapse; font-size: 11px; margin-bottom: 15px;">
                    @if(!$isIntraState)
                        <thead>
                            <tr>
                                <th rowspan="2" class="text-center"
                                    style="vertical-align: middle; border: 1px solid var(--border-color); font-weight: 700;">
                                    GST Rate (%)</th>
                                <th rowspan="2" class="text-center"
                                    style="vertical-align: middle; border: 1px solid var(--border-color); font-weight: 700;">
                                    Qty</th>
                                <th rowspan="2" class="text-right"
                                    style="vertical-align: middle; border: 1px solid var(--border-color); font-weight: 700;">
                                    Taxable Amount (₹)</th>
                                <th colspan="2" class="text-center"
                                    style="border: 1px solid var(--border-color); font-weight: 700;">IGST</th>
                                <th rowspan="2" class="text-right"
                                    style="vertical-align: middle; border: 1px solid var(--border-color); font-weight: 700;">
                                    Total Tax (₹)</th>
                                <th rowspan="2" class="text-right"
                                    style="vertical-align: middle; border: 1px solid var(--border-color); font-weight: 700;">
                                    Total Amount (₹)</th>
                            </tr>
                            <tr>
                                <th class="text-center" style="border: 1px solid var(--border-color); font-weight: 700;">
                                    Rate (%)</th>
                                <th class="text-right" style="border: 1px solid var(--border-color); font-weight: 700;">Amt
                                    (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rateTaxSummary as $summary)
                                <tr>
                                    <td class="text-center" style="border: 1px solid var(--border-color);">
                                        {{ number_format($summary['rate'], 1) }}%</td>
                                    <td class="text-center" style="border: 1px solid var(--border-color);">
                                        {{ $summary['qty'] ?? 0 }}</td>
                                    <td class="text-right" style="border: 1px solid var(--border-color);">
                                        {{ number_format($summary['taxable_value'], 2) }}</td>
                                    <td class="text-center" style="border: 1px solid var(--border-color);">
                                        {{ number_format($summary['igst_rate'] ?? $summary['rate'], 1) }}%</td>
                                    <td class="text-right" style="border: 1px solid var(--border-color);">
                                        {{ number_format($summary['igst_amount'] ?? $summary['tax_value'], 2) }}</td>
                                    <td class="text-right" style="border: 1px solid var(--border-color);">
                                        {{ number_format($summary['tax_value'], 2) }}</td>
                                    <td class="text-right" style="border: 1px solid var(--border-color);">
                                        {{ number_format($summary['amount'] ?? ($summary['taxable_value'] + $summary['tax_value']), 2) }}</td>
                                </tr>
                            @endforeach
                            <tr style="font-weight: 700; background-color: var(--slate-100);">
                                <td class="text-center" style="border: 1px solid var(--border-color);">TOTAL</td>
                                <td class="text-center" style="border: 1px solid var(--border-color);">
                                    {{ collect($rateTaxSummary)->sum('qty') }}</td>
                                <td class="text-right" style="border: 1px solid var(--border-color);">
                                    {{ number_format(collect($rateTaxSummary)->sum('taxable_value'), 2) }}</td>
                                <td class="text-center" style="border: 1px solid var(--border-color);"></td>
                                <td class="text-right" style="border: 1px solid var(--border-color);">
                                    {{ number_format(collect($rateTaxSummary)->sum('igst_amount'), 2) }}</td>
                                <td class="text-right" style="border: 1px solid var(--border-color);">
                                    {{ number_format(collect($rateTaxSummary)->sum('tax_value'), 2) }}</td>
                                <td class="text-right" style="border: 1px solid var(--border-color);">
                                    {{ number_format(collect($rateTaxSummary)->sum('amount'), 2) }}</td>
                            </tr>
                        </tbody>
                    @else
                        <thead>
                            <tr>
                                <th rowspan="2" class="text-center"
                                    style="vertical-align: middle; border: 1px solid var(--border-color); font-weight: 700;">
                                    GST Rate (%)</th>
                                <th rowspan="2" class="text-center"
                                    style="vertical-align: middle; border: 1px solid var(--border-color); font-weight: 700;">
                                    Qty</th>
                                <th rowspan="2" class="text-right"
                                    style="vertical-align: middle; border: 1px solid var(--border-color); font-weight: 700;">
                                    Taxable Amount (₹)</th>
                                <th colspan="2" class="text-center"
                                    style="border: 1px solid var(--border-color); font-weight: 700;">CGST</th>
                                <th colspan="2" class="text-center"
                                    style="border: 1px solid var(--border-color); font-weight: 700;">SGST</th>
                                <th rowspan="2" class="text-right"
                                    style="vertical-align: middle; border: 1px solid var(--border-color); font-weight: 700;">
                                    Total Tax (₹)</th>
                                <th rowspan="2" class="text-right"
                                    style="vertical-align: middle; border: 1px solid var(--border-color); font-weight: 700;">
                                    Total Amount (₹)</th>
                            </tr>
                            <tr>
                                <th class="text-center" style="border: 1px solid var(--border-color); font-weight: 700;">
                                    Rate (%)</th>
                                <th class="text-right" style="border: 1px solid var(--border-color); font-weight: 700;">Amt
                                    (₹)</th>
                                <th class="text-center" style="border: 1px solid var(--border-color); font-weight: 700;">
                                    Rate (%)</th>
                                <th class="text-right" style="border: 1px solid var(--border-color); font-weight: 700;">Amt
                                    (₹)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($rateTaxSummary as $summary)
                                <tr>
                                    <td class="text-center" style="border: 1px solid var(--border-color);">
                                        {{ number_format($summary['rate'], 1) }}%</td>
                                    <td class="text-center" style="border: 1px solid var(--border-color);">
                                        {{ $summary['qty'] ?? 0 }}</td>
                                    <td class="text-right" style="border: 1px solid var(--border-color);">
                                        {{ number_format($summary['taxable_value'], 2) }}</td>
                                    <td class="text-center" style="border: 1px solid var(--border-color);">
                                        {{ number_format($summary['cgst_rate'] ?? ($summary['rate'] / 2), 1) }}%</td>
                                    <td class="text-right" style="border: 1px solid var(--border-color);">
                                        {{ number_format($summary['cgst_amount'] ?? ($summary['tax_value'] / 2), 2) }}</td>
                                    <td class="text-center" style="border: 1px solid var(--border-color);">
                                        {{ number_format($summary['sgst_rate'] ?? ($summary['rate'] / 2), 1) }}%</td>
                                    <td class="text-right" style="border: 1px solid var(--border-color);">
                                        {{ number_format($summary['sgst_amount'] ?? ($summary['tax_value'] / 2), 2) }}</td>
                                    <td class="text-right" style="border: 1px solid var(--border-color);">
                                        {{ number_format($summary['tax_value'], 2) }}</td>
                                    <td class="text-right" style="border: 1px solid var(--border-color);">
                                        {{ number_format($summary['amount'] ?? ($summary['taxable_value'] + $summary['tax_value']), 2) }}</td>
                                </tr>
                            @endforeach
                            <tr style="font-weight: 700; background-color: var(--slate-100);">
                                <td class="text-center" style="border: 1px solid var(--border-color);">TOTAL</td>
                                <td class="text-center" style="border: 1px solid var(--border-color);">
                                    {{ collect($rateTaxSummary)->sum('qty') }}</td>
                                <td class="text-right" style="border: 1px solid var(--border-color);">
                                    {{ number_format(collect($rateTaxSummary)->sum('taxable_value'), 2) }}</td>
                                <td class="text-center" style="border: 1px solid var(--border-color);"></td>
                                <td class="text-right" style="border: 1px solid var(--border-color);">
                                    {{ number_format(collect($rateTaxSummary)->sum('cgst_amount'), 2) }}</td>
                                <td class="text-center" style="border: 1px solid var(--border-color);"></td>
                                <td class="text-right" style="border: 1px solid var(--border-color);">
                                    {{ number_format(collect($rateTaxSummary)->sum('sgst_amount'), 2) }}</td>
                                <td class="text-right" style="border: 1px solid var(--border-color);">
                                    {{ number_format(collect($rateTaxSummary)->sum('tax_value'), 2) }}</td>
                                <td class="text-right" style="border: 1px solid var(--border-color);">
                                    {{ number_format(collect($rateTaxSummary)->sum('amount'), 2) }}</td>
                            </tr>
                        </tbody>
                    @endif
                </table>

                <!-- HSN Wise Summary -->
                <div class="tax-summary-title">HSN Wise Summary</div>
                <table class="tax-summary-table" style="width: 100%; border-collapse: collapse; font-size: 11px;">
                    <thead>
                        <tr>
                            <th class="text-center" style="border: 1px solid var(--border-color); font-weight: 700;">HSN Code</th>
                            <th class="text-center" style="border: 1px solid var(--border-color); font-weight: 700;">Qty</th>
                            <th class="text-right" style="border: 1px solid var(--border-color); font-weight: 700;">Taxable Amount (₹)</th>
                            <th class="text-center" style="border: 1px solid var(--border-color); font-weight: 700;">GST Rate (%)</th>
                            <th class="text-right" style="border: 1px solid var(--border-color); font-weight: 700;">Total GST Amount (₹)</th>
                            <th class="text-right" style="border: 1px solid var(--border-color); font-weight: 700;">Total Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($hsnTaxSummary as $hsnItem)
                            <tr>
                                <td class="text-center" style="border: 1px solid var(--border-color);">{{ $hsnItem['hsn_code'] ?? 'N/A' }}</td>
                                <td class="text-center" style="border: 1px solid var(--border-color);">{{ $hsnItem['qty'] ?? 0 }}</td>
                                <td class="text-right" style="border: 1px solid var(--border-color);">{{ number_format($hsnItem['taxable_value'], 2) }}</td>
                                <td class="text-center" style="border: 1px solid var(--border-color);">{{ number_format($hsnItem['rate'], 1) }}%</td>
                                <td class="text-right" style="border: 1px solid var(--border-color);">{{ number_format($hsnItem['tax_value'], 2) }}</td>
                                <td class="text-right" style="border: 1px solid var(--border-color);">{{ number_format($hsnItem['amount'], 2) }}</td>
                            </tr>
                        @endforeach
                        <tr style="font-weight: 700; background-color: var(--slate-100);">
                            <td class="text-center" style="border: 1px solid var(--border-color);">TOTAL</td>
                            <td class="text-center" style="border: 1px solid var(--border-color);">{{ collect($hsnTaxSummary)->sum('qty') }}</td>
                            <td class="text-right" style="border: 1px solid var(--border-color);">{{ number_format(collect($hsnTaxSummary)->sum('taxable_value'), 2) }}</td>
                            <td class="text-center" style="border: 1px solid var(--border-color);"></td>
                            <td class="text-right" style="border: 1px solid var(--border-color);">{{ number_format(collect($hsnTaxSummary)->sum('tax_value'), 2) }}</td>
                            <td class="text-right" style="border: 1px solid var(--border-color);">{{ number_format(collect($hsnTaxSummary)->sum('amount'), 2) }}</td>
                        </tr>
                    </tbody>
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
                            <td class="text-right">₹{{ number_format(collect($taxSummary)->sum('cgst_amount'), 2) }}</td>
                        </tr>
                        <tr>
                            <td>SGST Total</td>
                            <td class="text-right">₹{{ number_format(collect($taxSummary)->sum('sgst_amount'), 2) }}</td>
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

    <!-- Success Toast Notification -->
    <div id="success-toast" class="toast-notification">
        <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-20"></iconify-icon>
        <span id="toast-message">Details saved successfully</span>
    </div>

    <!-- AJAX script to save transporter details -->
    <script>
        function toggleTransporterEdit(show) {
            const displayDiv = document.getElementById('transporter-display-mode');
            const editForm = document.getElementById('transporter-edit-form');
            if (show) {
                displayDiv.style.display = 'none';
                editForm.style.display = 'flex';
            } else {
                displayDiv.style.display = 'block';
                editForm.style.display = 'none';
            }
        }

        function saveTransporterDetails() {
            const name = document.getElementById('input-transporter_name').value;
            const vehicle = document.getElementById('input-vehicle_number').value;
            const gst = document.getElementById('input-transporter_gst').value;
            const eway = document.getElementById('input-eway_bill_number').value;

            const url = window.location.pathname + '/transporter';

            fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    transporter_name: name,
                    vehicle_number: vehicle,
                    transporter_gst: gst,
                    eway_bill_number: eway
                })
            })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Update labels
                        document.getElementById('lbl-transporter_name').textContent = data.data.transporter_name || 'N/A';
                        document.getElementById('lbl-vehicle_number').textContent = data.data.vehicle_number || 'N/A';
                        document.getElementById('lbl-transporter_gst').textContent = data.data.transporter_gst || 'N/A';
                        document.getElementById('lbl-eway_bill_number').textContent = data.data.eway_bill_number || 'N/A';

                        // Show success toast
                        showToast('Transporter details updated successfully!');

                        // Switch back to view mode
                        toggleTransporterEdit(false);
                    } else {
                        showToast(data.message || 'Error occurred while saving.', 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showToast('An error occurred. Please try again.', 'error');
                });
        }

        function showToast(message, type = 'success') {
            const toast = document.getElementById('success-toast');
            const messageEl = document.getElementById('toast-message');
            messageEl.textContent = message;

            if (type === 'error') {
                toast.style.backgroundColor = '#ef4444';
                toast.style.boxShadow = '0 4px 12px rgba(239, 68, 68, 0.3)';
            } else {
                toast.style.backgroundColor = '#10b981';
                toast.style.boxShadow = '0 4px 12px rgba(16, 185, 129, 0.3)';
            }

            toast.style.display = 'flex';
            setTimeout(() => {
                toast.style.display = 'none';
            }, 3000);
        }
    </script>
</body>

</html>