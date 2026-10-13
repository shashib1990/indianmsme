@extends('layouts.pos')

@section('title', 'ADC Retail POS Terminal')

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
            width: 100%;
        }

        .cart-table th {
            font-size: 11px !important;
            font-weight: 700 !important;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 7px 5px !important;
        }

        .cart-table td {
            font-size: 12px !important;
            padding: 7px 5px !important;
            vertical-align: middle;
            word-break: break-word;
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
                        <span class="badge bg-success text-white px-2.5 py-1 fs-12 fw-bold">
                            ADC RETAIL POS MODE
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
                    <p class="text-muted fs-12 mb-0">No matching products in your ADC stock inventory.</p>
                </div>
            </div>

        </div>

        <!-- RIGHT PANE: Customer Lookup + Cart + Checkout Controls -->
        <div class="pos-right-pane">

            <div class="d-flex align-items-center justify-content-between mb-1.5 pb-1.5 border-bottom">
                <h6 class="fw-bold text-slate-800 mb-0 fs-13 d-flex align-items-center gap-1">
                    <iconify-icon icon="solar:cart-bold-duotone" class="text-primary fs-16"></iconify-icon>
                    Current Order
                </h6>
                <div class="d-flex align-items-center gap-1">
                    <button type="button"
                        class="btn btn-xs btn-warning text-dark fw-bold d-flex align-items-center gap-0.5 shadow-sm py-0.5 px-1.5 fs-10"
                        onclick="openHeldCartsModal()" id="btnHeldCartsTitle" title="View held bills">
                        <iconify-icon icon="solar:pause-circle-bold-duotone" class="fs-12"></iconify-icon>
                        Held (<span id="heldCartCount">0</span>)
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-warning fw-bold d-flex align-items-center gap-0.5 py-0.5 px-1.5 fs-10"
                        onclick="holdCurrentBill()" title="Hold current cart">
                        <iconify-icon icon="solar:pause-bold" class="fs-11"></iconify-icon> Hold
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-danger py-0.5 px-1.5 fs-10" onclick="clearCart()">Clear</button>
                </div>
            </div>

            <!-- Retail Mode: Customer Search / Selection -->
            <div class="mb-1.5 p-2 rounded-2 bg-light border">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label text-slate-700 fw-semibold fs-11 mb-0">Customer Details</label>
                    <button type="button" class="btn btn-link p-0 text-primary fs-10 text-decoration-none fw-bold"
                        data-bs-toggle="modal" data-bs-target="#addCustomerModal">
                        + Add New Customer
                    </button>
                </div>

                <!-- Mobile Lookup Input -->
                <div class="input-group input-group-sm mb-0" id="custSearchGroup">
                    <input type="text" id="custMobileSearch" class="form-control py-0.5 fs-11"
                        placeholder="Enter Mobile Number (10 digits)..." maxlength="10"
                        oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <button class="btn btn-primary py-0.5 fs-11" type="button" id="btnSearchCust" onclick="searchCustomerByMobile()">
                        Search
                    </button>
                </div>

                <!-- Customer Selected Display Box -->
                <div id="selectedCustBox" class="d-none bg-white p-1.5 rounded border border-success">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <div class="fw-bold text-slate-800 fs-11" id="lblCustName">Customer Name</div>
                            <div class="text-muted fs-10" id="lblCustMobile">+91 XXXXXXXXXX</div>
                        </div>
                        <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1.5 fs-10"
                            onclick="resetCustomerSelection()">Change</button>
                    </div>
                </div>
            </div>

            <!-- Cart Items Scrollable Table -->
            <div class="cart-items-scroll mb-1.5">
                <table class="table table-sm align-middle cart-table mb-0" id="cartTable">
                    <thead class="table-light sticky-top">
                        <tr class="fs-10 text-muted text-uppercase">
                            <th style="width: 48%;">Item Details</th>
                            <th class="text-center" style="width: 27%;">Qty</th>
                            <th class="text-end" style="width: 20%;">Total</th>
                            <th class="text-center" style="width: 5%;"></th>
                        </tr>
                    </thead>
                    <tbody id="cartTableBody">
                        <tr>
                            <td colspan="4" class="text-center py-4 text-muted fs-12" id="emptyCartRow">
                                <div class="d-flex flex-column align-items-center justify-content-center py-3">
                                    <iconify-icon icon="solar:cart-large-minimalistic-broken"
                                        class="fs-36 text-muted mb-1.5"></iconify-icon>
                                    <h6 class="fw-semibold text-slate-700 mb-0.5 fs-12">Your Cart is Empty</h6>
                                    <p class="text-muted fs-11 mb-0">Click products on left catalog to add items.</p>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="pos-right-footer">
                <div class="d-flex justify-content-between fs-12 mb-1.5 px-0.5">
                    <span class="text-muted">Sub Total: <strong class="text-slate-800 fs-13" id="lblSubtotal">₹0.00</strong></span>
                    <span class="text-muted">Taxable: <strong class="text-slate-800 fs-13" id="lblTaxable">₹0.00</strong></span>
                </div>

                <div class="row g-1.5 my-1.5">
                    <div class="col-4">
                        <label class="form-label fs-10 fw-semibold text-muted mb-0.5">Discount (₹)</label>
                        <input type="text" id="inputDiscount"
                            class="form-control form-control-sm bg-light fw-bold text-success px-2 py-1 fs-12" value="0.00" readonly
                            title="Auto-calculated MRP Savings">
                    </div>
                    <div class="col-4">
                        <label class="form-label fs-10 fw-bold text-primary mb-0.5">Special Disc (₹)</label>
                        <input type="number" id="inputSpecialDiscount" min="0" step="any"
                            class="form-control form-control-sm bg-white fw-bold text-primary border-primary-subtle px-2 py-1 fs-12"
                            value="0.00" oninput="recalculateTotals()" onfocus="this.select()" placeholder="0.00">
                    </div>
                    <div class="col-4">
                        <label class="form-label fs-10 fw-semibold text-muted mb-0.5">Tax/GST (₹)</label>
                        <input type="text" id="inputTax"
                            class="form-control form-control-sm bg-light fw-bold text-slate-800 px-2 py-1 fs-12" value="0.00" readonly>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center bg-primary-subtle px-3 py-2 rounded-2 border border-primary-subtle my-2">
                    <span class="fw-bold text-slate-800 fs-13">Grand Total:</span>
                    <span class="fw-bold text-primary fs-18" id="lblGrandTotal">₹0.00</span>
                </div>

                <!-- Retail Mode Payment Method Buttons -->
                <div class="mb-2">
                    <label class="form-label fs-11 fw-semibold text-slate-700 mb-1">Payment Method</label>
                    <div class="row g-1.5 text-center">
                        <div class="col-3">
                            <input type="radio" class="btn-check" name="payment_mode" id="payCash" value="CASH" checked>
                            <label class="btn btn-sm btn-outline-secondary w-100 py-1.5 fs-12 fw-bold" for="payCash">💵 Cash</label>
                        </div>
                        <div class="col-3">
                            <input type="radio" class="btn-check" name="payment_mode" id="payCard" value="CARD">
                            <label class="btn btn-sm btn-outline-secondary w-100 py-1.5 fs-12 fw-bold" for="payCard">💳 Card</label>
                        </div>
                        <div class="col-3">
                            <input type="radio" class="btn-check" name="payment_mode" id="payUpi" value="UPI">
                            <label class="btn btn-sm btn-outline-secondary w-100 py-1.5 fs-12 fw-bold" for="payUpi">📱 UPI</label>
                        </div>
                        <div class="col-3">
                            <input type="radio" class="btn-check" name="payment_mode" id="payCredit" value="CREDIT">
                            <label class="btn btn-sm btn-outline-danger w-100 py-1.5 fs-12 fw-bold" for="payCredit">📋 Credit</label>
                        </div>
                    </div>
                </div>

                @php
                    $posAdcUser = Auth::guard('adc')->user();
                    $posAdcProfile = $posAdcUser?->adcProfile;
                    $posWalletBal = (float) ($posAdcProfile?->wallet_balance ?? 0);
                    $posIsDeactivated = ($posAdcUser?->is_active == 0 || $posAdcUser?->is_active === '0' || ($posAdcProfile?->first_sale_at && $posWalletBal <= 0));
                @endphp

                @if($posIsDeactivated)
                    <div class="alert alert-danger p-2 mb-2 fs-12 rounded-2 border-0 text-start"
                        style="background: #fef2f2; color: #991b1b; border-left: 3px solid #ef4444 !important;">
                        <iconify-icon icon="solar:close-circle-bold-duotone"
                            class="text-danger me-1 align-middle fs-16"></iconify-icon>
                        <strong>Account Deactivated:</strong>
                        {{ $posAdcProfile?->deactivation_reason ?: 'Wallet balance exhausted. Daily platform fee deduction failed. Recharge wallet to resume sales.' }}
                    </div>
                    <button type="button"
                        class="btn btn-secondary w-100 py-2 fs-14 fw-bold rounded-2 shadow-sm d-flex align-items-center justify-content-center gap-2"
                        disabled>
                        <iconify-icon icon="solar:forbidden-circle-bold-duotone" class="fs-18"></iconify-icon>
                        Sales Disabled (Recharge Required)
                    </button>
                @else
                    <!-- Complete Sale Button -->
                    <button type="button"
                        class="btn btn-primary w-100 py-2 fs-14 fw-bold rounded-2 shadow-sm d-flex align-items-center justify-content-center gap-2"
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

    <!-- Modal: Held Bills List -->
    <div class="modal fade" id="heldCartsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-warning text-dark py-2.5">
                    <h5 class="modal-title fw-bold fs-15 d-flex align-items-center gap-1.5 text-dark">
                        <iconify-icon icon="solar:pause-circle-bold-duotone" class="fs-20"></iconify-icon>
                        Pending Held Bills (Unbilled Carts)
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3" id="heldCartsModalBody">
                    <div class="mb-3">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0">
                                <iconify-icon icon="solar:magnifer-linear" class="fs-16 text-muted"></iconify-icon>
                            </span>
                            <input type="text" id="heldCartSearchInput" class="form-control border-start-0 ps-0"
                                placeholder="Search by Hold Bill No. (e.g. #HOLD-6 or 6), Customer Name, or Mobile..."
                                onkeyup="filterHeldCarts()">
                            <button class="btn btn-outline-secondary" type="button"
                                onclick="clearHeldCartSearch()">Clear</button>
                        </div>
                    </div>
                    <div id="heldCartsListTableContainer">
                        <div class="text-center py-4">
                            <div class="spinner-border text-warning spinner-border-sm me-1"></div>
                            <span class="fs-12 text-muted">Loading held bills...</span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
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
        const posMode = 'retail';
        let cart = [];
        let selectedCustomer = null;
        let currentResumedHeldId = null;

        function getShortUom(uom) {
            if (!uom) return '';
            let lower = uom.toLowerCase();
            if (lower.includes('bag')) return 'Bags';
            if (lower.includes('piece') || lower.includes('pcs')) return 'Pcs';
            if (lower.includes('liter') || lower.includes('ltr')) return 'Ltr';
            if (lower.includes('box')) return 'Boxes';
            if (lower.includes('packet') || lower.includes('pkt')) return 'Pkts';
            if (lower.includes('kg')) return 'Kg';
            return uom;
        }

        function isDecimalAllowed(uom) {
            if (!uom) return false;
            let lower = uom.toLowerCase().trim();
            const decimalUnits = ['kg', 'kilo', 'kilogram', 'gm', 'gram', 'gramme', 'liter', 'litre', 'ltr', 'ml', 'milliliter', 'gallon'];
            return decimalUnits.some(unit => lower.includes(unit));
        }

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
            loadSavedCart();

            container.addEventListener('scroll', function () {
                if (container.scrollTop + container.clientHeight >= container.scrollHeight - 250) {
                    if (hasMorePages && !isLoadingProducts) {
                        fetchProducts(false);
                    }
                }
            });

            // IntersectionObserver for reliable infinite scrolling
            if ('IntersectionObserver' in window && loader) {
                const scrollObserver = new IntersectionObserver((entries) => {
                    if (entries[0].isIntersecting && hasMorePages && !isLoadingProducts) {
                        fetchProducts(false);
                    }
                }, { root: container, threshold: 0.05 });
                scrollObserver.observe(loader);
            }

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
        });

        // Handle Barcode Scan / Enter Search Execution
        function handleBarcodeOrSearchScan(queryVal) {
            const url = `{{ route('adc.pos.products') }}?mode=${posMode}&page=1&search=${encodeURIComponent(queryVal)}`;

            fetch(url)
                .then(res => res.json())
                .then(data => {
                    if (data.status && data.items.length > 0) {
                        if (data.items.length === 1) {
                            addToCart(data.items[0]);
                        } else {
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

            const url = `{{ route('adc.pos.products') }}?mode=${posMode}&page=${currentPage}&search=${encodeURIComponent(currentSearchQuery)}`;

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

                            // Auto-load next page if screen has remaining space and more items exist
                            if (hasMorePages && !isLoadingProducts && container.scrollHeight <= container.clientHeight + 100) {
                                setTimeout(() => fetchProducts(false), 100);
                            }
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
                                                Barcode: ${inv.barcode || '—'}
                                            </span>
                                            ${inv.batch_number ? `<span class="badge bg-light text-muted border py-0 px-1 fs-9">B: ${inv.batch_number}</span>` : ''}
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center justify-content-between border-top pt-1 mt-1">
                                        <div>
                                            <small class="text-decoration-line-through text-muted fs-10">₹${inv.mrp.toFixed(2)}</small>
                                            <div class="fs-12 fw-bold text-success">
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
                    product_name: inv.product_name,
                    code: inv.product_code,
                    product_code: inv.product_code,
                    barcode: inv.barcode,
                    price: parseFloat(inv.price),
                    mrp: parseFloat(inv.mrp),
                    gst_rate: parseFloat(inv.gst_rate || 0),
                    qty: 1,
                    stock: inv.quantity,
                    unit_of_measure: inv.unit_of_measure || 'Unit',
                    batch: inv.batch_number,
                    expire_date: inv.expire_date
                });
                Toastify({ text: "Added to cart: " + inv.product_name, backgroundColor: "#10b981", duration: 1500 }).showToast();
            }
            renderCart();

            if (currentSearchQuery.length > 0 || document.getElementById('productSearchInput').value.trim().length > 0) {
                clearSearch();
            }
        }

        function updateQty(id, delta) {
            let item = cart.find(i => i.id === id);
            if (!item) return;
            let newQty = item.qty + delta;
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

        let syncTimeout = null;
        function syncCartToDb() {
            clearTimeout(syncTimeout);
            syncTimeout = setTimeout(() => {
                let payload = {
                    mode: posMode,
                    items: cart.map(i => ({ id: i.id, qty: i.qty })),
                    customer_id: selectedCustomer ? selectedCustomer.id : null
                };
                fetch("{{ route('adc.pos.cart.sync') }}", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "X-CSRF-TOKEN": "{{ csrf_token() }}"
                    },
                    body: JSON.stringify(payload)
                }).catch(e => console.error("Cart sync error:", e));
            }, 300);
        }

        function loadSavedCart() {
            fetch(`{{ route('adc.pos.cart.get') }}?mode=${posMode}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status && data.items && data.items.length > 0) {
                        cart = data.items;
                        renderCart(false);
                    }
                    if (data.customer) {
                        selectCustomer(data.customer, false);
                    }
                })
                .catch(e => console.error("Load cart error:", e));
        }

        function removeFromCart(id) {
            cart = cart.filter(i => i.id !== id);
            renderCart();
        }

        function clearCart() {
            cart = [];
            currentResumedHeldId = null;
            let specDiscInput = document.getElementById('inputSpecialDiscount');
            if (specDiscInput) specDiscInput.value = '';
            renderCart(false);
            fetch("{{ route('adc.pos.cart.clear') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify({ mode: posMode })
            }).catch(e => console.error("Clear cart error:", e));
        }

        function renderCart(doSync = true) {
            let tbody = document.getElementById('cartTableBody');
            if (cart.length === 0) {
                tbody.innerHTML = `
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted fs-12">
                                    <div class="d-flex flex-column align-items-center justify-content-center py-3">
                                        <iconify-icon icon="solar:cart-large-minimalistic-broken" class="fs-36 text-muted mb-1.5"></iconify-icon>
                                        <h6 class="fw-semibold text-slate-700 mb-0.5 fs-12">Your Cart is Empty</h6>
                                        <p class="text-muted fs-11 mb-0">Click products on left catalog to add items.</p>
                                    </div>
                                </td>
                            </tr>`;
                recalculateTotals();
                if (doSync) syncCartToDb();
                return;
            }

            let html = '';
            cart.forEach((item, idx) => {
                let itemName = item.name || item.product_name || 'Product';
                let itemCode = item.code || item.product_code || '';
                let lineTotal = item.price * item.qty;
                let shortUom = getShortUom(item.unit_of_measure);
                let stepAttr = 'min="0.001" step="any"';
                let discountPerItem = Math.max(0, item.mrp - item.price);
                let discountPercent = (item.mrp > 0 && discountPerItem > 0) ? Math.round((discountPerItem / item.mrp) * 100) : 0;

                let priceSub = `₹${item.price.toFixed(2)}`;
                if (discountPerItem > 0) {
                    priceSub += ` <span class="text-decoration-line-through text-muted fs-10">₹${item.mrp.toFixed(2)}</span> <span class="text-success fs-10 fw-semibold">(-${discountPercent}%)</span>`;
                }

                html += `
                            <tr>
                                <td class="py-1">
                                    <div class="fw-bold text-slate-800 fs-11 text-wrap text-break" title="${itemName}">${itemName}</div>
                                    <div class="text-muted fs-10 mt-0.5 d-flex align-items-center gap-1 flex-wrap">
                                        <span class="fw-semibold text-slate-700">${priceSub}</span>
                                        <span class="badge bg-secondary-subtle text-dark border fs-9 px-1 py-0 rounded-1">Stk: ${item.stock} ${shortUom}</span>
                                    </div>
                                </td>
                                <td class="text-center py-1">
                                    <div class="d-flex align-items-center justify-content-center gap-0.5">
                                        <div class="qty-control">
                                            <button type="button" class="qty-btn" onclick="updateQty(${item.id}, -1)">-</button>
                                            <input type="number" ${stepAttr} max="${item.stock}" value="${item.qty}" data-id="${item.id}"
                                                   class="qty-input"
                                                   onchange="setCartQty(${item.id}, this.value)"
                                                   onfocus="this.select()"
                                                   onkeydown="if(event.key === 'Enter') { this.blur(); }">
                                            <button type="button" class="qty-btn" onclick="updateQty(${item.id}, 1)">+</button>
                                        </div>
                                        <span class="fw-semibold text-muted fs-10 ms-0.5">${shortUom}</span>
                                    </div>
                                </td>
                                <td class="text-end fw-bold text-slate-800 fs-11 text-nowrap py-1">
                                    ₹${lineTotal.toFixed(2)}
                                </td>
                                <td class="text-center py-1">
                                    <button type="button" class="btn btn-xs text-danger p-0" onclick="removeFromCart(${item.id})">
                                        <iconify-icon icon="solar:trash-bin-trash-bold" class="fs-15"></iconify-icon>
                                    </button>
                                </td>
                            </tr>`;
            });
            tbody.innerHTML = html;
            recalculateTotals();
            if (doSync) syncCartToDb();
        }

        function recalculateTotals() {
            let totalMrp = 0;
            let sellingSubtotal = 0;

            cart.forEach(item => {
                let itemMrpTotal = item.mrp * item.qty;
                let itemSubtotal = item.price * item.qty;

                totalMrp += itemMrpTotal;
                sellingSubtotal += itemSubtotal;
            });

            let mrpDiscount = Math.max(0, totalMrp - sellingSubtotal);
            document.getElementById('inputDiscount').value = mrpDiscount.toFixed(2);

            let specialDiscountInput = parseFloat(document.getElementById('inputSpecialDiscount').value) || 0;
            let specialDiscount = Math.min(specialDiscountInput, sellingSubtotal);

            // Deduct Special Discount from sellingSubtotal first before calculating Taxable Amount & GST
            let netSellingSubtotal = Math.max(0, sellingSubtotal - specialDiscount);
            let taxTotal = 0;
            let taxableTotal = 0;

            cart.forEach(item => {
                let itemSubtotal = item.price * item.qty;
                let itemDiscount = sellingSubtotal > 0 ? (specialDiscount * (itemSubtotal / sellingSubtotal)) : 0;
                let itemNetAmount = Math.max(0, itemSubtotal - itemDiscount);

                let gstRate = parseFloat(item.gst_rate) || 0;
                if (gstRate > 0) {
                    let itemTaxable = itemNetAmount / (1 + (gstRate / 100));
                    let itemTax = itemNetAmount - itemTaxable;
                    taxableTotal += itemTaxable;
                    taxTotal += itemTax;
                } else {
                    taxableTotal += itemNetAmount;
                }
            });

            let grandTotal = netSellingSubtotal;

            document.getElementById('lblSubtotal').innerText = '₹' + sellingSubtotal.toFixed(2);
            document.getElementById('inputTax').value = taxTotal.toFixed(2);
            if (document.getElementById('lblTaxable')) {
                document.getElementById('lblTaxable').innerText = '₹' + taxableTotal.toFixed(2);
            }
            document.getElementById('lblGrandTotal').innerText = '₹' + grandTotal.toFixed(2);
        }

        // Customer Lookup
        function searchCustomerByMobile() {
            let mobile = document.getElementById('custMobileSearch').value.trim();
            if (mobile.length !== 10) {
                Toastify({ text: "Please enter a valid 10-digit mobile number", backgroundColor: "#ef4444" }).showToast();
                return;
            }

            let btn = document.getElementById('btnSearchCust');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

            fetch(`{{ route('adc.pos.customers.search') }}?mobile=${mobile}`)
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = 'Search';

                    if (data.status) {
                        selectCustomer(data.customer);
                        Toastify({ text: "Customer Found & Selected!", backgroundColor: "#10b981" }).showToast();
                    } else {
                        Swal.fire({
                            title: 'Customer Not Found',
                            text: `No customer registered with ${mobile}. Would you like to register new customer?`,
                            icon: 'question',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, Add New',
                            cancelButtonText: 'Cancel'
                        }).then((result) => {
                            if (result.isConfirmed) {
                                document.getElementById('newCustMobile').value = mobile;
                                let modal = new bootstrap.Modal(document.getElementById('addCustomerModal'));
                                modal.show();
                            }
                        });
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerHTML = 'Search';
                });
        }

        function selectCustomer(cust, skipCheck = false) {
            selectedCustomer = cust;
            document.getElementById('custSearchGroup').classList.add('d-none');
            document.getElementById('selectedCustBox').classList.remove('d-none');
            document.getElementById('lblCustName').innerText = cust.name + (cust.abhhyam_code ? (" (★ AVI Id: " + cust.abhhyam_code + ")") : "");
            document.getElementById('lblCustMobile').innerText = '+91 ' + cust.mobile + (cust.abhhyam_code ? (' | AVI Id: ' + cust.abhhyam_code) : '') + (cust.gst_number ? ' | GST: ' + cust.gst_number : '');

            if (!skipCheck) {
                checkCustomerPendingHeldCart(cust);
            }
        }

        function resetCustomerSelection() {
            selectedCustomer = null;
            document.getElementById('selectedCustBox').classList.add('d-none');
            document.getElementById('custSearchGroup').classList.remove('d-none');
            document.getElementById('custMobileSearch').value = '';
        }

        function submitNewCustomer(e) {
            e.preventDefault();
            let form = document.getElementById('addCustomerForm');
            let btn = document.getElementById('btnSaveCust') || form.querySelector('button[type="submit"]');
            let originalText = btn ? btn.innerHTML : 'Save & Select Customer';

            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...';
            }

            let formData = new FormData(form);

            fetch("{{ route('adc.pos.customers.store') }}", {
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

            if (!selectedCustomer) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Customer Selection Required',
                    text: 'Please search & select or add a Customer before completing checkout.',
                    confirmButtonText: 'Select / Add Customer',
                    confirmButtonColor: '#3b82f6'
                }).then(() => {
                    let custInput = document.getElementById('custMobileSearch');
                    if (custInput) custInput.focus();
                });
                return;
            }

            let discount = parseFloat(document.getElementById('inputDiscount').value) || 0;
            let specialDiscount = parseFloat(document.getElementById('inputSpecialDiscount').value) || 0;
            let tax = parseFloat(document.getElementById('inputTax').value) || 0;
            let subtotal = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);

            let payMode = document.querySelector('input[name="payment_mode"]:checked')?.value || 'CASH';

            if (payMode === 'CREDIT' && (!selectedCustomer || !selectedCustomer.mobile)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Customer Required for Credit Sale',
                    text: 'Please select a customer to create credit.',
                    confirmButtonText: 'Select / Add Customer',
                    confirmButtonColor: '#ef4444'
                }).then(() => {
                    let custInput = document.getElementById('custMobileSearch');
                    if (custInput) custInput.focus();
                });
                return;
            }

            let btn = document.getElementById('btnCheckout');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

            let payload = {
                mode: posMode,
                items: cart.map(i => ({ inventory_id: i.id, qty: i.qty })),
                payment_mode: payMode,
                discount_amount: discount,
                special_discount_amount: specialDiscount,
                tax_amount: tax,
                customer_id: selectedCustomer ? selectedCustomer.id : null,
                customer_mobile: selectedCustomer ? selectedCustomer.mobile : null,
                customer_name: selectedCustomer ? selectedCustomer.name : null,
                customer_gst: selectedCustomer ? selectedCustomer.gst_number : null,
                held_id: currentResumedHeldId
            };

            fetch("{{ route('adc.pos.checkout') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify(payload)
            })
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = '<iconify-icon icon="solar:check-circle-bold-duotone" class="fs-18"></iconify-icon> Complete Checkout & Print';

                    if (data.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Sale Completed Successfully!',
                            text: `Invoice #${data.invoice_number} generated.`,
                            showCancelButton: true,
                            confirmButtonText: 'Print Receipt',
                            cancelButtonText: 'New Order'
                        }).then((res) => {
                            clearCart();
                            resetCustomerSelection();
                            fetchProducts(true);

                            if (res.isConfirmed) {
                                openReceiptWindow(data.order_id);
                            }
                        });
                    } else {
                        Swal.fire('Checkout Failed', data.message || 'Error processing POS order.', 'error');
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = '<iconify-icon icon="solar:check-circle-bold-duotone" class="fs-18"></iconify-icon> Complete Checkout & Print';
                    console.error("Checkout error:", err);
                    Swal.fire('Error', 'Server connection error.', 'error');
                });
        }

        function openReceiptWindow(orderId) {
            let printUrl = "{{ route('adc.pos.orders.print', ['id' => 999999]) }}".replace('999999', orderId);
            window.open(printUrl, '_blank');
        }

        // ── HELD BILLS / PENDING CARTS MANAGEMENT ──

        document.addEventListener("DOMContentLoaded", function () {
            fetchHeldCartsCount();
        });

        function fetchHeldCartsCount() {
            fetch("{{ route('adc.pos.held-carts.index') }}?mode=" + posMode)
                .then(res => res.json())
                .then(data => {
                    if (data.status && data.carts) {
                        document.getElementById('heldCartCount').innerText = data.carts.length;
                    }
                })
                .catch(err => console.error("Error fetching held carts count:", err));
        }

        function holdCurrentBill() {
            if (cart.length === 0) {
                Toastify({ text: "Cart is empty! Add products first.", backgroundColor: "#ef4444" }).showToast();
                return;
            }

            let custName = selectedCustomer ? selectedCustomer.name : '';
            let custMobile = selectedCustomer ? selectedCustomer.mobile : '';
            let custId = selectedCustomer ? selectedCustomer.id : null;

            if (!custMobile || !/^[0-9]{10}$/.test(custMobile)) {
                Swal.fire({
                    title: 'Hold Bill - Mobile Number Required',
                    html: '<div class="text-start fs-13 text-muted mb-2">Enter <strong>10-digit Customer Mobile Number</strong> to hold this bill:</div>',
                    input: 'tel',
                    inputPlaceholder: 'Enter 10-digit Mobile Number (e.g. 9876543210)',
                    inputAttributes: {
                        maxlength: 10,
                        pattern: '[0-9]*',
                        oninput: "this.value = this.value.replace(/[^0-9]/g, '').slice(0, 10)"
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Hold Bill',
                    confirmButtonColor: '#f59e0b',
                    cancelButtonText: 'Cancel',
                    inputValidator: (value) => {
                        let clean = (value || '').trim();
                        if (!clean) {
                            return 'Mobile number is required to hold bill!';
                        }
                        if (!/^[0-9]{10}$/.test(clean)) {
                            return 'Please enter a valid 10-digit mobile number!';
                        }
                    }
                }).then((result) => {
                    if (result.isConfirmed) {
                        custMobile = result.value.trim();
                        if (!custName || custName === 'Guest Customer') {
                            custName = 'Customer ' + custMobile;
                        }
                        saveHeldBill(custId, custName, custMobile);
                    }
                });
            } else {
                saveHeldBill(custId, custName, custMobile);
            }
        }

        function saveHeldBill(custId, custName, custMobile) {
            let payload = {
                mode: posMode,
                items: cart.map(i => ({ id: i.id, qty: i.qty })),
                customer_id: custId,
                customer_name: custName,
                customer_mobile: custMobile,
                held_id: currentResumedHeldId
            };

            fetch("{{ route('adc.pos.held-carts.hold') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify(payload)
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Bill Held Successfully!',
                            html: `<strong>Held Bill ID:</strong> <span class="badge bg-warning text-dark fs-14">#HOLD-${data.held_id}</span><br><br>Customer: <strong>${custName}</strong><br>Status: Saved in pending held bills list.`,
                            confirmButtonText: 'OK',
                            confirmButtonColor: '#f59e0b'
                        });
                        clearCart();
                        resetCustomerSelection();
                        fetchHeldCartsCount();
                    } else {
                        Swal.fire('Error', data.message || 'Failed to hold bill.', 'error');
                    }
                });
        }

        let promptedCartIds = new Set();

        function checkCustomerPendingHeldCart(cust) {
            if (!cust) return;
            fetch(`{{ route('adc.pos.held-carts.check-customer') }}?mode=${posMode}&customer_id=${cust.id || ''}&mobile=${cust.mobile || ''}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status && data.cart && data.cart.items.length > 0) {
                        let c = data.cart;
                        if (promptedCartIds.has(c.id)) {
                            return;
                        }
                        promptedCartIds.add(c.id);

                        Swal.fire({
                            title: 'Pending Unbilled Cart Found!',
                            html: `Customer <strong>${c.customer_name}</strong> has a held cart with <strong>${c.items_count} items</strong> (Total: <strong>₹${c.subtotal.toFixed(2)}</strong>) saved at ${c.created_at}.<br><br>Would you like to resume this pending bill?`,
                            icon: 'info',
                            showCancelButton: true,
                            confirmButtonText: 'Yes, Resume Cart',
                            cancelButtonText: 'No, Start Fresh'
                        }).then((res) => {
                            if (res.isConfirmed) {
                                resumeHeldCart(c);
                            }
                        });
                    }
                })
                .catch(err => console.error("Error checking customer held cart:", err));
        }

        let allHeldCartsList = [];

        function openHeldCartsModal() {
            let searchInput = document.getElementById('heldCartSearchInput');
            if (searchInput) searchInput.value = '';

            let tableContainer = document.getElementById('heldCartsListTableContainer');
            if (tableContainer) {
                tableContainer.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-warning spinner-border-sm me-1"></div><span class="fs-12 text-muted">Loading held bills...</span></div>';
            }

            let modalEl = document.getElementById('heldCartsModal');
            let modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modal.show();

            fetch("{{ route('adc.pos.held-carts.index') }}?mode=" + posMode)
                .then(res => res.json())
                .then(data => {
                    allHeldCartsList = (data.status && data.carts) ? data.carts : [];
                    renderHeldCartsTable(allHeldCartsList);
                });
        }

        function filterHeldCarts() {
            let q = (document.getElementById('heldCartSearchInput')?.value || '').toLowerCase().trim();
            if (!q) {
                renderHeldCartsTable(allHeldCartsList);
                return;
            }

            let cleanIdQuery = q.replace(/#|hold-?/gi, '').trim();

            let filtered = allHeldCartsList.filter(c => {
                let holdNumStr = `#hold-${c.id}`.toLowerCase();
                let rawIdStr = String(c.id);
                let nameStr = (c.customer_name || '').toLowerCase();
                let mobileStr = (c.customer_mobile || '').toLowerCase();

                return holdNumStr.includes(q) ||
                    (cleanIdQuery !== '' && rawIdStr === cleanIdQuery) ||
                    nameStr.includes(q) ||
                    mobileStr.includes(q);
            });

            renderHeldCartsTable(filtered, q);
        }

        function clearHeldCartSearch() {
            let searchInput = document.getElementById('heldCartSearchInput');
            if (searchInput) searchInput.value = '';
            renderHeldCartsTable(allHeldCartsList);
        }

        function renderHeldCartsTable(carts, query = '') {
            let tableContainer = document.getElementById('heldCartsListTableContainer');
            if (!tableContainer) return;

            if (!carts || carts.length === 0) {
                tableContainer.innerHTML = `
                            <div class="text-center py-5">
                                <iconify-icon icon="solar:pause-circle-broken" class="fs-48 text-muted mb-2"></iconify-icon>
                                <h6 class="fw-bold text-slate-700">${query ? 'No Matching Held Bills Found' : 'No Held Bills Found'}</h6>
                                <p class="text-muted fs-12 mb-0">${query ? 'Try searching with a different Hold Bill No. or Customer Name.' : 'Currently there are no pending held bills.'}</p>
                            </div>`;
                return;
            }

            let html = '<div class="table-responsive"><table class="table table-hover align-middle fs-12 mb-0"><thead class="table-light"><tr><th>Held ID</th><th>Customer</th><th class="text-center">Items</th><th class="text-end">Subtotal</th><th class="text-center">Held On</th><th class="text-end text-nowrap" style="min-width: 150px;">Actions</th></tr></thead><tbody>';

            carts.forEach(c => {
                let cartJsonEscaped = JSON.stringify(c).replace(/'/g, "&apos;").replace(/"/g, "&quot;");
                html += `<tr>
                            <td><span class="badge bg-warning text-dark fw-bold">#HOLD-${c.id}</span></td>
                            <td>
                                <div class="fw-bold text-slate-800">${c.customer_name}</div>
                                <div class="text-muted fs-11">${c.customer_mobile ? '+91 ' + c.customer_mobile : 'No Mobile'}</div>
                            </td>
                            <td class="text-center"><span class="badge bg-secondary">${c.items_count} Products</span></td>
                            <td class="text-end fw-bold text-success">₹${c.subtotal.toFixed(2)}</td>
                            <td class="text-center text-muted fs-11">${c.created_at}</td>
                            <td class="text-end text-nowrap">
                                <div class="d-inline-flex align-items-center justify-content-end gap-1">
                                    <button type="button" class="btn btn-xs btn-primary fw-bold text-nowrap" onclick='resumeHeldCartFromModal(${cartJsonEscaped})'>
                                        <iconify-icon icon="solar:play-circle-bold" class="align-middle me-0.5"></iconify-icon> Resume
                                    </button>
                                    <button type="button" class="btn btn-xs btn-outline-danger text-nowrap" onclick="deleteHeldCart(${c.id})" title="Delete Held Bill">
                                        <iconify-icon icon="solar:trash-bin-trash-bold" class="align-middle"></iconify-icon>
                                    </button>
                                </div>
                            </td>
                        </tr>`;
            });

            html += '</tbody></table></div>';
            tableContainer.innerHTML = html;
        }

        function resumeHeldCartFromModal(c) {
            bootstrap.Modal.getInstance(document.getElementById('heldCartsModal'))?.hide();
            resumeHeldCart(c);
        }

        function resumeHeldCart(c) {
            Swal.close();
            bootstrap.Modal.getInstance(document.getElementById('heldCartsModal'))?.hide();

            let heldId = typeof c === 'object' ? c.id : c;
            if (!heldId) return;

            promptedCartIds.add(heldId);

            let resumeUrl = "{{ route('adc.pos.held-carts.resume', ['id' => ':id']) }}".replace(':id', heldId);

            fetch(resumeUrl, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
            })
                .then(res => res.json())
                .then(data => {
                    if (data.status && data.cart) {
                        let cartData = data.cart;
                        currentResumedHeldId = cartData.id;
                        cart = cartData.items.map(i => ({
                            id: i.id,
                            name: i.product_name,
                            price: i.price,
                            mrp: i.mrp,
                            qty: i.qty,
                            stock: (typeof i.stock !== 'undefined') ? i.stock : 9999,
                            unit_of_measure: i.unit_of_measure,
                            gst_rate: i.gst_rate,
                            image_url: i.image_url
                        }));

                        renderCart();
                        fetchHeldCartsCount();

                        if (cartData.customer_id || cartData.customer_mobile) {
                            selectCustomer({
                                id: cartData.customer_id,
                                name: cartData.customer_name,
                                mobile: cartData.customer_mobile
                            }, true);
                        }

                        Toastify({ text: `Pending Cart Resumed for ${cartData.customer_name}!`, backgroundColor: "#10b981" }).showToast();
                    }
                })
                .catch(err => console.error("Error resuming held cart:", err));
        }

        function deleteHeldCart(id) {
            Swal.fire({
                title: 'Delete Held Bill?',
                text: 'Are you sure you want to discard this pending unbilled cart?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Yes, Delete',
                confirmButtonColor: '#ef4444'
            }).then((res) => {
                if (res.isConfirmed) {
                    let deleteUrl = "{{ route('adc.pos.held-carts.delete', ['id' => ':id']) }}".replace(':id', id);

                    fetch(deleteUrl, {
                        method: 'DELETE',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' }
                    })
                        .then(res => res.json())
                        .then(data => {
                            Toastify({ text: "Held bill deleted.", backgroundColor: "#3b82f6" }).showToast();
                            fetchHeldCartsCount();
                            openHeldCartsModal();
                        });
                }
            });
        }
    </script>
@endpush