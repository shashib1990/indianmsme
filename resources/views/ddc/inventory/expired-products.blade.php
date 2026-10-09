@extends('layouts.ddc')

@section('title', 'Expired Products')
@section('page-title', 'Expired Products')

@push('styles')
    <style>
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

        .inv-table thead tr th {
            background: #1e293b;
            color: #f8fafc;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .6px;
            padding: 11px 12px;
            border-bottom: 2px solid #334155;
            white-space: nowrap;
        }

        .inv-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background .15s ease;
        }

        .inv-table tbody tr:hover {
            background: #fff1f2;
        }

        .inv-table td {
            padding: 10px 12px;
            vertical-align: middle;
        }

        .badge-expired {
            background: #ffe4e6;
            color: #e11d48;
            border: 1px solid #fecdd3;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 6px;
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
    </style>
@endpush

@section('content')

    {{-- ── Page Header ── --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0 bg-danger-subtle border-start border-danger border-4">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-7">
                            <h4 class="fw-bold text-danger mb-1" style="font-family: 'Outfit', sans-serif;">
                                <iconify-icon icon="solar:danger-triangle-bold-duotone" class="align-middle me-1 fs-22"></iconify-icon>
                                Expired Products List
                            </h4>
                            <p class="text-slate-700 small mb-0">Products whose expiry date has passed have been automatically moved from active inventory to this quarantine list.</p>
                        </div>
                        <div class="col-md-5 text-md-end d-flex align-items-center justify-content-md-end gap-2">
                            <a href="{{ route('ddc.inventory.index') }}" class="btn btn-outline-primary btn-sm fw-semibold">
                                <iconify-icon icon="solar:box-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Back to Active Inventory
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Summary Metrics ── --}}
    <div class="row g-3 mb-3">
        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-12 fw-semibold text-uppercase">Total Expired Batches</span>
                            <h3 class="fw-bold text-danger mb-0 mt-1" style="font-family: 'Outfit', sans-serif;">{{ number_format($totalExpiredCount) }}</h3>
                        </div>
                        <div class="avatar-sm bg-danger-subtle text-danger rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <iconify-icon icon="solar:danger-bold-duotone" class="fs-24"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-12 fw-semibold text-uppercase">Expired Stock Quantity</span>
                            <h3 class="fw-bold text-warning-emphasis mb-0 mt-1" style="font-family: 'Outfit', sans-serif;">{{ number_format($totalExpiredQuantity, 2) }}</h3>
                        </div>
                        <div class="avatar-sm bg-warning-subtle text-warning-emphasis rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="fs-24"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-12 fw-semibold text-uppercase">Estimated Expired Value (MRP)</span>
                            <h3 class="fw-bold text-dark mb-0 mt-1" style="font-family: 'Outfit', sans-serif;">₹{{ number_format($totalExpiredValue, 2) }}</h3>
                        </div>
                        <div class="avatar-sm bg-secondary-subtle text-secondary rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <iconify-icon icon="solar:dollar-minimalistic-bold-duotone" class="fs-24"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Search & Filter Bar ── --}}
    <div class="row mb-3">
        <div class="col-12 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <form action="{{ route('ddc.inventory.expired-products') }}" method="GET" class="d-flex align-items-center gap-2 flex-grow-1">
                <input type="hidden" name="source" value="{{ $source }}">
                <div class="input-group" style="max-width: 420px;">
                    <span class="input-group-text bg-white border-end-0">
                        <iconify-icon icon="solar:magnifer-bold-duotone" class="text-muted"></iconify-icon>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0"
                        placeholder="Search product, barcode, code, batch..." value="{{ $search }}">
                </div>
                <button type="submit" class="btn btn-primary px-4">Search</button>
                @if($search || $source !== 'ALL')
                    <a href="{{ route('ddc.inventory.expired-products') }}" class="btn btn-outline-secondary">Reset</a>
                @endif
            </form>

            <div class="btn-group" role="group">
                <a href="{{ route('ddc.inventory.expired-products', ['search' => $search, 'source' => 'ALL']) }}" class="btn btn-sm {{ $source === 'ALL' ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold">
                    All Expired
                </a>
                <a href="{{ route('ddc.inventory.expired-products', ['search' => $search, 'source' => 'MSME']) }}" class="btn btn-sm {{ $source === 'MSME' ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold">
                    MSME Expired
                </a>
                <a href="{{ route('ddc.inventory.expired-products', ['search' => $search, 'source' => 'LOCAL']) }}" class="btn btn-sm {{ $source === 'LOCAL' ? 'btn-success' : 'btn-outline-success' }} fw-semibold">
                    Local Expired
                </a>
            </div>
        </div>
    </div>

    {{-- ── Expired Inventory Table ── --}}
    @if($expiredItems->count())
        <div class="inv-table-wrap">
            <div class="table-responsive">
                <table class="inv-table">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 4%;">#</th>
                            <th style="width: 25%;">Product Info &amp; Source</th>
                            <th style="width: 15%;">Barcode &amp; Batch</th>
                            <th style="width: 15%;">Expiry Status</th>
                            <th style="width: 12%;" class="text-center">Stock Quantity</th>
                            <th style="width: 14%;" class="text-end">MRP / Landing Cost</th>
                            <th style="width: 15%;" class="text-end">Total Expired Value</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($expiredItems as $index => $item)
                            @php
                                $expDate = $item->expire_date ? \Carbon\Carbon::parse($item->expire_date) : null;
                                $daysPast = $expDate ? floor(now()->diffInDays($expDate, false)) : 0;
                                $daysPastAbs = abs($daysPast);
                                $totalVal = (float) $item->quantity * (float) $item->mrp;
                            @endphp
                            <tr>
                                <td class="text-center fw-bold text-muted">{{ $expiredItems->firstItem() + $index }}</td>
                                <td>
                                    <div class="fw-bold text-slate-800 fs-13">{{ $item->product_name }}</div>
                                    <div class="d-flex align-items-center gap-1.5 mt-0.5">
                                        <span class="badge {{ $item->inventory_source === 'LOCAL' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-primary-subtle text-primary border border-primary-subtle' }} fs-10 px-2 py-0.5">
                                            {{ $item->inventory_source }}
                                        </span>
                                        <span class="text-muted fs-11">Code: {{ $item->product_code ?: '—' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="font-monospace fs-12 text-dark">{{ $item->barcode ?: '—' }}</div>
                                    @if($item->batch_number)
                                        <span class="badge bg-light text-slate-700 border fs-10 mt-0.5">Batch: {{ $item->batch_number }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="badge-expired">
                                        <iconify-icon icon="solar:calendar-minimalistic-bold-duotone"></iconify-icon>
                                        Expired: {{ $expDate ? $expDate->format('d M Y') : 'N/A' }}
                                    </div>
                                    <div class="text-danger fs-11 fw-semibold mt-1">
                                        {{ $daysPastAbs }} day{{ $daysPastAbs !== 1 ? 's' : '' }} ago
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-danger text-white fs-12 px-2.5 py-1 font-monospace fw-bold">
                                        {{ number_format($item->quantity, 2) }} {{ $item->unit_of_measure ?: 'Units' }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="fw-semibold text-dark fs-12">MRP: ₹{{ number_format($item->mrp, 2) }}</div>
                                    <div class="text-muted fs-11">Landing: ₹{{ number_format($item->landing_cost ?: $item->purchase_price, 2) }}</div>
                                </td>
                                <td class="text-end">
                                    <div class="fw-bold text-danger fs-13">₹{{ number_format($totalVal, 2) }}</div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-3">
            {{ $expiredItems->links() }}
        </div>
    @else
        <div class="card border-0 shadow-sm rounded-3 py-5 text-center">
            <div class="card-body">
                <div class="avatar-lg bg-success-subtle text-success rounded-circle mx-auto d-flex align-items-center justify-content-center mb-3" style="width: 60px; height: 60px;">
                    <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-36"></iconify-icon>
                </div>
                <h5 class="fw-bold text-slate-800 mb-1">No Expired Products</h5>
                <p class="text-muted small mb-0">Great job! There are currently no expired items in your DDC inventory.</p>
            </div>
        </div>
    @endif

@endsection
