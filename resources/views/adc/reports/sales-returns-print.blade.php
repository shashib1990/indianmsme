<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADC Sale Return Report Print</title>
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
        <h2>ADC SALE RETURN REPORT ({{ strtoupper($filters['sourcing'] ?? 'ALL') }} SCOPE)</h2>
        <p>Generated on {{ date('d M Y, h:i A') }}</p>
    </div>

    <table class="meta-table">
        <tr>
            <td><strong>Period:</strong> {{ $filters['start_date'] ?? 'N/A' }} to {{ $filters['end_date'] ?? 'N/A' }}</td>
            <td><strong>Sourcing Scope:</strong> {{ strtoupper($filters['sourcing'] ?? 'ALL') }}</td>
            <td><strong>Total Return Receipts:</strong> {{ count($records) }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 3%;" class="text-center">#</th>
                <th style="width: 14%;">Return Number</th>
                <th style="width: 12%;">Original Order No</th>
                <th style="width: 12%;">Original Invoice No</th>
                <th style="width: 10%;">Channel</th>
                <th style="width: 12%;">Date & Time</th>
                <th style="width: 9%;">Sourcing</th>
                <th style="width: 14%;">Customer Details</th>
                <th style="width: 14%;" class="text-end">Refund Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($records as $idx => $row)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="fw-bold text-danger">{{ $row['return_number'] }}</td>
                    <td class="fw-bold">{{ $row['original_order_no'] }}</td>
                    <td class="fw-bold text-primary">{{ $row['original_invoice_no'] }}</td>
                    <td>{{ $row['sales_channel'] }}</td>
                    <td>{{ $row['date'] }}</td>
                    <td class="fw-bold">{{ strtoupper($row['sourcing_type']) }}</td>
                    <td>{{ $row['customer_name'] }}</td>
                    <td class="text-end fw-bold text-danger">₹{{ number_format($row['total_return_amount'], 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-box">
        <div class="summary-row"><span>Total Return Receipts:</span> <strong>{{ count($records) }}</strong></div>
        <div class="summary-row"><span>Total Returned Qty:</span> <strong>{{ number_format($summaryStats['total_qty'], 2) }}</strong></div>
        <div class="summary-row"><span>Taxable Refund Subtotal:</span> <strong>₹{{ number_format($summaryStats['total_taxable'], 2) }}</strong></div>
        <div class="summary-row"><span>GST Tax Reversal:</span> <strong>₹{{ number_format($summaryStats['total_tax'], 2) }}</strong></div>
        <div class="summary-row" style="border-top: 1px solid #cbd5e1; font-size: 11px; margin-top: 4px; padding-top: 4px;">
            <span>Grand Total Refund Amount:</span> <strong>₹{{ number_format($summaryStats['total_refund'], 2) }}</strong>
        </div>
    </div>
</body>
</html>
