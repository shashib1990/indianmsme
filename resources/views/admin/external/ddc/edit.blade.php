@extends('layouts.admin')

@section('title', 'Edit DDC')
@section('page-title', 'Edit DDC')

@section('content')
<!-- Back button & header -->
<x-admin.page-header 
    title="Edit DDC: {{ $user->name }}" 
    subtitle="Update DDC account and profile details."
    :actions="[
        [
            'label' => 'Back to Manage DDC',
            'icon' => 'solar:arrow-left-bold-duotone',
            'url' => route('admin.external.ddc.index'),
            'permission' => 'users.view',
            'btnClass' => 'btn-outline-secondary'
        ]
    ]"
/>

@php
    $currentStateId = null;
    $currentAdplStateId = null;
    if ($user->profile) {
        $stateObj = \App\Models\State::where('name', $user->profile->state)->first();
        if ($stateObj) {
            $currentStateId = $stateObj->id;
        }
        $adplStateObj = \App\Models\State::where('name', $user->profile->adpl_state)->first();
        if ($adplStateObj) {
            $currentAdplStateId = $adplStateObj->id;
        }
    }
@endphp

<div class="row mt-3">
    <div class="col-xl-12">
        <form action="{{ route('admin.external.ddc.update', $user->id) }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            @csrf
            @method('PUT')
            
            <div class="row g-3">
                <!-- Left: Form Stack -->
                <div class="col-lg-8">
                    <div class="d-flex flex-column gap-3">
                        
                        <!-- Card 1: DDC Account Details -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex align-items-center gap-2 border-bottom">
                                <iconify-icon icon="solar:user-id-bold-duotone" class="text-primary fs-20"></iconify-icon>
                                <h5 class="fw-bold text-slate-800 mb-0">DDC Account Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <!-- DDC Coordinator Name -->
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-slate-700">DDC Coordinator Name <span class="text-danger">*</span></label>
                                        <input type="text" name="ddc_owner_name" class="form-control" placeholder="DDC Coordinator Name" value="{{ old('ddc_owner_name', $user->profile->ddc_owner_name ?? $user->name) }}" required>
                                    </div>

                                    <!-- Company Name -->
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-slate-700">Company Name <span class="text-danger">*</span></label>
                                        <input type="text" name="company_name" class="form-control" placeholder="Company Name" value="{{ old('company_name', $user->profile->company_name ?? '') }}" required>
                                    </div>

                                    <!-- Trade Name -->
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-slate-700">Trade Name <span class="text-danger">*</span></label>
                                        <input type="text" name="trade_name" class="form-control" placeholder="Trade Name" value="{{ old('trade_name', $user->profile->trade_name ?? '') }}" required>
                                    </div>

                                    <!-- Official Phone -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-slate-700">Official Phone <span class="text-danger">*</span></label>
                                        <input type="text" name="official_phone" class="form-control" placeholder="Official Phone" value="{{ old('official_phone', $user->profile->official_phone ?? $user->mobile) }}" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                                    </div>

                                    <!-- Official Email -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-slate-700">Official Email <span class="text-danger">*</span></label>
                                        <input type="email" name="official_email" class="form-control" placeholder="Official Email" value="{{ old('official_email', $user->profile->official_email ?? $user->email) }}" required>
                                    </div>

                                    <!-- Password Fields -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-slate-700">Password <span class="text-muted">(Leave blank to keep current)</span></label>
                                        <div class="input-group">
                                            <input type="password" name="password" id="passwordField" class="form-control" placeholder="New Password">
                                            <button class="btn btn-outline-secondary" type="button" id="togglePwd">
                                                <i class="bx bx-hide" id="togglePwdIcon"></i>
                                            </button>
                                            <button type="button" class="btn btn-secondary" id="generatePwdBtn">
                                                <i class="bx bx-key me-1"></i> Generate
                                            </button>
                                        </div>
                                    </div>

                                    <!-- DDC Code (Read-only on edit) -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-slate-700">DDC Code</label>
                                        <input type="text" class="form-control bg-light fw-bold text-primary" value="{{ $user->profile->ddc_code ?? 'Not Assigned' }}" readonly>
                                        <input type="hidden" name="ddc_code" value="{{ $user->profile->ddc_code ?? '' }}">
                                        <small class="text-muted">DDC code cannot be changed after creation.</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 2: DDC (Depot) Details -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex align-items-center gap-2 border-bottom">
                                <iconify-icon icon="solar:shop-bold-duotone" class="text-primary fs-20"></iconify-icon>
                                <h5 class="fw-bold text-slate-800 mb-0">DDC (Depot) Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <!-- DDC GST Number -->
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold text-slate-700">DDC GST Number <span class="text-danger">*</span></label>
                                        <input type="text" name="ddc_gst_number" class="form-control text-uppercase" placeholder="15-digit GST No." value="{{ old('ddc_gst_number', $user->profile->ddc_gst_number ?? '') }}" minlength="15" maxlength="15" oninput="this.value = this.value.toUpperCase().slice(0, 15)" required>
                                    </div>

                                    <!-- State -->
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-slate-700">State <span class="text-danger">*</span></label>
                                        <select id="state" name="state" class="form-select s-select" required>
                                            <option value="">Select State</option>
                                            @foreach($states as $st)
                                                <option value="{{ $st->id }}" {{ old('state', $currentStateId) == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- District -->
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-slate-700">District <span class="text-danger">*</span></label>
                                        <select id="district" name="district" class="form-select s-select" required>
                                            <option value="">Select District</option>
                                        </select>
                                    </div>

                                    <!-- DDC Pincode -->
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-slate-700">Depot Pin Code <span class="text-danger">*</span></label>
                                        <input type="text" name="ddc_pincode" class="form-control" placeholder="6-digit Pincode" value="{{ old('ddc_pincode', $user->profile->ddc_pincode ?? '') }}" maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                                    </div>

                                    <!-- DDC Address -->
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold text-slate-700">DDC Depot Address <span class="text-danger">*</span></label>
                                        <textarea name="ddc_address" class="form-control" rows="3" placeholder="Enter Full DDC Depot Address" required>{{ old('ddc_address', $user->profile->ddc_address ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 3: ADPL (Head Office) Details -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex align-items-center gap-2 border-bottom">
                                <iconify-icon icon="solar:city-bold-duotone" class="text-primary fs-20"></iconify-icon>
                                <h5 class="fw-bold text-slate-800 mb-0">ADPL (Head Office) Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <!-- ADPL Company Name -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-slate-700">ADPL Company Name <span class="text-danger">*</span></label>
                                        <input type="text" name="adpl_company_name" class="form-control" placeholder="ADPL Company Name" value="{{ old('adpl_company_name', $user->profile->adpl_company_name ?? '') }}" required>
                                    </div>

                                    <!-- ADPL GST Number -->
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-slate-700">ADPL GST Number <span class="text-danger">*</span></label>
                                        <input type="text" name="adpl_gst_number" class="form-control text-uppercase" placeholder="15-digit GST No." value="{{ old('adpl_gst_number', $user->profile->adpl_gst_number ?? '') }}" minlength="15" maxlength="15" oninput="this.value = this.value.toUpperCase().slice(0, 15)" required>
                                    </div>

                                    <!-- ADPL State -->
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-slate-700">ADPL State <span class="text-danger">*</span></label>
                                        <select id="adpl_state" name="adpl_state" class="form-select s-select" required>
                                            <option value="">Select State</option>
                                            @foreach($states as $st)
                                                <option value="{{ $st->id }}" {{ old('adpl_state', $currentAdplStateId) == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- ADPL District -->
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-slate-700">ADPL District <span class="text-danger">*</span></label>
                                        <select id="adpl_district" name="adpl_district" class="form-select s-select" required>
                                            <option value="">Select District</option>
                                        </select>
                                    </div>

                                    <!-- ADPL Pincode -->
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-slate-700">ADPL Pin Code <span class="text-danger">*</span></label>
                                        <input type="text" name="adpl_pincode" class="form-control" placeholder="6-digit Pincode" value="{{ old('adpl_pincode', $user->profile->adpl_pincode ?? '') }}" maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                                    </div>

                                    <!-- ADPL Address -->
                                    <div class="col-md-12">
                                        <label class="form-label fw-semibold text-slate-700">ADPL Address <span class="text-danger">*</span></label>
                                        <textarea name="adpl_address" class="form-control" rows="3" placeholder="Enter Full ADPL Office Address" required>{{ old('adpl_address', $user->profile->adpl_address ?? '') }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 4: Bank Details -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex align-items-center gap-2 border-bottom">
                                <iconify-icon icon="solar:card-bold-duotone" class="text-primary fs-20"></iconify-icon>
                                <h5 class="fw-bold text-slate-800 mb-0">Bank Details</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-slate-700">Account Holder Name</label>
                                        <input type="text" name="account_holder_name" class="form-control" placeholder="As per bank records" value="{{ old('account_holder_name', $user->profile->account_holder_name ?? '') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-slate-700">Bank Account Number</label>
                                        <input type="text" name="bank_account_number" class="form-control" placeholder="e.g. 50200012345678" value="{{ old('bank_account_number', $user->profile->bank_account_number ?? '') }}" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-slate-700">IFSC Code</label>
                                        <input type="text" name="ifsc_code" id="ifsc_code" class="form-control" placeholder="e.g. HDFC0000123" value="{{ old('ifsc_code', $user->profile->ifsc_code ?? '') }}" oninput="this.value = this.value.toUpperCase()">
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-slate-700">Bank Name</label>
                                        <input type="text" name="bank_name" id="bank_name" class="form-control" placeholder="Auto-fetched via IFSC" value="{{ old('bank_name', $user->profile->bank_name ?? '') }}" readonly>
                                    </div>
                                    <div class="col-md-4">
                                        <label class="form-label fw-semibold text-slate-700">Branch Name</label>
                                        <input type="text" name="branch_name" id="branch_name" class="form-control" placeholder="Auto-fetched via IFSC" value="{{ old('branch_name', $user->profile->branch_name ?? '') }}" readonly>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Card 5: Payment QR Code -->
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-0 py-3 d-flex align-items-center gap-2 border-bottom">
                                <iconify-icon icon="solar:qr-code-bold-duotone" class="text-primary fs-20"></iconify-icon>
                                <h5 class="fw-bold text-slate-800 mb-0">Payment QR Code</h5>
                            </div>
                            <div class="card-body">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold text-slate-700">UPI/Bank QR Code</label>
                                        <input type="file" name="qr_code" class="form-control mb-1" accept="image/*">
                                        <small class="text-muted">Upload UPI/Bank QR code image. Max 5MB.</small>
                                    </div>
                                    <div class="col-md-6 text-center">
                                        @if($user->profile && $user->profile->qr_code)
                                            <div class="border rounded p-2 d-inline-block bg-light">
                                                <img src="{{ filter_var($user->profile->qr_code, FILTER_VALIDATE_URL) ? $user->profile->qr_code : asset('storage/' . $user->profile->qr_code) }}" alt="QR Code" class="img-thumbnail" style="max-height: 120px;">
                                            </div>
                                        @else
                                            <span class="text-muted small d-block mt-3">No QR code uploaded yet.</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- Right: Required Documents (col-lg-4) -->
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-0 py-3 d-flex align-items-center gap-2 border-bottom">
                            <iconify-icon icon="solar:document-bold-duotone" class="text-primary fs-20"></iconify-icon>
                            <h5 class="fw-bold text-slate-800 mb-0">Documents & KYC</h5>
                        </div>
                        <div class="card-body">
                            
                            @php
                                $documents = [
                                    ['name' => 'master_depot_image', 'label' => 'Master Depot Image', 'accept' => 'image/jpeg,image/jpg', 'help' => 'Only JPG/JPEG format. Max 5MB.'],
                                    ['name' => 'contract_document', 'label' => 'Contract Document', 'accept' => 'application/pdf', 'help' => 'Only PDF format. Max 5MB.'],
                                    ['name' => 'gst_certificate', 'label' => 'GST Certificate', 'accept' => 'application/pdf,image/*', 'help' => 'PDF or Image. Max 5MB.'],
                                    ['name' => 'aadhaar_card', 'label' => 'Aadhaar Card (Owner/Authorized Person)', 'accept' => 'application/pdf,image/*', 'help' => 'PDF or Image. Max 5MB.'],
                                    ['name' => 'fssai_certificate', 'label' => 'FSSAI Certificate', 'accept' => 'application/pdf,image/*', 'help' => 'PDF or Image. Max 5MB.'],
                                    ['name' => 'cancelled_cheque', 'label' => 'Cancelled Cheque / Bank Passbook', 'accept' => 'application/pdf,image/*', 'help' => 'PDF or Image. Max 5MB.'],
                                    ['name' => 'trade_license', 'label' => 'Trade License (Optional)', 'accept' => 'application/pdf,image/*', 'help' => 'PDF or Image. Max 5MB.'],
                                    ['name' => 'ddc_agreement', 'label' => 'DDC Agreement Copy', 'accept' => 'application/pdf,image/*', 'help' => 'PDF or Image. Max 5MB.'],
                                    ['name' => 'franchise_deposit_receipt', 'label' => 'Franchise Deposit Receipt', 'accept' => 'application/pdf,image/*', 'help' => 'PDF or Image. Max 5MB.']
                                ];
                            @endphp

                            @foreach($documents as $doc)
                                <div class="mb-3 pb-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                                    <label class="form-label fw-semibold text-slate-700 mb-1">{{ $doc['label'] }}</label>
                                    <input type="file" name="{{ $doc['name'] }}" class="form-control mb-1" accept="{{ $doc['accept'] }}">
                                    <small class="text-muted d-block mb-1">{{ $doc['help'] }}</small>
                                    
                                    @if($user->profile && $user->profile->{$doc['name']})
                                        <div class="mt-1">
                                            <a href="{{ filter_var($user->profile->{$doc['name']}, FILTER_VALIDATE_URL) ? $user->profile->{$doc['name']} : asset('storage/' . $user->profile->{$doc['name']}) }}" target="_blank" class="btn btn-xs btn-outline-info py-0 px-2 fs-11">
                                                <i class="bx bx-show me-1"></i> View Current
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                        </div>
                    </div>
                </div>
            </div>

            <!-- Submit Button Footer -->
            <div class="row mt-4">
                <div class="col-xl-12 text-end">
                    <button type="submit" id="submitBtn" class="btn btn-primary px-4 py-2 fw-semibold">
                        <i class="bx bx-save me-1"></i> Update DDC
                    </button>
                </div>
            </div>

        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener("DOMContentLoaded", function() {
    const districtSelect = document.getElementById('district');
    const adplDistrictSelect = document.getElementById('adpl_district');
    const stateSelect = document.getElementById('state');
    const adplStateSelect = document.getElementById('adpl_state');
    const submitBtn = document.getElementById('submitBtn');

    // Helper to safely clear and update TomSelect options if initialized, or fall back to standard HTML select
    function updateTomSelect(element, options, selectedValue) {
        if (element.tomselect) {
            const ts = element.tomselect;
            ts.clear();
            ts.clearOptions();
            ts.addOption(options);
            ts.setValue(selectedValue);
            ts.refreshOptions(false);
        } else {
            element.innerHTML = '';
            options.forEach(opt => {
                const o = document.createElement('option');
                o.value = opt.value;
                o.textContent = opt.text;
                if (opt.value === selectedValue) {
                    o.selected = true;
                }
                element.appendChild(o);
            });
        }
    }

    // Dynamic District Loading for DDC Details
    if (stateSelect) {
        stateSelect.addEventListener('change', function() {
            const stateId = this.value;
            if (!stateId) {
                updateTomSelect(districtSelect, [{ value: '', text: 'Select District' }], '');
                return;
            }
            fetch(`{{ route('admin.api.locations.districts') }}?state_id=${stateId}`)
                .then(res => res.json())
                .then(data => {
                    const options = [{ value: '', text: 'Select District' }];
                    data.forEach(d => {
                        options.push({ value: d.name, text: d.name });
                    });
                    updateTomSelect(districtSelect, options, '');
                })
                .catch(err => {
                    updateTomSelect(districtSelect, [{ value: '', text: 'Error loading districts' }], '');
                });
        });
    }

    // Dynamic District Loading for ADPL Details
    if (adplStateSelect) {
        adplStateSelect.addEventListener('change', function() {
            const stateId = this.value;
            if (!stateId) {
                updateTomSelect(adplDistrictSelect, [{ value: '', text: 'Select District' }], '');
                return;
            }
            fetch(`{{ route('admin.api.locations.districts') }}?state_id=${stateId}`)
                .then(res => res.json())
                .then(data => {
                    const options = [{ value: '', text: 'Select District' }];
                    data.forEach(d => {
                        options.push({ value: d.name, text: d.name });
                    });
                    updateTomSelect(adplDistrictSelect, options, '');
                })
                .catch(err => {
                    updateTomSelect(adplDistrictSelect, [{ value: '', text: 'Error loading districts' }], '');
                });
        });
    }

    // Prefill old/current district values
    const currentStateId = "{{ old('state', $currentStateId) }}";
    const currentDistrictName = "{{ old('district', $user->profile->district ?? '') }}";
    if (currentStateId) {
        fetch(`{{ route('admin.api.locations.districts') }}?state_id=${currentStateId}`)
            .then(response => response.json())
            .then(data => {
                const options = [{ value: '', text: 'Select District' }];
                data.forEach(district => {
                    options.push({ value: district.name, text: district.name });
                });
                updateTomSelect(districtSelect, options, currentDistrictName);
            });
    }

    const currentAdplStateId = "{{ old('adpl_state', $currentAdplStateId) }}";
    const currentAdplDistrictName = "{{ old('adpl_district', $user->profile->adpl_district ?? '') }}";
    if (currentAdplStateId && adplDistrictSelect) {
        fetch(`{{ route('admin.api.locations.districts') }}?state_id=${currentAdplStateId}`)
            .then(response => response.json())
            .then(data => {
                const options = [{ value: '', text: 'Select District' }];
                data.forEach(district => {
                    options.push({ value: district.name, text: district.name });
                });
                updateTomSelect(adplDistrictSelect, options, currentAdplDistrictName);
            });
    }

    // Password visibility toggle
    document.getElementById('togglePwd').addEventListener('click', function() {
        const field = document.getElementById('passwordField');
        const icon = document.getElementById('togglePwdIcon');
        if (field.type === 'password') {
            field.type = 'text';
            icon.className = 'bx bx-show';
        } else {
            field.type = 'password';
            icon.className = 'bx bx-hide';
        }
    });

    // Generate Password
    document.getElementById('generatePwdBtn').addEventListener('click', function() {
        const field = document.getElementById('passwordField');
        const chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()_+";
        let password = "";
        for (let i = 0; i < 12; i++) {
            password += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        field.value = password;
        field.type = 'text';
        document.getElementById('togglePwdIcon').className = 'bx bx-show';
    });

    // IFSC auto-fetch
    const ifscInput = document.getElementById('ifsc_code');
    const bankNameInput = document.getElementById('bank_name');
    const branchNameInput = document.getElementById('branch_name');
    if (ifscInput) {
        ifscInput.addEventListener('input', function() {
            const ifsc = this.value.trim().toUpperCase();
            if (ifsc.length === 11) {
                bankNameInput.value = 'Fetching...';
                branchNameInput.value = 'Fetching...';
                fetch('https://ifsc.razorpay.com/' + ifsc)
                    .then(res => { if (!res.ok) throw new Error('Invalid'); return res.json(); })
                    .then(data => { bankNameInput.value = data.BANK; branchNameInput.value = data.BRANCH; })
                    .catch(() => { bankNameInput.value = ''; branchNameInput.value = ''; showToast('Invalid IFSC code', 'error'); });
            }
        });
    }

    // Custom client-side validation to apply red borders on missing mandatory fields
    const form = document.querySelector('form.needs-validation');
    if (form) {
        form.addEventListener('submit', function (event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    }
});
</script>
@endpush

@push('styles')
<style>
    /* Premium Red-border highlighting for missing/invalid required fields */
    .was-validated .form-control:invalid,
    .was-validated .form-select:invalid {
        border-color: #dc3545 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 12 12' width='12' height='12' fill='none' stroke='%23dc3545'%3e%3ccircle cx='6' cy='6' r='4.5'/%3e%3cpath stroke-linejoin='round' d='M5.8 3.6h.4L6 6.5z'/%3e%3ccircle cx='6' cy='8.2' r='.6' fill='%23dc3545' stroke='none'/%3e%3c/svg%3e") !important;
        background-repeat: no-repeat !important;
        background-position: right calc(0.375em + 0.1875rem) center !important;
        background-size: calc(0.75em + 0.375rem) calc(0.75em + 0.375rem) !important;
    }
    .was-validated .form-control:invalid:focus,
    .was-validated .form-select:invalid:focus {
        border-color: #dc3545 !important;
        box-shadow: 0 0 0 0.25rem rgba(220, 53, 69, 0.25) !important;
    }
</style>
@endpush
