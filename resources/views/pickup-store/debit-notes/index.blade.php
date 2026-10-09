@extends('layouts.pickup_store')

@section('title', 'Debit Notes')
@section('page-title', 'Debit Notes Management')

@section('content')
<div class="row mb-4">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">Debit Notes</h4>
                        <p class="text-muted small mb-0">View all discrepancy Debit Notes linked to purchase orders assigned to your Authorized Pickup Store.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4">
        <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-18 align-middle me-1"></iconify-icon>
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card shadow-sm border-0">
    <div class="card-header bg-white border-bottom py-3">
        <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $tab == 'ddc_po' ? 'active fw-bold' : '' }}" href="{{ route('store.debit-notes.index', ['tab' => 'ddc_po']) }}">
                    <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="me-1"></iconify-icon> DDC PO Claims
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $tab == 'adc_order' ? 'active fw-bold' : '' }}" href="{{ route('store.debit-notes.index', ['tab' => 'adc_order']) }}">
                    <iconify-icon icon="solar:shop-2-bold-duotone" class="me-1"></iconify-icon> ADC Order Claims
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
                            <th class="ps-3">Debit Note #</th>
                            <th>PO Number</th>
                            <th>DDC Name</th>
                            <th>Seller / Vendor</th>
                            <th>Total Amount</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($debitNotes as $dn)
                            <tr>
                                <td class="ps-3 fw-bold text-primary">{{ $dn->debit_note_number }}</td>
                                <td>{{ $dn->purchaseOrder->po_number ?? 'N/A' }}</td>
                                <td>{{ $dn->ddc->name ?? 'N/A' }}</td>
                                <td>{{ $dn->seller->company_name ?? $dn->seller->name ?? 'N/A' }}</td>
                                <td class="fw-bold">₹{{ number_format($dn->final_amount ?? $dn->total_discrepancy_amount, 2) }}</td>
                                <td>
                                    @if($dn->status == 'PENDING_SELLER_APPROVAL')
                                        <span class="badge bg-warning-subtle text-warning">Pending Approval</span>
                                    @elseif($dn->status == 'APPROVED')
                                        <span class="badge bg-success-subtle text-success">Approved</span>
                                    @elseif($dn->status == 'REJECTED')
                                        <span class="badge bg-danger-subtle text-danger">Rejected</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">{{ $dn->status }}</span>
                                    @endif
                                </td>
                                <td>{{ $dn->created_at->format('d M Y, h:i A') }}</td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('store.debit-notes.show', $dn->id) }}" class="btn btn-sm btn-outline-primary">
                                        <iconify-icon icon="solar:eye-bold-duotone" class="align-middle"></iconify-icon> View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No DDC PO Debit Notes found for your store.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($debitNotes->hasPages())
                <div class="p-3">
                    {{ $debitNotes->links() }}
                </div>
            @endif
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-slate-700">
                        <tr>
                            <th class="ps-3">Debit Note #</th>
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
                        @forelse($adcDebitNotes as $adn)
                            <tr>
                                <td class="ps-3 fw-bold text-primary">{{ $adn->debit_note_number }}</td>
                                <td>{{ $adn->adcOrder->order_number ?? 'N/A' }}</td>
                                <td>{{ $adn->adc->name ?? 'N/A' }}</td>
                                <td>
                                    @if($adn->target_type == 'SELLER')
                                        <span class="badge bg-info-subtle text-info">Seller</span> {{ $adn->seller->company_name ?? $adn->seller->name ?? '' }}
                                    @else
                                        <span class="badge bg-primary-subtle text-primary">DDC</span> {{ $adn->ddc->name ?? '' }}
                                    @endif
                                </td>
                                <td class="fw-bold">₹{{ number_format($adn->final_amount ?? $adn->total_discrepancy_amount, 2) }}</td>
                                <td>
                                    @if($adn->status == 'PENDING_SELLER_APPROVAL')
                                        <span class="badge bg-warning-subtle text-warning">Pending</span>
                                    @elseif($adn->status == 'APPROVED')
                                        <span class="badge bg-success-subtle text-success">Approved</span>
                                    @elseif($adn->status == 'REJECTED')
                                        <span class="badge bg-danger-subtle text-danger">Rejected</span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary">{{ $adn->status }}</span>
                                    @endif
                                </td>
                                <td>{{ $adn->created_at->format('d M Y, h:i A') }}</td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('store.adc-debit-notes.show', $adn->id) }}" class="btn btn-sm btn-outline-primary">
                                        <iconify-icon icon="solar:eye-bold-duotone" class="align-middle"></iconify-icon> View Details
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">No ADC Debit Notes found for your store orders.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($adcDebitNotes->hasPages())
                <div class="p-3">
                    {{ $adcDebitNotes->links() }}
                </div>
            @endif
        @endif
    </div>
</div>
@endsection
