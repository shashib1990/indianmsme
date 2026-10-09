@extends('layouts.vendor')

@section('title', 'ADC Credit Note ' . $creditNote->credit_note_number)
@section('page-title', 'ADC Credit Note Details')

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ route('vendor.credit-notes.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Credit Notes
            </a>
            <span class="badge bg-success fs-13 px-2.5 py-1"><i class="bi bi-check-circle me-1"></i> {{ $creditNote->status ?? 'SETTLED' }}</span>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <h5 class="fw-bold mb-0">ADC Credit Note: {{ $creditNote->credit_note_number }}</h5>
                <span class="text-muted small">{{ $creditNote->created_at->format('d M Y, h:i A') }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <small class="text-muted d-block">ADC Order Number</small>
                        <strong class="fw-bold text-dark">#{{ $creditNote->adcOrder?->order_number ?? 'N/A' }}</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">ADC Store</small>
                        <strong class="text-dark">{{ $creditNote->adc?->name ?? 'ADC #'.$creditNote->adc_id }}</strong>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Credit Note Type</small>
                        <span class="badge bg-info-subtle text-info">{{ $creditNote->type }}</span>
                    </div>
                    <div class="col-md-6">
                        <small class="text-muted d-block">Linked Debit Note</small>
                        @if($creditNote->debitNote)
                            <a href="{{ route('vendor.adc-debit-notes.show', $creditNote->debitNote->id) }}" class="fw-bold text-decoration-none text-danger">
                                #{{ $creditNote->debitNote->debit_note_number }}
                            </a>
                        @else
                            <span class="text-muted">—</span>
                        @endif
                    </div>
                </div>

                @php
                    $filteredItems = ($creditNote->debitNote && $creditNote->debitNote->items && $creditNote->debitNote->items->count() > 0)
                        ? $creditNote->debitNote->items
                        : ($creditNote->adcOrder && $creditNote->adcOrder->items ? $creditNote->adcOrder->items : collect());
                @endphp

                @if($filteredItems->isNotEmpty())
                    <h6 class="fw-bold text-slate-800 mb-2">Itemized Products Breakdown</h6>
                    <div class="table-responsive bg-light p-2 rounded mb-3">
                        <table class="table table-sm table-hover align-middle mb-0">
                            <thead>
                                <tr class="text-muted small border-bottom">
                                    <th>Product Name</th>
                                    <th class="text-center">Order Qty</th>
                                    <th class="text-center text-danger">Shortage</th>
                                    <th class="text-center text-warning">Damage</th>
                                    <th class="text-center text-success">Agreed Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end pe-2">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($filteredItems as $item)
                                    @php
                                        $unitPrice = (float)($item->unit_price > 0 ? $item->unit_price : ($item->price > 0 ? $item->price : 0));
                                        $agreedQty = isset($item->seller_agreed_qty) ? $item->seller_agreed_qty : ($item->shortage_qty + $item->damage_qty > 0 ? ($item->shortage_qty + $item->damage_qty) : $item->quantity);
                                        $itemAmount = round($agreedQty * $unitPrice, 2);
                                    @endphp
                                    <tr>
                                        <td>
                                            <span class="fw-semibold text-slate-800 d-block">{{ $item->product_name ?? $item->product?->product_name }}</span>
                                        </td>
                                        <td class="text-center fw-medium">{{ $item->order_qty ?? $item->quantity }}</td>
                                        <td class="text-center text-danger">{{ $item->shortage_qty ?? 0 }}</td>
                                        <td class="text-center text-warning">{{ $item->damage_qty ?? 0 }}</td>
                                        <td class="text-center fw-bold text-success">{{ $agreedQty }}</td>
                                        <td class="text-end">₹{{ number_format($unitPrice, 2) }}</td>
                                        <td class="text-end pe-2 fw-bold text-slate-800">₹{{ number_format($item->seller_agreed_amount ?? $itemAmount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                <div class="p-3 bg-success-subtle rounded border border-success-subtle d-flex align-items-center justify-content-between">
                    <div>
                        <small class="d-block fw-bold text-success text-uppercase">Total Settled Amount</small>
                        <h3 class="fw-bold text-success mb-0">₹{{ number_format($creditNote->amount, 2) }}</h3>
                    </div>
                    <i class="bi bi-wallet-fill fs-1 text-success opacity-50"></i>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0">Credit Settlement Summary</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-success mb-3">
                    <i class="bi bi-check-circle-fill text-success fs-5 d-block mb-1"></i>
                    <strong>Settled via Wallet</strong>
                    <p class="small mb-0 mt-1">This credit note amount of ₹{{ number_format($creditNote->amount, 2) }} has been settled.</p>
                </div>
                <div class="mb-0">
                    <span class="text-muted small d-block">Created On</span>
                    <span class="fw-medium text-dark">{{ $creditNote->created_at->format('d M Y, h:i A') }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
