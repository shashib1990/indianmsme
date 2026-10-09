@extends('layouts.admin')

@section('title', 'Vendor Users')
@section('page-title', 'Vendor Users')

@section('content')
    @if($parentSeller)
        <div class="alert alert-info border-0 shadow-sm mb-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="solar:info-circle-bold-duotone" class="fs-20 text-info"></iconify-icon>
                <span>Viewing Sub-Sellers of: <strong class="text-slate-800">{{ $parentSeller->company_name ?: $parentSeller->name }}</strong> (Seller Code: <code>{{ $parentSeller->seller_id }}</code>)</span>
            </div>
            <a href="{{ route('admin.external.vendors.index') }}" class="btn btn-sm btn-info text-white fw-medium d-flex align-items-center gap-1">
                <iconify-icon icon="solar:arrow-left-bold-duotone" class="fs-14"></iconify-icon> Back to Main Sellers
            </a>
        </div>
    @endif

    @if(request('kyc_status') || request('active_sellers') || request('account_status'))
        <div class="alert alert-primary border-0 shadow-sm mb-3 d-flex align-items-center justify-content-between py-2 px-3" style="border-radius: 8px;">
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="solar:filter-bold-duotone" class="fs-18 text-primary"></iconify-icon>
                <span class="fs-13">
                    @if(request('kyc_status'))
                        Filtered by KYC Status: <strong class="text-uppercase text-primary me-2">{{ request('kyc_status') }}</strong>
                    @endif
                    @if(request('active_sellers'))
                        Filtered by: <strong class="text-primary me-2">Catalog Active Sellers (With Approved Products)</strong>
                    @endif
                    @if(request('account_status'))
                        Account Status: <strong class="text-primary me-2">{{ request('account_status') == '1' ? 'Active' : 'Inactive' }}</strong>
                    @endif
                </span>
            </div>
            <a href="{{ route('admin.external.vendors.index') }}" class="btn btn-sm btn-light fw-medium py-0.5 px-2 fs-11">
                Clear Filter &times;
            </a>
        </div>
    @endif

    <!-- Common Page Action Header -->
    <x-admin.page-header 
        title="Vendor Users" 
        subtitle="Manage Vendor Coordinator accounts, reset credentials, and impersonate sessions."
        :actions="[
            [
                'label' => 'DDC Users',
                'icon' => 'solar:user-id-bold-duotone',
                'url' => route('admin.external.ddc.index'),
                'permission' => 'users.view',
                'btnClass' => 'btn-outline-primary'
            ],
            [
                'label' => 'ADC Users',
                'icon' => 'solar:user-speak-rounded-bold-duotone',
                'url' => route('admin.external.adc.index'),
                'permission' => 'users.view',
                'btnClass' => 'btn-outline-warning'
            ],
            [
                'label' => 'Export CSV',
                'icon' => 'solar:document-text-bold-duotone',
                'onclick' => 'triggerExport(\'csv\')',
                'permission' => 'users.view',
                'btnClass' => 'btn-outline-secondary'
            ]
        ]"
    />

    <!-- Reusable Table Filters -->
    <x-admin.table-filters 
        action="{{ route('admin.external.vendors.index') }}" 
        :search="$search" 
        placeholder="Search name, code, email, company, GST, type..." 
        :limit="$limit" 
        :sort="$sort" 
        :order="$order"
        :bulk-actions="[
            'delete' => 'Delete Selected',
            'status_active' => 'Activate Selected',
            'status_inactive' => 'Deactivate Selected'
        ]"
    >
        <select name="kyc_status" class="form-select w-auto py-1.5" onchange="this.form.submit()">
            <option value="">All KYC Status</option>
            <option value="APPROVED" {{ strtolower(request('kyc_status')) == 'approved' ? 'selected' : '' }}>Approved (Verified)</option>
            <option value="PENDING" {{ in_array(strtolower(request('kyc_status')), ['pending', 'under_review']) ? 'selected' : '' }}>Pending / Review</option>
            <option value="REJECTED" {{ strtolower(request('kyc_status')) == 'rejected' ? 'selected' : '' }}>Rejected</option>
        </select>
        <select name="seller_type" class="form-select w-auto py-1.5" onchange="this.form.submit()">
            <option value="">All Seller Types</option>
            <option value="Manufacturer" {{ request('seller_type') == 'Manufacturer' ? 'selected' : '' }}>Manufacturer</option>
            <option value="Trader" {{ request('seller_type') == 'Trader' ? 'selected' : '' }}>Trader</option>
            <option value="Distributor" {{ request('seller_type') == 'Distributor' ? 'selected' : '' }}>Distributor</option>
        </select>
        <select name="account_status" class="form-select w-auto py-1.5" onchange="this.form.submit()">
            <option value="">All Account Status</option>
            <option value="1" {{ request('account_status') === '1' ? 'selected' : '' }}>Active Account</option>
            <option value="0" {{ request('account_status') === '0' ? 'selected' : '' }}>Inactive Account</option>
        </select>
    </x-admin.table-filters>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0 text-nowrap align-middle">
                            <thead class="bg-light bg-opacity-50 border-top border-bottom">
                                <tr>
                                    <th class="ps-3" style="width: 40px;">
                                        <div class="form-check fs-15">
                                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                                        </div>
                                    </th>
                                    <th class="ps-2" style="width: 60px;">#</th>
                                    <x-admin.table-header field="company_name" :sort="$sort" :order="$order">Company / Vendor Name</x-admin.table-header>
                                    <th>Seller Code</th>
                                    <x-admin.table-header field="email" :sort="$sort" :order="$order">Email</x-admin.table-header>
                                    <th>Phone</th>
                                    <th>Seller Type</th>
                                    <th>GST Number</th>
                                    <x-admin.table-header field="is_active" :sort="$sort" :order="$order">Status</x-admin.table-header>
                                    <x-admin.table-header field="created_at" :sort="$sort" :order="$order">Joined</x-admin.table-header>
                                    <th class="text-end pe-3" style="width: 130px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="form-check fs-15">
                                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $user->id }}">
                                            </div>
                                        </td>
                                        <td class="ps-2 text-muted small">{{ $loop->iteration + (($users->currentPage() - 1) * $users->perPage()) }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ $user->avatar_url }}" alt="avatar" class="rounded-circle border border-light-subtle shadow-sm" width="34" height="34">
                                                <div>
                                                    <span class="fw-semibold text-slate-800 d-block">{{ $user->company_name ?? ($user->profile?->company_name ?? '—') }}</span>
                                                    <span class="text-muted small d-block">({{ $user->name }})</span>
                                                    @if($user->sub_sellers_count > 0)
                                                        <a href="{{ route('admin.external.vendors.index', ['parent_id' => $user->id]) }}" class="badge bg-info-subtle text-info mt-1" style="text-decoration: none;">
                                                            <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" class="align-middle me-1"></iconify-icon>
                                                            Sub-Sellers: {{ $user->sub_sellers_count }}
                                                        </a>
                                                    @endif
                                                    @if($user->profile?->parent_id)
                                                        <span class="badge mt-1" style="color: #7c3aed; background: #f3e8ff;">
                                                            Sub-Seller
                                                        </span>
                                                    @endif
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
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->phone ?? ($user->mobile ?? '—') }}</td>
                                        <td>
                                            @php
                                                $sType = $user->profile?->seller_type ?: ($user->seller_type ?: null);
                                                $isDdcSub = (bool) ($user->profile?->is_ddc_subscribed ?? false);
                                            @endphp
                                            @if($sType)
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fs-11 fw-semibold">
                                                    <iconify-icon icon="solar:tag-bold-duotone" class="align-middle me-0.5"></iconify-icon> {{ $sType }}
                                                </span>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif

                                            @if($isDdcSub)
                                                <span class="badge bg-warning text-dark px-2 py-0.5 fs-10 fw-bold d-block mt-1" title="DDC Subscribed Seller">
                                                    <iconify-icon icon="solar:star-bold" class="align-middle me-0.5"></iconify-icon> DDC Active
                                                </span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->gst_number || ($user->profile->gst_number ?? null))
                                                <code class="small text-secondary">{{ $user->gst_number ?? $user->profile->gst_number }}</code>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($user->status === '1')
                                                <span class="badge bg-success-subtle text-success px-2 py-1 fs-12">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-12">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">{{ $user->created_at->format('d M Y') }}</td>
                                        <td class="text-end pe-3">
                                            <div class="d-inline-flex gap-1">
                                                @if($user->sub_sellers_count > 0)
                                                    <a href="{{ route('admin.external.vendors.index', ['parent_id' => $user->id]) }}" 
                                                        class="btn btn-sm btn-outline-info d-flex align-items-center justify-content-center p-1"
                                                        title="View Sub-Sellers"
                                                        style="width: 28px; height: 28px;">
                                                        <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" class="fs-14"></iconify-icon>
                                                    </a>
                                                @endif

                                                <a href="{{ route('admin.external.vendors.profile', $user->id) }}" 
                                                    class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center p-1"
                                                    title="View Profile"
                                                    style="width: 28px; height: 28px;">
                                                    <iconify-icon icon="solar:eye-bold-duotone" class="fs-14"></iconify-icon>
                                                </a>

                                                <button class="btn btn-sm btn-outline-warning d-flex align-items-center justify-content-center p-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#resetPwModal"
                                                    data-user-id="{{ $user->id }}"
                                                    data-user-name="{{ $user->name }}"
                                                    data-reset-url="{{ route('admin.external.vendors.reset-password', $user->id) }}"
                                                    title="Reset Password"
                                                    style="width: 28px; height: 28px;">
                                                    <iconify-icon icon="solar:lock-password-bold-duotone" class="fs-14"></iconify-icon>
                                                </button>

                                                <form action="{{ route('admin.external.vendors.toggle-status', $user->id) }}" method="POST" class="d-inline" data-ajax-form="true">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm confirm-action d-flex align-items-center justify-content-center p-1 {{ $user->status === '1' ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                                        data-confirm-message="Are you sure you want to {{ $user->status === '1' ? 'deactivate' : 'activate' }} {{ $user->name }}?"
                                                        title="{{ $user->status === '1' ? 'Deactivate' : 'Activate' }}"
                                                        style="width: 28px; height: 28px;">
                                                        <iconify-icon icon="{{ $user->status === '1' ? 'solar:user-block-rounded-bold-duotone' : 'solar:user-check-rounded-bold-duotone' }}" class="fs-14"></iconify-icon>
                                                    </button>
                                                </form>

                                                <form action="{{ route('admin.impersonate', ['vendor', $user->id]) }}" method="POST" class="d-inline" target="_blank">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-info confirm-action d-flex align-items-center justify-content-center p-1"
                                                        data-confirm-message="Login as {{ $user->name }} in a new tab?"
                                                        title="Login as this user (Opens in new tab)"
                                                        style="width: 28px; height: 28px;">
                                                        <iconify-icon icon="solar:user-check-bold-duotone" class="fs-14"></iconify-icon>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <x-admin.empty-state 
                                        icon="solar:shop-bold-duotone" 
                                        title="No Vendors Found" 
                                        description="We couldn't find any vendor users matching your search." 
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

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPwModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold text-slate-800">
                        <i class="bx bx-key me-1 text-warning"></i> Reset Vendor Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="resetPasswordForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted mb-3">Resetting password for: <strong id="resetUserName" class="text-slate-800"></strong></p>
                        <div class="alert alert-warning border-0 small py-2 d-flex align-items-center gap-2">
                            <iconify-icon icon="solar:danger-triangle-bold-duotone" class="fs-18 text-warning"></iconify-icon>
                            <span>The vendor will be forced to change their password on next login.</span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">New Password <span class="text-danger">*</span></label>
                            <input type="password" name="new_password" class="form-control" placeholder="Minimum 8 characters" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="new_password_confirmation" class="form-control" placeholder="Repeat new password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning text-white">
                            <i class="bx bx-key me-1"></i> Reset Password
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
    const resetModal = document.getElementById('resetPwModal');
    if (resetModal) {
        resetModal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            document.getElementById('resetUserName').textContent = btn.getAttribute('data-user-name');
            document.getElementById('resetPasswordForm').action = btn.getAttribute('data-reset-url');
        });
    }
});
</script>
@endpush
