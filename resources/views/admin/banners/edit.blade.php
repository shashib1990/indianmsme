@extends('layouts.admin')

@section('title', 'Edit Home Page Banner #' . $banner->id)
@section('page-title', 'Edit Banner #' . $banner->id)

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <div class="d-flex align-items-center justify-content-between mb-4">
            <h4 class="fw-bold text-slate-800 mb-0" style="font-family: 'Outfit', sans-serif;">
                <iconify-icon icon="solar:pen-bold-duotone" class="align-middle me-1 text-primary"></iconify-icon>
                Edit Banner #{{ $banner->id }}
            </h4>
            <a href="{{ route('admin.banners.index') }}" class="btn btn-outline-secondary btn-sm px-3 d-inline-flex align-items-center gap-1">
                <iconify-icon icon="solar:arrow-left-bold" class="fs-16"></iconify-icon>
                Back to All Banners
            </a>
        </div>

        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bx bx-error-circle me-1"></i> {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-4">
                <form action="{{ route('admin.banners.update', $banner->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')

                    <div class="row g-4">
                        <!-- Left Column: Current Image Preview -->
                        <div class="col-md-5 border-end pe-md-4">
                            <label class="form-label fw-bold text-slate-700 d-block mb-2">Current Banner Image</label>
                            <div class="p-3 bg-light rounded border text-center mb-3">
                                <img src="{{ $banner->image_url }}" alt="Banner {{ $banner->id }}" class="img-fluid rounded border shadow-xs" style="max-height: 220px; object-fit: contain;">
                            </div>
                            
                            <div class="d-flex align-items-center justify-content-between p-2 bg-light-subtle rounded border">
                                <span class="text-muted small fw-semibold">Current Status:</span>
                                <span class="badge {{ $banner->is_active ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} px-2 py-1 fs-12">
                                    {{ $banner->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>

                        <!-- Right Column: Edit Inputs -->
                        <div class="col-md-7 ps-md-4">
                            <!-- Position Selection -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-slate-700" for="position">Banner Position <span class="text-danger">*</span></label>
                                <select name="position" id="position" class="form-select" onchange="updateSizeHint()" required>
                                    <option value="left" {{ old('position', $banner->position) === 'left' ? 'selected' : '' }}>Hero Left Zone (Rec: 640x310 px)</option>
                                    <option value="right" {{ old('position', $banner->position) === 'right' ? 'selected' : '' }}>Hero Right Zone (Rec: 640x310 px)</option>
                                    <option value="middle_3_col" {{ old('position', $banner->position) === 'middle_3_col' ? 'selected' : '' }}>Middle Section - 3 Column (Rec: 400x220 px)</option>
                                    <option value="above_newsletter" {{ old('position', $banner->position) === 'above_newsletter' ? 'selected' : '' }}>Bottom Section - 2 Column (Rec: 630x240 px)</option>
                                </select>
                            </div>

                            <!-- Replace Image Input -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-slate-700" for="image">Replace Image (Optional)</label>
                                <input type="file" name="image" id="image" class="form-control mb-1" accept="image/*">
                                <div class="p-2 rounded bg-light border border-dashed mt-2">
                                    <small class="fw-bold text-primary d-block" id="bannerSizeHint">
                                        <iconify-icon icon="solar:ruler-bold" class="align-middle me-1"></iconify-icon>Recommended Image Size: 640 x 310 px. Leave empty to keep current image.
                                    </small>
                                </div>
                            </div>

                            <!-- Target Link URL -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-slate-700" for="link_url">Target Link (URL)</label>
                                <input type="url" name="link_url" id="link_url" class="form-control" placeholder="https://example.com/category or /product/..." value="{{ old('link_url', $banner->link_url) }}">
                                <small class="text-muted d-block mt-1">Optional. Direct URL opened when user clicks this banner image.</small>
                            </div>

                            <!-- Sort Order -->
                            <div class="mb-3">
                                <label class="form-label fw-bold text-slate-700" for="sort_order">Sort Order</label>
                                <input type="number" name="sort_order" id="sort_order" class="form-control" value="{{ old('sort_order', $banner->sort_order) }}" min="0" required>
                                <small class="text-muted d-block mt-1">Lower values will display first.</small>
                            </div>

                            <!-- Active Toggle -->
                            <div class="form-check form-switch mb-4">
                                <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $banner->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-slate-700" for="is_active">Active Banner</label>
                            </div>

                            <!-- Form Buttons -->
                            <div class="d-flex align-items-center gap-2">
                                <button type="submit" class="btn btn-primary px-4 py-2 d-inline-flex align-items-center gap-1">
                                    <iconify-icon icon="solar:diskette-bold-duotone" class="fs-18"></iconify-icon>
                                    Update Banner Details
                                </button>
                                <a href="{{ route('admin.banners.index') }}" class="btn btn-light px-3 py-2">
                                    Cancel
                                </a>
                            </div>
                        </div>
                    </div>
                </form>
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
        text = '<iconify-icon icon="solar:ruler-bold" class="align-middle me-1"></iconify-icon>Recommended Image Size: 640 x 310 px (Aspect ratio ~2:1). Leave empty to keep current image.';
    } else if (pos === 'right') {
        text = '<iconify-icon icon="solar:ruler-bold" class="align-middle me-1"></iconify-icon>Recommended Image Size: 640 x 310 px (Aspect ratio ~2:1). Leave empty to keep current image.';
    } else if (pos === 'middle_3_col') {
        text = '<iconify-icon icon="solar:ruler-bold" class="align-middle me-1"></iconify-icon>Recommended Image Size: 400 x 220 px (Aspect ratio ~16:9). Leave empty to keep current image.';
    } else if (pos === 'above_newsletter') {
        text = '<iconify-icon icon="solar:ruler-bold" class="align-middle me-1"></iconify-icon>Recommended Image Size: 630 x 240 px (Aspect ratio ~2.6:1). Leave empty to keep current image.';
    }
    
    if (hintEl) hintEl.innerHTML = text;
}
document.addEventListener('DOMContentLoaded', updateSizeHint);
</script>
@endpush
