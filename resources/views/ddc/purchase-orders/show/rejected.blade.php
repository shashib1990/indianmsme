@extends('layouts.ddc')

@section('title', 'PO Rejected – ' . $purchaseOrder->po_number)
@section('page-title', 'Purchase Order – Rejected')

@section('content')
    @include('ddc.purchase-orders.show._partials.header')

    <div class="row g-4">
        {{-- Left: Items Table --}}
        <div class="col-xl-8">
            @include('ddc.purchase-orders.show._partials.items_table', [
                'showSellerResponse' => true,
                'showBatchInfo'      => false,
            ])
        </div>

        {{-- Right: Rejection Info --}}
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm border-start border-3 border-danger">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle bg-danger-subtle d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:44px;height:44px;">
                            <iconify-icon icon="solar:close-circle-bold-duotone" class="text-danger fs-22"></iconify-icon>
                        </div>
                        <div>
                            <h6 class="fw-bold text-slate-800 mb-1">Purchase Order Rejected</h6>
                            <p class="text-muted small mb-0">
                                The seller has rejected all items in this Purchase Order.
                                No payment has been deducted. You can create a new Purchase Order if needed.
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-0 py-3 border-bottom">
                    <h6 class="fw-bold text-slate-800 mb-0">
                        <iconify-icon icon="solar:document-text-bold-duotone" class="align-middle me-1 text-danger fs-18"></iconify-icon>
                        Order Summary
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">PO Number</span>
                        <strong class="font-monospace">{{ $purchaseOrder->po_number }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Date Placed</span>
                        <span class="text-slate-700 small">{{ $purchaseOrder->created_at->format('d M Y') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">PO Status</span>
                        <span class="badge bg-danger-subtle text-danger">Rejected</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Payment Status</span>
                        <span class="badge bg-secondary-subtle text-secondary">Not Required</span>
                    </div>
                    @if($purchaseOrder->rejection_reason)
                        <hr class="text-muted opacity-20">
                        <div class="small text-muted mb-1">Rejection Reason:</div>
                        <div class="alert alert-danger border-0 small py-2 px-3 mb-0">
                            {{ $purchaseOrder->rejection_reason }}
                        </div>
                    @endif
                    <hr class="text-muted opacity-20">
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Estimated Amount</span>
                        <span class="fw-semibold text-slate-800">₹{{ number_format($purchaseOrder->total_amount, 2) }}</span>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <a href="{{ route('ddc.purchase-orders.create') }}" class="btn btn-primary w-100 py-2 fw-semibold">
                    <iconify-icon icon="solar:add-circle-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                    Create New Purchase Order
                </a>
            </div>
        </div>
    </div>
@endsection
