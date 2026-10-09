@extends('layouts.adc')

@section('title', 'Credit Note ' . $creditNote->credit_note_number)
@section('page-title', 'Credit Note Details')

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ route('adc.credit-notes.index') }}" class="btn btn-sm btn-outline-secondary">
                <i class="bi bi-arrow-left me-1"></i> Back to Credit Notes
            </a>
            <span class="badge bg-success fs-13 px-2.5 py-1">{{ $creditNote->status ?? 'SETTLED' }}</span>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="fw-bold text-slate-800 mb-0">Credit Note #{{ $creditNote->credit_note_number }}</h5>
                <span class="text-muted small">{{ $creditNote->created_at->format('d M Y, h:i A') }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <span class="text-muted small d-block">ADC Order Number:</span>
                        <strong class="fw-bold text-dark">
                            @if($creditNote->adcOrder)
                                <a href="{{ route('adc.orders.show', $creditNote->adc_order_id) }}" class="text-decoration-none">#{{ $creditNote->adcOrder->order_number }}</a>
                            @else
                                N/A
                            @endif
                        </strong>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small d-block">Issuer (Target Supplier):</span>
                        @if($creditNote->target_type === 'DDC')
                            <span class="badge bg-purple-100 text-purple-800 me-1">Physical DDC</span>
                            <strong class="text-dark">{{ $creditNote->ddc?->name ?? 'DDC #'.$creditNote->ddc_id }}</strong>
                        @else
                            <span class="badge bg-info-subtle text-info me-1">Seller / Vendor</span>
                            <strong class="text-dark">{{ $creditNote->seller?->company_name ?? $creditNote->seller?->name ?? 'Seller #'.$creditNote->seller_id }}</strong>
                        @endif
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small d-block">Date Issued:</span>
                        <strong class="text-dark">{{ $creditNote->created_at->format('d M Y, h:i A') }}</strong>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small d-block">Linked Debit Note:</span>
                        <strong>
                            @if($creditNote->debitNote)
                                <a href="{{ route('adc.debit-notes.show', $creditNote->adc_debit_note_id ?? $creditNote->debit_note_id ?? $creditNote->debitNote->id) }}" class="fw-bold font-monospace text-danger text-decoration-none">
                                    {{ $creditNote->debitNote->debit_note_number }}
                                </a>
                            @else
                                <span class="text-muted">N/A</span>
                            @endif
                        </strong>
                    </div>
                </div>

                <div class="p-3 bg-success-subtle rounded border border-success-subtle d-flex align-items-center justify-content-between">
                    <div>
                        <small class="d-block fw-bold text-success text-uppercase">Total Credit Amount</small>
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
                <h6 class="fw-bold mb-0">Credit Settlement Info</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-success mb-3">
                    <i class="bi bi-check-circle-fill text-success fs-5 d-block mb-1"></i>
                    <strong>Wallet Credit Settled</strong>
                    <p class="small mb-0 mt-1">This credit amount of ₹{{ number_format($creditNote->amount, 2) }} has been credited to your ADC Wallet.</p>
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
