@php
    $pageTitle = 'Pending Product Approvals';
    $pageSubtitle = 'Review, approve, or reject product catalogs submitted by sellers before they become visible on the platform.';

    if ($tab === 'new_approvals') {
        $pageTitle = 'New Product Submissions';
        $pageSubtitle = 'Review and approve new product catalogs submitted by sellers before they become visible on the platform.';
    } elseif ($tab === 'update_approvals') {
        $pageTitle = 'Update Product Submissions';
        $pageSubtitle = 'Review and approve updates/changes to existing products submitted by sellers.';
    } elseif ($tab === 'approved') {
        $pageTitle = 'Approved Product Catalog';
        $pageSubtitle = 'Browse all product catalogs that have been approved and are active on the platform.';
    } elseif ($tab === 'rejected') {
        $pageTitle = 'Rejected Product Catalog';
        $pageSubtitle = 'Browse all product catalogs that have been rejected with remarks.';
    }
@endphp

@extends('layouts.admin')

@section('title', $pageTitle)
@section('page-title', $pageTitle)

@section('content')
    <x-admin.page-header 
        :title="$pageTitle" 
        :subtitle="$pageSubtitle"
    />

    @if(request('filter') === 'overcosted')
        <div class="alert alert-warning border-0 shadow-sm mb-3 d-flex align-items-center justify-content-between py-2 px-3" style="border-radius: 8px;">
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="solar:danger-circle-bold-duotone" class="fs-18 text-warning"></iconify-icon>
                <span class="fs-13">Filtered by: <strong class="text-uppercase text-dark">Overcosted / High MRP Products</strong></span>
            </div>
            <a href="{{ route('admin.products.approvals.new') }}" class="btn btn-sm btn-light fw-medium py-0.5 px-2 fs-11">
                Clear Filter &times;
            </a>
        </div>
    @endif

    <!-- Reusable Table Filters -->
    <x-admin.table-filters 
        action="{{ url()->current() }}" 
        :search="$search" 
        placeholder="Search product name, barcode, seller..." 
        :limit="$limit" 
        :sort="$sort" 
        :order="$order"
        :bulk-actions="($tab === 'new_approvals' || $tab === 'update_approvals') ? [
            'bulk_approve' => 'Approve Selected',
            'bulk_reject' => 'Reject Selected'
        ] : []"
    >
        <input type="hidden" name="tab" value="{{ $tab }}">
    </x-admin.table-filters>

    <div class="row mt-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover table-centered mb-0 text-nowrap align-middle">
                            <thead class="bg-light bg-opacity-50 border-top border-bottom">
                                <tr>
                                    @if($tab === 'new_approvals' || $tab === 'update_approvals')
                                        <th class="ps-3" style="width: 40px;">
                                            <div class="form-check fs-15">
                                                <input type="checkbox" class="form-check-input" id="selectAllCheckbox">
                                            </div>
                                        </th>
                                    @endif
                                    <th class="ps-2" style="width: 60px;">#</th>
                                    <th style="width: 80px;">Image</th>
                                    <x-admin.table-header field="product_name" :sort="$sort" :order="$order">Product Details</x-admin.table-header>
                                    <th>Seller / Manufacturer</th>
                                    <th>Brand</th>
                                    <th>Category</th>
                                    <th>Seller Price (Incl. GST)</th>
                                    <th>MRP</th>
                                    <th>GST %</th>
                                    @if($tab === 'approved' || $tab === 'rejected')
                                        <x-admin.table-header field="updated_at" :sort="$sort" :order="$order">Processed At</x-admin.table-header>
                                    @else
                                        <x-admin.table-header field="updated_at" :sort="$sort" :order="$order">Submitted At</x-admin.table-header>
                                    @endif
                                    <th class="text-end pe-3" style="width: 100px;">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($products as $product)
                                    <tr>
                                        @if($tab === 'new_approvals' || $tab === 'update_approvals')
                                            <td class="ps-3">
                                                <div class="form-check fs-15">
                                                    <input type="checkbox" class="form-check-input row-checkbox" value="{{ $product->id }}">
                                                </div>
                                            </td>
                                        @endif
                                        <td class="ps-2 text-muted small">{{ $loop->iteration + (($products->currentPage() - 1) * $products->perPage()) }}</td>
                                        <td>
                                            @if($product->featured_image)
                                                <img src="{{ $product->featured_image }}" class="rounded border" style="width: 44px; height: 44px; object-fit: cover;">
                                            @else
                                                <div class="rounded border bg-light d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                                                    <iconify-icon icon="solar:box-bold-duotone" class="text-muted fs-22"></iconify-icon>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <div class="d-flex align-items-center gap-2">
                                                    <span class="fw-semibold text-slate-800">{{ $product->product_name }}</span>
                                                    @if($product->is_new)
                                                        <span class="badge bg-info-subtle text-info fs-10 px-2 py-1 rounded-pill fw-semibold" style="font-size: 9px !important; letter-spacing: 0.3px;">NEW</span>
                                                    @else
                                                        <span class="badge bg-warning-subtle text-warning fs-10 px-2 py-1 rounded-pill fw-semibold" style="font-size: 9px !important; letter-spacing: 0.3px;">UPDATED</span>
                                                    @endif
                                                </div>
                                                <span class="text-muted small fs-11">Code: <code>{{ $product->product_code ?? '—' }}</code> | Barcode: <code>{{ $product->barcode }}</code></span>
                                            </div>
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-semibold text-slate-700">{{ $product->seller ? ($product->seller->sellerProfile->company_name ?? $product->seller->name) : '—' }}</span>
                                                <span class="text-muted small fs-11">ID: <code>{{ $product->seller ? ($product->seller->sellerProfile->seller_id ?? $product->seller->seller_id ?? '—') : '—' }}</code></span>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-slate-700 border">{{ $product->brand->name ?? '—' }}</span>
                                        </td>
                                        <td class="small">{{ $product->category->name ?? '—' }}</td>
                                        <td class="fw-semibold text-slate-800">₹{{ number_format($product->seller_price_incl_gst, 2) }}</td>
                                        <td class="text-muted">₹{{ number_format($product->mrp, 2) }}</td>
                                        <td>
                                            <span class="badge bg-success-subtle text-success">{{ $product->gst_rate }}%</span>
                                        </td>
                                        <td class="text-muted small">{{ $product->updated_at->format('d M Y, h:i A') }}</td>
                                        <td class="text-end pe-3">
                                            @if($tab === 'new_approvals' || $tab === 'update_approvals')
                                                <a href="{{ route('admin.products.approvals.show', $product->id) }}" class="btn btn-sm btn-primary">
                                                    <iconify-icon icon="solar:clipboard-check-bold-duotone" class="me-1 align-middle"></iconify-icon>
                                                    Review
                                                </a>
                                            @else
                                                <a href="{{ route('admin.products.approvals.show', $product->id) }}" class="btn btn-sm btn-outline-secondary">
                                                    <iconify-icon icon="solar:eye-bold-duotone" class="me-1 align-middle"></iconify-icon>
                                                    View Details
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="{{ ($tab === 'new_approvals' || $tab === 'update_approvals') ? 12 : 11 }}" class="text-center py-5 text-muted">
                                            <iconify-icon icon="solar:check-circle-bold-duotone" class="text-success fs-48 mb-2"></iconify-icon>
                                            <h5 class="fw-semibold text-slate-800">No products found</h5>
                                            <p class="mb-0">There are no products in this section at the moment.</p>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($products->hasPages())
                        <div class="d-flex justify-content-between align-items-center p-3 border-top">
                            <span class="text-muted small">Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} results</span>
                            {{ $products->links() }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Action Modal for Reject Remarks -->
    <div class="modal fade" id="bulkRejectModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <form id="bulkRejectForm" method="POST" action="{{ route('admin.products.approvals.bulk') }}">
                @csrf
                <input type="hidden" name="action" value="reject">
                <div id="bulkRejectIdsContainer"></div>
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title fw-bold text-slate-800">Reject Selected Products</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label required fw-medium text-slate-700">Rejection Remarks / Reason</label>
                            <textarea name="admin_remarks" class="form-control" rows="4" placeholder="Specify why the selected products are being rejected..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-danger">Reject Products</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // Override default triggerBulkAction for approvals custom modals
    window.triggerBulkAction = function (action) {
        const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
        const ids = Array.from(checkedBoxes).map(cb => cb.value);

        if (ids.length === 0) return;

        if (action === 'bulk_approve') {
            Swal.fire({
                title: 'Approve Products?',
                text: `Are you sure you want to approve the ${ids.length} selected products? they will become immediately visible in the marketplace catalog.`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Approve All'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.showLoading();
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = "{{ route('admin.products.approvals.bulk') }}";

                    const csrfInput = document.createElement('input');
                    csrfInput.type = 'hidden';
                    csrfInput.name = '_token';
                    csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                    form.appendChild(csrfInput);

                    const actionInput = document.createElement('input');
                    actionInput.type = 'hidden';
                    actionInput.name = 'action';
                    actionInput.value = 'approve';
                    form.appendChild(actionInput);

                    ids.forEach(id => {
                        const idInput = document.createElement('input');
                        idInput.type = 'hidden';
                        idInput.name = 'ids[]';
                        idInput.value = id;
                        form.appendChild(idInput);
                    });

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        } else if (action === 'bulk_reject') {
            const container = document.getElementById('bulkRejectIdsContainer');
            container.innerHTML = '';
            ids.forEach(id => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'ids[]';
                input.value = id;
                container.appendChild(input);
            });

            const rejectModal = new bootstrap.Modal(document.getElementById('bulkRejectModal'));
            rejectModal.show();
        }
    };
});
</script>
@endpush
