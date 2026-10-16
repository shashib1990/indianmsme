<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Title Meta -->
    <meta charset="utf-8" />
    <title>@yield('title', 'Dashboard') | Seller Panel — Indian MSME</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Indian MSME Seller Panel" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- App favicon -->
    <link rel="shortcut icon" href="{{ \App\Models\SystemSetting::getVal('favicon', asset('admin-assets/images/favicon.ico')) }}">

    <!-- Vendor css -->
    <link href="{{ asset('admin-assets/css/vendor.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- Icons css -->
    <link href="{{ asset('admin-assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <!-- App css -->
    <link href="{{ asset('admin-assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>
    <!-- Theme Config js -->
    <script src="{{ asset('admin-assets/js/config.js') }}"></script>

    <!-- Tom Select: zero-dependency searchable dropdown, Bootstrap 5 native -->
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/css/tom-select.bootstrap5.min.css"
        rel="stylesheet" />
    <style>
        /* ── Tom Select admin theme tweaks ── */
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

        /* Enforce min-width for filters in table-filters form to prevent truncation */
        #filterForm .ts-wrapper {
            min-width: 170px !important;
            width: auto !important;
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

        /* ── Tailwind Slate Color Fallbacks ── */
        .text-slate-500 {
            color: #64748b !important;
        }

        .text-slate-600 {
            color: #475569 !important;
        }

        .text-slate-700 {
            color: #334155 !important;
        }

        .text-slate-800 {
            color: #1e293b !important;
        }

        .text-slate-900 {
            color: #0f172a !important;
        }

        /* ── Global Theme Button Alias ── */
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

            /* Custom Vendor Theme Colors - Premium Green */
            --adm-primary: #10b981;
            --adm-primary-lt: #34d399;
            --adm-primary-bg: rgba(16, 185, 129, 0.12);
        }

        /* ── Modern SaaS Sidebar Styling ── */
        .main-nav {
            background: #ffffff !important;
            border-right: 1px solid #e2e8f0 !important;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.03), 0 2px 4px -1px rgba(0, 0, 0, 0.02) !important;
        }

        /* ── Logo box ── */
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

        /* ── Section group headers ── */
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

        .navbar-nav .menu-title.mt-2 {
            margin-top: 0 !important;
        }

        /* ── Nav items ── */
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

        /* Active nav item */
        .main-nav .navbar-nav .nav-link.active,
        .main-nav .navbar-nav .nav-link[aria-expanded="true"] {
            color: var(--adm-primary) !important;
            background: rgba(16, 185, 129, 0.08) !important;
            font-weight: 600 !important;
        }

        .main-nav .navbar-nav .nav-link.active .nav-icon,
        .main-nav .navbar-nav .nav-link[aria-expanded="true"] .nav-icon {
            color: var(--adm-primary) !important;
        }

        /* Left accent bar on active */
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

        /* Collapse arrow */
        .nav-link.menu-arrow::after {
            margin-left: auto !important;
            color: #94a3b8 !important;
            transition: transform .2s !important;
        }

        .nav-link.menu-arrow[aria-expanded="true"]::after {
            color: var(--adm-primary) !important;
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

        /* ── Dark Mode Sidebar Styling ── */
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

        [data-bs-theme="dark"] .main-nav .navbar-nav .nav-icon {
            color: #94a3b8 !important;
        }

        [data-bs-theme="dark"] .main-nav .navbar-nav .nav-link:hover {
            color: var(--adm-primary-lt) !important;
            background: rgba(255, 255, 255, 0.05) !important;
        }

        [data-bs-theme="dark"] .main-nav .navbar-nav .nav-link:hover .nav-icon {
            color: var(--adm-primary-lt) !important;
        }

        [data-bs-theme="dark"] .main-nav .navbar-nav .nav-link.active,
        [data-bs-theme="dark"] .main-nav .navbar-nav .nav-link[aria-expanded="true"] {
            color: #ffffff !important;
            background: rgba(52, 211, 153, 0.2) !important;
        }

        [data-bs-theme="dark"] .main-nav .navbar-nav .nav-link.active .nav-icon,
        [data-bs-theme="dark"] .main-nav .navbar-nav .nav-link[aria-expanded="true"] .nav-icon {
            color: var(--adm-primary-lt) !important;
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

        /* ── Buttons ── */
        .btn-primary {
            background: var(--adm-primary) !important;
            border-color: var(--adm-primary) !important;
            font-weight: 500 !important;
            letter-spacing: .2px !important;
        }

        .btn-primary:hover {
            background: #059669 !important;
            border-color: #059669 !important;
        }

        .btn-outline-primary {
            border-color: var(--adm-primary-lt) !important;
            color: var(--adm-primary-lt) !important;
        }

        .btn-outline-primary:hover {
            background: var(--adm-primary-lt) !important;
            color: #fff !important;
        }

        /* ── Badges & text colors ── */
        .badge.bg-primary-subtle {
            background-color: rgba(16, 185, 129, 0.15) !important;
            color: var(--adm-primary-lt) !important;
        }

        .text-primary {
            color: var(--adm-primary-lt) !important;
        }

        /* ── Reusable DataTable & Sorting ── */
        .sortable-header {
            position: relative;
            transition: background-color 0.2s ease, color 0.2s ease;
        }

        .sortable-header:hover {
            background-color: rgba(16, 185, 129, 0.05) !important;
            color: var(--adm-primary) !important;
        }

        .sortable-header iconify-icon {
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        .sortable-header:hover iconify-icon {
            opacity: 1 !important;
        }

        /* ── Universal Table Alignment & Structure ── */
        .table > :not(caption) > * > * {
            vertical-align: middle !important;
        }

        .table thead th {
            vertical-align: middle !important;
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
        }

        .table tbody td {
            vertical-align: middle !important;
            padding-top: 0.75rem !important;
            padding-bottom: 0.75rem !important;
        }

        .table > thead > tr > th:first-child,
        .table > tbody > tr > td:first-child {
            padding-left: 1.25rem !important;
        }

        .table > thead > tr > th:last-child,
        .table > tbody > tr > td:last-child {
            padding-right: 1.25rem !important;
        }

        .bg-primary {
            background: var(--adm-primary) !important;
        }

        /* ── Cards ── */
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

        /* ── Tables ── */
        .table thead th {
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: .6px !important;
            color: #64748b !important;
            background: #f8fafc !important;
            border-bottom: 2px solid #e2e8f0 !important;
            padding: 10px 16px !important;
        }

        [data-bs-theme="dark"] .table thead th {
            background: #1a2535 !important;
            color: #94a3b8 !important;
            border-bottom-color: #334155 !important;
        }

        .table tbody tr {
            transition: background .12s;
        }

        .table-hover tbody tr:hover {
            background: rgba(16, 185, 129, .03) !important;
        }

        /* ── Footer ── */
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
    </style>
</head>

<body>
    <!-- Impersonation Banner -->
    @if(session('impersonating'))
        <div class="alert alert-warning alert-dismissible text-center mb-0 rounded-0 py-2" role="alert">
            <i class="bx bx-user-check me-1"></i>
            You are impersonating <strong>{{ session('impersonating.user_name') }}</strong>.
            <form action="{{ url('impersonate/stop') }}" method="POST" class="d-inline ms-3">
                @csrf
                <button type="submit" class="btn btn-sm btn-danger">
                    <i class="bx bx-log-out me-1"></i> Return to Admin
                </button>
            </form>
        </div>
    @endif

    <!-- START Wrapper -->
    <div class="wrapper">

        <!-- ========== Topbar Start ========== -->
        <header class="topbar">
            <div class="container-fluid">
                <div class="navbar-header">
                    <div class="d-flex align-items-center">
                        <!-- Menu Toggle -->
                        <div class="topbar-item">
                            <button type="button" class="button-toggle-menu me-2">
                                <iconify-icon icon="solar:hamburger-menu-broken"
                                    class="fs-24 align-middle"></iconify-icon>
                            </button>
                        </div>
                        <div class="topbar-item">
                            <h4 class="fw-bold topbar-button pe-none text-uppercase mb-0">
                                <span class="badge {{ (auth('vendor')->check() && auth('vendor')->user()->parent_id !== null) ? 'bg-info-subtle text-info' : 'bg-primary-subtle text-primary' }} px-2 me-1">
                                    {{ (auth('vendor')->check() && auth('vendor')->user()->parent_id !== null) ? 'Sub Seller' : 'Seller' }}
                                </span>
                                @yield('page-title', 'Dashboard')
                            </h4>
                        </div>
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

                        <!-- DDC Model Subscription Button -->
                        @if(auth('vendor')->check() && auth('vendor')->user()->isEligibleForDdcSubscription())
                            @php
                                $sellerProfile = auth('vendor')->user()->sellerProfile;
                                $ddcSubStatus = $sellerProfile?->ddc_subscription_status ?? 'none';
                            @endphp
                            <div class="topbar-item me-2">
                                @if($ddcSubStatus === 'approved')
                                    <button type="button" class="btn btn-success btn-sm fw-semibold d-flex align-items-center gap-1 shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#ddcSubscriptionModal" style="border-radius: 20px;">
                                        <iconify-icon icon="solar:star-bold-duotone" class="fs-18 align-middle"></iconify-icon>
                                        <span>DDC Subscribed</span>
                                    </button>
                                @elseif($ddcSubStatus === 'pending')
                                    <button type="button" class="btn btn-info text-white btn-sm fw-semibold d-flex align-items-center gap-1 shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#ddcSubscriptionModal" style="border-radius: 20px;">
                                        <iconify-icon icon="solar:clock-square-bold-duotone" class="fs-18 align-middle"></iconify-icon>
                                        <span>DDC Subscription Pending</span>
                                    </button>
                                @else
                                    <button type="button" class="btn btn-warning text-dark btn-sm fw-semibold d-flex align-items-center gap-1 shadow-sm px-3" data-bs-toggle="modal" data-bs-target="#ddcSubscriptionModal" style="border-radius: 20px;">
                                        <iconify-icon icon="solar:star-bold-duotone" class="fs-18 align-middle"></iconify-icon>
                                        <span>DDC Model Subscription</span>
                                    </button>
                                @endif
                            </div>
                        @endif

                        <!-- Dark Mode -->
                        <div class="topbar-item">
                            <button type="button" class="topbar-button" id="light-dark-mode">
                                <iconify-icon icon="solar:moon-bold-duotone" class="fs-24 align-middle"></iconify-icon>
                            </button>
                        </div>

                        <!-- Notifications Dropdown -->
                        <div class="dropdown topbar-item">
                            @php
                                $vendorId = auth('vendor')->id();
                                $vendorNotifications = \App\Models\Notification::where('recipient_type', 'seller')
                                    ->where('recipient_id', $vendorId)
                                    ->where('is_read', false)
                                    ->orderBy('created_at', 'desc')
                                    ->take(5)->get();
                                $unreadCount = \App\Models\Notification::where('recipient_type', 'seller')
                                    ->where('recipient_id', $vendorId)
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
                                    @forelse($vendorNotifications as $notif)
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
                            <a type="button" class="topbar-button" id="page-header-user-dropdown"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="d-flex align-items-center gap-2">
                                    <img class="rounded-circle" width="32" height="32" style="object-fit: cover; aspect-ratio: 1 / 1;"
                                        src="{{ auth('vendor')->user()->avatar_url }}" alt="avatar">
                                    <span class="d-none d-lg-flex flex-column text-start lh-sm">
                                        <span class="text-slate-700 fs-13 fw-semibold">{{ auth('vendor')->user()->name }}</span>
                                        <span class="text-muted fs-11">{{ auth('vendor')->user()->seller_id ?? '' }}</span>
                                    </span>
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <h6 class="dropdown-header text-slate-800 pb-1">Welcome {{ auth('vendor')->user()->name }}!</h6>
                                <div class="px-3 pb-2 small text-muted">ID: {{ auth('vendor')->user()->seller_id ?? '—' }}</div>
                                <a class="dropdown-item" href="{{ route('vendor.profile') }}">
                                    <i class="bx bx-user-circle text-muted fs-18 align-middle me-1"></i>
                                    <span class="align-middle">Profile</span>
                                </a>
                                <a class="dropdown-item" href="{{ route('vendor.profile.change-password') }}">
                                    <i class="bx bx-lock text-muted fs-18 align-middle me-1"></i>
                                    <span class="align-middle">Change Password</span>
                                </a>
                                <div class="dropdown-divider my-1"></div>
                                <form action="{{ route('vendor.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bx bx-log-out fs-18 align-middle me-1"></i>
                                        <span class="align-middle">Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- App Search -->
                        <form class="app-search d-none d-md-block ms-2">
                            <div class="position-relative">
                                <input type="search" class="form-control" placeholder="Search..." autocomplete="off">
                                <iconify-icon icon="solar:magnifer-linear" class="search-widget-icon"></iconify-icon>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </header>
        <!-- ========== Topbar End ========== -->

        <!-- ========== App Menu Start ========== -->
        <div class="main-nav">
            <!-- Sidebar Logo -->
            <div class="logo-box">
                <a href="{{ route('vendor.dashboard') }}" class="logo-dark">
                    <img src="{{ \App\Models\SystemSetting::getVal('website_header_logo', \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png'))) }}" class="logo-sm" alt="logo sm">
                    <img src="{{ \App\Models\SystemSetting::getVal('website_header_logo', \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png'))) }}" class="logo-lg" alt="logo dark">
                </a>
                <a href="{{ route('vendor.dashboard') }}" class="logo-light">
                    <img src="{{ \App\Models\SystemSetting::getVal('website_header_logo', \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png'))) }}" class="logo-sm" alt="logo sm">
                    <img src="{{ \App\Models\SystemSetting::getVal('website_header_logo', \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png'))) }}" class="logo-lg" alt="logo light">
                </a>
            </div>

            <div class="scrollbar" data-simplebar>
                <ul class="navbar-nav" id="navbar-nav">

                    {{-- ========= GENERAL ========= --}}
                    <li class="menu-title">General</li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('vendor.dashboard') ? 'active' : '' }}"
                            href="{{ route('vendor.dashboard') }}">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:widget-5-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>

                    {{-- ========= PRODUCT CATALOG & MEDIA ========= --}}
                    @if(auth('vendor')->user()->allow_own_products === 'Yes')
                        <li class="menu-title mt-2">Catalog & Products</li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('vendor.products.*') ? 'active' : '' }}"
                                href="{{ route('vendor.products.index') }}">
                                <span class="nav-icon"><iconify-icon icon="solar:box-bold-duotone"></iconify-icon></span>
                                <span class="nav-text">My Products</span>
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('vendor.media.*') ? 'active' : '' }}"
                                href="{{ route('vendor.media.index') }}">
                                <span class="nav-icon"><iconify-icon
                                        icon="solar:gallery-wide-bold-duotone"></iconify-icon></span>
                                <span class="nav-text">Media Library</span>
                            </a>
                        </li>
                    @endif

                    {{-- ========= ASSIGNED PRODUCTS (For Sub-Sellers) ========= --}}
                    @if(auth('vendor')->user()->parent_id !== null)
                        <li class="menu-title mt-2">Distribution</li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('vendor.products.assigned') ? 'active' : '' }}"
                                href="{{ route('vendor.products.assigned') }}">
                                <span class="nav-icon"><iconify-icon
                                        icon="solar:clipboard-list-bold-duotone"></iconify-icon></span>
                                <span class="nav-text">Assigned Products</span>
                            </a>
                        </li>
                    @endif

                    {{-- ========= SUB SELLERS ========= --}}
                    @if(auth('vendor')->user()->parent_id === null)
                        <li class="menu-title mt-2">Sub-Sellers</li>
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('vendor.sub-sellers.*') ? 'active' : '' }}"
                                href="{{ route('vendor.sub-sellers.index') }}">
                                <span class="nav-icon"><iconify-icon
                                        icon="solar:users-group-two-rounded-bold-duotone"></iconify-icon></span>
                                <span class="nav-text">Manage Sub-Sellers</span>
                            </a>
                        </li>
                    @endif

                    {{-- ========= PICKUP STORES ========= --}}
                    <li class="menu-title mt-2">Logistics</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('vendor.pickup-stores.*') ? 'active' : '' }}"
                            href="{{ route('vendor.pickup-stores.index') }}">
                            <span class="nav-icon"><iconify-icon
                                    icon="solar:shop-bold-duotone"></iconify-icon></span>
                            <span class="nav-text">Pickup Stores</span>
                        </a>
                    </li>

                    {{-- ========= RACK & SHELF STORAGE ========= --}}
                    <li class="menu-title mt-2">Depot Storage</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('vendor.rack-bookings.marketplace') ? 'active' : '' }}"
                            href="{{ route('vendor.rack-bookings.marketplace') }}">
                            <span class="nav-icon"><iconify-icon icon="solar:widget-bold-duotone" class="text-success"></iconify-icon></span>
                            <span class="nav-text fw-bold text-success">Book Rack</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('vendor.rack-bookings.index') ? 'active' : '' }}"
                            href="{{ route('vendor.rack-bookings.index') }}">
                            <span class="nav-icon"><iconify-icon icon="solar:history-bold-duotone"></iconify-icon></span>
                            <span class="nav-text">My Rack Bookings</span>
                        </a>
                    </li>

                    {{-- ========= WALLET & PAYOUTS ========= --}}
                    <li class="menu-title mt-2">Finance & Wallet</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('vendor.wallet.*') ? 'active' : '' }}"
                            href="{{ route('vendor.wallet.index') }}">
                            <span class="nav-icon"><iconify-icon icon="solar:wallet-money-bold-duotone" class="text-primary"></iconify-icon></span>
                            <span class="nav-text">Wallet & Payouts</span>
                        </a>
                    </li>

                    {{-- ========= PURCHASE ORDERS ========= --}}
                    <li class="menu-title mt-2">Purchase Orders</li>
                    <li class="nav-item">
                        <a class="nav-link menu-arrow {{ request()->routeIs('vendor.purchase-orders.*') ? '' : '' }}"
                            href="#sidebarVendorPurchaseOrders" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->routeIs('vendor.purchase-orders.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarVendorPurchaseOrders">
                            <span class="nav-icon"><iconify-icon icon="solar:cart-large-minimalistic-bold-duotone"></iconify-icon></span>
                            <span class="nav-text">Manage POs</span>
                        </a>
                        <div class="collapse {{ request()->routeIs('vendor.purchase-orders.*') ? 'show' : '' }}"
                            id="sidebarVendorPurchaseOrders">
                            <ul class="nav sub-navbar-nav">
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('vendor.purchase-orders.index') ? 'active' : '' }}"
                                        href="{{ route('vendor.purchase-orders.index') }}">All POs</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('vendor.purchase-orders.new') ? 'active' : '' }}"
                                        href="{{ route('vendor.purchase-orders.new') }}">New POs</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('vendor.purchase-orders.accepted') ? 'active' : '' }}"
                                        href="{{ route('vendor.purchase-orders.accepted') }}">Accepted POs</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('vendor.purchase-orders.verify') ? 'active' : '' }}"
                                        href="{{ route('vendor.purchase-orders.verify') }}">Payment Verification</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('vendor.purchase-orders.ready') ? 'active' : '' }}"
                                        href="{{ route('vendor.purchase-orders.ready') }}">Ready for Dispatch</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('vendor.purchase-orders.dispatched') ? 'active' : '' }}"
                                        href="{{ route('vendor.purchase-orders.dispatched') }}">Dispatched Orders</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('vendor.purchase-orders.received') ? 'active' : '' }}"
                                        href="{{ route('vendor.purchase-orders.received') }}">Received History</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('vendor.purchase-orders.rejected') ? 'active' : '' }}"
                                        href="{{ route('vendor.purchase-orders.rejected') }}">Rejected POs</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('vendor.purchase-orders.cancelled') ? 'active' : '' }}"
                                        href="{{ route('vendor.purchase-orders.cancelled') }}">Cancelled POs</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    {{-- ========= DISCREPANCY DEBIT & CREDIT NOTES ========= --}}
                    <li class="menu-title mt-2">Discrepancy Claims & Notes</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('vendor.debit-notes.*') ? 'active' : '' }}"
                            href="{{ route('vendor.debit-notes.index') }}">
                            <span class="nav-icon"><iconify-icon icon="solar:document-add-bold-duotone" class="text-danger"></iconify-icon></span>
                            <span class="nav-text fw-bold">Debit Notes</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('vendor.credit-notes.*') ? 'active' : '' }}"
                            href="{{ route('vendor.credit-notes.index') }}">
                            <span class="nav-icon"><iconify-icon icon="solar:document-text-bold-duotone" class="text-success"></iconify-icon></span>
                            <span class="nav-text fw-bold">Credit Notes</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('vendor.excess-invoices.*') ? 'active' : '' }}"
                            href="{{ route('vendor.excess-invoices.index') }}">
                            <span class="nav-icon"><iconify-icon icon="solar:box-minimalistic-bold-duotone" class="text-primary"></iconify-icon></span>
                            <span class="nav-text fw-bold">Excess Stock Invoices</span>
                        </a>
                    </li>

                    @if(auth('vendor')->check() && auth('vendor')->user()->sellerProfile?->is_ddc_subscribed)
                        <li class="nav-item">
                            <a class="nav-link menu-arrow {{ request()->routeIs('vendor.smart-sales-incentive*') ? 'active' : '' }}"
                                href="#sidebarSsi" data-bs-toggle="collapse" role="button" aria-expanded="{{ request()->routeIs('vendor.smart-sales-incentive*') ? 'true' : 'false' }}" aria-controls="sidebarSsi">
                                <span class="nav-icon">
                                    <iconify-icon icon="solar:ticket-sale-bold-duotone" class="text-primary"></iconify-icon>
                                </span>
                                <span class="nav-text">Smart Sales Incentive</span>
                            </a>
                            <div class="collapse {{ request()->routeIs('vendor.smart-sales-incentive*') ? 'show' : '' }}" id="sidebarSsi">
                                <ul class="nav sub-navbar-nav">
                                    <li class="sub-nav-item">
                                        <a class="sub-nav-link {{ request()->routeIs('vendor.smart-sales-incentive') && !request()->routeIs('vendor.smart-sales-incentive.deductions') ? 'active' : '' }}"
                                            href="{{ route('vendor.smart-sales-incentive') }}">Slab Configuration</a>
                                    </li>
                                    <li class="sub-nav-item">
                                        <a class="sub-nav-link {{ request()->routeIs('vendor.smart-sales-incentive.deductions') ? 'active' : '' }}"
                                            href="{{ route('vendor.smart-sales-incentive.deductions') }}">SSI Deductions Log</a>
                                    </li>
                                </ul>
                            </div>
                        </li>
                    @endif

                    {{-- ========= ACCOUNT ========= --}}
                    <li class="menu-title mt-2">Account</li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('vendor.profile') ? 'active' : '' }}"
                            href="{{ route('vendor.profile') }}">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:user-circle-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">My Profile</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('vendor.profile.change-password') ? 'active' : '' }}"
                            href="{{ route('vendor.profile.change-password') }}">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:lock-password-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">Change Password</span>
                        </a>
                    </li>

                </ul>
            </div>
        </div>
        <!-- ========== App Menu End ========== -->

        <!-- ========== Page Content Start ========== -->
        <div class="page-content">
            <div class="container-fluid">
                <!-- Alerts removed. Handled via Toastify at bottom of script. -->
                @yield('content')
            </div>

            <!-- ========== Footer Start ========== -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12 text-center">
                            <script>document.write(new Date().getFullYear())</script> &copy; Indian MSME — SELLER Panel
                        </div>
                    </div>
                </div>
            </footer>
            <!-- ========== Footer End ========== -->
        </div>
        <!-- ========== Page Content End ========== -->

    </div>
    <!-- END Wrapper -->

    <!-- Vendor & App Scripts -->
    <script src="{{ asset('admin-assets/js/vendor.js') }}"></script>
    <script src="{{ asset('admin-assets/js/app.js') }}"></script>

    <!-- Tom Select: zero-dependency searchable dropdown -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

    <!-- SweetAlert & Toastify Local Vendor Assets -->
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
    <script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/toastify/toastify.min.css') }}">
    <script type="text/javascript" src="{{ asset('plugins/toastify/toastify.min.js') }}"></script>

    <!-- SweetAlert and Toastify Setup & Helpers -->
    <script>
        // Global TomSelect initializer helper
        function initSUIDropdown(ctx) {
            if (typeof TomSelect === 'undefined') return;
            var selects = !ctx
                ? document.querySelectorAll('select.s-select')
                : ctx.querySelectorAll('select.s-select');

            selects.forEach(function (el) {
                if (el.tomselect) return; // already initialized

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
            // Re-init inside modals when they fully open
            document.querySelectorAll('.modal').forEach(function (modal) {
                modal.addEventListener('shown.bs.modal', function () {
                    initSUIDropdown(this);
                });
            });

            // Global SweetAlert Confirm
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

            // phpMyAdmin style sorting helper
            window.sortBy = function (field) {
                const sortInput = document.getElementById('formSort');
                const orderInput = document.getElementById('formOrder');
                const form = document.getElementById('filterForm');

                if (!sortInput || !orderInput || !form) return;

                if (sortInput.value === field) {
                    orderInput.value = orderInput.value === 'asc' ? 'desc' : 'asc';
                } else {
                    sortInput.value = field;
                    orderInput.value = 'asc';
                }
                form.submit();
            };

            // Checkbox selection helper for bulk operations
            document.addEventListener('change', function (e) {
                if (e.target && e.target.id === 'selectAllCheckbox') {
                    const checkboxes = document.querySelectorAll('.row-checkbox');
                    checkboxes.forEach(cb => cb.checked = e.target.checked);
                    updateBulkActionsContainer();
                } else if (e.target && e.target.classList.contains('row-checkbox')) {
                    const selectAll = document.getElementById('selectAllCheckbox');
                    const checkboxes = document.querySelectorAll('.row-checkbox');
                    const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;

                    if (selectAll) {
                        selectAll.checked = checkedCount === checkboxes.length;
                    }
                    updateBulkActionsContainer();
                }
            });

            function updateBulkActionsContainer() {
                const container = document.getElementById('bulkActionsDropdownContainer');
                const counter = document.getElementById('selectedCount');
                if (!container || !counter) return;

                const checkedCount = document.querySelectorAll('.row-checkbox:checked').length;
                counter.textContent = checkedCount;

                if (checkedCount > 0) {
                    container.style.display = 'inline-block';
                } else {
                    container.style.display = 'none';
                }
            }

            // Trigger Bulk Action
            window.triggerBulkAction = function (action) {
                const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
                const ids = Array.from(checkedBoxes).map(cb => cb.value);

                if (ids.length === 0) return;

                let confirmMsg = `Are you sure you want to perform this action on ${ids.length} selected items?`;
                if (action === 'delete') {
                    confirmMsg = `Are you sure you want to delete the ${ids.length} selected items? This action cannot be undone.`;
                }

                Swal.fire({
                    title: 'Are you sure?',
                    text: confirmMsg,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#10b981',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Yes, proceed!'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.showLoading();
                        const form = document.createElement('form');
                        form.method = 'POST';
                        form.action = window.location.pathname.replace(/\/+$/, "") + '/bulk';

                        const csrfInput = document.createElement('input');
                        csrfInput.type = 'hidden';
                        csrfInput.name = '_token';
                        csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                        form.appendChild(csrfInput);

                        const actionInput = document.createElement('input');
                        actionInput.type = 'hidden';
                        actionInput.name = 'action';
                        actionInput.value = action;
                        form.appendChild(actionInput);

                        ids.forEach(id => {
                            const idInput = document.createElement('input');
                            idInput.type = 'hidden';
                            idInput.name = 'ids[]';
                            idInput.value = id;
                            form.appendChild(idInput);
                        });

                        document.body.appendChild(form);
                        form.submit();
                    }
                });
            };

            // Dynamic Excel/CSV export helper
            window.triggerExport = function (format) {
                const form = document.getElementById('filterForm');
                let params = new URLSearchParams();
                if (form) {
                    const formData = new FormData(form);
                    params = new URLSearchParams(formData);
                }

                // If there are selected checkboxes, export only selected
                const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
                if (checkedBoxes.length > 0) {
                    const ids = Array.from(checkedBoxes).map(cb => cb.value);
                    params.set('ids', ids.join(','));
                }

                params.set('export', format);
                window.location.href = window.location.pathname.replace(/\/+$/, "") + '?' + params.toString();
            };

            // Toastify helper function
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

            // Session Alerts to Toastify
            @if(session('success')) showToast("{{ session('success') }}", "success"); @endif
            @if(session('error')) showToast("{{ session('error') }}", "error"); @endif
            @if(session('warning')) showToast("{{ session('warning') }}", "warning"); @endif
            @if(session('info')) showToast("{{ session('info') }}", "info"); @endif
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


    @if(auth('vendor')->check() && auth('vendor')->user()->isEligibleForDdcSubscription())
        @php
            $sellerProfile = auth('vendor')->user()->sellerProfile;
            $ddcSubStatus = $sellerProfile?->ddc_subscription_status ?? 'none';
            $isDdcSubscribed = ($ddcSubStatus === 'approved');

            // Fetch ASWT Official Bank Details & QR Code for DDC Subscription
            $aswtBankName      = \App\Models\SystemSetting::getVal('aswt_bank_name', 'State Bank of India');
            $aswtAccountName   = \App\Models\SystemSetting::getVal('aswt_account_name', 'ASC DISTRIBUTOR PRIVATE LIMITED');
            $aswtAccountNumber = \App\Models\SystemSetting::getVal('aswt_account_number', '43753668461');
            $aswtIfscCode      = \App\Models\SystemSetting::getVal('aswt_ifsc_code', 'SBIN0070613');
            $aswtBankBranch    = \App\Models\SystemSetting::getVal('aswt_bank_branch', 'SECTOR EIGHT, ROHINI BRANCH');
            $aswtUpiId         = \App\Models\SystemSetting::getVal('aswt_upi_id', 'ascto@sbi');

            $aswtQrCodeRaw = \App\Models\SystemSetting::getVal('aswt_qr_code');
            if ($aswtQrCodeRaw) {
                if (filter_var($aswtQrCodeRaw, FILTER_VALIDATE_URL)) {
                    $aswtQrCode = $aswtQrCodeRaw;
                } elseif (str_starts_with($aswtQrCodeRaw, 'storage/')) {
                    $aswtQrCode = asset($aswtQrCodeRaw);
                } else {
                    $aswtQrCode = asset('storage/' . $aswtQrCodeRaw);
                }
            } else {
                $aswtQrCode = asset('admin-assets/images/qr-code.png');
            }
        @endphp
        <!-- DDC Subscription Modal -->
        <div class="modal fade" id="ddcSubscriptionModal" tabindex="-1" aria-labelledby="ddcSubscriptionModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px; overflow: hidden;">
                    <div class="modal-header bg-primary text-white py-3 px-4 d-flex align-items-center justify-content-between">
                        <h5 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="ddcSubscriptionModalLabel">
                            <iconify-icon icon="solar:star-bold-duotone" class="fs-22 align-middle"></iconify-icon>
                            DDC Model Subscription — Join Ardh Sainik Mall
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 bg-light-subtle">
                        <!-- Navigation Tabs -->
                        <ul class="nav nav-pills nav-justified mb-3 p-1 bg-light border" id="ddcLangTab" role="tablist" style="border-radius: 30px;">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active rounded-pill fw-semibold py-2" id="en-tab" data-bs-toggle="tab" data-bs-target="#en-content" type="button" role="tab" aria-controls="en-content" aria-selected="true">
                                    <iconify-icon icon="emojione:flag-for-united-kingdom" class="align-middle me-1"></iconify-icon> English
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill fw-semibold py-2" id="hi-tab" data-bs-toggle="tab" data-bs-target="#hi-content" type="button" role="tab" aria-controls="hi-content" aria-selected="false">
                                    <iconify-icon icon="emojione:flag-for-india" class="align-middle me-1"></iconify-icon> हिंदी (Hindi)
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Contents -->
                        <div class="tab-content border rounded-3 p-3 bg-white mb-4" id="ddcLangTabContent" style="max-height: 380px; overflow-y: auto; font-size: 0.92rem; line-height: 1.6; border-color: #e2e8f0 !important;">
                            <!-- English Content -->
                            <div class="tab-pane fade show active" id="en-content" role="tabpanel" aria-labelledby="en-tab">
                                <div class="text-center mb-3">
                                    <span class="badge bg-warning-subtle text-warning fw-bold px-3 py-1.5 fs-12 uppercase" style="border-radius: 12px;">Offer for Sellers / Manufacturers</span>
                                </div>
                                <h5 class="fw-bold text-slate-800 mb-2">Ardh Sainik Mall DDC (District Distribution & Display Centre)</h5>
                                <p class="text-primary fw-semibold mb-3" style="font-size: 0.95rem;">A Revolutionary Initiative for Nationwide Distribution — Reaching Every Village!</p>
                                <p class="text-muted mb-3">Join the DDC Model and expand your reach with confidence! Avail the following exclusive facilities designed for smooth and effective business operations under the DDC Phygital (Digital + Physical) Distribution Model – (An initiative of IndianMSME.org under the “From Makers to Market” Mission)</p>
                                
                                <div class="d-flex flex-column gap-3 text-slate-700">
                                    <div class="p-3 rounded-3 border-start border-primary border-3" style="background-color: #f8fafc;">
                                        <strong class="text-slate-900 d-block mb-1">a) National Distribution Network</strong>
                                        Partner with Ardh Sainik Mall DDC as your official distribution partner and ensure your brand’s physical presence in 800+ districts across India. <span class="badge bg-primary-subtle text-primary ms-1">Mission: One District – One DDC</span>
                                    </div>
                                    <div class="p-3 rounded-3 border-start border-primary border-3" style="background-color: #f8fafc;">
                                        <strong class="text-slate-900 d-block mb-1">b) Rack Branding & Product Display</strong>
                                        Secure a dedicated branded rack at DDCs and Ardh Sainik Canteen retail stores to showcase your product range.
                                    </div>
                                    <div class="p-3 rounded-3 border-start border-primary border-3" style="background-color: #f8fafc;">
                                        <strong class="text-slate-900 d-block mb-1">c) Smart Sales Force – Abhhyam Marketing Advisors</strong>
                                        Leverage our freelance Smart Sales Network of Abhyam Marketing Advisors to boost the sales on a performance basis.
                                    </div>
                                    <div class="p-3 rounded-3 border-start border-primary border-3" style="background-color: #f8fafc;">
                                        <strong class="text-slate-900 d-block mb-1">d) Local B2B Delivery Network – Abhi Ayaa Partners</strong>
                                        Get access to Abhi Ayaa Delivery Partners (B2B) for faster movement of goods within the district.
                                    </div>
                                </div>
                                <div class="text-center mt-3 p-2 bg-success-subtle text-success fw-bold rounded-2">
                                    Connect today to book your DDC association slot and rack space!
                                </div>
                            </div>

                            <!-- Hindi Content -->
                            <div class="tab-pane fade" id="hi-content" role="tabpanel" aria-labelledby="hi-tab">
                                <div class="text-center mb-3">
                                    <span class="badge bg-warning-subtle text-warning fw-bold px-3 py-1.5 fs-12" style="border-radius: 12px;">सेलर्स / मैन्युफैक्चरर्स के लिए ऑफर</span>
                                </div>
                                <h5 class="fw-bold text-slate-800 mb-2">अर्ध सैनिक मॉल DDC (जिला वितरण एवं डिस्प्ले सेंटर)</h5>
                                <p class="text-primary fw-semibold mb-3" style="font-size: 0.95rem;">गांव-गांव तक राष्ट्रीय वितरण की एक क्रांतिकारी पहल! DDC मॉडल से जुड़ें और अपने ब्रांड की पहुंच बढ़ाएँ!</p>
                                <p class="text-muted mb-3">अपनी कंपनी के सुचारू और प्रभावी संचालन के लिए निम्नलिखित सुविधाओं का लाभ उठाएँ। यह सुविधा DDC फिजिटल (डिजिटल + फिजिकल) डिस्ट्रीब्यूशन मॉडल के अंतर्गत दी जा रही है – (IndianMSME.org के ‘From Makers to Market’ मिशन के अंतर्गत)।</p>
                                
                                <div class="d-flex flex-column gap-3 text-slate-700">
                                    <div class="p-3 rounded-3 border-start border-primary border-3" style="background-color: #f8fafc;">
                                        <strong class="text-slate-900 d-block mb-1">a) राष्ट्रीय वितरण नेटवर्क</strong>
                                        अर्ध सैनिक मॉल DDC को अपने आधिकारिक वितरण साझेदार के रूप में जोड़ें और अपने ब्रांड की उपस्थिति भारत के 800+ जिलों में सुनिश्चित करें। <span class="badge bg-primary-subtle text-primary ms-1">मिशन: एक जिला – एक DDC</span>
                                    </div>
                                    <div class="p-3 rounded-3 border-start border-primary border-3" style="background-color: #f8fafc;">
                                        <strong class="text-slate-900 d-block mb-1">b) रैक ब्रांडिंग एवं उत्पाद प्रदर्शन</strong>
                                        DDC और अर्ध सैनिक कैंटीन रिटेल स्टोर्स में अपने उत्पादों के प्रदर्शन हेतु एक समर्पित ब्रांडेड रैक बुक करें। उत्पादों को प्रदर्शित करें, हमारे सेल्समैन को प्रशिक्षित करें और बिक्री बढ़ाएँ।
                                    </div>
                                    <div class="p-3 rounded-3 border-start border-primary border-3" style="background-color: #f8fafc;">
                                        <strong class="text-slate-900 d-block mb-1">c) स्मार्ट सेल्स फोर्स – अभयम मार्केटिंग एडवाइज़र</strong>
                                        प्रदर्शन के आधार पर बिक्री को बढ़ावा देने के लिए अभ्यम मार्केटिंग एडवाइज़र्स के हमारे फ्रीलांस स्मार्ट सेल्स नेटवर्क का लाभ उठाएं।
                                    </div>
                                    <div class="p-3 rounded-3 border-start border-primary border-3" style="background-color: #f8fafc;">
                                        <strong class="text-slate-900 d-block mb-1">d) स्थानीय B2B डिलीवरी नेटवर्क – अभीआया पार्टनर्स</strong>
                                        तेजी से डिलीवरी के लिए अभीआया डिलीवरी पार्टनर्स (B2B) की सुविधा प्राप्त करें। यह सुविधा जल्द ही आपके क्षेत्र में उपलब्ध होगी। अब समय है अपने ब्रांड की पहचान को जिले से गाँव तक पहुँचाने का — अर्ध सैनिक मॉल DDC के विश्वसनीय वितरण नेटवर्क के साथ।
                                    </div>
                                </div>
                                <div class="text-center mt-3 p-2 bg-success-subtle text-success fw-bold rounded-2">
                                    आज ही संपर्क करें और अपना DDC Model एसोसिएशन स्लॉट तथा रैक स्पेस बुक करें!
                                </div>
                            </div>
                        </div>

                        <!-- Action Section depending on status -->
                        @if($ddcSubStatus === 'none' || $ddcSubStatus === 'rejected')
                            @if($ddcSubStatus === 'rejected')
                                <div class="alert alert-danger mb-3 p-3 border-danger">
                                    <h6 class="fw-bold mb-1 d-flex align-items-center gap-1">
                                        <iconify-icon icon="solar:danger-bold" class="fs-18"></iconify-icon> Request Rejected
                                    </h6>
                                    <p class="mb-0 fs-13">Your previous DDC subscription request was rejected by the admin. <br><strong>Reason:</strong> {{ $sellerProfile->ddc_subscription_reject_reason }}</p>
                                </div>
                            @endif

                            <div class="card border border-light-subtle rounded-3 p-4 mb-0 bg-white shadow-sm">
                                <h6 class="fw-bold text-slate-800 mb-3 d-flex align-items-center gap-1">
                                    <iconify-icon icon="solar:card-transfer-bold-duotone" class="text-primary fs-20"></iconify-icon>
                                    Subscribe to DDC Model — Payment Required (₹10,000)
                                </h6>

                                <!-- Official Bank Details & QR Code Box -->
                                <div class="row g-3 mb-4 p-3 rounded-3 bg-light border">
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
                                        <img src="{{ $aswtQrCode }}" alt="Payment QR Code" class="img-fluid border rounded bg-white shadow-sm p-1 mb-1" style="max-height: 140px; object-fit: contain;">
                                        <div class="fw-bold text-success fs-15 mt-1">Amount: ₹10,000</div>
                                    </div>
                                </div>

                                <!-- Payment Verification Submission Form -->
                                <form action="{{ route('vendor.profile.ddc-subscription') }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <label for="ddc_subscription_utr" class="form-label fw-bold text-slate-800 mb-1">UTR / Transaction ID <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control form-control-sm fw-bold" name="ddc_subscription_utr" id="ddc_subscription_utr" placeholder="Enter 12-digit UTR or Ref No." required>
                                        </div>
                                        <div class="col-md-6">
                                            <label for="ddc_subscription_slip" class="form-label fw-bold text-slate-800 mb-1">Upload Payment Slip / Receipt <span class="text-danger">*</span></label>
                                            <input type="file" class="form-control form-control-sm" name="ddc_subscription_slip" id="ddc_subscription_slip" accept="image/*,application/pdf" required>
                                            <span class="d-block text-muted fs-10 mt-1">Accepts JPG, PNG, PDF up to 5MB</span>
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-warning w-100 fw-bold py-2 shadow-sm text-dark">
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
                                    <div><strong>UTR Reference:</strong> <code>{{ $sellerProfile->ddc_subscription_utr }}</code></div>
                                    @if($sellerProfile->ddc_subscription_submitted_at)
                                        <div><strong>Submitted On:</strong> {{ \Carbon\Carbon::parse($sellerProfile->ddc_subscription_submitted_at)->format('d M Y, h:i A') }}</div>
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
                </div>
            </div>
        </div>

        <!-- Javascript checks -->
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                // Check if seller is subscribed
                const isSubscribed = {{ $isDdcSubscribed ? 'true' : 'false' }};
                const currentRouteName = "{{ request()->route()->getName() }}";
                const sellerId = "{{ auth('vendor')->user()->id }}";

                // Pop up on dashboard daily one time if not subscribed
                if (!isSubscribed && currentRouteName === 'vendor.dashboard') {
                    const today = new Date().toISOString().split('T')[0]; // Format: YYYY-MM-DD
                    const storageKey = 'ddc_modal_last_shown_' + sellerId;
                    const lastShown = localStorage.getItem(storageKey);

                    if (lastShown !== today) {
                        // Slight delay to ensure bootstrap is fully loaded
                        setTimeout(function() {
                            var modalEl = document.getElementById('ddcSubscriptionModal');
                            if (modalEl) {
                                var myModal = new bootstrap.Modal(modalEl);
                                myModal.show();
                                localStorage.setItem(storageKey, today);
                            }
                        }, 500);
                    }
                }
            });
        </script>
    @endif

    <!-- Universal Table Action & State Preservation Script -->
    <script src="{{ asset('js/table-action-handler.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <script>
        window.exportToExcel = function(filename, dataArray, sheetName = 'Import_Data') {
            if (!dataArray || dataArray.length === 0) {
                alert('No records available to export.');
                return;
            }

            const cleanFilename = filename.toLowerCase().endsWith('.xlsx') ? filename : (filename + '.xlsx');

            if (typeof XLSX !== 'undefined') {
                try {
                    const worksheet = XLSX.utils.json_to_sheet(dataArray);
                    const workbook = XLSX.utils.book_new();
                    XLSX.utils.book_append_sheet(workbook, worksheet, sheetName);
                    XLSX.writeFile(workbook, cleanFilename);
                    return;
                } catch (e) {
                    console.warn('XLSX export failed, using CSV Excel fallback:', e);
                }
            }

            const keys = Object.keys(dataArray[0]);
            const csvLines = [keys.map(k => `"${String(k).replace(/"/g, '""')}"`).join(',')];
            dataArray.forEach(row => {
                const line = keys.map(k => `"${String(row[k] ?? '').replace(/"/g, '""')}"`).join(',');
                csvLines.push(line);
            });

            const blob = new Blob(['\uFEFF' + csvLines.join('\r\n')], { type: 'text/csv;charset=utf-8;' });
            const a = document.createElement('a');
            a.href = URL.createObjectURL(blob);
            a.download = cleanFilename.replace(/\.xlsx$/i, '.csv');
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        };

        window.exportImportPreviewToExcel = function(filename, previewArray, onlyErrors = false) {
            if (!previewArray || previewArray.length === 0) {
                alert('No preview data available to export.');
                return;
            }

            let rowsToExport = previewArray;
            if (onlyErrors) {
                rowsToExport = previewArray.filter(item => {
                    const isValid = item.is_valid ?? item.isValid ?? true;
                    const hasErrors = Array.isArray(item.errors) ? item.errors.length > 0 : !isValid;
                    return !isValid || hasErrors;
                });

                if (rowsToExport.length === 0) {
                    alert('Great news! No error or invalid records found in preview.');
                    return;
                }
            }

            const formattedRows = rowsToExport.map(item => {
                const rowNum = item.row_number ?? item.row ?? '—';
                const identifier = item.seller_code ?? item.psc_code ?? item.product_code ?? item.ddc_code ?? item.adc_code ?? item.local_product_code ?? item.email ?? item.mobile ?? item.barcode ?? item.code ?? '—';
                const name = item.seller_name ?? item.company_name ?? item.product_name ?? item.store_name ?? item.district_name ?? item.customer_name ?? item.ddc_name ?? item.adc_name ?? item.name ?? '—';
                const status = (item.is_valid ?? item.isValid ?? true) ? 'Valid' : 'Validation Error';
                const errorMsg = Array.isArray(item.errors) ? item.errors.join(' | ') : (item.errors || item.message || 'Validation failed');

                return {
                    'Row Number': rowNum,
                    'Identifier / Code': identifier,
                    'Name / Title': name,
                    'Validation Status': status,
                    'Error Details': errorMsg
                };
            });

            window.exportToExcel(filename, formattedRows, 'Preview_Export');
        };

        window.exportImportLogsToExcel = function(filename, logArray, onlyErrors = false) {
            if (!logArray || logArray.length === 0) {
                alert('No import log records available to export.');
                return;
            }

            let rowsToExport = logArray;
            if (onlyErrors) {
                rowsToExport = logArray.filter(item => {
                    const st = String(item.status || item.Status || '').toLowerCase();
                    return st.includes('skip') || st.includes('fail') || st.includes('err') || item.is_valid === false;
                });

                if (rowsToExport.length === 0) {
                    alert('No skipped, failed, or error records found to export!');
                    return;
                }
            }

            const formattedRows = rowsToExport.map(item => {
                const rowNum = item.row ?? item.row_number ?? '—';
                const identifier = item.identifier ?? item.seller_code ?? item.psc_code ?? item.product_code ?? item.ddc_code ?? item.adc_code ?? item.code ?? item.email ?? item.mobile ?? item.data ?? '—';
                const name = item.name ?? item.seller_name ?? item.company_name ?? item.product_name ?? item.store_name ?? item.district_name ?? item.customer_name ?? item.ddc_name ?? item.adc_name ?? '—';
                const status = item.status ?? ((item.is_valid === false) ? 'Error' : 'Imported');
                const message = item.message ?? item.reason ?? (Array.isArray(item.errors) ? item.errors.join(' | ') : '—');

                return {
                    'Row Number': rowNum,
                    'Identifier / Code': identifier,
                    'Name / Value': name,
                    'Status': status,
                    'Details / Error Reason': message
                };
            });

            window.exportToExcel(filename, formattedRows, 'Import_Log_Report');
        };

        window.exportModalTableToExcel = function(modalId, filename, onlyErrors = false) {
            const modal = document.getElementById(modalId);
            if (!modal) return;
            const table = modal.querySelector('table');
            if (!table) { alert('No report table found in modal.'); return; }

            const headerCols = Array.from(table.querySelectorAll('thead th')).map(th => th.textContent.trim());
            const bodyRows = Array.from(table.querySelectorAll('tbody tr'));

            if (bodyRows.length === 0) {
                alert('No log rows available to export.');
                return;
            }

            const dataRows = [];
            bodyRows.forEach(tr => {
                const cols = Array.from(tr.querySelectorAll('td')).map(td => td.textContent.trim());
                if (cols.length === 0) return;

                const rowObj = {};
                headerCols.forEach((h, idx) => {
                    rowObj[h || `Col ${idx+1}`] = cols[idx] || '';
                });

                const statusStr = (rowObj['Status'] || cols.join(' ')).toLowerCase();
                const isErrorRow = statusStr.includes('skip') || statusStr.includes('fail') || statusStr.includes('err');

                if (!onlyErrors || isErrorRow) {
                    dataRows.push(rowObj);
                }
            });

            if (dataRows.length === 0) {
                alert('No skipped, failed, or error records found in table!');
                return;
            }

            window.exportToExcel(filename, dataRows, 'Modal_Export');
        };

        window.exportImportLogsToCsv = function(filename, logArray, onlyErrors) {
            window.exportImportLogsToExcel(filename, logArray, onlyErrors);
        };
        window.exportModalTableToCsv = function(modalId, filename) {
            window.exportModalTableToExcel(modalId, filename, true);
        };
    </script>
</body>

</html>