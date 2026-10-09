@extends('layouts.vendor')

@section('title', 'Debit Notes')
@section('page-title', 'Stock Discrepancy Debit Notes')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-md-8">
                        <h4 class="fw-bold text-slate-800 mb-1">Stock Discrepancy Debit Notes</h4>
                        <p class="text-muted small mb-0">Manage stock-in discrepancies reported by DDCs and ADCs, verify quantities, and approve/reject claims.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <ul class="nav nav-pills card-header-pills" id="debitNotesTab" role="tablist">
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

            <form method="GET" action="{{ route('vendor.debit-notes.index') }}" class="d-flex gap-2 align-items-center">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search DN #, Order #...">
                <button type="submit" class="btn btn-sm btn-primary text-nowrap"><i class="bi bi-filter"></i> Filter</button>
            </form>
        </div>
    </div>
    <div class="card-body p-0">
        <div class="tab-content" id="debitNotesTabContent">
            {{-- DDC DEBIT NOTES TAB --}}
            <div class="tab-pane fade show active" id="ddc-dn" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">DN Number</th>
                                <th>PO Number</th>
                                <th>DDC Center</th>
                                <th>Discrepancy Type</th>
                                <th>Short / Damaged</th>
                                <th>Claim Amount</th>
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
                                        <a href="{{ route('vendor.purchase-orders.show', $dn->purchase_order_id) }}" class="fw-semibold text-decoration-none">
                                            #{{ $dn->purchaseOrder?->po_number }}
                                        </a>
                                    </td>
                                    <td>{{ $dn->ddc?->name }}</td>
                                    <td>
                                        @if($dn->discrepancy_type === 'SHORTAGE')
                                            <span class="badge bg-warning-subtle text-warning">SHORTAGE</span>
                                        @elseif($dn->discrepancy_type === 'DAMAGE')
                                            <span class="badge bg-danger-subtle text-danger">DAMAGE</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">{{ $dn->discrepancy_type }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        Short: <strong>{{ $dn->shortage_qty }}</strong> | Damage: <strong>{{ $dn->damage_qty }}</strong>
                                    </td>
                                    <td>
                                        <strong>₹{{ number_format($dn->seller_amount ?: ($dn->final_amount ?: $dn->total_discrepancy_amount), 2) }}</strong>
                                    </td>
                                    <td>
                                        @if($dn->status === 'PENDING')
                                            <span class="badge bg-warning text-dark">PENDING</span>
                                        @elseif($dn->status === 'SELLER_APPROVED')
                                            <span class="badge bg-info text-white">APPROVED BY SELLER</span>
                                        @elseif($dn->status === 'SELLER_REJECTED')
                                            <span class="badge bg-danger">REJECTED BY SELLER</span>
                                        @elseif($dn->status === 'SETTLED')
                                            <span class="badge bg-success">SETTLED</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $dn->status }}</span>
                                        @endif
                                    </td>
                                    <td class="pe-3 text-end">
                                        <a href="{{ route('vendor.debit-notes.show', $dn->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2">
                                            View & Verify
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

            {{-- ADC DEBIT NOTES TAB --}}
            <div class="tab-pane fade" id="adc-dn" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="ps-3">DN Number</th>
                                <th>ADC Order #</th>
                                <th>ADC Store</th>
                                <th>Discrepancy Type</th>
                                <th>Short / Damaged</th>
                                <th>Claim Amount</th>
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
                                        @if($adn->discrepancy_type === 'SHORTAGE')
                                            <span class="badge bg-warning-subtle text-warning">SHORTAGE</span>
                                        @elseif($adn->discrepancy_type === 'DAMAGE')
                                            <span class="badge bg-danger-subtle text-danger">DAMAGE</span>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary">{{ $adn->discrepancy_type }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        Short: <strong>{{ $adn->shortage_qty }}</strong> | Damage: <strong>{{ $adn->damage_qty }}</strong>
                                    </td>
                                    <td>
                                        <strong>₹{{ number_format($adn->final_amount ?: $adn->total_discrepancy_amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        @if(in_array($adn->status, ['PENDING', 'PENDING_SELLER_APPROVAL']))
                                            <span class="badge bg-warning text-dark">PENDING APPROVAL</span>
                                        @elseif($adn->status === 'SELLER_APPROVED')
                                            <span class="badge bg-info text-white">APPROVED BY SELLER</span>
                                        @elseif($adn->status === 'SELLER_REJECTED')
                                            <span class="badge bg-danger">REJECTED BY SELLER</span>
                                        @elseif($adn->status === 'SETTLED')
                                            <span class="badge bg-success">SETTLED</span>
                                        @else
                                            <span class="badge bg-secondary">{{ $adn->status }}</span>
                                        @endif
                                    </td>
                                    <td class="pe-3 text-end">
                                        <a href="{{ route('vendor.adc-debit-notes.show', $adn->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2">
                                            View & Respond
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
