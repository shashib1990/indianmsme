@extends('layouts.admin')

@section('title', 'Pricing Margin Settings')
@section('page-title', 'Pricing Margin Settings')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <!-- Main Configuration Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:settings-bold-duotone" class="text-primary fs-22"></iconify-icon>
                    <h5 class="card-title fw-bold text-slate-800 mb-0">Margin & MBP Configuration</h5>
                </div>
                <div class="card-body p-4">
                    <!-- Informative Alert -->
                    <div
                        class="alert alert-info border-0 bg-info-subtle text-info-emphasis d-flex align-items-start gap-2 mb-4 p-3 rounded">
                        <iconify-icon icon="solar:info-circle-bold-duotone"
                            class="fs-22 text-info mt-0.5"></iconify-icon>
                        <div>
                            <h6 class="fw-bold mb-1">System Margin Configuration</h6>
                            <p class="mb-0 small">
                                These margin settings will be saved directly into the system settings configuration. Updating these values will <strong>not</strong> alter or recalculate existing products in the products table.
                            </p>
                        </div>
                    </div>

                    <form action="{{ route('admin.settings.pricing.update') }}" method="POST" id="pricingSettingsForm">
                        @csrf
                        <div class="row g-4">
                            <!-- Admin Margin -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-slate-700">Admin Margin</label>
                                <div class="input-group">
                                    <input type="number" name="admin_margin_percentage" class="form-control" step="0.01"
                                        min="0" max="100" value="{{ old('admin_margin_percentage', $companyMargin) }}"
                                        required>
                                    <span class="input-group-text bg-light fw-medium">%</span>
                                </div>
                                <small class="text-muted d-block mt-1">Applied directly to Base Cost.</small>
                            </div>

                            <!-- DDC Margin -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-slate-700">DDC Margin</label>
                                <div class="input-group">
                                    <input type="number" name="ddc_margin_percentage" class="form-control" step="0.01"
                                        min="0" max="100" value="{{ old('ddc_margin_percentage', $ddcMargin) }}" required>
                                    <span class="input-group-text bg-light fw-medium">%</span>
                                </div>
                                <small class="text-muted d-block mt-1">Applied directly to Base Cost.</small>
                            </div>

                            <!-- MBP Percentage -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-slate-700">MBP Percentage</label>
                                <div class="input-group">
                                    <input type="number" name="mbp_percentage" class="form-control" step="0.01" min="0"
                                        max="100" value="{{ old('mbp_percentage', $mbp) }}" required>
                                    <span class="input-group-text bg-light fw-medium">%</span>
                                </div>
                                <small class="text-muted d-block mt-1">Applied directly to Base Cost.</small>
                            </div>

                            <!-- FSI Percentage -->
                            <div class="col-md-3">
                                <label class="form-label fw-bold text-slate-700">FSI Percentage</label>
                                <div class="input-group">
                                    <input type="number" name="fsi_percentage" class="form-control" step="0.01" min="0"
                                        max="100" value="{{ old('fsi_percentage', $fsi) }}" required>
                                    <span class="input-group-text bg-light fw-medium">%</span>
                                </div>
                                <small class="text-muted d-block mt-1">Field Sales Incentive (for non-DDC sellers).</small>
                            </div>
                        </div>

                        <hr class="my-4 border-slate-100">

                        <!-- Actions -->
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
                            <a href="{{ route('admin.products.all') }}" class="btn btn-outline-secondary px-3 py-2 d-inline-flex align-items-center gap-1.5 fs-13">
                                <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="fs-18 text-primary"></iconify-icon>
                                Manage Product-Level Margins (Export/Upload)
                            </a>
                            <button type="submit" class="btn btn-primary px-4 py-2 d-inline-flex align-items-center gap-1">
                                <iconify-icon icon="solar:diskette-bold-duotone" class="fs-18"></iconify-icon>
                                Save Margin Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Formula and Math Reference Card -->
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:bookmark-bold-duotone" class="text-slate-600 fs-20"></iconify-icon>
                    <h6 class="card-title fw-bold text-slate-800 mb-0">Pricing & Margin Formulas Reference</h6>
                </div>
                <div class="card-body p-4 bg-light bg-opacity-50">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <h6 class="fw-bold text-slate-800 mb-2">1. Price Calculation</h6>
                            <ul class="list-unstyled mb-0 small text-slate-700 lh-lg">
                                <li class="d-flex align-items-start gap-1.5">
                                    <span class="text-primary fw-bold">•</span>
                                    <span><strong>Base Cost:</strong> Seller Price Incl GST / (1 + GST%)</span>
                                </li>
                                <li class="d-flex align-items-start gap-1.5">
                                    <span class="text-primary fw-bold">•</span>
                                    <span><strong>Company GST:</strong> Seller Price Incl GST - Base Cost</span>
                                </li>
                                <li class="d-flex align-items-start gap-1.5">
                                    <span class="text-primary fw-bold">•</span>
                                    <span><strong>Admin Margin Amount:</strong> Base Cost × Admin Margin %</span>
                                </li>
                                <li class="d-flex align-items-start gap-1.5">
                                    <span class="text-primary fw-bold">•</span>
                                    <span><strong>DDC Margin Amount:</strong> DDC Purchase Price (w/o GST) × DDC Margin %</span>
                                </li>
                                <li class="d-flex align-items-start gap-1.5">
                                    <span class="text-primary fw-bold">•</span>
                                    <span><strong>Price Before GST:</strong> DDC Purchase Price (w/o GST) + DDC Margin Amount</span>
                                </li>
                                <li class="d-flex align-items-start gap-1.5">
                                    <span class="text-primary fw-bold">•</span>
                                    <span><strong>Seller GST Amount:</strong> Price Before GST × GST %</span>
                                </li>
                                <li class="d-flex align-items-start gap-1.5">
                                    <span class="text-primary fw-bold">•</span>
                                    <span><strong>ADC Purchase Price:</strong> Price Before GST + Seller GST</span>
                                </li>
                            </ul>
                        </div>
                        <div class="col-md-6">
                            <h6 class="fw-bold text-slate-800 mb-2">2. MBP Calculation</h6>
                            <ul class="list-unstyled mb-0 small text-slate-700 lh-lg">
                                <li class="d-flex align-items-start gap-1.5">
                                    <span class="text-success fw-bold">•</span>
                                    <span><strong>MBP Amount:</strong> Base Cost × MBP %</span>
                                </li>
                                <li class="d-flex align-items-start gap-1.5">
                                    <span class="text-success fw-bold">•</span>
                                    <span><strong>MBP 90% Share:</strong> MBP Amount × 90%</span>
                                </li>
                                <li class="d-flex align-items-start gap-1.5">
                                    <span class="text-success fw-bold">•</span>
                                    <span><strong>MBP 10% Share:</strong> MBP Amount × 10%</span>
                                </li>
                                <li class="d-flex align-items-start gap-1.5">
                                    <span class="text-success fw-bold">•</span>
                                    <span><strong>MBP 38% Share:</strong> MBP 90% Share × 38%</span>
                                </li>
                                <li class="d-flex align-items-start gap-1.5">
                                    <span class="text-success fw-bold">•</span>
                                    <span><strong>MBP 62% Share:</strong> MBP 90% Share × 62%</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection