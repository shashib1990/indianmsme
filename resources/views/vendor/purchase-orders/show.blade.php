@extends('layouts.vendor')

@section('title', 'Respond to Purchase Order')
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
                            <p class="text-muted small mb-0">Review product requirements, submit responses, verify payment,
                                and manage dispatch.</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <a href="{{ route('vendor.purchase-orders.index') }}" class="btn btn-outline-primary">
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

    @if($purchaseOrder->status === 'Pending' && $purchaseOrder->payment_status !== 'Paid')
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
    @endif

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
            @php
                $isPending = ($purchaseOrder->status === 'Pending' && $purchaseOrder->payment_status !== 'Paid');
            @endphp

            @if($isPending)
                <form action="{{ route('vendor.purchase-orders.bulk-respond', $purchaseOrder->id) }}" method="POST"
                    id="poResponseForm">
                    @csrf
            @endif

                @php
                    $isRejectedTab = request()->get('tab') === 'rejected' || $purchaseOrder->status === 'Rejected';
                    $isAcceptedTab = in_array($purchaseOrder->status, ['Accepted', 'Processing', 'Dispatched', 'Received']) && !$isRejectedTab;
                @endphp
                <div class="card border-0 shadow-sm mb-4">
                    <div
                        class="card-header bg-white border-0 py-3 border-bottom d-flex align-items-center justify-content-between">
                        <h6 class="fw-bold text-slate-800 mb-0">
                            @if($isRejectedTab)
                                Rejected Products
                            @elseif($isAcceptedTab)
                                Accepted Products
                            @elseif($purchaseOrder->status === 'Cancelled')
                                Cancelled Products
                            @else
                                Requested Products
                            @endif
                        </h6>
                        <span class="text-muted small">PO ID: <strong>{{ $purchaseOrder->po_number }}</strong></span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="bg-light bg-opacity-50 border-top border-bottom">
                                    <tr>
                                        @if($isPending)
                                            <th class="ps-3" style="width: 40px;">
                                                <input type="checkbox" class="form-check-input" id="selectAllItemsCheckbox"
                                                    checked>
                                            </th>
                                        @endif
                                        <th class="{{ $isPending ? 'ps-2' : 'ps-3' }}" style="width: 250px;">Product Name
                                        </th>
                                        <th class="text-end">Price</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-end">Total</th>
                                        <th class="pe-3 text-center" style="width: 200px;">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        if ($isPending) {
                                            $displayItems = $items;
                                        } elseif ($isRejectedTab) {
                                            $displayItems = $items->where('status', 'Rejected');
                                            if ($displayItems->isEmpty()) {
                                                $displayItems = $items;
                                            }
                                        } elseif ($isAcceptedTab) {
                                            $displayItems = $items->where('status', 'Accepted');
                                            if ($displayItems->isEmpty()) {
                                                $displayItems = $items;
                                            }
                                        } else {
                                            $displayItems = $items;
                                        }
                                    @endphp
                                    @foreach($displayItems as $item)
                                        <tr>
                                            @if($isPending)
                                                <td class="ps-3">
                                                    <input type="checkbox" class="form-check-input item-select-checkbox"
                                                        name="accepted_items[]" value="{{ $item->id }}" checked>
                                                </td>
                                            @endif
                                            <td class="{{ $isPending ? 'ps-2' : 'ps-3' }}">
                                                <div class="fw-semibold text-slate-800">{{ $item->product?->product_name }}
                                                </div>
                                                <div class="text-muted small">UOM: {{ $item->unit_of_measure ?? ($item->product?->unit_of_measure ?? '—') }}</div>
                                                @if($item->mfg_date || $item->expire_date)
                                                    <div class="text-muted small" style="font-size: 11px;">
                                                        @if($item->mfg_date) <span class="me-2"><strong>MFG:</strong> {{ is_string($item->mfg_date) ? $item->mfg_date : $item->mfg_date->format('d M Y') }}</span> @endif
                                                        @if($item->expire_date) <span><strong>EXP:</strong> {{ is_string($item->expire_date) ? $item->expire_date : $item->expire_date->format('d M Y') }}</span> @endif
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="text-end fw-semibold">₹{{ number_format($item->seller_price ?? 0, 2) }}
                                            </td>
                                            <td class="text-center">{{ $item->quantity }} {{ $item->unit_of_measure ?? ($item->product?->unit_of_measure ?? 'Units') }}</td>
                                            <td class="text-end fw-bold text-slate-800">
                                                ₹{{ number_format($item->seller_total ?? 0, 2) }}</td>
                                            <td class="pe-3 text-center">
                                                @if($purchaseOrder->status === 'Rejected' || $item->status === 'Rejected')
                                                    <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-11"
                                                        data-bs-toggle="tooltip" title="{{ $item->remarks ?? $purchaseOrder->rejection_reason }}">
                                                        Rejected
                                                    </span>
                                                    @if($item->remarks || $purchaseOrder->rejection_reason)
                                                        <div class="text-danger small mt-1 fs-10" style="font-size: 10px;">
                                                            "{{ $item->remarks ?? $purchaseOrder->rejection_reason }}"</div>
                                                    @endif
                                                @elseif($item->status === 'Pending')
                                                    <span
                                                        class="badge bg-warning-subtle text-warning px-2 py-1 fs-11">Pending</span>
                                                @elseif($item->status === 'Accepted')
                                                    <span
                                                        class="badge bg-success-subtle text-success px-2 py-1 fs-11">Accepted</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Response actions for pending order -->
                @if($isPending)
                        <div class="card border-0 shadow-sm mb-4">
                            <div class="card-header bg-white border-0 py-3 border-bottom">
                                <h6 class="fw-bold text-slate-800 mb-0">Respond to Purchase Order</h6>
                            </div>
                            <div class="card-body">
                                <p class="text-muted small">Check the items you want to <strong>Accept</strong>. Unchecked items
                                    will be automatically <strong>Rejected</strong>. If any items are rejected or the entire order
                                    is cancelled, a reason is required below.</p>

                                <div class="mb-3">
                                    <label for="rejection_reason"
                                        class="form-label fw-semibold text-slate-700">Rejection/Cancellation Reason</label>
                                    <textarea name="rejection_reason" id="rejection_reason" rows="2" class="form-control"
                                        placeholder="Provide a reason if rejecting any items or cancelling the order..."></textarea>
                                </div>

                                <div class="d-flex flex-wrap gap-3 align-items-center">
                                    <button type="submit" class="btn btn-success px-4 py-2" id="submitResponseBtn">
                                        <iconify-icon icon="solar:check-circle-bold-duotone"
                                            class="align-middle me-1 fs-18"></iconify-icon>
                                        Submit Response
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                @endif

            <!-- Payment & Dispatch Workflow details -->
            @if($purchaseOrder->payment_status === 'Paid')
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3 border-bottom">
                        <h6 class="fw-bold text-slate-800 mb-0">Workflow & Operations Management</h6>
                    </div>
                    <div class="card-body">
                        <div class="row g-4">
                            <!-- Left: Payout Verification (Seller always receives and verifies payment from Admin) -->
                            <div class="col-md-6 border-end">
                                <h6 class="fw-bold text-slate-800 mb-3">1. Payout Verification</h6>

                                <div class="mb-3">
                                    <span class="text-muted small d-block">Payment Status:</span>
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

                                         $store = $purchaseOrder->pickupStore;
                                         $hasAuthorizedStore = $store && $store->can_receive_payment_and_dispatch;
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
 
                                         @if($hasAuthorizedStore)
                                             @if($purchaseOrder->payment_verification_status === 'Pending')
                                                 <div class="bg-info-subtle text-info p-3 rounded small fw-semibold d-flex align-items-start gap-2">
                                                     <iconify-icon icon="solar:info-circle-bold-duotone" class="fs-18 mt-0.5"></iconify-icon>
                                                     <div>
                                                         Payout verification is managed directly by your assigned Authorized Pickup Store:
                                                         <strong class="d-block mt-1">{{ $store->pickup_store_name }}</strong>
                                                     </div>
                                                 </div>
                                             @elseif($purchaseOrder->payment_verification_status === 'Verified')
                                                 <div class="bg-success-subtle text-success p-2 rounded small fw-bold">
                                                     <iconify-icon icon="solar:check-circle-bold-duotone"
                                                         class="align-middle me-1"></iconify-icon>
                                                     Payout Verified by Store
                                                 </div>
                                             @elseif($purchaseOrder->payment_verification_status === 'Rejected')
                                                 <div class="bg-danger-subtle text-danger p-2 rounded small">
                                                     <div class="fw-bold"><iconify-icon icon="solar:close-circle-bold-duotone"
                                                             class="align-middle me-1"></iconify-icon> Payout Rejected by Store</div>
                                                     @if($purchaseOrder->rejection_reason)
                                                         <div class="mt-1 small">Reason: "{{ $purchaseOrder->rejection_reason }}"</div>
                                                     @endif
                                                 </div>
                                             @endif
                                         @else
                                             @if($purchaseOrder->payment_verification_status === 'Pending')
                                                 <div class="bg-warning-subtle text-warning p-2 rounded small mb-2 fw-semibold">
                                                     Awaiting Your Verification
                                                 </div>
                                                 <div class="d-flex gap-2">
                                                     <form action="{{ route('vendor.purchase-orders.verify-payout', $purchaseOrder->id) }}"
                                                         method="POST" class="d-inline">
                                                         @csrf
                                                         <input type="hidden" name="action" value="Verify">
                                                         <button type="submit" class="btn btn-sm btn-success px-3 confirm-action"
                                                             data-confirm-message="Confirm that you have received this payment from Admin?">
                                                             Approve &amp; Verify
                                                         </button>
                                                     </form>
 
                                                     <button class="btn btn-sm btn-outline-danger px-3" type="button"
                                                         data-bs-toggle="collapse" data-bs-target="#payoutRejectCollapse">
                                                         Reject
                                                     </button>
                                                 </div>
 
                                                 <div class="collapse mt-2" id="payoutRejectCollapse">
                                                     <div class="card card-body bg-white border p-2 mb-0">
                                                         <form
                                                             action="{{ route('vendor.purchase-orders.verify-payout', $purchaseOrder->id) }}"
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
                                                     Payout Verified
                                                 </div>
                                             @elseif($purchaseOrder->payment_verification_status === 'Rejected')
                                                 <div class="bg-danger-subtle text-danger p-2 rounded small">
                                                     <div class="fw-bold"><iconify-icon icon="solar:close-circle-bold-duotone"
                                                             class="align-middle me-1"></iconify-icon> Payout Rejected</div>
                                                     @if($purchaseOrder->rejection_reason)
                                                         <div class="mt-1 small">Reason: "{{ $purchaseOrder->rejection_reason }}"</div>
                                                     @endif
                                                 </div>
                                             @endif
                                         @endif
                                     </div>
                                @else
                                    <div class="bg-light p-3 rounded text-muted small">
                                        Awaiting payout reference. Once the payout reference is available, you can verify the
                                        payment here.
                                    </div>
                                @endif
                            </div>

                            <!-- Right: Dispatch & Store Assignment -->
                            <div class="col-md-6">
                                <h6 class="fw-bold text-slate-800 mb-3">2. Dispatch Details</h6>
                                @if($purchaseOrder->pickupStore && $purchaseOrder->pickupStore->can_receive_payment_and_dispatch)
                                    <!-- Authorized Pickup Store Details -->
                                    <div class="alert alert-info border-0 mb-0">
                                        <h6 class="fw-bold text-primary mb-1">
                                            <iconify-icon icon="solar:shield-check-bold-duotone"
                                                class="align-middle me-1 fs-18"></iconify-icon>
                                            Authorized Pickup Store Assigned
                                        </h6>
                                        <p class="mb-0 small">This Purchase Order is assigned to your Authorized Pickup Store:
                                            <strong>{{ $purchaseOrder->pickupStore->pickup_store_name }}
                                                ({{ $purchaseOrder->pickupStore->pickup_store_code }})</strong>.
                                        </p>
                                        <p class="mb-0 mt-2 small text-muted">All remaining operations (Payment Verification,
                                            Package Dispatch) are fully managed by the store dashboard.</p>
                                    </div>
                                @else
                                    <!-- Unauthorized Store or No Store: Main/Sub Seller must handle Dispatch -->
                                    @if($purchaseOrder->payment_verification_status !== 'Verified')
                                        <div class="text-muted small p-3 bg-light rounded">
                                            Dispatch will become available once the payout has been verified by you.
                                        </div>
                                    @else
                                        @if($purchaseOrder->dispatch_status === 'Pending')
                                            <div class="bg-warning-subtle text-warning p-3 rounded mb-3 small fw-semibold">
                                                Package Ready for Dispatch
                                            </div>
                                            <form action="{{ route('vendor.purchase-orders.dispatch', $purchaseOrder->id) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                
                                                @php
                                                    $dispatchItems = $purchaseOrder->items->where('status', 'Accepted');
                                                    if ($dispatchItems->isEmpty()) {
                                                        $dispatchItems = $purchaseOrder->items;
                                                    }
                                                @endphp

                                                @if($dispatchItems->count() > 0)
                                                    <div class="mb-3 text-start border rounded p-3 bg-light bg-opacity-50">
                                                        <h6 class="fw-bold text-slate-800 fs-12 mb-2">
                                                            <iconify-icon icon="solar:calendar-bold-duotone" class="align-middle me-1 text-primary"></iconify-icon>
                                                            Manufacturing & Expiry Dates
                                                        </h6>
                                                        @foreach($dispatchItems as $dItem)
                                                            <div class="mb-2 pb-2 border-bottom last-border-0">
                                                                <div class="fw-semibold text-slate-800 small mb-1">
                                                                    {{ $dItem->product?->product_name ?? $dItem->product_name }} <span class="text-muted font-normal">(Qty: {{ $dItem->quantity }})</span>
                                                                </div>
                                                                <div class="row g-2">
                                                                    <div class="col-6">
                                                                        <label class="form-label fs-11 text-muted mb-1">MFG Date</label>
                                                                        <input type="date" name="items[{{ $dItem->id }}][mfg_date]" class="form-control form-control-sm" value="{{ $dItem->mfg_date ? (is_string($dItem->mfg_date) ? $dItem->mfg_date : $dItem->mfg_date->format('Y-m-d')) : '' }}">
                                                                    </div>
                                                                    <div class="col-6">
                                                                        <label class="form-label fs-11 text-muted mb-1">EXP Date</label>
                                                                        <input type="date" name="items[{{ $dItem->id }}][expire_date]" class="form-control form-control-sm" value="{{ $dItem->expire_date ? (is_string($dItem->expire_date) ? $dItem->expire_date : $dItem->expire_date->format('Y-m-d')) : '' }}">
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif

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

                                    @if($purchaseOrder->pickupStore)
                                        <div class="mt-3 p-2 bg-light rounded text-muted small">
                                            Note: This is an Unauthorized Store order. Either you or the store
                                            (<strong>{{ $purchaseOrder->pickupStore->pickup_store_name }}</strong>) can dispatch the
                                            products.
                                        </div>
                                    @endif
                                @endif

                                @if($purchaseOrder->invoice || in_array($purchaseOrder->dispatch_status, ['Dispatched', 'Delivered', 'Received']))
                                    <div class="mt-3">
                                        <a href="{{ route('vendor.purchase-orders.invoice', $purchaseOrder->id) }}" target="_blank" class="btn btn-outline-primary w-100 py-2">
                                            <iconify-icon icon="solar:document-text-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                                            View/Print Tax Invoice
                                        </a>
                                    </div>

                                    @if(!($purchaseOrder->pickupStore && $purchaseOrder->pickupStore->can_receive_payment_and_dispatch))
                                        <!-- Post-dispatch Upload option for Vendor -->
                                        @if(!in_array($purchaseOrder->status, ['Received', 'Completed']) && !in_array($purchaseOrder->dispatch_status, ['Received', 'Delivered']))
                                            <div class="card border border-dashed mt-3 p-3 bg-light bg-opacity-50">
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
                                                <form action="{{ route('vendor.purchase-orders.invoice.upload', $purchaseOrder->id) }}" method="POST" enctype="multipart/form-data">
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
                                @endif
                            </div>
                        </div>
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
                        <span class="text-muted">DDC District</span>
                        <span class="text-slate-700">{{ $purchaseOrder->ddc?->profile?->district ?? '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Order Date</span>
                        <span class="text-slate-700">{{ $purchaseOrder->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                    @if($purchaseOrder->adcOrder || !empty($purchaseOrder->adc_order_id))
                        @php
                            $vAdcOrder = $purchaseOrder->adcOrder;
                            if (!$vAdcOrder && !empty($purchaseOrder->adc_order_id)) {
                                $vAdcOrder = \App\Models\AdcOrder::with(['adc.adcProfile', 'adc.profile'])->find($purchaseOrder->adc_order_id);
                            }
                            $vAdcUser = $vAdcOrder?->adc;
                            $vAdcProfile = $vAdcUser?->adcProfile ?? $vAdcUser?->profile;
                            $vAdcCode = $vAdcProfile?->adc_code ?? $vAdcUser?->adc_code ?? ($vAdcUser ? ('ADC' . str_pad($vAdcUser->id, 3, '0', STR_PAD_LEFT)) : null);
                            $vAscCode = $vAdcProfile?->code ?? $vAdcUser?->code ?? ($vAdcUser ? ('ASC' . str_pad($vAdcUser->id, 3, '0', STR_PAD_LEFT)) : null);
                            $vCodes = array_filter(array_unique([$vAdcCode, $vAscCode]));
                            $vCanteenName = $vAdcProfile?->company_name ?: ($vAdcProfile?->trade_name ?: ($vAdcUser?->name ?? null));
                        @endphp
                        @if($vCanteenName)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">Canteen</span>
                                <span class="text-slate-700 fw-semibold">{{ $vCanteenName }}</span>
                            </div>
                        @endif
                        @if(!empty($vCodes))
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">ADC ID / Code</span>
                                <span class="badge font-monospace px-2 py-1" style="background-color: #e6fffa; color: #0d9488; border: 1px solid #99f6e4;">{{ implode(' / ', $vCodes) }}</span>
                            </div>
                        @endif
                    @endif
                    <hr class="text-muted opacity-20">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Overall PO Status</span>
                        <span>
                            @if($purchaseOrder->status === 'Pending')
                                <span class="badge bg-warning-subtle text-warning">Pending</span>
                            @elseif($purchaseOrder->status === 'Accepted')
                                <span class="badge bg-success-subtle text-success">Accepted</span>
                            @elseif($purchaseOrder->status === 'Processing')
                                <span class="badge bg-info-subtle text-info">Processing</span>
                            @elseif($purchaseOrder->status === 'Dispatched')
                                <span class="badge bg-primary-subtle text-primary">Dispatched</span>
                            @elseif($purchaseOrder->status === 'Received')
                                <span class="badge bg-success text-white">Received</span>
                            @elseif($purchaseOrder->status === 'Rejected')
                                <span class="badge bg-danger-subtle text-danger">Rejected</span>
                            @elseif($purchaseOrder->status === 'Cancelled')
                                <span class="badge bg-danger text-white">Cancelled</span>
                            @endif
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Payment Status</span>
                        <span>
                            @if(in_array($purchaseOrder->status, ['Processing', 'Dispatched', 'Received']))
                                @if($purchaseOrder->payment_verification_status === 'Verified')
                                    <span class="badge bg-success text-white">Paid / Verified</span>
                                @elseif($purchaseOrder->seller_payment_status === 'Paid' && $purchaseOrder->payment_verification_status === 'Pending')
                                    <span class="badge bg-warning-subtle text-warning">Awaiting Verification</span>
                                @elseif($purchaseOrder->payment_verification_status === 'Rejected')
                                    <span class="badge bg-danger-subtle text-danger">Rejected Payout</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger">Unpaid</span>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </span>
                    </div>

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
                                $sideSlipUrl = $sideCombined ? $sideMatches[2] : (Str::startsWith($sideRefText, ['http://', 'https://', 'seller_payments/', 'uploads/']) ? $sideRefText : null);
                                if (!$sideCombined && $sideSlipUrl) $sideTxnId = null;
                            }
                        @endphp
                        <div class="d-flex justify-content-between mb-2 align-items-center">
                            <span class="text-muted">Txn ID</span>
                            <span>
                                @if($sideTxnId)
                                    <code class="small fw-semibold">{{ $sideTxnId }}</code>
                                @endif
                                @if($sideSlipUrl)
                                    <a href="{{ Str::startsWith($sideSlipUrl, ['http://', 'https://']) ? $sideSlipUrl : asset('storage/' . $sideSlipUrl) }}"
                                        target="_blank" class="btn btn-xs btn-outline-primary py-0.5 px-2 fs-11 ms-1">
                                        View Slip
                                    </a>
                                @endif
                            </span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Dispatch Status</span>
                        <span>
                            @if(in_array($purchaseOrder->status, ['Processing', 'Dispatched', 'Received']))
                                @if($purchaseOrder->status === 'Received')
                                    <span class="badge bg-success text-white">Delivered</span>
                                @elseif($purchaseOrder->dispatch_status === 'Dispatched')
                                    <span class="badge bg-info-subtle text-info">Dispatched</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning">Pending</span>
                                @endif
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </span>
                    </div>

                    <hr class="text-muted opacity-20">

                    @if($isRejectedTab)
                        @if($purchaseOrder->ssi_applicable && $purchaseOrder->ssi_amount > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">SSI Total</span>
                                <span class="fw-bold text-success">₹{{ number_format($purchaseOrder->ssi_amount, 2) }}</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between mb-0">
                            <span class="text-muted">Total Rejected Amount</span>
                            <span class="fw-bold text-danger">₹{{ number_format($displayItems->sum('seller_total'), 2) }}</span>
                        </div>
                    @elseif($purchaseOrder->status === 'Cancelled')
                        @if($purchaseOrder->ssi_applicable && $purchaseOrder->ssi_amount > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">SSI Total</span>
                                <span class="fw-bold text-success">₹{{ number_format($purchaseOrder->ssi_amount, 2) }}</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between mb-0">
                            <span class="text-muted">Total Amount</span>
                            <span class="fw-bold text-danger">₹{{ number_format($purchaseOrder->seller_total_amount > 0 ? $purchaseOrder->seller_total_amount : $items->sum('seller_total'), 2) }}</span>
                        </div>
                    @elseif($purchaseOrder->status === 'Pending')
                        @if($purchaseOrder->ssi_applicable && $purchaseOrder->ssi_amount > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">SSI Total</span>
                                <span class="fw-bold text-success">₹{{ number_format($purchaseOrder->ssi_amount, 2) }}</span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between mb-0">
                            <span class="text-muted">Total Amount</span>
                            <span class="fw-bold text-slate-800">₹{{ number_format($purchaseOrder->seller_total_amount > 0 ? $purchaseOrder->seller_total_amount : $items->sum('seller_total'), 2) }}</span>
                        </div>
                    @else
                        @if($purchaseOrder->ssi_applicable && $purchaseOrder->ssi_amount > 0)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted">SSI Total</span>
                                <span class="fw-bold text-success">
                                    ₹{{ number_format($purchaseOrder->ssi_amount, 2) }}
                                    @if($purchaseOrder->ssi_percentage)
                                        <span class="fs-11 text-muted">({{ number_format($purchaseOrder->ssi_percentage, 1) }}%)</span>
                                    @endif
                                </span>
                            </div>
                        @endif
                        <div class="d-flex justify-content-between mb-0">
                            <span class="text-muted">Total Amount</span>
                            <span class="fw-bold text-success">₹{{ number_format($purchaseOrder->seller_accepted_amount > 0 ? $purchaseOrder->seller_accepted_amount : $items->where('status', 'Accepted')->sum('seller_total'), 2) }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const selectAllCheckbox = document.getElementById('selectAllItemsCheckbox');
            const itemCheckboxes = document.querySelectorAll('.item-select-checkbox');
            const reasonTextarea = document.getElementById('rejection_reason');
            const responseForm = document.getElementById('poResponseForm');
            const submitBtn = document.getElementById('submitResponseBtn');

            if (selectAllCheckbox) {
                selectAllCheckbox.addEventListener('change', function () {
                    itemCheckboxes.forEach(cb => {
                        cb.checked = this.checked;
                    });
                });

                itemCheckboxes.forEach(cb => {
                    cb.addEventListener('change', function () {
                        const allChecked = Array.from(itemCheckboxes).every(c => c.checked);
                        const noneChecked = Array.from(itemCheckboxes).every(c => !c.checked);
                        selectAllCheckbox.checked = allChecked;
                        selectAllCheckbox.indeterminate = (!allChecked && !noneChecked);
                    });
                });
            }

            if (submitBtn && responseForm) {
                // Intercept click (not submit) so we control when form submits
                submitBtn.addEventListener('click', function (e) {
                    e.preventDefault(); // prevent immediate submit

                    const checkedCount = Array.from(itemCheckboxes).filter(c => c.checked).length;
                    const uncheckedCount = itemCheckboxes.length - checkedCount;

                    // Validate rejection reason
                    if (uncheckedCount > 0 && !reasonTextarea.value.trim()) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Reason Required',
                            text: 'Please provide a reason for rejecting the unchecked items or cancelling the order.',
                            confirmButtonColor: '#10b981',
                        }).then(() => {
                            reasonTextarea.focus();
                        });
                        return;
                    }

                    let confirmTitle = '';
                    let confirmMsg = '';
                    let confirmIcon = 'question';

                    if (checkedCount === 0) {
                        confirmTitle = 'Reject Entire Order?';
                        confirmMsg = 'Are you sure you want to reject all items? This will mark the Purchase Order as "Rejected".';
                        confirmIcon = 'warning';
                    } else if (uncheckedCount > 0) {
                        confirmTitle = 'Partial Acceptance';
                        confirmMsg = `You are accepting ${checkedCount} item(s) and rejecting ${uncheckedCount} item(s). Do you want to proceed?`;
                    } else {
                        confirmTitle = 'Accept All Items?';
                        confirmMsg = 'Are you sure you want to accept all items in this Purchase Order?';
                    }

                    Swal.fire({
                        title: confirmTitle,
                        text: confirmMsg,
                        icon: confirmIcon,
                        showCancelButton: true,
                        confirmButtonColor: '#10b981',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, Submit',
                        cancelButtonText: 'Go Back',
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Show loading on button ONLY after user confirms
                            if (typeof window.submitButtonLoading === 'function') {
                                window.submitButtonLoading(submitBtn, 'Submitting...');
                            }
                            responseForm.submit();
                        }
                        // If cancelled, nothing happens — button stays normal
                    });
                });
            }
        });
    </script>
@endpush