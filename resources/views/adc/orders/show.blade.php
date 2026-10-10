@extends('layouts.adc')

@section('title', 'Order Details — ' . $order->order_number)
@section('page-title', 'Order Details')

@section('content')
<div class="row">
    <div class="col-12 mb-3 d-flex align-items-center justify-content-between">
        <a href="{{ route('adc.orders.index') }}" class="btn btn-sm btn-outline-secondary">
            <i class="bx bx-arrow-back me-1"></i> Back to Orders
        </a>
        @if(!in_array($order->status, ['Cancelled']))
            <a href="{{ route('adc.orders.invoice', $order->id) }}" target="_blank" class="btn btn-sm btn-outline-primary fw-medium">
                <i class="bx bx-file me-1"></i> Download Tax Invoice
            </a>
        @endif
    </div>
</div>

<div class="row">
    <!-- Main Order Details & Shipment Packages -->
    <div class="col-lg-8">
        <!-- Order Header Card -->
        <div class="card mb-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <div>
                    <h5 class="card-title mb-1">Order #{{ $order->order_number }}</h5>
                    <p class="text-muted small mb-0">Placed on {{ $order->created_at->format('d M Y, h:i A') }}</p>
                </div>
                <div class="text-end">
                    <span class="badge {{ $order->payment_status === 'Paid' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} fs-13 px-3 py-1 mb-1">
                        Payment: {{ $order->payment_status }}
                    </span><br>
                    <span class="badge {{ $order->status === 'Dispatched' ? 'bg-success-subtle text-success' : ($order->status === 'Partially Dispatched' ? 'bg-info-subtle text-info' : 'bg-primary-subtle text-primary') }} fs-12 px-2.5 py-0.5">
                        Status: {{ $order->status }}
                    </span>
                    @if($order->received_invoice_file)
                        <div class="mt-2">
                            <a href="{{ \Illuminate\Support\Str::startsWith($order->received_invoice_file, ['http://', 'https://']) ? $order->received_invoice_file : asset('storage/' . $order->received_invoice_file) }}" target="_blank" class="btn btn-xs btn-outline-primary py-1 px-2">
                                <i class="bx bx-download me-1"></i> Download Received Invoice
                            </a>
                        </div>
                    @endif
                </div>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-sm-4">
                        <span class="text-muted small d-block">Seller</span>
                        <strong class="text-dark">
                            <i class="bx bx-store me-1 text-primary"></i>{{ $order->seller_name_display }}
                        </strong>
                    </div>
                    <div class="col-sm-4">
                        <span class="text-muted small d-block">Payment Method</span>
                        <strong class="text-dark">{{ $order->payment_method ?? 'Wallet' }}</strong>
                    </div>
                    <div class="col-sm-4">
                        <span class="text-muted small d-block">Total Packages</span>
                        <strong class="text-dark">
                            {{ ($order->items->where('fulfillment_type', 'physical')->count() > 0 ? 1 : 0) + $order->items->where('fulfillment_type', 'virtual')->groupBy('purchase_order_id')->count() }} Package(s)
                        </strong>
                    </div>
                </div>
            </div>
        </div>

        <!-- Transport Invoice Upload Alert / Status Card -->
        @if($order->received_invoice_number && $order->received_invoice_file)
        <div class="card mb-4 border-start border-4 border-success shadow-sm">
            <div class="card-body py-2.5 px-3 d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-success fw-bold small"><i class="bx bx-check-circle me-1"></i> Received Transport Invoice Uploaded & Verified</span>
                    <span class="text-muted small ms-3">No: <strong>#{{ $order->received_invoice_number }}</strong> | Date: <strong>{{ $order->received_invoice_date ? $order->received_invoice_date->format('d M Y') : '—' }}</strong></span>
                </div>
                <div>
                    <a href="{{ \Illuminate\Support\Str::startsWith($order->received_invoice_file, ['http://', 'https://']) ? $order->received_invoice_file : asset('storage/' . $order->received_invoice_file) }}" target="_blank" class="btn btn-xs btn-outline-primary me-1">
                        <i class="bx bx-download me-1"></i> Download Copy
                    </a>
                </div>
            </div>
        </div>
        @elseif(in_array($order->status, ['Dispatched', 'Partially Dispatched', 'Received', 'Delivered']))
        <div class="card mb-4 border-start border-4 border-warning shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bx bx-upload me-1 text-warning fs-18 align-middle"></i> Upload Received Transport Invoice
                    </h6>
                    <span class="badge bg-secondary-subtle text-secondary fs-11">Optional Upload</span>
                </div>
                <p class="text-muted small mb-3">You can optionally upload the received transport/supplier invoice copy for record-keeping. Official GST Tax Invoice is available anytime.</p>
                <form action="{{ route('adc.orders.upload-invoice', $order->id) }}" method="POST" enctype="multipart/form-data" class="bg-light p-3 rounded border">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-dark">Received Invoice Number <span class="text-danger">*</span></label>
                            <input type="text" name="received_invoice_number" class="form-control form-control-sm" placeholder="e.g. INV-2026-001" value="{{ old('received_invoice_number', $order->received_invoice_number) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-dark">Received Invoice Date <span class="text-danger">*</span></label>
                            <input type="date" name="received_invoice_date" class="form-control form-control-sm" value="{{ old('received_invoice_date', $order->received_invoice_date ? $order->received_invoice_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-dark">Transport Invoice Copy <span class="text-danger">*</span></label>
                            <input type="file" name="received_invoice_file" class="form-control form-control-sm" accept=".pdf,.png,.jpg,.jpeg" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-sm btn-primary w-100 py-1.5 fw-semibold">
                                <i class="bx bx-upload me-1"></i> Upload Invoice
                            </button>
                        </div>
                    </div>
                </form>
        </div>
        </div>
        @endif

        @php
            $stockedInItems = $order->items->where('is_stocked_in', true);
            $hasDiscrepancy = $stockedInItems->contains(function($i) { return ($i->shortage_qty > 0 || $i->damage_qty > 0); });
            $hasExcess = $stockedInItems->contains(function($i) { return ($i->excess_qty > 0); });

            $existingDebitNote = \App\Models\AdcDebitNote::where('adc_order_id', $order->id)->where('discrepancy_type', '!=', 'EXCESS')->first();
            $existingExcessNote = \App\Models\AdcExcessInvoice::where('adc_order_id', $order->id)->first();

            $targetLabel = 'Supplier';
            if ($order->fulfillment_type === 'physical' || !empty($order->selected_ddc_id) || !empty($order->assigned_ddc_id)) {
                $targetLabel = 'DDC';
            } else {
                $targetLabel = 'Seller';
            }
        @endphp

        @if($hasDiscrepancy || $hasExcess)
        <div class="card mb-4 border-start border-4 border-warning shadow-sm">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h6 class="fw-bold text-dark mb-1">
                            <i class="bx bx-shield-quarter text-warning fs-18 align-middle me-1"></i> Stock Discrepancies Recorded
                        </h6>
                        <p class="text-muted small mb-0">
                            Shortage / Damage / Excess stock recorded for this order. You can generate Debit Notes routed directly to your <strong>{{ $targetLabel }}</strong>.
                        </p>
                    </div>
                    <div class="d-flex gap-2 flex-wrap">
                        @if($hasDiscrepancy)
                            @if($existingDebitNote)
                                <a href="{{ route('adc.debit-notes.show', $existingDebitNote->id) }}" class="btn btn-sm btn-outline-warning fw-semibold">
                                    <i class="bx bx-file me-1"></i> View Debit Note #{{ $existingDebitNote->debit_note_number }}
                                </a>
                            @else
                                <a href="{{ route('adc.debit-notes.create', $order->id) }}" class="btn btn-sm btn-danger fw-bold">
                                    <i class="bx bx-error me-1"></i> Generate Consolidated Debit Note
                                </a>
                            @endif
                        @endif

                        @if($hasExcess)
                            @if($existingExcessNote)
                                <a href="{{ route('adc.excess-invoices.show', $existingExcessNote->id) }}" class="btn btn-sm btn-outline-primary fw-semibold">
                                    <i class="bx bx-file me-1"></i> View Excess Invoice #{{ $existingExcessNote->invoice_number }}
                                </a>
                            @else
                                <a href="{{ route('adc.excess-invoices.create', $order->id) }}" class="btn btn-sm btn-primary fw-bold">
                                    <i class="bx bx-plus-circle me-1"></i> Generate Excess Stock Invoice
                                </a>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @endif

        @php
            $physicalItems = $order->items->where('fulfillment_type', 'physical');
            $virtualGrouped = $order->items->where('fulfillment_type', 'virtual')->groupBy('purchase_order_id');

            $rejectedOrderItems = $order->items->filter(function($item) {
                if (in_array($item->status, ['Rejected', 'Cancelled'])) return true;
                if ($item->purchaseOrder && in_array($item->purchaseOrder->status, ['Cancelled', 'Rejected'])) return true;
                if ($item->purchaseOrder) {
                    $poItem = $item->purchaseOrder->items->firstWhere('product_id', $item->product_id);
                    if ($poItem && in_array($poItem->status, ['Cancelled', 'Rejected'])) return true;
                }
                return false;
            });
            $rejectedTotal = $rejectedOrderItems->sum('total');
            $activeOrderTotal = max(0, (float)$order->total_amount - (float)$rejectedTotal);
        @endphp

        <!-- Physical DDC Package -->
        @if($physicalItems->count() > 0)
        <div class="card mb-4 border-start border-4 border-primary">
            <div class="card-header bg-light d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="card-title mb-0 text-primary">
                        <i class="bx bx-store me-1"></i> Package 1: Physical DDC Inventory Fulfillment
                    </h6>
                    <span class="text-muted small">Fulfilled directly from local DDC Stock</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @php $physStatus = $physicalItems->first()?->status ?? 'Processing'; @endphp
                    @php
                        $physBadgeMap = [
                            'Pending'    => 'bg-warning text-dark',
                            'Processing' => 'bg-info text-white',
                            'Dispatched' => 'bg-primary text-white',
                            'Received'   => 'bg-success text-white',
                            'Cancelled'  => 'bg-danger text-white',
                        ];
                    @endphp
                    <span class="badge {{ $physBadgeMap[$physStatus] ?? 'bg-secondary' }}">
                        {{ $physStatus === 'Received' ? 'Stocked In & Received' : $physStatus }}
                    </span>
                    @if($physStatus === 'Dispatched')
                        <a href="{{ route('adc.orders.receive.show', $order->id) }}" class="btn btn-xs btn-success fw-semibold px-2.5 py-1">
                            <i class="bx bx-import me-1"></i> Stock In / Receive Items
                        </a>
                    @endif
                </div>
            </div>
            @php
                $physDdc = $order->selectedDdc ?? $order->assignedDdc ?? $order->virtualDdc;
                $physDdcProfile = $physDdc?->ddcProfile ?? $physDdc?->profile ?? null;
                $physDdcName = $physDdcProfile?->company_name ?? ($physDdcProfile?->trade_name ?? ($physDdc?->name ?? 'DDC Warehouse'));
                $physDdcAddress = $physDdcProfile?->address ?? ($physDdcProfile?->ddc_address ?? null);
                $physDdcDistrict = $physDdcProfile?->district ?? ($physDdc?->district ?? null);
                $physDdcState = $physDdcProfile?->state ?? ($physDdc?->state ?? null);
                $physDdcPincode = $physDdcProfile?->pincode ?? ($physDdc?->pincode ?? null);
                $physDdcPhone = $physDdcProfile?->official_phone ?? ($physDdcProfile?->phone ?? ($physDdc?->mobile ?? null));
            @endphp
            <div class="card-body bg-light-subtle border-bottom py-2.5 px-3">
                <div class="d-flex align-items-start small">
                    <i class="bx bx-map-pin text-primary fs-16 me-2 mt-0.5"></i>
                    <div>
                        <span class="text-muted small fw-semibold text-uppercase">Dispatch From:</span>
                        <span class="fw-bold text-dark fs-13 ms-1">{{ $physDdcName }} (DDC Warehouse)</span>
                        @if($physDdcAddress)
                            <div class="text-muted small mt-0.5">
                                {{ $physDdcAddress }}@if($physDdcDistrict || $physDdcState || $physDdcPincode), {{ $physDdcDistrict }}{{ ($physDdcDistrict && $physDdcState) ? ', ' : '' }}{{ $physDdcState }}{{ $physDdcPincode ? ' - ' . $physDdcPincode : '' }}@endif
                                @if($physDdcPhone)
                                    <span class="ms-2 text-dark"><i class="bx bx-phone me-0.5 text-muted"></i>{{ $physDdcPhone }}</span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($physicalItems as $item)
                            @php
                                $isPhysRejected = in_array($item->status, ['Rejected', 'Cancelled']);
                            @endphp
                            <tr class="{{ $isPhysRejected ? 'table-danger bg-danger bg-opacity-10' : '' }}">
                                <td>
                                    <h6 class="mb-0 fs-14 fw-semibold {{ $isPhysRejected ? 'text-decoration-line-through text-muted' : 'text-dark' }}">{{ $item->product_name ?? 'Product' }}</h6>
                                    <span class="text-muted small">Code: {{ $item->product_code }}</span>
                                    @if($isPhysRejected)
                                        <br>
                                        <span class="badge bg-danger text-white fs-11 mt-1">
                                            <i class="bx bx-x-circle me-0.5"></i> Cancelled / Rejected (Refunded)
                                        </span>
                                        @if($item->remarks)
                                            <span class="text-danger small ms-1 fst-italic">Reason: {{ $item->remarks }}</span>
                                        @endif
                                    @elseif($item->is_stocked_in)
                                        <br><span class="badge bg-success-subtle text-success fs-11">Stocked In (Received MRP: ₹{{ number_format($item->received_mrp, 2) }})</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold {{ $isPhysRejected ? 'text-danger' : '' }}">{{ $item->quantity }}</td>
                                <td class="text-end {{ $isPhysRejected ? 'text-muted' : '' }}">₹{{ number_format($item->price, 2) }}</td>
                                <td class="text-end fw-bold {{ $isPhysRejected ? 'text-danger' : 'text-primary' }}">
                                    @if($isPhysRejected)
                                        <span class="text-decoration-line-through me-1 text-muted">₹{{ number_format($item->total, 2) }}</span>
                                        <span class="badge bg-danger-subtle text-danger">Refunded</span>
                                    @else
                                        ₹{{ number_format($item->total, 2) }}
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endif

        <!-- Virtual Seller Packages -->
        @foreach($virtualGrouped as $poId => $itemsList)
        @php
            $po = $itemsList->first()?->purchaseOrder;
            $seller = $itemsList->first()?->seller;
            $pkgNum = ($physicalItems->count() > 0 ? 1 : 0) + $loop->iteration;
            $itemStatus = $itemsList->first()?->status ?? ($po?->status ?? 'Pending');
            $poBadgeMap = [
                'Pending'    => 'bg-warning text-dark',
                'Accepted'   => 'bg-info text-white',
                'Processing' => 'bg-info text-white',
                'Dispatched' => 'bg-primary text-white',
                'Received'   => 'bg-success text-white',
                'Rejected'   => 'bg-danger text-white',
            ];
            $pickupStore = $po?->pickupStore;
            $dispatchName = $pickupStore?->pickup_store_name ?? ($seller?->sellerProfile?->company_name ?? ($seller?->name ?? 'N/A'));
            $dispatchCode = $pickupStore?->pickup_store_code;
            $dispatchAddress = $pickupStore?->address ?? ($seller?->sellerProfile?->address ?? null);
            $dispatchDistrict = $pickupStore?->districtRelation?->name ?? ($pickupStore?->district ?? ($seller?->sellerProfile?->district ?? null));
            $dispatchState = $pickupStore?->stateRelation?->name ?? ($pickupStore?->state ?? ($seller?->sellerProfile?->state ?? null));
            $dispatchPincode = $pickupStore?->pincode ?? ($seller?->sellerProfile?->pincode ?? null);
            $dispatchPhone = $pickupStore?->mobile ?? ($seller?->sellerProfile?->alternate_mobile ?? ($seller?->mobile ?? null));

            $activeItemsInPkg = $itemsList->reject(function($i) {
                return in_array($i->status, ['Rejected', 'Cancelled'])
                    || ($i->purchaseOrder && in_array($i->purchaseOrder->status, ['Cancelled', 'Rejected']))
                    || ($i->purchaseOrder && $i->purchaseOrder->items->firstWhere('product_id', $i->product_id)?->status === 'Rejected');
            });
            $rejectedItemsInPkg = $itemsList->filter(function($i) {
                return in_array($i->status, ['Rejected', 'Cancelled'])
                    || ($i->purchaseOrder && in_array($i->purchaseOrder->status, ['Cancelled', 'Rejected']))
                    || ($i->purchaseOrder && $i->purchaseOrder->items->firstWhere('product_id', $i->product_id)?->status === 'Rejected');
            });
        @endphp
        <div class="card mb-4 border-start border-4 border-info">
            <div class="card-header bg-light d-flex align-items-center justify-content-between">
                <div>
                    <h6 class="card-title mb-0 text-info">
                        <i class="bx bx-package me-1"></i> Package {{ $pkgNum }}: Direct Delivery
                    </h6>
                    <span class="text-muted small">Seller: <strong>{{ $seller?->company_name ?: $seller?->name }}</strong></span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    @if($activeItemsInPkg->isEmpty())
                        <span class="badge bg-danger text-white">All Items Rejected</span>
                    @else
                        <span class="badge {{ $poBadgeMap[$itemStatus] ?? 'bg-secondary' }}">
                            {{ $itemStatus === 'Received' ? 'Stocked In & Received' : $itemStatus }}
                        </span>
                        @if($rejectedItemsInPkg->isNotEmpty())
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle fs-11">
                                {{ $rejectedItemsInPkg->count() }} Item(s) Rejected &amp; Refunded
                            </span>
                        @endif
                    @endif
                    @if(($itemStatus === 'Dispatched' || $po?->status === 'Dispatched') && $itemStatus !== 'Received' && $activeItemsInPkg->isNotEmpty())
                        <a href="{{ route('adc.orders.receive.show', $order->id) }}" class="btn btn-xs btn-success fw-semibold px-2.5 py-1">
                            <i class="bx bx-import me-1"></i> Stock In / Receive Items
                        </a>
                    @endif
                </div>
            </div>
            <div class="card-body bg-light-subtle border-bottom py-2.5 px-3">
                <div class="row g-2 align-items-center">
                    <div class="col-md-7">
                        <div class="d-flex align-items-start">
                            <i class="bx bx-map-pin text-primary fs-16 me-2 mt-0.5"></i>
                            <div>
                                <span class="text-muted small fw-semibold text-uppercase">Dispatch From:</span>
                                <span class="fw-bold text-dark fs-13 ms-1">{{ $dispatchName }}</span>
                                @if($dispatchCode)
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace ms-1 fs-11">{{ $dispatchCode }}</span>
                                @endif
                                @if($dispatchAddress)
                                    <div class="text-muted small mt-0.5">
                                        {{ $dispatchAddress }}@if($dispatchDistrict || $dispatchState || $dispatchPincode), {{ $dispatchDistrict }}{{ ($dispatchDistrict && $dispatchState) ? ', ' : '' }}{{ $dispatchState }}{{ $dispatchPincode ? ' - ' . $dispatchPincode : '' }}@endif
                                        @if($dispatchPhone)
                                            <span class="ms-2 text-dark"><i class="bx bx-phone me-0.5 text-muted"></i>{{ $dispatchPhone }}</span>
                                        @endif
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="col-md-5 text-md-end">
                        @if($po && $po->dispatch_status === 'Dispatched')
                            <div class="small text-muted mb-1">
                                <span><strong>Courier:</strong> {{ $po->invoice?->transporter_name ?? 'N/A' }}</span>
                                <span class="ms-2"><strong>Tracking:</strong> {{ $po->invoice?->eway_bill_number ?? 'N/A' }}</span>
                            </div>
                        @endif
                        <div class="mt-1">
                            @if($po?->invoice?->ddc_invoice_file)
                                <a href="{{ \Illuminate\Support\Str::startsWith($po->invoice->ddc_invoice_file, ['http://', 'https://']) ? $po->invoice->ddc_invoice_file : asset('storage/' . $po->invoice->ddc_invoice_file) }}" target="_blank" class="btn btn-xs btn-outline-primary">
                                    <i class="bx bx-download me-1"></i> Download DDC Invoice
                                </a>
                            @else
                                <a href="{{ route('adc.orders.invoice', $order->id) }}" target="_blank" class="btn btn-xs btn-outline-primary">
                                    <i class="bx bx-file me-1"></i> Download Tax Invoice
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Product</th>
                                <th class="text-center">Qty</th>
                                <th class="text-end">Price</th>
                                <th class="text-end">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($itemsList as $item)
                            @php
                                $isItemRejected = in_array($item->status, ['Rejected', 'Cancelled'])
                                    || ($item->purchaseOrder && in_array($item->purchaseOrder->status, ['Cancelled', 'Rejected']))
                                    || ($item->purchaseOrder && $item->purchaseOrder->items->firstWhere('product_id', $item->product_id)?->status === 'Rejected');
                                $poItem = $item->purchaseOrder?->items->firstWhere('product_id', $item->product_id);
                                $itemRemarks = $item->remarks ?: ($poItem?->remarks ?: null);
                            @endphp
                            <tr class="{{ $isItemRejected ? 'table-danger bg-danger bg-opacity-10' : '' }}">
                                <td>
                                    <h6 class="mb-0 fs-14 fw-semibold {{ $isItemRejected ? 'text-decoration-line-through text-muted' : 'text-dark' }}">{{ $item->product_name ?? 'Product' }}</h6>
                                    <span class="text-muted small">Code: {{ $item->product_code }}</span>
                                    @if($isItemRejected)
                                        <br>
                                        <span class="badge bg-danger text-white fs-11 mt-1">
                                            <i class="bx bx-x-circle me-0.5"></i> Rejected by Seller (Refunded to Wallet)
                                        </span>
                                        @if($itemRemarks)
                                            <span class="text-danger small ms-1 fst-italic">Reason: {{ $itemRemarks }}</span>
                                        @endif
                                    @elseif($item->is_stocked_in)
                                        <br><span class="badge bg-success-subtle text-success fs-11">Stocked In (Received MRP: ₹{{ number_format($item->received_mrp, 2) }})</span>
                                    @endif
                                </td>
                                <td class="text-center fw-bold {{ $isItemRejected ? 'text-danger' : '' }}">{{ $item->quantity }}</td>
                                <td class="text-end {{ $isItemRejected ? 'text-muted' : '' }}">₹{{ number_format($item->price, 2) }}</td>
                                <td class="text-end fw-bold {{ $isItemRejected ? 'text-danger' : 'text-primary' }}">
                                    @if($isItemRejected)
                                        <span class="text-decoration-line-through me-1 text-muted">₹{{ number_format($item->total, 2) }}</span>
                                        <span class="badge bg-danger-subtle text-danger">Refunded</span>
                                    @else
                                        ₹{{ number_format($item->total, 2) }}
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        @endforeach

    </div>

    <!-- Sidebar Info (Shipping & Order Summary) -->
    <div class="col-lg-4">
        <!-- Summary Card -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Order Summary</h5>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Seller:</span>
                    <strong class="text-dark">{{ $order->seller_name_display }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total Ordered Items:</span>
                    <strong>{{ $order->items->count() }} items</strong>
                </div>
                @if($rejectedOrderItems->isNotEmpty())
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Accepted Active Items:</span>
                    <strong class="text-success">{{ $order->items->count() - $rejectedOrderItems->count() }} items</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Original Total:</span>
                    <span class="text-muted text-decoration-line-through">₹{{ number_format($order->total_amount, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between mb-2 text-danger">
                    <span>Less: Refunded to Wallet:</span>
                    <span class="fw-bold">-₹{{ number_format($rejectedTotal, 2) }}</span>
                </div>
                <hr>
                <div class="d-flex justify-content-between fs-16 fw-bold">
                    <span>Net Active Total:</span>
                    <span class="text-success">₹{{ number_format($activeOrderTotal, 2) }}</span>
                </div>
                <div class="mt-2 text-center">
                    <span class="badge bg-danger-subtle text-danger small px-2 py-1">
                        ₹{{ number_format($rejectedTotal, 2) }} refunded to wallet ({{ $rejectedOrderItems->count() }} rejected)
                    </span>
                </div>
                @else
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Subtotal (Excl. GST):</span>
                    <strong>₹{{ number_format($order->subtotal_excl_gst, 2) }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-2">
                    <span class="text-muted">Total GST:</span>
                    <strong>₹{{ number_format($order->total_gst, 2) }}</strong>
                </div>
                <hr>
                <div class="d-flex justify-content-between fs-16 fw-bold">
                    <span>Grand Total:</span>
                    <span class="text-primary">₹{{ number_format($order->total_amount, 2) }}</span>
                </div>
                @endif
            </div>
        </div>

        <!-- Shipping Address -->
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="card-title mb-0">Shipping & Delivery</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-1"><i class="bx bx-map me-1 text-primary"></i> Delivery Address (Ship To):</p>
                <p class="fw-medium text-dark mb-3">{{ $order->shipping_address }}</p>

                @if($order->delivery_pincode)
                <p class="text-muted small mb-1">Pincode / Location:</p>
                <p class="fw-medium text-dark mb-0">{{ $order->delivery_pincode }} ({{ $order->delivery_district ?? '' }}, {{ $order->delivery_state ?? '' }})</p>
                @endif

                @php
                    $firstStore = $order->purchaseOrders->first()?->pickupStore;
                    $firstStoreSeller = $order->purchaseOrders->first()?->seller;
                @endphp
                @if($firstStore || $firstStoreSeller)
                <hr>
                <p class="text-muted small mb-1"><i class="bx bx-paper-plane me-1 text-success"></i> Dispatch From (Origin):</p>
                <p class="fw-semibold text-dark mb-0">
                    {{ $firstStore?->pickup_store_name ?? ($firstStoreSeller?->sellerProfile?->company_name ?? ($firstStoreSeller?->name ?? 'N/A')) }}
                    @if($firstStore?->pickup_store_code)
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle font-monospace ms-1 fs-11">{{ $firstStore->pickup_store_code }}</span>
                    @endif
                </p>
                <p class="text-muted small mb-0">
                    {{ $firstStore?->address ?? ($firstStoreSeller?->sellerProfile?->address ?? '') }}
                    @if($firstStore?->district || $firstStore?->state || $firstStore?->pincode)
                        <br>{{ $firstStore->districtRelation?->name ?? $firstStore->district }}, {{ $firstStore->stateRelation?->name ?? $firstStore->state }} - {{ $firstStore->pincode }}
                    @endif
                </p>
                @endif

                @if($order->notes)
                <hr>
                <p class="text-muted small mb-1">Order Notes:</p>
                <p class="text-dark small mb-0">{{ $order->notes }}</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
