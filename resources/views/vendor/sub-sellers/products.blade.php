@extends('layouts.vendor')

@section('title', 'Sub-Seller Products')
@section('page-title', 'Sub-Seller Catalog')

@section('content')
    <div class="row">
        <div class="col-12 mb-3">
            <a href="{{ route('vendor.sub-sellers.index') }}" class="btn btn-sm btn-outline-secondary">
                <iconify-icon icon="solar:arrow-left-bold-duotone" class="align-middle me-1"></iconify-icon>
                Back to Sub-Sellers
            </a>
        </div>
    </div>

    <!-- Header Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0 bg-success bg-opacity-10 border-start border-success border-3">
                <div class="card-body">
                    <h4 class="fw-bold text-success-emphasis mb-1" style="font-family: 'Outfit', sans-serif;">
                        Products & Pricing — {{ $subSeller->sellerProfile->company_name }}
                    </h4>
                    <p class="text-muted small mb-0">
                        All approved products from your catalog are automatically available to this sub-seller.
                        The <strong>Distributor Price</strong> column shows the sub-seller's final selling price (your price + their markup).
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters & Table Section -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                        <h5 class="card-title fw-bold text-slate-800 mb-0">Product Catalog</h5>
                        <form method="GET" action="{{ route('vendor.sub-sellers.products', $subSeller->id) }}" class="d-flex align-items-center gap-2" style="max-width: 350px;">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-muted border-end-0">
                                    <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                                </span>
                                <input type="text" name="search" class="form-control border-start-0" placeholder="Search name or barcode..." value="{{ $search ?? '' }}">
                            </div>
                            @if($search)
                                <a href="{{ route('vendor.sub-sellers.products', $subSeller->id) }}" class="btn btn-sm btn-light border" title="Clear search">Clear</a>
                            @endif
                        </form>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0 text-nowrap align-middle">
                            <thead class="bg-light bg-opacity-50">
                                <tr>
                                    <th class="ps-3" style="width: 50px;">#</th>
                                    <th style="width: 60px;">Image</th>
                                    <th>Product Details</th>
                                    <th class="text-end">MRP</th>
                                    <th class="text-end">Seller Price (Incl. GST)</th>
                                    <th class="text-end pe-3">Distributor Price</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $p)
                                    @php
                                        $custom        = $customizations->get($p->id);
                                        $markup        = $custom ? (float)$custom->margin : 0.00;
                                        $subExFactory  = (float)$p->seller_price_incl_gst + $markup;
                                        $hasCustom     = $custom && $markup > 0;
                                    @endphp
                                    <tr>
                                        <td class="ps-3 text-muted small">
                                            {{ $loop->iteration + (($products->currentPage() - 1) * $products->perPage()) }}
                                        </td>
                                        <td>
                                            @if($p->featured_image)
                                                <img src="{{ $p->featured_image }}" class="rounded border bg-light" style="width: 44px; height: 44px; object-fit: contain;">
                                            @else
                                                <div class="rounded border bg-light d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                                    <iconify-icon icon="solar:box-bold-duotone" class="text-muted fs-20"></iconify-icon>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-slate-800">{{ $p->product_name }}</div>
                                            <div class="text-muted small fs-11">
                                                Barcode: <code>{{ $p->barcode }}</code>
                                                <span class="mx-1">|</span>
                                                Brand: <span class="badge bg-light text-slate-600 border px-1">{{ $p->brand->name ?? '—' }}</span>
                                            </div>
                                        </td>
                                        <td class="text-end text-muted small">₹{{ number_format($p->mrp, 2) }}</td>
                                        <td class="text-end text-slate-600">₹{{ number_format($p->seller_price_incl_gst, 2) }}</td>
                                        <td class="text-end pe-3">
                                            <span class="fw-bold {{ $hasCustom ? 'text-success' : 'text-slate-500' }}">
                                                ₹{{ number_format($subExFactory, 2) }}
                                            </span>
                                            @if(!$hasCustom)
                                                <div class="text-muted" style="font-size: 10px;">No markup set</div>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center py-5 text-muted">
                                            <iconify-icon icon="solar:box-bold-duotone" class="fs-48 mb-2"></iconify-icon>
                                            <h5>No Products Found</h5>
                                            <p class="mb-0">There are no approved products available in your catalog matching the criteria.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($products->hasPages())
                        <div class="d-flex justify-content-between align-items-center p-3 border-top">
                            <span class="text-muted small">Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} results</span>
                            {{ $products->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
