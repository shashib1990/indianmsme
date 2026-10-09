@extends('layouts.pickup_store')

@section('title', 'ADC Credit Note Details - ' . $creditNote->credit_note_number)
@section('page-title', 'ADC Credit Note Details')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">ADC Credit Note: {{ $creditNote->credit_note_number }}</h4>
                <p class="text-muted small mb-0">Issued for ADC Order #{{ $creditNote->adcOrder->order_number ?? 'N/A' }}</p>
            </div>
            <a href="{{ route('store.credit-notes.index', ['tab' => 'adc_order']) }}" class="btn btn-outline-secondary btn-sm">
                <iconify-icon icon="solar:arrow-left-bold-duotone" class="align-middle me-1"></iconify-icon> Back to List
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title fw-bold text-slate-800 mb-0">Credit Note Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <span class="text-muted small d-block">Credit Note Number</span>
                        <strong class="fs-16 text-primary">{{ $creditNote->credit_note_number }}</strong>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small d-block">Total Credited Amount</span>
                        <strong class="fs-18 text-success">₹{{ number_format($creditNote->amount, 2) }}</strong>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small d-block">Associated Debit Note</span>
                        <span>{{ $creditNote->debitNote->debit_note_number ?? 'N/A' }}</span>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small d-block">Issue Date</span>
                        <span>{{ $creditNote->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                </div>
            </div>
        </div>

        @php
            $filteredItems = ($creditNote->debitNote && $creditNote->debitNote->items && $creditNote->debitNote->items->count() > 0)
                ? $creditNote->debitNote->items
                : ($creditNote->adcOrder && $creditNote->adcOrder->items ? $creditNote->adcOrder->items : collect());
        @endphp

        @if($filteredItems->isNotEmpty())
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title fw-bold text-slate-800 mb-0">Itemized Products Breakdown</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light text-slate-700">
                                <tr>
                                    <th class="ps-3">Product Name</th>
                                    <th class="text-center">Order Qty</th>
                                    <th class="text-center text-danger">Shortage</th>
                                    <th class="text-center text-warning">Damage</th>
                                    <th class="text-center text-success">Agreed Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-end pe-3">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($filteredItems as $item)
                                    @php
                                        $unitPrice = (float)($item->unit_price > 0 ? $item->unit_price : ($item->price > 0 ? $item->price : 0));
                                        $agreedQty = isset($item->seller_agreed_qty) ? $item->seller_agreed_qty : ($item->shortage_qty + $item->damage_qty > 0 ? ($item->shortage_qty + $item->damage_qty) : ($item->order_qty ?? $item->quantity ?? 0));
                                        $itemAmount = round($agreedQty * $unitPrice, 2);
                                    @endphp
                                    <tr>
                                        <td class="ps-3">
                                            <span class="fw-semibold text-slate-800 d-block">{{ $item->product_name ?? $item->product?->product_name }}</span>
                                        </td>
                                        <td class="text-center fw-medium">{{ $item->order_qty ?? $item->quantity ?? '—' }}</td>
                                        <td class="text-center text-danger">{{ $item->shortage_qty ?? 0 }}</td>
                                        <td class="text-center text-warning">{{ $item->damage_qty ?? 0 }}</td>
                                        <td class="text-center fw-bold text-success">{{ $agreedQty }}</td>
                                        <td class="text-end">₹{{ number_format($unitPrice, 2) }}</td>
                                        <td class="text-end pe-3 fw-bold text-slate-800">₹{{ number_format($item->seller_agreed_amount ?? $itemAmount, 2) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="card-title fw-bold text-slate-800 mb-0">Entities Involved</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="text-muted small d-block">ADC Store</span>
                    <strong class="text-slate-800">{{ $creditNote->adc->name ?? 'N/A' }}</strong>
                </div>
                <div class="mb-3">
                    <span class="text-muted small d-block">Target Supplier</span>
                    @if($creditNote->target_type == 'SELLER')
                        <span class="badge bg-info-subtle text-info">Seller</span> {{ $creditNote->seller->company_name ?? $creditNote->seller->name ?? 'N/A' }}
                    @else
                        <span class="badge bg-primary-subtle text-primary">DDC</span> {{ $creditNote->ddc->name ?? 'N/A' }}
                    @endif
                </div>
                <div class="mb-3">
                    <span class="text-muted small d-block">Assigned Pickup Store</span>
                    <strong class="text-slate-800">{{ $store->pickup_store_name }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
