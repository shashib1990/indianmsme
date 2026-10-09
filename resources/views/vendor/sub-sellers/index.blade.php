@extends('layouts.vendor')

@section('title', 'Manage Sub-Sellers')
@section('page-title', 'Sub-Sellers')

@section('content')
    <!-- Standard Page Header -->
    <x-admin.page-header title="Sub-Seller Hierarchy"
        subtitle="Manage secondary manufacturers/distributors registered under your profile, assign custom sales margins, and toggle upload privileges."
        :actions="[
            [
                'label' => 'Add Sub-Seller',
                'icon' => 'solar:user-plus-bold-duotone',
                'url' => route('vendor.sub-sellers.create'),
                'btnClass' => 'btn-success'
            ]
        ]" />

    <!-- Filters Section -->
    <x-admin.table-filters action="{{ route('vendor.sub-sellers.index') }}" :search="$search"
        placeholder="Search name, email, company..." :limit="$limit" :sort="$sort" :order="$order">
    </x-admin.table-filters>

    <!-- Sub-Sellers Table Card -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0 text-nowrap align-middle">
                    <thead class="bg-light bg-opacity-50 border-top border-bottom">
                        <tr>
                            <th class="ps-3" style="width: 60px;">
                                #
                            </th>
                            <th>Sub-Seller ID</th>
                            <th>Company Details</th>
                            <x-admin.table-header field="name" :sort="$sort" :order="$order">
                                Contact Person
                            </x-admin.table-header>
                            <th>State / District</th>
                            <th>Category</th>
                            <x-admin.table-header field="created_at" :sort="$sort" :order="$order">
                                Created At
                            </x-admin.table-header>
                            <th>Status</th>
                            <th class="text-end pe-3" style="width: 150px;">
                                Actions
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subSellers as $sub)
                            <tr>
                                <td class="ps-3 text-muted small">
                                    {{ $loop->iteration + (($subSellers->currentPage() - 1) * $subSellers->perPage()) }}
                                </td>
                                <td><code>{{ $sub->sellerProfile->seller_id ?? '—' }}</code></td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span
                                            class="fw-semibold text-slate-800">{{ $sub->sellerProfile->company_name ?? '—' }}</span>
                                        <span class="text-muted small fs-11">Type:
                                            {{ $sub->sellerProfile->company_type ?? '—' }}
                                            @if(!empty($sub->sellerProfile->gst_number))
                                                | GST: <code>{{ $sub->sellerProfile->gst_number }}</code>
                                            @endif
                                        </span>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span
                                            class="fw-semibold text-slate-700">{{ $sub->sellerProfile->authorized_rep_name ?? $sub->name }}</span>
                                        <span class="text-muted small fs-11">{{ $sub->email }} | {{ $sub->mobile }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-slate-600 small">{{ $sub->sellerProfile->district ?? '—' }},
                                        {{ $sub->sellerProfile->state ?? '—' }}</span>
                                </td>
                                <td class="small">{{ $sub->sellerProfile->category->name ?? '—' }}</td>
                                <td class="text-muted small">{{ $sub->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="form-check form-switch d-inline-flex align-items-center gap-1">
                                        <input class="form-check-input sub-seller-status-toggle" type="checkbox" role="switch"
                                            data-id="{{ $sub->id }}"
                                            data-url="{{ route('vendor.sub-sellers.toggle-active', $sub->id) }}"
                                            {{ $sub->status === '1' ? 'checked' : '' }}>
                                        <span class="badge {{ $sub->status === '1' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} px-2 py-1 fs-12 sub-status-badge-{{ $sub->id }}">
                                            {{ $sub->status === '1' ? 'Active' : 'Inactive' }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('vendor.sub-sellers.products', $sub->id) }}"
                                            class="btn btn-sm btn-outline-info d-flex align-items-center justify-content-center p-1"
                                            style="width: 28px; height: 28px;" title="Manage Products">
                                            <iconify-icon icon="solar:box-bold-duotone" class="fs-14"></iconify-icon>
                                        </a>

                                        <a href="{{ route('vendor.sub-sellers.edit', $sub->id) }}"
                                            class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center p-1"
                                            style="width: 28px; height: 28px;" title="Edit config">
                                            <iconify-icon icon="solar:pen-bold-duotone" class="fs-14"></iconify-icon>
                                        </a>

                                        <form action="{{ route('vendor.sub-sellers.destroy', $sub->id) }}" method="POST"
                                            class="d-inline delete-sub-form">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center p-1"
                                                style="width: 28px; height: 28px;" title="Delete account">
                                                <iconify-icon icon="solar:trash-bin-trash-bold-duotone"
                                                    class="fs-14"></iconify-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <iconify-icon icon="solar:users-group-two-rounded-bold-duotone"
                                        class="fs-48 mb-2"></iconify-icon>
                                    <h5>No Sub-Sellers Registered</h5>
                                    <p class="mb-0">You have not registered any sub-sellers under your profile yet.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($subSellers->total() > 0)
                <div class="d-flex justify-content-between align-items-center p-3 border-top">
                    <span class="text-muted small">Showing {{ $subSellers->firstItem() }} to {{ $subSellers->lastItem() }} of
                        {{ $subSellers->total() }} entries</span>
                    @if($subSellers->hasPages())
                        {{ $subSellers->links() }}
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const deleteForms = document.querySelectorAll('.delete-sub-form');
            deleteForms.forEach(form => {
                form.addEventListener('submit', function (e) {
                    e.preventDefault();
                    Swal.fire({
                        title: 'Delete sub-seller?',
                        text: 'Are you sure you want to delete this sub-seller account? This action cannot be undone.',
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

            // AJAX Sub-Seller Status Toggle (No Page Reload)
            document.addEventListener('change', function (e) {
                if (e.target && e.target.classList.contains('sub-seller-status-toggle')) {
                    const checkbox = e.target;
                    const url = checkbox.dataset.url;
                    const subId = checkbox.dataset.id;
                    const badge = document.querySelector(`.sub-status-badge-${subId}`);
                    
                    checkbox.disabled = true;

                    fetch(url, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        checkbox.disabled = false;
                        if (data.success) {
                            checkbox.checked = (data.status === '1');
                            if (badge) {
                                badge.className = `badge ${data.badge_class} px-2 py-1 fs-12 sub-status-badge-${subId}`;
                                badge.textContent = data.status_label;
                            }
                            if (typeof window.showToast === 'function') {
                                window.showToast(data.message, 'success');
                            } else if (typeof toastr !== 'undefined') {
                                toastr.success(data.message);
                            }
                        } else {
                            checkbox.checked = !checkbox.checked;
                            if (typeof window.showToast === 'function') {
                                window.showToast(data.message || 'Failed to update status', 'error');
                            } else {
                                alert(data.message || 'Failed to update status');
                            }
                        }
                    })
                    .catch(err => {
                        checkbox.disabled = false;
                        checkbox.checked = !checkbox.checked;
                        if (typeof window.showToast === 'function') {
                            window.showToast('An error occurred while updating status.', 'error');
                        } else {
                            alert('An error occurred while updating status.');
                        }
                    });
                }
            });
        });
    </script>
@endpush