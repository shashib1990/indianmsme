@extends('layouts.adc')

@section('title', 'Purchase Invoice — ' . ($purchase->invoice_number ?? $purchase->purchase_no))

@section('content')
<style>
.invoice-card {
    background: #ffffff;
    border-radius: 12px;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
    border: 1px solid #e2e8f0;
}

.invoice-header-title {
    font-family: 'Outfit', sans-serif;
    font-weight: 800;
    color: #0f172a;
    letter-spacing: -0.5px;
}

.invoice-meta-box {
    background-color: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 14px 16px;
}

.invoice-table-custom {
    width: 100%;
    border-collapse: collapse;
}

.invoice-table-custom th {
    background-color: #f1f5f9;
    color: #334155;
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 10px 12px;
    border-bottom: 2px solid #cbd5e1;
    border-top: 1px solid #e2e8f0;
}

.invoice-table-custom td {
    padding: 10px 12px;
    font-size: 12px;
    color: #1e293b;
    border-bottom: 1px solid #e2e8f0;
    vertical-align: middle;
}

@media print {
    @page {
        size: A4 portrait;
        margin: 10mm 12mm;
    }
    header, nav, .sidebar, .navbar, .no-print, .btn, footer {
        display: none !important;
    }
    body, .container-fluid {
        background: #ffffff !important;
        padding: 0 !important;
        margin: 0 !important;
    }
    .invoice-card {
        border: none !important;
        box-shadow: none !important;
    }
}
</style>

<div class="container-fluid py-3 print-container">

    <!-- Action Toolbar (Hidden in Print) -->
    <div class="row mb-4 no-print">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <a href="{{ route('adc.local-purchases.index') }}" class="btn btn-outline-secondary rounded-3 px-3">
                <iconify-icon icon="solar:arrow-left-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Back to Purchases
            </a>
            <div class="d-flex gap-2">
                <button onclick="window.print()" class="btn btn-primary rounded-3 px-4 shadow-sm">
                    <iconify-icon icon="solar:printer-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Print Invoice
                </button>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4 no-print" role="alert">
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-20 text-success"></iconify-icon>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Invoice Card Container -->
    <div class="invoice-card p-4 p-md-5">
        
        <!-- Header -->
        <div class="row align-items-start border-bottom pb-4 mb-4 g-3">
            <div class="col-md-7">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ \App\Models\SystemSetting::getVal('website_header_logo', \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png'))) }}" alt="Logo" style="height: 55px; width: auto; object-fit: contain;">
                    <div>
                        <h3 class="invoice-header-title mb-0 fs-22">PURCHASE INVOICE / STOCK IN RECEIPT</h3>
                        <p class="text-muted fs-12 mb-0 fw-medium">ADC Regional Center Stock In Record</p>
                    </div>
                </div>
            </div>
            <div class="col-md-5 text-md-end">
                <span class="badge bg-success-subtle text-success fs-12 px-3 py-1.5 rounded-pill border border-success-subtle mb-2 fw-bold">STATUS: COMPLETED (STOCKED IN)</span>
                <h5 class="fw-bold text-slate-800 mb-0 fs-16">Receipt No: <code>{{ $purchase->purchase_no }}</code></h5>
                <span class="text-muted fs-12">Date: {{ $purchase->purchase_date->format('d M Y') }}</span>
            </div>
        </div>

        <!-- Supplier & Recipient Meta Details -->
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <div class="invoice-meta-box h-100">
                    <span class="text-uppercase fs-11 text-muted fw-bold d-block mb-1">SUPPLIER DETAILS (INWARD)</span>
                    <h5 class="fw-bold text-slate-800 mb-1 fs-15">{{ $purchase->supplier_name }}</h5>
                    @if($purchase->supplier_mobile)
                        <p class="text-muted fs-12 mb-0"><iconify-icon icon="solar:phone-bold-duotone" class="align-middle me-1"></iconify-icon> {{ $purchase->supplier_mobile }}</p>
                    @endif
                    @if($purchase->invoice_number)
                        <p class="text-slate-700 fs-12 mb-0 mt-1"><strong>Invoice No:</strong> {{ $purchase->invoice_number }} {{ $purchase->invoice_date ? '(' . $purchase->invoice_date->format('d M Y') . ')' : '' }}</p>
                    @endif
                </div>
            </div>
            <div class="col-md-6">
                <div class="invoice-meta-box h-100">
                    <span class="text-uppercase fs-11 text-muted fw-bold d-block mb-1">ADC CENTER (RECEIVER)</span>
                    <h5 class="fw-bold text-slate-800 mb-1 fs-15">{{ auth('adc')->user()->name }}</h5>
                    <p class="text-muted fs-12 mb-0"><iconify-icon icon="solar:letter-bold-duotone" class="align-middle me-1"></iconify-icon> {{ auth('adc')->user()->email }}</p>
                    <p class="text-muted fs-12 mb-0"><iconify-icon icon="solar:phone-bold-duotone" class="align-middle me-1"></iconify-icon> {{ auth('adc')->user()->mobile }}</p>
                </div>
            </div>
        </div>

        <!-- Purchase Items Table -->
        <div class="table-responsive mb-4">
            <table class="invoice-table-custom">
                <thead>
                    <tr>
                        <th style="width: 5%;" class="text-center">#</th>
                        <th style="width: 35%;">Regional Product Details</th>
                        <th style="width: 10%;" class="text-center">Qty</th>
                        <th style="width: 12%;" class="text-end">Pur. Price</th>
                        <th style="width: 12%;" class="text-end">Landing Cost</th>
                        <th style="width: 12%;" class="text-end">MRP / CSP</th>
                        <th style="width: 14%;" class="text-end pe-3">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchase->items as $idx => $item)
                        @php
                            $prod = $item->localProduct;
                        @endphp
                        <tr>
                            <td class="text-center fw-bold">{{ $idx + 1 }}</td>
                            <td>
                                <span class="fw-bold text-slate-800 d-block fs-13">{{ $prod->product_name ?? 'Local Product' }}</span>
                                <span class="text-muted fs-11">Code: <code>{{ $prod->local_product_code ?? '—' }}</code> | Barcode: {{ $prod->barcode ?? 'N/A' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1 fw-bold fs-12">{{ (float)$item->quantity }} {{ $prod->unit_of_measure ?? 'Piece' }}</span>
                            </td>
                            <td class="text-end font-monospace fs-13">₹{{ number_format($item->purchase_price, 2) }}</td>
                            <td class="text-end font-monospace fs-13 fw-bold text-slate-800">₹{{ number_format($item->landing_cost, 2) }}</td>
                            <td class="text-end font-monospace fs-12">
                                <span class="text-muted d-block text-decoration-line-through">₹{{ number_format($item->mrp, 2) }}</span>
                                <span class="fw-bold text-emerald-600">₹{{ number_format($item->csp, 2) }}</span>
                            </td>
                            <td class="text-end pe-3 font-monospace fw-bold fs-13">₹{{ number_format($item->quantity * $item->purchase_price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Remarks -->
        @if($purchase->remarks)
            <div class="p-3 bg-light rounded-3 border mb-4">
                <span class="text-uppercase fs-11 text-muted fw-bold d-block mb-1">REMARKS / NOTES</span>
                <p class="fs-12 text-slate-700 mb-0">{{ $purchase->remarks }}</p>
            </div>
        @endif

        <!-- Footer -->
        <div class="row align-items-end pt-3 mt-4 border-top">
            <div class="col-6">
                <span class="text-muted fs-11">System Generated Stock In Record • ADC Panel</span>
            </div>
            <div class="col-6 text-end">
                <h4 class="fw-bold text-primary mb-0">Total Amount: ₹{{ number_format($purchase->total_amount, 2) }}</h4>
            </div>
        </div>
    </div>
</div>
@endsection
