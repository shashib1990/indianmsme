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

  <!-- ========== Select2, Toastify & SweetAlert2 CSS (Local) ========== -->
  <link href="{{ asset('plugins/select2/select2.min.css') }}" rel="stylesheet" />
  <link rel="stylesheet" type="text/css" href="{{ asset('plugins/toastify/toastify.min.css') }}">
  <link rel="stylesheet" type="text/css" href="{{ asset('plugins/sweetalert2/sweetalert2.min.css') }}">

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

  <!-- Cart Sidebar & Quick View Modals Removed (Unused template mockups with missing assets) -->

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

  <!-- ========== Impersonation Banner ========== -->
  @if(session('impersonating'))
    <div class="bg-amber-400 text-slate-900 text-xs py-2 px-4 text-center font-medium flex items-center justify-center gap-x-3 z-50 relative border-b border-amber-500 shadow-sm">
      <span>You are currently shopping as ADC: <strong>{{ session('impersonating.user_name') }}</strong></span>
      <form action="{{ url('impersonate/stop') }}" method="POST" class="inline m-0 p-0">
        @csrf
        <button type="submit" class="bg-slate-900 hover:bg-black text-white text-xs font-semibold px-3 py-1 rounded cursor-pointer transition-colors border-0">
          Return to Admin
        </button>
      </form>
    </div>
  @endif

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
  <script src="{{ asset('plugins/select2/select2.min.js') }}"></script>
  <script type="text/javascript" src="{{ asset('plugins/toastify/toastify.min.js') }}"></script>
  <script src="{{ asset('plugins/sweetalert2/sweetalert2.min.js') }}"></script>

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