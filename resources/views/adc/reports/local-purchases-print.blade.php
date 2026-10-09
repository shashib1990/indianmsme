<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>ADC Local Purchases Report</title>
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1e293b; margin: 20px; }
        h2 { margin-bottom: 4px; color: #1e3a8a; }
        .table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        .table th, .table td { border: 1px solid #cbd5e1; padding: 8px 10px; text-align: left; }
        .table th { background-color: #f1f5f9; font-size: 11px; text-transform: uppercase; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        @media print {
            .no-print { display: none !important; }
        }
    </style>
</head>
<body onload="window.print()">
    <div class="no-print" style="margin-bottom: 20px;">
        <button onclick="window.print()" style="padding: 8px 16px; font-weight: bold; cursor: pointer;">Print Report</button>
    </div>

    <div class="header" style="text-align: center; border-bottom: 2px solid #1e293b; padding-bottom: 10px; margin-bottom: 15px;">
        @php
            $siteLogo = \App\Models\SystemSetting::getVal('website_header_logo', \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png')));
            if ($siteLogo && !str_starts_with($siteLogo, 'http') && !str_starts_with($siteLogo, '/')) {
                $siteLogo = asset('storage/' . $siteLogo);
            }
        @endphp
        <img src="{{ $siteLogo }}" style="max-height: 50px; max-width: 200px; object-fit: contain; margin-bottom: 6px;" alt="Logo" onerror="this.style.display='none'">
        <h2 style="margin: 0 0 4px 0; color: #1e3a8a; text-transform: uppercase;">ADC Local Purchases Report</h2>
        <p style="color: #64748b; margin: 0; font-size: 11px;">Generated on {{ date('d M Y, h:i A') }}</p>
    </div>

    <table class="table">
        <thead>
            <tr>
                <th>#</th>
                <th>Purchase No</th>
                <th>Date</th>
                <th>Supplier Name</th>
                <th>Invoice No</th>
                <th class="text-center">Items</th>
                <th class="text-end">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @foreach($purchases as $idx => $p)
                @php $grandTotal += $p->total_amount; @endphp
                <tr>
                    <td>{{ $idx + 1 }}</td>
                    <td><strong>{{ $p->purchase_no }}</strong></td>
                    <td>{{ $p->purchase_date ? $p->purchase_date->format('d/m/Y') : '—' }}</td>
                    <td>{{ $p->supplier_name }}</td>
                    <td>{{ $p->invoice_number ?: '—' }}</td>
                    <td class="text-center">{{ $p->items->count() }}</td>
                    <td class="text-end">₹{{ number_format($p->total_amount, 2) }}</td>
                </tr>
            @endforeach
            <tr style="font-weight: bold; background-color: #f8fafc;">
                <td colspan="6" class="text-end">Total Amount:</td>
                <td class="text-end">₹{{ number_format($grandTotal, 2) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
