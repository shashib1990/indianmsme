@extends('layouts.pickup_store')

@section('title', 'Excess Stock Invoices')
@section('page-title', 'Excess Invoices Management')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">Excess Stock Invoices</h4>
                        <p class="text-muted small mb-0">View all Excess Stock Invoices linked to purchase orders assigned to your Authorized Pickup Store.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom py-3">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $tab == 'ddc_po' ? 'active fw-bold' : '' }}" href="{{ route('store.excess-invoices.index', ['tab' => 'ddc_po']) }}">
                    <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="me-1"></iconify-icon> DDC PO Excess Invoices
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab == 'adc_order' ? 'active fw-bold' : '' }}" href="{{ route('store.excess-invoices.index', ['tab' => 'adc_order']) }}">
                    <iconify-icon icon="solar:shop-2-bold-duotone" class="me-1"></iconify-icon> ADC Order Excess Invoices
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body p-0">
        @if($tab == 'ddc_po')
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-slate-700">
                        <tr>
                            <th class="ps-3">Invoice #</th>
                            <th>PO Number</th>
                            <th>DDC Name</th>
                            <th>Seller / Vendor</th>
                            <th>Date</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($excessInvoices as $inv)
                            <tr>
                                <td class="ps-3 fw-bold text-primary">{{ $inv->invoice_number }}</td>
                                <td>{{ $inv->purchaseOrder->po_number ?? 'N/A' }}</td>
                                <td>{{ $inv->purchaseOrder->ddc->name ?? 'N/A' }}</td>
                                <td>{{ $inv->purchaseOrder->seller->company_name ?? $inv->purchaseOrder->seller->name ?? 'N/A' }}</td>
                                <td>{{ optional($inv->invoice_date)->format('d M Y') ?? $inv->created_at->format('d M Y') }}</td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('store.excess-invoices.show', $inv->id) }}" class="btn btn-sm btn-outline-primary">
                                        <iconify-icon icon="solar:eye-bold-duotone" class="align-middle"></iconify-icon> View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">No DDC PO Excess Invoices found for your store.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($excessInvoices->hasPages())
                <div class="p-3">{{ $excessInvoices->links() }}</div>
            @endif
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-slate-700">
                        <tr>
                            <th class="ps-3">Invoice #</th>
                            <th>Order #</th>
                            <th>ADC Store</th>
                            <th>Supplier (Target)</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($adcExcessInvoices as $ainv)
                            <tr>
                                <td class="ps-3 fw-bold text-primary">{{ $ainv->invoice_number }}</td>
                                <td>{{ $ainv->adcOrder->order_number ?? 'N/A' }}</td>
                                <td>{{ $ainv->adc->name ?? 'N/A' }}</td>
                                <td>
                                    @if($ainv->target_type == 'SELLER')
                                        <span class="badge bg-info-subtle text-info">Seller</span> {{ $ainv->seller->company_name ?? $ainv->seller->name ?? '' }}
                                    @else
                                        <span class="badge bg-primary-subtle text-primary">DDC</span> {{ $ainv->ddc->name ?? '' }}
                                    @endif
                                </td>
                                <td class="fw-bold">₹{{ number_format($ainv->total_amount, 2) }}</td>
                                <td>
                                    @if($ainv->status == 'PAID')
                                        <span class="badge bg-success-subtle text-success">Paid</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning">Pending Payment</span>
                                    @endif
                                </td>
                                <td>{{ $ainv->created_at->format('d M Y') }}</td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('store.adc-excess-invoices.show', $ainv->id) }}" class="btn btn-sm btn-outline-primary">
                                        <iconify-icon icon="solar:eye-bold-duotone" class="align-middle"></iconify-icon> View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No ADC Excess Invoices found for your store orders.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($adcExcessInvoices->hasPages())
                <div class="p-3">{{ $adcExcessInvoices->links() }}</div>
            @endif
        @endif
    </div>
</div>
@endsection
