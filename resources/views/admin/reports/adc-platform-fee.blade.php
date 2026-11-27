@extends('layouts.admin')

@section('title', 'Platform Deduction Fee for ADC (Monthly Report)')
@section('page-title', 'Platform Deduction Fee for ADC')

@push('styles')
<style>
    .report-stat-card {
        border-radius: 12px;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .report-stat-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08) !important;
    }
    .table-fee-report th {
        font-size: 0.74rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        white-space: nowrap;
        background-color: #f8fafc;
        vertical-align: middle;
        padding: 10px 12px;
    }
    .table-fee-report td {
        font-size: 0.82rem;
        vertical-align: middle;
        padding: 10px 12px;
        white-space: nowrap;
    }
</style>
@endpush

@section('content')
@php
    $exportExcelUrl = route('admin.reports.adc-platform-fee.export', array_merge(request()->query(), ['format' => 'xlsx']));
    $exportCsvUrl = route('admin.reports.adc-platform-fee.export', array_merge(request()->query(), ['format' => 'csv']));
@endphp

<div class="container-fluid p-0">

    <!-- Page Header with Action Buttons -->
    <x-admin.page-header title="Platform Deduction Fee for ADC"
        subtitle="Monthly platform fee deduction & GST invoice report for ADCs."
        :actions="[
            [
                'type' => 'link',
                'label' => 'Export Excel (.xlsx)',
                'icon' => 'solar:file-download-bold-duotone',
                'url' => $exportExcelUrl,
                'btnClass' => 'btn-success fw-semibold shadow-sm',
            ],
            [
                'type' => 'link',
                'label' => 'Export CSV',
                'icon' => 'solar:document-text-bold-duotone',
                'url' => $exportCsvUrl,
                'btnClass' => 'btn-outline-success fw-semibold shadow-sm',
            ],
        ]" />

    <!-- Summary Statistics Cards -->
    <div class="row g-3 mb-4">
        <!-- 1. Total Deducted Amount -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm report-stat-card h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar-md bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center me-3 fs-2 flex-shrink-0" style="width: 52px; height: 52px;">
                        <iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <span class="text-muted small fw-medium d-block text-uppercase">Total Fee Deducted</span>
                        <h4 class="mb-0 fw-bold text-slate-800">₹{{ number_format($totalDeductedAmount, 2) }}</h4>
                        <span class="badge bg-danger-subtle text-danger fs-10 mt-1 py-0 px-1.5 rounded">Platform Fee Deductions</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Total Monthly Groups Count -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm report-stat-card h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar-md bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center me-3 fs-2 flex-shrink-0" style="width: 52px; height: 52px;">
                        <iconify-icon icon="solar:bill-list-bold-duotone"></iconify-icon>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <span class="text-muted small fw-medium d-block text-uppercase">Monthly Invoices</span>
                        <h4 class="mb-0 fw-bold text-slate-800">{{ number_format($totalMonthlyGroups) }}</h4>
                        <span class="text-muted fs-11">ADC Monthly Statements</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 3. Unique ADCs Charged -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm report-stat-card h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar-md bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center me-3 fs-2 flex-shrink-0" style="width: 52px; height: 52px;">
                        <iconify-icon icon="solar:users-group-rounded-bold-duotone"></iconify-icon>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <span class="text-muted small fw-medium d-block text-uppercase">ADCs Billed</span>
                        <h4 class="mb-0 fw-bold text-slate-800">{{ number_format($totalAdcsCharged) }}</h4>
                        <span class="text-muted fs-11">Distinct centers billed</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- 4. Active ADCs Count -->
        <div class="col-xl-3 col-md-6">
            <div class="card border-0 shadow-sm report-stat-card h-100">
                <div class="card-body p-3 d-flex align-items-center">
                    <div class="avatar-md bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center me-3 fs-2 flex-shrink-0" style="width: 52px; height: 52px;">
                        <iconify-icon icon="solar:shop-2-bold-duotone"></iconify-icon>
                    </div>
                    <div class="flex-grow-1 overflow-hidden">
                        <span class="text-muted small fw-medium d-block text-uppercase">Total Active ADCs</span>
                        <h4 class="mb-0 fw-bold text-slate-800">{{ number_format($activeAdcsCount) }}</h4>
                        <span class="text-success fs-11 fw-medium">Operational centers</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('admin.reports.adc-platform-fee.index') }}" class="row g-2 align-items-end">

                <!-- 1. Search Keyword -->
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small fw-bold text-slate-700 mb-1">Search</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control form-control-sm border-start-0"
                            placeholder="ADC Code, ASC Code, Name, Phone...">
                    </div>
                </div>

                <!-- 2. Select Month -->
                <div class="col-lg-2 col-md-6">
                    <label class="form-label small fw-bold text-slate-700 mb-1">Select Month</label>
                    <select name="month" class="form-select form-select-sm">
                        <option value="">-- All Months --</option>
                        @foreach($availableMonths as $m)
                            @php
                                $mLabel = \Carbon\Carbon::createFromFormat('Y-m', $m)->format('F Y');
                            @endphp
                            <option value="{{ $m }}" {{ request('month') == $m ? 'selected' : '' }}>
                                {{ $mLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 3. Select ADC -->
                <div class="col-lg-3 col-md-6">
                    <label class="form-label small fw-bold text-slate-700 mb-1">ADC Center</label>
                    <select name="adc_id" class="form-select form-select-sm">
                        <option value="">-- All ADCs --</option>
                        @foreach($adcList as $adcOption)
                            @php
                                $adcCodeVal = $adcOption->adcProfile?->adc_code ?: ($adcOption->adc_code ?: ('ADC' . str_pad($adcOption->id, 3, '0', STR_PAD_LEFT)));
                                $ascCodeVal = $adcOption->adcProfile?->code ?: ($adcOption->code ?: ('ASC' . str_pad($adcOption->id, 3, '0', STR_PAD_LEFT)));
                            @endphp
                            <option value="{{ $adcOption->id }}" {{ request('adc_id') == $adcOption->id ? 'selected' : '' }}>
                                {{ $adcOption->name }} (ADC: {{ $adcCodeVal }} | ASC: {{ $ascCodeVal }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- 4. Per Page & Action Buttons -->
                <div class="col-lg-4 col-md-6 d-flex gap-2">
                    <div class="flex-grow-1">
                        <label class="form-label small fw-bold text-slate-700 mb-1">Per Page</label>
                        <select name="per_page" class="form-select form-select-sm">
                            <option value="15" {{ request('per_page') == 15 ? 'selected' : '' }}>15</option>
                            <option value="25" {{ request('per_page', 25) == 25 ? 'selected' : '' }}>25</option>
                            <option value="50" {{ request('per_page') == 50 ? 'selected' : '' }}>50</option>
                            <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                        </select>
                    </div>
                    <div>
                        <label class="form-label small d-block mb-1">&nbsp;</label>
                        <div class="d-flex gap-1">
                            <button type="submit" class="btn btn-sm btn-primary" title="Apply Filter">
                                <iconify-icon icon="solar:filter-bold" class="align-middle me-1"></iconify-icon> Filter
                            </button>
                            <a href="{{ route('admin.reports.adc-platform-fee.index') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filters">
                                <iconify-icon icon="solar:refresh-linear" class="align-middle"></iconify-icon>
                            </a>
                        </div>
                    </div>
                </div>

            </form>
        </div>
    </div>

    <!-- Data Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
            <h5 class="fw-bold text-slate-800 mb-0 d-flex align-items-center">
                <iconify-icon icon="solar:calendar-date-bold-duotone" class="text-primary me-2 fs-20"></iconify-icon>
                Monthly Platform Fee Statements ({{ $deductions->total() }})
            </h5>
            <span class="text-muted small">Showing {{ $deductions->firstItem() ?? 0 }} to {{ $deductions->lastItem() ?? 0 }} of {{ $deductions->total() }} records</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-fee-report align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3 text-center" style="width: 45px;">S.No</th>
                            <th style="min-width: 140px;">Invoice No.</th>
                            <th>Billing Month</th>
                            <th>ADC Center Name</th>
                            <th>ADC Code</th>
                            <th>ASC Code</th>
                            <th class="text-center">Days Charged</th>
                            <th class="text-end">Daily Rate</th>
                            <th class="text-end">Monthly Amount</th>
                            <th class="text-end">Wallet Balance</th>
                            <th class="text-center">Status</th>
                            <th class="pe-3 text-center" style="width: 140px;">Invoice View</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($deductions as $index => $row)
                            @php
                                $seqNumber = $deductions->firstItem() + $index;
                                $calYear = (int) substr($row->month_year, 0, 4);
                                $calMonth = (int) substr($row->month_year, 5, 2);
                                $fyStr = ($calMonth >= 4) ? ($calYear . '-' . substr((string)($calYear + 1), -2)) : (($calYear - 1) . '-' . substr((string)$calYear, -2));
                                $invNumberStr = 'PF/' . $fyStr . '/' . str_pad($seqNumber, 3, '0', STR_PAD_LEFT);

                                $user = $row->user;
                                $profile = $row->profile;
                                $adcCode = $profile?->adc_code ?: ($user?->adc_code ?: ('ADC' . str_pad($row->adc_id, 3, '0', STR_PAD_LEFT)));
                                $ascCode = $profile?->code ?: ($user?->code ?: ('ASC' . str_pad($row->adc_id, 3, '0', STR_PAD_LEFT)));
                                $dailyRate = (float) ($profile?->per_user_daily_charge ?? 10.00);
                                $monthlyAmount = (float) $row->monthly_amount;
                                $displayBalance = (float) ($row->running_balance ?? ($profile?->wallet_balance ?? 0.00));
                                $isActive = $user?->status === '1';
                                $monthLabel = \Carbon\Carbon::createFromFormat('Y-m', $row->month_year)->format('F Y');
                            @endphp
                            <tr>
                                <td class="ps-3 text-center text-muted fw-semibold">
                                    {{ $seqNumber }}
                                </td>
                                <td>
                                    <span class="badge bg-light text-primary border font-monospace px-2 py-1 fs-12">{{ $invNumberStr }}</span>
                                </td>
                                <td>
                                    <strong class="text-primary font-monospace fs-13 d-block">
                                        {{ $monthLabel }}
                                    </strong>
                                    <span class="text-muted small">
                                        {{ \Carbon\Carbon::parse($row->first_date)->format('d M') }} - {{ \Carbon\Carbon::parse($row->last_date)->format('d M Y') }}
                                    </span>
                                </td>
                                <td>
                                    <strong class="text-slate-800 d-block">{{ $user?->name ?? ('ADC #' . $row->adc_id) }}</strong>
                                    @if($profile?->company_name)
                                        <span class="text-muted small d-block">{{ $profile->company_name }}</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary font-monospace fs-12 px-2 py-1 border border-primary-subtle">
                                        {{ $adcCode }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info font-monospace fs-12 px-2 py-1 border border-info-subtle">
                                        {{ $ascCode }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border px-2 py-1 fw-bold fs-12">
                                        {{ $row->total_days }} Days
                                    </span>
                                </td>
                                <td class="text-end fw-semibold text-secondary">
                                    ₹{{ number_format($dailyRate, 2) }}
                                </td>
                                <td class="text-end">
                                    <span class="fw-bold text-danger fs-14">₹{{ number_format($monthlyAmount, 2) }}</span>
                                </td>
                                <td class="text-end">
                                    <span class="fw-semibold {{ $displayBalance <= 0 ? 'text-danger' : 'text-success' }}">
                                        ₹{{ number_format($displayBalance, 2) }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    @if($isActive)
                                        <span class="badge bg-success-subtle text-success px-2 py-1">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger px-2 py-1" title="{{ $profile?->deactivation_reason ?? 'Deactivated' }}">Deactivated</span>
                                    @endif
                                </td>
                                <td class="pe-3 text-center">
                                    <a href="{{ route('admin.reports.adc-platform-fee.monthly-invoice', ['adc_id' => $row->adc_id, 'month' => $row->month_year, 'seq' => $seqNumber]) }}"
                                       target="_blank"
                                       class="btn btn-sm btn-outline-primary fw-semibold d-inline-flex align-items-center gap-1 shadow-sm px-2.5 py-1">
                                        <iconify-icon icon="solar:document-text-bold-duotone" class="fs-15"></iconify-icon>
                                        <span>Invoice View</span>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-5">
                                    <div class="text-muted">
                                        <iconify-icon icon="solar:bill-cross-bold-duotone" class="fs-48 text-muted mb-2"></iconify-icon>
                                        <p class="mb-1 fw-semibold fs-15">No Monthly Platform Fee Records Found</p>
                                        <span class="small">Try selecting another month or adjusting search filters.</span>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($deductions->hasPages())
            <div class="card-footer bg-white py-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="text-muted small">
                    Page {{ $deductions->currentPage() }} of {{ $deductions->lastPage() }}
                </span>
                <div>
                    {{ $deductions->links() }}
                </div>
            </div>
        @endif
    </div>

</div>
@endsection
