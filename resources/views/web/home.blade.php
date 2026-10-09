@extends('web.layouts.app')

@section('title', 'Indian MSME Store — Home')

@section('content')

  @php
    $showLeft = !empty($leftBanners) && $leftBanners->count() > 0;
    $showRight = !empty($rightBanners) && $rightBanners->count() > 0;
  @endphp

  @if($showLeft || $showRight)
    <!-- ========== Hero Section Start ========== -->
    <section class="pt-6">
      <div class="container">
        <div class="hero-container">

          @if($showLeft)
            <!-- Left Slider wrapper -->
            <div class="hero-left-wrapper" style="{{ !$showRight ? 'width: 100% !important;' : '' }}">
              <div class="hero-left-slider overflow-hidden rounded-[14px]">
                @foreach($leftBanners as $banner)
                  <div class="hero-slide-item rounded-[14px] overflow-hidden">
                    @if($banner->link_url)
                      <a href="{{ $banner->link_url }}" target="_blank" class="block w-full h-full">
                        <img src="{{ $banner->image_url }}" alt="Banner" class="hero-image" loading="eager" />
                      </a>
                    @else
                      <img src="{{ $banner->image_url }}" alt="Banner" class="hero-image" loading="eager" />
                    @endif
                  </div>
                @endforeach
              </div>
              <!-- Left Slider Dots Container -->
              <div class="hero-left-dots"></div>
            </div>
          @endif

          @if($showRight)
            <!-- Right Slider wrapper -->
            <div class="hero-right-wrapper"
              style="{{ !$showLeft ? 'display: block !important; width: 100% !important;' : '' }}">
              <div class="hero-right-slider overflow-hidden rounded-[14px]">
                @foreach($rightBanners as $banner)
                  <div class="hero-slide-item rounded-[14px] overflow-hidden">
                    @if($banner->link_url)
                      <a href="{{ $banner->link_url }}" target="_blank" class="block w-full h-full">
                        <img src="{{ $banner->image_url }}" alt="Banner" class="hero-image" loading="eager" />
                      </a>
                    @else
                      <img src="{{ $banner->image_url }}" alt="Banner" class="hero-image" loading="eager" />
                    @endif
                  </div>
                @endforeach
              </div>
              <!-- Right Slider Dots Container -->
              <div class="hero-right-dots"></div>
            </div>
          @endif

        </div>
      </div>
    </section>
    <!-- ========== Hero Section End ========== -->
  @endif

  <style>
    .hero-container {
      display: flex;
      flex-direction: column;
      gap: 16px;
      width: 100%;
    }

    .hero-left-wrapper,
    .hero-right-wrapper {
      width: 100%;
      position: relative;
      flex: none;
    }

    @media (min-width: 768px) {
      .hero-container {
        flex-direction: row;
      }

      .hero-left-wrapper {
        width: calc(50% - 8px) !important;
      }

      .hero-right-wrapper {
        display: block !important;
        width: calc(50% - 8px) !important;
      }
    }

    /* Fixed uniform height for both sliders and slides */
    .hero-slide-item {
      width: 100%;
      height: 180px;
      position: relative;
      overflow: hidden;
      border-radius: 14px;
    }

    @media (min-width: 480px) {
      .hero-slide-item {
        height: 220px;
      }
    }

    @media (min-width: 768px) {
      .hero-slide-item {
        height: 310px;
      }
    }

    @media (min-width: 1024px) {
      .hero-slide-item {
        height: 310px;
      }
    }

    /* Target image cover */
    .hero-image {
      width: 100% !important;
      height: 100% !important;
      object-fit: cover !important;
      object-position: center !important;
      border-radius: 14px !important;
      display: block !important;
    }

    .hero-left-slider,
    .hero-right-slider {
      position: relative;
      border-radius: 14px !important;
    }

    /* Navigation Arrows */
    .hero-arrow {
      position: absolute !important;
      top: 50% !important;
      transform: translateY(-50%) !important;
      z-index: 20 !important;
      width: 38px !important;
      height: 38px !important;
      border-radius: 50% !important;
      background: rgba(255, 255, 255, 0.92) !important;
      box-shadow: 0 4px 14px rgba(0, 0, 0, 0.18) !important;
      border: 1px solid rgba(0, 0, 0, 0.08) !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      color: #0f172a !important;
      cursor: pointer !important;
      transition: all 0.25s ease !important;
      opacity: 0.85;
    }

    .hero-left-wrapper:hover .hero-arrow,
    .hero-right-wrapper:hover .hero-arrow {
      opacity: 1;
    }

    .hero-arrow:hover {
      background: #ffffff !important;
      color: #2563eb !important;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.25) !important;
      transform: translateY(-50%) scale(1.08) !important;
    }

    .hero-arrow-left {
      left: 14px !important;
    }

    .hero-arrow-right {
      right: 14px !important;
    }

    .hero-left-dots,
    .hero-right-dots {
      margin-top: 10px !important;
      margin-bottom: 14px !important;
      display: flex !important;
      justify-content: center !important;
      align-items: center !important;
    }

    .hero-left-dots .slick-dots,
    .hero-right-dots .slick-dots {
      display: flex !important;
      justify-content: center !important;
      align-items: center !important;
      gap: 8px !important;
      list-style: none !important;
      padding: 0 !important;
      margin: 0 !important;
    }

    .hero-left-dots .slick-dots li,
    .hero-right-dots .slick-dots li {
      margin: 0 !important;
      padding: 0 !important;
      display: inline-block !important;
    }

    .hero-left-dots .slick-dots li button,
    .hero-right-dots .slick-dots li button {
      width: 8px !important;
      height: 8px !important;
      border-radius: 50% !important;
      background-color: #cbd5e1 !important;
      border: none !important;
      text-indent: -9999px !important;
      overflow: hidden !important;
      padding: 0 !important;
      cursor: pointer !important;
      transition: all 0.3s ease !important;
    }

    .hero-left-dots .slick-dots li.slick-active button,
    .hero-right-dots .slick-dots li.slick-active button {
      background-color: #0f172a !important;
      width: 24px !important;
      border-radius: 4px !important;
    }

    /* Feature Highlights Bar */
    .msme-features-bar {
      background: #ffffff;
      border-top: 1px solid #f1f5f9;
      border-bottom: 1px solid #f1f5f9;
      padding: 14px 0;
      margin: 10px 0 10px 0;
    }

    .msme-features-grid {
      display: flex;
      flex-wrap: wrap;
      align-items: center;
      justify-content: space-between;
      gap: 16px;
    }

    .msme-feature-item {
      display: flex;
      align-items: center;
      gap: 12px;
      flex: 1 1 180px;
      min-width: 170px;
      padding: 4px 6px;
    }

    .msme-feature-icon {
      width: 44px;
      height: 44px;
      border-radius: 12px;
      background: #f8fafc;
      border: 1px solid #e2e8f0;
      display: flex;
      align-items: center;
      justify-content: center;
      color: #0f172a;
      flex-shrink: 0;
    }

    .msme-feature-title {
      font-size: 14px;
      font-weight: 700;
      color: #0f172a;
      line-height: 1.2;
      margin: 0;
    }

    .msme-feature-desc {
      font-size: 12px;
      font-weight: 500;
      color: #64748b;
      margin: 2px 0 0 0;
      line-height: 1.2;
    }

    /* Circle Category Section */
    .msme-cat-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 12px;
    }

    .msme-cat-heading {
      font-size: 24px;
      font-weight: 800;
      color: #0f172a;
      margin: 0;
    }

    .msme-cat-link {
      font-size: 14px;
      font-weight: 700;
      color: #0f172a;
      text-decoration: none;
      display: inline-flex;
      align-items: center;
      gap: 6px;
      transition: color 0.2s ease;
    }

    .msme-cat-link:hover {
      color: #2563eb;
    }

    .msme-cat-scroll-track {
      display: flex;
      align-items: flex-start;
      gap: 16px;
      overflow-x: auto;
      scroll-behavior: smooth;
      padding: 6px 4px 16px 4px;
      -webkit-overflow-scrolling: touch;
      scrollbar-width: none;
    }

    .msme-cat-scroll-track::-webkit-scrollbar {
      display: none;
    }

    .msme-cat-card {
      flex: 0 0 auto;
      width: 102px;
      display: flex;
      flex-direction: column;
      align-items: center;
      text-align: center;
      text-decoration: none !important;
    }

    .msme-cat-circle-ring {
      width: 92px !important;
      height: 92px !important;
      min-width: 92px !important;
      min-height: 92px !important;
      max-width: 92px !important;
      max-height: 92px !important;
      aspect-ratio: 1 / 1 !important;
      border-radius: 50% !important;
      padding: 4px !important;
      background: radial-gradient(circle, #fff7ed 0%, #ffffff 100%);
      border: 1.5px solid #fed7aa;
      box-shadow: 0 3px 10px rgba(0, 0, 0, 0.05);
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      overflow: hidden !important;
      margin: 0 auto !important;
      transition: all 0.25s ease !important;
    }

    .msme-cat-card:hover .msme-cat-circle-ring {
      transform: translateY(-3px) scale(1.05);
      border-color: #3b82f6 !important;
      box-shadow: 0 6px 16px rgba(37, 99, 235, 0.18) !important;
    }

    .msme-cat-img-inner {
      width: 100% !important;
      height: 100% !important;
      object-fit: cover !important;
      border-radius: 50% !important;
      display: block !important;
    }

    .msme-cat-name-text {
      margin-top: 8px;
      font-size: 12px;
      font-weight: 700;
      color: #0f172a;
      line-height: 1.25;
      text-align: center;
      width: 100%;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      transition: color 0.2s ease;
    }

    .msme-cat-card:hover .msme-cat-name-text {
      color: #2563eb;
    }

    /* Strict Left Alignment for All Sliders and Product Cards */
    .best-selling-products-wrapper,
    .home-one-newly-launched-products-slider,
    .hitesh-slider,
    .slick-list,
    .slick-track,
    .slick-slide {
      text-align: left !important;
    }

    .slick-track {
      margin-left: 0 !important;
      margin-right: auto !important;
      left: 0 !important;
    }

    .hitesh-slider .slick-slide {
      float: left !important;
    }

    .hitesh-slider .slick-slide>div {
      display: flex !important;
      justify-content: flex-start !important;
    }

    .universal-product-card {
      margin-left: auto !important;
      margin-right: auto !important;
    }

    /* Brand Card Styling */
    .msme-brands-row {
      display: grid;
      grid-template-columns: repeat(6, minmax(0, 1fr));
      gap: 14px;
      width: 100%;
    }

    @media (max-width: 991px) {
      .msme-brands-row {
        display: flex;
        flex-wrap: nowrap;
        overflow-x: auto;
        scroll-behavior: smooth;
        -webkit-overflow-scrolling: touch;
        scrollbar-width: none;
        padding-bottom: 6px;
      }

      .msme-brands-row::-webkit-scrollbar {
        display: none;
      }

      .msme-brand-card {
        flex: 0 0 150px;
      }
    }

    .msme-brand-card {
      height: 76px;
      background: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 12px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 10px 14px;
      transition: all 0.25s ease;
      text-decoration: none !important;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.03);
    }

    .msme-brand-card:hover {
      border-color: #cbd5e1;
      box-shadow: 0 6px 16px rgba(0, 0, 0, 0.07);
      transform: translateY(-2px);
    }
  </style>

  <!-- ========== Features Highlight Bar Start ========== -->
  <section class="msme-features-bar">
    <div class="container">
      <div class="msme-features-grid">

        <!-- Feature 1: Direct Factory Prices / Wholesale Price -->
        <div class="msme-feature-item">
          <div class="msme-feature-icon" style="font-weight: 800; font-size: 20px; font-family: 'Outfit', sans-serif;">
            ₹
          </div>
          <div>
            <h5 class="msme-feature-title">Direct Factory Prices</h5>
            <p class="msme-feature-desc">Wholesale Price</p>
          </div>
        </div>

        <!-- Feature 2: Verified Sellers -->
        <div class="msme-feature-item">
          <div class="msme-feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z" />
              <path d="m9 12 2 2 4-4" />
            </svg>
          </div>
          <div>
            <h5 class="msme-feature-title">Verified Sellers</h5>
            <p class="msme-feature-desc">Trusted & Quality Checked</p>
          </div>
        </div>

        <!-- Feature 3: Pan India Delivery -->
        <div class="msme-feature-item">
          <div class="msme-feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect width="16" height="13" x="1" y="3" rx="2" />
              <path d="M16 8h4l3 3v5h-7V8z" />
              <circle cx="5.5" cy="18.5" r="2.5" />
              <circle cx="18.5" cy="18.5" r="2.5" />
            </svg>
          </div>
          <div>
            <h5 class="msme-feature-title">Pan India Delivery</h5>
            <p class="msme-feature-desc">Reliable Delivery Network</p>
          </div>
        </div>

        <!-- Feature 4: Secure Payments -->
        <div class="msme-feature-item">
          <div class="msme-feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <rect width="20" height="14" x="2" y="5" rx="2" />
              <line x1="2" x2="22" y1="10" y2="10" />
            </svg>
          </div>
          <div>
            <h5 class="msme-feature-title">Secure Payments</h5>
            <p class="msme-feature-desc">Safe & Secure Checkout</p>
          </div>
        </div>

        <!-- Feature 5: Support MSMEs -->
        <div class="msme-feature-item">
          <div class="msme-feature-icon">
            <svg xmlns="http://www.w3.org/2000/svg" width="22" height="22" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
              <path
                d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z" />
            </svg>
          </div>
          <div>
            <h5 class="msme-feature-title">Support MSMEs</h5>
            <p class="msme-feature-desc">Buy Local, Grow Local</p>
          </div>
        </div>

      </div>
    </div>
  </section>
  <!-- ========== Features Highlight Bar End ========== -->

  <!-- ========== Category Section Start ========== -->
  <section class="pt-0 pb-6 text-left">
    <div class="container">
      <div class="msme-cat-header text-left">
        <div class="text-left">
          <h3 class="msme-cat-heading text-left">Shop by Category</h3>
          <p class="text-xs text-slate-500 font-medium mt-1 mb-0 text-left" style="color: #64748b !important;">Explore a
            wide variety of authentic MSME products 🔥</p>
        </div>
        <div class="flex items-center gap-3">
          <a href="{{ route('shop') }}" class="msme-cat-link">
            View All Categories
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="m9 18 6-6-6-6" />
            </svg>
          </a>
          <!-- Scroll Arrow Controls -->
          <div class="hidden sm:flex items-center gap-1.5 ms-2">
            <button onclick="scrollCatTrack('left')" type="button"
              class="w-8 h-8 rounded-full border border-slate-200 bg-white hover:bg-slate-900 hover:text-white hover:border-slate-900 flex items-center justify-center text-slate-700 shadow-2xs transition-all duration-200"
              aria-label="Scroll Left">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m15 18-6-6 6-6" />
              </svg>
            </button>
            <button onclick="scrollCatTrack('right')" type="button"
              class="w-8 h-8 rounded-full border border-slate-200 bg-white hover:bg-slate-900 hover:text-white hover:border-slate-900 flex items-center justify-center text-slate-700 shadow-2xs transition-all duration-200"
              aria-label="Scroll Right">
              <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="m9 18 6-6-6-6" />
              </svg>
            </button>
          </div>
        </div>
      </div>

      <div class="position-relative">
        <div id="categoryScrollTrack" class="msme-cat-scroll-track">
          @foreach(\App\Models\Category::active()->get() as $cat)
            <a href="{{ route('category.show', $cat->slug) }}" class="msme-cat-card group">
              <div class="msme-cat-circle-ring">
                <img src="{{ $cat->image_url }}" alt="{{ $cat->name }}" class="msme-cat-img-inner" loading="eager"
                  onerror="this.onerror=null; this.src='{{ asset('admin-assets/images/IndianMSME.png') }}';" />
              </div>
              <p class="msme-cat-name-text">
                {{ $cat->name }}
              </p>
            </a>
          @endforeach
        </div>
      </div>
    </div>
  </section>
  <!-- ========== Category Section End ========== -->

  <!-- ========== Middle Section Banners Start ========== -->
  @if(isset($middle3ColBanners) && $middle3ColBanners->count() > 0)
    <section>
      <div class="container">
        <div class="grid grid-cols-12 gap-4 lg:gap-6">
          @foreach($middle3ColBanners as $banner)
              <div class="md:col-span-6 col-span-12 xl:col-span-4 wow animate__animated animate__fadeInUp" data-wow-delay=".2s">
                @if($banner->link_url)
                  <a href="{{ $banner->link_url }}"
                    class="block overflow-hidden rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">
                @else
                    <div class="overflow-hidden rounded-2xl shadow-sm">
                  @endif
                    <img src="{{ $banner->image_url }}" alt="Banner Image" class="w-full h-auto object-cover rounded-2xl" />
                    @if($banner->link_url)
                      </a>
                    @else
                  </div>
                @endif
            </div>
          @endforeach
      </div>
      </div>
    </section>
  @endif
  <!-- ========== Middle Section Banners End ========== -->



  <!-- ========== Discount You Love Section Start ========== -->
  <section class="py-6 pb-10 text-left">
    <div class="container">
      <div class="flex items-center justify-between mb-3 text-left">
        <div class="flex items-center gap-3.5 flex-wrap text-left">
          <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight text-left"
            style="color: #0f172a !important;">
            Daily Discount You'll Love 🔥
          </h3>
          <!-- Countdown Timer Badges -->
          <div class="flex items-center gap-1.5 ms-1 sm:ms-3">
            <div class="bg-slate-900 text-white rounded-md px-2 py-1 text-center min-w-[34px] shadow-2xs">
              <span class="block text-xs font-extrabold leading-none">08</span>
              <span class="block text-[8px] text-slate-300 font-medium leading-none mt-0.5 uppercase">Hrs</span>
            </div>
            <div class="bg-slate-900 text-white rounded-md px-2 py-1 text-center min-w-[34px] shadow-2xs">
              <span class="block text-xs font-extrabold leading-none">24</span>
              <span class="block text-[8px] text-slate-300 font-medium leading-none mt-0.5 uppercase">Mins</span>
            </div>
            <div class="bg-slate-900 text-white rounded-md px-2 py-1 text-center min-w-[34px] shadow-2xs">
              <span class="block text-xs font-extrabold leading-none">36</span>
              <span class="block text-[8px] text-slate-300 font-medium leading-none mt-0.5 uppercase">Secs</span>
            </div>
          </div>
        </div>

        <a href="{{ route('shop') }}"
          class="text-xs sm:text-sm font-bold text-slate-900 hover:text-blue-600 inline-flex items-center gap-1.5 transition-colors"
          style="color: #0f172a !important;">
          View All
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="m9 18 6-6-6-6" />
          </svg>
        </a>
      </div>

      <div class="grid grid-cols-12 gap-3 sm:gap-4">
        @forelse($featuredProducts->take(6) as $product)
          <div class="2xl:col-span-2 xl:col-span-2 lg:col-span-3 md:col-span-4 sm:col-span-6 col-span-6">
            @include('web.partials.product-card', ['product' => $product])
          </div>
        @empty
          <div class="col-span-12 text-center py-12">
            <p class="text-gray-500">No products found.</p>
          </div>
        @endforelse
      </div>
    </div>
  </section>
  <!-- ========== Discount You Love Section End ========== -->

  <!-- ========== Best Selling Products Section Start ========== -->
  <section class="py-6 pb-10 text-left">
    <div class="container">
      <div class="flex items-center justify-between mb-3 text-left">
        <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight text-left"
          style="color: #0f172a !important;">
          Best Selling Products
        </h3>
        <a href="{{ route('shop') }}"
          class="text-xs sm:text-sm font-bold text-slate-900 hover:text-blue-600 inline-flex items-center gap-1.5 transition-colors"
          style="color: #0f172a !important;">
          View All
          <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
            stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
            <path d="m9 18 6-6-6-6" />
          </svg>
        </a>
      </div>

      <div class="best-selling-products-wrapper">
        <div
          data-slick='{ "slidesToShow": 6, "slidesToScroll": 1, "loop": true, "autoplay": true, "autoplaySpeed": 7000, "arrows": true, "infinite": true, "responsive": [{"breakpoint": 1441, "settings": {"slidesToShow": 4}}, {"breakpoint": 1025, "settings": {"slidesToShow": 3}}, {"breakpoint": 769, "settings": {"slidesToShow": 2}}, {"breakpoint": 480, "settings": {"slidesToShow": 1}}]}'
          class="-mx-3 hitesh-slider">
          @foreach($featuredProducts as $product)
            <div class="px-3">
              @include('web.partials.product-card', ['product' => $product])
            </div>
          @endforeach
        </div>
      </div>
    </div>
  </section>
  <!-- ========== Best Selling Products Section End ========== -->

  <!-- ========== Explore Indian Brands Section Start ========== -->
  @if(isset($brands) && $brands->count() > 0)
    <section class="text-left">
      <div class="container">
        <div class="flex items-center justify-between mb-2 text-left">
          <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight text-left"
            style="color: #0f172a !important;">
            Explore Brands
          </h3>
          <a href="{{ route('brands') }}"
            class="text-xs sm:text-sm font-bold text-slate-900 hover:text-blue-600 inline-flex items-center gap-1.5 transition-colors"
            style="color: #0f172a !important;">
            View All
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="m9 18 6-6-6-6" />
            </svg>
          </a>
        </div>

        <div class="msme-brands-row">
          @foreach($brands->take(12) as $b)
            @php
              $defaultLogo = \App\Models\SystemSetting::getVal('website_header_logo', \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png')));
              $bLogo = !empty($b->logo)
                ? (filter_var($b->logo, FILTER_VALIDATE_URL) ? $b->logo : asset('storage/' . $b->logo))
                : $defaultLogo;
            @endphp
            <a href="{{ route('shop', ['brand' => $b->id]) }}"
              class="block bg-white border border-gray-100 rounded-xl p-3 text-center hover:shadow-md transition">
              <img src="{{ $bLogo }}" alt="{{ $b->name }}" class="h-10 mx-auto object-contain mb-1"
                onerror="this.onerror=null; this.src='{{ $defaultLogo }}';" />
              <span class="text-xs font-semibold text-gray-700 block truncate">{{ $b->name }}</span>
            </a>
          @endforeach
        </div>
      </div>
    </section>
  @endif
  <!-- ========== Explore Indian Brands Section End ========== -->

  <!-- ========== Our Products Section Start ========== -->
  <section class="py-6 pb-10 text-left">
    <div class="container">
      @php
        $displayCategories = $categories->filter(function($cat) {
            return $cat->products()->where('status', 'Approved')->exists();
        })->take(4);
      @endphp
      <div class="mb-3 flex xl:flex-row flex-col gap-y-4 items-start xl:items-center justify-between text-left">
        <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight text-left"
          style="color: #0f172a !important;">
          Our Products
        </h3>
        <div class="flex gap-2 overflow-x-auto max-w-full pb-1 scrollbar-none home-one-product-filter">
          <button data-tab="all-products" class="btn btn-default outline py-2 px-4 rounded-full text-xs font-bold">
            All Products
          </button>
          @foreach($displayCategories as $cat)
            <button data-tab="category-{{ $cat->id }}"
              class="btn btn-default outline py-2 px-4 rounded-full text-xs font-bold shadow-none">
              {{ $cat->name }}
            </button>
          @endforeach
        </div>
      </div>

      <div class="tab-content">
        <!-- All Products Tab Start -->
        <div class="tab-pane" id="all-products">
          <div class="grid grid-cols-12 gap-3 sm:gap-4">
            @forelse($featuredProducts as $product)
              <div class="2xl:col-span-2 xl:col-span-2 lg:col-span-3 md:col-span-4 sm:col-span-6 col-span-6">
                @include('web.partials.product-card', ['product' => $product])
              </div>
            @empty
              <div class="col-span-12 text-center py-12">
                <p class="text-gray-500">No products found.</p>
              </div>
            @endforelse
          </div>
        </div>

        <!-- Category Tabs -->
        @foreach($displayCategories as $cat)
          <div class="tab-pane hidden" id="category-{{ $cat->id }}">
            <div class="grid grid-cols-12 gap-3 sm:gap-4">
              @foreach($cat->products()->where('status', 'Approved')->with(['category', 'brand', 'seller'])->latest()->take(8)->get() as $product)
                <div class="2xl:col-span-2 xl:col-span-2 lg:col-span-3 md:col-span-4 sm:col-span-6 col-span-6">
                  @include('web.partials.product-card', ['product' => $product])
                </div>
              @endforeach
            </div>
          </div>
        @endforeach
      </div>
    </div>
  </section>
  <!-- ========== Our Products Section End ========== -->

  <!-- ========== Why Shop With Indian MSME Section Start ========== -->
  <section class="text-left">
    <div class="container">
      <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight mb-4 text-left"
        style="color: #0b192c !important; font-weight: 800;">
        Why Shop With Indian MSME?
      </h3>

      <div class="why-shop-msme-wrapper">
        <div class="why-shop-msme-grid">

          {{-- 1. Support Indian Businesses --}}
          <div class="why-shop-item">
            <div class="why-shop-icon-box orange">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M16 11V7A4 4 0 008 7v4M5 9h14l1 12H4L5 9z" stroke="#f97316" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </div>
            <div class="why-shop-text">
              <h4 class="why-shop-title">Support Indian Businesses</h4>
              <p class="why-shop-desc">Every purchase helps India's MSME ecosystem grow.</p>
            </div>
          </div>

          {{-- 2. Verified Quality --}}
          <div class="why-shop-item">
            <div class="why-shop-icon-box green">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                  stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </div>
            <div class="why-shop-text">
              <h4 class="why-shop-title">Verified Quality</h4>
              <p class="why-shop-desc">Products from trusted sellers.</p>
            </div>
          </div>

          {{-- 3. Great Value --}}
          <div class="why-shop-item">
            <div class="why-shop-icon-box yellow">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M7 7h10M7 12h10M7 17h6M12 2a10 10 0 100 20 10 10 0 000-20z" stroke="#d97706" stroke-width="2"
                  stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </div>
            <div class="why-shop-text">
              <h4 class="why-shop-title">Great Value</h4>
              <p class="why-shop-desc">Competitive prices directly from businesses.</p>
            </div>
          </div>

          {{-- 4. Reliable Delivery --}}
          <div class="why-shop-item">
            <div class="why-shop-icon-box green">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M5 8h11a1 1 0 011 1v7a1 1 0 01-1 1H5a1 1 0 01-1-1V9a1 1 0 011-1zM17 11h4.5a1 1 0 01.8.4l1.6 2.13a1 1 0 01.1.47V16a1 1 0 01-1 1H21M6.5 17a1.5 1.5 0 100 3 1.5 1.5 0 000-3zm12 0a1.5 1.5 0 100 3 1.5 1.5 0 000-3z"
                  stroke="#16a34a" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </div>
            <div class="why-shop-text">
              <h4 class="why-shop-title">Reliable Delivery</h4>
              <p class="why-shop-desc">Convenient delivery across India.</p>
            </div>
          </div>

          {{-- 5. Real Businesses --}}
          <div class="why-shop-item">
            <div class="why-shop-icon-box orange">
              <svg width="22" height="22" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path
                  d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"
                  stroke="#f97316" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
              </svg>
            </div>
            <div class="why-shop-text">
              <h4 class="why-shop-title">Real Businesses</h4>
              <p class="why-shop-desc">Discover the people behind the products.</p>
            </div>
          </div>

        </div>
      </div>
    </div>
  </section>

  <style>
    .why-shop-msme-wrapper {
      background-color: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 14px;
      padding: 16px 20px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.02);
    }

    .why-shop-msme-grid {
      display: grid;
      grid-template-columns: repeat(5, minmax(0, 1fr));
      width: 100%;
    }

    .why-shop-item {
      display: flex;
      align-items: center;
      gap: 12px;
      padding: 4px 16px;
      border-right: 1px solid #f1f5f9;
    }

    .why-shop-item:first-child {
      padding-left: 4px;
    }

    .why-shop-item:last-child {
      border-right: none;
      padding-right: 4px;
    }

    .why-shop-icon-box {
      width: 44px;
      height: 44px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
    }

    .why-shop-icon-box.orange {
      background-color: #fff4ed;
      border: 1px solid #ffe4d6;
    }

    .why-shop-icon-box.green {
      background-color: #f0fdf4;
      border: 1px solid #dcfce7;
    }

    .why-shop-icon-box.yellow {
      background-color: #fffbeb;
      border: 1px solid #fef3c7;
    }

    .why-shop-text {
      display: flex;
      flex-direction: column;
    }

    .why-shop-title {
      font-size: 13px !important;
      font-weight: 700 !important;
      color: #0b192c !important;
      line-height: 1.25 !important;
      margin: 0 0 2px 0 !important;
    }

    .why-shop-desc {
      font-size: 11px !important;
      font-weight: 400 !important;
      color: #475569 !important;
      line-height: 1.35 !important;
      margin: 0 !important;
    }

    @media (max-width: 1024px) {
      .why-shop-msme-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 16px;
      }

      .why-shop-item {
        border-right: none;
        padding: 4px 8px;
      }
    }

    @media (max-width: 640px) {
      .why-shop-msme-grid {
        grid-template-columns: 1fr;
        gap: 14px;
      }
    }
  </style>
  <!-- ========== Why Shop With Indian MSME Section End ========== -->

  <!-- ========== Newly Launched Products Section Start ========== -->
  @if(isset($newArrivals) && $newArrivals->count() > 0)
    <section class="py-6 pb-12 text-left">
      <div class="container">
        <div class="flex items-center justify-between mb-3 text-left">
          <h3 class="text-xl sm:text-2xl font-extrabold text-slate-900 tracking-tight text-left"
            style="color: #0f172a !important;">
            Newly Launched Products
          </h3>
          <a href="{{ route('shop') }}"
            class="text-xs sm:text-sm font-bold text-slate-900 hover:text-blue-600 inline-flex items-center gap-1.5 transition-colors"
            style="color: #0f172a !important;">
            View All New Arrivals
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
              stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
              <path d="m9 18 6-6-6-6" />
            </svg>
          </a>
        </div>

        <div class="best-selling-products-wrapper">
          <div
            data-slick='{"slidesToShow": 6, "slidesToScroll": 1, "infinite": true, "arrows": true, "autoplay": true, "autoplaySpeed": 7000, "loop": true, "responsive": [{"breakpoint": 1441, "settings": {"slidesToShow": 4}}, {"breakpoint": 1025, "settings": {"slidesToShow": 3}}, {"breakpoint": 769, "settings": {"slidesToShow": 2}}, {"breakpoint": 480, "settings": {"slidesToShow": 1}}]}'
            class="home-one-newly-launched-products-slider -mx-3 hitesh-slider">
            @foreach($newArrivals as $product)
              <div class="single-newly-launched-product-item px-3">
                @include('web.partials.product-card', ['product' => $product])
              </div>
            @endforeach
          </div>
        </div>
      </div>
    </section>
  @endif
  <!-- ========== Newly Launched Products Section End ========== -->

  <!-- ========== Banner Section Start ========== -->
  @if(isset($aboveNewsletterBanners) && $aboveNewsletterBanners->count() > 0)
    <section class="pb-[70px]">
      <div class="container">
        <div class="grid grid-cols-12 gap-6">
          @foreach($aboveNewsletterBanners as $banner)
              <div class="lg:col-span-6 col-span-12 wow animate__animated animate__fadeInUp" data-wow-delay=".2s">
                @if($banner->link_url)
                  <a href="{{ $banner->link_url }}"
                    class="block overflow-hidden rounded-2xl shadow-sm hover:shadow-md transition-all duration-300 transform hover:-translate-y-1">
                @else
                    <div class="overflow-hidden rounded-2xl shadow-sm">
                  @endif
                    <img src="{{ $banner->image_url }}" alt="Banner Image" class="w-full h-auto object-cover rounded-2xl" />
                    @if($banner->link_url)
                      </a>
                    @else
                  </div>
                @endif
            </div>
          @endforeach
      </div>
      </div>
    </section>
  @endif
  <!-- ========== Banner Section End ========== -->




  @push('scripts')
    <script>
      $(document).ready(function () {
        const prevArrowSvg = '<button type="button" class="slick-prev hero-arrow hero-arrow-left" aria-label="Previous"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></button>';
        const nextArrowSvg = '<button type="button" class="slick-next hero-arrow hero-arrow-right" aria-label="Next"><svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></button>';

        // Left Hero Slider - Autoplay 4000ms (4s) only if > 1 slide
        if ($('.hero-left-slider .hero-slide-item').length > 1) {
          $('.hero-left-slider').slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            autoplay: true,
            autoplaySpeed: 4000,
            infinite: true,
            arrows: true,
            prevArrow: prevArrowSvg,
            nextArrow: nextArrowSvg,
            dots: true,
            appendDots: '.hero-left-dots',
            pauseOnHover: true,
            speed: 700,
            cssEase: 'cubic-bezier(0.25, 1, 0.5, 1)'
          });
        } else {
          $('.hero-left-dots').hide();
        }

        // Right Hero Slider - Autoplay 6000ms (6s) only if > 1 slide
        if ($('.hero-right-slider .hero-slide-item').length > 1) {
          $('.hero-right-slider').slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            autoplay: true,
            autoplaySpeed: 6000,
            infinite: true,
            arrows: true,
            prevArrow: prevArrowSvg,
            nextArrow: nextArrowSvg,
            dots: true,
            appendDots: '.hero-right-dots',
            pauseOnHover: true,
            speed: 700,
            cssEase: 'cubic-bezier(0.25, 1, 0.5, 1)'
          });
        } else {
          $('.hero-right-dots').hide();
        }

        window.scrollCatTrack = function (direction) {
          const track = document.getElementById('categoryScrollTrack');
          if (!track) return;
          const scrollAmount = 300;
          if (direction === 'left') {
            track.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
          } else {
            track.scrollBy({ left: scrollAmount, behavior: 'smooth' });
          }
        };
      });
    </script>
  @endpush

@endsection