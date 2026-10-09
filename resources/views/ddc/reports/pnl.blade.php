@extends('layouts.ddc')

@section('title', 'Profit & Loss (P&L) Report')
@section('page-title', 'Profit & Loss (P&L) Report')

@section('content')
    <!-- Header Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                Profit & Loss (P&L) Report
                                @if($source === 'msme')
                                    <span class="badge bg-primary-subtle text-primary fs-12 px-2.5 py-1 rounded-pill ms-2">MSME Central Only</span>
                                @elseif($source === 'local')
                                    <span class="badge bg-success-subtle text-success fs-12 px-2.5 py-1 rounded-pill ms-2">Regional Local Only</span>
                                @else
                                    <span class="badge bg-dark-subtle text-dark fs-12 px-2.5 py-1 rounded-pill ms-2">Combined All Sources</span>
                                @endif
                            </h4>
                            <p class="text-muted small mb-0">Financial statement analyzing gross revenue, Cost of Goods Sold (COGS), gross margins, stock purchases, and net profitability.</p>
                        </div>
                        <div class="col-md-6 text-md-end d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
                            <a href="{{ route('ddc.reports.pnl.export-csv', request()->query()) }}"
                                class="btn btn-outline-success btn-sm fw-semibold d-inline-flex align-items-center gap-1">
                                <iconify-icon icon="solar:file-download-bold-duotone" class="fs-18"></iconify-icon>
                                Export CSV
                            </a>
                            <a href="{{ route('ddc.reports.pnl.print', request()->query()) }}" target="_blank"
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

    <!-- Sourcing Scope Tabs & Filters -->
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body p-3">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3 pb-3 border-bottom">
                <div class="fw-bold text-dark fs-13 d-flex align-items-center gap-1">
                    <iconify-icon icon="solar:layers-bold-duotone" class="text-primary fs-18 me-1"></iconify-icon> Select P&L Sourcing Scope:
                </div>
                <div class="btn-group" role="group" aria-label="P&L Scope">
                    <a href="{{ route('ddc.reports.pnl', array_merge(request()->query(), ['source' => 'all'])) }}"
                        class="btn btn-sm {{ $source === 'all' ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold px-3">
                        <iconify-icon icon="solar:widget-bold-duotone" class="align-middle me-1"></iconify-icon> All Sources (Combined)
                    </a>
                    <a href="{{ route('ddc.reports.pnl', array_merge(request()->query(), ['source' => 'msme'])) }}"
                        class="btn btn-sm {{ $source === 'msme' ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold px-3">
                        <iconify-icon icon="solar:buildings-bold-duotone" class="align-middle me-1"></iconify-icon> MSME P&L (Central)
                    </a>
                    <a href="{{ route('ddc.reports.pnl', array_merge(request()->query(), ['source' => 'local'])) }}"
                        class="btn btn-sm {{ $source === 'local' ? 'btn-success' : 'btn-outline-success' }} fw-semibold px-3">
                        <iconify-icon icon="solar:shop-bold-duotone" class="align-middle me-1"></iconify-icon> Regional P&L (Local)
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('ddc.reports.pnl') }}" class="row g-2 align-items-end">
                <input type="hidden" name="source" value="{{ $source }}">
                <div class="col-md-3">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">Start Date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">End Date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}">
                </div>
                <div class="col-md-4 d-flex gap-1">
                    <a href="{{ route('ddc.reports.pnl', ['source' => $source, 'start_date' => now()->startOfMonth()->format('Y-m-d'), 'end_date' => now()->endOfMonth()->format('Y-m-d')]) }}" class="btn btn-xs btn-outline-secondary w-100 py-1.5">This Month</a>
                    <a href="{{ route('ddc.reports.pnl', ['source' => $source, 'start_date' => now()->subMonth()->startOfMonth()->format('Y-m-d'), 'end_date' => now()->subMonth()->endOfMonth()->format('Y-m-d')]) }}" class="btn btn-xs btn-outline-secondary w-100 py-1.5">Last Month</a>
                    <a href="{{ route('ddc.reports.pnl', ['source' => $source, 'start_date' => now()->startOfYear()->format('Y-m-d'), 'end_date' => now()->endOfYear()->format('Y-m-d')]) }}" class="btn btn-xs btn-outline-secondary w-100 py-1.5">This Year</a>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                        <iconify-icon icon="solar:filter-bold-duotone" class="align-middle me-1"></iconify-icon> Apply
                    </button>
                    <a href="{{ route('ddc.reports.pnl', ['source' => $source]) }}" class="btn btn-light btn-sm" title="Reset Dates">
                        <iconify-icon icon="solar:restart-bold-duotone" class="align-middle"></iconify-icon>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- KPI Highlight Cards -->
    <div class="row g-3 mb-3">
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Total Sales Revenue</div>
                        <div class="fs-20 fw-bold text-dark mt-1">₹{{ number_format($pnlData['revenue']['total'], 2) }}</div>
                        <div class="fs-11 text-muted mt-1">{{ number_format($pnlData['total_orders_count']) }} Orders Fulfilled</div>
                    </div>
                    <div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
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
                        <div class="fs-20 fw-bold text-secondary mt-1">₹{{ number_format($pnlData['cogs']['total'], 2) }}</div>
                        <div class="fs-11 text-muted mt-1">Product Purchase Cost</div>
                    </div>
                    <div class="avatar-sm bg-secondary-subtle text-secondary rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <iconify-icon icon="solar:cart-large-minimalistic-bold-duotone" class="fs-22"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Gross Profit & Margin</div>
                        <div class="fs-20 fw-bold text-success mt-1">₹{{ number_format($pnlData['gross_profit'], 2) }}</div>
                        <div class="fs-11 text-success fw-semibold mt-1">{{ number_format($pnlData['gross_margin_pct'], 2) }}% Gross Margin</div>
                    </div>
                    <div class="avatar-sm bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <iconify-icon icon="solar:chart-2-bold-duotone" class="fs-22"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted fs-12 fw-semibold text-uppercase">Net Profit / (Loss)</div>
                        <div class="fs-20 fw-bold {{ $pnlData['net_profit'] >= 0 ? 'text-success' : 'text-danger' }} mt-1">
                            ₹{{ number_format($pnlData['net_profit'], 2) }}
                        </div>
                        <div class="fs-11 mt-1">
                            @if($pnlData['net_profit'] >= 0)
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5">Profitable</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-0.5">Net Loss</span>
                            @endif
                        </div>
                    </div>
                    <div class="avatar-sm {{ $pnlData['net_profit'] >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                        <iconify-icon icon="{{ $pnlData['net_profit'] >= 0 ? 'solar:graph-up-bold-duotone' : 'solar:graph-down-bold-duotone' }}" class="fs-22"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Comparative Side-by-Side Analysis Card (MSME vs Regional) -->
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
            <h5 class="fw-bold text-slate-800 mb-0" style="font-family:'Outfit',sans-serif;">
                <iconify-icon icon="solar:transfer-horizontal-bold-duotone" class="text-primary me-1 align-middle fs-18"></iconify-icon>
                MSME (Central) vs Regional (Local) P&L Comparison
            </h5>
            <span class="fs-12 text-muted">Comparative Financial Analysis</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered align-middle mb-0 text-center">
                    <thead class="bg-light">
                        <tr class="fs-12 text-muted text-uppercase">
                            <th class="text-start ps-3" style="width: 25%;">Financial Component</th>
                            <th style="width: 25%;" class="text-primary">
                                <iconify-icon icon="solar:buildings-bold-duotone" class="align-middle me-1"></iconify-icon> MSME (Central)
                            </th>
                            <th style="width: 25%;" class="text-success">
                                <iconify-icon icon="solar:shop-bold-duotone" class="align-middle me-1"></iconify-icon> Regional (Local)
                            </th>
                            <th style="width: 25%;" class="bg-primary-subtle text-primary">Combined Total</th>
                        </tr>
                    </thead>
                    <tbody class="fs-13">
                        <tr>
                            <td class="text-start ps-3 fw-semibold">Sales Revenue (Gross Income)</td>
                            <td class="fw-bold text-dark">₹{{ number_format($pnlData['msme']['revenue']['total'], 2) }}</td>
                            <td class="fw-bold text-dark">₹{{ number_format($pnlData['local']['revenue']['total'], 2) }}</td>
                            <td class="fw-bold text-primary bg-light">₹{{ number_format($pnlData['combined']['revenue']['total'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-start ps-3 fw-semibold">Cost of Goods Sold (COGS)</td>
                            <td class="text-muted">₹{{ number_format($pnlData['msme']['cogs']['total'], 2) }}</td>
                            <td class="text-muted">₹{{ number_format($pnlData['local']['cogs']['total'], 2) }}</td>
                            <td class="fw-semibold text-secondary bg-light">₹{{ number_format($pnlData['combined']['cogs']['total'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-start ps-3 fw-bold text-success">Gross Profit</td>
                            <td class="fw-bold text-success">₹{{ number_format($pnlData['msme']['gross_profit'], 2) }}</td>
                            <td class="fw-bold text-success">₹{{ number_format($pnlData['local']['gross_profit'], 2) }}</td>
                            <td class="fw-bold text-success bg-light">₹{{ number_format($pnlData['combined']['gross_profit'], 2) }}</td>
                        </tr>
                        <tr>
                            <td class="text-start ps-3 fw-semibold">Gross Margin (%)</td>
                            <td>
                                <span class="badge bg-primary-subtle text-primary px-2 py-1 fs-11">
                                    {{ number_format($pnlData['msme']['gross_margin_pct'], 2) }}%
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success px-2 py-1 fs-11">
                                    {{ number_format($pnlData['local']['gross_margin_pct'], 2) }}%
                                </span>
                            </td>
                            <td class="bg-light">
                                <span class="badge bg-dark-subtle text-dark px-2 py-1 fs-11">
                                    {{ number_format($pnlData['combined']['gross_margin_pct'], 2) }}%
                                </span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-start ps-3 fw-semibold">Stock Purchases (Period Expenses)</td>
                            <td class="text-dark">₹{{ number_format($pnlData['msme']['purchases']['total'], 2) }}</td>
                            <td class="text-dark">₹{{ number_format($pnlData['local']['purchases']['total'], 2) }}</td>
                            <td class="fw-semibold text-dark bg-light">₹{{ number_format($pnlData['combined']['purchases']['total'], 2) }}</td>
                        </tr>
                        <tr class="fw-bold bg-light">
                            <td class="text-start ps-3 fs-14">Net Operating Profit</td>
                            <td class="fs-14 {{ $pnlData['msme']['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">₹{{ number_format($pnlData['msme']['net_profit'], 2) }}</td>
                            <td class="fs-14 {{ $pnlData['local']['net_profit'] >= 0 ? 'text-success' : 'text-danger' }}">₹{{ number_format($pnlData['local']['net_profit'], 2) }}</td>
                            <td class="fs-15 text-primary">₹{{ number_format($pnlData['combined']['net_profit'], 2) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Financial Statement Table & Stock Purchases Card -->
    <div class="row g-3 mb-3">
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold text-slate-800 mb-0" style="font-family:'Outfit',sans-serif;">
                        <iconify-icon icon="solar:document-text-bold-duotone" class="text-primary me-1 align-middle fs-18"></iconify-icon>
                        Profit & Loss Statement ({{ strtoupper($source) }})
                    </h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <tbody>
                                {{-- 1. REVENUE --}}
                                <tr class="table-light">
                                    <td class="fw-bold text-uppercase text-dark fs-12 ps-3" colspan="2">1. Sales Revenue / Income</td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fs-13">Retail POS Sales (CSP)</td>
                                    <td class="text-end fw-semibold fs-13 pe-3">₹{{ number_format($pnlData['revenue']['retail_pos'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fs-13">Wholesale POS Sales (MSP)</td>
                                    <td class="text-end fw-semibold fs-13 pe-3">₹{{ number_format($pnlData['revenue']['wholesale_pos'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fs-13">ADC Canteen Direct Orders</td>
                                    <td class="text-end fw-semibold fs-13 pe-3">₹{{ number_format($pnlData['revenue']['adc_canteen'], 2) }}</td>
                                </tr>
                                <tr class="bg-primary-subtle text-primary">
                                    <td class="fw-bold fs-13 ps-3">TOTAL GROSS REVENUE (A)</td>
                                    <td class="text-end fw-bold fs-14 pe-3">₹{{ number_format($pnlData['revenue']['total'], 2) }}</td>
                                </tr>

                                {{-- 2. COGS --}}
                                <tr class="table-light">
                                    <td class="fw-bold text-uppercase text-dark fs-12 ps-3" colspan="2">2. Cost of Goods Sold (COGS)</td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fs-13">Retail POS Goods Cost</td>
                                    <td class="text-end fw-semibold fs-13 text-muted pe-3">₹{{ number_format($pnlData['cogs']['retail_pos'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fs-13">Wholesale POS Goods Cost</td>
                                    <td class="text-end fw-semibold fs-13 text-muted pe-3">₹{{ number_format($pnlData['cogs']['wholesale_pos'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-4 fs-13">ADC Canteen Orders Goods Cost</td>
                                    <td class="text-end fw-semibold fs-13 text-muted pe-3">₹{{ number_format($pnlData['cogs']['adc_canteen'], 2) }}</td>
                                </tr>
                                <tr class="bg-secondary-subtle text-secondary">
                                    <td class="fw-bold fs-13 ps-3">TOTAL COST OF GOODS SOLD (B)</td>
                                    <td class="text-end fw-bold fs-14 pe-3">₹{{ number_format($pnlData['cogs']['total'], 2) }}</td>
                                </tr>

                                {{-- 3. SUMMARY --}}
                                <tr class="table-light">
                                    <td class="fw-bold text-uppercase text-dark fs-12 ps-3" colspan="2">3. Operating Profitability</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 fw-bold fs-13 text-success">GROSS PROFIT (A - B)</td>
                                    <td class="text-end fw-bold fs-14 text-success pe-3">₹{{ number_format($pnlData['gross_profit'], 2) }}</td>
                                </tr>
                                <tr>
                                    <td class="ps-3 fs-13 text-muted">Gross Profit Margin (%)</td>
                                    <td class="text-end fw-bold fs-13 text-success pe-3">{{ number_format($pnlData['gross_margin_pct'], 2) }}%</td>
                                </tr>
                                <tr class="bg-success-subtle text-success">
                                    <td class="fw-bold fs-14 ps-3">NET OPERATING PROFIT / (LOSS)</td>
                                    <td class="text-end fw-bold fs-15 pe-3">₹{{ number_format($pnlData['net_profit'], 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold text-slate-800 mb-0" style="font-family:'Outfit',sans-serif;">
                        <iconify-icon icon="solar:shop-bold-duotone" class="text-warning me-1 align-middle fs-18"></iconify-icon>
                        Period Purchases & Stock-In
                    </h5>
                </div>
                <div class="card-body p-3">
                    <p class="text-muted fs-12 mb-3">Total inventory purchases made during this selected period for stock building.</p>

                    @if($source === 'all' || $source === 'msme')
                        <div class="d-flex align-items-center justify-content-between p-3 mb-2 rounded-3 bg-light border">
                            <div>
                                <div class="fw-bold text-dark fs-13">Central Vendor PO Purchases</div>
                                <div class="text-muted fs-11">Procured from Central MSME Vendors</div>
                            </div>
                            <div class="fw-bold text-dark fs-14">₹{{ number_format($pnlData['purchases']['central_po'], 2) }}</div>
                        </div>
                    @endif

                    @if($source === 'all' || $source === 'local')
                        <div class="d-flex align-items-center justify-content-between p-3 mb-3 rounded-3 bg-light border">
                            <div>
                                <div class="fw-bold text-dark fs-13">Regional Stock Purchases</div>
                                <div class="text-muted fs-11">Procured from Local Suppliers</div>
                            </div>
                            <div class="fw-bold text-dark fs-14">₹{{ number_format($pnlData['purchases']['regional'], 2) }}</div>
                        </div>
                    @endif

                    <div class="p-3 rounded-3 bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="fw-bold fs-13">TOTAL STOCK PURCHASES</span>
                            <span class="fw-bold fs-15">₹{{ number_format($pnlData['purchases']['total'], 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Monthly Breakdown Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="fw-bold text-slate-800 mb-0" style="font-family:'Outfit',sans-serif;">
                <iconify-icon icon="solar:calendar-bold-duotone" class="text-primary me-1 align-middle fs-18"></iconify-icon>
                Monthly Breakdown Statement ({{ strtoupper($source) }})
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-3" style="width: 15%;">Period</th>
                            <th class="text-center" style="width: 10%;">Orders Count</th>
                            <th class="text-end" style="width: 15%;">Total Revenue</th>
                            <th class="text-end" style="width: 15%;">COGS</th>
                            <th class="text-end" style="width: 15%;">Gross Profit</th>
                            <th class="text-center" style="width: 10%;">Margin %</th>
                            <th class="text-end" style="width: 10%;">Purchases</th>
                            <th class="text-end" style="width: 10%;">Net Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pnlData['monthly_breakdown'] as $row)
                            <tr>
                                <td class="ps-3 fw-bold text-dark fs-13">{{ $row['period'] }}</td>
                                <td class="text-center fw-semibold fs-13">{{ number_format($row['orders_count']) }}</td>
                                <td class="text-end fw-bold text-dark fs-13">₹{{ number_format($row['revenue'], 2) }}</td>
                                <td class="text-end text-muted fs-13">₹{{ number_format($row['cogs'], 2) }}</td>
                                <td class="text-end fw-bold text-success fs-13">₹{{ number_format($row['gross_profit'], 2) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success fs-11 px-2 py-1">
                                        {{ number_format($row['margin_pct'], 2) }}%
                                    </span>
                                </td>
                                <td class="text-end text-dark fs-13">₹{{ number_format($row['purchases'], 2) }}</td>
                                <td class="text-end fw-bold {{ $row['net_profit'] >= 0 ? 'text-success' : 'text-danger' }} fs-13">
                                    ₹{{ number_format($row['net_profit'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    No records available for the selected period.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
