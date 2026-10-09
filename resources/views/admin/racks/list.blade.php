@extends('layouts.admin')

@section('title', 'Depot Racks & Shelves Master List')
@section('page-title', 'Depot Racks & Shelves Master List')

@section('content')
    <!-- Header Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-7">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                Master List: All DDC & ADC Depot Racks
                            </h4>
                            <p class="text-muted small mb-0">View all Racks and Shelves added across India by DDCs and ADCs, their available units, allotted units, and rates.</p>
                        </div>
                        <div class="col-md-5 text-md-end d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
                            <a href="{{ route('admin.racks.settings') }}" class="btn btn-outline-primary btn-sm fw-semibold">
                                <iconify-icon icon="solar:settings-bold-duotone" class="align-middle me-1"></iconify-icon> Pricing Rates
                            </a>
                            <a href="{{ route('admin.racks.images') }}" class="btn btn-outline-warning btn-sm fw-semibold">
                                <iconify-icon icon="solar:gallery-bold-duotone" class="align-middle me-1"></iconify-icon> Image Approvals
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Master Combined Filter Bar -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('admin.racks.list') }}" class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <input type="hidden" name="owner_type" value="{{ $ownerTypeFilter }}">

                        <!-- Left: Owner Type Tabs -->
                        <div class="btn-group shadow-sm" role="group">
                            <a href="{{ route('admin.racks.list', ['owner_type' => 'all', 'unit_type' => $unitTypeFilter, 'search' => $search]) }}" 
                               class="btn btn-sm fw-bold {{ $ownerTypeFilter === 'all' ? 'btn-primary' : 'btn-outline-secondary bg-white text-dark' }}">
                                <iconify-icon icon="solar:widget-bold-duotone" class="align-middle me-1"></iconify-icon>
                                All Racks <span class="badge {{ $ownerTypeFilter === 'all' ? 'bg-white text-primary' : 'bg-secondary text-white' }} ms-1 fs-11">{{ $allCount }}</span>
                            </a>
                            <a href="{{ route('admin.racks.list', ['owner_type' => 'ddc', 'unit_type' => $unitTypeFilter, 'search' => $search]) }}" 
                               class="btn btn-sm fw-bold {{ $ownerTypeFilter === 'ddc' ? 'text-white' : 'btn-outline-secondary bg-white text-dark' }}"
                               style="{{ $ownerTypeFilter === 'ddc' ? 'background-color:#00838f !important; border-color:#00838f !important;' : '' }}">
                                <iconify-icon icon="solar:buildings-bold-duotone" class="align-middle me-1"></iconify-icon>
                                DDC Racks <span class="badge {{ $ownerTypeFilter === 'ddc' ? 'bg-white text-dark' : 'bg-secondary text-white' }} ms-1 fs-11">{{ $ddcCount }}</span>
                            </a>
                            <a href="{{ route('admin.racks.list', ['owner_type' => 'adc', 'unit_type' => $unitTypeFilter, 'search' => $search]) }}" 
                               class="btn btn-sm fw-bold {{ $ownerTypeFilter === 'adc' ? 'btn-warning text-dark' : 'btn-outline-secondary bg-white text-dark' }}">
                                <iconify-icon icon="solar:shop-bold-duotone" class="align-middle me-1"></iconify-icon>
                                ADC Racks <span class="badge {{ $ownerTypeFilter === 'adc' ? 'bg-dark text-white' : 'bg-secondary text-white' }} ms-1 fs-11">{{ $adcCount }}</span>
                            </a>
                        </div>

                        <!-- Right: Search & Unit Type Filter -->
                        <div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1 justify-content-md-end">
                            <div class="input-group input-group-sm" style="max-width: 280px;">
                                <span class="input-group-text bg-white border-end-0"><iconify-icon icon="solar:magnifer-bold-duotone" class="text-muted"></iconify-icon></span>
                                <input type="text" name="search" class="form-control border-start-0" placeholder="Search by code, owner, district..." value="{{ $search }}">
                            </div>

                            <div style="width: 210px;">
                                <select name="unit_type" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="all" {{ $unitTypeFilter === 'all' ? 'selected' : '' }}>All Unit Types (Rack & Shelf)</option>
                                    <option value="RACK" {{ $unitTypeFilter === 'RACK' ? 'selected' : '' }}>Rack Only</option>
                                    <option value="SHELF" {{ $unitTypeFilter === 'SHELF' ? 'selected' : '' }}>Shelf Only</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold">Filter</button>
                            @if($search || $ownerTypeFilter !== 'all' || $unitTypeFilter !== 'all')
                                <a href="{{ route('admin.racks.list') }}" class="btn btn-outline-danger btn-sm px-3">Clear</a>
                            @endif
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Master Racks List Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light fs-11 text-muted text-uppercase">
                        <tr>
                            <th class="ps-4" style="width: 14%;">
                                @if($ownerTypeFilter === 'adc')
                                    ADC Code
                                @elseif($ownerTypeFilter === 'ddc')
                                    Depot Code
                                @else
                                    Code
                                @endif
                            </th>
                            <th style="width: 20%;">
                                @if($ownerTypeFilter === 'adc')
                                    Owner / Store Details
                                @elseif($ownerTypeFilter === 'ddc')
                                    Owner / Depot Details
                                @else
                                    Owner Details
                                @endif
                            </th>
                            <th style="width: 10%;">Unit Type</th>
                            <th class="text-center" style="width: 10%;">Total Units</th>
                            <th class="text-center" style="width: 10%;">Available</th>
                            <th class="text-center" style="width: 10%;">Allotted</th>
                            <th class="text-end" style="width: 14%;">Rate / Month (₹)</th>
                            <th class="text-center" style="width: 12%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($racks as $item)
                            <tr>
                                <td class="ps-4">
                                    <code class="fw-bold fs-13 text-primary">{{ $item->depot_code }}</code>
                                    <div class="fs-11 text-muted">{{ $item->created_at->format('d M Y') }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark fs-13">{{ $item->owner->company_name ?? $item->owner->name ?? 'N/A' }}</div>
                                    <div class="text-muted fs-11">
                                        <span class="badge {{ $item->owner_type === 'ddc' ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning' }} border me-1">
                                            {{ strtoupper($item->owner_type) }}
                                        </span>
                                        <iconify-icon icon="solar:map-point-bold-duotone" class="text-danger align-middle"></iconify-icon>
                                        {{ $item->owner->district ?? $item->owner->state ?? 'Location' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $item->unit_type === 'RACK' ? 'bg-primary-subtle text-primary' : 'bg-success-subtle text-success' }} fs-11 px-2.5 py-1 fw-bold">
                                        {{ $item->unit_type }}
                                    </span>
                                </td>
                                <td class="text-center fw-bold fs-13 text-dark">{{ $item->total_units }}</td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success fs-12 px-2.5 py-1">{{ $item->available_units }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning-subtle text-warning fs-12 px-2.5 py-1">{{ $item->allotted_units }}</span>
                                </td>
                                <td class="text-end fw-bold text-dark fs-13">
                                    ₹{{ number_format($item->amount_per_unit, 2) }}
                                    <div class="fs-10 text-success fw-semibold">{{ strtolower($item->owner_type ?? 'ddc') === 'adc' ? 'ADC 50%:' : 'DDC 50%:' }} ₹{{ number_format($item->amount_per_unit * 0.50, 2) }}</div>
                                </td>
                                <td class="text-center">
                                    @if($item->status === 'active')
                                        <span class="badge bg-success-subtle text-success fs-11 px-2.5 py-1 border border-success-subtle">ACTIVE</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary fs-11 px-2.5 py-1 border border-secondary-subtle">INACTIVE</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <iconify-icon icon="solar:widget-bold-duotone" class="fs-48 text-muted mb-2"></iconify-icon>
                                    <h5 class="fw-bold text-dark mb-1">No Depot Racks Found</h5>
                                    <p class="text-muted fs-13 mb-0">No depot rack records match your search filter.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($racks->hasPages())
        <div class="mt-3">
            {{ $racks->links() }}
        </div>
    @endif
@endsection
