@extends('layouts.vendor')

@section('title', 'Add Pickup Store')
@section('page-title', 'Add Pickup Store')

@section('content')
    <div class="row">
        <div class="col-12 mb-3">
            <a href="{{ route('vendor.pickup-stores.index') }}" class="btn btn-sm btn-outline-secondary">
                <iconify-icon icon="solar:arrow-left-bold-duotone" class="align-middle me-1"></iconify-icon>
                Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Left-aligned column instead of mx-auto centering -->
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title fw-bold text-slate-800 mb-0">Register New Pickup Store</h5>
                </div>
                <div class="card-body">
                    <form action="{{ route('vendor.pickup-stores.store') }}" method="POST">
                        @csrf
                        
                        <!-- Basic Information -->
                        <h6 class="fw-bold text-primary mb-3">Basic Information</h6>
                        <div class="row mb-4">
                            <!-- Store Name -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label required fw-medium text-slate-700">Pickup Store Name</label>
                                <input type="text" name="pickup_store_name" class="form-control" placeholder="Store Name" value="{{ old('pickup_store_name') }}" required>
                            </div>

                            <!-- Contact Person -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label required fw-medium text-slate-700">Contact Person Name</label>
                                <input type="text" name="contact_person" class="form-control" placeholder="Representative Name" value="{{ old('contact_person') }}" required>
                            </div>

                            <!-- Mobile -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium text-slate-700">Mobile Number</label>
                                <input type="text" id="mobileInput" name="mobile" class="form-control" placeholder="10-digit mobile number" value="{{ old('mobile') }}" maxlength="15" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                            </div>

                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium text-slate-700">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="store@domain.com" value="{{ old('email') }}">
                            </div>

                            <!-- GST Number -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium text-slate-700">GST Number</label>
                                <input type="text" name="gst_number" class="form-control text-uppercase" placeholder="15-digit GSTIN (optional)" value="{{ old('gst_number') }}" maxlength="15" oninput="this.value = this.value.toUpperCase().slice(0, 15)">
                            </div>

                            <!-- Pincode -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label required fw-medium text-slate-700">Pincode</label>
                                <input type="text" id="pincodeInput" name="pincode" class="form-control" placeholder="6-digit pincode" value="{{ old('pincode') }}" maxlength="6" pattern="[0-9]{6}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                            </div>

                            <!-- Address -->
                            <div class="col-12 mb-3">
                                <label class="form-label required fw-medium text-slate-700">Address</label>
                                <textarea name="address" rows="3" class="form-control" placeholder="Full address of the pickup store" required>{{ old('address') }}</textarea>
                            </div>

                            <!-- Landmark -->
                            <div class="col-12 mb-3">
                                <label class="form-label fw-medium text-slate-700">Landmark (Optional)</label>
                                <input type="text" name="landmark" class="form-control" placeholder="Nearby landmark" value="{{ old('landmark') }}">
                            </div>

                            <!-- State -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label required fw-medium text-slate-700">State</label>
                                <select id="stateSelect" name="state" class="form-select" required>
                                    <option value="">— Select State —</option>
                                    @foreach($states as $st)
                                        <option value="{{ $st->id }}" {{ old('state') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- District -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label required fw-medium text-slate-700">District</label>
                                <select id="districtSelect" name="district" class="form-select" required>
                                    <option value="">— Select State First —</option>
                                </select>
                            </div>

                            <!-- Payment & Dispatch Capabilities Radio Selection -->
                            <div class="col-12 mt-2 mb-3">
                                <label class="form-label required fw-medium text-slate-700">Will this Pickup Store receive payments and handle dispatch?</label>
                                <div class="d-flex gap-4 mt-1">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="can_receive_payment_and_dispatch" id="capability_yes" value="1" {{ old('can_receive_payment_and_dispatch') == '1' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-medium text-slate-800" for="capability_yes">
                                            Yes, independent portal access with payment and dispatch capabilities
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="can_receive_payment_and_dispatch" id="capability_no" value="0" {{ old('can_receive_payment_and_dispatch', '0') == '0' ? 'checked' : '' }}>
                                        <label class="form-check-label fw-medium text-slate-800" for="capability_no">
                                            No, seller-managed store (no independent login/portal credentials)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Conditionally Hidden Section: Bank Details (For Payment Capabilities) -->
                        <div id="paymentCapabilitiesSection" style="display: none;">
                            <h6 class="fw-bold text-primary mb-3">Bank Details (For Payments Settlement)</h6>
                            <div class="row mb-4">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required fw-medium text-slate-700">IFSC Code</label>
                                    <input type="text" name="ifsc_code" id="ifscCodeInput" class="form-control bank-field text-uppercase" placeholder="11-digit IFSC Code (e.g. SBIN0001234)" value="{{ old('ifsc_code') }}">
                                    <span id="ifscHelperText" class="form-text fs-11 text-muted">Entering a valid IFSC code will automatically load the bank name and branch name.</span>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required fw-medium text-slate-700">Account Holder Name</label>
                                    <input type="text" name="account_holder_name" class="form-control bank-field" placeholder="Holder Name" value="{{ old('account_holder_name') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required fw-medium text-slate-700">Bank Account Number</label>
                                    <input type="text" name="bank_account_number" class="form-control bank-field" placeholder="Account Number" value="{{ old('bank_account_number') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required fw-medium text-slate-700">Bank Name</label>
                                    <input type="text" name="bank_name" id="bankNameInput" class="form-control bank-field" placeholder="Bank Name" value="{{ old('bank_name') }}">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label required fw-medium text-slate-700">Branch Name</label>
                                    <input type="text" name="branch_name" id="branchNameInput" class="form-control bank-field" placeholder="Branch Name" value="{{ old('branch_name') }}">
                                </div>
                            </div>
                        </div>

                        <!-- Login Details (Always Available) -->
                        <h6 class="fw-bold text-primary mb-3">Login Details <span class="fw-normal text-muted fs-12">(Optional for seller-managed stores, required for independent access)</span></h6>
                        <div class="row mb-4">
                            <!-- Password -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium text-slate-700">Password <span class="password-req-asterisk text-danger" style="display:none;">*</span></label>
                                <input type="password" name="password" class="form-control credential-field" placeholder="At least 8 characters">
                            </div>

                            <!-- Confirm Password -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium text-slate-700">Confirm Password <span class="password-req-asterisk text-danger" style="display:none;">*</span></label>
                                <input type="password" name="password_confirmation" class="form-control credential-field" placeholder="Re-enter password">
                            </div>
                        </div>

                        <!-- Status Details (Visible to both) -->
                        <h6 class="fw-bold text-primary mb-3">Status Details</h6>
                        <div class="row mb-4">
                            <!-- Status -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label required fw-medium text-slate-700">Status</label>
                                <select name="status" class="form-select" required>
                                    <option value="Active" {{ old('status', 'Active') === 'Active' ? 'selected' : '' }}>Active</option>
                                    <option value="Inactive" {{ old('status') === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">
                                <iconify-icon icon="solar:check-circle-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                                Register Store
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const stateSelect = document.getElementById('stateSelect');
    const districtSelect = document.getElementById('districtSelect');

    stateSelect.addEventListener('change', function () {
        const stateId = this.value;
        districtSelect.innerHTML = '<option value="">Loading districts...</option>';

        if (!stateId) {
            districtSelect.innerHTML = '<option value="">— Select State First —</option>';
            return;
        }

        fetch(`/api/locations/districts?state_id=${stateId}`)
            .then(res => res.json())
            .then(data => {
                districtSelect.innerHTML = '<option value="">— Select District —</option>';
                data.forEach(d => {
                    const option = document.createElement('option');
                    option.value = d.id;
                    option.textContent = d.name;
                    districtSelect.appendChild(option);
                });
            })
            .catch(() => {
                districtSelect.innerHTML = '<option value="">Error loading districts</option>';
            });
    });

    // Trigger state change if old state was selected on validation error
    if (stateSelect.value) {
        const event = new Event('change');
        stateSelect.dispatchEvent(event);
    }

    // Toggle logic for Payment & Dispatch Capabilities
    const capabilityYes = document.getElementById('capability_yes');
    const capabilityNo = document.getElementById('capability_no');
    const paymentSection = document.getElementById('paymentCapabilitiesSection');
    
    const bankFields = document.querySelectorAll('.bank-field');
    const credentialFields = document.querySelectorAll('.credential-field');

    const passAsterisks = document.querySelectorAll('.password-req-asterisk');

    function toggleCapabilityView() {
        if (capabilityYes.checked) {
            paymentSection.style.display = 'block';
            bankFields.forEach(f => f.setAttribute('required', 'required'));
            credentialFields.forEach(f => f.setAttribute('required', 'required'));
            passAsterisks.forEach(a => a.style.display = 'inline');
        } else {
            paymentSection.style.display = 'none';
            bankFields.forEach(f => f.removeAttribute('required'));
            credentialFields.forEach(f => f.removeAttribute('required'));
            passAsterisks.forEach(a => a.style.display = 'none');
        }
    }

    capabilityYes.addEventListener('change', toggleCapabilityView);
    capabilityNo.addEventListener('change', toggleCapabilityView);

    // Initial run
    toggleCapabilityView();

    // IFSC Code auto-fetch logic
    const ifscInput = document.getElementById('ifscCodeInput');
    const bankNameInput = document.getElementById('bankNameInput');
    const branchNameInput = document.getElementById('branchNameInput');
    const ifscHelperText = document.getElementById('ifscHelperText');

    function fetchBankDetailsByIfsc() {
        const ifsc = ifscInput.value.trim().toUpperCase();
        if (ifsc.length !== 11) {
            ifscHelperText.textContent = 'Please enter a valid 11-character IFSC code.';
            ifscHelperText.className = 'form-text fs-11 text-danger';
            return;
        }

        ifscHelperText.textContent = 'Fetching bank details...';
        ifscHelperText.className = 'form-text fs-11 text-info';

        fetch(`https://ifsc.razorpay.com/${ifsc}`)
            .then(res => {
                if (!res.ok) {
                    throw new Error('Invalid IFSC code or API offline.');
                }
                return res.json();
            })
            .then(data => {
                bankNameInput.value = data.BANK || '';
                branchNameInput.value = data.BRANCH || '';
                ifscHelperText.textContent = 'IFSC details loaded successfully!';
                ifscHelperText.className = 'form-text fs-11 text-success';
            })
            .catch(err => {
                ifscHelperText.textContent = 'Could not find details for this IFSC code. Please enter manually.';
                ifscHelperText.className = 'form-text fs-11 text-warning';
            });
    }

    ifscInput.addEventListener('input', function(e) {
        const val = this.value.trim();
        if (val.length === 11) {
            fetchBankDetailsByIfsc();
        } else if (val.length === 0) {
            ifscHelperText.textContent = 'Entering a valid IFSC code will automatically load the bank name and branch name.';
            ifscHelperText.className = 'form-text fs-11 text-muted';
        } else {
            ifscHelperText.textContent = 'IFSC Code must be exactly 11 characters.';
            ifscHelperText.className = 'form-text fs-11 text-muted';
        }
    });

    // Form submit validation for Mobile & Pincode
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function (e) {
            const mobEl = document.getElementById('mobileInput');
            const pinEl = document.getElementById('pincodeInput');

            if (mobEl && !/^\d{10}$/.test(mobEl.value.trim())) {
                e.preventDefault();
                alert('Mobile number must be exactly 10 numeric digits.');
                mobEl.focus();
                return false;
            }

            if (pinEl && !/^\d{6}$/.test(pinEl.value.trim())) {
                e.preventDefault();
                alert('Pincode must be exactly 6 numeric digits.');
                pinEl.focus();
                return false;
            }
        });
    }
});
</script>
@endpush
