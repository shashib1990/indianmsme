@extends('layouts.ddc')

@section('title', 'Receive Stock')
@section('page-title', 'Stock Receive')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row align-items-center g-3">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <a href="{{ route('ddc.purchase-orders.index') }}" class="btn btn-sm btn-outline-secondary py-1 px-2.5">
                                    <iconify-icon icon="solar:arrow-left-bold" class="align-middle me-1"></iconify-icon> Back
                                </a>
                                <span class="badge bg-primary-subtle text-primary px-2.5 py-1.5 fs-12">PO Receipt</span>
                            </div>
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                Receive Stock for PO #{{ $purchaseOrder->po_number }}
                            </h4>
                            <p class="text-muted small mb-0">
                                Seller: <strong class="text-dark">{{ $purchaseOrder->seller->company_name ?: $purchaseOrder->seller->name }}</strong> 
                                | DDC Invoice: <strong class="text-dark">{{ $purchaseOrder->invoice->ddc_invoice_number }}</strong> ({{ \Carbon\Carbon::parse($purchaseOrder->invoice->ddc_invoice_date)->format('d M Y') }})
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end" id="po-status-badge">
                            <span class="fs-14 text-muted">PO Status:</span>
                            @if($purchaseOrder->status === 'Pending')
                                <span class="badge bg-secondary-subtle text-secondary px-2.5 py-1.5 fs-13">PO Created</span>
                            @elseif($purchaseOrder->status === 'Accepted')
                                <span class="badge bg-success-subtle text-success px-2.5 py-1.5 fs-13">Accepted</span>
                            @elseif($purchaseOrder->status === 'Processing')
                                <span class="badge bg-primary-subtle text-primary px-2.5 py-1.5 fs-13">Processing</span>
                            @elseif($purchaseOrder->status === 'Dispatched')
                                <span class="badge bg-warning-subtle text-warning px-2.5 py-1.5 fs-13">Dispatched</span>
                            @elseif($purchaseOrder->status === 'Received')
                                <span class="badge bg-success text-white px-2.5 py-1.5 fs-13">Received</span>
                            @else
                                <span class="badge bg-light text-muted px-2.5 py-1.5 fs-13">{{ $purchaseOrder->status }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Form Definitions (Hidden) -->
    @foreach($items as $item)
        @if(!$item->is_stocked_in)
            <form id="stock-in-form-{{ $item->id }}" action="{{ route('ddc.purchase-orders.items.stock-in', $item->id) }}" method="POST" enctype="multipart/form-data" style="display: none;">
                @csrf
                <input type="hidden" name="supply_qty" value="{{ $item->quantity }}">
            </form>
        @endif
    @endforeach

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="fw-bold text-slate-800 mb-0">Accepted Products List</h6>
            <span class="text-muted small">Update and stock-in each product below.</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="min-width: 1200px;">
                    <thead class="bg-light bg-opacity-50 border-top border-bottom">
                        <tr>
                            <th class="ps-3" style="width: 220px;">Product Details</th>
                            <th style="width: 110px;">Code & Barcode</th>
                            <th style="width: 110px;">Pack Size & Tax</th>
                            <th style="width: 130px;">Price & MRP</th>
                            <th class="text-center" style="width: 90px;">Demand & Supply</th>
                            <th class="text-center" style="width: 90px;">Damage Qty</th>
                            <th class="text-center" style="width: 90px;">Shortage Qty</th>
                            <th class="text-center" style="width: 90px;">Excess Qty</th>
                            <th style="width: 120px;">Batch No</th>
                            <th style="width: 130px;">Mfg Date</th>
                            <th style="width: 130px;">Expire Date</th>
                            <th class="pe-3 text-center" style="width: 120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            <tr id="item-row-{{ $item->id }}" class="{{ $item->is_stocked_in ? 'table-light text-muted' : '' }}">
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="border rounded bg-light overflow-hidden d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                            @if($item->product->featured_image)
                                                <img src="{{ $item->product->featured_image }}" alt="img" class="img-fluid object-fit-contain" style="max-height: 100%;">
                                            @else
                                                <iconify-icon icon="solar:gallery-bold" class="text-muted fs-20"></iconify-icon>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="fw-semibold text-slate-800 d-block fs-14 text-wrap" style="max-width: 200px;">
                                                {{ $item->product->product_name }}
                                            </span>
                                            <span class="text-muted small d-block">
                                                Brand: {{ $item->product->brand->name ?? '—' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="d-block fw-medium text-slate-700 small">PROD-{{ $item->product->product_code ?? $item->product->id }}</span>
                                    @php
                                        $existingBarcode = $item->barcode ?: $item->product->barcode;
                                    @endphp
                                    @if($existingBarcode)
                                        <span class="text-muted small d-block font-monospace mt-0.5" title="Product Barcode">
                                            <iconify-icon icon="solar:barcode-bold" class="align-middle me-0.5 text-slate-500"></iconify-icon>
                                            {{ $existingBarcode }}
                                        </span>
                                    @elseif(!$item->is_stocked_in)
                                        <input type="text" 
                                               form="stock-in-form-{{ $item->id }}" 
                                               name="barcode" 
                                               class="form-control form-control-sm px-1.5 py-1 mt-1 small" 
                                               placeholder="Barcode" 
                                               title="Enter Barcode">
                                    @else
                                        <span class="text-muted small d-block">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="fw-medium d-block text-slate-700 small">{{ $item->unit_of_measure ?: 'N/A' }}</span>
                                    <span class="text-muted small" style="font-size: 11px;">GST: {{ $item->product->gst_rate ?? 0 }}%</span>
                                </td>
                                <td id="mrp-cell-{{ $item->id }}">
                                    @if($item->is_stocked_in)
                                        <div class="mb-1">
                                            <span class="text-muted small">Purchase Price:</span>
                                            <span class="fw-semibold text-slate-800 d-block">₹{{ number_format($item->price, 2) }}</span>
                                        </div>
                                        <div>
                                            <span class="fw-bold text-success">₹{{ number_format($item->received_mrp, 2) }}</span>
                                            <span class="text-muted small d-block" style="font-size: 10px;">Ordered MRP: ₹{{ number_format($item->actual_mrp, 2) }}</span>
                                        </div>
                                    @else
                                        <div class="mb-1">
                                            <span class="text-muted small">Price:</span>
                                            <span class="fw-semibold text-slate-800 d-block">₹{{ number_format($item->price, 2) }}</span>
                                        </div>
                                        <label class="form-label text-muted small mb-0" style="font-size: 10px;">Received MRP</label>
                                        <input type="number" 
                                               step="0.01" 
                                               form="stock-in-form-{{ $item->id }}" 
                                               name="received_mrp" 
                                               class="form-control form-control-sm px-1.5 py-1 small fw-bold text-success" 
                                               value="{{ old('received_mrp', $item->received_mrp ?: $item->actual_mrp) }}" 
                                               placeholder="MRP">
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="mb-1" title="Ordered Qty">
                                        <span class="badge bg-secondary-subtle text-secondary px-2 py-0.5 fs-11">Ord: {{ $item->quantity }}</span>
                                    </div>
                                    @if($item->is_stocked_in)
                                        <span class="badge bg-success-subtle text-success px-2 py-0.5 fs-11">Rec: {{ $item->received_qty ?? $item->quantity }}</span>
                                    @else
                                        <label class="form-label text-muted small mb-0" style="font-size: 10px;">Sound Rec</label>
                                        <input type="number" 
                                               min="0" 
                                               max="{{ $item->quantity * 2 }}" 
                                               form="stock-in-form-{{ $item->id }}" 
                                               name="received_qty" 
                                               class="form-control form-control-sm px-1 text-center small fw-semibold text-success" 
                                               value="{{ old('received_qty', $item->quantity) }}" 
                                               placeholder="Qty">
                                    @endif
                                </td>
                                <td id="damage-cell-{{ $item->id }}" class="text-center">
                                    @if($item->is_stocked_in)
                                        <span class="fw-medium {{ $item->damage_qty > 0 ? 'text-danger' : 'text-muted' }}">{{ $item->damage_qty }}</span>
                                    @else
                                        <input type="number" 
                                               min="0" 
                                               max="{{ $item->quantity }}" 
                                               form="stock-in-form-{{ $item->id }}" 
                                               name="damage_qty" 
                                               class="form-control form-control-sm px-1 text-center small text-danger" 
                                               value="{{ old('damage_qty', $item->damage_qty ?? 0) }}" 
                                               placeholder="0">
                                    @endif
                                </td>
                                <td id="shortage-cell-{{ $item->id }}" class="text-center">
                                    @if($item->is_stocked_in)
                                        <span class="fw-medium {{ $item->shortage_qty > 0 ? 'text-warning' : 'text-muted' }}">{{ $item->shortage_qty }}</span>
                                    @else
                                        <input type="number" 
                                               min="0" 
                                               max="{{ $item->quantity }}" 
                                               form="stock-in-form-{{ $item->id }}" 
                                               name="shortage_qty" 
                                               class="form-control form-control-sm px-1 text-center small text-warning" 
                                               value="{{ old('shortage_qty', $item->shortage_qty ?? 0) }}" 
                                               placeholder="0">
                                    @endif
                                </td>
                                <td id="excess-cell-{{ $item->id }}" class="text-center">
                                    @if($item->is_stocked_in)
                                        <span class="fw-medium {{ $item->excess_qty > 0 ? 'text-primary' : 'text-muted' }}">{{ $item->excess_qty }}</span>
                                    @else
                                        <input type="number" 
                                               min="0" 
                                               form="stock-in-form-{{ $item->id }}" 
                                               name="excess_qty" 
                                               class="form-control form-control-sm px-1 text-center small text-primary" 
                                               value="{{ old('excess_qty', $item->excess_qty ?? 0) }}" 
                                               placeholder="0">
                                    @endif
                                </td>
                                <td id="batch-cell-{{ $item->id }}">
                                    @if($item->is_stocked_in)
                                        <span class="small">{{ $item->batch_number ?? '—' }}</span>
                                    @else
                                        <input type="text" 
                                               form="stock-in-form-{{ $item->id }}" 
                                               name="batch_number" 
                                               class="form-control form-control-sm px-1.5 py-1 small" 
                                               value="{{ old('batch_number', $item->batch_number) }}" 
                                               placeholder="Batch No">
                                    @endif
                                </td>
                                <td id="mfg-cell-{{ $item->id }}">
                                    @if($item->is_stocked_in)
                                        <span class="small">{{ $item->mfg_date ? (is_string($item->mfg_date) ? $item->mfg_date : $item->mfg_date->format('d M Y')) : '—' }}</span>
                                    @else
                                        <input type="date" 
                                               form="stock-in-form-{{ $item->id }}" 
                                               name="mfg_date" 
                                               class="form-control form-control-sm px-1 small" 
                                               value="{{ old('mfg_date', $item->mfg_date ? (is_string($item->mfg_date) ? $item->mfg_date : $item->mfg_date->format('Y-m-d')) : '') }}">
                                    @endif
                                </td>
                                <td id="exp-cell-{{ $item->id }}">
                                    @if($item->is_stocked_in)
                                        <span class="small">{{ $item->expire_date ? (is_string($item->expire_date) ? $item->expire_date : $item->expire_date->format('d M Y')) : '—' }}</span>
                                    @else
                                        <input type="date" 
                                               form="stock-in-form-{{ $item->id }}" 
                                               name="expire_date" 
                                               class="form-control form-control-sm px-1 small" 
                                               value="{{ old('expire_date', $item->expire_date ? (is_string($item->expire_date) ? $item->expire_date : $item->expire_date->format('Y-m-d')) : '') }}">
                                    @endif
                                </td>
                                <td id="action-cell-{{ $item->id }}" class="pe-3 text-center">
                                    @if($item->is_stocked_in)
                                        <span class="badge bg-success-subtle text-success px-2 py-1.5 fs-12 w-100">
                                            <iconify-icon icon="solar:check-circle-bold" class="align-middle me-1"></iconify-icon>
                                            Stocked In
                                        </span>
                                    @else
                                        <button type="submit" 
                                                form="stock-in-form-{{ $item->id }}" 
                                                class="btn btn-sm btn-primary py-1.5 px-3 w-100 fw-semibold">
                                            <iconify-icon icon="solar:import-bold" class="align-middle me-1"></iconify-icon>
                                            Stock In
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center py-4 text-muted small">No items to receive in this purchase order.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @php
        $pendingStockInCount = $items->where('is_stocked_in', false)->count();
        $hasDiscrepancy = $items->contains(function($i) {
            return ($i->shortage_qty > 0 || $i->damage_qty > 0 || $i->excess_qty > 0);
        });
        $existingDebitNote = $purchaseOrder->debitNote;
    @endphp

    <div class="card border-0 shadow-sm mt-4 bg-success bg-opacity-10 {{ ($pendingStockInCount === 0 && $items->isNotEmpty()) ? '' : 'd-none' }}" id="all-stocked-in-summary">
        <div class="card-body d-flex align-items-center justify-content-between py-3 flex-wrap gap-3">
            <div>
                <h6 class="fw-bold text-success mb-1">All Items Stocked In Successfully!</h6>
                <p class="text-muted small mb-0">Every product in this list has been stocked in and the order status has been updated to "Received".</p>
            </div>
            <div>
                @if($hasDiscrepancy)
                    @if($existingDebitNote)
                        <a href="{{ route('ddc.debit-notes.show', $existingDebitNote->id) }}" class="btn btn-warning text-dark fw-bold px-4 py-2 me-2">
                            <iconify-icon icon="solar:document-text-bold" class="align-middle me-1 fs-18"></iconify-icon>
                            View Debit Note #{{ $existingDebitNote->debit_note_number }}
                        </a>
                    @else
                        <a href="{{ route('ddc.purchase-orders.debit-note.create', $purchaseOrder->id) }}" class="btn btn-danger fw-bold px-4 py-2 me-2">
                            <iconify-icon icon="solar:document-add-bold" class="align-middle me-1 fs-18"></iconify-icon>
                            Generate Consolidated Debit Note
                        </a>
                    @endif
                @endif
                <a href="{{ route('ddc.purchase-orders.index') }}" class="btn btn-success fw-bold px-4 py-2">
                    <iconify-icon icon="solar:arrow-left-bold" class="align-middle me-1 fs-18"></iconify-icon>
                    Back to Purchase Orders
                </a>
            </div>
        </div>
    </div>

@push('styles')
<link rel="stylesheet" type="text/css" href="{{ asset('plugins/toastify/toastify.min.css') }}">
@endpush

@push('scripts')
<script type="text/javascript" src="{{ asset('plugins/toastify/toastify.min.js') }}"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[id^="stock-in-form-"]');
    
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formId = form.id;
            const itemId = formId.replace('stock-in-form-', '');
            const supplyQty = parseInt(form.querySelector('input[name="supply_qty"]')?.value) || 0;

            const damageInput = document.querySelector(`input[name="damage_qty"][form="${formId}"]`);
            const shortageInput = document.querySelector(`input[name="shortage_qty"][form="${formId}"]`);
            const mfgInput = document.querySelector(`input[name="mfg_date"][form="${formId}"]`);
            const expireInput = document.querySelector(`input[name="expire_date"][form="${formId}"]`);
            const submitBtn = document.querySelector(`button[form="${formId}"]`);

            const damageQty = parseInt(damageInput?.value) || 0;
            const shortageQty = parseInt(shortageInput?.value) || 0;
            
            // Damage Qty + Shortage Qty <= Supply Qty validation
            if ((damageQty + shortageQty) > supplyQty) {
                const errorMsg = `Damage Quantity (${damageQty}) + Shortage Quantity (${shortageQty}) cannot be greater than Supply Quantity (${supplyQty}).`;
                showToast(errorMsg, 'error');
                if (damageInput) damageInput.focus();
                return;
            }

            if (mfgInput && expireInput && mfgInput.value && expireInput.value) {
                if (new Date(expireInput.value) < new Date(mfgInput.value)) {
                    showToast('Expiry Date cannot be before Manufacturing Date.', 'error');
                    expireInput.focus();
                    return;
                }
            }

            // Disable button and show loading spinner
            let originalBtnHtml = '';
            if (submitBtn) {
                originalBtnHtml = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Stocking In...`;
            }

            // Construct FormData from form and explicit inputs associated with form ID
            const formData = new FormData(form);
            document.querySelectorAll(`[form="${formId}"]`).forEach(input => {
                if (input.name && !input.disabled) {
                    if (input.type === 'checkbox' || input.type === 'radio') {
                        if (input.checked) formData.set(input.name, input.value);
                    } else {
                        formData.set(input.name, input.value);
                    }
                }
            });

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: formData
            })
            .then(async response => {
                const data = await response.json();
                if (!response.ok || !data.success) {
                    throw new Error(data.message || 'Failed to stock in item.');
                }
                return data;
            })
            .then(data => {
                // DOM Updates for stocked-in row
                const tr = document.getElementById(`item-row-${itemId}`);
                if (tr) {
                    tr.classList.add('table-light', 'text-muted');
                }

                const mrpCell = document.getElementById(`mrp-cell-${itemId}`);
                if (mrpCell) {
                    mrpCell.innerHTML = `
                        <div class="mb-1">
                            <span class="text-muted small">Purchase Price:</span>
                            <span class="fw-semibold text-slate-800 d-block">₹${data.received_mrp}</span>
                        </div>
                        <div>
                            <span class="fw-bold text-success">₹${data.received_mrp}</span>
                            <span class="text-muted small d-block" style="font-size: 10px;">Ordered MRP: ₹${data.actual_mrp}</span>
                        </div>
                    `;
                }

                const damageCell = document.getElementById(`damage-cell-${itemId}`);
                if (damageCell) {
                    damageCell.innerHTML = `<span class="fw-medium ${data.damage_qty > 0 ? 'text-danger' : 'text-muted'}">${data.damage_qty}</span>`;
                }

                const shortageCell = document.getElementById(`shortage-cell-${itemId}`);
                if (shortageCell) {
                    shortageCell.innerHTML = `<span class="fw-medium ${data.shortage_qty > 0 ? 'text-warning' : 'text-muted'}">${data.shortage_qty}</span>`;
                }

                const excessCell = document.getElementById(`excess-cell-${itemId}`);
                if (excessCell) {
                    excessCell.innerHTML = `<span class="fw-medium ${data.excess_qty > 0 ? 'text-primary' : 'text-muted'}">${data.excess_qty}</span>`;
                }

                const batchCell = document.getElementById(`batch-cell-${itemId}`);
                if (batchCell) {
                    batchCell.innerHTML = `<span class="small">${data.batch_number}</span>`;
                }

                const mfgCell = document.getElementById(`mfg-cell-${itemId}`);
                if (mfgCell) {
                    mfgCell.innerHTML = `<span class="small">${data.mfg_date}</span>`;
                }

                const expCell = document.getElementById(`exp-cell-${itemId}`);
                if (expCell) {
                    expCell.innerHTML = `<span class="small">${data.expire_date}</span>`;
                }

                const actionCell = document.getElementById(`action-cell-${itemId}`);
                if (actionCell) {
                    actionCell.innerHTML = `
                        <span class="badge bg-success-subtle text-success px-2 py-1.5 fs-12 w-100">
                            <iconify-icon icon="solar:check-circle-bold" class="align-middle me-1"></iconify-icon>
                            Stocked In
                        </span>
                    `;
                }

                // If all items stocked in, update PO status badge & show summary banner + Success Modal
                if (data.all_stocked_in) {
                    const poBadgeContainer = document.getElementById('po-status-badge');
                    if (poBadgeContainer) {
                        poBadgeContainer.innerHTML = `
                            <span class="fs-14 text-muted">PO Status:</span>
                            <span class="badge bg-success text-white px-2.5 py-1.5 fs-13">Received</span>
                        `;
                    }
                    const summaryCard = document.getElementById('all-stocked-in-summary');
                    if (summaryCard) {
                        summaryCard.classList.remove('d-none');
                    }

                    // Open success modal when PO status changes to Received
                    showSuccessModal('PO Status: Received', data.message);
                } else {
                    // Item level stock-in toast notification
                    showToast(data.message, 'success');
                }
            })
            .catch(error => {
                showToast(error.message || 'Something went wrong.', 'error');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnHtml;
                }
            });
        });
    });

    function showToast(message, type = 'success') {
        if (typeof Toastify === 'function') {
            const bgColors = {
                success: "#10b981",
                error: "#ef4444",
                warning: "#f59e0b",
                info: "#3b82f6"
            };
            Toastify({
                text: message,
                duration: 3500,
                close: true,
                gravity: "top",
                position: "right",
                style: {
                    background: bgColors[type] || bgColors['success'],
                }
            }).showToast();
        } else if (typeof Swal === 'function') {
            const Toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true
            });
            Toast.fire({
                icon: type === 'error' ? 'error' : 'success',
                title: message
            });
        } else {
            alert(message);
        }
    }

    function showSuccessModal(title, message) {
        if (typeof Swal === 'function') {
            Swal.fire({
                icon: 'success',
                title: title || 'Stock In Completed',
                text: message,
                confirmButtonText: 'Great!',
                confirmButtonColor: '#10b981'
            });
        } else {
            alert((title ? title + ': ' : '') + message);
        }
    }
});
</script>
@endpush
@endsection
