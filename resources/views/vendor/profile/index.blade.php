@extends('layouts.vendor')

@section('title', 'My Profile')
@section('page-title', 'My Profile')

@section('content')
@push('styles')
<style>
    #profileTabs .nav-link.active {
        background-color: #198754 !important;
        border-color: #198754 !important;
        color: #fff !important;
    }
    #profileTabs .nav-link {
        color: #198754;
    }
    #profileTabs .nav-link:hover {
        color: #157347;
    }
</style>
@endpush

<div class="row">
    <!-- Left Profile Card -->
    <div class="col-xl-4 col-lg-5">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body text-center pt-4">
                <div class="position-relative d-inline-block mb-3">
                    <img src="{{ $vendor->avatar ? (filter_var($vendor->avatar, FILTER_VALIDATE_URL) ? $vendor->avatar : asset('storage/' . $vendor->avatar)) : asset('admin-assets/images/users/avatar-1.jpg') }}" 
                         alt="Avatar" 
                         class="rounded-circle border border-3 border-light shadow" 
                         width="120" height="120"
                         style="object-fit: cover;"
                         id="vendor-avatar-preview">
                    <!-- Profile upload loader overlay -->
                    <div id="vendor-avatar-loader" class="position-absolute top-0 start-0 w-100 h-100 rounded-circle bg-dark bg-opacity-50 d-none flex-column align-items-center justify-content-center text-white" style="z-index: 10;">
                        <span class="spinner-border spinner-border-sm mb-1" role="status" aria-hidden="true"></span>
                        <span class="fs-10 fw-bold">Saving...</span>
                    </div>
                    <label for="profile_image_input" class="btn btn-sm btn-success rounded-circle position-absolute bottom-0 end-0 p-1 d-flex align-items-center justify-content-center" style="width: 32px; height: 32px; cursor: pointer;" title="Change Profile Image">
                        <iconify-icon icon="solar:camera-bold-duotone" class="fs-16"></iconify-icon>
                    </label>
                </div>
                <h4 class="fw-bold text-slate-800 mb-1">{{ $vendor->name }}</h4>
                <p class="text-muted mb-2"><code class="fs-13 fw-semibold">{{ $vendor->seller_id ?? 'N/A' }}</code></p>
                
                <div class="d-flex justify-content-center gap-2 mb-3">
                    @if($vendor->is_active)
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

                <div class="text-start mt-4 pt-3 border-top">
                    <p class="text-muted mb-2 small text-uppercase fw-semibold tracking-wider">Quick Details</p>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <iconify-icon icon="solar:letter-bold-duotone" class="text-primary fs-18"></iconify-icon>
                        <span class="text-slate-700 fs-14 text-truncate" style="max-width: 100%;">{{ $vendor->email }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <iconify-icon icon="solar:phone-bold-duotone" class="text-primary fs-18"></iconify-icon>
                        <span class="text-slate-700 fs-14">{{ $vendor->phone ?? 'Not Provided' }}</span>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:tag-bold-duotone" class="text-primary fs-18"></iconify-icon>
                        <span class="text-slate-700 fs-14">
                            {{ $vendor->sellerProfile->category->name ?? 'Category Not Set' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Right Content Tabs -->
    <div class="col-xl-8 col-lg-7">
        <!-- Hidden input for file change -->
        <input type="file" name="profile_image" id="profile_image_input" class="d-none" accept="image/*" onchange="previewImage(this)">

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3">
                <ul class="nav nav-pills card-header-pills" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ session('active_tab', 'basic') === 'basic' ? 'active' : '' }} fw-medium px-4" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic-panel" type="button" role="tab">
                            Basic Profile
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ session('active_tab') === 'kyc' ? 'active' : '' }} fw-medium px-4" id="kyc-tab" data-bs-toggle="tab" data-bs-target="#kyc-panel" type="button" role="tab">
                            KYC Documents
                        </button>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ session('active_tab') === 'bank' ? 'active' : '' }} fw-medium px-4" id="bank-tab" data-bs-toggle="tab" data-bs-target="#bank-panel" type="button" role="tab">
                            Bank Details
                        </button>
                    </li>
                    @if($vendor->isEligibleForDdcSubscription())
                        <li class="nav-item" role="presentation">
                            <button class="nav-link {{ session('active_tab') === 'ddc' ? 'active' : '' }} fw-medium px-4" id="ddc-tab" data-bs-toggle="tab" data-bs-target="#ddc-panel" type="button" role="tab">
                                DDC Subscription
                            </button>
                        </li>
                    @endif
                </ul>
            </div>
            <div class="card-body">
                <div class="tab-content" id="profileTabContent">
                    
                    <!-- TAB 1: BASIC PROFILE -->
                    <div class="tab-pane fade {{ session('active_tab', 'basic') === 'basic' ? 'show active' : '' }}" id="basic-panel" role="tabpanel">
                        <form action="{{ route('vendor.profile.update') }}" method="POST" enctype="multipart/form-data" class="profile-form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="active_tab" value="basic">

                            <h5 class="fw-bold text-slate-800 mb-3">Seller Basic Profile</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-slate-700 fw-semibold">Seller ID / Username</label>
                                    <input type="text" class="form-control bg-light" value="{{ $vendor->seller_id ?? 'N/A'}}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-slate-700 fw-semibold">Company / Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control" value="{{ old('name', $vendor->name) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-slate-700 fw-semibold">Authorized Representative Name <span class="text-danger">*</span></label>
                                    <input type="text" name="authorized_rep_name" class="form-control" value="{{ old('authorized_rep_name', $vendor->sellerProfile->authorized_rep_name ?? '') }}" required placeholder="e.g. MANISH NARANG">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-slate-700 fw-semibold">Contact Phone <span class="text-danger">*</span></label>
                                    <input type="text" name="mobile" class="form-control" value="{{ old('mobile', $vendor->mobile ?? $vendor->phone ?? '') }}" maxlength="10" pattern="[0-9]{10}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" required placeholder="e.g. 9728785195">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-slate-700 fw-semibold">Alternate Phone</label>
                                    <input type="text" name="alternate_mobile" class="form-control" value="{{ old('alternate_mobile', $vendor->sellerProfile->alternate_mobile ?? '') }}" maxlength="10" pattern="[0-9]{10}" oninput="this.value = this.value.replace(/[^0-9]/g, '')" placeholder="e.g. 9876543210">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-slate-700 fw-semibold">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control" value="{{ old('email', $vendor->email) }}" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-slate-700 fw-semibold">Alternate Email Address</label>
                                    <input type="email" name="alternate_email" class="form-control" value="{{ old('alternate_email', $vendor->sellerProfile->alternate_email ?? '') }}" placeholder="e.g. alternate@example.com">
                                </div>
                                <div class="col-md-4">
                                     <label class="form-label text-slate-700 fw-semibold">Business Category <span class="text-danger">*</span></label>
                                     @php
                                         $selectedCategoryId = old('category_id', $vendor->sellerProfile->category_id ?? '');
                                     @endphp
                                     <select name="category_id" class="form-select s-select" required data-placeholder="Select Category">
                                         <option value="">Select Category</option>
                                         @foreach($categories as $cat)
                                             <option value="{{ $cat->id }}" {{ $selectedCategoryId == $cat->id ? 'selected' : '' }}>
                                                 {{ $cat->name }}
                                             </option>
                                         @endforeach
                                     </select>
                                 </div>
                                 <div class="col-md-4">
                                     <label class="form-label text-slate-700 fw-semibold">GST Number</label>
                                     <input type="text" name="gst_number" class="form-control text-uppercase @error('gst_number') is-invalid @enderror" value="{{ old('gst_number', $vendor->gst_number ?? ($vendor->sellerProfile->gst_number ?? '')) }}" minlength="15" maxlength="15" oninput="this.value = this.value.toUpperCase().slice(0, 15)" placeholder="15-digit GSTIN (Optional)">
                                     @error('gst_number')
                                         <div class="invalid-feedback">{{ $message }}</div>
                                     @enderror
                                 </div>
                                <div class="col-md-4">
                                    <label class="form-label text-slate-700 fw-semibold">Seller Type <span class="text-danger">*</span></label>
                                    @php
                                        $selectedSellerType = old('seller_type', $vendor->sellerProfile->seller_type ?? '');
                                    @endphp
                                    <select id="profile_seller_type" name="seller_type" class="form-select" required>
                                        <option value="">Select Seller Type</option>
                                        <option value="Trader" {{ $selectedSellerType == 'Trader' ? 'selected' : '' }}>Trader</option>
                                        <option value="Manufacturer" {{ $selectedSellerType == 'Manufacturer' ? 'selected' : '' }}>Manufacturer</option>
                                        <option value="Distributor" {{ $selectedSellerType == 'Distributor' ? 'selected' : '' }}>Distributor</option>
                                    </select>
                                </div>
                                <div class="col-md-12 mb-3" id="profile-brand-container" style="display: none;">
                                    <label class="form-label text-slate-700 fw-semibold">Brand Name <span class="text-danger">*</span></label>
                                    @php
                                        $currBrands = old('brand_name', $vendor->brand_name ?? ($vendor->sellerProfile->brand_name ?? ''));
                                        if (is_array($currBrands)) {
                                            $currBrands = implode(', ', array_filter(array_map('trim', $currBrands)));
                                        }
                                    @endphp
                                    <input type="text" id="profile_brand_name" name="brand_name" class="form-control" placeholder="e.g. Acme Brand" value="{{ $currBrands }}">
                                    <small class="text-muted fs-11">Enter brand name (required for manufacturers).</small>
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label text-slate-700 fw-semibold">Office Address <span class="text-danger">*</span></label>
                                    <textarea name="address" class="form-control" rows="2" required placeholder="Full business address">{{ old('address', $vendor->address) }}</textarea>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-slate-700 fw-semibold">State <span class="text-danger">*</span></label>
                                    <select name="state_id" id="state_id" class="form-select s-select" required data-placeholder="Select State">
                                        <option value="">Select State</option>
                                        @foreach($states as $state)
                                            <option value="{{ $state->id }}" {{ old('state_id', $selectedStateId) == $state->id ? 'selected' : '' }}>
                                                {{ $state->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-slate-700 fw-semibold">District <span class="text-danger">*</span></label>
                                    <select name="district_id" id="district_id" class="form-select s-select" required data-placeholder="Select District">
                                        <option value="">Select District</option>
                                        @foreach($districts as $district)
                                            <option value="{{ $district->id }}" {{ old('district_id', $selectedDistrictId) == $district->id ? 'selected' : '' }}>
                                                {{ $district->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-slate-700 fw-semibold">Pincode <span class="text-danger">*</span></label>
                                    <input type="text" name="pincode" class="form-control" value="{{ old('pincode', $vendor->pincode) }}" required placeholder="e.g. 124001">
                                </div>
                            </div>
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-success px-4 py-2">
                                    <iconify-icon icon="solar:diskette-bold-duotone" class="me-1 fs-16 align-middle"></iconify-icon> Save Basic Details
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB 2: KYC DOCUMENTS -->
                    <div class="tab-pane fade {{ session('active_tab') === 'kyc' ? 'show active' : '' }}" id="kyc-panel" role="tabpanel">
                        <form action="{{ route('vendor.profile.update') }}" method="POST" enctype="multipart/form-data" class="profile-form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="active_tab" value="kyc">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h5 class="fw-bold text-slate-800 mb-0">KYC Verification Details</h5>
                                <span class="badge bg-warning-subtle text-warning px-2.5 py-1 fs-12">Status: {{ $vendor->sellerProfile->kyc_status ?? 'PENDING' }}</span>
                            </div>

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
                                                        @else
                                                            <span class="text-muted font-normal">(Optional)</span>
                                                        @endif
                                                    </label>
                                                    
                                                    @if($uploadedPath)
                                                        <span class="badge bg-success-subtle text-success px-2 py-0.5 fs-11 rounded">Uploaded</span>
                                                    @else
                                                        @if($doc['mandatory'])
                                                            <span class="badge bg-danger-subtle text-danger px-2 py-0.5 fs-11 rounded">Required</span>
                                                        @else
                                                            <span class="badge bg-secondary-subtle text-secondary px-2 py-0.5 fs-11 rounded">Optional</span>
                                                        @endif
                                                    @endif
                                                </div>

                                                <input type="file" name="{{ $doc['key'] }}" class="form-control form-control-sm mb-2" accept=".jpg,.jpeg,.png,.webp,.pdf">

                                                @if($uploadedPath)
                                                    <div class="d-flex align-items-center justify-content-between mt-2 p-1.5 bg-light rounded border border-light-subtle">
                                                        <div class="d-flex align-items-center gap-2">
                                                            @if($isPdf)
                                                                <iconify-icon icon="solar:document-text-bold-duotone" class="text-danger fs-24"></iconify-icon>
                                                            @else
                                                                <img src="{{ filter_var($uploadedPath, FILTER_VALIDATE_URL) ? $uploadedPath : asset('storage/' . $uploadedPath) }}" alt="Preview" class="rounded border" width="24" height="24" style="object-fit: cover;">
                                                            @endif
                                                        </div>
                                                        <a href="{{ filter_var($uploadedPath, FILTER_VALIDATE_URL) ? $uploadedPath : asset('storage/' . $uploadedPath) }}" target="_blank" class="btn btn-xs btn-outline-primary py-0.5 px-2">
                                                            View File
                                                        </a>
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-success px-4 py-2">
                                    <iconify-icon icon="solar:diskette-bold-duotone" class="me-1 fs-16 align-middle"></iconify-icon> Save KYC Documents
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB 3: BANK DETAILS -->
                    <div class="tab-pane fade {{ session('active_tab') === 'bank' ? 'show active' : '' }}" id="bank-panel" role="tabpanel">
                        <form action="{{ route('vendor.profile.update') }}" method="POST" enctype="multipart/form-data" class="profile-form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="active_tab" value="bank">
                            <h5 class="fw-bold text-slate-800 mb-3">Bank Details</h5>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-slate-700 fw-semibold">Account Holder Name <span class="text-danger">*</span></label>
                                    <input type="text" name="account_holder_name" class="form-control @error('account_holder_name') is-invalid @enderror" value="{{ old('account_holder_name', $vendor->sellerProfile->account_holder_name ?? '') }}" placeholder="e.g. MANISH NARANG" required>
                                    @error('account_holder_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-slate-700 fw-semibold">Bank Account Number <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_account_number" class="form-control @error('bank_account_number') is-invalid @enderror" value="{{ old('bank_account_number', $vendor->sellerProfile->bank_account_number ?? '') }}" placeholder="e.g. 50200012345678" required>
                                    @error('bank_account_number')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-slate-700 fw-semibold">IFSC Code <span class="text-danger">*</span></label>
                                    <input type="text" name="ifsc_code" id="ifsc_code" class="form-control text-uppercase @error('ifsc_code') is-invalid @enderror" value="{{ old('ifsc_code', $vendor->sellerProfile->ifsc_code ?? '') }}" placeholder="e.g. HDFC0000123" maxlength="11" oninput="this.value = this.value.toUpperCase()" required>
                                    @error('ifsc_code')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-slate-700 fw-semibold">Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_name" id="bank_name" class="form-control @error('bank_name') is-invalid @enderror" value="{{ old('bank_name', $vendor->sellerProfile->bank_name ?? '') }}" placeholder="Bank Name" required>
                                    @error('bank_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-slate-700 fw-semibold">Branch Name <span class="text-danger">*</span></label>
                                    <input type="text" name="branch_name" id="branch_name" class="form-control @error('branch_name') is-invalid @enderror" value="{{ old('branch_name', $vendor->sellerProfile->branch_name ?? '') }}" placeholder="Branch Name" required>
                                    @error('branch_name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-success px-4 py-2">
                                    <iconify-icon icon="solar:diskette-bold-duotone" class="me-1 fs-16 align-middle"></iconify-icon> Save Bank Details
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB 4: DDC MODEL SUBSCRIPTION -->
                    @if($vendor->isEligibleForDdcSubscription())
                        @php
                            $ddcSubStatus = $vendor->sellerProfile?->ddc_subscription_status ?? 'none';
                            $isSubscribed = ($ddcSubStatus === 'approved');
                        @endphp
                        <div class="tab-pane fade {{ session('active_tab') === 'ddc' ? 'show active' : '' }}" id="ddc-panel" role="tabpanel">
                            <h5 class="fw-bold text-slate-800 mb-3">DDC Model Subscription</h5>
                            
                            @if($ddcSubStatus === 'none' || $ddcSubStatus === 'rejected')
                                @if($ddcSubStatus === 'rejected')
                                    <div class="alert alert-danger mb-3 p-3 border-danger">
                                        <h6 class="fw-bold mb-1 d-flex align-items-center gap-1">
                                            <iconify-icon icon="solar:danger-bold" class="fs-18"></iconify-icon> Request Rejected
                                        </h6>
                                        <p class="mb-0 fs-13">Your previous DDC subscription request was rejected by the admin. <br><strong>Reason:</strong> {{ $vendor->sellerProfile->ddc_subscription_reject_reason }}</p>
                                    </div>
                                @endif

                                <div class="card border border-light-subtle rounded-3 p-4 mb-0 bg-light-subtle">
                                    <h6 class="fw-bold text-slate-800 mb-3 d-flex align-items-center gap-1">
                                        <iconify-icon icon="solar:card-transfer-bold-duotone" class="text-primary fs-20"></iconify-icon>
                                        Subscribe to DDC Model — Payment Required (₹10,000)
                                    </h6>

                                    <!-- Official Bank Details Box -->
                                    <div class="row g-3 mb-4 p-3 rounded-3 bg-white border">
                                        <div class="col-md-7 border-end">
                                            <h6 class="fw-bold text-dark fs-13 mb-2">Official Bank Details:</h6>
                                            <div class="fs-12 mb-1.5"><strong>Bank Name:</strong> {{ $aswtBankName }}</div>
                                            <div class="fs-12 mb-1.5"><strong>Account Holder Name:</strong> {{ $aswtAccountName }}</div>
                                            <div class="fs-12 mb-1.5"><strong>Account Number:</strong> <code class="fw-bold text-primary fs-13">{{ $aswtAccountNumber }}</code></div>
                                            <div class="fs-12 mb-1.5"><strong>IFSC Code:</strong> <code class="fw-bold text-primary fs-13">{{ $aswtIfscCode }}</code></div>
                                            @if($aswtBankBranch)
                                                <div class="fs-12 mb-1.5"><strong>Branch:</strong> {{ $aswtBankBranch }}</div>
                                            @endif
                                            @if($aswtUpiId)
                                                <div class="fs-12 mb-0"><strong>UPI ID:</strong> <code class="fw-bold text-success fs-13">{{ $aswtUpiId }}</code></div>
                                            @endif
                                        </div>
                                        <div class="col-md-5 text-center d-flex flex-column align-items-center justify-content-center">
                                            <div class="mb-1 text-muted small fw-semibold">Scan QR Code to Pay</div>
                                            @if($aswtQrCode)
                                                <img src="{{ $aswtQrCode }}" alt="Payment QR Code" class="img-fluid border rounded bg-white shadow-sm p-1 mb-1" style="max-height: 140px; object-fit: contain;">
                                            @else
                                                <img src="{{ asset('assets/images/ddc_payment_qr.png') }}" alt="Payment QR Code" class="img-fluid border rounded bg-white shadow-sm p-1 mb-1" style="max-height: 140px; object-fit: contain;">
                                            @endif
                                            <div class="fw-bold text-success fs-15 mt-1">Amount: ₹10,000</div>
                                        </div>
                                    </div>

                                    <!-- Payment Verification Submission Form -->
                                    <form action="{{ route('vendor.profile.ddc-subscription') }}" method="POST" enctype="multipart/form-data">
                                        @csrf
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <label for="profile_ddc_subscription_utr" class="form-label fw-bold text-slate-800 mb-1">UTR / Transaction Reference ID <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control form-control-sm fw-bold" name="ddc_subscription_utr" id="profile_ddc_subscription_utr" placeholder="Enter 12-digit UTR or Ref No." required>
                                            </div>
                                            <div class="col-md-6">
                                                <label for="profile_ddc_subscription_slip" class="form-label fw-bold text-slate-800 mb-1">Upload Payment Slip / Receipt Proof <span class="text-danger">*</span></label>
                                                <input type="file" class="form-control form-control-sm" name="ddc_subscription_slip" id="profile_ddc_subscription_slip" accept="image/*,application/pdf" required>
                                                <span class="d-block text-muted fs-10 mt-1">Accepts JPG, PNG, PDF up to 5MB</span>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-warning w-100 fw-bold py-2 shadow-sm text-dark mt-3">
                                            <iconify-icon icon="solar:star-bold" class="align-middle me-1"></iconify-icon>
                                            Submit DDC Subscription
                                        </button>
                                    </form>
                                </div>
                            @elseif($ddcSubStatus === 'pending')
                                <div class="alert alert-warning mb-0 p-3 border-warning rounded-3">
                                    <h6 class="fw-bold mb-2 d-flex align-items-center gap-1">
                                        <iconify-icon icon="solar:clock-square-bold-duotone" class="fs-20"></iconify-icon>
                                        Request Pending Admin Approval
                                    </h6>
                                    <p class="fs-13 text-dark mb-3">Your DDC subscription request has been submitted and is currently being verified by the admin team.</p>
                                    <div class="p-2.5 bg-white border rounded-2 fs-12 text-slate-700">
                                        <div><strong>UTR Reference:</strong> <code>{{ $vendor->sellerProfile->ddc_subscription_utr }}</code></div>
                                        @if($vendor->sellerProfile->ddc_subscription_submitted_at)
                                            <div><strong>Submitted On:</strong> {{ \Carbon\Carbon::parse($vendor->sellerProfile->ddc_subscription_submitted_at)->format('d M Y, h:i A') }}</div>
                                        @endif
                                    </div>
                                </div>
                            @elseif($ddcSubStatus === 'approved')
                                <div class="alert alert-success mb-0 p-3 border-success rounded-3 text-center">
                                    <h5 class="fw-bold mb-2 text-success d-flex align-items-center justify-content-center gap-1">
                                        <iconify-icon icon="solar:verified-check-bold" class="fs-24"></iconify-icon>
                                        DDC Subscription Active
                                    </h5>
                                    <p class="fs-14 text-dark mb-0">Congratulations! Your company is actively registered under the Ardh Sainik Mall DDC Phygital Distribution Model.</p>
                                </div>
                            @endif
                        </div>
                    @endif
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
            var reader = new FileReader();
            reader.onload = function(e) {
                const img = document.getElementById('vendor-avatar-preview');
                if (img) {
                    img.src = e.target.result;
                }
                if (window.showToast) {
                    window.showToast('Profile picture selected! Click "Save" on any tab to save it.', 'info');
                }
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    document.addEventListener('DOMContentLoaded', function () {
        // State to District dynamic binding
        const stateSelectEl = document.getElementById('state_id');
        const districtSelectEl = document.getElementById('district_id');

        if (stateSelectEl && districtSelectEl) {
            stateSelectEl.addEventListener('change', function () {
                const stateId = this.value;
                if (!districtSelectEl.tomselect) return;
                
                const districtSelect = districtSelectEl.tomselect;
                districtSelect.clear();
                districtSelect.clearOptions();

                if (stateId) {
                    districtSelect.settings.placeholder = "Loading districts...";
                    districtSelect.inputState();

                    fetch('{{ route("vendor.api.locations.districts") }}?state_id=' + stateId)
                        .then(res => res.json())
                        .then(data => {
                            const options = data.map(item => ({
                                value: item.id,
                                text: item.name
                            }));
                            districtSelect.addOptions(options);
                            districtSelect.settings.placeholder = "Select District";
                            districtSelect.inputState();
                        })
                        .catch(err => {
                            districtSelect.settings.placeholder = "Error loading districts";
                            districtSelect.inputState();
                        });
                } else {
                    districtSelect.settings.placeholder = "Select State First";
                    districtSelect.inputState();
                }
            });
        }

        // IFSC code autocomplete API
        const ifscInput = document.getElementById('ifsc_code');
        const bankInput = document.getElementById('bank_name');
        const branchInput = document.getElementById('branch_name');

        if (ifscInput && bankInput && branchInput) {
            const fetchIfscDetails = function () {
                const ifsc = ifscInput.value.trim().toUpperCase();
                ifscInput.value = ifsc;
                if (ifsc.length === 11) {
                    bankInput.value = 'Loading bank name...';
                    branchInput.value = 'Loading branch name...';

                    fetch('https://ifsc.razorpay.com/' + ifsc)
                        .then(res => {
                            if (!res.ok) throw new Error('Invalid IFSC Code');
                            return res.json();
                        })
                        .then(data => {
                            bankInput.value = data.BANK || '';
                            branchInput.value = data.BRANCH || '';
                            if (window.showToast) {
                                window.showToast('Bank details fetched successfully!', 'success');
                            }
                        })
                        .catch(err => {
                            bankInput.value = '';
                            branchInput.value = '';
                            if (window.showToast) {
                                window.showToast('Failed to fetch details. Please verify the IFSC code.', 'error');
                            }
                        });
                }
            };

            ifscInput.addEventListener('input', fetchIfscDetails);
            ifscInput.addEventListener('change', fetchIfscDetails);
            ifscInput.addEventListener('blur', fetchIfscDetails);

            if (ifscInput.value.trim().length === 11 && (!bankInput.value.trim() || !branchInput.value.trim())) {
                fetchIfscDetails();
            }
        }

        // Vendor profile forms submission handler
        document.querySelectorAll('.profile-form').forEach(form => {
            form.addEventListener('submit', function() {
                // If a profile image has been selected, move it to the form being submitted
                const fileInput = document.getElementById('profile_image_input');
                if (fileInput && fileInput.files && fileInput.files.length > 0) {
                    this.appendChild(fileInput);
                    
                    // Show avatar save loader overlay
                    const loader = document.getElementById('vendor-avatar-loader');
                    if (loader) {
                        loader.classList.remove('d-none');
                        loader.classList.add('d-flex');
                    }
                }
            });
        });

        // KYC documents preview logic
        const kycInputs = document.querySelectorAll('#kyc-panel input[type="file"]');
        kycInputs.forEach(function (input) {
            input.addEventListener('change', function () {
                // Find or create preview container
                let container = this.parentNode.querySelector('.kyc-new-preview-container');
                if (!container) {
                    container = document.createElement('div');
                    container.className = 'kyc-new-preview-container d-flex align-items-center gap-2 mt-2 p-1.5 rounded border border-warning-subtle bg-warning-subtle bg-opacity-10';
                    // Insert after the input
                    this.parentNode.insertBefore(container, this.nextSibling);
                }

                // If there's an existing uploaded preview container, hide it
                const existingPreview = this.parentNode.querySelector('.bg-light.rounded.border');

                if (this.files && this.files[0]) {
                    if (existingPreview) {
                        existingPreview.style.setProperty('display', 'none', 'important');
                    }
                    const file = this.files[0];
                    const isImage = file.type.startsWith('image/');
                    const isPdf = file.type === 'application/pdf' || file.name.endsWith('.pdf');
                    
                    container.innerHTML = `
                        <div class="preview-media-wrapper d-flex align-items-center justify-content-center" style="width: 30px; height: 30px;">
                            ${isImage ? '<img src="" class="rounded border" width="30" height="30" style="object-fit: cover;">' : ''}
                            ${isPdf ? '<iconify-icon icon="solar:document-text-bold-duotone" class="text-danger fs-24"></iconify-icon>' : ''}
                            ${!isImage && !isPdf ? '<iconify-icon icon="solar:file-bold-duotone" class="text-secondary fs-24"></iconify-icon>' : ''}
                        </div>
                        <div class="flex-grow-1"></div>
                        <span class="badge bg-warning text-dark px-2 py-0.5 fs-10 rounded">Selected</span>
                    `;

                    if (isImage) {
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            const img = container.querySelector('img');
                            if (img) img.src = e.target.result;
                        };
                        reader.readAsDataURL(file);
                    }
                    container.style.display = 'flex';
                } else {
                    if (existingPreview) {
                        existingPreview.style.removeProperty('display');
                    }
                    container.remove();
                }
            });
        });

        // Toggle Brand Name input visibility for vendor profile
        const profSellerTypeSelect = document.getElementById('profile_seller_type');
        const profBrandContainer = document.getElementById('profile-brand-container');
        const profBrandInput = document.getElementById('profile_brand_name');

        function toggleProfBrandContainer() {
            if (!profSellerTypeSelect || !profBrandContainer) return;
            const val = (profSellerTypeSelect.value || '').trim().toLowerCase();
            if (val === 'manufacturer') {
                profBrandContainer.style.display = 'block';
            } else {
                profBrandContainer.style.display = 'none';
                if (profBrandInput) profBrandInput.value = '';
            }
        }

        if (profSellerTypeSelect) {
            profSellerTypeSelect.addEventListener('change', toggleProfBrandContainer);
            if (typeof $ !== 'undefined') {
                $(profSellerTypeSelect).on('change', toggleProfBrandContainer);
            }
            toggleProfBrandContainer();
        }
    });
</script>
@endpush
