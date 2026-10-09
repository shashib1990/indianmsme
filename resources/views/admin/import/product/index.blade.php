@extends('layouts.admin')

@section('title', 'Product Bulk Import')
@section('page-title', 'Product Bulk Import')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header Card -->
            <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #059669 0%, #047857 100%);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <h3 class="fw-bold text-white mb-1">
                                <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="align-middle me-2"></iconify-icon>
                                Bulk Product Catalog Import
                            </h3>
                            <p class="mb-0 text-white-50 fs-14">
                                Bulk import products using Excel (.xlsx, .csv). Specify Seller ID, Category, Brand, HSN, MRP, and Seller Price. Margin & GST pricing engine calculations will run automatically.
                            </p>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <a href="{{ route('admin.import.product.sample') }}" class="btn btn-light fw-bold text-success shadow-sm" style="border-radius: 8px;">
                                <iconify-icon icon="solar:download-square-bold-duotone" class="fs-18 align-middle me-1"></iconify-icon>
                                Download Sample Excel
                            </a>
                            <button type="button" class="btn btn-danger fw-bold shadow-sm" id="btnClearImportData" style="border-radius: 8px;">
                                <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="fs-18 align-middle me-1"></iconify-icon>
                                Clear All Data
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Card -->
            <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-transparent border-bottom p-3">
                    <h5 class="card-title fw-bold text-slate-800 mb-0">Step 1: Upload Product Excel File</h5>
                </div>
                <div class="card-body p-4">
                    <form id="productUploadForm" action="{{ route('admin.import.product.preview') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row align-items-center">
                            <div class="col-md-9 mb-3 mb-md-0">
                                <label for="excel_file" class="form-label fw-semibold text-slate-700">Select Excel File (.xlsx, .csv, .xls)</label>
                                <input type="file" class="form-control form-control-lg" id="excel_file" name="file" accept=".xlsx,.xls,.csv" required>
                                <div class="form-text text-muted fs-12 mt-1">
                                    Columns with * (Seller ID*, Product Code*, Product Name*, Category*, Brand*, HSN Code*, MRP*, Seller Price (Incl GST)*) are mandatory.
                                </div>
                            </div>
                            <div class="col-md-3 text-md-end">
                                <button type="submit" class="btn btn-success btn-lg w-100 fw-bold" id="btnPreview" style="border-radius: 8px;">
                                    <iconify-icon icon="solar:magnifer-linear" class="fs-18 align-middle me-1"></iconify-icon>
                                    Upload & Preview
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <style>
                #previewTable tr.table-danger,
                #previewTable tr.table-danger > td {
                    background-color: #fee2e2 !important;
                    --bs-table-bg: #fee2e2 !important;
                    --bs-table-accent-bg: #fee2e2 !important;
                    color: #991b1b !important;
                }
                #previewTable tr.table-danger code {
                    background-color: #fca5a5 !important;
                    color: #7f1d1d !important;
                }
            </style>

            <!-- Preview Card (Hidden by default) -->
            <div class="card mb-4 border-0 shadow-sm d-none" id="previewCard" style="border-radius: 12px;">
                <div class="card-header bg-transparent border-bottom p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h5 class="card-title fw-bold text-slate-800 mb-0">Step 2: Dry-Run Verification Preview</h5>
                        <p class="text-muted fs-12 mb-0">Review valid product records, calculated margins engine output, and error logs before importing.</p>
                    </div>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-primary-subtle text-primary px-3 py-2 fs-13" id="badgeTotal">Total: 0</span>
                        <span class="badge bg-success-subtle text-success px-3 py-2 fs-13" id="badgeValid">Valid: 0</span>
                        <span class="badge bg-danger-subtle text-danger px-3 py-2 fs-13" id="badgeErrors">Errors: 0</span>
                        <button type="button" class="btn btn-outline-danger fw-bold px-3 ms-1" id="btnExportPreviewErrors" style="border-radius: 8px;">
                            <iconify-icon icon="solar:download-square-linear" class="fs-18 align-middle me-1"></iconify-icon>
                            Export Error Records (.xlsx)
                        </button>
                        <button type="button" class="btn btn-outline-secondary fw-bold px-3 ms-1" id="btnExportPreviewAll" style="border-radius: 8px;">
                            <iconify-icon icon="solar:download-square-linear" class="fs-18 align-middle me-1"></iconify-icon>
                            Export All Preview (.xlsx)
                        </button>
                        <button type="button" class="btn btn-success fw-bold ms-1 px-4" id="btnProcessImport" style="border-radius: 8px;">
                            <iconify-icon icon="solar:check-read-bold-duotone" class="fs-18 align-middle me-1"></iconify-icon>
                            Confirm &amp; Import Valid Products
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 600px;">
                        <table class="table table-hover align-middle mb-0" id="previewTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 50px;">Row</th>
                                    <th>Seller & Product</th>
                                    <th>Category / Brand</th>
                                    <th>HSN / GST</th>
                                    <th>MRP / Seller Price / Base Cost</th>
                                    <th>Admin & DDC Margins</th>
                                    <th>MBP & Shares</th>
                                    <th>ADC Purchase & Final Margin</th>
                                    <th style="width: 90px;">Status</th>
                                    <th>Validation Remarks</th>
                                </tr>
                            </thead>
                            <tbody id="previewTableBody">
                                <!-- Dynamic JS content -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Import Report Card (Hidden by default) -->
            <div class="card mb-4 border-0 shadow-sm d-none" id="reportCard" style="border-radius: 12px;">
                <div class="card-header bg-transparent border-bottom p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h5 class="card-title fw-bold text-slate-800 mb-0">Step 3: Import Execution Summary Report</h5>
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
                                <span class="fw-semibold text-success fs-13">Successfully Imported</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="p-3 bg-warning-subtle rounded-3">
                                <h2 class="fw-bold text-warning mb-0" id="reportSkipped">0</h2>
                                <span class="fw-semibold text-warning fs-13">Skipped Records</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="p-3 bg-danger-subtle rounded-3">
                                <h2 class="fw-bold text-danger mb-0" id="reportFailed">0</h2>
                                <span class="fw-semibold text-danger fs-13">Failed Records</span>
                            </div>
                        </div>
                    </div>

                    <h6 class="fw-bold text-slate-800 mb-2">Detailed Log</h6>
                    <div class="table-responsive" style="max-height: 400px;">
                        <table class="table table-sm table-bordered mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;">Row</th>
                                    <th>Product Code</th>
                                    <th>Product Name</th>
                                    <th>Status</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody id="reportLogBody">
                                <!-- Dynamic JS content -->
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4 text-end">
                        <a href="{{ route('admin.products.all') }}" class="btn btn-success fw-bold">
                            View All Products
                        </a>
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

document.getElementById('productUploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    const btnPreview = document.getElementById('btnPreview');
    btnPreview.disabled = true;
    btnPreview.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Validating...`;

    document.getElementById('previewCard').classList.add('d-none');
    document.getElementById('reportCard').classList.add('d-none');

    fetch("{{ route('admin.import.product.preview') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btnPreview.disabled = false;
        btnPreview.innerHTML = `<iconify-icon icon="solar:magnifer-linear" class="fs-18 align-middle me-1"></iconify-icon> Upload & Preview`;

        if (!data.success) {
            alert(data.message || 'Error processing Excel file');
            return;
        }

        parsedPreviewRecords = data.records;
        renderPreview(data);
    })
    .catch(err => {
        btnPreview.disabled = false;
        btnPreview.innerHTML = `<iconify-icon icon="solar:magnifer-linear" class="fs-18 align-middle me-1"></iconify-icon> Upload & Preview`;
        alert('An unexpected server error occurred.');
    });
});

function renderPreview(data) {
    document.getElementById('badgeTotal').textContent = `Total: ${data.total}`;
    document.getElementById('badgeValid').textContent = `Valid: ${data.valid}`;
    document.getElementById('badgeErrors').textContent = `Errors: ${data.errors}`;

    const tbody = document.getElementById('previewTableBody');
    tbody.innerHTML = '';

    data.records.forEach(row => {
        const tr = document.createElement('tr');
        if (!row.is_valid) {
            tr.classList.add('table-danger');
            tr.style.backgroundColor = '#fee2e2';
        }

        let remarksHtml = '';
        if (row.is_valid) {
            remarksHtml = `<span class="text-success small fw-semibold"><i class="bx bx-check-circle me-1"></i>Ready for import</span>`;
        } else {
            remarksHtml = `<ul class="mb-0 text-danger ps-3 small fs-12">${row.errors.map(err => `<li>${err}</li>`).join('')}</ul>`;
        }

        const adminMarginStr = row.admin_margin_percentage ? `${row.admin_margin_percentage}% (₹${parseFloat(row.admin_margin_amount || 0).toFixed(2)})` : '—';
        const ddcMarginStr   = row.ddc_margin_percentage ? `${row.ddc_margin_percentage}% (₹${parseFloat(row.ddc_margin_amount || 0).toFixed(2)})` : '—';
        const mbpMarginStr   = row.mbp_percentage ? `${row.mbp_percentage}% (₹${parseFloat(row.mbp_amount || 0).toFixed(2)})` : '—';

        tr.innerHTML = `
            <td class="fw-bold text-center">${row.row_number}</td>
            <td>
                <div><span class="badge bg-light text-dark border me-1">${row.seller_code || '—'}</span> <code class="fw-bold text-success fs-12">${row.product_code || '—'}</code></div>
                <div class="fw-semibold text-dark mt-1 fs-13">${row.product_name || '—'}</div>
            </td>
            <td>
                <div class="fs-13 fw-medium text-slate-800">${row.category_name || '—'}</div>
                <div class="fs-12 text-muted">${row.brand_name || '—'}</div>
            </td>
            <td>
                <div class="fs-13 font-monospace text-dark">${row.hsn_code || '—'}</div>
                <span class="badge bg-secondary-subtle text-secondary fs-11">${row.gst_rate}% GST</span>
            </td>
            <td>
                <div class="fs-12 text-muted">MRP: <strong class="text-dark">₹${parseFloat(row.mrp || 0).toFixed(2)}</strong></div>
                <div class="fs-12 text-success">Seller Price: <strong>₹${parseFloat(row.seller_price_incl_gst || 0).toFixed(2)}</strong></div>
                <div class="fs-11 text-secondary mt-1">Base Cost: ₹${parseFloat(row.base_cost || 0).toFixed(2)}</div>
            </td>
            <td>
                <div class="fs-12 mb-1"><span class="badge bg-primary-subtle text-primary">Admin: ${adminMarginStr}</span></div>
                <div class="fs-12 mb-1"><span class="badge bg-info-subtle text-info">DDC: ${ddcMarginStr}</span></div>
                <div class="fs-11 text-muted">DDC Cost: ₹${parseFloat(row.ddc_purchase_price || 0).toFixed(2)}</div>
            </td>
            <td>
                <div class="fs-12 mb-1"><span class="badge bg-warning-subtle text-dark">MBP: ${mbpMarginStr}</span></div>
                <div class="fs-11 text-muted">90%: ₹${parseFloat(row.mbp_90_share || 0).toFixed(2)} | 10%: ₹${parseFloat(row.mbp_10_share || 0).toFixed(2)}</div>
                <div class="fs-11 text-muted">38%: ₹${parseFloat(row.mbp_38_share || 0).toFixed(2)} | 62%: ₹${parseFloat(row.mbp_62_share || 0).toFixed(2)}</div>
            </td>
            <td>
                <div class="fs-11 text-muted">ADC Cost: ₹${parseFloat(row.adc_purchase_price || 0).toFixed(2)}</div>
                <div class="fs-12 fw-bold text-success mt-1">Avail Margin: ₹${parseFloat(row.available_margin || 0).toFixed(2)}</div>
                <div class="fs-12 fw-bold text-primary">ADC Final Margin: ₹${parseFloat(row.adc_final_margin || 0).toFixed(2)}</div>
            </td>
            <td>
                ${row.is_valid 
                    ? '<span class="badge bg-success-subtle text-success fw-bold px-2 py-1">Valid</span>' 
                    : '<span class="badge bg-danger-subtle text-danger fw-bold px-2 py-1">Error</span>'}
            </td>
            <td>${remarksHtml}</td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('previewCard').classList.remove('d-none');
    document.getElementById('btnProcessImport').disabled = (data.valid === 0);
}

document.getElementById('btnExportPreviewErrors')?.addEventListener('click', function() {
    window.exportImportPreviewToExcel('Product_Import_Errors.xlsx', parsedPreviewRecords, true);
});

document.getElementById('btnExportPreviewAll')?.addEventListener('click', function() {
    window.exportImportPreviewToExcel('Product_Import_All_Preview.xlsx', parsedPreviewRecords, false);
});

document.getElementById('btnProcessImport').addEventListener('click', function() {
    if (!parsedPreviewRecords || parsedPreviewRecords.length === 0) return;

    if (!confirm('Are you sure you want to proceed with importing valid products into the database?')) {
        return;
    }

    const btnProcess = this;
    btnProcess.disabled = true;

    const recordsToProcess = parsedPreviewRecords;
    const chunkSize = 250;
    const totalRecords = recordsToProcess.length;
    let offset = 0;

    let totalImported = 0;
    let totalSkipped = 0;
    let totalFailed = 0;
    let allDetails = [];

    function processNextChunk() {
        if (offset >= totalRecords) {
            btnProcess.disabled = false;
            btnProcess.innerHTML = `<iconify-icon icon="solar:check-read-bold-duotone" class="fs-18 align-middle me-1"></iconify-icon> Confirm & Import Valid Products`;

            renderReport({
                imported: totalImported,
                skipped: totalSkipped,
                failed: totalFailed,
                details: allDetails
            });
            return;
        }

        const chunk = recordsToProcess.slice(offset, offset + chunkSize);
        const currentEnd = Math.min(offset + chunkSize, totalRecords);
        btnProcess.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Importing (${currentEnd}/${totalRecords})...`;

        fetch("{{ route('admin.import.product.process') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ records: chunk })
        })
        .then(res => {
            if (!res.ok) {
                return res.json().then(errData => {
                    throw new Error(errData.message || `Server error (${res.status})`);
                }).catch(() => {
                    throw new Error(`Server error (${res.status})`);
                });
            }
            return res.json();
        })
        .then(data => {
            if (!data.success) {
                alert(data.message || 'Import failed at chunk.');
                btnProcess.disabled = false;
                btnProcess.innerHTML = `<iconify-icon icon="solar:check-read-bold-duotone" class="fs-18 align-middle me-1"></iconify-icon> Confirm & Import Valid Products`;
                return;
            }

            totalImported += (data.imported || 0);
            totalSkipped += (data.skipped || 0);
            totalFailed += (data.failed || 0);
            if (data.details && Array.isArray(data.details)) {
                allDetails = allDetails.concat(data.details);
            }

            offset += chunkSize;
            processNextChunk();
        })
        .catch(err => {
            btnProcess.disabled = false;
            btnProcess.innerHTML = `<iconify-icon icon="solar:check-read-bold-duotone" class="fs-18 align-middle me-1"></iconify-icon> Confirm & Import Valid Products`;
            alert('An error occurred during import: ' + err.message);
        });
    }

    processNextChunk();
});

let lastReportDetails = [];

function renderReport(data) {
    document.getElementById('reportImported').textContent = data.imported ?? 0;
    document.getElementById('reportSkipped').textContent = data.skipped ?? 0;
    document.getElementById('reportFailed').textContent = data.failed ?? 0;
    lastReportDetails = data.details || [];

    const tbody = document.getElementById('reportLogBody');
    tbody.innerHTML = '';

    (data.details || []).forEach(log => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${log.row}</td>
            <td><code>${log.identifier}</code></td>
            <td>${log.name}</td>
            <td>
                ${log.status === 'Imported' 
                    ? '<span class="badge bg-success-subtle text-success">Imported</span>' 
                    : '<span class="badge bg-warning-subtle text-warning">Skipped</span>'}
            </td>
            <td class="small">${log.message}</td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('reportCard').classList.remove('d-none');
    window.scrollTo({ top: document.getElementById('reportCard').offsetTop - 20, behavior: 'smooth' });
}

document.getElementById('btnExportReportErrors')?.addEventListener('click', function() {
    window.exportImportLogsToExcel('Product_Import_Skipped_Failed_Records.xlsx', lastReportDetails, true);
});

document.getElementById('btnExportReportAll')?.addEventListener('click', function() {
    window.exportImportLogsToExcel('Product_Import_All_Report_Logs.xlsx', lastReportDetails, false);
});

document.getElementById('btnClearImportData')?.addEventListener('click', function() {
    if (!confirm('Are you sure you want to clear/delete all imported data for this module? This action cannot be undone.')) return;
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Clearing...`;
    fetch("{{ route('admin.import.product.clear') }}", {
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
