@extends('layouts.ddc')

@section('title', 'PO Processing – ' . $purchaseOrder->po_number)
@section('page-title', 'Purchase Order – Processing')

@section('content')
    @include('ddc.purchase-orders.show._partials.header')

    @php
        $acceptedAmount = $purchaseOrder->items->where('status', 'Accepted')->sum('total');
        $invoice        = $purchaseOrder->invoice;
        $hasDdcInvoice  = $invoice && $invoice->ddc_invoice_number && $invoice->ddc_invoice_file;
    @endphp

    <div class="row g-4">
        {{-- Left: Accepted Items --}}
        <div class="col-xl-8">
            @include('ddc.purchase-orders.show._partials.items_table', [
                'showSellerResponse' => true,
                'showBatchInfo'      => false,
            ])
        </div>

        {{-- Right: Payment Info + DDC Invoice Upload --}}
        <div class="col-xl-4">
            {{-- Payment Confirmation --}}
            <div class="card border-0 shadow-sm border-start border-3 border-success mb-4">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle bg-success d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:44px;height:44px;">
                            <iconify-icon icon="solar:verified-check-bold" class="text-white fs-22"></iconify-icon>
                        </div>
                        <div>
                            <h6 class="fw-bold text-slate-800 mb-1">Payment Confirmed</h6>
                            <p class="text-muted small mb-2">
                                Your payment of <strong class="text-success">₹{{ number_format($purchaseOrder->accepted_amount, 2) }}</strong>
                                has been processed successfully. The order is now awaiting dispatch from the seller.
                            </p>
                            @if($purchaseOrder->payment_reference)
                                <div class="bg-light rounded px-3 py-2">
                                    <span class="text-muted small">Payment Ref:</span>
                                    <code class="small fw-semibold d-block">{{ $purchaseOrder->payment_reference }}</code>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            {{-- Seller Invoice (read-only) --}}
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
                            <span class="text-muted small">Invoice Number</span>
                            <strong class="text-slate-800">{{ $invoice->invoice_number }}</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Invoice Date</span>
                            <span class="text-slate-700 small">{{ $invoice->invoice_date ? \Carbon\Carbon::parse($invoice->invoice_date)->format('d M Y') : '—' }}</span>
                        </div>
                        <a href="{{ route('ddc.purchase-orders.invoice', $purchaseOrder->id) }}" target="_blank"
                           class="btn btn-outline-primary w-100 btn-sm py-2 mt-2">
                            <iconify-icon icon="solar:printer-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon>
                            View / Print Invoice
                        </a>
                    </div>
                </div>
            @endif
            {{-- Awaiting Dispatch Info --}}
            <div class="card border-0 shadow-sm">
                <div class="card-body py-3 px-4">
                    <div class="d-flex align-items-center gap-3">
                        <iconify-icon icon="solar:delivery-bold-duotone" class="text-warning fs-28 flex-shrink-0"></iconify-icon>
                        <div>
                            <div class="fw-semibold text-slate-800 small">Awaiting Dispatch</div>
                            <div class="text-muted" style="font-size:12px;">The seller will dispatch your order soon. You will be notified once dispatched.</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
