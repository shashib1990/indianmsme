@extends('layouts.ddc')

@section('title', 'ADC Credit Note ' . $creditNote->credit_note_number)
@section('page-title', 'ADC Credit Note Details')

@section('content')
<div class="mb-3">
    <a href="{{ route('ddc.credit-notes.index') }}" class="btn btn-sm btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back to Credit Notes
    </a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
        <h5 class="fw-bold mb-0">ADC Credit Note: {{ $creditNote->credit_note_number }}</h5>
        <span class="badge bg-success fs-6"><i class="bi bi-check-circle"></i> {{ $creditNote->status }}</span>
    </div>
    <div class="card-body">
        <div class="row g-3 mb-4">
            <div class="col-md-6">
                <small class="text-muted d-block">ADC Order Number</small>
                <strong>#{{ $creditNote->adcOrder?->order_number }}</strong>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">ADC Store</small>
                <strong>{{ $creditNote->adc?->name ?? 'ADC #'.$creditNote->adc_id }}</strong>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">Credit Note Type</small>
                <span class="badge bg-info-subtle text-info">{{ $creditNote->type }}</span>
            </div>
            <div class="col-md-6">
                <small class="text-muted d-block">Created Date</small>
                <strong>{{ $creditNote->created_at->format('d M Y, h:i A') }}</strong>
            </div>
        </div>

        <h6 class="fw-bold mb-3 border-bottom pb-2">Credit Summary</h6>
        <div class="p-3 bg-light rounded d-flex justify-content-between align-items-center">
            <span class="fw-medium text-dark">Total Settled Amount:</span>
            <strong class="fs-4 text-success">₹{{ number_format($creditNote->amount, 2) }}</strong>
        </div>
    </div>
</div>
@endsection
