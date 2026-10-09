@extends('layouts.admin')

@section('title', 'HSN Master')
@section('page-title', 'HSN Master')

@section('content')
    <!-- Common Page Action Header -->
    <x-admin.page-header 
        title="HSN Master" 
        subtitle="Manage Harmonized System of Nomenclature (HSN) codes, GST rates, and descriptions."
        :actions="[
            [
                'label' => 'Add HSN Code',
                'icon' => 'solar:add-circle-bold-duotone',
                'target' => '#createModal',
                'permission' => 'hsns.create',
                'btnClass' => 'btn-primary'
            ],
            [
                'label' => 'Bulk Upload',
                'icon' => 'solar:upload-bold-duotone',
                'target' => '#bulkImportModal',
                'permission' => 'hsns.create',
                'btnClass' => 'btn-outline-primary'
            ],
            [
                'label' => 'Export CSV',
                'icon' => 'solar:document-text-bold-duotone',
                'onclick' => 'triggerExport(\'csv\')',
                'permission' => 'hsns.view',
                'btnClass' => 'btn-outline-success'
            ]
        ]"
    />

    <!-- Reusable Table Filters -->
    <x-admin.table-filters 
        action="{{ route('admin.hsns.index') }}" 
        :search="$search" 
        placeholder="Search HSN code or description..." 
        :limit="$limit" 
        :sort="$sort" 
        :order="$order"
        :bulk-actions="[
            'delete' => 'Delete Selected',
            'status_active' => 'Activate Selected',
            'status_inactive' => 'Deactivate Selected'
        ]"
    >
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
                                    <x-admin.table-header field="hsn_code" :sort="$sort" :order="$order">HSN Code</x-admin.table-header>
                                    <x-admin.table-header field="gst_percentage" :sort="$sort" :order="$order">GST Rate</x-admin.table-header>
                                    <x-admin.table-header field="description" :sort="$sort" :order="$order">Description</x-admin.table-header>
                                    <x-admin.table-header field="status" :sort="$sort" :order="$order">Status</x-admin.table-header>
                                    <x-admin.table-header field="created_at" :sort="$sort" :order="$order">Created At</x-admin.table-header>
                                    <th class="text-end pe-3" style="width: 120px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($hsns as $hsn)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="form-check fs-15">
                                                <input type="checkbox" class="form-check-input row-checkbox" value="{{ $hsn->id }}">
                                            </div>
                                        </td>
                                        <td class="ps-2 text-muted small">{{ $loop->iteration + (($hsns->currentPage() - 1) * $hsns->perPage()) }}</td>
                                        <td class="fw-bold text-dark">{{ $hsn->hsn_code }}</td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info px-2 py-1 fs-12 fw-semibold">
                                                {{ (int) $hsn->gst_percentage }}
                                            </span>
                                        </td>
                                        <td class="text-wrap text-slate-800" style="max-width: 280px; overflow: hidden; text-overflow: ellipsis;">
                                            {{ $hsn->description ?: '—' }}
                                        </td>
                                        <td>
                                            <form action="{{ route('admin.hsns.toggle-status', $hsn->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                @if($hsn->status === '1')
                                                    <button type="submit" class="btn btn-link p-0 border-0 text-decoration-none" title="Click to Deactivate">
                                                        <span class="badge bg-success-subtle text-success px-2.5 py-1 fs-12">
                                                            <iconify-icon icon="solar:check-circle-bold-duotone" class="align-middle me-1"></iconify-icon> Active
                                                        </span>
                                                    </button>
                                                @else
                                                    <button type="submit" class="btn btn-link p-0 border-0 text-decoration-none" title="Click to Activate">
                                                        <span class="badge bg-danger-subtle text-danger px-2.5 py-1 fs-12">
                                                            <iconify-icon icon="solar:close-circle-bold-duotone" class="align-middle me-1"></iconify-icon> Inactive
                                                        </span>
                                                    </button>
                                                @endif
                                            </form>
                                        </td>
                                        <td class="text-muted small">{{ $hsn->created_at->format('d M Y') }}</td>
                                        <td class="text-end pe-3">
                                            <div class="d-inline-flex gap-1">
                                                {{-- View Button --}}
                                                <button class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center p-1" 
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#viewModal"
                                                    data-code="{{ $hsn->hsn_code }}"
                                                    data-gst="{{ (int) $hsn->gst_percentage }}"
                                                    data-desc="{{ $hsn->description ?: '—' }}"
                                                    data-status="{{ $hsn->status === '1' ? 'Active' : 'Inactive' }}"
                                                    data-creator="{{ $hsn->creator ? $hsn->creator->name : 'System' }}"
                                                    data-updater="{{ $hsn->updater ? $hsn->updater->name : '—' }}"
                                                    data-created="{{ $hsn->created_at->format('d M Y, h:i A') }}"
                                                    data-updated="{{ $hsn->updated_at->format('d M Y, h:i A') }}"
                                                    style="width: 28px; height: 28px;" title="View Details">
                                                    <iconify-icon icon="solar:eye-bold-duotone" class="fs-14"></iconify-icon>
                                                </button>

                                                {{-- Edit Button --}}
                                                @permission('hsns.edit')
                                                <button class="btn btn-sm btn-outline-info d-flex align-items-center justify-content-center p-1" 
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#editModal"
                                                    data-id="{{ $hsn->id }}"
                                                    data-code="{{ $hsn->hsn_code }}"
                                                    data-gst="{{ $hsn->gst_percentage }}"
                                                    data-desc="{{ $hsn->description }}"
                                                    data-status="{{ $hsn->status }}"
                                                    style="width: 28px; height: 28px;" title="Edit">
                                                    <iconify-icon icon="solar:pen-bold-duotone" class="fs-14"></iconify-icon>
                                                </button>
                                                @endpermission

                                                {{-- Delete Button --}}
                                                @permission('hsns.delete')
                                                <form action="{{ route('admin.hsns.destroy', $hsn->id) }}" method="POST" class="delete-form d-inline">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="button" class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center p-1 confirm-delete-btn" 
                                                        style="width: 28px; height: 28px;" title="Delete">
                                                        <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="fs-14"></iconify-icon>
                                                    </button>
                                                </form>
                                                @endpermission
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-4 text-muted">
                                            <iconify-icon icon="solar:notes-bold-duotone" class="fs-24 d-block mx-auto mb-1"></iconify-icon>
                                            No HSN codes found.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($hsns->hasPages())
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                            <small class="text-muted">Showing {{ $hsns->firstItem() }}–{{ $hsns->lastItem() }} of {{ $hsns->total() }}</small>
                            {{ $hsns->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Create Modal -->
    <div class="modal fade" id="createModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form action="{{ route('admin.hsns.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Add New HSN Code</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">HSN Code</label>
                            <input type="text" name="hsn_code" class="form-control" placeholder="e.g. 85171200" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">GST Rate (%)</label>
                            <input type="number" name="gst_percentage" class="form-control" step="1" min="0" max="100" placeholder="e.g. 18" value="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" class="form-control" rows="3" placeholder="Enter HSN description (optional)"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Status</label>
                            <select name="status" class="form-select" required>
                                <option value="1" selected>Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Create HSN Code</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Modal -->
    <div class="modal fade" id="editModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="editForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Edit HSN Code</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required">HSN Code</label>
                            <input type="text" name="hsn_code" id="editCode" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">GST Rate (%)</label>
                            <input type="number" name="gst_percentage" id="editGst" class="form-control" step="1" min="0" max="100" placeholder="e.g. 18" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Description</label>
                            <textarea name="description" id="editDesc" class="form-control" rows="3"></textarea>
                        </div>
                        <div class="mb-3">
                            <label class="form-label required">Status</label>
                            <select name="status" id="editStatus" class="form-select" required>
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-primary">Update HSN Code</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- View Modal -->
    <div class="modal fade" id="viewModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title">HSN Code Details</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="text-center mb-3">
                        <h3 class="mb-0 text-primary" id="viewCode">85171200</h3>
                        <p class="text-muted mt-2 text-wrap px-3" id="viewDesc">Description details go here...</p>
                    </div>
                    <table class="table table-sm table-bordered mt-3">
                        <tr>
                            <th class="bg-light" style="width: 40%;">GST Rate</th>
                            <td id="viewGst" class="fw-bold text-info">18</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Status</th>
                            <td id="viewStatus">Active</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Created By</th>
                            <td id="viewCreator">System</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Created At</th>
                            <td id="viewCreated">Date</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Updated By</th>
                            <td id="viewUpdater">—</td>
                        </tr>
                        <tr>
                            <th class="bg-light">Updated At</th>
                            <td id="viewUpdated">Date</td>
                        </tr>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Import Modal -->
    <div class="modal fade" id="bulkImportModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold text-slate-800">
                        <iconify-icon icon="solar:upload-bold-duotone" class="me-2 text-primary"></iconify-icon>
                        Bulk HSN Code Upload
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="{{ route('admin.hsns.import') }}" method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Step 1 &mdash; Upload Excel / CSV <span class="text-danger">*</span></label>
                            <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                            <small class="text-muted">Columns: <code>hsn_code</code>, <code>gst_percentage</code>, <code>description</code> &mdash; duplicates skipped.</small>
                        </div>

                        <div class="alert alert-info py-2 mb-0 small d-flex align-items-center justify-content-between">
                            <span><strong>Step 2 &mdash;</strong> Use the standard HSN code template.</span>
                            <a href="{{ route('admin.hsns.sample') }}" class="fw-medium d-inline-flex align-items-center gap-1 ms-2">
                                <iconify-icon icon="solar:download-bold-duotone" class="fs-14"></iconify-icon>Download Sample
                            </a>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary" id="importBtn">
                            <iconify-icon icon="solar:upload-bold-duotone" class="me-1"></iconify-icon> Import HSN Codes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Import Result Toast & Modal -->
    @if(session()->has('import_result'))
        @php $r = session('import_result'); @endphp
        @if(($r['failed'] ?? 0) > 0 || ($r['skipped'] ?? 0) > 0)
        <!-- Details Modal -->
        <div class="modal fade" id="importReportModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-warning bg-opacity-10">
                        <h5 class="modal-title text-warning-emphasis d-flex align-items-center gap-1">
                            <iconify-icon icon="solar:danger-bold-duotone"></iconify-icon>
                            Import Report: {{ $r['inserted'] ?? 0 }} Imported, {{ $r['skipped'] ?? 0 }} Skipped, {{ $r['failed'] ?? 0 }} Failed
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="bg-light p-3 rounded mb-3">
                            <div class="row text-center">
                                <div class="col-3 border-end">
                                    <div class="text-muted small">Total Rows</div>
                                    <div class="fs-18 fw-bold text-dark">{{ $r['total'] ?? 0 }}</div>
                                </div>
                                <div class="col-3 border-end">
                                    <div class="text-muted small">Inserted</div>
                                    <div class="fs-18 fw-bold text-success">{{ $r['inserted'] ?? 0 }}</div>
                                </div>
                                <div class="col-3 border-end">
                                    <div class="text-muted small">Skipped</div>
                                    <div class="fs-18 fw-bold text-warning">{{ $r['skipped'] ?? 0 }}</div>
                                </div>
                                <div class="col-3">
                                    <div class="p-2 border rounded bg-white">
                                        <div class="text-muted small">Failed</div>
                                        <div class="fs-18 fw-bold text-danger">{{ $r['failed'] ?? 0 }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="table-responsive" style="max-height: 350px; overflow-y: auto;">
                            <table class="table table-hover table-centered mb-0 align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3" style="width: 80px;">Row #</th>
                                        <th style="width: 150px;">HSN Code</th>
                                        <th style="width: 100px;">Status</th>
                                        <th class="pe-3">Reason / Error</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(($r['details'] ?? []) as $det)
                                        @if(($det['status'] ?? '') === 'Skipped' || ($det['status'] ?? '') === 'Failed')
                                            <tr>
                                                <td class="ps-3 fw-semibold">{{ $det['row'] ?? '-' }}</td>
                                                <td><code class="text-dark">{{ $det['data'] ?? '' }}</code></td>
                                                <td>
                                                    @if(($det['status'] ?? '') === 'Failed')
                                                        <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1">Failed</span>
                                                    @else
                                                        <span class="badge bg-warning bg-opacity-10 text-warning px-2 py-1">Skipped</span>
                                                    @endif
                                                </td>
                                                <td class="pe-3 text-muted text-wrap">{{ $det['reason'] ?? '' }}</td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="modal-footer d-flex align-items-center justify-content-between">
                        <div>
                            <button type="button" class="btn btn-outline-danger btn-sm fw-bold me-1" onclick="exportModalTableToExcel('importReportModal', 'HSN_Import_Skipped_Failed_Records.xlsx', true)">
                                <iconify-icon icon="solar:download-square-linear" class="fs-16 align-middle me-1"></iconify-icon> Export Skipped &amp; Failed (.xlsx)
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm fw-bold" onclick="exportModalTableToExcel('importReportModal', 'HSN_Import_All_Records.xlsx', false)">
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
var hsnUpdateBaseUrl = '{{ parse_url(route("admin.hsns.update", 0), PHP_URL_PATH) }}'.replace('/0', '/');

document.addEventListener('DOMContentLoaded', function () {
    // Edit modal config
    var editModalEl = document.getElementById('editModal');
    if (editModalEl) {
        editModalEl.addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            document.getElementById('editForm').action = hsnUpdateBaseUrl + btn.dataset.id;
            document.getElementById('editCode').value = btn.dataset.code;
            document.getElementById('editGst').value = btn.dataset.gst;
            document.getElementById('editDesc').value = btn.dataset.desc === '—' ? '' : btn.dataset.desc;
            document.getElementById('editStatus').value = btn.dataset.status;
        });
    }

    // View modal config
    var viewModalEl = document.getElementById('viewModal');
    if (viewModalEl) {
        viewModalEl.addEventListener('show.bs.modal', function (e) {
            var btn = e.relatedTarget;
            document.getElementById('viewCode').innerText = btn.dataset.code;
            document.getElementById('viewGst').innerText = btn.dataset.gst;
            document.getElementById('viewDesc').innerText = btn.dataset.desc;
            document.getElementById('viewStatus').innerText = btn.dataset.status;
            document.getElementById('viewCreator').innerText = btn.dataset.creator;
            document.getElementById('viewCreated').innerText = btn.dataset.created;
            document.getElementById('viewUpdater').innerText = btn.dataset.updater;
            document.getElementById('viewUpdated').innerText = btn.dataset.updated;
        });
    }

    // Confirm Delete
    var deleteForms = document.querySelectorAll('.delete-form');
    deleteForms.forEach(function (form) {
        var btn = form.querySelector('.confirm-delete-btn');
        btn.addEventListener('click', function () {
            Swal.fire({
                title: 'Are you sure?',
                text: 'This will permanently delete the HSN code.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });
});

// Trigger bulk export
function triggerExport(format) {
    var selectedIds = [];
    document.querySelectorAll('.row-checkbox:checked').forEach(function (chk) {
        selectedIds.push(chk.value);
    });

    var url = new URL(window.location.href);
    url.searchParams.set('export', '1');
    if (selectedIds.length > 0) {
        url.searchParams.set('ids', selectedIds.join(','));
    } else {
        url.searchParams.delete('ids');
    }
    window.location.href = url.toString();
}
</script>
@endpush
