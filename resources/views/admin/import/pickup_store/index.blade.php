@extends('layouts.admin')

@section('title', 'Pickup Store Bulk Import')
@section('page-title', 'Pickup Store Bulk Import')

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
                                <iconify-icon icon="solar:shop-bold-duotone" class="align-middle me-2"></iconify-icon>
                                Bulk Pickup Store Import
                            </h3>
                            <p class="mb-0 text-white-50 fs-14">
                                Bulk import Pickup Store outlets for Sellers using Seller Code. Specify Seller Code, Store Name, Contact, Address, State, District, and Banking details.
                            </p>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <a href="{{ route('admin.import.pickup_store.sample') }}" class="btn btn-light fw-bold text-success shadow-sm" style="border-radius: 8px;">
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
                    <h5 class="card-title fw-bold text-slate-800 mb-0">Step 1: Upload Pickup Store Excel File</h5>
                </div>
                <div class="card-body p-4">
                    <form id="pickupStoreUploadForm" action="{{ route('admin.import.pickup_store.preview') }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="row align-items-center">
                            <div class="col-md-9 mb-3 mb-md-0">
                                <label for="excel_file" class="form-label fw-semibold text-slate-700">Select Excel File (.xlsx, .csv, .xls)</label>
                                <input type="file" class="form-control form-control-lg" id="excel_file" name="file" accept=".xlsx,.xls,.csv" required>
                                <div class="form-text text-muted fs-12 mt-1">
                                    Columns with * (Seller Code*, PSC Code*, Pickup Store Name*, Contact Person*, Mobile*, Email*, GST Number*, Address*, State*, District*, Pincode*) are mandatory.
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
                        <p class="text-muted fs-12 mb-0">Review valid pickup store records, resolved seller codes, and validation error logs before importing.</p>
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
                            Confirm &amp; Import Valid Stores
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 600px;">
                        <table class="table table-hover align-middle mb-0" id="previewTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 50px;">Row</th>
                                    <th>Seller Code & Name</th>
                                    <th>Store Name & Contact</th>
                                    <th>Mobile & Email</th>
                                    <th>Location & Address</th>
                                    <th>Bank & Dispatch Details</th>
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
                                    <th>Store Code</th>
                                    <th>Store Name</th>
                                    <th>Status</th>
                                    <th>Details</th>
                                </tr>
                            </thead>
                            <tbody id="reportLogBody">
                                <!-- Dynamic JS content -->
                            </tbody>
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

document.getElementById('pickupStoreUploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    const btnPreview = document.getElementById('btnPreview');
    btnPreview.disabled = true;
    btnPreview.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Validating...`;

    document.getElementById('previewCard').classList.add('d-none');
    document.getElementById('reportCard').classList.add('d-none');

    fetch("{{ route('admin.import.pickup_store.preview') }}", {
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

        const bankInfo = row.can_receive_payment_and_dispatch 
            ? `<div class="fs-12 text-slate-800 fw-semibold">${row.bank_name || '—'} (${row.ifsc_code || ''})</div>
               <div class="fs-11 text-muted">A/C: ${row.bank_account_number || ''}</div>
               <div class="fs-11 text-success font-monospace mt-1"><i class="bx bx-check"></i> Dispatch & Payment Enabled</div>`
            : `<div class="fs-12 text-secondary"><i class="bx bx-x"></i> Dispatch & Payment Disabled</div>`;

        tr.innerHTML = `
            <td class="fw-bold text-center">${row.row_number}</td>
            <td>
                <div><span class="badge bg-primary-subtle text-primary fw-bold">${row.seller_code || '—'}</span></div>
                <div class="fw-semibold text-dark mt-1 fs-13">${row.seller_name || '—'}</div>
                <span class="badge bg-light text-dark border fs-11 mt-1">${row.owner_type || 'Seller'}</span>
            </td>
            <td>
                <div class="fs-13 fw-bold text-slate-800">
                    <span class="badge bg-info-subtle text-info fw-bold me-1">${row.pickup_store_code || '—'}</span>
                    ${row.pickup_store_name || '—'}
                </div>
                <div class="fs-12 text-muted"><i class="bx bx-user me-1"></i>${row.contact_person || '—'}</div>
                <div class="fs-11 text-secondary font-monospace mt-0.5">GST: ${row.gst_number || '—'}</div>
            </td>
            <td>
                <div class="fs-12 fw-mono text-dark"><i class="bx bx-phone me-1"></i>${row.mobile || '—'}</div>
                <div class="fs-12 text-muted"><i class="bx bx-envelope me-1"></i>${row.email || '—'}</div>
            </td>
            <td>
                <div class="fs-12 fw-semibold text-slate-800">${row.district_name || '—'}, ${row.state_name || '—'}</div>
                <div class="fs-11 text-muted">${row.address || '—'}</div>
                <div class="fs-11 text-secondary font-monospace">PIN: ${row.pincode || '—'}</div>
            </td>
            <td>${bankInfo}</td>
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
    window.exportImportPreviewToExcel('PickupStore_Import_Errors.xlsx', parsedPreviewRecords, true);
});

document.getElementById('btnExportPreviewAll')?.addEventListener('click', function() {
    window.exportImportPreviewToExcel('PickupStore_Import_All_Preview.xlsx', parsedPreviewRecords, false);
});

document.getElementById('btnProcessImport').addEventListener('click', function() {
    if (!parsedPreviewRecords || parsedPreviewRecords.length === 0) return;

    if (!confirm('Are you sure you want to proceed with importing valid pickup stores into the database?')) {
        return;
    }

    const btnProcess = this;
    btnProcess.disabled = true;
    btnProcess.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Importing...`;

    fetch("{{ route('admin.import.pickup_store.process') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}"
        },
        body: JSON.stringify({ records: parsedPreviewRecords })
    })
    .then(res => res.json())
    .then(data => {
        btnProcess.disabled = false;
        btnProcess.innerHTML = `<iconify-icon icon="solar:check-read-bold-duotone" class="fs-18 align-middle me-1"></iconify-icon> Confirm & Import Valid Stores`;

        if (!data.success) {
            alert(data.message || 'Import failed');
            return;
        }

        renderReport(data);
    })
    .catch(err => {
        btnProcess.disabled = false;
        btnProcess.innerHTML = `<iconify-icon icon="solar:check-read-bold-duotone" class="fs-18 align-middle me-1"></iconify-icon> Confirm & Import Valid Stores`;
        alert('An error occurred during final import.');
    });
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
    window.exportImportLogsToExcel('PickupStore_Import_Skipped_Failed_Records.xlsx', lastReportDetails, true);
});

document.getElementById('btnExportReportAll')?.addEventListener('click', function() {
    window.exportImportLogsToExcel('PickupStore_Import_All_Report_Logs.xlsx', lastReportDetails, false);
});

document.getElementById('btnClearImportData')?.addEventListener('click', function() {
    if (!confirm('Are you sure you want to clear/delete all imported data for this module? This action cannot be undone.')) return;
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Clearing...`;
    fetch("{{ route('admin.import.pickup_store.clear') }}", {
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
