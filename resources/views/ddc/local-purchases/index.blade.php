@extends('layouts.ddc')

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
                            <a href="{{ route('ddc.reports.local-purchases.export', request()->all()) }}" class="btn btn-outline-success btn-sm fw-semibold">
                                <iconify-icon icon="solar:export-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Export CSV
                            </a>
                            <a href="{{ route('ddc.reports.local-purchases.print', request()->all()) }}" target="_blank" class="btn btn-outline-secondary btn-sm fw-semibold">
                                <iconify-icon icon="solar:printer-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Print
                            </a>
                            <a href="{{ route('ddc.local-purchases.create') }}" class="btn btn-primary btn-sm fw-semibold">
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
            <form action="{{ route('ddc.local-purchases.index') }}" method="GET" class="row g-2 align-items-center">
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
                        <a href="{{ route('ddc.local-purchases.index') }}" class="btn btn-outline-secondary btn-sm py-2 px-3"><iconify-icon icon="solar:restart-bold-duotone"></iconify-icon></a>
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
                                    <h6 class="fw-semibold text-slate-800 mb-0">{{ $purchase->supplier_name }}</h6>
                                    @if($purchase->supplier_mobile)
                                        <span class="text-muted fs-12">{{ $purchase->supplier_mobile }}</span>
                                    @endif
                                </td>
                                <td>
                                    @if($purchase->invoice_number)
                                        <span class="fw-semibold text-slate-700 d-block">{{ $purchase->invoice_number }}</span>
                                        @if($purchase->invoice_date)
                                            <span class="text-muted fs-11">{{ $purchase->invoice_date->format('d M Y') }}</span>
                                        @endif
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($product)
                                        <div class="fw-semibold text-slate-800">{{ $product->product_name }}</div>
                                        <span class="text-muted fs-11">Code: <code>{{ $product->local_product_code }}</code></span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($item)
                                        <span class="fw-bold text-slate-800">{{ $item->quantity }} {{ $product->unit_of_measure ?? 'Units' }}</span>
                                        <span class="text-muted fs-11 d-block">@ ₹{{ number_format($item->purchase_price, 2) }}/unit</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if($item)
                                        <span class="fw-semibold text-emerald-700">₹{{ number_format($item->landing_cost, 2) }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold text-slate-900 fs-14">₹{{ number_format($purchase->total_amount, 2) }}</span>
                                </td>
                                <td class="pe-4 text-end">
                                    <a href="{{ route('ddc.local-purchases.show', $purchase->id) }}" class="btn btn-sm btn-outline-primary px-3 py-1">
                                        <iconify-icon icon="solar:eye-bold-duotone" class="align-middle me-1"></iconify-icon> View PO
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="py-4">
                                        <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="fs-48 text-muted opacity-50 mb-2"></iconify-icon>
                                        <h5 class="fw-semibold text-slate-700">No Local Purchase History</h5>
                                        <p class="text-muted small mb-3">No stock in entries recorded yet.</p>
                                        <a href="{{ route('ddc.local-purchases.create') }}" class="btn btn-success btn-sm fw-semibold px-3 py-2">
                                            <iconify-icon icon="solar:add-circle-bold-duotone" class="align-middle me-1"></iconify-icon> Record First Stock In
                                        </a>
                                    </div>
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
</div>
@endsection
