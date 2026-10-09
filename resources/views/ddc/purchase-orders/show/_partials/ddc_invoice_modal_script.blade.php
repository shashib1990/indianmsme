<script>
document.addEventListener('DOMContentLoaded', function() {
    const ddcInvoiceModal = document.getElementById('ddcInvoiceModal');
    if (ddcInvoiceModal) {
        ddcInvoiceModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const actionUrl   = button.getAttribute('data-action');
            const invoiceNum  = button.getAttribute('data-invoice-number') || '';
            const invoiceDate = button.getAttribute('data-invoice-date') || '';
            const invoiceFile = button.getAttribute('data-invoice-file') || '';

            document.getElementById('ddcInvoiceForm').setAttribute('action', actionUrl);
            document.getElementById('modal_ddc_invoice_number').value = invoiceNum;
            document.getElementById('modal_ddc_invoice_date').value   = invoiceDate || new Date().toISOString().split('T')[0];

            const asterisk             = document.getElementById('file_required_asterisk');
            const fileInput            = document.getElementById('modal_ddc_invoice_file');
            const existingFileContainer = document.getElementById('existing_file_container');
            const existingFileLink     = document.getElementById('modal_existing_file_link');

            if (invoiceFile) {
                asterisk.classList.add('d-none');
                fileInput.removeAttribute('required');
                existingFileContainer.classList.remove('d-none');
                existingFileLink.setAttribute('href', invoiceFile);
            } else {
                asterisk.classList.remove('d-none');
                fileInput.setAttribute('required', 'required');
                existingFileContainer.classList.add('d-none');
                existingFileLink.setAttribute('href', '#');
            }
        });
    }
});
</script>
