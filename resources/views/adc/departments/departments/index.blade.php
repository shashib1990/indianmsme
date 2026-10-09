@extends('layouts.admin')

@section('title', 'Departments')
@section('page-title', 'Departments')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <form id="filterForm" action="{{ route('admin.departments.index') }}" method="GET" class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#createModal">
                        <i class="bx bx-plus me-1"></i> Add Department
                    </button>

                    <div class="d-flex gap-2 align-items-center">
                        <select name="limit" class="form-select form-select-sm w-auto" onchange="document.getElementById('filterForm').submit()">
                            <option value="15" {{ $limit == 15 ? 'selected' : '' }}>15 Entries</option>
                            <option value="50" {{ $limit == 50 ? 'selected' : '' }}>50 Entries</option>
                            <option value="100" {{ $limit == 100 ? 'selected' : '' }}>100 Entries</option>
                        </select>

                        <select name="sort" class="form-select form-select-sm w-auto" onchange="document.getElementById('filterForm').submit()">
                            <option value="created_at" {{ $sort == 'created_at' ? 'selected' : '' }}>Date Added</option>
                            <option value="name" {{ $sort == 'name' ? 'selected' : '' }}>Name</option>
                        </select>

                        <select name="order" class="form-select form-select-sm w-auto" onchange="document.getElementById('filterForm').submit()">
                            <option value="desc" {{ $order == 'desc' ? 'selected' : '' }}>DESC</option>
                            <option value="asc" {{ $order == 'asc' ? 'selected' : '' }}>ASC</option>
                        </select>

                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Live Search..." value="{{ $search }}" style="width:220px"
                               oninput="clearTimeout(this.delay); this.delay = setTimeout(() => this.form.submit(), 600);">
                        @if($search)
                        <a href="{{ route('admin.departments.index') }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                        @endif
                    </div>
                </form>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0 text-nowrap">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Status</th>
                                <th>Created</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($departments as $dept)
                            <tr>
                                <td>{{ $loop->iteration + (($departments->currentPage() - 1) * $departments->perPage()) }}</td>
                                <td class="fw-medium">{{ $dept->name }}</td>
                                <td>{{ \Illuminate\Support\Str::limit($dept->description, 50) ?? '—' }}</td>
                                <td>
                                    @if($dept->is_active === '1')
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                    @else
                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>{{ $dept->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <!-- Edit -->
                                        <button class="btn btn-sm btn-outline-info"
                                                data-bs-toggle="modal"
                                                data-bs-target="#editModal"
                                                data-id="{{ $dept->id }}"
                                                data-name="{{ $dept->name }}"
                                                data-description="{{ $dept->description }}"
                                                data-status="{{ $dept->is_active }}">
                                            <iconify-icon icon="solar:pen-bold-duotone"></iconify-icon>
                                        </button>

                                        <!-- Delete -->
                                        <form action="{{ route('admin.departments.destroy', $dept->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger confirm-action"
                                                    data-confirm-message="Are you sure you want to delete '{{ $dept->name }}'? Cannot be undone if not assigned.">
                                                <iconify-icon icon="solar:trash-bin-trash-bold-duotone"></iconify-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <iconify-icon icon="solar:buildings-2-bold-duotone" class="fs-40 mb-2 d-block"></iconify-icon>
                                    No departments found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($departments->hasPages())
                <div class="d-flex justify-content-center my-3">
                    {{ $departments->withQueryString()->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Create Modal -->
<div class="modal fade" id="createModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Create Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.departments.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="is_active" class="form-select" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Save Department</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Modal -->
<div class="modal fade" id="editModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Edit Department</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" id="editDescription" class="form-control" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Status <span class="text-danger">*</span></label>
                        <select name="is_active" id="editStatus" class="form-select" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Update Department</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Edit Modal Data Passing
    const editModal = document.getElementById('editModal');
    if(editModal) {
        editModal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            document.getElementById('editForm').action = `/departments/${btn.getAttribute('data-id')}`;
            document.getElementById('editName').value = btn.getAttribute('data-name');
            document.getElementById('editDescription').value = btn.getAttribute('data-description') || '';
            document.getElementById('editStatus').value = btn.getAttribute('data-status');
        });
    }

    // Search focus preserver
    const searchInput = document.querySelector('input[name="search"]');
    if(searchInput) {
        let val = searchInput.value;
        searchInput.focus();
        searchInput.value = '';
        searchInput.value = val;
    }
});
</script>
@endpush
