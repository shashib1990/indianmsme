@extends('layouts.vendor')

@section('title', 'My Product Catalog')
@section('page-title', 'My Products')

@section('content')
    <!-- Action Header -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">Product Catalog</h4>
                            <p class="text-muted small mb-0">Create and manage your products. Approved products will be listed automatically across customer, ADC, and DDC portals.</p>
                        </div>
                        <div class="col-md-6 text-md-end d-flex flex-wrap align-items-center justify-content-md-end gap-2">
                            <!-- 1. Add Products -->
                            <a href="{{ route('vendor.products.create') }}" class="btn btn-success fw-semibold">
                                <iconify-icon icon="solar:add-circle-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                                Add Products
                            </a>
                            <!-- 2. Bulk New Product Upload -->
                            <button type="button" class="btn btn-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#bulkUploadModal">
                                <iconify-icon icon="solar:upload-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                                Bulk New Product Upload
                            </button>
                            <!-- 3. Bulk Update Products -->
                            <button type="button" class="btn btn-outline-primary fw-semibold" data-bs-toggle="modal" data-bs-target="#bulkUpdateModal">
                                <iconify-icon icon="solar:pen-new-square-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                                Bulk Update Products
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="row mb-3 g-2">
        <div class="col-md-8">
            <form id="filterForm" action="{{ route('vendor.products.index') }}" method="GET" class="d-flex flex-wrap gap-2">
                <input type="hidden" name="sort" id="formSort" value="{{ $sort }}">
                <input type="hidden" name="order" id="formOrder" value="{{ $order }}">
                <div class="input-group" style="max-width: 250px;">
                    <span class="input-group-text bg-white border-end-0"><iconify-icon icon="solar:magnifer-bold-duotone" class="text-muted"></iconify-icon></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search name or barcode..." value="{{ $search }}">
                </div>
                <select name="status" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="Draft" {{ $status === 'Draft' ? 'selected' : '' }}>Draft</option>
                    <option value="Pending Approval" {{ $status === 'Pending Approval' ? 'selected' : '' }}>Pending Approval</option>
                    <option value="Approved" {{ $status === 'Approved' ? 'selected' : '' }}>Active</option>
                    <option value="Rejected" {{ $status === 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="Inactive" {{ $status === 'Inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
                <select name="limit" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="10" {{ $limit == 10 ? 'selected' : '' }}>10 per page</option>
                    <option value="15" {{ $limit == 15 ? 'selected' : '' }}>15 per page</option>
                    <option value="25" {{ $limit == 25 ? 'selected' : '' }}>25 per page</option>
                    <option value="50" {{ $limit == 50 ? 'selected' : '' }}>50 per page</option>
                    <option value="100" {{ $limit == 100 ? 'selected' : '' }}>100 per page</option>
                </select>
                <button type="submit" class="btn btn-outline-secondary">Filter</button>
            </form>
        </div>
        <div class="col-md-4 text-md-end">
            <div class="d-inline-flex gap-1.5">
                <button class="btn btn-outline-success" id="bulkSubmitBtn" style="display: none;">
                    <iconify-icon icon="solar:shield-check-bold-duotone" class="align-middle me-1"></iconify-icon>
                    Submit Selected (<span id="submitCount">0</span>)
                </button>
                <button class="btn btn-outline-danger" id="bulkDeleteBtn" style="display: none;">
                    <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="align-middle me-1"></iconify-icon>
                    Delete Selected (<span id="deleteCount">0</span>)
                </button>
            </div>
        </div>
    </div>



    <!-- Table -->
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
                            <th style="width: 80px;">Image</th>
                            <x-admin.table-header field="product_name" :sort="$sort" :order="$order">Product Name</x-admin.table-header>
                            <th>Brand</th>
                            <th>Category</th>
                            <x-admin.table-header field="case_pack_quantity" :sort="$sort" :order="$order">MOQ</x-admin.table-header>
                            <x-admin.table-header field="mrp" :sort="$sort" :order="$order">MRP</x-admin.table-header>
                            <x-admin.table-header field="seller_price_incl_gst" :sort="$sort" :order="$order">Seller Price (Incl. GST)</x-admin.table-header>
                            <th>GST %</th>
                            <th>Status</th>
                            <th class="text-end pe-3" style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                            <tr>
                                <td class="ps-3">
                                    <div class="form-check fs-15">
                                        <input type="checkbox" class="form-check-input row-checkbox" value="{{ $product->id }}">
                                    </div>
                                </td>
                                <td class="ps-2 text-muted small">{{ $loop->iteration + (($products->currentPage() - 1) * $products->perPage()) }}</td>
                                <td>
                                    @if($product->featured_image)
                                        <div class="position-relative d-inline-block">
                                            <img src="{{ $product->featured_image }}" class="rounded border" style="width: 44px; height: 44px; object-fit: cover;">
                                            <button type="button" class="btn btn-sm btn-dark position-absolute bottom-0 end-0 p-0 rounded-circle d-flex align-items-center justify-content-center shadow-sm" style="width:20px; height:20px; opacity:0.9;" onclick="openQuickImageModal({{ $product->id }}, '{{ addslashes($product->featured_image) }}', '{{ addslashes($product->product_name) }}')" title="Change Image">
                                                <iconify-icon icon="solar:camera-bold" style="font-size:11px;"></iconify-icon>
                                            </button>
                                        </div>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-warning rounded border-dashed p-1 d-flex flex-column align-items-center justify-content-center gap-1 shadow-2xs" style="width: 44px; height: 44px;" onclick="openQuickImageModal({{ $product->id }}, '', '{{ addslashes($product->product_name) }}')" title="Upload Product Image">
                                            <iconify-icon icon="solar:camera-add-bold-duotone" class="text-warning fs-18"></iconify-icon>
                                            <span class="fs-9 fw-bold text-warning" style="line-height:1;">+ Image</span>
                                        </button>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold text-slate-800">{{ $product->product_name }}</span>
                                        <span class="text-muted small fs-11">Code: <code>{{ $product->product_code ?? '—' }}</code> | Barcode: <code>{{ $product->barcode }}</code></span>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-slate-700 border">{{ $product->brand->name ?? '—' }}</span>
                                </td>
                                <td class="small">{{ $product->category->name ?? '—' }}</td>
                                <td class="fw-semibold text-slate-800">{{ $product->case_pack_quantity ?: 1 }} @if($product->unit_of_measure)<span class="text-muted small fw-normal">({{ $product->unit_of_measure }})</span>@endif</td>
                                <td class="fw-semibold text-slate-800">₹{{ number_format($product->mrp, 2) }}</td>
                                <td class="fw-semibold text-slate-800">₹{{ number_format($product->seller_price_incl_gst, 2) }}</td>
                                <td>{{ $product->gst_rate }}%</td>
                                <td>
                                    @if(in_array($product->status, ['Approved', 'Inactive']))
                                        <div class="form-check form-switch d-inline-flex align-items-center gap-1">
                                            <input class="form-check-input product-status-toggle" 
                                                   type="checkbox" 
                                                   role="switch" 
                                                   data-id="{{ $product->id }}"
                                                   data-url="{{ route('vendor.products.toggle-active', $product->id) }}"
                                                   {{ $product->status === 'Approved' ? 'checked' : '' }}>
                                            <span class="badge {{ $product->status === 'Approved' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} px-2 py-1 fs-12 product-status-badge-{{ $product->id }}">
                                                {{ $product->status === 'Approved' ? 'Active' : $product->status }}
                                            </span>
                                        </div>
                                    @elseif($product->status === 'Pending Approval')
                                        <span class="badge bg-warning-subtle text-warning px-2 py-1 fs-12">Pending Approval</span>
                                    @elseif($product->status === 'Rejected')
                                        <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-12" data-bs-toggle="tooltip" data-bs-placement="top" title="Rejection Reason: {{ $product->admin_remarks }}">
                                            Rejected 🛈
                                        </span>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary px-2 py-1 fs-12">Draft</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1">
                                        @if($product->status === 'Draft' || $product->status === 'Rejected')
                                            <!-- Submit for approval -->
                                            <form action="{{ route('vendor.products.submit', $product->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-outline-success d-flex align-items-center justify-content-center p-1" style="width: 28px; height: 28px;" title="Submit for Approval">
                                                    <iconify-icon icon="solar:shield-check-bold-duotone" class="fs-14"></iconify-icon>
                                                </button>
                                            </form>
                                        @endif

                                        <a href="{{ route('vendor.products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary d-flex align-items-center justify-content-center p-1" style="width: 28px; height: 28px;" title="Edit product">
                                            <iconify-icon icon="solar:pen-bold-duotone" class="fs-14"></iconify-icon>
                                        </a>

                                        <form action="{{ route('vendor.products.destroy', $product->id) }}" method="POST" class="delete-product-form d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger d-flex align-items-center justify-content-center p-1" style="width: 28px; height: 28px;" title="Delete">
                                                <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="fs-14"></iconify-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-5 text-muted">
                                    <iconify-icon icon="solar:box-bold-duotone" class="fs-48 mb-2"></iconify-icon>
                                    <h5>No Products Listed</h5>
                                    <p class="mb-0">You haven't added any products to your catalog yet. Click "Add Product" above.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($products->total() > 0)
                <div class="d-flex justify-content-between align-items-center p-3 border-top">
                    <span class="text-muted small">Showing {{ $products->firstItem() }} to {{ $products->lastItem() }} of {{ $products->total() }} products</span>
                    @if($products->hasPages())
                        {{ $products->links() }}
                    @endif
                </div>
            @endif
        </div>
    </div>
 
    <!-- Bulk Upload Modal -->
    <div class="modal fade text-slate-800" id="bulkUploadModal" tabindex="-1" aria-labelledby="bulkUploadModalLabel" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('vendor.products.bulk-import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header bg-light py-3 border-bottom">
                        <h5 class="modal-title fw-bold text-slate-800" id="bulkUploadModalLabel">
                            <iconify-icon icon="solar:upload-square-bold-duotone" class="align-middle me-1 text-primary fs-22"></iconify-icon>
                            Bulk Product Import
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-primary bg-primary-subtle border-0 mb-4 p-3 shadow-2xs">
                            <h6 class="fw-bold mb-2 d-flex align-items-center justify-content-between gap-1.5 text-primary-emphasis">
                                <span class="d-flex align-items-center gap-1.5">
                                    <iconify-icon icon="solar:info-square-bold-duotone" class="fs-18"></iconify-icon>
                                    Excel / CSV Import Instructions
                                </span>
                                <a href="{{ route('vendor.products.bulk-sample') }}" class="btn btn-xs btn-primary d-flex align-items-center gap-1 text-white py-1 px-2 fw-semibold" style="font-size: 11px; border-radius: 4px;">
                                    <iconify-icon icon="solar:download-bold-duotone" class="fs-14"></iconify-icon>
                                    Download Sample Excel
                                </a>
                            </h6>
                            <p class="small mb-2 text-slate-700">1. Prepare your product list in Excel/CSV with columns: <code>product_name, description, product_specification, storage_instructions, category, brand, search_keywords, barcode, hsn_code, mrp, seller_price_incl_gst, gst_rate, case_pack_quantity, unit_of_measure, shelf_life, length, width, height, dimension_unit, weight, weight_unit, featured_image</code></p>
                            <p class="small mb-0 text-slate-700">2. **Category** and **Brand** names in Excel must match existing active records in database (otherwise the row will be skipped).</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label required fw-medium text-slate-700">Excel / CSV File</label>
                                <input type="file" name="file" class="form-control" accept=".csv,.xlsx,.xls" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-3 border-top">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success d-flex align-items-center gap-1">
                            <iconify-icon icon="solar:play-bold-duotone" class="fs-16"></iconify-icon>
                            Start Import
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Import / Update Result Report Modal -->
    @if(session()->has('import_result'))
        @php
            $r = session('import_result');
            $isUpdate = ($r['type'] ?? '') === 'update';
        @endphp
        <div class="modal fade text-slate-800" id="importReportModal" tabindex="-1" aria-hidden="true" style="display: none;">
            <div class="modal-dialog modal-lg modal-dialog-centered">
                <div class="modal-content border-0 shadow">
                    <div class="modal-header {{ ($r['failed'] ?? 0) > 0 ? 'bg-danger bg-opacity-10' : (($r['skipped'] ?? 0) > 0 ? 'bg-warning bg-opacity-10' : 'bg-success bg-opacity-10') }} py-3 border-bottom">
                        <h5 class="modal-title {{ ($r['failed'] ?? 0) > 0 ? 'text-danger-emphasis' : (($r['skipped'] ?? 0) > 0 ? 'text-warning-emphasis' : 'text-success-emphasis') }} d-flex align-items-center gap-1.5 fw-bold">
                            @if(($r['failed'] ?? 0) > 0 || ($r['skipped'] ?? 0) > 0)
                                <iconify-icon icon="solar:danger-bold-duotone" class="fs-22 text-warning"></iconify-icon>
                            @else
                                <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-22 text-success"></iconify-icon>
                            @endif
                            Bulk {{ $isUpdate ? 'Update' : 'Upload' }} Report: {{ $r['inserted'] }} {{ $isUpdate ? 'Updated' : 'Imported' }}, {{ $r['skipped'] }} Skipped, {{ $r['failed'] }} Failed
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="bg-light p-3 rounded-3 mb-3 border">
                            <div class="row text-center">
                                <div class="col-3 border-end">
                                    <div class="text-muted small">Total Rows</div>
                                    <div class="fs-18 fw-bold text-dark">{{ $r['total'] }}</div>
                                </div>
                                <div class="col-3 border-end">
                                    <div class="text-muted small">{{ $isUpdate ? 'Updated' : 'Imported' }}</div>
                                    <div class="fs-18 fw-bold text-success">{{ $r['inserted'] }}</div>
                                </div>
                                <div class="col-3 border-end">
                                    <div class="text-muted small">Skipped</div>
                                    <div class="fs-18 fw-bold text-warning">{{ $r['skipped'] }}</div>
                                </div>
                                <div class="col-3">
                                    <div class="text-muted small">Failed</div>
                                    <div class="fs-18 fw-bold text-danger">{{ $r['failed'] }}</div>
                                </div>
                            </div>
                        </div>

                        @if(count($r['details'] ?? []) > 0)
                            <div class="table-responsive border rounded-3" style="max-height: 350px; overflow-y: auto;">
                                <table class="table table-hover table-centered mb-0 align-middle">
                                    <thead class="table-light sticky-top">
                                        <tr>
                                            <th class="ps-3" style="width: 70px;">Row #</th>
                                            <th style="width: 180px;">Product Name / ID</th>
                                            <th style="width: 100px;">Status</th>
                                            <th class="pe-3">Reason / Error</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($r['details'] as $det)
                                            <tr>
                                                <td class="ps-3 fw-semibold">{{ $det['row'] }}</td>
                                                <td><strong class="text-dark">{{ $det['data'] }}</strong></td>
                                                <td>
                                                    @if(($det['status'] ?? '') === 'Failed')
                                                        <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-12">Failed</span>
                                                    @else
                                                        <span class="badge bg-warning-subtle text-warning px-2 py-1 fs-12">Skipped</span>
                                                    @endif
                                                </td>
                                                <td class="pe-3 text-muted text-wrap small">{{ $det['reason'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                    <div class="modal-footer bg-light py-2 border-top d-flex align-items-center justify-content-between">
                        <div>
                            <button type="button" class="btn btn-outline-danger btn-sm fw-bold me-1" onclick="exportModalTableToExcel('importReportModal', 'Vendor_Product_Import_Skipped_Failed_Records.xlsx', true)">
                                <iconify-icon icon="solar:download-square-linear" class="fs-16 align-middle me-1"></iconify-icon> Export Skipped &amp; Failed (.xlsx)
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm fw-bold" onclick="exportModalTableToExcel('importReportModal', 'Vendor_Product_Import_All_Records.xlsx', false)">
                                <iconify-icon icon="solar:download-square-linear" class="fs-16 align-middle me-1"></iconify-icon> Export All Logs (.xlsx)
                            </button>
                        </div>
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                    </div>
                </div>
            </div>
        </div>

        @push('scripts')
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var importReportModalEl = document.getElementById('importReportModal');
                if (importReportModalEl) {
                    var reportModal = new bootstrap.Modal(importReportModalEl);
                    reportModal.show();
                }
            });
        </script>
        @endpush
    @endif

    <!-- Bulk Update Modal -->
    <div class="modal fade text-slate-800" id="bulkUpdateModal" tabindex="-1" aria-labelledby="bulkUpdateModalLabel" aria-hidden="true" style="display: none;">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('vendor.products.bulk-update-upload') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header bg-light py-3 border-bottom">
                        <h5 class="modal-title fw-bold text-slate-800" id="bulkUpdateModalLabel">
                            <iconify-icon icon="solar:pen-new-square-bold-duotone" class="align-middle me-1 text-primary fs-22"></iconify-icon>
                            Bulk Product Update
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="alert alert-primary bg-primary-subtle border-0 mb-4 p-3 shadow-2xs">
                            <h6 class="fw-bold mb-2 d-flex align-items-center justify-content-between gap-1.5 text-primary-emphasis">
                                <span class="d-flex align-items-center gap-1.5">
                                    <iconify-icon icon="solar:info-square-bold-duotone" class="fs-18"></iconify-icon>
                                    Bulk Update Instructions
                                </span>
                                <a href="{{ route('vendor.products.bulk-update-download') }}" class="btn btn-xs btn-primary d-flex align-items-center gap-1 text-white py-1 px-2 fw-semibold" style="font-size: 11px; border-radius: 4px;">
                                    <iconify-icon icon="solar:download-bold-duotone" class="fs-14"></iconify-icon>
                                    Download Current Products
                                </a>
                            </h6>
                            <p class="small mb-2 text-slate-700">1. Download your current product catalog file. It contains all your products with an extra column at the start: <code>id</code>.</p>
                            <p class="small mb-2 text-slate-700">2. Edit the product details in the spreadsheet. Do **NOT** change or delete the values in the <code>id</code> column, as the system uses them to match and update the correct rows.</p>
                            <p class="small mb-0 text-slate-700">3. Upload the modified Excel/CSV file below to apply updates directly. Category selections are not required because existing categories are preserved.</p>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-12">
                                <label class="form-label required fw-medium text-slate-700">Excel / CSV File</label>
                                <input type="file" name="file" class="form-control" accept=".csv,.xlsx,.xls" required>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-3 border-top">
                        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary d-flex align-items-center gap-1">
                            <iconify-icon icon="solar:play-bold-duotone" class="fs-16"></iconify-icon>
                            Start Update
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Quick Upload Product Image Modal -->
    <div class="modal fade" id="quickImageModal" tabindex="-1" aria-hidden="true" style="display: none; z-index: 1055;">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                <div class="modal-header border-bottom py-3">
                    <h5 class="modal-title fw-bold text-slate-800 fs-16 d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:gallery-wide-bold-duotone" class="text-primary fs-20"></iconify-icon>
                        Upload Product Image
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="quickImageForm" method="POST" action="">
                    @csrf
                    <div class="modal-body p-4">
                        <p class="small text-muted mb-3" id="quickImageProductName">Updating image for product...</p>
                        
                        <div class="mb-3 border rounded bg-light d-flex align-items-center justify-content-center overflow-hidden position-relative mx-auto" style="height: 180px; width: 180px;">
                            <img id="quick_image_preview" src="" class="img-fluid object-fit-contain d-none" style="max-height: 100%;">
                            <div id="quick_image_placeholder" class="text-muted small text-center">
                                <iconify-icon icon="solar:gallery-bold-duotone" class="fs-40 mb-1"></iconify-icon>
                                <p class="mb-0">No Image Selected</p>
                            </div>
                        </div>

                        <div class="input-group">
                            <input type="text" name="featured_image" id="quick_image_input" class="form-control" placeholder="Image URL / Media Path" required>
                            <button type="button" class="btn btn-outline-primary" onclick="openMediaLibrary('quick_image_input', 'quick_image_preview', false)">
                                <iconify-icon icon="solar:gallery-wide-bold-duotone" class="align-middle"></iconify-icon>
                                Media Library
                            </button>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2 px-3 border-top">
                        <button type="button" class="btn btn-sm btn-light fw-medium" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-primary fw-medium d-flex align-items-center gap-1">
                            <iconify-icon icon="solar:diskette-bold-duotone" class="fs-16"></iconify-icon>
                            Save Image
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @include('vendor.products.media-modal')
@endsection

@push('scripts')
<script>
function openQuickImageModal(productId, currentImage, productName) {
    const form = document.getElementById('quickImageForm');
    const nameEl = document.getElementById('quickImageProductName');
    const input = document.getElementById('quick_image_input');
    const preview = document.getElementById('quick_image_preview');
    const placeholder = document.getElementById('quick_image_placeholder');

    let actionUrl = "{{ route('vendor.products.update-image', ['id' => ':id']) }}";
    form.action = actionUrl.replace(':id', productId);
    nameEl.textContent = 'Updating image for: ' + productName;
    input.value = currentImage || '';

    if (currentImage) {
        preview.src = currentImage;
        preview.classList.remove('d-none');
        placeholder.classList.add('d-none');
    } else {
        preview.src = '';
        preview.classList.add('d-none');
        placeholder.classList.remove('d-none');
    }

    const modal = new bootstrap.Modal(document.getElementById('quickImageModal'));
    modal.show();
}

document.addEventListener('DOMContentLoaded', function () {
    // Checkbox and bulk actions setup
    const selectAll = document.getElementById('selectAllCheckbox');
    const checkboxes = document.querySelectorAll('.row-checkbox');
    const bulkDelete = document.getElementById('bulkDeleteBtn');
    const bulkSubmit = document.getElementById('bulkSubmitBtn');
    const submitCount = document.getElementById('submitCount');
    const deleteCount = document.getElementById('deleteCount');

    if (selectAll) {
        selectAll.addEventListener('change', function () {
            checkboxes.forEach(cb => {
                cb.checked = this.checked;
            });
            updateBulkButtons();
        });
    }

    checkboxes.forEach(cb => {
        cb.addEventListener('change', updateBulkButtons);
    });

    function updateBulkButtons() {
        const checked = document.querySelectorAll('.row-checkbox:checked');
        const count = checked.length;

        submitCount.textContent = count;
        deleteCount.textContent = count;

        if (count > 0) {
            bulkDelete.style.display = 'inline-block';
            bulkSubmit.style.display = 'inline-block';
        } else {
            bulkDelete.style.display = 'none';
            bulkSubmit.style.display = 'none';
        }
    }

    // Bulk Delete Action
    if (bulkDelete) {
        bulkDelete.addEventListener('click', function () {
            const checked = document.querySelectorAll('.row-checkbox:checked');
            const ids = Array.from(checked).map(cb => cb.value);

            Swal.fire({
                title: 'Delete selected products?',
                text: `Are you sure you want to delete the ${ids.length} selected products? This is irreversible.`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Delete All'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitBulkAction('delete', ids);
                }
            });
        });
    }

    // Bulk Submit Action
    if (bulkSubmit) {
        bulkSubmit.addEventListener('click', function () {
            const checked = document.querySelectorAll('.row-checkbox:checked');
            const ids = Array.from(checked).map(cb => cb.value);

            Swal.fire({
                title: 'Submit selected products?',
                text: `Submit the ${ids.length} selected products for administrator approval?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#10b981',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Yes, Submit All'
            }).then((result) => {
                if (result.isConfirmed) {
                    submitBulkAction('submit', ids);
                }
            });
        });
    }

    function submitBulkAction(action, ids) {
        Swal.showLoading();
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = "{{ route('vendor.products.bulk') }}";

        const csrfInput = document.createElement('input');
        csrfInput.type = 'hidden';
        csrfInput.name = '_token';
        csrfInput.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfInput);

        const actionInput = document.createElement('input');
        actionInput.type = 'hidden';
        actionInput.name = 'action';
        actionInput.value = action;
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

    // Individual Delete confirmation
    const deleteForms = document.querySelectorAll('.delete-product-form');
    deleteForms.forEach(form => {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            Swal.fire({
                title: 'Delete this product?',
                text: 'Are you sure you want to remove this product from your catalog? This will delete all history and assignments.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                cancelButtonColor: '#64748b',
                confirmButtonText: 'Delete'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(form.action, {
                        method: 'POST',
                        body: new FormData(form),
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                        }
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success !== false) {
                            if (typeof Toastify === 'function') {
                                Toastify({ text: data.message || 'Product deleted', duration: 3000, style: { background: '#10b981' } }).showToast();
                            }
                            var tr = form.closest('tr');
                            if (tr) {
                                tr.remove();
                            } else {
                                reloadTablePartial();
                            }
                        } else {
                            Swal.fire('Error', data.message || 'Could not delete product', 'error');
                        }
                    })
                    .catch(err => {
                        console.error('Delete error:', err);
                        form.submit();
                    });
                }
            });
        });
    });

    // Initialize tooltips for remarks
    const tooltips = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltips.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });

    // Bulk Upload Select cascade logic
    const bulkCategory = document.getElementById('bulkCategorySelect');
    const bulkSubCategory = document.getElementById('bulkSubCategorySelect');
    const bulkChildCategory = document.getElementById('bulkChildCategorySelect');

    if (bulkCategory) {
        bulkCategory.addEventListener('change', function () {
            const catId = this.value;
            bulkSubCategory.innerHTML = '<option value="">Loading...</option>';
            bulkChildCategory.innerHTML = '<option value="">— Select Sub-Category First —</option>';

            if (!catId) {
                bulkSubCategory.innerHTML = '<option value="">— Select Category First —</option>';
                return;
            }

            // Fetch sub-categories
            fetch("{{ route('vendor.sub-categories.ajax') }}?category_id=" + catId)
                .then(res => res.json())
                .then(data => {
                    bulkSubCategory.innerHTML = '<option value="">— Select Sub-Category —</option>';
                    data.forEach(sub => {
                        const opt = document.createElement('option');
                        opt.value = sub.id;
                        opt.textContent = sub.name;
                        bulkSubCategory.appendChild(opt);
                    });
                });
        });
    }

    if (bulkSubCategory) {
        bulkSubCategory.addEventListener('change', function () {
            const subId = this.value;
            bulkChildCategory.innerHTML = '<option value="">Loading...</option>';

            if (!subId) {
                bulkChildCategory.innerHTML = '<option value="">— Select Sub-Category First —</option>';
                return;
            }

            fetch("{{ route('vendor.child-categories.ajax') }}?sub_category_id=" + subId)
                .then(res => res.json())
                .then(data => {
                    bulkChildCategory.innerHTML = '<option value="">— Select Child-Category —</option>';
                    data.forEach(child => {
                        const opt = document.createElement('option');
                        opt.value = child.id;
                        opt.textContent = child.name;
                        bulkChildCategory.appendChild(opt);
                    });
                });
        });
    }

    if (window.location.hash === '#bulkUploadModal') {
        const uModal = document.getElementById('bulkUploadModal');
        if (uModal) new bootstrap.Modal(uModal).show();
    } else if (window.location.hash === '#bulkUpdateModal') {
        const upModal = document.getElementById('bulkUpdateModal');
        if (upModal) new bootstrap.Modal(upModal).show();
    }

    // AJAX Product Active/Inactive Toggle (No Page Reload)
    document.addEventListener('change', function (e) {
        if (e.target && e.target.classList.contains('product-status-toggle')) {
            const checkbox = e.target;
            const url = checkbox.dataset.url;
            const productId = checkbox.dataset.id;
            const badge = document.querySelector(`.product-status-badge-${productId}`);
            
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
                    checkbox.checked = (data.status === 'Approved');
                    if (badge) {
                        badge.className = `badge ${data.badge_class} px-2 py-1 fs-12 product-status-badge-${productId}`;
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
