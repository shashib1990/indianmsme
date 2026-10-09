@extends('layouts.vendor')

@section('title', 'Staff Management')
@section('page-title', 'Staff Management')

@section('content')
    <!-- Action Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">Staff Management</h4>
                            <p class="text-muted small mb-0">Manage vendor staff, assign departments, roles and manage custom permissions.</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            @permission('staff.create')
                            <a href="{{ route('vendor.staff.create') }}" class="btn btn-primary">
                                <iconify-icon icon="solar:add-circle-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                                Add Staff Member
                            </a>
                            @endpermission
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="row mb-3 g-2">
        <div class="col-md-12">
            <form action="{{ route('vendor.staff.index') }}" method="GET" id="filterForm" class="d-flex flex-wrap gap-2">
                <input type="hidden" name="sort" id="formSort" value="{{ $sort }}">
                <input type="hidden" name="order" id="formOrder" value="{{ $order }}">
                <div class="input-group" style="max-width: 350px;">
                    <span class="input-group-text bg-white border-end-0"><iconify-icon icon="solar:magnifer-bold-duotone" class="text-muted"></iconify-icon></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search name or email..." value="{{ $search }}">
                </div>
                <select name="limit" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="10" {{ $limit == 10 ? 'selected' : '' }}>10 per page</option>
                    <option value="25" {{ $limit == 25 || !$limit ? 'selected' : '' }}>25 per page</option>
                    <option value="50" {{ $limit == 50 ? 'selected' : '' }}>50 per page</option>
                    <option value="100" {{ $limit == 100 ? 'selected' : '' }}>100 per page</option>
                </select>
                <button type="submit" class="btn btn-outline-secondary">Filter</button>
            </form>
        </div>
    </div>

    <div class="row">
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
                                    <x-admin.table-header field="name" :sort="$sort" :order="$order">Staff Name</x-admin.table-header>
                                    <x-admin.table-header field="email" :sort="$sort" :order="$order">Email</x-admin.table-header>
                                    <th>Phone</th>
                                    <th>Department</th>
                                    <x-admin.table-header field="is_active" :sort="$sort" :order="$order">Status</x-admin.table-header>
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
                                        <td class="ps-2 text-muted small">{{ ($staff->currentPage() - 1) * $staff->perPage() + $loop->iteration }}</td>
                                        <td class="fw-semibold text-slate-800">
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ $member->avatar_url }}" alt="avatar" class="rounded-circle" width="30">
                                                <span>{{ $member->name }}</span>
                                            </div>
                                        </td>
                                        <td>{{ $member->email }}</td>
                                        <td>{{ $member->phone ?? '—' }}</td>
                                        <td>
                                            @if($member->department)
                                                <span class="badge bg-primary-subtle text-primary px-2 py-1 fs-12">
                                                    {{ $member->department->name }}
                                                </span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($member->is_active === '1' || $member->is_active === 1)
                                                <span class="badge bg-success-subtle text-success px-2 py-1 fs-12">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-12">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="d-inline-flex gap-1">
                                                @permission('staff.edit')
                                                <a href="{{ route('vendor.staff.edit', $member) }}" 
                                                    class="btn btn-sm btn-outline-info d-flex align-items-center justify-content-center p-1"
                                                    style="width: 28px; height: 28px;">
                                                    <iconify-icon icon="solar:pen-bold-duotone" class="fs-14"></iconify-icon>
                                                </a>
                                                @endpermission

                                                @permission('staff.delete')
                                                <form action="{{ route('vendor.staff.destroy', $member) }}" method="POST" class="d-inline">
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
                                        title="No Staff Found" 
                                        description="We couldn't find any staff matching your query. Add your first staff member to get started." 
                                        colspan="9" 
                                    />
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($staff->total() > 0)
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                            <small class="text-muted">Showing {{ $staff->firstItem() }}–{{ $staff->lastItem() }} of {{ $staff->total() }}</small>
                            @if($staff->hasPages())
                                {{ $staff->withQueryString()->links() }}
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
