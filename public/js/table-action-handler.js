/**
 * Indian MSME - Universal Table Action & State Preservation Handler
 * Preserves pagination, filters, search, scroll position, and handles AJAX row updates.
 */

(function () {
    'use strict';

    const STORAGE_KEY = 'msme_table_state';

    const TableStatePreserver = {
        save: function () {
            const state = {
                scrollY: window.scrollY || window.pageYOffset || document.documentElement.scrollTop,
                url: window.location.href,
                search: window.location.search,
                timestamp: Date.now()
            };
            try {
                sessionStorage.setItem(STORAGE_KEY, JSON.stringify(state));
            } catch (e) {
                console.warn('Unable to save table state to sessionStorage:', e);
            }
        },

        restore: function () {
            try {
                const raw = sessionStorage.getItem(STORAGE_KEY);
                if (!raw) return;
                const state = JSON.parse(raw);
                sessionStorage.removeItem(STORAGE_KEY);

                // Only restore scroll if we are on the same page/URL structure within 30 seconds
                if (state && (Date.now() - state.timestamp < 30000)) {
                    const currentPath = window.location.pathname;
                    const savedPath = new URL(state.url, window.location.origin).pathname;
                    if (currentPath === savedPath && typeof state.scrollY === 'number') {
                        window.scrollTo({
                            top: state.scrollY,
                            behavior: 'instant'
                        });
                    }
                }
            } catch (e) {
                console.warn('Unable to restore table state:', e);
            }
        }
    };

    window.TableStatePreserver = TableStatePreserver;

    // Toast Notification Helper
    function showToast(message, type = 'success') {
        if (typeof Toastify === 'function') {
            const bg = type === 'success' ? '#10b981' : (type === 'error' ? '#ef4444' : '#3b82f6');
            Toastify({
                text: message,
                duration: 3000,
                close: true,
                gravity: "top",
                position: "right",
                style: { background: bg }
            }).showToast();
        } else if (typeof Swal === 'function' || typeof Swal !== 'undefined') {
            Swal.fire({
                icon: type,
                title: message,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 3000
            });
        } else {
            console.log(`[${type.toUpperCase()}] ${message}`);
        }
    }

    // Row dynamic update helper
    function updateRowUI(targetRow, responseData) {
        if (!targetRow) return;

        // If action indicates deletion, remove row with animation
        if (responseData.deleted || responseData.action === 'deleted') {
            targetRow.style.transition = 'all 0.3s ease';
            targetRow.style.opacity = '0';
            targetRow.style.transform = 'scale(0.95)';
            setTimeout(() => {
                targetRow.remove();
            }, 300);
            return;
        }

        // Update status switches / badges if status returned
        if (typeof responseData.status !== 'undefined' || typeof responseData.is_active !== 'undefined') {
            const isActive = !!(responseData.status === 1 || responseData.status === 'active' || responseData.is_active || responseData.status === true);
            
            const badge = targetRow.querySelector('.status-badge, .badge-status, [data-status-badge]');
            if (badge) {
                badge.className = isActive ? 'badge bg-success-subtle text-success px-2 py-1 rounded-pill fw-semibold' : 'badge bg-danger-subtle text-danger px-2 py-1 rounded-pill fw-semibold';
                badge.textContent = isActive ? (responseData.status_text || 'Active') : (responseData.status_text || 'Inactive');
            }

            const toggle = targetRow.querySelector('.form-check-input, .status-toggle-switch');
            if (toggle) {
                toggle.checked = isActive;
            }
        }

        // Update specific returned field values if provided
        if (responseData.fields && typeof responseData.fields === 'object') {
            Object.keys(responseData.fields).forEach(key => {
                const el = targetRow.querySelector(`[data-field="${key}"], .field-${key}`);
                if (el) {
                    if (el.tagName === 'INPUT' || el.tagName === 'SELECT') {
                        el.value = responseData.fields[key];
                    } else {
                        el.textContent = responseData.fields[key];
                    }
                }
            });
        }
    }

    // Partial Table Refresh without Page Reload
    function reloadTablePartial(tableContainerSelector = '.table-responsive, [data-table-container]') {
        const container = document.querySelector(tableContainerSelector);
        if (!container) return Promise.resolve();

        const currentUrl = window.location.href;
        
        return fetch(currentUrl, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'text/html'
            }
        })
        .then(res => res.text())
        .then(html => {
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            const newContainer = doc.querySelector(tableContainerSelector);
            if (newContainer && container) {
                const currentScroll = window.scrollY;
                container.innerHTML = newContainer.innerHTML;
                window.scrollTo(0, currentScroll);
                bindAjaxTableActions();
            }
        })
        .catch(err => console.error('Error refreshing table container:', err));
    }

    window.reloadTablePartial = reloadTablePartial;

    // Core AJAX form / action handler
    function bindAjaxTableActions() {
        // 1. Universal AJAX Action Forms
        document.querySelectorAll('form[data-ajax-form="true"], form.ajax-action-form').forEach(form => {
            if (form.dataset.ajaxBound) return;
            form.dataset.ajaxBound = 'true';

            form.addEventListener('submit', function (e) {
                e.preventDefault();

                const submitBtn = form.querySelector('[type="submit"]');
                const originalBtnText = submitBtn ? submitBtn.innerHTML : '';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving...';
                }

                const formData = new FormData(form);
                const url = form.action;
                const method = (form.method || 'POST').toUpperCase();
                const targetRow = form.closest('tr');

                fetch(url, {
                    method: method,
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    }
                })
                .then(res => {
                    const contentType = res.headers.get('content-type');
                    if (contentType && contentType.includes('application/json')) {
                        return res.json();
                    }
                    return res.text().then(text => ({ success: res.ok, message: 'Updated successfully', html: text }));
                })
                .then(data => {
                    if (data.success !== false) {
                        showToast(data.message || 'Updated successfully', 'success');
                        
                        // Close modal if form was in modal
                        const modalEl = form.closest('.modal');
                        if (modalEl && typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                            const modalInstance = bootstrap.Modal.getInstance(modalEl);
                            if (modalInstance) modalInstance.hide();
                        }

                        if (targetRow) {
                            updateRowUI(targetRow, data);
                        } else {
                            // Refresh table container dynamically without resetting scroll/page
                            reloadTablePartial();
                        }
                    } else {
                        showToast(data.message || 'An error occurred', 'error');
                    }
                })
                .catch(err => {
                    console.error('AJAX Action error:', err);
                    showToast('Failed to process request.', 'error');
                })
                .finally(() => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                });
            });
        });

        // 2. Status Switch / Checkbox Toggles
        document.querySelectorAll('.status-toggle-switch, [data-toggle-url]').forEach(toggle => {
            if (toggle.dataset.ajaxBound) return;
            toggle.dataset.ajaxBound = 'true';

            toggle.addEventListener('change', function () {
                const url = this.dataset.toggleUrl || this.dataset.url;
                if (!url) return;

                const targetRow = this.closest('tr');
                const isChecked = this.checked;
                const statusVal = this.dataset.statusVal || (isChecked ? 1 : 0);

                fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({ status: statusVal, is_active: isChecked ? 1 : 0 })
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success !== false) {
                        showToast(data.message || 'Status updated successfully', 'success');
                        if (targetRow) {
                            updateRowUI(targetRow, data);
                        }
                    } else {
                        this.checked = !isChecked;
                        showToast(data.message || 'Failed to update status', 'error');
                    }
                })
                .catch(err => {
                    this.checked = !isChecked;
                    console.error('Status toggle error:', err);
                    showToast('Network error updating status.', 'error');
                });
            });
        });

        // 3. Delete Action Buttons
        document.querySelectorAll('[data-delete-url], .ajax-delete-btn').forEach(btn => {
            if (btn.dataset.ajaxBound) return;
            btn.dataset.ajaxBound = 'true';

            btn.addEventListener('click', function (e) {
                e.preventDefault();

                const url = this.dataset.deleteUrl || this.href || this.action;
                const confirmMsg = this.dataset.confirmMsg || 'Are you sure you want to delete this record?';
                const targetRow = this.closest('tr');

                const performDelete = () => {
                    fetch(url, {
                        method: 'DELETE',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success !== false) {
                            showToast(data.message || 'Deleted successfully', 'success');
                            if (targetRow) {
                                updateRowUI(targetRow, { deleted: true });
                            } else {
                                reloadTablePartial();
                            }
                        } else {
                            showToast(data.message || 'Failed to delete record.', 'error');
                        }
                    })
                    .catch(err => {
                        console.error('Delete error:', err);
                        showToast('Error deleting record.', 'error');
                    });
                };

                if (typeof Swal === 'function') {
                    Swal.fire({
                        title: 'Confirm Delete',
                        text: confirmMsg,
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#ef4444',
                        confirmButtonText: 'Yes, Delete'
                    }).then(result => {
                        if (result.isConfirmed) performDelete();
                    });
                } else if (confirm(confirmMsg)) {
                    performDelete();
                }
            });
        });
    }

    // Intercept full page reloads to preserve scroll & query state
    window.addEventListener('beforeunload', function () {
        TableStatePreserver.save();
    });

    document.addEventListener('DOMContentLoaded', function () {
        TableStatePreserver.restore();
        bindAjaxTableActions();
    });

})();
