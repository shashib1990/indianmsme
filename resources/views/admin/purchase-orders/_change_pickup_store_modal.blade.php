<!-- Change Pickup Store Modal Component -->
<div class="modal fade" id="changePickupStoreModal" tabindex="-1" aria-labelledby="changePickupStoreModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom py-3 px-4 bg-light bg-opacity-50">
                <h5 class="modal-title fw-bold text-slate-800 d-flex align-items-center gap-2" id="changePickupStoreModalLabel">
                    <iconify-icon icon="solar:shop-bold-duotone" class="text-primary fs-22"></iconify-icon>
                    Change Pickup Store
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="changePickupStoreForm">
                @csrf
                <input type="hidden" id="modalPoId" name="po_id">
                <div class="modal-body p-4">
                    <!-- PO Details Summary -->
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="row g-2 fs-13">
                            <div class="col-6">
                                <span class="text-muted d-block fs-11 text-uppercase fw-semibold">Purchase Order</span>
                                <span class="fw-bold text-dark font-monospace" id="modalPoNumber">—</span>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block fs-11 text-uppercase fw-semibold">Seller Partner</span>
                                <span class="fw-semibold text-dark text-truncate d-block" id="modalSellerName">—</span>
                            </div>
                        </div>
                    </div>

                    <!-- Pickup Store Selector -->
                    <div class="mb-3">
                        <label for="modalSelectPickupStore" class="form-label fw-semibold text-slate-700 fs-13 mb-1">
                            Select Pickup Store Code &amp; Outlet <span class="text-danger">*</span>
                        </label>
                        <select class="form-select fs-13 py-2" id="modalSelectPickupStore" name="pickup_store_id">
                            <option value="">Loading stores...</option>
                        </select>
                        <div class="form-text fs-12 mt-1 text-muted">
                            Changing the pickup store will automatically update the dispatch from name, address, and GSTIN on all linked invoices (DL Series).
                        </div>
                    </div>

                    <!-- Selected Store Preview Card -->
                    <div id="modalStorePreviewCard" class="p-3 rounded-3 border border-primary-subtle bg-primary-subtle bg-opacity-10 d-none">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <span class="badge bg-primary text-white font-monospace px-2 py-0.5 fs-11" id="previewStoreCode">PSC000000</span>
                            <span class="badge bg-success-subtle text-success px-2 py-0.5 fs-11" id="previewStoreAuth">Authorized</span>
                        </div>
                        <h6 class="fw-bold text-slate-800 mb-1 fs-13" id="previewStoreName">Store Name</h6>
                        <div class="text-muted small fs-11" id="previewStoreLocation">Location</div>
                    </div>
                </div>
                <div class="modal-footer bg-light bg-opacity-50 border-top py-2.5 px-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-outline-secondary px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary px-4 fw-semibold" id="btnSubmitChangeStore">
                        <span class="spinner-border spinner-border-sm me-1 d-none" id="btnSubmitStoreSpinner" role="status" aria-hidden="true"></span>
                        <iconify-icon icon="solar:check-circle-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Update Store &amp; Invoices
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    (function () {
        if (window.pickupStoreModalInitialized) return;
        window.pickupStoreModalInitialized = true;

        document.addEventListener('DOMContentLoaded', function () {
            const modalEl = document.getElementById('changePickupStoreModal');
            if (!modalEl) return;

            const modalPoId = document.getElementById('modalPoId');
            const modalPoNumber = document.getElementById('modalPoNumber');
            const modalSellerName = document.getElementById('modalSellerName');
            const selectEl = document.getElementById('modalSelectPickupStore');
            const previewCard = document.getElementById('modalStorePreviewCard');
            const previewCode = document.getElementById('previewStoreCode');
            const previewName = document.getElementById('previewStoreName');
            const previewAuth = document.getElementById('previewStoreAuth');
            const previewLoc = document.getElementById('previewStoreLocation');
            const form = document.getElementById('changePickupStoreForm');
            const btnSubmit = document.getElementById('btnSubmitChangeStore');
            const spinner = document.getElementById('btnSubmitStoreSpinner');

            function updatePreview() {
                const selectedOpt = selectEl.options[selectEl.selectedIndex];
                const storeId = selectEl.value;

                if (storeId && selectedOpt) {
                    const code = selectedOpt.getAttribute('data-code') || 'PSC';
                    const name = selectedOpt.getAttribute('data-name') || '';
                    const auth = selectedOpt.getAttribute('data-auth') === '1';
                    const loc = selectedOpt.getAttribute('data-loc') || '';

                    previewCode.textContent = code;
                    previewName.textContent = name;
                    previewLoc.textContent = loc ? 'Location: ' + loc : '';

                    if (auth) {
                        previewAuth.className = 'badge bg-success-subtle text-success px-2 py-0.5 fs-11';
                        previewAuth.textContent = 'Authorized Outlet';
                    } else {
                        previewAuth.className = 'badge bg-warning-subtle text-warning px-2 py-0.5 fs-11';
                        previewAuth.textContent = 'Unauthorized Outlet';
                    }

                    previewCard.classList.remove('d-none');
                } else {
                    previewCard.classList.add('d-none');
                }
            }

            selectEl.addEventListener('change', updatePreview);

            // Open Modal on button click
            document.addEventListener('click', function (e) {
                const btn = e.target.closest('.btn-change-pickup-store');
                if (!btn) return;

                const poId = btn.getAttribute('data-po-id');
                const poNumber = btn.getAttribute('data-po-number');
                const sellerName = btn.getAttribute('data-seller-name');
                const currentStoreId = btn.getAttribute('data-current-store-id') || '';

                modalPoId.value = poId;
                modalPoNumber.textContent = '#' + poNumber;
                modalSellerName.textContent = sellerName || 'Seller';
                previewCard.classList.add('d-none');

                selectEl.innerHTML = '<option value="">Loading stores...</option>';
                selectEl.disabled = true;
                btnSubmit.disabled = true;

                const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
                bsModal.show();

                const fetchUrl = "{{ url('admin/purchase-orders') }}/" + poId + "/pickup-stores";

                fetch(fetchUrl, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    selectEl.innerHTML = '';

                    const defaultOpt = document.createElement('option');
                    defaultOpt.value = '';
                    defaultOpt.textContent = '-- None (Direct Seller Fulfillment) --';
                    defaultOpt.setAttribute('data-code', 'None');
                    defaultOpt.setAttribute('data-name', 'None (Seller Direct)');
                    defaultOpt.setAttribute('data-auth', '0');
                    selectEl.appendChild(defaultOpt);

                    if (data.seller_stores && data.seller_stores.length > 0) {
                        data.seller_stores.forEach(store => {
                            const opt = document.createElement('option');
                            opt.value = store.id;
                            opt.setAttribute('data-code', store.pickup_store_code);
                            opt.setAttribute('data-name', store.pickup_store_name);
                            opt.setAttribute('data-auth', store.can_receive_payment_and_dispatch ? '1' : '0');
                            opt.setAttribute('data-loc', (store.district || '') + (store.state ? ', ' + store.state : ''));
                            opt.textContent = '[' + store.pickup_store_code + '] ' + store.pickup_store_name + (store.district ? ' — ' + store.district : '') + (store.can_receive_payment_and_dispatch ? ' (Authorized)' : ' (Unauthorized)');

                            if (String(store.id) === String(data.current_pickup_store_id)) {
                                opt.selected = true;
                            }
                            selectEl.appendChild(opt);
                        });
                    } else {
                        const noStoreOpt = document.createElement('option');
                        noStoreOpt.disabled = true;
                        noStoreOpt.textContent = 'No pickup stores found for this seller';
                        selectEl.appendChild(noStoreOpt);
                    }

                    selectEl.disabled = false;
                    btnSubmit.disabled = false;
                    updatePreview();
                })
                .catch(err => {
                    selectEl.innerHTML = '<option value="">Failed to load pickup stores</option>';
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', 'Failed to load pickup stores for this seller.', 'error');
                    } else {
                        alert('Failed to load pickup stores.');
                    }
                });
            });

            // Form Submit handler
            form.addEventListener('submit', function (e) {
                e.preventDefault();
                const poId = modalPoId.value;
                const storeId = selectEl.value;
                const updateUrl = "{{ url('admin/purchase-orders') }}/" + poId + "/update-pickup-store";

                btnSubmit.disabled = true;
                spinner.classList.remove('d-none');

                fetch(updateUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        pickup_store_id: storeId || null
                    })
                })
                .then(res => res.json())
                .then(res => {
                    btnSubmit.disabled = false;
                    spinner.classList.add('d-none');

                    if (res.success) {
                        const bsModal = bootstrap.Modal.getInstance(modalEl);
                        if (bsModal) bsModal.hide();

                        // Update Table Cell DOM if present on current page
                        const displayWrap = document.getElementById('po-store-display-' + poId);
                        if (displayWrap) {
                            if (res.pickup_store) {
                                const ps = res.pickup_store;
                                const authBadge = ps.can_receive_payment_and_dispatch
                                    ? '<span class="badge bg-info-subtle text-info px-1 py-0 fs-10 po-store-auth">Authorized</span>'
                                    : '<span class="badge bg-light text-muted px-1 py-0 fs-10 po-store-auth">Unauthorized</span>';

                                displayWrap.innerHTML = `
                                    <div class="fw-semibold text-slate-800 po-store-name">${escapeHtml(ps.pickup_store_name)}</div>
                                    <div class="small d-flex align-items-center gap-1 mt-0.5 flex-wrap">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-1.5 py-0.5 fs-10 font-monospace po-store-code" title="Pickup Store Code">
                                            <iconify-icon icon="solar:shop-bold-duotone" class="align-middle me-0.5"></iconify-icon>${escapeHtml(ps.pickup_store_code)}
                                        </span>
                                        ${authBadge}
                                    </div>
                                `;
                            } else {
                                displayWrap.innerHTML = '<span class="text-muted small po-store-none">None (Seller Direct)</span>';
                            }

                            // Update button data attribute
                            const changeBtn = document.querySelector(`.btn-change-pickup-store[data-po-id="${poId}"]`);
                            if (changeBtn) {
                                changeBtn.setAttribute('data-current-store-id', res.pickup_store ? res.pickup_store.id : '');
                            }
                        }

                        // If on PO show page, reload to refresh invoice and store info tables
                        if (document.getElementById('po-show-store-wrap')) {
                            window.location.reload();
                            return;
                        }

                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'Pickup Store Updated',
                                text: res.message || 'Store code and linked invoices updated successfully.',
                                confirmButtonColor: '#ff9933'
                            });
                        } else {
                            alert(res.message);
                        }
                    } else {
                        if (typeof Swal !== 'undefined') {
                            Swal.fire('Error', res.message || 'Failed to update pickup store.', 'error');
                        } else {
                            alert(res.message || 'Failed to update pickup store.');
                        }
                    }
                })
                .catch(err => {
                    btnSubmit.disabled = false;
                    spinner.classList.add('d-none');
                    if (typeof Swal !== 'undefined') {
                        Swal.fire('Error', 'Server error updating pickup store: ' + err.message, 'error');
                    } else {
                        alert('Server error updating pickup store.');
                    }
                });
            });

            function escapeHtml(text) {
                if (!text) return '';
                const map = {
                    '&': '&amp;',
                    '<': '&lt;',
                    '>': '&gt;',
                    '"': '&quot;',
                    "'": '&#039;'
                };
                return text.replace(/[&<>"']/g, function(m) { return map[m]; });
            }
        });
    })();
</script>
@endpush
