@extends('layouts.admin')

@section('title', 'ADPL Head Office Details')
@section('page-title', 'ADPL Head Office Details')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">

        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bx bx-check-circle me-1"></i> {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <ul class="mb-0">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Main Form -->
        <form action="{{ route('admin.settings.adpl-office.update') }}" method="POST">
            @csrf

            <!-- ADPL Head Office Details Configuration Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:city-bold-duotone" class="text-primary fs-22"></iconify-icon>
                    <h5 class="card-title fw-bold text-slate-800 mb-0">ADPL Head Office Details</h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted fs-13 mb-4">
                        Ye default ADPL Head Office details hain. Naya DDC add karte waqt ye details automatic pre-fill ho jayengi.
                    </p>

                    <div class="row g-3">
                        <!-- ADPL Company Name -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-slate-700">ADPL Company Name <span class="text-danger">*</span></label>
                            <input type="text" name="adpl_company_name" class="form-control" placeholder="e.g. ADPL Distributors Pvt. Ltd." value="{{ old('adpl_company_name', $companyName) }}" required>
                        </div>

                        <!-- ADPL GST Number -->
                        <div class="col-md-6">
                            <label class="form-label fw-semibold text-slate-700">ADPL GST Number <span class="text-danger">*</span></label>
                            <input type="text" name="adpl_gst_number" class="form-control text-uppercase" placeholder="15-digit GST No." value="{{ old('adpl_gst_number', $gstNumber) }}" minlength="15" maxlength="15" oninput="this.value = this.value.toUpperCase().slice(0, 15)" required>
                        </div>

                        <!-- ADPL State -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700">ADPL State <span class="text-danger">*</span></label>
                            <select id="adpl_state" name="adpl_state_id" class="form-select s-select" required>
                                <option value="">Select State</option>
                                @foreach($states as $st)
                                    <option value="{{ $st->id }}" {{ old('adpl_state_id', $stateId) == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- ADPL District -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700">ADPL District <span class="text-danger">*</span></label>
                            <select id="adpl_district" name="adpl_district_id" class="form-select s-select" required>
                                <option value="">Select District</option>
                                @foreach($districts as $dst)
                                    <option value="{{ $dst->id }}" {{ old('adpl_district_id', $districtId) == $dst->id ? 'selected' : '' }}>{{ $dst->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- ADPL Pincode -->
                        <div class="col-md-4">
                            <label class="form-label fw-semibold text-slate-700">ADPL Pin Code <span class="text-danger">*</span></label>
                            <input type="text" name="adpl_pincode" class="form-control" placeholder="6-digit Pincode" value="{{ old('adpl_pincode', $pincode) }}" maxlength="6" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                        </div>

                        <!-- ADPL Address -->
                        <div class="col-12">
                            <label class="form-label fw-semibold text-slate-700">ADPL Address <span class="text-danger">*</span></label>
                            <textarea name="adpl_address" rows="3" class="form-control" placeholder="Enter Full Head Office Address..." required>{{ old('adpl_address', $address) }}</textarea>
                        </div>
                    </div>
                </div>

                <div class="card-footer bg-light py-3 d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary d-flex align-items-center gap-1">
                        <iconify-icon icon="solar:check-read-bold-duotone" class="fs-18"></iconify-icon>
                        Save ADPL Head Office Details
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
    const ts = selectEl.tomselect;
    if (ts) {
        ts.clear(true);
        ts.clearOptions();
        options.forEach((opt, idx) => {
            ts.addOption({ value: opt.value, text: opt.text, sort_index: idx });
        });
        if (selectedValue) {
            ts.setValue(selectedValue, false);
        }
        ts.refreshOptions(false);
    } else {
        selectEl.innerHTML = '';
        options.forEach(opt => {
            const option = document.createElement('option');
            option.value = opt.value;
            option.textContent = opt.text;
            if (String(opt.value) === String(selectedValue)) {
                option.selected = true;
            }
            selectEl.appendChild(option);
        });
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const stateSelect = document.getElementById('adpl_state');
    const districtSelect = document.getElementById('adpl_district');

    if (stateSelect && districtSelect) {
        stateSelect.addEventListener('change', function () {
            const stateId = this.value;
            updateTomSelect(districtSelect, [{ value: '', text: 'Loading...' }], '');
            if (!stateId) {
                updateTomSelect(districtSelect, [{ value: '', text: 'Select District' }], '');
                return;
            }

            fetch(`{{ route('admin.api.locations.districts') }}?state_id=${stateId}`)
                .then(res => res.json())
                .then(data => {
                    let options = [{ value: '', text: 'Select District' }];
                    data.forEach(d => {
                        options.push({ value: d.id, text: d.name });
                    });
                    updateTomSelect(districtSelect, options, '');
                })
                .catch(err => {
                    updateTomSelect(districtSelect, [{ value: '', text: 'Select District' }], '');
                });
        });

        // Load districts if state is already selected on page load
        const currentOfficeStateId = "{{ old('adpl_state_id', $stateId) }}";
        const currentOfficeDistrictId = "{{ old('adpl_district_id', $districtId) }}";
        if (currentOfficeStateId) {
            fetch(`{{ route('admin.api.locations.districts') }}?state_id=${currentOfficeStateId}`)
                .then(res => res.json())
                .then(data => {
                    let options = [{ value: '', text: 'Select District' }];
                    data.forEach(d => {
                        options.push({ value: d.id, text: d.name });
                    });
                    updateTomSelect(districtSelect, options, currentOfficeDistrictId);
                });
        }
    }
});
</script>
@endpush
