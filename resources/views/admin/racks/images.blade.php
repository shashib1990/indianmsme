@extends('layouts.admin')

@section('title', 'Depot Store Photo Approvals')
@section('page-title', 'Depot Store Photo Approvals')

@section('content')
    <!-- Header Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-8">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                Depot Store Photo Approvals
                            </h4>
                            <p class="text-muted small mb-0">Verify and approve store photos uploaded by DDCs and ADCs before displaying to Sellers on the Rack Marketplace.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <form method="GET" action="{{ route('admin.racks.images') }}" class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <input type="hidden" name="owner_type" value="{{ $ownerType }}">
                        <input type="hidden" name="status" value="{{ $statusFilter }}">

                        <!-- Left: Joined Owner Type Filter Button Group -->
                        <div class="btn-group shadow-sm" role="group">
                            <a href="{{ route('admin.racks.images', ['owner_type' => 'all', 'status' => $statusFilter, 'search' => $search]) }}" 
                               class="btn btn-sm fw-bold {{ $ownerType === 'all' ? 'btn-primary' : 'btn-outline-secondary bg-white text-dark' }}">
                                <iconify-icon icon="solar:gallery-bold-duotone" class="align-middle me-1"></iconify-icon>
                                All Photos <span class="badge {{ $ownerType === 'all' ? 'bg-white text-primary' : 'bg-secondary text-white' }} ms-1 fs-11">{{ $allCount }}</span>
                            </a>
                            <a href="{{ route('admin.racks.images', ['owner_type' => 'ddc', 'status' => $statusFilter, 'search' => $search]) }}" 
                               class="btn btn-sm fw-bold {{ $ownerType === 'ddc' ? 'text-white' : 'btn-outline-secondary bg-white text-dark' }}"
                               style="{{ $ownerType === 'ddc' ? 'background-color:#00838f !important; border-color:#00838f !important;' : '' }}">
                                <iconify-icon icon="solar:buildings-bold-duotone" class="align-middle me-1"></iconify-icon>
                                DDC Depot Photos <span class="badge {{ $ownerType === 'ddc' ? 'bg-white text-dark' : 'bg-secondary text-white' }} ms-1 fs-11">{{ $ddcCount }}</span>
                            </a>
                            <a href="{{ route('admin.racks.images', ['owner_type' => 'adc', 'status' => $statusFilter, 'search' => $search]) }}" 
                               class="btn btn-sm fw-bold {{ $ownerType === 'adc' ? 'btn-warning text-dark' : 'btn-outline-secondary bg-white text-dark' }}">
                                <iconify-icon icon="solar:shop-bold-duotone" class="align-middle me-1"></iconify-icon>
                                ADC Store Photos <span class="badge {{ $ownerType === 'adc' ? 'bg-dark text-white' : 'bg-secondary text-white' }} ms-1 fs-11">{{ $adcCount }}</span>
                            </a>
                        </div>

                        <!-- Right: Search Input & Joined Status Filter -->
                        <div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1 justify-content-md-end">
                            <div class="input-group input-group-sm" style="max-width: 260px;">
                                <span class="input-group-text bg-white border-end-0"><iconify-icon icon="solar:magnifer-bold-duotone" class="text-muted"></iconify-icon></span>
                                <input type="text" name="search" class="form-control border-start-0" placeholder="Search depot code, owner..." value="{{ $search }}">
                            </div>

                            <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold">Search</button>

                            <div class="btn-group shadow-sm ms-1" role="group">
                                <a href="{{ route('admin.racks.images', ['owner_type' => $ownerType, 'status' => 'all', 'search' => $search]) }}" class="btn btn-sm {{ $statusFilter === 'all' ? 'btn-dark fw-bold' : 'btn-outline-secondary bg-white text-dark' }}">All Status</a>
                                <a href="{{ route('admin.racks.images', ['owner_type' => $ownerType, 'status' => 'pending', 'search' => $search]) }}" class="btn btn-sm {{ $statusFilter === 'pending' ? 'btn-warning text-dark fw-bold' : 'btn-outline-secondary bg-white text-dark' }}">Pending</a>
                                <a href="{{ route('admin.racks.images', ['owner_type' => $ownerType, 'status' => 'approved', 'search' => $search]) }}" class="btn btn-sm {{ $statusFilter === 'approved' ? 'btn-success fw-bold' : 'btn-outline-secondary bg-white text-dark' }}">Approved</a>
                                <a href="{{ route('admin.racks.images', ['owner_type' => $ownerType, 'status' => 'rejected', 'search' => $search]) }}" class="btn btn-sm {{ $statusFilter === 'rejected' ? 'btn-danger fw-bold' : 'btn-outline-secondary bg-white text-dark' }}">Rejected</a>
                            </div>

                            @if($search || $ownerType !== 'all' || $statusFilter !== 'all')
                                <a href="{{ route('admin.racks.images') }}" class="btn btn-outline-danger btn-sm px-3 ms-1">Clear</a>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Images Gallery Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light fs-11 text-muted text-uppercase">
                        <tr>
                            <th class="ps-4" style="width: 14%;">Code</th>
                            <th style="width: 14%;">Image Preview</th>
                            <th style="width: 25%;">Owner & Depot Details</th>
                            <th style="width: 20%;">Photo Label</th>
                            <th class="text-center" style="width: 12%;">Status</th>
                            <th class="text-center" style="width: 15%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($images as $img)
                            @php
                                $ownerTypeVal = strtolower($img->owner_type ?? 'ddc');
                                $defaultPrefix = $ownerTypeVal === 'adc' ? 'ADC' : 'DDB';
                                $depotCode = $img->depotRack->depot_code ?? ($img->owner->adc_code ?? ($img->owner->ddc_code ?? ($img->owner->code ?? null))) ?? ($defaultPrefix . str_pad($img->owner_id, 3, '0', STR_PAD_LEFT));
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <code class="fw-bold fs-13 {{ $ownerTypeVal === 'adc' ? 'text-warning' : 'text-primary' }}">{{ $depotCode }}</code>
                                    <div class="fs-11 text-muted">ID: #{{ $img->owner_id }}</div>
                                </td>
                                <td>
                                    <a href="{{ $img->image_url }}" target="_blank">
                                        <img src="{{ $img->image_url }}" style="width: 75px; height: 60px; object-fit: cover;" class="rounded border shadow-sm" alt="Rack Photo">
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark fs-13">{{ $img->owner->company_name ?? $img->owner->name ?? 'N/A' }}</div>
                                    <div class="text-muted fs-11">
                                        <span class="badge {{ strtolower($img->owner_type ?? 'ddc') === 'ddc' ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning' }} border me-1">
                                            {{ strtoupper($img->owner_type ?? 'DDC') }}
                                        </span>
                                        <iconify-icon icon="solar:map-point-bold-duotone" class="text-danger align-middle"></iconify-icon>
                                        {{ $img->owner->district ?? $img->owner->state ?? 'Location' }}
                                    </div>
                                </td>
                                <td>
                                    <div class="fs-12 text-dark fw-bold">{{ $img->image_label ?: 'Store Photo' }}</div>
                                    <div class="fs-11 text-muted">{{ $img->created_at->format('d M Y h:i A') }}</div>
                                </td>
                                <td class="text-center">
                                    @if($img->approval_status === 'approved')
                                        <span class="badge bg-success-subtle text-success fs-11 px-2.5 py-1 border border-success-subtle">APPROVED</span>
                                    @elseif($img->approval_status === 'rejected')
                                        <span class="badge bg-danger-subtle text-danger fs-11 px-2.5 py-1 border border-danger-subtle" title="{{ $img->rejection_reason }}">REJECTED</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning fs-11 px-2.5 py-1 border border-warning-subtle">PENDING</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($img->approval_status !== 'approved')
                                        <form action="{{ route('admin.racks.images.approve', $img->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-xs btn-success fw-semibold me-1">Approve</button>
                                        </form>
                                    @endif
                                    @if($img->approval_status !== 'rejected')
                                        <button type="button" class="btn btn-xs btn-outline-danger fw-semibold" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $img->id }}">
                                            Reject
                                        </button>
                                    @endif
                                </td>
                            </tr>

                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal{{ $img->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <form action="{{ route('admin.racks.images.reject', $img->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title fw-bold text-white">Reject Image Request</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <div class="mb-3">
                                                    <label class="form-label fs-13 fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
                                                    <textarea name="rejection_reason" class="form-control" rows="3" placeholder="e.g. Image blur or invalid store photo..." required></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger btn-sm fw-semibold">Confirm Rejection</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    No rack image requests found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($images->hasPages())
        <div class="mt-3">
            {{ $images->links() }}
        </div>
    @endif
@endsection
