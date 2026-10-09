<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>P&L Financial Statement - ADC Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #0f172a;
            background: #ffffff;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 12px;
            margin-bottom: 15px;
        }
        .header h2 { margin: 0 0 4px 0; font-size: 20px; text-transform: uppercase; letter-spacing: 0.5px; }
        .header p { margin: 0; font-size: 11px; color: #475569; }
        .summary-box {
            display: flex;
            justify-content: space-between;
            background: #f8fafc;
            padding: 10px 16px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 12px;
            margin-bottom: 15px;
        }
        table th, table td {
            border: 1px solid #cbd5e1;
            padding: 10px 12px;
            text-align: left;
        }
        table th {
            background-color: #f1f5f9;
            font-weight: bold;
            text-transform: uppercase;
        }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .font-monospace { font-family: monospace; }
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
        }
    </style>
</head>
<body onload="window.print();">

    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="background: #22c55e; color: #fff; border: none; padding: 8px 18px; border-radius: 4px; font-weight: bold; cursor: pointer;">🖨️ Print Statement</button>
        <button onclick="window.close()" style="background: #ef4444; color: #fff; border: none; padding: 8px 18px; border-radius: 4px; font-weight: bold; cursor: pointer; margin-left: 8px;">✖ Close</button>
    </div>

    <div class="header">
        @php
            $siteLogo = \App\Models\SystemSetting::getVal('website_header_logo', \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png')));
            if ($siteLogo && !str_starts_with($siteLogo, 'http') && !str_starts_with($siteLogo, '/')) {
                $siteLogo = asset('storage/' . $siteLogo);
            }
        @endphp
        <img src="{{ $siteLogo }}" style="max-height: 50px; max-width: 200px; object-fit: contain; margin-bottom: 6px;" alt="Logo" onerror="this.style.display='none'">
        <h2>ADC Profit & Loss Financial Statement</h2>
        <p>Statement Period: <strong>{{ $fromDate->format('d/m/Y') }}</strong> to <strong>{{ $toDate->format('d/m/Y') }}</strong> | Generated: {{ date('d/m/Y h:i A') }}</p>
    </div>

    <div class="summary-box">
        <div>Sales Income: <strong>₹{{ number_format($totalSalesIncome, 2) }}</strong></div>
        <div>COGS: <strong>₹{{ number_format($cogs, 2) }}</strong></div>
        <div>Gross Margin: <strong>{{ $grossMarginPct }}%</strong></div>
        <div>Net Profit: <strong style="color: {{ $netProfit >= 0 ? '#16a34a' : '#dc2626' }};">₹{{ number_format($netProfit, 2) }} ({{ $netMarginPct }}%)</strong></div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 70%;">Financial Statement Line Item</th>
                <th style="width: 30%;" class="text-end">Amount (INR)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td><strong>Total Sales Income (Gross Revenue)</strong></td>
                <td class="text-end font-monospace fw-bold" style="color: #16a34a;">₹{{ number_format($totalSalesIncome, 2) }}</td>
            </tr>
            <tr>
                <td>Less: Cost of Goods Sold (COGS)</td>
                <td class="text-end font-monospace" style="color: #d97706;">- ₹{{ number_format($cogs, 2) }}</td>
            </tr>
            <tr style="background-color: #f8fafc;">
                <td><strong>GROSS PROFIT (Margin: {{ $grossMarginPct }}%)</strong></td>
                <td class="text-end font-monospace fw-bold" style="color: #7c3aed;">₹{{ number_format($grossProfit, 2) }}</td>
            </tr>
            <tr>
                <td>Less: Local Purchases / Operating Expenses</td>
                <td class="text-end font-monospace" style="color: #dc2626;">- ₹{{ number_format($operatingPurchases, 2) }}</td>
            </tr>
            <tr style="background-color: #f0fdf4;">
                <td><strong style="font-size: 14px;">NET PROFIT / (LOSS) (Net Margin: {{ $netMarginPct }}%)</strong></td>
                <td class="text-end font-monospace fw-bold" style="font-size: 15px; color: {{ $netProfit >= 0 ? '#16a34a' : '#dc2626' }};">₹{{ number_format($netProfit, 2) }}</td>
            </tr>
        </tbody>
    </table>

</body>
</html>
