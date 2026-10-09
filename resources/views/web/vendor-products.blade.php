@extends('web.layouts.app')

@section('title', ($seller->company_name ?? $seller->name) . ' — Products | IndianMSME')
@section('meta_description', 'Browse all verified products from ' . ($seller->company_name ?? $seller->name) . ' on IndianMSME.')

@push('styles')
  <style>
    .vendor-hero-banner {
      position: relative;
      height: 220px;
      overflow: hidden;
      background: #0f172a;
    }

    .vendor-hero-banner img.banner-bg {
      width: 100%;
      height: 100%;
      object-fit: cover;
      opacity: 0.45;
    }

    .vendor-hero-banner .hero-overlay {
      position: absolute;
      inset: 0;
      background: linear-gradient(to right, rgba(0, 0, 0, 0.75) 0%, rgba(0, 0, 0, 0.2) 100%);
      display: flex;
      align-items: flex-end;
      padding: 24px;
    }

    .vendor-profile-logo {
      width: 72px;
      height: 72px;
      border-radius: 50%;
      border: 3px solid #fff;
      background: #fff;
      object-fit: contain;
      flex-shrink: 0;
    }

    .vendor-hero-badge {
      display: inline-flex;
      align-items: center;
      gap: 5px;
      background: #16a34a;
      color: #fff;
      font-size: 12px;
      font-weight: 600;
      padding: 4px 10px;
      border-radius: 20px;
      margin-bottom: 6px;
    }

    .product-card-vendor {
      background: #fff;
      border: 1px solid #e5e7eb;
      border-radius: 14px;
      overflow: hidden;
      display: flex;
      flex-direction: column;
      height: 100%;
      transition: transform 0.2s, box-shadow 0.2s;
    }

    .product-card-vendor:hover {
      transform: translateY(-3px);
      box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
    }

    .product-card-vendor .product-img-wrap {
      position: relative;
      height: 180px;
      overflow: hidden;
      background: #f8fafc;
      display: flex;
      align-items: center;
      justify-content: center;
    }

    .product-card-vendor .product-img-wrap img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      transition: transform 0.3s;
    }

    .product-card-vendor:hover .product-img-wrap img {
      transform: scale(1.05);
    }

    .discount-badge-v {
      position: absolute;
      top: 10px;
      left: 10px;
      background: #ef4444;
      color: #fff;
      font-size: 10px;
      font-weight: 700;
      padding: 2px 7px;
      border-radius: 4px;
    }

    .product-card-vendor .card-body-v {
      padding: 12px 14px;
      flex: 1;
      display: flex;
      flex-direction: column;
    }

    .product-card-vendor .pname {
      font-size: 13px;
      font-weight: 600;
      color: #1e293b;
      line-height: 1.4;
      margin-bottom: 4px;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    .product-card-vendor .pcat {
      font-size: 11px;
      color: #94a3b8;
      margin-bottom: 8px;
    }

    .product-card-vendor .pprice-row {
      display: flex;
      align-items: center;
      gap: 6px;
      flex-wrap: wrap;
      margin-top: auto;
    }

    .product-card-vendor .pprice {
      font-size: 15px;
      font-weight: 700;
      color: #16a34a;
    }

    .product-card-vendor .pmrp {
      font-size: 12px;
      color: #9ca3af;
      text-decoration: line-through;
    }

    .product-card-vendor .padd-btn {
      display: block;
      width: 100%;
      margin-top: 10px;
      padding: 8px;
      text-align: center;
      background: linear-gradient(135deg, #16a34a, #15803d);
      color: #fff;
      border-radius: 8px;
      font-size: 12px;
      font-weight: 600;
      text-decoration: none;
      transition: opacity 0.2s;
    }

    .product-card-vendor .padd-btn:hover {
      opacity: 0.88;
    }
  </style>
@endpush

@section('content')
  @php
    $profile = $seller->sellerProfile;
    $imvCode = $profile->seller_id ?? 'N/A';
    $companyName = $profile->company_name ?? $seller->name;
    $category = $profile?->category?->name ?? 'General';
    $bannerRaw = $profile->company_banner ?? null;
    $logoRaw = $profile->company_logo ?? null;
    $bannerUrl = $bannerRaw
      ? (filter_var($bannerRaw, FILTER_VALIDATE_URL) ? $bannerRaw : asset('storage/' . $bannerRaw))
      : \App\Models\SystemSetting::getVal('website_header_logo', asset('admin-assets/images/IndianMSME.png'));
    $logoUrl = $logoRaw
      ? (filter_var($logoRaw, FILTER_VALIDATE_URL) ? $logoRaw : asset('storage/' . $logoRaw))
      : \App\Models\SystemSetting::getVal('website_header_logo', asset('admin-assets/images/IndianMSME.png'));
  @endphp

  <!-- ── Vendor Hero ──────────────────────────────────────────────── -->
  <div class="vendor-hero-banner">
    <img src="{{ $bannerUrl }}" alt="{{ $companyName }}" class="banner-bg">
    <div class="hero-overlay">
      <div class="flex items-end gap-5 w-full flex-wrap">
        <img src="{{ $logoUrl }}" alt="{{ $companyName }} logo" class="vendor-profile-logo">
        <div class="flex-1 min-w-0">
          <div class="vendor-hero-badge">
            <svg width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3">
              <polyline points="20 6 9 17 4 12" />
            </svg>
            Verified Seller
          </div>
          <h1 class="text-white text-xl font-bold leading-tight truncate">{{ $companyName }}</h1>
          <div class="flex items-center gap-4 flex-wrap mt-1">
            <span class="text-gray-300 text-sm font-mono">{{ $imvCode }}</span>
            <span class="text-gray-400 text-xs">{{ $category }}</span>
            <span class="text-gray-400 text-xs">{{ $seller->active_products_count }} Products</span>
            @if($seller->has_sub_sellers)
              <a href="{{ route('vendor.sub-sellers', $imvCode) }}" class="text-yellow-300 text-xs underline">View Sub
                Sellers →</a>
            @endif
          </div>
        </div>
        <a href="{{ route('vendors') }}"
          class="ml-auto text-white/70 text-sm hover:text-white transition-colors flex items-center gap-1 self-start">
          ← All Sellers
        </a>
      </div>
    </div>
  </div>

  <div class="container py-8">

    {{-- Pincode banner if not set --}}
    @if(!$pincode)
      <div class="bg-amber-50 border border-amber-200 rounded-xl px-5 py-4 mb-6 flex items-center gap-3">
        <svg class="flex-none text-amber-500" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor"
          stroke-width="2">
          <path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
          <path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
        </svg>
        <div>
          <p class="text-amber-800 font-semibold text-sm">Set your delivery location</p>
          <p class="text-amber-700 text-xs">Set a pincode to see products available in your area.</p>
        </div>
      </div>
    @endif

    {{-- Filters --}}
    <form method="GET" action="{{ route('vendor.products', $imvCode) }}" class="flex items-center gap-3 flex-wrap mb-6">
      <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-xl px-4 py-2 flex-1 min-w-[180px]">
        <i class="hgi hgi-stroke hgi-layers-01 text-gray-400 text-sm"></i>
        <select name="category" class="flex-1 border-0 outline-none text-sm text-gray-700 bg-transparent">
          <option value="">All Categories</option>
          @foreach($categories as $cat)
            <option value="{{ $cat->slug }}" @selected(request('category') === $cat->slug)>{{ $cat->name }}</option>
          @endforeach
        </select>
      </div>
      <div class="flex items-center gap-2 bg-white border border-gray-200 rounded-xl px-4 py-2 flex-1 min-w-[200px]">
        <i class="hgi hgi-stroke hgi-search-01 text-gray-400 text-sm"></i>
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Search products…"
          class="flex-1 border-0 outline-none text-sm bg-transparent">
      </div>
      <button type="submit"
        class="px-5 py-2.5 bg-primary text-white rounded-xl text-sm font-semibold hover:opacity-90 transition-opacity">
        Filter
      </button>
      @if(request('search') || request('category'))
        <a href="{{ route('vendor.products', $imvCode) }}"
          class="px-4 py-2.5 border border-gray-300 rounded-xl text-sm text-gray-600 hover:bg-gray-50">
          Clear
        </a>
      @endif
    </form>

    {{-- Product Count --}}
    <p class="text-sm text-gray-500 mb-5">
      Showing <strong>{{ $products->firstItem() ?? 0 }}–{{ $products->lastItem() ?? 0 }}</strong>
      of <strong>{{ $products->total() }}</strong> products
    </p>

    {{-- Products Grid --}}
    @if($products->isEmpty())
      <div class="text-center py-20">
        <div class="text-5xl mb-4">📦</div>
        <h3 class="text-lg font-semibold text-gray-700 mb-2">No products found</h3>
        <p class="text-gray-500 text-sm">This seller has no active products matching your filters.</p>
      </div>
    @else
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
        @foreach($products as $product)
          @php
            $imgRaw = $product->featured_image;
            $imgUrl = $imgRaw
              ? (filter_var($imgRaw, FILTER_VALIDATE_URL) ? $imgRaw : (file_exists(public_path('storage/' . $imgRaw)) ? asset('storage/' . $imgRaw) : \App\Models\SystemSetting::getVal('website_header_logo', asset('admin-assets/images/IndianMSME.png'))))
              : \App\Models\SystemSetting::getVal('website_header_logo', asset('admin-assets/images/IndianMSME.png'));
            $price = (float) ($product->adc_purchase_price ?: $product->mrp ?: 0);
            $mrp = (float) ($product->mrp ?: 0);
            $discount = ($mrp > 0 && $price < $mrp) ? round((($mrp - $price) / $mrp) * 100) : 0;
          @endphp
          <div class="product-card-vendor">
            <div class="product-img-wrap">
              <a href="{{ route('product.show', ['id' => $product->id, 'slug' => \Str::slug($product->product_name)]) }}">
                <img src="{{ $imgUrl }}" alt="{{ $product->product_name }}" loading="lazy">
              </a>
              @if($discount > 0)
                <span class="discount-badge-v">{{ $discount }}% OFF</span>
              @endif
            </div>
            <div class="card-body-v">
              <div class="pcat">{{ $product->category?->name ?? $category }}</div>
              <a href="{{ route('product.show', ['id' => $product->id, 'slug' => \Str::slug($product->product_name)]) }}"
                class="pname hover:text-primary transition-colors">
                {{ $product->product_name }}
              </a>
              @php
                $rawUnitStr = $product->unit_of_measure ?: 'pc';
                $cleanedUnit = strtolower(trim(preg_replace('/^(per\s*|\/\s*)/i', '', $rawUnitStr)));
                $unitDisplay = '/ per ' . ($cleanedUnit ?: 'pc');
               @endphp
              <div class="pprice-row">
                <span class="pprice">₹{{ number_format($price, 2) }} <span
                    style="font-size: 11px; font-weight: 600; color: #64748b;">{{ $unitDisplay }}</span></span>
                @if($discount > 0)
                  <span class="pmrp">₹{{ number_format($mrp, 2) }}</span>
                @endif
              </div>
              <a href="{{ route('product.show', ['id' => $product->id, 'slug' => \Str::slug($product->product_name)]) }}"
                class="padd-btn mt-2">
                View Product
              </a>
            </div>
          </div>
        @endforeach
      </div>

      <!-- Smart Controlled On-Scroll Progress & Loading Container -->
      @if($products->count() > 0)
        <div id="vendor-prod-scroll-container" style="margin-top: 32px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding-bottom: 32px;">
          @php
            $visibleCount = min($products->count(), $products->total());
            $progressPercent = $products->total() > 0 ? min(100, round(($visibleCount / $products->total()) * 100)) : 100;
          @endphp
          <div style="font-size: 12px; color: #64748b; font-weight: 600; margin-bottom: 6px;" id="vendor-prod-scroll-count-text">
            Showing <span id="vendor-prod-visible-count">{{ $visibleCount }}</span> of <span id="vendor-prod-total-count">{{ $products->total() }}</span> products
          </div>
          <div style="width: 200px; height: 6px; background-color: #e2e8f0; border-radius: 9999px; overflow: hidden; margin-bottom: 12px;">
            <div id="vendor-prod-progress-bar-inner" style="background-color: #0284c7; height: 100%; transition: width 0.3s ease; width: {{ $progressPercent }}%;"></div>
          </div>

          <div id="vendor-prod-scroll-loader" style="display: none !important; padding: 8px 0; align-items: center; justify-content: center; gap: 8px; color: #475569; font-size: 12px; font-weight: 600;">
            <span>Loading more products...</span>
          </div>

          <div id="vendor-prod-end-text" style="font-size: 12px; color: #94a3b8; font-weight: 500; margin: 8px 0; display: {{ $products->hasMorePages() ? 'none' : 'block' }};">
            You've viewed all {{ $products->total() }} products
          </div>

          <div id="vendor-prod-scroll-sentinel" style="width: 100%; height: 4px;"></div>
        </div>
      @endif
    @endif

  </div>
@endsection