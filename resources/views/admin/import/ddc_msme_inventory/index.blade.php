@extends('layouts.admin')

@section('title', 'DDC MSME Stock Inventory Import')
@section('page-title', 'DDC MSME Stock Inventory Import')

@section('content')
    <div class="container-fluid">
        <div class="row">
            <div class="col-12">
                <!-- Header Card -->
                <div class="card mb-4 border-0 shadow-sm"
                    style="border-radius: 12px; background: linear-gradient(135deg, #d97706 0%, #92400e 100%);">
                    <div class="card-body p-4 text-white">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                            <div>
                                <h3 class="fw-bold text-white mb-1">
                                    <iconify-icon icon="solar:box-bold-duotone" class="align-middle me-2"></iconify-icon>
                                    DDC MSME Stock Inventory Import
                                </h3>
                                <p class="mb-0 text-white-50 fs-14">
                                    Bulk import MSME product stock directly into <strong>ddc_inventories</strong> table.
                                    All 23 fields supported — ideal for migrating data from old systems.
                                </p>
                            </div>
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <a href="{{ route('admin.import.ddc_msme_inventory.sample') }}"
                                    class="btn btn-light fw-bold shadow-sm" style="border-radius: 8px; color: #92400e;">
                                    <iconify-icon icon="solar:download-square-bold-duotone"
                                        class="fs-18 align-middle me-1"></iconify-icon>
                                    Download Sample Excel (23 Columns)
                                </a>
                                <button type="button" class="btn btn-danger fw-bold shadow-sm" id="btnClearImportData" style="border-radius: 8px;">
                                    <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="fs-18 align-middle me-1"></iconify-icon>
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
                            <iconify-icon icon="solar:document-text-bold-duotone"
                                class="fs-18 align-middle me-1 text-warning"></iconify-icon>
                            Excel Column Reference — All 23 Columns (A to W)
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
                                        <td>DDC Code</td>
                                        <td><span class="badge bg-danger-subtle text-danger">Required</span></td>
                                        <td>e.g. DDB001</td>
                                        <td><strong>M</strong></td>
                                        <td>MSP</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>Minimum Sale Price</td>
                                    </tr>
                                    <tr>
                                        <td><strong>B</strong></td>
                                        <td>Product Code</td>
                                        <td><span class="badge bg-danger-subtle text-danger">Required</span></td>
                                        <td>Auto-matches catalog if exists (optional)</td>
                                        <td><strong>N</strong></td>
                                        <td>CSP</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>Canteen Sale Price</td>
                                    </tr>
                                    <tr>
                                        <td><strong>C</strong></td>
                                        <td>Product Name Override</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>Custom product name (Optional)</td>
                                        <td><strong>O</strong></td>
                                        <td>Margin</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>Exact Excel value</td>
                                    </tr>
                                    <tr>
                                        <td><strong>D</strong></td>
                                        <td>Barcode</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td></td>
                                        <td><strong>P</strong></td>
                                        <td>ADC Margin %</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>Percentage</td>
                                    </tr>
                                    <tr>
                                        <td><strong>E</strong></td>
                                        <td>Unit of Measure</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>Default: Piece</td>
                                        <td><strong>Q</strong></td>
                                        <td>CSP Margin %</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>Percentage</td>
                                    </tr>
                                    <tr>
                                        <td><strong>F</strong></td>
                                        <td>Quantity</td>
                                        <td><span class="badge bg-danger-subtle text-danger">Required</span></td>
                                        <td>Supports decimals</td>
                                        <td><strong>R</strong></td>
                                        <td>GST Rate %</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>Exact Excel value</td>
                                    </tr>
                                    <tr>
                                        <td><strong>G</strong></td>
                                        <td>Reserved Qty</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>Default: 0</td>
                                        <td><strong>S</strong></td>
                                        <td>Mfg Date</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>YYYY-MM-DD</td>
                                    </tr>
                                    <tr>
                                        <td><strong>H</strong></td>
                                        <td>Sold Qty</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>Default: 0</td>
                                        <td><strong>T</strong></td>
                                        <td>Expire Date</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>YYYY-MM-DD</td>
                                    </tr>
                                    <tr>
                                        <td><strong>I</strong></td>
                                        <td>Returned Qty</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>Default: 0</td>
                                        <td><strong>U</strong></td>
                                        <td>Batch Number</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td><strong>J</strong></td>
                                        <td>MRP</td>
                                        <td><span class="badge bg-danger-subtle text-danger">Required</span></td>
                                        <td>Exact Excel value</td>
                                        <td><strong>V</strong></td>
                                        <td>Reference Type</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>MSME PO / Local Purchase</td>
                                    </tr>
                                    <tr>
                                        <td><strong>K</strong></td>
                                        <td>Purchase Price</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td></td>
                                        <td><strong>W</strong></td>
                                        <td>POS Status</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>active / inactive</td>
                                    </tr>
                                    <tr>
                                        <td><strong>L</strong></td>
                                        <td>Landing Cost</td>
                                        <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                        <td>Defaults to Purchase Price</td>
                                        <td colspan="4"></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Upload Card -->
                <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px;">
                    <div class="card-header bg-transparent border-bottom p-3">
                        <h5 class="card-title fw-bold text-slate-800 mb-0">Step 1: Upload Inventory Excel File</h5>
                    </div>
                    <div class="card-body p-4">
                        <form id="ddcMsmeInventoryUploadForm" enctype="multipart/form-data">
                            @csrf
                            <div class="row align-items-center">
                                <div class="col-md-9 mb-3 mb-md-0">
                                    <label for="excel_file" class="form-label fw-semibold text-slate-700">Select Excel File
                                        (.xlsx, .csv, .xls) — Max 150MB</label>
                                    <input type="file" class="form-control form-control-lg" id="excel_file" name="file"
                                        accept=".xlsx,.xls,.csv" required>
                                    <div class="form-text text-muted fs-12 mt-1">
                                        Mandatory: <strong>A: DDC Code</strong>, <strong>B: Product Code</strong>,
                                        <strong>F: Quantity</strong>, <strong>J: MRP</strong>.
                                        All other columns are optional. Download sample Excel for full reference.
                                    </div>
                                </div>
                                <div class="col-md-3 text-md-end">
                                    <button type="submit" class="btn btn-lg w-100 fw-bold text-white" id="btnPreview"
                                        style="border-radius: 8px; background: #d97706; border-color: #d97706;">
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
                            <p class="text-muted fs-12 mb-0">Review all fields before final import.</p>
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
                                style="border-radius: 8px; background: #d97706; border-color: #d97706;">
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
                                        <th>DDC</th>
                                        <th>Product</th>
                                        <th>Qty</th>
                                        <th>Res.</th>
                                        <th>Sold</th>
                                        <th>Ret.</th>
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
                                    <span class="fw-semibold text-success fs-13">Inventory Rows Added</span>
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
                                        <th>DDC / Product</th>
                                        <th>Name</th>
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

        document.getElementById('ddcMsmeInventoryUploadForm').addEventListener('submit', function (e) {
            e.preventDefault();
            const formData = new FormData(this);
            const btn = document.getElementById('btnPreview');
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Validating...`;
            document.getElementById('previewCard').classList.add('d-none');
            document.getElementById('reportCard').classList.add('d-none');

            fetch("{{ route('admin.import.ddc_msme_inventory.preview') }}", {
                method: "POST",
                headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: formData
            })
                .then(r => r.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = `<iconify-icon icon="solar:magnifer-linear" class="fs-18 align-middle me-1"></iconify-icon> Upload &amp; Preview`;
                    if (!data.success) { alert(data.message || 'Error processing file'); return; }
                    parsedPreviewRecords = data.records;
                    renderPreview(data);
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerHTML = `<iconify-icon icon="solar:magnifer-linear" class="fs-18 align-middle me-1"></iconify-icon> Upload &amp; Preview`;
                    alert('Unexpected server error.');
                });
        });

        function f(v) { return (v !== null && v !== undefined && v !== '') ? v : '—'; }
        function fc(v) { return (v !== null && v !== undefined && v !== '') ? '₹' + parseFloat(v).toFixed(2) : '—'; }

        function renderPreview(data) {
            document.getElementById('badgeTotal').textContent = `Total: ${data.total}`;
            document.getElementById('badgeValid').textContent = `Valid: ${data.valid}`;
            document.getElementById('badgeErrors').textContent = `Errors: ${data.errors}`;

            const tbody = document.getElementById('previewTableBody');
            tbody.innerHTML = '';
            data.records.forEach(row => {
                const tr = document.createElement('tr');
                if (!row.is_valid) tr.classList.add('row-error');
                const remarks = row.is_valid
                    ? `<span class="text-success fw-semibold">✓ Ready</span>`
                    : `<ul class="mb-0 ps-3 fs-11 text-danger">${(row.errors || []).map(e => `<li>${e}</li>`).join('')}</ul>`;
                tr.innerHTML = `
                <td class="fw-bold">${row.row_number}</td>
                <td><span class="badge font-monospace" style="background:#fef3c7;color:#92400e;">${f(row.ddc_code)}</span><div class="fs-11 text-muted">${f(row.ddc_name)}</div></td>
                <td><div class="fw-semibold">${f(row.product_name)}</div><code class="fs-11">${f(row.product_code)}</code></td>
                <td>${f(row.quantity)}</td><td>${f(row.reserved_qty)}</td><td>${f(row.sold_qty)}</td><td>${f(row.returned_qty)}</td>
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

        document.getElementById('btnExportPreviewErrors')?.addEventListener('click', function() {
            window.exportImportPreviewToExcel('DDC_MSME_Inventory_Import_Errors.xlsx', parsedPreviewRecords, true);
        });

        document.getElementById('btnExportPreviewAll')?.addEventListener('click', function() {
            window.exportImportPreviewToExcel('DDC_MSME_Inventory_Import_All_Preview.xlsx', parsedPreviewRecords, false);
        });

        document.getElementById('btnProcessImport').addEventListener('click', function () {
            if (!parsedPreviewRecords || parsedPreviewRecords.length === 0) return;
            if (!confirm('Are you sure you want to import valid inventory rows into ddc_inventories?')) return;
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Importing...`;
            fetch("{{ route('admin.import.ddc_msme_inventory.process') }}", {
                method: "POST",
                headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
                body: JSON.stringify({ records: parsedPreviewRecords })
            })
                .then(r => r.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = `<iconify-icon icon="solar:check-read-bold-duotone" class="fs-18 align-middle me-1"></iconify-icon> Confirm &amp; Import`;
                    if (!data.success) { alert(data.message || 'Import failed'); return; }
                    renderReport(data);
                })
                .catch(() => {
                    btn.disabled = false;
                    btn.innerHTML = `<iconify-icon icon="solar:check-read-bold-duotone" class="fs-18 align-middle me-1"></iconify-icon> Confirm &amp; Import`;
                    alert('Error during import.');
                });
        });

        let lastReportDetails = [];

        function renderReport(data) {
            document.getElementById('reportImported').textContent = data.imported ?? data.inserted ?? 0;
            document.getElementById('reportSkipped').textContent = data.skipped ?? 0;
            document.getElementById('reportFailed').textContent = data.failed ?? 0;
            lastReportDetails = data.details || [];
            const tbody = document.getElementById('reportLogBody');
            tbody.innerHTML = '';
            (data.details || []).forEach(log => {
                const tr = document.createElement('tr');
                const cls = (log.status === 'Imported' || log.status === 'Success' || log.status === 'Inserted') ? 'bg-success-subtle text-success' : log.status === 'Failed' ? 'bg-danger-subtle text-danger' : 'bg-warning-subtle text-warning';
                tr.innerHTML = `<td>${log.row}</td><td><code class="fs-12">${log.identifier}</code></td><td class="fs-13">${log.name}</td><td><span class="badge ${cls}">${log.status}</span></td><td class="small">${log.message}</td>`;
                tbody.appendChild(tr);
            });
            document.getElementById('reportCard').classList.remove('d-none');
            window.scrollTo({ top: document.getElementById('reportCard').offsetTop - 20, behavior: 'smooth' });
        }

        document.getElementById('btnExportReportErrors')?.addEventListener('click', function () {
            window.exportImportLogsToExcel('DDC_MSME_Inventory_Skipped_Failed_Records.xlsx', lastReportDetails, true);
        });

        document.getElementById('btnExportReportAll')?.addEventListener('click', function () {
            window.exportImportLogsToExcel('DDC_MSME_Inventory_All_Report_Logs.xlsx', lastReportDetails, false);
        });

        document.getElementById('btnClearImportData')?.addEventListener('click', function() {
            if (!confirm('Are you sure you want to clear/delete all imported data for this module? This action cannot be undone.')) return;
            const btn = this;
            btn.disabled = true;
            btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Clearing...`;
            fetch("{{ route('admin.import.ddc_msme_inventory.clear') }}", {
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