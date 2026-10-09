@extends('layouts.admin')

@section('title', 'Child Category Management')
@section('page-title', 'Child Category Management')

@section('content')
    <!-- Common Page Action Header -->
    <x-admin.page-header 
        title="Child Category Management" 
        subtitle="Manage product child-categories, map them to parent sub-categories, and upload templates."
        :actions="[
            [
                'label' => 'Add Child Category',
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
                    action="{{ route('admin.child-categories.index') }}" 
                    :search="$search" 
                    placeholder="Search child category..." 
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
                    <select name="category_id" id="filterCategoryId" class="form-select form-select-sm w-auto flex-shrink-0" style="min-width:160px">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>
                                {{ $cat->name }}
                            </option>
                        @endforeach
                    </select>

                    {{-- Sub Category Filter --}}
                    <select name="sub_category_id" id="filterSubCategoryId" class="form-select form-select-sm w-auto flex-shrink-0" style="min-width:170px">
                        <option value="">All Sub Categories</option>
                        @foreach($subcategories as $sub)
                            <option value="{{ $sub->id }}" {{ $subCategoryId == $sub->id ? 'selected' : '' }}>
                                {{ $sub->name }}
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
                                    <x-admin.table-header field="category_id" :sort="$sort" :order="$order">Category</x-admin.table-header>
                                    <x-admin.table-header field="sub_category_id" :sort="$sort" :order="$order">Sub Category</x-admin.table-header>
                                    <x-admin.table-header field="name" :sort="$sort" :order="$order">Child Category Name</x-admin.table-header>
                                    <x-admin.table-header field="slug" :sort="$sort" :order="$order">Slug</x-admin.table-header>
                                    <x-admin.table-header field="status" :sort="$sort" :order="$order">Status</x-admin.table-header>
                                    <x-admin.table-header field="created_at" :sort="$sort" :order="$order">Created At</x-admin.table-header>
                                    <th class="text-end pe-3" style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($childcategories as $child)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="form-check fs-15">
                                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $child->id }}">
                                            </div>
                                        </td>
                                        <td class="ps-2 text-muted small">{{ $loop->iteration + (($childcategories->currentPage() - 1) * $childcategories->perPage()) }}</td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary px-2 py-1 fs-12">
                                                {{ $child->category->name ?? '—' }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info px-2 py-1 fs-12">
                                                {{ $child->subCategory->name ?? '—' }}
                                            </span>
                                        </td>
                                        <td class="fw-semibold text-slate-800">{{ $child->name }}</td>
                                        <td><code>{{ $child->slug }}</code></td>
                                        <td>
                                            @if($child->status === '1')
                                                <span class="badge bg-success-subtle text-success px-2 py-1 fs-12">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-12">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">{{ $child->created_at->format('d M Y') }}</td>
                                        <td class="text-end pe-3">
                                            <div class="d-inline-flex gap-1">
                                                {{-- View Button --}}
                                                <button class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center p-1" 
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#viewModal"
                                                    data-category="{{ $child->category->name ?? '—' }}"
                                                    data-subcategory="{{ $child->subCategory->name ?? '—' }}"
                                                    data-name="{{ $child->name }}"
                                                    data-slug="{{ $child->slug }}"
                                                    data-status="{{ $child->status === '1' ? 'Active' : 'Inactive' }}"
                                                    data-creator="{{ $child->creator ? $child->creator->name : 'System' }}"
                                                    data-updater="{{ $child->updater ? $child->updater->name : '—' }}"
                                                    data-created="{{ $child->created_at->format('d M Y, h:i A') }}"
                                                    data-updated="{{ $child->updated_at->format('d M Y, h:i A') }}"
                                                    style="width: 28px; height: 28px;" title="View Details">
                                                    <iconify-icon icon="solar:eye-bold-duotone" class="fs-14"></iconify-icon>
                                                </button>
                                                
                                                {{-- Edit Button --}}
                                                @permission('categories.edit')
                                                <button class="btn btn-sm btn-outline-info d-flex align-items-center justify-content-center p-1" 
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal"
                                                    data-id="{{ $child->id }}"
                                                    data-category-id="{{ $child->category_id }}"
                                                    data-subcategory-id="{{ $child->sub_category_id }}"
                                                    data-name="{{ $child->name }}"
                                                    data-slug="{{ $child->slug }}"
                                                    data-status="{{ $child->status }}"
                                                    style="width: 28px; height: 28px;" title="Edit">
                                                    <iconify-icon icon="solar:pen-bold-duotone" class="fs-14"></iconify-icon>
                                                </button>
                                                @endpermission

                                                {{-- Delete Button --}}
                                                @permission('categories.delete')
                                                <form action="{{ route('admin.child-categories.destroy', $child->id) }}" method="POST" class="delete-form d-inline">
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
                                            <p class="mb-0">No child categories found matching your criteria.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($childcategories->hasPages())
                        <div class="d-flex justify-content-between align-items-center p-3 border-top">
                            <span class="text-muted small">Showing {{ $childcategories->firstItem() }} to {{ $childcategories->lastItem() }} of {{ $childcategories->total() }} results</span>
                            {{ $childcategories->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.child-categories.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New Child Category</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">Select Parent Category</label>
                            <select name="category_id" id="createCategoryId" class="form-select" required>
                                <option value="">Choose Category...</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Select Sub Category</label>
                            <select name="sub_category_id" id="createSubCategoryId" class="form-select" required disabled>
                                <option value="">Choose Sub Category...</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Child Category Name</label>
                            <input type="text" name="name" id="createName" class="form-control" placeholder="e.g. iPhones" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Child Category Slug <span class="text-muted small">(Auto-generated if left blank)</span></label>
                            <input type="text" name="slug" id="createSlug" class="form-control" placeholder="e.g. iphones">
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
                        <button type="submit" class="btn btn-primary">Create Child Category</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit Child Category</h5>
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
                            <label class="form-label required">Select Sub Category</label>
                            <select name="sub_category_id" id="editSubCategoryId" class="form-select" required>
                                <!-- Will be filled dynamically -->
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Child Category Name</label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Child Category Slug</label>
                            <input type="text" name="slug" id="editSlug" class="form-control">
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
                        <button type="submit" class="btn btn-primary">Update Child Category</button>
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
                    <h5 class="modal-title">Child Category Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <div class="mb-3">
                        <div class="d-flex justify-content-center gap-1 mb-2">
                            <span class="badge bg-secondary-subtle text-secondary px-3 py-1 fs-12" id="viewCategory">Parent Category</span>
                            <span class="badge bg-info-subtle text-info px-3 py-1 fs-12" id="viewSubCategory">Sub Category</span>
                        </div>
                        <h4 class="mt-2 mb-0" id="viewName">Child Category Name</h4>
                        <code class="text-muted" id="viewSlug">child-category-slug</code>
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
                        Bulk Child Category Upload
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.child-categories.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf
                    <div class="modal-body">


                        {{-- File Upload --}}
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Step 1 &mdash; Upload Excel / CSV <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <small class="text-muted">Required columns: <code>Category Name</code>, <code>Sub Category Name</code>, <code>Child Category Name</code>.</small>
                        </div>

                        {{-- Sample download --}}
                        <div class="alert alert-info py-2 mb-0 small d-flex align-items-center justify-content-between">
                            <span><strong>Step 2 &mdash;</strong> Use the standard child-category template.</span>
                            <a href="{{ route('admin.child-categories.sample') }}" class="fw-medium d-inline-flex align-items-center gap-1 ms-2">
                                <iconify-icon icon="solar:download-bold-duotone" class="fs-14"></iconify-icon>Download Sample
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="importBtn">
                            <iconify-icon icon="solar:upload-bold-duotone" class="me-1"></iconify-icon> Import Child Categories
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
                                        <th style="width: 150px;">Child Category Name</th>
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
                            <button type="button" class="btn btn-outline-danger btn-sm fw-bold me-1" onclick="exportModalTableToExcel('importReportModal', 'ChildCategory_Import_Skipped_Failed_Records.xlsx', true)">
                                <iconify-icon icon="solar:download-square-linear" class="fs-16 align-middle me-1"></iconify-icon> Export Skipped &amp; Failed (.xlsx)
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm fw-bold" onclick="exportModalTableToExcel('importReportModal', 'ChildCategory_Import_All_Records.xlsx', false)">
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
var childCategoryUpdateBaseUrl = '{{ parse_url(route("admin.child-categories.update", 0), PHP_URL_PATH) }}'.replace('/0', '/');

document.addEventListener('DOMContentLoaded', function () {
    // Path-only URL so fetch works on any host/port (dev or production)
    var getSubcategoriesUrl = '{{ parse_url(route("admin.child-categories.get-subcategories"), PHP_URL_PATH) }}';

    // Chained dropdown logic helper
    function loadSubcategories(categoryId, targetSelectId, currentSubId = null) {
        var targetSelect = document.getElementById(targetSelectId);
        targetSelect.innerHTML = '<option value="">Loading Sub Categories...</option>';
        targetSelect.disabled = true;

        if (!categoryId) {
            targetSelect.innerHTML = '<option value="">Choose Sub Category...</option>';
            targetSelect.disabled = false;
            return;
        }

        fetch(getSubcategoriesUrl + '?category_id=' + categoryId, {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(response => {
                if (!response.ok) throw new Error('HTTP ' + response.status);
                return response.json();
            })
            .then(data => {
                targetSelect.innerHTML = '<option value="">Choose Sub Category...</option>';
                if (data.length === 0) {
                    targetSelect.innerHTML = '<option value="">No Sub Categories found</option>';
                } else {
                    data.forEach(sub => {
                        var selectedAttr = (currentSubId && currentSubId == sub.id) ? 'selected' : '';
                        targetSelect.innerHTML += `<option value="${sub.id}" ${selectedAttr}>${sub.name}</option>`;
                    });
                }
                targetSelect.disabled = false;
            })
            .catch(err => {
                console.error('Subcategory fetch error:', err);
                targetSelect.innerHTML = '<option value="">Error loading subcategories</option>';
                targetSelect.disabled = false;
            });
    }

    // Filters chaining
    var filterCat = document.getElementById('filterCategoryId');
    var filterSub = document.getElementById('filterSubCategoryId');
    if (filterCat && filterSub) {
        filterCat.addEventListener('change', function() {
            loadSubcategories(filterCat.value, 'filterSubCategoryId');
        });
        filterSub.addEventListener('change', function() {
            filterSub.form.submit();
        });
    }

    // Create Modal chaining
    var createCat = document.getElementById('createCategoryId');
    if (createCat) {
        createCat.addEventListener('change', function() {
            loadSubcategories(createCat.value, 'createSubCategoryId');
        });
    }

    // Edit Modal chaining
    var editCat = document.getElementById('editCategoryId');
    if (editCat) {
        editCat.addEventListener('change', function() {
            loadSubcategories(editCat.value, 'editSubCategoryId');
        });
    }

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
    var editBtn = null;
    if (editModalEl) {
        editModalEl.addEventListener('show.bs.modal', function (e) {
            editBtn = e.relatedTarget;
            document.getElementById('editForm').action = childCategoryUpdateBaseUrl + editBtn.dataset.id;
            document.getElementById('editName').value = editBtn.dataset.name;
            document.getElementById('editSlug').value = editBtn.dataset.slug;
            document.getElementById('editStatus').value = editBtn.dataset.status;
            document.getElementById('editCategoryId').value = editBtn.dataset.categoryId;
            
            // Load subcategories and select the correct one
            loadSubcategories(editBtn.dataset.categoryId, 'editSubCategoryId', editBtn.dataset.subcategoryId);
        });
    }

    // View modal config
    var viewModalEl = document.getElementById('viewModal');
    if (viewModalEl) {
        viewModalEl.addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            document.getElementById('viewCategory').innerText = btn.dataset.category;
            document.getElementById('viewSubCategory').innerText = btn.dataset.subcategory;
            document.getElementById('viewName').innerText = btn.dataset.name;
            document.getElementById('viewSlug').innerText = btn.dataset.slug;
            document.getElementById('viewStatus').innerText = btn.dataset.status;
            document.getElementById('viewCreator').innerText = btn.dataset.creator;
            document.getElementById('viewCreated').innerText = btn.dataset.created;
            document.getElementById('viewUpdater').innerText = btn.dataset.updater;
            document.getElementById('viewUpdated').innerText = btn.dataset.updated;
        });
    }

    // Confirm Delete
    var deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(function (form) {
        var btn = form.querySelector('.confirm-delete-btn');
        btn.addEventListener('click', function () {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will permanently delete the child category.',
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
