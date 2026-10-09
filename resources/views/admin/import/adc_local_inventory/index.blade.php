@extends('layouts.admin')

@section('title', 'ADC Local / Regional Stock Inventory Import')
@section('page-title', 'ADC Local / Regional Stock Inventory Import')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Header Card -->
                <div class="card mb-4 border-0 shadow-sm"
                    style="border-radius: 12px; background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);">
                    <div class="card-body p-4 text-white">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div>
                                <h3 class="fw-bold text-white mb-1">
                                    <iconify-icon icon="solar:archive-down-bold-duotone"
                                        class="align-middle me-2"></iconify-icon>
                                    ADC Local / Regional Stock Inventory Import
                                </h3>
                                <p class="mb-0 text-white-50 fs-14">
                                    Bulk import Regional / Local product stock for ADC. If the product does not exist, it is
                                    automatically created in <strong>adc_local_products</strong> and stocked in
                                    <strong>adc_inventories</strong>.
                                </p>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <a href="{{ route('admin.import.adc_local_inventory.sample') }}"
                                    class="btn btn-light fw-bold shadow-sm" style="border-radius: 8px; color: #0369a1;">
                                    <iconify-icon icon="solar:download-square-bold-duotone"
                                        class="fs-18 align-middle me-1"></iconify-icon>
                                    Download Sample Excel (22 Columns)
                                </a>
                                <button type="button" class="btn btn-danger fw-bold shadow-sm" id="btnClearImportData"
                                    style="border-radius: 8px;">
                                    <iconify-icon icon="solar:trash-bin-trash-bold-duotone"
                                        class="fs-18 align-middle me-1"></iconify-icon>
                                    Clear All Data
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Column Reference Card -->
                <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-header bg-transparent border-bottom p-3">
                        <h6 class="fw-bold text-slate-800 mb-0">
                            <iconify-icon icon="solar:document-text-bold-duotone" class="fs-18 align-middle me-1"
                                style="color: #0284c7;"></iconify-icon>
                            Excel Column Reference — All 22 Columns (A to V)
                        </h6>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0 fs-12">
                                <thead class="table-dark">
                                    <tr>
                                        <th>Col</th>
                                        <th>Field</th>
                                        <th>Required</th>
                                        <th>Notes</th>
                                        <th>Col</th>
                                        <th>Field</th>
                                        <th>Required</th>
                                        <th>Notes</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td><strong>A</strong></td>
                                        <td>ADC Code</td>
                                        <td><span class="badge bg-danger-subtle text-danger">Required</span></td>
                                        <td>e.g. ADCB001</td>
                                        <td><strong>L</strong></td>
                                        <td>MSP</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>Wholesale Price</td>
                                    </tr>
                                    <tr>
                                        <td><strong>B</strong></td>
                                        <td>Product Name</td>
                                        <td><span class="badge bg-danger-subtle text-danger">Required</span></td>
                                        <td>Regional product name</td>
                                        <td><strong>M</strong></td>
                                        <td>CSP</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>Canteen Selling Price</td>
                                    </tr>
                                    <tr>
                                        <td><strong>C</strong></td>
                                        <td>Local Product Code</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>Blank = auto-generated (LCL000001)</td>
                                        <td><strong>N</strong></td>
                                        <td>Margin</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>Auto-calculated if blank</td>
                                    </tr>
                                    <tr>
                                        <td><strong>D</strong></td>
                                        <td>Category</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>Category name or ID</td>
                                        <td><strong>O</strong></td>
                                        <td>ADC Margin %</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>Percentage</td>
                                    </tr>
                                    <tr>
                                        <td><strong>E</strong></td>
                                        <td>HSN Code</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>e.g. 1514</td>
                                        <td><strong>P</strong></td>
                                        <td>CSP Margin %</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>Percentage</td>
                                    </tr>
                                    <tr>
                                        <td><strong>F</strong></td>
                                        <td>Barcode</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>EAN / UPC / Custom</td>
                                        <td><strong>Q</strong></td>
                                        <td>GST Rate %</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>e.g. 5, 12, 18</td>
                                    </tr>
                                    <tr>
                                        <td><strong>G</strong></td>
                                        <td>Unit of Measure</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>Default: PCS</td>
                                        <td><strong>R</strong></td>
                                        <td>Mfg Date</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>YYYY-MM-DD</td>
                                    </tr>
                                    <tr>
                                        <td><strong>H</strong></td>
                                        <td>Quantity</td>
                                        <td><span class="badge bg-danger-subtle text-danger">Required</span></td>
                                        <td>Stock-in quantity</td>
                                        <td><strong>S</strong></td>
                                        <td>Expire Date</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>YYYY-MM-DD</td>
                                    </tr>
                                    <tr>
                                        <td><strong>I</strong></td>
                                        <td>MRP</td>
                                        <td><span class="badge bg-danger-subtle text-danger">Required</span></td>
                                        <td>Maximum Retail Price</td>
                                        <td><strong>T</strong></td>
                                        <td>Batch Number</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>Batch code</td>
                                    </tr>
                                    <tr>
                                        <td><strong>J</strong></td>
                                        <td>Purchase Price</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>Defaults to MRP if blank</td>
                                        <td><strong>U</strong></td>
                                        <td>Reference Type</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>Local Purchase / Stock In</td>
                                    </tr>
                                    <tr>
                                        <td><strong>K</strong></td>
                                        <td>Landing Cost</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>Defaults to Purchase Price</td>
                                        <td><strong>V</strong></td>
                                        <td>POS Status</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>active / inactive</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Upload Card -->
                <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-header bg-transparent border-bottom p-3">
                        <h5 class="card-title fw-bold text-slate-800 mb-0">Step 1: Upload Local Inventory Excel File</h5>
                    </div>
                    <div class="card-body p-4">
                        <form id="adcLocalInventoryUploadForm" enctype="multipart/form-data">
                            @csrf
                            <div class="row align-items-center">
                                <div class="col-md-9 mb-3 mb-md-0">
                                    <label for="excel_file" class="form-label fw-semibold text-slate-700">Select Excel File
                                        (.xlsx, .csv, .xls) — Max 150MB</label>
                                    <input type="file" class="form-control form-control-lg" id="excel_file" name="file"
                                        accept=".xlsx,.xls,.csv" required>
                                    <div class="form-text text-muted fs-12 mt-1">
                                        Mandatory: <strong>A: ADC Code</strong>, <strong>B: Product Name</strong>,
                                        <strong>H: Quantity</strong>, <strong>I: MRP</strong>.
                                        If the Local Product does not exist, it will be automatically created on import!
                                    </div>
                                </div>
                                <div class="col-md-3 text-md-end">
                                    <button type="submit" class="btn btn-lg w-100 fw-bold text-white" id="btnPreview"
                                        style="border-radius: 8px; background: #0284c7; border-color: #0284c7;">
                                        <iconify-icon icon="solar:magnifer-linear"
                                            class="fs-18 align-middle me-1"></iconify-icon>
                                        Upload &amp; Preview
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <style>
                    #previewTable tr.row-error,
                    #previewTable tr.row-error>td {
                        background-color: #fee2e2 !important;
                        color: #991b1b !important;
                    }
                </style>

                <!-- Preview Card -->
                <div class="card mb-4 border-0 shadow-sm d-none" id="previewCard" style="border-radius: 12px;">
                    <div
                        class="card-header bg-transparent border-bottom p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div>
                            <h5 class="card-title fw-bold text-slate-800 mb-0">Step 2: Verification Preview</h5>
                            <p class="text-muted fs-12 mb-0">Review all Local Inventory records before final creation &amp;
                                stock in.</p>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <span class="badge bg-primary-subtle text-primary px-3 py-2 fs-13" id="badgeTotal">Total:
                                0</span>
                            <span class="badge bg-success-subtle text-success px-3 py-2 fs-13" id="badgeValid">Valid:
                                0</span>
                            <span class="badge bg-danger-subtle text-danger px-3 py-2 fs-13" id="badgeErrors">Errors:
                                0</span>
                            <button type="button" class="btn btn-outline-danger fw-bold px-3 ms-1" id="btnExportPreviewErrors" style="border-radius: 8px;">
                                <iconify-icon icon="solar:download-square-linear" class="fs-16 align-middle me-1"></iconify-icon> Export Error Records (.xlsx)
                            </button>
                            <button type="button" class="btn btn-outline-secondary fw-bold px-3 ms-1" id="btnExportPreviewAll" style="border-radius: 8px;">
                                <iconify-icon icon="solar:download-square-linear" class="fs-16 align-middle me-1"></iconify-icon> Export All Records (.xlsx)
                            </button>
                            <button type="button" class="btn fw-bold px-4 text-white" id="btnProcessImport"
                                style="border-radius: 8px; background: #0284c7; border-color: #0284c7;">
                                <iconify-icon icon="solar:check-read-bold-duotone"
                                    class="fs-18 align-middle me-1"></iconify-icon>
                                Confirm &amp; Import
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height: 600px;">
                            <table class="table table-hover align-middle mb-0 fs-12" id="previewTable">
                                <thead class="table-light sticky-top">
                                    <tr>
                                        <th>#</th>
                                        <th>ADC</th>
                                        <th>Product Name</th>
                                        <th>Code</th>
                                        <th>Qty</th>
                                        <th>MRP</th>
                                        <th>PP</th>
                                        <th>LC</th>
                                        <th>MSP</th>
                                        <th>CSP</th>
                                        <th>Margin</th>
                                        <th>ADC%</th>
                                        <th>CSP%</th>
                                        <th>GST%</th>
                                        <th>Batch</th>
                                        <th>Mfg</th>
                                        <th>Exp</th>
                                        <th>UOM</th>
                                        <th>Barcode</th>
                                        <th>Ref</th>
                                        <th>Status</th>
                                        <th>Valid</th>
                                        <th>Remarks</th>
                                    </tr>
                                </thead>
                                <tbody id="previewTableBody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="card-footer bg-transparent border-top p-3 d-flex align-items-center justify-content-between flex-wrap gap-2 fs-13">
                        <div class="d-flex align-items-center gap-2">
                            <span class="text-muted" id="previewPaginationSummary">Showing rows 1 - 50 of 0</span>
                            <select id="previewPerPageSelect" class="form-select form-select-sm ms-2" style="width: 140px; border-radius: 6px;">
                                <option value="50" selected>50 rows / page</option>
                                <option value="100">100 rows / page</option>
                                <option value="200">200 rows / page</option>
                                <option value="500">500 rows / page</option>
                                <option value="1000">1000 rows / page</option>
                            </select>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary px-3" id="btnPreviewPrevPage" disabled style="border-radius: 6px;">
                                <iconify-icon icon="solar:alt-arrow-left-linear" class="align-middle me-1"></iconify-icon> Previous
                            </button>
                            <span class="fw-semibold text-slate-700 px-2" id="previewPageIndicator">Page 1 of 1</span>
                            <button type="button" class="btn btn-sm btn-outline-secondary px-3" id="btnPreviewNextPage" disabled style="border-radius: 6px;">
                                Next <iconify-icon icon="solar:alt-arrow-right-linear" class="align-middle ms-1"></iconify-icon>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Progress Card -->
                <div class="card mb-4 border-0 shadow-sm d-none" id="progressCard" style="border-radius: 12px; background: #f8fafc;">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="fw-bold text-slate-800 mb-0 d-flex align-items-center gap-2">
                                <span class="spinner-border spinner-border-sm text-primary" id="progressSpinner"></span>
                                <span id="progressStatusTitle">Importing High-Volume Inventory Data...</span>
                            </h6>
                            <span class="badge bg-primary fs-13 fw-bold px-3 py-1" id="progressPercentBadge">0%</span>
                        </div>
                        <div class="progress mb-3" style="height: 14px; border-radius: 7px; background-color: #e2e8f0;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-primary" id="importProgressBar" role="progressbar" style="width: 0%;"></div>
                        </div>
                        <div class="d-flex align-items-center justify-content-between text-muted fs-13 flex-wrap gap-2">
                            <div id="progressCountDetail" class="fw-semibold text-slate-700">Processed 0 of 0 records</div>
                            <div id="progressLiveStats">Imported: <span id="statImported" class="text-success fw-bold">0</span> | Skipped: <span id="statSkipped" class="text-warning fw-bold">0</span> | Failed: <span id="statFailed" class="text-danger fw-bold">0</span></div>
                        </div>
                    </div>
                </div>

                <!-- Report Card -->
                <div class="card mb-4 border-0 shadow-sm d-none" id="reportCard" style="border-radius: 12px;">
                    <div class="card-header bg-transparent border-bottom p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <h5 class="card-title fw-bold text-slate-800 mb-0">Step 3: Import Summary Report</h5>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <button type="button" class="btn btn-sm btn-outline-danger fw-bold px-3" id="btnExportReportErrors" style="border-radius: 8px;">
                                <iconify-icon icon="solar:download-square-linear" class="fs-16 align-middle me-1"></iconify-icon> Export Skipped &amp; Failed (.xlsx)
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-secondary fw-bold px-3" id="btnExportReportAll" style="border-radius: 8px;">
                                <iconify-icon icon="solar:download-square-linear" class="fs-16 align-middle me-1"></iconify-icon> Export All Logs (.xlsx)
                            </button>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        <div class="row text-center mb-4">
                            <div class="col-md-4 mb-2">
                                <div class="p-3 bg-success-subtle rounded-3">
                                    <h2 class="fw-bold text-success mb-0" id="reportImported">0</h2>
                                    <span class="fw-semibold text-success fs-13">Local Inventory Rows Added</span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="p-3 bg-warning-subtle rounded-3">
                                    <h2 class="fw-bold text-warning mb-0" id="reportSkipped">0</h2>
                                    <span class="fw-semibold text-warning fs-13">Skipped (Invalid)</span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-2">
                                <div class="p-3 bg-danger-subtle rounded-3">
                                    <h2 class="fw-bold text-danger mb-0" id="reportFailed">0</h2>
                                    <span class="fw-semibold text-danger fs-13">Failed (DB Error)</span>
                                </div>
                            </div>
                        </div>
                        <h6 class="fw-bold text-slate-800 mb-2">Detailed Log</h6>
                        <div class="table-responsive" style="max-height: 400px;">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>Row</th>
                                        <th>ADC / Product Code</th>
                                        <th>Product Name</th>
                                        <th>Status</th>
                                        <th>Details</th>
                                    </tr>
                                </thead>
                                <tbody id="reportLogBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let parsedPreviewRecords = [];
        let currentImportToken = null;
        let totalRowsToProcess = 0;
        let currentPreviewPage = 1;
        let totalPreviewPages  = 1;
        let previewPerPage     = 50;

        document.getElementById('adcLocalInventoryUploadForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const btn = document.getElementById('btnPreview');
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Validating &amp; Preparing...`;
            document.getElementById('previewCard').classList.add('d-none');
            document.getElementById('progressCard').classList.add('d-none');
            document.getElementById('reportCard').classList.add('d-none');

            fetch("{{ route('admin.import.adc_local_inventory.preview') }}", {
                method: "POST",
                headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = `<iconify-icon icon="solar:magnifer-linear" class="fs-18 align-middle me-1"></iconify-icon> Upload &amp; Preview`;
                    if (!data.success) { alert(data.message || 'Error processing file'); return; }
                    parsedPreviewRecords = data.records || [];
                    currentImportToken   = data.import_token || null;
                    totalRowsToProcess   = data.total || 0;
                    currentPreviewPage   = 1;
                    renderPreview(data);
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerHTML = `<iconify-icon icon="solar:magnifer-linear" class="fs-18 align-middle me-1"></iconify-icon> Upload &amp; Preview`;
                    alert('Unexpected server error while parsing file.');
                });
        });

        function f(v) { return (v !== null && v !== undefined && v !== '') ? v : '—'; }
        function fc(v) { return (v !== null && v !== undefined && v !== '') ? '₹' + parseFloat(v).toFixed(2) : '—'; }

        function fetchPreviewPage(page) {
            if (!currentImportToken) return;
            const tbody = document.getElementById('previewTableBody');
            tbody.innerHTML = `<tr><td colspan="23" class="text-center py-4 text-muted"><span class="spinner-border spinner-border-sm me-2"></span> Loading page ${page}...</td></tr>`;

            fetch("{{ route('admin.import.adc_local_inventory.preview_page') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({
                    import_token: currentImportToken,
                    page: page,
                    per_page: previewPerPage
                })
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success) { alert(data.message || 'Error loading preview page'); return; }
                currentPreviewPage = data.page;
                totalPreviewPages  = data.total_pages;
                renderPreview(data);
            })
            .catch(() => {
                alert('Error loading preview page.');
            });
        }

        function renderPreview(data) {
            document.getElementById('badgeTotal').textContent = `Total: ${(data.total || 0).toLocaleString()}`;
            document.getElementById('badgeValid').textContent = `Valid: ${(data.valid || 0).toLocaleString()}`;
            document.getElementById('badgeErrors').textContent = `Errors: ${(data.errors || 0).toLocaleString()}`;

            currentPreviewPage = data.page || 1;
            totalPreviewPages  = data.total_pages || Math.ceil((data.total || 0) / previewPerPage);

            const startRow = ((currentPreviewPage - 1) * previewPerPage) + 1;
            const endRow   = Math.min(startRow + (data.records || []).length - 1, data.total || 0);
            document.getElementById('previewPaginationSummary').textContent = `Showing rows ${startRow.toLocaleString()} - ${endRow.toLocaleString()} of ${(data.total || 0).toLocaleString()}`;
            document.getElementById('previewPageIndicator').textContent = `Page ${currentPreviewPage.toLocaleString()} of ${totalPreviewPages.toLocaleString()}`;

            document.getElementById('btnPreviewPrevPage').disabled = (currentPreviewPage <= 1);
            document.getElementById('btnPreviewNextPage').disabled = (currentPreviewPage >= totalPreviewPages);

            const tbody = document.getElementById('previewTableBody');
            tbody.innerHTML = '';
            (data.records || []).forEach(row => {
                const tr = document.createElement('tr');
                if (!row.is_valid) tr.classList.add('row-error');
                const remarks = row.is_valid
                    ? `<span class="text-success fw-semibold">✓ Ready</span>`
                    : `<ul class="mb-0 ps-3 fs-11 text-danger">${(row.errors || []).map(e => `<li>${e}</li>`).join('')}</ul>`;
                tr.innerHTML = `
                <td class="fw-bold">${row.row_number}</td>
                <td><span class="badge font-monospace" style="background:#e0f2fe;color:#0369a1;">${f(row.adc_code)}</span><div class="fs-11 text-muted">${f(row.adc_name)}</div></td>
                <td><div class="fw-semibold">${f(row.product_name)}</div></td>
                <td><code class="fs-11">${row.local_product_code ? row.local_product_code : '(Auto)'}</code></td>
                <td>${f(row.quantity)}</td>
                <td>${fc(row.mrp)}</td><td>${fc(row.purchase_price)}</td><td>${fc(row.landing_cost)}</td>
                <td>${fc(row.msp)}</td><td>${fc(row.csp)}</td><td>${fc(row.margin)}</td>
                <td>${f(row.adc_margin)}%</td><td>${f(row.csp_margin)}%</td><td>${f(row.gst_rate)}%</td>
                <td class="fs-11">${f(row.batch_number)}</td>
                <td class="fs-11">${f(row.mfg_date)}</td>
                <td class="fs-11">${f(row.expire_date)}</td>
                <td>${f(row.unit_of_measure)}</td>
                <td class="fs-11">${f(row.barcode)}</td>
                <td class="fs-11">${f(row.reference_type)}</td>
                <td><span class="badge ${row.pos_status === 'active' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'}">${f(row.pos_status)}</span></td>
                <td>${row.is_valid ? '<span class="badge bg-success-subtle text-success">Valid</span>' : '<span class="badge bg-danger-subtle text-danger">Error</span>'}</td>
                <td>${remarks}</td>
            `;
                tbody.appendChild(tr);
            });

            document.getElementById('previewCard').classList.remove('d-none');
            document.getElementById('btnProcessImport').disabled = (data.valid === 0);
            window.scrollTo({ top: document.getElementById('previewCard').offsetTop - 20, behavior: 'smooth' });
        }

        document.getElementById('btnPreviewPrevPage')?.addEventListener('click', function() {
            if (currentPreviewPage > 1) {
                fetchPreviewPage(currentPreviewPage - 1);
            }
        });

        document.getElementById('btnPreviewNextPage')?.addEventListener('click', function() {
            if (currentPreviewPage < totalPreviewPages) {
                fetchPreviewPage(currentPreviewPage + 1);
            }
        });

        document.getElementById('previewPerPageSelect')?.addEventListener('change', function() {
            previewPerPage = parseInt(this.value, 10);
            fetchPreviewPage(1);
        });




        document.getElementById('btnProcessImport').addEventListener('click', function () {
            if (!currentImportToken && (!parsedPreviewRecords || parsedPreviewRecords.length === 0)) return;
            const totalText = totalRowsToProcess > 0 ? totalRowsToProcess.toLocaleString() : parsedPreviewRecords.length;
            if (!confirm(`Are you sure you want to process high-speed import of ${totalText} records into ADC Local Inventory?`)) return;

            const btn = this;
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Initializing...`;

            document.getElementById('progressCard').classList.remove('d-none');
            document.getElementById('reportCard').classList.add('d-none');
            document.getElementById('importProgressBar').style.width = '0%';
            document.getElementById('progressPercentBadge').textContent = '0%';
            document.getElementById('progressSpinner').classList.remove('d-none');
            document.getElementById('progressStatusTitle').textContent = 'Importing High-Volume Inventory Data...';

            window.scrollTo({ top: document.getElementById('progressCard').offsetTop - 20, behavior: 'smooth' });

            let offset = 0;
            const batchSize = 5000;
            let totalImported = 0;
            let totalSkipped  = 0;
            let totalFailed   = 0;
            let accumulatedDetails = [];

            function executeNextBatch() {
                fetch("{{ route('admin.import.adc_local_inventory.process') }}", {
                    method: "POST",
                    headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                    body: JSON.stringify({
                        import_token: currentImportToken,
                        records: currentImportToken ? undefined : parsedPreviewRecords,
                        offset: offset,
                        batch_size: batchSize
                    })
                })
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        btn.disabled = false;
                        btn.innerHTML = `<iconify-icon icon="solar:check-read-bold-duotone" class="fs-18 align-middle me-1"></iconify-icon> Confirm &amp; Import`;
                        alert(data.message || 'Import failed');
                        return;
                    }

                    if (!currentImportToken) {
                        btn.disabled = false;
                        btn.innerHTML = `<iconify-icon icon="solar:check-read-bold-duotone" class="fs-18 align-middle me-1"></iconify-icon> Confirm &amp; Import`;
                        document.getElementById('progressCard').classList.add('d-none');
                        renderReport(data);
                        return;
                    }

                    totalImported += data.batch_imported || 0;
                    totalSkipped  += data.batch_skipped || 0;
                    totalFailed   += data.batch_failed || 0;

                    if (data.details && data.details.length > 0) {
                        accumulatedDetails = accumulatedDetails.concat(data.details);
                    }

                    const currentProcessed = data.next_offset || (offset + batchSize);
                    const grandTotal = data.total || totalRowsToProcess;
                    const percent = Math.min(100, Math.round((currentProcessed / Math.max(1, grandTotal)) * 100));

                    document.getElementById('importProgressBar').style.width = percent + '%';
                    document.getElementById('progressPercentBadge').textContent = percent + '%';
                    document.getElementById('progressCountDetail').textContent = `Processed ${Math.min(currentProcessed, grandTotal).toLocaleString()} of ${grandTotal.toLocaleString()} records`;
                    document.getElementById('statImported').textContent = totalImported.toLocaleString();
                    document.getElementById('statSkipped').textContent = totalSkipped.toLocaleString();
                    document.getElementById('statFailed').textContent = totalFailed.toLocaleString();

                    if (!data.is_finished && currentProcessed < grandTotal) {
                        offset = data.next_offset;
                        executeNextBatch();
                    } else {
                        document.getElementById('importProgressBar').style.width = '100%';
                        document.getElementById('progressPercentBadge').textContent = '100%';
                        document.getElementById('progressSpinner').classList.add('d-none');
                        document.getElementById('progressStatusTitle').textContent = 'Import Completed Successfully!';

                        btn.disabled = false;
                        btn.innerHTML = `<iconify-icon icon="solar:check-read-bold-duotone" class="fs-18 align-middle me-1"></iconify-icon> Confirm &amp; Import`;

                        renderReport({
                            imported: totalImported,
                            skipped: totalSkipped,
                            failed: totalFailed,
                            details: accumulatedDetails
                        });
                    }
                })
                .catch(err => {
                    console.error('Batch import error:', err);
                    alert('Transient connection error during batch. Retrying in 2 seconds...');
                    setTimeout(executeNextBatch, 2000);
                });
            }

            executeNextBatch();
        });

        let lastReportDetails = [];

        function renderReport(data) {
            document.getElementById('reportImported').textContent = (data.imported || 0).toLocaleString();
            document.getElementById('reportSkipped').textContent = (data.skipped || 0).toLocaleString();
            document.getElementById('reportFailed').textContent = (data.failed || 0).toLocaleString();
            lastReportDetails = data.details || [];
            const tbody = document.getElementById('reportLogBody');
            tbody.innerHTML = '';
            const displayLimit = 1000;
            const allDetails = data.details || [];
            allDetails.slice(0, displayLimit).forEach(log => {
                const tr = document.createElement('tr');
                const st = String(log.status || '').toLowerCase();
                const cls = (st === 'imported' || st === 'success' || st === 'inserted')
                    ? 'bg-success-subtle text-success'
                    : (st === 'failed' || st === 'error')
                        ? 'bg-danger-subtle text-danger'
                        : 'bg-warning-subtle text-warning';
                tr.innerHTML = `<td>${log.row}</td><td><code class="fs-12">${log.identifier}</code></td><td class="fs-13">${log.name}</td><td><span class="badge ${cls}">${log.status}</span></td><td class="small">${log.message}</td>`;
                tbody.appendChild(tr);
            });
            if (allDetails.length > displayLimit) {
                const tr = document.createElement('tr');
                tr.innerHTML = `<td colspan="5" class="text-center text-muted py-3 fs-13">
                    <iconify-icon icon="solar:info-circle-bold-duotone" class="fs-16 me-1 text-primary"></iconify-icon>
                    Showing first <strong>${displayLimit.toLocaleString()}</strong> of <strong>${allDetails.length.toLocaleString()}</strong> log records.
                    Use the <strong>Export</strong> buttons above to download the complete log file.
                </td>`;
                tbody.appendChild(tr);
            }
            document.getElementById('reportCard').classList.remove('d-none');
            window.scrollTo({ top: document.getElementById('reportCard').offsetTop - 20, behavior: 'smooth' });
        }

        document.getElementById('btnExportPreviewErrors')?.addEventListener('click', function() {
            if (currentImportToken) {
                window.location.href = "{{ route('admin.import.adc_local_inventory.export_preview_errors') }}?token=" + encodeURIComponent(currentImportToken);
            } else {
                window.exportImportPreviewToExcel('ADC_Local_Inventory_Import_Errors.xlsx', parsedPreviewRecords, true);
            }
        });

        document.getElementById('btnExportPreviewAll')?.addEventListener('click', function() {
            if (currentImportToken) {
                window.location.href = "{{ route('admin.import.adc_local_inventory.export_preview_errors') }}?token=" + encodeURIComponent(currentImportToken) + "&all=1";
            } else {
                window.exportImportPreviewToExcel('ADC_Local_Inventory_Import_All_Preview.xlsx', parsedPreviewRecords, false);
            }
        });

        document.getElementById('btnExportReportErrors')?.addEventListener('click', function () {
            if (currentImportToken) {
                window.location.href = "{{ route('admin.import.adc_local_inventory.export_import_logs') }}?token=" + encodeURIComponent(currentImportToken) + "&type=failed_skipped";
            } else {
                window.exportImportLogsToExcel('ADC_Local_Inventory_Skipped_Failed_Records.xlsx', lastReportDetails, true);
            }
        });

        document.getElementById('btnExportReportAll')?.addEventListener('click', function () {
            if (currentImportToken) {
                window.location.href = "{{ route('admin.import.adc_local_inventory.export_import_logs') }}?token=" + encodeURIComponent(currentImportToken) + "&type=all";
            } else {
                window.exportImportLogsToExcel('ADC_Local_Inventory_All_Report_Logs.xlsx', lastReportDetails, false);
            }
        });

        document.getElementById('btnClearImportData')?.addEventListener('click', function () {
            if (!confirm('Are you sure you want to clear/delete all imported data for this module? This action cannot be undone.')) return;
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Clearing...`;
            fetch("{{ route('admin.import.adc_local_inventory.clear') }}", {
                method: "POST",
                headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" }
            })
                .then(r => r.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = `<iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="fs-18 align-middle me-1"></iconify-icon> Clear All Data`;
                    alert(data.message || (data.success ? 'Data cleared successfully' : 'Error clearing data'));
                    if (data.success) {
                        if (document.getElementById('previewCard')) document.getElementById('previewCard').classList.add('d-none');
                        if (document.getElementById('progressCard')) document.getElementById('progressCard').classList.add('d-none');
                        if (document.getElementById('reportCard')) document.getElementById('reportCard').classList.add('d-none');
                    }
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerHTML = `<iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="fs-18 align-middle me-1"></iconify-icon> Clear All Data`;
                    alert('Unexpected server error while clearing data.');
                });
        });
    </script>
@endpush