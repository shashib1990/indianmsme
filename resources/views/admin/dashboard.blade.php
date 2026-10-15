@extends('layouts.admin')

@section('title', 'Admin Overview Dashboard')
@section('page-title', 'Admin Overview Dashboard')

@section('content')
<div class="px-1 px-md-2">
    {{-- Header Banner & Quick Action Buttons --}}
    <div class="card border-0 shadow-sm mb-3" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); border-radius: 12px;">
        <div class="card-body py-2.5 px-3 text-white">
            <div class="row align-items-center">
                <div class="col-md-7">
                    <div class="d-flex align-items-center gap-2.5">
                        <div class="avatar-md rounded-circle bg-white bg-opacity-10 d-flex align-items-center justify-content-center p-1.5">
                            <iconify-icon icon="solar:shield-user-bold-duotone" class="text-warning fs-28"></iconify-icon>
                        </div>
                        <div>
                            <h4 class="fw-bold mb-0 text-white fs-18">Welcome, {{ auth('admin')->user()->name ?? 'Administrator' }}!</h4>
                            <div class="text-slate-300 fs-12 d-flex align-items-center gap-2">
                                <span>Indian MSME Central Control & Ecosystem Overview</span>
                                <span class="badge bg-primary bg-opacity-20 text-info border border-info border-opacity-25 px-2 py-0.5 fs-11">
                                    <iconify-icon icon="solar:calendar-date-bold-duotone" class="me-1 align-middle"></iconify-icon> {{ date('d M Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 text-md-end mt-2 mt-md-0">
                    <div class="d-flex flex-wrap gap-1.5 justify-content-md-end">
                        <a href="{{ route('admin.external.ddc.create') }}" class="btn btn-xs px-2.5 py-1.5 fs-12 fw-semibold text-white d-inline-flex align-items-center gap-1 shadow-sm" style="background: rgba(255, 255, 255, 0.16); border: 1px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(8px); border-radius: 0 !important;">
                            <iconify-icon icon="solar:add-circle-bold-duotone" class="fs-15 text-info"></iconify-icon> Add DDC
                        </a>
                        <a href="{{ route('admin.external.adc.index') }}" class="btn btn-xs px-2.5 py-1.5 fs-12 fw-semibold text-white d-inline-flex align-items-center gap-1 shadow-sm" style="background: rgba(255, 255, 255, 0.16); border: 1px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(8px); border-radius: 0 !important;">
                            <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" class="fs-15 text-primary"></iconify-icon> Manage ADCs
                        </a>
                        <a href="{{ route('admin.external.vendors.index') }}" class="btn btn-xs px-2.5 py-1.5 fs-12 fw-semibold text-white d-inline-flex align-items-center gap-1 shadow-sm" style="background: rgba(255, 255, 255, 0.16); border: 1px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(8px); border-radius: 0 !important;">
                            <iconify-icon icon="solar:shop-bold-duotone" class="fs-15 text-success"></iconify-icon> Manage Vendors
                        </a>
                        <a href="{{ route('admin.products.approvals.new') }}" class="btn btn-xs px-2.5 py-1.5 fs-12 fw-semibold text-white d-inline-flex align-items-center gap-1 shadow-sm" style="background: rgba(255, 255, 255, 0.16); border: 1px solid rgba(255, 255, 255, 0.3); backdrop-filter: blur(8px); border-radius: 0 !important;">
                            <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="fs-15 text-warning"></iconify-icon> Product Queue
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- High-End Admin Metric Cards System --}}
    @push('styles')
    <style>
        /* Category Header Title Pill Bar */
        .imsme-category-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-top: 14px;
            margin-bottom: 10px;
        }

        .imsme-category-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            color: #ffffff;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.5px;
            text-transform: uppercase;
            padding: 5px 14px;
            border-radius: 20px;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.15);
        }

        .imsme-category-pill iconify-icon {
            font-size: 1rem;
            color: #38bdf8;
        }

        .imsme-category-line {
            flex-grow: 1;
            height: 1px;
            background: linear-gradient(90deg, #e2e8f0 0%, rgba(226, 232, 240, 0) 100%);
            margin-left: 12px;
        }

        /* Modern Card Item System */
        .imsme-metric-card {
            background: #ffffff;
            border: 1px solid #e2e8f0 !important;
            border-radius: 12px !important;
            padding: 12px 14px;
            display: flex;
            align-items: center;
            text-decoration: none !important;
            color: #1e293b !important;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
            min-height: 72px;
        }

        .imsme-metric-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            border-radius: 4px 0 0 4px;
            opacity: 0.85;
            transition: width 0.2s ease;
        }

        .imsme-metric-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 24px -6px rgba(15, 23, 42, 0.1) !important;
            border-color: #cbd5e1 !important;
        }

        .imsme-metric-card:hover::before {
            width: 6px;
        }

        .imsme-metric-card .icon-wrapper {
            width: 44px;
            height: 44px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.4rem;
            margin-right: 12px;
            flex-shrink: 0;
            transition: transform 0.2s ease;
        }

        .imsme-metric-card:hover .icon-wrapper {
            transform: scale(1.08);
        }

        .imsme-metric-card .card-data {
            flex-grow: 1;
            min-width: 0;
        }

        .imsme-metric-card .card-data .label {
            font-size: 0.75rem;
            font-weight: 600;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.4px;
            margin-bottom: 2px;
            line-height: 1.2;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .imsme-metric-card .card-data .value {
            font-size: 1.25rem;
            font-weight: 800;
            color: #0f172a;
            line-height: 1.1;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .imsme-metric-card .card-data .sub-value {
            font-size: 0.72rem;
            font-weight: 600;
            color: #475569;
            margin-top: 2px;
        }

        .imsme-metric-card .arrow-hint {
            font-size: 1rem;
            color: #cbd5e1;
            transition: all 0.2s ease;
            margin-left: 6px;
            opacity: 0.5;
        }

        .imsme-metric-card:hover .arrow-hint {
            color: #4f46e5;
            transform: translateX(3px);
            opacity: 1;
        }

        /* Category Theme Accent Variations */
        /* Indigo / Violet */
        .theme-indigo::before { background: #6366f1; }
        .theme-indigo .icon-wrapper { background: rgba(99, 102, 241, 0.1); color: #6366f1; }

        /* Sky / Cyan */
        .theme-cyan::before { background: #0284c7; }
        .theme-cyan .icon-wrapper { background: rgba(2, 132, 199, 0.1); color: #0284c7; }

        /* Amber / Gold */
        .theme-amber::before { background: #f59e0b; }
        .theme-amber .icon-wrapper { background: rgba(245, 158, 11, 0.1); color: #d97706; }

        /* Emerald / Green */
        .theme-emerald::before { background: #10b981; }
        .theme-emerald .icon-wrapper { background: rgba(16, 185, 129, 0.1); color: #059669; }

        /* Rose / Red */
        .theme-rose::before { background: #f43f5e; }
        .theme-rose .icon-wrapper { background: rgba(244, 63, 94, 0.1); color: #e11d48; }

        /* Dark Mode Support */
        [data-bs-theme="dark"] .imsme-metric-card {
            background: #1e293b;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }
        [data-bs-theme="dark"] .imsme-metric-card .card-data .label {
            color: #94a3b8;
        }
        [data-bs-theme="dark"] .imsme-metric-card .card-data .value {
            color: #f1f5f9;
        }
        [data-bs-theme="dark"] .imsme-category-line {
            background: linear-gradient(90deg, #334155 0%, rgba(51, 65, 85, 0) 100%);
        }
    </style>
    @endpush

    {{-- SECTION 1: REGISTRATION --}}
    <div class="mb-3">
        <div class="imsme-category-header">
            <span class="imsme-category-pill">
                <iconify-icon icon="solar:user-plus-bold-duotone"></iconify-icon>
                Registration & Partners
            </span>
            <div class="imsme-category-line"></div>
        </div>
        <div class="row g-2 mb-2">
            {{-- MSME Sellers (All Registered) --}}
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                <a href="{{ route('admin.external.vendors.index') }}" class="imsme-metric-card theme-indigo">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:users-group-two-rounded-bold-duotone"></iconify-icon>
                    </div>
                    <div class="card-data">
                        <div class="label">MSME Sellers <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon></div>
                        <div class="value">{{ number_format($stats['msme']) }}</div>
                    </div>
                </a>
            </div>

            {{-- Active Seller (Account Active + KYC Approved + Min 1 Product + Pickup Store) --}}
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                <a href="{{ route('admin.external.vendors.index', ['filter_status' => 'active']) }}" class="imsme-metric-card theme-emerald">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon>
                    </div>
                    <div class="card-data">
                        <div class="label">Active Seller <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon></div>
                        <div class="value text-success">{{ number_format($stats['active_sellers']) }}</div>
                    </div>
                </a>
            </div>

            {{-- Inactive Seller (Fails any 1 of 4 conditions) --}}
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                <a href="{{ route('admin.external.vendors.index', ['filter_status' => 'inactive']) }}" class="imsme-metric-card theme-rose">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:danger-triangle-bold-duotone"></iconify-icon>
                    </div>
                    <div class="card-data">
                        <div class="label">Inactive Seller <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon></div>
                        <div class="value text-danger">{{ number_format($stats['inactive_sellers']) }}</div>
                    </div>
                </a>
            </div>

            {{-- Pickup Stores --}}
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                <a href="{{ route('admin.external.vendors.index') }}" class="imsme-metric-card theme-cyan">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:shop-bold-duotone"></iconify-icon>
                    </div>
                    <div class="card-data">
                        <div class="label">Pickup Stores <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon></div>
                        <div class="value">{{ number_format($stats['total_pickup_stores']) }}</div>
                    </div>
                </a>
            </div>
        </div>

        <div class="row g-2">
            {{-- ADC Partners (All) --}}
            <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                <a href="{{ route('admin.external.adc.index') }}" class="imsme-metric-card theme-cyan">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:shop-window-bold-duotone"></iconify-icon>
                    </div>
                    <div class="card-data">
                        <div class="label">ADC Partners <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon></div>
                        <div class="value">{{ number_format($stats['adc']) }}</div>
                    </div>
                </a>
            </div>

            {{-- Active ADC --}}
            <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                <a href="{{ route('admin.external.adc.index', ['filter_status' => 'active']) }}" class="imsme-metric-card theme-emerald">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon>
                    </div>
                    <div class="card-data">
                        <div class="label">Active ADC <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon></div>
                        <div class="value text-success">{{ number_format($stats['active_adcs']) }}</div>
                    </div>
                </a>
            </div>

            {{-- Inactive ADC --}}
            <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                <a href="{{ route('admin.external.adc.index', ['filter_status' => 'inactive']) }}" class="imsme-metric-card theme-rose">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:danger-triangle-bold-duotone"></iconify-icon>
                    </div>
                    <div class="card-data">
                        <div class="label">Inactive ADC <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon></div>
                        <div class="value text-danger">{{ number_format($stats['inactive_adcs']) }}</div>
                    </div>
                </a>
            </div>

            {{-- DDC Partners (All) --}}
            <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                <a href="{{ route('admin.external.ddc.index') }}" class="imsme-metric-card theme-amber">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:structure-bold-duotone"></iconify-icon>
                    </div>
                    <div class="card-data">
                        <div class="label">DDC Partners <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon></div>
                        <div class="value">{{ number_format($stats['ddc']) }}</div>
                    </div>
                </a>
            </div>

            {{-- Active DDC --}}
            <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                <a href="{{ route('admin.external.ddc.index', ['filter_status' => 'active']) }}" class="imsme-metric-card theme-emerald">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon>
                    </div>
                    <div class="card-data">
                        <div class="label">Active DDC <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon></div>
                        <div class="value text-success">{{ number_format($stats['active_ddcs']) }}</div>
                    </div>
                </a>
            </div>

            {{-- Inactive DDC --}}
            <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                <a href="{{ route('admin.external.ddc.index', ['filter_status' => 'inactive']) }}" class="imsme-metric-card theme-rose">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:danger-triangle-bold-duotone"></iconify-icon>
                    </div>
                    <div class="card-data">
                        <div class="label">Inactive DDC <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon></div>
                        <div class="value text-danger">{{ number_format($stats['inactive_ddcs']) }}</div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    {{-- SECTION 2: ORDERS --}}
    <div class="mb-3">
        <div class="imsme-category-header">
            <span class="imsme-category-pill">
                <iconify-icon icon="solar:cart-large-4-bold-duotone"></iconify-icon>
                Orders & Operations
            </span>
            <div class="imsme-category-line"></div>
        </div>
        <div class="row g-2">
            {{-- DDB PO --}}
            <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                <a href="{{ route('admin.purchase-orders.index') }}" class="imsme-metric-card theme-indigo">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:document-text-bold-duotone"></iconify-icon>
                    </div>
                    <div class="card-data">
                        <div class="label">DDB POs <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon></div>
                        <div class="value">{{ number_format($stats['ddb_po']) }}</div>
                    </div>
                </a>
            </div>
            {{-- Online Order --}}
            <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                <a href="{{ route('admin.adc-orders.index') }}" class="imsme-metric-card theme-cyan">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:cart-check-bold-duotone"></iconify-icon>
                    </div>
                    <div class="card-data">
                        <div class="label">Online Orders <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon></div>
                        <div class="value">{{ number_format($stats['online_order']) }}</div>
                    </div>
                </a>
            </div>
            {{-- Payment / Online Payment --}}
            <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                <a href="{{ route('admin.seller-payments.index') }}" class="imsme-metric-card theme-amber">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon>
                    </div>
                    <div class="card-data">
                        <div class="label">Payments <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-hint></div>
                        <div class="value" style="font-size: 1rem;">Paid: {{ number_format($stats['payment_count']) }}</div>
                        <div class="sub-value">Online: {{ number_format($stats['online_payment_count']) }}</div>
                    </div>
                </a>
            </div>
            {{-- Placed --}}
            <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                <a href="{{ route('admin.adc-orders.index', ['status' => 'PENDING']) }}" class="imsme-metric-card theme-rose">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:cart-bold-duotone"></iconify-icon>
                    </div>
                    <div class="card-data">
                        <div class="label">Placed <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon></div>
                        <div class="value">{{ number_format($stats['placed_orders']) }}</div>
                    </div>
                </a>
            </div>
            {{-- Dispatch --}}
            <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                <a href="{{ route('admin.adc-orders.index', ['status' => 'DISPATCHED']) }}" class="imsme-metric-card theme-indigo">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:delivery-bold-duotone"></iconify-icon>
                    </div>
                    <div class="card-data">
                        <div class="label">Dispatched <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon></div>
                        <div class="value">{{ number_format($stats['dispatch_orders']) }}</div>
                    </div>
                </a>
            </div>
            {{-- Received --}}
            <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                <a href="{{ route('admin.adc-orders.index', ['status' => 'COMPLETED']) }}" class="imsme-metric-card theme-emerald">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:check-read-bold-duotone"></iconify-icon>
                    </div>
                    <div class="card-data">
                        <div class="label">Received <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon></div>
                        <div class="value">{{ number_format($stats['received_orders']) }}</div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    {{-- SECTION 3: ACCOUNT --}}
    <div class="mb-3">
        <div class="imsme-category-header">
            <span class="imsme-category-pill">
                <iconify-icon icon="solar:chart-2-bold-duotone"></iconify-icon>
                Ecosystem Sales & Revenue
            </span>
            <div class="imsme-category-line"></div>
        </div>
        <div class="row g-2">
            {{-- DDB Sales --}}
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                <a href="{{ route('admin.purchase-orders.index') }}" class="imsme-metric-card theme-indigo">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:chart-2-bold-duotone"></iconify-icon>
                    </div>
                    <div class="card-data">
                        <div class="label">DDB PO Sales Revenue <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon></div>
                        <div class="value">₹{{ number_format($stats['ddb_sales'], 2) }}</div>
                    </div>
                </a>
            </div>
            {{-- Online Sales --}}
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                <a href="{{ route('admin.adc-orders.index') }}" class="imsme-metric-card theme-cyan">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:graph-up-bold-duotone"></iconify-icon>
                    </div>
                    <div class="card-data">
                        <div class="label">Online Supply Revenue <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon></div>
                        <div class="value">₹{{ number_format($stats['online_sales'], 2) }}</div>
                    </div>
                </a>
            </div>
            {{-- POS Sales --}}
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                <a href="{{ route('admin.adc-orders.index') }}" class="imsme-metric-card theme-amber">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:shop-bold-duotone"></iconify-icon>
                    </div>
                    <div class="card-data">
                        <div class="label">POS Outlets Sales <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon></div>
                        <div class="value">₹{{ number_format($stats['total_pos_revenue'], 2) }}</div>
                    </div>
                </a>
            </div>
            {{-- Total Sales --}}
            <div class="col-xl-3 col-lg-3 col-md-6 col-sm-6">
                <a href="{{ route('admin.purchase-orders.index') }}" class="imsme-metric-card theme-emerald">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:wallet-bold-duotone"></iconify-icon>
                    </div>
                    <div class="card-data">
                        <div class="label">Grand Ecosystem Revenue <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon></div>
                        <div class="value">₹{{ number_format($stats['total_sales'], 2) }}</div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    {{-- SECTION 4: PRODUCT --}}
    <div class="mb-3">
        <div class="imsme-category-header">
            <span class="imsme-category-pill">
                <iconify-icon icon="solar:box-bold-duotone"></iconify-icon>
                Product Catalog & Approvals
            </span>
            <div class="imsme-category-line"></div>
        </div>
        <div class="row g-2">
            {{-- Product --}}
            <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                <a href="{{ route('admin.products.all') }}" class="imsme-metric-card theme-indigo">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:box-minimalistic-bold-duotone"></iconify-icon>
                    </div>
                    <div class="card-data">
                        <div class="label">Total Products <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon></div>
                        <div class="value">{{ number_format($stats['total_products']) }}</div>
                    </div>
                </a>
            </div>
            {{-- Activated --}}
            <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                <a href="{{ route('admin.products.all', ['status' => 'Approved']) }}" class="imsme-metric-card theme-emerald">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon>
                    </div>
                    <div class="card-data">
                        <div class="label">Activated <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon></div>
                        <div class="value">{{ number_format($stats['activated_products']) }}</div>
                    </div>
                </a>
            </div>
            {{-- Deactivated --}}
            <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                <a href="{{ route('admin.products.approvals.rejected') }}" class="imsme-metric-card theme-rose">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:forbidden-circle-bold-duotone"></iconify-icon>
                    </div>
                    <div class="card-data">
                        <div class="label">Deactivated <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon></div>
                        <div class="value">{{ number_format($stats['deactivated_products']) }}</div>
                    </div>
                </a>
            </div>
            {{-- Review --}}
            <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                <a href="{{ route('admin.products.approvals.new') }}" class="imsme-metric-card theme-amber">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:clock-circle-bold-duotone"></iconify-icon>
                    </div>
                    <div class="card-data">
                        <div class="label">In Review <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon></div>
                        <div class="value">{{ number_format($stats['review_products']) }}</div>
                    </div>
                </a>
            </div>
            {{-- Overcosted --}}
            <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                <a href="{{ route('admin.products.all', ['filter' => 'overcosted']) }}" class="imsme-metric-card theme-cyan">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:danger-circle-bold-duotone"></iconify-icon>
                    </div>
                    <div class="card-data">
                        <div class="label">Overcosted <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon></div>
                        <div class="value">{{ number_format($stats['overcosted_products']) }}</div>
                    </div>
                </a>
            </div>
            {{-- Total Live Products --}}
            <div class="col-xl-2 col-lg-4 col-md-4 col-sm-6">
                <a href="{{ route('admin.products.all', ['status' => 'Approved']) }}" class="imsme-metric-card theme-emerald">
                    <div class="icon-wrapper">
                        <iconify-icon icon="solar:shield-check-bold-duotone"></iconify-icon>
                    </div>
                    <div class="card-data">
                        <div class="label">Total Live <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon></div>
                        <div class="value">{{ number_format($stats['total_live_products']) }}</div>
                    </div>
                </a>
            </div>
        </div>
    </div>

    {{-- Top Section: Wallet Deposits & Storage Racks (Row 3 - 4 Compact Cards) --}}
    <div class="row g-2 mb-3">
        {{-- DDC Wallet Requests --}}
        <div class="col-xl-3 col-md-6 col-6">
            <div class="card border-0 shadow-sm border-start border-3 border-info" style="border-radius: 8px;">
                <div class="card-body p-2 px-2.5">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-11 fw-semibold">DDC Wallet Reqs</span>
                            <div class="fs-15 fw-bold text-slate-800 my-0">₹{{ number_format($stats['pending_ddc_wallet_requests_amount'], 2) }}</div>
                            <span class="text-info fs-10 fw-medium">{{ $stats['pending_ddc_wallet_requests_count'] }} Reqs Pending</span>
                        </div>
                        <a href="{{ route('admin.external.ddc.wallet-requests') }}" class="btn btn-sm btn-soft-info p-1 px-1.5" title="Manage Wallet Requests">
                            <iconify-icon icon="solar:wallet-bold-duotone" class="fs-18"></iconify-icon>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- ADC Wallet Requests --}}
        <div class="col-xl-3 col-md-6 col-6">
            <div class="card border-0 shadow-sm border-start border-3 border-purple" style="border-radius: 8px; border-color: #8b5cf6 !important;">
                <div class="card-body p-2 px-2.5">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-11 fw-semibold">ADC Wallet Reqs</span>
                            <div class="fs-15 fw-bold text-slate-800 my-0">₹{{ number_format($stats['pending_adc_wallet_requests_amount'], 2) }}</div>
                            <span class="fs-10 fw-medium" style="color: #8b5cf6;">{{ $stats['pending_adc_wallet_requests_count'] }} Reqs Pending</span>
                        </div>
                        <a href="{{ route('admin.external.adc.index') }}" class="btn btn-sm p-1 px-1.5" style="background-color: rgba(139,92,246,0.1); color: #8b5cf6;" title="Manage ADC Requests">
                            <iconify-icon icon="solar:card-send-bold-duotone" class="fs-18"></iconify-icon>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Active Incentive Holds --}}
        <div class="col-xl-3 col-md-6 col-6">
            <div class="card border-0 shadow-sm border-start border-3 border-danger" style="border-radius: 8px;">
                <div class="card-body p-2 px-2.5">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-11 fw-semibold">AMA Incentive Holds</span>
                            <div class="fs-15 fw-bold text-slate-800 my-0">₹{{ number_format($stats['active_incentive_holds_amount'], 2) }}</div>
                            <span class="text-danger fs-10 fw-medium">{{ $stats['active_incentive_holds_count'] }} Held Transactions</span>
                        </div>
                        <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-sm btn-soft-danger p-1 px-1.5" title="View Holds">
                            <iconify-icon icon="solar:lock-keyhole-bold-duotone" class="fs-18"></iconify-icon>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Depot Storage Racks --}}
        <div class="col-xl-3 col-md-6 col-6">
            <div class="card border-0 shadow-sm border-start border-3 border-success" style="border-radius: 8px;">
                <div class="card-body p-2 px-2.5">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-11 fw-semibold">Depot Storage Racks</span>
                            <div class="fs-15 fw-bold text-slate-800 my-0">{{ number_format($stats['total_racks_count']) }} Racks Total</div>
                            <span class="text-success fs-10 fw-medium">{{ $stats['active_rack_bookings_count'] }} Booked by Sellers</span>
                        </div>
                        <div class="avatar-xs rounded bg-success bg-opacity-10 d-flex align-items-center justify-content-center p-1">
                            <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="fs-18 text-success"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Charts Section --}}
    <div class="row g-2 mb-3">
        {{-- Sales & Multi-Channel Trend Chart --}}
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 10px;">
                <div class="card-header bg-white py-2 px-3 d-flex align-items-center justify-content-between border-bottom border-light">
                    <h6 class="card-title mb-0 fw-bold text-slate-800 fs-14">
                        <iconify-icon icon="solar:chart-2-bold-duotone" class="me-1 text-primary align-middle"></iconify-icon>
                        Multi-Channel Revenue & Volume Performance (Last 6 Months)
                    </h6>
                </div>
                <div class="card-body p-2">
                    <div id="multiChannelRevenueChart" style="min-height: 290px;"></div>
                </div>
            </div>
        </div>

        {{-- Donut Chart Column --}}
        <div class="col-lg-4">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 10px;">
                <div class="card-header bg-white py-2 px-3 border-bottom border-light">
                    <h6 class="card-title mb-0 fw-bold text-slate-800 fs-14">
                        <iconify-icon icon="solar:pie-chart-3-bold-duotone" class="me-1 text-info align-middle"></iconify-icon>
                        Channel Revenue Ratio
                    </h6>
                </div>
                <div class="card-body d-flex flex-column align-items-center justify-content-center p-2">
                    <div id="channelContributionChart" style="width: 100%; min-height: 240px;"></div>
                    <div class="w-100 border-top pt-2 mt-1">
                        <div class="row text-center fs-11">
                            <div class="col-4">
                                <div class="fw-bold text-slate-800">₹{{ number_format($stats['total_po_revenue'], 0) }}</div>
                                <div class="text-muted fs-10">B2B POs</div>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold text-slate-800">₹{{ number_format($stats['total_adc_order_revenue'], 0) }}</div>
                                <div class="text-muted fs-10">ADC Supply</div>
                            </div>
                            <div class="col-4">
                                <div class="fw-bold text-slate-800">₹{{ number_format($stats['total_pos_revenue'], 0) }}</div>
                                <div class="text-muted fs-10">POS Retail</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Live Data Queues (Purchase Orders & ADC Orders) --}}
    <div class="row g-2 mb-3">
        {{-- Recent Purchase Orders --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 10px;">
                <div class="card-header bg-white py-2 px-3 d-flex align-items-center justify-content-between border-bottom border-light">
                    <h6 class="card-title mb-0 fw-bold text-slate-800 fs-14">
                        <iconify-icon icon="solar:document-text-bold-duotone" class="me-1 text-primary align-middle"></iconify-icon>
                        Recent Purchase Orders (POs)
                    </h6>
                    <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-sm btn-link text-primary p-0 fw-semibold fs-11">View All &rarr;</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 fs-12">
                        <thead class="bg-light text-muted fs-11">
                            <tr>
                                <th>PO Number</th>
                                <th>Seller / Destination DDC</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentPurchaseOrders as $po)
                                <tr>
                                    <td class="fw-semibold text-slate-800">
                                        <a href="{{ route('admin.purchase-orders.show', $po->id) }}" class="text-decoration-none text-primary">
                                            {{ $po->po_number }}
                                        </a>
                                        <div class="text-muted fs-10">{{ $po->created_at->format('d M, h:i A') }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-slate-800">{{ $po->seller->sellerProfile->company_name ?? $po->seller->name ?? '—' }}</div>
                                        <div class="text-muted fs-10">To: {{ $po->ddc->profile->ddc_code ?? $po->ddc->name ?? '—' }}</div>
                                    </td>
                                    <td class="fw-bold text-slate-800">₹{{ number_format($po->total_amount, 2) }}</td>
                                    <td>
                                        @if($po->status === 'Completed')
                                            <span class="badge bg-success bg-opacity-10 text-success px-1.5 py-0.5 fs-10">Completed</span>
                                        @elseif($po->status === 'Rejected')
                                            <span class="badge bg-danger bg-opacity-10 text-danger px-1.5 py-0.5 fs-10">Rejected</span>
                                        @else
                                            <span class="badge bg-warning bg-opacity-10 text-warning px-1.5 py-0.5 fs-10">{{ $po->status }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.purchase-orders.show', $po->id) }}" class="btn btn-sm btn-light p-1" title="View Details">
                                            <iconify-icon icon="solar:eye-bold-duotone" class="fs-15"></iconify-icon>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-3 text-muted fs-12">No recent purchase orders found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Recent ADC Supply Orders --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 10px;">
                <div class="card-header bg-white py-2 px-3 d-flex align-items-center justify-content-between border-bottom border-light">
                    <h6 class="card-title mb-0 fw-bold text-slate-800 fs-14">
                        <iconify-icon icon="solar:cart-check-bold-duotone" class="me-1 text-info align-middle"></iconify-icon>
                        Recent ADC Supply Orders
                    </h6>
                    <a href="{{ route('admin.adc-orders.index') }}" class="btn btn-sm btn-link text-info p-0 fw-semibold fs-11">View All &rarr;</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 fs-12">
                        <thead class="bg-light text-muted fs-11">
                            <tr>
                                <th>Order No</th>
                                <th>ADC Canteen / Supply DDC</th>
                                <th>Amount</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentAdcOrders as $adcOrder)
                                <tr>
                                    <td class="fw-semibold text-slate-800">
                                        <a href="{{ route('admin.adc-orders.show', $adcOrder->id) }}" class="text-decoration-none text-info">
                                            {{ $adcOrder->order_number }}
                                        </a>
                                        <div class="text-muted fs-10">{{ $adcOrder->created_at->format('d M, h:i A') }}</div>
                                    </td>
                                    <td>
                                        <div class="fw-medium text-slate-800">{{ $adcOrder->adc->adcProfile->company_name ?? $adcOrder->adc->name ?? '—' }}</div>
                                        <div class="text-muted fs-10">DDC: {{ $adcOrder->assignedDdc->profile->ddc_code ?? $adcOrder->selectedDdc->profile->ddc_code ?? '—' }}</div>
                                    </td>
                                    <td class="fw-bold text-slate-800">₹{{ number_format($adcOrder->total_amount, 2) }}</td>
                                    <td>
                                        @if(in_array($adcOrder->status, ['COMPLETED', 'DELIVERED']))
                                            <span class="badge bg-success bg-opacity-10 text-success px-1.5 py-0.5 fs-10">{{ $adcOrder->status }}</span>
                                        @elseif($adcOrder->status === 'CANCELLED')
                                            <span class="badge bg-danger bg-opacity-10 text-danger px-1.5 py-0.5 fs-10">CANCELLED</span>
                                        @else
                                            <span class="badge bg-info bg-opacity-10 text-info px-1.5 py-0.5 fs-10">{{ $adcOrder->status }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.adc-orders.show', $adcOrder->id) }}" class="btn btn-sm btn-light p-1" title="View Order">
                                            <iconify-icon icon="solar:eye-bold-duotone" class="fs-15"></iconify-icon>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-3 text-muted fs-12">No recent ADC orders found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Live Data Queues (Pending Products & Recent Vendors) --}}
    <div class="row g-2 mb-3">
        {{-- Pending Product Approvals Queue --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 10px;">
                <div class="card-header bg-white py-2 px-3 d-flex align-items-center justify-content-between border-bottom border-light">
                    <h6 class="card-title mb-0 fw-bold text-slate-800 fs-14">
                        <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="me-1 text-warning align-middle"></iconify-icon>
                        Pending Product Approval Queue
                    </h6>
                    <a href="{{ route('admin.products.approvals.new') }}" class="btn btn-sm btn-link text-warning p-0 fw-semibold fs-11">Review All &rarr;</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 fs-12">
                        <thead class="bg-light text-muted fs-11">
                            <tr>
                                <th>Product</th>
                                <th>Category</th>
                                <th>Vendor</th>
                                <th>MRP</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pendingProducts as $prod)
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            @if($prod->featured_image)
                                                @php
                                                    $prodImg = filter_var($prod->featured_image, FILTER_VALIDATE_URL)
                                                        ? $prod->featured_image
                                                        : asset('storage/' . $prod->featured_image);
                                                @endphp
                                                <img src="{{ $prodImg }}" alt="img" width="32" height="32" class="rounded border object-cover" onerror="this.style.display='none';">
                                            @else
                                                <div class="avatar-xs rounded bg-light d-flex align-items-center justify-content-center border">
                                                    <iconify-icon icon="solar:box-bold-duotone" class="text-muted"></iconify-icon>
                                                </div>
                                            @endif
                                            <div>
                                                <div class="fw-semibold text-slate-800 text-truncate" style="max-width: 130px;" title="{{ $prod->product_name }}">{{ $prod->product_name }}</div>
                                                <div class="text-muted fs-10">Code: {{ $prod->product_code }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-muted">{{ $prod->category->name ?? '—' }}</td>
                                    <td class="fw-medium text-slate-700">{{ $prod->seller->sellerProfile->company_name ?? $prod->seller->name ?? '—' }}</td>
                                    <td class="fw-bold text-slate-800">₹{{ number_format($prod->mrp, 2) }}</td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.products.approvals.show', $prod->id) }}" class="btn btn-sm btn-outline-warning py-0.5 px-1.5 fs-10 fw-semibold">
                                            Review
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-3 text-muted fs-12">No pending product approvals. All catalog up to date!</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Recent Seller Registrations --}}
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm h-100" style="border-radius: 10px;">
                <div class="card-header bg-white py-2 px-3 d-flex align-items-center justify-content-between border-bottom border-light">
                    <h6 class="card-title mb-0 fw-bold text-slate-800 fs-14">
                        <iconify-icon icon="solar:shop-window-bold-duotone" class="me-1 text-success align-middle"></iconify-icon>
                        Recent Seller Registrations
                    </h6>
                    <a href="{{ route('admin.external.vendors.index') }}" class="btn btn-sm btn-link text-success p-0 fw-semibold fs-11">Manage All &rarr;</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0 fs-12">
                        <thead class="bg-light text-muted fs-11">
                            <tr>
                                <th>Seller Details</th>
                                <th>Mobile</th>
                                <th>KYC Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentVendors as $v)
                                <tr>
                                    <td>
                                        <div class="fw-semibold text-slate-800">{{ $v->sellerProfile->company_name ?? $v->name }}</div>
                                        <div class="text-muted fs-10">Code: {{ $v->sellerProfile->seller_code ?? '—' }} | Owner: {{ $v->name }}</div>
                                    </td>
                                    <td class="text-slate-700">{{ $v->mobile }}</td>
                                    <td>
                                        @if(($v->sellerProfile->kyc_status ?? '') === 'approved')
                                            <span class="badge bg-success bg-opacity-10 text-success px-1.5 py-0.5 fs-10">Approved</span>
                                        @elseif(($v->sellerProfile->kyc_status ?? '') === 'pending')
                                            <span class="badge bg-warning bg-opacity-10 text-warning px-1.5 py-0.5 fs-10">Pending KYC</span>
                                        @else
                                            <span class="badge bg-light text-muted px-1.5 py-0.5 fs-10">{{ ucfirst($v->sellerProfile->kyc_status ?? 'Registered') }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <a href="{{ route('admin.external.vendors.profile', $v->id) }}" class="btn btn-sm btn-light p-1" title="View Profile">
                                            <iconify-icon icon="solar:user-id-bold-duotone" class="fs-15"></iconify-icon>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted fs-12">No recent seller registrations found.</td>
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof ApexCharts === 'undefined') {
        console.warn('ApexCharts library is not loaded');
        return;
    }
    // 1. Multi-Channel Revenue & Volume Performance Chart
    const monthLabels = @json($chartMonths);
    const poSeriesData = @json($poMonthlyData);
    const adcSeriesData = @json($adcMonthlyData);
    const posSeriesData = @json($posMonthlyData);

    const revenueChartOptions = {
        series: [{
            name: 'Vendor B2B PO Sales',
            data: poSeriesData
        }, {
            name: 'ADC Supply Orders',
            data: adcSeriesData
        }, {
            name: 'POS Retail Counter Sales',
            data: posSeriesData
        }],
        chart: {
            type: 'bar',
            height: 290,
            toolbar: { show: false },
            fontFamily: 'Inter, sans-serif'
        },
        plotOptions: {
            bar: {
                horizontal: false,
                columnWidth: '45%',
                borderRadius: 4
            },
        },
        colors: ['#3b82f6', '#06b6d4', '#8b5cf6'],
        dataLabels: { enabled: false },
        stroke: { show: true, width: 2, colors: ['transparent'] },
        xaxis: {
            categories: monthLabels,
            labels: { style: { colors: '#64748b', fontSize: '11px' } }
        },
        yaxis: {
            labels: {
                formatter: function (val) {
                    return '₹' + val.toLocaleString();
                },
                style: { colors: '#64748b', fontSize: '11px' }
            }
        },
        fill: { opacity: 1 },
        tooltip: {
            y: {
                formatter: function (val) {
                    return '₹' + val.toLocaleString('en-IN', { minimumFractionDigits: 2 });
                }
            }
        },
        legend: { position: 'top', horizontalAlign: 'right', fontSize: '11px' }
    };

    const revenueChart = new ApexCharts(document.querySelector("#multiChannelRevenueChart"), revenueChartOptions);
    revenueChart.render();

    // 2. Channel Revenue Contribution Donut Chart
    const poRevSum = {{ $stats['total_po_revenue'] }};
    const adcRevSum = {{ $stats['total_adc_order_revenue'] }};
    const posRevSum = {{ $stats['total_pos_revenue'] }};

    const channelChartOptions = {
        series: [poRevSum > 0 ? poRevSum : 1, adcRevSum > 0 ? adcRevSum : 1, posRevSum > 0 ? posRevSum : 1],
        labels: ['B2B PO Sales', 'ADC Supply', 'POS Counter Sales'],
        chart: {
            type: 'donut',
            height: 240,
            fontFamily: 'Inter, sans-serif'
        },
        colors: ['#3b82f6', '#06b6d4', '#8b5cf6'],
        legend: { position: 'bottom', fontSize: '11px' },
        dataLabels: { enabled: true, formatter: function (val) { return Math.round(val) + '%'; } },
        plotOptions: {
            pie: {
                donut: {
                    size: '65%',
                    labels: {
                        show: true,
                        total: {
                            show: true,
                            label: 'Total Revenue',
                            fontSize: '12px',
                            color: '#64748b',
                            formatter: function (w) {
                                return '₹' + (poRevSum + adcRevSum + posRevSum).toLocaleString('en-IN', { maximumFractionDigits: 0 });
                            }
                        }
                    }
                }
            }
        }
    };

    const channelChart = new ApexCharts(document.querySelector("#channelContributionChart"), channelChartOptions);
    channelChart.render();
});
</script>
@endpush