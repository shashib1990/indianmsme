@extends('layouts.pickup_store')

@section('title', 'Ready for Dispatch')
@section('page-title', 'Ready for Dispatch')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">Ready for Dispatch</h4>
                            <p class="text-muted small mb-0">
                                @if($store->can_receive_payment_and_dispatch)
                                    Track and manage DDC payments and order dispatches for your Authorized store.
                                @else
                                    Monitor seller payout verifications and package dispatches for your store.
                                @endif
                            </p>
                        </div>
                    </div>
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

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <iconify-icon icon="solar:danger-triangle-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Status Navigation Tabs -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2 px-3">
            <div class="d-flex align-items-center gap-2 overflow-auto text-nowrap py-1">
                <a href="{{ route('store.purchase-orders.all') }}" 
                   class="btn btn-sm {{ request()->routeIs('store.purchase-orders.all') ? 'btn-primary' : 'btn-light text-slate-700' }} rounded-pill px-3 py-1">
                    All Orders
                </a>
                @if($store->can_receive_payment_and_dispatch)
                    <a href="{{ route('store.purchase-orders.verify') }}" 
                       class="btn btn-sm {{ request()->routeIs('store.purchase-orders.verify') ? 'btn-warning' : 'btn-light text-slate-700' }} rounded-pill px-3 py-1">
                        Payment Verification
                    </a>
                @endif
                <a href="{{ route('store.purchase-orders.ready') }}" 
                   class="btn btn-sm {{ request()->routeIs('store.purchase-orders.ready') ? 'btn-info' : 'btn-light text-slate-700' }} rounded-pill px-3 py-1">
                    Ready for Dispatch
                </a>
                <a href="{{ route('store.purchase-orders.dispatched') }}" 
                   class="btn btn-sm {{ request()->routeIs('store.purchase-orders.dispatched') ? 'btn-success' : 'btn-light text-slate-700' }} rounded-pill px-3 py-1">
                    Dispatched Orders
                </a>
                <a href="{{ route('store.purchase-orders.received') }}" 
                   class="btn btn-sm {{ request()->routeIs('store.purchase-orders.received') ? 'btn-secondary' : 'btn-light text-slate-700' }} rounded-pill px-3 py-1">
                    Received History
                </a>
            </div>
        </div>
    </div>

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
                    <input type="text" name="search" class="form-control form-control-sm border-start-0 py-1" placeholder="Search PO, DDC or Seller..." value="{{ $search }}">
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
            <h6 class="fw-bold text-slate-800 mb-0">Ready for Dispatch</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light bg-opacity-50 border-top border-bottom">
                        <tr>
                            <x-admin.table-header field="po_number" :sort="$sort" :order="$order" class="ps-3">PO Number</x-admin.table-header>
                            <x-admin.table-header field="created_at" :sort="$sort" :order="$order">Date Assigned</x-admin.table-header>
                            <th>DDC Details</th>
                            <th class="text-center">SSI Total</th>
                            <x-admin.table-header field="total_amount" :sort="$sort" :order="$order" class="text-center">Estimated Amount</x-admin.table-header>
                            <x-admin.table-header field="status" :sort="$sort" :order="$order" class="text-center">PO Status</x-admin.table-header>
                            <x-admin.table-header field="payment_status" :sort="$sort" :order="$order" class="text-center">Payment Status</x-admin.table-header>
                            <x-admin.table-header field="dispatch_status" :sort="$sort" :order="$order" class="text-center">Dispatch Status</x-admin.table-header>
                            <th class="pe-3 text-center" style="width: 120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseOrders as $po)
                            <tr>
                                <td class="ps-3 fw-bold text-slate-800">#{{ $po->po_number }}</td>
                                <td class="text-muted small">{{ $po->created_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    <div class="fw-semibold text-slate-700">{{ $po->ddc?->name }}</div>
                                    <div class="text-muted small" style="font-size: 11px;">District: {{ $po->ddc?->profile?->district ?? '—' }}</div>
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
                                <td class="text-center fw-bold text-slate-800">₹{{ number_format($po->effective_seller_amount, 2) }}</td>
                                <td class="text-center">
                                    <span class="badge bg-primary-subtle text-primary px-2 py-1 fs-11">Processing</span>
                                </td>
                                <td class="text-center">
                                    @if($po->payment_verification_status === 'Verified')
                                        <span class="badge bg-success text-white px-2 py-1 fs-11">Verified</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning px-2 py-1 fs-11">Pending</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning-subtle text-warning px-2 py-1 fs-11">Pending</span>
                                </td>
                                <td class="pe-3 text-center">
                                    <a href="{{ route('store.purchase-orders.show', $po->id) }}" class="btn btn-sm btn-outline-primary py-1 px-3">
                                        View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted small">
                                    <iconify-icon icon="solar:cart-large-bold-duotone" class="fs-32 mb-2 text-slate-300"></iconify-icon><br>
                                    No purchase orders found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            
            <div class="d-flex justify-content-between align-items-center p-3 border-top">
                <span class="text-muted small">Showing {{ $purchaseOrders->firstItem() ?? 0 }} to {{ $purchaseOrders->lastItem() ?? 0 }} of {{ $purchaseOrders->total() }} entries</span>
                @if($purchaseOrders->hasPages())
                    {{ $purchaseOrders->links() }}
                @endif
            </div>
        </div>
    </div>
@endsection
