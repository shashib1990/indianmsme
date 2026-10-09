@extends('layouts.admin')

@section('title', 'ADC Wallet Requests')
@section('page-title', 'ADC Wallet Top-Up Requests')

@section('content')
<div class="row">
    <div class="col-12">

        @if(session('success'))
            <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-3" role="alert">
                <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-18 align-middle me-2"></iconify-icon>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show mb-3" role="alert">
                <iconify-icon icon="solar:danger-bold-duotone" class="fs-18 align-middle me-2"></iconify-icon>
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if(session('info'))
            <div class="alert alert-info border-0 shadow-sm alert-dismissible fade show mb-3" role="alert">
                <iconify-icon icon="solar:info-circle-bold-duotone" class="fs-18 align-middle me-2"></iconify-icon>
                {{ session('info') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3">
                <div class="row align-items-center g-3">
                    <div class="col-md-5">
                        <h5 class="card-title mb-0 fw-bold">ADC Wallet Top-Up Requests</h5>
                        <p class="text-muted small mb-0">Monitor all ADC requests. Virtual DDC requests can be approved/rejected by Admin; Physical requests are managed by assigned DDCs.</p>
                    </div>
                    <div class="col-md-7">
                        <form action="{{ route('admin.external.adc.wallet-requests') }}" method="GET" class="row g-2 justify-content-md-end">
                            <div class="col-auto">
                                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">All Channels</option>
                                    <option value="PHYSICAL" {{ request('type') === 'PHYSICAL' ? 'selected' : '' }}>Physical DDC</option>
                                    <option value="VIRTUAL" {{ request('type') === 'VIRTUAL' ? 'selected' : '' }}>Virtual DDC (Admin)</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                    <option value="">All Statuses</option>
                                    <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>Pending</option>
                                    <option value="APPROVED" {{ request('status') === 'APPROVED' ? 'selected' : '' }}>Approved</option>
                                    <option value="REJECTED" {{ request('status') === 'REJECTED' ? 'selected' : '' }}>Rejected</option>
                                </select>
                            </div>
                            <div class="col-auto">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search" class="form-control" placeholder="Search ADC, DDC, UTR, Pincode..." value="{{ request('search') }}">
                                    <button class="btn btn-outline-secondary" type="submit">
                                        <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>ADC Details</th>
                                <th>Pincode</th>
                                <th>Assigned DDC / Channel</th>
                                <th>Amount</th>
                                <th>UTR Number</th>
                                <th>Receipt</th>
                                <th>Status</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $req)
                            <tr class="{{ strtoupper($req->request_type) === 'VIRTUAL' ? 'table-warning-subtle' : '' }}">
                                <td>{{ $req->created_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    <div class="fw-bold text-slate-800">{{ $req->adc?->name ?? 'Unknown ADC' }}</div>
                                    <div class="text-muted small">{{ $req->adc?->mobile }} | Code: {{ $req->adc?->adcProfile?->adc_code ?? '—' }}</div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark">{{ $req->pincode ?? '—' }}</span>
                                </td>
                                <td>
                                    @if(strtoupper($req->request_type) === 'PHYSICAL' && $req->assignedDdc)
                                        <div>
                                            <span class="badge bg-primary-subtle text-primary">
                                                <iconify-icon icon="solar:shop-bold-duotone" class="align-middle me-1"></iconify-icon>
                                                Physical DDC
                                            </span>
                                        </div>
                                        <small class="fw-bold text-slate-700">{{ $req->assignedDdc->name }}</small>
                                    @else
                                        <div>
                                            <span class="badge bg-warning text-dark font-weight-bold">
                                                <iconify-icon icon="solar:shield-warning-bold-duotone" class="align-middle me-1"></iconify-icon>
                                                Virtual DDC (Admin)
                                            </span>
                                        </div>
                                        <small class="text-muted">{{ $req->assignedDdc?->name ?? 'Virtual DDC' }}</small>
                                    @endif
                                </td>
                                <td class="fw-bold text-success fs-15">₹{{ number_format($req->amount, 2) }}</td>
                                <td><code>{{ $req->utr_number }}</code></td>
                                <td>
                                    @if($req->payment_receipt)
                                        <a href="{{ $req->payment_receipt }}" target="_blank" class="btn btn-sm btn-outline-secondary py-1 px-2">
                                            <iconify-icon icon="solar:document-text-bold-duotone" class="align-middle me-1"></iconify-icon> View
                                        </a>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if(strtoupper($req->status) === 'APPROVED')
                                        <span class="badge bg-success-subtle text-success px-2 py-1">APPROVED</span>
                                    @elseif(strtoupper($req->status) === 'REJECTED')
                                        <span class="badge bg-danger-subtle text-danger px-2 py-1">REJECTED</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning px-2 py-1">PENDING</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    @if(strtoupper($req->status) === 'PENDING')
                                        @if(strtoupper($req->request_type) === 'VIRTUAL')
                                            <button class="btn btn-sm btn-success me-1" data-bs-toggle="modal" data-bs-target="#adminApproveModal{{ $req->id }}">
                                                Approve
                                            </button>
                                            <button class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#adminRejectModal{{ $req->id }}">
                                                Reject
                                            </button>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary px-2 py-1" title="Physical DDC action pending. Only assigned DDC can approve/reject.">
                                                <iconify-icon icon="solar:shop-bold-duotone" class="align-middle me-1"></iconify-icon>
                                                Assigned DDC Action Pending
                                            </span>
                                        @endif
                                    @else
                                        <small class="text-muted">Processed by {{ $req->processedBy?->name ?? 'Admin/DDC' }}</small>
                                    @endif
                                </td>
                            </tr>

                            @if(strtoupper($req->status) === 'PENDING' && strtoupper($req->request_type) === 'VIRTUAL')
                            <!-- Approve Modal -->
                            <div class="modal fade text-start" id="adminApproveModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-success text-white">
                                            <h5 class="modal-title text-white fw-bold">Virtual DDC Approval: ADC Top-Up Request</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('admin.external.adc.wallet-requests.approve', $req->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <p>Are you sure you want to approve this Virtual DDC wallet request?</p>
                                                <ul class="list-group list-group-flush mb-3">
                                                    <li class="list-group-item d-flex justify-content-between">
                                                        <span>ADC Name:</span> <strong>{{ $req->adc?->name }}</strong>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between">
                                                        <span>Pincode:</span> <code>{{ $req->pincode ?? 'N/A' }}</code>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between">
                                                        <span>Channel:</span> 
                                                        <strong>Virtual DDC (Admin)</strong>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between">
                                                        <span>Amount:</span> <strong class="text-success">₹{{ number_format($req->amount, 2) }}</strong>
                                                    </li>
                                                    <li class="list-group-item d-flex justify-content-between">
                                                        <span>UTR Number:</span> <code>{{ $req->utr_number }}</code>
                                                    </li>
                                                </ul>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Admin Remarks (Optional)</label>
                                                    <input type="text" class="form-control" name="admin_remarks" placeholder="e.g. Verified by Virtual DDC Admin">
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-success">Approve & Credit Wallet</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Reject Modal -->
                            <div class="modal fade text-start" id="adminRejectModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-danger text-white">
                                            <h5 class="modal-title text-white fw-bold">Reject Virtual DDC Top-Up Request</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                        </div>
                                        <form action="{{ route('admin.external.adc.wallet-requests.reject', $req->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body">
                                                <p class="text-danger fw-semibold">Are you sure you want to reject this Virtual DDC request?</p>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold required">Reason for Rejection</label>
                                                    <textarea class="form-control" name="admin_remarks" rows="3" required placeholder="Specify reason..."></textarea>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-danger">Reject Request</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @endif

                            @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No ADC wallet top-up requests found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($requests->hasPages())
            <div class="card-footer bg-white">
                {{ $requests->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
