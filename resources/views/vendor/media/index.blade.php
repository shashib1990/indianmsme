@extends('layouts.vendor')

@section('title', 'Media Library')
@section('page-title', 'Media Library')

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">Media Library</h4>
                            <p class="text-muted small mb-0">Upload and manage product photos, brochures, technical sheets, and other catalog assets.</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <form action="{{ route('vendor.media.store') }}" method="POST" enctype="multipart/form-data" id="quickUploadForm" class="d-inline-block">
                                @csrf
                                <div class="input-group">
                                    <input type="file" name="file" class="form-control" id="uploadFileSelect" required>
                                    <button class="btn btn-success" type="submit" id="uploadSubmitBtn">
                                        <iconify-icon icon="solar:upload-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon>
                                        Upload Asset
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="row mb-3 g-2">
        <div class="col-md-9">
            <form id="filterForm" action="{{ route('vendor.media.index') }}" method="GET" class="d-flex flex-wrap gap-2">
                <input type="hidden" name="sort" id="formSort" value="{{ $sort }}">
                <input type="hidden" name="order" id="formOrder" value="{{ $order }}">
                <div class="input-group" style="max-width: 250px;">
                    <span class="input-group-text bg-white border-end-0"><iconify-icon icon="solar:magnifer-bold-duotone" class="text-muted"></iconify-icon></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search file name..." value="{{ $search }}">
                </div>
                <select name="type" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="">All Types</option>
                    <option value="image" {{ $type === 'image' ? 'selected' : '' }}>Images</option>
                    <option value="document" {{ $type === 'document' ? 'selected' : '' }}>Documents</option>
                </select>
                <select name="limit" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="10" {{ $limit == 10 ? 'selected' : '' }}>10 per page</option>
                    <option value="15" {{ $limit == 15 ? 'selected' : '' }}>15 per page</option>
                    <option value="25" {{ $limit == 25 ? 'selected' : '' }}>25 per page</option>
                    <option value="50" {{ $limit == 50 ? 'selected' : '' }}>50 per page</option>
                    <option value="100" {{ $limit == 100 ? 'selected' : '' }}>100 per page</option>
                </select>
                <button type="submit" class="btn btn-outline-secondary">Filter</button>
            </form>
        </div>
        <div class="col-md-3 text-md-end">
            <button class="btn btn-outline-danger" id="bulkDeleteBtn" style="display: none;">
                <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="align-middle me-1"></iconify-icon>
                Delete Selected (<span id="selectedCount">0</span>)
            </button>
        </div>
    </div>



    <!-- Media Table -->
    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0 text-nowrap align-middle">
                    <thead class="bg-light bg-opacity-50 border-top border-bottom">
                        <tr>
                            <th class="ps-3" style="width: 40px;">
                                <div class="form-check fs-15">
                                    <input type="checkbox" class="form-check-input" id="selectAllMediaCheckbox">
                                </div>
                            </th>
                            <th class="ps-2" style="width: 60px;">#</th>
                            <th style="width: 80px;">Preview</th>
                            <x-admin.table-header field="file_name" :sort="$sort" :order="$order">File Name</x-admin.table-header>
                            <th>File Type</th>
                            <x-admin.table-header field="file_size" :sort="$sort" :order="$order">File Size</x-admin.table-header>
                            <x-admin.table-header field="created_at" :sort="$sort" :order="$order">Uploaded At</x-admin.table-header>
                            <th class="text-end pe-3" style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($media as $item)
                            @php
                                $isImage = str_starts_with($item->file_type, 'image/');
                                $sizeMb = round($item->file_size / (1024 * 1024), 2);
                            @endphp
                            <tr>
                                <td class="ps-3">
                                    <div class="form-check fs-15">
                                        <input type="checkbox" class="form-check-input media-checkbox" value="{{ $item->id }}">
                                    </div>
                                </td>
                                <td class="ps-2 text-muted small">{{ $loop->iteration + (($media->currentPage() - 1) * $media->perPage()) }}</td>
                                <td>
                                    <div class="rounded border bg-light d-flex align-items-center justify-content-center overflow-hidden" style="width: 44px; height: 44px;">
                                        @if($isImage)
                                            <img src="{{ $item->file_path }}" class="img-fluid object-fit-contain" style="max-height: 100%; max-width: 100%;">
                                        @else
                                            <iconify-icon icon="{{ $item->file_type === 'application/pdf' ? 'solar:pdf-file-bold-duotone' : 'solar:document-text-bold-duotone' }}" class="text-danger fs-24"></iconify-icon>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="fw-semibold text-slate-800" title="{{ $item->file_name }}">{{ \Illuminate\Support\Str::limit($item->file_name, 40) }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-slate-600 border">{{ strtoupper(explode('/', $item->file_type)[1] ?? $item->file_type) }}</span>
                                </td>
                                <td>{{ $sizeMb }} MB</td>
                                <td class="text-muted small">{{ $item->created_at->format('d M Y h:i A') }}</td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1">
                                        <button class="btn btn-sm btn-outline-success d-flex align-items-center justify-content-center p-1" style="width: 28px; height: 28px;" onclick="copyLink('{{ $item->file_path }}')" title="Copy URL">
                                            <iconify-icon icon="solar:copy-bold-duotone" class="fs-14"></iconify-icon>
                                        </button>
                                        <a href="{{ $item->file_path }}" target="_blank" class="btn btn-sm btn-outline-info d-flex align-items-center justify-content-center p-1" style="width: 28px; height: 28px;" title="View Original">
                                            <iconify-icon icon="solar:eye-bold-duotone" class="fs-14"></iconify-icon>
                                        </a>
                                        <form action="{{ route('vendor.media.destroy', $item->id) }}" method="POST" class="d-inline delete-media-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center p-1" style="width: 28px; height: 28px;" title="Delete">
                                                <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="fs-14"></iconify-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <iconify-icon icon="solar:gallery-bold-duotone" class="fs-48 mb-2"></iconify-icon>
                                    <h5>No Assets Found</h5>
                                    <p class="mb-0">Your media library is empty. Upload image or document files above.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($media->total() > 0)
                <div class="d-flex justify-content-between align-items-center p-3 border-top">
                    <span class="text-muted small">Showing {{ $media->firstItem() }} to {{ $media->lastItem() }} of {{ $media->total() }} files</span>
                    @if($media->hasPages())
                        {{ $media->links() }}
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
function copyLink(url) {
    navigator.clipboard.writeText(url).then(() => {
        Swal.fire({
            title: 'Copied!',
            text: 'Asset URL copied to clipboard.',
            icon: 'success',
            toast: true,
            position: 'top-end',
            showConfirmButton: false,
            timer: 2000
        });
    });
}

document.addEventListener('DOMContentLoaded', function () {
    // Show spinner on upload submit
    const uploadForm = document.getElementById('quickUploadForm');
    const submitBtn = document.getElementById('uploadSubmitBtn');
    if (uploadForm) {
        uploadForm.addEventListener('submit', function () {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Uploading...';
        });
    }

    // Media selection check logic
    const checkboxes = document.querySelectorAll('.media-checkbox');
    const selectAll = document.getElementById('selectAllMediaCheckbox');
    const bulkDeleteBtn = document.getElementById('bulkDeleteBtn');
    const selectedCount = document.getElementById('selectedCount');

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            updateBulkButton();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            if (selectAll) {
                const checkedCount = document.querySelectorAll('.media-checkbox:checked').length;
                selectAll.checked = checkedCount === checkboxes.length;
            }
            updateBulkButton();
        });
    });

    function updateBulkButton() {
        const checked = document.querySelectorAll('.media-checkbox:checked');
        selectedCount.textContent = checked.length;
        if (checked.length > 0) {
            bulkDeleteBtn.style.display = 'inline-block';
        } else {
            bulkDeleteBtn.style.display = 'none';
        }
    }

    if (bulkDeleteBtn) {
        bulkDeleteBtn.addEventListener('click', function () {
            const checked = document.querySelectorAll('.media-checkbox:checked');
            const ids = Array.from(checked).map(cb => cb.value);

            Swal.fire({
                title: 'Delete selected assets?',
                text: `Are you sure you want to delete the ${ids.length} selected files? This cannot be undone.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Delete Them'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.showLoading();
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = "{{ route('vendor.media.bulk-delete') }}";

                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    form.appendChild(csrfInput);

                    ids.forEach(id => {
                        const idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'ids[]';
                        idInput.value = id;
                        form.appendChild(idInput);
                    });

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        });
    }

    // Individual delete confirmation
    const deleteForms = document.querySelectorAll('.delete-media-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Delete this asset?',
                text: 'Are you sure you want to remove this file from your library?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Delete'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});
</script>
@endpush
