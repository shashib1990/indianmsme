@extends('layouts.adc')

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
                            <a href="{{ route('adc.local-purchases.index') }}" class="btn btn-outline-secondary btn-sm fw-semibold">
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
    <form action="{{ route('adc.local-purchases.store') }}" method="POST" id="stock-in-form">
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
                                <th style="width: 35%;" class="ps-4">Product Master <span class="text-danger">*</span></th>
                                <th style="width: 12%;">Qty <span class="text-danger">*</span></th>
                                <th style="width: 15%;">MRP <span class="text-danger">*</span></th>
                                <th style="width: 15%;">Purch. Price <span class="text-danger">*</span></th>
                                <th style="width: 15%;">CSP / Price <span class="text-danger">*</span></th>
                                <th style="width: 8%;" class="pe-4 text-center">Action</th>
                            </tr>
                        </thead>
                        <tbody id="items-table-body" class="fs-13">
                            <!-- Dynamic Item Rows -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white py-3 px-4 border-top text-end">
                <h5 class="fw-bold text-slate-800 mb-0">Total Purchase Amount: <span class="text-primary" id="grand-total-display">₹0.00</span></h5>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-3 mb-5">
            <a href="{{ route('adc.local-purchases.index') }}" class="btn btn-light rounded-3 px-4 fw-semibold">Cancel</a>
            <button type="submit" class="btn btn-success btn-lg rounded-3 px-5 fw-bold shadow-sm">
                <iconify-icon icon="solar:check-circle-bold-duotone" class="align-middle me-1 fs-20"></iconify-icon>
                Perform Stock In & Save Invoice
            </button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        const localProducts = @json($localProducts);
        const selectedProductId = "{{ $selectedProductId }}";

        let itemIndex = 0;

        function addRow(selectedId = '') {
            const tbody = document.getElementById('items-table-body');
            const tr = document.createElement('tr');
            tr.id = `item-row-${itemIndex}`;

            let optionsHtml = '<option value="">Select Regional Product</option>';
            localProducts.forEach(p => {
                const isSelected = (selectedId && selectedId == p.id) ? 'selected' : '';
                optionsHtml += `<option value="${p.id}" ${isSelected}>${p.product_name} (${p.local_product_code})</option>`;
            });

            tr.innerHTML = `
                <td class="ps-4">
                    <select name="items[${itemIndex}][local_product_id]" class="form-select product-select" required>
                        ${optionsHtml}
                    </select>
                </td>
                <td>
                    <input type="number" step="0.001" name="items[${itemIndex}][quantity]" class="form-control quantity-input" placeholder="Qty" value="1" required>
                </td>
                <td>
                    <input type="number" step="0.01" name="items[${itemIndex}][mrp]" class="form-control mrp-input" placeholder="MRP" value="0.00" required>
                </td>
                <td>
                    <input type="number" step="0.01" name="items[${itemIndex}][purchase_price]" class="form-control purchase-price-input" placeholder="Purch. Price" value="0.00" required>
                </td>
                <td>
                    <input type="number" step="0.01" name="items[${itemIndex}][csp]" class="form-control csp-input" placeholder="CSP" value="0.00" required>
                </td>
                <td class="pe-4 text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" onclick="removeRow(${itemIndex})">
                        <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="fs-16 align-middle"></iconify-icon>
                    </button>
                </td>
            `;

            tbody.appendChild(tr);

            // Add change event listeners for calculation
            const pInput = tr.querySelector('.purchase-price-input');
            const qInput = tr.querySelector('.quantity-input');
            const mInput = tr.querySelector('.mrp-input');
            const cInput = tr.querySelector('.csp-input');

            [qInput, mInput, pInput, cInput].forEach(inp => {
                if (inp) inp.addEventListener('input', calculateGrandTotal);
            });

            itemIndex++;
            calculateGrandTotal();
        }

        function removeRow(idx) {
            const tr = document.getElementById(`item-row-${idx}`);
            if (tr) tr.remove();
            calculateGrandTotal();
        }

        function calculateGrandTotal() {
            let total = 0;
            const rows = document.querySelectorAll('#items-table-body tr');
            rows.forEach(r => {
                const qty = parseFloat(r.querySelector('.quantity-input')?.value || 0);
                const price = parseFloat(r.querySelector('.purchase-price-input')?.value || r.querySelector('.mrp-input')?.value || 0);
                total += (qty * price);
            });
            document.getElementById('grand-total-display').textContent = '₹' + total.toFixed(2);
        }

        document.getElementById('btn-add-item').addEventListener('click', function() {
            addRow();
        });

        document.addEventListener('DOMContentLoaded', function() {
            addRow(selectedProductId);
        });
    </script>
@endpush
