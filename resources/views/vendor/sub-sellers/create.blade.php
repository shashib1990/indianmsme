@extends('layouts.vendor')

@section('title', 'Register Sub-Seller')
@section('page-title', 'Add Sub-Seller')

@section('content')
    <div class="row">
        <div class="col-12 mb-3">
            <a href="{{ route('vendor.sub-sellers.index') }}" class="btn btn-sm btn-outline-secondary">
                <iconify-icon icon="solar:arrow-left-bold-duotone" class="align-middle me-1"></iconify-icon>
                Back to List
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title fw-bold text-slate-800 mb-0">Register Sub-Seller Profile</h5>
                </div>
                <div class="card-body">


                    <form action="{{ route('vendor.sub-sellers.store') }}" method="POST">
                        @csrf
                        <div class="row">
                            <!-- Company Name -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label required fw-medium text-slate-700">Company Name</label>
                                <input type="text" name="company_name" class="form-control"
                                    placeholder="e.g. Acme Sub-Seller Ltd" value="{{ old('company_name') }}" required>
                            </div>

                            <!-- Company Type -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label required fw-medium text-slate-700">Company Type</label>
                                <select name="company_type" class="form-select" required>
                                    <option value="">— Select Type —</option>
                                    <option value="Company" {{ old('company_type') == 'Company' ? 'selected' : '' }}>Company
                                    </option>
                                    <option value="LLP" {{ old('company_type') == 'LLP' ? 'selected' : '' }}>LLP</option>
                                    <option value="Proprietorship Firm" {{ old('company_type') == 'Proprietorship Firm' ? 'selected' : '' }}>Proprietorship Firm</option>
                                    <option value="Other" {{ old('company_type') == 'Other' ? 'selected' : '' }}>Other
                                    </option>
                                </select>
                            </div>

                            <!-- GST Number -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium text-slate-700">GST Number <span class="text-muted small">(Optional)</span></label>
                                <input type="text" name="gst_number" class="form-control"
                                    placeholder="e.g. 07AAAAA1111A1Z1" value="{{ old('gst_number') }}" maxlength="15">
                            </div>

                            <!-- Primary Category -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label required fw-medium text-slate-700">Primary Category</label>
                                <select name="category_id" class="form-select" required>
                                    <option value="">— Select Category —</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Representative Name -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label required fw-medium text-slate-700">Authorized
                                    Representative</label>
                                <input type="text" name="authorized_rep_name" class="form-control"
                                    placeholder="Representative Full Name" value="{{ old('authorized_rep_name') }}"
                                    required>
                            </div>

                            <!-- Email -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label required fw-medium text-slate-700">Email Address</label>
                                <input type="email" name="email" class="form-control" placeholder="subseller@domain.com"
                                    value="{{ old('email') }}" required>
                            </div>

                            <!-- Mobile -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label required fw-medium text-slate-700">Mobile Number</label>
                                <input type="text" name="mobile" class="form-control" placeholder="10-digit mobile number"
                                    value="{{ old('mobile') }}" maxlength="10" pattern="[0-9]{10}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required>
                            </div>

                            <!-- State -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label required fw-medium text-slate-700">State</label>
                                <select id="stateSelect" name="state" class="form-select" required>
                                    <option value="">— Select State —</option>
                                    @foreach($states as $st)
                                        <option value="{{ $st->id }}" {{ old('state') == $st->id ? 'selected' : '' }}>
                                            {{ $st->name }}</option>
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

                            <!-- Bank Details Header -->
                            <div class="col-12 mt-3 mb-2">
                                <h6 class="fw-bold text-slate-800 border-bottom pb-2">
                                    <iconify-icon icon="solar:card-2-bold-duotone"
                                        class="align-middle me-1 text-primary fs-18"></iconify-icon>
                                    Bank Details
                                </h6>
                            </div>

                            <!-- Account Holder Name -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium text-slate-700">Account Holder Name</label>
                                <input type="text" name="account_holder_name" class="form-control"
                                    value="{{ old('account_holder_name') }}" placeholder="e.g. John Doe">
                            </div>

                            <!-- Bank Account Number -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium text-slate-700">Bank Account Number</label>
                                <input type="text" name="bank_account_number" class="form-control"
                                    value="{{ old('bank_account_number') }}" placeholder="e.g. 50200012345678">
                            </div>

                            <!-- IFSC Code -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-medium text-slate-700">IFSC Code</label>
                                <input type="text" name="ifsc_code" id="ifsc_code" class="form-control"
                                    value="{{ old('ifsc_code') }}" placeholder="e.g. HDFC0000123">
                            </div>

                            <!-- Bank Name -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-medium text-slate-700">Bank Name</label>
                                <input type="text" name="bank_name" id="bank_name" class="form-control"
                                    value="{{ old('bank_name') }}" placeholder="Bank Name (Auto-fetched)" readonly>
                            </div>

                            <!-- Branch Name -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-medium text-slate-700">Branch Name</label>
                                <input type="text" name="branch_name" id="branch_name" class="form-control"
                                    value="{{ old('branch_name') }}" placeholder="Branch Name (Auto-fetched)" readonly>
                            </div>

                            <!-- Credentials Header -->
                            <div class="col-12 mt-3 mb-2">
                                <h6 class="fw-bold text-slate-800 border-bottom pb-2">
                                    <iconify-icon icon="solar:key-bold-duotone"
                                        class="align-middle me-1 text-primary fs-18"></iconify-icon>
                                    Account Credentials
                                </h6>
                            </div>



                            <!-- Password -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label required fw-medium text-slate-700">Password</label>
                                <input type="password" name="password" class="form-control"
                                    placeholder="At least 8 characters" required>
                            </div>

                            <!-- Confirm Password -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label required fw-medium text-slate-700">Confirm Password</label>
                                <input type="password" name="password_confirmation" class="form-control"
                                    placeholder="Confirm password" required>
                            </div>
                        </div>

                        <div class="mt-3">
                            <button type="submit" class="btn btn-success">
                                <iconify-icon icon="solar:check-circle-bold-duotone"
                                    class="align-middle me-1 fs-18"></iconify-icon>
                                Register Sub-Seller
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
                    .catch(err => {
                        districtSelect.innerHTML = '<option value="">Error loading districts</option>';
                    });
            });

            // IFSC Code Auto-fetch
            const ifscInput = document.getElementById('ifsc_code');
            const bankInput = document.getElementById('bank_name');
            const branchInput = document.getElementById('branch_name');

            if (ifscInput && bankInput && branchInput) {
                ifscInput.addEventListener('input', function () {
                    const ifsc = this.value.trim().toUpperCase();
                    if (ifsc.length === 11) {
                        bankInput.value = 'Loading bank name...';
                        branchInput.value = 'Loading branch name...';

                        fetch('https://ifsc.razorpay.com/' + ifsc)
                            .then(res => {
                                if (!res.ok) throw new Error('Invalid IFSC Code');
                                return res.json();
                            })
                            .then(data => {
                                bankInput.value = data.BANK;
                                branchInput.value = data.BRANCH;
                            })
                            .catch(err => {
                                bankInput.value = '';
                                branchInput.value = '';
                            });
                    }
                });
            }
        });
    </script>
@endpush