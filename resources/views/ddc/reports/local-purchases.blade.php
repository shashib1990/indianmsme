@extends('layouts.ddc')

@section('title', 'Regional Purchase Report')
@section('page-title', 'Regional Purchase Report')

@section('content')
    <!-- Header Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">Regional
                                Purchase Report</h4>
                            <p class="text-muted small mb-0">Detailed report of all regional stock in records, purchase costs,
                                landing costs, margins, and available stock levels.</p>
                        </div>
                        <div class="col-md-6 text-md-end d-flex align-items-center justify-content-md-end gap-2">
                            <a href="{{ route('ddc.reports.local-purchases.export', request()->all()) }}"
                                class="btn btn-outline-success btn-sm fw-semibold d-inline-flex align-items-center gap-1">
                                <iconify-icon icon="solar:file-download-bold-duotone"
                                    class="fs-18"></iconify-icon>
                                Export Excel (CSV)
                            </a>
                            <a href="{{ route('ddc.reports.local-purchases.print', request()->all()) }}" target="_blank"
                                class="btn btn-primary btn-sm fw-semibold d-inline-flex align-items-center gap-1">
                                <iconify-icon icon="solar:printer-bold-duotone"
                                    class="fs-18"></iconify-icon>
                                Print / PDF
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-3">
        <div class="card-header bg-light border-0 py-3 rounded-top-4">
            <h6 class="fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2">
                <iconify-icon icon="solar:filter-bold-duotone" class="text-teal-700 fs-18"></iconify-icon>
                Report Filters
            </h6>
        </div>
        <div class="card-body p-3">
            <form action="{{ route('ddc.reports.local-purchases') }}" method="GET" class="row g-3">
                <!-- Date Range -->
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-slate-700 fs-12 mb-1">From Date</label>
                    <input type="date" name="start_date" class="form-control" value="{{ $filters['start_date'] ?? '' }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-slate-700 fs-12 mb-1">To Date</label>
                    <input type="date" name="end_date" class="form-control" value="{{ $filters['end_date'] ?? '' }}">
                </div>

                <!-- Supplier Filter -->
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-slate-700 fs-12 mb-1">Supplier</label>
                    <select name="supplier_name" class="form-select">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup }}" {{ ($filters['supplier_name'] ?? '') == $sup ? 'selected' : '' }}>
                                {{ $sup }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Product Filter -->
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-slate-700 fs-12 mb-1">Regional Product</label>
                    <select name="local_product_id" class="form-select">
                        <option value="">All Regional Products</option>
                        @foreach($localProducts as $lp)
                            <option value="{{ $lp->id }}" {{ ($filters['local_product_id'] ?? '') == $lp->id ? 'selected' : '' }}>
                                {{ $lp->product_name }} ({{ $lp->local_product_code }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Category Filter -->
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-slate-700 fs-12 mb-1">Category</label>
                    <select name="category_id" class="form-select">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ ($filters['category_id'] ?? '') == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Brand Filter -->
                <div class="col-md-3">
                    <label class="form-label fw-semibold text-slate-700 fs-12 mb-1">Brand</label>
                    <select name="brand_id" class="form-select">
                        <option value="">All Brands</option>
                        @foreach($brands as $brand)
                            <option value="{{ $brand->id }}" {{ ($filters['brand_id'] ?? '') == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-6 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-teal px-4 py-2 text-white fw-bold"
                        style="background-color: #0f766e; border-color: #0f766e;">
                        <iconify-icon icon="solar:magnifer-linear" class="align-middle me-1"></iconify-icon> Generate Report
                    </button>
                    @if(array_filter($filters))
                        <a href="{{ route('ddc.reports.local-purchases') }}" class="btn btn-outline-secondary px-3 py-2">
                            Reset Filters
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Report Data Table -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 fs-12">
                    <thead class="bg-light text-uppercase fw-bold text-muted border-bottom" style="font-size: 11px;">
                        <tr>
                            <th class="ps-3">Purchase No & Date</th>
                            <th>Supplier</th>
                            <th>Invoice No</th>
                            <th>Product Name</th>
                            <th>Qty</th>
                            <th>Unit Price (₹)</th>
                            <th>Landing Cost (₹)</th>
                            <th>MRP (₹)</th>
                            <th>CSP (₹)</th>
                            <th>MSP (₹)</th>
                            <th>GST %</th>
                            <th>Total Purchase (₹)</th>
                            <th>Current Stock</th>
                            <th class="pe-3">Source</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            @php
                                $purchase = $item->purchase;
                                $product = $item->localProduct;
                                $inventory = $item->inventory;
                                $totalCost = ($item->purchase_price * $item->quantity) + $item->freight_share + $item->loading_share + $item->other_share;
                            @endphp
                            <tr>
                                <td class="ps-3">
                                    <span class="fw-bold text-slate-800 d-block">{{ $purchase->purchase_no ?? '—' }}</span>
                                    <span
                                        class="text-muted fs-11">{{ $purchase->purchase_date ? $purchase->purchase_date->format('d M Y') : '—' }}</span>
                                </td>
                                <td>
                                    <span class="fw-semibold text-slate-800">{{ $purchase->supplier_name ?? '—' }}</span>
                                </td>
                                <td>{{ $purchase->invoice_number ?? '—' }}</td>
                                <td>
                                    <strong class="text-slate-900 d-block">{{ $product->product_name ?? '—' }}</strong>
                                    <span class="text-muted fs-11">{{ $product->local_product_code ?? '—' }}</span>
                                </td>
                                <td class="fw-bold text-slate-900">{{ $item->quantity }}</td>
                                <td>₹{{ number_format($item->purchase_price, 2) }}</td>
                                <td><strong class="text-emerald-700">₹{{ number_format($item->landing_cost, 2) }}</strong></td>
                                <td>₹{{ number_format($item->mrp, 2) }}</td>
                                <td>₹{{ number_format($item->csp, 2) }}</td>
                                <td>₹{{ number_format($item->msp, 2) }}</td>
                                <td>{{ $item->gst_rate }}%</td>
                                <td><strong class="text-slate-900">₹{{ number_format($totalCost, 2) }}</strong></td>
                                <td>
                                    <span
                                        class="badge bg-secondary-subtle text-secondary px-2 py-1">{{ $inventory->quantity ?? 0 }}
                                        {{ $product->unit_of_measure ?? '' }}</span>
                                </td>
                                <td class="pe-3">
                                    <span class="badge bg-success-subtle text-success fw-semibold px-2 py-1">REGIONAL</span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="text-center py-5 text-muted">
                                    No regional purchase records found for the selected filter criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($items->hasPages())
                <div class="p-3 border-top">
                    {{ $items->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection