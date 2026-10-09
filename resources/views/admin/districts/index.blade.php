@extends('layouts.admin')

@section('title', 'District Management')
@section('page-title', 'District Management')

@section('content')
    <!-- Common Page Action Header -->
    <x-admin.page-header 
        title="District Management" 
        subtitle="Manage regional districts, link states, and upload batch location templates."
        :actions="[
            [
                'label' => 'Add District',
                'icon' => 'solar:add-circle-bold-duotone',
                'target' => '#createModal',
                'permission' => 'locations.create',
                'btnClass' => 'btn-primary'
            ],
            [
                'label' => 'Bulk Upload',
                'icon' => 'solar:upload-bold-duotone',
                'target' => '#bulkImportModal',
                'permission' => 'locations.create',
                'btnClass' => 'btn-outline-primary'
            ],
            [
                'label' => 'Export CSV',
                'icon' => 'solar:document-text-bold-duotone',
                'onclick' => 'triggerExport(\'csv\')',
                'permission' => 'locations.view',
                'btnClass' => 'btn-outline-success'
            ]
        ]"
    />

    <!-- Reusable Table Filters -->
                <x-admin.table-filters 
                    action="{{ route('admin.districts.index') }}" 
                    :search="$search" 
                    placeholder="Search district..." 
                    :limit="$limit" 
                    :sort="$sort" 
                    :order="$order"
                    :bulk-actions="[
                        'delete' => 'Delete Selected',
                        'status_active' => 'Activate Selected',
                        'status_inactive' => 'Deactivate Selected'
                    ]"
                >
                    {{-- State Filter --}}
                    <select name="state_id" id="filterStateId" class="w-auto form-select form-select-sm" style="min-width:180px"
                        data-placeholder="Filter by State" onchange="this.form.submit()">
                        <option value="">All States</option>
                        @foreach($states as $s)
                            <option value="{{ $s->id }}" {{ $stateFilter == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
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
                                    <x-admin.table-header field="name" :sort="$sort" :order="$order">District Name</x-admin.table-header>
                                    <x-admin.table-header field="state_id" :sort="$sort" :order="$order">State</x-admin.table-header>
                                    <x-admin.table-header field="pincodes_count" :sort="$sort" :order="$order">Pincodes</x-admin.table-header>
                                    <x-admin.table-header field="status" :sort="$sort" :order="$order">Status</x-admin.table-header>
                                    <x-admin.table-header field="created_at" :sort="$sort" :order="$order">Created At</x-admin.table-header>
                                    <th class="text-end pe-3" style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($districts as $district)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="form-check fs-15">
                                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $district->id }}">
                                            </div>
                                        </td>
                                        <td class="ps-2 text-muted small">{{ $loop->iteration + (($districts->currentPage() - 1) * $districts->perPage()) }}</td>
                                        <td class="fw-semibold text-slate-800">{{ $district->name }}</td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary px-2 py-1 fs-12">
                                                {{ $district->state->name ?? '—' }}
                                            </span>
                                        </td>
                                        <td>
                                            <a href="{{ route('admin.pincodes.index', ['district_id' => $district->id]) }}"
                                                class="text-decoration-none">
                                                <span class="badge bg-info-subtle text-info px-2 py-1 fs-12 d-inline-flex align-items-center gap-1">
                                                    <iconify-icon icon="solar:map-point-wave-bold-duotone" class="fs-12"></iconify-icon>
                                                    {{ $district->pincodes_count }}
                                                </span>
                                            </a>
                                        </td>
                                        <td>
                                            @if($district->status === '1')
                                                <span class="badge bg-success-subtle text-success px-2 py-1 fs-12">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-12">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">{{ $district->created_at->format('d M Y') }}</td>
                                        <td class="text-end pe-3">
                                            <div class="d-inline-flex gap-1">
                                                @permission('locations.edit')
                                                <button class="btn btn-sm btn-outline-info d-flex align-items-center justify-content-center p-1" 
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal"
                                                    data-id="{{ $district->id }}"
                                                    data-name="{{ $district->name }}"
                                                    data-state-id="{{ $district->state_id }}"
                                                    data-state-name="{{ $district->state->name ?? '' }}"
                                                    data-status="{{ $district->status }}"
                                                    style="width: 28px; height: 28px;">
                                                    <iconify-icon icon="solar:pen-bold-duotone" class="fs-14"></iconify-icon>
                                                </button>
                                                @endpermission

                                                @permission('locations.delete')
                                                <form action="{{ route('admin.districts.destroy', $district->id) }}" method="POST" class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger confirm-action d-flex align-items-center justify-content-center p-1"
                                                        data-confirm-message="Delete district '{{ $district->name }}'? All pincodes under it will also be removed."
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
                                        icon="solar:city-bold-duotone" 
                                        title="No Districts Found" 
                                        description="We couldn't find any district matching your search. Create one manually or upload a batch list." 
                                        colspan="8" 
                                    />
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($districts->hasPages())
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                            <small class="text-muted">Showing {{ $districts->firstItem() }}–{{ $districts->lastItem() }} of {{ $districts->total() }}</small>
                            {{ $districts->withQueryString()->links() }}
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
                    <h5 class="modal-title fw-semibold text-slate-800">Add New District</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.districts.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">State <span class="text-danger">*</span></label>
                            <select name="state_id" class="s-select" data-placeholder="— Select State —" required>
                                <option value="">— Select State —</option>
                                @foreach($states as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">District Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" placeholder="e.g. Mumbai" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Status <span class="text-danger">*</span></label>
                            <select name="status" class="form-select" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Save District</button>
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
                    <h5 class="modal-title fw-semibold text-slate-800">Edit District</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="editForm" method="POST">
                    @csrf @method('PUT')
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">State <span class="text-danger">*</span></label>
                            <select name="state_id" id="editStateId" class="s-select" data-placeholder="— Select State —" required>
                                <option value="">— Select State —</option>
                                @foreach($states as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">District Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="editName" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Status <span class="text-danger">*</span></label>
                            <select name="status" id="editStatus" class="form-select" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update District</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Bulk Import Modal: Select State → Upload Excel --}}
    <div class="modal fade" id="bulkImportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold text-slate-800">
                        <iconify-icon icon="solar:upload-bold-duotone" class="me-2 text-primary"></iconify-icon>
                        Bulk District Upload
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.locations.district.import') }}" method="POST" enctype="multipart/form-data"
                    id="districtImportForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Upload Excel / CSV <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <small class="text-muted">Columns: <code>state_name</code> (Column A), <code>district_name</code> (Column B). State must already exist in State Master.</small>
                        </div>

                        <div class="alert alert-info py-2 mb-0 small">
                            <strong>Sample format:</strong>
                            <br>Column A: <code>state_name</code> (e.g. Maharashtra, Delhi)
                            <br>Column B: <code>district_name</code> (e.g. Mumbai, Pune, New Delhi)
                            <a href="{{ route('admin.locations.district.sample') }}" class="ms-2 fw-medium">
                                <iconify-icon icon="solar:download-bold-duotone" class="me-1"></iconify-icon>Download Sample Template
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="districtImportBtn">
                            <iconify-icon icon="solar:upload-bold-duotone" class="me-1"></iconify-icon> Import Districts
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Import Result --}}
    @if(session('import_result'))
        @php $r = session('import_result'); @endphp
        <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999">
            <div class="toast show align-items-center {{ $r['failed'] > 0 || $r['skipped'] > 0 ? 'text-bg-warning' : 'text-bg-success' }} border-0" role="alert">
                <div class="d-flex">
                    <div class="toast-body">
                        @if($r['failed'] > 0 || $r['skipped'] > 0)
                            ⚠️ Import completed with warnings: <strong>{{ $r['inserted'] }} inserted</strong>,
                            {{ $r['skipped'] }} skipped, {{ $r['failed'] }} failed.
                            <a href="#" class="text-dark fw-bold ms-2 text-decoration-underline" data-bs-toggle="modal" data-bs-target="#importReportModal">View Details</a>
                        @else
                            ✅ Import complete: <strong>{{ $r['inserted'] }} inserted</strong> successfully (Total: {{ $r['total'] }})
                        @endif
                    </div>
                    <button type="button" class="btn-close me-2 m-auto {{ $r['failed'] > 0 || $r['skipped'] > 0 ? '' : 'btn-close-white' }}" data-bs-dismiss="toast"></button>
                </div>
            </div>
        </div>

        @if(!empty($r['details']) && ($r['skipped'] > 0 || $r['failed'] > 0))
        <div class="modal fade" id="importReportModal" tabindex="-1" aria-labelledby="importReportModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header bg-light">
                        <h5 class="modal-title fw-semibold text-slate-800" id="importReportModalLabel">
                            <iconify-icon icon="solar:info-square-bold-duotone" class="align-middle me-1 text-warning fs-20"></iconify-icon>
                            Import Report Details
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-0">
                        <div class="p-3 bg-light border-bottom">
                            <div class="row g-2 text-center">
                                <div class="col-3">
                                    <div class="p-2 border rounded bg-white">
                                        <div class="text-muted small">Total Rows</div>
                                        <div class="fs-18 fw-bold text-dark">{{ $r['total'] }}</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="p-2 border rounded bg-white">
                                        <div class="text-muted small">Inserted</div>
                                        <div class="fs-18 fw-bold text-success">{{ $r['inserted'] }}</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="p-2 border rounded bg-white">
                                        <div class="text-muted small">Skipped</div>
                                        <div class="fs-18 fw-bold text-warning">{{ $r['skipped'] }}</div>
                                    </div>
                                </div>
                                <div class="col-3">
                                    <div class="p-2 border rounded bg-white">
                                        <div class="text-muted small">Failed</div>
                                        <div class="fs-18 fw-bold text-danger">{{ $r['failed'] }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-hover table-centered mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3" style="width: 80px;">Row #</th>
                                        <th style="width: 150px;">Data Value</th>
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
                            <button type="button" class="btn btn-outline-danger btn-sm fw-bold me-1" onclick="exportModalTableToExcel('importReportModal', 'District_Import_Skipped_Failed_Records.xlsx', true)">
                                <iconify-icon icon="solar:download-square-linear" class="fs-16 align-middle me-1"></iconify-icon> Export Skipped &amp; Failed (.xlsx)
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm fw-bold" onclick="exportModalTableToExcel('importReportModal', 'District_Import_All_Records.xlsx', false)">
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
var districtUpdateBaseUrl = '{{ parse_url(route("admin.districts.update", 0), PHP_URL_PATH) }}'.replace('/0', '/');

document.addEventListener('DOMContentLoaded', function () {
    var editBtn = null;

    // Filter: state change → submit form (Tom Select handled globally/locally)
    var filterStateEl = document.getElementById('filterStateId');
    if (filterStateEl && filterStateEl.tomselect) {
        filterStateEl.tomselect.on('change', function () {
            document.getElementById('filterForm').submit();
        });
    }

    // Edit modal: capture data on show
    document.getElementById('editModal').addEventListener('show.bs.modal', function (e) {
        editBtn = e.relatedTarget;
        document.getElementById('editForm').action = districtUpdateBaseUrl + editBtn.dataset.id;
        document.getElementById('editName').value   = editBtn.dataset.name;
        document.getElementById('editStatus').value = editBtn.dataset.status;
    });

    // Edit modal: set Tom Select value after modal fully opens
    document.getElementById('editModal').addEventListener('shown.bs.modal', function () {
        if (editBtn) {
            var editStateEl = document.getElementById('editStateId');
            if (editStateEl && editStateEl.tomselect) {
                editStateEl.tomselect.setValue(String(editBtn.dataset.stateId), true);
            }
        }
    });

    // Import form loading state
    var importForm = document.getElementById('districtImportForm');
    if (importForm) {
        importForm.addEventListener('submit', function () {
            var btn = document.getElementById('districtImportBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Importing...';
        });
    }
});
</script>
@endpush
