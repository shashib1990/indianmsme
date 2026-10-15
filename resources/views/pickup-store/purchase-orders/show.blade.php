@extends('layouts.pickup_store')

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
                            <p class="text-muted small mb-0">View product requirements, verify DDC payments, and complete
                                order dispatch.</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <a href="{{ route('store.purchase-orders.index') }}" class="btn btn-outline-primary">
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

    <div class="row g-4">
        <!-- Left: Order Info & Items -->
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm mb-4">
                <div
                    class="card-header bg-white border-0 py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold text-slate-800 mb-0">Requested Products</h6>
                    <span class="text-muted small">PO ID: <strong>{{ $purchaseOrder->po_number }}</strong></span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0 align-middle">
                            <thead class="bg-light bg-opacity-50 border-top border-bottom">
                                <tr>
                                    <th class="ps-3" style="width: 250px;">Product Name</th>
                                    <th class="text-end">Price</th>
                                    <th class="text-center">Quantity</th>
                                    <th class="text-end">Total</th>
                                    <th class="pe-3 text-center" style="width: 200px;">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($items as $item)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="fw-semibold text-slate-800">{{ $item->product_name ?? $item->product?->product_name }}</div>
                                            <div class="text-muted small">UOM: {{ $item->unit_of_measure ?? ($item->product?->unit_of_measure ?? '—') }}</div>
                                        </td>
                                        <td class="text-end fw-semibold">₹{{ number_format($item->seller_price ?? 0, 2) }}</td>
                                        <td class="text-center">{{ $item->quantity }}</td>
                                        <td class="text-end fw-bold text-slate-800">
                                            ₹{{ number_format($item->seller_total ?? 0, 2) }}</td>
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
                                                    <div class="text-danger small mt-1 fs-10" style="font-size: 10px;">
                                                        "{{ $item->remarks }}"</div>
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

            <!-- Workflow management based on Authorized/Unauthorized Store status -->
            @if($purchaseOrder->payment_status === 'Paid')
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3 border-bottom">
                        <h6 class="fw-bold text-slate-800 mb-0">Workflow Operations</h6>
                    </div>
                    <div class="card-body">
                        @if($store->can_receive_payment_and_dispatch)
                            <!-- Authorized Pickup Store: Full Operations allowed -->
                            <div class="row g-4">
                                <div class="col-md-6 border-end">
                                    <h6 class="fw-bold text-slate-800 mb-3">1. Payment Verification</h6>

                                    <div class="mb-3">
                                        <span class="text-muted small d-block mb-1">Payout Status from Admin:</span>
                                        @if($purchaseOrder->seller_payment_status === 'Paid')
                                            <span class="badge bg-success text-white px-2 py-1 fs-11">Paid</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-11">Unpaid / Processing</span>
                                        @endif
                                    </div>

                                    @if($purchaseOrder->seller_payment_status === 'Paid')
                                    @php
                                            $refText = $purchaseOrder->seller_payment_reference;
                                            $refData = null;
                                            $utr = null;
                                            $slipUrl = null;

                                            // Try JSON first (new format)
                                            $decoded = json_decode($refText, true);
                                            if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
                                                $refData = $decoded;
                                                $utr = $refData['utr'] ?? null;
                                                $slipUrl = $refData['slip'] ?? null;
                                            } else {
                                                // Legacy format: "UTR: xxx | Slip: url"
                                                $isCombined = preg_match('/UTR:\s*(.*?)\s*\|\s*Slip:\s*(.*)/', $refText, $matches);
                                                $utr = $isCombined ? $matches[1] : $refText;
                                                $slipUrl = $isCombined ? $matches[2] : (Str::startsWith($refText, ['http://', 'https://', 'seller_payments/', 'uploads/']) ? $refText : null);
                                                if (!$isCombined && !$slipUrl) $utr = $refText;
                                            }
                                        @endphp
                                        <div class="border rounded p-3 bg-light bg-opacity-50 mb-3">
                                            <span class="fw-semibold text-slate-800 d-block mb-2">Payment Reference:</span>

                                            @if($utr)
                                                <div class="mb-2">
                                                    <code class="d-block bg-white p-2 border rounded fw-semibold">UTR: {{ $utr }}</code>
                                                </div>
                                            @endif

                                            @if($slipUrl)
                                                <div class="mb-2">
                                                    <a href="{{ Str::startsWith($slipUrl, ['http://', 'https://']) ? $slipUrl : asset('storage/' . $slipUrl) }}"
                                                        target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2 mt-1">
                                                        <iconify-icon icon="solar:document-bold" class="align-middle me-1"></iconify-icon>
                                                        View Payout Slip
                                                    </a>
                                                </div>
                                            @endif

                                            <hr class="my-2 text-muted opacity-25">

                                            @if($purchaseOrder->payment_verification_status === 'Pending')
                                                <div class="bg-warning-subtle text-warning p-2 rounded small mb-2 fw-semibold">
                                                    Awaiting Your Verification
                                                </div>
                                                <div class="d-flex gap-2">
                                                    <form action="{{ route('store.purchase-orders.verify-payment', $purchaseOrder->id) }}"
                                                        method="POST" class="d-inline">
                                                        @csrf
                                                        <input type="hidden" name="action" value="Verify">
                                                        <button type="submit" class="btn btn-sm btn-success px-3 confirm-action"
                                                            data-confirm-message="Verify payment for this Purchase Order?">
                                                            Approve &amp; Verify
                                                        </button>
                                                    </form>

                                                    <button class="btn btn-sm btn-outline-danger px-3" type="button"
                                                        data-bs-toggle="collapse" data-bs-target="#paymentRejectCollapse">
                                                        Reject
                                                    </button>
                                                </div>

                                                <div class="collapse mt-2" id="paymentRejectCollapse">
                                                    <div class="card card-body bg-white border p-2 mb-0">
                                                        <form
                                                            action="{{ route('store.purchase-orders.verify-payment', $purchaseOrder->id) }}"
                                                            method="POST">
                                                            @csrf
                                                            <input type="hidden" name="action" value="Reject">
                                                            <input type="text" name="reason" class="form-control form-control-sm mb-1"
                                                                placeholder="Rejection Reason" required>
                                                            <button type="submit" class="btn btn-xs btn-danger w-100">Confirm
                                                                Reject</button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @elseif($purchaseOrder->payment_verification_status === 'Verified')
                                                <div class="bg-success-subtle text-success p-2 rounded small fw-bold">
                                                    <iconify-icon icon="solar:check-circle-bold-duotone"
                                                        class="align-middle me-1"></iconify-icon>
                                                    Payment Verified
                                                </div>
                                            @elseif($purchaseOrder->payment_verification_status === 'Rejected')
                                                <div class="bg-danger-subtle text-danger p-2 rounded small">
                                                    <div class="fw-bold"><iconify-icon icon="solar:close-circle-bold-duotone"
                                                            class="align-middle me-1"></iconify-icon> Payment Rejected</div>
                                                    @if($purchaseOrder->rejection_reason)
                                                        <div class="mt-1 small">Reason: "{{ $purchaseOrder->rejection_reason }}"</div>
                                                    @endif
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="bg-light p-3 rounded text-muted small">
                                            Awaiting payout reference. Once the payout reference is available, you can verify the
                                            payment here.
                                        </div>
                                    @endif
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-slate-800 mb-3">2. Dispatch Details</h6>
                                    @if($purchaseOrder->payment_verification_status !== 'Verified')
                                        <div class="text-muted small p-3 bg-light rounded">
                                            Dispatch will become available once the payment has been verified.
                                        </div>
                                    @else
                                        @if($purchaseOrder->dispatch_status === 'Pending')
                                            <div class="bg-warning-subtle text-warning p-3 rounded mb-3 small fw-semibold">
                                                Package Ready for Dispatch
                                            </div>
                                            <form action="{{ route('store.purchase-orders.dispatch', $purchaseOrder->id) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="mb-3 text-start">
                                                    <label class="form-label small fw-semibold text-slate-750">Upload Invoice Copy (PDF or Image) <span class="text-danger">*</span></label>
                                                    <input type="file" name="invoice_file" class="form-control form-control-sm" accept=".pdf,.png,.jpg,.jpeg" required>
                                                </div>
                                                <button type="submit" class="btn btn-primary w-100 py-2">
                                                    <iconify-icon icon="solar:delivery-bold-duotone"
                                                        class="align-middle me-1 fs-18"></iconify-icon>
                                                    Dispatch Order Now
                                                </button>
                                            </form>
                                        @elseif($purchaseOrder->dispatch_status === 'Dispatched')
                                            <div class="bg-success text-white p-3 rounded small fw-bold">
                                                <iconify-icon icon="solar:delivery-bold-duotone"
                                                    class="align-middle me-1 fs-18"></iconify-icon>
                                                Order Dispatched
                                            </div>
                                        @elseif($purchaseOrder->dispatch_status === 'Delivered')
                                            <div class="bg-success text-white p-3 rounded small fw-bold">
                                                <iconify-icon icon="solar:verified-check-bold"
                                                    class="align-middle me-1 fs-18"></iconify-icon>
                                                Order Delivered
                                            </div>
                                        @endif
                                    @endif

                                    @if($purchaseOrder->invoice || in_array($purchaseOrder->dispatch_status, ['Dispatched', 'Delivered', 'Received']))
                                        <div class="mt-3">
                                            <a href="{{ route('store.purchase-orders.invoice', $purchaseOrder->id) }}" target="_blank" class="btn btn-outline-primary w-100 py-2">
                                                <iconify-icon icon="solar:document-text-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                                                View/Print Tax Invoice
                                            </a>
                                        </div>

                                        <!-- Post-dispatch Upload option for Authorized Pickup Store -->
                                        @if(!in_array($purchaseOrder->status, ['Received', 'Completed']) && !in_array($purchaseOrder->dispatch_status, ['Received', 'Delivered']))
                                            <div class="card border border-dashed mt-3 p-3 bg-light bg-opacity-50 text-start">
                                                <h6 class="fw-bold text-slate-800 mb-2" style="font-size: 13px;">
                                                    <iconify-icon icon="solar:upload-bold-duotone" class="align-middle me-1 text-primary"></iconify-icon>
                                                    {{ $purchaseOrder->invoice && $purchaseOrder->invoice->invoice_file ? 'Update Invoice Copy' : 'Upload Invoice Copy' }}
                                                </h6>
                                                @if($purchaseOrder->invoice && $purchaseOrder->invoice->invoice_file)
                                                    <div class="mb-2">
                                                        <a href="{{ $purchaseOrder->invoice->invoice_file }}" target="_blank" class="text-success small fw-semibold">
                                                            <iconify-icon icon="solar:eye-bold-duotone" class="align-middle me-0.5"></iconify-icon> View Current File
                                                        </a>
                                                    </div>
                                                @endif
                                                <form action="{{ route('store.purchase-orders.invoice.upload', $purchaseOrder->id) }}" method="POST" enctype="multipart/form-data">
                                                    @csrf
                                                    <div class="mb-2">
                                                        <input type="file" name="invoice_file" class="form-control form-control-sm" accept=".pdf,.png,.jpg,.jpeg" required>
                                                    </div>
                                                    <button type="submit" class="btn btn-primary btn-sm w-100 py-1.5">
                                                        <iconify-icon icon="solar:upload-minimalistic-bold-duotone" class="align-middle me-1"></iconify-icon>
                                                        Upload Document
                                                    </button>
                                                </form>
                                            </div>
                                        @elseif($purchaseOrder->invoice && $purchaseOrder->invoice->invoice_file)
                                            <div class="mt-2 text-center">
                                                <a href="{{ $purchaseOrder->invoice->invoice_file }}" target="_blank" class="btn btn-sm btn-outline-success w-100 py-1.5">
                                                    <iconify-icon icon="solar:eye-bold-duotone" class="align-middle me-1"></iconify-icon> View Uploaded Invoice Copy
                                                </a>
                                            </div>
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @else
                            <!-- Unauthorized Pickup Store: Payment Verification disabled, Dispatch allowed after seller verification -->
                            <div class="row g-4">
                                <div class="col-md-6 border-end">
                                    <h6 class="fw-bold text-slate-800 mb-3">1. Seller Payout Status</h6>
                                    <div class="alert alert-warning border-0 small mb-0">
                                        <iconify-icon icon="solar:info-circle-bold-duotone"
                                            class="align-middle me-1 fs-16"></iconify-icon>
                                        Payout Verification is managed directly by the Seller.
                                    </div>
                                    <div class="mt-3">
                                        <span class="text-muted small">Seller Payout Verification Status:</span>
                                        @if($purchaseOrder->payment_verification_status === 'Pending')
                                            <span
                                                class="badge bg-warning-subtle text-warning px-2.5 py-1 fs-11 d-inline-block mt-1">Awaiting
                                                Seller Verification</span>
                                        @elseif($purchaseOrder->payment_verification_status === 'Verified')
                                            <span
                                                class="badge bg-success-subtle text-success px-2.5 py-1 fs-11 d-inline-block mt-1">Verified
                                                by Seller</span>
                                        @elseif($purchaseOrder->payment_verification_status === 'Rejected')
                                            <span
                                                class="badge bg-danger-subtle text-danger px-2.5 py-1 fs-11 d-inline-block mt-1">Rejected
                                                by Seller</span>
                                            @if($purchaseOrder->rejection_reason)
                                                <div class="mt-1 text-danger small">"{{ $purchaseOrder->rejection_reason }}"</div>
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="fw-bold text-slate-800 mb-3">2. Dispatch Details</h6>
                                    @if($purchaseOrder->payment_verification_status !== 'Verified')
                                        <div class="text-muted small p-3 bg-light rounded">
                                            Dispatch will become available once the payout has been verified by the Seller.
                                        </div>
                                    @else
                                        @if($purchaseOrder->dispatch_status === 'Pending')
                                            <div class="bg-warning-subtle text-warning p-3 rounded mb-3 small fw-semibold">
                                                Seller has verified payout. Package ready for dispatch.
                                            </div>
                                            <form action="{{ route('store.purchase-orders.dispatch', $purchaseOrder->id) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                <div class="mb-3 text-start">
                                                    <label class="form-label small fw-semibold text-slate-750">Upload Invoice Copy (PDF or Image) <span class="text-danger">*</span></label>
                                                    <input type="file" name="invoice_file" class="form-control form-control-sm" accept=".pdf,.png,.jpg,.jpeg" required>
                                                </div>
                                                <button type="submit" class="btn btn-primary w-100 py-2">
                                                    <iconify-icon icon="solar:delivery-bold-duotone"
                                                        class="align-middle me-1 fs-18"></iconify-icon>
                                                    Dispatch Order Now
                                                </button>
                                            </form>
                                        @elseif($purchaseOrder->dispatch_status === 'Dispatched')
                                            <div class="bg-success text-white p-3 rounded small fw-bold">
                                                <iconify-icon icon="solar:delivery-bold-duotone"
                                                    class="align-middle me-1 fs-18"></iconify-icon>
                                                Order Dispatched
                                            </div>
                                        @elseif($purchaseOrder->dispatch_status === 'Delivered')
                                            <div class="bg-success text-white p-3 rounded small fw-bold">
                                                <iconify-icon icon="solar:verified-check-bold"
                                                    class="align-middle me-1 fs-18"></iconify-icon>
                                                Order Delivered
                                            </div>
                                        @endif
                                    @endif

                                    @if($purchaseOrder->invoice || in_array($purchaseOrder->dispatch_status, ['Dispatched', 'Delivered', 'Received']))
                                        <div class="mt-3">
                                            <a href="{{ route('store.purchase-orders.invoice', $purchaseOrder->id) }}" target="_blank" class="btn btn-outline-primary w-100 py-2">
                                                <iconify-icon icon="solar:document-text-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                                                View/Print Tax Invoice
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            @endif
        </div>

        <!-- Right: Summary Sidebar -->
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3 border-bottom">
                    <h6 class="fw-bold text-slate-800 mb-0">PO Information</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">PO ID</span>
                        <strong class="text-slate-800">{{ $purchaseOrder->po_number }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Requested By</span>
                        <span class="text-slate-700 fw-semibold">{{ $purchaseOrder->ddc?->name }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Seller Account</span>
                        <span
                            class="text-slate-700 fw-semibold">{{ $purchaseOrder->seller?->company_name ?: $purchaseOrder->seller?->name }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Order Date</span>
                        <span class="text-slate-700">{{ $purchaseOrder->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                    <hr class="text-muted opacity-20">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Overall PO Status</span>
                        <span>
                            @if($purchaseOrder->status === 'Pending')
                                <span class="badge bg-warning-subtle text-warning">Pending</span>
                            @elseif($purchaseOrder->status === 'Accepted')
                                <span class="badge bg-success-subtle text-success">Accepted</span>
                            @elseif($purchaseOrder->status === 'Rejected')
                                <span class="badge bg-danger-subtle text-danger">Rejected</span>
                            @elseif($purchaseOrder->status === 'Cancelled')
                                <span class="badge bg-danger text-white">Cancelled</span>
                            @elseif($purchaseOrder->status === 'Paid')
                                <span class="badge bg-success text-white">Paid</span>
                            @endif
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Payment Status</span>
                        <span>
                            @if($store->can_receive_payment_and_dispatch)
                                @if($purchaseOrder->payment_verification_status === 'Verified')
                                    <span class="badge bg-success text-white">Verified</span>
                                @elseif($purchaseOrder->seller_payment_status === 'Paid' && $purchaseOrder->payment_verification_status === 'Pending')
                                    <span class="badge bg-warning-subtle text-warning">Awaiting Verification</span>
                                @elseif($purchaseOrder->payment_verification_status === 'Rejected')
                                    <span class="badge bg-danger-subtle text-danger">Rejected Payment</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">Unpaid</span>
                                @endif
                            @else
                                @if($purchaseOrder->payment_verification_status === 'Verified')
                                    <span class="badge bg-success text-white">Verified (Ready)</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">Awaiting Seller</span>
                                @endif
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


                    <hr class="text-muted opacity-20">

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Total Requested Amount</span>
                        <span
                            class="fw-bold text-slate-800">₹{{ number_format($purchaseOrder->seller_total_amount > 0 ? $purchaseOrder->seller_total_amount : $items->sum('seller_total'), 2) }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Accepted Amount</span>
                        <span
                            class="fw-bold text-success">₹{{ number_format($purchaseOrder->seller_accepted_amount > 0 ? $purchaseOrder->seller_accepted_amount : $items->where('status', 'Accepted')->sum('seller_total'), 2) }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection