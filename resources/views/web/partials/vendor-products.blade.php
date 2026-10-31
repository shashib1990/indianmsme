{{--
  Partial: vendor-products
  Used by: VendorController::products() AJAX response
  Variables: $products (LengthAwarePaginator), $seller (User)
--}}
@forelse($products as $product)
  <div class="wow animate__animated animate__fadeIn" data-product-id="{{ $product->id }}">
    @include('web.partials.product-card', ['product' => $product])
  </div>
@empty
  {{-- Empty state handled in JS --}}
@endforelse
