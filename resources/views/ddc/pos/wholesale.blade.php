@extends('layouts.pos')

@section('title', 'Wholesale POS Terminal')

@push('styles')
    <style>
        .pos-left-header {
            background: #ffffff;
            border-bottom: 1px solid #e2e8f0;
            padding: 0.75rem 1.25rem;
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
                        <span class="badge bg-warning text-dark px-2.5 py-1 fs-12 fw-bold">
                            WHOLESALE POS MODE
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

        <!-- RIGHT PANE: Canteen Lookup + Cart + Checkout Controls -->
        <div class="pos-right-pane p-3">

            <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                <h6 class="fw-bold text-slate-800 mb-0 fs-15 d-flex align-items-center gap-1.5">
                    <iconify-icon icon="solar:cart-bold-duotone" class="text-primary fs-20"></iconify-icon>
                    Current POS Order
                </h6>
                <button type="button" class="btn btn-xs btn-outline-danger" onclick="clearCart()">Clear Cart</button>
            </div>

            <!-- Wholesale Mode: Select Assigned ADC Canteen -->
            <div class="mb-2 p-2.5 rounded-3 bg-light border border-warning-subtle shadow-xs">
                <div class="d-flex justify-content-between align-items-center mb-1.5 px-0.5">
                    <label class="form-label text-slate-800 fw-bold fs-12 mb-0 d-flex align-items-center gap-1">
                        <iconify-icon icon="solar:shop-bold-duotone" class="text-warning fs-15"></iconify-icon>
                        Select Canteen (ADC)
                    </label>
                    <span
                        class="badge bg-warning-subtle text-dark fw-bold fs-10 px-2 py-0.5 rounded-pill border border-warning-subtle">Canteen
                        Wallet Deduction</span>
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
                <div id="selectedAdcBox" class="d-none bg-white p-2 rounded-2 border border-success shadow-xs mt-1.5">
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

                <!-- Wholesale Mode Notice: Payment automatically deducted from Canteen Wallet -->
                <div class="p-2 bg-success-subtle text-success rounded-2 border border-success mb-2 text-center fs-12 fw-bold">
                    <iconify-icon icon="solar:wallet-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon>
                    Payment will be deducted from Canteen Wallet
                </div>

                @php
                    $posDdcUser = Auth::guard('ddc')->user();
                    $posDdcProfile = $posDdcUser?->ddcProfile;
                    $posWalletBal = (float) ($posDdcProfile?->wallet_balance ?? 0);
                    $posIsDeactivated = ($posDdcUser?->status == 0 || $posDdcUser?->status === '0' || $posWalletBal <= 0);
                @endphp

                @if($posIsDeactivated)
                    <div class="alert alert-danger p-2 mb-2 fs-12 rounded-2 border-0 text-start"
                        style="background: #fef2f2; color: #991b1b; border-left: 3px solid #ef4444 !important;">
                        <iconify-icon icon="solar:close-circle-bold-duotone"
                            class="text-danger me-1 align-middle fs-16"></iconify-icon>
                        <strong>POS Sales Deactivated:</strong>
                        {{ $posDdcProfile?->deactivation_reason ?: 'Wallet balance exhausted. Daily platform fee deduction failed. Recharge wallet to resume sales.' }}
                    </div>
                    <button type="button"
                        class="btn btn-secondary w-100 py-2 fs-14 fw-bold rounded-2 shadow-sm d-flex align-items-center justify-content-center gap-2"
                        disabled>
                        <iconify-icon icon="solar:forbidden-circle-bold-duotone" class="fs-18"></iconify-icon>
                        Sales Disabled (Recharge Required)
                    </button>
                @else
                    <!-- Complete Wholesale Order Button -->
                    <button type="button"
                        class="btn btn-success w-100 py-2 fs-14 fw-bold rounded-2 shadow-sm d-flex align-items-center justify-content-center gap-2"
                        id="btnCheckout" onclick="processCheckout()">
                        <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-18"></iconify-icon>
                        Process Order & Deduct Wallet
                    </button>
                @endif
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
        const posMode = 'wholesale';
        let cart = [];
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
            loadSavedCart();

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

        });

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

        // Handle Barcode Scan / Enter Search Execution
        function handleBarcodeOrSearchScan(queryVal) {
            const url = `{{ route('ddc.pos.products') }}?mode=${posMode}&page=1&search=${encodeURIComponent(queryVal)}`;

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

        // Handle ADC Selection in Wholesale Mode
        function onAdcSelected(selectElem, doSync = true) {
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
            if (doSync) syncCartToDb();
        }

        // DB Cart Persistent Sync Functions
        let syncTimeout = null;
        function syncCartToDb() {
            clearTimeout(syncTimeout);
            syncTimeout = setTimeout(() => {
                let payload = {
                    mode: posMode,
                    items: cart.map(i => ({ id: i.id, qty: i.qty })),
                    adc_id: selectedAdcId
                };
                fetch("{{ route('ddc.pos.cart.sync') }}", {
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
            fetch(`{{ route('ddc.pos.cart.get') }}?mode=${posMode}`)
                .then(res => res.json())
                .then(data => {
                    if (data.status && data.items && data.items.length > 0) {
                        cart = data.items;
                        renderCart(false);
                    }
                    if (data.adc_id) {
                        let adcSel = document.getElementById('adcSelect');
                        if (adcSel) {
                            adcSel.value = data.adc_id;
                            onAdcSelected(adcSel, false);
                        }
                    }
                })
                .catch(e => console.error("Load cart error:", e));
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
                                    <div class="fs-12 fw-bold text-warning-emphasis">
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
            if (!selectedAdcId) {
                Swal.fire({
                    title: 'Select Canteen First',
                    text: 'Please select an assigned Canteen (ADC) before adding items to cart.',
                    icon: 'warning',
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#ff9933'
                }).then(() => {
                    let adcSelect = document.getElementById('adcSelect');
                    if (adcSelect) {
                        adcSelect.focus();
                        adcSelect.classList.add('is-invalid');
                        setTimeout(() => adcSelect.classList.remove('is-invalid'), 2500);
                    }
                });
                return;
            }

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

            // Auto focus Qty input box for bulk entry in Wholesale POS mode
            setTimeout(() => {
                let inputEl = document.querySelector(`.qty-input[data-id="${inv.id}"]`);
                if (inputEl) {
                    inputEl.focus();
                    inputEl.select();
                }
            }, 60);
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
            let isDec = isDecimalAllowed(item.unit_of_measure);
            let newQty = isDec ? parseFloat(val) : Math.floor(parseFloat(val) || 0);
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
            isDiscountManuallyEdited = false;
            let specDiscInput = document.getElementById('inputSpecialDiscount');
            if (specDiscInput) specDiscInput.value = '';
            renderCart();
            fetch("{{ route('ddc.pos.cart.clear') }}", {
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
                let lineTotal = item.price * item.qty;
                let shortUom = getShortUom(item.unit_of_measure);
                let isDec = isDecimalAllowed(item.unit_of_measure);
                let stepAttr = isDec ? 'min="0.001" step="any"' : 'min="1" step="1"';
                let discountPerItem = Math.max(0, item.mrp - item.price);
                let discountPercent = (item.mrp > 0 && discountPerItem > 0) ? Math.round((discountPerItem / item.mrp) * 100) : 0;

                let priceSub = `₹${item.price.toFixed(2)}`;
                if (discountPerItem > 0) {
                    priceSub += ` <span class="text-decoration-line-through text-muted fs-10">₹${item.mrp.toFixed(2)}</span> <span class="text-success fs-10 fw-semibold">(-${discountPercent}%)</span>`;
                }

                html += `
                    <tr>
                        <td class="py-1">
                            <div class="fw-bold text-slate-800 fs-11 text-wrap text-break" title="${item.name}">${item.name}</div>
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

            let specialDiscountInput = parseFloat(document.getElementById('inputSpecialDiscount')?.value) || 0;
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

        // Checkout
        function processCheckout() {
            if (cart.length === 0) {
                Toastify({ text: "Your cart is empty!", backgroundColor: "#ef4444" }).showToast();
                return;
            }

            let discount = parseFloat(document.getElementById('inputDiscount').value) || 0;
            let specialDiscount = parseFloat(document.getElementById('inputSpecialDiscount')?.value) || 0;
            let tax = parseFloat(document.getElementById('inputTax').value) || 0;
            let subtotal = cart.reduce((acc, item) => acc + (item.price * item.qty), 0);
            let grandTotal = Math.max(0, subtotal - specialDiscount);

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

            let btn = document.getElementById('btnCheckout');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Deducting Wallet...';

            let payload = {
                mode: posMode,
                items: cart.map(i => ({ id: i.id, inventory_id: i.id, qty: i.qty })),
                payment_mode: 'WALLET',
                discount_amount: discount,
                special_discount_amount: specialDiscount,
                tax_amount: tax,
                adc_user_id: selectedAdcId
            };

            fetch("{{ route('ddc.pos.checkout') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "Accept": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}"
                },
                body: JSON.stringify(payload)
            })
                .then(async res => {
                    const data = await res.json();
                    if (!res.ok) {
                        let msg = data.message || 'Error processing Wholesale POS order.';
                        if (data.errors) {
                            msg = Object.values(data.errors).flat().join('<br>');
                        }
                        throw new Error(msg);
                    }
                    return data;
                })
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = '<iconify-icon icon="solar:check-circle-bold-duotone" class="fs-18"></iconify-icon> Process Order & Deduct Wallet';

                    if (data.status) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Wholesale Order Processed!',
                            html: `Tax Invoice <strong>#${data.invoice_number}</strong> generated.<br>Status for ADC: <span class="badge bg-success">Dispatched</span><br>Canteen Wallet Deducted: <strong>₹${grandTotal.toFixed(2)}</strong>`,
                            showCancelButton: true,
                            confirmButtonText: 'Print Tax Invoice',
                            cancelButtonText: 'New Order'
                        }).then((res) => {
                            clearCart();
                            fetchProducts(true);

                            if (res.isConfirmed) {
                                if (data.invoice_url) {
                                    window.open(data.invoice_url, '_blank');
                                } else {
                                    openReceiptModal(data.order_id);
                                }
                            }
                        });
                    } else {
                        Swal.fire('Checkout Failed', data.message || 'Error processing Wholesale POS order.', 'error');
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = '<iconify-icon icon="solar:check-circle-bold-duotone" class="fs-18"></iconify-icon> Process Order & Deduct Wallet';
                    console.error("Checkout error:", err);
                    Swal.fire('Error', err.message || 'Server connection error.', 'error');
                });
        }

        function renderPosReceiptHtml(order) {
            let itemsHtml = (order.items || []).map((it, idx) => `
                <tr>
                    <td style="padding: 6px; border: 1px solid #dee2e6;">${idx + 1}</td>
                    <td style="padding: 6px; border: 1px solid #dee2e6;">
                        <strong>${it.product_name || 'Item'}</strong>
                        ${it.batch_number ? `<br><small class="text-muted">Batch: ${it.batch_number}</small>` : ''}
                    </td>
                    <td style="padding: 6px; border: 1px solid #dee2e6;" class="text-center">${it.quantity} ${it.unit_of_measure || ''}</td>
                    <td style="padding: 6px; border: 1px solid #dee2e6;" class="text-end">₹${parseFloat(it.unit_price || 0).toFixed(2)}</td>
                    <td style="padding: 6px; border: 1px solid #dee2e6;" class="text-end">₹${parseFloat(it.total_price || 0).toFixed(2)}</td>
                </tr>
            `).join('');

            let ddcName = (order.ddc && order.ddc.name) ? order.ddc.name : 'DDC CANTEEN STORE';
            let gstin = (order.invoice && order.invoice.supplier_gstin && order.invoice.supplier_gstin !== 'N/A')
                ? order.invoice.supplier_gstin
                : ((order.ddc && order.ddc.ddc_profile && order.ddc.ddc_profile.ddc_gst_number)
                    ? order.ddc.ddc_profile.ddc_gst_number
                    : ((order.ddc && order.ddc.profile && order.ddc.profile.ddc_gst_number) ? order.ddc.profile.ddc_gst_number : 'N/A'));

            let invoiceNo = (order.invoice && order.invoice.invoice_number) ? order.invoice.invoice_number : (order.order_no || 'N/A');

            return `
                <div class="text-center mb-3 border-bottom pb-3">
                    <h4 class="fw-bold mb-0 text-slate-800">${ddcName}</h4>
                    <div class="text-muted fs-12">GSTIN: <strong>${gstin}</strong></div>
                    <div class="fw-bold text-primary fs-13 mt-1">POS WHOLESALE TAX INVOICE / SALE RECEIPT</div>
                </div>
                <div class="row fs-12 mb-3">
                    <div class="col-6">
                        <div><strong>Invoice No:</strong> <code class="fw-bold text-dark fs-13">${invoiceNo}</code></div>
                        <div><strong>Order No:</strong> <code>${order.order_no}</code></div>
                        <div><strong>Date:</strong> ${new Date(order.created_at).toLocaleString()}</div>
                        <div><strong>POS Mode:</strong> WHOLESALE (ADC CANTEEN)</div>
                    </div>
                    <div class="col-6 text-end">
                        <div><strong>ADC Canteen:</strong> ${order.customer_name || 'ADC Canteen'}</div>
                        <div><strong>Mobile:</strong> ${order.customer_mobile || '—'}</div>
                        <div><strong>Payment:</strong> <span class="badge bg-success">${order.payment_mode || 'WALLET'}</span></div>
                    </div>
                </div>
                <table class="table table-sm table-bordered fs-12 mb-3 align-middle" style="width: 100%; border-collapse: collapse;">
                    <thead class="table-light">
                        <tr>
                            <th style="padding: 6px; border: 1px solid #dee2e6;">#</th>
                            <th style="padding: 6px; border: 1px solid #dee2e6;">Product / Item Details</th>
                            <th style="padding: 6px; border: 1px solid #dee2e6;" class="text-center">Qty</th>
                            <th style="padding: 6px; border: 1px solid #dee2e6;" class="text-end">Rate</th>
                            <th style="padding: 6px; border: 1px solid #dee2e6;" class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>${itemsHtml}</tbody>
                </table>
                <div class="row fs-13 border-top pt-2">
                    <div class="col-6 text-muted fs-11">
                        Thank you for your business!<br>
                        <em>This is a computer generated POS Tax Invoice.</em>
                    </div>
                    <div class="col-6">
                        <div class="d-flex justify-content-between"><span>Subtotal:</span> <strong>₹${parseFloat(order.subtotal || 0).toFixed(2)}</strong></div>
                        ${order.discount_amount > 0 ? `<div class="d-flex justify-content-between text-danger"><span>Discount:</span> <strong>-₹${parseFloat(order.discount_amount).toFixed(2)}</strong></div>` : ''}
                        ${order.special_discount_amount > 0 ? `<div class="d-flex justify-content-between text-danger"><span>Special Disc:</span> <strong>-₹${parseFloat(order.special_discount_amount).toFixed(2)}</strong></div>` : ''}
                        ${order.tax_amount > 0 ? `<div class="d-flex justify-content-between"><span>Tax/GST:</span> <strong>+₹${parseFloat(order.tax_amount).toFixed(2)}</strong></div>` : ''}
                        <div class="d-flex justify-content-between border-top pt-1 mt-1 fs-15 fw-bold text-success"><span>Grand Total:</span> <strong>₹${parseFloat(order.total_amount || 0).toFixed(2)}</strong></div>
                    </div>
                </div>
            `;
        }

        function openReceiptWindow(orderId) {
            let printUrl = "{{ route('ddc.pos.orders.print', ['id' => 999999]) }}".replace('999999', orderId);
            window.open(printUrl, '_blank');
        }
        window.openReceiptModal = openReceiptWindow;
    </script>
@endpush