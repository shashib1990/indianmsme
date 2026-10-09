@extends('layouts.pickup_store')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')
<div class="row">
    <!-- Welcome Card -->
    <div class="col-12 mb-4">
        <div class="card border-0 overflow-hidden" style="background: linear-gradient(135deg, rgba(16, 185, 129, 0.1) 0%, rgba(52, 211, 153, 0.05) 100%);">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h3 class="fw-bold text-slate-800 dark:text-white mb-1">Welcome back, {{ $store->contact_person }}!</h3>
                        <p class="text-slate-500 mb-0">Manage your Pickup Store profile and account details from this panel.</p>
                    </div>
                    <div class="d-none d-md-block">
                        <iconify-icon icon="solar:shop-bold-duotone" class="fs-60 text-primary opacity-75"></iconify-icon>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <!-- Store Details Grid -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center">
                <iconify-icon icon="solar:info-square-bold-duotone" class="fs-20 text-primary me-2"></iconify-icon>
                <h5 class="card-title mb-0">Store Information</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless align-middle mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted fw-medium py-2" style="width: 40%;">Store Code:</td>
                            <td class="text-slate-800 dark:text-slate-200 fw-bold py-2">
                                <span class="badge bg-primary-subtle text-primary fs-12 px-2.5 py-1">{{ $store->pickup_store_code }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-medium py-2">Store Name:</td>
                            <td class="text-slate-800 dark:text-slate-200 py-2">{{ $store->pickup_store_name }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-medium py-2">Contact Person:</td>
                            <td class="text-slate-800 dark:text-slate-200 py-2">{{ $store->contact_person }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-medium py-2">Mobile Number:</td>
                            <td class="text-slate-800 dark:text-slate-200 py-2">{{ $store->mobile }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-medium py-2">Email Address:</td>
                            <td class="text-slate-800 dark:text-slate-200 py-2">{{ $store->email ?: '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Owner & Status -->
    <div class="col-md-6 mb-4">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center">
                <iconify-icon icon="solar:shield-user-bold-duotone" class="fs-20 text-primary me-2"></iconify-icon>
                <h5 class="card-title mb-0">Account & Owner Details</h5>
            </div>
            <div class="card-body">
                <table class="table table-borderless align-middle mb-0">
                    <tbody>
                        <tr>
                            <td class="text-muted fw-medium py-2" style="width: 40%;">Owner Type:</td>
                            <td class="text-slate-800 dark:text-slate-200 py-2">
                                <span class="badge bg-info-subtle text-info px-2.5 py-1 fs-12">{{ $store->owner_type }}</span>
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-medium py-2">Owner Name:</td>
                            <td class="text-slate-800 dark:text-slate-200 py-2">{{ $store->owner?->name ?? '—' }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-medium py-2">Status:</td>
                            <td class="py-2">
                                @if($store->status === 'Active')
                                    <span class="badge bg-success-subtle text-success px-2.5 py-1 fs-12">
                                        <i class="bx bxs-circle me-1 align-middle fs-9"></i> Active
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger px-2.5 py-1 fs-12">
                                        <i class="bx bxs-circle me-1 align-middle fs-9"></i> Inactive
                                    </span>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td class="text-muted fw-medium py-2">Created Date:</td>
                            <td class="text-slate-800 dark:text-slate-200 py-2">{{ $store->created_at->format('d M Y, h:i A') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
