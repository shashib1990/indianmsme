@extends('layouts.ddc')

@section('title', 'PO Accepted – ' . $purchaseOrder->po_number)
@section('page-title', 'Purchase Order – Accepted')

@section('content')
    @include('ddc.purchase-orders.show._partials.header')

    @php
        $acceptedAmount   = $purchaseOrder->items->where('status', 'Accepted')->sum('total');
        $rejectedCount    = $purchaseOrder->items->where('status', 'Rejected')->count();
        $pendingCount     = $purchaseOrder->items->where('status', 'Pending')->count();
        $walletBalance    = auth('ddc')->user()->profile?->wallet_balance ?? 0;
        $canPay           = $pendingCount === 0 && $acceptedAmount > 0;
    @endphp

    <div class="row g-4">
        {{-- Left: Items Table --}}
        <div class="col-xl-8">
            @include('ddc.purchase-orders.show._partials.items_table', [
                'showSellerResponse' => true,
                'showBatchInfo'      => false,
            ])
        </div>

        {{-- Right: Summary + Payment --}}
        <div class="col-xl-4">
            {{-- Order Summary --}}
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
                        <strong class="font-monospace text-slate-800">{{ $purchaseOrder->po_number }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">AMA Code</span>
                        <span class="text-slate-700 small fw-semibold">{{ $purchaseOrder->ama_code ?? '—' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Seller Response</span>
                        @if($rejectedCount > 0 && $acceptedAmount > 0)
                            <span class="badge bg-info-subtle text-info">Partially Accepted</span>
                        @elseif($acceptedAmount > 0)
                            <span class="badge bg-success-subtle text-success">Accepted</span>
                        @else
                            <span class="badge bg-danger-subtle text-danger">All Rejected</span>
                        @endif
                    </div>
                    <hr class="text-muted opacity-20">
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted small">Estimated Amount</span>
                        <span class="fw-semibold text-slate-800">₹{{ number_format($purchaseOrder->total_amount, 2) }}</span>
                    </div>
                    @if($rejectedCount > 0)
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small text-danger">Rejected Amount</span>
                            <span class="fw-semibold text-danger">− ₹{{ number_format($purchaseOrder->items->where('status','Rejected')->sum('total'), 2) }}</span>
                        </div>
                    @endif
                    <div class="d-flex justify-content-between">
                        <span class="text-muted small">Accepted Amount (Payable)</span>
                        <span class="fw-bold text-success fs-16">₹{{ number_format($acceptedAmount, 2) }}</span>
                    </div>
                </div>
            </div>

            {{-- Payment Card --}}
            <div class="card border-0 shadow-sm border-start border-3 border-primary">
                <div class="card-header bg-white border-0 py-3 border-bottom">
                    <h6 class="fw-bold text-slate-800 mb-0">
                        <iconify-icon icon="solar:wallet-money-bold-duotone" class="align-middle me-1 text-primary fs-18"></iconify-icon>
                        Wallet Payment
                    </h6>
                </div>
                <div class="card-body">
                    <div class="text-center bg-light rounded p-3 mb-3">
                        <span class="text-muted small d-block">Your Wallet Balance</span>
                        <h4 class="fw-bold mb-0 {{ $walletBalance >= $acceptedAmount ? 'text-primary' : 'text-danger' }}">
                            ₹{{ number_format($walletBalance, 2) }}
                        </h4>
                    </div>

                    @if($pendingCount > 0)
                        <div class="alert alert-warning border-0 small mb-0 py-2 px-3" role="alert">
                            <iconify-icon icon="solar:clock-circle-bold-duotone" class="align-middle me-1"></iconify-icon>
                            Waiting for seller response on <strong>{{ $pendingCount }}</strong> item(s).
                        </div>
                    @elseif($acceptedAmount <= 0)
                        <div class="alert alert-danger border-0 small mb-0 py-2 px-3" role="alert">
                            <iconify-icon icon="solar:info-circle-bold-duotone" class="align-middle me-1"></iconify-icon>
                            All items were rejected. No payment required.
                        </div>
                        <form action="{{ route('ddc.purchase-orders.pay', $purchaseOrder->id) }}" method="POST" class="mt-3">
                            @csrf
                            <button type="submit" class="btn btn-outline-secondary w-100 fw-semibold confirm-action"
                                data-confirm-message="This will close the order with 0 payment since all items were rejected.">
                                Close Order (₹0 — All Rejected)
                            </button>
                        </form>
                    @else
                        <div class="alert alert-success border-0 small mb-3 py-2 px-3" role="alert">
                            <iconify-icon icon="solar:verified-check-bold" class="align-middle me-1"></iconify-icon>
                            Seller has responded. Pay now for accepted items.
                        </div>
                        @if($walletBalance < $acceptedAmount)
                            <div class="alert alert-danger border-0 small mb-3 py-2 px-3" role="alert">
                                <iconify-icon icon="solar:danger-triangle-bold-duotone" class="align-middle me-1"></iconify-icon>
                                Insufficient balance. Need ₹{{ number_format($acceptedAmount - $walletBalance, 2) }} more.
                                <a href="{{ route('ddc.wallet.index') }}" class="fw-semibold">Top Up Wallet →</a>
                            </div>
                        @endif
                        <form action="{{ route('ddc.purchase-orders.pay', $purchaseOrder->id) }}" method="POST">
                            @csrf
                            <div class="d-flex justify-content-between small text-muted mb-1">
                                <span>Amount to Pay:</span>
                                <strong class="text-slate-800">₹{{ number_format($acceptedAmount, 2) }}</strong>
                            </div>
                            <small class="text-muted d-block mb-3" style="font-size:11px;">
                                Rejected products (if any) are excluded from payment.
                            </small>
                            <button type="submit" class="btn btn-success w-100 fw-semibold py-2 confirm-action"
                                    {{ $walletBalance < $acceptedAmount ? 'disabled' : '' }}
                                    data-confirm-message="Deduct ₹{{ number_format($acceptedAmount, 2) }} from your wallet?">
                                <iconify-icon icon="solar:wallet-money-bold-duotone" class="me-1 align-middle fs-18"></iconify-icon>
                                Pay Now — ₹{{ number_format($acceptedAmount, 2) }}
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
