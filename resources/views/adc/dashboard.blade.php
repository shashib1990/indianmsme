@extends('layouts.adc')

@section('title', 'ADC Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    /* ── ADC Dashboard Compact Premium Theme Styling (Matching DDC Layout) ── */
    body {
        background-color: #f8fafc !important;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    /* Hero Banner matching DDC layout with ADC Royal Blue Gradient */
    .hero-banner-adc {
        background: linear-gradient(135deg, #06186b 0%, #0f269a 50%, #091763 100%);
        border-radius: 14px;
        position: relative;
        overflow: hidden;
        color: #ffffff;
        padding: 16px 20px;
        box-shadow: 0 8px 20px -4px rgba(6, 24, 107, 0.35);
    }

    .hero-banner-adc::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 350px;
        height: 350px;
        background: radial-gradient(circle, rgba(59, 130, 246, 0.3) 0%, rgba(0,0,0,0) 70%);
        pointer-events: none;
    }

    .hero-banner-adc .dot-pattern {
        position: absolute;
        top: 0;
        right: 5%;
        width: 220px;
        height: 100%;
        background-image: radial-gradient(rgba(255, 255, 255, 0.2) 1.5px, transparent 1.5px);
        background-size: 12px 12px;
        opacity: 0.6;
        pointer-events: none;
    }

    .avatar-ring-hero {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        border: 2px solid rgba(255, 255, 255, 0.35);
        background: radial-gradient(circle, #2563eb 0%, #0f269a 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0 14px rgba(37, 99, 235, 0.4);
        position: relative;
    }

    .online-indicator {
        position: absolute;
        bottom: 1px;
        right: 1px;
        width: 11px;
        height: 11px;
        background-color: #22c55e;
        border: 2.5px solid #091763;
        border-radius: 50%;
    }

    /* KPI Cards v2 (Identical to DDC Dashboard) */
    .kpi-card-v2 {
        background: #ffffff;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 12px 15px;
        box-shadow: 0 1px 4px 0 rgba(0, 0, 0, 0.02);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        height: 100%;
        display: flex;
        flex-direction: column;
        justify-content: space-between;
        position: relative;
        overflow: hidden;
    }

    .kpi-card-v2:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 16px -4px rgba(0, 0, 0, 0.06);
        border-color: #cbd5e1;
    }

    .expiry-kpi-card {
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .expiry-kpi-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 22px -4px rgba(15, 23, 42, 0.08) !important;
    }

    .kpi-title-v2 {
        color: #64748b;
        font-size: 10.5px;
        font-weight: 700;
        letter-spacing: 0.5px;
        text-transform: uppercase;
    }

    .kpi-value-v2 {
        font-family: 'Outfit', sans-serif;
        font-size: 20px;
        font-weight: 800;
        color: #0f172a;
        margin-top: 2px;
        margin-bottom: 4px;
        line-height: 1.15;
    }

    .icon-box-lg {
        width: 36px;
        height: 36px;
        border-radius: 10px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .quick-action-pill {
        background: rgba(255, 255, 255, 0.16);
        backdrop-filter: blur(8px);
        border: 1px solid rgba(255, 255, 255, 0.25);
        color: #ffffff;
        padding: 6px 13px;
        border-radius: 0px !important;
        font-size: 11.5px;
        font-weight: 600;
        text-decoration: none !important;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        gap: 5px;
    }

    .quick-action-pill:hover {
        background: rgba(255, 255, 255, 0.3);
        color: #ffffff;
        transform: translateY(-1px);
    }

    .interactive-row {
        transition: all 0.15s ease;
        padding: 2px 4px !important;
        border-radius: 4px;
    }

    .interactive-row:hover {
        background-color: #f1f5f9 !important;
    }

    .sparkline-container {
        width: 100%;
        height: 55px;
        margin-top: 4px;
    }

    /* ═════════════════════════════════════════════════════════════════════
       ADC DASHBOARD MOBILE VIEW & CARDS DENSITY OPTIMIZATION
       ═════════════════════════════════════════════ */
    @media (max-width: 767.98px) {
        /* Hero Banner on Phones */
        .hero-banner-adc {
            padding: 12px 14px !important;
            border-radius: 12px !important;
        }
        .hero-banner-adc::before {
            display: none;
        }
        .avatar-ring-hero {
            width: 40px !important;
            height: 40px !important;
        }
        .avatar-ring-hero iconify-icon {
            font-size: 20px !important;
        }
        .hero-title-text {
            font-size: 15px !important;
        }

        /* Horizontal Swipe for Quick Action Launcher (prevents vertical bloat) */
        .quick-actions-mobile-scroll {
            display: flex !important;
            gap: 6px !important;
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
            padding: 4px 1px 2px !important;
            scrollbar-width: none !important;
            width: 100% !important;
        }
        .quick-actions-mobile-scroll::-webkit-scrollbar {
            display: none !important;
        }
        .quick-action-pill {
            padding: 5px 11px !important;
            font-size: 11px !important;
            border-radius: 20px !important;
            white-space: nowrap !important;
            flex-shrink: 0 !important;
        }

        /* 2-Column KPI Cards (Clean high-density presentation without clutter) */
        .kpi-card-v2 {
            padding: 10px 10px !important;
            border-radius: 10px !important;
            border-width: 1px !important;
            border-left-width: 3.5px !important;
        }
        .kpi-title-v2 {
            font-size: 9.5px !important;
            letter-spacing: 0.2px !important;
            line-height: 1.2 !important;
        }
        .kpi-value-v2 {
            font-size: 16px !important;
            margin-top: 2px !important;
            margin-bottom: 2px !important;
            line-height: 1.2 !important;
        }
        .icon-box-lg {
            width: 26px !important;
            height: 26px !important;
            border-radius: 7px !important;
        }
        .icon-box-lg iconify-icon {
            font-size: 14px !important;
        }
        .interactive-row {
            padding: 1px 0 !important;
            font-size: 10.5px !important;
        }
        .interactive-row span {
            font-size: 10px !important;
        }
        .interactive-row strong {
            font-size: 10.5px !important;
        }

        /* Expiry KPI Cards (2-Column on Mobile) */
        .expiry-kpi-card {
            padding: 10px 9px !important;
            border-radius: 10px !important;
        }
        .expiry-kpi-card h2 {
            font-size: 20px !important;
        }
        .expiry-kpi-card .avatar-sm {
            width: 28px !important;
            height: 28px !important;
            border-radius: 6px !important;
        }
        .expiry-kpi-card .avatar-sm iconify-icon {
            font-size: 16px !important;
        }

        /* Chart Period Filter Horizontal Scroll */
        .chart-filter-scroll {
            overflow-x: auto !important;
            -webkit-overflow-scrolling: touch !important;
            scrollbar-width: none !important;
            width: 100% !important;
            padding-bottom: 2px !important;
        }
        .chart-filter-scroll::-webkit-scrollbar {
            display: none !important;
        }
        .chart-filter-scroll .btn-group {
            flex-wrap: nowrap !important;
            white-space: nowrap !important;
        }
        .chart-filter-scroll .btn {
            font-size: 11px !important;
            padding: 3px 8px !important;
            white-space: nowrap !important;
        }
    }
</style>
@endpush

@section('content')

    {{-- ── 1. Hero Compact Banner & Action Launcher (Matching DDC Design) ── --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="hero-banner-adc position-relative shadow-sm">
                <div class="dot-pattern"></div>
                <div class="row align-items-center g-2 position-relative" style="z-index: 2;">
                    <div class="col-lg-7">
                        <div class="d-flex align-items-center gap-2.5">
                            <div class="avatar-ring-hero flex-shrink-0">
                                <iconify-icon icon="solar:shop-bold-duotone" class="text-white fs-24"></iconify-icon>
                                <div class="online-indicator" title="ADC Store Active"></div>
                            </div>
                            <div style="min-width: 0;">
                                <div class="d-flex align-items-center gap-1.5 flex-wrap mb-0.5">
                                    <h4 class="fw-bold text-white mb-0 hero-title-text" style="font-family: 'Outfit', sans-serif;">
                                        Welcome back, {{ $user->name }}! 👋
                                    </h4>
                                    {{-- ADC Code Badge --}}
                                    <span class="badge shadow-sm d-inline-flex align-items-center gap-1 font-monospace px-2 py-0.5 rounded-pill fw-bold" style="background: #0f172a; color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.4); font-size: 11px;">
                                        <iconify-icon icon="solar:user-id-bold-duotone" width="13" height="13" class="text-warning"></iconify-icon>
                                        <span>ADC: {{ $adcCode }}</span>
                                    </span>
                                </div>
                                <div class="d-flex flex-wrap align-items-center gap-1.5 text-white text-opacity-90 fs-11">
                                    <span>
                                        <iconify-icon icon="solar:map-point-bold-duotone" class="text-warning align-middle me-0.5 fs-12"></iconify-icon>
                                        <strong class="text-white">{{ $adcProfile?->district ?? 'Area Center' }}</strong>@if($adcProfile?->state), {{ $adcProfile?->state }}@endif
                                        @if($adcProfile?->pincode) (PIN: {{ $adcProfile->pincode }})@endif
                                    </span>
                                    <span class="d-none d-sm-inline">•</span>
                                    <span class="d-none d-sm-inline">
                                        <iconify-icon icon="solar:shield-check-bold-duotone" class="text-warning align-middle me-0.5 fs-12"></iconify-icon>
                                        ADC Store Center
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Action Launcher Buttons (Horizontal scroll chips on mobile) --}}
                    <div class="col-lg-5 text-lg-end mt-2 mt-lg-0">
                        <div class="quick-actions-mobile-scroll justify-content-lg-end">
                            <a href="{{ route('adc.pos.retail') }}" class="quick-action-pill">
                                <iconify-icon icon="solar:cart-large-minimalistic-bold-duotone" class="text-warning fs-13"></iconify-icon>
                                Retail POS
                            </a>
                            <a href="{{ route('adc.pos.orders') }}" class="quick-action-pill">
                                <iconify-icon icon="solar:bill-list-bold-duotone" class="text-white fs-13"></iconify-icon>
                                Sales Orders
                            </a>
                            <a href="{{ route('adc.inventory.index') }}" class="quick-action-pill">
                                <iconify-icon icon="solar:box-bold-duotone" class="text-white fs-13"></iconify-icon>
                                Store Stock
                            </a>
                            <a href="{{ route('adc.wallet.index') }}" class="quick-action-pill">
                                <iconify-icon icon="solar:wallet-bold-duotone" class="text-warning fs-13"></iconify-icon>
                                Wallet Top Up
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 2. Core Financial & Operational KPI Cards Grid (2-Column Mobile Grid) ── --}}
    <div class="row g-2 g-md-2.5 mb-3">
        {{-- Card 1: Total POS Sales Revenue --}}
        <div class="col-6 col-md-6 col-xl-3">
            <div class="kpi-card-v2 border-start border-3 border-success">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <a href="{{ route('adc.pos.orders') }}" class="kpi-title-v2 text-decoration-none text-slate-600 hover-primary text-truncate pe-1" title="Total POS Sales">
                            Total POS Sales
                        </a>
                        <a href="{{ route('adc.pos.orders') }}" class="icon-box-lg bg-success-subtle text-success text-decoration-none flex-shrink-0">
                            <iconify-icon icon="solar:cart-large-minimalistic-bold-duotone" class="fs-18"></iconify-icon>
                        </a>
                    </div>
                    <a href="{{ route('adc.pos.orders') }}" class="kpi-value-v2 text-decoration-none text-dark d-block text-truncate" title="₹{{ number_format($totalPosSaleAmount, 2) }}">
                        ₹{{ number_format($totalPosSaleAmount, 2) }}
                    </a>
                </div>
                <div class="pt-1.5 border-top mt-1">
                    <a href="{{ route('adc.pos.orders') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 mb-0.5 interactive-row">
                        <span class="text-muted text-truncate"><iconify-icon icon="solar:calendar-mark-bold-duotone" class="text-primary me-0.5 align-middle"></iconify-icon> Month:</span>
                        <strong class="text-slate-800 font-monospace flex-shrink-0">₹{{ number_format($monthlyPosSaleAmount, 2) }}</strong>
                    </a>
                    <a href="{{ route('adc.pos.orders') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 interactive-row">
                        <span class="text-muted text-truncate"><iconify-icon icon="solar:graph-up-bold-duotone" class="text-success me-0.5 align-middle"></iconify-icon> Today:</span>
                        <strong class="text-success font-monospace flex-shrink-0">₹{{ number_format($todayPosSaleAmount, 2) }}</strong>
                    </a>
                </div>
            </div>
        </div>

        {{-- Card 2: Net POS Profit --}}
        <div class="col-6 col-md-6 col-xl-3">
            <div class="kpi-card-v2 border-start border-3 border-primary">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="kpi-title-v2 text-slate-600 text-truncate pe-1">Net POS Profit</span>
                        <div class="icon-box-lg bg-primary-subtle text-primary flex-shrink-0">
                            <iconify-icon icon="solar:dollar-minimalistic-bold-duotone" class="fs-18"></iconify-icon>
                        </div>
                    </div>
                    <div class="kpi-value-v2 text-primary d-block text-truncate" title="₹{{ number_format($totalProfit, 2) }}">
                        ₹{{ number_format($totalProfit, 2) }}
                    </div>
                </div>
                <div class="pt-1.5 border-top mt-1">
                    <div class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 mb-0.5 interactive-row">
                        <span class="text-muted text-truncate"><iconify-icon icon="solar:chart-2-bold-duotone" class="text-primary me-0.5 align-middle"></iconify-icon> Month:</span>
                        <strong class="text-slate-800 font-monospace flex-shrink-0">₹{{ number_format($monthlyProfit, 2) }}</strong>
                    </div>
                    <div class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 interactive-row">
                        <span class="text-muted text-truncate"><iconify-icon icon="solar:clock-circle-bold-duotone" class="text-success me-0.5 align-middle"></iconify-icon> Today:</span>
                        <strong class="text-success font-monospace flex-shrink-0">₹{{ number_format($todayProfit, 2) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 3: Wallet Balance --}}
        <div class="col-6 col-md-6 col-xl-3">
            <div class="kpi-card-v2 border-start border-3 border-warning">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <a href="{{ route('adc.wallet.index') }}" class="kpi-title-v2 text-decoration-none text-slate-600 hover-primary text-truncate pe-1" title="Store Wallet Balance">
                            Wallet Balance
                        </a>
                        <a href="{{ route('adc.wallet.index') }}" class="icon-box-lg bg-warning-subtle text-warning text-decoration-none flex-shrink-0">
                            <iconify-icon icon="solar:wallet-bold-duotone" class="fs-18"></iconify-icon>
                        </a>
                    </div>
                    <a href="{{ route('adc.wallet.index') }}" class="kpi-value-v2 text-decoration-none text-success d-block text-truncate">
                        ₹{{ number_format($walletBalance, 2) }}
                    </a>
                </div>
                <div class="pt-1.5 border-top mt-1 d-flex align-items-center justify-content-between">
                    <span class="text-muted fs-11 text-truncate">Available</span>
                    <a href="{{ route('adc.wallet.index') }}" class="btn btn-xs btn-outline-primary fw-semibold py-0.5 px-1.5 fs-10 flex-shrink-0">
                        Top Up
                    </a>
                </div>
            </div>
        </div>

        {{-- Card 4: Active Stock & Valuation --}}
        <div class="col-6 col-md-6 col-xl-3">
            <div class="kpi-card-v2 border-start border-3 border-info">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <a href="{{ route('adc.inventory.index') }}" class="kpi-title-v2 text-decoration-none text-slate-600 hover-primary text-truncate pe-1" title="Active Stock & Valuation">
                            Active Stock
                        </a>
                        <a href="{{ route('adc.inventory.index') }}" class="icon-box-lg bg-info-subtle text-info text-decoration-none flex-shrink-0">
                            <iconify-icon icon="solar:box-bold-duotone" class="fs-18"></iconify-icon>
                        </a>
                    </div>
                    <a href="{{ route('adc.inventory.index') }}" class="kpi-value-v2 text-decoration-none text-dark d-block text-truncate">
                        {{ number_format($totalStockQuantity) }}
                        <span class="fs-11 fw-normal text-muted">Units</span>
                    </a>
                </div>
                <div class="pt-1.5 border-top mt-1">
                    <a href="{{ route('adc.inventory.index') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 mb-0.5 interactive-row">
                        <span class="text-muted text-truncate">Batches:</span>
                        <strong class="text-slate-800 flex-shrink-0">{{ $totalStockItems }}</strong>
                    </a>
                    <a href="{{ route('adc.inventory.index') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 interactive-row">
                        <span class="text-muted text-truncate">MRP:</span>
                        <strong class="text-success font-monospace flex-shrink-0">₹{{ number_format($totalStockValue, 2) }}</strong>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 3. Secondary Operational Grid (2-Column Mobile Grid for Customers & Returns) ── --}}
    <div class="row g-2 g-md-2.5 mb-3">
        {{-- POS Customers Card --}}
        <div class="col-6 col-md-6 col-lg-4">
            <div class="kpi-card-v2 border-start border-3 border-info">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <a href="{{ route('adc.pos.customers') }}" class="kpi-title-v2 text-decoration-none text-slate-600 hover-primary text-truncate pe-1" title="POS Customers Directory">
                            Customers
                        </a>
                        <a href="{{ route('adc.pos.customers') }}" class="icon-box-lg bg-info-subtle text-info text-decoration-none flex-shrink-0">
                            <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" class="fs-18"></iconify-icon>
                        </a>
                    </div>
                    <div class="d-flex align-items-baseline justify-content-between">
                        <a href="{{ route('adc.pos.customers') }}" class="kpi-value-v2 text-decoration-none text-dark d-block mb-0 text-truncate">
                            {{ number_format($totalCustomersCount) }}
                            <span class="fs-11 fw-normal text-muted d-none d-sm-inline">Cust</span>
                        </a>
                        <a href="{{ route('adc.pos.customers') }}" class="btn btn-xs btn-outline-primary fw-semibold py-0.5 px-1.5 fs-10 flex-shrink-0 d-none d-sm-inline-block">List</a>
                    </div>
                </div>
                <div class="pt-1.5 border-top mt-1">
                    <a href="{{ route('adc.pos.customers') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 mb-0.5 interactive-row">
                        <span class="text-muted text-truncate"><iconify-icon icon="solar:user-bold-duotone" class="text-info me-0.5 align-middle"></iconify-icon> Reg:</span>
                        <strong class="text-slate-800 font-monospace flex-shrink-0">{{ number_format($totalCustomersCount) }}</strong>
                    </a>
                    <a href="{{ route('adc.pos.orders') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 interactive-row">
                        <span class="text-muted text-truncate"><iconify-icon icon="solar:bill-list-bold-duotone" class="text-primary me-0.5 align-middle"></iconify-icon> Bills:</span>
                        <strong class="text-primary font-monospace flex-shrink-0">{{ number_format($totalPosSaleCount) }}</strong>
                    </a>
                </div>
            </div>
        </div>

        {{-- Sales Returns Card --}}
        <div class="col-6 col-md-6 col-lg-4">
            <div class="kpi-card-v2 border-start border-3 border-danger">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <a href="{{ route('adc.pos.returns.index') }}" class="kpi-title-v2 text-decoration-none text-slate-600 hover-primary text-truncate pe-1" title="Sales Returns & Refunds">
                            Returns
                        </a>
                        <a href="{{ route('adc.pos.returns.index') }}" class="icon-box-lg bg-danger-subtle text-danger text-decoration-none flex-shrink-0">
                            <iconify-icon icon="solar:restart-bold-duotone" class="fs-18"></iconify-icon>
                        </a>
                    </div>
                    <div class="d-flex align-items-baseline justify-content-between">
                        <a href="{{ route('adc.pos.returns.index') }}" class="kpi-value-v2 text-decoration-none text-danger d-block mb-0 font-monospace text-truncate">
                            ₹{{ number_format($totalSalesReturnAmount, 2) }}
                        </a>
                        <a href="{{ route('adc.pos.returns.index') }}" class="btn btn-xs btn-outline-danger fw-semibold py-0.5 px-1.5 fs-10 flex-shrink-0 d-none d-sm-inline-block">Orders</a>
                    </div>
                </div>
                <div class="pt-1.5 border-top mt-1">
                    <a href="{{ route('adc.pos.returns.index') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 mb-0.5 interactive-row">
                        <span class="text-muted text-truncate"><iconify-icon icon="solar:bill-cross-bold-duotone" class="text-danger me-0.5 align-middle"></iconify-icon> Refunds:</span>
                        <strong class="text-danger font-monospace flex-shrink-0">{{ number_format($totalSalesReturnCount) }} Orders</strong>
                    </a>
                    <a href="{{ route('adc.pos.returns.index') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 interactive-row">
                        <span class="text-muted text-truncate"><iconify-icon icon="solar:restart-circle-bold-duotone" class="text-danger me-0.5 align-middle"></iconify-icon> Action:</span>
                        <span class="text-danger fw-semibold fs-11 flex-shrink-0">View Details &rarr;</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Quarantine Stock Alert Card (Span full width on phone as a priority alert banner) --}}
        <div class="col-12 col-md-12 col-lg-4">
            <div class="kpi-card-v2 border-start border-3 border-danger">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <a href="{{ route('adc.inventory.index', ['expiry_filter' => 'expired']) }}" class="kpi-title-v2 text-decoration-none text-slate-600 hover-primary text-truncate pe-1" title="Quarantine & Expiry Risk">
                            Quarantine &amp; Expiry Risk
                        </a>
                        <a href="{{ route('adc.inventory.index', ['expiry_filter' => 'expired']) }}" class="icon-box-lg bg-danger-subtle text-danger text-decoration-none flex-shrink-0">
                            <iconify-icon icon="solar:danger-triangle-bold-duotone" class="fs-18"></iconify-icon>
                        </a>
                    </div>
                    <div class="d-flex align-items-baseline justify-content-between">
                        <a href="{{ route('adc.inventory.index', ['expiry_filter' => 'expired']) }}" class="kpi-value-v2 text-decoration-none text-danger d-block mb-0 text-truncate">
                            {{ number_format($expiredCount) }}
                            <span class="fs-11 fw-normal text-muted">Expired Batches</span>
                        </a>
                        <a href="{{ route('adc.inventory.index', ['expiry_filter' => 'expired']) }}" class="btn btn-xs btn-outline-danger fw-semibold py-0.5 px-2 fs-10 flex-shrink-0">
                            Manage Stock
                        </a>
                    </div>
                </div>
                <div class="pt-1.5 border-top mt-1">
                    <a href="{{ route('adc.inventory.index', ['expiry_filter' => 'expired']) }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 mb-0.5 interactive-row">
                        <span class="text-danger text-truncate">
                            <iconify-icon icon="solar:danger-bold-duotone" class="text-danger me-0.5 align-middle"></iconify-icon>
                            Quarantine / Expired:
                        </span>
                        <strong class="text-danger font-monospace flex-shrink-0">{{ number_format($expiredCount) }} Batches</strong>
                    </a>
                    <a href="{{ route('adc.inventory.index', ['expiry_filter' => '1_month']) }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 interactive-row">
                        <span class="text-warning-emphasis text-truncate">
                            <iconify-icon icon="solar:alarm-bold-duotone" class="text-warning me-0.5 align-middle"></iconify-icon>
                            Near-Expiry (1M):
                        </span>
                        <strong class="text-warning-emphasis font-monospace flex-shrink-0">{{ number_format($nearExpiry1MonthCount) }} Batches</strong>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 4. Product Expiry Analytics Section (Clean High-Visibility Matrix) ── --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
                {{-- Card Header --}}
                <div class="card-header bg-white border-bottom py-3 px-3.5 d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center gap-2">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="avatar-sm bg-danger-subtle text-danger rounded-3 d-flex align-items-center justify-content-center shadow-xs flex-shrink-0" style="width: 40px; height: 40px; border: 1px solid #fecdd3;">
                            <iconify-icon icon="solar:clock-circle-bold-duotone" class="fs-22 text-danger"></iconify-icon>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0 fs-15" style="font-family: 'Outfit', sans-serif;">
                                Product Expiry Analytics &amp; Risk Alerts
                            </h6>
                            <span class="text-muted fs-11 d-none d-sm-inline">Real-time batch tracking &amp; shelf-life risk distribution across active store inventory.</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2 w-100 w-sm-auto justify-content-between justify-content-sm-end">
                        <a href="{{ route('adc.inventory.index', ['expiry_filter' => 'expired']) }}" class="btn btn-sm btn-danger fw-semibold px-3 py-1.5 rounded-2 shadow-xs d-flex align-items-center gap-1.5">
                            <iconify-icon icon="solar:danger-triangle-bold-duotone" class="fs-16"></iconify-icon>
                            <span>Quarantine List</span>
                            <span class="badge bg-white text-danger fw-bold rounded-pill ms-1 px-1.5 py-0.5 fs-10">{{ number_format($expiredCount) }}</span>
                        </a>
                        <a href="{{ route('adc.inventory.index') }}" class="btn btn-sm btn-outline-primary fw-semibold px-3 py-1.5 rounded-2">
                            <iconify-icon icon="solar:box-bold-duotone" class="align-middle me-1 fs-15"></iconify-icon>
                            Batches: <strong class="ms-1">{{ number_format($totalStockItems ?? 0) }}</strong>
                        </a>
                    </div>
                </div>

                {{-- Metric Cards Grid --}}
                <div class="card-body p-3 bg-light-subtle">
                    <div class="row g-2.5">
                        {{-- 1. Expired Products (< Today) --}}
                        <div class="col-6 col-md-6 col-xl-3">
                            <a href="{{ route('adc.inventory.index', ['expiry_filter' => 'expired']) }}" class="text-decoration-none d-block h-100">
                                <div class="expiry-kpi-card p-3 rounded-3 bg-white border border-start border-3 border-danger shadow-sm h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="badge bg-danger text-white fw-bold fs-10 px-2 py-1 rounded-pill d-inline-flex align-items-center gap-1">
                                                Expired (&lt; Today)
                                            </span>
                                            <div class="avatar-sm bg-danger-subtle text-danger rounded-3 d-flex align-items-center justify-content-center shadow-xs flex-shrink-0" style="width: 36px; height: 36px; border: 1px solid #fecdd3;">
                                                <iconify-icon icon="solar:danger-triangle-bold-duotone" class="fs-20"></iconify-icon>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-baseline justify-content-between gap-2 mb-2">
                                            <div>
                                                <h2 class="fw-bold text-danger mb-0" style="font-family: 'Outfit', sans-serif; font-size: 26px; line-height: 1;">{{ number_format($expiredCount) }}</h2>
                                                <span class="fs-11 text-slate-700 fw-semibold d-block mt-1">Expired Batches</span>
                                            </div>
                                            <span class="text-danger fs-11 fw-bold text-nowrap d-none d-sm-inline">Quarantine &rarr;</span>
                                        </div>
                                    </div>
                                    <div class="mt-2 pt-1 border-top">
                                        <div class="progress" style="height: 5px; background: #fee2e2;">
                                            <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $totalStockItems > 0 ? min(100, round(($expiredCount / $totalStockItems) * 100)) : 0 }}%;"></div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-1.5">
                                            <span class="fs-10 text-muted fw-medium">Risk: Immediate</span>
                                            <span class="fs-10 fw-bold text-danger">{{ $totalStockItems > 0 ? round(($expiredCount / $totalStockItems) * 100, 1) : 0 }}% of Stock</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        {{-- 2. Urgent Near-Expiry (1 - 30 Days) --}}
                        <div class="col-6 col-md-6 col-xl-3">
                            <a href="{{ route('adc.inventory.index', ['expiry_filter' => '1_month']) }}" class="text-decoration-none d-block h-100">
                                <div class="expiry-kpi-card p-3 rounded-3 bg-white border border-start border-3 border-warning shadow-sm h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="badge bg-warning text-dark fw-bold fs-10 px-2 py-1 rounded-pill d-inline-flex align-items-center gap-1">
                                                1 Month (1–30 D)
                                            </span>
                                            <div class="avatar-sm bg-warning-subtle text-warning-emphasis rounded-3 d-flex align-items-center justify-content-center shadow-xs flex-shrink-0" style="width: 36px; height: 36px; border: 1px solid #fef08a;">
                                                <iconify-icon icon="solar:alarm-bold-duotone" class="fs-20"></iconify-icon>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-baseline justify-content-between gap-2 mb-2">
                                            <div>
                                                <h2 class="fw-bold text-dark mb-0" style="font-family: 'Outfit', sans-serif; font-size: 26px; line-height: 1;">{{ number_format($nearExpiry1MonthCount) }}</h2>
                                                <span class="fs-11 text-slate-700 fw-semibold d-block mt-1">Critical Batches</span>
                                            </div>
                                            <span class="text-warning-emphasis fs-11 fw-bold text-nowrap d-none d-sm-inline">Action &rarr;</span>
                                        </div>
                                    </div>
                                    <div class="mt-2 pt-1 border-top">
                                        <div class="progress" style="height: 5px; background: #fef3c7;">
                                            <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $totalStockItems > 0 ? min(100, round(($nearExpiry1MonthCount / $totalStockItems) * 100)) : 0 }}%;"></div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-1.5">
                                            <span class="fs-10 text-muted fw-medium">Sale Priority</span>
                                            <span class="fs-10 fw-bold text-warning-emphasis">{{ $totalStockItems > 0 ? round(($nearExpiry1MonthCount / $totalStockItems) * 100, 1) : 0 }}% of Stock</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        {{-- 3. Clearance Scope (31 - 90 Days) --}}
                        <div class="col-6 col-md-6 col-xl-3">
                            <a href="{{ route('adc.inventory.index', ['expiry_filter' => '3_months']) }}" class="text-decoration-none d-block h-100">
                                <div class="expiry-kpi-card p-3 rounded-3 bg-white border border-start border-3 border-info shadow-sm h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="badge bg-info text-white fw-bold fs-10 px-2 py-1 rounded-pill d-inline-flex align-items-center gap-1">
                                                1–3M (31–90 D)
                                            </span>
                                            <div class="avatar-sm bg-info-subtle text-info rounded-3 d-flex align-items-center justify-content-center shadow-xs flex-shrink-0" style="width: 36px; height: 36px; border: 1px solid #bae6fd;">
                                                <iconify-icon icon="solar:calendar-minimalistic-bold-duotone" class="fs-20"></iconify-icon>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-baseline justify-content-between gap-2 mb-2">
                                            <div>
                                                <h2 class="fw-bold text-dark mb-0" style="font-family: 'Outfit', sans-serif; font-size: 26px; line-height: 1;">{{ number_format($nearExpiry3MonthsCount) }}</h2>
                                                <span class="fs-11 text-slate-700 fw-semibold d-block mt-1">Moderate Batches</span>
                                            </div>
                                            <span class="text-info fs-11 fw-bold text-nowrap d-none d-sm-inline">FIFO &rarr;</span>
                                        </div>
                                    </div>
                                    <div class="mt-2 pt-1 border-top">
                                        <div class="progress" style="height: 5px; background: #e0f2fe;">
                                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $totalStockItems > 0 ? min(100, round(($nearExpiry3MonthsCount / $totalStockItems) * 100)) : 0 }}%;"></div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-1.5">
                                            <span class="fs-10 text-muted fw-medium">Clearance Scope</span>
                                            <span class="fs-10 fw-bold text-info">{{ $totalStockItems > 0 ? round(($nearExpiry3MonthsCount / $totalStockItems) * 100, 1) : 0 }}% of Stock</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>

                        {{-- 4. Safe Shelf-Life (91 - 180 Days) --}}
                        <div class="col-6 col-md-6 col-xl-3">
                            <a href="{{ route('adc.inventory.index', ['expiry_filter' => '6_months']) }}" class="text-decoration-none d-block h-100">
                                <div class="expiry-kpi-card p-3 rounded-3 bg-white border border-start border-3 border-success shadow-sm h-100 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="badge bg-success text-white fw-bold fs-10 px-2 py-1 rounded-pill d-inline-flex align-items-center gap-1">
                                                3–6M (91–180 D)
                                            </span>
                                            <div class="avatar-sm bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center shadow-xs flex-shrink-0" style="width: 36px; height: 36px; border: 1px solid #bbf7d0;">
                                                <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-20"></iconify-icon>
                                            </div>
                                        </div>
                                        <div class="d-flex align-items-baseline justify-content-between gap-2 mb-2">
                                            <div>
                                                <h2 class="fw-bold text-dark mb-0" style="font-family: 'Outfit', sans-serif; font-size: 26px; line-height: 1;">{{ number_format($nearExpiry6MonthsCount) }}</h2>
                                                <span class="fs-11 text-slate-700 fw-semibold d-block mt-1">Safe Batches</span>
                                            </div>
                                            <span class="text-success fs-11 fw-bold text-nowrap d-none d-sm-inline">Healthy &rarr;</span>
                                        </div>
                                    </div>
                                    <div class="mt-2 pt-1 border-top">
                                        <div class="progress" style="height: 5px; background: #dcfce7;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $totalStockItems > 0 ? min(100, round(($nearExpiry6MonthsCount / $totalStockItems) * 100)) : 0 }}%;"></div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-1.5">
                                            <span class="fs-10 text-muted fw-medium">Safe Shelf-Life</span>
                                            <span class="fs-10 fw-bold text-success">{{ $totalStockItems > 0 ? round(($nearExpiry6MonthsCount / $totalStockItems) * 100, 1) : 0 }}% of Stock</span>
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 5. Analytics Chart & Recent Bills Section ── --}}
    <div class="row g-3 mb-4">
        <!-- Monthly POS Sales & Net Profit Analytics Chart Container -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3 bg-white h-100">
                <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <div class="avatar-xs bg-primary-subtle text-primary rounded-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                            <iconify-icon icon="solar:chart-2-bold-duotone" class="fs-20"></iconify-icon>
                        </div>
                        <div>
                            <h6 class="fw-bold text-slate-800 mb-0" style="font-family: 'Outfit', sans-serif;">Monthly Sales &amp; Net Profit Performance</h6>
                            <span class="text-muted fs-11">Comparison of Monthly Revenue vs Net Profit with period filtering.</span>
                        </div>
                    </div>

                    {{-- Period Selection Filters (Horizontal Scroll on Mobile) --}}
                    <div class="chart-filter-scroll">
                        <div class="btn-group btn-group-sm" role="group" aria-label="Period Filter">
                            <button type="button" class="btn btn-primary active chart-period-btn" data-period="this_year">Current FY</button>
                            <button type="button" class="btn btn-outline-secondary chart-period-btn" data-period="last_year">Previous FY</button>
                            <button type="button" class="btn btn-outline-secondary chart-period-btn" data-period="last_6_months">Last 6 Months</button>
                            <button type="button" class="btn btn-outline-secondary chart-period-btn" data-period="last_12_months">Last 12 Months</button>
                        </div>
                    </div>
                </div>

                <div class="card-body p-2.5 p-sm-3">
                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-6">
                            <div class="p-2 px-2.5 bg-light rounded-2 border">
                                <span class="text-muted fs-10 fs-sm-11 fw-semibold text-uppercase d-block text-truncate">Period Revenue</span>
                                <span class="fw-bold text-slate-900 fs-13 fs-sm-15 font-monospace text-truncate d-block" id="chartTotalSales">₹{{ number_format($sixMonthTotalSales, 2) }}</span>
                            </div>
                        </div>
                        <div class="col-6">
                            <div class="p-2 px-2.5 bg-success-subtle border border-success-subtle rounded-2">
                                <span class="text-success fw-semibold fs-10 fs-sm-11 text-uppercase d-block text-truncate">Period Net Profit</span>
                                <span class="fw-bold text-success fs-13 fs-sm-15 font-monospace text-truncate d-block" id="chartTotalProfit">₹{{ number_format($sixMonthTotalProfit, 2) }}</span>
                            </div>
                        </div>
                    </div>

                    <div style="position: relative; height: 260px; width: 100%;">
                        <canvas id="monthlySalesProfitChartCanvas"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent POS Sales Bills & Sales Returns Card -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm rounded-3 bg-white overflow-hidden h-100">
                <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
                    <ul class="nav nav-pills card-header-pills fs-12 fw-bold gap-1 my-0" id="recentTransTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active py-1.5 px-2.5 fs-12" id="sales-tab" data-bs-toggle="tab" data-bs-target="#tab-recent-sales" type="button" role="tab" aria-selected="true">
                                <iconify-icon icon="solar:receipt-bold-duotone" class="align-middle me-1 fs-14 text-primary"></iconify-icon> Sales Bills
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1.5 px-2.5 fs-12 text-danger" id="returns-tab" data-bs-toggle="tab" data-bs-target="#tab-recent-returns" type="button" role="tab" aria-selected="false">
                                <iconify-icon icon="solar:restart-bold-duotone" class="align-middle me-1 fs-14 text-danger"></iconify-icon> Sales Returns
                            </button>
                        </li>
                    </ul>
                    <a href="{{ route('adc.pos.orders') }}" class="text-primary fs-12 fw-semibold text-decoration-none">
                        View &rarr;
                    </a>
                </div>
                <div class="tab-content" id="recentTransTabContent">
                    <!-- Tab 1: Recent Sales Bills -->
                    <div class="tab-pane fade show active" id="tab-recent-sales" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light fs-11 text-uppercase text-muted">
                                    <tr>
                                        <th class="ps-3 py-2">BILL NO</th>
                                        <th class="py-2">CUSTOMER</th>
                                        <th class="py-2 text-end pe-3">AMOUNT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentSales as $sale)
                                        <tr>
                                            <td class="ps-3 font-monospace fw-bold fs-11">
                                                <a href="{{ route('adc.pos.orders.show', $sale->id) }}" class="text-decoration-none text-primary">#{{ $sale->order_no }}</a>
                                                <span class="d-block fs-10 text-muted font-normal">{{ $sale->created_at->format('d M, h:i A') }}</span>
                                            </td>
                                            <td>
                                                <strong class="text-slate-800 d-block fs-11.5 mb-0.5 text-truncate" style="max-width: 110px;">{{ $sale->customer_name ?: ($sale->customer?->name ?: 'Walk-in Customer') }}</strong>
                                                <span class="badge icon-box-blue fs-10 px-1.5 py-0.5 font-monospace rounded-1">{{ strtoupper($sale->payment_mode ?? 'CASH') }}</span>
                                            </td>
                                            <td class="pe-3 text-end fw-bold fs-12 text-success font-monospace">
                                                ₹{{ number_format($sale->total_amount, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted fs-12">No recent POS sales bills issued.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Tab 2: Recent Sales Returns -->
                    <div class="tab-pane fade" id="tab-recent-returns" role="tabpanel">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light fs-11 text-uppercase text-muted">
                                    <tr>
                                        <th class="ps-3 py-2">RETURN NO</th>
                                        <th class="py-2">CUSTOMER</th>
                                        <th class="py-2 text-end pe-3">REFUND</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentSalesReturns as $ret)
                                        <tr>
                                            <td class="ps-3 font-monospace fw-bold fs-11">
                                                <a href="{{ route('adc.pos.returns.index') }}" class="text-decoration-none text-danger">{{ $ret->return_number }}</a>
                                                <span class="d-block fs-10 text-muted font-normal">{{ $ret->created_at->format('d M, h:i A') }}</span>
                                            </td>
                                            <td>
                                                <strong class="text-slate-800 d-block fs-11.5 mb-0.5 text-truncate" style="max-width: 110px;">{{ $ret->customer_name ?: 'Walk-in Customer' }}</strong>
                                                <span class="badge bg-danger-subtle text-danger fs-10 px-1.5 py-0.5 font-monospace rounded-1">{{ strtoupper($ret->refund_mode ?? 'CASH') }}</span>
                                            </td>
                                            <td class="pe-3 text-end fw-bold fs-12 text-danger font-monospace">
                                                -₹{{ number_format($ret->total_return_amount, 2) }}
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted fs-12">No recent sales returns processed.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
    <!-- Load Chart.js CDN for Zero-Conflict HTML5 Canvas Rendering -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            let salesProfitChartInstance = null;

            function fetchAndRenderChart(period = 'this_year') {
                const btnGroup = document.querySelectorAll('.chart-period-btn');
                btnGroup.forEach(btn => {
                    if (btn.dataset.period === period) {
                        btn.classList.remove('btn-outline-secondary');
                        btn.classList.add('btn-primary', 'active');
                    } else {
                        btn.classList.remove('btn-primary', 'active');
                        btn.classList.add('btn-outline-secondary');
                    }
                });

                fetch(`{{ route('adc.analytics.sales-profit-chart') }}?period=${period}`)
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'success') {
                            const salesEl = document.getElementById('chartTotalSales');
                            const profitEl = document.getElementById('chartTotalProfit');
                            if (salesEl) salesEl.textContent = '₹' + Number(data.total_sales).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                            if (profitEl) profitEl.textContent = '₹' + Number(data.total_profit).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});

                            renderChart(data.labels, data.sales, data.net_profit);
                        }
                    })
                    .catch(err => console.error('ADC Chart fetch error:', err));
            }

            function renderChart(labels, salesData, profitData) {
                const canvas = document.getElementById('monthlySalesProfitChartCanvas');
                if (!canvas) return;
                const ctx = canvas.getContext('2d');

                if (salesProfitChartInstance) {
                    salesProfitChartInstance.destroy();
                }

                // Sales Area Gradient
                var salesGrad = ctx.createLinearGradient(0, 0, 0, 260);
                salesGrad.addColorStop(0, 'rgba(59, 130, 246, 0.22)');
                salesGrad.addColorStop(1, 'rgba(59, 130, 246, 0.00)');

                // Profit Area Gradient
                var profitGrad = ctx.createLinearGradient(0, 0, 0, 260);
                profitGrad.addColorStop(0, 'rgba(16, 185, 129, 0.22)');
                profitGrad.addColorStop(1, 'rgba(16, 185, 129, 0.00)');

                salesProfitChartInstance = new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'POS Sales (₹)',
                                data: salesData,
                                borderColor: '#3b82f6',
                                backgroundColor: salesGrad,
                                borderWidth: 2.5,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: '#3b82f6',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 4.5,
                                pointHoverRadius: 6.5
                            },
                            {
                                label: 'Net Profit (₹)',
                                data: profitData,
                                borderColor: '#10b981',
                                backgroundColor: profitGrad,
                                borderWidth: 2.5,
                                fill: true,
                                tension: 0.4,
                                pointBackgroundColor: '#10b981',
                                pointBorderColor: '#ffffff',
                                pointBorderWidth: 2,
                                pointRadius: 4.5,
                                pointHoverRadius: 6.5
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: {
                            mode: 'index',
                            intersect: false
                        },
                        plugins: {
                            legend: {
                                position: 'top',
                                labels: {
                                    usePointStyle: true,
                                    boxWidth: 8,
                                    font: { family: 'Inter', size: 12, weight: '500' }
                                }
                            },
                            tooltip: {
                                backgroundColor: '#0f172a',
                                titleFont: { family: 'Inter', size: 13 },
                                bodyFont: { family: 'Inter', size: 12 },
                                padding: 10,
                                cornerRadius: 8,
                                callbacks: {
                                    label: function(context) {
                                        let label = context.dataset.label || '';
                                        if (label) label += ': ';
                                        if (context.parsed.y !== null) {
                                            label += '₹' + Number(context.parsed.y).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
                                        }
                                        return label;
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                grid: { display: false },
                                ticks: { font: { family: 'Inter', size: 11 }, color: '#64748b' }
                            },
                            y: {
                                grid: { color: '#f1f5f9' },
                                ticks: {
                                    font: { family: 'Inter', size: 11 },
                                    color: '#64748b',
                                    callback: function(value) {
                                        if (value >= 1000) {
                                            return (value / 1000).toFixed(0) + 'K';
                                        }
                                        return value;
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Bind click event to period buttons
            const periodButtons = document.querySelectorAll('.chart-period-btn');
            periodButtons.forEach(btn => {
                btn.addEventListener('click', function() {
                    const period = this.dataset.period;
                    fetchAndRenderChart(period);
                });
            });

            // Initial load
            fetchAndRenderChart('this_year');
        });
    </script>
@endpush
