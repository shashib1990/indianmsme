@extends('layouts.pickup_store')

@section('title', 'Debit Note Details - ' . $debitNote->debit_note_number)
@section('page-title', 'Debit Note Details')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">Debit Note: {{ $debitNote->debit_note_number }}</h4>
                <p class="text-muted small mb-0">Discrepancy claim for Purchase Order #{{ $debitNote->purchaseOrder->po_number ?? 'N/A' }}</p>
            </div>
            <a href="{{ route('store.debit-notes.index') }}" class="btn btn-outline-secondary btn-sm">
                <iconify-icon icon="solar:arrow-left-bold-duotone" class="align-middle me-1"></iconify-icon> Back to List
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title fw-bold text-slate-800 mb-0">Claim Items & Discrepancies</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light text-slate-700">
                            <tr>
                                <th class="ps-3">Item / Product</th>
                                <th>Order Qty</th>
                                <th>Shortage</th>
                                <th>Damage</th>
                                <th>Unit Price</th>
                                <th class="text-center" style="width: 100px;">Approved Qty</th>
                                <th class="text-end pe-3">Claim Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($debitNote->items && $debitNote->items->count() > 0)
                                @foreach($debitNote->items as $item)
                                    @php
                                        $claimedQty = $item->shortage_qty + $item->damage_qty;
                                    @endphp
                                    <tr>
                                        <td class="ps-3 fw-semibold">{{ $item->product->name ?? $item->product_name ?? 'N/A' }}</td>
                                        <td>{{ $item->order_qty }}</td>
                                        <td class="text-danger fw-bold">{{ $item->shortage_qty }}</td>
                                        <td class="text-warning fw-bold">{{ $item->damage_qty }}</td>
                                        <td>₹{{ number_format($item->unit_price, 2) }}</td>
                                        <td class="text-center">
                                            @if(in_array($debitNote->status, ['PENDING', 'PENDING_SELLER_APPROVAL']))
                                                <input type="number" 
                                                       min="0" 
                                                       max="{{ $claimedQty }}" 
                                                       name="item_agreed_qty[{{ $item->id }}]" 
                                                       form="store-response-form-po"
                                                       class="form-control form-control-sm text-center fw-bold px-1" 
                                                       value="{{ old('item_agreed_qty.'.$item->id, $item->seller_agreed_qty ?? $claimedQty) }}"
                                                       title="Enter agreed discrepancy quantity">
                                            @else
                                                <span class="fw-bold text-success">{{ $item->seller_agreed_qty ?? $claimedQty }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3 fw-bold">₹{{ number_format($item->seller_agreed_amount ?? $item->total_discrepancy_amount ?? $item->item_discrepancy_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            @else
                                @php
                                    $claimedQty = $debitNote->shortage_qty + $debitNote->damage_qty;
                                @endphp
                                <tr>
                                    <td class="ps-3 fw-semibold">{{ $debitNote->purchaseOrderItem->product->name ?? 'Discrepancy Item' }}</td>
                                    <td>{{ $debitNote->order_qty }}</td>
                                    <td class="text-danger fw-bold">{{ $debitNote->shortage_qty }}</td>
                                    <td class="text-warning fw-bold">{{ $debitNote->damage_qty }}</td>
                                    <td>₹{{ number_format($debitNote->unit_price, 2) }}</td>
                                    <td class="text-center">
                                        @if(in_array($debitNote->status, ['PENDING', 'PENDING_SELLER_APPROVAL']))
                                            <input type="number" 
                                                   min="0" 
                                                   max="{{ $claimedQty }}" 
                                                   name="agreed_qty" 
                                                   form="store-response-form-po"
                                                   class="form-control form-control-sm text-center fw-bold px-1" 
                                                   value="{{ old('agreed_qty', $debitNote->seller_agreed_qty ?? $claimedQty) }}"
                                                   title="Enter agreed discrepancy quantity">
                                        @else
                                            <span class="fw-bold text-success">{{ $debitNote->seller_agreed_qty ?? $claimedQty }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3 fw-bold">₹{{ number_format($debitNote->seller_amount ?? $debitNote->total_discrepancy_amount, 2) }}</td>
                                </tr>
                            @endif
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td colspan="6" class="ps-3">Total Discrepancy Amount:</td>
                                <td class="text-end pe-3 text-primary fs-16">₹{{ number_format($debitNote->final_amount ?? $debitNote->total_discrepancy_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        @if(!empty($debitNote->proof_images) && is_array($debitNote->proof_images))
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title fw-bold text-slate-800 mb-0">Discrepancy Proof Documents</h5>
                </div>
                <div class="card-body">
                    <div class="row g-3">
                        @foreach($debitNote->proof_images as $imgUrl)
                            <div class="col-md-3 col-6">
                                <a href="{{ $imgUrl }}" target="_blank" class="d-block border rounded p-1 text-center bg-light">
                                    <img src="{{ $imgUrl }}" class="img-fluid rounded" style="max-height: 120px; object-fit: contain;">
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="card-title fw-bold text-slate-800 mb-0">Pickup Store Action / Response</h5>
            </div>
            <div class="card-body">
                @if(in_array($debitNote->status, ['PENDING', 'PENDING_SELLER_APPROVAL']))
                    <form id="store-response-form-po" method="POST" action="{{ route('store.debit-notes.respond', $debitNote->id) }}">
                        @csrf
                        <div class="alert alert-info py-2 px-3 small mb-3">
                            <iconify-icon icon="solar:info-circle-bold" class="align-middle me-1 fs-16"></iconify-icon>
                            You can adjust the agreed discrepancy quantity per product in the table on the left before approving.
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-bold text-slate-700">Remarks (Optional)</label>
                            <textarea name="seller_remarks" class="form-control form-control-sm" rows="3" placeholder="Add remarks for approval or rejection..."></textarea>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" name="action" value="APPROVE" class="btn btn-success fw-bold py-2" onclick="return confirm('Approve discrepancy claim? Agreed quantities will be settled.');">
                                <iconify-icon icon="solar:check-circle-bold" class="align-middle me-1 fs-16"></iconify-icon> Approve & Settle Claim
                            </button>
                            <button type="submit" name="action" value="REJECT" class="btn btn-outline-danger py-2" onclick="return confirm('Reject this discrepancy claim?');">
                                <iconify-icon icon="solar:close-circle-bold" class="align-middle me-1 fs-16"></iconify-icon> Reject Claim
                            </button>
                        </div>
                    </form>
                @else
                    <div class="alert {{ $debitNote->status === 'SETTLED' || $debitNote->status === 'APPROVED' ? 'alert-success' : 'alert-danger' }} mb-3">
                        <strong class="d-block mb-1">Status: {{ $debitNote->status }}</strong>
                        @if($debitNote->seller_remarks)
                            <p class="small mb-0">Supplier Remarks: {{ $debitNote->seller_remarks }}</p>
                        @endif
                    </div>
                @endif
                <hr>
                <div class="mb-3">
                    <span class="text-muted small d-block">DDC Name</span>
                    <strong class="text-slate-800">{{ $debitNote->ddc->name ?? 'N/A' }}</strong>
                </div>
                <div class="mb-3">
                    <span class="text-muted small d-block">Seller / Vendor</span>
                    <strong class="text-slate-800">{{ $debitNote->seller->company_name ?? $debitNote->seller->name ?? 'N/A' }}</strong>
                </div>
                <div class="mb-3">
                    <span class="text-muted small d-block">Assigned Pickup Store</span>
                    <strong class="text-slate-800">{{ $store->pickup_store_name }}</strong>
                </div>
                <div class="mb-3">
                    <span class="text-muted small d-block">Created On</span>
                    <span>{{ $debitNote->created_at->format('d M Y, h:i A') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
