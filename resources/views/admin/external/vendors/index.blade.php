@extends('layouts.admin')

@section('title', 'MSME Sellers')
@section('page-title', 'MSME Sellers')

@push('styles')
<style>
    /* Metric Cards System matching Dashboard Aesthetics */
    .imsme-metric-card {
        background: #ffffff;
        border: 1px solid #e2e8f0 !important;
        border-radius: 12px !important;
        padding: 14px 16px;
        display: flex;
        align-items: center;
        text-decoration: none !important;
        color: #1e293b !important;
        transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
        min-height: 86px;
        cursor: pointer;
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
        box-shadow: 0 12px 24px -6px rgba(15, 23, 42, 0.12) !important;
        border-color: #cbd5e1 !important;
    }

    .imsme-metric-card:hover::before {
        width: 6px;
    }

    .imsme-metric-card .icon-wrapper {
        width: 48px;
        height: 48px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
        margin-right: 14px;
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
        font-size: 0.78rem;
        font-weight: 700;
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
        font-size: 1.45rem;
        font-weight: 800;
        color: #0f172a;
        line-height: 1.1;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .imsme-metric-card .card-data .sub-value {
        font-size: 0.72rem;
        font-weight: 500;
        color: #64748b;
        margin-top: 3px;
    }

    .imsme-metric-card .arrow-hint {
        font-size: 1rem;
        color: #cbd5e1;
        transition: all 0.2s ease;
        margin-left: 6px;
        opacity: 0.6;
    }

    .imsme-metric-card:hover .arrow-hint {
        color: currentColor;
        transform: translateX(3px);
        opacity: 1;
    }

    /* Category Themes */
    .theme-indigo::before { background: #6366f1; }
    .theme-indigo .icon-wrapper { background: rgba(99, 102, 241, 0.12); color: #6366f1; }
    .theme-indigo .arrow-hint { color: #6366f1; }

    .theme-emerald::before { background: #10b981; }
    .theme-emerald .icon-wrapper { background: rgba(16, 185, 129, 0.12); color: #059669; }
    .theme-emerald .arrow-hint { color: #059669; }

    .theme-rose::before { background: #f43f5e; }
    .theme-rose .icon-wrapper { background: rgba(244, 63, 94, 0.12); color: #e11d48; }
    .theme-rose .arrow-hint { color: #e11d48; }

    /* Active Selected Card Rings */
    .imsme-metric-card.active-filter {
        border-color: #6366f1 !important;
        box-shadow: 0 0 0 2px rgba(99, 102, 241, 0.35), 0 8px 16px -4px rgba(99, 102, 241, 0.2) !important;
        background: linear-gradient(to bottom, #ffffff, #fafbff);
    }
    .imsme-metric-card.theme-emerald.active-filter {
        border-color: #10b981 !important;
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.35), 0 8px 16px -4px rgba(16, 185, 129, 0.2) !important;
        background: linear-gradient(to bottom, #ffffff, #f0fdf4);
    }
    .imsme-metric-card.theme-rose.active-filter {
        border-color: #f43f5e !important;
        box-shadow: 0 0 0 2px rgba(244, 63, 94, 0.35), 0 8px 16px -4px rgba(244, 63, 94, 0.2) !important;
        background: linear-gradient(to bottom, #ffffff, #fff1f2);
    }

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
</style>
@endpush

@section('content')
    @if($parentSeller)
        <div class="alert alert-info border-0 shadow-sm mb-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="solar:info-circle-bold-duotone" class="fs-20 text-info"></iconify-icon>
                <span>Viewing Sub-Sellers of: <strong class="text-slate-800">{{ $parentSeller->company_name ?: $parentSeller->name }}</strong> (Seller Code: <code>{{ $parentSeller->seller_id }}</code>)</span>
            </div>
            <a href="{{ route('admin.external.vendors.index') }}" class="btn btn-sm btn-info text-white fw-medium d-flex align-items-center gap-1">
                <iconify-icon icon="solar:arrow-left-bold-duotone" class="fs-14"></iconify-icon> Back to Main Sellers
            </a>
        </div>
    @endif

    <!-- Common Page Action Header -->
    <x-admin.page-header 
        title="MSME Sellers" 
        subtitle="Manage registered MSME seller partners, track verification compliance, catalog readiness, and pickup store coverage."
        :actions="[
            [
                'label' => 'DDC Users',
                'icon' => 'solar:user-id-bold-duotone',
                'url' => route('admin.external.ddc.index'),
                'permission' => 'users.view',
                'btnClass' => 'btn-outline-primary'
            ],
            [
                'label' => 'ADC Users',
                'icon' => 'solar:user-speak-rounded-bold-duotone',
                'url' => route('admin.external.adc.index'),
                'permission' => 'users.view',
                'btnClass' => 'btn-outline-warning'
            ],
            [
                'label' => 'Export CSV',
                'icon' => 'solar:document-text-bold-duotone',
                'onclick' => 'triggerExport(\'csv\')',
                'permission' => 'users.view',
                'btnClass' => 'btn-outline-secondary'
            ]
        ]"
    />

    <!-- Interactive Seller Readiness Metrics Cards -->
    <div class="row g-3 mb-3">
        <!-- 1. All MSME Sellers (Sab Sellers) -->
        <div class="col-lg-4 col-md-4 col-sm-12">
            <a href="{{ route('admin.external.vendors.index', array_filter(array_merge(request()->except(['filter_status', 'page']), []))) }}" 
               class="imsme-metric-card theme-indigo {{ empty($filterStatus) ? 'active-filter' : '' }}">
                <div class="icon-wrapper">
                    <iconify-icon icon="solar:users-group-two-rounded-bold-duotone"></iconify-icon>
                </div>
                <div class="card-data">
                    <div class="label">
                        <span>All MSME Sellers</span>
                        @if(empty($filterStatus))
                            <span class="badge bg-indigo-subtle text-primary border border-primary-subtle py-0.5 px-1.5 fs-10 fw-bold">Active Filter</span>
                        @else
                            <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon>
                        @endif
                    </div>
                    <div class="value">{{ number_format($totalSellersCount) }}</div>
                    <div class="sub-value text-muted">All registered sellers list (click to view)</div>
                </div>
            </a>
        </div>

        <!-- 2. Active Sellers (Account Active + KYC Verified + Min 1 Product + Pickup Store) -->
        <div class="col-lg-4 col-md-4 col-sm-12">
            <a href="{{ route('admin.external.vendors.index', array_filter(array_merge(request()->except(['filter_status', 'page']), ['filter_status' => 'active']))) }}" 
               class="imsme-metric-card theme-emerald {{ $filterStatus === 'active' ? 'active-filter' : '' }}">
                <div class="icon-wrapper">
                    <iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon>
                </div>
                <div class="card-data">
                    <div class="label">
                        <span>Active Sellers</span>
                        @if($filterStatus === 'active')
                            <span class="badge bg-success text-white py-0.5 px-1.5 fs-10 fw-bold">Active Filter</span>
                        @else
                            <iconify-icon icon="solar:alt-arrow-right-linear" class="arrow-hint"></iconify-icon>
                        @endif
                    </div>
                    <div class="value text-success">{{ number_format($activeSellersCount) }}</div>
                    <div class="sub-value text-muted">Eligible sellers list</div>
                </div>
            </a>
        </div>

        <!-- 3. Inactive Sellers (Missing 1 or more requirements) -->
        <div class="col-lg-4 col-md-4 col-sm-12">
            <a href="{{ route('admin.external.vendors.index', array_filter(array_merge(request()->except(['filter_status', 'page']), ['filter_status' => 'inactive']))) }}" 
               class="imsme-metric-card theme-rose {{ $filterStatus === 'inactive' ? 'active-filter' : '' }}">
                <div class="icon-wrapper">
                    <iconify-icon icon="solar:danger-triangle-bold-duotone"></iconify-icon>
                </div>
                <div class="card-data">
                    <div class="label">
                        <span>Inactive Sellers</span>
                        @if($filterStatus === 'inactive')
                            <span class="badge bg-danger text-white py-0.5 px-1.5 fs-10 fw-bold">Active Filter</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger py-0.5 px-1.5 fs-10 fw-bold">Action Needed</span>
                        @endif
                    </div>
                    <div class="value text-danger">{{ number_format($inactiveSellersCount) }}</div>
                    <div class="sub-value text-muted">Missing account, KYC, product, or pickup store</div>
                </div>
            </a>
        </div>
    </div>

    <!-- Active Filter Notice Banner -->
    @if($filterStatus === 'active')
        <div class="alert alert-success border-0 shadow-sm mb-3 d-flex align-items-center justify-content-between py-2 px-3" style="border-radius: 8px;">
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-18 text-success"></iconify-icon>
                <span class="fs-13">
                    Showing <strong>Active Sellers ({{ number_format($activeSellersCount) }})</strong>
                </span>
            </div>
            <a href="{{ route('admin.external.vendors.index', array_filter(request()->except(['filter_status', 'page']))) }}" class="btn btn-sm btn-light fw-medium py-0.5 px-2 fs-11">
                View All Sellers &times;
            </a>
        </div>
    @elseif($filterStatus === 'inactive')
        <div class="alert alert-danger border-0 shadow-sm mb-3 d-flex align-items-center justify-content-between py-2 px-3" style="border-radius: 8px;">
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="solar:danger-triangle-bold-duotone" class="fs-18 text-danger"></iconify-icon>
                <span class="fs-13">
                    Showing <strong>Inactive Sellers ({{ number_format($inactiveSellersCount) }})</strong>: Sellers missing one or more readiness criteria. Inspect the <strong>Readiness & Missing Items</strong> column to see what needs action.
                </span>
            </div>
            <a href="{{ route('admin.external.vendors.index', array_filter(request()->except(['filter_status', 'page']))) }}" class="btn btn-sm btn-light fw-medium py-0.5 px-2 fs-11">
                View All Sellers &times;
            </a>
        </div>
    @endif

    @if(request('kyc_status') || request('active_sellers') || request('account_status'))
        <div class="alert alert-primary border-0 shadow-sm mb-3 d-flex align-items-center justify-content-between py-2 px-3" style="border-radius: 8px;">
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="solar:filter-bold-duotone" class="fs-18 text-primary"></iconify-icon>
                <span class="fs-13">
                    @if(request('kyc_status'))
                        Filtered by KYC Status: <strong class="text-uppercase text-primary me-2">{{ request('kyc_status') }}</strong>
                    @endif
                    @if(request('active_sellers'))
                        Filtered by: <strong class="text-primary me-2">Catalog Active Sellers (With Approved Products)</strong>
                    @endif
                    @if(request('account_status'))
                        Account Status: <strong class="text-primary me-2">{{ request('account_status') == '1' ? 'Active' : 'Inactive' }}</strong>
                    @endif
                </span>
            </div>
            <a href="{{ route('admin.external.vendors.index') }}" class="btn btn-sm btn-light fw-medium py-0.5 px-2 fs-11">
                Clear Filter &times;
            </a>
        </div>
    @endif

    <!-- Reusable Table Filters -->
    <x-admin.table-filters 
        action="{{ route('admin.external.vendors.index') }}" 
        :search="$search" 
        placeholder="Search name, code, email, company, GST, type..." 
        :limit="$limit" 
        :sort="$sort" 
        :order="$order"
        :bulk-actions="[
            'delete' => 'Delete Selected',
            'status_active' => 'Activate Selected',
            'status_inactive' => 'Deactivate Selected'
        ]"
    >
        @if($filterStatus)
            <input type="hidden" name="filter_status" value="{{ $filterStatus }}">
        @endif
        <select name="filter_status" class="form-select w-auto py-1.5" onchange="this.form.submit()">
            <option value="">All Readiness States</option>
            <option value="active" {{ $filterStatus === 'active' ? 'selected' : '' }}>Active Sellers</option>
            <option value="inactive" {{ $filterStatus === 'inactive' ? 'selected' : '' }}>Inactive Sellers</option>
        </select>
        <select name="kyc_status" class="form-select w-auto py-1.5" onchange="this.form.submit()">
            <option value="">All KYC Status</option>
            <option value="APPROVED" {{ strtolower(request('kyc_status') ?? '') == 'approved' ? 'selected' : '' }}>Approved (Verified)</option>
            <option value="PENDING" {{ in_array(strtolower(request('kyc_status') ?? ''), ['pending', 'under_review']) ? 'selected' : '' }}>Pending / Review</option>
            <option value="REJECTED" {{ strtolower(request('kyc_status') ?? '') == 'rejected' ? 'selected' : '' }}>Rejected</option>
        </select>
        <select name="seller_type" class="form-select w-auto py-1.5" onchange="this.form.submit()">
            <option value="">All Seller Types</option>
            <option value="Manufacturer" {{ request('seller_type') == 'Manufacturer' ? 'selected' : '' }}>Manufacturer</option>
            <option value="Trader" {{ request('seller_type') == 'Trader' ? 'selected' : '' }}>Trader</option>
            <option value="Distributor" {{ request('seller_type') == 'Distributor' ? 'selected' : '' }}>Distributor</option>
        </select>
        <select name="account_status" class="form-select w-auto py-1.5" onchange="this.form.submit()">
            <option value="">All Account Status</option>
            <option value="1" {{ request('account_status') === '1' ? 'selected' : '' }}>Active Account</option>
            <option value="0" {{ request('account_status') === '0' ? 'selected' : '' }}>Inactive Account</option>
        </select>
    </x-admin.table-filters>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0 text-nowrap align-middle">
                            <thead class="bg-light bg-opacity-50 border-top border-bottom">
                                <tr>
                                    <th class="ps-3" style="width: 40px;">
                                        <div class="form-check fs-15">
                                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                                        </div>
                                    </th>
                                    <th class="ps-2" style="width: 50px;">#</th>
                                    <x-admin.table-header field="company_name" :sort="$sort" :order="$order">Company / Vendor Name</x-admin.table-header>
                                    <th>Seller Code</th>
                                    <x-admin.table-header field="email" :sort="$sort" :order="$order">Contact Info</x-admin.table-header>
                                    <th>Seller Type</th>
                                    <th>Catalog & Store</th>
                                    <th>KYC Status</th>
                                    <x-admin.table-header field="is_active" :sort="$sort" :order="$order">Account</x-admin.table-header>
                                    <th>Readiness & Missing Items</th>
                                    <x-admin.table-header field="created_at" :sort="$sort" :order="$order">Joined</x-admin.table-header>
                                    <th class="text-end pe-3" style="width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="form-check fs-15">
                                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $user->id }}">
                                            </div>
                                        </td>
                                        <td class="ps-2 text-muted small">{{ $loop->iteration + (($users->currentPage() - 1) * $users->perPage()) }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ $user->avatar_url }}" alt="avatar" class="rounded-circle border border-light-subtle shadow-sm" width="34" height="34">
                                                <div>
                                                    <span class="fw-semibold text-slate-800 d-block">{{ $user->company_name ?? ($user->profile?->company_name ?? '—') }}</span>
                                                    <span class="text-muted small d-block">({{ $user->name }})</span>
                                                    @if($user->sub_sellers_count > 0)
                                                        <a href="{{ route('admin.external.vendors.index', ['parent_id' => $user->id]) }}" class="badge bg-info-subtle text-info mt-1" style="text-decoration: none;">
                                                            <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" class="align-middle me-1"></iconify-icon>
                                                            Sub-Sellers: {{ $user->sub_sellers_count }}
                                                        </a>
                                                    @endif
                                                    @if($user->profile?->parent_id)
                                                        <span class="badge mt-1" style="color: #7c3aed; background: #f3e8ff;">
                                                            Sub-Seller
                                                        </span>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            @if($user->seller_id)
                                                <code class="small text-primary fw-bold">{{ $user->seller_id }}</code>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div>
                                                <div class="text-slate-800 fs-12">{{ $user->email }}</div>
                                                <div class="text-muted small">{{ $user->phone ?? ($user->mobile ?? '—') }}</div>
                                            </div>
                                        </td>
                                        <td>
                                            @php
                                                $sType = $user->profile?->seller_type ?: ($user->seller_type ?: null);
                                                $isDdcSub = (bool) ($user->profile?->is_ddc_subscribed ?? false);
                                            @endphp
                                            @if($sType)
                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 fs-11 fw-semibold">
                                                    <iconify-icon icon="solar:tag-bold-duotone" class="align-middle me-0.5"></iconify-icon> {{ $sType }}
                                                </span>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif

                                            @if($isDdcSub)
                                                <span class="badge bg-warning text-dark px-2 py-0.5 fs-10 fw-bold d-block mt-1" title="DDC Subscribed Seller">
                                                    <iconify-icon icon="solar:star-bold" class="align-middle me-0.5"></iconify-icon> DDC Active
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Catalog & Store Count -->
                                        <td>
                                            <div class="d-flex flex-column gap-1">
                                                <span class="badge {{ $user->products_count > 0 ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-light text-muted border' }} fs-11 fw-semibold py-0.5 px-1.5 align-self-start" title="Catalog Products">
                                                    <iconify-icon icon="solar:box-bold" class="fs-11 align-middle"></iconify-icon> {{ $user->products_count }} {{ Str::plural('Product', $user->products_count) }}
                                                </span>
                                                <span class="badge {{ $user->pickup_stores_count > 0 ? 'bg-info-subtle text-info border border-info-subtle' : 'bg-light text-muted border' }} fs-11 fw-semibold py-0.5 px-1.5 align-self-start" title="Pickup Stores">
                                                    <iconify-icon icon="solar:shop-bold" class="fs-11 align-middle"></iconify-icon> {{ $user->pickup_stores_count }} {{ Str::plural('Store', $user->pickup_stores_count) }}
                                                </span>
                                            </div>
                                        </td>

                                        <!-- KYC Status -->
                                        <td>
                                            @php
                                                $kStatus = strtoupper($user->profile?->kyc_status ?? 'PENDING');
                                            @endphp
                                            @if($kStatus === 'APPROVED')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-11 fw-semibold d-inline-flex align-items-center gap-1">
                                                    <iconify-icon icon="solar:shield-check-bold" class="fs-12"></iconify-icon> Verified
                                                </span>
                                            @elseif(in_array($kStatus, ['PENDING', 'UNDER_REVIEW']))
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 fs-11 fw-semibold d-inline-flex align-items-center gap-1">
                                                    <iconify-icon icon="solar:shield-warning-bold" class="fs-12"></iconify-icon> Pending
                                                </span>
                                            @elseif($kStatus === 'REJECTED')
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fs-11 fw-semibold d-inline-flex align-items-center gap-1">
                                                    <iconify-icon icon="solar:shield-cross-bold" class="fs-12"></iconify-icon> Rejected
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1 fs-11">
                                                    Unverified
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Account Status -->
                                        <td>
                                            @if($user->status === '1')
                                                <span class="badge bg-success-subtle text-success px-2 py-1 fs-11 fw-semibold d-inline-flex align-items-center gap-1">
                                                    <i class="bx bxs-circle fs-8 text-success"></i> Active
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-11 fw-semibold d-inline-flex align-items-center gap-1">
                                                    <i class="bx bxs-circle fs-8 text-danger"></i> Inactive
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Readiness & Missing Items -->
                                        <td>
                                            @if($user->is_seller_active)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fs-11 fw-semibold d-inline-flex align-items-center gap-1 shadow-xs">
                                                    <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-13 text-success"></iconify-icon>
                                                    Active
                                                </span>
                                            @else
                                                @php
                                                    $missing = $user->missing_requirements;
                                                    $missingCount = count($missing);
                                                @endphp
                                                <div class="d-flex flex-column gap-1" style="min-width: 185px;">
                                                    <div class="d-flex align-items-center gap-1">
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-1.5 py-0.5 fs-10 fw-bold d-inline-flex align-items-center gap-1">
                                                            <iconify-icon icon="solar:danger-triangle-bold" class="fs-11"></iconify-icon>
                                                            {{ $missingCount }} Missing
                                                        </span>
                                                    </div>
                                                    <div class="d-flex flex-wrap gap-1 mt-0.5">
                                                        @foreach($missing as $mItem)
                                                            <span class="badge {{ $mItem['class'] }} px-1.5 py-0.5 fs-10 fw-medium d-inline-flex align-items-center gap-1" title="{{ $mItem['label'] }}">
                                                                <iconify-icon icon="{{ $mItem['icon'] }}" class="fs-10"></iconify-icon>
                                                                {{ $mItem['label'] }}
                                                            </span>
                                                        @endforeach
                                                    </div>
                                                </div>
                                            @endif
                                        </td>

                                        <td class="text-muted small">{{ $user->created_at->format('d M Y') }}</td>
                                        <td class="text-end pe-3">
                                            <div class="d-inline-flex gap-1">
                                                @if($user->sub_sellers_count > 0)
                                                    <a href="{{ route('admin.external.vendors.index', ['parent_id' => $user->id]) }}" 
                                                        class="btn btn-sm btn-outline-info d-flex align-items-center justify-content-center p-1"
                                                        title="View Sub-Sellers"
                                                        style="width: 28px; height: 28px;">
                                                        <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" class="fs-14"></iconify-icon>
                                                    </a>
                                                @endif

                                                <a href="{{ route('admin.external.vendors.profile', $user->id) }}" 
                                                    class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center p-1"
                                                    title="View Profile"
                                                    style="width: 28px; height: 28px;">
                                                    <iconify-icon icon="solar:eye-bold-duotone" class="fs-14"></iconify-icon>
                                                </a>

                                                <button class="btn btn-sm btn-outline-warning d-flex align-items-center justify-content-center p-1"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#resetPwModal"
                                                    data-user-id="{{ $user->id }}"
                                                    data-user-name="{{ $user->name }}"
                                                    data-reset-url="{{ route('admin.external.vendors.reset-password', $user->id) }}"
                                                    title="Reset Password"
                                                    style="width: 28px; height: 28px;">
                                                    <iconify-icon icon="solar:lock-password-bold-duotone" class="fs-14"></iconify-icon>
                                                </button>

                                                <form action="{{ route('admin.external.vendors.toggle-status', $user->id) }}" method="POST" class="d-inline" data-ajax-form="true">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm confirm-action d-flex align-items-center justify-content-center p-1 {{ $user->status === '1' ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                                        data-confirm-message="Are you sure you want to {{ $user->status === '1' ? 'deactivate' : 'activate' }} {{ $user->name }}?"
                                                        title="{{ $user->status === '1' ? 'Deactivate' : 'Activate' }}"
                                                        style="width: 28px; height: 28px;">
                                                        <iconify-icon icon="{{ $user->status === '1' ? 'solar:user-block-rounded-bold-duotone' : 'solar:user-check-rounded-bold-duotone' }}" class="fs-14"></iconify-icon>
                                                    </button>
                                                </form>

                                                <form action="{{ route('admin.impersonate', ['vendor', $user->id]) }}" method="POST" class="d-inline" target="_blank">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-outline-info confirm-action d-flex align-items-center justify-content-center p-1"
                                                        data-confirm-message="Login as {{ $user->name }} in a new tab?"
                                                        title="Login as this user (Opens in new tab)"
                                                        style="width: 28px; height: 28px;">
                                                        <iconify-icon icon="solar:user-check-bold-duotone" class="fs-14"></iconify-icon>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <x-admin.empty-state 
                                        icon="solar:shop-bold-duotone" 
                                        title="No Sellers Found" 
                                        description="We couldn't find any MSME sellers matching the selected criteria." 
                                        colspan="12" 
                                    />
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($users->hasPages())
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                            <small class="text-muted">Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of {{ $users->total() }}</small>
                            {{ $users->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPwModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold text-slate-800">
                        <i class="bx bx-key me-1 text-warning"></i> Reset Vendor Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="resetPasswordForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted mb-3">Resetting password for: <strong id="resetUserName" class="text-slate-800"></strong></p>
                        <div class="alert alert-warning border-0 small py-2 d-flex align-items-center gap-2">
                            <iconify-icon icon="solar:danger-triangle-bold-duotone" class="fs-18 text-warning"></iconify-icon>
                            <span>The vendor will be forced to change their password on next login.</span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">New Password <span class="text-danger">*</span></label>
                            <input type="password" name="new_password" class="form-control" placeholder="Minimum 8 characters" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="new_password_confirmation" class="form-control" placeholder="Repeat new password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning text-white">
                            <i class="bx bx-key me-1"></i> Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const resetModal = document.getElementById('resetPwModal');
    if (resetModal) {
        resetModal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            document.getElementById('resetUserName').textContent = btn.getAttribute('data-user-name');
            document.getElementById('resetPasswordForm').action = btn.getAttribute('data-reset-url');
        });
    }
});
</script>
@endpush
