@extends('layouts.adc')

@section('title', 'Manage Rack & Shelf')
@section('page-title', 'Manage Rack & Shelf')

@section('content')
    <!-- Header Card with Top Action Buttons -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                Manage Rack & Shelf Space
                            </h4>
                            <p class="text-muted small mb-0">Add Racks and Shelves to your ADC store and upload store photos for Seller bookings.</p>
                        </div>
                        <div class="col-md-6 text-md-end d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
                            <button type="button" class="btn btn-info text-white btn-sm fw-semibold d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#uploadRackImagesModal" style="background-color: #00838f; border-color: #00838f;">
                                <iconify-icon icon="solar:gallery-bold-duotone" class="fs-18"></iconify-icon> Add Rack Images
                            </button>
                            <button type="button" class="btn btn-success btn-sm fw-semibold d-inline-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#addRackModal">
                                <iconify-icon icon="solar:add-circle-bold-duotone" class="fs-18"></iconify-icon> + Add Rack Availability
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Manage Rack List Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <h5 class="fw-bold text-dark mb-0" style="font-family:'Outfit',sans-serif;">
                <iconify-icon icon="solar:widget-bold-duotone" class="text-primary align-middle me-1"></iconify-icon>
                ADC Rack / Shelf Availability List
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light fs-11 text-muted text-uppercase">
                        <tr>
                            <th class="ps-4" style="width: 14%;">ADC Code</th>
                            <th style="width: 14%;">Unit Type</th>
                            <th class="text-center" style="width: 12%;">Total Units</th>
                            <th class="text-center" style="width: 12%;">Available</th>
                            <th class="text-center" style="width: 12%;">Allotted</th>
                            <th class="text-end" style="width: 15%;">ADC Share / Unit</th>
                            <th class="text-end" style="width: 15%;">Total ADC Share</th>
                            <th class="text-center" style="width: 12%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($racks as $item)
                            @php
                                $adcUnitShare = $item->amount_per_unit * 0.50;
                                $adcTotalShare = $item->total_units * $adcUnitShare;
                            @endphp
                            <tr>
                                <td class="ps-4">
                                    <code class="fw-bold fs-13 text-primary">{{ $item->depot_code }}</code>
                                    <div class="fs-11 text-muted">{{ $item->created_at->format('d M Y') }}</div>
                                </td>
                                <td>
                                    <span class="badge {{ $item->unit_type === 'RACK' ? 'bg-primary-subtle text-primary' : 'bg-success-subtle text-success' }} fs-11 px-2.5 py-1 fw-bold">
                                        {{ $item->unit_type }}
                                    </span>
                                </td>
                                <td class="text-center fw-bold fs-13 text-dark">{{ $item->total_units }}</td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success fs-12 px-2.5 py-1">{{ $item->available_units }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-warning-subtle text-warning fs-12 px-2.5 py-1">{{ $item->allotted_units }}</span>
                                </td>
                                <td class="text-end fw-semibold text-dark fs-13">
                                    ₹{{ number_format($adcUnitShare, 2) }}
                                </td>
                                <td class="text-end fw-bold text-success fs-13">
                                    ₹{{ number_format($adcTotalShare, 2) }}
                                </td>
                                <td class="text-center">
                                    <form action="{{ route('adc.racks.toggle-status', $item->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @if($item->status === 'active')
                                            <button type="submit" class="btn btn-xs btn-success rounded-pill px-2.5 py-1 fs-11" title="Click to set Inactive">Active</button>
                                        @else
                                            <button type="submit" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 fs-11" title="Click to set Active">Inactive</button>
                                        @endif
                                    </form>
                                    @if($item->allotted_units > 0)
                                        <div class="fs-10 text-muted mt-0.5">Cannot deactivate (Allotted)</div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <iconify-icon icon="solar:widget-bold-duotone" class="fs-48 text-muted mb-2"></iconify-icon>
                                    <h5 class="fw-bold text-dark mb-1">No Racks or Shelves Added Yet</h5>
                                    <p class="text-muted fs-13 mb-3">Add your ADC Racks & Shelves to start receiving Seller booking requests.</p>
                                    <button type="button" class="btn btn-success btn-sm fw-semibold px-4" data-bs-toggle="modal" data-bs-target="#addRackModal">
                                        <iconify-icon icon="solar:add-circle-bold-duotone" class="align-middle me-1"></iconify-icon> + Add Rack Availability
                                    </button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Depot Rack Images Gallery Card (Matching Reference Screenshot) -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
            <h5 class="fw-bold text-dark mb-0" style="font-family:'Outfit',sans-serif;">
                <iconify-icon icon="solar:gallery-bold-duotone" class="text-info align-middle me-1"></iconify-icon>
                Rack Images Gallery
            </h5>
            <button type="button" class="btn btn-info text-white btn-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#uploadRackImagesModal" style="background-color: #00838f; border-color: #00838f;">
                Upload / Update Store Photos
            </button>
        </div>
        <div class="card-body p-4">
            <div class="row g-3">
                @forelse($depotImagesList as $img)
                    <div class="col-md-3 col-sm-6">
                        <div class="card border shadow-sm h-100">
                            <a href="{{ $img->image_url }}" target="_blank">
                                <img src="{{ $img->image_url }}" style="height: 160px; object-fit: cover; width: 100%;" class="card-img-top" alt="Store Photo">
                            </a>
                            <div class="card-body p-2 text-center fs-12">
                                <div class="fw-bold text-dark text-truncate" title="{{ $img->image_label }}">{{ $img->image_label }}</div>
                                <div class="mt-1">
                                    @if($img->approval_status === 'approved')
                                        <span class="badge bg-success text-white fs-10 px-2 py-0.5">APPROVED</span>
                                    @elseif($img->approval_status === 'rejected')
                                        <span class="badge bg-danger text-white fs-10 px-2 py-0.5" title="{{ $img->rejection_reason }}">REJECTED</span>
                                    @else
                                        <span class="badge bg-warning text-dark fs-10 px-2 py-0.5">PENDING APPROVAL</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-4 text-muted fs-13">
                        <iconify-icon icon="solar:gallery-bold-duotone" class="fs-36 text-muted mb-1"></iconify-icon>
                        <div>No store photos uploaded for this ADC store yet.</div>
                        <button type="button" class="btn btn-outline-info btn-xs mt-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#uploadRackImagesModal">
                            Upload ADC Store Photos Now
                        </button>
                    </div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Allotted Seller Bookings Card -->
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-header bg-white border-bottom py-3 px-4">
            <h5 class="fw-bold text-dark mb-0" style="font-family:'Outfit',sans-serif;">
                <iconify-icon icon="solar:user-check-bold-duotone" class="text-success align-middle me-1"></iconify-icon>
                Seller Allotment Details & ADC Revenue Share
            </h5>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light fs-11 text-muted text-uppercase">
                        <tr>
                            <th class="ps-4" style="width: 14%;">Booking No</th>
                            <th style="width: 20%;">Seller Details</th>
                            <th style="width: 12%;">Type</th>
                            <th class="text-center" style="width: 10%;">Units</th>
                            <th style="width: 14%;">Start / Expiry Date</th>
                            <th class="text-end" style="width: 15%;">ADC Share (50%)</th>
                            <th class="text-center" style="width: 15%;">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allotments as $alt)
                            <tr>
                                <td class="ps-4">
                                    <div class="fw-bold text-dark fs-13">{{ $alt->booking_no }}</div>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark fs-13">{{ $alt->seller->company_name ?? $alt->seller->name ?? 'N/A' }}</div>
                                    <div class="text-muted fs-11">Seller Code: <code class="fw-bold text-primary">{{ $alt->seller->sellerProfile->seller_id ?? ('SLR' . str_pad($alt->seller_id, 4, '0', STR_PAD_LEFT)) }}</code></div>
                                </td>
                                <td>
                                    <span class="badge {{ $alt->unit_type === 'RACK' ? 'bg-primary-subtle text-primary' : 'bg-success-subtle text-success' }} fs-11 px-2.5 py-1 fw-bold">
                                        {{ $alt->unit_type }}
                                    </span>
                                </td>
                                <td class="text-center fw-bold fs-13 text-dark">{{ $alt->quantity }}</td>
                                <td>
                                    <div class="fs-12 text-dark">{{ $alt->start_date ? $alt->start_date->format('d M Y') : 'N/A' }}</div>
                                    <div class="fs-11 text-muted">Expires: {{ $alt->expiry_date ? $alt->expiry_date->format('d M Y') : 'N/A' }} ({{ $alt->duration_months }} mo)</div>
                                </td>
                                <td class="text-end fw-bold text-success fs-14">
                                    ₹{{ number_format($alt->depot_share > 0 ? $alt->depot_share : ($alt->total_amount * 0.50), 2) }}
                                </td>
                                <td class="text-center">
                                    @if($alt->status === 'Approved')
                                        <span class="badge bg-success-subtle text-success fs-11 px-2.5 py-1 border border-success-subtle">ALLOTTED</span>
                                    @elseif($alt->status === 'Expired')
                                        <span class="badge bg-secondary-subtle text-secondary fs-11 px-2.5 py-1 border border-secondary-subtle">EXPIRED</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning fs-11 px-2.5 py-1 border border-warning-subtle">{{ $alt->status }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    No seller allotments active yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 1. Single Upload Rack Images Modal for Entire Depot (Exact Replica of Reference Screenshot) -->
    <div class="modal fade" id="uploadRackImagesModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('adc.racks.upload-images') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-header text-white" style="background-color: #2b3b4e;">
                        <h5 class="modal-title fw-bold text-white fs-16">Upload Rack Images</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>

                    <div class="modal-body p-4 text-start">
                        <div class="row g-3">
                            @foreach($predefinedSlots as $slotKey => $slotLabel)
                                @php
                                    $existingImg = $depotImages->get($slotKey);
                                @endphp
                                <div class="col-md-6">
                                    <label class="form-label fs-12 fw-semibold mb-1 text-dark">{{ $slotLabel }}</label>
                                    <input type="file" name="images[{{ $slotKey }}]" class="form-control form-control-sm" accept="image/*">
                                    <div class="mt-1 fs-11 text-muted d-flex align-items-center justify-content-between">
                                        <span>Current:</span>
                                        @if($existingImg)
                                            <a href="{{ $existingImg->image_url }}" target="_blank" class="text-primary fw-semibold me-1">View Image</a>
                                            @if($existingImg->approval_status === 'approved')
                                                <span class="badge bg-success text-white fs-10 px-1.5 py-0.5">APPROVED</span>
                                            @elseif($existingImg->approval_status === 'rejected')
                                                <span class="badge bg-danger text-white fs-10 px-1.5 py-0.5">REJECTED</span>
                                            @else
                                                <span class="badge bg-warning text-dark fs-10 px-1.5 py-0.5">PENDING</span>
                                            @endif
                                        @else
                                            <span class="text-muted italic">No Image</span>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                        </div>

                        <div class="alert alert-info border-0 mt-4 mb-0 py-2.5 px-3 fs-12 rounded-3" style="background-color: #e0f2fe; color: #0369a1;">
                            <iconify-icon icon="solar:info-circle-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon>
                            Only upload new images if you want to replace existing ones. Supported formats: JPG, PNG, WEBP. Max size: 1MB per image.
                        </div>
                    </div>

                    <div class="modal-footer bg-light py-2 px-3">
                        <button type="button" class="btn btn-secondary btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success btn-sm px-4 fw-semibold" style="background-color: #388e3c; border-color: #388e3c;">Upload Images</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- 2. Add Rack Modal -->
    <div class="modal fade" id="addRackModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <form action="{{ route('adc.racks.store') }}" method="POST" id="addRackForm">
                    @csrf
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title fw-bold text-white">Add Rack / Shelf Availability</h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body p-4 text-start">
                        <!-- Select Type -->
                        <div class="mb-3">
                            <label class="form-label fw-bold fs-13">Select Type <span class="text-danger">*</span></label>
                            <select name="unit_type" id="unit_type_select" class="form-select fw-semibold" required>
                                <option value="RACK">Rack</option>
                                <option value="SHELF">Shelf</option>
                            </select>
                        </div>

                        <!-- Number of Units -->
                        <div class="mb-3">
                            <label class="form-label fw-bold fs-13">Number of Units <span class="text-danger">*</span></label>
                            <input type="number" name="total_units" id="total_units_input" class="form-control fw-bold" placeholder="e.g. 10" required min="1" value="10">
                        </div>

                        <!-- Description -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-13">Description / Location Notes</label>
                            <textarea name="description" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">Confirm & Add</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
