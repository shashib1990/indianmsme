@extends('layouts.vendor')

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
                            Excess Stock Invoices
                        </h4>
                        <p class="text-muted small mb-0">
                            View all excess stock invoices issued by DDCs and ADCs at seller prices and credited to your wallet.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3 border-bottom">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
            <ul class="nav nav-pills card-header-pills" id="excessInvoicesTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-bold" id="ddc-ex-tab" data-bs-toggle="tab" data-bs-target="#ddc-ex" type="button" role="tab">
                        <i class="bi bi-building me-1"></i> DDC Excess Invoices ({{ $invoices->total() }})
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-bold" id="adc-ex-tab" data-bs-toggle="tab" data-bs-target="#adc-ex" type="button" role="tab">
                        <i class="bi bi-shop me-1"></i> ADC Excess Stock Invoices ({{ $adcExcessInvoices->count() }})
                    </button>
                </li>
            </ul>

            <form action="{{ route('vendor.excess-invoices.index') }}" method="GET" class="d-flex align-items-center gap-2">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search invoice or PO #" value="{{ $search }}">
                <button type="submit" class="btn btn-sm btn-primary text-nowrap"><i class="bi bi-filter"></i> Search</button>
            </form>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="tab-content" id="excessInvoicesTabContent">
            {{-- DDC EXCESS INVOICES TAB --}}
            <div class="tab-pane fade show active" id="ddc-ex" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light border-top border-bottom">
                            <tr>
                                <th class="ps-3">Invoice Number</th>
                                <th>PO Number</th>
                                <th>Billed To</th>
                                <th>Invoice Date</th>
                                <th>Status</th>
                                <th class="pe-3 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($invoices as $inv)
                                <tr>
                                    <td class="ps-3">
                                        <span class="fw-bold text-primary font-monospace">{{ $inv->invoice_number }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-medium text-slate-800">#{{ $inv->purchaseOrder?->po_number }}</span>
                                    </td>
                                    <td>
                                        <span class="small font-weight-bold">{{ $inv->bill_to_name }}</span>
                                    </td>
                                    <td>
                                        <span class="small text-muted">{{ $inv->invoice_date ? $inv->invoice_date->format('d M Y') : 'N/A' }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success px-2.5 py-1 fs-12">Credited to Wallet</span>
                                    </td>
                                    <td class="pe-3 text-end">
                                        <a href="{{ route('vendor.excess-invoices.show', $inv->id) }}" class="btn btn-sm btn-outline-primary px-3 py-1">
                                            View Tax Invoice
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted small">No DDC excess stock invoices found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($invoices->hasPages())
                    <div class="card-footer bg-white border-0 py-3">
                        {{ $invoices->links() }}
                    </div>
                @endif
            </div>

            {{-- ADC EXCESS INVOICES TAB --}}
            <div class="tab-pane fade" id="adc-ex" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light border-top border-bottom">
                            <tr>
                                <th class="ps-3">Invoice Number</th>
                                <th>ADC Order #</th>
                                <th>ADC Store</th>
                                <th>Excess Qty</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th class="pe-3 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($adcExcessInvoices as $aex)
                                <tr>
                                    <td class="ps-3">
                                        <span class="fw-bold text-primary font-monospace">{{ $aex->invoice_number }}</span>
                                        <div class="small text-muted">{{ $aex->created_at->format('d M Y') }}</div>
                                    </td>
                                    <td>
                                        <span class="fw-medium text-slate-800">#{{ $aex->order?->order_number }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-medium text-dark">{{ $aex->adc?->name }}</span>
                                    </td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success fs-6">+{{ $aex->total_excess_qty }}</span>
                                    </td>
                                    <td>
                                        <strong class="text-dark">₹{{ number_format($aex->total_excess_amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        @if($aex->status === 'PAID')
                                            <span class="badge bg-success-subtle text-success px-2.5 py-1 fs-12">PAID VIA WALLET</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning px-2.5 py-1 fs-12">PENDING PAYMENT</span>
                                        @endif
                                    </td>
                                    <td class="pe-3 text-end">
                                        <a href="{{ route('vendor.adc-excess-invoices.show', $aex->id) }}" class="btn btn-sm btn-outline-primary px-3 py-1">
                                            View Invoice
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" class="text-center py-4 text-muted small">No ADC excess stock invoices found.</td>
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
