@extends('layouts.ddc')

@section('title', 'Purchase Invoice — ' . ($purchase->invoice_number ?? $purchase->purchase_no))

@section('content')
<style>
/* Modern Professional Invoice Styling */
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

.invoice-summary-card {
    background: linear-gradient(135deg, #064e3b 0%, #047857 100%);
    color: #ffffff;
    border-radius: 8px;
    padding: 16px 20px;
}

@media print {
    @page {
        size: A4 portrait;
        margin: 10mm 12mm;
    }

    /* Hide UI navigation elements */
    header, nav, .sidebar, .navbar, .no-print, .btn, footer, #sidebar-wrapper, .app-header {
        display: none !important;
    }
    
    body, .main-content, .content-wrapper, .container-fluid, .page-wrapper {
        background: #ffffff !important;
        padding: 0 !important;
        margin: 0 !important;
        width: 100% !important;
    }
    
    .invoice-card {
        border: none !important;
        box-shadow: none !important;
        padding: 0 !important;
        margin: 0 !important;
        border-radius: 0 !important;
    }
    
    .print-container {
        padding: 0 !important;
        width: 100% !important;
        color: #000000 !important;
    }

    .invoice-meta-box {
        background-color: #f8fafc !important;
        border: 1px solid #94a3b8 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
    
    .invoice-table-custom {
        table-layout: fixed !important;
        width: 100% !important;
    }
    
    .invoice-table-custom th, 
    .invoice-table-custom td {
        border: 1px solid #cbd5e1 !important;
        padding: 6px 8px !important;
        font-size: 10px !important;
        word-wrap: break-word !important;
    }

    .invoice-table-custom th {
        background-color: #e2e8f0 !important;
        color: #000000 !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    .invoice-summary-card {
        background: #047857 !important;
        color: #ffffff !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }
}
</style>

<div class="container-fluid py-3 print-container">
    <!-- Action Bar (Hidden in Print) -->
    <div class="mb-3 d-flex align-items-center justify-content-between no-print">
        <a href="{{ route('ddc.local-purchases.index') }}" class="btn btn-sm btn-outline-secondary rounded-3 px-3 shadow-sm">
            <iconify-icon icon="solar:arrow-left-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Back to Purchases
        </a>
        <div class="d-flex gap-2">
            <button onclick="window.print();" class="btn btn-sm btn-primary rounded-3 px-4 shadow-sm fw-bold">
                <iconify-icon icon="solar:printer-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Print Invoice
            </button>
        </div>
    </div>

    <!-- Main Invoice Document -->
    <div class="invoice-card p-4 p-md-5">
        <!-- Invoice Header Top -->
        <div class="row align-items-center pb-4 mb-4 border-bottom">
            <div class="col-7">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="badge bg-success text-white fw-bold px-3 py-1 fs-12 rounded-pill text-uppercase">Regional Stock In Receipt</span>
                </div>
                <h2 class="invoice-header-title mb-1">STOCK INVOICE</h2>
                <div class="text-muted small">
                    Receipt Ref #: <strong class="text-slate-800">{{ $purchase->purchase_no }}</strong>
                </div>
            </div>
            <div class="col-5 text-end">
                <div class="fw-bold text-slate-800 fs-16">INDIAN MSME</div>
                <div class="text-muted small">Regional Sourcing & Procurement</div>
                <div class="text-muted small">Date: <strong>{{ $purchase->purchase_date->format('d M Y') }}</strong></div>
            </div>
        </div>

        <!-- Supplier & Recipient Meta Grid -->
        <div class="row g-3 mb-4">
            <div class="col-6">
                <div class="invoice-meta-box h-100">
                    <div class="text-uppercase fs-11 fw-bold text-muted mb-2 border-bottom pb-1">Supplier / Vendor Details</div>
                    <div class="fs-15 fw-bold text-slate-900 mb-1">{{ $purchase->supplier_name }}</div>
                    @if($purchase->supplier_mobile)
                        <div class="fs-13 text-slate-600"><iconify-icon icon="solar:phone-calling-bold" class="align-middle me-1 no-print"></iconify-icon>Contact: {{ $purchase->supplier_mobile }}</div>
                    @endif
                    @if($purchase->invoice_number)
                        <div class="fs-13 text-slate-800 mt-2">Bill / Invoice No: <strong>{{ $purchase->invoice_number }}</strong></div>
                    @endif
                    @if($purchase->invoice_date)
                        <div class="fs-12 text-muted">Invoice Date: {{ $purchase->invoice_date->format('d M Y') }}</div>
                    @endif
                </div>
            </div>

            <div class="col-6">
                <div class="invoice-meta-box h-100">
                    <div class="text-uppercase fs-11 fw-bold text-muted mb-2 border-bottom pb-1">DDC Center Details</div>
                    <div class="fs-15 fw-bold text-slate-900 mb-1">{{ auth('ddc')->user()?->name ?? 'District Development Coordinator' }}</div>
                    @if(auth('ddc')->user()?->mobile)
                        <div class="fs-13 text-slate-600"><iconify-icon icon="solar:phone-calling-bold" class="align-middle me-1 no-print"></iconify-icon>Contact: {{ auth('ddc')->user()?->mobile }}</div>
                    @endif
                    <div class="fs-13 text-slate-800 mt-2">DDC Code: <strong>{{ auth('ddc')->user()?->ddcProfile?->ddc_code ?? 'N/A' }}</strong></div>
                    <div class="fs-12 text-muted">Stock Entry Time: {{ $purchase->created_at->format('d M Y, h:i A') }}</div>
                </div>
            </div>
        </div>

        <!-- Products Item Table -->
        <div class="table-responsive mb-4">
            <table class="invoice-table-custom">
                <thead>
                    <tr>
                        <th style="width: 4%;" class="text-center">#</th>
                        <th style="width: 25%;">Product Name & Code</th>
                        <th style="width: 14%;">Category</th>
                        <th style="width: 7%;" class="text-center">Qty</th>
                        <th style="width: 10%;" class="text-end">MRP (₹)</th>
                        <th style="width: 11%;" class="text-end">Landing Cost (₹)</th>
                        <th style="width: 10%;" class="text-end">CSP / Retail (₹)</th>
                        <th style="width: 10%;" class="text-end">MSP / Wholesale (₹)</th>
                        <th style="width: 9%;" class="text-end pe-3">Total Amount (₹)</th>
                    </tr>
                </thead>
                <tbody>
                    @php $grandTotalQty = 0; @endphp
                    @foreach($purchase->items as $index => $item)
                        @php
                            $prod = $item->localProduct;
                            $qty = $item->quantity;
                            $grandTotalQty += $qty;
                            $unitCost = ($item->landing_cost > 0 ? $item->landing_cost : $item->purchase_price);
                            $lineTotal = $unitCost * $qty;
                        @endphp
                        <tr>
                            <td class="text-center fw-bold text-muted">{{ $index + 1 }}</td>
                            <td>
                                <div class="fw-bold text-slate-900">{{ $prod->product_name ?? '—' }}</div>
                                <span class="badge bg-light text-dark border fs-11 fw-semibold mt-1">{{ $prod->local_product_code ?? '—' }}</span>
                                @if($item->batch_number)
                                    <div class="fs-11 text-muted mt-1">Batch: <strong>{{ $item->batch_number }}</strong></div>
                                @endif
                                @if($item->mfg_date || $item->expire_date)
                                    <div class="fs-10 text-muted">
                                        @if($item->mfg_date) <span>MFG: {{ \Carbon\Carbon::parse($item->mfg_date)->format('d/m/Y') }}</span> @endif
                                        @if($item->expire_date) <span class="ms-1">EXP: {{ \Carbon\Carbon::parse($item->expire_date)->format('d/m/Y') }}</span> @endif
                                    </div>
                                @endif
                            </td>
                            <td>
                                <span class="fw-semibold text-slate-800">{{ $prod?->category?->name ?? '—' }}</span>
                                <span class="d-block text-muted fs-11">{{ $prod?->brand?->name ?? 'Unbranded' }}</span>
                            </td>
                            <td class="text-center">
                                <span class="fw-bold text-slate-900 fs-13">{{ $qty }}</span>
                                <span class="text-muted fs-11 d-block">{{ $prod->unit_of_measure ?? 'Units' }}</span>
                            </td>
                            <td class="text-end fw-semibold text-slate-700">{{ number_format($item->mrp, 2) }}</td>
                            <td class="text-end fw-bold text-slate-900">{{ number_format($unitCost, 2) }}</td>
                            <td class="text-end text-emerald-700 fw-bold">{{ number_format($item->csp, 2) }}</td>
                            <td class="text-end text-slate-700">{{ number_format($item->msp, 2) }}</td>
                            <td class="text-end pe-3 fw-bold text-slate-900">{{ number_format($lineTotal, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Summary & Footer Grid -->
        <div class="row align-items-center g-3 pt-2">
            <div class="col-7">
                @if($purchase->remarks)
                    <div class="invoice-meta-box">
                        <span class="text-muted d-block small fw-bold text-uppercase">Invoice Remarks:</span>
                        <span class="text-slate-800 fs-13">{{ $purchase->remarks }}</span>
                    </div>
                @else
                    <div class="text-muted fs-11">
                        * This stock receipt invoice was generated upon regional product entry into DDC inventory.
                    </div>
                @endif
            </div>

            <div class="col-5">
                <div class="invoice-summary-card">
                    <div class="d-flex justify-content-between align-items-center mb-1 fs-12">
                        <span>Total Items:</span>
                        <strong class="text-white">{{ count($purchase->items) }} Products</strong>
                    </div>
                    <div class="d-flex justify-content-between align-items-center mb-2 fs-12">
                        <span>Total Stock Received:</span>
                        <strong class="text-white">{{ $grandTotalQty }} Units</strong>
                    </div>
                    <div class="border-top border-light-subtle pt-2 d-flex justify-content-between align-items-center">
                        <span class="fw-bold fs-13 text-uppercase">Grand Total Amount</span>
                        <h3 class="fw-bold text-white mb-0" style="font-family: 'Outfit', sans-serif;">₹{{ number_format($purchase->total_amount, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Signatures Row -->
        <div class="row align-items-end mt-5 pt-4">
            <div class="col-6">
                <div class="fs-11 text-muted">
                    Generated via Indian MSME DDC System
                </div>
            </div>
            <div class="col-6 text-end">
                <div class="pt-3 border-top border-dark d-inline-block text-center px-4" style="min-width: 200px;">
                    <span class="fw-bold text-slate-900 d-block fs-12">Authorized Signature</span>
                    <span class="text-muted fs-11">DDC Regional Procurement</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
