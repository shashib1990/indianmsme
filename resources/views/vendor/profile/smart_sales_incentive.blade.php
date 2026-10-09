@extends('layouts.vendor')

@section('title', 'Smart Sales Incentive — Slab Configuration')
@section('page-title', 'Smart Sales Incentive')

@section('content')
    <!-- Action Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-4">
                    <div class="row align-items-center g-3">
                        <div class="col-lg-7 col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                <iconify-icon icon="solar:ticket-sale-bold-duotone" class="text-primary align-middle me-1 fs-24"></iconify-icon>
                                Slab-Wise Smart Sales Incentive (SSI)
                            </h4>
                            <p class="text-muted small mb-0">
                                Configure shopping amount range slabs. The incentive percentage will be automatically applied to AMA advisors based on total order value.
                            </p>
                        </div>
                        <div class="col-lg-5 col-md-6 text-md-end d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
                            <a href="{{ route('vendor.smart-sales-incentive.deductions') }}" class="btn btn-outline-primary d-inline-flex align-items-center gap-1">
                                <iconify-icon icon="solar:document-text-bold-duotone" class="fs-18"></iconify-icon>
                                SSI Deductions Log
                            </a>
                            <a href="{{ route('vendor.dashboard') }}" class="btn btn-outline-secondary d-inline-flex align-items-center gap-1">
                                <iconify-icon icon="solar:widget-5-bold-duotone" class="fs-18"></iconify-icon>
                                Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm mb-4 d-flex align-items-center gap-2">
            <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-20"></iconify-icon>
            <div>{{ session('success') }}</div>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger border-0 shadow-sm mb-4 d-flex align-items-center gap-2">
            <iconify-icon icon="solar:danger-bold-duotone" class="fs-20"></iconify-icon>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm mb-4">
            <h6 class="fw-bold mb-2 d-flex align-items-center gap-1">
                <iconify-icon icon="solar:shield-warning-bold-duotone" class="fs-18"></iconify-icon>
                Please correct the following errors:
            </h6>
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="row mt-2">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-light py-3 border-bottom d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold text-slate-800 mb-0">
                        <iconify-icon icon="solar:tuning-square-2-bold-duotone" class="text-primary me-1 fs-18"></iconify-icon>
                        Configure Shopping Amount Slabs
                    </h6>
                    <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1 fw-semibold" id="btn-add-slab">
                        <iconify-icon icon="solar:add-circle-bold-duotone" class="fs-16"></iconify-icon>
                        Add New Slab
                    </button>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('vendor.smart-sales-incentive.post') }}" method="POST" id="ssi-slab-form">
                        @csrf

                        <div class="table-responsive">
                            <table class="table table-bordered align-middle mb-3" id="slabs-table">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 60px;" class="text-center">#</th>
                                        <th style="min-width: 200px;">Min Shopping Amount (₹) <span class="text-danger">*</span></th>
                                        <th style="min-width: 220px;">Max Shopping Amount (₹) <small class="text-muted font-normal">(Leave empty for Unlimited)</small></th>
                                        <th style="min-width: 180px;">Incentive Percentage (%) <span class="text-danger">*</span></th>
                                        <th style="width: 90px;" class="text-center">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="slabs-container">
                                    @php
                                        $initialSlabs = old('slabs', $slabs->toArray());
                                    @endphp

                                    @if(!empty($initialSlabs) && count($initialSlabs) > 0)
                                        @foreach($initialSlabs as $index => $slab)
                                            <tr class="slab-row" data-id="{{ $slab['id'] ?? '' }}">
                                                <td class="text-center fw-semibold row-number">{{ $loop->iteration }}</td>
                                                <td>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light text-slate-600">₹</span>
                                                        <input type="number" 
                                                            name="slabs[{{ $index }}][min_amount]" 
                                                            class="form-control slab-min" 
                                                            value="{{ $slab['min_amount'] ?? '0' }}" 
                                                            min="0" 
                                                            step="0.01" 
                                                            required 
                                                            placeholder="0.00">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light text-slate-600">₹</span>
                                                        <input type="number" 
                                                            name="slabs[{{ $index }}][max_amount]" 
                                                            class="form-control slab-max" 
                                                            value="{{ isset($slab['max_amount']) && $slab['max_amount'] !== null ? $slab['max_amount'] : '' }}" 
                                                            min="0" 
                                                            step="0.01" 
                                                            placeholder="Above / Unlimited">
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="input-group">
                                                        <input type="number" 
                                                            name="slabs[{{ $index }}][percentage]" 
                                                            class="form-control slab-pct" 
                                                            value="{{ $slab['percentage'] ?? '0.00' }}" 
                                                            min="0" 
                                                            max="100" 
                                                            step="0.01" 
                                                            required 
                                                            placeholder="0.00">
                                                        <span class="input-group-text bg-light text-slate-600">%</span>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-outline-danger btn-sm btn-remove-row" title="Remove Slab">
                                                        <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="fs-16 align-middle" style="pointer-events: none;"></iconify-icon>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @else
                                        <!-- Empty state when no slabs exist -->
                                        <tr id="no-slabs-row">
                                            <td colspan="5" class="text-center text-muted py-4 fs-14">
                                                No SSI slabs added yet. Click "Add New Slab" to create an input slab.
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <div class="alert alert-info border-0 bg-info-subtle text-info-emphasis py-2 px-3 mb-4 small rounded">
                            <iconify-icon icon="solar:info-square-bold-duotone" class="fs-16 align-middle me-1"></iconify-icon>
                            <strong>Tip:</strong> If <code>Max Amount</code> is left empty, the slab will apply to all orders above the <code>Min Amount</code> (Unlimited upper range).
                        </div>

                        <div class="d-flex align-items-center gap-2">
                            <button type="submit" class="btn btn-success fw-bold px-4 py-2.5 shadow-sm text-white d-inline-flex align-items-center gap-1">
                                <iconify-icon icon="solar:diskette-bold-duotone" class="fs-18"></iconify-icon>
                                Save Slab Incentives
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const slabsContainer = document.getElementById('slabs-container');
    const btnAddSlab = document.getElementById('btn-add-slab');

    function checkEmptyState() {
        const rows = slabsContainer.querySelectorAll('.slab-row');
        let noSlabsRow = document.getElementById('no-slabs-row');
        if (rows.length === 0) {
            if (!noSlabsRow) {
                noSlabsRow = document.createElement('tr');
                noSlabsRow.id = 'no-slabs-row';
                noSlabsRow.innerHTML = `
                    <td colspan="5" class="text-center text-muted py-4 fs-14">
                        No SSI slabs added yet. Click "Add New Slab" to create an input slab.
                    </td>
                `;
                slabsContainer.appendChild(noSlabsRow);
            }
        } else {
            if (noSlabsRow) {
                noSlabsRow.remove();
            }
        }
    }

    function updateRowNumbers() {
        const rows = slabsContainer.querySelectorAll('.slab-row');
        rows.forEach((row, index) => {
            const numTd = row.querySelector('.row-number');
            if (numTd) numTd.textContent = index + 1;

            // Re-index input names
            row.querySelectorAll('input').forEach(input => {
                const nameAttr = input.getAttribute('name');
                if (nameAttr) {
                    const newName = nameAttr.replace(/slabs\[\d+\]/, `slabs[${index}]`);
                    input.setAttribute('name', newName);
                }
            });
        });
        checkEmptyState();
    }

    if (btnAddSlab) {
        btnAddSlab.addEventListener('click', function () {
            const noSlabsRow = document.getElementById('no-slabs-row');
            if (noSlabsRow) {
                noSlabsRow.remove();
            }

            const rowCount = slabsContainer.querySelectorAll('.slab-row').length;
            const newIndex = rowCount;

            // Suggest min_amount based on previous max_amount + 1 if available
            let suggestedMin = 0;
            const lastRow = slabsContainer.querySelector('.slab-row:last-child');
            if (lastRow) {
                const lastMaxInput = lastRow.querySelector('.slab-max');
                if (lastMaxInput && lastMaxInput.value) {
                    suggestedMin = parseFloat(lastMaxInput.value) + 1;
                }
            }

            const tr = document.createElement('tr');
            tr.className = 'slab-row';
            tr.innerHTML = `
                <td class="text-center fw-semibold row-number">${newIndex + 1}</td>
                <td>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-slate-600">₹</span>
                        <input type="number" name="slabs[${newIndex}][min_amount]" class="form-control slab-min" value="${suggestedMin}" min="0" step="0.01" required placeholder="0.00">
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-slate-600">₹</span>
                        <input type="number" name="slabs[${newIndex}][max_amount]" class="form-control slab-max" value="" min="0" step="0.01" placeholder="Above / Unlimited">
                    </div>
                </td>
                <td>
                    <div class="input-group">
                        <input type="number" name="slabs[${newIndex}][percentage]" class="form-control slab-pct" value="" min="0" max="100" step="0.01" required placeholder="e.g. 5.00">
                        <span class="input-group-text bg-light text-slate-600">%</span>
                    </div>
                </td>
                <td class="text-center">
                    <button type="button" class="btn btn-outline-danger btn-sm btn-remove-row" title="Remove Slab">
                        <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="fs-16 align-middle" style="pointer-events: none;"></iconify-icon>
                    </button>
                </td>
            `;

            slabsContainer.appendChild(tr);
            updateRowNumbers();
        });
    }

    if (slabsContainer) {
        slabsContainer.addEventListener('click', function (e) {
            const btnRemove = e.target.closest('.btn-remove-row');
            if (btnRemove) {
                const row = btnRemove.closest('.slab-row');
                if (row) {
                    const slabId = row.getAttribute('data-id');
                    row.remove();
                    updateRowNumbers();

                    if (slabId) {
                        fetch(`{{ url('vendor/smart-sales-incentive/slabs') }}/${slabId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            }
                        }).catch(err => {
                            console.error('Error deleting slab from DB:', err);
                        });
                    }
                }
            }
        });
    }
});
</script>
@endpush
