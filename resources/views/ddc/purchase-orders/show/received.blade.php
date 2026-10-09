@extends('layouts.ddc')

@section('title', 'PO Received – ' . $purchaseOrder->po_number)
@section('page-title', 'Purchase Order – Received')

@section('content')
    @include('ddc.purchase-orders.show._partials.header')

    @php $invoice = $purchaseOrder->invoice; @endphp

    <div class="row g-4">
        <div class="col-xl-8">
            @include('ddc.purchase-orders.show._partials.items_table', ['showSellerResponse' => true, 'showBatchInfo' => true])
        </div>

        <div class="col-xl-4">
            {{-- Completed Notice --}}
            <div class="card border-0 shadow-sm border-start border-3 border-success mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle bg-success d-flex align-items-center justify-content-center flex-shrink-0" style="width:44px;height:44px;">
                            <iconify-icon icon="solar:check-circle-bold-duotone" class="text-white fs-22"></iconify-icon>
                        </div>
                        <div>
                            <h6 class="fw-bold text-slate-800 mb-1">Order Received & Stocked In</h6>
                            <p class="text-muted small mb-0">This Purchase Order has been fully received and added to your DDC inventory.</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Summary --}}
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-0 py-3 border-bottom">
                    <h6 class="fw-bold text-slate-800 mb-0">
                        <iconify-icon icon="solar:document-text-bold-duotone" class="align-middle me-1 text-primary fs-18"></iconify-icon>
                        Order Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">PO Number</span>
                        <strong class="font-monospace">{{ $purchaseOrder->po_number }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Payment Ref</span>
                        <code class="small fw-semibold">{{ $purchaseOrder->payment_reference ?? '—' }}</code>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Accepted Amount</span>
                        <span class="fw-bold text-success">₹{{ number_format($purchaseOrder->accepted_amount, 2) }}</span>
                    </div>
                    <hr class="text-muted opacity-20">
                    @if($invoice)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Seller Invoice</span>
                            <a href="{{ route('ddc.purchase-orders.invoice', $purchaseOrder->id) }}" target="_blank" class="text-primary fw-semibold small">
                                {{ $invoice->invoice_number }}
                            </a>
                        </div>
                        @if($invoice->ddc_invoice_number)
                            <div class="d-flex justify-content-between mb-2">
                                <span class="text-muted small">DDC Invoice</span>
                                <a href="{{ $invoice->ddc_invoice_file }}" target="_blank" class="text-success fw-semibold small">
                                    {{ $invoice->ddc_invoice_number }}
                                </a>
                            </div>
                        @endif
                    @endif
                </div>
            </div>

            {{-- Invoice Downloads --}}
            @if($invoice && $invoice->invoice_number)
                <div class="d-grid gap-2">
                    <a href="{{ route('ddc.purchase-orders.invoice', $purchaseOrder->id) }}" target="_blank"
                       class="btn btn-outline-primary fw-semibold py-2">
                        <iconify-icon icon="solar:printer-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                        View / Print Seller Invoice
                    </a>
                    @if($invoice->ddc_invoice_file)
                        <a href="{{ $invoice->ddc_invoice_file }}" target="_blank"
                           class="btn btn-outline-success fw-semibold py-2">
                            <iconify-icon icon="solar:document-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                            View DDC Invoice
                        </a>
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection
