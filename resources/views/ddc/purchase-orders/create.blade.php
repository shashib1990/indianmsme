@extends('layouts.ddc')

@section('title', 'Generate Purchase Order')
@section('page-title', 'Generate Purchase Order')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3">
                    <!-- Title & Cart Button Row -->
                    <div class="row align-items-center g-3 mb-2">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-0" style="font-family: 'Outfit', sans-serif; font-size: 20px;">Generate Purchase Order</h4>
                            <p class="text-muted small mb-0">Select a seller, search their catalog, and add products to the cart to place a Purchase Order.</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <a href="{{ route('ddc.purchase-orders.cart.view') }}" class="btn btn-primary position-relative px-3 py-1.5">
                                <iconify-icon icon="solar:cart-large-minimalistic-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                                View Cart
                                @if(isset($cartCount) && $cartCount > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white">
                                        {{ $cartCount }}
                                    </span>
                                @endif
                            </a>
                        </div>
                    </div>
                    
                    <hr class="text-muted opacity-15 my-3">
                    
                    <!-- Filters & Search Row -->
                    <form id="sellerForm" action="{{ route('ddc.purchase-orders.create') }}" method="GET">
                        <div class="row align-items-end g-3">
                            <!-- Main Seller Dropdown -->
                            <div class="col-md-4">
                                <label class="form-label fw-semibold text-slate-700 mb-1">Select Seller</label>
                                <select name="parent_seller_id" class="form-select s-select" onchange="document.getElementById('seller_id_input').value=''; this.form.submit()">
                                    <option value="">-- Choose Seller --</option>
                                    @foreach($sellers as $v)
                                        <option value="{{ $v->id }}" {{ $selectedParentSellerId == $v->id ? 'selected' : '' }}>
                                            {{ $v->company_name ?: $v->name }} ({{ $v->seller_id }})
                                        </option>
                                    @endforeach
                                </select>
                                <input type="hidden" name="seller_id" id="seller_id_input" value="{{ $selectedSubSellerId }}">
                            </div>

                            {{-- Sub Seller Dropdown: only show if seller has active sub-sellers --}}
                            @if($hasSubSellers && $hasActiveSubSellers)
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-slate-700 mb-1">Select Sub-Seller</label>
                                    <select class="form-select s-select" onchange="document.getElementById('seller_id_input').value=this.value; document.getElementById('sellerForm').submit()">
                                        <option value="">-- Choose Sub-Seller --</option>
                                        @foreach($subSellers as $sv)
                                            <option value="{{ $sv->id }}" {{ $selectedSubSellerId == $sv->id ? 'selected' : '' }}>
                                                {{ $sv->company_name ?: $sv->name }} ({{ $sv->seller_id }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <!-- Search Input (Only shown if products will be displayed) -->
                            @php
                                $willShowProducts = $selectedParentSellerId && (
                                    !$hasSubSellers ||
                                    ($hasSubSellers && !$hasActiveSubSellers) ||
                                    ($hasSubSellers && $hasActiveSubSellers && $selectedSubSellerId)
                                );
                            @endphp
                            @if($willShowProducts)
                                <div class="{{ ($hasSubSellers && $hasActiveSubSellers) ? 'col-md-4' : 'col-md-5' }}">
                                    <label class="form-label fw-semibold text-slate-700 mb-1">Search Products</label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0">
                                            <iconify-icon icon="solar:magnifer-bold-duotone" class="text-muted"></iconify-icon>
                                        </span>
                                        <input type="text" name="search" class="form-control border-start-0" placeholder="Type to search..." value="{{ $search }}" autocomplete="off">
                                        @if($search)
                                            <a href="{{ route('ddc.purchase-orders.create', ['parent_seller_id' => $selectedParentSellerId, 'seller_id' => $selectedSubSellerId]) }}" class="btn btn-outline-danger d-flex align-items-center">Clear</a>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Reset Selection Button -->
                            @if($selectedParentSellerId && (!$hasSubSellers || ($hasSubSellers && !$hasActiveSubSellers)))
                                <div class="col-md-3">
                                    <a href="{{ route('ddc.purchase-orders.create') }}" class="btn btn-outline-danger w-100 py-2">
                                        Reset Selection
                                    </a>
                                </div>
                            @endif
                            
                            @if($selectedParentSellerId && $hasSubSellers && $hasActiveSubSellers)
                                <div class="col-md-12 text-end mt-2">
                                    <a href="{{ route('ddc.purchase-orders.create') }}" class="btn btn-sm btn-outline-danger px-4">
                                        Reset Selection
                                    </a>
                                </div>
                            @endif
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>



    <!-- Products Catalog Column (Full Width) -->
    <div class="row">
        <div class="col-12">
            @if(!$selectedParentSellerId)
                <div class="card border-0 shadow-sm text-center py-5">
                    <div class="card-body">
                        <div class="avatar-lg mx-auto bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
                            <iconify-icon icon="solar:shop-2-bold-duotone" class="fs-36"></iconify-icon>
                        </div>
                        <h5 class="fw-bold text-slate-800">Please Select a Seller</h5>
                        <p class="text-muted small mx-auto mb-0" style="max-width: 380px;">
                            Choose a Seller from the dropdown above to view their product catalog and add items to your Purchase Order cart.
                        </p>
                    </div>
                </div>
            @elseif($hasSubSellers && $hasActiveSubSellers && !$selectedSubSellerId)
                {{-- Seller has active sub-sellers → must choose one --}}
                <div class="card border-0 shadow-sm text-center py-5">
                    <div class="card-body">
                        <div class="avatar-lg mx-auto bg-light rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
                            <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" class="text-primary fs-36"></iconify-icon>
                        </div>
                        <h5 class="fw-bold text-slate-800">Please Select a Sub-Seller</h5>
                        <p class="text-muted small mx-auto" style="max-width: 360px;">
                            This seller has active sub-sellers. Please select a Sub-Seller from the dropdown to view and order products.
                        </p>
                    </div>
                </div>
            @else

                @if($products->isEmpty())
                    <div class="card border-0 shadow-sm text-center py-5">
                        <div class="card-body">
                            <div class="avatar-lg mx-auto bg-light rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
                                <iconify-icon icon="solar:box-bold-duotone" class="text-muted fs-36"></iconify-icon>
                            </div>
                            <h5 class="fw-bold text-slate-800">No Products Found</h5>
                            <p class="text-muted small mx-auto" style="max-width: 360px;">
                                We couldn't find any approved products for this seller. Try adjusting your search query.
                            </p>
                        </div>
                    </div>
                @else
                    <div class="card border-0 shadow-sm">
                        <div class="card-header bg-white border-bottom py-3">
                            <h6 class="fw-bold text-slate-800 mb-0">Products List ({{ $products->total() }} items)</h6>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover table-centered mb-0 align-middle">
                                    <thead class="bg-light bg-opacity-50">
                                        <tr>
                                            <th class="ps-3" style="width: 60px;">Sn.</th>
                                            <th>Product Image</th>
                                            <th>Product Details</th>
                                            <th>Category</th>
                                            <th class="text-end">MRP</th>
                                            <th class="text-end">DDC Price</th>
                                            <th class="text-center">Margin %</th>
                                            <th class="text-center">Stock in Hand</th>
                                            <th class="text-center" style="width: 150px;">Add to Cart</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($products as $product)
                                            <tr>
                                                <td class="ps-3 text-muted small">{{ $loop->iteration + (($products->currentPage() - 1) * $products->perPage()) }}</td>
                                                <td>
                                                    <div class="bg-light rounded text-center d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; overflow: hidden; flex-shrink: 0;">
                                                        @if($product->featured_image)
                                                            <img src="{{ Str::startsWith($product->featured_image, ['http://', 'https://']) ? $product->featured_image : asset('storage/' . $product->featured_image) }}" alt="{{ $product->product_name }}" class="object-fit-contain p-1" style="max-width: 100%; max-height: 100%;">
                                                        @else
                                                            <iconify-icon icon="solar:gallery-bold-duotone" class="fs-20 text-muted"></iconify-icon>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <h6 class="fw-semibold text-slate-800 mb-1 text-wrap" style="max-width: 250px;">{{ $product->product_name }}</h6>
                                                    <span class="text-muted small d-block mb-1">Brand: {{ $product->brand?->name ?: '—' }}</span>
                                                    @if($product->barcode)
                                                        <span class="text-muted small d-block mb-1">Code: {{ $product->barcode }}</span>
                                                    @endif
                                                    @if($product->case_pack_quantity && $product->case_pack_quantity > 0)
                                                        <span class="badge bg-info-subtle text-info">Pack Size: {{ $product->case_pack_quantity }} {{ $product->unit_of_measure ?: 'Units' }}</span>
                                                    @endif
                                                </td>
                                                <td>{{ $product->category?->name ?: '—' }}</td>
                                                <td class="text-end fw-semibold text-muted">₹{{ number_format($product->mrp, 2) }}</td>
                                                <td class="text-end fw-bold text-primary">₹{{ number_format($product->display_price, 2) }}</td>
                                                <td class="text-center">
                                                    @php
                                                        $marginPercent = 0;
                                                        if ($product->mrp > 0) {
                                                            $marginPercent = (($product->mrp - $product->display_price) / $product->mrp) * 100;
                                                        }
                                                    @endphp
                                                    <span class="badge bg-success-subtle text-success fs-12 px-2 py-1">{{ number_format($marginPercent, 2) }}%</span>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary-subtle text-secondary fs-12">{{ $product->stock_in_hand ?? 0 }} {{ $product->unit_of_measure ?: 'Units' }}</span>
                                                </td>
                                                <td class="text-center pe-3">
                                                    @php
                                                        $isInCart = in_array($product->id, $cartProductIds ?? []);
                                                    @endphp
                                                    <form action="{{ route('ddc.purchase-orders.cart.add') }}" method="POST" class="add-to-cart-form d-flex align-items-center justify-content-center">
                                                        @csrf
                                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                        <input type="hidden" name="seller_id" value="{{ $activeSellerId }}">
                                                        
                                                        <button type="submit" class="btn btn-sm {{ $isInCart ? 'btn-success' : 'btn-primary' }} d-flex align-items-center justify-content-center gap-1 px-3 py-1" title="{{ $isInCart ? 'Item in Cart' : 'Add to Cart' }}" {{ $isInCart ? 'disabled' : '' }}>
                                                            <iconify-icon icon="{{ $isInCart ? 'solar:check-circle-bold-duotone' : 'solar:cart-plus-bold-duotone' }}" class="fs-16"></iconify-icon>
                                                            <span>{{ $isInCart ? 'Added' : 'Add to Cart' }}</span>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-center mt-3">
                        {{ $products->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.querySelector('input[name="search"]');
            if (searchInput) {
                // If there's an active search, focus and place cursor at the end
                if (searchInput.value.trim() !== '') {
                    searchInput.focus();
                    const len = searchInput.value.length;
                    searchInput.setSelectionRange(len, len);
                }

                let debounceTimer;
                searchInput.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        searchInput.form.submit();
                    }, 500); // 500ms debounce delay
                });
            }

            // AJAX Add to Cart (No page reload / maintains scroll position)
            document.querySelectorAll('.add-to-cart-form').forEach(function (form) {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    const btn = form.querySelector('button[type="submit"]');
                    if (btn.disabled || btn.classList.contains('btn-success')) return;

                    const originalContent = btn.innerHTML;
                    btn.disabled = true;
                    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Adding...';

                    const formData = new FormData(form);

                    fetch(form.action, {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => response.json().then(data => ({ ok: response.ok, status: response.status, data })))
                    .then(({ ok, data }) => {
                        if (ok && data.status === 'success') {
                            // Update View Cart Badge
                            const cartLink = document.querySelector('a[href*="/purchase-orders/cart"]');
                            if (cartLink) {
                                let badge = cartLink.querySelector('.badge');
                                if (!badge) {
                                    badge = document.createElement('span');
                                    badge.className = 'position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger border border-white';
                                    cartLink.appendChild(badge);
                                }
                                badge.textContent = data.cart_count;
                            }

                            // Toast notification
                            if (typeof Toastify === 'function') {
                                Toastify({
                                    text: "✓ " + (data.product_name || "Product") + " added to cart!",
                                    duration: 2500,
                                    gravity: "top",
                                    position: "right",
                                    style: { background: "linear-gradient(to right, #00b09b, #96c93d)" }
                                }).showToast();
                            }

                            // Button success state update to 'Added' & disabled
                            btn.disabled = true;
                            btn.className = 'btn btn-sm btn-success d-flex align-items-center justify-content-center gap-1 px-3 py-1';
                            btn.title = 'Item in Cart';
                            btn.innerHTML = '<iconify-icon icon="solar:check-circle-bold-duotone" class="fs-16"></iconify-icon> <span>Added</span>';
                        } else {
                            btn.disabled = false;
                            btn.innerHTML = originalContent;

                            const err = (data && data.message) ? data.message : 'Failed to add product to cart.';
                            if (typeof Toastify === 'function') {
                                Toastify({
                                    text: "⚠️ " + err,
                                    duration: 3500,
                                    gravity: "top",
                                    position: "right",
                                    style: { background: "#dc3545" }
                                }).showToast();
                            } else {
                                alert(err);
                            }
                        }
                    })
                    .catch(err => {
                        btn.disabled = false;
                        btn.innerHTML = originalContent;
                        console.error(err);
                        if (typeof Toastify === 'function') {
                            Toastify({
                                text: "⚠️ An error occurred while adding product to cart.",
                                duration: 3500,
                                gravity: "top",
                                position: "right",
                                style: { background: "#dc3545" }
                            }).showToast();
                        } else {
                            alert("An error occurred while adding product to cart.");
                        }
                    });
                });
            });
        });
    </script>
@endpush
