@extends('layouts.adc')

@section('title', 'Product Wise Report')
@section('page-title', 'Product Wise Report')

@section('content')
    <!-- Header Card (DDC Reference Design) -->
    <div class="row mb-3 mt-0">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">Product Wise Sales Report</h4>
                            <p class="text-muted small mb-0">Itemized product performance, quantities sold vs returned, net revenue, and estimated profit margins.</p>
                        </div>
                        <div class="col-md-6 text-md-end d-flex align-items-center justify-content-md-end gap-2">
                            <a href="{{ route('adc.reports.product-wise.export', request()->all()) }}"
                                class="btn btn-outline-success btn-sm fw-semibold d-inline-flex align-items-center gap-1">
                                <iconify-icon icon="solar:file-download-bold-duotone" width="18" height="18" style="font-size: 18px !important; width: 18px !important; height: 18px !important;"></iconify-icon>
                                Export CSV
                            </a>
                            <a href="{{ route('adc.reports.product-wise.print', request()->all()) }}" target="_blank"
                                class="btn btn-primary btn-sm fw-semibold d-inline-flex align-items-center gap-1">
                                <iconify-icon icon="solar:printer-bold-duotone" width="18" height="18" style="font-size: 18px !important; width: 18px !important; height: 18px !important;"></iconify-icon>
                                Print Report
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Metrics Cards (DDC Reference Design) -->
    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Products Sold</div>
                        <div class="fs-20 fw-bold text-primary mt-1">{{ number_format($totalProductsSold) }}</div>
                        <div class="fs-11 text-muted mt-1">Distinct Product SKUs</div>
                    </div>
                    <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <iconify-icon icon="solar:box-minimalistic-bold-duotone" width="22" height="22" style="font-size: 22px !important; width: 22px !important; height: 22px !important;"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Total Units Sold</div>
                        <div class="fs-20 fw-bold text-purple-600 mt-1" style="color: #7c3aed;">{{ number_format($totalUnitsSold) }}</div>
                        <div class="fs-11 text-muted mt-1">Net Quantity Delivered</div>
                    </div>
                    <div class="avatar-sm bg-purple-subtle text-purple-600 rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: #f3e8ff; color: #7c3aed;">
                        <iconify-icon icon="solar:cart-large-bold-duotone" width="22" height="22" style="font-size: 22px !important; width: 22px !important; height: 22px !important;"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Product Revenue</div>
                        <div class="fs-20 fw-bold text-success mt-1">₹{{ number_format($totalProductSales, 2) }}</div>
                        <div class="fs-11 text-muted mt-1">Gross Product Revenue</div>
                    </div>
                    <div class="avatar-sm bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <iconify-icon icon="solar:dollar-minimalistic-bold-duotone" width="22" height="22" style="font-size: 22px !important; width: 22px !important; height: 22px !important;"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Est. Net Profit</div>
                        <div class="fs-20 fw-bold text-warning mt-1">₹{{ number_format($totalProductProfit, 2) }}</div>
                        <div class="fs-11 text-muted mt-1">Est. Profit Margin</div>
                    </div>
                    <div class="avatar-sm bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <iconify-icon icon="solar:graph-up-bold-duotone" width="22" height="22" style="font-size: 22px !important; width: 22px !important; height: 22px !important;"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section (DDC Reference Design) -->
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('adc.reports.product-wise') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">From Date</label>
                    <input type="date" name="from_date" class="form-control form-control-sm" value="{{ $fromDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">To Date</label>
                    <input type="date" name="to_date" class="form-control form-control-sm" value="{{ $toDate->format('Y-m-d') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">Search Keywords</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Product name, SKU, barcode..." value="{{ request('search') }}">
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                        <iconify-icon icon="solar:filter-bold-duotone" width="16" height="16" class="align-middle me-0.5"></iconify-icon> Filter
                    </button>
                    <a href="{{ route('adc.reports.product-wise') }}" class="btn btn-light btn-sm" title="Reset Filters">
                        <iconify-icon icon="solar:restart-bold-duotone" width="16" height="16" class="align-middle"></iconify-icon>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Data Table Card (DDC Reference Design) -->
    <div class="card border-0 shadow-sm rounded-3 overflow-hidden mb-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light fs-11 text-uppercase text-muted">
                    <tr>
                        <th class="ps-4">BARCODE / SKU</th>
                        <th>PRODUCT NAME</th>
                        <th>CATEGORY</th>
                        <th>QTY SOLD</th>
                        <th>RETURNED QTY</th>
                        <th>NET QTY SOLD</th>
                        <th>TOTAL REVENUE</th>
                        <th class="pe-4 text-end">EST. PROFIT</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($groupedProducts as $p)
                        <tr>
                            <td class="ps-4 font-monospace fw-bold fs-12 text-muted">{{ $p->barcode }}</td>
                            <td class="fw-bold text-slate-900 fs-13">{{ $p->product_name }}</td>
                            <td><span class="badge bg-light text-dark border fs-11 px-2.5 py-1 rounded-pill">{{ $p->category }}</span></td>
                            <td class="font-monospace fs-13">{{ number_format($p->total_qty) }}</td>
                            <td class="font-monospace fs-13 text-danger">{{ number_format($p->returned_qty) }}</td>
                            <td class="font-monospace fw-bold fs-13 text-primary">{{ number_format($p->net_qty) }}</td>
                            <td class="font-monospace fw-bold fs-13 text-success">₹{{ number_format($p->total_sales, 2) }}</td>
                            <td class="pe-4 text-end font-monospace fw-extrabold fs-13 text-warning">₹{{ number_format($p->total_profit, 2) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted fs-13">
                                No product sales recorded for the selected filter parameters.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
