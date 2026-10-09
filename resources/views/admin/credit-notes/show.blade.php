@extends('layouts.admin')

@section('title', 'Credit Note Settlement Audit')
@section('page-title', 'ADPL Credit Note Audit')

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ route('admin.credit-notes.index') }}" class="btn btn-sm btn-outline-secondary">
                <iconify-icon icon="solar:arrow-left-bold" class="align-middle me-1"></iconify-icon> Back to Credit Notes
            </a>
            <div>
                <span class="fs-13 text-muted me-2">Status:</span>
                @if($creditNote->status === 'GENERATED')
                    <span class="badge bg-warning text-dark fs-13 px-2.5 py-1">GENERATED (AWAITING SETTLEMENT)</span>
                @elseif($creditNote->status === 'SETTLED')
                    <span class="badge bg-success fs-13 px-2.5 py-1">SETTLED</span>
                @else
                    <span class="badge bg-secondary fs-13 px-2.5 py-1">{{ $creditNote->status }}</span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="fw-bold text-slate-800 mb-0">Credit Note #{{ $creditNote->credit_note_number }}</h5>
                <span class="text-muted small">{{ $creditNote->created_at->format('d M Y, h:i A') }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <span class="text-muted small d-block">PO Number:</span>
                        <a href="{{ route('admin.purchase-orders.show', $creditNote->purchase_order_id) }}" class="fw-bold text-decoration-none">
                            #{{ $creditNote->purchaseOrder?->po_number }}
                        </a>
                    </div>
                    <div class="col-6">
                        <span class="text-muted small d-block">DDC Center:</span>
                        <strong class="text-dark">{{ $creditNote->ddc?->name }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted small d-block">Seller:</span>
                        <strong class="text-dark">{{ $creditNote->seller?->company_name ?: $creditNote->seller?->name }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted small d-block">Linked Debit Note:</span>
                        @if($creditNote->debitNote)
                            <a href="{{ route('admin.debit-notes.show', $creditNote->debitNote->id) }}" class="fw-bold text-decoration-none text-danger">
                                #{{ $creditNote->debitNote->debit_note_number }}
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                    <div class="col-12">
                        <span class="text-muted small d-block">Credit Note Type:</span>
                        <strong class="text-primary">{{ $creditNote->type }}</strong>
                    </div>
                </div>

                @php
                    $isExcessBill = $creditNote->type === 'EXCESS_STOCK_BILL';
                    $filteredItems = ($creditNote->debitNote && $creditNote->debitNote->items)
                        ? $creditNote->debitNote->items->filter(function($dnItem) use ($isExcessBill) {
                            return $isExcessBill 
                                ? ($dnItem->excess_qty > 0) 
                                : (($dnItem->shortage_qty > 0 || $dnItem->damage_qty > 0 || $dnItem->seller_agreed_qty > 0));
                        })
                        : collect();
                @endphp

                @if($filteredItems->isNotEmpty())
                    <h6 class="fw-bold text-slate-800 mb-2">Itemized Products Breakdown</h6>
                    <div class="table-responsive bg-light p-2 rounded mb-3">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted small border-bottom">
                                    <th>Product Name</th>
                                    <th class="text-center">Order Qty</th>
                                    <th class="text-center">Sound Recv</th>
                                    @if($isExcessBill)
                                        <th class="text-center text-info">Excess Qty</th>
                                    @else
                                        <th class="text-center text-danger">Shortage</th>
                                        <th class="text-center text-warning">Damage</th>
                                        <th class="text-center text-success">Agreed Qty</th>
                                    @endif
                                    <th class="text-end">Seller Price</th>
                                    <th class="text-end">DDC Price (Inc. Margin)</th>
                                    <th class="text-end pe-2">Amount (Seller Price)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($filteredItems as $dnItem)
                                    @php
                                        $itemSellerPrice = (float)($dnItem->seller_unit_price > 0 ? $dnItem->seller_unit_price : ($dnItem->purchaseOrderItem?->seller_price > 0 ? $dnItem->purchaseOrderItem->seller_price : ($dnItem->purchaseOrderItem?->product?->seller_price_incl_gst ?: $dnItem->unit_price)));
                                        $ddcPrice = (float)$dnItem->unit_price;
                                        $qty = $isExcessBill ? $dnItem->excess_qty : ($dnItem->seller_agreed_qty ?? ($dnItem->shortage_qty + $dnItem->damage_qty));
                                        $itemAmount = round($qty * $itemSellerPrice, 2);
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="fw-semibold text-slate-800 d-block">{{ $dnItem->product_name }}</span>
                                        </td>
                                        <td class="text-center">{{ $dnItem->order_qty }}</td>
                                        <td class="text-center text-success">{{ $dnItem->received_qty }}</td>
                                        @if($isExcessBill)
                                            <td class="text-center text-info font-monospace fw-bold">+{{ $dnItem->excess_qty }}</td>
                                        @else
                                            <td class="text-center text-danger">{{ $dnItem->shortage_qty ?: '—' }}</td>
                                            <td class="text-center text-warning">{{ $dnItem->damage_qty ?: '—' }}</td>
                                            <td class="text-center text-success fw-bold">{{ $dnItem->seller_agreed_qty ?? ($dnItem->shortage_qty + $dnItem->damage_qty) }}</td>
                                        @endif
                                        <td class="text-end text-muted small">₹{{ number_format($itemSellerPrice, 2) }}</td>
                                        <td class="text-end fw-semibold text-dark">₹{{ number_format($ddcPrice, 2) }}</td>
                                        <td class="text-end pe-2 fw-bold text-slate-800">₹{{ number_format($itemAmount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <div class="d-flex align-items-center justify-content-between p-3 bg-success-subtle rounded text-success mb-3">
                    <span class="fw-semibold">Total Credit Note Amount:</span>
                    <h4 class="fw-bold mb-0">₹{{ number_format($creditNote->amount, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
