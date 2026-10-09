@extends('web.layouts.app')

@section('title', $category->name . ' | Indian MSME')

@section('content')
  <!-- Main Category Page (Pure White Background) -->
  <div style="background-color: #ffffff !important;" class="min-h-screen py-5 text-left">
    <div class="container mx-auto px-4">

      <!-- ================= TOP HERO HEADER SECTION ================= -->
      <div class="mb-6">
        <!-- Breadcrumb Top Nav -->
        <div class="flex items-center gap-2 text-xs text-[#64748b] mb-4 font-normal">
          <a href="{{ route('home') }}" class="hover:text-[#0f172a] transition-colors">Home</a>
          <span class="text-[#94a3b8] text-[11px]">&rsaquo;</span>
          <a href="{{ route('shop') }}" class="hover:text-[#0f172a] transition-colors">Shop</a>
          <span class="text-[#94a3b8] text-[11px]">&rsaquo;</span>
          <span class="font-bold text-[#0f172a]">{{ $category->name }}</span>
        </div>

        <!-- Main Hero Flex Container -->
        <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-5">

          <!-- Left: Perfect Circle Avatar + Title & Tight Subtitle -->
          <div class="flex items-center gap-4">
            <div style="width: 80px !important; height: 80px !important; min-width: 80px !important; min-height: 80px !important; max-width: 80px !important; max-height: 80px !important; border-radius: 50% !important; overflow: hidden !important; background-color: #f8fafc !important; border: 1px solid #cbd5e1 !important; flex-shrink: 0 !important; display: flex !important; align-items: center !important; justify-content: center !important;">
              @php $catImg = $category->image_url ?: asset('admin-assets/images/IndianMSME.png'); @endphp
              <img src="{{ $catImg }}" alt="{{ $category->name }}" style="width: 80px !important; height: 80px !important; min-width: 80px !important; min-height: 80px !important; object-fit: cover !important; border-radius: 50% !important;" onerror="this.onerror=null; this.src='{{ asset('admin-assets/images/IndianMSME.png') }}';" />
            </div>
            <div style="display: flex !important; flex-direction: column !important; justify-content: center !important;">
              <h1 style="font-size: 26px !important; font-weight: 800 !important; color: #0b192c !important; line-height: 1.1 !important; margin: 0 0 4px 0 !important; font-family: 'Inter', sans-serif !important;">
                {{ $category->name }}
              </h1>
              <p style="font-size: 13px !important; font-weight: 400 !important; color: #475569 !important; line-height: 1.3 !important; margin: 0 !important; max-width: 500px !important;">
                {{ $category->description ?? 'Explore authentic ' . $category->name . ' products from India\'s MSME manufacturers and artisans.' }}
              </p>
            </div>
          </div>

          <!-- Right: Trust Badges Cream Box -->
          <div style="background-color: #faf1e8 !important; border: none !important; border-radius: 10px !important; padding: 12px 20px !important; display: flex !important; align-items: center !important; gap: 20px !important; flex-shrink: 0 !important;">
            
            <!-- 1. Quality -->
            <div style="display: flex !important; align-items: center !important; gap: 10px !important;">
              <div style="width: 32px !important; height: 32px !important; border-radius: 50% !important; background-color: #dcfce7 !important; display: flex !important; align-items: center !important; justify-content: center !important; flex-shrink: 0 !important;">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#16a34a" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                  <path d="m9 12 2 2 4-4"/>
                </svg>
              </div>
              <div style="font-size: 11px !important; font-weight: 700 !important; color: #0f172a !important; line-height: 1.2 !important; text-align: left !important;">
                100% Quality<br />
                <span style="color: #64748b !important; font-weight: 400 !important;">Assured Products</span>
              </div>
            </div>

            <!-- 2. Made in India -->
            <div style="display: flex !important; align-items: center !important; gap: 10px !important;">
              <div style="width: 28px !important; height: 20px !important; border-radius: 3px !important; overflow: hidden !important; flex-shrink: 0 !important; display: flex !important; align-items: center !important; justify-content: center !important; border: 1px solid #cbd5e1 !important;">
                <svg width="28" height="20" viewBox="0 0 900 600">
                  <rect width="900" height="200" fill="#FF9933"/>
                  <rect y="200" width="900" height="200" fill="#FFFFFF"/>
                  <rect y="400" width="900" height="200" fill="#128807"/>
                  <circle cx="450" cy="300" r="80" fill="none" stroke="#000080" stroke-width="12"/>
                  <circle cx="450" cy="300" r="16" fill="#000080"/>
                </svg>
              </div>
              <div style="font-size: 11px !important; font-weight: 700 !important; color: #0f172a !important; line-height: 1.2 !important; text-align: left !important;">
                Made in India<br />
                <span style="color: #64748b !important; font-weight: 400 !important;">Proudly Supporting MSMEs</span>
              </div>
            </div>

            <!-- 3. Secure Payments -->
            <div style="display: flex !important; align-items: center !important; gap: 10px !important;">
              <div style="width: 32px !important; height: 32px !important; border-radius: 50% !important; background-color: #ffffff !important; border: 1px solid #cbd5e1 !important; display: flex !important; align-items: center !important; justify-content: center !important; flex-shrink: 0 !important;">
                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#0f172a" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                  <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                  <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                </svg>
              </div>
              <div style="font-size: 11px !important; font-weight: 700 !important; color: #0f172a !important; line-height: 1.2 !important; text-align: left !important;">
                Secure Payments<br />
                <span style="color: #64748b !important; font-weight: 400 !important;">100% Safe & Secure</span>
              </div>
            </div>

            <!-- 4. Dashed Border Support MSMEs Inner Pill Box -->
            <div style="border: 1.5px dashed #dfa887 !important; border-radius: 8px !important; padding: 6px 14px !important; background-color: transparent !important; flex-shrink: 0 !important; text-align: left !important;">
              <div style="display: flex !important; align-items: center !important; gap: 4px !important; font-size: 11px !important; font-weight: 800 !important; color: #0f172a !important;">
                <span>Support MSMEs</span>
                <span style="color: #ef4444 !important; font-size: 11px !important;">❤️</span>
              </div>
              <div style="font-size: 10px !important; color: #64748b !important; font-weight: 400 !important;">Buy Local, Grow Local</div>
            </div>

          </div>

        </div>
      </div>


      <!-- ================= MAIN CONTENT FLEX LAYOUT ================= -->
      <div class="flex flex-col lg:flex-row gap-6 items-start">

        <!-- ================= LEFT SIDEBAR (OPTIMIZED 260px WIDTH) ================= -->
        <div style="width: 260px !important; min-width: 260px !important; max-width: 260px !important; flex-shrink: 0 !important; display: flex !important; flex-direction: column !important; gap: 20px !important; background: transparent !important;">
          
          <!-- CARD 1: Categories Card -->
          <div style="background-color: #ffffff !important; border: 1px solid #e2e8f0 !important; border-radius: 10px !important; padding: 18px 20px !important; box-shadow: 0 1px 3px rgba(0,0,0,0.02) !important;">
            <div style="display: flex !important; align-items: center !important; justify-content: space-between !important; margin-bottom: 14px !important;">
              <h3 style="font-size: 14px !important; font-weight: 800 !important; color: #0f172a !important; margin: 0 !important; font-family: 'Inter', sans-serif !important;">
                Categories
              </h3>
              <span style="color: #94a3b8 !important; font-size: 16px !important; font-weight: 700 !important; cursor: pointer !important;">−</span>
            </div>

            <!-- Active All Category Pill -->
            <a href="{{ route('category.show', $category->slug) }}"
              style="display: block !important; background-color: #edf5ff !important; color: #0284c7 !important; border-radius: 6px !important; padding: 10px 14px !important; font-size: 12px !important; font-weight: 700 !important; margin-bottom: 10px !important; text-decoration: none !important;">
              All {{ $category->name }}
            </a>

            <!-- Subcategories Clean List with Child Categories Accordion -->
            <div style="display: flex !important; flex-direction: column !important; gap: 4px !important;">
              @foreach($category->subCategories as $subCat)
                @php
                  $isSubActive = $selectedSubCategory && $selectedSubCategory->id == $subCat->id;
                  $hasChild = $subCat->childCategories && $subCat->childCategories->isNotEmpty();
                @endphp
                
                <div>
                  <div style="display: flex !important; align-items: center !important; justify-content: space-between !important; padding: 8px 12px !important; border-radius: 6px !important; font-size: 12px !important; font-weight: {{ $isSubActive ? '700' : '500' }} !important; color: {{ $isSubActive ? '#0284c7' : '#475569' }} !important; background-color: {{ $isSubActive ? '#edf5ff' : 'transparent' }} !important;">
                    <a href="{{ route('category.show', ['slug' => $category->slug, 'sub_category' => $subCat->id]) }}"
                      style="color: inherit !important; text-decoration: none !important; flex-grow: 1 !important;">
                      {{ $subCat->name }}
                    </a>
                    @if($hasChild)
                      <span onclick="toggleSubChild('sub_child_{{ $subCat->id }}', this)"
                        style="color: #94a3b8 !important; font-size: 15px !important; font-weight: 700 !important; cursor: pointer !important; padding: 0 4px !important; user-select: none !important;"
                        title="Toggle Child Categories">
                        {{ $isSubActive ? '−' : '+' }}
                      </span>
                    @else
                      <span style="color: #cbd5e1 !important; font-size: 13px !important; font-weight: 400 !important;">+</span>
                    @endif
                  </div>

                  <!-- Child Categories Accordion Dropdown -->
                  @if($hasChild)
                    <div id="sub_child_{{ $subCat->id }}" style="display: {{ $isSubActive ? 'flex' : 'none' }} !important; flex-direction: column !important; gap: 2px !important; padding-left: 18px !important; margin-top: 3px !important; margin-bottom: 5px !important;">
                      @foreach($subCat->childCategories as $childCat)
                        @php $isChildActive = $selectedChildCategory && $selectedChildCategory->id == $childCat->id; @endphp
                        <a href="{{ route('category.show', ['slug' => $category->slug, 'sub_category' => $subCat->id, 'child_category' => $childCat->id]) }}"
                          style="display: block !important; padding: 5px 10px !important; border-radius: 6px !important; font-size: 11.5px !important; font-weight: {{ $isChildActive ? '700' : '400' }} !important; color: {{ $isChildActive ? '#0284c7' : '#64748b' }} !important; background-color: {{ $isChildActive ? '#e0f2fe' : 'transparent' }} !important; text-decoration: none !important;">
                          &bull; {{ $childCat->name }}
                        </a>
                      @endforeach
                    </div>
                  @endif
                </div>
              @endforeach
            </div>
          </div>

          <!-- CARD 2: Filter By Card (Separate White Box Below Card 1) -->
          <div style="background-color: #ffffff !important; border: 1px solid #e2e8f0 !important; border-radius: 10px !important; padding: 18px 20px !important; box-shadow: 0 1px 3px rgba(0,0,0,0.02) !important;">
            <form method="GET" action="{{ url()->current() }}">
              @if(request('sub_category')) <input type="hidden" name="sub_category" value="{{ request('sub_category') }}"> @endif
              @if(request('child_category')) <input type="hidden" name="child_category" value="{{ request('child_category') }}"> @endif

              <!-- Filter By Header -->
              <div style="display: flex !important; align-items: center !important; justify-content: space-between !important; margin-bottom: 16px !important;">
                <h3 style="font-size: 14px !important; font-weight: 800 !important; color: #0f172a !important; margin: 0 !important; font-family: 'Inter', sans-serif !important;">
                  Filter By
                </h3>
                <span style="color: #94a3b8 !important; font-size: 16px !important; font-weight: 700 !important; cursor: pointer !important;">−</span>
              </div>

              <!-- Price Range Section (Shop Page Style Min/Max Dropdowns) -->
              <style>
                /* Force flat rectangular styling on price nice-select wrappers and hide native select */
                .cat-price-box-container select {
                  display: none !important;
                }
                .cat-price-box-container .nice-select {
                  border-radius: 0px !important;
                  border: 1px solid #cbd5e1 !important;
                  background-color: #ffffff !important;
                  height: 32px !important;
                  line-height: 30px !important;
                  padding-left: 8px !important;
                  padding-right: 22px !important;
                  width: 100% !important;
                  box-shadow: none !important;
                  outline: none !important;
                  font-size: 12px !important;
                  font-weight: 600 !important;
                  color: #0f172a !important;
                  display: flex !important;
                  align-items: center !important;
                  float: none !important;
                }
                .cat-price-box-container .nice-select span.current {
                  font-size: 12px !important;
                  font-weight: 600 !important;
                  color: #0f172a !important;
                  line-height: 30px !important;
                }
                .cat-price-box-container .nice-select:after {
                  right: 8px !important;
                  margin-top: -3px !important;
                }
                .cat-price-box-container .nice-select .list {
                  border-radius: 0px !important;
                  border: 1px solid #cbd5e1 !important;
                  box-shadow: 0 4px 10px rgba(0,0,0,0.1) !important;
                  background-color: #ffffff !important;
                  z-index: 1000 !important;
                }
                .cat-price-box-container .nice-select .list .option {
                  font-size: 12px !important;
                  padding-left: 8px !important;
                  padding-right: 8px !important;
                  min-height: 28px !important;
                  line-height: 28px !important;
                }
              </style>

              <div style="margin-bottom: 16px !important;">
                <div style="display: flex !important; align-items: center !important; justify-content: space-between !important; margin-bottom: 8px !important;">
                  <span style="font-size: 12px !important; font-weight: 700 !important; color: #0f172a !important;">Price Range</span>
                  @if(request()->filled('min_price') || request()->filled('max_price'))
                    <a href="javascript:void(0)" onclick="resetCategoryPriceFilter()" style="font-size: 11px !important; font-weight: 600 !important; color: #0d6efd !important; text-decoration: none !important;">Reset</a>
                  @else
                    <span style="color: #94a3b8 !important; font-size: 14px !important; font-weight: 700 !important;">−</span>
                  @endif
                </div>

                <input type="hidden" name="min_price" id="cat_min_price_input" value="{{ request('min_price', 0) }}">
                <input type="hidden" name="max_price" id="cat_max_price_input" value="{{ request('max_price', '') }}">

                <div class="cat-price-box-container" style="display: flex !important; align-items: center !important; gap: 8px !important; margin-top: 4px !important;">
                  <!-- Min Box -->
                  <div style="flex: 1 !important; min-width: 0 !important;">
                    <span style="font-size: 10px !important; font-weight: 600 !important; color: #64748b !important; text-transform: uppercase !important; display: block !important; margin-bottom: 3px !important;">Min</span>
                    <select id="cat-price-min-select" onchange="onCatPriceMinChange(this.value)"
                      style="font-size: 12px !important; font-weight: 600 !important; color: #0f172a !important; background: #ffffff !important; border: 1px solid #cbd5e1 !important; border-radius: 0px !important; outline: none !important; width: 100% !important; height: 32px !important; padding: 0 6px !important; cursor: pointer !important; -webkit-appearance: auto !important; appearance: auto !important;">
                      <option value="0" {{ request('min_price', 0) == 0 ? 'selected' : '' }}>Min</option>
                      <option value="10000" {{ request('min_price') == 10000 ? 'selected' : '' }}>₹10,000</option>
                      <option value="15000" {{ request('min_price') == 15000 ? 'selected' : '' }}>₹15,000</option>
                      <option value="20000" {{ request('min_price') == 20000 ? 'selected' : '' }}>₹20,000</option>
                      <option value="30000" {{ request('min_price') == 30000 ? 'selected' : '' }}>₹30,000</option>
                    </select>
                  </div>

                  <span style="font-size: 12px !important; color: #94a3b8 !important; font-weight: 500 !important; margin-top: 14px !important; flex-shrink: 0 !important;">to</span>

                  <!-- Max Box -->
                  <div style="flex: 1 !important; min-width: 0 !important;">
                    <span style="font-size: 10px !important; font-weight: 600 !important; color: #64748b !important; text-transform: uppercase !important; display: block !important; margin-bottom: 3px !important;">Max</span>
                    <select id="cat-price-max-select" onchange="onCatPriceMaxChange(this.value)"
                      style="font-size: 12px !important; font-weight: 600 !important; color: #0f172a !important; background: #ffffff !important; border: 1px solid #cbd5e1 !important; border-radius: 0px !important; outline: none !important; width: 100% !important; height: 32px !important; padding: 0 6px !important; cursor: pointer !important; -webkit-appearance: auto !important; appearance: auto !important;">
                      <option value="10000" {{ request('max_price') == 10000 ? 'selected' : '' }}>₹10,000</option>
                      <option value="15000" {{ request('max_price') == 15000 ? 'selected' : '' }}>₹15,000</option>
                      <option value="20000" {{ request('max_price') == 20000 ? 'selected' : '' }}>₹20,000</option>
                      <option value="30000" {{ request('max_price') == 30000 ? 'selected' : '' }}>₹30,000</option>
                      <option value="" {{ (!request()->filled('max_price') || request('max_price') > 30000) ? 'selected' : '' }}>₹30,000+</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Divider Line -->
              <div style="border-top: 1px solid #f1f5f9 !important; margin: 16px 0 !important;"></div>

              <!-- Brand Section -->
              <div>
                <div style="display: flex !important; align-items: center !important; justify-content: space-between !important; margin-bottom: 10px !important;">
                  <span style="font-size: 12px !important; font-weight: 700 !important; color: #0f172a !important;">Brand</span>
                  <span style="color: #94a3b8 !important; font-size: 14px !important; font-weight: 700 !important; cursor: pointer !important;">−</span>
                </div>

                @if(isset($brands) && $brands->isNotEmpty())
                  <div style="display: flex !important; flex-direction: column !important; gap: 8px !important; max-height: 180px !important; overflow-y: auto !important; padding-right: 4px !important;">
                    @foreach($brands as $b)
                      <label style="display: flex !important; align-items: center !important; gap: 8px !important; font-size: 12px !important; color: #475569 !important; cursor: pointer !important; font-weight: 500 !important;">
                        <input type="checkbox" name="brand" value="{{ $b->id }}" {{ request('brand') == $b->id ? 'checked' : '' }} onchange="this.form.submit()"
                          style="width: 14px !important; height: 14px !important; border-radius: 4px !important; border: 1px solid #cbd5e1 !important; accent-color: #0d6efd !important;" />
                        <span>{{ $b->name }}</span>
                      </label>
                    @endforeach
                  </div>
                @endif
              </div>

            </form>
          </div>

        </div>


        <!-- ================= RIGHT CONTENT AREA (EXPANDS FLEX GROW) ================= -->
        <div style="flex-grow: 1 !important; min-width: 0 !important; display: flex !important; flex-direction: column !important; gap: 24px !important;">

          <!-- Subcategories Cards Row -->
          @if($category->subCategories->isNotEmpty())
            <div class="relative">
              <div style="display: flex !important; align-items: center !important; justify-content: space-between !important; margin-bottom: 14px !important;">
                <h3 style="font-size: 18px !important; font-weight: 800 !important; color: #0b192c !important; margin: 0 !important;">
                  Subcategories
                </h3>
                <a href="{{ route('category.show', $category->slug) }}"
                  style="font-size: 12px !important; font-weight: 700 !important; color: #0d6efd !important; text-decoration: none !important; display: inline-flex !important; align-items: center !important; gap: 4px !important;">
                  <span>View All {{ $category->name }} Products</span>
                  <span>&rsaquo;</span>
                </a>
              </div>

              @php
                $displaySubCats = $category->subCategories;
              @endphp

              <!-- Fixed-Width Compact Subcategory Cards -->
              <div style="display: flex !important; gap: 14px !important; flex-wrap: wrap !important; align-items: stretch !important;">
                @foreach($displaySubCats as $subCat)
                  @php
                    $isSubActive = $selectedSubCategory && $selectedSubCategory->id == ($subCat->id ?? null);
                    $subImg = $subCat->image_url ?: asset('admin-assets/images/IndianMSME.png');
                    $prodCount = $subCat->products_count ?? 0;
                    $targetUrl = $subCat->id ? route('category.show', ['slug' => $category->slug, 'sub_category' => $subCat->id]) : route('category.show', $category->slug);
                  @endphp
                  <a href="{{ $targetUrl }}"
                    style="width: 140px !important; min-width: 140px !important; max-width: 140px !important; background-color: #f8fafc !important; border-radius: 10px !important; padding: 10px 8px !important; text-align: center !important; text-decoration: none !important; transition: all 0.2s ease !important; cursor: pointer !important;">
                    <div style="height: 95px !important; border-radius: 8px !important; overflow: hidden !important; margin-bottom: 8px !important; display: flex !important; align-items: center !important; justify-content: center !important; padding: 4px !important; background-color: #ffffff !important;">
                      <img src="{{ $subImg }}" alt="{{ $subCat->name }}" style="max-height: 100% !important; max-width: 100% !important; object-fit: contain !important;"
                        onerror="this.onerror=null; this.src='{{ asset('admin-assets/images/IndianMSME.png') }}';" />
                    </div>
                    <div style="font-size: 12px !important; font-weight: 700 !important; color: #0f172a !important; text-overflow: ellipsis !important; overflow: hidden !important; white-space: nowrap !important;">
                      {{ $subCat->name }}
                    </div>
                    <div style="font-size: 11px !important; color: #64748b !important; font-weight: 400 !important; margin-top: 2px !important;">
                      ({{ number_format($prodCount) }} Products)
                    </div>
                  </a>
                @endforeach
              </div>
            </div>
          @endif


          <!-- Products Section -->
          <div>
            <!-- Toolbar -->
            <div style="display: flex !important; flex-wrap: wrap !important; align-items: center !important; justify-content: space-between !important; gap: 16px !important; margin-bottom: 20px !important;">
              <div>
                <h3 style="font-size: 18px !important; font-weight: 800 !important; color: #0b192c !important; margin: 0 !important;">
                  Products in {{ $selectedChildCategory ? $selectedChildCategory->name : ($selectedSubCategory ? $selectedSubCategory->name : $category->name) }}
                </h3>
                <p style="font-size: 12px !important; color: #64748b !important; font-weight: 400 !important; margin: 2px 0 0 0 !important;">
                  Showing 1-{{ $products->count() }} of {{ $products->total() }} products
                </p>
              </div>

              <!-- Controls (Compact Sort By, Grid View Icons Removed) -->
              <div style="display: flex !important; align-items: center !important; flex-shrink: 0 !important;">
                <form method="GET" action="{{ url()->current() }}" id="sortForm">
                  @if(request('sub_category')) <input type="hidden" name="sub_category" value="{{ request('sub_category') }}"> @endif
                  @if(request('child_category')) <input type="hidden" name="child_category" value="{{ request('child_category') }}"> @endif
                  @if(request('brand')) <input type="hidden" name="brand" value="{{ request('brand') }}"> @endif
                  <select name="sort" onchange="document.getElementById('sortForm').submit()"
                    style="font-size: 11px !important; font-weight: 500 !important; background-color: #ffffff !important; border: 1px solid #cbd5e1 !important; border-radius: 6px !important; padding: 4px 10px !important; color: #1e293b !important; outline: none !important; cursor: pointer !important; height: 32px !important; min-width: 130px !important;">
                    <option value="popularity" {{ request('sort') == 'popularity' ? 'selected' : '' }}>Sort by: Popularity</option>
                    <option value="latest" {{ request('sort') == 'latest' ? 'selected' : '' }}>Sort by: Latest</option>
                    <option value="price_low_high" {{ request('sort') == 'price_low_high' ? 'selected' : '' }}>Price: Low to High</option>
                    <option value="price_high_low" {{ request('sort') == 'price_high_low' ? 'selected' : '' }}>Price: High to Low</option>
                  </select>
                </form>
              </div>
            </div>

            <!-- Products Grid Using Project's Untouched Universal Product Card (Tight Flex Row Layout) -->
            @if($products->count() > 0)
              <div id="category-products-grid" style="display: flex !important; flex-wrap: wrap !important; gap: 14px !important; align-items: stretch !important;">
                @foreach($products as $product)
                  <div style="width: 225px !important; min-width: 225px !important; max-width: 225px !important; flex-shrink: 0 !important;">
                    @include('web.partials.product-card', ['product' => $product])
                  </div>
                @endforeach
              </div>

              <!-- Flipkart / Amazon Style Smart On-Scroll Progress & Loading Container -->
              <div id="smart-scroll-container" class="mt-8 flex flex-col items-center justify-center text-center">
                @php
                  $visibleCount = min($products->count(), $products->total());
                  $progressPercent = $products->total() > 0 ? min(100, round(($visibleCount / $products->total()) * 100)) : 100;
                @endphp
                <div class="text-xs text-slate-500 font-semibold mb-1.5" id="smart-scroll-count-text">
                  Showing <span id="smart-visible-count">{{ $visibleCount }}</span> of <span id="smart-total-count">{{ $products->total() }}</span> products
                </div>
                <div class="w-48 h-1.5 bg-slate-200 rounded-full overflow-hidden mb-3">
                  <div id="smart-progress-bar-inner" class="bg-primary h-full transition-all duration-300" style="width: {{ $progressPercent }}%;"></div>
                </div>

                <div id="smart-scroll-loader" style="display: none !important;" class="py-2 flex items-center gap-2 text-slate-600 text-xs font-semibold">
                  <span class="spinner-border spinner-border-sm text-primary" role="status"></span>
                  <span>Loading more products...</span>
                </div>

                @if($products->hasMorePages())
                  <div id="smart-manual-load-wrapper" class="my-2" style="display: none !important;">
                    <button type="button" onclick="triggerManualLoadNextPage()" 
                      class="btn btn-sm btn-outline-primary rounded-pill px-4 py-2 fw-bold text-xs shadow-sm">
                      <i class="bi bi-plus-circle me-1"></i> Load More Products
                    </button>
                  </div>
                @else
                  <div id="smart-end-text" class="text-xs text-slate-400 font-medium my-2">
                    You've viewed all {{ $products->total() }} products
                  </div>
                @endif

                <div id="smart-scroll-sentinel" class="w-100 h-1"></div>
              </div>
            @else
              <div class="py-12 px-4 text-center" style="display: flex !important; flex-direction: column !important; align-items: center !important; justify-content: center !important;">
                <div style="width: 80px !important; height: 80px !important; border-radius: 50% !important; background-color: #f1f5f9 !important; display: flex !important; align-items: center !important; justify-content: center !important; margin-bottom: 16px !important; flex-shrink: 0 !important;">
                  <svg width="44" height="44" viewBox="0 0 24 24" fill="none" stroke="#94a3b8" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/>
                    <path d="m3.3 7 8.7 5 8.7-5"/>
                    <path d="M12 22V12"/>
                  </svg>
                </div>
                <h3 class="text-base font-bold text-slate-800">No products found under this category</h3>
                <p class="text-xs text-slate-500 mt-1">Try resetting filters or explore all products.</p>
                <a href="{{ route('category.show', $category->slug) }}"
                  class="inline-block mt-4 text-xs font-bold text-white px-5 py-2.5 rounded-lg transition-all"
                  style="background-color: #0d6efd !important;">
                  View All {{ $category->name }} Products
                </a>
              </div>
            @endif

          </div>

        </div>

      </div>

    </div>
  </div>

  <script>
    function toggleSubChild(id, element) {
      const target = document.getElementById(id);
      if (!target) return;
      if (target.style.display === 'none' || target.style.display === '') {
        target.style.setProperty('display', 'flex', 'important');
        element.textContent = '−';
      } else {
        target.style.setProperty('display', 'none', 'important');
        element.textContent = '+';
      }
    }

    var _catPriceMinOpts = [
      { v: '0', l: 'Min' },
      { v: '10000', l: '₹10,000' },
      { v: '15000', l: '₹15,000' },
      { v: '20000', l: '₹20,000' },
      { v: '30000', l: '₹30,000' }
    ];
    var _catPriceMaxOpts = [
      { v: '10000', l: '₹10,000' },
      { v: '15000', l: '₹15,000' },
      { v: '20000', l: '₹20,000' },
      { v: '30000', l: '₹30,000' },
      { v: '', l: '₹30,000+' }
    ];

    function onCatPriceMinChange(minVal) {
      var maxSel = document.getElementById('cat-price-max-select');
      var minInput = document.getElementById('cat_min_price_input');
      var maxInput = document.getElementById('cat_max_price_input');
      if (!maxSel) return;

      var prevMax = maxSel.value;
      maxSel.innerHTML = '';
      var minNum = parseInt(minVal || 0);

      _catPriceMaxOpts.forEach(function(o) {
        if (o.v !== '' && parseInt(o.v) <= minNum) return;
        var el = document.createElement('option');
        el.value = o.v;
        el.textContent = o.l;
        if (o.v === prevMax) el.selected = true;
        maxSel.appendChild(el);
      });

      if (maxSel.selectedIndex < 0 && maxSel.options.length > 0) {
        maxSel.options[maxSel.options.length - 1].selected = true;
      }

      if (minInput) minInput.value = minVal;
      if (maxInput) maxInput.value = maxSel.value;

      if (maxSel.form) maxSel.form.submit();
    }

    function onCatPriceMaxChange(maxVal) {
      var minSel = document.getElementById('cat-price-min-select');
      var minInput = document.getElementById('cat_min_price_input');
      var maxInput = document.getElementById('cat_max_price_input');
      if (!minSel) return;

      var prevMin = minSel.value;
      minSel.innerHTML = '';

      _catPriceMinOpts.forEach(function(o) {
        if (maxVal !== '' && parseInt(o.v) >= parseInt(maxVal)) return;
        var el = document.createElement('option');
        el.value = o.v;
        el.textContent = o.l;
        if (o.v === prevMin) el.selected = true;
        minSel.appendChild(el);
      });

      if (minSel.selectedIndex < 0 && minSel.options.length > 0) {
        minSel.options[0].selected = true;
      }

      if (minInput) minInput.value = minSel.value;
      if (maxInput) maxInput.value = maxVal;

      if (minSel.form) minSel.form.submit();
    }

    function resetCategoryPriceFilter() {
      var minInput = document.getElementById('cat_min_price_input');
      var maxInput = document.getElementById('cat_max_price_input');
      if (minInput) minInput.value = '0';
      if (maxInput) maxInput.value = '';
      if (minInput && minInput.form) minInput.form.submit();
    }

    // ── Seamless On-Scroll Product Loading ──
    var catCurrentPage = parseInt("{{ $products->currentPage() }}") || 1;
    var catLastPage = parseInt("{{ $products->lastPage() }}") || 1;
    var catIsFetching = false;

    function handleCategoryScroll() {
      if (catIsFetching || catCurrentPage >= catLastPage) return;

      var sentinel = document.getElementById('smart-scroll-sentinel');
      if (!sentinel) return;

      var rect = sentinel.getBoundingClientRect();
      var isNearBottom = rect.top <= (window.innerHeight || document.documentElement.clientHeight) + 350;

      if (isNearBottom) {
        loadNextCategoryPage();
      }
    }

    function triggerManualLoadNextPage() {
      loadNextCategoryPage();
    }

    function loadNextCategoryPage() {
      if (catIsFetching || catCurrentPage >= catLastPage) return;
      catIsFetching = true;

      var loader = document.getElementById('smart-scroll-loader');
      var manualBtnWrapper = document.getElementById('smart-manual-load-wrapper');
      if (loader) loader.style.setProperty('display', 'flex', 'important');
      if (manualBtnWrapper) manualBtnWrapper.style.setProperty('display', 'none', 'important');

      var nextPage = catCurrentPage + 1;
      var url = new URL(window.location.href);
      url.searchParams.set('page', nextPage);
      url.searchParams.set('ajax', '1');

      fetch(url.toString(), {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
      })
      .then(function(r) { return r.json(); })
      .then(function(data) {
        if (data && data.html) {
          var grid = document.getElementById('category-products-grid');
          if (grid) {
            grid.insertAdjacentHTML('beforeend', data.html);
            var visibleCount = grid.children.length;
            var visibleElem = document.getElementById('smart-visible-count');
            var progressElem = document.getElementById('smart-progress-bar-inner');
            if (visibleElem) visibleElem.textContent = visibleCount;
            if (progressElem && data.total > 0) {
              var percent = Math.min(100, Math.round((visibleCount / data.total) * 100));
              progressElem.style.width = percent + '%';
            }
          }
          catCurrentPage = data.current_page;
          catLastPage = data.last_page;

          // Push state URL so address bar reflects page number
          var newUrl = new URL(window.location.href);
          newUrl.searchParams.set('page', catCurrentPage);
          newUrl.searchParams.delete('ajax');
          window.history.pushState(null, '', newUrl.toString());
        }

        if (loader) loader.style.setProperty('display', 'none', 'important');
        catIsFetching = false;

        if (catCurrentPage >= catLastPage) {
          if (manualBtnWrapper) manualBtnWrapper.style.setProperty('display', 'none', 'important');
          var endText = document.getElementById('smart-end-text');
          if (endText) endText.style.setProperty('display', 'block', 'important');
        }
      })
      .catch(function() {
        if (loader) loader.style.setProperty('display', 'none', 'important');
        catIsFetching = false;
      });
    }

    window.addEventListener('scroll', handleCategoryScroll, { passive: true });
  </script>
@endsection
