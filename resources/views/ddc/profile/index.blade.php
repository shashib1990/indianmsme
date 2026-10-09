@extends('layouts.ddc')
@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
    @php
        $profile = $user->profile;
        $currentStateId = null;
        if ($profile) {
            $stateObj = \App\Models\State::where('name', $profile->state)->first();
            if ($stateObj)
                $currentStateId = $stateObj->id;
        }
        $kycStatus = $profile->kyc_status ?? 'PENDING';
        $kycBadge = match ($kycStatus) {
            'APPROVED' => ['class' => 'bg-success-subtle text-success', 'label' => 'KYC Approved'],
            'REJECTED' => ['class' => 'bg-danger-subtle text-danger', 'label' => 'KYC Rejected'],
            'UNDER_REVIEW' => ['class' => 'bg-info-subtle text-info', 'label' => 'KYC Under Review'],
            default => ['class' => 'bg-warning-subtle text-warning', 'label' => 'KYC Pending'],
        };
    @endphp

    <div class="row">
        {{-- Left Profile Card --}}
        <div class="col-xl-4 col-lg-5">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-body text-center pt-4">
                    <div class="position-relative d-inline-block mb-3">
                        <img src="{{ $profile?->profile_image ? (filter_var($profile->profile_image, FILTER_VALIDATE_URL) ? $profile->profile_image : asset('storage/' . $profile->profile_image)) : asset('admin-assets/images/users/avatar-1.jpg') }}"
                            alt="Avatar" class="rounded-circle border border-3 border-light shadow" width="120" height="120"
                            style="object-fit:cover;" id="ddc-avatar-preview">
                        <div id="ddc-avatar-loader"
                            class="position-absolute top-0 start-0 w-100 h-100 rounded-circle bg-dark bg-opacity-50 d-none flex-column align-items-center justify-content-center text-white"
                            style="z-index:10;">
                            <span class="spinner-border spinner-border-sm mb-1"></span>
                            <span class="fs-10 fw-bold">Saving...</span>
                        </div>
                        <label for="profile_image_input"
                            class="btn btn-sm btn-primary rounded-circle position-absolute bottom-0 end-0 p-1 d-flex align-items-center justify-content-center"
                            style="width:32px;height:32px;cursor:pointer;" title="Change Photo">
                            <iconify-icon icon="solar:camera-bold-duotone" class="fs-16"></iconify-icon>
                        </label>
                    </div>
                    <h4 class="fw-bold text-slate-800 mb-1">{{ $user->name }}</h4>
                    <p class="text-muted mb-2"><code class="fs-13 fw-semibold">{{ $profile->ddc_code ?? 'N/A' }}</code></p>
                    <div class="d-flex justify-content-center gap-2 mb-3 flex-wrap">
                        @if($user->status === '1')
                            <span class="badge bg-success-subtle text-success px-3 py-1 fs-12 rounded-pill">Active</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger px-3 py-1 fs-12 rounded-pill">Inactive</span>
                        @endif
                        <span
                            class="badge {{ $kycBadge['class'] }} px-3 py-1 fs-12 rounded-pill">{{ $kycBadge['label'] }}</span>
                    </div>
                    @if($kycStatus === 'REJECTED' && $profile->kyc_remarks)
                        <div class="alert alert-danger border-0 text-start py-2 px-3 small mb-3">
                            <strong>Rejection Reason:</strong><br>{{ $profile->kyc_remarks }}
                        </div>
                    @endif
                    <div class="text-start mt-3 pt-3 border-top">
                        <p class="text-muted mb-2 small text-uppercase fw-semibold">Quick Details</p>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <iconify-icon icon="solar:letter-bold-duotone" class="text-primary fs-18"></iconify-icon>
                            <span class="text-slate-700 fs-14 text-truncate">{{ $user->email }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <iconify-icon icon="solar:phone-bold-duotone" class="text-primary fs-18"></iconify-icon>
                            <span class="text-slate-700 fs-14">{{ $user->mobile ?? 'Not Provided' }}</span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <iconify-icon icon="solar:shop-bold-duotone" class="text-primary fs-18"></iconify-icon>
                            <span
                                class="text-slate-700 fs-14 text-truncate">{{ $profile->company_name ?? 'Not Provided' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- QR Code display (if set by admin) --}}
            @if($profile?->qr_code)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3 d-flex align-items-center gap-2 border-bottom">
                        <iconify-icon icon="solar:qr-code-bold-duotone" class="text-primary fs-18"></iconify-icon>
                        <h6 class="fw-bold text-slate-800 mb-0">Payment QR Code</h6>
                    </div>
                    <div class="card-body text-center py-3">
                        <img src="{{ filter_var($profile->qr_code, FILTER_VALIDATE_URL) ? $profile->qr_code : asset('storage/' . $profile->qr_code) }}"
                            alt="QR Code" class="img-fluid rounded" style="max-height:180px;">
                    </div>
                </div>
            @endif
        </div>

        {{-- Right Content Tabs --}}
        <div class="col-xl-8 col-lg-7">
            <input type="file" name="profile_image" id="profile_image_input" class="d-none" accept="image/*"
                onchange="previewImage(this)">

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3">
                    <ul class="nav nav-pills card-header-pills" id="profileTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button
                                class="nav-link {{ session('active_tab', 'basic') === 'basic' ? 'active' : '' }} fw-medium px-4"
                                id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic-panel" type="button"
                                role="tab">Basic Profile</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ session('active_tab') === 'kyc' ? 'active' : '' }} fw-medium px-4"
                                id="kyc-tab" data-bs-toggle="tab" data-bs-target="#kyc-panel" type="button" role="tab">KYC
                                Documents</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ session('active_tab') === 'bank' ? 'active' : '' }} fw-medium px-4"
                                id="bank-tab" data-bs-toggle="tab" data-bs-target="#bank-panel" type="button"
                                role="tab">Bank Details</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ session('active_tab') === 'pincodes' ? 'active' : '' }} fw-medium px-4"
                                id="pincodes-tab" data-bs-toggle="tab" data-bs-target="#pincodes-panel" type="button"
                                role="tab">Delivered Pincodes</button>
                        </li>
                    </ul>
                </div>
                <div class="card-body">
                    <div class="tab-content">

                        {{-- TAB 1: BASIC PROFILE --}}
                        <div class="tab-pane fade {{ session('active_tab', 'basic') === 'basic' ? 'show active' : '' }}"
                            id="basic-panel" role="tabpanel">
                            <form action="{{ route('ddc.profile.update') }}" method="POST" enctype="multipart/form-data"
                                class="profile-form">
                                @csrf @method('PUT')
                                <input type="hidden" name="active_tab" value="basic">
                                <h5 class="fw-bold text-slate-800 mb-3">DDC Depot Profile</h5>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-slate-700 fw-semibold">DDC Code</label>
                                        <input type="text" class="form-control bg-light fw-bold text-primary"
                                            value="{{ $profile->ddc_code ?? 'N/A' }}" readonly>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-slate-700 fw-semibold">Coordinator / Owner Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="name" class="form-control"
                                            value="{{ old('name', $profile->ddc_owner_name ?? $user->name) }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-slate-700 fw-semibold">Company Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="company_name" class="form-control"
                                            value="{{ old('company_name', $profile->company_name ?? '') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-slate-700 fw-semibold">Trade Name <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="trade_name" class="form-control"
                                            value="{{ old('trade_name', $profile->trade_name ?? '') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-slate-700 fw-semibold">Contact Phone <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="phone" class="form-control"
                                            value="{{ old('phone', $profile->official_phone ?? $user->mobile) }}" required
                                            maxlength="10" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-slate-700 fw-semibold">Email Address <span
                                                class="text-danger">*</span></label>
                                        <input type="email" name="email" class="form-control"
                                            value="{{ old('email', $profile->official_email ?? $user->email) }}" required>
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label text-slate-700 fw-semibold">DDC GST Number <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="ddc_gst_number" class="form-control text-uppercase"
                                            value="{{ old('ddc_gst_number', $profile->ddc_gst_number ?? '') }}" required maxlength="15" oninput="this.value = this.value.toUpperCase().slice(0, 15)"
                                            placeholder="15-digit GST Number">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-slate-700 fw-semibold">State <span
                                                class="text-danger">*</span></label>
                                        <select name="state" id="state" class="form-select s-select" required>
                                            <option value="">Select State</option>
                                            @foreach($states as $st)
                                                <option value="{{ $st->id }}" {{ old('state', $currentStateId) == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-slate-700 fw-semibold">District <span
                                                class="text-danger">*</span></label>
                                        <select name="district" id="district" class="form-select s-select" required>
                                            <option value="">Select District</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label text-slate-700 fw-semibold">Depot Pincode <span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="pincode" class="form-control"
                                            value="{{ old('pincode', $profile->ddc_pincode ?? ($profile->pincode ?? '')) }}" required
                                            placeholder="6-digit Pincode" maxlength="6"
                                            oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="form-label text-slate-700 fw-semibold">DDC Depot Address <span
                                                class="text-danger">*</span></label>
                                        <textarea name="address" class="form-control" rows="3"
                                            required>{{ old('address', $profile->ddc_address ?? '') }}</textarea>
                                    </div>
                                </div>
                                <div class="text-end mt-4">
                                    <button type="submit" class="btn btn-primary px-4 fw-semibold">Save Basic Details</button>
                                </div>
                            </form>
                        </div>

                        {{-- TAB 2: KYC DOCUMENTS --}}
                        <div class="tab-pane fade {{ session('active_tab') === 'kyc' ? 'show active' : '' }}"
                            id="kyc-panel" role="tabpanel">
                            <form action="{{ route('ddc.profile.update') }}" method="POST" enctype="multipart/form-data"
                                class="profile-form">
                                @csrf @method('PUT')
                                <input type="hidden" name="active_tab" value="kyc">
                                <h5 class="fw-bold text-slate-800 mb-1">KYC & Legal Documents</h5>
                                <p class="text-muted fs-13 mb-3">Uploading new documents will send your profile for Admin verification.</p>

                                <div class="row g-3">
                                    @php
                                        $kycDocs = [
                                            ['name' => 'master_depot_image', 'label' => 'Master Depot Photo', 'file' => $profile->master_depot_image ?? null, 'type' => 'image'],
                                            ['name' => 'contract_document', 'label' => 'Agreement / Contract Doc', 'file' => $profile->contract_document ?? null, 'type' => 'pdf'],
                                            ['name' => 'gst_certificate', 'label' => 'GST Certificate', 'file' => $profile->gst_certificate ?? null, 'type' => 'file'],
                                            ['name' => 'aadhaar_card', 'label' => 'Aadhaar Card Copy', 'file' => $profile->aadhaar_card ?? null, 'type' => 'file'],
                                            ['name' => 'fssai_certificate', 'label' => 'FSSAI License Certificate', 'file' => $profile->fssai_certificate ?? null, 'type' => 'file'],
                                            ['name' => 'cancelled_cheque', 'label' => 'Cancelled Cheque', 'file' => $profile->cancelled_cheque ?? null, 'type' => 'file'],
                                        ];
                                    @endphp

                                    @foreach($kycDocs as $doc)
                                        <div class="col-md-6">
                                            <div class="card bg-light border-0 p-3 h-100">
                                                <label class="form-label text-slate-800 fw-semibold fs-13 mb-1">{{ $doc['label'] }}</label>
                                                @if($doc['file'])
                                                    <div class="d-flex align-items-center gap-2 mb-2 p-2 rounded bg-white border">
                                                        <iconify-icon icon="solar:document-text-bold-duotone" class="text-primary fs-20"></iconify-icon>
                                                        <span class="fs-12 text-slate-700 text-truncate flex-grow-1">Document Uploaded</span>
                                                        <a href="{{ filter_var($doc['file'], FILTER_VALIDATE_URL) ? $doc['file'] : asset('storage/' . $doc['file']) }}"
                                                           target="_blank" class="btn btn-xs btn-outline-primary">View</a>
                                                    </div>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary align-self-start mb-2 fs-11">Not Uploaded</span>
                                                @endif
                                                <input type="file" name="{{ $doc['name'] }}" class="form-control form-control-sm"
                                                    accept="{{ $doc['type'] === 'image' ? 'image/jpeg,image/png' : ($doc['type'] === 'pdf' ? 'application/pdf' : '.pdf,.jpg,.png') }}">
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                <div class="text-end mt-4">
                                    <button type="submit" class="btn btn-primary px-4 fw-semibold">Save KYC Documents</button>
                                </div>
                            </form>
                        </div>

                        {{-- TAB 3: BANK DETAILS --}}
                        <div class="tab-pane fade {{ session('active_tab') === 'bank' ? 'show active' : '' }}"
                            id="bank-panel" role="tabpanel">
                            <form action="{{ route('ddc.profile.update') }}" method="POST" enctype="multipart/form-data"
                                class="profile-form">
                                @csrf @method('PUT')
                                <input type="hidden" name="active_tab" value="bank">
                                <h5 class="fw-bold text-slate-800 mb-3">Bank Account Information</h5>

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-slate-700 fw-semibold">IFSC Code <span class="text-danger">*</span></label>
                                        <input type="text" name="ifsc_code" id="ifsc_code" class="form-control text-uppercase"
                                            value="{{ old('ifsc_code', $profile->ifsc_code ?? '') }}" required maxlength="11"
                                            placeholder="e.g. SBIN0001234" oninput="this.value = this.value.toUpperCase()">
                                        <span class="fs-11 text-muted mt-1 d-block">Entering 11-digit IFSC automatically fetches Bank & Branch Name.</span>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-slate-700 fw-semibold">Bank Name <span class="text-danger">*</span></label>
                                        <input type="text" name="bank_name" id="bank_name" class="form-control"
                                            value="{{ old('bank_name', $profile->bank_name ?? '') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-slate-700 fw-semibold">Branch Name <span class="text-danger">*</span></label>
                                        <input type="text" name="branch_name" id="branch_name" class="form-control"
                                            value="{{ old('branch_name', $profile->branch_name ?? '') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-slate-700 fw-semibold">Account Number <span class="text-danger">*</span></label>
                                        <input type="text" name="account_number" class="form-control"
                                            value="{{ old('account_number', $profile->account_number ?? '') }}" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-slate-700 fw-semibold">Account Holder Name <span class="text-danger">*</span></label>
                                        <input type="text" name="account_holder_name" class="form-control"
                                            value="{{ old('account_holder_name', $profile->account_holder_name ?? '') }}" required>
                                    </div>

                                    <!-- Payment QR Code Upload -->
                                    <div class="col-md-12 mt-4 pt-3 border-top">
                                        <h6 class="fw-bold text-slate-800 mb-2 d-flex align-items-center gap-2">
                                            <iconify-icon icon="solar:qr-code-bold-duotone" class="text-primary fs-18"></iconify-icon>
                                            Payment QR Code (Optional)
                                        </h6>
                                        <p class="text-muted fs-12 mb-3">Upload your QR Code image for payments. If no QR Code is uploaded, the QR option will not be displayed.</p>

                                        <div class="row align-items-center g-3">
                                            <div class="col-md-6">
                                                <label class="form-label text-slate-700 fw-semibold">Upload QR Image</label>
                                                <input type="file" name="qr_code" class="form-control" accept="image/jpeg,image/png,image/jpg,image/webp">
                                                <span class="fs-11 text-muted mt-1 d-block">Supported formats: JPG, PNG, WEBP (Max size: 5MB)</span>
                                            </div>

                                            @if($profile?->qr_code)
                                                <div class="col-md-6">
                                                    <div class="p-3 bg-light border rounded-3 d-flex align-items-center justify-content-between">
                                                        <div class="d-flex align-items-center gap-3">
                                                            <img src="{{ filter_var($profile->qr_code, FILTER_VALIDATE_URL) ? $profile->qr_code : asset('storage/' . $profile->qr_code) }}"
                                                                 alt="Current QR Code" class="rounded border bg-white p-1" style="height: 60px; width: 60px; object-fit: contain;">
                                                            <div>
                                                                <span class="fw-semibold text-slate-800 d-block fs-13">Uploaded QR Code</span>
                                                                <a href="{{ filter_var($profile->qr_code, FILTER_VALIDATE_URL) ? $profile->qr_code : asset('storage/' . $profile->qr_code) }}"
                                                                   target="_blank" class="fs-12 text-primary text-decoration-none fw-semibold">
                                                                    <iconify-icon icon="solar:eye-bold-duotone" class="align-middle me-1"></iconify-icon>View Image
                                                                </a>
                                                            </div>
                                                        </div>
                                                        <div class="form-check text-end">
                                                            <input class="form-check-input" type="checkbox" name="remove_qr_code" id="remove_qr_code" value="1">
                                                            <label class="form-check-label text-danger fs-12 fw-semibold" for="remove_qr_code">
                                                                Remove QR
                                                            </label>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>

                                <div class="text-end mt-4">
                                    <button type="submit" class="btn btn-primary px-4 fw-semibold">Save Bank & QR Details</button>
                                </div>
                            </form>
                        </div>

                        {{-- TAB 4: DELIVERED PINCODES --}}
                        <div class="tab-pane fade {{ session('active_tab') === 'pincodes' ? 'show active' : '' }}"
                            id="pincodes-panel" role="tabpanel">
                            <div class="d-flex align-items-center justify-content-between mb-4 border-bottom pb-3">
                                <div>
                                    <h5 class="fw-bold text-slate-800 mb-1">Delivered Area Pincodes</h5>
                                    <p class="text-muted fs-12 mb-0">List of active pincodes covered for order deliveries & canteen supply under your DDC.</p>
                                </div>
                                <span class="badge bg-primary px-3 py-2 fs-12 fw-bold rounded-pill">
                                    <iconify-icon icon="solar:map-point-wave-bold-duotone" class="align-middle me-1 fs-15"></iconify-icon>
                                    {{ count($assignedPincodes ?? []) }} Delivered Pincodes
                                </span>
                            </div>

                            @if(!empty($assignedPincodes) && count($assignedPincodes) > 0)
                                <div class="row g-3">
                                    @foreach($assignedPincodes as $pin)
                                        <div class="col-6 col-sm-4 col-md-3">
                                            <div class="p-3 rounded-3 bg-light border text-center shadow-xs">
                                                <iconify-icon icon="solar:map-point-bold-duotone" class="text-primary fs-28 mb-1.5 d-block mx-auto"></iconify-icon>
                                                <div class="fw-bold text-slate-900 fs-16 mb-0">{{ $pin }}</div>
                                                <span class="badge bg-success-subtle text-success fs-10 fw-bold px-2 py-0.5 rounded-pill mt-1.5">Delivered Area</span>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-center py-5 bg-light rounded-3 border">
                                    <iconify-icon icon="solar:map-point-broken" class="fs-48 text-muted mb-2"></iconify-icon>
                                    <h6 class="fw-semibold text-slate-700">No Delivered Pincodes Found</h6>
                                    <p class="text-muted fs-12 mb-0">No pincodes have been assigned to your DDC for delivery yet.</p>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function previewImage(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    document.getElementById('ddc-avatar-preview').src = e.target.result;
                    if (window.showToast) showToast('Photo selected. Click "Save" on any tab to apply.', 'info');
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        document.addEventListener('DOMContentLoaded', function () {
            const stateEl = document.getElementById('state');
            const districtEl = document.getElementById('district');

            function updateSelect(el, options, selected) {
                if (el.tomselect) {
                    const ts = el.tomselect;
                    ts.clear(); ts.clearOptions(); ts.addOption(options); ts.setValue(selected); ts.refreshOptions(false);
                } else {
                    el.innerHTML = options.map(o => `<option value="${o.value}" ${o.value == selected ? 'selected' : ''}>${o.text}</option>`).join('');
                }
            }

            if (stateEl) {
                stateEl.addEventListener('change', function () {
                    const sid = this.value;
                    if (!sid) { updateSelect(districtEl, [{ value: '', text: 'Select District' }], ''); return; }
                    fetch(`/api/locations/districts?state_id=${sid}`).then(r => r.json()).then(data => {
                        updateSelect(districtEl, [{ value: '', text: 'Select District' }, ...data.map(d => ({ value: d.name, text: d.name }))], '');
                    });
                });
            }

            // Prefill district on load
            const initStateId = "{{ old('state', $currentStateId) }}";
            const initDistrict = "{{ old('district', $profile->district ?? '') }}";
            if (initStateId) {
                fetch(`/api/locations/districts?state_id=${initStateId}`).then(r => r.json()).then(data => {
                    updateSelect(districtEl, [{ value: '', text: 'Select District' }, ...data.map(d => ({ value: d.name, text: d.name }))], initDistrict);
                });
            }

            // IFSC auto-fetch
            const ifscEl = document.getElementById('ifsc_code');
            const bankEl = document.getElementById('bank_name');
            const branchEl = document.getElementById('branch_name');
            if (ifscEl && bankEl) {
                const handleIfscFetch = function () {
                    const code = ifscEl.value.trim().toUpperCase();
                    ifscEl.value = code;
                    if (code.length === 11) {
                        bankEl.value = 'Fetching...';
                        branchEl.value = 'Fetching...';
                        fetch('https://ifsc.razorpay.com/' + code)
                            .then(r => { if (!r.ok) throw new Error(); return r.json(); })
                            .then(d => { bankEl.value = d.BANK; branchEl.value = d.BRANCH; })
                            .catch(() => {
                                bankEl.value = '';
                                branchEl.value = '';
                                if (window.showToast) showToast('Invalid IFSC Code. Please check & try again.', 'error');
                            });
                    }
                };
                ifscEl.addEventListener('input', handleIfscFetch);
                ifscEl.addEventListener('change', handleIfscFetch);
            }

            // KYC file change preview
            document.querySelectorAll('#kyc-panel input[type="file"]').forEach(input => {
                input.addEventListener('change', function () {
                    let container = this.parentNode.querySelector('.kyc-preview-new');
                    if (!container) { container = document.createElement('div'); container.className = 'kyc-preview-new d-flex align-items-center gap-2 mt-2 p-2 rounded border border-warning-subtle bg-warning-subtle'; this.parentNode.insertBefore(container, this.nextSibling); }
                    const existing = this.parentNode.querySelector('.bg-light.rounded.border');
                    if (this.files && this.files[0]) {
                        if (existing) existing.style.setProperty('display', 'none', 'important');
                        const f = this.files[0]; const isImg = f.type.startsWith('image/'); const isPdf = f.type === 'application/pdf';
                        container.innerHTML = `<span class="badge bg-warning text-dark">New file selected: ${f.name.substring(0, 25)}${f.name.length > 25 ? '...' : ''}</span>`;
                        container.style.display = 'flex';
                    } else {
                        if (existing) existing.style.removeProperty('display');
                        container.remove();
                    }
                });
            });

            // DDC profile forms submission handler
            document.querySelectorAll('.profile-form').forEach(form => {
                form.addEventListener('submit', function () {
                    const fileInput = document.getElementById('profile_image_input');
                    if (fileInput && fileInput.files && fileInput.files.length > 0) {
                        this.appendChild(fileInput);
                        const loader = document.getElementById('ddc-avatar-loader');
                        if (loader) {
                            loader.classList.remove('d-none');
                            loader.classList.add('d-flex');
                        }
                    }
                });
            });
        });
    </script>
@endpush