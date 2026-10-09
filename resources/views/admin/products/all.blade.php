@extends('layouts.admin')

@section('title', 'All Products & Margin Manager')
@section('page-title', 'All Products & Margin Manager')

@section('content')
    <x-admin.page-header 
        title="All Products & Margin Manager" 
        subtitle="Manage product catalog, filter by vendors, and update Admin, DDC & MBP margins inline with real-time price calculations."
    />

    @if(request('filter') === 'overcosted')
        <div class="alert alert-warning border-0 shadow-sm mb-3 d-flex align-items-center justify-content-between py-2 px-3" style="border-radius: 8px;">
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="solar:danger-circle-bold-duotone" class="fs-18 text-warning"></iconify-icon>
                <span class="fs-13">Filtered by: <strong class="text-uppercase text-dark">Overcosted / High MRP Products (MRP &gt; ₹5,000)</strong></span>
            </div>
            <a href="{{ route('admin.products.all') }}" class="btn btn-sm btn-light fw-medium py-0.5 px-2 fs-11">
                Clear Filter &times;
            </a>
        </div>
    @endif

    <!-- Vendor Filter & Search Toolbar -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-body p-3.5">
            <form method="GET" action="{{ route('admin.products.all') }}" class="row g-3 align-items-center">
                @if(request('filter'))
                    <input type="hidden" name="filter" value="{{ request('filter') }}">
                @endif
                
                <!-- Vendor / Seller Search Filter -->
                <div class="col-lg-4 col-md-6">
                    <label class="form-label fs-12 fw-bold text-uppercase text-muted mb-1">Filter By Vendor / Seller</label>
                    <select name="seller_id" id="sellerFilterSelect" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">— All Vendors & Sub-Sellers —</option>
                        
                        <optgroup label="Primary Vendors / Sellers">
                            @foreach($primaryVendors as $vendor)
                                @php
                                    $comp = $vendor->sellerProfile?->company_name ?: $vendor->name;
                                    $code = $vendor->sellerProfile?->seller_id ? ' (' . $vendor->sellerProfile->seller_id . ')' : '';
                                @endphp
                                <option value="{{ $vendor->id }}" {{ (string)$sellerId === (string)$vendor->id ? 'selected' : '' }}>
                                    {{ $comp }}{{ $code }}
                                </option>
                            @endforeach
                        </optgroup>

                        @if($subSellers->count() > 0)
                            <optgroup label="Sub-Sellers (Delegated Outlets)">
                                @foreach($subSellers as $sub)
                                    @php
                                        $subComp = $sub->sellerProfile?->company_name ?: $sub->name;
                                        $subCode = $sub->sellerProfile?->seller_id ? ' (' . $sub->sellerProfile->seller_id . ')' : '';
                                        $parentName = $sub->parentVendorModel?->sellerProfile?->company_name ?: ($sub->parentVendorModel?->name ?? 'Vendor #' . $sub->sellerProfile?->parent_id);
                                    @endphp
                                    <option value="{{ $sub->id }}" {{ (string)$sellerId === (string)$sub->id ? 'selected' : '' }}>
                                        ↳ {{ $subComp }}{{ $subCode }} [Sub-Seller of {{ $parentName }}]
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-lg-3 col-md-6">
                    <label class="form-label fs-12 fw-bold text-uppercase text-muted mb-1">Status</label>
                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" {{ $status === 'all' || !$status ? 'selected' : '' }}>All Statuses</option>
                        <option value="Approved" {{ $status === 'Approved' ? 'selected' : '' }}>Approved</option>
                        <option value="Pending Approval" {{ $status === 'Pending Approval' ? 'selected' : '' }}>Pending Approval</option>
                        <option value="Inactive" {{ $status === 'Inactive' ? 'selected' : '' }}>Inactive / Deactivated</option>
                        <option value="Draft" {{ $status === 'Draft' ? 'selected' : '' }}>Draft</option>
                        <option value="Rejected" {{ $status === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    </select>
                </div>

                <!-- Search Input -->
                <div class="col-lg-3 col-md-8">
                    <label class="form-label fs-12 fw-bold text-uppercase text-muted mb-1">Search Keywords</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0">
                            <iconify-icon icon="solar:magnifer-linear" class="fs-14 text-muted"></iconify-icon>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Product name, barcode, code..." value="{{ $search }}">
                    </div>
                </div>

                <!-- Action Buttons -->
                <div class="col-lg-2 col-md-4 d-flex align-items-end gap-2 mt-auto">
                    <button type="submit" class="btn btn-sm btn-primary w-100 fw-semibold d-flex align-items-center justify-content-center gap-1">
                        <iconify-icon icon="solar:filter-bold-duotone" class="fs-15"></iconify-icon> Filter
                    </button>
                    @if($sellerId || $search || ($status && $status !== 'all') || request('filter'))
                        <a href="{{ route('admin.products.all') }}" class="btn btn-sm btn-outline-secondary" title="Reset Filters">
                            &times;
                        </a>
                    @endif
                </div>

            </form>
        </div>
    </div>

    <!-- Bulk Margins Action & Product List Card -->
    <div class="card shadow-sm border-0 mb-4" style="border-radius: 12px;">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="fw-bold text-slate-800 fs-16">Products Catalog List</span>
                <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 fs-12">{{ $products->total() }} Products</span>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                <a href="{{ request()->fullUrlWithQuery(['export' => 1]) }}" class="btn btn-sm btn-outline-success fw-semibold d-flex align-items-center gap-1 shadow-sm" title="Export Current Product List to CSV">
                    <iconify-icon icon="solar:export-bold-duotone" class="fs-16"></iconify-icon>
                    Export CSV
                </a>
                <button type="button" id="bulkSaveBtn" class="btn btn-sm btn-success fw-semibold d-none align-items-center gap-1 shadow-sm" onclick="submitBulkMargins()">
                    <iconify-icon icon="solar:diskette-bold-duotone" class="fs-16"></iconify-icon>
                    Save Selected Margins (<span id="selectedCount">0</span>)
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-nowrap" id="productsMarginTable">
                <thead class="bg-light text-slate-700 fs-12 text-uppercase fw-semibold">
                    <tr>
                        <th class="ps-3" style="width: 40px;">
                            <div class="form-check fs-15 mb-0">
                                <input type="checkbox" id="selectAllCheckbox" class="form-check-input">
                            </div>
                        </th>
                        <th style="width: 50px;">#</th>
                        <th>Product Details</th>
                        <th>Seller / Vendor</th>
                        <th>Seller Price (Incl GST)</th>
                        <th style="min-width: 110px;">Admin Margin (%)</th>
                        <th>DDC Purchase Price</th>
                        <th style="min-width: 110px;">DDC Margin (%)</th>
                        <th>ADC Purchase Price</th>
                        <th style="min-width: 110px;">MBP Margin (%)</th>
                        <th>Status</th>
                        <th class="text-end pe-3">Action</th>
                    </tr>
                </thead>
                <tbody class="fs-13">
                    @forelse($products as $product)
                        @php
                            $admPct = $product->admin_margin_percentage !== null ? (float)$product->admin_margin_percentage : $defaultAdminMargin;
                            $ddcPct = $product->ddc_margin_percentage !== null ? (float)$product->ddc_margin_percentage : $defaultDdcMargin;
                            $mbpPct = $product->mbp_percentage !== null ? (float)$product->mbp_percentage : $defaultMbp;

                            $sellerPrice = (float) $product->seller_price_incl_gst;
                            $gstRate     = (float) $product->gst_rate;
                            $mrp         = (float) $product->mrp;
                        @endphp
                        <tr id="product_row_{{ $product->id }}" 
                            data-product-id="{{ $product->id }}"
                            data-seller-price="{{ $sellerPrice }}"
                            data-gst-rate="{{ $gstRate }}"
                            data-mrp="{{ $mrp }}"
                            data-default-admin="{{ $defaultAdminMargin }}"
                            data-default-ddc="{{ $defaultDdcMargin }}"
                            data-default-mbp="{{ $defaultMbp }}">
                            
                            <td class="ps-3">
                                <div class="form-check fs-15 mb-0">
                                    <input type="checkbox" class="form-check-input row-checkbox" value="{{ $product->id }}">
                                </div>
                            </td>

                            <td class="text-muted small">{{ $loop->iteration + (($products->currentPage() - 1) * $products->perPage()) }}</td>

                            <!-- Product Info -->
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    @if($product->featured_image)
                                        <img src="{{ $product->featured_image }}" class="rounded border object-fit-cover" style="width: 40px; height: 40px;">
                                    @else
                                        <div class="rounded border bg-light d-flex align-items-center justify-content-center text-muted" style="width: 40px; height: 40px;">
                                            <iconify-icon icon="solar:box-bold-duotone" class="fs-20"></iconify-icon>
                                        </div>
                                    @endif
                                    <div class="d-flex flex-column">
                                        <a href="{{ route('admin.products.approvals.show', $product->id) }}" class="fw-semibold text-slate-800 text-decoration-none hover-primary">
                                            {{ $product->product_name }}
                                        </a>
                                        <span class="text-muted fs-11">
                                            Code: <code>{{ $product->product_code ?? '—' }}</code> | Barcode: <code>{{ $product->barcode }}</code>
                                        </span>
                                    </div>
                                </div>
                            </td>

                            <!-- Seller & Sub-Seller Info -->
                            <td>
                                <div class="d-flex flex-column gap-1">
                                    <div>
                                        <span class="fw-semibold text-dark fs-12">
                                            {{ $product->seller?->sellerProfile?->company_name ?: ($product->seller->name ?? '—') }}
                                        </span>
                                        <span class="text-muted fs-11 ms-1">
                                            <code>{{ $product->seller?->sellerProfile?->seller_id ?? 'IMV' . $product->seller_id }}</code>
                                        </span>
                                    </div>

                                    <!-- If product has assigned sub-sellers -->
                                    @if($product->subSellerAssignments && $product->subSellerAssignments->count() > 0)
                                        <div class="d-flex flex-wrap gap-1 mt-0.5">
                                            @foreach($product->subSellerAssignments as $assignment)
                                                @php
                                                    $sub = $assignment->subSeller;
                                                    $subName = $sub?->sellerProfile?->company_name ?: ($sub?->name ?? 'Sub-Seller #' . $assignment->sub_seller_id);
                                                @endphp
                                                <span class="badge bg-info-subtle text-info border border-info-subtle fs-10" title="Assigned Sub-Seller">
                                                    <iconify-icon icon="solar:user-bold-duotone" class="fs-10 align-middle me-0.5"></iconify-icon>
                                                    {{ $subName }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @elseif($product->seller?->parent_id)
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle fs-10 w-fit">
                                            Sub-Seller Product
                                        </span>
                                    @endif
                                </div>
                            </td>

                            <!-- Price & GST -->
                            <td>
                                <div class="d-flex flex-column">
                                    <span class="fw-bold text-dark fs-13">₹{{ number_format($sellerPrice, 2) }}</span>
                                    <span class="badge bg-light text-muted border fs-10 w-fit">GST: {{ $gstRate }}%</span>
                                </div>
                            </td>

                            <!-- Admin Margin Input -->
                            <td>
                                <div class="input-group input-group-sm" style="width: 95px;">
                                    <input type="number" step="0.01" min="0" max="100" 
                                           class="form-control text-center admin-margin-input px-1" 
                                           value="{{ $product->admin_margin_percentage }}" 
                                           placeholder="{{ $defaultAdminMargin }}">
                                    <span class="input-group-text px-1.5">%</span>
                                </div>
                            </td>

                            <!-- DDC Purchase Price Preview -->
                            <td>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-12 fw-bold ddc-price-preview">
                                    ₹{{ number_format($product->ddc_purchase_price ?: 0, 2) }}
                                </span>
                            </td>

                            <!-- DDC Margin Input -->
                            <td>
                                <div class="input-group input-group-sm" style="width: 95px;">
                                    <input type="number" step="0.01" min="0" max="100" 
                                           class="form-control text-center ddc-margin-input px-1" 
                                           value="{{ $product->ddc_margin_percentage }}" 
                                           placeholder="{{ $defaultDdcMargin }}">
                                    <span class="input-group-text px-1.5">%</span>
                                </div>
                            </td>

                            <!-- ADC Purchase Price Preview -->
                            <td>
                                <span class="badge bg-info-subtle text-info border border-info-subtle fs-12 fw-bold adc-price-preview">
                                    ₹{{ number_format($product->adc_purchase_price ?: 0, 2) }}
                                </span>
                            </td>

                            <!-- MBP Margin Input -->
                            <td>
                                <div class="input-group input-group-sm" style="width: 95px;">
                                    <input type="number" step="0.01" min="0" max="100" 
                                           class="form-control text-center mbp-margin-input px-1" 
                                           value="{{ $product->mbp_percentage }}" 
                                           placeholder="{{ $defaultMbp }}">
                                    <span class="input-group-text px-1.5">%</span>
                                </div>
                            </td>

                            <!-- Status -->
                            <td>
                                @if($product->status === 'Approved')
                                    <span class="badge bg-success-subtle text-success px-2 py-1 fs-11">Approved</span>
                                @elseif($product->status === 'Pending Approval')
                                    <span class="badge bg-warning-subtle text-warning px-2 py-1 fs-11">Pending Approval</span>
                                @elseif($product->status === 'Rejected')
                                    <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-11">Rejected</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary px-2 py-1 fs-11">Draft</span>
                                @endif
                            </td>

                            <!-- Action -->
                            <td class="text-end pe-3">
                                <button type="button" class="btn btn-sm btn-outline-success py-1 px-2.5 d-inline-flex align-items-center gap-1 fs-11 fw-semibold save-row-btn" onclick="saveRowMargins({{ $product->id }})">
                                    <iconify-icon icon="solar:diskette-bold-duotone" class="fs-14"></iconify-icon> Save
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="12" class="text-center py-5 text-muted">
                                <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="fs-48 text-slate-300 mb-2"></iconify-icon>
                                <p class="mb-0 fw-medium">No products found matching the criteria.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="card-footer bg-white py-3 border-top">
                {{ $products->links() }}
            </div>
        @endif
    </div>

@endsection

@push('scripts')
<script>
    // Initialize TomSelect on Seller Filter if available
    document.addEventListener('DOMContentLoaded', function () {
        const sellerSelect = document.getElementById('sellerFilterSelect');
        if (sellerSelect && typeof TomSelect !== 'undefined') {
            new TomSelect(sellerSelect, {
                create: false,
                sortField: { field: "text", direction: "asc" },
                placeholder: "Type vendor company name or IMV code..."
            });
        }

        // Live calculation on margin input change
        document.querySelectorAll('#productsMarginTable tbody tr').forEach(row => {
            const adminInput = row.querySelector('.admin-margin-input');
            const ddcInput   = row.querySelector('.ddc-margin-input');
            const mbpInput   = row.querySelector('.mbp-margin-input');

            if (adminInput && ddcInput && mbpInput) {
                [adminInput, ddcInput, mbpInput].forEach(inp => {
                    inp.addEventListener('input', () => recalculateRowPricing(row));
                });
            }
        });

        // Select All Checkbox
        const selectAll = document.getElementById('selectAllCheckbox');
        const rowCheckboxes = document.querySelectorAll('.row-checkbox');
        const bulkBtn = document.getElementById('bulkSaveBtn');
        const countSpan = document.getElementById('selectedCount');

        if (selectAll) {
            selectAll.addEventListener('change', function () {
                rowCheckboxes.forEach(cb => cb.checked = this.checked);
                updateBulkBtn();
            });
        }

        rowCheckboxes.forEach(cb => {
            cb.addEventListener('change', updateBulkBtn);
        });

        function updateBulkBtn() {
            const checked = document.querySelectorAll('.row-checkbox:checked');
            if (checked.length > 0) {
                bulkBtn.classList.remove('d-none');
                bulkBtn.classList.add('d-inline-flex');
                countSpan.textContent = checked.length;
            } else {
                bulkBtn.classList.add('d-none');
                bulkBtn.classList.remove('d-inline-flex');
                countSpan.textContent = '0';
            }
        }
    });

    // Full Precision Calculation Engine for Live Preview
    function recalculateRowPricing(row) {
        const sellerPrice = parseFloat(row.getAttribute('data-seller-price')) || 0;
        const gstRate     = parseFloat(row.getAttribute('data-gst-rate')) || 0;
        const defaultAdmin = parseFloat(row.getAttribute('data-default-admin')) || 8;
        const defaultDdc   = parseFloat(row.getAttribute('data-default-ddc')) || 6;

        const adminVal = row.querySelector('.admin-margin-input').value;
        const ddcVal   = row.querySelector('.ddc-margin-input').value;

        const adminMarginPercent = adminVal !== '' ? parseFloat(adminVal) : defaultAdmin;
        const ddcMarginPercent   = ddcVal !== '' ? parseFloat(ddcVal) : defaultDdc;

        // Unrounded intermediate steps (Matching PricingService.php)
        const rawBaseCost = sellerPrice / (1.0 + (gstRate / 100.0));
        const rawAdminMarginAmount = rawBaseCost * (adminMarginPercent / 100.0);
        const rawDdcPurchasePriceWithoutGst = rawBaseCost + rawAdminMarginAmount;
        const rawDdcPurchasePrice = rawDdcPurchasePriceWithoutGst * (1.0 + (gstRate / 100.0));

        const rawDdcMarginAmount = rawDdcPurchasePriceWithoutGst * (ddcMarginPercent / 100.0);
        const rawPriceBeforeGst = rawDdcPurchasePriceWithoutGst + rawDdcMarginAmount;
        const rawSellerGst = rawPriceBeforeGst * (gstRate / 100.0);
        const rawAdcPurchasePrice = rawPriceBeforeGst + rawSellerGst;

        // Display rounded 2-decimal values
        const ddcFinal = Math.round(rawDdcPurchasePrice * 100) / 100;
        const adcFinal = Math.round(rawAdcPurchasePrice * 100) / 100;

        const ddcPreview = row.querySelector('.ddc-price-preview');
        const adcPreview = row.querySelector('.adc-price-preview');

        if (ddcPreview) ddcPreview.textContent = '₹' + ddcFinal.toFixed(2);
        if (adcPreview) adcPreview.textContent = '₹' + adcFinal.toFixed(2);
    }

    // Save Single Row Margins
    function saveRowMargins(productId) {
        const row = document.getElementById('product_row_' + productId);
        const btn = row.querySelector('.save-row-btn');

        const adminMargin = row.querySelector('.admin-margin-input').value;
        const ddcMargin   = row.querySelector('.ddc-margin-input').value;
        const mbpMargin   = row.querySelector('.mbp-margin-input').value;

        btn.disabled = true;
        btn.innerHTML = `<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...`;

        fetch(`{{ url('admin/products') }}/${productId}/update-margins`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                admin_margin_percentage: adminMargin,
                ddc_margin_percentage: ddcMargin,
                mbp_percentage: mbpMargin
            })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = `<iconify-icon icon="solar:check-circle-bold" class="fs-14 text-success"></iconify-icon> Saved`;
            setTimeout(() => {
                btn.innerHTML = `<iconify-icon icon="solar:diskette-bold-duotone" class="fs-14"></iconify-icon> Save`;
            }, 2000);
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = `<iconify-icon icon="solar:diskette-bold-duotone" class="fs-14"></iconify-icon> Save`;
            alert('Failed to update margins.');
        });
    }

    // Bulk Save Selected Row Margins
    function submitBulkMargins() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        if (checked.length === 0) return;

        const marginsData = [];
        checked.forEach(cb => {
            const productId = cb.value;
            const row = document.getElementById('product_row_' + productId);
            if (row) {
                marginsData.push({
                    id: productId,
                    admin_margin: row.querySelector('.admin-margin-input').value,
                    ddc_margin: row.querySelector('.ddc-margin-input').value,
                    mbp_margin: row.querySelector('.mbp-margin-input').value,
                });
            }
        });

        const bulkBtn = document.getElementById('bulkSaveBtn');
        bulkBtn.disabled = true;
        bulkBtn.innerHTML = `<span class="spinner-border spinner-border-sm"></span> Saving...`;

        fetch(`{{ route('admin.products.bulk-update-margins') }}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ margins: marginsData })
        })
        .then(res => res.json())
        .then(data => {
            alert(data.message || 'Margins updated successfully!');
            window.location.reload();
        })
        .catch(err => {
            bulkBtn.disabled = false;
            bulkBtn.innerHTML = `<iconify-icon icon="solar:diskette-bold-duotone" class="fs-16"></iconify-icon> Save Selected Margins`;
            alert('Bulk update failed.');
        });
    }
</script>
@endpush
