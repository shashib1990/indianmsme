@extends('layouts.admin')

@section('title', 'Debit Note Settlement Audit')
@section('page-title', 'ADPL Settlement Audit')

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ route('admin.debit-notes.index') }}" class="btn btn-sm btn-outline-secondary">
                <iconify-icon icon="solar:arrow-left-bold" class="align-middle me-1"></iconify-icon> Back to Debit Notes
            </a>
            <div>
                <span class="fs-13 text-muted me-2">Status:</span>
                @if($debitNote->status === 'PENDING')
                    <span class="badge bg-warning text-dark fs-13 px-2.5 py-1">AWAITING SELLER VERIFICATION</span>
                @elseif($debitNote->status === 'SELLER_APPROVED')
                    <span class="badge bg-primary text-white fs-13 px-2.5 py-1">APPROVED BY SELLER (READY FOR ADPL SETTLEMENT)</span>
                @elseif($debitNote->status === 'SELLER_REJECTED')
                    <span class="badge bg-danger fs-13 px-2.5 py-1">REJECTED BY SELLER</span>
                @elseif($debitNote->status === 'SETTLED')
                    <span class="badge bg-success fs-13 px-2.5 py-1">SETTLED</span>
                @else
                    <span class="badge bg-secondary fs-13 px-2.5 py-1">{{ $debitNote->status }}</span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <!-- Left Column: Details & Audit Trail -->
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="fw-bold text-slate-800 mb-0">Debit Note #{{ $debitNote->debit_note_number }}</h5>
                <span class="text-muted small">{{ $debitNote->created_at->format('d M Y, h:i A') }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <span class="text-muted small d-block">PO Number:</span>
                        <a href="{{ route('admin.purchase-orders.show', $debitNote->purchase_order_id) }}" class="fw-bold text-decoration-none">
                            #{{ $debitNote->purchaseOrder?->po_number }}
                        </a>
                    </div>
                    <div class="col-6">
                        <span class="text-muted small d-block">DDC Center:</span>
                        <strong class="text-dark">{{ $debitNote->ddc?->name }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted small d-block">Seller:</span>
                        <strong class="text-dark">{{ $debitNote->seller?->company_name ?: $debitNote->seller?->name }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted small d-block">Discrepancy Type:</span>
                        <strong class="text-primary">{{ $debitNote->discrepancy_type }}</strong>
                    </div>
                </div>

                @php
                    $dnItems = ($debitNote->items && $debitNote->items->isNotEmpty())
                        ? $debitNote->items->filter(fn($i) => ($i->shortage_qty > 0 || $i->damage_qty > 0))
                        : collect();
                @endphp

                @if($dnItems->isNotEmpty())
                    <h6 class="fw-bold text-slate-800 mb-2">Itemized Discrepancy Breakdown ({{ $dnItems->count() }} items)</h6>
                    <div class="table-responsive bg-light p-2 rounded mb-3">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted small border-bottom">
                                    <th>Product Name</th>
                                    <th class="text-center">Order Qty</th>
                                    <th class="text-center">Sound Recv</th>
                                    <th class="text-center text-danger">Shortage</th>
                                    <th class="text-center text-warning">Damage</th>
                                    <th class="text-end">Seller Price</th>
                                    <th class="text-end">DDC Price (Inc. Margin)</th>
                                    <th class="text-end pe-2">Discrepancy Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($dnItems as $dnItem)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold text-slate-800 d-block">{{ $dnItem->product_name }}</span>
                                            <span class="text-muted small font-monospace">PROD-{{ $dnItem->purchaseOrderItem?->product?->product_code ?? $dnItem->purchaseOrderItem?->product_id }}</span>
                                        </td>
                                        <td class="text-center fw-medium">{{ $dnItem->order_qty }}</td>
                                        <td class="text-center text-success fw-medium">{{ $dnItem->received_qty }}</td>
                                        <td class="text-center">
                                            @if($dnItem->shortage_qty > 0)
                                                <span class="badge bg-danger-subtle text-danger px-1.5 py-0.5 fs-11">{{ $dnItem->shortage_qty }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            @if($dnItem->damage_qty > 0)
                                                <span class="badge bg-warning-subtle text-warning px-1.5 py-0.5 fs-11">{{ $dnItem->damage_qty }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="text-end text-muted small">₹{{ number_format($dnItem->seller_unit_price ?: $dnItem->unit_price, 2) }}</td>
                                        <td class="text-end fw-semibold text-dark">₹{{ number_format($dnItem->unit_price, 2) }}</td>
                                        <td class="text-end pe-2 fw-bold text-slate-800">₹{{ number_format($dnItem->item_discrepancy_amount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="table-responsive bg-light p-2 rounded mb-3">
                        <table class="table table-sm table-borderless mb-0">
                            <thead>
                                <tr class="text-muted small border-bottom">
                                    <th>Item Name</th>
                                    <th>Order Qty</th>
                                    <th>Recv Qty</th>
                                    <th>Short</th>
                                    <th>Damage</th>
                                    <th>Unit Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="fw-semibold">
                                    <td>{{ $debitNote->purchaseOrderItem?->product_name }}</td>
                                    <td>{{ $debitNote->order_qty }}</td>
                                    <td>{{ $debitNote->received_qty }}</td>
                                    <td class="text-warning">{{ $debitNote->shortage_qty }}</td>
                                    <td class="text-danger">{{ $debitNote->damage_qty }}</td>
                                    <td>₹{{ number_format($debitNote->unit_price, 2) }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @endif

                <div class="d-flex align-items-center justify-content-between p-3 bg-primary-subtle rounded text-primary mb-3">
                    <span class="fw-semibold">Final Settlement Amount:</span>
                    <h4 class="fw-bold mb-0">₹{{ number_format($debitNote->final_amount ?: $debitNote->total_discrepancy_amount, 2) }}</h4>
                </div>

                <!-- Proof Images Gallery -->
                <h6 class="fw-bold text-slate-800 mb-2">Discrepancy Proof Images Uploaded by DDC (Max 5)</h6>
                @if(!empty($debitNote->proof_images))
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach($debitNote->proof_images as $img)
                            <a href="{{ $img }}" target="_blank" class="border rounded p-1 d-inline-block bg-white shadow-sm">
                                <img src="{{ $img }}" alt="Proof" style="width: 90px; height: 90px; object-fit: cover;" class="rounded">
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted small mb-3">No proof images uploaded.</p>
                @endif

                @if($debitNote->seller_invoice_file)
                    <h6 class="fw-bold text-slate-800 mb-2">Seller Invoice for Excess Stock</h6>
                    <a href="{{ $debitNote->seller_invoice_file }}" target="_blank" class="btn btn-sm btn-outline-info">
                        <iconify-icon icon="solar:document-bold" class="align-middle me-1"></iconify-icon> View Seller Excess Invoice
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Right Column: Settlement Execution Form -->
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3 border-bottom">
                <h5 class="fw-bold text-slate-800 mb-0">ADPL Wallet Settlement Action</h5>
            </div>
            <div class="card-body">
                @if($debitNote->status === 'SELLER_APPROVED')
                    <div class="alert alert-warning mb-3">
                        <strong>Seller Approved:</strong> Seller agreed to quantity {{ $debitNote->seller_agreed_qty }} for total amount ₹{{ number_format($debitNote->final_amount, 2) }}.
                    </div>

                    <form action="{{ route('admin.debit-notes.verify-settlement', $debitNote->id) }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">ADPL Remarks / Verification Notes:</label>
                            <textarea name="adpl_remarks" rows="3" class="form-control" placeholder="Add any verification notes..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-success fw-bold w-100 py-2.5" onclick="return confirm('Confirm settlement release? Wallet transactions will be executed automatically.');">
                            <iconify-icon icon="solar:shield-check-bold" class="align-middle me-1"></iconify-icon>
                            Verify & Execute Wallet Settlement
                        </button>
                    </form>
                @elseif($debitNote->status === 'SETTLED')
                    <div class="alert alert-success mb-3">
                        <iconify-icon icon="solar:check-circle-bold" class="fs-18 me-1 align-middle"></iconify-icon>
                        <strong>SETTLEMENT COMPLETED:</strong> Funds have been transferred to the respective wallets.
                    </div>

                    @if($debitNote->adpl_remarks)
                        <p class="text-muted small"><strong>ADPL Remarks:</strong> {{ $debitNote->adpl_remarks }}</p>
                    @endif
                @else
                    <div class="p-3 bg-light text-muted rounded">
                        Settlement action available after Seller approves the Debit Note.
                    </div>
                @endif

                @if($adplTransactions->isNotEmpty())
                    <h6 class="fw-bold text-slate-800 mt-4 mb-2">ADPL Escrow Audit Log</h6>
                    <div class="list-group list-group-flush border rounded">
                        @foreach($adplTransactions as $tx)
                            <div class="list-group-item py-2">
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="badge {{ $tx->type === 'credit' ? 'bg-success' : 'bg-danger' }}">{{ strtoupper($tx->type) }}</span>
                                    <strong class="font-monospace">₹{{ number_format($tx->amount, 2) }}</strong>
                                </div>
                                <small class="text-muted d-block mt-1">{{ $tx->description }}</small>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
