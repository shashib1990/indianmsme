@extends('layouts.ddc')

@section('title', 'PO Dispatched – ' . $purchaseOrder->po_number)
@section('page-title', 'Purchase Order – Dispatched')

@section('content')
    @include('ddc.purchase-orders.show._partials.header')

    @php
        $invoice       = $purchaseOrder->invoice;
        $hasDdcInvoice = $invoice && $invoice->ddc_invoice_number && $invoice->ddc_invoice_file;
    @endphp

    <div class="row g-4">
        <div class="col-xl-8">
            @include('ddc.purchase-orders.show._partials.items_table', ['showSellerResponse' => true, 'showBatchInfo' => false])
        </div>

        <div class="col-xl-4">
            {{-- Dispatched Notice --}}
            <div class="card border-0 shadow-sm border-start border-3 border-warning mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle bg-warning-subtle d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px;">
                            <iconify-icon icon="solar:delivery-bold-duotone" class="text-warning fs-22"></iconify-icon>
                        </div>
                        <div>
                            <h6 class="fw-bold text-slate-800 mb-1">Order Dispatched</h6>
                            <p class="text-muted small mb-0">The seller has dispatched your order. Please confirm once you physically receive the package.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- DDC Invoice --}}
            <div class="card border-0 shadow-sm mb-4 border-start border-3 {{ $hasDdcInvoice ? 'border-success' : 'border-warning' }}">
                <div class="card-header bg-white border-0 py-3 border-bottom">
                    <h6 class="fw-bold text-slate-800 mb-0">
                        <iconify-icon icon="solar:document-bold-duotone" class="align-middle me-1 {{ $hasDdcInvoice ? 'text-success' : 'text-warning' }} fs-18"></iconify-icon>
                        DDC Invoice
                        @if($hasDdcInvoice)
                            <span class="badge bg-success-subtle text-success ms-2 fs-11">✓ Uploaded</span>
                        @else
                            <span class="badge bg-warning-subtle text-warning ms-2 fs-11">Required</span>
                        @endif
                    </h6>
                </div>
                <div class="card-body">
                    @if($hasDdcInvoice)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">DDC Invoice No.</span>
                            <strong>{{ $invoice->ddc_invoice_number }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Date</span>
                            <span class="small">{{ \Carbon\Carbon::parse($invoice->ddc_invoice_date)->format('d M Y') }}</span>
                        </div>
                        <a href="{{ $invoice->ddc_invoice_file }}" target="_blank" class="btn btn-outline-success btn-sm w-100 py-2 mb-2">
                            <iconify-icon icon="solar:document-bold-duotone" class="align-middle me-1"></iconify-icon>
                            View DDC Invoice File
                        </a>
                        <button class="btn btn-outline-secondary btn-sm w-100 py-2"
                                data-bs-toggle="modal" data-bs-target="#ddcInvoiceModal"
                                data-action="{{ route('ddc.purchase-orders.ddc-invoice', $purchaseOrder->id) }}"
                                data-invoice-number="{{ $invoice->ddc_invoice_number }}"
                                data-invoice-date="{{ $invoice->ddc_invoice_date ? $invoice->ddc_invoice_date->format('Y-m-d') : '' }}"
                                data-invoice-file="{{ $invoice->ddc_invoice_file }}">
                            <iconify-icon icon="solar:pen-bold-duotone" class="align-middle me-1"></iconify-icon> Edit DDC Invoice
                        </button>
                    @else
                        <p class="text-muted small mb-3">Upload your DDC Invoice before confirming receipt.</p>
                        <button class="btn btn-warning w-100 fw-semibold py-2"
                                data-bs-toggle="modal" data-bs-target="#ddcInvoiceModal"
                                data-action="{{ route('ddc.purchase-orders.ddc-invoice', $purchaseOrder->id) }}"
                                data-invoice-number="" data-invoice-date="" data-invoice-file="">
                            <iconify-icon icon="solar:upload-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                            Upload DDC Invoice
                        </button>
                    @endif
                </div>
            </div>

            {{-- Seller Invoice --}}
            @if($invoice && $invoice->invoice_number)
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3 border-bottom">
                        <h6 class="fw-bold text-slate-800 mb-0">
                            <iconify-icon icon="solar:document-text-bold-duotone" class="align-middle me-1 text-primary fs-18"></iconify-icon>
                            Seller Invoice
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Invoice No.</span>
                            <strong>{{ $invoice->invoice_number }}</strong>
                        </div>
                        <a href="{{ route('ddc.purchase-orders.invoice', $purchaseOrder->id) }}" target="_blank"
                           class="btn btn-outline-primary btn-sm w-100 py-2">
                            <iconify-icon icon="solar:printer-bold-duotone" class="align-middle me-1"></iconify-icon>
                            View / Print Invoice
                        </a>
                    </div>
                </div>
            @endif

            {{-- Proceed to Stock In --}}
            <div class="card border-0 shadow-sm border-start border-3 border-success">
                <div class="card-header bg-white border-0 py-3 border-bottom">
                    <h6 class="fw-bold text-slate-800 mb-0">
                        <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="align-middle me-1 text-success fs-18"></iconify-icon>
                        Confirm Receipt
                    </h6>
                </div>
                <div class="card-body">
                    @if(!$hasDdcInvoice)
                        <div class="alert alert-warning border-0 small mb-3 py-2 px-3">
                            <iconify-icon icon="solar:danger-triangle-bold-duotone" class="align-middle me-1"></iconify-icon>
                            Upload DDC Invoice first to proceed.
                        </div>
                        <button class="btn btn-success w-100 fw-semibold py-2" disabled>
                            <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="me-1 align-middle fs-18"></iconify-icon>
                            Proceed to Stock In
                        </button>
                    @else
                        <div class="alert alert-info border-0 small mb-3 py-2 px-3">
                            Verify your received package before proceeding to stock in.
                        </div>
                        <a href="{{ route('ddc.purchase-orders.receive', $purchaseOrder->id) }}" class="btn btn-success w-100 fw-semibold py-2">
                            <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="me-1 align-middle fs-18"></iconify-icon>
                            Proceed to Stock In
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @include('ddc.purchase-orders.show._partials.ddc_invoice_modal')
@endsection

@push('scripts')
@include('ddc.purchase-orders.show._partials.ddc_invoice_modal_script')
@endpush
