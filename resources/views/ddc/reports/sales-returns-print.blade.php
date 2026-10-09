<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sale Return Report - DDC Panel</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #000;
            background: #fff;
            margin: 0;
            padding: 15px;
            width: 100%;
            box-sizing: border-box;
        }

        .header {
            text-align: center;
            border-bottom: 2px solid #000;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }

        .header h2 {
            margin: 0 0 5px 0;
            font-size: 20px;
            text-transform: uppercase;
        }

        .header p {
            margin: 0;
            font-size: 12px;
        }

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

        table th,
        table td {
            border: 1px solid #94a3b8;
            padding: 6px 8px;
            text-align: left;
        }

        table th {
            background-color: #e2e8f0;
            font-weight: bold;
        }

        .text-end {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .fw-bold {
            font-weight: bold;
        }

        @media print {
            .no-print {
                display: none !important;
            }

            body {
                margin: 0;
            }
        }
    </style>
</head>

<body>

    <div class="no-print" style="margin-bottom: 15px; text-align: right;">
        <button onclick="window.print()"
            style="background: #22c55e; color: #fff; border: none; padding: 8px 16px; border-radius: 4px; font-weight: bold; cursor: pointer;">🖨️
            Print Report</button>
        <button onclick="window.close()"
            style="background: #ef4444; color: #fff; border: none; padding: 8px 16px; border-radius: 4px; font-weight: bold; cursor: pointer; margin-left: 8px;">✖
            Close</button>
    </div>

    <div class="header">
        @php
            $siteLogo = \App\Models\SystemSetting::getVal('website_header_logo', \App\Models\SystemSetting::getVal('panel_logo', asset('assets/images/logo.png')));
            $sourcingScope = strtoupper($filters['sourcing'] ?? 'ALL');
        @endphp
        <img src="{{ $siteLogo }}" style="max-height: 50px; margin-bottom: 5px;" alt="Logo"
            onerror="this.style.display='none'">
        <h2>DDC Sale Return Report ({{ $sourcingScope }} SCOPE)</h2>
        <p>Generated Date: {{ date('d/m/Y h:i A') }}</p>
    </div>

    <div class="summary-box">
        <div>Total Returns: <strong>{{ number_format($summaryStats['total_count']) }}</strong></div>
        <div>Total Returned Qty: <strong>{{ number_format($summaryStats['total_returned_qty'], 2) }}</strong></div>
        <div>Taxable Refund Subtotal: <strong>₹{{ number_format($summaryStats['subtotal_refund'], 2) }}</strong></div>
        <div>GST Tax Reversal: <strong>₹{{ number_format($summaryStats['tax_reversal'], 2) }}</strong></div>
        <div>Total Refund Amount: <strong>₹{{ number_format($summaryStats['total_refund'], 2) }}</strong></div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 3%;" class="text-center">#</th>
                <th style="width: 12%;">Return Number</th>
                <th style="width: 11%;">Order No</th>
                <th style="width: 11%;">Invoice No</th>
                <th style="width: 10%;">Channel</th>
                <th style="width: 11%;">Date & Time</th>
                <th style="width: 8%;">Sourcing</th>
                <th style="width: 14%;">Customer Details</th>
                <th style="width: 5%;" class="text-center">Qty</th>
                <th style="width: 8%;" class="text-end">Refund Amount</th>
                <th style="width: 7%;" class="text-center">Refund Mode</th>
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
                    <td class="text-center">{{ number_format($row['total_qty'], 2) }}</td>
                    <td class="text-end fw-bold">₹{{ number_format($row['total_return_amount'], 2) }}</td>
                    <td class="text-center">{{ $row['refund_mode'] }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <script>
        window.onload = function () {
            setTimeout(function () {
                window.print();
            }, 300);
        };
    </script>
</body>

</html>