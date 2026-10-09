@extends('layouts.vendor')

@section('title', 'ADC Excess Invoice ' . $invoice->invoice_number)
@section('page-title', 'ADC Excess Invoice Details')

@section('content')
<div class="mb-3">
    <a href="{{ route('vendor.excess-invoices.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Excess Invoices
    </a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
        <h5 class="fw-bold mb-0">ADC Excess Stock Invoice: {{ $invoice->invoice_number }}</h5>
        @if($invoice->status === 'PAID')
            <span class="badge bg-success fs-6"><i class="bi bi-check-circle"></i> PAID</span>
        @else
            <span class="badge bg-warning text-dark fs-6"><i class="bi bi-clock"></i> PENDING PAYMENT</span>
        @endif
    </div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <small class="text-muted d-block">ADC Order Number</small>
                <strong>#{{ $invoice->order?->order_number }}</strong>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">ADC Store</small>
                <strong>{{ $invoice->adc?->name ?? 'ADC #'.$invoice->adc_id }}</strong>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">Total Excess Qty</small>
                <span class="badge bg-success fs-6">+{{ $invoice->total_excess_qty }}</span>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">Created Date</small>
                <strong>{{ $invoice->created_at->format('d M Y, h:i A') }}</strong>
            </div>
        </div>

        <h6 class="fw-bold mb-3 border-bottom pb-2">Excess Stock Items</h6>
        <div class="table-responsive">
            <table class="table table-bordered align-middle">
                <thead class="bg-light">
                    <tr>
                        <th>Product</th>
                        <th class="text-center">Order Qty</th>
                        <th class="text-center">Excess Qty</th>
                        <th class="text-end">Unit Price</th>
                        <th class="text-end">Total Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @if($invoice->order && $invoice->order->items)
                        @foreach($invoice->order->items->filter(fn($i) => $i->excess_qty > 0) as $item)
                            <tr>
                                <td>{{ $item->product_name ?? $item->product?->product_name }}</td>
                                <td class="text-center">{{ $item->quantity }}</td>
                                <td class="text-center text-success fw-bold">+{{ $item->excess_qty }}</td>
                                <td class="text-end">₹{{ number_format($item->price, 2) }}</td>
                                <td class="text-end fw-bold">₹{{ number_format($item->excess_qty * $item->price, 2) }}</td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="4" class="text-end">Total Amount:</th>
                        <th class="text-end text-success fs-5">₹{{ number_format($invoice->total_excess_amount, 2) }}</th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>
@endsection
