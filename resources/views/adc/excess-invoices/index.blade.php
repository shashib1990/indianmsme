@extends('layouts.adc')

@section('title', 'Excess Stock Invoices')
@section('page-title', 'Excess Stock Invoices')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                            ADC Excess Stock Invoices & Payments
                        </h4>
                        <p class="text-muted small mb-0">
                            Pay for additional/excess items received during stock-in. Payments route to Physical DDC or Seller wallet.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="bi bi-check-circle me-1"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle me-1"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom">
        <form method="GET" action="{{ route('adc.excess-invoices.index') }}" class="row g-2 align-items-center">
            <div class="col-md-6">
                <input type="text" name="search" value="{{ request('search') }}" class="form-control form-control-sm" placeholder="Search Invoice #, Order #, DDC/Seller...">
            </div>
            <div class="col-md-4">
                <select name="status" class="form-select form-select-sm">
                    <option value="">All Statuses</option>
                    <option value="PENDING" {{ request('status') === 'PENDING' ? 'selected' : '' }}>PENDING PAYMENT</option>
                    <option value="PAID" {{ request('status') === 'PAID' ? 'selected' : '' }}>PAID VIA WALLET</option>
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
                        <th class="ps-3">Invoice Number</th>
                        <th>ADC Order #</th>
                        <th>Target (DDC / Seller)</th>
                        <th>Excess Qty</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th class="pe-3 text-end">Action / Payment</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($invoices as $inv)
                        <tr>
                            <td class="ps-3">
                                <strong class="text-primary font-monospace">{{ $inv->invoice_number }}</strong>
                                <span class="d-block text-muted small">{{ $inv->created_at->format('d M Y, h:i A') }}</span>
                            </td>
                            <td>
                                @if($inv->order)
                                    <a href="{{ route('adc.orders.show', $inv->adc_order_id) }}" class="fw-semibold text-decoration-none">
                                        #{{ $inv->order->order_number }}
                                    </a>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($inv->target_type === 'DDC')
                                    <span class="badge bg-purple-100 text-purple-800 me-1">DDC</span>
                                    <span class="fw-medium">{{ $inv->ddc?->name ?? 'DDC #'.$inv->ddc_id }}</span>
                                @else
                                    <span class="badge bg-info-subtle text-info me-1">Seller</span>
                                    <span class="fw-medium">{{ $inv->seller?->company_name ?? $inv->seller?->name ?? 'Seller #'.$inv->seller_id }}</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-success-subtle text-success fs-6">+{{ $inv->total_excess_qty }}</span>
                            </td>
                            <td>
                                <strong class="text-dark">₹{{ number_format($inv->total_excess_amount, 2) }}</strong>
                            </td>
                            <td>
                                @if($inv->status === 'PAID')
                                    <span class="badge bg-success-subtle text-success px-2.5 py-1 fs-12"><i class="bi bi-check-circle me-1"></i> PAID BY ADC</span>
                                @else
                                    <span class="badge bg-warning-subtle text-warning px-2.5 py-1 fs-12"><i class="bi bi-clock me-1"></i> PENDING PAYMENT</span>
                                @endif
                            </td>
                            <td class="pe-3 text-end">
                                <a href="{{ route('adc.excess-invoices.show', $inv->id) }}" class="btn btn-sm btn-outline-primary px-3 py-1 me-1">
                                    Details
                                </a>
                                @if($inv->status !== 'PAID')
                                    <form method="POST" action="{{ route('adc.excess-invoices.pay', $inv->id) }}" class="d-inline" onsubmit="return confirm('Pay ₹{{ number_format($inv->total_excess_amount, 2) }} for this excess stock invoice using your ADC Wallet?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-success px-3 py-1">
                                            <i class="bi bi-wallet2 me-1"></i> Pay via Wallet
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">
                                <i class="bi bi-inbox fs-3 d-block mb-2"></i> No excess stock invoices found.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($invoices->hasPages())
        <div class="card-footer bg-white py-3 border-top">
            {{ $invoices->links() }}
        </div>
    @endif
</div>
@endsection
