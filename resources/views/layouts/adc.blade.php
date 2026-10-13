<!DOCTYPE html>
<html lang="en">

<head>
    <!-- Title Meta -->
    <meta charset="utf-8" />
    <title>@yield('title', 'Dashboard') | ADC Panel — Indian MSME</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Indian MSME ADC Panel" />
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
            box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25) !important;
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

        /* ── Browse Website Button Subtle Light-Blue Hover Styling ── */
        .btn-browse-web {
            background: #eff6ff !important;
            border: 1px solid #bfdbfe !important;
            color: #2563eb !important;
            transition: all 0.2s ease-in-out !important;
        }

        .btn-browse-web:hover,
        .btn-browse-web:focus,
        .btn-browse-web:active {
            background: #dbeafe !important;
            border-color: #3b82f6 !important;
            color: #1d4ed8 !important;
            box-shadow: 0 2px 8px rgba(59, 130, 246, 0.2) !important;
        }

        .btn-browse-web iconify-icon,
        .btn-browse-web span {
            color: #2563eb !important;
            transition: color 0.2s ease-in-out !important;
        }

        .btn-browse-web:hover iconify-icon,
        .btn-browse-web:hover span {
            color: #1d4ed8 !important;
        }

        :root {
            --bs-primary: #3b82f6 !important;
            --bs-primary-rgb: 59, 130, 246 !important;
            --bs-link-color: #3b82f6 !important;
            --bs-link-hover-color: #1d4ed8 !important;

            /* Custom ADC Theme Colors - Premium Blue */
            --adm-primary: #3b82f6;
            --adm-primary-lt: #60a5fa;
            --adm-primary-bg: rgba(59, 130, 246, 0.12);
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
            background: rgba(59, 130, 246, 0.05) !important;
        }

        .main-nav .navbar-nav .nav-link:hover .nav-icon {
            color: var(--adm-primary) !important;
        }

        /* Active nav item */
        .main-nav .navbar-nav .nav-link.active,
        .main-nav .navbar-nav .nav-link[aria-expanded="true"] {
            color: var(--adm-primary) !important;
            background: rgba(59, 130, 246, 0.08) !important;
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

        iconify-icon {
            display: inline-block !important;
            vertical-align: middle !important;
            width: 1em;
            height: 1em;
        }

        iconify-icon::part(icon) {
            width: 100% !important;
            height: 100% !important;
        }

        .sub-navbar-nav .sub-nav-link:hover {
            color: var(--adm-primary) !important;
            background: rgba(59, 130, 246, 0.04) !important;
            padding-left: 22px;
        }

        .sub-navbar-nav .sub-nav-link:hover::before {
            background: var(--adm-primary);
            transform: translateY(0) scale(1.2) !important;
        }

        .sub-navbar-nav .sub-nav-link.active {
            color: var(--adm-primary) !important;
            font-weight: 600;
            background: rgba(59, 130, 246, 0.06) !important;
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
            background: rgba(96, 165, 250, 0.2) !important;
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
            background: rgba(96, 165, 250, 0.12) !important;
        }

        /* ── Buttons ── */
        .btn-primary {
            background: var(--adm-primary) !important;
            border-color: var(--adm-primary) !important;
            font-weight: 500 !important;
            letter-spacing: .2px !important;
        }

        .btn-primary:hover {
            background: #1d4ed8 !important;
            border-color: #1d4ed8 !important;
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
            background-color: rgba(59, 130, 246, 0.15) !important;
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
            background-color: rgba(59, 130, 246, 0.05) !important;
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

        /* ── Dark Mode Text & Container Overrides ── */
        [data-bs-theme="dark"] .text-dark,
        [data-bs-theme="dark"] .text-slate-800,
        [data-bs-theme="dark"] .text-slate-900,
        [data-bs-theme="dark"] .card-title {
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .text-slate-700,
        [data-bs-theme="dark"] .text-slate-600,
        [data-bs-theme="dark"] .text-muted {
            color: #94a3b8 !important;
        }

        [data-bs-theme="dark"] .bg-white {
            background-color: #1e293b !important;
        }

        [data-bs-theme="dark"] .bg-light,
        [data-bs-theme="dark"] .bg-slate-50 {
            background-color: #0f172a !important;
            border-color: #334155 !important;
        }

        [data-bs-theme="dark"] .border-bottom,
        [data-bs-theme="dark"] .border-light,
        [data-bs-theme="dark"] .border {
            border-color: #334155 !important;
        }

        [data-bs-theme="dark"] .detail-label {
            color: #94a3b8 !important;
        }

        [data-bs-theme="dark"] .detail-value {
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .detail-row {
            border-bottom-color: #334155 !important;
        }

        [data-bs-theme="dark"] .form-label {
            color: #e2e8f0 !important;
        }

        [data-bs-theme="dark"] .form-control {
            background-color: #0f172a !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        [data-bs-theme="dark"] .form-control:focus {
            background-color: #0f172a !important;
            border-color: #3b82f6 !important;
            color: #ffffff !important;
        }

        [data-bs-theme="dark"] .btn-outline-secondary {
            border-color: #334155 !important;
            color: #94a3b8 !important;
        }

        [data-bs-theme="dark"] .btn-outline-secondary:hover {
            background-color: #334155 !important;
            color: #ffffff !important;
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
            background: rgba(59, 130, 246, .03) !important;
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
                        <div class="topbar-item d-flex align-items-center gap-2">
                            <h4 class="fw-bold topbar-button pe-none text-uppercase mb-0 d-inline-flex align-items-center me-2">
                                <span class="badge bg-primary-subtle text-primary px-2 me-1">ADC</span>
                                @yield('page-title', 'Dashboard')
                            </h4>
                            <!-- Premium Yellow / Amber Card POS Button on Left -->
                            <a href="{{ route('adc.pos.retail') }}"
                                class="btn btn-sm px-3 py-1 rounded-pill fw-extrabold d-inline-flex align-items-center gap-1.5 shadow-sm me-2"
                                style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%) !important; color: #ffffff !important; border: 1px solid rgba(255,255,255,0.4) !important; font-size: 12px; text-shadow: 0 1px 2px rgba(0,0,0,0.25);"
                                title="Open Retail POS Terminal">
                                <iconify-icon icon="solar:shop-2-bold-duotone"
                                    class="fs-16 align-middle" style="color: #ffffff !important;"></iconify-icon>
                                <span>Yellow Card POS</span>
                            </a>

                            <!-- Premium Green Card POS External Button -->
                            @php
                                $greenCardAdcProfile = Auth::guard('adc')->user()?->adcProfile;
                                $greenCardAbhhyamVendorId = $greenCardAdcProfile?->abhhyam_vendor_id;
                            @endphp
                            @if(!empty($greenCardAbhhyamVendorId) && (int)$greenCardAbhhyamVendorId > 0)
                                <a href="https://abhhyam.com/vendors/visit-asc-vendor/{{ base64_encode($greenCardAbhhyamVendorId) }}" target="_blank"
                                    class="btn btn-sm px-3 py-1 rounded-pill fw-extrabold d-inline-flex align-items-center gap-1.5 shadow-sm"
                                    style="background: linear-gradient(135deg, #10b981 0%, #047857 100%) !important; color: #ffffff !important; border: 1px solid rgba(255,255,255,0.4) !important; font-size: 12px; text-shadow: 0 1px 2px rgba(0,0,0,0.25);"
                                    title="Visit Abhhyam Green Card POS Terminal">
                                    <iconify-icon icon="solar:card-recive-bold-duotone"
                                        class="fs-16 align-middle" style="color: #ffffff !important;"></iconify-icon>
                                    <span>Green Card POS</span>
                                </a>
                            @endif
                        </div>
                    </div>

                    <div class="d-flex align-items-center gap-1">
                        <!-- Shopping / Visit Main Website Button -->
                        <div class="topbar-item me-1">
                            <a href="{{ main_website_url() }}" target="_blank"
                                class="btn btn-sm btn-browse-web d-flex align-items-center gap-1.5 rounded-pill px-3 py-1.5 shadow-sm"
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
                                $adcId = auth('adc')->id();
                                $adcNotifications = \App\Models\Notification::where('recipient_type', 'adc')
                                    ->where('recipient_id', $adcId)
                                    ->where('is_read', false)
                                    ->orderBy('created_at', 'desc')
                                    ->take(5)->get();
                                $unreadCount = \App\Models\Notification::where('recipient_type', 'adc')
                                    ->where('recipient_id', $adcId)
                                    ->where('is_read', false)->count();
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
                                </div>
                                <div class="list-group list-group-flush">
                                    @forelse($adcNotifications as $notif)
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

                        <!-- User Dropdown with Profile Image & Code Directly Below -->
                        @php
                            $currentAdcUser = auth('adc')->user();
                            $adcCode = $currentAdcUser?->adc_code ?? $currentAdcUser?->adcProfile?->adc_code;
                        @endphp
                        <div class="dropdown topbar-item">
                            <a type="button" class="topbar-button py-1 px-1.5" id="page-header-user-dropdown"
                                data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="{{ auth('adc')->user()->name }} ({{ $adcCode }})">
                                <span class="d-flex flex-column align-items-center justify-content-center">
                                    <img class="rounded-circle border border-primary border-opacity-25 shadow-sm" width="30" height="30"
                                        style="object-fit: cover; aspect-ratio: 1 / 1;"
                                        src="{{ auth('adc')->user()->avatar_url }}" alt="avatar">
                                    @if($adcCode)
                                        <span class="fs-11 fw-semibold text-slate-700 mt-0.5" style="line-height: 1.1;">
                                            {{ $adcCode }}
                                        </span>
                                    @endif
                                </span>
                            </a>
                            <div class="dropdown-menu dropdown-menu-end">
                                <div class="px-3 py-2 border-bottom bg-light bg-opacity-50">
                                    <h6 class="dropdown-header p-0 mb-1 fw-bold text-dark fs-13">Welcome {{ auth('adc')->user()->name }}!</h6>
                                    @if($adcCode)
                                        <span class="text-slate-600 fw-medium fs-12 d-block mt-0.5">{{ $adcCode }}</span>
                                    @endif
                                </div>
                                <a class="dropdown-item mt-1" href="{{ route('adc.profile') }}">
                                    <i class="bx bx-user-circle text-muted fs-18 align-middle me-1"></i>
                                    <span class="align-middle">Profile</span>
                                </a>
                                <a class="dropdown-item" href="{{ route('adc.profile.change-password') }}">
                                    <i class="bx bx-lock text-muted fs-18 align-middle me-1"></i>
                                    <span class="align-middle">Change Password</span>
                                </a>
                                <div class="dropdown-divider my-1"></div>
                                <form action="{{ route('adc.logout') }}" method="POST">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger">
                                        <i class="bx bx-log-out fs-18 align-middle me-1"></i>
                                        <span class="align-middle">Logout</span>
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- App Search with Live Menu & Submenu Auto-Suggestion -->
                        <div class="app-search d-none d-md-block ms-2 position-relative" id="globalMenuSearchContainer" style="min-width: 260px;">
                            <div class="position-relative">
                                <input type="search" class="form-control" id="globalMenuSearchInput" placeholder="Search menu, pages & reports..." autocomplete="off">
                                <iconify-icon icon="solar:magnifer-linear" class="search-widget-icon"></iconify-icon>
                            </div>
                            <!-- Auto-Suggestion Dropdown -->
                            <div class="dropdown-menu dropdown-menu-end p-0 shadow-lg border-0 rounded-3 position-absolute end-0 mt-1 d-none" id="globalMenuSearchDropdown" style="max-height: 380px; overflow-y: auto; z-index: 1060; background: #ffffff; width: 350px; max-width: calc(100vw - 20px); left: auto !important; right: 0 !important;">
                                <div class="p-2 border-bottom bg-light d-flex align-items-center justify-content-between">
                                    <span class="fs-11 fw-bold text-uppercase text-muted">Menu Navigation</span>
                                    <span class="badge bg-primary-subtle text-primary fs-10" id="globalSearchMatchCount">0 matches</span>
                                </div>
                                <div class="list-group list-group-flush py-1" id="globalMenuSearchResults">
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
                <a href="{{ route('adc.dashboard') }}" class="logo-dark">
                    <img src="{{ \App\Models\SystemSetting::getVal('website_header_logo', \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png'))) }}"
                        class="logo-sm" alt="logo sm">
                    <img src="{{ \App\Models\SystemSetting::getVal('website_header_logo', \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png'))) }}"
                        class="logo-lg" alt="logo dark">
                </a>
                <a href="{{ route('adc.dashboard') }}" class="logo-light">
                    <img src="{{ \App\Models\SystemSetting::getVal('website_header_logo', \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png'))) }}"
                        class="logo-sm" alt="logo sm">
                    <img src="{{ \App\Models\SystemSetting::getVal('website_header_logo', \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png'))) }}"
                        class="logo-lg" alt="logo light">
                </a>
            </div>

            <div class="scrollbar" data-simplebar>
                <ul class="navbar-nav" id="navbar-nav">

                    {{-- ========= MAIN MENU ========= --}}
                    <li class="menu-title">Main Menu</li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('adc.dashboard') ? 'active' : '' }}"
                            href="{{ route('adc.dashboard') }}">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:widget-5-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('adc.orders.*') ? 'active' : '' }}"
                            href="{{ route('adc.orders.index') }}">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:bag-check-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">My Orders</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('adc.inventory.*') ? 'active' : '' }}"
                            href="{{ route('adc.inventory.index') }}">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:box-minimalistic-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">Stock & Inventory</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('adc.racks.*') ? 'active' : '' }}"
                            href="{{ route('adc.racks.index') }}">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:widget-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">Rack & Shelf Management</span>
                        </a>
                    </li>

                    {{-- ========= CLAIMS & ACCOUNTS ========= --}}
                    <li class="menu-title mt-2">Claims & Accounts</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('adc.debit-notes.*') ? 'active' : '' }}"
                            href="{{ route('adc.debit-notes.index') }}">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:document-text-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">Debit Notes</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('adc.credit-notes.*') ? 'active' : '' }}"
                            href="{{ route('adc.credit-notes.index') }}">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:bill-check-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">Credit Notes</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('adc.excess-invoices.*') ? 'active' : '' }}"
                            href="{{ route('adc.excess-invoices.index') }}">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:card-send-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">Excess Stock Invoices</span>
                        </a>
                    </li>

                    {{-- ========= POS & RETAIL COUNTER ========= --}}
                    <li class="menu-title mt-2">POS & Retail Counter</li>
                    <li class="nav-item">
                        <a class="nav-link menu-arrow {{ request()->routeIs('adc.pos.*') ? '' : '' }}"
                            href="#sidebarAdcPosBilling" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->routeIs('adc.pos.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarAdcPosBilling">
                            <span class="nav-icon"><iconify-icon icon="solar:shop-2-bold-duotone"></iconify-icon></span>
                            <span class="nav-text">POS Retail Billing</span>
                        </a>
                        <div class="collapse {{ request()->routeIs('adc.pos.*') ? 'show' : '' }}"
                            id="sidebarAdcPosBilling">
                            <ul class="nav sub-navbar-nav">
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('adc.pos.retail') ? 'active' : '' }}"
                                        href="{{ route('adc.pos.retail') }}">Retail POS Counter</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('adc.pos.customers') ? 'active' : '' }}"
                                        href="{{ route('adc.pos.customers') }}">POS Customers & Credit (Udhaar)</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('adc.pos.orders') ? 'active' : '' }}"
                                        href="{{ route('adc.pos.orders') }}">Sales History</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('adc.pos.returns.*') ? 'active' : '' }}"
                                        href="{{ route('adc.pos.returns.index') }}">Sales Returns</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    {{-- ========= REGIONAL PRODUCTS & PURCHASES ========= --}}
                    <li class="menu-title mt-2">Regional Sourcing</li>
                    <li class="nav-item">
                        <a class="nav-link menu-arrow {{ request()->routeIs('adc.local-products.*') || request()->routeIs('adc.local-purchases.*') ? '' : '' }}"
                            href="#sidebarAdcLocalSourcing" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->routeIs('adc.local-products.*') || request()->routeIs('adc.local-purchases.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarAdcLocalSourcing">
                            <span class="nav-icon"><iconify-icon icon="solar:shop-bold-duotone"></iconify-icon></span>
                            <span class="nav-text">Regional Products & Purchases</span>
                        </a>
                        <div class="collapse {{ request()->routeIs('adc.local-products.*') || request()->routeIs('adc.local-purchases.*') ? 'show' : '' }}"
                            id="sidebarAdcLocalSourcing">
                            <ul class="nav sub-navbar-nav">
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('adc.local-products.index') || request()->routeIs('adc.local-products.create') || request()->routeIs('adc.local-products.edit') ? 'active' : '' }}"
                                        href="{{ route('adc.local-products.index') }}">Regional Product Master</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('adc.local-purchases.create') ? 'active' : '' }}"
                                        href="{{ route('adc.local-purchases.create') }}">Stock In / Regional Purchase</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('adc.local-purchases.index') || request()->routeIs('adc.local-purchases.show') ? 'active' : '' }}"
                                        href="{{ route('adc.local-purchases.index') }}">Regional Purchase History</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('adc.local-products.comparison') ? 'active' : '' }}"
                                        href="{{ route('adc.local-products.comparison') }}">Regional Price Comparison</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    {{-- ========= REPORTS & ANALYTICS ========= --}}
                    <li class="menu-title mt-2">Reports & Analytics</li>
                    <li class="nav-item">
                        <a class="nav-link menu-arrow {{ request()->routeIs('adc.reports.*') ? '' : '' }}"
                            href="#sidebarAdcReports" data-bs-toggle="collapse" role="button"
                            aria-expanded="{{ request()->routeIs('adc.reports.*') ? 'true' : 'false' }}"
                            aria-controls="sidebarAdcReports">
                            <span class="nav-icon"><iconify-icon icon="solar:chart-2-bold-duotone"></iconify-icon></span>
                            <span class="nav-text">Reports</span>
                        </a>
                        <div class="collapse {{ request()->routeIs('adc.reports.*') ? 'show' : '' }}"
                            id="sidebarAdcReports">
                            <ul class="nav sub-navbar-nav">
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('adc.reports.sales') ? 'active' : '' }}"
                                        href="{{ route('adc.reports.sales') }}">Sale Report</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('adc.reports.hsn-sales') ? 'active' : '' }}"
                                        href="{{ route('adc.reports.hsn-sales') }}">HSN-wise Sales Report</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('adc.reports.purchases') ? 'active' : '' }}"
                                        href="{{ route('adc.reports.purchases') }}">Purchase Report</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('adc.reports.sales-returns') ? 'active' : '' }}"
                                        href="{{ route('adc.reports.sales-returns') }}">Sale Return Report</a>
                                </li>
                                <li class="sub-nav-item">
                                    <a class="sub-nav-link {{ request()->routeIs('adc.reports.pnl') ? 'active' : '' }}"
                                        href="{{ route('adc.reports.pnl') }}">P&L Report</a>
                                </li>
                            </ul>
                        </div>
                    </li>

                    {{-- ========= FINANCE & WALLET ========= --}}
                    @permission('wallet.view')
                    <li class="menu-title mt-2">Finance & Wallet</li>
                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('adc.wallet.*') ? 'active' : '' }}"
                            href="{{ route('adc.wallet.index') }}">
                            <span class="nav-icon"><iconify-icon icon="solar:wallet-bold-duotone"></iconify-icon></span>
                            <span class="nav-text">Wallet Management</span>
                        </a>
                    </li>
                    @endpermission

                    {{-- ========= ACCOUNT & SETTINGS ========= --}}
                    <li class="menu-title mt-2">Account & Settings</li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('adc.profile') ? 'active' : '' }}"
                            href="{{ route('adc.profile') }}">
                            <span class="nav-icon">
                                <iconify-icon icon="solar:user-circle-bold-duotone"></iconify-icon>
                            </span>
                            <span class="nav-text">My Profile</span>
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link {{ request()->routeIs('adc.profile.change-password') ? 'active' : '' }}"
                            href="{{ route('adc.profile.change-password') }}">
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
            <div class="container-fluid pt-3">
                @php
                    $globalAdcUser = Auth::guard('adc')->user();
                    $globalAdcProfile = $globalAdcUser?->adcProfile;
                    $globalWalletBal = (float) ($globalAdcProfile?->wallet_balance ?? 0);
                    $globalIsActive = $globalAdcUser?->is_active;
                @endphp

                @if($globalAdcUser)
                    @if($globalIsActive == 0 || $globalIsActive === '0' || ($globalAdcProfile?->first_sale_at && $globalWalletBal <= 0))
                        <div class="alert alert-danger border-0 shadow-sm d-flex align-items-center justify-content-between mb-3 rounded-3 py-2.5 px-3" style="background: #fef2f2; border-left: 4px solid #ef4444 !important; color: #991b1b;">
                            <div class="d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:close-circle-bold-duotone" class="text-danger fs-22"></iconify-icon>
                                <div>
                                    <strong class="text-danger">Account Deactivated!</strong>
                                    <span class="ms-1 fs-13" style="color: #7f1d1d;">
                                        {{ $globalAdcProfile?->deactivation_reason ?: 'Your wallet balance has reached ₹0.00. Daily platform fee could not be deducted, so sales have been temporarily deactivated.' }}
                                    </span>
                                </div>
                            </div>
                            <a href="{{ route('adc.wallet.index') }}" class="btn btn-danger btn-sm fw-semibold rounded-pill px-3 ms-2 text-nowrap">
                                Recharge Wallet Now
                            </a>
                        </div>
                    @elseif($globalAdcProfile?->first_sale_at && $globalWalletBal <= 50)
                        <div class="alert alert-warning border-0 shadow-sm d-flex align-items-center justify-content-between mb-3 rounded-3 py-2 px-3" style="background: #fff8e6; border-left: 4px solid #f59e0b !important; color: #78350f;">
                            <div class="d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:danger-triangle-bold-duotone" class="text-warning fs-20"></iconify-icon>
                                <div>
                                    <strong class="text-warning-emphasis">Wallet Balance Low!</strong>
                                    <span class="ms-1 fs-13">
                                        Your wallet balance is low (<strong>₹{{ number_format($globalWalletBal, 2) }}</strong>). Daily platform fee of ₹10.00 will be deducted. Please recharge your wallet to avoid sales deactivation.
                                    </span>
                                </div>
                            </div>
                            <a href="{{ route('adc.wallet.index') }}" class="btn btn-warning btn-sm fw-semibold rounded-pill px-3 ms-2 text-nowrap">
                                Recharge Wallet
                            </a>
                        </div>
                    @endif
                @endif

                @yield('content')
            </div>

            <!-- ========== Footer Start ========== -->
            <footer class="footer">
                <div class="container-fluid">
                    <div class="row">
                        <div class="col-12 text-center">
                            <script>document.write(new Date().getFullYear())</script> &copy; Indian MSME — ADC Panel
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

    <!-- SweetAlert & Toastify CDNs -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/toastify-js/src/toastify.min.css">
    <script type="text/javascript" src="https://cdn.jsdelivr.net/npm/toastify-js"></script>

    <!-- TomSelect JS -->
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.3.1/dist/js/tom-select.complete.min.js"></script>

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
                        confirmButtonColor: '#3b82f6',
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
                    confirmButtonColor: '#3b82f6',
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
                    'success': 'linear-gradient(to right, #3b82f6, #60a5fa)',
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

    <!-- Global Header Menu & Submenu Live Search Script -->
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const searchInput = document.getElementById('globalMenuSearchInput');
            const searchDropdown = document.getElementById('globalMenuSearchDropdown');
            const searchResults = document.getElementById('globalMenuSearchResults');
            const matchCountBadge = document.getElementById('globalSearchMatchCount');
            const searchContainer = document.getElementById('globalMenuSearchContainer');

            if (!searchInput || !searchDropdown || !searchResults) return;

            let menuIndex = [];

            function buildMenuIndex() {
                menuIndex = [];

                // 1. Index dynamically from sidebar DOM
                const sidebarLinks = document.querySelectorAll('#navbar-nav a[href], .main-nav a[href]');
                sidebarLinks.forEach(link => {
                    const href = link.getAttribute('href');
                    if (!href || href === '#' || href.startsWith('#') || href.includes('javascript:')) return;

                    let titleText = link.querySelector('.nav-text')?.textContent?.trim() || link.textContent?.trim();
                    if (!titleText || titleText.length < 2) return;
                    
                    titleText = titleText.replace(/\s+/g, ' ');

                    // Extract Icon if present
                    const iconEl = link.querySelector('iconify-icon');
                    const iconName = iconEl ? iconEl.getAttribute('icon') : 'solar:link-circle-bold-duotone';

                    // Find parent category / section title
                    let parentCategory = 'Main Menu';
                    let currentLi = link.closest('li');

                    // Check if inside collapse submenu
                    const parentCollapse = link.closest('.collapse');
                    if (parentCollapse) {
                        const parentTrigger = document.querySelector(`a[href="#${parentCollapse.id}"]`);
                        if (parentTrigger) {
                            const parentTitle = parentTrigger.querySelector('.nav-text')?.textContent?.trim();
                            if (parentTitle) parentCategory = parentTitle;
                        }
                    } else if (currentLi) {
                        // Look for preceding menu-title
                        let prevEl = currentLi.previousElementSibling;
                        while (prevEl) {
                            if (prevEl.classList.contains('menu-title')) {
                                parentCategory = prevEl.textContent.trim();
                                break;
                            }
                            prevEl = prevEl.previousElementSibling;
                        }
                    }

                    if (!menuIndex.some(m => m.url === href || m.title.toLowerCase() === titleText.toLowerCase())) {
                        menuIndex.push({
                            title: titleText,
                            url: href,
                            icon: iconName,
                            category: parentCategory
                        });
                    }
                });

                // 2. Add complete panel routes & submenus index
                const extraRoutes = [
                    // Main Menu / General
                    { title: 'Dashboard', url: '{{ route("adc.dashboard") }}', icon: 'solar:widget-5-bold-duotone', category: 'Main Menu' },
                    { title: 'My Orders', url: '{{ route("adc.orders.index") }}', icon: 'solar:bag-check-bold-duotone', category: 'Main Menu' },
                    { title: 'Stock & Inventory', url: '{{ route("adc.inventory.index") }}', icon: 'solar:box-minimalistic-bold-duotone', category: 'Main Menu' },
                    { title: 'Rack & Shelf Management', url: '{{ route("adc.racks.index") }}', icon: 'solar:widget-bold-duotone', category: 'Main Menu' },

                    // POS & Retail Counter
                    { title: 'Retail POS Counter', url: '{{ route("adc.pos.retail") }}', icon: 'solar:shop-2-bold-duotone', category: 'POS & Retail Counter' },
                    { title: 'POS Customers & Credit (Udhaar)', url: '{{ route("adc.pos.customers") }}', icon: 'solar:users-group-two-rounded-bold-duotone', category: 'POS & Retail Counter' },
                    { title: 'Sales History', url: '{{ route("adc.pos.orders") }}', icon: 'solar:bill-list-bold-duotone', category: 'POS & Retail Counter' },
                    { title: 'Sales Returns', url: '{{ route("adc.pos.returns.index") }}', icon: 'solar:restart-bold-duotone', category: 'POS & Retail Counter' },

                    // Regional Sourcing
                    { title: 'Regional Product Master', url: '{{ route("adc.local-products.index") }}', icon: 'solar:shop-bold-duotone', category: 'Regional Sourcing' },
                    { title: 'Stock In / Regional Purchase', url: '{{ route("adc.local-purchases.create") }}', icon: 'solar:box-minimalistic-bold-duotone', category: 'Regional Sourcing' },
                    { title: 'Regional Purchase History', url: '{{ route("adc.local-purchases.index") }}', icon: 'solar:bill-list-bold-duotone', category: 'Regional Sourcing' },
                    { title: 'Regional Price Comparison', url: '{{ route("adc.local-products.comparison") }}', icon: 'solar:graph-bold-duotone', category: 'Regional Sourcing' },

                    // Reports & Analytics
                    { title: 'Sale Report', url: '{{ route("adc.reports.sales") }}', icon: 'solar:document-text-bold-duotone', category: 'Reports & Analytics' },
                    { title: 'HSN-wise Sales Report', url: '{{ route("adc.reports.hsn-sales") }}', icon: 'solar:document-text-bold-duotone', category: 'Reports & Analytics' },
                    { title: 'Purchase Report', url: '{{ route("adc.reports.purchases") }}', icon: 'solar:document-text-bold-duotone', category: 'Reports & Analytics' },
                    { title: 'Sale Return Report', url: '{{ route("adc.reports.sales-returns") }}', icon: 'solar:document-text-bold-duotone', category: 'Reports & Analytics' },
                    { title: 'P&L Report (Profit & Loss)', url: '{{ route("adc.reports.pnl") }}', icon: 'solar:chart-2-bold-duotone', category: 'Reports & Analytics' },
                    { title: 'Invoice-wise Report', url: '{{ route("adc.reports.invoice-wise") }}', icon: 'solar:document-bold-duotone', category: 'Reports & Analytics' },
                    { title: 'Product-wise Report', url: '{{ route("adc.reports.product-wise") }}', icon: 'solar:box-bold-duotone', category: 'Reports & Analytics' },
                    { title: 'Local Purchase Report', url: '{{ route("adc.reports.local-purchases") }}', icon: 'solar:cart-bold-duotone', category: 'Reports & Analytics' },

                    // Finance & Wallet
                    @permission('wallet.view')
                    { title: 'Wallet Management', url: '{{ route("adc.wallet.index") }}', icon: 'solar:wallet-bold-duotone', category: 'Finance & Wallet' },
                    @endpermission

                    // Account & Settings
                    { title: 'My Profile', url: '{{ route("adc.profile") }}', icon: 'solar:user-circle-bold-duotone', category: 'Account & Settings' },
                    { title: 'Change Password', url: '{{ route("adc.profile.change-password") }}', icon: 'solar:lock-bold-duotone', category: 'Account & Settings' }
                ];

                extraRoutes.forEach(routeItem => {
                    if (!menuIndex.some(m => m.url === routeItem.url || m.title.toLowerCase() === routeItem.title.toLowerCase())) {
                        menuIndex.push(routeItem);
                    }
                });
            }

            buildMenuIndex();

            function showDropdown() {
                searchDropdown.classList.remove('d-none');
                searchDropdown.classList.add('show');
                searchDropdown.style.setProperty('display', 'block', 'important');
            }

            function hideDropdown() {
                searchDropdown.classList.add('d-none');
                searchDropdown.classList.remove('show');
                searchDropdown.style.setProperty('display', 'none', 'important');
            }

            let activeFocusIndex = -1;

            searchInput.addEventListener('focus', function () {
                if (menuIndex.length === 0) buildMenuIndex();
                const query = searchInput.value.trim();
                if (query.length > 0) {
                    performSearch(query);
                }
            });

            searchInput.addEventListener('input', function () {
                if (menuIndex.length === 0) buildMenuIndex();
                const query = searchInput.value.trim();
                if (query.length === 0) {
                    hideDropdown();
                    searchResults.innerHTML = '';
                    activeFocusIndex = -1;
                    return;
                }
                performSearch(query);
            });

            function performSearch(query) {
                const q = query.toLowerCase();
                const matches = menuIndex.filter(item => 
                    item.title.toLowerCase().includes(q) || 
                    item.category.toLowerCase().includes(q)
                );

                searchResults.innerHTML = '';
                activeFocusIndex = -1;

                if (matches.length > 0) {
                    matchCountBadge.textContent = `${matches.length} found`;
                    matches.forEach((item, index) => {
                        const a = document.createElement('a');
                        a.href = item.url;
                        a.className = 'list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2 px-3 search-result-item border-0';
                        a.setAttribute('data-index', index);
                        a.style.cursor = 'pointer';

                        // Highlight query match in title
                        const escapedQuery = query.replace(/[-\/\\^$*+?.()|[\]{}]/g, '\\$&');
                        const regex = new RegExp(`(${escapedQuery})`, 'gi');
                        const highlightedTitle = item.title.replace(regex, '<mark class="bg-warning-subtle text-dark p-0 fw-bold">$1</mark>');

                        a.innerHTML = `
                            <div class="d-flex align-items-center gap-2 text-truncate me-2">
                                <iconify-icon icon="${item.icon}" class="fs-18 text-primary flex-shrink-0 align-middle"></iconify-icon>
                                <span class="fw-semibold text-slate-800 fs-13 text-truncate">${highlightedTitle}</span>
                            </div>
                            <span class="badge bg-light text-muted border fs-10 flex-shrink-0">${item.category}</span>
                        `;

                        a.addEventListener('click', function (evt) {
                            evt.preventDefault();
                            window.location.href = item.url;
                        });

                        searchResults.appendChild(a);
                    });
                    showDropdown();
                } else {
                    matchCountBadge.textContent = '0 found';
                    searchResults.innerHTML = `
                        <div class="p-3 text-center text-muted fs-12">
                            <iconify-icon icon="solar:magnifer-linear" class="fs-24 text-muted mb-1 d-block"></iconify-icon>
                            No matching menu or page found for "<strong>${escapeHtml(query)}</strong>"
                        </div>
                    `;
                    showDropdown();
                }
            }

            function escapeHtml(str) {
                return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
            }

            // Keyboard navigation (ArrowDown, ArrowUp, Enter, Escape)
            searchInput.addEventListener('keydown', function (e) {
                const items = searchResults.querySelectorAll('.search-result-item');
                if (items.length === 0) return;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    activeFocusIndex = (activeFocusIndex + 1) % items.length;
                    updateItemHighlight(items);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    activeFocusIndex = (activeFocusIndex - 1 + items.length) % items.length;
                    updateItemHighlight(items);
                } else if (e.key === 'Enter') {
                    if (activeFocusIndex >= 0 && items[activeFocusIndex]) {
                        e.preventDefault();
                        items[activeFocusIndex].click();
                    }
                } else if (e.key === 'Escape') {
                    hideDropdown();
                    searchInput.blur();
                }
            });

            function updateItemHighlight(items) {
                items.forEach((item, idx) => {
                    if (idx === activeFocusIndex) {
                        item.classList.add('active', 'bg-light');
                        item.scrollIntoView({ block: 'nearest' });
                    } else {
                        item.classList.remove('active', 'bg-light');
                    }
                });
            }

            // Close dropdown when clicking outside
            document.addEventListener('click', function (e) {
                if (searchContainer && !searchContainer.contains(e.target)) {
                    hideDropdown();
                }
            });
        });
    </script>

    @stack('scripts')
    <!-- Client-side Image Compression Script -->
    <script src="{{ asset('js/image-compressor.js') }}"></script>
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