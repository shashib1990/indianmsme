<!DOCTYPE html>
<html lang="en" class="h-100">

<head>
    <meta charset="utf-8" />
    <title>Vendor Portal Sign In | Indian MSME</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="shortcut icon" href="{{ \App\Models\SystemSetting::getVal('favicon', asset('admin-assets/images/favicon.ico')) }}">

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

        .form-control:focus {
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
                <!-- Left Panel: Login Form -->
                <div
                    class="col-xxl-7 col-lg-7 d-flex align-items-center justify-content-center bg-white rounded-start-4 shadow-sm">
                    <div class="w-100 py-5" style="max-width: 440px; padding: 0 20px;">
                        <div class="auth-logo mb-4">
                            <a href="/" class="logo-dark">
                                <img src="{{ \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png')) }}" height="64" alt="logo">
                            </a>
                        </div>

                        <div class="mb-2">
                            <span class="badge role-badge px-3 py-2 fs-12 mb-3">
                                <iconify-icon icon="solar:shop-bold-duotone" class="me-1 align-middle"></iconify-icon>
                                Seller & Vendor Business Portal
                            </span>
                        </div>

                        <h2 class="fw-bold fs-28 text-slate-800" style="font-family: 'Outfit', sans-serif;">Vendor Sign
                            In</h2>
                        <p class="text-muted mt-1 mb-4">Enter business credentials to access the Seller Commerce
                            dashboard.</p>

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

                        @if(session('error'))
                            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                                <div class="d-flex align-items-center gap-2">
                                    <iconify-icon icon="solar:danger-bold-duotone"
                                        class="fs-18 align-middle text-danger"></iconify-icon>
                                    <span class="fw-semibold text-danger small">
                                        {{ session('error') }}
                                    </span>
                                </div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        @endif

                        <form action="{{ route('vendor.login.post') }}" method="POST" class="authentication-form">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label fw-medium text-slate-700" for="login_id">Vendor Code</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><iconify-icon
                                            icon="solar:user-id-bold-duotone" class="text-muted"></iconify-icon></span>
                                    <input type="text" id="login_id" name="login_id" class="form-control border-start-0"
                                        placeholder="Enter your Vendor Code (e.g. IMV1001)" value="{{ old('login_id') }}" required autofocus>
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label fw-medium text-slate-700" for="password">Password</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0"><iconify-icon
                                            icon="solar:lock-password-bold-duotone"
                                            class="text-muted"></iconify-icon></span>
                                    <input type="password" id="password" name="password"
                                        class="form-control border-start-0 border-end-0"
                                        placeholder="Enter secure password" required>
                                    <span class="input-group-text bg-light border-start-0" id="togglePasswordBtn"
                                        style="cursor: pointer;">
                                        <iconify-icon icon="solar:eye-bold-duotone" class="text-muted"
                                            id="togglePasswordIcon"></iconify-icon>
                                    </span>
                                </div>
                            </div>

                            <div class="mb-4 d-flex justify-content-between align-items-center">
                                <div class="form-check">
                                    <input type="checkbox" class="form-check-input" id="remember" name="remember">
                                    <label class="form-check-label text-muted small" for="remember">Keep me
                                        authenticated</label>
                                </div>
                            </div>

                            <div class="d-grid">
                                <button class="btn btn-theme" type="submit">
                                    <iconify-icon icon="solar:login-2-bold-duotone"
                                        class="me-1 align-middle fs-18"></iconify-icon>
                                    Sign In to Seller Dashboard
                                </button>
                            </div>
                        </form>

                        <div class="text-center mt-4">
                            <span class="text-muted small">Don't have an account? <a
                                    href="{{ route('vendor.register') }}" class="text-success fw-semibold">Sign
                                    Up</a></span>
                        </div>
                        <div class="text-center mt-2">
                            <span class="text-muted small">Need assistance? Contact Support.</span>
                        </div>
                    </div>
                </div>

                <!-- Right Panel: Premium Brand Showcase -->
                <div
                    class="col-xxl-5 col-lg-5 d-none d-lg-flex align-items-center justify-content-center login-card-right rounded-end-4">
                    <div class="p-5 w-100 text-center" style="max-width: 480px;">
                        <!-- Glowing Brand Icon -->
                        <div class="mb-4 d-inline-flex align-items-center justify-content-center rounded-circle"
                            style="width: 80px; height: 80px; background: rgba(255, 255, 255, 0.06); border: 1px solid rgba(255, 255, 255, 0.15); box-shadow: 0 8px 32px 0 rgba(6, 78, 59, 0.2); backdrop-filter: blur(4px);">
                            <iconify-icon icon="solar:shop-2-bold-duotone" class="glow-icon"
                                style="font-size: 40px; color: #ffffff !important;"></iconify-icon>
                        </div>

                        <!-- Premium Typography Header -->
                        <h3 class="fw-bold text-white fs-32 mb-2"
                            style="font-family: 'Outfit', sans-serif; letter-spacing: -0.5px;">Indian MSME</h3>
                        <p class="mb-4" style="color: #a7f3d0; font-size: 1.05rem; font-weight: 500;">Seller & Partner
                            Trading Portal</p>

                        <!-- Floating Glassmorphic Features -->
                        <div class="d-flex flex-column gap-3 mt-4 text-start">
                            <!-- Feature 1 -->
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

                            <!-- Feature 2 -->
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

                            <!-- Feature 3 -->
                            <div class="d-flex align-items-center gap-3 p-3 rounded-4"
                                style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08); backdrop-filter: blur(12px);">
                                <div class="d-flex align-items-center justify-content-center rounded-3"
                                    style="width: 44px; height: 44px; background: rgba(16, 185, 129, 0.15); border: 1px solid rgba(16, 185, 129, 0.25);">
                                    <iconify-icon icon="solar:card-transfer-bold-duotone" class="text-success fs-22"
                                        style="color: #10b981 !important;"></iconify-icon>
                                </div>
                                <div>
                                    <h5 class="mb-0 text-white fw-semibold fs-14">Payout Settlements</h5>
                                    <p class="mb-0 text-muted small"
                                        style="font-size: 12px; color: #a7f3d0 !important;">Track wallet payouts and
                                        clear commerce invoices</p>
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
    <script src="{{ asset('admin-assets/js/config.js') }}"></script>
    <script src="{{ asset('admin-assets/js/app.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const togglePasswordBtn = document.getElementById('togglePasswordBtn');
            const passwordInput = document.getElementById('password');
            const togglePasswordIcon = document.getElementById('togglePasswordIcon');

            if (togglePasswordBtn && passwordInput && togglePasswordIcon) {
                togglePasswordBtn.addEventListener('click', function () {
                    const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                    passwordInput.setAttribute('type', type);

                    if (type === 'password') {
                        togglePasswordIcon.setAttribute('icon', 'solar:eye-bold-duotone');
                    } else {
                        togglePasswordIcon.setAttribute('icon', 'solar:eye-closed-bold-duotone');
                    }
                });
            }
        });
    </script>
</body>

</html>