@extends('layouts.ddc')

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
                            <a href="{{ route('ddc.local-products.index') }}" class="btn btn-outline-secondary btn-sm fw-semibold">
                                <iconify-icon icon="solar:arrow-left-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Back to Products
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Category HSN Warning Banner (Hidden by default) -->
    <div id="hsn-error-banner" class="alert alert-danger border-0 shadow-sm rounded-3 mb-4 d-none" role="alert">
        <div class="d-flex align-items-center gap-2">
            <iconify-icon icon="solar:danger-bold-duotone" class="fs-22 text-danger flex-shrink-0"></iconify-icon>
            <div>
                <strong class="d-block" id="hsn-error-message">HSN Code is not mapped with this Category. Please configure
                    it from the Admin Panel.</strong>
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

    <form action="{{ route('ddc.local-products.update', $product->id) }}" method="POST" enctype="multipart/form-data"
        id="edit-local-product-form">
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
                            <!-- Product Name with Live Search Suggestions -->
                            <div class="col-md-12 position-relative">
                                <label class="form-label fw-semibold text-slate-700">Product Name <span
                                        class="text-danger">*</span></label>
                                <input type="text" name="product_name" id="product_name_input"
                                    class="form-control form-control-lg fs-15"
                                    value="{{ old('product_name', $product->product_name) }}" autocomplete="off" required>
                                <input type="hidden" name="msme_product_id" id="msme_product_id_input"
                                    value="{{ old('msme_product_id', $product->msme_product_id) }}">

                                <!-- Linked MSME Product Badge -->
                                <div id="linked-msme-badge" class="mt-2 {{ $product->msmeProduct ? '' : 'd-none' }}">
                                    <span
                                        class="badge bg-success-subtle text-success fs-12 px-3 py-1.5 rounded-pill border border-success-subtle d-inline-flex align-items-center gap-1">
                                        <iconify-icon icon="solar:link-bold-duotone" class="fs-14"></iconify-icon>
                                        Linked with MSME Catalog Product (<span
                                            id="linked-msme-name">{{ $product->msmeProduct ? $product->msmeProduct->product_name . ' (' . $product->msmeProduct->product_code . ')' : '' }}</span>).
                                        Enables Website vs Local Price Comparison.
                                        <button type="button" class="btn-close ms-2 fs-10" id="unlink-msme-btn"
                                            aria-label="Unlink"></button>
                                    </span>
                                </div>

                                <!-- Live Suggestions Dropdown Box -->
                                <div id="product-suggestions-box"
                                    class="position-absolute start-0 end-0 top-100 mt-1 shadow-lg rounded-3 bg-white border d-none"
                                    style="z-index: 1060; max-height: 320px; overflow-y: auto;">
                                    <div id="suggestions-list" class="list-group list-group-flush"></div>
                                </div>
                            </div>

                            <!-- Category (Searchable Dropdown) -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-slate-700">Category <span
                                        class="text-danger">*</span></label>
                                <select name="category_id" id="category_id" class="form-select s-select"
                                    data-placeholder="Search & Select Category" required>
                                    <option value="">— Select Category —</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Brand (Searchable Dropdown) -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-slate-700">Brand <span class="text-muted fw-normal fs-12">(Optional)</span></label>
                                <select name="brand_id" id="brand_id" class="form-select s-select"
                                    data-placeholder="Search & Select Brand">
                                    <option value="">— Select Brand —</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- HSN Code (Searchable Dynamic Dropdown) -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-slate-700">HSN Code <span
                                        class="text-danger">*</span></label>
                                <select name="hsn_code" id="hsn_code_select" class="form-select s-select"
                                    data-placeholder="Select HSN Code" required>
                                    <option value="{{ old('hsn_code', $product->hsn_code) }}" selected>{{ old('hsn_code', $product->hsn_code) }}</option>
                                </select>
                            </div>

                            <!-- GST Rate -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-slate-700">GST Rate (%) <span class="text-danger">*</span></label>
                                <input type="number" name="gst_rate" id="gst_rate" class="form-control fw-bold text-slate-800" step="1" min="0" max="100" placeholder="0" value="{{ old('gst_rate', (int)$product->gst_rate) }}" required>
                            </div>

                            <!-- Unit of Measure -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-slate-700">Unit of Measure (UOM) <span
                                        class="text-danger">*</span></label>
                                <select name="unit_of_measure" id="unit_of_measure_select" class="form-select s-select"
                                    data-placeholder="Select Unit" required>
                                    <option value="">— Select Unit —</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit }}" {{ old('unit_of_measure', $product->unit_of_measure) == $unit ? 'selected' : '' }}>{{ $unit }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Barcode -->
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-slate-700">Barcode <span
                                        class="text-muted font-normal">(Optional)</span></label>
                                <input type="text" name="barcode" id="barcode_input" class="form-control"
                                    value="{{ old('barcode', $product->barcode) }}">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Column: Product Image & Update Card -->
            <div class="col-lg-4">
                <!-- Submit Action Card -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title fw-bold text-slate-800 mb-0">Update & Save</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-grid gap-2">
                            <button type="submit" id="submit-btn" class="btn btn-primary btn-lg fw-bold">
                                <iconify-icon icon="solar:disk-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                                Update Product Master
                            </button>
                            <a href="{{ route('ddc.local-products.index') }}" class="btn btn-outline-secondary">Cancel</a>
                        </div>
                    </div>
                </div>

                <!-- Product Image Upload -->
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title fw-bold text-slate-800 mb-0">Product Image</h5>
                    </div>
                    <div class="card-body">
                        <input type="hidden" name="fetched_image_url" id="fetched_image_url_input" value="{{ $product->product_image }}">
                        <input type="file" name="product_image" id="product_image_input" class="form-control mb-3"
                            accept="image/*">
                        <div class="border rounded-3 bg-light d-flex align-items-center justify-content-center overflow-hidden position-relative"
                            style="height: 180px;">
                            @php
                                $imgSrc = $product->product_image
                                    ? ((str_starts_with($product->product_image, 'http://') || str_starts_with($product->product_image, 'https://') || str_starts_with($product->product_image, '/'))
                                        ? $product->product_image
                                        : asset('storage/' . $product->product_image))
                                    : '';
                            @endphp
                            <img id="product_image_preview"
                                src="{{ $imgSrc }}"
                                class="img-fluid object-fit-contain {{ $product->product_image ? '' : 'd-none' }}"
                                style="max-height: 100%;">
                            <div id="product_image_placeholder"
                                class="text-muted text-center p-3 {{ $product->product_image ? 'd-none' : '' }}">
                                <iconify-icon icon="solar:gallery-bold-duotone"
                                    class="fs-36 text-muted mb-1 d-block"></iconify-icon>
                                <span class="fs-12">No image uploaded yet</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const categorySelect = document.getElementById('category_id');
            const brandSelect = document.getElementById('brand_id');
            const uomSelect = document.getElementById('unit_of_measure_select');
            const barcodeInput = document.getElementById('barcode_input');
            const hsnSelect = document.getElementById('hsn_code_select');
            const gstInput = document.getElementById('gst_rate');
            const hsnBanner = document.getElementById('hsn-error-banner');
            const hsnMessage = document.getElementById('hsn-error-message');
            const submitBtn = document.getElementById('submit-btn');

            const productNameInput = document.getElementById('product_name_input');
            const msmeProductIdInput = document.getElementById('msme_product_id_input');
            const suggestionsBox = document.getElementById('product-suggestions-box');
            const suggestionsList = document.getElementById('suggestions-list');
            const linkedBadge = document.getElementById('linked-msme-badge');
            const linkedMsmeName = document.getElementById('linked-msme-name');
            const unlinkBtn = document.getElementById('unlink-msme-btn');

            const currentHsn = "{{ old('hsn_code', $product->hsn_code) }}";
            const currentGst = "{{ old('gst_rate', (int)$product->gst_rate) }}";
            let hsnGstMap = {};

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

            function loadAllHsns(selectedHsnCode = null) {
                hsnSelect.innerHTML = '<option value="">Loading HSN Codes...</option>';
                syncTomSelect(hsnSelect);

                fetch(`{{ route('ddc.local-products.category-hsn') }}`)
                    .then(res => res.json())
                    .then(data => {
                        hsnSelect.innerHTML = '<option value="">Select HSN Code</option>';
                        hsnGstMap = {};
                        if (data.success && data.hsns && data.hsns.length > 0) {
                            if(hsnBanner) hsnBanner.classList.add('d-none');
                            submitBtn.disabled = false;
                            const targetHsn = selectedHsnCode || currentHsn;

                            data.hsns.forEach(hsn => {
                                const codeKey = String(hsn.hsn_code).trim();
                                hsnGstMap[codeKey] = hsn.gst_percentage;

                                const opt = document.createElement('option');
                                opt.value = hsn.hsn_code;
                                opt.textContent = `${hsn.hsn_code} (${hsn.description || 'General'})`;
                                if (targetHsn && String(targetHsn).trim() === codeKey) {
                                    opt.selected = true;
                                }
                                hsnSelect.appendChild(opt);
                            });
                            syncTomSelect(hsnSelect, targetHsn);
                            if (currentGst !== '') {
                                setGstValue(currentGst);
                            }
                        } else {
                            if(hsnBanner) hsnBanner.classList.remove('d-none');
                            submitBtn.disabled = true;
                            hsnSelect.innerHTML = '<option value="">— No HSN Codes —</option>';
                            syncTomSelect(hsnSelect);
                        }
                    })
                    .catch(err => {
                        console.error(err);
                        hsnSelect.innerHTML = '<option value="">Error loading HSNs</option>';
                        syncTomSelect(hsnSelect);
                    });
            }

            loadAllHsns("{{ old('hsn_code', $product->hsn_code) }}");

            hsnSelect.addEventListener('change', function () {
                const val = this.value ? String(this.value).trim() : '';
                if (val && hsnGstMap[val] !== undefined && hsnGstMap[val] !== null) {
                    setGstValue(hsnGstMap[val]);
                }
            });

            // ── Live Product Search Suggestions ──
            let searchTimeout = null;
            let isSelectingSuggestion = false;

            function hideSuggestions() {
                suggestionsBox.classList.add('d-none');
                suggestionsBox.style.display = 'none';
            }

            function showSuggestions() {
                suggestionsBox.classList.remove('d-none');
                suggestionsBox.style.display = 'block';
            }

            productNameInput.addEventListener('input', function () {
                if (isSelectingSuggestion) return;
                const query = this.value.trim();
                clearTimeout(searchTimeout);

                if (query.length < 2) {
                    hideSuggestions();
                    return;
                }

                searchTimeout = setTimeout(function () {
                    if (isSelectingSuggestion) return;
                    fetch(`{{ route('ddc.local-products.search-suggestions') }}?q=${encodeURIComponent(query)}`)
                        .then(res => res.json())
                        .then(data => {
                            if (isSelectingSuggestion) return;
                            if (data.success) {
                                renderSuggestions(data.suggestions, query);
                            }
                        });
                }, 250);
            });

            function renderSuggestions(suggestions, query) {
                suggestionsList.innerHTML = '';

                if (suggestions.length > 0) {
                    suggestions.forEach(item => {
                        const a = document.createElement('a');
                        a.href = 'javascript:void(0)';
                        a.className = 'list-group-item list-group-item-action d-flex align-items-center justify-content-between py-2 px-3 border-bottom';

                        const isMsme = item.type === 'MSME';
                        const badgeClass = isMsme ? 'bg-primary-subtle text-primary border-primary-subtle' : 'bg-success-subtle text-success border-success-subtle';
                        const badgeLabel = isMsme ? '✓ MSME Product' : '✓ REGIONAL Product';

                        a.innerHTML = `
                        <div>
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge ${badgeClass} fs-11 px-2 py-0.5 rounded-pill">${badgeLabel}</span>
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

                const createNewItem = document.createElement('a');
                createNewItem.href = 'javascript:void(0)';
                createNewItem.className = 'list-group-item list-group-item-action bg-light text-primary fw-semibold py-2.5 px-3 d-flex align-items-center gap-2';
                createNewItem.innerHTML = `
                <iconify-icon icon="solar:add-circle-bold-duotone" class="fs-18"></iconify-icon>
                <div>
                    <span class="d-block fs-14">+ Create New Regional Product "${query}"</span>
                    <span class="text-muted fs-11 font-normal">Auto-generates Regional Product Code (LCLxxxxx)</span>
                </div>
            `;
                createNewItem.addEventListener('click', function (e) {
                    e.preventDefault();
                    e.stopPropagation();
                    isSelectingSuggestion = true;
                    msmeProductIdInput.value = '';
                    linkedBadge.classList.add('d-none');
                    hideSuggestions();
                    setTimeout(() => { isSelectingSuggestion = false; }, 400);
                });
                suggestionsList.appendChild(createNewItem);

                showSuggestions();
            }

            function selectSuggestion(item) {
                isSelectingSuggestion = true;
                productNameInput.value = item.product_name;

                // 1. Linked MSME Product Badge & Input
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

                // 2. Category
                if (item.category_id && categorySelect) {
                    categorySelect.value = item.category_id;
                    syncTomSelect(categorySelect, item.category_id);
                }

                // 3. Brand
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

                // 4. Barcode
                if (item.barcode && barcodeInput) {
                    barcodeInput.value = item.barcode;
                }

                // 5. Unit of Measure (UOM)
                if ((item.uom || item.unit_of_measure) && uomSelect) {
                    const uomVal = item.uom || item.unit_of_measure;
                    uomSelect.value = uomVal;
                    syncTomSelect(uomSelect, uomVal);
                }

                // 6. HSN Code & GST Rate
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
                    linkedBadge.classList.add('d-none');
                });
            }

            document.addEventListener('click', function (e) {
                if (!productNameInput.contains(e.target) && !suggestionsBox.contains(e.target)) {
                    suggestionsBox.classList.add('d-none');
                }
            });

            const imgInput = document.getElementById('product_image_input');
            const imgPreview = document.getElementById('product_image_preview');
            const imgPlaceholder = document.getElementById('product_image_placeholder');

            if (imgInput) {
                imgInput.addEventListener('change', function () {
                    const file = this.files[0];
                    if (file) {
                        const fetchedImgInput = document.getElementById('fetched_image_url_input');
                        if (fetchedImgInput) fetchedImgInput.value = '';
                        const reader = new FileReader();
                        reader.onload = function (e) {
                            imgPreview.src = e.target.result;
                            imgPreview.classList.remove('d-none');
                            imgPlaceholder.classList.add('d-none');
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    </script>
@endpush