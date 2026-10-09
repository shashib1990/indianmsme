<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profit & Loss Statement - DDC Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            background: #fff;
            margin: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h2 { margin: 0 0 5px 0; font-size: 20px; text-transform: uppercase; }
        .header p { margin: 0; font-size: 12px; }
        .summary-box {
            display: flex;
            justify-content: space-between;
            background: #f1f5f9;
            padding: 10px 15px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            margin-bottom: 15px;
            font-size: 12px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 11px;
            margin-bottom: 15px;
        }
        table th, table td {
            border: 1px solid #94a3b8;
            padding: 6px 8px;
            text-align: left;
        }
        table th {
            background-color: #e2e8f0;
            font-weight: bold;
        }
        .text-end { text-align: right; }
        .text-center { text-align: center; }
        .fw-bold { font-weight: bold; }
        .bg-subtle { background-color: #f8fafc; }
        .bg-total { background-color: #e2e8f0; font-weight: bold; }
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="background: #22c55e; color: #fff; border: none; padding: 8px 16px; border-radius: 4px; font-weight: bold; cursor: pointer;">🖨️ Print Statement</button>
        <button onclick="window.close()" style="background: #ef4444; color: #fff; border: none; padding: 8px 16px; border-radius: 4px; font-weight: bold; cursor: pointer; margin-left: 8px;">✖ Close</button>
    </div>

    <div class="header">
        @php
            $siteLogo = \App\Models\SystemSetting::getVal('website_header_logo', \App\Models\SystemSetting::getVal('panel_logo', asset('assets/images/logo.png')));
        @endphp
        <img src="{{ $siteLogo }}" style="max-height: 50px; margin-bottom: 5px;" alt="Logo" onerror="this.style.display='none'">
        <h2>DDC Profit & Loss Statement ({{ strtoupper($source) }})</h2>
        <p>Period: {{ Carbon\Carbon::parse($startDate)->format('d M Y') }} to {{ Carbon\Carbon::parse($endDate)->format('d M Y') }} | Scope: {{ strtoupper($source) }} | Generated Date: {{ date('d/m/Y h:i A') }}</p>
    </div>

    <div class="summary-box">
        <div>Total Revenue: <strong>₹{{ number_format($pnlData['revenue']['total'], 2) }}</strong></div>
        <div>COGS: <strong>₹{{ number_format($pnlData['cogs']['total'], 2) }}</strong></div>
        <div>Gross Profit: <strong>₹{{ number_format($pnlData['gross_profit'], 2) }}</strong></div>
        <div>Gross Margin: <strong>{{ number_format($pnlData['gross_margin_pct'], 2) }}%</strong></div>
        <div>Net Profit: <strong>₹{{ number_format($pnlData['net_profit'], 2) }}</strong></div>
    </div>

    <h3>1. Comparative Financial Analysis (MSME vs Regional)</h3>
    <table>
        <thead>
            <tr class="bg-total text-center">
                <th>Financial Component</th>
                <th>MSME (Central)</th>
                <th>Regional (Local)</th>
                <th>Combined Total</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td class="fw-bold">Sales Revenue (Gross Income)</td>
                <td class="text-end">₹{{ number_format($pnlData['msme']['revenue']['total'], 2) }}</td>
                <td class="text-end">₹{{ number_format($pnlData['local']['revenue']['total'], 2) }}</td>
                <td class="text-end fw-bold">₹{{ number_format($pnlData['combined']['revenue']['total'], 2) }}</td>
            </tr>
            <tr>
                <td class="fw-bold">Cost of Goods Sold (COGS)</td>
                <td class="text-end">₹{{ number_format($pnlData['msme']['cogs']['total'], 2) }}</td>
                <td class="text-end">₹{{ number_format($pnlData['local']['cogs']['total'], 2) }}</td>
                <td class="text-end fw-bold">₹{{ number_format($pnlData['combined']['cogs']['total'], 2) }}</td>
            </tr>
            <tr>
                <td class="fw-bold">Gross Profit</td>
                <td class="text-end fw-bold">₹{{ number_format($pnlData['msme']['gross_profit'], 2) }}</td>
                <td class="text-end fw-bold">₹{{ number_format($pnlData['local']['gross_profit'], 2) }}</td>
                <td class="text-end fw-bold">₹{{ number_format($pnlData['combined']['gross_profit'], 2) }}</td>
            </tr>
            <tr>
                <td class="fw-bold">Gross Margin (%)</td>
                <td class="text-center">{{ number_format($pnlData['msme']['gross_margin_pct'], 2) }}%</td>
                <td class="text-center">{{ number_format($pnlData['local']['gross_margin_pct'], 2) }}%</td>
                <td class="text-center fw-bold">{{ number_format($pnlData['combined']['gross_margin_pct'], 2) }}%</td>
            </tr>
            <tr>
                <td class="fw-bold">Stock Purchases (Period Expenses)</td>
                <td class="text-end">₹{{ number_format($pnlData['msme']['purchases']['total'], 2) }}</td>
                <td class="text-end">₹{{ number_format($pnlData['local']['purchases']['total'], 2) }}</td>
                <td class="text-end fw-bold">₹{{ number_format($pnlData['combined']['purchases']['total'], 2) }}</td>
            </tr>
            <tr class="bg-total">
                <td>Net Operating Profit</td>
                <td class="text-end fw-bold">₹{{ number_format($pnlData['msme']['net_profit'], 2) }}</td>
                <td class="text-end fw-bold">₹{{ number_format($pnlData['local']['net_profit'], 2) }}</td>
                <td class="text-end fw-bold">₹{{ number_format($pnlData['combined']['net_profit'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <h3>2. Active Statement Breakdown ({{ strtoupper($source) }})</h3>
    <table>
        <tbody>
            <tr class="bg-total">
                <td colspan="2">1. REVENUE / INCOME</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Retail POS Sales (CSP)</td>
                <td class="text-end">₹{{ number_format($pnlData['revenue']['retail_pos'], 2) }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Wholesale POS Sales (MSP)</td>
                <td class="text-end">₹{{ number_format($pnlData['revenue']['wholesale_pos'], 2) }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">ADC Canteen Direct Orders</td>
                <td class="text-end">₹{{ number_format($pnlData['revenue']['adc_canteen'], 2) }}</td>
            </tr>
            <tr class="bg-subtle fw-bold">
                <td>TOTAL GROSS REVENUE (A)</td>
                <td class="text-end">₹{{ number_format($pnlData['revenue']['total'], 2) }}</td>
            </tr>

            <tr class="bg-total">
                <td colspan="2">2. COST OF GOODS SOLD (COGS)</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Retail POS Goods Cost</td>
                <td class="text-end">₹{{ number_format($pnlData['cogs']['retail_pos'], 2) }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">Wholesale POS Goods Cost</td>
                <td class="text-end">₹{{ number_format($pnlData['cogs']['wholesale_pos'], 2) }}</td>
            </tr>
            <tr>
                <td style="padding-left: 20px;">ADC Canteen Orders Goods Cost</td>
                <td class="text-end">₹{{ number_format($pnlData['cogs']['adc_canteen'], 2) }}</td>
            </tr>
            <tr class="bg-subtle fw-bold">
                <td>TOTAL COST OF GOODS SOLD (B)</td>
                <td class="text-end">₹{{ number_format($pnlData['cogs']['total'], 2) }}</td>
            </tr>

            <tr class="bg-total">
                <td colspan="2">3. PROFITABILITY SUMMARY</td>
            </tr>
            <tr>
                <td>GROSS PROFIT (A - B)</td>
                <td class="text-end fw-bold">₹{{ number_format($pnlData['gross_profit'], 2) }}</td>
            </tr>
            <tr>
                <td>Gross Profit Margin (%)</td>
                <td class="text-end fw-bold">{{ number_format($pnlData['gross_margin_pct'], 2) }}%</td>
            </tr>
            <tr class="bg-total" style="font-size: 13px;">
                <td>NET OPERATING PROFIT / (LOSS)</td>
                <td class="text-end">₹{{ number_format($pnlData['net_profit'], 2) }}</td>
            </tr>
        </tbody>
    </table>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 300);
        };
    </script>
</body>
</html>
