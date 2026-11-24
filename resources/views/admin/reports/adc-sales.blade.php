@extends('layouts.admin')

@section('title', 'Product Wise Sale Report (ADC)')
@section('page-title', 'Product Wise Sale Report (ADC)')

@push('styles')
<style>
    .report-stat-card {
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .report-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08) !important;
    }
    .table-product-report th {
        font-size: 0.72rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
        background-color: #f8fafc;
        vertical-align: middle;
        padding: 10px 12px;
    }
    .table-product-report td {
        font-size: 0.81rem;
        vertical-align: middle;
        padding: 9px 12px;
        white-space: nowrap;
    }
    .badge-pay-cash { background-color: #f1f5f9; color: #334155; border: 1px solid #cbd5e1; }
    .badge-pay-upi { background-color: #fdf2f8; color: #db2777; border: 1px solid #fbcfe8; }
    .badge-pay-card { background-color: #f5f3ff; color: #7c3aed; border: 1px solid #ddd6fe; }
    .badge-pay-wallet { background-color: #fff7ed; color: #ea580c; border: 1px solid #fed7aa; }
    .badge-pay-online { background-color: #ecfeff; color: #0891b2; border: 1px solid #a5f3fc; }
</style>
@endpush

@section('content')
@php
    $exportExcelUrl = route('admin.reports.adc.export', array_merge(request()->query(), ['format' => 'xlsx']));
    $exportCsvUrl = route('admin.reports.adc.export', array_merge(request()->query(), ['format' => 'csv']));
@endphp

<div class="container-fluid p-0">

    <!-- Page Header with Action Buttons -->
    <x-admin.page-header title="Product Wise Sale Report (ADC)"
        subtitle="Detailed product-wise sales report from ADC Orders table. (Paid transactions only)"
        :actions="[
            [
                'type' => 'link',
                'label' => 'Export Excel (.xlsx)',
                'icon' => 'solar:file-download-bold-duotone',
                'url' => $exportExcelUrl,
                'btnClass' => 'btn-success fw-semibold shadow-sm',
            ],
            [
                'type' => 'link',
                'label' => 'Export CSV',
                'icon' => 'solar:document-text-bold-duotone',
                'url' => $exportCsvUrl,
                'btnClass' => 'btn-outline-success fw-semibold shadow-sm',
            ],
        ]" />

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <!-- 1. Total Paid Sales Amount -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm report-stat-card h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar-md bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center me-3 fs-2 flex-shrink-0" style="width: 52px; height: 52px;">
                        <iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <span class="text-muted small fw-medium d-block text-uppercase">Total Paid Sales</span>
                        <h4 class="mb-0 fw-bold text-slate-800">₹{{ number_format($totalSalesAmount, 2) }}</h4>
                        <span class="badge bg-success-subtle text-success fs-10 mt-1 py-0 px-1.5 rounded">Paid Only</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Total Items Quantity Sold -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm report-stat-card h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar-md bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center me-3 fs-2 flex-shrink-0" style="width: 52px; height: 52px;">
                        <iconify-icon icon="solar:box-bold-duotone"></iconify-icon>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <span class="text-muted small fw-medium d-block text-uppercase">Total Items Sold</span>
                        <h4 class="mb-0 fw-bold text-slate-800">{{ number_format($totalItemsCount, 2) }}</h4>
                        <span class="text-muted fs-11">Total Unit Quantity</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Total Paid Orders Count -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm report-stat-card h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar-md bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3 fs-2 flex-shrink-0" style="width: 52px; height: 52px;">
                        <iconify-icon icon="solar:bag-check-bold-duotone"></iconify-icon>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <span class="text-muted small fw-medium d-block text-uppercase">Total Invoices / Orders</span>
                        <h4 class="mb-0 fw-bold text-slate-800">{{ number_format($totalOrdersCount) }}</h4>
                        <span class="text-muted fs-11">Distinct ADC Orders</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Active Ordering ADCs -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm report-stat-card h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar-md bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center me-3 fs-2 flex-shrink-0" style="width: 52px; height: 52px;">
                        <iconify-icon icon="solar:shop-bold-duotone"></iconify-icon>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <span class="text-muted small fw-medium d-block text-uppercase">Ordering ADCs</span>
                        <h4 class="mb-0 fw-bold text-slate-800">{{ number_format($activeAdcsCount) }}</h4>
                        <span class="text-muted fs-11">Canteens with Paid Sales</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.reports.adc.index') }}" class="row g-2 align-items-end" id="reportFilterForm">
                <!-- Search -->
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small text-muted fw-semibold mb-1">Search Product / Invoice</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0">
                            <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0"
                            placeholder="Product, code, HSN, invoice, ADC..."
                            value="{{ request('search') }}">
                    </div>
                </div>

                <!-- ADC Canteen Filter -->
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small text-muted fw-semibold mb-1">Select ADC Canteen</label>
                    <select name="adc_id" class="form-select form-select-sm">
                        <option value="all">All ADC Canteens</option>
                        @foreach ($adcList as $adcUser)
                            @php
                                $aCode = $adcUser->adcProfile?->adc_code ?: ($adcUser->adcProfile?->code ?: 'ADC');
                                $aName = $adcUser->adcProfile?->company_name ?: ($adcUser->adcProfile?->trade_name ?: $adcUser->name);
                            @endphp
                            <option value="{{ $adcUser->id }}" {{ request('adc_id') == $adcUser->id ? 'selected' : '' }}>
                                {{ $aCode }} - {{ \Illuminate\Support\Str::limit($aName, 26) }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Payment Method -->
                <div class="col-lg-2 col-md-4">
                    <label class="form-label small text-muted fw-semibold mb-1">Payment Method</label>
                    <select name="payment_mode" class="form-select form-select-sm">
                        <option value="all" {{ request('payment_mode') == 'all' || !request('payment_mode') ? 'selected' : '' }}>All Methods</option>
                        <option value="wallet" {{ strtolower(request('payment_mode')) === 'wallet' ? 'selected' : '' }}>Wallet</option>
                        <option value="online" {{ strtolower(request('payment_mode')) === 'online' ? 'selected' : '' }}>Online</option>
                        <option value="cash" {{ strtolower(request('payment_mode')) === 'cash' ? 'selected' : '' }}>Cash</option>
                        <option value="credit" {{ strtolower(request('payment_mode')) === 'credit' ? 'selected' : '' }}>Credit</option>
                    </select>
                </div>

                <!-- Date Range -->
                <div class="col-lg-4 col-md-8">
                    <div class="row g-1">
                        <div class="col-6">
                            <label class="form-label small text-muted fw-semibold mb-1">Date From</label>
                            <input type="date" name="date_from" class="form-control form-control-sm" value="{{ request('date_from') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted fw-semibold mb-1">Date To</label>
                            <input type="date" name="date_to" class="form-control form-control-sm" value="{{ request('date_to') }}">
                        </div>
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="col-12 d-flex justify-content-end gap-2 pt-2 border-top">
                    <a href="{{ route('admin.reports.adc.index') }}" class="btn btn-sm btn-outline-secondary px-3">
                        Reset Filters
                    </a>
                    <button type="submit" class="btn btn-sm btn-primary px-4 fw-semibold d-inline-flex align-items-center gap-1 shadow-sm">
                        <iconify-icon icon="solar:filter-bold-duotone"></iconify-icon>
                        Filter Results
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-0 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <div class="avatar-xs bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center">
                    <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="fs-16"></iconify-icon>
                </div>
                <h5 class="card-title mb-0 fw-bold">ADC Product Wise Sales Records</h5>
                <span class="badge bg-slate-100 text-slate-700 border ms-1 fw-medium">{{ $items->total() }} Products Sold</span>
            </div>

            <!-- Per-Page Selector -->
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted small">Per Page:</span>
                <select class="form-select form-select-sm" style="width: auto;" onchange="location = this.value;">
                    @foreach ([15, 25, 50, 100] as $size)
                        <option value="{{ request()->fullUrlWithQuery(['per_page' => $size, 'page' => 1]) }}"
                            {{ request('per_page', 25) == $size ? 'selected' : '' }}>
                            {{ $size }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive" style="max-height: 680px; overflow-y: auto;">
                <table class="table table-hover table-striped mb-0 table-product-report">
                    <thead class="sticky-top shadow-sm">
                        <tr>
                            <th class="text-center" style="width: 50px;">S.No</th>
                            <th>Invoice Date</th>
                            <th>State</th>
                            <th>GST Number</th>
                            <th>ADC Code</th>
                            <th>Invoice No</th>
                            <th>Canteen Name</th>
                            <th>Product Name</th>
                            <th>Product Code</th>
                            <th>HSN Code</th>
                            <th>Brand Name</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">MRP</th>
                            <th class="text-end">CSP / Price</th>
                            <th class="text-center">Tax %</th>
                            <th class="text-end">Taxable Amt</th>
                            <th class="text-end">SGST</th>
                            <th class="text-end">CGST</th>
                            <th class="text-end">IGST</th>
                            <th class="text-end">Total Amount</th>
                            <th>Mfg Date</th>
                            <th>Exp Date</th>
                            <th class="text-center">Payment Mode</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($items as $idx => $row)
                            @php
                                $sNo = ($items->currentPage() - 1) * $items->perPage() + $idx + 1;
                                $payBadge = match ($row->payment_mode) {
                                    'wallet' => 'badge-pay-wallet',
                                    'online' => 'badge-pay-online',
                                    'cash' => 'badge-pay-cash',
                                    'upi' => 'badge-pay-upi',
                                    'card' => 'badge-pay-card',
                                    default => 'badge-pay-cash',
                                };
                            @endphp
                            <tr>
                                <!-- 1. S.No -->
                                <td class="text-center text-muted fw-semibold">{{ $sNo }}</td>

                                <!-- 2. Invoice Date -->
                                <td class="fw-medium text-slate-700">{{ $row->invoice_date ?: '—' }}</td>

                                <!-- 3. State -->
                                <td>
                                    <span class="badge bg-light text-slate-800 border px-1.5 py-1 fs-11">
                                        {{ $row->state }}
                                    </span>
                                </td>

                                <!-- 4. GST Number -->
                                <td class="font-monospace text-slate-700 fs-12 fw-semibold">
                                    {{ $row->gst_number }}
                                </td>

                                <!-- 5. ADC Code -->
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-1.5 py-1 fw-bold fs-11" title="{{ $row->depot_name }}">
                                        {{ $row->depot_code }}
                                    </span>
                                </td>

                                <!-- 6. Invoice No -->
                                <td class="fw-bold text-primary font-monospace">{{ $row->invoice_no }}</td>

                                <!-- 7. Canteen Name -->
                                <td>
                                    <span class="fw-semibold text-slate-800">{{ $row->customer_name }}</span>
                                </td>

                                <!-- 8. Product Name -->
                                <td style="max-width: 260px;" class="text-truncate" title="{{ $row->product_name }}">
                                    <span class="fw-semibold text-dark">{{ $row->product_name }}</span>
                                </td>

                                <!-- 9. Product Code -->
                                <td class="font-monospace text-muted fs-12">{{ $row->product_code }}</td>

                                <!-- 10. HSN Code -->
                                <td class="font-monospace text-slate-600 fs-12">{{ $row->hsn_code }}</td>

                                <!-- 11. Brand Name -->
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary px-1.5 py-0.5 fs-11">
                                        {{ $row->brand_name }}
                                    </span>
                                </td>

                                <!-- 12. Qty -->
                                <td class="text-center fw-bold text-slate-800">
                                    {{ rtrim(rtrim(number_format($row->qty, 3), '0'), '.') }}
                                </td>

                                <!-- 13. MRP -->
                                <td class="text-end text-muted">₹{{ number_format($row->mrp, 2) }}</td>

                                <!-- 14. CSP / Price -->
                                <td class="text-end fw-semibold text-slate-800">₹{{ number_format($row->csp, 2) }}</td>

                                <!-- 15. Tax Rate -->
                                <td class="text-center">
                                    <span class="badge bg-info-subtle text-info fs-11">
                                        {{ (float) $row->tax_rate }}%
                                    </span>
                                </td>

                                <!-- 16. Taxable Amount -->
                                <td class="text-end text-slate-700">₹{{ number_format($row->taxable_amount, 2) }}</td>

                                <!-- 17. SGST -->
                                <td class="text-end text-slate-600">₹{{ number_format($row->sgst, 2) }}</td>

                                <!-- 18. CGST -->
                                <td class="text-end text-slate-600">₹{{ number_format($row->cgst, 2) }}</td>

                                <!-- 19. IGST -->
                                <td class="text-end text-slate-600">₹{{ number_format($row->igst, 2) }}</td>

                                <!-- 20. Total Amount -->
                                <td class="text-end fw-bold text-success fs-13">₹{{ number_format($row->total_amount, 2) }}</td>

                                <!-- 21. Mfg Date -->
                                <td class="text-muted fs-11">{{ $row->mfg_date ?: '—' }}</td>

                                <!-- 22. Exp Date -->
                                <td class="text-muted fs-11">{{ $row->exp_date ?: '—' }}</td>

                                <!-- 23. Payment Mode -->
                                <td class="text-center">
                                    <span class="badge {{ $payBadge }} px-2 py-1 fs-11 text-uppercase fw-semibold">
                                        {{ $row->payment_mode }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="23" class="text-center py-5 text-muted">
                                    <div class="d-flex flex-column align-items-center">
                                        <iconify-icon icon="solar:folder-error-bold-duotone" class="fs-48 text-muted mb-2"></iconify-icon>
                                        <h6 class="fw-semibold">No Sales Records Found</h6>
                                        <p class="small mb-0">No paid ADC orders match the selected filters.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($items->hasPages())
            <div class="card-footer bg-white py-3 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="text-muted small">
                    Showing <strong>{{ $items->firstItem() ?? 0 }}</strong> to <strong>{{ $items->lastItem() ?? 0 }}</strong> of <strong>{{ number_format($items->total()) }}</strong> products sold
                </div>
                <div>
                    {{ $items->links() }}
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
