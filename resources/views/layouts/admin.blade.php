<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Title Meta -->
    <meta charset="utf-8" />
    <title>@yield('title', 'Dashboard') | Admin Panel — Indian MSME</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Indian MSME Admin Panel" />
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <!-- App favicon -->
    <link rel="shortcut icon"
        href="{{ \App\Models\SystemSetting::getVal('favicon', asset('admin-assets/images/favicon.ico')) }}">

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
            box-shadow: 0 0 0 0.25rem rgba(79, 70, 229, 0.25) !important;
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

        /* ── Badge text visibility fix ── */
        .bg-success-subtle {
            background-color: rgba(40, 199, 111, 0.15) !important;
        }

        .text-success {
            color: #28c76f !important;
        }

        .bg-danger-subtle {
            background-color: rgba(234, 84, 85, 0.15) !important;
        }

        .text-danger {
            color: #ea5455 !important;
        }

        .bg-warning-subtle {
            background-color: rgba(255, 159, 67, 0.15) !important;
        }

        .text-warning {
            color: #ff9f43 !important;
        }

        .bg-info-subtle {
            background-color: rgba(0, 207, 232, 0.15) !important;
        }

        .text-info {
            color: #00cfe8 !important;
        }

        .badge.bg-success-subtle,
        .badge.bg-danger-subtle,
        .badge.bg-warning-subtle,
        .badge.bg-info-subtle {
            font-weight: 600 !important;
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
            --bs-primary: #4f46e5 !important;
            --bs-primary-rgb: 79, 70, 229 !important;
            --bs-link-color: #4f46e5 !important;
            --bs-link-hover-color: #3730a3 !important;

            /* Custom Admin Theme Colors - Premium Indigo */
            --adm-primary: #4f46e5;
            --adm-primary-lt: #6366f1;
            --adm-primary-bg: rgba(79, 70, 229, 0.12);
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
            background: rgba(79, 70, 229, 0.05) !important;
        }

        .main-nav .navbar-nav .nav-link:hover .nav-icon {
            color: var(--adm-primary) !important;
        }

        /* Active nav item */
        .main-nav .navbar-nav .nav-link.active,
        .main-nav .navbar-nav .nav-link[aria-expanded="true"] {
            color: var(--adm-primary) !important;
            background: rgba(79, 70, 229, 0.08) !important;
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
            background: rgba(79, 70, 229, 0.04) !important;
            padding-left: 22px;
        }

        .sub-navbar-nav .sub-nav-link:hover::before {
            background: var(--adm-primary);
            transform: translateY(0) scale(1.2) !important;
        }

        .sub-navbar-nav .sub-nav-link.active {
            color: var(--adm-primary) !important;
            font-weight: 600;
            background: rgba(79, 70, 229, 0.06) !important;
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
            background: rgba(99, 102, 241, 0.2) !important;
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
            background: rgba(99, 102, 241, 0.12) !important;
        }

        /* ── Buttons ── */
        .btn-primary {
            background: var(--adm-primary) !important;
            border-color: var(--adm-primary) !important;
            font-weight: 500 !important;
            letter-spacing: .2px !important;
        }

        .btn-primary:hover {
            background: #3730a3 !important;
            border-color: #3730a3 !important;
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
            background-color: rgba(79, 70, 229, 0.15) !important;
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
            background-color: rgba(79, 70, 229, 0.05) !important;
            color: var(--adm-primary) !important;
        }

        .sortable-header iconify-icon {
            transition: opacity 0.2s ease, transform 0.2s ease;
        }

        .sortable-header:hover iconify-icon {
            opacity: 1 !important;
        }

        /* ── Universal Table Alignment & Structure ── */
        .table> :not(caption)>*>* {
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

        .table>thead>tr>th:first-child,
        .table>tbody>tr>td:first-child {
            padding-left: 1.25rem !important;
        }

        .table>thead>tr>th:last-child,
        .table>tbody>tr>td:last-child {
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
            background: rgba(79, 70, 229, .03) !important;
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
                                <span class="badge bg-primary-subtle text-primary px-2 me-1">Admin</span>
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

                        <!-- Dark Mode -->
                        <div class="topbar-item">
                            <button type="button" class="topbar-button" id="light-dark-mode">
                                <iconify-icon icon="solar:moon-bold-duotone" class="fs-24 align-middle"></iconify-icon>
                            </button>
                        </div>

                        <!-- Notifications Dropdown -->
                        <div class="dropdown topbar-item">
                            @php
                                $adminNotifications = \App\Models\Notification::where(function ($q) {
                                    $q->where('recipient_type', 'admin')->orWhereNull('recipient_type');
                                })->where('is_read', false)->orderBy('created_at', 'desc')->take(5)->get();
                                $unreadCount = \App\Models\Notification::where(function ($q) {
                                    $q->where('recipient_type', 'admin')->orWhereNull('recipient_type');
                                })->where('is_read', false)->count();
                            @endphp
                            <a type="button" class="topbar-button position-relative"
                                id="page-header-notifications-dropdown" data-bs-toggle="dropdown" aria-haspopup="true"
                                aria-expanded="false">
                                <iconify-icon icon="solar:bell-bing-bold-duotone"
                                    class="fs-24 align-middle"></iconify-icon>
                                @if($unreadCount > 0)
                                    <span
                                        class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger"
                                        style="margin-top: 10px; margin-left: -10px; font-size: 10px;">
                                        {{ $unreadCount }}
                                    </span>
                                @endif
                            </a>
                            <div class="dropdown-menu dropdown-menu-end p-0"
                                style="width: 300px; max-height: 400px; overflow-y: auto;">
                                <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                                    <h6 class="mb-0 fw-bold">Notifications</h6>
                                    @if($unreadCount > 0)
                                        <a href="{{ route('admin.notifications.mark-all-read') }}"
                                            class="text-primary small fw-semibold">Mark all read</a>
                                    @endif
                                </div>
                                <div class="list-group list-group-flush">
                                    @forelse($adminNotifications as $notif)
                                        <a href="{{ $notif->link ?? '#' }}"
                                            class="list-group-item list-group-item-action p-3">
                                            <div class="d-flex align-items-start">
                                                <div class="flex-grow-1">
                                                    <h6 class="mb-1 text-slate-800 fw-semibold">{{ $notif->title }}</h6>
                                                    <p class="mb-1 text-muted small">{{ $notif->message }}</p>
                                                    <span
                                                        class="text-muted small fs-11">{{ $notif->created_at->diffForHumans() }}</span>
                                                </div>
                                            </div>
                                        </a>
                                    @empty
                                        <div class="p-4 text-center text-muted">
                                            <iconify-icon icon="solar:bell-broken"
                                                class="fs-32 text-muted mb-2"></iconify-icon><br>
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
                                <span class="d-flex align-items-center">
                                    <img class="rounded-circle" width="32" height="32"
                                        style="object-fit: cover; aspect-ratio: 1 / 1;"
                                        src="{{ auth('admin')->user()->avatar_url }}" alt="avatar">
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <h6 class="dropdown-header">Welcome {{ auth('admin')->user()->name }}!</h6>
                                <a class="dropdown-item" href="{{ route('admin.profile') }}">
                                    <i class="bx bx-user-circle text-muted fs-18 align-middle me-1"></i>
                                    <span class="align-middle">Profile</span>
                                </a>
                                <a class="dropdown-item" href="{{ route('admin.profile.change-password') }}">
                                    <i class="bx bx-lock text-muted fs-18 align-middle me-1"></i>
                                    <span class="align-middle">Change Password</span>
                                </a>
                                <div class="dropdown-divider my-1"></div>
                                <form action="{{ route('admin.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bx bx-log-out fs-18 align-middle me-1"></i>
                                        <span class="align-middle">Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- App Global Menu & Submenu Search -->
                        <div class="app-search d-none d-md-block ms-2 position-relative" style="min-width: 320px;">
                            <div class="position-relative">
                                <input type="text" id="adminGlobalSearchInput" class="form-control pe-5" placeholder="Search menu & submenus (e.g. Products, Margins, Wallets)..." autocomplete="off">
                                <span class="position-absolute end-0 top-50 translate-middle-y me-2 badge bg-light text-muted border fs-10 fw-bold d-none d-lg-inline-block">⌘K</span>
                                <iconify-icon icon="solar:magnifer-linear" class="search-widget-icon"></iconify-icon>
                            </div>

                            <!-- Live Search Results Dropdown -->
                            <div id="adminGlobalSearchResults" class="dropdown-menu shadow-lg border-0 w-100 p-0 mt-1 d-none" style="max-height: 420px; overflow-y: auto; z-index: 1080; border-radius: 10px;">
                                <div class="p-2 border-bottom bg-light text-muted fs-11 fw-semibold text-uppercase d-flex align-items-center justify-content-between">
                                    <span>Quick Menu Navigation</span>
                                    <span class="badge bg-secondary-subtle text-secondary fs-10">Use ↑ ↓ & Enter</span>
                                </div>
                                <div id="adminSearchListGroup" class="list-group list-group-flush fs-13">
                                    <!-- Dynamic Items Rendered Here -->
                                </div>
                                <div id="adminSearchNoResults" class="p-4 text-center text-muted d-none fs-12">
                                    <iconify-icon icon="solar:magnifer-bug-bold-duotone" class="fs-32 text-slate-300 mb-1"></iconify-icon>
                                    <p class="mb-0 fw-medium">No matching menu or submenu found.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        <!-- ========== Topbar End ========== -->

        <!-- ========== App Menu Start ========== -->
        <div class="main-nav">
            <!-- Sidebar Logo -->
            <div class="logo-box">
                <a href="{{ route('admin.dashboard') }}" class="logo-dark">
                    <img src="{{ \App\Models\SystemSetting::getVal('website_header_logo', \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png'))) }}"
                        class="logo-sm" alt="logo sm">
                    <img src="{{ \App\Models\SystemSetting::getVal('website_header_logo', \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png'))) }}"
                        class="logo-lg" alt="logo dark">
                </a>
                <a href="{{ route('admin.dashboard') }}" class="logo-light">
                    <img src="{{ \App\Models\SystemSetting::getVal('website_header_logo', \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png'))) }}"
                        class="logo-sm" alt="logo sm">
                    <img src="{{ \App\Models\SystemSetting::getVal('website_header_logo', \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png'))) }}"
                        class="logo-lg" alt="logo light">
                </a>
            </div>

            <div class="scrollbar" data-simplebar>
                <ul class="navbar-nav" id="navbar-nav">

                    {{-- ========= GENERAL ========= --}}
                    <li class="menu-title">General</li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}"
                            href="{{ route('admin.dashboard') }}">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:widget-5-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>

                    {{-- ========= MASTER DATA ========= --}}
                    @if(auth('admin')->user()->hasPermission('locations.view') || auth('admin')->user()->hasPermission('categories.view') || auth('admin')->user()->hasPermission('departments.view'))
                    <li class="menu-title mt-2">Master Data</li>
                    @endif

                    @permission('locations.view')
                    <li class="nav-item">
                        <a class="nav-link menu-arrow {{ request()->routeIs('admin.states.*') || request()->routeIs('admin.districts.*') || request()->routeIs('admin.pincodes.*') || request()->routeIs('admin.locations.*') ? '' : '' }}"
                            href="#sidebarLocations" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->routeIs('admin.states.*') || request()->routeIs('admin.districts.*') || request()->routeIs('admin.pincodes.*') || request()->routeIs('admin.locations.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarLocations">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:map-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">Location Master</span>
                        </a>
                        <div class="collapse {{ request()->routeIs('admin.states.*') || request()->routeIs('admin.districts.*') || request()->routeIs('admin.pincodes.*') ? 'show' : '' }}"
                            id="sidebarLocations">
                            <ul class="nav sub-navbar-nav">
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.states.*') ? 'active' : '' }}"
                                        href="{{ route('admin.states.index') }}">States</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.districts.*') ? 'active' : '' }}"
                                        href="{{ route('admin.districts.index') }}">Districts</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.pincodes.*') ? 'active' : '' }}"
                                        href="{{ route('admin.pincodes.index') }}">Pincodes</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endpermission

                    @permission('categories.view')
                    <li class="nav-item">
                        <a class="nav-link menu-arrow {{ request()->routeIs('admin.categories.*') || request()->routeIs('admin.sub-categories.*') || request()->routeIs('admin.child-categories.*') || request()->routeIs('admin.hsns.*') || request()->routeIs('admin.brands.*') || request()->routeIs('admin.units-of-measure.*') ? '' : '' }}"
                            href="#sidebarCategories" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->routeIs('admin.categories.*') || request()->routeIs('admin.sub-categories.*') || request()->routeIs('admin.child-categories.*') || request()->routeIs('admin.hsns.*') || request()->routeIs('admin.brands.*') || request()->routeIs('admin.units-of-measure.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarCategories">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:folder-with-files-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">Category & Product Master</span>
                        </a>
                        <div class="collapse {{ request()->routeIs('admin.categories.*') || request()->routeIs('admin.sub-categories.*') || request()->routeIs('admin.child-categories.*') || request()->routeIs('admin.hsns.*') || request()->routeIs('admin.brands.*') || request()->routeIs('admin.units-of-measure.*') ? 'show' : '' }}"
                            id="sidebarCategories">
                            <ul class="nav sub-navbar-nav">
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"
                                        href="{{ route('admin.categories.index') }}">Categories</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.sub-categories.*') ? 'active' : '' }}"
                                        href="{{ route('admin.sub-categories.index') }}">Sub Categories</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.child-categories.*') ? 'active' : '' }}"
                                        href="{{ route('admin.child-categories.index') }}">Child Categories</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.hsns.*') ? 'active' : '' }}"
                                        href="{{ route('admin.hsns.index') }}">HSN Master</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}"
                                        href="{{ route('admin.brands.index') }}">Brands</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.units-of-measure.*') ? 'active' : '' }}"
                                        href="{{ route('admin.units-of-measure.index') }}">Units of Measure</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endpermission

                    @permission('departments.view')
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.departments.*') ? 'active' : '' }}"
                            href="{{ route('admin.departments.index') }}">
                            <span class="nav-icon"><iconify-icon icon="solar:buildings-2-bold-duotone"></iconify-icon></span>
                            <span class="nav-text">Department Master</span>
                        </a>
                    </li>
                    @endpermission

                    {{-- ========= NETWORK PARTNERS ========= --}}
                    @permission('users.view')
                    <li class="menu-title mt-2">Network Partners</li>
                    <!-- Manage DDC -->
                    <li class="nav-item">
                        <a class="nav-link menu-arrow {{ request()->routeIs('admin.external.ddc.*') ? '' : '' }}"
                            href="#sidebarDDC" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->routeIs('admin.external.ddc.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarDDC">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:user-id-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">Manage DDC</span>
                        </a>
                        <div class="collapse {{ request()->routeIs('admin.external.ddc.*') ? 'show' : '' }}"
                            id="sidebarDDC">
                            <ul class="nav sub-navbar-nav">
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.external.ddc.index') || request()->routeIs('admin.external.ddc.create') || request()->routeIs('admin.external.ddc.edit') || request()->routeIs('admin.external.ddc.show') ? 'active' : '' }}"
                                        href="{{ route('admin.external.ddc.index') }}">DDC Users</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.external.ddc.wallet-requests') ? 'active' : '' }}"
                                        href="{{ route('admin.external.ddc.wallet-requests') }}">DDC Wallet Requests</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Manage ADC -->
                    <li class="nav-item">
                        <a class="nav-link menu-arrow {{ request()->routeIs('admin.external.adc.*') ? '' : '' }}"
                            href="#sidebarADC" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->routeIs('admin.external.adc.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarADC">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:user-speak-rounded-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">Manage ADC</span>
                        </a>
                        <div class="collapse {{ request()->routeIs('admin.external.adc.*') ? 'show' : '' }}"
                            id="sidebarADC">
                            <ul class="nav sub-navbar-nav">
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.external.adc.index') ? 'active' : '' }}"
                                        href="{{ route('admin.external.adc.index') }}">ADC Users</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.external.adc.wallet-requests') ? 'active' : '' }}"
                                        href="{{ route('admin.external.adc.wallet-requests') }}">ADC Wallet Requests</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Manage Vendors -->
                    <li class="nav-item">
                        <a class="nav-link menu-arrow {{ request()->routeIs('admin.external.vendors.*') ? '' : '' }}"
                            href="#sidebarVendors" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->routeIs('admin.external.vendors.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarVendors">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:shop-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">Vendors & Sellers</span>
                        </a>
                        <div class="collapse {{ request()->routeIs('admin.external.vendors.*') ? 'show' : '' }}"
                            id="sidebarVendors">
                            <ul class="nav sub-navbar-nav">
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.external.vendors.index') || request()->routeIs('admin.external.vendors.profile') ? 'active' : '' }}"
                                        href="{{ route('admin.external.vendors.index') }}">Vendors List</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.external.vendor.wallet-requests') ? 'active' : '' }}"
                                        href="{{ route('admin.external.vendor.wallet-requests') }}">Seller Wallet Requests</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.wallets.seller') ? 'active' : '' }}"
                                        href="{{ route('admin.wallets.seller') }}">Seller Wallets Management</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.external.pickup_store.wallet-requests') ? 'active' : '' }}"
                                        href="{{ route('admin.external.pickup_store.wallet-requests') }}">Pickup Store Wallet Requests</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('admin.external.vendors.ddc-requests') ? 'active' : '' }}"
                                        style="white-space: normal;"
                                        href="{{ route('admin.external.vendors.ddc-requests') }}">
                                        <span>DDC Subscription Requests</span>
                                        @php
                                            $pendingDdcSubCount = \App\Models\SellerProfile::where('ddc_subscription_status', 'pending')->count();
                                        @endphp
                                        @if($pendingDdcSubCount > 0)
                                            <span class="badge bg-warning text-dark rounded-pill ms-auto">{{ $pendingDdcSubCount }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.external.vendors.ddc-approved') ? 'active' : '' }}"
                                        style="white-space: normal;"
                                        href="{{ route('admin.external.vendors.ddc-approved') }}">DDC Subscriptions Approved</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    <!-- Bulk Import -->
                    <li class="nav-item">
                        <a class="nav-link menu-arrow {{ request()->routeIs('admin.import.*') ? '' : '' }}"
                            href="#sidebarImport" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->routeIs('admin.import.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarImport">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:file-download-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">Import</span>
                        </a>
                        <div class="collapse {{ request()->routeIs('admin.import.*') ? 'show' : '' }}"
                            id="sidebarImport">
                            <ul class="nav sub-navbar-nav">
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.import.seller*') ? 'active' : '' }}"
                                        href="{{ route('admin.import.seller') }}">Seller Import</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.import.sub_seller*') ? 'active' : '' }}"
                                        href="{{ route('admin.import.sub_seller') }}">Sub Seller Import</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.import.adc*') ? 'active' : '' }}"
                                        href="{{ route('admin.import.adc') }}">ADC Import</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.import.ddc') || request()->routeIs('admin.import.ddc.sample') || request()->routeIs('admin.import.ddc.preview') || request()->routeIs('admin.import.ddc.process') ? 'active' : '' }}"
                                        href="{{ route('admin.import.ddc') }}">DDC Import</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.import.product*') ? 'active' : '' }}"
                                        href="{{ route('admin.import.product') }}">Product Import</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.import.pickup_store*') ? 'active' : '' }}"
                                        href="{{ route('admin.import.pickup_store') }}">Pickup Store Import</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.import.delivery_area*') ? 'active' : '' }}"
                                        href="{{ route('admin.import.delivery_area') }}">Delivery Area Import</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.import.ddc_customer*') ? 'active' : '' }}"
                                        href="{{ route('admin.import.ddc_customer') }}">DDC Customer Import</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.import.adc_customer*') ? 'active' : '' }}"
                                        href="{{ route('admin.import.adc_customer') }}">ADC Customer Import</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.import.ddc_msme_inventory*') ? 'active' : '' }}"
                                        href="{{ route('admin.import.ddc_msme_inventory') }}">DDC MSME Inventory Import</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.import.ddc_local_inventory*') ? 'active' : '' }}"
                                        href="{{ route('admin.import.ddc_local_inventory') }}">DDC Local Inventory Import</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.import.adc_msme_inventory*') ? 'active' : '' }}"
                                        href="{{ route('admin.import.adc_msme_inventory') }}">ADC MSME Inventory Import</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.import.adc_local_inventory*') ? 'active' : '' }}"
                                        href="{{ route('admin.import.adc_local_inventory') }}">ADC Local Inventory Import</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endpermission

                    {{-- ========= ORDERS & LOGISTICS ========= --}}
                    <li class="menu-title mt-2">Orders & Logistics</li>
                    @permission('users.view')
                    <!-- Product Approvals -->
                    <li class="nav-item">
                        <a class="nav-link menu-arrow {{ request()->routeIs('admin.products.*') ? '' : '' }}"
                            href="#sidebarApprovals" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->routeIs('admin.products.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarApprovals">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:bill-list-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">Manage Products</span>
                            @php
                                $pendingProductsCount = \App\Models\Product::where('status', 'Pending Approval')->count();
                            @endphp
                            @if($pendingProductsCount > 0)
                                <span class="badge bg-danger rounded-pill ms-auto">{{ $pendingProductsCount }}</span>
                            @endif
                        </a>
                        <div class="collapse {{ request()->routeIs('admin.products.*') ? 'show' : '' }}"
                            id="sidebarApprovals">
                            <ul class="nav sub-navbar-nav">
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('admin.products.approvals.new') || (request()->routeIs('admin.products.approvals.index') && request('tab', 'new_approvals') === 'new_approvals') ? 'active' : '' }}"
                                        href="{{ route('admin.products.approvals.new') }}">
                                        <span>New Submissions</span>
                                        @php
                                            $newCount = \App\Models\Product::where('status', 'Pending Approval')->where('is_new', true)->count();
                                        @endphp
                                        @if($newCount > 0)
                                            <span class="badge bg-info rounded-pill py-0.5 px-1.5 fs-10"
                                                style="margin-left: auto;">{{ $newCount }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('admin.products.approvals.updated') || (request()->routeIs('admin.products.approvals.index') && request('tab') === 'update_approvals') ? 'active' : '' }}"
                                        href="{{ route('admin.products.approvals.updated') }}">
                                        <span>Update Submissions</span>
                                        @php
                                            $updateCount = \App\Models\Product::where('status', 'Pending Approval')->where('is_new', false)->count();
                                        @endphp
                                        @if($updateCount > 0)
                                            <span class="badge bg-warning text-dark rounded-pill py-0.5 px-1.5 fs-10"
                                                style="margin-left: auto;">{{ $updateCount }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.products.all') || request()->routeIs('admin.products.approvals.all') ? 'active' : '' }}"
                                        href="{{ route('admin.products.all') }}">
                                        All Products & Margins
                                    </a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.products.approvals.approved') || (request()->routeIs('admin.products.approvals.index') && request('tab') === 'approved') ? 'active' : '' }}"
                                        href="{{ route('admin.products.approvals.approved') }}">
                                        Approved Catalog
                                    </a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.products.approvals.rejected') || (request()->routeIs('admin.products.approvals.index') && request('tab') === 'rejected') ? 'active' : '' }}"
                                        href="{{ route('admin.products.approvals.rejected') }}">
                                        Rejected Catalog
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endpermission

                    <!-- Manage PO -->
                    <li class="nav-item">
                        <a class="nav-link menu-arrow {{ request()->routeIs('admin.purchase-orders.*') ? '' : '' }}"
                            href="#sidebarAdminPurchaseOrders" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->routeIs('admin.purchase-orders.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarAdminPurchaseOrders">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:cart-large-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">DDC Purchase Orders (PO)</span>
                        </a>
                        <div class="collapse {{ request()->routeIs('admin.purchase-orders.*') ? 'show' : '' }}"
                            id="sidebarAdminPurchaseOrders">
                            <ul class="nav sub-navbar-nav">
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.purchase-orders.index') && !request('status') ? 'active' : '' }}"
                                        href="{{ route('admin.purchase-orders.index') }}">All Orders</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.purchase-orders.index') && request('status') === 'Pending' ? 'active' : '' }}"
                                        href="{{ route('admin.purchase-orders.index', ['status' => 'Pending']) }}">PO Created</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.purchase-orders.index') && request('status') === 'Accepted' ? 'active' : '' }}"
                                        href="{{ route('admin.purchase-orders.index', ['status' => 'Accepted']) }}">Accepted</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.purchase-orders.index') && request('status') === 'Processing' ? 'active' : '' }}"
                                        href="{{ route('admin.purchase-orders.index', ['status' => 'Processing']) }}">Processing</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.purchase-orders.index') && request('status') === 'Dispatched' ? 'active' : '' }}"
                                        href="{{ route('admin.purchase-orders.index', ['status' => 'Dispatched']) }}">Dispatched</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.purchase-orders.index') && request('status') === 'Received' ? 'active' : '' }}"
                                        href="{{ route('admin.purchase-orders.index', ['status' => 'Received']) }}">Received</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.purchase-orders.index') && request('status') === 'Rejected' ? 'active' : '' }}"
                                        href="{{ route('admin.purchase-orders.index', ['status' => 'Rejected']) }}">Rejected</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.purchase-orders.index') && request('status') === 'Cancelled' ? 'active' : '' }}"
                                        href="{{ route('admin.purchase-orders.index', ['status' => 'Cancelled']) }}">Cancelled</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    {{-- ========= DISCREPANCY DEBIT & CREDIT NOTES ========= --}}
                    <li class="menu-title mt-2">Discrepancy Claims & Notes</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.debit-notes.*') ? 'active' : '' }}"
                            href="{{ route('admin.debit-notes.index') }}">
                            <span class="nav-icon"><iconify-icon icon="solar:document-add-bold-duotone" class="text-danger"></iconify-icon></span>
                            <span class="nav-text fw-bold">Debit Notes</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.credit-notes.*') ? 'active' : '' }}"
                            href="{{ route('admin.credit-notes.index') }}">
                            <span class="nav-icon"><iconify-icon icon="solar:document-text-bold-duotone" class="text-success"></iconify-icon></span>
                            <span class="nav-text fw-bold">Credit Notes</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.excess-invoices.*') ? 'active' : '' }}"
                            href="{{ route('admin.excess-invoices.index') }}">
                            <span class="nav-icon"><iconify-icon icon="solar:box-minimalistic-bold-duotone" class="text-primary"></iconify-icon></span>
                            <span class="nav-text fw-bold">Excess Stock Invoices</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.invoices.*') ? 'active' : '' }}"
                            href="{{ route('admin.invoices.index') }}">
                            <span class="nav-icon"><iconify-icon icon="solar:bill-list-bold-duotone" class="text-warning"></iconify-icon></span>
                            <span class="nav-text fw-bold">All Invoices</span>
                        </a>
                    </li>

                    <!-- ADC Orders -->
                    <li class="nav-item">
                        <a class="nav-link menu-arrow {{ request()->routeIs('admin.adc-orders.*') ? '' : '' }}"
                            href="#sidebarAdminAdcOrders" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->routeIs('admin.adc-orders.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarAdminAdcOrders">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:bag-check-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">ADC Orders</span>
                        </a>
                        <div class="collapse {{ request()->routeIs('admin.adc-orders.*') ? 'show' : '' }}"
                            id="sidebarAdminAdcOrders">
                            <ul class="nav sub-navbar-nav">
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.adc-orders.index') && !request('status') ? 'active' : '' }}"
                                        href="{{ route('admin.adc-orders.index') }}">All ADC Orders</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.adc-orders.index') && request('status') === 'Pending' ? 'active' : '' }}"
                                        href="{{ route('admin.adc-orders.index', ['status' => 'Pending']) }}">Pending</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.adc-orders.index') && request('status') === 'Processing' ? 'active' : '' }}"
                                        href="{{ route('admin.adc-orders.index', ['status' => 'Processing']) }}">Processing</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.adc-orders.index') && request('status') === 'Dispatched' ? 'active' : '' }}"
                                        href="{{ route('admin.adc-orders.index', ['status' => 'Dispatched']) }}">Dispatched</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.adc-orders.index') && request('status') === 'Received' ? 'active' : '' }}"
                                        href="{{ route('admin.adc-orders.index', ['status' => 'Received']) }}">Received</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.adc-orders.index') && request('status') === 'Delivered' ? 'active' : '' }}"
                                        href="{{ route('admin.adc-orders.index', ['status' => 'Delivered']) }}">Delivered</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.adc-orders.index') && request('status') === 'Cancelled' ? 'active' : '' }}"
                                        href="{{ route('admin.adc-orders.index', ['status' => 'Cancelled']) }}">Cancelled</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    {{-- ========= DEPOT & STORAGE ========= --}}
                    @permission('users.view')
                    <li class="menu-title mt-2">Depot & Storage</li>
                    <li class="nav-item">
                        <a class="nav-link menu-arrow {{ request()->routeIs('admin.racks.*') ? '' : '' }}"
                            href="#sidebarRackManagement" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->routeIs('admin.racks.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarRackManagement">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:widget-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">Rack & Shelf Master</span>
                        </a>
                        <div class="collapse {{ request()->routeIs('admin.racks.list') || request()->routeIs('admin.racks.settings') || request()->routeIs('admin.racks.images') || request()->routeIs('admin.racks.payments') ? 'show' : '' }}"
                            id="sidebarRackManagement">
                            <ul class="nav sub-navbar-nav">
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.racks.list') ? 'active' : '' }}"
                                        href="{{ route('admin.racks.list') }}">Depot Racks Master List</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.racks.settings') ? 'active' : '' }}"
                                        href="{{ route('admin.racks.settings') }}">Rack & Shelf Pricing</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('admin.racks.images') ? 'active' : '' }}"
                                        style="white-space: normal;" href="{{ route('admin.racks.images') }}">
                                        <span>Depot Store Photos</span>
                                        @php
                                            $pendingRackImagesCount = \App\Models\DepotRackImage::where('approval_status', 'pending')->count();
                                        @endphp
                                        @if($pendingRackImagesCount > 0)
                                            <span class="badge bg-danger rounded-pill ms-auto">{{ $pendingRackImagesCount }}</span>
                                        @endif
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endpermission

                    {{-- ========= FINANCE & SETTLEMENTS ========= --}}
                    @permission('users.view')
                    <li class="menu-title mt-2">Finance & Settlements</li>
                    <li class="nav-item">
                        <a class="nav-link menu-arrow {{ request()->routeIs('admin.seller-payments.*') || request()->routeIs('admin.external.ddc.wallet-requests') || request()->routeIs('admin.external.adc.wallet-requests') || request()->routeIs('admin.ama-transactions.*') || request()->routeIs('admin.racks.payments') || request()->routeIs('admin.external.vendors.ddc-requests') || request()->routeIs('admin.external.vendors.ddc-approved') ? '' : '' }}"
                            href="#sidebarPayments" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->routeIs('admin.seller-payments.*') || request()->routeIs('admin.external.ddc.wallet-requests') || request()->routeIs('admin.external.adc.wallet-requests') || request()->routeIs('admin.ama-transactions.*') || request()->routeIs('admin.racks.payments') || request()->routeIs('admin.external.vendors.ddc-requests') || request()->routeIs('admin.external.vendors.ddc-approved') ? 'true' : 'false' }}"
                            aria-controls="sidebarPayments">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">Payment & Wallet Operations</span>
                        </a>
                        <div class="collapse {{ request()->routeIs('admin.seller-payments.*') || request()->routeIs('admin.external.ddc.wallet-requests') || request()->routeIs('admin.external.adc.wallet-requests') || request()->routeIs('admin.ama-transactions.*') || request()->routeIs('admin.racks.payments') || request()->routeIs('admin.external.vendors.ddc-requests') || request()->routeIs('admin.external.vendors.ddc-approved') ? 'show' : '' }}"
                            id="sidebarPayments">
                            <ul class="nav sub-navbar-nav">
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.seller-payments.index') ? 'active' : '' }}"
                                        href="{{ route('admin.seller-payments.index') }}">Seller Payouts & Payments</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.external.vendor.wallet-requests') ? 'active' : '' }}"
                                        href="{{ route('admin.external.vendor.wallet-requests') }}">Seller Wallet Requests</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.external.ddc.wallet-requests') ? 'active' : '' }}"
                                        href="{{ route('admin.external.ddc.wallet-requests') }}">DDC Wallet Requests</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.external.adc.wallet-requests') ? 'active' : '' }}"
                                        href="{{ route('admin.external.adc.wallet-requests') }}">ADC Wallet Requests</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('admin.external.vendors.ddc-requests') ? 'active' : '' }}"
                                        style="white-space: normal;"
                                        href="{{ route('admin.external.vendors.ddc-requests') }}">
                                        <span>DDC Subscription Requests</span>
                                        @if($pendingDdcSubCount > 0)
                                            <span class="badge bg-warning text-dark rounded-pill ms-auto">{{ $pendingDdcSubCount }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.external.vendors.ddc-approved') ? 'active' : '' }}"
                                        style="white-space: normal;"
                                        href="{{ route('admin.external.vendors.ddc-approved') }}">DDC Subscriptions Approved</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link d-flex align-items-center justify-content-between {{ request()->routeIs('admin.racks.payments') ? 'active' : '' }}"
                                        style="white-space: normal;" href="{{ route('admin.racks.payments') }}">
                                        <span>Rack Booking Payments</span>
                                        @php
                                            $pendingRackPaymentsCount = \App\Models\DepotRackBooking::where('status', 'Pending Payment Verification')->count();
                                        @endphp
                                        @if($pendingRackPaymentsCount > 0)
                                            <span class="badge bg-warning text-dark rounded-pill ms-auto">{{ $pendingRackPaymentsCount }}</span>
                                        @endif
                                    </a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.ama-transactions.*') ? 'active' : '' }}"
                                        href="{{ route('admin.ama-transactions.index') }}">AMA Transactions Ledger</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endpermission

                    {{-- ========= ADMINISTRATION & SYSTEM SETTINGS ========= --}}
                    <li class="menu-title mt-2">Administration & Security</li>

                    @php
                        $canViewStaff = auth('admin')->user() && (auth('admin')->user()->is_super_admin === '1' || auth('admin')->user()->hasPermission('staff.view'));
                        $canViewPerms = auth('admin')->user() && (auth('admin')->user()->is_super_admin === '1' || auth('admin')->user()->hasPermission('permissions.view'));
                    @endphp

                    @if($canViewStaff)
                    <li class="nav-item">
                        <a class="nav-link menu-arrow {{ request()->routeIs('admin.staff.*') ? '' : '' }}"
                            href="#sidebarStaff" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->routeIs('admin.staff.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarStaff">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:users-group-two-rounded-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">Staff Management</span>
                        </a>
                        <div class="collapse {{ request()->routeIs('admin.staff.*') ? 'show' : '' }}" id="sidebarStaff">
                            <ul class="nav sub-navbar-nav">
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.staff.index') ? 'active' : '' }}"
                                        href="{{ route('admin.staff.index') }}">All Staff Users</a>
                                </li>
                                @permission('staff.create')
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.staff.create') ? 'active' : '' }}"
                                        href="{{ route('admin.staff.create') }}">Add New Staff User</a>
                                </li>
                                @endpermission
                            </ul>
                        </div>
                    </li>
                    @endif

                    @if($canViewPerms)
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}"
                            href="{{ route('admin.permissions.index') }}">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:shield-check-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">Roles & Permissions</span>
                        </a>
                    </li>
                    @endif

                    @permission('users.manage')
                    <li class="nav-item">
                        <a class="nav-link menu-arrow {{ request()->routeIs('admin.settings.pricing.*') || request()->routeIs('admin.settings.logos.*') || request()->routeIs('admin.settings.bank.*') || request()->routeIs('admin.settings.adpl-office.*') || request()->routeIs('admin.banners.*') ? '' : '' }}"
                            href="#sidebarSystemSettings" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->routeIs('admin.settings.pricing.*') || request()->routeIs('admin.settings.logos.*') || request()->routeIs('admin.settings.bank.*') || request()->routeIs('admin.settings.adpl-office.*') || request()->routeIs('admin.banners.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarSystemSettings">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:settings-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">System & Site Settings</span>
                        </a>
                        <div class="collapse {{ request()->routeIs('admin.settings.pricing.*') || request()->routeIs('admin.settings.logos.*') || request()->routeIs('admin.settings.bank.*') || request()->routeIs('admin.settings.adpl-office.*') || request()->routeIs('admin.banners.*') ? 'show' : '' }}"
                            id="sidebarSystemSettings">
                            <ul class="nav sub-navbar-nav">
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.settings.pricing.*') ? 'active' : '' }}"
                                        href="{{ route('admin.settings.pricing.index') }}">Pricing Margins</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.settings.logos.*') ? 'active' : '' }}"
                                        href="{{ route('admin.settings.logos.index') }}">Logo Settings</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.settings.bank.*') ? 'active' : '' }}"
                                        href="{{ route('admin.settings.bank.index') }}">ADPL Bank & Payment QR</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.settings.aswt-bank.*') ? 'active' : '' }}"
                                        href="{{ route('admin.settings.aswt-bank.index') }}">ASWT Bank & Payment QR</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.settings.adpl-office.*') ? 'active' : '' }}"
                                        href="{{ route('admin.settings.adpl-office.index') }}">ADPL Head Office Details</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.settings.cashback.*') ? 'active' : '' }}"
                                        href="{{ route('admin.settings.cashback.index') }}">Shopping Cashback Settings</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}"
                                        href="{{ route('admin.banners.index') }}">Homepage Banners</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.faq-topics.*') ? 'active' : '' }}"
                                        href="{{ route('admin.faq-topics.index') }}">FAQ Topics</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.faqs.*') ? 'active' : '' }}"
                                        href="{{ route('admin.faqs.index') }}">Manage FAQs</a>
                                </li>
                            </ul>
                        </div>
                    </li>
                    @endpermission

                    <li class="nav-item">
                        <a class="nav-link menu-arrow {{ request()->routeIs('admin.contact-inquiries.*') || request()->routeIs('admin.newsletter-subscribers.*') ? '' : '' }}"
                            href="#sidebarInquiries" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->routeIs('admin.contact-inquiries.*') || request()->routeIs('admin.newsletter-subscribers.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarInquiries">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:letter-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">Contact & Newsletter Inquiries</span>
                        </a>
                        <div class="collapse {{ request()->routeIs('admin.contact-inquiries.*') || request()->routeIs('admin.newsletter-subscribers.*') ? 'show' : '' }}"
                            id="sidebarInquiries">
                            <ul class="nav sub-navbar-nav">
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.contact-inquiries.index') ? 'active' : '' }}"
                                        href="{{ route('admin.contact-inquiries.index') }}">Contact Messages</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('admin.newsletter-subscribers.index') ? 'active' : '' }}"
                                        href="{{ route('admin.newsletter-subscribers.index') }}">Newsletter Subscribers</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    {{-- ========= ACCOUNT ========= --}}
                    <li class="menu-title mt-2">Account</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.profile') ? 'active' : '' }}"
                            href="{{ route('admin.profile') }}">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:user-circle-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">My Profile</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('admin.profile.change-password') ? 'active' : '' }}"
                            href="{{ route('admin.profile.change-password') }}">
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

        <!-- ========== Main Content Start ========== -->
        <div class="page-content">
            <div class="container-fluid pt-3">

                @yield('content')

            </div>

            <!-- ========== Footer Start ========== -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12 text-center">
                            <script>document.write(new Date().getFullYear())</script> &copy; Indian MSME — Admin Panel
                        </div>
                    </div>
                </div>
            </footer>
            <!-- ========== Footer End ========== -->
        </div>
        <!-- ========== Main Content End ========== -->

    </div>
    <!-- END Wrapper -->

    <!-- Vendor Javascript -->
    <script src="{{ asset('admin-assets/js/vendor.js') }}"></script>
    <!-- App Javascript -->
    <script src="{{ asset('admin-assets/js/app.js') }}"></script>

    <!-- SweetAlert & Toastify Local Vendor Assets -->
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
    <script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/toastify/toastify.min.css') }}">
    <script type="text/javascript" src="{{ asset('plugins/toastify/toastify.min.js') }}"></script>

    <!-- jQuery (needed for SweetAlert confirm-action plugin patterns + AJAX) -->
    <script src="{{ asset('plugins/jquery/jquery.min.js') }}"></script>

    <!-- Tom Select: zero-dependency searchable dropdown -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

    <script>
        // Global Tom Select initializer — call on page load and inside modals
        function initSUIDropdown(context) {
            var ctx = context || document;
            var selects = (ctx === document)
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
        });
    </script>

    <!-- SweetAlert and Toastify Setup -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
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
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, proceed!'
                    }).then((result) => {
                        if (result.isConfirmed) {
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

                                if (form.getAttribute('target') === '_blank') {
                                    form.submit();
                                    setTimeout(function () {
                                        Swal.close();
                                    }, 400);
                                } else {
                                    Swal.showLoading();
                                    if (typeof window.submitButtonLoading === 'function') {
                                        window.submitButtonLoading(clickedButton, text);
                                    }
                                    form.submit();
                                }
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
                    confirmButtonColor: '#3085d6',
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
                    'success': 'linear-gradient(to right, #00b09b, #96c93d)',
                    'error': 'linear-gradient(to right, #ff5f6d, #ffc371)',
                    'warning': 'linear-gradient(to right, #f2994a, #f2c94c)',
                    'info': 'linear-gradient(to right, #2193b0, #6dd5ed)'
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
    <!-- Universal Table Action & State Preservation Script -->
    <script src="{{ asset('js/table-action-handler.js') }}"></script>

    <!-- Header Global Menu & Submenu Search Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('adminGlobalSearchInput');
            const searchResults = document.getElementById('adminGlobalSearchResults');
            const searchListGroup = document.getElementById('adminSearchListGroup');
            const searchNoResults = document.getElementById('adminSearchNoResults');

            if (!searchInput || !searchResults) return;

            // 1. Static Core Navigation Index
            const coreNavIndex = [
                { parent: 'Dashboard', title: 'Admin Dashboard Overview', url: "{{ route('admin.dashboard') }}", icon: 'solar:widget-bold-duotone', keywords: 'home main overview metrics statistics analytics' },
                { parent: 'Manage Products', title: 'All Products & Margins Manager', url: "{{ route('admin.products.all') }}", icon: 'solar:box-minimalistic-bold-duotone', keywords: 'products margins admin margin ddc margin mbp price edit pricing list catalog' },
                { parent: 'Manage Products', title: 'New Submissions (Pending Approvals)', url: "{{ route('admin.products.approvals.new') }}", icon: 'solar:shield-check-bold-duotone', keywords: 'new product approval seller upload pending' },
                { parent: 'Manage Products', title: 'Update Submissions (Pending Changes)', url: "{{ route('admin.products.approvals.updated') }}", icon: 'solar:pen-bold-duotone', keywords: 'updated product approval seller edit review' },
                { parent: 'Manage Products', title: 'Approved Product Catalog', url: "{{ route('admin.products.approvals.approved') }}", icon: 'solar:check-circle-bold-duotone', keywords: 'approved products active catalog live' },
                { parent: 'Manage Products', title: 'Rejected Product Catalog', url: "{{ route('admin.products.approvals.rejected') }}", icon: 'solar:close-circle-bold-duotone', keywords: 'rejected products decline reason remarks' },
                { parent: 'External Partners', title: 'DDC Accounts & Profiles', url: "{{ route('admin.external.ddc.index') }}", icon: 'solar:users-group-two-rounded-bold-duotone', keywords: 'ddc district development coordinator partners' },
                { parent: 'External Partners', title: 'ADC Accounts & Profiles', url: "{{ route('admin.external.adc.index') }}", icon: 'solar:user-handshake-bold-duotone', keywords: 'adc area development coordinator partners' },
                { parent: 'External Partners', title: 'Primary Vendors & Sellers List', url: "{{ route('admin.external.vendors.index') }}", icon: 'solar:shop-bold-duotone', keywords: 'vendors sellers imv code manufacturers' },
                { parent: 'Wallets', title: 'DDC Wallet Recharges', url: "{{ route('admin.wallets.ddc') }}", icon: 'solar:wallet-money-bold-duotone', keywords: 'ddc wallet balance recharge credit debit' },
                { parent: 'Wallets', title: 'ADC Wallet Recharges', url: "{{ route('admin.wallets.adc') }}", icon: 'solar:card-transfer-bold-duotone', keywords: 'adc wallet balance recharge credit debit' },
                { parent: 'B2B Orders', title: 'Purchase Orders List', url: "{{ route('admin.purchase-orders.index') }}", icon: 'solar:document-text-bold-duotone', keywords: 'purchase orders b2b po invoice orders' },
                { parent: 'Categories Catalog', title: 'Main Categories', url: "{{ route('admin.categories.index') }}", icon: 'solar:folder-with-files-bold-duotone', keywords: 'categories main category add create' },
                { parent: 'Categories Catalog', title: 'Sub Categories', url: "{{ route('admin.sub-categories.index') }}", icon: 'solar:structure-bold-duotone', keywords: 'sub categories level 2' },
                { parent: 'Categories Catalog', title: 'Child Categories', url: "{{ route('admin.child-categories.index') }}", icon: 'solar:diagram-up-bold-duotone', keywords: 'child categories level 3' },
                { parent: 'Categories Catalog', title: 'Brands Management', url: "{{ route('admin.brands.index') }}", icon: 'solar:tag-bold-duotone', keywords: 'brands manufacturer trademark' },
                { parent: 'Categories Catalog', title: 'Units of Measure', url: "{{ route('admin.units-of-measure.index') }}", icon: 'solar:ruler-cross-bold-duotone', keywords: 'units of measure uom pcs kg meter box' },
                { parent: 'System Settings', title: 'Global System Margins & Pricing', url: "{{ route('admin.settings.pricing.index') }}", icon: 'solar:settings-bold-duotone', keywords: 'settings global margins company admin margin ddc margin mbp fsi' },
                { parent: 'System Settings', title: 'Depot Rack Pricing & Settings', url: "{{ route('admin.racks.settings') }}", icon: 'solar:server-square-bold-duotone', keywords: 'rack settings shelf amount depot pricing' },
                { parent: 'System Settings', title: 'ADPL Bank & Payment QR', url: "{{ route('admin.settings.bank.index') }}", icon: 'solar:bank-bold-duotone', keywords: 'adpl bank details upi qr code hdfc account' },
                { parent: 'System Settings', title: 'ASWT Bank & Payment QR', url: "{{ route('admin.settings.aswt-bank.index') }}", icon: 'solar:bank-bold-duotone', keywords: 'aswt bank details upi qr code account' },
                { parent: 'System Settings', title: 'Logos & Platform Branding', url: "{{ route('admin.settings.logos.index') }}", icon: 'solar:gallery-wide-bold-duotone', keywords: 'logo header logo footer logo favicon branding' },
                { parent: 'System Settings', title: 'Homepage Banners', url: "{{ route('admin.banners.index') }}", icon: 'solar:slider-minimalistic-bold-duotone', keywords: 'banners hero slider homepage image' }
            ];

            // 2. Dynamically Scrape any active sidebar links
            const dynamicLinks = [];
            document.querySelectorAll('.main-nav .sub-nav-link, .main-nav .nav-link').forEach(link => {
                const href = link.getAttribute('href');
                if (href && href !== '#' && !href.startsWith('javascript:')) {
                    const text = link.innerText.trim();
                    if (text && !coreNavIndex.some(i => i.url === href)) {
                        let parentText = 'Navigation';
                        const parentCollapse = link.closest('.collapse');
                        if (parentCollapse) {
                            const parentNav = parentCollapse.previousElementSibling;
                            if (parentNav) parentText = parentNav.innerText.trim();
                        }
                        dynamicLinks.push({
                            parent: parentText,
                            title: text,
                            url: href,
                            icon: 'solar:link-circle-bold-duotone',
                            keywords: text.toLowerCase()
                        });
                    }
                }
            });

            const fullIndex = [...coreNavIndex, ...dynamicLinks];
            let activeIndex = -1;

            // Render function
            function renderResults(query) {
                const q = query.trim().toLowerCase();
                if (!q) {
                    searchResults.classList.add('d-none');
                    searchResults.classList.remove('show');
                    return;
                }

                const matches = fullIndex.filter(item => {
                    return item.title.toLowerCase().includes(q) || 
                           item.parent.toLowerCase().includes(q) || 
                           item.keywords.toLowerCase().includes(q);
                });

                searchListGroup.innerHTML = '';
                activeIndex = -1;

                if (matches.length > 0) {
                    searchNoResults.classList.add('d-none');
                    matches.forEach((item, idx) => {
                        const a = document.createElement('a');
                        a.href = item.url;
                        a.className = `list-group-item list-group-item-action d-flex align-items-center justify-content-between p-2.5 search-item-${idx}`;
                        a.dataset.index = idx;
                        a.innerHTML = `
                            <div class="d-flex align-items-center gap-2.5 overflow-hidden me-2">
                                <div class="rounded bg-primary-subtle text-primary p-1.5 d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="${item.icon}" class="fs-18"></iconify-icon>
                                </div>
                                <div class="d-flex flex-column text-truncate">
                                    <span class="fw-semibold text-slate-800 fs-13 text-truncate">${item.title}</span>
                                    <span class="text-muted fs-11 text-truncate">${item.parent}</span>
                                </div>
                            </div>
                            <iconify-icon icon="solar:alt-arrow-right-linear" class="fs-14 text-muted flex-shrink-0"></iconify-icon>
                        `;
                        searchListGroup.appendChild(a);
                    });
                    searchResults.classList.remove('d-none');
                    searchResults.classList.add('show');
                } else {
                    searchNoResults.classList.remove('d-none');
                    searchResults.classList.remove('d-none');
                    searchResults.classList.add('show');
                }
            }

            searchInput.addEventListener('input', (e) => renderResults(e.target.value));
            searchInput.addEventListener('focus', (e) => {
                if (e.target.value.trim()) renderResults(e.target.value);
            });

            // Keyboard Shortcuts & Arrow Key Navigation
            document.addEventListener('keydown', function (e) {
                // Focus search with Ctrl+K or Cmd+K
                if ((e.ctrlKey || e.metaKey) && e.key.toLowerCase() === 'k') {
                    e.preventDefault();
                    searchInput.focus();
                    searchInput.select();
                }

                if (!searchResults.classList.contains('show')) return;

                const items = searchListGroup.querySelectorAll('.list-group-item');
                if (items.length === 0) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    activeIndex = (activeIndex + 1) % items.length;
                    highlightItem(items);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    activeIndex = (activeIndex - 1 + items.length) % items.length;
                    highlightItem(items);
                } else if (e.key === 'Enter') {
                    if (activeIndex >= 0 && items[activeIndex]) {
                        e.preventDefault();
                        window.location.href = items[activeIndex].href;
                    }
                } else if (e.key === 'Escape') {
                    searchResults.classList.add('d-none');
                    searchResults.classList.remove('show');
                    searchInput.blur();
                }
            });

            function highlightItem(items) {
                items.forEach((item, idx) => {
                    if (idx === activeIndex) {
                        item.classList.add('active', 'bg-light', 'border-start', 'border-primary', 'border-3');
                        item.scrollIntoView({ block: 'nearest' });
                    } else {
                        item.classList.remove('active', 'bg-light', 'border-start', 'border-primary', 'border-3');
                    }
                });
            }

            // Close on outside click
            document.addEventListener('click', function (e) {
                if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                    searchResults.classList.add('d-none');
                    searchResults.classList.remove('show');
                }
            });
        });

        // Global Bulk Import Excel Export Helpers
    </script>
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

            // Fallback UTF-8 BOM CSV for Microsoft Excel
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

        // Legacy CSV aliases for backwards compatibility
        window.exportImportLogsToCsv = function(filename, logArray, onlyErrors) {
            window.exportImportLogsToExcel(filename, logArray, onlyErrors);
        };
        window.exportModalTableToCsv = function(modalId, filename) {
            window.exportModalTableToExcel(modalId, filename, true);
        };
    </script>
</body>

</html>