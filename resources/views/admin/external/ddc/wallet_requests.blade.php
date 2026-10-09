@extends('layouts.admin')

@section('title', 'DDC Wallet Requests')
@section('page-title', 'DDC Wallet Requests')

@section('content')
    <!-- Action Header Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">DDC Wallet Top-up Requests</h4>
                            <p class="text-muted small mb-0">Review and process DDC coordinator wallet credit requests.</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            @if(auth()->user()->is_super_admin === '1' || auth()->user()->parent_id === null || auth()->user()->hasPermission('users.view'))
                                <a href="{{ route('admin.external.ddc.index') }}" class="btn btn-outline-secondary">
                                    <iconify-icon icon="solar:arrow-left-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                                    Back to DDC Users
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="row mb-3 g-2">
        <div class="col-md-12">
            <form id="filterForm" action="{{ route('admin.external.ddc.wallet-requests') }}" method="GET" class="d-flex flex-wrap gap-2">
                <input type="hidden" name="sort" id="formSort" value="{{ $sort }}">
                <input type="hidden" name="order" id="formOrder" value="{{ $order }}">
                <div class="input-group" style="max-width: 250px;">
                    <span class="input-group-text bg-white border-end-0"><iconify-icon icon="solar:magnifer-bold-duotone" class="text-muted"></iconify-icon></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="UTR, Name, Email, Phone..." value="{{ $search }}">
                </div>
                <select name="status" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="PENDING" {{ $status === 'PENDING' ? 'selected' : '' }}>PENDING</option>
                    <option value="APPROVED" {{ $status === 'APPROVED' ? 'selected' : '' }}>APPROVED</option>
                    <option value="REJECTED" {{ $status === 'REJECTED' ? 'selected' : '' }}>REJECTED</option>
                </select>
                <select name="limit" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="10" {{ $limit == 10 ? 'selected' : '' }}>10 per page</option>
                    <option value="15" {{ $limit == 15 ? 'selected' : '' }}>15 per page</option>
                    <option value="25" {{ $limit == 25 ? 'selected' : '' }}>25 per page</option>
                    <option value="50" {{ $limit == 50 ? 'selected' : '' }}>50 per page</option>
                    <option value="100" {{ $limit == 100 ? 'selected' : '' }}>100 per page</option>
                </select>
                <button type="submit" class="btn btn-outline-secondary">Filter</button>
                @if($search || $status)
                    <a href="{{ route('admin.external.ddc.wallet-requests') }}" class="btn btn-outline-danger">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0 text-nowrap align-middle">
                    <thead class="bg-light bg-opacity-50 border-top border-bottom">
                        <tr>
                            <th class="ps-3" style="width: 60px;">#</th>
                            <th>DDC Coordinator</th>
                            <th>DDC Code</th>
                            <x-admin.table-header field="amount" :sort="$sort" :order="$order">Amount</x-admin.table-header>
                            <x-admin.table-header field="utr_number" :sort="$sort" :order="$order">UTR Number</x-admin.table-header>
                            <th>Receipt</th>
                            <x-admin.table-header field="created_at" :sort="$sort" :order="$order">Submitted</x-admin.table-header>
                            <x-admin.table-header field="status" :sort="$sort" :order="$order">Status</x-admin.table-header>
                            <th class="text-end pe-3" style="width: 200px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($requests as $req)
                            <tr>
                                <td class="ps-3 text-muted small">
                                    {{ $loop->iteration + (($requests->currentPage() - 1) * $requests->perPage()) }}
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="fw-semibold text-slate-800">{{ $req->ddc->name }}</div>
                                        <span class="text-muted small">({{ $req->ddc->phone ?? ($req->ddc->mobile ?? '—') }})</span>
                                    </div>
                                </td>
                                <td>
                                    <code class="small text-primary fw-bold">{{ $req->ddc->profile->ddc_code ?? '—' }}</code>
                                </td>
                                <td class="fw-bold text-slate-800">
                                    ₹{{ number_format($req->amount, 2) }}
                                </td>
                                <td>
                                    <code class="small text-muted fw-semibold">{{ $req->utr_number }}</code>
                                </td>
                                <td>
                                    <a href="{{ Str::startsWith($req->payment_receipt, ['http://', 'https://']) ? $req->payment_receipt : asset('storage/' . $req->payment_receipt) }}" target="_blank" class="btn btn-xs btn-outline-info py-0.5 px-2 fs-11">
                                        <i class="bx bx-show me-1"></i> View Receipt
                                    </a>
                                </td>
                                <td class="text-muted small">
                                    {{ $req->created_at->format('d M Y, h:i A') }}
                                </td>
                                <td>
                                    @if($req->status === 'APPROVED')
                                        <span class="badge bg-success-subtle text-success px-2 py-1 fs-11 rounded-pill">APPROVED</span>
                                    @elseif($req->status === 'REJECTED')
                                        <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-11 rounded-pill">REJECTED</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning px-2 py-1 fs-11 rounded-pill">PENDING</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    @if($req->status === 'PENDING')
                                        <div class="d-inline-flex gap-1">
                                            <!-- Approve form -->
                                            <form action="{{ route('admin.external.ddc.wallet-requests.approve', $req->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-success px-2 py-1 confirm-action" 
                                                    data-confirm-message="Are you sure you want to approve this credit request for ₹{{ number_format($req->amount, 2) }}?">
                                                    <i class="bx bx-check me-1"></i> Approve
                                                </button>
                                            </form>

                                            <!-- Reject button trigger -->
                                            <button type="button" class="btn btn-sm btn-danger px-2 py-1 reject-trigger-btn" 
                                                data-id="{{ $req->id }}" 
                                                data-amount="₹{{ number_format($req->amount, 2) }}">
                                                <i class="bx bx-x me-1"></i> Reject
                                            </button>
                                        </div>
                                    @else
                                        <div class="text-muted small">
                                            Processed by: <span class="fw-semibold">{{ $req->processor->name ?? 'System' }}</span>
                                            @if($req->remarks)
                                                <div class="text-wrap mt-0.5" style="max-width: 200px; font-size: 10px;">
                                                    Remarks: <em>"{{ $req->remarks }}"</em>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <iconify-icon icon="solar:card-send-bold-duotone" class="fs-48 mb-2"></iconify-icon>
                                    <h5>No Wallet Requests Found</h5>
                                    <p class="mb-0">There are no wallet top-up requests matching the filters.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($requests->total() > 0)
                <div class="d-flex justify-content-between align-items-center p-3 border-top">
                    <span class="text-muted small">Showing {{ $requests->firstItem() }} to {{ $requests->lastItem() }} of {{ $requests->total() }} entries</span>
                    @if($requests->hasPages())
                        {{ $requests->links() }}
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <form id="rejectForm" action="" method="POST">
                    @csrf
                    <div class="modal-header bg-danger text-white border-0 py-3">
                        <h5 class="modal-title fw-bold text-white" id="rejectModalLabel">Reject Wallet Top-up Request</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body py-4">
                        <p class="text-slate-600 mb-3">
                            You are rejecting the credit request for <strong id="rejectModalAmount" class="text-danger"></strong>. 
                            Please provide a reason/remark for the rejection:
                        </p>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-semibold">Rejection Remarks <span class="text-danger">*</span></label>
                            <textarea name="remarks" class="form-control" rows="3" required placeholder="e.g. UTR Number mismatch / Payment receipt is not readable."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light border-0 py-2.5">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Confirm Rejection</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Action Confirmation
    const confirmButtons = document.querySelectorAll('.confirm-action');
    confirmButtons.forEach(btn => {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            const form = this.closest('form');
            const message = this.getAttribute('data-confirm-message') || 'Are you sure you want to perform this action?';
            
            Swal.fire({
                title: 'Confirm Action',
                text: message,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Approve'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // Reject Modal Handler
    const rejectTriggerBtns = document.querySelectorAll('.reject-trigger-btn');
    const rejectModal = new bootstrap.Modal(document.getElementById('rejectModal'));
    const rejectForm = document.getElementById('rejectForm');
    const rejectModalAmount = document.getElementById('rejectModalAmount');

    rejectTriggerBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const reqId = this.getAttribute('data-id');
            const amount = this.getAttribute('data-amount');

            const routeUrl = "{{ route('admin.external.ddc.wallet-requests.reject', ':id') }}";
            rejectForm.action = routeUrl.replace(':id', reqId);
            rejectModalAmount.textContent = amount;
            rejectModal.show();
        });
    });
});
</script>
@endpush
