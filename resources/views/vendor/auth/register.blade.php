<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <meta charset="utf-8" />
    <title>Vendor Portal Sign Up | Indian MSME</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon"
        href="{{ \App\Models\SystemSetting::getVal('favicon', asset('admin-assets/images/favicon.ico')) }}">

    <!-- CSS Assets -->
    <link href="{{ asset('admin-assets/css/vendor.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('admin-assets/css/icons.min.css') }}" rel="stylesheet" />
    <link href="{{ asset('admin-assets/css/app.min.css') }}" rel="stylesheet" />
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>

    <style>
        body {
            background-color: #f4faf7;
            font-family: 'Inter', sans-serif;
        }

        .login-card-right {
            background: linear-gradient(135deg, #059669 0%, #064e3b 100%);
            position: relative;
            overflow: hidden;
        }

        .login-card-right::before {
            content: '';
            position: absolute;
            width: 300px;
            height: 300px;
            background: rgba(255, 255, 255, 0.05);
            border-radius: 50%;
            top: -10%;
            right: -10%;
        }

        .btn-theme {
            background-color: #10b981 !important;
            border-color: #10b981 !important;
            color: #fff !important;
            font-weight: 600;
            padding: 10px 20px;
            transition: all 0.3s ease;
        }

        .btn-theme:hover {
            background-color: #059669 !important;
            border-color: #059669 !important;
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25);
        }

        .role-badge {
            background-color: rgba(16, 185, 129, 0.1) !important;
            color: #10b981 !important;
            font-weight: 600;
            border: 1px solid rgba(16, 185, 129, 0.2);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #10b981 !important;
            box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.15) !important;
        }

        .glow-icon {
            filter: drop-shadow(0 0 15px rgba(255, 255, 255, 0.1));
            color: #d1fae5;
        }
    </style>
</head>

<body class="h-100">
    <div class="d-flex flex-column h-100 p-3">
        <div class="d-flex flex-column flex-grow-1">
            <div class="row h-100 align-items-stretch">
                <!-- Left Panel: Registration Form -->
                <div
                    class="col-xxl-7 col-lg-7 d-flex align-items-center justify-content-center bg-white rounded-start-4 shadow-sm">
                    <div class="w-100 py-4" style="max-width: 600px; padding: 0 30px;">
                        <div class="auth-logo mb-3">
                            <a href="/" class="logo-dark">
                                <img src="{{ \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png')) }}"
                                    height="54" alt="logo">
                            </a>
                        </div>

                        <div class="mb-2">
                            <span class="badge role-badge px-3 py-2 fs-12 mb-2">
                                <iconify-icon icon="solar:shop-bold-duotone" class="me-1 align-middle"></iconify-icon>
                                Seller Registration
                            </span>
                        </div>

                        <h2 class="fw-bold fs-24 text-slate-800" style="font-family: 'Outfit', sans-serif;">Register as
                            a Seller</h2>
                        <p class="text-muted mt-1 mb-4">Complete the fields below to create your manufacturer or seller
                            profile.</p>

                        @if($errors->any())
                            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                                <div class="d-flex align-items-center gap-2">
                                    <iconify-icon icon="solar:danger-bold-duotone"
                                        class="fs-18 align-middle text-danger"></iconify-icon>
                                    <span class="fw-semibold text-danger small">
                                        {{ $errors->first() }}
                                    </span>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('vendor.register.post') }}" method="POST" class="authentication-form">
                            @csrf
                            <div class="row">
                                <!-- Company Name -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium text-slate-700" for="company_name">Company Name
                                        <span class="text-danger">*</span></label>
                                    <input type="text" id="company_name" name="company_name" class="form-control"
                                        placeholder="e.g. Acme Corporation" value="{{ old('company_name') }}" required>
                                </div>

                                <!-- Company Type -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium text-slate-700" for="company_type">Company Type
                                        <span class="text-danger">*</span></label>
                                    <select id="company_type" name="company_type" class="form-select" required>
                                        <option value="">— Select Type —</option>
                                        <option value="Company" {{ old('company_type') == 'Company' ? 'selected' : '' }}>
                                            Company</option>
                                        <option value="LLP" {{ old('company_type') == 'LLP' ? 'selected' : '' }}>LLP
                                        </option>
                                        <option value="Proprietorship Firm" {{ old('company_type') == 'Proprietorship Firm' ? 'selected' : '' }}>Proprietorship Firm</option>
                                        <option value="Other" {{ old('company_type') == 'Other' ? 'selected' : '' }}>Other
                                        </option>
                                    </select>
                                </div>

                                <!-- Primary Category -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium text-slate-700" for="category_id">Primary
                                        Category <span class="text-danger">*</span></label>
                                    <select id="category_id" name="category_id" class="form-select" required>
                                        <option value="">— Select Category —</option>
                                        @foreach($categories as $cat)
                                            <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- Seller Type -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium text-slate-700" for="seller_type">Seller Type
                                        <span class="text-danger">*</span></label>
                                    <select id="seller_type" name="seller_type" class="form-select" required>
                                        <option value="">— Select Seller Type —</option>
                                        <option value="Trader" {{ old('seller_type') == 'Trader' ? 'selected' : '' }}>
                                            Trader</option>
                                        <option value="Manufacturer" {{ old('seller_type') == 'Manufacturer' ? 'selected' : '' }}>Manufacturer</option>
                                        <option value="Distributor" {{ old('seller_type') == 'Distributor' ? 'selected' : '' }}>Distributor</option>
                                    </select>
                                </div>

                                <!-- Brand Name (Only shown if Manufacturer) -->
                                <div class="col-md-12 mb-3" id="brand-container" style="display: none;">
                                    <label class="form-label fw-medium text-slate-700" for="brand_name">
                                        Brand Name <span class="text-danger">*</span>
                                    </label>
                                    <input type="text" id="brand_name" name="brand_name" class="form-control"
                                        placeholder="e.g. Acme Brand" value="{{ old('brand_name') }}">
                                    <small class="text-muted fs-11">Enter brand name (required for manufacturers).</small>
                                </div>

                                <!-- Authorized Representative -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium text-slate-700"
                                        for="authorized_rep_name">Authorized Representative <span
                                            class="text-danger">*</span></label>
                                    <input type="text" id="authorized_rep_name" name="authorized_rep_name"
                                        class="form-control" placeholder="Full Name"
                                        value="{{ old('authorized_rep_name') }}" required>
                                </div>

                                <!-- Email -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium text-slate-700" for="email">Email Address <span
                                            class="text-danger">*</span></label>
                                    <input type="email" id="email" name="email" class="form-control"
                                        placeholder="vendor@domain.com" value="{{ old('email') }}" required>
                                </div>

                                <!-- Mobile Number -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium text-slate-700" for="mobile">Mobile Number <span
                                            class="text-danger">*</span></label>
                                    <input type="text" id="mobile" name="mobile" class="form-control"
                                        placeholder="10-digit number" value="{{ old('mobile') }}" maxlength="10"
                                        pattern="[0-9]{10}" oninput="this.value = this.value.replace(/[^0-9]/g, '')"
                                        required>
                                </div>

                                <!-- State -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium text-slate-700" for="state">State <span
                                            class="text-danger">*</span></label>
                                    <select id="state" name="state" class="form-select" required>
                                        <option value="">— Select State —</option>
                                        @foreach($states as $st)
                                            <option value="{{ $st->id }}" {{ old('state') == $st->id ? 'selected' : '' }}>
                                                {{ $st->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                <!-- District -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium text-slate-700" for="district">District <span
                                            class="text-danger">*</span></label>
                                    <select id="district" name="district" class="form-select" required>
                                        <option value="">— Select State First —</option>
                                    </select>
                                </div>

                                <!-- Password -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium text-slate-700" for="password">Password <span
                                            class="text-danger">*</span></label>
                                    <input type="password" id="password" name="password" class="form-control"
                                        placeholder="At least 8 characters" required>
                                </div>

                                <!-- Confirm Password -->
                                <div class="col-md-6 mb-3">
                                    <label class="form-label fw-medium text-slate-700"
                                        for="password_confirmation">Confirm Password <span
                                            class="text-danger">*</span></label>
                                    <input type="password" id="password_confirmation" name="password_confirmation"
                                        class="form-control" placeholder="Repeat password" required>
                                </div>
                            </div>

                            <div class="d-grid mt-2">
                                <button class="btn btn-theme" type="submit">
                                    <iconify-icon icon="solar:user-plus-bold-duotone"
                                        class="me-1 align-middle fs-18"></iconify-icon>
                                    Register Account
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <span class="text-muted small">Already have an account? <a
                                    href="{{ route('vendor.login') }}" class="text-success fw-semibold">Sign
                                    In</a></span>
                        </div>
                    </div>
                </div>

                <!-- Right Panel: Premium Brand Showcase -->
                <div
                    class="col-xxl-5 col-lg-5 d-none d-lg-flex align-items-center justify-content-center login-card-right rounded-end-4">
                    <div class="p-5 w-100 text-center" style="max-width: 480px;">
                        <div class="mb-4 d-inline-flex align-items-center justify-content-center rounded-circle"
                            style="width: 80px; height: 80px; background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.15); box-shadow: 0 8px 32px 0 rgba(6, 78, 59, 0.2); backdrop-filter: blur(4px);">
                            <iconify-icon icon="solar:shop-2-bold-duotone" class="glow-icon"
                                style="font-size: 40px; color: #ffffff !important;"></iconify-icon>
                        </div>

                        <h3 class="fw-bold text-white fs-32 mb-2"
                            style="font-family: 'Outfit', sans-serif; letter-spacing: -0.5px;">Indian MSME</h3>
                        <p class="mb-4" style="color: #a7f3d0; font-size: 1.05rem; font-weight: 500;">Seller & Partner
                            Trading Portal</p>

                        <div class="d-flex flex-column gap-3 mt-4 text-start">
                            <div class="d-flex align-items-center gap-3 p-3 rounded-4"
                                style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); backdrop-filter: blur(12px);">
                                <div class="d-flex align-items-center justify-content-center rounded-3"
                                    style="width: 44px; height: 44px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.25);">
                                    <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="text-success fs-22"
                                        style="color: #10b981 !important;"></iconify-icon>
                                </div>
                                <div>
                                    <h5 class="mb-0 text-white fw-semibold fs-14">Catalog & Inventory</h5>
                                    <p class="mb-0 text-muted small"
                                        style="font-size: 12px; color: #a7f3d0 !important;">List items and track
                                        inventory levels effortlessly</p>
                                </div>
                            </div>

                            <div class="d-flex align-items-center gap-3 p-3 rounded-4"
                                style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); backdrop-filter: blur(12px);">
                                <div class="d-flex align-items-center justify-content-center rounded-3"
                                    style="width: 44px; height: 44px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.25);">
                                    <iconify-icon icon="solar:cart-large-4-bold-duotone" class="text-success fs-22"
                                        style="color: #10b981 !important;"></iconify-icon>
                                </div>
                                <div>
                                    <h5 class="mb-0 text-white fw-semibold fs-14">Order Fulfillment</h5>
                                    <p class="mb-0 text-muted small"
                                        style="font-size: 12px; color: #a7f3d0 !important;">Process received orders and
                                        secure courier dispatches</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('admin-assets/js/vendor.js') }}"></script>
    <script src="{{ asset('admin-assets/js/app.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const stateSelect = document.getElementById('state');
            const districtSelect = document.getElementById('district');

            function loadDistricts(stateId, selectedDistrictId) {
                if (!stateId) {
                    districtSelect.innerHTML = '<option value="">— Select State First —</option>';
                    return;
                }

                districtSelect.innerHTML = '<option value="">Loading districts...</option>';

                fetch(`/api/locations/districts?state_id=${stateId}`)
                    .then(response => response.json())
                    .then(data => {
                        districtSelect.innerHTML = '';
                        const defaultOpt = document.createElement('option');
                        defaultOpt.value = "";
                        defaultOpt.textContent = "— Select District —";
                        if (!selectedDistrictId) {
                            defaultOpt.selected = true;
                        }
                        districtSelect.appendChild(defaultOpt);

                        data.forEach(district => {
                            const option = document.createElement('option');
                            option.value = district.id;
                            option.textContent = district.name;
                            if (selectedDistrictId && district.id == selectedDistrictId) {
                                option.selected = true;
                            }
                            districtSelect.appendChild(option);
                        });
                    })
                    .catch(err => {
                        districtSelect.innerHTML = '<option value="">Error loading districts</option>';
                    });
            }

            stateSelect.addEventListener('change', function () {
                loadDistricts(this.value, null);
            });

            // Initial load for validation redirects
            const oldStateId = stateSelect.value;
            const oldDistrictId = "{{ old('district') }}";
            if (oldStateId) {
                loadDistricts(oldStateId, oldDistrictId);
            }

            // Toggle Brand Name input visibility based on Seller Type (only Manufacturer)
            const sellerTypeSelect = document.getElementById('seller_type');
            const brandContainer = document.getElementById('brand-container');
            const brandInput = document.getElementById('brand_name');

            function toggleBrandContainer() {
                if (!sellerTypeSelect || !brandContainer) return;
                const val = (sellerTypeSelect.value || '').trim().toLowerCase();
                if (val === 'manufacturer') {
                    brandContainer.style.display = 'block';
                } else {
                    brandContainer.style.display = 'none';
                    if (brandInput) brandInput.value = '';
                }
            }

            if (sellerTypeSelect) {
                sellerTypeSelect.addEventListener('change', toggleBrandContainer);
                if (typeof $ !== 'undefined') {
                    $(sellerTypeSelect).on('change', toggleBrandContainer);
                }
                toggleBrandContainer(); // Initial check on page load
            }

            // Client-side mobile validation on form submit
            const form = document.querySelector('.authentication-form');
            if (form) {
                form.addEventListener('submit', function (e) {
                    const mobileInput = document.getElementById('mobile');
                    const mobileVal = mobileInput ? mobileInput.value.trim() : '';
                    if (!/^\d{10}$/.test(mobileVal)) {
                        e.preventDefault();
                        Swal.fire({
                            icon: 'error',
                            title: 'Validation Error',
                            text: 'Mobile number must be exactly 10 numeric digits.',
                            confirmButtonColor: '#10b981'
                        });
                        mobileInput.focus();
                        return false;
                    }
                });
            }
        });
    </script>
</body>

</html>