@extends('web.layouts.app')

@section('title', 'Indian MSME Store — Shop')

@push('styles')
<style>
  /* Price range box + embedded dropdown */
  .price-box {
    background: #fff;
    border: 1px solid #d1d5db;
    border-radius: 0px !important;
    padding: 0;
    display: flex;
    align-items: center;
    min-width: 0;
    height: 28px;
    transition: border-color 0.2s, box-shadow 0.2s;
    position: relative;
    width: 100%;
  }
  .price-box:focus-within {
    border-color: #06172e;
    box-shadow: 0 0 0 2px rgba(0, 171, 85, 0.1);
  }
  .price-box-label {
    font-size: 10px !important;
    font-weight: 500;
    color: #878787;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    line-height: 1.2;
    margin-bottom: 4px;
    display: block;
  }
  .price-box-select {
    font-size: 14px !important;
    font-weight: 500;
    color: #212529 !important;
    background: transparent;
    border: none;
    outline: none;
    width: 100%;
    height: 100%;
    padding: 0 4px;
    cursor: pointer;
    /* Keep native arrow - don't use appearance:none so it stays functional */
    -webkit-appearance: auto;
    appearance: auto;
  }
  .price-range-separator {
    font-size: 14px;
    color: #878787;
    font-weight: 400;
    flex-shrink: 0;
    margin-top: 22px;
  }

  /* Ensure price range widget sits on top of subsequent widgets */
  .widget-price-range {
    position: relative;
    z-index: 10;
  }

  /* Style the NiceSelect wrapper when inside a price-box */
  .price-box .nice-select {
    border: none !important;
    background: transparent !important;
    height: 100% !important;
    width: 100% !important;
    padding: 0 8px !important;
    padding-right: 20px !important;
    display: flex !important;
    align-items: center !important;
    border-radius: 0 !important;
    line-height: 26px !important;
  }
  .price-box .nice-select span.current {
    font-size: 14px !important;
    font-weight: 500;
    color: #212529 !important;
  }
  .price-box .nice-select:before {
    right: 8px !important;
    font-size: 10px !important;
  }

  /* Style the NiceSelect dropdown list specifically for price filter */
  .price-box .nice-select .list {
    min-width: 100% !important;
    width: 100% !important;
    box-sizing: border-box !important;
    background-color: #ffffff !important;
    z-index: 10000 !important;
    border: 1px solid #d1d5db !important;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1) !important;
    border-radius: 4px !important;
    margin-top: 4px !important;
    padding: 4px 0 !important;
  }
  .price-box .nice-select .list li {
    font-size: 14px !important;
    padding-left: 8px !important;
    padding-right: 8px !important;
    line-height: 28px !important;
    height: 28px !important;
  }

  /* Compact Flipkart-style grid columns */
  @media (min-width: 1200px) and (max-width: 1599px) {
    .shop-custom-grid { grid-template-columns: repeat(4, minmax(0, 1fr)) !important; }
  }
  @media (min-width: 1600px) {
    .shop-custom-grid { grid-template-columns: repeat(5, minmax(0, 1fr)) !important; }
  }

  /* Filter sidebar font sizes */
  .sidebar h5 { font-size: 15px !important; font-weight: 700; }
  .sidebar h6 { font-size: 12.5px !important; font-weight: 700; text-transform: uppercase; color: #212121; letter-spacing: 0.5px; }
  .sidebar-title { padding: 10px 16px !important; }
  .sidebar label { font-size: 13px !important; }
  .sidebar span { font-size: 13px !important; }
  .widget-category-title a, #price-range-reset, .widget-discount-title a, .widget-brand-title a { font-size: 12px !important; font-weight: 600; color: #06172e; }
  .sidebar input[type="radio"], .sidebar input[type="checkbox"] { width: 15px !important; height: 15px !important; }
  .sidebar .w-9.h-9 { width: 24px !important; height: 24px !important; }

  /* Compact Flipkart-style product cards on Shop page */
  .shop-custom-grid .universal-product-card {
    height: 100% !important;
    min-height: auto !important;
    max-width: 260px !important;
    padding: 12px !important;
    border-radius: 12px !important;
    gap: 0 !important;
  }
  .shop-custom-grid .universal-image-container {
    height: 180px !important;
    border-radius: 8px !important;
  }
  .shop-custom-grid .universal-product-title {
    font-size: 13px !important;
    height: 36px !important;
    margin-top: 8px !important;
  }
  .shop-custom-grid .universal-meta-info {
    font-size: 10.5px !important;
  }
  .shop-custom-grid .universal-price-section {
    padding-top: 8px !important;
    margin-top: 8px !important;
  }
  .shop-custom-grid .universal-price-val {
    font-size: 14.5px !important;
  }
  .shop-custom-grid .universal-old-price {
    font-size: 11px !important;
  }
  .shop-custom-grid .universal-button-section {
    margin-top: auto !important;
    padding-top: 10px !important;
  }
  .shop-custom-grid .universal-add-to-cart-btn {
    padding: 8px 16px !important;
    border-radius: 8px !important;
    font-size: 12px !important;
  }

  /* Progress loader bar for AJAX feedback */
  #shop-loader-bar-inner {
    width: 0%;
  }
  #shop-loader-bar.loading #shop-loader-bar-inner {
    width: 100% !important;
    animation: loader-running 1.5s infinite linear;
  }
  @keyframes loader-running {
    0% { left: -30%; width: 30%; }
    50% { left: 30%; width: 40%; }
    100% { left: 100%; width: 30%; }
  }

  /* Dot loading spinner */
  .shop-loading-spinner {
    display: flex;
    gap: 6px;
    align-items: center;
    justify-content: center;
  }
  .shop-spinner-dot {
    width: 8px;
    height: 8px;
    background-color: #06172e;
    border-radius: 50%;
    animation: shop-bounce 0.6s infinite alternate;
  }
  .shop-spinner-dot:nth-child(2) { animation-delay: 0.2s; }
  .shop-spinner-dot:nth-child(3) { animation-delay: 0.4s; }
  @keyframes shop-bounce {
    to { transform: translateY(-6px); opacity: 0.3; }
  }
</style>
@endpush

@section('content')

    <!-- ========== Breadcrumb Section Start ========== -->
    <div class="container py-12">
      <div class="breadcrumb">
        <ul>
          <li>
            <a href='index.html'>
              <span class="inline-flex items-center justify-center">
                <i
                  class="hgi hgi-stroke hgi-home-01 text-2xl leading-6"
                ></i></span
              >Home</a
            >
          </li>
          <li class="text-light-disabled-text">&#8226;</li>
          <li><span class="text-sm leading-[22px]">Categories</span></li>
        </ul>
      </div>
    </div>
    <!-- ========== Breadcrumb Section End ========== -->

    <!-- ========== Filter with 3-column Section Start ========== -->
    <section class="pb-[90px]">
      <div class="container">
        <!-- ===== FILTER FORM START ===== -->
        <form id="shop-filter-form" method="GET" action="{{ route('shop') }}">
        <input type="hidden" name="sort" id="sort_input" value="{{ $sort }}">
        @if(request('seller'))<input type="hidden" name="seller" value="{{ request('seller') }}">@endif

        <div class="grid grid-cols-12 lg:gap-x-6 gap-y-6">
          <div
            class="xl:col-span-3 lg:col-span-3 col-span-12 row-start-2 lg:row-start-auto"
          >
            <div class="sidebar sticky top-20">
              <div class="border border-gray-300 rounded-2xl bg-white">
                <!-- title -->
                <div
                  class="px-6 py-4 bg-gray-200 rounded-t-2xl sidebar-title wow animate__animated animate__fadeInUp"
                  data-wow-delay=".2s"
                >
                  <div class="flex items-center justify-between">
                    <h5 class="text-light-primary-text">Filters</h5>
                    <a
                      href="#"
                      class="text-primary text-base leading-[26px] font-semibold hover:underline"
                      >Clear All</a
                    >
                  </div>
                </div>

                <!-- category -->
                <div class="px-6 py-6">
                  <!-- Category-content -->
                  <div
                    class="widget-category wow animate__animated animate__fadeInUp"
                    data-wow-delay=".2s"
                  >
                    <div
                      class="flex flex-col gap-y-4 pb-8 border-b border-gray-300"
                    >
                      <div
                        class="flex items-center justify-between widget-category-title"
                      >
                        <h6>Category</h6>
                        <a
                          href="#"
                          class="category-reset-btn text-base leading-[26px] hover:underline hover:text-primary transition-colors duration-300 ease-in-out"
                          >Reset</a
                        >
                      </div>
                      <div class="widget-category-content-list">
                        <div>
                          <ul class="flex flex-col gap-y-2">
                            @foreach(\App\Models\Category::active()->get() as $cat)
                            <li class="widget-category-content-list-items">
                              <label
                                class="group flex items-center justify-between w-full cursor-pointer"
                              >
                                <span class="flex items-center gap-x-2">
                                  <span
                                    class="group-has-[input:checked]:group-hover:bg-[#06172e]/8 flex items-center justify-center w-9 h-9 bg-transparent rounded-full group-hover:bg-[#919EAB]/8 transition-colors duration-300 ease-in-out"
                                  >
                                    <span
                                      class="relative inline-flex w-5 h-5 items-center justify-center"
                                    >
                                      <input
                                        type="radio"
                                        name="category"
                                        value="{{ $cat->slug }}"
                                        {{ request('category') == $cat->slug ? 'checked' : '' }}
                                        onchange="submitShopFilterForm()"
                                        class="peer appearance-none w-full h-full border-2 focus:outline-none checked:border-none border-gray-300 rounded-sm bg-white checked:bg-primary transition-all duration-300 ease-in-out"
                                      />
                                      <span
                                        class="absolute inset-0 inline-flex items-center justify-center text-white opacity-0 peer-checked:opacity-100 transition-all"
                                      >
                                        <i
                                          class="hgi hgi-stroke hgi-tick-02 text-[18px] leading-[18px]"
                                        ></i>
                                      </span>
                                    </span>
                                  </span>
                                  <span
                                    class="text-light-primary-text group-hover:text-primary transition-colors duration-300 ease-in-out"
                                  >
                                    {{ $cat->name }}
                                  </span>
                                </span>
                                  <span
                                    class="group-hover:text-primary transition-colors duration-300 ease-in-out"
                                  >
                                    ({{ $categoryCounts[$cat->id] ?? 0 }})
                                  </span>
                              </label>
                            </li>
                            @endforeach
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                @if(request('category'))
                @php
                  $activeCategory = \App\Models\Category::where('slug', request('category'))->first() ?? \App\Models\Category::find(request('category'));
                @endphp
                @if($activeCategory && $activeCategory->subCategories()->active()->exists())
                <!-- sub-category -->
                <div class="px-6 py-6 border-t border-gray-300">
                  <div class="widget-category wow animate__animated animate__fadeInUp" data-wow-delay=".2s">
                    <div class="flex flex-col gap-y-4 pb-8 border-b border-gray-300">
                      <div class="flex items-center justify-between widget-category-title">
                        <h6>Sub-Category</h6>
                        <a href="#" class="subcategory-reset-btn text-base leading-[26px] hover:underline hover:text-primary transition-colors duration-300 ease-in-out">Reset</a>
                      </div>
                      <div class="widget-category-content-list">
                        <div>
                          <ul class="flex flex-col gap-y-2">
                            @foreach($activeCategory->subCategories()->active()->get() as $subCat)
                            <li class="widget-category-content-list-items">
                              <label class="group flex items-center justify-between w-full cursor-pointer">
                                <span class="flex items-center gap-x-2">
                                  <span class="group-has-[input:checked]:group-hover:bg-[#06172e]/8 flex items-center justify-center w-9 h-9 bg-transparent rounded-full group-hover:bg-[#919EAB]/8 transition-colors duration-300 ease-in-out">
                                    <span class="relative inline-flex w-5 h-5 items-center justify-center">
                                      <input type="radio" name="sub_category" value="{{ $subCat->id }}" {{ request('sub_category') == $subCat->id ? 'checked' : '' }} onchange="submitShopFilterForm()" class="peer appearance-none w-full h-full border-2 focus:outline-none checked:border-none border-gray-300 rounded-sm bg-white checked:bg-primary transition-all duration-300 ease-in-out" />
                                      <span class="absolute inset-0 inline-flex items-center justify-center text-white opacity-0 peer-checked:opacity-100 transition-all">
                                        <i class="hgi hgi-stroke hgi-tick-02 text-[18px] leading-[18px]"></i>
                                      </span>
                                    </span>
                                  </span>
                                  <span class="text-light-primary-text group-hover:text-primary transition-colors duration-300 ease-in-out">{{ $subCat->name }}</span>
                                </span>
                                <span class="group-hover:text-primary transition-colors duration-300 ease-in-out">
                                  ({{ $subCategoryCounts[$subCat->id] ?? 0 }})
                                </span>
                              </label>
                            </li>
                            @endforeach
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                @endif
                @endif

                @if(request('sub_category'))
                @php
                  $activeSubCategory = \App\Models\SubCategory::find(request('sub_category'));
                @endphp
                @if($activeSubCategory && $activeSubCategory->childCategories()->active()->exists())
                <!-- child-category -->
                <div class="px-6 py-6 border-t border-gray-300">
                  <div class="widget-category wow animate__animated animate__fadeInUp" data-wow-delay=".2s">
                    <div class="flex flex-col gap-y-4 pb-8 border-b border-gray-300">
                      <div class="flex items-center justify-between widget-category-title">
                        <h6>Child Category</h6>
                        <a href="#" class="childcategory-reset-btn text-base leading-[26px] hover:underline hover:text-primary transition-colors duration-300 ease-in-out">Reset</a>
                      </div>
                      <div class="widget-category-content-list">
                        <div>
                          <ul class="flex flex-col gap-y-2">
                            @foreach($activeSubCategory->childCategories()->active()->get() as $childCat)
                            <li class="widget-category-content-list-items">
                              <label class="group flex items-center justify-between w-full cursor-pointer">
                                <span class="flex items-center gap-x-2">
                                  <span class="group-has-[input:checked]:group-hover:bg-[#06172e]/8 flex items-center justify-center w-9 h-9 bg-transparent rounded-full group-hover:bg-[#919EAB]/8 transition-colors duration-300 ease-in-out">
                                    <span class="relative inline-flex w-5 h-5 items-center justify-center">
                                      <input type="radio" name="child_category" value="{{ $childCat->id }}" {{ request('child_category') == $childCat->id ? 'checked' : '' }} onchange="submitShopFilterForm()" class="peer appearance-none w-full h-full border-2 focus:outline-none checked:border-none border-gray-300 rounded-sm bg-white checked:bg-primary transition-all duration-300 ease-in-out" />
                                      <span class="absolute inset-0 inline-flex items-center justify-center text-white opacity-0 peer-checked:opacity-100 transition-all">
                                        <i class="hgi hgi-stroke hgi-tick-02 text-[18px] leading-[18px]"></i>
                                      </span>
                                    </span>
                                  </span>
                                  <span class="text-light-primary-text group-hover:text-primary transition-colors duration-300 ease-in-out">{{ $childCat->name }}</span>
                                </span>
                                <span class="group-hover:text-primary transition-colors duration-300 ease-in-out">
                                  ({{ $childCategoryCounts[$childCat->id] ?? 0 }})
                                </span>
                              </label>
                            </li>
                            @endforeach
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
                @endif
                @endif
                  <!-- PRICE-Range-content -->
                  <div class="widget-price-range wow animate__animated animate__fadeInUp" data-wow-delay=".2s">
                    <div class="flex flex-col gap-y-4 px-6 py-6 border-b border-gray-300 widget-price-range-title">
                      <div class="flex items-center justify-between">
                        <h6>Price Range</h6>
                        <a href="#" id="price-range-reset" class="text-base leading-[26px] hover:underline hover:text-primary transition-colors duration-300 ease-in-out">Reset</a>
                      </div>
                      {{-- Hidden inputs submitted with form --}}
                      <input type="hidden" name="min_price" id="min_price_input" value="{{ $minPrice ?? 0 }}">
                      <input type="hidden" name="max_price" id="max_price_input" value="{{ $maxPrice ?? '' }}">
                      {{-- Two-box row with inline onchange handlers --}}
                      <div class="flex items-center gap-2">
                        <div class="flex flex-col flex-1 min-w-0">
                          <span class="price-box-label">Min</span>
                          <div class="price-box">
                            <select id="price-min-select" class="price-box-select" onchange="onPriceMinChange(this.value)">
                              <option value="0"     {{ ($minPrice ?? 0) == 0     ? 'selected' : '' }}>Min</option>
                              <option value="10000" {{ ($minPrice ?? 0) == 10000 ? 'selected' : '' }}>&#8377;10,000</option>
                              <option value="15000" {{ ($minPrice ?? 0) == 15000 ? 'selected' : '' }}>&#8377;15,000</option>
                              <option value="20000" {{ ($minPrice ?? 0) == 20000 ? 'selected' : '' }}>&#8377;20,000</option>
                              <option value="30000" {{ ($minPrice ?? 0) == 30000 ? 'selected' : '' }}>&#8377;30,000</option>
                            </select>
                          </div>
                        </div>
                        <span class="price-range-separator">to</span>
                        <div class="flex flex-col flex-1 min-w-0">
                          <span class="price-box-label">Max</span>
                          <div class="price-box">
                            <select id="price-max-select" class="price-box-select" onchange="onPriceMaxChange(this.value)">
                              <option value="10000" {{ ($maxPrice ?? '') == 10000 ? 'selected' : '' }}>&#8377;10,000</option>
                              <option value="15000" {{ ($maxPrice ?? '') == 15000 ? 'selected' : '' }}>&#8377;15,000</option>
                              <option value="20000" {{ ($maxPrice ?? '') == 20000 ? 'selected' : '' }}>&#8377;20,000</option>
                              <option value="30000" {{ ($maxPrice ?? '') == 30000 ? 'selected' : '' }}>&#8377;30,000</option>
                              <option value=""      {{ (($maxPrice ?? '') === '' || ($maxPrice ?? 0) > 30000) ? 'selected' : '' }}>&#8377;30,000+</option>
                            </select>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <!-- Rating-content -->

                  <!-- Discount-content -->
                  <div
                    class="widget-discount wow animate__animated animate__fadeInUp"
                    data-wow-delay=".2s"
                  >
                    <div
                      class="flex flex-col gap-y-4 px-6 py-8 border-b border-gray-300"
                    >
                      <div
                        class="flex items-center justify-between widget-discount-title"
                      >
                        <h6>Discount</h6>
                        <a
                          href="#"
                          class="discount-reset-btn text-base leading-[26px] hover:underline hover:text-primary transition-colors duration-300 ease-in-out"
                          >Reset</a
                        >
                      </div>
                      <div class="discount">
                        <ul class="flex flex-col gap-y-2">
                          <li class="discount-items">
                            <label
                              class="group flex items-center justify-between w-full cursor-pointer"
                            >
                              <span class="flex items-center gap-x-2">
                                <!-- custom checkbox wrapper -->
                                <span
                                  class="group-has-[input:checked]:group-hover:bg-[#06172e]/8 flex items-center justify-center w-9 h-9 bg-transparent rounded-full group-hover:bg-[#919EAB]/8 transition-colors duration-300 ease-in-out"
                                >
                                  <span
                                    class="relative inline-flex w-5 h-5 items-center justify-center"
                                  >
                                    <input
                                      type="checkbox"
                                      name="discount[]"
                                      value="0-5"
                                      {{ in_array('0-5', request('discount', [])) ? 'checked' : '' }}
                                      onchange="submitShopFilterForm()"
                                      class="peer appearance-none w-full h-full border-2 focus:outline-none checked:border-none border-gray-300 rounded-sm bg-white checked:bg-primary transition-all duration-300 ease-in-out"
                                    />

                                    <!-- checkbox tick icon -->
                                    <span
                                      class="absolute inset-0 inline-flex items-center justify-center text-white opacity-0 peer-checked:opacity-100 transition-all"
                                    >
                                      <i
                                        class="hgi hgi-stroke hgi-tick-02 text-[18px] leading-[18px]"
                                      ></i>
                                    </span>
                                  </span>
                                </span>
                                <span
                                  class="text-light-primary-text group-hover:text-primary transition-colors duration-300 ease-in-out"
                                >
                                  upto 5%
                                </span>
                              </span>
                              <span
                                class="group-hover:text-primary transition-colors duration-300 ease-in-out"
                              >
                                ({{ $discountCounts['0-5'] ?? 0 }})
                              </span>
                            </label>
                          </li>
                          <li class="discount-items">
                            <label
                              class="group flex items-center justify-between w-full cursor-pointer"
                            >
                              <span class="flex items-center gap-x-2">
                                <!-- custom checkbox wrapper -->
                                <span
                                  class="group-has-[input:checked]:group-hover:bg-[#06172e]/8 flex items-center justify-center w-9 h-9 bg-transparent rounded-full group-hover:bg-[#919EAB]/8 transition-colors duration-300 ease-in-out"
                                >
                                  <span
                                    class="relative inline-flex w-5 h-5 items-center justify-center"
                                  >
                                    <input
                                      type="checkbox"
                                      name="discount[]"
                                      value="5-10"
                                      {{ in_array('5-10', request('discount', [])) ? 'checked' : '' }}
                                      onchange="submitShopFilterForm()"
                                      class="peer appearance-none w-full h-full border-2 focus:outline-none checked:border-none border-gray-300 rounded-sm bg-white checked:bg-primary transition-all duration-300 ease-in-out"
                                    />

                                    <!-- checkbox tick icon -->
                                    <span
                                      class="absolute inset-0 inline-flex items-center justify-center text-white opacity-0 peer-checked:opacity-100 transition-all"
                                    >
                                      <i
                                        class="hgi hgi-stroke hgi-tick-02 text-[18px] leading-[18px]"
                                      ></i>
                                    </span>
                                  </span>
                                </span>
                                <span
                                  class="text-light-primary-text group-hover:text-primary transition-colors duration-300 ease-in-out"
                                >
                                  5% - 10%
                                </span>
                              </span>
                              <span
                                class="group-hover:text-primary transition-colors duration-300 ease-in-out"
                              >
                                ({{ $discountCounts['5-10'] ?? 0 }})
                              </span>
                            </label>
                          </li>
                          <li class="discount-items">
                            <label
                              class="group flex items-center justify-between w-full cursor-pointer"
                            >
                              <span class="flex items-center gap-x-2">
                                <!-- custom checkbox wrapper -->
                                <span
                                  class="group-has-[input:checked]:group-hover:bg-[#06172e]/8 flex items-center justify-center w-9 h-9 bg-transparent rounded-full group-hover:bg-[#919EAB]/8 transition-colors duration-300 ease-in-out"
                                >
                                  <span
                                    class="relative inline-flex w-5 h-5 items-center justify-center"
                                  >
                                    <input
                                      type="checkbox"
                                      name="discount[]"
                                      value="10-15"
                                      {{ in_array('10-15', request('discount', [])) ? 'checked' : '' }}
                                      onchange="submitShopFilterForm()"
                                      class="peer appearance-none w-full h-full border-2 focus:outline-none checked:border-none border-gray-300 rounded-sm bg-white checked:bg-primary transition-all duration-300 ease-in-out"
                                    />

                                    <!-- checkbox tick icon -->
                                    <span
                                      class="absolute inset-0 inline-flex items-center justify-center text-white opacity-0 peer-checked:opacity-100 transition-all"
                                    >
                                      <i
                                        class="hgi hgi-stroke hgi-tick-02 text-[18px] leading-[18px]"
                                      ></i>
                                    </span>
                                  </span>
                                </span>
                                <span
                                  class="text-light-primary-text group-hover:text-primary transition-colors duration-300 ease-in-out"
                                >
                                  10% - 15%
                                </span>
                              </span>
                              <span
                                class="group-hover:text-primary transition-colors duration-300 ease-in-out"
                              >
                                ({{ $discountCounts['10-15'] ?? 0 }})
                              </span>
                            </label>
                          </li>
                          <li class="discount-items">
                            <label
                              class="group flex items-center justify-between w-full cursor-pointer"
                            >
                              <span class="flex items-center gap-x-2">
                                <!-- custom checkbox wrapper -->
                                <span
                                  class="group-has-[input:checked]:group-hover:bg-[#06172e]/8 flex items-center justify-center w-9 h-9 bg-transparent rounded-full group-hover:bg-[#919EAB]/8 transition-colors duration-300 ease-in-out"
                                >
                                  <span
                                    class="relative inline-flex w-5 h-5 items-center justify-center"
                                  >
                                    <input
                                      type="checkbox"
                                      name="discount[]"
                                      value="15-25"
                                      {{ in_array('15-25', request('discount', [])) ? 'checked' : '' }}
                                      onchange="submitShopFilterForm()"
                                      class="peer appearance-none w-full h-full border-2 focus:outline-none checked:border-none border-gray-300 rounded-sm bg-white checked:bg-primary transition-all duration-300 ease-in-out"
                                    />

                                    <!-- checkbox tick icon -->
                                    <span
                                      class="absolute inset-0 inline-flex items-center justify-center text-white opacity-0 peer-checked:opacity-100 transition-all"
                                    >
                                      <i
                                        class="hgi hgi-stroke hgi-tick-02 text-[18px] leading-[18px]"
                                      ></i>
                                    </span>
                                  </span>
                                </span>
                                <span
                                  class="text-light-primary-text group-hover:text-primary transition-colors duration-300 ease-in-out"
                                >
                                  15% - 25%
                                </span>
                              </span>
                              <span
                                class="group-hover:text-primary transition-colors duration-300 ease-in-out"
                              >
                                ({{ $discountCounts['15-25'] ?? 0 }})
                              </span>
                            </label>
                          </li>
                          <li class="discount-items">
                            <label
                              class="group flex items-center justify-between w-full cursor-pointer"
                            >
                              <span class="flex items-center gap-x-2">
                                <!-- custom checkbox wrapper -->
                                <span
                                  class="group-has-[input:checked]:group-hover:bg-[#06172e]/8 flex items-center justify-center w-9 h-9 bg-transparent rounded-full group-hover:bg-[#919EAB]/8 transition-colors duration-300 ease-in-out"
                                >
                                  <span
                                    class="relative inline-flex w-5 h-5 items-center justify-center"
                                  >
                                    <input
                                      type="checkbox"
                                      name="discount[]"
                                      value="25-100"
                                      {{ in_array('25-100', request('discount', [])) ? 'checked' : '' }}
                                      onchange="submitShopFilterForm()"
                                      class="peer appearance-none w-full h-full border-2 focus:outline-none checked:border-none border-gray-300 rounded-sm bg-white checked:bg-primary transition-all duration-300 ease-in-out"
                                    />

                                    <!-- checkbox tick icon -->
                                    <span
                                      class="absolute inset-0 inline-flex items-center justify-center text-white opacity-0 peer-checked:opacity-100 transition-all"
                                    >
                                      <i
                                        class="hgi hgi-stroke hgi-tick-02 text-[18px] leading-[18px]"
                                      ></i>
                                    </span>
                                  </span>
                                </span>
                                <span
                                  class="text-light-primary-text group-hover:text-primary transition-colors duration-300 ease-in-out"
                                >
                                  More than 25%
                                </span>
                              </span>
                              <span
                                class="group-hover:text-primary transition-colors duration-300 ease-in-out"
                              >
                                ({{ $discountCounts['25-100'] ?? 0 }})
                              </span>
                            </label>
                          </li>
                        </ul>
                      </div>
                    </div>
                  </div>
                  <!-- Brand-content -->
                  <div
                    class="widget-brand wow animate__animated animate__fadeInUp"
                    data-wow-delay=".2s"
                  >
                    <div
                      class="flex flex-col gap-y-4 px-6 py-8 border-b border-gray-300"
                    >
                      <div
                        class="flex items-center justify-between widget-brand-title"
                      >
                        <h6>Brand</h6>
                        <a
                          href="#"
                          class="brand-reset-btn text-base leading-[26px] hover:underline hover:text-primary transition-colors duration-300 ease-in-out"
                          >Reset</a
                        >
                      </div>
                      <div class="brand-content-list max-h-60 overflow-y-auto pr-1">
                        <div>
                          <ul class="flex flex-col gap-y-2">
                            @foreach($brands as $brand)
                            <li class="brand-list-item">
                              <label
                                class="group flex items-center justify-between w-full cursor-pointer"
                              >
                                <span class="flex items-center gap-x-2">
                                  <span
                                    class="group-has-[input:checked]:group-hover:bg-[#06172e]/8 flex items-center justify-center w-9 h-9 bg-transparent rounded-full group-hover:bg-[#919EAB]/8 transition-colors duration-300 ease-in-out"
                                  >
                                    <span
                                      class="relative inline-flex w-5 h-5 items-center justify-center"
                                    >
                                      <input
                                        type="radio"
                                        name="brand"
                                        value="{{ $brand->id }}"
                                        {{ request('brand') == $brand->id ? 'checked' : '' }}
                                        onchange="submitShopFilterForm()"
                                        class="peer appearance-none w-full h-full border-2 focus:outline-none checked:border-none border-gray-300 rounded-sm bg-white checked:bg-primary transition-all duration-300 ease-in-out"
                                      />
                                      <span
                                        class="absolute inset-0 inline-flex items-center justify-center text-white opacity-0 peer-checked:opacity-100 transition-all"
                                      >
                                        <i
                                          class="hgi hgi-stroke hgi-tick-02 text-[18px] leading-[18px]"
                                        ></i>
                                      </span>
                                    </span>
                                  </span>
                                  <span
                                    class="text-light-primary-text group-hover:text-primary transition-colors duration-300 ease-in-out"
                                  >
                                    {{ $brand->name }}
                                  </span>
                                </span>
                                <span
                                  class="group-hover:text-primary transition-colors duration-300 ease-in-out"
                                >
                                  ({{ $brand->products_count ?? 0 }})
                                </span>
                              </label>
                            </li>
                            @endforeach
                          </ul>
                        </div>
                      </div>
                    </div>
                  </div>

                </div>
            </div>
          </div>
          <div
            class="xl:col-span-9 lg:col-span-9 col-span-12 row-start-1 lg:row-start-auto"
          >
            @if($seller)
              @php
                $banner = $seller->company_banner 
                  ? (filter_var($seller->company_banner, FILTER_VALIDATE_URL) ? $seller->company_banner : asset('storage/' . $seller->company_banner))
                  : asset('ecommerce-assets/images/vendors-list/vendor-1.png');
                $logo = $seller->company_logo 
                  ? (filter_var($seller->company_logo, FILTER_VALIDATE_URL) ? $seller->company_logo : asset('storage/' . $seller->company_logo))
                  : asset('admin-assets/images/users/avatar-1.jpg');
              @endphp
              <div class="mb-8 rounded-3xl border border-gray-200 overflow-hidden bg-white shadow-sm wow animate__animated animate__fadeInDown" data-wow-delay=".2s">
                <div class="w-full h-48 md:h-64 relative">
                  <img src="{{ $banner }}" alt="Seller Banner" class="w-full h-full object-cover">
                  <div class="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                  
                  <div class="absolute bottom-6 left-6 right-6 flex flex-col md:flex-row items-start md:items-end justify-between gap-4">
                    <div class="flex items-center gap-4">
                      <div class="w-20 h-20 rounded-full border-4 border-white bg-white overflow-hidden shadow shrink-0">
                        <img src="{{ $logo }}" alt="Seller Logo" class="w-full h-full object-cover">
                      </div>
                      <div>
                        <h3 class="text-white text-2xl font-bold mb-1">{{ $seller->company_name ?: $seller->name }}</h3>
                        <p class="text-gray-200 text-sm flex items-center gap-1.5">
                           <i class="hgi hgi-stroke hgi-location-06 text-base"></i>
                          {{ implode(', ', array_filter([$seller->address, $seller->district, $seller->state])) }}
                        </p>
                      </div>
                    </div>
                    
                    <div class="flex items-center gap-3 shrink-0">
                      @if($seller->mobile)
                        <a href="tel:{{ $seller->mobile }}" class="bg-white/20 hover:bg-white/30 text-white rounded-full p-2.5 backdrop-blur-sm transition-colors" title="Call Seller">
                          <i class="hgi hgi-stroke hgi-call text-xl"></i>
                        </a>
                      @endif
                      @if($seller->email)
                        <a href="mailto:{{ $seller->email }}" class="bg-white/20 hover:bg-white/30 text-white rounded-full p-2.5 backdrop-blur-sm transition-colors" title="Email Seller">
                          <i class="hgi hgi-stroke hgi-mail-01 text-xl"></i>
                        </a>
                      @endif
                      <a href="{{ route('shop') }}" class="bg-primary hover:bg-primary-hover text-white rounded-full px-5 py-2.5 text-sm font-semibold shadow transition-colors flex items-center gap-2">
                        <i class="hgi hgi-stroke hgi-left-to-right-list-bullet text-lg"></i>
                        All Products
                      </a>
                    </div>
                  </div>
                </div>
              </div>
            @endif

            <!-- Top bar with Search and Sorting outside the AJAX-replaceable area -->
            <div class="flex items-center justify-between pb-8 wow animate__animated animate__fadeInUp" data-wow-delay=".2s">
              <div class="flex items-center gap-x-4 shrink-0 justify-start">
                <div class="flex items-center gap-x-4">
                  <button class="w-10 h-10 rounded-full inline-flex items-center justify-center btn-primary cursor-pointer">
                    <i class="hgi hgi-stroke hgi-grid-view text-lg text-white"></i>
                  </button>
                </div>
                <div class="lg:flex">
                  <p class="text-light-secondary-text">
                    Showing <span id="shop-range-prefix">{{ $products->count() > 0 ? '1' : '0' }}</span>-<span id="shop-showing-count">{{ $products->count() }}</span> of <span id="shop-total-count">{{ $products->total() }}</span> results
                  </p>
                </div>
              </div>
              <!-- Search & Sorting container -->
              <div class="flex items-center gap-4">
                <!-- Search Input Box -->
                <div class="relative w-full max-w-[280px]">
                  <div class="input-group medium pl-4 py-1.5 pr-2.5 rounded-[100px] border border-gray-300 flex items-center gap-x-2 bg-white">
                    <i class="hgi hgi-stroke hgi-search-01 text-lg text-gray-400"></i>
                    <input
                      type="text"
                      name="search"
                      id="sidebar-search-input"
                      class="form-control border-none focus:ring-0 p-0 text-sm leading-5 placeholder-gray-400 w-full"
                      placeholder="Search products..."
                      value="{{ $search }}"
                    />
                  </div>
                </div>
                <!-- Sorting -->
                <div class="relative min-w-[130px]">
                  <select id="sorting" class="filter-select label"
                    onchange="document.getElementById('sort_input').value = this.value; submitShopFilterForm();">
                    <option value="latest" {{ $sort == 'latest' ? 'selected' : '' }}>Latest</option>
                    <option value="price_asc" {{ $sort == 'price_asc' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="price_desc" {{ $sort == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                    <option value="name_asc" {{ $sort == 'name_asc' ? 'selected' : '' }}>A-Z Order</option>
                  </select>
                  <label for="sorting" class="nice-select-label">Sorting</label>
                </div>
              </div>
            </div>

            <!-- Loader bar for Flipkart style feedback -->
            <div id="shop-loader-bar" class="w-full h-1 bg-gray-100 overflow-hidden relative mb-4 rounded">
              <div class="bg-primary h-full w-1/3 absolute left-0 transition-all duration-300" style="width: 0%;" id="shop-loader-bar-inner"></div>
            </div>

            @include('web.partials.shop-products')
          </div>
        </div>
        </form>
      </div>
    </section>
    <!-- ========== Filter with 3-column Section End ========== -->



@push('scripts')
<script>
// Seamless On-Scroll Loading State
let page = {{ $products->currentPage() }};
let lastPage = {{ $products->lastPage() }};
let loading = false;
let currentFetchController = null;

function triggerManualLoadShopPage() {
    loadPage(page + 1, true);
}

function handleShopScroll() {
    if (loading || page >= lastPage) return;
    const sentinel = document.getElementById('smart-scroll-sentinel');
    if (!sentinel) return;

    const rect = sentinel.getBoundingClientRect();
    const isNearBottom = rect.top <= (window.innerHeight || document.documentElement.clientHeight) + 350;

    if (isNearBottom) {
        loadPage(page + 1, true);
    }
}

window.addEventListener('scroll', handleShopScroll, { passive: true });

function loadPage(pageNum, isAppend) {
    if (isAppend && loading) return;
    loading = true;

    // Save search input focus state
    const activeEl = document.activeElement;
    const isSearchFocused = activeEl && activeEl.id === 'sidebar-search-input';
    const selectionStart = isSearchFocused ? activeEl.selectionStart : null;
    const selectionEnd = isSearchFocused ? activeEl.selectionEnd : null;

    // Abort previous fetch if starting a new filter load
    if (!isAppend && currentFetchController) {
        currentFetchController.abort();
    }
    currentFetchController = new AbortController();
    const signal = currentFetchController.signal;

    const loaderBar = document.getElementById('shop-loader-bar');
    if (loaderBar) loaderBar.classList.add('loading');

    const loader = document.getElementById('smart-scroll-loader');
    const manualBtnWrapper = document.getElementById('smart-manual-load-wrapper');
    if (loader && isAppend) loader.style.setProperty('display', 'flex', 'important');
    if (manualBtnWrapper) manualBtnWrapper.style.setProperty('display', 'none', 'important');

    const form = document.getElementById('shop-filter-form');
    const formData = new FormData(form);
    const params = new URLSearchParams();

    for (const [key, value] of formData.entries()) {
        if (value !== '') {
            params.append(key, value);
        }
    }
    params.set('page', pageNum);

    const queryString = params.toString();
    const fetchUrl = form.action + (queryString ? '?' + queryString : '');

    fetch(fetchUrl, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        },
        signal: signal
    })
    .then(response => response.json())
    .then(data => {
        const grid = document.querySelector('.shop-custom-grid');
        if (!grid) return;

        if (isAppend) {
            // Remove empty message if present
            const emptyMsg = grid.querySelector('.vm-empty');
            if (emptyMsg) grid.innerHTML = '';

            // Append new cards HTML directly
            grid.insertAdjacentHTML('beforeend', data.html);
        } else {
            // Reset grid completely
            grid.innerHTML = data.html;
            autoBatchCount = 0;
        }

        // Update counts & progress bar
        page = data.current_page;
        lastPage = data.last_page;
        const currentCount = grid.children.length;

        const totalCountSpan = document.getElementById('shop-total-count');
        const showingCountSpan = document.getElementById('shop-showing-count');
        const rangePrefixSpan = document.getElementById('shop-range-prefix');
        const smartVisibleCount = document.getElementById('smart-visible-count');
        const smartTotalCount = document.getElementById('smart-total-count');
        const progressBarInner = document.getElementById('smart-progress-bar-inner');

        if (totalCountSpan) totalCountSpan.textContent = data.total;
        if (showingCountSpan) showingCountSpan.textContent = currentCount;
        if (rangePrefixSpan) rangePrefixSpan.textContent = currentCount > 0 ? '1' : '0';
        if (smartVisibleCount) smartVisibleCount.textContent = currentCount;
        if (smartTotalCount) smartTotalCount.textContent = data.total;
        if (progressBarInner && data.total > 0) {
            const percent = Math.min(100, Math.round((currentCount / data.total) * 100));
            progressBarInner.style.width = percent + '%';
        }

        // Hide loader
        if (loader) loader.style.setProperty('display', 'none', 'important');

        // Manage Load More button vs End of Results
        if (page >= lastPage) {
            if (manualBtnWrapper) manualBtnWrapper.style.setProperty('display', 'none', 'important');
            const endText = document.getElementById('smart-end-text');
            if (endText) endText.style.setProperty('display', 'block', 'important');
        }

        // Push URL state to browser address bar
        window.history.pushState(null, '', fetchUrl);

        // Restore focus to search input if active before AJAX
        if (isSearchFocused) {
            const searchInput = document.getElementById('sidebar-search-input');
            if (searchInput) {
                const restoreFocus = () => {
                    searchInput.focus();
                    if (selectionStart !== null) {
                        const valLen = searchInput.value.length;
                        const start = Math.min(selectionStart, valLen);
                        const end = Math.min(selectionEnd, valLen);
                        searchInput.setSelectionRange(start, end);
                    }
                };
                restoreFocus();
                setTimeout(restoreFocus, 50);
                setTimeout(restoreFocus, 150);
            }
        }
    })
    .catch(error => {
        if (error.name === 'AbortError') return;
        console.error('Error loading products:', error);
    })
    .finally(() => {
        if (currentFetchController && currentFetchController.signal === signal) {
            loading = false;
            if (loaderBar) loaderBar.classList.remove('loading');
            if (loader) loader.style.setProperty('display', 'none', 'important');
        }
    });
}

function submitShopFilterForm() {
    page = 1;
    exhausted = false;
    loadPage(1, false);
}

// ── Global price filter helpers (called by inline onchange) ───
var _priceMinOpts = [
    { v: '0',     l: 'Min' },
    { v: '10000', l: '\u20b910,000' },
    { v: '15000', l: '\u20b915,000' },
    { v: '20000', l: '\u20b920,000' },
    { v: '30000', l: '\u20b930,000' },
];
var _priceMaxOpts = [
    { v: '10000', l: '\u20b910,000' },
    { v: '15000', l: '\u20b915,000' },
    { v: '20000', l: '\u20b920,000' },
    { v: '30000', l: '\u20b930,000' },
    { v: '',      l: '\u20b930,000+' },
];

function initPriceFilters() {
    var minSel = document.getElementById('price-min-select');
    var maxSel = document.getElementById('price-max-select');
    if (!minSel || !maxSel) return;

    var minVal = minSel.value;
    var maxVal = maxSel.value;

    // Filter Max options based on minVal
    var minNum = parseInt(minVal || 0);
    var prevMax = maxVal;
    maxSel.innerHTML = '';
    _priceMaxOpts.forEach(function(o) {
        if (o.v !== '' && parseInt(o.v) <= minNum) return;
        var el = document.createElement('option');
        el.value = o.v; el.textContent = o.l;
        if (o.v === prevMax) el.selected = true;
        maxSel.appendChild(el);
    });
    if (maxSel.selectedIndex < 0 && maxSel.options.length > 0) {
        maxSel.options[maxSel.options.length - 1].selected = true;
    }

    // Filter Min options based on maxVal
    var prevMin = minSel.value;
    minSel.innerHTML = '';
    _priceMinOpts.forEach(function(o) {
        if (maxVal !== '' && parseInt(o.v) >= parseInt(maxVal)) return;
        var el = document.createElement('option');
        el.value = o.v; el.textContent = o.l;
        if (o.v === prevMin) el.selected = true;
        minSel.appendChild(el);
    });
    if (minSel.selectedIndex < 0 && minSel.options.length > 0) {
        minSel.options[0].selected = true;
    }

    // Sync NiceSelect custom dropdowns
    if (window.jQuery && jQuery.fn.niceSelect) {
        jQuery('#price-min-select, #price-max-select').niceSelect('update');
    }
}

function onPriceMinChange(minVal) {
    var maxSel = document.getElementById('price-max-select');
    var minInput = document.getElementById('min_price_input');
    var maxInput = document.getElementById('max_price_input');
    if (!maxSel) return;

    // Rebuild max: only options strictly > minVal
    var prevMax = maxSel.value;
    maxSel.innerHTML = '';
    var minNum = parseInt(minVal || 0);
    _priceMaxOpts.forEach(function(o) {
        if (o.v !== '' && parseInt(o.v) <= minNum) return;
        var el = document.createElement('option');
        el.value = o.v; el.textContent = o.l;
        if (o.v === prevMax) el.selected = true;
        maxSel.appendChild(el);
    });
    if (maxSel.selectedIndex < 0 && maxSel.options.length > 0) {
        maxSel.options[maxSel.options.length - 1].selected = true;
    }

    // Update hidden inputs and submit
    if (minInput) minInput.value = minVal;
    if (maxInput) maxInput.value = maxSel.value;

    // Sync NiceSelect custom dropdowns
    if (window.jQuery && jQuery.fn.niceSelect) {
        jQuery('#price-min-select, #price-max-select').niceSelect('update');
    }

    submitShopFilterForm();
}

function onPriceMaxChange(maxVal) {
    var minSel = document.getElementById('price-min-select');
    var minInput = document.getElementById('min_price_input');
    var maxInput = document.getElementById('max_price_input');
    if (!minSel) return;

    // Rebuild min: only options strictly < maxVal
    var prevMin = minSel.value;
    minSel.innerHTML = '';
    _priceMinOpts.forEach(function(o) {
        if (maxVal !== '' && parseInt(o.v) >= parseInt(maxVal)) return;
        var el = document.createElement('option');
        el.value = o.v; el.textContent = o.l;
        if (o.v === prevMin) el.selected = true;
        minSel.appendChild(el);
    });
    if (minSel.selectedIndex < 0 && minSel.options.length > 0) {
        minSel.options[0].selected = true;
    }

    // Update hidden inputs and submit
    if (minInput) minInput.value = minSel.value;
    if (maxInput) maxInput.value = maxVal;

    // Sync NiceSelect custom dropdowns
    if (window.jQuery && jQuery.fn.niceSelect) {
        jQuery('#price-min-select, #price-max-select').niceSelect('update');
    }

    submitShopFilterForm();
}



document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('shop-filter-form');

    // ── Price Range Reset ─────────────────────────────────────
    const priceReset = document.getElementById('price-range-reset');
    if (priceReset) {
        priceReset.addEventListener('click', function(e) {
            e.preventDefault();
            const minSel = document.getElementById('price-min-select');
            const maxSel = document.getElementById('price-max-select');
            if (minSel) { 
                minSel.innerHTML = '';
                _priceMinOpts.forEach(function(o) {
                    var el = document.createElement('option');
                    el.value = o.v; el.textContent = o.l;
                    minSel.appendChild(el);
                });
                minSel.value = '0'; 
            }
            if (maxSel) { 
                maxSel.innerHTML = '';
                _priceMaxOpts.forEach(function(o) {
                    var el = document.createElement('option');
                    el.value = o.v; el.textContent = o.l;
                    maxSel.appendChild(el);
                });
                maxSel.value = ''; 
            }
            document.getElementById('min_price_input').value = '0';
            document.getElementById('max_price_input').value = '';
            initPriceFilters();
            submitShopFilterForm();
        });
    }

    // Intercept Reset button clicks
    document.addEventListener('click', function(e) {
        const categoryReset = e.target.closest('.category-reset-btn');
        if (categoryReset) {
            e.preventDefault();
            if (form) {
                form.querySelectorAll('input[name="category"], input[name="sub_category"], input[name="child_category"]').forEach(radio => radio.checked = false);
                submitShopFilterForm();
            }
            return;
        }

        const subcategoryReset = e.target.closest('.subcategory-reset-btn');
        if (subcategoryReset) {
            e.preventDefault();
            if (form) {
                form.querySelectorAll('input[name="sub_category"], input[name="child_category"]').forEach(radio => radio.checked = false);
                submitShopFilterForm();
            }
            return;
        }

        const childcategoryReset = e.target.closest('.childcategory-reset-btn');
        if (childcategoryReset) {
            e.preventDefault();
            if (form) {
                form.querySelectorAll('input[name="child_category"]').forEach(radio => radio.checked = false);
                submitShopFilterForm();
            }
            return;
        }

        const brandReset = e.target.closest('.brand-reset-btn');
        if (brandReset) {
            e.preventDefault();
            if (form) {
                form.querySelectorAll('input[name="brand"]').forEach(radio => radio.checked = false);
                submitShopFilterForm();
            }
            return;
        }

        const discountReset = e.target.closest('.discount-reset-btn');
        if (discountReset) {
            e.preventDefault();
            if (form) {
                form.querySelectorAll('input[name="discount[]"]').forEach(checkbox => checkbox.checked = false);
                submitShopFilterForm();
            }
            return;
        }
    });

    // Intercept form submission
    if (form) {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            submitShopFilterForm();
        });
    }

    // Real-time Debounced Search Input
    const searchInput = document.getElementById('sidebar-search-input');
    var debounceTimeout = null;
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(debounceTimeout);
            debounceTimeout = setTimeout(submitShopFilterForm, 400);
        });
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                submitShopFilterForm();
            }
        });
    }

    // Clear All Filters Button Action
    const clearAllBtn = document.querySelector('.sidebar-title a');
    if (clearAllBtn) {
        clearAllBtn.addEventListener('click', function(e) {
            e.preventDefault();
            if (form) {
                form.reset();
                if (searchInput) searchInput.value = '';
                
                // Uncheck radio/checkboxes
                form.querySelectorAll('input[type="checkbox"], input[type="radio"]').forEach(function(input) {
                    input.checked = false;
                });

                // Reset hidden price inputs
                document.getElementById('min_price_input').value = '0';
                document.getElementById('max_price_input').value = '';

                // Reset dropdowns to defaults
                const minSel = document.getElementById('price-min-select');
                const maxSel = document.getElementById('price-max-select');
                if (minSel) {
                    minSel.innerHTML = '';
                    _priceMinOpts.forEach(function(o) {
                        var el = document.createElement('option');
                        el.value = o.v; el.textContent = o.l;
                        minSel.appendChild(el);
                    });
                    minSel.value = '0';
                }
                if (maxSel) {
                    maxSel.innerHTML = '';
                    _priceMaxOpts.forEach(function(o) {
                        var el = document.createElement('option');
                        el.value = o.v; el.textContent = o.l;
                        maxSel.appendChild(el);
                    });
                    maxSel.value = '';
                }

                initPriceFilters();
                submitShopFilterForm();
            }
        });
    }

    // Run initial price constraint on load
    initPriceFilters();
});
</script>
@endpush
@endsection
