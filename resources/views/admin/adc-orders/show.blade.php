@extends('layouts.admin')

@section('title', 'ADC Order #' . $order->order_number)
@section('page-title', 'ADC Order Details')

@section('content')
<div class="container-fluid p-0">
    <div class="mb-3 d-flex align-items-center justify-content-between">
        <a href="{{ route('admin.adc-orders.index') }}" class="btn btn-sm btn-outline-secondary">
            <iconify-icon icon="solar:arrow-left-bold-duotone" class="align-middle me-1"></iconify-icon> Back to ADC Orders
        </a>
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('admin.adc-orders.invoice', $order->id) }}" target="_blank" class="btn btn-sm btn-success d-inline-flex align-items-center gap-1.5 shadow-sm">
                <iconify-icon icon="solar:printer-bold" class="fs-16"></iconify-icon> View / Print Tax Invoice
            </a>
            @if(!in_array($order->status, ['Cancelled', 'Received', 'Delivered', 'Completed']))
                <form action="{{ route('admin.adc-orders.cancel', $order->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to cancel this ADC Order? All items will be cancelled and refund will be credited to ADC wallet.');">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-danger">
                        <iconify-icon icon="solar:close-circle-bold" class="align-middle me-1"></iconify-icon> Cancel Order
                    </button>
                </form>
            @endif
            <span class="badge {{ $order->payment_status === 'Paid' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} fs-13 px-3 py-1">
                Payment: {{ $order->payment_status }}
            </span>
            <span class="badge {{ $order->status === 'Cancelled' ? 'bg-danger-subtle text-danger' : ($order->status === 'Dispatched' || $order->status === 'Delivered' || $order->status === 'Received' ? 'bg-success-subtle text-success' : ($order->status === 'Partially Dispatched' ? 'bg-info-subtle text-info' : 'bg-primary-subtle text-primary')) }} fs-13 px-3 py-1">
                Order Status: {{ $order->status }}
            </span>
        </div>
    </div>

    <!-- Header Summary Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-center">
                <div class="col-md-3 border-end">
                    <span class="text-muted small d-block mb-1">Order Reference</span>
                    <h4 class="fw-bold text-slate-800 mb-0">#{{ $order->order_number }}</h4>
                    <span class="text-muted small">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</span>
                </div>
                <div class="col-md-3 border-end">
                    <span class="text-muted small d-block mb-1">Tax Invoice</span>
                    @if($order->invoice && $order->invoice->invoice_number)
                        <div class="fw-bold text-success font-monospace mb-0 fs-15">#{{ $order->invoice->invoice_number }}</div>
                        <span class="text-muted small">Dated {{ \Carbon\Carbon::parse($order->invoice->invoice_date)->format('d M Y') }}</span>
                    @else
                        <div class="d-flex align-items-center gap-1.5 mt-0.5">
                            <span class="badge bg-light text-muted border fs-11">Pending Generation</span>
                            <a href="{{ route('admin.adc-orders.invoice', $order->id) }}" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-1.5 fs-11">Generate</a>
                        </div>
                    @endif
                </div>
                <div class="col-md-3 border-end">
                    <span class="text-muted small d-block mb-1">ADC Coordinator</span>
                    <div class="fw-bold text-slate-800">{{ $order->adc?->name ?? 'N/A' }}</div>
                    <div class="text-muted small">
                        <iconify-icon icon="solar:phone-bold-duotone" class="align-middle me-1"></iconify-icon>
                        {{ $order->adc?->mobile ?? 'N/A' }} | {{ $order->adc?->email ?? 'N/A' }}
                    </div>
                </div>
                <div class="col-md-3">
                    <span class="text-muted small d-block mb-1">Fulfillment Routing</span>
                    <div class="d-flex align-items-center gap-2">
                        @if($order->fulfillment_type === 'physical')
                            <span class="badge bg-info-subtle text-info px-2.5 py-1 fs-12">
                                <iconify-icon icon="solar:box-bold-duotone" class="align-middle me-1"></iconify-icon> Physical DDC
                            </span>
                        @else
                            <span class="badge bg-warning-subtle text-warning px-2.5 py-1 fs-12">
                                <iconify-icon icon="solar:delivery-bold-duotone" class="align-middle me-1"></iconify-icon> Virtual (Seller Direct)
                            </span>
                        @endif
                        <span class="text-muted small">({{ $order->routing_logic ?? 'Standard Routing' }})</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        <!-- Main Column: Packages & Items -->
        <div class="col-lg-8">
            @php
                $physicalItems = $order->items->where('fulfillment_type', 'physical');
                $virtualItems  = $order->items->where('fulfillment_type', 'virtual');
                $virtualGrouped = $virtualItems->groupBy('purchase_order_id');
            @endphp

            <!-- Physical DDC Package Card -->
            @if($physicalItems->count() > 0)
                <div class="card border-0 shadow-sm mb-4 border-start border-4 border-info">
                    <div class="card-header bg-light bg-opacity-50 py-3 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="fw-bold text-info mb-0 d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:box-bold-duotone" class="fs-5"></iconify-icon>
                                Package 1: Physical DDC Inventory Fulfillment
                            </h6>
                            <span class="text-muted small">Assigned DDC: {{ $order->assignedDdc?->ddcProfile?->company_name ?: ($order->assignedDdc?->name ?? $order->selectedDdc?->name ?? 'Default DDC') }}</span>
                        </div>
                        @php $physStatus = $physicalItems->first()?->status ?? 'Pending'; @endphp
                        <span class="badge {{ $physStatus === 'Dispatched' || $physStatus === 'Received' ? 'bg-success' : ($physStatus === 'Cancelled' ? 'bg-danger' : 'bg-warning text-dark') }} px-3 py-1">
                            {{ $physStatus === 'Pending' ? 'Stock Reserved' : $physStatus }}
                        </span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light bg-opacity-30">
                                    <tr>
                                        <th class="ps-3">Product Name</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-center">Item Status</th>
                                        <th class="text-end pe-3">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($physicalItems as $item)
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-semibold text-slate-800">{{ $item->product_name ?? $item->product?->product_name ?? $item->product?->name }}</div>
                                                <div class="text-muted small">Code: {{ $item->product_code }} | Seller: {{ $item->seller?->company_name ?: $item->seller?->name }}</div>
                                            </td>
                                            <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                            <td class="text-end">₹{{ number_format($item->price, 2) }}</td>
                                            <td class="text-center">
                                                @if($item->is_stocked_in || $item->status === 'Received')
                                                    <span class="badge bg-success-subtle text-success px-2 py-0.5 fs-11">Stocked In / Received</span>
                                                @elseif($item->status === 'Dispatched')
                                                    <span class="badge bg-warning-subtle text-warning px-2 py-0.5 fs-11">Dispatched</span>
                                                @else
                                                    <span class="badge bg-info-subtle text-info px-2 py-0.5 fs-11">{{ $item->status ?? 'Stock Reserved' }}</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-3 fw-bold text-primary">₹{{ number_format($item->total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Virtual Seller Packages Cards -->
            @foreach($virtualGrouped as $poId => $itemsList)
                @php
                    $po = $itemsList->first()?->purchaseOrder;
                    $seller = $itemsList->first()?->seller;
                    $pkgNum = ($physicalItems->count() > 0 ? 1 : 0) + $loop->iteration;
                @endphp
                <div class="card border-0 shadow-sm mb-4 border-start border-4 border-warning">
                    <div class="card-header bg-light bg-opacity-50 py-3 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="fw-bold text-warning-emphasis mb-0 d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:delivery-bold-duotone" class="fs-5"></iconify-icon>
                                Package {{ $pkgNum }}: Direct Seller Delivery
                            </h6>
                            <span class="text-muted small">Seller: <strong>{{ $seller?->company_name ?: $seller?->name }}</strong></span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            @if($po)
                                <a href="{{ route('admin.purchase-orders.show', $po->id) }}" class="btn btn-sm btn-outline-primary py-0.5 px-2 fs-12">
                                    PO #{{ $po->po_number }}
                                </a>
                            @endif
                            <span class="badge {{ $po?->status === 'Dispatched' || $po?->status === 'Received' ? 'bg-success' : ($po?->status === 'Rejected' || $po?->status === 'Cancelled' ? 'bg-danger' : 'bg-info') }} px-2.5 py-1">
                                {{ $po?->status ?? 'Pending' }}
                            </span>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light bg-opacity-30">
                                    <tr>
                                        <th class="ps-3">Product Name</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-end">Unit Price</th>
                                        <th class="text-center">Item Status</th>
                                        <th class="text-end pe-3">Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($itemsList as $item)
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-semibold text-slate-800">{{ $item->product_name ?? $item->product?->product_name ?? $item->product?->name }}</div>
                                                <div class="text-muted small">Code: {{ $item->product_code }}</div>
                                            </td>
                                            <td class="text-center fw-bold">{{ $item->quantity }}</td>
                                            <td class="text-end">₹{{ number_format($item->price, 2) }}</td>
                                            <td class="text-center">
                                                @if($item->is_stocked_in || $item->status === 'Received')
                                                    <span class="badge bg-success-subtle text-success px-2 py-0.5 fs-11">Stocked In / Received</span>
                                                @elseif($item->status === 'Dispatched')
                                                    <span class="badge bg-warning-subtle text-warning px-2 py-0.5 fs-11">Dispatched</span>
                                                @else
                                                    <span class="badge bg-info-subtle text-info px-2 py-0.5 fs-11">{{ $item->status ?? 'Pending' }}</span>
                                                @endif
                                            </td>
                                            <td class="text-end pe-3 fw-bold text-primary">₹{{ number_format($item->total, 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endforeach

            <!-- Generated Purchase Orders Card -->
            @if($order->purchaseOrders->count() > 0)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white py-3">
                        <h6 class="fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2">
                            <iconify-icon icon="solar:cart-large-bold-duotone" class="text-primary fs-5"></iconify-icon>
                            Generated Purchase Orders ({{ $order->purchaseOrders->count() }})
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light bg-opacity-50">
                                    <tr>
                                        <th class="ps-3">PO Number</th>
                                        <th>Seller</th>
                                        <th class="text-center">Amount</th>
                                        <th class="text-center">Status</th>
                                        <th class="text-end pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($order->purchaseOrders as $po)
                                        <tr>
                                            <td class="ps-3 fw-bold text-primary">#{{ $po->po_number }}</td>
                                            <td>{{ $po->seller?->company_name ?: $po->seller?->name }}</td>
                                            <td class="text-center fw-bold">₹{{ number_format($po->total_amount, 2) }}</td>
                                            <td class="text-center">
                                                <span class="badge bg-info-subtle text-info px-2 py-0.5 fs-11">{{ $po->status }}</span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <a href="{{ route('admin.purchase-orders.show', $po->id) }}" class="btn btn-sm btn-outline-primary py-0.5 px-2 fs-12">
                                                    View PO
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <!-- Sidebar Column: Addresses & Summary -->
        <div class="col-lg-4">
            <!-- Tax Invoice Card -->
            <div class="card border-0 shadow-sm mb-4 border-start border-4 border-success">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:document-text-bold-duotone" class="text-success fs-5"></iconify-icon>
                        Official Tax Invoice
                    </h6>
                    @if($order->invoice && $order->invoice->invoice_number)
                        <span class="badge bg-success-subtle text-success fs-11 font-monospace">
                            {{ $order->invoice->invoice_number }}
                        </span>
                    @endif
                </div>
                <div class="card-body">
                    @if($order->invoice && $order->invoice->invoice_number)
                        <div class="mb-2">
                            <span class="text-muted small d-block">Invoice Number:</span>
                            <span class="fw-bold text-slate-800 font-monospace fs-15">#{{ $order->invoice->invoice_number }}</span>
                        </div>
                        <div class="mb-3">
                            <span class="text-muted small d-block">Invoice Date:</span>
                            <span class="fw-semibold text-slate-700 small">{{ \Carbon\Carbon::parse($order->invoice->invoice_date)->format('d M Y') }}</span>
                        </div>
                    @else
                        <p class="text-muted small mb-3">
                            Tax Invoice is available for this order. Click below to generate, view or print the official invoice copy.
                        </p>
                    @endif
                    <a href="{{ route('admin.adc-orders.invoice', $order->id) }}" target="_blank" class="btn btn-success w-100 py-2 d-inline-flex align-items-center justify-content-center gap-1.5 fw-semibold shadow-sm">
                        <iconify-icon icon="solar:printer-bold" class="fs-18"></iconify-icon>
                        View / Print Tax Invoice
                    </a>
                </div>
            </div>

            <!-- Shipping & Delivery Address Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:map-point-bold-duotone" class="text-danger fs-5"></iconify-icon>
                        Delivery Information
                    </h6>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <span class="text-muted small d-block fw-semibold mb-1">Shipping Address</span>
                        <p class="text-slate-700 mb-0 small" style="white-space: pre-line;">{{ $order->shipping_address ?? 'No address recorded' }}</p>
                    </div>
                    <div class="row g-2 pt-2 border-top">
                        <div class="col-6">
                            <span class="text-muted small d-block">District</span>
                            <span class="fw-semibold text-slate-800 small">{{ $order->delivery_district ?? '—' }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block">State</span>
                            <span class="fw-semibold text-slate-800 small">{{ $order->delivery_state ?? '—' }}</span>
                        </div>
                        <div class="col-6 mt-2">
                            <span class="text-muted small d-block">Pincode</span>
                            <span class="fw-semibold text-slate-800 small">{{ $order->delivery_pincode ?? '—' }}</span>
                        </div>
                        <div class="col-6 mt-2">
                            <span class="text-muted small d-block">Payment Method</span>
                            <span class="fw-semibold text-slate-800 small">{{ $order->payment_method ?? 'Wallet' }}</span>
                        </div>
                    </div>
                    @if($order->notes)
                        <div class="mt-3 pt-2 border-top">
                            <span class="text-muted small d-block fw-semibold mb-1">Order Notes</span>
                            <div class="p-2 bg-light rounded small text-slate-600">{{ $order->notes }}</div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Financial Summary Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:bill-list-bold-duotone" class="text-success fs-5"></iconify-icon>
                        Financial Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Subtotal (Excl. GST)</span>
                        <span class="fw-semibold text-slate-700">₹{{ number_format($order->subtotal_excl_gst, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Total GST</span>
                        <span class="fw-semibold text-slate-700">₹{{ number_format($order->total_gst, 2) }}</span>
                    </div>
                    @if($order->total_mbp_coins > 0)
                        <div class="d-flex justify-content-between mb-2 text-success">
                            <span class="small">MBP Coins Earned</span>
                            <span class="fw-bold">+{{ number_format($order->total_mbp_coins, 2) }} Coins</span>
                        </div>
                    @endif
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center pt-1">
                        <span class="fw-bold text-slate-800">Grand Total</span>
                        <span class="fw-bold fs-4 text-primary">₹{{ number_format($order->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- MBP Coins Distribution (38% & 62% Split) Card -->
            <div class="card border-0 shadow-sm mb-4 border-start border-4 border-warning">
                <div class="card-header bg-white py-3">
                    <h6 class="fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:stars-bold-duotone" class="text-warning fs-5"></iconify-icon>
                        MBP Coins Share Breakdown
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">MBP 38% Share Pool</span>
                        <span class="fw-bold text-amber-700">₹{{ number_format($mbp38Total, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">MBP 62% Share Pool</span>
                        <span class="fw-bold text-amber-700">₹{{ number_format($mbp62Total, 2) }}</span>
                    </div>
                    <hr class="my-2">
                    <div class="d-flex justify-content-between align-items-center pt-1">
                        <span class="fw-bold text-slate-800 small">Total MBP 90% Pool</span>
                        <span class="fw-bold text-warning-emphasis">₹{{ number_format($mbp90Total, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- AMA Incentive Holds & Releases Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:medal-star-bold-duotone" class="text-primary fs-5"></iconify-icon>
                        AMA Incentives (SSI / FSI)
                    </h6>
                    <span class="badge bg-primary-subtle text-primary fs-11">
                        AMA: {{ $order->advisor_code ?? $order->adc?->adcProfile?->advisor_code ?? $order->adc?->advisor_code ?? 'N/A' }}
                    </span>
                </div>
                <div class="card-body p-0">
                    @if($incentiveHolds->count() > 0 || $incentiveReleases->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-sm align-middle mb-0" style="font-size: 12px;">
                                <thead class="bg-light">
                                    <tr>
                                        <th class="ps-3">Type</th>
                                        <th class="text-end">Hold Amt</th>
                                        <th class="text-end">Released</th>
                                        <th class="text-center pe-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($incentiveHolds as $hold)
                                        <tr>
                                            <td class="ps-3">
                                                <span class="badge {{ $hold->type === 'SSI' ? 'bg-primary-subtle text-primary' : 'bg-info-subtle text-info' }} px-1.5 py-0.5">
                                                    {{ $hold->type }} ({{ number_format($hold->percentage, 2) }}%)
                                                </span>
                                            </td>
                                            <td class="text-end fw-semibold">₹{{ number_format($hold->amount, 2) }}</td>
                                            <td class="text-end text-success fw-semibold">₹{{ number_format($hold->released_amount, 2) }}</td>
                                            <td class="text-center pe-3">
                                                <span class="badge {{ $hold->status === 'Fully_Released' ? 'bg-success' : ($hold->status === 'Partial_Released' ? 'bg-info' : 'bg-warning text-dark') }} fs-10 px-1.5 py-0.5">
                                                    {{ str_replace('_', ' ', $hold->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-3 text-muted small text-center">No incentive holds/releases created for this order yet.</div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
