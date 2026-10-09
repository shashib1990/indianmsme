@extends('layouts.ddc')

@section('title', 'Retail POS Customers Directory')

@section('content')
    <!-- Header Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-7">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" class="text-primary align-middle me-1"></iconify-icon>
                                Retail POS Customers Directory
                            </h4>
                            <p class="text-muted small mb-0">View, search, and track all walk-in and registered retail customers served at your DDC POS counter.</p>
                        </div>
                        <div class="col-md-5 text-md-end">
                            <a href="{{ route('ddc.pos.retail') }}" class="btn btn-primary btn-sm fw-semibold me-1">
                                <iconify-icon icon="solar:shop-2-bold-duotone" class="align-middle me-1"></iconify-icon> Open Retail POS
                            </a>
                            <a href="{{ route('ddc.pos.orders') }}" class="btn btn-outline-primary btn-sm fw-semibold">
                                <iconify-icon icon="solar:bill-list-bold-duotone" class="align-middle me-1"></iconify-icon> Sales History
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Metric Widgets -->
    <div class="row g-3 mb-4">
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-12 fw-semibold uppercase tracking-wider">Total POS Customers</span>
                            <h3 class="fw-bold text-dark mb-0 mt-1" style="font-family: 'Outfit', sans-serif;">{{ number_format($totalCustomers) }}</h3>
                        </div>
                        <div class="avatar-sm bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <iconify-icon icon="solar:users-group-rounded-bold-duotone" class="fs-24"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-12 fw-semibold uppercase tracking-wider">My Store Customers</span>
                            <h3 class="fw-bold text-info mb-0 mt-1" style="font-family: 'Outfit', sans-serif;">{{ number_format($myCustomersCount) }}</h3>
                        </div>
                        <div class="avatar-sm bg-info-subtle text-info rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <iconify-icon icon="solar:shop-bold-duotone" class="fs-24"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-12 fw-semibold uppercase tracking-wider">Total Retail Spend</span>
                            <h3 class="fw-bold text-success mb-0 mt-1" style="font-family: 'Outfit', sans-serif;">₹{{ number_format($totalSpend, 2) }}</h3>
                        </div>
                        <div class="avatar-sm bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <iconify-icon icon="solar:card-recive-bold-duotone" class="fs-24"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3 col-sm-6">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-12 fw-semibold uppercase tracking-wider">Avg Spend / Customer</span>
                            <h3 class="fw-bold text-warning mb-0 mt-1" style="font-family: 'Outfit', sans-serif;">₹{{ number_format($avgSpend, 2) }}</h3>
                        </div>
                        <div class="avatar-sm bg-warning-subtle text-warning rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <iconify-icon icon="solar:chart-2-bold-duotone" class="fs-24"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="card border-0 shadow-sm rounded-3 mb-3">
        <div class="card-body p-3">
            <form method="GET" action="{{ route('ddc.pos.customers') }}" class="row g-2 align-items-center">
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0"><iconify-icon icon="solar:magnifer-bold-duotone" class="text-muted"></iconify-icon></span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Search by name, mobile, AVI Id, email, address, GSTIN or pincode..." value="{{ $search }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <select name="scope" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all" {{ $scope === 'all' ? 'selected' : '' }}>Scope: All POS Customers</option>
                        <option value="my_ddc" {{ $scope === 'my_ddc' ? 'selected' : '' }}>Scope: My DDC Customers Only</option>
                    </select>
                </div>
                <div class="col-md-2">
                    <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="created_at" {{ $sort === 'created_at' ? 'selected' : '' }}>Sort by Recently Added</option>
                        <option value="name" {{ $sort === 'name' ? 'selected' : '' }}>Sort by Name</option>
                        <option value="orders_count" {{ $sort === 'orders_count' ? 'selected' : '' }}>Sort by Orders Count</option>
                        <option value="orders_sum_total_amount" {{ $sort === 'orders_sum_total_amount' ? 'selected' : '' }}>Sort by Total Spend (₹)</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex gap-1">
                    <button type="submit" class="btn btn-primary btn-sm px-3 fw-semibold w-100">Filter</button>
                    @if($search || $scope !== 'all')
                        <a href="{{ route('ddc.pos.customers') }}" class="btn btn-light btn-sm">Reset</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <!-- Customers Data Table -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-dark text-white fs-11 text-uppercase">
                        <tr>
                            <th class="ps-3" style="width: 4%;">#</th>
                            <th style="width: 22%;">Customer Name & Mobile</th>
                            <th style="width: 14%;">AVI Id</th>
                            <th style="width: 17%;">Email & GSTIN</th>
                            <th style="width: 17%;">Address & Pincode</th>
                            <th style="width: 12%;">Origin DDC</th>
                            <th style="width: 10%;" class="text-center">Orders & Spend</th>
                            <th class="pe-3 text-end" style="width: 8%;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $index => $customer)
                            <tr>
                                <td class="ps-3 fw-bold fs-13 text-muted">{{ $customers->firstItem() + $index }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-xs bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center fw-bold fs-12" style="width: 34px; height: 34px;">
                                            {{ strtoupper(substr($customer->name ?: 'C', 0, 1)) }}
                                        </div>
                                        <div>
                                            <strong class="text-slate-800 d-block fs-13">{{ $customer->name ?: 'Walk-in Customer' }}</strong>
                                            <span class="text-success fw-medium fs-12">
                                                <iconify-icon icon="solar:phone-calling-bold-duotone" class="align-middle me-0.5"></iconify-icon>
                                                {{ $customer->mobile }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    @if(!empty($customer->abhhyam_code))
                                        <span class="badge fs-11 px-2.5 py-1 rounded-2 font-monospace fw-bold" style="background: #fef3c7; color: #92400e; border: 1px solid #fde68a;" title="AVI Id">
                                            ★ {{ $customer->abhhyam_code }}
                                        </span>
                                    @else
                                        <span class="text-muted fs-11">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if($customer->email)
                                        <div class="text-slate-700 fs-12 truncate" style="max-width: 180px;" title="{{ $customer->email }}">
                                            <iconify-icon icon="solar:letter-bold-duotone" class="text-muted align-middle me-0.5"></iconify-icon>
                                            {{ $customer->email }}
                                        </div>
                                    @else
                                        <span class="text-muted fs-11">—</span>
                                    @endif
                                    @if($customer->gst_number)
                                        <code class="text-dark fs-11 d-block mt-0.5">GST: {{ $customer->gst_number }}</code>
                                    @endif
                                </td>
                                <td>
                                    @if($customer->address || $customer->pincode)
                                        <div class="text-slate-700 fs-12">
                                            <iconify-icon icon="solar:map-point-bold-duotone" class="text-danger align-middle me-0.5"></iconify-icon>
                                            {{ $customer->address ?: 'N/A' }}
                                        </div>
                                        @if($customer->pincode)
                                            <code class="text-muted fs-11">PIN: {{ $customer->pincode }}</code>
                                        @endif
                                    @else
                                        <span class="text-muted fs-11">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-slate-700 border fs-11 fw-medium px-2 py-0.5">
                                        {{ $customer->ddc?->name ?? 'Walk-in / General' }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info-subtle text-info border border-info-subtle fs-11 px-2 py-0.5 fw-bold d-block mb-1">
                                        {{ number_format($customer->orders_count ?? 0) }} Orders
                                    </span>
                                    <div class="fw-bold text-success fs-12">₹{{ number_format($customer->orders_sum_total_amount ?? 0, 2) }}</div>
                                </td>
                                <td class="pe-3 text-end">
                                    <a href="{{ route('ddc.pos.orders', ['search' => $customer->mobile]) }}" class="btn btn-xs btn-outline-primary px-2 py-1 fw-semibold">
                                        <iconify-icon icon="solar:bill-list-bold-duotone" class="align-middle me-0.5"></iconify-icon> History
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" class="fs-48 text-muted mb-2"></iconify-icon>
                                    <h5 class="fw-bold text-dark mb-1">No Customers Found</h5>
                                    <p class="text-muted fs-13 mb-0">No records found in `ddc_pos_customers` matching your search criteria.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($customers->hasPages())
        <div class="mt-3">
            {{ $customers->links() }}
        </div>
    @endif
@endsection
