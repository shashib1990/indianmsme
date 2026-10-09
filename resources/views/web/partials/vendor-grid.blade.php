@if($sellers->isEmpty())
  <div style="grid-column: 1 / -1; padding: 60px 20px; text-align: center; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 16px; margin-bottom: 24px;">
    <i class="hgi hgi-stroke hgi-store-01" style="font-size: 44px; color: #cbd5e1; margin-bottom: 10px; display: block;"></i>
    <p style="color: #64748b; font-size: 14px; font-weight: 500; margin: 0;">No vendors found matching your criteria.</p>
  </div>
@else

  <div class="vendor-cards-outer-box">
    <div class="vendor-cards-grid">
      @foreach($sellers as $seller)
        @php
          $profile     = $seller->sellerProfile;
          $imvCode     = $profile->seller_id ?? 'N/A';
          $companyName = $profile->company_name ?? $seller->name;
          
          // Category resolution
          if ($profile?->category?->name) {
            $catDisplay = $profile->category->name;
          } elseif ($profile?->category_id) {
            $catDisplay = \App\Models\Category::find($profile->category_id)?->name ?? 'General';
          } else {
            $catDisplay = 'General';
          }

          // Product count
          $productCount = $seller->active_products_count ?? $seller->products()->where('status', 'Approved')->count();
          $displayProdCount = $productCount . ' ' . \Illuminate\Support\Str::plural('Product', $productCount);

          // Sub-sellers condition check
          $hasSubs = $seller->has_sub_sellers ?? false;
          $cardUrl = $hasSubs
            ? route('vendor.sub-sellers', $imvCode)
            : route('vendor.products', $imvCode);

          // Logo src resolution
          $logoRaw = $profile->company_logo ?? null;
          $logoSrc = null;
          if ($logoRaw) {
            if (filter_var($logoRaw, FILTER_VALIDATE_URL)) {
              $logoSrc = $logoRaw;
            } elseif (file_exists(public_path('storage/' . $logoRaw))) {
              $logoSrc = asset('storage/' . $logoRaw);
            }
          }
        @endphp

        {{-- Seller Card --}}
        <div class="vendor-card" style="position: relative;" onclick="window.location='{{ $cardUrl }}'">
          
          {{-- Share Button (Triggers global openShareModal) --}}
          <button type="button" class="seller-share-btn"
            onclick="event.stopPropagation(); openShareModal('{{ addslashes($companyName) }}', '{{ $cardUrl }}', '{{ $imvCode }}')"
            title="Share Seller" aria-label="Share Seller"
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

            {{-- IMV Code Badge (No "IMV:" label text as requested) --}}
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

@endif