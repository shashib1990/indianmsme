@extends('layouts.vendor')

@section('title', 'Manage Delivery Areas')
@section('page-title', 'Pickup Store Delivery Areas')

@section('content')
    <div class="row">
        <div class="col-12 mb-3">
            <a href="{{ route('vendor.pickup-stores.index') }}" class="btn btn-sm btn-outline-secondary">
                <iconify-icon icon="solar:arrow-left-bold-duotone" class="align-middle me-1"></iconify-icon>
                Back to Stores
            </a>
            <span class="ms-2 fs-18 fw-bold text-slate-800">
                Delivery Areas for: <strong class="text-primary">{{ $store->pickup_store_name }} ({{ $store->pickup_store_code }})</strong>
            </span>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-18 align-middle text-success"></iconify-icon>
                <span class="fw-semibold text-success small">{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="solar:danger-bold-duotone" class="fs-18 align-middle text-danger"></iconify-icon>
                <span class="fw-semibold text-danger small">{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="solar:danger-bold-duotone" class="fs-18 align-middle text-danger"></iconify-icon>
                <span class="fw-semibold text-danger small">{{ $errors->first() }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row">
        <!-- Delivery Coverage Selector -->
        <div class="col-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title fw-bold text-slate-800 mb-0">Active Delivery Coverage Type</h5>
                    <p class="text-muted small mb-0 mt-1">Specify how this store's delivery range is configured. Switching coverage types will deactivate configured areas of other types.</p>
                </div>
                <div class="card-body">
                    <form action="{{ route('vendor.pickup-stores.delivery-areas.update-coverage', $store->id) }}" method="POST" id="coverageTypeForm">
                        @csrf
                        <div class="row g-3">
                            <!-- Option 1: Pan India -->
                            <div class="col-md-3">
                                <div class="border rounded p-3 text-center h-100 cursor-pointer coverage-card {{ $activeCoverage === 'pan_india' ? 'border-primary bg-primary-subtle bg-opacity-10' : '' }}" onclick="selectCoverage('pan_india')">
                                    <input type="radio" name="coverage_type" value="pan_india" class="d-none" id="cov_pan_india" {{ $activeCoverage === 'pan_india' ? 'checked' : '' }}>
                                    <iconify-icon icon="solar:global-bold-duotone" class="fs-32 text-primary mb-2"></iconify-icon>
                                    <h6 class="fw-bold text-slate-800 mb-1">Pan India</h6>
                                    <p class="text-muted fs-11 mb-0">Deliver across all states in India</p>
                                </div>
                            </div>
                            <!-- Option 2: State Wise -->
                            <div class="col-md-3">
                                <div class="border rounded p-3 text-center h-100 cursor-pointer coverage-card {{ $activeCoverage === 'state' ? 'border-primary bg-primary-subtle bg-opacity-10' : '' }}" onclick="selectCoverage('state')">
                                    <input type="radio" name="coverage_type" value="state" class="d-none" id="cov_state" {{ $activeCoverage === 'state' ? 'checked' : '' }}>
                                    <iconify-icon icon="solar:map-bold-duotone" class="fs-32 text-warning mb-2"></iconify-icon>
                                    <h6 class="fw-bold text-slate-800 mb-1">State Wise</h6>
                                    <p class="text-muted fs-11 mb-0">Deliver to selected states</p>
                                </div>
                            </div>
                            <!-- Option 3: District Wise -->
                            <div class="col-md-3">
                                <div class="border rounded p-3 text-center h-100 cursor-pointer coverage-card {{ $activeCoverage === 'district' ? 'border-primary bg-primary-subtle bg-opacity-10' : '' }}" onclick="selectCoverage('district')">
                                    <input type="radio" name="coverage_type" value="district" class="d-none" id="cov_district" {{ $activeCoverage === 'district' ? 'checked' : '' }}>
                                    <iconify-icon icon="solar:map-arrow-bold-duotone" class="fs-32 text-success mb-2"></iconify-icon>
                                    <h6 class="fw-bold text-slate-800 mb-1">District Wise</h6>
                                    <p class="text-muted fs-11 mb-0">Deliver to selected districts</p>
                                </div>
                            </div>
                            <!-- Option 4: Pincode Wise -->
                            <div class="col-md-3">
                                <div class="border rounded p-3 text-center h-100 cursor-pointer coverage-card {{ $activeCoverage === 'pincode' ? 'border-primary bg-primary-subtle bg-opacity-10' : '' }}" onclick="selectCoverage('pincode')">
                                    <input type="radio" name="coverage_type" value="pincode" class="d-none" id="cov_pincode" {{ $activeCoverage === 'pincode' ? 'checked' : '' }}>
                                    <iconify-icon icon="solar:pin-place-bold-duotone" class="fs-32 text-danger mb-2"></iconify-icon>
                                    <h6 class="fw-bold text-slate-800 mb-1">Pincode Wise</h6>
                                    <p class="text-muted fs-11 mb-0">Deliver to specific pincodes</p>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Configuration Panel -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title fw-bold text-slate-800 mb-0">Add Delivery Location</h5>
                </div>
                <div class="card-body">
                    @if($activeCoverage === 'pan_india')
                        <div class="text-center py-4">
                            <iconify-icon icon="solar:global-bold-duotone" class="fs-48 text-primary mb-3"></iconify-icon>
                            <h5 class="fw-bold text-slate-800">Pan India Delivery Enabled</h5>
                            <p class="text-muted fs-13">This store is configured to deliver nationwide. No custom area setup is needed.</p>
                        </div>
                    @elseif($activeCoverage === 'state')
                        <!-- State Wise Form -->
                        <form action="{{ route('vendor.pickup-stores.delivery-areas.store', $store->id) }}" method="POST" class="mb-4">
                            @csrf
                            <input type="hidden" name="coverage_type" value="state">
                            
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label required mb-0">Select State(s)</label>
                                    <div>
                                        <button type="button" id="btnSelectAllStates" class="btn btn-link btn-sm p-0 text-decoration-none me-2 text-primary fw-semibold" style="font-size: 11px;">Select All</button>
                                        <button type="button" id="btnDeselectAllStates" class="btn btn-link btn-sm p-0 text-decoration-none text-muted" style="font-size: 11px;">Clear</button>
                                    </div>
                                </div>
                                <select id="formStateWise" name="state_ids[]" class="form-select" multiple required style="min-height: 120px;">
                                    @foreach($states as $st)
                                        <option value="{{ $st->id }}">{{ $st->name }}</option>
                                    @endforeach
                                </select>
                                <small class="text-muted d-block mt-1">Select one or multiple states to add to delivery coverage.</small>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <iconify-icon icon="solar:add-circle-bold-duotone" class="align-middle me-1"></iconify-icon>
                                Add Selected State(s)
                            </button>
                        </form>

                        <hr>

                        <!-- State Excel Bulk Import -->
                        <form action="{{ route('vendor.pickup-stores.delivery-areas.bulk-import', $store->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <h6 class="fw-bold text-slate-800 mb-2">Excel/CSV Bulk Import</h6>
                            
                            <div class="mb-3">
                                <label class="form-label required">Choose File (.xlsx, .csv)</label>
                                <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                                <small class="text-muted d-block mt-1">Excel/CSV should contain a column named <strong>State Name</strong> or state code.</small>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-outline-success w-100">
                                    <iconify-icon icon="solar:upload-bold-duotone" class="align-middle me-1"></iconify-icon>
                                    Import States
                                </button>
                                <a href="{{ route('vendor.pickup-stores.delivery-areas.sample', ['id' => $store->id, 'type' => 'state']) }}" class="btn btn-outline-secondary w-100" title="Download Sample CSV">
                                    <iconify-icon icon="solar:file-download-bold-duotone" class="align-middle me-1"></iconify-icon>
                                    Sample CSV
                                </a>
                            </div>
                        </form>
                    @elseif($activeCoverage === 'district')
                        <!-- District Wise Form -->
                        <form action="{{ route('vendor.pickup-stores.delivery-areas.store', $store->id) }}" method="POST" class="mb-4">
                            @csrf
                            <input type="hidden" name="coverage_type" value="district">
                            
                            <div class="mb-3">
                                <label class="form-label required">Select State</label>
                                <select id="formState" name="state_id" class="form-select" required>
                                    <option value="">-- Choose State --</option>
                                    @foreach($states as $st)
                                        <option value="{{ $st->id }}">{{ $st->name }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <label class="form-label required mb-0">Select District(s)</label>
                                    <div id="districtActionBtns" style="display: none;">
                                        <button type="button" id="btnSelectAllDistricts" class="btn btn-link btn-sm p-0 text-decoration-none me-2 text-primary fw-semibold" style="font-size: 11px;">Select All</button>
                                        <button type="button" id="btnDeselectAllDistricts" class="btn btn-link btn-sm p-0 text-decoration-none text-muted" style="font-size: 11px;">Clear</button>
                                    </div>
                                </div>
                                <select id="formDistrict" name="district_ids[]" class="form-select" multiple required style="min-height: 140px;">
                                    <option value="" disabled>-- Choose State First --</option>
                                </select>
                                <small class="text-muted d-block mt-1">Select one or multiple districts, or click "Select All" to add all districts of the state.</small>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <iconify-icon icon="solar:add-circle-bold-duotone" class="align-middle me-1"></iconify-icon>
                                Add Selected District(s)
                            </button>
                        </form>

                        <hr>

                        <!-- District Excel Bulk Import -->
                        <form action="{{ route('vendor.pickup-stores.delivery-areas.bulk-import', $store->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <h6 class="fw-bold text-slate-800 mb-2">Excel/CSV Bulk Import</h6>
                            
                            <div class="mb-3">
                                <label class="form-label required">Choose File (.xlsx, .csv)</label>
                                <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                                <small class="text-muted d-block mt-1">Excel/CSV should contain columns named <strong>District Name</strong> and optional <strong>State Name</strong>.</small>
                            </div>

                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-outline-success w-100">
                                    <iconify-icon icon="solar:upload-bold-duotone" class="align-middle me-1"></iconify-icon>
                                    Import Districts
                                </button>
                                <a href="{{ route('vendor.pickup-stores.delivery-areas.sample', ['id' => $store->id, 'type' => 'district']) }}" class="btn btn-outline-secondary w-100" title="Download Sample CSV">
                                    <iconify-icon icon="solar:file-download-bold-duotone" class="align-middle me-1"></iconify-icon>
                                    Sample CSV
                                </a>
                            </div>
                        </form>
                    @elseif($activeCoverage === 'pincode')
                        <!-- Pincode Wise Form -->
                        <form action="{{ route('vendor.pickup-stores.delivery-areas.store', $store->id) }}" method="POST" class="mb-4">
                            @csrf
                            <input type="hidden" name="coverage_type" value="pincode">
                            
                            <div class="mb-3">
                                <label class="form-label required">Enter Pincode(s)</label>
                                <textarea name="pincode" class="form-control" rows="3" placeholder="Enter 6-digit Indian pincode(s), separated by commas or new lines (e.g. 226001, 226002)" required></textarea>
                                <small class="text-muted d-block mt-1">Enter multiple pincodes separated by comma or new line.</small>
                            </div>

                            <button type="submit" class="btn btn-primary w-100">
                                <iconify-icon icon="solar:add-circle-bold-duotone" class="align-middle me-1"></iconify-icon>
                                Add Pincode(s)
                            </button>
                        </form>

                        <hr>

                        <!-- Pincode Excel Bulk Import -->
                        <form action="{{ route('vendor.pickup-stores.delivery-areas.bulk-import', $store->id) }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <h6 class="fw-bold text-slate-800 mb-2">Excel Bulk Import</h6>
                            
                            <div class="mb-3">
                                <label class="form-label required">Choose File (.xlsx, .csv)</label>
                                <input type="file" name="file" class="form-control" accept=".xlsx,.xls,.csv" required>
                                <small class="text-muted d-block mt-1">Excel must have a header column named <strong>Pincode</strong>.</small>
                            </div>

                            <div class="d-flex flex-wrap gap-2">
                                <button type="submit" class="btn btn-outline-success flex-fill">
                                    <iconify-icon icon="solar:upload-bold-duotone" class="align-middle me-1"></iconify-icon>
                                    Import
                                </button>
                                <a href="{{ route('vendor.pickup-stores.delivery-areas.sample', ['id' => $store->id, 'type' => 'pincode']) }}" class="btn btn-outline-secondary flex-fill" title="Download Sample CSV">
                                    <iconify-icon icon="solar:file-download-bold-duotone" class="align-middle me-1"></iconify-icon>
                                    Sample CSV
                                </a>
                                <a href="{{ route('vendor.pickup-stores.delivery-areas.export', $store->id) }}" class="btn btn-outline-info flex-fill" title="Export current active pincodes to excel">
                                    <iconify-icon icon="solar:download-bold-duotone" class="align-middle me-1"></iconify-icon>
                                    Export
                                </a>
                            </div>
                        </form>
                    @endif
                </div>
            </div>
        </div>

        <!-- Listings / Table Panel -->
        <div class="col-md-8 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-white border-bottom py-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                    <h5 class="card-title fw-bold text-slate-800 mb-0">Configured Areas</h5>
                    
                    <!-- Search & Filter Form -->
                    <form action="{{ route('vendor.pickup-stores.delivery-areas.index', $store->id) }}" method="GET" class="d-flex flex-wrap align-items-center gap-2">
                        <!-- Search input -->
                        <div class="input-group input-group-sm" style="max-width: 200px;">
                            <input type="text" name="search" class="form-control" placeholder="Search state, district, pin..." value="{{ $search }}">
                            <button type="submit" class="btn btn-outline-secondary"><i class="bx bx-search"></i></button>
                        </div>

                        <!-- Status Filter -->
                        <select name="status" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                            <option value="">All Statuses</option>
                            <option value="Active" {{ $status == 'Active' ? 'selected' : '' }}>Active</option>
                            <option value="Inactive" {{ $status == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>

                        <!-- Coverage Type Filter -->
                        <select name="coverage_type" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                            <option value="">All Types</option>
                            <option value="pan_india" {{ $coverage_type == 'pan_india' ? 'selected' : '' }}>Pan India</option>
                            <option value="state" {{ $coverage_type == 'state' ? 'selected' : '' }}>State Wise</option>
                            <option value="district" {{ $coverage_type == 'district' ? 'selected' : '' }}>District Wise</option>
                            <option value="pincode" {{ $coverage_type == 'pincode' ? 'selected' : '' }}>Pincode Wise</option>
                        </select>
                    </form>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0 text-nowrap align-middle">
                            <thead class="bg-light bg-opacity-50 border-top border-bottom">
                                <tr>
                                    <th class="ps-3" style="width: 60px;">#</th>
                                    <th>Coverage Type</th>
                                    <th>State</th>
                                    <th>District</th>
                                    <th>Pincode</th>
                                    <th>Status</th>
                                    <th>Created Date</th>
                                    <th class="text-end pe-3" style="width: 100px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($deliveryAreas as $area)
                                    <tr>
                                        <td class="ps-3 text-muted small">{{ $loop->iteration + (($deliveryAreas->currentPage() - 1) * $deliveryAreas->perPage()) }}</td>
                                        <td>
                                            @if($area->coverage_type === 'pan_india')
                                                <span class="badge bg-primary-subtle text-primary">Pan India</span>
                                            @elseif($area->coverage_type === 'state')
                                                <span class="badge bg-warning-subtle text-warning">State Wise</span>
                                            @elseif($area->coverage_type === 'district')
                                                <span class="badge bg-success-subtle text-success">District Wise</span>
                                            @elseif($area->coverage_type === 'pincode')
                                                <span class="badge bg-danger-subtle text-danger">Pincode Wise</span>
                                            @endif
                                        </td>
                                        <td>{{ $area->state?->name ?: '—' }}</td>
                                        <td>{{ $area->district?->name ?: '—' }}</td>
                                        <td>
                                            @if($area->pincode)
                                                <span class="font-monospace fw-semibold text-slate-800">{{ $area->pincode }}</span>
                                            @else
                                                —
                                            @endif
                                        </td>
                                        <td>
                                            @if($area->status === 'Active')
                                                <span class="badge bg-success-subtle text-success fs-11 d-inline-flex align-items-center gap-1 cursor-pointer" onclick="toggleAreaStatus('{{ $area->id }}', '{{ $area->status }}')">
                                                    <i class="bx bxs-circle fs-8"></i> Active
                                                </span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger fs-11 d-inline-flex align-items-center gap-1 cursor-pointer" onclick="toggleAreaStatus('{{ $area->id }}', '{{ $area->status }}')">
                                                    <i class="bx bxs-circle fs-8"></i> Inactive
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">{{ $area->created_at->format('d M Y') }}</td>
                                        <td class="text-end pe-3">
                                            <form action="{{ route('vendor.pickup-stores.delivery-areas.destroy', [$store->id, $area->id]) }}" method="POST" class="d-inline delete-area-form">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center p-0" 
                                                        style="width: 28px; height: 28px; cursor: pointer;" 
                                                        data-bs-toggle="tooltip" data-bs-placement="top" title="Remove Area">
                                                    <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="fs-14" style="pointer-events: none;"></iconify-icon>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <iconify-icon icon="solar:routing-bold-duotone" class="fs-48 mb-2"></iconify-icon>
                                            <h5>No Delivery Coverage Configured</h5>
                                            <p class="mb-0">Choose a coverage type above to specify delivery range.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($deliveryAreas->total() > 0)
                        <div class="d-flex justify-content-between align-items-center p-3 border-top">
                            <span class="text-muted small">Showing {{ $deliveryAreas->firstItem() }} to {{ $deliveryAreas->lastItem() }} of {{ $deliveryAreas->total() }} entries</span>
                            @if($deliveryAreas->hasPages())
                                {{ $deliveryAreas->links() }}
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
function selectCoverage(type) {
    const radio = document.getElementById('cov_' + type);
    if (radio && !radio.checked) {
        Swal.fire({
            title: 'Change Active Delivery Coverage?',
            text: 'Changing the active delivery coverage type will automatically set other types to Inactive. Do you want to continue?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ea580c',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Yes, Change Type'
        }).then((result) => {
            if (result.isConfirmed) {
                radio.checked = true;
                document.getElementById('coverageTypeForm').submit();
            }
        });
    }
}

document.addEventListener('DOMContentLoaded', function () {
    // Delete Confirmation
    const deleteForms = document.querySelectorAll('.delete-area-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Remove Delivery Area?',
                text: 'Are you sure you want to remove this delivery coverage area?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Remove'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // 1. Initialize TomSelect for State Wise form if TomSelect is loaded
    const formStateWise = document.getElementById('formStateWise');
    let stateTomSelect = null;
    if (formStateWise && typeof TomSelect !== 'undefined') {
        stateTomSelect = new TomSelect('#formStateWise', {
            plugins: ['remove_button', 'clear_button'],
            maxItems: null,
            placeholder: '-- Choose State(s) --',
        });
    }

    document.getElementById('btnSelectAllStates')?.addEventListener('click', function () {
        if (stateTomSelect) {
            const allValues = Object.keys(stateTomSelect.options);
            stateTomSelect.setValue(allValues);
        } else if (formStateWise) {
            for (let opt of formStateWise.options) {
                opt.selected = true;
            }
        }
    });

    document.getElementById('btnDeselectAllStates')?.addEventListener('click', function () {
        if (stateTomSelect) {
            stateTomSelect.clear();
        } else if (formStateWise) {
            for (let opt of formStateWise.options) {
                opt.selected = false;
            }
        }
    });

    // 2. Dynamic District dropdown loading with TomSelect / Native Multiple support
    const formState = document.getElementById('formState');
    const formDistrict = document.getElementById('formDistrict');
    const districtActionBtns = document.getElementById('districtActionBtns');
    let districtTomSelect = null;

    if (formDistrict && typeof TomSelect !== 'undefined') {
        districtTomSelect = new TomSelect('#formDistrict', {
            plugins: ['remove_button', 'clear_button'],
            maxItems: null,
            placeholder: '-- Select State First --',
        });
    }

    if (formState && formDistrict) {
        formState.addEventListener('change', function () {
            const stateId = this.value;
            if (!stateId) {
                if (districtTomSelect) {
                    districtTomSelect.clear();
                    districtTomSelect.clearOptions();
                } else {
                    formDistrict.innerHTML = '<option value="" disabled>-- Choose State First --</option>';
                }
                if (districtActionBtns) districtActionBtns.style.display = 'none';
                return;
            }

            fetch(`/api/locations/districts?state_id=${stateId}`)
                .then(res => res.json())
                .then(data => {
                    if (districtTomSelect) {
                        districtTomSelect.clear();
                        districtTomSelect.clearOptions();
                        data.forEach(d => {
                            districtTomSelect.addOption({ value: d.id, text: d.name });
                        });
                        districtTomSelect.refreshOptions(false);
                    } else {
                        formDistrict.innerHTML = '';
                        data.forEach(d => {
                            const option = document.createElement('option');
                            option.value = d.id;
                            option.textContent = d.name;
                            formDistrict.appendChild(option);
                        });
                    }
                    if (districtActionBtns) districtActionBtns.style.display = 'inline-block';
                });
        });

        document.getElementById('btnSelectAllDistricts')?.addEventListener('click', function () {
            if (districtTomSelect) {
                const allValues = Object.keys(districtTomSelect.options);
                districtTomSelect.setValue(allValues);
            } else {
                for (let opt of formDistrict.options) {
                    opt.selected = true;
                }
            }
        });

        document.getElementById('btnDeselectAllDistricts')?.addEventListener('click', function () {
            if (districtTomSelect) {
                districtTomSelect.clear();
            } else {
                for (let opt of formDistrict.options) {
                    opt.selected = false;
                }
            }
        });
    }
});

// Toggle status AJAX
function toggleAreaStatus(areaId, currentStatus) {
    const newStatus = currentStatus === 'Active' ? 'Inactive' : 'Active';
    fetch('{{ route("vendor.pickup-stores.delivery-areas.status", ["id" => $store->id, "areaId" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', areaId), {
        method: 'PATCH',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
        },
        body: JSON.stringify({ status: newStatus })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            window.showToast(data.message, 'success');
            setTimeout(() => window.location.reload(), 1000);
        } else {
            window.showToast(data.error || 'Failed to toggle status', 'error');
        }
    })
    .catch(() => {
        window.showToast('Something went wrong', 'error');
    });
}
</script>

<style>
.coverage-card {
    transition: all 0.25s ease-in-out;
}
.coverage-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.05);
}
</style>
@endpush
