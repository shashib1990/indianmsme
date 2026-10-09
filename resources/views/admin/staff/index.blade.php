@extends('layouts.admin')

@section('title', 'Staff Management')
@section('page-title', 'Staff Management')

@section('content')
    <!-- Common Page Action Header -->
    <x-admin.page-header 
        title="Staff Management" 
        subtitle="Manage administrator staff members, roles assignment, department access, and security permissions."
        :actions="[
            [
                'label' => 'Add Staff Member',
                'icon' => 'solar:add-circle-bold-duotone',
                'url' => route('admin.staff.create'),
                'permission' => 'staff.create',
                'btnClass' => 'btn-primary'
            ],
            [
                'label' => 'Export CSV',
                'icon' => 'solar:document-text-bold-duotone',
                'onclick' => 'triggerExport(\'csv\')',
                'permission' => 'staff.view',
                'btnClass' => 'btn-outline-primary'
            ]
        ]"
    />

    <div class="row mt-3">
        <div class="col-12">
            <!-- Reusable Table Filters -->
                <x-admin.table-filters 
                    action="{{ route('admin.staff.index') }}" 
                    :search="$search" 
                    placeholder="Search name, email, phone..." 
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
                                    <x-admin.table-header field="name" :sort="$sort" :order="$order">Staff Name</x-admin.table-header>
                                    <x-admin.table-header field="email" :sort="$sort" :order="$order">Email</x-admin.table-header>
                                    <x-admin.table-header field="phone" :sort="$sort" :order="$order">Phone</x-admin.table-header>
                                    <th>Department</th>
                                    <th>Permissions</th>
                                    <x-admin.table-header field="is_active" :sort="$sort" :order="$order">Status</x-admin.table-header>
                                    <x-admin.table-header field="created_at" :sort="$sort" :order="$order">Created At</x-admin.table-header>
                                    <th class="text-end pe-3" style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($staff as $member)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="form-check fs-15">
                                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $member->id }}">
                                            </div>
                                        </td>
                                        <td class="ps-2 text-muted small">{{ $loop->iteration + (($staff->currentPage() - 1) * $staff->perPage()) }}</td>
                                        <td class="fw-semibold text-slate-800">
                                            <div class="d-flex align-items-center">
                                                <img src="{{ $member->avatar_url }}" alt="avatar" class="rounded-circle me-2" width="30">
                                                <span>{{ $member->name }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $member->email }}</td>
                                        <td>{{ $member->phone ?? '—' }}</td>
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary px-2 py-1 fs-12">
                                                {{ $member->department ? $member->department->name : 'N/A' }}
                                            </span>
                                        </td>
                                        <td>
                                            @php
                                                $permCount = $member->roles->flatMap(fn($r) => $r->permissions)->unique('id')->count();
                                            @endphp
                                            <span class="badge bg-info-subtle text-info px-2 py-1 fs-12">
                                                <i class="bx bx-shield-quarter me-1"></i>{{ $permCount }} Permissions
                                            </span>
                                        </td>
                                        <td>
                                            @if($member->is_active === '1')
                                                <span class="badge bg-success-subtle text-success px-2 py-1 fs-12">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-12">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">{{ $member->created_at->format('d M Y') }}</td>
                                        <td class="text-end pe-3">
                                            <div class="d-inline-flex gap-1">
                                                @permission('staff.edit')
                                                <a href="{{ route('admin.staff.edit', $member->id) }}"
                                                    class="btn btn-sm btn-outline-info d-flex align-items-center justify-content-center p-1" 
                                                    style="width: 28px; height: 28px;">
                                                    <iconify-icon icon="solar:pen-bold-duotone" class="fs-14"></iconify-icon>
                                                </a>
                                                @endpermission

                                                @permission('staff.delete')
                                                <form action="{{ route('admin.staff.destroy', $member->id) }}" method="POST" class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger confirm-action d-flex align-items-center justify-content-center p-1"
                                                        data-confirm-message="Delete staff member '{{ $member->name }}'?"
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
                                        icon="solar:users-group-rounded-bold-duotone" 
                                        title="No Staff Members Found" 
                                        description="We couldn't find any staff member matching your query. Add your first staff member to get started." 
                                        colspan="10" 
                                    />
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($staff->hasPages())
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                            <small class="text-muted">Showing {{ $staff->firstItem() }}–{{ $staff->lastItem() }} of {{ $staff->total() }}</small>
                            {{ $staff->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
