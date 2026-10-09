@extends('layouts.admin')

@section('title', 'Units of Measure')
@section('page-title', 'Units of Measure')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header 
        title="Units of Measure (UOM)" 
        subtitle="Manage standard product measurement units used across Seller, DDC, and ADC panels."
        :actions="[
            [
                'label' => 'Add New Unit',
                'icon' => 'solar:add-circle-bold-duotone',
                'target' => '#createUnitModal',
                'permission' => 'categories.create',
                'btnClass' => 'btn-primary'
            ]
        ]"
    />

    <!-- Alerts -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-20 align-middle me-2"></iconify-icon>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <iconify-icon icon="solar:danger-triangle-bold-duotone" class="fs-20 align-middle me-2"></iconify-icon>
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm mb-4" role="alert">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Search & Filter Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-3">
            <form action="{{ route('admin.units-of-measure.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-white border-end-0 text-muted">
                            <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0 ps-0" placeholder="Search by Unit Name or Short Code..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="status" class="form-select" onchange="this.form.submit()">
                        <option value="">All Statuses</option>
                        <option value="1" {{ request('status') === '1' ? 'selected' : '' }}>Active Only</option>
                        <option value="0" {{ request('status') === '0' ? 'selected' : '' }}>Inactive Only</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn btn-dark px-3">Filter</button>
                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('admin.units-of-measure.index') }}" class="btn btn-light border px-3">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Units Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-slate-700 fw-bold fs-13">
                        <tr>
                            <th class="ps-3" style="width: 60px;">#</th>
                            <th>Unit Name</th>
                            <th>Short Code</th>
                            <th class="text-center" style="width: 100px;">Sort Order</th>
                            <th class="text-center" style="width: 120px;">Status</th>
                            <th class="text-end pe-3" style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($units as $unit)
                            <tr>
                                <td class="ps-3 text-muted small">{{ $loop->iteration + (($units->currentPage() - 1) * $units->perPage()) }}</td>
                                <td class="fw-bold text-slate-800">{{ $unit->name }}</td>
                                <td>
                                    @if($unit->short_code)
                                        <code class="bg-light px-2 py-1 rounded text-primary fw-bold">{{ $unit->short_code }}</code>
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td class="text-center fw-semibold text-slate-600">{{ $unit->sort_order }}</td>
                                <td class="text-center">
                                    <form action="{{ route('admin.units-of-measure.toggle-status', $unit->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn border-0 p-0" title="Click to toggle status">
                                            @if($unit->status)
                                                <span class="badge bg-success-subtle text-success px-2 py-1 fs-12">Active</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1 fs-12">Inactive</span>
                                            @endif
                                        </button>
                                    </form>
                                </td>
                                <td class="text-end pe-3">
                                    <button type="button" 
                                            class="btn btn-sm btn-light border text-primary me-1 edit-unit-btn" 
                                            data-id="{{ $unit->id }}"
                                            data-name="{{ $unit->name }}"
                                            data-short_code="{{ $unit->short_code }}"
                                            data-sort_order="{{ $unit->sort_order }}"
                                            data-status="{{ $unit->status ? '1' : '0' }}"
                                            data-bs-toggle="modal" 
                                            data-bs-target="#editUnitModal">
                                        <iconify-icon icon="solar:pen-bold-duotone" class="align-middle"></iconify-icon> Edit
                                    </button>
                                    <form action="{{ route('admin.units-of-measure.destroy', $unit->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete unit {{ $unit->name }}?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-light border text-danger">
                                            <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="align-middle"></iconify-icon>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <iconify-icon icon="solar:ruler-bold-duotone" class="fs-40 text-muted mb-2 d-block mx-auto"></iconify-icon>
                                    No Units of Measure found.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($units->hasPages())
            <div class="card-footer bg-white border-0 py-3">
                {{ $units->links() }}
            </div>
        @endif
    </div>
</div>

<!-- Create Unit Modal -->
<div class="modal fade" id="createUnitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-slate-800">
                    <iconify-icon icon="solar:add-circle-bold-duotone" class="text-primary align-middle me-1"></iconify-icon>
                    Add New Unit of Measure
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('admin.units-of-measure.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-slate-700">Unit Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Piece, Box, Kilogram, Litre" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-slate-700">Short Code <span class="text-muted fw-normal">(Optional)</span></label>
                        <input type="text" name="short_code" class="form-control" placeholder="e.g. pc, box, kg, ltr">
                        <small class="text-muted fs-11">Lowercase short representation used in price displays.</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-slate-700">Sort Order</label>
                        <input type="number" name="sort_order" class="form-control" value="0" min="0">
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="status" id="createStatusSwitch" value="1" checked>
                        <label class="form-check-label fw-medium text-slate-700" for="createStatusSwitch">Active</label>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-light border px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Create Unit</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Unit Modal -->
<div class="modal fade" id="editUnitModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-light">
                <h5 class="modal-title fw-bold text-slate-800">
                    <iconify-icon icon="solar:pen-bold-duotone" class="text-primary align-middle me-1"></iconify-icon>
                    Edit Unit of Measure
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUnitForm" action="" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-slate-700">Unit Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" id="editUnitName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-slate-700">Short Code <span class="text-muted fw-normal">(Optional)</span></label>
                        <input type="text" name="short_code" id="editUnitShortCode" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-slate-700">Sort Order</label>
                        <input type="number" name="sort_order" id="editUnitSortOrder" class="form-control" min="0">
                    </div>
                    <div class="form-check form-switch mt-3">
                        <input class="form-check-input" type="checkbox" name="status" id="editStatusSwitch" value="1">
                        <label class="form-check-label fw-medium text-slate-700" for="editStatusSwitch">Active</label>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0">
                    <button type="button" class="btn btn-light border px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary px-4">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.edit-unit-btn').forEach(button => {
            button.addEventListener('click', function () {
                const id = this.dataset.id;
                const name = this.dataset.name;
                const shortCode = this.dataset.short_code || '';
                const sortOrder = this.dataset.sort_order || 0;
                const status = this.dataset.status === '1';

                const form = document.getElementById('editUnitForm');
                form.action = `/admin/units-of-measure/${id}/update`;

                document.getElementById('editUnitName').value = name;
                document.getElementById('editUnitShortCode').value = shortCode;
                document.getElementById('editUnitSortOrder').value = sortOrder;
                document.getElementById('editStatusSwitch').checked = status;
            });
        });
    });
</script>
@endpush
@endsection
