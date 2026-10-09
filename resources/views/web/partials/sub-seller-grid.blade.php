@if($subSellers->isEmpty())
  <div style="grid-column: 1 / -1; padding: 60px 20px; text-align: center; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; margin-bottom: 24px;">
    <i class="hgi hgi-stroke hgi-store-01" style="font-size: 44px; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
    <p style="color: #64748b; font-size: 14px; font-weight: 500; margin: 0;">No sub sellers found matching your criteria.</p>
  </div>
@else

  <div class="vendor-cards-outer-box">
    <div class="vendor-cards-grid">
      @foreach($subSellers as $seller)
        @php
          $profile     = $seller->sellerProfile;
          $imvCode     = $profile->seller_id ?? 'N/A';
          $companyName = $profile->company_name ?? $seller->name;

          // Resolve sub-seller category
          if ($profile?->category_id) {
            $catDisplay = \App\Models\Category::find($profile->category_id)?->name ?? '';
          } else {
            $catDisplay = '';
          }
          // Fallback to parent seller category if empty
          if (empty($catDisplay) && isset($parentSeller)) {
            $parentProf = $parentSeller->sellerProfile;
            if ($parentProf?->category_id) {
              $catDisplay = \App\Models\Category::find($parentProf->category_id)?->name ?? 'General';
            }
          }
          if (empty($catDisplay)) {
            $catDisplay = 'General';
          }

          $productCount = $seller->active_products_count ?? 0;
          $displayProdCount = $productCount . ' ' . \Illuminate\Support\Str::plural('Product', $productCount);

          $cardUrl = route('vendor.products', $imvCode);

          // Parent profile fallback for logo if sub-seller has no logo
          $parentProfile = null;
          if (!empty($profile->parent_id)) {
            $parentProfile = \App\Models\SellerProfile::where('user_id', $profile->parent_id)->first();
          }

          $logoRaw = $profile->company_logo ?? ($parentProfile?->company_logo ?? null);
          $logoSrc = null;
          if ($logoRaw) {
            if (filter_var($logoRaw, FILTER_VALIDATE_URL)) {
              $logoSrc = $logoRaw;
            } elseif (file_exists(public_path('storage/' . $logoRaw))) {
              $logoSrc = asset('storage/' . $logoRaw);
            }
          }
        @endphp

        {{-- Sub-Seller Card --}}
        <div class="vendor-card" style="position: relative;" onclick="window.location='{{ $cardUrl }}'">
          
          {{-- Share Button --}}
          <button type="button" class="seller-share-btn"
            onclick="event.stopPropagation(); openShareModal('{{ addslashes($companyName) }}', '{{ $cardUrl }}', '{{ $imvCode }}')"
            title="Share Sub-Seller" aria-label="Share Sub-Seller"
            style="position: absolute; top: 10px; right: 10px; z-index: 10; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 50%; width: 30px; height: 30px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #0284c7; box-shadow: 0 1px 4px rgba(0,0,0,0.08); transition: all 0.2s;">
            <i class="hgi hgi-stroke hgi-share-01" style="font-size: 14px;"></i>
          </button>

          <div>
            {{-- Logo Container --}}
            <div class="vendor-card-logo-box">
              @if($logoSrc)
                <img src="{{ $logoSrc }}" alt="{{ $companyName }}" style="max-height: 100%; max-width: 100%; object-fit: contain;" loading="lazy">
              @else
                <div style="width: 44px; height: 44px; border-radius: 50%; background: #e0f2fe; color: #0284c7; display: flex; align-items: center; justify-content: center;">
                  <i class="hgi hgi-stroke hgi-store-01" style="font-size: 22px;"></i>
                </div>
              @endif
            </div>

            {{-- Company Name --}}
            <h3 class="vendor-card-name">{{ $companyName }}</h3>

            {{-- Category --}}
            <p class="vendor-card-category">{{ $catDisplay }}</p>

            {{-- IMV Code Badge --}}
            <div>
              <span class="vendor-card-imv-badge">{{ $imvCode }}</span>
            </div>
          </div>

          <div>
            {{-- Products Count Footer --}}
            <div class="vendor-card-footer" style="justify-content: center;">
              <span class="vendor-card-products">
                <i class="hgi hgi-stroke hgi-store-01"></i>
                {{ $displayProdCount }}
              </span>
            </div>

            {{-- Action CTA Button --}}
            <a href="{{ $cardUrl }}" class="vendor-card-btn" onclick="event.stopPropagation()">
              View Profile
            </a>
          </div>

        </div>
      @endforeach
    </div>
  </div>

  <!-- Smart Controlled On-Scroll Progress & Loading Container -->
  @if($subSellers->count() > 0)
    <div id="sub-seller-scroll-container" style="margin-top: 32px; display: flex; flex-direction: column; align-items: center; justify-content: center; text-align: center; padding-bottom: 32px;">
      @php
        $visibleCount = min($subSellers->count(), $subSellers->total());
        $progressPercent = $subSellers->total() > 0 ? min(100, round(($visibleCount / $subSellers->total()) * 100)) : 100;
      @endphp
      <div style="font-size: 12px; color: #64748b; font-weight: 600; margin-bottom: 6px;" id="sub-seller-scroll-count-text">
        Showing <span id="sub-seller-visible-count">{{ $visibleCount }}</span> of <span id="sub-seller-total-count">{{ $subSellers->total() }}</span> sub-sellers
      </div>
      <div style="width: 200px; height: 6px; background-color: #e2e8f0; border-radius: 9999px; overflow: hidden; margin-bottom: 12px;">
        <div id="sub-seller-progress-bar-inner" style="background-color: #0284c7; height: 100%; transition: width 0.3s ease; width: {{ $progressPercent }}%;"></div>
      </div>

      <div id="sub-seller-scroll-loader" style="display: none !important; padding: 8px 0; align-items: center; justify-content: center; gap: 8px; color: #475569; font-size: 12px; font-weight: 600;">
        <span>Loading more sub-sellers...</span>
      </div>

      <div id="sub-seller-end-text" style="font-size: 12px; color: #94a3b8; font-weight: 500; margin: 8px 0; display: {{ $subSellers->hasMorePages() ? 'none' : 'block' }};">
        You've viewed all {{ $subSellers->total() }} sub-sellers
      </div>

      <div id="sub-seller-scroll-sentinel" style="width: 100%; height: 4px;"></div>
    </div>
  @endif

@endif