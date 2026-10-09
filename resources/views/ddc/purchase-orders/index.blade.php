@extends('layouts.ddc')

@section('title', $pageTitle ?? 'Purchase Orders')
@section('page-title', $pageTitle ?? 'My Purchase Orders')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">{{ $pageTitle ?? 'Purchase Orders' }}</h4>
                            <p class="text-muted small mb-0">Track and manage your {{ strtolower($statusTitle ?? 'placed') }} Purchase Orders and payments.</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <a href="{{ route('ddc.purchase-orders.create') }}" class="btn btn-primary">
                                <iconify-icon icon="solar:add-circle-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                                New Purchase Order
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Status Navigation Tabs -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body py-2 px-3">
            <div class="d-flex align-items-center gap-2 overflow-auto text-nowrap py-1">
                <a href="{{ route('ddc.purchase-orders.index') }}" 
                   class="btn btn-sm {{ request()->routeIs('ddc.purchase-orders.index') ? 'btn-primary' : 'btn-light text-slate-700' }} rounded-pill px-3 py-1">
                    All Orders <span class="badge bg-white text-dark ms-1">{{ $totalCount ?? 0 }}</span>
                </a>
                <a href="{{ route('ddc.purchase-orders.pending') }}" 
                   class="btn btn-sm {{ request()->routeIs('ddc.purchase-orders.pending') ? 'btn-secondary' : 'btn-light text-slate-700' }} rounded-pill px-3 py-1">
                    PO Created <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $statusCounts['Pending'] ?? 0 }}</span>
                </a>
                <a href="{{ route('ddc.purchase-orders.accepted') }}" 
                   class="btn btn-sm {{ request()->routeIs('ddc.purchase-orders.accepted') ? 'btn-success' : 'btn-light text-slate-700' }} rounded-pill px-3 py-1">
                    Accepted <span class="badge bg-success-subtle text-success ms-1">{{ $statusCounts['Accepted'] ?? 0 }}</span>
                </a>
                <a href="{{ route('ddc.purchase-orders.processing') }}" 
                   class="btn btn-sm {{ request()->routeIs('ddc.purchase-orders.processing') ? 'btn-info' : 'btn-light text-slate-700' }} rounded-pill px-3 py-1">
                    Processing <span class="badge bg-info-subtle text-info ms-1">{{ $statusCounts['Processing'] ?? 0 }}</span>
                </a>
                <a href="{{ route('ddc.purchase-orders.dispatched') }}" 
                   class="btn btn-sm {{ request()->routeIs('ddc.purchase-orders.dispatched') ? 'btn-warning' : 'btn-light text-slate-700' }} rounded-pill px-3 py-1">
                    Dispatched <span class="badge bg-warning-subtle text-warning ms-1">{{ $statusCounts['Dispatched'] ?? 0 }}</span>
                </a>
                <a href="{{ route('ddc.purchase-orders.received-orders') }}" 
                   class="btn btn-sm {{ request()->routeIs('ddc.purchase-orders.received-orders') ? 'btn-success' : 'btn-light text-slate-700' }} rounded-pill px-3 py-1">
                    Received <span class="badge bg-success text-white ms-1">{{ $statusCounts['Received'] ?? 0 }}</span>
                </a>
                <a href="{{ route('ddc.purchase-orders.rejected') }}" 
                   class="btn btn-sm {{ request()->routeIs('ddc.purchase-orders.rejected') ? 'btn-danger' : 'btn-light text-slate-700' }} rounded-pill px-3 py-1">
                    Rejected <span class="badge bg-danger-subtle text-danger ms-1">{{ $statusCounts['Rejected'] ?? 0 }}</span>
                </a>
                <a href="{{ route('ddc.purchase-orders.cancelled') }}" 
                   class="btn btn-sm {{ request()->routeIs('ddc.purchase-orders.cancelled') ? 'btn-danger' : 'btn-light text-slate-700' }} rounded-pill px-3 py-1">
                    Cancelled <span class="badge bg-danger text-white ms-1">{{ $statusCounts['Cancelled'] ?? 0 }}</span>
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
                
                <div class="input-group input-group-sm" style="max-width: 300px;">
                    <span class="input-group-text bg-white border-end-0 py-1">
                        <iconify-icon icon="solar:magnifer-bold-duotone" class="text-muted"></iconify-icon>
                    </span>
                    <input type="text" name="search" class="form-control form-control-sm border-start-0 py-1" placeholder="Search PO, AMA or Seller..." value="{{ $search }}">
                </div>

                <button type="submit" class="btn btn-sm btn-primary py-1 px-3">Search</button>
                @if($search)
                    <a href="{{ url()->current() }}" class="btn btn-sm btn-outline-danger">Reset</a>
                @endif
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="fw-bold text-slate-800 mb-0">{{ $statusTitle ?? 'My Placed POs' }}</h6>
            <span class="badge bg-light text-muted">{{ $purchaseOrders->total() }} Orders</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light bg-opacity-50 border-top border-bottom">
                        <tr>
                            <x-admin.table-header field="po_number" :sort="$sort" :order="$order" class="ps-3">PO Number</x-admin.table-header>
                            <th class="py-3">Seller</th>
                            <x-admin.table-header field="created_at" :sort="$sort" :order="$order">Date Placed</x-admin.table-header>
                            <x-admin.table-header field="total_amount" :sort="$sort" :order="$order" class="text-center">Total Amount</x-admin.table-header>
                            <x-admin.table-header field="status" :sort="$sort" :order="$order" class="text-center">PO Status</x-admin.table-header>
                            <x-admin.table-header field="payment_status" :sort="$sort" :order="$order" class="text-center">Payment</x-admin.table-header>
                            <th class="pe-3 text-center" style="width: 130px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseOrders as $po)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-bold text-slate-800 font-monospace small">{{ $po->po_number }}</div>
                                    @if($po->ama_code)
                                        <div class="text-muted" style="font-size:11px;">AMA: {{ $po->ama_code }}</div>
                                    @endif
                                    @if(in_array($po->status, ['Dispatched', 'Received']) && $po->invoice)
                                        @if($po->invoice->invoice_number)
                                            <div class="text-info" style="font-size:11px;">Seller Inv: {{ $po->invoice->invoice_number }}</div>
                                        @endif
                                        @if($po->invoice->ddc_invoice_number)
                                            <div class="text-primary" style="font-size:11px;">DDC Inv: {{ $po->invoice->ddc_invoice_number }}</div>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-slate-700 small">
                                        {{ $po->seller?->company_name ?: $po->seller?->name }}
                                    </div>
                                    <div class="text-muted" style="font-size:11px;">ID: {{ $po->seller?->seller_id }}</div>
                                </td>
                                <td class="text-muted small">{{ $po->created_at->format('d M Y') }}<br>
                                    <span style="font-size:11px;">{{ $po->created_at->format('h:i A') }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="fw-semibold text-slate-800">₹{{ number_format($po->total_amount, 2) }}</div>
                                    @if(in_array($po->status, ['Accepted', 'Processing', 'Dispatched', 'Received']) && $po->accepted_amount > 0)
                                        <div class="text-success fw-bold" style="font-size:12px;">
                                            Paid: ₹{{ number_format($po->accepted_amount, 2) }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($po->status === 'Pending')
                                        <span class="badge bg-secondary-subtle text-secondary px-2 py-1 fs-11">PO Created</span>
                                    @elseif($po->status === 'Accepted')
                                        <span class="badge bg-success-subtle text-success px-2 py-1 fs-11">Accepted</span>
                                    @elseif($po->status === 'Rejected')
                                        <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-11">Rejected</span>
                                    @elseif($po->status === 'Cancelled')
                                        <span class="badge bg-danger text-white px-2 py-1 fs-11">Cancelled</span>
                                    @elseif($po->status === 'Processing')
                                        <span class="badge bg-primary-subtle text-primary px-2 py-1 fs-11">Processing</span>
                                    @elseif($po->status === 'Dispatched')
                                        <span class="badge bg-warning-subtle text-warning px-2 py-1 fs-11">Dispatched</span>
                                    @elseif($po->status === 'Received')
                                        <span class="badge bg-success text-white px-2 py-1 fs-11">Received</span>
                                    @else
                                        <span class="badge bg-light text-muted px-2 py-1 fs-11">{{ $po->status }}</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($po->payment_status === 'Paid')
                                        <span class="badge bg-success text-white px-2 py-1 fs-11">
                                            <iconify-icon icon="solar:verified-check-bold" class="align-middle me-1"></iconify-icon> Paid
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-11">Unpaid</span>
                                    @endif
                                </td>
                                <td class="pe-3 text-center">
                                    <div class="d-flex flex-column gap-1 align-items-center">
                                        <a href="{{ route('ddc.purchase-orders.show', $po->id) }}"
                                           class="btn btn-sm btn-outline-primary py-1 px-3 w-100">
                                            <iconify-icon icon="solar:eye-bold-duotone" class="align-middle me-1"></iconify-icon>
                                            View
                                        </a>
                                        @if($po->status === 'Dispatched' && $po->invoice?->ddc_invoice_number && $po->invoice?->ddc_invoice_file)
                                            <a href="{{ route('ddc.purchase-orders.receive', $po->id) }}"
                                               class="btn btn-sm btn-success py-1 px-3 w-100">
                                                <iconify-icon icon="solar:box-bold-duotone" class="align-middle me-1"></iconify-icon>
                                                Stock In
                                            </a>
                                        @endif
                                        @if(in_array($po->status, ['Received']) || $po->stock_received_by_ddc)
                                            @php
                                                $hasShortageDamage = $po->items && $po->items->filter(fn($i) => ($i->shortage_qty > 0 || $i->damage_qty > 0))->isNotEmpty();
                                                $hasExcess = $po->items && $po->items->filter(fn($i) => ($i->excess_qty > 0))->isNotEmpty();
                                                $excessInv = $hasExcess ? \App\Models\Invoice::where('purchase_order_id', $po->id)->where('type', 'excess_stock_ddc')->first() : null;
                                            @endphp

                                            @if($hasShortageDamage)
                                                @if($po->debitNote)
                                                    <a href="{{ route('ddc.debit-notes.show', $po->debitNote->id) }}"
                                                       class="btn btn-sm btn-info text-white py-1 px-2 w-100 fs-11"
                                                       title="View Discrepancy">
                                                        <iconify-icon icon="solar:document-bold-duotone" class="align-middle me-1"></iconify-icon>
                                                        View Discrepancy
                                                    </a>
                                                @else
                                                    <a href="{{ route('ddc.purchase-orders.debit-note.create', $po->id) }}"
                                                       class="btn btn-sm btn-warning text-dark py-1 px-2 w-100 fs-11 fw-bold"
                                                       title="Send Discrepancy to Seller">
                                                        <iconify-icon icon="solar:shield-warning-bold-duotone" class="align-middle me-1"></iconify-icon>
                                                        Send Discrepancy to Seller
                                                    </a>
                                                @endif
                                            @endif

                                            @if($hasExcess)
                                                @if($excessInv)
                                                    <a href="{{ route('ddc.excess-invoices.show', $excessInv->id) }}"
                                                       class="btn btn-sm btn-outline-success py-1 px-2 w-100 fs-11 fw-bold"
                                                       title="View Excess Invoice">
                                                        <iconify-icon icon="solar:document-text-bold-duotone" class="align-middle me-1"></iconify-icon>
                                                        View Excess Invoice
                                                    </a>
                                                @else
                                                    <a href="{{ route('ddc.purchase-orders.excess-invoice.create', $po->id) }}"
                                                       class="btn btn-sm btn-primary py-1 px-2 w-100 fs-11 fw-bold"
                                                       title="Submit Excess Stock">
                                                        <iconify-icon icon="solar:add-circle-bold-duotone" class="align-middle me-1"></iconify-icon>
                                                        Submit Excess Stock
                                                    </a>
                                                @endif
                                            @endif
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <iconify-icon icon="solar:document-bold-duotone" class="fs-36 text-muted mb-2"></iconify-icon><br>
                                    No Purchase Orders placed yet.
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
