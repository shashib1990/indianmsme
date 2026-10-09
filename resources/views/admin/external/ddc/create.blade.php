@extends('layouts.admin')

@section('title', 'Add DDC')
@section('page-title', 'Add DDC')

@section('content')
<!-- Back button & header -->
<x-admin.page-header 
    title="Add DDC" 
    subtitle="Create a new DDC account with profile details."
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

<div class="row mt-3 justify-content-center">
    <div class="col-xl-12">


        <form action="{{ route('admin.external.ddc.store') }}" method="POST" enctype="multipart/form-data" class="needs-validation" novalidate>
            @csrf
            
            <!-- Section 1: DDC Account Details -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center gap-2 border-bottom">
                    <iconify-icon icon="solar:user-id-bold-duotone" class="text-primary fs-20"></iconify-icon>
                    <h5 class="fw-bold text-slate-800 mb-0">DDC Account Details</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- DDC Coordinator Name -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700">DDC Coordinator Name <span class="text-danger">*</span></label>
                            <input type="text" name="ddc_owner_name" class="form-control" placeholder="DDC Coordinator Name" value="{{ old('ddc_owner_name') }}" required>
                        </div>

                        <!-- Company Name -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700">Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="company_name" class="form-control" placeholder="Company Name" value="{{ old('company_name') }}" required>
                        </div>

                        <!-- Trade Name -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700">Trade Name <span class="text-danger">*</span></label>
                            <input type="text" name="trade_name" class="form-control" placeholder="Trade Name" value="{{ old('trade_name') }}" required>
                        </div>

                        <!-- Official Phone -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700">Official Phone <span class="text-danger">*</span></label>
                            <input type="text" name="official_phone" class="form-control" placeholder="Official Phone" value="{{ old('official_phone') }}" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                        </div>

                        <!-- Official Email -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700">Official Email <span class="text-danger">*</span></label>
                            <input type="email" name="official_email" class="form-control" placeholder="Official Email" value="{{ old('official_email') }}" required>
                        </div>

                        <!-- Password Fields -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700">Password <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="password" name="password" id="passwordField" class="form-control" placeholder="Password" required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePwd">
                                    <i class="bx bx-hide" id="togglePwdIcon"></i>
                                </button>
                                <button type="button" class="btn btn-secondary" id="generatePwdBtn">
                                    <i class="bx bx-key me-1"></i> Generate
                                </button>
                            </div>
                        </div>

                        <!-- DDC Code Selector -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-slate-700">DDC Code <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <select name="ddc_code" id="ddc_code" class="form-select s-select" required>
                                    <option value="">Select DDC Code</option>
                                    @foreach($ddcCodes as $code)
                                        <option value="{{ $code->code }}" {{ old('ddc_code') == $code->code ? 'selected' : '' }}>
                                            {{ $code->code }} {{ $code->is_assigned ? '(Assigned)' : '' }}
                                        </option>
                                    @endforeach
                                </select>
                                <button class="btn btn-outline-primary" type="button" id="btnGenerateCode">
                                    <i class="bx bx-plus-circle me-1"></i> Generate Code
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 2: DDC Location & ADPL Details -->
            <div class="row g-3 mb-4">
                <!-- ADPL Details Card -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 py-3 d-flex align-items-center gap-2 border-bottom">
                            <iconify-icon icon="solar:city-bold-duotone" class="text-primary fs-20"></iconify-icon>
                            <h5 class="fw-bold text-slate-800 mb-0">ADPL (Head Office) Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- ADPL Company Name -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-slate-700">ADPL Company Name <span class="text-danger">*</span></label>
                                    <input type="text" name="adpl_company_name" class="form-control" placeholder="ADPL Company Name" value="{{ old('adpl_company_name', $adplDefaults['company_name'] ?? '') }}" required>
                                </div>

                                <!-- ADPL GST Number -->
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold text-slate-700">ADPL GST Number <span class="text-danger">*</span></label>
                                    <input type="text" name="adpl_gst_number" class="form-control text-uppercase" placeholder="15-digit GST No." value="{{ old('adpl_gst_number', $adplDefaults['gst_number'] ?? '') }}" minlength="15" maxlength="15" oninput="this.value = this.value.toUpperCase().slice(0, 15)" required>
                                </div>

                                <!-- ADPL State -->
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-slate-700">ADPL State <span class="text-danger">*</span></label>
                                    <select id="adpl_state" name="adpl_state" class="form-select s-select" required>
                                        <option value="">Select State</option>
                                        @foreach($states as $st)
                                            <option value="{{ $st->id }}" {{ old('adpl_state', $adplDefaults['state_id'] ?? '') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
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
                                    <input type="text" name="adpl_pincode" class="form-control" placeholder="ADPL Pin Code" value="{{ old('adpl_pincode', $adplDefaults['pincode'] ?? '') }}" maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                                </div>

                                <!-- ADPL Address -->
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-slate-700">ADPL Address <span class="text-danger">*</span></label>
                                    <textarea name="adpl_address" rows="3" class="form-control" placeholder="ADPL Address..." required>{{ old('adpl_address', $adplDefaults['address'] ?? '') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DDC Details Card -->
                <div class="col-lg-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 py-3 d-flex align-items-center gap-2 border-bottom">
                            <iconify-icon icon="solar:shop-bold-duotone" class="text-primary fs-20"></iconify-icon>
                            <h5 class="fw-bold text-slate-800 mb-0">DDC (Depot Location) Details</h5>
                        </div>
                        <div class="card-body">
                            <div class="row g-3">
                                <!-- DDC GST Number -->
                                <div class="col-md-12">
                                    <label class="form-label fw-semibold text-slate-700">DDC GST Number <span class="text-danger">*</span></label>
                                    <input type="text" name="ddc_gst_number" class="form-control text-uppercase" placeholder="15-digit GST No." value="{{ old('ddc_gst_number') }}" minlength="15" maxlength="15" oninput="this.value = this.value.toUpperCase().slice(0, 15)" required>
                                </div>

                                <!-- State -->
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-slate-700">State <span class="text-danger">*</span></label>
                                    <select id="state" name="state" class="form-select s-select" required>
                                        <option value="">Select State</option>
                                        @foreach($states as $st)
                                            <option value="{{ $st->id }}" {{ old('state') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
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
                                    <label class="form-label fw-semibold text-slate-700">DDC Pin Code <span class="text-danger">*</span></label>
                                    <input type="text" name="ddc_pincode" class="form-control" placeholder="DDC Pin Code" value="{{ old('ddc_pincode') }}" maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                                </div>

                                <!-- DDC Address -->
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-slate-700">DDC Address <span class="text-danger">*</span></label>
                                    <textarea name="ddc_address" rows="3" class="form-control" placeholder="DDC Address..." required>{{ old('ddc_address') }}</textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: Required Documents & KYC Uploads -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3 d-flex align-items-center gap-2 border-bottom">
                    <iconify-icon icon="solar:document-bold-duotone" class="text-primary fs-20"></iconify-icon>
                    <h5 class="fw-bold text-slate-800 mb-0">Required Documents / KYC</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        <!-- Master Depot Image -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700">Master Depot Image <span class="text-danger">*</span></label>
                            <input type="file" name="master_depot_image" class="form-control mb-1" accept="image/jpeg,image/jpg" required>
                            <small class="text-muted d-block">Only JPG/JPEG format. Max 5MB.</small>
                        </div>

                        <!-- Contract Document -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700">Contract Document <span class="text-danger">*</span></label>
                            <input type="file" name="contract_document" class="form-control mb-1" accept="application/pdf" required>
                            <small class="text-muted d-block">Only PDF format. Max 5MB.</small>
                        </div>

                        <!-- GST Certificate -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700">GST Certificate <span class="text-danger">*</span></label>
                            <input type="file" name="gst_certificate" class="form-control mb-1" accept="application/pdf,image/*" required>
                            <small class="text-muted d-block">PDF or Image. Max 5MB.</small>
                        </div>

                        <!-- Aadhaar Card -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700">Aadhaar Card (Owner/Authorized)</label>
                            <input type="file" name="aadhaar_card" class="form-control mb-1" accept="application/pdf,image/*">
                            <small class="text-muted d-block">PDF or Image. Max 5MB.</small>
                        </div>

                        <!-- FSSAI Certificate -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700">FSSAI Certificate <span class="text-danger">*</span></label>
                            <input type="file" name="fssai_certificate" class="form-control mb-1" accept="application/pdf,image/*" required>
                            <small class="text-muted d-block">PDF or Image. Max 5MB.</small>
                        </div>

                        <!-- Cancelled Cheque -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700">Cancelled Cheque / Passbook <span class="text-danger">*</span></label>
                            <input type="file" name="cancelled_cheque" class="form-control mb-1" accept="application/pdf,image/*" required>
                            <small class="text-muted d-block">PDF or Image. Max 5MB.</small>
                        </div>

                        <!-- Trade License -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700">Trade License <span class="text-muted small">(Optional)</span></label>
                            <input type="file" name="trade_license" class="form-control mb-1" accept="application/pdf,image/*">
                            <small class="text-muted d-block">PDF or Image. Max 5MB.</small>
                        </div>

                        <!-- DDC Agreement Copy -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700">DDC Agreement Copy <span class="text-danger">*</span></label>
                            <input type="file" name="ddc_agreement" class="form-control mb-1" accept="application/pdf,image/*" required>
                            <small class="text-muted d-block">PDF or Image. Max 5MB.</small>
                        </div>

                        <!-- Franchise Deposit Receipt -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700">Franchise Deposit Receipt <span class="text-danger">*</span></label>
                            <input type="file" name="franchise_deposit_receipt" class="form-control mb-1" accept="application/pdf,image/*" required>
                            <small class="text-muted d-block">PDF or Image. Max 5MB.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="d-flex gap-2 justify-content-end mb-4 mt-3">
                <button type="submit" class="btn btn-primary px-4" id="submitBtn">
                    <iconify-icon icon="solar:check-read-bold-duotone" class="me-1 fs-16 align-middle"></iconify-icon> Add DDC
                </button>
                <a href="{{ route('admin.external.ddc.index') }}" class="btn btn-light px-4">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<style>
    /* Styling fix for Tom Select when inside a Bootstrap input-group */
    .input-group > .ts-wrapper {
        flex: 1 1 auto;
        width: 1%;
    }
    .input-group > .ts-wrapper .ts-control {
        border-top-right-radius: 0 !important;
        border-bottom-right-radius: 0 !important;
    }
</style>
@endpush

@push('scripts')
<script>
// Helper to safely update a select element initialized with Tom Select
function updateTomSelect(selectEl, options, selectedValue = '') {
    const ts = selectEl.tomselect;
    if (ts) {
        ts.clear(true); // silent clear
        ts.clearOptions();
        options.forEach((opt, idx) => {
            ts.addOption({ value: opt.value, text: opt.text, sort_index: idx });
        });
        if (selectedValue) {
            ts.setValue(selectedValue, false);
        }
        ts.refreshOptions(false); // redraw options list
    } else {
        // Fallback for standard select
        selectEl.innerHTML = '';
        options.forEach(opt => {
            const option = document.createElement('option');
            option.value = opt.value;
            option.textContent = opt.text;
            if (opt.value === selectedValue) {
                option.selected = true;
            }
            selectEl.appendChild(option);
        });
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const stateSelect = document.getElementById('state');
    const districtSelect = document.getElementById('district');
    const adplStateSelect = document.getElementById('adpl_state');
    const adplDistrictSelect = document.getElementById('adpl_district');
    const ddcCodeSelect = document.getElementById('ddc_code');
    const submitBtn = document.getElementById('submitBtn');
    const btnGenerateCode = document.getElementById('btnGenerateCode');
    const csrfToken = document.querySelector('input[name="_token"]').value;

    stateSelect.addEventListener('change', function() {
        const stateId = this.value;
        
        // Show loading state in district dropdown
        updateTomSelect(districtSelect, [{ value: '', text: 'Loading districts...' }], '');
        
        if (!stateId) {
            updateTomSelect(districtSelect, [{ value: '', text: 'Select District' }], '');
            return;
        }

        fetch(`{{ route('admin.api.locations.districts') }}?state_id=${stateId}`)
            .then(response => response.json())
            .then(data => {
                const options = [{ value: '', text: 'Select District' }];
                data.forEach(district => {
                    options.push({ value: district.name, text: district.name });
                });
                updateTomSelect(districtSelect, options, '');
            })
            .catch(err => {
                updateTomSelect(districtSelect, [{ value: '', text: 'Error loading districts' }], '');
            });
    });

    if (adplStateSelect && adplDistrictSelect) {
        adplStateSelect.addEventListener('change', function() {
            const stateId = this.value;
            
            // Show loading state in ADPL district dropdown
            updateTomSelect(adplDistrictSelect, [{ value: '', text: 'Loading districts...' }], '');
            
            if (!stateId) {
                updateTomSelect(adplDistrictSelect, [{ value: '', text: 'Select District' }], '');
                return;
            }

            fetch(`{{ route('admin.api.locations.districts') }}?state_id=${stateId}`)
                .then(response => response.json())
                .then(data => {
                    const options = [{ value: '', text: 'Select District' }];
                    data.forEach(district => {
                        options.push({ value: district.name, text: district.name });
                    });
                    updateTomSelect(adplDistrictSelect, options, '');
                })
                .catch(err => {
                    updateTomSelect(adplDistrictSelect, [{ value: '', text: 'Error loading districts' }], '');
                });
        });
    }

    // Prefill old district values on validation failure
    const oldState = "{{ old('state') }}";
    const oldDistrict = "{{ old('district') }}";
    if (oldState) {
        fetch(`{{ route('admin.api.locations.districts') }}?state_id=${oldState}`)
            .then(response => response.json())
            .then(data => {
                const options = [{ value: '', text: 'Select District' }];
                data.forEach(district => {
                    options.push({ value: district.name, text: district.name });
                });
                updateTomSelect(districtSelect, options, oldDistrict);
            });
    }

    const oldAdplState = "{{ old('adpl_state', $adplDefaults['state_id'] ?? '') }}";
    const oldAdplDistrict = "{{ old('adpl_district', $adplDefaults['district_name'] ?? '') }}";
    if (oldAdplState && adplDistrictSelect) {
        fetch(`{{ route('admin.api.locations.districts') }}?state_id=${oldAdplState}`)
            .then(response => response.json())
            .then(data => {
                const options = [{ value: '', text: 'Select District' }];
                data.forEach(district => {
                    options.push({ value: district.name, text: district.name });
                });
                updateTomSelect(adplDistrictSelect, options, oldAdplDistrict);
            });
    }

    // Check code availability in real-time
    function checkDdcCodeAvailability(code) {
        if (!code) {
            submitBtn.disabled = false;
            return;
        }
        fetch(`{{ route('admin.external.ddc.check-code') }}?code=${code}`)
            .then(res => res.json())
            .then(data => {
                if (data.assigned) {
                    showToast('This code is already assigned!', 'error');
                    const tsDdcCode = ddcCodeSelect.tomselect;
                    if (tsDdcCode) {
                        tsDdcCode.setValue('');
                    } else {
                        ddcCodeSelect.value = '';
                    }
                    submitBtn.disabled = true;
                } else {
                    submitBtn.disabled = false;
                }
            })
            .catch(err => {
                console.error(err);
            });
    }

    ddcCodeSelect.addEventListener('change', function() {
        checkDdcCodeAvailability(this.value);
    });

    // Generate next DDC Code dynamically
    btnGenerateCode.addEventListener('click', function() {
        btnGenerateCode.disabled = true;
        btnGenerateCode.innerHTML = '<i class="bx bx-loader-alt bx-spin me-1"></i> Generating...';

        fetch(`{{ route('admin.external.ddc.generate-code') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            }
        })
        .then(res => res.json())
        .then(data => {
            btnGenerateCode.disabled = false;
            btnGenerateCode.innerHTML = '<i class="bx bx-plus-circle me-1"></i> Generate Code';
            
            if (data.status === 'success') {
                const tsDdcCode = ddcCodeSelect.tomselect;
                if (tsDdcCode) {
                    tsDdcCode.addOption({ value: data.code, text: data.code + ' (Available)', sort_index: 0 });
                    tsDdcCode.setValue(data.code);
                    tsDdcCode.refreshOptions(false);
                } else {
                    const newOption = document.createElement('option');
                    newOption.value = data.code;
                    newOption.textContent = data.code + ' (Available)';
                    ddcCodeSelect.appendChild(newOption);
                    ddcCodeSelect.value = data.code;
                }
                submitBtn.disabled = false;
                showToast('DDC Code generated successfully: ' + data.code, 'success');
            } else {
                showToast('Failed to generate code: ' + (data.message || 'Unknown error'), 'error');
            }
        })
        .catch(err => {
            btnGenerateCode.disabled = false;
            btnGenerateCode.innerHTML = '<i class="bx bx-plus-circle me-1"></i> Generate Code';
            console.error(err);
            showToast('External error occurred while generating code.', 'error');
        });
    });

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
