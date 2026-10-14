<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <title>@yield('title', 'POS Billing System') | Indian MSME DDC</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon"
        href="{{ \App\Models\SystemSetting::getVal('favicon', asset('admin-assets/images/favicon.ico')) }}">

    <!-- Vendor & App css -->
    <link href="{{ asset('admin-assets/css/vendor.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin-assets/css/icons.min.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin-assets/css/app.min.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/toastify/toastify.min.css') }}">

    <style>
        :root {
            --bs-primary: #ff9933;
            --bs-primary-rgb: 255, 153, 51;
            --adm-primary: #ff9933;
        }

        body,
        html {
            height: 100vh;
            overflow: hidden;
            background-color: #f8fafc;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            display: flex;
            flex-direction: column;
        }

        /* Hide HTML5 input number spinners (up/down arrows) */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        input[type=number] {
            -moz-appearance: textfield;
        }

        .qty-control {
            display: inline-flex;
            align-items: center;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background: #ffffff;
            overflow: hidden;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
        }

        .qty-btn {
            width: 24px;
            height: 24px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: none;
            background: #f1f5f9;
            color: #334155;
            font-weight: 700;
            font-size: 13px;
            cursor: pointer;
            user-select: none;
            transition: all 0.15s ease;
        }

        .qty-btn:hover {
            background: #e2e8f0;
            color: #0f172a;
        }

        .qty-btn:active {
            background: #cbd5e1;
        }

        .qty-input {
            width: 40px;
            height: 24px;
            border: none !important;
            background: #ffffff !important;
            text-align: center;
            font-weight: 700;
            font-size: 12px;
            color: #0f172a;
            padding: 0 2px;
            outline: none !important;
            box-shadow: none !important;
            -webkit-appearance: none;
            -moz-appearance: textfield;
        }

        .pos-header {
            flex: 0 0 auto;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.35rem 1rem;
            z-index: 1000;
        }

        .pos-wrapper {
            flex: 1 1 0%;
            height: 0;
            min-height: 0;
            display: flex;
            overflow: hidden;
        }

        .pos-left-pane {
            flex: 1 1 0%;
            display: flex;
            flex-direction: column;
            height: 100%;
            overflow: hidden;
            background: #f1f5f9;
            min-width: 0;
        }

        .pos-right-pane {
            flex: 0 0 440px;
            width: 440px;
            max-width: 42vw;
            min-width: 360px;
            height: 100%;
            background: #ffffff;
            border-left: 1px solid #e2e8f0;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: -4px 0 15px rgba(0, 0, 0, 0.03);
            padding: 0.5rem 0.65rem;
        }

        .cart-items-scroll {
            flex: 1 1 0%;
            min-height: 100px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .pos-right-footer {
            flex: 0 0 auto;
            margin-top: auto;
            border-top: 1px solid #e2e8f0;
            padding-top: 0.5rem;
            background: #ffffff;
        }

        @media (max-width: 991px) {
            .pos-wrapper {
                flex-direction: column;
                overflow-y: auto;
            }

            .pos-left-pane,
            .pos-right-pane {
                flex: none;
                width: 100%;
                max-width: 100%;
                min-width: 100%;
                height: auto;
            }

            body,
            html {
                overflow: auto;
                height: auto;
            }
        }

        .product-grid-container {
            flex: 1;
            overflow-y: auto;
            padding: 0.85rem;
        }

        #productsGrid {
            display: grid !important;
            grid-template-columns: repeat(auto-fill, minmax(145px, 1fr)) !important;
            gap: 0.75rem !important;
            margin: 0 !important;
            width: 100% !important;
        }

        #productsGrid>div {
            width: 100% !important;
            max-width: 100% !important;
            flex: none !important;
            padding: 0 !important;
        }

        .product-card {
            transition: all 0.15s ease-in-out;
            cursor: pointer;
            border: 1px solid #e2e8f0;
            background: #ffffff;
            border-radius: 10px !important;
            display: flex;
            flex-direction: column;
            height: 100%;
        }

        .product-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.08) !important;
            border-color: var(--adm-primary) !important;
        }

        .product-card-title {
            font-size: 11.5px;
            font-weight: 700;
            color: #1e293b;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            min-height: 30px;
            max-height: 30px;
            margin-bottom: 4px;
        }

        .btn-theme {
            background: var(--adm-primary) !important;
            border-color: var(--adm-primary) !important;
            color: #ffffff !important;
        }

        /* Custom Scrollbars */
        ::-webkit-scrollbar {
            width: 6px;
            height: 6px;
        }

        ::-webkit-scrollbar-track {
            background: #f1f5f9;
        }

        ::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
    </style>
    @stack('styles')
</head>

<body>

    @php
        $isAdc = auth('adc')->check();
        $user = $isAdc ? auth('adc')->user() : auth('ddc')->user();
        $dashboardRoute = $isAdc ? route('adc.dashboard') : route('ddc.dashboard');
        $posRetailRoute = $isAdc ? route('adc.pos.retail') : route('ddc.pos.retail');
        $posWholesaleRoute = $isAdc ? null : route('ddc.pos.wholesale');
        $posOrdersRoute = $isAdc ? route('adc.pos.orders') : route('ddc.pos.orders');
        $posReturnsRoute = $isAdc ? route('adc.pos.returns.index') : route('ddc.pos.returns.index');
        $terminalLabel = $isAdc ? 'ADC Retail POS' : 'DDC POS Terminal';
    @endphp

    <!-- Top Fullscreen POS Header -->
    <header class="pos-header">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ $dashboardRoute }}"
                class="btn btn-sm btn-light border d-flex align-items-center gap-1 text-slate-700 fw-semibold"
                title="Exit POS to Dashboard">
                <iconify-icon icon="solar:arrow-left-linear" class="fs-16"></iconify-icon>
                <span>Exit POS</span>
            </a>
            <div class="vr my-2"></div>
            <div class="d-flex align-items-center gap-2">
                <span
                    class="badge bg-primary-subtle text-primary px-2.5 py-1 fs-12 fw-bold text-uppercase">{{ $terminalLabel }}</span>
                <h5 class="fw-bold text-slate-800 mb-0 fs-15 d-none d-md-block">{{ $user?->name }}</h5>
            </div>
        </div>

        <!-- Mode Toggle Pills in Header -->
        <div class="nav nav-pills p-1 bg-light rounded-pill d-inline-flex border shadow-sm" role="tablist">
            <a href="{{ $posRetailRoute }}"
                class="nav-link px-3 py-1 fs-12 fw-bold rounded-pill {{ ($mode ?? 'retail') === 'retail' ? 'active bg-success text-white shadow-sm' : 'text-slate-700' }}">
                <iconify-icon icon="solar:user-bold-duotone" class="align-middle me-1 fs-15"></iconify-icon>
                Retail POS
            </a>
            @if($posWholesaleRoute)
                <a href="{{ $posWholesaleRoute }}"
                    class="nav-link px-3 py-1 fs-12 fw-bold rounded-pill {{ ($mode ?? 'retail') === 'wholesale' ? 'active bg-warning text-dark shadow-sm' : 'text-slate-700' }}">
                    <iconify-icon icon="solar:shop-bold-duotone" class="align-middle me-1 fs-15"></iconify-icon>
                    Wholesale POS
                </a>
            @endif
        </div>

        <div class="d-flex align-items-center gap-2">
            <div class="text-end d-none d-lg-block me-2">
                <div class="fw-bold text-slate-800 fs-12" id="posClock">--:--:--</div>
                <div class="text-muted fs-11" id="posDate">---</div>
            </div>

            <a href="{{ $posOrdersRoute }}" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1"
                title="Sales History">
                <iconify-icon icon="solar:history-bold-duotone" class="fs-16"></iconify-icon>
                <span class="d-none d-sm-inline">Sales History</span>
            </a>

            @if($posReturnsRoute)
                <a href="{{ $posReturnsRoute }}" class="btn btn-sm btn-outline-danger d-flex align-items-center gap-1"
                    title="Sales Returns History">
                    <iconify-icon icon="solar:restart-circle-bold-duotone" class="fs-16"></iconify-icon>
                    <span class="d-none d-sm-inline">Returns</span>
                </a>
            @endif

            <button type="button" class="btn btn-sm btn-light border" onclick="toggleFullScreen()"
                title="Toggle Fullscreen">
                <iconify-icon icon="solar:full-screen-bold" class="fs-16 align-middle"></iconify-icon>
            </button>
        </div>
    </header>

    <!-- Main Content Area -->
    @yield('content')

    <!-- Vendor Scripts -->
    <script src="{{ asset('admin-assets/js/vendor.js') }}"></script>
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
    <script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script type="text/javascript" src="{{ asset('plugins/toastify/toastify.min.js') }}"></script>

    <script>
        // Live Clock Updates
        function updateClock() {
            const now = new Date();
            const timeStr = now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
            const dateStr = now.toLocaleDateString('en-US', { weekday: 'short', month: 'short', day: 'numeric' });
            const clockEl = document.getElementById('posClock');
            const dateEl = document.getElementById('posDate');
            if (clockEl) clockEl.innerText = timeStr;
            if (dateEl) dateEl.innerText = dateStr;
        }
        setInterval(updateClock, 1000);
        updateClock();

        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    console.error(err);
                });
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
        }
    </script>
    @stack('scripts')
</body>

</html>