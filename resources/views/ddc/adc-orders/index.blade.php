@extends('layouts.ddc')

@section('title', 'ADC Orders')
@section('page-title', 'ADC Orders Management')

@section('content')
<!-- KPI Statistics Cards -->
<div class="row g-3 mb-4">
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Total ADC Orders</span>
                    <h4 class="fw-bold text-slate-800 mb-0">{{ number_format($totalOrdersCount ?? 0) }}</h4>
                </div>
                <div class="avatar-md bg-primary-subtle rounded-circle d-flex align-items-center justify-content-center">
                    <iconify-icon icon="solar:bag-check-bold-duotone" class="fs-24 text-primary"></iconify-icon>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Total Business Value</span>
                    <h4 class="fw-bold text-success mb-0">₹{{ number_format($totalOrdersAmount ?? 0, 2) }}</h4>
                </div>
                <div class="avatar-md bg-success-subtle rounded-circle d-flex align-items-center justify-content-center">
                    <iconify-icon icon="solar:wallet-money-bold-duotone" class="fs-24 text-success"></iconify-icon>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Pending Orders</span>
                    <h4 class="fw-bold text-warning mb-0">{{ number_format($pendingOrdersCount ?? 0) }}</h4>
                </div>
                <div class="avatar-md bg-warning-subtle rounded-circle d-flex align-items-center justify-content-center">
                    <iconify-icon icon="solar:clock-circle-bold-duotone" class="fs-24 text-warning"></iconify-icon>
                </div>
            </div>
        </div>
    </div>
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-sm">
            <div class="card-body p-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold text-uppercase d-block mb-1">Dispatched Orders</span>
                    <h4 class="fw-bold text-info mb-0">{{ number_format($dispatchedOrdersCount ?? 0) }}</h4>
                </div>
                <div class="avatar-md bg-info-subtle rounded-circle d-flex align-items-center justify-content-center">
                    <iconify-icon icon="solar:delivery-bold-duotone" class="fs-24 text-info"></iconify-icon>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0">
            <!-- Header Section with Navigation Tabs & Filter Bar -->
            <div class="card-header bg-white py-3 border-bottom">
                <div class="row align-items-center g-3">
                    <div class="col-md-4">
                        <!-- Nav Tabs -->
                        <ul class="nav nav-pills nav-custom-pill gap-1" id="orderViewTabs" role="tablist">
                            <li class="nav-item">
                                <button class="nav-link active py-1.5 px-3 fs-13 fw-semibold" id="all-orders-tab" data-bs-toggle="tab" data-bs-target="#all-orders-pane" type="button" role="tab">
                                    <iconify-icon icon="solar:document-text-bold-duotone" class="align-middle me-1"></iconify-icon> Orders List ({{ $orders->total() }})
                                </button>
                            </li>
                            <li class="nav-item">
                                <button class="nav-link py-1.5 px-3 fs-13 fw-semibold" id="product-summary-tab" data-bs-toggle="tab" data-bs-target="#product-summary-pane" type="button" role="tab">
                                    <iconify-icon icon="solar:box-bold-duotone" class="align-middle me-1"></iconify-icon> Product Aggregates
                                </button>
                            </li>
                        </ul>
                    </div>

                    <div class="col-md-8 text-md-end">
                        <form action="{{ route('ddc.adc-orders.index') }}" method="GET" class="d-flex flex-wrap align-items-center justify-content-md-end gap-2" id="filterForm">
                            <!-- Status Filter -->
                            <select name="status" class="form-select form-select-sm fw-medium" onchange="this.form.submit()" style="max-width: 150px;">
                                <option value="">All Statuses</option>
                                <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                                <option value="Processing" {{ request('status') == 'Processing' ? 'selected' : '' }}>Processing</option>
                                <option value="Dispatched" {{ request('status') == 'Dispatched' ? 'selected' : '' }}>Dispatched</option>
                                <option value="Received" {{ request('status') == 'Received' ? 'selected' : '' }}>Received</option>
                                <option value="Delivered" {{ request('status') == 'Delivered' ? 'selected' : '' }}>Delivered</option>
                                <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                            </select>

                            <!-- ADC Canteen Filter -->
                            <select name="adc_id" class="form-select form-select-sm fw-medium" onchange="this.form.submit()" style="max-width: 200px;">
                                <option value="">All ADC Canteens</option>
                                @foreach($areaAdcList as $adcUser)
                                    <option value="{{ $adcUser->id }}" {{ request('adc_id') == $adcUser->id ? 'selected' : '' }}>
                                        {{ $adcUser->name }} {{ $adcUser->adcProfile?->company_name ? '('.$adcUser->adcProfile->company_name.')' : '' }}
                                    </option>
                                @endforeach
                            </select>

                            <!-- Search Filter -->
                            <div class="input-group input-group-sm" style="max-width: 220px;">
                                <input type="text" name="search" class="form-control" placeholder="Search order / canteen..." value="{{ request('search') }}">
                                <button class="btn btn-primary" type="submit">
                                    <iconify-icon icon="solar:magnifer-linear" class="align-middle"></iconify-icon>
                                </button>
                            </div>

                            @if(request()->hasAny(['search', 'adc_id', 'status']))
                                <a href="{{ route('ddc.adc-orders.index') }}" class="btn btn-sm btn-light border fw-medium" title="Reset Filters">
                                    <i class="bx bx-reset me-1"></i> Reset
                                </a>
                            @endif
                        </form>
                    </div>
                </div>
            </div>
            
            <!-- Tab Contents -->
            <div class="tab-content" id="orderViewTabContent">
                <!-- Tab 1: Orders List -->
                <div class="tab-pane fade show active" id="all-orders-pane" role="tabpanel">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light border-bottom">
                                    <tr>
                                        <th class="ps-3" style="width: 160px;">Order Number</th>
                                        <th style="width: 150px;">Order Date</th>
                                        <th>ADC Canteen</th>
                                        <th class="text-center" style="width: 110px;">Items</th>
                                        <th class="text-end" style="width: 140px;">Total Amount</th>
                                        <th class="text-center" style="width: 120px;">Payment</th>
                                        <th class="text-center" style="width: 130px;">Status</th>
                                        <th class="text-end pe-3" style="width: 140px;">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($orders as $order)
                                    <tr>
                                        <td class="ps-3">
                                            <a href="{{ route('ddc.adc-orders.show', $order->id) }}" class="fw-bold text-primary fs-14">
                                                #{{ $order->order_number }}
                                            </a>
                                            @if($order->virtual_ddc_id)
                                                <span class="badge bg-purple-subtle text-purple fs-10 d-block mt-0.5" style="max-width: fit-content;">Virtual Fulfillment</span>
                                            @elseif($order->selected_ddc_id)
                                                <span class="badge bg-primary-subtle text-primary fs-10 d-block mt-0.5" style="max-width: fit-content;">Physical Fulfillment</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-medium text-slate-800 d-block fs-13">{{ $order->created_at->format('d M Y') }}</span>
                                            <span class="text-muted fs-11">{{ $order->created_at->format('h:i A') }}</span>
                                        </td>
                                        <td>
                                            <strong class="text-slate-800 fs-14 d-block">{{ $order->adc?->name ?? 'ADC Canteen' }}</strong>
                                            @if($order->adc?->adcProfile?->company_name)
                                                <span class="text-muted fs-12 d-block fw-medium">{{ $order->adc->adcProfile->company_name }}</span>
                                            @endif
                                            @if($order->adc?->mobile)
                                                <span class="text-muted fs-11"><i class="bx bx-phone me-0.5"></i>{{ $order->adc->mobile }}</span>
                                            @endif
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-secondary-subtle text-secondary fs-12 px-2.5 py-1 fw-bold rounded-pill">
                                                {{ $order->items->count() }} Item(s)
                                            </span>
                                        </td>
                                        <td class="text-end">
                                            <span class="fw-bold text-slate-900 fs-15">₹{{ number_format($order->total_amount, 2) }}</span>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge {{ $order->payment_status === 'Paid' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} px-2.5 py-1 fs-12">
                                                {{ $order->payment_status }}
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            @php
                                                $statusBadges = [
                                                    'Pending'    => 'bg-warning-subtle text-warning',
                                                    'Processing' => 'bg-info-subtle text-info',
                                                    'Dispatched' => 'bg-primary-subtle text-primary',
                                                    'Received'   => 'bg-success-subtle text-success',
                                                    'Delivered'  => 'bg-success text-white',
                                                    'Cancelled'  => 'bg-danger-subtle text-danger',
                                                ];
                                            @endphp
                                            <span class="badge {{ $statusBadges[$order->status] ?? 'bg-secondary-subtle text-secondary' }} px-2.5 py-1 fs-12">
                                                {{ $order->status }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <a href="{{ route('ddc.adc-orders.show', $order->id) }}" class="btn btn-sm btn-primary py-1 px-2.5" title="View Order Details">
                                                <i class="bx bx-show me-1"></i> Details
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="8" class="text-center py-5 text-muted">
                                            <iconify-icon icon="solar:box-minimalistic-broken" class="fs-40 text-muted mb-2"></iconify-icon><br>
                                            <h6 class="fw-bold">No ADC Orders Found</h6>
                                            <p class="small text-muted mb-0">No orders match the selected filters or area criteria.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    @if(isset($orders) && $orders->hasPages())
                    <div class="card-footer border-top bg-white py-3">
                        {{ $orders->links() }}
                    </div>
                    @endif
                </div>

                <!-- Tab 2: Product Summaries Aggregates -->
                <div class="tab-pane fade" id="product-summary-pane" role="tabpanel">
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light border-bottom">
                                    <tr>
                                        <th class="ps-3 text-center" style="width: 70px;">S.No.</th>
                                        <th>ADC Canteen</th>
                                        <th>Product Details</th>
                                        <th class="text-center" style="width: 140px;">Total Qty</th>
                                        <th class="text-end pe-3" style="width: 160px;">Total Amount</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($productSummaries as $item)
                                    <tr>
                                        <td class="ps-3 text-center fw-semibold text-slate-600">
                                            {{ ($productSummaries->currentPage() - 1) * $productSummaries->perPage() + $loop->iteration }}
                                        </td>
                                        <td>
                                            <strong class="text-slate-800 fs-14 d-block">{{ $item->adc_name ?? 'ADC Canteen' }}</strong>
                                            @if($item->adc_company_name)
                                                <span class="text-muted fs-12 d-block fw-medium">{{ $item->adc_company_name }}</span>
                                            @endif
                                            @if($item->adc_mobile)
                                                <span class="text-muted fs-11"><i class="bx bx-phone me-0.5"></i>{{ $item->adc_mobile }}</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2.5">
                                                <div class="bg-light rounded overflow-hidden d-flex align-items-center justify-content-center flex-shrink-0 border"
                                                     style="width: 44px; height: 44px; min-width: 44px;">
                                                    @if($item->featured_image)
                                                        <img src="{{ filter_var($item->featured_image, FILTER_VALIDATE_URL) ? $item->featured_image : asset('storage/' . $item->featured_image) }}"
                                                             alt="Product Image" class="img-fluid object-fit-contain p-1" style="max-height: 100%; max-width: 100%;"
                                                             onerror="this.onerror=null; this.src='{{ asset('admin-assets/images/IndianMSME.png') }}';">
                                                    @else
                                                        <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="text-muted fs-22"></iconify-icon>
                                                    @endif
                                                </div>
                                                <div class="flex-grow-1" style="min-width: 0;">
                                                    <h6 class="mb-0 fs-14 fw-bold text-slate-800 text-wrap" style="word-break: break-word;">
                                                        {{ $item->product_name }}
                                                    </h6>
                                                    <div class="text-muted small mt-0.5">
                                                        @if($item->brand_name)<span class="badge bg-light text-dark border me-1">{{ $item->brand_name }}</span>@endif
                                                        @if($item->category_name)<span class="text-muted fs-11">{{ $item->category_name }}</span>@endif
                                                        @if($item->product_code)<span class="text-muted fs-11 ms-1">| Code: {{ $item->product_code }}</span>@endif
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-primary-subtle text-primary fs-14 px-3 py-1.5 fw-bold rounded-pill">
                                                {{ number_format($item->total_qty) }} {{ $item->unit_of_measure ?? 'Pcs' }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <span class="fw-bold text-slate-900 fs-15">₹{{ number_format($item->total_amount, 2) }}</span>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-5 text-muted">
                                            <iconify-icon icon="solar:box-minimalistic-broken" class="fs-40 text-muted mb-2"></iconify-icon><br>
                                            <h6 class="fw-bold">No Product Purchase Records Found</h6>
                                            <p class="small text-muted mb-0">No product purchases found under the selected filter.</p>
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    
                    @if(isset($productSummaries) && $productSummaries->hasPages())
                    <div class="card-footer border-top bg-white py-3">
                        {{ $productSummaries->links() }}
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
