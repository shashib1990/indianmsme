<!DOCTYPE html>
<html lang="en" data-bs-theme="light">

<head>
    <meta charset="utf-8" />
    <title>ADC Retail POS | {{ $storeName }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <link rel="shortcut icon" href="{{ \App\Models\SystemSetting::getVal('favicon', asset('admin-assets/images/favicon.ico')) }}">

    <!-- Google Fonts: Inter & Outfit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Outfit:wght@500;600;700;800;900&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 & Icons -->
    <link href="{{ asset('admin-assets/css/vendor.min.css') }}" rel="stylesheet" type="text/css" />
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/toastify/toastify.min.css') }}">

    <style>
        :root {
            --pos-primary: #f95721;
            --pos-primary-hover: #e04815;
            --pos-primary-light: #fff3ec;
            --pos-header-bg: #0d233a;
            --pos-accent: #f95721;
            --pos-surface: #ffffff;
            --pos-bg: #f4f6f8;
            --pos-border: #e2e8f0;
            --pos-text: #0f172a;
            --pos-text-muted: #64748b;
        }

        * {
            box-sizing: border-box;
            -webkit-tap-highlight-color: transparent;
        }

        body, html {
            height: 100%;
            margin: 0;
            padding: 0;
            background-color: var(--pos-bg);
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: var(--pos-text);
            overflow: hidden;
            -webkit-user-select: none;
            user-select: none;
        }

        input, textarea, select {
            -webkit-user-select: auto;
            user-select: auto;
        }

        /* ── Master App Viewport Shell ── */
        .pos-app-shell {
            display: flex;
            flex-direction: column;
            height: 100vh;
            height: 100dvh;
            width: 100vw;
            max-width: 520px;
            margin: 0 auto;
            background: var(--pos-surface);
            position: relative;
            box-shadow: 0 0 30px rgba(0, 0, 0, 0.08);
            overflow: hidden;
        }

        .d-none {
            display: none !important;
        }

        /* ── App Topbar ── */
        .pos-app-header {
            flex: 0 0 54px;
            height: 54px;
            background: var(--pos-header-bg);
            color: #ffffff;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 10px;
            z-index: 1020;
            border-bottom: 1px solid rgba(255, 255, 255, 0.08);
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .header-left-group {
            display: flex;
            align-items: center;
            gap: 8px;
            min-width: 0;
            flex-shrink: 0;
        }

        .header-nav-btn {
            background: transparent;
            border: none;
            color: #ffffff;
            padding: 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.15s ease;
            flex-shrink: 0;
        }

        .header-nav-btn:active {
            transform: scale(0.92);
            background: rgba(255, 255, 255, 0.12);
        }

        .header-title-box {
            display: flex;
            flex-direction: column;
            justify-content: center;
            min-width: 0;
            text-align: left;
        }

        .header-app-name {
            font-family: 'Outfit', sans-serif;
            font-size: 15px;
            font-weight: 800;
            letter-spacing: 0.5px;
            color: #ffffff;
            line-height: 1.15;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .header-store-name {
            font-size: 10.5px;
            font-weight: 600;
            color: #94a3b8;
            line-height: 1.1;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            white-space: nowrap;
        }

        .header-right-tools {
            display: flex;
            align-items: center;
            gap: 2px;
            flex-shrink: 0;
        }

        .header-clear-cart-btn {
            background: #ef4444;
            color: #ffffff;
            border: none;
            border-radius: 14px;
            padding: 4px 10px;
            font-size: 11px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            cursor: pointer;
            transition: all 0.15s ease;
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.35);
            flex-shrink: 0;
        }

        .header-clear-cart-btn:active {
            transform: scale(0.94);
            background: #dc2626;
        }

        .header-tool-btn {
            background: transparent;
            border: none;
            color: #ffffff;
            padding: 4px 6px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            border-radius: 6px;
        }

        .header-tool-btn:active {
            transform: scale(0.92);
        }

        .cart-icon-badge-box {
            position: relative;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding-right: 12px; /* Ensures cart icon is never hidden behind the badge */
        }

        .cart-header-icon {
            font-size: 24px;
            color: #ffffff;
            display: block;
        }

        .header-tool-col {
            background: transparent;
            border: none;
            color: #ffffff;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 0 5px;
            text-decoration: none;
            cursor: pointer;
            line-height: 1.05;
            min-width: 44px;
            border-radius: 6px;
            transition: all 0.15s ease;
        }

        .header-tool-col:active {
            transform: scale(0.92);
            opacity: 0.85;
        }

        .header-col-text {
            font-size: 9px;
            font-weight: 700;
            color: #ffffff;
            text-align: center;
            margin-top: 2px;
            letter-spacing: 0.2px;
            line-height: 1.05;
        }

        .header-v-divider {
            width: 1px;
            height: 26px;
            background: rgba(255, 255, 255, 0.22);
            margin: 0 3px;
        }

        .header-cart-badge {
            position: absolute;
            top: -5px;
            right: 0px;
            background: #f95721;
            color: #ffffff;
            font-size: 10px;
            font-weight: 800;
            min-width: 17px;
            height: 17px;
            border-radius: 9px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0 4px;
            box-shadow: 0 2px 5px rgba(249, 87, 33, 0.4);
            border: 1.5px solid var(--pos-header-bg);
            z-index: 5;
            pointer-events: none;
            transition: transform 0.2s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        /* ── Views Manager ── */
        .pos-views-container {
            flex: 1 1 0%;
            height: 0;
            position: relative;
            overflow: hidden;
            background: var(--pos-bg);
        }

        .pos-view-screen {
            position: absolute;
            inset: 0;
            display: flex;
            flex-direction: column;
            background: var(--pos-bg);
            transition: transform 0.24s cubic-bezier(0.2, 0.8, 0.2, 1), opacity 0.2s ease;
            opacity: 1;
            z-index: 10;
        }

        .pos-view-screen.slide-right {
            transform: translateX(100%);
            opacity: 0;
            pointer-events: none;
            z-index: 5;
        }

        .pos-view-screen.slide-left {
            transform: translateX(-100%);
            opacity: 0;
            pointer-events: none;
            z-index: 5;
        }

        /* ── Screen 1: Catalog & Search Styles ── */
        .catalog-search-bar {
            padding: 10px 12px;
            background: var(--pos-surface);
            border-bottom: 1px solid var(--pos-border);
            display: flex;
            gap: 8px;
            align-items: center;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
            z-index: 20;
        }

        .search-input-wrap {
            position: relative;
            flex: 1;
        }

        .search-input-wrap iconify-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: #94a3b8;
            font-size: 18px;
        }

        .search-input-field {
            width: 100%;
            height: 42px;
            padding: 8px 34px 8px 34px;
            border: 1.5px solid var(--pos-border);
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            color: var(--pos-text);
            background: #f8fafc;
            outline: none;
            transition: border-color 0.15s, background-color 0.15s;
        }

        .search-input-field:focus {
            border-color: var(--pos-accent);
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(249, 115, 22, 0.12);
        }

        .search-clear-btn {
            position: absolute;
            right: 8px;
            top: 50%;
            transform: translateY(-50%);
            border: none;
            background: transparent;
            color: #94a3b8;
            font-size: 18px;
            padding: 0;
            cursor: pointer;
            display: none;
        }

        .btn-scan-trigger {
            height: 42px;
            padding: 0 14px;
            background: #ffffff;
            border: 1.5px solid #f95721;
            border-radius: 10px;
            color: #f95721;
            font-size: 13px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 1px 4px rgba(249, 87, 33, 0.12);
            cursor: pointer;
            flex-shrink: 0;
            transition: all 0.15s ease;
        }

        .btn-scan-trigger iconify-icon {
            color: #f95721;
        }

        .btn-scan-trigger:active {
            background: #fff5f0;
            transform: scale(0.96);
        }

        /* ── Empty Catalog State (Pixel Matched with Bag Illustration) ── */
        .pos-empty-catalog-state {
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 45px 20px 60px;
            margin: auto 0;
            width: 100%;
        }

        .empty-bag-illustration-wrap {
            margin-bottom: 24px;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .pos-empty-title {
            font-family: 'Outfit', sans-serif;
            font-size: 23px;
            font-weight: 800;
            color: #0d1e38;
            margin: 0 0 10px;
            letter-spacing: -0.2px;
            line-height: 1.2;
        }

        .pos-empty-subtitle {
            font-family: 'Inter', system-ui, sans-serif;
            font-size: 15px;
            font-weight: 500;
            color: #2b3e5a;
            line-height: 1.45;
            margin: 0;
            max-width: 330px;
        }

        /* Catalog Product Scroll List */
        .catalog-scroll-list {
            flex: 1 1 0%;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            padding: 10px 12px 85px;
            display: flex;
            flex-direction: column;
            gap: 9px;
        }

        .mobile-product-card {
            background: var(--pos-surface);
            border: 1px solid var(--pos-border);
            border-radius: 12px;
            padding: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.02);
            transition: border-color 0.15s, box-shadow 0.15s;
        }

        .mobile-product-card:active {
            background-color: #fafaf9;
        }

        .product-thumb-box {
            width: 58px;
            height: 58px;
            border-radius: 10px;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .product-thumb-box img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 3px;
        }

        .product-info-box {
            flex: 1;
            min-width: 0;
        }

        .product-name-title {
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            line-height: 1.25;
            margin-bottom: 2px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-meta-sub {
            font-size: 10.5px;
            color: #64748b;
            line-height: 1.2;
            margin-bottom: 3px;
        }

        .product-stock-badge {
            display: inline-block;
            font-size: 11px;
            font-weight: 700;
            color: #16a34a;
        }

        .product-stock-badge.out-of-stock {
            color: #dc2626;
        }

        .product-price-action-box {
            display: flex;
            flex-direction: column;
            align-items: flex-end;
            justify-content: space-between;
            flex-shrink: 0;
            gap: 6px;
        }

        .product-price-val {
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
            line-height: 1;
        }

        .product-mrp-strike {
            font-size: 10px;
            color: #94a3b8;
            text-decoration: line-through;
            line-height: 1;
        }

        .product-disc-badge {
            background: #dcfce7;
            color: #15803d;
            font-size: 9.5px;
            font-weight: 700;
            padding: 1px 4px;
            border-radius: 4px;
            display: inline-block;
            line-height: 1.2;
        }

        .btn-add-cart-pill {
            background: #f95721;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            padding: 6px 16px;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(249, 87, 33, 0.3);
            transition: all 0.15s ease;
            white-space: nowrap;
        }

        .btn-add-cart-pill:active {
            transform: scale(0.92);
        }

        /* Mini stepper when product already in cart */
        .cart-mini-stepper {
            display: flex;
            align-items: center;
            background: #ffedd5;
            border: 1px solid #fdba74;
            border-radius: 8px;
            overflow: hidden;
            height: 28px;
        }

        .cart-mini-stepper button {
            width: 26px;
            height: 28px;
            border: none;
            background: transparent;
            color: #ea580c;
            font-weight: 800;
            font-size: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 0;
        }

        .cart-mini-stepper span {
            font-size: 12px;
            font-weight: 800;
            color: #9a3412;
            min-width: 22px;
            text-align: center;
        }

        /* Sticky Bottom Float View-Cart Bar */
        .bottom-cart-floating-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: var(--pos-surface);
            border-top: 1px solid var(--pos-border);
            padding: 10px 14px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 -4px 18px rgba(0, 0, 0, 0.08);
            z-index: 30;
            transition: transform 0.2s ease;
        }

        .bottom-cart-floating-bar.d-none {
            transform: translateY(100%);
        }

        .floating-cart-total-info {
            display: flex;
            flex-direction: column;
        }

        .floating-cart-items-count {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
        }

        .floating-cart-grand-val {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 900;
            color: #0f172a;
            line-height: 1;
        }

        .btn-floating-view-cart {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: #ffffff;
            border: none;
            border-radius: 10px;
            padding: 10px 18px;
            font-size: 13.5px;
            font-weight: 800;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            box-shadow: 0 4px 12px rgba(234, 88, 12, 0.35);
            cursor: pointer;
        }

        .btn-floating-view-cart:active {
            transform: scale(0.96);
        }

        /* ── Screen 2: Barcode Camera Scanner Overlay ── */
        .scanner-overlay-view {
            position: absolute;
            inset: 0;
            background: #000000;
            z-index: 1050;
            display: flex;
            flex-direction: column;
            color: #ffffff;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }

        .scanner-overlay-view.active {
            opacity: 1;
            pointer-events: auto;
        }

        .scanner-top-bar {
            height: 54px;
            background: rgba(15, 23, 42, 0.85);
            backdrop-filter: blur(8px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 14px;
            z-index: 20;
        }

        .scanner-viewport-box {
            flex: 1;
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            background: #000000;
        }

        #cameraPreviewArea {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
        }

        #cameraPreviewArea video {
            width: 100% !important;
            height: 100% !important;
            object-fit: cover !important;
        }

        /* Reticle Viewfinder with glowing corner brackets & red laser */
        .scanner-reticle-frame {
            position: absolute;
            width: 250px;
            height: 250px;
            border-radius: 16px;
            pointer-events: none;
            box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.55);
            z-index: 15;
        }

        .reticle-corner {
            position: absolute;
            width: 26px;
            height: 26px;
            border-color: #f97316;
            border-style: solid;
        }

        .reticle-corner.tl { top: 0; left: 0; border-width: 4px 0 0 4px; border-top-left-radius: 14px; }
        .reticle-corner.tr { top: 0; right: 0; border-width: 4px 4px 0 0; border-top-right-radius: 14px; }
        .reticle-corner.bl { bottom: 0; left: 0; border-width: 0 0 4px 4px; border-bottom-left-radius: 14px; }
        .reticle-corner.br { bottom: 0; right: 0; border-width: 0 4px 4px 0; border-bottom-right-radius: 14px; }

        .scanner-laser-line {
            position: absolute;
            top: 10px;
            left: 8px;
            right: 8px;
            height: 2.5px;
            background: linear-gradient(90deg, transparent, #ef4444 20%, #ff2222 50%, #ef4444 80%, transparent);
            box-shadow: 0 0 12px #ff2222, 0 0 4px #ff8888;
            border-radius: 2px;
            animation: laserScanSweep 1.8s ease-in-out infinite alternate;
        }

        @keyframes laserScanSweep {
            0% { top: 12px; }
            100% { top: 234px; }
        }

        .scanner-instruction-text {
            position: absolute;
            bottom: 22px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 12px;
            font-weight: 600;
            color: #ffffff;
            text-shadow: 0 2px 6px rgba(0, 0, 0, 0.8);
            z-index: 16;
        }

        .scanner-bottom-controls {
            height: 90px;
            background: rgba(15, 23, 42, 0.95);
            display: flex;
            align-items: center;
            justify-content: space-around;
            padding: 0 20px;
            z-index: 20;
        }

        .scanner-ctrl-btn {
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #ffffff;
            width: 48px;
            height: 48px;
            border-radius: 50%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            cursor: pointer;
        }

        .scanner-ctrl-btn.active {
            background: #eab308;
            color: #0f172a;
        }

        .scanner-capture-btn {
            width: 60px;
            height: 60px;
            background: #ef4444;
            border: 3px solid #ffffff;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 24px;
            box-shadow: 0 0 16px rgba(239, 68, 68, 0.6);
            cursor: pointer;
        }

        /* ── Screen 4: Cart & Discount Screen ── */
        .cart-header-bar {
            height: 50px;
            padding: 0 14px;
            background: var(--pos-surface);
            border-bottom: 1px solid var(--pos-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            z-index: 20;
        }

        /* ── Cart Screen Styles ── */
        .cart-screen-header-bar {
            height: 38px;
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 12px;
            flex-shrink: 0;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
        }

        .cart-back-btn {
            border: 1px solid #e2e8f0;
            background: #f8fafc;
            color: #334155;
            border-radius: 14px;
            padding: 3px 10px;
            font-size: 11px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 4px;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .cart-back-btn:active {
            background: #e2e8f0;
        }

        .cart-screen-title {
            font-family: 'Outfit', sans-serif;
            font-size: 12.5px;
            font-weight: 700;
            color: #0f172a;
            letter-spacing: -0.1px;
        }

        .cart-clear-btn {
            border: 1px solid #fee2e2;
            background: #fef2f2;
            color: #ef4444;
            border-radius: 14px;
            padding: 3px 9px;
            font-size: 11px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 3px;
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .cart-clear-btn:active {
            background: #fee2e2;
        }

        .cart-items-list-container {
            flex: 1 1 0%;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            padding: 8px 10px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .cart-item-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 7px 10px;
            display: flex;
            align-items: center;
            gap: 9px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
        }

        .cart-item-thumb {
            width: 38px;
            height: 38px;
            border-radius: 6px;
            background: #f8fafc;
            border: 1px solid #edf2f7;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            flex-shrink: 0;
        }

        .cart-item-thumb img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            padding: 2px;
        }

        .cart-item-details {
            flex: 1;
            min-width: 0;
            display: flex;
            flex-direction: column;
            gap: 2px;
        }

        .cart-item-header-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 6px;
        }

        .cart-item-name {
            font-size: 12px;
            font-weight: 600;
            color: #0f172a;
            line-height: 1.2;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .cart-item-remove-btn {
            border: none;
            background: transparent;
            color: #94a3b8;
            padding: 2px;
            cursor: pointer;
            font-size: 14px;
            line-height: 1;
            border-radius: 4px;
            flex-shrink: 0;
            transition: color 0.15s ease;
        }

        .cart-item-remove-btn:hover, .cart-item-remove-btn:active {
            color: #ef4444;
        }

        .cart-item-unit-price {
            font-size: 11px;
            font-weight: 600;
            color: #64748b;
            line-height: 1.2;
        }

        .cart-item-uom {
            font-size: 10px;
            color: #94a3b8;
            font-weight: 500;
        }

        .cart-item-footer-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 3px;
        }

        /* Stepper Pill */
        .cart-stepper-compact {
            display: inline-flex;
            align-items: center;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            height: 24px;
            padding: 1px;
            box-sizing: border-box;
            flex-shrink: 0;
        }

        .cart-stepper-compact .stepper-btn {
            width: 22px;
            height: 22px;
            border: none;
            background: #ffffff;
            color: #0f172a;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 11px;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04);
            transition: background-color 0.1s;
            flex-shrink: 0;
            padding: 0;
        }

        .cart-stepper-compact .stepper-btn:active {
            background: #e2e8f0;
        }

        .cart-stepper-compact .stepper-input {
            width: 28px !important;
            min-width: 28px !important;
            max-width: 28px !important;
            height: 22px !important;
            border: none !important;
            background: transparent !important;
            text-align: center;
            font-family: 'Outfit', sans-serif;
            font-size: 11.5px;
            font-weight: 700;
            color: #0f172a;
            padding: 0 !important;
            margin: 0 !important;
            outline: none !important;
            box-shadow: none !important;
            -moz-appearance: textfield;
        }

        .cart-stepper-compact .stepper-input::-webkit-outer-spin-button,
        .cart-stepper-compact .stepper-input::-webkit-inner-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        .cart-item-total-price {
            font-family: 'Outfit', sans-serif;
            font-size: 13.5px;
            font-weight: 700;
            color: #0f172a;
            text-align: right;
            letter-spacing: -0.1px;
        }

        /* Cart Financial Summary Bottom Sheet */
        .cart-summary-bottom-card {
            background: var(--pos-surface);
            border-top: 1px solid var(--pos-border);
            padding: 8px 12px 10px;
            box-shadow: 0 -3px 12px rgba(0, 0, 0, 0.04);
            z-index: 20;
        }

        .summary-line-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 11px;
            color: #475569;
        }

        .summary-line-row strong {
            color: #0f172a;
            font-family: 'Outfit', sans-serif;
            font-size: 12px;
        }

        .summary-discount-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 4px;
            font-size: 11px;
            gap: 8px;
        }

        .discount-input-pill {
            display: flex;
            align-items: center;
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            overflow: hidden;
            height: 24px;
            width: 80px;
        }

        .discount-input-pill input {
            border: none;
            width: 100%;
            height: 24px;
            padding: 0 6px;
            font-size: 11px;
            font-weight: 700;
            text-align: right;
            outline: none;
            background: transparent;
        }

        .discount-unit-toggle {
            width: 40px;
            height: 24px;
            background: #e2e8f0;
            border: none;
            font-size: 11px;
            font-weight: 800;
            color: #334155;
            cursor: pointer;
        }

        .grand-total-banner-row {
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
            border: 1px solid #fed7aa;
            border-radius: 8px;
            padding: 6px 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin: 6px 0 8px;
        }

        .grand-total-label {
            font-family: 'Outfit', sans-serif;
            font-size: 12.5px;
            font-weight: 700;
            color: #9a3412;
        }

        .grand-total-value {
            font-family: 'Outfit', sans-serif;
            font-size: 16.5px;
            font-weight: 800;
            color: #ea580c;
        }

        .btn-proceed-action {
            width: 100%;
            height: 40px;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            box-shadow: 0 3px 10px rgba(234, 88, 12, 0.3);
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-proceed-action:active {
            transform: scale(0.98);
        }

        /* ── Screen 5: Customer Details & Payment ── */
        .customer-scroll-area {
            flex: 1 1 0%;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            padding: 12px 12px 20px;
            display: flex;
            flex-direction: column;
            gap: 12px;
            background: #f8fafc;
        }

        .checkout-section-card {
            background: #ffffff;
            border: 1px solid #e2e8f0;
            border-radius: 14px;
            padding: 13px 14px;
            box-shadow: 0 1px 4px rgba(15, 23, 42, 0.04);
        }

        .checkout-card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .checkout-card-title {
            font-family: 'Outfit', sans-serif;
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            display: flex;
            align-items: center;
            gap: 6px;
        }

        /* Phone Input Container */
        .cust-phone-input-wrap {
            display: flex;
            align-items: center;
            background: #ffffff;
            border: 1.5px solid #cbd5e1;
            border-radius: 10px;
            overflow: hidden;
            transition: all 0.2s ease;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
        }

        .cust-phone-input-wrap:focus-within {
            border-color: #f95721;
            box-shadow: 0 0 0 3px rgba(249, 87, 33, 0.12);
        }

        .cust-phone-prefix {
            display: flex;
            align-items: center;
            padding: 0 10px 0 12px;
            background: #f8fafc;
            border-right: 1.5px solid #e2e8f0;
            height: 42px;
            font-family: 'Outfit', sans-serif;
            font-size: 13px;
            font-weight: 700;
            color: #334155;
            flex-shrink: 0;
        }

        .cust-phone-input {
            flex: 1 1 auto;
            height: 42px;
            border: none;
            outline: none;
            padding: 0 10px;
            font-size: 13.5px;
            font-weight: 600;
            letter-spacing: 0.5px;
            color: #0f172a;
            background: transparent;
            min-width: 0;
        }

        .cust-phone-input::placeholder {
            color: #94a3b8;
            font-weight: 400;
            font-size: 12px;
            letter-spacing: normal;
        }

        .btn-cust-search {
            height: 32px;
            margin-right: 5px;
            padding: 0 12px;
            background: linear-gradient(135deg, #f95721 0%, #ea580c 100%);
            border: none;
            border-radius: 7px;
            color: #ffffff;
            font-family: 'Outfit', sans-serif;
            font-size: 12px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            box-shadow: 0 2px 6px rgba(249, 87, 33, 0.3);
            transition: all 0.15s ease;
            flex-shrink: 0;
        }

        .btn-cust-search:active {
            transform: scale(0.96);
        }

        /* Verified Customer Card - Sleek & Compact */
        .verified-customer-card {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            border-left: 3.5px solid #10b981;
            border-radius: 9px;
            padding: 7px 11px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
            min-height: 44px;
        }

        .verified-cust-avatar {
            width: 28px;
            height: 28px;
            border-radius: 50%;
            background: #ecfdf5;
            color: #059669;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            flex-shrink: 0;
        }

        .verified-cust-info {
            display: flex;
            flex-direction: column;
            min-width: 0;
            line-height: 1.25;
        }

        .verified-cust-name {
            font-family: 'Outfit', sans-serif;
            font-size: 12.5px;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .verified-cust-phone {
            font-size: 11px;
            color: #64748b;
            font-weight: 500;
        }

        .btn-change-cust {
            padding: 3px 9px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-family: 'Outfit', sans-serif;
            font-size: 11px;
            font-weight: 700;
            color: #f95721;
            display: flex;
            align-items: center;
            cursor: pointer;
            transition: all 0.15s ease;
            flex-shrink: 0;
        }

        .btn-change-cust:active {
            background: #fff7ed;
            border-color: #f95721;
        }

        /* New Customer Form */
        .new-cust-form-container {
            margin-top: 10px;
            padding-top: 10px;
            border-top: 1px dashed #cbd5e1;
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .new-cust-notice-banner {
            background: #eff6ff;
            border: 1px solid #bfdbfe;
            border-radius: 8px;
            padding: 7px 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .new-cust-input-group {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .new-cust-label {
            font-size: 11px;
            font-weight: 700;
            color: #475569;
            display: flex;
            align-items: center;
        }

        .new-cust-input {
            width: 100%;
            padding: 8px 11px;
            font-size: 12.5px;
            font-weight: 500;
            border: 1.5px solid #cbd5e1;
            border-radius: 8px;
            outline: none;
            background: #f8fafc;
            color: #0f172a;
            transition: all 0.15s ease;
        }

        .new-cust-input:focus {
            border-color: #f95721;
            background: #ffffff;
            box-shadow: 0 0 0 3px rgba(249, 87, 33, 0.12);
        }

        .new-cust-textarea {
            resize: none;
            font-family: inherit;
        }

        /* Order Summary */
        .badge-item-count {
            background: #f1f5f9;
            color: #334155;
            font-family: 'Outfit', sans-serif;
            font-size: 10.5px;
            font-weight: 700;
            padding: 2.5px 7px;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            display: inline-flex;
            align-items: center;
        }

        .order-summary-content {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 10px;
            padding: 10px 12px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .summary-meta-line {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .summary-grand-total-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding-top: 6px;
            border-top: 1px dashed #cbd5e1;
        }

        .summary-payable-text {
            font-family: 'Outfit', sans-serif;
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
        }

        .summary-grand-price {
            font-family: 'Outfit', sans-serif;
            font-size: 19px;
            font-weight: 800;
            color: #f95721;
        }

        /* Payment Methods Grid */
        .payment-methods-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 7px;
        }

        .payment-tile {
            position: relative;
            background: #ffffff;
            border: 1.5px solid #e2e8f0;
            border-radius: 11px;
            padding: 9px 3px 7px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 5px;
            cursor: pointer;
            transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.02);
        }

        .payment-tile:active {
            transform: scale(0.97);
        }

        .payment-tile-icon {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 17px;
            transition: all 0.18s ease;
        }

        .payment-tile-icon.icon-cash {
            background: #ecfdf5;
            color: #059669;
        }

        .payment-tile-icon.icon-card {
            background: #eff6ff;
            color: #2563eb;
        }

        .payment-tile-icon.icon-upi {
            background: #f5f3ff;
            color: #7c3aed;
        }

        .payment-tile-icon.icon-credit {
            background: #fffbeb;
            color: #d97706;
        }

        .payment-tile-title {
            font-family: 'Outfit', sans-serif;
            font-size: 11px;
            font-weight: 700;
            color: #334155;
            transition: all 0.18s ease;
        }

        .payment-tile.active {
            background: #fff7ed;
            border-color: #f95721;
            box-shadow: 0 3px 8px rgba(249, 87, 33, 0.22);
        }

        .payment-tile.active .payment-tile-icon {
            background: #f95721;
            color: #ffffff;
            box-shadow: 0 2px 5px rgba(249, 87, 33, 0.35);
        }

        .payment-tile.active .payment-tile-title {
            color: #ea580c;
            font-weight: 800;
        }

        /* Sticky Checkout Footer */
        .checkout-sticky-footer {
            background: #ffffff;
            border-top: 1px solid #e2e8f0;
            padding: 10px 14px 12px;
            box-shadow: 0 -3px 12px rgba(15, 23, 42, 0.06);
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex-shrink: 0;
        }

        .checkout-footer-meta {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .footer-meta-label {
            font-size: 10px;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .footer-meta-val {
            font-family: 'Outfit', sans-serif;
            font-size: 16px;
            font-weight: 800;
            color: #0f172a;
        }

        .footer-mode-pill {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 20px;
            padding: 3px 9px;
            font-size: 11px;
            font-weight: 700;
            color: #334155;
            display: inline-flex;
            align-items: center;
        }

        .btn-proceed-checkout-main {
            width: 100%;
            height: 46px;
            background: linear-gradient(135deg, #f95721 0%, #ea580c 100%);
            border: none;
            border-radius: 12px;
            color: #ffffff;
            font-family: 'Outfit', sans-serif;
            font-size: 14.5px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 4px 12px rgba(249, 87, 33, 0.35);
            cursor: pointer;
            transition: all 0.15s ease;
        }

        .btn-proceed-checkout-main:active {
            transform: scale(0.98);
            box-shadow: 0 2px 6px rgba(249, 87, 33, 0.25);
        }

        /* ── Screen 6: Invoice Success Screen ── */
        .invoice-success-scroll {
            flex: 1 1 0%;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
            padding: 24px 16px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .success-bill-card {
            width: 100%;
            max-width: 360px;
            background: #eefaf0;
            border: 1.5px solid #a3e6b5;
            border-radius: 18px;
            padding: 30px 20px 26px;
            text-align: center;
            box-shadow: 0 4px 14px rgba(22, 163, 74, 0.08);
            margin: auto 0;
        }

        .success-checkmark-circle {
            width: 54px;
            height: 54px;
            background: #16a34a;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 32px;
            margin-bottom: 14px;
            box-shadow: 0 4px 12px rgba(22, 163, 74, 0.25);
            animation: bounceIn 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            70% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }

        .success-bill-title {
            font-family: 'Outfit', sans-serif;
            font-size: 18px;
            font-weight: 800;
            color: #15803d;
            margin: 0 0 6px;
            letter-spacing: -0.2px;
        }

        .success-invoice-no-text {
            font-family: 'Outfit', -apple-system, sans-serif;
            font-size: 14.5px;
            font-weight: 600;
            color: #166534;
            margin-bottom: 22px;
        }

        .success-action-wrap {
            display: flex;
            justify-content: center;
            width: 100%;
        }

        .btn-success-new-bill {
            background: #ffffff;
            border: 1.5px solid #1e293b;
            color: #0f172a;
            border-radius: 12px;
            padding: 10px 28px;
            font-size: 14.5px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            cursor: pointer;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.04);
            transition: all 0.15s ease;
            width: 100%;
            max-width: 200px;
        }

        .btn-success-new-bill:active {
            transform: scale(0.97);
            background: #f8fafc;
        }

        .btn-success-new-bill .plus-icon {
            font-size: 18px;
            font-weight: 800;
            line-height: 1;
        }

        /* Thermal Slip Preview Card */
        .thermal-slip-card {
            width: 100%;
            background: #ffffff;
            border: 1px dashed #cbd5e1;
            border-radius: 12px;
            padding: 16px 12px;
            font-family: -apple-system, BlinkMacSystemFont, 'Courier New', monospace;
            font-size: 11px;
            color: #000000;
            text-align: left;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            position: relative;
        }

        .thermal-slip-card table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10.5px;
            margin: 8px 0;
        }

        .thermal-slip-card th {
            border-bottom: 1px dashed #000;
            border-top: 1px dashed #000;
            padding: 4px 0;
            font-weight: bold;
        }

        .thermal-slip-card td {
            padding: 3px 0;
        }

        /* ── Modals & Drawers ── */
        .custom-mobile-modal {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(4px);
            z-index: 1060;
            display: flex;
            align-items: flex-end;
            justify-content: center;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.2s ease;
        }

        .custom-mobile-modal.active {
            opacity: 1;
            pointer-events: auto;
        }

        .mobile-modal-sheet {
            background: #ffffff;
            width: 100%;
            max-width: 520px;
            border-top-left-radius: 20px;
            border-top-right-radius: 20px;
            max-height: 85vh;
            display: flex;
            flex-direction: column;
            box-shadow: 0 -8px 30px rgba(0, 0, 0, 0.2);
            transform: translateY(100%);
            transition: transform 0.25s cubic-bezier(0.175, 0.885, 0.32, 1);
        }

        .custom-mobile-modal.active .mobile-modal-sheet {
            transform: translateY(0);
        }

        .modal-sheet-header {
            padding: 12px 16px;
            border-bottom: 1px solid var(--pos-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .modal-sheet-body {
            padding: 14px 16px;
            overflow-y: auto;
            -webkit-overflow-scrolling: touch;
        }

        /* Share Options Grid */
        .share-options-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 12px;
            padding: 8px 0 14px;
            text-align: center;
        }

        .share-app-btn {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 6px;
            background: transparent;
            border: none;
            cursor: pointer;
            padding: 0;
        }

        .share-app-icon {
            width: 48px;
            height: 48px;
            border-radius: 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            color: #ffffff;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
        }

        .share-app-btn span {
            font-size: 11px;
            font-weight: 700;
            color: #334155;
        }
    </style>
</head>

<body>
    <div class="pos-app-shell">

        <!-- ── TOP APP BAR ── -->
        <header class="pos-app-header" id="masterAppHeader">
            <div class="header-left-group">
                <button type="button" class="header-nav-btn" onclick="handleHeaderBack()" title="Back">
                    <iconify-icon icon="solar:arrow-left-linear" class="fs-22 text-white"></iconify-icon>
                </button>
                <div class="header-title-box">
                    <span class="header-app-name" id="lblHeaderAppName">ADC RETAIL POS</span>
                    <span class="header-store-name" id="lblHeaderStoreName">{{ $storeName }}</span>
                </div>
            </div>

            <!-- Clear All button for Cart Screen in Top Header -->
            <button type="button" class="header-clear-cart-btn" id="btnHeaderClearCart" onclick="confirmClearCart()" style="display: none;" title="Clear Cart">
                <iconify-icon icon="solar:trash-bin-trash-linear" class="fs-14 text-white"></iconify-icon>
                <span>Clear All</span>
            </button>

            <div class="header-right-tools" id="headerRightActions">
                <!-- Cart with Circular Orange Badge -->
                <button type="button" class="header-tool-btn header-cart-btn" id="btnHeaderCart" onclick="handleCartClick()" title="View Cart">
                    <div class="cart-icon-badge-box">
                        <iconify-icon icon="solar:cart-large-minimalistic-bold" class="cart-header-icon"></iconify-icon>
                        <span class="header-cart-badge d-none" id="lblHeaderCartCount">0</span>
                    </div>
                </button>

                <div class="header-v-divider"></div>

                <!-- Sale History -->
                <a href="{{ route('adc.pos.orders') }}" class="header-tool-col" title="Sale History">
                    <iconify-icon icon="solar:bill-list-bold-duotone" class="fs-19 text-white"></iconify-icon>
                    <span class="header-col-text">Sale<br>History</span>
                </a>

                <div class="header-v-divider"></div>

                <!-- Sale Return -->
                <a href="{{ route('adc.pos.returns.index') }}" class="header-tool-col" title="Sale Return">
                    <iconify-icon icon="solar:restart-bold" class="fs-19 text-white"></iconify-icon>
                    <span class="header-col-text">Sale<br>Return</span>
                </a>
            </div>
        </header>

        <!-- ═══════════════════════════════════════════════════════════
             VIEW MANAGER SCREENS CONTAINER
             ═══════════════════════════════════════════════════════════ -->
        <main class="pos-views-container">

            <!-- ── SCREEN 1: PRODUCT CATALOG & SEARCH ── -->
            <section class="pos-view-screen" id="screenCatalog">
                <!-- Search Bar with Scan Button -->
                <div class="catalog-search-bar">
                    <div class="search-input-wrap">
                        <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                        <input type="text" id="mobileSearchInput" class="search-input-field"
                            placeholder="Search product / code / barcode" autocomplete="off">
                        <button type="button" class="search-clear-btn" id="btnSearchClear" onclick="clearCatalogSearch()">
                            <iconify-icon icon="solar:close-circle-bold"></iconify-icon>
                        </button>
                    </div>

                    <button type="button" class="btn-scan-trigger" onclick="openCameraScanner()" title="Scan Product Barcode">
                        <iconify-icon icon="solar:scanner-bold-duotone" class="fs-18"></iconify-icon>
                        <span>Scan</span>
                    </button>
                </div>

                <!-- Product Cards Infinite Scroll List -->
                <div class="catalog-scroll-list" id="mobileProductsScrollList">
                    <!-- Dynamic Product Cards Injected Here -->
                </div>

                <!-- Sticky Bottom View-Cart Float Bar (shown if cart > 0) -->
                <div class="bottom-cart-floating-bar d-none" id="bottomCartFloatingBar">
                    <div class="floating-cart-total-info">
                        <span class="floating-cart-items-count" id="lblFloatingItemsCount">0 Items in Cart</span>
                        <span class="floating-cart-grand-val" id="lblFloatingGrandTotal">₹0.00</span>
                    </div>
                    <button type="button" class="btn-floating-view-cart" onclick="handleCartClick()">
                        <span>View Cart</span>
                        <iconify-icon icon="solar:arrow-right-bold" class="fs-16"></iconify-icon>
                    </button>
                </div>
            </section>


            <!-- ── SCREEN 2: CAMERA BARCODE SCANNER OVERLAY ── -->
            <div class="scanner-overlay-view" id="screenScanner">
                <div class="scanner-viewport-box">
                    <div id="cameraPreviewArea"></div>

                    <!-- Reticle Frame Overlay -->
                    <div class="scanner-reticle-frame">
                        <div class="reticle-corner tl"></div>
                        <div class="reticle-corner tr"></div>
                        <div class="reticle-corner bl"></div>
                        <div class="reticle-corner br"></div>
                        <div class="scanner-laser-line"></div>
                    </div>

                    <div class="scanner-instruction-text">
                        Scanning... Align barcode within the frame
                    </div>
                </div>

                <div class="scanner-bottom-controls">
                    <button type="button" class="scanner-ctrl-btn" id="btnToggleFlash" onclick="toggleScannerFlashlight()" title="Toggle Torch">
                        <iconify-icon icon="solar:bolt-bold-duotone"></iconify-icon>
                    </button>
                    <button type="button" class="scanner-capture-btn" onclick="restartScannerCamera()" title="Re-center / Scan">
                        <iconify-icon icon="solar:scanner-bold-duotone"></iconify-icon>
                    </button>
                    <button type="button" class="scanner-ctrl-btn" onclick="closeCameraScanner()" title="Close Scanner">
                        <iconify-icon icon="solar:close-circle-bold"></iconify-icon>
                    </button>
                </div>
            </div>


            <!-- ── SCREEN 4: CURRENT ORDER / CART & DISCOUNT ── -->
            <section class="pos-view-screen slide-right" id="screenCart">
                <!-- Scrollable Cart Items List -->
                <div class="cart-items-list-container" id="cartItemsListContainer">
                    <!-- Dynamic Cart Item Cards Injected Here -->
                </div>

                <!-- Financial Summary & Checkout Action -->
                <div class="cart-summary-bottom-card">
                    <div class="summary-line-row">
                        <span>Sub Total (<span id="lblSummaryTotalItems">0</span> items)</span>
                        <strong id="lblSummarySubtotal">₹0.00</strong>
                    </div>

                    <div class="summary-line-row">
                        <span>Taxable Value</span>
                        <strong id="lblSummaryTaxable">₹0.00</strong>
                    </div>

                    <div class="summary-line-row">
                        <span class="text-success fw-semibold d-inline-flex align-items-center gap-1">
                            <iconify-icon icon="solar:tag-price-bold"></iconify-icon>
                            Discount
                        </span>
                        <strong class="text-success" id="lblSummaryMrpDiscount">-₹0.00</strong>
                    </div>

                    <!-- Special Discount Row -->
                    <div class="summary-discount-row">
                        <span class="text-slate-600">Special Discount (₹)</span>
                        <div class="discount-input-pill" style="width: 85px;">
                            <input type="number" id="inputCartSpecialDiscount" value="0.00" min="0" step="any"
                                style="width: 100%;" oninput="recalcMobileTotals()" onfocus="this.select()" placeholder="0.00">
                        </div>
                    </div>

                    <!-- Tax / GST -->
                    <div class="summary-line-row">
                        <span>Tax / GST</span>
                        <strong id="lblSummaryGstTotal">₹0.00</strong>
                    </div>

                    <!-- Grand Total Banner -->
                    <div class="grand-total-banner-row">
                        <span class="grand-total-label">Grand Total:</span>
                        <span class="grand-total-value" id="lblSummaryGrandTotal">₹0.00</span>
                    </div>

                    <button type="button" class="btn-proceed-action" onclick="showScreen('customer')">
                        <iconify-icon icon="solar:cart-check-bold" class="fs-18"></iconify-icon>
                        <span>Proceed to Customer</span>
                    </button>
                </div>
            </section>


            <!-- ── SCREEN 5: CUSTOMER DETAILS & PAYMENT ── -->
            <section class="pos-view-screen slide-right" id="screenCustomer">
                <div class="customer-scroll-area">
                    <!-- Customer Details Card -->
                    <div class="checkout-section-card">
                        <div class="checkout-card-header mb-2">
                            <div class="checkout-card-title">
                                <iconify-icon icon="solar:user-rounded-bold-duotone" class="text-orange fs-16"></iconify-icon>
                                <span>Customer Details</span>
                            </div>
                        </div>

                        <!-- Mobile Number Input & Search Box -->
                        <div class="cust-phone-input-wrap" id="phoneSearchBoxWrap">
                            <div class="cust-phone-prefix">
                                <span>+91</span>
                            </div>
                            <input type="tel" id="inputSearchCustPhone" class="cust-phone-input"
                                placeholder="Enter Mobile Number (10 digits)" maxlength="10"
                                oninput="handleCustPhoneInput(this.value)">
                            <button class="btn-cust-search" type="button" id="btnExecuteSearchCust"
                                onclick="searchCustomerByPhone()">
                                <iconify-icon icon="solar:magnifer-linear" class="fs-13 me-1"></iconify-icon>Search
                            </button>
                        </div>

                        <!-- Selected Customer Info Pill (compact & sleek when existing customer found) -->
                        <div class="verified-customer-card d-none" id="selectedCustomerPillBox">
                            <div class="verified-cust-info min-w-0">
                                <span class="verified-cust-name" id="lblSelectedCustName">Customer Name</span>
                                <span class="verified-cust-phone" id="lblSelectedCustPhone">+91 0000000000</span>
                            </div>
                            <button type="button" class="btn-change-cust" onclick="clearSelectedCustomer(true)">
                                Change
                            </button>
                        </div>

                        <!-- Form Fields for New Customer (visible when phone is entered but not in DB) -->
                        <div id="newCustomerFormFields" class="new-cust-form-container d-none">
                            <div class="new-cust-input-group">
                                <label class="new-cust-label">
                                    <iconify-icon icon="solar:user-bold" class="fs-12 me-1 text-muted"></iconify-icon>
                                    Customer Name <span class="text-danger ms-0.5">*</span>
                                </label>
                                <input type="text" id="inputNewCustName" placeholder="Enter Customer Full Name" class="new-cust-input">
                            </div>
                            <div class="new-cust-input-group">
                                <label class="new-cust-label">
                                    <iconify-icon icon="solar:letter-bold" class="fs-12 me-1 text-muted"></iconify-icon>
                                    Email Address <span class="text-muted fw-normal ms-1">(Optional)</span>
                                </label>
                                <input type="email" id="inputNewCustEmail" placeholder="e.g. name@example.com" class="new-cust-input">
                            </div>
                            <div class="new-cust-input-group">
                                <label class="new-cust-label">
                                    <iconify-icon icon="solar:map-point-bold" class="fs-12 me-1 text-muted"></iconify-icon>
                                    Address <span class="text-muted fw-normal ms-1">(Optional)</span>
                                </label>
                                <textarea id="inputNewCustAddress" rows="2" placeholder="Street, city, pincode..." class="new-cust-input new-cust-textarea"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Order Summary Box -->
                    <div class="checkout-section-card">
                        <div class="checkout-card-header">
                            <div class="checkout-card-title">
                                <iconify-icon icon="solar:bill-check-bold-duotone" class="text-orange fs-17"></iconify-icon>
                                <span>Order Summary</span>
                            </div>
                            <span class="badge-item-count" id="lblPayTotalItemsBadge">
                                <iconify-icon icon="solar:bag-3-bold" class="fs-11 me-1 text-muted"></iconify-icon>
                                <span id="lblPayTotalItems">0</span> Items
                            </span>
                        </div>
                        <div class="order-summary-content">
                            <div class="summary-meta-line">
                                <span class="text-muted fs-12">Net Bill Amount</span>
                                <span class="fs-11 text-success fw-bold d-inline-flex align-items-center">
                                    <iconify-icon icon="solar:check-circle-bold" class="fs-11 me-1"></iconify-icon>Taxes Included
                                </span>
                            </div>
                            <div class="summary-grand-total-row">
                                <span class="summary-payable-text">Total Payable:</span>
                                <strong class="summary-grand-price font-monospace" id="lblPayGrandTotal">₹0.00</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Method Select -->
                    <div class="checkout-section-card">
                        <div class="checkout-card-header">
                            <div class="checkout-card-title">
                                <iconify-icon icon="solar:card-2-bold-duotone" class="text-orange fs-17"></iconify-icon>
                                <span>Payment Method</span>
                            </div>
                            <span class="badge bg-light text-muted border fs-10 px-2 py-0.5 rounded-pill">Select Mode</span>
                        </div>
                        <div class="payment-methods-grid">
                            <div class="payment-tile active" data-mode="CASH" onclick="selectPaymentMethod('CASH')">
                                <div class="payment-tile-icon icon-cash">
                                    <iconify-icon icon="solar:wallet-money-bold"></iconify-icon>
                                </div>
                                <span class="payment-tile-title">Cash</span>
                            </div>
                            <div class="payment-tile" data-mode="CARD" onclick="selectPaymentMethod('CARD')">
                                <div class="payment-tile-icon icon-card">
                                    <iconify-icon icon="solar:card-bold"></iconify-icon>
                                </div>
                                <span class="payment-tile-title">Card</span>
                            </div>
                            <div class="payment-tile" data-mode="UPI" onclick="selectPaymentMethod('UPI')">
                                <div class="payment-tile-icon icon-upi">
                                    <iconify-icon icon="solar:smartphone-bold"></iconify-icon>
                                </div>
                                <span class="payment-tile-title">UPI</span>
                            </div>
                            <div class="payment-tile" data-mode="CREDIT" onclick="selectPaymentMethod('CREDIT')">
                                <div class="payment-tile-icon icon-credit">
                                    <iconify-icon icon="solar:users-group-two-rounded-bold"></iconify-icon>
                                </div>
                                <span class="payment-tile-title">Credit</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sticky Checkout Submit Button -->
                <div class="checkout-sticky-footer">
                    <div class="checkout-footer-meta">
                        <div class="d-flex flex-column">
                            <span class="footer-meta-label">Total to Pay</span>
                            <span class="footer-meta-val font-monospace" id="lblFooterPayAmount">₹0.00</span>
                        </div>
                        <div class="footer-mode-pill" id="lblFooterPayMode">
                            <iconify-icon icon="solar:wallet-money-bold" class="fs-13 me-1 text-success"></iconify-icon>
                            <span>Cash Payment</span>
                        </div>
                    </div>
                    <button type="button" class="btn-proceed-checkout-main" id="btnMobileCheckoutSubmit" onclick="executeMobileCheckout()">
                        <iconify-icon icon="solar:printer-bold-duotone" class="fs-18 me-1.5"></iconify-icon>
                        <span>Complete Checkout &amp; Print</span>
                    </button>
                </div>
            </section>


            <!-- ── SCREEN 6: INVOICE GENERATED / SUCCESS ── -->
            <section class="pos-view-screen slide-right" id="screenSuccess">
                <div class="invoice-success-scroll">
                    <div class="success-bill-card">
                        <div class="success-checkmark-circle">
                            <iconify-icon icon="solar:check-circle-bold"></iconify-icon>
                        </div>
                        <h4 class="success-bill-title">Billing Completed Successfully</h4>
                        <div class="success-invoice-no-text" id="lblSuccessInvoiceNo">Invoice No: INV_R_0000</div>

                        <div class="success-action-wrap">
                            <button type="button" class="btn-success-new-bill" onclick="executeStartNewBill()">
                                <span class="plus-icon">+</span>
                                <span>New Bill</span>
                            </button>
                        </div>
                    </div>
                </div>
            </section>

        </main>


        <!-- ═══════════════════════════════════════════════════════════
             MODALS & BOTTOM SHEETS
             ═══════════════════════════════════════════════════════════ -->

        <!-- ── MODAL 1: MULTIPLE PRODUCTS FOUND FOR BARCODE ── -->
        <div class="custom-mobile-modal" id="modalMultipleProducts">
            <div class="mobile-modal-sheet">
                <div class="modal-sheet-header">
                    <div>
                        <h6 class="fw-bold mb-0 fs-14" style="font-family: 'Outfit', sans-serif;">Select Product</h6>
                        <span class="text-muted fs-11">Multiple products found for this barcode</span>
                    </div>
                    <button type="button" class="btn-close" onclick="closeMultipleProductsModal()"></button>
                </div>
                <div class="px-3 pt-2">
                    <span class="badge bg-warning text-dark font-monospace px-2.5 py-1 fs-11" id="lblMultipleBarcodeTag">
                        Barcode: —
                    </span>
                </div>
                <div class="modal-sheet-body" id="multipleProductsListBody" style="max-height: 55vh; display: flex; flex-direction: column; gap: 8px;">
                    <!-- Dynamic Product Choice Items Injected Here -->
                </div>
                <div class="p-3 border-top">
                    <button type="button" class="btn btn-sm btn-light w-100 fw-bold py-2" onclick="closeMultipleProductsModal()">
                        Cancel
                    </button>
                </div>
            </div>
        </div>


        <!-- ── MODAL 2: VIEW INVOICE MODAL ── -->
        <div class="custom-mobile-modal" id="modalViewInvoice">
            <div class="mobile-modal-sheet" style="max-height: 90vh;">
                <div class="modal-sheet-header">
                    <h6 class="fw-bold mb-0 fs-14" style="font-family: 'Outfit', sans-serif;">View Invoice Receipt</h6>
                    <button type="button" class="btn-close" onclick="closeViewInvoiceModal()"></button>
                </div>
                <div class="modal-sheet-body p-2" id="viewInvoiceFrameContainer" style="height: 60vh;">
                    <iframe id="invoiceReceiptIframe" src="about:blank" style="width: 100%; height: 100%; border: none;"></iframe>
                </div>
                <div class="p-3 border-top d-flex gap-2">
                    <button type="button" class="btn-invoice-action primary flex-grow-1" onclick="printSuccessInvoice()">
                        <iconify-icon icon="solar:printer-bold-duotone" class="fs-16"></iconify-icon>
                        <span>Print</span>
                    </button>
                    <button type="button" class="btn-invoice-action share flex-grow-1" onclick="openShareInvoiceModal()">
                        <iconify-icon icon="solar:share-bold-duotone" class="fs-16"></iconify-icon>
                        <span>Share</span>
                    </button>
                </div>
            </div>
        </div>


        <!-- ── MODAL 3: SHARE INVOICE MODAL ── -->
        <div class="custom-mobile-modal" id="modalShareInvoice">
            <div class="mobile-modal-sheet">
                <div class="modal-sheet-header">
                    <h6 class="fw-bold mb-0 fs-14" style="font-family: 'Outfit', sans-serif;">Share Invoice</h6>
                    <button type="button" class="btn-close" onclick="closeShareInvoiceModal()"></button>
                </div>
                <div class="modal-sheet-body">
                    <div class="share-options-grid">
                        <!-- WhatsApp Direct Share -->
                        <button type="button" class="share-app-btn" onclick="shareViaWhatsApp()">
                            <div class="share-app-icon" style="background: #25d366;">
                                <iconify-icon icon="solar:chat-round-dots-bold"></iconify-icon>
                            </div>
                            <span>WhatsApp</span>
                        </button>

                        <!-- Save / Download PDF -->
                        <button type="button" class="share-app-btn" onclick="shareViaPdfDownload()">
                            <div class="share-app-icon" style="background: #ef4444;">
                                <iconify-icon icon="solar:file-download-bold"></iconify-icon>
                            </div>
                            <span>Save PDF</span>
                        </button>

                        <!-- Email / Gmail -->
                        <button type="button" class="share-app-btn" onclick="shareViaEmail()">
                            <div class="share-app-icon" style="background: #ea4335;">
                                <iconify-icon icon="solar:letter-bold"></iconify-icon>
                            </div>
                            <span>Gmail</span>
                        </button>

                        <!-- More Apps / Web Share API -->
                        <button type="button" class="share-app-btn" onclick="shareViaWebShareApi()">
                            <div class="share-app-icon" style="background: #3b82f6;">
                                <iconify-icon icon="solar:share-circle-bold"></iconify-icon>
                            </div>
                            <span>More Apps</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>


        <!-- ── MODAL 4: START NEW BILL CONFIRMATION MODAL ── -->
        <div class="custom-mobile-modal" id="modalNewBillConfirm">
            <div class="mobile-modal-sheet" style="text-align: center; padding: 20px 16px;">
                <div class="avatar-lg bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 60px; height: 60px;">
                    <iconify-icon icon="solar:question-circle-bold" class="fs-36 text-primary"></iconify-icon>
                </div>
                <h5 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">Start New Bill?</h5>
                <p class="text-muted fs-12 mb-3">Current cart will be cleared and you can start a new retail billing session.</p>
                <div class="d-flex gap-2">
                    <button type="button" class="btn btn-sm btn-light flex-grow-1 py-2 fw-bold" onclick="closeNewBillConfirmModal()">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-sm btn-primary flex-grow-1 py-2 fw-bold" onclick="executeStartNewBill()">
                        Yes, New Bill
                    </button>
                </div>
            </div>
        </div>


        <!-- ── MODAL 5: BILL HISTORY / RECENT BILLS ── -->
        <div class="custom-mobile-modal" id="modalBillHistory">
            <div class="mobile-modal-sheet" style="max-height: 85vh;">
                <div class="modal-sheet-header">
                    <h6 class="fw-bold mb-0 fs-14" style="font-family: 'Outfit', sans-serif;">Recent POS Bills</h6>
                    <button type="button" class="btn-close" onclick="closeBillHistoryModal()"></button>
                </div>
                <div class="modal-sheet-body p-2" id="billHistoryListContainer" style="display: flex; flex-direction: column; gap: 8px;">
                    @forelse($recentBills as $bill)
                        <div class="d-flex align-items-center justify-content-between p-2.5 rounded-3 border bg-white shadow-xs">
                            <div>
                                <strong class="fs-12 text-slate-800 d-block font-monospace">
                                    {{ $bill->invoice?->invoice_number ?: $bill->order_no }}
                                </strong>
                                <span class="fs-10 text-muted">
                                    {{ $bill->created_at->format('d-M-Y h:i A') }} • {{ $bill->customer_name ?: 'Walk-in' }}
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="fw-bold fs-13 font-monospace text-slate-800">₹{{ number_format($bill->total_amount, 2) }}</span>
                                <button type="button" class="btn btn-xs btn-outline-primary py-1 px-2 fw-bold fs-11 rounded-pill"
                                    onclick="previewHistoricalInvoice({{ $bill->id }}, '{{ $bill->invoice?->invoice_number ?: $bill->order_no }}')">
                                    View
                                </button>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted fs-12">
                            No recent bills issued yet today.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

    </div>

    <!-- ═══════════════════════════════════════════════════════════
         SCRIPTS & CAMERA BARCODE SCANNER ENGINE
         ═══════════════════════════════════════════════════════════ -->
    <script src="https://code.iconify.design/iconify-icon/2.1.0/iconify-icon.min.js"></script>
    <link rel="stylesheet" type="text/css" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">
    <script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>
    <script src="{{ asset('plugins/toastify/toastify.min.js') }}"></script>
    <!-- Local Fast HTML5-QRCode Scanner Library -->
    <script src="{{ asset('admin-assets/js/html5-qrcode.min.js') }}"></script>

    <script>
        const posMode = 'retail';
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        // Application State
        let cart = [];
        let selectedCustomer = null;
        let selectedPaymentMode = 'CASH';
        let discountUnitMode = '%'; // '%' or '₹'
        let lastSuccessfulOrder = null;

        // Infinite Scroll & Search State
        let catalogPage = 1;
        let catalogHasMore = true;
        let catalogIsLoading = false;
        let catalogSearchQuery = '';
        let catalogSearchDebounce = null;

        // Camera Scanner State
        let html5QrScanner = null;
        let isScannerRunning = false;
        let isTorchOn = false;
        let scannerTorchTrack = null;

        // Navigation History Stack
        let screenHistory = ['catalog'];

        /* ── Sound Synthesizer (Zero-Latency Barcode Beep) ── */
        function playBarcodeBeep() {
            try {
                const audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = audioCtx.createOscillator();
                const gain = audioCtx.createGain();
                osc.type = 'sine';
                osc.frequency.setValueAtTime(880, audioCtx.currentTime); // 880 Hz (A5)
                gain.gain.setValueAtTime(0.25, audioCtx.currentTime);
                gain.gain.exponentialRampToValueAtTime(0.001, audioCtx.currentTime + 0.12);
                osc.connect(gain);
                gain.connect(audioCtx.destination);
                osc.start();
                osc.stop(audioCtx.currentTime + 0.12);
                if (navigator.vibrate) navigator.vibrate(60);
            } catch (e) {
                console.log("Audio beep bypassed", e);
            }
        }

        /* ── Screen Navigation Router & Dynamic Master Header ── */
        let currentScreen = 'catalog';

        function showScreen(screenId, pushHistory = true) {
            if (screenId === 'cart' && cart.length === 0) {
                Toastify({
                    text: "Cart is empty! Please add products from catalog.",
                    backgroundColor: "#f59e0b",
                    duration: 2000,
                    gravity: "bottom",
                    position: "center"
                }).showToast();
                if (currentScreen !== 'catalog') {
                    showScreen('catalog', false);
                }
                return;
            }

            const screens = ['catalog', 'cart', 'customer', 'success'];
            const target = document.getElementById('screen' + screenId.charAt(0).toUpperCase() + screenId.slice(1));
            if (!target) return;

            currentScreen = screenId;

            screens.forEach(s => {
                const el = document.getElementById('screen' + s.charAt(0).toUpperCase() + s.slice(1));
                if (el) {
                    if (s === screenId) {
                        el.classList.remove('slide-right', 'slide-left');
                    } else {
                        el.classList.add('slide-right');
                    }
                }
            });

            if (pushHistory && screenHistory[screenHistory.length - 1] !== screenId) {
                screenHistory.push(screenId);
            }

            // Dynamically manage master header based on active screen
            const headerRight = document.getElementById('headerRightActions');
            const headerAppName = document.getElementById('lblHeaderAppName');
            const headerClearCart = document.getElementById('btnHeaderClearCart');
            if (headerRight) {
                headerRight.style.display = (screenId === 'catalog') ? 'flex' : 'none';
            }
            if (headerClearCart) {
                headerClearCart.style.display = (screenId === 'cart') ? 'inline-flex' : 'none';
            }
            if (headerAppName) {
                if (screenId === 'cart') {
                    headerAppName.textContent = 'Cart';
                } else if (screenId === 'customer') {
                    headerAppName.textContent = 'Checkout';
                } else if (screenId === 'success') {
                    headerAppName.textContent = 'Order Placed';
                } else {
                    headerAppName.textContent = 'ADC RETAIL POS';
                }
            }

            if (screenId === 'cart') {
                renderCartScreen();
            } else if (screenId === 'customer') {
                const totalItems = cart.reduce((acc, i) => acc + i.qty, 0);
                const payTotalItems = document.getElementById('lblPayTotalItems');
                if (payTotalItems) payTotalItems.innerText = totalItems;
                const grandTotalEl = document.getElementById('lblSummaryGrandTotal');
                const payGrandTotal = document.getElementById('lblPayGrandTotal');
                const footerPayAmount = document.getElementById('lblFooterPayAmount');
                const totalText = grandTotalEl ? grandTotalEl.innerText : '₹0.00';
                if (payGrandTotal) payGrandTotal.innerText = totalText;
                if (footerPayAmount) footerPayAmount.innerText = totalText;
            }
        }

        function handleCartClick() {
            if (cart.length === 0) {
                Toastify({
                    text: "Cart is empty! Please add products from catalog.",
                    backgroundColor: "#f59e0b",
                    duration: 2000,
                    gravity: "bottom",
                    position: "center"
                }).showToast();
                return;
            }
            showScreen('cart');
        }

        function handleHeaderBack() {
            // Close camera scanner overlay if active
            const scannerView = document.getElementById('screenScanner');
            if (isScannerRunning || (scannerView && scannerView.classList.contains('active'))) {
                closeCameraScanner();
                return;
            }

            // Close any open custom modal sheet if active
            const activeModal = document.querySelector('.custom-mobile-modal.active');
            if (activeModal) {
                activeModal.classList.remove('active');
                return;
            }

            if (screenHistory.length > 1) {
                screenHistory.pop();
                const prev = screenHistory[screenHistory.length - 1];
                showScreen(prev, false);
            } else {
                window.location.href = "{{ route('adc.dashboard') }}";
            }
        }

        /* ── Product Catalog & Infinite Scroll ── */
        document.addEventListener('DOMContentLoaded', function () {
            fetchCatalogProducts(true);
            loadSavedCartFromBackend();

            // Catalog Scroll Listener
            const scrollList = document.getElementById('mobileProductsScrollList');
            scrollList.addEventListener('scroll', function () {
                if (scrollList.scrollTop + scrollList.clientHeight >= scrollList.scrollHeight - 150) {
                    if (catalogHasMore && !catalogIsLoading) {
                        fetchCatalogProducts(false);
                    }
                }
            });

            // Live Search Input with Debounce
            const searchInput = document.getElementById('mobileSearchInput');
            const clearBtn = document.getElementById('btnSearchClear');

            searchInput.addEventListener('input', function () {
                catalogSearchQuery = this.value.trim();
                clearBtn.style.display = catalogSearchQuery.length > 0 ? 'block' : 'none';

                clearTimeout(catalogSearchDebounce);
                catalogSearchDebounce = setTimeout(() => {
                    fetchCatalogProducts(true);
                }, 300);
            });

            // Enter key trigger barcode/search scan
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    clearTimeout(catalogSearchDebounce);
                    const val = this.value.trim();
                    if (val.length > 0) {
                        executeBarcodeScanQuery(val);
                    }
                }
            });
        });

        function clearCatalogSearch() {
            const input = document.getElementById('mobileSearchInput');
            input.value = '';
            document.getElementById('btnSearchClear').style.display = 'none';
            catalogSearchQuery = '';
            fetchCatalogProducts(true);
        }

        function fetchCatalogProducts(reset = false) {
            if (catalogIsLoading) return;

            if (reset) {
                catalogPage = 1;
                catalogHasMore = true;
                const scrollList = document.getElementById('mobileProductsScrollList');
                if (scrollList) scrollList.innerHTML = '';
            }

            if (!catalogHasMore) return;

            catalogIsLoading = true;

            const url = `{{ route('adc.pos.products') }}?mode=${posMode}&page=${catalogPage}&search=${encodeURIComponent(catalogSearchQuery)}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    catalogIsLoading = false;

                    if (data.status) {
                        catalogHasMore = data.has_more;

                        if (data.items && data.items.length > 0) {
                            renderCatalogCards(data.items);
                            catalogPage++;

                            // Auto-load next page if screen has space and more items exist
                            const scrollList = document.getElementById('mobileProductsScrollList');
                            if (catalogHasMore && !catalogIsLoading && scrollList && scrollList.scrollHeight <= scrollList.clientHeight + 100) {
                                setTimeout(() => fetchCatalogProducts(false), 100);
                            }
                        } else if (catalogPage === 1) {
                            renderCatalogEmptyState();
                        }
                    } else if (catalogPage === 1) {
                        renderCatalogEmptyState();
                    }
                })
                .catch(err => {
                    catalogIsLoading = false;
                    console.error("Error loading products:", err);
                    if (catalogPage === 1) {
                        renderCatalogEmptyState();
                    }
                });
        }

        function renderCatalogEmptyState() {
            const list = document.getElementById('mobileProductsScrollList');
            if (!list) return;
            list.innerHTML = `
                <div class="pos-empty-catalog-state" id="catalogEmptyState">
                    <div class="empty-bag-illustration-wrap">
                        <svg width="200" height="190" viewBox="0 0 200 190" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <!-- Drop Shadow Oval -->
                            <ellipse cx="100" cy="172" rx="72" ry="14" fill="#E1EAFB" />

                            <!-- Bag Back Handle -->
                            <path d="M78 78 C78 52 116 52 116 78" stroke="#7BA2FF" stroke-width="6.5" stroke-linecap="round" fill="none" />

                            <!-- Bag 3D Gusset Side Panel -->
                            <path d="M125 76 L148 83 L137 165 L118 162 Z" fill="#6B96F8" />

                            <!-- Bag Front Face Gradient -->
                            <path d="M56 82 L125 76 L118 162 L51 156 Z" fill="url(#emptyBagGradient)" />

                            <!-- Front Top Fold Shadow -->
                            <path d="M56 82 L125 76 L124 81 L57 87 Z" fill="#5A83E8" opacity="0.45" />

                            <!-- Bag Front Handle -->
                            <path d="M86 77 C86 54 122 54 122 77" stroke="#A7C4FF" stroke-width="6.5" stroke-linecap="round" fill="none" />

                            <!-- White Shopping Cart Icon Stamped on Bag -->
                            <g transform="translate(68, 104) scale(0.85)">
                                <path d="M3 5 H10 L15 32 H41 L47 13 H13" stroke="#FFFFFF" stroke-width="4" stroke-linecap="round" stroke-linejoin="round" fill="none" />
                                <circle cx="19" cy="40" r="3.5" fill="#FFFFFF" />
                                <circle cx="37" cy="40" r="3.5" fill="#FFFFFF" />
                            </g>

                            <!-- 3 Orange Accent Rays / Sparkles on Top-Right -->
                            <path d="M142 46 L148 37" stroke="#F95721" stroke-width="4" stroke-linecap="round" />
                            <path d="M154 53 L166 48" stroke="#F95721" stroke-width="4" stroke-linecap="round" />
                            <path d="M152 68 L164 73" stroke="#F95721" stroke-width="4" stroke-linecap="round" />

                            <defs>
                                <linearGradient id="emptyBagGradient" x1="56" y1="76" x2="125" y2="162" gradientUnits="userSpaceOnUse">
                                    <stop stop-color="#93B8FF" />
                                    <stop offset="0.6" stop-color="#79A2FC" />
                                    <stop offset="1" stop-color="#6791F7" />
                                </linearGradient>
                            </defs>
                        </svg>
                    </div>
                    <h3 class="pos-empty-title">No Product Found</h3>
                    <p class="pos-empty-subtitle">Please search for a product, scan a barcode<br>or enter product code.</p>
                </div>
            `;
        }

        function renderCatalogCards(items) {
            const list = document.getElementById('mobileProductsScrollList');

            items.forEach(inv => {
                const cartItem = cart.find(i => i.id === inv.id);
                const isOutOfStock = inv.quantity <= 0;

                const card = document.createElement('div');
                card.className = 'mobile-product-card';
                card.id = `prodCard_${inv.id}`;

                const imgHtml = inv.image_url
                    ? `<img src="${inv.image_url}" alt="${inv.product_name}">`
                    : `<iconify-icon icon="solar:box-bold-duotone" class="fs-24 text-muted"></iconify-icon>`;

                const stockBadge = isOutOfStock
                    ? `<span class="product-stock-badge out-of-stock">Out of Stock</span>`
                    : `<span class="product-stock-badge">In Stock (${inv.quantity})</span>`;

                const mrpVal = parseFloat(inv.mrp) || 0;
                const priceVal = parseFloat(inv.price) || 0;

                let priceBlockHtml = '';
                if (mrpVal > priceVal) {
                    const discPercent = Math.round(((mrpVal - priceVal) / mrpVal) * 100);
                    priceBlockHtml = `
                        <div class="text-end">
                            <div class="d-flex align-items-center justify-content-end gap-1 flex-wrap mb-0.5">
                                <span class="product-mrp-strike" title="MRP">₹${mrpVal.toFixed(2)}</span>
                                <span class="product-disc-badge">-${discPercent}%</span>
                            </div>
                            <div class="product-price-val">₹${priceVal.toFixed(2)}</div>
                        </div>
                    `;
                } else {
                    priceBlockHtml = `
                        <div class="text-end">
                            <div class="product-price-val">₹${priceVal.toFixed(2)}</div>
                        </div>
                    `;
                }

                let actionHtml = '';
                if (isOutOfStock) {
                    actionHtml = `<span class="badge bg-secondary-subtle text-muted fs-10 px-2 py-1">Unavailable</span>`;
                } else {
                    actionHtml = `
                        <button type="button" class="btn-add-cart-pill" onclick="addProductToCart(${JSON.stringify(inv).replace(/"/g, '&quot;')})">
                            + Add
                        </button>
                    `;
                }

                card.innerHTML = `
                    <div class="product-thumb-box">${imgHtml}</div>
                    <div class="product-info-box">
                        <div class="product-name-title" title="${inv.product_name}">${inv.product_name}</div>
                        <div class="product-meta-sub">
                            <div>Code: ${inv.product_code || '—'}</div>
                            ${inv.barcode ? `<div>Barcode: ${inv.barcode}</div>` : ''}
                        </div>
                        <div class="mt-0.5">${stockBadge}</div>
                    </div>
                    <div class="product-price-action-box">
                        ${priceBlockHtml}
                        <div id="cardActionWrap_${inv.id}">
                            ${actionHtml}
                        </div>
                    </div>
                `;

                list.appendChild(card);
            });
        }

        /* ── Barcode Scanning Query & Multi-Match Handler ── */
        function executeBarcodeScanQuery(barcodeVal) {
            const url = `{{ route('adc.pos.products') }}?mode=${posMode}&page=1&search=${encodeURIComponent(barcodeVal)}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.status && data.items && data.items.length > 0) {
                        playBarcodeBeep();

                        if (data.items.length === 1) {
                            // Automatically add to cart if exactly 1 match
                            addProductToCart(data.items[0]);
                            Toastify({
                                text: `✓ Added to Cart: ${data.items[0].product_name}`,
                                duration: 2500,
                                backgroundColor: "#10b981",
                                gravity: "top",
                                position: "center"
                            }).showToast();

                            if (isScannerRunning) {
                                closeCameraScanner();
                            }
                        } else {
                            // Multiple products found: Show Select Product Modal
                            if (isScannerRunning) {
                                closeCameraScanner();
                            }
                            openMultipleProductsModal(data.items, barcodeVal);
                        }
                    } else {
                        Toastify({
                            text: `No product found for barcode '${barcodeVal}'`,
                            duration: 3000,
                            backgroundColor: "#ef4444",
                            gravity: "top",
                            position: "center"
                        }).showToast();
                    }
                })
                .catch(err => console.error("Barcode scan query error:", err));
        }

        function openMultipleProductsModal(items, barcode) {
            document.getElementById('lblMultipleBarcodeTag').innerText = `Barcode: ${barcode}`;
            const container = document.getElementById('multipleProductsListBody');
            container.innerHTML = '';

            items.forEach(inv => {
                const card = document.createElement('div');
                card.className = 'd-flex align-items-center justify-content-between p-2 rounded-3 border bg-light';

                const imgHtml = inv.image_url
                    ? `<img src="${inv.image_url}" style="width: 42px; height: 42px; object-fit: contain;">`
                    : `<iconify-icon icon="solar:box-bold-duotone" class="fs-24 text-muted"></iconify-icon>`;

                const mMrp = parseFloat(inv.mrp) || 0;
                const mPrice = parseFloat(inv.price) || 0;
                let mPriceHtml = `<strong class="fs-13 text-slate-800">₹${mPrice.toFixed(2)}</strong>`;
                if (mMrp > mPrice) {
                    const mDisc = Math.round(((mMrp - mPrice) / mMrp) * 100);
                    mPriceHtml = `<span class="product-mrp-strike me-1">₹${mMrp.toFixed(2)}</span><strong class="fs-13 text-slate-800 me-1">₹${mPrice.toFixed(2)}</strong><span class="product-disc-badge">-${mDisc}%</span>`;
                }

                card.innerHTML = `
                    <div class="d-flex align-items-center gap-2" style="min-width: 0;">
                        <div class="rounded-2 bg-white border p-1" style="width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;">
                            ${imgHtml}
                        </div>
                        <div style="min-width: 0;">
                            <h6 class="fw-bold mb-0 fs-12 text-truncate" style="max-width: 170px;">${inv.product_name}</h6>
                            <span class="fs-10 text-muted d-block">Code: ${inv.product_code || '—'} • Stock: ${inv.quantity}</span>
                            <div class="mt-0.5">${mPriceHtml}</div>
                        </div>
                    </div>
                    <button type="button" class="btn-add-cart-pill flex-shrink-0" onclick='addProductFromMultipleModal(${JSON.stringify(inv)})'>
                        + Add
                    </button>
                `;
                container.appendChild(card);
            });

            document.getElementById('modalMultipleProducts').classList.add('active');
        }

        function addProductFromMultipleModal(inv) {
            addProductToCart(inv);
            closeMultipleProductsModal();
            Toastify({ text: `✓ Added: ${inv.product_name}`, backgroundColor: "#10b981", duration: 2000 }).showToast();
        }

        function closeMultipleProductsModal() {
            document.getElementById('modalMultipleProducts').classList.remove('active');
        }

        /* ── Camera Barcode Scanner Engine (HTML5-QRCode) ── */
        function openCameraScanner() {
            const scannerView = document.getElementById('screenScanner');
            scannerView.classList.add('active');
            isScannerRunning = true;

            // Master Header update for Scanner view
            const headerAppName = document.getElementById('lblHeaderAppName');
            if (headerAppName) {
                headerAppName.textContent = 'Scan Barcode';
            }
            const headerRight = document.getElementById('headerRightActions');
            if (headerRight) {
                headerRight.style.display = 'none';
            }
            const headerClearCart = document.getElementById('btnHeaderClearCart');
            if (headerClearCart) {
                headerClearCart.style.display = 'none';
            }

            const previewArea = document.getElementById('cameraPreviewArea');
            previewArea.innerHTML = '';

            html5QrScanner = new Html5Qrcode("cameraPreviewArea");

            const qrConfig = {
                fps: 15,
                qrbox: { width: 250, height: 250 },
                aspectRatio: 1.0
            };

            html5QrScanner.start(
                { facingMode: "environment" },
                qrConfig,
                onBarcodeScannedSuccess,
                onBarcodeScanFailure
            ).then(() => {
                // Check if torch track is available
                try {
                    const videoElem = document.querySelector("#cameraPreviewArea video");
                    if (videoElem && videoElem.srcObject) {
                        const track = videoElem.srcObject.getVideoTracks()[0];
                        const capabilities = track.getCapabilities ? track.getCapabilities() : {};
                        if (capabilities.torch) {
                            scannerTorchTrack = track;
                            document.getElementById('btnToggleFlash').style.display = 'flex';
                        }
                    }
                } catch (e) {}
            }).catch(err => {
                console.error("Camera scanner start failed:", err);
                Toastify({
                    text: "Could not access camera. Please check permissions.",
                    backgroundColor: "#ef4444",
                    duration: 3500
                }).showToast();
                closeCameraScanner();
            });
        }

        let lastScannedCode = '';
        let lastScannedTime = 0;

        function onBarcodeScannedSuccess(decodedText, decodedResult) {
            const now = Date.now();
            if (decodedText === lastScannedCode && (now - lastScannedTime) < 2500) {
                return; // Prevent duplicate rapid scans of same item
            }
            lastScannedCode = decodedText;
            lastScannedTime = now;

            executeBarcodeScanQuery(decodedText);
        }

        function onBarcodeScanFailure(error) {
            // Scanning in progress
        }

        function toggleScannerFlashlight() {
            if (!scannerTorchTrack) {
                Toastify({ text: "Flashlight not supported on this device/camera", duration: 2000 }).showToast();
                return;
            }
            isTorchOn = !isTorchOn;
            scannerTorchTrack.applyConstraints({
                advanced: [{ torch: isTorchOn }]
            }).then(() => {
                const btn = document.getElementById('btnToggleFlash');
                if (isTorchOn) btn.classList.add('active');
                else btn.classList.remove('active');
            }).catch(e => console.log("Flash error", e));
        }

        function restartScannerCamera() {
            if (html5QrScanner && isScannerRunning) {
                html5QrScanner.stop().then(() => {
                    openCameraScanner();
                }).catch(() => openCameraScanner());
            }
        }

        function closeCameraScanner() {
            isScannerRunning = false;
            isTorchOn = false;
            document.getElementById('screenScanner').classList.remove('active');

            // Restore master header according to current screen
            const headerAppName = document.getElementById('lblHeaderAppName');
            const headerRight = document.getElementById('headerRightActions');
            const headerClearCart = document.getElementById('btnHeaderClearCart');

            if (headerAppName) {
                if (currentScreen === 'cart') {
                    headerAppName.textContent = 'Cart';
                } else if (currentScreen === 'customer') {
                    headerAppName.textContent = 'Checkout';
                } else if (currentScreen === 'success') {
                    headerAppName.textContent = 'Order Placed';
                } else {
                    headerAppName.textContent = 'ADC RETAIL POS';
                }
            }

            if (headerRight) {
                headerRight.style.display = (currentScreen === 'catalog') ? 'flex' : 'none';
            }

            if (headerClearCart) {
                headerClearCart.style.display = (currentScreen === 'cart') ? 'inline-flex' : 'none';
            }

            if (html5QrScanner) {
                html5QrScanner.stop().then(() => {
                    html5QrScanner.clear();
                    html5QrScanner = null;
                }).catch(() => {
                    html5QrScanner = null;
                });
            }
        }

        /* ── Cart Operations & Business Calculations ── */
        function addProductToCart(inv) {
            if (inv.quantity <= 0) {
                Toastify({ text: `'${inv.product_name}' is out of stock!`, backgroundColor: "#ef4444" }).showToast();
                return;
            }

            if (inv.expire_date) {
                const exp = new Date(inv.expire_date + 'T00:00:00');
                const today = new Date();
                today.setHours(0, 0, 0, 0);
                if (exp < today) {
                    Toastify({ text: `'${inv.product_name}' has expired! Cannot sell.`, backgroundColor: "#ef4444" }).showToast();
                    return;
                }
            }

            let existing = cart.find(i => i.id === inv.id);
            if (existing) {
                if (existing.qty < inv.quantity) {
                    existing.qty++;
                } else {
                    Toastify({ text: `Cannot exceed stock (${inv.quantity})`, backgroundColor: "#ef4444" }).showToast();
                    return;
                }
            } else {
                cart.push({
                    id: inv.id,
                    name: inv.product_name,
                    code: inv.product_code,
                    barcode: inv.barcode,
                    price: parseFloat(inv.price) || 0,
                    mrp: (parseFloat(inv.mrp) > 0 ? parseFloat(inv.mrp) : (parseFloat(inv.price) || 0)),
                    gst_rate: parseFloat(inv.gst_rate || 0),
                    qty: 1,
                    stock: inv.quantity,
                    image_url: inv.image_url,
                    unit_of_measure: inv.unit_of_measure || 'Unit'
                });
            }

            updateCartHeaderAndFloatingBadge();
            syncCartToDatabase();
            Toastify({
                text: `✓ Added: ${inv.product_name}`,
                duration: 1500,
                backgroundColor: "#10b981",
                gravity: "bottom",
                position: "center"
            }).showToast();
        }

        function changeCartQty(id, delta) {
            const item = cart.find(i => i.id === id);
            if (!item) return;

            const newQty = item.qty + delta;
            if (newQty <= 0) {
                cart = cart.filter(i => i.id !== id);
            } else if (newQty > item.stock) {
                Toastify({ text: `Cannot exceed stock (${item.stock})`, backgroundColor: "#ef4444" }).showToast();
                return;
            } else {
                item.qty = newQty;
            }

            updateCatalogCardAction(id);
            updateCartHeaderAndFloatingBadge();
            if (cart.length === 0) {
                if (currentScreen === 'cart') {
                    showScreen('catalog');
                }
                Toastify({ text: "Cart is empty.", backgroundColor: "#64748b", duration: 1500 }).showToast();
            } else if (document.getElementById('screenCart').classList.contains('slide-right') === false) {
                renderCartScreen();
            }
            syncCartToDatabase();
        }

        function setCartItemExactQty(id, val) {
            const item = cart.find(i => i.id === id);
            if (!item) return;

            const num = parseFloat(val);
            if (isNaN(num) || num <= 0) {
                cart = cart.filter(i => i.id !== id);
            } else if (num > item.stock) {
                item.qty = item.stock;
                Toastify({ text: `Maximum available stock: ${item.stock}`, backgroundColor: "#ef4444" }).showToast();
            } else {
                item.qty = num;
            }

            updateCatalogCardAction(id);
            updateCartHeaderAndFloatingBadge();
            if (cart.length === 0) {
                if (currentScreen === 'cart') {
                    showScreen('catalog');
                }
                Toastify({ text: "Cart is empty.", backgroundColor: "#64748b", duration: 1500 }).showToast();
            } else {
                renderCartScreen();
            }
            syncCartToDatabase();
        }

        function removeCartItem(id) {
            cart = cart.filter(i => i.id !== id);
            updateCatalogCardAction(id);
            updateCartHeaderAndFloatingBadge();
            if (cart.length === 0) {
                if (currentScreen === 'cart') {
                    showScreen('catalog');
                }
                Toastify({ text: "Cart is empty.", backgroundColor: "#64748b", duration: 1500 }).showToast();
            } else {
                renderCartScreen();
            }
            syncCartToDatabase();
        }

        function confirmClearCart() {
            if (cart.length === 0) return;

            Swal.fire({
                title: 'Clear Current Cart?',
                text: 'All items in your cart will be removed.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Yes, Clear'
            }).then((res) => {
                if (res.isConfirmed) {
                    cart = [];
                    document.querySelectorAll('[id^="cardActionWrap_"]').forEach(el => {
                        const id = el.id.replace('cardActionWrap_', '');
                        updateCatalogCardAction(id);
                    });
                    updateCartHeaderAndFloatingBadge();
                    if (currentScreen === 'cart') {
                        showScreen('catalog');
                    }
                    Toastify({ text: "Cart cleared.", backgroundColor: "#64748b", duration: 1500 }).showToast();
                    fetch("{{ route('adc.pos.cart.clear') }}", {
                        method: "POST",
                        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken },
                        body: JSON.stringify({ mode: posMode })
                    }).catch(e => console.log(e));
                }
            });
        }

        function updateCatalogCardAction(id) {
            // Keep "+ Add" button always visible on product cards as requested
        }

        function fetchAndAddProductById(id) {
            fetch(`{{ route('adc.pos.products') }}?mode=${posMode}&search=${id}`)
                .then(r => r.json())
                .then(d => {
                    if (d.status && d.items) {
                        const match = d.items.find(i => i.id == id);
                        if (match) addProductToCart(match);
                    }
                });
        }

        function updateCartHeaderAndFloatingBadge() {
            const totalCount = cart.reduce((acc, i) => acc + i.qty, 0);
            const badge = document.getElementById('lblHeaderCartCount');
            const floatBar = document.getElementById('bottomCartFloatingBar');

            if (totalCount > 0) {
                badge.innerText = totalCount;
                badge.classList.remove('d-none');
                badge.style.transform = 'scale(1.25)';
                setTimeout(() => badge.style.transform = 'scale(1)', 200);

                floatBar.classList.remove('d-none');
                document.getElementById('lblFloatingItemsCount').innerText = `${totalCount} Item${totalCount > 1 ? 's' : ''} in Cart`;
                document.getElementById('lblFloatingGrandTotal').innerText = calculateGrandTotalString();
            } else {
                badge.classList.add('d-none');
                floatBar.classList.add('d-none');
            }
        }

        function renderCartScreen() {
            if (cart.length === 0) {
                if (currentScreen === 'cart') {
                    showScreen('catalog', false);
                }
                return;
            }

            const container = document.getElementById('cartItemsListContainer');
            const totalCount = cart.reduce((acc, i) => acc + i.qty, 0);

            const lblCartTitle = document.getElementById('lblCartScreenTitle');
            if (lblCartTitle) {
                lblCartTitle.innerText = `Order Items (${totalCount})`;
            }
            const summaryTotalItems = document.getElementById('lblSummaryTotalItems');
            if (summaryTotalItems) summaryTotalItems.innerText = totalCount;

            container.innerHTML = '';
            cart.forEach(item => {
                const lineTotal = item.price * item.qty;
                const card = document.createElement('div');
                card.className = 'cart-item-card';

                const imgHtml = item.image_url
                    ? `<img src="${item.image_url}" alt="${item.name}">`
                    : `<iconify-icon icon="solar:box-bold-duotone" class="fs-18 text-muted"></iconify-icon>`;

                const itemMrp = parseFloat(item.mrp) || 0;
                const itemPrice = parseFloat(item.price) || 0;
                let mrpHtml = '';
                if (itemMrp > itemPrice) {
                    const discPercent = Math.round(((itemMrp - itemPrice) / itemMrp) * 100);
                    mrpHtml = `<span class="text-muted text-decoration-line-through me-1" style="font-size:10px;">₹${itemMrp.toFixed(2)}</span><span class="product-disc-badge me-1">-${discPercent}%</span>`;
                }

                card.innerHTML = `
                    <div class="cart-item-thumb">${imgHtml}</div>
                    <div class="cart-item-details">
                        <div class="cart-item-header-row">
                            <span class="cart-item-name" title="${item.name}">${item.name}</span>
                            <button type="button" class="cart-item-remove-btn" onclick="removeCartItem(${item.id})" title="Remove">
                                <iconify-icon icon="solar:trash-bin-trash-linear"></iconify-icon>
                            </button>
                        </div>
                        <div class="cart-item-unit-price">
                            ${mrpHtml}<strong class="text-slate-800">₹${itemPrice.toFixed(2)}</strong> <span class="cart-item-uom">/ ${item.unit_of_measure || 'Unit'}</span>
                        </div>
                        <div class="cart-item-footer-row">
                            <div class="cart-stepper-compact">
                                <button type="button" class="stepper-btn" onclick="changeCartQty(${item.id}, -1)" title="Decrease">
                                    <iconify-icon icon="solar:minus-bold"></iconify-icon>
                                </button>
                                <input type="number" class="stepper-input" min="1" max="${item.stock}" value="${item.qty}"
                                    onchange="setCartItemExactQty(${item.id}, this.value)"
                                    onfocus="this.select()">
                                <button type="button" class="stepper-btn" onclick="changeCartQty(${item.id}, 1)" title="Increase">
                                    <iconify-icon icon="solar:add-bold"></iconify-icon>
                                </button>
                            </div>
                            <div class="cart-item-total-price">
                                ₹${lineTotal.toFixed(2)}
                            </div>
                        </div>
                    </div>
                `;
                container.appendChild(card);
            });

            recalcMobileTotals();
        }

        function toggleDiscountUnit() {
            recalcMobileTotals();
        }

        function recalcMobileTotals() {
            let sellingSubtotal = 0;
            let totalMrp = 0;

            cart.forEach(item => {
                const itemMrp = (parseFloat(item.mrp) > 0) ? parseFloat(item.mrp) : item.price;
                totalMrp += (itemMrp * item.qty);
                sellingSubtotal += (item.price * item.qty);
            });

            // Auto MRP Discount: difference between total MRP and selling price (same as desktop Retail POS)
            const mrpDiscount = Math.max(0, totalMrp - sellingSubtotal);

            // Special Discount (cash reduction from selling subtotal)
            const specialDiscountInput = parseFloat(document.getElementById('inputCartSpecialDiscount')?.value) || 0;
            const specialDiscount = Math.min(specialDiscountInput, sellingSubtotal);

            // Deduct Special Discount from sellingSubtotal first before calculating Taxable Amount & GST
            const netSellingSubtotal = Math.max(0, sellingSubtotal - specialDiscount);
            let taxTotal = 0;
            let taxableTotal = 0;

            cart.forEach(item => {
                const itemSubtotal = item.price * item.qty;
                const itemDiscount = sellingSubtotal > 0 ? (specialDiscount * (itemSubtotal / sellingSubtotal)) : 0;
                const itemNetAmount = Math.max(0, itemSubtotal - itemDiscount);

                const gstRate = parseFloat(item.gst_rate) || 0;
                if (gstRate > 0) {
                    const itemTaxable = itemNetAmount / (1 + (gstRate / 100));
                    const itemTax = itemNetAmount - itemTaxable;
                    taxableTotal += itemTaxable;
                    taxTotal += itemTax;
                } else {
                    taxableTotal += itemNetAmount;
                }
            });

            const grandTotal = netSellingSubtotal;

            if (document.getElementById('lblSummarySubtotal')) {
                document.getElementById('lblSummarySubtotal').innerText = '₹' + sellingSubtotal.toFixed(2);
            }
            if (document.getElementById('lblSummaryTaxable')) {
                document.getElementById('lblSummaryTaxable').innerText = '₹' + taxableTotal.toFixed(2);
            }
            if (document.getElementById('lblSummaryMrpDiscount')) {
                document.getElementById('lblSummaryMrpDiscount').innerText = '-₹' + mrpDiscount.toFixed(2);
            }
            if (document.getElementById('lblSummaryGstTotal')) {
                document.getElementById('lblSummaryGstTotal').innerText = '₹' + taxTotal.toFixed(2);
            }
            if (document.getElementById('lblSummaryGrandTotal')) {
                document.getElementById('lblSummaryGrandTotal').innerText = '₹' + grandTotal.toFixed(2);
            }
            if (document.getElementById('lblPayGrandTotal')) {
                document.getElementById('lblPayGrandTotal').innerText = '₹' + grandTotal.toFixed(2);
            }

            return {
                totalMrp,
                sellingSubtotal,
                mrpDiscount,
                specialDiscount,
                taxableTotal,
                taxTotal,
                grandTotal
            };
        }

        function calculateGrandTotalString() {
            let sellingSubtotal = 0;
            cart.forEach(i => sellingSubtotal += (i.price * i.qty));
            const specialDiscountInput = parseFloat(document.getElementById('inputCartSpecialDiscount')?.value) || 0;
            const specialDiscount = Math.min(specialDiscountInput, sellingSubtotal);
            const grandTotal = Math.max(0, sellingSubtotal - specialDiscount);
            return '₹' + grandTotal.toFixed(2);
        }

        let syncCartTimer = null;
        function syncCartToDatabase() {
            clearTimeout(syncCartTimer);
            syncCartTimer = setTimeout(() => {
                const payload = {
                    mode: posMode,
                    items: cart.map(i => ({ id: i.id, qty: i.qty })),
                    customer_id: selectedCustomer ? selectedCustomer.id : null
                };
                fetch("{{ route('adc.pos.cart.sync') }}", {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken },
                    body: JSON.stringify(payload)
                }).catch(e => console.log("Sync error", e));
            }, 300);
        }

        function loadSavedCartFromBackend() {
            fetch(`{{ route('adc.pos.cart.get') }}?mode=${posMode}`)
                .then(r => r.json())
                .then(data => {
                    if (data.status && data.items && data.items.length > 0) {
                        cart = data.items.map(item => ({
                            ...item,
                            mrp: (parseFloat(item.mrp) > 0 ? parseFloat(item.mrp) : (parseFloat(item.price) || 0)),
                            price: parseFloat(item.price) || 0,
                            gst_rate: parseFloat(item.gst_rate || 0)
                        }));
                        updateCartHeaderAndFloatingBadge();
                    }
                    if (data.customer) {
                        selectCustomerObject(data.customer);
                    }
                })
                .catch(e => console.log(e));
        }

        /* ── Customer Management & Input Handlers ── */
        function handleCustPhoneInput(val) {
            const clean = val.replace(/\D/g, '');
            document.getElementById('inputSearchCustPhone').value = clean;
            const searchWrap = document.getElementById('phoneSearchBoxWrap');
            if (searchWrap) searchWrap.classList.remove('d-none');

            if (clean.length === 10) {
                searchCustomerByPhone();
            } else {
                selectedCustomer = null;
                const pill = document.getElementById('selectedCustomerPillBox');
                if (pill) pill.classList.add('d-none');
                const form = document.getElementById('newCustomerFormFields');
                if (form) form.classList.add('d-none');
                const badge = document.getElementById('lblCustStatusBadge');
                if (badge) {
                    badge.className = 'badge bg-secondary-subtle text-secondary fs-10 fw-bold rounded-pill px-2 py-1';
                    badge.innerText = 'Enter Mobile Number';
                }
            }
        }

        function searchCustomerByPhone() {
            const phone = document.getElementById('inputSearchCustPhone').value.trim();
            if (phone.length < 10) {
                Toastify({ text: "Please enter a valid 10-digit mobile number", backgroundColor: "#ef4444" }).showToast();
                return;
            }

            const btn = document.getElementById('btnExecuteSearchCust');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" style="width:12px; height:12px;"></span>...';
            }

            fetch(`{{ route('adc.pos.customers.search') }}?mobile=${phone}`)
                .then(r => r.json())
                .then(data => {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<iconify-icon icon="solar:magnifer-linear" class="fs-13 me-1"></iconify-icon>Search';
                    }

                    const badge = document.getElementById('lblCustStatusBadge');
                    const form = document.getElementById('newCustomerFormFields');

                    if (data.status && data.customer) {
                        selectCustomerObject(data.customer);
                        if (form) form.classList.add('d-none');
                        if (badge) {
                            badge.className = 'badge bg-success-subtle text-success fs-10 fw-bold rounded-pill px-2 py-1';
                            badge.innerText = 'Verified Customer';
                        }
                        Toastify({ text: "✓ Customer Found: " + data.customer.name, backgroundColor: "#10b981", duration: 2000 }).showToast();
                    } else {
                        selectedCustomer = null;
                        const pill = document.getElementById('selectedCustomerPillBox');
                        if (pill) pill.classList.add('d-none');
                        const searchWrap = document.getElementById('phoneSearchBoxWrap');
                        if (searchWrap) searchWrap.classList.remove('d-none');

                        if (form) form.classList.remove('d-none');
                        if (badge) {
                            badge.className = 'badge bg-warning-subtle text-warning-emphasis fs-10 fw-bold rounded-pill px-2 py-1';
                            badge.innerText = 'New Customer';
                        }
                        const nameInput = document.getElementById('inputNewCustName');
                        if (nameInput) {
                            nameInput.focus();
                        }
                        Toastify({ text: "New customer! Please enter Name below.", backgroundColor: "#3b82f6", duration: 2500 }).showToast();
                    }
                })
                .catch(e => {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = '<iconify-icon icon="solar:magnifer-linear" class="fs-13 me-1"></iconify-icon>Search';
                    }
                });
        }

        function selectCustomerObject(cust) {
            selectedCustomer = cust;
            document.getElementById('lblSelectedCustName').innerText = cust.name;
            document.getElementById('lblSelectedCustPhone').innerText = `+91 ${cust.mobile}` + (cust.email ? ` • ${cust.email}` : '');

            // Clean UX: show verified customer card and hide search box
            const searchWrap = document.getElementById('phoneSearchBoxWrap');
            if (searchWrap) searchWrap.classList.add('d-none');
            const pill = document.getElementById('selectedCustomerPillBox');
            if (pill) pill.classList.remove('d-none');
            const form = document.getElementById('newCustomerFormFields');
            if (form) form.classList.add('d-none');
            const badge = document.getElementById('lblCustStatusBadge');
            if (badge) {
                badge.className = 'badge bg-success-subtle text-success fs-10 fw-bold rounded-pill px-2 py-1';
                badge.innerText = 'Verified Customer';
            }
        }

        function clearSelectedCustomer(focus = false) {
            selectedCustomer = null;
            const pill = document.getElementById('selectedCustomerPillBox');
            if (pill) pill.classList.add('d-none');
            const searchWrap = document.getElementById('phoneSearchBoxWrap');
            if (searchWrap) searchWrap.classList.remove('d-none');
            const form = document.getElementById('newCustomerFormFields');
            if (form) form.classList.add('d-none');

            const phoneInput = document.getElementById('inputSearchCustPhone');
            if (phoneInput) phoneInput.value = '';
            const nameInput = document.getElementById('inputNewCustName');
            if (nameInput) nameInput.value = '';
            const emailInput = document.getElementById('inputNewCustEmail');
            if (emailInput) emailInput.value = '';
            const addrInput = document.getElementById('inputNewCustAddress');
            if (addrInput) addrInput.value = '';

            const badge = document.getElementById('lblCustStatusBadge');
            if (badge) {
                badge.className = 'badge bg-secondary-subtle text-secondary fs-10 fw-bold rounded-pill px-2 py-1';
                badge.innerText = 'Enter Mobile Number';
            }
            if (focus && phoneInput) {
                phoneInput.focus();
            }
        }

        function selectPaymentMethod(mode) {
            selectedPaymentMode = mode;
            document.querySelectorAll('.payment-tile').forEach(tile => {
                if (tile.dataset.mode === mode) tile.classList.add('active');
                else tile.classList.remove('active');
            });

            const modeLabels = {
                'CASH': '<iconify-icon icon="solar:wallet-money-bold" class="fs-13 me-1 text-success"></iconify-icon><span>Cash Payment</span>',
                'CARD': '<iconify-icon icon="solar:card-bold" class="fs-13 me-1 text-primary"></iconify-icon><span>Card Payment</span>',
                'UPI': '<iconify-icon icon="solar:smartphone-bold" class="fs-13 me-1" style="color:#7c3aed;"></iconify-icon><span>UPI Payment</span>',
                'CREDIT': '<iconify-icon icon="solar:users-group-two-rounded-bold" class="fs-13 me-1 text-warning"></iconify-icon><span>Credit (Udhaar)</span>'
            };
            const footerMode = document.getElementById('lblFooterPayMode');
            if (footerMode) footerMode.innerHTML = modeLabels[mode] || mode;
        }

        /* ── Checkout Processing & Automatic Customer Creation on Submit ── */
        async function executeMobileCheckout() {
            if (cart.length === 0) {
                Toastify({ text: "Your cart is empty!", backgroundColor: "#ef4444" }).showToast();
                return;
            }

            const phone = document.getElementById('inputSearchCustPhone').value.trim();

            if (!selectedCustomer && phone.length < 10) {
                Toastify({ text: "Please enter Customer Mobile Number (10 digits)!", backgroundColor: "#ef4444" }).showToast();
                document.getElementById('inputSearchCustPhone').focus();
                return;
            }

            const btn = document.getElementById('btnMobileCheckoutSubmit');

            let custName = selectedCustomer ? selectedCustomer.name : '';
            let custEmail = selectedCustomer ? (selectedCustomer.email || '') : '';
            let custAddress = selectedCustomer ? (selectedCustomer.address || '') : '';

            // If new customer (not yet in database)
            if (!selectedCustomer) {
                const nameInput = document.getElementById('inputNewCustName');
                custName = nameInput ? nameInput.value.trim() : '';
                custEmail = document.getElementById('inputNewCustEmail')?.value.trim() || '';
                custAddress = document.getElementById('inputNewCustAddress')?.value.trim() || '';

                if (!custName) {
                    Toastify({ text: "Please enter Customer Name to proceed!", backgroundColor: "#ef4444" }).showToast();
                    const form = document.getElementById('newCustomerFormFields');
                    if (form) form.classList.remove('d-none');
                    if (nameInput) nameInput.focus();
                    return;
                }
            }

            if (selectedPaymentMode === 'CREDIT' && (!selectedCustomer || !selectedCustomer.mobile) && phone.length < 10) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Customer Required for Credit',
                    text: 'A customer profile with mobile number is required for Credit / Udhaar billing.',
                    confirmButtonText: 'OK'
                });
                return;
            }

            const totals = recalcMobileTotals();
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing Bill...';

            const payload = {
                mode: posMode,
                items: cart.map(i => ({ inventory_id: i.id, qty: i.qty })),
                payment_mode: selectedPaymentMode,
                discount_amount: totals.mrpDiscount,
                special_discount_amount: totals.specialDiscount,
                tax_amount: totals.taxTotal,
                customer_id: selectedCustomer ? selectedCustomer.id : null,
                customer_mobile: selectedCustomer ? selectedCustomer.mobile : phone,
                customer_name: custName || 'Walk-in Customer',
                customer_address: custAddress || null,
                customer_email: custEmail || null
            };

            fetch("{{ route('adc.pos.checkout') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": csrfToken },
                body: JSON.stringify(payload)
            })
                .then(r => r.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = '<iconify-icon icon="solar:printer-bold-duotone" class="fs-18 me-1.5"></iconify-icon> <span>Complete Checkout &amp; Print</span>';

                    if (data.status) {
                        lastSuccessfulOrder = {
                            order_id: data.order_id,
                            invoice_number: data.invoice_number,
                            order_no: data.order_no,
                            dateStr: new Date().toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' }) + ' ' + new Date().toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' }),
                            customer: selectedCustomer || { name: custName, mobile: phone, address: custAddress },
                            items: [...cart],
                            totals: totals,
                            payMode: selectedPaymentMode
                        };

                        // Populate Success Screen & Thermal Slip
                        populateSuccessInvoiceScreen(lastSuccessfulOrder);

                        // Clear Cart State
                        cart = [];
                        updateCartHeaderAndFloatingBadge();

                        // Immediate screen transition to invoice success
                        showScreen('success');
                        playBarcodeBeep();

                        // Refresh catalog silently in background
                        setTimeout(() => {
                            fetchCatalogProducts(true);
                        }, 300);
                    } else {
                        Swal.fire('Checkout Failed', data.message || 'Error processing POS order.', 'error');
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = '<iconify-icon icon="solar:printer-bold-duotone" class="fs-18 me-1.5"></iconify-icon> <span>Complete Checkout &amp; Print</span>';
                    console.error("Checkout error:", err);
                    Swal.fire('Error', 'Server connection error.', 'error');
                });
        }

        function populateSuccessInvoiceScreen(order) {
            const lblInvoice = document.getElementById('lblSuccessInvoiceNo');
            if (lblInvoice) {
                lblInvoice.innerText = `Invoice No: ${order.invoice_number}`;
            }
            const lblDate = document.getElementById('lblSuccessDateStr');
            if (lblDate) {
                lblDate.innerText = `Date: ${order.dateStr}`;
            }

            const slipMeta = document.getElementById('slipInvoiceMetaHeader');
            if (slipMeta) {
                slipMeta.innerText = `Invoice: ${order.invoice_number} | ${order.payMode}`;
            }
            if (document.getElementById('slipSubtotal')) {
                document.getElementById('slipSubtotal').innerText = '₹' + order.totals.sellingSubtotal.toFixed(2);
            }
            if (document.getElementById('slipTaxable')) {
                document.getElementById('slipTaxable').innerText = '₹' + order.totals.taxableTotal.toFixed(2);
            }
            if (document.getElementById('slipDiscount')) {
                document.getElementById('slipDiscount').innerText = '-₹' + (order.totals.mrpDiscount + order.totals.specialDiscount).toFixed(2);
            }
            if (document.getElementById('slipGst')) {
                document.getElementById('slipGst').innerText = '₹' + order.totals.taxTotal.toFixed(2);
            }
            if (document.getElementById('slipGrandTotal')) {
                document.getElementById('slipGrandTotal').innerText = '₹' + order.totals.grandTotal.toFixed(2);
            }

            const tbody = document.getElementById('slipItemsTableBody');
            if (tbody) {
                tbody.innerHTML = '';
                order.items.forEach((item, idx) => {
                    const tr = document.createElement('tr');
                    tr.innerHTML = `
                        <td>${idx + 1}. ${item.name}</td>
                        <td class="text-center">${item.qty}</td>
                        <td class="text-end">₹${item.price.toFixed(2)}</td>
                        <td class="text-end fw-bold">₹${(item.price * item.qty).toFixed(2)}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        }

        /* ── Print, View & Share Actions ── */
        function printSuccessInvoice() {
            if (!lastSuccessfulOrder) return;
            const printUrl = `{{ route('adc.pos.orders.print', ['id' => 999999]) }}`.replace('999999', lastSuccessfulOrder.order_id);
            window.open(printUrl, '_blank');
        }

        function openViewInvoiceModal() {
            if (!lastSuccessfulOrder) return;
            const printUrl = `{{ route('adc.pos.orders.print', ['id' => 999999]) }}`.replace('999999', lastSuccessfulOrder.order_id);
            document.getElementById('invoiceReceiptIframe').src = printUrl;
            document.getElementById('modalViewInvoice').classList.add('active');
        }

        function closeViewInvoiceModal() {
            document.getElementById('modalViewInvoice').classList.remove('active');
            document.getElementById('invoiceReceiptIframe').src = 'about:blank';
        }

        function openShareInvoiceModal() {
            document.getElementById('modalShareInvoice').classList.add('active');
        }

        function closeShareInvoiceModal() {
            document.getElementById('modalShareInvoice').classList.remove('active');
        }

        function shareViaWhatsApp() {
            if (!lastSuccessfulOrder) return;
            const invoiceNo = lastSuccessfulOrder.invoice_number;
            const amount = '₹' + lastSuccessfulOrder.totals.grandTotal.toFixed(2);
            const custMobile = lastSuccessfulOrder.customer ? lastSuccessfulOrder.customer.mobile : '';
            const store = @json($storeName ?? 'ADC Store');

            const message = `🛍️ *Invoice from ${store}*\nInvoice No: ${invoiceNo}\nTotal Amount: ${amount}\nDate: ${lastSuccessfulOrder.dateStr}\nThank you for shopping with us!`;
            const waUrl = `https://api.whatsapp.com/send?phone=${custMobile ? '91' + custMobile : ''}&text=${encodeURIComponent(message)}`;
            window.open(waUrl, '_blank');
            closeShareInvoiceModal();
        }

        function shareViaPdfDownload() {
            if (!lastSuccessfulOrder) return;
            const cleanInv = lastSuccessfulOrder.invoice_number.replace(/[\/\\]/g, '_');
            const pdfUrl = `{{ asset('invoices') }}/${cleanInv}.pdf`;
            window.open(pdfUrl, '_blank');
            closeShareInvoiceModal();
        }

        function shareViaEmail() {
            if (!lastSuccessfulOrder) return;
            const store = @json($storeName ?? 'ADC Store');
            const subject = `Invoice ${lastSuccessfulOrder.invoice_number} from ${store}`;
            const body = `Dear Customer,\n\nPlease find your retail invoice details below:\nInvoice: ${lastSuccessfulOrder.invoice_number}\nAmount: ₹${lastSuccessfulOrder.totals.grandTotal.toFixed(2)}\n\nThank you for shopping with Indian MSME!`;
            window.location.href = `mailto:${lastSuccessfulOrder.customer?.email || ''}?subject=${encodeURIComponent(subject)}&body=${encodeURIComponent(body)}`;
            closeShareInvoiceModal();
        }

        function shareViaWebShareApi() {
            if (!lastSuccessfulOrder) return;
            const store = @json($storeName ?? 'ADC Store');
            if (navigator.share) {
                navigator.share({
                    title: `Invoice ${lastSuccessfulOrder.invoice_number}`,
                    text: `Invoice #${lastSuccessfulOrder.invoice_number} for ₹${lastSuccessfulOrder.totals.grandTotal.toFixed(2)} from ${store}`
                }).catch(() => {});
            } else {
                shareViaWhatsApp();
            }
            closeShareInvoiceModal();
        }

        /* ── Start New Bill Modal ── */
        function openNewBillConfirmModal() {
            document.getElementById('modalNewBillConfirm').classList.add('active');
        }

        function closeNewBillConfirmModal() {
            document.getElementById('modalNewBillConfirm').classList.remove('active');
        }

        function executeStartNewBill() {
            closeNewBillConfirmModal();
            clearSelectedCustomer(false);
            cart = [];
            lastSuccessfulOrder = null;
            const cartDisc = document.getElementById('inputCartDiscountVal');
            if (cartDisc) cartDisc.value = '0.00';
            const specialDisc = document.getElementById('inputCartSpecialDiscount');
            if (specialDisc) specialDisc.value = '0.00';
            updateCartHeaderAndFloatingBadge();
            screenHistory = ['catalog'];
            showScreen('catalog', false);
        }

        /* ── Bill History Modal ── */
        function openBillHistoryModal() {
            document.getElementById('modalBillHistory').classList.add('active');
        }

        function closeBillHistoryModal() {
            document.getElementById('modalBillHistory').classList.remove('active');
        }

        function previewHistoricalInvoice(orderId, invNo) {
            closeBillHistoryModal();
            lastSuccessfulOrder = {
                order_id: orderId,
                invoice_number: invNo
            };
            openViewInvoiceModal();
        }
    </script>
</body>
</html>
