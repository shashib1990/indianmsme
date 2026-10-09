@extends('layouts.admin')

@section('title', 'Home Page Banners')
@section('page-title', 'Home Page Banners')

@section('content')
<div class="row g-4">
    <!-- Left Column: Add New Banner Form -->
    <div class="col-lg-4">
        <!-- Banner Dimensions Helper Guide Card -->
        <div class="card border-0 shadow-sm mb-3 bg-primary-subtle text-primary border-primary-subtle">
            <div class="card-body p-3">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <iconify-icon icon="solar:info-square-bold-duotone" class="fs-20 text-primary"></iconify-icon>
                    <h6 class="fw-bold mb-0 text-primary fs-14">Recommended Image Sizes</h6>
                </div>
                <ul class="list-unstyled mb-0 fs-12 text-slate-700 space-y-1">
                    <li class="mb-1">📐 <strong>Hero Left Zone:</strong> <span class="badge bg-white text-dark border ms-1">640 x 310 px</span></li>
                    <li class="mb-1">📐 <strong>Hero Right Zone:</strong> <span class="badge bg-white text-dark border ms-1">640 x 310 px</span></li>
                    <li class="mb-1">📐 <strong>Middle (3-Column):</strong> <span class="badge bg-white text-dark border ms-1">400 x 220 px</span></li>
                    <li>📐 <strong>Bottom (2-Column):</strong> <span class="badge bg-white text-dark border ms-1">630 x 240 px</span></li>
                </ul>
            </div>
        </div>

        <div class="card border-0 shadow-sm sticky-top" style="top: 24px; z-index: 10;">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                <iconify-icon icon="solar:gallery-add-bold-duotone" class="text-primary fs-22"></iconify-icon>
                <h5 class="card-title fw-bold text-slate-800 mb-0">Add New Banner</h5>
            </div>
            <div class="card-body p-4">
                <form action="{{ route('admin.banners.store') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Position Selection -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-slate-700" for="position">Banner Position</label>
                        <select name="position" id="position" class="form-select" onchange="updateSizeHint()" required>
                            <option value="left" selected>Hero Left Zone (Rec: 640x310 px)</option>
                            <option value="right">Hero Right Zone (Rec: 640x310 px)</option>
                            <option value="middle_3_col">Middle Section - 3 Column (Rec: 400x220 px)</option>
                            <option value="above_newsletter">Bottom Section - 2 Column (Rec: 630x240 px)</option>
                        </select>
                    </div>

                    <!-- Image Upload -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-slate-700" for="image">Choose Image</label>
                        <input type="file" name="image" id="image" class="form-control mb-1" accept="image/*" required>
                        <div class="p-2 rounded bg-light border border-dashed mt-2">
                            <small class="fw-bold text-primary d-block" id="bannerSizeHint">
                                <iconify-icon icon="solar:ruler-bold" class="align-middle me-1"></iconify-icon>Required Image Size: 640 x 310 px (Aspect ratio ~2:1). Max: 5MB.
                            </small>
                        </div>
                    </div>

                    <!-- Target Link URL -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-slate-700" for="link_url">Target Link (URL)</label>
                        <input type="url" name="link_url" id="link_url" class="form-control" placeholder="https://example.com/category or /product/..." value="{{ old('link_url') }}">
                        <small class="text-muted d-block mt-1">Optional. Direct URL opened when user clicks this banner image.</small>
                    </div>

                    <!-- Sort Order -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-slate-700" for="sort_order">Sort Order</label>
                        <input type="number" name="sort_order" id="sort_order" class="form-control" value="0" min="0" required>
                        <small class="text-muted d-block mt-1">Lower values will display first.</small>
                    </div>

                    <!-- Active Toggle -->
                    <div class="form-check form-switch mb-4">
                        <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" checked>
                        <label class="form-check-label fw-semibold text-slate-700" for="is_active">Set Active immediately</label>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary w-100 py-2 d-inline-flex align-items-center justify-content-center gap-1">
                        <iconify-icon icon="solar:upload-bold-duotone" class="fs-18"></iconify-icon>
                        Upload & Save Banner
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Right Column: Current Banners List -->
    <div class="col-lg-8">
        <!-- Hero Left Zone Banners Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:slider-bold-duotone" class="text-primary fs-22"></iconify-icon>
                    <h5 class="card-title fw-bold text-slate-800 mb-0">Hero Left Zone Banners ({{ $leftBanners->count() }})</h5>
                </div>
                <small class="text-muted fw-semibold">(Recommended Size: 640x310 px)</small>
            </div>
            <div class="card-body p-0">
                @if($leftBanners->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <iconify-icon icon="solar:camera-broken" class="fs-48 text-muted mb-2"></iconify-icon>
                        <p class="mb-0 small fw-semibold">No Hero Left Zone Banners uploaded yet.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 100px;">Image</th>
                                    <th>Link URL</th>
                                    <th>Sort</th>
                                    <th>Status</th>
                                    <th class="text-end" style="width: 90px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($leftBanners as $banner)
                                    <tr>
                                        <td>
                                            <div class="border rounded p-1 bg-light d-flex align-items-center justify-content-center overflow-hidden" style="width: 90px; height: 50px;">
                                                <img src="{{ $banner->image_url }}" alt="Left Banner" class="img-fluid rounded" style="max-height: 100%; object-fit: contain;">
                                            </div>
                                        </td>
                                        <td>
                                            @if($banner->link_url)
                                                <a href="{{ $banner->link_url }}" target="_blank" class="text-primary text-truncate d-inline-block" style="max-width: 180px; font-size: 13px;">
                                                    <iconify-icon icon="solar:link-bold" class="align-middle me-1"></iconify-icon>{{ $banner->link_url }}
                                                </a>
                                            @else
                                                <span class="text-muted fs-12">None</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border px-2 py-1">{{ $banner->sort_order }}</span>
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.banners.toggle-status', $banner->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn p-0 border-0 bg-transparent">
                                                    <span class="badge {{ $banner->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} px-2 py-1 fs-12">
                                                        <iconify-icon icon="{{ $banner->is_active ? 'solar:check-circle-bold' : 'solar:close-circle-bold' }}" class="align-middle me-0.5"></iconify-icon>
                                                        {{ $banner->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </button>
                                            </form>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.banners.edit', $banner->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2 me-1" style="font-size: 11px;">
                                                <iconify-icon icon="solar:pen-bold-duotone" class="align-middle"></iconify-icon> Edit
                                            </a>
                                            <form action="{{ route('admin.banners.delete', $banner->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2 confirm-action" style="font-size: 11px;" data-confirm-message="Are you sure you want to delete this banner?">
                                                    <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="align-middle"></iconify-icon> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Hero Right Zone Banners Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:slider-bold-duotone" class="text-primary fs-22"></iconify-icon>
                    <h5 class="card-title fw-bold text-slate-800 mb-0">Hero Right Zone Banners ({{ $rightBanners->count() }})</h5>
                </div>
                <small class="text-muted fw-semibold">(Recommended Size: 640x310 px)</small>
            </div>
            <div class="card-body p-0">
                @if($rightBanners->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <iconify-icon icon="solar:camera-broken" class="fs-48 text-muted mb-2"></iconify-icon>
                        <p class="mb-0 small fw-semibold">No Hero Right Zone Banners uploaded yet.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 100px;">Image</th>
                                    <th>Link URL</th>
                                    <th>Sort</th>
                                    <th>Status</th>
                                    <th class="text-end" style="width: 90px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($rightBanners as $banner)
                                    <tr>
                                        <td>
                                            <div class="border rounded p-1 bg-light d-flex align-items-center justify-content-center overflow-hidden" style="width: 90px; height: 50px;">
                                                <img src="{{ $banner->image_url }}" alt="Right Banner" class="img-fluid rounded" style="max-height: 100%; object-fit: contain;">
                                            </div>
                                        </td>
                                        <td>
                                            @if($banner->link_url)
                                                <a href="{{ $banner->link_url }}" target="_blank" class="text-primary text-truncate d-inline-block" style="max-width: 180px; font-size: 13px;">
                                                    <iconify-icon icon="solar:link-bold" class="align-middle me-1"></iconify-icon>{{ $banner->link_url }}
                                                </a>
                                            @else
                                                <span class="text-muted fs-12">None</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border px-2 py-1">{{ $banner->sort_order }}</span>
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.banners.toggle-status', $banner->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn p-0 border-0 bg-transparent">
                                                    <span class="badge {{ $banner->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} px-2 py-1 fs-12">
                                                        <iconify-icon icon="{{ $banner->is_active ? 'solar:check-circle-bold' : 'solar:close-circle-bold' }}" class="align-middle me-0.5"></iconify-icon>
                                                        {{ $banner->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </button>
                                            </form>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.banners.edit', $banner->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2 me-1" style="font-size: 11px;">
                                                <iconify-icon icon="solar:pen-bold-duotone" class="align-middle"></iconify-icon> Edit
                                            </a>
                                            <form action="{{ route('admin.banners.delete', $banner->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2 confirm-action" style="font-size: 11px;" data-confirm-message="Are you sure you want to delete this banner?">
                                                    <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="align-middle"></iconify-icon> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Middle Section 3-Column Banners Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:widget-3-bold-duotone" class="text-primary fs-22"></iconify-icon>
                    <h5 class="card-title fw-bold text-slate-800 mb-0">Middle Section - 3 Column Banners ({{ $middle3ColBanners->count() }})</h5>
                </div>
                <small class="text-muted fw-semibold">(Between Products & Newly Launched | Rec: 400x220px)</small>
            </div>
            <div class="card-body p-0">
                @if($middle3ColBanners->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <iconify-icon icon="solar:camera-broken" class="fs-48 text-muted mb-2"></iconify-icon>
                        <p class="mb-0 small fw-semibold">No Middle 3-Column Banners uploaded yet.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 100px;">Image</th>
                                    <th>Link URL</th>
                                    <th>Sort</th>
                                    <th>Status</th>
                                    <th class="text-end" style="width: 90px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($middle3ColBanners as $banner)
                                    <tr>
                                        <td>
                                            <div class="border rounded p-1 bg-light d-flex align-items-center justify-content-center overflow-hidden" style="width: 90px; height: 50px;">
                                                <img src="{{ $banner->image_url }}" alt="Middle Banner" class="img-fluid rounded" style="max-height: 100%; object-fit: contain;">
                                            </div>
                                        </td>
                                        <td>
                                            @if($banner->link_url)
                                                <a href="{{ $banner->link_url }}" target="_blank" class="text-primary text-truncate d-inline-block" style="max-width: 180px; font-size: 13px;">
                                                    <iconify-icon icon="solar:link-bold" class="align-middle me-1"></iconify-icon>{{ $banner->link_url }}
                                                </a>
                                            @else
                                                <span class="text-muted fs-12">None</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border px-2 py-1">{{ $banner->sort_order }}</span>
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.banners.toggle-status', $banner->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn p-0 border-0 bg-transparent">
                                                    <span class="badge {{ $banner->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} px-2 py-1 fs-12">
                                                        <iconify-icon icon="{{ $banner->is_active ? 'solar:check-circle-bold' : 'solar:close-circle-bold' }}" class="align-middle me-0.5"></iconify-icon>
                                                        {{ $banner->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </button>
                                            </form>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.banners.edit', $banner->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2 me-1" style="font-size: 11px;">
                                                <iconify-icon icon="solar:pen-bold-duotone" class="align-middle"></iconify-icon> Edit
                                            </a>
                                            <form action="{{ route('admin.banners.delete', $banner->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2 confirm-action" style="font-size: 11px;" data-confirm-message="Are you sure you want to delete this banner?">
                                                    <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="align-middle"></iconify-icon> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <!-- Bottom Section 2-Column Banners Card -->
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:layout-2-bold-duotone" class="text-primary fs-22"></iconify-icon>
                    <h5 class="card-title fw-bold text-slate-800 mb-0">Bottom Section - 2 Column Banners ({{ $aboveNewsletterBanners->count() }})</h5>
                </div>
                <small class="text-muted fw-semibold">(Above Subscribe Newsletter | Rec: 630x240px)</small>
            </div>
            <div class="card-body p-0">
                @if($aboveNewsletterBanners->isEmpty())
                    <div class="text-center py-4 text-muted">
                        <iconify-icon icon="solar:camera-broken" class="fs-48 text-muted mb-2"></iconify-icon>
                        <p class="mb-0 small fw-semibold">No Bottom 2-Column Banners uploaded yet.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 100px;">Image</th>
                                    <th>Link URL</th>
                                    <th>Sort</th>
                                    <th>Status</th>
                                    <th class="text-end" style="width: 90px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($aboveNewsletterBanners as $banner)
                                    <tr>
                                        <td>
                                            <div class="border rounded p-1 bg-light d-flex align-items-center justify-content-center overflow-hidden" style="width: 90px; height: 50px;">
                                                <img src="{{ $banner->image_url }}" alt="Bottom Banner" class="img-fluid rounded" style="max-height: 100%; object-fit: contain;">
                                            </div>
                                        </td>
                                        <td>
                                            @if($banner->link_url)
                                                <a href="{{ $banner->link_url }}" target="_blank" class="text-primary text-truncate d-inline-block" style="max-width: 180px; font-size: 13px;">
                                                    <iconify-icon icon="solar:link-bold" class="align-middle me-1"></iconify-icon>{{ $banner->link_url }}
                                                </a>
                                            @else
                                                <span class="text-muted fs-12">None</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border px-2 py-1">{{ $banner->sort_order }}</span>
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.banners.toggle-status', $banner->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn p-0 border-0 bg-transparent">
                                                    <span class="badge {{ $banner->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} px-2 py-1 fs-12">
                                                        <iconify-icon icon="{{ $banner->is_active ? 'solar:check-circle-bold' : 'solar:close-circle-bold' }}" class="align-middle me-0.5"></iconify-icon>
                                                        {{ $banner->is_active ? 'Active' : 'Inactive' }}
                                                    </span>
                                                </button>
                                            </form>
                                        </td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.banners.edit', $banner->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2 me-1" style="font-size: 11px;">
                                                <iconify-icon icon="solar:pen-bold-duotone" class="align-middle"></iconify-icon> Edit
                                            </a>
                                            <form action="{{ route('admin.banners.delete', $banner->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-danger py-1 px-2 confirm-action" style="font-size: 11px;" data-confirm-message="Are you sure you want to delete this banner?">
                                                    <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="align-middle"></iconify-icon> Delete
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
function updateSizeHint() {
    const pos = document.getElementById('position').value;
    const hintEl = document.getElementById('bannerSizeHint');
    
    let text = "";
    if (pos === 'left') {
        text = '<iconify-icon icon="solar:ruler-bold" class="align-middle me-1"></iconify-icon>Required Image Size: 640 x 310 px (Aspect ratio ~2:1). Max: 5MB.';
    } else if (pos === 'right') {
        text = '<iconify-icon icon="solar:ruler-bold" class="align-middle me-1"></iconify-icon>Required Image Size: 640 x 310 px (Aspect ratio ~2:1). Max: 5MB.';
    } else if (pos === 'middle_3_col') {
        text = '<iconify-icon icon="solar:ruler-bold" class="align-middle me-1"></iconify-icon>Required Image Size: 400 x 220 px (Aspect ratio ~16:9). Max: 5MB.';
    } else if (pos === 'above_newsletter') {
        text = '<iconify-icon icon="solar:ruler-bold" class="align-middle me-1"></iconify-icon>Required Image Size: 630 x 240 px (Aspect ratio ~2.6:1). Max: 5MB.';
    }
    
    if (hintEl) hintEl.innerHTML = text;
}
document.addEventListener('DOMContentLoaded', updateSizeHint);
</script>
@endpush
