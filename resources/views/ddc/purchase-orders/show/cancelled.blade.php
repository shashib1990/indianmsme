@extends('layouts.ddc')

@section('title', 'PO Cancelled – ' . $purchaseOrder->po_number)
@section('page-title', 'Purchase Order – Cancelled')

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

        {{-- Right: Cancellation Info --}}
        <div class="col-xl-4">
            <div class="card border-0 shadow-sm border-start border-3 border-danger">
                <div class="card-body p-4">
                    <div class="d-flex align-items-start gap-3">
                        <div class="rounded-circle bg-danger d-flex align-items-center justify-content-center flex-shrink-0"
                             style="width:44px;height:44px;">
                            <iconify-icon icon="solar:close-circle-bold-duotone" class="text-white fs-22"></iconify-icon>
                        </div>
                        <div>
                            <h6 class="fw-bold text-slate-800 mb-1">Purchase Order Cancelled</h6>
                            <p class="text-muted small mb-0">
                                This Purchase Order has been cancelled.
                                @if($purchaseOrder->payment_status === 'Paid')
                                    The paid amount of <strong>₹{{ number_format($purchaseOrder->accepted_amount, 2) }}</strong>
                                    has been refunded to your wallet.
                                @else
                                    No payment was deducted.
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-0 py-3 border-bottom">
                    <h6 class="fw-bold text-slate-800 mb-0">Order Summary</h6>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">PO Number</span>
                        <strong class="font-monospace">{{ $purchaseOrder->po_number }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Date Placed</span>
                        <span class="text-slate-700 small">{{ $purchaseOrder->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Cancelled By</span>
                        <span class="text-slate-700 small">
                            {{ str_starts_with($purchaseOrder->rejection_reason ?? '', 'Cancelled by DDC') ? 'You (DDC)' : 'System' }}
                        </span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Payment Status</span>
                        @if($purchaseOrder->payment_status === 'Paid')
                            <span class="badge bg-info-subtle text-info">Refunded</span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary">Not Required</span>
                        @endif
                    </div>
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
