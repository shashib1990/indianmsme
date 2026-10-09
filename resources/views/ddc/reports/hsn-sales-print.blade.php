<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HSN-wise Sales Report - DDC Panel</title>
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
        @media print {
            .no-print { display: none !important; }
            body { margin: 0; }
        }
    </style>
</head>
<body>

    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()" style="background: #22c55e; color: #fff; border: none; padding: 8px 16px; border-radius: 4px; font-weight: bold; cursor: pointer;">🖨️ Print Report</button>
        <button onclick="window.close()" style="background: #ef4444; color: #fff; border: none; padding: 8px 16px; border-radius: 4px; font-weight: bold; cursor: pointer; margin-left: 8px;">✖ Close</button>
    </div>

    <div class="header">
        @php
            $siteLogo = \App\Models\SystemSetting::getVal('website_header_logo', \App\Models\SystemSetting::getVal('panel_logo', asset('assets/images/logo.png')));
            $sourcingScope = strtoupper($filters['sourcing'] ?? 'ALL');
        @endphp
        <img src="{{ $siteLogo }}" style="max-height: 50px; margin-bottom: 5px;" alt="Logo" onerror="this.style.display='none'">
        <h2>DDC HSN-wise Sales Report ({{ $sourcingScope }} SCOPE)</h2>
        <p>Generated Date: {{ date('d/m/Y h:i A') }}</p>
    </div>

    <div class="summary-box">
        <div>Total HSNs: <strong>{{ number_format($summaryStats['total_hsns']) }}</strong></div>
        <div>Total Qty Sold: <strong>{{ number_format($summaryStats['total_qty'], 2) }}</strong></div>
        <div>Total Taxable: <strong>₹{{ number_format($summaryStats['total_taxable'], 2) }}</strong></div>
        <div>Total Tax Amount: <strong>₹{{ number_format($summaryStats['total_tax'], 2) }}</strong></div>
        <div>Grand Total: <strong>₹{{ number_format($summaryStats['total_amount'], 2) }}</strong></div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%;" class="text-center">#</th>
                <th style="width: 10%;">HSN Code</th>
                <th style="width: 12%;">Order No</th>
                <th style="width: 12%;">Invoice No</th>
                <th style="width: 20%;">Product Description</th>
                <th style="width: 9%;">Sourcing</th>
                <th style="width: 8%;" class="text-center">Qty Sold</th>
                <th style="width: 8%;" class="text-end">Taxable Value</th>
                <th style="width: 8%;" class="text-end">GST Amount</th>
                <th style="width: 10%;" class="text-end">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach($hsnRecords as $idx => $row)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="fw-bold">{{ $row['hsn_code'] }}</td>
                    <td class="fw-bold">{!! nl2br(e($row['order_no_excel'] ?? $row['order_no_full'])) !!}</td>
                    <td class="fw-bold text-primary">{!! nl2br(e($row['invoice_no_excel'] ?? $row['invoice_no_full'])) !!}</td>
                    <td>{{ $row['description'] }}</td>
                    <td class="fw-bold">{{ strtoupper($row['sourcing_type']) }}</td>
                    <td class="text-center">{{ number_format($row['total_qty'], 2) }}</td>
                    <td class="text-end">₹{{ number_format($row['taxable_amount'], 2) }}</td>
                    <td class="text-end">₹{{ number_format($row['tax_amount'], 2) }}</td>
                    <td class="text-end fw-bold">₹{{ number_format($row['total_amount'], 2) }}</td>
                </tr>
            @endforeach
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
