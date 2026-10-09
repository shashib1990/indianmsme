@extends('layouts.admin')

@section('title', 'Review Product Approval')
@section('page-title', 'Review Product')

@section('content')
    @php
        if ($product->status === 'Approved') {
            $backRoute = route('admin.products.approvals.approved');
        } elseif ($product->status === 'Rejected') {
            $backRoute = route('admin.products.approvals.rejected');
        } elseif ($product->is_new) {
            $backRoute = route('admin.products.approvals.new');
        } else {
            $backRoute = route('admin.products.approvals.updated');
        }
    @endphp
    {{-- Top bar --}}
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <a href="{{ $backRoute }}" class="btn btn-sm btn-outline-secondary px-3 py-2 rounded-pill d-inline-flex align-items-center gap-2 shadow-sm bg-white">
            <iconify-icon icon="solar:arrow-left-bold-duotone" class="fs-16"></iconify-icon>
            Back to Approvals Queue
        </a>
        <div class="d-flex align-items-center gap-2">
            @if($product->status === 'Pending Approval')
                <span class="badge px-3 py-2 rounded-pill fs-12 d-inline-flex align-items-center gap-1 fw-semibold" style="background:#fef3c7; color:#92400e;">
                    <iconify-icon icon="solar:clock-circle-bold-duotone"></iconify-icon> Pending Approval
                </span>
            @elseif($product->status === 'Approved')
                <span class="badge px-3 py-2 rounded-pill fs-12 d-inline-flex align-items-center gap-1 fw-semibold" style="background:#d1fae5; color:#065f46;">
                    <iconify-icon icon="solar:check-circle-bold-duotone"></iconify-icon> Approved
                </span>
            @else
                <span class="badge px-3 py-2 rounded-pill fs-12 d-inline-flex align-items-center gap-1 fw-semibold" style="background:#fee2e2; color:#991b1b;">
                    <iconify-icon icon="solar:close-circle-bold-duotone"></iconify-icon> Rejected
                </span>
            @endif
            @if($product->is_new)
                <span class="badge px-3 py-2 rounded-pill fs-12 d-inline-flex align-items-center gap-1 fw-bold" style="background:#dbeafe; color:#1e40af;">
                    <iconify-icon icon="solar:star-bold-duotone"></iconify-icon> NEW CATALOG
                </span>
            @else
                <span class="badge px-3 py-2 rounded-pill fs-12 d-inline-flex align-items-center gap-1 fw-bold" style="background:#fef9c3; color:#713f12;">
                    <iconify-icon icon="solar:history-bold-duotone"></iconify-icon> RE-SUBMITTED
                </span>
            @endif
        </div>
    </div>

    {{-- Hero Identity Card --}}
    <div class="rounded-4 mb-4 overflow-hidden shadow" style="background:linear-gradient(135deg,#0f172a 0%,#1e3a5f 60%,#0e7490 100%); position:relative;">
        <div style="position:absolute;inset:0;background:url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23ffffff\' fill-opacity=\'0.03\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');opacity:.4;"></div>
        <div class="p-4 position-relative">
            <div class="d-flex flex-column flex-md-row align-items-start gap-4">
                {{-- Product Image --}}
                <div class="flex-shrink-0">
                    <div class="rounded-3 p-2 shadow" style="background:rgba(255,255,255,0.1);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,0.2); width:130px;height:130px;display:flex;align-items:center;justify-content:center;">
                        @if($product->featured_image)
                            <img src="{{ $product->featured_image }}" class="rounded-2" style="max-width:110px;max-height:110px;object-fit:contain;">
                        @else
                            <iconify-icon icon="solar:box-bold-duotone" class="fs-48" style="color:rgba(255,255,255,0.4);"></iconify-icon>
                        @endif
                    </div>
                </div>
                {{-- Product Info --}}
                <div class="flex-grow-1">
                    <h1 class="fw-extrabold mb-1" style="font-family:'Outfit',sans-serif;font-size:1.8rem;color:#fff;letter-spacing:-0.5px;line-height:1.2;">{{ $product->product_name }}</h1>
                    <p class="mb-3" style="color:rgba(255,255,255,0.55);font-size:13px;">
                        Submitted by <strong style="color:rgba(255,255,255,0.85);">{{ $product->seller ? ($product->seller->sellerProfile->company_name ?? $product->seller->name) : '—' }}</strong>
                        @if($product->seller?->sellerProfile?->seller_id)
                            &nbsp;<span style="background:rgba(255,255,255,0.1);padding:1px 8px;border-radius:20px;font-size:11px;color:rgba(255,255,255,0.6);">{{ $product->seller->sellerProfile->seller_id }}</span>
                        @endif
                    </p>
                    <div class="d-flex flex-wrap gap-2">
                        <span class="d-inline-flex align-items-center gap-1 px-3 py-1 rounded-pill" style="background:rgba(255,255,255,0.12);color:rgba(255,255,255,0.8);font-size:12px;border:1px solid rgba(255,255,255,0.15);">
                            <iconify-icon icon="solar:tag-bold-duotone"></iconify-icon> Code: {{ $product->product_code ?? '—' }}
                        </span>
                        <span class="d-inline-flex align-items-center gap-1 px-3 py-1 rounded-pill" style="background:rgba(255,255,255,0.12);color:rgba(255,255,255,0.8);font-size:12px;border:1px solid rgba(255,255,255,0.15);">
                            <iconify-icon icon="solar:hashtag-bold-duotone"></iconify-icon> Barcode: {{ $product->barcode }}
                        </span>
                        <span class="d-inline-flex align-items-center gap-1 px-3 py-1 rounded-pill" style="background:rgba(255,255,255,0.12);color:rgba(255,255,255,0.8);font-size:12px;border:1px solid rgba(255,255,255,0.15);">
                            <iconify-icon icon="solar:tag-bold-duotone"></iconify-icon> {{ $product->brand->name ?? '—' }}
                        </span>
                        <span class="d-inline-flex align-items-center gap-1 px-3 py-1 rounded-pill" style="background:rgba(255,255,255,0.12);color:rgba(255,255,255,0.8);font-size:12px;border:1px solid rgba(255,255,255,0.15);">
                            <iconify-icon icon="solar:folder-with-files-bold-duotone"></iconify-icon> {{ $product->category->name ?? '—' }}
                        </span>
                        <span class="d-inline-flex align-items-center gap-1 px-3 py-1 rounded-pill" style="background:rgba(255,255,255,0.12);color:rgba(255,255,255,0.8);font-size:12px;border:1px solid rgba(255,255,255,0.15);">
                            <iconify-icon icon="solar:earth-bold-duotone"></iconify-icon> {{ $product->country_of_origin ?? '—' }}
                        </span>
                    </div>
                </div>
                {{-- Quick Stats --}}
                <div class="flex-shrink-0 d-none d-md-flex flex-column gap-2 text-end">
                    <div class="rounded-3 p-3 text-center" style="background:rgba(255,255,255,0.1);backdrop-filter:blur(6px);border:1px solid rgba(255,255,255,0.15);min-width:110px;">
                        <div style="font-size:10px;color:rgba(255,255,255,0.55);text-transform:uppercase;letter-spacing:.6px;">MRP</div>
                        <div class="fw-extrabold mt-1" style="font-size:18px;color:#fff;">₹{{ number_format($product->mrp, 0) }}</div>
                    </div>
                    <div class="rounded-3 p-3 text-center" style="background:rgba(16,185,129,0.25);backdrop-filter:blur(6px);border:1px solid rgba(16,185,129,0.4);min-width:110px;">
                        <div style="font-size:10px;color:rgba(167,243,208,0.8);text-transform:uppercase;letter-spacing:.6px;">GST</div>
                        <div class="fw-extrabold mt-1" style="font-size:18px;color:#6ee7b7;">{{ $product->gst_rate }}%</div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Left Column: Product Specifications -->
        <div class="col-lg-8">


            <!-- Detailed Specifications -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:bill-list-bold-duotone" class="text-primary fs-20"></iconify-icon>
                    <h5 class="card-title fw-bold text-slate-800 mb-0">Detailed Specifications</h5>
                </div>
                <div class="card-body p-4">
                    <!-- Section 1: Classification & Origin -->
                    <h6 class="fw-bold text-slate-900 mb-3 d-flex align-items-center gap-2">
                        <span class="bg-primary bg-opacity-10 text-primary rounded-circle p-1 d-inline-flex">
                            <iconify-icon icon="solar:folder-with-files-bold-duotone" class="fs-14"></iconify-icon>
                        </span>
                        Classification & Origin
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 bg-light border border-slate-100 h-100">
                                <span class="text-slate-500 small d-block mb-1 fw-medium">Category Hierarchy</span>
                                <span class="fw-semibold text-slate-800 d-flex align-items-center gap-1 flex-wrap">
                                    <span class="bg-white border rounded px-2 py-1 small">{{ $product->category->name ?? '—' }}</span>
                                    @if($product->subCategory) 
                                        <iconify-icon icon="solar:alt-arrow-right-bold" class="text-slate-400 fs-12"></iconify-icon>
                                        <span class="bg-white border rounded px-2 py-1 small">{{ $product->subCategory->name }}</span> 
                                    @endif
                                    @if($product->childCategory) 
                                        <iconify-icon icon="solar:alt-arrow-right-bold" class="text-slate-400 fs-12"></iconify-icon>
                                        <span class="bg-white border rounded px-2 py-1 small text-primary">{{ $product->childCategory->name }}</span> 
                                    @endif
                                </span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded-3 bg-light border border-slate-100 h-100">
                                <span class="text-slate-500 small d-block mb-1 fw-medium">HSN Code</span>
                                <span class="fw-bold text-slate-800 d-flex align-items-center gap-1">
                                    <iconify-icon icon="solar:code-circle-bold-duotone" class="text-slate-400"></iconify-icon>
                                    <code>{{ $product->hsn_code }}</code>
                                </span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded-3 bg-light border border-slate-100 h-100">
                                <span class="text-slate-500 small d-block mb-1 fw-medium">Country of Origin</span>
                                <span class="fw-semibold text-slate-800 d-flex align-items-center gap-1">
                                    <iconify-icon icon="solar:earth-bold-duotone" class="text-slate-400"></iconify-icon>
                                    {{ $product->country_of_origin }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Section 2: Pricing & Commercials -->
                    <h6 class="fw-bold text-slate-900 mb-3 d-flex align-items-center gap-2">
                        <span class="bg-success bg-opacity-10 text-success rounded-circle p-1 d-inline-flex">
                            <iconify-icon icon="solar:tag-price-bold-duotone" class="fs-14"></iconify-icon>
                        </span>
                        Pricing & Taxation
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="p-3 rounded-3 border border-slate-200 shadow-2xs h-100 bg-white" style="border-left: 4px solid var(--adm-primary) !important;">
                                <span class="text-slate-500 small d-block mb-1 fw-medium">Seller Price (Incl. GST)</span>
                                <span class="fw-extrabold text-slate-900 fs-16">₹{{ number_format($product->seller_price_incl_gst, 2) }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded-3 border border-slate-200 shadow-2xs h-100 bg-white" style="border-left: 4px solid #3b82f6 !important;">
                                <span class="text-slate-500 small d-block mb-1 fw-medium">DDC Purchase Price</span>
                                <span class="fw-extrabold fs-16" style="color:#3b82f6">₹{{ number_format($product->ddc_purchase_price ?? 0, 2) }}</span>
                                <span class="text-muted d-block" style="font-size:10px;">Base + Admin Margin + GST</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded-3 border border-slate-200 shadow-2xs h-100 bg-white">
                                <span class="text-slate-500 small d-block mb-1 fw-medium">Maximum Retail Price (MRP)</span>
                                <span class="fw-bold text-slate-800 fs-16">₹{{ number_format($product->mrp, 2) }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded-3 border border-slate-200 shadow-2xs h-100 bg-white" style="border-left: 4px solid #10b981 !important;">
                                <span class="text-slate-500 small d-block mb-1 fw-medium">GST Rate Applicability</span>
                                <span class="fw-extrabold text-success fs-16 d-flex align-items-center gap-1">
                                    {{ $product->gst_rate }}% GST
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Section 3: Physical & Packaging Specs -->
                    <h6 class="fw-bold text-slate-900 mb-3 d-flex align-items-center gap-2">
                        <span class="bg-info bg-opacity-10 text-info rounded-circle p-1 d-inline-flex">
                            <iconify-icon icon="solar:ruler-pen-bold-duotone" class="fs-14"></iconify-icon>
                        </span>
                        Packaging, Dimensions & Weight
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-3">
                            <div class="p-3 rounded-3 bg-light border border-slate-100 h-100">
                                <span class="text-slate-500 small d-block mb-1 fw-medium">UOM (Unit of Measure)</span>
                                <span class="fw-semibold text-slate-800">{{ $product->unit_of_measure }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded-3 bg-light border border-slate-100 h-100">
                                <span class="text-slate-500 small d-block mb-1 fw-medium">Net Quantity / Pack Size</span>
                                <span class="fw-semibold text-slate-800">{{ $product->net_quantity ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded-3 bg-light border border-slate-100 h-100">
                                <span class="text-slate-500 small d-block mb-1 fw-medium">Case Pack Qty</span>
                                <span class="fw-semibold text-slate-800">{{ $product->case_pack_quantity ?? '—' }}</span>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="p-3 rounded-3 bg-light border border-slate-100 h-100">
                                <span class="text-slate-500 small d-block mb-1 fw-medium">Shelf Life</span>
                                <span class="fw-semibold text-slate-800">{{ $product->shelf_life ?? '—' }}</span>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="p-3 rounded-3 bg-light border border-slate-100 h-100">
                                <span class="text-slate-500 small d-block mb-2 fw-medium">Dimensions (L × W × H)</span>
                                <span class="fw-semibold text-slate-800 d-inline-flex align-items-center gap-2">
                                    <iconify-icon icon="solar:ruler-bold-duotone" class="text-slate-400"></iconify-icon>
                                    {{ $product->length ?? '—' }} × {{ $product->width ?? '—' }} × {{ $product->height ?? '—' }} {{ $product->dimension_unit }}
                                </span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 bg-light border border-slate-100 h-100">
                                <span class="text-slate-500 small d-block mb-2 fw-medium">Weight</span>
                                <span class="fw-semibold text-slate-800 d-inline-flex align-items-center gap-2">
                                    <iconify-icon icon="solar:scale-bold-duotone" class="text-slate-400"></iconify-icon>
                                    {{ $product->weight ?? '—' }} {{ $product->weight_unit }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Section 4: Storage, Brochure & Keywords -->
                    <h6 class="fw-bold text-slate-900 mb-3 d-flex align-items-center gap-2">
                        <span class="bg-secondary bg-opacity-10 text-secondary rounded-circle p-1 d-inline-flex">
                            <iconify-icon icon="solar:document-bold-duotone" class="fs-14"></iconify-icon>
                        </span>
                        Documents & Keywords
                    </h6>
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 bg-light border border-slate-100 h-100 d-flex flex-column justify-content-between">
                                <div>
                                    <span class="text-slate-500 small d-block mb-2 fw-medium">Brochure PDF / Catalog Spec Sheet</span>
                                </div>
                                <div>
                                    @if($product->brochure)
                                        <a href="{{ $product->brochure }}" target="_blank" class="btn btn-sm btn-outline-danger d-inline-flex align-items-center gap-2 px-3 py-2 rounded-2">
                                            <iconify-icon icon="solar:pdf-file-bold-duotone" class="fs-16"></iconify-icon>
                                            View Brochure Document
                                        </a>
                                    @else
                                        <span class="text-muted small d-inline-flex align-items-center gap-1">
                                            <iconify-icon icon="solar:document-text-broken" class="fs-16 text-slate-400"></iconify-icon>
                                            No brochure uploaded
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 bg-light border border-slate-100 h-100">
                                <span class="text-slate-500 small d-block mb-2 fw-medium">Search Keywords / Meta Tags</span>
                                <div class="d-flex flex-wrap gap-2">
                                    @if(is_array($product->product_keywords))
                                        @forelse($product->product_keywords as $tag)
                                            <span class="badge bg-secondary-subtle text-secondary border px-3 py-1 fs-11 rounded-pill">{{ $tag }}</span>
                                        @empty
                                            <span class="text-muted small">No keywords specified</span>
                                        @endforelse
                                    @else
                                        <span class="text-muted small">No keywords specified</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Textual Descriptions -->
                    <div class="row g-3">
                        <div class="col-12">
                            <div class="border rounded-3 p-4 bg-white shadow-2xs">
                                <label class="fw-bold text-slate-800 mb-2 d-flex align-items-center gap-2 fs-14">
                                    <iconify-icon icon="solar:align-left-bold-duotone" class="text-primary"></iconify-icon>
                                    Product Description
                                </label>
                                <div class="text-slate-600 small lh-base">{!! strip_tags($product->description) === $product->description ? nl2br(e($product->description)) : $product->description !!}</div>
                            </div>
                        </div>
 
                        @if($product->product_specification)
                        <div class="col-12">
                            <div class="border rounded-3 p-4 bg-white shadow-2xs">
                                <label class="fw-bold text-slate-800 mb-2 d-flex align-items-center gap-2 fs-14">
                                    <iconify-icon icon="solar:settings-bold-duotone" class="text-primary"></iconify-icon>
                                    Technical Specifications
                                </label>
                                <div class="text-slate-600 small lh-base">{!! strip_tags($product->product_specification) === $product->product_specification ? nl2br(e($product->product_specification)) : $product->product_specification !!}</div>
                            </div>
                        </div>
                        @endif
 
                        @if($product->storage_instructions)
                        <div class="col-12">
                            <div class="border border-warning-subtle rounded-3 p-4 bg-warning-subtle shadow-2xs">
                                <label class="fw-bold text-warning-emphasis mb-2 d-flex align-items-center gap-2 fs-14">
                                    <iconify-icon icon="solar:shield-warning-bold-duotone" class="text-warning"></iconify-icon>
                                    Storage & Usage Instructions
                                </label>
                                <div class="text-slate-700 small lh-base">{!! strip_tags($product->storage_instructions) === $product->storage_instructions ? nl2br(e($product->storage_instructions)) : $product->storage_instructions !!}</div>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Product Gallery Carousel / Grid -->
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:gallery-wide-bold-duotone" class="text-primary fs-20"></iconify-icon>
                    <h5 class="card-title fw-bold text-slate-800 mb-0">Image Gallery</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3">
                        @if(is_array($product->gallery) && count($product->gallery) > 0)
                            @foreach($product->gallery as $img)
                                <div class="col-md-3 col-6">
                                    <div class="position-relative overflow-hidden rounded-3 border p-1 bg-white hover-zoom shadow-2xs" style="transition: all 0.2s ease-in-out;">
                                        <a href="{{ $img }}" target="_blank">
                                            <img src="{{ $img }}" class="img-fluid rounded-2" style="height: 140px; width: 100%; object-fit: contain; background: #fff;">
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        @else
                            <div class="col-12 text-center py-5 text-muted border border-dashed rounded-3 bg-light">
                                <iconify-icon icon="solar:gallery-broken" class="fs-40 text-slate-300 mb-2"></iconify-icon>
                                <p class="mb-0 small fw-medium">No additional gallery images uploaded.</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Verification panel & Update Diffs -->
        <div class="col-lg-4">
            <form action="{{ route('admin.products.approvals.action', $product->id) }}" method="POST">
                @csrf
                @php
                    $defaultAdminMargin = (float) \App\Models\SystemSetting::getVal('admin_margin_percentage', 8.0);
                    $defaultDdcMargin = (float) \App\Models\SystemSetting::getVal('ddc_margin_percentage', 5.0);
                    $defaultMbp = (float) \App\Models\SystemSetting::getVal('mbp_percentage', 2.0);
                @endphp
                <!-- Margins Config Card -->
                <div class="card shadow-sm border-0 mb-4 overflow-hidden" style="border-top: 4px solid var(--adm-primary) !important;">
                    <div class="card-header bg-white py-3 d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:settings-bold-duotone" class="text-primary fs-20"></iconify-icon>
                        <h5 class="card-title fw-bold text-slate-800 mb-0">Pricing Margins (Override)</h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="alert alert-info py-2 px-3 small border-0 rounded-3 mb-4 lh-sm">
                            <iconify-icon icon="solar:info-circle-bold" class="align-middle me-1 fs-14"></iconify-icon>
                            Configure margins for this specific product. Empty fields will default to global settings.
                        </div>

                        <div class="row g-3 mb-4">
                            <div class="col-12">
                                <label class="form-label fw-bold text-slate-700 small mb-1">Admin Margin (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" name="admin_margin_percentage" id="admin_margin_percentage" value="{{ $product->admin_margin_percentage }}" placeholder="{{ $defaultAdminMargin }}" {{ !in_array($product->status, ['Pending Approval', 'Approved']) ? 'disabled' : '' }}>
                                    <span class="input-group-text bg-light">%</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold text-slate-700 small mb-1">DDC Margin (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" name="ddc_margin_percentage" id="ddc_margin_percentage" value="{{ $product->ddc_margin_percentage }}" placeholder="{{ $defaultDdcMargin }}" {{ !in_array($product->status, ['Pending Approval', 'Approved']) ? 'disabled' : '' }}>
                                    <span class="input-group-text bg-light">%</span>
                                </div>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-bold text-slate-700 small mb-1">MBP (%)</label>
                                <div class="input-group">
                                    <input type="number" step="0.01" class="form-control" name="mbp_percentage" id="mbp_percentage" value="{{ $product->mbp_percentage }}" placeholder="{{ $defaultMbp }}" {{ !in_array($product->status, ['Pending Approval', 'Approved']) ? 'disabled' : '' }}>
                                    <span class="input-group-text bg-light">%</span>
                                </div>
                            </div>
                        </div>

                        <div class="border-top pt-3">
                            <h6 class="fw-bold text-slate-800 mb-3 d-flex align-items-center gap-2 fs-13">
                                <iconify-icon icon="solar:calculator-bold-duotone" class="text-success fs-16"></iconify-icon>
                                Live Pricing Preview
                            </h6>

                            <div class="rounded-3 border overflow-hidden" style="font-size: 12.5px;">

                                {{-- Step 1: Seller Input --}}
                                <div class="px-3 py-2 bg-slate-700 text-white d-flex align-items-center gap-2" style="background:#334155;">
                                    <iconify-icon icon="solar:tag-price-bold-duotone" class="fs-13"></iconify-icon>
                                    <span class="fw-bold" style="font-size:11px; letter-spacing:.5px;">STEP 1 — SELLER INPUT</span>
                                </div>
                                <div class="px-3 py-2 bg-white border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-semibold text-slate-700">Seller Price (incl. GST)</span>
                                            <span class="d-block text-muted" style="font-size:10px;">What seller charges the company</span>
                                        </div>
                                        <span class="fw-bold text-slate-900" id="live_seller_price">₹0.00</span>
                                    </div>
                                </div>
                                <div class="px-3 py-2 bg-white border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-semibold text-slate-700">Base Cost</span>
                                            <span class="d-block text-muted" style="font-size:10px;">Seller Price ÷ (1 + GST%)</span>
                                        </div>
                                        <span class="fw-semibold text-slate-800" id="live_base_cost">₹0.00</span>
                                    </div>
                                </div>
                                <div class="px-3 py-2 bg-white border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-semibold text-slate-700">Company GST Amount</span>
                                            <span class="d-block text-muted" style="font-size:10px;">Seller Price − Base Cost</span>
                                        </div>
                                        <span class="fw-semibold text-slate-800" id="live_company_gst">₹0.00</span>
                                    </div>
                                </div>

                                {{-- Step 2: DDC Chain --}}
                                <div class="px-3 py-2 d-flex align-items-center gap-2" style="background:#1e40af; color:#fff;">
                                    <iconify-icon icon="solar:shop-2-bold-duotone" class="fs-13"></iconify-icon>
                                    <span class="fw-bold" style="font-size:11px; letter-spacing:.5px;">STEP 2 — DDC PURCHASE PRICE</span>
                                </div>
                                <div class="px-3 py-2 bg-white border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-semibold text-slate-700">Admin Margin Amount</span>
                                            <span class="d-block text-muted" style="font-size:10px;">Base Cost × Admin Margin%</span>
                                        </div>
                                        <span class="fw-semibold text-primary" id="live_admin_margin_amt">₹0.00</span>
                                    </div>
                                </div>
                                <div class="px-3 py-2 border-bottom" style="background:#eff6ff;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-bold text-primary">DDC Purchase Price</span>
                                            <span class="d-block text-muted" style="font-size:10px;">(Base Cost + Admin Margin) × (1 + GST%)</span>
                                        </div>
                                        <span class="fw-extrabold" style="color:#1d4ed8; font-size:14px;" id="live_ddc_purchase_price">₹0.00</span>
                                    </div>
                                </div>

                                {{-- Step 3: ADC Chain --}}
                                <div class="px-3 py-2 d-flex align-items-center gap-2" style="background:#065f46; color:#fff;">
                                    <iconify-icon icon="solar:buildings-2-bold-duotone" class="fs-13"></iconify-icon>
                                    <span class="fw-bold" style="font-size:11px; letter-spacing:.5px;">STEP 3 — ADC PURCHASE PRICE</span>
                                </div>
                                <div class="px-3 py-2 bg-white border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-semibold text-slate-700">DDC Margin Amount</span>
                                            <span class="d-block text-muted" style="font-size:10px;">DDC Purchase Price (w/o GST) × DDC Margin%</span>
                                        </div>
                                        <span class="fw-semibold text-info" id="live_ddc_margin_amt">₹0.00</span>
                                    </div>
                                </div>
                                <div class="px-3 py-2 bg-white border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-semibold text-slate-700">Price Before GST</span>
                                            <span class="d-block text-muted" style="font-size:10px;">DDC Purchase Price (w/o GST) + DDC Margin</span>
                                        </div>
                                        <span class="fw-semibold text-slate-800" id="live_price_before_gst">₹0.00</span>
                                    </div>
                                </div>
                                <div class="px-3 py-2 bg-white border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-semibold text-slate-700">Seller GST Amount</span>
                                            <span class="d-block text-muted" style="font-size:10px;">Price Before GST × GST%</span>
                                        </div>
                                        <span class="fw-semibold text-slate-800" id="live_seller_gst">₹0.00</span>
                                    </div>
                                </div>
                                <div class="px-3 py-2 border-bottom" style="background:#ecfdf5;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-bold text-success">ADC Purchase Price</span>
                                            <span class="d-block text-muted" style="font-size:10px;">Price Before GST + Seller GST</span>
                                        </div>
                                        <span class="fw-extrabold text-success" style="font-size:14px;" id="live_adc_purchase_price">₹0.00</span>
                                    </div>
                                </div>

                                {{-- Step 4: Margins from MRP --}}
                                <div class="px-3 py-2 d-flex align-items-center gap-2" style="background:#78350f; color:#fff;">
                                    <iconify-icon icon="solar:chart-2-bold-duotone" class="fs-13"></iconify-icon>
                                    <span class="fw-bold" style="font-size:11px; letter-spacing:.5px;">STEP 4 — MRP MARGINS &amp; MBP DISTRIBUTION</span>
                                </div>

                                {{-- MRP & Available Margin --}}
                                <div class="px-3 py-2 bg-white border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-semibold text-slate-700">MRP</span>
                                            <span class="d-block text-muted" style="font-size:10px;">Maximum Retail Price</span>
                                        </div>
                                        <span class="fw-semibold text-slate-800" id="live_mrp">₹0.00</span>
                                    </div>
                                </div>
                                <div class="px-3 py-2 bg-white border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-semibold text-slate-700">Available Margin</span>
                                            <span class="d-block text-muted" style="font-size:10px;">MRP − ADC Purchase Price</span>
                                        </div>
                                        <span class="fw-semibold text-slate-800" id="live_available_margin">₹0.00</span>
                                    </div>
                                </div>

                                {{-- MBP sub-header --}}
                                <div class="px-3 py-1 d-flex align-items-center gap-2" style="background:#d97706; color:#fff;">
                                    <iconify-icon icon="solar:pie-chart-2-bold-duotone" class="fs-12"></iconify-icon>
                                    <span class="fw-bold" style="font-size:10px; letter-spacing:.4px;">MBP (MSME Business Points) BREAKDOWN</span>
                                </div>

                                {{-- MBP Total --}}
                                <div class="px-3 py-2 bg-white border-bottom">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-semibold text-slate-700">MBP Amount (Total)</span>
                                            <span class="d-block text-muted" style="font-size:10px;">Base Cost × MBP%</span>
                                        </div>
                                        <span class="fw-bold text-warning" id="live_mbp_amt">₹0.00</span>
                                    </div>
                                </div>

                                {{-- MBP 90% + 10% split --}}
                                <div class="px-3 py-2 border-bottom" style="background:#fffbeb;">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div>
                                            <span class="fw-semibold text-slate-700">MBP 90% Share</span>
                                            <span class="d-block text-muted" style="font-size:10px;">MBP Amount × 90% &nbsp;▶ Field distribution pool</span>
                                        </div>
                                        <span class="fw-semibold" style="color:#b45309;" id="live_mbp_90">₹0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-semibold text-slate-700">MBP 10% Share</span>
                                            <span class="d-block text-muted" style="font-size:10px;">MBP Amount × 10% &nbsp;▶ Company retention</span>
                                        </div>
                                        <span class="fw-semibold text-muted" id="live_mbp_10">₹0.00</span>
                                    </div>
                                </div>

                                {{-- MBP 90% further split: 38% + 62% --}}
                                <div class="px-3 py-2 border-bottom" style="background:#fef9ec; padding-left:20px !important;">
                                    <p class="text-muted mb-2" style="font-size:10px; border-bottom:1px dashed #e5e7eb; padding-bottom:4px;">↳ MBP 90% Share further split</p>
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <div>
                                            <span class="fw-semibold text-slate-700">38% of 90% Share</span>
                                            <span class="d-block text-muted" style="font-size:10px;">MBP 90% × 38%</span>
                                        </div>
                                        <span class="fw-semibold" style="color:#92400e;" id="live_mbp_38">₹0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-semibold text-slate-700">62% of 90% Share</span>
                                            <span class="d-block text-muted" style="font-size:10px;">MBP 90% × 62%</span>
                                        </div>
                                        <span class="fw-semibold" style="color:#92400e;" id="live_mbp_62">₹0.00</span>
                                    </div>
                                </div>

                                {{-- ADC Final Margin --}}
                                <div class="px-3 py-2" style="background:#fef3c7;">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div>
                                            <span class="fw-bold" style="color:#92400e;">ADC Final Margin</span>
                                            <span class="d-block text-muted" style="font-size:10px;">Available Margin − MBP Amount</span>
                                        </div>
                                        <span class="fw-extrabold" style="color:#92400e; font-size:14px;" id="live_adc_final_margin">₹0.00</span>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>
 
                <!-- Actions Card -->
                @if($product->status !== 'Pending Approval')
                    <div class="card shadow-sm border-0 mb-4 overflow-hidden" style="border-top: 4px solid {{ $product->status === 'Approved' ? '#10b981' : '#ef4444' }} !important;">
                        <div class="card-header bg-white py-3 d-flex align-items-center gap-2">
                            <iconify-icon icon="solar:shield-check-bold-duotone" class="{{ $product->status === 'Approved' ? 'text-success' : 'text-danger' }} fs-20"></iconify-icon>
                            <h5 class="card-title fw-bold text-slate-800 mb-0">Status Details</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="text-center py-2">
                                <iconify-icon icon="{{ $product->status === 'Approved' ? 'solar:check-circle-bold-duotone' : 'solar:close-circle-bold-duotone' }}" class="{{ $product->status === 'Approved' ? 'text-success' : 'text-danger' }} fs-48 mb-2"></iconify-icon>
                                <h5 class="fw-bold text-slate-800">This product is {{ $product->status }}</h5>
                                <p class="text-muted small">Processed on {{ $product->updated_at->format('d M Y, h:i A') }}</p>
                                @if($product->admin_remarks)
                                    <div class="p-3 rounded-2 bg-light border text-start mt-3">
                                        <strong class="text-slate-800 small d-block mb-1">Admin Remarks:</strong>
                                        <span class="text-slate-600 small">{{ $product->admin_remarks }}</span>
                                    </div>
                                @endif

                                @if($product->status === 'Approved')
                                    <input type="hidden" name="action" value="update_margins">
                                    <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 d-flex align-items-center justify-content-center gap-2 fs-14 shadow-sm mt-4">
                                        <iconify-icon icon="solar:settings-bold-duotone" class="fs-18"></iconify-icon>
                                        Update Margins
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @else
                    <div class="card shadow-sm border-0 mb-4 overflow-hidden" style="border-top: 4px solid var(--adm-primary) !important;">
                        <div class="card-header bg-white py-3 d-flex align-items-center gap-2">
                            <iconify-icon icon="solar:shield-check-bold-duotone" class="text-primary fs-20"></iconify-icon>
                            <h5 class="card-title fw-bold text-slate-800 mb-0">Approval Action</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <label class="form-label fw-bold text-slate-800 small mb-3">Select Decision</label>
                                <div class="d-flex flex-column gap-3">
                                    <label class="p-3 rounded-3 border d-flex align-items-center gap-3 cursor-pointer bg-light bg-opacity-25 hover-shadow-sm transition-all" for="actionApprove">
                                        <input class="form-check-input" type="radio" name="action" id="actionApprove" value="approve" checked onclick="toggleRemarks(false)">
                                        <div class="flex-grow-1">
                                            <span class="d-block fw-bold text-success fs-13">Approve & Publish</span>
                                            <span class="d-block text-slate-500 fs-11 mt-1">Publish product live to MSME catalog</span>
                                        </div>
                                    </label>
                                    <label class="p-3 rounded-3 border d-flex align-items-center gap-3 cursor-pointer bg-light bg-opacity-25 hover-shadow-sm transition-all" for="actionReject">
                                        <input class="form-check-input" type="radio" name="action" id="actionReject" value="reject" onclick="toggleRemarks(true)">
                                        <div class="flex-grow-1">
                                            <span class="d-block fw-bold text-danger fs-13">Reject Catalog</span>
                                            <span class="d-block text-slate-500 fs-11 mt-1">Send back to seller with feedback</span>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="mb-4" id="remarksContainer" style="display: none;">
                                <label class="form-label required fw-bold text-slate-800 small mb-2">Remarks / Reason for Rejection</label>
                                <textarea id="admin_remarks" name="admin_remarks" class="form-control rounded-3" rows="4" placeholder="Please explain why the product catalog is being rejected..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-theme w-100 py-3 rounded-3 d-flex align-items-center justify-content-center gap-2 fs-14 shadow-sm mt-3">
                                <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-18"></iconify-icon>
                                Submit Decision
                            </button>
                        </div>
                    </div>
                @endif
            </form>
 
            <!-- Diff / Update Comparison Card -->
            @php
                $modificationLog = $product->auditLogs()
                    ->whereNotNull('previous_values')
                    ->whereNotNull('updated_values')
                    ->first();
            @endphp
            @if($modificationLog)
                <div class="card shadow-sm border-0 mb-4 bg-light bg-opacity-50 overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <iconify-icon icon="solar:transfer-horizontal-bold-duotone" class="text-info fs-20"></iconify-icon>
                            <h5 class="card-title fw-bold text-slate-800 mb-0">Modified Fields</h5>
                        </div>
                        <span class="badge bg-info-subtle text-info px-2 py-1 fs-11 rounded-pill">Update</span>
                    </div>
                    <div class="card-body p-0">
                        <div class="p-3 bg-info bg-opacity-10 text-info-emphasis small border-bottom lh-sm">
                            <iconify-icon icon="solar:info-circle-bold" class="align-middle me-1 fs-14"></iconify-icon>
                            Below are the values that were recently modified by the seller compared to the previously approved catalog version.
                        </div>
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-sm mb-0 text-wrap align-middle">
                                <thead>
                                    <tr class="table-light">
                                        <th class="ps-3 py-2" style="width: 30%;">Field</th>
                                        <th style="width: 35%;" class="text-danger py-2">Previous Value</th>
                                        <th style="width: 35%;" class="text-success py-2">New Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $prev = $modificationLog->previous_values;
                                        $new = $modificationLog->updated_values;
                                        $ignored = ['updated_at', 'status', 'admin_remarks'];
                                    @endphp
                                    @forelse($new as $key => $newVal)
                                        @if(!in_array($key, $ignored))
                                            @php
                                                $prevVal = $prev[$key] ?? '—';
                                                // Format arrays
                                                if (is_array($newVal)) $newVal = implode(', ', $newVal);
                                                if (is_array($prevVal)) $prevVal = implode(', ', $prevVal);
                                            @endphp
                                            <tr>
                                                <td class="ps-3 fw-bold text-slate-700 text-uppercase" style="font-size: 10px;">{{ str_replace('_', ' ', $key) }}</td>
                                                <td class="small text-danger bg-danger-subtle text-wrap p-2">{!! nl2br(e($prevVal)) !!}</td>
                                                <td class="small text-success bg-success-subtle text-wrap p-2 fw-semibold">{!! nl2br(e($newVal)) !!}</td>
                                            </tr>
                                        @endif
                                    @empty
                                        <tr>
                                            <td colspan="3" class="text-center py-4 text-muted small bg-white">No specific data fields changed.</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
 
            {{-- Audit Timeline --}}
            <div class="card border-0 shadow-sm mb-4 overflow-hidden">
                <div class="card-header py-3 d-flex align-items-center gap-2" style="background:#f8fafc;border-bottom:1px solid #e2e8f0;">
                    <iconify-icon icon="solar:history-bold-duotone" class="text-primary fs-20"></iconify-icon>
                    <h5 class="card-title fw-bold text-slate-800 mb-0">Version History</h5>
                </div>
                <div class="card-body p-3">
                    @forelse($product->auditLogs as $log)
                        @php
                            $isApproved = $log->approval_status === 'Approved';
                            $isRejected = $log->approval_status === 'Rejected';
                            $dotColor = $isApproved ? '#10b981' : ($isRejected ? '#ef4444' : '#f59e0b');
                            $icon = $isApproved ? 'solar:check-circle-bold-duotone' : ($isRejected ? 'solar:close-circle-bold-duotone' : 'solar:clock-circle-bold-duotone');
                        @endphp
                        <div class="d-flex gap-3 mb-3 pb-3" style="border-bottom:1px dashed #e2e8f0;">
                            <div class="flex-shrink-0 d-flex flex-column align-items-center">
                                <div class="rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width:34px;height:34px;background:{{ $dotColor }}20;border:2px solid {{ $dotColor }};">
                                    <iconify-icon icon="{{ $icon }}" style="color:{{ $dotColor }};font-size:16px;"></iconify-icon>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-bold" style="font-size:12px;color:{{ $dotColor }};">{{ $log->approval_status }}</span>
                                    <span class="text-muted d-inline-flex align-items-center gap-1" style="font-size:11px;">
                                        <iconify-icon icon="solar:clock-circle-broken"></iconify-icon>
                                        {{ $log->created_at->diffForHumans() }}
                                    </span>
                                </div>
                                <p class="mb-1 text-slate-600" style="font-size:12px;">
                                    By: <strong class="text-slate-800">{{ $log->modifier->name ?? 'Seller' }}</strong>
                                </p>
                                @if($log->admin_remarks)
                                    <div class="p-2 rounded-2 mt-1" style="background:#fef9ec;border:1px solid #fde68a;font-size:11px;color:#92400e;line-height:1.5;">
                                        <iconify-icon icon="solar:chat-round-like-bold-duotone" class="me-1"></iconify-icon>
                                        {{ $log->admin_remarks }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-4 text-muted small">
                            <iconify-icon icon="solar:history-broken" class="fs-32 d-block mb-2 text-slate-300"></iconify-icon>
                            No history available yet.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function toggleRemarks(show) {
    const remarksContainer = document.getElementById('remarksContainer');
    const remarksText = document.getElementById('admin_remarks');
    if (show) {
        remarksContainer.style.display = 'block';
        remarksText.required = true;
        remarksText.focus();
    } else {
        remarksContainer.style.display = 'none';
        remarksText.required = false;
        remarksText.value = '';
    }
}

document.addEventListener('DOMContentLoaded', function () {
    const sellerPrice = {{ (float) $product->seller_price_incl_gst }};
    const gstRate = {{ (float) $product->gst_rate }};
    const mrp = {{ (float) $product->mrp }};
    const defaultAdminMargin = {{ $defaultAdminMargin }};
    const defaultDdcMargin = {{ $defaultDdcMargin }};
    const defaultMbp = {{ $defaultMbp }};

    const adminMarginInput = document.getElementById('admin_margin_percentage');
    const ddcMarginInput = document.getElementById('ddc_margin_percentage');
    const mbpInput = document.getElementById('mbp_percentage');

    const liveSellerPriceEl = document.getElementById('live_seller_price');
    const liveBaseCost = document.getElementById('live_base_cost');
    const liveCompanyGst = document.getElementById('live_company_gst');
    const liveAdminMarginAmt = document.getElementById('live_admin_margin_amt');
    const liveDdcPurchasePrice = document.getElementById('live_ddc_purchase_price');
    const liveDdcMarginAmt = document.getElementById('live_ddc_margin_amt');
    const livePriceBeforeGst = document.getElementById('live_price_before_gst');
    const liveSellerGst = document.getElementById('live_seller_gst');
    const liveAdcPurchasePrice = document.getElementById('live_adc_purchase_price');
    const liveMrpEl = document.getElementById('live_mrp');
    const liveAvailableMargin = document.getElementById('live_available_margin');
    const liveMbpAmt = document.getElementById('live_mbp_amt');
    const liveMbp90 = document.getElementById('live_mbp_90');
    const liveMbp10 = document.getElementById('live_mbp_10');
    const liveMbp38 = document.getElementById('live_mbp_38');
    const liveMbp62 = document.getElementById('live_mbp_62');
    const liveAdcFinalMargin = document.getElementById('live_adc_final_margin');

    function recalculatePricing() {
        let adminMarginPercent = adminMarginInput.value !== '' ? parseFloat(adminMarginInput.value) : defaultAdminMargin;
        let ddcMarginPercent = ddcMarginInput.value !== '' ? parseFloat(ddcMarginInput.value) : defaultDdcMargin;
        let mbpPercent = mbpInput.value !== '' ? parseFloat(mbpInput.value) : defaultMbp;

        if (isNaN(adminMarginPercent)) adminMarginPercent = defaultAdminMargin;
        if (isNaN(ddcMarginPercent)) ddcMarginPercent = defaultDdcMargin;
        if (isNaN(mbpPercent)) mbpPercent = defaultMbp;

        // ── 1. Full Decimal Precision Intermediate Calculations (Un-rounded) ──
        const rawBaseCost = sellerPrice / (1.0 + (gstRate / 100.0));
        const rawCompanyGst = sellerPrice - rawBaseCost;

        const rawAdminMarginAmount = rawBaseCost * (adminMarginPercent / 100.0);
        const rawDdcPurchasePriceWithoutGst = rawBaseCost + rawAdminMarginAmount;
        const rawDdcPurchasePrice = rawDdcPurchasePriceWithoutGst * (1.0 + (gstRate / 100.0));

        const rawDdcMarginAmount = rawDdcPurchasePriceWithoutGst * (ddcMarginPercent / 100.0);
        const rawPriceBeforeGst = rawDdcPurchasePriceWithoutGst + rawDdcMarginAmount;
        const rawSellerGst = rawPriceBeforeGst * (gstRate / 100.0);
        const rawAdcPurchasePrice = rawPriceBeforeGst + rawSellerGst;

        const rawMbpAmount = rawBaseCost * (mbpPercent / 100.0);
        const rawAvailableMargin = mrp - rawAdcPurchasePrice;
        const rawAdcFinalMargin = rawAvailableMargin - rawMbpAmount;

        const rawMbp90 = rawMbpAmount * 0.90;
        const rawMbp10 = rawMbpAmount * 0.10;
        const rawMbp38 = rawMbp90 * 0.38;
        const rawMbp62 = rawMbp90 * 0.62;

        // ── 2. Final Monetary Output Values (Standard 2-Decimal Rounding) ──
        const baseCost = Math.round(rawBaseCost * 100) / 100;
        const companyGst = Math.round(rawCompanyGst * 100) / 100;
        const adminMarginAmount = Math.round(rawAdminMarginAmount * 100) / 100;
        const ddcPurchasePrice = Math.round(rawDdcPurchasePrice * 100) / 100;
        const ddcMarginAmount = Math.round(rawDdcMarginAmount * 100) / 100;
        const priceBeforeGst = Math.round(rawPriceBeforeGst * 100) / 100;
        const sellerGst = Math.round(rawSellerGst * 100) / 100;
        const adcPurchasePrice = Math.round(rawAdcPurchasePrice * 100) / 100;
        const mbpAmount = Math.round(rawMbpAmount * 100) / 100;
        const availableMargin = Math.round((mrp - adcPurchasePrice) * 100) / 100;
        const adcFinalMargin = Math.round((availableMargin - mbpAmount) * 100) / 100;

        let mbp90 = Math.round(rawMbp90 * 100) / 100;
        let mbp10 = Math.round(rawMbp10 * 100) / 100;
        if (Math.round((mbp90 + mbp10) * 100) !== Math.round(mbpAmount * 100)) {
            mbp10 = Math.round((mbpAmount - mbp90) * 100) / 100;
        }

        let mbp38 = Math.round(rawMbp38 * 100) / 100;
        let mbp62 = Math.round(rawMbp62 * 100) / 100;
        if (Math.round((mbp38 + mbp62) * 100) !== Math.round(mbp90 * 100)) {
            mbp62 = Math.round((mbp90 - mbp38) * 100) / 100;
        }

        // Format utility
        const formatINR = (val) => '₹' + val.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        // Update UI — in logical order
        liveSellerPriceEl.innerText = formatINR(sellerPrice);
        liveBaseCost.innerText = formatINR(baseCost);
        liveCompanyGst.innerText = formatINR(companyGst);
        liveAdminMarginAmt.innerText = formatINR(adminMarginAmount);
        liveDdcPurchasePrice.innerText = formatINR(ddcPurchasePrice);
        liveDdcMarginAmt.innerText = formatINR(ddcMarginAmount);
        livePriceBeforeGst.innerText = formatINR(priceBeforeGst);
        liveSellerGst.innerText = formatINR(sellerGst);
        liveAdcPurchasePrice.innerText = formatINR(adcPurchasePrice);
        liveMrpEl.innerText = formatINR(mrp);
        liveAvailableMargin.innerText = formatINR(availableMargin);
        liveMbpAmt.innerText = formatINR(mbpAmount);
        liveMbp90.innerText = formatINR(mbp90);
        liveMbp10.innerText = formatINR(mbp10);
        liveMbp38.innerText = formatINR(mbp38);
        liveMbp62.innerText = formatINR(mbp62);
        liveAdcFinalMargin.innerText = formatINR(adcFinalMargin);
    }

    adminMarginInput.addEventListener('input', recalculatePricing);
    ddcMarginInput.addEventListener('input', recalculatePricing);
    mbpInput.addEventListener('input', recalculatePricing);

    // Initial calculation
    recalculatePricing();
});
</script>
@endpush
