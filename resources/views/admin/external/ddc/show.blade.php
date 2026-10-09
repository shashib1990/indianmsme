@extends('layouts.admin')

@section('title', 'DDC Profile - ' . $user->name)
@section('page-title', 'DDC Profile')

@section('content')
<!-- Back button & header -->
<x-admin.page-header 
    title="DDC User Profile" 
    subtitle="View and verify DDC profile, KYC documentation, and bank details."
    :actions="[
        [
            'label' => 'Back to DDC Users',
            'icon' => 'solar:arrow-left-bold-duotone',
            'url' => route('admin.external.ddc.index'),
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
                <img src="{{ $user->profile && $user->profile->profile_image ? (filter_var($user->profile->profile_image, FILTER_VALIDATE_URL) ? $user->profile->profile_image : asset('storage/' . $user->profile->profile_image)) : asset('admin-assets/images/users/avatar-1.jpg') }}" 
                     alt="Avatar" 
                     class="rounded-circle border border-3 border-light shadow mb-3" 
                     width="120" height="120"
                     style="object-fit: cover;">
                <h4 class="fw-bold text-slate-800 mb-1">{{ $user->name }}</h4>
                <p class="text-muted mb-2"><code class="fs-13 fw-semibold">{{ $user->profile->ddc_code ?? 'N/A' }}</code></p>
                
                <div class="d-flex justify-content-center gap-2 mb-3 flex-wrap">
                    @if($user->status === '1')
                        <span class="badge bg-success-subtle text-success px-3 py-1 fs-12 rounded-pill">Active Account</span>
                    @else
                        <span class="badge bg-danger-subtle text-danger px-3 py-1 fs-12 rounded-pill">Inactive Account</span>
                    @endif

                    @php
                        $kyc = $user->profile->kyc_status ?? 'PENDING';
                    @endphp
                    @if($kyc === 'APPROVED')
                        <span class="badge bg-success-subtle text-success px-3 py-1 fs-12 rounded-pill">KYC Approved</span>
                    @elseif($kyc === 'REJECTED')
                        <span class="badge bg-danger-subtle text-danger px-3 py-1 fs-12 rounded-pill">KYC Rejected</span>
                    @elseif($kyc === 'UNDER_REVIEW')
                        <span class="badge bg-info-subtle text-info px-3 py-1 fs-12 rounded-pill">Under Review</span>
                    @else
                        <span class="badge bg-warning-subtle text-warning px-3 py-1 fs-12 rounded-pill">KYC Pending</span>
                    @endif
                </div>
            </div>
        </div>

        <!-- KYC Status Update Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-0 py-3 border-bottom">
                <h5 class="fw-bold text-slate-800 mb-0">Update KYC Status</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('admin.external.ddc.kyc-status', $user->id) }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label text-slate-700 fw-semibold">KYC Verification Status</label>
                        <select name="kyc_status" id="kycStatusSelect" class="form-select">
                            <option value="PENDING" {{ $kyc === 'PENDING' ? 'selected' : '' }}>PENDING</option>
                            <option value="UNDER_REVIEW" {{ $kyc === 'UNDER_REVIEW' ? 'selected' : '' }}>UNDER REVIEW</option>
                            <option value="APPROVED" {{ $kyc === 'APPROVED' ? 'selected' : '' }}>APPROVED</option>
                            <option value="REJECTED" {{ $kyc === 'REJECTED' ? 'selected' : '' }}>REJECTED</option>
                        </select>
                    </div>

                    <!-- KYC Remarks (Shown if rejected) -->
                    <div class="mb-3" id="kycRemarksWrapper" style="{{ $kyc === 'REJECTED' ? '' : 'display: none;' }}">
                        <label class="form-label text-slate-700 fw-semibold">Rejection Remarks <span class="text-danger">*</span></label>
                        <textarea name="kyc_remarks" id="kycRemarksInput" class="form-control" rows="3" placeholder="Specify reason for KYC rejection...">{{ $user->profile->kyc_remarks ?? '' }}</textarea>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 fw-semibold">
                        <iconify-icon icon="solar:check-read-bold-duotone" class="me-1 fs-16 align-middle"></iconify-icon> Save Status
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Detailed Information Tabs -->
    <div class="col-xl-8 col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 border-bottom">
                <ul class="nav nav-pills card-header-pills gap-1" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-medium px-3" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic-panel" type="button" role="tab">
                            DDC Details
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-medium px-3" id="adpl-tab" data-bs-toggle="tab" data-bs-target="#adpl-panel" type="button" role="tab">
                            ADPL Details
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-medium px-3" id="bank-tab" data-bs-toggle="tab" data-bs-target="#bank-panel" type="button" role="tab">
                            Bank & QR
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-medium px-3" id="kyc-tab" data-bs-toggle="tab" data-bs-target="#kyc-panel" type="button" role="tab">
                            KYC Documents
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-medium px-3" id="pincodes-tab" data-bs-toggle="tab" data-bs-target="#pincodes-panel" type="button" role="tab">
                            Assigned Pincodes
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="profileTabContent">
                    
                    <!-- TAB 1: DDC DETAILS -->
                    <div class="tab-pane fade show active" id="basic-panel" role="tabpanel">
                        <h5 class="fw-bold text-slate-800 mb-3">DDC Profile Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-slate-500 small mb-0">Coordinator Name</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $user->profile->ddc_owner_name ?? $user->name }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-slate-500 small mb-0">DDC Code</label>
                                <div class="fs-15 fw-semibold text-primary"><code class="fw-bold">{{ $user->profile->ddc_code ?? '—' }}</code></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-slate-500 small mb-0">Company Name</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $user->profile->company_name ?? '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-slate-500 small mb-0">Trade Name</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $user->profile->trade_name ?? '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-slate-500 small mb-0">Official Phone</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $user->mobile ?? ($user->profile->official_phone ?? '—') }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-slate-500 small mb-0">Official Email</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $user->email ?? ($user->profile->official_email ?? '—') }}</div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-slate-500 small mb-0">DDC GST Number</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $user->profile->ddc_gst_number ?? '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-slate-500 small mb-0">State</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $user->profile->state ?? '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-slate-500 small mb-0">District</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $user->profile->district ?? '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-slate-500 small mb-0">Pincode</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $user->profile->ddc_pincode ?? ($user->profile->pincode ?? '—') }}</div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-slate-500 small mb-0">Depot Address</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $user->profile->ddc_address ?? ($user->profile->address ?? '—') }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 2: ADPL DETAILS -->
                    <div class="tab-pane fade" id="adpl-panel" role="tabpanel">
                        <h5 class="fw-bold text-slate-800 mb-3">ADPL (Head Office) Details</h5>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label text-slate-500 small mb-0">ADPL Company Name</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $user->profile->adpl_company_name ?? '—' }}</div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label text-slate-500 small mb-0">ADPL GST Number</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $user->profile->adpl_gst_number ?? '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-slate-500 small mb-0">ADPL State</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $user->profile->adpl_state ?? '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-slate-500 small mb-0">ADPL District</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $user->profile->adpl_district ?? '—' }}</div>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label text-slate-500 small mb-0">ADPL Pin Code</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $user->profile->adpl_pincode ?? '—' }}</div>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label text-slate-500 small mb-0">ADPL Office Address</label>
                                <div class="fs-15 fw-semibold text-slate-800">{{ $user->profile->adpl_address ?? '—' }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- TAB 3: BANK DETAILS & QR -->
                    <div class="tab-pane fade" id="bank-panel" role="tabpanel">
                        <div class="row g-3">
                            <div class="col-md-8">
                                <h5 class="fw-bold text-slate-800 mb-3">Bank Details</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-slate-500 small mb-0">Account Holder Name</label>
                                        <div class="fs-15 fw-semibold text-slate-800">{{ $user->profile->account_holder_name ?? '—' }}</div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-slate-500 small mb-0">Bank Account Number</label>
                                        <div class="fs-15 fw-semibold text-slate-800">{{ $user->profile->bank_account_number ?? '—' }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-slate-500 small mb-0">IFSC Code</label>
                                        <div class="fs-15 fw-semibold text-slate-800">{{ $user->profile->ifsc_code ?? '—' }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-slate-500 small mb-0">Bank Name</label>
                                        <div class="fs-15 fw-semibold text-slate-800">{{ $user->profile->bank_name ?? '—' }}</div>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-slate-500 small mb-0">Branch Name</label>
                                        <div class="fs-15 fw-semibold text-slate-800">{{ $user->profile->branch_name ?? '—' }}</div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-4 text-center border-start">
                                <h5 class="fw-bold text-slate-800 mb-3">Payment QR</h5>
                                @if($user->profile && $user->profile->qr_code)
                                    <div class="border rounded p-2 bg-light d-inline-block">
                                        <img src="{{ filter_var($user->profile->qr_code, FILTER_VALIDATE_URL) ? $user->profile->qr_code : asset('storage/' . $user->profile->qr_code) }}" alt="QR Code" class="img-thumbnail" style="max-height: 150px;">
                                    </div>
                                @else
                                    <div class="text-muted small italic p-4 bg-light rounded text-center">No QR Code uploaded.</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- TAB 4: KYC DOCUMENTS -->
                    <div class="tab-pane fade" id="kyc-panel" role="tabpanel">
                        <h5 class="fw-bold text-slate-800 mb-3">KYC Verification Documents</h5>
                        <div class="row g-3">
                            @php
                                $kycDocs = [
                                    ['key' => 'gst_certificate', 'label' => 'GST Certificate', 'mandatory' => true],
                                    ['key' => 'aadhaar_card', 'label' => 'Aadhaar Card (Owner/Auth)', 'mandatory' => true],
                                    ['key' => 'fssai_certificate', 'label' => 'FSSAI Certificate', 'mandatory' => true],
                                    ['key' => 'cancelled_cheque', 'label' => 'Cancelled Cheque / Passbook', 'mandatory' => true],
                                    ['key' => 'master_depot_image', 'label' => 'Master Depot Image', 'mandatory' => true],
                                    ['key' => 'contract_document', 'label' => 'Contract Document', 'mandatory' => true],
                                    ['key' => 'ddc_agreement', 'label' => 'DDC Agreement Copy', 'mandatory' => true],
                                    ['key' => 'franchise_deposit_receipt', 'label' => 'Franchise Deposit Receipt', 'mandatory' => true],
                                    ['key' => 'trade_license', 'label' => 'Trade License (Optional)', 'mandatory' => false]
                                ];
                            @endphp

                            @foreach($kycDocs as $doc)
                                @php
                                    $uploadedPath = $user->profile->{$doc['key']} ?? null;
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

                    <!-- TAB 5: ASSIGNED PINCODES & AUDIT TRAIL -->
                    <div class="tab-pane fade" id="pincodes-panel" role="tabpanel">
                        <div class="row">
                            <!-- Grouped Pincode List -->
                            <div class="col-md-7">
                                <h5 class="fw-bold text-slate-800 mb-3">Service Area Mappings</h5>
                                
                                @php
                                    $groupedPincodes = $user->assignedPincodes->groupBy(function($pin) {
                                        $state = $pin->state->name ?? 'Unknown State';
                                        $district = $pin->district->name ?? 'Unknown District';
                                        return $state . ' > ' . $district;
                                    });
                                @endphp

                                @if($groupedPincodes->isEmpty())
                                    <div class="alert alert-info py-3 text-center border-0">
                                        <iconify-icon icon="solar:info-circle-bold-duotone" class="fs-24 text-info align-middle mb-1"></iconify-icon>
                                        <p class="mb-0 fw-semibold">No Pincodes Assigned</p>
                                        <span class="text-muted small">This DDC currently has no assigned service area pincodes.</span>
                                    </div>
                                @else
                                    <div class="accordion" id="pincodesAccordion">
                                        @php $i = 0; @endphp
                                        @foreach($groupedPincodes as $region => $pins)
                                            @php 
                                                $regionId = 'region-' . $i++; 
                                                // Extract state and district
                                                $parts = explode(' > ', $region);
                                                $stateName = $parts[0] ?? '—';
                                                $districtName = $parts[1] ?? '—';
                                            @endphp
                                            <div class="accordion-item mb-2 border rounded shadow-none overflow-hidden">
                                                <h2 class="accordion-header" id="heading-{{ $regionId }}">
                                                    <button class="accordion-button bg-light py-2 px-3 fw-semibold text-slate-700" type="button" data-bs-toggle="collapse" data-bs-target="#collapse-{{ $regionId }}" aria-expanded="true">
                                                        <i class="bx bx-map-pin me-1 text-primary"></i> 
                                                        {{ $districtName }} 
                                                        <span class="text-muted small fw-normal ms-1">({{ $stateName }}) — {{ $pins->count() }} Pincodes</span>
                                                    </button>
                                                </h2>
                                                <div id="collapse-{{ $regionId }}" class="accordion-collapse collapse show" aria-labelledby="heading-{{ $regionId }}">
                                                    <div class="accordion-body p-3">
                                                        <div class="d-flex flex-wrap gap-1.5">
                                                            @foreach($pins as $p)
                                                                <span class="badge bg-success-subtle text-success border border-success border-opacity-10 py-1.5 px-2.5 fs-12 fw-semibold rounded">
                                                                    {{ $p->pincode }}
                                                                </span>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>

                            <!-- Audit Trail / History -->
                            <div class="col-md-5 border-start">
                                <h5 class="fw-bold text-slate-800 mb-3 px-2">Assignment Activity Trail</h5>
                                <div class="px-2" style="max-height: 450px; overflow-y: auto;">
                                    @forelse($user->pincodeAuditLogs->sortByDesc('created_at') as $log)
                                        <div class="d-flex gap-2 mb-3 align-items-start border-bottom pb-2">
                                            <div class="flex-shrink-0">
                                                @if($log->action === 'ASSIGNED')
                                                    <span class="badge bg-success-subtle text-success p-1 rounded-circle">
                                                        <i class="bx bx-plus fs-12"></i>
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger p-1 rounded-circle">
                                                        <i class="bx bx-minus fs-12"></i>
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="flex-grow-1">
                                                <div class="d-flex justify-content-between align-items-center">
                                                    <span class="small fw-bold text-slate-800">
                                                        Pincode: {{ $log->pincode }}
                                                    </span>
                                                    <span class="small text-muted" style="font-size: 10px;">
                                                        {{ $log->created_at->format('d M, h:i A') }}
                                                    </span>
                                                </div>
                                                <p class="mb-0 text-muted small" style="font-size: 11px;">
                                                    Action: <span class="fw-semibold {{ $log->action === 'ASSIGNED' ? 'text-success' : 'text-danger' }}">{{ $log->action }}</span>
                                                </p>
                                                <p class="mb-0 text-muted small" style="font-size: 11px;">
                                                    By: <strong class="text-slate-700">{{ $log->modifier->name ?? 'System' }}</strong>
                                                </p>
                                            </div>
                                        </div>
                                    @empty
                                        <div class="text-center text-muted py-4 small">
                                            No activity logs found.
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const kycStatusSelect = document.getElementById('kycStatusSelect');
    const kycRemarksWrapper = document.getElementById('kycRemarksWrapper');
    const kycRemarksInput = document.getElementById('kycRemarksInput');

    if (kycStatusSelect) {
        kycStatusSelect.addEventListener('change', function() {
            if (this.value === 'REJECTED') {
                kycRemarksWrapper.style.display = 'block';
                kycRemarksInput.setAttribute('required', 'true');
            } else {
                kycRemarksWrapper.style.display = 'none';
                kycRemarksInput.removeAttribute('required');
            }
        });
    }
});
</script>
@endpush
