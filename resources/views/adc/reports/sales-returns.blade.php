@extends('layouts.adc')

@section('title', 'Sale Return Report')
@section('page-title', 'Sale Return Report')

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
                            <p class="text-muted small mb-0">Detailed breakdown of customer sales return receipts, refund amounts, GST tax reversals, and original Order/Invoice numbers.</p>
                        </div>
                        <div class="col-md-6 text-md-end d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
                            <a href="{{ route('adc.reports.sales-returns.export-csv', request()->query()) }}"
                                class="btn btn-outline-success btn-sm fw-semibold d-inline-flex align-items-center gap-1">
                                <iconify-icon icon="solar:file-download-bold-duotone" class="fs-18"></iconify-icon>
                                Export CSV
                            </a>
                            <a href="{{ route('adc.reports.sales-returns.print', request()->query()) }}" target="_blank"
                                class="btn btn-primary btn-sm fw-semibold d-inline-flex align-items-center gap-1">
                                <iconify-icon icon="solar:printer-bold-duotone" class="fs-18"></iconify-icon>
                                Print Statement
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
                    <iconify-icon icon="solar:layers-bold-duotone" class="text-primary fs-18 me-1"></iconify-icon> Select Sale Return Sourcing Scope:
                </div>
                <div class="btn-group" role="group" aria-label="Return Scope">
                    <a href="{{ route('adc.reports.sales-returns', array_merge(request()->query(), ['sourcing' => 'all'])) }}"
                        class="btn btn-sm {{ $currSourcing === 'all' ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold px-3">
                        <iconify-icon icon="solar:widget-bold-duotone" class="align-middle me-1"></iconify-icon> All Sources (Combined)
                    </a>
                    <a href="{{ route('adc.reports.sales-returns', array_merge(request()->query(), ['sourcing' => 'msme'])) }}"
                        class="btn btn-sm {{ $currSourcing === 'msme' ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold px-3">
                        <iconify-icon icon="solar:buildings-bold-duotone" class="align-middle me-1"></iconify-icon> MSME Returns (Central)
                    </a>
                    <a href="{{ route('adc.reports.sales-returns', array_merge(request()->query(), ['sourcing' => 'regional'])) }}"
                        class="btn btn-sm {{ $currSourcing === 'regional' ? 'btn-success' : 'btn-outline-success' }} fw-semibold px-3">
                        <iconify-icon icon="solar:shop-bold-duotone" class="align-middle me-1"></iconify-icon> Regional Returns (Local)
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('adc.reports.sales-returns') }}" class="row g-2 align-items-end">
                <input type="hidden" name="sourcing" value="{{ $currSourcing }}">
                <div class="col-md-3">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">Start Date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">End Date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">Search Keywords</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Return No / Order No / Customer" value="{{ $filters['search'] ?? '' }}">
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                        <iconify-icon icon="solar:filter-bold-duotone" class="align-middle me-1"></iconify-icon> Filter
                    </button>
                    <a href="{{ route('adc.reports.sales-returns', ['sourcing' => $currSourcing]) }}" class="btn btn-light btn-sm" title="Reset Filters">
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
                                <a href="{{ route('adc.reports.sales-returns', array_merge(request()->query(), ['start_date' => now()->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')])) }}"
                                    class="btn {{ ($startDate === now()->format('Y-m-d') && $endDate === now()->format('Y-m-d')) ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold px-3">
                                    <iconify-icon icon="solar:sun-bold-duotone" class="align-middle me-1"></iconify-icon> Today (Daily)
                                </a>
                                <a href="{{ route('adc.reports.sales-returns', array_merge(request()->query(), ['start_date' => now()->startOfWeek()->format('Y-m-d'), 'end_date' => now()->endOfWeek()->format('Y-m-d')])) }}"
                                    class="btn {{ ($startDate === now()->startOfWeek()->format('Y-m-d') && $endDate === now()->endOfWeek()->format('Y-m-d')) ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold px-3">
                                    <iconify-icon icon="solar:calendar-mark-bold-duotone" class="align-middle me-1"></iconify-icon> This Week (Weekly)
                                </a>
                                <a href="{{ route('adc.reports.sales-returns', array_merge(request()->query(), ['start_date' => now()->startOfMonth()->format('Y-m-d'), 'end_date' => now()->startOfMonth()->format('Y-m-d')])) }}"
                                    class="btn {{ ($startDate === now()->startOfMonth()->format('Y-m-d') && $endDate === now()->startOfMonth()->format('Y-m-d')) ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold px-3">
                                    <iconify-icon icon="solar:calendar-bold-duotone" class="align-middle me-1"></iconify-icon> This Month (Monthly)
                                </a>
                                <a href="{{ route('adc.reports.sales-returns', array_merge(request()->query(), ['start_date' => now()->subMonth()->startOfMonth()->format('Y-m-d'), 'end_date' => now()->subMonth()->endOfMonth()->format('Y-m-d')])) }}"
                                    class="btn {{ ($startDate === now()->subMonth()->startOfMonth()->format('Y-m-d') && $endDate === now()->subMonth()->endOfMonth()->format('Y-m-d')) ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold px-3">
                                    <iconify-icon icon="solar:history-bold-duotone" class="align-middle me-1"></iconify-icon> Last Month
                                </a>
                                <a href="{{ route('adc.reports.sales-returns', array_merge(request()->query(), ['start_date' => now()->startOfYear()->format('Y-m-d'), 'end_date' => now()->startOfYear()->format('Y-m-d')])) }}"
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
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Total Return Receipts</div>
                        <div class="fs-20 fw-bold text-dark mt-1">{{ number_format($summaryStats['total_returns']) }}</div>
                        <div class="fs-11 text-muted mt-1">Processed Returns Count</div>
                    </div>
                    <div class="avatar-sm bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <iconify-icon icon="solar:restart-bold-duotone" class="fs-22"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Total Returned Items Qty</div>
                        <div class="fs-20 fw-bold text-info mt-1">{{ number_format($summaryStats['total_qty'], 2) }}</div>
                        <div class="fs-11 text-muted mt-1">Returned Items Count</div>
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
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Taxable Refund Subtotal</div>
                        <div class="fs-20 fw-bold text-warning-emphasis mt-1">₹{{ number_format($summaryStats['total_taxable'], 2) }}</div>
                        <div class="fs-11 text-muted mt-1">GST Reversal: ₹{{ number_format($summaryStats['total_tax'], 2) }}</div>
                    </div>
                    <div class="avatar-sm bg-warning-subtle text-warning-emphasis rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <iconify-icon icon="solar:bill-check-bold-duotone" class="fs-22"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Total Refund Paid</div>
                        <div class="fs-20 fw-bold text-danger mt-1">₹{{ number_format($summaryStats['total_refund'], 2) }}</div>
                        <div class="fs-11 text-muted mt-1">Net Refund Outflow</div>
                    </div>
                    <div class="avatar-sm bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <iconify-icon icon="solar:wallet-money-bold-duotone" class="fs-22"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Return Records Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3" style="width: 4%;">#</th>
                            <th style="width: 14%;">Return Number</th>
                            <th style="width: 12%;">Original Order No</th>
                            <th style="width: 12%;">Original Invoice No</th>
                            <th style="width: 12%;">Date & Time</th>
                            <th style="width: 10%;">Channel</th>
                            <th style="width: 9%;">Sourcing</th>
                            <th style="width: 13%;">Customer Name</th>
                            <th class="text-end pe-3" style="width: 14%;">Refund Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paginatedRecords as $idx => $row)
                            <tr>
                                <td class="ps-3 text-muted fs-12">{{ $paginatedRecords->firstItem() + $idx }}</td>
                                <td><code class="fw-bold text-danger fs-12">{{ $row['return_number'] }}</code></td>
                                <td><code class="fw-bold text-dark fs-12">{{ $row['original_order_no'] }}</code></td>
                                <td><code class="fw-bold text-primary fs-12">{{ $row['original_invoice_no'] }}</code></td>
                                <td class="fs-12 text-muted">{{ $row['date'] }}</td>
                                <td><span class="badge bg-secondary-subtle text-secondary px-2 py-1 fs-11">{{ $row['sales_channel'] }}</span></td>
                                <td>
                                    @if($row['sourcing_type'] === 'MSME')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-11 fw-bold">MSME</span>
                                    @elseif($row['sourcing_type'] === 'Regional')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 fs-11 fw-bold">Regional</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2 py-1 fs-11 fw-bold">Mixed</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark fs-12">{{ $row['customer_name'] }}</div>
                                    <div class="text-muted fs-11">{{ $row['customer_mobile'] }}</div>
                                </td>
                                <td class="text-end pe-3 fw-bold text-danger fs-13">
                                    ₹{{ number_format($row['total_return_amount'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4">
                                    <div class="text-muted">
                                        <iconify-icon icon="solar:restart-bold-duotone" class="fs-36 text-secondary opacity-50 mb-2"></iconify-icon>
                                        <h6 class="mb-1">No Sale Return Receipts Found</h6>
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
