@extends('layouts.vendor')

@section('title', 'Manage Pickup Stores')
@section('page-title', 'Pickup Stores')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header 
        title="Pickup Stores" 
        subtitle="Create, update, and manage your network of localized pickup stores and distribution centers."
        :actions="[
            [
                'label' => 'Add Pickup Store',
                'icon' => 'solar:shop-bold-duotone',
                'url' => route('vendor.pickup-stores.create'),
                'btnClass' => 'btn-success'
            ]
        ]"
    />

    <!-- Filters Section -->
    <x-admin.table-filters action="{{ route('vendor.pickup-stores.index') }}" :search="$search"
        placeholder="Search code, name, mobile..." :limit="15" :sort="$sort" :order="$order">
        
        <!-- Status Filter -->
        <select name="status" class="form-select w-auto py-1.5" onchange="this.form.submit()">
            <option value="">All Statuses</option>
            <option value="Active" {{ request('status') == 'Active' ? 'selected' : '' }}>Active</option>
            <option value="Inactive" {{ request('status') == 'Inactive' ? 'selected' : '' }}>Inactive</option>
        </select>

        <!-- State Filter -->
        <select id="filterState" name="state_id" class="form-select w-auto py-1.5" onchange="this.form.submit()">
            <option value="">All States</option>
            @foreach($states as $st)
                <option value="{{ $st->id }}" {{ request('state_id') == $st->id ? 'selected' : '' }}>{{ $st->name }}</option>
            @endforeach
        </select>

        <!-- District Filter -->
        <select id="filterDistrict" name="district_id" class="form-select w-auto py-1.5" onchange="this.form.submit()">
            <option value="">All Districts</option>
            @foreach($districts as $dt)
                <option value="{{ $dt->id }}" {{ request('district_id') == $dt->id ? 'selected' : '' }}>{{ $dt->name }}</option>
            @endforeach
        </select>

        <!-- Owner Type Filter (Only for Main Seller) -->
        @if(!$isSubSeller)
            <select name="owner_type" class="form-select w-auto py-1.5" onchange="this.form.submit()">
                <option value="">All Owner Types</option>
                <option value="Seller" {{ request('owner_type') == 'Seller' ? 'selected' : '' }}>Seller</option>
                <option value="Sub Seller" {{ request('owner_type') == 'Sub Seller' ? 'selected' : '' }}>Sub Seller</option>
            </select>
        @endif
    </x-admin.table-filters>

    <!-- Table Card -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0 text-nowrap align-middle">
                    <thead class="bg-light bg-opacity-50 border-top border-bottom">
                        <tr>
                            <th class="ps-3" style="width: 60px;">#</th>
                            <x-admin.table-header field="pickup_store_code" :sort="$sort" :order="$order">Store Code</x-admin.table-header>
                            <x-admin.table-header field="pickup_store_name" :sort="$sort" :order="$order">Store Name</x-admin.table-header>
                            <th>Owner</th>
                            <th>Contact Person</th>
                            <th>Mobile / Email</th>
                            <th>State / District</th>
                            <th>Capabilities</th>
                            <th>Status</th>
                            <th>Created Date</th>
                            <th class="text-end pe-3" style="width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pickupStores as $store)
                            <tr>
                                <td class="ps-3 text-muted small">{{ $loop->iteration + (($pickupStores->currentPage() - 1) * $pickupStores->perPage()) }}</td>
                                <td><span class="badge bg-primary-subtle text-primary fs-12 font-monospace">{{ $store->pickup_store_code }}</span></td>
                                <td class="fw-semibold text-slate-800">{{ $store->pickup_store_name }}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-medium text-slate-800">{{ $store->owner?->name }}</span>
                                        <span class="text-muted small fs-11">{{ $store->owner_type }}</span>
                                    </div>
                                </td>
                                <td>{{ $store->contact_person }}</td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-medium text-slate-700">{{ $store->mobile }}</span>
                                        <span class="text-muted small fs-11">{{ $store->email }}</span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="text-slate-800">{{ $store->districtRelation?->name }}</span>
                                        <span class="text-muted small fs-11">{{ $store->stateRelation?->name }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($store->can_receive_payment_and_dispatch)
                                        <span class="badge bg-info-subtle text-info fs-11" title="Independent portal credentials enabled">
                                            Payment & Dispatch
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary fs-11" title="No portal access; seller-managed">
                                            Seller Managed
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($store->status === 'Active')
                                        <span class="badge bg-success-subtle text-success fs-11 d-inline-flex align-items-center gap-1 cursor-pointer" onclick="toggleStoreStatus('{{ $store->id }}', '{{ $store->status }}')">
                                            <i class="bx bxs-circle fs-8"></i> Active
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger fs-11 d-inline-flex align-items-center gap-1 cursor-pointer" onclick="toggleStoreStatus('{{ $store->id }}', '{{ $store->status }}')">
                                            <i class="bx bxs-circle fs-8"></i> Inactive
                                        </span>
                                    @endif
                                </td>
                                <td class="text-muted small">{{ $store->created_at->format('d M Y') }}</td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1 align-items-center justify-content-end">
                                        <a href="{{ route('vendor.pickup-stores.show', $store->id) }}" 
                                           class="btn btn-sm btn-outline-info d-inline-flex align-items-center justify-content-center p-0" 
                                           style="width: 28px; height: 28px; cursor: pointer;" 
                                           data-bs-toggle="tooltip" data-bs-placement="top" title="View Details">
                                            <iconify-icon icon="solar:eye-bold-duotone" class="fs-14" style="pointer-events: none;"></iconify-icon>
                                        </a>

                                        <a href="{{ route('vendor.pickup-stores.edit', $store->id) }}" 
                                           class="btn btn-sm btn-outline-primary d-inline-flex align-items-center justify-content-center p-0" 
                                           style="width: 28px; height: 28px; cursor: pointer;" 
                                           data-bs-toggle="tooltip" data-bs-placement="top" title="Edit Store">
                                            <iconify-icon icon="solar:pen-bold-duotone" class="fs-14" style="pointer-events: none;"></iconify-icon>
                                        </a>

                                        <a href="{{ route('vendor.pickup-stores.delivery-areas.index', $store->id) }}" 
                                           class="btn btn-sm btn-outline-success d-inline-flex align-items-center justify-content-center p-0" 
                                           style="width: 28px; height: 28px; cursor: pointer;" 
                                           data-bs-toggle="tooltip" data-bs-placement="top" title="Manage Delivery Areas">
                                            <iconify-icon icon="solar:routing-bold-duotone" class="fs-14" style="pointer-events: none;"></iconify-icon>
                                        </a>

                                        @if($store->can_receive_payment_and_dispatch)
                                            <button type="button" 
                                                    class="btn btn-sm btn-outline-warning d-inline-flex align-items-center justify-content-center p-0" 
                                                    style="width: 28px; height: 28px; cursor: pointer;" 
                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Reset Password" 
                                                    onclick="openResetPasswordModal('{{ $store->id }}', '{{ $store->pickup_store_code }}')">
                                                <iconify-icon icon="solar:key-bold-duotone" class="fs-14" style="pointer-events: none;"></iconify-icon>
                                            </button>
                                        @endif

                                        <form action="{{ route('vendor.pickup-stores.destroy', $store->id) }}" method="POST" class="d-inline delete-store-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="btn btn-sm btn-outline-danger d-inline-flex align-items-center justify-content-center p-0" 
                                                    style="width: 28px; height: 28px; cursor: pointer;" 
                                                    data-bs-toggle="tooltip" data-bs-placement="top" title="Delete Store">
                                                <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="fs-14" style="pointer-events: none;"></iconify-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <iconify-icon icon="solar:shop-bold-duotone" class="fs-48 mb-2"></iconify-icon>
                                    <h5>No Pickup Stores Registered</h5>
                                    <p class="mb-0">You have not registered any Pickup Stores yet.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($pickupStores->total() > 0)
                <div class="d-flex justify-content-between align-items-center p-3 border-top">
                    <span class="text-muted small">Showing {{ $pickupStores->firstItem() }} to {{ $pickupStores->lastItem() }} of {{ $pickupStores->total() }} entries</span>
                    @if($pickupStores->hasPages())
                        {{ $pickupStores->links() }}
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- Reset Password Modal -->
    <div class="modal fade" id="resetPasswordModal" tabindex="-1" aria-labelledby="resetPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="resetPasswordForm" method="POST" action="">
                    @csrf
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold" id="resetPasswordModalLabel">Reset Store Password</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="alert alert-info py-2 border-0 bg-info-subtle text-info fs-13 mb-3">
                            Resetting password for Store: <strong id="modalStoreCode"></strong>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label required">New Password</label>
                            <input type="password" name="password" class="form-control" placeholder="At least 8 characters" required>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label required">Confirm Password</label>
                            <input type="password" name="password_confirmation" class="form-control" placeholder="Re-enter password" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Password</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Initialize tooltips
    const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Delete Confirmation
    const deleteForms = document.querySelectorAll('.delete-store-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Delete Pickup Store?',
                text: 'Are you sure you want to delete this Pickup Store? This action cannot be undone.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Delete'
            }).then((result) => {
                if (result.isConfirmed) {
                    form.submit();
                }
            });
        });
    });

    // Dynamic District loading for filters
    const stateFilter = document.getElementById('filterState');
    const districtFilter = document.getElementById('filterDistrict');

    stateFilter.addEventListener('change', function () {
        const stateId = this.value;
        if (!stateId) {
            districtFilter.innerHTML = '<option value="">All Districts</option>';
            return;
        }

        fetch(`/api/locations/districts?state_id=${stateId}`)
            .then(res => res.json())
            .then(data => {
                districtFilter.innerHTML = '<option value="">All Districts</option>';
                data.forEach(d => {
                    const option = document.createElement('option');
                    option.value = d.id;
                    option.textContent = d.name;
                    districtFilter.appendChild(option);
                });
            });
    });
});

// Toggle status AJAX
function toggleStoreStatus(storeId, currentStatus) {
    const newStatus = currentStatus === 'Active' ? 'Inactive' : 'Active';
    fetch('{{ route("vendor.pickup-stores.status", ["id" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', storeId), {
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

// Reset Password modal handler
function openResetPasswordModal(storeId, storeCode) {
    document.getElementById('modalStoreCode').textContent = storeCode;
    document.getElementById('resetPasswordForm').action = '{{ route("vendor.pickup-stores.reset-password", ["id" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', storeId);
    const modal = new bootstrap.Modal(document.getElementById('resetPasswordModal'));
    modal.show();
}
</script>
@endpush
