@extends('layouts.adc')

@section('title', 'Generate Excess Stock Note')
@section('page-title', 'Generate Excess Stock Note')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <a href="{{ route('adc.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary py-1 px-2.5">
                                    <iconify-icon icon="solar:arrow-left-bold" class="align-middle me-1"></iconify-icon>
                                    Back to Order
                                </a>
                                <span class="badge bg-primary-subtle text-primary px-2.5 py-1.5 fs-12 fw-semibold">
                                    Excess Stock Invoice
                                </span>
                            </div>
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                Generate Excess Stock Invoice for Order #{{ $order->order_number }}
                            </h4>
                            <p class="text-muted small mb-0">
                                Target Supplier: 
                                @if($targetType === 'DDC')
                                    <span class="badge bg-purple-100 text-purple-800 ms-1 me-1">DDC</span>
                                    <strong class="text-dark">{{ $targetDdc->name ?? 'Assigned DDC #'.$order->assigned_ddc_id }}</strong>
                                @else
                                    <span class="badge bg-info-subtle text-info ms-1 me-1">Seller</span>
                                    <strong class="text-dark">{{ $targetSeller->company_name ?: ($targetSeller->name ?? 'Direct Seller') }}</strong>
                                @endif
                                | Total Excess Items: <strong class="text-dark">{{ $excessItems->count() }}</strong>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <iconify-icon icon="solar:danger-circle-bold" class="align-middle me-1 fs-18"></iconify-icon>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('adc.excess-invoices.store', $order->id) }}" method="POST">
        @csrf

        <div class="row g-4">
            <!-- Excess Items Table -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                        <h6 class="fw-bold text-slate-800 mb-0">
                            <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="align-middle me-1 text-primary fs-18"></iconify-icon>
                            Excess Received Products Breakdown
                        </h6>
                        <span class="badge bg-light text-muted px-2.5 py-1">Auto-fetched from Stock In</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light bg-opacity-50 border-top border-bottom small text-muted">
                                    <tr>
                                        <th class="ps-3" style="width: 220px;">Product Name</th>
                                        <th class="text-center" style="width: 100px;">Ordered Qty</th>
                                        <th class="text-center text-primary" style="width: 100px;">Excess Qty</th>
                                        <th class="text-center" style="width: 100px;">Total Stocked In</th>
                                        <th class="text-end" style="width: 110px;">Unit Price</th>
                                        <th class="pe-3 text-end" style="width: 130px;">Excess Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php $grandExcessAmount = 0; @endphp
                                    @foreach($excessItems as $item)
                                        @php
                                            $itemExcessAmount = $item->excess_qty * (float) $item->price;
                                            $grandExcessAmount += $itemExcessAmount;
                                        @endphp
                                        <tr>
                                            <td class="ps-3">
                                                <div class="fw-semibold text-slate-800 fs-14">
                                                    {{ $item->product_name ?? $item->product?->product_name }}
                                                </div>
                                                <div class="text-muted small font-monospace">
                                                    {{ $item->product_code ?? 'PROD-'.$item->product_id }}
                                                </div>
                                            </td>
                                            <td class="text-center fw-medium">{{ $item->quantity }}</td>
                                            <td class="text-center fw-bold text-primary">+{{ $item->excess_qty }}</td>
                                            <td class="text-center text-success fw-medium">{{ $item->quantity + $item->excess_qty }}</td>
                                            <td class="text-end">₹{{ number_format($item->price, 2) }}</td>
                                            <td class="pe-3 text-end fw-bold text-slate-800">
                                                ₹{{ number_format($itemExcessAmount, 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot class="bg-light bg-opacity-50 border-top">
                                    <tr>
                                        <td colspan="5" class="ps-3 text-end fw-bold text-slate-700">Total Excess Invoice Amount:</td>
                                        <td class="pe-3 text-end fw-extrabold text-primary fs-15">
                                            ₹{{ number_format($grandExcessAmount, 2) }}
                                        </td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Side Card: Order & Target Summary -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-bottom py-3">
                        <h6 class="fw-bold text-slate-800 mb-0">
                            <iconify-icon icon="solar:document-text-bold-duotone" class="align-middle me-1 text-primary fs-18"></iconify-icon>
                            Excess Invoice Summary
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Order Number:</span>
                            <span class="fw-semibold text-slate-800">#{{ $order->order_number }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small">Target Supplier:</span>
                            @if($targetType === 'DDC')
                                <span class="badge bg-purple-100 text-purple-800">DDC: {{ $targetDdc->name ?? 'DDC #'.$order->assigned_ddc_id }}</span>
                            @else
                                <span class="badge bg-info-subtle text-info">Seller: {{ $targetSeller->company_name ?: ($targetSeller->name ?? 'Seller') }}</span>
                            @endif
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted small">Invoice Amount:</span>
                            <span class="fw-bold text-primary fs-16">₹{{ number_format($grandExcessAmount, 2) }}</span>
                        </div>

                        <hr class="my-3">

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-slate-700">Remarks / Description</label>
                            <textarea name="remarks" rows="4" class="form-control form-control-sm" placeholder="Add any details about excess received stock..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-success w-100 py-2 fw-semibold shadow-sm" onclick="return confirm('Generate Excess Stock Invoice and pay ₹{{ number_format($grandExcessAmount, 2) }} via your ADC Wallet balance?');">
                            <iconify-icon icon="solar:wallet-money-bold" class="align-middle me-1 fs-18"></iconify-icon>
                            Submit & Pay via ADC Wallet
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection
