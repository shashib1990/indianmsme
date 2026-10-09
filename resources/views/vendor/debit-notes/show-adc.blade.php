@extends('layouts.vendor')

@section('title', 'ADC Debit Note Details - ' . $debitNote->debit_note_number)
@section('page-title', 'ADC Debit Note Details')

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ route('vendor.debit-notes.index', ['tab' => 'adc_order']) }}" class="btn btn-sm btn-outline-secondary">
                <iconify-icon icon="solar:arrow-left-bold" class="align-middle me-1"></iconify-icon> Back to Debit Notes
            </a>
            <div>
                <span class="fs-13 text-muted me-2">Status:</span>
                @if(in_array($debitNote->status, ['PENDING', 'PENDING_SELLER_APPROVAL']))
                    <span class="badge bg-warning text-dark fs-13 px-2.5 py-1">AWAITING SELLER VERIFICATION</span>
                @elseif($debitNote->status === 'SETTLED')
                    <span class="badge bg-success fs-13 px-2.5 py-1">SETTLED</span>
                @elseif($debitNote->status === 'SELLER_REJECTED')
                    <span class="badge bg-danger fs-13 px-2.5 py-1">REJECTED</span>
                @else
                    <span class="badge bg-secondary fs-13 px-2.5 py-1">{{ $debitNote->status }}</span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="fw-bold text-slate-800 mb-0">ADC Debit Note #{{ $debitNote->debit_note_number }}</h5>
                <span class="text-muted small">{{ $debitNote->created_at->format('d M Y, h:i A') }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <span class="text-muted small d-block">ADC Order Number:</span>
                        <strong class="fw-bold text-dark">#{{ $debitNote->adcOrder?->order_number ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted small d-block">Reporting ADC Store:</span>
                        <strong class="text-dark">{{ $debitNote->adc?->name ?? ('ADC #' . $debitNote->adc_id) }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted small d-block">Discrepancy Type:</span>
                        <strong class="text-primary">{{ $debitNote->discrepancy_type }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted small d-block">Target Supplier:</span>
                        <span class="badge bg-info-subtle text-info">Seller</span> 
                        <strong class="text-dark">{{ $debitNote->seller?->company_name ?? $debitNote->seller?->name }}</strong>
                    </div>
                </div>

                @if($debitNote->adpl_remarks)
                    <div class="mb-3">
                        <span class="text-muted small d-block fw-semibold mb-1">ADC Discrepancy Remarks:</span>
                        <div class="bg-light p-2.5 rounded text-dark fs-13">{{ $debitNote->adpl_remarks }}</div>
                    </div>
                @endif

                <h6 class="fw-bold text-slate-800 mb-2">Itemized Discrepancy Breakdown</h6>
                <div class="table-responsive bg-light p-2 rounded mb-3">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-muted small border-bottom">
                                <th>Product Name</th>
                                <th class="text-center">Order Qty</th>
                                <th class="text-center">Sound Recv</th>
                                <th class="text-center text-danger">Shortage</th>
                                <th class="text-center text-warning">Damage</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-center">Approved Qty</th>
                                <th class="text-end pe-2">Discrepancy Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($debitNote->items as $item)
                                @php
                                    $claimedQty = $item->shortage_qty + $item->damage_qty;
                                    $soundQty = max(0, $item->order_qty - $claimedQty);
                                @endphp
                                <tr>
                                    <td>
                                        <span class="fw-semibold text-slate-800 d-block">{{ $item->product_name }}</span>
                                        @if($item->adcOrderItem?->product)
                                            <span class="text-muted small font-monospace">PROD-{{ $item->adcOrderItem->product->product_code ?? $item->adcOrderItem->product_id }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-medium">{{ $item->order_qty }}</td>
                                    <td class="text-center text-success fw-medium">{{ $soundQty }}</td>
                                    <td class="text-center">
                                        @if($item->shortage_qty > 0)
                                            <span class="badge bg-danger-subtle text-danger px-1.5 py-0.5 fs-11">{{ $item->shortage_qty }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item->damage_qty > 0)
                                            <span class="badge bg-warning-subtle text-warning px-1.5 py-0.5 fs-11">{{ $item->damage_qty }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-semibold text-dark">₹{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-center" style="width: 100px;">
                                        @if(in_array($debitNote->status, ['PENDING', 'PENDING_SELLER_APPROVAL']))
                                            <input type="number" 
                                                   min="0" 
                                                   max="{{ $claimedQty }}" 
                                                   name="item_agreed_qty[{{ $item->id }}]" 
                                                   form="seller-response-form"
                                                   class="form-control form-control-sm text-center fw-bold px-1" 
                                                   value="{{ old('item_agreed_qty.'.$item->id, $item->seller_agreed_qty ?? $claimedQty) }}"
                                                   title="Enter agreed discrepancy quantity">
                                        @else
                                            <span class="fw-bold text-success">{{ $item->seller_agreed_qty ?? $claimedQty }}</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-2 fw-bold text-slate-800">
                                        ₹{{ number_format($item->seller_agreed_amount ?? $item->item_discrepancy_amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-3 text-muted">No line items found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex align-items-center justify-content-between p-3 bg-primary-subtle rounded text-primary mb-3">
                    <span class="fw-semibold">Total Discrepancy Amount:</span>
                    <h4 class="fw-bold mb-0">₹{{ number_format($debitNote->final_amount ?? $debitNote->total_discrepancy_amount, 2) }}</h4>
                </div>

                <!-- Discrepancy Proof Documents Uploaded by ADC -->
                <h6 class="fw-bold text-slate-800 mb-2">Discrepancy Proof Documents ({{ count($debitNote->proof_images ?: []) }}/5)</h6>
                @if(!empty($debitNote->proof_images) && is_array($debitNote->proof_images))
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach($debitNote->proof_images as $imgUrl)
                            <a href="{{ $imgUrl }}" target="_blank" class="border rounded p-1 d-inline-block bg-white shadow-sm">
                                <img src="{{ $imgUrl }}" alt="Proof" style="width: 90px; height: 90px; object-fit: cover;" class="rounded">
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted small">No proof documents attached.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0">Seller Action / Response</h6>
            </div>
            <div class="card-body">
                @if(in_array($debitNote->status, ['PENDING', 'PENDING_SELLER_APPROVAL']))
                    <form id="seller-response-form" method="POST" action="{{ route('vendor.adc-debit-notes.respond', $debitNote->id) }}">
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
                            <button type="submit" name="action" value="APPROVE" class="btn btn-success fw-bold py-2" onclick="return confirm('Approve discrepancy claim? Agreed quantities will be deducted from your wallet and credited to ADC.');">
                                <iconify-icon icon="solar:check-circle-bold" class="align-middle me-1 fs-16"></iconify-icon> Approve & Settle Claim
                            </button>
                            <button type="submit" name="action" value="REJECT" class="btn btn-outline-danger py-2" onclick="return confirm('Reject this discrepancy claim?');">
                                <iconify-icon icon="solar:close-circle-bold" class="align-middle me-1 fs-16"></iconify-icon> Reject Claim
                            </button>
                        </div>
                    </form>
                @else
                    <div class="alert {{ $debitNote->status === 'SETTLED' ? 'alert-success' : 'alert-danger' }} mb-0">
                        <strong class="d-block mb-1">Status: {{ $debitNote->status }}</strong>
                        @if($debitNote->seller_remarks)
                            <p class="small mb-0">Remarks: {{ $debitNote->seller_remarks }}</p>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
