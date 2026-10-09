@extends('web.layouts.app')

@php
  $productId = $product->id;
  $mrp = (float) ($product->mrp ?? 0);

  // Determine active sub-seller pricing context
  $subSellerId = request()->input('seller') ?: (auth('adc')->check() ? optional(auth('adc')->user()->adcProfile)->code : null);
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

  // DDC Inventory Priority Logic based on active session pincode
  $ddcPricingDetails = \App\Services\DdcInventoryService::resolveProductDetails($product, session('user_pincode'));

  if ($ddcPricingDetails['is_ddc_inventory']) {
    $price = $ddcPricingDetails['selling_price'];
    $mrp = $ddcPricingDetails['mrp'];
    $mbpShare = (float) (data_get($ddcPricingDetails['ddc_inventory'], 'mbp_90_share') ?: ($product->mbp_90_share ?: 0.00));
    $isDdcSourced = true;
  } else {
    if ($subSellerProduct) {
      $price = (float) $subSellerProduct->adc_purchase_price;
      $mbpShare = (float) $subSellerProduct->mbp_90_share;
    } else {
      $price = (float) ($product->adc_purchase_price ?: $product->mrp ?: 0);
      $mbpShare = (float) ($product->mbp_90_share ?: 0.00);
    }
    $isDdcSourced = false;
  }

  $discount = ($mrp > 0 && $mrp > $price) ? round((($mrp - $price) / $mrp) * 100) : 0;

  $productName = $product->product_name;

  // Product image fallback logic
  $featuredImg = $product->featured_image;
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

  $brandName = optional($product->brand)->name ?: 'N/A';
  $categoryName = optional($product->category)->name ?: 'N/A';
  $unit = $product->unit_of_measure ?: 'Pc';
  $moq = $product->case_pack_quantity ?: 1;

  // Build array of images for slider
  $images = [];
  if ($productImage !== $logoUrl) {
    $images[] = $productImage;
  }

  $galleryList = is_array($product->gallery) ? $product->gallery : (json_decode($product->gallery, true) ?: []);
  foreach ($galleryList as $gImg) {
    if ($gImg) {
      if (filter_var($gImg, FILTER_VALIDATE_URL)) {
        $images[] = $gImg;
      } else {
        $storagePath = 'storage/' . $gImg;
        if (file_exists(public_path($storagePath))) {
          $images[] = asset($storagePath);
        }
      }
    }
  }

  if (empty($images)) {
    $images[] = $logoUrl;
  }

  // Keep only unique URLs
  $images = array_unique($images);
@endphp

@section('title', $productName . ' — Indian MSME Store')
@section('meta_description', Str::limit(strip_tags($product->description), 155))

@section('content')

  <!-- ========== Breadcrumb Section Start ========== -->
  <div class="py-12">
    <div class="container">
      <div class="breadcrumb">
        <ul>
          <li>
            <a href="{{ route('home') }}">
              <span>
                <i class="hgi hgi-stroke hgi-home-01 text-[20px]"></i>
              </span>
              Home
            </a>
          </li>
          <li class="text-light-disabled-text">&#8226;</li>
          <li>
            <a href="{{ route('vendor-marketplace') }}">Marketplace</a>
          </li>
          <li class="text-light-disabled-text">&#8226;</li>
          <li><span>{{ $productName }}</span></li>
        </ul>
      </div>
    </div>
  </div>
  <!-- ========== Breadcrumb Section End ========== -->

  <!-- ========== Product Details Section Start ========== -->
  <section>
    <div class="container">
      <div class="lg:grid lg:grid-cols-12 lg:gap-12 items-start">
        <div class="xl:col-span-6 lg:col-span-6">
          <div class="flex gap-x-6 xl:flex-row flex-col gap-y-6 xl:gap-y-0 xl:gap-x-6">

            {{-- Small Thumbnails Slider --}}
            <div class="xl:w-[114px] lg:w-full order-2 xl:order-1 flex-none overflow-hidden">
              <div class="product-details-small-slider -m-2.5 hitesh-slider"
                data-slick='{"slidesToShow": 6, "slidesToScroll": 1, "arrows": false, "infinite": true, "loop": true, "vertical": true, "verticalSwiping": true, "asNavFor": ".product-details-big-slider", "focusOnSelect": true, "responsive": [{"breakpoint": 1025, "settings": {"slidesToShow": 4, "vertical": false, "verticalSwiping": false}}, {"breakpoint": 769, "settings": {"slidesToShow": 3, "vertical": false, "verticalSwiping": false}}, {"breakpoint": 480, "settings": {"slidesToShow": 2, "vertical": false, "verticalSwiping": false}}]}'>
                @foreach($images as $img)
                  <div class="single-product-small-slider-item p-2.5 rounded-lg overflow-hidden border border-gray-200">
                    <img src="{{ $img }}" alt="{{ $productName }}" class="w-full h-full object-contain rounded-lg"
                      style="aspect-ratio: 1/1;" onerror="this.onerror=null; this.src='{{ $logoUrl }}';" />
                  </div>
                @endforeach
              </div>
            </div>

            {{-- Large Big Slider --}}
            <div
              class="w-full xl:flex-1 order-1 xl:order-2 overflow-hidden relative border border-gray-200 rounded-3xl p-4 bg-white"
              style="height: 382px;">
              <div class="product-details-big-slider -mx-3 hitesh-slider"
                data-slick='{"slidesToShow": 1, "slidesToScroll": 1, "arrows": true, "asNavFor": ".product-details-small-slider", "appendArrows": ".product-details-big-slider-nav", "responsive": [{"breakpoint": 769, "settings": {"slidesToShow": 1, "arrows": false}}]}'>
                @foreach($images as $img)
                  <div
                    class="single-product-big-slider-item px-3 rounded-2xl overflow-hidden flex items-center justify-center bg-white"
                    style="height: 350px;">
                    <img src="{{ $img }}" alt="{{ $productName }}" class="max-w-full max-h-full object-contain rounded-3xl"
                      style="height: 100%; width: 100%; object-fit: contain; max-height: 350px;"
                      onerror="this.onerror=null; this.src='{{ $logoUrl }}';" />
                  </div>
                @endforeach
              </div>
              <div
                class="product-details-big-slider-nav absolute top-1/2 -translate-y-1/2 left-0 right-0 flex items-center justify-between px-3">
              </div>
            </div>

          </div>
        </div>

        <div class="xl:col-span-6 lg:col-span-6 mt-6 lg:mt-0">
          <div class="rounded-3xl border border-gray-300 p-6 bg-white">

            {{-- Discount Badge (if any) --}}
            @if($discount > 0)
              <span
                class="product-discount-badge inline-block mb-6 uppercase relative bg-error text-white font-medium text-sm leading-[22px] px-2 py-0.5 rounded">
                {{ $discount }}% OFF
              </span>
            @endif

            <div class="product-title-section flex items-center justify-between mb-4">
              <h3 class="text-2xl font-bold text-gray-800 leading-tight">
                {{ $productName }}
              </h3>
            </div>

            {{-- Meta (Brand & Category) --}}
            <div class="flex flex-wrap gap-x-4 gap-y-1 mb-6 text-sm text-gray-500">
              <span>Brand: <strong class="text-gray-800">{{ $brandName }}</strong></span>
              <span>•</span>
              <span>Category: <strong class="text-gray-800">{{ $categoryName }}</strong></span>
              @if($subSeller)
                <span>•</span>
                <span>Seller: <strong
                    class="text-gray-800">{{ $subSeller->company_name ?: $subSeller->name }}</strong></span>
              @elseif(optional($product->seller)->name)
                <span>•</span>
                <span>Seller: <strong
                    class="text-gray-800">{{ $product->seller->company_name ?: $product->seller->name }}</strong></span>
              @endif
            </div>

            {{-- Pricing Section --}}
            @php
              $rawUnitStr = $product->unit_of_measure ?: 'pc';
              $cleanedUnit = strtolower(trim(preg_replace('/^(per\s*|\/\s*)/i', '', $rawUnitStr)));
              $unitDisplay = '/ per ' . ($cleanedUnit ?: 'pc');
            @endphp
            <div class="price-section py-4 border-t border-b border-dashed border-gray-200 mb-6">
              <div class="flex items-baseline gap-x-3">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider block">Price</span>
              </div>
              <div class="flex items-baseline gap-x-3 mt-1">
                <span class="current-price text-3xl leading-9 font-extrabold text-[#092040]">
                  ₹{{ number_format($price, 2) }} <span
                    class="text-base font-semibold text-slate-500 ms-1">{{ $unitDisplay }}</span>
                </span>
                @if($discount > 0)
                  <span class="old-price text-xl leading-9 font-normal text-gray-400 line-through">
                    ₹{{ number_format($mrp, 2) }}
                  </span>
                  <span class="text-sm font-bold text-red-500">
                    (Save {{ $discount }}%)
                  </span>
                @endif
              </div>
            </div>

            {{-- Business Metrics Pill Card --}}
            <div class="mb-6 p-4 bg-gray-50 rounded-2xl border border-gray-200">
              <div class="flex flex-col gap-y-1">
                <span class="text-xs font-bold text-gray-400 uppercase tracking-wider">MBP</span>
                <div class="flex items-center gap-x-2 mt-0.5">
                  <svg width="18" height="18" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <circle cx="12" cy="12" r="10" stroke="#ca8a04" stroke-width="2.5" fill="#facc15" />
                    <circle cx="12" cy="12" r="5" stroke="#ca8a04" stroke-width="1.5" stroke-dasharray="2 1.5"
                      fill="none" />
                  </svg>
                  <span class="text-lg font-extrabold text-gray-800">{{ number_format($mbpShare, 2) }}</span>
                </div>
              </div>
            </div>

            {{-- Add to Cart Section --}}
            <div class="product-add-to-cart-section py-4">
              <div class="product-add-to-cart-btn-section">
                @php
                  $cartProductIds = $cartProductIds ?? [];
                  $isInCart = in_array($product->id, $cartProductIds);
                @endphp

                @if(!$isDdcSourced && $moq > 1)
                  <p class="text-xs text-amber-600 font-normal mb-3">
                    * Minimum Order Quantity (Case Pack): {{ $moq }} {{ $product->unit_of_measure ?? 'Units' }}
                  </p>
                @endif

                <div class="flex items-center gap-x-4">
                  @if($isInCart)
                    <a href="{{ route('adc.cart.index') }}"
                      class="btn btn-primary btn-large rounded-[80px] w-full transition-all duration-300 cursor-pointer flex items-center justify-center gap-x-2"
                      style="background-color: #092040 !important; color: #fff !important; border: none !important;">
                      <i class="hgi hgi-stroke hgi-shopping-cart-02 leading-6 text-2xl text-white"></i>
                      Go to Cart
                    </a>
                  @else
                    <button type="button" id="add-to-cart-btn"
                      class="btn btn-primary btn-large rounded-[80px] w-full transition-all duration-300 cursor-pointer flex items-center justify-center gap-x-2"
                      style="background-color: #092040 !important; color: #fff !important; border: none !important;">
                      <i class="hgi hgi-stroke hgi-shopping-cart-add-02 leading-6 text-2xl text-white"></i>
                      Add to Cart
                    </button>
                  @endif
                </div>
              </div>
            </div>

            {{-- Specifications List --}}
            <div
              class="product-extra-info-section flex flex-col gap-y-3 mt-6 border-t border-gray-100 pt-4 text-sm text-gray-600">
              <div class="flex items-center justify-between">
                <span class="font-medium text-gray-400">Product Code:</span>
                <span class="font-semibold text-gray-800">{{ $product->product_code ?? 'N/A' }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="font-medium text-gray-400">SKU Code:</span>
                <span class="font-semibold text-gray-800">{{ $product->barcode ?? 'N/A' }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="font-medium text-gray-400">HSN Code:</span>
                <span class="font-semibold text-gray-800">{{ $product->hsn_code ?? 'N/A' }}</span>
              </div>
              <div class="flex items-center justify-between">
                <span class="font-medium text-gray-400">Country of Origin:</span>
                <span class="font-semibold text-gray-800">{{ $product->country_of_origin ?? 'India' }}</span>
              </div>
            </div>

          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- ========== Product Details Section End ========== -->

  <!-- ========== CTA Info Section Start ========== -->
  <section class="py-12">
    <div class="container">
      <div class="grid grid-cols-12 gap-6">
        <div class="md:col-span-6 col-span-12 xl:col-span-3">
          <div class="p-6 border-gray-300 border rounded-2xl text-center bg-white">
            <span class="inline-flex items-center justify-center size-14 bg-warning-lighter rounded-full">
              <i class="hgi hgi-stroke hgi-container-truck text-3xl text-light-primary-text"></i>
            </span>
            <h5 class="pt-3 pb-0.5 font-bold">Free Shipping</h5>
            <p class="text-sm text-gray-500">Enjoy the Convenience of Free Shipping on Every Order</p>
          </div>
        </div>
        <div class="md:col-span-6 col-span-12 xl:col-span-3">
          <div class="p-6 border-gray-300 border rounded-2xl text-center bg-white">
            <span class="inline-flex items-center justify-center size-14 bg-warning-lighter rounded-full">
              <i class="hgi hgi-stroke hgi-customer-support text-3xl text-light-primary-text"></i>
            </span>
            <h5 class="pt-3 pb-0.5 font-bold">24x7 Support</h5>
            <p class="text-sm text-gray-500">Round-the-Clock Assistance, Anytime You Need It</p>
          </div>
        </div>
        <div class="md:col-span-6 col-span-12 xl:col-span-3">
          <div class="p-6 border-gray-300 border rounded-2xl text-center bg-white">
            <span class="inline-flex items-center justify-center size-14 bg-warning-lighter rounded-full">
              <i class="hgi hgi-stroke hgi-delivery-return-02 text-3xl text-light-primary-text"></i>
            </span>
            <h5 class="pt-3 pb-0.5 font-bold">Easy Returns</h5>
            <p class="text-sm text-gray-500 font-normal">Hassle-free return policy for damaged or defective items.</p>
          </div>
        </div>
        <div class="md:col-span-6 col-span-12 xl:col-span-3">
          <div class="p-6 border-gray-300 border rounded-2xl text-center bg-white">
            <span class="inline-flex items-center justify-center size-14 bg-warning-lighter rounded-full">
              <i class="hgi hgi-stroke hgi-transaction text-3xl text-light-primary-text"></i>
            </span>
            <h5 class="pt-3 pb-0.5 font-bold">Secure Payment</h5>
            <p class="text-sm text-gray-500">Seamless Shopping Backed by Safe and Secure Payment Options</p>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- ========== CTA Info Section End ========== -->

  <!-- ========== Tabs Section Start ========== -->
  <section class="pb-12">
    <div class="container">
      <div id="product-details-tabs">
        <ul class="filter-list">
          <li>
            <button class="active" data-tab="description">Description</button>
          </li>
          <li><button data-tab="additional-info">Specifications</button></li>
        </ul>

        {{-- Description Tab --}}
        <div id="description" class="product-details-tab active fade">
          <h5 class="product-details-tab-title">Description</h5>
          <div class="product-details-tab-content bg-white p-6 rounded-3xl border border-gray-200">
            <div class="text-gray-700 leading-relaxed text-base">
              {!! nl2br(e($product->description)) !!}
            </div>
          </div>
        </div>

        {{-- Specifications Tab --}}
        <div id="additional-info" class="product-details-tab hidden">
          <h5 class="product-details-tab-title">Specifications</h5>
          <div class="product-details-tab-content bg-white p-6 rounded-3xl border border-gray-200">
            <table class="w-full border-collapse">
              <tbody class="divide-y divide-gray-200 text-sm">
                <tr>
                  <th class="font-semibold w-[220px] text-left py-3 text-gray-500">Product Name</th>
                  <td class="py-3 text-gray-800 font-medium">{{ $productName }}</td>
                </tr>
                <tr>
                  <th class="font-semibold w-[220px] text-left py-3 text-gray-500">Brand</th>
                  <td class="py-3 text-gray-800 font-medium">{{ $brandName }}</td>
                </tr>
                <tr>
                  <th class="font-semibold w-[220px] text-left py-3 text-gray-500">Category</th>
                  <td class="py-3 text-gray-800 font-medium">{{ $categoryName }}</td>
                </tr>
                <tr>
                  <th class="font-semibold w-[220px] text-left py-3 text-gray-500">Sub-Category</th>
                  <td class="py-3 text-gray-800 font-medium">{{ optional($product->subCategory)->name ?: 'N/A' }}</td>
                </tr>
                <tr>
                  <th class="font-semibold w-[220px] text-left py-3 text-gray-500">HSN Code</th>
                  <td class="py-3 text-gray-800 font-medium">{{ $product->hsn_code ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <th class="font-semibold w-[220px] text-left py-3 text-gray-500">GST Rate</th>
                  <td class="py-3 text-gray-800 font-medium">{{ $product->gst_rate ? $product->gst_rate . '%' : 'N/A' }}
                  </td>
                </tr>
                <tr>
                  <th class="font-semibold w-[220px] text-left py-3 text-gray-500">Unit of Measure</th>
                  <td class="py-3 text-gray-800 font-medium">{{ $unit }}</td>
                </tr>
                @if($product->length || $product->width || $product->height)
                  <tr>
                    <th class="font-semibold w-[220px] text-left py-3 text-gray-500">Dimensions (L x W x H)</th>
                    <td class="py-3 text-gray-800 font-medium">
                      {{ $product->length }} x {{ $product->width }} x {{ $product->height }}
                      {{ $product->dimension_unit ?? 'cm' }}
                    </td>
                  </tr>
                @endif
                @if($product->weight)
                  <tr>
                    <th class="font-semibold w-[220px] text-left py-3 text-gray-500">Weight</th>
                    <td class="py-3 text-gray-800 font-medium">{{ $product->weight }} {{ $product->weight_unit ?? 'gm' }}
                    </td>
                  </tr>
                @endif
                <tr>
                  <th class="font-semibold w-[220px] text-left py-3 text-gray-500">Shelf Life</th>
                  <td class="py-3 text-gray-800 font-medium">{{ $product->shelf_life ?? 'N/A' }}</td>
                </tr>
                <tr>
                  <th class="font-semibold w-[220px] text-left py-3 text-gray-500">Storage Instructions</th>
                  <td class="py-3 text-gray-800 font-medium">{{ $product->storage_instructions ?? 'N/A' }}</td>
                </tr>
                @if($product->product_specification)
                  <tr>
                    <th class="font-semibold w-[220px] text-left py-3 text-gray-500">Additional Specifications</th>
                    <td class="py-3 text-gray-800 font-medium">{{ $product->product_specification }}</td>
                  </tr>
                @endif
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </section>
  <!-- ========== Tabs Section End ========== -->

  <!-- ========== Related Products Section Start ========== -->
  <section class="pb-[70px]">
    <div class="container">
      <div class="flex items-center justify-between mb-12">
        <h3 class="wow animate__animated animate__fadeInUp" data-wow-delay="0.2s">
          Related Products
        </h3>
        <div data-wow-delay="0.2s"
          class="related-products-slider-nav flex items-center gap-x-6 wow animate__animated animate__fadeInUp"></div>
      </div>
      <div
        data-slick='{"slidesToShow": 4, "slidesToScroll": 1, "loop": true, "arrows": true, "infinite": true, "appendArrows": ".related-products-slider-nav", "autoplay": true, "autoplaySpeed": 7000, "responsive": [{"breakpoint": 1441, "settings": {"slidesToShow": 4}}, {"breakpoint": 1025, "settings": {"slidesToShow": 3}}, {"breakpoint": 769, "settings": {"slidesToShow": 2}}, {"breakpoint": 480, "settings": {"slidesToShow": 1}}]}'
        class="related-products-slider -mx-3 hitesh-slider">
        @forelse($relatedProducts as $relProduct)
          <div class="px-3">
            @include('web.partials.product-card', ['product' => $relProduct])
          </div>
        @empty
          <div class="text-center py-6 text-gray-500 w-full col-span-12">No related products found.</div>
        @endforelse
      </div>
    </div>
  </section>
  <!-- ========== Related Products Section End ========== -->




  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const addToCartBtn = document.getElementById('add-to-cart-btn');
      const defaultQty = {{ $isDdcSourced ? 1 : $moq }};

      if (addToCartBtn) {
        addToCartBtn.addEventListener('click', function (e) {
          e.preventDefault();

          // Show loading state
          const originalHtml = addToCartBtn.innerHTML;
          addToCartBtn.disabled = true;
          addToCartBtn.innerHTML = '<span class="inline-block animate-spin mr-2">⏳</span> Adding...';

          $.post("{{ route('adc.cart.add') }}", {
            _token: "{{ csrf_token() }}",
            product_id: "{{ $product->id }}",
            quantity: defaultQty
          }).done(function (res) {
            Toastify({
              text: res.success || "Added to cart successfully!",
              duration: 5000,
              close: true,
              gravity: "top",
              position: "right",
              style: { background: "#10b981" }
            }).showToast();
            if (res.cart_count !== undefined) {
              $('.js-cart-count').text(res.cart_count).removeClass('hidden');
              $('.js-cart-count-label').text(res.cart_count === 1 ? 'Item' : 'Items');
            }
            const $btn = $('#add-to-cart-btn');
            if ($btn.length) {
              const $btnContainer = $btn.parent();
              $btn.remove();
              $btnContainer.append(`
                          <a href="{{ route('adc.cart.index') }}" class="btn btn-primary btn-large rounded-[80px] w-full transition-all duration-300 cursor-pointer flex items-center justify-center gap-x-2" style="background-color: #092040 !important; color: #fff !important; border: none !important;">
                              <i class="hgi hgi-stroke hgi-shopping-cart-02 leading-6 text-2xl text-white"></i>
                              Go to Cart
                          </a>
                      `);
            }
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
            if (addToCartBtn && document.contains(addToCartBtn)) {
              addToCartBtn.disabled = false;
              addToCartBtn.innerHTML = originalHtml;
            }
          });
        });
      }
    });
  </script>

@endsection