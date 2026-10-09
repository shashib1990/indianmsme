@extends('layouts.admin')

@section('title', 'Manage Rack & Shelf Settings')
@section('page-title', 'Manage Rack & Shelf Settings')

@section('content')
    <!-- Header Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-8">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                Depot Rack & Shelf Pricing Configuration
                            </h4>
                            <p class="text-muted small mb-0">Configure default monthly pricing rates for DDC & ADC Racks & Shelves. Rates will be automatically fetched by DDC & ADC panels.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row justify-content-center">
        <!-- Depot Rack & Shelf Pricing Configuration Card -->
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="fw-bold text-dark mb-0" style="font-family:'Outfit',sans-serif;">
                        <iconify-icon icon="solar:tag-price-bold-duotone" class="text-primary align-middle me-1"></iconify-icon>
                        Default Rate Configuration
                    </h5>
                </div>
                <div class="card-body p-4">
                    <p class="text-muted fs-13 mb-4">DDC and ADC panels automatically fetch these configured monthly rates when adding Racks & Shelves. Prices cannot be entered or changed manually by DDCs or ADCs.</p>

                    <form action="{{ route('admin.racks.settings.pricing') }}" method="POST">
                        @csrf

                        <div class="mb-4">
                            <h6 class="fw-bold text-primary mb-3 pb-2 border-bottom">
                                <iconify-icon icon="solar:buildings-bold-duotone" class="align-middle me-1"></iconify-icon>
                                DDC Depot Rates
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold fs-13">DDC Rack Monthly Rate (₹) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold">₹</span>
                                        <input type="number" step="0.01" name="ddc_rack_amount" class="form-control fw-bold fs-15" value="{{ $ddcRackAmount }}" required>
                                    </div>
                                    <div class="fs-11 text-muted mt-1">Default: ₹4,000.00 / month</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold fs-13">DDC Shelf Monthly Rate (₹) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold">₹</span>
                                        <input type="number" step="0.01" name="ddc_shelf_amount" class="form-control fw-bold fs-15" value="{{ $ddcShelfAmount }}" required>
                                    </div>
                                    <div class="fs-11 text-muted mt-1">Default: ₹1,000.00 / month (50% Admin Share / 50% DDC Share)</div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-4">
                            <h6 class="fw-bold text-success mb-3 pb-2 border-bottom">
                                <iconify-icon icon="solar:shop-bold-duotone" class="align-middle me-1"></iconify-icon>
                                ADC Depot Rates
                            </h6>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="form-label fw-bold fs-13">ADC Rack Monthly Rate (₹) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold">₹</span>
                                        <input type="number" step="0.01" name="adc_rack_amount" class="form-control fw-bold fs-15" value="{{ $adcRackAmount }}" required>
                                    </div>
                                    <div class="fs-11 text-muted mt-1">Default: ₹2,000.00 / month</div>
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label fw-bold fs-13">ADC Shelf Monthly Rate (₹) <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold">₹</span>
                                        <input type="number" step="0.01" name="adc_shelf_amount" class="form-control fw-bold fs-15" value="{{ $adcShelfAmount }}" required>
                                    </div>
                                    <div class="fs-11 text-muted mt-1">Default: ₹1,000.00 / month (50% Admin Share / 50% ADC Share)</div>
                                </div>
                            </div>
                        </div>

                        <div class="text-end pt-3 border-top">
                            <button type="submit" class="btn btn-primary btn-sm px-4 fw-semibold">
                                <iconify-icon icon="solar:diskette-bold-duotone" class="align-middle me-1"></iconify-icon> Save Pricing Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
