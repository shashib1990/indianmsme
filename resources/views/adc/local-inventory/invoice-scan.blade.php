@extends('layouts.adc')

@section('title', 'Smart Invoice Scanner — Local Inventory')
@section('page-title', 'Smart Invoice Scanner')

@push('styles')
<style>
    /* ── Scanner UI Styles ── */
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
</style>
@endpush

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">

    {{-- ── Header Card ── --}}
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0 rounded-4">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                <h4 class="fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2" style="font-family: 'Outfit', sans-serif;">
                                    <iconify-icon icon="solar:scanner-bold-duotone" class="text-primary fs-28"></iconify-icon>
                                    Smart Invoice Scanner
                                </h4>
                                @if(!empty($geminiConfigured))
                                    <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-1 fs-11 fw-semibold d-inline-flex align-items-center gap-1">
                                        <iconify-icon icon="solar:stars-bold-duotone" class="fs-13 text-primary"></iconify-icon> Gemini AI ({{ $geminiModel }})
                                    </span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger border border-danger-subtle rounded-pill px-3 py-1 fs-11 fw-semibold d-inline-flex align-items-center gap-1" title="Set GEMINI_API_KEY in .env to activate Google Gemini multimodal AI">
                                        <iconify-icon icon="solar:danger-circle-bold-duotone" class="fs-13 text-danger"></iconify-icon> Gemini Key Pending (.env)
                                    </span>
                                @endif
                            </div>
                            <p class="text-muted small mb-0">Upload or capture any regional supplier invoice (Image/PDF). AI multimodal understanding auto-extracts supplier, invoice details &amp; product rows.</p>
                        <div class="col-md-5 text-md-end d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
                            <a href="{{ route('adc.local-purchases.index') }}" class="btn btn-outline-secondary btn-sm rounded-3 fw-semibold">
                                <iconify-icon icon="solar:history-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Purchase History
                            </a>
                            <a href="{{ route('adc.local-products.index') }}" class="btn btn-outline-primary btn-sm rounded-3 fw-semibold">
                                <iconify-icon icon="solar:shop-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Regional Products
                            </a>
                            <a href="{{ route('adc.local-purchases.create') }}" class="btn btn-outline-success btn-sm rounded-3 fw-semibold">
                                <iconify-icon icon="solar:pen-new-square-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Manual Stock In
                            </a>
                        </div>
                    </div>
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
                    <h5 class="fw-bold text-slate-800 mb-1" id="scan-status-title">Processing Invoice with Gemini AI...</h5>
                    <p class="text-muted small mb-0" id="scan-status-desc">Multimodal AI is analyzing document layout, supplier details &amp; product rows...</p>
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
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <h6 class="fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2">
                                    <iconify-icon icon="solar:user-bold-duotone" class="text-success fs-20"></iconify-icon>
                                    1. Supplier &amp; Invoice Information
                                </h6>
                                <span id="badge-engine-type" class="badge rounded-pill px-3 py-1 fs-12 fw-semibold d-none"></span>
                            </div>
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

                    {{-- Card 2: Extracted Product Items Table --}}
                    <div class="card shadow-sm border-0 rounded-4 mb-4">
                        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                            <h6 class="fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2">
                                <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="text-primary fs-20"></iconify-icon>
                                2. Extracted Product Rows (<span id="items-count-badge">0</span> Items)
                            </h6>
                            <button type="button" class="btn btn-sm btn-success rounded-3 px-3 shadow-sm" id="btn-add-product-row">
                                <iconify-icon icon="solar:add-circle-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Add Row
                            </button>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0" id="extracted-items-table">
                                    <thead class="bg-light fs-11 text-uppercase fw-bold text-muted border-bottom">
                                        <tr>
                                            <th style="min-width: 220px;" class="ps-3">Product Name &amp; Match</th>
                                            <th style="min-width: 140px;">Category</th>
                                            <th style="min-width: 130px;">Brand</th>
                                            <th style="width: 80px;">HSN</th>
                                            <th style="width: 75px;">GST%</th>
                                            <th style="width: 90px;">Unit</th>
                                            <th style="width: 75px;">Qty</th>
                                            <th style="width: 85px;">Purch. Price</th>
                                            <th style="width: 85px;">MRP</th>
                                            <th style="width: 85px;">CSP</th>
                                            <th style="width: 50px;" class="pe-3 text-center">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody id="extracted-items-tbody">
                                        {{-- Dynamic extracted product rows inserted via JS --}}
                                    </tbody>
                                </table>
                            </div>
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
    const csrfToken = "{{ csrf_token() }}";

    let currentRotation = 0;
    let currentZoom = 1;
    let cameraStream = null;
    let capturedBase64 = null;
    let rowIndexCounter = 0;

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

    // ── Backend Process Call (Gemini AI Vision Extraction) ──
    async function processInvoiceData(payload, isFormData = false) {
        showProgress('Analyzing Invoice with Gemini AI...', 'Reading supplier info, product rows, rates & pricing via AI vision...');

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
            engineBadge.innerHTML = '<iconify-icon icon="solar:stars-bold-duotone" class="fs-14 text-primary"></iconify-icon> ' + (data.engine_label || 'Google Gemini AI');
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

        // Populate Extracted Products Table
        const tbody = document.getElementById('extracted-items-tbody');
        tbody.innerHTML = '';
        rowIndexCounter = 0;

        const products = data.products || [];
        if (products.length === 0) {
            // Add at least one empty row for manual entry
            addProductRow();
        } else {
            products.forEach(item => {
                addProductRow(item);
            });
        }

        updateCalculations();
    }

    // ── Add Product Row to Table ──
    function addProductRow(data = {}) {
        const tbody = document.getElementById('extracted-items-tbody');
        const idx = rowIndexCounter++;

        const tr = document.createElement('tr');
        tr.id = `row-${idx}`;

        const pName = data.product_name || '';
        const matchType = data.match_type || 'NEW';
        const matchSource = data.match_source || '';
        const selectedProductId = data.local_product_id || 'NEW';
        const categoryId = data.category_id || 1;
        const brandId = data.brand_id || '';
        const hsn = data.hsn_code || '';
        const gst = data.gst_rate !== undefined ? parseFloat(data.gst_rate) : 0;
        const uom = data.unit_of_measure || 'Piece';
        const qty = data.quantity || 1;
        const pPrice = data.purchase_price !== undefined ? parseFloat(data.purchase_price).toFixed(2) : '0.00';
        const mrp = data.mrp !== undefined ? parseFloat(data.mrp).toFixed(2) : '0.00';
        const csp = data.csp !== undefined ? parseFloat(data.csp).toFixed(2) : '0.00';
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

        // Category Options
        let catOptions = '';
        categoriesList.forEach(c => {
            catOptions += `<option value="${c.id}" ${c.id == categoryId ? 'selected' : ''}>${c.name}</option>`;
        });

        // Brand Options
        let brandOptions = '<option value="">None / Unbranded</option>';
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

        // Missing field warnings
        const mrpBadge = (parseFloat(mrp) <= 0) ? `<div class="text-danger fs-10 fw-semibold mt-0.5"><iconify-icon icon="solar:danger-circle-bold" class="align-middle"></iconify-icon> Enter MRP</div>` : '';
        const cspBadge = (parseFloat(csp) <= 0) ? `<div class="text-warning fs-10 fw-semibold mt-0.5"><iconify-icon icon="solar:danger-circle-bold" class="align-middle"></iconify-icon> Enter CSP</div>` : '';

        tr.innerHTML = `
            <td class="ps-3">
                <div class="d-flex align-items-center gap-1 mb-1">
                    ${matchBadgeHtml}
                    ${matchSource ? `<small class="text-muted fs-11 text-truncate" style="max-width: 140px;" title="${matchSource}">${matchSource}</small>` : ''}
                </div>
                <input type="text" name="items[${idx}][product_name]" class="form-control table-input-sm fw-semibold mb-1 item-name-input" value="${pName}" placeholder="Product Name" required>
                <select name="items[${idx}][local_product_id]" class="form-select table-input-sm text-muted fs-11 item-product-select">
                    ${productMasterOptions}
                </select>
                <input type="hidden" name="items[${idx}][barcode]" value="${barcode}">
                <input type="hidden" name="items[${idx}][hsn_code]" value="${hsn}">
            </td>
            <td>
                <select name="items[${idx}][category_id]" class="form-select table-input-sm" required>
                    ${catOptions}
                </select>
            </td>
            <td>
                <select name="items[${idx}][brand_id]" class="form-select table-input-sm">
                    ${brandOptions}
                </select>
            </td>
            <td>
                <input type="text" name="items[${idx}][hsn_code]" class="form-control table-input-sm" value="${hsn}" placeholder="HSN">
            </td>
            <td>
                <select name="items[${idx}][gst_rate]" class="form-select table-input-sm">
                    ${gstOptions}
                </select>
            </td>
            <td>
                <select name="items[${idx}][unit_of_measure]" class="form-select table-input-sm">
                    ${unitOptions}
                </select>
            </td>
            <td>
                <input type="number" step="0.001" min="0.001" name="items[${idx}][quantity]" class="form-control table-input-sm item-qty text-end" value="${qty}" required>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${idx}][purchase_price]" class="form-control table-input-sm item-price text-end fw-bold text-slate-800" value="${pPrice}" required>
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${idx}][mrp]" class="form-control table-input-sm item-mrp text-end ${parseFloat(mrp) <= 0 ? 'border-warning bg-warning-subtle' : ''}" value="${mrp}" placeholder="MRP" required>
                ${mrpBadge}
            </td>
            <td>
                <input type="number" step="0.01" min="0" name="items[${idx}][csp]" class="form-control table-input-sm item-csp text-end text-success fw-bold ${parseFloat(csp) <= 0 ? 'border-warning bg-warning-subtle' : ''}" value="${csp}" placeholder="CSP" required>
                ${cspBadge}
            </td>
            <td class="pe-3 text-center">
                <button type="button" class="btn btn-sm btn-outline-danger p-1 rounded-2" onclick="removeProductRow(${idx})" title="Remove Row">
                    <iconify-icon icon="solar:trash-bin-trash-bold" class="fs-16"></iconify-icon>
                </button>
            </td>
        `;

        tbody.appendChild(tr);

        // Attach recalculation listeners (isolated to this row)
        const priceInput = tr.querySelector('.item-price');
        const mrpInput = tr.querySelector('.item-mrp');
        const cspInput = tr.querySelector('.item-csp');
        const qtyInput = tr.querySelector('.item-qty');

        // Only calculate CSP if MRP > Purchase Price and Purchase Price > 0
        // Never guess or fabricate CSP if MRP is missing
        [priceInput, mrpInput].forEach(inp => {
            inp.addEventListener('input', () => {
                const p = parseFloat(priceInput.value || 0);
                const m = parseFloat(mrpInput.value || 0);
                if (m > p && p > 0) {
                    cspInput.value = (p + ((m - p) / 2)).toFixed(2);
                    cspInput.classList.remove('border-warning', 'bg-warning-subtle');
                }
                if (m > 0) {
                    mrpInput.classList.remove('border-warning', 'bg-warning-subtle');
                }
                updateCalculations();
            });
        });

        qtyInput.addEventListener('input', updateCalculations);
        cspInput.addEventListener('input', () => {
            if (parseFloat(cspInput.value || 0) > 0) {
                cspInput.classList.remove('border-warning', 'bg-warning-subtle');
            }
            updateCalculations();
        });

        updateCalculations();
    }

    function removeProductRow(idx) {
        const row = document.getElementById(`row-${idx}`);
        if (row) {
            row.remove();
            updateCalculations();
        }
    }

    document.getElementById('btn-add-product-row').addEventListener('click', () => {
        addProductRow();
    });

    function updateCalculations() {
        const rows = document.querySelectorAll('#extracted-items-tbody tr');
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

        document.getElementById('items-count-badge').textContent = totalItems;
        document.getElementById('summary-total-items').textContent = totalItems;
        document.getElementById('summary-total-qty').textContent = totalQty.toFixed(2);
        document.getElementById('summary-grand-total').textContent = '₹' + grandTotal.toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
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

        const rowCount = document.querySelectorAll('#extracted-items-tbody tr').length;
        if (rowCount === 0) {
            Swal.fire('No Products', 'Please extract or add at least one product row to stock in.', 'warning');
            return;
        }

        const confirmRes = await Swal.fire({
            title: 'Confirm Stock-In?',
            text: `Are you sure you want to stock-in ${rowCount} product item(s) from this invoice?`,
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
