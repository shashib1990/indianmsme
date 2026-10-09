<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADC Purchase Report Print</title>
    <style>
        body { font-family: 'DejaVu Sans', Arial, sans-serif; font-size: 11px; color: #333; margin: 20px; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 2px solid #333; padding-bottom: 10px; }
        .header h2 { margin: 0 0 5px 0; text-transform: uppercase; color: #1e293b; }
        .header p { margin: 0; color: #64748b; font-size: 10px; }
        .meta-table { width: 100%; margin-bottom: 15px; border-collapse: collapse; }
        .meta-table td { padding: 4px 8px; font-size: 10px; }
        table.data-table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        table.data-table th, table.data-table td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        table.data-table th { background-color: #f1f5f9; font-weight: bold; text-transform: uppercase; font-size: 9px; color: #475569; }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .summary-box { margin-top: 15px; float: right; width: 300px; border: 1px solid #cbd5e1; padding: 8px; background: #f8fafc; }
        .summary-row { display: flex; justify-content: space-between; padding: 3px 0; font-size: 10px; }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        @php
            $siteLogo = \App\Models\SystemSetting::getVal('website_header_logo', \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png')));
            if ($siteLogo && !str_starts_with($siteLogo, 'http') && !str_starts_with($siteLogo, '/')) {
                $siteLogo = asset('storage/' . $siteLogo);
            }
        @endphp
        <img src="{{ $siteLogo }}" style="max-height: 50px; max-width: 200px; object-fit: contain; margin-bottom: 6px;" alt="Logo" onerror="this.style.display='none'">
        <h2>ADC PURCHASE REPORT ({{ strtoupper($filters['sourcing'] ?? 'ALL') }} SCOPE)</h2>
        <p>Generated on {{ date('d M Y, h:i A') }}</p>
    </div>

    <table class="meta-table">
        <tr>
            <td><strong>Period:</strong> {{ $filters['start_date'] ?? 'N/A' }} to {{ $filters['end_date'] ?? 'N/A' }}</td>
            <td><strong>Sourcing Scope:</strong> {{ strtoupper($filters['sourcing'] ?? 'ALL') }}</td>
            <td><strong>Total Purchase Records:</strong> {{ count($records) }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th class="text-center">#</th>
                <th>PO / Ref No</th>
                <th>Invoice No</th>
                <th>Date</th>
                <th>Source Type</th>
                <th>Supplier / Vendor</th>
                <th class="text-center">Items Qty</th>
                <th class="text-end">Subtotal</th>
                <th class="text-end">Tax Amount</th>
                <th class="text-end">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $idx => $row)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="fw-bold">{{ $row['reference_no'] }}</td>
                    <td class="fw-bold text-primary">{{ $row['invoice_no'] }}</td>
                    <td>{{ $row['date'] }}</td>
                    <td>{{ $row['source_type'] }}</td>
                    <td>{{ $row['supplier_name'] }}</td>
                    <td class="text-center">{{ number_format($row['total_items'], 2) }}</td>
                    <td class="text-end">₹{{ number_format($row['subtotal'], 2) }}</td>
                    <td class="text-end">₹{{ number_format($row['tax_amount'], 2) }}</td>
                    <td class="text-end fw-bold">₹{{ number_format($row['total_amount'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-box">
        <div class="summary-row"><span>Total Purchase Orders:</span> <strong>{{ count($records) }}</strong></div>
        <div class="summary-row"><span>Total Purchased Qty:</span> <strong>{{ number_format($summaryStats['total_items'], 2) }}</strong></div>
        <div class="summary-row"><span>Total Subtotal:</span> <strong>₹{{ number_format($summaryStats['total_subtotal'], 2) }}</strong></div>
        <div class="summary-row"><span>Total Tax Amount:</span> <strong>₹{{ number_format($summaryStats['total_tax'], 2) }}</strong></div>
        <div class="summary-row" style="border-top: 1px solid #cbd5e1; font-size: 11px; margin-top: 4px; padding-top: 4px;">
            <span>Grand Total Purchase Amount:</span> <strong>₹{{ number_format($summaryStats['total_amount'], 2) }}</strong>
        </div>
    </div>
</body>
</html>
