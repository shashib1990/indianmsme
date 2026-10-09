<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Indian MSME — National Digital Gateway</title>
    <link rel="shortcut icon" href="{{ \App\Models\SystemSetting::getVal('favicon', asset('admin-assets/images/favicon.ico')) }}">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600&family=Outfit:wght@400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Iconify for premium vector icons -->
    <script src="https://code.iconify.design/iconify-icon/1.0.7/iconify-icon.min.js"></script>

    <style>
        :root {
            --bg-color: #0b0f19;
            --card-bg: rgba(17, 24, 39, 0.55);
            --card-border: rgba(255, 255, 255, 0.08);
            --accent-saffron: #ff9933;
            --accent-green: #138808;
            --accent-blue: #000080;
            --text-primary: #f3f4f6;
            --text-secondary: #9ca3af;
            --glow-saffron: rgba(255, 153, 51, 0.15);
            --glow-green: rgba(19, 136, 8, 0.15);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-primary);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            overflow-x: hidden;
            position: relative;
        }

        /* Tricolor Background Ambient Glows */
        body::before {
            content: '';
            position: absolute;
            top: -10%;
            left: -10%;
            width: 50%;
            height: 50%;
            background: radial-gradient(circle, var(--glow-saffron) 0%, rgba(11, 15, 25, 0) 70%);
            z-index: 0;
            pointer-events: none;
        }

        body::after {
            content: '';
            position: absolute;
            bottom: -10%;
            right: -10%;
            width: 50%;
            height: 50%;
            background: radial-gradient(circle, var(--glow-green) 0%, rgba(11, 15, 25, 0) 70%);
            z-index: 0;
            pointer-events: none;
        }

        /* Navigation Header */
        header {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 30px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            z-index: 10;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
        }

        .brand-logo {
            height: 48px;
            width: auto;
            filter: drop-shadow(0 0 8px rgba(255, 255, 255, 0.1));
        }

        .brand-text {
            font-family: 'Outfit', sans-serif;
            font-size: 22px;
            font-weight: 700;
            letter-spacing: -0.5px;
            background: linear-gradient(135deg, #ffffff 40%, var(--accent-saffron) 70%, var(--accent-green) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .server-status {
            display: flex;
            align-items: center;
            gap: 8px;
            background: rgba(255, 255, 255, 0.04);
            border: 1px solid var(--card-border);
            padding: 6px 14px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 500;
            color: #10b981;
        }

        .status-dot {
            width: 8px;
            height: 8px;
            background-color: #10b981;
            border-radius: 50%;
            box-shadow: 0 0 8px #10b981;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(0.9); opacity: 0.6; }
            50% { transform: scale(1.1); opacity: 1; box-shadow: 0 0 12px #10b981; }
            100% { transform: scale(0.9); opacity: 0.6; }
        }

        /* Hero Section */
        main {
            flex-grow: 1;
            max-width: 1200px;
            width: 100%;
            margin: 0 auto;
            padding: 40px 20px 80px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 50px;
            z-index: 10;
        }

        .hero {
            text-align: center;
            max-width: 800px;
        }

        .hero-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            background: linear-gradient(90deg, rgba(255, 153, 51, 0.1) 0%, rgba(19, 136, 8, 0.1) 100%);
            border: 1px solid rgba(255, 255, 255, 0.08);
            padding: 6px 16px;
            border-radius: 30px;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            color: #d1d5db;
            margin-bottom: 24px;
        }

        .hero h1 {
            font-family: 'Outfit', sans-serif;
            font-size: 48px;
            font-weight: 800;
            line-height: 1.15;
            letter-spacing: -1.5px;
            margin-bottom: 18px;
        }

        .hero p {
            font-size: 17px;
            color: var(--text-secondary);
            line-height: 1.6;
            font-weight: 400;
        }

        /* Grid Layout */
        .gateway-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 24px;
            width: 100%;
        }

        /* Glassmorphic Portal Card */
        .portal-card {
            background-color: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: 20px;
            padding: 32px 24px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            backdrop-filter: blur(16px);
            position: relative;
            overflow: hidden;
            text-decoration: none;
        }

        .portal-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(180deg, rgba(255, 255, 255, 0.03) 0%, rgba(255, 255, 255, 0) 100%);
            z-index: 0;
            pointer-events: none;
        }

        .portal-card:hover {
            transform: translateY(-8px);
            border-color: rgba(255, 255, 255, 0.2);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.4);
        }

        /* Custom Hover Border Glows */
        .portal-card.admin:hover {
            box-shadow: 0 15px 35px rgba(255, 255, 255, 0.05), inset 0 0 20px rgba(255, 255, 255, 0.02);
            border-color: rgba(255, 255, 255, 0.3);
        }

        .portal-card.ddc:hover {
            box-shadow: 0 15px 35px rgba(255, 153, 51, 0.1), inset 0 0 20px rgba(255, 153, 51, 0.02);
            border-color: rgba(255, 153, 51, 0.3);
        }

        .portal-card.adc:hover {
            box-shadow: 0 15px 35px rgba(59, 130, 246, 0.1), inset 0 0 20px rgba(59, 130, 246, 0.02);
            border-color: rgba(59, 130, 246, 0.3);
        }

        .portal-card.vendor:hover {
            box-shadow: 0 15px 35px rgba(19, 136, 8, 0.1), inset 0 0 20px rgba(19, 136, 8, 0.02);
            border-color: rgba(19, 136, 8, 0.3);
        }

        .card-top {
            display: flex;
            flex-direction: column;
            gap: 20px;
            z-index: 1;
        }

        .icon-wrapper {
            width: 54px;
            height: 54px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
        }

        .admin .icon-wrapper {
            background: rgba(255, 255, 255, 0.05);
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: #ffffff;
        }

        .ddc .icon-wrapper {
            background: rgba(255, 153, 51, 0.08);
            border: 1px solid rgba(255, 153, 51, 0.15);
            color: var(--accent-saffron);
        }

        .adc .icon-wrapper {
            background: rgba(59, 130, 246, 0.08);
            border: 1px solid rgba(59, 130, 246, 0.15);
            color: #3b82f6;
        }

        .vendor .icon-wrapper {
            background: rgba(19, 136, 8, 0.08);
            border: 1px solid rgba(19, 136, 8, 0.15);
            color: var(--accent-green);
        }

        .portal-card h2 {
            font-family: 'Outfit', sans-serif;
            font-size: 20px;
            font-weight: 700;
            color: #ffffff;
        }

        .portal-card p {
            font-size: 14px;
            color: var(--text-secondary);
            line-height: 1.5;
        }

        .card-bottom {
            margin-top: 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 13px;
            font-weight: 600;
            color: #ffffff;
            z-index: 1;
        }

        .arrow-icon {
            font-size: 18px;
            transition: transform 0.3s ease;
        }

        .portal-card:hover .arrow-icon {
            transform: translateX(5px);
        }

        /* Technical Info Overlay Banner */
        .system-banner {
            max-width: 1200px;
            width: 100%;
            margin: 20px auto 0 auto;
            background: rgba(255, 255, 255, 0.02);
            border: 1px solid rgba(255, 255, 255, 0.05);
            border-radius: 16px;
            padding: 20px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 16px;
            z-index: 10;
        }

        .system-badges {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 12px;
        }

        .badge-pill {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            background: rgba(255, 255, 255, 0.03);
            border: 1px solid rgba(255, 255, 255, 0.05);
            color: var(--text-secondary);
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .badge-pill iconify-icon {
            font-size: 15px;
        }

        .system-tag {
            font-size: 12px;
            font-weight: 500;
            color: var(--text-secondary);
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Footer */
        footer {
            width: 100%;
            border-top: 1px solid rgba(255, 255, 255, 0.05);
            background: rgba(7, 10, 18, 0.85);
            backdrop-filter: blur(10px);
            padding: 30px 20px;
            text-align: center;
            font-size: 13px;
            color: var(--text-secondary);
            z-index: 10;
        }

        footer .emblem {
            font-size: 24px;
            color: #ffffff;
            margin-bottom: 10px;
        }

        footer p {
            line-height: 1.5;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .gateway-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 20px;
            }
        }

        @media (max-width: 640px) {
            .gateway-grid {
                grid-template-columns: 1fr;
                gap: 16px;
            }

            .hero h1 {
                font-size: 34px;
            }

            header {
                flex-direction: column;
                gap: 16px;
                padding: 20px;
            }

            .system-banner {
                flex-direction: column;
                align-items: flex-start;
                padding: 16px;
            }
        }
    </style>
</head>

<body>
    @php
        $host = request()->getHost();
        $adminSub = config('app.admin_subdomain') ?: 'admin';
        $vendorSub = config('app.vendor_subdomain') ?: 'vendor';
        $ddcSub = config('app.ddc_subdomain') ?: 'ddc';

        $subdomains = [$adminSub, $vendorSub, $ddcSub];
        $isSubdomainRequest = false;
        $baseDomain = config('app.domain') ?: 'localhost';

        foreach ($subdomains as $sub) {
            if (str_starts_with($host, $sub . '.')) {
                $isSubdomainRequest = true;
                $baseDomain = substr($host, strlen($sub) + 1);
                break;
            }
        }

        if (!$isSubdomainRequest && config('app.domain') && config('app.domain') !== 'localhost') {
            $isSubdomainRequest = true;
            $baseDomain = config('app.domain');
        }

        $port = request()->getPort();
        $portSuffix = ($port && $port != 80 && $port != 443) ? ':' . $port : '';

        if ($isSubdomainRequest) {
            $scheme = request()->getScheme();
            $adminUrl  = $scheme . '://' . $adminSub  . '.' . $baseDomain . $portSuffix . '/login';
            $ddcUrl    = $scheme . '://' . $ddcSub    . '.' . $baseDomain . $portSuffix . '/login';
            $vendorUrl = $scheme . '://' . $vendorSub . '.' . $baseDomain . $portSuffix . '/login';
            $adcUrl    = $scheme . '://' . $baseDomain . $portSuffix . '/login';
        } else {
            $adminUrl  = url('/admin/login');
            $ddcUrl    = url('/ddc/login');
            $adcUrl    = url('/login');
            $vendorUrl = url('/vendor/login');
        }
    @endphp

    <!-- Navigation Header -->
    <header>
        <a href="/" class="brand">
            <img src="{{ \App\Models\SystemSetting::getVal('website_header_logo', asset('admin-assets/images/IndianMSME.png')) }}" class="brand-logo" alt="Indian MSME Logo">
            <span class="brand-text">Indian MSME</span>
        </a>
        <div class="server-status">
            <span class="status-dot"></span>
            National Node Active
        </div>
    </header>

    <!-- Main Section -->
    <main>
        <!-- Hero Title -->
        <div class="hero">
            <div class="hero-badge">
                <iconify-icon icon="solar:shield-up-bold-duotone" style="color:var(--accent-saffron)"></iconify-icon>
                Unified Multi-Tenant Digital Gateway
            </div>
            <h1>National MSME Development Portal</h1>
            <p>A high-performance secure entry gateway coordinating administrators, development commissioners, collectorates, and registered sellers across the Indian MSME landscape.</p>
        </div>

        <!-- 4 Subdomain Portals Grid -->
        <div class="gateway-grid">

            <!-- Admin Gateway -->
            <a href="{{ $adminUrl }}" class="portal-card admin">
                <div class="card-top">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:user-speak-rounded-bold-duotone"></iconify-icon>
                    </div>
                    <h2>Super Admin</h2>
                    <p>Centralized authorization hub. Coordinate system users, manage roles & permissions, allocate department scopes, and monitor impersonation audits.</p>
                </div>
                <div class="card-bottom">
                    <span>Access Admin Portal</span>
                    <iconify-icon icon="solar:arrow-right-line-duotone" class="arrow-icon"></iconify-icon>
                </div>
            </a>

            <!-- DDC Gateway -->
            <a href="{{ $ddcUrl }}" class="portal-card ddc">
                <div class="card-top">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:user-id-bold-duotone"></iconify-icon>
                    </div>
                    <h2>DDC Portal</h2>
                    <p>District Development Commissioners. Oversee district staff, authorize regional roles, deploy regional budgets, and review local industrial development metrics.</p>
                </div>
                <div class="card-bottom">
                    <span>Access DDC Portal</span>
                    <iconify-icon icon="solar:arrow-right-line-duotone" class="arrow-icon"></iconify-icon>
                </div>
            </a>

            <!-- ADC Gateway -->
            <a href="{{ $adcUrl }}" class="portal-card adc">
                <div class="card-top">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:card-search-bold-duotone"></iconify-icon>
                    </div>
                    <h2>ADC Portal</h2>
                    <p>Additional District Collectors. Regulate local transactions, verify district seller registration compliance, and audit regional wallet daily allocations.</p>
                </div>
                <div class="card-bottom">
                    <span>Access ADC Portal</span>
                    <iconify-icon icon="solar:arrow-right-line-duotone" class="arrow-icon"></iconify-icon>
                </div>
            </a>

            <!-- Vendor Gateway -->
            <a href="{{ $vendorUrl }}" class="portal-card vendor">
                <div class="card-top">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:shop-bold-duotone"></iconify-icon>
                    </div>
                    <h2>MSME Seller</h2>
                    <p>Registered MSME Sellers & Vendors. Update business profiles, list industrial catalog goods, process orders, and utilize regional development sub-panels.</p>
                </div>
                <div class="card-bottom">
                    <span>Access Seller Portal</span>
                    <iconify-icon icon="solar:arrow-right-line-duotone" class="arrow-icon"></iconify-icon>
                </div>
            </a>

        </div>

        <!-- Infrastructure Banner -->
        <div class="system-banner">
            <div class="system-badges">
                <div class="badge-pill">
                    <iconify-icon icon="solar:database-bold-duotone" style="color:#60a5fa"></iconify-icon>
                    Single-Table Schema
                </div>
                <div class="badge-pill">
                    <iconify-icon icon="solar:shield-check-bold-duotone" style="color:#34d399"></iconify-icon>
                    Multi-Guard RBAC Lock
                </div>
                <div class="badge-pill">
                    <iconify-icon icon="solar:history-bold-duotone" style="color:#fbbf24"></iconify-icon>
                    Impersonation Audited
                </div>
            </div>
            <div class="system-tag">
                <iconify-icon icon="solar:cpu-bold-duotone"></iconify-icon>
                Framework Node: Laravel 10.x &bull; PHP 8.2
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer>
        <div class="emblem">🇮🇳</div>
        <p>National Portal Gateway for Micro, Small & Medium Enterprises</p>
        <p style="margin-top: 8px; font-size: 11px; opacity: 0.6;">Ministry of MSME &bull; Government of India &bull; Administered under National Secure Node Infrastructure</p>
    </footer>

</body>

</html>