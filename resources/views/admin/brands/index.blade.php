@extends('layouts.admin')

@section('title', 'Brand Management')
@section('page-title', 'Brand Management')

@section('content')
    <!-- Common Page Action Header -->
    <x-admin.page-header 
        title="Brand Management" 
        subtitle="Manage product brands, upload logos, and associate them with products."
        :actions="[
            [
                'label' => 'Add Brand',
                'icon' => 'solar:add-circle-bold-duotone',
                'target' => '#createModal',
                'permission' => 'categories.create',
                'btnClass' => 'btn-primary'
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
                    action="{{ route('admin.brands.index') }}" 
                    :search="$search" 
                    placeholder="Search brand..." 
                    :limit="$limit" 
                    :sort="$sort" 
                    :order="$order"
                    :bulk-actions="[
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
                                    <th style="width: 80px;">Logo</th>
                                    <x-admin.table-header field="name" :sort="$sort" :order="$order">Brand Name</x-admin.table-header>
                                    <x-admin.table-header field="slug" :sort="$sort" :order="$order">Slug</x-admin.table-header>
                                    <th>Linked Products</th>
                                    <x-admin.table-header field="status" :sort="$sort" :order="$order">Status</x-admin.table-header>
                                    <x-admin.table-header field="created_at" :sort="$sort" :order="$order">Created At</x-admin.table-header>
                                    <th class="text-end pe-3" style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($brands as $brand)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="form-check fs-15">
                                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $brand->id }}">
                                            </div>
                                        </td>
                                        <td class="ps-2 text-muted small">{{ $loop->iteration + (($brands->currentPage() - 1) * $brands->perPage()) }}</td>
                                        <td>
                                            @if($brand->logo)
                                                <img src="{{ $brand->logo }}" class="rounded border" style="width: 40px; height: 40px; object-fit: contain; background: #fff;">
                                            @else
                                                <div class="rounded border bg-light d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                                    <iconify-icon icon="solar:gallery-bold-duotone" class="text-muted fs-20"></iconify-icon>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="fw-semibold text-slate-800">{{ $brand->name }}</td>
                                        <td><code>{{ $brand->slug }}</code></td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary px-2 py-1 fs-12 d-inline-flex align-items-center gap-1">
                                                <iconify-icon icon="solar:box-bold-duotone" class="fs-12"></iconify-icon>
                                                {{ $brand->products_count }} Products
                                            </span>
                                        </td>
                                        <td>
                                            @if($brand->status === '1')
                                                <span class="badge bg-success-subtle text-success px-2 py-1 fs-12">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-12">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">{{ $brand->created_at->format('d M Y') }}</td>
                                        <td class="text-end pe-3">
                                            <div class="d-inline-flex gap-1">
                                                {{-- View Button --}}
                                                <button class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center p-1" 
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#viewModal"
                                                    data-name="{{ $brand->name }}"
                                                    data-slug="{{ $brand->slug }}"
                                                    data-logo="{{ $brand->logo }}"
                                                    data-status="{{ $brand->status === '1' ? 'Active' : 'Inactive' }}"
                                                    data-created="{{ $brand->created_at->format('d M Y, h:i A') }}"
                                                    data-updated="{{ $brand->updated_at->format('d M Y, h:i A') }}"
                                                    style="width: 28px; height: 28px;" title="View Details">
                                                    <iconify-icon icon="solar:eye-bold-duotone" class="fs-14"></iconify-icon>
                                                </button>
                                                
                                                {{-- Edit Button --}}
                                                @permission('categories.edit')
                                                <button class="btn btn-sm btn-outline-info d-flex align-items-center justify-content-center p-1" 
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal"
                                                    data-id="{{ $brand->id }}"
                                                    data-name="{{ $brand->name }}"
                                                    data-slug="{{ $brand->slug }}"
                                                    data-logo="{{ $brand->logo }}"
                                                    data-status="{{ $brand->status }}"
                                                    style="width: 28px; height: 28px;" title="Edit">
                                                    <iconify-icon icon="solar:pen-bold-duotone" class="fs-14"></iconify-icon>
                                                </button>
                                                @endpermission

                                                {{-- Toggle Status Button --}}
                                                @permission('categories.edit')
                                                <form action="{{ route('admin.brands.toggle-status', $brand->id) }}" method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm {{ $brand->status === '1' ? 'btn-outline-danger' : 'btn-outline-success' }} d-flex align-items-center justify-content-center p-1" 
                                                        style="width: 28px; height: 28px;" title="{{ $brand->status === '1' ? 'Deactivate' : 'Activate' }}">
                                                        <iconify-icon icon="{{ $brand->status === '1' ? 'solar:shield-close-bold-duotone' : 'solar:shield-check-bold-duotone' }}" class="fs-14"></iconify-icon>
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
                                            <p class="mb-0">No brands found matching your criteria.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($brands->hasPages())
                        <div class="d-flex justify-content-between align-items-center p-3 border-top">
                            <span class="text-muted small">Showing {{ $brands->firstItem() }} to {{ $brands->lastItem() }} of {{ $brands->total() }} results</span>
                            {{ $brands->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.brands.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-slate-800">Add New Brand</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required fw-medium text-slate-700">Brand Name</label>
                            <input type="text" name="name" id="createName" class="form-control" placeholder="e.g. Samsung" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium text-slate-700">Brand Slug <span class="text-muted small">(Auto-generated if left blank)</span></label>
                            <input type="text" name="slug" id="createSlug" class="form-control" placeholder="e.g. samsung">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium text-slate-700">Brand Logo</label>
                            <input type="file" name="logo" class="form-control" accept="image/*" onchange="previewImage(this, 'createImagePreview')">
                            <img id="createImagePreview" src="" class="img-thumbnail mt-2 d-none" style="max-height: 120px;">
                        </div>
                        <div class="mb-3">
                            <label class="form-label required fw-medium text-slate-700">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Create Brand</button>
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
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-slate-800">Edit Brand</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required fw-medium text-slate-700">Brand Name</label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium text-slate-700">Brand Slug</label>
                            <input type="text" name="slug" id="editSlug" class="form-control">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-medium text-slate-700">Brand Logo</label>
                            <input type="file" name="logo" class="form-control" accept="image/*" onchange="previewImage(this, 'editImagePreview')">
                            <div class="mt-2">
                                <img id="editImagePreview" src="" class="img-thumbnail d-none" style="max-height: 120px;">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required fw-medium text-slate-700">Status</label>
                            <select name="status" id="editStatus" class="form-select" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update Brand</button>
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
                    <h5 class="modal-title fw-bold text-slate-800">Brand Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <img id="viewImage" src="" class="img-thumbnail bg-white d-none" style="width: 100px; height: 100px; object-fit: contain;">
                        <div id="viewImagePlaceholder" class="rounded bg-light d-inline-flex align-items-center justify-content-center border" style="width: 100px; height: 100px;">
                            <iconify-icon icon="solar:gallery-bold-duotone" class="text-muted fs-40"></iconify-icon>
                        </div>
                        <h4 class="mt-2 mb-0" id="viewName">Brand Name</h4>
                        <code class="text-muted" id="viewSlug">brand-slug</code>
                    </div>
                    <table class="table table-sm table-bordered mt-3">
                        <tr>
                            <th class="bg-light" style="width: 40%;">Status</th>
                            <td id="viewStatus">Active</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Created At</th>
                            <td id="viewCreated">Date</td>
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
@endsection

@push('scripts')
<script>
var brandUpdateUrlPattern = '{{ route("admin.brands.update", ":id") }}';

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
            document.getElementById('editForm').action = brandUpdateUrlPattern.replace(':id', btn.dataset.id);
            document.getElementById('editName').value = btn.dataset.name;
            document.getElementById('editSlug').value = btn.dataset.slug;
            document.getElementById('editStatus').value = btn.dataset.status;
            
            var imgPreview = document.getElementById('editImagePreview');
            if (btn.dataset.logo) {
                imgPreview.src = btn.dataset.logo;
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
            document.getElementById('viewCreated').innerText = btn.dataset.created;
            document.getElementById('viewUpdated').innerText = btn.dataset.updated;

            var viewImg = document.getElementById('viewImage');
            var placeholder = document.getElementById('viewImagePlaceholder');
            if (btn.dataset.logo) {
                viewImg.src = btn.dataset.logo;
                viewImg.classList.remove('d-none');
                placeholder.classList.add('d-none');
            } else {
                viewImg.src = '';
                viewImg.classList.add('d-none');
                placeholder.classList.remove('d-none');
            }
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
