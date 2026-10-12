<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Customer Credit Ledger - {{ $customer->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #fff; font-size: 13px; color: #1e293b; }
        .statement-box { max-width: 900px; margin: 20px auto; padding: 25px; border: 1px solid #cbd5e1; border-radius: 8px; }
        .table-ledger th { background-color: #0f172a; color: #fff; text-transform: uppercase; font-size: 11px; }
        .no-print-bar { background: #1e293b; color: #fff; padding: 12px; text-align: center; position: sticky; top: 0; z-index: 999; }
        @media print {
            .no-print-bar { display: none !important; }
            .statement-box { border: none; padding: 0; margin: 0; max-width: 100%; }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <button onclick="window.print()" class="btn btn-success btn-sm px-4 me-2 fw-bold">🖨️ Print Statement (A4)</button>
        <button onclick="window.close()" class="btn btn-danger btn-sm px-4 fw-bold">✖ Close Window</button>
    </div>

    <div class="statement-box">
        <!-- Header -->
        <div class="d-flex justify-content-between align-items-center mb-3 pb-3 border-bottom">
            <div>
                @php
                    $siteLogo = \App\Models\SystemSetting::getVal('website_header_logo', \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png')));
                    if ($siteLogo && !str_starts_with($siteLogo, 'http') && !str_starts_with($siteLogo, '/')) {
                        $siteLogo = asset('storage/' . $siteLogo);
                    }
                @endphp
                <img src="{{ $siteLogo }}" style="max-height: 48px; max-width: 180px; object-fit: contain; margin-bottom: 6px; display: block;" alt="Logo" onerror="this.style.display='none'">
                <h4 class="fw-bold mb-1 text-primary">CUSTOMER CREDIT STATEMENT</h4>
                <div class="fs-12 text-muted">ADC Canteen Retail Credit Ledger Account</div>
            </div>
            <div class="text-end">
                <h6 class="fw-bold mb-0">Ardh Sainik Mall</h6>
                <div class="small text-muted">Statement Date: {{ now()->format('d M Y, h:i A') }}</div>
            </div>
        </div>

        <!-- Customer Summary Card -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="p-3 bg-light rounded border">
                    <div class="fw-bold text-dark fs-15 mb-1">{{ $customer->name }}</div>
                    <div class="text-muted small">Mobile: <strong>{{ $customer->mobile }}</strong></div>
                    <div class="text-muted small">Address: {{ $customer->address ?: 'N/A' }}</div>
                    @if($customer->gst_number)
                        <div class="text-muted small">GSTIN: {{ $customer->gst_number }}</div>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="p-3 bg-light rounded border">
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Total Credit Given:</span>
                        <span class="fw-bold text-danger">₹{{ number_format($customer->total_credit_given, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span class="text-muted">Total Payment Received:</span>
                        <span class="fw-bold text-success">₹{{ number_format($customer->total_payment_received, 2) }}</span>
                    </div>
                    <hr class="my-1.5">
                    <div class="d-flex justify-content-between fs-14 fw-bold">
                        <span>Current Outstanding:</span>
                        <span class="{{ $customer->outstanding_balance > 0 ? 'text-danger' : 'text-success' }}">
                            ₹{{ number_format($customer->outstanding_balance, 2) }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ledger Table -->
        <div class="table-responsive rounded border mb-4">
            <table class="table table-bordered align-middle table-ledger mb-0">
                <thead>
                    <tr>
                        <th class="text-center" style="width: 40px;">#</th>
                        <th>Date & Time</th>
                        <th>Reference No</th>
                        <th>Transaction Type</th>
                        <th class="text-end">Debit (₹)</th>
                        <th class="text-end">Credit (₹)</th>
                        <th class="text-center">Mode</th>
                        <th class="text-end pe-3">Balance (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $index => $row)
                        <tr>
                            <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                            <td>{{ $row->created_at->format('d/m/Y h:i A') }}</td>
                            <td class="fw-bold font-monospace">{{ $row->reference_no }}</td>
                            <td>
                                @if($row->transaction_type === 'Credit Sale')
                                    <span class="badge bg-danger">Credit Sale</span>
                                @elseif($row->transaction_type === 'Payment Received')
                                    <span class="badge bg-success">Payment Received</span>
                                @else
                                    <span class="badge bg-info text-dark">{{ $row->transaction_type }}</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-danger">
                                {{ $row->debit > 0 ? '₹'.number_format($row->debit, 2) : '—' }}
                            </td>
                            <td class="text-end fw-bold text-success">
                                {{ $row->credit > 0 ? '₹'.number_format($row->credit, 2) : '—' }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-light text-dark border">{{ $row->payment_mode }}</span>
                            </td>
                            <td class="text-end pe-3 fw-bold {{ $row->balance > 0 ? 'text-danger' : 'text-success' }}">
                                ₹{{ number_format($row->balance, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">No ledger transactions found for this customer.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="text-center small text-muted mt-4 pt-3 border-top">
            <div>This is a computer generated Customer Credit Ledger Statement.</div>
            <div class="fw-bold">Initiative of Ardh Sainik Welfare Trust (Regd) | www.ArdhsainikCanteen.in</div>
        </div>
    </div>

</body>
</html>
