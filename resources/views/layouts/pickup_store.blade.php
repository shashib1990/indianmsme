<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'Dashboard') | Pickup Store Panel — Indian MSME</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Indian MSME Pickup Store Panel" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ \App\Models\SystemSetting::getVal('favicon', asset('admin-assets/images/favicon.ico')) }}">

    <!-- Vendor css -->
    <link href="{{ asset('admin-assets/css/vendor.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Icons css -->
    <link href="{{ asset('admin-assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App css -->
    <link href="{{ asset('admin-assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Theme Config js -->
    <script src="{{ asset('admin-assets/js/config.js') }}"></script>

    <!-- Tom Select: zero-dependency searchable dropdown, Bootstrap 5 native -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet" />
    <style>
        .ts-wrapper {
            width: 100%;
        }

        .ts-wrapper.single .ts-control {
            font-size: 0.875rem;
            border-radius: 0.375rem;
            border-color: #dee2e6;
            min-height: calc(1.5em + 0.75rem + 2px);
            padding: 0.375rem 2.25rem 0.375rem 0.75rem;
        }

        .ts-wrapper.single.input-active .ts-control,
        .ts-wrapper.single.focus .ts-control {
            border-color: var(--adm-primary-lt) !important;
            box-shadow: 0 0 0 0.25rem rgba(16, 185, 129, 0.25) !important;
        }

        .ts-dropdown {
            z-index: 9999 !important;
            border-radius: 0.375rem;
            font-size: 0.875rem;
        }

        .ts-dropdown .option:hover,
        .ts-dropdown .option.active {
            background: var(--adm-primary-bg) !important;
            color: var(--adm-primary-lt) !important;
        }

        .ts-dropdown .option.selected {
            background: var(--adm-primary-bg) !important;
            color: var(--adm-primary) !important;
            font-weight: 500;
        }

        /* Dark mode */
        [data-bs-theme="dark"] .ts-wrapper.single .ts-control {
            background: var(--bs-body-bg);
            border-color: var(--bs-border-color);
            color: var(--bs-body-color);
        }

        [data-bs-theme="dark"] .ts-dropdown {
            background: var(--bs-body-bg);
            border-color: var(--bs-border-color);
        }

        [data-bs-theme="dark"] .ts-dropdown .option {
            color: var(--bs-body-color);
        }
    </style>

    @stack('styles')
    <style>
        label.required::after,
        .form-label.required::after {
            content: " *";
            color: #ef4444 !important;
            font-weight: bold;
        }

        div.required::after,
        .ts-wrapper::after,
        .ts-control::after {
            content: none !important;
        }

        .text-slate-500 { color: #64748b !important; }
        .text-slate-600 { color: #475569 !important; }
        .text-slate-700 { color: #334155 !important; }
        .text-slate-800 { color: #1e293b !important; }
        .text-slate-900 { color: #0f172a !important; }

        .btn-theme {
            background: var(--adm-primary) !important;
            border-color: var(--adm-primary) !important;
            color: #ffffff !important;
            font-weight: 500 !important;
            letter-spacing: .2px !important;
            transition: all 0.15s ease-in-out;
        }

        .btn-theme:hover,
        .btn-theme:focus,
        .btn-theme:active {
            background: var(--adm-primary-lt) !important;
            border-color: var(--adm-primary-lt) !important;
            color: #ffffff !important;
            box-shadow: 0 4px 10px rgba(var(--bs-primary-rgb), 0.25) !important;
        }

        :root {
            --bs-primary: #10b981 !important;
            --bs-primary-rgb: 16, 185, 129 !important;
            --bs-link-color: #10b981 !important;
            --bs-link-hover-color: #059669 !important;

            --adm-primary: #10b981;
            --adm-primary-lt: #34d399;
            --adm-primary-bg: rgba(16, 185, 129, 0.12);
        }

        /* Modern SaaS Sidebar Styling */
        .main-nav {
            background: #ffffff !important;
            border-right: 1px solid #e2e8f0 !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03), 0 2px 4px -1px rgba(0, 0, 0, 0.02) !important;
        }

        .logo-box {
            background: #ffffff !important;
            border-bottom: 1px solid #f1f5f9 !important;
            padding: 12px 16px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            height: 70px !important;
            margin-bottom: 0 !important;
        }

        .logo-lg,
        .logo-sm {
            height: 120px !important;
            width: auto !important;
            object-fit: contain !important;
            padding-top: 20px;
            padding-bottom: 10px;
        }

        .navbar-nav .menu-title {
            font-size: 0.68rem !important;
            font-weight: 700 !important;
            letter-spacing: 1px !important;
            text-transform: uppercase !important;
            color: #94a3b8 !important;
            padding: 20px 20px 8px !important;
            margin: 0 !important;
            line-height: 1 !important;
        }

        .main-nav .navbar-nav .nav-item {
            margin: 2px 12px !important;
            position: relative;
        }

        .main-nav .navbar-nav .nav-link {
            color: #334155 !important;
            border-radius: 8px !important;
            padding: 10px 14px !important;
            font-size: 0.875rem !important;
            font-weight: 500 !important;
            transition: background 0.15s ease, color 0.15s ease !important;
            display: flex !important;
            align-items: center !important;
            gap: 10px !important;
        }

        .main-nav .navbar-nav .nav-icon {
            width: 22px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            color: #64748b !important;
            flex-shrink: 0 !important;
            transition: color 0.15s !important;
            font-size: 1.25rem !important;
        }

        .main-nav .navbar-nav .nav-link:hover {
            color: var(--adm-primary) !important;
            background: rgba(16, 185, 129, 0.05) !important;
        }

        .main-nav .navbar-nav .nav-link:hover .nav-icon {
            color: var(--adm-primary) !important;
        }

        .main-nav .navbar-nav .nav-link.active {
            color: var(--adm-primary) !important;
            background: rgba(16, 185, 129, 0.08) !important;
            font-weight: 600 !important;
        }

        .main-nav .navbar-nav .nav-link.active .nav-icon {
            color: var(--adm-primary) !important;
        }

        .main-nav .navbar-nav .nav-link.active::before {
            content: '';
            position: absolute;
            left: -12px;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 60%;
            background: var(--adm-primary);
            border-radius: 0 4px 4px 0;
        }

        [data-bs-theme="dark"] .main-nav {
            background: #1e293b !important;
            border-right: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        [data-bs-theme="dark"] .logo-box {
            background: #1e293b !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08) !important;
        }

        [data-bs-theme="dark"] .main-nav .navbar-nav .nav-link {
            color: #cbd5e1 !important;
        }

        [data-bs-theme="dark"] .main-nav .navbar-nav .nav-link:hover {
            color: var(--adm-primary-lt) !important;
            background: rgba(255, 255, 255, 0.05) !important;
        }

        [data-bs-theme="dark"] .main-nav .navbar-nav .nav-link.active {
            color: #ffffff !important;
            background: rgba(52, 211, 153, 0.2) !important;
        }

        .btn-primary {
            background: var(--adm-primary) !important;
            border-color: var(--adm-primary) !important;
            font-weight: 500 !important;
        }

        .btn-primary:hover {
            background: #059669 !important;
            border-color: #059669 !important;
        }

        .badge.bg-primary-subtle {
            background-color: rgba(16, 185, 129, 0.15) !important;
            color: var(--adm-primary-lt) !important;
        }

        .text-primary {
            color: var(--adm-primary-lt) !important;
        }

        .card {
            border: 1px solid #e8ecf4 !important;
            box-shadow: 0 1px 6px rgba(0, 0, 0, .05) !important;
            border-radius: 10px !important;
        }

        [data-bs-theme="dark"] .card {
            border-color: #334155 !important;
            background: #1e293b !important;
        }

        .card-header {
            background: #fafbfd !important;
            border-bottom: 1px solid #e8ecf4 !important;
            padding: 14px 20px !important;
            border-radius: 10px 10px 0 0 !important;
        }

        [data-bs-theme="dark"] .card-header {
            background: #1a2842 !important;
            border-bottom-color: #334155 !important;
        }

        .footer {
            border-top: 1px solid #e5e9f0 !important;
            background: #fff !important;
            font-size: 0.8rem !important;
            color: #94a3b8 !important;
        }

        [data-bs-theme="dark"] .footer {
            background: #1e293b !important;
            border-top-color: #334155 !important;
        }

        /* ── Sidebar Menu Arrow ── */
        .nav-link.menu-arrow::after {
            content: "\f0142";
            font-family: "solar-icons" !important;
            margin-left: auto;
            transition: transform .2s !important;
        }

        .nav-link.menu-arrow[aria-expanded="true"]::after {
            color: var(--adm-primary) !important;
            transform: rotate(90deg);
        }

        /* ── Sub-navigation items ── */
        .sub-navbar-nav {
            list-style: none;
            padding: 4px 0 8px 0;
            margin: 0;
            border-left: 1px solid #e2e8f0 !important;
            margin-left: 28px;
        }

        .sub-navbar-nav .sub-nav-item {
            margin: 1px 0;
        }

        /* ── Sidebar Text Wrapping & Overflow Fix ── */
        .main-nav,
        .app-menu,
        .navbar-nav,
        .sub-navbar-nav {
            overflow-x: hidden !important;
        }

        .main-nav .navbar-nav .nav-link,
        .sub-navbar-nav .sub-nav-link,
        .sub-nav-link {
            white-space: normal !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            word-break: break-word !important;
            height: auto !important;
            min-height: auto !important;
            line-height: 1.35 !important;
        }

        .main-nav .navbar-nav .nav-link .nav-text,
        .sub-navbar-nav .sub-nav-link .nav-text,
        .sub-nav-link .nav-text,
        .sub-navbar-nav .sub-nav-link span,
        .sub-nav-link span {
            white-space: normal !important;
            word-wrap: break-word !important;
            overflow-wrap: break-word !important;
            word-break: break-word !important;
            display: inline-block;
            max-width: 100%;
        }

        .main-nav .navbar-nav .nav-link {
            display: flex !important;
            align-items: center !important;
        }

        .main-nav .navbar-nav .nav-link .nav-text {
            flex: 1 1 auto;
            min-width: 0;
        }

        .sub-navbar-nav .sub-nav-link {
            display: flex !important;
            align-items: flex-start !important;
            padding: 8px 12px 8px 18px;
            font-size: 0.825rem;
            font-weight: 500;
            color: #475569 !important;
            text-decoration: none;
            border-radius: 0 8px 8px 0;
            transition: color 0.15s, background 0.15s, padding-left 0.15s;
            position: relative;
        }

        .sub-navbar-nav .sub-nav-link::before {
            content: '';
            position: absolute;
            left: -1px;
            top: 14px !important;
            transform: translateY(0) !important;
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #cbd5e1;
            transition: background 0.15s, transform 0.15s;
        }

        .sub-navbar-nav .sub-nav-link:hover {
            color: var(--adm-primary) !important;
            background: rgba(16, 185, 129, 0.04) !important;
            padding-left: 22px;
        }

        .sub-navbar-nav .sub-nav-link:hover::before {
            background: var(--adm-primary);
            transform: translateY(0) scale(1.2) !important;
        }

        .sub-navbar-nav .sub-nav-link.active {
            color: var(--adm-primary) !important;
            font-weight: 600;
            background: rgba(16, 185, 129, 0.06) !important;
            padding-left: 22px;
        }

        .sub-navbar-nav .sub-nav-link.active::before {
            background: var(--adm-primary);
            transform: translateY(0) scale(1.2) !important;
        }

        [data-bs-theme="dark"] .sub-navbar-nav {
            border-left: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        [data-bs-theme="dark"] .sub-navbar-nav .sub-nav-link {
            color: #94a3b8 !important;
        }

        [data-bs-theme="dark"] .sub-navbar-nav .sub-nav-link::before {
            background: #475569;
        }

        [data-bs-theme="dark"] .sub-navbar-nav .sub-nav-link:hover {
            color: var(--adm-primary-lt) !important;
            background: rgba(255, 255, 255, 0.04) !important;
        }

        [data-bs-theme="dark"] .sub-navbar-nav .sub-nav-link.active {
            color: var(--adm-primary-lt) !important;
            background: rgba(52, 211, 153, 0.12) !important;
        }
    </style>
</head>

<body>
    <div class="wrapper">
        <!-- Topbar -->
        <header class="topbar">
            <div class="container-fluid">
                <div class="navbar-header">
                    <div class="d-flex align-items-center">
                        <button type="button" class="button-toggle-menu me-2">
                            <iconify-icon icon="solar:hamburger-menu-broken" class="fs-24 align-middle"></iconify-icon>
                        </button>
                        <h4 class="fw-bold topbar-button pe-none text-uppercase mb-0">
                            <span class="badge bg-primary-subtle text-primary px-2 me-1">Pickup Store</span>
                            @yield('page-title', 'Dashboard')
                        </h4>
                    </div>

                    <div class="d-flex align-items-center gap-1">
                        <!-- Shopping / Visit Main Website Button -->
                        <div class="topbar-item me-1">
                            <a href="{{ main_website_url() }}" target="_blank"
                                class="btn btn-sm btn-outline-primary d-flex align-items-center gap-1.5 rounded-pill px-3 py-1.5 shadow-sm"
                                title="Shopping">
                                <iconify-icon icon="solar:global-bold-duotone"
                                    class="fs-18 align-middle"></iconify-icon>
                                <span class="fw-semibold d-none d-sm-inline" style="font-size: 13px;">Shopping</span>
                            </a>
                        </div>

                        <button type="button" class="topbar-button" id="light-dark-mode">
                            <iconify-icon icon="solar:moon-bold-duotone" class="fs-24 align-middle"></iconify-icon>
                        </button>

                        <!-- Notifications Dropdown -->
                        <div class="dropdown topbar-item">
                            @php
                                $storeId = auth('pickup_store')->id();
                                $storeNotifications = \App\Models\Notification::where('recipient_type', 'pickup_store')
                                    ->where('recipient_id', $storeId)
                                    ->where('is_read', false)
                                    ->orderBy('created_at', 'desc')
                                    ->take(5)->get();
                                $unreadCount = \App\Models\Notification::where('recipient_type', 'pickup_store')
                                    ->where('recipient_id', $storeId)
                                    ->where('is_read', false)->count();
                            @endphp
                            <a type="button" class="topbar-button position-relative" id="page-header-notifications-dropdown"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <iconify-icon icon="solar:bell-bing-bold-duotone" class="fs-24 align-middle"></iconify-icon>
                                @if($unreadCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger" style="margin-top: 10px; margin-left: -10px; font-size: 10px;">
                                        {{ $unreadCount }}
                                    </span>
                                @endif
                            </a>
                            <div class="dropdown-menu dropdown-menu-end p-0" style="width: 300px; max-height: 400px; overflow-y: auto;">
                                <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0 fw-bold">Notifications</h6>
                                </div>
                                <div class="list-group list-group-flush">
                                    @forelse($storeNotifications as $notif)
                                        <a href="{{ $notif->link ?? '#' }}" class="list-group-item list-group-item-action p-3">
                                            <div class="d-flex align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 text-slate-800 fw-semibold">{{ $notif->title }}</h6>
                                                    <p class="mb-1 text-muted small">{{ $notif->message }}</p>
                                                    <span class="text-muted small fs-11">{{ $notif->created_at->diffForHumans() }}</span>
                                                </div>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="p-4 text-center text-muted">
                                            <iconify-icon icon="solar:bell-broken" class="fs-32 text-muted mb-2"></iconify-icon><br>
                                            <span class="small">No new notifications</span>
                                        </div>
                                    @endforelse
                                </div>
                            </div>
                        </div>

                        <!-- User Dropdown -->
                        <div class="dropdown topbar-item">
                            <a type="button" class="topbar-button" id="page-header-user-dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="d-flex align-items-center gap-2">
                                    <iconify-icon icon="solar:user-circle-bold-duotone" class="fs-32 text-slate-500"></iconify-icon>
                                    <span class="d-none d-lg-flex flex-column text-start lh-sm">
                                        <span class="text-slate-700 fs-13 fw-semibold">{{ auth('pickup_store')->user()->pickup_store_name }}</span>
                                        <span class="text-muted fs-11">{{ auth('pickup_store')->user()->pickup_store_code }}</span>
                                    </span>
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <h6 class="dropdown-header text-slate-800 pb-1">Welcome!</h6>
                                <div class="px-3 pb-2 small text-muted">Code: {{ auth('pickup_store')->user()->pickup_store_code }}</div>
                                <a class="dropdown-item" href="{{ route('store.profile') }}">
                                    <i class="bx bx-user-circle text-muted fs-18 align-middle me-1"></i>
                                    <span class="align-middle">Profile</span>
                                </a>
                                <a class="dropdown-item" href="{{ route('store.profile.change-password') }}">
                                    <i class="bx bx-lock text-muted fs-18 align-middle me-1"></i>
                                    <span class="align-middle">Change Password</span>
                                </a>
                                <div class="dropdown-divider my-1"></div>
                                <form action="{{ route('store.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bx bx-log-out fs-18 align-middle me-1"></i>
                                        <span class="align-middle">Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Sidebar -->
        <div class="main-nav">
            <div class="logo-box">
                <a href="{{ route('store.dashboard') }}" class="logo-dark">
                    <img src="{{ \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png')) }}" class="logo-sm" alt="logo sm">
                    <img src="{{ \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png')) }}" class="logo-lg" alt="logo dark">
                </a>
                <a href="{{ route('store.dashboard') }}" class="logo-light">
                    <img src="{{ \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png')) }}" class="logo-sm" alt="logo sm">
                    <img src="{{ \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png')) }}" class="logo-lg" alt="logo light">
                </a>
            </div>

            <div class="scrollbar" data-simplebar>
                <ul class="navbar-nav" id="navbar-nav">
                    <li class="menu-title">Main</li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('store.dashboard') ? 'active' : '' }}" href="{{ route('store.dashboard') }}">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:widget-5-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>

                    @php
                        $storeUser = auth('pickup_store')->user();
                    @endphp
                    <li class="nav-item">
                        <a class="nav-link menu-arrow {{ request()->routeIs('store.purchase-orders.*') ? '' : '' }}"
                            href="#sidebarStorePurchaseOrders" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->routeIs('store.purchase-orders.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarStorePurchaseOrders">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:cart-large-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">Manage PO</span>
                        </a>
                        <div class="collapse {{ request()->routeIs('store.purchase-orders.*') ? 'show' : '' }}"
                            id="sidebarStorePurchaseOrders">
                            <ul class="nav sub-navbar-nav">
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('store.purchase-orders.all') || (request()->routeIs('store.purchase-orders.index') && !request()->routeIs('store.purchase-orders.verify')) ? 'active' : '' }}"
                                        href="{{ route('store.purchase-orders.all') }}">All Orders</a>
                                </li>
                                @if($storeUser->can_receive_payment_and_dispatch)
                                    <li class="sub-nav-item">
                                        <a class="sub-nav-link {{ request()->routeIs('store.purchase-orders.verify') ? 'active' : '' }}"
                                            href="{{ route('store.purchase-orders.verify') }}">Payment Verification</a>
                                    </li>
                                @endif
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('store.purchase-orders.ready') ? 'active' : '' }}"
                                        href="{{ route('store.purchase-orders.ready') }}">Ready for Dispatch</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('store.purchase-orders.dispatched') ? 'active' : '' }}"
                                        href="{{ route('store.purchase-orders.dispatched') }}">Dispatched Orders</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('store.purchase-orders.received') ? 'active' : '' }}"
                                        href="{{ route('store.purchase-orders.received') }}">Received History</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    @if($storeUser->can_receive_payment_and_dispatch)
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('store.wallets.*') ? 'active' : '' }}" href="{{ route('store.wallets.index') }}">
                                <span class="nav-icon">
                                    <iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon>
                                </span>
                                <span class="nav-text">Recharge Wallets</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('store.debit-notes.*') || request()->routeIs('store.adc-debit-notes.*') ? 'active' : '' }}" href="{{ route('store.debit-notes.index') }}">
                                <span class="nav-icon">
                                    <iconify-icon icon="solar:document-text-bold-duotone"></iconify-icon>
                                </span>
                                <span class="nav-text">Debit Notes</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('store.credit-notes.*') || request()->routeIs('store.adc-credit-notes.*') ? 'active' : '' }}" href="{{ route('store.credit-notes.index') }}">
                                <span class="nav-icon">
                                    <iconify-icon icon="solar:bill-check-bold-duotone"></iconify-icon>
                                </span>
                                <span class="nav-text">Credit Notes</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('store.excess-invoices.*') || request()->routeIs('store.adc-excess-invoices.*') ? 'active' : '' }}" href="{{ route('store.excess-invoices.index') }}">
                                <span class="nav-icon">
                                    <iconify-icon icon="solar:file-remove-bold-duotone"></iconify-icon>
                                </span>
                                <span class="nav-text">Excess Invoices</span>
                            </a>
                        </li>
                    @endif

                    <li class="menu-title mt-2">Account</li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('store.profile') ? 'active' : '' }}" href="{{ route('store.profile') }}">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:user-circle-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">Profile</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('store.profile.change-password') ? 'active' : '' }}" href="{{ route('store.profile.change-password') }}">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:lock-password-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">Change Password</span>
                        </a>
                    </li>
                </ul>
            </div>
        </div>

        <!-- Page Content -->
        <div class="page-content">
            <div class="container-fluid">
                @yield('content')
            </div>

            <!-- Footer -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12 text-center">
                            <script>document.write(new Date().getFullYear())</script> &copy; Indian MSME — Store Panel
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>

    <!-- Scripts -->
    <script src="{{ asset('admin-assets/js/vendor.js') }}"></script>
    <script src="{{ asset('admin-assets/js/app.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>
    <!-- SweetAlert & Toastify Local Vendor Assets -->
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
    <script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/toastify/toastify.min.css') }}">
    <script type="text/javascript" src="{{ asset('plugins/toastify/toastify.min.js') }}"></script>

    <script>
        function initSUIDropdown(ctx) {
            if (typeof TomSelect === 'undefined') return;
            var selects = !ctx
                ? document.querySelectorAll('select.s-select')
                : ctx.querySelectorAll('select.s-select');

            selects.forEach(function (el) {
                if (el.tomselect) return;

                var parsedOptions = [];
                for (var i = 0; i < el.options.length; i++) {
                    var opt = el.options[i];
                    parsedOptions.push({
                        value: opt.value,
                        text: opt.text,
                        sort_index: i
                    });
                }

                new TomSelect(el, {
                    options: parsedOptions,
                    create: false,
                    allowEmptyOption: true,
                    placeholder: el.dataset.placeholder || '— Select —',
                    maxOptions: 200,
                    sortField: [
                        { field: 'sort_index', direction: 'asc' },
                        { field: '$score' }
                    ]
                });
            });
        }

        document.addEventListener('DOMContentLoaded', function () {
            initSUIDropdown();

            // confirm-action handler using SweetAlert2
            document.querySelectorAll('.confirm-action').forEach(function (element) {
                element.addEventListener('click', function (e) {
                    e.preventDefault();
                    let form = this.closest('form');
                    let message = this.getAttribute('data-confirm-message') || "Are you sure you want to proceed?";
                    let clickedButton = this;

                    Swal.fire({
                        title: 'Are you sure?',
                        text: message,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, proceed!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            Swal.showLoading();
                            if (form) {
                                let text = 'Processing...';
                                const btnText = clickedButton.innerText.trim().toLowerCase();
                                if (btnText.includes('delete') || btnText.includes('remove')) {
                                    text = 'Deleting...';
                                } else if (btnText.includes('save') || btnText.includes('update') || btnText.includes('add') || btnText.includes('edit')) {
                                    text = 'Saving...';
                                } else if (btnText.includes('generate') || btnText.includes('submit') || btnText.includes('create')) {
                                    text = 'Submitting...';
                                }
                                if (typeof window.submitButtonLoading === 'function') {
                                    window.submitButtonLoading(clickedButton, text);
                                }
                                form.submit();
                            } else if (clickedButton.hasAttribute('href')) {
                                clickedButton.disabled = true;
                                clickedButton.classList.add('disabled');
                                clickedButton.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>`;
                                window.location.href = clickedButton.getAttribute('href');
                            }
                        }
                    });
                });
            });

            window.showToast = function (message, type = 'success') {
                let bgColors = {
                    'success': 'linear-gradient(to right, #10b981, #34d399)',
                    'error': 'linear-gradient(to right, #ef4444, #f87171)',
                    'warning': 'linear-gradient(to right, #f59e0b, #fbbf24)',
                    'info': 'linear-gradient(to right, #3b82f6, #60a5fa)'
                };

                Toastify({
                    text: message,
                    duration: 3000,
                    close: true,
                    gravity: "top",
                    position: "right",
                    style: {
                        background: bgColors[type] || bgColors['success'],
                    }
                }).showToast();
            };

            @if(session('success')) showToast("{{ session('success') }}", "success"); @endif
            @if(session('error')) showToast("{{ session('error') }}", "error"); @endif
            @if($errors->any())
                @foreach($errors->all() as $error)
                    showToast("{{ $error }}", "error");
                @endforeach
            @endif
        });
    </script>
    @stack('scripts')
    <!-- Client-side Image Compression Script -->
    <script src="{{ asset('js/image-compressor.js') }}"></script>
    <!-- Universal Table Action & State Preservation Script -->
    <script src="{{ asset('js/table-action-handler.js') }}"></script>
</body>

</html>
