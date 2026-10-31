@extends('web.layouts.app')

@section('title', 'Our Verified Sellers — Indian MSME')
@section('meta_description', 'Partnering with trusted distributors, suppliers, and service providers to empower businesses across India.')

@push('styles')
  <style>
    /* Prevent nice-select from wrapping our Select2 category dropdown */
    .select-category-searchable+.nice-select {
      display: none !important;
    }

    /* Select2 custom styling inside the search pill */
    .select2-container--default .select2-selection--single {
      background-color: transparent !important;
      border: none !important;
      height: 44px !important;
      display: flex !important;
      align-items: center !important;
      box-shadow: none !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
      color: #071a41 !important;
      font-size: 12.5px !important;
      font-weight: 500 !important;
      font-family: 'Poppins', sans-serif !important;
      padding-left: 14px !important;
      padding-right: 28px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
      height: 44px !important;
      right: 8px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__clear {
      color: #475569 !important;
      font-size: 18px !important;
      font-weight: 700 !important;
      display: inline-flex !important;
      align-items: center !important;
      justify-content: center !important;
      height: 20px !important;
      width: 20px !important;
      line-height: 1 !important;
      margin-right: 6px !important;
      cursor: pointer !important;
      vertical-align: middle !important;
      position: relative !important;
      top: -1px !important;
    }

    .select2-container--default .select2-selection--single .select2-selection__clear:hover {
      color: #ef4444 !important;
    }

    /* Page Wrapper */
    .vendor-page-wrap {
      background-color: #ffffff;
      padding: 30px 0 60px 0;
      font-family: 'Poppins', sans-serif;
    }

    .vendor-container {
      max-width: 100% !important;
      width: 100% !important;
      margin: 0 auto;
      padding: 0 40px;
      box-sizing: border-box;
    }

    @media (max-width: 768px) {
      .vendor-container {
        padding: 0 16px;
      }
    }

    /* Breadcrumbs */
    .vendor-breadcrumb {
      display: flex;
      align-items: center;
      gap: 6px;
      font-size: 12px;
      color: #071a41;
      margin-bottom: 24px;
    }

    .vendor-breadcrumb a {
      color: #071a41;
      text-decoration: none;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .vendor-breadcrumb a:hover {
      color: #16a34a;
    }

    .vendor-breadcrumb-sep {
      color: #94a3b8;
      font-size: 11px;
    }

    .vendor-breadcrumb-active {
      color: #071a41;
      font-weight: 600;
    }

    /* Header Section */
    .vendor-header-section {
      text-align: center;
      max-width: 900px;
      margin: 0 auto 30px auto;
    }

    .vendor-title {
      font-size: 34px;
      font-weight: 800;
      color: #071a41;
      margin: 0 0 6px 0;
      letter-spacing: -0.5px;
    }

    .vendor-title-underline {
      width: 48px;
      height: 4px;
      background-color: #16a34a;
      border-radius: 99px;
      margin: 0 auto 12px auto;
    }

    .vendor-subtitle {
      font-size: 13.5px;
      color: #475569;
      line-height: 1.5;
      margin: 0;
      font-weight: 400;
    }

    /* Search & Filter Bar */
    .vendor-search-filter-row {
      max-width: 1150px;
      margin: 0 auto 36px auto;
      display: flex;
      align-items: center;
    }

    .vendor-search-pill {
      width: 100%;
      display: flex;
      align-items: center;
      background-color: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 14px;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
      overflow: hidden;
      height: 48px;
    }

    .vendor-search-input-box {
      flex: 1;
      display: flex;
      align-items: center;
      gap: 10px;
      padding: 0 18px;
      height: 100%;
    }

    .vendor-search-input-box i {
      color: #94a3b8;
      font-size: 17px;
      flex-shrink: 0;
    }

    .vendor-search-input-box input {
      width: 100%;
      border: none !important;
      outline: none !important;
      background: transparent !important;
      font-size: 13px !important;
      color: #071a41 !important;
      font-family: 'Poppins', sans-serif !important;
      padding: 0 !important;
      box-shadow: none !important;
    }

    .vendor-search-input-box input::placeholder {
      color: #94a3b8;
    }

    .vendor-search-divider {
      width: 1px;
      height: 24px;
      background-color: #e2e8f0;
      flex-shrink: 0;
    }

    .vendor-category-dropdown-box {
      width: 220px;
      flex-shrink: 0;
    }

    .vendor-filter-btn {
      display: flex;
      align-items: center;
      justify-content: center;
      width: 46px;
      height: 46px;
      padding: 0;
      background-color: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 14px;
      color: #071a41;
      cursor: pointer;
      box-shadow: 0 1px 3px rgba(0, 0, 0, 0.04);
      transition: all 0.2s ease;
      flex-shrink: 0;
    }

    .vendor-filter-btn:hover {
      background-color: #f8fafc;
      border-color: #cbd5e1;
    }

    /* Cards Outer Box & Grid (Matching Reference Image 1:1) */
    .vendor-cards-outer-box {
      background-color: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 20px;
      padding: 20px;
      margin-bottom: 28px;
    }

    .vendor-cards-grid {
      display: grid;
      grid-template-columns: repeat(5, 1fr);
      gap: 14px;
    }

    @media (max-width: 1400px) {
      .vendor-cards-grid {
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
      }
    }

    @media (max-width: 1024px) {
      .vendor-cards-grid {
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
      }
    }

    @media (max-width: 768px) {
      .vendor-cards-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
      }
    }

    @media (max-width: 500px) {
      .vendor-cards-grid {
        grid-template-columns: repeat(1, 1fr);
      }
    }

    .vendor-card {
      background-color: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 16px;
      padding: 14px 12px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      text-align: center;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
      transition: all 0.25s ease;
      cursor: pointer;
      height: 100%;
      min-height: 290px;
      box-sizing: border-box;
    }

    .vendor-card:hover {
      border-color: #cbd5e1;
      box-shadow: 0 10px 25px rgba(7, 26, 65, 0.08);
      transform: translateY(-3px);
    }

    .vendor-card-logo-box {
      background-color: #f8fafc;
      border: 1px solid #f1f5f9;
      border-radius: 12px;
      height: 100px;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 8px;
      margin-bottom: 10px;
      overflow: hidden;
    }

    .vendor-card-name {
      font-size: 13.5px;
      font-weight: 700;
      color: #071a41;
      margin: 0 0 4px 0;
      height: 38px;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
      text-overflow: ellipsis;
      line-height: 1.35;
      text-align: center;
    }

    .vendor-card-category {
      font-size: 11px;
      color: #64748b;
      margin: 0 0 4px 0;
      height: 16px;
      line-height: 16px;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
      font-weight: 400;
    }

    .vendor-card-imv-badge {
      display: inline-block;
      font-size: 10.5px;
      font-weight: 700;
      color: #16a34a;
      background-color: #f0fdf4;
      border: 1px solid #bbf7d0;
      border-radius: 6px;
      padding: 2px 7px;
      margin: 4px auto 10px auto;
      letter-spacing: 0.2px;
    }

    .vendor-card-footer {
      padding-top: 10px;
      border-top: 1px solid #f1f5f9;
      display: flex;
      align-items: center;
      justify-content: space-between;
      font-size: 11px;
      margin-bottom: 12px;
    }

    .vendor-card-products {
      font-weight: 600;
      color: #334155;
      display: flex;
      align-items: center;
      gap: 4px;
    }

    .vendor-card-products i {
      color: #94a3b8;
      font-size: 12px;
    }

    .vendor-card-rating {
      font-weight: 700;
      color: #1e293b;
      display: flex;
      align-items: center;
      gap: 3px;
    }

    .vendor-card-btn {
      display: block;
      width: 100%;
      padding: 9px 0;
      background-color: #071a41;
      color: #ffffff !important;
      font-weight: 600;
      font-size: 11.5px;
      border-radius: 10px;
      text-decoration: none;
      text-align: center;
      transition: background-color 0.2s ease;
      border: none;
    }

    .vendor-card-btn:hover {
      background-color: #0d285c;
      color: #ffffff !important;
    }

    /* DDC Subscribed Card Highlight */
    .vendor-card.is-ddc-subscribed {
      border: 1.5px solid #f59e0b;
      box-shadow: 0 4px 16px rgba(245, 158, 11, 0.12);
      background: linear-gradient(180deg, #fffdf8 0%, #ffffff 100%);
    }

    .vendor-card.is-ddc-subscribed:hover {
      border-color: #d97706;
      box-shadow: 0 10px 28px rgba(217, 119, 6, 0.2);
    }

    .vendor-ddc-badge {
      position: absolute;
      top: 10px;
      left: 10px;
      z-index: 10;
      background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
      color: #92400e;
      border: 1px solid #f59e0b;
      font-size: 10px;
      font-weight: 700;
      padding: 3px 8px;
      border-radius: 20px;
      display: inline-flex;
      align-items: center;
      gap: 4px;
      letter-spacing: 0.2px;
      box-shadow: 0 2px 5px rgba(245, 158, 11, 0.2);
    }

    .vendor-ddc-badge i {
      font-size: 11px;
      color: #d97706;
    }

    /* Quick Action Top Corner Catalog Download Button */
    .seller-quick-catalog-btn {
      position: absolute;
      top: 10px;
      right: 46px;
      z-index: 10;
      background: #fef2f2;
      border: 1px solid #fecaca;
      border-radius: 50%;
      width: 30px;
      height: 30px;
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      color: #dc2626;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.06);
      transition: all 0.2s;
      text-decoration: none;
    }

    .seller-quick-catalog-btn:hover {
      background: #dc2626;
      border-color: #dc2626;
      color: #ffffff !important;
      transform: scale(1.08);
      box-shadow: 0 3px 8px rgba(220, 38, 38, 0.25);
    }

    /* Card Footer Catalog Pill */
    .vendor-card-catalog-pill {
      display: inline-flex;
      align-items: center;
      gap: 4px;
      font-size: 11px;
      font-weight: 600;
      color: #dc2626 !important;
      text-decoration: none;
      padding: 3px 8px;
      background-color: #fef2f2;
      border: 1px solid #fecaca;
      border-radius: 6px;
      transition: all 0.2s ease;
    }

    .vendor-card-catalog-pill:hover {
      background-color: #dc2626;
      color: #ffffff !important;
      border-color: #dc2626;
    }

    /* Stats Section */
    .vendor-stats-container {
      max-width: 100% !important;
      width: 100% !important;
      margin: 0 auto;
      background-color: #f4f7fb;
      border: 1px solid #e3e8f0;
      border-radius: 16px;
      padding: 22px 32px;
      box-sizing: border-box;
    }

    .vendor-stats-grid {
      display: grid;
      grid-template-columns: repeat(4, 1fr);
      align-items: center;
    }

    @media (max-width: 768px) {
      .vendor-stats-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
      }
    }

    .vendor-stat-item {
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 12px;
      padding: 0 10px;
    }

    .vendor-stat-item+.vendor-stat-item {
      border-left: 1px solid #e2e8f0;
    }

    @media (max-width: 768px) {
      .vendor-stat-item+.vendor-stat-item {
        border-left: none;
      }
    }

    .vendor-stat-icon {
      width: 42px;
      height: 42px;
      border-radius: 50%;
      display: flex;
      align-items: center;
      justify-content: center;
      flex-shrink: 0;
      font-size: 18px;
    }

    .vendor-stat-icon.blue {
      background-color: #dbeafe;
      color: #2563eb;
    }

    .vendor-stat-icon.green {
      background-color: #d1fae5;
      color: #10b981;
    }

    .vendor-stat-icon.orange {
      background-color: #ffedd5;
      color: #f97316;
    }

    .vendor-stat-icon.purple {
      background-color: #f3e8ff;
      color: #a855f7;
    }

    .vendor-stat-number {
      font-size: 19px;
      font-weight: 800;
      color: #071a41;
      line-height: 1.1;
      margin-bottom: 2px;
    }

    .vendor-stat-label {
      font-size: 11.5px;
      color: #64748b;
      font-weight: 500;
    }

    /* Custom Web Pagination Styling */
    .custom-web-pagination {
      display: flex;
      justify-content: center;
      align-items: center;
      margin-top: 28px;
      margin-bottom: 20px;
      width: 100%;
    }
    .custom-web-pagination .pagination-list {
      display: inline-flex;
      align-items: center;
      gap: 6px;
      list-style: none;
      padding: 0;
      margin: 0;
      flex-wrap: wrap;
    }
    .custom-web-pagination li a,
    .custom-web-pagination li span {
      display: inline-flex;
      align-items: center;
      justify-content: center;
      min-width: 38px;
      height: 38px;
      padding: 0 12px;
      font-size: 13px;
      font-weight: 600;
      color: #071a41;
      background-color: #ffffff;
      border: 1px solid #e2e8f0;
      border-radius: 10px;
      text-decoration: none;
      transition: all 0.2s ease;
      box-shadow: 0 1px 3px rgba(0,0,0,0.03);
    }
    .custom-web-pagination li a:hover {
      background-color: #071a41;
      color: #ffffff !important;
      border-color: #071a41;
    }
    .custom-web-pagination li.active span {
      background-color: #16a34a;
      color: #ffffff !important;
      border-color: #16a34a;
      box-shadow: 0 2px 6px rgba(22, 163, 74, 0.3);
    }
    .custom-web-pagination li.disabled span {
      color: #cbd5e1;
      background-color: #f8fafc;
      border-color: #f1f5f9;
      cursor: not-allowed;
    }
  </style>
@endpush

@section('content')
  <div class="vendor-page-wrap" style="padding-top: 0 !important;">

    {{-- Premium Hero Header Section (#071933 Blue Background) --}}
    <div class="vendor-hero-wrapper" style="background: #071933 !important; padding: 55px 20px 50px 20px; text-align: center; color: #ffffff !important; margin-bottom: 35px;">
      <div style="max-width: 950px; margin: 0 auto;">
        
        <h1 style="color: #ffffff !important; font-size: 32px; font-weight: 800; margin-top: 0; margin-bottom: 10px; line-height: 1.3;">
          Our Verified Sellers
        </h1>
        
        <p style="color: rgba(255, 255, 255, 0.9) !important; font-size: 14.5px; max-width: 680px; margin: 0 auto 28px auto; line-height: 1.5;">
          Partnering with trusted distributors, suppliers, and service providers to empower businesses across India.
        </p>

        {{-- Search & Filter Bar Inside Hero --}}
        <form id="vendor-filter-form" method="GET" action="{{ route('vendors') }}" class="vendor-search-filter-row"
          style="max-width: 850px; margin: 0 auto;" onsubmit="event.preventDefault();">

          {{-- Pill Search Box --}}
          <div class="vendor-search-pill" style="background-color: #ffffff; border-radius: 999px; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15); height: 48px;">

            <div class="vendor-search-input-box">
              <i class="hgi hgi-stroke hgi-search-01"></i>
              <input type="text" name="search" id="vendor-search-input" value="{{ request('search') }}"
                placeholder="Search sellers by name, category or location...">
              <button type="button" id="clear-search-btn" style="border:none; background:none; cursor:pointer;"
                class="{{ request('search') ? '' : 'hidden' }}">
                <i class="hgi hgi-stroke hgi-cancel-01" style="color:#ef4444;"></i>
              </button>
            </div>

            <div class="vendor-search-divider"></div>

            <div class="vendor-category-dropdown-box">
              <select name="category" id="vendor-category-select" class="select-category-searchable">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                  <option value="{{ $cat->slug }}" @selected(request('category') === $cat->slug)>{{ $cat->name }}</option>
                @endforeach
              </select>
            </div>

          </div>
        </form>

      </div>
    </div>

    <div class="vendor-container">

      {{-- 4. Vendor Cards Grid Container --}}
      <div id="vendor-grid-container">
        @include('web.partials.vendor-grid')
      </div>

      <!-- Smart Controlled On-Scroll Progress & Loading Container -->
      @if($sellers->count() > 0)
        <div id="vendor-scroll-container" style="margin-top: 32px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding-bottom: 32px;">
          @php
            $visibleCount = min($sellers->count(), $sellers->total());
            $progressPercent = $sellers->total() > 0 ? min(100, round(($visibleCount / $sellers->total()) * 100)) : 100;
          @endphp
          <div style="font-size: 12px; color: #64748b; font-weight: 600; margin-bottom: 6px;" id="vendor-scroll-count-text">
            Showing <span id="vendor-visible-count">{{ $visibleCount }}</span> of <span id="vendor-total-count">{{ $sellers->total() }}</span> sellers
          </div>
          <div style="width: 200px; height: 6px; background-color: #e2e8f0; border-radius: 9999px; overflow: hidden; margin-bottom: 12px;">
            <div id="vendor-progress-bar-inner" style="background-color: #0284c7; height: 100%; transition: width 0.3s ease; width: {{ $progressPercent }}%;"></div>
          </div>

          <div id="vendor-scroll-loader" style="display: none !important; padding: 8px 0; align-items: center; justify-content: center; gap: 8px; color: #475569; font-size: 12px; font-weight: 600;">
            <span>Loading more sellers...</span>
          </div>

          <div id="vendor-end-text" style="font-size: 12px; color: #94a3b8; font-weight: 500; margin: 8px 0; display: {{ $sellers->hasMorePages() ? 'none' : 'block' }};">
            You've viewed all {{ $sellers->total() }} sellers
          </div>

          <div id="vendor-scroll-sentinel" style="width: 100%; height: 4px;"></div>
        </div>
      @endif

      {{-- 5. Bottom Stats Strip --}}
      <div class="vendor-stats-container">
        <div class="vendor-stats-grid">

          <div class="vendor-stat-item">
            <div class="vendor-stat-icon blue">
              <i class="hgi hgi-stroke hgi-store-01"></i>
            </div>
            <div>
              <div class="vendor-stat-number">{{ number_format($stats['total_vendors'] ?? $sellers->total()) }}</div>
              <div class="vendor-stat-label">Total Verified Sellers</div>
            </div>
          </div>

          <div class="vendor-stat-item">
            <div class="vendor-stat-icon green">
              <i class="hgi hgi-stroke hgi-package"></i>
            </div>
            <div>
              <div class="vendor-stat-number">{{ number_format($stats['total_products'] ?? 0) }}</div>
              <div class="vendor-stat-label">Products Listed</div>
            </div>
          </div>

          <div class="vendor-stat-item">
            <div class="vendor-stat-icon orange">
              <i class="hgi hgi-stroke hgi-shopping-bag-01"></i>
            </div>
            <div>
              <div class="vendor-stat-number">{{ number_format($stats['total_categories'] ?? 0) }}</div>
              <div class="vendor-stat-label">Categories</div>
            </div>
          </div>

          <div class="vendor-stat-item">
            <div class="vendor-stat-icon purple">
              <i class="hgi hgi-stroke hgi-maps-circle-01"></i>
            </div>
            <div>
              <div class="vendor-stat-number">{{ number_format($stats['total_states'] ?? 0) }}</div>
              <div class="vendor-stat-label">States Covered</div>
            </div>
          </div>

        </div>
      </div>

    </div>
  </div>

  {{-- Copy Toast --}}
  <div id="sellerCopyToast"
    style="position: fixed; bottom: 24px; left: 50%; transform: translate(-50%, 80px); background: #0f172a; color: #ffffff; padding: 10px 20px; border-radius: 12px; font-size: 13px; font-weight: 500; z-index: 99999; transition: transform 0.3s ease; pointer-events: none; box-shadow: 0 4px 14px rgba(0,0,0,0.2);">
    🔗 Link copied to clipboard!
  </div>
@endsection

@push('scripts')
  <script>
    function toggleSellerShare(btn) {
      const menu = btn.closest('div').querySelector('.seller-share-menu');
      const isOpen = !menu.classList.contains('hidden');
      document.querySelectorAll('.seller-share-menu').forEach(m => m.classList.add('hidden'));
      if (!isOpen) menu.classList.remove('hidden');
    }

    document.addEventListener('click', function (e) {
      if (!e.target.closest('[onclick*="toggleSellerShare"]') && !e.target.closest('.seller-share-menu')) {
        document.querySelectorAll('.seller-share-menu').forEach(m => m.classList.add('hidden'));
      }
    });

    function copySellerLink(url) {
      navigator.clipboard.writeText(url).then(() => {
        const t = document.getElementById('sellerCopyToast');
        if (t) {
          t.style.transform = 'translate(-50%, 0)';
          setTimeout(() => {
            t.style.transform = 'translate(-50%, 80px)';
          }, 2500);
        }
      });
      document.querySelectorAll('.seller-share-menu').forEach(m => m.classList.add('hidden'));
    }

    $(document).ready(function () {
      // Destroy any existing nice-select on our dropdown first
      const rawSelect = document.getElementById('vendor-category-select');
      if (rawSelect && rawSelect.nextElementSibling && rawSelect.nextElementSibling.classList.contains('nice-select')) {
        rawSelect.nextElementSibling.remove();
      }

      // Initialize Select2 on the category dropdown
      const $catSelect = $('#vendor-category-select').select2({
        placeholder: 'All Categories',
        allowClear: true,
        width: '100%'
      });

      let searchTimeout = null;

      $catSelect.on('change', function () {
        fetchVendors();
      });

      $('#vendor-search-input').on('input', function () {
        const val = $(this).val();
        $('#clear-search-btn').toggleClass('hidden', !val);

        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function () {
          fetchVendors();
        }, 400);
      });

      $('#clear-search-btn').on('click', function () {
        $('#vendor-search-input').val('');
        $(this).addClass('hidden');
        fetchVendors();
      });

      $('#apply-filter-btn').on('click', function () {
        fetchVendors();
      });

      function fetchVendors() {
        const category = $('#vendor-category-select').val();
        const search = $('#vendor-search-input').val();

        const $gridContainer = $('#vendor-grid-container');
        $gridContainer.css({ opacity: 0.5, 'pointer-events': 'none' });

        const url = new URL(window.location.href);
        if (category) {
          url.searchParams.set('category', category);
        } else {
          url.searchParams.delete('category');
        }

        if (search) {
          url.searchParams.set('search', search);
        } else {
          url.searchParams.delete('search');
        }
        url.searchParams.delete('page');

        $.ajax({
          url: url.toString(),
          type: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          },
          success: function (response) {
            vendorPage = response.current_page || 1;
            vendorLastPage = response.last_page || 1;
            $gridContainer.html(response.html);
            $gridContainer.css({ opacity: 1, 'pointer-events': 'auto' });
            window.history.pushState(null, '', url.toString());

            var visibleCount = $('.vendor-cards-grid .vendor-card').length;
            $('#vendor-visible-count').text(visibleCount);
            $('#vendor-total-count').text(response.total || visibleCount);
            if (response.total > 0) {
              var percent = Math.min(100, Math.round((visibleCount / response.total) * 100));
              $('#vendor-progress-bar-inner').css('width', percent + '%');
            }
            if (vendorPage >= vendorLastPage) {
              $('#vendor-end-text').show();
            } else {
              $('#vendor-end-text').hide();
            }
          },
          error: function (xhr) {
            console.error('Error fetching vendors:', xhr);
            $gridContainer.css({ opacity: 1, 'pointer-events': 'auto' });
          }
        });
      }

      // ── Seamless On-Scroll Seller Loading ──
      var vendorPage = parseInt("{{ $sellers->currentPage() }}") || 1;
      var vendorLastPage = parseInt("{{ $sellers->lastPage() }}") || 1;
      var vendorIsLoading = false;

      function handleVendorScroll() {
        if (vendorIsLoading || vendorPage >= vendorLastPage) return;

        var sentinel = document.getElementById('vendor-scroll-sentinel');
        if (!sentinel) return;

        var rect = sentinel.getBoundingClientRect();
        var isNearBottom = rect.top <= (window.innerHeight || document.documentElement.clientHeight) + 350;

        if (isNearBottom) {
          loadNextVendorPage();
        }
      }

      function loadNextVendorPage() {
        if (vendorIsLoading || vendorPage >= vendorLastPage) return;
        vendorIsLoading = true;

        var loader = document.getElementById('vendor-scroll-loader');
        if (loader) loader.style.setProperty('display', 'flex', 'important');

        var nextPage = vendorPage + 1;
        var url = new URL(window.location.href);
        url.searchParams.set('page', nextPage);

        $.ajax({
          url: url.toString(),
          type: 'GET',
          headers: { 'X-Requested-With': 'XMLHttpRequest' },
          success: function (response) {
            if (response && response.html) {
              var tempDiv = $('<div>').html(response.html);
              var newCards = tempDiv.find('.vendor-cards-grid').html();
              if (newCards) {
                $('.vendor-cards-grid').append(newCards);
              }

              vendorPage = response.current_page || nextPage;
              vendorLastPage = response.last_page || vendorLastPage;

              var visibleCount = $('.vendor-cards-grid .vendor-card').length;
              $('#vendor-visible-count').text(visibleCount);
              $('#vendor-total-count').text(response.total);

              if (response.total > 0) {
                var percent = Math.min(100, Math.round((visibleCount / response.total) * 100));
                $('#vendor-progress-bar-inner').css('width', percent + '%');
              }

              // Update address bar URL state
              var newUrl = new URL(window.location.href);
              newUrl.searchParams.set('page', vendorPage);
              window.history.pushState(null, '', newUrl.toString());
            }

            if (loader) loader.style.setProperty('display', 'none', 'important');
            vendorIsLoading = false;

            if (vendorPage >= vendorLastPage) {
              $('#vendor-end-text').show();
            }
          },
          error: function() {
            if (loader) loader.style.setProperty('display', 'none', 'important');
            vendorIsLoading = false;
          }
        });
      }

      window.addEventListener('scroll', handleVendorScroll, { passive: true });
    });
  </script>
@endpush