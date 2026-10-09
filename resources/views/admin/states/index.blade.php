@extends('layouts.admin')

@section('title', 'State Management')
@section('page-title', 'State Management')

@section('content')
<!-- Common Page Action Header -->
<x-admin.page-header title="State Management"
    subtitle="Manage India states, codes, and view linked regional statistics." :actions="[
        [
            'label' => 'Add State',
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
    ]" />

<div class="row mt-3">
    <div class="col-12">
        <!-- Reusable Table Filters -->
            <x-admin.table-filters action="{{ route('admin.states.index') }}" :search="$search"
                placeholder="Search state or code..." :limit="$limit" :sort="$sort" :order="$order" :bulk-actions="[
                        'delete' => 'Delete Selected',
                        'status_active' => 'Activate Selected',
                        'status_inactive' => 'Deactivate Selected'
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
                                <x-admin.table-header field="name" :sort="$sort" :order="$order">State
                                    Name</x-admin.table-header>
                                <x-admin.table-header field="code" :sort="$sort"
                                    :order="$order">Code</x-admin.table-header>
                                <x-admin.table-header field="districts_count" :sort="$sort"
                                    :order="$order">Districts</x-admin.table-header>
                                <x-admin.table-header field="pincodes_count" :sort="$sort"
                                    :order="$order">Pincodes</x-admin.table-header>
                                <x-admin.table-header field="status" :sort="$sort"
                                    :order="$order">Status</x-admin.table-header>
                                <x-admin.table-header field="created_at" :sort="$sort" :order="$order">Created
                                    At</x-admin.table-header>
                                <th class="text-end pe-3" style="width: 100px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($states as $state)
                            <tr>
                                <td class="ps-3">
                                    <div class="form-check fs-15">
                                        <input type="checkbox" class="form-check-input row-checkbox"
                                            value="{{ $state->id }}">
                                    </div>
                                </td>
                                <td class="ps-2 text-muted small">
                                    {{ $loop->iteration + (($states->currentPage() - 1) * $states->perPage()) }}
                                </td>
                                <td class="fw-semibold text-slate-800">{{ $state->name }}</td>
                                <td>
                                    @if($state->code)
                                        <span
                                            class="badge bg-secondary-subtle text-secondary px-2 py-1 fs-12">{{ $state->code }}</span>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('admin.districts.index', ['state_id' => $state->id]) }}"
                                        class="text-decoration-none">
                                        <span
                                            class="badge bg-primary-subtle text-primary px-2 py-1 fs-12 d-inline-flex align-items-center gap-1">
                                            <iconify-icon icon="solar:folder-with-files-bold-duotone"
                                                class="fs-12"></iconify-icon>
                                            {{ $state->districts_count }}
                                        </span>
                                    </a>
                                </td>
                                <td>
                                    <a href="{{ route('admin.pincodes.index', ['state_id' => $state->id]) }}"
                                        class="text-decoration-none">
                                        <span
                                            class="badge bg-info-subtle text-info px-2 py-1 fs-12 d-inline-flex align-items-center gap-1">
                                            <iconify-icon icon="solar:map-point-wave-bold-duotone"
                                                class="fs-12"></iconify-icon>
                                            {{ $state->pincodes_count }}
                                        </span>
                                    </a>
                                </td>
                                <td>
                                    @if($state->status === '1')
                                        <span class="badge bg-success-subtle text-success px-2 py-1 fs-12">Active</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-12">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $state->created_at->format('d M Y') }}</td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1">
                                        @permission('locations.edit')
                                        <button
                                            class="btn btn-sm btn-outline-info d-flex align-items-center justify-content-center p-1"
                                            data-bs-toggle="modal" data-bs-target="#editModal"
                                            data-id="{{ $state->id }}" data-name="{{ $state->name }}"
                                            data-code="{{ $state->code }}" data-status="{{ $state->status }}"
                                            style="width: 28px; height: 28px;">
                                            <iconify-icon icon="solar:pen-bold-duotone" class="fs-14"></iconify-icon>
                                        </button>
                                        @endpermission

                                        @permission('locations.delete')
                                        <form action="{{ route('admin.states.destroy', $state->id) }}" method="POST"
                                            class="d-inline">
                                            @csrf @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-danger confirm-action d-flex align-items-center justify-content-center p-1"
                                                data-confirm-message="Delete state '{{ $state->name }}'? All districts & pincodes will also be deleted."
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
                            <x-admin.empty-state icon="solar:map-bold-duotone" title="No States Found"
                                description="We couldn't find any state matching your query. Add your first state to get started."
                                colspan="9" />
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($states->hasPages())
                    <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                        <small class="text-muted">Showing {{ $states->firstItem() }}–{{ $states->lastItem() }} of
                            {{ $states->total() }}</small>
                        {{ $states->withQueryString()->links() }}
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
                <h5 class="modal-title fw-semibold text-slate-800">Add New State</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.states.store') }}" method="POST">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-slate-700 fw-medium">State Name <span
                                class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Maharashtra" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-slate-700 fw-medium">State Code <small
                                class="text-muted">(optional)</small></label>
                        <input type="text" name="code" class="form-control" placeholder="e.g. MH" maxlength="10">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-slate-700 fw-medium">Status <span
                                class="text-danger">*</span></label>
                        <select name="status" class="form-select" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Save State</button>
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
                <h5 class="modal-title fw-semibold text-slate-800">Edit State</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="editForm" method="POST">
                @csrf @method('PUT')
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-slate-700 fw-medium">State Name <span
                                class="text-danger">*</span></label>
                        <input type="text" name="name" id="editName" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-slate-700 fw-medium">State Code <small
                                class="text-muted">(optional)</small></label>
                        <input type="text" name="code" id="editCode" class="form-control" maxlength="10">
                    </div>
                    <div class="mb-3">
                        <label class="form-label text-slate-700 fw-medium">Status <span
                                class="text-danger">*</span></label>
                        <select name="status" id="editStatus" class="form-select" required>
                            <option value="1">Active</option>
                            <option value="0">Inactive</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update State</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Bulk Import Modal --}}
<div class="modal fade" id="bulkImportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title fw-semibold text-slate-800">
                    <iconify-icon icon="solar:upload-bold-duotone" class="align-middle me-1 text-primary fs-20"></iconify-icon>
                    Bulk Import States
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.locations.state.import') }}" method="POST" enctype="multipart/form-data" id="stateImportForm">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label text-slate-700 fw-medium">Upload Excel / CSV <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                        <small class="text-muted">Columns: <code>state_name</code> (Required), <code>state_code</code> (Optional). Duplicate state names or codes are automatically skipped.</small>
                    </div>

                    <div class="alert alert-info py-2 mb-0 small">
                        <strong>Sample format:</strong>
                        <br>Column A: <code>state_name</code> (e.g. Maharashtra, Delhi, Uttar Pradesh)
                        <br>Column B: <code>state_code</code> (e.g. MH, DL, UP)
                        <a href="{{ route('admin.locations.state.sample') }}" class="ms-2 fw-medium">
                            <iconify-icon icon="solar:download-bold-duotone" class="me-1"></iconify-icon>Download Sample Template
                        </a>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary" id="stateImportBtn">
                        <iconify-icon icon="solar:upload-bold-duotone" class="me-1"></iconify-icon> Import States
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Import Result Toast & Report Modal --}}
@if(session('import_result'))
    @php $r = session('import_result'); @endphp
    <div class="toast-container position-fixed top-0 end-0 p-3" style="z-index:9999">
        <div class="toast show align-items-center {{ $r['failed'] > 0 || $r['skipped'] > 0 ? 'text-bg-warning' : 'text-bg-success' }} border-0" role="alert">
            <div class="d-flex">
                <div class="toast-body">
                    @if($r['failed'] > 0 || $r['skipped'] > 0)
                        ⚠️ Import completed with warnings: <strong>{{ $r['inserted'] }} inserted</strong>,
                        {{ $r['skipped'] }} skipped (duplicates), {{ $r['failed'] }} failed.
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
                                    <th style="width: 180px;">Data Value</th>
                                    <th style="width: 100px;">Status</th>
                                    <th>Reason</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($r['details'] as $item)
                                <tr>
                                    <td class="ps-3 text-muted">#{{ $item['row'] }}</td>
                                    <td class="fw-semibold text-dark">{{ $item['data'] }}</td>
                                    <td>
                                        @if($item['status'] === 'Skipped')
                                            <span class="badge bg-warning-subtle text-warning">Skipped</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger">Failed</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">{{ $item['reason'] }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="modal-footer d-flex align-items-center justify-content-between">
                    <div>
                        <button type="button" class="btn btn-outline-danger btn-sm fw-bold me-1" onclick="exportModalTableToExcel('importReportModal', 'State_Import_Skipped_Failed_Records.xlsx', true)">
                            <iconify-icon icon="solar:download-square-linear" class="fs-16 align-middle me-1"></iconify-icon> Export Skipped &amp; Failed (.xlsx)
                        </button>
                        <button type="button" class="btn btn-outline-secondary btn-sm fw-bold" onclick="exportModalTableToExcel('importReportModal', 'State_Import_All_Records.xlsx', false)">
                            <iconify-icon icon="solar:download-square-linear" class="fs-16 align-middle me-1"></iconify-icon> Export All Logs (.xlsx)
                        </button>
                    </div>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var reportModal = document.getElementById('importReportModal');
            if (reportModal) {
                var myModal = new bootstrap.Modal(reportModal);
                myModal.show();
            }
        });
    </script>
    @endif
@endif
@endsection

@push('scripts')
    <script>
        var stateUpdateBaseUrl = '{{ parse_url(route("admin.states.update", 0), PHP_URL_PATH) }}'.replace('/0', '/');

        document.addEventListener('DOMContentLoaded', function () {
            const editModal = document.getElementById('editModal');
            if (editModal) {
                editModal.addEventListener('show.bs.modal', function (e) {
                    const btn = e.relatedTarget;
                    document.getElementById('editForm').action = stateUpdateBaseUrl + btn.dataset.id;
                    document.getElementById('editName').value = btn.dataset.name;
                    document.getElementById('editCode').value = btn.dataset.code || '';
                    document.getElementById('editStatus').value = btn.dataset.status;
                });
            }

            const importForm = document.getElementById('stateImportForm');
            if (importForm) {
                importForm.addEventListener('submit', function () {
                    const btn = document.getElementById('stateImportBtn');
                    if (btn) {
                        btn.disabled = true;
                        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Importing...';
                    }
                });
            }
        });
    </script>
@endpush