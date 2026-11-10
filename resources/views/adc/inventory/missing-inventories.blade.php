@extends('layouts.adc')

@section('title', 'Missing Inventories - ADC POS')
@section('page-title', 'Missing Inventories List')

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

        .inv-table thead tr.thead-cols th {
            background: #f8fafd;
            color: #475569;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .55px;
            padding: 11px 12px;
            border-bottom: 2px solid #e8ecf4;
            white-space: nowrap;
        }

        .inv-table tbody tr {
            border-bottom: 1px solid #f1f5f9;
            transition: background .15s;
        }

        .inv-table tbody tr:hover {
            background: #f8fafc;
        }

        .inv-table td {
            padding: 10px 12px;
            vertical-align: middle;
        }

        .badge-source-msme {
            background: #eff6ff;
            color: #2563eb;
            border: 1px solid #bfdbfe;
        }

        .badge-source-local {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }

        .barcode-input-group {
            max-width: 270px;
        }

        .barcode-input-group input {
            font-family: monospace;
            font-weight: 700;
            letter-spacing: 0.5px;
        }

        /* Desktop View (≥ 768px): prevent table column squashing */
        @media (min-width: 768px) {
            .inv-table {
                min-width: 860px;
            }
        }

        /* Mobile View (≤ 767.98px): transform table into finger-friendly modern cards */
        @media (max-width: 767.98px) {
            .inv-table-wrap {
                background: transparent !important;
                border: none !important;
                box-shadow: none !important;
                overflow: visible !important;
            }

            .table-responsive {
                overflow: visible !important;
            }

            .inv-table,
            .inv-table tbody,
            .inv-table tr.inv-card-row {
                display: block !important;
                width: 100% !important;
            }

            .inv-table thead,
            .inv-table td.td-desktop-only {
                display: none !important;
            }

            .inv-table tbody tr.inv-card-row {
                background: #ffffff !important;
                border: 1px solid #e2e8f0 !important;
                border-radius: 12px !important;
                padding: 12px 14px !important;
                margin-bottom: 12px !important;
                box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05) !important;
            }

            .inv-table tbody tr.inv-card-row:hover {
                background: #ffffff !important;
            }

            .inv-table tr.inv-card-row td:not(.td-desktop-only) {
                display: block !important;
                width: 100% !important;
                padding: 0 !important;
                border: none !important;
            }

            .barcode-input-group {
                max-width: 100% !important;
                width: 100% !important;
            }

            .btn-save-row {
                width: auto !important;
            }

            .card-footer {
                background: #ffffff !important;
                border: 1px solid #e2e8f0 !important;
                border-radius: 12px !important;
                padding: 12px 14px !important;
                margin-top: 8px !important;
                margin-bottom: 8px !important;
                box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05) !important;
            }
        }

        /* Mobile Pagination Styling */
        .pagination-mobile-wrap .pagination {
            margin: 0;
            flex-wrap: nowrap !important;
        }

        .pagination-mobile-wrap .page-link {
            padding: 6px 9px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 6px;
            border: 1px solid #e2e8f0;
            color: #334155;
            background: #ffffff;
            line-height: 1;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 32px;
            min-height: 32px;
            text-decoration: none;
            transition: all 0.15s;
        }

        .pagination-mobile-wrap .page-item.active .page-link {
            background-color: #16a34a !important;
            border-color: #16a34a !important;
            color: #ffffff !important;
        }

        .pagination-mobile-wrap .page-item.disabled .page-link {
            color: #94a3b8 !important;
            background: #f8fafc !important;
            border-color: #e2e8f0 !important;
            opacity: 0.7;
        }

        .pagination-mobile-wrap .page-link:hover:not(.disabled) {
            background-color: #f1f5f9;
            color: #0f172a;
        }
    </style>
@endpush

@section('content')
    <!-- Header Navigation & Summary Bar -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body py-3 px-4">
                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                        <div>
                            <h4 class="fw-bold text-slate-800 mb-1 d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:barcode-bold-duotone" class="text-warning fs-26"></iconify-icon>
                                Missing Inventories (No Barcode)
                            </h4>
                            <p class="text-muted small mb-0">
                                Stock products in ADC Inventory missing a barcode. Enter or scan barcodes inline to enable fast POS billing.
                            </p>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <a href="{{ route('adc.inventory.index') }}" class="btn btn-outline-secondary btn-sm fw-semibold">
                                <iconify-icon icon="solar:box-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> All Inventory
                            </a>
                            <a href="{{ route('adc.pos.retail') }}" class="btn btn-warning btn-sm fw-semibold text-dark">
                                <iconify-icon icon="solar:cart-large-4-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> POS Retail Billing
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="row g-3 mb-3">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold d-block">Total Missing Barcodes</span>
                        <h3 class="fw-bold text-slate-800 mb-0 mt-1">{{ number_format($summaryStats['total_missing']) }}</h3>
                    </div>
                    <div class="avatar bg-warning-subtle text-warning rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <iconify-icon icon="solar:danger-triangle-bold" class="fs-22"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold d-block">MSME Products (No Barcode)</span>
                        <h3 class="fw-bold text-primary mb-0 mt-1">{{ number_format($summaryStats['msme_missing']) }}</h3>
                    </div>
                    <div class="avatar bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <iconify-icon icon="solar:buildings-bold-duotone" class="fs-22"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-body p-3 d-flex align-items-center justify-content-between">
                    <div>
                        <span class="text-muted small fw-semibold d-block">Local Products (No Barcode)</span>
                        <h3 class="fw-bold text-success mb-0 mt-1">{{ number_format($summaryStats['local_missing']) }}</h3>
                    </div>
                    <div class="avatar bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                        <iconify-icon icon="solar:shop-bold-duotone" class="fs-22"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Search Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body py-3 px-3">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3 mb-3 pb-3 border-bottom">
                <div class="fw-bold text-dark fs-13 d-flex align-items-center gap-1">
                    <iconify-icon icon="solar:filter-bold-duotone" class="text-primary fs-18 me-1"></iconify-icon> Filter by Product Type:
                </div>
                <div class="d-flex flex-wrap gap-2" role="group" aria-label="Source Filter">
                    <a href="{{ route('adc.inventory.missing', array_merge(request()->query(), ['source' => 'ALL'])) }}"
                        class="btn btn-sm {{ $source === 'ALL' ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold rounded-2 px-3">
                        <iconify-icon icon="solar:widget-bold-duotone" class="align-middle me-1"></iconify-icon> All ({{ $summaryStats['total_missing'] }})
                    </a>
                    <a href="{{ route('adc.inventory.missing', array_merge(request()->query(), ['source' => 'MSME'])) }}"
                        class="btn btn-sm {{ $source === 'MSME' ? 'btn-primary' : 'btn-outline-primary' }} fw-semibold rounded-2 px-3">
                        <iconify-icon icon="solar:buildings-bold-duotone" class="align-middle me-1"></iconify-icon> MSME ({{ $summaryStats['msme_missing'] }})
                    </a>
                    <a href="{{ route('adc.inventory.missing', array_merge(request()->query(), ['source' => 'LOCAL'])) }}"
                        class="btn btn-sm {{ $source === 'LOCAL' ? 'btn-success' : 'btn-outline-success' }} fw-semibold rounded-2 px-3">
                        <iconify-icon icon="solar:shop-bold-duotone" class="align-middle me-1"></iconify-icon> Local ({{ $summaryStats['local_missing'] }})
                    </a>
                </div>
            </div>

            <form method="GET" action="{{ route('adc.inventory.missing') }}" class="row g-2 align-items-center">
                <input type="hidden" name="source" value="{{ $source }}">
                <div class="col-12 col-md-9">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0">
                            <iconify-icon icon="solar:magnifer-linear" class="fs-16 text-muted"></iconify-icon>
                        </span>
                        <input type="text" name="search" class="form-control form-control-sm border-start-0"
                            placeholder="Search by Product Name, Product Code, Batch Number..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-12 col-md-3 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                        <iconify-icon icon="solar:magnifer-linear" class="align-middle me-1"></iconify-icon> Filter
                    </button>
                    <a href="{{ route('adc.inventory.missing', ['source' => $source]) }}" class="btn btn-light btn-sm px-2.5" title="Reset Search">
                        <iconify-icon icon="solar:restart-bold-duotone" class="align-middle"></iconify-icon>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Missing Barcodes Table -->
    <form method="POST" action="{{ route('adc.inventory.bulk-update-barcodes') }}">
        @csrf
        <div class="card border-0 shadow-sm rounded-3 mb-3">
            <div class="card-header bg-white py-3 px-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="fw-bold text-dark fs-13 d-flex align-items-center gap-1">
                    <iconify-icon icon="solar:list-bold-duotone" class="text-warning fs-18 me-1"></iconify-icon>
                    Missing Inventories List
                    <span class="badge bg-warning-subtle text-warning px-2 py-0.5 ms-1">{{ $inventories->total() }} items</span>
                </div>
                @if($inventories->count() > 0)
                    <button type="submit" class="btn btn-success btn-sm fw-semibold px-3 text-nowrap">
                        <iconify-icon icon="solar:diskette-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Save All Entered Barcodes
                    </button>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="inv-table">
                        <thead>
                            <tr class="thead-cols d-none d-md-table-row">
                                <th style="width: 5%;" class="text-center">S.No</th>
                                <th style="width: 25%;">Product Name</th>
                                <th style="width: 12%;">Product Code</th>
                                <th style="width: 12%;">Type</th>
                                <th style="width: 8%;" class="text-center">Stock</th>
                                <th style="width: 8%;" class="text-end">MRP</th>
                                <th style="width: 22%;">Barcode</th>
                                <th style="width: 8%;" class="text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($inventories as $idx => $inv)
                                <tr class="inv-card-row">
                                    <!-- Desktop Only S.No -->
                                    <td class="text-center text-muted fw-semibold fs-12 td-desktop-only td-sno">
                                        {{ $inventories->firstItem() + $idx }}
                                    </td>

                                    <!-- Product Info (Mobile Card Header + Product Title + Details) -->
                                    <td class="td-product">
                                        <!-- Mobile Card Top Bar (S.No, Type Badge, Price) -->
                                        <div class="d-md-none d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                            <div class="d-flex align-items-center gap-1.5">
                                                <span class="badge bg-light text-secondary border fw-bold px-2 py-0.5 fs-11">
                                                    #{{ $inventories->firstItem() + $idx }}
                                                </span>
                                                @if($inv->inventory_source === 'LOCAL' || $inv->adc_local_product_id)
                                                    <span class="badge badge-source-local px-2 py-0.5 fs-11 fw-bold">
                                                        <iconify-icon icon="solar:shop-bold" class="align-middle me-0.5"></iconify-icon> Local
                                                    </span>
                                                @else
                                                    <span class="badge badge-source-msme px-2 py-0.5 fs-11 fw-bold">
                                                        <iconify-icon icon="solar:buildings-bold" class="align-middle me-0.5"></iconify-icon> MSME
                                                    </span>
                                                @endif
                                            </div>
                                            <div class="text-end">
                                                <span class="text-muted fs-11 me-1">MRP:</span>
                                                <span class="fw-bold text-success fs-13">₹{{ number_format($inv->mrp, 2) }}</span>
                                            </div>
                                        </div>

                                        <!-- Product Name -->
                                        <div class="fw-bold text-dark fs-13 mb-0.5">{{ $inv->product_name }}</div>

                                        <!-- Desktop Batch -->
                                        @if($inv->batch_number)
                                            <div class="fs-11 text-muted d-none d-md-block">Batch: <code>{{ $inv->batch_number }}</code></div>
                                        @endif

                                        <!-- Mobile Details Strip (Code, Batch, Stock) -->
                                        <div class="d-md-none d-flex flex-wrap align-items-center gap-x-2 gap-y-1 fs-11 text-muted mt-1 mb-2">
                                            @if($inv->product_code)
                                                <span>Code: <code class="fw-bold text-primary">{{ $inv->product_code }}</code></span>
                                            @endif
                                            @if($inv->batch_number)
                                                <span>• Batch: <code>{{ $inv->batch_number }}</code></span>
                                            @endif
                                            <span>• Stock: <strong class="text-dark">{{ number_format($inv->quantity, 2) }} {{ $inv->unit_of_measure ?: 'Units' }}</strong></span>
                                        </div>
                                    </td>

                                    <!-- Desktop Only Product Code -->
                                    <td class="td-desktop-only td-code">
                                        <code class="fw-bold text-primary fs-12">{{ $inv->product_code ?: '—' }}</code>
                                    </td>

                                    <!-- Desktop Only Type -->
                                    <td class="td-desktop-only td-type">
                                        @if($inv->inventory_source === 'LOCAL' || $inv->adc_local_product_id)
                                            <span class="badge badge-source-local px-2 py-1 fs-11 fw-bold">
                                                <iconify-icon icon="solar:shop-bold" class="align-middle me-0.5"></iconify-icon> Local Product
                                            </span>
                                        @else
                                            <span class="badge badge-source-msme px-2 py-1 fs-11 fw-bold">
                                                <iconify-icon icon="solar:buildings-bold" class="align-middle me-0.5"></iconify-icon> MSME Product
                                            </span>
                                        @endif
                                    </td>

                                    <!-- Desktop Only Stock -->
                                    <td class="text-center fw-bold text-dark fs-13 td-desktop-only td-stock">
                                        {{ number_format($inv->quantity, 2) }}
                                        <span class="fs-11 text-muted fw-normal d-block">{{ $inv->unit_of_measure ?: 'Units' }}</span>
                                    </td>

                                    <!-- Desktop Only MRP -->
                                    <td class="text-end fw-semibold text-dark fs-13 td-desktop-only td-mrp">
                                        ₹{{ number_format($inv->mrp, 2) }}
                                    </td>

                                    <!-- Barcode Input Group (Visible on Desktop & Mobile) -->
                                    <td class="td-barcode pt-1 pt-md-0">
                                        <div class="d-md-none small fw-semibold text-muted mb-1 fs-11">
                                            <iconify-icon icon="solar:barcode-bold" class="align-middle me-0.5 text-primary"></iconify-icon> Barcode:
                                        </div>
                                        <div class="input-group input-group-sm barcode-input-group">
                                            <span class="input-group-text bg-light text-muted px-2" title="Barcode">
                                                <iconify-icon icon="solar:barcode-bold-duotone" class="fs-16"></iconify-icon>
                                            </span>
                                            <input type="text" name="barcodes[{{ $inv->id }}]" id="barcode-input-{{ $inv->id }}"
                                                class="form-control form-control-sm border-start-0 text-dark fw-bold font-monospace missing-barcode-input"
                                                placeholder="Scan or enter Barcode..."
                                                value="{{ old('barcodes.'.$inv->id, ($inv->barcode && !in_array(strtoupper(trim($inv->barcode)), ['N/A', 'NA', '—', '-', '0', 'NULL']) ? $inv->barcode : '')) }}"
                                                onkeypress="if(event.key === 'Enter'){ event.preventDefault(); quickSaveBarcode({{ $inv->id }}, this); }">
                                            <button type="button" class="btn btn-outline-primary px-2" title="Auto-Generate Barcode" onclick="generateRowBarcode({{ $inv->id }})">
                                                <iconify-icon icon="solar:magic-stick-3-bold-duotone" class="fs-15"></iconify-icon>
                                            </button>
                                            <button type="button" class="btn btn-outline-secondary px-2" title="View & Print Barcode Label"
                                                data-bs-toggle="modal" data-bs-target="#barcodePreviewModal"
                                                data-id="{{ $inv->id }}"
                                                data-name="{{ $inv->product_name }}"
                                                data-mrp="{{ $inv->mrp }}"
                                                onclick="openBarcodeModalFromBtn(this)">
                                                <iconify-icon icon="solar:eye-bold-duotone" class="fs-15"></iconify-icon>
                                            </button>
                                        </div>
                                    </td>

                                    <!-- Action Button (Visible on Desktop & Mobile) -->
                                    <td class="text-center td-action pt-2 pt-md-0">
                                        <div class="d-flex justify-content-end align-items-center d-md-block">
                                            <button type="button" class="btn btn-primary btn-sm fw-semibold px-2.5 py-1 btn-save-row text-nowrap" onclick="quickSaveBarcode({{ $inv->id }}, this)">
                                                <iconify-icon icon="solar:diskette-bold-duotone" class="align-middle me-1"></iconify-icon> Save
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5">
                                        <div class="text-muted">
                                            <iconify-icon icon="solar:check-read-bold-duotone" class="fs-48 text-success opacity-75 mb-2"></iconify-icon>
                                            <h6 class="fw-bold text-dark mb-1">Great! No Missing Inventories</h6>
                                            <p class="fs-12 mb-0">All products in ADC Inventory have barcodes assigned for POS.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($inventories->hasPages())
                <div class="card-footer bg-white border-top py-2.5 px-3">
                    {{-- Desktop Pagination --}}
                    <div class="d-none d-md-flex align-items-center justify-content-between">
                        <div class="text-muted small">
                            Showing <strong>{{ $inventories->firstItem() }}</strong> to <strong>{{ $inventories->lastItem() }}</strong> of <strong>{{ number_format($inventories->total()) }}</strong> entries
                        </div>
                        <div>
                            {{ $inventories->onEachSide(1)->links() }}
                        </div>
                    </div>

                    {{-- Mobile Pagination: 1, 2, 3 ... last (Single compact line) --}}
                    @php
                        $cur = $inventories->currentPage();
                        $last = $inventories->lastPage();
                        $mobilePages = [];

                        if ($last <= 5) {
                            for ($i = 1; $i <= $last; $i++) {
                                $mobilePages[] = $i;
                            }
                        } else {
                            if ($cur <= 2) {
                                $mobilePages = [1, 2, 3, '...', $last];
                            } elseif ($cur == 3) {
                                $mobilePages = [1, 2, 3, 4, '...', $last];
                            } elseif ($cur >= $last - 1) {
                                $mobilePages = [1, '...', $last - 2, $last - 1, $last];
                            } elseif ($cur == $last - 2) {
                                $mobilePages = [1, '...', $last - 3, $last - 2, $last - 1, $last];
                            } else {
                                $mobilePages = [1, '...', $cur - 1, $cur, $cur + 1, '...', $last];
                            }
                        }
                    @endphp

                    <div class="d-md-none pagination-mobile-wrap d-flex flex-column align-items-center gap-1.5 py-1">
                        <div class="text-muted fs-11">
                            Showing {{ $inventories->firstItem() }}-{{ $inventories->lastItem() }} of {{ $inventories->total() }} items (Page {{ $cur }}/{{ $last }})
                        </div>
                        <nav aria-label="Mobile Pagination">
                            <ul class="pagination pagination-sm mb-0 d-flex justify-content-center align-items-center flex-nowrap gap-1">
                                {{-- Prev Button --}}
                                @if ($inventories->onFirstPage())
                                    <li class="page-item disabled" aria-disabled="true">
                                        <span class="page-link px-2">‹ Prev</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <a class="page-link px-2" href="{{ $inventories->previousPageUrl() }}" rel="prev">‹ Prev</a>
                                    </li>
                                @endif

                                {{-- Page Links --}}
                                @foreach ($mobilePages as $p)
                                    @if ($p === '...')
                                        <li class="page-item disabled" aria-disabled="true">
                                            <span class="page-link border-0 bg-transparent text-muted px-1">…</span>
                                        </li>
                                    @elseif ($p == $cur)
                                        <li class="page-item active" aria-current="page">
                                            <span class="page-link">{{ $p }}</span>
                                        </li>
                                    @else
                                        <li class="page-item">
                                            <a class="page-link" href="{{ $inventories->url($p) }}">{{ $p }}</a>
                                        </li>
                                    @endif
                                @endforeach

                                {{-- Next Button --}}
                                @if ($inventories->hasMorePages())
                                    <li class="page-item">
                                        <a class="page-link px-2" href="{{ $inventories->nextPageUrl() }}" rel="next">Next ›</a>
                                    </li>
                                @else
                                    <li class="page-item disabled" aria-disabled="true">
                                        <span class="page-link px-2">Next ›</span>
                                    </li>
                                @endif
                            </ul>
                        </nav>
                    </div>
                </div>
            @endif
        </div>
    </form>
    <!-- Barcode View & Label Print Modal -->
    <div class="modal fade" id="barcodePreviewModal" tabindex="-1" aria-labelledby="barcodePreviewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header bg-light py-3 border-bottom">
                    <h5 class="modal-title fw-bold text-slate-800 d-flex align-items-center gap-2" id="barcodePreviewModalLabel">
                        <iconify-icon icon="solar:barcode-bold-duotone" class="text-primary fs-22"></iconify-icon>
                        Barcode View & Download
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <input type="hidden" id="modal_inv_id" value="">
                    
                    <!-- Product Info Box -->
                    <div class="p-2.5 rounded-3 bg-light border mb-3">
                        <div class="fw-bold text-dark fs-13" id="modal_prod_name">Product Name</div>
                        <div class="d-flex align-items-center justify-content-between text-muted fs-11 mt-1">
                            <span>MRP: <strong class="text-dark fs-12" id="modal_prod_mrp">₹0.00</strong></span>
                            <span class="badge bg-secondary-subtle text-secondary px-2 py-0.5">ADC POS Inventory</span>
                        </div>
                    </div>

                    <!-- Format & Generation Row -->
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-1 mb-1">
                            <label class="form-label fw-bold text-slate-700 fs-12 mb-0">Barcode Format & Number</label>
                            <div class="d-flex align-items-center gap-1">
                                <span class="fs-11 text-muted">Format:</span>
                                <select id="modal_barcode_format" class="form-select form-select-sm py-0.5 px-2 text-dark fw-bold border-primary-subtle" style="width: 155px; font-size: 11px;">
                                    <option value="EAN13" selected>EAN-13 (Standard Retail)</option>
                                    <option value="CODE128">Code 128 (Alpha-Numeric)</option>
                                    <option value="EAN8">EAN-8 (8 Digits)</option>
                                </select>
                            </div>
                        </div>
                        <div class="input-group">
                            <input type="text" id="modal_barcode_input" class="form-control fw-bold font-monospace text-dark" placeholder="Enter barcode number...">
                            <button type="button" class="btn btn-outline-primary fw-semibold px-2.5" id="btn_modal_generate" onclick="modalGenerateBarcode()">
                                <iconify-icon icon="solar:magic-stick-3-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Generate
                            </button>
                        </div>
                        <div id="modal_barcode_feedback" class="mt-1 small fw-semibold"></div>
                    </div>

                    <!-- Label Visual Preview (Exact high contrast EAN-13 preview) -->
                    <div class="text-center p-3 rounded-3 border shadow-xs mb-3" style="background: #ffffff !important;">
                        <div class="fw-bold text-dark fs-13 text-truncate mb-2" id="modal_label_title" style="max-width: 320px; margin: 0 auto;"></div>
                        <div class="d-flex justify-content-center align-items-center py-2" style="background: #ffffff; min-height: 130px;">
                            <svg id="modal_barcode_svg" style="max-width: 100%; height: auto;"></svg>
                        </div>
                        <canvas id="modal_barcode_canvas" class="d-none"></canvas>
                        <div class="fs-12 text-muted mt-2 fw-semibold" id="modal_label_price"></div>
                    </div>

                    <!-- Download & Print Actions -->
                    <div class="row g-2">
                        <div class="col-12 col-sm-7">
                            <button type="button" class="btn btn-success fw-bold w-100 py-2 d-flex align-items-center justify-content-center gap-1.5 shadow-sm" onclick="modalDownloadPNG()">
                                <iconify-icon icon="solar:download-square-bold" class="fs-18"></iconify-icon> Download Barcode (PNG)
                            </button>
                        </div>
                        <div class="col-12 col-sm-5">
                            <button type="button" class="btn btn-outline-dark fw-semibold w-100 py-2 d-flex align-items-center justify-content-center gap-1.5" onclick="modalPrintLabel()">
                                <iconify-icon icon="solar:printer-bold" class="fs-18"></iconify-icon> Print Sticker
                            </button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-3 border-top d-flex justify-content-between">
                    <button type="button" class="btn btn-light btn-sm fw-semibold" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-primary btn-sm fw-semibold px-3" id="btn_modal_save" onclick="modalSaveBarcode()">
                        <iconify-icon icon="solar:diskette-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Save to Product
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let currentModalInvId = null;
        let currentModalProdName = '';
        let currentModalMrp = '';
        let modalCheckTimeout = null;

        function showNotification(type, message) {
            if (typeof Toastify === 'function') {
                Toastify({
                    text: message,
                    duration: 3500,
                    gravity: 'top',
                    position: 'right',
                    backgroundColor: type === 'success' ? '#10b981' : '#ef4444',
                    close: true
                }).showToast();
            } else if (window.Swal) {
                Swal.fire({
                    icon: type,
                    title: type === 'success' ? 'Success' : 'Notice',
                    text: message,
                    timer: 2500,
                    showConfirmButton: false
                });
            } else {
                alert(message);
            }
        }

        function generateRowBarcode(invId) {
            const inputElem = document.getElementById('barcode-input-' + invId);
            if (!inputElem) return;

            const trRow = inputElem.closest('tr');
            const genBtn = trRow ? trRow.querySelector('button[title*="Auto-Generate"]') : null;
            if (genBtn) {
                genBtn.disabled = true;
                genBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
            }

            fetch("{{ route('adc.barcode.generate') }}?format=EAN13", {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (genBtn) {
                    genBtn.disabled = false;
                    genBtn.innerHTML = '<iconify-icon icon="solar:magic-stick-3-bold-duotone" class="fs-15"></iconify-icon>';
                }
                if (data.success && data.barcode) {
                    inputElem.value = data.barcode;
                    inputElem.style.backgroundColor = '#ecfdf5';
                    setTimeout(() => { inputElem.style.backgroundColor = ''; }, 1000);
                }
            })
            .catch(err => {
                if (genBtn) {
                    genBtn.disabled = false;
                    genBtn.innerHTML = '<iconify-icon icon="solar:magic-stick-3-bold-duotone" class="fs-15"></iconify-icon>';
                }
                console.error('Row generate barcode error:', err);
            });
        }

        function openBarcodeModalFromBtn(btn) {
            const invId = btn.getAttribute('data-id');
            const prodName = btn.getAttribute('data-name') || '';
            const mrp = btn.getAttribute('data-mrp') || '0';
            openBarcodeModal(invId, prodName, mrp);
        }

        function openBarcodeModal(invId, prodName, mrp) {
            currentModalInvId = invId;
            currentModalProdName = prodName;
            currentModalMrp = mrp;

            const modalInvId = document.getElementById('modal_inv_id');
            const modalProdName = document.getElementById('modal_prod_name');
            const modalProdMrp = document.getElementById('modal_prod_mrp');
            const modalLabelTitle = document.getElementById('modal_label_title');
            const modalLabelPrice = document.getElementById('modal_label_price');

            if (modalInvId) modalInvId.value = invId;
            if (modalProdName) modalProdName.textContent = prodName;
            if (modalProdMrp) modalProdMrp.textContent = '₹' + parseFloat(mrp || 0).toFixed(2);
            if (modalLabelTitle) modalLabelTitle.textContent = prodName;
            if (modalLabelPrice) modalLabelPrice.textContent = 'MRP: ₹' + parseFloat(mrp || 0).toFixed(2);

            const rowInput = document.getElementById('barcode-input-' + invId);
            let val = rowInput ? rowInput.value.trim() : '';

            const modalInput = document.getElementById('modal_barcode_input');
            if (modalInput) modalInput.value = val;

            const modalFeedback = document.getElementById('modal_barcode_feedback');
            if (modalFeedback) modalFeedback.innerHTML = '';

            const modalEl = document.getElementById('barcodePreviewModal');
            if (modalEl) {
                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    const inst = bootstrap.Modal.getOrCreateInstance ? bootstrap.Modal.getOrCreateInstance(modalEl) : new bootstrap.Modal(modalEl);
                    inst.show();
                } else if (typeof $ !== 'undefined' && $('#barcodePreviewModal').modal) {
                    $('#barcodePreviewModal').modal('show');
                }
            }

            if (val) {
                modalRenderBarcode();
            } else {
                modalGenerateBarcode();
            }
        }

        function modalRenderBarcode() {
            const inputEl = document.getElementById('modal_barcode_input');
            const formatEl = document.getElementById('modal_barcode_format');
            const rawVal = inputEl ? inputEl.value.trim() : '';
            const format = formatEl ? formatEl.value : 'EAN13';
            if (!rawVal) return;

            const cleanVal = rawVal.replace(/\s+/g, '');
            let jsFmt = format;

            // Intelligent format detection for EAN-13, EAN-8 and Code 128
            if (/^\d{12,13}$/.test(cleanVal)) {
                jsFmt = 'EAN13';
            } else if (/^\d{7,8}$/.test(cleanVal)) {
                jsFmt = 'EAN8';
            } else if (format === 'EAN13' && !/^\d+$/.test(cleanVal)) {
                jsFmt = 'CODE128';
            }

            try {
                if (typeof JsBarcode === 'function') {
                    // 1. High contrast SVG preview (standard EAN-13 retail barcode look)
                    JsBarcode('#modal_barcode_svg', cleanVal, {
                        format: jsFmt,
                        lineColor: '#000000',
                        width: 2.6,
                        height: 95,
                        displayValue: true,
                        font: 'Arial, Helvetica, sans-serif',
                        fontSize: 18,
                        textMargin: 8,
                        background: '#ffffff',
                        margin: 10
                    });

                    // 2. High-resolution canvas for PNG download
                    JsBarcode('#modal_barcode_canvas', cleanVal, {
                        format: jsFmt,
                        lineColor: '#000000',
                        width: 3.5,
                        height: 130,
                        displayValue: true,
                        font: 'Arial, Helvetica, sans-serif',
                        fontSize: 22,
                        textMargin: 10,
                        background: '#ffffff',
                        margin: 25
                    });
                }
            } catch (e) {
                console.warn('Modal JsBarcode format fallback:', e);
                // Safe fallback to Code 128 if checksum is invalid
                try {
                    JsBarcode('#modal_barcode_svg', cleanVal, {
                        format: 'CODE128',
                        lineColor: '#000000',
                        width: 2.2,
                        height: 80,
                        displayValue: true,
                        fontSize: 16,
                        background: '#ffffff',
                        margin: 10
                    });
                    JsBarcode('#modal_barcode_canvas', cleanVal, {
                        format: 'CODE128',
                        lineColor: '#000000',
                        width: 3.2,
                        height: 110,
                        displayValue: true,
                        fontSize: 20,
                        background: '#ffffff',
                        margin: 20
                    });
                } catch (err2) {
                    console.error('JsBarcode fallback error:', err2);
                }
            }
        }

        function modalGenerateBarcode() {
            const formatEl = document.getElementById('modal_barcode_format');
            const format = formatEl ? formatEl.value : 'EAN13';
            const btn = document.getElementById('btn_modal_generate');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Generating...';
            }

            fetch("{{ route('adc.barcode.generate') }}?format=" + encodeURIComponent(format), {
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<iconify-icon icon="solar:magic-stick-3-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Generate';
                }
                if (data.success && data.barcode) {
                    const input = document.getElementById('modal_barcode_input');
                    if (input) {
                        input.value = data.barcode;
                        modalRenderBarcode();
                        modalValidateBarcode();
                    }
                }
            })
            .catch(err => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<iconify-icon icon="solar:magic-stick-3-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Generate';
                }
                console.error('Modal generate error:', err);
            });
        }

        function modalValidateBarcode() {
            const inputEl = document.getElementById('modal_barcode_input');
            const formatEl = document.getElementById('modal_barcode_format');
            const code = inputEl ? inputEl.value.trim() : '';
            const format = formatEl ? formatEl.value : 'EAN13';
            const feedback = document.getElementById('modal_barcode_feedback');

            if (!code) {
                if (feedback) feedback.innerHTML = '';
                return;
            }

            if (feedback) {
                feedback.className = 'mt-1 small fw-semibold text-muted d-flex align-items-center gap-1';
                feedback.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Validating barcode...';
            }

            fetch("{{ route('adc.barcode.check-duplicate') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    barcode: code,
                    format: format,
                    exclude_id: currentModalInvId,
                    type: 'inventory'
                })
            })
            .then(r => r.json())
            .then(data => {
                if (!feedback) return;
                if (data.valid) {
                    feedback.className = 'mt-1 small fw-semibold text-success d-flex align-items-center gap-1';
                    feedback.innerHTML = '<iconify-icon icon="solar:check-circle-bold" class="fs-15"></iconify-icon> ' + data.message;
                } else {
                    feedback.className = 'mt-1 small fw-semibold text-danger d-flex align-items-center gap-1';
                    feedback.innerHTML = '<iconify-icon icon="solar:danger-triangle-bold" class="fs-15"></iconify-icon> ' + data.message;
                }
            })
            .catch(err => {
                console.error('Modal validate error:', err);
            });
        }

        document.getElementById('modal_barcode_input')?.addEventListener('input', function () {
            modalRenderBarcode();
            clearTimeout(modalCheckTimeout);
            modalCheckTimeout = setTimeout(modalValidateBarcode, 400);
        });

        document.getElementById('modal_barcode_format')?.addEventListener('change', function () {
            modalRenderBarcode();
            modalValidateBarcode();
        });

        function modalDownloadPNG() {
            const inputElem = document.getElementById('modal_barcode_input');
            const code = (inputElem ? inputElem.value.trim() : '') || 'barcode';
            const canvas = document.getElementById('modal_barcode_canvas');
            if (!canvas) {
                alert('No barcode image available to download.');
                return;
            }
            const a = document.createElement('a');
            a.download = 'barcode-' + code + '.png';
            a.href = canvas.toDataURL('image/png');
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
        }

        function modalPrintLabel() {
            const inputEl = document.getElementById('modal_barcode_input');
            const code = inputEl ? inputEl.value.trim() : '';
            const canvas = document.getElementById('modal_barcode_canvas');
            if (!canvas || !code) {
                alert('Please enter or generate a barcode first.');
                return;
            }
            const dataUrl = canvas.toDataURL('image/png');
            const prodName = currentModalProdName || 'Product';
            const mrpText = currentModalMrp ? ('MRP: ₹' + parseFloat(currentModalMrp).toFixed(2)) : '';

            const printWin = window.open('', '_blank', 'width=450,height=380');
            printWin.document.write(`
                <!DOCTYPE html>
                <html>
                <head>
                    <title>Print Barcode Label - ${code}</title>
                    <style>
                        @page { size: 50mm 25mm; margin: 0; }
                        body {
                            margin: 0;
                            padding: 4px;
                            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                            text-align: center;
                            display: flex;
                            flex-direction: column;
                            justify-content: center;
                            align-items: center;
                            height: 100vh;
                            box-sizing: border-box;
                        }
                        .title {
                            font-size: 11px;
                            font-weight: bold;
                            max-width: 175px;
                            white-space: nowrap;
                            overflow: hidden;
                            text-overflow: ellipsis;
                            margin-bottom: 2px;
                        }
                        img {
                            max-width: 175px;
                            max-height: 60px;
                            object-fit: contain;
                        }
                        .price {
                            font-size: 10px;
                            font-weight: bold;
                            margin-top: 1px;
                        }
                    </style>
                </head>
                <body onload="window.print(); window.close();">
                    <div class="title">${prodName}</div>
                    <img src="${dataUrl}">
                    ${mrpText ? `<div class="price">${mrpText}</div>` : ''}
                </body>
                </html>
            `);
            printWin.document.close();
        }

        function modalSaveBarcode() {
            const invId = currentModalInvId;
            const inputEl = document.getElementById('modal_barcode_input');
            const formatEl = document.getElementById('modal_barcode_format');
            const code = inputEl ? inputEl.value.trim() : '';
            const format = formatEl ? formatEl.value : 'EAN13';
            const btn = document.getElementById('btn_modal_save');

            if (!code) {
                alert('Please enter or generate a barcode first.');
                return;
            }

            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving...';
            }

            const updateUrl = "{{ route('adc.inventory.update-barcode', ['id' => ':id']) }}".replace(':id', invId);

            fetch(updateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    barcode: code,
                    format: format
                })
            })
            .then(res => res.json())
            .then(response => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<iconify-icon icon="solar:diskette-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Save to Product';
                }

                if (response.success) {
                    showNotification('success', response.message || 'Barcode saved successfully!');

                    // Update table row input
                    const rowInput = document.getElementById('barcode-input-' + invId);
                    if (rowInput) {
                        rowInput.value = code;
                        const trRow = rowInput.closest('tr');
                        if (trRow) {
                            trRow.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                            trRow.style.backgroundColor = '#dcfce7';
                            trRow.style.opacity = '0';
                            trRow.style.transform = 'translateX(30px)';
                            setTimeout(function() {
                                trRow.remove();
                            }, 550);
                        }
                    }

                    // Close modal
                    const modalEl = document.getElementById('barcodePreviewModal');
                    if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                        const modalInstance = bootstrap.Modal.getInstance(modalEl);
                        if (modalInstance) modalInstance.hide();
                    } else if (typeof $ !== 'undefined' && $('#barcodePreviewModal').modal) {
                        $('#barcodePreviewModal').modal('hide');
                    }
                } else {
                    showNotification('error', response.message || 'Failed to save barcode.');
                }
            })
            .catch(err => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = '<iconify-icon icon="solar:diskette-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Save to Product';
                }
                console.error('Modal save error:', err);
                showNotification('error', 'An error occurred while saving barcode.');
            });
        }

        function quickSaveBarcode(invId, btnElem) {
            const inputElem = document.getElementById('barcode-input-' + invId);
            const barcodeVal = inputElem ? inputElem.value.trim() : '';

            if (!barcodeVal) {
                showNotification('warning', 'Please enter or scan a valid Barcode number first.');
                if (inputElem) inputElem.focus();
                return;
            }

            let targetBtn = null;
            if (btnElem && btnElem.tagName === 'BUTTON') {
                targetBtn = btnElem;
            } else {
                const trRow = inputElem ? inputElem.closest('tr') : null;
                if (trRow) targetBtn = trRow.querySelector('.btn-save-row') || trRow.querySelector('button');
            }

            if (targetBtn) {
                targetBtn.disabled = true;
                targetBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving...';
            }

            const updateUrl = "{{ route('adc.inventory.update-barcode', ['id' => ':id']) }}".replace(':id', invId);

            fetch(updateUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    barcode: barcodeVal
                })
            })
            .then(res => res.json())
            .then(response => {
                if (response.success) {
                    showNotification('success', response.message || 'Barcode saved successfully!');

                    if (targetBtn) {
                        targetBtn.className = 'btn btn-success btn-sm fw-semibold px-2.5 py-1';
                        targetBtn.innerHTML = '<iconify-icon icon="solar:check-circle-bold" class="align-middle me-1"></iconify-icon> Saved';
                    }

                    // Animate and remove row from missing list
                    const trRow = inputElem ? inputElem.closest('tr') : null;
                    if (trRow) {
                        trRow.style.transition = 'all 0.5s cubic-bezier(0.4, 0, 0.2, 1)';
                        trRow.style.backgroundColor = '#dcfce7';
                        trRow.style.opacity = '0';
                        trRow.style.transform = 'translateX(30px)';
                        setTimeout(function() {
                            trRow.remove();
                        }, 550);
                    }
                } else {
                    showNotification('error', response.message || "Failed to update barcode.");
                    if (targetBtn) {
                        targetBtn.disabled = false;
                        targetBtn.innerHTML = '<iconify-icon icon="solar:diskette-bold-duotone" class="align-middle me-1"></iconify-icon> Save';
                    }
                }
            })
            .catch(error => {
                console.error('Save Barcode Error:', error);
                showNotification('error', "An error occurred while updating barcode.");
                if (targetBtn) {
                    targetBtn.disabled = false;
                    targetBtn.innerHTML = '<iconify-icon icon="solar:diskette-bold-duotone" class="align-middle me-1"></iconify-icon> Save';
                }
            });
        }
    </script>
@endpush

