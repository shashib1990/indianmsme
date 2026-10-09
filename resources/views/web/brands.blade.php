@extends('web.layouts.app')

@section('title', 'Indian MSME Store — Explore Brands')

@section('content')
<style>
  /* Native CSS Grid to bypass purged Tailwind classes */
  #brands-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(0, 1fr));
    gap: 12px;
  }
  
  @media (min-width: 640px) {
    #brands-grid {
      grid-template-columns: repeat(5, minmax(0, 1fr));
      gap: 16px;
    }
  }
  
  @media (min-width: 768px) {
    #brands-grid {
      grid-template-columns: repeat(6, minmax(0, 1fr));
    }
  }
  
  @media (min-width: 1024px) {
    #brands-grid {
      grid-template-columns: repeat(8, minmax(0, 1fr));
    }
  }

  /* Premium Card Styles */
  .brand-card-inner {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 12px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    text-decoration: none;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    height: 100%;
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.02);
  }
  
  @media (min-width: 640px) {
    .brand-card-inner {
      padding: 20px;
    }
  }
  
  .brand-card-inner:hover {
    border-color: #06172e;
    transform: translateY(-5px);
    box-shadow: 0 12px 24px rgba(7, 25, 51, 0.12);
  }

  .brand-logo-wrap {
    width: 55px;
    height: 55px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f9fafb;
    border-radius: 12px;
    padding: 6px;
    margin-bottom: 8px;
    transition: background-color 0.3s;
    overflow: hidden;
  }
  
  @media (min-width: 640px) {
    .brand-logo-wrap {
      width: 80px;
      height: 80px;
      padding: 10px;
      margin-bottom: 12px;
    }
  }
  
  .brand-card-inner:hover .brand-logo-wrap {
    background: rgba(7, 25, 51, 0.06);
  }

  .brand-logo-img {
    max-width: 100%;
    max-height: 100%;
    object-fit: contain;
    transition: transform 0.3s;
  }
  
  .brand-card-inner:hover .brand-logo-img {
    transform: scale(1.08);
  }

  .brand-placeholder-txt {
    width: 100%;
    height: 100%;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #ffffff;
    font-size: 20px;
    font-weight: 800;
    box-shadow: inset 0 2px 4px rgba(0,0,0,0.1);
  }
  
  @media (min-width: 640px) {
    .brand-placeholder-txt {
      font-size: 28px;
    }
  }

  .brand-name-txt {
    font-size: 11px;
    font-weight: 700;
    color: #374151;
    text-align: center;
    transition: color 0.3s;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    width: 100%;
  }
  
  @media (min-width: 640px) {
    .brand-name-txt {
      font-size: 13px;
    }
  }
  
  .brand-card-inner:hover .brand-name-txt {
    color: #06172e;
  }

  /* Alphabet Quick Filter Button Styles */
  .alpha-btn {
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    border-radius: 50%;
    font-size: 13px;
    font-weight: 700;
    color: #4b5563;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    background: transparent;
    cursor: pointer;
    border: 1px solid #f3f4f6;
    user-select: none;
    flex-shrink: 0;
  }
  
  .alpha-btn:hover {
    background: #eef2f6;
    color: #06172e;
    border-color: #06172e;
    transform: scale(1.1);
  }
  
  .alpha-btn.active {
    background: #06172e;
    color: #ffffff;
    border-color: #06172e;
    box-shadow: 0 6px 12px rgba(7, 25, 51, 0.2);
  }
  
  .alpha-btn[data-letter="all"] {
    width: auto;
    padding: 0 12px;
    border-radius: 9999px;
  }

  /* Hero Banner Styled with Flat Borderless Design */
  .brand-hero-wrapper {
    background: #071933 !important;
    padding: 60px 20px;
    border-radius: 0px !important; /* Removed border radius */
    border: none !important; /* Removed borders */
    margin-top: 0px;
    margin-bottom: 40px;
    color: #ffffff !important;
    text-align: center;
    position: relative;
    box-shadow: none !important;
    overflow: hidden;
  }
  
  .brand-hero-wrapper h1 {
    color: #ffffff !important;
    font-size: 32px;
    font-weight: 800;
    margin-top: 0;
    margin-bottom: 12px;
    line-height: 1.3;
  }
  
  .brand-hero-wrapper p {
    color: rgba(255, 255, 255, 0.9) !important;
    font-size: 15px;
    max-width: 650px;
    margin: 0 auto;
    line-height: 1.5;
  }
  
  .brand-hero-wrapper nav {
    color: rgba(255, 255, 255, 0.7) !important;
    margin-bottom: 12px;
    font-weight: 600;
    font-size: 13px;
    text-transform: uppercase;
    letter-spacing: 1px;
  }
  
  .brand-hero-wrapper nav a {
    color: rgba(255, 255, 255, 0.7) !important;
    text-decoration: none;
    transition: color 0.2s;
  }
  
  .brand-hero-wrapper nav a:hover {
    color: #ffffff !important;
  }

  /* Unified Single-Row Search, Alphabet & Counter Toolbar */
  .filter-search-bar {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 16px;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 99px;
    padding: 8px 16px;
    margin-bottom: 40px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03);
  }
  
  .results-counter {
    font-size: 13px;
    font-weight: 700;
    color: #4b5563;
    white-space: nowrap;
    padding-left: 8px;
  }
  
  .results-counter span {
    color: #06172e;
  }

  .alphabet-scroll-container {
    display: flex;
    align-items: center;
    gap: 6px;
    overflow-x: auto;
    white-space: nowrap;
    scrollbar-width: none; /* Firefox */
    -ms-overflow-style: none;  /* IE and Edge */
    flex: 1;
    padding: 2px 0;
  }
  
  .alphabet-scroll-container::-webkit-scrollbar {
    display: none; /* Chrome, Safari and Opera */
  }

  .search-input-wrapper {
    position: relative;
    display: flex;
    align-items: center;
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 99px;
    padding: 6px 14px;
    width: 240px;
    transition: all 0.3s;
  }
  
  .search-input-wrapper:focus-within {
    border-color: #06172e;
    background: #ffffff;
    box-shadow: 0 0 0 3px rgba(7, 25, 51, 0.1);
  }

  .search-input-wrapper i {
    color: #9ca3af;
    font-size: 16px;
    margin-right: 6px;
  }

  .search-input-wrapper input {
    border: none !important;
    outline: none !important;
    box-shadow: none !important;
    background: transparent;
    padding: 0 !important;
    margin: 0 !important;
    font-size: 13px;
    color: #1f2937;
    width: 100%;
  }

  /* Responsive styling for Mobile */
  @media (max-width: 768px) {
    .filter-search-bar {
      flex-direction: column;
      border-radius: 20px;
      padding: 16px;
      align-items: stretch;
      gap: 12px;
    }
    
    .search-input-wrapper {
      width: 100%;
    }
    
    .results-counter {
      padding-left: 0;
      text-align: center;
    }
  }

  /* Bottom spacing to prevent sticking to footer */
  .brands-section-wrapper {
    margin-bottom: 120px !important;
    padding-bottom: 60px !important;
  }
</style>

  {{-- ── Premium Hero Header ── --}}
  <div class="brand-hero-wrapper">
    <div style="position: relative; z-index: 2;">
      <h1>
        Empowering India's Finest Brands
      </h1>
      <p>
        Connect directly with trusted MSMEs, certified sellers, and household names delivering quality goods to your doorstep.
      </p>
    </div>
  </div>

  {{-- ── Main Unified Toolbar & Grid Area ── --}}
  <section class="brands-section-wrapper">
    <div class="container">
      
      {{-- Single-Row Unified Filter & Search Bar --}}
      <div class="filter-search-bar">
        
        {{-- Results count (Left) --}}
        <div class="results-counter">
          Showing <span id="visible-brands-count">{{ count($brands) }}</span> active brands
        </div>
        
        {{-- Alphabet scroll filter (Center) --}}
        <div class="alphabet-scroll-container">
          <button class="alpha-btn active" data-letter="all">All Brands</button>
          @foreach(range('A', 'Z') as $char)
            <button class="alpha-btn" data-letter="{{ strtolower($char) }}">{{ $char }}</button>
          @endforeach
        </div>
        
        {{-- Search input (Right) --}}
        <div class="search-input-wrapper">
          <i class="hgi hgi-stroke hgi-search-01"></i>
          <input
            type="text"
            id="brand-search-input"
            placeholder="Search by brand name..."
            autocomplete="off"
          />
        </div>
        
      </div>

      {{-- Brands Grid --}}
      <div id="brands-grid">
        @forelse($brands as $brand)
          @php
            $defaultLogo = \App\Models\SystemSetting::getVal('website_header_logo', \App\Models\SystemSetting::getVal('panel_logo', asset('admin-assets/images/IndianMSME.png')));
            $logoUrl = !empty($brand->logo)
              ? (filter_var($brand->logo, FILTER_VALIDATE_URL) ? $brand->logo : asset('storage/' . $brand->logo))
              : $defaultLogo;
            $shopUrl = route('shop', [
                'brand' => $brand->id, 
                'pincode' => request('pincode') ?? session('user_pincode')
            ]);
          @endphp
          
          <div class="brand-card-item" data-name="{{ strtolower($brand->name) }}">
            <a href="{{ $shopUrl }}" class="brand-card-inner group">
              
              {{-- Brand Logo Container --}}
              <div class="brand-logo-wrap">
                <img
                  src="{{ $logoUrl }}"
                  alt="{{ $brand->name }}"
                  class="brand-logo-img"
                  loading="lazy"
                  onerror="this.onerror=null; this.src='{{ $defaultLogo }}';"
                />
              </div>
              
              {{-- Brand Name --}}
              <span class="brand-name-txt">
                {{ $brand->name }}
              </span>
            </a>
          </div>
        @empty
          <div class="col-span-full py-16 text-center w-full">
            <i class="hgi hgi-stroke hgi-package text-5xl text-gray-300 mb-4 block"></i>
            <p class="text-gray-500 font-medium">No active brands found at this time.</p>
          </div>
        @endforelse
      </div>

      {{-- No search results notice --}}
      <div id="no-brands-found" class="hidden py-20 text-center w-full">
        <i class="hgi hgi-stroke hgi-search-01 text-5xl text-gray-300 mb-4 block"></i>
        <p class="text-gray-500 font-medium text-lg mb-2">No brands match your selection</p>
        <p class="text-gray-400 text-sm">Try choosing another letter or adjust your search term.</p>
      </div>

    </div>
  </section>

  {{-- ── Client side instant dynamic double filtering script ── --}}
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const searchInput = document.getElementById('brand-search-input');
      const alphaButtons = document.querySelectorAll('.alpha-btn');
      const cards = document.querySelectorAll('.brand-card-item');
      const noResults = document.getElementById('no-brands-found');
      const visibleCount = document.getElementById('visible-brands-count');

      let currentLetter = 'all';
      let searchQuery = '';

      function filterBrands() {
        let matchedCount = 0;

        cards.forEach(card => {
          const name = card.getAttribute('data-name');
          const firstLetter = name.charAt(0);
          
          const matchesLetter = (currentLetter === 'all' || firstLetter === currentLetter);
          const matchesSearch = (searchQuery === '' || name.includes(searchQuery));

          if (matchesLetter && matchesSearch) {
            card.style.display = 'block';
            matchedCount++;
          } else {
            card.style.display = 'none';
          }
        });

        visibleCount.textContent = matchedCount;

        if (matchedCount === 0) {
          noResults.classList.remove('hidden');
        } else {
          noResults.classList.add('hidden');
        }
      }

      if (searchInput) {
        searchInput.addEventListener('input', function () {
          searchQuery = this.value.trim().toLowerCase();
          filterBrands();
        });
      }

      alphaButtons.forEach(btn => {
        btn.addEventListener('click', function () {
          alphaButtons.forEach(b => b.classList.remove('active'));
          this.classList.add('active');
          currentLetter = this.getAttribute('data-letter');
          filterBrands();
        });
      });
    });
  </script>
@endsection
