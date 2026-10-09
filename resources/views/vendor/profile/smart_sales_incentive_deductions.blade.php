@extends('layouts.vendor')

@section('title', 'Smart Sales Incentive (SSI) — Deductions Log')
@section('page-title', 'Smart Sales Incentive Deductions')

@section('content')
    <!-- Header Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-7">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                <iconify-icon icon="solar:ticket-sale-bold-duotone" class="text-primary align-middle me-1 fs-24"></iconify-icon>
                                Smart Sales Incentive (SSI) Deductions Log
                            </h4>
                            <p class="text-muted small mb-0">
                                View order-wise Smart Sales Incentive (SSI) deductions applied on your fulfilled purchase orders.
                            </p>
                        </div>
                        <div class="col-md-5 text-md-end">
                            <a href="{{ route('vendor.smart-sales-incentive') }}" class="btn btn-outline-primary me-1">
                                <iconify-icon icon="solar:tuning-square-2-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                                Slab Configuration
                            </a>
                            <a href="{{ route('vendor.dashboard') }}" class="btn btn-outline-secondary">
                                <iconify-icon icon="solar:widget-5-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                                Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Metrics -->
    <div class="row g-3 mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-danger">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fs-12 text-uppercase fw-bold tracking-wider">Total SSI Deducted Amount</span>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-danger-subtle text-danger rounded-circle">
                                <iconify-icon icon="solar:ticket-sale-bold" class="fs-20"></iconify-icon>
                            </span>
                        </div>
                    </div>
                    <h2 class="fw-extrabold text-danger mb-0" style="font-family: 'Outfit', sans-serif;">
                        ₹{{ number_format($totalDeducted, 2) }}
                    </h2>
                    <small class="text-muted fs-11 mt-1 d-block">Smart Sales Incentive (SSI) deducted across seller orders</small>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-info">
                <div class="card-body p-3.5">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted fs-12 text-uppercase fw-bold tracking-wider">Total Purchase Orders with SSI</span>
                        <div class="avatar-sm flex-shrink-0">
                            <span class="avatar-title bg-info-subtle text-info rounded-circle">
                                <iconify-icon icon="solar:bill-list-bold" class="fs-20"></iconify-icon>
                            </span>
                        </div>
                    </div>
                    <h2 class="fw-extrabold text-slate-800 mb-0" style="font-family: 'Outfit', sans-serif;">
                        {{ number_format($totalOrders) }}
                    </h2>
                    <small class="text-muted fs-11 mt-1 d-block">Purchase orders with active SSI deductions</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filters Section -->
    <div class="row mb-3 g-2">
        <div class="col-md-12">
            <form id="filterForm" action="{{ route('vendor.smart-sales-incentive.deductions') }}" method="GET" class="d-flex flex-wrap gap-2">
                <input type="hidden" name="sort" id="formSort" value="{{ $sort }}">
                <input type="hidden" name="order" id="formOrder" value="{{ $order }}">
                
                <div class="input-group" style="max-width: 340px;">
                    <span class="input-group-text bg-white border-end-0"><iconify-icon icon="solar:magnifer-bold-duotone" class="text-muted"></iconify-icon></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search PO #, DDC Center, Product..." value="{{ $search }}">
                </div>

                <select name="limit" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="15" {{ $limit == 15 ? 'selected' : '' }}>15 per page</option>
                    <option value="25" {{ $limit == 25 ? 'selected' : '' }}>25 per page</option>
                    <option value="50" {{ $limit == 50 ? 'selected' : '' }}>50 per page</option>
                </select>

                <button type="submit" class="btn btn-primary px-3">Filter</button>
                @if($search)
                    <a href="{{ route('vendor.smart-sales-incentive.deductions') }}" class="btn btn-outline-danger">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Deductions Table (1 Row Per Purchase Order) -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0 align-middle">
                    <thead class="bg-light bg-opacity-50 border-top border-bottom fs-13">
                        <tr>
                            <th class="ps-3" style="width: 50px;">#</th>
                            <th>PO Number & Date</th>
                            <th>Buyer / DDC Hub</th>
                            <th class="text-center">Items</th>
                            <th class="text-end">Order Total</th>
                            <th class="text-center">SSI Rate (%)</th>
                            <th class="text-end">SSI Deducted Amount</th>
                            <th class="text-end pe-3" style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="fs-13">
                        @forelse($paginatedPos as $index => $po)
                            @php
                                $poTotalAmt = (float) ($po->seller_accepted_amount > 0 ? $po->seller_accepted_amount : ($po->seller_total_amount > 0 ? $po->seller_total_amount : $po->total_amount));
                                $poItemsData = $po->items->map(function($item) use ($po) {
                                    $itemTotal = (float) ($item->seller_total > 0 ? $item->seller_total : ($item->total ?? 0));
                                    $itemSsi = round($itemTotal * ((float) $po->ssi_percentage / 100.0), 2);
                                    return [
                                        'product_name' => $item->product?->product_name ?? ('Product #' . $item->product_id),
                                        'product_code' => $item->product?->product_code ?? 'SKU-N/A',
                                        'quantity' => $item->quantity,
                                        'total' => number_format($itemTotal, 2),
                                        'ssi_amount' => number_format($itemSsi, 2)
                                    ];
                                });
                            @endphp
                            <tr>
                                <td class="ps-3 text-muted">{{ $paginatedPos->firstItem() + $index }}</td>
                                <td>
                                    <a href="{{ route('vendor.purchase-orders.show', $po->id) }}" class="fw-bold text-primary font-monospace d-block text-decoration-none">
                                        {{ $po->po_number }}
                                    </a>
                                    <span class="text-muted fs-11">{{ $po->created_at->format('d M Y, h:i A') }}</span>
                                </td>
                                <td>
                                    @if($po->ddc)
                                        <strong class="text-slate-800 d-block fs-12">{{ $po->ddc->name ?? $po->ddc->company_name ?? 'DDC Hub' }}</strong>
                                        <span class="text-muted fs-11">{{ $po->ddc->profile?->district ?? '' }}</span>
                                    @else
                                        <span class="text-muted">DDC Hub</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border px-2 py-1 font-monospace fs-12">
                                        {{ $po->items->count() }} items
                                    </span>
                                </td>
                                <td class="text-end fw-semibold text-slate-800 font-monospace">
                                    ₹{{ number_format($poTotalAmt, 2) }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-emerald-subtle text-emerald font-monospace fs-12 border border-emerald-subtle px-2 py-1" style="background: rgba(4, 120, 87, 0.1); color: #047857;">
                                        {{ number_format($po->ssi_percentage, 2) }}%
                                    </span>
                                </td>
                                <td class="text-end">
                                    <strong class="text-danger fs-14 font-monospace">
                                        ₹{{ number_format($po->ssi_amount, 2) }}
                                    </strong>
                                </td>
                                <td class="text-end pe-3">
                                    <button type="button" class="btn btn-xs btn-outline-primary py-1 px-2.5 fs-11 fw-semibold" onclick="openAuditModal({{ json_encode([
                                        'po_number' => $po->po_number,
                                        'ddc_name' => $po->ddc->name ?? ($po->ddc->company_name ?? 'DDC Hub'),
                                        'po_total' => number_format($poTotalAmt, 2),
                                        'ssi_rate' => number_format($po->ssi_percentage, 2),
                                        'amount' => number_format($po->ssi_amount, 2),
                                        'created_at' => $po->created_at->format('d M Y, h:i A'),
                                        'items' => $poItemsData
                                    ]) }})">
                                        <iconify-icon icon="solar:eye-bold" class="align-middle me-0.5"></iconify-icon> Details
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <div class="d-flex flex-column align-items-center justify-content-center py-2">
                                        <div class="avatar-lg bg-light rounded-circle d-flex align-items-center justify-content-center mb-3 text-muted border shadow-xs" style="width: 64px; height: 64px;">
                                            <iconify-icon icon="solar:ticket-sale-bold-duotone" class="fs-32 text-primary"></iconify-icon>
                                        </div>
                                        <h5 class="fw-bold text-slate-800 mb-1">No Purchase Orders with SSI Deductions</h5>
                                        <p class="mb-0 fs-12 text-muted" style="max-width: 480px;">Only purchase orders where Smart Sales Incentive (SSI) was deducted (> ₹0.00) will appear here.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-2.5 px-3">
            {{ $paginatedPos->appends(request()->query())->links() }}
        </div>
    </div>

    <!-- PO Details Modal -->
    <div class="modal fade" id="auditModal" tabindex="-1" aria-labelledby="auditModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white py-3">
                    <h5 class="modal-title fw-bold text-white fs-15 mb-0" id="auditModalLabel">
                        <iconify-icon icon="solar:ticket-sale-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                        Smart Sales Incentive (SSI) Order Deduction Breakdown
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 fs-13">
                    <div class="row g-3 align-items-center mb-3 p-3 bg-light rounded border">
                        <div class="col-md-7">
                            <span class="text-muted fs-11 d-block text-uppercase fw-semibold">Purchase Order Number</span>
                            <h5 class="fw-bold text-slate-800 font-monospace mb-1" id="mPoNumber">PO-0000</h5>
                            <div class="text-muted fs-12">
                                Buyer / DDC: <strong class="text-dark" id="mDdcName">--</strong>
                            </div>
                            <div class="text-muted fs-11 mt-0.5">Order Date: <span id="mCreatedAt">--</span></div>
                        </div>
                        <div class="col-md-5 text-md-end border-start border-md-start-0 pt-2 pt-md-0">
                            <span class="text-muted fs-11 d-block text-uppercase fw-semibold">Total SSI Deducted Amount</span>
                            <h3 class="fw-extrabold text-danger mb-0" id="mAmount">₹0.00</h3>
                            <span class="fs-12 text-muted fw-semibold">SSI Slab Rate: <strong class="text-primary font-monospace" id="mSsiRate">0.00%</strong></span>
                        </div>
                    </div>

                    <div class="card border mb-0">
                        <div class="card-header bg-white py-2.5 fw-bold text-slate-800 fs-13 border-bottom d-flex align-items-center justify-content-between">
                            <span>Order Items & SSI Deduction Breakdown</span>
                            <span class="text-muted fs-12 font-monospace">Order Total: ₹<strong id="mPoTotal">0.00</strong></span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 text-nowrap">
                                    <thead class="table-light fs-12">
                                        <tr>
                                            <th>#</th>
                                            <th>Product Name & Code</th>
                                            <th class="text-center">Qty</th>
                                            <th class="text-end">Item Total</th>
                                            <th class="text-center">SSI Rate</th>
                                            <th class="text-end">Deduction</th>
                                        </tr>
                                    </thead>
                                    <tbody id="mItemsList" class="fs-12">
                                        <!-- Dynamic JS Injection -->
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-3">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openAuditModal(data) {
            document.getElementById('mPoNumber').textContent = data.po_number;
            document.getElementById('mDdcName').textContent = data.ddc_name;
            document.getElementById('mCreatedAt').textContent = data.created_at;
            document.getElementById('mPoTotal').textContent = data.po_total;
            document.getElementById('mSsiRate').textContent = data.ssi_rate + '%';
            document.getElementById('mAmount').textContent = '₹' + data.amount;

            let itemsHtml = '';
            data.items.forEach((item, index) => {
                itemsHtml += `
                    <tr>
                        <td class="text-muted">${index + 1}</td>
                        <td>
                            <strong class="text-dark d-block fs-12">${item.product_name}</strong>
                            <span class="text-muted fs-11 font-monospace">${item.product_code}</span>
                        </td>
                        <td class="text-center fw-semibold">${item.quantity}</td>
                        <td class="text-end font-monospace">₹${item.total}</td>
                        <td class="text-center font-monospace text-emerald fw-semibold">${data.ssi_rate}%</td>
                        <td class="text-end fw-bold text-danger font-monospace">₹${item.ssi_amount}</td>
                    </tr>
                `;
            });
            document.getElementById('mItemsList').innerHTML = itemsHtml;

            const modal = new bootstrap.Modal(document.getElementById('auditModal'));
            modal.show();
        }
    </script>
@endpush

