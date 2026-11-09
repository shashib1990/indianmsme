@extends('layouts.admin')

@section('title', 'ADC Debit Note ' . $debitNote->debit_note_number)
@section('page-title', 'ADC Debit Note Audit')

@section('content')
<div class="row mb-3">
    <div class="col-12">
        <div class="d-flex align-items-center justify-content-between">
            <a href="{{ route('admin.debit-notes.index') }}" class="btn btn-sm btn-outline-secondary">
                <iconify-icon icon="solar:arrow-left-bold" class="align-middle me-1"></iconify-icon> Back to Debit Notes
            </a>
            <div>
                <span class="fs-13 text-muted me-2">Status:</span>
                @if(in_array($debitNote->status, ['PENDING', 'PENDING_ADMIN_APPROVAL']))
                    <span class="badge bg-warning text-dark fs-13 px-2.5 py-1">PENDING ADMIN APPROVAL</span>
                @elseif($debitNote->status === 'ADMIN_REJECTED')
                    <span class="badge bg-danger fs-13 px-2.5 py-1">REJECTED BY ADMIN</span>
                @elseif($debitNote->status === 'PENDING_SELLER_APPROVAL')
                    <span class="badge bg-info text-white fs-13 px-2.5 py-1">FORWARDED TO VENDOR</span>
                @elseif($debitNote->status === 'SELLER_APPROVED')
                    <span class="badge bg-primary text-white fs-13 px-2.5 py-1">APPROVED BY VENDOR</span>
                @elseif($debitNote->status === 'SELLER_REJECTED')
                    <span class="badge bg-danger fs-13 px-2.5 py-1">REJECTED BY VENDOR</span>
                @elseif($debitNote->status === 'SETTLED')
                    <span class="badge bg-success fs-13 px-2.5 py-1">SETTLED</span>
                @else
                    <span class="badge bg-secondary fs-13 px-2.5 py-1">{{ $debitNote->status }}</span>
                @endif
            </div>
        </div>
    </div>
</div>

<div class="row g-3">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-3">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="fw-bold text-slate-800 mb-0">ADC Debit Note #{{ $debitNote->debit_note_number }}</h5>
                <span class="text-muted small">{{ $debitNote->created_at->format('d M Y, h:i A') }}</span>
            </div>
            <div class="card-body">
                <div class="row g-3 mb-3">
                    <div class="col-6">
                        <span class="text-muted small d-block">Invoice No:</span>
                        <strong class="fw-bold text-dark font-monospace fs-14">{{ $debitNote->invoice_number ?: ('#' . ($debitNote->adcOrder?->order_number ?? 'N/A')) }}</strong>
                        @if($debitNote->invoice_number && $debitNote->adcOrder?->order_number)
                            <small class="d-block text-muted">Order: #{{ $debitNote->adcOrder->order_number }}</small>
                        @endif
                    </div>
                    <div class="col-6">
                        <span class="text-muted small d-block">Reporting ADC Store:</span>
                        <strong class="text-dark">{{ $debitNote->adc?->name ?? ('ADC #' . $debitNote->adc_id) }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted small d-block">Discrepancy Type:</span>
                        <strong class="text-primary">{{ $debitNote->discrepancy_type }}</strong>
                    </div>
                    <div class="col-6">
                        <span class="text-muted small d-block">Target Destination:</span>
                        @if($debitNote->target_type === 'DDC')
                            <span class="badge bg-primary-subtle text-primary me-1">DDC</span>
                            <strong class="text-dark">{{ $debitNote->ddc?->name ?? ('DDC #' . $debitNote->ddc_id) }}</strong>
                        @else
                            <span class="badge bg-info-subtle text-info me-1">Seller</span>
                            <strong class="text-dark">{{ $debitNote->seller?->company_name ?? $debitNote->seller?->name ?? ('Seller #' . $debitNote->seller_id) }}</strong>
                        @endif
                    </div>
                </div>

                @if($debitNote->adpl_remarks)
                    <div class="mb-3">
                        <span class="text-muted small d-block fw-semibold mb-1">ADC Discrepancy Remarks:</span>
                        <div class="bg-light p-2.5 rounded text-dark fs-13">{{ $debitNote->adpl_remarks }}</div>
                    </div>
                @endif

                <h6 class="fw-bold text-slate-800 mb-2">Itemized Discrepancy Breakdown</h6>
                <div class="table-responsive bg-light p-2 rounded mb-3">
                    <table class="table table-sm table-hover align-middle mb-0">
                        <thead>
                            <tr class="text-muted small border-bottom">
                                <th>Product Name</th>
                                <th class="text-center">Order Qty</th>
                                <th class="text-center">Received</th>
                                <th class="text-center text-danger">Shortage</th>
                                <th class="text-center text-warning">Damage</th>
                                <th class="text-end">Unit Price</th>
                                <th class="text-center">Approved Qty</th>
                                <th class="text-end pe-2">Discrepancy Amount</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($debitNote->items as $item)
                                @php
                                    $claimedQty = $item->shortage_qty + $item->damage_qty;
                                    $receivedQty = $item->received_qty ?? max(0, (int)$item->order_qty - (int)($item->shortage_qty ?? 0));
                                @endphp
                                <tr>
                                    <td>
                                        <span class="fw-semibold text-slate-800 d-block">{{ $item->product_name }}</span>
                                        @if($item->adcOrderItem?->product)
                                            <span class="text-muted small font-monospace">PROD-{{ $item->adcOrderItem->product->product_code ?? $item->adcOrderItem->product_id }}</span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-medium">{{ $item->order_qty }}</td>
                                    <td class="text-center text-success fw-medium">{{ $receivedQty }}</td>
                                    <td class="text-center">
                                        @if($item->shortage_qty > 0)
                                            <span class="badge bg-danger-subtle text-danger px-1.5 py-0.5 fs-11">{{ $item->shortage_qty }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($item->damage_qty > 0)
                                            <span class="badge bg-warning-subtle text-warning px-1.5 py-0.5 fs-11">{{ $item->damage_qty }}</span>
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-semibold text-dark">₹{{ number_format($item->unit_price, 2) }}</td>
                                    <td class="text-center">
                                        @if($debitNote->status === 'SETTLED')
                                            <span class="fw-bold text-success">{{ $item->seller_agreed_qty ?? $claimedQty }}</span>
                                        @else
                                            <span class="text-muted font-monospace">Pending</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-2 fw-bold text-slate-800">
                                        ₹{{ number_format($item->seller_agreed_amount ?? $item->item_discrepancy_amount, 2) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-3 text-muted">No line items found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex align-items-center justify-content-between p-3 bg-primary-subtle rounded text-primary mb-3">
                    <span class="fw-semibold">Total Discrepancy Amount:</span>
                    <h4 class="fw-bold mb-0">₹{{ number_format($debitNote->final_amount ?? $debitNote->total_discrepancy_amount, 2) }}</h4>
                </div>

                <!-- Discrepancy Proof Documents Uploaded by ADC -->
                <h6 class="fw-bold text-slate-800 mb-2">Discrepancy Proof Documents ({{ count($debitNote->proof_images ?: []) }}/5)</h6>
                @if(!empty($debitNote->proof_images) && is_array($debitNote->proof_images))
                    <div class="d-flex flex-wrap gap-2 mb-3">
                        @foreach($debitNote->proof_images as $imgUrl)
                            <a href="{{ $imgUrl }}" target="_blank" class="border rounded p-1 d-inline-block bg-white shadow-sm">
                                <img src="{{ $imgUrl }}" alt="Proof" style="width: 90px; height: 90px; object-fit: cover;" class="rounded">
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted small">No proof documents attached.</p>
                @endif
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0">Audit & Verification Action</h6>
            </div>
            <div class="card-body">
                {{-- Status Alert Box --}}
                @if(in_array($debitNote->status, ['PENDING', 'PENDING_ADMIN_APPROVAL']))
                    <div class="alert alert-warning mb-3">
                        <strong class="d-block mb-1"><i class="bx bx-time-five me-1"></i> Awaiting Admin Verification</strong>
                        <p class="small mb-0">This Debit Note was generated by ADC and is currently on hold. Vendor cannot view or settle it until Admin approves.</p>
                    </div>
                @elseif($debitNote->status === 'ADMIN_REJECTED')
                    <div class="alert alert-danger mb-3">
                        <strong class="d-block mb-1"><i class="bx bx-x-circle me-1"></i> Rejected by Admin</strong>
                        <p class="small mb-0">This Debit Note was rejected by Admin. It has not been sent to the Vendor.</p>
                        @if($debitNote->adpl_remarks)
                            <p class="small mb-0 mt-1"><strong>Admin Remarks:</strong> {{ $debitNote->adpl_remarks }}</p>
                        @endif
                    </div>
                @elseif($debitNote->status === 'PENDING_SELLER_APPROVAL')
                    <div class="alert alert-info mb-3">
                        <strong class="d-block mb-1"><i class="bx bx-paper-plane me-1"></i> Forwarded to Vendor</strong>
                        <p class="small mb-0">Admin approved. Waiting for Vendor/Seller verification and claim approval.</p>
                        @if($debitNote->adpl_remarks)
                            <p class="small mb-0 mt-1"><strong>Admin Remarks:</strong> {{ $debitNote->adpl_remarks }}</p>
                        @endif
                    </div>
                @elseif($debitNote->status === 'SELLER_APPROVED')
                    <div class="alert alert-primary mb-3">
                        <strong class="d-block mb-1"><i class="bx bx-check-double me-1"></i> Approved by Vendor</strong>
                        <p class="small mb-0">Vendor has approved the discrepancy claim.</p>
                    </div>
                @elseif($debitNote->status === 'SELLER_REJECTED')
                    <div class="alert alert-danger mb-3">
                        <strong class="d-block mb-1"><i class="bx bx-x-circle me-1"></i> Rejected by Vendor</strong>
                        @if($debitNote->seller_remarks)
                            <p class="small mb-0 mt-1"><strong>Vendor Remarks:</strong> {{ $debitNote->seller_remarks }}</p>
                        @endif
                    </div>
                @elseif($debitNote->status === 'SETTLED')
                    <div class="alert alert-success mb-3">
                        <strong class="d-block mb-1"><i class="bx bx-badge-check me-1"></i> Settled</strong>
                        <p class="small mb-0">Debit note claim has been settled and credited.</p>
                    </div>
                @else
                    <div class="alert alert-secondary mb-3">
                        <strong class="d-block mb-1">Status: {{ $debitNote->status }}</strong>
                    </div>
                @endif

                {{-- Action Form for Admin: Allowed ONLY when PENDING or PENDING_ADMIN_APPROVAL --}}
                @if(in_array($debitNote->status, ['PENDING', 'PENDING_ADMIN_APPROVAL']))
                    <form action="{{ route('admin.adc-debit-notes.verify', $debitNote->id) }}" method="POST" class="mb-3" id="adminApproveForm">
                        @csrf
                        <input type="hidden" name="action" value="APPROVE">
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Admin Remarks (Optional)</label>
                            <textarea name="remarks" id="pageAdminRemarks" rows="3" class="form-control form-control-sm" placeholder="Add remarks or instructions...">{{ old('remarks', $debitNote->adpl_remarks) }}</textarea>
                        </div>
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success fw-bold py-2">
                                <i class="bx bx-check-circle me-1"></i> Approve & Send to Vendor
                            </button>
                            <button type="button" class="btn btn-outline-danger fw-semibold" data-bs-toggle="modal" data-bs-target="#rejectDebitNoteModal">
                                <i class="bx bx-x-circle me-1"></i> Reject Debit Note
                            </button>
                        </div>
                    </form>
                @endif

                <div class="border-top pt-3">
                    <div class="d-flex justify-content-between mb-1 small">
                        <span class="text-muted">Target Vendor:</span>
                        <span class="fw-semibold text-dark">{{ $debitNote->seller?->company_name ?? $debitNote->seller?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="d-flex justify-content-between mb-1 small">
                        <span class="text-muted">Reporting ADC:</span>
                        <span class="fw-semibold text-dark">{{ $debitNote->adc?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="d-flex justify-content-between small">
                        <span class="text-muted">Created On:</span>
                        <span class="text-dark">{{ $debitNote->created_at->format('d M Y, h:i A') }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if(in_array($debitNote->status, ['PENDING', 'PENDING_ADMIN_APPROVAL']))
{{-- Reject Confirmation Modal --}}
<div class="modal fade" id="rejectDebitNoteModal" tabindex="-1" aria-labelledby="rejectDebitNoteModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form action="{{ route('admin.adc-debit-notes.verify', $debitNote->id) }}" method="POST">
            @csrf
            <input type="hidden" name="action" value="REJECT">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger-subtle border-bottom border-danger-subtle py-3">
                    <h5 class="modal-title fw-bold text-danger d-flex align-items-center mb-0" id="rejectDebitNoteModalLabel">
                        <i class="bx bx-error-circle fs-4 me-2"></i> Reject ADC Debit Note
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-4">
                    <div class="alert alert-warning border-0 bg-warning-subtle text-dark py-2 px-3 small mb-3">
                        <i class="bx bx-error me-1 text-warning fs-16 align-middle"></i>
                        Are you sure you want to reject Debit Note <strong>#{{ $debitNote->debit_note_number }}</strong>? Once rejected, it will <strong>NOT</strong> be sent to the vendor.
                    </div>
                    <div class="mb-2">
                        <label class="form-label fw-semibold text-slate-700 small">Rejection Reason / Admin Remarks (Optional)</label>
                        <textarea name="remarks" id="modalRejectionRemarks" rows="3" class="form-control" placeholder="Provide reason for rejecting this debit note...">{{ old('remarks', $debitNote->adpl_remarks) }}</textarea>
                        <span class="text-muted small">This remark will be sent to the reporting ADC store.</span>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 border-top">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger fw-semibold px-4">
                        <i class="bx bx-x-circle me-1"></i> Confirm & Reject
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endif
@endsection

@push('scripts')
@if(in_array($debitNote->status, ['PENDING', 'PENDING_ADMIN_APPROVAL']))
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const rejectModal = document.getElementById('rejectDebitNoteModal');
        const pageRemarks = document.getElementById('pageAdminRemarks');
        const modalRemarks = document.getElementById('modalRejectionRemarks');

        if (rejectModal && pageRemarks && modalRemarks) {
            rejectModal.addEventListener('show.bs.modal', function () {
                if (pageRemarks.value.trim() !== '') {
                    modalRemarks.value = pageRemarks.value.trim();
                }
            });
        }
    });
</script>
@endif
@endpush
