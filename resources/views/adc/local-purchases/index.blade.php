@extends('layouts.adc')

@section('title', 'Local Purchase History')
@section('page-title', 'Local Purchase History')

@section('content')
    <!-- Header Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                Regional Stock In History
                            </h4>
                            <p class="text-muted small mb-0">View all Stock In entries, supplier invoices, quantities, expenses, and purchase amounts.</p>
                        </div>
                        <div class="col-md-6 text-md-end d-flex gap-2 justify-content-md-end align-items-center flex-wrap">
                            <a href="{{ route('adc.reports.local-purchases.export', request()->all()) }}" class="btn btn-outline-success btn-sm fw-semibold">
                                <iconify-icon icon="solar:export-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Export CSV
                            </a>
                            <a href="{{ route('adc.reports.local-purchases.print', request()->all()) }}" target="_blank" class="btn btn-outline-secondary btn-sm fw-semibold">
                                <iconify-icon icon="solar:printer-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Print
                            </a>
                            <a href="{{ route('adc.local-purchases.create') }}" class="btn btn-primary btn-sm fw-semibold">
                                <iconify-icon icon="solar:add-circle-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> New Stock In
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-20 text-success"></iconify-icon>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filter & Search Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('adc.local-purchases.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><iconify-icon icon="solar:magnifer-linear"></iconify-icon></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0 ps-0" placeholder="Search by Purchase No, Supplier, Invoice..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <input type="date" name="start_date" class="form-control bg-light" value="{{ $startDate }}" placeholder="From Date">
                </div>
                <div class="col-md-3">
                    <input type="date" name="end_date" class="form-control bg-light" value="{{ $endDate }}" placeholder="To Date">
                </div>
                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm fw-semibold w-100 py-2">Filter</button>
                    @if($search || $startDate || $endDate)
                        <a href="{{ route('adc.local-purchases.index') }}" class="btn btn-outline-secondary btn-sm py-2 px-3"><iconify-icon icon="solar:restart-bold-duotone"></iconify-icon></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Purchases Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase fs-11 text-muted fw-bold border-bottom">
                        <tr>
                            <th class="ps-4">Purchase No & Date</th>
                            <th>Supplier Details</th>
                            <th>Invoice Info</th>
                            <th>Product Stocked In</th>
                            <th class="text-center">Qty & Unit Price</th>
                            <th class="text-end">Landing Cost</th>
                            <th class="text-end">Total Amount</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody class="fs-13">
                        @forelse($purchases as $purchase)
                            @php
                                $item = $purchase->items->first();
                                $product = $item?->localProduct;
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1 fs-12 d-block mb-1" style="width: fit-content;">
                                        {{ $purchase->purchase_no }}
                                    </span>
                                    <span class="text-muted fs-12"><iconify-icon icon="solar:calendar-linear" class="align-middle me-1"></iconify-icon>{{ $purchase->purchase_date->format('d M Y') }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-slate-800 d-block">{{ $purchase->supplier_name }}</span>
                                    <span class="text-muted fs-11">{{ $purchase->supplier_mobile ?? 'No Mobile' }}</span>
                                </td>
                                <td>
                                    <span class="fw-medium text-slate-700 d-block">Inv: {{ $purchase->invoice_number ?? 'N/A' }}</span>
                                    <span class="text-muted fs-11">{{ $purchase->invoice_date ? $purchase->invoice_date->format('d M Y') : '—' }}</span>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-2 bg-emerald-50 text-emerald-700 d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; background: #ecfdf5; color: #047857;">
                                            <iconify-icon icon="solar:box-bold-duotone" class="fs-18"></iconify-icon>
                                        </div>
                                        <div>
                                            <span class="fw-semibold text-slate-800 d-block">{{ $product->product_name ?? 'Local Product' }}</span>
                                            <span class="text-muted fs-11">Code: <code>{{ $product->local_product_code ?? '—' }}</code></span>
                                        </div>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle text-secondary px-2 py-1 fs-12 fw-bold mb-1">{{ (float)($item->quantity ?? 0) }} {{ $product->unit_of_measure ?? 'Piece' }}</span>
                                    <span class="text-muted fs-11 d-block">@ ₹{{ number_format($item->purchase_price ?? 0, 2) }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold text-slate-800">₹{{ number_format($item->landing_cost ?? 0, 2) }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold text-primary fs-14">₹{{ number_format($purchase->total_amount, 2) }}</span>
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('adc.local-purchases.show', $purchase->id) }}" class="btn btn-sm btn-outline-primary px-2.5 py-1">
                                        <iconify-icon icon="solar:eye-bold-duotone" class="align-middle me-1"></iconify-icon> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="fs-48 text-muted opacity-50 mb-2"></iconify-icon>
                                    <h5 class="fw-semibold text-slate-700">No Local Purchases Recorded</h5>
                                    <p class="text-muted small mb-3">Record your first Stock-In / Inward Purchase invoice.</p>
                                    <a href="{{ route('adc.local-purchases.create') }}" class="btn btn-success btn-sm fw-semibold px-3 py-2">
                                        <iconify-icon icon="solar:add-circle-bold-duotone" class="align-middle me-1"></iconify-icon> New Stock In
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($purchases->hasPages())
                <div class="p-3 border-top">
                    {{ $purchases->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
