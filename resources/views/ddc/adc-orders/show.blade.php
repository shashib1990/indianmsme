@extends('layouts.ddc')

@section('title', 'ADC Order Details — ' . $order->order_number)
@section('page-title', 'ADC Order Details')

@section('content')
<div class="row">
    <div class="col-12 mb-3">
        <a href="{{ route('ddc.adc-orders.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Back to ADC Orders
        </a>
    </div>
</div>

<div class="row">
    <!-- Main Order Details & Items -->
    <div class="col-lg-8">
        <!-- Order Header Card -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title mb-1">Order #{{ $order->order_number }}</h5>
                    <p class="text-muted small mb-0">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</p>
                </div>
                <div class="text-end">
                    <span class="badge {{ $order->payment_status === 'Paid' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} fs-13 px-3 py-1 mb-1">
                        Payment: {{ $order->payment_status }}
                    </span><br>
                    <span class="badge bg-primary-subtle text-primary fs-12 px-2.5 py-0.5">
                        Status: {{ $order->status }}
                    </span>
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">ADC Customer Name</span>
                        <strong class="text-dark">{{ $order->adc?->name ?? 'ADC User' }}</strong>
                        <span class="text-muted small d-block">{{ $order->adc?->mobile }} | {{ $order->adc?->email }}</span>
                    </div>
                    <div class="col-sm-6">
                        <span class="text-muted small d-block">Payment Method</span>
                        <strong class="text-dark">{{ $order->payment_method ?? 'Wallet' }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Items Table -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Ordered Items ({{ $order->items->count() }})</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Product Details</th>
                                <th>Seller</th>
                                <th class="text-center">Reserved Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end">GST</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($order->items as $item)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="bg-light rounded overflow-hidden d-flex align-items-center justify-content-center flex-shrink-0 border"
                                             style="width: 44px; height: 44px; min-width: 44px;">
                                            @if($item->product?->featured_image)
                                                <img src="{{ filter_var($item->product->featured_image, FILTER_VALIDATE_URL) ? $item->product->featured_image : asset('storage/' . $item->product->featured_image) }}"
                                                     alt="Product Image" class="img-fluid object-fit-contain p-1" style="max-height: 100%; max-width: 100%;"
                                                     onerror="this.onerror=null; this.src='{{ asset('admin-assets/images/IndianMSME.png') }}';">
                                            @else
                                                <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="text-muted fs-22"></iconify-icon>
                                            @endif
                                        </div>
                                        <div class="flex-grow-1" style="min-width: 0;">
                                            <h6 class="mb-0 fs-14 fw-semibold text-dark text-wrap" style="word-break: break-word;">{{ $item->product_name ?? $item->product?->product_name ?? 'Product' }}</h6>
                                            <span class="text-muted small d-block">Code: {{ $item->product_code ?? 'N/A' }} | HSN: {{ $item->hsn_code ?? 'N/A' }}</span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="small text-dark fw-medium">
                                        {{ $item->seller ? ($item->seller->company_name ?: $item->seller->name) : 'Seller' }}
                                    </span>
                                </td>
                                <td class="text-center fw-bold">
                                    {{ $item->quantity }}
                                    @if($item->status === 'Dispatched')
                                        <br><span class="badge bg-primary-subtle text-primary fs-11">Dispatched / Sold</span>
                                    @elseif($item->status === 'Received')
                                        <br><span class="badge bg-success-subtle text-success fs-11">Received</span>
                                    @elseif($item->status === 'Cancelled')
                                        <br><span class="badge bg-danger-subtle text-danger fs-11">Released</span>
                                    @else
                                        <br><span class="badge bg-warning-subtle text-warning fs-11">Processing / Reserved</span>
                                    @endif
                                </td>
                                <td class="text-end">₹{{ number_format($item->price, 2) }}</td>
                                <td class="text-end">
                                    ₹{{ number_format($item->gst_amount, 2) }}<br>
                                    <span class="text-muted small">({{ $item->gst_rate }}%)</span>
                                </td>
                                <td class="text-end fw-bold text-primary">₹{{ number_format($item->total, 2) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot class="table-light">
                            @php
                                $ddcSubtotalExclGst = $order->items->sum('taxable_amount');
                                $ddcTotalGst        = $order->items->sum('gst_amount');
                                $ddcGrandTotal      = $order->items->sum('total');
                            @endphp
                            @if($ddcSubtotalExclGst > 0)
                            <tr>
                                <td colspan="5" class="text-end fw-semibold">Subtotal (Excl. GST):</td>
                                <td class="text-end fw-semibold">₹{{ number_format($ddcSubtotalExclGst, 2) }}</td>
                            </tr>
                            @endif
                            @if($ddcTotalGst > 0)
                            <tr>
                                <td colspan="5" class="text-end fw-semibold">Total GST:</td>
                                <td class="text-end fw-semibold">₹{{ number_format($ddcTotalGst, 2) }}</td>
                            </tr>
                            @endif
                            <tr>
                                <td colspan="5" class="text-end fw-bold fs-15">Grand Total:</td>
                                <td class="text-end fw-bold fs-15 text-primary">₹{{ number_format($ddcGrandTotal, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Sidebar Info (Tax Invoice, Delivery & Actions) -->
    <div class="col-lg-4">
        <!-- Tax Invoice Card (Generated upon Dispatch) -->
        @if(isset($invoice) && $invoice)
        <div class="card mb-4 border-start border-4 border-success shadow-sm">
            <div class="card-header bg-white py-3 d-flex align-items-center justify-content-between">
                <h6 class="fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:document-text-bold-duotone" class="text-success fs-5"></iconify-icon>
                    Tax Invoice Generated
                </h6>
                <span class="badge bg-success-subtle text-success fs-12 px-2.5 py-1">
                    {{ $invoice->invoice_number }}
                </span>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="text-muted small d-block">Invoice Number:</span>
                    <h6 class="fw-bold text-dark mb-1">{{ $invoice->invoice_number }}</h6>
                    <span class="text-muted small d-block">Date: {{ \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') }}</span>
                </div>
                <a href="{{ route('ddc.adc-orders.invoice', $order->id) }}" target="_blank" class="btn btn-success w-100 d-inline-flex align-items-center justify-content-center gap-1.5 shadow-sm">
                    <iconify-icon icon="solar:printer-bold-duotone" class="fs-18"></iconify-icon>
                    View / Print Tax Invoice
                </a>
            </div>
        </div>
        @endif

        <!-- Order Fulfillment & Stock Management Actions -->
        @php
            $pendingItems = $order->items->whereIn('status', ['Pending', 'Processing'])->values();
            $hasPendingItems = $pendingItems->count() > 0;
            $isFullyDispatchedOrCancelled = $order->items->whereIn('status', ['Dispatched', 'Cancelled'])->count() === $order->items->count();
        @endphp

        @if($hasPendingItems && !$isFullyDispatchedOrCancelled && $order->status !== 'Dispatched' && $order->status !== 'Cancelled' && $order->status !== 'Delivered')
        <div class="card mb-4">
            <div class="card-header bg-light">
                <h5 class="card-title mb-0">Order Actions</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('ddc.adc-orders.dispatch', $order->id) }}" method="POST" class="mb-3">
                    @csrf
                    @foreach($pendingItems as $idx => $item)
                        <input type="hidden" name="items[{{ $idx }}][item_id]" value="{{ $item->id }}">
                        <input type="hidden" name="items[{{ $idx }}][dispatch_qty]" value="{{ (float)$item->quantity }}">
                    @endforeach
                    <button type="submit" class="btn btn-success w-100 mb-2" onclick="return confirm('Confirm dispatch? Reserved quantity will be released, Physical Stock reduced, and quantity marked as Sold.')">
                        <i class="bx bx-check-circle me-1"></i> Confirm Dispatch & Mark Sold
                    </button>
                </form>

                <form action="{{ route('ddc.adc-orders.cancel', $order->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-danger w-100" onclick="return confirm('Cancel physical DDC items? Reserved stock will be released and amount refunded to ADC.')">
                        <i class="bx bx-x-circle me-1"></i> Cancel Order & Release Stock
                    </button>
                </form>
            </div>
        </div>
        @endif

        <!-- Shipping Address -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Delivery Information</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-1"><i class="bx bx-map me-1 text-primary"></i> Shipping Address:</p>
                <p class="fw-medium text-dark mb-3">{{ $order->shipping_address }}</p>

                @if($order->delivery_pincode)
                <p class="text-muted small mb-1">Pincode / Location:</p>
                <p class="fw-medium text-dark mb-0">{{ $order->delivery_pincode }} ({{ $order->delivery_district ?? '' }}, {{ $order->delivery_state ?? '' }})</p>
                @endif

                @if($order->notes)
                <hr>
                <p class="text-muted small mb-1">Order Notes:</p>
                <p class="text-dark small mb-0">{{ $order->notes }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
