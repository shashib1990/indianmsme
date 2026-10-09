@extends('layouts.admin')

@section('title', 'Permission Management')
@section('page-title', 'Permission Management')

@section('content')
    <!-- Common Page Action Header -->
    <x-admin.page-header 
        title="Permission Management" 
        subtitle="Manage granular application capabilities, route permissions, and modules."
        :actions="[
            [
                'label' => 'Add Permission',
                'icon' => 'solar:add-circle-bold-duotone',
                'target' => '#createModal',
                'permission' => 'permissions.create',
                'btnClass' => 'btn-primary'
            ],
            [
                'label' => 'Export CSV',
                'icon' => 'solar:document-text-bold-duotone',
                'onclick' => 'triggerExport(\'csv\')',
                'permission' => 'permissions.view',
                'btnClass' => 'btn-outline-primary'
            ]
        ]"
    />

    <div class="row mt-3">
        <div class="col-12">
            <!-- Reusable Table Filters -->
                <x-admin.table-filters 
                    action="{{ route('admin.permissions.index') }}" 
                    :search="$search" 
                    placeholder="Search permission or slug..." 
                    :limit="$limit" 
                    :sort="$sort" 
                    :order="$order"
                    :bulk-actions="[
                        'delete' => 'Delete Selected'
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
                                    <x-admin.table-header field="name" :sort="$sort" :order="$order">Permission Name</x-admin.table-header>
                                    <x-admin.table-header field="slug" :sort="$sort" :order="$order">Slug</x-admin.table-header>
                                    <x-admin.table-header field="module" :sort="$sort" :order="$order">Module</x-admin.table-header>
                                    <x-admin.table-header field="type" :sort="$sort" :order="$order">Type</x-admin.table-header>
                                    <x-admin.table-header field="created_at" :sort="$sort" :order="$order">Created At</x-admin.table-header>
                                    <th class="text-end pe-3" style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($permissions as $perm)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="form-check fs-15">
                                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $perm->id }}">
                                            </div>
                                        </td>
                                        <td class="ps-2 text-muted small">{{ $loop->iteration + (($permissions->currentPage() - 1) * $permissions->perPage()) }}</td>
                                        <td class="fw-semibold text-slate-800">{{ $perm->name }}</td>
                                        <td><code>{{ $perm->slug }}</code></td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary px-2 py-1 fs-12">
                                                {{ $perm->module }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($perm->type == 'read')
                                                <span class="badge bg-success-subtle text-success px-2 py-1 fs-12">Read</span>
                                            @else
                                                <span class="badge bg-warning-subtle text-warning px-2 py-1 fs-12">Write</span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">{{ $perm->created_at->format('d M Y') }}</td>
                                        <td class="text-end pe-3">
                                            <div class="d-inline-flex gap-1">
                                                @permission('permissions.edit')
                                                @php
                                                    $parts = explode('.', $perm->slug);
                                                    $actionValue = count($parts) > 1 ? $parts[1] : '';
                                                @endphp
                                                <button class="btn btn-sm btn-outline-info d-flex align-items-center justify-content-center p-1" 
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal"
                                                    data-id="{{ $perm->id }}"
                                                    data-name="{{ $perm->name }}"
                                                    data-slug="{{ $perm->slug }}"
                                                    data-module="{{ $perm->module }}"
                                                    data-action-val="{{ $actionValue }}"
                                                    style="width: 28px; height: 28px;">
                                                    <iconify-icon icon="solar:pen-bold-duotone" class="fs-14"></iconify-icon>
                                                </button>
                                                @endpermission

                                                @permission('permissions.delete')
                                                <form action="{{ route('admin.permissions.destroy', $perm->id) }}" method="POST" class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger confirm-action d-flex align-items-center justify-content-center p-1"
                                                        data-confirm-message="Delete permission '{{ $perm->name }}'?"
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
                                        icon="solar:shield-keyhole-bold-duotone" 
                                        title="No Permissions Found" 
                                        description="We couldn't find any permission matching your query. Add your first permission to get started." 
                                        colspan="8" 
                                    />
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($permissions->hasPages())
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                            <small class="text-muted">Showing {{ $permissions->firstItem() }}–{{ $permissions->lastItem() }} of {{ $permissions->total() }}</small>
                            {{ $permissions->withQueryString()->links() }}
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
                    <h5 class="modal-title fw-semibold text-slate-800">Add New Permission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.permissions.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Permission Name (Display) <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Create User" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Slug (Identifier)</label>
                            <input type="text" name="slug" class="form-control" placeholder="e.g. staff.create">
                            <small class="text-muted">Optional. If empty, will be generated as <strong>module.action</strong></small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Module <span class="text-danger">*</span></label>
                            <input type="text" name="module" class="form-control" placeholder="e.g. Staff Management" required>
                            <small class="text-muted">Grouping for permissions list</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Action (Fallback)</label>
                            <input type="text" name="action" class="form-control" placeholder="e.g. create">
                            <small class="text-muted">Only used if Slug is empty</small>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save Permission</button>
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
                    <h5 class="modal-title fw-semibold text-slate-800">Edit Permission</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Permission Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Slug (Identifier) <span class="text-danger">*</span></label>
                            <input type="text" name="slug" id="editSlug" class="form-control" required>
                            <small class="text-danger">Changing this will affect access control in code!</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Module <span class="text-danger">*</span></label>
                            <input type="text" name="module" id="editModule" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Action (Fallback)</label>
                            <input type="text" name="action" id="editAction" class="form-control">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Permission</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
var permissionUpdateBaseUrl = '{{ parse_url(route("admin.permissions.update", 0), PHP_URL_PATH) }}'.replace('/0', '/');

document.addEventListener('DOMContentLoaded', function () {
    const editModal = document.getElementById('editModal');
    if (editModal) {
        editModal.addEventListener('show.bs.modal', function (e) {
            const btn = e.relatedTarget;
            document.getElementById('editForm').action = permissionUpdateBaseUrl + btn.dataset.id;
            document.getElementById('editName').value = btn.dataset.name;
            document.getElementById('editSlug').value = btn.dataset.slug;
            document.getElementById('editModule').value = btn.dataset.module;
            document.getElementById('editAction').value = btn.dataset.actionVal || '';
        });
    }
});
</script>
@endpush