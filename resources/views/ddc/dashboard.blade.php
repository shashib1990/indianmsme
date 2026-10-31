@extends('layouts.ddc')

@section('title', 'DDC Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    /* ── DDC Dashboard Compact Saffron Theme Styling ── */
    body {
        background-color: #f8fafc !important;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .hero-banner-ddc {
        background: linear-gradient(135deg, #c2410c 0%, #ea580c 50%, #9a3412 100%);
        border-radius: 14px;
        position: relative;
        overflow: hidden;
        color: #ffffff;
        padding: 16px 20px;
        box-shadow: 0 8px 20px -4px rgba(234, 88, 12, 0.35);
    }

    .hero-banner-ddc::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 350px;
        height: 350px;
        background: radial-gradient(circle, rgba(253, 186, 116, 0.3) 0%, rgba(0,0,0,0) 70%);
        pointer-events: none;
    }

    .hero-banner-ddc .dot-pattern {
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
        background: radial-gradient(circle, #f97316 0%, #c2410c 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0 14px rgba(249, 115, 22, 0.4);
        position: relative;
    }

    .online-indicator {
        position: absolute;
        bottom: 1px;
        right: 1px;
        width: 11px;
        height: 11px;
        background-color: #22c55e;
        border: 2.5px solid #9a3412;
        border-radius: 50%;
    }

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
</style>
@endpush

@section('content')

    {{-- ── Hero Compact Banner & Action Launcher ── --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="hero-banner-ddc position-relative shadow-sm">
                <div class="dot-pattern"></div>
                <div class="row align-items-center g-2 position-relative" style="z-index: 2;">
                    <div class="col-lg-7">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-ring-hero flex-shrink-0">
                                <iconify-icon icon="solar:shop-bold-duotone" class="text-white fs-26"></iconify-icon>
                                <div class="online-indicator" title="Depot Active"></div>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                    <h4 class="fw-bold text-white mb-0" style="font-family: 'Outfit', sans-serif; font-size: 20px;">
                                        Welcome back, {{ $user->name }}! 👋
                                    </h4>
                                    {{-- DDC Code Badge --}}
                                    <span class="badge shadow-sm d-inline-flex align-items-center gap-1 font-monospace px-2.5 py-1 rounded-pill fw-bold" style="background: #0f172a; color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.4); font-size: 11.5px;">
                                        <iconify-icon icon="solar:user-id-bold-duotone" width="14" height="14" class="text-warning"></iconify-icon>
                                        <span>DDC Code: {{ $ddcCode }}</span>
                                    </span>
                                </div>
                                <div class="d-flex flex-wrap align-items-center gap-2 text-white text-opacity-90 fs-12">
                                    <span>
                                        <iconify-icon icon="solar:map-point-bold-duotone" class="text-warning align-middle me-0.5 fs-13"></iconify-icon>
                                        <strong class="text-white">{{ $ddcProfile?->district ?? ($user->profile?->district ?? 'District Depot') }}</strong>@if($ddcProfile?->state || $user->profile?->state), {{ $ddcProfile?->state ?? $user->profile?->state }}@endif
                                    </span>
                                    <span>•</span>
                                    <span>
                                        <iconify-icon icon="solar:shield-check-bold-duotone" class="text-warning align-middle me-0.5 fs-13"></iconify-icon>
                                        {{ ucfirst($user->type ?? 'Physical') }} Depot
                                    </span>
                                    <span>•</span>
                                    <span class="badge shadow-sm d-inline-flex align-items-center gap-1 font-monospace px-2.5 py-1 rounded-pill fw-bold" style="background: rgba(15, 23, 42, 0.65); color: #38bdf8; border: 1px solid rgba(56, 189, 248, 0.4); font-size: 11px;" title="Joined Date">
                                        <iconify-icon icon="solar:calendar-mark-bold-duotone" width="13" height="13" class="text-info"></iconify-icon>
                                        <span>Joined: {{ $user->created_at ? $user->created_at->format('d M Y') : '—' }}</span>
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Action Launcher Buttons --}}
                    <div class="col-lg-5 text-lg-end">
                        <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-1.5">
                            <a href="{{ route('ddc.pos.retail') }}" class="quick-action-pill">
                                <iconify-icon icon="solar:shop-2-bold-duotone" class="text-warning fs-14"></iconify-icon>
                                Retail POS
                            </a>
                            @if(($user->type ?? 'physical') === 'physical')
                                <a href="{{ route('ddc.purchase-orders.create') }}" class="quick-action-pill">
                                    <iconify-icon icon="solar:cart-large-minimalistic-bold-duotone" class="text-white fs-14"></iconify-icon>
                                    Create PO
                                </a>
                            @endif
                            <a href="{{ route('ddc.local-purchases.create') }}" class="quick-action-pill">
                                <iconify-icon icon="solar:bag-check-bold-duotone" class="text-white fs-14"></iconify-icon>
                                Regional Purchase
                            </a>
                            <a href="{{ route('ddc.wallet.index') }}" class="quick-action-pill">
                                <iconify-icon icon="solar:wallet-bold-duotone" class="text-warning fs-14"></iconify-icon>
                                Top Up
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Financial & Operational Core KPI Cards Grid ── --}}
    <div class="row g-2.5 mb-3">
        {{-- Card 1: Total Sales Revenue --}}
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card-v2 border-start border-3 border-success">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <a href="{{ route('ddc.reports.sales') }}" class="kpi-title-v2 text-decoration-none text-slate-600 hover-primary">
                            Total Sales Revenue <iconify-icon icon="solar:alt-arrow-right-linear" class="align-middle fs-11"></iconify-icon>
                        </a>
                        <a href="{{ route('ddc.reports.sales') }}" class="icon-box-lg bg-success-subtle text-success text-decoration-none">
                            <iconify-icon icon="solar:card-recive-bold-duotone" class="fs-18"></iconify-icon>
                        </a>
                    </div>
                    <a href="{{ route('ddc.reports.sales') }}" class="kpi-value-v2 text-decoration-none text-dark d-block">
                        ₹{{ number_format($totalSalesAmount, 2) }}
                    </a>
                </div>
                <div class="pt-2 border-top mt-1.5">
                    <a href="{{ route('ddc.pos.orders') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 mb-1 interactive-row">
                        <span class="text-muted"><iconify-icon icon="solar:shop-2-bold-duotone" class="text-primary me-1 align-middle"></iconify-icon> Retail POS:</span>
                        <strong class="text-slate-800 font-monospace">₹{{ number_format($posSalesAmount, 2) }}</strong>
                    </a>
                    <a href="{{ route('ddc.adc-orders.index') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 interactive-row">
                        <span class="text-muted"><iconify-icon icon="solar:shop-bold-duotone" class="text-info me-1 align-middle"></iconify-icon> ADC Order:</span>
                        <strong class="text-info font-monospace">₹{{ number_format($adcSalesAmount, 2) }}</strong>
                    </a>
                </div>
            </div>
        </div>

        {{-- Card 2: Total Procurement --}}
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card-v2 border-start border-3 border-primary">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <a href="{{ route('ddc.reports.purchases') }}" class="kpi-title-v2 text-decoration-none text-slate-600 hover-primary">
                            Total Procurement <iconify-icon icon="solar:alt-arrow-right-linear" class="align-middle fs-11"></iconify-icon>
                        </a>
                        <a href="{{ route('ddc.reports.purchases') }}" class="icon-box-lg bg-primary-subtle text-primary text-decoration-none">
                            <iconify-icon icon="solar:cart-large-minimalistic-bold-duotone" class="fs-18"></iconify-icon>
                        </a>
                    </div>
                    <a href="{{ route('ddc.reports.purchases') }}" class="kpi-value-v2 text-decoration-none text-dark d-block">
                        ₹{{ number_format($totalPurchaseAmount, 2) }}
                    </a>
                </div>
                <div class="pt-2 border-top mt-1.5">
                    <a href="{{ route('ddc.purchase-orders.index') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 mb-1 interactive-row">
                        <span class="text-muted"><iconify-icon icon="solar:box-bold-duotone" class="text-primary me-1 align-middle"></iconify-icon> MSME POs:</span>
                        <strong class="text-slate-800 font-monospace">₹{{ number_format($msmePurchaseAmount, 2) }}</strong>
                    </a>
                    <a href="{{ route('ddc.reports.local-purchases') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 interactive-row">
                        <span class="text-muted"><iconify-icon icon="solar:bag-check-bold-duotone" class="text-success me-1 align-middle"></iconify-icon> Regional Purchases:</span>
                        <strong class="text-success font-monospace">₹{{ number_format($localPurchaseAmount, 2) }}</strong>
                    </a>
                </div>
            </div>
        </div>

        {{-- Card 3: Wallet Balance --}}
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card-v2 border-start border-3 border-warning">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <a href="{{ route('ddc.wallet.index') }}" class="kpi-title-v2 text-decoration-none text-slate-600 hover-primary">
                            My Wallet Balance <iconify-icon icon="solar:alt-arrow-right-linear" class="align-middle fs-11"></iconify-icon>
                        </a>
                        <a href="{{ route('ddc.wallet.index') }}" class="icon-box-lg bg-warning-subtle text-warning text-decoration-none">
                            <iconify-icon icon="solar:wallet-bold-duotone" class="fs-18"></iconify-icon>
                        </a>
                    </div>
                    <div class="d-flex align-items-center justify-content-between">
                        <a href="{{ route('ddc.wallet.index') }}" class="kpi-value-v2 text-decoration-none {{ $walletBalance <= 0 ? 'text-danger' : ($walletBalance <= 100 ? 'text-warning' : 'text-success') }} d-block">
                            ₹{{ number_format($walletBalance, 2) }}
                        </a>
                        @if($walletBalance <= 0)
                            <span class="badge bg-danger-subtle text-danger fs-11 fw-bold">POS Deactivated</span>
                        @elseif($walletBalance <= 100)
                            <span class="badge bg-warning-subtle text-warning-emphasis fs-11 fw-bold">Low Balance</span>
                        @endif
                    </div>
                </div>
                <div class="pt-2 border-top mt-1.5 d-flex align-items-center justify-content-between">
                    <span class="text-muted fs-11">Available Balance</span>
                    <a href="{{ route('ddc.wallet.index') }}" class="btn btn-xs btn-outline-primary fw-semibold py-0.5 px-2">
                        Top Up <iconify-icon icon="solar:arrow-right-bold" class="align-middle"></iconify-icon>
                    </a>
                </div>
            </div>
        </div>

        {{-- Card 4: Inventory & Stock Valuation --}}
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card-v2 border-start border-3 border-info">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <a href="{{ route('ddc.inventory.index') }}" class="kpi-title-v2 text-decoration-none text-slate-600 hover-primary">
                            Active Stock &amp; Valuation <iconify-icon icon="solar:alt-arrow-right-linear" class="align-middle fs-11"></iconify-icon>
                        </a>
                        <a href="{{ route('ddc.inventory.index') }}" class="icon-box-lg bg-info-subtle text-info text-decoration-none">
                            <iconify-icon icon="solar:box-bold-duotone" class="fs-18"></iconify-icon>
                        </a>
                    </div>
                    <a href="{{ route('ddc.inventory.index') }}" class="kpi-value-v2 text-decoration-none text-dark d-block">
                        {{ rtrim(rtrim(number_format($totalStockQuantity, 2), '0'), '.') }}
                        <span class="fs-12 fw-normal text-muted">Units</span>
                    </a>
                </div>
                <div class="pt-2 border-top mt-1.5">
                    <a href="{{ route('ddc.inventory.index') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 mb-1 interactive-row">
                        <span class="text-muted">Stock Items:</span>
                        <strong class="text-slate-800">{{ $totalStockItems }} Batches</strong>
                    </a>
                    <a href="{{ route('ddc.inventory.index') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 interactive-row">
                        <span class="text-muted">Total MRP Value:</span>
                        <strong class="text-success font-monospace">₹{{ number_format($totalStockValue, 2) }}</strong>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Network, POS Customers & PO Overview (Row 2 Grid) ── --}}
    <div class="row g-2.5 mb-3">
        {{-- Canteen Stores Network Card --}}
        <div class="col-lg-3 col-md-6">
            <div class="kpi-card-v2 border-start border-3 border-warning">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <a href="{{ route('ddc.canteens.index') }}" class="kpi-title-v2 text-decoration-none text-slate-600 hover-primary">
                            Canteen Network <iconify-icon icon="solar:alt-arrow-right-linear" class="align-middle fs-11"></iconify-icon>
                        </a>
                        <a href="{{ route('ddc.canteens.index') }}" class="icon-box-lg bg-warning-subtle text-warning text-decoration-none">
                            <iconify-icon icon="solar:shop-bold-duotone" class="fs-18"></iconify-icon>
                        </a>
                    </div>
                    <div class="d-flex align-items-baseline justify-content-between">
                        <a href="{{ route('ddc.canteens.index') }}" class="kpi-value-v2 text-decoration-none text-dark d-block mb-0">
                            {{ $totalCanteensCount }}
                            <span class="fs-12 fw-normal text-muted">Stores</span>
                        </a>
                        <span class="badge bg-success-subtle text-success border border-success-subtle fs-10 px-1.5 py-0.5 fw-bold">
                            {{ $activeCanteensCount }} Active
                        </span>
                    </div>
                </div>
                <div class="pt-2 border-top mt-1.5">
                    <a href="{{ route('ddc.canteens.index') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 mb-1 interactive-row">
                        <span class="text-muted"><iconify-icon icon="solar:shop-2-bold-duotone" class="text-warning me-1 align-middle"></iconify-icon> Assigned Canteens:</span>
                        <strong class="text-slate-800 font-monospace">{{ $totalCanteensCount }}</strong>
                    </a>
                    <a href="{{ route('ddc.canteens.index') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 interactive-row">
                        <span class="text-muted"><iconify-icon icon="solar:check-circle-bold-duotone" class="text-success me-1 align-middle"></iconify-icon> Active Status:</span>
                        <strong class="text-success font-monospace">{{ $activeCanteensCount }} Stores</strong>
                    </a>
                </div>
            </div>
        </div>

        {{-- POS Customers Card --}}
        <div class="col-lg-3 col-md-6">
            <div class="kpi-card-v2 border-start border-3 border-info">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <a href="{{ route('ddc.pos.customers') }}" class="kpi-title-v2 text-decoration-none text-slate-600 hover-primary">
                            POS Customers <iconify-icon icon="solar:alt-arrow-right-linear" class="align-middle fs-11"></iconify-icon>
                        </a>
                        <a href="{{ route('ddc.pos.customers') }}" class="icon-box-lg bg-info-subtle text-info text-decoration-none">
                            <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" class="fs-18"></iconify-icon>
                        </a>
                    </div>
                    <div class="d-flex align-items-baseline justify-content-between">
                        <a href="{{ route('ddc.pos.customers') }}" class="kpi-value-v2 text-decoration-none text-dark d-block mb-0">
                            {{ number_format($totalCustomersCount) }}
                            <span class="fs-12 fw-normal text-muted">Customers</span>
                        </a>
                        <a href="{{ route('ddc.pos.customers') }}" class="btn btn-xs btn-outline-primary fw-semibold py-0.5 px-2">Directory</a>
                    </div>
                </div>
                <div class="pt-2 border-top mt-1.5">
                    <a href="{{ route('ddc.pos.customers') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 mb-1 interactive-row">
                        <span class="text-muted"><iconify-icon icon="solar:user-bold-duotone" class="text-info me-1 align-middle"></iconify-icon> Total Customers:</span>
                        <strong class="text-slate-800 font-monospace">{{ number_format($totalCustomersCount) }}</strong>
                    </a>
                    <a href="{{ route('ddc.pos.orders') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 interactive-row">
                        <span class="text-muted"><iconify-icon icon="solar:bill-list-bold-duotone" class="text-primary me-1 align-middle"></iconify-icon> POS Bills Issued:</span>
                        <strong class="text-primary font-monospace">{{ number_format($totalPosSalesCount) }} Bills</strong>
                    </a>
                </div>
            </div>
        </div>

        {{-- Purchase Orders Breakdown Card --}}
        <div class="col-lg-3 col-md-6">
            <div class="kpi-card-v2 border-start border-3 border-indigo" style="border-left-color: #6366f1 !important;">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <a href="{{ route('ddc.purchase-orders.index') }}" class="kpi-title-v2 text-decoration-none text-slate-600 hover-primary">
                            Purchase Orders <iconify-icon icon="solar:alt-arrow-right-linear" class="align-middle fs-11"></iconify-icon>
                        </a>
                        <a href="{{ route('ddc.purchase-orders.index') }}" class="icon-box-lg text-decoration-none" style="background-color: #e0e7ff; color: #4338ca;">
                            <iconify-icon icon="solar:bill-list-bold-duotone" class="fs-18"></iconify-icon>
                        </a>
                    </div>
                    <div class="d-flex align-items-baseline justify-content-between">
                        <a href="{{ route('ddc.purchase-orders.index') }}" class="kpi-value-v2 text-decoration-none text-dark d-block mb-0">
                            {{ $totalPurchaseOrdersCount }}
                            <span class="fs-12 fw-normal text-muted">Total POs</span>
                        </a>
                        @if(($user->type ?? 'physical') === 'physical')
                            <a href="{{ route('ddc.purchase-orders.index') }}" class="btn btn-xs btn-outline-primary fw-semibold py-0.5 px-2">Manage</a>
                        @endif
                    </div>
                </div>
                <div class="pt-2 border-top mt-1.5">
                    <a href="{{ route('ddc.purchase-orders.pending') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 mb-1 interactive-row">
                        <span class="text-muted"><iconify-icon icon="solar:clock-circle-bold-duotone" class="text-warning me-1 align-middle"></iconify-icon> Pending POs:</span>
                        <strong class="text-warning-emphasis font-monospace">{{ $pendingPosCount }} Pending</strong>
                    </a>
                    <a href="{{ route('ddc.purchase-orders.received-orders') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 interactive-row">
                        <span class="text-muted"><iconify-icon icon="solar:check-circle-bold-duotone" class="text-success me-1 align-middle"></iconify-icon> Completed POs:</span>
                        <strong class="text-success font-monospace">{{ $completedPosCount }} Received</strong>
                    </a>
                </div>
            </div>
        </div>

        {{-- Quarantine Stock Alert Card --}}
        <div class="col-lg-3 col-md-6">
            <div class="kpi-card-v2 border-start border-3 border-danger">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <a href="{{ route('ddc.inventory.expired-products') }}" class="kpi-title-v2 text-decoration-none text-slate-600 hover-primary">
                            Quarantine Stock <iconify-icon icon="solar:alt-arrow-right-linear" class="align-middle fs-11"></iconify-icon>
                        </a>
                        <a href="{{ route('ddc.inventory.expired-products') }}" class="icon-box-lg bg-danger-subtle text-danger text-decoration-none">
                            <iconify-icon icon="solar:danger-triangle-bold-duotone" class="fs-18"></iconify-icon>
                        </a>
                    </div>
                    <div class="d-flex align-items-baseline justify-content-between">
                        <a href="{{ route('ddc.inventory.expired-products') }}" class="kpi-value-v2 text-decoration-none text-danger d-block mb-0">
                            {{ $expiredCount }}
                            <span class="fs-12 fw-normal text-muted">Expired</span>
                        </a>
                        <a href="{{ route('ddc.inventory.expired-products') }}" class="btn btn-xs btn-danger fw-semibold py-0.5 px-2">View List</a>
                    </div>
                </div>
                <div class="pt-2 border-top mt-1.5">
                    <a href="{{ route('ddc.inventory.expired-products') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 mb-1 interactive-row">
                        <span class="text-danger fw-semibold"><iconify-icon icon="solar:danger-bold-duotone" class="text-danger me-1 align-middle"></iconify-icon> Expired Batches:</span>
                        <strong class="text-danger font-monospace">{{ $expiredCount }}</strong>
                    </a>
                    <a href="{{ route('ddc.inventory.index', ['expiry_filter' => '1M']) }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 interactive-row">
                        <span class="text-muted"><iconify-icon icon="solar:alarm-bold-duotone" class="text-warning me-1 align-middle"></iconify-icon> Near-Expiry (1M):</span>
                        <strong class="text-warning-emphasis font-monospace">{{ $nearExpiry1MonthCount }} Batches</strong>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Product Expiry Analytics Section (Clean High-Visibility Matrix) ── --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3 overflow-hidden bg-white">
                {{-- Card Header with High-Contrast White Background & Red Accent Badge --}}
                <div class="card-header bg-white border-bottom py-3 px-3.5 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="avatar-sm bg-danger-subtle text-danger rounded-3 d-flex align-items-center justify-content-center shadow-xs" style="width: 40px; height: 40px; border: 1px solid #fecdd3;">
                            <iconify-icon icon="solar:clock-circle-bold-duotone" class="fs-22 text-danger"></iconify-icon>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0 fs-15" style="font-family: 'Outfit', sans-serif;">
                                Product Expiry Analytics &amp; Smart Risk Alerts
                            </h6>
                            <span class="text-muted fs-11">Real-time batch tracking &amp; shelf-life risk distribution across active stock inventory.</span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('ddc.inventory.expired-products') }}" class="btn btn-sm btn-danger fw-semibold px-3 py-1.5 rounded-2 shadow-xs d-flex align-items-center gap-1.5">
                            <iconify-icon icon="solar:danger-triangle-bold-duotone" class="fs-16"></iconify-icon>
                            <span>Quarantine List</span>
                            <span class="badge bg-white text-danger fw-bold rounded-pill ms-1 px-1.5 py-0.5 fs-10">{{ $expiredCount }}</span>
                        </a>
                        <a href="{{ route('ddc.inventory.index') }}" class="btn btn-sm btn-outline-primary fw-semibold px-3 py-1.5 rounded-2">
                            <iconify-icon icon="solar:box-bold-duotone" class="align-middle me-1 fs-15"></iconify-icon> Active Inventory
                        </a>
                    </div>
                </div>

                {{-- Metric Cards Grid --}}
                <div class="card-body p-3 bg-light-subtle">
                    <div class="row g-2.5">
                        {{-- 1. Expired Products (< Today) --}}
                        <div class="col-xl-3 col-md-6">
                            <a href="{{ route('ddc.inventory.expired-products') }}" class="text-decoration-none d-block h-100">
                                <div class="expiry-kpi-card p-3 rounded-3 bg-white border border-danger border-opacity-25 shadow-sm h-100">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="badge bg-danger text-white fw-bold fs-10 px-2 py-1 rounded-pill d-inline-flex align-items-center gap-1">
                                            Expired (&lt; Today)
                                        </span>
                                        <div class="avatar-sm bg-danger-subtle text-danger rounded-3 d-flex align-items-center justify-content-center shadow-xs" style="width: 36px; height: 36px; border: 1px solid #fecdd3;">
                                            <iconify-icon icon="solar:danger-triangle-bold-duotone" class="fs-20"></iconify-icon>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-baseline justify-content-between gap-2 mb-2">
                                        <div>
                                            <h2 class="fw-bold text-danger mb-0" style="font-family: 'Outfit', sans-serif; font-size: 26px; line-height: 1;">{{ number_format($expiredCount) }}</h2>
                                            <span class="fs-11 text-slate-700 fw-semibold">Expired Batches</span>
                                        </div>
                                        <span class="text-danger fs-11 fw-bold text-nowrap">Quarantine &rarr;</span>
                                    </div>
                                    <div class="mt-2 pt-1">
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
                        <div class="col-xl-3 col-md-6">
                            <a href="{{ route('ddc.inventory.index', ['expiry_filter' => '1M']) }}" class="text-decoration-none d-block h-100">
                                <div class="expiry-kpi-card p-3 rounded-3 bg-white border border-warning border-opacity-50 shadow-sm h-100">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="badge bg-warning text-dark fw-bold fs-10 px-2 py-1 rounded-pill d-inline-flex align-items-center gap-1">
                                            1 Month (1–30 Days)
                                        </span>
                                        <div class="avatar-sm bg-warning-subtle text-warning-emphasis rounded-3 d-flex align-items-center justify-content-center shadow-xs" style="width: 36px; height: 36px; border: 1px solid #fef08a;">
                                            <iconify-icon icon="solar:alarm-bold-duotone" class="fs-20"></iconify-icon>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-baseline justify-content-between gap-2 mb-2">
                                        <div>
                                            <h2 class="fw-bold text-dark mb-0" style="font-family: 'Outfit', sans-serif; font-size: 26px; line-height: 1;">{{ number_format($nearExpiry1MonthCount) }}</h2>
                                            <span class="fs-11 text-slate-700 fw-semibold">Critical Batches</span>
                                        </div>
                                        <span class="text-warning-emphasis fs-11 fw-bold text-nowrap">Urgent Action &rarr;</span>
                                    </div>
                                    <div class="mt-2 pt-1">
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
                        <div class="col-xl-3 col-md-6">
                            <a href="{{ route('ddc.inventory.index', ['expiry_filter' => '3M']) }}" class="text-decoration-none d-block h-100">
                                <div class="expiry-kpi-card p-3 rounded-3 bg-white border border-info border-opacity-30 shadow-sm h-100">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="badge bg-info text-white fw-bold fs-10 px-2 py-1 rounded-pill d-inline-flex align-items-center gap-1">
                                            1 to 3 Months (31–90 Days)
                                        </span>
                                        <div class="avatar-sm bg-info-subtle text-info rounded-3 d-flex align-items-center justify-content-center shadow-xs" style="width: 36px; height: 36px; border: 1px solid #bae6fd;">
                                            <iconify-icon icon="solar:calendar-minimalistic-bold-duotone" class="fs-20"></iconify-icon>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-baseline justify-content-between gap-2 mb-2">
                                        <div>
                                            <h2 class="fw-bold text-dark mb-0" style="font-family: 'Outfit', sans-serif; font-size: 26px; line-height: 1;">{{ number_format($nearExpiry3MonthsCount) }}</h2>
                                            <span class="fs-11 text-slate-700 fw-semibold">Moderate Batches</span>
                                        </div>
                                        <span class="text-info fs-11 fw-bold text-nowrap">Clearance &rarr;</span>
                                    </div>
                                    <div class="mt-2 pt-1">
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

                        {{-- 4. Stock Rotation (91 - 180 Days) --}}
                        <div class="col-xl-3 col-md-6">
                            <a href="{{ route('ddc.inventory.index', ['expiry_filter' => '6M']) }}" class="text-decoration-none d-block h-100">
                                <div class="expiry-kpi-card p-3 rounded-3 bg-white border border-success border-opacity-30 shadow-sm h-100">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="badge bg-success text-white fw-bold fs-10 px-2 py-1 rounded-pill d-inline-flex align-items-center gap-1">
                                            3 to 6 Months (91–180 Days)
                                        </span>
                                        <div class="avatar-sm bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center shadow-xs" style="width: 36px; height: 36px; border: 1px solid #bbf7d0;">
                                            <iconify-icon icon="solar:history-bold-duotone" class="fs-20"></iconify-icon>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-baseline justify-content-between gap-2 mb-2">
                                        <div>
                                            <h2 class="fw-bold text-dark mb-0" style="font-family: 'Outfit', sans-serif; font-size: 26px; line-height: 1;">{{ number_format($nearExpiry6MonthsCount) }}</h2>
                                            <span class="fs-11 text-slate-700 fw-semibold">Healthy Batches</span>
                                        </div>
                                        <span class="text-success fs-11 fw-bold text-nowrap">Safe Rotation &rarr;</span>
                                    </div>
                                    <div class="mt-2 pt-1">
                                        <div class="progress" style="height: 5px; background: #dcfce7;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $totalStockItems > 0 ? min(100, round(($nearExpiry6MonthsCount / $totalStockItems) * 100)) : 0 }}%;"></div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-1.5">
                                            <span class="fs-10 text-muted fw-medium">Normal Shelf Life</span>
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

    {{-- ── Monthly Sales Revenue & Net Profit Analytics Chart ── --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
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

                    {{-- Period Selection Filters --}}
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <div class="btn-group btn-group-sm" role="group" aria-label="Period Filter">
                            <button type="button" class="btn btn-primary active chart-period-btn" data-period="this_year">Current FY</button>
                            <button type="button" class="btn btn-outline-secondary chart-period-btn" data-period="last_year">Previous FY</button>
                            <button type="button" class="btn btn-outline-secondary chart-period-btn" data-period="last_6_months">Last 6 Months</button>
                            <button type="button" class="btn btn-outline-secondary chart-period-btn" data-period="last_12_months">Last 12 Months</button>
                        </div>
                        <a href="{{ route('ddc.reports.pnl') }}" class="btn btn-xs btn-outline-primary fw-semibold py-1 px-2.5">
                            Full P&amp;L Report &rarr;
                        </a>
                    </div>
                </div>

                <div class="card-body p-3">
                    <div class="row g-2 align-items-center mb-2">
                        <div class="col-md-6 d-flex align-items-center gap-3">
                            <div class="p-2 px-3 bg-light rounded-2 border">
                                <span class="text-muted fs-11 fw-semibold text-uppercase d-block">Period Sales Revenue</span>
                                <span class="fw-bold text-slate-900 fs-15 font-monospace" id="chartTotalSales">₹0.00</span>
                            </div>
                            <div class="p-2 px-3 bg-success-subtle border border-success-subtle rounded-2">
                                <span class="text-success fw-semibold fs-11 text-uppercase d-block">Period Net Profit</span>
                                <span class="fw-bold text-success fs-15 font-monospace" id="chartTotalProfit">₹0.00</span>
                            </div>
                        </div>
                    </div>

                    <div style="position: relative; height: 260px; width: 100%;">
                        <canvas id="salesProfitChart"></canvas>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Recent Activity Tables (Split Row) ── --}}
    <div class="row g-2.5">
        {{-- Recent Purchase Orders --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-slate-800 mb-0">Recent Purchase Orders</h6>
                    @if(($user->type ?? 'physical') === 'physical')
                        <a href="{{ route('ddc.purchase-orders.index') }}" class="btn btn-xs btn-outline-primary fw-semibold py-0.5 px-2">View All</a>
                    @endif
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light fs-11 text-uppercase text-muted">
                                <tr>
                                    <th class="ps-3 py-2">PO Number</th>
                                    <th class="py-2">Date</th>
                                    <th class="py-2">Amount</th>
                                    <th class="pe-3 py-2 text-end">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentPos as $po)
                                    <tr>
                                        <td class="ps-3 py-2 font-monospace fw-bold text-primary fs-12">
                                            @if(($user->type ?? 'physical') === 'physical')
                                                <a href="{{ route('ddc.purchase-orders.show', $po->id) }}" class="text-decoration-none text-primary">#{{ $po->po_number }}</a>
                                            @else
                                                #{{ $po->po_number }}
                                            @endif
                                        </td>
                                        <td class="py-2 text-muted fs-12">{{ $po->created_at->format('d M Y') }}</td>
                                        <td class="py-2 fw-bold text-dark fs-12">₹{{ number_format($po->total_amount, 2) }}</td>
                                        <td class="pe-3 py-2 text-end">
                                            @if(in_array($po->status, ['Received', 'Completed']))
                                                <span class="badge bg-success-subtle text-success fs-10 px-2 py-0.5">Received</span>
                                            @elseif($po->status === 'Pending')
                                                <span class="badge bg-warning-subtle text-warning fs-10 px-2 py-0.5">Pending</span>
                                            @else
                                                <span class="badge bg-info-subtle text-info fs-10 px-2 py-0.5">{{ $po->status }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3 text-muted fs-12">No purchase orders created yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Recent POS Sales --}}
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-2.5 px-3 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-slate-800 mb-0">Recent POS Sales Bills</h6>
                    <a href="{{ route('ddc.pos.orders') }}" class="btn btn-xs btn-outline-primary fw-semibold py-0.5 px-2">View All</a>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light fs-11 text-uppercase text-muted">
                                <tr>
                                    <th class="ps-3 py-2">Bill No</th>
                                    <th class="py-2">Customer / Mobile</th>
                                    <th class="py-2">Amount</th>
                                    <th class="pe-3 py-2 text-end">Mode</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentSales as $sale)
                                    <tr>
                                        <td class="ps-3 py-2 font-monospace fw-bold text-slate-800 fs-12">#{{ $sale->order_no }}</td>
                                        <td class="py-2">
                                            <strong class="text-dark d-block fs-12">{{ $sale->customer_name ?: 'Walk-in' }}</strong>
                                            <span class="text-muted fs-11">{{ $sale->customer_mobile ?: '—' }}</span>
                                        </td>
                                        <td class="py-2 fw-bold text-success fs-12">₹{{ number_format($sale->total_amount, 2) }}</td>
                                        <td class="pe-3 py-2 text-end">
                                            <span class="badge bg-primary-subtle text-primary fs-10 px-2 py-0.5">{{ $sale->mode }}</span>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-3 text-muted fs-12">No POS sales bills issued yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
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

        fetch(`{{ route('ddc.analytics.sales-profit-chart') }}?period=${period}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    document.getElementById('chartTotalSales').textContent = '₹' + Number(data.total_sales).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});
                    document.getElementById('chartTotalProfit').textContent = '₹' + Number(data.total_profit).toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2});

                    renderChart(data.labels, data.sales, data.net_profit);
                }
            })
            .catch(err => console.error('Chart fetch error:', err));
    }

    function renderChart(labels, salesData, profitData) {
        const ctx = document.getElementById('salesProfitChart').getContext('2d');

        if (salesProfitChartInstance) {
            salesProfitChartInstance.destroy();
        }

        salesProfitChartInstance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Sales Revenue (₹)',
                        data: salesData,
                        backgroundColor: 'rgba(234, 88, 12, 0.75)',
                        borderColor: '#ea580c',
                        borderWidth: 1.5,
                        borderRadius: 6,
                        order: 2,
                    },
                    {
                        label: 'Net Profit (₹)',
                        data: profitData,
                        type: 'line',
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.15)',
                        borderWidth: 3,
                        pointBackgroundColor: '#10b981',
                        pointRadius: 4,
                        pointHoverRadius: 6,
                        tension: 0.35,
                        fill: true,
                        order: 1,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            font: { family: 'Inter', size: 12, weight: '600' },
                            usePointStyle: true,
                            padding: 15
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) { label += ': '; }
                                if (context.parsed.y !== null) {
                                    label += '₹' + Number(context.parsed.y).toLocaleString('en-IN', {minimumFractionDigits: 2});
                                }
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { family: 'Inter', size: 11 } }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { family: 'Inter', size: 11 },
                            callback: function(value) {
                                return '₹' + Number(value).toLocaleString('en-IN');
                            }
                        }
                    }
                }
            }
        });
    }

    document.querySelectorAll('.chart-period-btn').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const period = this.dataset.period;
            fetchAndRenderChart(period);
        });
    });

    fetchAndRenderChart('this_year');
});
</script>
@endpush
