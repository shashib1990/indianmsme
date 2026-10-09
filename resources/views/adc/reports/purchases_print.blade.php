<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Purchase Report - ADC Panel</title>
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
            font-size: 11px;
            margin-bottom: 15px;
        }
        table th, table td {
            border: 1px solid #94a3b8;
            padding: 6px 8px;
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
        <h2>ADC Purchase & Sourcing Report</h2>
        <p>Period: <strong>{{ $fromDate->format('d/m/Y') }}</strong> to <strong>{{ $toDate->format('d/m/Y') }}</strong> | Generated: {{ date('d/m/Y h:i A') }}</p>
    </div>

    <div class="summary-box">
        <div>Purchase Volume: <strong>₹{{ number_format($totalPurchaseAmount, 2) }}</strong></div>
        <div>Purchase Orders: <strong>{{ number_format($totalPurchaseOrders) }}</strong></div>
        <div>Items Procured: <strong>{{ number_format($totalItemsPurchased) }} Units</strong></div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%;" class="text-center">#</th>
                <th style="width: 15%;">Purchase No</th>
                <th style="width: 25%;">Supplier Name</th>
                <th style="width: 18%;">Purchase Date</th>
                <th style="width: 10%;" class="text-center">Items Count</th>
                <th style="width: 12%;" class="text-end">Subtotal</th>
                <th style="width: 16%;" class="text-end">Total Amount</th>
            </tr>
        </thead>
        <tbody>
            @forelse($purchases as $idx => $p)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="fw-bold font-monospace">#{{ $p->purchase_no }}</td>
                    <td>{{ $p->supplier_name ?: 'N/A' }}</td>
                    <td class="font-monospace">{{ $p->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-center font-monospace">{{ $p->items->count() }}</td>
                    <td class="text-end font-monospace">₹{{ number_format($p->subtotal ?? 0, 2) }}</td>
                    <td class="text-end font-monospace fw-bold" style="color: #16a34a;">₹{{ number_format($p->total_amount ?? 0, 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 20px;">No purchase records found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
