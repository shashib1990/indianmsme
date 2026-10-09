@extends('layouts.adc')

@section('title', 'Local Purchases Report')
@section('page-title', 'Local Purchases Report')

@section('content')
    <!-- Header Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-8">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                Local Purchases Report
                            </h4>
                            <p class="text-muted small mb-0">Detailed analytical report of all regional inward purchases, supplier metrics, and landed inventory costs.</p>
                        </div>
                        <div class="col-md-4 text-md-end d-flex gap-2 justify-content-md-end">
                            <a href="{{ route('adc.reports.local-purchases.export', request()->all()) }}" class="btn btn-outline-success btn-sm fw-semibold">
                                <iconify-icon icon="solar:export-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Export CSV
                            </a>
                            <a href="{{ route('adc.reports.local-purchases.print', request()->all()) }}" target="_blank" class="btn btn-outline-secondary btn-sm fw-semibold">
                                <iconify-icon icon="solar:printer-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Print Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Stats -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-lg bg-primary-subtle rounded-4 p-3 text-primary d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <iconify-icon icon="solar:document-bold-duotone" class="fs-28"></iconify-icon>
                    </div>
                    <div>
                        <span class="text-muted fs-12 d-block fw-semibold">Total Inward Invoices</span>
                        <h3 class="fw-bold text-slate-800 mb-0">{{ $totalPurchasesCount }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-lg bg-success-subtle rounded-4 p-3 text-success d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <iconify-icon icon="solar:wallet-money-bold-duotone" class="fs-28"></iconify-icon>
                    </div>
                    <div>
                        <span class="text-muted fs-12 d-block fw-semibold">Total Purchased Value</span>
                        <h3 class="fw-bold text-success mb-0">₹{{ number_format($totalAmountSum, 2) }}</h3>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-lg bg-warning-subtle rounded-4 p-3 text-warning d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <iconify-icon icon="solar:box-bold-duotone" class="fs-28"></iconify-icon>
                    </div>
                    <div>
                        <span class="text-muted fs-12 d-block fw-semibold">Total Units Stocked In</span>
                        <h3 class="fw-bold text-slate-800 mb-0">{{ number_format($totalItemsCount) }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('adc.reports.local-purchases') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <input type="text" name="search" class="form-control bg-light" placeholder="Search by Purchase No, Supplier..." value="{{ $search }}">
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
                        <a href="{{ route('adc.reports.local-purchases') }}" class="btn btn-outline-secondary btn-sm py-2 px-3"><iconify-icon icon="solar:restart-bold-duotone"></iconify-icon></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Report Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase fs-11 text-muted fw-bold border-bottom">
                        <tr>
                            <th class="ps-4">Purchase No & Date</th>
                            <th>Supplier</th>
                            <th>Invoice Number</th>
                            <th>Items Count</th>
                            <th class="text-end">Total Amount</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody class="fs-13">
                        @forelse($purchases as $p)
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-primary d-block">{{ $p->purchase_no }}</span>
                                    <span class="text-muted fs-11">{{ $p->purchase_date->format('d M Y') }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-slate-800 d-block">{{ $p->supplier_name }}</span>
                                    <span class="text-muted fs-11">{{ $p->supplier_mobile ?? 'No Mobile' }}</span>
                                </td>
                                <td>
                                    <span class="text-slate-700">{{ $p->invoice_number ?: 'N/A' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1 fw-semibold">{{ $p->items->count() }} Item(s)</span>
                                </td>
                                <td class="text-end fw-bold text-slate-800">
                                    ₹{{ number_format($p->total_amount, 2) }}
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('adc.local-purchases.show', $p->id) }}" class="btn btn-sm btn-outline-primary px-2.5 py-1">
                                        View Invoice
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5">
                                    <h5 class="fw-semibold text-slate-700">No Purchases Found</h5>
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
