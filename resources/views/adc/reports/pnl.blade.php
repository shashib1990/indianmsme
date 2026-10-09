@extends('layouts.adc')

@section('title', 'Profit & Loss (P&L) Report')
@section('page-title', 'Profit & Loss (P&L) Report')

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
                                Profit & Loss (P&L) Report
                                @if($currSourcing === 'msme')
                                    <span class="badge bg-primary-subtle text-primary fs-12 px-2.5 py-1 rounded-pill ms-2">MSME Central Only</span>
                                @elseif($currSourcing === 'regional')
                                    <span class="badge bg-success-subtle text-success fs-12 px-2.5 py-1 rounded-pill ms-2">Regional Local Only</span>
                                @else
                                    <span class="badge bg-dark-subtle text-dark fs-12 px-2.5 py-1 rounded-pill ms-2">Combined All Sources</span>
                                @endif
                            </h4>
                            <p class="text-muted small mb-0">Financial P&L statement breakdown with Gross Revenue, COGS, Gross Margin, Net Profit, and Stock Outlay.</p>
                        </div>
                        <div class="col-md-6 text-md-end d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
                            <a href="{{ route('adc.reports.pnl.export-csv', request()->query()) }}"
                                class="btn btn-outline-success btn-sm fw-semibold d-inline-flex align-items-center gap-1">
                                <iconify-icon icon="solar:file-download-bold-duotone" class="fs-18"></iconify-icon>
                                Export CSV
                            </a>
                            <a href="{{ route('adc.reports.pnl.print', request()->query()) }}" target="_blank"
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
                    <iconify-icon icon="solar:layers-bold-duotone" class="text-primary fs-18 me-1"></iconify-icon> Select P&L Sourcing Scope:
                </div>
                <div class="btn-group" role="group" aria-label="P&L Scope">
                    <a href="{{ route('adc.reports.pnl', array_merge(request()->query(), ['sourcing' => 'all'])) }}"
                        class="btn btn-sm {{ $currSourcing === 'all' ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold px-3">
                        <iconify-icon icon="solar:widget-bold-duotone" class="align-middle me-1"></iconify-icon> All Sources (Combined)
                    </a>
                    <a href="{{ route('adc.reports.pnl', array_merge(request()->query(), ['sourcing' => 'msme'])) }}"
                        class="btn btn-sm {{ $currSourcing === 'msme' ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold px-3">
                        <iconify-icon icon="solar:buildings-bold-duotone" class="align-middle me-1"></iconify-icon> MSME P&L (Central)
                    </a>
                    <a href="{{ route('adc.reports.pnl', array_merge(request()->query(), ['sourcing' => 'regional'])) }}"
                        class="btn btn-sm {{ $currSourcing === 'regional' ? 'btn-success' : 'btn-outline-success' }} fw-semibold px-3">
                        <iconify-icon icon="solar:shop-bold-duotone" class="align-middle me-1"></iconify-icon> Regional P&L (Local)
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('adc.reports.pnl') }}" class="row g-2 align-items-end">
                <input type="hidden" name="sourcing" value="{{ $currSourcing }}">
                <div class="col-md-4">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">Start Date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">End Date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
                </div>
                <div class="col-md-4 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                        <iconify-icon icon="solar:filter-bold-duotone" class="align-middle me-1"></iconify-icon> Apply Period Filter
                    </button>
                    <a href="{{ route('adc.reports.pnl', ['sourcing' => $currSourcing]) }}" class="btn btn-light btn-sm" title="Reset Filters">
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
                                <a href="{{ route('adc.reports.pnl', array_merge(request()->query(), ['start_date' => now()->format('Y-m-d'), 'end_date' => now()->format('Y-m-d')])) }}"
                                    class="btn {{ ($startDate === now()->format('Y-m-d') && $endDate === now()->format('Y-m-d')) ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold px-3">
                                    <iconify-icon icon="solar:sun-bold-duotone" class="align-middle me-1"></iconify-icon> Today (Daily)
                                </a>
                                <a href="{{ route('adc.reports.pnl', array_merge(request()->query(), ['start_date' => now()->startOfWeek()->format('Y-m-d'), 'end_date' => now()->endOfWeek()->format('Y-m-d')])) }}"
                                    class="btn {{ ($startDate === now()->startOfWeek()->format('Y-m-d') && $endDate === now()->endOfWeek()->format('Y-m-d')) ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold px-3">
                                    <iconify-icon icon="solar:calendar-mark-bold-duotone" class="align-middle me-1"></iconify-icon> This Week (Weekly)
                                </a>
                                <a href="{{ route('adc.reports.pnl', array_merge(request()->query(), ['start_date' => now()->startOfMonth()->format('Y-m-d'), 'end_date' => now()->endOfMonth()->format('Y-m-d')])) }}"
                                    class="btn {{ ($startDate === now()->startOfMonth()->format('Y-m-d') && $endDate === now()->endOfMonth()->format('Y-m-d')) ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold px-3">
                                    <iconify-icon icon="solar:calendar-bold-duotone" class="align-middle me-1"></iconify-icon> This Month (Monthly)
                                </a>
                                <a href="{{ route('adc.reports.pnl', array_merge(request()->query(), ['start_date' => now()->subMonth()->startOfMonth()->format('Y-m-d'), 'end_date' => now()->subMonth()->endOfMonth()->format('Y-m-d')])) }}"
                                    class="btn {{ ($startDate === now()->subMonth()->startOfMonth()->format('Y-m-d') && $endDate === now()->subMonth()->endOfMonth()->format('Y-m-d')) ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold px-3">
                                    <iconify-icon icon="solar:history-bold-duotone" class="align-middle me-1"></iconify-icon> Last Month
                                </a>
                                <a href="{{ route('adc.reports.pnl', array_merge(request()->query(), ['start_date' => now()->startOfYear()->format('Y-m-d'), 'end_date' => now()->startOfYear()->format('Y-m-d')])) }}"
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
    <div class="row g-3 mb-4">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Total Gross Revenue</div>
                        <div class="fs-20 fw-bold text-success mt-1">₹{{ number_format($pnlData['revenue']['total'], 2) }}</div>
                        <div class="fs-11 text-muted mt-1">Gross Sales Income</div>
                    </div>
                    <div class="avatar-sm bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <iconify-icon icon="solar:wallet-money-bold-duotone" class="fs-22"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Cost of Goods Sold (COGS)</div>
                        <div class="fs-20 fw-bold text-danger mt-1">₹{{ number_format($pnlData['cogs']['total'], 2) }}</div>
                        <div class="fs-11 text-muted mt-1">Direct Inventory Cost</div>
                    </div>
                    <div class="avatar-sm bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <iconify-icon icon="solar:card-send-bold-duotone" class="fs-22"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Gross Profit Margin</div>
                        <div class="fs-20 fw-bold text-primary mt-1">{{ number_format($pnlData['gross_margin_pct'], 1) }}%</div>
                        <div class="fs-11 text-muted mt-1">Gross Profit: ₹{{ number_format($pnlData['gross_profit'], 2) }}</div>
                    </div>
                    <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <iconify-icon icon="solar:pie-chart-2-bold-duotone" class="fs-22"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Net Profit</div>
                        <div class="fs-20 fw-bold {{ $pnlData['net_profit'] >= 0 ? 'text-success' : 'text-danger' }} mt-1">
                            ₹{{ number_format($pnlData['net_profit'], 2) }}
                        </div>
                        <div class="fs-11 text-muted mt-1">Net Earnings After COGS</div>
                    </div>
                    <div class="avatar-sm bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <iconify-icon icon="solar:chart-2-bold-duotone" class="fs-22"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- P&L Financial Statement Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold text-dark mb-0">Financial Profit & Loss Statement</h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4" style="width: 70%;">Particulars</th>
                            <th class="text-end pe-4" style="width: 30%;">Amount (₹)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- 1. Revenue -->
                        <tr class="table-light fw-bold">
                            <td class="ps-4 text-primary fs-14">1. REVENUE / INCOME</td>
                            <td class="text-end pe-4 text-primary fs-14">₹{{ number_format($pnlData['revenue']['total'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="ps-5 text-muted">Retail POS Sales (CSP)</td>
                            <td class="text-end pe-4 fw-semibold text-dark">₹{{ number_format($pnlData['revenue']['retail_pos'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="ps-5 text-muted">Wholesale POS Sales (MSP)</td>
                            <td class="text-end pe-4 fw-semibold text-dark">₹{{ number_format($pnlData['revenue']['wholesale_pos'], 2) }}</td>
                        </tr>

                        <!-- 2. COGS -->
                        <tr class="table-light fw-bold border-top">
                            <td class="ps-4 text-danger fs-14">2. COST OF GOODS SOLD (COGS)</td>
                            <td class="text-end pe-4 text-danger fs-14">₹{{ number_format($pnlData['cogs']['total'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="ps-5 text-muted">Retail POS Goods Cost</td>
                            <td class="text-end pe-4 fw-semibold text-dark">₹{{ number_format($pnlData['cogs']['retail_pos'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="ps-5 text-muted">Wholesale POS Goods Cost</td>
                            <td class="text-end pe-4 fw-semibold text-dark">₹{{ number_format($pnlData['cogs']['wholesale_pos'], 2) }}</td>
                        </tr>

                        <!-- 3. Summary -->
                        <tr class="table-success fw-bold border-top">
                            <td class="ps-4 fs-14 text-success">3. GROSS PROFIT (Revenue - COGS)</td>
                            <td class="text-end pe-4 fs-14 text-success">₹{{ number_format($pnlData['gross_profit'], 2) }}</td>
                        </tr>
                        <tr class="table-success fw-bold">
                            <td class="ps-4 fs-14 text-success">NET PROFIT / (LOSS)</td>
                            <td class="text-end pe-4 fs-14 text-success">₹{{ number_format($pnlData['net_profit'], 2) }}</td>
                        </tr>

                        <!-- 4. Stock Outlay -->
                        <tr class="table-light fw-bold border-top">
                            <td class="ps-4 text-dark fs-14">4. PURCHASES & STOCK-IN (PERIOD EXPENSES)</td>
                            <td class="text-end pe-4 text-dark fs-14">₹{{ number_format($pnlData['purchases']['total'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="ps-5 text-muted">Central Vendor PO Stock Purchases</td>
                            <td class="text-end pe-4 fw-semibold text-dark">₹{{ number_format($pnlData['purchases']['central_po'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="ps-5 text-muted">Regional Local Stock Purchases</td>
                            <td class="text-end pe-4 fw-semibold text-dark">₹{{ number_format($pnlData['purchases']['regional'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
