<div id="shop-products-container">
  <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 shop-custom-grid gap-4 pb-8">
    <!-- products -->
    @forelse($products as $product)
      <div class="wow animate__animated animate__fadeInUp" data-wow-delay=".1s">
        @include('web.partials.product-card', ['product' => $product])
      </div>
    @empty
      <div class="col-span-12 py-12 text-center vm-empty">
        <i class="hgi hgi-stroke hgi-package text-5xl text-gray-300 mb-4 block"></i>
        <p class="text-gray-500">No products found matching your criteria.</p>
      </div>
    @endforelse
  </div>

  <!-- Flipkart / Amazon Style Smart On-Scroll Progress & Loading Container -->
  @if($products->count() > 0)
    <div id="smart-scroll-container" class="mt-8 flex flex-col items-center justify-center text-center pb-8">
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
        <div class="shop-loading-spinner">
          <div class="shop-spinner-dot"></div>
          <div class="shop-spinner-dot"></div>
          <div class="shop-spinner-dot"></div>
        </div>
        <span>Loading more products...</span>
      </div>

      @if($products->hasMorePages())
        <div id="smart-manual-load-wrapper" class="my-2" style="display: none !important;">
          <button type="button" onclick="triggerManualLoadShopPage()" 
            class="px-5 py-2.5 bg-white border border-primary text-primary hover:bg-primary hover:text-white transition-all rounded-full font-bold text-xs shadow-sm flex items-center gap-2">
            <i class="hgi hgi-stroke hgi-plus-circle text-base"></i> Load More Products
          </button>
        </div>
      @else
        <div id="smart-end-text" class="text-xs text-slate-400 font-medium my-2">
          You've viewed all {{ $products->total() }} products
        </div>
      @endif

      <div id="smart-scroll-sentinel" class="w-full h-1"></div>
    </div>
  @endif
</div>