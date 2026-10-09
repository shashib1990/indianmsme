@extends('layouts.adc')

@section('title', 'Regional Product Master')
@section('page-title', 'Regional Product Master')

@section('content')
    <!-- Header Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                Regional Product Master
                            </h4>
                            <p class="text-muted small mb-0">Manage independent regional products for your ADC center. Products created here are stored as Draft until Stocked In.</p>
                        </div>
                        <div class="col-md-6 text-md-end d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
                            <a href="{{ route('adc.local-products.comparison') }}" class="btn btn-outline-info btn-sm fw-semibold">
                                <iconify-icon icon="solar:scale-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Price Analytics
                            </a>
                            <a href="{{ route('adc.local-purchases.create') }}" class="btn btn-outline-success btn-sm fw-semibold">
                                <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Stock In
                            </a>
                            <a href="{{ route('adc.local-products.create') }}" class="btn btn-primary btn-sm fw-semibold">
                                <iconify-icon icon="solar:add-circle-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Create Regional Product
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-20 text-success"></iconify-icon>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="solar:danger-bold-duotone" class="fs-20 text-danger"></iconify-icon>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filter & Search Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('adc.local-products.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-6 col-lg-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><iconify-icon icon="solar:magnifer-linear"></iconify-icon></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0 ps-0" placeholder="Search by name, local code (LCL...), barcode..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-md-4 col-lg-4">
                    <select name="category_id" class="form-select bg-light">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary fw-semibold btn-sm w-100 py-2">Filter</button>
                    @if($search || $categoryId)
                        <a href="{{ route('adc.local-products.index') }}" class="btn btn-outline-secondary btn-sm py-2 px-3"><iconify-icon icon="solar:restart-bold-duotone"></iconify-icon></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase fs-11 text-muted fw-bold border-bottom">
                        <tr>
                            <th class="ps-4">Product Details</th>
                            <th>Code</th>
                            <th>Category</th>
                            <th>HSN & GST</th>
                            <th>Brand</th>
                            <th class="text-center">Unit</th>
                            <th class="text-center">Status</th>
                            <th class="pe-4 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="fs-13">
                        @forelse($products as $product)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        @if($product->product_image)
                                            @php
                                                $imgSrc = (str_starts_with($product->product_image, 'http://') || str_starts_with($product->product_image, 'https://') || str_starts_with($product->product_image, '/'))
                                                    ? $product->product_image
                                                    : asset('storage/' . $product->product_image);
                                            @endphp
                                            <img src="{{ $imgSrc }}" class="rounded-3 border" style="width: 48px; height: 48px; object-fit: cover;" alt="product">
                                        @else
                                            <div class="rounded-3 bg-emerald-50 text-emerald-700 d-flex align-items-center justify-content-center fw-bold" style="width: 48px; height: 48px; background: #ecfdf5; color: #047857;">
                                                <iconify-icon icon="solar:box-bold-duotone" class="fs-24"></iconify-icon>
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="fw-semibold text-slate-800 mb-0">{{ $product->product_name }}</h6>
                                            @if($product->barcode)
                                                <span class="text-muted fs-11">Barcode: <code>{{ $product->barcode }}</code></span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1 fs-12" style="border-radius: 8px;">
                                        {{ $product->local_product_code }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-medium text-slate-700">{{ $product->category->name ?? '—' }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold text-slate-700 fs-12">HSN: {{ $product->hsn_code ?? 'N/A' }}</span>
                                        <span class="badge bg-info-subtle text-info w-auto d-inline-block text-start fs-10 px-2 py-0.5 rounded-pill">GST {{ $product->gst_rate }}%</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-slate-600">{{ $product->brand->name ?? 'Unbranded' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-2">{{ $product->unit_of_measure }}</span>
                                </td>
                                <td>
                                    @if($product->status === 'Draft')
                                        <span class="badge bg-warning-subtle text-warning fw-semibold px-2.5 py-1 rounded-pill">
                                            <iconify-icon icon="solar:notes-bold-duotone" class="align-middle me-1"></iconify-icon> Draft
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success fw-semibold px-2.5 py-1 rounded-pill">
                                            <iconify-icon icon="solar:check-circle-bold-duotone" class="align-middle me-1"></iconify-icon> Active
                                        </span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('adc.local-purchases.create', ['local_product_id' => $product->id]) }}" class="btn btn-sm btn-success px-2.5 py-1 text-white fw-semibold" title="Stock In / Add Inventory">
                                            <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="align-middle me-1"></iconify-icon> Stock In
                                        </a>
                                        <a href="{{ route('adc.local-products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary px-2 py-1" title="Edit Master">
                                            <iconify-icon icon="solar:pen-bold-duotone" class="align-middle"></iconify-icon>
                                        </a>
                                        @if(!$product->inventories()->exists())
                                            <form action="{{ route('adc.local-products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this Regional Product master?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger px-2 py-1" title="Delete Master">
                                                    <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="align-middle"></iconify-icon>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="py-4">
                                        <iconify-icon icon="solar:shop-bold-duotone" class="fs-48 text-muted opacity-50 mb-2"></iconify-icon>
                                        <h5 class="fw-semibold text-slate-700">No Regional Products Found</h5>
                                        <p class="text-muted small mb-3">Create your first Regional Product Master or adjust your search filters.</p>
                                        <a href="{{ route('adc.local-products.create') }}" class="btn btn-success btn-sm fw-semibold px-3 py-2">
                                            <iconify-icon icon="solar:add-circle-bold-duotone" class="align-middle me-1"></iconify-icon> Create Regional Product
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($products->hasPages())
                <div class="p-3 border-top">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection
