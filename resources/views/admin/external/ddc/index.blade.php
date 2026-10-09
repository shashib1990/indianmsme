@extends('layouts.admin')

@section('title', 'Manage DDC')
@section('page-title', 'Manage DDC')

@section('content')
    <!-- Common Page Action Header -->
    <x-admin.page-header title="Manage DDC"
        subtitle="Manage DDC accounts, reset credentials, assign pincodes, and monitor DDC sessions."
        :actions="[
            [
                'label' => 'Add DDC',
                'icon' => 'solar:user-plus-bold-duotone',
                'url' => route('admin.external.ddc.create'),
                'permission' => 'users.manage',
                'btnClass' => 'btn-primary'
            ],
            [
                'label' => 'Manage ADC',
                'icon' => 'solar:user-speak-rounded-bold-duotone',
                'url' => route('admin.external.adc.index'),
                'permission' => 'users.view',
                'btnClass' => 'btn-outline-warning'
            ],
            [
                'label' => 'Vendors',
                'icon' => 'solar:shop-bold-duotone',
                'url' => route('admin.external.vendors.index'),
                'permission' => 'users.view',
                'btnClass' => 'btn-outline-info'
            ],
            [
                'label' => 'Export CSV',
                'icon' => 'solar:document-text-bold-duotone',
                'onclick' => 'triggerExport(\'csv\')',
                'permission' => 'users.view',
                'btnClass' => 'btn-outline-success'
            ]
        ]" />

    <!-- Reusable Table Filters -->
    <x-admin.table-filters action="{{ route('admin.external.ddc.index') }}" :search="$search"
        placeholder="Search name, email, phone, state..." :limit="$limit" :sort="$sort" :order="$order"
        :bulk-actions="[
                    'delete' => 'Delete Selected',
                    'status_active' => 'Activate Selected',
                    'status_inactive' => 'Deactivate Selected'
                ]" />

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
                                    <x-admin.table-header field="name" :sort="$sort"
                                        :order="$order">Name</x-admin.table-header>
                                    <th>DDB Code</th>
                                    <x-admin.table-header field="email" :sort="$sort"
                                        :order="$order">Email</x-admin.table-header>
                                    <th>Phone</th>
                                    <th>Type</th>
                                    <th>District</th>
                                    <th>State</th>
                                    <th>Assigned Pincodes</th>
                                    <x-admin.table-header field="is_active" :sort="$sort"
                                        :order="$order">Status</x-admin.table-header>
                                    <x-admin.table-header field="created_at" :sort="$sort"
                                        :order="$order">Joined</x-admin.table-header>
                                    <th class="text-end pe-3" style="width: 170px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($users as $user)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="form-check fs-15">
                                                <input type="checkbox" class="form-check-input row-checkbox"
                                                    value="{{ $user->id }}">
                                            </div>
                                        </td>
                                        <td class="ps-2 text-muted small">
                                            {{ $loop->iteration + (($users->currentPage() - 1) * $users->perPage()) }}
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="{{ $user->avatar_url }}" alt="avatar"
                                                    class="rounded-circle border border-light-subtle shadow-sm" width="32"
                                                    height="32">
                                                <span class="fw-semibold text-slate-800">{{ $user->name }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($user->profile && $user->profile->ddc_code)
                                                <code class="small text-primary fw-bold">{{ $user->profile->ddc_code }}</code>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->phone ?? ($user->mobile ?? '—') }}</td>
                                        <td>
                                            @if(($user->type ?? ($user->profile->type ?? 'physical')) === 'virtual')
                                                <span class="badge bg-primary-subtle text-primary px-2 py-1 fs-12">Virtual</span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary px-2 py-1 fs-12">Physical</span>
                                            @endif
                                        </td>
                                        <td>{{ $user->district ?? ($user->profile->district ?? '—') }}</td>
                                        <td>{{ $user->state ?? ($user->profile->state ?? '—') }}</td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info px-2 py-1 fs-12 fw-semibold">
                                                {{ $user->assigned_pincodes_count ?? 0 }} Pincodes
                                            </span>
                                        </td>
                                        <td>
                                            @if($user->status === '1')
                                                <span class="badge bg-success-subtle text-success px-2 py-1 fs-12">Active</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-12">Inactive</span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">{{ $user->created_at->format('d M Y') }}</td>
                                        <td class="text-end pe-3">
                                            <div class="d-inline-flex gap-1">
                                                <a href="{{ route('admin.external.ddc.show', $user->id) }}"
                                                    class="btn btn-sm btn-outline-info d-flex align-items-center justify-content-center p-1"
                                                    title="View Details" style="width: 28px; height: 28px;">
                                                    <iconify-icon icon="solar:eye-bold-duotone"
                                                        class="fs-14"></iconify-icon>
                                                </a>

                                                <a href="{{ route('admin.external.ddc.edit', $user->id) }}"
                                                    class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center p-1"
                                                    title="Edit DDC" style="width: 28px; height: 28px;">
                                                    <iconify-icon icon="solar:pen-bold-duotone"
                                                        class="fs-14"></iconify-icon>
                                                </a>

                                                <button type="button"
                                                    class="btn btn-sm btn-outline-success d-flex align-items-center justify-content-center p-1 btn-assign-pincodes"
                                                    data-user-id="{{ $user->id }}"
                                                    data-user-name="{{ $user->name }}"
                                                    data-user-code="{{ $user->profile->ddc_code ?? $user->name }}"
                                                    title="Assign Pincodes" style="width: 28px; height: 28px;">
                                                    <iconify-icon icon="solar:map-point-school-bold-duotone"
                                                        class="fs-14"></iconify-icon>
                                                </button>

                                                <button
                                                    class="btn btn-sm btn-outline-warning d-flex align-items-center justify-content-center p-1"
                                                    data-bs-toggle="modal" data-bs-target="#resetPwModal"
                                                    data-user-id="{{ $user->id }}" data-user-name="{{ $user->name }}"
                                                    data-reset-url="{{ route('admin.external.ddc.reset-password', $user->id) }}"
                                                    title="Reset Password" style="width: 28px; height: 28px;">
                                                    <iconify-icon icon="solar:lock-password-bold-duotone"
                                                        class="fs-14"></iconify-icon>
                                                </button>

                                                <form action="{{ route('admin.external.ddc.toggle-status', $user->id) }}"
                                                    method="POST" class="d-inline">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn btn-sm confirm-action d-flex align-items-center justify-content-center p-1 {{ $user->status === '1' ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                                        data-confirm-message="Are you sure you want to {{ $user->status === '1' ? 'deactivate' : 'activate' }} {{ $user->name }}?"
                                                        title="{{ $user->status === '1' ? 'Deactivate' : 'Activate' }}"
                                                        style="width: 28px; height: 28px;">
                                                        <iconify-icon
                                                            icon="{{ $user->status === '1' ? 'solar:user-block-rounded-bold-duotone' : 'solar:user-check-rounded-bold-duotone' }}"
                                                            class="fs-14"></iconify-icon>
                                                    </button>
                                                </form>

                                                <form action="{{ route('admin.impersonate', ['ddc', $user->id]) }}"
                                                    method="POST" class="d-inline" target="_blank">
                                                    @csrf
                                                    <button type="submit"
                                                        class="btn btn-sm btn-outline-info confirm-action d-flex align-items-center justify-content-center p-1"
                                                        data-confirm-message="Login as {{ $user->name }} in a new tab?"
                                                        title="Login as this user (Opens in new tab)" style="width: 28px; height: 28px;">
                                                        <iconify-icon icon="solar:user-check-bold-duotone"
                                                            class="fs-14"></iconify-icon>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <x-admin.empty-state icon="solar:user-id-bold-duotone" title="No DDC Users Found"
                                        description="We couldn't find any DDC users matching your search." colspan="10" />
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    @if($users->hasPages())
                        <div class="d-flex justify-content-between align-items-center px-3 py-2 border-top">
                            <small class="text-muted">Showing {{ $users->firstItem() }}–{{ $users->lastItem() }} of
                                {{ $users->total() }}</small>
                            {{ $users->withQueryString()->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPwModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title fw-semibold text-slate-800">
                        <i class="bx bx-key me-1 text-warning"></i> Reset DDC Password
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form id="resetPasswordForm" method="POST">
                    @csrf
                    <div class="modal-body">
                        <p class="text-muted mb-3">Resetting password for: <strong id="resetUserName"
                                class="text-slate-800"></strong></p>
                        <div class="alert alert-warning border-0 small py-2 d-flex align-items-center gap-2">
                            <iconify-icon icon="solar:danger-triangle-bold-duotone"
                                class="fs-18 text-warning"></iconify-icon>
                            <span>The user will be forced to change their password on next login.</span>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">New Password <span
                                    class="text-danger">*</span></label>
                            <input type="password" name="new_password" class="form-control"
                                placeholder="Minimum 8 characters" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label text-slate-700 fw-medium">Confirm Password <span
                                    class="text-danger">*</span></label>
                            <input type="password" name="new_password_confirmation" class="form-control"
                                placeholder="Repeat new password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-warning text-white">
                            <i class="bx bx-key me-1"></i> Reset Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Assign Pincodes Modal -->
    <div class="modal fade" id="assignPincodesModal" tabindex="-1" aria-labelledby="assignPincodesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold text-slate-800" id="assignPincodesModalLabel">
                        <iconify-icon icon="solar:map-point-school-bold-duotone" class="align-middle me-1 text-success"></iconify-icon>
                        Assign Pincodes to DDC: <span id="assignDdcName" class="text-primary"></span>
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body py-3">
                    <div id="modalAlertContainer"></div>
                    
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-semibold text-slate-600 mb-0">Search Pincode, District, or State</label>
                            <span id="assignDdcLocationText" class="badge bg-primary-subtle text-primary fw-semibold fs-12 px-2 py-1"></span>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="bx bx-search fs-18"></i></span>
                            <input type="text" id="pincodeModalSearch" class="form-control border-start-0" placeholder="Type pincode, district name, state name to filter...">
                        </div>
                    </div>

                    <div id="pincodeLoadingSpinner" class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted small mt-2">Loading available pincodes...</p>
                    </div>

                    <form id="assignPincodesForm" style="display: none;">
                        @csrf
                        <input type="hidden" id="assignDdcId" name="ddc_id">
                        
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <div class="small text-muted">
                                <span class="badge bg-success-subtle text-success me-1">Checked</span> Current Assignments
                                <span class="badge bg-danger-subtle text-danger ms-2 me-1">Disabled</span> Assigned to other DDC
                            </div>
                            <div>
                                <button type="button" class="btn btn-xs btn-outline-secondary me-1" id="expandAllDistrictsBtn">Expand All</button>
                                <button type="button" class="btn btn-xs btn-outline-secondary" id="collapseAllDistrictsBtn">Collapse All</button>
                            </div>
                        </div>

                        <div id="pincodesTreeContainer" style="max-height: 400px; overflow-y: auto; padding-right: 5px;">
                            <!-- Dynamically loaded tree -->
                        </div>
                    </form>
                </div>
                <div class="modal-footer bg-light">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-success fw-semibold" id="savePincodesBtn" style="display: none;">
                        <i class="bx bx-save me-1"></i> Save Changes
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Reset Password Modal
            const resetModal = document.getElementById('resetPwModal');
            if (resetModal) {
                resetModal.addEventListener('show.bs.modal', function (event) {
                    const btn = event.relatedTarget;
                    document.getElementById('resetUserName').textContent = btn.getAttribute('data-user-name');
                    document.getElementById('resetPasswordForm').action = btn.getAttribute('data-reset-url');
                });
            }

            // Assign Pincodes Logic
            const assignPincodesModal = new bootstrap.Modal(document.getElementById('assignPincodesModal'));
            const modalSearchInput = document.getElementById('pincodeModalSearch');
            const treeContainer = document.getElementById('pincodesTreeContainer');
            const spinner = document.getElementById('pincodeLoadingSpinner');
            const form = document.getElementById('assignPincodesForm');
            const saveBtn = document.getElementById('savePincodesBtn');
            const ddcNameSpan = document.getElementById('assignDdcName');
            const ddcIdInput = document.getElementById('assignDdcId');
            const alertContainer = document.getElementById('modalAlertContainer');

            const pincodesUrlTemplate = "{{ route('admin.external.ddc.pincodes', ':id') }}";
            let allPincodes = [];
            let currentDdcId = null;

            // Fetch and render pincodes when clicking assign button
            document.querySelectorAll('.btn-assign-pincodes').forEach(btn => {
                btn.addEventListener('click', function() {
                    currentDdcId = this.getAttribute('data-user-id');
                    const ddcName = this.getAttribute('data-user-name');
                    const ddcCode = this.getAttribute('data-user-code');

                    ddcNameSpan.textContent = `${ddcName} (${ddcCode})`;
                    ddcIdInput.value = currentDdcId;

                    // Show loader and hide form
                    spinner.style.display = 'block';
                    form.style.display = 'none';
                    saveBtn.style.display = 'none';
                    treeContainer.innerHTML = '';
                    alertContainer.innerHTML = '';
                    modalSearchInput.value = '';
                    document.getElementById('assignDdcLocationText').textContent = 'Loading location...';

                    assignPincodesModal.show();

                    const fetchUrl = pincodesUrlTemplate.replace(':id', currentDdcId);
                    fetch(fetchUrl)
                        .then(res => res.json())
                        .then(data => {
                            if (data.status) {
                                allPincodes = data.pincodes;
                                renderPincodes();
                                spinner.style.display = 'none';
                                form.style.display = 'block';
                                saveBtn.style.display = 'inline-block';
                                
                                const locationText = data.ddc.district ? `District: ${data.ddc.district} (${data.ddc.state ?? ''})` : 'Location not configured';
                                document.getElementById('assignDdcLocationText').textContent = locationText;
                            } else {
                                alertContainer.innerHTML = `<div class="alert alert-danger">${data.message || 'Failed to load pincodes.'}</div>`;
                                document.getElementById('assignDdcLocationText').textContent = 'Error';
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            alertContainer.innerHTML = `<div class="alert alert-danger">An error occurred while loading pincodes.</div>`;
                        });
                });
            });

            // Group and render function
            function renderPincodes(filterText = '') {
                filterText = filterText.toLowerCase().trim();
                
                // Group by State and District
                const grouped = {};
                
                allPincodes.forEach(p => {
                    const matchText = `${p.pincode} ${p.district_name} ${p.state_name}`.toLowerCase();
                    if (filterText && !matchText.includes(filterText)) {
                        return;
                    }

                    const state = p.state_name;
                    const district = p.district_name;

                    if (!grouped[state]) {
                        grouped[state] = {};
                    }
                    if (!grouped[state][district]) {
                        grouped[state][district] = [];
                    }
                    grouped[state][district].push(p);
                });

                if (Object.keys(grouped).length === 0) {
                    treeContainer.innerHTML = '<div class="text-center text-muted py-4">No pincodes found matching filters.</div>';
                    return;
                }

                let html = '';
                let index = 0;

                for (const state in grouped) {
                    html += `
                        <div class="state-group mb-3 border rounded shadow-xs overflow-hidden">
                            <div class="bg-light px-3 py-2 border-bottom fw-bold text-slate-800 d-flex justify-content-between align-items-center">
                                <span><i class="bx bx-map-pin text-primary me-1"></i> ${state}</span>
                                <span class="badge bg-secondary-subtle text-secondary small">${Object.keys(grouped[state]).length} Districts</span>
                            </div>
                            <div class="state-body p-2">
                    `;

                    for (const district in grouped[state]) {
                        const items = grouped[state][district];
                        const districtId = `dist-${index++}`;
                        const availableCount = items.filter(p => !p.assigned_ddc_id || p.is_assigned_to_current).length;

                        html += `
                            <div class="district-card border border-light-subtle rounded mb-2">
                                <div class="district-header d-flex justify-content-between align-items-center bg-light bg-opacity-25 px-3 py-2 cursor-pointer" 
                                     data-bs-toggle="collapse" data-bs-target="#${districtId}" aria-expanded="true" style="cursor: pointer;">
                                    <div class="fw-semibold text-slate-700">
                                        <i class="bx bx-chevron-right collapse-icon me-1"></i> ${district}
                                        <span class="text-muted small fw-normal">(${items.length} Pincodes)</span>
                                    </div>
                                    <div onclick="event.stopPropagation();">
                                        <button type="button" class="btn btn-xs btn-outline-primary select-all-district py-0.5 px-2" data-target="${districtId}">Select All</button>
                                        <button type="button" class="btn btn-xs btn-outline-warning deselect-all-district py-0.5 px-2 ms-1" data-target="${districtId}">Clear</button>
                                    </div>
                                </div>
                                <div class="collapse show" id="${districtId}">
                                    <div class="card-body p-3">
                                        <div class="row row-cols-2 row-cols-md-4 g-2">
                        `;

                        items.forEach(p => {
                            const isCurrent = p.is_assigned_to_current;
                            const isOther = p.assigned_ddc_id && !p.is_assigned_to_current;

                            html += `
                                <div class="col">
                                    <div class="form-check p-2 rounded border border-light-subtle h-100 ${isCurrent ? 'bg-success bg-opacity-10 border-success border-opacity-25' : (isOther ? 'bg-danger bg-opacity-10 border-danger border-opacity-25' : '')}" style="font-size: 13px;">
                                        <input class="form-check-input pincode-checkbox ms-0 me-2" 
                                               type="checkbox" 
                                               name="pincode_ids[]" 
                                               value="${p.id}" 
                                               id="pin-${p.id}" 
                                               ${isCurrent ? 'checked' : ''} 
                                               ${isOther ? 'disabled' : ''}>
                                        <label class="form-check-label fw-semibold text-slate-800 d-block" for="pin-${p.id}" style="margin-left: 20px;">
                                            ${p.pincode}
                                            ${isOther ? `<span class="d-block text-danger font-normal" style="font-size: 9px;">(Assigned: ${p.assigned_ddc_code})</span>` : ''}
                                            ${isCurrent ? '<span class="badge bg-success py-0 px-1 text-white ms-1" style="font-size: 8px;">Active</span>' : ''}
                                        </label>
                                    </div>
                                </div>
                            `;
                        });

                        html += `
                                        </div>
                                    </div>
                                </div>
                            </div>
                        `;
                    }

                    html += `
                            </div>
                        </div>
                    `;
                }

                treeContainer.innerHTML = html;

                // Bind select/deselect all actions inside districts
                document.querySelectorAll('.select-all-district').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const targetId = this.getAttribute('data-target');
                        const container = document.getElementById(targetId);
                        container.querySelectorAll('.pincode-checkbox:not(:disabled)').forEach(cb => {
                            cb.checked = true;
                            cb.closest('.form-check').classList.add('bg-success', 'bg-opacity-10', 'border-success', 'border-opacity-25');
                        });
                    });
                });

                document.querySelectorAll('.deselect-all-district').forEach(btn => {
                    btn.addEventListener('click', function() {
                        const targetId = this.getAttribute('data-target');
                        const container = document.getElementById(targetId);
                        container.querySelectorAll('.pincode-checkbox:not(:disabled)').forEach(cb => {
                            cb.checked = false;
                            cb.closest('.form-check').classList.remove('bg-success', 'bg-opacity-10', 'border-success', 'border-opacity-25');
                        });
                    });
                });

                // Checkbox styling toggle on manual click
                document.querySelectorAll('.pincode-checkbox').forEach(cb => {
                    cb.addEventListener('change', function() {
                        const wrapper = this.closest('.form-check');
                        if (this.checked) {
                            wrapper.classList.add('bg-success', 'bg-opacity-10', 'border-success', 'border-opacity-25');
                        } else {
                            wrapper.classList.remove('bg-success', 'bg-opacity-10', 'border-success', 'border-opacity-25');
                        }
                    });
                });
            }

            // Real-time filter keyup handler
            modalSearchInput.addEventListener('input', function() {
                renderPincodes(this.value);
            });

            // Expand/Collapse All
            document.getElementById('expandAllDistrictsBtn').addEventListener('click', function() {
                document.querySelectorAll('#pincodesTreeContainer .collapse').forEach(el => {
                    el.classList.add('show');
                });
            });

            document.getElementById('collapseAllDistrictsBtn').addEventListener('click', function() {
                document.querySelectorAll('#pincodesTreeContainer .collapse').forEach(el => {
                    el.classList.remove('show');
                });
            });

            // Form Submit / Sync Save button
            saveBtn.addEventListener('click', function() {
                alertContainer.innerHTML = '';
                
                // Gather form data
                const formData = new FormData(form);
                const payload = {
                    pincode_ids: formData.getAll('pincode_ids[]'),
                    _token: document.querySelector('input[name="_token"]').value
                };

                saveBtn.disabled = true;
                saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...';

                const saveUrl = pincodesUrlTemplate.replace(':id', currentDdcId);
                fetch(saveUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="bx bx-save me-1"></i> Save Changes';

                    if (data.status) {
                        // Success toast or alert
                        alertContainer.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                        
                        // Close modal after 1.5s and reload list page to show updated counts
                        setTimeout(() => {
                            assignPincodesModal.hide();
                            window.location.reload();
                        }, 1200);
                    } else {
                        // Show warning/conflict details
                        alertContainer.innerHTML = `<div class="alert alert-danger">${data.message || 'Failed to update mappings.'}</div>`;
                    }
                })
                .catch(err => {
                    console.error(err);
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = '<i class="bx bx-save me-1"></i> Save Changes';
                    alertContainer.innerHTML = `<div class="alert alert-danger">An unexpected error occurred while saving mappings.</div>`;
                });
            });
        });
    </script>
@endpush