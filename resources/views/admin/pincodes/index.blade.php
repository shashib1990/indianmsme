@extends('layouts.admin')

@section('title', 'Pincode Management')
@section('page-title', 'Pincode Management')

@section('content')
@php
    $districtApiUrl = request()->getSchemeAndHttpHost() . route('admin.api.locations.districts', [], false);
@endphp

    <!-- Common Page Action Header -->
    <x-admin.page-header 
        title="Pincode Management" 
        subtitle="Manage administrative pincodes, regional alignments, and imports."
        :actions="[
            [
                'label' => 'Add Pincode',
                'icon' => 'solar:add-circle-bold-duotone',
                'target' => '#createModal',
                'permission' => 'locations.create',
                'btnClass' => 'btn-primary'
            ],
            [
                'label' => 'Excel Import',
                'icon' => 'solar:upload-bold-duotone',
                'target' => '#bulkImportModal',
                'permission' => 'locations.create',
                'btnClass' => 'btn-outline-success'
            ],
            [
                'label' => 'Export CSV',
                'icon' => 'solar:document-text-bold-duotone',
                'onclick' => 'triggerExport(\'csv\')',
                'permission' => 'locations.view',
                'btnClass' => 'btn-outline-secondary'
            ]
        ]"
    />

    <!-- Reusable Table Filters -->
                <x-admin.table-filters 
                    action="{{ route('admin.pincodes.index') }}" 
                    :search="$search" 
                    placeholder="Search pincode..." 
                    :limit="$limit" 
                    :sort="$sort" 
                    :order="$order"
                    :bulk-actions="[
                        'delete' => 'Delete Selected',
                        'status_active' => 'Activate Selected',
                        'status_inactive' => 'Deactivate Selected'
                    ]"
                >
                    {{-- State & District Filter Slot --}}
                    <select name="state_id" id="filterStateId" class="w-auto form-select form-select-sm" style="min-width:160px"
                        data-placeholder="Filter State">
                        <option value="">All States</option>
                        @foreach($states as $s)
                            <option value="{{ $s->id }}" {{ $stateFilter == $s->id ? 'selected' : '' }}>
                                {{ $s->name }}
                            </option>
                        @endforeach
                    </select>

                    <select name="district_id" id="filterDistrictId" class="w-auto form-select form-select-sm" style="min-width:160px"
                        data-placeholder="Filter District">
                        <option value="">All Districts</option>
                        @if($districtFilter)
                            @php $selD = \App\Models\District::find($districtFilter); @endphp
                            @if($selD)
                                <option value="{{ $selD->id }}" selected>{{ $selD->name }}</option>
                            @endif
                        @endif
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
                                    <x-admin.table-header field="pincode" :sort="$sort" :order="$order">Pincode</x-admin.table-header>
                                    <x-admin.table-header field="district_id" :sort="$sort" :order="$order">District</x-admin.table-header>
                                    <x-admin.table-header field="state_id" :sort="$sort" :order="$order">State</x-admin.table-header>
                                    <x-admin.table-header field="status" :sort="$sort" :order="$order">Status</x-admin.table-header>
                                    <x-admin.table-header field="created_at" :sort="$sort" :order="$order">Created At</x-admin.table-header>
                                    <th class="text-end pe-3" style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($pincodes as $pin)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="form-check fs-15">
                                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $pin->id }}">
                                            </div>
                                        </td>
                                        <td class="ps-2 text-muted small">{{ $loop->iteration + (($pincodes->currentPage() - 1) * $pincodes->perPage()) }}</td>
                                        <td class="fw-semibold text-slate-800">
                                            <span class="d-flex align-items-center gap-1">
                                                <iconify-icon icon="solar:map-point-bold-duotone" class="text-primary fs-16"></iconify-icon>
                                                {{ $pin->pincode }}
                                            </span>
                                        </td>
                                        <td>{{ $pin->district->name ?? '—' }}</td>
                                        <td>
                                            <span class="badge bg-secondary-subtle text-secondary px-2 py-1 fs-12">
                                                {{ $pin->state->name ?? '—' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($pin->status === '1')
                                                <span class="badge bg-success-subtle text-success px-2 py-1 fs-12">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-12">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">{{ $pin->created_at->format('d M Y') }}</td>
                                        <td class="text-end pe-3">
                                            <div class="d-inline-flex gap-1">
                                                @permission('locations.edit')
                                                <button class="btn btn-sm btn-outline-info d-flex align-items-center justify-content-center p-1" 
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal"
                                                    data-id="{{ $pin->id }}"
                                                    data-pincode="{{ $pin->pincode }}"
                                                    data-state-id="{{ $pin->state_id }}"
                                                    data-district-id="{{ $pin->district_id }}"
                                                    data-status="{{ $pin->status }}"
                                                    style="width: 28px; height: 28px;">
                                                    <iconify-icon icon="solar:pen-bold-duotone" class="fs-14"></iconify-icon>
                                                </button>
                                                @endpermission

                                                @permission('locations.delete')
                                                <form action="{{ route('admin.pincodes.destroy', $pin->id) }}" method="POST" class="d-inline">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger confirm-action d-flex align-items-center justify-content-center p-1"
                                                        data-confirm-message="Delete pincode {{ $pin->pincode }}?"
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
                                        icon="solar:map-point-search-bold-duotone" 
                                        title="No Pincodes Found" 
                                        description="We couldn't find any pincodes matching your query. Create one manually or upload a batch list." 
                                        colspan="8" 
                                    />
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($pincodes->hasPages())
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                            <small class="text-muted">Showing {{ $pincodes->firstItem() }}–{{ $pincodes->lastItem() }} of {{ $pincodes->total() }}</small>
                            {{ $pincodes->withQueryString()->links() }}
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
                    <h5 class="modal-title fw-semibold text-slate-800">Add Pincode</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.pincodes.store') }}" method="POST">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">State <span class="text-danger">*</span></label>
                            <select name="state_id" id="createStateId" class="s-select" data-placeholder="— Select State —" required>
                                <option value="">— Select State —</option>
                                @foreach($states as $s)
                                    <option value="{{ $s->id }}">{{ $s->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">District <span class="text-danger">*</span></label>
                            <select name="district_id" id="createDistrictId" class="s-select" data-placeholder="— Select State first —" required>
                                <option value="">— Select State first —</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Pincode <span class="text-danger">*</span></label>
                            <input type="text" name="pincode" class="form-control" placeholder="e.g. 400001" pattern="\d{4,10}" maxlength="10" required>
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
                        <button type="submit" class="btn btn-primary">Save Pincode</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Excel Import Modal --}}
    <div class="modal fade" id="bulkImportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold text-slate-800">
                        <iconify-icon icon="solar:upload-bold-duotone" class="me-2 text-success"></iconify-icon>
                        Excel Import Pincodes
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.locations.pincode.import') }}" method="POST" enctype="multipart/form-data" id="pincodeImportForm">
                    @csrf
                    <div class="modal-body">
                        <div class="alert alert-info py-2 px-3 small mb-3">
                            <iconify-icon icon="solar:info-square-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon>
                            Excel/CSV file columns: <code>state_name</code>, <code>district_name</code>, <code>pincode</code>. Database mein jo State aur District exist karte hain sirf unke pincodes add honge, baaki skip ho jayenge.
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Upload Excel / CSV File <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <small class="text-muted d-block mt-1">Required columns: <code>state_name</code>, <code>district_name</code>, <code>pincode</code></small>
                        </div>
                        <div class="alert alert-light border py-2 small mb-0 d-flex align-items-center justify-content-between">
                            <span>Download reference template:</span>
                            <a href="{{ route('admin.locations.pincode.sample') }}" class="fw-medium d-inline-flex align-items-center gap-1">
                                <iconify-icon icon="solar:download-bold-duotone" class="fs-14"></iconify-icon> Download Sample Excel
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success" id="pincodeImportBtn">
                            <iconify-icon icon="solar:upload-bold-duotone" class="me-1"></iconify-icon> Import Pincodes
                        </button>
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
                    <h5 class="modal-title fw-semibold text-slate-800">Edit Pincode</h5>
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
                            <label class="form-label text-slate-700 fw-medium">District <span class="text-danger">*</span></label>
                            <select name="district_id" id="editDistrictId" class="s-select" data-placeholder="— Select State first —" required>
                                <option value="">— Select State first —</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Pincode <span class="text-danger">*</span></label>
                            <input type="text" name="pincode" id="editPincode" class="form-control" pattern="\d{4,10}" maxlength="10" required>
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
                        <button type="submit" class="btn btn-primary">Update Pincode</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

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
                            <button type="button" class="btn btn-outline-danger btn-sm fw-bold me-1" onclick="exportModalTableToExcel('importReportModal', 'Pincode_Import_Skipped_Failed_Records.xlsx', true)">
                                <iconify-icon icon="solar:download-square-linear" class="fs-16 align-middle me-1"></iconify-icon> Export Skipped &amp; Failed (.xlsx)
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm fw-bold" onclick="exportModalTableToExcel('importReportModal', 'Pincode_Import_All_Records.xlsx', false)">
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
var pincodeUpdateBaseUrl = '{{ parse_url(route("admin.pincodes.update", 0), PHP_URL_PATH) }}'.replace('/0', '/');

const DIST_API = '{{ $districtApiUrl }}';

/**
 * Load districts via fetch and update a Tom Select instance.
 */
function loadDistricts(stateId, selectId, selectedId) {
    var el = document.getElementById(selectId);
    if (!el || !el.tomselect) return;
    var ts = el.tomselect;

    ts.clear(true);
    ts.clearOptions();
    ts.disable();

    if (!stateId) {
        ts.addOption({ value: '', text: '— Select State first —', sort_index: 0 });
        ts.refreshOptions(false);
        ts.enable();
        return;
    }

    fetch(DIST_API + '?state_id=' + encodeURIComponent(stateId))
        .then(function(r) { return r.json(); })
        .then(function(data) {
            ts.addOption({ value: '', text: '— Select District —', sort_index: 0 });
            var index = 1;
            data.forEach(function(d) {
                ts.addOption({ value: String(d.id), text: d.name, sort_index: index++ });
            });
            ts.refreshOptions(false);
            ts.enable();
            if (selectedId) ts.setValue(String(selectedId), true);
        })
        .catch(function() {
            ts.addOption({ value: '', text: 'Error loading districts' });
            ts.refreshOptions(false);
            ts.enable();
        });
}

document.addEventListener('DOMContentLoaded', function () {

    // ── Filter bar: State → load Districts → auto-submit ──
    var filterStateEl = document.getElementById('filterStateId');
    var filterDistEl  = document.getElementById('filterDistrictId');

    if (filterStateEl && filterStateEl.tomselect) {
        filterStateEl.tomselect.on('change', function (value) {
            var ts = filterDistEl.tomselect;
            ts.clear(true);
            ts.clearOptions();
            ts.addOption({ value: '', text: 'All Districts', sort_index: 0 });
            ts.refreshOptions(false);

            if (!value) { document.getElementById('filterForm').submit(); return; }

            fetch(DIST_API + '?state_id=' + encodeURIComponent(value))
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    var index = 1;
                    data.forEach(function(d) {
                        ts.addOption({ value: String(d.id), text: d.name, sort_index: index++ });
                    });
                    ts.refreshOptions(false);
                    document.getElementById('filterForm').submit();
                });
        });

        if (filterDistEl && filterDistEl.tomselect) {
            filterDistEl.tomselect.on('change', function () {
                document.getElementById('filterForm').submit();
            });
        }
    }

    // Pre-populate filter districts on page load (if state is pre-selected)
    @if($stateFilter)
    fetch(DIST_API + '?state_id={{ $stateFilter }}')
        .then(function(r) { return r.json(); })
        .then(function(data) {
            var ts = filterDistEl.tomselect;
            var index = 1;
            data.forEach(function(d) {
                ts.addOption({ value: String(d.id), text: d.name, sort_index: index++ });
            });
            ts.refreshOptions(false);
            @if($districtFilter)
            ts.setValue('{{ $districtFilter }}', true);
            @endif
        });
    @endif

    // ── Create Modal: State → load Districts ──
    var createStateEl = document.getElementById('createStateId');
    if (createStateEl && createStateEl.tomselect) {
        createStateEl.tomselect.on('change', function (value) {
            loadDistricts(value, 'createDistrictId', null);
        });
    }

    // ── Import Modal: State → load Districts ──
    var importStateEl = document.getElementById('importStateId');
    if (importStateEl && importStateEl.tomselect) {
        importStateEl.tomselect.on('change', function (value) {
            loadDistricts(value, 'importDistrictId', null);
        });
    }

    // ── Edit Modal ──
    var editData = {};

    document.getElementById('editModal').addEventListener('show.bs.modal', function (e) {
        var btn = e.relatedTarget;
        editData = {
            id:         btn.dataset.id,
            pincode:    btn.dataset.pincode,
            stateId:    btn.dataset.stateId,
            districtId: btn.dataset.districtId,
            status:     btn.dataset.status
        };
        document.getElementById('editForm').action = pincodeUpdateBaseUrl + editData.id;
        document.getElementById('editPincode').value = editData.pincode;
        document.getElementById('editStatus').value  = editData.status;
    });

    document.getElementById('editModal').addEventListener('shown.bs.modal', function () {
        var editStateEl = document.getElementById('editStateId');
        if (editStateEl && editStateEl.tomselect) {
            editStateEl.tomselect.setValue(String(editData.stateId), true);
            loadDistricts(editData.stateId, 'editDistrictId', editData.districtId);
        }
    });

    // Edit modal: state change → reload districts
    var editStateEl = document.getElementById('editStateId');
    if (editStateEl && editStateEl.tomselect) {
        editStateEl.tomselect.on('change', function (value) {
            if (value !== editData.stateId) {
                loadDistricts(value, 'editDistrictId', null);
            }
        });
    }

    // Import form loading state
    var importForm = document.getElementById('pincodeImportForm');
    if (importForm) {
        importForm.addEventListener('submit', function () {
            var btn = document.getElementById('pincodeImportBtn');
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Importing...';
        });
    }
});
</script>
@endpush