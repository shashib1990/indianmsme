@extends('layouts.ddc')

@section('title', 'Regional Purchase & Stock In')
@section('page-title', 'Regional Purchase & Stock In')

@section('content')
    <!-- Header Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-8">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                Regional Purchase & Stock In
                            </h4>
                            <p class="text-muted small mb-0">Record regional supplier stock receipt and pricing. Add one or multiple products under a single purchase invoice.</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <a href="{{ route('ddc.local-purchases.index') }}" class="btn btn-outline-secondary btn-sm fw-semibold">
                                <iconify-icon icon="solar:arrow-left-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Back to Purchases
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-4 mb-4" role="alert">
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form Card -->
    <form action="{{ route('ddc.local-purchases.store') }}" method="POST" id="stock-in-form">
        @csrf
        
        <!-- Section 1: Supplier & Invoice Information -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <h6 class="fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:user-bold-duotone" class="text-success fs-20"></iconify-icon>
                    1. Supplier & Invoice Details
                </h6>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-slate-700">Purchase Date <span class="text-danger">*</span></label>
                        <input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-slate-700">Supplier Name <span class="text-danger">*</span></label>
                        <input type="text" name="supplier_name" class="form-control" placeholder="e.g. Acme Wholesalers" value="{{ old('supplier_name') }}" required>
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-slate-700">Supplier Mobile</label>
                        <input type="text" name="supplier_mobile" class="form-control" placeholder="Mobile Number" value="{{ old('supplier_mobile') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-slate-700">Invoice / Bill Number</label>
                        <input type="text" name="invoice_number" class="form-control" placeholder="Invoice No." value="{{ old('invoice_number') }}">
                    </div>

                    <div class="col-md-3">
                        <label class="form-label fw-semibold text-slate-700">Invoice Date</label>
                        <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', date('Y-m-d')) }}">
                    </div>

                    <div class="col-md-9">
                        <label class="form-label fw-semibold text-slate-700">Remarks / Note</label>
                        <input type="text" name="remarks" class="form-control" placeholder="Optional notes for this stock in" value="{{ old('remarks') }}">
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Multi-Product Purchase Table -->
        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="text-primary fs-20"></iconify-icon>
                    2. Product Items Receipt
                </h6>
                <button type="button" class="btn btn-sm btn-success rounded-3 px-3 shadow-sm" id="btn-add-item">
                    <iconify-icon icon="solar:add-circle-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Add Product
                </button>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table align-middle mb-0" id="items-table">
                        <thead class="bg-light fs-11 text-uppercase fw-bold text-muted border-bottom">
                            <tr>
                                <th class="ps-4 py-3" style="min-width: 220px;">Select Product</th>
                                <th class="py-3" style="width: 90px;">Qty</th>
                                <th class="py-3" style="width: 130px;">MRP</th>
                                <th class="py-3" style="width: 140px;">Purch. Price</th>
                                <th class="py-3" style="width: 130px;">CSP (Retail)</th>
                                <th class="py-3" style="width: 130px;">MSP (Wholesale)</th>
                                <th class="py-3" style="min-width: 200px;">Batch, MFG & EXP Date</th>
                                <th class="pe-4 text-center py-3" style="width: 60px;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="items-body" class="fs-13">
                            <!-- Item Row 0 -->
                            <tr class="item-row" data-index="0">
                                <td class="ps-4 py-3">
                                    <select name="items[0][local_product_id]" class="form-select form-select-sm product-select" required>
                                        <option value="">— Select Product —</option>
                                        @foreach($localProducts as $lp)
                                            <option value="{{ $lp->id }}" {{ old('local_product_id', $selectedProductId) == $lp->id ? 'selected' : '' }}>
                                                {{ $lp->product_name }} ({{ $lp->local_product_code }})
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td>
                                    <input type="number" name="items[0][quantity]" class="form-control form-control-sm qty-input text-center fw-bold" min="1" value="1" required>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" step="0.01" name="items[0][mrp]" class="form-control mrp-input text-end" min="0" placeholder="0.00" required>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" step="0.01" name="items[0][purchase_price]" class="form-control price-input text-end fw-bold" min="0" placeholder="0.00" required>
                                        <input type="hidden" name="items[0][landing_cost]" class="landing-input">
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" step="0.01" name="items[0][csp]" class="form-control csp-input text-end text-emerald-700 fw-bold" min="0" placeholder="0.00" required>
                                    </div>
                                </td>
                                <td>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text">₹</span>
                                        <input type="number" step="0.01" name="items[0][msp]" class="form-control msp-input text-end" min="0" placeholder="0.00" required>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column gap-1">
                                        <input type="text" name="items[0][batch_number]" class="form-control form-control-sm" placeholder="Batch No.">
                                        <div class="d-flex gap-1">
                                            <input type="date" name="items[0][mfg_date]" class="form-control form-control-sm fs-11 text-muted" title="MFG Date">
                                            <input type="date" name="items[0][expire_date]" class="form-control form-control-sm fs-11 text-muted" title="Expiry Date">
                                        </div>
                                    </div>
                                </td>
                                <td class="pe-4 text-center py-3">
                                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row rounded-3 px-2 py-1" title="Remove Item">
                                        <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white p-3 border-top d-flex align-items-center justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-success rounded-3 px-3" id="btn-add-item-bottom">
                    <iconify-icon icon="solar:add-circle-bold" class="align-middle me-1"></iconify-icon> Add Another Product
                </button>

                <div class="text-end">
                    <span class="text-muted small me-2">Total Invoice Amount:</span>
                    <h4 class="fw-bold text-emerald-800 d-inline-block mb-0" id="grand-total-display">₹0.00</h4>
                </div>
            </div>
        </div>

        <!-- Submit Buttons -->
        <div class="text-end">
            <a href="{{ route('ddc.local-purchases.index') }}" class="btn btn-light rounded-3 px-4 me-2">Cancel</a>
            <button type="submit" class="btn btn-primary rounded-3 px-5 py-2 fw-bold shadow-sm">
                <iconify-icon icon="solar:check-circle-bold" class="align-middle me-1 fs-18"></iconify-icon> Save Stock In Receipt
            </button>
    </form>

<!-- Product Select Template Options (Hidden) -->
<select id="product-options-template" class="d-none">
    <option value="">— Select Product —</option>
    @foreach($localProducts as $lp)
        <option value="{{ $lp->id }}">
            {{ $lp->product_name }} ({{ $lp->local_product_code }})
        </option>
    @endforeach
</select>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let rowIndex = 1;
    const itemsBody = document.getElementById('items-body');
    const productOptionsHtml = document.getElementById('product-options-template').innerHTML;
    const grandTotalDisplay = document.getElementById('grand-total-display');

    function calculateTotals() {
        let total = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.qty-input')?.value || 0);
            const price = parseFloat(row.querySelector('.price-input')?.value || 0);
            const landingInput = row.querySelector('.landing-input');
            if (landingInput) {
                landingInput.value = price;
            }
            total += (qty * price);
        });
        grandTotalDisplay.textContent = '₹' + total.toFixed(2);
    }

    function addRow() {
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.dataset.index = rowIndex;
        tr.innerHTML = `
            <td class="ps-4 py-3">
                <select name="items[${rowIndex}][local_product_id]" class="form-select form-select-sm product-select" required>
                    ${productOptionsHtml}
                </select>
            </td>
            <td>
                <input type="number" name="items[${rowIndex}][quantity]" class="form-control form-control-sm qty-input text-center fw-bold" min="1" value="1" required>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">₹</span>
                    <input type="number" step="0.01" name="items[${rowIndex}][mrp]" class="form-control mrp-input text-end" min="0" placeholder="0.00" required>
                </div>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">₹</span>
                    <input type="number" step="0.01" name="items[${rowIndex}][purchase_price]" class="form-control price-input text-end fw-bold" min="0" placeholder="0.00" required>
                    <input type="hidden" name="items[${rowIndex}][landing_cost]" class="landing-input">
                </div>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">₹</span>
                    <input type="number" step="0.01" name="items[${rowIndex}][csp]" class="form-control csp-input text-end text-emerald-700 fw-bold" min="0" placeholder="0.00" required>
                </div>
            </td>
            <td>
                <div class="input-group input-group-sm">
                    <span class="input-group-text">₹</span>
                    <input type="number" step="0.01" name="items[${rowIndex}][msp]" class="form-control msp-input text-end" min="0" placeholder="0.00" required>
                </div>
            </td>
            <td>
                <div class="d-flex flex-column gap-1">
                    <input type="text" name="items[${rowIndex}][batch_number]" class="form-control form-control-sm" placeholder="Batch No.">
                    <div class="d-flex gap-1">
                        <input type="date" name="items[${rowIndex}][mfg_date]" class="form-control form-control-sm fs-11 text-muted" title="MFG Date">
                        <input type="date" name="items[${rowIndex}][expire_date]" class="form-control form-control-sm fs-11 text-muted" title="Expiry Date">
                    </div>
                </div>
            </td>
            <td class="pe-4 text-center py-3">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row rounded-3 px-2 py-1" title="Remove Item">
                    <iconify-icon icon="solar:trash-bin-trash-bold"></iconify-icon>
                </button>
            </td>
        `;
        itemsBody.appendChild(tr);
        rowIndex++;
        calculateTotals();
    }

    document.getElementById('btn-add-item')?.addEventListener('click', addRow);
    document.getElementById('btn-add-item-bottom')?.addEventListener('click', addRow);

    itemsBody.addEventListener('click', function(e) {
        if (e.target.closest('.btn-remove-row')) {
            const rows = itemsBody.querySelectorAll('.item-row');
            if (rows.length > 1) {
                e.target.closest('.item-row').remove();
                calculateTotals();
            } else {
                alert('At least one product item is required in the purchase receipt.');
            }
        }
    });

    itemsBody.addEventListener('input', function(e) {
        if (e.target.classList.contains('qty-input') || e.target.classList.contains('price-input')) {
            calculateTotals();
        }
    });

    calculateTotals();
});
</script>
@endpush
@endsection
