@extends('web.layouts.app')

@section('title', 'Indian MSME Store — My Cart')

@push('styles')
  <style>
    /* Custom Cart Page Styling matching UI mockup with #071933 theme */
    .cart-page-wrap {
      background-color: #f8fafc;
      padding: 30px 0 80px 0;
      font-family: 'Poppins', sans-serif;
    }

    .cart-container {
      max-width: 100% !important;
      width: 100% !important;
      margin: 0 auto;
      padding: 0 40px;
      box-sizing: border-box;
    }

    @media (max-width: 768px) {
      .cart-container {
        padding: 0 16px;
      }
    }

    /* Header Row */
    .cart-header-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
    }

    .cart-header-left {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .cart-icon-badge {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      background-color: #e2e8f0;
      color: #071933;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 22px;
      flex-shrink: 0;
    }

    .cart-title {
      font-size: 26px;
      font-weight: 800;
      color: #0f172a;
      margin: 0;
    }

    .cart-count-pill {
      background-color: #e2e8f0;
      color: #071933;
      font-size: 12px;
      font-weight: 700;
      padding: 4px 14px;
      border-radius: 99px;
    }

    /* Breadcrumb */
    .cart-breadcrumb {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 13px;
      color: #64748b;
      font-weight: 500;
    }

    .cart-breadcrumb a {
      color: #334155;
      text-decoration: none;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .cart-breadcrumb a:hover {
      color: #071933;
    }

    /* Seller Card Box - Compact */
    .seller-card-box {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      padding: 16px 20px;
      margin-bottom: 16px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }

    .seller-header-strip {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding-bottom: 12px;
      margin-bottom: 14px;
      border-bottom: 1px solid #f1f5f9;
    }

    .seller-info-wrap {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .seller-icon-badge {
      width: 32px;
      height: 32px;
      border-radius: 8px;
      background-color: #e2e8f0;
      color: #071933;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
      flex-shrink: 0;
    }

    .seller-name-title {
      font-size: 13.5px;
      font-weight: 700;
      color: #0f172a;
    }

    .view-shop-btn {
      border: 1px solid #071933;
      color: #071933 !important;
      border-radius: 8px;
      padding: 5px 14px;
      font-weight: 600;
      font-size: 12px;
      text-decoration: none;
      transition: all 0.2s ease;
    }

    .view-shop-btn:hover {
      background-color: #071933;
      color: #ffffff !important;
    }

    /* Cart Item Row Layout - Compact */
    .cart-item-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
      padding-bottom: 14px;
      margin-bottom: 14px;
      border-bottom: 1px solid #f1f5f9;
    }

    .cart-item-row:last-child {
      margin-bottom: 0;
      border-bottom: none;
    }

    .product-thumb-title-wrap {
      display: flex;
      align-items: center;
      gap: 14px;
      flex: 1.5;
    }

    .product-img-box {
      width: 75px;
      height: 75px;
      border-radius: 12px;
      object-fit: cover;
      background-color: #f8fafc;
      border: 1px solid #e2e8f0;
      flex-shrink: 0;
    }

    .product-title-txt {
      font-size: 14px;
      font-weight: 700;
      color: #0f172a;
      text-decoration: none;
      line-height: 1.3;
      display: block;
      margin-bottom: 3px;
    }

    .product-title-txt:hover {
      color: #071933;
    }

    .brand-subtext {
      font-size: 11.5px;
      color: #64748b;
      margin-bottom: 4px;
    }

    .mbp-coins-badge {
      background: #fef9c3;
      border: 1px solid #fef08a;
      color: #854d0e;
      font-size: 11px;
      font-weight: 700;
      padding: 2px 10px;
      border-radius: 99px;
      display: inline-flex;
      align-items: center;
      gap: 4px;
    }

    .item-unit-price-box {
      text-align: center;
      flex: 0.8;
    }

    .item-qty-box {
      text-align: center;
      flex: 1;
    }

    .item-total-price-box {
      text-align: right;
      flex: 0.9;
    }

    .item-action-box {
      flex: 0.4;
      display: flex;
      justify-content: flex-end;
    }

    /* Quantity Control Pill - Compact */
    .cart-qty-pill {
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      background: #ffffff;
      padding: 4px 10px;
      display: inline-flex;
      align-items: center;
      gap: 10px;
    }

    .cart-qty-btn {
      border: none;
      background: transparent;
      color: #334155;
      font-weight: 700;
      font-size: 15px;
      cursor: pointer;
      width: 20px;
      height: 20px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 0;
    }

    .cart-qty-btn:hover {
      color: #071933;
    }

    .delete-item-btn {
      border: 1px solid #e2e8f0;
      width: 32px;
      height: 32px;
      border-radius: 8px;
      background: #ffffff;
      color: #64748b;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
      cursor: pointer;
      transition: all 0.2s ease;
    }

    .delete-item-btn:hover {
      border-color: #ef4444;
      color: #ef4444;
      background-color: #fef2f2;
    }

    /* Bottom Savings / MBP Strip Inside Seller Card - Compact */
    .seller-bottom-banner {
      display: flex;
      align-items: center;
      justify-content: space-between;
      background: #f8fafc;
      border-radius: 12px;
      padding: 10px 16px;
      margin-top: 14px;
    }

    @media (max-width: 768px) {
      .seller-bottom-banner {
        flex-direction: column;
        gap: 12px;
        align-items: stretch;
      }

      .cart-item-row {
        flex-direction: column;
        align-items: flex-start;
      }

      .product-thumb-title-wrap {
        width: 100%;
      }

      .item-unit-price-box,
      .item-qty-box,
      .item-total-price-box,
      .item-action-box {
        text-align: left;
        width: 100%;
        justify-content: space-between;
      }
    }

    .savings-banner-left {
      display: flex;
      align-items: center;
      gap: 10px;
    }

    .savings-tag-icon {
      width: 32px;
      height: 32px;
      border-radius: 50%;
      background-color: #e2e8f0;
      color: #071933;
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 16px;
      flex-shrink: 0;
    }

    .mbp-earned-box {
      border: 1.5px dashed #071933;
      background-color: #f8fafc;
      border-radius: 10px;
      padding: 6px 14px;
      display: flex;
      align-items: center;
      gap: 10px;
    }

    /* Right Column Styling - Compact */
    .free-shipping-card {
      background: linear-gradient(135deg, #071933 0%, #0d285c 100%);
      border-radius: 16px;
      padding: 16px 18px;
      color: #ffffff;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      justify-content: space-between;
      box-shadow: 0 4px 14px rgba(7, 25, 51, 0.25);
    }

    .order-summary-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 18px;
      padding: 20px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }

    .summary-total-dark-box {
      background-color: #071933;
      border-radius: 12px;
      padding: 14px 18px;
      color: #ffffff;
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin: 14px 0;
    }

    .btn-checkout-primary {
      width: 100%;
      background-color: #071933;
      color: #ffffff !important;
      border-radius: 10px;
      padding: 12px 18px;
      font-size: 14.5px;
      font-weight: 700;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      border: none;
      text-decoration: none;
      transition: background-color 0.2s ease;
    }

    .btn-checkout-primary:hover {
      background-color: #0d285c;
      color: #ffffff !important;
    }

    .btn-continue-shopping {
      width: 100%;
      background-color: #ffffff;
      border: 1px solid #e2e8f0;
      color: #0f172a !important;
      border-radius: 10px;
      padding: 11px 18px;
      font-size: 13.5px;
      font-weight: 700;
      text-align: center;
      display: block;
      margin-top: 10px;
      text-decoration: none;
      transition: background-color 0.2s ease;
    }

    .btn-continue-shopping:hover {
      background-color: #f8fafc;
      color: #0f172a !important;
    }

    /* Trust Features Grid (Bottom Strip) - Compact */
    .trust-features-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      padding: 18px 20px;
      margin-top: 18px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }

    .trust-features-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      gap: 20px;
    }

    @media (max-width: 992px) {
      .trust-features-grid {
        grid-template-columns: repeat(2, 1fr);
      }
    }

    @media (max-width: 576px) {
      .trust-features-grid {
        grid-template-columns: repeat(1, 1fr);
      }
    }
  </style>
@endpush

@section('content')
  <div class="cart-page-wrap">
    <div class="cart-container">

      {{-- Top Header Row: Icon + My Cart + Item Count Badge --}}
      <div class="cart-header-row">
        <div class="cart-header-left">
          <div class="cart-icon-badge">
            <i class="hgi hgi-stroke hgi-shopping-cart-02"></i>
          </div>
          <h1 class="cart-title">My Cart</h1>
          <span class="cart-count-pill cart-items-count-header">
            {{ count($items) }} {{ count($items) === 1 ? 'Item' : 'Items' }}
          </span>
        </div>
      </div>

      {{-- Main Cart Grid (Left: Seller Cards & Bottom Features | Right: Shipping & Order Summary) --}}
      <div class="grid grid-cols-12 gap-6">

        {{-- Left Column (Cart Items grouped by Seller) --}}
        <div class="xl:col-span-8 col-span-12">

          @forelse(collect($items)->groupBy('seller_id') as $sellerId => $sellerItems)
            @php
              $firstItem = $sellerItems->first();
              $sellerName = $firstItem['seller_name'] ?? 'Verified Seller';
              $imvCode = $firstItem['imv_code'] ?? '';
              
              // Calculate group savings if MRP is present
              $sellerSavings = 0;
              foreach($sellerItems as $sItem) {
                if (isset($sItem['mrp']) && $sItem['mrp'] > $sItem['price']) {
                  $sellerSavings += ($sItem['mrp'] - $sItem['price']) * $sItem['quantity'];
                }
              }
              
              $groupMbpCoins = $sellerItems->where('available', true)->sum('total_mbp_coins');
            @endphp

            <div class="seller-card-box" data-seller-card="{{ $sellerId }}">

              {{-- 1. Seller Header Strip --}}
              <div class="seller-header-strip">
                <div class="seller-info-wrap">
                  <div class="seller-icon-badge">
                    <i class="hgi hgi-stroke hgi-store-01"></i>
                  </div>
                  <div>
                    <span style="font-size: 11px; color: #64748b; font-weight: 500;">Sold by</span>
                    <div class="seller-name-title">
                      {{ $sellerName }} {{ $imvCode ? '('.$imvCode.')' : '' }}
                    </div>
                  </div>
                </div>

                @if($imvCode && $imvCode !== '—')
                  <a href="{{ route('vendor.products', ['imv_code' => $imvCode]) }}" class="view-shop-btn">
                    View Shop
                  </a>
                @endif
              </div>

              {{-- 2. Seller Products List --}}
              @foreach($sellerItems as $item)
                @php
                  $featuredImg = $item['image'];
                  $logoUrl = asset('admin-assets/images/IndianMSME.png');
                  if ($featuredImg) {
                      if (filter_var($featuredImg, FILTER_VALIDATE_URL)) {
                          $imageSrc = $featuredImg;
                      } else {
                          $imageSrc = asset('storage/' . $featuredImg);
                      }
                  } else {
                      $imageSrc = $logoUrl;
                  }

                  $rawCartUnit = $item['unit_of_measure'] ?? 'pc';
                  $cleanCartUnit = strtolower(trim(preg_replace('/^(per\s*|\/\s*)/i', '', $rawCartUnit)));
                  $cartUnitDisplay = '/ Per ' . ucfirst($cleanCartUnit ?: 'Pc');
                @endphp

                <div class="cart-item-row" data-item-id="{{ $item['id'] }}" data-seller-group="{{ $sellerId }}" {!! !$item['available'] ? 'style="background-color: #fef2f2 !important; border-left: 4px solid #ef4444 !important; padding: 12px; border-radius: 12px;"' : '' !!}>

                  {{-- Product Image + Details --}}
                  <div class="product-thumb-title-wrap">
                    <img src="{{ $imageSrc }}" alt="{{ $item['product_name'] }}" class="product-img-box">
                    <div>
                      <a href="{{ route('product.show', ['id' => $item['product_id']]) }}" class="product-title-txt">
                        {{ $item['product_name'] }}
                      </a>

                      @if(!empty($item['brand']))
                        <div class="brand-subtext">Brand: {{ $item['brand'] }}</div>
                      @endif

                      @if($item['mbp_coins'] > 0)
                        <div class="mbp-coins-badge">
                          <svg width="12" height="12" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <circle cx="12" cy="12" r="10" stroke="#ca8a04" stroke-width="2.5" fill="#facc15"/>
                            <circle cx="12" cy="12" r="5" stroke="#ca8a04" stroke-width="1.5" stroke-dasharray="2 1.5" fill="none"/>
                          </svg>
                          <span>{{ number_format($item['mbp_coins'], 2) }} MBP/Unit</span>
                        </div>
                      @endif

                      @if(!$item['available'])
                        <div style="font-size: 11px; font-weight: 700; color: #b91c1c; background: #fee2e2; padding: 2px 8px; border-radius: 6px; display: inline-block; margin-top: 4px;">
                          Currently Unavailable
                        </div>
                      @endif
                    </div>
                  </div>

                  {{-- Unit Price --}}
                  <div class="item-unit-price-box">
                    <div style="font-size: 15px; font-weight: 800; color: #0f172a;">
                      ₹{{ number_format($item['price'], 2) }}
                    </div>
                    <div style="font-size: 11.5px; color: #64748b; font-weight: 500;">
                      {{ $cartUnitDisplay }}
                    </div>
                  </div>

                  {{-- Quantity Selector --}}
                  <div class="item-qty-box">
                    <form action="{{ route('adc.cart.update') }}" method="POST" class="cart-update-form flex flex-col items-center">
                      @csrf
                      <input type="hidden" name="cart_item_id" value="{{ $item['id'] }}">
                      <div class="cart-qty-pill" {!! !$item['available'] ? 'style="opacity: 0.5; pointer-events: none;"' : '' !!}>
                        <button type="button" class="quantity-dec cart-qty-btn" {{ !$item['available'] ? 'disabled' : '' }}>-</button>
                        <input type="text" name="quantity" readonly value="{{ $item['quantity'] }}"
                          data-step="{{ $item['step_quantity'] }}" data-min="{{ $item['step_quantity'] }}"
                          class="quantity-input" style="width: 26px; text-align: center; border: none; outline: none; background: transparent; font-weight: 800; font-size: 14px; color: #0f172a;"
                          {{ !$item['available'] ? 'disabled' : '' }}>
                        <button type="button" class="quantity-inc cart-qty-btn" {{ !$item['available'] ? 'disabled' : '' }}>+</button>
                      </div>

                      @if(!$item['is_ddc_inventory'] && $item['case_pack_quantity'] > 1)
                        <div style="font-size: 11px; color: #64748b; font-weight: 500; margin-top: 4px;">
                          (Min Pack: {{ $item['case_pack_quantity'] }} Units)
                        </div>
                      @endif
                    </form>
                  </div>

                  {{-- Total Item Price --}}
                  <div class="item-total-price-box">
                    <div class="item-total-val" style="font-size: 17px; font-weight: 800; color: #0f172a;">
                      ₹{{ number_format($item['item_total'] ?? $item['total'] ?? ($item['price'] * $item['quantity']), 2) }}
                    </div>
                  </div>

                  {{-- Action: Delete Item --}}
                  <div class="item-action-box">
                    <form action="{{ route('adc.cart.remove') }}" method="POST" class="cart-remove-form">
                      @csrf
                      <input type="hidden" name="cart_item_id" value="{{ $item['id'] }}">
                      <button type="submit" class="delete-item-btn" title="Remove item">
                        <i class="hgi hgi-stroke hgi-delete-01"></i>
                      </button>
                    </form>
                  </div>

                </div>
              @endforeach

              {{-- 3. Bottom Savings & MBP Banner Strip --}}
              <div class="seller-bottom-banner">
                <div class="savings-banner-left">
                  <div class="savings-tag-icon">
                    <i class="hgi hgi-stroke hgi-discount-01"></i>
                  </div>
                  <div>
                    <span style="font-size: 13.5px; font-weight: 700; color: #0f172a;">Great Choice!</span>
                    <div style="font-size: 12px; color: #64748b;" class="seller-savings-text">
                      @if($sellerSavings > 0)
                        You are saving <strong style="color: #071933; font-weight: 700;">₹{{ number_format($sellerSavings, 2) }}</strong> on this order.
                      @else
                        You are getting the best verified prices on this order.
                      @endif
                    </div>
                  </div>
                </div>

                <div class="mbp-earned-box seller-mbp-box {{ $groupMbpCoins <= 0 ? 'hidden' : '' }}">
                  <div style="width: 32px; height: 32px; border-radius: 8px; background-color: #071933; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 16px;">
                    <i class="hgi hgi-stroke hgi-wallet-02"></i>
                  </div>
                  <div>
                    <div style="font-size: 11px; font-weight: 600; color: #071933; text-transform: uppercase;">MBP Coins Earned</div>
                    <div class="seller-mbp-value" style="font-size: 15px; font-weight: 800; color: #071933;">
                      {{ number_format($groupMbpCoins, 2) }}
                    </div>
                  </div>
                </div>
              </div>

            </div>
          @empty
            <div class="seller-card-box text-center py-16">
              <div class="flex flex-col items-center gap-y-4">
                <div style="width: 70px; height: 70px; border-radius: 50%; background: #e2e8f0; color: #071933; display: flex; align-items: center; justify-content: center; font-size: 32px;">
                  <i class="hgi hgi-stroke hgi-shopping-bag-02"></i>
                </div>
                <h4 style="font-size: 20px; font-weight: 800; color: #0f172a; margin: 0;">Your Cart is Empty</h4>
                <p style="font-size: 14px; color: #64748b; margin: 0;">Explore thousands of products from verified MSME sellers.</p>
                <a href="{{ route('shop') }}" class="btn-checkout-primary" style="max-width: 220px; margin-top: 10px;">
                  Shop Now
                </a>
              </div>
            </div>
          @endforelse

          {{-- Guarantee / Trust Features Bottom Strip --}}
          <div class="trust-features-card">
            <div class="trust-features-grid">
              
              <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background-color: #e2e8f0; color: #071933; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                  <i class="hgi hgi-stroke hgi-container-truck"></i>
                </div>
                <div>
                  <div style="font-size: 14px; font-weight: 700; color: #0f172a;">Free Shipping</div>
                  <div style="font-size: 11.5px; color: #64748b; line-height: 1.35; margin-top: 2px;">
                    Enjoy the convenience of Free Shipping on every order.
                  </div>
                </div>
              </div>

              <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background-color: #e2e8f0; color: #071933; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                  <i class="hgi hgi-stroke hgi-customer-support"></i>
                </div>
                <div>
                  <div style="font-size: 14px; font-weight: 700; color: #0f172a;">24x7 Support</div>
                  <div style="font-size: 11.5px; color: #64748b; line-height: 1.35; margin-top: 2px;">
                    Round-the-clock assistance, anytime you need it.
                  </div>
                </div>
              </div>

              <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background-color: #e2e8f0; color: #071933; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                  <i class="hgi hgi-stroke hgi-delivery-return-02"></i>
                </div>
                <div>
                  <div style="font-size: 14px; font-weight: 700; color: #0f172a;">Easy Returns</div>
                  <div style="font-size: 11.5px; color: #64748b; line-height: 1.35; margin-top: 2px;">
                    Hassle-free return policy for damaged or defective items.
                  </div>
                </div>
              </div>

              <div style="display: flex; align-items: center; gap: 12px;">
                <div style="width: 44px; height: 44px; border-radius: 50%; background-color: #e2e8f0; color: #071933; display: flex; align-items: center; justify-content: center; font-size: 20px; flex-shrink: 0;">
                  <i class="hgi hgi-stroke hgi-transaction"></i>
                </div>
                <div>
                  <div style="font-size: 14px; font-weight: 700; color: #0f172a;">Secure Payment</div>
                  <div style="font-size: 11.5px; color: #64748b; line-height: 1.35; margin-top: 2px;">
                    Seamless shopping backed by safe and secure payment options.
                  </div>
                </div>
              </div>

            </div>
          </div>

        </div>

        {{-- Right Column (Free Shipping Card & Order Summary Card) --}}
        <div class="xl:col-span-4 col-span-12">

          {{-- 1. Free Shipping Promo Banner --}}
          <div class="free-shipping-card">
            <div style="display: flex; align-items: center; gap: 12px;">
              <div style="width: 40px; height: 40px; border-radius: 50%; background-color: rgba(255, 255, 255, 0.15); display: flex; align-items: center; justify-content: center; font-size: 20px;">
                <i class="hgi hgi-stroke hgi-container-truck"></i>
              </div>
              <div>
                <div style="font-size: 16px; font-weight: 800; color: #ffffff;">Free Shipping</div>
                <div style="font-size: 12px; color: #cbd5e1; margin-top: 1px;">Yay! You get free shipping on this order.</div>
              </div>
            </div>
            <div style="font-size: 24px; opacity: 0.85;">
              🎁
            </div>
          </div>

          {{-- 2. Order Summary Card --}}
          <div class="order-summary-card">
            <h3 style="font-size: 20px; font-weight: 800; color: #0f172a; margin-top: 0; margin-bottom: 18px;">
              Order Summary
            </h3>

            <div style="display: flex; flex-direction: column; gap: 12px; font-size: 13.5px; color: #64748b; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9;">
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>Total Items</span>
                <span class="summary-total-items" style="font-weight: 700; color: #0f172a;">{{ count($items) }}</span>
              </div>
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>Total Quantity</span>
                <span class="summary-total-qty" style="font-weight: 700; color: #0f172a;">{{ collect($items)->sum('quantity') }}</span>
              </div>
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>Shipping</span>
                <span style="font-weight: 700; color: #071933;">Free</span>
              </div>
            </div>

            @php $totalMbpCoins = collect($items)->where('available', true)->sum('total_mbp_coins'); @endphp
            <div class="summary-mbp-wrapper {{ $totalMbpCoins <= 0 ? 'hidden' : '' }}" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px 14px; margin-top: 14px; display: flex; justify-content: space-between; align-items: center;">
              <span style="display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: #071933;">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                  <circle cx="12" cy="12" r="10" stroke="#ca8a04" stroke-width="2.5" fill="#facc15"/>
                  <circle cx="12" cy="12" r="5" stroke="#ca8a04" stroke-width="1.5" stroke-dasharray="2 1.5" fill="none"/>
                </svg>
                MBP Coins Earned
              </span>
              <span class="summary-mbp-coins" style="font-size: 14px; font-weight: 800; color: #071933;">
                {{ number_format($totalMbpCoins, 2) }}
              </span>
            </div>

            {{-- Total Amount Dark Navy Box (#071933) --}}
            <div class="summary-total-dark-box">
              <div>
                <div style="font-size: 15px; font-weight: 800; color: #ffffff;">Total Amount</div>
                <div style="font-size: 11px; color: #cbd5e1; margin-top: 1px;">(Inclusive of all taxes)</div>
              </div>
              <div class="summary-total-amount" style="font-size: 22px; font-weight: 800; color: #ffffff;">
                ₹{{ number_format($subtotal, 2) }}
              </div>
            </div>

            {{-- Checkout & Continue Buttons --}}
            @if(count($items) > 0)
              <a href="{{ route('adc.checkout.index') }}" class="btn-checkout-primary">
                <span>Proceed to Checkout</span>
                <i class="hgi hgi-stroke hgi-arrow-right-01"></i>
              </a>
            @else
              <button disabled class="btn-checkout-primary" style="opacity: 0.5; cursor: not-allowed;">
                <span>Proceed to Checkout</span>
              </button>
            @endif

            <a href="{{ route('shop') }}" class="btn-continue-shopping">
              Continue Shopping
            </a>

            {{-- Bottom Trust Icons --}}
            <div style="display: flex; align-items: center; justify-content: space-between; margin-top: 18px; font-size: 11px; color: #64748b; font-weight: 600;">
              <span style="display: flex; align-items: center; gap: 4px;">
                <i class="hgi hgi-stroke hgi-security-check" style="color: #071933;"></i> Secure Payment
              </span>
              <span style="display: flex; align-items: center; gap: 4px;">
                <i class="hgi hgi-stroke hgi-arrow-reload-01" style="color: #071933;"></i> Easy Returns
              </span>
              <span style="display: flex; align-items: center; gap: 4px;">
                <i class="hgi hgi-stroke hgi-shield-check" style="color: #071933;"></i> 100% Safe
              </span>
            </div>

          </div>

        </div>

      </div>

    </div>
  </div>
@endsection

@push('scripts')
<script>
$(document).ready(function() {
    // Helper function to update all total and count labels on the page
    function updateCartUI(data) {
        $('.summary-total-items').text(data.cart_count);
        $('.summary-total-qty').text(data.total_qty);
        
        let subtotalVal = 0;
        if (data.subtotal) {
            subtotalVal = parseFloat(String(data.subtotal).replace(/,/g, ''));
        }
        $('.summary-total-amount').text('₹' + (isNaN(subtotalVal) ? '0.00' : subtotalVal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2})));
        
        let mbpVal = 0;
        if (data.total_mbp_coins) {
            mbpVal = parseFloat(String(data.total_mbp_coins).replace(/,/g, ''));
        }
        if (!isNaN(mbpVal) && mbpVal > 0) {
            $('.summary-mbp-wrapper').removeClass('hidden');
            $('.summary-mbp-coins').text(mbpVal.toLocaleString('en-IN', {minimumFractionDigits: 2, maximumFractionDigits: 2}));
        } else {
            $('.summary-mbp-wrapper').addClass('hidden');
        }
        
        $('.cart-items-count-header').text(data.cart_count + ' ' + (data.cart_count === 1 ? 'Item' : 'Items'));
        
        $('.js-cart-count').text(data.cart_count);
        if (parseInt(data.cart_count) > 0) {
            $('.js-cart-count').removeClass('hidden');
        } else {
            $('.js-cart-count').addClass('hidden');
        }

        if (data.cart_count === 1) {
            $('.js-cart-count-label').text('Item');
        } else {
            $('.js-cart-count-label').text('Items');
        }

        // Dynamically update seller group level MBP coins & Great Choice savings
        if (data.seller_id) {
            let $sellerCard = $('[data-seller-card="' + data.seller_id + '"]');
            if ($sellerCard.length) {
                if (data.group_mbp_coins !== undefined) {
                    let groupMbp = parseFloat(String(data.group_mbp_coins).replace(/,/g, ''));
                    if (!isNaN(groupMbp) && groupMbp > 0) {
                        $sellerCard.find('.seller-mbp-box').removeClass('hidden');
                        $sellerCard.find('.seller-mbp-value').text(data.group_mbp_coins);
                    } else {
                        $sellerCard.find('.seller-mbp-box').addClass('hidden');
                    }
                }

                if (data.group_savings !== undefined) {
                    let groupSavings = parseFloat(String(data.group_savings).replace(/,/g, ''));
                    if (!isNaN(groupSavings) && groupSavings > 0) {
                        $sellerCard.find('.seller-savings-text').html(
                            'You are saving <strong style="color: #071933; font-weight: 700;">₹' + data.group_savings + '</strong> on this order.'
                        );
                    } else {
                        $sellerCard.find('.seller-savings-text').text('You are getting the best verified prices on this order.');
                    }
                }
            }
        }

        if (parseInt(data.cart_count) === 0) {
            location.reload();
        }
    }

    // Handle Quantity Inc/Dec clicks
    $(document).on('click', '.quantity-inc', function(e) {
        e.preventDefault();
        let $input = $(this).siblings('.quantity-input');
        let step = parseInt($input.attr('data-step')) || 1;
        let currentVal = parseInt($input.val()) || step;
        $input.val(currentVal + step);
        $(this).closest('form').submit();
    });

    $(document).on('click', '.quantity-dec', function(e) {
        e.preventDefault();
        let $input = $(this).siblings('.quantity-input');
        let step = parseInt($input.attr('data-step')) || 1;
        let minQty = parseInt($input.attr('data-min')) || step;
        let currentVal = parseInt($input.val()) || step;
        if (currentVal - step >= minQty) {
            $input.val(currentVal - step);
            $(this).closest('form').submit();
        }
    });

    // Ajax Quantity Update Form Submit
    $(document).on('submit', '.cart-update-form', function(e) {
        e.preventDefault();
        let $form = $(this);
        let url = $form.attr('action');
        let data = $form.serialize();
        
        $form.find('button').prop('disabled', true);

        $.ajax({
            url: url,
            method: 'POST',
            data: data,
            dataType: 'json',
            success: function(response) {
                $form.find('button').prop('disabled', false);
                if (response.success) {
                    $form.closest('.cart-item-row').find('.item-total-val').text('₹' + response.item_total);
                    updateCartUI(response);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: response.error || 'Failed to update quantity.',
                        confirmButtonColor: '#071933'
                    });
                }
            },
            error: function(xhr) {
                $form.find('button').prop('disabled', false);
                let msg = 'Failed to update quantity.';
                if (xhr.responseJSON && xhr.responseJSON.error) {
                    msg = xhr.responseJSON.error;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: msg,
                    confirmButtonColor: '#071933'
                }).then(() => {
                    location.reload();
                });
            }
        });
    });

    // Ajax Remove Item Form Submit
    $(document).on('submit', '.cart-remove-form', function(e) {
        e.preventDefault();
        let $form = $(this);
        let url = $form.attr('action');
        let data = $form.serialize();
        let $row = $form.closest('.cart-item-row');

        let performRemove = function() {
            $form.find('button').prop('disabled', true);

            $.ajax({
                url: url,
                method: 'POST',
                data: data,
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(300, function() {
                            let sellerGroup = $row.attr('data-seller-group');
                            $row.remove();
                            if ($('.cart-item-row[data-seller-group="' + sellerGroup + '"]').length === 0) {
                                $('[data-seller-card="' + sellerGroup + '"]').fadeOut(300, function() {
                                    $(this).remove();
                                });
                            }
                            updateCartUI(response);
                        });
                    } else {
                        $form.find('button').prop('disabled', false);
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'Oops...',
                                text: response.error || 'Failed to remove item.',
                                confirmButtonColor: '#071933'
                            });
                        } else {
                            alert(response.error || 'Failed to remove item.');
                        }
                    }
                },
                error: function(xhr) {
                    $form.find('button').prop('disabled', false);
                    let msg = 'Failed to remove item.';
                    if (xhr.responseJSON && xhr.responseJSON.error) {
                        msg = xhr.responseJSON.error;
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: msg,
                            confirmButtonColor: '#071933'
                        });
                    } else {
                        alert(msg);
                    }
                }
            });
        };

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Are you sure?',
                text: 'Are you sure you want to remove this item?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#071933',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, remove it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    performRemove();
                }
            });
        } else {
            if (confirm('Are you sure you want to remove this item?')) {
                performRemove();
            }
        }
    });
});
</script>
@endpush
