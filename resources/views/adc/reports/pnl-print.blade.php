<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ADC Profit & Loss Statement Print</title>
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
        .fw-bold { font-weight: bold; }
        .table-light { background-color: #f8fafc; }
        .table-success { background-color: #f0fdf4; }
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
        <h2>ADC PROFIT & LOSS STATEMENT ({{ strtoupper($filters['sourcing'] ?? 'ALL') }} SCOPE)</h2>
        <p>Generated on {{ date('d M Y, h:i A') }}</p>
    </div>

    <table class="meta-table">
        <tr>
            <td><strong>Period:</strong> {{ $filters['start_date'] ?? 'N/A' }} to {{ $filters['end_date'] ?? 'N/A' }}</td>
            <td><strong>Sourcing Scope:</strong> {{ strtoupper($filters['sourcing'] ?? 'ALL') }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 70%;">Particulars</th>
                <th class="text-end" style="width: 30%;">Amount (₹)</th>
            </tr>
        </thead>
        <tbody>
            <!-- 1. Revenue -->
            <tr class="table-light fw-bold">
                <td>1. REVENUE / INCOME</td>
                <td class="text-end">₹{{ number_format($pnlData['revenue']['total'], 2) }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Retail POS Sales (CSP)</td>
                <td class="text-end">₹{{ number_format($pnlData['revenue']['retail_pos'], 2) }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Wholesale POS Sales (MSP)</td>
                <td class="text-end">₹{{ number_format($pnlData['revenue']['wholesale_pos'], 2) }}</td>
            </tr>

            <!-- 2. COGS -->
            <tr class="table-light fw-bold">
                <td>2. COST OF GOODS SOLD (COGS)</td>
                <td class="text-end">₹{{ number_format($pnlData['cogs']['total'], 2) }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Retail POS Goods Cost</td>
                <td class="text-end">₹{{ number_format($pnlData['cogs']['retail_pos'], 2) }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Wholesale POS Goods Cost</td>
                <td class="text-end">₹{{ number_format($pnlData['cogs']['wholesale_pos'], 2) }}</td>
            </tr>

            <!-- 3. Summary -->
            <tr class="table-success fw-bold">
                <td>3. GROSS PROFIT (Revenue - COGS)</td>
                <td class="text-end">₹{{ number_format($pnlData['gross_profit'], 2) }}</td>
            </tr>
            <tr class="table-success fw-bold">
                <td>GROSS PROFIT MARGIN (%)</td>
                <td class="text-end">{{ number_format($pnlData['gross_margin_pct'], 1) }}%</td>
            </tr>
            <tr class="table-success fw-bold">
                <td>NET PROFIT / (LOSS)</td>
                <td class="text-end">₹{{ number_format($pnlData['net_profit'], 2) }}</td>
            </tr>

            <!-- 4. Stock Outlay -->
            <tr class="table-light fw-bold">
                <td>4. PURCHASES & STOCK-IN (PERIOD EXPENSES)</td>
                <td class="text-end">₹{{ number_format($pnlData['purchases']['total'], 2) }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Central Vendor PO Stock Purchases</td>
                <td class="text-end">₹{{ number_format($pnlData['purchases']['central_po'], 2) }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Regional Local Stock Purchases</td>
                <td class="text-end">₹{{ number_format($pnlData['purchases']['regional'], 2) }}</td>
            </tr>
        </tbody>
    </table>
</body>
</html>
