@extends('layouts.vendor')

@section('title', 'View Pickup Store')
@section('page-title', 'Pickup Store Details')

@section('content')
    <div class="row">
        <div class="col-12 mb-3">
            <a href="{{ route('vendor.pickup-stores.index') }}" class="btn btn-sm btn-outline-secondary">
                <iconify-icon icon="solar:arrow-left-bold-duotone" class="align-middle me-1"></iconify-icon>
                Back to List
            </a>
            <a href="{{ route('vendor.pickup-stores.delivery-areas.index', $store->id) }}" class="btn btn-sm btn-success float-end me-2">
                <iconify-icon icon="solar:routing-bold-duotone" class="align-middle me-1"></iconify-icon>
                Manage Delivery Areas
            </a>
            <a href="{{ route('vendor.pickup-stores.edit', $store->id) }}" class="btn btn-sm btn-primary float-end">
                <iconify-icon icon="solar:pen-bold-duotone" class="align-middle me-1"></iconify-icon>
                Edit Store
            </a>
        </div>
    </div>

    <div class="row">
        <!-- Overview Card -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title fw-bold text-slate-800 mb-0">Overview</h5>
                </div>
                <div class="card-body text-center">
                    <div class="avatar-lg bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 72px; height: 72px;">
                        <iconify-icon icon="solar:shop-bold-duotone" class="fs-40"></iconify-icon>
                    </div>
                    <h5 class="fw-bold text-slate-800 mb-1">{{ $store->pickup_store_name }}</h5>
                    <span class="badge bg-primary-subtle text-primary px-2.5 py-1 fs-12 font-monospace">{{ $store->pickup_store_code }}</span>
                    
                    <hr class="my-4">

                    <div class="text-start">
                        <div class="mb-3">
                            <label class="text-muted fs-12 mb-1 d-block">Owner</label>
                            <span class="fw-semibold text-slate-800">{{ $store->owner?->name }} ({{ $store->owner_type }})</span>
                        </div>

                        <div class="mb-3">
                            <label class="text-muted fs-12 mb-1 d-block">Account Status</label>
                            @if($store->status === 'Active')
                                <span class="badge bg-success-subtle text-success fs-11 px-2.5 py-1">
                                    <i class="bx bxs-circle me-1 align-middle fs-8"></i> Active
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger fs-11 px-2.5 py-1">
                                    <i class="bx bxs-circle me-1 align-middle fs-8"></i> Inactive
                                </span>
                            @endif
                        </div>

                        <div class="mb-3">
                            <label class="text-muted fs-12 mb-1 d-block">Capabilities</label>
                            @if($store->can_receive_payment_and_dispatch)
                                <span class="badge bg-info-subtle text-info fs-11 px-2.5 py-1">
                                    Independent Portal
                                </span>
                            @else
                                <span class="badge bg-secondary-subtle text-secondary fs-11 px-2.5 py-1">
                                    Seller Managed
                                </span>
                            @endif
                        </div>

                        <div class="mb-0">
                            <label class="text-muted fs-12 mb-1 d-block">Created Date</label>
                            <span class="fw-semibold text-slate-800">{{ $store->created_at->format('d M Y, h:i A') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Full Store Information -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100 shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title fw-bold text-slate-800 mb-0">Store Details</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-borderless align-middle mb-0">
                            <tbody>
                                <tr>
                                    <td class="text-muted fw-medium py-2.5" style="width: 30%;">Pickup Store Name:</td>
                                    <td class="text-slate-800 py-2.5 fw-semibold">{{ $store->pickup_store_name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-medium py-2.5">Contact Person:</td>
                                    <td class="text-slate-800 py-2.5 fw-semibold">{{ $store->contact_person }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-medium py-2.5">Mobile Number:</td>
                                    <td class="text-slate-800 py-2.5 fw-semibold">{{ $store->mobile }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-medium py-2.5">Email Address:</td>
                                    <td class="text-slate-800 py-2.5 fw-semibold">{{ $store->email }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-medium py-2.5">GST Number:</td>
                                    <td class="text-slate-800 py-2.5 fw-semibold">{{ $store->gst_number }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-medium py-2.5">Pincode:</td>
                                    <td class="text-slate-800 py-2.5 fw-semibold">{{ $store->pincode }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-medium py-2.5">Address:</td>
                                    <td class="text-slate-800 py-2.5 fw-semibold text-wrap" style="max-width: 400px;">{{ $store->address }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-medium py-2.5">Landmark:</td>
                                    <td class="text-slate-800 py-2.5 fw-semibold">{{ $store->landmark ?: '—' }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-medium py-2.5">District:</td>
                                    <td class="text-slate-800 py-2.5 fw-semibold">{{ $store->districtRelation?->name }}</td>
                                </tr>
                                <tr>
                                    <td class="text-muted fw-medium py-2.5">State:</td>
                                    <td class="text-slate-800 py-2.5 fw-semibold">{{ $store->stateRelation?->name }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        @if($store->can_receive_payment_and_dispatch)
        <!-- Bank Details Section -->
        <div class="col-12 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="card-title fw-bold text-slate-800 mb-0">Bank Settlement Details</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="text-muted fs-12 mb-1 d-block fw-semibold">Account Holder Name</label>
                            <span class="fw-bold text-slate-800 fs-14">{{ $store->account_holder_name ?: '—' }}</span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted fs-12 mb-1 d-block fw-semibold">Bank Account Number</label>
                            <span class="fw-bold text-slate-800 fs-14">{{ $store->bank_account_number ?: '—' }}</span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted fs-12 mb-1 d-block fw-semibold">IFSC Code</label>
                            <span class="fw-bold text-slate-800 fs-14">{{ $store->ifsc_code ?: '—' }}</span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted fs-12 mb-1 d-block fw-semibold">Bank Name</label>
                            <span class="fw-bold text-slate-800 fs-14">{{ $store->bank_name ?: '—' }}</span>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="text-muted fs-12 mb-1 d-block fw-semibold">Branch Name</label>
                            <span class="fw-bold text-slate-800 fs-14">{{ $store->branch_name ?: '—' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
    </div>
@endsection
