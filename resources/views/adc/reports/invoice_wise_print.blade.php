<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Wise Report - ADC Panel</title>
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
        <h2>ADC Invoice Wise Sales Report</h2>
        <p>Period: <strong>{{ $fromDate->format('d/m/Y') }}</strong> to <strong>{{ $toDate->format('d/m/Y') }}</strong> | Generated: {{ date('d/m/Y h:i A') }}</p>
    </div>

    <div class="summary-box">
        <div>Total Revenue: <strong>₹{{ number_format($totalRevenue, 2) }}</strong></div>
        <div>Cash Sales: <strong>₹{{ number_format($cashSales, 2) }}</strong></div>
        <div>Credit Sales: <strong>₹{{ number_format($creditSales, 2) }}</strong></div>
        <div>Total Invoices: <strong>{{ number_format($orders->count()) }}</strong></div>
    </div>

    <table>
        <thead>
            <tr>
                <th style="width: 4%;" class="text-center">#</th>
                <th style="width: 14%;">Invoice No</th>
                <th style="width: 20%;">Customer Name</th>
                <th style="width: 12%;">Mobile</th>
                <th style="width: 15%;">Date & Time</th>
                <th style="width: 10%;" class="text-end">Subtotal</th>
                <th style="width: 9%;" class="text-end">Discount</th>
                <th style="width: 10%;" class="text-end">Net Total</th>
                <th style="width: 6%;" class="text-center">Mode</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $idx => $ord)
                <tr>
                    <td class="text-center">{{ $idx + 1 }}</td>
                    <td class="fw-bold font-monospace">
                        @php
                            $invNo = $ord->invoice?->invoice_number ?: ($ord->received_invoice_number ?: null);
                        @endphp
                        {{ $invNo ?: '#'.$ord->order_no }}
                        @if($invNo && $invNo !== $ord->order_no)
                            <br><small style="color: #64748b; font-weight: normal;">Ord: #{{ $ord->order_no }}</small>
                        @endif
                    </td>
                    <td>{{ $ord->customer_name ?: ($ord->customer?->name ?: 'Walk-in Customer') }}</td>
                    <td class="font-monospace">{{ $ord->customer_mobile ?: ($ord->customer?->mobile ?: '—') }}</td>
                    <td>{{ $ord->created_at->format('d/m/Y H:i') }}</td>
                    <td class="text-end font-monospace">₹{{ number_format($ord->subtotal ?? 0, 2) }}</td>
                    <td class="text-end font-monospace" style="color: #dc2626;">₹{{ number_format($ord->discount_amount ?? 0, 2) }}</td>
                    <td class="text-end font-monospace fw-bold" style="color: #16a34a;">₹{{ number_format((float) $ord->total_amount - (float) ($ord->total_returned_amount ?? 0), 2) }}</td>
                    <td class="text-center font-monospace fw-bold">{{ strtoupper($ord->payment_mode ?? 'CASH') }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="text-center" style="padding: 20px;">No sales invoices found.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

</body>
</html>
