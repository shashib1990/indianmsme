@extends('layouts.vendor')

@section('title', 'Submit Booking Payment')

@section('content')
    <!-- Header Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                Submit Payment for Booking #{{ $booking->booking_no }}
                            </h4>
                            <p class="text-muted small mb-0">Pay the booking total amount using Admin bank details or QR code below, then upload payment proof (UTR / Slip).</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <a href="{{ route('vendor.rack-bookings.marketplace') }}" class="btn btn-outline-secondary btn-sm fw-semibold">
                                <iconify-icon icon="solar:arrow-left-bold-duotone" class="align-middle me-1"></iconify-icon> Back to Marketplace
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3 justify-content-center">
        <!-- 1. Booking Summary Box -->
        <div class="col-lg-5">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold text-dark mb-0" style="font-family:'Outfit',sans-serif;">
                        <iconify-icon icon="solar:document-text-bold-duotone" class="text-primary me-1 align-middle"></iconify-icon>
                        Booking Order Summary
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="p-3 mb-3 rounded-3 bg-light border">
                        <div class="d-flex justify-content-between mb-2 fs-13">
                            <span class="text-muted">Target Depot:</span>
                            <strong class="text-primary">{{ $booking->owner->company_name ?? $booking->owner->name ?? 'Depot' }} ({{ strtoupper($booking->owner_type) }})</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2 fs-13">
                            <span class="text-muted">Depot Code:</span>
                            <code class="fw-bold fs-13">{{ $booking->depotRack->depot_code ?? 'N/A' }}</code>
                        </div>
                        <div class="d-flex justify-content-between mb-2 fs-13">
                            <span class="text-muted">Unit Type:</span>
                            <span class="badge {{ $booking->unit_type === 'RACK' ? 'bg-primary-subtle text-primary' : 'bg-success-subtle text-success' }} fs-11 px-2.5 py-0.5 font-bold">{{ $booking->unit_type }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 fs-13">
                            <span class="text-muted">Quantity:</span>
                            <strong class="text-dark">{{ $booking->quantity }} Unit(s)</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2 fs-13">
                            <span class="text-muted">Duration:</span>
                            <strong class="text-dark">{{ $booking->duration_months }} Month(s)</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2 fs-13">
                            <span class="text-muted">Monthly Rate:</span>
                            <strong class="text-dark">₹{{ number_format($booking->monthly_rate, 2) }}/mo</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-0 fs-13">
                            <span class="text-muted">Start Date:</span>
                            <strong class="text-dark">{{ $booking->start_date ? $booking->start_date->format('d M Y') : date('d M Y') }}</strong>
                        </div>
                    </div>

                    <div class="p-3 rounded-3 bg-success-subtle border border-success-subtle text-center">
                        <div class="fs-12 text-success font-semibold text-uppercase">Total Payable Amount</div>
                        <div class="fs-24 fw-bold text-success mt-1">₹{{ number_format($booking->total_amount, 2) }}</div>
                        <div class="fs-11 text-muted mt-1">Payment goes directly to Central Admin Account</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- 2. Admin Bank & QR Details + Form Card -->
        <div class="col-lg-7">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold text-dark mb-0" style="font-family:'Outfit',sans-serif;">
                        <iconify-icon icon="solar:card-bold-duotone" class="text-success me-1 align-middle"></iconify-icon>
                        Official Payment Gateway Details
                    </h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-3 mb-4 p-3 rounded-3 bg-light border">
                        <div class="col-md-6 border-end">
                            <h6 class="fw-bold text-dark fs-13 mb-2">Bank Transfer Details:</h6>
                            <div class="fs-12 mb-1"><strong>Bank:</strong> {{ $bankName }}</div>
                            <div class="fs-12 mb-1"><strong>A/c Name:</strong> {{ $accountName }}</div>
                            <div class="fs-12 mb-1"><strong>A/c No:</strong> <code class="fw-bold text-primary">{{ $accountNumber }}</code></div>
                            <div class="fs-12 mb-1"><strong>IFSC:</strong> <code class="fw-bold text-primary">{{ $ifscCode }}</code></div>
                            @if($upiId)
                                <div class="fs-12 mb-0"><strong>UPI ID:</strong> <code class="fw-bold text-success">{{ $upiId }}</code></div>
                            @endif
                        </div>
                        <div class="col-md-6 text-center d-flex flex-column align-items-center justify-content-center">
                            <h6 class="fw-bold text-dark fs-13 mb-2">Scan & Pay QR:</h6>
                            @if($qrCode)
                                <img src="{{ $qrCode }}" style="max-height: 120px;" class="rounded border p-1 bg-white shadow-sm mb-1" alt="QR Code">
                            @else
                                <div class="fs-12 text-muted py-3">QR Code Image Not Available</div>
                            @endif
                        </div>
                    </div>

                    <!-- Payment Proof Form -->
                    <form action="{{ route('vendor.rack-bookings.submit-payment', $booking->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fs-13 fw-semibold">UTR / Transaction Ref No <span class="text-danger">*</span></label>
                            <input type="text" name="payment_utr" class="form-control fw-bold" placeholder="e.g. UTR1234567890" value="{{ old('payment_utr', $booking->payment_utr) }}" required>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fs-13 fw-semibold">Upload Payment Slip / Screenshot Proof</label>
                            <input type="file" name="payment_slip" class="form-control" accept="image/*">
                            <div class="fs-11 text-muted mt-1">Upload a clear photo of the bank transfer receipt or UPI success screen.</div>
                        </div>

                        <div class="d-flex align-items-center justify-content-end gap-2 pt-3 border-top">
                            <a href="{{ route('vendor.rack-bookings.marketplace') }}" class="btn btn-light btn-sm px-3">Cancel</a>
                            <button type="submit" class="btn btn-success btn-sm px-4 fw-semibold d-inline-flex align-items-center gap-1">
                                <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-18"></iconify-icon> Submit Payment for Verification
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
