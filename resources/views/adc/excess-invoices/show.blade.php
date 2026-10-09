@extends('layouts.adc')

@section('title', 'Excess Stock Invoice ' . $invoice->invoice_number)
@section('page-title', 'Excess Stock Invoice Details')

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ route('adc.excess-invoices.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Excess Invoices
            </a>
            @if($invoice->status === 'PAID')
                <span class="badge bg-success fs-13 px-2.5 py-1"><i class="bi bi-check-circle me-1"></i> PAID BY ADC</span>
            @else
                <span class="badge bg-warning text-dark fs-13 px-2.5 py-1"><i class="bi bi-clock me-1"></i> PENDING PAYMENT</span>
            @endif
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="fw-bold text-slate-800 mb-0">Excess Stock Invoice #{{ $invoice->invoice_number }}</h5>
                <span class="text-muted small">{{ $invoice->created_at->format('d M Y, h:i A') }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <span class="text-muted small d-block">ADC Order Number:</span>
                        <strong class="fw-bold text-dark">
                            @if($invoice->order)
                                <a href="{{ route('adc.orders.show', $invoice->adc_order_id) }}" class="text-decoration-none">#{{ $invoice->order->order_number }}</a>
                            @else
                                N/A
                            @endif
                        </strong>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small d-block">Payment Destination:</span>
                        @if($invoice->target_type === 'DDC')
                            <span class="badge bg-purple-100 text-purple-800 me-1">Physical DDC Wallet</span>
                            <strong class="text-dark">{{ $invoice->ddc?->name ?? 'DDC #'.$invoice->ddc_id }}</strong>
                        @else
                            <span class="badge bg-info-subtle text-info me-1">Seller / Vendor Wallet</span>
                            <strong class="text-dark">{{ $invoice->seller?->company_name ?? $invoice->seller?->name ?? 'Seller #'.$invoice->seller_id }}</strong>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small d-block">Created Date:</span>
                        <strong class="text-dark">{{ $invoice->created_at->format('d M Y, h:i A') }}</strong>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small d-block">Reporting Store:</span>
                        <strong class="text-dark">{{ $invoice->adc?->name ?? 'ADC Store' }}</strong>
                    </div>
                </div>

                <h6 class="fw-bold text-slate-800 mb-2">Excess Stock Items Breakdown</h6>
                <div class="table-responsive bg-light p-2 rounded mb-3">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-muted small border-bottom">
                                <th>Product Name</th>
                                <th class="text-center">Order Qty</th>
                                <th class="text-center text-success">Excess Qty</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-end pe-2">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @if($invoice->order && $invoice->order->items)
                                @foreach($invoice->order->items->filter(fn($i) => $i->excess_qty > 0) as $item)
                                    <tr>
                                        <td>
                                            <span class="fw-semibold text-slate-800 d-block">{{ $item->product_name ?? $item->product?->product_name }}</span>
                                        </td>
                                        <td class="text-center fw-medium">{{ $item->quantity }}</td>
                                        <td class="text-center text-success fw-bold">+{{ $item->excess_qty }}</td>
                                        <td class="text-end fw-semibold text-dark">₹{{ number_format($item->price, 2) }}</td>
                                        <td class="text-end pe-2 fw-bold text-slate-800">₹{{ number_format($item->excess_qty * $item->price, 2) }}</td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </div>

                <div class="d-flex align-items-center justify-content-between p-3 bg-success-subtle rounded text-success mb-0">
                    <span class="fw-semibold">Total Payable Amount for Excess Stock:</span>
                    <h4 class="fw-bold mb-0">₹{{ number_format($invoice->total_excess_amount, 2) }}</h4>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom">
                <h6 class="fw-bold mb-0">ADC Wallet Payment Action</h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small">Status:</span>
                    @if($invoice->status === 'PAID')
                        <span class="badge bg-success">PAID</span>
                    @else
                        <span class="badge bg-warning text-dark">UNPAID</span>
                    @endif
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small">Total Amount:</span>
                    <strong class="fs-5 text-dark">₹{{ number_format($invoice->total_excess_amount, 2) }}</strong>
                </div>

                @if($invoice->status !== 'PAID')
                    <hr>
                    <form method="POST" action="{{ route('adc.excess-invoices.pay', $invoice->id) }}" onsubmit="return confirm('Pay ₹{{ number_format($invoice->total_excess_amount, 2) }} using your ADC Wallet balance?');">
                        @csrf
                        <button type="submit" class="btn btn-success w-100 py-2 fw-bold mb-2">
                            <i class="bi bi-wallet2 me-1"></i> Pay via ADC Wallet
                        </button>
                    </form>
                    <form method="POST" action="{{ route('adc.excess-invoices.cancel', $invoice->id) }}" onsubmit="return confirm('Undo / cancel this pending excess stock invoice? You will be able to claim/submit it again.');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-outline-danger w-100 py-1.5 small">
                            <i class="bi bi-arrow-counterclockwise me-1"></i> Undo / Cancel Invoice
                        </button>
                    </form>
                @else
                    <div class="alert alert-success mb-0 text-center">
                        <i class="bi bi-check-circle-fill text-success fs-3 d-block mb-1"></i>
                        <strong>Paid via ADC Wallet</strong>
                        <p class="small text-muted mb-0 mt-1">Wallet deduction completed and credited to supplier.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
