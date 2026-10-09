@extends('layouts.admin')

@section('title', 'Sub Seller Bulk Import')
@section('page-title', 'Sub Seller Bulk Import')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">

            <!-- Header Card -->
            <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #7c3aed 0%, #5b21b6 100%);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <h3 class="fw-bold text-white mb-1">
                                <iconify-icon icon="solar:users-group-rounded-bold-duotone" class="align-middle me-2"></iconify-icon>
                                Bulk Sub Seller Import
                            </h3>
                            <p class="mb-0 text-white-50 fs-14">
                                Migrate existing Sub Seller accounts from old software. Each sub seller must be linked to an existing Parent Seller via <code class="text-warning">Parent Seller ID</code>.
                            </p>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <a href="{{ route('admin.import.sub_seller.sample') }}" class="btn btn-light fw-bold text-purple shadow-sm" style="border-radius: 8px; color: #7c3aed !important;">
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

            <!-- Info Banner -->
            <div class="alert border-0 d-flex align-items-start gap-3 mb-4" style="border-radius: 10px; background: #f3e8ff;">
                <iconify-icon icon="solar:info-circle-bold-duotone" class="fs-22 mt-1 flex-shrink-0" style="color: #7c3aed;"></iconify-icon>
                <div class="fs-13" style="color: #5b21b6;">
                    <strong>Column Reference (27 columns):</strong>
                    <div class="row mt-2">
                        <div class="col-md-6">
                            <ul class="mb-0 ps-3">
                                <li><strong>A: Seller ID (Old System)*</strong> — The seller's ID from old software (e.g. IMV2001). Preserved as-is in the new system.</li>
                                <li><strong>B: Parent Seller ID*</strong> — Must match existing Seller ID (e.g. IMV1001)</li>
                                <li><strong>C: Name*, D: Email*, E: Mobile*</strong></li>
                                <li><strong>F: Company Name*, L: State*, M: District*, N: Pincode*</strong></li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <ul class="mb-0 ps-3">
                                <li><strong>G: Company Type, H: GST, I: Auth Rep, J-K: Alt Contact</strong></li>
                                <li><strong>O: Address, P: Aadhaar, Q: PAN</strong></li>
                                <li><strong>R–V: Bank Details, W: Wallet Balance</strong></li>
                                <li><strong>X: KYC Status, Y: Allow Own Products, Z: Status, AA: Password</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Upload Card -->
            <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-transparent border-bottom p-3">
                    <h5 class="card-title fw-bold text-slate-800 mb-0">Step 1: Upload Sub Seller Excel File</h5>
                </div>
                <div class="card-body p-4">
                    <form id="subSellerUploadForm" enctype="multipart/form-data">
                        @csrf
                        <div class="row align-items-center">
                            <div class="col-md-9 mb-3 mb-md-0">
                                <label for="excel_file" class="form-label fw-semibold text-slate-700">Select Excel File (.xlsx, .csv, .xls)</label>
                                <input type="file" class="form-control form-control-lg" id="excel_file" name="file" accept=".xlsx,.xls,.csv" required>
                                <div class="form-text text-muted fs-12 mt-1">
                                    Mandatory: <strong>A: Seller ID (Old System), B: Parent Seller ID, C: Name, D: Email, E: Mobile, F: Company Name, L: State, M: District, N: Pincode</strong>
                                </div>
                            </div>
                            <div class="col-md-3 text-md-end">
                                <button type="submit" class="btn btn-lg w-100 fw-bold" id="btnPreview" style="border-radius: 8px; background: #7c3aed; color: #fff;">
                                    <iconify-icon icon="solar:magnifer-linear" class="fs-18 align-middle me-1"></iconify-icon>
                                    Upload &amp; Preview
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
                    color: #991b1b !important;
                }
            </style>

            <!-- Preview Card -->
            <div class="card mb-4 border-0 shadow-sm d-none" id="previewCard" style="border-radius: 12px;">
                <div class="card-header bg-transparent border-bottom p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h5 class="card-title fw-bold text-slate-800 mb-0">Step 2: Dry-Run Verification Preview</h5>
                        <p class="text-muted fs-12 mb-0">Review all Sub Sellers, parent resolution, and validation errors before importing.</p>
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
                        <button type="button" class="btn fw-bold ms-1 px-4" id="btnProcessImport" style="border-radius: 8px; background: #7c3aed; color: #fff;">
                            <iconify-icon icon="solar:check-read-bold-duotone" class="fs-18 align-middle me-1"></iconify-icon>
                            Confirm &amp; Import Valid Records
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 600px; overflow-y: auto;">
                        <table class="table table-hover align-middle mb-0 fs-13" id="previewTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 50px;">Row</th>
                                    <th>Seller ID (Old System)</th>
                                    <th>Parent Seller</th>
                                    <th>Name &amp; Contact</th>
                                    <th>Company</th>
                                    <th>Location</th>
                                    <th style="width: 80px;">KYC</th>
                                    <th style="width: 75px;">Status</th>
                                    <th style="width: 75px;">Valid?</th>
                                    <th>Errors / Remarks</th>
                                </tr>
                            </thead>
                            <tbody id="previewTableBody">
                            </tbody>
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
                                <span class="fw-semibold text-success fs-13">Successfully Imported</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="p-3 bg-warning-subtle rounded-3">
                                <h2 class="fw-bold text-warning mb-0" id="reportSkipped">0</h2>
                                <span class="fw-semibold text-warning fs-13">Skipped</span>
                            </div>
                        </div>
                        <div class="col-md-4 mb-2">
                            <div class="p-3 bg-danger-subtle rounded-3">
                                <h2 class="fw-bold text-danger mb-0" id="reportFailed">0</h2>
                                <span class="fw-semibold text-danger fs-13">Failed</span>
                            </div>
                        </div>
                    </div>
                    <h6 class="fw-bold text-slate-800 mb-2">Detailed Log</h6>
                    <div class="table-responsive" style="max-height: 400px;">
                        <table class="table table-sm table-bordered mb-0 fs-13">
                            <thead class="table-light">
                                <tr>
                                    <th style="width: 60px;">Row</th>
                                    <th>Seller ID (Old System)</th>
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

document.getElementById('subSellerUploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnPreview');
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Validating...`;
    document.getElementById('previewCard').classList.add('d-none');
    document.getElementById('reportCard').classList.add('d-none');

    fetch("{{ route('admin.import.sub_seller.preview') }}", {
        method: "POST",
        headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
        body: new FormData(this)
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

function renderPreview(data) {
    document.getElementById('badgeTotal').textContent = `Total: ${data.total}`;
    document.getElementById('badgeValid').textContent = `Valid: ${data.valid}`;
    document.getElementById('badgeErrors').textContent = `Errors: ${data.errors}`;
    document.getElementById('btnProcessImport').disabled = (data.valid === 0);

    const tbody = document.getElementById('previewTableBody');
    tbody.innerHTML = '';

    data.records.forEach(row => {
        const tr = document.createElement('tr');
        if (!row.is_valid) { tr.classList.add('table-danger'); tr.style.backgroundColor = '#fee2e2'; }

        const remarksHtml = row.is_valid
            ? `<span class="text-success small fw-semibold"><i class="bx bx-check-circle me-1"></i>Ready</span>`
            : `<ul class="mb-0 text-danger ps-3 small">${row.errors.map(e => `<li>${e}</li>`).join('')}</ul>`;

        tr.innerHTML = `
            <td class="fw-bold text-center">${row.row_number}</td>
            <td><span class="badge fw-bold font-monospace" style="background:#7c3aed;">${row.seller_id_old || '—'}</span></td>
            <td>
                <div><span class="badge bg-primary-subtle text-primary fw-bold font-monospace">${row.parent_seller_id || '—'}</span></div>
                <div class="text-muted fs-12 mt-1">${row.parent_name || '—'}</div>
            </td>
            <td>
                <div class="fw-semibold">${row.name || '—'}</div>
                <div class="text-muted fs-12">${row.email || ''}</div>
                <div class="text-muted fs-12">${row.mobile || ''}</div>
            </td>
            <td>
                <div class="fs-13">${row.company_name || '—'}</div>
                ${row.company_type ? `<div class="text-muted fs-12">${row.company_type}</div>` : ''}
            </td>
            <td>
                <div class="fs-13 fw-semibold">${row.district || '—'}</div>
                <div class="text-muted fs-12">${row.state || '—'}</div>
                <div class="text-muted fs-12">${row.pincode || ''}</div>
            </td>
            <td><span class="badge ${row.kyc_status === 'APPROVED' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning'} fs-11">${row.kyc_status || '—'}</span></td>
            <td>${row.status === '1' ? '<span class="badge bg-success-subtle text-success">Active</span>' : '<span class="badge bg-secondary-subtle text-secondary">Inactive</span>'}</td>
            <td>${row.is_valid ? '<span class="badge bg-success-subtle text-success fw-bold">Valid</span>' : '<span class="badge bg-danger-subtle text-danger fw-bold">Error</span>'}</td>
            <td>${remarksHtml}</td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('previewCard').classList.remove('d-none');
}

document.getElementById('btnExportPreviewErrors')?.addEventListener('click', function() {
    window.exportImportPreviewToExcel('SubSeller_Import_Errors.xlsx', parsedPreviewRecords, true);
});

document.getElementById('btnExportPreviewAll')?.addEventListener('click', function() {
    window.exportImportPreviewToExcel('SubSeller_Import_All_Preview.xlsx', parsedPreviewRecords, false);
});

document.getElementById('btnProcessImport').addEventListener('click', function() {
    if (!parsedPreviewRecords.length) return;
    if (!confirm('Confirm import of all valid Sub Seller records into database?')) return;

    const btn = this;
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Importing...`;

    fetch("{{ route('admin.import.sub_seller.process') }}", {
        method: "POST",
        headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
        body: JSON.stringify({ records: parsedPreviewRecords })
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = `<iconify-icon icon="solar:check-read-bold-duotone" class="fs-18 align-middle me-1"></iconify-icon> Confirm &amp; Import Valid Records`;
        if (!data.success) { alert(data.message || 'Import failed'); return; }
        renderReport(data);
    })
    .catch(() => {
        btn.disabled = false;
        btn.innerHTML = `<iconify-icon icon="solar:check-read-bold-duotone" class="fs-18 align-middle me-1"></iconify-icon> Confirm &amp; Import Valid Records`;
        alert('Server error during import.');
    });
});

let lastReportDetails = [];

function renderReport(data) {
    document.getElementById('reportImported').textContent = data.imported ?? 0;
    document.getElementById('reportSkipped').textContent  = data.skipped ?? 0;
    document.getElementById('reportFailed').textContent   = data.failed ?? 0;
    lastReportDetails = data.details || [];
    const tbody = document.getElementById('reportLogBody');
    tbody.innerHTML = '';
    (data.details || []).forEach(log => {
        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${log.row}</td>
            <td><code>${log.identifier}</code></td>
            <td>${log.name}</td>
            <td>${log.status === 'Imported' ? '<span class="badge bg-success-subtle text-success">Imported</span>' : '<span class="badge bg-warning-subtle text-warning">Skipped</span>'}</td>
            <td class="small">${log.message}</td>
        `;
        tbody.appendChild(tr);
    });
    document.getElementById('reportCard').classList.remove('d-none');
    window.scrollTo({ top: document.getElementById('reportCard').offsetTop - 20, behavior: 'smooth' });
}

document.getElementById('btnExportReportErrors')?.addEventListener('click', function() {
    window.exportImportLogsToExcel('SubSeller_Import_Skipped_Failed_Records.xlsx', lastReportDetails, true);
});

document.getElementById('btnExportReportAll')?.addEventListener('click', function() {
    window.exportImportLogsToExcel('SubSeller_Import_All_Report_Logs.xlsx', lastReportDetails, false);
});

document.getElementById('btnClearImportData')?.addEventListener('click', function() {
    if (!confirm('Are you sure you want to clear/delete all imported data for this module? This action cannot be undone.')) return;
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Clearing...`;
    fetch("{{ route('admin.import.sub_seller.clear') }}", {
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
