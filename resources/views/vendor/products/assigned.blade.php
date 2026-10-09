@extends('layouts.vendor')

@section('title', 'My Product Pricing')
@section('page-title', 'Product Pricing')

@section('content')
    <!-- Standard Page Header -->
    <x-admin.page-header 
        title="Catalog Pricing & Markups" 
        subtitle="Below are the approved products from your parent seller. You can customize your pricing by entering your markup amount. The final sub-seller price will be calculated automatically."
    />

    <!-- Filters Section -->
    <x-admin.table-filters action="{{ route('vendor.products.assigned') }}" :search="$search"
        placeholder="Search name or barcode..." :limit="$limit" :sort="$sort" :order="$order">
    </x-admin.table-filters>

    <!-- Products Table Card -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0 align-middle">
                    <thead class="bg-light bg-opacity-50 border-top border-bottom">
                        <tr>
                            <th class="ps-3" style="width: 50px;">#</th>
                            <th style="width: 70px;">Image</th>
                            <x-admin.table-header field="product_name" :sort="$sort" :order="$order">Product Details</x-admin.table-header>
                            <x-admin.table-header field="seller_price_incl_gst" :sort="$sort" :order="$order">Parent Seller Price</x-admin.table-header>
                            <th class="text-center" style="width: 180px;">Your Markup (Add-on)</th>
                            <th class="text-end" style="width: 180px;">Your Price (Incl. GST)</th>
                            <x-admin.table-header field="mrp" :sort="$sort" :order="$order">MRP</x-admin.table-header>
                            <th class="text-center" style="width: 100px;">GST %</th>
                            <th class="text-center" style="width: 120px;">Status</th>
                            <th class="text-center" style="width: 100px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            @php
                                $custom = $customizations->get($product->id);
                                $extra = $custom ? (float)$custom->margin : 0.00;
                                $subExFactory = (float)$product->seller_price_incl_gst + $extra;
                            @endphp
                            <tr id="row-{{ $product->id }}">
                                <td class="ps-3 text-muted small">{{ $loop->iteration + (($products->currentPage() - 1) * $products->perPage()) }}</td>
                                <td>
                                    @if($product->featured_image)
                                        <img src="{{ $product->featured_image }}" class="rounded border bg-light" style="width: 48px; height: 48px; object-fit: contain;">
                                    @else
                                        <div class="rounded border bg-light d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                            <iconify-icon icon="solar:box-bold-duotone" class="text-muted fs-24"></iconify-icon>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-column" style="max-width: 300px;">
                                        <span class="fw-semibold text-slate-800 text-truncate" title="{{ $product->product_name }}">{{ $product->product_name }}</span>
                                        <span class="text-muted small fs-11">Barcode: <code>{{ $product->barcode }}</code></span>
                                        <span class="text-muted small fs-11">Brand: <span class="badge bg-light text-slate-600 border px-1">{{ $product->brand->name ?? '—' }}</span></span>
                                    </div>
                                </td>
                                <td class="text-end fw-medium text-slate-600">₹{{ number_format($product->seller_price_incl_gst, 2) }}</td>
                                <td class="text-center">
                                    <div class="input-group input-group-sm mx-auto" style="width: 130px;">
                                        <span class="input-group-text bg-light">₹</span>
                                        <input type="number" 
                                               step="0.01" 
                                               min="0" 
                                               class="form-control text-center markup-input" 
                                               data-product-id="{{ $product->id }}" 
                                               data-parent-price="{{ $product->seller_price_incl_gst }}"  
                                               data-gst-rate="{{ $product->gst_rate }}" 
                                               data-mrp="{{ $product->mrp }}" 
                                               value="{{ number_format($extra, 2, '.', '') }}">
                                    </div>
                                </td>
                                <td class="text-end fw-semibold text-primary">
                                    <div class="d-flex flex-column align-items-end">
                                        <span class="sub-ex-factory-cell">₹{{ number_format($subExFactory, 2) }}</span>
                                        @if($custom)
                                            <span class="text-muted text-nowrap pricing-breakdown-info" style="font-size: 10px; cursor: help;" 
                                                  data-bs-toggle="popover" 
                                                  data-bs-trigger="hover focus" 
                                                  data-bs-html="true" 
                                                  title="Pricing Breakdown" 
                                                  data-bs-content="
                                                    <div class='small text-start'>
                                                        <div>Base Cost: <strong>₹{{ number_format($custom->base_cost, 2) }}</strong></div>
                                                        <div>Admin Margin: <strong>₹{{ number_format($custom->admin_margin_amount, 2) }}</strong> ({{ number_format($custom->admin_margin_percentage, 2) }}%)</div>
                                                        <div>DDC Margin: <strong>₹{{ number_format($custom->ddc_margin_amount, 2) }}</strong> ({{ number_format($custom->ddc_margin_percentage, 2) }}%)</div>
                                                        <hr class='my-1'>
                                                        <div>ADC Purchase: <strong class='text-success'>₹{{ number_format($custom->adc_purchase_price, 2) }}</strong></div>
                                                        <div>MBP Amount: <strong>₹{{ number_format($custom->mbp_amount, 2) }}</strong> ({{ number_format($custom->mbp_percentage, 2) }}%)</div>
                                                        <div>ADC Final Margin: <strong class='text-primary'>₹{{ number_format($custom->adc_final_margin, 2) }}</strong></div>
                                                    </div>
                                                  ">
                                                <iconify-icon icon="solar:info-circle-bold" class="align-middle me-1 text-slate-400"></iconify-icon>Breakdown
                                            </span>
                                        @else
                                            <span class="text-muted text-nowrap pricing-breakdown-info text-slate-400" style="font-size: 10px;" data-no-breakdown="true">
                                                Save markup to view breakdown
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-end text-muted small">₹{{ number_format($product->mrp, 2) }}</td>
                                <td class="text-center text-slate-600 font-monospace">{{ $product->gst_rate }}%</td>
                                <td class="text-center">
                                    @php
                                        $statusVal = $custom ? $custom->status : 'Active';
                                    @endphp
                                    <div class="form-check form-switch d-inline-flex align-items-center gap-1">
                                        <input class="form-check-input toggle-status-switch" 
                                               type="checkbox" 
                                               role="switch" 
                                               data-product-id="{{ $product->id }}" 
                                               {{ $statusVal === 'Active' ? 'checked' : '' }}>
                                        <span class="badge {{ $statusVal === 'Active' ? 'bg-success text-white' : 'bg-danger text-white' }} status-badge" style="font-size: 10px;">
                                            {{ $statusVal }}
                                        </span>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button type="button" 
                                            class="btn btn-sm btn-success d-inline-flex align-items-center justify-content-center p-2 save-markup-btn" 
                                            data-product-id="{{ $product->id }}" 
                                            title="Save Markup">
                                        <iconify-icon icon="solar:diskette-bold-duotone" class="fs-16 me-1"></iconify-icon>
                                        Save
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <iconify-icon icon="solar:box-bold-duotone" class="fs-48 mb-2"></iconify-icon>
                                    <h5>No Approved Products Available</h5>
                                    <p class="mb-0">There are no approved products listed by your parent seller yet.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($products->total() > 0)
                <div class="d-flex justify-content-between align-items-center p-3 border-top">
                    <span class="text-muted small">Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} entries</span>
                    @if($products->hasPages())
                        {{ $products->links() }}
                    @endif
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const saveBtns = document.querySelectorAll('.save-markup-btn');
    const statusSwitches = document.querySelectorAll('.toggle-status-switch');

    statusSwitches.forEach(sw => {
        sw.addEventListener('change', function () {
            const productId = this.getAttribute('data-product-id');
            const isChecked = this.checked;
            const newStatus = isChecked ? 'Active' : 'Inactive';
            const badge = this.nextElementSibling;

            this.disabled = true;

            fetch('{{ route("vendor.products.assigned.toggle-status", ["productId" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', productId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    status: newStatus
                })
            })
            .then(res => res.json())
            .then(data => {
                this.disabled = false;
                if (data.success) {
                    badge.textContent = data.status;
                    if (data.status === 'Active') {
                        badge.className = 'badge bg-success text-white status-badge';
                    } else {
                        badge.className = 'badge bg-danger text-white status-badge';
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Status Updated',
                        text: data.message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                } else {
                    this.checked = !isChecked;
                    Swal.fire({
                        icon: 'error',
                        title: 'Failed to update status',
                        text: data.message || 'Something went wrong.',
                        confirmButtonColor: '#ef4444'
                    });
                }
            })
            .catch(err => {
                this.disabled = false;
                this.checked = !isChecked;
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Failed to communicate with the server.',
                    confirmButtonColor: '#ef4444'
                });
            });
        });
    });

    // Initialize all popovers on load
    var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
    popoverTriggerList.map(function (popoverTriggerEl) {
        return new bootstrap.Popover(popoverTriggerEl)
    });

    saveBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            const productId = this.getAttribute('data-product-id');
            const row = document.getElementById(`row-${productId}`);
            const markupInput = row.querySelector('.markup-input');
            const extraPrice = parseFloat(markupInput.value) || 0;

            if (extraPrice < 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Markup',
                    text: 'Markup cannot be negative.',
                    confirmButtonColor: '#ef4444'
                });
                return;
            }

            const parentPrice = parseFloat(markupInput.getAttribute('data-parent-price')) || 0;
            const mrp = parseFloat(markupInput.getAttribute('data-mrp')) || 0;
            const subExFactoryPrice = parentPrice + extraPrice;

            if (subExFactoryPrice > mrp) {
                Swal.fire({
                    icon: 'error',
                    title: 'Invalid Price',
                    text: `Sub-seller price (₹${subExFactoryPrice.toFixed(2)}) cannot exceed MRP (₹${mrp.toFixed(2)}).`,
                    confirmButtonColor: '#ef4444'
                });
                return;
            }

            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span>...';

            fetch('{{ route("vendor.products.assigned.update-markup", ["productId" => "PLACEHOLDER"]) }}'.replace('PLACEHOLDER', productId), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    extra_price: extraPrice
                })
            })
            .then(res => res.json())
            .then(data => {
                btn.disabled = false;
                btn.innerHTML = '<iconify-icon icon="solar:diskette-bold-duotone" class="fs-16 me-1"></iconify-icon>Save';

                if (data.success) {
                    row.querySelector('.sub-ex-factory-cell').textContent = '₹' + data.sub_ex_factory;

                    let infoSpan = row.querySelector('.pricing-breakdown-info');
                    if (infoSpan) {
                        const contentHtml = `
                            <div class='small text-start'>
                                <div>Base Cost: <strong>₹${data.base_cost}</strong></div>
                                <div>Admin Margin: <strong>₹${data.admin_margin_amount}</strong> (${data.admin_margin_percentage}%)</div>
                                <div>DDC Margin: <strong>₹${data.ddc_margin_amount}</strong> (${data.ddc_margin_percentage}%)</div>
                                <hr class='my-1'>
                                <div>ADC Purchase: <strong class='text-success'>₹${data.adc_purchase_price}</strong></div>
                                <div>MBP Amount: <strong>₹${data.mbp_amount}</strong> (${data.mbp_percentage}%)</div>
                                <div>ADC Final Margin: <strong class='text-primary'>₹${data.adc_final_margin}</strong></div>
                            </div>
                        `;
                        
                        if (infoSpan.getAttribute('data-no-breakdown')) {
                            infoSpan.removeAttribute('data-no-breakdown');
                            infoSpan.classList.remove('text-slate-400');
                            infoSpan.style.cursor = 'help';
                            infoSpan.setAttribute('data-bs-toggle', 'popover');
                            infoSpan.setAttribute('data-bs-trigger', 'hover focus');
                            infoSpan.setAttribute('data-bs-html', 'true');
                            infoSpan.setAttribute('title', 'Pricing Breakdown');
                            infoSpan.innerHTML = '<iconify-icon icon="solar:info-circle-bold" class="align-middle me-1 text-slate-400"></iconify-icon>Breakdown';
                        }
                        
                        infoSpan.setAttribute('data-bs-content', contentHtml);
                        
                        let popoverInstance = bootstrap.Popover.getInstance(infoSpan);
                        if (popoverInstance) {
                            popoverInstance.dispose();
                        }
                        new bootstrap.Popover(infoSpan);
                    }

                    Swal.fire({
                        icon: 'success',
                        title: 'Pricing Updated',
                        text: data.message,
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000,
                        timerProgressBar: true
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Update Failed',
                        text: data.message || 'Something went wrong.',
                        confirmButtonColor: '#ef4444'
                    });
                }
            })
            .catch(err => {
                btn.disabled = false;
                btn.innerHTML = '<iconify-icon icon="solar:diskette-bold-duotone" class="fs-16 me-1"></iconify-icon>Save';
                Swal.fire({
                    icon: 'error',
                    title: 'Network Error',
                    text: 'Failed to communicate with the server.',
                    confirmButtonColor: '#ef4444'
                });
            });
        });
    });
});
</script>
@endpush
