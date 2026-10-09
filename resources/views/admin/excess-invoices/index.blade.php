@extends('layouts.admin')

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
                            Monitor all excess stock purchase invoices generated across DDCs, ADCs, and Sellers.
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
            <ul class="nav nav-pills card-header-pills" id="adminExcessInvoicesTab" role="tablist">
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

            <form action="{{ route('admin.excess-invoices.index') }}" method="GET" class="d-flex align-items-center gap-2">
                <input type="text" name="search" class="form-control form-control-sm" placeholder="Search invoice or PO #" value="{{ $search }}">
                <button type="submit" class="btn btn-sm btn-primary text-nowrap"><i class="bi bi-filter"></i> Search</button>
            </form>
        </div>
    </div>

    <div class="card-body p-0">
        <div class="tab-content" id="adminExcessInvoicesTabContent">
            {{-- DDC PO EXCESS INVOICES TAB --}}
            <div class="tab-pane fade show active" id="ddc-ex" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light border-top border-bottom">
                            <tr>
                                <th class="ps-3">Invoice Number</th>
                                <th>PO Number</th>
                                <th>DDC</th>
                                <th>Seller</th>
                                <th>Invoice Date</th>
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
                                        <span class="small font-weight-bold">{{ $inv->purchaseOrder?->ddc?->name }}</span>
                                    </td>
                                    <td>
                                        <span class="small">{{ $inv->purchaseOrder?->seller?->company_name ?: $inv->purchaseOrder?->seller?->name }}</span>
                                    </td>
                                    <td>
                                        <span class="small text-muted">{{ $inv->invoice_date ? $inv->invoice_date->format('d M Y') : 'N/A' }}</span>
                                    </td>
                                    <td class="pe-3 text-end">
                                        <a href="{{ route('admin.excess-invoices.show', $inv->id) }}" class="btn btn-sm btn-outline-primary px-3 py-1">
                                            View Details
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
                                <th>Target (DDC / Seller)</th>
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
                                        @if($aex->target_type === 'DDC')
                                            <span class="badge bg-purple-100 text-purple-800 me-1">DDC</span>
                                            {{ $aex->ddc?->name ?? 'DDC #'.$aex->ddc_id }}
                                        @else
                                            <span class="badge bg-info-subtle text-info me-1">Seller</span>
                                            {{ $aex->seller?->name ?? 'Seller #'.$aex->seller_id }}
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-success-subtle text-success fs-6">+{{ $aex->total_excess_qty }}</span>
                                    </td>
                                    <td>
                                        <strong class="text-dark">₹{{ number_format($aex->total_excess_amount, 2) }}</strong>
                                    </td>
                                    <td>
                                        @if($aex->status === 'PAID')
                                            <span class="badge bg-success-subtle text-success px-2.5 py-1 fs-12">PAID</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning px-2.5 py-1 fs-12">PENDING</span>
                                        @endif
                                    </td>
                                    <td class="pe-3 text-end">
                                        <a href="{{ route('admin.adc-excess-invoices.show', $aex->id) }}" class="btn btn-sm btn-outline-primary px-3 py-1">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-4 text-muted small">No ADC excess stock invoices found.</td>
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
