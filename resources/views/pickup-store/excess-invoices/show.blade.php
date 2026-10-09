@extends('layouts.pickup_store')

@section('title', 'Excess Invoice Details - ' . $invoice->invoice_number)
@section('page-title', 'Excess Invoice Details')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">Excess Invoice: {{ $invoice->invoice_number }}</h4>
                <p class="text-muted small mb-0">Linked to Purchase Order #{{ $invoice->purchaseOrder->po_number ?? 'N/A' }}</p>
            </div>
            <a href="{{ route('store.excess-invoices.index') }}" class="btn btn-outline-secondary btn-sm">
                <iconify-icon icon="solar:arrow-left-bold-duotone" class="align-middle me-1"></iconify-icon> Back to List
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title fw-bold text-slate-800 mb-0">Invoice Information</h5>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <span class="text-muted small d-block">Invoice Number</span>
                        <strong class="fs-16 text-primary">{{ $invoice->invoice_number }}</strong>
                    </div>
                    <div class="col-md-6">
                        <span class="text-muted small d-block">Invoice Date</span>
                        <span>{{ optional($invoice->invoice_date)->format('d M Y') ?? $invoice->created_at->format('d M Y') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="card-title fw-bold text-slate-800 mb-0">Entities Involved</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="text-muted small d-block">DDC Name</span>
                    <strong class="text-slate-800">{{ $invoice->purchaseOrder->ddc->name ?? 'N/A' }}</strong>
                </div>
                <div class="mb-3">
                    <span class="text-muted small d-block">Seller / Vendor</span>
                    <strong class="text-slate-800">{{ $invoice->purchaseOrder->seller->company_name ?? $invoice->purchaseOrder->seller->name ?? 'N/A' }}</strong>
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
