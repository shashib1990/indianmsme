@extends('layouts.vendor')

@section('title', 'New Purchase Orders')
@section('page-title', 'New Purchase Orders')

@section('content')
    <!-- Standard Page Header -->
    <x-admin.page-header title="New Purchase Orders"
        subtitle="Manage Purchase Orders at this stage. Review item counts, pricing, payment verification status, and fulfillment state." />

    <!-- Penalty Notice for Item Acceptance -->
    <div class="alert alert-warning border border-warning-subtle shadow-sm mb-4 p-3 rounded-3" style="background-color: #fff8e6;">
        <div class="d-flex align-items-start gap-3">
            <iconify-icon icon="solar:danger-bold-duotone" class="text-warning fs-24 flex-shrink-0 mt-1"></iconify-icon>
            <div>
                <h6 class="fw-bold text-dark mb-1">
                    <span class="badge bg-warning text-dark me-1">Note / सूचना</span>
                </h6>
                <div class="fw-bold text-dark fs-14 mb-1" style="line-height: 1.5;">
                    कृपया केवल उपलब्ध items ही Accept करें। Acceptance के बाद dispatch में shortfall होने पर Seller पर Penalty लगाई जाएगी ।
                </div>
                <div class="text-muted small fw-semibold" style="line-height: 1.4;">
                    Please accept only available items. Any shortfall at dispatch after acceptance penalty will be imposed on the Seller.
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <iconify-icon icon="solar:check-circle-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filters Section -->
    <div class="row mb-3 g-2">
        <div class="col-12">
            <form action="{{ url()->current() }}" method="GET" class="d-flex align-items-center gap-2 mb-0" id="filterForm">
                <input type="hidden" name="sort" id="formSort" value="{{ $sort }}">
                <input type="hidden" name="order" id="formOrder" value="{{ $order }}">

                <div class="input-group input-group-sm" style="max-width: 250px;">
                    <span class="input-group-text bg-white border-end-0 py-1">
                        <iconify-icon icon="solar:magnifer-bold-duotone" class="text-muted"></iconify-icon>
                    </span>
                    <input type="text" name="search" class="form-control form-control-sm border-start-0 py-1"
                        placeholder="Search PO or DDC..." value="{{ $search }}">
                </div>
                <button type="submit" class="btn btn-sm btn-primary py-1 px-3">Search</button>
                @if($search)
                    <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-danger">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3 border-bottom">
            <h6 class="fw-bold text-slate-800 mb-0">
                New Purchase Orders
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light bg-opacity-50 border-top border-bottom">
                        <tr>
                            <x-admin.table-header field="po_number" :sort="$sort" :order="$order" class="ps-3">PO
                                Number</x-admin.table-header>
                            <x-admin.table-header field="created_at" :sort="$sort" :order="$order">Date
                                Assigned</x-admin.table-header>
                            <th>DDC Details</th>
                            <th>Pickup Store</th>
                            <th class="text-center">SSI Total</th>
                            <x-admin.table-header field="total_amount" :sort="$sort" :order="$order"
                                class="text-center">Total Amount</x-admin.table-header>
                            <x-admin.table-header field="status" :sort="$sort" :order="$order" class="text-center">PO
                                Status</x-admin.table-header>
                            <x-admin.table-header field="payment_status" :sort="$sort" :order="$order"
                                class="text-center">Payment Status</x-admin.table-header>
                            <x-admin.table-header field="dispatch_status" :sort="$sort" :order="$order"
                                class="text-center">Dispatch Status</x-admin.table-header>
                            <th class="pe-3 text-center" style="width: 120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseOrders as $po)
                            @php
                                $sellerId = auth('vendor')->id();
                                $yourItemsTotal = $po->seller_total_amount > 0 ? $po->seller_total_amount : $po->items->where('seller_id', $sellerId)->sum('seller_total');
                                $hasPaymentCycleStarted = in_array($po->status, ['Processing', 'Dispatched', 'Received']);
                            @endphp
                            <tr>
                                <td class="ps-3 fw-bold text-slate-800">#{{ $po->po_number }}</td>
                                <td class="text-muted small">{{ $po->created_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    <div class="fw-semibold text-slate-700">{{ $po->ddc?->name }}</div>
                                    <div class="text-muted small" style="font-size: 11px;">District:
                                        {{ $po->ddc?->profile?->district ?? '—' }}</div>
                                </td>
                                <td>
                                    @if($po->pickupStore)
                                        <div class="fw-semibold text-slate-800">
                                            <a href="{{ route('vendor.pickup-stores.show', $po->pickupStore->id) }}" class="text-slate-800 text-decoration-none hover-primary" title="View Pickup Store">
                                                {{ $po->pickupStore->pickup_store_name }}
                                            </a>
                                        </div>
                                        <div class="small d-flex align-items-center gap-1 mt-0.5 flex-wrap">
                                            <a href="{{ route('vendor.pickup-stores.show', $po->pickupStore->id) }}" class="badge bg-primary-subtle text-primary border border-primary-subtle px-1.5 py-0.5 fs-10 font-monospace text-decoration-none" title="Pickup Store Code">
                                                <iconify-icon icon="solar:shop-bold-duotone" class="align-middle me-0.5"></iconify-icon>{{ $po->pickupStore->pickup_store_code }}
                                            </a>
                                            @if($po->pickupStore->can_receive_payment_and_dispatch)
                                                <span class="badge bg-info-subtle text-info px-1 py-0 fs-10">Authorized</span>
                                            @else
                                                <span class="badge bg-light text-muted px-1 py-0 fs-10">Unauthorized</span>
                                            @endif
                                        </div>
                                    @else
                                        <span class="text-muted small">None (Seller Direct)</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($po->ssi_applicable && $po->ssi_amount > 0)
                                        <span class="fw-bold text-success">₹{{ number_format($po->ssi_amount, 2) }}</span>
                                        @if($po->ssi_percentage)
                                            <span class="fs-10 text-muted d-block">({{ number_format($po->ssi_percentage, 1) }}%)</span>
                                        @endif
                                    @else
                                        <span class="text-muted font-monospace">₹0.00</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold text-primary">₹{{ number_format($yourItemsTotal, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-secondary-subtle text-secondary px-2 py-1 fs-11">PO Created</span>
                                </td>
                                <td class="text-center">
                                    <span class="text-muted small">—</span>
                                </td>
                                <td class="text-center">
                                    <span class="text-muted small">—</span>
                                </td>
                                <td class="pe-3 text-center">
                                    <a href="{{ route('vendor.purchase-orders.show', $po->id) }}"
                                        class="btn btn-sm btn-outline-primary py-1 px-3">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted small">
                                    <iconify-icon icon="solar:cart-large-minimalistic-bold-duotone"
                                        class="fs-32 mb-2 text-slate-300"></iconify-icon><br>
                                    No purchase orders found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="d-flex justify-content-between align-items-center p-3 border-top">
                <span class="text-muted small">Showing {{ $purchaseOrders->firstItem() ?? 0 }} to
                    {{ $purchaseOrders->lastItem() ?? 0 }} of {{ $purchaseOrders->total() }} entries</span>
                @if($purchaseOrders->hasPages())
                    {{ $purchaseOrders->links() }}
                @endif
            </div>
        </div>
    </div>
@endsection