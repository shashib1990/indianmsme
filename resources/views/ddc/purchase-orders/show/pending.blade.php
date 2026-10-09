@extends('layouts.ddc')

@section('title', 'PO Created – ' . $purchaseOrder->po_number)
@section('page-title', 'Purchase Order – Created')

@section('content')
    @include('ddc.purchase-orders.show._partials.header')

    <div class="row g-4">
        {{-- Left: Items --}}
        <div class="col-xl-8">
            @include('ddc.purchase-orders.show._partials.items_table', [
                'showSellerResponse' => true,
                'showBatchInfo'      => false,
            ])
        </div>

        {{-- Right: Summary --}}
        <div class="col-xl-4">
            {{-- PO Summary --}}
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
                        <strong class="text-slate-800 font-monospace">{{ $purchaseOrder->po_number }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Date Placed</span>
                        <span class="text-slate-700 small">{{ $purchaseOrder->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                    @if($purchaseOrder->ama_code)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">AMA Code</span>
                            <span class="text-slate-700 fw-semibold small">{{ $purchaseOrder->ama_code }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Total Items</span>
                        <span class="text-slate-700 small">{{ $purchaseOrder->items->count() }}</span>
                    </div>
                    <hr class="text-muted opacity-20">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Estimated Amount</span>
                        <span class="fw-bold text-slate-800">₹{{ number_format($purchaseOrder->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Status Info Card --}}
            <div class="card border-0 shadow-sm border-start border-3 border-warning">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle bg-warning-subtle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:44px;height:44px;">
                            <iconify-icon icon="solar:clock-circle-bold-duotone" class="text-warning fs-22"></iconify-icon>
                        </div>
                        <div>
                            <h6 class="fw-bold text-slate-800 mb-1">Awaiting Seller Response</h6>
                            <p class="text-muted small mb-3">
                                Your Purchase Order has been placed. The seller will review and accept or reject each item.
                                You will be able to proceed to payment once the seller responds.
                            </p>
                            <div class="alert alert-warning border-0 bg-warning-subtle small mb-0 py-2 px-3">
                                <iconify-icon icon="solar:info-circle-bold-duotone" class="align-middle me-1"></iconify-icon>
                                You can <strong>cancel this PO</strong> at any time before the seller responds.
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
