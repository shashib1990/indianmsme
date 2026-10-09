@extends('layouts.ddc')

@section('title', 'Product Purchase Report')
@section('page-title', 'Product Purchase Report')

@section('content')
    <!-- Header Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">Product Purchase Report</h4>
                            <p class="text-muted small mb-0">Detailed itemized report of all purchased products, HSN codes, supplier sources, unit costs, and landing costs.</p>
                        </div>
                        <div class="col-md-6 text-md-end d-flex align-items-center justify-content-md-end gap-2">
                            <a href="{{ route('ddc.reports.product-purchases.export-csv', request()->query()) }}"
                                class="btn btn-outline-success btn-sm fw-semibold d-inline-flex align-items-center gap-1">
                                <iconify-icon icon="solar:file-download-bold-duotone" class="fs-18"></iconify-icon>
                                Export CSV
                            </a>
                            <a href="{{ route('ddc.reports.product-purchases.print', request()->query()) }}" target="_blank"
                                class="btn btn-primary btn-sm fw-semibold d-inline-flex align-items-center gap-1">
                                <iconify-icon icon="solar:printer-bold-duotone" class="fs-18"></iconify-icon>
                                Print Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Total Item Entries</div>
                        <div class="fs-20 fw-bold text-dark mt-1">{{ number_format($summaryStats['total_count']) }}</div>
                    </div>
                    <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <iconify-icon icon="solar:box-bold-duotone" class="fs-22"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Total Quantity Purchased</div>
                        <div class="fs-20 fw-bold text-dark mt-1">{{ number_format($summaryStats['total_items_qty']) }}</div>
                    </div>
                    <div class="avatar-sm bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <iconify-icon icon="solar:bag-3-bold-duotone" class="fs-22"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Central Vendor PO Cost</div>
                        <div class="fs-20 fw-bold text-success mt-1">₹{{ number_format($summaryStats['total_central_amount'], 2) }}</div>
                    </div>
                    <div class="avatar-sm bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <iconify-icon icon="solar:buildings-bold-duotone" class="fs-22"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Total Product Cost</div>
                        <div class="fs-20 fw-bold text-primary mt-1">₹{{ number_format($summaryStats['total_amount'], 2) }}</div>
                    </div>
                    <div class="avatar-sm bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <iconify-icon icon="solar:wallet-money-bold-duotone" class="fs-22"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('ddc.reports.product-purchases') }}" class="row g-2 align-items-end">
                <div class="col-md-2">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">Start Date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $filters['start_date'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">End Date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $filters['end_date'] ?? '' }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">Sourcing Type</label>
                    <select name="source" class="form-select form-select-sm">
                        <option value="all" {{ ($filters['source'] ?? 'all') == 'all' ? 'selected' : '' }}>All Sources</option>
                        <option value="central" {{ ($filters['source'] ?? '') == 'central' ? 'selected' : '' }}>Central Vendor PO</option>
                        <option value="local" {{ ($filters['source'] ?? '') == 'local' ? 'selected' : '' }}>Regional Purchase</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">Supplier / Vendor</label>
                    <select name="supplier" class="form-select form-select-sm tom-select">
                        <option value="">All Suppliers</option>
                        @foreach($suppliers as $sup)
                            <option value="{{ $sup }}" {{ ($filters['supplier'] ?? '') == $sup ? 'selected' : '' }}>{{ $sup }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">Search Keywords</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Product / HSN / PO / Invoice" value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                        <iconify-icon icon="solar:filter-bold-duotone" class="align-middle me-1"></iconify-icon> Filter
                    </button>
                    <a href="{{ route('ddc.reports.product-purchases') }}" class="btn btn-light btn-sm" title="Reset Filters">
                        <iconify-icon icon="solar:restart-bold-duotone" class="align-middle"></iconify-icon>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Product Purchase Records Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3" style="width: 4%;">#</th>
                            <th style="width: 20%;">Product Details</th>
                            <th style="width: 9%;">HSN Code</th>
                            <th style="width: 12%;">Source Type</th>
                            <th style="width: 14%;">Purchase / PO No</th>
                            <th style="width: 14%;">Supplier / Vendor</th>
                            <th class="text-center" style="width: 8%;">Qty</th>
                            <th class="text-end" style="width: 9%;">Unit Cost</th>
                            <th class="text-end" style="width: 10%;">Total Amount</th>
                            <th class="text-center" style="width: 6%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paginatedRecords as $idx => $row)
                            <tr>
                                <td class="ps-3 text-muted fs-12">{{ $paginatedRecords->firstItem() + $idx }}</td>
                                <td>
                                    <strong class="text-dark d-block fs-13">{{ $row['product_name'] }}</strong>
                                    <span class="text-muted fs-11">Code: <code>{{ $row['product_code'] }}</code></span>
                                </td>
                                <td>
                                    <code class="text-primary fw-semibold fs-12">{{ $row['hsn_code'] }}</code>
                                </td>
                                <td>
                                    @if($row['type'] === 'central')
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fs-11">
                                            <iconify-icon icon="solar:buildings-bold-duotone" class="align-middle me-1"></iconify-icon> Central PO
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-11">
                                            <iconify-icon icon="solar:shop-bold-duotone" class="align-middle me-1"></iconify-icon> Regional Purchase
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <code class="fw-bold text-dark fs-12">{{ $row['reference_no'] }}</code>
                                    <div class="text-muted fs-11">{{ $row['date'] }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark fs-13">{{ $row['supplier_name'] }}</div>
                                    @if($row['invoice_no'] !== '—' && $row['invoice_no'] !== $row['reference_no'])
                                        <div class="text-muted fs-11">Inv: <code>{{ $row['invoice_no'] }}</code></div>
                                    @endif
                                </td>
                                <td class="text-center fw-bold text-dark fs-13">
                                    {{ number_format($row['quantity']) }} <span class="fs-11 text-muted font-normal">{{ $row['unit_of_measure'] }}</span>
                                </td>
                                <td class="text-end fs-13">
                                    <div class="fw-semibold text-dark">₹{{ number_format($row['unit_price'], 2) }}</div>
                                    @if($row['landing_cost'] > 0 && $row['landing_cost'] != $row['unit_price'])
                                        <div class="text-muted fs-11">Land: ₹{{ number_format($row['landing_cost'], 2) }}</div>
                                    @endif
                                </td>
                                <td class="text-end fw-bold text-primary fs-13">
                                    ₹{{ number_format($row['total_amount'], 2) }}
                                </td>
                                <td class="text-center">
                                    <a href="{{ $row['view_url'] }}" target="_blank" class="btn btn-xs btn-outline-secondary" title="View Details">
                                        <iconify-icon icon="solar:eye-bold-duotone" class="align-middle"></iconify-icon>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4">
                                    <div class="text-muted">
                                        <iconify-icon icon="solar:box-bold-duotone" class="fs-36 text-secondary opacity-50 mb-2"></iconify-icon>
                                        <h6 class="mb-1">No Product Purchase Records Found</h6>
                                        <p class="fs-12 mb-0">Try adjusting your filters or date range.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($paginatedRecords->hasPages())
            <div class="card-footer bg-white border-top py-2 px-3">
                {{ $paginatedRecords->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof TomSelect !== 'undefined') {
            document.querySelectorAll('.tom-select').forEach(function(el) {
                new TomSelect(el, { create: false, allowEmptyOption: true });
            });
        }
    });
</script>
@endpush
