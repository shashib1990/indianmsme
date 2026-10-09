@extends('web.layouts.app')

@section('title', 'Indian MSME Store — Checkout')

@push('styles')
  <style>
    /* Checkout Page Styling matching Cart Page 1:1 (#071933 theme) */
    .checkout-page-wrap {
      background-color: #f8fafc;
      padding: 30px 0 80px 0;
      font-family: 'Poppins', sans-serif;
    }

    .checkout-container {
      max-width: 100% !important;
      width: 100% !important;
      margin: 0 auto;
      padding: 0 40px;
      box-sizing: border-box;
    }

    @media (max-width: 768px) {
      .checkout-container {
        padding: 0 16px;
      }
    }

    /* Header Row */
    .checkout-header-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 24px;
    }

    .checkout-header-left {
      display: flex;
      align-items: center;
      gap: 12px;
    }

    .checkout-icon-badge {
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

    .checkout-title {
      font-size: 26px;
      font-weight: 800;
      color: #0f172a;
      margin: 0;
    }

    .checkout-count-pill {
      background-color: #e2e8f0;
      color: #071933;
      font-size: 12px;
      font-weight: 700;
      padding: 4px 14px;
      border-radius: 99px;
    }

    .back-to-cart-btn {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      color: #071933;
      font-size: 13.5px;
      font-weight: 700;
      text-decoration: none;
      transition: color 0.2s ease;
    }

    .back-to-cart-btn:hover {
      color: #0d285c;
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

    .qty-read-only-badge {
      border: 1px solid #e2e8f0;
      border-radius: 8px;
      background: #f8fafc;
      padding: 4px 14px;
      display: inline-block;
      font-size: 13.5px;
      font-weight: 700;
      color: #0f172a;
    }

    /* Right Column Cards */
    .checkout-card {
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 18px;
      padding: 20px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
      margin-bottom: 16px;
    }

    .checkout-card-title {
      font-size: 18px;
      font-weight: 800;
      color: #0f172a;
      margin-top: 0;
      margin-bottom: 16px;
      display: flex;
      align-items: center;
      gap: 8px;
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
      cursor: pointer;
      text-decoration: none;
      transition: background-color 0.2s ease;
    }

    .btn-checkout-primary:hover {
      background-color: #0d285c;
      color: #ffffff !important;
    }

    /* Trust Features Grid (Bottom Strip) */
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
  <div class="checkout-page-wrap">
    <div class="checkout-container">

      {{-- Session Error Alert --}}
      @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 text-red-700 rounded-xl flex items-center gap-x-2">
           <i class="hgi hgi-stroke hgi-alert-circle text-xl"></i>
           <span>{{ session('error') }}</span>
        </div>
      @endif

      {{-- Top Header Row: Icon + Checkout + Item Count Badge & Back to Cart --}}
      <div class="checkout-header-row">
        <div class="checkout-header-left">
          <div class="checkout-icon-badge">
            <i class="hgi hgi-stroke hgi-shopping-bag-02"></i>
          </div>
          <h1 class="checkout-title">Checkout</h1>
          <span class="checkout-count-pill">
            {{ count($items) }} {{ count($items) === 1 ? 'Item' : 'Items' }}
          </span>
        </div>

        <a href="{{ route('adc.cart.index') }}" class="back-to-cart-btn">
          <i class="hgi hgi-stroke hgi-arrow-left-01 text-base"></i>
          <span>Back to Cart</span>
        </a>
      </div>

      {{-- Main Checkout Grid --}}
      <div class="grid grid-cols-12 gap-6">

        {{-- LEFT COLUMN: Order Items (Grouped by Seller) --}}
        <div class="xl:col-span-8 col-span-12">

          @foreach(collect($items)->groupBy('seller_id') as $sellerId => $sellerItems)
            @php
              $firstItem = $sellerItems->first();
              $sellerName = $firstItem['seller_name'] ?? 'Verified Seller';
              $imvCode = $firstItem['imv_code'] ?? '';
              $groupMbpCoins = $sellerItems->where('available', true)->sum('total_mbp_coins');
            @endphp

            <div class="seller-card-box">

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
                      $imageSrc = filter_var($featuredImg, FILTER_VALIDATE_URL)
                          ? $featuredImg
                          : asset('storage/' . $featuredImg);
                  } else {
                      $imageSrc = $logoUrl;
                  }

                  $rawCartUnit = $item['unit_of_measure'] ?? 'pc';
                  $cleanCartUnit = strtolower(trim(preg_replace('/^(per\s*|\/\s*)/i', '', $rawCartUnit)));
                  $cartUnitDisplay = '/ Per ' . ucfirst($cleanCartUnit ?: 'Pc');
                @endphp

                <div class="cart-item-row">

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

                  {{-- Quantity (Read-only Pill Badge) --}}
                  <div class="item-qty-box">
                    <span class="qty-read-only-badge">
                      Qty: {{ $item['quantity'] }}
                    </span>
                  </div>

                  {{-- Total Item Price --}}
                  <div class="item-total-price-box">
                    <div style="font-size: 16px; font-weight: 800; color: #0f172a;">
                      ₹{{ number_format($item['total'], 2) }}
                    </div>
                  </div>

                </div>
              @endforeach

            </div>
          @endforeach

          {{-- Hidden Form for Checkout Submission --}}
          <form id="checkout-form" method="POST" action="{{ route('adc.checkout.store') }}" class="hidden">
            @csrf
            <input type="hidden" name="shipping_address"
              value="{{ Auth::guard('adc')->user()->profile?->address ?: 'ADC Default Location' }}">
            <input type="hidden" name="notes" value="Order placed via ADC Wallet.">
          </form>

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

        {{-- RIGHT COLUMN: Price Details + Advisor Code + Payment Method --}}
        <div class="xl:col-span-4 col-span-12">

          {{-- 1. Price Details Card --}}
          <div class="checkout-card">
            <h3 class="checkout-card-title">
              <i class="hgi hgi-stroke hgi-file-02" style="color: #071933;"></i>
              <span>Price Details</span>
            </h3>

            <div style="display: flex; flex-direction: column; gap: 12px; font-size: 13.5px; color: #64748b; padding-bottom: 16px; border-bottom: 1px solid #f1f5f9;">
              <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>Total Items</span>
                <span style="font-weight: 700; color: #0f172a;">{{ collect($items)->sum('quantity') }}</span>
              </div>

              <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>Subtotal <span style="font-size: 11px;">(excl. tax)</span></span>
                <span style="font-weight: 700; color: #0f172a;">₹{{ number_format($subtotalExclGst, 2) }}</span>
              </div>

              @if($totalGst > 0)
                <div style="display: flex; justify-content: space-between; align-items: center;">
                  <span>GST / Tax</span>
                  <span style="font-weight: 700; color: #0f172a;">+ ₹{{ number_format($totalGst, 2) }}</span>
                </div>
              @endif

              <div style="display: flex; justify-content: space-between; align-items: center;">
                <span>Shipping</span>
                <span style="font-weight: 700; color: #071933;">Free</span>
              </div>
            </div>

            @if($totalMbpCoins > 0)
              <div style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 10px 14px; margin-top: 14px; display: flex; justify-content: space-between; align-items: center;">
                <span style="display: flex; align-items: center; gap: 6px; font-size: 13px; font-weight: 700; color: #071933;">
                  <svg width="14" height="14" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="#ca8a04" stroke-width="2.5" fill="#facc15"/>
                    <circle cx="12" cy="12" r="5" stroke="#ca8a04" stroke-width="1.5" stroke-dasharray="2 1.5" fill="none"/>
                  </svg>
                  MBP Coins Earned
                </span>
                <span style="font-size: 14px; font-weight: 800; color: #071933;">
                  {{ number_format($totalMbpCoins, 2) }}
                </span>
              </div>
            @endif

            {{-- Total Amount Dark Navy Box (#071933) --}}
            <div class="summary-total-dark-box">
              <div>
                <div style="font-size: 14px; font-weight: 800; color: #ffffff;">Total Payable</div>
                @if($totalGst > 0)
                  <div style="font-size: 11px; color: #cbd5e1; margin-top: 1px;">(incl. ₹{{ number_format($totalGst, 2) }} GST)</div>
                @endif
              </div>
              <div style="font-size: 22px; font-weight: 800; color: #ffffff;">
                ₹{{ number_format($subtotal, 2) }}
              </div>
            </div>
          </div>

          {{-- 2. Advisor Code Card --}}
          <div class="checkout-card">
            <h3 class="checkout-card-title">
              <i class="hgi hgi-stroke hgi-user-check-01" style="color: #071933;"></i>
              <span>Advisor Code</span>
            </h3>
            <div>
              <select
                id="checkout-advisor-code"
                name="advisor_code"
                form="checkout-form"
                class="w-full form-control rounded-xl select-searchable"
                required
              >
                <option value="">Loading Advisor Codes...</option>
              </select>
            </div>
          </div>

          {{-- 3. Payment Method Card (ADC Wallet) --}}
          <div class="checkout-card" style="position: sticky; top: 20px;">
            <h3 class="checkout-card-title">
              <i class="hgi hgi-stroke hgi-wallet-02" style="color: #071933;"></i>
              <span>Payment Method</span>
            </h3>

            <div style="display: flex; flex-direction: column; gap: 16px;">
              {{-- Active Wallet Strip --}}
              <div style="display: flex; align-items: center; justify-content: space-between; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 12px 14px;">
                <div style="display: flex; align-items: center; gap: 10px;">
                  <div style="width: 36px; height: 36px; border-radius: 10px; background-color: #071933; color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 18px; flex-shrink: 0;">
                    <i class="hgi hgi-stroke hgi-wallet-02"></i>
                  </div>
                  <div>
                    <div style="font-size: 13.5px; font-weight: 700; color: #0f172a;">ADC Wallet</div>
                    <div style="font-size: 11px; color: #64748b;">Fast & Secure Checkout</div>
                  </div>
                </div>
                <span style="font-size: 11px; font-weight: 700; color: #166534; background: #dcfce7; padding: 2px 10px; border-radius: 99px;">
                  • Active
                </span>
              </div>

              {{-- Available Balance --}}
              <div style="display: flex; align-items: center; justify-content: space-between; font-size: 13.5px;">
                <span style="color: #64748b; font-weight: 500;">Available Balance:</span>
                <span style="font-size: 18px; font-weight: 800; color: #0f172a;">₹{{ number_format($walletBalance, 2) }}</span>
              </div>

              {{-- Insufficient Warning --}}
              @if($walletBalance < $subtotal)
                <div style="background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 12px 14px; font-size: 12px; color: #b91c1c;">
                  <div style="display: flex; align-items: flex-start; gap: 6px;">
                    <i class="hgi hgi-stroke hgi-alert-circle text-base mt-0.5 flex-shrink-0"></i>
                    <div>
                      Insufficient balance. This order requires <strong>₹{{ number_format($subtotal, 2) }}</strong>.
                    </div>
                  </div>
                  <a href="{{ route('adc.wallet.index') }}" target="_blank" style="color: #071933; font-weight: 700; text-decoration: underline; margin-top: 6px; display: inline-flex; align-items: center; gap: 4px;">
                    <i class="hgi hgi-stroke hgi-wallet-add-01 text-sm"></i>
                    Request Wallet Balance
                  </a>
                </div>
              @endif

              {{-- Submit Button --}}
              <button
                type="submit"
                form="checkout-form"
                class="btn-checkout-primary {{ $walletBalance < $subtotal ? 'opacity-50 cursor-not-allowed' : '' }}"
                {{ $walletBalance < $subtotal ? 'disabled' : '' }}
              >
                <span>Proceed with Wallet Payment</span>
                <i class="hgi hgi-stroke hgi-arrow-right-01"></i>
              </button>

            </div>
          </div>

        </div>

      </div>

    </div>
  </div>
@endsection

@push('scripts')
  <script>
    document.addEventListener("DOMContentLoaded", function() {
      var apiAmaCodes = "{{ route('api.ama-codes') }}";
      var $select = $('#checkout-advisor-code');

      // Destroy NiceSelect to prevent duplicate display
      try { if ($.fn.niceSelect) $select.niceSelect('destroy'); } catch(e){}
      $select.next('.nice-select').remove();
      $select.css({ display: 'block', visibility: 'visible' });

      // Initialize select2 with 100% width
      $select.select2({
        placeholder: "Select Advisor Code",
        allowClear: false,
        width: '100%'
      });

      // Load AMA codes from Abhhyam API
      fetch(apiAmaCodes)
        .then(function(r) { return r.json(); })
        .then(function(data) {
          $select.empty();

          var itemsList = [];
          if (data.status && Array.isArray(data.data)) {
            itemsList = data.data;
          } else if (Array.isArray(data)) {
            itemsList = data;
          }

          if (itemsList.length > 0) {
            itemsList.forEach(function(item, idx) {
              var code = item.code || item.name;
              var label = item.code ? (item.code + (item.name ? ' - ' + item.name : '')) : item.name;
              var isSelected = (idx === 0);
              var opt = new Option(label, code, isSelected, isSelected);
              $select.append(opt);
            });
            var topCode = itemsList[0].code || itemsList[0].name;
            $select.val(topCode).trigger('change');
          } else {
            $select.append(new Option('No Advisor Codes Found', '', true, true)).trigger('change');
          }
        })
        .catch(function(err) {
          console.error("Error loading advisor codes:", err);
          $select.empty().append(new Option('Failed to load Advisor Codes', '', true, true)).trigger('change');
        });
    });
  </script>
@endpush
