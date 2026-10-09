@extends('layouts.ddc')

@section('title', 'Debit Note Details')
@section('page-title', 'Debit Note Details')

@section('content')
    <div class="row mb-3">
        <div class="col-12">
            <div class="d-flex align-items-center justify-content-between">
                <a href="{{ route('ddc.debit-notes.index') }}" class="btn btn-sm btn-outline-secondary">
                    <iconify-icon icon="solar:arrow-left-bold" class="align-middle me-1"></iconify-icon> Back to Debit Notes
                </a>
                <div>
                    <span class="fs-13 text-muted me-2">Status:</span>
                    @if($debitNote->status === 'PENDING')
                        <span class="badge bg-warning text-dark fs-13 px-2.5 py-1">AWAITING SELLER VERIFICATION</span>
                    @elseif($debitNote->status === 'SELLER_APPROVED')
                        <span class="badge bg-info text-white fs-13 px-2.5 py-1">APPROVED BY SELLER (Awaiting ADPL
                            Settlement)</span>
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
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold text-slate-800 mb-0">Debit Note #{{ $debitNote->debit_note_number }}</h5>
                    <span class="text-muted small">{{ $debitNote->created_at->format('d M Y, h:i A') }}</span>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <span class="text-muted small d-block">PO Number:</span>
                            <a href="{{ route('ddc.purchase-orders.show', $debitNote->purchase_order_id) }}"
                                class="fw-bold text-decoration-none">
                                #{{ $debitNote->purchaseOrder?->po_number }}
                            </a>
                        </div>
                        <div class="col-6">
                            <span class="text-muted small d-block">Seller:</span>
                            <strong
                                class="text-dark">{{ $debitNote->seller?->company_name ?: $debitNote->seller?->name }}</strong>
                        </div>
                        @if(!$debitNote->items || $debitNote->items->isEmpty())
                            <div class="col-6">
                                <span class="text-muted small d-block">Item:</span>
                                <strong class="text-dark">{{ $debitNote->purchaseOrderItem?->product_name }}</strong>
                            </div>
                        @endif
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
                        <h6 class="fw-bold text-slate-800 mb-2">Itemized Discrepancy Breakdown ({{ $dnItems->count() }}
                            items)</h6>
                        <div class="table-responsive bg-light p-2 rounded mb-3">
                            <table class="table table-sm table-hover align-middle mb-0">
                                <thead>
                                    <tr class="text-muted small border-bottom">
                                        <th>Product Name</th>
                                        <th class="text-center">Order Qty</th>
                                        <th class="text-center">Sound Recv</th>
                                        <th class="text-center text-danger">Shortage</th>
                                        <th class="text-center text-warning">Damage</th>
                                        <th class="text-end">DDC Price (Inc. Margin)</th>
                                        <th class="text-end pe-2">Discrepancy Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dnItems as $dnItem)
                                        <tr>
                                            <td>
                                                <span class="fw-semibold text-slate-800 d-block">{{ $dnItem->product_name }}</span>
                                                <span
                                                    class="text-muted small font-monospace">PROD-{{ $dnItem->purchaseOrderItem?->product?->product_code ?? $dnItem->purchaseOrderItem?->product_id }}</span>
                                            </td>
                                            <td class="text-center fw-medium">{{ $dnItem->order_qty }}</td>
                                            <td class="text-center text-success fw-medium">{{ $dnItem->received_qty }}</td>
                                            <td class="text-center">
                                                @if($dnItem->shortage_qty > 0)
                                                    <span
                                                        class="badge bg-danger-subtle text-danger px-1.5 py-0.5 fs-11">{{ $dnItem->shortage_qty }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-center">
                                                @if($dnItem->damage_qty > 0)
                                                    <span
                                                        class="badge bg-warning-subtle text-warning px-1.5 py-0.5 fs-11">{{ $dnItem->damage_qty }}</span>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td class="text-end fw-semibold text-dark">₹{{ number_format($dnItem->unit_price, 2) }}
                                            </td>
                                            <td class="text-end pe-2 fw-bold text-slate-800">
                                                ₹{{ number_format($dnItem->item_discrepancy_amount, 2) }}</td>
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
                                        <th>Order Qty</th>
                                        <th>Received Qty</th>
                                        <th>Shortage</th>
                                        <th>Damage</th>
                                        <th>Unit Price</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr class="fw-semibold">
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

                    <div
                        class="d-flex align-items-center justify-content-between p-3 bg-primary-subtle rounded text-primary mb-3">
                        <span class="fw-semibold">Total Claim Amount:</span>
                        <h4 class="fw-bold mb-0">
                            ₹{{ number_format($debitNote->final_amount ?: $debitNote->total_discrepancy_amount, 2) }}</h4>
                    </div>

                    <!-- Proof Images Gallery (Max 5) -->
                    <h6 class="fw-bold text-slate-800 mb-2">Uploaded Proof Images
                        ({{ count($debitNote->proof_images ?: []) }}/5)</h6>
                    @if(!empty($debitNote->proof_images))
                        <div class="d-flex flex-wrap gap-2 mb-3">
                            @foreach($debitNote->proof_images as $img)
                                <a href="{{ $img }}" target="_blank" class="border rounded p-1 d-inline-block bg-white shadow-sm">
                                    <img src="{{ $img }}" alt="Proof" style="width: 90px; height: 90px; object-fit: cover;"
                                        class="rounded">
                                </a>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small mb-3">No proof images uploaded.</p>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-3">
                <div class="card-header bg-white py-3 border-bottom">
                    <h5 class="fw-bold text-slate-800 mb-0">Status Summary</h5>
                </div>
                <div class="card-body">
                    <div class="p-3 bg-light rounded mb-3">
                        <p class="mb-1"><strong>Status:</strong> {{ $debitNote->status }}</p>
                        @if($debitNote->seller_remarks)
                            <p class="mb-1 text-muted"><strong>Seller Remarks:</strong> {{ $debitNote->seller_remarks }}</p>
                        @endif
                        @if($debitNote->adpl_remarks)
                            <p class="mb-0 text-muted"><strong>ADPL Remarks:</strong> {{ $debitNote->adpl_remarks }}</p>
                        @endif
                    </div>

                    @php
                        $linkedCns = $debitNote->creditNotes && $debitNote->creditNotes->isNotEmpty()
                            ? $debitNote->creditNotes
                            : ($debitNote->creditNote ? collect([$debitNote->creditNote]) : collect());
                    @endphp
                    @if($linkedCns->isNotEmpty())
                        <div class="border rounded p-2.5 bg-success-subtle" style="padding:10px">
                            <h6 class="fw-bold text-success mb-2 fs-13">Linked Credit Note(s)</h6>
                            @foreach($linkedCns as $cn)
                                @php
                                    $cnAmount = $cn->type === 'EXCESS_STOCK_BILL'
                                        ? ($debitNote->items->sum(fn($i) => $i->excess_qty * (float) $i->unit_price) ?: ($debitNote->excess_qty * (float) $debitNote->unit_price))
                                        : ($debitNote->final_amount ?: $debitNote->total_discrepancy_amount);
                                @endphp
                                <div class="d-flex align-items-center justify-content-between p-2 mb-1 bg-white rounded shadow-sm">
                                    <div>
                                        <strong
                                            class="text-success small d-block font-monospace">#{{ $cn->credit_note_number }}</strong>
                                        <span class="text-muted extra-small d-block">{{ $cn->type }} |
                                            ₹{{ number_format($cnAmount, 2) }}</span>
                                    </div>
                                    <a href="{{ route('ddc.credit-notes.show', $cn->id) }}"
                                        class="btn btn-sm btn-success fw-bold py-1 px-2.5 fs-11">
                                        View CN <iconify-icon icon="solar:arrow-right-bold"
                                            class="align-middle ms-1"></iconify-icon>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection