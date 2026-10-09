@extends('layouts.admin')

@section('title', 'Manage ADC')
@section('page-title', 'Manage ADC')

@section('content')
    <!-- Common Page Action Header -->
    <x-admin.page-header title="Manage ADC"
        subtitle="Manage ADC accounts, reset credentials, and monitor ADC sessions." :actions="[
            [
                'label' => 'Manage DDC',
                'icon' => 'solar:user-id-bold-duotone',
                'url' => route('admin.external.ddc.index'),
                'permission' => 'users.view',
                'btnClass' => 'btn-outline-primary'
            ],
            [
                'label' => 'Vendors',
                'icon' => 'solar:shop-bold-duotone',
                'url' => route('admin.external.vendors.index'),
                'permission' => 'users.view',
                'btnClass' => 'btn-outline-info'
            ],
            [
                'label' => 'Export CSV',
                'icon' => 'solar:document-text-bold-duotone',
                'onclick' => 'triggerExport(\'csv\')',
                'permission' => 'users.view',
                'btnClass' => 'btn-outline-success'
            ]
        ]" />

    <!-- Reusable Table Filters -->
    <x-admin.table-filters action="{{ route('admin.external.adc.index') }}" :search="$search"
        placeholder="Search name, email, phone, state, designation..." :limit="$limit" :sort="$sort"
        :order="$order" :bulk-actions="[
                'delete' => 'Delete Selected',
                'status_active' => 'Activate Selected',
                'status_inactive' => 'Deactivate Selected'
            ]" />

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
                                    <x-admin.table-header field="name" :sort="$sort"
                                        :order="$order">Name</x-admin.table-header>
                                    <th>Canteen Name</th>
                                    <th>ADC Code</th>
                                    <th>Code</th>
                                    <x-admin.table-header field="email" :sort="$sort"
                                        :order="$order">Email</x-admin.table-header>
                                    <th>Phone</th>
                                    <th>Designation</th>
                                    <th>District</th>
                                    <th>State</th>
                                    <x-admin.table-header field="is_active" :sort="$sort"
                                        :order="$order">Status</x-admin.table-header>
                                    <th>KYC Status</th>
                                    <x-admin.table-header field="created_at" :sort="$sort"
                                        :order="$order">Joined</x-admin.table-header>
                                    <th class="text-end pe-3" style="width: 150px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="form-check fs-15">
                                                <input type="checkbox" class="form-check-input row-checkbox"
                                                    value="{{ $user->id }}">
                                            </div>
                                        </td>
                                        <td class="ps-2 text-muted small">
                                            {{ $loop->iteration + (($users->currentPage() - 1) * $users->perPage()) }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ $user->avatar_url }}" alt="avatar"
                                                    class="rounded-circle border border-light-subtle shadow-sm" width="32"
                                                    height="32">
                                                <span class="fw-semibold text-slate-800">{{ $user->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-medium text-dark">
                                                {{ $user->adcProfile?->company_name ?? ($user->adcProfile?->trade_name ?? ($user->profile?->company_name ?? '—')) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-teal-subtle text-teal border border-teal-subtle px-2 py-1 fs-12" style="background-color: #e6fffa; color: #0d9488; border-color: #99f6e4; font-family: monospace;">
                                                {{ $user->adcProfile?->adc_code ?? $user->adc_code ?? ('ASC' . str_pad($user->id, 3, '0', STR_PAD_LEFT)) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-purple-subtle text-purple border border-purple-subtle px-2 py-1 fs-12" style="background-color: #f3e8ff; color: #7e22ce; border-color: #d8b4fe; font-family: monospace;">
                                                {{ $user->adcProfile?->code ?? '—' }}
                                            </span>
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->phone ?? ($user->mobile ?? '—') }}</td>
                                        <td>{{ $user->designation ?? ($user->profile->designation ?? '—') }}</td>
                                        <td>{{ $user->district ?? ($user->profile->district ?? '—') }}</td>
                                        <td>{{ $user->state ?? ($user->profile->state ?? '—') }}</td>
                                        <td>
                                            @if($user->status === '1')
                                                <span class="badge bg-success-subtle text-success px-2 py-1 fs-12">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-12">Inactive</span>
                                            @endif
                                        </td>
                                        <td>
                                            @php
                                                $kStatus = $user->adcProfile?->kyc_status ?? 'PENDING';
                                            @endphp
                                            @if($kStatus === 'APPROVED')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-11">
                                                    <i class="bx bx-check-circle me-1"></i>Approved
                                                </span>
                                            @elseif($kStatus === 'UNDER_REVIEW')
                                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 fs-11">
                                                    <i class="bx bx-time-five me-1"></i>Under Review
                                                </span>
                                            @elseif($kStatus === 'REJECTED')
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fs-11">
                                                    <i class="bx bx-x-circle me-1"></i>Rejected
                                                </span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 fs-11">
                                                    <i class="bx bx-error-circle me-1"></i>Pending
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">{{ $user->created_at->format('d M Y') }}</td>
                                        <td class="text-end pe-3">
                                            <div class="d-inline-flex gap-1">
                                                <button
                                                    class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center p-1 btn-review-kyc"
                                                    data-user-id="{{ $user->id }}"
                                                    data-user-name="{{ $user->name }}"
                                                    data-details-url="{{ route('admin.external.adc.get-details', $user->id) }}"
                                                    data-kyc-url="{{ route('admin.external.adc.kyc-status', $user->id) }}"
                                                    title="View & Review KYC / Profile Details" style="width: 28px; height: 28px;">
                                                    <iconify-icon icon="solar:shield-check-bold-duotone"
                                                        class="fs-14"></iconify-icon>
                                                </button>

                                                <button
                                                    class="btn btn-sm btn-outline-warning d-flex align-items-center justify-content-center p-1"
                                                    data-bs-toggle="modal" data-bs-target="#resetPwModal"
                                                    data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}"
                                                    data-reset-url="{{ route('admin.external.adc.reset-password', $user->id) }}"
                                                    title="Reset Password" style="width: 28px; height: 28px;">
                                                    <iconify-icon icon="solar:lock-password-bold-duotone"
                                                        class="fs-14"></iconify-icon>
                                                </button>

                                                <form action="{{ route('admin.external.adc.toggle-status', $user->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn btn-sm confirm-action d-flex align-items-center justify-content-center p-1 {{ $user->status === '1' ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                                        data-confirm-message="Are you sure you want to {{ $user->status === '1' ? 'deactivate' : 'activate' }} {{ $user->name }}?"
                                                        title="{{ $user->status === '1' ? 'Deactivate' : 'Activate' }}"
                                                        style="width: 28px; height: 28px;">
                                                        <iconify-icon
                                                            icon="{{ $user->status === '1' ? 'solar:user-block-rounded-bold-duotone' : 'solar:user-check-rounded-bold-duotone' }}"
                                                            class="fs-14"></iconify-icon>
                                                    </button>
                                                </form>

                                                <form action="{{ route('admin.impersonate', ['adc', $user->id]) }}"
                                                    method="POST" class="d-inline" target="_blank">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-info confirm-action d-flex align-items-center justify-content-center p-1"
                                                        data-confirm-message="Login as {{ $user->name }} in a new tab?"
                                                        title="Login as this user (Opens in new tab)" style="width: 28px; height: 28px;">
                                                        <iconify-icon icon="solar:user-check-bold-duotone"
                                                            class="fs-14"></iconify-icon>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <x-admin.empty-state icon="solar:user-speak-rounded-bold-duotone" title="No ADC Users Found"
                                        description="We couldn't find any ADC users matching your search." colspan="15" />
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($users->hasPages())
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                            <small class="text-muted">Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of
                                {{ $users->total() }}</small>
                            {{ $users->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Review KYC & Profile Modal -->
    <div class="modal fade" id="reviewKycModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-dark text-white py-3">
                    <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="kycModalTitle">
                        <iconify-icon icon="solar:shield-check-bold-duotone" class="text-warning fs-22"></iconify-icon>
                        Review ADC Profile & KYC Details
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="kycModalBody">
                    <div class="text-center py-5" id="kycModalLoader">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted fs-13 mt-2">Loading ADC profile details...</p>
                    </div>

                    <div id="kycModalContent" class="d-none">
                        <!-- User Header Card -->
                        <div class="card border-0 bg-light p-3 mb-4 rounded-3">
                            <div class="d-flex flex-column flex-sm-row align-items-center justify-content-between gap-3">
                                <div>
                                    <h5 class="fw-bold text-dark mb-1" id="kName">—</h5>
                                    <p class="text-muted fs-13 mb-0">
                                        Email: <span id="kEmail" class="fw-semibold text-primary">—</span> | 
                                        Mobile: <span id="kMobile" class="fw-semibold text-dark">—</span>
                                    </p>
                                </div>
                                <div class="d-flex gap-2">
                                    <span class="badge bg-primary px-3 py-1.5 rounded-pill fs-12 font-monospace" id="kAdcCode">ADC</span>
                                    <span class="badge bg-secondary px-3 py-1.5 rounded-pill fs-12 font-monospace" id="kCanteenCode">ASC</span>
                                </div>
                            </div>
                        </div>

                        <!-- 1. Identity & Basic Profile Details -->
                        <div class="mb-4">
                            <h6 class="fw-bold text-slate-800 border-bottom pb-2 mb-3 d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:user-id-bold-duotone" class="text-primary fs-18"></iconify-icon>
                                Basic Profile & Legal Identification
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-2 border rounded bg-white">
                                        <small class="text-muted d-block fs-11 fw-bold text-uppercase">Canteen / Entity Name</small>
                                        <span id="kCompanyName" class="fw-bold text-slate-800 fs-13">—</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-2 border rounded bg-white">
                                        <small class="text-muted d-block fs-11 fw-bold text-uppercase">Trade Name</small>
                                        <span id="kTradeName" class="fw-semibold text-slate-800 fs-13">—</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2 border rounded bg-white">
                                        <small class="text-muted d-block fs-11 fw-bold text-uppercase">Aadhaar Number</small>
                                        <span id="kAadhaarNo" class="font-monospace fw-bold text-slate-800 fs-13">—</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2 border rounded bg-white">
                                        <small class="text-muted d-block fs-11 fw-bold text-uppercase">PAN Number</small>
                                        <span id="kPanNo" class="font-monospace fw-bold text-slate-800 fs-13 text-uppercase">—</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2 border rounded bg-white">
                                        <small class="text-muted d-block fs-11 fw-bold text-uppercase">GST Number</small>
                                        <span id="kGstNo" class="font-monospace fw-bold text-slate-800 fs-13 text-uppercase">—</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2 border rounded bg-white">
                                        <small class="text-muted d-block fs-11 fw-bold text-uppercase">State</small>
                                        <span id="kState" class="fw-semibold text-slate-800 fs-13">—</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2 border rounded bg-white">
                                        <small class="text-muted d-block fs-11 fw-bold text-uppercase">District</small>
                                        <span id="kDistrict" class="fw-semibold text-slate-800 fs-13">—</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2 border rounded bg-white">
                                        <small class="text-muted d-block fs-11 fw-bold text-uppercase">Pincode</small>
                                        <span id="kPincode" class="font-monospace fw-bold text-slate-800 fs-13">—</span>
                                    </div>
                                </div>
                                <div class="col-12">
                                    <div class="p-2 border rounded bg-white">
                                        <small class="text-muted d-block fs-11 fw-bold text-uppercase">Complete Address</small>
                                        <span id="kAddress" class="fw-semibold text-slate-800 fs-13">—</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 2. Bank Details -->
                        <div class="mb-4">
                            <h6 class="fw-bold text-slate-800 border-bottom pb-2 mb-3 d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:card-bold-duotone" class="text-success fs-18"></iconify-icon>
                                Bank Account & Settlement Details
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <div class="p-2 border rounded bg-white">
                                        <small class="text-muted d-block fs-11 fw-bold text-uppercase">Account Holder Name</small>
                                        <span id="kAccHolder" class="fw-bold text-slate-800 fs-13">—</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="p-2 border rounded bg-white">
                                        <small class="text-muted d-block fs-11 fw-bold text-uppercase">Bank Name</small>
                                        <span id="kBankName" class="fw-bold text-slate-800 fs-13">—</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2 border rounded bg-white">
                                        <small class="text-muted d-block fs-11 fw-bold text-uppercase">Account Number</small>
                                        <span id="kAccNo" class="font-monospace fw-bold text-primary fs-13">—</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2 border rounded bg-white">
                                        <small class="text-muted d-block fs-11 fw-bold text-uppercase">IFSC Code</small>
                                        <span id="kIfsc" class="font-monospace fw-bold text-slate-800 fs-13">—</span>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="p-2 border rounded bg-white">
                                        <small class="text-muted d-block fs-11 fw-bold text-uppercase">Branch Name</small>
                                        <span id="kBranch" class="fw-semibold text-slate-800 fs-13">—</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- 3. Uploaded KYC Documents -->
                        <div class="mb-4">
                            <h6 class="fw-bold text-slate-800 border-bottom pb-2 mb-3 d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:document-text-bold-duotone" class="text-info fs-18"></iconify-icon>
                                Submitted Proof Documents
                            </h6>
                            <div class="row g-2" id="kDocsGrid">
                                <!-- Document Badges Populated via JS -->
                            </div>
                        </div>

                        <!-- 4. Approval Action Form -->
                        <form id="kycApprovalForm" method="POST" class="p-3 bg-light rounded border">
                            @csrf
                            <h6 class="fw-bold text-slate-800 mb-3 d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:shield-check-bold" class="text-warning fs-18"></iconify-icon>
                                Admin KYC Verification & Approval Action
                            </h6>

                            <div class="row g-3 align-items-center">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-dark fs-13">Update KYC Status <span class="text-danger">*</span></label>
                                    <select name="kyc_status" id="kSelectStatus" class="form-select fw-bold" required onchange="toggleKycRemarks(this.value)">
                                        <option value="APPROVED">✓ APPROVED (Verify & Activate Profile)</option>
                                        <option value="UNDER_REVIEW">⏳ UNDER_REVIEW (Pending Review)</option>
                                        <option value="REJECTED">✕ REJECTED (Reject with Reason)</option>
                                    </select>
                                </div>

                                <div class="col-md-6 d-none" id="kRemarksBox">
                                    <label class="form-label fw-semibold text-dark fs-13">Rejection Reason / Remarks <span class="text-danger">*</span></label>
                                    <input type="text" name="kyc_remarks" id="kRemarksInput" class="form-control" placeholder="Enter reason for rejection...">
                                </div>

                                <div class="col-12 text-end mt-3">
                                    <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Close</button>
                                    <button type="submit" class="btn btn-primary px-4 fw-bold">
                                        <iconify-icon icon="solar:diskette-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                                        Save Verification Decision
                                    </button>
                                </div>
                            </div>
                        </form>

                    </div>
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
                        <i class="bx bx-key me-1 text-warning"></i> Reset ADC Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="resetPasswordForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted mb-3">Resetting password for: <strong id="resetUserName"
                                class="text-slate-800"></strong></p>
                        <div class="alert alert-warning border-0 small py-2 d-flex align-items-center gap-2">
                            <iconify-icon icon="solar:danger-triangle-bold-duotone"
                                class="fs-18 text-warning"></iconify-icon>
                            <span>The user will be forced to change their password on next login.</span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">New Password <span
                                    class="text-danger">*</span></label>
                            <input type="password" name="new_password" class="form-control"
                                placeholder="Minimum 8 characters" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Confirm Password <span
                                    class="text-danger">*</span></label>
                            <input type="password" name="new_password_confirmation" class="form-control"
                                placeholder="Repeat new password" required>
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
        function toggleKycRemarks(val) {
            const remarksBox = document.getElementById('kRemarksBox');
            const remarksInput = document.getElementById('kRemarksInput');
            if (val === 'REJECTED') {
                remarksBox.classList.remove('d-none');
                remarksInput.setAttribute('required', 'required');
            } else {
                remarksBox.classList.add('d-none');
                remarksInput.removeAttribute('required');
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const resetModal = document.getElementById('resetPwModal');
            if (resetModal) {
                resetModal.addEventListener('show.bs.modal', function (event) {
                    const btn = event.relatedTarget;
                    document.getElementById('resetUserName').textContent = btn.getAttribute('data-user-name');
                    document.getElementById('resetPasswordForm').action = btn.getAttribute('data-reset-url');
                });
            }

            const reviewModalEl = document.getElementById('reviewKycModal');
            const reviewModal = new bootstrap.Modal(reviewModalEl);

            $(document).on('click', '.btn-review-kyc', function () {
                const btn = $(this);
                const detailsUrl = btn.data('details-url');
                const kycUrl = btn.data('kyc-url');

                $('#kycModalLoader').removeClass('d-none');
                $('#kycModalContent').addClass('d-none');
                $('#kycApprovalForm').attr('action', kycUrl);

                reviewModal.show();

                $.ajax({
                    url: detailsUrl,
                    type: 'GET',
                    success: function (res) {
                        if (res.status && res.adc) {
                            const a = res.adc;
                            $('#kName').text(a.name || '—');
                            $('#kEmail').text(a.email || '—');
                            $('#kMobile').text(a.mobile || '—');
                            $('#kAdcCode').text('ADC Code: ' + (a.adc_code || a.code || '—'));
                            $('#kCanteenCode').text('Canteen Code: ' + (a.canteen_code || '—'));

                            $('#kCompanyName').text(a.canteen_name || '—');
                            $('#kTradeName').text(a.trade_name || '—');
                            $('#kAadhaarNo').text(a.aadhaar_no || 'Not Provided');
                            $('#kPanNo').text(a.pan_no || 'Not Provided');
                            $('#kGstNo').text(a.gst_number || 'Not Provided');
                            $('#kState').text(a.state || '—');
                            $('#kDistrict').text(a.district || '—');
                            $('#kPincode').text(a.pincode || '—');
                            $('#kAddress').text(a.address || '—');

                            $('#kAccHolder').text(a.account_holder_name || 'Not Provided');
                            $('#kBankName').text(a.bank_name || 'Not Provided');
                            $('#kAccNo').text(a.bank_account_number || 'Not Provided');
                            $('#kIfsc').text(a.ifsc_code || 'Not Provided');
                            $('#kBranch').text(a.branch_name || 'Not Provided');

                            // Build documents grid
                            const docMap = [
                                { label: 'Aadhaar Card Copy', file: a.aadhaar_card },
                                { label: 'PAN Card Copy', file: a.pan_card },
                                { label: 'GST Certificate', file: a.gst_certificate },
                                { label: 'Bank Details / Cancelled Cheque', file: a.cancelled_cheque }
                            ];

                            let docsHtml = '';
                            docMap.forEach(d => {
                                docsHtml += '<div class="col-sm-6"><div class="p-2 border rounded bg-white d-flex align-items-center justify-content-between">';
                                docsHtml += '<span class="fs-12 fw-semibold text-slate-800">' + d.label + '</span>';
                                if (d.file) {
                                    docsHtml += '<a href="' + d.file + '" target="_blank" class="btn btn-xs btn-outline-success py-0.5 px-2"><i class="bx bx-show me-1"></i>View Doc</a>';
                                } else {
                                    docsHtml += '<span class="badge bg-secondary text-white">Not Uploaded</span>';
                                }
                                docsHtml += '</div></div>';
                            });
                            $('#kDocsGrid').html(docsHtml);

                            // Status selection
                            const currentKycStatus = a.kyc_status || 'PENDING';
                            $('#kSelectStatus').val(currentKycStatus);
                            if (currentKycStatus === 'REJECTED') {
                                $('#kRemarksBox').removeClass('d-none');
                                $('#kRemarksInput').val(a.kyc_remarks || '');
                            } else {
                                $('#kRemarksBox').addClass('d-none');
                                $('#kRemarksInput').val('');
                            }

                            $('#kycModalLoader').addClass('d-none');
                            $('#kycModalContent').removeClass('d-none');
                        }
                    },
                    error: function () {
                        alert('Failed to load ADC details. Please try again.');
                        reviewModal.hide();
                    }
                });
            });
        });
    </script>
@endpush