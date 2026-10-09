@extends('layouts.ddc')

@section('title', 'Credit Notes List')
@section('page-title', 'Credit Notes')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                            <iconify-icon icon="solar:document-text-bold-duotone" class="align-middle text-success me-1"></iconify-icon>
                            Credit Notes
                        </h4>
                        <p class="text-muted small mb-0">View all Credit Notes generated for PO Discrepancy Claims and ADC Stock-In Claims.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <ul class="nav nav-pills card-header-pills" id="ddcCreditNotesTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="my-cn-tab" data-bs-toggle="tab" data-bs-target="#my-cn" type="button" role="tab">
                        <i class="bi bi-building me-1"></i> PO Credit Notes ({{ $creditNotes->total() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="adc-cn-tab" data-bs-toggle="tab" data-bs-target="#adc-cn" type="button" role="tab">
                        <i class="bi bi-shop me-1"></i> ADC Credit Notes ({{ $adcCreditNotes->count() }})
                    </button>
                </li>
            </ul>

            <form method="GET" action="{{ route('ddc.credit-notes.index') }}" class="d-flex gap-2 align-items-center">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search CN #, PO #..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-sm btn-primary fw-semibold text-nowrap"><i class="bi bi-filter"></i> Filter</button>
            </form>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="tab-content" id="ddcCreditNotesTabContent">
            {{-- DDC PO CREDIT NOTES TAB --}}
            <div class="tab-pane fade show active" id="my-cn" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-50 border-top border-bottom small text-muted">
                            <tr>
                                <th class="ps-3">CN Number</th>
                                <th>PO Number</th>
                                <th>Seller</th>
                                <th>Products / Items</th>
                                <th>Claim Type</th>
                                <th class="text-end">Amount</th>
                                <th class="text-center">Status</th>
                                <th>Date</th>
                                <th class="pe-3 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($creditNotes as $cn)
                                <tr>
                                    <td class="ps-3">
                                        <span class="fw-bold text-slate-800">{{ $cn->credit_note_number }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('ddc.purchase-orders.show', $cn->purchase_order_id) }}" class="fw-semibold text-primary">
                                            #{{ $cn->purchaseOrder?->po_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="fw-medium text-dark">{{ $cn->seller?->company_name ?: $cn->seller?->name }}</span>
                                    </td>
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
                                    <td>
                                        <span class="badge bg-primary-subtle text-primary px-2 py-1 fs-11">{{ $cn->type }}</span>
                                    </td>
                                    <td class="text-end fw-bold text-success fs-14">
                                        ₹{{ number_format($cn->amount, 2) }}
                                    </td>
                                    <td class="text-center">
                                        @if($cn->status === 'GENERATED')
                                            <span class="badge bg-warning text-dark px-2 py-1 fs-11">GENERATED</span>
                                        @elseif($cn->status === 'SETTLED')
                                            <span class="badge bg-success text-white px-2 py-1 fs-11">SETTLED</span>
                                        @else
                                            <span class="badge bg-secondary text-white px-2 py-1 fs-11">{{ $cn->status }}</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $cn->created_at->format('d M Y') }}</td>
                                    <td class="pe-3 text-end">
                                        <a href="{{ route('ddc.credit-notes.show', $cn->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2.5 fs-12">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted small">No PO Credit Notes found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($creditNotes->hasPages())
                    <div class="card-footer bg-white border-top py-3">
                        {{ $creditNotes->links() }}
                    </div>
                @endif
            </div>

            {{-- ADC CREDIT NOTES TAB --}}
            <div class="tab-pane fade" id="adc-cn" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-50 border-top border-bottom small text-muted">
                            <tr>
                                <th class="ps-3">CN Number</th>
                                <th>ADC Order #</th>
                                <th>ADC Store</th>
                                <th>Products / Items</th>
                                <th>Claim Type</th>
                                <th class="text-end">Amount</th>
                                <th class="text-center">Status</th>
                                <th>Date</th>
                                <th class="pe-3 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($adcCreditNotes as $acn)
                                <tr>
                                    <td class="ps-3">
                                        <span class="fw-bold text-slate-800">{{ $acn->credit_note_number }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold text-dark">#{{ $acn->adcOrder?->order_number }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-medium text-dark">{{ $acn->adc?->name }}</span>
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
                                    <td>
                                        <span class="badge bg-info-subtle text-info px-2 py-1 fs-11">{{ $acn->type }}</span>
                                    </td>
                                    <td class="text-end fw-bold text-success fs-14">
                                        ₹{{ number_format($acn->amount, 2) }}
                                    </td>
                                    <td class="text-center">
                                        @if($acn->status === 'GENERATED')
                                            <span class="badge bg-warning text-dark px-2 py-1 fs-11">GENERATED</span>
                                        @elseif($acn->status === 'SETTLED')
                                            <span class="badge bg-success text-white px-2 py-1 fs-11">SETTLED</span>
                                        @else
                                            <span class="badge bg-secondary text-white px-2 py-1 fs-11">{{ $acn->status }}</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $acn->created_at->format('d M Y') }}</td>
                                    <td class="pe-3 text-end">
                                        <a href="{{ route('ddc.adc-credit-notes.show', $acn->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2.5 fs-12">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted small">No ADC Credit Notes found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
