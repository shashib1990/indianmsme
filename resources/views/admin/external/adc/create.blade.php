@extends('layouts.admin')

@section('title', 'Add Canteen')
@section('page-title', 'Add Canteen')

@section('content')
<!-- Back button & header -->
<x-admin.page-header 
    title="Add Canteen" 
    subtitle="Manage and configure Canteen Store details for registered ADC accounts."
    :actions="[
        [
            'label' => 'Back to ADC Users',
            'icon' => 'solar:arrow-left-bold-duotone',
            'url' => route('admin.external.adc.index'),
            'permission' => 'users.view',
            'btnClass' => 'btn-outline-secondary'
        ]
    ]"
/>

<div class="row mt-3 justify-content-center">
    <div class="col-xl-11 col-lg-12">
        <form action="{{ route('admin.external.adc.store') }}" method="POST" class="needs-validation" novalidate>
            @csrf
            
            <!-- Category 1: Authorised Person & ADC Account -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3 px-3 px-md-4 d-flex align-items-center gap-2 border-bottom">
                    <iconify-icon icon="solar:user-id-bold-duotone" class="text-primary fs-20"></iconify-icon>
                    <h5 class="fw-bold text-slate-800 mb-0 fs-16">Authorised Person & ADC Account</h5>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="row g-3">
                        <!-- Select ADC Code / Registered User -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700 fs-13 mb-1">Select ADC Code <span class="text-danger">*</span></label>
                            <select id="select_adc_id" name="adc_id" class="form-select form-select-sm s-select" required>
                                <option value="">Select ADC Code / User</option>
                                @foreach($adcs as $adc)
                                    <option value="{{ $adc->id }}" {{ old('adc_id') == $adc->id ? 'selected' : '' }}>
                                        {{ $adc->adcProfile?->code ?? ('ADC' . str_pad($adc->id, 3, '0', STR_PAD_LEFT)) }} - {{ $adc->name }} ({{ $adc->mobile ?? $adc->phone }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-muted fs-12 mt-1">Selecting an ADC will auto-fill profile details.</div>
                        </div>

                        <!-- Name -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700 fs-13 mb-1">Authorised Person Name <span class="text-danger">*</span></label>
                            <input type="text" id="name" name="name" class="form-control form-control-sm" placeholder="Authorised Person Name" value="{{ old('name') }}" required>
                        </div>

                        <!-- Mobile -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700 fs-13 mb-1">Mobile Number <span class="text-danger">*</span></label>
                            <input type="text" id="mobile" name="mobile" class="form-control form-control-sm" placeholder="10-digit Mobile Number" value="{{ old('mobile') }}" maxlength="10" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                        </div>

                        <!-- Email -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700 fs-13 mb-1">Email Address <span class="text-danger">*</span></label>
                            <input type="email" id="email" name="email" class="form-control form-control-sm" placeholder="Email Address" value="{{ old('email') }}" required>
                        </div>

                        <!-- Aadhaar Number -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700 fs-13 mb-1">Aadhaar Number <span class="text-muted fs-12">(Optional)</span></label>
                            <input type="text" id="aadhaar_no" name="aadhaar_no" class="form-control form-control-sm" placeholder="12-digit Aadhaar Number" value="{{ old('aadhaar_no') }}">
                        </div>

                        <!-- PAN Number -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700 fs-13 mb-1">PAN Number <span class="text-muted fs-12">(Optional)</span></label>
                            <input type="text" id="pan_no" name="pan_no" class="form-control form-control-sm" placeholder="PAN Number" value="{{ old('pan_no') }}">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category 2: Canteen Store Details -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3 px-3 px-md-4 d-flex align-items-center gap-2 border-bottom">
                    <iconify-icon icon="solar:shop-bold-duotone" class="text-primary fs-20"></iconify-icon>
                    <h5 class="fw-bold text-slate-800 mb-0 fs-16">Canteen Store Details</h5>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="row g-3">
                        <!-- Name Of Entity (Canteen Name) -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700 fs-13 mb-1">Canteen Name (Entity) <span class="text-danger">*</span></label>
                            <input type="text" id="canteen_name" name="canteen_name" class="form-control form-control-sm" placeholder="Canteen / Entity Name" value="{{ old('canteen_name') }}" required>
                        </div>

                        <!-- Canteen Code -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700 fs-13 mb-1">Canteen Code</label>
                            <input type="text" id="canteen_code" name="canteen_code" class="form-control form-control-sm" placeholder="Canteen Code (e.g. ASC001)" value="{{ old('canteen_code') }}">
                        </div>

                        <!-- Reg Code -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700 fs-13 mb-1">Code (Reg Code)</label>
                            <input type="text" id="reg_code" name="reg_code" class="form-control form-control-sm" placeholder="Registration Code" value="{{ old('reg_code') }}">
                        </div>

                        <!-- Select Type -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700 fs-13 mb-1">Canteen Type</label>
                            <select id="type" name="type" class="form-select form-select-sm s-select">
                                <option value="Canteen Store" {{ old('type') == 'Canteen Store' ? 'selected' : '' }}>Canteen Store</option>
                                <option value="ASC Canteen" {{ old('type') == 'ASC Canteen' ? 'selected' : '' }}>ASC Canteen</option>
                                <option value="MVS Canteen" {{ old('type') == 'MVS Canteen' ? 'selected' : '' }}>MVS Canteen</option>
                                <option value="Regional Canteen" {{ old('type') == 'Regional Canteen' ? 'selected' : '' }}>Regional Canteen</option>
                            </select>
                        </div>

                        <!-- Date of Registration -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700 fs-13 mb-1">Date of Registration</label>
                            <input type="date" id="date_of_registration" name="date_of_registration" class="form-control form-control-sm" value="{{ old('date_of_registration') }}">
                        </div>

                        <!-- GST Number -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700 fs-13 mb-1">GST Number <span class="text-danger">*</span></label>
                            <input type="text" id="gst_number" name="gst_number" class="form-control form-control-sm text-uppercase" placeholder="15-digit GSTIN" value="{{ old('gst_number') }}" minlength="15" maxlength="15" oninput="this.value = this.value.toUpperCase().slice(0, 15)" required>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Category 3: Location & DDC Assignment -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3 px-3 px-md-4 d-flex align-items-center gap-2 border-bottom">
                    <iconify-icon icon="solar:map-point-bold-duotone" class="text-primary fs-20"></iconify-icon>
                    <h5 class="fw-bold text-slate-800 mb-0 fs-16">Location & DDC Assignment</h5>
                </div>
                <div class="card-body p-3 p-md-4">
                    <div class="row g-3">
                        <!-- State -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-slate-700 fs-13 mb-1">State <span class="text-danger">*</span></label>
                            <select id="state" name="state" class="form-select form-select-sm s-select" required>
                                <option value="">Select State</option>
                                @foreach($states as $st)
                                    <option value="{{ $st->id }}" {{ old('state') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- District -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-slate-700 fs-13 mb-1">District <span class="text-danger">*</span></label>
                            <select id="district" name="district" class="form-select form-select-sm s-select" required>
                                <option value="">Select District</option>
                            </select>
                        </div>

                        <!-- Pin Code/Zip Code -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-slate-700 fs-13 mb-1">Pin Code / Zip Code <span class="text-danger">*</span></label>
                            <input type="text" id="pincode" name="pincode" class="form-control form-control-sm" placeholder="6-digit Pincode" value="{{ old('pincode') }}" maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                        </div>

                        <!-- Country -->
                        <div class="col-md-3">
                            <label class="form-label fw-semibold text-slate-700 fs-13 mb-1">Country</label>
                            <input type="text" class="form-control form-control-sm bg-light" value="India" readonly>
                        </div>

                        <!-- Address -->
                        <div class="col-md-8">
                            <label class="form-label fw-semibold text-slate-700 fs-13 mb-1">Full Address</label>
                            <input type="text" id="address" name="address" class="form-control form-control-sm" placeholder="Canteen / Delivery Address" value="{{ old('address') }}">
                        </div>

                        <!-- Assigned DDC -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700 fs-13 mb-1">Assigned DDC</label>
                            <select id="supply_ddc_id" name="supply_ddc_id" class="form-select form-select-sm s-select">
                                <option value="">Select Assigned DDC</option>
                                @foreach($allDdcs as $ddc)
                                    <option value="{{ $ddc->id }}" {{ old('supply_ddc_id') == $ddc->id ? 'selected' : '' }}>
                                        [{{ $ddc->ddcProfile?->ddc_code ?? 'DDC' }}] {{ $ddc->name }} ({{ $ddc->ddcProfile?->district ?? 'All Dist' }})
                                    </option>
                                @endforeach
                            </select>
                            <div id="supply_ddc_help" class="form-text text-muted fs-12 mt-1">Auto-filtered based on Pincode delivery area.</div>
                        </div>
                    </div>
                </div>
                <div class="card-footer bg-white py-3 px-3 px-md-4 d-flex justify-content-end gap-2 border-top">
                    <a href="{{ route('admin.external.adc.index') }}" class="btn btn-sm btn-light px-4">Cancel</a>
                    <button type="submit" class="btn btn-sm btn-primary px-4" id="submitBtn">
                        <iconify-icon icon="solar:check-read-bold-duotone" class="me-1 fs-16 align-middle"></iconify-icon> Save Canteen Details
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateTomSelect(selectEl, options, selectedValue = '') {
    if (!selectEl) return;
    const ts = selectEl.tomselect;
    if (ts) {
        ts.clear(true);
        ts.clearOptions();
        options.forEach((opt, idx) => {
            ts.addOption({ value: opt.value, text: opt.text, sort_index: idx });
        });
        ts.setValue(selectedValue, true);
        ts.refreshOptions(false);
    } else {
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
    const adcSelect = document.getElementById('select_adc_id');
    const stateSelect = document.getElementById('state');
    const districtSelect = document.getElementById('district');
    const pincodeInput = document.getElementById('pincode');
    const supplyDdcSelect = document.getElementById('supply_ddc_id');
    const helpText = document.getElementById('supply_ddc_help');

    // Fetch districts when state changes
    if (stateSelect) {
        stateSelect.addEventListener('change', function() {
            populateDistricts(this.value, '');
        });
        if (stateSelect.tomselect) {
            stateSelect.tomselect.on('change', function(val) {
                populateDistricts(val, '');
            });
        }
    }

    function populateDistricts(stateId, selectedDistrict = '') {
        if (!stateId) {
            updateTomSelect(districtSelect, [{ value: '', text: 'Select District' }], '');
            return;
        }

        updateTomSelect(districtSelect, [{ value: '', text: 'Loading districts...' }], '');

        fetch(`{{ route('admin.api.locations.districts') }}?state_id=${stateId}`)
            .then(res => res.json())
            .then(data => {
                let options = [{ value: '', text: 'Select District' }];
                data.forEach(d => {
                    options.push({ value: d.name, text: d.name });
                });
                updateTomSelect(districtSelect, options, selectedDistrict);
            })
            .catch(err => {
                updateTomSelect(districtSelect, [{ value: '', text: 'Select District' }], '');
                console.error(err);
            });
    }

    // Fetch eligible DDCs when pincode changes
    pincodeInput.addEventListener('change', function() {
        fetchEligibleDdcs(this.value);
    });

    pincodeInput.addEventListener('input', function() {
        if (this.value.length === 6) {
            fetchEligibleDdcs(this.value);
        }
    });

    function fetchEligibleDdcs(pincode, preselectedDdcId = null) {
        if (!pincode || pincode.length !== 6) return;

        fetch(`{{ route('admin.external.adc.get-eligible-ddcs') }}?pincode=${pincode}`)
            .then(res => res.json())
            .then(data => {
                let eligible = data.eligible_ddcs || [];
                let allDdcs = data.all_ddcs || [];
                const ts = supplyDdcSelect ? supplyDdcSelect.tomselect : null;

                if (ts) {
                    ts.clear(true);
                    ts.clearOptions();
                    ts.clearOptionGroups();

                    ts.addOption({ value: '', text: 'Select Assigned DDC' });

                    if (eligible.length > 0) {
                        const grp = 'DDCs Serving Pincode ' + pincode;
                        ts.addGroup(grp, { label: grp });
                        eligible.forEach(d => {
                            ts.addOption({ value: String(d.id), text: `[${d.code}] ${d.company_name} (${d.district})`, optgroup: grp });
                        });
                        helpText.textContent = `Found ${eligible.length} DDC(s) directly serving pincode ${pincode}.`;
                        helpText.className = "form-text text-success font-weight-bold fs-12 mt-1";
                    } else if (data.virtual_ddc) {
                        const v = data.virtual_ddc;
                        const grp = 'Virtual DDC Route';
                        ts.addGroup(grp, { label: grp });
                        ts.addOption({ value: String(v.id), text: `[${v.code}] ${v.name}`, optgroup: grp });
                        helpText.textContent = `No Physical DDC assigned for ${pincode}. Routing via Virtual DDC.`;
                        helpText.className = "form-text text-warning font-weight-bold fs-12 mt-1";
                    }

                    if (allDdcs.length > 0) {
                        const grp = 'All Available DDCs';
                        ts.addGroup(grp, { label: grp });
                        allDdcs.forEach(d => {
                            ts.addOption({ value: String(d.id), text: `[${d.code}] ${d.company_name} (${d.district})`, optgroup: grp });
                        });
                    }

                    ts.refreshOptions(false);
                    if (preselectedDdcId) {
                        ts.setValue(String(preselectedDdcId), true);
                    }
                } else {
                    let html = '<option value="">Select Assigned DDC</option>';
                    if (eligible.length > 0) {
                        html += '<optgroup label="DDCs Delivering to Pincode ' + pincode + '">';
                        eligible.forEach(d => {
                            const sel = (preselectedDdcId && d.id == preselectedDdcId) ? 'selected' : '';
                            html += `<option value="${d.id}" ${sel}>[${d.code}] ${d.company_name} (${d.district})</option>`;
                        });
                        html += '</optgroup>';
                        helpText.textContent = `Found ${eligible.length} DDC(s) directly serving pincode ${pincode}.`;
                        helpText.className = "form-text text-success font-weight-bold fs-12 mt-1";
                    } else if (data.virtual_ddc) {
                        const v = data.virtual_ddc;
                        const sel = (preselectedDdcId && v.id == preselectedDdcId) ? 'selected' : '';
                        html += `<option value="${v.id}" ${sel}>[${v.code}] ${v.name}</option>`;
                        helpText.textContent = `No Physical DDC assigned for ${pincode}. Routing via Virtual DDC.`;
                        helpText.className = "form-text text-warning font-weight-bold fs-12 mt-1";
                    }

                    if (allDdcs.length > 0) {
                        html += '<optgroup label="All Available DDCs">';
                        allDdcs.forEach(d => {
                            const sel = (preselectedDdcId && d.id == preselectedDdcId) ? 'selected' : '';
                            html += `<option value="${d.id}" ${sel}>[${d.code}] ${d.company_name} (${d.district})</option>`;
                        });
                        html += '</optgroup>';
                    }

                    supplyDdcSelect.innerHTML = html;
                }
            })
            .catch(err => console.error(err));
    }

    // Auto-fill when ADC is selected
    function onAdcChange(adcId) {
        if (!adcId) return;

        fetch(`{{ route('admin.external.adc.get-details', ':id') }}`.replace(':id', adcId))
            .then(res => res.json())
            .then(data => {
                if (!data.status) return;
                const adc = data.adc;

                document.getElementById('name').value = adc.name || '';
                document.getElementById('mobile').value = adc.mobile || '';
                document.getElementById('email').value = adc.email || '';
                document.getElementById('canteen_code').value = adc.canteen_code || adc.code || '';
                document.getElementById('canteen_name').value = adc.canteen_name || '';
                document.getElementById('address').value = adc.address || '';
                document.getElementById('pincode').value = adc.pincode || '';
                document.getElementById('gst_number').value = adc.gst_number || '';
                document.getElementById('aadhaar_no').value = adc.aadhaar_no || '';
                document.getElementById('pan_no').value = adc.pan_no || '';
                document.getElementById('reg_code').value = adc.canteen_code || adc.code || '';
                
                if (adc.created_at) {
                    document.getElementById('date_of_registration').value = adc.created_at;
                }

                if (adc.type) {
                    const typeSelect = document.getElementById('type');
                    if (typeSelect.tomselect) {
                        typeSelect.tomselect.setValue(adc.type, true);
                    } else {
                        typeSelect.value = adc.type;
                    }
                }

                // Match State Dropdown
                if (adc.state_id || adc.state) {
                    let matchedStateId = adc.state_id;
                    if (!matchedStateId && adc.state) {
                        Array.from(stateSelect.options).forEach(opt => {
                            if (opt.text.toLowerCase() === adc.state.toLowerCase()) {
                                matchedStateId = opt.value;
                            }
                        });
                    }
                    if (matchedStateId) {
                        if (stateSelect.tomselect) {
                            stateSelect.tomselect.setValue(matchedStateId, true);
                        } else {
                            stateSelect.value = matchedStateId;
                        }
                        populateDistricts(matchedStateId, adc.district);
                    }
                }

                // Match/Fetch Supply DDBs for Pincode
                if (adc.pincode) {
                    fetchEligibleDdcs(adc.pincode, adc.assigned_ddc_id);
                }
            })
            .catch(err => console.error('Error fetching ADC details:', err));
    }

    if (adcSelect) {
        adcSelect.addEventListener('change', function() {
            onAdcChange(this.value);
        });
        if (adcSelect.tomselect) {
            adcSelect.tomselect.on('change', function(val) {
                onAdcChange(val);
            });
        }
    }
});
</script>
@endpush
