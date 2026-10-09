@extends('layouts.ddc')

@section('title', 'MSME vs Regional Product Comparison')
@section('page-title', 'MSME vs Regional Product Comparison')

@section('content')
    <!-- Header Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                MSME Website vs Regional Product Comparison
                            </h4>
                            <p class="text-muted small mb-0">Compare MSME Catalog pricing against Regional Catalog prices based on <strong>Landing Cost</strong>.</p>
                        </div>
                        <div class="col-md-6 text-md-end d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
                            <a href="{{ route('ddc.local-products.index') }}" class="btn btn-outline-secondary btn-sm fw-semibold">
                                <iconify-icon icon="solar:box-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Manage Products
                            </a>
                            <a href="{{ route('ddc.local-purchases.create') }}" class="btn btn-primary btn-sm fw-semibold">
                                <iconify-icon icon="solar:add-circle-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Stock In
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Stats Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-lg bg-primary-subtle rounded-4 p-3 text-primary d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="fs-28"></iconify-icon>
                    </div>
                    <div>
                        <span class="text-muted fs-12 d-block fw-semibold">Total Regional Products</span>
                        <h3 class="fw-bold text-slate-800 mb-0">{{ $totalProducts }}</h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-lg bg-success-subtle rounded-4 p-3 text-success d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <iconify-icon icon="solar:link-circle-bold-duotone" class="fs-28"></iconify-icon>
                    </div>
                    <div>
                        <span class="text-muted fs-12 d-block fw-semibold">Linked with MSME Catalog</span>
                        <h3 class="fw-bold text-success mb-0">{{ $linkedCount }} <small class="fs-12 text-muted fw-normal">({{ $totalProducts > 0 ? round(($linkedCount / $totalProducts) * 100, 1) : 0 }}%)</small></h3>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-4 h-100 p-3 bg-white">
                <div class="d-flex align-items-center gap-3">
                    <div class="avatar-lg bg-warning-subtle rounded-4 p-3 text-warning d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                        <iconify-icon icon="solar:shop-2-bold-duotone" class="fs-28"></iconify-icon>
                    </div>
                    <div>
                        <span class="text-muted fs-12 d-block fw-semibold">Regional Only (Unlinked)</span>
                        <h3 class="fw-bold text-slate-800 mb-0">{{ $unlinkedCount }}</h3>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('ddc.local-products.comparison') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><iconify-icon icon="solar:magnifer-linear"></iconify-icon></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0" placeholder="Search product name or code..." value="{{ $search }}">
                    </div>
                </div>

                <div class="col-md-3">
                    <select name="filter" class="form-select bg-light">
                        <option value="all" {{ $filter === 'all' ? 'selected' : '' }}>All Products</option>
                        <option value="linked" {{ $filter === 'linked' ? 'selected' : '' }}>Linked to MSME Only</option>
                        <option value="unlinked" {{ $filter === 'unlinked' ? 'selected' : '' }}>Regional Only (Unlinked)</option>
                    </select>
                </div>

                <div class="col-md-3">
                    <select name="category_id" class="form-select bg-light">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fw-semibold shadow-sm">Filter</button>
                    @if($search || $filter !== 'all' || $categoryId)
                        <a href="{{ route('ddc.local-products.comparison') }}" class="btn btn-light text-muted"><iconify-icon icon="solar:restart-bold"></iconify-icon></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Comparison Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2">
                <iconify-icon icon="solar:scale-bold-duotone" class="text-primary fs-20"></iconify-icon>
                Comparison List ({{ $localProducts->count() }} Products)
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light fs-11 text-uppercase fw-bold text-muted border-bottom">
                        <tr>
                            <th class="ps-4 py-3">Product Info</th>
                            <th class="py-3">MSME Catalog Prices</th>
                            <th class="py-3">Regional Catalog Prices</th>
                            <th class="pe-4 text-end py-3">Landing Cost Comparison</th>
                        </tr>
                    </thead>
                    <tbody class="fs-13">
                        @forelse($localProducts as $lp)
                            @php
                                $msme = $lp->msmeProduct;
                                $inv  = $lp->inventories->first();

                                // 1. MRP
                                $msmeMrp  = $msme ? (float)$msme->mrp : 0;
                                $localMrp = $inv ? (float)$inv->mrp : 0;

                                // 2. CSP (Retail Customer Selling Price)
                                $msmeCsp  = $msme ? (float)($msme->adc_purchase_price ?: ($msme->seller_price_incl_gst ?: $msme->mrp)) : 0;
                                $localCsp = $inv ? (float)$inv->csp : 0;

                                // 3. MSP (Wholesale Minimum Selling Price)
                                $msmeMsp  = $msme ? (float)($msme->ddc_purchase_price ?: ($msme->seller_price_incl_gst ?: $msme->mrp)) : 0;
                                $localMsp = $inv ? (float)$inv->msp : 0;

                                // 4. Landing Cost (Purchase Price named Landing Cost)
                                $msmeLanding  = $msme ? (float)($msme->seller_price_incl_gst ?: ($msme->ddc_purchase_price ?: $msme->mrp)) : 0;
                                $localLanding = $inv ? (float)($inv->landing_cost ?: $inv->purchase_price) : 0;

                                // Landing Cost Variance (Regional - MSME)
                                $landingDiff = ($msme && $localLanding > 0) ? ($localLanding - $msmeLanding) : 0;
                                $landingDiffPercent = ($msme && $msmeLanding > 0 && $localLanding > 0) ? round(($landingDiff / $msmeLanding) * 100, 1) : 0;
                            @endphp
                            <tr>
                                <td class="ps-4 py-3" style="min-width: 220px;">
                                    <div class="d-flex align-items-center gap-3">
                                        <img src="{{ $lp->product_image ?? ($msme->featured_image ?? asset('assets/images/placeholder.png')) }}" 
                                             class="rounded-3 border object-fit-cover shadow-sm" width="48" height="48" alt="img"
                                             onerror="this.src='https://via.placeholder.com/48?text=Product';">
                                        <div>
                                            <div class="fw-bold text-slate-800 mb-0.5">{{ $lp->product_name }}</div>
                                            <div class="d-flex align-items-center gap-1.5">
                                                @if($msme)
                                                    <span class="badge bg-success-subtle text-success fs-10 fw-semibold">✓ MSME Linked</span>
                                                @else
                                                    <span class="badge bg-secondary-subtle text-secondary fs-10 fw-semibold">REGIONAL Only</span>
                                                @endif
                                                <span class="text-muted fs-11">Code: <strong>{{ $lp->local_product_code }}</strong></span>
                                            </div>
                                            <small class="text-muted d-block fs-11 mt-0.5">{{ $lp->category->name ?? '—' }} • {{ $lp->brand->name ?? 'Unbranded' }}</small>
                                        </div>
                                    </div>
                                </td>

                                <!-- MSME Catalog Pricing -->
                                <td class="py-3">
                                    @if($msme)
                                        <div class="fs-12">
                                            <div class="d-flex justify-content-between gap-3 py-0.5">
                                                <span class="text-muted fs-11">MRP:</span>
                                                <strong class="text-slate-800">₹{{ number_format($msmeMrp, 2) }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between gap-3 py-0.5">
                                                <span class="text-muted fs-11">CSP (Retail):</span>
                                                <strong class="text-primary">₹{{ number_format($msmeCsp, 2) }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between gap-3 py-0.5">
                                                <span class="text-muted fs-11">MSP (Wholesale):</span>
                                                <strong class="text-indigo-600">₹{{ number_format($msmeMsp, 2) }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between gap-3 py-0.5">
                                                <span class="text-muted fs-11">Landing Cost:</span>
                                                <strong class="text-slate-700">₹{{ number_format($msmeLanding, 2) }}</strong>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted fs-12 italic">— Not Linked —</span>
                                    @endif
                                </td>

                                <!-- Regional Catalog Pricing -->
                                <td class="py-3">
                                    @if($inv)
                                        <div class="fs-12">
                                            <div class="d-flex justify-content-between gap-3 py-0.5">
                                                <span class="text-muted fs-11">MRP:</span>
                                                <strong class="text-slate-800">₹{{ number_format($localMrp, 2) }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between gap-3 py-0.5">
                                                <span class="text-muted fs-11">CSP (Retail):</span>
                                                <strong class="text-emerald-700 fw-bold">₹{{ number_format($localCsp, 2) }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between gap-3 py-0.5">
                                                <span class="text-muted fs-11">MSP (Wholesale):</span>
                                                <strong class="text-slate-800">₹{{ number_format($localMsp, 2) }}</strong>
                                            </div>
                                            <div class="d-flex justify-content-between gap-3 py-0.5">
                                                <span class="text-muted fs-11">Landing Cost:</span>
                                                <strong class="text-slate-800">₹{{ number_format($localLanding, 2) }}</strong>
                                            </div>
                                        </div>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning fs-11">No Stock Entry</span>
                                    @endif
                                </td>

                                <!-- Single Overall Landing Cost Comparison -->
                                <td class="pe-4 text-end py-3">
                                    @if($msme && $localLanding > 0)
                                        @if($landingDiff == 0)
                                            <span class="badge bg-success-subtle text-success fs-12 px-2.5 py-1.5 rounded-pill border border-success-subtle">
                                                <iconify-icon icon="solar:check-read-bold" class="align-middle me-1"></iconify-icon> Equal Landing Cost
                                            </span>
                                        @elseif($landingDiff < 0)
                                            <div>
                                                <span class="badge fs-12 px-2.5 py-1.5 rounded-pill shadow-sm" style="background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0;">
                                                    ↓ Cheaper (₹{{ number_format(abs($landingDiff), 2) }} less)
                                                </span>
                                                <span class="d-block fs-11 text-emerald-700 mt-1 fw-semibold">{{ abs($landingDiffPercent) }}% lower than MSME Landing Cost</span>
                                            </div>
                                        @else
                                            <div>
                                                <span class="badge bg-danger-subtle text-danger fs-12 px-2.5 py-1.5 rounded-pill border border-danger-subtle shadow-sm">
                                                    ↑ Higher (+₹{{ number_format($landingDiff, 2) }})
                                                </span>
                                                <span class="d-block fs-11 text-danger mt-1 fw-semibold">+{{ $landingDiffPercent }}% higher than MSME Landing Cost</span>
                                            </div>
                                        @endif
                                    @elseif(!$msme)
                                        <span class="text-muted fs-11 italic">— Regional Only —</span>
                                    @else
                                        <span class="text-muted fs-11 italic">Stock in required for comparison</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center py-5 text-muted">
                                    <iconify-icon icon="solar:box-minimalistic-broken" class="fs-40 text-muted mb-2"></iconify-icon>
                                    <p class="mb-0">No Regional Products found for comparison.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
