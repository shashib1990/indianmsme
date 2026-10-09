{{-- DDC Invoice Upload/Edit Modal --}}
<div class="modal fade" id="ddcInvoiceModal" tabindex="-1" aria-labelledby="ddcInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title fw-bold" id="ddcInvoiceModalLabel" style="font-family:'Outfit',sans-serif;">
                    Update DDC Invoice Details
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="ddcInvoiceForm" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="modal_ddc_invoice_number" class="form-label small fw-semibold text-slate-700">
                            DDC Invoice Number <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control" id="modal_ddc_invoice_number" name="ddc_invoice_number"
                               required placeholder="e.g. DDC-26-27-001">
                    </div>
                    <div class="mb-3">
                        <label for="modal_ddc_invoice_date" class="form-label small fw-semibold text-slate-700">
                            DDC Invoice Date <span class="text-danger">*</span>
                        </label>
                        <input type="date" class="form-control" id="modal_ddc_invoice_date" name="ddc_invoice_date"
                               required value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="mb-3">
                        <label for="modal_ddc_invoice_file" class="form-label small fw-semibold text-slate-700">
                            Invoice Attachment (PDF or Image) <span class="text-danger" id="file_required_asterisk">*</span>
                        </label>
                        <input type="file" class="form-control" id="modal_ddc_invoice_file" name="ddc_invoice_file"
                               accept=".pdf,.png,.jpg,.jpeg">
                        <div id="existing_file_container" class="mt-2 small text-success d-none">
                            <iconify-icon icon="solar:document-bold-duotone" class="align-middle me-1"></iconify-icon>
                            <a href="#" id="modal_existing_file_link" target="_blank" class="text-success fw-semibold">View uploaded file</a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 p-3 bg-light bg-opacity-50">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>
</div>
