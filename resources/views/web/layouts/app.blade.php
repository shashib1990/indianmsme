@php
  $adcUser = \App\Models\User::getLoggedInAdc();
@endphp
<!DOCTYPE html>
<html lang="en">

<meta http-equiv="content-type" content="text/html;charset=UTF-8" />

<head>
  <!-- ========== Meta Tags ========== -->
  <meta charset="UTF-8" />
  <meta http-equiv="X-UA-Compatible" content="IE=edge" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>@yield('title', 'Indian MSME - Store')</title>
  <meta name="description"
    content="@yield('meta_description', 'Indian MSME Store is a premium marketplace for Indian micro, small and medium enterprises.')" />

  <!-- ========== Favicon ========== -->
  <link rel="shortcut icon"
    href="{{ \App\Models\SystemSetting::getVal('favicon', asset('ecommerce-assets/images/favicon.png')) }}"
    type="image/x-icon" />

  <!-- ========== Google Fonts (Local) ========== -->
  <link rel="stylesheet" href="{{ asset('fonts/poppins/poppins.css') }}" />

  <!-- ========== Huge Icons CSS ========== -->
  <link rel="stylesheet" href="{{ asset('ecommerce-assets/css/vendor/hugeicons/hgi-stroke-rounded.css') }}" />

  <!-- ========== Slick CSS ========== -->
  <link rel="stylesheet" href="{{ asset('ecommerce-assets/css/vendor/slick.min.css') }}" />

  <!-- ========== Nice Select CSS ========== -->
  <link rel="stylesheet" href="{{ asset('ecommerce-assets/css/vendor/nice-select.css') }}" />

  <!-- ========== Animate CSS ========== -->
  <link rel="stylesheet" href="{{ asset('ecommerce-assets/css/vendor/animate.min.css') }}" />

  <!-- ========== Custom CSS ========== -->
  <link rel="stylesheet" href="{{ asset('ecommerce-assets/css/style.css') }}" />
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <!-- ========== Select2 & Toastify CSS (Local) ========== -->
  <link href="{{ asset('vendor/select2/select2.min.css') }}" rel="stylesheet" />
  <link rel="stylesheet" type="text/css" href="{{ asset('vendor/toastify/toastify.min.css') }}">

  <style>
    :root {
      --color-primary: #3d516b !important;
      --color-primary-light: #1F3A5F !important;
      --color-primary-lighter: #E7EBF0 !important;
      --color-primary-dark: #06172E !important;
      --color-primary-darker: #3d516b !important;
      --font-sans: 'Poppins', sans-serif !important;
      --font-urbanist: 'Poppins', sans-serif !important;
      --font-dm-sans: 'Poppins', sans-serif !important;
      --default-font-family: 'Poppins', sans-serif !important;
    }

    body,
    html,
    h1,
    h2,
    h3,
    h4,
    h5,
    h6,
    p,
    a,
    span,
    button,
    input,
    select,
    textarea,
    label,
    div {
      font-family: 'Poppins', sans-serif !important;
    }
  </style>
  <style>
    /* Premium Select2 Override to match the clean rounded border theme */
    .select2-container--default .select2-selection--single {
      height: 46px !important;
      border: 1px solid #d1d5db !important;
      border-radius: 0.75rem !important;
      padding-left: 8px !important;
      display: flex !important;
      align-items: center !important;
      background-color: #fff !important;
      transition: border-color 0.2s, box-shadow 0.2s;
    }

    .select2-container--default.select2-container--focus .select2-selection--single {
      border-color: #092040 !important;
      box-shadow: 0 0 0 1px #092040 !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 44px !important;
      right: 10px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
      color: #1f2937 !important;
      font-size: 0.875rem !important;
      font-weight: 500 !important;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field {
      border-radius: 0.5rem !important;
      border: 1px solid #d1d5db !important;
      padding: 6px 12px !important;
      outline: none !important;
    }

    .select2-container--default .select2-search--dropdown .select2-search__field:focus {
      border-color: #092040 !important;
    }

    .select2-dropdown {
      border: 1px solid #e5e7eb !important;
      border-radius: 0.75rem !important;
      box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
      overflow: hidden !important;
      z-index: 9999 !important;
    }

    .select2-container--default .select2-results__option--highlighted[aria-selected] {
      background-color: #092040 !important;
      color: white !important;
    }

    .select2-container--default .select2-results__option {
      padding: 8px 12px !important;
      font-size: 0.875rem !important;
    }

    .select2-container {
      margin-top: 2px !important;
    }

    /* Premium scrollbar for the scrollable sidebar registration form */
    .register-page-sidebar-content::-webkit-scrollbar {
      width: 6px;
    }

    .register-page-sidebar-content::-webkit-scrollbar-track {
      background: transparent;
    }

    .register-page-sidebar-content::-webkit-scrollbar-thumb {
      background-color: #cbd5e1;
      border-radius: 3px;
    }

    .register-page-sidebar-content::-webkit-scrollbar-thumb:hover {
      background-color: #94a3b8;
    }

    .register-page-sidebar-content {
      scrollbar-width: thin;
      scrollbar-color: #cbd5e1 transparent;
    }
  </style>

  @stack('styles')
</head>

<body>
  <!-- Preloader Start -->
  <div id="preloader" class="preloader">
    <div class="pxl-loader-spinner">
      <div class="pxl-loader-bounce1"></div>
      <div class="pxl-loader-bounce2"></div>
      <div class="pxl-loader-bounce3"></div>
    </div>
  </div>
  <!-- Preloader End -->

  <!-- Overlay Start -->
  <div data-overlay-for class="modal-overlay hidden w-full h-screen fixed top-0 left-0 bg-[#161C247A] z-90"></div>
  <!-- Overlay End -->

  <!-- Scroll To Top Button Start -->
  <button
    class="scroll-to-top hide btn btn-primary size-10 rounded-[50px] z-50 inline-flex items-center justify-center fixed md:right-8 md:bottom-8 right-[15px] bottom-[85px] transition-all duration-400 ease-in-out">
    <i class="hgi hgi-stroke hgi-arrow-up-01 leading-6 text-2xl"></i>
  </button>
  <!-- Scroll To Top Button End -->

  <!-- SIDEBAR START -->
  <div
    class="fixed top-0 left-0 bottom-0 w-[310px] max-w-[85vw] bg-white px-5 py-6 flex flex-col justify-between overflow-y-auto shadow-2xl transition-all duration-300 ease-in-out"
    id="sidebar" data-state="close"
    style="top: 0 !important; bottom: 0 !important; height: 100vh !important; max-height: 100vh !important; z-index: 99999 !important;">
    <div class="flex flex-col gap-y-6 pb-12">
      <!-- Sidebar Header (Logo without close icon) -->
      <div class="flex flex-col gap-y-2.5 pb-3 border-b border-slate-100">
        <a href="{{ url('/') }}" class="block">
          <img
            src="{{ \App\Models\SystemSetting::getVal('website_header_logo', asset('ecommerce-assets/images/logo.png')) }}"
            alt="Logo" class="object-contain"
            style="width: 155px !important; max-width: 170px !important; height: auto !important;" />
        </a>
        <div class="h-0.5 w-12 rounded-full"
          style="background: linear-gradient(90deg, #0d6efd 0%, #6366f1 100%) !important;"></div>
      </div>

      <!-- Premium Seller Registration CTA Card -->
      <div class="pt-1">
        <a href="{{ route('vendor-account') }}"
          class="flex items-center justify-between w-full p-3.5 rounded-2xl text-white transition-all hover:opacity-95"
          style="background: linear-gradient(135deg, #0d6efd 0%, #0044b3 100%) !important; color: #ffffff !important; text-decoration: none; box-shadow: 0 10px 20px -5px rgba(13, 110, 253, 0.35) !important;">
          <div class="flex items-center gap-x-3">
            <div class="w-10 h-10 rounded-xl bg-white/20 backdrop-blur-sm flex items-center justify-center shrink-0">
              <i class="hgi hgi-stroke hgi-user-add-01 text-xl text-white"></i>
            </div>
            <div class="flex flex-col text-left">
              <span class="text-xs font-extrabold uppercase tracking-wider text-white">Seller Registration</span>
              <span class="text-[10px] text-blue-100 font-medium">Sell Products Nationwide</span>
            </div>
          </div>
          <i class="hgi hgi-stroke hgi-arrow-right-01 text-sm text-white"></i>
        </a>
      </div>

      <!-- Navigation Links Section -->
      <div class="flex flex-col gap-y-2">
        <span class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 px-1"
          style="color: #94a3b8 !important;">Navigation Menu</span>
        <nav class="sidebar-nav-menu">
          <ul class="flex flex-col gap-y-1.5 text-sm font-bold text-slate-800">
            <li>
              <a href="{{ route('home') }}"
                class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('home') ? 'bg-blue-50 text-blue-600' : 'hover:bg-slate-50' }}"
                style="color: {{ request()->routeIs('home') ? '#0d6efd' : '#1e293b' }} !important; text-decoration: none;">
                <div class="flex items-center gap-x-3">
                  <div class="w-8 h-8 rounded-lg bg-blue-50 flex items-center justify-center shrink-0"
                    style="background-color: #eff6ff !important;">
                    <i class="hgi hgi-stroke hgi-home-01 text-base" style="color: #0d6efd !important;"></i>
                  </div>
                  <span>Home</span>
                </div>
                <i class="hgi hgi-stroke hgi-arrow-right-01 text-xs opacity-60 group-hover:translate-x-0.5 transition-transform"
                  style="color: #94a3b8 !important;"></i>
              </a>
            </li>
            <li>
              <a href="{{ route('shop') }}"
                class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('shop') || request()->routeIs('product.show') ? 'bg-blue-50 text-blue-600' : 'hover:bg-slate-50' }}"
                style="color: {{ request()->routeIs('shop') || request()->routeIs('product.show') ? '#0d6efd' : '#1e293b' }} !important; text-decoration: none;">
                <div class="flex items-center gap-x-3">
                  <div class="w-8 h-8 rounded-lg bg-emerald-50 flex items-center justify-center shrink-0"
                    style="background-color: #ecfdf5 !important;">
                    <i class="hgi hgi-stroke hgi-shopping-bag-02 text-base" style="color: #059669 !important;"></i>
                  </div>
                  <span>Shop</span>
                </div>
                <i class="hgi hgi-stroke hgi-arrow-right-01 text-xs opacity-60 group-hover:translate-x-0.5 transition-transform"
                  style="color: #94a3b8 !important;"></i>
              </a>
            </li>
            <li>
              <a href="{{ route('vendors') }}"
                class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ (request()->routeIs('vendors*') || request()->routeIs('vendor*')) ? 'bg-blue-50 text-blue-600' : 'hover:bg-slate-50' }}"
                style="color: {{ (request()->routeIs('vendors*') || request()->routeIs('vendor*')) ? '#0d6efd' : '#1e293b' }} !important; text-decoration: none;">
                <div class="flex items-center gap-x-3">
                  <div class="w-8 h-8 rounded-lg bg-purple-50 flex items-center justify-center shrink-0"
                    style="background-color: #faf5ff !important;">
                    <i class="hgi hgi-stroke hgi-store-01 text-base" style="color: #9333ea !important;"></i>
                  </div>
                  <span>Verified Sellers</span>
                </div>
                <i class="hgi hgi-stroke hgi-arrow-right-01 text-xs opacity-60 group-hover:translate-x-0.5 transition-transform"
                  style="color: #94a3b8 !important;"></i>
              </a>
            </li>
            <li>
              <a href="{{ route('brands') }}"
                class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ (request()->routeIs('brands*') || request()->routeIs('brand*')) ? 'bg-blue-50 text-blue-600' : 'hover:bg-slate-50' }}"
                style="color: {{ (request()->routeIs('brands*') || request()->routeIs('brand*')) ? '#0d6efd' : '#1e293b' }} !important; text-decoration: none;">
                <div class="flex items-center gap-x-3">
                  <div class="w-8 h-8 rounded-lg bg-amber-50 flex items-center justify-center shrink-0"
                    style="background-color: #fffbeb !important;">
                    <i class="hgi hgi-stroke hgi-tag-01 text-base" style="color: #d97706 !important;"></i>
                  </div>
                  <span>Brands</span>
                </div>
                <i class="hgi hgi-stroke hgi-arrow-right-01 text-xs opacity-60 group-hover:translate-x-0.5 transition-transform"
                  style="color: #94a3b8 !important;"></i>
              </a>
            </li>
            <li>
              <a href="{{ route('about') }}"
                class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('about') ? 'bg-blue-50 text-blue-600' : 'hover:bg-slate-50' }}"
                style="color: {{ request()->routeIs('about') ? '#0d6efd' : '#1e293b' }} !important; text-decoration: none;">
                <div class="flex items-center gap-x-3">
                  <div class="w-8 h-8 rounded-lg bg-sky-50 flex items-center justify-center shrink-0"
                    style="background-color: #f0f9ff !important;">
                    <i class="hgi hgi-stroke hgi-information-circle text-base" style="color: #0284c7 !important;"></i>
                  </div>
                  <span>About Us</span>
                </div>
                <i class="hgi hgi-stroke hgi-arrow-right-01 text-xs opacity-60 group-hover:translate-x-0.5 transition-transform"
                  style="color: #94a3b8 !important;"></i>
              </a>
            </li>
            <li>
              <a href="{{ route('contact') }}"
                class="group flex items-center justify-between px-3 py-2 rounded-xl transition-all {{ request()->routeIs('contact') ? 'bg-blue-50 text-blue-600' : 'hover:bg-slate-50' }}"
                style="color: {{ request()->routeIs('contact') ? '#0d6efd' : '#1e293b' }} !important; text-decoration: none;">
                <div class="flex items-center gap-x-3">
                  <div class="w-8 h-8 rounded-lg bg-rose-50 flex items-center justify-center shrink-0"
                    style="background-color: #fff1f2 !important;">
                    <i class="hgi hgi-stroke hgi-mail-01 text-base" style="color: #e11d48 !important;"></i>
                  </div>
                  <span>Contact & Support</span>
                </div>
                <i class="hgi hgi-stroke hgi-arrow-right-01 text-xs opacity-60 group-hover:translate-x-0.5 transition-transform"
                  style="color: #94a3b8 !important;"></i>
              </a>
            </li>
          </ul>
        </nav>
      </div>

      <!-- Trust Badge -->
      <div class="mt-2 p-3 bg-slate-50 rounded-2xl border border-slate-200/80 flex items-center gap-x-3"
        style="background-color: #f8fafc !important; border: 1px solid #e2e8f0 !important;">
        <span class="text-xl shrink-0">🇮🇳</span>
        <div class="flex flex-col text-left">
          <span class="text-[11px] font-bold" style="color: #1e293b !important;">Indian MSME Platform</span>
          <span class="text-[10px] font-medium" style="color: #64748b !important;">Government of India Initiative</span>
        </div>
      </div>
    </div>
  </div>
  <style>
    html,
    body {
      overflow-x: hidden !important;
      max-width: 100vw !important;
      width: 100% !important;
    }

    body.overflow-hidden {
      overflow: hidden !important;
      position: fixed !important;
      width: 100% !important;
      height: 100% !important;
    }

    #sidebar[data-state="open"] {
      transform: translateX(0) !important;
      opacity: 1 !important;
      visibility: visible !important;
    }

    #sidebar[data-state="close"] {
      transform: translateX(-100%) !important;
      opacity: 0 !important;
      visibility: hidden !important;
    }
  </style>
  <!-- SIDEBAR END -->

  <!-- Cart Sidebar Start -->
  <div class="hidden">
    <div class="similar-products max-w-[500px] w-full h-full border-r border-gray-300 xl:block hidden">
      <div class="similar-products-header pt-6 px-6 pb-4 border-b border-gray-300 flex flex-col gap-y-1">
        <h5>Similar Products</h5>
        <p>You May Also Like</p>
      </div>
      <div class="similar-products-content p-6 flex flex-col gap-y-4 overflow-y-auto h-[calc(100%-100px)]">
        <div class="similar-product-item flex items-center gap-x-4 p-4 border border-gray-300 rounded-2xl">
          <a class='similar-product-item-image w-[102px] h-[102px] rounded-xl bg-[#F4F3F5] overflow-hidden relative'
            href="{{ url('/shop') }}">
            <img src="{{ asset('ecommerce-assets/images/vitamin-c.png') }}" alt="Product Image"
              class="w-full h-full object-cover rounded-xl" />
            <span
              class="product-discount-badge absolute top-[11px] left-0 bg-error text-warning-lighter font-medium text-sm leading-[22px] px-1 after:absolute after:top-0 after:left-full after:z-10 after:w-1 after:h-full after:bg-[url('images/discount-shape.html')] after:bg-contain">Sale</span>
          </a>
          <div class="similar-product-item-content flex flex-col gap-y-2 flex-1">
            <h6 class="text-base font-semibold">
              <a href="{{ url('/shop') }}">Fresh Bask Basket Fruits</a>
            </h6>
            <p class="text-sm leading-[22px]">Shop Name</p>
            <div class="flex items-center justify-between">
              <div class="price-section flex items-center gap-x-3">
                <span class="current-price text-base font-semibold text-light-primary-text">$27.49</span>
                <span class="old-price text-base text-light-disabled-text line-through">$39.99</span>
              </div>
              <a class='btn btn-primary btn-medium rounded-full font-semibold text-sm leading-6 px-4 py-2'
                href='{{ route('cart-multi-vendor') }}'>
                <span>
                  <i class="hgi hgi-stroke hgi-shopping-cart-02 text-xl text-white"></i>
                </span>
                <span>Add</span>
              </a>
            </div>
          </div>
        </div>
        <div class="similar-product-item flex items-center gap-x-4 p-4 border border-gray-300 rounded-2xl">
          <a class='similar-product-item-image w-[102px] h-[102px] rounded-xl bg-[#F4F3F5] overflow-hidden relative'
            href="{{ url('/shop') }}">
            <img src="{{ asset('ecommerce-assets/images/apple-juice.png') }}" alt="Product Image"
              class="w-full h-full object-cover rounded-xl" />
            <span
              class="product-discount-badge absolute top-[11px] left-0 bg-error text-warning-lighter font-medium text-sm leading-[22px] px-1 after:absolute after:top-0 after:left-full after:z-10 after:w-1 after:h-full after:bg-[url('images/discount-shape.html')] after:bg-contain">Sale</span>
          </a>
          <div class="similar-product-item-content flex flex-col gap-y-2 flex-1">
            <h6 class="text-base font-semibold">
              <a href="{{ url('/shop') }}">Fresh Bask Basket Fruits</a>
            </h6>
            <p class="text-sm leading-[22px]">Shop Name</p>
            <div class="flex items-center justify-between">
              <div class="price-section flex items-center gap-x-3">
                <span class="current-price text-base font-semibold text-light-primary-text">$27.49</span>
                <span class="old-price text-base text-light-disabled-text line-through">$39.99</span>
              </div>
              <a class='btn btn-primary btn-medium rounded-full font-semibold text-sm leading-6 px-4 py-2'
                href='{{ route('cart-multi-vendor') }}'>
                <span>
                  <i class="hgi hgi-stroke hgi-shopping-cart-02 text-xl text-white"></i>
                </span>
                <span>Add</span>
              </a>
            </div>
          </div>
        </div>
        <div class="similar-product-item flex items-center gap-x-4 p-4 border border-gray-300 rounded-2xl">
          <a class='similar-product-item-image w-[102px] h-[102px] rounded-xl bg-[#F4F3F5] overflow-hidden relative'
            href="{{ url('/shop') }}">
            <img src="{{ asset('ecommerce-assets/images/ground-nuts-oil.png') }}" alt="Product Image"
              class="w-full h-full object-cover rounded-xl" />
            <span
              class="product-discount-badge absolute top-[11px] left-0 bg-error text-warning-lighter font-medium text-sm leading-[22px] px-1 after:absolute after:top-0 after:left-full after:z-10 after:w-1 after:h-full after:bg-[url('images/discount-shape.html')] after:bg-contain">Sale</span>
          </a>
          <div class="similar-product-item-content flex flex-col gap-y-2 flex-1">
            <h6 class="text-base font-semibold">
              <a href="{{ url('/shop') }}">Fresh Bask Basket Fruits</a>
            </h6>
            <p class="text-sm leading-[22px]">Shop Name</p>
            <div class="flex items-center justify-between">
              <div class="price-section flex items-center gap-x-3">
                <span class="current-price text-base font-semibold text-light-primary-text">$27.49</span>
                <span class="old-price text-base text-light-disabled-text line-through">$39.99</span>
              </div>
              <a class='btn btn-primary btn-medium rounded-full font-semibold text-sm leading-6 px-4 py-2'
                href='{{ route('cart-multi-vendor') }}'>
                <span>
                  <i class="hgi hgi-stroke hgi-shopping-cart-02 text-xl text-white"></i>
                </span>
                <span>Add</span>
              </a>
            </div>
          </div>
        </div>
        <div class="similar-product-item flex items-center gap-x-4 p-4 border border-gray-300 rounded-2xl">
          <a class='similar-product-item-image w-[102px] h-[102px] rounded-xl bg-[#F4F3F5] overflow-hidden relative'
            href="{{ url('/shop') }}">
            <img src="{{ asset('ecommerce-assets/images/vitamin-c-2.png') }}" alt="Product Image"
              class="w-full h-full object-cover rounded-xl" />
            <span
              class="product-discount-badge absolute top-[11px] left-0 bg-error text-warning-lighter font-medium text-sm leading-[22px] px-1 after:absolute after:top-0 after:left-full after:z-10 after:w-1 after:h-full after:bg-[url('images/discount-shape.html')] after:bg-contain">Sale</span>
          </a>
          <div class="similar-product-item-content flex flex-col gap-y-2 flex-1">
            <h6 class="text-base font-semibold">
              <a href="{{ url('/shop') }}">Fresh Bask Basket Fruits</a>
            </h6>
            <p class="text-sm leading-[22px]">Shop Name</p>
            <div class="flex items-center justify-between">
              <div class="price-section flex items-center gap-x-3">
                <span class="current-price text-base font-semibold text-light-primary-text">$27.49</span>
                <span class="old-price text-base text-light-disabled-text line-through">$39.99</span>
              </div>
              <a class='btn btn-primary btn-medium rounded-full font-semibold text-sm leading-6 px-4 py-2'
                href='{{ route('cart-multi-vendor') }}'>
                <span>
                  <i class="hgi hgi-stroke hgi-shopping-cart-02 text-xl text-white"></i>
                </span>
                <span>Add</span>
              </a>
            </div>
          </div>
        </div>
        <div class="similar-product-item flex items-center gap-x-4 p-4 border border-gray-300 rounded-2xl">
          <a class='similar-product-item-image w-[102px] h-[102px] rounded-xl bg-[#F4F3F5] overflow-hidden relative'
            href="{{ url('/shop') }}">
            <img src="{{ asset('ecommerce-assets/images/temperature-gun-2.png') }}" alt="Product Image"
              class="w-full h-full object-cover rounded-xl" />
            <span
              class="product-discount-badge absolute top-[11px] left-0 bg-error text-warning-lighter font-medium text-sm leading-[22px] px-1 after:absolute after:top-0 after:left-full after:z-10 after:w-1 after:h-full after:bg-[url('images/discount-shape.html')] after:bg-contain">Sale</span>
          </a>
          <div class="similar-product-item-content flex flex-col gap-y-2 flex-1">
            <h6 class="text-base font-semibold">
              <a href="{{ url('/shop') }}">Fresh Bask Basket Fruits</a>
            </h6>
            <p class="text-sm leading-[22px]">Shop Name</p>
            <div class="flex items-center justify-between">
              <div class="price-section flex items-center gap-x-3">
                <span class="current-price text-base font-semibold text-light-primary-text">$27.49</span>
                <span class="old-price text-base text-light-disabled-text line-through">$39.99</span>
              </div>
              <a class='btn btn-primary btn-medium rounded-full font-semibold text-sm leading-6 px-4 py-2'
                href='{{ route('cart-multi-vendor') }}'>
                <span>
                  <i class="hgi hgi-stroke hgi-shopping-cart-02 text-xl text-white"></i>
                </span>
                <span>Add</span>
              </a>
            </div>
          </div>
        </div>
        <div class="similar-product-item flex items-center gap-x-4 p-4 border border-gray-300 rounded-2xl">
          <a class='similar-product-item-image w-[102px] h-[102px] rounded-xl bg-[#F4F3F5] overflow-hidden relative'
            href="{{ url('/shop') }}">
            <img src="{{ asset('ecommerce-assets/images/aooca.png') }}" alt="Product Image"
              class="w-full h-full object-cover rounded-xl" />
            <span
              class="product-discount-badge absolute top-[11px] left-0 bg-error text-warning-lighter font-medium text-sm leading-[22px] px-1 after:absolute after:top-0 after:left-full after:z-10 after:w-1 after:h-full after:bg-[url('images/discount-shape.html')] after:bg-contain">Sale</span>
          </a>
          <div class="similar-product-item-content flex flex-col gap-y-2 flex-1">
            <h6 class="text-base font-semibold">
              <a href="{{ url('/shop') }}">Fresh Bask Basket Fruits</a>
            </h6>
            <p class="text-sm leading-[22px]">Shop Name</p>
            <div class="flex items-center justify-between">
              <div class="price-section flex items-center gap-x-3">
                <span class="current-price text-base font-semibold text-light-primary-text">$27.49</span>
                <span class="old-price text-base text-light-disabled-text line-through">$39.99</span>
              </div>
              <a class='btn btn-primary btn-medium rounded-full font-semibold text-sm leading-6 px-4 py-2'
                href='{{ route('cart-multi-vendor') }}'>
                <span>
                  <i class="hgi hgi-stroke hgi-shopping-cart-02 text-xl text-white"></i>
                </span>
                <span>Add</span>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="cart-products xl:max-w-[600px] w-full h-full flex flex-col justify-between">
      <div class="cart-products-header pt-6 px-6 pb-4 border-b border-gray-300 flex flex-col gap-y-1 relative">
        <h5>Cart Products</h5>
        <p>1 Item in Cart</p>

        <button data-close-sidebar=".cart-sidebar"
          class="close-sidebar-btn absolute top-6 right-6 cursor-pointer inline-flex items-center justify-center size-9 rounded-full bg-[rgba(145,158,171,0.08)]">
          <i class="hgi hgi-stroke hgi-multiplication-sign text-xl leading-5 text-light-primary-text"></i>
        </button>
      </div>
      <div class="cart-products-content p-6 flex flex-col gap-y-4 overflow-y-auto h-[calc(100%-250px)]">
        <div
          class="cart-product-item flex flex-col sm:flex-row items-center sm:gap-x-4 gap-y-2 sm:gap-y-0 p-4 border border-gray-300 rounded-2xl">
          <a class='cart-product-item-image sm:w-[102px] sm:h-[102px] rounded-xl bg-[#F4F3F5] overflow-hidden relative'
            href="{{ url('/shop') }}">
            <img src="{{ asset('ecommerce-assets/images/home-3/watermelon.png') }}" alt="Product Image"
              class="w-full h-full object-cover rounded-xl" />
          </a>
          <div class="cart-product-item-content flex flex-col gap-y-2 flex-1 w-full">
            <div class="flex items-center justify-between gap-x-2">
              <h6 class="text-base font-semibold">
                <a href="{{ url('/shop') }}">Fresh Bask Basket Fruits</a>
              </h6>
              <div class="cart-edit-remove flex items-center gap-x-3">
                <button>
                  <i class="hgi hgi-stroke hgi-edit-02 text-xl text-light-primary-text"></i>
                </button>
                <button>
                  <i class="hgi hgi-stroke hgi-delete-01 text-xl text-light-primary-text"></i>
                </button>
              </div>
            </div>
            <p class="text-sm leading-[22px]">Color: Black, Size: 250 ML</p>
            <div class="flex items-center justify-between">
              <div class="price-section flex items-center gap-x-3">
                <span class="current-price text-base font-semibold text-light-primary-text">$27.49</span>
                <span class="old-price text-base text-light-disabled-text line-through">$39.99</span>
              </div>
              <div
                class="border border-gray-300 inline-flex items-center justify-center rounded-[80px] max-w-[108px] py-2.5 px-4">
                <button class="inline-flex items-center justify-center hover:text-primary">
                  <i class="hgi hgi-stroke hgi-remove-circle text-2xl leading-6"></i>
                </button>
                <input type="text" readonly value="1"
                  class="border-0 w-full grow text-center focus:outline-none font-semibold" />

                <button class="inline-flex items-center justify-center hover:text-primary">
                  <i class="hgi hgi-stroke hgi-add-circle text-2xl leading-6"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
        <div
          class="cart-product-item flex flex-col sm:flex-row items-center sm:gap-x-4 gap-y-2 sm:gap-y-0 p-4 border border-gray-300 rounded-2xl">
          <a class='cart-product-item-image sm:w-[102px] sm:h-[102px] rounded-xl bg-[#F4F3F5] overflow-hidden relative'
            href="{{ url('/shop') }}">
            <img src="{{ asset('ecommerce-assets/images/home-3/avocado.png') }}" alt="Product Image"
              class="w-full h-full object-cover rounded-xl" />
          </a>
          <div class="cart-product-item-content flex flex-col gap-y-2 flex-1 w-full">
            <div class="flex items-center justify-between gap-x-2">
              <h6 class="text-base font-semibold">
                <a href="{{ url('/shop') }}">Fresh Bask Basket Fruits</a>
              </h6>
              <div class="cart-edit-remove flex items-center gap-x-3">
                <button>
                  <i class="hgi hgi-stroke hgi-edit-02 text-xl text-light-primary-text"></i>
                </button>
                <button>
                  <i class="hgi hgi-stroke hgi-delete-01 text-xl text-light-primary-text"></i>
                </button>
              </div>
            </div>
            <p class="text-sm leading-[22px]">Color: Black, Size: 250 ML</p>
            <div class="flex items-center justify-between">
              <div class="price-section flex items-center gap-x-3">
                <span class="current-price text-base font-semibold text-light-primary-text">$27.49</span>
                <span class="old-price text-base text-light-disabled-text line-through">$39.99</span>
              </div>
              <div
                class="border border-gray-300 inline-flex items-center justify-center rounded-[80px] max-w-[108px] py-2.5 px-4">
                <button class="inline-flex items-center justify-center hover:text-primary">
                  <i class="hgi hgi-stroke hgi-remove-circle text-2xl leading-6"></i>
                </button>
                <input type="text" readonly value="1"
                  class="border-0 w-full grow text-center focus:outline-none font-semibold" />

                <button class="inline-flex items-center justify-center hover:text-primary">
                  <i class="hgi hgi-stroke hgi-add-circle text-2xl leading-6"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
        <div
          class="cart-product-item flex flex-col sm:flex-row items-center sm:gap-x-4 gap-y-2 sm:gap-y-0 p-4 border border-gray-300 rounded-2xl">
          <a class='cart-product-item-image sm:w-[102px] sm:h-[102px] rounded-xl bg-[#F4F3F5] overflow-hidden relative'
            href="{{ url('/shop') }}">
            <img src="{{ asset('ecommerce-assets/images/home-3/pouch-mockup.png') }}" alt="Product Image"
              class="w-full h-full object-cover rounded-xl" />
            <span
              class="product-discount-badge absolute top-1/2 -translate-y-1/2 left-0 bg-error text-warning-lighter font-medium text-sm leading-[22px] px-1 after:absolute after:top-0 after:left-full after:z-10 after:w-1 after:h-full after:bg-[url('images/discount-shape.html')] after:bg-contain">Out
              of Stock</span>
          </a>
          <div class="cart-product-item-content flex flex-col gap-y-2 flex-1 w-full">
            <div class="flex items-center justify-between gap-x-2">
              <h6 class="text-base font-semibold">
                <a href="{{ url('/shop') }}">Fresh Bask Basket Fruits</a>
              </h6>
              <div class="cart-edit-remove flex items-center gap-x-3">
                <button>
                  <i class="hgi hgi-stroke hgi-edit-02 text-xl text-light-primary-text"></i>
                </button>
                <button>
                  <i class="hgi hgi-stroke hgi-delete-01 text-xl text-light-primary-text"></i>
                </button>
              </div>
            </div>
            <p class="text-sm leading-[22px]">Color: Black, Size: 250 ML</p>
            <div class="flex items-center justify-between">
              <div class="price-section flex items-center gap-x-3">
                <span class="current-price text-base font-semibold text-light-primary-text">$27.49</span>
                <span class="old-price text-base text-light-disabled-text line-through">$39.99</span>
              </div>
              <div
                class="border border-gray-300 inline-flex items-center justify-center rounded-[80px] max-w-[108px] py-2.5 px-4">
                <button class="inline-flex items-center justify-center hover:text-primary">
                  <i class="hgi hgi-stroke hgi-remove-circle text-2xl leading-6"></i>
                </button>
                <input type="text" readonly value="1"
                  class="border-0 w-full grow text-center focus:outline-none font-semibold" />

                <button class="inline-flex items-center justify-center hover:text-primary">
                  <i class="hgi hgi-stroke hgi-add-circle text-2xl leading-6"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
        <div
          class="cart-product-item flex flex-col sm:flex-row items-center sm:gap-x-4 gap-y-2 sm:gap-y-0 p-4 border border-gray-300 rounded-2xl">
          <a class='cart-product-item-image sm:w-[102px] sm:h-[102px] rounded-xl bg-[#F4F3F5] overflow-hidden relative'
            href="{{ url('/shop') }}">
            <img src="{{ asset('ecommerce-assets/images/home-3/strawberry-snacks.png') }}" alt="Product Image"
              class="w-full h-full object-cover rounded-xl" />
          </a>
          <div class="cart-product-item-content flex flex-col gap-y-2 flex-1 w-full">
            <div class="flex items-center justify-between gap-x-2">
              <h6 class="text-base font-semibold">
                <a href="{{ url('/shop') }}">Fresh Bask Basket Fruits</a>
              </h6>
              <div class="cart-edit-remove flex items-center gap-x-3">
                <button>
                  <i class="hgi hgi-stroke hgi-edit-02 text-xl text-light-primary-text"></i>
                </button>
                <button>
                  <i class="hgi hgi-stroke hgi-delete-01 text-xl text-light-primary-text"></i>
                </button>
              </div>
            </div>
            <p class="text-sm leading-[22px]">Color: Black, Size: 250 ML</p>
            <div class="flex items-center justify-between">
              <div class="price-section flex items-center gap-x-3">
                <span class="current-price text-base font-semibold text-light-primary-text">$27.49</span>
                <span class="old-price text-base text-light-disabled-text line-through">$39.99</span>
              </div>
              <div
                class="border border-gray-300 inline-flex items-center justify-center rounded-[80px] max-w-[108px] py-2.5 px-4">
                <button class="inline-flex items-center justify-center hover:text-primary">
                  <i class="hgi hgi-stroke hgi-remove-circle text-2xl leading-6"></i>
                </button>
                <input type="text" readonly value="1"
                  class="border-0 w-full grow text-center focus:outline-none font-semibold" />

                <button class="inline-flex items-center justify-center hover:text-primary">
                  <i class="hgi hgi-stroke hgi-add-circle text-2xl leading-6"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
        <div
          class="cart-product-item flex flex-col sm:flex-row items-center sm:gap-x-4 gap-y-2 sm:gap-y-0 p-4 border border-gray-300 rounded-2xl">
          <a class='cart-product-item-image sm:w-[102px] sm:h-[102px] rounded-xl bg-[#F4F3F5] overflow-hidden relative'
            href="{{ url('/shop') }}">
            <img src="{{ asset('ecommerce-assets/images/home-3/pomegranate.png') }}" alt="Product Image"
              class="w-full h-full object-cover rounded-xl" />
          </a>
          <div class="cart-product-item-content flex flex-col gap-y-2 flex-1 w-full">
            <div class="flex items-center justify-between gap-x-2">
              <h6 class="text-base font-semibold">
                <a href="{{ url('/shop') }}">Fresh Bask Basket Fruits</a>
              </h6>
              <div class="cart-edit-remove flex items-center gap-x-3">
                <button>
                  <i class="hgi hgi-stroke hgi-edit-02 text-xl text-light-primary-text"></i>
                </button>
                <button>
                  <i class="hgi hgi-stroke hgi-delete-01 text-xl text-light-primary-text"></i>
                </button>
              </div>
            </div>
            <p class="text-sm leading-[22px]">Color: Black, Size: 250 ML</p>
            <div class="flex items-center justify-between">
              <div class="price-section flex items-center gap-x-3">
                <span class="current-price text-base font-semibold text-light-primary-text">$27.49</span>
                <span class="old-price text-base text-light-disabled-text line-through">$39.99</span>
              </div>
              <div
                class="border border-gray-300 inline-flex items-center justify-center rounded-[80px] max-w-[108px] py-2.5 px-4">
                <button class="inline-flex items-center justify-center hover:text-primary">
                  <i class="hgi hgi-stroke hgi-remove-circle text-2xl leading-6"></i>
                </button>
                <input type="text" readonly value="1"
                  class="border-0 w-full grow text-center focus:outline-none font-semibold" />

                <button class="inline-flex items-center justify-center hover:text-primary">
                  <i class="hgi hgi-stroke hgi-add-circle text-2xl leading-6"></i>
                </button>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="cart-products-footer p-6 border-t border-gray-300 flex flex-col gap-y-4">
        <div class="cart-subtotal flex items-center justify-between">
          <h5>Subtotal</h5>
          <h5>₹{{ number_format($cartSubtotal ?? 0, 2) }}</h5>
        </div>
        <div class="cart-buttons flex items-center gap-x-4">
          <a class='btn btn-default outline btn-large rounded-[80px] flex-1 shadow-none'
            href='{{ route("adc.cart.index") }}'>
            View Cart
          </a>
          <a class='btn btn-primary btn-large rounded-[80px] flex-1' href='{{ route("adc.checkout.index") }}'>
            Proceed to Checkout
          </a>
        </div>
      </div>
    </div>
  </div>
  <!-- Cart Sidebar End -->



  <!-- Register/Login Modals Removed -->

  <!-- Quick View Sidebar Start -->
  <div data-state="close"
    class="quick-view-sidebar fixed xl:top-[30px] xl:right-[22px] right-0 top-0 xl:h-[calc(100vh-52px)] h-full z-99 max-w-[950px] w-full bg-white xl:rounded-2xl rounded-none transition-all duration-250 ease-[cubic-bezier(0.645,0.045,0.355,1)] data-[state=open]:translate-x-0 data-[state=open]:opacity-100 data-[state=open]:visible data-[state=close]:translate-x-[200px] data-[state=close]:opacity-0 data-[state=close]:invisible">
    <div class="quick-view-sidebar-header px-6 py-[15px] border-b border-gray-300 relative">
      <h5>Quick View</h5>
      <button data-close-sidebar=".quick-view-sidebar"
        class="close-sidebar-btn absolute top-1/2 right-6 -translate-y-1/2 cursor-pointer inline-flex items-center justify-center size-9 rounded-full bg-[rgba(145,158,171,0.08)]">
        <i class="hgi hgi-stroke hgi-multiplication-sign text-xl leading-5 text-light-primary-text"></i>
      </button>
    </div>
    <div
      class="quick-view-sidebar-content lg:flex lg:flex-row p-6 gap-x-6 gap-y-6 lg:gap-y-0 w-full lg:h-full h-[calc(100%-60px)] overflow-y-auto">
      <div
        class="product-images-wrapper lg:max-w-[350px] w-full lg:flex-none lg:overflow-y-auto lg:h-[calc(100%-60px)] h-auto mb-6 lg:mb-0">
        <div class="space-y-4">
          <img class="max-h-[300px] w-full rounded-xl object-cover"
            src="{{ asset('ecommerce-assets/images/product-details/product-details-18.png') }}" alt="product-image" />
          <img class="max-h-[300px] w-full rounded-xl object-cover"
            src="{{ asset('ecommerce-assets/images/product-details/product-details-16.png') }}" alt="product-image" />
          <img class="max-h-[300px] w-full rounded-xl object-cover"
            src="{{ asset('ecommerce-assets/images/product-details/product-details-17.png') }}" alt="product-image" />
        </div>
      </div>
      <div
        class="product-details-wrapper w-full lg:flex-1 @container/quick-view-product-details lg:overflow-y-auto lg:h-[calc(100%-60px)] h-auto pr-4">
        <span
          class="product-discount-badge inline-block mb-4 uppercase relative bg-error text-warning-lighter font-medium text-sm leading-[22px] px-1 after:absolute after:top-0 after:left-full after:z-10 after:w-1 after:h-full after:bg-[url('images/discount-shape.html')] after:bg-contain after:bg-no-repeat">Sales</span>
        <p class="uppercase text-info text-xs leading-[18px] font-bold new-arrival-badge mb-4">
          New Arrival
        </p>
        <h4 class="line-clamp-2 mb-4">AE 24/7 Active Hoodie With Gaiter</h4>
        <div class="rating-section flex items-center mb-4">
          <div class="bg-[url('../images/star-icon.png')] w-[90px] h-4.5 bg-repeat-x overflow-hidden bg-position-[0_0]">
            <div style="width: 80%" class="bg-[url('../images/star-icon.png')] h-4.5 bg-repeat-x bg-position-[0_-18px]">
            </div>
          </div>
          <span class="font-normal inline-block ml-1">(11.78k reviews)</span>
        </div>
        <div class="price-section flex items-center gap-x-3 mb-6">
          <span
            class="current-price text-2xl leading-9 font-bold text-light-primary-text relative after:absolute after:h-6 after:w-px after:bg-gray-300 after:right-0 after:top-1/2 after:-translate-y-1/2 pr-3 inline-block">$62.97</span>
          <span class="old-price text-2xl leading-9 font-normal text-light-disabled-text">$39.99</span>
          <span
            class="product-discount-badge uppercase relative bg-error text-warning-lighter font-medium text-sm leading-[22px] px-1 after:absolute after:top-0 after:left-full after:z-10 after:w-1 after:h-full after:bg-[url('images/discount-shape.html')] after:bg-contain after:bg-no-repeat">15%
            OFF</span>
        </div>
        <div class="product-add-to-cart-section py-6 border-b border-dashed border-gray-300 border-t">
          <div class="color-variation-section mb-6">
            <div class="color-variation-section-title mb-4">
              <p class="font-semibold text-light-primary-text flex items-center gap-x-2.5">
                Color:
                <span class="text-light-primary-text font-normal capitalize color-variation-selected-color">Green</span>
              </p>
            </div>
            <div class="color-variation-items flex items-center gap-x-2">
              <div class="color-variation-item">
                <button data-color-text="green" data-color="#088178"
                  class="cursor-pointer flex items-center justify-center rounded-full size-10 border border-primary hover:bg-[rgba(145,158,171,0.08)]">
                  <img src="{{ asset('ecommerce-assets/images/product-details/color-selector-1.png') }}" alt="color" />
                </button>
              </div>
              <div class="color-variation-item">
                <button data-color-text="blue" data-color="#3366FF"
                  class="cursor-pointer flex items-center justify-center rounded-full size-10 border border-gray-300 hover:bg-[rgba(145,158,171,0.08)]">
                  <img src="{{ asset('ecommerce-assets/images/product-details/color-selector-2.png') }}" alt="color" />
                </button>
              </div>
              <div class="color-variation-item">
                <button data-color-text="yellow" data-color="#FFC107"
                  class="cursor-pointer flex items-center justify-center rounded-full size-10 border border-gray-300 hover:bg-[rgba(145,158,171,0.08)]">
                  <img src="{{ asset('ecommerce-assets/images/product-details/color-selector-3.png') }}" alt="color" />
                </button>
              </div>
              <div class="color-variation-item">
                <button data-color-text="red" data-color="#CB0233"
                  class="cursor-pointer flex items-center justify-center rounded-full size-10 border border-gray-300 hover:bg-[rgba(145,158,171,0.08)]">
                  <img src="{{ asset('ecommerce-assets/images/product-details/color-selector-4.png') }}" alt="color" />
                </button>
              </div>
            </div>
          </div>
          <div class="size-variation-section mb-6">
            <div class="size-variation-section-title mb-4 flex items-center justify-between">
              <p class="font-semibold text-light-primary-text flex items-center gap-x-2.5">
                Size:
                <span class="text-light-primary-text font-normal capitalize size-variation-selected-size">S</span>
              </p>
              <!-- <a
                  href="#"
                  class="text-sm leading-[22px] hover:underline variation-size-guide-btn"
                  >See Size Chart</a
                > -->
            </div>
            <div class="size-variation-items flex items-center gap-2 2xl:flex-nowrap flex-wrap">
              <div class="size-variation-item">
                <button data-size-text="S"
                  class="cursor-pointer flex items-center justify-center text-sm leading-6 px-[29px] py-1.5 font-semibold border border-primary bg-primary rounded-[100px] text-white hover:bg-primary">
                  S
                </button>
              </div>
              <div class="size-variation-item">
                <button data-size-text="M"
                  class="cursor-pointer flex items-center justify-center text-sm leading-6 px-[29px] py-1.5 text-light-primary-text font-semibold border border-gray-300 rounded-[100px] hover:bg-[rgba(145,158,171,0.08)]">
                  M
                </button>
              </div>
              <div class="size-variation-item">
                <button data-size-text="L"
                  class="cursor-pointer flex items-center justify-center text-sm leading-6 px-[29px] py-1.5 text-light-primary-text font-semibold border border-gray-300 rounded-[100px] hover:bg-[rgba(145,158,171,0.08)]">
                  L
                </button>
              </div>
              <div class="size-variation-item">
                <button data-size-text="XL"
                  class="cursor-pointer flex items-center justify-center text-sm leading-6 px-[29px] py-1.5 text-light-primary-text font-semibold border border-gray-300 rounded-[100px] hover:bg-[rgba(145,158,171,0.08)]">
                  XL
                </button>
              </div>
              <div class="size-variation-item">
                <button data-size-text="XXL"
                  class="cursor-pointer flex items-center justify-center text-sm leading-6 px-[29px] py-2 text-light-primary-text font-semibold border border-gray-300 rounded-[100px] hover:bg-[rgba(145,158,171,0.08)]">
                  XXL
                </button>
              </div>
              <div class="size-variation-item">
                <button data-size-text="XXXL"
                  class="cursor-pointer flex items-center justify-center text-sm leading-6 px-[29px] py-2 text-light-primary-text font-semibold border border-gray-300 rounded-[100px] hover:bg-[rgba(145,158,171,0.08)]">
                  XXXL
                </button>
              </div>
            </div>
          </div>
          <div class="product-add-to-cart-btn-section">
            <p class="font-semibold text-light-primary-text mb-4">
              Quantity:
            </p>
            <div class="flex items-center justify-between gap-x-4 gap-y-4 flex-wrap md:flex-nowrap md:gap-y-0">
              <div
                class="quantity-section flex-1 max-w-[126px] border border-gray-300 rounded-[80px] px-4 py-[11px] flex items-center justify-between">
                <button class="quantity-btn inline-flex items-center justify-center hover:text-primary">
                  <i class="hgi hgi-stroke hgi-minus-sign text-xl leading-5"></i>
                </button>
                <input type="text"
                  class="quantity-input text-center w-full focus:outline-none font-semibold text-base leading-6 text-light-primary-text"
                  value="1" />
                <button class="quantity-btn inline-flex items-center justify-center hover:text-primary">
                  <i class="hgi hgi-stroke hgi-plus-sign text-xl leading-5"></i>
                </button>
              </div>
              <div class="flex-1">
                <a href="#" class="btn btn-warning btn-large rounded-[80px] w-full">
                  Buy Now
                </a>
              </div>
              <div class="flex-1">
                <a href="#" class="btn btn-primary btn-large rounded-[80px] w-full">
                  <i class="hgi hgi-stroke hgi-shopping-cart-add-02 text-2xl leading-6 text-white"></i>
                  Add to Cart
                </a>
              </div>
            </div>
          </div>
        </div>
        <div class="product-extra-info-section flex flex-col gap-y-4 my-6">
          <div
            class="product-extra-info-item flex items-center gap-x-4 gap-y-4 flex-wrap @md/quick-view-product-details:flex-nowrap @md/quick-view-product-details:gap-y-0">
            <p class="font-semibold text-light-primary-text">
              Free Shipping:
            </p>
            <p>Estimated Delivery Time 5-7 Days</p>
          </div>
          <div
            class="product-extra-info-item flex items-center gap-x-4 gap-y-4 flex-wrap @md/quick-view-product-details:flex-nowrap @md/quick-view-product-details:gap-y-0">
            <p class="font-semibold text-light-primary-text">SKU:</p>
            <p>SKU-001</p>
          </div>
          <div
            class="product-extra-info-item flex items-center gap-x-4 gap-y-4 flex-wrap @md/quick-view-product-details:flex-nowrap @md/quick-view-product-details:gap-y-0">
            <p class="font-semibold text-light-primary-text">Categories:</p>
            <p>Electronics, Computers, Accessories</p>
          </div>
        </div>
        <div class="accordion" id="product-details-accordion">
          <div class="accordion-item">
            <div class="accordion-header active">
              <h5>Description</h5>
              <i class="hgi hgi-stroke hgi-arrow-down-01 text-2xl leading-6"></i>
            </div>

            <div class="accordion-body">
              <p class="mb-6">
                To begin, carefully unpack the product and ensure all
                necessary components are included. Place the product on a
                clean, flat surface before use. If the device requires power,
                insert the batteries or connect it to a power source as
                indicated in the manual. Press the power button to activate
                the product, and refer to the control panel or app interface
                to adjust the settings according to your preference. Use the
                product as recommended, following all safety guidelines
                provided. After use, turn off the device and disconnect it
                from the power source. Clean and store the product in a cool,
                dry place to maintain its condition for future use.
              </p>
              <p class="mb-6">
                To begin, carefully unpack the product and ensure all
                necessary components are included. Place the product on a
                clean, flat surface before use. If the device requires power,
                insert the batteries or connect it to a power source as
                indicated in the manual. Press the power button to activate
                the product, and refer to the control panel or app interface
                to adjust the settings according to your preference. Use the
                product as recommended, following all safety guidelines
                provided. After use, turn off the device and disconnect it
                from the power source. Clean and store the product in a cool,
                dry place to maintain its condition for future use.
              </p>
              <div class="mb-6">
                <ul class="custom-list-style">
                  <li>
                    <p>
                      Real-time shipping rates, tracking, and delivery
                      management
                    </p>
                  </li>
                  <li>
                    <p>
                      Real-time stock tracking and alerts for low inventory
                    </p>
                  </li>
                  <li>
                    <p>
                      Support for multiple payment options like credit cards,
                      PayPal, Stripe,
                    </p>
                  </li>
                  <li>
                    <p>
                      Real-time shipping rates, tracking, and delivery
                      management
                    </p>
                  </li>
                  <li>
                    <p>
                      Real-time stock tracking and alerts for low inventory
                    </p>
                  </li>
                  <li>
                    <p>
                      Support for multiple payment options like credit cards,
                      PayPal, Stripe,
                    </p>
                  </li>
                </ul>
              </div>
              <div class="mb-6">
                <img src="{{ asset('ecommerce-assets/images/product-details/product-details-19.png') }}"
                  alt="product-details" class="w-full h-full object-cover rounded-3xl max-h-[500px]" />
              </div>
              <div class="mb-6">
                <h4 class="mb-6">🛒 eCommerce Platform Features</h4>
                <p class="mb-6">
                  Launch, manage, and grow your online store with our
                  all-in-one eCommerce solution. Whether you're a small
                  business or a growing brand, our platform is designed to
                  simplify the selling process and enhance your customers’
                  shopping experience. From product listings to secure
                  payments and seamless shipping, everything you need is built
                  right in.
                </p>
                <ul class="custom-list-style">
                  <li>
                    <p>
                      Real-time shipping rates, tracking, and delivery
                      management
                    </p>
                  </li>
                  <li>
                    <p>
                      Real-time stock tracking and alerts for low inventory
                    </p>
                  </li>
                  <li>
                    <p>
                      Support for multiple payment options like credit cards,
                      PayPal, Stripe,
                    </p>
                  </li>
                  <li>
                    <p>
                      Seamless experience across smartphones, tablets, and
                      desktops
                    </p>
                  </li>
                  <li>
                    <p>
                      Track sales, traffic, conversion rates, and customer
                      behavior
                    </p>
                  </li>
                  <li>
                    <p>
                      Allow customers to track orders, reorder, and manage
                      profiles
                    </p>
                  </li>
                </ul>
              </div>
              <div class="mb-6">
                <h4 class="mb-6">Easy to Customization</h4>
                <p class="mb-6">
                  Launch, manage, and grow your online store with our
                  all-in-one eCommerce solution. Whether you're a small
                  business or a growing brand, our platform is designed to
                  simplify the selling process and enhance your customers’
                  shopping experience. From product listings to secure
                  payments and seamless shipping, everything you need is built
                  right in.
                </p>
                <ul class="custom-list-style">
                  <li>
                    <p>
                      Real-time shipping rates, tracking, and delivery
                      management
                    </p>
                  </li>
                  <li>
                    <p>
                      Real-time stock tracking and alerts for low inventory
                    </p>
                  </li>
                  <li>
                    <p>
                      Support for multiple payment options like credit cards,
                      PayPal, Stripe,
                    </p>
                  </li>
                  <li>
                    <p>
                      Seamless experience across smartphones, tablets, and
                      desktops
                    </p>
                  </li>
                  <li>
                    <p>
                      Track sales, traffic, conversion rates, and customer
                      behavior
                    </p>
                  </li>
                  <li>
                    <p>
                      Allow customers to track orders, reorder, and manage
                      profiles
                    </p>
                  </li>
                </ul>
              </div>
              <div class="mb-6">
                <img src="{{ asset('ecommerce-assets/images/product-details/product-details-20.png') }}"
                  alt="product-details" class="w-full h-full object-cover rounded-3xl max-h-[500px]" />
              </div>
              <div class="grid grid-cols-12 gap-6 mb-6">
                <div class="col-span-12 p-6 border-gray-300 border rounded-2xl text-center">
                  <span class="inline-flex items-center justify-center size-14 bg-warning-lighter rounded-full">
                    <i class="hgi hgi-stroke hgi-container-truck text-3xl text-light-primary-text"></i>
                  </span>
                  <h5 class="pt-3 pb-0.5">Free Shipping</h5>
                  <p>Enjoy the Convenience of Free Shipping on Every Order</p>
                </div>
                <div class="col-span-12 p-6 border-gray-300 border rounded-2xl text-center">
                  <span class="inline-flex items-center justify-center size-14 bg-warning-lighter rounded-full">
                    <i class="hgi hgi-stroke hgi-customer-support text-3xl text-light-primary-text"></i>
                  </span>
                  <h5 class="pt-3 pb-0.5">24x7 Support</h5>
                  <p>Round-the-Clock Assistance, Anytime You Need It</p>
                </div>
                <div class="col-span-12 p-6 border-gray-300 border rounded-2xl text-center">
                  <span class="inline-flex items-center justify-center size-14 bg-warning-lighter rounded-full">
                    <i class="hgi hgi-stroke hgi-delivery-return-02 text-3xl text-light-primary-text"></i>
                  </span>
                  <h5 class="pt-3 pb-0.5">Easy Returns</h5>
                  <p>
                    Hassle-free return policy for damaged or defective items.
                  </p>
                </div>
                <div class="col-span-12 p-6 border-gray-300 border rounded-2xl text-center">
                  <span class="inline-flex items-center justify-center size-14 bg-warning-lighter rounded-full">
                    <i class="hgi hgi-stroke hgi-transaction text-3xl text-light-primary-text"></i>
                  </span>
                  <h5 class="pt-3 pb-0.5">Secure Payment</h5>
                  <p>
                    Seamless Shopping Backed by Safe and Secure Payment
                    Options
                  </p>
                </div>
              </div>
              <p>
                To begin, carefully unpack the product and ensure all
                necessary components are included. Place the product on a
                clean, flat surface before use. If the device requires power,
                insert the batteries or connect it to a power source as
                indicated in the manual. Press the power button to activate
                the product, and refer to the control panel or app interface
                to adjust the settings according to your preference. Use the
                product as recommended, following all safety guidelines
                provided. After use, turn off the device and disconnect it
                from the power source. Clean and store the product in a cool,
                dry place to maintain its condition for future use.
              </p>
            </div>
          </div>
          <div class="accordion-item">
            <div class="accordion-header">
              <h5>Additional Info</h5>
              <i class="hgi hgi-stroke hgi-arrow-down-01 text-2xl leading-6"></i>
            </div>

            <div class="accordion-body">
              <table class="w-full border-collapse">
                <tbody class="divide-y divide-gray-300">
                  <tr>
                    <th class="font-semibold w-[180px] text-left py-3 text-light-primary-text">
                      Product Type
                    </th>
                    <td>Touchless Infrared Thermometer</td>
                  </tr>
                  <tr>
                    <th class="font-semibold w-[180px] text-left py-3 text-light-primary-text">
                      Brand
                    </th>
                    <td>Mediguard</td>
                  </tr>
                  <tr>
                    <th class="font-semibold w-[180px] text-left py-3 text-light-primary-text">
                      Model
                    </th>
                    <td>X200</td>
                  </tr>
                  <tr>
                    <th class="font-semibold w-[180px] text-left py-3 text-light-primary-text">
                      Measurement Time
                    </th>
                    <td>1 Second</td>
                  </tr>
                  <tr>
                    <th class="font-semibold w-[180px] text-left py-3 text-light-primary-text">
                      Contact Type
                    </th>
                    <td>Non-Contact</td>
                  </tr>
                  <tr>
                    <th class="font-semibold w-[180px] text-left py-3 text-light-primary-text">
                      Measurement Range
                    </th>
                    <td>32°C – 42.9°C / 89.6°F – 109.2°F</td>
                  </tr>
                  <tr>
                    <th class="font-semibold w-[180px] text-left py-3 text-light-primary-text">
                      Modes
                    </th>
                    <td>Body & Surface</td>
                  </tr>
                  <tr>
                    <th class="font-semibold w-[180px] text-left py-3 text-light-primary-text">
                      Accuracy
                    </th>
                    <td>±0.2°C / ±0.4°F</td>
                  </tr>
                  <tr>
                    <th class="font-semibold w-[180px] text-left py-3 text-light-primary-text">
                      Memory Capacity
                    </th>
                    <td>20 Readings</td>
                  </tr>
                  <tr>
                    <th class="font-semibold w-[180px] text-left py-3 text-light-primary-text">
                      Display Type
                    </th>
                    <td>Backlit LCD</td>
                  </tr>
                  <tr>
                    <th class="font-semibold w-[180px] text-left py-3 text-light-primary-text">
                      Battery Type
                    </th>
                    <td>2 × AAA (not included)</td>
                  </tr>
                  <tr>
                    <th class="font-semibold w-[180px] text-left py-3 text-light-primary-text">
                      Dimensions
                    </th>
                    <td>150mm × 40mm × 45mm</td>
                  </tr>
                  <tr>
                    <th class="font-semibold w-[180px] text-left py-3 text-light-primary-text">
                      Weight
                    </th>
                    <td>90g (without batteries)</td>
                  </tr>
                  <tr>
                    <th class="font-semibold w-[180px] text-left py-3 text-light-primary-text">
                      Warranty
                    </th>
                    <td>1 Year</td>
                  </tr>
                  <tr>
                    <th class="font-semibold w-[180px] text-left py-3 text-light-primary-text">
                      Usage
                    </th>
                    <td>Suitable for all age groups</td>
                  </tr>
                  <tr>
                    <th class="font-semibold w-[180px] text-left py-3 text-light-primary-text">
                      Certification
                    </th>
                    <td>CE, FDA Approved</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
          <div class="accordion-item">
            <div class="accordion-header">
              <h5>Reviews</h5>
              <i class="hgi hgi-stroke hgi-arrow-down-01 text-2xl leading-6"></i>
            </div>

            <div class="p-0! accordion-body">
              <div class="grid grid-cols-12 divide-y divide-gray-300 rating-overview">
                <div class="col-span-12 flex items-center justify-center py-6">
                  <div class="rating-heading space-y-2 text-center">
                    <p class="font-semibold text-light-primary-text">
                      Average Rating
                    </p>
                    <h2 class="text-error">4/5</h2>
                    <div class="rating-section flex items-center justify-center">
                      <div
                        class="bg-[url('../images/star-icon.png')] w-[90px] h-4.5 bg-repeat-x overflow-hidden bg-position-[0_0]">
                        <div style="width: 80%"
                          class="bg-[url('../images/star-icon.png')] h-4.5 bg-repeat-x bg-position-[0_-18px]"></div>
                      </div>
                    </div>
                    <p>(8.24k reviews)</p>
                  </div>
                </div>
                <div class="col-span-12 p-6 flex items-center justify-center">
                  <div class="list-rating space-y-6 w-full">
                    <div class="flex gap-x-4 items-center">
                      <span class="font-semibold text-light-primary-text">5 Star</span>
                      <div
                        class="progress w-full flex-1 h-1.5 bg-[rgba(145,158,171,0.24)] rounded-[50px] overflow-hidden">
                        <div style="width: 70%" class="progress-bar h-full bg-primary rounded-[50px]"></div>
                      </div>
                      <span>35.74k</span>
                    </div>
                    <div class="flex gap-x-4 items-center">
                      <span class="font-semibold text-light-primary-text">4 Star</span>
                      <div
                        class="progress w-full flex-1 h-1.5 bg-[rgba(145,158,171,0.24)] rounded-[50px] overflow-hidden">
                        <div style="width: 70%" class="progress-bar h-full bg-primary rounded-[50px]"></div>
                      </div>
                      <span>79.41k</span>
                    </div>
                    <div class="flex gap-x-4 items-center">
                      <span class="font-semibold text-light-primary-text">3 Star</span>
                      <div
                        class="progress w-full flex-1 h-1.5 bg-[rgba(145,158,171,0.24)] rounded-[50px] overflow-hidden">
                        <div style="width: 70%" class="progress-bar h-full bg-primary rounded-[50px]"></div>
                      </div>
                      <span>60.69k</span>
                    </div>
                    <div class="flex gap-x-4 items-center">
                      <span class="font-semibold text-light-primary-text">2 Star</span>
                      <div
                        class="progress w-full flex-1 h-1.5 bg-[rgba(145,158,171,0.24)] rounded-[50px] overflow-hidden">
                        <div style="width: 70%" class="progress-bar h-full bg-primary rounded-[50px]"></div>
                      </div>
                      <span>42.12k</span>
                    </div>
                    <div class="flex gap-x-4 items-center">
                      <span class="font-semibold text-light-primary-text">1 Star</span>
                      <div
                        class="progress w-full flex-1 h-1.5 bg-[rgba(145,158,171,0.24)] rounded-[50px] overflow-hidden">
                        <div style="width: 70%" class="progress-bar h-full bg-primary rounded-[50px]"></div>
                      </div>
                      <span>12.58k</span>
                    </div>
                  </div>
                </div>
                <div class="col-span-12 flex items-center justify-center py-6">
                  <a href="#quick-view-comment-form" class="btn btn-primary outline btn-large rounded-[100px]">Write a
                    Review</a>
                </div>
              </div>
              <div id="quick-view-comment-form" class="comment-form-wrapper p-6 border-t border-gray-300 border-b">
                <div
                  class="comment-respond @md/quick-view-product-details:border @md/quick-view-product-details:border-gray-300 @md/quick-view-product-details:rounded-3xl @md/quick-view-product-details:p-6">
                  <h5 class="mb-6">Add Comment</h5>
                  <div class="flex items-center gap-x-3 mb-6">
                    <p class="text-light-primary-text font-medium">
                      Your review about this product:
                    </p>
                    <div class="rating-section flex items-center justify-center">
                      <div
                        class="bg-[url('../images/star-icon.png')] w-[90px] h-4.5 bg-repeat-x overflow-hidden bg-position-[0_0]">
                        <div style="width: 0%"
                          class="bg-[url('../images/star-icon.png')] h-4.5 bg-repeat-x bg-position-[0_-18px]"></div>
                      </div>
                    </div>
                  </div>
                  <form class="quick-view-comment-form flex flex-col gap-y-6" action="#">
                    <div class="input-group medium rounded-[20px] px-3.5 resize-none">
                      <textarea id="quick-view-post-comment" rows="4"
                        class="peer form-control placeholder-transparent focus:placeholder-transparent"
                        placeholder="Comment *"></textarea>
                      <label for="quick-view-post-comment"
                        class="absolute left-[14px] top-1/2 -translate-y-1/2 text-xs leading-[18px] transition-all peer-placeholder-shown:text-light-disabled-text peer-focus:text-light-disabled-text peer-placeholder-shown:text-[16px] peer-placeholder-shown:top-6 peer-focus:text-[12px] peer-focus:top-0 peer-[:not(:placeholder-shown)]:text-[12px] peer-[:not(:placeholder-shown)]:top-0 bg-white peer-focus:px-1 peer-[:not(:placeholder-shown)]:px-1">
                        Comment *
                      </label>
                    </div>
                    <div class="relative w-full">
                      <input type="text" id="quick-view-name"
                        class="peer form-control input-group medium rounded-[80px] px-3.5 placeholder-transparent focus:placeholder-transparent focus:outline-none"
                        placeholder="Name *" />

                      <label for="quick-view-name"
                        class="absolute left-[14px] top-1/2 -translate-y-1/2 text-xs leading-[18px] transition-all peer-placeholder-shown:text-light-disabled-text peer-focus:text-light-disabled-text peer-placeholder-shown:text-[16px] peer-placeholder-shown:top-1/2 peer-focus:text-[12px] peer-focus:top-0 peer-[:not(:placeholder-shown)]:text-[12px] peer-[:not(:placeholder-shown)]:top-0 bg-white peer-focus:px-1 peer-[:not(:placeholder-shown)]:px-1">
                        Name *
                      </label>
                    </div>
                    <div class="relative w-full">
                      <input type="email" id="quick-view-email"
                        class="peer form-control input-group medium rounded-[80px] px-3.5 placeholder-transparent focus:placeholder-transparent focus:outline-none"
                        placeholder="Email *" />

                      <label for="quick-view-email"
                        class="absolute left-[14px] top-1/2 -translate-y-1/2 text-xs leading-[18px] transition-all peer-placeholder-shown:text-light-disabled-text peer-focus:text-light-disabled-text peer-placeholder-shown:text-[16px] peer-placeholder-shown:top-1/2 peer-focus:text-[12px] peer-focus:top-0 peer-[:not(:placeholder-shown)]:text-[12px] peer-[:not(:placeholder-shown)]:top-0 bg-white peer-focus:px-1 peer-[:not(:placeholder-shown)]:px-1">
                        Email *
                      </label>
                    </div>

                    <div class="flex md:justify-end justify-center gap-x-4">
                      <button class="btn btn-default outline btn-large rounded-[100px] py-[11px] shadow-none">
                        Cancel
                      </button>
                      <button class="btn btn-primary btn-large rounded-[100px] py-[11px]">
                        Post Review
                      </button>
                    </div>
                  </form>
                </div>
              </div>
              <div class="comment-list-wrapper p-6">
                <div class="comment-list-title flex items-center justify-between pb-6 border-b border-gray-300 mb-6">
                  <h5>Customer Ratings & Review</h5>
                  <div class="relative min-w-[100px]">
                    <select id="quick-view-sorting" class="rounded-[100px]! quick-view-select filter-select label">
                      <option value="newest">Newest</option>
                      <option value="oldest">Oldest</option>
                      <option value="popular">Popular</option>
                      <option value="rating">Rating</option>
                      <option value="relevance">Relevance</option>
                      <option value="comment-count">Comment Count</option>
                    </select>
                    <label for="quick-view-sorting" class="nice-select-label">Sorting</label>
                  </div>
                </div>
                <ol class="comment-list">
                  <li class="comment">
                    <div class="comment-body">
                      <div class="comment-avatar-card flex items-center gap-x-4 mb-3">
                        <div class="comment-author-avatar size-12 rounded-full">
                          <img src="{{ asset('ecommerce-assets/images/blog/user-avatar-1.png') }}"
                            alt="Comment Author Avatar" class="rounded-full" />
                        </div>
                        <div class="comment-author-info flex-1">
                          <p class="comment-author font-semibold text-light-primary-text">
                            Robert Fox
                          </p>
                        </div>
                      </div>
                      <div class="flex items-center mb-3">
                        <div
                          class="rating-section flex items-center relative after:absolute after:h-[22px] after:w-px after:right-0 after:top-1/2 after:-translate-y-1/2 after:bg-gray-300 pr-3">
                          <div
                            class="bg-[url('../images/star-icon.png')] w-[90px] h-4.5 bg-repeat-x overflow-hidden bg-position-[0_0]">
                            <div style="width: 80%"
                              class="bg-[url('../images/star-icon.png')] h-4.5 bg-repeat-x bg-position-[0_-18px]"></div>
                          </div>
                          <span
                            class="text-sm leading-[22px] font-normal inline-flex ml-2 text-light-primary-text">4.5</span>
                        </div>
                        <div class="flex items-center gap-x-1 pl-3">
                          <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                              d="M11.5269 3.13379L13.9331 3.67969L13.7065 6.13965L15.3335 8L13.7065 9.86035L13.9331 12.3203L11.5269 12.8662L10.2661 14.9932L7.99951 14.0195L5.73291 15L4.47314 12.873L2.06689 12.3271L2.29346 9.86035L0.666504 8L2.29346 6.13379L2.06689 3.66699L4.47314 3.12695L5.73291 1L7.99951 1.97363L10.2661 1L11.5269 3.13379ZM6.72607 9.17285L5.18018 7.62012L4.19287 8.60645L6.72607 11.1465L11.6128 6.24707L10.6265 5.25977L6.72607 9.17285Z"
                              fill="#088178" />
                          </svg>
                          <p class="text-primary text-sm leading-[22px]">
                            Verified purchase
                          </p>
                        </div>
                      </div>
                      <div class="comment-content pl-0! pr-0! mb-3">
                        <p class="text-light-primary-text">
                          Very nice ! On the other hand, we denounce with
                          righteous indignation and dislike men who are so
                          beguiled and demoralized by the
                        </p>
                      </div>
                      <div
                        class="comment-actions flex @md/quick-view-product-details:items-center @md/quick-view-product-details:flex-row flex-col gap-y-3 @md/quick-view-product-details:gap-y-0">
                        <p class="text-sm leading-[22px] @md/quick-view-product-details:pr-3">
                          was this review helpful to you?
                        </p>
                        <a href="#"
                          class="comment-action-item text-sm leading-[22px] inline-flex items-center gap-x-1 @md/quick-view-product-details:pr-3 relative @md/quick-view-product-details:after:absolute @md/quick-view-product-details:after:h-5 @md/quick-view-product-details:after:w-px @md/quick-view-product-details:after:right-0 @md/quick-view-product-details:after:top-1/2 @md/quick-view-product-details:after:-translate-y-1/2 @md/quick-view-product-details:after:bg-gray-300">
                          <i class="hgi hgi-stroke hgi-thumbs-up text-lg leading-[18px] text-light-primary-text"></i>
                          Thank (234)
                        </a>
                        <a href="#"
                          class="comment-action-item text-sm leading-[22px] inline-flex items-center gap-x-1 @md/quick-view-product-details:pl-3">
                          <i class="hgi hgi-stroke hgi-thumbs-down text-lg leading-[18px] text-light-primary-text"></i>
                          Dislike (12)
                        </a>
                      </div>
                    </div>
                  </li>
                  <li class="comment">
                    <div class="comment-body">
                      <div class="comment-avatar-card flex items-center gap-x-4 mb-3">
                        <div class="comment-author-avatar size-12 rounded-full">
                          <img src="{{ asset('ecommerce-assets/images/blog/user-avatar-1.png') }}"
                            alt="Comment Author Avatar" class="rounded-full" />
                        </div>
                        <div class="comment-author-info flex-1">
                          <p class="comment-author font-semibold text-light-primary-text">
                            Jenny Wilson
                          </p>
                        </div>
                      </div>
                      <div class="flex items-center mb-3">
                        <div
                          class="rating-section flex items-center relative after:absolute after:h-[22px] after:w-px after:right-0 after:top-1/2 after:-translate-y-1/2 after:bg-gray-300 pr-3">
                          <div
                            class="bg-[url('../images/star-icon.png')] w-[90px] h-4.5 bg-repeat-x overflow-hidden bg-position-[0_0]">
                            <div style="width: 80%"
                              class="bg-[url('../images/star-icon.png')] h-4.5 bg-repeat-x bg-position-[0_-18px]"></div>
                          </div>
                          <span
                            class="text-sm leading-[22px] font-normal inline-flex ml-2 text-light-primary-text">4.5</span>
                        </div>
                        <div class="flex items-center gap-x-1 pl-3">
                          <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                              d="M11.5269 3.13379L13.9331 3.67969L13.7065 6.13965L15.3335 8L13.7065 9.86035L13.9331 12.3203L11.5269 12.8662L10.2661 14.9932L7.99951 14.0195L5.73291 15L4.47314 12.873L2.06689 12.3271L2.29346 9.86035L0.666504 8L2.29346 6.13379L2.06689 3.66699L4.47314 3.12695L5.73291 1L7.99951 1.97363L10.2661 1L11.5269 3.13379ZM6.72607 9.17285L5.18018 7.62012L4.19287 8.60645L6.72607 11.1465L11.6128 6.24707L10.6265 5.25977L6.72607 9.17285Z"
                              fill="#088178" />
                          </svg>
                          <p class="text-primary text-sm leading-[22px]">
                            Verified purchase
                          </p>
                        </div>
                      </div>
                      <div class="comment-content pl-0! pr-0! mb-3">
                        <p class="text-light-primary-text">
                          Very nice ! On the other hand, we denounce with
                          righteous indignation and dislike men who are so
                          beguiled and demoralized by the
                        </p>
                      </div>
                      <div
                        class="comment-actions flex @md/quick-view-product-details:items-center @md/quick-view-product-details:flex-row flex-col gap-y-3 @md/quick-view-product-details:gap-y-0">
                        <p class="text-sm leading-[22px] @md/quick-view-product-details:pr-3">
                          was this review helpful to you?
                        </p>
                        <a href="#"
                          class="comment-action-item text-sm leading-[22px] inline-flex items-center gap-x-1 @md/quick-view-product-details:pr-3 relative @md/quick-view-product-details:after:absolute @md/quick-view-product-details:after:h-5 @md/quick-view-product-details:after:w-px @md/quick-view-product-details:after:right-0 @md/quick-view-product-details:after:top-1/2 @md/quick-view-product-details:after:-translate-y-1/2 @md/quick-view-product-details:after:bg-gray-300">
                          <i class="hgi hgi-stroke hgi-thumbs-up text-lg leading-[18px] text-light-primary-text"></i>
                          Thank (234)
                        </a>
                        <a href="#"
                          class="comment-action-item text-sm leading-[22px] inline-flex items-center gap-x-1 @md/quick-view-product-details:pl-3">
                          <i class="hgi hgi-stroke hgi-thumbs-down text-lg leading-[18px] text-light-primary-text"></i>
                          Dislike (12)
                        </a>
                      </div>
                    </div>
                  </li>
                  <li class="comment">
                    <div class="comment-body">
                      <div class="comment-avatar-card flex items-center gap-x-4 mb-3">
                        <div class="comment-author-avatar size-12 rounded-full">
                          <img src="{{ asset('ecommerce-assets/images/blog/user-avatar-1.png') }}"
                            alt="Comment Author Avatar" class="rounded-full" />
                        </div>
                        <div class="comment-author-info flex-1">
                          <p class="comment-author font-semibold text-light-primary-text">
                            Brooklyn Simmons
                          </p>
                        </div>
                      </div>
                      <div class="flex items-center mb-3">
                        <div
                          class="rating-section flex items-center relative after:absolute after:h-[22px] after:w-px after:right-0 after:top-1/2 after:-translate-y-1/2 after:bg-gray-300 pr-3">
                          <div
                            class="bg-[url('../images/star-icon.png')] w-[90px] h-4.5 bg-repeat-x overflow-hidden bg-position-[0_0]">
                            <div style="width: 80%"
                              class="bg-[url('../images/star-icon.png')] h-4.5 bg-repeat-x bg-position-[0_-18px]"></div>
                          </div>
                          <span
                            class="text-sm leading-[22px] font-normal inline-flex ml-2 text-light-primary-text">4.5</span>
                        </div>
                        <div class="flex items-center gap-x-1 pl-3">
                          <svg width="16" height="16" viewBox="0 0 16 16" fill="none"
                            xmlns="http://www.w3.org/2000/svg">
                            <path
                              d="M11.5269 3.13379L13.9331 3.67969L13.7065 6.13965L15.3335 8L13.7065 9.86035L13.9331 12.3203L11.5269 12.8662L10.2661 14.9932L7.99951 14.0195L5.73291 15L4.47314 12.873L2.06689 12.3271L2.29346 9.86035L0.666504 8L2.29346 6.13379L2.06689 3.66699L4.47314 3.12695L5.73291 1L7.99951 1.97363L10.2661 1L11.5269 3.13379ZM6.72607 9.17285L5.18018 7.62012L4.19287 8.60645L6.72607 11.1465L11.6128 6.24707L10.6265 5.25977L6.72607 9.17285Z"
                              fill="#088178" />
                          </svg>
                          <p class="text-primary text-sm leading-[22px]">
                            Verified purchase
                          </p>
                        </div>
                      </div>
                      <div class="comment-content pl-0! pr-0! mb-3">
                        <p class="text-light-primary-text">
                          Very nice ! On the other hand, we denounce with
                          righteous indignation and dislike men who are so
                          beguiled and demoralized by the
                        </p>
                      </div>
                      <div
                        class="comment-actions flex @md/quick-view-product-details:items-center @md/quick-view-product-details:flex-row flex-col gap-y-3 @md/quick-view-product-details:gap-y-0">
                        <p class="text-sm leading-[22px] @md/quick-view-product-details:pr-3">
                          was this review helpful to you?
                        </p>
                        <a href="#"
                          class="comment-action-item text-sm leading-[22px] inline-flex items-center gap-x-1 @md/quick-view-product-details:pr-3 relative @md/quick-view-product-details:after:absolute @md/quick-view-product-details:after:h-5 @md/quick-view-product-details:after:w-px @md/quick-view-product-details:after:right-0 @md/quick-view-product-details:after:top-1/2 @md/quick-view-product-details:after:-translate-y-1/2 @md/quick-view-product-details:after:bg-gray-300">
                          <i class="hgi hgi-stroke hgi-thumbs-up text-lg leading-[18px] text-light-primary-text"></i>
                          Thank (234)
                        </a>
                        <a href="#"
                          class="comment-action-item text-sm leading-[22px] inline-flex items-center gap-x-1 @md/quick-view-product-details:pl-3">
                          <i class="hgi hgi-stroke hgi-thumbs-down text-lg leading-[18px] text-light-primary-text"></i>
                          Dislike (12)
                        </a>
                      </div>
                    </div>
                  </li>
                </ol>
                <div class="comment-pagination-wrapper mt-6">
                  <ul class="flex items-center justify-center gap-x-1.5 comment-pagination">
                    <li class="group comment-pagination-item">
                      <a href="#"
                        class="inline-flex items-center justify-center md:size-10 size-9 rounded-[50px] bg-white cursor-pointer border border-gray-300 group-hover:font-semibold group-hover:border-primary group-hover:bg-[rgba(0,171,85,0.08)] transition-colors duration-300 ease-in-out">
                        <span class="inline-flex items-center justify-center">
                          <i
                            class="hgi hgi-stroke hgi-arrow-left-01 text-[20px] group-hover:font-semibold leading-5 text-light-primary-text group-hover:text-primary"></i></span>
                      </a>
                    </li>
                    <li class="group comment-pagination-item">
                      <a href="#"
                        class="inline-flex items-center justify-center md:size-10 size-9 rounded-[50px] active">
                        1
                      </a>
                    </li>
                    <li class="group blog-pagination-item">
                      <a href="#"
                        class="inline-flex items-center justify-center md:size-10 size-9 rounded-[50px] text-base leading-6 text-light-primary-text group-hover:text-primary group-hover:font-semibold bg-white cursor-pointer border border-gray-300 group-hover:border-primary group-hover:bg-[rgba(0,171,85,0.08)] transition-colors duration-300 ease-in-out">
                        2
                      </a>
                    </li>
                    <li class="group comment-pagination-item">
                      <a href="#"
                        class="inline-flex items-center justify-center md:size-10 size-9 rounded-[50px] text-base leading-6 text-light-primary-text group-hover:text-primary group-hover:font-semibold bg-white cursor-pointer border border-gray-300 group-hover:border-primary group-hover:bg-[rgba(0,171,85,0.08)] transition-colors duration-300 ease-in-out">
                        3
                      </a>
                    </li>
                    <li class="group blog-pagination-item">
                      <a href="#"
                        class="inline-flex items-center justify-center md:size-10 size-9 rounded-[50px] text-base leading-6 text-light-primary-text group-hover:text-primary group-hover:font-semibold bg-white cursor-pointer border border-gray-300 group-hover:border-primary group-hover:bg-[rgba(0,171,85,0.08)] transition-colors duration-300 ease-in-out">
                        4
                      </a>
                    </li>
                    <li class="group comment-pagination-item">
                      <a href="#"
                        class="inline-flex items-center justify-center md:size-10 size-9 rounded-[50px] text-base leading-6 text-light-primary-text group-hover:text-primary group-hover:font-semibold bg-white cursor-pointer border border-gray-300 group-hover:border-primary group-hover:bg-[rgba(0,171,85,0.08)] transition-colors duration-300 ease-in-out">
                        5
                      </a>
                    </li>
                    <li class="comment-pagination-item">
                      <a href="#"
                        class="inline-flex items-center justify-center md:size-10 size-9 rounded-[50px] bg-white">
                        <span class="inline-flex items-center justify-center">
                          <i
                            class="hgi hgi-stroke hgi-more-horizontal text-[20px] leading-5 text-light-primary-text"></i></span>
                      </a>
                    </li>
                    <li class="group comment-pagination-item">
                      <a href="#"
                        class="inline-flex items-center justify-center md:size-10 size-9 rounded-[50px] group-hover:font-semibold bg-white cursor-pointer border border-gray-300 group-hover:border-primary group-hover:bg-[rgba(0,171,85,0.08)] transition-colors duration-300 ease-in-out">
                        <span class="inline-flex items-center justify-center">
                          <i
                            class="hgi hgi-stroke hgi-arrow-right-01 text-[20px] leading-5 group-hover:font-semibold text-light-primary-text group-hover:text-primary"></i></span>
                      </a>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Quick View Sidebar End -->

  <!-- Size Variation Modal Start -->
  <div data-state="close"
    class="size-variation-modal max-w-[950px] w-full overflow-y-auto h-full lg:h-auto fixed top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 bg-white rounded-2xl z-99 data-[state=close]:invisible data-[state=close]:opacity-0 data-[state=open]:visible data-[state=open]:opacity-100 transition-all duration-250">
    <div class="size-variation-modal-content">
      <h5 class="size-variation-modal-title py-[15px] px-6 border-b border-gray-300 relative">
        Size Guide
        <button data-close-sidebar=".size-variation-modal"
          class="absolute top-1/2 -translate-y-1/2 right-6 close-sidebar-btn">
          <i class="hgi hgi-stroke hgi-multiplication-sign text-xl"></i>
        </button>
      </h5>
      <div class="size-variation-modal-content-inner p-[34px]">
        <div class="height-range-slider-section mb-6 flex items-center gap-x-10">
          <div class="flex items-center gap-x-6">
            <span>Height:</span>
            <p
              class="py-2.5 px-3.5 border border-gray-300 rounded-[80px] font-semibold text-light-primary-text min-w-21">
              <span class="height-range-slider-value">150</span><span>cm</span>
            </p>
          </div>
          <div class="flex-1" id="height-range-slider"></div>
        </div>

        <div class="weight-range-slider-section mb-6 flex items-center gap-x-10">
          <div class="flex items-center gap-x-6">
            <span>Weight:</span>
            <p
              class="py-2.5 px-3.5 border border-gray-300 rounded-[80px] font-semibold text-light-primary-text min-w-21">
              <span class="weight-range-slider-value">150</span><span>kg</span>
            </p>
          </div>
          <div class="flex-1" id="weight-range-slider"></div>
        </div>

        <div class="suggestion-section mb-4">
          <h6 class="mb-2">Suggests For You:</h6>
          <div class="suggestion-items flex items-center gap-x-2">
            <div
              class="suggestion-item py-[7px] px-4 rounded-full border border-gray-300 flex items-center justify-center leading-[26px] font-semibold text-light-primary-text hover:bg-[rgba(145,158,171,0.08)]">
              S
            </div>
            <div
              class="suggestion-item py-[7px] px-[15px] rounded-full border border-gray-300 flex items-center justify-center leading-[26px] font-semibold text-light-primary-text hover:bg-[rgba(145,158,171,0.08)]">
              M
            </div>
            <div
              class="suggestion-item py-[7px] px-[15px] rounded-full border border-gray-300 flex items-center justify-center leading-[26px] font-semibold text-light-primary-text hover:bg-[rgba(145,158,171,0.08)]">
              L
            </div>
            <div
              class="suggestion-item py-[7px] px-[15px] rounded-full border border-gray-300 flex items-center justify-center leading-[26px] font-semibold text-light-primary-text hover:bg-[rgba(145,158,171,0.08)]">
              XL
            </div>
            <div
              class="suggestion-item py-[7px] px-[15px] rounded-full border border-gray-300 flex items-center justify-center leading-[26px] font-semibold text-light-primary-text hover:bg-[rgba(145,158,171,0.08)]">
              XXL
            </div>
            <div
              class="suggestion-item py-[7px] px-[15px] rounded-full border border-gray-300 flex items-center justify-center leading-[26px] font-semibold text-light-primary-text hover:bg-[rgba(145,158,171,0.08)]">
              XXXL
            </div>
          </div>
        </div>

        <div class="size-table-section p-6 border border-gray-300 rounded-2xl">
          <div class="size-table w-full overflow-x-auto">
            <table class="w-full">
              <thead>
                <tr>
                  <th class="text-left font-semibold p-4 bg-[#F4F6F8]">
                    Size
                  </th>
                  <th class="text-left font-semibold p-4 bg-[#F4F6F8]">
                    Bust
                  </th>
                  <th class="text-left font-semibold p-4 bg-[#F4F6F8]">
                    Waist
                  </th>
                  <th class="text-left font-semibold p-4 bg-[#F4F6F8]">
                    Low Hip
                  </th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-300">
                <tr>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    XS
                  </td>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    32
                  </td>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    24-25
                  </td>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    33-34
                  </td>
                </tr>
                <tr>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    S
                  </td>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    34
                  </td>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    26-27
                  </td>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    35-36
                  </td>
                </tr>
                <tr>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    M
                  </td>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    36
                  </td>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    28-29
                  </td>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    37-38
                  </td>
                </tr>
                <tr>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    L
                  </td>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    38
                  </td>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    30-31
                  </td>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    39-40
                  </td>
                </tr>
                <tr>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    XL
                  </td>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    40
                  </td>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    32-33
                  </td>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    41-42
                  </td>
                </tr>
                <tr>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    XXL
                  </td>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    42
                  </td>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    34-35
                  </td>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    43-44
                  </td>
                </tr>
                <tr>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    XXXL
                  </td>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    44
                  </td>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    36-37
                  </td>
                  <td class="text-left py-3 px-4 font-semibold text-light-primary-text">
                    45-46
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- Size Variation Modal End -->

  <!-- ========== HEADER Section Start ========== -->
  @include('web.partials.header')

  <style>
    /* ── Explore All Categories dropdown scrollbar ── */
    #dropdownMenu::-webkit-scrollbar {
      width: 4px;
    }

    #dropdownMenu::-webkit-scrollbar-track {
      background: transparent;
    }

    #dropdownMenu::-webkit-scrollbar-thumb {
      background: #d1d5db;
      border-radius: 99px;
    }

    #dropdownMenu::-webkit-scrollbar-thumb:hover {
      background: #06172e;
    }

    #dropdownMenu {
      scrollbar-width: thin;
      scrollbar-color: #d1d5db transparent;
    }

    /* Pincode Modal Stacking & Backdrop Blur Overlay */
    #pincode-modal-overlay {
      position: fixed !important;
      top: 0 !important;
      left: 0 !important;
      width: 100% !important;
      height: 100% !important;
      background-color: rgba(22, 28, 36, 0.7) !important;
      backdrop-filter: blur(8px) !important;
      -webkit-backdrop-filter: blur(8px) !important;
      z-index: 999999 !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out !important;
    }

    #pincode-modal-overlay.invisible {
      opacity: 0 !important;
      visibility: hidden !important;
      pointer-events: none !important;
    }

    #pincode-modal-overlay.visible {
      opacity: 1 !important;
      visibility: visible !important;
      pointer-events: auto !important;
    }

    /* Centered Modal Container */
    #pincode-modal {
      background: #ffffff !important;
      border-radius: 24px !important;
      box-shadow: 0 24px 48px -12px rgba(22, 28, 36, 0.3) !important;
      width: 100% !important;
      max-width: 480px !important;
      margin: 16px !important;
      overflow: hidden !important;
      transform: scale(0.9) !important;
      opacity: 0 !important;
      transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease-in-out !important;
    }

    #pincode-modal-overlay.visible #pincode-modal {
      transform: scale(1) !important;
      opacity: 1 !important;
    }

    /* Header Section */
    .pincode-header {
      background: #06172e !important;
      padding: 28px !important;
      position: relative !important;
      color: #ffffff !important;
    }

    .pincode-close-btn {
      position: absolute !important;
      top: 20px !important;
      right: 20px !important;
      width: 32px !important;
      height: 32px !important;
      border-radius: 50% !important;
      background: rgba(255, 255, 255, 0.2) !important;
      border: none !important;
      color: #ffffff !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      cursor: pointer !important;
      transition: background 0.2s ease !important;
    }

    .pincode-close-btn:hover {
      background: rgba(255, 255, 255, 0.3) !important;
    }

    /* Body & Input Fields */
    .pincode-body {
      padding: 28px !important;
    }

    .pincode-input-row {
      display: flex !important;
      gap: 12px !important;
      margin-bottom: 20px !important;
    }

    .pincode-input-container {
      position: relative !important;
      flex: 1 !important;
    }

    .pincode-input-icon {
      position: absolute !important;
      left: 16px !important;
      top: 50% !important;
      transform: translateY(-50%) !important;
      color: #919eab !important;
      font-size: 18px !important;
    }

    .pincode-input-field {
      width: 100% !important;
      padding: 12px 16px 12px 48px !important;
      border: 2px solid #e5e7eb !important;
      border-radius: 16px !important;
      font-size: 14px !important;
      font-weight: 600 !important;
      outline: none !important;
      transition: border-color 0.2s ease !important;
      letter-spacing: 0.15em !important;
    }

    .pincode-input-field:focus {
      border-color: #06172e !important;
    }

    .pincode-check-btn {
      background: #06172e !important;
      color: #ffffff !important;
      border: none !important;
      padding: 12px 24px !important;
      border-radius: 16px !important;
      font-size: 14px !important;
      font-weight: 700 !important;
      cursor: pointer !important;
      transition: background 0.2s ease !important;
    }

    .pincode-check-btn:hover {
      background: #008f47 !important;
    }

    /* Status & Result Cards */
    .pincode-result {
      padding: 16px !important;
      border-radius: 16px !important;
      margin-bottom: 20px !important;
    }

    .pincode-result-flex {
      display: flex !important;
      align-items: flex-start !important;
      gap: 16px !important;
    }

    .pincode-result-icon {
      width: 48px !important;
      height: 48px !important;
      border-radius: 12px !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      font-size: 24px !important;
      flex-shrink: 0 !important;
    }

    .pincode-result-text {
      flex: 1 !important;
    }

    .pincode-result-title {
      font-weight: 700 !important;
      font-size: 16px !important;
      margin: 0 0 4px 0 !important;
    }

    .pincode-result-desc {
      color: #637381 !important;
      font-size: 14px !important;
      margin: 0 !important;
    }

    .pincode-result-footer {
      margin-top: 16px !important;
      padding-top: 16px !important;
      border-top: 1px solid rgba(145, 158, 171, 0.2) !important;
      display: flex !important;
      align-items: center !important;
      justify-content: space-between !important;
    }

    .pincode-result-footer p {
      color: #637381 !important;
      font-size: 12px !important;
      margin: 0 !important;
    }

    .pincode-change-link {
      color: #06172e !important;
      font-weight: 600 !important;
      font-size: 12px !important;
      text-decoration: none !important;
      background: none !important;
      border: none !important;
      padding: 0 !important;
      cursor: pointer !important;
    }

    .pincode-change-link:hover {
      text-decoration: underline !important;
    }

    /* Quick picks */
    .quick-picks-container {
      margin-top: 20px !important;
    }

    .quick-picks-title {
      font-size: 12px !important;
      color: #637381 !important;
      font-weight: 600 !important;
      margin-bottom: 10px !important;
    }

    .quick-picks-flex {
      display: flex !important;
      flex-wrap: wrap !important;
      gap: 8px !important;
    }

    .quick-pick-btn {
      font-size: 12px !important;
      font-weight: 600 !important;
      background: #f4f6f8 !important;
      color: #212b36 !important;
      border: none !important;
      border-radius: 50px !important;
      padding: 6px 12px !important;
      cursor: pointer !important;
      transition: all 0.2s ease !important;
    }

    .quick-pick-btn:hover {
      background: #06172e !important;
      color: #ffffff !important;
    }

    /* Support classes for Javascript toggles */
    #pincode-result-card.hidden,
    #pincode-result-success.hidden,
    #pincode-result-error.hidden {
      display: none !important;
    }

    .bg-primary\/5 {
      background-color: rgba(0, 171, 85, 0.05) !important;
      border: 1px solid rgba(0, 171, 85, 0.15) !important;
    }

    .bg-error\/5 {
      background-color: rgba(255, 86, 48, 0.05) !important;
      border: 1px solid rgba(255, 86, 48, 0.15) !important;
    }

    .text-primary {
      color: #06172e !important;
    }

    .text-error {
      color: #ff5630 !important;
    }

    .bg-primary\/10 {
      background-color: rgba(0, 171, 85, 0.1) !important;
      color: #06172e !important;
    }

    .bg-error\/10 {
      background-color: rgba(255, 86, 48, 0.1) !important;
      color: #ff5630 !important;
    }
  </style>

  <!-- ========== PINCODE MODAL ========== -->
  <div id="pincode-modal-overlay" class="invisible">
    <div id="pincode-modal" class="scale-90 opacity-0">

      <!-- Modal Header -->
      <div class="pincode-header">
        <button id="pincode-modal-close" class="pincode-close-btn">
          <i class="hgi hgi-stroke hgi-multiplication-sign text-base"></i>
        </button>
        <div style="display: flex; align-items: center; gap: 12px;">
          <span
            style="display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px; border-radius: 12px; background: rgba(255, 255, 255, 0.2);">
            <i class="hgi hgi-stroke hgi-location-01 text-2xl text-white"></i>
          </span>
          <div>
            <h5 style="margin: 0; color: #fff; font-size: 18px; font-weight: 700; line-height: 1.2;">Check Delivery</h5>
            <p style="margin: 4px 0 0 0; color: rgba(255, 255, 255, 0.8); font-size: 13px; line-height: 1.2;">Enter your
              pincode to check availability</p>
          </div>
        </div>
      </div>

      <!-- Modal Body -->
      <div class="pincode-body">
        <!-- Input Row -->
        <div class="pincode-input-row">
          <div class="pincode-input-container">
            <span class="pincode-input-icon">
              <i class="hgi hgi-stroke hgi-search-02 text-lg"></i>
            </span>
            <input id="modal-pincode-input" type="text" maxlength="6" inputmode="numeric" pattern="[0-9]*"
              class="pincode-input-field" placeholder="e.g. 110001" />
          </div>
          <button id="modal-pincode-check-btn" class="pincode-check-btn">
            <span id="modal-btn-label" style="display: flex; align-items: center; gap: 6px;">
              <i class="hgi hgi-stroke hgi-checkmark-badge-01 text-base"></i>
              Check
            </span>
          </button>
        </div>

        <!-- Status Card (hidden by default) -->
        <div id="pincode-result-card" class="hidden pincode-result">
          <!-- SUCCESS STATE -->
          <div id="pincode-result-success" class="hidden">
            <div class="pincode-result-flex">
              <span class="pincode-result-icon">
                <i class="hgi hgi-stroke hgi-checkmark-circle-02 text-2xl"></i>
              </span>
              <div class="pincode-result-text">
                <p class="pincode-result-title">Delivery Available! ✓</p>
                <p id="pincode-result-location" class="pincode-result-desc"></p>
              </div>
            </div>
            <div class="pincode-result-footer">
              <p>Pincode saved for this session.</p>
              <button id="pincode-change-btn" class="pincode-change-link">Change</button>
            </div>
          </div>

          <!-- ERROR STATE -->
          <div id="pincode-result-error" class="hidden">
            <div class="pincode-result-flex">
              <span class="pincode-result-icon">
                <i class="hgi hgi-stroke hgi-cancel-circle text-2xl"></i>
              </span>
              <div class="pincode-result-text">
                <p class="pincode-result-title">Not Available</p>
                <p id="pincode-result-error-msg" class="pincode-result-desc"></p>
              </div>
            </div>
          </div>
        </div>

        <!-- Quick-pick popular pincodes -->
        <div class="quick-picks-container">
          <p class="quick-picks-title">Popular Areas:</p>
          <div class="quick-picks-flex" id="pincode-quick-picks">
            <button class="quick-pick-btn pincode-quick" data-pin="110001">Delhi - 110001</button>
            <button class="quick-pick-btn pincode-quick" data-pin="400001">Mumbai - 400001</button>
            <button class="quick-pick-btn pincode-quick" data-pin="560001">Bangalore - 560001</button>
          </div>
        </div>
      </div>
    </div>
  </div>
  <!-- ========== END PINCODE MODAL ========== -->

  <!-- ========== SHARE MODAL ========== -->
  <style>
    /* Share Modal Stacking & Backdrop Blur Overlay */
    #share-modal-overlay {
      position: fixed !important;
      top: 0 !important;
      left: 0 !important;
      width: 100% !important;
      height: 100% !important;
      background-color: rgba(22, 28, 36, 0.7) !important;
      backdrop-filter: blur(8px) !important;
      -webkit-backdrop-filter: blur(8px) !important;
      z-index: 999999 !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out !important;
    }

    #share-modal-overlay.invisible {
      opacity: 0 !important;
      visibility: hidden !important;
      pointer-events: none !important;
    }

    #share-modal-overlay.visible {
      opacity: 1 !important;
      visibility: visible !important;
      pointer-events: auto !important;
    }

    /* Centered Share Modal Container */
    #share-modal {
      background: #ffffff !important;
      border-radius: 24px !important;
      box-shadow: 0 24px 48px -12px rgba(22, 28, 36, 0.3) !important;
      width: 100% !important;
      max-width: 480px !important;
      margin: 16px !important;
      overflow: hidden !important;
      transform: scale(0.9) !important;
      opacity: 0 !important;
      transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), opacity 0.3s ease-in-out !important;
    }

    #share-modal-overlay.visible #share-modal {
      transform: scale(1) !important;
      opacity: 1 !important;
    }

    /* Header Section */
    .share-header {
      background: #06172e !important;
      padding: 24px !important;
      position: relative !important;
      color: #ffffff !important;
    }

    .share-close-btn {
      position: absolute !important;
      top: 18px !important;
      right: 18px !important;
      width: 32px !important;
      height: 32px !important;
      border-radius: 50% !important;
      background: rgba(255, 255, 255, 0.2) !important;
      border: none !important;
      color: #ffffff !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      cursor: pointer !important;
      transition: background 0.2s ease !important;
    }

    .share-close-btn:hover {
      background: rgba(255, 255, 255, 0.3) !important;
    }

    /* Body & Platforms List */
    .share-body {
      padding: 28px !important;
    }

    .share-platform-list {
      display: flex !important;
      flex-direction: row !important;
      justify-content: space-around !important;
      align-items: center !important;
      gap: 16px !important;
      margin-bottom: 24px !important;
    }

    .share-platform-item {
      display: flex !important;
      flex-direction: column !important;
      align-items: center !important;
      gap: 8px !important;
      text-decoration: none !important;
      color: #212b36 !important;
      font-size: 12px !important;
      font-weight: 600 !important;
      cursor: pointer !important;
      transition: transform 0.2s ease !important;
    }

    .share-platform-item:hover {
      transform: translateY(-4px) !important;
    }

    .share-platform-icon {
      width: 54px !important;
      height: 54px !important;
      border-radius: 50% !important;
      display: flex !important;
      align-items: center !important;
      justify-content: center !important;
      font-size: 24px !important;
      color: #ffffff !important;
      box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1) !important;
      transition: box-shadow 0.2s ease !important;
    }

    .share-platform-item:hover .share-platform-icon {
      box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15) !important;
    }

    /* Copy Link Input */
    .share-copy-container {
      display: flex !important;
      border: 2px solid #e5e7eb !important;
      border-radius: 16px !important;
      padding: 6px 6px 6px 16px !important;
      align-items: center !important;
      background: #f8f9fa !important;
    }

    .share-copy-input {
      flex: 1 !important;
      border: none !important;
      background: transparent !important;
      outline: none !important;
      font-size: 13px !important;
      color: #637381 !important;
      font-weight: 500 !important;
      text-overflow: ellipsis !important;
      white-space: nowrap !important;
      overflow: hidden !important;
    }

    .share-copy-btn {
      background: #06172e !important;
      color: #ffffff !important;
      border: none !important;
      padding: 8px 16px !important;
      border-radius: 12px !important;
      font-size: 13px !important;
      font-weight: 700 !important;
      cursor: pointer !important;
      transition: background 0.2s ease !important;
      white-space: nowrap !important;
    }

    .share-copy-btn:hover {
      background: #008f47 !important;
    }
  </style>

  <div id="share-modal-overlay" class="invisible">
    <div id="share-modal" class="scale-90 opacity-0">
      <!-- Modal Header -->
      <div class="share-header">
        <button id="share-modal-close" class="share-close-btn" onclick="closeShareModal()">
          <i class="hgi hgi-stroke hgi-multiplication-sign text-base"></i>
        </button>
        <div style="display: flex; align-items: center; gap: 12px;">
          <span
            style="display: inline-flex; align-items: center; justify-content: center; width: 44px; height: 44px; border-radius: 12px; background: rgba(255, 255, 255, 0.2);">
            <i class="hgi hgi-stroke hgi-share-01 text-2xl text-white"></i>
          </span>
          <div>
            <h5 id="share-modal-title"
              style="margin: 0; color: #fff; font-size: 18px; font-weight: 700; line-height: 1.2;">Share Seller</h5>
            <p style="margin: 4px 0 0 0; color: rgba(255, 255, 255, 0.8); font-size: 13px; line-height: 1.2;">Share this
              seller profile with others</p>
          </div>
        </div>
      </div>

      <!-- Modal Body -->
      <div class="share-body">
        <div class="share-platform-list">
          <!-- WhatsApp -->
          <a id="share-wa" href="#" target="_blank" rel="noopener" class="share-platform-item">
            <span class="share-platform-icon" style="background: #25d366;">
              <svg fill="#ffffff" viewBox="0 0 24 24" width="24" height="24">
                <path
                  d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 00-5.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z" />
              </svg>
            </span>
            WhatsApp
          </a>

          <!-- Facebook -->
          <a id="share-fb" href="#" target="_blank" rel="noopener" class="share-platform-item" onclick="handleFacebookShare(event)">
            <span class="share-platform-icon" style="background: #1877f2;">
              <svg fill="#ffffff" viewBox="0 0 24 24" width="24" height="24">
                <path
                  d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z" />
              </svg>
            </span>
            Facebook
          </a>

          <!-- X (Twitter) -->
          <a id="share-tw" href="#" target="_blank" rel="noopener" class="share-platform-item">
            <span class="share-platform-icon" style="background: #000000;">
              <svg fill="#ffffff" viewBox="0 0 24 24" width="24" height="24">
                <path
                  d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-4.714-6.231-5.401 6.231H2.748l7.73-8.835L1.254 2.25H8.08l4.253 5.622zm-1.161 17.52h1.833L7.084 4.126H5.117z" />
              </svg>
            </span>
            X (Twitter)
          </a>

          <!-- LinkedIn -->
          <a id="share-li" href="#" target="_blank" rel="noopener" class="share-platform-item">
            <span class="share-platform-icon" style="background: #0077b5;">
              <svg fill="#ffffff" viewBox="0 0 24 24" width="24" height="24">
                <path
                  d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z" />
              </svg>
            </span>
            LinkedIn
          </a>
        </div>

        <!-- Copy Link Container -->
        <div class="share-copy-container" style="margin-bottom: 12px !important;">
          <input id="share-copy-input" type="text" readonly class="share-copy-input" />
          <button id="share-copy-btn" class="share-copy-btn" onclick="copyShareLink()">
            Copy Link
          </button>
        </div>

        <!-- Copy Message Container -->
        <button id="share-msg-copy-btn" class="share-copy-btn"
          style="width: 100% !important; justify-content: center !important; display: flex !important; align-items: center !important; gap: 8px !important; padding: 12px !important; border-radius: 12px !important;"
          onclick="copyFullMessage()">
          <i class="hgi hgi-stroke hgi-copy-01 text-base"></i>
          Copy Full Message
        </button>
      </div>
    </div>
  </div>

  <script>
    let currentShareTitle = '';
    let currentShareLink = '';
    let currentFullMessage = '';

    function writeToClipboardFallback(text) {
      if (navigator.clipboard && window.isSecureContext) {
        return navigator.clipboard.writeText(text);
      } else {
        return new Promise((resolve, reject) => {
          try {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            textArea.style.position = "fixed";
            textArea.style.left = "-999999px";
            textArea.style.top = "-999999px";
            document.body.appendChild(textArea);
            textArea.focus();
            textArea.select();
            const successful = document.execCommand('copy');
            document.body.removeChild(textArea);
            if (successful) resolve();
            else reject(new Error('Copy failed'));
          } catch (err) {
            reject(err);
          }
        });
      }
    }

    function openShareModal(companyName, link, imvCode, customMsg) {
      currentShareTitle = companyName || 'Share Seller';
      currentShareLink = link || window.location.href;

      document.getElementById('share-modal-title').textContent = currentShareTitle;
      document.getElementById('share-copy-input').value = currentShareLink;

      // Construct the formatted message dynamically with real JS template literal newlines
      const msg = customMsg || `🏢 Explore products from ${companyName} on IndianMSME.\n✅ Verified MSME Seller\n🆔 Vendor Code: ${imvCode}\n📦 View all products: ${link}\nSupport trusted Indian MSME businesses!`;

      currentFullMessage = msg;

      // Convert localhost link to live amsmetrading.com domain URL for Facebook crawler
      let fbLink = link;
      try {
        const urlObj = new URL(link, window.location.origin);
        if (urlObj.hostname === 'localhost' || urlObj.hostname === '127.0.0.1') {
          let cleanPath = urlObj.pathname.replace(/^\/(Indian%20MSME\/public|Indian\sMSME\/public|var\/www\/html\/Indian%20MSME)/i, '');
          if (!cleanPath.startsWith('/')) cleanPath = '/' + cleanPath;
          fbLink = 'https://amsmetrading.com' + cleanPath + urlObj.search;
        }
      } catch(e) {
        fbLink = 'https://amsmetrading.com';
      }

      const shareMsg = encodeURIComponent(msg);
      const shareLink = encodeURIComponent(link);
      const fbShareLink = encodeURIComponent(fbLink);

      // Fix WhatsApp share URL to official api.whatsapp.com/send?text= endpoint
      document.getElementById('share-wa').href = `https://api.whatsapp.com/send?text=${shareMsg}`;
      document.getElementById('share-fb').href = `https://www.facebook.com/sharer/sharer.php?u=${fbShareLink}`;
      document.getElementById('share-tw').href = `https://twitter.com/intent/tweet?text=${shareMsg}`;
      document.getElementById('share-li').href = `https://www.linkedin.com/shareArticle?mini=true&url=${shareLink}&title=${encodeURIComponent(companyName)}`;

      const overlay = document.getElementById('share-modal-overlay');
      const modal = document.getElementById('share-modal');

      overlay.classList.remove('invisible');
      overlay.classList.add('visible');
      setTimeout(() => {
        modal.classList.remove('scale-90', 'opacity-0');
        modal.classList.add('scale-100', 'opacity-100');
      }, 50);
    }

    function closeShareModal() {
      const overlay = document.getElementById('share-modal-overlay');
      const modal = document.getElementById('share-modal');

      modal.classList.remove('scale-100', 'opacity-100');
      modal.classList.add('scale-90', 'opacity-0');
      setTimeout(() => {
        overlay.classList.remove('visible');
        overlay.classList.add('invisible');
      }, 300);
    }

    // Close when clicking overlay backdrop
    document.addEventListener('DOMContentLoaded', () => {
      const overlay = document.getElementById('share-modal-overlay');
      if (overlay) {
        overlay.addEventListener('click', (e) => {
          if (e.target === overlay) closeShareModal();
        });
      }
      document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') closeShareModal();
      });
    });

    function copyShareLink() {
      const input = document.getElementById('share-copy-input');
      input.select();
      input.setSelectionRange(0, 99999);
      navigator.clipboard.writeText(input.value).then(() => {
        const btn = document.getElementById('share-copy-btn');
        btn.textContent = 'Copied! ✓';
        btn.style.background = '#008f47';
        setTimeout(() => {
          btn.textContent = 'Copy Link';
          btn.style.background = '#06172e';
        }, 2000);
      });
    }

    function copyFullMessage() {
      navigator.clipboard.writeText(currentFullMessage).then(() => {
        const btn = document.getElementById('share-msg-copy-btn');
        const originalHtml = btn.innerHTML;
        btn.innerHTML = '<span>Message Copied! ✓</span>';
        btn.style.background = '#008f47';
        setTimeout(() => {
          btn.innerHTML = originalHtml;
          btn.style.background = '#06172e';
        }, 2000);
      });
    }
  </script>
  <!-- ========== END SHARE MODAL ========== -->

  @yield('content')
  @include('web.partials.footer')

  <!-- Footer Bottom Nav Start -->
  <div class="w-full z-80 bg-white border-t border-gray-300 block md:hidden fixed bottom-0 left-0 pb-3"
    style="background-color: #ffffff !important; border-top: 1px solid #e2e8f0 !important; z-index: 9999 !important;">
    <div class="container">
      <ul class="flex items-center justify-between footer-bottom-nav -mt-px">
        <li class="group">
          <a class='footer-bottom-nav-btn flex items-center flex-col gap-y-1 border-t-2 border-transparent text-sm leading-[22px] text-light-primary-text px-[9px] pt-2.5 pb-1 {{ request()->routeIs('home') ? 'active' : '' }}'
            href="{{ route('home') }}">
            <span class="inline-flex items-center justify-center">
              <i class="hgi hgi-stroke hgi-home-01 text-2xl leading-6 text-light-primary-text"></i>
            </span>Home
          </a>
        </li>
        <li class="group">
          <a class='footer-bottom-nav-btn flex items-center flex-col gap-y-1 border-t-2 border-transparent text-sm leading-[22px] text-light-primary-text px-[9px] pt-2.5 pb-1 {{ request()->routeIs('shop') || request()->routeIs('category.show') ? 'active' : '' }}'
            href='{{ route('shop') }}'>
            <span class="inline-flex items-center justify-center">
              <i class="hgi hgi-stroke hgi-shopping-bag-02 text-2xl leading-6 text-light-primary-text"></i>
            </span>Shop
          </a>
        </li>
        <li class="group">
          <a class='footer-bottom-nav-btn flex items-center flex-col gap-y-1 border-t-2 border-transparent text-sm leading-[22px] text-light-primary-text px-[9px] pt-2.5 pb-1 {{ request()->routeIs('vendors') ? 'active' : '' }}'
            href='{{ route('vendors') }}'>
            <span class="inline-flex items-center justify-center">
              <i class="hgi hgi-stroke hgi-store-01 text-2xl leading-6 text-light-primary-text"></i>
            </span>Sellers
          </a>
        </li>
        <li class="group">
          <a class='footer-bottom-nav-btn flex items-center flex-col gap-y-1 border-t-2 border-transparent text-sm leading-[22px] text-light-primary-text px-[9px] pt-2.5 pb-1 {{ request()->routeIs('login') || request()->routeIs('adc.*') ? 'active' : '' }}'
            href='{{ auth('adc')->check() ? route('adc.dashboard') : route('login', ['tab' => 'login']) }}'>
            <span class="inline-flex items-center justify-center">
              <i class="hgi hgi-stroke hgi-user-circle text-2xl leading-6 text-light-primary-text"></i>
            </span>My Account
          </a>
        </li>
      </ul>
    </div>
  </div>
  <!-- Footer Bottom Nav End -->

  <!-- ========== Plugins JS ========== -->
  <script src="{{ asset('ecommerce-assets/js/vendor/jquery-3.7.1.min.js') }}"></script>
  <script src="{{ asset('ecommerce-assets/js/vendor/slick.min.js') }}"></script>
  <script src="{{ asset('ecommerce-assets/js/vendor/jquery.countdown.min.js') }}"></script>
  <script src="{{ asset('ecommerce-assets/js/vendor/jquery.nice-select.min.js') }}"></script>
  <script src="{{ asset('ecommerce-assets/js/vendor/jquery.counterup-2.min.js') }}"></script>
  <script src="{{ asset('ecommerce-assets/js/vendor/jquery.magnific-popup.min.js') }}"></script>
  <script src="{{ asset('ecommerce-assets/js/vendor/nouislider.min.js') }}"></script>
  <script src="{{ asset('ecommerce-assets/js/vendor/wow.min.js') }}"></script>

  <!-- ========== Custom JS ========== -->
  <script src="{{ asset('ecommerce-assets/js/main.js') }}"></script>

  <!-- ========== Select2, Toastify & SweetAlert2 JS (Local) ========== -->
  <script src="{{ asset('vendor/select2/select2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('vendor/toastify/toastify.min.js') }}"></script>
  <script src="{{ asset('vendor/sweetalert2/sweetalert2.min.js') }}"></script>

  <script>
    // ═══════════════════════════════════════════════════════
    // PINCODE MODAL
    // ═══════════════════════════════════════════════════════
    (function () {
      const overlay = document.getElementById('pincode-modal-overlay');
      const modal = document.getElementById('pincode-modal');
      const triggerBtn = document.getElementById('pincode-trigger-btn');
      const closeBtn = document.getElementById('pincode-modal-close');
      const input = document.getElementById('modal-pincode-input');
      const checkBtn = document.getElementById('modal-pincode-check-btn');
      const btnLabel = document.getElementById('modal-btn-label');
      const resultCard = document.getElementById('pincode-result-card');
      const successDiv = document.getElementById('pincode-result-success');
      const errorDiv = document.getElementById('pincode-result-error');
      const locationTxt = document.getElementById('pincode-result-location');
      const errorTxt = document.getElementById('pincode-result-error-msg');
      const headerLabel = document.getElementById('pincode-header-label');
      const headerLabelMobile = document.getElementById('pincode-header-label-mobile');
      const changeBtn = document.getElementById('pincode-change-btn');
      const quickPicks = document.querySelectorAll('.pincode-quick');
      const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

      function updateHeaderLabels(val, mobileVal) {
        if (headerLabel) headerLabel.textContent = val;
        if (headerLabelMobile) headerLabelMobile.textContent = mobileVal || val;
      }

      // ── Restore from localStorage & Sync with Session (Loop Protected) ──────
      const savedLabel = localStorage.getItem('msme_pincode_label');
      const savedPin = localStorage.getItem('msme_pincode');
      const sessionPin = "{{ session('user_pincode') }}";

      if (savedLabel || savedPin) updateHeaderLabels(savedLabel || savedPin, savedPin || 'Deliver to');
      if (savedPin && input) input.value = savedPin;

      if (savedPin && savedPin !== sessionPin) {
        document.cookie = "user_pincode=" + savedPin + "; path=/; max-age=31536000; SameSite=Lax";
        const url = new URL(window.location.href);
        if (url.searchParams.has('pincode') && url.searchParams.get('pincode') !== savedPin) {
          url.searchParams.set('pincode', savedPin);
          window.location.href = url.pathname + url.search;
        } else if (!sessionStorage.getItem('pincode_sync_reloaded')) {
          sessionStorage.setItem('pincode_sync_reloaded', '1');
          window.location.reload();
        }
      } else if (!savedPin && sessionPin) {
        document.cookie = "user_pincode=; path=/; max-age=0; SameSite=Lax";
        const url = new URL(window.location.href);
        if (url.searchParams.has('pincode')) {
          url.searchParams.delete('pincode');
          window.location.href = url.pathname + url.search;
        } else if (!sessionStorage.getItem('pincode_sync_reloaded')) {
          sessionStorage.setItem('pincode_sync_reloaded', '1');
          window.location.reload();
        }
      } else {
        sessionStorage.removeItem('pincode_sync_reloaded');
      }

      // ── Open / Close ───────────────────────────────────────
      function openModal() {
        overlay.classList.remove('opacity-0', 'invisible');
        overlay.classList.add('opacity-100', 'visible');
        modal.classList.remove('scale-90', 'opacity-0');
        modal.classList.add('scale-100', 'opacity-100');
        setTimeout(() => input && input.focus(), 250);
      }
      function closeModal() {
        overlay.classList.remove('opacity-100', 'visible');
        overlay.classList.add('opacity-0', 'invisible');
        modal.classList.remove('scale-100', 'opacity-100');
        modal.classList.add('scale-90', 'opacity-0');
      }

      if (triggerBtn) triggerBtn.addEventListener('click', openModal);
      // Mobile pincode trigger
      const triggerBtnMobile = document.getElementById('pincode-trigger-btn-mobile');
      if (triggerBtnMobile) triggerBtnMobile.addEventListener('click', openModal);
      if (closeBtn) closeBtn.addEventListener('click', closeModal);
      overlay && overlay.addEventListener('click', e => { if (e.target === overlay) closeModal(); });
      document.addEventListener('keydown', e => { if (e.key === 'Escape') closeModal(); });

      // ── Loading state ──────────────────────────────────────
      function setLoading(loading) {
        if (!checkBtn || !btnLabel) return;
        checkBtn.disabled = loading;
        btnLabel.innerHTML = loading
          ? '<svg class="animate-spin size-4" viewBox="0 0 24 24" fill="none"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="white" stroke-width="4"/><path class="opacity-75" fill="white" d="M4 12a8 8 0 018-8v8z"/></svg> Checking…'
          : '<i class="hgi hgi-stroke hgi-checkmark-badge-01 text-base"></i> Check';
      }

      // ── Result states ──────────────────────────────────────
      function showSuccess(pin, district, state) {
        resultCard.classList.remove('hidden', 'bg-error/5');
        resultCard.classList.add('bg-primary/5');
        successDiv.classList.remove('hidden');
        errorDiv.classList.add('hidden');

        let loc = pin;
        if (district) loc += ' — ' + district;
        if (state) loc += ', ' + state;
        if (locationTxt) locationTxt.textContent = loc;

        const lbl = district ? district + ' — ' + pin : pin;
        updateHeaderLabels(lbl, pin);
        localStorage.setItem('msme_pincode', pin);
        localStorage.setItem('msme_pincode_label', lbl);
        document.cookie = "user_pincode=" + pin + "; path=/; max-age=31536000; SameSite=Lax";
      }

      function showError(msg) {
        resultCard.classList.remove('hidden', 'bg-primary/5');
        resultCard.classList.add('bg-error/5');
        successDiv.classList.add('hidden');
        errorDiv.classList.remove('hidden');
        if (errorTxt) errorTxt.textContent = msg || 'Sorry, delivery is not available at this pincode yet.';
        localStorage.removeItem('msme_pincode');
        localStorage.removeItem('msme_pincode_label');
        updateHeaderLabels('Select Pincode', 'Deliver to');
        document.cookie = "user_pincode=; path=/; max-age=0; SameSite=Lax";
      }

      function resetResult() {
        resultCard.classList.add('hidden');
        successDiv.classList.add('hidden');
        errorDiv.classList.add('hidden');
      }

      // ── Check Pincode ──────────────────────────────────────
      function checkPincode() {
        const pin = input ? input.value.trim() : '';
        if (!/^\d{6}$/.test(pin)) {
          showError('Please enter a valid 6-digit pincode.');
          return;
        }
        setLoading(true);
        resetResult();

        fetch('/api/check-pincode?pincode=' + pin, {
          headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }
        })
          .then(r => r.json())
          .then(data => {
            setLoading(false);
            if (data.available) {
              showSuccess(pin, data.district, data.state);
              setTimeout(() => {
                window.location.href = "{{ route('shop') }}?pincode=" + pin;
              }, 1000);
            } else {
              showError(data.message);
              if (sessionPin) {
                setTimeout(() => {
                  const url = new URL(window.location.href);
                  if (url.searchParams.has('pincode')) {
                    url.searchParams.delete('pincode');
                    window.location.href = url.pathname + url.search;
                  } else {
                    window.location.reload();
                  }
                }, 1000);
              }
            }
          })
          .catch(() => {
            setLoading(false);
            showError('Network error. Please try again.');
          });
      }

      if (checkBtn) checkBtn.addEventListener('click', checkPincode);
      if (input) input.addEventListener('keydown', e => { if (e.key === 'Enter') checkPincode(); });
      if (changeBtn) changeBtn.addEventListener('click', () => {
        resetResult();
        if (input) { input.value = ''; input.focus(); }
      });

      // ── Quick-pick chips ───────────────────────────────────
      quickPicks.forEach(btn => {
        btn.addEventListener('click', () => {
          if (input) input.value = btn.dataset.pin;
          checkPincode();
        });
      });
    })();
  </script>
  @stack('scripts')

  <script>
    jQuery(document).ready(function ($) {
      const baseUrl = "{{ url('/') }}";

      // ── Global Newsletter Subscription ──
      $(document).on('click', 'button:contains("Subscribe")', function (e) {
        e.preventDefault();
        const $btn = $(this);
        let $emailInput = $btn.closest('form').find('input[name="email"], input[type="email"], input[id="email"], input[id="subscribe_email"]');
        if ($emailInput.length === 0) {
          $emailInput = $btn.closest('.input-group').find('input[name="email"], input[type="email"], input[id="email"], input[id="subscribe_email"]');
        }
        if ($emailInput.length === 0) {
          $emailInput = $btn.closest('div').parent().find('input[name="email"], input[type="email"], input[id="email"], input[id="subscribe_email"]');
        }
        const email = $emailInput.val() ? $emailInput.val().trim() : '';

        if (!email) {
          Toastify({
            text: "Please enter your email address.",
            duration: 3000,
            gravity: "top",
            position: "right",
            style: { background: "#EF4444" }
          }).showToast();
          return;
        }

        $btn.prop('disabled', true).text('Subscribing...');

        $.ajax({
          url: "{{ route('newsletter.subscribe') }}",
          type: "POST",
          data: {
            _token: "{{ csrf_token() }}",
            email: email
          },
          success: function (res) {
            $btn.prop('disabled', false).text('Subscribe');
            if (res.success) {
              $emailInput.val('');
              Toastify({
                text: res.message || "Thank you for subscribing!",
                duration: 3000,
                gravity: "top",
                position: "right",
                style: { background: "#10B981" }
              }).showToast();
            } else {
              Toastify({
                text: res.message || "Something went wrong.",
                duration: 3000,
                gravity: "top",
                position: "right",
                style: { background: "#EF4444" }
              }).showToast();
            }
          },
          error: function (xhr) {
            $btn.prop('disabled', false).text('Subscribe');
            let msg = "Failed to subscribe. Please try again.";
            if (xhr.responseJSON && xhr.responseJSON.message) {
              msg = xhr.responseJSON.message;
            }
            Toastify({
              text: msg,
              duration: 3000,
              gravity: "top",
              position: "right",
              style: { background: "#EF4444" }
            }).showToast();
          }
        });
      });

      // Destroy NiceSelect on our selects
      $('select.select-searchable-adc, select.select-simple-adc').each(function () {
        $(this).next('.nice-select').remove();
        $(this).css('display', 'block');
      });



      // ── Header Autocomplete Search Logic (Body-level portal to avoid z-index issues) ──
      let searchDebounceTimer = null;
      let activeSearchInput = null;

      // Create a single shared dropdown portal at body level (outside any stacking context)
      const $searchPortal = $(`
        <div id="search-autocomplete-portal" style="
          display: none;
          position: fixed;
          background: white;
          border: 1px solid #e5e7eb;
          border-radius: 16px;
          box-shadow: 0 20px 60px rgba(0,0,0,0.15);
          z-index: 999999;
          max-height: 380px;
          overflow-y: auto;
          padding: 8px 0;
        ">
          <ul class="search-suggestions-list" style="list-style:none;margin:0;padding:0;"></ul>
          <div class="search-no-results" style="display:none;padding:12px 16px;text-align:center;color:#9ca3af;font-size:14px;">No results found</div>
        </div>
      `);
      $('body').append($searchPortal);

      function positionPortal(inputEl) {
        const rect = inputEl.getBoundingClientRect();
        $searchPortal.css({
          top: (rect.bottom + 8) + 'px',
          left: rect.left + 'px',
          width: rect.width + 'px',
        });
      }

      function closePortal() {
        $searchPortal.fadeOut(150);
        activeSearchInput = null;
      }

      function openPortal(inputEl) {
        activeSearchInput = inputEl;
        positionPortal(inputEl);
        $searchPortal.fadeIn(150);
      }

      $(document).on('input', '.header-search-input', function () {
        const input = this;
        const query = $(input).val().trim();

        if (query.length < 2) {
          closePortal();
          return;
        }

        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(function () {
          fetch(`${baseUrl}/api/search-autocomplete?query=${encodeURIComponent(query)}`)
            .then(res => res.json())
            .then(data => {
              const $list = $searchPortal.find('.search-suggestions-list').empty();
              const $noResults = $searchPortal.find('.search-no-results').hide();

              const products = data.products || [];

              if (products.length === 0) {
                $noResults.show();
                openPortal(input);
                return;
              }

              products.forEach(p => {
                $list.append(`
                  <li>
                    <a href="${p.url}" style="display:flex;align-items:center;gap:12px;padding:10px 16px;text-decoration:none;color:#374151;transition:background 0.15s;" 
                       onmouseover="this.style.background='#f9fafb'" 
                       onmouseout="this.style.background=''">
                      <i class="hgi hgi-stroke hgi-search-01" style="color:#9ca3af;font-size:14px;flex-shrink:0;"></i>
                      <span style="font-size:14px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">${p.name}</span>
                    </a>
                  </li>
                `);
              });

              // "Search all results" row
              const searchAllUrl = `${baseUrl}/shop?search=${encodeURIComponent(query)}`;
              $list.append(`
                <li style="border-top:1px solid #f3f4f6;">
                  <a href="${searchAllUrl}" style="display:flex;align-items:center;gap:12px;padding:10px 16px;text-decoration:none;color:var(--color-primary, #06172e);font-weight:600;border-radius:0 0 16px 16px;transition:background 0.15s;"
                     onmouseover="this.style.background='var(--color-primary,#06172e)';this.style.color='white';"
                     onmouseout="this.style.background='';this.style.color='var(--color-primary,#06172e)';">
                    <i class="hgi hgi-stroke hgi-search-01" style="font-size:14px;flex-shrink:0;"></i>
                    <span style="font-size:14px;">Search all results for "<strong>${query}</strong>"</span>
                  </a>
                </li>
              `);

              openPortal(input);
            })
            .catch(err => console.error('Search autocomplete error:', err));
        }, 250);
      });

      // Reposition on scroll/resize
      $(window).on('scroll resize', function () {
        if (activeSearchInput && $searchPortal.is(':visible')) {
          positionPortal(activeSearchInput);
        }
      });

      // Enter key → full search
      $(document).on('keydown', '.header-search-input', function (e) {
        if (e.key === 'Enter') {
          e.preventDefault();
          const q = $(this).val().trim();
          if (q.length > 0) window.location.href = `${baseUrl}/shop?search=${encodeURIComponent(q)}`;
        }
        if (e.key === 'Escape') closePortal();
        // Arrow key navigation
        if (e.key === 'ArrowDown' || e.key === 'ArrowUp') {
          e.preventDefault();
          const items = $searchPortal.find('li a');
          const focused = $searchPortal.find('li a:focus');
          if (!items.length) return;
          if (!focused.length) {
            e.key === 'ArrowDown' ? items.first().focus() : items.last().focus();
          } else {
            const idx = items.index(focused);
            if (e.key === 'ArrowDown') {
              items.eq(idx + 1).length ? items.eq(idx + 1).focus() : items.first().focus();
            } else {
              idx > 0 ? items.eq(idx - 1).focus() : $(this).focus();
            }
          }
        }
      });

      // Close on outside click
      $(document).on('click', function (e) {
        if (!$(e.target).closest('.search-input-container').length && !$(e.target).closest('#search-autocomplete-portal').length) {
          closePortal();
        }
      });

      // Display Server-side Validation Errors
      @if(isset($errors) && $errors->any())
        @foreach($errors->all() as $error)
          Toastify({
            text: "{{ $error }}",
            duration: 5000,
            close: true,
            gravity: "top",
            position: "right",
            style: { background: "#ef4444" }
          }).showToast();
        @endforeach
      @endif

      // Global session success/error messages and other validation errors
      @if(session('success'))
        Toastify({
          text: "{{ session('success') }}",
          duration: 5000,
          close: true,
          gravity: "top",
          position: "right",
          style: { background: "#10b981" }
        }).showToast();
      @endif

      @if(session('error'))
        Toastify({
          text: "{{ session('error') }}",
          duration: 5000,
          close: true,
          gravity: "top",
          position: "right",
          style: { background: "#ef4444" }
        }).showToast();
      @endif

      // Global form submit loader handler for web forms
      document.addEventListener('submit', function (e) {
        var form = e.target;
        if (!form || form.tagName !== 'FORM') return;

        setTimeout(function () {
          if (e.defaultPrevented) return;
          var btn = form.querySelector('button[type="submit"]');
          if (btn && !btn.disabled) {
            btn.disabled = true;
            btn.style.opacity = '0.75';
            btn.style.pointerEvents = 'none';
            if (!btn.dataset.origHtml) btn.dataset.origHtml = btn.innerHTML;
            btn.innerHTML = '<svg class="animate-spin size-5 text-current inline-block me-2" viewBox="0 0 24 24" fill="none" style="width:18px;height:18px;display:inline-block;"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8z"/></svg> <span>Please wait...</span>';
          }
        }, 15);
      });
    });
  </script>
</body>

</html>