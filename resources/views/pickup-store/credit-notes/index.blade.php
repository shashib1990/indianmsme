@extends('layouts.pickup_store')

@section('title', 'Credit Notes')
@section('page-title', 'Credit Notes Management')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">Credit Notes</h4>
                        <p class="text-muted small mb-0">View all Credit Notes issued for purchase orders assigned to your Authorized Pickup Store.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom py-3">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $tab == 'ddc_po' ? 'active fw-bold' : '' }}" href="{{ route('store.credit-notes.index', ['tab' => 'ddc_po']) }}">
                    <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="me-1"></iconify-icon> DDC PO Credit Notes ({{ $creditNotes->total() }})
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab == 'adc_order' ? 'active fw-bold' : '' }}" href="{{ route('store.credit-notes.index', ['tab' => 'adc_order']) }}">
                    <iconify-icon icon="solar:shop-2-bold-duotone" class="me-1"></iconify-icon> ADC Order Credit Notes ({{ $adcCreditNotes->total() }})
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        @if($tab == 'ddc_po')
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-slate-700">
                        <tr>
                            <th class="ps-3">Credit Note #</th>
                            <th>PO Number</th>
                            <th>DDC Name</th>
                            <th>Seller / Vendor</th>
                            <th>Products / Items</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($creditNotes as $cn)
                            <tr>
                                <td class="ps-3 fw-bold text-primary">{{ $cn->credit_note_number }}</td>
                                <td>{{ $cn->purchaseOrder->po_number ?? 'N/A' }}</td>
                                <td>{{ $cn->ddc->name ?? 'N/A' }}</td>
                                <td>{{ $cn->seller->company_name ?? $cn->seller->name ?? 'N/A' }}</td>
                                <td>
                                    @php
                                        $itemNames = ($cn->debitNote && $cn->debitNote->items && $cn->debitNote->items->count() > 0)
                                            ? $cn->debitNote->items->pluck('product_name')->filter()->values()
                                            : ($cn->purchaseOrder && $cn->purchaseOrder->items ? $cn->purchaseOrder->items->pluck('product_name')->filter()->values() : collect());
                                        $itemCount = $itemNames->count();
                                    @endphp
                                    @if($itemCount > 0)
                                        <span class="fw-semibold text-dark fs-13">{{ $itemNames->take(2)->implode(', ') }}{{ $itemCount > 2 ? ' +'.($itemCount - 2).' more' : '' }}</span>
                                        <span class="d-block text-muted small font-monospace">({{ $itemCount }} {{ Str::plural('item', $itemCount) }})</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="fw-bold text-success">₹{{ number_format($cn->amount, 2) }}</td>
                                <td><span class="badge bg-success-subtle text-success">Issued</span></td>
                                <td>{{ $cn->created_at->format('d M Y, h:i A') }}</td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('store.credit-notes.show', $cn->id) }}" class="btn btn-sm btn-outline-primary">
                                        <iconify-icon icon="solar:eye-bold-duotone" class="align-middle"></iconify-icon> View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No DDC PO Credit Notes found for your store.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($creditNotes->hasPages())
                <div class="p-3">{{ $creditNotes->links() }}</div>
            @endif
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-slate-700">
                        <tr>
                            <th class="ps-3">Credit Note #</th>
                            <th>Order #</th>
                            <th>ADC Store</th>
                            <th>Supplier (Target)</th>
                            <th>Products / Items</th>
                            <th>Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adcCreditNotes as $acn)
                            <tr>
                                <td class="ps-3 fw-bold text-primary">{{ $acn->credit_note_number }}</td>
                                <td>{{ $acn->adcOrder->order_number ?? 'N/A' }}</td>
                                <td>{{ $acn->adc->name ?? 'N/A' }}</td>
                                <td>
                                    @if($acn->target_type == 'SELLER')
                                        <span class="badge bg-info-subtle text-info">Seller</span> {{ $acn->seller->company_name ?? $acn->seller->name ?? '' }}
                                    @else
                                        <span class="badge bg-primary-subtle text-primary">DDC</span> {{ $acn->ddc->name ?? '' }}
                                    @endif
                                </td>
                                <td>
                                    @php
                                        $itemNames = ($acn->debitNote && $acn->debitNote->items && $acn->debitNote->items->count() > 0)
                                            ? $acn->debitNote->items->pluck('product_name')->filter()->values()
                                            : ($acn->adcOrder && $acn->adcOrder->items ? $acn->adcOrder->items->pluck('product_name')->filter()->values() : collect());
                                        $itemCount = $itemNames->count();
                                    @endphp
                                    @if($itemCount > 0)
                                        <span class="fw-semibold text-dark fs-13">{{ $itemNames->take(2)->implode(', ') }}{{ $itemCount > 2 ? ' +'.($itemCount - 2).' more' : '' }}</span>
                                        <span class="d-block text-muted small font-monospace">({{ $itemCount }} {{ Str::plural('item', $itemCount) }})</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="fw-bold text-success">₹{{ number_format($acn->amount, 2) }}</td>
                                <td><span class="badge bg-success-subtle text-success">Issued</span></td>
                                <td>{{ $acn->created_at->format('d M Y, h:i A') }}</td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('store.adc-credit-notes.show', $acn->id) }}" class="btn btn-sm btn-outline-primary">
                                        <iconify-icon icon="solar:eye-bold-duotone" class="align-middle"></iconify-icon> View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">No ADC Credit Notes found for your store orders.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($adcCreditNotes->hasPages())
                <div class="p-3">{{ $adcCreditNotes->links() }}</div>
            @endif
        @endif
    </div>
</div>
@endsection
