@extends('layouts.admin')

@section('title', 'Department Management')
@section('page-title', 'Department Management')

@section('content')
    <!-- Common Page Action Header -->
    <x-admin.page-header 
        title="Department Management" 
        subtitle="Manage administrative departments, descriptions, and view linked regional roles."
        :actions="[
            [
                'label' => 'Add Department',
                'icon' => 'solar:add-circle-bold-duotone',
                'target' => '#createModal',
                'permission' => 'departments.create',
                'btnClass' => 'btn-primary'
            ],
            [
                'label' => 'Export CSV',
                'icon' => 'solar:document-text-bold-duotone',
                'onclick' => 'triggerExport(\'csv\')',
                'permission' => 'departments.view',
                'btnClass' => 'btn-outline-success'
            ]
        ]"
    />

    <div class="row mt-3">
        <div class="col-12">
            <!-- Reusable Table Filters -->
                <x-admin.table-filters 
                    action="{{ route('admin.departments.index') }}" 
                    :search="$search" 
                    placeholder="Search department name or description..." 
                    :limit="$limit" 
                    :sort="$sort" 
                    :order="$order"
                    :bulk-actions="[
                        'delete' => 'Delete Selected',
                        'status_active' => 'Activate Selected',
                        'status_inactive' => 'Deactivate Selected'
                    ]"
                />

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
                                    <x-admin.table-header field="name" :sort="$sort" :order="$order">Department Name</x-admin.table-header>
                                    <th>Description</th>
                                    <x-admin.table-header field="is_active" :sort="$sort" :order="$order">Status</x-admin.table-header>
                                    <x-admin.table-header field="created_at" :sort="$sort" :order="$order">Created At</x-admin.table-header>
                                    <th class="text-end pe-3" style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($departments as $dept)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="form-check fs-15">
                                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $dept->id }}">
                                            </div>
                                        </td>
                                        <td class="ps-2 text-muted small">{{ $loop->iteration + (($departments->currentPage() - 1) * $departments->perPage()) }}</td>
                                        <td class="fw-semibold text-slate-800">{{ $dept->name }}</td>
                                        <td class="text-muted">{{ \Illuminate\Support\Str::limit($dept->description, 60) ?? '—' }}</td>
                                        <td>
                                            @if($dept->is_active === '1')
                                                <span class="badge bg-success-subtle text-success px-2 py-1 fs-12">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-12">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">{{ $dept->created_at->format('d M Y') }}</td>
                                        <td class="text-end pe-3">
                                            <div class="d-inline-flex gap-1">
                                                @permission('departments.edit')
                                                <button class="btn btn-sm btn-outline-info d-flex align-items-center justify-content-center p-1" 
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal"
                                                    data-id="{{ $dept->id }}"
                                                    data-name="{{ $dept->name }}"
                                                    data-description="{{ $dept->description }}"
                                                    data-status="{{ $dept->is_active }}"
                                                    style="width: 28px; height: 28px;">
                                                    <iconify-icon icon="solar:pen-bold-duotone" class="fs-14"></iconify-icon>
                                                </button>
                                                @endpermission

                                                @permission('departments.delete')
                                                <form action="{{ route('admin.departments.destroy', $dept->id) }}" method="POST" class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger confirm-action d-flex align-items-center justify-content-center p-1"
                                                        data-confirm-message="Delete department '{{ $dept->name }}'?"
                                                        style="width: 28px; height: 28px;">
                                                        <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="fs-14"></iconify-icon>
                                                    </button>
                                                </form>
                                                @endpermission
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <x-admin.empty-state 
                                        icon="solar:buildings-bold-duotone" 
                                        title="No Departments Found" 
                                        description="We couldn't find any department matching your query. Add your first department to get started." 
                                        colspan="7" 
                                    />
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($departments->hasPages())
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                            <small class="text-muted">Showing {{ $departments->firstItem() }}–{{ $departments->lastItem() }} of {{ $departments->total() }}</small>
                            {{ $departments->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Create Modal --}}
    <div class="modal fade" id="createModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold text-slate-800">Add New Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.departments.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Department Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Sales" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Description</label>
                            <textarea name="description" class="form-control" placeholder="Optional description..." rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Status <span class="text-danger">*</span></label>
                            <select name="is_active" class="form-select" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Edit Modal --}}
    <div class="modal fade" id="editModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold text-slate-800">Edit Department</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Department Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Description</label>
                            <textarea name="description" id="editDescription" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Status <span class="text-danger">*</span></label>
                            <select name="is_active" id="editStatus" class="form-select" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Department</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
var departmentUpdateBaseUrl = '{{ parse_url(route("admin.departments.update", 0), PHP_URL_PATH) }}'.replace('/0', '/');

document.addEventListener('DOMContentLoaded', function () {
    const editModal = document.getElementById('editModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (e) {
            const btn = e.relatedTarget;
            document.getElementById('editForm').action = departmentUpdateBaseUrl + btn.dataset.id;
            document.getElementById('editName').value = btn.dataset.name;
            document.getElementById('editDescription').value = btn.dataset.description || '';
            document.getElementById('editStatus').value = btn.dataset.status;
        });
    }
});
</script>
@endpush