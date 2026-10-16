@extends('layouts.adc')

@section('title', 'Regional Product Master')
@section('page-title', 'Regional Product Master')

@section('content')
    <!-- Header Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                Regional Product Master
                            </h4>
                            <p class="text-muted small mb-0">Manage independent regional products for your ADC center. Products created here are stored as Draft until Stocked In.</p>
                        </div>
                        <div class="col-md-6 text-md-end d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
                            <a href="{{ route('adc.local-products.comparison') }}" class="btn btn-outline-info btn-sm fw-semibold">
                                <iconify-icon icon="solar:scale-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Price Analytics
                            </a>
                            <a href="{{ route('adc.local-purchases.create') }}" class="btn btn-outline-success btn-sm fw-semibold">
                                <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Stock In
                            </a>
                            <button type="button" class="btn btn-outline-primary btn-sm fw-semibold shadow-sm" data-bs-toggle="modal" data-bs-target="#bulkUploadModal">
                                <iconify-icon icon="solar:upload-track-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Bulk Upload
                            </button>
                            <a href="{{ route('adc.local-products.create') }}" class="btn btn-primary btn-sm fw-semibold">
                                <iconify-icon icon="solar:add-circle-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon> Create Regional Product
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Alert Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-20 text-success"></iconify-icon>
                <span>{{ session('success') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-center gap-2">
                <iconify-icon icon="solar:danger-bold-duotone" class="fs-20 text-danger"></iconify-icon>
                <span>{{ session('error') }}</span>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('import_errors') && count(session('import_errors')) > 0)
        <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
            <div class="d-flex align-items-start gap-2">
                <iconify-icon icon="solar:danger-triangle-bold-duotone" class="fs-22 text-warning flex-shrink-0 mt-0.5"></iconify-icon>
                <div class="flex-grow-1">
                    <div class="fw-bold text-dark mb-1">
                        Notice on Skipped / Errored Rows ({{ count(session('import_errors')) }}):
                    </div>
                    <ul class="mb-0 ps-3 fs-12 text-slate-700" style="max-height: 160px; overflow-y: auto;">
                        @foreach(session('import_errors') as $err)
                            <li>{{ $err }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Filter & Search Card -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-3">
            <form action="{{ route('adc.local-products.index') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-md-6 col-lg-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted"><iconify-icon icon="solar:magnifer-linear"></iconify-icon></span>
                        <input type="text" name="search" class="form-control bg-light border-start-0 ps-0" placeholder="Search by name, local code (LCL...), barcode..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-md-4 col-lg-4">
                    <select name="category_id" class="form-select bg-light">
                        <option value="">All Categories</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}" {{ $categoryId == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-2 col-lg-3 d-flex gap-2">
                    <button type="submit" class="btn btn-primary fw-semibold btn-sm w-100 py-2">Filter</button>
                    @if($search || $categoryId)
                        <a href="{{ route('adc.local-products.index') }}" class="btn btn-outline-secondary btn-sm py-2 px-3"><iconify-icon icon="solar:restart-bold-duotone"></iconify-icon></a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Products Table Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light text-uppercase fs-11 text-muted fw-bold border-bottom">
                        <tr>
                            <th class="ps-4">Product Details</th>
                            <th>Code</th>
                            <th>Category</th>
                            <th>HSN & GST</th>
                            <th>Brand</th>
                            <th class="text-center">Unit</th>
                            <th class="text-center">Status</th>
                            <th class="pe-4 text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="fs-13">
                        @forelse($products as $product)
                            <tr>
                                <td class="ps-4">
                                    <div class="d-flex align-items-center gap-3">
                                        @if($product->product_image)
                                            @php
                                                $imgSrc = (str_starts_with($product->product_image, 'http://') || str_starts_with($product->product_image, 'https://') || str_starts_with($product->product_image, '/'))
                                                    ? $product->product_image
                                                    : asset('storage/' . $product->product_image);
                                            @endphp
                                            <img src="{{ $imgSrc }}" class="rounded-3 border" style="width: 48px; height: 48px; object-fit: cover;" alt="product">
                                        @else
                                            <div class="rounded-3 bg-emerald-50 text-emerald-700 d-flex align-items-center justify-content-center fw-bold" style="width: 48px; height: 48px; background: #ecfdf5; color: #047857;">
                                                <iconify-icon icon="solar:box-bold-duotone" class="fs-24"></iconify-icon>
                                            </div>
                                        @endif
                                        <div>
                                            <h6 class="fw-semibold text-slate-800 mb-0">{{ $product->product_name }}</h6>
                                            @if($product->barcode)
                                                <span class="text-muted fs-11">Barcode: <code>{{ $product->barcode }}</code></span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary fw-bold px-2 py-1 fs-12" style="border-radius: 8px;">
                                        {{ $product->local_product_code }}
                                    </span>
                                </td>
                                <td>
                                    <span class="fw-medium text-slate-700">{{ $product->category->name ?? '—' }}</span>
                                </td>
                                <td>
                                    <div class="d-flex flex-column">
                                        <span class="fw-semibold text-slate-700 fs-12">HSN: {{ $product->hsn_code ?? 'N/A' }}</span>
                                        <span class="badge bg-info-subtle text-info w-auto d-inline-block text-start fs-10 px-2 py-0.5 rounded-pill">GST {{ $product->gst_rate }}%</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="text-slate-600">{{ $product->brand->name ?? 'Unbranded' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary px-2 py-1 rounded-2">{{ $product->unit_of_measure }}</span>
                                </td>
                                <td>
                                    @if($product->status === 'Draft')
                                        <span class="badge bg-warning-subtle text-warning fw-semibold px-2.5 py-1 rounded-pill">
                                            <iconify-icon icon="solar:notes-bold-duotone" class="align-middle me-1"></iconify-icon> Draft
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success fw-semibold px-2.5 py-1 rounded-pill">
                                            <iconify-icon icon="solar:check-circle-bold-duotone" class="align-middle me-1"></iconify-icon> Active
                                        </span>
                                    @endif
                                </td>
                                <td class="pe-4 text-end">
                                    <div class="d-inline-flex gap-1">
                                        <a href="{{ route('adc.local-purchases.create', ['local_product_id' => $product->id]) }}" class="btn btn-sm btn-success px-2.5 py-1 text-white fw-semibold" title="Stock In / Add Inventory">
                                            <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="align-middle me-1"></iconify-icon> Stock In
                                        </a>
                                        <a href="{{ route('adc.local-products.edit', $product->id) }}" class="btn btn-sm btn-outline-primary px-2 py-1" title="Edit Master">
                                            <iconify-icon icon="solar:pen-bold-duotone" class="align-middle"></iconify-icon>
                                        </a>
                                        @if(!$product->inventories()->exists())
                                            <form action="{{ route('adc.local-products.destroy', $product->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this Regional Product master?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger px-2 py-1" title="Delete Master">
                                                    <iconify-icon icon="solar:trash-bin-trash-bold-duotone" class="align-middle"></iconify-icon>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5">
                                    <div class="py-4">
                                        <iconify-icon icon="solar:shop-bold-duotone" class="fs-48 text-muted opacity-50 mb-2"></iconify-icon>
                                        <h5 class="fw-semibold text-slate-700">No Regional Products Found</h5>
                                        <p class="text-muted small mb-3">Create your first Regional Product Master or adjust your search filters.</p>
                                        <a href="{{ route('adc.local-products.create') }}" class="btn btn-success btn-sm fw-semibold px-3 py-2">
                                            <iconify-icon icon="solar:add-circle-bold-duotone" class="align-middle me-1"></iconify-icon> Create Regional Product
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($products->hasPages())
                <div class="p-3 border-top">
                    {{ $products->links() }}
                </div>
            @endif
        </div>
    </div>

    <!-- Bulk Upload Modal -->
    <div class="modal fade" id="bulkUploadModal" tabindex="-1" aria-labelledby="bulkUploadModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                <div class="modal-header text-white px-4 py-3" style="background: linear-gradient(135deg, #4338ca 0%, #312e81 100%);">
                    <div class="d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:upload-track-bold-duotone" class="fs-24"></iconify-icon>
                        <h5 class="modal-title fw-bold text-white mb-0" id="bulkUploadModalLabel">Bulk Upload Regional Products</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('adc.local-products.bulk-upload') }}" method="POST" enctype="multipart/form-data" id="bulkUploadForm">
                    @csrf
                    <div class="modal-body p-4">
                        <!-- Top Action & Sample Download Box -->
                        <div class="alert alert-light border border-primary-subtle rounded-3 p-3 mb-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div>
                                <div class="fw-bold text-slate-800 fs-13 mb-0.5">Need the official Excel template?</div>
                                <div class="text-muted small">Download the structured Excel template (.xlsx) with built-in dropdowns for <strong>Category</strong>, <strong>Brand</strong>, <strong>Unit of Measure</strong>, and <strong>POS Status</strong>.</div>
                            </div>
                            <div>
                                <a href="{{ route('adc.local-products.sample-template') }}" class="btn btn-sm btn-success fw-semibold shadow-sm px-3 py-2 d-inline-flex align-items-center gap-1.5 rounded-pill">
                                    <iconify-icon icon="solar:file-download-bold-duotone" class="fs-18"></iconify-icon>
                                    Download Excel Template (.xlsx)
                                </a>
                            </div>
                        </div>

                        <!-- File Input Box -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-slate-700 fs-13">
                                Select Excel File (.xlsx) <span class="text-danger">*</span>
                            </label>
                            <input type="file" name="file" id="bulkProductFile" class="form-control form-control-lg fs-13" accept=".xlsx,.xls" required>
                            <div class="form-text text-muted fs-11 mt-1">
                                Supported formats: <strong>.xlsx, .xls</strong> only (Max file size: 10MB). Plain CSV is disabled.
                            </div>
                        </div>

                        <!-- Column Guidelines Reference Card -->
                        <div class="card border border-slate-200 bg-slate-50 rounded-3 mb-2">
                            <div class="card-header bg-transparent border-bottom py-2 px-3 d-flex align-items-center justify-content-between flex-wrap gap-1">
                                <span class="fw-bold fs-12 text-slate-700">
                                    <iconify-icon icon="solar:document-text-bold-duotone" class="align-middle me-1 text-primary"></iconify-icon>
                                    Unified Column Reference Guide (Product + Inventory Stock)
                                </span>
                                <span class="badge bg-primary-subtle text-primary fs-10 fw-bold">11 Required &bull; 5 Optional</span>
                            </div>
                            <div class="card-body p-0">
                                <div class="table-responsive" style="max-height: 250px; overflow-y: auto;">
                                    <table class="table table-sm table-bordered mb-0 fs-11 text-slate-700">
                                        <thead class="table-dark">
                                            <tr>
                                                <th style="width: 24%;">Column Name</th>
                                                <th style="width: 14%;">Requirement</th>
                                                <th>Description &amp; Dropdowns</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr class="table-light"><td colspan="3" class="fw-bold text-primary py-1"><iconify-icon icon="solar:box-bold-duotone" class="align-middle me-1"></iconify-icon> Section A: Regional Product Master</td></tr>
                                            <tr>
                                                <td class="fw-bold">Product Name</td>
                                                <td><span class="badge bg-danger-subtle text-danger">Required</span></td>
                                                <td>e.g. <em>Fresh Cow Milk 1L</em>, <em>Organic Atta 10Kg</em></td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Category</td>
                                                <td><span class="badge bg-danger-subtle text-danger">Required</span></td>
                                                <td><strong>Excel Dropdown:</strong> Select an active category from the system list.</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Barcode</td>
                                                <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                                <td>Optional barcode for POS scanner.</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Brand</td>
                                                <td><span class="badge bg-danger-subtle text-danger">Required</span></td>
                                                <td><strong>Excel Dropdown:</strong> Choose from dropdown or type brand name.</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Unit of Measure</td>
                                                <td><span class="badge bg-danger-subtle text-danger">Required</span></td>
                                                <td><strong>Excel Dropdown:</strong> Select standard unit (<em>Piece, Box, Kg, Litre, Gram</em>, etc.).</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">GST Rate (%)</td>
                                                <td><span class="badge bg-danger-subtle text-danger">Required</span></td>
                                                <td>GST tax percentage (e.g. <em>0, 5, 12, 18, 28</em>).</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">HSN Code</td>
                                                <td><span class="badge bg-danger-subtle text-danger">Required</span></td>
                                                <td>Standard HSN Code (e.g. <em>0401, 1101, 1514</em>).</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Local Product Code</td>
                                                <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                                <td>Leave blank to auto-generate (e.g. <strong>LCL000001</strong>).</td>
                                            </tr>

                                            <tr class="table-light"><td colspan="3" class="fw-bold text-success py-1"><iconify-icon icon="solar:layers-bold-duotone" class="align-middle me-1"></iconify-icon> Section B: Inventory Stock &amp; Pricing (Immediate Stock-In)</td></tr>
                                            <tr>
                                                <td class="fw-bold">Quantity</td>
                                                <td><span class="badge bg-danger-subtle text-danger">Required</span></td>
                                                <td>Opening stock quantity (&ge; 0). Stock is added directly to ADC Inventory!</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">MRP</td>
                                                <td><span class="badge bg-danger-subtle text-danger">Required</span></td>
                                                <td>Maximum Retail Price (&gt; 0, e.g. <em>66.00</em>).</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Purchase Price</td>
                                                <td><span class="badge bg-danger-subtle text-danger">Required</span></td>
                                                <td>Purchase / Inward Cost Price (&gt; 0, e.g. <em>58.00</em>).</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">CSP</td>
                                                <td><span class="badge bg-danger-subtle text-danger">Required</span></td>
                                                <td>Canteen Selling Price (&gt; 0, Purchase &le; CSP &le; MRP).</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Batch Number</td>
                                                <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                                <td>Batch identifier (auto-generated if left blank).</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Mfg Date</td>
                                                <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                                <td>Manufacturing date (YYYY-MM-DD or DD-MM-YYYY).</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">Expire Date</td>
                                                <td><span class="badge bg-secondary-subtle text-secondary">Optional</span></td>
                                                <td>Expiry date (YYYY-MM-DD or DD-MM-YYYY).</td>
                                            </tr>
                                            <tr>
                                                <td class="fw-bold">POS Status</td>
                                                <td><span class="badge bg-danger-subtle text-danger">Required</span></td>
                                                <td><strong>Excel Dropdown:</strong> Choose <em>Active</em> or <em>Inactive</em> for POS billing.</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer bg-light px-4 py-2.5 d-flex justify-content-between">
                        <button type="button" class="btn btn-outline-secondary btn-sm fw-semibold" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm fw-bold px-4 shadow-sm" id="btnSubmitBulkUpload">
                            <iconify-icon icon="solar:upload-track-bold-duotone" class="align-middle me-1 fs-16" id="btnSubmitIcon"></iconify-icon>
                            <span id="btnSubmitText">Upload &amp; Process Products</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var form = document.getElementById('bulkUploadForm');
            if (form) {
                form.addEventListener('submit', function() {
                    var btn = document.getElementById('btnSubmitBulkUpload');
                    var icon = document.getElementById('btnSubmitIcon');
                    var text = document.getElementById('btnSubmitText');
                    if (btn) {
                        btn.disabled = true;
                        if (icon) icon.setAttribute('icon', 'solar:refresh-circle-bold-duotone');
                        if (text) text.innerText = 'Processing Import...';
                    }
                });
            }

            if (window.location.search.includes('open_bulk_upload=1')) {
                var modalEl = document.getElementById('bulkUploadModal');
                if (modalEl && typeof bootstrap !== 'undefined') {
                    var myModal = new bootstrap.Modal(modalEl);
                    myModal.show();
                }
            }
        });
    </script>
    @endpush
@endsection
