@extends('layouts.admin')

@section('title', 'Role Management')
@section('page-title', 'Role Management')

@section('content')
<!-- Common Page Action Header -->
<x-admin.page-header title="Role Management"
    subtitle="Manage administrator and employee roles, access policies, and permission levels." :actions="[
        [
            'label' => 'Add Role',
            'icon' => 'solar:add-circle-bold-duotone',
            'url' => route('admin.roles.create'),
            'permission' => 'roles.create',
            'btnClass' => 'btn-primary'
        ],
        [
            'label' => 'Export CSV',
            'icon' => 'solar:document-text-bold-duotone',
            'onclick' => 'triggerExport(\'csv\')',
            'permission' => 'roles.view',
            'btnClass' => 'btn-outline-success'
        ]
    ]" />

<div class="row mt-3">
    <div class="col-12">
        <!-- Reusable Table Filters -->
            <x-admin.table-filters action="{{ route('admin.roles.index') }}" :search="$search"
                placeholder="Search roles or slug..." :limit="$limit" :sort="$sort" :order="$order" :bulk-actions="[
                        'delete' => 'Delete Selected'
                    ]" />

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
                                <x-admin.table-header field="name" :sort="$sort" :order="$order">Role
                                    Name</x-admin.table-header>
                                <x-admin.table-header field="slug" :sort="$sort"
                                    :order="$order">Slug</x-admin.table-header>
                                <th>Department</th>
                                <th>Permissions</th>
                                <th>Description</th>
                                <x-admin.table-header field="created_at" :sort="$sort" :order="$order">Created
                                    At</x-admin.table-header>
                                <th class="text-end pe-3" style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($roles as $role)
                            <tr>
                                <td class="ps-3">
                                    <div class="form-check fs-15">
                                        <input type="checkbox" class="form-check-input row-checkbox"
                                            value="{{ $role->id }}">
                                    </div>
                                </td>
                                <td class="ps-2 text-muted small">
                                    {{ $loop->iteration + (($roles->currentPage() - 1) * $roles->perPage()) }}</td>
                                <td class="fw-semibold text-slate-800">{{ $role->name }}</td>
                                <td><code>{{ $role->slug }}</code></td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary px-2 py-1 fs-12">
                                        {{ $role->department ? $role->department->name : 'Global' }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info px-2 py-1 fs-12">
                                        {{ $role->permissions_count }} permissions
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    {{ \Illuminate\Support\Str::limit($role->description, 50) ?? '—' }}</td>
                                <td class="text-muted small">{{ $role->created_at->format('d M Y') }}</td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1">
                                        @permission('roles.edit')
                                        <a href="{{ route('admin.roles.edit', $role->id) }}"
                                            class="btn btn-sm btn-outline-info d-flex align-items-center justify-content-center p-1"
                                            style="width: 28px; height: 28px;">
                                            <iconify-icon icon="solar:pen-bold-duotone" class="fs-14"></iconify-icon>
                                        </a>
                                        @endpermission

                                        @permission('roles.delete')
                                        <form action="{{ route('admin.roles.destroy', $role->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-danger confirm-action d-flex align-items-center justify-content-center p-1"
                                                data-confirm-message="Delete role '{{ $role->name }}'?"
                                                style="width: 28px; height: 28px;">
                                                <iconify-icon icon="solar:trash-bin-trash-bold-duotone"
                                                    class="fs-14"></iconify-icon>
                                            </button>
                                        </form>
                                        @endpermission
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <x-admin.empty-state icon="solar:key-bold-duotone" title="No Roles Found"
                                description="We couldn't find any role matching your query. Add your first role to get started."
                                colspan="9" />
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($roles->hasPages())
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                        <small class="text-muted">Showing {{ $roles->firstItem() }}–{{ $roles->lastItem() }} of
                            {{ $roles->total() }}</small>
                        {{ $roles->withQueryString()->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection