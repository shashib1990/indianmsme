@extends('layouts.vendor')

@section('title', 'My Rack & Shelf Bookings')

@section('content')
    <!-- Header Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                My Rack & Shelf Bookings
                            </h4>
                            <p class="text-muted small mb-0">Track your submitted space booking requests, payment verification status, and allotted occupancy dates.</p>
                        </div>
                        <div class="col-md-6 text-md-end d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
                            <a href="{{ route('vendor.rack-bookings.marketplace') }}" class="btn btn-theme btn-sm fw-semibold d-inline-flex align-items-center gap-1">
                                <iconify-icon icon="solar:shop-2-bold-duotone" class="fs-18"></iconify-icon> Browse Marketplace
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('vendor.rack-bookings.index') }}" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><iconify-icon icon="solar:magnifer-bold-duotone" class="text-muted"></iconify-icon></span>
                        <input type="text" name="search" class="form-control" placeholder="Search by booking no, UTR, or depot..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-md-3 d-flex gap-1">
                    <button type="submit" class="btn btn-theme btn-sm px-3 fw-semibold">Search</button>
                    @if($search)
                        <a href="{{ route('vendor.rack-bookings.index') }}" class="btn btn-light btn-sm">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light fs-11 text-muted text-uppercase">
                        <tr>
                            <th class="ps-4" style="width: 14%;">Booking No</th>
                            <th style="width: 22%;">Target Depot</th>
                            <th style="width: 12%;">Rack / Shelf</th>
                            <th class="text-center" style="width: 10%;">Qty & Duration</th>
                            <th class="text-end" style="width: 12%;">Total Amount</th>
                            <th style="width: 15%;">Start / Expiry Date</th>
                            <th class="text-center" style="width: 15%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $item)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark fs-13">{{ $item->booking_no }}</div>
                                    <div class="fs-11 text-muted">{{ $item->created_at->format('d M Y') }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark fs-13">{{ $item->owner->company_name ?? $item->owner->name ?? 'N/A' }}</div>
                                    <div class="text-muted fs-11">
                                        <span class="badge bg-light text-dark border me-1">{{ strtoupper($item->owner_type) }}</span>
                                        {{ $item->owner->district ?? $item->owner->state ?? '' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $item->unit_type === 'RACK' ? 'bg-primary-subtle text-primary' : 'bg-success-subtle text-success' }} fs-11 px-2.5 py-1 fw-bold">
                                        {{ $item->unit_type }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="fw-bold text-dark fs-13">{{ $item->quantity }} Unit(s)</div>
                                    <div class="fs-11 text-muted">{{ $item->duration_months }} Month(s)</div>
                                </td>
                                <td class="text-end fw-bold text-dark fs-14">
                                    ₹{{ number_format($item->total_amount, 2) }}
                                </td>
                                <td>
                                    @if($item->start_date)
                                        <div class="fs-12 text-dark">From: {{ $item->start_date->format('d M Y') }}</div>
                                        <div class="fs-11 text-muted">Expires: {{ $item->expiry_date ? $item->expiry_date->format('d M Y') : 'N/A' }}</div>
                                    @else
                                        <span class="text-muted fs-12">Pending Allotment</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($item->status === 'Approved')
                                        <span class="badge bg-success-subtle text-success fs-11 px-2.5 py-1 border border-success-subtle">APPROVED / ALLOTTED</span>
                                    @elseif($item->status === 'Rejected')
                                        <span class="badge bg-danger-subtle text-danger fs-11 px-2.5 py-1 border border-danger-subtle" title="{{ $item->admin_remarks }}">REJECTED</span>
                                    @elseif($item->status === 'Expired')
                                        <span class="badge bg-secondary-subtle text-secondary fs-11 px-2.5 py-1 border border-secondary-subtle">EXPIRED</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning fs-11 px-2.5 py-1 border border-warning-subtle">PENDING VERIFICATION</span>
                                        <a href="{{ route('vendor.rack-bookings.payment', $item->id) }}" class="btn btn-xs btn-link p-0 text-primary d-block mt-1 fs-11">Submit / Edit Payment</a>
                                    @endif

                                    @if($item->admin_remarks)
                                        <div class="fs-11 text-muted mt-1 text-truncate" style="max-width: 140px;" title="{{ $item->admin_remarks }}">
                                            Note: {{ $item->admin_remarks }}
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <iconify-icon icon="solar:box-bold-duotone" class="fs-48 text-muted mb-2"></iconify-icon>
                                    <h5 class="fw-bold text-dark mb-1">No Booking Requests Submitted Yet</h5>
                                    <p class="fs-13 text-muted mb-3">Browse the Rack Marketplace to book storage space across DDC and ADC depots.</p>
                                    <a href="{{ route('vendor.rack-bookings.marketplace') }}" class="btn btn-theme btn-sm px-4 fw-semibold">
                                        Browse Marketplace
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($bookings->hasPages())
        <div class="mt-3">
            {{ $bookings->links() }}
        </div>
    @endif
@endsection
