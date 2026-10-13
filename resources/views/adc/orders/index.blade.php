@extends('layouts.adc')

@section('title', 'My Orders')
@section('page-title', 'My Orders')

@section('content')
    <!-- Header Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-8">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                My Orders & Purchase History
                            </h4>
                            <p class="text-muted small mb-0">Track all central MSME orders placed by your canteen, view order statuses, and receive stock.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

<div class="row">
    <div class="col-12">
        <div class="card shadow-sm border-0 rounded-3">
            <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                <h5 class="card-title mb-0">Order History</h5>
                <form action="{{ route('adc.orders.index') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2" id="filterForm">
                    <div class="input-group input-group-sm" style="width: 250px;">
                        <input type="text" name="search" class="form-control" placeholder="Search Order # or Address..." value="{{ request('search') }}">
                        <button class="btn btn-outline-secondary" type="submit">
                            <iconify-icon icon="solar:magnifer-linear"></iconify-icon>
                        </button>
                    </div>

                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()" style="width: 140px;">
                        <option value="">All Statuses</option>
                        <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                        <option value="Processing" {{ request('status') == 'Processing' ? 'selected' : '' }}>Processing</option>
                        <option value="Dispatched" {{ request('status') == 'Dispatched' ? 'selected' : '' }}>Dispatched</option>
                        <option value="Received" {{ request('status') == 'Received' ? 'selected' : '' }}>Received</option>
                        <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>

                    @if(request()->hasAny(['search', 'status']))
                        <a href="{{ route('adc.orders.index') }}" class="btn btn-sm btn-light" title="Reset Filters">
                            <i class="bx bx-reset"></i>
                        </a>
                    @endif
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Order #</th>
                                <th>Date</th>
                                <th>Seller</th>
                                <th>Items</th>
                                <th>Total Amount</th>
                                <th>Payment</th>
                                <th>Status</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($orders as $order)
                            <tr>
                                <td class="fw-bold">
                                    <a href="{{ route('adc.orders.show', $order->id) }}" class="text-primary">
                                        {{ $order->order_number }}
                                    </a>
                                </td>
                                <td>{{ $order->created_at->format('d M Y, h:i A') }}</td>
                                <td>
                                    @php
                                        $sellerNames = $order->seller_names;
                                    @endphp
                                    @if(!empty($sellerNames))
                                        @foreach($sellerNames as $sName)
                                            <div class="fw-semibold text-slate-800" style="font-size: 12.5px;">
                                                <iconify-icon icon="solar:shop-bold-duotone" class="align-middle me-1 text-primary"></iconify-icon>{{ $sName }}
                                            </div>
                                        @endforeach
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill">
                                        {{ $order->items->count() }} {{ Str::plural('item', $order->items->count()) }}
                                    </span>
                                </td>
                                <td class="fw-bold text-dark">₹{{ number_format($order->total_amount, 2) }}</td>
                                <td>
                                    <span class="badge {{ $order->payment_status === 'Paid' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }}">
                                        {{ $order->payment_status }}
                                    </span>
                                </td>
                                <td>
                                    @php
                                        $statusBadges = [
                                            'Pending'    => 'bg-warning-subtle text-warning',
                                            'Processing' => 'bg-info-subtle text-info',
                                            'Dispatched' => 'bg-primary-subtle text-primary',
                                            'Received'   => 'bg-success-subtle text-success',
                                            'Delivered'  => 'bg-success-subtle text-success',
                                            'Cancelled'  => 'bg-danger-subtle text-danger',
                                        ];
                                    @endphp
                                    <span class="badge {{ $statusBadges[$order->status] ?? 'bg-secondary-subtle text-secondary' }}">
                                        {{ $order->status }}
                                    </span>
                                </td>
                                    @php
                                        $pendingStockCount = $order->items->where('is_stocked_in', false)->whereNotIn('status', ['Cancelled'])->count();
                                        $stockedInItems = $order->items->where('is_stocked_in', true);
                                        $hasShortageOrDamage = $stockedInItems->contains(function($i) { return ($i->shortage_qty > 0 || $i->damage_qty > 0); });
                                        $hasExcessQty = $stockedInItems->contains(function($i) { return ($i->excess_qty > 0); });
                                        $dn = \App\Models\AdcDebitNote::where('adc_order_id', $order->id)->where('discrepancy_type', '!=', 'EXCESS')->first();
                                        $ex = \App\Models\AdcExcessInvoice::where('adc_order_id', $order->id)->first();
                                    @endphp
                                    <td class="text-end text-nowrap">
                                        <a href="{{ route('adc.orders.show', $order->id) }}" class="btn btn-sm btn-primary py-1 px-2.5" title="View Order Details">
                                            <i class="bx bx-show me-1"></i> Details
                                        </a>
                                        @if(in_array($order->status, ['Dispatched', 'Partially Dispatched', 'Received', 'Delivered', 'Completed']) || $order->invoice)
                                            <a href="{{ route('adc.orders.invoice', $order->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-1 px-2.5 ms-1" title="Download Tax Invoice">
                                                <i class="bx bx-file me-1"></i> Invoice
                                            </a>
                                        @endif
                                        @if(in_array($order->status, ['Dispatched', 'Partially Dispatched']) && $pendingStockCount > 0)
                                            <a href="{{ route('adc.orders.receive.show', $order->id) }}" class="btn btn-sm btn-outline-success py-1 px-2.5 ms-1" title="Stock In Items">
                                                <i class="bx bx-import me-1"></i> Stock In
                                            </a>
                                        @endif
                                        @if($hasShortageOrDamage)
                                            @if($dn)
                                                <a href="{{ route('adc.debit-notes.show', $dn->id) }}" class="btn btn-sm btn-outline-warning py-1 px-2.5 ms-1" title="View Debit Note #{{ $dn->debit_note_number }}">
                                                    <i class="bx bx-file me-1"></i> Debit Note
                                                </a>
                                            @else
                                                <a href="{{ route('adc.debit-notes.create', $order->id) }}" class="btn btn-sm btn-danger py-1 px-2.5 ms-1 fw-bold" title="Generate Consolidated Debit Note">
                                                    <i class="bx bx-error me-1"></i> Claim Debit Note
                                                </a>
                                            @endif
                                        @endif
                                        @if($hasExcessQty)
                                            @if($ex)
                                                <a href="{{ route('adc.excess-invoices.show', $ex->id) }}" class="btn btn-sm btn-outline-primary py-1 px-2.5 ms-1" title="View Excess Invoice #{{ $ex->invoice_number }}">
                                                    <i class="bx bx-file me-1"></i> Excess Inv
                                                </a>
                                            @else
                                                <a href="{{ route('adc.excess-invoices.create', $order->id) }}" class="btn btn-sm btn-primary py-1 px-2.5 ms-1 fw-bold" title="Generate Excess Stock Invoice">
                                                    <i class="bx bx-plus-circle me-1"></i> Claim Excess
                                                </a>
                                            @endif
                                        @endif
                                    </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <iconify-icon icon="solar:bag-smile-broken" class="fs-36 text-muted mb-2"></iconify-icon><br>
                                    No orders found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($orders->hasPages())
            <div class="card-footer border-top">
                {{ $orders->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
