@extends('layouts.adc')

@section('title', 'Smart Invoice Scanner — Local Inventory')
@section('page-title', 'Smart Invoice Scanner')

@push('styles')
<style>
    /* ── Scanner UI Styles ── */
    .page-content > .container-fluid {
        max-width: 100% !important;
        padding-left: 1.25rem !important;
        padding-right: 1.25rem !important;
    }
    .invoice-scanner-wrapper {
        width: 100%;
        margin: 0;
        padding: 0;
    }
    .scan-card-dropzone {
        border: 2px dashed #cbd5e1;
        border-radius: 16px;
        background: #f8fafc;
        transition: all 0.25s ease-in-out;
        cursor: pointer;
    }
    .scan-card-dropzone:hover, .scan-card-dropzone.dragover {
        border-color: #6366f1;
        background: #f5f3ff;
    }
    .scanner-step-pill {
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 14px;
    }
    .camera-video-wrap {
        position: relative;
        width: 100%;
        max-width: 640px;
        margin: 0 auto;
        border-radius: 16px;
        overflow: hidden;
        background: #000;
        aspect-ratio: 4/3;
    }
    .camera-video-wrap video {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    .camera-guide-box {
        position: absolute;
        top: 8%;
        left: 8%;
        right: 8%;
        bottom: 8%;
        border: 2px dashed rgba(255, 255, 255, 0.7);
        border-radius: 12px;
        pointer-events: none;
        box-shadow: 0 0 0 9999px rgba(0, 0, 0, 0.35);
    }
    /* Side-by-side Review UI */
    .doc-viewer-sticky {
        position: sticky;
        top: 85px;
        height: calc(100vh - 120px);
        background: #1e293b;
        border-radius: 16px;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
    .doc-viewer-body {
        flex: 1;
        overflow: auto;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #0f172a;
        padding: 12px;
    }
    .doc-preview-img {
        max-width: 100%;
        max-height: 100%;
        object-fit: contain;
        transition: transform 0.2s ease;
        box-shadow: 0 4px 20px rgba(0,0,0,0.5);
    }
    .match-badge-exact {
        background: #dcfce7;
        color: #15803d;
        border: 1px solid #86efac;
        font-size: 10.5px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 6px;
    }
    .match-badge-suggested {
        background: #fef9c3;
        color: #a16207;
        border: 1px solid #fde047;
        font-size: 10.5px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 6px;
    }
    .match-badge-new {
        background: #e0e7ff;
        color: #4338ca;
        border: 1px solid #c7d2fe;
        font-size: 10.5px;
        font-weight: 700;
        padding: 2px 7px;
        border-radius: 6px;
    }
    .table-input-sm {
        padding: 4px 8px;
        font-size: 12px;
        border-radius: 6px;
    }
    .product-item-row {
        background: #ffffff;
        border: 1px solid #e2e8f0;
        transition: all 0.2s ease-in-out;
    }
    .product-item-row:hover {
        border-color: #cbd5e1;
        box-shadow: 0 4px 16px rgba(0, 0, 0, 0.06);
    }
    .product-item-row .item-csp:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 0.25rem rgba(59, 130, 246, 0.25);
    }
</style>
@endpush

@section('content')
<div class="invoice-scanner-wrapper">

    {{-- ── Header Card ── --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body py-3 px-4">
                    <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                        <h4 class="fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2" style="font-family: 'Outfit', sans-serif;">
                            <iconify-icon icon="solar:scanner-bold-duotone" class="text-primary fs-28"></iconify-icon>
                            Smart Invoice Scanner
                        </h4>
                    </div>
                    <p class="text-muted small mb-0">Upload or capture any regional supplier invoice (Image/PDF). AI multimodal understanding auto-extracts supplier, invoice details &amp; product rows.</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── SECTION 1: INVOICE INPUT METHODS (Tabs: File Upload / Camera Capture) ── --}}
    <div id="scanner-input-container">
        <div class="card shadow-sm border-0 rounded-4 mb-4">
            <div class="card-header bg-white py-3 px-4 border-bottom">
                <ul class="nav nav-pills card-header-pills gap-2" id="inputMethodTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill px-4 fw-bold d-flex align-items-center gap-2" id="tab-upload-btn" data-bs-toggle="pill" data-bs-target="#tab-upload" type="button" role="tab">
                            <iconify-icon icon="solar:file-send-bold-duotone" class="fs-18"></iconify-icon>
                            1. Upload Invoice File (Image / PDF)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-4 fw-bold d-flex align-items-center gap-2" id="tab-camera-btn" data-bs-toggle="pill" data-bs-target="#tab-camera" type="button" role="tab">
                            <iconify-icon icon="solar:camera-bold-duotone" class="fs-18"></iconify-icon>
                            2. Capture via Device Camera
                        </button>
                    </li>
                </ul>
            </div>
            <div class="card-body p-4">
                <div class="tab-content" id="inputMethodTabsContent">

                    {{-- TAB 1: FILE UPLOAD --}}
                    <div class="tab-pane fade show active" id="tab-upload" role="tabpanel">
                        <div class="scan-card-dropzone p-5 text-center" id="dropzone-area">
                            <input type="file" id="invoice-file-input" class="d-none" accept=".jpg,.jpeg,.png,.webp,.pdf,image/jpeg,image/png,image/webp,application/pdf">
                            <div class="mb-3">
                                <span class="d-inline-flex p-3 rounded-circle bg-primary-subtle text-primary">
                                    <iconify-icon icon="solar:cloud-upload-bold-duotone" class="fs-48"></iconify-icon>
                                </span>
                            </div>
                            <h5 class="fw-bold text-slate-800 mb-2">Drag &amp; drop invoice here or click to browse</h5>
                            <p class="text-muted small mb-3">Supports JPG, JPEG, PNG, WEBP and multi-page PDFs (Up to 15MB).</p>
                            <button type="button" class="btn btn-primary rounded-3 px-4 fw-bold" onclick="document.getElementById('invoice-file-input').click()">
                                <iconify-icon icon="solar:folder-with-files-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Choose File
                            </button>
                            <div id="selected-file-info" class="mt-3 text-success fw-semibold small d-none"></div>
                        </div>
                    </div>

                    {{-- TAB 2: CAMERA CAPTURE --}}
                    <div class="tab-pane fade" id="tab-camera" role="tabpanel">
                        <div class="text-center">
                            {{-- Camera Stream Screen --}}
                            <div id="camera-stream-box" class="d-none">
                                <div class="camera-video-wrap mb-3 shadow">
                                    <video id="camera-video" playsinline autoplay muted></video>
                                    <div class="camera-guide-box"></div>
                                </div>
                                <div class="d-flex justify-content-center gap-3">
                                    <button type="button" class="btn btn-danger rounded-3 px-4 fw-semibold" id="btn-stop-camera">
                                        <iconify-icon icon="solar:close-circle-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Close Camera
                                    </button>
                                    <button type="button" class="btn btn-success btn-lg rounded-pill px-5 fw-bold shadow" id="btn-snap-photo">
                                        <iconify-icon icon="solar:camera-minimalistic-bold-duotone" class="align-middle me-1 fs-20"></iconify-icon> Take Photo
                                    </button>
                                </div>
                            </div>

                            {{-- Camera Captured Preview Screen --}}
                            <div id="camera-snapshot-box" class="d-none">
                                <div class="camera-video-wrap mb-3 shadow">
                                    <img id="camera-snapshot-img" class="w-100 h-100 object-fit-contain" src="" alt="Captured Invoice">
                                </div>
                                <div class="d-flex justify-content-center gap-3">
                                    <button type="button" class="btn btn-outline-secondary rounded-3 px-4 fw-semibold" id="btn-retake-photo">
                                        <iconify-icon icon="solar:restart-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Retake Photo
                                    </button>
                                    <button type="button" class="btn btn-primary rounded-3 px-5 fw-bold shadow" id="btn-use-snapshot">
                                        <iconify-icon icon="solar:check-circle-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Scan This Photo
                                    </button>
                                </div>
                            </div>

                            {{-- Initial State: Start Camera Button --}}
                            <div id="camera-prompt-box" class="p-4">
                                <span class="d-inline-flex p-3 rounded-circle bg-success-subtle text-success mb-3">
                                    <iconify-icon icon="solar:camera-square-bold-duotone" class="fs-48"></iconify-icon>
                                </span>
                                <h5 class="fw-bold text-slate-800 mb-2">Scan Invoice with Device Camera</h5>
                                <p class="text-muted small mb-3">Point your phone or webcam directly at the printed bill or receipt.</p>
                                <button type="button" class="btn btn-success rounded-3 px-4 fw-bold" id="btn-start-camera">
                                    <iconify-icon icon="solar:videocamera-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Start Camera
                                </button>
                            </div>
                        </div>
                    </div>

                </div>

                {{-- Processing Progress Indicator --}}
                <div id="scan-progress-wrap" class="mt-4 p-4 rounded-4 bg-light text-center d-none border">
                    <div class="spinner-border text-primary mb-3" style="width: 3rem; height: 3rem;" role="status"></div>
                    <h5 class="fw-bold text-slate-800 mb-1" id="scan-status-title">Processing Invoice with AI...</h5>
                    <p class="text-muted small mb-0" id="scan-status-desc">AI is analyzing document layout, supplier details &amp; product rows...</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ── SECTION 2: SIDE-BY-SIDE REVIEW & SAVE (Initially hidden, revealed on successful scan) ── --}}
    <div id="scanner-review-container" class="d-none">

        {{-- Duplicate Warning Alert --}}
        <div id="duplicate-warning-alert" class="alert alert-warning border-0 shadow-sm rounded-4 mb-3 d-none d-flex align-items-center gap-3" role="alert">
            <iconify-icon icon="solar:danger-triangle-bold-duotone" class="text-warning fs-28 flex-shrink-0"></iconify-icon>
            <div class="flex-grow-1">
                <h6 class="fw-bold mb-0">Potential Duplicate Invoice Detected</h6>
                <p class="small mb-0" id="duplicate-warning-text"></p>
            </div>
        </div>

        {{-- Total Reconciliation Alert --}}
        <div id="reconciliation-alert" class="alert border-0 shadow-sm rounded-4 mb-3 d-none d-flex align-items-center gap-3" role="alert">
            <iconify-icon id="reconciliation-icon" icon="solar:check-circle-bold-duotone" class="fs-28 flex-shrink-0"></iconify-icon>
            <div class="flex-grow-1">
                <h6 class="fw-bold mb-0" id="reconciliation-title">Invoice Total Verification</h6>
                <p class="small mb-0" id="reconciliation-text"></p>
            </div>
        </div>

        <div class="row g-3">
            {{-- FULL WIDTH: EDITABLE INVOICE & PRODUCT DATA FORM --}}
            <div class="col-12">
                <form id="stock-save-form">
                    @csrf

                    {{-- Card 1: Supplier & Invoice Information --}}
                    <div class="card shadow-sm border-0 rounded-4 mb-3">
                        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <h6 class="fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:user-bold-duotone" class="text-success fs-20"></iconify-icon>
                                1. Supplier &amp; Invoice Information
                            </h6>
                            <div class="d-flex align-items-center gap-2">
                                <a href="#" target="_blank" id="btn-view-doc-link" class="btn btn-sm btn-outline-info rounded-pill px-3 d-none">
                                    <iconify-icon icon="solar:document-bold-duotone" class="align-middle me-1 fs-14"></iconify-icon> View Uploaded Invoice
                                </a>
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" id="btn-scan-another">
                                    <iconify-icon icon="solar:restart-bold-duotone" class="align-middle me-1 fs-14"></iconify-icon> Scan Different Invoice
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-4">
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-slate-700 fs-13">Supplier Name <span class="text-danger">*</span></label>
                                    <input type="text" name="supplier_name" id="inp-supplier-name" class="form-control" placeholder="e.g. Acme Wholesalers" required>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-slate-700 fs-13">Supplier Mobile</label>
                                    <input type="text" name="supplier_mobile" id="inp-supplier-mobile" class="form-control" placeholder="e.g. 9876543210">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-slate-700 fs-13">Supplier GSTIN</label>
                                    <input type="text" name="supplier_gstin" id="inp-supplier-gstin" class="form-control" placeholder="e.g. 24AKPPP1343N1ZK">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-slate-700 fs-13">Invoice / Bill Number</label>
                                    <input type="text" name="invoice_number" id="inp-invoice-number" class="form-control" placeholder="e.g. INV-2026-001">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-slate-700 fs-13">Invoice Date</label>
                                    <input type="date" name="invoice_date" id="inp-invoice-date" class="form-control" value="{{ date('Y-m-d') }}">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label fw-semibold text-slate-700 fs-13">Purchase Date <span class="text-danger">*</span></label>
                                    <input type="date" name="purchase_date" id="inp-purchase-date" class="form-control" value="{{ date('Y-m-d') }}" required>
                                </div>
                                <div class="col-12">
                                    <label class="form-label fw-semibold text-slate-700 fs-13">Remarks / Note</label>
                                    <input type="text" name="remarks" id="inp-remarks" class="form-control" placeholder="Optional notes for this stock receipt">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Card 2: Extracted Product Entries (Spacious Two-Row Layout) --}}
                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <h6 class="fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2">
                                    <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="text-primary fs-20"></iconify-icon>
                                    2. Extracted Product Entries (<span id="items-count-badge">0</span> Items)
                                </h6>
                                <span class="badge bg-light text-muted border fs-11">Two-Row Spacious Layout</span>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-3 px-3 fw-semibold" onclick="openAddBrandModal(null)">
                                    <iconify-icon icon="solar:tag-bold-duotone" class="align-middle me-1 fs-15"></iconify-icon> + New Brand
                                </button>
                                <button type="button" class="btn btn-sm btn-success rounded-3 px-3 fw-semibold shadow-sm" id="btn-add-product-row">
                                    <iconify-icon icon="solar:add-circle-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Add Product Entry
                                </button>
                            </div>
                        </div>
                        <div class="card-body p-3 bg-light bg-opacity-25" id="extracted-items-container">
                            {{-- Dynamic extracted product items rendered across two rows --}}
                        </div>
                        <div class="card-footer bg-white py-3 px-4 border-top d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <span class="text-muted small">Total Items: <strong id="summary-total-items" class="text-slate-800">0</strong></span>
                                <span class="mx-2 text-muted">|</span>
                                <span class="text-muted small">Total Qty: <strong id="summary-total-qty" class="text-slate-800">0</strong></span>
                            </div>
                            <div class="fs-15">
                                <span class="text-muted">Total Purchase Value: </span>
                                <strong class="text-primary fs-18" id="summary-grand-total">₹0.00</strong>
                            </div>
                        </div>
                    </div>

                    {{-- Actions Bar --}}
                    <div class="d-flex align-items-center justify-content-end gap-3 mb-5">
                        <button type="button" class="btn btn-light rounded-3 px-4 fw-semibold" id="btn-cancel-review">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-success btn-lg rounded-3 px-5 fw-bold shadow" id="btn-confirm-stock-in">
                            <iconify-icon icon="solar:check-circle-bold-duotone" class="align-middle me-1 fs-20"></iconify-icon>
                            Confirm Details &amp; Stock In
                        </button>
                    </div>

                </form>
            </div>
        </div>

    </div>

</div>

{{-- ── Quick Add Brand Modal ── --}}
<div class="modal fade" id="addBrandModal" tabindex="-1" aria-labelledby="addBrandModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3 px-4 bg-white">
                <h5 class="modal-title fw-bold text-slate-800 d-flex align-items-center gap-2" id="addBrandModalLabel">
                    <iconify-icon icon="solar:tag-bold-duotone" class="text-primary fs-22"></iconify-icon>
                    Add New Brand
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="form-quick-add-brand">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="new-brand-name" class="form-label fw-bold text-slate-700 fs-13">Brand Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control rounded-3" id="new-brand-name" placeholder="e.g. Tata, Fortune, Nestlé, Amul" required autocomplete="off">
                        <div class="form-text text-muted mt-2 small">
                            <iconify-icon icon="solar:info-circle-bold" class="align-middle text-primary"></iconify-icon>
                            This brand will be created instantly and made available across all brand selectors on this invoice.
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2 px-4 bg-light bg-opacity-50">
                    <button type="button" class="btn btn-light rounded-3 px-3 fw-semibold" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-3 px-4 fw-bold" id="btn-save-new-brand">
                        <iconify-icon icon="solar:check-circle-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Save Brand
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Master Lists passed from backend
    const categoriesList = @json($categories);
    const brandsList = @json($brands);
    const unitsList = @json($units);
    const localProductsList = @json($localProducts);

    const processRoute = "{{ route('adc.local-inventory.invoice-scan.process') }}";
    const saveRoute = "{{ route('adc.local-inventory.invoice-scan.save') }}";
    const brandStoreRoute = "{{ route('adc.local-inventory.invoice-scan.brand.store') }}";
    const csrfToken = "{{ csrf_token() }}";

    let currentRotation = 0;
    let currentZoom = 1;
    let cameraStream = null;
    let capturedBase64 = null;
    let rowIndexCounter = 0;
    let activeBrandTargetRowIdx = null;
    let addBrandModalInstance = null;

    // ── Inline Brand Creation Modal Helper ──
    function openAddBrandModal(rowIdx = null) {
        activeBrandTargetRowIdx = rowIdx;
        const brandInput = document.getElementById('new-brand-name');
        if (brandInput) brandInput.value = '';
        const modalEl = document.getElementById('addBrandModal');
        if (!addBrandModalInstance && modalEl) {
            addBrandModalInstance = new bootstrap.Modal(modalEl);
        }
        if (addBrandModalInstance) {
            addBrandModalInstance.show();
            setTimeout(() => {
                if (brandInput) brandInput.focus();
            }, 350);
        }
    }

    // Handle Quick Brand Creation Form Submission
    document.getElementById('form-quick-add-brand')?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const brandInput = document.getElementById('new-brand-name');
        const brandName = brandInput ? brandInput.value.trim() : '';
        if (!brandName) return;

        const btnSave = document.getElementById('btn-save-new-brand');
        const origText = btnSave.innerHTML;
        btnSave.disabled = true;
        btnSave.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span>Saving...`;

        try {
            const resp = await fetch(brandStoreRoute, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ name: brandName })
            });
            const data = await resp.json();
            btnSave.disabled = false;
            btnSave.innerHTML = origText;

            if (data.success && data.brand) {
                const newBrand = data.brand;
                // Add to memory list if not already present
                const exists = brandsList.some(b => b.id == newBrand.id);
                if (!exists) {
                    brandsList.push(newBrand);
                    brandsList.sort((a, b) => a.name.localeCompare(b.name));
                }

                // Update all existing brand dropdowns in the form
                document.querySelectorAll('.item-brand-select').forEach(sel => {
                    const optExists = Array.from(sel.options).some(o => o.value == newBrand.id);
                    if (!optExists) {
                        const opt = document.createElement('option');
                        opt.value = newBrand.id;
                        opt.textContent = newBrand.name;
                        sel.appendChild(opt);
                    }
                });

                // If opened from a specific row, select this new brand
                if (activeBrandTargetRowIdx !== null) {
                    const targetRow = document.getElementById(`row-${activeBrandTargetRowIdx}`);
                    if (targetRow) {
                        const sel = targetRow.querySelector('.item-brand-select');
                        if (sel) {
                            sel.value = newBrand.id;
                        }
                    }
                }

                if (addBrandModalInstance) {
                    addBrandModalInstance.hide();
                }

                Swal.fire({
                    icon: 'success',
                    title: 'Brand Created',
                    text: `Brand "${newBrand.name}" was added successfully!`,
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            } else {
                Swal.fire('Error', data.message || 'Could not create brand.', 'error');
            }
        } catch (err) {
            btnSave.disabled = false;
            btnSave.innerHTML = origText;
            Swal.fire('Error', 'Server request error: ' + err.message, 'error');
        }
    });

    // ── Input Dropzone Events ──
    const dropzone = document.getElementById('dropzone-area');
    const fileInput = document.getElementById('invoice-file-input');

    ['dragenter', 'dragover'].forEach(name => {
        dropzone.addEventListener(name, (e) => { e.preventDefault(); dropzone.classList.add('dragover'); });
    });
    ['dragleave', 'drop'].forEach(name => {
        dropzone.addEventListener(name, (e) => { e.preventDefault(); dropzone.classList.remove('dragover'); });
    });
    dropzone.addEventListener('drop', (e) => {
        if (e.dataTransfer.files.length) {
            handleFileUpload(e.dataTransfer.files[0]);
        }
    });
    fileInput.addEventListener('change', (e) => {
        if (e.target.files.length) {
            handleFileUpload(e.target.files[0]);
        }
    });

    // ── Camera Handlers ──
    const btnStartCamera = document.getElementById('btn-start-camera');
    const btnStopCamera = document.getElementById('btn-stop-camera');
    const btnSnapPhoto = document.getElementById('btn-snap-photo');
    const btnRetakePhoto = document.getElementById('btn-retake-photo');
    const btnUseSnapshot = document.getElementById('btn-use-snapshot');

    const cameraPromptBox = document.getElementById('camera-prompt-box');
    const cameraStreamBox = document.getElementById('camera-stream-box');
    const cameraSnapshotBox = document.getElementById('camera-snapshot-box');
    const cameraVideo = document.getElementById('camera-video');
    const cameraSnapshotImg = document.getElementById('camera-snapshot-img');

    btnStartCamera.addEventListener('click', async () => {
        try {
            // Request back/rear camera on mobile devices
            const constraints = {
                video: {
                    facingMode: { ideal: "environment" },
                    width: { ideal: 1920 },
                    height: { ideal: 1080 }
                }
            };
            cameraStream = await navigator.mediaDevices.getUserMedia(constraints);
            cameraVideo.srcObject = cameraStream;
            cameraPromptBox.classList.add('d-none');
            cameraSnapshotBox.classList.add('d-none');
            cameraStreamBox.classList.remove('d-none');
        } catch (err) {
            Swal.fire('Camera Error', 'Could not access device camera: ' + err.message, 'error');
        }
    });

    btnStopCamera.addEventListener('click', stopCameraStream);

    function stopCameraStream() {
        if (cameraStream) {
            cameraStream.getTracks().forEach(track => track.stop());
            cameraStream = null;
        }
        cameraStreamBox.classList.add('d-none');
        cameraSnapshotBox.classList.add('d-none');
        cameraPromptBox.classList.remove('d-none');
    }

    btnSnapPhoto.addEventListener('click', () => {
        const canvas = document.createElement('canvas');
        canvas.width = cameraVideo.videoWidth || 1280;
        canvas.height = cameraVideo.videoHeight || 720;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(cameraVideo, 0, 0, canvas.width, canvas.height);
        capturedBase64 = canvas.toDataURL('image/jpeg', 0.95);

        cameraSnapshotImg.src = capturedBase64;
        cameraStreamBox.classList.add('d-none');
        cameraSnapshotBox.classList.remove('d-none');
    });

    btnRetakePhoto.addEventListener('click', () => {
        capturedBase64 = null;
        cameraSnapshotBox.classList.add('d-none');
        cameraStreamBox.classList.remove('d-none');
    });

    btnUseSnapshot.addEventListener('click', () => {
        if (!capturedBase64) return;
        stopCameraStream();
        processInvoiceData({ captured_image: capturedBase64 });
    });

    // ── Upload Handler ──
    function handleFileUpload(file) {
        document.getElementById('selected-file-info').textContent = `Selected: ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
        document.getElementById('selected-file-info').classList.remove('d-none');

        const formData = new FormData();
        formData.append('invoice_file', file);
        processInvoiceData(formData, true);
    }

    // ── Backend Process Call (AI Vision Extraction) ──
    async function processInvoiceData(payload, isFormData = false) {
        showProgress('Analyzing Invoice with AI...', 'Reading supplier info, product rows, rates & pricing via AI vision...');

        try {
            let res;
            if (isFormData) {
                payload.append('_token', csrfToken);
                res = await fetch(processRoute, {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': csrfToken },
                    body: payload
                });
            } else {
                res = await fetch(processRoute, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ ...payload, _token: csrfToken })
                });
            }

            const data = await res.json();
            hideProgress();

            if (!data.success) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Scan Warning',
                    text: data.message || 'Could not extract data from the invoice.',
                    confirmButtonColor: '#ff9933'
                });
                return;
            }

            // Populate Review UI
            populateReviewScreen(data);

        } catch (err) {
            hideProgress();
            Swal.fire('Error', 'Server processing error: ' + err.message, 'error');
        }
    }

    function showProgress(title, desc) {
        document.getElementById('scan-status-title').textContent = title;
        document.getElementById('scan-status-desc').textContent = desc;
        document.getElementById('scan-progress-wrap').classList.remove('d-none');
        document.getElementById('dropzone-area').classList.add('opacity-50');
    }

    function hideProgress() {
        document.getElementById('scan-progress-wrap').classList.add('d-none');
        document.getElementById('dropzone-area').classList.remove('opacity-50');
    }

    // ── Populate Side-by-Side Review Screen ──
    function populateReviewScreen(data) {
        document.getElementById('scanner-input-container').classList.add('d-none');
        document.getElementById('scanner-review-container').classList.remove('d-none');

        // Document Viewer Link
        const viewDocLink = document.getElementById('btn-view-doc-link');
        if (viewDocLink && data.preview_url) {
            viewDocLink.href = data.preview_url;
            viewDocLink.classList.remove('d-none');
        }

        // Engine Badge
        const engineBadge = document.getElementById('badge-engine-type');
        if (engineBadge) {
            engineBadge.className = 'badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-1 fs-12 fw-semibold d-inline-flex align-items-center gap-1';
            engineBadge.innerHTML = '<iconify-icon icon="solar:stars-bold-duotone" class="fs-14 text-primary"></iconify-icon> ' + (data.engine_label || 'AI');
            engineBadge.classList.remove('d-none');
        }

        // Supplier & Invoice Fields
        document.getElementById('inp-supplier-name').value = data.supplier_name || '';
        document.getElementById('inp-supplier-mobile').value = data.supplier_mobile || '';
        const gstinInput = document.getElementById('inp-supplier-gstin');
        if (gstinInput) {
            gstinInput.value = data.supplier_gstin || '';
        }
        document.getElementById('inp-invoice-number').value = data.invoice_number || '';
        document.getElementById('inp-invoice-date').value = data.invoice_date || '{{ date("Y-m-d") }}';
        document.getElementById('inp-purchase-date').value = data.purchase_date || '{{ date("Y-m-d") }}';

        // Duplicate Warning
        const dupAlert = document.getElementById('duplicate-warning-alert');
        if (data.duplicate_warning) {
            document.getElementById('duplicate-warning-text').textContent = data.duplicate_warning;
            dupAlert.classList.remove('d-none');
        } else {
            dupAlert.classList.add('d-none');
        }

        // Reconciliation Alert Banner
        const recAlert = document.getElementById('reconciliation-alert');
        const recIcon = document.getElementById('reconciliation-icon');
        const recTitle = document.getElementById('reconciliation-title');
        const recText = document.getElementById('reconciliation-text');

        if (data.reconciliation_note) {
            recText.textContent = data.reconciliation_note;
            if (data.is_reconciled) {
                recAlert.className = 'alert alert-success border-0 shadow-sm rounded-4 mb-3 d-flex align-items-center gap-3';
                recIcon.className = 'text-success fs-28 flex-shrink-0';
                recIcon.setAttribute('icon', 'solar:check-circle-bold-duotone');
                recTitle.textContent = 'Invoice Totals Reconciled';
            } else {
                recAlert.className = 'alert alert-warning border-0 shadow-sm rounded-4 mb-3 d-flex align-items-center gap-3';
                recIcon.className = 'text-warning fs-28 flex-shrink-0';
                recIcon.setAttribute('icon', 'solar:danger-triangle-bold-duotone');
                recTitle.textContent = 'Total Mismatch Warning — Please Verify';
            }
            recAlert.classList.remove('d-none');
        } else {
            recAlert.classList.add('d-none');
        }

        // Populate Extracted Products Container
        const container = document.getElementById('extracted-items-container');
        container.innerHTML = '';
        rowIndexCounter = 0;

        const products = data.products || [];
        if (products.length === 0) {
            // Add at least one empty entry for manual entry
            addProductRow();
        } else {
            products.forEach(item => {
                addProductRow(item);
            });
        }

        updateCalculations();
    }

    // ── Add Product Row to Container (Two-Row Spacious Entry) ──
    function addProductRow(data = {}) {
        const container = document.getElementById('extracted-items-container');
        const idx = rowIndexCounter++;

        const block = document.createElement('div');
        block.className = 'product-item-row p-3 mb-3 border rounded-3 bg-white shadow-sm';
        block.id = `row-${idx}`;
        block.setAttribute('data-idx', idx);

        const pName = data.product_name || '';
        const matchType = data.match_type || 'NEW';
        const matchSource = data.match_source || '';
        const selectedProductId = data.local_product_id || 'NEW';

        // REQUIREMENT 3: Keep category unselected by default ("No Selection")
        const categoryId = (data.category_id !== undefined && data.category_id !== null && data.category_id !== '') ? data.category_id : '';

        const brandId = data.brand_id || '';
        const hsn = data.hsn_code || '';
        const gst = data.gst_rate !== undefined ? parseFloat(data.gst_rate) : 0;
        const uom = data.unit_of_measure || 'Piece';
        const qty = data.quantity || 1;
        const pPrice = (data.purchase_price !== undefined && data.purchase_price !== null && parseFloat(data.purchase_price) > 0) ? parseFloat(data.purchase_price).toFixed(2) : '0.00';
        const mrp = (data.mrp !== undefined && data.mrp !== null && parseFloat(data.mrp) > 0) ? parseFloat(data.mrp).toFixed(2) : '';

        // REQUIREMENT 1: CSP must be entered manually; keep empty by default
        const csp = (data.csp !== undefined && data.csp !== null && parseFloat(data.csp) > 0) ? parseFloat(data.csp).toFixed(2) : '';

        const barcode = data.barcode || '';

        // Match Badge HTML
        let matchBadgeHtml = '';
        if (matchType === 'EXACT') {
            matchBadgeHtml = `<span class="match-badge-exact" title="${matchSource}"><iconify-icon icon="solar:check-circle-bold" class="align-middle me-0.5"></iconify-icon> Matched</span>`;
        } else if (matchType === 'SUGGESTED') {
            matchBadgeHtml = `<span class="match-badge-suggested" title="${matchSource}"><iconify-icon icon="solar:info-circle-bold" class="align-middle me-0.5"></iconify-icon> Suggested</span>`;
        } else {
            matchBadgeHtml = `<span class="match-badge-new" title="New Regional Product"><iconify-icon icon="solar:add-circle-bold" class="align-middle me-0.5"></iconify-icon> New Product</span>`;
        }

        // Category Options: REQUIREMENT 3 - Display "No Selection" until chosen
        let catOptions = `<option value="" ${!categoryId ? 'selected' : ''}>-- No Selection --</option>`;
        categoriesList.forEach(c => {
            catOptions += `<option value="${c.id}" ${c.id == categoryId ? 'selected' : ''}>${c.name}</option>`;
        });

        // Brand Options: REQUIREMENT 4 - Inline creation option available
        let brandOptions = '<option value="">-- None / Unbranded --</option>';
        brandsList.forEach(b => {
            brandOptions += `<option value="${b.id}" ${b.id == brandId ? 'selected' : ''}>${b.name}</option>`;
        });

        // Unit Options
        let unitOptions = '';
        unitsList.forEach(u => {
            unitOptions += `<option value="${u}" ${u.toLowerCase() === uom.toLowerCase() ? 'selected' : ''}>${u}</option>`;
        });

        // GST Options
        const gstOptions = [0, 5, 12, 18, 28].map(g => `<option value="${g}" ${g == gst ? 'selected' : ''}>${g}%</option>`).join('');

        // Candidate matches options
        let candidateOptions = '';
        if (data.possible_matches && data.possible_matches.length > 0) {
            data.possible_matches.forEach(pm => {
                candidateOptions += `<option value="${pm.id}" ${selectedProductId == pm.id ? 'selected' : ''}>Suggested: ${pm.name} (${pm.code}) [${pm.similarity}% match]</option>`;
            });
        }

        // Product Master Link Options (Match dropdown)
        let productMasterOptions = `<option value="NEW" ${selectedProductId === 'NEW' ? 'selected' : ''}>+ Create as New Regional Product</option>`;
        if (candidateOptions) {
            productMasterOptions += `<optgroup label="── Suggested Catalog Matches ──">${candidateOptions}</optgroup>`;
        }
        productMasterOptions += `<optgroup label="── All Regional Products ──">`;
        localProductsList.forEach(lp => {
            productMasterOptions += `<option value="${lp.id}" ${selectedProductId == lp.id ? 'selected' : ''}>Link to: ${lp.product_name} (${lp.local_product_code})</option>`;
        });
        productMasterOptions += `</optgroup>`;

        // Warnings / Helpers
        const mrpBadge = (!mrp || parseFloat(mrp) <= 0) ? `<div class="text-danger fs-11 mt-1 mrp-warning-hint"><iconify-icon icon="solar:danger-circle-bold" class="align-middle"></iconify-icon> Enter MRP</div>` : '';
        const cspBadge = (!csp || parseFloat(csp) <= 0) ? `<div class="text-primary fs-11 fw-semibold mt-1 csp-manual-hint"><iconify-icon icon="solar:pen-bold" class="align-middle"></iconify-icon> Manual entry required</div>` : '';

        block.innerHTML = `
            {{-- Entry Header Bar --}}
            <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-light text-dark fw-bold px-2.5 py-1.5 fs-12 border product-index-badge">#${idx + 1}</span>
                    ${matchBadgeHtml}
                    ${matchSource ? `<small class="text-muted fs-12 text-truncate" style="max-width: 250px;" title="${matchSource}">${matchSource}</small>` : ''}
                </div>
                <div>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-2 py-1 px-2.5 d-inline-flex align-items-center gap-1" onclick="removeProductRow(${idx})" title="Remove Item">
                        <iconify-icon icon="solar:trash-bin-trash-bold" class="fs-15"></iconify-icon> <span class="fs-12">Remove</span>
                    </button>
                </div>
            </div>

            {{-- Row 1: Product Master Info (Name, Match Link, Category, Brand, Unit) --}}
            <div class="row g-2 mb-2 align-items-start">
                <div class="col-xl-5 col-lg-5 col-md-12">
                    <label class="form-label fs-12 fw-bold text-slate-700 mb-1">Product Description / Name <span class="text-danger">*</span></label>
                    <input type="text" name="items[${idx}][product_name]" class="form-control form-control-sm fw-semibold mb-1 item-name-input" value="${pName}" placeholder="e.g. Basmati Rice 1kg" required>
                    <select name="items[${idx}][local_product_id]" class="form-select form-select-sm text-muted fs-11 item-product-select">
                        ${productMasterOptions}
                    </select>
                    <input type="hidden" name="items[${idx}][barcode]" value="${barcode}">
                </div>
                <div class="col-xl-3 col-lg-3 col-md-4">
                    <label class="form-label fs-12 fw-bold text-slate-700 mb-1">Category <span class="text-danger">*</span></label>
                    <select name="items[${idx}][category_id]" class="form-select form-select-sm item-category-select ${!categoryId ? 'border-warning bg-warning-subtle' : ''}" required>
                        ${catOptions}
                    </select>
                    ${!categoryId ? `<div class="text-warning fs-11 mt-1 cat-warning-hint"><iconify-icon icon="solar:danger-circle-bold" class="align-middle"></iconify-icon> Please select a category</div>` : ''}
                </div>
                <div class="col-xl-3 col-lg-3 col-md-5">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label class="form-label fs-12 fw-bold text-slate-700 mb-0">Brand</label>
                        <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none fs-11 fw-semibold text-primary" onclick="openAddBrandModal(${idx})">
                            <iconify-icon icon="solar:add-circle-bold" class="align-middle"></iconify-icon> + Add Brand
                        </button>
                    </div>
                    <div class="input-group input-group-sm">
                        <select name="items[${idx}][brand_id]" class="form-select form-select-sm item-brand-select">
                            ${brandOptions}
                        </select>
                        <button type="button" class="btn btn-outline-primary" onclick="openAddBrandModal(${idx})" title="Add new brand">
                            <iconify-icon icon="solar:add-circle-bold" class="align-middle fs-15"></iconify-icon>
                        </button>
                    </div>
                </div>
                <div class="col-xl-1 col-lg-1 col-md-3">
                    <label class="form-label fs-12 fw-bold text-slate-700 mb-1">Unit <span class="text-danger">*</span></label>
                    <select name="items[${idx}][unit_of_measure]" class="form-select form-select-sm">
                        ${unitOptions}
                    </select>
                </div>
            </div>

            {{-- Row 2: Pricing, Tax & Inventory Details --}}
            <div class="row g-2 align-items-end pt-2 border-top bg-light bg-opacity-50 rounded-2 p-2 mt-1">
                <div class="col-xl-2 col-lg-2 col-md-4 col-6">
                    <label class="form-label fs-11 text-muted fw-bold text-uppercase mb-1">HSN / SAC</label>
                    <input type="text" name="items[${idx}][hsn_code]" class="form-control form-control-sm" value="${hsn}" placeholder="HSN Code">
                </div>
                <div class="col-xl-2 col-lg-2 col-md-4 col-6">
                    <label class="form-label fs-11 text-muted fw-bold text-uppercase mb-1">GST Rate</label>
                    <select name="items[${idx}][gst_rate]" class="form-select form-select-sm">
                        ${gstOptions}
                    </select>
                </div>
                <div class="col-xl-2 col-lg-2 col-md-4 col-6">
                    <label class="form-label fs-11 text-muted fw-bold text-uppercase mb-1">Quantity <span class="text-danger">*</span></label>
                    <input type="number" step="0.001" min="0.001" name="items[${idx}][quantity]" class="form-control form-control-sm item-qty text-end fw-semibold" value="${qty}" required>
                </div>
                <div class="col-xl-2 col-lg-2 col-md-4 col-6">
                    <label class="form-label fs-11 text-muted fw-bold text-uppercase mb-1">Purch. Price (₹) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="items[${idx}][purchase_price]" class="form-control form-control-sm item-price text-end fw-bold text-slate-800" value="${pPrice}" required>
                </div>
                <div class="col-xl-2 col-lg-2 col-md-4 col-6">
                    <label class="form-label fs-11 text-muted fw-bold text-uppercase mb-1">MRP (₹) <span class="text-danger">*</span></label>
                    <input type="number" step="0.01" min="0" name="items[${idx}][mrp]" class="form-control form-control-sm item-mrp text-end ${(!mrp || parseFloat(mrp) <= 0) ? 'border-warning bg-warning-subtle' : ''}" value="${mrp}" placeholder="MRP" required>
                    ${mrpBadge}
                </div>
                <div class="col-xl-2 col-lg-2 col-md-4 col-6">
                    <label class="form-label fs-11 text-primary fw-bold text-uppercase mb-1 d-flex align-items-center justify-content-between">
                        <span>CSP (₹) <span class="text-danger">*</span></span>
                        <span class="badge bg-primary-subtle text-primary fs-10 py-0 px-1">Manual</span>
                    </label>
                    <input type="number" step="0.01" min="0.01" name="items[${idx}][csp]" class="form-control form-control-sm item-csp text-end text-primary fw-bold ${(!csp || parseFloat(csp) <= 0) ? 'border-primary bg-primary-subtle bg-opacity-10' : ''}" value="${csp > 0 ? csp : ''}" placeholder="Enter CSP" required>
                    ${cspBadge}
                </div>
            </div>
        `;

        container.appendChild(block);

        // Attach event listeners
        const priceInput = block.querySelector('.item-price');
        const mrpInput = block.querySelector('.item-mrp');
        const cspInput = block.querySelector('.item-csp');
        const qtyInput = block.querySelector('.item-qty');
        const catSelect = block.querySelector('.item-category-select');

        // Price, MRP & Qty update totals. CSP is strictly manual and never altered!
        priceInput.addEventListener('input', updateCalculations);
        qtyInput.addEventListener('input', updateCalculations);

        mrpInput.addEventListener('input', () => {
            const m = parseFloat(mrpInput.value || 0);
            if (m > 0) {
                mrpInput.classList.remove('border-warning', 'bg-warning-subtle');
                const hint = block.querySelector('.mrp-warning-hint');
                if (hint) hint.remove();
            } else {
                mrpInput.classList.add('border-warning', 'bg-warning-subtle');
            }
        });

        // CSP manual input listener
        cspInput.addEventListener('input', () => {
            const c = parseFloat(cspInput.value || 0);
            if (c > 0) {
                cspInput.classList.remove('border-primary', 'bg-primary-subtle', 'bg-opacity-10', 'border-danger', 'bg-danger-subtle');
                const hint = block.querySelector('.csp-manual-hint');
                if (hint) hint.remove();
            } else {
                cspInput.classList.add('border-primary', 'bg-primary-subtle', 'bg-opacity-10');
            }
        });

        // Category selection listener
        catSelect.addEventListener('change', () => {
            if (catSelect.value) {
                catSelect.classList.remove('border-warning', 'bg-warning-subtle', 'border-danger', 'bg-danger-subtle');
                const hint = block.querySelector('.cat-warning-hint');
                if (hint) hint.remove();
            } else {
                catSelect.classList.add('border-warning', 'bg-warning-subtle');
            }
        });

        updateCalculations();
    }

    // ── Remove Product Entry ──
    function removeProductRow(idx) {
        const row = document.getElementById(`row-${idx}`);
        if (row) {
            row.remove();
            // Re-index remaining badges
            document.querySelectorAll('.product-item-row').forEach((item, i) => {
                const badge = item.querySelector('.product-index-badge');
                if (badge) badge.textContent = `#${i + 1}`;
            });
            updateCalculations();
        }
    }

    document.getElementById('btn-add-product-row').addEventListener('click', () => {
        addProductRow();
    });

    // ── Calculate Grand Totals across all Entries ──
    function updateCalculations() {
        const rows = document.querySelectorAll('.product-item-row');
        let totalItems = 0;
        let totalQty = 0;
        let grandTotal = 0;

        rows.forEach(r => {
            totalItems++;
            const q = parseFloat(r.querySelector('.item-qty')?.value || 0);
            const p = parseFloat(r.querySelector('.item-price')?.value || 0);
            totalQty += q;
            grandTotal += (q * p);
        });

        const badgeCount = document.getElementById('items-count-badge');
        if (badgeCount) badgeCount.textContent = totalItems;
        const sumItems = document.getElementById('summary-total-items');
        if (sumItems) sumItems.textContent = totalItems;
        const sumQty = document.getElementById('summary-total-qty');
        if (sumQty) sumQty.textContent = totalQty.toFixed(2);
        const sumTotal = document.getElementById('summary-grand-total');
        if (sumTotal) sumTotal.textContent = '₹' + grandTotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    // ── Rescan / Reset Buttons ──
    document.getElementById('btn-scan-another').addEventListener('click', resetScanner);
    document.getElementById('btn-cancel-review').addEventListener('click', resetScanner);

    function resetScanner() {
        document.getElementById('scanner-review-container').classList.add('d-none');
        document.getElementById('scanner-input-container').classList.remove('d-none');
        document.getElementById('selected-file-info').classList.add('d-none');
        fileInput.value = '';
        capturedBase64 = null;
    }

    // ── Form Submission (Save & Stock-In) ──
    const saveForm = document.getElementById('stock-save-form');
    saveForm.addEventListener('submit', async (e) => {
        e.preventDefault();

        const items = document.querySelectorAll('.product-item-row');
        if (items.length === 0) {
            Swal.fire('No Products', 'Please extract or add at least one product row to stock in.', 'warning');
            return;
        }

        // Validate Category Selection on all items
        let missingCat = false;
        items.forEach(it => {
            const cat = it.querySelector('.item-category-select');
            if (!cat || !cat.value) {
                missingCat = true;
                cat.classList.add('border-danger', 'bg-danger-subtle');
                cat.focus();
            }
        });
        if (missingCat) {
            Swal.fire({
                icon: 'warning',
                title: 'Category Required',
                text: 'Please select a Category for every product item (cannot be left as "No Selection").',
                confirmButtonColor: '#eab308'
            });
            return;
        }

        // Validate Manual CSP Entry on all items
        let missingCsp = false;
        items.forEach(it => {
            const csp = it.querySelector('.item-csp');
            if (!csp || !csp.value || parseFloat(csp.value) <= 0) {
                missingCsp = true;
                csp.classList.add('border-danger', 'bg-danger-subtle');
                csp.focus();
            }
        });
        if (missingCsp) {
            Swal.fire({
                icon: 'warning',
                title: 'CSP Required',
                text: 'Please enter the Customer Sale Price (CSP) manually for all products before saving.',
                confirmButtonColor: '#3b82f6'
            });
            return;
        }

        const confirmRes = await Swal.fire({
            title: 'Confirm Stock-In?',
            text: `Are you sure you want to stock-in ${items.length} product item(s) from this invoice?`,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: 'Yes, Confirm & Stock In',
            cancelButtonText: 'Review Details',
            confirmButtonColor: '#16a34a'
        });

        if (!confirmRes.isConfirmed) return;

        const btnSubmit = document.getElementById('btn-confirm-stock-in');
        const origText = btnSubmit.innerHTML;
        btnSubmit.disabled = true;
        btnSubmit.innerHTML = `<span class="spinner-border spinner-border-sm me-2"></span>Saving Stock...`;

        try {
            const formData = new FormData(saveForm);

            const res = await fetch(saveRoute, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json'
                },
                body: formData
            });

            const result = await res.json();
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = origText;

            if (result.success) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Stock In Successful!',
                    text: result.message,
                    confirmButtonColor: '#16a34a'
                });

                if (result.redirect_url) {
                    window.location.href = result.redirect_url;
                } else {
                    resetScanner();
                }
            } else {
                if (result.is_duplicate_error) {
                    const allowDup = await Swal.fire({
                        icon: 'warning',
                        title: 'Duplicate Detected',
                        text: result.message + ' Do you still want to proceed and add stock again?',
                        showCancelButton: true,
                        confirmButtonText: 'Force Add Stock',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#ea580c'
                    });

                    if (allowDup.isConfirmed) {
                        formData.append('allow_duplicate', '1');
                        // Retry with allow_duplicate
                        btnSubmit.disabled = true;
                        const res2 = await fetch(saveRoute, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' }, body: formData });
                        const result2 = await res2.json();
                        btnSubmit.disabled = false;
                        if (result2.success) {
                            Swal.fire('Success', result2.message, 'success').then(() => {
                                window.location.href = result2.redirect_url;
                            });
                        } else {
                            Swal.fire('Error', result2.message, 'error');
                        }
                    }
                } else {
                    Swal.fire('Save Failed', result.message || 'Validation error while saving inventory.', 'error');
                }
            }
        } catch (err) {
            btnSubmit.disabled = false;
            btnSubmit.innerHTML = origText;
            Swal.fire('Error', 'Server request failed: ' + err.message, 'error');
        }
    });

</script>
@endpush
