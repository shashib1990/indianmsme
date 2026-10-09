@extends('layouts.admin')

@section('title', 'AMA Incentive Transactions')
@section('page-title', 'AMA Transactions')

@section('content')
    <!-- Common Page Action Header -->
    <x-admin.page-header title="AMA Incentive Transactions"
        subtitle="Monitor Advisor incentives (SSI & FSI) including holds, payouts, cancellations, and released commissions." />

    <!-- Search & Filters -->
    <div class="card border-0 shadow-sm mb-3">
        <div class="card-body">
            <form action="{{ route('admin.ama-transactions.index') }}" method="GET" class="row g-3" id="filterForm">
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-slate-700">AMA Code</label>
                    <input type="text" name="ama_code" class="form-control form-control-sm" placeholder="Search by Advisor Code..." value="{{ request('ama_code') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-slate-700">PO Number</label>
                    <input type="text" name="po_number" class="form-control form-control-sm" placeholder="Search by PO Number..." value="{{ request('po_number') }}">
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-slate-700">Incentive Type</label>
                    <select name="type" class="form-select form-select-sm">
                        <option value="">All Types</option>
                        <option value="SSI" {{ request('type') === 'SSI' ? 'selected' : '' }}>SSI (Smart Sales)</option>
                        <option value="FSI" {{ request('type') === 'FSI' ? 'selected' : '' }}>FSI (Field Sales)</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label small fw-semibold text-slate-700">Status</label>
                    <select name="status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="Hold" {{ request('status') === 'Hold' ? 'selected' : '' }}>Hold</option>
                        <option value="Partial_Released" {{ request('status') === 'Partial_Released' ? 'selected' : '' }}>Partial Released</option>
                        <option value="Fully_Released" {{ request('status') === 'Fully_Released' ? 'selected' : '' }}>Fully Released</option>
                        <option value="Released" {{ request('status') === 'Released' ? 'selected' : '' }}>Released</option>
                        <option value="Paid" {{ request('status') === 'Paid' ? 'selected' : '' }}>Paid</option>
                        <option value="Cancelled" {{ request('status') === 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100 py-1">Filter</button>
                    <a href="{{ route('admin.ama-transactions.index') }}" class="btn btn-sm btn-light py-1" title="Reset Filters">
                        <iconify-icon icon="solar:restart-bold" class="align-middle"></iconify-icon>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Transactions Table -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0 align-middle">
                             <thead class="bg-light bg-opacity-50 border-top border-bottom">
                                <tr>
                                    <th class="ps-3">Transaction ID</th>
                                    <th>Date</th>
                                    <th>PO Number</th>
                                    <th>Advisor Code</th>
                                    <th>Incentive Type</th>
                                    <th>Qty Details</th>
                                    <th class="text-end">Hold Amount</th>
                                    <th class="text-end">Released Amount</th>
                                    <th class="text-end">Balance Amount</th>
                                    <th class="text-center">Status</th>
                                    <th class="pe-3">Notes</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $tx)
                                    <tr>
                                        <td class="ps-3 fw-bold text-slate-800">#{{ $tx->id }}</td>
                                        <td class="text-muted small">{{ $tx->created_at->format('d M Y, h:i A') }}</td>
                                        <td>
                                            @if($tx->purchaseOrder)
                                                <a href="{{ route('admin.purchase-orders.show', $tx->purchase_order_id) }}" class="text-primary fw-semibold">
                                                    #{{ $tx->purchaseOrder->po_number }}
                                                </a>
                                            @else
                                                <span class="text-muted small">N/A</span>
                                            @endif
                                        </td>
                                        <td class="fw-semibold text-slate-700">
                                            @if($tx->releases->isNotEmpty())
                                                {{ $tx->releases->pluck('ama_code')->unique()->implode(', ') }}
                                            @else
                                                {{ $tx->ama_code ?? '—' }}
                                            @endif
                                        </td>
                                        <td>
                                            @if($tx->type === 'SSI')
                                                <span class="badge bg-primary-subtle text-primary px-2 py-0.5" title="Percentage: {{ number_format($tx->percentage, 2) }}%">SSI</span>
                                            @else
                                                <span class="badge bg-info-subtle text-info px-2 py-0.5" title="Percentage: {{ number_format($tx->percentage, 2) }}%">FSI</span>
                                            @endif
                                        </td>
                                        <td class="small">
                                            <div class="text-slate-850">Orig: <strong>{{ $tx->quantity }}</strong></div>
                                            <div class="text-success">Rel: <strong>{{ $tx->released_quantity }}</strong></div>
                                            <div class="text-warning">Rem: <strong>{{ $tx->remaining_quantity }}</strong></div>
                                        </td>
                                        <td class="text-end fw-bold text-slate-800">₹{{ number_format($tx->amount, 2) }}</td>
                                        <td class="text-end fw-bold text-success">₹{{ number_format($tx->released_amount, 2) }}</td>
                                        <td class="text-end fw-bold text-warning">₹{{ number_format($tx->balance_amount, 2) }}</td>
                                        <td class="text-center">
                                            @if($tx->status === 'Hold')
                                                <span class="badge bg-warning text-dark px-2 py-1 fs-11">Hold</span>
                                            @elseif($tx->status === 'Partial_Released')
                                                <span class="badge bg-info text-white px-2 py-1 fs-11">Partial Released</span>
                                            @elseif($tx->status === 'Fully_Released')
                                                <span class="badge bg-success text-white px-2 py-1 fs-11">Fully Released</span>
                                            @elseif($tx->status === 'Released')
                                                <span class="badge bg-success-subtle text-success px-2 py-1 fs-11">Released</span>
                                            @elseif($tx->status === 'Paid')
                                                <span class="badge bg-success text-white px-2 py-1 fs-11">Paid</span>
                                            @elseif($tx->status === 'Cancelled')
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-11">Cancelled</span>
                                            @else
                                                <span class="badge bg-light text-muted px-2 py-1 fs-11">{{ $tx->status }}</span>
                                            @endif
                                        </td>
                                        <td class="pe-3 text-muted small" style="max-width: 250px; white-space: normal;">
                                            {{ $tx->notes ?? '—' }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="13" class="text-center py-4 text-muted small">No transactions found matching filters.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($transactions->hasPages())
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                            <small class="text-muted">Showing {{ $transactions->firstItem() }}–{{ $transactions->lastItem() }} of {{ $transactions->total() }}</small>
                            {{ $transactions->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
