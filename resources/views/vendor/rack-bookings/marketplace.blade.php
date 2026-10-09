@extends('layouts.vendor')

@section('title', 'Rack & Shelf Marketplace')

@section('content')
    <!-- Header Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                Depot Rack & Shelf Marketplace
                            </h4>
                            <p class="text-muted small mb-0">Browse and book available Racks & Shelves across all DDC & ADC
                                Depots in India for inventory storage.</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <a href="{{ route('vendor.rack-bookings.index') }}"
                                class="btn btn-outline-secondary btn-sm fw-semibold">
                                <iconify-icon icon="solar:history-bold-duotone" class="align-middle me-1"></iconify-icon> My
                                Bookings History
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Joined Owner Type Filter Tabs -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body py-2.5 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="btn-group shadow-sm" role="group">
                        <a href="{{ route('vendor.rack-bookings.marketplace', ['owner_type' => 'all', 'unit_type' => $unitTypeFilter, 'search' => $search]) }}" 
                           class="btn btn-sm fw-bold {{ $ownerTypeFilter === 'all' ? 'btn-primary' : 'btn-outline-secondary bg-white text-dark' }}">
                            <iconify-icon icon="solar:widget-bold-duotone" class="align-middle me-1"></iconify-icon>
                            All Racks <span class="badge {{ $ownerTypeFilter === 'all' ? 'bg-white text-primary' : 'bg-secondary text-white' }} ms-1 fs-11">{{ $allCount }}</span>
                        </a>
                        <a href="{{ route('vendor.rack-bookings.marketplace', ['owner_type' => 'ddc', 'unit_type' => $unitTypeFilter, 'search' => $search]) }}" 
                           class="btn btn-sm fw-bold {{ $ownerTypeFilter === 'ddc' ? 'text-white' : 'btn-outline-secondary bg-white text-dark' }}"
                           style="{{ $ownerTypeFilter === 'ddc' ? 'background-color:#00838f !important; border-color:#00838f !important;' : '' }}">
                            <iconify-icon icon="solar:buildings-bold-duotone" class="align-middle me-1"></iconify-icon>
                            DDC Racks <span class="badge {{ $ownerTypeFilter === 'ddc' ? 'bg-white text-dark' : 'bg-secondary text-white' }} ms-1 fs-11">{{ $ddcCount }}</span>
                        </a>
                        <a href="{{ route('vendor.rack-bookings.marketplace', ['owner_type' => 'adc', 'unit_type' => $unitTypeFilter, 'search' => $search]) }}" 
                           class="btn btn-sm fw-bold {{ $ownerTypeFilter === 'adc' ? 'btn-warning text-dark' : 'btn-outline-secondary bg-white text-dark' }}">
                            <iconify-icon icon="solar:shop-bold-duotone" class="align-middle me-1"></iconify-icon>
                            ADC Racks <span class="badge {{ $ownerTypeFilter === 'adc' ? 'bg-dark text-white' : 'bg-secondary text-white' }} ms-1 fs-11">{{ $adcCount }}</span>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('vendor.rack-bookings.marketplace') }}" class="row g-2 align-items-center">
                <input type="hidden" name="owner_type" value="{{ $ownerTypeFilter }}">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white"><iconify-icon icon="solar:magnifer-bold-duotone"
                                class="text-muted"></iconify-icon></span>
                        <input type="text" name="search" class="form-control"
                            placeholder="Search by code, district, or owner..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="unit_type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" {{ $unitTypeFilter === 'all' ? 'selected' : '' }}>All Unit Types (Rack & Shelf)
                        </option>
                        <option value="RACK" {{ $unitTypeFilter === 'RACK' ? 'selected' : '' }}>Rack Only</option>
                        <option value="SHELF" {{ $unitTypeFilter === 'SHELF' ? 'selected' : '' }}>Shelf Only</option>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-1">
                    <button type="submit" class="btn btn-theme btn-sm px-3 fw-semibold">Search / Filter</button>
                    @if($search || $ownerTypeFilter !== 'all' || $unitTypeFilter !== 'all')
                        <a href="{{ route('vendor.rack-bookings.marketplace') }}" class="btn btn-light btn-sm">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Marketplace Table (Matching Screenshot 1) -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="bg-dark text-white fs-11 text-uppercase">
                        <tr>
                            <th class="ps-3" style="width: 5%;">SN</th>
                            <th style="width: 10%;">Code</th>
                            <th style="width: 15%;">Image</th>
                            <th style="width: 18%;">District</th>
                            <th style="width: 10%;">Status</th>
                            <th style="width: 10%;">Unit Type</th>
                            <th style="width: 8%;">Unit</th>
                            <th style="width: 12%;">Price/Month</th>
                            <th style="width: 10%;">Available Units</th>
                            <th style="width: 12%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($racks as $index => $item)
                            <tr>
                                <td class="ps-3 fw-bold fs-13 text-muted">{{ $racks->firstItem() + $index }}</td>
                                <td>
                                    <span
                                        class="badge bg-info-subtle text-info border border-info-subtle fs-12 px-2.5 py-1 font-monospace fw-bold">
                                        {{ $item->depot_code }}
                                    </span>
                                </td>
                                <td>
                                    <button type="button"
                                        class="btn btn-xs btn-success fw-semibold px-2.5 py-1 view-rack-img-btn"
                                        data-rack-id="{{ $item->id }}">
                                        <iconify-icon icon="solar:eye-bold-duotone" class="align-middle me-1"></iconify-icon>
                                        View Rack Image
                                    </button>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark fs-13">
                                        <iconify-icon icon="solar:map-point-bold-duotone"
                                            class="text-danger me-1 align-middle"></iconify-icon>
                                        {{ $item->owner->district ?? $item->owner->state ?? 'Depot Location' }}
                                    </div>
                                    <div class="fs-11 text-muted">{{ $item->owner->company_name ?? $item->owner->name ?? '' }}
                                        ({{ strtoupper($item->owner_type) }})</div>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-success text-white fs-11 px-2.5 py-1 uppercase fw-bold">AVAILABLE</span>
                                </td>
                                <td>
                                    <span
                                        class="badge {{ $item->unit_type === 'RACK' ? 'bg-primary-subtle text-primary border border-primary-subtle' : 'bg-success-subtle text-success border border-success-subtle' }} fs-11 px-2 py-1 fw-bold">
                                        {{ $item->unit_type }}
                                    </span>
                                </td>
                                <td class="fw-bold text-dark fs-13">{{ $item->total_units }}</td>
                                <td>
                                    <div class="fw-bold text-success fs-14">₹{{ number_format($item->amount_per_unit, 2) }}
                                    </div>
                                </td>
                                <td>
                                    <span
                                        class="badge bg-light text-dark border fs-13 px-2.5 py-1 fw-bold">{{ $item->available_units }}</span>
                                </td>
                                <td>
                                    <button type="button"
                                        class="btn btn-xs btn-success px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1 book-now-btn"
                                        data-rack-id="{{ $item->id }}" data-depot-code="{{ $item->depot_code }}"
                                        data-unit-type="{{ $item->unit_type }}"
                                        data-district="{{ $item->owner->district ?? $item->owner->state ?? 'Location' }}"
                                        data-available-units="{{ $item->available_units }}"
                                        data-monthly-charge="{{ $item->amount_per_unit }}">
                                        <iconify-icon icon="solar:calendar-add-bold-duotone" class="fs-16"></iconify-icon> Book
                                        Now
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <iconify-icon icon="solar:widget-bold-duotone" class="fs-48 text-muted mb-2"></iconify-icon>
                                    <h5 class="fw-bold text-dark mb-1">No Available Racks or Shelves</h5>
                                    <p class="text-muted fs-13 mb-0">No active depot racks match your search filter.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($racks->hasPages())
        <div class="mt-3">
            {{ $racks->links() }}
        </div>
    @endif

    <!-- 1. Book Rack Modal Popup (Matching Screenshot 2) -->
    <div class="modal fade" id="bookRackModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-md">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('vendor.rack-bookings.store') }}" method="POST" id="bookRackForm">
                    @csrf
                    <input type="hidden" name="depot_rack_id" id="modal_depot_rack_id">

                    <div class="modal-header bg-success text-white py-2.5 px-3">
                        <h5 class="modal-title fw-bold text-white fs-15" id="modal_header_title">Book Rack DDB036</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                            aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4 text-start">
                        <!-- Rack Details Header Box -->
                        <div class="p-3 mb-3 rounded-3 bg-cyan-subtle border border-cyan-subtle"
                            style="background-color: #e0f2fe;">
                            <h6 class="fw-bold text-primary mb-2 fs-13">Rack Details</h6>
                            <div class="row g-2 fs-12 text-dark">
                                <div class="col-6">District: <strong id="modal_district_text">Nagaur</strong></div>
                                <div class="col-6">Unit Type: <strong id="modal_unit_type_text">RACK</strong></div>
                                <div class="col-6">Available Units: <strong id="modal_available_units_text">50</strong>
                                </div>
                                <div class="col-6">Monthly Charge: <strong id="modal_monthly_charge_text">₹4000.00</strong>
                                </div>
                            </div>
                        </div>

                        <!-- Form Inputs -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label fs-12 fw-semibold mb-1">Start Date <span
                                        class="text-danger">*</span></label>
                                <input type="date" name="start_date" id="modal_start_date"
                                    class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fs-12 fw-semibold mb-1">Duration <span
                                        class="text-danger">*</span></label>
                                <select name="duration_months" id="modal_duration_select"
                                    class="form-select form-select-sm fw-bold" required>
                                    <option value="3" selected>3 Months</option>
                                    <option value="6">6 Months</option>
                                    <option value="12">12 Months</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fs-12 fw-semibold mb-1">Qty <span
                                        class="text-danger">*</span></label>
                                <input type="number" name="quantity" id="modal_quantity_input"
                                    class="form-control form-control-sm fw-bold" value="1" min="1" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fs-12 fw-semibold mb-1">Remarks / Storage Notes (Optional)</label>
                            <textarea name="seller_remarks" class="form-control form-control-sm" rows="2"
                                placeholder="Add optional remarks for booking..."></textarea>
                        </div>

                        <!-- Booking Summary Box -->
                        <div class="p-3 rounded-3 bg-light border">
                            <div class="fw-bold text-dark fs-13 mb-1">Booking Summary</div>
                            <div class="d-flex align-items-center justify-content-between fs-12 text-muted mb-2">
                                <span>Monthly Charge: <strong id="modal_summary_rate">₹4000.00</strong></span>
                                <span>Duration: <strong id="modal_summary_duration">3 month(s)</strong></span>
                            </div>
                            <div class="pt-2 border-top d-flex align-items-center justify-content-between">
                                <span class="fw-bold text-dark fs-14">Total Amount:</span>
                                <span class="fw-bold text-success fs-20" id="modal_summary_total">₹12000</span>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer bg-light py-2 px-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3"
                            data-bs-dismiss="modal">Cancel</button>
                        <button type="submit"
                            class="btn btn-success btn-sm px-4 fw-semibold d-inline-flex align-items-center gap-1">
                            <iconify-icon icon="solar:card-bold-duotone" class="fs-16"></iconify-icon> Proceed to Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 2. View Rack Images Modal Lightbox (Extra-Wide Modal - 4 Photos Per Row) -->
    <div class="modal fade" id="rackImagesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white py-2.5 px-3">
                    <h5 class="modal-title fw-bold text-white fs-15" id="images_modal_title">Rack Store Images</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 text-start">
                    <div class="row g-3" id="images_gallery_container">
                        <div class="col-12 text-center py-4">
                            <div class="spinner-border text-primary" role="status"></div>
                            <div class="fs-13 text-muted mt-2">Loading depot photos...</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            let currentMonthlyRate = 4000;

            const bookRackModal = new bootstrap.Modal(document.getElementById('bookRackModal'));
            const rackImagesModal = new bootstrap.Modal(document.getElementById('rackImagesModal'));

            // Modal elements
            const modalRackId = document.getElementById('modal_depot_rack_id');
            const modalHeaderTitle = document.getElementById('modal_header_title');
            const modalDistrictText = document.getElementById('modal_district_text');
            const modalUnitTypeText = document.getElementById('modal_unit_type_text');
            const modalAvailableUnitsText = document.getElementById('modal_available_units_text');
            const modalMonthlyChargeText = document.getElementById('modal_monthly_charge_text');
            const modalDurationSelect = document.getElementById('modal_duration_select');
            const modalQuantityInput = document.getElementById('modal_quantity_input');
            const modalSummaryRate = document.getElementById('modal_summary_rate');
            const modalSummaryDuration = document.getElementById('modal_summary_duration');
            const modalSummaryTotal = document.getElementById('modal_summary_total');

            function calculateBookingTotal() {
                const qty = parseInt(modalQuantityInput.value) || 1;
                const duration = parseInt(modalDurationSelect.value) || 3;
                const total = qty * currentMonthlyRate * duration;

                modalSummaryRate.textContent = '₹' + currentMonthlyRate.toFixed(2);
                modalSummaryDuration.textContent = duration + ' month(s)';
                modalSummaryTotal.textContent = '₹' + total.toFixed(2);
            }

            // Book Now button click
            document.querySelectorAll('.book-now-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const rackId = this.getAttribute('data-rack-id');
                    const depotCode = this.getAttribute('data-depot-code');
                    const unitType = this.getAttribute('data-unit-type');
                    const district = this.getAttribute('data-district');
                    const availableUnits = this.getAttribute('data-available-units');
                    const monthlyCharge = parseFloat(this.getAttribute('data-monthly-charge'));

                    currentMonthlyRate = monthlyCharge;

                    modalRackId.value = rackId;
                    modalHeaderTitle.textContent = `Book ${unitType} ${depotCode}`;
                    modalDistrictText.textContent = district;
                    modalUnitTypeText.textContent = unitType;
                    modalAvailableUnitsText.textContent = availableUnits;
                    modalMonthlyChargeText.textContent = `₹${monthlyCharge.toFixed(2)}`;

                    modalQuantityInput.max = availableUnits;
                    modalQuantityInput.value = 1;
                    modalDurationSelect.value = 3;

                    calculateBookingTotal();
                    bookRackModal.show();
                });
            });

            modalDurationSelect.addEventListener('change', calculateBookingTotal);
            modalQuantityInput.addEventListener('input', calculateBookingTotal);

            // View Rack Image button click
            document.querySelectorAll('.view-rack-img-btn').forEach(btn => {
                btn.addEventListener('click', function () {
                    const rackId = this.getAttribute('data-rack-id');
                    const container = document.getElementById('images_gallery_container');
                    const title = document.getElementById('images_modal_title');

                    container.innerHTML = '<div class="col-12 text-center py-4"><div class="spinner-border text-primary"></div><div class="fs-13 text-muted mt-2">Loading depot photos...</div></div>';
                    rackImagesModal.show();

                    const imgApiRouteTemplate = "{{ route('vendor.rack-bookings.images', '__ID__') }}";
                    const fetchUrl = imgApiRouteTemplate.replace('__ID__', rackId);

                    fetch(fetchUrl)
                        .then(res => res.json())
                        .then(data => {
                            if (data.success && data.images.length > 0) {
                                title.textContent = `Store Photos - ${data.rack.depot_code} (${data.rack.district})`;
                                let html = '';
                                data.images.forEach(img => {
                                    const badge = (img.approval_status === 'approved')
                                        ? '<span class="badge bg-success text-white fs-10 mt-1">APPROVED PHOTO</span>'
                                        : '<span class="badge bg-warning text-dark fs-10 mt-1">PENDING APPROVAL</span>';

                                    html += `
                                    <div class="col-md-3 col-sm-6 mb-3">
                                        <div class="card border shadow-sm h-100">
                                            <a href="${img.image_url}" target="_blank">
                                                <img src="${img.image_url}" style="height: 150px; object-fit: cover; width: 100%;" class="card-img-top" alt="Rack Photo">
                                            </a>
                                            <div class="card-body p-2 text-center fs-12">
                                                <div class="fw-bold text-dark text-truncate" title="${img.image_label || 'Depot Store Photo'}">${img.image_label || 'Depot Store Photo'}</div>
                                                ${badge}
                                            </div>
                                        </div>
                                    </div>
                                `;
                                });
                                container.innerHTML = html;
                            } else {
                                container.innerHTML = '<div class="col-12 text-center py-4 text-muted fs-13">No store photos uploaded for this depot yet.</div>';
                            }
                        })
                        .catch(err => {
                            console.error(err);
                            container.innerHTML = '<div class="col-12 text-center py-4 text-danger fs-13">Failed to load images.</div>';
                        });
                });
            });
        });
    </script>
@endpush