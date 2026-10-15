@extends('layouts.ddc')

@section('title', 'Purchase Order Details')
@section('page-title', 'PO Details')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">Purchase
                                Order Details</h4>
                            <p class="text-muted small mb-0">View product list, seller responses, and process wallet
                                payment.</p>
                        </div>
                        <div class="col-md-6 text-md-end d-flex justify-content-md-end gap-2">
                            @if(!in_array($purchaseOrder->status, ['Cancelled', 'Rejected', 'Received', 'Dispatched']) && $purchaseOrder->dispatch_status !== 'Dispatched')
                                <form action="{{ route('ddc.purchase-orders.cancel', $purchaseOrder->id) }}" method="POST"
                                    class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-outline-danger confirm-action" data-confirm-message="Are you sure you want to cancel this Purchase Order?">
                                        <iconify-icon icon="solar:close-circle-bold-duotone"
                                            class="align-middle me-1 fs-18"></iconify-icon>
                                        Cancel Purchase Order
                                    </button>
                                </form>
                            @endif
                            <a href="{{ route('ddc.purchase-orders.index') }}" class="btn btn-outline-primary">
                                <iconify-icon icon="solar:arrow-left-bold-duotone"
                                    class="align-middle me-1 fs-18"></iconify-icon>
                                Back to PO List
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    <div class="row g-4">
        <!-- Left: Order Info & Items -->
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm mb-4">
                <div
                    class="card-header bg-white border-0 py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold text-slate-800 mb-0">PO Items List</h6>
                    <span class="text-muted small">PO ID: <strong>{{ $purchaseOrder->po_number }}</strong></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light bg-opacity-50 border-top border-bottom">
                                <tr>
                                    <th class="ps-3">Product Name</th>
                                    <th>Seller</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Total</th>
                                    <th class="pe-3 text-center">Seller Response</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseOrder->items as $item)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-semibold text-slate-800">{{ $item->product_name ?? $item->product?->product_name }}</div>
                                            <div class="text-muted small">UOM: {{ $item->unit_of_measure ?? ($item->product?->unit_of_measure ?? '—') }}</div>
                                        </td>
                                        <td>
                                            <div class="small fw-semibold text-slate-700">
                                                {{ $item->seller?->company_name ?: $item->seller?->name }}</div>
                                            <div class="text-muted small" style="font-size: 11px;">Seller ID:
                                                {{ $item->seller?->seller_id }}</div>
                                        </td>
                                        <td class="text-end fw-semibold">₹{{ number_format($item->price, 2) }}</td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-end fw-bold text-slate-800">₹{{ number_format($item->total, 2) }}</td>
                                        <td class="pe-3 text-center">
                                            @if($item->status === 'Pending')
                                                <span class="badge bg-warning-subtle text-warning px-2 py-1 fs-11">Pending</span>
                                            @elseif($item->status === 'Accepted')
                                                <span class="badge bg-success-subtle text-success px-2 py-1 fs-11">Accepted</span>
                                            @elseif($item->status === 'Rejected')
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-11"
                                                    data-bs-toggle="tooltip" title="{{ $item->remarks }}">
                                                    Rejected
                                                </span>
                                                @if($item->remarks)
                                                    <div class="text-danger small" style="font-size: 10px;">{{ $item->remarks }}</div>
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
        </div>

        <!-- Right: Summary & Actions -->
        <div class="col-xl-4">
            <!-- Summary Info -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3 border-bottom">
                    <h6 class="fw-bold text-slate-800 mb-0">Purchase Order Summary</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">PO Number</span>
                        <strong class="text-slate-800">{{ $purchaseOrder->po_number }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Placed Date</span>
                        <span class="text-slate-700">{{ $purchaseOrder->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                    @if($purchaseOrder->ama_code)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">AMA Code</span>
                            <span class="text-slate-700 fw-semibold">{{ $purchaseOrder->ama_code }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Seller Response</span>
                        <span>
                            @php
                                $sellerResponse = 'Pending';
                                if ($purchaseOrder->status === 'Cancelled') {
                                    $sellerResponse = 'Cancelled';
                                } elseif ($purchaseOrder->status === 'Rejected') {
                                    $sellerResponse = 'Rejected';
                                } elseif (in_array($purchaseOrder->status, ['Accepted', 'Processing', 'Dispatched', 'Received'])) {
                                    $hasRejected = $purchaseOrder->items->where('status', 'Rejected')->count() > 0;
                                    $sellerResponse = $hasRejected ? 'Partially Accepted' : 'Accepted';
                                }
                            @endphp

                            @if($sellerResponse === 'Pending')
                                <span class="badge bg-warning-subtle text-warning">Pending Response</span>
                            @elseif($sellerResponse === 'Accepted')
                                <span class="badge bg-success-subtle text-success">Accepted</span>
                            @elseif($sellerResponse === 'Partially Accepted')
                                <span class="badge bg-info-subtle text-info">Partially Accepted</span>
                            @elseif($sellerResponse === 'Rejected')
                                <span class="badge bg-danger-subtle text-danger">Rejected</span>
                            @elseif($sellerResponse === 'Cancelled')
                                <span class="badge bg-danger text-white">Cancelled</span>
                            @endif
                        </span>
                    </div>
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
                            <span class="text-muted">Payment Ref</span>
                            <code class="small fw-semibold">{{ $purchaseOrder->payment_reference }}</code>
                        </div>
                    @endif

                    <hr class="text-muted opacity-20">

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Estimated Amount</span>
                        <span
                            class="fw-semibold text-slate-800">₹{{ number_format($purchaseOrder->total_amount, 2) }}</span>
                    </div>

                    @php
                        $acceptedAmount = $purchaseOrder->items->where('status', 'Accepted')->sum('total');
                        $pendingItemsCount = $purchaseOrder->items->where('status', 'Pending')->count();
                    @endphp

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Accepted Amount</span>
                        <span class="fw-bold text-success">₹{{ number_format($acceptedAmount, 2) }}</span>
                    </div>
                </div>
            </div>

            <!-- Payment Process Section -->
            @if($purchaseOrder->payment_status === 'Unpaid')
                <div class="card border-0 shadow-sm border-top border-3 border-primary">
                    <div class="card-header bg-white border-0 py-3 border-bottom">
                        <h6 class="fw-bold text-slate-800 mb-0">DDC Wallet Payment</h6>
                    </div>
                    <div class="card-body">
                        <div class="mb-3 text-center bg-light rounded p-3">
                            <span class="text-muted small d-block">Your Current Wallet Balance</span>
                            <h4 class="fw-bold text-primary mb-0">
                                ₹{{ number_format(auth('ddc')->user()->profile->wallet_balance, 2) }}</h4>
                        </div>

                        @if($pendingItemsCount > 0)
                            <div class="alert alert-warning border-0 small mb-0" role="alert">
                                <iconify-icon icon="solar:info-circle-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon>
                                Payment is disabled. Waiting for seller response on <strong>{{ $pendingItemsCount }}</strong>
                                pending product(s).
                            </div>
                        @else
                            <div class="alert alert-info border-0 small mb-3" role="alert">
                                <iconify-icon icon="solar:verified-check-bold" class="align-middle me-1 fs-16"></iconify-icon>
                                All sellers have responded. You can make payment for the accepted items.
                            </div>

                            <form action="{{ route('ddc.purchase-orders.pay', $purchaseOrder->id) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <div class="d-flex justify-content-between small text-muted mb-1">
                                        <span>Amount to Pay:</span>
                                        <strong class="text-slate-800">₹{{ number_format($acceptedAmount, 2) }}</strong>
                                    </div>
                                    <small class="text-muted d-block" style="font-size: 11px;">
                                        Note: Rejected products (if any) are excluded from the payment.
                                    </small>
                                </div>

                                <button type="submit" class="btn btn-success w-100 fw-semibold py-2 confirm-action"
                                    data-confirm-message="Are you sure you want to deduct ₹{{ number_format($acceptedAmount, 2) }} from your wallet to pay for this PO?">
                                    <iconify-icon icon="solar:wallet-money-bold-duotone"
                                        class="me-1 align-middle fs-18"></iconify-icon> Pay Now
                                    (₹{{ number_format($acceptedAmount, 2) }})
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endif

            @if($purchaseOrder->status === 'Dispatched')
                <div class="card border-0 shadow-sm border-top border-3 border-success mt-4">
                    <div class="card-header bg-white border-0 py-3 border-bottom">
                        <h6 class="fw-bold text-slate-800 mb-0">Delivery Confirmation</h6>
                    </div>
                    <div class="card-body">
                        <div class="alert alert-info border-0 small mb-3">
                            The seller has dispatched your package. Please confirm when you receive it.
                        </div>
                        <form action="{{ route('ddc.purchase-orders.received', $purchaseOrder->id) }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-success w-100 fw-semibold py-2">
                                <iconify-icon icon="solar:check-circle-bold-duotone"
                                    class="me-1 align-middle fs-18"></iconify-icon> Mark as Received
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            @if($purchaseOrder->status === 'Received' || $purchaseOrder->stock_received_by_ddc)
                @php
                    $hasShortageDamage = $purchaseOrder->items && $purchaseOrder->items->filter(fn($i) => ($i->shortage_qty > 0 || $i->damage_qty > 0))->isNotEmpty();
                    $hasExcess = $purchaseOrder->items && $purchaseOrder->items->filter(fn($i) => ($i->excess_qty > 0))->isNotEmpty();
                    $excessInvoice = $hasExcess ? \App\Models\Invoice::where('purchase_order_id', $purchaseOrder->id)->where('type', 'excess_stock_ddc')->first() : null;
                @endphp

                @if($hasShortageDamage)
                    <div class="card border-0 shadow-sm border-top border-3 border-warning mt-4">
                        <div class="card-header bg-white border-0 py-3 border-bottom d-flex align-items-center justify-content-between">
                            <h6 class="fw-bold text-slate-800 mb-0">Shortage &amp; Damage Claims</h6>
                            <span class="badge bg-warning text-dark">Discrepancy Recorded</span>
                        </div>
                        <div class="card-body">
                            @if($purchaseOrder->debitNote)
                                <div class="alert alert-info border-0 small mb-3">
                                    <iconify-icon icon="solar:info-circle-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon>
                                    Debit Note <strong>#{{ $purchaseOrder->debitNote->debit_note_number }}</strong> generated. Status: <strong>{{ $purchaseOrder->debitNote->status }}</strong>.
                                </div>
                                <a href="{{ route('ddc.debit-notes.show', $purchaseOrder->debitNote->id) }}" class="btn btn-info text-white w-100 py-2 fw-bold">
                                    <iconify-icon icon="solar:document-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                                    View Discrepancy
                                </a>
                            @else
                                <div class="alert alert-warning border-0 small mb-3">
                                    <iconify-icon icon="solar:danger-triangle-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon>
                                    Stock shortages or damages were recorded during stock-in. You can generate and send the claim to seller.
                                </div>
                                <a href="{{ route('ddc.purchase-orders.debit-note.create', $purchaseOrder->id) }}" class="btn btn-warning text-dark w-100 py-2 fw-bold">
                                    <iconify-icon icon="solar:shield-warning-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                                    Send Discrepancy to Seller
                                </a>
                            @endif
                        </div>
                    </div>
                @endif

                @if($hasExcess)
                    <div class="card border-0 shadow-sm border-top border-3 border-primary mt-4">
                        <div class="card-header bg-white border-0 py-3 border-bottom d-flex align-items-center justify-content-between">
                            <h6 class="fw-bold text-slate-800 mb-0">Excess Stock Received</h6>
                            <span class="badge bg-primary text-white">Excess Stock</span>
                        </div>
                        <div class="card-body">
                            @if($excessInvoice)
                                <div class="alert alert-success border-0 small mb-3">
                                    <iconify-icon icon="solar:check-circle-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon>
                                    Excess Stock Invoice <strong>#{{ $excessInvoice->invoice_number }}</strong> has been generated.
                                </div>
                                <a href="{{ route('ddc.excess-invoices.show', $excessInvoice->id) }}" class="btn btn-outline-success w-100 py-2 fw-bold">
                                    <iconify-icon icon="solar:document-text-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                                    View Excess Stock Invoice
                                </a>
                            @else
                                <div class="alert alert-primary border-0 small mb-3">
                                    <iconify-icon icon="solar:info-circle-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon>
                                    Excess stock items were received during stock-in. Submit to generate Tax Invoice.
                                </div>
                                <a href="{{ route('ddc.purchase-orders.excess-invoice.create', $purchaseOrder->id) }}" class="btn btn-primary w-100 py-2 fw-bold">
                                    <iconify-icon icon="solar:add-circle-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                                    Submit Excess Stock
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            @endif

            @if($purchaseOrder->invoice || in_array($purchaseOrder->status, ['Dispatched', 'Received']))
                <div class="card border-0 shadow-sm mt-4">
                    <div class="card-header bg-white border-0 py-3 border-bottom">
                        <h6 class="fw-bold text-slate-800 mb-0">Tax Invoice</h6>
                    </div>
                    <div class="card-body">
                        <a href="{{ route('ddc.purchase-orders.invoice', $purchaseOrder->id) }}" target="_blank"
                            class="btn btn-outline-primary w-100 py-2">
                            <iconify-icon icon="solar:document-text-bold-duotone"
                                class="align-middle me-1 fs-18"></iconify-icon>
                            View/Print Invoice
                        </a>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection