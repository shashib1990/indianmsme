@extends('layouts.ddc')

@section('title', 'Debit Notes')
@section('page-title', 'Discrepancy Debit Notes')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-md-8">
                        <h4 class="fw-bold text-slate-800 mb-1">DDC Stock Discrepancy Debit Notes</h4>
                        <p class="text-muted small mb-0">Track all discrepancy claims (Shortage & Damage) for DDC Purchase Orders and ADC Stock-In claims.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <ul class="nav nav-pills card-header-pills" id="ddcDebitNotesTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="my-dn-tab" data-bs-toggle="tab" data-bs-target="#my-dn" type="button" role="tab">
                        <i class="bi bi-building me-1"></i> My PO Debit Notes (To Seller) ({{ $debitNotes->total() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="adc-dn-tab" data-bs-toggle="tab" data-bs-target="#adc-dn" type="button" role="tab">
                        <i class="bi bi-shop me-1"></i> ADC Stock-In Claims (From ADC) ({{ $adcDebitNotes->count() }})
                    </button>
                </li>
            </ul>

            <form method="GET" action="{{ route('ddc.debit-notes.index') }}" class="d-flex gap-2 align-items-center">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search DN #, PO #...">
                <button type="submit" class="btn btn-sm btn-primary text-nowrap"><i class="bi bi-filter"></i> Filter</button>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="tab-content" id="ddcDebitNotesTabContent">
            {{-- DDC PO DEBIT NOTES TAB --}}
            <div class="tab-pane fade show active" id="my-dn" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">DN Number</th>
                                <th>PO Number</th>
                                <th>Seller</th>
                                <th>Discrepancy Type</th>
                                <th>Short / Damage Qty</th>
                                <th>Discrepancy Amount</th>
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
                                        <a href="{{ route('ddc.purchase-orders.show', $dn->purchase_order_id) }}" class="fw-semibold text-decoration-none">
                                            #{{ $dn->purchaseOrder?->po_number }}
                                        </a>
                                    </td>
                                    <td>{{ $dn->seller?->company_name ?: $dn->seller?->name }}</td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-dark">{{ $dn->discrepancy_type }}</span>
                                    </td>
                                    <td>
                                        S: <strong>{{ $dn->shortage_qty }}</strong> | D: <strong>{{ $dn->damage_qty }}</strong>
                                    </td>
                                    <td>
                                        <strong>₹{{ number_format($dn->final_amount ?: $dn->total_discrepancy_amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        @if($dn->status === 'PENDING')
                                            <span class="badge bg-warning text-dark">PENDING SELLER</span>
                                        @elseif($dn->status === 'SELLER_APPROVED')
                                            <span class="badge bg-info text-white">APPROVED BY SELLER</span>
                                        @elseif($dn->status === 'SELLER_REJECTED')
                                            <span class="badge bg-danger">SELLER REJECTED</span>
                                        @elseif($dn->status === 'SETTLED')
                                            <span class="badge bg-success">SETTLED</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $dn->status }}</span>
                                        @endif
                                    </td>
                                    <td class="pe-3 text-end">
                                        <a href="{{ route('ddc.debit-notes.show', $dn->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No PO Debit Notes found.</td>
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

            {{-- ADC STOCK-IN CLAIMS TAB --}}
            <div class="tab-pane fade" id="adc-dn" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">DN Number</th>
                                <th>ADC Order #</th>
                                <th>ADC Store</th>
                                <th>Discrepancy Type</th>
                                <th>Short / Damage Qty</th>
                                <th>Discrepancy Amount</th>
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
                                    <td>{{ $adn->adc?->name ?? 'ADC #'.$adn->adc_id }}</td>
                                    <td>
                                        <span class="badge bg-secondary-subtle text-dark">{{ $adn->discrepancy_type }}</span>
                                    </td>
                                    <td>
                                        S: <strong>{{ $adn->shortage_qty }}</strong> | D: <strong>{{ $adn->damage_qty }}</strong>
                                    </td>
                                    <td>
                                        <strong>₹{{ number_format($adn->final_amount ?: $adn->total_discrepancy_amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        @if(in_array($adn->status, ['PENDING', 'PENDING_SELLER_APPROVAL']))
                                            <span class="badge bg-warning text-dark">PENDING APPROVAL</span>
                                        @elseif($adn->status === 'SELLER_APPROVED')
                                            <span class="badge bg-info text-white">APPROVED BY DDC</span>
                                        @elseif($adn->status === 'SELLER_REJECTED')
                                            <span class="badge bg-danger">REJECTED BY DDC</span>
                                        @elseif($adn->status === 'SETTLED')
                                            <span class="badge bg-success">SETTLED</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $adn->status }}</span>
                                        @endif
                                    </td>
                                    <td class="pe-3 text-end">
                                        <a href="{{ route('ddc.adc-debit-notes.show', $adn->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2">
                                            View & Respond
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted">No ADC Stock-In Debit Notes found.</td>
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
