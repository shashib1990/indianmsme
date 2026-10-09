@extends('layouts.ddc')

@section('title', 'Canteen Details - ' . ($canteen->adcProfile?->company_name ?? $canteen->name))

@section('content')
    <!-- Header Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-7">
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ route('ddc.canteens.index') }}" class="btn btn-outline-secondary btn-sm me-2">
                                    <iconify-icon icon="solar:arrow-left-bold-duotone" class="align-middle me-1"></iconify-icon> Back
                                </a>
                                <div>
                                    <h4 class="fw-bold text-slate-800 mb-0" style="font-family: 'Outfit', sans-serif;">
                                        {{ $canteen->adcProfile?->company_name ?? $canteen->name }}
                                    </h4>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace fs-12 me-2">
                                        {{ $canteen->adcProfile?->adc_code ?? $canteen->adcProfile?->code ?? 'ADC-' . $canteen->id }}
                                    </span>
                                    <span class="badge {{ ($canteen->adcProfile?->type ?? 'ASC') === 'ASC' ? 'bg-info-subtle text-info' : 'bg-secondary-subtle text-secondary' }} fs-11 uppercase fw-bold">
                                        {{ $canteen->adcProfile?->type ?? 'ASC Canteen' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-5 text-md-end">
                            <a href="{{ route('ddc.adc-orders.index', ['search' => $canteen->adcProfile?->adc_code ?? $canteen->name]) }}" class="btn btn-outline-primary btn-sm fw-semibold me-1">
                                <iconify-icon icon="solar:bag-check-bold-duotone" class="align-middle me-1"></iconify-icon> View Orders
                            </a>
                            <a href="{{ route('ddc.adc-wallet-requests.index') }}" class="btn btn-primary btn-sm fw-semibold">
                                <iconify-icon icon="solar:wallet-bold-duotone" class="align-middle me-1"></iconify-icon> Wallet Requests
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Summary Widgets -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-12 fw-semibold uppercase">Current Wallet Balance</span>
                            <h3 class="fw-bold text-success mb-0 mt-1" style="font-family: 'Outfit', sans-serif;">₹{{ number_format($canteen->adcProfile?->wallet_balance ?? 0, 2) }}</h3>
                        </div>
                        <div class="avatar-sm bg-success-subtle text-success rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <iconify-icon icon="solar:wallet-money-bold-duotone" class="fs-24"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-12 fw-semibold uppercase">Total Orders Placed</span>
                            <h3 class="fw-bold text-dark mb-0 mt-1" style="font-family: 'Outfit', sans-serif;">{{ $totalOrdersCount }}</h3>
                        </div>
                        <div class="avatar-sm bg-primary-subtle text-primary rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <iconify-icon icon="solar:bag-check-bold-duotone" class="fs-24"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card border-0 shadow-sm rounded-3">
                <div class="card-body p-3">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted fs-12 fw-semibold uppercase">Completed Orders Total</span>
                            <h3 class="fw-bold text-primary mb-0 mt-1" style="font-family: 'Outfit', sans-serif;">₹{{ number_format($totalOrdersAmount, 2) }}</h3>
                        </div>
                        <div class="avatar-sm bg-warning-subtle text-warning rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <iconify-icon icon="solar:bill-check-bold-duotone" class="fs-24"></iconify-icon>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Profile Details Card -->
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold text-slate-800 mb-0">Canteen Profile Information</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm align-middle mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted fw-medium" style="width: 35%;">Owner Name:</td>
                                <td class="fw-semibold text-slate-800">{{ $canteen->name }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-medium">Firm / Store:</td>
                                <td class="fw-semibold text-slate-800">{{ $canteen->adcProfile?->company_name ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-medium">Canteen Code:</td>
                                <td><code class="text-primary font-monospace fw-bold">{{ $canteen->adcProfile?->adc_code ?? $canteen->adcProfile?->code ?? '—' }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-medium">Store Type:</td>
                                <td><span class="badge bg-info-subtle text-info fw-bold">{{ $canteen->adcProfile?->type ?? 'ASC' }}</span></td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-medium">GST Number:</td>
                                <td><code class="text-dark">{{ $canteen->adcProfile?->gst_number ?? 'N/A' }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-medium">Status:</td>
                                <td>
                                    @if($canteen->status == 1 || $canteen->status == '1')
                                        <span class="badge bg-success text-white">Active</span>
                                    @else
                                        <span class="badge bg-danger text-white">Inactive</span>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-3 h-100">
                <div class="card-header bg-white py-3 border-bottom">
                    <h6 class="fw-bold text-slate-800 mb-0">Contact & Address Details</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless table-sm align-middle mb-0">
                        <tbody>
                            <tr>
                                <td class="text-muted fw-medium" style="width: 35%;">Mobile:</td>
                                <td class="fw-semibold text-slate-800">{{ $canteen->mobile ?? $canteen->adcProfile?->phone ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-medium">Email:</td>
                                <td class="text-slate-700">{{ $canteen->email ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-medium">District:</td>
                                <td class="text-slate-800 fw-medium">{{ $canteen->adcProfile?->district ?? $canteen->district ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-medium">State:</td>
                                <td class="text-slate-800 fw-medium">{{ $canteen->adcProfile?->state ?? $canteen->state ?? '—' }}</td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-medium">Pincode:</td>
                                <td><code class="text-dark fw-bold">{{ $canteen->adcProfile?->pincode ?? $canteen->pincode ?? '—' }}</code></td>
                            </tr>
                            <tr>
                                <td class="text-muted fw-medium">Address:</td>
                                <td class="text-slate-700 small">{{ $canteen->adcProfile?->address ?? $canteen->address ?? '—' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Orders by Canteen -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-slate-800 mb-0">Recent Wholesale Orders</h6>
            <a href="{{ route('ddc.adc-orders.index') }}" class="btn btn-xs btn-outline-primary fw-semibold">View All Orders</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="bg-light fs-11 text-uppercase text-muted">
                        <tr>
                            <th class="ps-3">Order Number</th>
                            <th>Order Date</th>
                            <th>Total Amount</th>
                            <th>Payment Status</th>
                            <th>Order Status</th>
                            <th class="pe-3 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentOrders as $order)
                            <tr>
                                <td class="ps-3 font-monospace fw-bold text-primary fs-12">#{{ $order->order_number }}</td>
                                <td class="text-muted fs-12">{{ $order->created_at->format('d M Y, h:i A') }}</td>
                                <td class="fw-bold text-dark fs-13">₹{{ number_format($order->grand_total, 2) }}</td>
                                <td>
                                    <span class="badge bg-success-subtle text-success fs-11 px-2 py-0.5">{{ $order->payment_status }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-info-subtle text-info fs-11 px-2 py-0.5">{{ $order->order_status }}</span>
                                </td>
                                <td class="pe-3 text-end">
                                    <a href="{{ route('ddc.adc-orders.show', $order->id) }}" class="btn btn-xs btn-light border px-2 py-1">Details</a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted fs-13">No recent orders placed by this canteen.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Recent Wallet Requests by Canteen -->
    <div class="card border-0 shadow-sm rounded-3 mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
            <h6 class="fw-bold text-slate-800 mb-0">Recent Wallet Top-Up Requests</h6>
            <a href="{{ route('ddc.adc-wallet-requests.index') }}" class="btn btn-xs btn-outline-primary fw-semibold">View All Requests</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 text-center">
                    <thead class="bg-light fs-11 text-uppercase text-muted">
                        <tr>
                            <th class="ps-3">Request ID</th>
                            <th>Amount</th>
                            <th>UTR / Reference</th>
                            <th>Requested On</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($walletRequests as $req)
                            <tr>
                                <td class="ps-3 font-monospace fw-bold text-dark fs-12">#{{ $req->id }}</td>
                                <td class="fw-bold text-success fs-13">₹{{ number_format($req->amount, 2) }}</td>
                                <td><code class="text-dark">{{ $req->utr_number ?? '—' }}</code></td>
                                <td class="text-muted fs-12">{{ $req->created_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    @if($req->status === 'Approved')
                                        <span class="badge bg-success text-white fs-11 px-2 py-0.5">Approved</span>
                                    @elseif($req->status === 'Pending')
                                        <span class="badge bg-warning-subtle text-warning fs-11 px-2 py-0.5">Pending</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger fs-11 px-2 py-0.5">Rejected</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-4 text-muted fs-13">No recent wallet top-up requests.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
