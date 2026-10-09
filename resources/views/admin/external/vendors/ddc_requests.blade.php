@extends('layouts.admin')

@section('title', 'DDC Subscription Requests')
@section('page-title', 'DDC Subscription Requests')

@section('content')
    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-3">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-3">
            {{ session('error') }}
        </div>
    @endif

    <!-- Common Page Action Header -->
    <x-admin.page-header 
        title="DDC Subscription Requests" 
        subtitle="Review, approve or reject manual payment details submitted by vendors for DDC subscription."
        :actions="[
            [
                'label' => 'All Vendors',
                'icon' => 'solar:shop-bold-duotone',
                'url' => route('admin.external.vendors.index'),
                'permission' => 'users.view',
                'btnClass' => 'btn-outline-secondary'
            ],
            [
                'label' => 'DDC Subscriptions Approved',
                'icon' => 'solar:user-id-bold-duotone',
                'url' => route('admin.external.vendors.ddc-approved'),
                'permission' => 'users.view',
                'btnClass' => 'btn-outline-success'
            ]
        ]"
    />

    <!-- Reusable Table Filters -->
    <x-admin.table-filters 
        action="{{ route('admin.external.vendors.ddc-requests') }}" 
        :search="$search" 
        placeholder="Search name, email, company, UTR..." 
        :limit="$limit" 
        :sort="$sort" 
        :order="$order"
    />

    <div class="row mt-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0 text-nowrap align-middle">
                            <thead class="bg-light bg-opacity-50 border-top border-bottom">
                                <tr>
                                    <th class="ps-3" style="width: 60px;">#</th>
                                    <x-admin.table-header field="name" :sort="$sort" :order="$order">Vendor / Company</x-admin.table-header>
                                    <th>Seller Code</th>
                                    <th>UTR Number</th>
                                    <th>Payment Slip</th>
                                    <th>Submitted At</th>
                                    <th class="text-end pe-3" style="width: 200px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    @php
                                        $profile = $user->profile;
                                    @endphp
                                    <tr>
                                        <td class="ps-3 text-muted small">{{ $loop->iteration + (($users->currentPage() - 1) * $users->perPage()) }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ $user->avatar_url }}" alt="avatar" class="rounded-circle border border-light-subtle shadow-sm" width="34" height="34">
                                                <div>
                                                    <span class="fw-semibold text-slate-800 d-block">{{ $profile->company_name ?? ($user->company_name ?? '—') }}</span>
                                                    <span class="text-muted small d-block">({{ $user->name }})</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($user->seller_id)
                                                <code class="small text-primary fw-bold">{{ $user->seller_id }}</code>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($profile && $profile->ddc_subscription_utr)
                                                <code class="small text-dark fw-semibold">{{ $profile->ddc_subscription_utr }}</code>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($profile && $profile->ddc_subscription_slip)
                                                <a href="{{ $profile->ddc_subscription_slip }}" target="_blank" class="btn btn-xs btn-outline-info py-1 px-2 d-inline-flex align-items-center gap-1">
                                                    <iconify-icon icon="solar:document-text-bold-duotone" class="fs-12"></iconify-icon>
                                                    View Slip
                                                </a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">
                                            @if($profile && $profile->ddc_subscription_submitted_at)
                                                {{ \Carbon\Carbon::parse($profile->ddc_subscription_submitted_at)->format('d M Y h:i A') }}
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="d-inline-flex gap-1">
                                                <form action="{{ route('admin.external.vendors.ddc-approve', $user->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-success confirm-action d-flex align-items-center gap-1"
                                                        data-confirm-message="Approve DDC subscription for {{ $user->name }}?">
                                                        <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-14"></iconify-icon>
                                                        Approve
                                                    </button>
                                                </form>

                                                <button class="btn btn-sm btn-danger d-flex align-items-center gap-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#rejectModal"
                                                    data-user-id="{{ $user->id }}"
                                                    data-user-name="{{ $user->name }}"
                                                    data-reject-url="{{ route('admin.external.vendors.ddc-reject', $user->id) }}">
                                                    <iconify-icon icon="solar:close-circle-bold-duotone" class="fs-14"></iconify-icon>
                                                    Reject
                                                </button>

                                                <a href="{{ route('admin.external.vendors.profile', $user->id) }}" 
                                                    class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center p-1"
                                                    title="View Profile"
                                                    style="width: 28px; height: 28px;">
                                                    <iconify-icon icon="solar:eye-bold-duotone" class="fs-14"></iconify-icon>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <x-admin.empty-state 
                                        icon="solar:user-id-bold-duotone" 
                                        title="No Pending DDC Subscription Requests" 
                                        description="There are currently no pending manual DDC model subscription requests." 
                                        colspan="10" 
                                    />
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($users->hasPages())
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                            <small class="text-muted">Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }}</small>
                            {{ $users->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Reject DDC Request Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold text-slate-800">
                        <i class="bx bx-x-circle me-1 text-danger"></i> Reject DDC Subscription Request
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted mb-3">Rejecting request for: <strong id="rejectUserName" class="text-slate-800"></strong></p>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Reason for Rejection <span class="text-danger">*</span></label>
                            <textarea name="ddc_subscription_reject_reason" class="form-control" rows="4" placeholder="Enter reason for rejection so the vendor can rectify and apply again..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">
                            <i class="bx bx-check-circle me-1"></i> Reject Request
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const rejectModal = document.getElementById('rejectModal');
    if (rejectModal) {
        rejectModal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            document.getElementById('rejectUserName').textContent = btn.getAttribute('data-user-name');
            document.getElementById('rejectForm').action = btn.getAttribute('data-reject-url');
        });
    }
});
</script>
@endpush
