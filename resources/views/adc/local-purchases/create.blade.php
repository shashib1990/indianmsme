@extends('layouts.adc')

@section('title', 'Regional Purchase & Stock In')
@section('page-title', 'Regional Purchase & Stock In')

@push('styles')
    <style>
        /* Desktop Table Styling */
        #items-table th,
        #items-table td {
            vertical-align: middle;
        }

        /* Mobile View: Vertical Card Layout (Screens <= 768px) */
        @media (max-width: 768px) {
            .table-responsive {
                overflow-x: visible !important;
                padding: 10px 10px 0 !important;
            }

            #items-table,
            #items-table tbody,
            #items-table-body {
                display: block !important;
                width: 100% !important;
                min-width: 0 !important;
                border: none !important;
            }

            /* Hide horizontal table header on mobile */
            #items-table thead {
                display: none !important;
            }

            /* Each item row becomes a clean, touch-friendly vertical Card */
            #items-table tr.stock-item-row {
                display: flex !important;
                flex-wrap: wrap !important;
                background: #ffffff !important;
                border: 1.5px solid #e2e8f0 !important;
                border-radius: 14px !important;
                padding: 14px !important;
                margin-bottom: 16px !important;
                box-shadow: 0 3px 10px rgba(15, 23, 42, 0.05) !important;
            }

            /* Product dropdown: Full width */
            #items-table tr.stock-item-row td.cell-product {
                width: 100% !important;
                padding: 0 0 12px 0 !important;
                border: none !important;
            }

            /* Qty & MRP: 50% - 50% side-by-side */
            #items-table tr.stock-item-row td.cell-qty {
                width: 50% !important;
                padding: 0 6px 12px 0 !important;
                border: none !important;
            }

            #items-table tr.stock-item-row td.cell-mrp {
                width: 50% !important;
                padding: 0 0 12px 6px !important;
                border: none !important;
            }

            /* Purchase Price & CSP: 50% - 50% side-by-side */
            #items-table tr.stock-item-row td.cell-purchase-price {
                width: 50% !important;
                padding: 0 6px 0 0 !important;
                border: none !important;
            }

            #items-table tr.stock-item-row td.cell-csp {
                width: 50% !important;
                padding: 0 0 0 6px !important;
                border: none !important;
            }

            /* Hide desktop action column cell on mobile */
            #items-table tr.stock-item-row td.cell-action {
                display: none !important;
            }

            /* Remove number stepper arrows to maximize typing area on touch */
            #items-table input[type="number"]::-webkit-inner-spin-button,
            #items-table input[type="number"]::-webkit-outer-spin-button {
                -webkit-appearance: none !important;
                margin: 0 !important;
            }
            #items-table input[type="number"] {
                -moz-appearance: textfield !important;
                appearance: textfield !important;
            }

            /* Large comfortable input sizes for mobile fingers */
            #items-table .form-control,
            #items-table .form-select {
                height: 42px !important;
                font-size: 15px !important;
                border-radius: 8px !important;
            }

            #items-table .quantity-input,
            #items-table .mrp-input,
            #items-table .purchase-price-input,
            #items-table .csp-input {
                font-size: 15px !important;
                font-weight: 600 !important;
                min-width: 0 !important;
            }

            /* Action Buttons on Mobile: Clean horizontal pair with comfortable 44px height */
            .form-action-group {
                display: flex !important;
                gap: 10px !important;
                width: 100% !important;
                margin-bottom: 95px !important;
            }

            .form-action-group .btn-cancel {
                flex: 0 0 32% !important;
                height: 44px !important;
                font-size: 14px !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                padding: 0 8px !important;
            }

            .form-action-group .btn-submit-stock {
                flex: 1 1 68% !important;
                height: 44px !important;
                font-size: 13.5px !important;
                display: inline-flex !important;
                align-items: center !important;
                justify-content: center !important;
                padding: 0 10px !important;
                white-space: nowrap !important;
            }
        }
    </style>
@endpush

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
                        <div class="col-md-4 text-md-end d-flex align-items-center justify-content-md-end gap-2">
                            <a href="{{ route('adc.local-inventory.invoice-scan') }}" class="btn btn-primary btn-sm fw-semibold shadow-sm">
                                <iconify-icon icon="solar:scanner-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Smart Invoice Scanner
                            </a>
                            <a href="{{ route('adc.local-purchases.index') }}" class="btn btn-outline-secondary btn-sm fw-semibold">
                                <iconify-icon icon="solar:arrow-left-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Back to Purchases
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Smart Invoice Import Promo Banner -->
    <div class="alert alert-primary bg-primary-subtle border-0 rounded-4 shadow-sm mb-4 d-flex align-items-center justify-content-between flex-wrap gap-3 p-3">
        <div class="d-flex align-items-center gap-3">
            <span class="p-2 rounded-circle bg-primary text-white d-flex align-items-center justify-content-center">
                <iconify-icon icon="solar:scanner-bold-duotone" class="fs-24"></iconify-icon>
            </span>
            <div>
                <h6 class="fw-bold mb-0 text-slate-800">Scan &amp; Auto-Fill from Supplier Invoice</h6>
                <p class="small text-muted mb-0">Upload a PDF or bill photo (or use phone camera) to automatically extract supplier, invoice details and multiple products.</p>
            </div>
        </div>
        <a href="{{ route('adc.local-inventory.invoice-scan') }}" class="btn btn-primary rounded-3 px-3 fw-bold text-nowrap">
            <iconify-icon icon="solar:magic-stick-3-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Launch Invoice Scanner
        </a>
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
                        <thead class="bg-light fs-11 text-uppercase fw-bold text-muted border-bottom d-none d-md-table-header-group">
                            <tr>
                                <th class="ps-4" style="width: 34%;">Product Master <span class="text-danger">*</span></th>
                                <th style="width: 12%;">Qty <span class="text-danger">*</span></th>
                                <th style="width: 14%;">MRP <span class="text-danger">*</span></th>
                                <th style="width: 14%;">Purch. Price <span class="text-danger">*</span></th>
                                <th style="width: 14%;">CSP / Price <span class="text-danger">*</span></th>
                                <th class="pe-4 text-center" style="width: 12%;">Action</th>
                            </tr>
                        </thead>
                        <tbody id="items-table-body" class="fs-13">
                            <!-- Dynamic Item Rows / Cards -->
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="card-footer bg-white py-3 px-4 border-top text-end">
                <h5 class="fw-bold text-slate-800 mb-0">Total Purchase Amount: <span class="text-primary" id="grand-total-display">₹0.00</span></h5>
            </div>
        </div>

        <div class="d-flex align-items-center justify-content-end gap-2 gap-md-3 mb-5 form-action-group">
            <a href="{{ route('adc.local-purchases.index') }}" class="btn btn-light rounded-3 px-3 px-md-4 fw-semibold btn-cancel">Cancel</a>
            <button type="submit" class="btn btn-success rounded-3 px-3 px-md-5 fw-bold shadow-sm btn-submit-stock">
                <iconify-icon icon="solar:check-circle-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                <span class="d-none d-sm-inline">Perform </span>Stock In & Save<span class="d-none d-sm-inline"> Invoice</span>
            </button>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        const localProducts = @json($localProducts);
        const selectedProductId = "{{ $selectedProductId }}";

        let itemIndex = 0;

        function updateCardNumbers() {
            const rows = document.querySelectorAll('#items-table-body tr.stock-item-row');
            rows.forEach((r, idx) => {
                const badge = r.querySelector('.item-card-num');
                if (badge) badge.textContent = `Product #${idx + 1}`;
            });
        }

        function addRow(selectedId = '') {
            const tbody = document.getElementById('items-table-body');
            const tr = document.createElement('tr');
            tr.id = `item-row-${itemIndex}`;
            tr.className = 'stock-item-row';

            let optionsHtml = '<option value="">Select Regional Product</option>';
            localProducts.forEach(p => {
                const isSelected = (selectedId && selectedId == p.id) ? 'selected' : '';
                optionsHtml += `<option value="${p.id}" ${isSelected}>${p.product_name} (${p.local_product_code})</option>`;
            });

            tr.innerHTML = `
                <td class="cell-product ps-md-4">
                    <!-- Mobile Card Top Bar (Item # & Remove Button) -->
                    <div class="d-flex align-items-center justify-content-between d-md-none mb-2 pb-1 border-bottom">
                        <span class="badge bg-primary-subtle text-primary fw-bold fs-12 px-2.5 py-1">
                            <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="align-middle me-1"></iconify-icon>
                            <span class="item-card-num">Product #${document.querySelectorAll('#items-table-body tr.stock-item-row').length + 1}</span>
                        </span>
                        <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2.5 fs-12 d-inline-flex align-items-center gap-1 rounded-2" onclick="removeRow(${itemIndex})">
                            <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="fs-14"></iconify-icon> Remove
                        </button>
                    </div>

                    <label class="form-label d-md-none fw-semibold text-slate-700 fs-12 mb-1">
                        Product Master <span class="text-danger">*</span>
                    </label>
                    <select name="items[${itemIndex}][local_product_id]" class="form-select product-select" required>
                        ${optionsHtml}
                    </select>
                </td>
                <td class="cell-qty">
                    <label class="form-label d-md-none fw-semibold text-slate-700 fs-12 mb-1">
                        Qty <span class="text-danger">*</span>
                    </label>
                    <input type="number" step="any" min="0.001" inputmode="decimal" name="items[${itemIndex}][quantity]" class="form-control quantity-input" placeholder="Qty" value="1" required onclick="this.select()" onfocus="this.select()">
                </td>
                <td class="cell-mrp">
                    <label class="form-label d-md-none fw-semibold text-slate-700 fs-12 mb-1">
                        MRP (₹) <span class="text-danger">*</span>
                    </label>
                    <input type="number" step="any" min="0" inputmode="decimal" name="items[${itemIndex}][mrp]" class="form-control mrp-input" placeholder="MRP" value="0.00" required onclick="this.select()" onfocus="this.select()">
                </td>
                <td class="cell-purchase-price">
                    <label class="form-label d-md-none fw-semibold text-slate-700 fs-12 mb-1">
                        Purch. Price (₹) <span class="text-danger">*</span>
                    </label>
                    <input type="number" step="any" min="0" inputmode="decimal" name="items[${itemIndex}][purchase_price]" class="form-control purchase-price-input" placeholder="Purch. Price" value="0.00" required onclick="this.select()" onfocus="this.select()">
                </td>
                <td class="cell-csp">
                    <label class="form-label d-md-none fw-semibold text-slate-700 fs-12 mb-1">
                        CSP / Price (₹) <span class="text-danger">*</span>
                    </label>
                    <input type="number" step="any" min="0" inputmode="decimal" name="items[${itemIndex}][csp]" class="form-control csp-input" placeholder="CSP" value="0.00" required onclick="this.select()" onfocus="this.select()">
                </td>
                <td class="pe-md-4 text-center cell-action d-none d-md-table-cell">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remove-row" onclick="removeRow(${itemIndex})" title="Remove product">
                        <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="fs-16 align-middle"></iconify-icon>
                    </button>
                </td>
            `;

            tbody.appendChild(tr);
            updateCardNumbers();

            // Add change and input event listeners for real-time calculation
            const pInput = tr.querySelector('.purchase-price-input');
            const qInput = tr.querySelector('.quantity-input');
            const mInput = tr.querySelector('.mrp-input');
            const cInput = tr.querySelector('.csp-input');

            [qInput, mInput, pInput, cInput].forEach(inp => {
                if (inp) {
                    inp.addEventListener('input', calculateGrandTotal);
                    inp.addEventListener('change', calculateGrandTotal);
                }
            });

            itemIndex++;
            calculateGrandTotal();
        }

        function removeRow(idx) {
            const tr = document.getElementById(`item-row-${idx}`);
            if (tr) tr.remove();
            updateCardNumbers();
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
