@extends('layouts.adc')

@section('title', 'My Inventory')
@section('page-title', 'My Inventory')

@push('styles')
    <style>
        /* ── Table Wrapper ── */
        .inv-table-wrap {
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, .07);
            overflow: hidden;
            border: 1px solid #e8ecf4;
        }

        .inv-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }

        .inv-table thead tr.thead-group th {
            background: #1e293b;
            color: #cbd5e1;
            font-size: 9.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .7px;
            padding: 8px 10px;
            text-align: center;
            border-bottom: none;
        }

        .inv-table thead tr.thead-cols th {
            background: #f8fafd;
            color: #64748b;
            font-size: 10.5px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .55px;
            padding: 9px 10px;
            border-bottom: 2px solid #e8ecf4;
            white-space: nowrap;
            position: sticky;
            top: 0;
            z-index: 2;
        }

        .inv-table thead .group-product {
            border-left: 3px solid #6366f1;
        }

        .inv-table thead .group-stock {
            border-left: 3px solid #f97316;
            background: #fff7ed;
            color: #9a3412;
        }

        .inv-table thead .group-pricing {
            border-left: 3px solid #3b82f6;
            background: #eff6ff;
            color: #1e40af;
        }

        .inv-table thead .group-dates {
            border-left: 3px solid #10b981;
            background: #f0fdf4;
            color: #065f46;
        }

        /* Column group header background matching */
        .inv-table thead tr.thead-group .g-product {
            background: #1e293b;
            border-right: 1px solid #334155;
        }

        .inv-table thead tr.thead-group .g-stock {
            background: #431407;
            border-right: 1px solid #7c2d12;
            color: #fed7aa;
        }

        .inv-table thead tr.thead-group .g-pricing {
            background: #1e3a5f;
            border-right: 1px solid #1e40af;
            color: #bfdbfe;
        }

        .inv-table thead tr.thead-group .g-dates {
            background: #052e16;
            border-right: 1px solid #065f46;
            color: #bbf7d0;
        }

        .inv-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background .15s;
        }

        .inv-table tbody tr:last-child {
            border-bottom: none;
        }

        .inv-table tbody tr:hover {
            background: #fafbff;
        }

        .inv-table tbody td {
            padding: 9px 10px;
            vertical-align: middle;
        }

        /* Stock group cells light tint */
        .inv-table tbody td.col-stock {
            background: rgba(249, 115, 22, .04);
        }

        .inv-table tbody td.col-price {
            background: rgba(59, 130, 246, .03);
        }

        .inv-table tbody td.col-date {
            background: rgba(16, 185, 129, .03);
        }

        /* ── Product cell ── */
        .prod-img {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            object-fit: contain;
            background: #f1f5f9;
            border: 1px solid #e2e8f0;
            padding: 3px;
            flex-shrink: 0;
        }

        .prod-img-placeholder {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            background: #f1f5f9;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .prod-name {
            font-weight: 600;
            color: #1e293b;
            font-size: 13px;
            line-height: 1.3;
        }

        .prod-meta {
            font-size: 10.5px;
            color: #94a3b8;
            margin-top: 2px;
        }

        .prod-meta span {
            font-family: monospace;
        }

        /* ── Stock badges ── */
        .qty-badge {
            display: inline-flex;
            align-items: center;
            gap: 3px;
            border-radius: 6px;
            padding: 3px 8px;
            font-weight: 700;
            font-size: 12px;
            white-space: nowrap;
        }

        .qty-received {
            background: rgba(99, 102, 241, .1);
            color: #4338ca;
            border: 1px solid rgba(99, 102, 241, .2);
        }

        .qty-sold {
            background: rgba(239, 68, 68, .1);
            color: #b91c1c;
            border: 1px solid rgba(239, 68, 68, .2);
        }

        .qty-returned {
            background: rgba(16, 185, 129, .1);
            color: #065f46;
            border: 1px solid rgba(16, 185, 129, .2);
        }

        .qty-balance {
            background: rgba(249, 115, 22, .12);
            color: #b45309;
            border: 1px solid rgba(249, 115, 22, .3);
        }

        /* ── Price / Number cells ── */
        .price-val {
            font-weight: 700;
            color: #1e293b;
            font-size: 12.5px;
        }

        .price-val.pp {
            color: #7c3aed;
        }

        .price-val.msp {
            color: #0369a1;
        }

        .price-val.csp {
            color: #0f766e;
        }

        .price-val.disc {
            color: #16a34a;
        }

        /* ── Modern Expiry Badges ── */
        .badge-expiry {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 3px;
            padding: 4px 10px;
            border-radius: 50rem;
            font-size: 11px;
            font-weight: 600;
            white-space: nowrap;
            box-shadow: 0 1px 2px rgba(0, 0, 0, 0.03);
        }

        .badge-expiry-ok {
            background-color: #ecfdf5;
            color: #047857;
            border: 1px solid #a7f3d0;
        }

        .badge-expiry-warn {
            background-color: #fff7ed;
            color: #c2410c;
            border: 1px solid #ffedd5;
        }

        .badge-expiry-danger {
            background-color: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        /* ── Editable inline fields ── */
        .inv-editable {
            border: 1.5px solid transparent;
            border-radius: 6px;
            padding: 4px 6px;
            background: transparent;
            font-size: 12.5px;
            color: #1e293b;
            transition: border-color .2s, background .2s;
            width: 100%;
            min-width: 110px;
        }

        .inv-editable:hover {
            border-color: #cbd5e1;
            background: #f8fafc;
        }

        .inv-editable:focus {
            border-color: #ff9933;
            background: #fff;
            outline: none;
            box-shadow: 0 0 0 3px rgba(255, 153, 51, .12);
        }

        .inv-editable.saving {
            border-color: #a78bfa;
            background: #faf5ff;
        }

        .inv-editable.saved {
            border-color: #4ade80;
            background: #f0fdf4;
        }

        .inv-editable.err {
            border-color: #f87171;
            background: #fef2f2;
        }

        .save-tip {
            font-size: 9.5px;
            margin-top: 1px;
            height: 13px;
            display: block;
        }

        .save-tip.saving {
            color: #7c3aed;
        }

        .save-tip.saved {
            color: #16a34a;
        }

        .save-tip.err {
            color: #dc2626;
        }

        /* ── Margin cell ── */
        .margin-wrap {
            position: relative;
        }

        .margin-wrap .rs {
            position: absolute;
            left: 8px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 11px;
            color: #64748b;
            pointer-events: none;
        }

        .margin-wrap .inv-editable {
            padding-left: 18px;
        }

        /* ── Responsive scroll ── */
        .inv-scroll-wrap {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
        }
    </style>
@endpush

@section('content')

    {{-- ── Page Header ── --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-8">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                My Inventory Management
                            </h4>
                            <p class="text-muted small mb-0">Stock received, sold, returned &amp; balance — all items in stock. Edit Margins, Mfg &amp; Expiry dates inline.</p>
                        </div>
                        <div class="col-md-4 text-md-end d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
                            <span class="badge bg-primary-subtle text-primary fs-12 px-3 py-2 rounded-pill">
                                <iconify-icon icon="solar:layers-bold-duotone" class="align-middle me-1 fs-14"></iconify-icon> {{ $inventories->total() }} Batch{{ $inventories->total() !== 1 ? 'es' : '' }} in Stock
                            </span>
                            <a href="{{ route('adc.inventory.export', request()->query()) }}" class="btn btn-sm btn-success px-3 py-2 rounded-pill fw-semibold shadow-sm d-inline-flex align-items-center gap-1" id="exportInventoryBtn" title="Export Inventory to CSV">
                                <iconify-icon icon="solar:file-download-bold-duotone" class="fs-16"></iconify-icon> Export CSV
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Active Expiry Filter Notification ── --}}
    @if(($expiryFilter ?? 'ALL') !== 'ALL')
        <div class="row mb-3">
            <div class="col-12">
                <div class="alert alert-warning border-0 shadow-sm py-2.5 px-3 d-flex align-items-center justify-content-between mb-0 rounded-3">
                    <div class="d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:filter-bold-duotone" class="text-warning fs-18"></iconify-icon>
                        <span class="fs-13">
                            Showing filtered inventory for: 
                            <strong class="text-dark">
                                @if($expiryFilter === 'expired') 🚨 Expired Batches (&lt; Today)
                                @elseif($expiryFilter === '1_month') ⚠️ Critical Batches (1 to 30 Days Expiry)
                                @elseif($expiryFilter === '3_months') ℹ️ Moderate Batches (31 to 90 Days Expiry)
                                @elseif($expiryFilter === '6_months') ✅ Safe Shelf-Life (91 to 180 Days Expiry)
                                @endif
                            </strong>
                        </span>
                    </div>
                    <a href="{{ route('adc.inventory.index') }}" class="btn btn-sm btn-outline-dark fw-semibold py-1 px-2.5 fs-12">
                        Clear Expiry Filter
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- ── Search & Filter Bar ── --}}
    <div class="row mb-3">
        <div class="col-12 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <form action="{{ route('adc.inventory.index') }}" method="GET" class="d-flex align-items-center gap-2 flex-grow-1 flex-wrap" id="filterForm">
                <input type="hidden" name="source" value="{{ $source }}">
                <div class="input-group" style="max-width: 320px;">
                    <span class="input-group-text bg-white border-end-0">
                        <iconify-icon icon="solar:magnifer-bold-duotone" class="text-muted"></iconify-icon>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0"
                        placeholder="Product name, code, barcode, batch..." value="{{ $search }}">
                </div>
                <button type="submit" class="btn btn-primary px-3">Search</button>

                <!-- Expiry Filter Selector -->
                <select name="expiry_filter" class="form-select form-select-sm" style="width: 175px;" onchange="this.form.submit()">
                    <option value="ALL" {{ ($expiryFilter ?? 'ALL') === 'ALL' ? 'selected' : '' }}>All Expiry Statuses</option>
                    <option value="expired" {{ ($expiryFilter ?? '') === 'expired' ? 'selected' : '' }}>🚨 Expired (&lt; Today)</option>
                    <option value="1_month" {{ ($expiryFilter ?? '') === '1_month' ? 'selected' : '' }}>⚠️ 1 Month (1–30 Days)</option>
                    <option value="3_months" {{ ($expiryFilter ?? '') === '3_months' ? 'selected' : '' }}>ℹ️ 1 to 3 Months</option>
                    <option value="6_months" {{ ($expiryFilter ?? '') === '6_months' ? 'selected' : '' }}>✅ 3 to 6 Months</option>
                </select>

                <!-- Per Page Limit Selector -->
                <select name="per_page" class="form-select form-select-sm" style="width: 110px;" onchange="this.form.submit()">
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 / Page</option>
                    <option value="15" {{ $perPage == 15 ? 'selected' : '' }}>15 / Page</option>
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 / Page</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 / Page</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 / Page</option>
                </select>

                @if($search || $source !== 'ALL' || $perPage != 15 || ($expiryFilter ?? 'ALL') !== 'ALL')
                    <a href="{{ route('adc.inventory.index') }}" class="btn btn-outline-secondary px-3">Reset</a>
                @endif
            </form>

            <!-- Source Filter Tabs -->
            <div class="btn-group" role="group" aria-label="Inventory Source">
                <a href="{{ route('adc.inventory.index', array_filter(['search' => $search, 'source' => 'ALL', 'per_page' => $perPage, 'expiry_filter' => ($expiryFilter ?? 'ALL') !== 'ALL' ? $expiryFilter : null])) }}" class="btn btn-sm {{ $source === 'ALL' ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold">
                    All Inventory
                </a>
                <a href="{{ route('adc.inventory.index', array_filter(['search' => $search, 'source' => 'MSME', 'per_page' => $perPage, 'expiry_filter' => ($expiryFilter ?? 'ALL') !== 'ALL' ? $expiryFilter : null])) }}" class="btn btn-sm {{ $source === 'MSME' ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold">
                    MSME Inventory
                </a>
                <a href="{{ route('adc.inventory.index', array_filter(['search' => $search, 'source' => 'LOCAL', 'per_page' => $perPage, 'expiry_filter' => ($expiryFilter ?? 'ALL') !== 'ALL' ? $expiryFilter : null])) }}" class="btn btn-sm {{ $source === 'LOCAL' ? 'btn-success' : 'btn-outline-success' }} fw-semibold">
                    Regional Inventory
                </a>
            </div>
        </div>
    </div>

    {{-- ── Inventory Table ── --}}
    @if($inventories->count())
        <div class="inv-table-wrap">
            <div class="inv-scroll-wrap">
                <table class="inv-table" id="inventoryTable">
                    <thead>
                        {{-- Group Headers --}}
                        <tr class="thead-group">
                            <th colspan="6" class="g-product text-start" style="padding-left:12px;">
                                <iconify-icon icon="solar:box-bold-duotone" class="me-1"></iconify-icon> Product Info &amp; POS Status
                            </th>
                            <th colspan="4" class="g-stock text-center">
                                <iconify-icon icon="solar:layers-bold-duotone" class="me-1"></iconify-icon> Stock Tracking
                            </th>
                            <th colspan="4" class="g-pricing text-center">
                                <iconify-icon icon="solar:tag-price-bold-duotone" class="me-1"></iconify-icon> Pricing &amp; Margin
                            </th>
                            <th colspan="3" class="g-dates text-center">
                                <iconify-icon icon="solar:calendar-bold-duotone" class="me-1"></iconify-icon> Dates &amp; Expiry
                            </th>
                        </tr>
                        {{-- Column Headers --}}
                        <tr class="thead-cols">
                            {{-- S.No & POS Status --}}
                            <th class="text-center" style="min-width:50px;">S.No.</th>
                            <th class="text-center" style="min-width:110px;">POS Status</th>

                            {{-- Product --}}
                            <th class="group-product" style="min-width:210px;">Product</th>
                            <th style="min-width:70px;">Source</th>
                            <th style="min-width:75px;">Batch</th>
                            <th style="min-width:70px;">UOM</th>

                            {{-- Stock --}}
                            <th class="group-stock" style="min-width:85px;">Received</th>
                            <th class="col-stock" style="min-width:75px;">Sold</th>
                            <th class="col-stock" style="min-width:80px;">Returned</th>
                            <th class="col-stock" style="min-width:80px;">Balance</th>

                            {{-- Pricing --}}
                            <th class="group-price" style="min-width:85px;">MRP</th>
                            <th style="min-width:95px;">Purch. Price</th>
                            <th style="min-width:95px;">CSP (₹) ✎</th>
                            <th style="min-width:110px;">CSP Margin (%)</th>

                            {{-- Dates --}}
                            <th class="group-dates" style="min-width:140px;">Mfg. Date ✎</th>
                            <th class="col-date" style="min-width:140px;">Expire Date ✎</th>
                            <th class="col-date" style="min-width:85px;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($inventories as $inv)
                            @php
                                $mrpVal = (float) ($inv->mrp ?? 0);
                                $purchasePriceVal = (float) ($inv->purchase_price ?? 0);
                                $calculatedCsp = $purchasePriceVal + (($mrpVal - $purchasePriceVal) / 2);
                                $cspVal = (float) ($inv->csp > 0 ? $inv->csp : ($calculatedCsp > 0 ? $calculatedCsp : $purchasePriceVal));

                                // Margin % = ((MRP - CSP) / MRP) * 100
                                $calcMargin = $mrpVal > 0 ? round((($mrpVal - $cspVal) / $mrpVal) * 100, 2) : 0;
                                $cspMarginVal = $inv->csp_margin !== null ? (float) $inv->csp_margin : $calcMargin;

                                // Stock figures
                                $soldQty = (float) ($inv->sold_quantity ?? 0);
                                $returnedQty = (float) ($inv->returned_quantity ?? 0);
                                $balanceQty = (float) $inv->quantity;  // current on-hand
                                $receivedQty = $balanceQty + $soldQty - $returnedQty; // total ever received

                                $formatQty = function($qty) {
                                    $v = (float) $qty;
                                    if (abs($v - round($v)) < 0.0001) {
                                        return (int) round($v);
                                    }
                                    return rtrim(rtrim(number_format($v, 3, '.', ''), '0'), '.');
                                };

                                $daysLeft = $inv->expire_date ? (int) floor(now()->startOfDay()->diffInDays($inv->expire_date->startOfDay(), false)) : null;
                                $isExpired = $daysLeft !== null && $daysLeft < 0;
                                $isNearExpiry = $daysLeft !== null && $daysLeft >= 0 && $daysLeft <= 30;
                                $expiryClass = $isExpired ? 'badge-expiry-danger' : ($isNearExpiry ? 'badge-expiry-warn' : 'badge-expiry-ok');

                                $imgSrc = null;
                                if ($inv->product?->featured_image) {
                                    $imgSrc = Str::startsWith($inv->product->featured_image, ['http://', 'https://'])
                                        ? $inv->product->featured_image
                                        : asset('storage/' . $inv->product->featured_image);
                                }
                                $uom = $inv->unit_of_measure ?? ($inv->product?->unit_of_measure ?? 'Units');
                                $gstRateVal = (float) (
                                    $inv->gst_rate
                                    ?? $inv->product?->gst_rate
                                    ?? $inv->localProduct?->gst_rate
                                    ?? 0
                                );
                            @endphp
                            <tr data-inv-id="{{ $inv->id }}" data-update-url="{{ route('adc.inventory.update', $inv->id) }}">

                                {{-- S.No. --}}
                                <td class="text-center fw-bold text-slate-600" style="font-size:12px;">
                                    {{ $loop->iteration + ($inventories->currentPage() - 1) * $inventories->perPage() }}
                                </td>

                                {{-- POS Status Toggle --}}
                                <td class="text-center">
                                    @if(($inv->pos_status ?? 'Active') === 'Active')
                                        <button type="button" class="btn btn-sm btn-success px-2 py-0.5 fs-11 rounded-pill toggle-pos-btn" data-url="{{ route('adc.inventory.toggle-pos', $inv->id) }}">
                                            <iconify-icon icon="solar:check-circle-bold-duotone" class="align-middle me-0.5"></iconify-icon> POS Active
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-warning px-2 py-0.5 fs-11 rounded-pill toggle-pos-btn" data-url="{{ route('adc.inventory.toggle-pos', $inv->id) }}">
                                            <iconify-icon icon="solar:shop-bold-duotone" class="align-middle me-0.5"></iconify-icon> Enable POS
                                        </button>
                                    @endif
                                </td>

                                {{-- Product --}}
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        @if($imgSrc)
                                            <img src="{{ $imgSrc }}" alt="{{ $inv->product_name }}" class="prod-img">
                                        @else
                                            <div class="prod-img-placeholder">
                                                <iconify-icon icon="solar:box-bold-duotone" class="text-muted"
                                                    style="font-size:17px;"></iconify-icon>
                                            </div>
                                        @endif
                                        <div>
                                            <div class="prod-name">{{ $inv->product_name ?? ($inv->product?->product_name ?? '—') }}
                                            </div>
                                            <div class="prod-meta">
                                                <span>{{ $inv->product_code ?? '—' }}</span>
                                                @if($inv->barcode) &nbsp;·&nbsp;<span>{{ $inv->barcode }}</span> @endif
                                                &nbsp;·&nbsp;<span class="badge bg-info-subtle text-info fw-bold fs-10 px-1.5 py-0.5 rounded-pill">GST {{ number_format($gstRateVal, 0) }}%</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Source --}}
                                <td>
                                    @if(($inv->inventory_source ?? 'MSME') === 'LOCAL')
                                        <span class="badge bg-success-subtle text-success fw-semibold px-2 py-1 rounded-pill" style="font-size:10.5px;">LOCAL</span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1 rounded-pill" style="font-size:10.5px;">MSME</span>
                                    @endif
                                </td>

                                {{-- Batch --}}
                                <td>
                                    <span class="font-monospace fw-semibold text-primary" style="font-size:11.5px;">
                                        {{ $inv->batch_number ?: '—' }}
                                    </span>
                                </td>

                                {{-- UOM --}}
                                <td><span class="text-muted small">{{ $uom }}</span></td>

                                {{-- ── Stock Tracking ── --}}
                                <td class="col-stock">
                                    <span class="qty-badge qty-received">{{ $formatQty($receivedQty) }}</span>
                                </td>
                                <td class="col-stock">
                                    <span class="qty-badge qty-sold">{{ $formatQty($soldQty) }}</span>
                                </td>
                                <td class="col-stock">
                                    <span class="qty-badge qty-returned">{{ $formatQty($returnedQty) }}</span>
                                </td>
                                <td class="col-stock">
                                    <span class="qty-badge qty-balance">{{ $formatQty($balanceQty) }}</span>
                                </td>

                                {{-- ── Pricing ── --}}
                                <td class="col-price"><span class="price-val">₹{{ number_format($mrpVal, 2) }}</span></td>
                                <td class="col-price"><span class="price-val pp">₹{{ number_format($purchasePriceVal, 2) }}</span></td>
                                <td class="col-price">
                                    <div class="margin-wrap">
                                        <span class="rs">₹</span>
                                        <input type="number" step="0.01" min="0" class="inv-editable price-field"
                                            data-field="csp" style="padding-left:18px;"
                                            value="{{ number_format($cspVal, 2, '.', '') }}"
                                            placeholder="0.00">
                                    </div>
                                    <span class="save-tip" id="si-csp-{{ $inv->id }}"></span>
                                </td>

                                {{-- CSP Margin % (read-only calculated badge) --}}
                                <td class="col-price">
                                    <span class="badge bg-success-subtle text-success fw-bold fs-12 px-2.5 py-1.5 rounded-pill csp-margin-badge" id="csp-margin-badge-{{ $inv->id }}">
                                        {{ number_format($cspMarginVal, 2) }}%
                                    </span>
                                </td>

                                {{-- Mfg Date (editable) --}}
                                <td class="col-date">
                                    <input type="date" class="inv-editable mfg-field" data-field="mfg_date"
                                        value="{{ $inv->mfg_date ? $inv->mfg_date->format('Y-m-d') : '' }}">
                                    <span class="save-tip" id="si-mfg-{{ $inv->id }}"></span>
                                </td>

                                {{-- Expire Date (editable) --}}
                                <td class="col-date">
                                    <input type="date" class="inv-editable exp-field" data-field="expire_date"
                                        value="{{ $inv->expire_date ? $inv->expire_date->format('Y-m-d') : '' }}">
                                    <span class="save-tip" id="si-exp-{{ $inv->id }}"></span>
                                </td>

                                {{-- Expiry Status --}}
                                <td class="col-date text-center col-expiry-status" id="expiry-status-td-{{ $inv->id }}">
                                    @if($inv->expire_date)
                                        <span class="badge-expiry {{ $expiryClass }}">
                                            @if($isExpired)
                                                <iconify-icon icon="solar:danger-circle-bold" class="fs-12 align-middle"></iconify-icon> Expired
                                            @elseif($isNearExpiry)
                                                <iconify-icon icon="solar:clock-circle-bold" class="fs-12 align-middle"></iconify-icon>
                                                @if($daysLeft == 0) Today
                                                @elseif($daysLeft == 1) 1 Day Left
                                                @else {{ $daysLeft }} Days Left
                                                @endif
                                            @else
                                                <iconify-icon icon="solar:check-circle-bold" class="fs-12 align-middle"></iconify-icon> Valid
                                            @endif
                                        </span>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>

                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Legend --}}
            <div class="d-flex flex-wrap gap-3 align-items-center px-3 py-2 border-top bg-light" style="font-size:11.5px;">
                <span class="text-muted fw-semibold">Pricing &amp; Margin Rules:</span>
                <span class="badge bg-primary-subtle text-primary">Margin = ((CSP - MSP) / MSP) × 100</span>
                <span class="ms-auto text-muted" style="font-size:10.5px;">✎ = Click to edit &amp; auto-save</span>
            </div>
        </div>
    @else
        <div class="card border-0 shadow-sm py-5 text-center">
            <iconify-icon icon="solar:box-minimalistic-broken"
                class="fs-48 text-muted d-block mx-auto mb-2 opacity-50"></iconify-icon>
            <p class="text-muted mb-0">No stock records found{{ $search ? ' matching your search' : '' }}.</p>
            @if($search)
                <a href="{{ route('adc.inventory.index') }}" class="btn btn-sm btn-outline-primary mt-3">Clear Search</a>
            @endif
        </div>
    @endif

    {{-- ── Pagination ── --}}
    @if($inventories->hasPages())
        <div class="mt-3">
            {{ $inventories->links() }}
        </div>
    @endif

@endsection

@push('scripts')
    <script>
        (function () {
            const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

            function debounce(fn, delay) {
                let timer;
                return function (...args) {
                    clearTimeout(timer);
                    timer = setTimeout(() => fn.apply(this, args), delay);
                };
            }

            function getIndicatorId(invId, field) {
                if (field === 'adc_margin') return `si-adc_margin-${invId}`;
                if (field === 'csp_margin') return `si-csp_margin-${invId}`;
                if (field === 'margin') return `si-margin-${invId}`;
                if (field === 'mfg_date') return `si-mfg-${invId}`;
                if (field === 'expire_date') return `si-exp-${invId}`;
                return null;
            }

            function showTip(id, state, msg) {
                const el = document.getElementById(id);
                if (!el) return;
                el.className = `save-tip ${state}`;
                el.textContent = msg;
                if (state === 'saved') {
                    setTimeout(() => { el.textContent = ''; el.className = 'save-tip'; }, 2500);
                }
            }

            function saveField(input) {
                const row = input.closest('tr');
                const invId = row.dataset.invId;
                const updateUrl = row.dataset.updateUrl;
                const field = input.dataset.field;
                const value = input.value;
                const tipId = getIndicatorId(invId, field);

                input.classList.remove('saved', 'err');
                input.classList.add('saving');
                showTip(tipId, 'saving', '⏳ Saving…');

                const body = new URLSearchParams();
                body.append('_method', 'PUT');
                body.append('_token', CSRF);
                body.append(field, value);

                fetch(updateUrl, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
                    body: body,
                })
                    .then(r => r.json().then(data => ({ ok: r.ok, data })))
                    .then(({ ok, data }) => {
                        input.classList.remove('saving');
                        if (ok && data.success) {
                            input.classList.add('saved');
                            showTip(tipId, 'saved', '✓ Saved');
                            if (data.csp_margin !== undefined) {
                                const badgeEl = document.getElementById(`csp-margin-badge-${invId}`);
                                if (badgeEl) { badgeEl.textContent = data.csp_margin + '%'; }
                            }
                            if (data.expiry_html !== undefined) {
                                const statusTd = document.getElementById(`expiry-status-td-${invId}`);
                                if (statusTd) {
                                    statusTd.innerHTML = data.expiry_html;
                                }
                            }
                        } else {
                            input.classList.add('err');
                            showTip(tipId, 'err', '✗ ' + (data.message || 'Value greater than MRP'));
                        }
                    })
                    .catch(() => {
                        input.classList.remove('saving');
                        input.classList.add('err');
                        showTip(tipId, 'err', '✗ Failed');
                    });
            }

            const debouncedSave = debounce(saveField, 700);

            document.querySelectorAll('.price-field').forEach(inp => {
                inp.addEventListener('input', () => debouncedSave(inp));
                inp.addEventListener('change', () => debouncedSave(inp));
            });

            document.querySelectorAll('.mfg-field, .exp-field').forEach(inp => {
                inp.addEventListener('change', () => saveField(inp));
            });

            // Toggle POS Status Ajax Handler
            document.querySelectorAll('.toggle-pos-btn').forEach(btn => {
                btn.addEventListener('click', function(e) {
                    e.preventDefault();
                    const url = this.getAttribute('data-url') || this.dataset.url;
                    if (!url) return;

                    const originalText = this.innerHTML;
                    this.disabled = true;
                    this.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Updating...';

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': CSRF,
                            'Accept': 'application/json',
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(r => r.json().then(data => ({ ok: r.ok, data })))
                    .then(({ ok, data }) => {
                        this.disabled = false;
                        if (ok && data.success) {
                            if (data.pos_status === 'Active') {
                                this.className = 'btn btn-sm btn-success px-2 py-0.5 fs-11 rounded-pill toggle-pos-btn';
                                this.innerHTML = '<iconify-icon icon="solar:check-circle-bold-duotone" class="align-middle me-0.5"></iconify-icon> POS Active';
                            } else {
                                this.className = 'btn btn-sm btn-outline-warning px-2 py-0.5 fs-11 rounded-pill toggle-pos-btn';
                                this.innerHTML = '<iconify-icon icon="solar:shop-bold-duotone" class="align-middle me-0.5"></iconify-icon> Enable POS';
                            }
                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'POS Status Updated',
                                    text: data.message,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            }
                        } else {
                            this.innerHTML = originalText;
                            if (window.Swal) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Cannot Enable POS',
                                    text: data.message || 'Validation failed for enabling POS.'
                                });
                            } else {
                                alert(data.message || 'Validation failed.');
                            }
                        }
                    })
                    .catch(err => {
                        this.disabled = false;
                        this.innerHTML = originalText;
                        alert('Server error occurred while toggling POS status.');
                    });
                });
            });
        })();
    </script>
@endpush
