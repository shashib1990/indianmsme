@extends('layouts.admin')

@section('title', 'ADC Orders')
@section('page-title', 'ADC Orders Management')

@section('content')
    <div class="container-fluid p-0">
        <x-admin.page-header title="ADC Orders"
            subtitle="Monitor orders placed by Area Development Coordinators (ADCs), track fulfillment types, and review linked Purchase Orders." />

        @if(request('status'))
            <div class="alert alert-info border-0 shadow-sm mb-3 d-flex align-items-center justify-content-between py-2 px-3" style="border-radius: 8px;">
                <div class="d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:filter-bold-duotone" class="fs-18 text-info"></iconify-icon>
                    <span class="fs-13">Filtered by Order Status: <strong class="text-uppercase text-info">{{ request('status') }}</strong></span>
                </div>
                <a href="{{ route('admin.adc-orders.index') }}" class="btn btn-sm btn-light fw-medium py-0.5 px-2 fs-11">
                    Clear Filter &times;
                </a>
            </div>
        @endif

        <!-- Statistics Cards -->
        <div class="row g-3 mb-4">
            <div class="col-xl-3 col-sm-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="avatar-md bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center me-3 fs-3">
                            <iconify-icon icon="solar:bag-check-bold-duotone"></iconify-icon>
                        </div>
                        <div>
                            <span class="text-muted small fw-medium d-block">Total ADC Orders</span>
                            <h4 class="mb-0 fw-bold text-slate-800">{{ number_format($totalOrdersCount) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="avatar-md bg-success-subtle text-success rounded-circle d-flex align-items-center justify-content-center me-3 fs-3">
                            <iconify-icon icon="solar:wallet-money-bold-duotone"></iconify-icon>
                        </div>
                        <div>
                            <span class="text-muted small fw-medium d-block">Total Order Value</span>
                            <h4 class="mb-0 fw-bold text-slate-800">₹{{ number_format($totalOrdersAmount, 2) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="avatar-md bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center me-3 fs-3">
                            <iconify-icon icon="solar:box-bold-duotone"></iconify-icon>
                        </div>
                        <div>
                            <span class="text-muted small fw-medium d-block">Physical DDC Orders</span>
                            <h4 class="mb-0 fw-bold text-slate-800">{{ number_format($physicalOrdersCount) }}</h4>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-xl-3 col-sm-6">
                <div class="card border-0 shadow-sm">
                    <div class="card-body p-3 d-flex align-items-center">
                        <div class="avatar-md bg-warning-subtle text-warning rounded-circle d-flex align-items-center justify-content-center me-3 fs-3">
                            <iconify-icon icon="solar:delivery-bold-duotone"></iconify-icon>
                        </div>
                        <div>
                            <span class="text-muted small fw-medium d-block">Virtual Delivery Orders</span>
                            <h4 class="mb-0 fw-bold text-slate-800">{{ number_format($virtualOrdersCount) }}</h4>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body p-3">
                <form action="{{ route('admin.adc-orders.index') }}" method="GET" class="row g-3 align-items-center" id="filterForm">
                    <div class="col-lg-4 col-md-6">
                        <label class="form-label small fw-semibold text-muted mb-1">Search</label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0">
                                <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                            </span>
                            <input type="text" name="search" class="form-control form-control-sm border-start-0" 
                                placeholder="Order #, ADC name, mobile, address, district..." 
                                value="{{ request('search') }}">
                        </div>
                    </div>

                    <div class="col-lg-2 col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1">Status</label>
                        <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="Pending" {{ request('status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Processing" {{ request('status') === 'Processing' ? 'selected' : '' }}>Processing</option>
                            <option value="Partially Dispatched" {{ request('status') === 'Partially Dispatched' ? 'selected' : '' }}>Partially Dispatched</option>
                            <option value="Dispatched" {{ request('status') === 'Dispatched' ? 'selected' : '' }}>Dispatched</option>
                            <option value="Received" {{ request('status') === 'Received' ? 'selected' : '' }}>Received</option>
                            <option value="Delivered" {{ request('status') === 'Delivered' ? 'selected' : '' }}>Delivered</option>
                            <option value="Cancelled" {{ request('status') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1">Fulfillment Type</label>
                        <select name="fulfillment_type" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Fulfillment</option>
                            <option value="physical" {{ request('fulfillment_type') === 'physical' ? 'selected' : '' }}>Physical DDC</option>
                            <option value="virtual" {{ request('fulfillment_type') === 'virtual' ? 'selected' : '' }}>Virtual (Seller Direct)</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-3">
                        <label class="form-label small fw-semibold text-muted mb-1">Payment Status</label>
                        <select name="payment_status" class="form-select form-select-sm" onchange="this.form.submit()">
                            <option value="">All Payments</option>
                            <option value="Paid" {{ request('payment_status') === 'Paid' ? 'selected' : '' }}>Paid</option>
                            <option value="Pending" {{ request('payment_status') === 'Pending' ? 'selected' : '' }}>Pending</option>
                            <option value="Failed" {{ request('payment_status') === 'Failed' ? 'selected' : '' }}>Failed</option>
                        </select>
                    </div>

                    <div class="col-lg-2 col-md-3 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-sm btn-primary py-1 px-3 w-100">
                            <iconify-icon icon="solar:filter-bold-duotone" class="align-middle me-1"></iconify-icon> Filter
                        </button>
                        @if(request()->anyFilled(['search', 'status', 'fulfillment_type', 'payment_status']))
                            <a href="{{ route('admin.adc-orders.index') }}" class="btn btn-sm btn-light py-1 px-2" title="Reset Filters">
                                <iconify-icon icon="solar:restart-bold-duotone" class="align-middle"></iconify-icon>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        <!-- Orders Table Card -->
        <div class="card shadow-sm border-0">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0 align-middle">
                        <thead class="bg-light bg-opacity-50 border-top border-bottom">
                            <tr>
                                <x-admin.table-header field="order_number" :sort="$sort" :order="$order" class="ps-3">Order Number</x-admin.table-header>
                                <th>Invoice No.</th>
                                <x-admin.table-header field="created_at" :sort="$sort" :order="$order">Date</x-admin.table-header>
                                <th>ADC Coordinator</th>
                                <th>Destination / District</th>
                                <x-admin.table-header field="fulfillment_type" :sort="$sort" :order="$order" class="text-center">Fulfillment</x-admin.table-header>
                                <x-admin.table-header field="total_amount" :sort="$sort" :order="$order" class="text-center">Total Amount</x-admin.table-header>
                                <x-admin.table-header field="payment_status" :sort="$sort" :order="$order" class="text-center">Payment</x-admin.table-header>
                                <x-admin.table-header field="status" :sort="$sort" :order="$order" class="text-center">Status</x-admin.table-header>
                                <th class="text-end pe-3" style="width: 120px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $ord)
                                <tr>
                                    <td class="ps-3 fw-bold text-slate-800">
                                        <a href="{{ route('admin.adc-orders.show', $ord->id) }}" class="text-decoration-none text-slate-800">
                                            #{{ $ord->order_number }}
                                        </a>
                                        @if($ord->purchaseOrders->count() > 0)
                                            <div class="mt-0.5">
                                                <span class="badge bg-secondary-subtle text-secondary fs-10" title="Generated Purchase Orders">
                                                    {{ $ord->purchaseOrders->count() }} PO(s)
                                                </span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($ord->invoice && $ord->invoice->invoice_number)
                                            <a href="{{ route('admin.adc-orders.invoice', $ord->id) }}" target="_blank" class="badge bg-success-subtle text-success border border-success-subtle fw-semibold font-monospace text-decoration-none" title="View / Print Tax Invoice">
                                                <iconify-icon icon="solar:document-text-bold" class="align-middle me-0.5"></iconify-icon>
                                                {{ $ord->invoice->invoice_number }}
                                            </a>
                                        @elseif(in_array($ord->status, ['Dispatched', 'Partially Dispatched', 'Received', 'Delivered', 'Completed']))
                                            <a href="{{ route('admin.adc-orders.invoice', $ord->id) }}" target="_blank" class="badge bg-light text-primary border border-primary-subtle fw-medium text-decoration-none" title="Generate / Print Tax Invoice">
                                                <iconify-icon icon="solar:printer-bold" class="align-middle me-0.5"></iconify-icon> Print
                                            </a>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $ord->created_at->format('d M Y, h:i A') }}</td>
                                    <td>
                                        <div class="fw-semibold text-slate-700">{{ $ord->adc?->name ?? 'N/A' }}</div>
                                        <div class="text-muted small" style="font-size: 11px;">
                                            <iconify-icon icon="solar:phone-bold-duotone" class="align-middle me-0.5"></iconify-icon>
                                            {{ $ord->adc?->mobile ?? '—' }}
                                        </div>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-slate-700">{{ $ord->delivery_district ?? '—' }}, {{ $ord->delivery_state ?? '—' }}</div>
                                        <div class="text-muted small" style="font-size: 11px;">PIN: {{ $ord->delivery_pincode ?? '—' }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if($ord->fulfillment_type === 'physical')
                                            <span class="badge bg-info-subtle text-info px-2 py-1 fs-11">
                                                <iconify-icon icon="solar:box-bold-duotone" class="align-middle me-0.5"></iconify-icon>
                                                Physical DDC
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning px-2 py-1 fs-11">
                                                <iconify-icon icon="solar:delivery-bold-duotone" class="align-middle me-0.5"></iconify-icon>
                                                Virtual (Seller Direct)
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold text-slate-800">
                                        ₹{{ number_format($ord->total_amount, 2) }}
                                    </td>
                                    <td class="text-center">
                                        @if($ord->payment_status === 'Paid')
                                            <span class="badge bg-success-subtle text-success px-2 py-1 fs-11">Paid</span>
                                        @elseif($ord->payment_status === 'Pending')
                                            <span class="badge bg-warning-subtle text-warning px-2 py-1 fs-11">Pending</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-11">{{ $ord->payment_status }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($ord->status === 'Pending')
                                            <span class="badge bg-secondary-subtle text-secondary px-2 py-1 fs-11">Pending</span>
                                        @elseif($ord->status === 'Processing')
                                            <span class="badge bg-primary-subtle text-primary px-2 py-1 fs-11">Processing</span>
                                        @elseif($ord->status === 'Partially Dispatched')
                                            <span class="badge bg-info-subtle text-info px-2 py-1 fs-11">Partially Dispatched</span>
                                        @elseif($ord->status === 'Dispatched')
                                            <span class="badge bg-warning-subtle text-warning px-2 py-1 fs-11">Dispatched</span>
                                        @elseif($ord->status === 'Received')
                                            <span class="badge bg-success-subtle text-success px-2 py-1 fs-11">Received</span>
                                        @elseif($ord->status === 'Delivered')
                                            <span class="badge bg-success text-white px-2 py-1 fs-11">Delivered</span>
                                        @elseif($ord->status === 'Cancelled')
                                            <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-11">Cancelled</span>
                                        @else
                                            <span class="badge bg-light text-dark px-2 py-1 fs-11">{{ $ord->status }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3">
                                        <div class="d-flex justify-content-end gap-1">
                                            <a href="{{ route('admin.adc-orders.show', $ord->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2" title="View Details">
                                                <iconify-icon icon="solar:eye-bold-duotone" class="align-middle me-1"></iconify-icon> Details
                                            </a>
                                            <a href="{{ route('admin.adc-orders.invoice', $ord->id) }}" target="_blank" class="btn btn-sm btn-outline-success py-1 px-2" title="View / Print Tax Invoice">
                                                <iconify-icon icon="solar:printer-bold" class="align-middle"></iconify-icon>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="10" class="text-center py-4 text-muted small">No ADC Orders found matching the criteria.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($orders->hasPages())
                <div class="card-footer bg-white border-top py-3">
                    {{ $orders->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
