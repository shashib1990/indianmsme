@extends('web.layouts.app')

@php
  $profile     = $seller->sellerProfile;
  $companyName = $profile->company_name ?? $seller->name;
@endphp

@section('title', $companyName . ' — Indian MSME Store')

@push('styles')
<style>
  /* Price range box + embedded dropdown */
  .price-box {
    background: #fff;
    border: 1px solid #d1d5db;
    border-radius: 4px;
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

  /* Sidebar scrollbar */
  .vm-sidebar::-webkit-scrollbar { width: 4px; }
  .vm-sidebar::-webkit-scrollbar-track { background: transparent; }
  .vm-sidebar::-webkit-scrollbar-thumb { background: #d1d5db; border-radius: 99px; }
  .vm-sidebar::-webkit-scrollbar-thumb:hover { background: #06172e; }
  .vm-sidebar { scrollbar-width: thin; scrollbar-color: #d1d5db transparent; }

  /* Infinite scroll spinner & Grid override */
  #vm-products-grid {
    display: grid !important;
    grid-template-columns: repeat(5, minmax(0, 1fr)) !important;
    gap: 24px !important;
    min-height: 200px;
  }
  @media (max-width: 1399px) {
    #vm-products-grid {
      grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
    }
  }
  @media (max-width: 1199px) {
    #vm-products-grid {
      grid-template-columns: repeat(3, minmax(0, 1fr)) !important;
    }
  }
  @media (max-width: 767px) {
    #vm-products-grid {
      grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
    }
  }
  @media (max-width: 479px) {
    #vm-products-grid {
      grid-template-columns: repeat(1, minmax(0, 1fr)) !important;
    }
  }
  .vm-loading-spinner {
    display: flex; align-items: center; justify-content: center;
    padding: 32px 0; gap: 10px; color: #637381;
  }
  .vm-spinner-dot {
    width: 10px; height: 10px; border-radius: 50%; background: #06172e;
    animation: vmBounce 1s infinite ease-in-out;
  }
  .vm-spinner-dot:nth-child(2) { animation-delay: .15s; }
  .vm-spinner-dot:nth-child(3) { animation-delay: .3s; }
  @keyframes vmBounce {
    0%,80%,100% { transform:scale(.7); opacity:.5; }
    40%          { transform:scale(1);  opacity:1; }
  }



  .vm-empty { grid-column: 1 / -1; text-align: center; padding: 60px 20px; }

  /* Flipkart smooth progress loader bar */
  #vm-loader-bar {
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: #06172e;
    transform: scaleX(0);
    transform-origin: left;
    transition: transform .2s ease;
    z-index: 50;
  }
  #vm-loader-bar.loading {
    transform: scaleX(1);
    animation: vmProgress 1s infinite linear;
  }
  @keyframes vmProgress {
    0% { transform: translateX(-100%) scaleX(0.5); }
    50% { transform: translateX(0) scaleX(0.5); }
    100% { transform: translateX(100%) scaleX(0.5); }
  }
</style>
@endpush

@section('content')
@php
  $imvCode     = $profile->seller_id ?? 'N/A';
  $bannerRaw   = $profile->company_banner ?? null;
  $logoRaw     = $profile->company_logo ?? null;

  $defaultLogo = asset('admin-assets/images/IndianMSME.png');

  $bannerSrc = $bannerRaw
    ? (filter_var($bannerRaw, FILTER_VALIDATE_URL) ? $bannerRaw : asset('storage/' . $bannerRaw))
    : \App\Models\SystemSetting::getVal('website_header_logo', asset('ecommerce-assets/images/logo.png'));

  $logoSrc = $logoRaw
    ? (filter_var($logoRaw, FILTER_VALIDATE_URL) ? $logoRaw : asset('storage/' . $logoRaw))
    : $defaultLogo;

  $vendorCategory  = $profile->category->name ?? $products->first()?->category?->name ?? 'General';
  $priceMin        = (int) ($priceRange['min'] ?? 0);
  $priceMax        = (int) ($priceRange['max'] ?? 10000);
  $currentMin      = request('price_min') !== null ? (int) request('price_min') : 0;
  $currentMax      = request('price_max') !== null ? (int) request('price_max') : '';
  $currentDiscount = request('discount', '');
@endphp

{{-- ── Breadcrumb ─────────────────────────────────────────────────── --}}
<div class="container py-12">
  <div class="breadcrumb">
    <ul>
      <li><a href="{{ route('home') }}"><span class="inline-flex items-center justify-center"><i class="hgi hgi-stroke hgi-home-01 text-2xl leading-6"></i></span>Home</a></li>
      <li class="text-light-disabled-text">&#8226;</li>
      <li><a href="{{ route('vendors') }}" class="text-sm! leading-[22px]! text-light-primary-text! font-normal!">Sellers</a></li>
      <li class="text-light-disabled-text">&#8226;</li>
      <li><span class="text-sm leading-[22px]">{{ $companyName }}</span></li>
    </ul>
  </div>
</div>

{{-- ── Main grid ───────────────────────────────────────────────────── --}}
<div class="pb-[90px]">
  <div class="container">
    <div class="grid grid-cols-12 lg:gap-x-6 gap-y-6">

      {{-- ══════ SIDEBAR (left) ══════ --}}
      <div class="xl:col-span-3 lg:col-span-4 col-span-12 row-start-2 lg:row-start-auto flex flex-col gap-y-4">

          {{-- Vendor profile card — original theme style --}}
          <div class="rounded-2xl border border-gray-300 overflow-visible">
            {{-- Banner thumbnail --}}
            <div class="w-full h-[194px] rounded-t-2xl overflow-hidden">
              <img src="{{ $bannerSrc }}" alt="{{ $companyName }}" class="w-full h-full object-cover rounded-t-2xl" />
            </div>
            {{-- Info below banner --}}
            <div class="pb-6 px-6 flex flex-col gap-y-6 relative" style="padding-top: 60px;">
              {{-- Logo shape & circle overlapping banner (original theme style) --}}
              <div class="z-50 size-[64px] bg-transparent absolute top-[-36px] left-1/2 -translate-x-1/2 before:absolute before:left-1/2 before:-translate-x-1/2 before:top-0 before:z-[-1] before:w-[144px] before:h-[64px] before:bg-[url('images/vendor-filter-top-shape.html')] before:bg-no-repeat">
                <img src="{{ $logoSrc }}" alt="{{ $companyName }}" class="w-full h-full object-contain rounded-full bg-white border border-gray-200" />
              </div>

              <div class="flex flex-col items-center text-center gap-y-2 wow animate__animated animate__fadeInUp" data-wow-delay=".6s">
                <h5 class="font-bold text-light-primary-text">{{ $companyName }}</h5>
                <p class="text-sm text-light-secondary-text">
                  {{ $vendorCategory ?? 'General' }}
                </p>
                <div class="inline-flex items-center gap-1 bg-primary/10 text-primary px-3 py-1 rounded-full text-xs font-semibold mt-1">
                  <i class="hgi hgi-stroke hgi-checkmark-circle-02 text-[14px]"></i>
                  Verified Seller
                </div>
              </div>

              <div class="flex flex-col gap-y-6 divide-y divide-gray-300 wow animate__animated animate__fadeInUp" data-wow-delay=".8s">
                <div class="flex items-center justify-between gap-x-2 pb-6">
                  <div class="flex flex-col items-center gap-y-1 p-4 rounded-2xl border border-gray-300 flex-1">
                    <p class="text-xs leading-[18px] font-medium text-gray-500">Total Product</p>
                    <h6 class="text-light-primary-text font-bold">{{ $totalProducts }}</h6>
                  </div>
                  <div class="flex flex-col items-center gap-y-1 p-4 rounded-2xl border border-gray-300 flex-1">
                    <p class="text-xs leading-[18px] font-medium text-gray-500">IMV Code</p>
                    <h6 class="text-light-primary-text font-bold">{{ $imvCode }}</h6>
                  </div>
                </div>
                @if($seller->has_sub_sellers)
                  <div class="flex items-center pt-4">
                    <a href="{{ route('vendor.sub-sellers', $imvCode) }}"
                       class="btn btn-small flex-1 text-center bg-amber-500/10 hover:bg-amber-500/20 text-amber-600 transition-colors duration-300 rounded-xl py-2.5 font-semibold text-xs flex items-center justify-center gap-1.5">
                      <i class="hgi hgi-stroke hgi-user-group text-sm"></i>
                      Sub Sellers
                    </a>
                  </div>
                @endif
              </div>
            </div>
          </div>

          {{-- Filters card — original theme style --}}
          <div class="border border-gray-300 rounded-2xl">
            {{-- Filter header --}}
            <div class="px-6 py-4 bg-gray-200 rounded-t-2xl sidebar-title wow animate__animated animate__fadeInUp" data-wow-delay=".5s">
              <div class="flex items-center justify-between">
                <h5>Filters</h5>
                <button id="vm-clear-all" class="text-primary text-base leading-[26px] font-semibold hover:underline">Clear All</button>
              </div>
            </div>

            <div class="px-6 py-6 flex flex-col gap-y-0">

              {{-- Price Range --}}
              <div class="widget-price-range wow animate__animated animate__fadeInUp" data-wow-delay=".3s">
                <div class="flex flex-col gap-y-4 py-8 border-b border-gray-300">
                  <div class="flex items-center justify-between">
                    <h6>Price Range</h6>
                    <button id="price-range-reset" class="text-base leading-[26px] hover:underline hover:text-primary transition-colors duration-300" data-target="price">Reset</button>
                  </div>
                  {{-- Hidden inputs --}}
                  <input type="hidden" id="min_price_input" value="{{ $currentMin }}">
                  <input type="hidden" id="max_price_input" value="{{ $currentMax }}">
                  {{-- Two-box row --}}
                  <div class="flex items-center gap-2">
                    <div class="flex flex-col flex-1 min-w-0">
                      <span class="price-box-label">Min</span>
                      <div class="price-box">
                        <select id="price-min-select" class="price-box-select" onchange="onPriceMinChange(this.value)">
                          <option value="0"     {{ $currentMin == 0     ? 'selected' : '' }}>Min</option>
                          <option value="10000" {{ $currentMin == 10000 ? 'selected' : '' }}>&#8377;10,000</option>
                          <option value="15000" {{ $currentMin == 15000 ? 'selected' : '' }}>&#8377;15,000</option>
                          <option value="20000" {{ $currentMin == 20000 ? 'selected' : '' }}>&#8377;20,000</option>
                          <option value="30000" {{ $currentMin == 30000 ? 'selected' : '' }}>&#8377;30,000</option>
                        </select>
                      </div>
                    </div>
                    <span class="price-range-separator">to</span>
                    <div class="flex flex-col flex-1 min-w-0">
                      <span class="price-box-label">Max</span>
                      <div class="price-box">
                        <select id="price-max-select" class="price-box-select" onchange="onPriceMaxChange(this.value)">
                          <option value="10000" {{ $currentMax == 10000 ? 'selected' : '' }}>&#8377;10,000</option>
                          <option value="15000" {{ $currentMax == 15000 ? 'selected' : '' }}>&#8377;15,000</option>
                          <option value="20000" {{ $currentMax == 20000 ? 'selected' : '' }}>&#8377;20,000</option>
                          <option value="30000" {{ $currentMax == 30000 ? 'selected' : '' }}>&#8377;30,000</option>
                          <option value=""      {{ ($currentMax === '' || $currentMax > 30000) ? 'selected' : '' }}>&#8377;30,000+</option>
                        </select>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              {{-- Discount --}}
              <div class="widget-discount wow animate__animated animate__fadeInUp" data-wow-delay=".7s">
                <div class="flex flex-col gap-y-4 py-8">
                  <div class="flex items-center justify-between widget-discount-title">
                    <h6>Discount</h6>
                    <button class="vm-reset-btn text-base leading-[26px] hover:underline hover:text-primary transition-colors duration-300" data-target="discount">Reset</button>
                  </div>
                  <div class="discount">
                    <ul class="flex flex-col gap-y-2">
                      @foreach([
                        ['value'=>'5',  'label'=>'Upto 5%'],
                        ['value'=>'10', 'label'=>'5% - 10%'],
                        ['value'=>'15', 'label'=>'10% - 15%'],
                        ['value'=>'25', 'label'=>'15% - 25%'],
                        ['value'=>'26', 'label'=>'More than 25%'],
                      ] as $disc)
                        <li class="discount-items">
                          <label class="group flex items-center justify-between w-full cursor-pointer">
                            <span class="flex items-center gap-x-2">
                              <span class="group-has-[input:checked]:group-hover:bg-[#06172e]/8 flex items-center justify-center w-9 h-9 bg-transparent rounded-full group-hover:bg-[#919EAB]/8 transition-colors duration-300 ease-in-out">
                                <span class="relative inline-flex w-5 h-5 items-center justify-center">
                                  <input type="radio" name="vm_discount" value="{{ $disc['value'] }}"
                                    class="peer appearance-none w-full h-full border-2 focus:outline-none checked:border-none border-gray-300 rounded-sm bg-white checked:bg-primary transition-all duration-300 ease-in-out cursor-pointer"
                                    {{ $currentDiscount == $disc['value'] ? 'checked' : '' }} />
                                  <span class="absolute inset-0 inline-flex items-center justify-center text-white opacity-0 peer-checked:opacity-100 transition-all">
                                    <i class="hgi hgi-stroke hgi-tick-02 text-[18px] leading-[18px]"></i>
                                  </span>
                                </span>
                              </span>
                              <span class="text-light-primary-text group-hover:text-primary transition-colors duration-300 ease-in-out">{{ $disc['label'] }}</span>
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
      {{-- end sidebar --}}

      {{-- ══════ RIGHT CONTENT ══════ --}}
      <div class="xl:col-span-9 lg:col-span-8 col-span-12 row-start-1 lg:row-start-auto relative">
        <div id="vm-loader-bar"></div>

        {{-- Toolbar with Search and Sorting --}}
        <div class="flex items-center justify-between pb-8 wow animate__animated animate__fadeInUp" data-wow-delay=".2s">
          <div class="flex items-center gap-x-4 shrink-0 justify-start">
            <div class="flex items-center gap-x-4">
              <button class="w-10 h-10 rounded-full inline-flex items-center justify-center btn-primary cursor-pointer">
                <i class="hgi hgi-stroke hgi-grid-view text-lg text-white"></i>
              </button>
            </div>
            <div class="lg:flex hidden">
              <p class="text-light-secondary-text">
                Showing <span id="vm-range-prefix">{{ $products->count() > 0 ? '1' : '0' }}</span>-<span id="vm-showing-count">{{ $products->count() }}</span> of <span id="vm-total-count">{{ $products->total() }}</span> results
              </p>
            </div>
          </div>
          {{-- Search & Sorting Container --}}
          <div class="flex items-center gap-4">
            <!-- Search Input Box -->
            <div class="relative w-full max-w-[280px]">
              <div class="input-group medium pl-4 py-1.5 pr-2.5 rounded-[100px] border border-gray-300 flex items-center gap-x-2 bg-white">
                <i class="hgi hgi-stroke hgi-search-01 text-lg text-gray-400"></i>
                <input
                  type="text"
                  id="vm-search-input"
                  class="form-control border-none focus:ring-0 p-0 text-sm leading-5 placeholder-gray-400 w-full"
                  placeholder="Search products..."
                  value="{{ $currentSearch }}"
                />
              </div>
            </div>
            <!-- Sorting -->
            <div class="relative min-w-[130px]">
              <select id="vm-sort" class="filter-select label">
                <option value="latest"     {{ $sort == 'latest'     ? 'selected' : '' }}>Latest</option>
                <option value="price_asc"  {{ $sort == 'price_asc'  ? 'selected' : '' }}>Price: Low to High</option>
                <option value="price_desc" {{ $sort == 'price_desc' ? 'selected' : '' }}>Price: High to Low</option>
                <option value="name_asc"   {{ $sort == 'name_asc'   ? 'selected' : '' }}>A-Z Order</option>
              </select>
              <label for="vm-sort" class="nice-select-label">Sorting</label>
            </div>
          </div>
        </div>

        {{-- Products grid --}}
        <div id="vm-products-grid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-6 pb-8">
          @forelse($products as $product)
            <div class="wow animate__animated animate__fadeInUp" data-wow-delay=".1s">
              @include('web.partials.product-card', ['product' => $product])
            </div>
          @empty
            <div class="vm-empty">
              <i class="hgi hgi-stroke hgi-package text-5xl text-gray-300 mb-4 block"></i>
              <p class="text-gray-500">No products found.</p>
            </div>
          @endforelse
        </div>

        {{-- Infinite scroll sentinel + spinner --}}
        <div id="vm-load-more-sentinel" class="flex items-center justify-center">
          <div class="vm-loading-spinner" id="vm-spinner" style="{{ $products->hasMorePages() ? '' : 'display:none;' }}">
            <span class="vm-spinner-dot"></span>
            <span class="vm-spinner-dot"></span>
            <span class="vm-spinner-dot"></span>
          </div>
        </div>

      </div>{{-- end right --}}
    </div>
  </div>
</div>



@endsection

@push('scripts')
<script>
(function () {
  const AJAX_URL  = '{{ route('vendor.products.ajax', $imvCode) }}';

  // State object
  window.state = {
    search:    '{{ addslashes($currentSearch) }}',
    sort:      '{{ $sort }}',
    price_min: {{ $currentMin }},
    price_max: {{ $currentMax ? $currentMax : 99999999 }},
    discount:  '{{ $currentDiscount }}',
    page:      1,
  };

  let loading  = false;
  let exhausted = {{ $products->hasMorePages() ? 'false' : 'true' }};

  // ── Global price filter helpers (called by inline onchange) ───
  window._priceMinOpts = [
      { v: '0',     l: 'Min' },
      { v: '10000', l: '₹10,000' },
      { v: '15000', l: '₹15,000' },
      { v: '20000', l: '₹20,000' },
      { v: '30000', l: '₹30,000' },
  ];
  window._priceMaxOpts = [
      { v: '10000', l: '₹10,000' },
      { v: '15000', l: '₹15,000' },
      { v: '20000', l: '₹20,000' },
      { v: '30000', l: '₹30,000' },
      { v: '',      l: '₹30,000+' },
  ];

  window.initPriceFilters = function() {
      var minSel = document.getElementById('price-min-select');
      var maxSel = document.getElementById('price-max-select');
      if (!minSel || !maxSel) return;

      var minVal = minSel.value;
      var maxVal = maxSel.value;

      // Filter Max options based on minVal
      var minNum = parseInt(minVal || 0);
      var prevMax = maxVal;
      maxSel.innerHTML = '';
      window._priceMaxOpts.forEach(function(o) {
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
      window._priceMinOpts.forEach(function(o) {
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

  window.onPriceMinChange = function(minVal) {
      var maxSel = document.getElementById('price-max-select');
      var minInput = document.getElementById('min_price_input');
      var maxInput = document.getElementById('max_price_input');
      if (!maxSel) return;

      // Rebuild max: only options strictly > minVal
      var prevMax = maxSel.value;
      maxSel.innerHTML = '';
      var minNum = parseInt(minVal || 0);
      window._priceMaxOpts.forEach(function(o) {
          if (o.v !== '' && parseInt(o.v) <= minNum) return;
          var el = document.createElement('option');
          el.value = o.v; el.textContent = o.l;
          if (o.v === prevMax) el.selected = true;
          maxSel.appendChild(el);
      });
      if (maxSel.selectedIndex < 0 && maxSel.options.length > 0) {
          maxSel.options[maxSel.options.length - 1].selected = true;
      }

      // Update hidden inputs and state
      window.state.price_min = parseInt(minVal || 0);
      window.state.price_max = maxSel.value === '' ? 99999999 : parseInt(maxSel.value || 0);
      if (minInput) minInput.value = minVal;
      if (maxInput) maxInput.value = maxSel.value;

      // Sync NiceSelect custom dropdowns
      if (window.jQuery && jQuery.fn.niceSelect) {
          jQuery('#price-min-select, #price-max-select').niceSelect('update');
      }

      resetAndLoad();
  }

  window.onPriceMaxChange = function(maxVal) {
      var minSel = document.getElementById('price-min-select');
      var minInput = document.getElementById('min_price_input');
      var maxInput = document.getElementById('max_price_input');
      if (!minSel) return;

      // Rebuild min: only options strictly < maxVal
      var prevMin = minSel.value;
      minSel.innerHTML = '';
      window._priceMinOpts.forEach(function(o) {
          if (maxVal !== '' && parseInt(o.v) >= parseInt(maxVal)) return;
          var el = document.createElement('option');
          el.value = o.v; el.textContent = o.l;
          if (o.v === prevMin) el.selected = true;
          minSel.appendChild(el);
      });
      if (minSel.selectedIndex < 0 && minSel.options.length > 0) {
          minSel.options[0].selected = true;
      }

      // Update hidden inputs and state
      window.state.price_min = parseInt(minSel.value || 0);
      window.state.price_max = maxVal === '' ? 99999999 : parseInt(maxVal || 0);
      if (minInput) minInput.value = minSel.value;
      if (maxInput) maxInput.value = maxVal;

      // Sync NiceSelect custom dropdowns
      if (window.jQuery && jQuery.fn.niceSelect) {
          jQuery('#price-min-select, #price-max-select').niceSelect('update');
      }

      resetAndLoad();
  }

  // ── Discount ──────────────────────────────────────────────────────
  document.querySelectorAll('input[name="vm_discount"]').forEach(function (r) {
    r.addEventListener('change', function () {
      window.state.discount = this.value;
      resetAndLoad();
    });
  });

  // ── Sort ──────────────────────────────────────────────────────────
  document.getElementById('vm-sort').addEventListener('change', function () {
    window.state.sort = this.value;
    resetAndLoad();
  });

  // ── Search ────────────────────────────────────────────────────────
  const searchInput = document.getElementById('vm-search-input');
  let searchTimeout = null;
  function doSearch() {
    window.state.search = searchInput.value.trim();
    resetAndLoad();
  }
  if (searchInput) {
    searchInput.addEventListener('input', function () {
      clearTimeout(searchTimeout);
      searchTimeout = setTimeout(doSearch, 400); // 400ms debounce
    });
  }

  // ── Clear All ─────────────────────────────────────────────────────
  const clearAllBtn = document.getElementById('vm-clear-all');
  if (clearAllBtn) {
    clearAllBtn.addEventListener('click', function () {
      window.state.search   = '';
      window.state.discount = '';
      window.state.price_min = 0;
      window.state.price_max = 99999999;
      if (searchInput) searchInput.value = '';
      document.querySelectorAll('input[name="vm_discount"]').forEach(r => r.checked = false);
      
      const minSel = document.getElementById('price-min-select');
      const maxSel = document.getElementById('price-max-select');
      if (minSel) { 
          minSel.innerHTML = '';
          window._priceMinOpts.forEach(function(o) {
              var el = document.createElement('option');
              el.value = o.v; el.textContent = o.l;
              minSel.appendChild(el);
          });
          minSel.value = '0'; 
      }
      if (maxSel) { 
          maxSel.innerHTML = '';
          window._priceMaxOpts.forEach(function(o) {
              var el = document.createElement('option');
              el.value = o.v; el.textContent = o.l;
              maxSel.appendChild(el);
          });
          maxSel.value = ''; 
      }
      if (window.jQuery && jQuery.fn.niceSelect) {
          jQuery('#price-min-select, #price-max-select').niceSelect('update');
      }

      resetAndLoad();
    });
  }

  // ── Reset price ─────────────────────────────────────────────────
  const priceReset = document.getElementById('price-range-reset');
  if (priceReset) {
      priceReset.addEventListener('click', function(e) {
          e.preventDefault();
          const minSel = document.getElementById('price-min-select');
          const maxSel = document.getElementById('price-max-select');
          if (minSel) { 
              minSel.innerHTML = '';
              window._priceMinOpts.forEach(function(o) {
                  var el = document.createElement('option');
                  el.value = o.v; el.textContent = o.l;
                  minSel.appendChild(el);
              });
              minSel.value = '0'; 
          }
          if (maxSel) { 
              maxSel.innerHTML = '';
              window._priceMaxOpts.forEach(function(o) {
                  var el = document.createElement('option');
                  el.value = o.v; el.textContent = o.l;
                  maxSel.appendChild(el);
              });
              maxSel.value = ''; 
          }
          window.state.price_min = 0;
          window.state.price_max = 99999999;
          if (window.jQuery && jQuery.fn.niceSelect) {
              jQuery('#price-min-select, #price-max-select').niceSelect('update');
          }
          resetAndLoad();
      });
  }

  // ── Discount Reset ──────────────────────────────────────────────
  document.querySelectorAll('.vm-reset-btn').forEach(function (btn) {
    btn.addEventListener('click', function () {
      if (this.dataset.target === 'discount') {
        window.state.discount = '';
        document.querySelectorAll('input[name="vm_discount"]').forEach(r => r.checked = false);
      }
      resetAndLoad();
    });
  });

  // ── AJAX load ─────────────────────────────────────────────────────
  function buildParams() {
    const p = new URLSearchParams();
    if (window.state.search)              p.set('search',    window.state.search);
    if (window.state.sort)                p.set('sort',      window.state.sort);
    if (window.state.price_min > 0)       p.set('price_min', window.state.price_min);
    if (window.state.price_max < 99999999) p.set('price_max', window.state.price_max);
    if (window.state.discount)            p.set('discount',  window.state.discount);
    p.set('page', window.state.page);
    return p;
  }

  function resetAndLoad() {
    window.state.page = 1;
    exhausted  = false;

    // Smooth Flipkart style filter: dim grid and show loader bar
    const grid = document.getElementById('vm-products-grid');
    if (grid) {
      grid.style.opacity = '0.5';
      grid.style.pointerEvents = 'none';
    }
    const loaderBar = document.getElementById('vm-loader-bar');
    if (loaderBar) {
      loaderBar.classList.add('loading');
    }

    loadPage(true);
  }

  function loadPage(isReset = false) {
    if (loading || (exhausted && !isReset)) return;
    loading = true;

    if (!isReset) {
      document.getElementById('vm-spinner').style.display = 'flex';
    }

    fetch(AJAX_URL + '?' + buildParams().toString(), {
      headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(function (data) {
      const grid = document.getElementById('vm-products-grid');
      const loaderBar = document.getElementById('vm-loader-bar');

      // Reset loading states
      if (grid) {
        grid.style.opacity = '1';
        grid.style.pointerEvents = 'auto';
      }
      if (loaderBar) {
        loaderBar.classList.remove('loading');
      }

      if (isReset) {
        grid.innerHTML = '';
      }

      if (window.state.page === 1 && !data.html.trim()) {
        grid.innerHTML = '<div class="vm-empty"><i class="hgi hgi-stroke hgi-package text-5xl text-gray-300 mb-4 block"></i><p class="text-gray-500">No products found.</p></div>';
      } else {
        const tmp = document.createElement('div');
        tmp.innerHTML = data.html;
        const children = Array.from(tmp.children);
        children.forEach(child => {
          child.classList.remove('wow');
          child.classList.add('animate__animated','animate__fadeIn');
          grid.appendChild(child);
        });
      }
      if (data.total !== undefined)
        document.getElementById('vm-total-count').textContent = data.total;
      const shown = grid.querySelectorAll('.universal-product-card').length;
      document.getElementById('vm-showing-count').textContent = shown;
      const rangePrefix = document.getElementById('vm-range-prefix');
      if (rangePrefix) {
        rangePrefix.textContent = shown > 0 ? '1' : '0';
      }

      if (data.has_more) {
        window.state.page++;
        document.getElementById('vm-spinner').style.display = 'flex';
      } else {
        exhausted = true;
        document.getElementById('vm-spinner').style.display = 'none';
      }
      loading = false;
    })
    .catch(function () {
      loading = false;
      const grid = document.getElementById('vm-products-grid');
      if (grid) {
        grid.style.opacity = '1';
        grid.style.pointerEvents = 'auto';
      }
      const loaderBar = document.getElementById('vm-loader-bar');
      if (loaderBar) {
        loaderBar.classList.remove('loading');
      }
      document.getElementById('vm-spinner').style.display = 'none';
    });
  }

  // ── Infinite scroll ───────────────────────────────────────────────
  if ('IntersectionObserver' in window) {
    new IntersectionObserver(function (entries) {
      if (entries[0].isIntersecting && !loading && !exhausted) loadPage();
    }, { rootMargin: '200px' }).observe(document.getElementById('vm-load-more-sentinel'));
  }

  // Initialize Price Dropdown states
  document.addEventListener('DOMContentLoaded', function() {
    window.initPriceFilters();
  });
  // Also call it immediately in case DOMContentLoaded has already fired
  if (document.readyState === 'interactive' || document.readyState === 'complete') {
    window.initPriceFilters();
  }

})();
</script>
@endpush