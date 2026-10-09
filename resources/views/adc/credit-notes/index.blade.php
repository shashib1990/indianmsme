@extends('layouts.adc')

@section('title', 'Credit Notes')
@section('page-title', 'Stock Credit Notes')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="row align-items-center g-3">
                    <div class="col-md-8">
                        <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">ADC Stock Discrepancy Credit Notes</h4>
                        <p class="text-muted small mb-0">View all Credit Notes issued to ADC by Physical DDC or Sellers for approved discrepancy claims.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom">
        <form method="GET" action="{{ route('adc.credit-notes.index') }}" class="row g-2 align-items-center">
            <div class="col-md-6">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search CN #, Order #, Issuer...">
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="ISSUED" {{ request('status') === 'ISSUED' ? 'selected' : '' }}>Issued</option>
                    <option value="SETTLED" {{ request('status') === 'SETTLED' ? 'selected' : '' }}>Settled</option>
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
                        <th class="ps-3">CN Number</th>
                        <th>ADC Order #</th>
                        <th>Issuer (DDC / Seller)</th>
                        <th>Linked Debit Note #</th>
                        <th>Credit Amount</th>
                        <th>Status</th>
                        <th class="pe-3 text-end">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($creditNotes as $cn)
                        <tr>
                            <td class="ps-3">
                                <strong class="text-success font-monospace">{{ $cn->credit_note_number }}</strong>
                                <span class="d-block text-muted small">{{ $cn->created_at->format('d M Y, h:i A') }}</span>
                            </td>
                            <td>
                                @if($cn->adcOrder)
                                    <a href="{{ route('adc.orders.show', $cn->adc_order_id) }}" class="fw-semibold text-decoration-none">
                                        #{{ $cn->adcOrder->order_number }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($cn->target_type === 'DDC')
                                    <span class="badge bg-purple-100 text-purple-800 me-1">DDC</span>
                                    <span class="fw-medium">{{ $cn->ddc?->name ?? 'DDC #'.$cn->ddc_id }}</span>
                                @else
                                    <span class="badge bg-info-subtle text-info me-1">Seller</span>
                                    <span class="fw-medium">{{ $cn->seller?->company_name ?? $cn->seller?->name ?? 'Seller #'.$cn->seller_id }}</span>
                                @endif
                            </td>
                            <td>
                                @if($cn->debitNote)
                                    <a href="{{ route('adc.debit-notes.show', $cn->adc_debit_note_id ?? $cn->debit_note_id ?? $cn->debitNote->id) }}" class="fw-semibold font-monospace text-decoration-none text-danger">
                                        {{ $cn->debitNote->debit_note_number }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <strong class="text-success">₹{{ number_format($cn->amount, 2) }}</strong>
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success px-2.5 py-1 fs-12">{{ $cn->status ?? 'SETTLED' }}</span>
                            </td>
                            <td class="pe-3 text-end">
                                <a href="{{ route('adc.credit-notes.show', $cn->id) }}" class="btn btn-sm btn-outline-success py-1 px-3">
                                    View Details
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i> No credit notes found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($creditNotes->hasPages())
        <div class="card-footer bg-white py-3 border-top">
            {{ $creditNotes->links() }}
        </div>
    @endif
</div>
@endsection
