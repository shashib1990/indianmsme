@extends('layouts.admin')

@section('title', 'All Invoices Management')

@section('content')
<div class="container-fluid px-4 py-4">
    <!-- Header Page Title -->
    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-4 gap-3">
        <div>
            <h3 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                <iconify-icon icon="solar:bill-list-bold-duotone" class="text-primary fs-2"></iconify-icon>
                All Invoices Management
            </h3>
            <p class="text-muted mb-0 fs-14">View all invoices across MSME POs, ADC Orders, POS Sales, and Local Purchases. Edit Invoice Numbers directly.</p>
        </div>
    </div>

    <!-- Filter & Search Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-3 p-md-4">
            <form action="{{ route('admin.invoices.index') }}" method="GET" class="row g-3 align-items-end">
                <div class="col-12 col-md-4">
                    <label class="form-label fw-semibold text-secondary fs-13 mb-1">Search</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted">
                            <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                        </span>
                        <input type="text" name="search" class="form-control bg-light border-start-0 fs-14" 
                            placeholder="Invoice No, Order No, Customer, DDC/ADC..." value="{{ $search }}">
                    </div>
                </div>

                <div class="col-12 col-sm-6 col-md-3">
                    <label class="form-label fw-semibold text-secondary fs-13 mb-1">Order Channel / Type</label>
                    <select name="channel" class="form-select bg-light fs-14">
                        <option value="">All Order Channels</option>
                        <option value="msme_po" {{ $channel === 'msme_po' ? 'selected' : '' }}>MSME PO (DDC Purchase)</option>
                        <option value="adc_order" {{ $channel === 'adc_order' ? 'selected' : '' }}>ADC MSME Order</option>
                        <option value="retail" {{ $channel === 'retail' ? 'selected' : '' }}>POS Retail Sales</option>
                        <option value="wholesale" {{ $channel === 'wholesale' ? 'selected' : '' }}>POS Wholesale Sales</option>
                        <option value="local_purchase" {{ $channel === 'local_purchase' ? 'selected' : '' }}>Local Purchases</option>
                        <option value="general" {{ $channel === 'general' ? 'selected' : '' }}>General Invoices</option>
                    </select>
                </div>

                <div class="col-6 col-sm-3 col-md-2">
                    <label class="form-label fw-semibold text-secondary fs-13 mb-1">From Date</label>
                    <input type="date" name="date_from" class="form-control bg-light fs-14" value="{{ $dateFrom }}">
                </div>

                <div class="col-6 col-sm-3 col-md-2">
                    <label class="form-label fw-semibold text-secondary fs-13 mb-1">To Date</label>
                    <input type="date" name="date_to" class="form-control bg-light fs-14" value="{{ $dateTo }}">
                </div>

                <div class="col-12 col-md-1 d-flex gap-2">
                    <button type="submit" class="btn btn-primary w-100 fs-14 d-flex align-items-center justify-content-center gap-1">
                        <iconify-icon icon="solar:filter-bold-duotone"></iconify-icon> Filter
                    </button>
                    @if($search || $channel || $dateFrom || $dateTo)
                        <a href="{{ route('admin.invoices.index') }}" class="btn btn-outline-secondary fs-14" title="Reset Filters">
                            <iconify-icon icon="solar:restart-bold-duotone"></iconify-icon>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Invoices List Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
            <h5 class="card-title fw-bold mb-0 fs-16 text-dark">
                Invoices List <span class="badge bg-primary-subtle text-primary rounded-pill ms-2 fs-12">{{ $invoices->total() }} Records</span>
            </h5>
            <span class="text-muted fs-13"><i class="text-info">*</i> Invoice numbers can be updated directly using the edit button.</span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-nowrap">
                    <thead class="bg-light text-secondary fs-13 text-uppercase">
                        <tr>
                            <th class="ps-4">Invoice Number (Editable)</th>
                            <th>Order No / Ref</th>
                            <th>Channel / Order Type</th>
                            <th>From ➔ To (Order Route)</th>
                            <th class="text-end">Total Amount</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th class="pe-4 text-center">Action</th>
                        </tr>
                    </thead>
                    <tbody class="fs-14 text-dark">
                        @forelse($invoices as $inv)
                            <tr id="row-{{ $inv['table_type'] }}-{{ $inv['record_id'] }}">
                                <!-- Invoice Number Column -->
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="fw-bold text-primary display-inv-no" id="inv-text-{{ $inv['table_type'] }}-{{ $inv['record_id'] }}">
                                            {{ $inv['invoice_number'] ?: '— (No Invoice No)' }}
                                        </span>
                                        <button type="button" class="btn btn-sm btn-light border text-secondary px-2 py-1 btn-edit-inv"
                                            data-table="{{ $inv['table_type'] }}"
                                            data-id="{{ $inv['record_id'] }}"
                                            data-number="{{ $inv['invoice_number'] }}"
                                            title="Edit Invoice Number">
                                            <iconify-icon icon="solar:pen-bold-duotone" class="fs-14"></iconify-icon>
                                        </button>
                                    </div>
                                </td>

                                <!-- Order Number -->
                                <td>
                                    <span class="fw-semibold text-dark">{{ $inv['order_number'] ?: '—' }}</span>
                                </td>

                                <!-- Channel Badge -->
                                <td>
                                    @php
                                        $channelClass = match($inv['channel_type']) {
                                            'MSME PO'            => 'bg-info-subtle text-info border border-info-subtle',
                                            'ADC MSME Order'     => 'bg-primary-subtle text-primary border border-primary-subtle',
                                            'DDC POS Retail'     => 'bg-success-subtle text-success border border-success-subtle',
                                            'DDC POS Wholesale'  => 'bg-warning-subtle text-warning border border-warning-subtle',
                                            'ADC POS Retail'     => 'bg-teal-subtle text-teal border border-teal-subtle',
                                            'ADC POS Wholesale'  => 'bg-purple-subtle text-purple border border-purple-subtle',
                                            'Online Order'       => 'bg-danger-subtle text-danger border border-danger-subtle',
                                            'DDC Local Purchase' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                            'ADC Local Purchase' => 'bg-secondary-subtle text-secondary border border-secondary-subtle',
                                            default              => 'bg-dark-subtle text-dark border border-dark-subtle',
                                        };
                                    @endphp
                                    <span class="badge rounded-pill {{ $channelClass }} px-3 py-2 fs-12 fw-semibold">
                                        {{ $inv['channel_type'] }}
                                    </span>
                                </td>

                                <!-- From -> To (Order Route) -->
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-light text-dark border px-2 py-1 fw-medium">{{ $inv['source_name'] ?: 'N/A' }}</span>
                                        <iconify-icon icon="solar:arrow-right-line-duotone" class="text-primary fs-16"></iconify-icon>
                                        <span class="badge bg-light text-dark border px-2 py-1 fw-medium">{{ $inv['destination_name'] ?: 'N/A' }}</span>
                                    </div>
                                </td>

                                <!-- Total Amount -->
                                <td class="text-end">
                                    <span class="fw-bold text-dark fs-15">₹{{ number_format((float)($inv['total_amount'] ?? 0), 2) }}</span>
                                </td>

                                <!-- Date -->
                                <td class="text-muted fs-13">
                                    {{ $inv['invoice_date'] ? date('d M Y, h:i A', strtotime($inv['invoice_date'])) : '—' }}
                                </td>

                                <!-- Status -->
                                <td>
                                    @php
                                        $st = strtolower($inv['status'] ?? 'pending');
                                        $stClass = match($st) {
                                            'approved', 'completed', 'delivered', 'paid' => 'bg-success text-white',
                                            'cancelled', 'rejected', 'failed'           => 'bg-danger text-white',
                                            'dispatched', 'shipped'                     => 'bg-info text-white',
                                            default                                     => 'bg-warning text-dark',
                                        };
                                    @endphp
                                    <span class="badge {{ $stClass }} px-2 py-1 fs-11 text-uppercase fw-bold">
                                        {{ ucfirst($inv['status'] ?? 'Pending') }}
                                    </span>
                                </td>

                                <!-- Action -->
                                <td class="pe-4 text-center">
                                    <button type="button" class="btn btn-sm btn-outline-primary px-3 py-1 btn-edit-inv fs-13"
                                        data-table="{{ $inv['table_type'] }}"
                                        data-id="{{ $inv['record_id'] }}"
                                        data-number="{{ $inv['invoice_number'] }}">
                                        <iconify-icon icon="solar:pen-new-square-bold-duotone" class="me-1"></iconify-icon> Edit Invoice No
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="d-flex flex-column align-items-center justify-content-center text-center py-4">
                                        <div class="rounded-circle bg-light d-flex align-items-center justify-content-center mb-3 shadow-sm mx-auto" style="width: 84px; height: 84px;">
                                            <iconify-icon icon="solar:file-remove-bold-duotone" class="text-secondary" style="font-size: 42px;"></iconify-icon>
                                        </div>
                                        <h5 class="fw-bold text-dark mb-1">No Invoices Found</h5>
                                        <p class="text-muted fs-14 mb-3 mx-auto" style="max-width: 420px;">No invoice or order records were found matching your search query or channel filter.</p>
                                        @if($search || $channel || $dateFrom || $dateTo)
                                            <a href="{{ route('admin.invoices.index') }}" class="btn btn-sm btn-outline-primary px-3 rounded-pill">
                                                <iconify-icon icon="solar:restart-bold-duotone" class="me-1"></iconify-icon> Reset Search Filters
                                            </a>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($invoices->hasPages())
            <div class="card-footer bg-white border-top py-3">
                <div class="d-flex justify-content-end">
                    {{ $invoices->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Modal: Update Invoice Number Only -->
<div class="modal fade" id="updateInvoiceModal" tabindex="-1" aria-labelledby="updateInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-3">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold fs-16 d-flex align-items-center gap-2" id="updateInvoiceModalLabel">
                    <iconify-icon icon="solar:pen-bold-duotone" class="fs-18"></iconify-icon> Edit Invoice Number
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="updateInvoiceForm">
                @csrf
                <input type="hidden" id="modalTableType" name="table_type">
                <input type="hidden" id="modalRecordId" name="record_id">

                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 shadow-sm fs-13 mb-3 d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:info-circle-bold" class="fs-18 flex-shrink-0"></iconify-icon>
                        <div>Note: Only the Invoice Number can be edited. All other order parameters remain locked.</div>
                    </div>

                    <div class="mb-3">
                        <label for="modalInvoiceNumber" class="form-label fw-bold text-dark fs-14">Invoice Number <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg fs-14 fw-semibold" id="modalInvoiceNumber" name="invoice_number" required placeholder="e.g. INV-2026-00123">
                    </div>
                </div>

                <div class="modal-footer bg-light py-3">
                    <button type="button" class="btn btn-light border px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4 d-flex align-items-center gap-1" id="btnSubmitModal">
                        <span class="spinner-border spinner-border-sm d-none" id="spinnerSubmit"></span>
                        <iconify-icon icon="solar:check-read-bold-duotone"></iconify-icon> Update Invoice Number
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const editButtons = document.querySelectorAll('.btn-edit-inv');
    const updateModal = new bootstrap.Modal(document.getElementById('updateInvoiceModal'));
    const modalTableType = document.getElementById('modalTableType');
    const modalRecordId = document.getElementById('modalRecordId');
    const modalInvoiceNumber = document.getElementById('modalInvoiceNumber');
    const updateForm = document.getElementById('updateInvoiceForm');
    const btnSubmitModal = document.getElementById('btnSubmitModal');
    const spinnerSubmit = document.getElementById('spinnerSubmit');

    editButtons.forEach(btn => {
        btn.addEventListener('click', function () {
            const table = this.getAttribute('data-table');
            const id = this.getAttribute('data-id');
            const currentNum = this.getAttribute('data-number') || '';

            modalTableType.value = table;
            modalRecordId.value = id;
            modalInvoiceNumber.value = currentNum;

            updateModal.show();
        });
    });

    updateForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const tableType = modalTableType.value;
        const recordId = modalRecordId.value;
        const newInvoiceNo = modalInvoiceNumber.value.trim();

        if (!newInvoiceNo) {
            Swal.fire('Validation Error', 'Invoice Number cannot be empty.', 'warning');
            return;
        }

        btnSubmitModal.disabled = true;
        spinnerSubmit.classList.remove('d-none');

        fetch("{{ route('admin.invoices.update-number') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                table_type: tableType,
                record_id: recordId,
                invoice_number: newInvoiceNo
            })
        })
        .then(res => res.json())
        .then(data => {
            btnSubmitModal.disabled = false;
            spinnerSubmit.classList.add('d-none');

            if (data.success) {
                updateModal.hide();

                // Update text on page live
                const textElem = document.getElementById(`inv-text-${tableType}-${recordId}`);
                if (textElem) {
                    textElem.textContent = data.invoice_number;
                }

                // Update data-number attribute on edit buttons
                document.querySelectorAll(`.btn-edit-inv[data-table="${tableType}"][data-id="${recordId}"]`).forEach(b => {
                    b.setAttribute('data-number', data.invoice_number);
                });

                Swal.fire({
                    icon: 'success',
                    title: 'Updated!',
                    text: data.message || 'Invoice Number updated successfully.',
                    timer: 2000,
                    showConfirmButton: false
                });
            } else {
                Swal.fire('Error', data.message || 'Could not update invoice number.', 'error');
            }
        })
        .catch(err => {
            btnSubmitModal.disabled = false;
            spinnerSubmit.classList.add('d-none');
            Swal.fire('Error', 'Server communication error: ' + err.message, 'error');
        });
    });
});
</script>
@endpush
