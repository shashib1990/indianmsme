@extends('layouts.adc')
@section('title', 'My Profile & KYC')
@section('page-title', 'My Profile & KYC Management')

@section('content')
@php
    $profile = $user->adcProfile;

    $profileImgUrl = asset('admin-assets/images/users/avatar-1.jpg');
    if ($profile && $profile->profile_image) {
        $profileImgUrl = filter_var($profile->profile_image, FILTER_VALIDATE_URL) 
            ? $profile->profile_image 
            : asset('storage/' . $profile->profile_image);
    }

    $adcCodeVal = $profile?->adc_code ?? '—';
    $canteenCodeVal = $profile?->code ?? '—';
    $abhhyamCodeVal = $profile?->abhhyam_code 
        ?: ($user->abhhyam_code 
        ?: ($profile?->advisor_code 
        ?: ($user->advisor_code 
        ?: '—')));
    $walletBal = (float) ($profile?->wallet_balance ?? 0.00);
    $addressVal = $profile?->address ?: ($user->address ?: 'No detailed store address provided.');

    $kycStatus = $profile?->kyc_status ?? 'PENDING';
    $kycBadge = match ($kycStatus) {
        'APPROVED' => [
            'class' => 'bg-success text-white border-success',
            'style' => 'background: #10b981 !important; color: #ffffff !important; border: 1px solid #059669 !important;',
            'label' => '✓ KYC Approved'
        ],
        'REJECTED' => [
            'class' => 'bg-danger text-white border-danger',
            'style' => 'background: #ef4444 !important; color: #ffffff !important; border: 1px solid #dc2626 !important;',
            'label' => '✕ KYC Rejected'
        ],
        'UNDER_REVIEW' => [
            'class' => 'bg-info text-white border-info',
            'style' => 'background: #0ea5e9 !important; color: #ffffff !important; border: 1px solid #0284c7 !important;',
            'label' => '⏳ KYC Under Review'
        ],
        default => [
            'class' => 'bg-warning text-dark border-warning',
            'style' => 'background: #f59e0b !important; color: #000000 !important; border: 1px solid #d97706 !important;',
            'label' => '⚠️ KYC Pending'
        ],
    };

    $activeTabSession = session('active_tab', 'overview');
@endphp

@push('styles')
<style>
    /* ── Royal Indigo Hero Banner ── */
    .profile-hero-card {
        background: linear-gradient(135deg, #071135 0%, #152466 50%, #060e29 100%);
        border-radius: 20px;
        position: relative;
        overflow: hidden;
        color: #ffffff;
        padding: 24px 28px;
        box-shadow: 0 16px 36px -12px rgba(7, 17, 53, 0.5), inset 0 1px 0 rgba(255, 255, 255, 0.15);
        border: 1px solid rgba(255, 255, 255, 0.08);
    }
    .profile-hero-card::before {
        content: '';
        position: absolute;
        top: -40%;
        right: -10%;
        width: 450px;
        height: 450px;
        background: radial-gradient(circle, rgba(245, 158, 11, 0.22) 0%, rgba(59, 130, 246, 0.1) 45%, transparent 70%);
        pointer-events: none;
    }
    .profile-dot-grid {
        position: absolute;
        inset: 0;
        background-image: radial-gradient(rgba(255, 255, 255, 0.12) 1.5px, transparent 1.5px);
        background-size: 18px 18px;
        opacity: 0.35;
        pointer-events: none;
    }

    /* ── Avatar Styling ── */
    .avatar-wrapper {
        position: relative;
        display: inline-block;
    }
    .avatar-img-main {
        width: 110px;
        height: 110px;
        object-fit: cover;
        border: 3.5px solid #f59e0b;
        box-shadow: 0 0 20px rgba(245, 158, 11, 0.35), 0 8px 24px rgba(0,0,0,0.4);
        border-radius: 50%;
    }
    .avatar-camera-btn {
        position: absolute;
        bottom: 2px;
        right: 2px;
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
        color: #ffffff;
        border: 2px solid #ffffff;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        box-shadow: 0 4px 10px rgba(0,0,0,0.3);
        transition: all 0.25s ease;
    }
    .avatar-camera-btn:hover {
        transform: scale(1.12);
        color: #ffffff;
    }

    .glass-pill {
        background: rgba(255, 255, 255, 0.18) !important;
        color: #ffffff !important;
        border: 1px solid rgba(255, 255, 255, 0.35) !important;
        backdrop-filter: blur(4px);
    }

    /* ── KPI Summary Cards ── */
    .kpi-mini-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 14px;
        padding: 16px 20px;
        box-shadow: 0 4px 14px -2px rgba(0, 0, 0, 0.03);
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: space-between;
        transition: all 0.2s ease;
    }
    .kpi-mini-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.06);
    }
    .kpi-label {
        font-size: 11px;
        font-weight: 700;
        color: #64748b;
        letter-spacing: 0.6px;
        text-transform: uppercase;
    }
    .kpi-value {
        font-family: 'Outfit', sans-serif;
        font-size: 18px;
        font-weight: 800;
        color: #0f172a;
        margin-top: 2px;
    }

    /* ── Solid High-Visibility Button ── */
    .btn-solid-white {
        background: #ffffff !important;
        color: #071135 !important;
        border: 1px solid #ffffff !important;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        transition: all 0.2s ease;
    }
    .btn-solid-white:hover {
        background: #f8fafc !important;
        color: #06186b !important;
        transform: translateY(-1px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.2);
    }

    /* ── Profile Summary Cards ── */
    .profile-section-card {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        box-shadow: 0 4px 18px -4px rgba(0, 0, 0, 0.04);
        overflow: hidden;
    }
    .profile-section-header {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        padding: 16px 20px;
        color: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
    }
    .info-tile {
        background: #f8fafc;
        border: 1px solid #f1f5f9;
        border-radius: 12px;
        padding: 14px 16px;
        height: 100%;
        transition: all 0.2s ease;
    }
    .info-tile:hover {
        background: #ffffff;
        border-color: #cbd5e1;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.04);
    }
    .info-tile-label {
        font-size: 11.5px;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .info-tile-value {
        font-size: 14px;
        font-weight: 700;
        color: #0f172a;
        margin-bottom: 0;
        word-break: break-word;
    }
</style>
@endpush

<!-- Flash Alerts -->
@if(session('success'))
    <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-3" role="alert">
        <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-20 align-middle me-2"></iconify-icon>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show mb-3" role="alert">
        <ul class="mb-0 ps-3">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<!-- 1. Hero Header Card -->
<div class="row mb-3 mt-0">
    <div class="col-12">
        <div class="profile-hero-card">
            <div class="profile-dot-grid"></div>
            <div class="d-flex flex-column flex-lg-row align-items-center align-items-lg-center gap-3.5 position-relative" style="z-index: 2;">
                
                <!-- Avatar Section -->
                <form action="{{ route('adc.profile.update') }}" method="POST" enctype="multipart/form-data" id="adcAvatarForm" class="flex-shrink-0">
                    @csrf
                    @method('PUT')
                    
                    <div class="avatar-wrapper">
                        <img src="{{ $profileImgUrl }}" alt="Avatar" class="avatar-img-main" id="adc-avatar-preview">
                        
                        <div id="adc-avatar-loader"
                            class="position-absolute top-0 start-0 w-100 h-100 rounded-circle bg-dark bg-opacity-70 d-none flex-column align-items-center justify-content-center text-white"
                            style="z-index:10;">
                            <span class="spinner-border spinner-border-sm mb-1 text-warning"></span>
                            <span class="fs-10 fw-bold">Updating...</span>
                        </div>

                        <label for="profile_image_input" class="avatar-camera-btn" title="Change Photo">
                            <iconify-icon icon="solar:camera-bold-duotone" width="18" height="18" style="font-size: 18px !important; width: 18px !important; height: 18px !important;"></iconify-icon>
                        </label>

                        <input type="file" name="profile_image" id="profile_image_input" class="d-none" accept="image/*" onchange="uploadAvatarImage(this)">
                    </div>
                </form>

                <!-- Profile Information Header -->
                <div class="flex-grow-1 w-100 text-center text-lg-start">
                    <div class="d-flex flex-column flex-xl-row align-items-center justify-content-between gap-3">
                        
                        <!-- User Name & Status Badges -->
                        <div class="d-flex flex-column align-items-center align-items-lg-start">
                            <div class="d-flex align-items-center justify-content-center justify-content-lg-start gap-2 mb-2 flex-wrap">
                                <h2 class="fw-extrabold text-white mb-0" style="font-family: 'Outfit', sans-serif; letter-spacing: -0.4px; font-size: 24px;">
                                    {{ $user->name }}
                                </h2>
                                @if($user->status === '1')
                                    <span class="badge px-3 py-1.5 rounded-pill font-monospace fw-bold fs-11" style="background: rgba(16, 185, 129, 0.22); color: #34d399; border: 1px solid rgba(52, 211, 153, 0.45);">
                                        ● Active Canteen
                                    </span>
                                @endif
                                <span class="badge px-3 py-1.5 rounded-pill font-monospace fw-bold fs-11" style="{{ $kycBadge['style'] }}">
                                    {{ $kycBadge['label'] }}
                                </span>
                            </div>
                            
                            <!-- Company Name -->
                            <div class="mt-1 mb-2">
                                <span class="text-white text-opacity-90 fs-13 d-inline-flex align-items-center justify-content-center justify-content-lg-start gap-2 px-3 py-1.5 rounded-pill" style="background: rgba(255, 255, 255, 0.12); border: 1px solid rgba(255, 255, 255, 0.2);">
                                    <iconify-icon icon="solar:shop-bold-duotone" width="18" height="18" style="font-size: 18px !important; width: 18px !important; height: 18px !important; color: #fbbf24 !important; flex-shrink: 0;"></iconify-icon>
                                    <span class="fw-bold" style="color: #ffffff;">{{ $profile->company_name ?? 'ASC Canteen Store Entity' }}</span>
                                </span>
                            </div>
                        </div>

                        <!-- Action Buttons -->
                        <div class="d-flex align-items-center gap-2.5 flex-wrap justify-content-center justify-content-lg-end flex-shrink-0">
                            <a href="{{ route('adc.wallet.index') }}" class="btn btn-warning btn-sm px-3.5 py-2 rounded-pill fw-bold font-monospace fs-12 d-inline-flex align-items-center gap-2 shadow-sm text-nowrap" style="background: #f59e0b; color: #000000; border: none; height: 38px;">
                                <iconify-icon icon="solar:wallet-bold-duotone" width="18" height="18" style="font-size: 18px; display: inline-flex; align-items: center; justify-content: center;"></iconify-icon>
                                <span style="line-height: 1;">₹{{ number_format($walletBal, 2) }}</span>
                            </a>

                            <a href="{{ route('adc.profile.change-password') }}" class="btn btn-solid-white btn-sm px-3.5 py-2 rounded-pill fw-extrabold font-monospace fs-12 d-inline-flex align-items-center gap-2 text-nowrap" style="background: #ffffff; color: #071135 !important; border: 1px solid #ffffff; height: 38px;">
                                <iconify-icon icon="solar:lock-password-bold-duotone" width="18" height="18" style="color: #071135 !important; font-size: 18px; display: inline-flex; align-items: center; justify-content: center;"></iconify-icon>
                                <span style="color: #071135 !important; line-height: 1;">Change Password</span>
                            </a>
                        </div>
                    </div>

                    <!-- Code Badges Row -->
                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-lg-start gap-2 mt-2">
                        <span class="badge glass-pill px-3 py-1.5 fs-12 rounded-pill font-monospace fw-semibold">
                            ADC Code: <strong>{{ $adcCodeVal }}</strong>
                        </span>
                        <span class="badge glass-pill px-3 py-1.5 fs-12 rounded-pill font-monospace fw-semibold">
                            Canteen Code: <strong>{{ $canteenCodeVal }}</strong>
                        </span>
                        <span class="badge glass-pill px-3 py-1.5 fs-12 rounded-pill font-monospace fw-semibold">
                            Category: <strong>{{ $profile->type ?? 'ASC Canteen' }}</strong>
                        </span>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2. KPI Quick Stats Bar -->
<div class="row g-3 mb-3">
    <div class="col-xl-3 col-md-6">
        <div class="kpi-mini-card" style="border-left: 4px solid #2563eb; background: #eff6ff;">
            <div>
                <div class="kpi-label text-primary-emphasis">ADC CODE</div>
                <div class="kpi-value text-primary font-monospace fs-18 fw-extrabold">{{ $adcCodeVal }}</div>
            </div>
            <div class="avatar-sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width: 42px; height: 42px;">
                <iconify-icon icon="solar:user-id-bold-duotone" width="22" height="22"></iconify-icon>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="kpi-mini-card" style="border-left: 4px solid #f59e0b;">
            <div>
                <div class="kpi-label text-amber-700">AVI CODE</div>
                <div class="kpi-value text-amber-800 font-monospace">{{ $abhhyamCodeVal }}</div>
            </div>
            <div class="avatar-sm rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background: #fffbe6; color: #d97706;">
                <iconify-icon icon="solar:shield-check-bold-duotone" width="22" height="22"></iconify-icon>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="kpi-mini-card" style="border-left: 4px solid #0284c7;">
            <div>
                <div class="kpi-label">CANTEEN CODE</div>
                <div class="kpi-value text-info font-monospace">{{ $canteenCodeVal }}</div>
            </div>
            <div class="avatar-sm bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <iconify-icon icon="solar:shop-bold-duotone" width="22" height="22"></iconify-icon>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6">
        <div class="kpi-mini-card" style="border-left: 4px solid #10b981;">
            <div>
                <div class="kpi-label">WALLET BALANCE</div>
                <div class="kpi-value text-success font-monospace">₹{{ number_format($walletBal, 2) }}</div>
            </div>
            <div class="avatar-sm bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                <iconify-icon icon="solar:wallet-money-bold-duotone" width="22" height="22"></iconify-icon>
            </div>
        </div>
    </div>
</div>

<!-- Rejection Remarks Alert (if KYC Rejected) -->
@if($kycStatus === 'REJECTED' && $profile?->kyc_remarks)
    <div class="alert alert-danger border-0 shadow-sm mb-3">
        <div class="d-flex align-items-center gap-2">
            <iconify-icon icon="solar:danger-triangle-bold" class="fs-22 text-danger flex-shrink-0"></iconify-icon>
            <div>
                <strong class="fs-14">KYC Rejection Reason:</strong>
                <p class="mb-0 fs-13 text-slate-800">{{ $profile->kyc_remarks }}</p>
                <small class="text-muted">Please click "Update KYC Documents" below to re-submit your documents for review.</small>
            </div>
        </div>
    </div>
@endif

<!-- 3. Profile Navigation Tabs -->
<div class="row g-3" id="profile-tabs-section">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 border-bottom d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-2">
                <ul class="nav nav-pills card-header-pills gap-1" id="profileTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTabSession === 'overview' ? 'active' : '' }} fw-semibold px-3.5"
                            id="overview-tab" data-bs-toggle="tab" data-bs-target="#overview-panel" type="button" role="tab">
                            <iconify-icon icon="solar:eye-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                            Profile Overview
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTabSession === 'basic' ? 'active' : '' }} fw-semibold px-3.5"
                            id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic-panel" type="button" role="tab">
                            <iconify-icon icon="solar:user-id-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                            Edit Basic Profile
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTabSession === 'kyc' ? 'active' : '' }} fw-semibold px-3.5"
                            id="kyc-tab" data-bs-toggle="tab" data-bs-target="#kyc-panel" type="button" role="tab">
                            <iconify-icon icon="solar:shield-check-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                            Update KYC Documents
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link {{ $activeTabSession === 'bank' ? 'active' : '' }} fw-semibold px-3.5"
                            id="bank-tab" data-bs-toggle="tab" data-bs-target="#bank-panel" type="button" role="tab">
                            <iconify-icon icon="solar:card-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                            Edit Bank Details
                        </button>
                    </li>
                </ul>

                <div class="text-muted small fs-12">
                    <iconify-icon icon="solar:info-circle-bold-duotone" class="align-middle me-1 text-primary"></iconify-icon>
                    Click any tab to view or update specific information.
                </div>
            </div>

            <div class="card-body p-4">
                <div class="tab-content">

                    <!-- TAB 1: READ-ONLY PROFILE OVERVIEW -->
                    <div class="tab-pane fade {{ $activeTabSession === 'overview' ? 'show active' : '' }}"
                        id="overview-panel" role="tabpanel">
                        
                        <div class="row g-4 mb-4">
                            <!-- Left Card: Canteen & Entity Specifications -->
                            <div class="col-lg-6">
                                <div class="profile-section-card h-100">
                                    <div class="profile-section-header">
                                        <div class="d-flex align-items-center gap-2">
                                            <iconify-icon icon="solar:shop-bold-duotone" width="22" height="22" style="color: #f59e0b !important; flex-shrink: 0;"></iconify-icon>
                                            <h6 class="fw-extrabold text-white mb-0" style="font-family: 'Outfit', sans-serif; font-size: 15px;">
                                                Canteen & Entity Specifications
                                            </h6>
                                        </div>
                                        <button type="button" class="btn btn-xs btn-warning text-dark fw-bold rounded-pill px-2.5 py-1" onclick="switchToEditTab('basic-tab')">
                                            <iconify-icon icon="solar:pen-bold" class="align-middle me-1"></iconify-icon> Edit Profile
                                        </button>
                                    </div>

                                    <div class="p-4">
                                        <div class="row g-3">
                                            <div class="col-sm-6">
                                                <div class="info-tile">
                                                    <div class="info-tile-label">
                                                        <iconify-icon icon="solar:user-bold-duotone" class="text-primary"></iconify-icon>
                                                        <span>Authorised Person</span>
                                                    </div>
                                                    <div class="info-tile-value text-slate-900 fs-15">{{ $user->name }}</div>
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <div class="info-tile">
                                                    <div class="info-tile-label">
                                                        <iconify-icon icon="solar:buildings-bold-duotone" class="text-info"></iconify-icon>
                                                        <span>Canteen Entity Name</span>
                                                    </div>
                                                    <div class="info-tile-value text-slate-900 fs-15">{{ $profile?->company_name ?? '—' }}</div>
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <div class="info-tile">
                                                    <div class="info-tile-label">
                                                        <iconify-icon icon="solar:shop-window-bold-duotone" class="text-warning"></iconify-icon>
                                                        <span>Trade Name</span>
                                                    </div>
                                                    <div class="info-tile-value text-slate-900 fs-14">{{ $profile?->trade_name ?? '—' }}</div>
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <div class="info-tile">
                                                    <div class="info-tile-label">
                                                        <iconify-icon icon="solar:tag-bold-duotone" class="text-success"></iconify-icon>
                                                        <span>Canteen Category / Type</span>
                                                    </div>
                                                    <div class="info-tile-value">
                                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill fs-12 font-monospace fw-bold">
                                                            {{ $profile?->type ?? 'ASC Canteen' }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-sm-4">
                                                <div class="info-tile">
                                                    <div class="info-tile-label">
                                                        <iconify-icon icon="solar:document-text-bold-duotone" class="text-success"></iconify-icon>
                                                        <span>GST Number</span>
                                                    </div>
                                                    <div class="info-tile-value font-monospace text-slate-800 fs-13">
                                                        {{ $profile?->gst_number ?? '—' }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-sm-4">
                                                <div class="info-tile">
                                                    <div class="info-tile-label">
                                                        <iconify-icon icon="solar:card-bold-duotone" class="text-primary"></iconify-icon>
                                                        <span>PAN Number</span>
                                                    </div>
                                                    <div class="info-tile-value font-monospace text-slate-800 fs-13">
                                                        {{ $profile?->pan_no ?? '—' }}
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-sm-4">
                                                <div class="info-tile">
                                                    <div class="info-tile-label">
                                                        <iconify-icon icon="solar:user-id-bold-duotone" class="text-info"></iconify-icon>
                                                        <span>Aadhaar Number</span>
                                                    </div>
                                                    <div class="info-tile-value font-monospace text-slate-800 fs-13">
                                                        {{ $profile?->aadhaar_no ?? '—' }}
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Right Card: Contact & Location Logistics -->
                            <div class="col-lg-6">
                                <div class="profile-section-card h-100">
                                    <div class="profile-section-header" style="background: linear-gradient(135deg, #06186b 0%, #112a96 100%);">
                                        <div class="d-flex align-items-center gap-2">
                                            <iconify-icon icon="solar:map-point-bold-duotone" width="22" height="22" style="color: #38bdf8 !important; flex-shrink: 0;"></iconify-icon>
                                            <h6 class="fw-extrabold text-white mb-0" style="font-family: 'Outfit', sans-serif; font-size: 15px;">
                                                Contact & Location Logistics
                                            </h6>
                                        </div>
                                        <button type="button" class="btn btn-xs btn-warning text-dark fw-bold rounded-pill px-2.5 py-1" onclick="switchToEditTab('basic-tab')">
                                            <iconify-icon icon="solar:pen-bold" class="align-middle me-1"></iconify-icon> Edit Location
                                        </button>
                                    </div>

                                    <div class="p-4">
                                        <div class="row g-3">
                                            
                                            <div class="col-sm-6">
                                                <div class="info-tile">
                                                    <div class="info-tile-label">
                                                        <iconify-icon icon="solar:letter-bold-duotone" class="text-primary"></iconify-icon>
                                                        <span>Email Address</span>
                                                    </div>
                                                    <div class="info-tile-value text-primary font-monospace text-break fs-13">{{ $user->email }}</div>
                                                </div>
                                            </div>

                                            <div class="col-sm-6">
                                                <div class="info-tile">
                                                    <div class="info-tile-label">
                                                        <iconify-icon icon="solar:phone-calling-bold-duotone" class="text-success"></iconify-icon>
                                                        <span>Mobile Number</span>
                                                    </div>
                                                    <div class="info-tile-value font-monospace text-slate-900 fs-14">{{ $user->mobile ?? $user->phone ?? '—' }}</div>
                                                </div>
                                            </div>

                                            <div class="col-sm-4">
                                                <div class="info-tile">
                                                    <div class="info-tile-label">
                                                        <iconify-icon icon="solar:map-bold-duotone" class="text-secondary"></iconify-icon>
                                                        <span>State</span>
                                                    </div>
                                                    <div class="info-tile-value text-slate-900 fs-13">{{ $profile?->state ?? '—' }}</div>
                                                </div>
                                            </div>

                                            <div class="col-sm-4">
                                                <div class="info-tile">
                                                    <div class="info-tile-label">
                                                        <iconify-icon icon="solar:city-bold-duotone" class="text-secondary"></iconify-icon>
                                                        <span>District</span>
                                                    </div>
                                                    <div class="info-tile-value text-slate-900 fs-13">{{ $profile?->district ?? '—' }}</div>
                                                </div>
                                            </div>

                                            <div class="col-sm-4">
                                                <div class="info-tile">
                                                    <div class="info-tile-label">
                                                        <iconify-icon icon="solar:mailbox-bold-duotone" class="text-secondary"></iconify-icon>
                                                        <span>Pincode</span>
                                                    </div>
                                                    <div class="info-tile-value font-monospace text-slate-900 fs-13">{{ $profile?->pincode ?? '—' }}</div>
                                                </div>
                                            </div>

                                            <div class="col-12">
                                                <div class="info-tile">
                                                    <div class="info-tile-label">
                                                        <iconify-icon icon="solar:home-bold-duotone" class="text-primary"></iconify-icon>
                                                        <span>Complete Store Delivery Address</span>
                                                    </div>
                                                    <div class="info-tile-value text-slate-900 fs-13 fw-semibold" style="line-height: 1.5;">
                                                        {{ $addressVal }}
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bank & KYC Summary Grid -->
                        <div class="row g-4 mb-4">
                            <!-- Bank Details Summary -->
                            <div class="col-lg-6">
                                <div class="profile-section-card h-100">
                                    <div class="profile-section-header" style="background: linear-gradient(135deg, #0f766e 0%, #115e59 100%);">
                                        <div class="d-flex align-items-center gap-2">
                                            <iconify-icon icon="solar:card-bold-duotone" width="22" height="22" style="color: #5eead4 !important; flex-shrink: 0;"></iconify-icon>
                                            <h6 class="fw-extrabold text-white mb-0" style="font-family: 'Outfit', sans-serif; font-size: 15px;">
                                                Bank Account & Settlement Summary
                                            </h6>
                                        </div>
                                        <button type="button" class="btn btn-xs btn-warning text-dark fw-bold rounded-pill px-2.5 py-1" onclick="switchToEditTab('bank-tab')">
                                            <iconify-icon icon="solar:pen-bold" class="align-middle me-1"></iconify-icon> Edit Bank
                                        </button>
                                    </div>
                                    <div class="p-4">
                                        <div class="row g-3">
                                            <div class="col-sm-6">
                                                <div class="info-tile">
                                                    <div class="info-tile-label"><span>Account Holder</span></div>
                                                    <div class="info-tile-value text-slate-900 fs-14">{{ $profile?->account_holder_name ?? 'Not Provided' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="info-tile">
                                                    <div class="info-tile-label"><span>Bank Name</span></div>
                                                    <div class="info-tile-value text-slate-900 fs-14">{{ $profile?->bank_name ?? 'Not Provided' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="info-tile">
                                                    <div class="info-tile-label"><span>Account Number</span></div>
                                                    <div class="info-tile-value font-monospace text-slate-900 fs-14">{{ $profile?->bank_account_number ?? 'Not Provided' }}</div>
                                                </div>
                                            </div>
                                            <div class="col-sm-6">
                                                <div class="info-tile">
                                                    <div class="info-tile-label"><span>IFSC Code</span></div>
                                                    <div class="info-tile-value font-monospace text-primary fs-14">{{ $profile?->ifsc_code ?? 'Not Provided' }}</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- KYC Documents Summary Grid -->
                            <div class="col-lg-6">
                                <div class="profile-section-card h-100">
                                    <div class="profile-section-header" style="background: linear-gradient(135deg, #3730a3 0%, #312e81 100%);">
                                        <div class="d-flex align-items-center gap-2">
                                            <iconify-icon icon="solar:shield-check-bold-duotone" width="22" height="22" style="color: #a5b4fc !important; flex-shrink: 0;"></iconify-icon>
                                            <h6 class="fw-extrabold text-white mb-0" style="font-family: 'Outfit', sans-serif; font-size: 15px;">
                                                KYC Documents Status
                                            </h6>
                                        </div>
                                        <button type="button" class="btn btn-xs btn-warning text-dark fw-bold rounded-pill px-2.5 py-1" onclick="switchToEditTab('kyc-tab')">
                                            <iconify-icon icon="solar:upload-square-bold" class="align-middle me-1"></iconify-icon> Update KYC
                                        </button>
                                    </div>
                                    <div class="p-3 fs-13">
                                        <div class="row g-2">
                                            @php
                                                $overviewDocs = [
                                                    ['label' => 'Aadhaar Card', 'file' => $profile?->aadhaar_card],
                                                    ['label' => 'PAN Card', 'file' => $profile?->pan_card],
                                                    ['label' => 'GST Certificate', 'file' => $profile?->gst_certificate],
                                                    ['label' => 'Bank Details / Cancelled Cheque', 'file' => $profile?->cancelled_cheque],
                                                ];
                                            @endphp
                                            @foreach($overviewDocs as $doc)
                                                <div class="col-sm-6">
                                                    <div class="p-2 border rounded bg-light d-flex align-items-center justify-content-between">
                                                        <span class="fs-12 text-slate-800 fw-semibold">{{ $doc['label'] }}</span>
                                                        @if($doc['file'])
                                                            <a href="{{ filter_var($doc['file'], FILTER_VALIDATE_URL) ? $doc['file'] : asset('storage/' . $doc['file']) }}"
                                                               target="_blank" class="badge bg-success text-white py-1 px-2 text-decoration-none">
                                                                <iconify-icon icon="solar:check-circle-bold" class="align-middle me-0.5"></iconify-icon> Uploaded
                                                            </a>
                                                        @else
                                                            <span class="badge bg-secondary text-white py-1 px-2">Missing</span>
                                                        @endif
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- TAB 2: EDIT BASIC PROFILE (SEPARATE FORM) -->
                    <div class="tab-pane fade {{ $activeTabSession === 'basic' ? 'show active' : '' }}"
                        id="basic-panel" role="tabpanel">
                        <form action="{{ route('adc.profile.update') }}" method="POST" class="profile-form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="active_tab" value="basic">

                            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                <h5 class="fw-bold text-slate-800 mb-0">
                                    <iconify-icon icon="solar:pen-new-square-bold-duotone" class="text-primary align-middle me-1.5"></iconify-icon>
                                    Edit Basic Profile Details
                                </h5>
                                <span class="text-muted fs-12">* Required fields</span>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-slate-700 fw-semibold">ADC Code</label>
                                    <input type="text" class="form-control bg-light fw-bold text-primary"
                                        value="{{ $adcCodeVal }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-slate-700 fw-semibold">Canteen Code</label>
                                    <input type="text" class="form-control bg-light fw-bold text-slate-700"
                                        value="{{ $canteenCodeVal }}" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-slate-700 fw-semibold">Authorised Person / Coordinator Name <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control"
                                        value="{{ old('name', $user->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-slate-700 fw-semibold">Company / Entity Name <span class="text-danger">*</span></label>
                                    <input type="text" name="company_name" class="form-control"
                                        value="{{ old('company_name', $profile?->company_name ?? '') }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-slate-700 fw-semibold">Trade Name</label>
                                    <input type="text" name="trade_name" class="form-control"
                                        value="{{ old('trade_name', $profile?->trade_name ?? '') }}" placeholder="Trade / Shop Name">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-slate-700 fw-semibold">Contact Mobile Number <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control"
                                        value="{{ old('phone', $profile?->official_phone ?? ($user->mobile ?? $user->phone)) }}" required
                                        maxlength="10" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-slate-700 fw-semibold">Email Address <span class="text-danger">*</span></label>
                                    <input type="email" name="email" class="form-control"
                                        value="{{ old('email', $profile?->official_email ?? $user->email) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-slate-700 fw-semibold">GST Number</label>
                                    <input type="text" name="gst_number" class="form-control text-uppercase"
                                        value="{{ old('gst_number', $profile?->gst_number ?? '') }}" maxlength="15"
                                        oninput="this.value = this.value.toUpperCase().slice(0, 15)" placeholder="15-digit GST Number">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-slate-700 fw-semibold">PAN Number</label>
                                    <input type="text" name="pan_no" class="form-control text-uppercase"
                                        value="{{ old('pan_no', $profile?->pan_no ?? '') }}" maxlength="10"
                                        oninput="this.value = this.value.toUpperCase().slice(0, 10)" placeholder="10-digit PAN">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-slate-700 fw-semibold">Aadhaar Number</label>
                                    <input type="text" name="aadhaar_no" class="form-control font-monospace"
                                        value="{{ old('aadhaar_no', $profile?->aadhaar_no ?? '') }}" maxlength="12"
                                        oninput="this.value = this.value.replace(/[^0-9]/g,'').slice(0, 12)" placeholder="12-digit Aadhaar">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label text-slate-700 fw-semibold">State <span class="text-danger">*</span></label>
                                    <select name="state" id="state" class="form-select" required>
                                        <option value="">Select State</option>
                                        @foreach($states as $st)
                                            <option value="{{ $st->id }}" {{ old('state', $currentStateId) == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-slate-700 fw-semibold">District <span class="text-danger">*</span></label>
                                    <select name="district" id="district" class="form-select" required>
                                        <option value="">Select District</option>
                                    </select>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label text-slate-700 fw-semibold">Store Pincode <span class="text-danger">*</span></label>
                                    <input type="text" name="pincode" class="form-control font-monospace"
                                        value="{{ old('pincode', $profile?->pincode ?? ($user->pincode ?? '')) }}" required
                                        placeholder="6-digit Pincode" maxlength="6"
                                        oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                </div>
                                <div class="col-md-12">
                                    <label class="form-label text-slate-700 fw-semibold">Detailed Store Address <span class="text-danger">*</span></label>
                                    <textarea name="address" class="form-control" rows="3" required>{{ old('address', $profile?->address ?? ($user->address ?? '')) }}</textarea>
                                </div>
                            </div>
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                                    <iconify-icon icon="solar:diskette-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                                    Save Basic Details
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB 3: UPDATE KYC DOCUMENTS (SEPARATE FORM) -->
                    <div class="tab-pane fade {{ $activeTabSession === 'kyc' ? 'show active' : '' }}"
                        id="kyc-panel" role="tabpanel">
                        <form action="{{ route('adc.profile.update') }}" method="POST" enctype="multipart/form-data" class="profile-form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="active_tab" value="kyc">

                            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                <div>
                                    <h5 class="fw-bold text-slate-800 mb-1">
                                        <iconify-icon icon="solar:shield-check-bold-duotone" class="text-primary align-middle me-1.5"></iconify-icon>
                                        Update KYC & Legal Documents
                                    </h5>
                                    <p class="text-muted fs-13 mb-0">Uploading new documents will submit your KYC profile to Admin for verification.</p>
                                </div>
                                <span class="badge {{ $kycBadge['class'] }} px-3 py-1.5 fs-12 rounded-pill border fw-bold" style="{{ $kycBadge['style'] }}">
                                    {{ $kycBadge['label'] }}
                                </span>
                            </div>

                            <div class="row g-3">
                                @php
                                    $kycDocs = [
                                        ['name' => 'aadhaar_card', 'label' => 'Aadhaar Card Copy', 'file' => $profile?->aadhaar_card],
                                        ['name' => 'pan_card', 'label' => 'PAN Card Copy', 'file' => $profile?->pan_card],
                                        ['name' => 'gst_certificate', 'label' => 'GST Certificate', 'file' => $profile?->gst_certificate],
                                        ['name' => 'cancelled_cheque', 'label' => 'Bank Details / Cancelled Cheque', 'file' => $profile?->cancelled_cheque],
                                    ];
                                @endphp

                                @foreach($kycDocs as $doc)
                                    <div class="col-md-6">
                                        <div class="card bg-light border-0 p-3 h-100">
                                            <label class="form-label text-slate-800 fw-semibold fs-13 mb-1">{{ $doc['label'] }}</label>
                                            @if($doc['file'])
                                                <div class="d-flex align-items-center gap-2 mb-2 p-2 rounded bg-white border">
                                                    <iconify-icon icon="solar:document-text-bold-duotone" class="text-primary fs-20"></iconify-icon>
                                                    <span class="fs-12 text-slate-700 text-truncate flex-grow-1">Document Uploaded</span>
                                                    <a href="{{ filter_var($doc['file'], FILTER_VALIDATE_URL) ? $doc['file'] : asset('storage/' . $doc['file']) }}"
                                                       target="_blank" class="btn btn-xs btn-outline-primary py-0.5 px-2">
                                                        <iconify-icon icon="solar:eye-bold" class="align-middle me-0.5"></iconify-icon> View
                                                    </a>
                                                </div>
                                            @else
                                                <span class="text-muted fs-11 d-block mb-2">No file uploaded yet.</span>
                                            @endif
                                            <input type="file" name="{{ $doc['name'] }}" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.webp">
                                            <small class="text-muted fs-11 mt-1">PDF, JPG, PNG or WEBP (Max 5MB)</small>
                                        </div>
                                    </div>
                                @endforeach
                            </div>

                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                                    <iconify-icon icon="solar:upload-square-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                                    Submit KYC Documents
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- TAB 4: EDIT BANK DETAILS (SEPARATE FORM) -->
                    <div class="tab-pane fade {{ $activeTabSession === 'bank' ? 'show active' : '' }}"
                        id="bank-panel" role="tabpanel">
                        <form action="{{ route('adc.profile.update') }}" method="POST" enctype="multipart/form-data" class="profile-form">
                            @csrf
                            @method('PUT')
                            <input type="hidden" name="active_tab" value="bank">

                            <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2">
                                <h5 class="fw-bold text-slate-800 mb-0">
                                    <iconify-icon icon="solar:card-bold-duotone" class="text-primary align-middle me-1.5"></iconify-icon>
                                    Edit Bank Account & Settlement Details
                                </h5>
                                <span class="text-muted fs-12">* Required fields</span>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label text-slate-700 fw-semibold">Account Holder Name <span class="text-danger">*</span></label>
                                    <input type="text" name="account_holder_name" class="form-control"
                                        value="{{ old('account_holder_name', $profile?->account_holder_name ?? $user->name) }}" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-slate-700 fw-semibold">Bank Name <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_name" class="form-control"
                                        value="{{ old('bank_name', $profile?->bank_name ?? '') }}" placeholder="e.g. HDFC Bank" required>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-slate-700 fw-semibold">Bank Account Number <span class="text-danger">*</span></label>
                                    <input type="text" name="bank_account_number" class="form-control font-monospace"
                                        value="{{ old('bank_account_number', $profile?->bank_account_number ?? '') }}" required placeholder="Account Number (Digits Only)"
                                        maxlength="20" oninput="this.value=this.value.replace(/[^0-9]/g,'')">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-slate-700 fw-semibold">IFSC Code <span class="text-danger">*</span></label>
                                    <input type="text" name="ifsc_code" class="form-control font-monospace text-uppercase"
                                        value="{{ old('ifsc_code', $profile?->ifsc_code ?? '') }}" required maxlength="11"
                                        oninput="this.value = this.value.toUpperCase()" placeholder="e.g. HDFC0001234">
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label text-slate-700 fw-semibold">Branch Name</label>
                                    <input type="text" name="branch_name" class="form-control"
                                        value="{{ old('branch_name', $profile?->branch_name ?? '') }}" placeholder="Branch Name">
                                </div>
                            </div>
                            <div class="text-end mt-4">
                                <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                                    <iconify-icon icon="solar:diskette-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                                    Save Bank Details
                                </button>
                            </div>
                        </form>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Associated DDC Details (If Available) -->
@if(isset($matchedDdc) && $matchedDdc)
    <div class="row mt-4 mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm border-start border-4 border-success">
                <div class="card-header bg-success text-white py-2.5 px-3">
                    <div class="d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:box-minimalistic-bold-duotone" width="20" height="20" class="text-white fs-20"></iconify-icon>
                        <h6 class="fw-extrabold text-white mb-0" style="font-family: 'Outfit', sans-serif; font-size: 14px;">
                            Associated DDC Hub (Primary Logistics Depot)
                        </h6>
                    </div>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="p-3 rounded-circle bg-success-subtle text-success flex-shrink-0">
                            <iconify-icon icon="solar:user-bold-duotone" width="26" height="26" class="fs-26"></iconify-icon>
                        </div>
                        <div>
                            <h6 class="fw-bold text-dark mb-1 fs-15">{{ $matchedDdc->name }}</h6>
                            <div class="d-flex align-items-center gap-2">
                                <span class="text-muted fs-13">
                                    DDC Code: <span class="badge bg-success text-white font-monospace px-2.5 py-1">{{ $matchedDdcProfile?->ddc_code ?? 'DDC' }}</span>
                                </span>
                                @if($matchedDdcProfile?->district)
                                    <span class="text-muted fs-13">• {{ $matchedDdcProfile->district }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endif

@endsection

@push('scripts')
<script>
function uploadAvatarImage(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = e => {
            document.getElementById('adc-avatar-preview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);

        const loader = document.getElementById('adc-avatar-loader');
        if (loader) {
            loader.classList.remove('d-none');
            loader.classList.add('d-flex');
        }

        document.getElementById('adcAvatarForm').submit();
    }
}

function switchToEditTab(tabBtnId) {
    const tabBtn = document.getElementById(tabBtnId);
    if (tabBtn) {
        const tab = new bootstrap.Tab(tabBtn);
        tab.show();
        const section = document.getElementById('profile-tabs-section');
        if (section) {
            section.scrollIntoView({ behavior: 'smooth' });
        }
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const stateSelect = document.getElementById('state');
    const districtSelect = document.getElementById('district');
    const currentDistrict = "{{ old('district', $profile?->district ?? '') }}";

    if (!stateSelect || !districtSelect) return;

    function loadDistricts(stateId, selectedDistrict) {
        if (!stateId) {
            districtSelect.innerHTML = '<option value="">Select District</option>';
            return;
        }

        districtSelect.innerHTML = '<option value="">Loading districts...</option>';

        fetch("{{ route('adc.api.locations.districts') }}?state_id=" + encodeURIComponent(stateId))
            .then(function (res) { return res.json(); })
            .then(function (data) {
                districtSelect.innerHTML = '<option value="">Select District</option>';
                data.forEach(function (dist) {
                    const opt = document.createElement('option');
                    opt.value = dist.name;
                    opt.textContent = dist.name;
                    if (selectedDistrict && (dist.name === selectedDistrict || dist.id == selectedDistrict)) {
                        opt.selected = true;
                    }
                    districtSelect.appendChild(opt);
                });

                if (selectedDistrict) {
                    districtSelect.value = selectedDistrict;
                }
            })
            .catch(function (err) {
                console.error('Error fetching districts:', err);
                districtSelect.innerHTML = '<option value="">Select District</option>';
            });
    }

    stateSelect.addEventListener('change', function () {
        loadDistricts(this.value, '');
    });

    const initialStateId = stateSelect.value;
    if (initialStateId) {
        loadDistricts(initialStateId, currentDistrict);
    }
});
</script>
@endpush
