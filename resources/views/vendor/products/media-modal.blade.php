<!-- Media Library Selector Modal -->
<div class="modal fade" id="mediaLibraryModal" tabindex="-1" aria-labelledby="mediaLibraryModalLabel" aria-hidden="true" style="display: none; z-index: 1060;">
    <div class="modal-dialog modal-xl modal-dialog-scrollable modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-slate-800" id="mediaLibraryModalLabel">
                    <iconify-icon icon="solar:gallery-wide-bold-duotone" class="align-middle me-1 text-success"></iconify-icon>
                    Select Asset from Media Library
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <!-- Top controls -->
                <div class="row g-2 mb-3">
                    <div class="col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-end-0"><iconify-icon icon="solar:magnifer-bold-duotone" class="text-muted"></iconify-icon></span>
                            <input type="text" id="modalSearch" class="form-control border-start-0" placeholder="Search file name...">
                        </div>
                    </div>
                    <div class="col-md-3">
                        <select id="modalTypeFilter" class="form-select">
                            <option value="">All Formats</option>
                            <option value="image">Images</option>
                            <option value="pdf">PDFs</option>
                        </select>
                    </div>
                    <div class="col-md-4 text-end">
                        <!-- Inline upload form -->
                        <form id="modalUploadForm" enctype="multipart/form-data" class="d-flex gap-1">
                            @csrf
                            <input type="file" name="file" id="modalUploadFile" class="form-control form-control-sm" required accept="image/*,application/pdf">
                            <button type="submit" class="btn btn-sm btn-success text-nowrap" id="modalUploadBtn">
                                <iconify-icon icon="solar:upload-bold-duotone" class="align-middle me-0.5"></iconify-icon> Upload
                            </button>
                        </form>
                    </div>
                </div>

                <!-- Alert for errors -->
                <div class="alert alert-danger d-none" id="modalAlert"></div>

                <!-- Grid container -->
                <div class="row g-2" id="modalMediaGrid" style="min-height: 250px;">
                    <!-- Filled dynamically via JS -->
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="confirmSelectionBtn">Choose Selected</button>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    let targetInputId = '';
    let targetPreviewId = '';
    let isMultiSelect = false;
    let selectedUrls = [];

    const mediaModalEl = document.getElementById('mediaLibraryModal');
    // Relocate modal to body to prevent styling/scrolling issues from parent wrapper container constraints
    document.body.appendChild(mediaModalEl);
    const mediaModal = new bootstrap.Modal(mediaModalEl);
    const mediaGrid = document.getElementById('modalMediaGrid');
    const searchInput = document.getElementById('modalSearch');
    const typeFilter = document.getElementById('modalTypeFilter');
    const uploadForm = document.getElementById('modalUploadForm');
    const uploadInput = document.getElementById('modalUploadFile');
    const uploadBtn = document.getElementById('modalUploadBtn');
    const confirmBtn = document.getElementById('confirmSelectionBtn');
    const modalAlert = document.getElementById('modalAlert');

    // Trigger opening of media library modal
    window.openMediaLibrary = function (inputId, previewId, multi = false) {
        targetInputId = inputId;
        targetPreviewId = previewId;
        isMultiSelect = multi;
        selectedUrls = [];
        modalAlert.classList.add('d-none');
        
        // Load files
        loadLibraryFiles();
        mediaModal.show();
    };

    function loadLibraryFiles() {
        mediaGrid.innerHTML = `
            <div class="col-12 text-center py-5 text-muted">
                <div class="spinner-border text-success" role="status"></div>
                <p class="mt-2">Loading library assets...</p>
            </div>
        `;

        const query = new URLSearchParams({
            search: searchInput.value,
            type: typeFilter.value
        });

        fetch(`{{ route('vendor.media.api') }}?${query.toString()}`)
            .then(res => res.json())
            .then(data => {
                mediaGrid.innerHTML = '';
                if (data.length === 0) {
                    mediaGrid.innerHTML = `
                        <div class="col-12 text-center py-5 text-muted">
                            <iconify-icon icon="solar:gallery-bold-duotone" class="fs-40 mb-1"></iconify-icon>
                            <p class="mb-0">No matching assets found in your library.</p>
                        </div>
                    `;
                    return;
                }

                data.forEach(item => {
                    const isImg = item.file_type.startsWith('image/');
                    const cardCol = document.createElement('div');
                    cardCol.className = 'col-md-3 col-6';
                    
                    cardCol.innerHTML = `
                        <div class="card h-100 border border-slate-200 media-select-card cursor-pointer" data-url="${item.file_path}">
                            <div class="ratio ratio-16x9 bg-slate-50 d-flex align-items-center justify-content-center overflow-hidden border-bottom position-relative">
                                ${isImg ? 
                                    `<img src="${item.file_path}" class="img-fluid object-fit-contain" style="max-height: 100%;">` : 
                                    `<div class="text-center text-muted py-2"><iconify-icon icon="solar:pdf-file-bold-duotone" class="text-danger fs-36"></iconify-icon><br><span class="fs-10">PDF Doc</span></div>`
                                }
                                <div class="position-absolute top-0 end-0 p-1.5 selection-badge d-none">
                                    <span class="badge bg-success rounded-circle p-1"><iconify-icon icon="solar:check-circle-bold-duotone" class="text-white fs-16"></iconify-icon></span>
                                </div>
                            </div>
                            <div class="card-body p-2 text-truncate small fw-medium text-slate-700" title="${item.file_name}">
                                ${item.file_name}
                            </div>
                        </div>
                    `;

                    // Handle card click
                    const card = cardCol.querySelector('.media-select-card');
                    card.addEventListener('click', function () {
                        const url = this.getAttribute('data-url');
                        const badge = this.querySelector('.selection-badge');
                        
                        if (isMultiSelect) {
                            if (selectedUrls.includes(url)) {
                                selectedUrls = selectedUrls.filter(u => u !== url);
                                badge.classList.add('d-none');
                                this.classList.remove('border-success');
                            } else {
                                selectedUrls.push(url);
                                badge.classList.remove('d-none');
                                this.classList.add('border-success');
                            }
                        } else {
                            // Single select
                            document.querySelectorAll('.media-select-card').forEach(c => {
                                c.classList.remove('border-success');
                                c.querySelector('.selection-badge').classList.add('d-none');
                            });
                            selectedUrls = [url];
                            badge.classList.remove('d-none');
                            this.classList.add('border-success');
                        }
                    });

                    mediaGrid.appendChild(cardCol);
                });
            })
            .catch(err => {
                mediaGrid.innerHTML = `<div class="col-12 text-center py-5 text-danger">Failed to load media files.</div>`;
            });
    }

    // Modal Filters
    searchInput.addEventListener('input', debounce(loadLibraryFiles, 300));
    typeFilter.addEventListener('change', loadLibraryFiles);

    // Modal Upload
    uploadForm.addEventListener('submit', function (e) {
        e.preventDefault();
        if (!uploadInput.files[0]) return;

        uploadBtn.disabled = true;
        uploadBtn.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';
        modalAlert.classList.add('d-none');

        const formData = new FormData();
        formData.append('file', uploadInput.files[0]);
        formData.append('_token', '{{ csrf_token() }}');

        fetch(`{{ route('vendor.media.store') }}`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(res => {
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<iconify-icon icon="solar:upload-bold-duotone" class="align-middle me-0.5"></iconify-icon> Upload';
            uploadInput.value = '';
            
            if (res.status) {
                // Reload files and auto-select
                loadLibraryFiles();
            } else {
                modalAlert.textContent = res.message || 'Upload failed.';
                modalAlert.classList.remove('d-none');
            }
        })
        .catch(err => {
            uploadBtn.disabled = false;
            uploadBtn.innerHTML = '<iconify-icon icon="solar:upload-bold-duotone" class="align-middle me-0.5"></iconify-icon> Upload';
            modalAlert.textContent = 'External microservice upload error.';
            modalAlert.classList.remove('d-none');
        });
    });

    // Confirm Selection Click
    confirmBtn.addEventListener('click', function () {
        if (selectedUrls.length === 0) {
            mediaModal.hide();
            return;
        }

        const input = document.getElementById(targetInputId);
        const preview = document.getElementById(targetPreviewId);

        if (isMultiSelect) {
            // Append to gallery grid container
            selectedUrls.forEach(url => {
                addGalleryImageItem(targetInputId, targetPreviewId, url);
            });
        } else {
            // Set single URL value
            input.value = selectedUrls[0];
            if (preview) {
                preview.src = selectedUrls[0];
                preview.classList.remove('d-none');
            }
            input.dispatchEvent(new Event('input', { bubbles: true }));
            input.dispatchEvent(new Event('change', { bubbles: true }));
        }

        mediaModal.hide();
    });

    // Debounce Helper
    function debounce(func, wait) {
        let timeout;
        return function (...args) {
            clearTimeout(timeout);
            timeout = setTimeout(() => func.apply(this, args), wait);
        };
    }
});
</script>
