<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Local Purchase Report - DDC Panel</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }

        h2 {
            margin-bottom: 5px;
            color: #0f766e;
        }

        .meta-info {
            margin-bottom: 20px;
            font-size: 11px;
            color: #666;
            border-bottom: 1px solid #ddd;
            padding-bottom: 10px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 6px 8px;
            text-align: left;
            font-size: 11px;
        }

        th {
            background-color: #f3f4f6;
            font-weight: bold;
            text-transform: uppercase;
        }

        .text-right {
            text-align: right;
        }

        .fw-bold {
            font-weight: bold;
        }

        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body onload="window.print();">
    <div class="no-print" style="margin-bottom: 15px;">
        <button onclick="window.print();"
            style="padding: 6px 16px; background: #0f766e; color: white; border: none; border-radius: 4px; cursor: pointer;">Print
            Report</button>
        <button onclick="window.close();"
            style="padding: 6px 16px; background: #6b7280; color: white; border: none; border-radius: 4px; cursor: pointer;">Close
            Window</button>
    </div>

    <h2>Local Purchase Report</h2>
    <div class="meta-info">
        <span>Generated Date: {{ date('d M Y, h:i A') }}</span> |
        <span>DDC Panel Local Sourcing</span>
    </div>

    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Purchase No</th>
                <th>Date</th>
                <th>Supplier</th>
                <th>Invoice No</th>
                <th>Product Name</th>
                <th>Qty</th>
                <th>Purchase Price</th>
                <th>Landing Cost</th>
                <th>MRP</th>
                <th>CSP</th>
                <th>MSP</th>
                <th>GST %</th>
                <th>Total (₹)</th>
                <th>Source</th>
            </tr>
        </thead>
        <tbody>
            @php $grandTotal = 0; @endphp
            @forelse($items as $index => $item)
                @php
                    $purchase = $item->purchase;
                    $product = $item->localProduct;
                    $totalCost = ($item->purchase_price * $item->quantity) + $item->freight_share + $item->loading_share + $item->other_share;
                    $grandTotal += $totalCost;
                @endphp
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>{{ $purchase->purchase_no ?? '—' }}</td>
                    <td>{{ $purchase->purchase_date ? $purchase->purchase_date->format('d/m/Y') : '—' }}</td>
                    <td>{{ $purchase->supplier_name ?? '—' }}</td>
                    <td>{{ $purchase->invoice_number ?? '—' }}</td>
                    <td>{{ $product->product_name ?? '—' }} ({{ $product->local_product_code ?? '—' }})</td>
                    <td>{{ $item->quantity }}</td>
                    <td>₹{{ number_format($item->purchase_price, 2) }}</td>
                    <td>₹{{ number_format($item->landing_cost, 2) }}</td>
                    <td>₹{{ number_format($item->mrp, 2) }}</td>
                    <td>₹{{ number_format($item->csp, 2) }}</td>
                    <td>₹{{ number_format($item->msp, 2) }}</td>
                    <td>{{ $item->gst_rate }}%</td>
                    <td class="fw-bold">₹{{ number_format($totalCost, 2) }}</td>
                    <td>LOCAL</td>
                </tr>
            @empty
                <tr>
                    <td colspan="15" style="text-align: center; padding: 20px;">No purchase data available.</td>
                </tr>
            @endforelse
        </tbody>
        @if(count($items) > 0)
            <tfoot>
                <tr>
                    <th colspan="13" class="text-right">Grand Total:</th>
                    <th colspan="2" class="fw-bold">₹{{ number_format($grandTotal, 2) }}</th>
                </tr>
            </tfoot>
        @endif
    </table>
</body>

</html>