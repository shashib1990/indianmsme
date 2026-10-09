@extends('web.layouts.app')

@php
  $parentProfile = $parentSeller->sellerProfile;
  $parentImv     = $parentProfile->seller_id ?? 'N/A';
  $parentName    = $parentProfile->company_name ?? $parentSeller->name;
@endphp

@section('title', 'Verified Sub-Sellers of ' . $parentName . ' — Indian MSME Store')
@section('meta_description', 'Discover verified sub-sellers representing ' . $parentName . ' across India.')

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

    /* Cards Outer Box & Grid */
    .vendor-cards-outer-box {
      background-color: #f8fafc;
      border: 1px solid #e2e8f0;
      border-radius: 20px;
      padding: 20px;
      margin-bottom: 28px;
    }

    .vendor-cards-grid {
      display: grid;
      grid-template-columns: repeat(6, 1fr);
      gap: 12px;
    }

    @media (max-width: 1300px) {
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
      padding: 12px;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      text-align: center;
      box-shadow: 0 1px 4px rgba(0, 0, 0, 0.03);
      transition: all 0.25s ease;
      cursor: pointer;
      height: 100%;
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
      height: 105px;
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
      margin: 0 0 2px 0;
      white-space: nowrap;
      overflow: hidden;
      text-overflow: ellipsis;
    }

    .vendor-card-category {
      font-size: 11px;
      color: #64748b;
      margin: 0 0 4px 0;
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
      margin: 2px 0 10px 0;
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
          Verified Sub-Sellers
        </h1>
        
        <p style="color: rgba(255, 255, 255, 0.9) !important; font-size: 14.5px; max-width: 680px; margin: 0 auto 28px auto; line-height: 1.5;">
          Discover trusted sub-sellers representing <strong style="color: #ffffff;">{{ $parentName }}</strong> across India.
        </p>

        {{-- Search & Filter Bar Inside Hero --}}
        <form id="sub-seller-filter-form" method="GET" action="{{ route('vendor.sub-sellers', $parentImv) }}"
          class="vendor-search-filter-row" style="max-width: 850px; margin: 0 auto;" onsubmit="event.preventDefault();">

          {{-- Pill Search Box --}}
          <div class="vendor-search-pill" style="background-color: #ffffff; border-radius: 999px; box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15); height: 48px;">

            <div class="vendor-search-input-box">
              <i class="hgi hgi-stroke hgi-search-01"></i>
              <input type="text" name="search" id="sub-seller-search-input" value="{{ request('search') }}"
                placeholder="Search sub-sellers by name, company or IMV code...">
              <button type="button" id="clear-search-btn" style="border:none; background:none; cursor:pointer;"
                class="{{ request('search') ? '' : 'hidden' }}">
                <i class="hgi hgi-stroke hgi-cancel-01" style="color:#ef4444;"></i>
              </button>
            </div>

            <div class="vendor-search-divider"></div>

            <div class="vendor-category-dropdown-box">
              <select name="category" id="sub-seller-category-select" class="select-category-searchable">
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

      {{-- 4. Sub-Seller Cards Grid Container --}}
      <div id="sub-seller-grid-container">
        @include('web.partials.sub-seller-grid')
      </div>

      {{-- 5. Bottom Stats Strip --}}
      <div class="vendor-stats-container">
        <div class="vendor-stats-grid">

          <div class="vendor-stat-item">
            <div class="vendor-stat-icon blue">
              <i class="hgi hgi-stroke hgi-user-group"></i>
            </div>
            <div>
              <div class="vendor-stat-number">{{ number_format($stats['total_sub_sellers'] ?? $subSellers->total()) }}</div>
              <div class="vendor-stat-label">Total Sub-Sellers</div>
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
      const rawSelect = document.getElementById('sub-seller-category-select');
      if (rawSelect && rawSelect.nextElementSibling && rawSelect.nextElementSibling.classList.contains('nice-select')) {
        rawSelect.nextElementSibling.remove();
      }

      // Initialize Select2 on the category dropdown
      const $catSelect = $('#sub-seller-category-select').select2({
        placeholder: 'All Categories',
        allowClear: true,
        width: '100%'
      });

      let searchTimeout = null;

      $catSelect.on('change', function () {
        fetchSubSellers();
      });

      $('#sub-seller-search-input').on('input', function () {
        const val = $(this).val();
        $('#clear-search-btn').toggleClass('hidden', !val);

        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(function () {
          fetchSubSellers();
        }, 400);
      });

      $('#clear-search-btn').on('click', function () {
        $('#sub-seller-search-input').val('');
        $(this).addClass('hidden');
        fetchSubSellers();
      });

      function fetchSubSellers() {
        const category = $('#sub-seller-category-select').val();
        const search = $('#sub-seller-search-input').val();

        const $gridContainer = $('#sub-seller-grid-container');
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
            $gridContainer.html(response.html);
            $gridContainer.css({ opacity: 1, 'pointer-events': 'auto' });
            window.history.pushState(null, '', url.toString());
          },
          error: function (xhr) {
            console.error('Error fetching sub-sellers:', xhr);
            $gridContainer.css({ opacity: 1, 'pointer-events': 'auto' });
          }
        });
      }

      $(document).on('click', '#sub-seller-grid-container .blog-pagination a', function (e) {
        e.preventDefault();
        const pageUrl = $(this).attr('href');
        if (!pageUrl) return;

        const $gridContainer = $('#sub-seller-grid-container');
        $gridContainer.css({ opacity: 0.5, 'pointer-events': 'none' });

        $.ajax({
          url: pageUrl,
          type: 'GET',
          headers: {
            'X-Requested-With': 'XMLHttpRequest'
          },
          success: function (response) {
            $gridContainer.html(response.html);
            $gridContainer.css({ opacity: 1, 'pointer-events': 'auto' });
            window.history.pushState(null, '', pageUrl);

            $('html, body').animate({
              scrollTop: $("#sub-seller-filter-form").offset().top - 80
            }, 300);
          },
          error: function (xhr) {
            console.error('Error paginating sub-sellers:', xhr);
            $gridContainer.css({ opacity: 1, 'pointer-events': 'auto' });
          }
        });
      });
    });
  </script>
@endpush