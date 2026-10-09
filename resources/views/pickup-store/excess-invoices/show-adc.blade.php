@extends('layouts.pickup_store')

@section('title', 'ADC Excess Invoice Details - ' . $invoice->invoice_number)
@section('page-title', 'ADC Excess Invoice Details')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">ADC Excess Invoice: {{ $invoice->invoice_number }}</h4>
                <p class="text-muted small mb-0">Linked to ADC Order #{{ $invoice->adcOrder->order_number ?? 'N/A' }}</p>
            </div>
            <a href="{{ route('store.excess-invoices.index', ['tab' => 'adc_order']) }}" class="btn btn-outline-secondary btn-sm">
                <iconify-icon icon="solar:arrow-left-bold-duotone" class="align-middle me-1"></iconify-icon> Back to List
            </a>
        </div>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3">
                <h5 class="card-title fw-bold text-slate-800 mb-0">Excess Items</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0">
                        <thead class="bg-light text-slate-700">
                            <tr>
                                <th class="ps-3">Item / Product</th>
                                <th>Excess Qty</th>
                                <th>Unit Price</th>
                                <th class="text-end pe-3">Total Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoice->items as $item)
                                <tr>
                                    <td class="ps-3 fw-semibold">{{ $item->product->name ?? 'Product' }}</td>
                                    <td>{{ $item->excess_qty }}</td>
                                    <td>₹{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-end pe-3 fw-bold">₹{{ number_format($item->total_amount, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center py-3 text-muted">No line items found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        <tfoot class="bg-light fw-bold">
                            <tr>
                                <td colspan="3" class="ps-3">Grand Total:</td>
                                <td class="text-end pe-3 text-primary fs-16">₹{{ number_format($invoice->total_amount, 2) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card shadow-sm border-0">
            <div class="card-header bg-white py-3">
                <h5 class="card-title fw-bold text-slate-800 mb-0">Entities & Status</h5>
            </div>
            <div class="card-body">
                <div class="mb-3">
                    <span class="text-muted small d-block">Status</span>
                    @if($invoice->status == 'PAID')
                        <span class="badge bg-success-subtle text-success fs-13 px-2 py-1">Paid</span>
                    @else
                        <span class="badge bg-warning-subtle text-warning fs-13 px-2 py-1">Pending Payment</span>
                    @endif
                </div>
                <hr>
                <div class="mb-3">
                    <span class="text-muted small d-block">ADC Store</span>
                    <strong class="text-slate-800">{{ $invoice->adc->name ?? 'N/A' }}</strong>
                </div>
                <div class="mb-3">
                    <span class="text-muted small d-block">Target Supplier</span>
                    @if($invoice->target_type == 'SELLER')
                        <span class="badge bg-info-subtle text-info">Seller</span> {{ $invoice->seller->company_name ?? $invoice->seller->name ?? 'N/A' }}
                    @else
                        <span class="badge bg-primary-subtle text-primary">DDC</span> {{ $invoice->ddc->name ?? 'N/A' }}
                    @endif
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
