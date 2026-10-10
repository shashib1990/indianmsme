@extends('layouts.admin')

@section('title', 'Purchase Order Details')
@section('page-title', 'PO Details')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">Purchase Order: #{{ $purchaseOrder->po_number }}</h4>
                            <p class="text-muted small mb-0">Monitor the details, item-wise status, payment tracking, and logistics routing for this Purchase Order.</p>
                        </div>
                        <div class="col-md-6 text-md-end d-flex justify-content-md-end gap-2">
                            @if(!in_array($purchaseOrder->status, ['Cancelled', 'Rejected', 'Received', 'Dispatched']) && $purchaseOrder->dispatch_status !== 'Dispatched' && $purchaseOrder->payment_status !== 'Paid')
                                <form action="{{ route('admin.purchase-orders.cancel', $purchaseOrder->id) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-danger confirm-action" data-confirm-message="Are you sure you want to cancel this Purchase Order? All items will be rejected.">
                                        <iconify-icon icon="solar:close-circle-bold" class="align-middle me-1"></iconify-icon>
                                        Cancel Purchase Order
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('admin.purchase-orders.index') }}" class="btn btn-outline-secondary">
                                <iconify-icon icon="solar:arrow-left-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                                Back to PO List
                            </a>
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

    @if($purchaseOrder->adcOrder)
        <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #4f46e5 !important;">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <h5 class="fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:bag-check-bold-duotone" class="text-primary fs-4"></iconify-icon>
                        Linked ADC Order Information
                    </h5>
                    <a href="{{ route('admin.adc-orders.show', $purchaseOrder->adcOrder->id) }}" class="btn btn-sm btn-primary py-1 px-3">
                        <iconify-icon icon="solar:eye-bold-duotone" class="align-middle me-1"></iconify-icon> View Full ADC Order
                    </a>
                </div>
                <div class="row g-3">
                    <div class="col-md-3">
                        <span class="text-muted small d-block">ADC Order Number</span>
                        <a href="{{ route('admin.adc-orders.show', $purchaseOrder->adcOrder->id) }}" class="fw-bold text-primary">
                            #{{ $purchaseOrder->adcOrder->order_number }}
                        </a>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted small d-block">Canteen / ADC Name</span>
                        @php
                            $adcUser = $purchaseOrder->adcOrder->adc;
                            $adcProf = $adcUser?->profile ?? $adcUser?->adcProfile;
                            $adcIdVal = $adcProf?->adc_code ?: ($adcUser?->adc_code ?: ($adcProf?->code ?: ($adcUser ? ('ADC' . str_pad($adcUser->id, 3, '0', STR_PAD_LEFT)) : null)));
                            $canteenName = $adcProf?->company_name ?: ($adcProf?->trade_name ?: ($adcUser?->name ?? 'N/A'));
                        @endphp
                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                            @if($adcIdVal)
                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-1.5 py-0.5 fs-11 font-monospace" title="ADC ID">{{ $adcIdVal }}</span>
                            @endif
                            <span class="fw-semibold text-slate-800">{{ $canteenName }}</span>
                        </div>
                        <span class="small text-muted d-block mt-0.5">{{ $adcUser?->name }} ({{ $adcUser?->mobile }})</span>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted small d-block">Delivery Destination</span>
                        <span class="fw-semibold text-slate-800 d-block">{{ $purchaseOrder->adcOrder->delivery_district }}, {{ $purchaseOrder->adcOrder->delivery_state }}</span>
                        <span class="small text-muted">PIN: {{ $purchaseOrder->adcOrder->delivery_pincode }}</span>
                    </div>
                    <div class="col-md-3">
                        <span class="text-muted small d-block">ADC Order Status</span>
                        <span class="badge {{ $purchaseOrder->adcOrder->status === 'Dispatched' || $purchaseOrder->adcOrder->status === 'Received' || $purchaseOrder->adcOrder->status === 'Delivered' ? 'bg-success-subtle text-success' : 'bg-primary-subtle text-primary' }} px-2.5 py-1">
                            {{ $purchaseOrder->adcOrder->status }}
                        </span>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <div class="row g-4">
        <!-- Left: Order Info & Items -->
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold text-slate-800 mb-0">Requested Products</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light bg-opacity-50 border-top border-bottom">
                                <tr>
                                    <th class="ps-3" style="width: 250px;">Product Name</th>
                                    <th class="text-end">DDC Price</th>
                                    <th class="text-end">Seller Price</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">DDC Total</th>
                                    <th class="text-end">Seller Total</th>
                                    <th class="pe-3 text-center" style="width: 150px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseOrder->items as $item)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-semibold text-slate-800">{{ $item->product?->product_name }}</div>
                                            <div class="text-muted small">UOM: {{ $item->unit_of_measure ?? ($item->product?->unit_of_measure ?? '—') }}</div>
                                        </td>
                                        <td class="text-end fw-semibold">₹{{ number_format($item->price, 2) }}</td>
                                        <td class="text-end fw-semibold text-muted">₹{{ number_format($item->seller_price > 0 ? $item->seller_price : $item->price, 2) }}</td>
                                        <td class="text-center">{{ $item->quantity }} {{ $item->unit_of_measure ?? ($item->product?->unit_of_measure ?? 'Units') }}</td>
                                        <td class="text-end fw-bold text-slate-800">₹{{ number_format($item->total, 2) }}</td>
                                        <td class="text-end fw-bold text-muted">₹{{ number_format($item->seller_total > 0 ? $item->seller_total : $item->total, 2) }}</td>
                                        <td class="pe-3 text-center">
                                            @if($item->status === 'Pending')
                                                <span class="badge bg-warning-subtle text-warning px-2 py-1 fs-11">Pending</span>
                                            @elseif($item->status === 'Accepted')
                                                <span class="badge bg-success-subtle text-success px-2 py-1 fs-11">Accepted</span>
                                            @elseif($item->status === 'Rejected')
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-11" data-bs-toggle="tooltip" title="{{ $item->remarks }}">
                                                    Rejected
                                                </span>
                                                @if($item->remarks)
                                                    <div class="text-danger small mt-1 fs-10" style="font-size: 10px;">"{{ $item->remarks }}"</div>
                                                @endif
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Routing Details Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3 border-bottom">
                    <h6 class="fw-bold text-slate-800 mb-0">Delivery & Logistics Routing</h6>
                </div>
                <div class="card-body">
                    <div class="row g-4">
                        <div class="col-md-6 border-end">
                            <h6 class="fw-bold text-slate-800 mb-2">DDC Shipping Destination</h6>
                            <table class="table table-borderless table-sm mb-0">
                                <tr>
                                    <td class="text-muted" style="width: 120px;">DDC Name:</td>
                                    <td class="fw-semibold text-slate-800">{{ $purchaseOrder->ddc?->name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">Pincode:</td>
                                    <td><code>{{ $purchaseOrder->ddc?->profile?->pincode ?? '—' }}</code></td>
                                </tr>
                                <tr>
                                    <td class="text-muted">District:</td>
                                    <td>{{ $purchaseOrder->ddc?->profile?->district ?? '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted">State:</td>
                                    <td>{{ $purchaseOrder->ddc?->profile?->state ?? '—' }}</td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-slate-800 mb-2">Assigned Pickup Store</h6>
                            @if($purchaseOrder->pickupStore)
                                <table class="table table-borderless table-sm mb-0">
                                    <tr>
                                        <td class="text-muted" style="width: 120px;">Store Name:</td>
                                        <td class="fw-semibold text-slate-800">{{ $purchaseOrder->pickupStore->pickup_store_name }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Store Owner:</td>
                                        <td class="fw-semibold text-slate-800">
                                            @if($purchaseOrder->pickupStore->owner)
                                                {{ $purchaseOrder->pickupStore->owner->company_name ?: $purchaseOrder->pickupStore->owner->name }}
                                                <span class="text-muted small">({{ ucfirst($purchaseOrder->pickupStore->owner_type->value ?? $purchaseOrder->pickupStore->owner_type) }})</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Store Code:</td>
                                        <td><span class="badge bg-primary-subtle text-primary">{{ $purchaseOrder->pickupStore->pickup_store_code }}</span></td>
                                    </tr>
                                    <tr>
                                        <td class="text-muted">Authorization:</td>
                                        <td>
                                            @if($purchaseOrder->pickupStore->can_receive_payment_and_dispatch)
                                                <span class="badge bg-success-subtle text-success px-2 py-0.5 fs-11">Authorized Store</span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning px-2 py-0.5 fs-11">Unauthorized Store</span>
                                            @endif
                                        </td>
                                    </tr>
                                </table>
                            @else
                                <div class="text-muted small py-3">
                                    <iconify-icon icon="solar:info-circle-bold" class="align-middle me-1"></iconify-icon>
                                    No pickup store was assigned. Seller will fulfill the order directly.
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Summary Sidebar -->
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3 border-bottom">
                    <h6 class="fw-bold text-slate-800 mb-0">Purchase Order Summary</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">PO Number</span>
                        <strong class="text-slate-800">#{{ $purchaseOrder->po_number }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">DDC Coordinator</span>
                        <span class="text-slate-700 fw-semibold">{{ $purchaseOrder->ddc?->name }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Seller Company</span>
                        <span class="text-slate-700 fw-semibold">{{ $purchaseOrder->seller?->company_name ?: $purchaseOrder->seller?->name }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Order Date</span>
                        <span class="text-slate-700">{{ $purchaseOrder->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                    
                    <hr class="text-muted opacity-20">

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">PO Status</span>
                        <span>
                            @if($purchaseOrder->status === 'Pending')
                                <span class="badge bg-warning-subtle text-warning">PO Created</span>
                            @elseif($purchaseOrder->status === 'Accepted')
                                <span class="badge bg-success-subtle text-success">Accepted</span>
                                                        @elseif($purchaseOrder->status === 'Rejected')
                                <span class="badge bg-danger-subtle text-danger">Rejected</span>
                            @elseif($purchaseOrder->status === 'Cancelled')
                                <span class="badge bg-danger text-white">Cancelled</span>
                            @elseif($purchaseOrder->status === 'Processing')
                                <span class="badge bg-primary-subtle text-primary">Processing</span>
                            @elseif($purchaseOrder->status === 'Dispatched')
                                <span class="badge bg-warning-subtle text-warning">Dispatched</span>
                            @elseif($purchaseOrder->status === 'Received')
                                <span class="badge bg-success-subtle text-success">Received</span>
                                                        @endif
                        </span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Payment Status</span>
                        <span>
                            @if($purchaseOrder->payment_status === 'Paid')
                                <span class="badge bg-success text-white">Paid</span>
                            @else
                                <span class="badge bg-danger-subtle text-danger">Unpaid</span>
                            @endif
                        </span>
                    </div>

                    @if($purchaseOrder->payment_reference)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">DDC Txn ID</span>
                            <code class="small fw-semibold">{{ $purchaseOrder->payment_reference }}</code>
                        </div>
                    @endif

                    @if($purchaseOrder->seller_payment_reference)
                        @php
                            $sideRefText = $purchaseOrder->seller_payment_reference;
                            $sideDecoded = json_decode($sideRefText, true);
                            if (json_last_error() === JSON_ERROR_NONE && is_array($sideDecoded)) {
                                $sideTxnId = $sideDecoded['utr'] ?? null;
                                $sideSlipUrl = $sideDecoded['slip'] ?? null;
                            } else {
                                $sideCombined = preg_match('/UTR:\s*(.*?)\s*\|\s*Slip:\s*(.*)/', $sideRefText, $sideMatches);
                                $sideTxnId = $sideCombined ? $sideMatches[1] : $sideRefText;
                                $sideSlipUrl = $sideCombined ? $sideMatches[2] : (\Illuminate\Support\Str::startsWith($sideRefText, ['http://', 'https://', 'seller_payments/', 'uploads/']) ? $sideRefText : null);
                                if (!$sideCombined && $sideSlipUrl) $sideTxnId = null;
                            }
                        @endphp
                        @if($sideTxnId)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Seller UTR</span>
                                <code class="small fw-semibold">{{ $sideTxnId }}</code>
                            </div>
                        @endif
                        @if($sideSlipUrl)
                            <div class="d-flex justify-content-between mb-2 align-items-center">
                                <span class="text-muted">Seller Payout Slip</span>
                                <a href="{{ \Illuminate\Support\Str::startsWith($sideSlipUrl, ['http://', 'https://']) ? $sideSlipUrl : asset('storage/' . $sideSlipUrl) }}" target="_blank" class="btn btn-xs btn-outline-primary py-0 px-1" style="font-size: 11px;">
                                    <iconify-icon icon="solar:document-bold-duotone" class="align-middle"></iconify-icon> View
                                </a>
                            </div>
                        @endif
                    @endif

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Verification</span>
                        <span>
                            @if($purchaseOrder->payment_verification_status === 'Pending')
                                <span class="badge bg-warning-subtle text-warning">Pending</span>
                            @elseif($purchaseOrder->payment_verification_status === 'Verified')
                                <span class="badge bg-success-subtle text-success">Verified</span>
                            @elseif($purchaseOrder->payment_verification_status === 'Rejected')
                                <span class="badge bg-danger-subtle text-danger">Rejected</span>
                            @endif
                        </span>
                    </div>

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Dispatch Status</span>
                        <span>
                            @if($purchaseOrder->dispatch_status === 'Pending')
                                <span class="badge bg-warning-subtle text-warning">Pending</span>
                            @elseif($purchaseOrder->dispatch_status === 'Dispatched')
                                <span class="badge bg-info-subtle text-info">Dispatched</span>
                            @elseif($purchaseOrder->dispatch_status === 'Delivered')
                                <span class="badge bg-success text-white">Delivered</span>
                            @endif
                        </span>
                    </div>

                    @if($purchaseOrder->rejection_reason)
                        <div class="mt-2 p-2 bg-danger bg-opacity-10 text-danger rounded small">
                            <strong>Reason:</strong> "{{ $purchaseOrder->rejection_reason }}"
                        </div>
                    @endif

                    <hr class="text-muted opacity-20">

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">DDC Grand Total</span>
                        <span class="fw-bold text-slate-800">₹{{ number_format($purchaseOrder->total_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Seller Grand Total</span>
                        <span class="fw-semibold text-slate-700">₹{{ number_format($purchaseOrder->seller_total_amount > 0 ? $purchaseOrder->seller_total_amount : $purchaseOrder->items->sum('seller_total'), 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2 border-top pt-2 mt-2">
                        <span class="text-muted">DDC Accepted Total</span>
                        <span class="fw-bold text-success">₹{{ number_format($purchaseOrder->accepted_amount, 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-0">
                        <span class="text-muted">Seller Accepted Total</span>
                        <span class="fw-semibold text-success">₹{{ number_format($purchaseOrder->seller_accepted_amount > 0 ? $purchaseOrder->seller_accepted_amount : $purchaseOrder->items->where('status', 'Accepted')->sum('seller_total'), 2) }}</span>
                    </div>

                    @if($purchaseOrder->status === 'Received')
                        <div class="mt-4 pt-3 border-top">
                            <form action="{{ route('admin.purchase-orders.complete', $purchaseOrder->id) }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-success w-100 fw-semibold py-2 confirm-action" data-confirm-message="Are you sure you want to mark this Purchase Order as Completed?">
                                    <iconify-icon icon="solar:check-circle-bold" class="align-middle me-1 fs-18"></iconify-icon>
                                    Mark as Completed
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            @if($purchaseOrder->invoice || in_array($purchaseOrder->status, ['Dispatched', 'Received']))
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white border-0 py-3 border-bottom">
                        <h6 class="fw-bold text-slate-800 mb-0">Invoices & Documents</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-3">
                            <!-- Seller Invoice -->
                            <div>
                                <h6 class="fw-bold text-slate-700 mb-1" style="font-size: 13px;">Seller Generated Invoice</h6>
                                @if($purchaseOrder->invoice && $purchaseOrder->invoice->invoice_number)
                                    <div class="small text-muted mb-2">
                                        <div>Number: <strong class="text-slate-800 font-monospace">{{ $purchaseOrder->invoice->invoice_number }}</strong></div>
                                        @if($purchaseOrder->invoice->invoice_date)
                                            <div>Date: {{ $purchaseOrder->invoice->invoice_date->format('d M Y') }}</div>
                                        @endif
                                    </div>
                                    <div class="d-flex gap-2">
                                        <a href="{{ route('admin.purchase-orders.invoice', $purchaseOrder->id) }}" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2 flex-grow-1" style="font-size:12px;">
                                            <iconify-icon icon="solar:eye-bold" class="align-middle me-1"></iconify-icon> View/Print
                                        </a>
                                        @if($purchaseOrder->invoice->invoice_file)
                                            <a href="{{ \Illuminate\Support\Str::startsWith($purchaseOrder->invoice->invoice_file, ['http://', 'https://']) ? $purchaseOrder->invoice->invoice_file : asset('storage/' . $purchaseOrder->invoice->invoice_file) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-1 px-2" style="font-size:12px;">
                                                <iconify-icon icon="solar:download-bold" class="align-middle"></iconify-icon>
                                            </a>
                                        @endif
                                    </div>
                                @else
                                    <div class="text-muted small">Not generated yet.</div>
                                @endif
                            </div>

                            <hr class="text-muted my-1 opacity-20">

                            <!-- DDC Invoice -->
                            <div>
                                <h6 class="fw-bold text-slate-700 mb-1" style="font-size: 13px;">DDC Uploaded Invoice</h6>
                                @if($purchaseOrder->invoice && $purchaseOrder->invoice->ddc_invoice_number)
                                    <div class="small text-muted mb-2">
                                        <div>Number: <strong class="text-slate-800 font-monospace">{{ $purchaseOrder->invoice->ddc_invoice_number }}</strong></div>
                                        @if($purchaseOrder->invoice->ddc_invoice_date)
                                            <div>Date: {{ $purchaseOrder->invoice->ddc_invoice_date->format('d M Y') }}</div>
                                        @endif
                                    </div>
                                    @if($purchaseOrder->invoice->ddc_invoice_file)
                                        <a href="{{ \Illuminate\Support\Str::startsWith($purchaseOrder->invoice->ddc_invoice_file, ['http://', 'https://']) ? $purchaseOrder->invoice->ddc_invoice_file : asset('storage/' . $purchaseOrder->invoice->ddc_invoice_file) }}" target="_blank" class="btn btn-sm btn-outline-primary w-100 py-1 px-2" style="font-size:12px;">
                                            <iconify-icon icon="solar:download-bold" class="align-middle me-1"></iconify-icon> Download DDC Invoice
                                        </a>
                                    @else
                                        <span class="text-muted small">No file uploaded.</span>
                                    @endif
                                @else
                                    <div class="text-muted small">No DDC invoice uploaded.</div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
