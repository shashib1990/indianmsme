@extends('layouts.admin')

@section('title', 'ADPL Bank & QR Details')
@section('page-title', 'ADPL Bank Details & QR Code')

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
        <form action="{{ route('admin.settings.bank.update') }}" method="POST" enctype="multipart/form-data">
            @csrf

            <!-- Bank Details Configuration Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:card-bold-duotone" class="text-primary fs-22"></iconify-icon>
                    <h5 class="card-title fw-bold text-slate-800 mb-0">ADPL Bank Details</h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted fs-13 mb-4">
                        Ye details DDC/ADC panels aur billing sections me payment receive karne ke liye display hongi. Nayi entries add karne ki jagah yahi details update hoti rahengi.
                    </p>

                    <div class="row g-3">
                        <!-- Bank Name -->
                        <div class="col-md-6">
                            <label for="admin_bank_name" class="form-label fw-semibold text-slate-700">Bank Name</label>
                            <input type="text" class="form-control @error('admin_bank_name') is-invalid @enderror" 
                                   id="admin_bank_name" name="admin_bank_name" 
                                   value="{{ old('admin_bank_name', $bankName) }}" 
                                   placeholder="e.g. HDFC Bank">
                            @error('admin_bank_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Account Name -->
                        <div class="col-md-6">
                            <label for="admin_account_name" class="form-label fw-semibold text-slate-700">Account Holder Name</label>
                            <input type="text" class="form-control @error('admin_account_name') is-invalid @enderror" 
                                   id="admin_account_name" name="admin_account_name" 
                                   value="{{ old('admin_account_name', $accountName) }}" 
                                   placeholder="e.g. ASC DISTRIBUTOR PVT LTD">
                            @error('admin_account_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Account Number -->
                        <div class="col-md-6">
                            <label for="admin_account_number" class="form-label fw-semibold text-slate-700">Account Number</label>
                            <input type="text" class="form-control @error('admin_account_number') is-invalid @enderror" 
                                   id="admin_account_number" name="admin_account_number" 
                                   value="{{ old('admin_account_number', $accountNumber) }}" 
                                   placeholder="e.g. 50200089652670">
                            @error('admin_account_number')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- IFSC Code -->
                        <div class="col-md-6">
                            <label for="admin_ifsc_code" class="form-label fw-semibold text-slate-700">IFSC Code</label>
                            <input type="text" class="form-control text-uppercase @error('admin_ifsc_code') is-invalid @enderror" 
                                   id="admin_ifsc_code" name="admin_ifsc_code" 
                                   value="{{ old('admin_ifsc_code', $ifscCode) }}" 
                                   placeholder="e.g. HDFC0000886" maxlength="11">
                            <div id="ifsc_feedback" class="form-text mt-1"></div>
                            @error('admin_ifsc_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- Branch Name -->
                        <div class="col-md-6">
                            <label for="admin_bank_branch" class="form-label fw-semibold text-slate-700">Branch Name</label>
                            <input type="text" class="form-control @error('admin_bank_branch') is-invalid @enderror" 
                                   id="admin_bank_branch" name="admin_bank_branch" 
                                   value="{{ old('admin_bank_branch', $bankBranch) }}" 
                                   placeholder="e.g. Naraina Plaza, Sec A, Rohini, Delhi">
                            @error('admin_bank_branch')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <!-- UPI ID -->
                        <div class="col-md-6">
                            <label for="admin_upi_id" class="form-label fw-semibold text-slate-700">Official UPI ID</label>
                            <input type="text" class="form-control @error('admin_upi_id') is-invalid @enderror" 
                                   id="admin_upi_id" name="admin_upi_id" 
                                   value="{{ old('admin_upi_id', $upiId ?? '') }}" 
                                   placeholder="e.g. ascdistributor@hdfcbank">
                            @error('admin_upi_id')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>

            <!-- Payment QR Code Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:qr-code-bold-duotone" class="text-primary fs-22"></iconify-icon>
                    <h5 class="card-title fw-bold text-slate-800 mb-0">Payment QR Code Image</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row align-items-center g-4">
                        <!-- Current QR Code Preview -->
                        <div class="col-md-4 text-center border-end pe-md-4">
                            <label class="form-label fw-semibold text-slate-700 d-block mb-2">Current QR Code</label>
                            <div class="p-3 bg-light rounded border d-inline-block">
                                @if($qrCode)
                                    <img src="{{ filter_var($qrCode, FILTER_VALIDATE_URL) ? $qrCode : asset('storage/' . $qrCode) }}" 
                                         alt="Admin Payment QR Code" 
                                         class="img-fluid rounded" 
                                         style="max-height: 180px; object-fit: contain;">
                                @else
                                    <img src="{{ asset('admin-assets/images/qr-code.png') }}" 
                                         alt="Default QR Code" 
                                         class="img-fluid rounded" 
                                         style="max-height: 180px; object-fit: contain;">
                                @endif
                            </div>
                            @if($qrCode)
                                <div class="form-check justify-content-center d-flex align-items-center gap-1 mt-3">
                                    <input class="form-check-input text-danger" type="checkbox" name="remove_qr_code" id="remove_qr_code" value="1">
                                    <label class="form-check-label text-danger fs-13 fw-semibold" for="remove_qr_code">
                                        Remove Uploaded QR Code
                                    </label>
                                </div>
                            @endif
                        </div>

                        <!-- Upload Input & Requirements -->
                        <div class="col-md-8">
                            <label for="admin_qr_code" class="form-label fw-bold text-slate-700">Upload New QR Code Image</label>
                            <input class="form-control @error('admin_qr_code') is-invalid @enderror mb-2" 
                                   type="file" 
                                   name="admin_qr_code" 
                                   id="admin_qr_code" 
                                   accept="image/png,image/jpeg,image/jpg,image/webp,image/svg+xml">
                            @error('admin_qr_code')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <small class="text-muted d-block mt-2">
                                <i class="bx bx-info-circle me-1"></i> Format: PNG, JPG, JPEG, WebP, SVG. Max file size: 5MB. Clear & high-resolution square QR code image is recommended for seamless scanning.
                            </small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Save Actions -->
            <div class="d-flex justify-content-end gap-2 mb-4">
                <button type="submit" class="btn btn-primary px-4 py-2 d-inline-flex align-items-center gap-2">
                    <iconify-icon icon="solar:diskette-bold-duotone" class="fs-18"></iconify-icon>
                    Update Bank Details & QR Code
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ifscInput = document.getElementById('admin_ifsc_code');
    const bankNameInput = document.getElementById('admin_bank_name');
    const branchInput = document.getElementById('admin_bank_branch');
    const ifscFeedback = document.getElementById('ifsc_feedback');

    if (ifscInput) {
        let lastFetchedIfsc = '';

        function fetchIfscDetails() {
            const ifsc = ifscInput.value.trim().toUpperCase();
            if (ifsc.length === 11 && ifsc !== lastFetchedIfsc) {
                lastFetchedIfsc = ifsc;
                if (ifscFeedback) {
                    ifscFeedback.className = 'form-text text-info fw-semibold mt-1';
                    ifscFeedback.textContent = 'Fetching bank details...';
                }

                fetch('https://ifsc.razorpay.com/' + ifsc)
                    .then(res => {
                        if (!res.ok) throw new Error('Invalid IFSC Code');
                        return res.json();
                    })
                    .then(data => {
                        if (bankNameInput) bankNameInput.value = data.BANK || '';
                        if (branchInput) branchInput.value = data.BRANCH || '';
                        if (ifscFeedback) {
                            ifscFeedback.className = 'form-text text-success fw-semibold mt-1';
                            ifscFeedback.textContent = '✓ Bank & Branch fetched: ' + (data.BANK || '') + ' (' + (data.BRANCH || '') + ')';
                        }
                    })
                    .catch(err => {
                        if (ifscFeedback) {
                            ifscFeedback.className = 'form-text text-danger fw-semibold mt-1';
                            ifscFeedback.textContent = '⚠ Invalid IFSC code. Please check and try again.';
                        }
                    });
            }
        }

        ifscInput.addEventListener('input', function() {
            this.value = this.value.toUpperCase();
            if (this.value.trim().length === 11) {
                fetchIfscDetails();
            } else if (ifscFeedback) {
                ifscFeedback.textContent = '';
            }
        });

        ifscInput.addEventListener('blur', function() {
            if (this.value.trim().length === 11) {
                fetchIfscDetails();
            }
        });
    }
});
</script>
@endpush
