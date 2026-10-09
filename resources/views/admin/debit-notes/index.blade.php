@extends('layouts.admin')

@section('title', 'Debit Notes & Settlement (ADPL)')
@section('page-title', 'ADPL Discrepancy Debit Notes')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-md-8">
                        <h4 class="fw-bold text-slate-800 mb-1">Debit & Credit Notes Settlement (ADPL Escrow)</h4>
                        <p class="text-muted small mb-0">Monitor DDC and ADC stock-in discrepancies, verify seller approvals, and execute settlements.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <ul class="nav nav-pills card-header-pills" id="adminDebitNotesTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="ddc-dn-tab" data-bs-toggle="tab" data-bs-target="#ddc-dn" type="button" role="tab">
                        <i class="bi bi-building me-1"></i> DDC PO Debit Notes ({{ $debitNotes->total() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="adc-dn-tab" data-bs-toggle="tab" data-bs-target="#adc-dn" type="button" role="tab">
                        <i class="bi bi-shop me-1"></i> ADC Order Debit Notes ({{ $adcDebitNotes->count() }})
                    </button>
                </li>
            </ul>

            <form method="GET" action="{{ route('admin.debit-notes.index') }}" class="d-flex gap-2 align-items-center">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search DN #, Order #...">
                <button type="submit" class="btn btn-sm btn-primary text-nowrap"><i class="bi bi-filter"></i> Filter</button>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="tab-content" id="adminDebitNotesTabContent">
            {{-- DDC PO DEBIT NOTES TAB --}}
            <div class="tab-pane fade show active" id="ddc-dn" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">DN Number</th>
                                <th>PO Number</th>
                                <th>Seller</th>
                                <th>DDC Center</th>
                                <th>Discrepancy</th>
                                <th>Final Amount</th>
                                <th>Status</th>
                                <th class="pe-3 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($debitNotes as $dn)
                                <tr>
                                    <td class="ps-3">
                                        <strong class="text-primary font-monospace">{{ $dn->debit_note_number }}</strong>
                                        <span class="d-block text-muted small">{{ $dn->created_at->format('d M Y, h:i A') }}</span>
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.purchase-orders.show', $dn->purchase_order_id) }}" class="fw-semibold text-decoration-none">
                                            #{{ $dn->purchaseOrder?->po_number }}
                                        </a>
                                    </td>
                                    <td>
                                        <strong>{{ $dn->seller?->company_name ?: $dn->seller?->name }}</strong>
                                    </td>
                                    <td>{{ $dn->ddc?->name }}</td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-dark me-1">{{ $dn->discrepancy_type }}</span>
                                        <span class="small text-muted">S:{{ $dn->shortage_qty }} | D:{{ $dn->damage_qty }}</span>
                                    </td>
                                    <td>
                                        <strong>₹{{ number_format($dn->final_amount ?: $dn->total_discrepancy_amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        @if($dn->status === 'PENDING')
                                            <span class="badge bg-warning text-dark">PENDING SELLER</span>
                                        @elseif($dn->status === 'SELLER_APPROVED')
                                            <span class="badge bg-primary text-white">READY FOR ADPL SETTLEMENT</span>
                                        @elseif($dn->status === 'SELLER_REJECTED')
                                            <span class="badge bg-danger">SELLER REJECTED</span>
                                        @elseif($dn->status === 'SETTLED')
                                            <span class="badge bg-success">SETTLED</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $dn->status }}</span>
                                        @endif
                                    </td>
                                    <td class="pe-3 text-end">
                                        <a href="{{ route('admin.debit-notes.show', $dn->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2">
                                            View Audit & Settle
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No DDC Debit Notes found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($debitNotes->hasPages())
                    <div class="card-footer bg-white border-0 py-3">
                        {{ $debitNotes->links() }}
                    </div>
                @endif
            </div>

            {{-- ADC ORDER DEBIT NOTES TAB --}}
            <div class="tab-pane fade" id="adc-dn" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">DN Number</th>
                                <th>ADC Order #</th>
                                <th>ADC Store</th>
                                <th>Target (DDC / Seller)</th>
                                <th>Discrepancy</th>
                                <th>Final Amount</th>
                                <th>Status</th>
                                <th class="pe-3 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($adcDebitNotes as $adn)
                                <tr>
                                    <td class="ps-3">
                                        <strong class="text-primary font-monospace">{{ $adn->debit_note_number }}</strong>
                                        <span class="d-block text-muted small">{{ $adn->created_at->format('d M Y, h:i A') }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-semibold">#{{ $adn->order?->order_number }}</span>
                                    </td>
                                    <td>
                                        <strong>{{ $adn->adc?->name ?? 'ADC #'.$adn->adc_id }}</strong>
                                    </td>
                                    <td>
                                        @if($adn->target_type === 'DDC')
                                            <span class="badge bg-purple-100 text-purple-800 me-1">DDC</span>
                                            {{ $adn->ddc?->name ?? 'DDC #'.$adn->ddc_id }}
                                        @else
                                            <span class="badge bg-info-subtle text-info me-1">Seller</span>
                                            {{ $adn->seller?->name ?? 'Seller #'.$adn->seller_id }}
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-dark me-1">{{ $adn->discrepancy_type }}</span>
                                        <span class="small text-muted">S:{{ $adn->shortage_qty }} | D:{{ $adn->damage_qty }}</span>
                                    </td>
                                    <td>
                                        <strong>₹{{ number_format($adn->final_amount ?: $adn->total_discrepancy_amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        @if(in_array($adn->status, ['PENDING', 'PENDING_SELLER_APPROVAL']))
                                            <span class="badge bg-warning text-dark">PENDING APPROVAL</span>
                                        @elseif($adn->status === 'SELLER_APPROVED')
                                            <span class="badge bg-primary text-white">APPROVED</span>
                                        @elseif($adn->status === 'SELLER_REJECTED')
                                            <span class="badge bg-danger">REJECTED</span>
                                        @elseif($adn->status === 'SETTLED')
                                            <span class="badge bg-success">SETTLED</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $adn->status }}</span>
                                        @endif
                                    </td>
                                    <td class="pe-3 text-end">
                                        <a href="{{ route('admin.adc-debit-notes.show', $adn->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2">
                                            View Audit
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No ADC Debit Notes found.</td>
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
