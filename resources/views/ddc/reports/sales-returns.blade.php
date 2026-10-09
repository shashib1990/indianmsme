@extends('layouts.ddc')

@section('title', 'Sale Return Report')
@section('page-title', 'Sale Return Report')

@push('styles')
    <style>
        .btn-icon.rounded-circle {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }
    </style>
@endpush

@section('content')
    @php
        $currSourcing = strtolower($filters['sourcing'] ?? 'all');
        $startDate = $filters['start_date'] ?? \Carbon\Carbon::now()->startOfMonth()->format('Y-m-d');
        $endDate = $filters['end_date'] ?? \Carbon\Carbon::now()->endOfMonth()->format('Y-m-d');
    @endphp

    <!-- Header Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                Sale Return Report
                                @if($currSourcing === 'msme')
                                    <span class="badge bg-primary-subtle text-primary fs-12 px-2.5 py-1 rounded-pill ms-2">MSME Central Only</span>
                                @elseif($currSourcing === 'regional')
                                    <span class="badge bg-success-subtle text-success fs-12 px-2.5 py-1 rounded-pill ms-2">Regional Local Only</span>
                                @else
                                    <span class="badge bg-dark-subtle text-dark fs-12 px-2.5 py-1 rounded-pill ms-2">Combined All Sources</span>
                                @endif
                            </h4>
                            <p class="text-muted small mb-0">Detailed view of processed product returns, refund modes, tax reversals, and refund totals.</p>
                        </div>
                        <div class="col-md-6 text-md-end d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
                            <a href="{{ route('ddc.reports.sales-returns.export-csv', request()->query()) }}"
                                class="btn btn-outline-success btn-sm fw-semibold d-inline-flex align-items-center gap-1">
                                <iconify-icon icon="solar:file-download-bold-duotone" class="fs-18"></iconify-icon>
                                Export CSV
                            </a>
                            <a href="{{ route('ddc.reports.sales-returns.print', request()->query()) }}" target="_blank"
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

    <!-- Sourcing Scope Tabs & Filters Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body p-3">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3 pb-3 border-bottom">
                <div class="fw-bold text-dark fs-13 d-flex align-items-center gap-1">
                    <iconify-icon icon="solar:layers-bold-duotone" class="text-primary fs-18 me-1"></iconify-icon> Select Sales Return Sourcing Scope:
                </div>
                <div class="btn-group" role="group" aria-label="Returns Scope">
                    <a href="{{ route('ddc.reports.sales-returns', array_merge(request()->query(), ['sourcing' => 'all'])) }}"
                        class="btn btn-sm {{ $currSourcing === 'all' ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold px-3">
                        <iconify-icon icon="solar:widget-bold-duotone" class="align-middle me-1"></iconify-icon> All Sources (Combined)
                    </a>
                    <a href="{{ route('ddc.reports.sales-returns', array_merge(request()->query(), ['sourcing' => 'msme'])) }}"
                        class="btn btn-sm {{ $currSourcing === 'msme' ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold px-3">
                        <iconify-icon icon="solar:buildings-bold-duotone" class="align-middle me-1"></iconify-icon> MSME Returns (Central)
                    </a>
                    <a href="{{ route('ddc.reports.sales-returns', array_merge(request()->query(), ['sourcing' => 'regional'])) }}"
                        class="btn btn-sm {{ $currSourcing === 'regional' ? 'btn-success' : 'btn-outline-success' }} fw-semibold px-3">
                        <iconify-icon icon="solar:shop-bold-duotone" class="align-middle me-1"></iconify-icon> Regional Returns (Local)
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('ddc.reports.sales-returns') }}" class="row g-2 align-items-end">
                <input type="hidden" name="sourcing" value="{{ $currSourcing }}">
                <div class="col-md-2">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">Start Date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">End Date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">Refund Mode</label>
                    <select name="refund_mode" class="form-select form-select-sm">
                        <option value="all" {{ ($filters['refund_mode'] ?? 'all') == 'all' ? 'selected' : '' }}>All Refund Modes</option>
                        <option value="CASH" {{ ($filters['refund_mode'] ?? '') == 'CASH' ? 'selected' : '' }}>CASH</option>
                        <option value="UPI" {{ ($filters['refund_mode'] ?? '') == 'UPI' ? 'selected' : '' }}>UPI</option>
                        <option value="CARD" {{ ($filters['refund_mode'] ?? '') == 'CARD' ? 'selected' : '' }}>CARD</option>
                        <option value="WALLET" {{ ($filters['refund_mode'] ?? '') == 'WALLET' ? 'selected' : '' }}>WALLET</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">Search Keywords</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Return No / Order No / Customer" value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                        <iconify-icon icon="solar:filter-bold-duotone" class="align-middle me-1"></iconify-icon> Filter
                    </button>
                    <a href="{{ route('ddc.reports.sales-returns', ['sourcing' => $currSourcing]) }}" class="btn btn-light btn-sm" title="Reset Filters">
                        <iconify-icon icon="solar:restart-bold-duotone" class="align-middle"></iconify-icon>
                    </a>
                </div>

                <div class="col-12 mt-3 pt-3 border-top">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2.5">
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="fs-12 fw-bold text-dark d-flex align-items-center gap-1">
                                <iconify-icon icon="solar:clock-circle-bold-duotone" class="text-primary fs-16 me-0.5"></iconify-icon> Quick Timeframe:
                            </span>
                            <div class="btn-group btn-group-sm" role="group" aria-label="Quick Timeframe Filter">
                                <a href="{{ route('ddc.reports.sales-returns', array_merge(request()->query(), ['start_date' => now()->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')])) }}"
                                    class="btn {{ ($startDate === now()->format('Y-m-d') && $endDate === now()->format('Y-m-d')) ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold px-3">
                                    <iconify-icon icon="solar:sun-bold-duotone" class="align-middle me-1"></iconify-icon> Today (Daily)
                                </a>
                                <a href="{{ route('ddc.reports.sales-returns', array_merge(request()->query(), ['start_date' => now()->startOfWeek()->format('Y-m-d'), 'end_date' => now()->endOfWeek()->format('Y-m-d')])) }}"
                                    class="btn {{ ($startDate === now()->startOfWeek()->format('Y-m-d') && $endDate === now()->endOfWeek()->format('Y-m-d')) ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold px-3">
                                    <iconify-icon icon="solar:calendar-mark-bold-duotone" class="align-middle me-1"></iconify-icon> This Week (Weekly)
                                </a>
                                <a href="{{ route('ddc.reports.sales-returns', array_merge(request()->query(), ['start_date' => now()->startOfMonth()->format('Y-m-d'), 'end_date' => now()->endOfMonth()->format('Y-m-d')])) }}"
                                    class="btn {{ ($startDate === now()->startOfMonth()->format('Y-m-d') && $endDate === now()->endOfMonth()->format('Y-m-d')) ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold px-3">
                                    <iconify-icon icon="solar:calendar-bold-duotone" class="align-middle me-1"></iconify-icon> This Month (Monthly)
                                </a>
                                <a href="{{ route('ddc.reports.sales-returns', array_merge(request()->query(), ['start_date' => now()->subMonth()->startOfMonth()->format('Y-m-d'), 'end_date' => now()->subMonth()->endOfMonth()->format('Y-m-d')])) }}"
                                    class="btn {{ ($startDate === now()->subMonth()->startOfMonth()->format('Y-m-d') && $endDate === now()->subMonth()->endOfMonth()->format('Y-m-d')) ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold px-3">
                                    <iconify-icon icon="solar:history-bold-duotone" class="align-middle me-1"></iconify-icon> Last Month
                                </a>
                                <a href="{{ route('ddc.reports.sales-returns', array_merge(request()->query(), ['start_date' => now()->startOfYear()->format('Y-m-d'), 'end_date' => now()->startOfYear()->format('Y-m-d')])) }}"
                                    class="btn {{ ($startDate === now()->startOfYear()->format('Y-m-d') && $endDate === now()->startOfYear()->format('Y-m-d')) ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold px-3">
                                    <iconify-icon icon="solar:chart-2-bold-duotone" class="align-middle me-1"></iconify-icon> This Year
                                </a>
                            </div>
                        </div>
                        <div class="badge bg-light text-dark border px-3 py-2 fs-11.5 fw-medium shadow-xs d-inline-flex align-items-center gap-1">
                            <iconify-icon icon="solar:calendar-search-bold-duotone" class="text-primary fs-14"></iconify-icon>
                            Active Range: <strong class="text-primary font-monospace">{{ \Carbon\Carbon::parse($startDate)->format('d M Y') }}</strong> &ndash; <strong class="text-primary font-monospace">{{ \Carbon\Carbon::parse($endDate)->format('d M Y') }}</strong>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Summary Metrics Cards -->
    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Total Return Orders</div>
                        <div class="fs-20 fw-bold text-dark mt-1">{{ number_format($summaryStats['total_count']) }}</div>
                        <div class="fs-11 text-muted mt-1">Processed Returns</div>
                    </div>
                    <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <iconify-icon icon="solar:restart-bold-duotone" class="fs-22"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Returned Items Quantity</div>
                        <div class="fs-20 fw-bold text-info mt-1">{{ number_format($summaryStats['total_returned_qty'], 2) }}</div>
                        <div class="fs-11 text-muted mt-1">Total Units Returned</div>
                    </div>
                    <div class="avatar-sm bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <iconify-icon icon="solar:box-bold-duotone" class="fs-22"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Tax Reversal (GST)</div>
                        <div class="fs-20 fw-bold text-warning-emphasis mt-1">₹{{ number_format($summaryStats['tax_reversal'], 2) }}</div>
                        <div class="fs-11 text-muted mt-1">GST Rebuilt into Stock</div>
                    </div>
                    <div class="avatar-sm bg-warning-subtle text-warning-emphasis rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <iconify-icon icon="solar:bill-cross-bold-duotone" class="fs-22"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Total Refund Amount</div>
                        <div class="fs-20 fw-bold text-danger mt-1">₹{{ number_format($summaryStats['total_refund'], 2) }}</div>
                        <div class="fs-11 text-muted mt-1">Refunded to Customers</div>
                    </div>
                    <div class="avatar-sm bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <iconify-icon icon="solar:card-send-bold-duotone" class="fs-22"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Returns Records Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="fs-11 text-muted text-uppercase">
                            <th class="ps-3" style="width: 130px;">Return No</th>
                            <th style="width: 160px;">Original Order / Invoice</th>
                            <th>Date & Time</th>
                            <th>Sourcing</th>
                            <th>Customer Details</th>
                            <th class="text-center">Returned Qty</th>
                            <th class="text-end">Refund Amount</th>
                            <th class="text-center">Refund Mode</th>
                            <th class="text-center" style="width: 120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paginatedRecords as $ret)
                            <tr>
                                <td class="ps-3">
                                    <code class="fw-bold text-danger fs-12">{{ $ret['return_number'] }}</code>
                                    <div class="mt-0.5"><span class="badge bg-light text-dark border fs-10">{{ $ret['sales_channel'] }}</span></div>
                                </td>
                                <td>
                                    <div><span class="text-muted fs-11 text-uppercase fw-semibold me-1">Ord:</span> <code class="fw-bold text-dark fs-12">{{ $ret['original_order_no'] }}</code></div>
                                    <div class="mt-0.5"><span class="text-muted fs-11 text-uppercase fw-semibold me-1">Inv:</span> <code class="fw-bold text-primary fs-12">{{ $ret['original_invoice_no'] }}</code></div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-slate-800 fs-12">{{ $ret['date'] }}</div>
                                </td>
                                <td>
                                    @if($ret['sourcing_type'] === 'MSME')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-11 fw-bold">
                                            <iconify-icon icon="solar:verified-check-bold-duotone" class="align-middle me-1"></iconify-icon> MSME
                                        </span>
                                    @elseif($ret['sourcing_type'] === 'Regional')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 fs-11 fw-bold">
                                            <iconify-icon icon="solar:shop-2-bold-duotone" class="align-middle me-1"></iconify-icon> Regional
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 fs-11 fw-bold">
                                            <iconify-icon icon="solar:layers-minimalistic-bold-duotone" class="align-middle me-1"></iconify-icon> Mixed
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-slate-800 fs-13">{{ $ret['customer_name'] }}</div>
                                    <div class="text-muted fs-11">{{ $ret['customer_mobile'] }}</div>
                                </td>
                                <td class="text-center fw-bold fs-13">
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fs-11">
                                        {{ number_format($ret['total_qty'], 2) }} Qty
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-danger fs-14">
                                    ₹{{ number_format($ret['total_return_amount'], 2) }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-dark-subtle text-dark px-2 py-1 fs-11">
                                        {{ $ret['refund_mode'] }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex align-items-center justify-content-center gap-1">
                                        <a href="{{ $ret['print_url'] }}" target="_blank" class="btn btn-sm btn-outline-danger btn-icon rounded-circle" data-bs-toggle="tooltip" title="Print Return Receipt">
                                            <iconify-icon icon="solar:printer-bold-duotone" class="fs-16"></iconify-icon>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted fs-13">
                                    <iconify-icon icon="solar:restart-square-broken" class="fs-36 text-muted mb-2 d-block mx-auto"></iconify-icon>
                                    No sales returns records found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($paginatedRecords->hasPages())
            <div class="card-footer bg-white border-top p-2.5">
                {{ $paginatedRecords->links() }}
            </div>
        @endif
    </div>
@endsection
