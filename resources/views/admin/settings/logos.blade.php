@extends('layouts.admin')

@section('title', 'Logo Settings')
@section('page-title', 'Logo Settings')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-10">
        <!-- Main Form -->
        <form action="{{ route('admin.settings.logos.update') }}" method="POST" enctype="multipart/form-data" data-no-compress="true">
            @csrf

            <!-- Header and Footer Logos Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:globus-bold-duotone" class="text-primary fs-22"></iconify-icon>
                    <h5 class="card-title fw-bold text-slate-800 mb-0">Main Website Logos</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <!-- Website Header Logo -->
                        <div class="col-md-6">
                            <div class="border rounded p-3 bg-light bg-opacity-50">
                                <label class="form-label fw-bold text-slate-700">Website Header Logo</label>
                                <div class="mb-3 d-flex align-items-center justify-content-center border rounded p-3" style="min-height: 120px; background-image: repeating-conic-gradient(#e2e8f0 0% 25%, #ffffff 0% 50%); background-size: 16px 16px;">
                                    @if($headerLogo)
                                        <img src="{{ $headerLogo }}" alt="Website Header Logo" class="img-fluid" style="max-height: 80px; object-fit: contain;">
                                    @else
                                        <span class="text-muted small">No Header Logo Uploaded</span>
                                    @endif
                                </div>
                                <div class="mb-3">
                                    <input class="form-control" type="file" name="website_header_logo" id="website_header_logo" accept="image/*" data-no-compress="true">
                                </div>
                                <small class="text-muted d-block">Recommended Size: 150px x 40px. Format: PNG, WebP, SVG, JPG.</small>
                            </div>
                        </div>

                        <!-- Website Footer Logo -->
                        <div class="col-md-6">
                            <div class="border rounded p-3 bg-light bg-opacity-50">
                                <label class="form-label fw-bold text-slate-700">Website Footer Logo</label>
                                <div class="mb-3 d-flex align-items-center justify-content-center border rounded p-3" style="min-height: 120px; background-image: repeating-conic-gradient(#e2e8f0 0% 25%, #ffffff 0% 50%); background-size: 16px 16px;">
                                    @if($footerLogo)
                                        <img src="{{ $footerLogo }}" alt="Website Footer Logo" class="img-fluid" style="max-height: 80px; object-fit: contain;">
                                    @else
                                        <span class="text-muted small">No Footer Logo Uploaded</span>
                                    @endif
                                </div>
                                <div class="mb-3">
                                    <input class="form-control" type="file" name="website_footer_logo" id="website_footer_logo" accept="image/*" data-no-compress="true">
                                </div>
                                <small class="text-muted d-block">Recommended Size: 150px x 40px. (Transparent logo recommended for dark footer). Format: PNG, WebP, SVG, JPG.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Panel & Favicon Logos Card -->
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:case-bold-duotone" class="text-primary fs-22"></iconify-icon>
                    <h5 class="card-title fw-bold text-slate-800 mb-0">System Panels & Favicon Configuration</h5>
                </div>
                <div class="card-body p-4">
                    <div class="row g-4">
                        <!-- Panel Sidebar & Login Logo -->
                        <div class="col-md-6">
                            <div class="border rounded p-3 bg-light bg-opacity-50">
                                <label class="form-label fw-bold text-slate-700">Panel Sidebar & Login Logo</label>
                                <div class="mb-3 d-flex align-items-center justify-content-center border rounded p-3" style="min-height: 120px; background-image: repeating-conic-gradient(#e2e8f0 0% 25%, #ffffff 0% 50%); background-size: 16px 16px;">
                                    @if($panelLogo)
                                        <img src="{{ $panelLogo }}" alt="Panel Logo" class="img-fluid" style="max-height: 80px; object-fit: contain;">
                                    @else
                                        <span class="text-muted small">No Panel Logo Uploaded</span>
                                    @endif
                                </div>
                                <div class="mb-3">
                                    <input class="form-control" type="file" name="panel_logo" id="panel_logo" accept="image/*" data-no-compress="true">
                                </div>
                                <small class="text-muted d-block">Used in Admin, Vendor, DDC, ADC, and Pickup Store Sidebars and Login pages. Format: PNG, WebP, SVG, JPG.</small>
                            </div>
                        </div>

                        <!-- Favicon -->
                        <div class="col-md-6">
                            <div class="border rounded p-3 bg-light bg-opacity-50">
                                <label class="form-label fw-bold text-slate-700">System Favicon Icon</label>
                                <div class="mb-3 d-flex align-items-center justify-content-center border rounded p-3" style="min-height: 120px; background-image: repeating-conic-gradient(#e2e8f0 0% 25%, #ffffff 0% 50%); background-size: 16px 16px;">
                                    @if($favicon)
                                        <img src="{{ $favicon }}" alt="Favicon" class="img-fluid" style="max-height: 48px; object-fit: contain;">
                                    @else
                                        <span class="text-muted small">No Favicon Uploaded</span>
                                    @endif
                                </div>
                                <div class="mb-3">
                                    <input class="form-control" type="file" name="favicon" id="favicon" accept="image/*" data-no-compress="true">
                                </div>
                                <small class="text-muted d-block">Used in browser tabs across all panels and websites. Format: ICO, PNG, WebP, SVG, GIF.</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="d-flex justify-content-end gap-2 mb-4">
                <button type="submit" class="btn btn-primary px-4 py-2 d-inline-flex align-items-center gap-1">
                    <iconify-icon icon="solar:diskette-bold-duotone" class="fs-18"></iconify-icon>
                    Save Logos
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
