@extends('layouts.vendor')

@section('title', 'Edit Product')
@section('page-title', 'Edit Product')

@section('content')
    <div class="row">
        <div class="col-12 mb-3">
            <a href="{{ route('vendor.products.index') }}" class="btn btn-sm btn-outline-secondary">
                <iconify-icon icon="solar:arrow-left-bold-duotone" class="align-middle me-1"></iconify-icon>
                Back to Catalog
            </a>
        </div>
    </div>

    <!-- Alert if Rejected -->
    @if($product->status === 'Rejected')
        <div class="alert alert-danger border-0 shadow-sm mb-4 d-flex align-items-start gap-2">
            <iconify-icon icon="solar:info-circle-bold-duotone" class="fs-22 text-danger mt-0.5"></iconify-icon>
            <div>
                <h6 class="fw-bold text-danger mb-1">Catalog Rejected by Administrator</h6>
                <p class="mb-0 small text-slate-700"><strong>Reason:</strong> {{ $product->admin_remarks }}</p>
                <p class="mb-0 mt-1.5 small text-muted">Please correct the specified issues below and re-submit the product for
                    approval.</p>
            </div>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4" role="alert">
            <h6 class="fw-bold text-danger mb-2">
                <iconify-icon icon="solar:danger-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                Please correct the following errors:
            </h6>
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('vendor.products.update', $product->id) }}" method="POST" id="editProductForm">
        @csrf
        @method('PUT')
        <input type="hidden" name="submit_approval" id="submit_approval_flag" value="0">
        <div class="row">
            <!-- Left Side: Form Details -->
            <div class="col-lg-8">
                <!-- Card 1: General Details -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title fw-bold text-slate-800 mb-0">General Information</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label required fw-medium text-slate-700">Product Name</label>
                                <input type="text" name="product_name" class="form-control"
                                    value="{{ old('product_name', $product->product_name) }}" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium text-slate-700">Barcode / GTIN <span
                                        class="text-muted fw-normal fs-12">(Optional)</span></label>
                                <input type="text" name="barcode" class="form-control"
                                    placeholder="Barcode identifier (Optional)"
                                    value="{{ old('barcode', $product->barcode) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <label class="form-label required fw-medium text-slate-700 mb-0">Brand</label>
                                    <button type="button" class="btn btn-xs btn-link p-0 text-decoration-none fw-semibold text-primary d-flex align-items-center gap-0.5" data-bs-toggle="modal" data-bs-target="#quickAddBrandModal" style="font-size: 12px;">
                                        <iconify-icon icon="solar:add-circle-bold-duotone" class="fs-14"></iconify-icon>
                                        + Add New Brand
                                    </button>
                                </div>
                                <select name="brand_id" id="brandSelect" class="form-select s-select" required
                                    data-placeholder="— Select Brand —">
                                    <option value="">— Select Brand —</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->id }}" {{ old('brand_id', $product->brand_id) == $brand->id ? 'selected' : '' }}>{{ $brand->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required fw-medium text-slate-700">Search Keywords / Tags</label>
                                <input type="text" id="keywordsInput" class="form-control"
                                    placeholder="Type word and press Enter">
                                <div id="keywordsContainer" class="d-flex flex-wrap gap-1 mt-2"></div>
                                <div id="hiddenKeywordsInputs"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 2: Categories, HSN & GST Cascade -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title fw-bold text-slate-800 mb-0">Category & Tax Details</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label required fw-medium text-slate-700">Primary Category</label>
                                <select id="categorySelect" name="category_id" class="form-select s-select" required
                                    data-placeholder="— Select Category —">
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-medium text-slate-700">Sub-Category</label>
                                <select id="subCategorySelect" name="sub_category_id" class="form-select s-select"
                                    data-placeholder="— Select Sub-Category —">
                                    <option value="">— Select Category —</option>
                                    @foreach($subCategories as $sub)
                                        <option value="{{ $sub->id }}" {{ old('sub_category_id', $product->sub_category_id) == $sub->id ? 'selected' : '' }}>{{ $sub->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-medium text-slate-700">Child-Category</label>
                                <select id="childCategorySelect" name="child_category_id" class="form-select s-select"
                                    data-placeholder="— Select Child-Category —">
                                    <option value="">— Select Sub-Category —</option>
                                    @foreach($childCategories as $child)
                                        <option value="{{ $child->id }}" {{ old('child_category_id', $product->child_category_id) == $child->id ? 'selected' : '' }}>{{ $child->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label required fw-medium text-slate-700">HSN Code</label>
                                <select id="hsnSelect" name="hsn_code" class="form-select s-select" required
                                    data-placeholder="— Select HSN Code —">
                                    <option value="{{ $product->hsn_code }}" selected>{{ $product->hsn_code }}</option>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label required fw-medium text-slate-700">GST Rate (%)</label>
                                <input type="number" id="gstRateInput" name="gst_rate" class="form-control" step="1" min="0" max="100" placeholder="e.g. 18" value="{{ old('gst_rate', (int)$product->gst_rate) }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 3: Pricing & Package Specifications -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title fw-bold text-slate-800 mb-0">Pricing & Package Specifications</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label required fw-medium text-slate-700">Seller Price (GST
                                    Included)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" id="sellerPriceInput" name="seller_price_incl_gst"
                                        class="form-control" placeholder="Including GST" step="0.01" min="0"
                                        value="{{ old('seller_price_incl_gst', $product->seller_price_incl_gst) }}"
                                        required>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required fw-medium text-slate-700">MRP (Max Retail Price)</label>
                                <div class="input-group">
                                    <span class="input-group-text">₹</span>
                                    <input type="number" id="mrpInput" name="mrp" class="form-control" step="0.01" min="0"
                                        value="{{ old('mrp', $product->mrp) }}" required>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label required fw-medium text-slate-700">Unit of Measure (UOM)</label>
                                @php
                                    $units = $units ?? \App\Models\UnitOfMeasure::getUnitsList();
                                    $currentUom = old('unit_of_measure', $product->unit_of_measure);
                                    if ($currentUom && !in_array($currentUom, $units)) {
                                        $units[] = $currentUom;
                                    }
                                @endphp
                                <select name="unit_of_measure" class="form-select s-select" required
                                    data-placeholder="— Select Unit —">
                                    <option value="">— Select Unit —</option>
                                    @foreach($units as $unit)
                                        <option value="{{ $unit }}" {{ $currentUom == $unit ? 'selected' : '' }}>{{ $unit }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>


                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium text-slate-700">Case Pack Qty</label>
                                <input type="number" name="case_pack_quantity" class="form-control" min="1"
                                    value="{{ old('case_pack_quantity', $product->case_pack_quantity) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium text-slate-700">Shelf Life</label>
                                <input type="text" name="shelf_life" class="form-control"
                                    value="{{ old('shelf_life', $product->shelf_life) }}">
                            </div>

                            <div class="col-md-12 mb-3">
                                <label class="form-label required fw-medium text-slate-700">Country of Origin</label>
                                <input type="text" name="country_of_origin" class="form-control"
                                    value="{{ old('country_of_origin', $product->country_of_origin) }}" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 4: Dimensions & Weight -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title fw-bold text-slate-800 mb-0">Dimensions & Weight</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-medium text-slate-700">Length</label>
                                <input type="number" name="length" class="form-control" step="0.01" min="0"
                                    value="{{ old('length', $product->length) }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-medium text-slate-700">Width</label>
                                <input type="number" name="width" class="form-control" step="0.01" min="0"
                                    value="{{ old('width', $product->width) }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-medium text-slate-700">Height</label>
                                <input type="number" name="height" class="form-control" step="0.01" min="0"
                                    value="{{ old('height', $product->height) }}">
                            </div>
                            <div class="col-md-3 mb-3">
                                <label class="form-label fw-medium text-slate-700">Dimension Unit</label>
                                <select name="dimension_unit" class="form-select s-select"
                                    data-placeholder="— Select Dimension Unit —">
                                    <option value="cm" {{ old('dimension_unit', $product->dimension_unit) == 'cm' ? 'selected' : '' }}>cm</option>
                                    <option value="inch" {{ old('dimension_unit', $product->dimension_unit) == 'inch' ? 'selected' : '' }}>inch</option>
                                    <option value="mm" {{ old('dimension_unit', $product->dimension_unit) == 'mm' ? 'selected' : '' }}>mm</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium text-slate-700">Weight</label>
                                <input type="number" name="weight" class="form-control" step="0.01" min="0"
                                    value="{{ old('weight', $product->weight) }}">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-medium text-slate-700">Weight Unit</label>
                                <select name="weight_unit" class="form-select s-select"
                                    data-placeholder="— Select Weight Unit —">
                                    <option value="kg" {{ old('weight_unit', $product->weight_unit) == 'kg' ? 'selected' : '' }}>kg</option>
                                    <option value="gm" {{ old('weight_unit', $product->weight_unit) == 'gm' ? 'selected' : '' }}>gm</option>
                                    <option value="mg" {{ old('weight_unit', $product->weight_unit) == 'mg' ? 'selected' : '' }}>mg</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Card 5: Text Descriptions -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title fw-bold text-slate-800 mb-0">Descriptions & Storage</h5>
                    </div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label required fw-medium text-slate-700">Product Description</label>
                            <textarea name="description" class="form-control"
                                rows="4">{{ old('description', $product->description) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium text-slate-700">Technical Specifications</label>
                            <textarea name="product_specification" class="form-control"
                                rows="3">{{ old('product_specification', $product->product_specification) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium text-slate-700">Storage & Usage Instructions</label>
                            <textarea name="storage_instructions" class="form-control"
                                rows="3">{{ old('storage_instructions', $product->storage_instructions) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right Side: Image Galleries & Submit -->
            <div class="col-lg-4">
                <!-- Submit Action Card -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title fw-bold text-slate-800 mb-0">Publish Catalog</h5>
                    </div>
                    <div class="card-body">
                        @if($product->status === 'Approved')
                            <div class="p-2 mb-3 bg-warning bg-opacity-10 border rounded small text-warning-emphasis">
                                <iconify-icon icon="solar:info-circle-bold-duotone" class="align-middle me-1"></iconify-icon>
                                Modifying an approved product will immediately revert its status to <strong>Pending
                                    Approval</strong> and hide it from the marketplace until re-approved.
                            </div>
                        @endif
                        <p class="text-muted small">Update your catalog draft or submit/re-submit changes for Administrator
                            Review.</p>

                        <div class="d-grid gap-2">
                            <button type="submit" onclick="document.getElementById('submit_approval_flag').value='1';"
                                class="btn btn-success">
                                <iconify-icon icon="solar:shield-check-bold-duotone"
                                    class="align-middle me-1 fs-16"></iconify-icon>
                                Submit for Review
                            </button>
                            <button type="submit" onclick="document.getElementById('submit_approval_flag').value='0';"
                                class="btn btn-light border">
                                <iconify-icon icon="solar:document-bold-duotone"
                                    class="align-middle me-1 fs-16"></iconify-icon>
                                Update Draft
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Featured Image -->
                <div class="card shadow-sm border-0 mb-4">
                    <div class="card-header bg-white py-3 border-bottom">
                        <h5 class="card-title fw-bold text-slate-800 mb-0">Featured Image</h5>
                    </div>
                    <div class="card-body text-center">
                        <div class="mb-3 border rounded bg-light d-flex align-items-center justify-content-center overflow-hidden position-relative"
                            style="height: 200px;">
                            <img id="featured_image_preview" src="{{ $product->featured_image }}"
                                class="img-fluid object-fit-contain {{ $product->featured_image ? '' : 'd-none' }}"
                                style="max-height: 100%;">
                            <div id="featured_image_placeholder"
                                class="text-muted small {{ $product->featured_image ? 'd-none' : '' }}">
                                <iconify-icon icon="solar:gallery-bold-duotone" class="fs-40 mb-1"></iconify-icon>
                                <p class="mb-0">No Image Selected</p>
                            </div>
                        </div>

                        <div class="input-group">
                            <input type="text" name="featured_image" id="featured_image_input" class="form-control"
                                value="{{ old('featured_image', $product->featured_image) }}" placeholder="Asset URL (Optional)">
                            <button type="button" class="btn btn-outline-primary"
                                onclick="openMediaLibrary('featured_image_input', 'featured_image_preview', false)">
                                <iconify-icon icon="solar:gallery-wide-bold-duotone" class="align-middle"></iconify-icon>
                                Library
                            </button>
                        </div>
                    </div>


                    <!-- Gallery Images -->
                    <div class="card shadow-sm border-0 mb-4">
                        <div
                            class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                            <h5 class="card-title fw-bold text-slate-800 mb-0">Gallery Images</h5>
                            <button type="button" class="btn btn-xs btn-outline-primary"
                                onclick="openMediaLibrary('gallery_input', 'gallery_container', true)">
                                <iconify-icon icon="solar:add-circle-bold-duotone"
                                    class="align-middle me-0.5"></iconify-icon> Add
                            </button>
                        </div>
                        <div class="card-body">
                            <div class="row g-2 mb-2" id="gallery_container">
                                @php
                                    $galleryItems = old('gallery', $product->gallery ?? []);
                                    if (!is_array($galleryItems))
                                        $galleryItems = [];
                                @endphp
                                @foreach($galleryItems as $url)
                                    @php $idx = $loop->index; @endphp
                                    <div class="col-4 gallery-item-card position-relative" id="gallery_item_{{ $idx }}">
                                        <div class="ratio ratio-1x1 border rounded bg-light overflow-hidden">
                                            <img src="{{ $url }}" class="img-fluid object-fit-contain">
                                            <button type="button"
                                                class="btn btn-xs btn-danger position-absolute top-0 end-0 m-1 p-0.5 d-flex align-items-center justify-content-center rounded-circle"
                                                style="width: 20px; height: 20px;" onclick="removeGalleryItem('{{ $idx }}')">
                                                <iconify-icon icon="solar:close-circle-bold-duotone"
                                                    class="fs-14"></iconify-icon>
                                            </button>
                                            <input type="hidden" name="gallery[]" value="{{ $url }}">
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                            <p class="text-muted small mb-0 text-center" id="gallery_placeholder"
                                style="{{ count($galleryItems) > 0 ? 'display:none;' : '' }}">No gallery images added yet.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
    </form>

    <!-- Inject Modal -->
    @include('vendor.products.media-modal')

    <!-- Quick Add Brand Modal -->
    <div class="modal fade" id="quickAddBrandModal" tabindex="-1" aria-labelledby="quickAddBrandModalLabel" aria-hidden="true" style="display: none; z-index: 1080;">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header py-3 bg-light border-bottom">
                    <h6 class="modal-title fw-bold text-slate-800 d-flex align-items-center gap-1.5" id="quickAddBrandModalLabel">
                        <iconify-icon icon="solar:tag-bold-duotone" class="fs-18 text-primary"></iconify-icon>
                        Add New Brand
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="quickAddBrandForm">
                    @csrf
                    <div class="modal-body p-3">
                        <div id="quickBrandAlert" class="alert alert-danger d-none py-2 px-3 small mb-3"></div>
                        <div class="mb-2">
                            <label for="quickBrandName" class="form-label required fw-medium text-slate-700 small">Brand Name</label>
                            <input type="text" class="form-control form-control-sm" id="quickBrandName" name="name" placeholder="e.g. Samsung, Amul, Local Organic" required autocomplete="off">
                        </div>
                    </div>
                    <div class="modal-footer py-2 bg-light border-top">
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" id="saveQuickBrandBtn" class="btn btn-primary btn-sm d-flex align-items-center gap-1">
                            <iconify-icon icon="solar:check-read-bold-duotone" class="fs-14"></iconify-icon>
                            <span>Save & Select</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const categorySelect = document.getElementById('categorySelect');
            const subCategorySelect = document.getElementById('subCategorySelect');
            const childCategorySelect = document.getElementById('childCategorySelect');
            const hsnSelect = document.getElementById('hsnSelect');
            const gstRateInput = document.getElementById('gstRateInput');

            const initialCatId = "{{ $product->category_id }}";
            const initialHsnCode = "{{ $product->hsn_code }}";

            if (typeof initSUIDropdown === 'function') {
                initSUIDropdown();
            }

            let hsnGstMap = {};

            function syncTomSelect(selectEl, targetValue = null) {
                if (!selectEl || typeof TomSelect === 'undefined') return;
                if (selectEl.tomselect) {
                    var valToSet = targetValue !== null ? targetValue : selectEl.value;
                    if (!valToSet) {
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
                    if (valToSet) {
                        selectEl.tomselect.setValue(valToSet, true);
                    }
                    selectEl.tomselect.refreshOptions(false);
                } else {
                    if (typeof initSUIDropdown === 'function') {
                        initSUIDropdown(selectEl.parentNode);
                    }
                }

                if (selectEl.tomselect && !selectEl.tomselect._hasChangeSync) {
                    selectEl.tomselect._hasChangeSync = true;
                    selectEl.tomselect.on('change', function () {
                        selectEl.dispatchEvent(new Event('change', { bubbles: true }));
                    });
                }
            }

            function updateGstRate() {
                const val = hsnSelect.value ? String(hsnSelect.value).trim() : '';
                let foundGst = null;
                if (val && hsnGstMap[val] !== undefined && hsnGstMap[val] !== null) {
                    foundGst = Math.round(parseFloat(hsnGstMap[val]));
                } else if (val) {
                    const options = hsnSelect.options;
                    for (let i = 0; i < options.length; i++) {
                        if (String(options[i].value).trim() === val) {
                            const gst = options[i].getAttribute('data-gst');
                            if (gst !== null && gst !== undefined && gst !== '') {
                                foundGst = Math.round(parseFloat(gst));
                                break;
                            }
                        }
                    }
                }
                if (foundGst !== null) {
                    gstRateInput.value = foundGst;
                }
            }

            // Load initial HSN dropdown
            function loadHsns(preselectedHsn = '') {
                const hsnUrl = "{{ route('vendor.categories.hsns', ['id' => 'all']) }}";
                return fetch(hsnUrl)
                    .then(res => res.json())
                    .then(data => {
                        hsnSelect.innerHTML = '<option value="">— Select HSN Code —</option>';
                        let foundPreselected = false;
                        hsnGstMap = {};
                        data.forEach(hsn => {
                            const codeKey = String(hsn.hsn_code).trim();
                            hsnGstMap[codeKey] = hsn.gst_percentage;

                            const opt = document.createElement('option');
                            opt.value = hsn.hsn_code;
                            opt.textContent = `${hsn.hsn_code} (${hsn.description || 'No description'})`;
                            opt.setAttribute('data-gst', hsn.gst_percentage);
                            if (preselectedHsn && String(preselectedHsn).trim() === codeKey) {
                                opt.selected = true;
                                foundPreselected = true;
                            }
                            hsnSelect.appendChild(opt);
                        });

                        if (preselectedHsn && !foundPreselected) {
                            const opt = document.createElement('option');
                            opt.value = preselectedHsn;
                            opt.textContent = preselectedHsn;
                            opt.selected = true;
                            hsnSelect.appendChild(opt);
                        }
                        syncTomSelect(hsnSelect, preselectedHsn);
                    })
                    .catch(() => {
                        if (preselectedHsn) {
                            hsnSelect.innerHTML = `<option value="${preselectedHsn}" selected>${preselectedHsn}</option>`;
                        }
                        syncTomSelect(hsnSelect, preselectedHsn);
                    });
            }

            loadHsns(initialHsnCode);

            categorySelect.addEventListener('change', function () {
                const catId = this.value;
                subCategorySelect.innerHTML = '<option value="">Loading...</option>';
                childCategorySelect.innerHTML = '<option value="">— Select Sub-Category First —</option>';
                syncTomSelect(subCategorySelect);
                syncTomSelect(childCategorySelect);

                if (!catId) {
                    subCategorySelect.innerHTML = '<option value="">— Select Category First —</option>';
                    syncTomSelect(subCategorySelect);
                    return;
                }

                // Fetch sub-categories
                fetch("{{ route('vendor.sub-categories.ajax') }}?category_id=" + catId)
                    .then(res => res.json())
                    .then(data => {
                        subCategorySelect.innerHTML = '<option value="">— Select Sub-Category —</option>';
                        data.forEach(sub => {
                            const opt = document.createElement('option');
                            opt.value = sub.id;
                            opt.textContent = sub.name;
                            subCategorySelect.appendChild(opt);
                        });
                        syncTomSelect(subCategorySelect);
                    });
            });

            subCategorySelect.addEventListener('change', function () {
                const subId = this.value;
                childCategorySelect.innerHTML = '<option value="">Loading...</option>';
                syncTomSelect(childCategorySelect);

                if (!subId) {
                    childCategorySelect.innerHTML = '<option value="">— Select Sub-Category First —</option>';
                    syncTomSelect(childCategorySelect);
                    return;
                }

                fetch("{{ route('vendor.child-categories.ajax') }}?sub_category_id=" + subId)
                    .then(res => res.json())
                    .then(data => {
                        childCategorySelect.innerHTML = '<option value="">— Select Child-Category —</option>';
                        data.forEach(child => {
                            const opt = document.createElement('option');
                            opt.value = child.id;
                            opt.textContent = child.name;
                            childCategorySelect.appendChild(opt);
                        });
                        syncTomSelect(childCategorySelect);
                    });
            });

            hsnSelect.addEventListener('change', updateGstRate);

            // Sync Featured Image input text to preview
            const featuredInput = document.getElementById('featured_image_input');
            const featuredPreview = document.getElementById('featured_image_preview');
            const featuredPlaceholder = document.getElementById('featured_image_placeholder');

            function syncFeaturedImage() {
                if (featuredInput.value) {
                    featuredPreview.src = featuredInput.value;
                    featuredPreview.classList.remove('d-none');
                    featuredPlaceholder.classList.add('d-none');
                } else {
                    featuredPreview.src = '';
                    featuredPreview.classList.add('d-none');
                    featuredPlaceholder.classList.remove('d-none');
                }
            }
            featuredInput.addEventListener('input', syncFeaturedImage);
            syncFeaturedImage();


            // Keywords Tags Logic
            const keywordsInput = document.getElementById('keywordsInput');
            const keywordsContainer = document.getElementById('keywordsContainer');
            const hiddenKeywords = document.getElementById('hiddenKeywordsInputs');

            // Parse initial keywords from PHP model or old input
            let tags = @json(old('product_keywords', $product->product_keywords ?? []));
            if (!Array.isArray(tags)) tags = [];

            keywordsInput.addEventListener('keydown', function (e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                    const val = this.value.trim();
                    if (val && !tags.includes(val)) {
                        tags.push(val);
                        renderTags();
                    }
                    this.value = '';
                }
            });

            function renderTags() {
                keywordsContainer.innerHTML = '';
                hiddenKeywords.innerHTML = '';
                tags.forEach(tag => {
                    // Display badge
                    const badge = document.createElement('span');
                    badge.className = 'badge bg-success-subtle text-success d-inline-flex align-items-center gap-1 px-2.5 py-1.5 fs-12';
                    badge.innerHTML = `${tag} <iconify-icon icon="solar:close-circle-bold-duotone" class="cursor-pointer" onclick="removeTag('${tag}')"></iconify-icon>`;
                    keywordsContainer.appendChild(badge);

                    // Hidden input
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'product_keywords[]';
                    input.value = tag;
                    hiddenKeywords.appendChild(input);
                });
            }

            window.removeTag = function (tag) {
                tags = tags.filter(t => t !== tag);
                renderTags();
            };

            // Render initial tags
            renderTags();


        });

        // Gallery Items JS Append Helper
        function addGalleryImageItem(inputId, containerId, url) {
            const container = document.getElementById(containerId);
            const placeholder = document.getElementById('gallery_placeholder');
            if (placeholder) placeholder.style.display = 'none';

            // Unique index
            const idx = Date.now() + Math.random().toString(36).substr(2, 5);

            const col = document.createElement('div');
            col.className = 'col-4 gallery-item-card position-relative';
            col.id = `gallery_item_${idx}`;
            col.innerHTML = `
            <div class="ratio ratio-1x1 border rounded bg-light overflow-hidden">
                <img src="${url}" class="img-fluid object-fit-contain">
                <button type="button" class="btn btn-xs btn-danger position-absolute top-0 end-0 m-1 p-0.5 d-flex align-items-center justify-content-center rounded-circle" style="width: 20px; height: 20px;" onclick="removeGalleryItem('${idx}')">
                    <iconify-icon icon="solar:close-circle-bold-duotone" class="fs-14"></iconify-icon>
                </button>
                <input type="hidden" name="gallery[]" value="${url}">
            </div>
        `;

            container.appendChild(col);
        }

        function removeGalleryItem(id) {
            const col = document.getElementById(`gallery_item_${id}`);
            if (col) col.remove();

            const container = document.getElementById('gallery_container');
            if (container.children.length === 0) {
                const placeholder = document.getElementById('gallery_placeholder');
                if (placeholder) placeholder.style.display = 'block';
            }
        }
    </script>
@endpush

@push('styles')
    <style>
        .ck-editor__editable {
            min-height: 160px;
            border-bottom-left-radius: 0.5rem !important;
            border-bottom-right-radius: 0.5rem !important;
        }

        .ck-editor {
            font-family: inherit !important;
        }

        #quickAddBrandModal {
            z-index: 1080 !important;
        }
    </style>
@endpush

@push('scripts')
    <script src="https://cdn.ckeditor.com/ckeditor5/41.1.0/classic/ckeditor.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const textareas = [
                'textarea[name="description"]',
                'textarea[name="product_specification"]',
                'textarea[name="storage_instructions"]'
            ];
            textareas.forEach(selector => {
                const el = document.querySelector(selector);
                if (el) {
                    ClassicEditor
                        .create(el, {
                            toolbar: ['heading', '|', 'bold', 'italic', 'link', 'bulletedList', 'numberedList', 'blockQuote', 'undo', 'redo']
                        })
                        .catch(error => {
                            console.error('CKEditor Error:', error);
                        });
                }
            });

            // Append modal to body on show so position: fixed centers relative to window
            const quickModalEl = document.getElementById('quickAddBrandModal');
            if (quickModalEl) {
                quickModalEl.addEventListener('show.bs.modal', function () {
                    if (quickModalEl.parentElement !== document.body) {
                        document.body.appendChild(quickModalEl);
                    }
                });
            }

            // Quick Brand AJAX Submit Handler
            document.getElementById('quickAddBrandForm')?.addEventListener('submit', function (e) {
                e.preventDefault();
                const brandNameInput = document.getElementById('quickBrandName');
                const alertBox = document.getElementById('quickBrandAlert');
                const submitBtn = document.getElementById('saveQuickBrandBtn');
                const brandName = brandNameInput.value.trim();

                if (!brandName) return;

                alertBox.classList.add('d-none');
                alertBox.textContent = '';
                submitBtn.disabled = true;
                const btnSpan = submitBtn.querySelector('span');
                if (btnSpan) btnSpan.textContent = 'Saving...';

                fetch('{{ route('vendor.brands.quick-store') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ name: brandName })
                })
                .then(res => res.json())
                .then(data => {
                    submitBtn.disabled = false;
                    if (btnSpan) btnSpan.textContent = 'Save & Select';

                    if (data.success && data.brand) {
                        const brandSelect = document.getElementById('brandSelect') || document.querySelector('select[name="brand_id"]');
                        if (brandSelect) {
                            let existingOption = Array.from(brandSelect.options).find(opt => opt.value == data.brand.id);
                            if (!existingOption) {
                                const newOption = new Option(data.brand.name, data.brand.id, true, true);
                                brandSelect.add(newOption);
                            }

                            if (brandSelect.tomselect) {
                                brandSelect.tomselect.addOption({ value: data.brand.id, text: data.brand.name });
                                brandSelect.tomselect.setValue(data.brand.id);
                            } else {
                                brandSelect.value = data.brand.id;
                            }
                        }

                        brandNameInput.value = '';
                        const modalEl = document.getElementById('quickAddBrandModal');
                        const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                        modalInstance.hide();

                        if (typeof Toastify !== 'undefined') {
                            Toastify({
                                text: "Brand '" + data.brand.name + "' added & selected!",
                                duration: 3000,
                                gravity: "top",
                                position: "right",
                                style: { background: "#10b981" }
                            }).showToast();
                        }
                    } else {
                        alertBox.textContent = data.message || 'Failed to add brand.';
                        alertBox.classList.remove('d-none');
                    }
                })
                .catch(err => {
                    submitBtn.disabled = false;
                    if (btnSpan) btnSpan.textContent = 'Save & Select';
                    alertBox.textContent = 'An error occurred while saving the brand.';
                    alertBox.classList.remove('d-none');
                });
            });
        });
    </script>
@endpush