@extends('layouts.adc')

@section('title', 'GST Report (Outward Supplies)')
@section('page-title', 'GST Report')

@section('content')
    @php
        $startDate = $filters['start_date'] ?? '';
        $endDate   = $filters['end_date'] ?? '';
        $taxRate   = $filters['tax_rate'] ?? 'all';
        $taxType   = $filters['tax_type'] ?? 'all';
        $search    = $filters['search'] ?? '';
    @endphp

    <!-- Header Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                <iconify-icon icon="solar:document-text-bold-duotone" class="text-primary align-middle me-1 fs-24"></iconify-icon>
                                GST Report (Outward Supplies)
                            </h4>
                            <p class="text-muted small mb-0">Detailed GST sales report with HSN code, tax rate %, taxable values, and SGST / CGST / IGST breakdown.</p>
                        </div>
                        <div class="col-md-6 text-md-end d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
                            <!-- <button type="button"
                                class="btn btn-primary btn-sm fw-semibold d-inline-flex align-items-center gap-1 shadow-sm px-3"
                                data-bs-toggle="modal" data-bs-target="#sendGstReportModal">
                                <iconify-icon icon="solar:plain-bold-duotone" class="fs-18"></iconify-icon>
                                Send
                            </button> -->
                            <a href="{{ route('adc.reports.gst.export-csv', request()->query()) }}"
                                class="btn btn-success btn-sm fw-semibold d-inline-flex align-items-center gap-1 shadow-sm px-3">
                                <iconify-icon icon="solar:file-download-bold-duotone" class="fs-18"></iconify-icon>
                                Export CSV (Excel)
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary KPI Stat Cards -->
    <div class="row g-3 mb-3">
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted fs-11 text-uppercase fw-semibold d-block mb-1">Total Entries</span>
                    <h5 class="fw-bold text-slate-800 mb-0">{{ number_format($summaryStats['total_rows']) }}</h5>
                    <small class="text-muted fs-11">Filtered Lines</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body p-3">
                    <span class="text-muted fs-11 text-uppercase fw-semibold d-block mb-1">Taxable Amount</span>
                    <h5 class="fw-bold text-dark mb-0">₹{{ number_format($summaryStats['total_taxable'], 2) }}</h5>
                    <small class="text-muted fs-11">Total Taxable Value</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-info">
                <div class="card-body p-3">
                    <span class="text-muted fs-11 text-uppercase fw-semibold d-block mb-1">Total SGST</span>
                    <h5 class="fw-bold text-info mb-0">₹{{ number_format($summaryStats['total_sgst'], 2) }}</h5>
                    <small class="text-muted fs-11">State GST</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-primary">
                <div class="card-body p-3">
                    <span class="text-muted fs-11 text-uppercase fw-semibold d-block mb-1">Total CGST</span>
                    <h5 class="fw-bold text-primary mb-0">₹{{ number_format($summaryStats['total_cgst'], 2) }}</h5>
                    <small class="text-muted fs-11">Central GST</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 border-start border-4 border-warning">
                <div class="card-body p-3">
                    <span class="text-muted fs-11 text-uppercase fw-semibold d-block mb-1">Total IGST</span>
                    <h5 class="fw-bold text-warning-emphasis mb-0">₹{{ number_format($summaryStats['total_igst'], 2) }}</h5>
                    <small class="text-muted fs-11">Integrated GST</small>
                </div>
            </div>
        </div>
        <div class="col-6 col-md-4 col-xl-2">
            <div class="card border-0 shadow-sm h-100 bg-success-subtle border-start border-4 border-success">
                <div class="card-body p-3">
                    <span class="text-success-emphasis fs-11 text-uppercase fw-bold d-block mb-1">Total Amount</span>
                    <h5 class="fw-bold text-success mb-0">₹{{ number_format($summaryStats['total_amount'], 2) }}</h5>
                    <small class="text-muted fs-11">Invoice Total</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('adc.reports.gst') }}" class="row g-2 align-items-end">
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">Start Date</label>
                    <input type="date" name="start_date" class="form-control form-control-sm" value="{{ $startDate }}" style="height: 34px;">
                </div>
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">End Date</label>
                    <input type="date" name="end_date" class="form-control form-control-sm" value="{{ $endDate }}" style="height: 34px;">
                </div>
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">Tax Rate (%)</label>
                    <select name="tax_rate" class="form-select form-select-sm" style="height: 34px;">
                        <option value="all" {{ $taxRate === 'all' ? 'selected' : '' }}>All Tax Rates</option>
                        <option value="0" {{ $taxRate === '0' ? 'selected' : '' }}>0% (Exempt / Nil)</option>
                        <option value="5" {{ $taxRate === '5' ? 'selected' : '' }}>5% GST</option>
                        <option value="12" {{ $taxRate === '12' ? 'selected' : '' }}>12% GST</option>
                        <option value="18" {{ $taxRate === '18' ? 'selected' : '' }}>18% GST</option>
                        <option value="28" {{ $taxRate === '28' ? 'selected' : '' }}>28% GST</option>
                    </select>
                </div>
                <div class="col-xl-2 col-md-4 col-sm-6">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">Supply / Tax Type</label>
                    <select name="tax_type" class="form-select form-select-sm" style="height: 34px;">
                        <option value="all" {{ $taxType === 'all' ? 'selected' : '' }}>All Supplies</option>
                        <option value="intra" {{ $taxType === 'intra' ? 'selected' : '' }}>Intra-State (CGST + SGST)</option>
                        <option value="inter" {{ $taxType === 'inter' ? 'selected' : '' }}>Inter-State (IGST)</option>
                    </select>
                </div>
                <div class="col-xl-2 col-md-4 col-sm-8">
                    <label class="form-label fs-12 fw-semibold text-muted mb-1">Search Keywords</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="Invoice / HSN / Customer" value="{{ $search }}" style="height: 34px;">
                </div>
                <div class="col-xl-2 col-md-4 col-sm-4">
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-sm btn-primary flex-grow-1 d-inline-flex align-items-center justify-content-center gap-1 fw-semibold" style="height: 34px; white-space: nowrap;" title="Apply Filter">
                            <iconify-icon icon="solar:magnifer-linear" class="fs-15"></iconify-icon> Filter
                        </button>
                        <a href="{{ route('adc.reports.gst') }}" class="btn btn-sm btn-outline-secondary d-inline-flex align-items-center justify-content-center" style="height: 34px; width: 36px; min-width: 36px;" title="Reset Filters">
                            <iconify-icon icon="solar:restart-bold" class="fs-15"></iconify-icon>
                        </a>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Main GST Report Table Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="font-size: 12.5px;">
                    <thead class="bg-light text-uppercase fs-11 text-muted border-bottom">
                        <tr>
                            <th class="ps-3" style="width: 45px;">#</th>
                            <th style="min-width: 130px;">Invoice Date</th>
                            <th style="min-width: 140px;">Invoice No</th>
                            <th style="min-width: 180px;">Product Name</th>
                            <th style="min-width: 150px;">GSTIN OF RECEIPIENT</th>
                            <th style="min-width: 180px;">RECEIVER NAME</th>
                            <th style="min-width: 130px;">PLACE OF SUPPLY</th>
                            <th class="text-center" style="width: 80px;">Tax Rate</th>
                            <th class="text-center" style="min-width: 100px;">HSN CODE</th>
                            <th class="text-end" style="min-width: 120px;">Taxable Amount</th>
                            <th class="text-end" style="min-width: 100px;">SGST</th>
                            <th class="text-end" style="min-width: 100px;">CGST</th>
                            <th class="text-end" style="min-width: 100px;">IGST</th>
                            <th class="text-end pe-3" style="min-width: 125px;">Total AMOUNT</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($paginatedRecords as $row)
                            <tr>
                                <td class="ps-3 fw-bold text-slate-500">
                                    {{ $loop->iteration + ($paginatedRecords->currentPage() - 1) * $paginatedRecords->perPage() }}
                                </td>
                                <td class="fw-semibold text-slate-800 text-nowrap">
                                    {{ $row['invoice_date'] }}
                                </td>
                                <td class="text-nowrap">
                                    @if(!empty($row['order_id']))
                                        <a href="{{ route('adc.pos.orders.print', $row['order_id']) }}" target="_blank"
                                            class="fw-bold text-primary text-decoration-none"
                                            title="View Tax Invoice">
                                            {{ $row['invoice_no'] }}
                                        </a>
                                    @else
                                        <span class="fw-bold text-primary">{{ $row['invoice_no'] }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark text-truncate" style="max-width: 220px;" title="{{ $row['product_name'] ?? '—' }}">
                                        {{ $row['product_name'] ?? '—' }}
                                    </div>
                                </td>
                                <td>
                                    @if($row['recipient_gstin'] === 'REGISTERED/URP' || $row['recipient_gstin'] === 'URP')
                                        <span class="badge bg-secondary-subtle text-secondary px-2 py-0.5 fs-11">REGISTERED/URP</span>
                                    @else
                                        <code class="fw-bold text-dark fs-12">{{ $row['recipient_gstin'] }}</code>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-slate-800 text-truncate" style="max-width: 220px;" title="{{ $row['receiver_name'] }}">
                                        {{ $row['receiver_name'] }}
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-slate-700 border text-uppercase px-2 py-0.5 fs-11">
                                        {{ $row['place_of_supply'] }}
                                    </span>
                                </td>
                                <td class="text-center fw-bold text-slate-700">
                                    {{ (float) $row['tax_rate'] }}%
                                </td>
                                <td class="text-center">
                                    <code class="fw-bold text-dark fs-12">{{ $row['hsn_code'] }}</code>
                                </td>
                                <td class="text-end fw-semibold text-slate-800">
                                    ₹{{ number_format($row['taxable_amount'], 2) }}
                                </td>
                                <td class="text-end fw-semibold text-info">
                                    ₹{{ number_format($row['sgst_amount'], 2) }}
                                </td>
                                <td class="text-end fw-semibold text-primary">
                                    ₹{{ number_format($row['cgst_amount'], 2) }}
                                </td>
                                <td class="text-end fw-semibold text-warning-emphasis">
                                    ₹{{ number_format($row['igst_amount'], 2) }}
                                </td>
                                <td class="text-end pe-3 fw-bold text-success fs-13">
                                    ₹{{ number_format($row['total_amount'], 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="14" class="text-center py-5 text-muted">
                                    <iconify-icon icon="solar:box-minimalistic-broken" class="fs-48 text-muted mb-2 d-block mx-auto"></iconify-icon>
                                    <p class="mb-1 fw-semibold fs-14">No GST Sales Records Found</p>
                                    <span class="text-muted small">No outward supplies matching the selected dates and filters.</span>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    @if($paginatedRecords->isNotEmpty())
                        <tfoot class="bg-light fw-bold border-top border-2">
                            <tr>
                                <td colspan="9" class="text-end text-uppercase fs-12 ps-3">Total (Current Filter):</td>
                                <td class="text-end text-dark">₹{{ number_format($summaryStats['total_taxable'], 2) }}</td>
                                <td class="text-end text-info">₹{{ number_format($summaryStats['total_sgst'], 2) }}</td>
                                <td class="text-end text-primary">₹{{ number_format($summaryStats['total_cgst'], 2) }}</td>
                                <td class="text-end text-warning-emphasis">₹{{ number_format($summaryStats['total_igst'], 2) }}</td>
                                <td class="text-end pe-3 text-success fs-13">₹{{ number_format($summaryStats['total_amount'], 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
        @if($paginatedRecords->hasPages())
            <div class="card-footer bg-white py-2 px-3 border-top d-flex justify-content-between align-items-center flex-wrap gap-2">
                <span class="text-muted small">
                    Showing {{ $paginatedRecords->firstItem() }} to {{ $paginatedRecords->lastItem() }} of {{ $paginatedRecords->total() }} records
                </span>
                <div>
                    {{ $paginatedRecords->links() }}
                </div>
            </div>
        @endif
    </div>

    <!-- Send GST Report Modal -->
    <div class="modal fade" id="sendGstReportModal" tabindex="-1" aria-labelledby="sendGstReportModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-3">
                <div class="modal-header bg-light border-bottom py-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="rounded-circle p-2 bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <iconify-icon icon="solar:plain-bold-duotone" class="fs-20"></iconify-icon>
                        </span>
                        <div>
                            <h5 class="modal-title fw-bold text-dark fs-16 mb-0" id="sendGstReportModalLabel">Send GST Report</h5>
                            <small class="text-muted fs-12">Email your GST Outward Supplies report as an attachment</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <form id="sendGstReportForm" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <!-- Alert container for AJAX errors/validation -->
                        <div id="modalAlertBox" class="alert d-none mb-3 py-2 px-3 fs-13" role="alert"></div>

                        <!-- ADC User Details (Readonly) -->
                        <div class="row g-2 mb-3">
                            <div class="col-sm-7">
                                <label class="form-label fs-12 fw-semibold text-muted mb-1">
                                    <iconify-icon icon="solar:user-linear" class="align-middle me-1"></iconify-icon> ADC User Name
                                </label>
                                <input type="text" class="form-control form-control-sm bg-light text-dark fw-semibold" 
                                    value="{{ $adcUser->name ?? (auth('adc')->user()->name ?? 'ADC Depot') }}" readonly disabled>
                            </div>
                            <div class="col-sm-5">
                                <label class="form-label fs-12 fw-semibold text-muted mb-1">
                                    <iconify-icon icon="solar:tag-linear" class="align-middle me-1"></iconify-icon> ADC Code
                                </label>
                                <input type="text" class="form-control form-control-sm bg-light text-primary fw-bold" 
                                    value="{{ $adcCode ?? (auth('adc')->user()->adcProfile->adc_code ?? 'ADC') }}" readonly disabled>
                            </div>
                        </div>

                        <!-- Recipient Email -->
                        <div class="mb-3">
                            <label for="recipientEmail" class="form-label fs-12 fw-semibold text-dark mb-1">
                                <iconify-icon icon="solar:letter-linear" class="align-middle me-1 text-primary"></iconify-icon> Recipient Email <span class="text-danger">*</span>
                            </label>
                            <input type="email" class="form-control" id="recipientEmail" name="recipient_email" 
                                placeholder="e.g. ca@example.com, tax@company.com" required>
                            <small class="text-muted fs-11">The report will be sent directly to this email address.</small>
                        </div>

                        <!-- File Upload Option -->
                        <div class="mb-3">
                            <label for="reportFile" class="form-label fs-12 fw-semibold text-dark mb-1">
                                <iconify-icon icon="solar:file-spreadsheet-linear" class="align-middle me-1 text-success"></iconify-icon> Attach GST Report (Excel / CSV) <span class="text-danger">*</span>
                            </label>
                            <input type="file" class="form-control" id="reportFile" name="report_file" 
                                accept=".csv, .xlsx, .xls, text/csv, application/vnd.ms-excel, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" required>
                            <div class="d-flex justify-content-between align-items-center mt-1">
                                <small class="text-muted fs-11">Supported: .CSV, .XLS, .XLSX (Max: 10MB)</small>
                                <a href="{{ route('adc.reports.gst.export-csv', request()->query()) }}" class="text-decoration-none fs-11 fw-semibold text-success" title="Download latest CSV to attach">
                                    <iconify-icon icon="solar:download-linear" class="align-middle"></iconify-icon> Download Current CSV
                                </a>
                            </div>
                        </div>

                        <!-- Optional Remarks / Note -->
                        <div class="mb-1">
                            <label for="sendRemarks" class="form-label fs-12 fw-semibold text-muted mb-1">
                                <iconify-icon icon="solar:notes-linear" class="align-middle me-1"></iconify-icon> Message / Remarks (Optional)
                            </label>
                            <textarea class="form-control fs-13" id="sendRemarks" name="remarks" rows="2" 
                                placeholder="Add any note for the recipient (e.g. GST Outward Supplies report for October 2026)..." maxlength="500"></textarea>
                        </div>
                    </div>

                    <div class="modal-footer bg-light border-top py-2 px-4 d-flex justify-content-between">
                        <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary px-4 fw-semibold d-inline-flex align-items-center gap-2" id="sendReportSubmitBtn">
                            <span class="spinner-border spinner-border-sm d-none" id="sendReportSpinner" role="status" aria-hidden="true"></span>
                            <iconify-icon icon="solar:plain-bold" id="sendReportIcon" class="fs-16"></iconify-icon>
                            <span id="sendReportBtnText">Send Report</span>
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
    const form = document.getElementById('sendGstReportForm');
    if (!form) return;

    const submitBtn   = document.getElementById('sendReportSubmitBtn');
    const spinner     = document.getElementById('sendReportSpinner');
    const icon        = document.getElementById('sendReportIcon');
    const btnText     = document.getElementById('sendReportBtnText');
    const alertBox    = document.getElementById('modalAlertBox');
    const emailInput  = document.getElementById('recipientEmail');
    const fileInput   = document.getElementById('reportFile');

    function showAlert(msg, isSuccess = false) {
        alertBox.className = 'alert mb-3 py-2 px-3 fs-13 ' + (isSuccess ? 'alert-success' : 'alert-danger');
        alertBox.innerHTML = (isSuccess ? '<i class="bi bi-check-circle-fill me-1"></i> ' : '<i class="bi bi-exclamation-triangle-fill me-1"></i> ') + msg;
        alertBox.classList.remove('d-none');
    }

    function hideAlert() {
        alertBox.classList.add('d-none');
        alertBox.innerHTML = '';
    }

    form.addEventListener('submit', function (e) {
        e.preventDefault();
        hideAlert();

        const email = emailInput.value.trim();
        if (!email) {
            showAlert('Please enter a recipient email address.');
            emailInput.focus();
            return;
        }

        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (!emailRegex.test(email)) {
            showAlert('Please enter a valid email address.');
            emailInput.focus();
            return;
        }

        if (!fileInput.files || fileInput.files.length === 0) {
            showAlert('Please select an Excel or CSV file to attach.');
            fileInput.focus();
            return;
        }

        const file = fileInput.files[0];
        if (file.size > 10 * 1024 * 1024) {
            showAlert('File size exceeds the 10MB limit.');
            return;
        }

        const formData = new FormData(form);

        // UI loading state
        submitBtn.disabled = true;
        spinner.classList.remove('d-none');
        icon.classList.add('d-none');
        btnText.textContent = 'Sending Report...';

        fetch("{{ route('adc.reports.gst.send-email') }}", {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || form.querySelector('input[name="_token"]').value
            },
            body: formData
        })
        .then(async response => {
            const data = await response.json().catch(() => ({}));
            if (!response.ok) {
                const errMsg = data.message || (data.errors ? Object.values(data.errors).flat().join('<br>') : 'Failed to send email. Please try again.');
                throw new Error(errMsg);
            }
            return data;
        })
        .then(data => {
            if (data.success) {
                showAlert(data.message || 'Report sent successfully!', true);

                if (typeof window.showToast === 'function') {
                    window.showToast(data.message || 'GST Report sent successfully!', 'success');
                } else if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Sent!',
                        text: data.message || 'GST Report sent successfully!',
                        timer: 3000,
                        showConfirmButton: false
                    });
                }

                setTimeout(() => {
                    form.reset();
                    hideAlert();
                    const modalEl = document.getElementById('sendGstReportModal');
                    const modalInstance = bootstrap.Modal.getInstance(modalEl);
                    if (modalInstance) {
                        modalInstance.hide();
                    }
                }, 2000);
            } else {
                showAlert(data.message || 'Failed to send report.');
            }
        })
        .catch(err => {
            showAlert(err.message || 'Server error occurred while sending the email.');
        })
        .finally(() => {
            submitBtn.disabled = false;
            spinner.classList.add('d-none');
            icon.classList.remove('d-none');
            btnText.textContent = 'Send Report';
        });
    });

    const modalEl = document.getElementById('sendGstReportModal');
    if (modalEl) {
        modalEl.addEventListener('hidden.bs.modal', function () {
            hideAlert();
        });
    }
});
</script>
@endpush

