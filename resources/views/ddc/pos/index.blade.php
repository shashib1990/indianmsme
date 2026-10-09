@extends('layouts.pos')

@section('title', ($mode === 'wholesale' ? 'Wholesale POS' : 'Retail POS'))

@push('styles')
    <style>
        .pos-left-header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1.25rem;
        }

        .qty-btn {
            width: 24px;
            height: 24px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 4px;
            font-weight: bold;
        }

        .cart-items-scroll {
            flex: 1 1 auto;
            height: 0;
            min-height: 120px;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .cart-table {
            table-layout: fixed;
            width: 100%;
        }

        .cart-table th {
            font-size: 10px !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 5px 3px !important;
        }

        .cart-table td {
            font-size: 11px !important;
            padding: 5px 3px !important;
            vertical-align: middle;
            word-break: break-word;
        }

        .price-badge-retail {
            background: rgba(16, 185, 129, 0.12);
            color: #059669;
            font-weight: 700;
        }

        .price-badge-wholesale {
            background: rgba(245, 158, 11, 0.15);
            color: #d97706;
            font-weight: 700;
        }
    </style>
@endpush

@section('content')
    <div class="pos-wrapper">

        <!-- LEFT PANE: Search + Product Catalog Grid with Infinite Scroll -->
        <div class="pos-left-pane">

            <!-- Search & Control Header -->
            <div class="pos-left-header">
                <div class="row align-items-center g-2">
                    <div class="col-md-7">
                        <div class="input-group">
                            <span class="input-group-text bg-white text-muted border-end-0">
                                <iconify-icon icon="solar:magnifer-linear" class="fs-18"></iconify-icon>
                            </span>
                            <input type="text" id="productSearchInput" class="form-control border-start-0 ps-0 fs-13"
                                placeholder="Type Product Name, Code or Scan Barcode..." autocomplete="off" autofocus>
                            <button class="btn btn-outline-secondary btn-sm" type="button"
                                onclick="clearSearch()">Clear</button>
                        </div>
                    </div>
                    <div class="col-md-5 text-md-end">
                        <span class="fs-12 text-muted me-2" id="lblProductCount">Loading stock...</span>
                        <span
                            class="badge {{ $mode === 'wholesale' ? 'bg-warning text-dark' : 'bg-success text-white' }} px-2.5 py-1 fs-12 fw-bold">
                            {{ strtoupper($mode) }} POS MODE
                        </span>
                    </div>
                </div>
            </div>

            <!-- Scrollable Product Container for Infinite Scroll -->
            <div class="product-grid-container" id="productsScrollContainer">
                <div class="row g-3" id="productsGrid">
                    <!-- Dynamic Product Cards Appended via Infinite Scroll JS -->
                </div>

                <!-- Loader Sentinel for Infinite Scroll -->
                <div id="infiniteScrollLoader" class="text-center py-4">
                    <div class="spinner-border text-primary spinner-border-sm me-1" role="status"></div>
                    <span class="fs-12 text-muted fw-semibold">Loading more products...</span>
                </div>

                <!-- Empty State -->
                <div id="emptyStateBox" class="d-none text-center py-5">
                    <iconify-icon icon="solar:box-broken" class="fs-48 text-muted mb-2"></iconify-icon>
                    <h6 class="fw-semibold text-slate-700">No Inventory Items Found</h6>
                    <p class="text-muted fs-12 mb-0">No matching products in your stock inventory.</p>
                </div>
            </div>

        </div>

        <!-- RIGHT PANE: Customer / Canteen Lookup + Cart + Checkout Controls -->
        <div class="pos-right-pane p-3">

            <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                <h6 class="fw-bold text-slate-800 mb-0 fs-15 d-flex align-items-center gap-1.5">
                    <iconify-icon icon="solar:cart-bold-duotone" class="text-primary fs-20"></iconify-icon>
                    Current POS Order
                </h6>
                <button type="button" class="btn btn-xs btn-outline-danger" onclick="clearCart()">Clear Cart</button>
            </div>

            @if($mode === 'wholesale')
                <!-- Wholesale Mode: Select Assigned ADC Canteen -->
                <div class="mb-2 p-2.5 rounded-3 bg-light border border-warning-subtle shadow-xs">
                    <div class="d-flex justify-content-between align-items-center mb-1.5 px-0.5">
                        <label class="form-label text-slate-800 fw-bold fs-12 mb-0 d-flex align-items-center gap-1">
                            <iconify-icon icon="solar:shop-bold-duotone" class="text-warning fs-15"></iconify-icon>
                            Select Canteen (ADC)
                        </label>
                    </div>

                    <select id="adcSelect"
                        class="form-select form-select-sm fw-semibold text-slate-800 rounded-2 border-warning-subtle shadow-xs"
                        onchange="onAdcSelected(this)" style="padding: 5px 8px; font-size: 12px;">
                        <option value="">— Select Assigned Canteen (ADC) —</option>
                        @foreach($adcs as $adc)
                            @php
                                $cName = $adc->adcProfile?->company_name ?: $adc->name;
                                $cCode = $adc->adcProfile?->adc_code ?: ($adc->adcProfile?->code ?? 'ADC');
                                $wBal = (float) ($adc->adcProfile?->wallet_balance ?? 0);
                            @endphp
                            <option value="{{ $adc->id }}" data-company="{{ $cName }}" data-code="{{ $cCode }}"
                                data-wallet="{{ $wBal }}" data-gst="{{ $adc->adcProfile?->gst_number }}">
                                {{ $cName }} ({{ $cCode }}) — Wallet: ₹{{ number_format($wBal, 2) }}
                            </option>
                        @endforeach
                    </select>

                    <!-- Selected ADC Canteen Info Box & Wallet Display -->
                    <div id="selectedAdcBox" class="d-none p-2 rounded-2 shadow-xs mt-1.5">
                        <div class="d-flex align-items-center justify-content-between px-0.5">
                            <div>
                                <div class="fw-bold text-slate-900 fs-12" id="lblAdcCompany">Canteen Name</div>
                                <div class="text-muted fs-11">Canteen Code / ID: <span class="fw-bold text-slate-800"
                                        id="lblAdcCode">ADC001</span></div>
                            </div>
                            <div class="text-end">
                                <span class="text-muted d-block fs-10">Canteen Wallet</span>
                                <span class="badge bg-success-subtle text-success fw-bold fs-12 px-2 py-0.5 rounded-pill"
                                    id="lblAdcWallet">₹0.00</span>
                            </div>
                        </div>
                    </div>
                </div>
            @else
                <!-- Retail Mode: Customer Search / Selection -->
                <div class="mb-2 p-2.5 rounded-3 bg-light border">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label text-slate-700 fw-semibold fs-12 mb-0">Customer Details</label>
                        <button type="button" class="btn btn-link p-0 text-primary fs-11 text-decoration-none fw-bold"
                            data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                            + Add New Customer
                        </button>
                    </div>

                    <!-- Mobile Lookup Input -->
                    <div class="input-group input-group-sm mb-0" id="custSearchGroup">
                        <input type="text" id="custMobileSearch" class="form-control"
                            placeholder="Enter Mobile Number (10 digits)..." maxlength="10"
                            oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        <button class="btn btn-primary" type="button" id="btnSearchCust" onclick="searchCustomerByMobile()">
                            Search
                        </button>
                    </div>

                    <!-- Customer Selected Display Box -->
                    <div id="selectedCustBox" class="d-none bg-white p-2 rounded border border-success">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <div class="fw-bold text-slate-800 fs-12" id="lblCustName">Customer Name</div>
                                <div class="text-muted fs-11" id="lblCustMobile">+91 XXXXXXXXXX</div>
                            </div>
                            <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1.5"
                                onclick="resetCustomerSelection()">Change</button>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Cart Items Scrollable Table -->
            <div class="cart-items-scroll mb-2">
                <table class="table table-sm align-middle cart-table mb-0" id="cartTable">
                    <thead class="table-light sticky-top">
                        <tr class="fs-10 text-muted text-uppercase">
                            <th style="width: 4%;" class="text-center">#</th>
                            <th style="width: 27%;">Product Details</th>
                            <th class="text-end" style="width: 13%;">MRP</th>
                            <th class="text-end" style="width: 14%;">Price</th>
                            <th class="text-center" style="width: 8%;">Stk</th>
                            <th class="text-center" style="width: 16%;">Qty</th>
                            <th class="text-end text-nowrap" style="width: 15%;">Total</th>
                            <th class="text-center" style="width: 4%;"></th>
                        </tr>
                    </thead>
                    <tbody id="cartTableBody">
                        <tr>
                            <td colspan="8" class="text-center py-5 text-muted fs-12" id="emptyCartRow">
                                <div class="d-flex flex-column align-items-center justify-content-center py-4">
                                    <iconify-icon icon="solar:cart-large-minimalistic-broken"
                                        class="fs-48 text-muted mb-2"></iconify-icon>
                                    <h6 class="fw-semibold text-slate-700 mb-1">Your Cart is Empty</h6>
                                    <p class="text-muted fs-12 mb-0">Click products on the left catalog to add items.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Calculations & Checkout Bar -->
            <div class="border-top pt-2 mt-auto">
                <div class="d-flex justify-content-between fs-12 mb-1">
                    <span class="text-muted">Sub Total:</span>
                    <span class="fw-bold text-slate-800" id="lblSubtotal">₹0.00</span>
                </div>

                <div class="row g-2 my-1">
                    <div class="col-6">
                        <label class="form-label fs-11 text-muted mb-0">Discount (₹)</label>
                        <input type="text" id="inputDiscount"
                            class="form-control form-control-sm bg-light fw-bold text-success" value="0.00" readonly>
                    </div>
                    <div class="col-6">
                        <label class="form-label fs-11 text-muted mb-0">Tax / GST (Incl.) (₹)</label>
                        <input type="text" id="inputTax"
                            class="form-control form-control-sm bg-light fw-bold text-slate-800" value="0.00" readonly>
                    </div>
                </div>

                <div
                    class="d-flex justify-content-between align-items-center bg-primary-subtle p-2 rounded-3 border border-primary-subtle my-2">
                    <span class="fw-bold text-slate-800 fs-13">Grand Total:</span>
                    <span class="fw-bold text-primary fs-18" id="lblGrandTotal">₹0.00</span>
                </div>

                @if($mode === 'wholesale')
                    <!-- Wholesale Mode Notice: Payment automatically deducted from Canteen Wallet -->
                    <div
                        class="p-2 bg-success-subtle text-success rounded-3 border border-success mb-2 text-center fs-12 fw-bold">
                        <iconify-icon icon="solar:wallet-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon>
                        Payment will be deducted from Canteen Wallet
                    </div>

                    <!-- Complete Wholesale Order Button -->
                    <button type="button"
                        class="btn btn-success w-100 py-2 fs-14 fw-bold rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-1.5"
                        id="btnCheckout" onclick="processCheckout()">
                        <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-18"></iconify-icon>
                        Process Order & Deduct Wallet
                    </button>
                @else
                    <!-- Retail Mode Payment Method Buttons -->
                    <div class="mb-2">
                        <label class="form-label fs-11 fw-semibold text-slate-700 mb-1">Payment Method</label>
                        <div class="row g-1">
                            <div class="col-3">
                                <input type="radio" class="btn-check" name="payment_mode" id="payCash" value="CASH" checked>
                                <label class="btn btn-sm btn-outline-secondary w-100 py-1 fs-11 fw-bold" for="payCash">💵
                                    Cash</label>
                            </div>
                            <div class="col-3">
                                <input type="radio" class="btn-check" name="payment_mode" id="payUpi" value="UPI">
                                <label class="btn btn-sm btn-outline-secondary w-100 py-1 fs-11 fw-bold" for="payUpi">📱
                                    UPI</label>
                            </div>
                            <div class="col-3">
                                <input type="radio" class="btn-check" name="payment_mode" id="payCard" value="CARD">
                                <label class="btn btn-sm btn-outline-secondary w-100 py-1 fs-11 fw-bold" for="payCard">💳
                                    Card</label>
                            </div>
                            <div class="col-3">
                                <input type="radio" class="btn-check" name="payment_mode" id="payWallet" value="WALLET">
                                <label class="btn btn-sm btn-outline-secondary w-100 py-1 fs-11 fw-bold" for="payWallet">👛
                                    Wallet</label>
                            </div>
                        </div>
                    </div>

                    <!-- Complete Sale Button -->
                    <button type="button"
                        class="btn btn-primary w-100 py-2 fs-14 fw-bold rounded-3 shadow-sm d-flex align-items-center justify-content-center gap-1.5"
                        id="btnCheckout" onclick="processCheckout()">
                        <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-18"></iconify-icon>
                        Complete Checkout & Print
                    </button>
                @endif
            </div>

        </div>

    </div>

    <!-- Modal: Add New Customer (Retail Only) -->
    <div class="modal fade" id="addCustomerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-primary text-white py-2.5">
                    <h5 class="modal-title fw-bold text-white fs-15">Add New Customer</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="addCustomerForm" onsubmit="submitNewCustomer(event)">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-slate-700 fs-13 required">Mobile Number</label>
                            <input type="text" id="newCustMobile" name="mobile" class="form-control form-control-sm"
                                placeholder="10-digit mobile number" maxlength="10" required
                                oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-slate-700 fs-13 required">Customer Name</label>
                            <input type="text" id="newCustName" name="name" class="form-control form-control-sm"
                                placeholder="Full name" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-slate-700 fs-13">GSTIN / Tax ID (Optional)</label>
                            <input type="text" id="newCustGst" name="gst_number"
                                class="form-control form-control-sm text-uppercase"
                                placeholder="15-digit GSTIN (e.g. 07AAAAA0000A1Z5)" minlength="15" maxlength="15"
                                oninput="this.value = this.value.toUpperCase().slice(0, 15)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-slate-700 fs-13">AVI Id (Optional)</label>
                            <input type="text" id="newCustAbhhyam" name="abhhyam_code" class="form-control form-control-sm text-uppercase"
                                placeholder="Enter AVI Id (e.g. ABH123456)">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-slate-700 fs-13">Email Address (Optional)</label>
                            <input type="email" id="newCustEmail" name="email" class="form-control form-control-sm"
                                placeholder="Email address">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-slate-700 fs-13">Address (Optional)</label>
                            <textarea id="newCustAddress" name="address" class="form-control form-control-sm" rows="2"
                                placeholder="Customer delivery address"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary" id="btnSaveCust">Save & Select
                            Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Printable Receipt -->
    <div class="modal fade" id="printableReceiptModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white py-2">
                    <h5 class="modal-title fw-bold text-white fs-14">POS Sale Invoice</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4" id="receiptContent">
                    <!-- Dynamic Receipt Body -->
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-sm btn-success" onclick="window.print()">
                        <iconify-icon icon="solar:printer-bold-duotone" class="align-middle me-1"></iconify-icon> Print
                        Invoice
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        const posMode = @json($mode);
        let cart = [];
        let selectedCustomer = null;
        let selectedAdcId = null;
        let selectedAdcWallet = 0;

        // Infinite Scroll State Variables
        let currentPage = 1;
        let hasMorePages = true;
        let isLoadingProducts = false;
        let currentSearchQuery = '';

        const container = document.getElementById('productsScrollContainer');
        const grid = document.getElementById('productsGrid');
        const loader = document.getElementById('infiniteScrollLoader');
        const emptyBox = document.getElementById('emptyStateBox');
        const countLbl = document.getElementById('lblProductCount');

        // Initial load
        document.addEventListener('DOMContentLoaded', function () {
            fetchProducts(true);

            container.addEventListener('scroll', function () {
                if (container.scrollTop + container.clientHeight >= container.scrollHeight - 100) {
                    if (hasMorePages && !isLoadingProducts) {
                        fetchProducts(false);
                    }
                }
            });

            let searchInput = document.getElementById('productSearchInput');
            let searchTimeout = null;

            searchInput.addEventListener('input', function () {
                clearTimeout(searchTimeout);
                currentSearchQuery = this.value.trim();
                searchTimeout = setTimeout(() => {
                    fetchProducts(true);
                }, 300);
            });

            // Barcode Scanner & Enter Key Listener
            searchInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.keyCode === 13) {
                    e.preventDefault();
                    clearTimeout(searchTimeout);
                    let val = this.value.trim();
                    if (val.length > 0) {
                        handleBarcodeOrSearchScan(val);
                    }
                }
            });

            // In Wholesale POS mode, auto-select first assigned Canteen (ADC) if available
            if (posMode === 'wholesale') {
                const adcSel = document.getElementById('adcSelect');
                if (adcSel && adcSel.options.length > 1 && !adcSel.value) {
                    adcSel.selectedIndex = 1;
                    onAdcSelected(adcSel);
                }
            }
        });

        // Handle Barcode Scan / Enter Search Execution
        function handleBarcodeOrSearchScan(queryVal) {
            const url = `{{ route('ddc.pos.products') }}?mode=${posMode}&page=1&search=${encodeURIComponent(queryVal)}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.status && data.items.length > 0) {
                        if (data.items.length === 1) {
                            // Exactly 1 matching product -> Auto Add to Cart & Reset Search
                            addToCart(data.items[0]);
                        } else {
                            // Multiple products match (different batches/items) -> Display all in grid for selection
                            grid.innerHTML = '';
                            emptyBox.classList.add('d-none');
                            renderProductCards(data.items);
                            Toastify({ text: `${data.items.length} products found for '${queryVal}'. Please select one to add.`, backgroundColor: "#3b82f6" }).showToast();
                        }
                    } else {
                        Toastify({ text: `No active/valid product found for '${queryVal}'`, backgroundColor: "#ef4444" }).showToast();
                    }
                })
                .catch(err => console.error("Scan search error:", err));
        }

        // Handle ADC Selection in Wholesale Mode
        function onAdcSelected(selectElem) {
            const option = selectElem.options[selectElem.selectedIndex];
            if (selectElem.value) {
                selectedAdcId = selectElem.value;
                selectedAdcWallet = parseFloat(option.getAttribute('data-wallet') || 0);

                document.getElementById('selectedAdcBox').classList.remove('d-none');
                document.getElementById('lblAdcCompany').innerText = option.getAttribute('data-company');
                document.getElementById('lblAdcCode').innerText = option.getAttribute('data-code');
                document.getElementById('lblAdcWallet').innerText = '₹' + selectedAdcWallet.toFixed(2);
            } else {
                selectedAdcId = null;
                selectedAdcWallet = 0;
                document.getElementById('selectedAdcBox').classList.add('d-none');
            }
        }

        // Fetch Products Batch from Server
        function fetchProducts(reset = false) {
            if (isLoadingProducts) return;

            if (reset) {
                currentPage = 1;
                hasMorePages = true;
                grid.innerHTML = '';
                emptyBox.classList.add('d-none');
            }

            if (!hasMorePages) return;

            isLoadingProducts = true;
            loader.classList.remove('d-none');

            const url = `{{ route('ddc.pos.products') }}?mode=${posMode}&page=${currentPage}&search=${encodeURIComponent(currentSearchQuery)}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    isLoadingProducts = false;
                    loader.classList.add('d-none');

                    if (data.status) {
                        countLbl.innerText = `${data.total} Products Available`;
                        hasMorePages = data.has_more;

                        if (data.items.length === 0 && currentPage === 1) {
                            emptyBox.classList.remove('d-none');
                        } else {
                            renderProductCards(data.items);
                            currentPage++;
                        }
                    }
                })
                .catch(err => {
                    isLoadingProducts = false;
                    loader.classList.add('d-none');
                    console.error("Error fetching products:", err);
                });
        }

        function renderProductCards(items) {
            items.forEach(inv => {
                let col = document.createElement('div');
                col.className = 'pos-card-wrapper';

                let imgHtml = inv.image_url
                    ? `<img src="${inv.image_url}" alt="${inv.product_name}" class="img-fluid max-h-100 object-fit-contain" style="max-height: 65px;">`
                    : `<div class="avatar-sm bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center">
                                    <iconify-icon icon="solar:box-bold-duotone" class="fs-22"></iconify-icon>
                               </div>`;

                col.innerHTML = `
                            <div class="card h-100 product-card rounded-3 overflow-hidden shadow-xs" onclick='addToCart(${JSON.stringify(inv)})'>
                                <div class="position-relative bg-light text-center p-2 border-bottom d-flex align-items-center justify-content-center" style="height: 85px;">
                                    ${imgHtml}
                                    <span class="position-absolute top-0 end-0 badge bg-dark opacity-75 m-1 fs-9">
                                        Stk: ${inv.quantity}
                                    </span>
                                </div>
                                <div class="card-body p-2 d-flex flex-column justify-content-between">
                                    <div>
                                        <div class="product-card-title" title="${inv.product_name}">
                                            ${inv.product_name}
                                        </div>
                                        <div class="text-muted fs-10 mb-0.5 d-flex justify-content-between">
                                            <span class="text-truncate" title="Code: ${inv.product_code || '—'}">Code: ${inv.product_code || '—'}</span>
                                        </div>
                                        <div class="text-muted fs-10 mb-1 d-flex justify-content-between align-items-center">
                                            <span class="text-truncate text-slate-600" title="Barcode: ${inv.barcode || '—'}">
                                                <iconify-icon icon="solar:barcode-bold-duotone" class="align-middle me-0.5 text-primary fs-12"></iconify-icon>${inv.barcode || '—'}
                                            </span>
                                            ${inv.batch_number ? `<span class="badge bg-light text-muted border py-0 px-1 fs-9">B: ${inv.batch_number}</span>` : ''}
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between border-top pt-1 mt-1">
                                        <div>
                                            <small class="text-decoration-line-through text-muted fs-10">₹${inv.mrp.toFixed(2)}</small>
                                            <div class="fs-12 fw-bold ${posMode === 'wholesale' ? 'text-warning-emphasis' : 'text-success'}">
                                                ₹${inv.price.toFixed(2)}
                                            </div>
                                        </div>
                                        <button type="button" class="btn btn-xs btn-primary px-2 py-0.5 fs-10 rounded-2">
                                            + Add
                                        </button>
                                    </div>
                                </div>
                            </div>`;
                grid.appendChild(col);
            });
        }

        function clearSearch() {
            document.getElementById('productSearchInput').value = '';
            currentSearchQuery = '';
            fetchProducts(true);
        }

        // Cart Logic
        function addToCart(inv) {
            if (inv.quantity <= 0) {
                Toastify({ text: "Product '" + inv.product_name + "' is out of stock!", backgroundColor: "#ef4444" }).showToast();
                return;
            }

            if (inv.expire_date) {
                let expDate = new Date(inv.expire_date + 'T00:00:00');
                let today = new Date();
                today.setHours(0, 0, 0, 0);
                if (expDate < today) {
                    Toastify({ text: "Product '" + inv.product_name + "' has EXPIRED (" + inv.expire_date + ") and cannot be sold!", backgroundColor: "#ef4444" }).showToast();
                    return;
                }
            }

            let existing = cart.find(item => item.id === inv.id);
            if (existing) {
                if (existing.qty < inv.quantity) {
                    existing.qty++;
                    Toastify({ text: `Updated Qty (${existing.qty}): ` + inv.product_name, backgroundColor: "#10b981", duration: 1500 }).showToast();
                } else {
                    Toastify({ text: "Cannot exceed available stock (" + inv.quantity + ")", backgroundColor: "#ef4444" }).showToast();
                    return;
                }
            } else {
                cart.push({
                    id: inv.id,
                    name: inv.product_name,
                    code: inv.product_code,
                    barcode: inv.barcode,
                    price: parseFloat(inv.price),
                    mrp: parseFloat(inv.mrp),
                    gst_rate: parseFloat(inv.gst_rate || 0),
                    qty: 1,
                    stock: parseFloat(inv.quantity),
                    uom: inv.unit_of_measure || 'Unit',
                    batch: inv.batch_number,
                    expire_date: inv.expire_date
                });
                Toastify({ text: "Added to cart: " + inv.product_name, backgroundColor: "#10b981", duration: 1500 }).showToast();
            }
            renderCart();

            // Auto reset search filter & reload all products grid with infinite scroll intact
            if (currentSearchQuery.length > 0 || document.getElementById('productSearchInput').value.trim().length > 0) {
                clearSearch();
            }

            // Auto focus Qty input box for bulk entry in Wholesale POS mode
            if (posMode === 'wholesale') {
                setTimeout(() => {
                    let inputEl = document.querySelector(`.qty-input[data-id="${inv.id}"]`);
                    if (inputEl) {
                        inputEl.focus();
                        inputEl.select();
                    }
                }, 60);
            }
        }

        function updateQty(id, delta) {
            let item = cart.find(i => i.id === id);
            if (!item) return;
            let newQty = parseFloat((item.qty + delta).toFixed(3));
            if (newQty <= 0) {
                cart = cart.filter(i => i.id !== id);
            } else if (newQty > item.stock) {
                Toastify({ text: "Cannot exceed available stock (" + item.stock + ")", backgroundColor: "#ef4444" }).showToast();
                return;
            } else {
                item.qty = newQty;
            }
            renderCart();
        }

        function setCartQty(id, val) {
            let item = cart.find(i => i.id === id);
            if (!item) return;
            let newQty = parseFloat(val);
            if (isNaN(newQty) || newQty <= 0) {
                cart = cart.filter(i => i.id !== id);
            } else if (newQty > item.stock) {
                item.qty = item.stock;
                Toastify({ text: "Cannot exceed available stock (" + item.stock + ")", backgroundColor: "#ef4444" }).showToast();
            } else {
                item.qty = newQty;
            }
            renderCart();
        }

        function removeFromCart(id) {
            cart = cart.filter(i => i.id !== id);
            renderCart();
        }

        function clearCart() {
            cart = [];
            renderCart();
        }

        function renderCart() {
            let tbody = document.getElementById('cartTableBody');
            if (cart.length === 0) {
                tbody.innerHTML = `
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted fs-12">
                                    <div class="d-flex flex-column align-items-center justify-content-center py-4">
                                        <iconify-icon icon="solar:cart-large-minimalistic-broken" class="fs-48 text-muted mb-2"></iconify-icon>
                                        <h6 class="fw-semibold text-slate-700 mb-1">Your Cart is Empty</h6>
                                        <p class="text-muted fs-12 mb-0">Click products on the left catalog to add items.</p>
                                    </div>
                                </td>
                            </tr>`;
                recalculateTotals();
                return;
            }

            let html = '';
            cart.forEach((item, idx) => {
                let lineTotal = item.price * item.qty;
                let discountPerItem = Math.max(0, item.mrp - item.price);
                let discountPercent = (item.mrp > 0 && discountPerItem > 0) ? Math.round((discountPerItem / item.mrp) * 100) : 0;

                html += `
                            <tr>
                                <td class="text-center fw-bold text-muted">${idx + 1}</td>
                                <td>
                                    <div class="fw-bold text-slate-800 fs-11 lh-sm text-wrap text-break" title="${item.name}">
                                        ${item.name}
                                        <span class="badge bg-secondary-subtle text-secondary fs-10 ms-1">${item.uom || ''}</span>
                                    </div>
                                    <div class="text-muted fs-10 mt-0.5">${item.code || ''}</div>
                                </td>
                                <td class="text-end text-muted fs-11 text-nowrap">₹${item.mrp.toFixed(2)}</td>
                                <td class="text-end text-nowrap">
                                    <div class="fw-bold text-slate-800 fs-11">₹${item.price.toFixed(2)}</div>
                                    ${discountPerItem > 0 ? `<div class="text-success fs-10 fw-semibold" title="Discount per unit">Disc: ₹${discountPerItem.toFixed(2)} (${discountPercent}%)</div>` : ''}
                                </td>
                                <td class="text-center fw-semibold text-slate-700 fs-11">${item.stock} ${item.uom || ''}</td>
                                <td class="text-center">
                                    <div class="qty-control">
                                        <button type="button" class="qty-btn" onclick="updateQty(${item.id}, -1)">-</button>
                                        <input type="number" step="any" min="0.001" max="${item.stock}" value="${item.qty}" data-id="${item.id}"
                                               class="qty-input"
                                               onchange="setCartQty(${item.id}, this.value)"
                                               onfocus="this.select()"
                                               onkeydown="if(event.key === 'Enter') { this.blur(); }">
                                        <button type="button" class="qty-btn" onclick="updateQty(${item.id}, 1)">+</button>
                                    </div>
                                </td>
                                <td class="text-end fw-bold text-slate-800 fs-11 text-nowrap pe-1">
                                    ₹${lineTotal.toFixed(2)}
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-xs text-danger p-0" onclick="removeFromCart(${item.id})">
                                        <iconify-icon icon="solar:trash-bin-trash-bold" class="fs-15"></iconify-icon>
                                    </button>
                                </td>
                            </tr>`;
            });
            tbody.innerHTML = html;
            recalculateTotals();
        }

        function recalculateTotals() {
            let subtotal = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);

            // Auto calculate total MRP discount (MRP - Sale Price)
            let autoDiscount = cart.reduce((acc, item) => {
                let diff = Math.max(0, item.mrp - item.price);
                return acc + (diff * item.qty);
            }, 0);

            // Included GST / Tax extracted from tax-inclusive selling price:
            // Tax Included = lineTotal - (lineTotal / (1 + (gstRate / 100)))
            let autoTax = cart.reduce((acc, item) => {
                let lineTotal = item.price * item.qty;
                let gstRate = item.gst_rate || 0;
                if (gstRate <= 0) return acc;
                let taxIncluded = lineTotal - (lineTotal / (1 + (gstRate / 100)));
                return acc + taxIncluded;
            }, 0);

            // Grand Total equals subtotal because Tax is already INCLUDED in Selling Price
            let grandTotal = subtotal;

            document.getElementById('inputDiscount').value = autoDiscount.toFixed(2);
            document.getElementById('inputTax').value = autoTax.toFixed(2);
            document.getElementById('lblSubtotal').innerText = '₹' + subtotal.toFixed(2);
            document.getElementById('lblGrandTotal').innerText = '₹' + grandTotal.toFixed(2);
        }

        // Customer Lookup (Retail)
        function searchCustomerByMobile() {
            let mobile = document.getElementById('custMobileSearch').value.trim();
            if (mobile.length !== 10) {
                Toastify({ text: "Please enter a valid 10-digit mobile number.", backgroundColor: "#f59e0b" }).showToast();
                return;
            }

            fetch(`{{ route('ddc.pos.customers.search') }}?mobile=${mobile}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status && data.found) {
                        selectCustomer(data.customer);
                        Toastify({ text: "Customer found: " + data.customer.name, backgroundColor: "#10b981" }).showToast();
                    } else {
                        document.getElementById('newCustMobile').value = mobile;
                        var modal = new bootstrap.Modal(document.getElementById('addCustomerModal'));
                        modal.show();
                        Toastify({ text: "Customer not registered. Fill details to register.", backgroundColor: "#3b82f6" }).showToast();
                    }
                });
        }

        function selectCustomer(cust) {
            selectedCustomer = cust;
            document.getElementById('custSearchGroup').classList.add('d-none');
            document.getElementById('selectedCustBox').classList.remove('d-none');
            document.getElementById('lblCustName').innerText = cust.name + (cust.abhhyam_code ? (" (★ AVI Id: " + cust.abhhyam_code + ")") : "");
            document.getElementById('lblCustMobile').innerText = "+91 " + cust.mobile + (cust.abhhyam_code ? (" | AVI Id: " + cust.abhhyam_code) : "");
        }

        function resetCustomerSelection() {
            selectedCustomer = null;
            document.getElementById('custSearchGroup').classList.remove('d-none');
            document.getElementById('selectedCustBox').classList.add('d-none');
            document.getElementById('custMobileSearch').value = '';
        }

        function submitNewCustomer(e) {
            e.preventDefault();
            let gstVal = (document.getElementById('newCustGst')?.value || '').trim();
            if (gstVal.length > 0 && gstVal.length !== 15) {
                Toastify({ text: "GSTIN must be exactly 15 characters long.", backgroundColor: "#ef4444" }).showToast();
                return;
            }

            let form = document.getElementById('addCustomerForm');
            let btn = document.getElementById('btnSaveCust') || form.querySelector('button[type="submit"]');
            let originalText = btn ? btn.innerHTML : 'Save & Select Customer';

            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...';
            }

            let formData = new FormData(form);

            fetch("{{ route('ddc.pos.customers.store') }}", {
                method: "POST",
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
                .then(res => res.json())
                .then(data => {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                    if (data.status) {
                        bootstrap.Modal.getInstance(document.getElementById('addCustomerModal')).hide();
                        selectCustomer(data.customer);
                        Toastify({ text: "New Customer registered & selected!", backgroundColor: "#10b981" }).showToast();
                        form.reset();
                    } else if (data.already_exists) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Customer Already Added!',
                            text: data.message || `Customer with this phone number is already added.`,
                            showCancelButton: true,
                            confirmButtonText: 'Select Existing Customer',
                            cancelButtonText: 'Cancel',
                            confirmButtonColor: '#0284c7'
                        }).then((result) => {
                            if (result.isConfirmed && data.customer) {
                                let modalEl = document.getElementById('addCustomerModal');
                                if (modalEl && bootstrap.Modal.getInstance(modalEl)) {
                                    bootstrap.Modal.getInstance(modalEl).hide();
                                }
                                selectCustomer(data.customer);
                                Toastify({ text: "Existing Customer selected!", backgroundColor: "#0284c7" }).showToast();
                                form.reset();
                            }
                        });
                    } else {
                        Toastify({ text: data.message || "Failed to save customer.", backgroundColor: "#ef4444" }).showToast();
                    }
                })
                .catch(err => {
                    if (btn) {
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                    Toastify({ text: "An error occurred while saving customer.", backgroundColor: "#ef4444" }).showToast();
                });
        }

        // Checkout
        function processCheckout() {
            if (cart.length === 0) {
                Toastify({ text: "Your cart is empty!", backgroundColor: "#ef4444" }).showToast();
                return;
            }

            let discount = parseFloat(document.getElementById('inputDiscount').value) || 0;
            let tax = parseFloat(document.getElementById('inputTax').value) || 0;
            let subtotal = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
            let grandTotal = Math.max(0, subtotal - discount);

            let payMode = 'CASH';
            let adcUserId = null;

            if (posMode === 'wholesale') {
                if (!selectedAdcId) {
                    Swal.fire('Select Canteen', 'Please select an assigned Canteen (ADC) to proceed.', 'warning');
                    return;
                }

                if (selectedAdcWallet < grandTotal) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Insufficient Canteen Wallet',
                        html: `Canteen Wallet Balance: <strong>₹${selectedAdcWallet.toFixed(2)}</strong><br>Required Grand Total: <strong>₹${grandTotal.toFixed(2)}</strong><br><br><span class="text-danger">Please ask the Canteen to top-up their wallet.</span>`,
                    });
                    return;
                }

                adcUserId = selectedAdcId;
                payMode = 'WALLET';
            } else {
                payMode = document.querySelector('input[name="payment_mode"]:checked')?.value || 'CASH';
            }

            let payload = {
                mode: posMode,
                payment_mode: payMode,
                adc_user_id: adcUserId,
                discount_amount: discount,
                tax_amount: tax,
                customer_id: selectedCustomer ? selectedCustomer.id : null,
                customer_name: selectedCustomer ? selectedCustomer.name : (document.getElementById('custMobileSearch')?.value || 'Walk-in Customer'),
                customer_mobile: selectedCustomer ? selectedCustomer.mobile : document.getElementById('custMobileSearch')?.value,
                items: cart.map(i => ({ id: i.id, qty: i.qty }))
            };

            let btn = document.getElementById('btnCheckout');
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Processing...`;

            fetch("{{ route('ddc.pos.checkout') }}", {
                method: "POST",
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            })
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = posMode === 'wholesale'
                        ? `<iconify-icon icon="solar:check-circle-bold-duotone" class="fs-18"></iconify-icon> Process Order & Deduct Wallet`
                        : `<iconify-icon icon="solar:check-circle-bold-duotone" class="fs-18"></iconify-icon> Complete Checkout & Print`;

                    if (data.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Order Successful!',
                            text: data.message,
                            timer: 2000,
                            showConfirmButton: false
                        });
                        showReceiptModal(data.order_id);
                        clearCart();
                        if (posMode === 'retail') resetCustomerSelection();
                        fetchProducts(true);
                    } else {
                        Swal.fire('Checkout Failed', data.message, 'error');
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = posMode === 'wholesale'
                        ? `<iconify-icon icon="solar:check-circle-bold-duotone" class="fs-18"></iconify-icon> Process Order & Deduct Wallet`
                        : `<iconify-icon icon="solar:check-circle-bold-duotone" class="fs-18"></iconify-icon> Complete Checkout & Print`;
                    Swal.fire('Error', 'An error occurred during checkout.', 'error');
                });
        }

        function showReceiptModal(orderId) {
            let orderUrl = "{{ route('ddc.pos.orders.show', ['id' => 999999]) }}".replace('999999', orderId);
            fetch(orderUrl)
                .then(res => res.json())
                .then(data => {
                    if (data.status) {
                        let order = data.order;
                        let isWholesale = order.mode === 'wholesale';
                        let adcProfile = order.adc_user ? order.adc_user.adc_profile : null;

                        let itemsHtml = order.items.map((it, idx) => `
                                    <tr>
                                        <td class="text-center">${idx + 1}</td>
                                        <td>
                                            <strong>${it.product_name}</strong>
                                            <div class="text-muted fs-10">${it.product_code || ''}</div>
                                        </td>
                                        <td class="text-end">₹${parseFloat(it.mrp || 0).toFixed(2)}</td>
                                        <td class="text-center">${it.quantity} ${it.unit_of_measure || ''}</td>
                                        <td class="text-end">₹${parseFloat(it.unit_price).toFixed(2)}</td>
                                        <td class="text-end pe-2">₹${parseFloat(it.total_price).toFixed(2)}</td>
                                    </tr>
                                `).join('');

                        let buyerTitle = isWholesale ? 'BUYER CANTEEN (ADC)' : 'CUSTOMER DETAILS';
                        let buyerName = isWholesale ? (adcProfile?.company_name || order.customer_name) : (order.customer_name || 'Walk-in Customer');
                        let buyerSub = isWholesale ? `Canteen Code / ID: <strong>${adcProfile?.adc_code || 'N/A'}</strong>` : `Mobile: ${order.customer_mobile || 'N/A'}`;

                        let html = `
                                    <div class="text-center mb-3 border-bottom pb-3">
                                        <h4 class="fw-bold mb-1">${order.ddc ? order.ddc.name : 'DDC STORE'}</h4>
                                        <div class="text-muted fs-12">GSTIN: <strong>${order.ddc && order.ddc.ddc_profile ? (order.ddc.ddc_profile.gst_number || '—') : '—'}</strong></div>
                                        <div class="badge bg-dark text-white px-3 py-1 mt-1 fs-11 text-uppercase">${isWholesale ? 'WHOLESALE B2B TAX INVOICE' : 'RETAIL TAX INVOICE'}</div>
                                    </div>
                                    <div class="row fs-12 mb-3 g-2">
                                        <div class="col-6">
                                            <div class="p-2 bg-light rounded border">
                                                <div class="text-muted fs-10 text-uppercase fw-bold mb-1">INVOICE META</div>
                                                <div>Invoice No: <strong>${order.invoice ? order.invoice.invoice_number : order.order_no}</strong></div>
                                                <div>Order No: <code>${order.order_no}</code></div>
                                                <div>Date: ${new Date(order.created_at).toLocaleString()}</div>
                                                <div>Mode: <strong>${order.mode.toUpperCase()}</strong></div>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <div class="p-2 bg-light rounded border">
                                                <div class="text-muted fs-10 text-uppercase fw-bold mb-1">${buyerTitle}</div>
                                                <div class="fw-bold text-slate-900">${buyerName}</div>
                                                <div>${buyerSub}</div>
                                                <div>Payment: <span class="badge ${isWholesale ? 'bg-success' : 'bg-primary'}">${order.payment_mode}</span></div>
                                            </div>
                                        </div>
                                    </div>
                                    <table class="table table-sm table-bordered fs-12 mb-3">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="width: 5%;" class="text-center">#</th>
                                                <th style="width: 35%;">Product</th>
                                                <th style="width: 15%;" class="text-end">MRP</th>
                                                <th style="width: 10%;" class="text-center">Qty</th>
                                                <th style="width: 15%;" class="text-end">${isWholesale ? 'MSP' : 'Rate'}</th>
                                                <th style="width: 20%;" class="text-end pe-2">Amount</th>
                                            </tr>
                                        </thead>
                                        <tbody>${itemsHtml}</tbody>
                                    </table>
                                    <div class="row fs-13 border-top pt-2 align-items-center">
                                        <div class="col-6 text-muted fs-11">
                                            ${isWholesale ? '* Payment deducted from ADC Canteen Wallet.' : 'Thank you for your business!'}
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex justify-content-between"><span>Subtotal:</span> <strong>₹${parseFloat(order.subtotal).toFixed(2)}</strong></div>
                                            ${order.discount_amount > 0 ? `<div class="d-flex justify-content-between text-danger"><span>Discount:</span> <strong>-₹${parseFloat(order.discount_amount).toFixed(2)}</strong></div>` : ''}
                                            ${order.tax_amount > 0 ? `<div class="d-flex justify-content-between"><span>Tax:</span> <strong>+₹${parseFloat(order.tax_amount).toFixed(2)}</strong></div>` : ''}
                                            <div class="d-flex justify-content-between border-top pt-1 mt-1 fs-15 fw-bold text-success"><span>Grand Total:</span> <strong>₹${parseFloat(order.total_amount).toFixed(2)}</strong></div>
                                        </div>
                                    </div>`;

                        document.getElementById('receiptContent').innerHTML = html;
                        let modal = new bootstrap.Modal(document.getElementById('printableReceiptModal'));
                        modal.show();
                    }
                });
        }
        window.openReceiptModal = showReceiptModal;
    </script>
@endpush