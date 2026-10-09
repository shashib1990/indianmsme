@extends('layouts.ddc')

@section('title', 'My Canteen Network')

@section('content')
    <!-- Header Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-7">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                <iconify-icon icon="solar:shop-bold-duotone" class="text-primary align-middle me-1"></iconify-icon>
                                My Canteen Network (ADC List)
                            </h4>
                            <p class="text-muted small mb-0">View and manage all Ardh Sainik Canteens (ASC) and Multi Variety Stores (MVS) mapped under your DDC district & pincode jurisdiction.</p>
                        </div>
                        <div class="col-md-5 text-md-end">
                            <a href="{{ route('ddc.adc-orders.index') }}" class="btn btn-outline-primary btn-sm fw-semibold me-1">
                                <iconify-icon icon="solar:bag-check-bold-duotone" class="align-middle me-1"></iconify-icon> Canteen Orders
                            </a>
                            <a href="{{ route('ddc.adc-wallet-requests.index') }}" class="btn btn-primary btn-sm fw-semibold">
                                <iconify-icon icon="solar:wallet-bold-duotone" class="align-middle me-1"></iconify-icon> Wallet Requests
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-12 fw-semibold uppercase tracking-wider">Total Canteens</span>
                            <h3 class="fw-bold text-dark mb-0 mt-1" style="font-family: 'Outfit', sans-serif;">{{ $totalCount }}</h3>
                        </div>
                        <div class="avatar-sm bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <iconify-icon icon="solar:shop-bold-duotone" class="fs-24"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-12 fw-semibold uppercase tracking-wider">Active Stores</span>
                            <h3 class="fw-bold text-success mb-0 mt-1" style="font-family: 'Outfit', sans-serif;">{{ $activeCount }}</h3>
                        </div>
                        <div class="avatar-sm bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <iconify-icon icon="solar:verified-check-bold-duotone" class="fs-24"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-12 fw-semibold uppercase tracking-wider">ASC vs MVS Stores</span>
                            <h3 class="fw-bold text-dark mb-0 mt-1 fs-18" style="font-family: 'Outfit', sans-serif;">
                                <span class="text-info">{{ $ascCount }} ASC</span> / <span class="text-secondary">{{ $mvsCount }} MVS</span>
                            </h3>
                        </div>
                        <div class="avatar-sm bg-info-subtle text-info rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <iconify-icon icon="solar:buildings-2-bold-duotone" class="fs-24"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-12 fw-semibold uppercase tracking-wider">Canteen Wallet Pool</span>
                            <h3 class="fw-bold text-primary mb-0 mt-1" style="font-family: 'Outfit', sans-serif;">₹{{ number_format($totalWalletBalance, 2) }}</h3>
                        </div>
                        <div class="avatar-sm bg-warning-subtle text-warning rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <iconify-icon icon="solar:card-recive-bold-duotone" class="fs-24"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Bar -->
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('ddc.canteens.index') }}" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><iconify-icon icon="solar:magnifer-bold-duotone" class="text-muted"></iconify-icon></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search by Canteen Code, Name, District, Mobile or Pincode..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" {{ $typeFilter === 'all' ? 'selected' : '' }}>All Store Types (ASC & MVS)</option>
                        <option value="ASC" {{ $typeFilter === 'ASC' ? 'selected' : '' }}>ASC – Ardh Sainik Canteen</option>
                        <option value="MVS" {{ $typeFilter === 'MVS' ? 'selected' : '' }}>MVS – Multi Variety Store</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" {{ $statusFilter === 'all' ? 'selected' : '' }}>All Status</option>
                        <option value="active" {{ $statusFilter === 'active' ? 'selected' : '' }}>Active Only</option>
                        <option value="inactive" {{ $statusFilter === 'inactive' ? 'selected' : '' }}>Inactive Only</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold w-100">Filter</button>
                    @if($search || $typeFilter !== 'all' || $statusFilter !== 'all')
                        <a href="{{ route('ddc.canteens.index') }}" class="btn btn-light btn-sm">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Canteens Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-dark text-white fs-11 text-uppercase">
                        <tr>
                            <th class="ps-3" style="width: 5%;">#</th>
                            <th style="width: 14%;">Canteen Code</th>
                            <th style="width: 25%;">Store / Firm Name</th>
                            <th style="width: 12%;">Type</th>
                            <th style="width: 15%;">Contact Info</th>
                            <th style="width: 17%;">District & Pincode</th>
                            <th style="width: 12%;">Wallet Balance</th>
                            <th class="pe-3 text-end" style="width: 10%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($canteens as $index => $canteen)
                            @php
                                $profile = $canteen->adcProfile;
                                $code = $profile?->adc_code ?? $profile?->code ?? 'ADC-' . $canteen->id;
                            @endphp
                            <tr>
                                <td class="ps-3 fw-bold fs-13 text-muted">{{ $canteens->firstItem() + $index }}</td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-12 px-2.5 py-1 font-monospace fw-bold">
                                        {{ $code }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-slate-800 fs-13">{{ $profile?->company_name ?? $canteen->name }}</div>
                                    <div class="text-muted fs-11">
                                        <iconify-icon icon="solar:user-bold-duotone" class="align-middle me-0.5 text-muted"></iconify-icon>
                                        {{ $canteen->name }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ ($profile?->type ?? 'ASC') === 'ASC' ? 'bg-info-subtle text-info border border-info-subtle' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }} fs-11 px-2.5 py-1 uppercase fw-bold">
                                        {{ $profile?->type ?? 'ASC' }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark fs-12">
                                        <iconify-icon icon="solar:phone-calling-bold-duotone" class="text-success align-middle me-1"></iconify-icon>
                                        {{ $canteen->mobile ?? $profile?->phone ?? '—' }}
                                    </div>
                                    @if($canteen->email)
                                        <div class="text-muted fs-11 truncate" style="max-width: 160px;" title="{{ $canteen->email }}">{{ $canteen->email }}</div>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-medium text-dark fs-12">
                                        <iconify-icon icon="solar:map-point-bold-duotone" class="text-danger align-middle me-0.5"></iconify-icon>
                                        {{ $profile?->district ?? $canteen->district ?? '—' }}, {{ $profile?->state ?? $canteen->state ?? '—' }}
                                    </div>
                                    <code class="text-muted fs-11">PIN: {{ $profile?->pincode ?? $canteen->pincode ?? '—' }}</code>
                                </td>
                                <td>
                                    <div class="fw-bold text-success fs-14">₹{{ number_format($profile?->wallet_balance ?? 0, 2) }}</div>
                                </td>
                                <td class="pe-3 text-end">
                                    <a href="{{ route('ddc.canteens.show', $canteen->id) }}" class="btn btn-xs btn-outline-primary px-2.5 py-1 fw-semibold">
                                        <iconify-icon icon="solar:eye-bold-duotone" class="align-middle me-0.5"></iconify-icon> View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <iconify-icon icon="solar:shop-bold-duotone" class="fs-48 text-muted mb-2"></iconify-icon>
                                    <h5 class="fw-bold text-dark mb-1">No Canteens Found</h5>
                                    <p class="text-muted fs-13 mb-0">No Ardh Sainik Canteens match your search or filter query.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($canteens->hasPages())
        <div class="mt-3">
            {{ $canteens->links() }}
        </div>
    @endif
@endsection
