@extends('layouts.adc')

@section('title', 'Edit Regional Product Master')
@section('page-title', 'Edit Regional Product Master')

@section('content')
    <!-- Header Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-8">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                Edit Regional Product Master
                            </h4>
                            <p class="text-muted small mb-0">Update master details for <strong>{{ $product->product_name }}</strong> ({{ $product->local_product_code }}).</p>
                        </div>
                        <div class="col-md-4 text-md-end">
                            <a href="{{ route('adc.local-products.index') }}" class="btn btn-outline-secondary btn-sm fw-semibold">
                                <iconify-icon icon="solar:arrow-left-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Back to Products
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category HSN Warning Banner -->
    <div id="hsn-error-banner" class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 d-none" role="alert">
        <div class="d-flex align-items-center gap-2">
            <iconify-icon icon="solar:danger-bold-duotone" class="fs-22 text-danger flex-shrink-0"></iconify-icon>
            <div>
                <strong class="d-block" id="hsn-error-message">HSN Code is not mapped with this Category. Please configure it from the Admin Panel.</strong>
                <span class="fs-12 text-danger-emphasis">Select a Category with mapped HSN codes to proceed.</span>
            </div>
        </div>
    </div>

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4" role="alert">
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('adc.local-products.update', $product->id) }}" method="POST" enctype="multipart/form-data" id="edit-local-product-form">
        @csrf
        @method('PUT')
        <div class="row g-4">
            <!-- Left Column: Master Details -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2">
                            <iconify-icon icon="solar:pen-bold-duotone" class="text-primary fs-20"></iconify-icon>
                            1. Product Master Information
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <div class="row g-3">
                            <!-- Product Name -->
                            <div class="col-md-12 position-relative">
                                <label class="form-label fw-semibold text-slate-700">Product Name <span class="text-danger">*</span></label>
                                <input type="text" name="product_name" id="product_name_input" class="form-control form-control-lg fs-15" value="{{ old('product_name', $product->product_name) }}" autocomplete="off" required>
                                <input type="hidden" name="msme_product_id" id="msme_product_id_input" value="{{ old('msme_product_id', $product->msme_product_id) }}">

                                <!-- Linked MSME Product Badge -->
                                <div id="linked-msme-badge" class="mt-2 {{ $product->msme_product_id ? '' : 'd-none' }}">
                                    <span class="badge bg-success-subtle text-success fs-12 px-3 py-1.5 rounded-pill border border-success-subtle d-inline-flex align-items-center gap-1">
                                        <iconify-icon icon="solar:link-bold-duotone" class="fs-14"></iconify-icon>
                                        Linked with MSME Catalog Product (<span id="linked-msme-name">{{ $product->msmeProduct?->product_name ?? 'Product #'.$product->msme_product_id }}</span>). Enables Website vs Local Price Comparison.
                                        <button type="button" class="btn-close ms-2 fs-10" id="unlink-msme-btn" aria-label="Unlink"></button>
                                    </span>
                                </div>

                                <div id="product-suggestions-box" class="position-absolute start-0 end-0 top-100 mt-1 shadow-lg rounded-3 bg-white border d-none" style="z-index: 1060; max-height: 320px; overflow-y: auto;">
                                    <div id="suggestions-list" class="list-group list-group-flush"></div>
                                </div>
                            </div>

                            <!-- Category -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-slate-700">Category <span class="text-danger">*</span></label>
                                <select name="category_id" id="category_id_select" class="form-select s-select" data-placeholder="— Select Category —" required>
                                    <option value="">Select Category</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- HSN Code -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-slate-700">HSN Code <span class="text-danger">*</span></label>
                                <select name="hsn_code" id="hsn_code_select" class="form-select s-select" data-placeholder="— Select HSN Code —" required>
                                    <option value="">Select HSN Code</option>
                                </select>
                            </div>

                            <!-- GST Rate -->
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold text-slate-700">GST Rate (%) <span class="text-danger">*</span></label>
                                <input type="number" name="gst_rate" id="gst_rate_input" class="form-control" step="1" min="0" max="100" placeholder="e.g. 18" value="{{ old('gst_rate', (int)$product->gst_rate) }}" required>
                            </div>

                            <!-- Brand -->
                            <div class="col-md-6">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <label class="form-label fw-semibold text-slate-700 mb-0">Brand <span class="text-muted fw-normal fs-12">(Optional)</span></label>
                                    <button type="button" class="btn btn-link btn-sm text-decoration-none p-0 fw-semibold text-primary" data-bs-toggle="modal" data-bs-target="#quickBrandModal">
                                        <iconify-icon icon="solar:add-circle-bold-duotone" class="align-middle me-1"></iconify-icon>+ Add New Brand
                                    </button>
                                </div>
                                <select name="brand_id" id="brand_id_select" class="form-select s-select" data-placeholder="— Select Brand —">
                                    <option value="">Select Brand</option>
                                    @foreach($brands as $b)
                                        <option value="{{ $b->id }}" {{ old('brand_id', $product->brand_id) == $b->id ? 'selected' : '' }}>{{ $b->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Unit of Measure -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-slate-700">Unit of Measure <span class="text-danger">*</span></label>
                                <select name="unit_of_measure" id="uom_select" class="form-select s-select" required>
                                    @foreach($units as $u)
                                        <option value="{{ $u }}" {{ old('unit_of_measure', $product->unit_of_measure) == $u ? 'selected' : '' }}>{{ $u }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Barcode Generator Section -->
                            <div class="col-12 mt-2">
                                <div class="p-3 rounded-3 border bg-light-subtle">
                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2">
                                        <label class="form-label fw-bold text-slate-800 mb-0 d-flex align-items-center gap-1.5">
                                            <iconify-icon icon="solar:barcode-bold-duotone" class="text-primary fs-18"></iconify-icon>
                                            Barcode / EAN Generator
                                            <span class="text-muted fw-normal fs-12">(Optional)</span>
                                        </label>
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="fs-12 text-muted fw-semibold">Format:</span>
                                            <select name="barcode_format" id="barcode_format_select" class="form-select form-select-sm py-1 px-2 fw-semibold text-dark border-secondary-subtle" style="width: 155px;">
                                                <option value="EAN13" {{ old('barcode_format', 'EAN13') === 'EAN13' ? 'selected' : '' }}>EAN-13 (Standard Retail)</option>
                                                <option value="CODE128" {{ old('barcode_format') === 'CODE128' ? 'selected' : '' }}>Code 128 (Alpha)</option>
                                                <option value="EAN8" {{ old('barcode_format') === 'EAN8' ? 'selected' : '' }}>EAN-8 (8 Digits)</option>
                                            </select>
                                        </div>
                                    </div>

                                    <div class="input-group">
                                        <span class="input-group-text bg-white border-end-0 text-muted">
                                            <iconify-icon icon="solar:scanner-bold-duotone" class="fs-18"></iconify-icon>
                                        </span>
                                        <input type="text" name="barcode" id="barcode_input"
                                            class="form-control border-start-0 fw-bold text-dark font-monospace"
                                            placeholder="Enter barcode or click 'Generate'..."
                                            value="{{ old('barcode', $product->barcode) }}"
                                            autocomplete="off">
                                        <button type="button" class="btn btn-primary fw-semibold px-3" id="btn_generate_barcode">
                                            <iconify-icon icon="solar:magic-stick-3-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Generate Barcode
                                        </button>
                                        <button type="button" class="btn btn-outline-secondary fw-semibold px-2.5" id="btn_toggle_preview" title="Toggle Preview">
                                            <iconify-icon icon="solar:eye-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Preview
                                        </button>
                                    </div>

                                    <!-- Feedback message (validation / duplicate status) -->
                                    <div id="barcode_feedback" class="mt-1 small fw-semibold">
                                        @error('barcode')
                                            <span class="text-danger d-flex align-items-center gap-1">
                                                <iconify-icon icon="solar:danger-triangle-bold" class="fs-15"></iconify-icon> {{ $message }}
                                            </span>
                                        @enderror
                                    </div>

                                    <!-- Live Preview & Action Container -->
                                    <div id="barcode_preview_card" class="mt-3 p-3 bg-white rounded-3 border d-none">
                                        <div class="row align-items-center g-3">
                                            <div class="col-md-7 text-center">
                                                <div class="border rounded-2 p-2 bg-white d-inline-block shadow-xs" style="max-width: 100%;">
                                                    <div class="fw-bold text-truncate text-slate-800 small mb-1" id="preview_label_title" style="max-width: 260px;">{{ $product->product_name }}</div>
                                                    <!-- Barcode Canvas / SVG -->
                                                    <svg id="barcode_svg"></svg>
                                                    <div class="d-none"><canvas id="barcode_canvas"></canvas></div>
                                                </div>
                                            </div>
                                            <div class="col-md-5">
                                                <div class="d-flex flex-column gap-2">
                                                    <span class="text-muted fs-11 fw-semibold text-uppercase">Barcode Actions</span>
                                                    <button type="button" class="btn btn-outline-dark btn-sm fw-semibold w-100 text-start" id="btn_download_barcode">
                                                        <iconify-icon icon="solar:download-square-bold-duotone" class="align-middle me-1 text-primary fs-16"></iconify-icon> Download Barcode (PNG)
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm fw-semibold w-100 text-start" id="btn_print_barcode">
                                                        <iconify-icon icon="solar:printer-bold-duotone" class="align-middle me-1 text-success fs-16"></iconify-icon> Print Label Sticker
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Product Image & Actions -->
            <div class="col-lg-4">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2">
                            <iconify-icon icon="solar:camera-bold-duotone" class="text-primary fs-20"></iconify-icon>
                            2. Product Image
                        </h5>
                    </div>
                    <div class="card-body p-4 text-center">
                        <div class="mb-3">
                            <div class="position-relative d-inline-block">
                                @if($product->product_image)
                                    @php
                                        $imgSrc = (str_starts_with($product->product_image, 'http://') || str_starts_with($product->product_image, 'https://') || str_starts_with($product->product_image, '/'))
                                            ? $product->product_image
                                            : asset('storage/' . $product->product_image);
                                    @endphp
                                    <img id="product_image_preview" src="{{ $imgSrc }}" alt="Preview" class="rounded-4 border shadow-sm" style="width: 160px; height: 160px; object-fit: cover;">
                                @else
                                    <img id="product_image_preview" src="#" alt="Preview" class="rounded-4 border shadow-sm d-none" style="width: 160px; height: 160px; object-fit: cover;">
                                    <div id="product_image_placeholder" class="rounded-4 bg-light border d-flex flex-column align-items-center justify-content-center text-muted mx-auto" style="width: 160px; height: 160px;">
                                        <iconify-icon icon="solar:gallery-wide-bold-duotone" class="fs-40 opacity-50 mb-1"></iconify-icon>
                                        <span class="fs-12">Upload Image</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                        <input type="hidden" name="fetched_image_url" id="fetched_image_url_input" value="{{ $product->product_image }}">
                        <input type="file" name="product_image" id="product_image_input" class="form-control form-control-sm" accept="image/jpeg,image/png,image/webp">
                        <span class="form-text text-muted fs-11 mt-1 d-block">JPG, PNG or WEBP up to 2MB</span>
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <button type="submit" id="submit-btn" class="btn btn-primary btn-lg w-100 fw-bold shadow-sm mb-2">
                            <iconify-icon icon="solar:check-circle-bold-duotone" class="align-middle me-1 fs-20"></iconify-icon>
                            Update Master Product
                        </button>
                        <a href="{{ route('adc.local-products.index') }}" class="btn btn-light w-100 text-slate-600 fw-semibold">Cancel</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <!-- Quick Add Brand Modal -->
    <div class="modal fade" id="quickBrandModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-4 border-0 shadow">
                <div class="modal-header border-bottom">
                    <h5 class="modal-title fw-bold text-slate-800">Add New Brand</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div id="quick-brand-alert" class="alert alert-danger d-none mb-3 py-2 fs-13"></div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Brand Name <span class="text-danger">*</span></label>
                        <input type="text" id="quick_brand_name" class="form-control" placeholder="e.g. Amul, Nestle, Local Dairy">
                    </div>
                </div>
                <div class="modal-footer border-top">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="save-quick-brand-btn" class="btn btn-primary fw-bold px-3">
                        <iconify-icon icon="solar:check-circle-bold-duotone" class="align-middle me-1"></iconify-icon> Save & Select Brand
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof initSUIDropdown === 'function') {
                initSUIDropdown();
            }

            const categorySelect = document.getElementById('category_id_select');
            const hsnSelect = document.getElementById('hsn_code_select');
            const gstInput = document.getElementById('gst_rate_input');
            const submitBtn = document.getElementById('submit-btn');
            const hsnBanner = document.getElementById('hsn-error-banner');

            let hsnGstMap = {};

            const currentGst = "{{ old('gst_rate', (int)$product->gst_rate) }}";

            function syncTomSelect(selectEl, targetValue = null) {
                if (!selectEl) return;
                var valToSet = (targetValue !== null && targetValue !== undefined) ? String(targetValue) : selectEl.value;
                if (typeof TomSelect !== 'undefined' && selectEl.tomselect) {
                    if (!valToSet && valToSet !== '0') {
                        var selectedOpt = selectEl.querySelector('option[selected]');
                        if (selectedOpt) valToSet = selectedOpt.value;
                    }
                    selectEl.tomselect.clear(true);
                    selectEl.tomselect.clearOptions();
                    var parsedOptions = [];
                    for (var i = 0; i < selectEl.options.length; i++) {
                        var opt = selectEl.options[i];
                        parsedOptions.push({
                            value: opt.value,
                            text: opt.text,
                            sort_index: i
                        });
                    }
                    selectEl.tomselect.addOptions(parsedOptions);
                    if (valToSet !== null && valToSet !== undefined && valToSet !== '') {
                        selectEl.tomselect.setValue(valToSet, true);
                    }
                    selectEl.tomselect.refreshOptions(false);
                } else {
                    if (valToSet !== null && valToSet !== undefined && valToSet !== '') {
                        selectEl.value = valToSet;
                    }
                    if (typeof initSUIDropdown === 'function' && selectEl.parentNode) {
                        initSUIDropdown(selectEl.parentNode);
                    }
                }

                if (typeof TomSelect !== 'undefined' && selectEl.tomselect && !selectEl.tomselect._hasChangeSync) {
                    selectEl.tomselect._hasChangeSync = true;
                    selectEl.tomselect.on('change', function () {
                        selectEl.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                }
            }

            function setGstValue(gstValue) {
                if (gstValue !== undefined && gstValue !== null && gstValue !== '') {
                    const roundedGst = String(Math.round(parseFloat(gstValue)));
                    if (gstInput) {
                        gstInput.value = roundedGst;
                        syncTomSelect(gstInput, roundedGst);
                    }
                }
            }

            function loadAllHsns(selectedHsn = null) {
                hsnSelect.innerHTML = '<option value="">Loading HSNs...</option>';
                syncTomSelect(hsnSelect);

                fetch(`{{ route('adc.local-products.category-hsn') }}`)
                    .then(res => res.json())
                    .then(data => {
                        hsnSelect.innerHTML = '<option value="">Select HSN Code</option>';
                        hsnGstMap = {};
                        if (data.success && data.hsns && data.hsns.length > 0) {
                            hsnBanner.classList.add('d-none');
                            submitBtn.disabled = false;

                            data.hsns.forEach(hsn => {
                                const codeKey = String(hsn.hsn_code).trim();
                                hsnGstMap[codeKey] = hsn.gst_percentage;

                                const opt = document.createElement('option');
                                opt.value = hsn.hsn_code;
                                opt.dataset.gst = hsn.gst_percentage;
                                opt.textContent = `${hsn.hsn_code} (${hsn.description || 'General'})`;
                                if (selectedHsn && String(selectedHsn).trim() === codeKey) {
                                    opt.selected = true;
                                }
                                hsnSelect.appendChild(opt);
                            });
                            syncTomSelect(hsnSelect, selectedHsn);
                            if (currentGst !== '') {
                                setGstValue(currentGst);
                            }
                        } else {
                            hsnBanner.classList.remove('d-none');
                            submitBtn.disabled = true;
                            hsnSelect.innerHTML = '<option value="">No HSN codes available</option>';
                            syncTomSelect(hsnSelect);
                        }
                    })
                    .catch(err => {
                        hsnSelect.innerHTML = '<option value="">Error loading HSNs</option>';
                        syncTomSelect(hsnSelect);
                    });
            }

            hsnSelect.addEventListener('change', function () {
                const val = this.value ? String(this.value).trim() : '';
                if (val && hsnGstMap[val] !== undefined && hsnGstMap[val] !== null) {
                    setGstValue(hsnGstMap[val]);
                }
            });

            loadAllHsns("{{ old('hsn_code', $product->hsn_code) }}");

            // Quick Add Brand
            const saveBrandBtn = document.getElementById('save-quick-brand-btn');
            const brandInput = document.getElementById('quick_brand_name');
            const brandAlert = document.getElementById('quick-brand-alert');
            const brandSelect = document.getElementById('brand_id_select');

            if (saveBrandBtn && brandInput && brandSelect) {
                saveBrandBtn.addEventListener('click', function () {
                    const brandName = brandInput.value.trim();
                    if (!brandName) {
                        brandAlert.textContent = 'Please enter a brand name.';
                        brandAlert.classList.remove('d-none');
                        return;
                    }

                    saveBrandBtn.disabled = true;
                    fetch(`{{ route('adc.local-products.brands.quick-store') }}`, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({ brand_name: brandName })
                    })
                    .then(res => res.json())
                    .then(data => {
                        saveBrandBtn.disabled = false;
                        if (data.success) {
                            const opt = document.createElement('option');
                            opt.value = data.brand.id;
                            opt.textContent = data.brand.name;
                            opt.selected = true;
                            brandSelect.appendChild(opt);
                            syncTomSelect(brandSelect, data.brand.id);

                            brandInput.value = '';
                            const modal = bootstrap.Modal.getInstance(document.getElementById('quickBrandModal'));
                            if (modal) modal.hide();
                        } else {
                            brandAlert.textContent = data.message || 'Failed to create brand.';
                            brandAlert.classList.remove('d-none');
                        }
                    });
                });
            }

            // ── Live Product Search Suggestions ──
            const productNameInput = document.getElementById('product_name_input');
            const msmeProductIdInput = document.getElementById('msme_product_id_input');
            const suggestionsBox = document.getElementById('product-suggestions-box');
            const suggestionsList = document.getElementById('suggestions-list');
            const linkedBadge = document.getElementById('linked-msme-badge');
            const linkedMsmeName = document.getElementById('linked-msme-name');
            const unlinkBtn = document.getElementById('unlink-msme-btn');
            const uomSelect = document.getElementById('uom_select');
            const barcodeInput = document.getElementById('barcode_input');

            if (productNameInput && suggestionsBox && suggestionsList) {
                let searchTimeout = null;
                let isSelectingSuggestion = false;
                let isProductSelected = false;

                function hideSuggestions() {
                    suggestionsBox.classList.add('d-none');
                    suggestionsBox.style.display = 'none';
                    suggestionsList.innerHTML = '';
                }

                function showSuggestions() {
                    if (isProductSelected || isSelectingSuggestion) return;
                    suggestionsBox.classList.remove('d-none');
                    suggestionsBox.style.display = 'block';
                }

                productNameInput.addEventListener('input', function () {
                    if (isSelectingSuggestion) return;

                    if (isProductSelected) {
                        isProductSelected = false;
                    }

                    const query = this.value.trim();
                    clearTimeout(searchTimeout);

                    if (query.length < 2) {
                        hideSuggestions();
                        return;
                    }

                    searchTimeout = setTimeout(function () {
                        if (isSelectingSuggestion || isProductSelected) return;
                        fetch(`{{ route('adc.local-products.search-suggestions') }}?q=${encodeURIComponent(query)}`)
                            .then(res => res.json())
                            .then(data => {
                                if (isSelectingSuggestion || isProductSelected || productNameInput.value.trim() !== query) return;
                                if (data.success) {
                                    renderSuggestions(data.suggestions, query);
                                }
                            });
                    }, 250);
                });

                function renderSuggestions(suggestions, query) {
                    if (isProductSelected || isSelectingSuggestion) return;
                    suggestionsList.innerHTML = '';

                    if (suggestions.length > 0) {
                        suggestions.forEach(item => {
                            const a = document.createElement('a');
                            a.href = 'javascript:void(0)';
                            a.className = 'list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2 px-3 border-bottom';

                            a.innerHTML = `
                            <div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary-subtle text-primary border-primary-subtle fs-11 px-2 py-0.5 rounded-pill">✓ MSME Product</span>
                                    <strong class="text-slate-800 fs-14">${item.product_name}</strong>
                                </div>
                                <span class="text-muted fs-12 d-block mt-0.5">Code: <code>${item.product_code}</code> | Cat: ${item.category_name} | Brand: ${item.brand_name} | Barcode: ${item.barcode || 'N/A'}</span>
                            </div>
                            <iconify-icon icon="solar:arrow-right-bold-duotone" class="fs-16 text-primary"></iconify-icon>
                        `;

                            a.addEventListener('click', function (e) {
                                e.preventDefault();
                                e.stopPropagation();
                                selectSuggestion(item);
                            });

                            suggestionsList.appendChild(a);
                        });
                    }

                    // Add "Create New Product" option
                    const createNewItem = document.createElement('a');
                    createNewItem.href = 'javascript:void(0)';
                    createNewItem.className = 'list-group-item list-group-item-action bg-light text-primary fw-semibold py-2.5 px-3 d-flex align-items-center gap-2 border-top';
                    createNewItem.innerHTML = `
                        <iconify-icon icon="solar:add-circle-bold-duotone" class="fs-18 text-primary"></iconify-icon>
                        <div>
                            <span class="d-block fs-14 text-primary">+ Create New Product "${query}"</span>
                            <span class="text-muted fs-11 font-normal">Add as a new local product if not found in catalog</span>
                        </div>
                    `;
                    createNewItem.addEventListener('click', function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        isSelectingSuggestion = true;
                        isProductSelected = true;
                        clearTimeout(searchTimeout);
                        msmeProductIdInput.value = '';
                        if (linkedBadge) linkedBadge.classList.add('d-none');
                        hideSuggestions();
                        setTimeout(() => { isSelectingSuggestion = false; }, 400);
                    });
                    suggestionsList.appendChild(createNewItem);

                    showSuggestions();
                }

                function selectSuggestion(item) {
                    isSelectingSuggestion = true;
                    isProductSelected = true;
                    clearTimeout(searchTimeout);
                    hideSuggestions();

                    productNameInput.value = item.product_name;

                    if (item.type === 'MSME' || item.msme_product_id) {
                        msmeProductIdInput.value = item.msme_product_id || item.id;
                        if (linkedBadge) {
                            if (linkedMsmeName) {
                                linkedMsmeName.textContent = item.product_name + (item.product_code ? ' (' + item.product_code + ')' : '');
                            }
                            linkedBadge.classList.remove('d-none');
                        }
                    } else {
                        msmeProductIdInput.value = '';
                        if (linkedBadge) linkedBadge.classList.add('d-none');
                    }

                    if (item.category_id && categorySelect) {
                        categorySelect.value = item.category_id;
                        syncTomSelect(categorySelect, item.category_id);
                    }

                    if (brandSelect) {
                        let foundOpt = null;
                        if (item.brand_id) {
                            foundOpt = Array.from(brandSelect.options).find(o => String(o.value) === String(item.brand_id));
                        }
                        if (!foundOpt && item.brand_name && item.brand_name !== 'Unbranded' && item.brand_name !== '—') {
                            foundOpt = Array.from(brandSelect.options).find(o => o.text.trim().toLowerCase() === item.brand_name.trim().toLowerCase());
                        }
                        if (!foundOpt && item.brand_name && item.brand_name !== 'Unbranded' && item.brand_name !== '—') {
                            foundOpt = document.createElement('option');
                            foundOpt.value = item.brand_id || '';
                            foundOpt.textContent = item.brand_name;
                            brandSelect.appendChild(foundOpt);
                        }
                        if (foundOpt) {
                            brandSelect.value = foundOpt.value;
                            syncTomSelect(brandSelect, foundOpt.value);
                        }
                    }

                    if (item.barcode && barcodeInput) {
                        barcodeInput.value = item.barcode;
                    }

                    if ((item.uom || item.unit_of_measure) && uomSelect) {
                        const uomVal = item.uom || item.unit_of_measure;
                        uomSelect.value = uomVal;
                        syncTomSelect(uomSelect, uomVal);
                    }

                    if (item.hsn_code && hsnSelect) {
                        let hsnOpt = Array.from(hsnSelect.options).find(o => String(o.value).trim() === String(item.hsn_code).trim());
                        if (!hsnOpt) {
                            hsnOpt = document.createElement('option');
                            hsnOpt.value = item.hsn_code;
                            hsnOpt.textContent = `${item.hsn_code} (General)`;
                            hsnSelect.appendChild(hsnOpt);
                        }
                        hsnOpt.selected = true;
                        syncTomSelect(hsnSelect, item.hsn_code);

                        if (item.gst_rate !== undefined && item.gst_rate !== null) {
                            setGstValue(item.gst_rate);
                        }
                    } else if (item.gst_rate !== undefined && item.gst_rate !== null) {
                        setGstValue(item.gst_rate);
                    }

                    // Auto-fill Product Image URL & Preview if available
                    const imgUrl = item.image_url || item.featured_image || '';
                    const fetchedImgInput = document.getElementById('fetched_image_url_input');
                    if (fetchedImgInput) {
                        fetchedImgInput.value = item.featured_image || item.image_url || '';
                    }

                    const imgPreview = document.getElementById('product_image_preview');
                    const imgPlaceholder = document.getElementById('product_image_placeholder');
                    if (imgUrl && imgPreview) {
                        imgPreview.src = imgUrl;
                        imgPreview.classList.remove('d-none');
                        if (imgPlaceholder) imgPlaceholder.classList.add('d-none');
                    }

                    hideSuggestions();
                    setTimeout(() => { isSelectingSuggestion = false; }, 400);
                }

                if (unlinkBtn) {
                    unlinkBtn.addEventListener('click', function () {
                        msmeProductIdInput.value = '';
                        isProductSelected = false;
                        if (linkedBadge) linkedBadge.classList.add('d-none');
                    });
                }

                document.addEventListener('click', function (e) {
                    if (!productNameInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                        hideSuggestions();
                    }
                });

                const imgInput = document.getElementById('product_image_input');
                if (imgInput) {
                    imgInput.addEventListener('change', function () {
                        const file = this.files[0];
                        if (file) {
                            const fetchedImgInput = document.getElementById('fetched_image_url_input');
                            if (fetchedImgInput) fetchedImgInput.value = '';
                            const imgPreview = document.getElementById('product_image_preview');
                            const imgPlaceholder = document.getElementById('product_image_placeholder');
                            const reader = new FileReader();
                            reader.onload = function (e) {
                                if (imgPreview) {
                                    imgPreview.src = e.target.result;
                                    imgPreview.classList.remove('d-none');
                                }
                                if (imgPlaceholder) imgPlaceholder.classList.add('d-none');
                            }
                            reader.readAsDataURL(file);
                        }
                    });
                }
            }

            // ── Barcode Generator & Live Preview Logic ──
            const barcodeInput = document.getElementById('barcode_input');
            const formatSelect = document.getElementById('barcode_format_select');
            const btnGenerateBarcode = document.getElementById('btn_generate_barcode');
            const btnTogglePreview = document.getElementById('btn_toggle_preview');
            const previewCard = document.getElementById('barcode_preview_card');
            const previewTitle = document.getElementById('preview_label_title');
            const barcodeFeedback = document.getElementById('barcode_feedback');
            const btnDownloadBarcode = document.getElementById('btn_download_barcode');
            const btnPrintBarcode = document.getElementById('btn_print_barcode');
            const nameInput = document.getElementById('product_name_input');
            const currentProductId = {{ $product->id }};

            function getSelectedFormat() {
                return formatSelect ? formatSelect.value : 'CODE128';
            }

            function updatePreviewTitle() {
                if (previewTitle) {
                    const nameVal = nameInput ? nameInput.value.trim() : '';
                    previewTitle.textContent = nameVal || '{{ addslashes($product->product_name) }}';
                }
            }

            function renderLiveBarcode() {
                const rawCode = barcodeInput ? barcodeInput.value.trim() : '';
                if (!rawCode) {
                    if (previewCard) previewCard.classList.add('d-none');
                    return;
                }

                updatePreviewTitle();
                const cleanCode = rawCode.replace(/\s+/g, '');
                const fmt = getSelectedFormat();
                let jsFmt = fmt;

                if (/^\d{12,13}$/.test(cleanCode)) {
                    jsFmt = 'EAN13';
                } else if (/^\d{7,8}$/.test(cleanCode)) {
                    jsFmt = 'EAN8';
                } else if (fmt === 'EAN13' && !/^\d+$/.test(cleanCode)) {
                    jsFmt = 'CODE128';
                }

                try {
                    if (typeof JsBarcode === 'function') {
                        // High-contrast clean preview
                        JsBarcode('#barcode_svg', cleanCode, {
                            format: jsFmt,
                            lineColor: '#000000',
                            width: 2.5,
                            height: 85,
                            displayValue: true,
                            font: 'Arial, Helvetica, sans-serif',
                            fontSize: 17,
                            textMargin: 8,
                            background: '#ffffff',
                            margin: 10
                        });

                        // High-resolution for download
                        JsBarcode('#barcode_canvas', cleanCode, {
                            format: jsFmt,
                            lineColor: '#000000',
                            width: 3.5,
                            height: 120,
                            displayValue: true,
                            font: 'Arial, Helvetica, sans-serif',
                            fontSize: 22,
                            textMargin: 10,
                            background: '#ffffff',
                            margin: 25
                        });

                        if (previewCard) previewCard.classList.remove('d-none');
                    }
                } catch (err) {
                    console.warn('JsBarcode preview warning:', err);
                    try {
                        JsBarcode('#barcode_svg', cleanCode, {
                            format: 'CODE128',
                            lineColor: '#000000',
                            width: 2.2,
                            height: 75,
                            displayValue: true,
                            fontSize: 15,
                            background: '#ffffff',
                            margin: 8
                        });
                        JsBarcode('#barcode_canvas', cleanCode, {
                            format: 'CODE128',
                            lineColor: '#000000',
                            width: 3.2,
                            height: 110,
                            displayValue: true,
                            fontSize: 20,
                            background: '#ffffff',
                            margin: 20
                        });
                        if (previewCard) previewCard.classList.remove('d-none');
                    } catch (e2) {}
                }
            }

            let checkTimeout = null;
            function triggerDuplicateCheck() {
                const code = barcodeInput ? barcodeInput.value.trim() : '';
                if (!code) {
                    if (barcodeFeedback) barcodeFeedback.innerHTML = '';
                    return;
                }

                if (barcodeFeedback) {
                    barcodeFeedback.className = 'mt-1 small fw-semibold text-muted d-flex align-items-center gap-1';
                    barcodeFeedback.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span> Validating barcode...';
                }

                fetch("{{ route('adc.barcode.check-duplicate') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        barcode: code,
                        format: getSelectedFormat(),
                        exclude_id: currentProductId,
                        type: 'local_product'
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (!barcodeFeedback) return;
                    if (data.valid) {
                        barcodeFeedback.className = 'mt-1 small fw-semibold text-success d-flex align-items-center gap-1';
                        barcodeFeedback.innerHTML = '<iconify-icon icon="solar:check-circle-bold" class="fs-15"></iconify-icon> ' + data.message;
                    } else {
                        barcodeFeedback.className = 'mt-1 small fw-semibold text-danger d-flex align-items-center gap-1';
                        barcodeFeedback.innerHTML = '<iconify-icon icon="solar:danger-triangle-bold" class="fs-15"></iconify-icon> ' + data.message;
                    }
                })
                .catch(err => {
                    console.error('Check duplicate error:', err);
                });
            }

            if (btnGenerateBarcode) {
                btnGenerateBarcode.addEventListener('click', function () {
                    const fmt = getSelectedFormat();
                    btnGenerateBarcode.disabled = true;
                    btnGenerateBarcode.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Generating...';

                    fetch("{{ route('adc.barcode.generate') }}?format=" + encodeURIComponent(fmt), {
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        }
                    })
                    .then(r => r.json())
                    .then(data => {
                        btnGenerateBarcode.disabled = false;
                        btnGenerateBarcode.innerHTML = '<iconify-icon icon="solar:magic-stick-3-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Generate Barcode';

                        if (data.success && data.barcode) {
                            if (barcodeInput) {
                                barcodeInput.value = data.barcode;
                                renderLiveBarcode();
                                triggerDuplicateCheck();
                            }
                        }
                    })
                    .catch(err => {
                        btnGenerateBarcode.disabled = false;
                        btnGenerateBarcode.innerHTML = '<iconify-icon icon="solar:magic-stick-3-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Generate Barcode';
                        console.error('Generate barcode error:', err);
                    });
                });
            }

            if (barcodeInput) {
                barcodeInput.addEventListener('input', function () {
                    renderLiveBarcode();
                    clearTimeout(checkTimeout);
                    checkTimeout = setTimeout(triggerDuplicateCheck, 400);
                });
            }

            if (formatSelect) {
                formatSelect.addEventListener('change', function () {
                    renderLiveBarcode();
                    triggerDuplicateCheck();
                });
            }

            if (btnTogglePreview) {
                btnTogglePreview.addEventListener('click', function () {
                    if (previewCard) {
                        if (previewCard.classList.contains('d-none')) {
                            renderLiveBarcode();
                            previewCard.classList.remove('d-none');
                        } else {
                            previewCard.classList.add('d-none');
                        }
                    }
                });
            }

            if (btnDownloadBarcode) {
                btnDownloadBarcode.addEventListener('click', function () {
                    const code = barcodeInput ? barcodeInput.value.trim() : 'barcode';
                    const canvas = document.getElementById('barcode_canvas');
                    if (!canvas) {
                        alert('No barcode available to download.');
                        return;
                    }
                    const a = document.createElement('a');
                    a.download = 'barcode-' + code + '.png';
                    a.href = canvas.toDataURL('image/png');
                    document.body.appendChild(a);
                    a.click();
                    document.body.removeChild(a);
                });
            }

            if (btnPrintBarcode) {
                btnPrintBarcode.addEventListener('click', function () {
                    const code = barcodeInput ? barcodeInput.value.trim() : '';
                    const canvas = document.getElementById('barcode_canvas');
                    if (!canvas || !code) return;
                    const dataUrl = canvas.toDataURL('image/png');
                    const prodName = nameInput ? nameInput.value.trim() : '{{ addslashes($product->product_name) }}';

                    const printWin = window.open('', '_blank', 'width=450,height=380');
                    printWin.document.write(`
                        <!DOCTYPE html>
                        <html>
                        <head>
                            <title>Print Barcode - ${code}</title>
                            <style>
                                @page { size: 50mm 25mm; margin: 0; }
                                body {
                                    margin: 0;
                                    padding: 4px;
                                    font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
                                    text-align: center;
                                    display: flex;
                                    flex-direction: column;
                                    justify-content: center;
                                    align-items: center;
                                    height: 100vh;
                                    box-sizing: border-box;
                                }
                                .title {
                                    font-size: 11px;
                                    font-weight: bold;
                                    max-width: 175px;
                                    white-space: nowrap;
                                    overflow: hidden;
                                    text-overflow: ellipsis;
                                    margin-bottom: 2px;
                                }
                                img {
                                    max-width: 175px;
                                    max-height: 65px;
                                    object-fit: contain;
                                }
                            </style>
                        </head>
                        <body onload="window.print(); window.close();">
                            <div class="title">${prodName}</div>
                            <img src="${dataUrl}">
                        </body>
                        </html>
                    `);
                    printWin.document.close();
                });
            }

            // Initial render if value exists on page load
            if (barcodeInput && barcodeInput.value.trim()) {
                renderLiveBarcode();
            }
        });
    </script>
@endpush
