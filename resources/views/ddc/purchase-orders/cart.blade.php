@extends('layouts.ddc')

@section('title', 'Purchase Order Cart')
@section('page-title', 'PO Cart')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">Purchase Order Cart</h4>
                            <p class="text-muted small mb-0">Review items, adjust quantities, and place your purchase order.</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <a href="{{ route('ddc.purchase-orders.create') }}" class="btn btn-outline-primary">
                                <iconify-icon icon="solar:arrow-left-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                                Back to Products
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>



    @if(empty($cart))
        <div class="card border-0 shadow-sm text-center py-5">
            <div class="card-body">
                <div class="avatar-lg mx-auto bg-light rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 72px; height: 72px;">
                    <iconify-icon icon="solar:cart-large-minimalistic-bold-duotone" class="text-muted fs-36"></iconify-icon>
                </div>
                <h5 class="fw-bold text-slate-800">Your PO Cart is Empty</h5>
                <p class="text-muted small mx-auto mb-4" style="max-width: 360px;">
                    You have not added any products to the purchase order cart yet.
                </p>
                <a href="{{ route('ddc.purchase-orders.create') }}" class="btn btn-primary px-4 py-2">
                    Browse Products
                </a>
            </div>
        </div>
    @else
        <div class="row g-4">
            <!-- Left: Cart Items List -->
            <div class="col-xl-8">
                <div class="card border-0 shadow-sm">
                    <div class="card-header bg-white border-0 py-3 border-bottom">
                        <h6 class="fw-bold text-slate-800 mb-0">Cart Items</h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover mb-0 align-middle">
                                <thead class="bg-light bg-opacity-50 border-top border-bottom">
                                    <tr>
                                        <th class="ps-3" style="width: 100px;">Product</th>
                                        <th>Details</th>
                                        <th>Seller</th>
                                        <th class="text-end">Price</th>
                                        <th class="text-center">Stock in Hand</th>
                                        <th class="text-center" style="width: 150px;">Quantity</th>
                                        <th class="text-end">Total</th>
                                        <th class="text-center">Status</th>
                                        <th class="pe-3 text-center" style="width: 80px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php 
                                        $grandTotal = 0; 
                                        $grandTaxableTotal = 0;
                                        $grandTaxTotal = 0;
                                        
                                        // Group cart items by seller
                                        $groupedCart = [];
                                        foreach ($cart as $key => $item) {
                                            $sellerName = $item['seller_name'];
                                            $groupedCart[$sellerName][$key] = $item;
                                        }
                                    @endphp

                                    @foreach($groupedCart as $sellerName => $items)
                                        <!-- Group Header for Seller -->
                                        <tr class="table-light">
                                            <td colspan="9" class="ps-3 fw-bold text-slate-800 py-2">
                                                <iconify-icon icon="solar:shop-bold-duotone" class="align-middle me-1 text-primary fs-18"></iconify-icon>
                                                {{ $sellerName }}
                                            </td>
                                        </tr>

                                        @foreach($items as $key => $item)
                                            @php 
                                                $isAvailable = $item['is_available'] ?? true;
                                                $itemTotal = $isAvailable ? ($item['price'] * $item['quantity']) : 0;
                                                
                                                if ($isAvailable) {
                                                    $prodModel = \App\Models\Product::find($item['product_id']);
                                                    $gstRate = $prodModel ? (float) $prodModel->gst_rate : 0.00;
                                                    $itemTaxableTotal = round($itemTotal / (1 + ($gstRate / 100)), 2);
                                                    $itemTaxTotal = round($itemTotal - $itemTaxableTotal, 2);

                                                    $grandTotal += $itemTotal;
                                                    $grandTaxableTotal += $itemTaxableTotal;
                                                    $grandTaxTotal += $itemTaxTotal;
                                                }
                                            @endphp
                                            <tr class="{{ !$isAvailable ? 'table-danger bg-opacity-10' : '' }}">
                                                <td class="ps-3">
                                                    <div class="bg-light rounded text-center" style="width: 60px; height: 60px; overflow: hidden; position: relative;">
                                                        @if($item['featured_image'])
                                                            <img src="{{ Str::startsWith($item['featured_image'], ['http://', 'https://']) ? $item['featured_image'] : asset('storage/' . $item['featured_image']) }}" alt="{{ $item['product_name'] }}" class="h-100 w-100 object-fit-contain p-1">
                                                        @else
                                                            <div class="d-flex align-items-center justify-content-center h-100">
                                                                <iconify-icon icon="solar:box-bold-duotone" class="text-muted fs-24"></iconify-icon>
                                                            </div>
                                                        @endif

                                                        @if(!$isAvailable)
                                                            <div class="position-absolute top-0 start-0 w-100 h-100 d-flex align-items-center justify-content-center bg-dark bg-opacity-50">
                                                                <span class="badge bg-danger fs-10 px-1 py-0.5">N/A</span>
                                                            </div>
                                                        @endif
                                                    </div>
                                                </td>
                                                <td>
                                                    <h6 class="fw-semibold text-slate-800 mb-0 {{ !$isAvailable ? 'text-decoration-line-through text-muted' : '' }}">{{ $item['product_name'] }}</h6>
                                                    <span class="text-muted small d-block">Brand: {{ $item['brand_name'] ?? '—' }}</span>
                                                    @if($item['barcode'] ?? null)
                                                        <span class="text-muted small d-block">Code: {{ $item['barcode'] }}</span>
                                                    @endif
                                                    @if(!$isAvailable)
                                                        <span class="badge bg-danger-subtle text-danger mt-1">Not Available / Inactive</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <span class="text-muted small">{{ $item['seller_name'] }}</span>
                                                </td>
                                                <td class="text-end fw-semibold text-slate-800">
                                                    @if($isAvailable)
                                                        ₹{{ number_format($item['price'], 2) }}
                                                    @else
                                                        <span class="text-muted">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-secondary-subtle text-secondary fs-12">{{ $item['stock_in_hand'] ?? 0 }} {{ $item['unit_of_measure'] ?? 'Units' }}</span>
                                                </td>
                                                <td>
                                                    @if($isAvailable)
                                                        <form action="{{ route('ddc.purchase-orders.cart.update') }}" method="POST" class="d-flex align-items-center justify-content-center gap-1 cart-qty-form">
                                                            @csrf
                                                            <input type="hidden" name="cart_key" value="{{ $key }}">
                                                            <input type="number" name="quantity" class="form-control form-control-sm text-center py-1 cart-qty-input" value="{{ $item['quantity'] }}" min="{{ $item['case_pack_quantity'] ?? 1 }}" step="{{ $item['case_pack_quantity'] ?? 1 }}" data-cart-key="{{ $key }}" data-case-pack="{{ $item['case_pack_quantity'] ?? 1 }}" style="max-width: 65px;">
                                                        </form>
                                                    @else
                                                        <span class="text-muted small text-center d-block">—</span>
                                                    @endif
                                                </td>
                                                <td class="text-end fw-bold text-primary row-total" id="row-total-{{ $key }}">
                                                    @if($isAvailable)
                                                        ₹{{ number_format($itemTotal, 2) }}
                                                    @else
                                                        <span class="text-danger small">Unavailable</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($isAvailable)
                                                        <span class="badge bg-success-subtle text-success fs-12">Available</span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger fs-12">Not Available</span>
                                                    @endif
                                                </td>
                                                <td class="pe-3 text-center">
                                                    <form action="{{ route('ddc.purchase-orders.cart.remove') }}" method="POST">
                                                        @csrf
                                                        <input type="hidden" name="cart_key" value="{{ $key }}">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger border-0 p-1" title="Remove Item">
                                                            <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="fs-18"></iconify-icon>
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Order Summary and Checkout -->
            <div class="col-xl-4">
                <div class="card border-0 shadow-sm mb-4">
                    <div class="card-header bg-white border-0 py-3 border-bottom">
                        <h6 class="fw-bold text-slate-800 mb-0">PO Summary</h6>
                    </div>
                    <div class="card-body">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Items</span>
                            <span class="fw-semibold text-slate-800" id="summary-total-items">{{ count($cart) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Total Product Qty</span>
                            <span class="fw-semibold text-slate-800" id="summary-total-qty">{{ array_sum(array_column($cart, 'quantity')) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Taxable Amount</span>
                            <span class="fw-semibold text-slate-800" id="summary-taxable-amount">₹{{ number_format($grandTaxableTotal, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Total GST / Tax</span>
                            <span class="fw-semibold text-slate-800" id="summary-tax-amount">₹{{ number_format($grandTaxTotal, 2) }}</span>
                        </div>
                        <hr class="text-muted opacity-20">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <span class="h6 fw-bold text-slate-800 mb-0">Total Estimated Amount</span>
                            <span class="h5 fw-bold text-primary mb-0" id="summary-grand-total">₹{{ number_format($grandTotal, 2) }}</span>
                        </div>

                        @if($hasUnavailableItems)
                            <div class="alert alert-warning border-0 small mb-3">
                                <iconify-icon icon="solar:danger-triangle-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon>
                                Some items in your cart are inactive/not available. Please remove them to proceed.
                            </div>
                            <button type="button" class="btn btn-secondary w-100 fw-semibold py-2 fs-15" disabled>
                                <iconify-icon icon="solar:send-bold-duotone" class="me-1 align-middle fs-18"></iconify-icon> Place Purchase Order
                            </button>
                        @else
                            <form id="place-order-form" action="{{ route('ddc.purchase-orders.store') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn btn-primary w-100 fw-semibold py-2 fs-15">
                                    <iconify-icon icon="solar:send-bold-duotone" class="me-1 align-middle fs-18"></iconify-icon> Place Purchase Order
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                const qtyInputs = document.querySelectorAll('.cart-qty-input');
                const qtyForms = document.querySelectorAll('.cart-qty-form');

                qtyForms.forEach(form => {
                    form.addEventListener('submit', function(e) {
                        e.preventDefault();
                    });
                });

                qtyInputs.forEach(input => {
                    input.addEventListener('change', function() {
                        const cartKey = this.getAttribute('data-cart-key');
                        const quantity = parseInt(this.value);
                        const casePack = parseInt(this.getAttribute('data-case-pack') || 1);

                        if (isNaN(quantity) || quantity < casePack) {
                            showToast("Quantity cannot be less than case pack quantity (" + casePack + ").", "error");
                            this.value = casePack;
                            return;
                        }

                        if (quantity % casePack !== 0) {
                            showToast("Quantity must be in multiples of case pack quantity (" + casePack + ").", "error");
                            this.value = Math.round(quantity / casePack) * casePack || casePack;
                            return;
                        }

                        this.disabled = true;

                        fetch("{{ route('ddc.purchase-orders.cart.update') }}", {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify({
                                cart_key: cartKey,
                                quantity: quantity
                            })
                        })
                        .then(response => {
                            if (!response.ok) {
                                return response.json().then(err => { throw err; });
                            }
                            return response.json();
                        })
                        .then(data => {
                            this.disabled = false;
                            if (data.success) {
                                const rowTotalElem = document.getElementById('row-total-' + cartKey);
                                if (rowTotalElem) {
                                    rowTotalElem.innerText = '₹' + parseFloat(data.item_total).toLocaleString('en-IN', {
                                        minimumFractionDigits: 2,
                                        maximumFractionDigits: 2
                                    });
                                }

                                document.getElementById('summary-total-qty').innerText = data.grand_total_qty;
                                document.getElementById('summary-taxable-amount').innerText = '₹' + parseFloat(data.grand_taxable_total).toLocaleString('en-IN', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                                document.getElementById('summary-tax-amount').innerText = '₹' + parseFloat(data.grand_tax_total).toLocaleString('en-IN', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });
                                document.getElementById('summary-grand-total').innerText = '₹' + parseFloat(data.grand_total).toLocaleString('en-IN', {
                                    minimumFractionDigits: 2,
                                    maximumFractionDigits: 2
                                });

                                showToast(data.message, "success");
                            } else {
                                showToast(data.message || "Failed to update cart.", "error");
                                if (data.original_quantity) {
                                    this.value = data.original_quantity;
                                }
                            }
                        })
                        .catch(error => {
                            this.disabled = false;
                            console.error('Error:', error);
                            const errMsg = error.message || "Something went wrong. Please try again.";
                            showToast(errMsg, "error");
                            if (error.original_quantity) {
                                this.value = error.original_quantity;
                            }
                        });
                    });
                });

                function showToast(message, type = 'success') {
                    if (typeof showSessionAlert === 'function') {
                        showSessionAlert(message, type);
                    } else if (typeof Toastify === 'function') {
                        const bgColors = {
                            success: "#10b981",
                            error: "#ef4444",
                            warning: "#f59e0b",
                            info: "#3b82f6"
                        };
                        Toastify({
                            text: message,
                            duration: 3000,
                            close: true,
                            gravity: "top",
                            position: "right",
                            style: {
                                background: bgColors[type] || bgColors['success'],
                            }
                        }).showToast();
                    } else {
                        if (typeof Swal === 'function') {
                            Swal.fire({
                                icon: type === 'error' ? 'error' : 'success',
                                text: message,
                                confirmButtonColor: '#ff9933'
                            });
                        } else {
                            console.log(message);
                        }
                    }
                }
            });
        </script>
    @endpush
@endsection
