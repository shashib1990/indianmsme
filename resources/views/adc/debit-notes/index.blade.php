@extends('layouts.adc')

@section('title', 'Debit Notes')
@section('page-title', 'Stock-In Debit Notes')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-md-8">
                        <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">ADC Stock Discrepancy Debit Notes</h4>
                        <p class="text-muted small mb-0">Track shortage and damage discrepancy claims generated during stock-in (Routed to Physical DDC or Seller).</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom">
        <form method="GET" action="{{ route('adc.debit-notes.index') }}" class="row g-2 align-items-center">
            <div class="col-md-6">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search DN #, Order #, Target DDC/Seller...">
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="PENDING" {{ request('status') === 'PENDING' || request('status') === 'PENDING_SELLER_APPROVAL' ? 'selected' : '' }}>PENDING APPROVAL</option>
                    <option value="SETTLED" {{ request('status') === 'SETTLED' ? 'selected' : '' }}>SETTLED</option>
                    <option value="SELLER_REJECTED" {{ request('status') === 'SELLER_REJECTED' ? 'selected' : '' }}>REJECTED</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-sm btn-primary w-100"><i class="bi bi-filter me-1"></i> Filter</button>
            </div>
        </form>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light border-top border-bottom">
                    <tr>
                        <th class="ps-3">DN Number</th>
                        <th>ADC Order #</th>
                        <th>Target (DDC / Seller)</th>
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
                                @if($dn->adcOrder)
                                    <a href="{{ route('adc.orders.show', $dn->adc_order_id) }}" class="fw-semibold text-decoration-none">
                                        #{{ $dn->adcOrder->order_number }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($dn->target_type === 'DDC')
                                    <span class="badge bg-purple-100 text-purple-800 me-1">DDC</span>
                                    <span class="fw-medium">{{ $dn->ddc?->name ?? 'DDC #'.$dn->ddc_id }}</span>
                                @else
                                    <span class="badge bg-info-subtle text-info me-1">Seller</span>
                                    <span class="fw-medium">{{ $dn->seller?->company_name ?? $dn->seller?->name ?? 'Seller #'.$dn->seller_id }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-secondary-subtle text-dark">{{ str_replace('_', ' ', $dn->discrepancy_type) }}</span>
                            </td>
                            <td>
                                S: <strong>{{ $dn->shortage_qty }}</strong> | D: <strong>{{ $dn->damage_qty }}</strong>
                            </td>
                            <td>
                                <strong>₹{{ number_format($dn->final_amount ?: $dn->total_discrepancy_amount, 2) }}</strong>
                            </td>
                            <td>
                                @if(in_array($dn->status, ['PENDING', 'PENDING_SELLER_APPROVAL']))
                                    <span class="badge bg-warning text-dark">PENDING APPROVAL</span>
                                @elseif($dn->status === 'SELLER_APPROVED')
                                    <span class="badge bg-info text-white">APPROVED BY TARGET</span>
                                @elseif($dn->status === 'SELLER_REJECTED')
                                    <span class="badge bg-danger">REJECTED BY TARGET</span>
                                @elseif($dn->status === 'SETTLED')
                                    <span class="badge bg-success">SETTLED</span>
                                @else
                                    <span class="badge bg-secondary">{{ $dn->status }}</span>
                                @endif
                            </td>
                            <td class="pe-3 text-end">
                                <a href="{{ route('adc.debit-notes.show', $dn->id) }}" class="btn btn-sm btn-outline-primary py-1 px-3">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i> No stock-in debit notes found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($debitNotes->hasPages())
        <div class="card-footer bg-white py-3 border-top">
            {{ $debitNotes->links() }}
        </div>
    @endif
</div>
@endsection
