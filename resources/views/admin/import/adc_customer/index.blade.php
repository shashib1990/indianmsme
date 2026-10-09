@extends('layouts.admin')

@section('title', 'ADC Customer Bulk Import')
@section('page-title', 'ADC Customer Bulk Import')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <!-- Header Card -->
            <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px; background: linear-gradient(135deg, #059669 0%, #065f46 100%);">
                <div class="card-body p-4 text-white">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                        <div>
                            <h3 class="fw-bold text-white mb-1">
                                <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" class="align-middle me-2"></iconify-icon>
                                Bulk ADC Customer Import
                            </h3>
                            <p class="mb-0 text-white-50 fs-14">
                                Import customers into the <strong>adc_pos_customers</strong> table in bulk via Excel/CSV.
                                Identify ADC by <strong>ADC Code</strong>. Existing customers (same mobile + ADC) will be updated.
                            </p>
                        </div>
                        <div class="d-flex align-items-center gap-2 flex-wrap">
                            <a href="{{ route('admin.import.adc_customer.sample') }}" class="btn btn-light fw-bold text-success shadow-sm" style="border-radius: 8px;">
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
            <div class="alert border-0 d-flex align-items-start gap-3 mb-4" style="border-radius: 10px; background: #d1fae5;">
                <iconify-icon icon="solar:info-circle-bold-duotone" class="fs-22 mt-1 flex-shrink-0" style="color: #065f46;"></iconify-icon>
                <div class="fs-13" style="color: #064e3b;">
                    <strong>How it works:</strong>
                    Each row represents one ADC POS customer. The <strong>ADC Code</strong> uniquely identifies which ADC this customer belongs to.
                    <ul class="mb-0 mt-1 ps-3">
                        <li><strong>ADC Code*</strong> — ADC's code (e.g. ADC001). Must exist in the system.</li>
                        <li><strong>Customer Name*</strong> — Full name of the customer.</li>
                        <li><strong>Mobile*</strong> — 10-digit Indian mobile number (starts with 6–9).</li>
                        <li><strong>Email</strong> — Optional. Must be a valid email format.</li>
                        <li><strong>Address</strong> — Optional. Customer's address.</li>
                        <li><strong>Pincode</strong> — Optional. 6-digit pincode.</li>
                        <li><strong>GST Number</strong> — Optional. Must match standard GST format.</li>
                        <li><strong>Abhhyam Code</strong> — Optional. Abhhyam membership code.</li>
                        <li>If a customer with the <strong>same mobile already exists under the same ADC</strong>, their record will be <strong>updated</strong> (not duplicated).</li>
                        <li>Credit fields (<code>total_credit_given</code>, <code>outstanding_balance</code>) are initialized to <strong>0</strong> for new customers.</li>
                        <li>Abhhyam API registration is <strong>skipped</strong> during bulk import to avoid API rate limits.</li>
                    </ul>
                </div>
            </div>

            <!-- Upload Card -->
            <div class="card mb-4 border-0 shadow-sm" style="border-radius: 12px;">
                <div class="card-header bg-transparent border-bottom p-3">
                    <h5 class="card-title fw-bold text-slate-800 mb-0">Step 1: Upload Customer Excel File</h5>
                </div>
                <div class="card-body p-4">
                    <form id="adcCustomerUploadForm" enctype="multipart/form-data">
                        @csrf
                        <div class="row align-items-center">
                            <div class="col-md-9 mb-3 mb-md-0">
                                <label for="excel_file" class="form-label fw-semibold text-slate-700">Select Excel File (.xlsx, .csv, .xls)</label>
                                <input type="file" class="form-control form-control-lg" id="excel_file" name="file" accept=".xlsx,.xls,.csv" required>
                                <div class="form-text text-muted fs-12 mt-1">
                                    Mandatory columns: <strong>A: ADC Code*</strong>, <strong>B: Customer Name*</strong>, <strong>C: Mobile*</strong>.
                                    Optional: D: Email, E: Address, F: Pincode, G: GST Number, H: Abhhyam Code.
                                </div>
                            </div>
                            <div class="col-md-3 text-md-end">
                                <button type="submit" class="btn btn-success btn-lg w-100 fw-bold" id="btnPreview" style="border-radius: 8px; background: #059669; border-color: #059669;">
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
                    --bs-table-accent-bg: #fee2e2 !important;
                    color: #991b1b !important;
                }
            </style>

            <!-- Preview Card (Hidden by default) -->
            <div class="card mb-4 border-0 shadow-sm d-none" id="previewCard" style="border-radius: 12px;">
                <div class="card-header bg-transparent border-bottom p-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div>
                        <h5 class="card-title fw-bold text-slate-800 mb-0">Step 2: Dry-Run Verification Preview</h5>
                        <p class="text-muted fs-12 mb-0">Review resolved ADC Codes, customer details, and validation errors before importing.</p>
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
                        <button type="button" class="btn btn-success fw-bold ms-1 px-4 text-white" id="btnProcessImport" style="border-radius: 8px;">
                            <iconify-icon icon="solar:check-read-bold-duotone" class="fs-18 align-middle me-1"></iconify-icon>
                            Confirm &amp; Import Valid Customers
                        </button>
                    </div>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive" style="max-height: 600px;">
                        <table class="table table-hover align-middle mb-0" id="previewTable">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th style="width: 50px;">Row</th>
                                    <th>ADC</th>
                                    <th>Customer Name</th>
                                    <th>Mobile</th>
                                    <th>Email</th>
                                    <th>Pincode</th>
                                    <th>GST</th>
                                    <th style="width: 80px;">Validity</th>
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
                                <span class="fw-semibold text-warning fs-13">Updated / Skipped</span>
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
                                    <th>Mobile</th>
                                    <th>Customer Name</th>
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

document.getElementById('adcCustomerUploadForm').addEventListener('submit', function(e) {
    e.preventDefault();
    const formData = new FormData(this);

    const btnPreview = document.getElementById('btnPreview');
    btnPreview.disabled = true;
    btnPreview.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Validating...`;

    document.getElementById('previewCard').classList.add('d-none');
    document.getElementById('reportCard').classList.add('d-none');

    fetch("{{ route('admin.import.adc_customer.preview') }}", {
        method: "POST",
        headers: { "X-CSRF-TOKEN": "{{ csrf_token() }}" },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        btnPreview.disabled = false;
        btnPreview.innerHTML = `<iconify-icon icon="solar:magnifer-linear" class="fs-18 align-middle me-1"></iconify-icon> Upload &amp; Preview`;

        if (!data.success) {
            alert(data.message || 'Error processing Excel file');
            return;
        }

        parsedPreviewRecords = data.records;
        renderPreview(data);
    })
    .catch(() => {
        btnPreview.disabled = false;
        btnPreview.innerHTML = `<iconify-icon icon="solar:magnifer-linear" class="fs-18 align-middle me-1"></iconify-icon> Upload &amp; Preview`;
        alert('An unexpected server error occurred.');
    });
});

function renderPreview(data) {
    document.getElementById('badgeTotal').textContent = `Total: ${data.total.toLocaleString()}`;
    document.getElementById('badgeValid').textContent = `Valid: ${data.valid.toLocaleString()}`;
    document.getElementById('badgeErrors').textContent = `Errors: ${data.errors.toLocaleString()}`;

    const tbody = document.getElementById('previewTableBody');
    tbody.innerHTML = '';

    const displayRecords = (data.records || []).slice(0, 1000);

    displayRecords.forEach(row => {
        const tr = document.createElement('tr');
        if (!row.is_valid) {
            tr.classList.add('table-danger');
            tr.style.backgroundColor = '#fee2e2';
        }

        const remarksHtml = row.is_valid
            ? `<span class="text-success small fw-semibold"><i class="bx bx-check-circle me-1"></i>Ready for import</span>`
            : `<ul class="mb-0 text-danger ps-3 small fs-12">${row.errors.map(err => `<li>${err}</li>`).join('')}</ul>`;

        tr.innerHTML = `
            <td class="fw-bold text-center">${row.row_number}</td>
            <td>
                <div><span class="badge fw-bold font-monospace" style="background:#d1fae5;color:#065f46;">${row.adc_code || '—'}</span></div>
                <div class="fs-12 text-muted mt-1">${row.adc_name || '—'}</div>
            </td>
            <td class="fw-semibold text-slate-800">${row.name || '—'}</td>
            <td><code>${row.mobile || '—'}</code></td>
            <td class="fs-12 text-muted">${row.email || '—'}</td>
            <td class="fs-12">${row.pincode || '—'}</td>
            <td class="fs-12 font-monospace">${row.gst_number || '—'}</td>
            <td>
                ${row.is_valid
                    ? '<span class="badge bg-success-subtle text-success fw-bold px-2 py-1">Valid</span>'
                    : '<span class="badge bg-danger-subtle text-danger fw-bold px-2 py-1">Error</span>'}
            </td>
            <td>${remarksHtml}</td>
        `;
        tbody.appendChild(tr);
    });

    if (data.records.length > 1000) {
        const tr = document.createElement('tr');
        tr.innerHTML = `<td colspan="9" class="text-center text-muted p-3 bg-light fw-bold fs-13">
            <iconify-icon icon="solar:info-circle-bold" class="align-middle me-1 text-success fs-16"></iconify-icon>
            Showing first 1,000 rows out of ${data.records.length.toLocaleString()} total rows in preview table. All ${data.valid.toLocaleString()} valid rows will be imported during execution.
        </td>`;
        tbody.appendChild(tr);
    }

    document.getElementById('previewCard').classList.remove('d-none');
    document.getElementById('btnProcessImport').disabled = (data.valid === 0);
}

document.getElementById('btnExportPreviewErrors')?.addEventListener('click', function() {
    window.exportImportPreviewToExcel('ADC_Customer_Import_Errors.xlsx', parsedPreviewRecords, true);
});

document.getElementById('btnExportPreviewAll')?.addEventListener('click', function() {
    window.exportImportPreviewToExcel('ADC_Customer_Import_All_Preview.xlsx', parsedPreviewRecords, false);
});

document.getElementById('btnProcessImport').addEventListener('click', async function() {
    if (!parsedPreviewRecords || parsedPreviewRecords.length === 0) return;
    if (!confirm('Are you sure you want to import valid ADC customers into the database?')) return;

    const btnProcess = this;
    btnProcess.disabled = true;

    const chunkSize = 500;
    const totalRecords = parsedPreviewRecords.length;
    let imported = 0, skipped = 0, failed = 0;
    let allDetails = [];

    for (let i = 0; i < totalRecords; i += chunkSize) {
        const chunk = parsedPreviewRecords.slice(i, i + chunkSize);
        const chunkNum = Math.floor(i / chunkSize) + 1;
        const totalChunks = Math.ceil(totalRecords / chunkSize);

        btnProcess.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Processing batch ${chunkNum}/${totalChunks}...`;

        try {
            const res = await fetch("{{ route('admin.import.adc_customer.process') }}", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Accept": "application/json"
                },
                body: JSON.stringify({ records: chunk })
            });

            const data = await res.json().catch(() => null);
            if (!res.ok || !data || !data.success) {
                alert(data?.message || `Error processing batch ${chunkNum}`);
                btnProcess.disabled = false;
                btnProcess.innerHTML = `<iconify-icon icon="solar:check-read-bold-duotone" class="fs-18 align-middle me-1"></iconify-icon> Confirm &amp; Import Valid Customers`;
                return;
            }

            imported += (data.imported || 0);
            skipped  += (data.skipped || 0);
            failed   += (data.failed || 0);
            if (Array.isArray(data.details)) {
                allDetails = allDetails.concat(data.details);
            }
        } catch (err) {
            console.error('Batch import error:', err);
            alert(`Network error during batch ${chunkNum}: ` + err.message);
            btnProcess.disabled = false;
            btnProcess.innerHTML = `<iconify-icon icon="solar:check-read-bold-duotone" class="fs-18 align-middle me-1"></iconify-icon> Confirm &amp; Import Valid Customers`;
            return;
        }
    }

    btnProcess.disabled = false;
    btnProcess.innerHTML = `<iconify-icon icon="solar:check-read-bold-duotone" class="fs-18 align-middle me-1"></iconify-icon> Confirm &amp; Import Valid Customers`;

    renderReport({
        imported: imported,
        skipped: skipped,
        failed: failed,
        details: allDetails
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
            <td>${log.status === 'Imported'
                ? '<span class="badge bg-success-subtle text-success">Imported</span>'
                : '<span class="badge bg-warning-subtle text-warning">Skipped/Updated</span>'}</td>
            <td class="small">${log.message}</td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('reportCard').classList.remove('d-none');
    window.scrollTo({ top: document.getElementById('reportCard').offsetTop - 20, behavior: 'smooth' });
}

document.getElementById('btnExportReportErrors')?.addEventListener('click', function() {
    window.exportImportLogsToExcel('ADC_Customer_Import_Skipped_Failed_Records.xlsx', lastReportDetails, true);
});

document.getElementById('btnExportReportAll')?.addEventListener('click', function() {
    window.exportImportLogsToExcel('ADC_Customer_Import_All_Report_Logs.xlsx', lastReportDetails, false);
});

document.getElementById('btnClearImportData')?.addEventListener('click', function() {
    if (!confirm('Are you sure you want to clear/delete all imported data for this module? This action cannot be undone.')) return;
    const btn = this;
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-1"></span> Clearing...`;
    fetch("{{ route('admin.import.adc_customer.clear') }}", {
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
