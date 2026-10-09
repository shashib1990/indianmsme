@extends('layouts.admin')

@section('title', 'Category Management')
@section('page-title', 'Category Management')

@section('content')
    <!-- Common Page Action Header -->
    <x-admin.page-header 
        title="Category Management" 
        subtitle="Manage product categories, upload images, and configure active categories."
        :actions="[
            [
                'label' => 'Add Category',
                'icon' => 'solar:add-circle-bold-duotone',
                'target' => '#createModal',
                'permission' => 'categories.create',
                'btnClass' => 'btn-primary'
            ],
            [
                'label' => 'Bulk Upload',
                'icon' => 'solar:upload-bold-duotone',
                'target' => '#bulkImportModal',
                'permission' => 'categories.create',
                'btnClass' => 'btn-outline-primary'
            ],
            [
                'label' => 'Export CSV',
                'icon' => 'solar:document-text-bold-duotone',
                'onclick' => 'triggerExport(\'csv\')',
                'permission' => 'categories.view',
                'btnClass' => 'btn-outline-success'
            ]
        ]"
    />

    <!-- Reusable Table Filters -->
                <x-admin.table-filters 
                    action="{{ route('admin.categories.index') }}" 
                    :search="$search" 
                    placeholder="Search category..." 
                    :limit="$limit" 
                    :sort="$sort" 
                    :order="$order"
                    :bulk-actions="[
                        'delete' => 'Delete Selected',
                        'status_active' => 'Activate Selected',
                        'status_inactive' => 'Deactivate Selected'
                    ]"
                >
                    {{-- Status Filter --}}
                    <select name="status" id="filterStatus" class="form-select form-select-sm w-auto" style="min-width:140px" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="1" {{ $status === '1' ? 'selected' : '' }}>Active</option>
                        <option value="0" {{ $status === '0' ? 'selected' : '' }}>Inactive</option>
                    </select>
                </x-admin.table-filters>

<div class="row mt-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0 text-nowrap align-middle">
                            <thead class="bg-light bg-opacity-50 border-top border-bottom">
                                <tr>
                                    <th class="ps-3" style="width: 40px;">
                                        <div class="form-check fs-15">
                                            <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                                        </div>
                                    </th>
                                    <th class="ps-2" style="width: 60px;">#</th>
                                    <th style="width: 80px;">Image</th>
                                    <x-admin.table-header field="name" :sort="$sort" :order="$order">Category Name</x-admin.table-header>
                                    <x-admin.table-header field="slug" :sort="$sort" :order="$order">Slug</x-admin.table-header>
                                    <th>Sub Categories</th>
                                    <x-admin.table-header field="status" :sort="$sort" :order="$order">Status</x-admin.table-header>
                                    <x-admin.table-header field="created_at" :sort="$sort" :order="$order">Created At</x-admin.table-header>
                                    <th class="text-end pe-3" style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($categories as $category)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="form-check fs-15">
                                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $category->id }}">
                                            </div>
                                        </td>
                                        <td class="ps-2 text-muted small">{{ $loop->iteration + (($categories->currentPage() - 1) * $categories->perPage()) }}</td>
                                        <td>
                                            @if($category->image)
                                                <img src="{{ asset($category->image) }}" class="rounded border" style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="rounded border bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <iconify-icon icon="solar:gallery-bold-duotone" class="text-muted fs-20"></iconify-icon>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="fw-semibold text-slate-800">{{ $category->name }}</td>
                                        <td><code>{{ $category->slug }}</code></td>
                                        <td>
                                            <a href="{{ route('admin.sub-categories.index', ['category_id' => $category->id]) }}" class="text-decoration-none">
                                                <span class="badge bg-primary-subtle text-primary px-2 py-1 fs-12 d-inline-flex align-items-center gap-1">
                                                    <iconify-icon icon="solar:list-bold-duotone" class="fs-12"></iconify-icon>
                                                    {{ $category->sub_categories_count }} Subs
                                                </span>
                                            </a>
                                        </td>
                                        <td>
                                            @if($category->status === '1')
                                                <span class="badge bg-success-subtle text-success px-2 py-1 fs-12">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-12">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">{{ $category->created_at->format('d M Y') }}</td>
                                        <td class="text-end pe-3">
                                            <div class="d-inline-flex gap-1">
                                                {{-- View Button --}}
                                                <button class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center p-1" 
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#viewModal"
                                                    data-name="{{ $category->name }}"
                                                    data-slug="{{ $category->slug }}"
                                                    data-image="{{ $category->image ? asset($category->image) : '' }}"
                                                    data-status="{{ $category->status === '1' ? 'Active' : 'Inactive' }}"
                                                    data-creator="{{ $category->creator ? $category->creator->name : 'System' }}"
                                                    data-updater="{{ $category->updater ? $category->updater->name : '—' }}"
                                                    data-created="{{ $category->created_at->format('d M Y, h:i A') }}"
                                                    data-updated="{{ $category->updated_at->format('d M Y, h:i A') }}"
                                                    style="width: 28px; height: 28px;" title="View Details">
                                                    <iconify-icon icon="solar:eye-bold-duotone" class="fs-14"></iconify-icon>
                                                </button>
                                                
                                                {{-- Edit Button --}}
                                                @permission('categories.edit')
                                                <button class="btn btn-sm btn-outline-info d-flex align-items-center justify-content-center p-1" 
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal"
                                                    data-id="{{ $category->id }}"
                                                    data-name="{{ $category->name }}"
                                                    data-slug="{{ $category->slug }}"
                                                    data-image="{{ $category->image ? asset($category->image) : '' }}"
                                                    data-status="{{ $category->status }}"
                                                    style="width: 28px; height: 28px;" title="Edit">
                                                    <iconify-icon icon="solar:pen-bold-duotone" class="fs-14"></iconify-icon>
                                                </button>
                                                @endpermission

                                                {{-- Delete Button --}}
                                                @permission('categories.delete')
                                                <form action="{{ route('admin.categories.destroy', $category->id) }}" method="POST" class="delete-form d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center p-1 confirm-delete-btn" 
                                                        style="width: 28px; height: 28px;" title="Delete">
                                                        <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="fs-14"></iconify-icon>
                                                    </button>
                                                </form>
                                                @endpermission
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="text-center py-4 text-muted">
                                            <iconify-icon icon="solar:database-fail-bold-duotone" class="fs-40 mb-2"></iconify-icon>
                                            <p class="mb-0">No categories found matching your criteria.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($categories->hasPages())
                        <div class="d-flex justify-content-between align-items-center p-3 border-top">
                            <span class="text-muted small">Showing {{ $categories->firstItem() }} to {{ $categories->lastItem() }} of {{ $categories->total() }} results</span>
                            {{ $categories->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.categories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Category Name</label>
                            <input type="text" name="name" id="createName" class="form-control" placeholder="e.g. Electronics" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category Slug <span class="text-muted small">(Auto-generated if left blank)</span></label>
                            <input type="text" name="slug" id="createSlug" class="form-control" placeholder="e.g. electronics">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(this, 'createImagePreview')">
                            <img id="createImagePreview" src="" class="img-thumbnail mt-2 d-none" style="max-height: 120px;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Create Category</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="editForm" method="POST" enctype="multipart/form-data" data-ajax-form="true">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Category Name</label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category Slug</label>
                            <input type="text" name="slug" id="editSlug" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Category Image</label>
                            <input type="file" name="image" class="form-control" accept="image/*" onchange="previewImage(this, 'editImagePreview')">
                            <div class="mt-2">
                                <img id="editImagePreview" src="" class="img-thumbnail d-none" style="max-height: 120px;">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Status</label>
                            <select name="status" id="editStatus" class="form-select" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Category</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">Category Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <img id="viewImage" src="" class="img-thumbnail rounded-circle bg-light d-none" style="width: 100px; height: 100px; object-fit: cover;">
                        <div id="viewImagePlaceholder" class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center border" style="width: 100px; height: 100px;">
                            <iconify-icon icon="solar:gallery-bold-duotone" class="text-muted fs-40"></iconify-icon>
                        </div>
                        <h4 class="mt-2 mb-0" id="viewName">Category Name</h4>
                        <code class="text-muted" id="viewSlug">category-slug</code>
                    </div>
                    <table class="table table-sm table-bordered mt-3">
                        <tr>
                            <th class="bg-light" style="width: 40%;">Status</th>
                            <td id="viewStatus">Active</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Created By</th>
                            <td id="viewCreator">System</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Created At</th>
                            <td id="viewCreated">Date</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Updated By</th>
                            <td id="viewUpdater">—</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Updated At</th>
                            <td id="viewUpdated">Date</td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Import Modal -->
    <div class="modal fade" id="bulkImportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold text-slate-800">
                        <iconify-icon icon="solar:upload-bold-duotone" class="me-2 text-primary"></iconify-icon>
                        Bulk Category Upload
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.categories.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Step 1 — Upload Excel / CSV <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <small class="text-muted">Required columns: <code>name</code>, <code>slug</code> (optional), <code>status</code> (1/0) — duplicates are skipped.</small>
                        </div>

                        <div class="alert alert-info py-2 mb-0 small d-flex align-items-center justify-content-between">
                            <span><strong>Step 2 —</strong> Use the standard category template.</span>
                            <a href="{{ route('admin.categories.sample') }}" class="fw-medium d-inline-flex align-items-center gap-1 ms-2">
                                <iconify-icon icon="solar:download-bold-duotone" class="fs-14"></iconify-icon>Download Sample
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="importBtn">
                            <iconify-icon icon="solar:upload-bold-duotone" class="me-1"></iconify-icon> Import Categories
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Import Result Toast & Modal -->
    @if(session()->has('import_result'))
        @php $r = session('import_result'); @endphp
        @if($r['failed'] > 0 || $r['skipped'] > 0)
        <!-- Details Modal -->
        <div class="modal fade" id="importReportModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning bg-opacity-10">
                        <h5 class="modal-title text-warning-emphasis d-flex align-items-center gap-1">
                            <iconify-icon icon="solar:danger-bold-duotone"></iconify-icon>
                            Import Report: {{ $r['inserted'] }} Imported, {{ $r['skipped'] }} Skipped, {{ $r['failed'] }} Failed
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="bg-light p-3 rounded mb-3">
                            <div class="row text-center">
                                <div class="col-3 border-end">
                                    <div class="text-muted small">Total Rows</div>
                                    <div class="fs-18 fw-bold text-dark">{{ $r['total'] }}</div>
                                </div>
                                <div class="col-3 border-end">
                                    <div class="text-muted small">Inserted</div>
                                    <div class="fs-18 fw-bold text-success">{{ $r['inserted'] }}</div>
                                </div>
                                <div class="col-3 border-end">
                                    <div class="text-muted small">Skipped</div>
                                    <div class="fs-18 fw-bold text-warning">{{ $r['skipped'] }}</div>
                                </div>
                                <div class="col-3">
                                    <div class="p-2 border rounded bg-white">
                                        <div class="text-muted small">Failed</div>
                                        <div class="fs-18 fw-bold text-danger">{{ $r['failed'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-hover table-centered mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3" style="width: 80px;">Row #</th>
                                        <th style="width: 150px;">Category Name</th>
                                        <th style="width: 100px;">Status</th>
                                        <th class="pe-3">Reason / Error</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($r['details'] as $det)
                                        @if($det['status'] === 'Skipped' || $det['status'] === 'Failed')
                                            <tr>
                                                <td class="ps-3 fw-semibold">{{ $det['row'] }}</td>
                                                <td><code class="text-dark">{{ $det['data'] }}</code></td>
                                                <td>
                                                    @if($det['status'] === 'Failed')
                                                        <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1">Failed</span>
                                                    @else
                                                        <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1">Skipped</span>
                                                    @endif
                                                </td>
                                                <td class="pe-3 text-muted text-wrap">{{ $det['reason'] }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer d-flex align-items-center justify-content-between">
                        <div>
                            <button type="button" class="btn btn-outline-danger btn-sm fw-bold me-1" onclick="exportModalTableToExcel('importReportModal', 'Category_Import_Skipped_Failed_Records.xlsx', true)">
                                <iconify-icon icon="solar:download-square-linear" class="fs-16 align-middle me-1"></iconify-icon> Export Skipped &amp; Failed (.xlsx)
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm fw-bold" onclick="exportModalTableToExcel('importReportModal', 'Category_Import_All_Records.xlsx', false)">
                                <iconify-icon icon="solar:download-square-linear" class="fs-16 align-middle me-1"></iconify-icon> Export All Logs (.xlsx)
                            </button>
                        </div>
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var myModal = new bootstrap.Modal(document.getElementById('importReportModal'));
                myModal.show();
            });
        </script>
        @endif
    @endif
@endsection

@push('scripts')
<script>
var categoryUpdateBaseUrl = '{{ parse_url(route("admin.categories.update", 0), PHP_URL_PATH) }}'.replace('/0', '/');

function previewImage(input, previewId) {
    var preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = "";
        preview.classList.add('d-none');
    }
}

document.addEventListener('DOMContentLoaded', function () {
    // Generate slug from name dynamically
    var createName = document.getElementById('createName');
    var createSlug = document.getElementById('createSlug');
    if (createName && createSlug) {
        createName.addEventListener('input', function() {
            createSlug.value = createName.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
        });
    }

    var editName = document.getElementById('editName');
    var editSlug = document.getElementById('editSlug');
    if (editName && editSlug) {
        editName.addEventListener('input', function() {
            editSlug.value = editName.value.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)+/g, '');
        });
    }

    // Edit modal config
    var editModalEl = document.getElementById('editModal');
    if (editModalEl) {
        editModalEl.addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            document.getElementById('editForm').action = categoryUpdateBaseUrl + btn.dataset.id;
            document.getElementById('editName').value = btn.dataset.name;
            document.getElementById('editSlug').value = btn.dataset.slug;
            document.getElementById('editStatus').value = btn.dataset.status;
            
            var imgPreview = document.getElementById('editImagePreview');
            if (btn.dataset.image) {
                imgPreview.src = btn.dataset.image;
                imgPreview.classList.remove('d-none');
            } else {
                imgPreview.src = '';
                imgPreview.classList.add('d-none');
            }
        });
    }

    // View modal config
    var viewModalEl = document.getElementById('viewModal');
    if (viewModalEl) {
        viewModalEl.addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            document.getElementById('viewName').innerText = btn.dataset.name;
            document.getElementById('viewSlug').innerText = btn.dataset.slug;
            document.getElementById('viewStatus').innerText = btn.dataset.status;
            document.getElementById('viewCreator').innerText = btn.dataset.creator;
            document.getElementById('viewCreated').innerText = btn.dataset.created;
            document.getElementById('viewUpdater').innerText = btn.dataset.updater;
            document.getElementById('viewUpdated').innerText = btn.dataset.updated;

            var viewImg = document.getElementById('viewImage');
            var placeholder = document.getElementById('viewImagePlaceholder');
            if (btn.dataset.image) {
                viewImg.src = btn.dataset.image;
                viewImg.classList.remove('d-none');
                placeholder.classList.add('d-none');
            } else {
                viewImg.src = '';
                viewImg.classList.add('d-none');
                placeholder.classList.remove('d-none');
            }
        });
    }

    // Confirm Delete
    var deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(function (form) {
        var btn = form.querySelector('.confirm-delete-btn');
        btn.addEventListener('click', function () {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will permanently delete the category unless it has sub-categories linked.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success !== false) {
                            if (typeof Toastify === 'function') {
                                Toastify({ text: data.message || 'Category deleted', duration: 3000, style: { background: '#10b981' } }).showToast();
                            }
                            var tr = form.closest('tr');
                            if (tr) {
                                tr.remove();
                            } else {
                                reloadTablePartial();
                            }
                        } else {
                            Swal.fire('Error', data.message || 'Could not delete category', 'error');
                        }
                    })
                    .catch(err => {
                        console.error('Delete error:', err);
                        form.submit();
                    });
                }
            });
        });
    });

    // Import loader
    var importForm = document.getElementById('importForm');
    if (importForm) {
        importForm.addEventListener('submit', function () {
            var btn = document.getElementById('importBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Importing...';
        });
    }
});

// Trigger bulk export
function triggerExport(format) {
    var selectedIds = [];
    document.querySelectorAll('.row-checkbox:checked').forEach(function (chk) {
        selectedIds.push(chk.value);
    });

    var url = new URL(window.location.href);
    url.searchParams.set('export', '1');
    if (selectedIds.length > 0) {
        url.searchParams.set('ids', selectedIds.join(','));
    } else {
        url.searchParams.delete('ids');
    }
    window.location.href = url.toString();
}
</script>
@endpush
