@extends('layouts.admin')

@section('title', 'Credit Notes Settlement Monitoring')
@section('page-title', 'ADPL Credit Notes Audit')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                            <iconify-icon icon="solar:document-text-bold-duotone" class="align-middle text-success me-1"></iconify-icon>
                            Credit Notes Management
                        </h4>
                        <p class="text-muted small mb-0">Monitor and audit all Credit Notes issued across DDC centers, ADC stores, and Sellers.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <ul class="nav nav-pills card-header-pills" id="adminCreditNotesTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="ddc-cn-tab" data-bs-toggle="tab" data-bs-target="#ddc-cn" type="button" role="tab">
                        <i class="bi bi-building me-1"></i> DDC PO Credit Notes ({{ $creditNotes->total() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="adc-cn-tab" data-bs-toggle="tab" data-bs-target="#adc-cn" type="button" role="tab">
                        <i class="bi bi-shop me-1"></i> ADC Order Credit Notes ({{ $adcCreditNotes->count() }})
                    </button>
                </li>
            </ul>

            <form method="GET" action="{{ route('admin.credit-notes.index') }}" class="d-flex gap-2 align-items-center">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search CN #, PO #, Order #..." value="{{ request('search') }}">
                <button type="submit" class="btn btn-sm btn-primary fw-semibold text-nowrap"><i class="bi bi-filter"></i> Filter</button>
            </form>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="tab-content" id="adminCreditNotesTabContent">
            {{-- DDC PO CREDIT NOTES TAB --}}
            <div class="tab-pane fade show active" id="ddc-cn" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-50 border-top border-bottom small text-muted">
                            <tr>
                                <th class="ps-3">CN Number</th>
                                <th>PO Number</th>
                                <th>DDC Center</th>
                                <th>Seller</th>
                                <th>Type</th>
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
                                        <a href="{{ route('admin.purchase-orders.show', $cn->purchase_order_id) }}" class="fw-semibold text-primary">
                                            #{{ $cn->purchaseOrder?->po_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <span class="fw-medium text-dark">{{ $cn->ddc?->name }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-medium text-dark">{{ $cn->seller?->company_name ?: $cn->seller?->name }}</span>
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
                                        <a href="{{ route('admin.credit-notes.show', $cn->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2.5 fs-12">
                                            View Audit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="9" class="text-center py-4 text-muted small">No DDC Credit Notes found.</td>
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

            {{-- ADC ORDER CREDIT NOTES TAB --}}
            <div class="tab-pane fade" id="adc-cn" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light bg-opacity-50 border-top border-bottom small text-muted">
                            <tr>
                                <th class="ps-3">CN Number</th>
                                <th>ADC Order #</th>
                                <th>ADC Store</th>
                                <th>Target (DDC / Seller)</th>
                                <th>Type</th>
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
                                        @if($acn->target_type === 'DDC')
                                            <span class="badge bg-purple-100 text-purple-800 me-1">DDC</span>
                                            {{ $acn->ddc?->name ?? 'DDC #'.$acn->ddc_id }}
                                        @else
                                            <span class="badge bg-info-subtle text-info me-1">Seller</span>
                                            {{ $acn->seller?->name ?? 'Seller #'.$acn->seller_id }}
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
                                        <a href="{{ route('admin.adc-credit-notes.show', $acn->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2.5 fs-12">
                                            View Audit
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
