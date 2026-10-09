@extends('layouts.admin')

@section('title', 'Seller Rack Payment Verifications')
@section('page-title', 'Seller Rack Payment Verifications')

@section('content')
    <!-- Header Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-8">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                Seller Rack Booking Payments Verification
                            </h4>
                            <p class="text-muted small mb-0">Verify seller payment proofs (UTR / Slip) to automatically allot Rack/Shelf units and allocate 50/50 revenue split.</p>
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
                    <form method="GET" action="{{ route('admin.racks.payments') }}" class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        <input type="hidden" name="owner_type" value="{{ $ownerType }}">

                        <!-- Left: Owner Type Tabs -->
                        <div class="btn-group shadow-sm" role="group">
                            <a href="{{ route('admin.racks.payments', ['owner_type' => 'all', 'status' => $status]) }}" 
                               class="btn btn-sm fw-bold {{ $ownerType === 'all' ? 'btn-primary' : 'btn-outline-secondary bg-white text-dark' }}">
                                <iconify-icon icon="solar:card-bold-duotone" class="align-middle me-1"></iconify-icon>
                                All Payments <span class="badge {{ $ownerType === 'all' ? 'bg-white text-primary' : 'bg-secondary text-white' }} ms-1 fs-11">{{ $allCount }}</span>
                            </a>
                            <a href="{{ route('admin.racks.payments', ['owner_type' => 'ddc', 'status' => $status]) }}" 
                               class="btn btn-sm fw-bold {{ $ownerType === 'ddc' ? 'text-white' : 'btn-outline-secondary bg-white text-dark' }}"
                               style="{{ $ownerType === 'ddc' ? 'background-color:#00838f !important; border-color:#00838f !important;' : '' }}">
                                <iconify-icon icon="solar:buildings-bold-duotone" class="align-middle me-1"></iconify-icon>
                                DDC Depot Payments <span class="badge {{ $ownerType === 'ddc' ? 'bg-white text-dark' : 'bg-secondary text-white' }} ms-1 fs-11">{{ $ddcCount }}</span>
                            </a>
                            <a href="{{ route('admin.racks.payments', ['owner_type' => 'adc', 'status' => $status]) }}" 
                               class="btn btn-sm fw-bold {{ $ownerType === 'adc' ? 'btn-warning text-dark' : 'btn-outline-secondary bg-white text-dark' }}">
                                <iconify-icon icon="solar:shop-bold-duotone" class="align-middle me-1"></iconify-icon>
                                ADC Store Payments <span class="badge {{ $ownerType === 'adc' ? 'bg-dark text-white' : 'bg-secondary text-white' }} ms-1 fs-11">{{ $adcCount }}</span>
                            </a>
                        </div>

                        <!-- Right: Search & Status Filter -->
                        <div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1 justify-content-md-end">
                            <div class="input-group input-group-sm" style="max-width: 280px;">
                                <span class="input-group-text bg-white border-end-0"><iconify-icon icon="solar:magnifer-bold-duotone" class="text-muted"></iconify-icon></span>
                                <input type="text" name="search" class="form-control border-start-0" placeholder="Search booking #, UTR, seller, owner..." value="{{ $search }}">
                            </div>

                            <div style="width: 220px;">
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="Pending Payment Verification" {{ $status === 'Pending Payment Verification' ? 'selected' : '' }}>Pending Verification Only</option>
                                    <option value="Approved" {{ $status === 'Approved' ? 'selected' : '' }}>Approved / Allotted Only</option>
                                    <option value="Rejected" {{ $status === 'Rejected' ? 'selected' : '' }}>Rejected Only</option>
                                    <option value="all" {{ $status === 'all' ? 'selected' : '' }}>All Statuses</option>
                                </select>
                            </div>

                            <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold">Search / Filter</button>
                            @if($search || $ownerType !== 'all' || $status !== 'all')
                                <a href="{{ route('admin.racks.payments') }}" class="btn btn-outline-danger btn-sm px-3">Clear</a>
                            @endif
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light fs-11 text-muted text-uppercase">
                        <tr>
                            <th class="ps-4" style="width: 13%;">Booking No</th>
                            <th style="width: 18%;">Seller Details</th>
                            <th style="width: 18%;">
                                @if($ownerType === 'adc')
                                    Target ADC Store
                                @elseif($ownerType === 'ddc')
                                    Target DDC Depot
                                @else
                                    Target Depot / Store
                                @endif
                            </th>
                            <th style="width: 12%;">Rack / Shelf</th>
                            <th class="text-center" style="width: 10%;">Qty & Duration</th>
                            <th class="text-end" style="width: 12%;">Total Amount (₹)</th>
                            <th class="text-center" style="width: 8%;">Status</th>
                            <th class="text-center" style="width: 9%;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bookings as $item)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark fs-13">{{ $item->booking_no }}</div>
                                    <div class="fs-11 text-muted">{{ $item->created_at->format('d M Y h:i A') }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark fs-13">{{ $item->seller->company_name ?? $item->seller->name ?? 'N/A' }}</div>
                                    <div class="text-muted fs-11">
                                        Seller Code: <code class="fw-bold text-primary">{{ $item->seller->sellerProfile->seller_id ?? ('SLR' . str_pad($item->seller_id, 4, '0', STR_PAD_LEFT)) }}</code>
                                    </div>
                                </td>
                                <td>
                                    <div class="fw-bold text-primary fs-13">{{ $item->owner->company_name ?? $item->owner->name ?? 'N/A' }}</div>
                                    <div class="text-muted fs-11">
                                        <span class="badge bg-light text-dark border me-1">{{ strtoupper($item->owner_type) }}</span>
                                        Code: {{ $item->depotRack->depot_code ?? 'N/A' }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge {{ $item->unit_type === 'RACK' ? 'bg-primary-subtle text-primary' : 'bg-success-subtle text-success' }} fs-11 px-2 py-0.5">
                                        {{ $item->unit_type }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="fw-bold text-dark fs-13">{{ $item->quantity }} Unit(s)</div>
                                    <div class="fs-11 text-muted">{{ $item->duration_months }} Month(s)</div>
                                </td>
                                <td class="text-end">
                                    <div class="fw-bold text-dark fs-14">₹{{ number_format($item->total_amount, 2) }}</div>
                                    <div class="fs-11 text-muted">₹{{ number_format($item->monthly_rate, 2) }}/mo</div>
                                    @if($item->admin_share > 0)
                                        <div class="fs-10 text-success fw-semibold">Admin: ₹{{ number_format($item->admin_share, 2) }} | {{ strtolower($item->owner_type ?? 'ddc') === 'adc' ? 'ADC:' : 'DDC:' }} ₹{{ number_format($item->depot_share, 2) }}</div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($item->status === 'Approved')
                                        <span class="badge bg-success-subtle text-success fs-11 px-2.5 py-1 border border-success-subtle">Approved</span>
                                    @elseif($item->status === 'Rejected')
                                        <span class="badge bg-danger-subtle text-danger fs-11 px-2.5 py-1 border border-danger-subtle">Rejected</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning fs-11 px-2.5 py-1 border border-warning-subtle">Pending</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-xs btn-outline-primary mb-1 w-100" data-bs-toggle="modal" data-bs-target="#viewModal{{ $item->id }}">
                                        View Proof
                                    </button>
                                    @if($item->status === 'Pending Payment Verification')
                                        <button type="button" class="btn btn-xs btn-success me-1 w-100 mb-1 fw-semibold" data-bs-toggle="modal" data-bs-target="#verifyModal{{ $item->id }}">
                                            Verify
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-danger w-100 fw-semibold" data-bs-toggle="modal" data-bs-target="#rejectModal{{ $item->id }}">
                                            Reject
                                        </button>
                                    @endif
                                </td>
                            </tr>

                            <!-- View Proof Modal -->
                            <div class="modal fade" id="viewModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-light">
                                            <h5 class="modal-title fw-bold">Payment Details #{{ $item->booking_no }}</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <div class="modal-body text-start fs-13">
                                            <div class="mb-2"><strong>Payment Method:</strong> {{ $item->payment_method ?: 'N/A' }}</div>
                                            <div class="mb-2"><strong>UTR / Reference No:</strong> <code class="fw-bold fs-13 text-primary">{{ $item->payment_utr ?: 'N/A' }}</code></div>
                                            <div class="mb-2"><strong>Seller Remarks:</strong> {{ $item->seller_remarks ?: 'None' }}</div>
                                            @if($item->payment_slip)
                                                <div class="mt-3 text-center">
                                                    <div class="fw-bold mb-2">Payment Receipt Slip:</div>
                                                    <a href="{{ $item->payment_slip }}" target="_blank">
                                                        <img src="{{ $item->payment_slip }}" style="max-height: 250px; max-width: 100%;" class="rounded border shadow-sm" alt="Payment Slip">
                                                    </a>
                                                </div>
                                            @endif
                                        </div>
                                        <div class="modal-footer bg-light">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Verify Modal -->
                            <div class="modal fade" id="verifyModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <form action="{{ route('admin.racks.payments.verify', $item->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-header bg-success text-white">
                                                <h5 class="modal-title fw-bold text-white">Verify Payment & Allot Unit #{{ $item->booking_no }}</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <div class="alert alert-info py-2 px-3 fs-12 mb-3">
                                                    <iconify-icon icon="solar:info-circle-bold-duotone" class="align-middle me-1"></iconify-icon>
                                                    Verifying this payment will automatically allot <strong>{{ $item->quantity }} {{ $item->unit_type }}(s)</strong> to <strong>{{ $item->seller->company_name ?? $item->seller->name }}</strong> for <strong>{{ $item->duration_months }} Months</strong>.
                                                </div>
                                                <div class="row g-2 mb-3 fs-13">
                                                    <div class="col-6"><strong>Total Amount:</strong> ₹{{ number_format($item->total_amount, 2) }}</div>
                                                    <div class="col-6"><strong>50/50 Revenue Split:</strong></div>
                                                    <div class="col-6 text-success">Admin Share: ₹{{ number_format(round($item->total_amount * 0.50, 2), 2) }}</div>
                                                    <div class="col-6 text-primary">Depot Share: ₹{{ number_format(round($item->total_amount * 0.50, 2), 2) }}</div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fs-13 fw-semibold">Admin Approval Notes (Optional)</label>
                                                    <textarea name="admin_remarks" class="form-control" rows="2" placeholder="Payment verified via UTR {{ $item->payment_utr }}..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-success btn-sm fw-semibold">Confirm Payment & Allot Unit</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Reject Modal -->
                            <div class="modal fade" id="rejectModal{{ $item->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <form action="{{ route('admin.racks.payments.reject', $item->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-header bg-danger text-white">
                                                <h5 class="modal-title fw-bold text-white">Reject Booking Payment #{{ $item->booking_no }}</h5>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-start">
                                                <div class="mb-3">
                                                    <label class="form-label fs-13 fw-semibold">Rejection Reason <span class="text-danger">*</span></label>
                                                    <textarea name="admin_remarks" class="form-control" rows="3" placeholder="e.g. Invalid UTR or payment slip not verified..." required></textarea>
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
                                <td colspan="8" class="text-center py-4 text-muted">
                                    No seller booking payment requests found.
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
