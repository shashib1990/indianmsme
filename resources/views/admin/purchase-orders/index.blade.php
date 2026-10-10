@extends('layouts.admin')

@section('title', 'Purchase Orders')
@section('page-title', 'Purchase Orders')

@section('content')
    <!-- Common Page Action Header -->
    <x-admin.page-header title="Purchase Orders"
        subtitle="Monitor the complete lifecycle of Purchase Orders, verify routing, track payments, and manage cancellations." />

    <!-- Search & Filters -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
                     <form action="{{ route('admin.purchase-orders.index') }}" method="GET" class="row g-3" id="filterForm">
                        <input type="hidden" name="sort" id="formSort" value="{{ $sort }}">
                        <input type="hidden" name="order" id="formOrder" value="{{ $order }}">
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-slate-700">Search</label>
                            <input type="text" name="search" class="form-control form-control-sm" placeholder="PO Number, DDC or Seller..." value="{{ request('search') }}">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold text-slate-700">PO Status</label>
                            <select name="status" class="form-select form-select-sm">
                                <option value="">All Statuses</option>
                                <option value="Pending"    {{ request('status') === 'Pending'    ? 'selected' : '' }}>PO Created</option>
                                <option value="Accepted"   {{ request('status') === 'Accepted'   ? 'selected' : '' }}>Accepted</option>
                                <option value="Rejected"   {{ request('status') === 'Rejected'   ? 'selected' : '' }}>Rejected</option>
                                <option value="Processing" {{ request('status') === 'Processing' ? 'selected' : '' }}>Processing</option>
                                <option value="Cancelled"  {{ request('status') === 'Cancelled'  ? 'selected' : '' }}>Cancelled</option>
                                <option value="Dispatched" {{ request('status') === 'Dispatched' ? 'selected' : '' }}>Dispatched</option>
                                <option value="Received"   {{ request('status') === 'Received'   ? 'selected' : '' }}>Received</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold text-slate-700">Payment Status</label>
                            <select name="payment_status" class="form-select form-select-sm">
                                <option value="">All Statuses</option>
                                <option value="Unpaid" {{ request('payment_status') === 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                                <option value="Paid" {{ request('payment_status') === 'Paid' ? 'selected' : '' }}>Paid</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold text-slate-700">Verification</label>
                            <select name="payment_verification" class="form-select form-select-sm">
                                <option value="">All Statuses</option>
                                <option value="Pending" {{ request('payment_verification') === 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Verified" {{ request('payment_verification') === 'Verified' ? 'selected' : '' }}>Verified</option>
                                <option value="Rejected" {{ request('payment_verification') === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label small fw-semibold text-slate-700">Dispatch</label>
                            <select name="dispatch_status" class="form-select form-select-sm">
                                <option value="">All Statuses</option>
                                <option value="Pending" {{ request('dispatch_status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Dispatched" {{ request('dispatch_status') === 'Dispatched' ? 'selected' : '' }}>Dispatched</option>
                                <option value="Delivered" {{ request('dispatch_status') === 'Delivered' ? 'selected' : '' }}>Delivered</option>
                            </select>
                        </div>
                        <div class="col-md-1 d-flex align-items-end gap-1">
                            <button type="submit" class="btn btn-sm btn-primary w-100 py-1">Filter</button>
                            <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-sm btn-light py-1" title="Reset Filters">
                                <iconify-icon icon="solar:restart-bold" class="align-middle"></iconify-icon>
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Purchase Orders Table Card -->
            <div class="row">
                <div class="col-12">
                    <div class="card shadow-sm border-0">
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-centered mb-0 align-middle">
                                    <thead class="bg-light bg-opacity-50 border-top border-bottom">
                                            <tr>
                                                <x-admin.table-header field="po_number" :sort="$sort" :order="$order" class="ps-3">PO Number</x-admin.table-header>
                                                <th>Invoice No.</th>
                                                <x-admin.table-header field="created_at" :sort="$sort" :order="$order">Date</x-admin.table-header>
                                                <th>DDC Coordinator</th>
                                                <th>Seller Account</th>
                                                <th>Pickup Store</th>
                                                <x-admin.table-header field="total_amount" :sort="$sort" :order="$order" class="text-center">Total Amount</x-admin.table-header>
                                                <x-admin.table-header field="status" :sort="$sort" :order="$order" class="text-center">Response Status</x-admin.table-header>
                                                <x-admin.table-header field="payment_status" :sort="$sort" :order="$order" class="text-center">Payment Status</x-admin.table-header>
                                                <x-admin.table-header field="payment_verification_status" :sort="$sort" :order="$order" class="text-center">Verification</x-admin.table-header>
                                                <x-admin.table-header field="dispatch_status" :sort="$sort" :order="$order" class="text-center">Dispatch</x-admin.table-header>
                                                <th class="text-end pe-3" style="width: 120px;">Actions</th>
                                            </tr>
                                        </thead>
                                <tbody>
                                    @forelse($purchaseOrders as $po)
                                        <tr>
                                            <td class="ps-3 fw-bold text-slate-800">
                                                #{{ $po->po_number }}
                                                @if($po->adcOrder)
                                                    <div class="mt-1">
                                                        <a href="{{ route('admin.adc-orders.show', $po->adcOrder->id) }}" class="badge bg-primary-subtle text-primary border border-primary-subtle fw-medium" title="Linked ADC Order">
                                                            <iconify-icon icon="solar:bag-check-bold-duotone" class="align-middle me-0.5"></iconify-icon>
                                                            ADC: #{{ $po->adcOrder->order_number }}
                                                        </a>
                                                        @if($po->adcOrder->adc)
                                                            @php
                                                                $adcUser = $po->adcOrder->adc;
                                                                $adcProf = $adcUser->profile ?? $adcUser->adcProfile;
                                                                $adcIdVal = $adcProf?->adc_code ?: ($adcUser->adc_code ?: ($adcProf?->code ?: ('ADC' . str_pad($adcUser->id, 3, '0', STR_PAD_LEFT))));
                                                                $canteenName = $adcProf?->company_name ?: ($adcProf?->trade_name ?: $adcUser->name);
                                                                $locParts = array_filter([$adcProf?->district ?: $po->adcOrder->delivery_district, $adcProf?->state ?: $po->adcOrder->delivery_state]);
                                                                $canteenLoc = implode(', ', $locParts);
                                                            @endphp
                                                            <div class="text-muted small fs-10 mt-0.5 fw-normal">
                                                                @if($adcIdVal)
                                                                    <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-1 py-0 fs-10 font-monospace me-1" title="ADC ID">{{ $adcIdVal }}</span>
                                                                @endif
                                                                <span class="fw-semibold text-slate-700">{{ $canteenName }}</span>
                                                                @if($canteenLoc)
                                                                    <span class="text-muted">({{ $canteenLoc }})</span>
                                                                @endif
                                                            </div>
                                                        @endif
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                @if($po->invoice)
                                                    <a href="{{ route('admin.purchase-orders.invoice', $po->id) }}" target="_blank" class="text-primary fw-semibold">
                                                        <iconify-icon icon="solar:document-text-bold-duotone" class="align-middle me-0.5"></iconify-icon>
                                                        {{ $po->invoice->invoice_number }}
                                                    </a>
                                                @else
                                                    <span class="text-muted small">—</span>
                                                @endif
                                            </td>
                                            <td class="text-muted small">{{ $po->created_at->format('d M Y, h:i A') }}</td>
                                        <td>
                                            <div class="fw-semibold text-slate-700">{{ $po->ddc?->name }}</div>
                                            <div class="text-muted small" style="font-size: 11px;">District: {{ $po->ddc?->profile?->district ?? '—' }}</div>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-slate-700">{{ $po->seller?->company_name ?: $po->seller?->name }}</div>
                                            @if($po->seller?->parent_id)
                                                <div class="text-muted small" style="font-size: 10px;">Sub Seller Account</div>
                                            @else
                                                <div class="text-muted small" style="font-size: 10px;">Main Seller Account</div>
                                            @endif
                                        </td>
                                        <td>
                                            @if($po->pickupStore)
                                                <div class="fw-semibold text-slate-800">{{ $po->pickupStore->pickup_store_name }}</div>
                                                <div class="small">
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
                                        <td class="text-center text-nowrap">
                                            <div class="fw-bold text-slate-800" title="Ordered Amount">₹{{ number_format($po->total_amount, 2) }}</div>
                                            @if($po->status !== 'Pending' && $po->status !== 'Rejected' && $po->status !== 'Cancelled')
                                                <div class="text-success small" style="font-size: 10px;" title="DDC Accepted Amount">
                                                    <i class="bx bx-check-circle align-middle"></i> DDC: ₹{{ number_format($po->accepted_amount, 2) }}
                                                </div>
                                                <div class="text-primary small" style="font-size: 10px;" title="Seller Accepted Amount">
                                                    <i class="bx bx-store align-middle"></i> Seller: ₹{{ number_format($po->seller_accepted_amount, 2) }}
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
                                                <span class="badge bg-success text-white px-2 py-1 fs-11">Paid</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-11">Unpaid</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($po->payment_verification_status === 'Pending')
                                                <span class="badge bg-warning-subtle text-warning px-2 py-1 fs-11">Pending</span>
                                            @elseif($po->payment_verification_status === 'Verified')
                                                <span class="badge bg-success-subtle text-success px-2 py-1 fs-11">Verified</span>
                                            @elseif($po->payment_verification_status === 'Rejected')
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-11">Rejected</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($po->dispatch_status === 'Pending')
                                                <span class="badge bg-warning-subtle text-warning px-2 py-1 fs-11">Pending</span>
                                            @elseif($po->dispatch_status === 'Dispatched')
                                                <span class="badge bg-info-subtle text-info px-2 py-1 fs-11">Dispatched</span>
                                            @elseif($po->dispatch_status === 'Delivered')
                                                <span class="badge bg-success text-white px-2 py-1 fs-11">Delivered</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('admin.purchase-orders.show', $po->id) }}" class="btn btn-sm btn-outline-primary py-1 px-3">
                                                View
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="12" class="text-center py-4 text-muted small">No Purchase Orders found matching filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($purchaseOrders->hasPages())
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                            <small class="text-muted">Showing {{ $purchaseOrders->firstItem() }}–{{ $purchaseOrders->lastItem() }} of {{ $purchaseOrders->total() }}</small>
                            {{ $purchaseOrders->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
