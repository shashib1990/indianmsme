@extends('layouts.vendor')

@section('title', 'Seller Dashboard')
@section('page-title', 'Dashboard')

@push('styles')
<style>
    /* ── DDC-Style Vendor Dashboard Compact Styling ── */
    body {
        background-color: #f8fafc !important;
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
    }

    .hero-banner-ddc {
        background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #022c22 100%);
        border-radius: 14px;
        position: relative;
        overflow: hidden;
        color: #ffffff;
        padding: 16px 20px;
        box-shadow: 0 8px 20px -4px rgba(4, 120, 87, 0.35);
    }

    .hero-banner-ddc::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 350px;
        height: 350px;
        background: radial-gradient(circle, rgba(52, 211, 153, 0.25) 0%, rgba(0,0,0,0) 70%);
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
        background: radial-gradient(circle, #059669 0%, #064e3b 100%);
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 0 14px rgba(5, 150, 105, 0.4);
        position: relative;
    }

    .online-indicator {
        position: absolute;
        bottom: 1px;
        right: 1px;
        width: 11px;
        height: 11px;
        background-color: #22c55e;
        border: 2.5px solid #064e3b;
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
        border-radius: 6px !important;
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

    .product-item-card {
        padding: 12px 14px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        transition: background 0.15s ease;
        overflow: hidden;
        max-width: 100%;
    }

    .product-item-card:hover {
        background: #f8fafc;
    }

    .product-img-box {
        width: 44px;
        height: 44px;
        border-radius: 10px;
        overflow: hidden;
        background: #f8fafc;
        border: 1px solid #e2e8f0;
        display: flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
    }
</style>
@endpush

@section('content')
@php
    $companyNameVal = $profile?->company_name ?: ($vendor->name . ' Enterprises');
    $sellerCodeVal = $profile?->seller_id ?: ('SEL' . str_pad($vendor->id, 3, '0', STR_PAD_LEFT));
    $isDdcSubscribed = (bool) ($profile?->is_ddc_subscribed ?? false);
@endphp

    {{-- ── 1. Hero Compact Banner & Action Launcher (Identical to DDC Panel Header) ── --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="hero-banner-ddc position-relative shadow-sm">
                <div class="dot-pattern"></div>
                <div class="row align-items-center g-2 position-relative" style="z-index: 2;">
                    <div class="col-lg-7">
                        <div class="d-flex align-items-center gap-3">
                            <div class="avatar-ring-hero flex-shrink-0">
                                <iconify-icon icon="solar:shop-bold-duotone" class="text-white fs-26"></iconify-icon>
                                <div class="online-indicator" title="Seller Active"></div>
                            </div>
                            <div>
                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                    <h4 class="fw-bold text-white mb-0" style="font-family: 'Outfit', sans-serif; font-size: 20px;">
                                        Welcome back, {{ $vendor->name }}! 👋
                                    </h4>
                                    {{-- Seller Code Badge --}}
                                    <a href="{{ route('vendor.profile') }}" class="badge shadow-sm d-inline-flex align-items-center gap-1 font-monospace px-2.5 py-1 rounded-pill fw-bold text-decoration-none" style="background: #0f172a; color: #fbbf24; border: 1px solid rgba(251, 191, 36, 0.4); font-size: 11.5px;">
                                        <iconify-icon icon="solar:user-id-bold-duotone" width="14" height="14" class="text-warning"></iconify-icon>
                                        <span>Seller Code: {{ $sellerCodeVal }}</span>
                                    </a>
                                </div>
                                <div class="d-flex flex-wrap align-items-center gap-2 text-white text-opacity-90 fs-12">
                                    <span>
                                        <iconify-icon icon="solar:shop-2-bold-duotone" class="text-warning align-middle me-0.5 fs-13"></iconify-icon>
                                        <strong class="text-white">{{ $companyNameVal }}</strong>
                                    </span>
                                    <span>•</span>
                                    <span>
                                        <iconify-icon icon="solar:map-point-bold-duotone" class="text-warning align-middle me-0.5 fs-13"></iconify-icon>
                                        <strong class="text-white">{{ $profile?->district ?? 'District Unit' }}</strong>@if($profile?->state), {{ $profile->state }}@endif
                                    </span>
                                    <span>•</span>
                                    @if($isDdcSubscribed)
                                        <a href="{{ route('vendor.smart-sales-incentive') }}" class="badge bg-warning text-dark px-2.5 py-0.5 rounded-pill fw-bold fs-11 text-decoration-none">
                                            <iconify-icon icon="solar:star-bold" class="align-middle me-0.5"></iconify-icon> DDC Subscribed (SSI Active)
                                        </a>
                                    @else
                                        <a href="{{ route('vendor.smart-sales-incentive') }}" class="badge bg-info bg-opacity-30 text-white border border-info border-opacity-40 px-2.5 py-0.5 rounded-pill fw-bold fs-11 text-decoration-none">
                                            Field Sales Incentive (FSI Active)
                                        </a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Quick Action Launcher Buttons --}}
                    <div class="col-lg-5 text-lg-end">
                        <div class="d-flex flex-wrap align-items-center justify-content-lg-end gap-1.5">
                            <a href="{{ route('vendor.products.create') }}" class="quick-action-pill">
                                <iconify-icon icon="solar:add-circle-bold-duotone" class="text-warning fs-14"></iconify-icon>
                                Add Product
                            </a>
                            <a href="{{ route('vendor.purchase-orders.new') }}" class="quick-action-pill">
                                <iconify-icon icon="solar:bell-bold-duotone" class="text-white fs-14"></iconify-icon>
                                New POs ({{ $pendingPoCount }})
                            </a>
                            <a href="{{ route('vendor.products.index') }}" class="quick-action-pill">
                                <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="text-white fs-14"></iconify-icon>
                                Catalog
                            </a>
                            <a href="{{ route('vendor.wallet.index') }}" class="quick-action-pill">
                                <iconify-icon icon="solar:wallet-bold-duotone" class="text-warning fs-14"></iconify-icon>
                                Wallet
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 2. Financial & Operational Core KPI Cards Grid (Row 1) ── --}}
    <div class="row g-2.5 mb-3">
        {{-- Card 1: Total Revenue & Wallet --}}
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card-v2 border-start border-3 border-success">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <a href="{{ route('vendor.purchase-orders.accepted') }}" class="kpi-title-v2 text-decoration-none text-slate-600 hover-primary">
                            Total Revenue <iconify-icon icon="solar:alt-arrow-right-linear" class="align-middle fs-11"></iconify-icon>
                        </a>
                        <a href="{{ route('vendor.wallet.index') }}" class="icon-box-lg bg-success-subtle text-success text-decoration-none">
                            <iconify-icon icon="solar:wallet-money-bold-duotone" class="fs-18"></iconify-icon>
                        </a>
                    </div>
                    <a href="{{ route('vendor.wallet.index') }}" class="kpi-value-v2 text-decoration-none text-success d-block">
                        ₹{{ number_format($totalRevenue, 2) }}
                    </a>
                </div>
                <div class="pt-2 border-top mt-1.5">
                    <a href="{{ route('vendor.wallet.index') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 mb-1 interactive-row">
                        <span class="text-muted"><iconify-icon icon="solar:wallet-bold-duotone" class="text-primary me-1 align-middle"></iconify-icon> Wallet Balance:</span>
                        <strong class="text-slate-800 font-monospace">₹{{ number_format($walletBalance, 2) }}</strong>
                    </a>
                </div>
            </div>
        </div>

        {{-- Card 2: Purchase Orders Overview --}}
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card-v2 border-start border-3 border-primary">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <a href="{{ route('vendor.purchase-orders.index') }}" class="kpi-title-v2 text-decoration-none text-slate-600 hover-primary">
                            Purchase Orders <iconify-icon icon="solar:alt-arrow-right-linear" class="align-middle fs-11"></iconify-icon>
                        </a>
                        <a href="{{ route('vendor.purchase-orders.index') }}" class="icon-box-lg bg-primary-subtle text-primary text-decoration-none">
                            <iconify-icon icon="solar:bill-list-bold-duotone" class="fs-18"></iconify-icon>
                        </a>
                    </div>
                    <a href="{{ route('vendor.purchase-orders.index') }}" class="kpi-value-v2 text-decoration-none text-primary d-block">
                        {{ number_format($totalPos) }}
                        <span class="fs-12 fw-normal text-muted">Orders</span>
                    </a>
                </div>
                <div class="pt-2 border-top mt-1.5">
                    <a href="{{ route('vendor.purchase-orders.new') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 mb-1 interactive-row">
                        <span class="text-muted"><iconify-icon icon="solar:clock-circle-bold-duotone" class="text-warning me-1 align-middle"></iconify-icon> Pending:</span>
                        <strong class="text-warning-emphasis font-monospace">{{ $pendingPoCount }} New</strong>
                    </a>
                    <a href="{{ route('vendor.purchase-orders.accepted') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 interactive-row">
                        <span class="text-muted"><iconify-icon icon="solar:check-circle-bold-duotone" class="text-success me-1 align-middle"></iconify-icon> Accepted:</span>
                        <strong class="text-success font-monospace">{{ $acceptedPoCount }} Orders</strong>
                    </a>
                </div>
            </div>
        </div>

        {{-- Card 3: Products Catalog --}}
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card-v2 border-start border-3 border-info">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <a href="{{ route('vendor.products.index') }}" class="kpi-title-v2 text-decoration-none text-slate-600 hover-primary">
                            Products Listed <iconify-icon icon="solar:alt-arrow-right-linear" class="align-middle fs-11"></iconify-icon>
                        </a>
                        <a href="{{ route('vendor.products.index') }}" class="icon-box-lg bg-info-subtle text-info text-decoration-none">
                            <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="fs-18"></iconify-icon>
                        </a>
                    </div>
                    <a href="{{ route('vendor.products.index') }}" class="kpi-value-v2 text-decoration-none text-dark d-block">
                        {{ number_format($totalProducts) }}
                        <span class="fs-12 fw-normal text-muted">Items</span>
                    </a>
                </div>
                <div class="pt-2 border-top mt-1.5">
                    <a href="{{ route('vendor.products.index', ['status' => 'approved']) }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 mb-1 interactive-row">
                        <span class="text-muted"><iconify-icon icon="solar:check-circle-bold-duotone" class="text-success me-1 align-middle"></iconify-icon> Approved:</span>
                        <strong class="text-success font-monospace">{{ $approvedProducts }} Live</strong>
                    </a>
                    <a href="{{ route('vendor.products.index', ['status' => 'pending']) }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 interactive-row">
                        <span class="text-muted"><iconify-icon icon="solar:clock-circle-bold-duotone" class="text-warning me-1 align-middle"></iconify-icon> Pending:</span>
                        <strong class="text-warning font-monospace">{{ $pendingProducts }} Items</strong>
                    </a>
                </div>
            </div>
        </div>

        {{-- Card 4: Outlets & Network --}}
        <div class="col-xl-3 col-md-6">
            <div class="kpi-card-v2 border-start border-3 border-warning">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <a href="{{ route('vendor.pickup-stores.index') }}" class="kpi-title-v2 text-decoration-none text-slate-600 hover-primary">
                            Outlets &amp; Network <iconify-icon icon="solar:alt-arrow-right-linear" class="align-middle fs-11"></iconify-icon>
                        </a>
                        <a href="{{ route('vendor.pickup-stores.index') }}" class="icon-box-lg bg-warning-subtle text-warning text-decoration-none">
                            <iconify-icon icon="solar:shop-bold-duotone" class="fs-18"></iconify-icon>
                        </a>
                    </div>
                    <a href="{{ route('vendor.pickup-stores.index') }}" class="kpi-value-v2 text-decoration-none text-dark d-block">
                        {{ number_format($totalPickupStores) }}
                        <span class="fs-12 fw-normal text-muted">Outlets</span>
                    </a>
                </div>
                <div class="pt-2 border-top mt-1.5">
                    <a href="{{ route('vendor.sub-sellers.index') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 mb-1 interactive-row">
                        <span class="text-muted">Sub-Sellers:</span>
                        <strong class="text-slate-800 font-monospace">{{ $totalSubSellers }}</strong>
                    </a>
                    <a href="{{ route('vendor.pickup-stores.index') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 interactive-row">
                        <span class="text-muted">Active Outlets:</span>
                        <strong class="text-success font-monospace">{{ $activePickupStores }} Outlets</strong>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 3. Additional Operational KPI Cards Grid (Row 2 Grid) ── --}}
    <div class="row g-2.5 mb-3">
        {{-- Card 5: Dispatched Orders --}}
        <div class="col-lg-3 col-md-6">
            <div class="kpi-card-v2 border-start border-3 border-indigo" style="border-left-color: #6366f1 !important;">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <a href="{{ route('vendor.purchase-orders.dispatched') }}" class="kpi-title-v2 text-decoration-none text-slate-600 hover-primary">
                            Dispatched Orders <iconify-icon icon="solar:alt-arrow-right-linear" class="align-middle fs-11"></iconify-icon>
                        </a>
                        <a href="{{ route('vendor.purchase-orders.dispatched') }}" class="icon-box-lg text-decoration-none" style="background-color: #e0e7ff; color: #4338ca;">
                            <iconify-icon icon="solar:delivery-bold-duotone" class="fs-18"></iconify-icon>
                        </a>
                    </div>
                    <div class="d-flex align-items-baseline justify-content-between">
                        <a href="{{ route('vendor.purchase-orders.dispatched') }}" class="kpi-value-v2 text-decoration-none text-dark d-block mb-0">
                            {{ $dispatchedPoCount }}
                            <span class="fs-12 fw-normal text-muted">Orders</span>
                        </a>
                        <a href="{{ route('vendor.purchase-orders.dispatched') }}" class="badge bg-primary-subtle text-primary border border-primary-subtle fs-10 px-1.5 py-0.5 fw-bold text-decoration-none">
                            In-Transit
                        </a>
                    </div>
                </div>
                <div class="pt-2 border-top mt-1.5">
                    <a href="{{ route('vendor.purchase-orders.dispatched') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 mb-1 interactive-row">
                        <span class="text-muted"><iconify-icon icon="solar:delivery-bold-duotone" class="text-primary me-1 align-middle"></iconify-icon> Dispatched:</span>
                        <strong class="text-indigo font-monospace">{{ $dispatchedPoCount }} Orders</strong>
                    </a>
                    <a href="{{ route('vendor.purchase-orders.accepted') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 interactive-row">
                        <span class="text-muted"><iconify-icon icon="solar:check-circle-bold-duotone" class="text-success me-1 align-middle"></iconify-icon> Accepted:</span>
                        <strong class="text-success font-monospace">{{ $acceptedPoCount }} Orders</strong>
                    </a>
                </div>
            </div>
        </div>

        {{-- Card 6: Cancelled & Rejected Orders --}}
        <div class="col-lg-3 col-md-6">
            <div class="kpi-card-v2 border-start border-3 border-danger">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <a href="{{ route('vendor.purchase-orders.cancelled') }}" class="kpi-title-v2 text-decoration-none text-slate-600 hover-primary">
                            Cancelled / Rejected <iconify-icon icon="solar:alt-arrow-right-linear" class="align-middle fs-11"></iconify-icon>
                        </a>
                        <a href="{{ route('vendor.purchase-orders.cancelled') }}" class="icon-box-lg bg-danger-subtle text-danger text-decoration-none">
                            <iconify-icon icon="solar:close-circle-bold-duotone" class="fs-18"></iconify-icon>
                        </a>
                    </div>
                    <div class="d-flex align-items-baseline justify-content-between">
                        <a href="{{ route('vendor.purchase-orders.cancelled') }}" class="kpi-value-v2 text-decoration-none text-danger d-block mb-0">
                            {{ $cancelledPoCount }}
                            <span class="fs-12 fw-normal text-muted">Orders</span>
                        </a>
                        <a href="{{ route('vendor.purchase-orders.cancelled') }}" class="btn btn-xs btn-outline-danger fw-semibold py-0.5 px-2">View List</a>
                    </div>
                </div>
                <div class="pt-2 border-top mt-1.5">
                    <a href="{{ route('vendor.purchase-orders.cancelled') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 mb-1 interactive-row">
                        <span class="text-danger fw-semibold"><iconify-icon icon="solar:close-circle-bold-duotone" class="text-danger me-1 align-middle"></iconify-icon> Cancelled POs:</span>
                        <strong class="text-danger font-monospace">{{ $cancelledPoCount }}</strong>
                    </a>
                    <a href="{{ route('vendor.purchase-orders.index') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 interactive-row">
                        <span class="text-muted"><iconify-icon icon="solar:bill-list-bold-duotone" class="text-primary me-1 align-middle"></iconify-icon> Total POs:</span>
                        <strong class="text-slate-800 font-monospace">{{ $totalPos }} Orders</strong>
                    </a>
                </div>
            </div>
        </div>

        {{-- Card 7: Smart Sales Incentive (SSI) Summary --}}
        <div class="col-lg-3 col-md-6">
            <div class="kpi-card-v2 border-start border-3 border-emerald" style="border-left-color: #047857 !important;">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <a href="{{ route('vendor.smart-sales-incentive.deductions') }}" class="kpi-title-v2 text-decoration-none text-slate-600 hover-primary">
                            SSI Incentive Log <iconify-icon icon="solar:alt-arrow-right-linear" class="align-middle fs-11"></iconify-icon>
                        </a>
                        <a href="{{ route('vendor.smart-sales-incentive.deductions') }}" class="icon-box-lg text-decoration-none" style="background-color: #d1fae5; color: #047857;">
                            <iconify-icon icon="solar:ticket-sale-bold-duotone" class="fs-18"></iconify-icon>
                        </a>
                    </div>
                    <div class="d-flex align-items-baseline justify-content-between">
                        <a href="{{ route('vendor.smart-sales-incentive.deductions') }}" class="kpi-value-v2 text-decoration-none text-dark d-block mb-0">
                            ₹{{ number_format($totalSsiDeducted, 2) }}
                        </a>
                        <a href="{{ route('vendor.smart-sales-incentive.deductions') }}" class="btn btn-xs btn-outline-success fw-semibold py-0.5 px-2">Log &rarr;</a>
                    </div>
                </div>
                <div class="pt-2 border-top mt-1.5">
                    <a href="{{ route('vendor.smart-sales-incentive') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 mb-1 interactive-row">
                        <span class="text-muted">Subscription Status:</span>
                        @if($isDdcSubscribed)
                            <strong class="text-success font-monospace">DDC Active &rarr;</strong>
                        @else
                            <strong class="text-info font-monospace">FSI Active &rarr;</strong>
                        @endif
                    </a>
                </div>
            </div>
        </div>

        {{-- Card 8: Sub-Sellers Directory --}}
        <div class="col-lg-3 col-md-6">
            <div class="kpi-card-v2 border-start border-3 border-warning">
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <a href="{{ route('vendor.sub-sellers.index') }}" class="kpi-title-v2 text-decoration-none text-slate-600 hover-primary">
                            Sub-Sellers Network <iconify-icon icon="solar:alt-arrow-right-linear" class="align-middle fs-11"></iconify-icon>
                        </a>
                        <a href="{{ route('vendor.sub-sellers.index') }}" class="icon-box-lg bg-warning-subtle text-warning text-decoration-none">
                            <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" class="fs-18"></iconify-icon>
                        </a>
                    </div>
                    <div class="d-flex align-items-baseline justify-content-between">
                        <a href="{{ route('vendor.sub-sellers.index') }}" class="kpi-value-v2 text-decoration-none text-dark d-block mb-0">
                            {{ $totalSubSellers }}
                            <span class="fs-12 fw-normal text-muted">Sellers</span>
                        </a>
                        <a href="{{ route('vendor.sub-sellers.index') }}" class="btn btn-xs btn-outline-warning fw-semibold py-0.5 px-2">Manage</a>
                    </div>
                </div>
                <div class="pt-2 border-top mt-1.5">
                    <a href="{{ route('vendor.pickup-stores.index') }}" class="d-flex align-items-center justify-content-between text-decoration-none fs-11.5 mb-1 interactive-row">
                        <span class="text-muted"><iconify-icon icon="solar:shop-bold-duotone" class="text-warning me-1 align-middle"></iconify-icon> Outlets:</span>
                        <strong class="text-slate-800 font-monospace">{{ $totalPickupStores }} Outlets</strong>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── 4. Smart Sales Incentive (SSI) Alert Banner ── --}}
    @if($totalSsiDeducted > 0 || $isDdcSubscribed)
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm bg-white border-start border-4 border-emerald" style="border-left-color: #047857 !important;">
                <div class="card-body p-3.5 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-2.5 rounded-circle flex-shrink-0" style="background: rgba(4, 120, 87, 0.12); color: #047857;">
                            <iconify-icon icon="solar:ticket-sale-bold-duotone" class="fs-24"></iconify-icon>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-0.5" style="font-family: 'Outfit', sans-serif;">Smart Sales Incentive (SSI) Deductions Log</h6>
                            <p class="text-muted fs-13 mb-0">Total SSI Amount Deducted on Accepted POs: <strong class="text-danger font-monospace">₹{{ number_format($totalSsiDeducted, 2) }}</strong></p>
                        </div>
                    </div>
                    <a href="{{ route('vendor.smart-sales-incentive.deductions') }}" class="btn btn-sm btn-outline-success px-3 py-1.5 fw-semibold flex-shrink-0">
                        View SSI Deductions Log &rarr;
                    </a>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- ── 5. Two-Column Layout: Recent Purchase Orders & Recently Added Products ── --}}
    <div class="row g-3 mb-4">
        {{-- Left Column: Recent Purchase Orders Table --}}
        <div class="col-xl-7 col-lg-7">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom">
                    <h6 class="fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2" style="font-family: 'Outfit', sans-serif;">
                        <iconify-icon icon="solar:bill-list-bold-duotone" class="text-primary fs-18"></iconify-icon>
                        Recent Purchase Orders
                    </h6>
                    <a href="{{ route('vendor.purchase-orders.index') }}" class="btn btn-xs btn-outline-primary fw-semibold px-2.5">
                        View All Orders <i class="bx bx-right-arrow-alt align-middle ms-0.5"></i>
                    </a>
                </div>
                <div class="card-body p-0 overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 text-nowrap">
                            <thead class="table-light fs-12">
                                <tr>
                                    <th>PO Number</th>
                                    <th>DDC Hub</th>
                                    <th>Date</th>
                                    <th class="text-center">Status</th>
                                    <th class="text-end text-nowrap">Amount (₹)</th>
                                    <th class="text-end pe-3">Action</th>
                                </tr>
                            </thead>
                            <tbody class="fs-13">
                                @forelse($recentPos as $po)
                                    <tr>
                                        <td>
                                            <a href="{{ route('vendor.purchase-orders.show', $po->id) }}" class="fw-bold text-primary font-monospace d-block text-decoration-none">
                                                {{ $po->po_number }}
                                            </a>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-slate-800 d-block fs-12">{{ $po->ddc?->name ?? 'DDC Hub' }}</span>
                                            <small class="text-muted fs-11">{{ $po->ddc?->profile?->district ?? '' }}</small>
                                        </td>
                                        <td class="text-muted fs-12">
                                            {{ $po->created_at->format('d M Y, h:i A') }}
                                        </td>
                                        <td class="text-center">
                                            @if($po->status === 'Accepted')
                                                <span class="badge bg-success-subtle text-success px-2.5 py-1 fs-11">Accepted</span>
                                            @elseif($po->status === 'Dispatched')
                                                <span class="badge bg-primary-subtle text-primary px-2.5 py-1 fs-11">Dispatched</span>
                                            @elseif($po->status === 'Pending')
                                                <span class="badge bg-warning-subtle text-warning px-2.5 py-1 fs-11">Pending</span>
                                            @elseif($po->status === 'Rejected' || $po->status === 'Cancelled')
                                                <span class="badge bg-danger-subtle text-danger px-2.5 py-1 fs-11">{{ $po->status }}</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1 fs-11">{{ $po->status }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end fw-bold text-slate-800 font-monospace">
                                            ₹{{ number_format($po->seller_accepted_amount > 0 ? $po->seller_accepted_amount : ($po->seller_total_amount > 0 ? $po->seller_total_amount : $po->total_amount), 2) }}
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('vendor.purchase-orders.show', $po->id) }}" class="btn btn-xs btn-outline-primary px-2 py-1 fw-semibold">
                                                Details
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-4 text-muted fs-13">
                                            <iconify-icon icon="solar:document-text-bold-duotone" class="fs-32 text-slate-300 mb-1 d-block"></iconify-icon>
                                            No purchase orders assigned yet.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column: Recently Added Products --}}
        <div class="col-xl-5 col-lg-5">
            <div class="card border-0 shadow-sm h-100 overflow-hidden">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between border-bottom min-w-0">
                    <h6 class="fw-bold text-slate-800 mb-0 d-flex align-items-center gap-1.5 text-nowrap me-2" style="font-family: 'Outfit', sans-serif;">
                        <iconify-icon icon="solar:box-bold-duotone" class="text-info fs-18 flex-shrink-0"></iconify-icon>
                        <span>Recent Products</span>
                    </h6>
                    <a href="{{ route('vendor.products.index') }}" class="btn btn-xs btn-outline-info fw-semibold px-2.5 flex-shrink-0">
                        Catalog ({{ $totalProducts }})
                    </a>
                </div>
                <div class="card-body p-0 overflow-hidden">
                    <div>
                        @forelse($recentProducts as $prod)
                            @php
                                $prodStatusLower = strtolower($prod->status ?? 'pending');
                                $priceVal = (float) ($prod->price ?? ($prod->seller_price_incl_gst ?? $prod->mrp ?? 0));
                                $imgSrc = null;
                                if ($prod->featured_image) {
                                    $imgSrc = filter_var($prod->featured_image, FILTER_VALIDATE_URL) 
                                        ? $prod->featured_image 
                                        : asset('storage/' . $prod->featured_image);
                                }
                            @endphp
                            <div class="product-item-card">
                                <div class="d-flex align-items-center gap-3 w-100">
                                    <div class="product-img-box flex-shrink-0" style="width: 42px; height: 42px;">
                                        @if($imgSrc)
                                            <img src="{{ $imgSrc }}" alt="{{ $prod->product_name }}" class="img-fluid object-fit-contain p-0.5" style="max-height: 100%; width: 100%;">
                                        @else
                                            <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="text-slate-400 fs-22"></iconify-icon>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1 min-w-0">
                                        <div class="d-flex align-items-center justify-content-between gap-2 mb-1">
                                            <h6 class="mb-0 fs-13 fw-bold text-dark text-truncate flex-grow-1 min-w-0" title="{{ $prod->product_name }}">
                                                {{ $prod->product_name }}
                                            </h6>
                                            <span class="fw-extrabold text-slate-900 fs-13 font-monospace flex-shrink-0 ms-1 text-nowrap">
                                                ₹{{ number_format($priceVal, 2) }}
                                            </span>
                                        </div>
                                        <div class="d-flex align-items-center justify-content-between gap-2 flex-wrap">
                                            <div class="d-flex align-items-center gap-1.5 min-w-0 overflow-hidden">
                                                <span class="font-monospace fs-11 fw-semibold text-slate-700 bg-light px-1.5 py-0.5 rounded border text-nowrap">
                                                    Code: {{ $prod->product_code }}
                                                </span>
                                                @if($prod->category)
                                                    <span class="badge bg-light text-muted fs-10 fw-normal border px-1.5 py-0.5 text-truncate" style="max-width: 120px;" title="{{ $prod->category->name }}">{{ $prod->category->name }}</span>
                                                @endif
                                            </div>
                                            <div class="flex-shrink-0">
                                                @if($prodStatusLower === 'approved')
                                                    <span class="badge bg-success-subtle text-success px-2 py-0.5 fs-10 fw-bold rounded-pill">
                                                        <iconify-icon icon="solar:check-circle-bold" class="align-middle me-0.5"></iconify-icon> Approved
                                                    </span>
                                                @elseif($prodStatusLower === 'rejected')
                                                    <span class="badge bg-danger-subtle text-danger px-2 py-0.5 fs-10 fw-bold rounded-pill">
                                                        <iconify-icon icon="solar:close-circle-bold" class="align-middle me-0.5"></iconify-icon> Rejected
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning px-2 py-0.5 fs-10 fw-bold rounded-pill">
                                                        <iconify-icon icon="solar:clock-circle-bold" class="align-middle me-0.5"></iconify-icon> Pending
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted fs-13">
                                <iconify-icon icon="solar:box-linear" class="fs-32 text-slate-300 mb-1 d-block"></iconify-icon>
                                No products added yet.
                                <div class="mt-2">
                                    <a href="{{ route('vendor.products.create') }}" class="btn btn-xs btn-primary">Add First Product</a>
                                </div>
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="card-footer bg-light text-center py-2.5">
                    <a href="{{ route('vendor.products.index') }}" class="text-primary fw-semibold fs-12 text-decoration-none">
                        View Complete Catalog ({{ $totalProducts }} items) <i class="bx bx-right-arrow-alt align-middle"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection
