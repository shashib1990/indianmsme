@extends('layouts.admin')

@section('title', 'Vendor Profile - ' . $vendor->name)
@section('page-title', 'Vendor Profile')

@section('content')
<!-- Back button & header -->
<x-admin.page-header 
    title="Vendor Profile" 
    subtitle="View and verify seller profile, KYC documentation, and bank details."
    :actions="[
        [
            'label' => 'Back to Vendors',
            'icon' => 'solar:arrow-left-bold-duotone',
            'url' => route('admin.external.vendors.index'),
            'permission' => 'users.view',
            'btnClass' => 'btn-outline-secondary'
        ]
    ]"
/>

<div class="row mt-3">
    <!-- Left Profile Summary & KYC Status Action Card -->
    <div class="col-xl-4 col-lg-5">
        <!-- Profile summary card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center pt-4">
                <img src="{{ $vendor->avatar ? (filter_var($vendor->avatar, FILTER_VALIDATE_URL) ? $vendor->avatar : asset('storage/' . $vendor->avatar)) : asset('admin-assets/images/users/avatar-1.jpg') }}" 
                     alt="Avatar" 
                     class="rounded-circle border border-3 border-light shadow mb-3" 
                     width="120" height="120"
                     style="object-fit: cover;">
                <h4 class="fw-bold text-slate-800 mb-1">{{ $vendor->name }}</h4>
                <p class="text-muted mb-2"><code class="fs-13 fw-semibold">{{ $vendor->seller_id ?? 'N/A' }}</code></p>
                
                <div class="d-flex justify-content-center gap-2 mb-3">
                    @if($vendor->status)
                        <span class="badge bg-success-subtle text-success px-3 py-1 fs-12 rounded-pill">Active Account</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger px-3 py-1 fs-12 rounded-pill">Inactive Account</span>
                    @endif

                    @if(($vendor->sellerProfile->kyc_status ?? 'PENDING') == 'APPROVED')
                        <span class="badge bg-success-subtle text-success px-3 py-1 fs-12 rounded-pill">KYC Approved</span>
                    @elseif(($vendor->sellerProfile->kyc_status ?? 'PENDING') == 'REJECTED')
                        <span class="badge bg-danger-subtle text-danger px-3 py-1 fs-12 rounded-pill">KYC Rejected</span>
                    @else
                        <span class="badge bg-warning-subtle text-warning px-3 py-1 fs-12 rounded-pill">KYC Pending</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- KYC Status Update Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="fw-bold text-slate-800 mb-0">Update KYC Status</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.external.vendors.kyc-status', $vendor->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-slate-700 fw-semibold">KYC Verification Status</label>
                        <select name="kyc_status" class="form-select">
                            <option value="PENDING" {{ ($vendor->sellerProfile->kyc_status ?? 'PENDING') == 'PENDING' ? 'selected' : '' }}>PENDING</option>
                            <option value="APPROVED" {{ ($vendor->sellerProfile->kyc_status ?? '') == 'APPROVED' ? 'selected' : '' }}>APPROVED</option>
                            <option value="REJECTED" {{ ($vendor->sellerProfile->kyc_status ?? '') == 'REJECTED' ? 'selected' : '' }}>REJECTED</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <iconify-icon icon="solar:check-read-bold-duotone" class="me-1 fs-16 align-middle"></iconify-icon> Save Status
                    </button>
                </form>
            </div>
        </div>

        <!-- DDC Model Subscription Management Card -->
        @php
            $subStatus = $vendor->sellerProfile->ddc_subscription_status ?? 'none';
        @endphp
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between">
                <h5 class="fw-bold text-slate-800 mb-0">DDC Subscription</h5>
                @if($subStatus === 'approved')
                    <span class="badge bg-success text-white px-2 py-1 rounded-pill">Subscribed</span>
                @elseif($subStatus === 'pending')
                    <span class="badge bg-warning text-dark px-2 py-1 rounded-pill">Pending Approval</span>
                @elseif($subStatus === 'rejected')
                    <span class="badge bg-danger text-white px-2 py-1 rounded-pill">Rejected</span>
                @else
                    <span class="badge bg-secondary text-white px-2 py-1 rounded-pill">Not Subscribed</span>
                @endif
            </div>
            <div class="card-body">
                @if($subStatus === 'pending' || $subStatus === 'approved' || $subStatus === 'rejected')
                    <div class="mb-3 p-3 bg-light rounded border border-light-subtle">
                        <h6 class="fw-bold text-slate-800 mb-2">Payment Details</h6>
                        <p class="mb-1 text-slate-600 fs-13"><strong>Amount:</strong> ₹10,000</p>
                        <p class="mb-1 text-slate-600 fs-13"><strong>UTR Number:</strong> <code class="text-primary">{{ $vendor->sellerProfile->ddc_subscription_utr ?? '—' }}</code></p>
                        @if($vendor->sellerProfile->ddc_subscription_submitted_at)
                            <p class="mb-2 text-slate-600 fs-13"><strong>Submitted:</strong> {{ \Carbon\Carbon::parse($vendor->sellerProfile->ddc_subscription_submitted_at)->format('d M Y, h:i A') }}</p>
                        @endif
                        @if($vendor->sellerProfile->ddc_subscription_slip)
                            @php
                                $slipPath = $vendor->sellerProfile->ddc_subscription_slip;
                                $isPdfSlip = strtolower(pathinfo($slipPath, PATHINFO_EXTENSION)) === 'pdf';
                            @endphp
                            <div class="mt-2">
                                <a href="{{ filter_var($slipPath, FILTER_VALIDATE_URL) ? $slipPath : asset('storage/' . $slipPath) }}" target="_blank" class="btn btn-xs btn-primary d-inline-flex align-items-center gap-1">
                                    <iconify-icon icon="solar:eye-bold-duotone"></iconify-icon> View Payment Slip
                                </a>
                            </div>
                        @endif
                    </div>
                @endif

                @if($subStatus === 'rejected')
                    <div class="alert alert-danger p-2 fs-12 mb-3">
                        <strong>Rejection Reason:</strong> {{ $vendor->sellerProfile->ddc_subscription_reject_reason ?? 'No reason provided.' }}
                    </div>
                @endif

                @if($subStatus === 'pending')
                    <div class="d-flex gap-2 mb-3">
                        <form action="{{ route('admin.external.vendors.ddc-approve', $vendor->id) }}" method="POST" class="flex-grow-1">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 py-1.5 fw-semibold d-flex align-items-center justify-content-center gap-1 confirm-action" data-confirm-message="Are you sure you want to approve this DDC subscription?">
                                <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-16"></iconify-icon> Approve
                            </button>
                        </form>
                        <button type="button" class="btn btn-danger flex-grow-1 py-1.5 fw-semibold d-flex align-items-center justify-content-center gap-1" data-bs-toggle="collapse" data-bs-target="#rejectFormCollapse">
                            <iconify-icon icon="solar:close-circle-bold-duotone" class="fs-16"></iconify-icon> Reject
                        </button>
                    </div>

                    <div class="collapse border border-danger rounded p-3 mb-3 bg-danger-subtle" id="rejectFormCollapse">
                        <form action="{{ route('admin.external.vendors.ddc-reject', $vendor->id) }}" method="POST">
                            @csrf
                            <div class="mb-2">
                                <label for="ddc_subscription_reject_reason" class="form-label fw-semibold text-danger fs-12 mb-1">Rejection Reason</label>
                                <textarea name="ddc_subscription_reject_reason" id="ddc_subscription_reject_reason" rows="2" class="form-control form-control-sm" placeholder="Explain why the payment info is rejected..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-danger btn-sm w-100">Submit Rejection</button>
                        </form>
                    </div>
                @endif

                @if($subStatus === 'approved')
                    <form action="{{ route('admin.external.vendors.ddc-cancel', $vendor->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger w-100 py-1.5 fw-semibold d-flex align-items-center justify-content-center gap-1 confirm-action" data-confirm-message="Are you sure you want to cancel this vendor's DDC subscription? This will disable their DDC orders.">
                            <iconify-icon icon="solar:close-circle-bold-duotone" class="fs-16"></iconify-icon> Cancel Subscription
                        </button>
                    </form>
                @endif

                @if($subStatus === 'none')
                    <p class="text-muted text-center fs-12 mb-0 italic">This seller has not applied for the DDC Model Subscription yet.</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Detailed Information Tabs -->
    <div class="col-xl-8 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <ul class="nav nav-pills card-header-pills" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-medium px-4" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic-panel" type="button" role="tab">
                            Basic Profile
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-medium px-4" id="kyc-tab" data-bs-toggle="tab" data-bs-target="#kyc-panel" type="button" role="tab">
                            KYC Documents
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-medium px-4" id="bank-tab" data-bs-toggle="tab" data-bs-target="#bank-panel" type="button" role="tab">
                            Bank Details
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="profileTabContent">
                    
                    <!-- TAB 1: BASIC PROFILE -->
                    <div class="tab-pane fade show active" id="basic-panel" role="tabpanel">
                        <h5 class="fw-bold text-slate-800 mb-3">Seller Basic Profile</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-slate-500 small mb-0">Seller ID / Username</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $vendor->seller_id ?? 'N/A' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-slate-500 small mb-0">Company / Name</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $vendor->name }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-slate-500 small mb-0">Authorized Representative Name</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $vendor->sellerProfile->authorized_rep_name ?? '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-slate-500 small mb-0">Contact Phone</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $vendor->phone ?? ($vendor->mobile ?? '—') }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-slate-500 small mb-0">Alternate Phone</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $vendor->sellerProfile->alternate_mobile ?? '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-slate-500 small mb-0">Email Address</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $vendor->email }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-slate-500 small mb-0">Alternate Email Address</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $vendor->sellerProfile->alternate_email ?? '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-slate-500 small mb-0">Business Category</label>
                                <div class="fs-15 fw-semibold text-slate-800">
                                    {{ $vendor->sellerProfile->category->name ?? '—' }}
                                </div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-slate-500 small mb-0">GST Number</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $vendor->gst_number ?? ($vendor->sellerProfile->gst_number ?? '—') }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-slate-500 small mb-0">Seller Type</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $vendor->sellerProfile->seller_type ?? ($vendor->seller_type ?? '—') }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-slate-500 small mb-0">Brand Name(s)</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $vendor->brand_name ?? ($vendor->sellerProfile->brand_name ?? '—') }}</div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-slate-500 small mb-0">Office Address</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $vendor->address ?? '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-slate-500 small mb-0">State</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $vendor->state ?? '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-slate-500 small mb-0">District</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $vendor->district ?? '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-slate-500 small mb-0">Pincode</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $vendor->pincode ?? '—' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: KYC DOCUMENTS -->
                    <div class="tab-pane fade" id="kyc-panel" role="tabpanel">
                        <h5 class="fw-bold text-slate-800 mb-3">KYC Verification Details</h5>
                        <div class="row g-3">
                            @php
                                $kycDocs = [
                                    ['key' => 'gst_certificate', 'label' => 'GST Certificate', 'mandatory' => true],
                                    ['key' => 'pan_card', 'label' => 'PAN Card', 'mandatory' => true],
                                    ['key' => 'udyam_certificate', 'label' => 'Udyam Registration Certificate', 'mandatory' => true],
                                    ['key' => 'incorporation_certificate', 'label' => 'Incorporation Certificate', 'mandatory' => true],
                                    ['key' => 'cancelled_cheque', 'label' => 'Cancelled Cheque / Passbook', 'mandatory' => true],
                                    ['key' => 'company_logo', 'label' => 'Company Logo', 'mandatory' => false],
                                    ['key' => 'company_banner', 'label' => 'Company Banner', 'mandatory' => false]
                                ];
                            @endphp

                            @foreach($kycDocs as $doc)
                                @php
                                    $uploadedPath = $vendor->sellerProfile->{$doc['key']} ?? null;
                                    $fileExt = $uploadedPath ? pathinfo($uploadedPath, PATHINFO_EXTENSION) : '';
                                    $isPdf = strtolower($fileExt) === 'pdf';
                                @endphp
                                <div class="col-md-6">
                                    <div class="card border border-light-subtle shadow-none h-100">
                                        <div class="card-body p-3">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <label class="form-label text-slate-700 fw-semibold mb-0">
                                                    {{ $doc['label'] }} 
                                                    @if($doc['mandatory'])
                                                        <span class="text-danger">*</span>
                                                    @endif
                                                </label>
                                                
                                                @if($uploadedPath)
                                                    <span class="badge bg-success-subtle text-success px-2 py-0.5 fs-11 rounded">Uploaded</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger px-2 py-0.5 fs-11 rounded">Not Uploaded</span>
                                                @endif
                                            </div>

                                            @if($uploadedPath)
                                                <div class="d-flex align-items-center gap-2 mt-2 p-1.5 bg-light rounded border border-light-subtle">
                                                    @if($isPdf)
                                                        <iconify-icon icon="solar:document-text-bold-duotone" class="text-danger fs-24"></iconify-icon>
                                                    @else
                                                        <img src="{{ filter_var($uploadedPath, FILTER_VALIDATE_URL) ? $uploadedPath : asset('storage/' . $uploadedPath) }}" alt="Preview" class="rounded border" width="24" height="24" style="object-fit: cover;">
                                                    @endif
                                                    <span class="text-truncate text-muted small flex-grow-1">{{ basename($uploadedPath) }}</span>
                                                    <a href="{{ filter_var($uploadedPath, FILTER_VALIDATE_URL) ? $uploadedPath : asset('storage/' . $uploadedPath) }}" target="_blank" class="btn btn-xs btn-primary py-0.5 px-2">
                                                        View File
                                                    </a>
                                                </div>
                                            @else
                                                <div class="text-muted small italic p-2 bg-light rounded text-center">No document uploaded yet.</div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- TAB 3: BANK DETAILS -->
                    <div class="tab-pane fade" id="bank-panel" role="tabpanel">
                        <h5 class="fw-bold text-slate-800 mb-3">Bank Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-slate-500 small mb-0">Account Holder Name</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $vendor->sellerProfile->account_holder_name ?? '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-slate-500 small mb-0">Bank Account Number</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $vendor->sellerProfile->bank_account_number ?? '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-slate-500 small mb-0">IFSC Code</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $vendor->sellerProfile->ifsc_code ?? '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-slate-500 small mb-0">Bank Name</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $vendor->sellerProfile->bank_name ?? '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-slate-500 small mb-0">Branch Name</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $vendor->sellerProfile->branch_name ?? '—' }}</div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection
