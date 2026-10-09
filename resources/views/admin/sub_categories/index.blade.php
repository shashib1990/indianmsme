@extends('layouts.admin')

@section('title', 'Sub Category Management')
@section('page-title', 'Sub Category Management')

@section('content')
    <!-- Common Page Action Header -->
    <x-admin.page-header 
        title="Sub Category Management" 
        subtitle="Manage product sub-categories, map them to parent categories, and upload templates."
        :actions="[
            [
                'label' => 'Add Sub Category',
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
                    action="{{ route('admin.sub-categories.index') }}" 
                    :search="$search" 
                    placeholder="Search subcategory..." 
                    :limit="$limit" 
                    :sort="$sort" 
                    :order="$order"
                    :bulk-actions="[
                        'delete' => 'Delete Selected',
                        'status_active' => 'Activate Selected',
                        'status_inactive' => 'Deactivate Selected'
                    ]"
                >
                    {{-- Category Filter --}}
                    <select name="category_id" id="filterCategoryId" class="form-select form-select-sm w-auto" style="min-width:140px" onchange="this.form.submit()">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
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
                                    <x-admin.table-header field="category_id" :sort="$sort" :order="$order">Parent Category</x-admin.table-header>
                                    <x-admin.table-header field="name" :sort="$sort" :order="$order">Sub Category Name</x-admin.table-header>
                                    <x-admin.table-header field="slug" :sort="$sort" :order="$order">Slug</x-admin.table-header>
                                    <th>Child Categories</th>
                                    <x-admin.table-header field="status" :sort="$sort" :order="$order">Status</x-admin.table-header>
                                    <x-admin.table-header field="created_at" :sort="$sort" :order="$order">Created At</x-admin.table-header>
                                    <th class="text-end pe-3" style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($subcategories as $sub)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="form-check fs-15">
                                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $sub->id }}">
                                            </div>
                                        </td>
                                        <td class="ps-2 text-muted small">{{ $loop->iteration + (($subcategories->currentPage() - 1) * $subcategories->perPage()) }}</td>
                                        <td>
                                            @if($sub->image)
                                                <img src="{{ asset($sub->image) }}" class="rounded border" style="width: 40px; height: 40px; object-fit: cover;">
                                            @else
                                                <div class="rounded border bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <iconify-icon icon="solar:gallery-bold-duotone" class="text-muted fs-20"></iconify-icon>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary px-2 py-1 fs-12">
                                                {{ $sub->category->name ?? '—' }}
                                            </span>
                                        </td>
                                        <td class="fw-semibold text-slate-800">{{ $sub->name }}</td>
                                        <td><code>{{ $sub->slug }}</code></td>
                                        <td>
                                            <a href="{{ route('admin.child-categories.index', ['category_id' => $sub->category_id, 'sub_category_id' => $sub->id]) }}" class="text-decoration-none">
                                                <span class="badge bg-primary-subtle text-primary px-2 py-1 fs-12 d-inline-flex align-items-center gap-1">
                                                    <iconify-icon icon="solar:list-bold-duotone" class="fs-12"></iconify-icon>
                                                    {{ $sub->child_categories_count }} Childs
                                                </span>
                                            </a>
                                        </td>
                                        <td>
                                            @if($sub->status === '1')
                                                <span class="badge bg-success-subtle text-success px-2 py-1 fs-12">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-12">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">{{ $sub->created_at->format('d M Y') }}</td>
                                        <td class="text-end pe-3">
                                            <div class="d-inline-flex gap-1">
                                                {{-- View Button --}}
                                                <button class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center p-1" 
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#viewModal"
                                                    data-category="{{ $sub->category->name ?? '—' }}"
                                                    data-name="{{ $sub->name }}"
                                                    data-slug="{{ $sub->slug }}"
                                                    data-image="{{ $sub->image ? asset($sub->image) : '' }}"
                                                    data-status="{{ $sub->status === '1' ? 'Active' : 'Inactive' }}"
                                                    data-creator="{{ $sub->creator ? $sub->creator->name : 'System' }}"
                                                    data-updater="{{ $sub->updater ? $sub->updater->name : '—' }}"
                                                    data-created="{{ $sub->created_at->format('d M Y, h:i A') }}"
                                                    data-updated="{{ $sub->updated_at->format('d M Y, h:i A') }}"
                                                    style="width: 28px; height: 28px;" title="View Details">
                                                    <iconify-icon icon="solar:eye-bold-duotone" class="fs-14"></iconify-icon>
                                                </button>
                                                
                                                {{-- Edit Button --}}
                                                @permission('categories.edit')
                                                <button class="btn btn-sm btn-outline-info d-flex align-items-center justify-content-center p-1" 
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal"
                                                    data-id="{{ $sub->id }}"
                                                    data-category-id="{{ $sub->category_id }}"
                                                    data-name="{{ $sub->name }}"
                                                    data-slug="{{ $sub->slug }}"
                                                    data-image="{{ $sub->image ? asset($sub->image) : '' }}"
                                                    data-status="{{ $sub->status }}"
                                                    style="width: 28px; height: 28px;" title="Edit">
                                                    <iconify-icon icon="solar:pen-bold-duotone" class="fs-14"></iconify-icon>
                                                </button>
                                                @endpermission

                                                {{-- Delete Button --}}
                                                @permission('categories.delete')
                                                <form action="{{ route('admin.sub-categories.destroy', $sub->id) }}" method="POST" class="delete-form d-inline">
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
                                            <p class="mb-0">No sub-categories found matching your criteria.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($subcategories->hasPages())
                        <div class="d-flex justify-content-between align-items-center p-3 border-top">
                            <span class="text-muted small">Showing {{ $subcategories->firstItem() }} to {{ $subcategories->lastItem() }} of {{ $subcategories->total() }} results</span>
                            {{ $subcategories->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.sub-categories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Sub Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Select Parent Category</label>
                            <select name="category_id" id="createCategoryId" class="form-select s-select" required>
                                <option value="">Choose Category...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Sub Category Name</label>
                            <input type="text" name="name" id="createName" class="form-control" placeholder="e.g. Mobile Phones" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sub Category Slug <span class="text-muted small">(Auto-generated if left blank)</span></label>
                            <input type="text" name="slug" id="createSlug" class="form-control" placeholder="e.g. mobile-phones">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sub Category Image</label>
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
                        <button type="submit" class="btn btn-primary">Create Sub Category</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="editForm" method="POST" enctype="multipart/form-data">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Sub Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Select Parent Category</label>
                            <select name="category_id" id="editCategoryId" class="form-select" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Sub Category Name</label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sub Category Slug</label>
                            <input type="text" name="slug" id="editSlug" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Sub Category Image</label>
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
                        <button type="submit" class="btn btn-primary">Update Sub Category</button>
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
                    <h5 class="modal-title">Sub Category Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <img id="viewImage" src="" class="img-thumbnail rounded-circle mb-2 d-none" style="width: 80px; height: 80px; object-fit: cover;">
                        <div>
                            <span class="badge bg-secondary-subtle text-secondary px-3 py-1 fs-13" id="viewCategory">Parent Category</span>
                        </div>
                        <h4 class="mt-2 mb-0" id="viewName">Sub Category Name</h4>
                        <code class="text-muted" id="viewSlug">sub-category-slug</code>
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
                        Bulk Sub Category Upload
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.sub-categories.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf
                    <div class="modal-body">


                        {{-- Step 1: File --}}
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Step 1 &mdash; Upload Excel / CSV <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <small class="text-muted">Required columns: <code>Category Name</code>, <code>Sub Category Name</code>, <code>Slug</code> (optional).</small>
                        </div>

                        {{-- Step 2: Sample --}}
                        <div class="alert alert-info py-2 mb-0 small d-flex align-items-center justify-content-between">
                            <span><strong>Step 2 &mdash;</strong> Use the standard sub-category template.</span>
                            <a href="{{ route('admin.sub-categories.sample') }}" class="fw-medium d-inline-flex align-items-center gap-1 ms-2">
                                <iconify-icon icon="solar:download-bold-duotone" class="fs-14"></iconify-icon>Download Sample
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="importBtn">
                            <iconify-icon icon="solar:upload-bold-duotone" class="me-1"></iconify-icon> Import Sub Categories
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
                                        <th style="width: 150px;">Sub Category Name</th>
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
                            <button type="button" class="btn btn-outline-danger btn-sm fw-bold me-1" onclick="exportModalTableToExcel('importReportModal', 'SubCategory_Import_Skipped_Failed_Records.xlsx', true)">
                                <iconify-icon icon="solar:download-square-linear" class="fs-16 align-middle me-1"></iconify-icon> Export Skipped &amp; Failed (.xlsx)
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm fw-bold" onclick="exportModalTableToExcel('importReportModal', 'SubCategory_Import_All_Records.xlsx', false)">
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
var subCategoryUpdateBaseUrl = '{{ parse_url(route("admin.sub-categories.update", 0), PHP_URL_PATH) }}'.replace('/0', '/');

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

function previewImage(input, previewId) {
    const preview = document.getElementById(previewId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            preview.src = e.target.result;
            preview.classList.remove('d-none');
        }
        reader.readAsDataURL(input.files[0]);
    } else {
        preview.src = '';
        preview.classList.add('d-none');
    }
}

    // Edit modal config
    var editModalEl = document.getElementById('editModal');
    if (editModalEl) {
        editModalEl.addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            document.getElementById('editForm').action = subCategoryUpdateBaseUrl + btn.dataset.id;
            document.getElementById('editName').value = btn.dataset.name;
            document.getElementById('editSlug').value = btn.dataset.slug;
            document.getElementById('editStatus').value = btn.dataset.status;
            document.getElementById('editCategoryId').value = btn.dataset.categoryId;

            var editImgPreview = document.getElementById('editImagePreview');
            if (btn.dataset.image) {
                editImgPreview.src = btn.dataset.image;
                editImgPreview.classList.remove('d-none');
            } else {
                editImgPreview.src = '';
                editImgPreview.classList.add('d-none');
            }
        });
    }

    // View modal config
    var viewModalEl = document.getElementById('viewModal');
    if (viewModalEl) {
        viewModalEl.addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            document.getElementById('viewCategory').innerText = btn.dataset.category;
            document.getElementById('viewName').innerText = btn.dataset.name;
            document.getElementById('viewSlug').innerText = btn.dataset.slug;
            document.getElementById('viewStatus').innerText = btn.dataset.status;
            document.getElementById('viewCreator').innerText = btn.dataset.creator;
            document.getElementById('viewCreated').innerText = btn.dataset.created;
            document.getElementById('viewUpdater').innerText = btn.dataset.updater;
            document.getElementById('viewUpdated').innerText = btn.dataset.updated;

            var viewImg = document.getElementById('viewImage');
            if (btn.dataset.image) {
                viewImg.src = btn.dataset.image;
                viewImg.classList.remove('d-none');
            } else {
                viewImg.src = '';
                viewImg.classList.add('d-none');
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
                text: 'This will permanently delete the sub-category unless it has child-categories linked.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
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
