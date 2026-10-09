{{-- Universal Reusable Product Card Component --}}
@php
    $productId = data_get($product, 'id') ?: 0;
    $mrp = (float) (data_get($product, 'mrp') ?? 0);

    // Determine active sub-seller pricing context
    $subSellerId = request()->input('seller') ?: (isset($seller) && $seller->parent_id !== null ? $seller->id : null) ?: (auth('adc')->check() ? optional(auth('adc')->user()->adcProfile)->code : null);
    $subSeller = null;

    if ($subSellerId) {
        $subSeller = \App\Models\User::where('role', 'seller')
            ->where(function ($q) use ($subSellerId) {
                $q->where('id', $subSellerId)
                    ->orWhereHas('sellerProfile', function ($sq) use ($subSellerId) {
                        $sq->where('seller_id', $subSellerId)
                            ->orWhere('company_name', $subSellerId);
                    });
            })->first();
    }

    if (!$subSeller && isset($product) && method_exists($product, 'getActiveSubSellerForPincode')) {
        $subSeller = $product->getActiveSubSellerForPincode();
    }

    $subSellerProduct = null;
    if ($subSeller) {
        $subSellerProduct = \App\Models\SubSellerProduct::where('sub_seller_id', $subSeller->id)
            ->where('product_id', $productId)
            ->where('status', 'Active')
            ->first();
    }

    // DDC Inventory Priority Logic based on active pincode
    $pincodeSession = session('user_pincode');
    $ddcCardDetails = \App\Services\DdcInventoryService::resolveProductDetails($product, $pincodeSession);

    if ($ddcCardDetails['is_ddc_inventory']) {
        $price = $ddcCardDetails['selling_price'];
        $mrp = $ddcCardDetails['mrp'];
        $mbpShare = (float) (data_get($ddcCardDetails['ddc_inventory'], 'mbp_90_share') ?: (data_get($product, 'mbp_90_share') ?: 0.00));
        $isDdcSourced = true;
    } else {
        if ($subSellerProduct) {
            $price = (float) $subSellerProduct->adc_purchase_price;
            $mbpShare = (float) $subSellerProduct->mbp_90_share;
        } else {
            $price = (float) (data_get($product, 'adc_purchase_price') ?: data_get($product, 'mrp') ?: 0);
            $mbpShare = (float) (data_get($product, 'mbp_90_share') ?: 0.00);
        }
        $isDdcSourced = false;
    }

    $discount = ($mrp > 0 && $mrp > $price) ? round((($mrp - $price) / $mrp) * 100) : 0;

    $productName = data_get($product, 'product_name') ?: 'Product';
    $slug = Str::slug($productName);
    $url = route('product.show', ['id' => $productId, 'slug' => $slug]) . ($subSeller ? '?seller=' . $subSeller->id : '');

    // Product image fallback logic with file existence check
    $featuredImg = data_get($product, 'featured_image');
    $logoUrl = \App\Models\SystemSetting::getVal('website_header_logo', asset('admin-assets/images/IndianMSME.png'));

    if ($featuredImg) {
        if (filter_var($featuredImg, FILTER_VALIDATE_URL)) {
            $productImage = $featuredImg;
        } else {
            $storagePath = 'storage/' . $featuredImg;
            if (file_exists(public_path($storagePath))) {
                $productImage = asset($storagePath);
            } else {
                $productImage = $logoUrl;
            }
        }
    } else {
        $productImage = $logoUrl;
    }

    $brandName = data_get($product, 'brand.name') ?: 'N/A';
    $categoryName = data_get($product, 'category.name') ?: 'N/A';
    $productCode = data_get($product, 'product_code');
    $unit = data_get($product, 'unit_of_measure') ?: 'Pc';
    $moq = data_get($product, 'case_pack_quantity') ?: 1;
@endphp

@once
    <style>
        .universal-product-card {
            height: 100% !important;
            min-height: auto !important;
            width: 100% !important;
            max-width: 260px !important;
            display: flex !important;
            flex-direction: column !important;
            justify-content: space-between !important;
            border: 1px solid #e5e7eb !important;
            border-radius: 20px !important;
            padding: 12px 12px 14px 12px !important;
            background-color: #ffffff !important;
            position: relative !important;
            overflow: hidden !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04) !important;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-sizing: border-box !important;
        }

        .universal-product-card * {
            box-sizing: border-box !important;
        }

        .universal-product-card:hover {
            border-color: #cbd5e1 !important;
            box-shadow: 0 12px 24px -6px rgba(15, 23, 42, 0.12) !important;
            transform: translateY(-3px);
        }

        .universal-image-container {
            position: relative !important;
            height: 180px !important;
            background-color: #f8fafc !important;
            border-radius: 14px !important;
            overflow: hidden !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            flex-shrink: 0 !important;
            width: 100% !important;
            padding: 10px !important;
        }

        .universal-product-image {
            max-width: 100% !important;
            max-height: 100% !important;
            object-fit: contain !important;
            transition: transform 0.4s ease !important;
        }

        .universal-product-card:hover .universal-product-image {
            transform: scale(1.06);
        }

        @media (max-width: 640px) {
            .universal-product-card {
                padding: 8px 8px 10px 8px !important;
                border-radius: 14px !important;
            }
            .universal-image-container {
                height: 135px !important;
                padding: 6px !important;
                border-radius: 10px !important;
            }
            .universal-product-title {
                font-size: 12px !important;
                line-height: 1.3 !important;
                height: 2.6em !important;
            }
            .universal-price-current {
                font-size: 14px !important;
            }
        }

        .universal-discount-badge {
            position: absolute !important;
            top: 10px !important;
            left: 10px !important;
            background: linear-gradient(135deg, #ff4d00 0%, #ef4444 100%) !important;
            color: #ffffff !important;
            font-size: 10px !important;
            font-weight: 800 !important;
            text-transform: uppercase !important;
            padding: 3px 8px !important;
            border-radius: 20px !important;
            z-index: 10 !important;
            letter-spacing: 0.04em !important;
            box-shadow: 0 2px 6px rgba(239, 68, 68, 0.3) !important;
            line-height: 1.2 !important;
        }

        .universal-badge-stack {
            position: absolute !important;
            top: 10px !important;
            right: 10px !important;
            display: flex !important;
            flex-direction: column !important;
            gap: 4px !important;
            align-items: flex-end !important;
            z-index: 10 !important;
        }

        .universal-badge-pill {
            background-color: #ffffff !important;
            color: #1e293b !important;
            font-size: 10px !important;
            font-weight: 700 !important;
            padding: 3px 8px !important;
            border-radius: 20px !important;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.08) !important;
            border: 1px solid #e2e8f0 !important;
            display: flex !important;
            align-items: center !important;
            gap: 4px !important;
            line-height: 1.2 !important;
        }

        .universal-middle-section {
            display: flex !important;
            flex-direction: column !important;
            gap: 4px !important;
            margin-top: 10px !important;
            flex-grow: 1 !important;
            justify-content: flex-start !important;
        }

        .universal-brand-name {
            font-size: 12px !important;
            font-weight: 700 !important;
            color: #1e293b !important;
            letter-spacing: -0.01em !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
            margin-bottom: 2px !important;
        }

        .universal-product-title {
            font-size: 13px !important;
            font-weight: 600 !important;
            color: #334155 !important;
            line-height: 1.3 !important;
            height: 34px !important;
            overflow: hidden !important;
            display: -webkit-box !important;
            -webkit-line-clamp: 2 !important;
            -webkit-box-orient: vertical !important;
            transition: color 0.2s ease !important;
            margin: 0 !important;
        }

        .universal-product-title a {
            color: inherit !important;
            text-decoration: none !important;
        }

        .universal-product-card:hover .universal-product-title a {
            color: #092040 !important;
        }

        .universal-meta-info-row {
            display: flex !important;
            align-items: center !important;
            flex-wrap: wrap !important;
            gap: 6px !important;
            margin-top: 4px !important;
        }

        .universal-meta-tag {
            font-size: 10px !important;
            color: #64748b !important;
            font-weight: 500 !important;
            background-color: #f1f5f9 !important;
            padding: 1px 6px !important;
            border-radius: 4px !important;
            white-space: nowrap !important;
        }

        .universal-rating-row {
            display: flex !important;
            align-items: center !important;
            gap: 4px !important;
            margin-top: 6px !important;
            font-size: 11px !important;
        }

        .universal-stars {
            color: #f59e0b !important;
            display: flex !important;
            gap: 1px !important;
            font-size: 12px !important;
        }

        .universal-rating-score {
            color: #64748b !important;
            font-weight: 600 !important;
            font-size: 11px !important;
        }

        .universal-price-section {
            margin-top: 8px !important;
            margin-bottom: 10px !important;
            display: flex !important;
            align-items: baseline !important;
            gap: 8px !important;
            width: 100% !important;
            flex-shrink: 0 !important;
        }

        .universal-price-val {
            font-size: 17px !important;
            font-weight: 800 !important;
            color: #0f172a !important;
            line-height: 1 !important;
        }

        .universal-old-price {
            font-size: 12px !important;
            color: #94a3b8 !important;
            text-decoration: line-through !important;
            font-weight: 500 !important;
            line-height: 1 !important;
        }

        .universal-save-text {
            font-size: 10px !important;
            font-weight: 700 !important;
            color: #ef4444 !important;
            margin-left: auto !important;
        }

        .universal-button-section {
            width: 100% !important;
            flex-shrink: 0 !important;
            margin-top: auto !important;
        }

        .universal-add-to-cart-btn {
            width: 100% !important;
            background-color: #092040 !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            font-size: 12px !important;
            padding: 9px 16px !important;
            border-radius: 9999px !important;
            display: flex !important;
            align-items: center !important;
            justify-content: center !important;
            gap: 6px !important;
            transition: all 0.25s ease !important;
            box-shadow: 0 2px 4px rgba(15, 23, 42, 0.1) !important;
            border: none !important;
            cursor: pointer !important;
            text-decoration: none !important;
            line-height: 1.2 !important;
        }

        .universal-add-to-cart-btn:hover {
            background-color: #1e293b !important;
            box-shadow: 0 4px 8px rgba(15, 23, 42, 0.2) !important;
            transform: scale(1.01);
            color: #ffffff !important;
        }

        .universal-add-to-cart-btn.js-go-to-cart-btn {
            background-color: #059669 !important;
        }

        .universal-add-to-cart-btn.js-go-to-cart-btn:hover {
            background-color: #047857 !important;
        }
    </style>
@endonce

<div class="universal-product-card">
    {{-- 1. TOP SECTION (Image & Badges) --}}
    <div class="universal-image-container">
        <a href="{{ $url }}"
            style="display: flex; width: 100%; height: 100%; align-items: center; justify-content: center;">
            <img src="{{ $productImage }}" alt="{{ $productName }}" loading="lazy" class="universal-product-image"
                onerror="this.onerror=null; this.src='{{ $logoUrl }}';" />
        </a>

        {{-- Discount Badge (Top Left) --}}
        @if($discount > 0)
            <span class="universal-discount-badge">
                {{ $discount }}% OFF
            </span>
        @endif

        {{-- MBP Share Badge (Top Right) --}}
        <div class="universal-badge-stack">
            <span class="universal-badge-pill" title="MBP Share">
                <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg"
                    style="display: inline-block;">
                    <circle cx="12" cy="12" r="10" stroke="#ca8a04" stroke-width="2.5" fill="#facc15" />
                    <circle cx="12" cy="12" r="5" stroke="#ca8a04" stroke-width="1.5" stroke-dasharray="2 1.5"
                        fill="none" />
                </svg>
                {{ number_format($mbpShare, 2) }}
            </span>
        </div>
    </div>

    {{-- 2. MIDDLE SECTION (Brand, Title & Metadata) --}}
    <div class="universal-middle-section">
        {{-- Brand Name Header (Matching Reference Design) --}}
        <div class="universal-brand-name" title="{{ $brandName }}">
            {{ $brandName !== 'N/A' ? $brandName : ($categoryName !== 'N/A' ? $categoryName : 'Product') }}
        </div>

        {{-- Product Title --}}
        <h4 class="universal-product-title" title="{{ $productName }}">
            <a href="{{ $url }}">{{ $productName }}</a>
        </h4>

        {{-- Product Metadata (Code, Category, MOQ) --}}
        <div class="universal-meta-info-row">
            @if($productCode)
                <span class="universal-meta-tag">Code: {{ $productCode }}</span>
            @endif
            @if($categoryName !== 'N/A')
                <span class="universal-meta-tag">{{ $categoryName }}</span>
            @endif
            @if($moq > 1)
                <span class="universal-meta-tag">MOQ: {{ $moq }} {{ $unit }}</span>
            @endif
        </div>
    </div>

    {{-- 3. PRICE SECTION (Selling Price, MRP Strikethrough) --}}
    @php
        $rawUnitStr = data_get($product, 'unit_of_measure') ?: 'pc';
        $cleanedUnit = strtolower(trim(preg_replace('/^(per\s*|\/\s*)/i', '', $rawUnitStr)));
        $unitDisplay = '/ per ' . ($cleanedUnit ?: 'pc');
    @endphp
    <div class="universal-price-section">
        <span class="universal-price-val">
            ₹{{ number_format($price, 2) }}<span class="universal-unit-text" style="font-size: 11px; font-weight: 600; color: #64748b; margin-left: 2px;">{{ $unitDisplay }}</span>
        </span>
        @if($discount > 0)
            <span class="universal-old-price">
                ₹{{ number_format($mrp, 2) }}
            </span>
            <span class="universal-save-text">
                Save {{ $discount }}%
            </span>
        @endif
    </div>

    @php
        $cartProductIds = $cartProductIds ?? [];
        $isInCart = in_array($productId, $cartProductIds);
    @endphp

    {{-- 4. BOTTOM SECTION (Pill Add to Cart Button UI) --}}
    <div class="universal-button-section">
        @if($isInCart)
            <a href="{{ route('adc.cart.index') }}"
                class="universal-add-to-cart-btn js-go-to-cart-btn">
                <i class="hgi hgi-stroke hgi-shopping-cart-02" style="font-size: 15px;"></i>
                <span>Go to Cart</span>
            </a>
        @else
            <button type="button" class="universal-add-to-cart-btn js-add-to-cart" data-product-id="{{ $productId }}"
                data-moq="{{ $isDdcSourced ? 1 : $moq }}">
                <i class="hgi hgi-stroke hgi-shopping-cart-02" style="font-size: 15px;"></i>
                <span>Add to Cart</span>
            </button>
        @endif
    </div>
</div>

@once
    @push('scripts')
        <script>
            $(document).ready(function () {
                $(document).on('click', '.js-add-to-cart', function (e) {
                    e.preventDefault();
                    const $btn = $(this);
                    const productId = $btn.data('product-id');
                    const moq = $btn.data('moq') || 1;

                    // Show loading state
                    const originalHtml = $btn.html();
                    $btn.prop('disabled', true).css('opacity', '0.7');
                    $btn.find('span').text('Adding...');

                    $.post("{{ route('adc.cart.add') }}", {
                        _token: "{{ csrf_token() }}",
                        product_id: productId,
                        quantity: moq
                    }).done(function (res) {
                        Toastify({
                            text: res.success || "Added to cart successfully!",
                            duration: 5000,
                            close: true,
                            gravity: "top",
                            position: "right",
                            style: { background: "#10b981" }
                        }).showToast();
                        if (window.location.pathname.includes('/cart')) {
                            setTimeout(function () {
                                window.location.reload();
                            }, 800);
                            return;
                        }
                        if (res.cart_count !== undefined) {
                            $('.js-cart-count').text(res.cart_count).removeClass('hidden');
                            $('.js-cart-count-label').text(res.cart_count === 1 ? 'Item' : 'Items');
                        }
                        const $btnContainer = $btn.parent();
                        $btnContainer.html(`
                        <a href="{{ route('adc.cart.index') }}" class="universal-add-to-cart-btn js-go-to-cart-btn">
                            <i class="hgi hgi-stroke hgi-shopping-cart-02" style="font-size: 15px;"></i>
                            <span>Go to Cart</span>
                        </a>
                    `);
                    }).fail(function (xhr) {
                        if (xhr.status === 401) {
                            window.location.href = "{{ route('login', ['tab' => 'login']) }}";
                            return;
                        }
                        if (xhr.responseJSON && xhr.responseJSON.pincode_missing) {
                            const triggerBtn = document.getElementById('pincode-trigger-btn');
                            if (triggerBtn) {
                                triggerBtn.click();
                            }
                            return;
                        }
                        let msg = "Failed to add item to cart.";
                        if (xhr.responseJSON && xhr.responseJSON.error) {
                            msg = xhr.responseJSON.error;
                        }
                        Toastify({
                            text: msg,
                            duration: 5000,
                            close: true,
                            gravity: "top",
                            position: "right",
                            style: { background: "#ef4444" }
                        }).showToast();
                    }).always(function () {
                        $btn.prop('disabled', false).css('opacity', '1');
                        $btn.html(originalHtml);
                    });
                });
            });
        </script>
    @endpush
@endonce