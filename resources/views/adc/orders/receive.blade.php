@extends('layouts.adc')

@section('title', 'Receive Stock')
@section('page-title', 'Stock Receive')

@section('content')
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row align-items-center g-3">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <a href="{{ route('adc.orders.show', $order->id) }}" class="btn btn-sm btn-outline-secondary py-1 px-2.5">
                                    <iconify-icon icon="solar:arrow-left-bold" class="align-middle me-1"></iconify-icon> Back to Order
                                </a>
                                <a href="{{ route('adc.orders.invoice', $order->id) }}" target="_blank" class="btn btn-sm btn-outline-primary py-1 px-2.5">
                                    <iconify-icon icon="solar:document-text-bold" class="align-middle me-1"></iconify-icon> Download Tax Invoice
                                </a>
                                <span class="badge bg-primary-subtle text-primary px-2.5 py-1.5 fs-12">Order Stock Receipt</span>
                            </div>
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                Receive Stock for Order #{{ $order->order_number }}
                            </h4>
                            <p class="text-muted small mb-0">
                                Shipping Address: <strong class="text-dark">{{ $order->shipping_address }}</strong>
                                @if($order->delivery_pincode)
                                    | Location: <strong class="text-dark">{{ $order->delivery_pincode }} ({{ $order->delivery_district ?? '' }}, {{ $order->delivery_state ?? '' }})</strong>
                                @endif
                            </p>
                        </div>
                        <div class="col-md-4 text-md-end" id="order-status-badge">
                            <span class="fs-14 text-muted">Order Status:</span>
                            @if($order->status === 'Pending')
                                <span class="badge bg-warning-subtle text-warning px-2.5 py-1.5 fs-13">Pending</span>
                            @elseif($order->status === 'Processing')
                                <span class="badge bg-info-subtle text-info px-2.5 py-1.5 fs-13">Processing</span>
                            @elseif($order->status === 'Dispatched')
                                <span class="badge bg-primary-subtle text-primary px-2.5 py-1.5 fs-13">Dispatched</span>
                            @elseif($order->status === 'Received')
                                <span class="badge bg-success text-white px-2.5 py-1.5 fs-13">Received</span>
                            @else
                                <span class="badge bg-light text-muted px-2.5 py-1.5 fs-13">{{ $order->status }}</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Received Invoice Upload / Details Card -->
    @if(($order->received_invoice_number && $order->received_invoice_file) || in_array($order->status, ['Dispatched', 'Partially Dispatched', 'Received', 'Delivered']))
    <div class="card border-0 shadow-sm mb-4" style="border-left: 4px solid #4f46e5 !important;">
        <div class="card-body">
            @if($order->received_invoice_number && $order->received_invoice_file)
                <!-- Saved Invoice Details & Download Option -->
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold text-slate-800 mb-1 d-flex align-items-center gap-2">
                            <iconify-icon icon="solar:check-circle-bold-duotone" class="text-success fs-4"></iconify-icon>
                            Received Invoice Uploaded & Verified
                        </h6>
                        <div class="small text-muted">
                            Invoice No: <strong class="text-slate-800 me-3">#{{ $order->received_invoice_number }}</strong>
                            Invoice Date: <strong class="text-slate-800 me-3">{{ $order->received_invoice_date ? $order->received_invoice_date->format('d M Y') : '—' }}</strong>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="{{ \Illuminate\Support\Str::startsWith($order->received_invoice_file, ['http://', 'https://']) ? $order->received_invoice_file : asset('storage/' . $order->received_invoice_file) }}" target="_blank" class="btn btn-sm btn-outline-primary py-1.5 px-3">
                            <iconify-icon icon="solar:download-bold" class="align-middle me-1"></iconify-icon> Download Received Invoice
                        </a>
                        <button type="button" class="btn btn-sm btn-light py-1.5 px-2" data-bs-toggle="collapse" data-bs-target="#edit-invoice-box">
                            <iconify-icon icon="solar:pen-bold" class="align-middle"></iconify-icon> Edit
                        </button>
                    </div>
                </div>

                <!-- Collapsible edit form -->
                <div class="collapse mt-3" id="edit-invoice-box">
                    <form action="{{ route('adc.orders.upload-invoice', $order->id) }}" method="POST" enctype="multipart/form-data" class="bg-light p-3 rounded border">
                        @csrf
                        <div class="row g-3 align-items-end">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-slate-700">Invoice Number <span class="text-danger">*</span></label>
                                <input type="text" name="received_invoice_number" class="form-control form-control-sm" value="{{ old('received_invoice_number', $order->received_invoice_number) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold text-slate-700">Invoice Date <span class="text-danger">*</span></label>
                                <input type="date" name="received_invoice_date" class="form-control form-control-sm" value="{{ old('received_invoice_date', $order->received_invoice_date ? $order->received_invoice_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label small fw-semibold text-slate-700">Update Copy (Optional)</label>
                                <input type="file" name="received_invoice_file" class="form-control form-control-sm" accept=".pdf,.png,.jpg,.jpeg">
                            </div>
                            <div class="col-md-2">
                                <button type="submit" class="btn btn-sm btn-primary w-100 py-1.5 fw-semibold">Update Invoice</button>
                            </div>
                        </div>
                    </form>
                </div>
            @else
                <!-- Alert & Upload Form -->
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h5 class="fw-bold text-slate-800 mb-1 d-flex align-items-center gap-2">
                            <iconify-icon icon="solar:document-bold-duotone" class="text-primary fs-4"></iconify-icon>
                            Upload Received Transport Invoice (Optional Record)
                        </h5>
                        <p class="text-muted small mb-0">You can optionally enter the Transport/Supplier Invoice Number, Date, and upload a copy for record-keeping.</p>
                    </div>
                </div>

                <form action="{{ route('adc.orders.upload-invoice', $order->id) }}" method="POST" enctype="multipart/form-data" class="bg-light p-3 rounded border">
                    @csrf
                    <div class="row g-3 align-items-end">
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-slate-700">Received Invoice Number <span class="text-danger">*</span></label>
                            <input type="text" name="received_invoice_number" class="form-control form-control-sm" placeholder="e.g. INV-2026-001" value="{{ old('received_invoice_number', $order->received_invoice_number) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-slate-700">Received Invoice Date <span class="text-danger">*</span></label>
                            <input type="date" name="received_invoice_date" class="form-control form-control-sm" value="{{ old('received_invoice_date', $order->received_invoice_date ? $order->received_invoice_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-slate-700">Invoice Copy (PDF or Image) <span class="text-danger">*</span></label>
                            <input type="file" name="received_invoice_file" class="form-control form-control-sm" accept=".pdf,.png,.jpg,.jpeg" required>
                        </div>
                        <div class="col-md-2">
                            <button type="submit" class="btn btn-sm btn-primary w-100 py-1.5 fw-semibold">
                                <iconify-icon icon="solar:upload-bold-duotone" class="align-middle me-1"></iconify-icon> Upload Invoice
                            </button>
                        </div>
                    </div>
                </form>
            @endif
        </div>
    </div>
    @endif

    @php
        $hasUploadedInvoice = !empty($order->received_invoice_number) && !empty($order->received_invoice_file);
    @endphp

    <!-- Form Definitions (Hidden) -->
    @foreach($items as $item)
        @if(!$item->is_stocked_in)
            <form id="stock-in-form-{{ $item->id }}" action="{{ route('adc.orders.items.stock-in', $item->id) }}" method="POST" data-price="{{ (float)$item->price }}" style="display: none;">
                @csrf
                <input type="hidden" name="supply_qty" value="{{ $item->quantity }}">
            </form>
        @endif
    @endforeach

    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="fw-bold text-slate-800 mb-0">Dispatched Products List</h6>
            <span class="text-muted small">Update received details and stock-in each product below to add to your ADC Inventory.</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" style="min-width: 1200px;">
                    <thead class="bg-light bg-opacity-50 border-top border-bottom">
                        <tr>
                            <th class="ps-3" style="width: 220px;">Product Details</th>
                            <th style="width: 110px;">Code & Barcode</th>
                            <th style="width: 110px;">Pack Size & Tax</th>
                            <th style="width: 130px;">Price & MRP</th>
                            <th class="text-center" style="width: 90px;">Demand & Supply</th>
                            <th class="text-center" style="width: 90px;">Damage Qty</th>
                            <th class="text-center" style="width: 90px;">Shortage Qty</th>
                            <th class="text-center" style="width: 90px;">Excess Qty</th>
                            <th style="width: 120px;">Batch No</th>
                            <th style="width: 130px;">Mfg Date</th>
                            <th style="width: 130px;">Expire Date</th>
                            <th class="pe-3 text-center" style="width: 120px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($items as $item)
                            @php
                                $ddcIdForInv = $item->ddc_id ?? $order->selected_ddc_id ?? $order->assigned_ddc_id;
                                $ddcInv = null;
                                if ($ddcIdForInv && $item->product_id) {
                                    // 1. First try to match the batch that sold/supplied this quantity
                                    $ddcInv = \App\Models\DdcInventory::where('ddc_id', $ddcIdForInv)
                                        ->where('product_id', $item->product_id)
                                        ->where('sold_quantity', '>=', $item->quantity)
                                        ->orderBy('created_at', 'desc')
                                        ->first();

                                    // 2. Next try active stock batch
                                    if (!$ddcInv) {
                                        $ddcInv = \App\Models\DdcInventory::where('ddc_id', $ddcIdForInv)
                                            ->where('product_id', $item->product_id)
                                            ->where(function($q) {
                                                $q->where('quantity', '>', 0)
                                                  ->orWhere('reserved_quantity', '>', 0);
                                            })
                                            ->orderBy('created_at', 'asc')
                                            ->first();
                                    }

                                    // 3. Fallback to latest inventory entry
                                    if (!$ddcInv) {
                                        $ddcInv = \App\Models\DdcInventory::where('ddc_id', $ddcIdForInv)
                                            ->where('product_id', $item->product_id)
                                            ->orderBy('created_at', 'desc')
                                            ->first();
                                    }
                                }

                                $itemMfgDate = $item->mfg_date ? (is_string($item->mfg_date) ? $item->mfg_date : $item->mfg_date->format('Y-m-d')) : '';
                                if (!$itemMfgDate && $ddcInv && $ddcInv->mfg_date) {
                                    $itemMfgDate = is_string($ddcInv->mfg_date) ? $ddcInv->mfg_date : $ddcInv->mfg_date->format('Y-m-d');
                                }
                                if (!$itemMfgDate && $item->purchaseOrder) {
                                    $poItem = $item->purchaseOrder->items->firstWhere('product_id', $item->product_id);
                                    if ($poItem && $poItem->mfg_date) {
                                        $itemMfgDate = is_string($poItem->mfg_date) ? $poItem->mfg_date : $poItem->mfg_date->format('Y-m-d');
                                    }
                                }

                                $itemExpDate = $item->expire_date ? (is_string($item->expire_date) ? $item->expire_date : $item->expire_date->format('Y-m-d')) : '';
                                if (!$itemExpDate && $ddcInv && $ddcInv->expire_date) {
                                    $itemExpDate = is_string($ddcInv->expire_date) ? $ddcInv->expire_date : $ddcInv->expire_date->format('Y-m-d');
                                }
                                if (!$itemExpDate && $item->purchaseOrder) {
                                    $poItem = $item->purchaseOrder->items->firstWhere('product_id', $item->product_id);
                                    if ($poItem && $poItem->expire_date) {
                                        $itemExpDate = is_string($poItem->expire_date) ? $poItem->expire_date : $poItem->expire_date->format('Y-m-d');
                                    }
                                }

                                $itemBatchNumber = $item->batch_number ?: ($ddcInv?->batch_number ?: '');
                                if (!$itemBatchNumber && $item->purchaseOrder) {
                                    $poItem = $item->purchaseOrder->items->firstWhere('product_id', $item->product_id);
                                    if ($poItem && $poItem->batch_number) {
                                        $itemBatchNumber = $poItem->batch_number;
                                    }
                                }

                                $defaultMrp = $item->received_mrp ?: ($item->mrp ?: ($ddcInv?->mrp ?: $item->product?->mrp));
                            @endphp
                            <tr id="item-row-{{ $item->id }}" class="{{ $item->is_stocked_in ? 'table-light text-muted' : '' }}">
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="border rounded bg-light overflow-hidden d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                            @if($item->product?->featured_image)
                                                <img src="{{ filter_var($item->product->featured_image, FILTER_VALIDATE_URL) ? $item->product->featured_image : asset('storage/' . $item->product->featured_image) }}" alt="img" class="img-fluid object-fit-contain" style="max-height: 100%;">
                                            @else
                                                <iconify-icon icon="solar:gallery-bold" class="text-muted fs-20"></iconify-icon>
                                            @endif
                                        </div>
                                        <div>
                                            <span class="fw-semibold text-slate-800 d-block fs-14 text-wrap" style="max-width: 200px;">
                                                {{ $item->product_name ?? $item->product?->product_name }}
                                            </span>
                                            <span class="text-muted small d-block">
                                                Brand: {{ $item->brand_name ?? $item->product?->brand?->name ?? '—' }}
                                            </span>
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="d-block fw-medium text-slate-700 small">{{ $item->product_code ?? 'N/A' }}</span>
                                    <span class="text-muted small">{{ $item->product?->barcode ?: '—' }}</span>
                                </td>
                                <td>
                                    <span class="d-block small text-slate-800">UOM: {{ $item->unit_of_measure ?: 'Unit' }}</span>
                                    <span class="badge bg-info-subtle text-info fs-10 mt-1">Tax: {{ number_format($item->gst_rate, 0) }}%</span>
                                    @if(($item->mbp_amount ?? 0) > 0 || ($item->product?->mbp_coins ?? 0) > 0)
                                        <span class="badge bg-warning-subtle text-warning fs-10 mt-1 d-block">MBP: {{ number_format($item->mbp_amount ?: $item->product?->mbp_coins, 2) }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="mb-1">
                                        <span class="text-muted small">Purch. Price:</span>
                                        <span class="fw-semibold text-slate-800 d-block">₹{{ number_format($item->price, 2) }}</span>
                                    </div>
                                    <div>
                                        <label class="text-muted small mb-0 d-block">MRP <span class="text-danger">*</span></label>
                                        @if($item->is_stocked_in)
                                            <span class="fw-bold text-success">₹{{ number_format($item->received_mrp, 2) }}</span>
                                            @php
                                                $itemPurchPrice = (float) $item->price;
                                                $itemMrp = (float) ($item->received_mrp ?: $item->mrp);
                                                $itemCsp = $itemPurchPrice + (($itemMrp - $itemPurchPrice) / 2);
                                                $itemMargin = $itemMrp > 0 ? round((($itemMrp - $itemCsp) / $itemMrp) * 100, 2) : 0;
                                            @endphp
                                            <span class="badge bg-success-subtle text-success fs-10 d-block mt-0.5">CSP: ₹{{ number_format($itemCsp, 2) }} (Margin: {{ $itemMargin }}%)</span>
                                        @else
                                            <div class="input-group input-group-sm mt-0.5" style="width: 105px;">
                                                <span class="input-group-text px-1">₹</span>
                                                <input type="number" step="0.01" min="0" 
                                                       form="stock-in-form-{{ $item->id }}" 
                                                       name="received_mrp" 
                                                       class="form-control px-1 fw-bold text-dark" 
                                                       value="{{ old('received_mrp', $defaultMrp) }}" required>
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-center">
                                    <span class="d-block small">Demand: <strong>{{ $item->quantity }}</strong></span>
                                    <span class="d-block small">Supply: <strong class="text-success">{{ $item->quantity }}</strong></span>
                                </td>
                                <td>
                                    @if($item->is_stocked_in)
                                        <span class="fw-medium {{ $item->damage_qty > 0 ? 'text-danger' : 'text-muted' }}">{{ $item->damage_qty }}</span>
                                    @else
                                        <input type="number" min="0" 
                                               form="stock-in-form-{{ $item->id }}" 
                                               name="damage_qty" 
                                               class="form-control form-control-sm text-center mx-auto" 
                                               style="width: 70px;" 
                                               value="{{ old('damage_qty', 0) }}" required>
                                    @endif
                                </td>
                                <td>
                                    @if($item->is_stocked_in)
                                        <span class="fw-medium {{ $item->shortage_qty > 0 ? 'text-warning' : 'text-muted' }}">{{ $item->shortage_qty }}</span>
                                    @else
                                        <input type="number" min="0" 
                                               form="stock-in-form-{{ $item->id }}" 
                                               name="shortage_qty" 
                                               class="form-control form-control-sm text-center mx-auto" 
                                               style="width: 70px;" 
                                               value="{{ old('shortage_qty', 0) }}" required>
                                    @endif
                                </td>
                                <td>
                                    @if($item->is_stocked_in)
                                        <span class="fw-medium {{ $item->excess_qty > 0 ? 'text-primary' : 'text-muted' }}">{{ $item->excess_qty }}</span>
                                    @else
                                        <input type="number" min="0" 
                                               form="stock-in-form-{{ $item->id }}" 
                                               name="excess_qty" 
                                               class="form-control form-control-sm text-center mx-auto" 
                                               style="width: 70px;" 
                                               value="{{ old('excess_qty', 0) }}" required>
                                    @endif
                                </td>
                                <td>
                                    @if($item->is_stocked_in)
                                        <span class="small">{{ $item->batch_number ?? '—' }}</span>
                                    @else
                                        <input type="text" 
                                               form="stock-in-form-{{ $item->id }}" 
                                               name="batch_number" 
                                               class="form-control form-control-sm px-1 small" 
                                               placeholder="Batch No"
                                               value="{{ old('batch_number', $itemBatchNumber) }}">
                                    @endif
                                </td>
                                <td>
                                    @if($item->is_stocked_in)
                                        <span class="small">{{ $item->mfg_date ? (is_string($item->mfg_date) ? $item->mfg_date : $item->mfg_date->format('d M Y')) : '—' }}</span>
                                    @else
                                        <input type="date" 
                                               form="stock-in-form-{{ $item->id }}" 
                                               name="mfg_date" 
                                               class="form-control form-control-sm px-1 small" 
                                               value="{{ old('mfg_date', $itemMfgDate) }}">
                                    @endif
                                </td>
                                <td>
                                    @if($item->is_stocked_in)
                                        <span class="small">{{ $item->expire_date ? (is_string($item->expire_date) ? $item->expire_date : $item->expire_date->format('d M Y')) : '—' }}</span>
                                    @else
                                        <input type="date" 
                                               form="stock-in-form-{{ $item->id }}" 
                                               name="expire_date" 
                                               class="form-control form-control-sm px-1 small" 
                                               value="{{ old('expire_date', $itemExpDate) }}">
                                    @endif
                                </td>
                                <td class="pe-3 text-center">
                                    @if($item->is_stocked_in)
                                        <span class="badge bg-success-subtle text-success px-2 py-1.5 fs-12 w-100">
                                            <iconify-icon icon="solar:check-circle-bold" class="align-middle me-1"></iconify-icon>
                                            Stocked In
                                        </span>
                                    @elseif(!$hasUploadedInvoice)
                                        <button type="button" 
                                                class="btn btn-sm btn-secondary py-1.5 px-2 w-100 fw-semibold" 
                                                disabled 
                                                title="Please upload Received Transport Invoice first">
                                            <iconify-icon icon="solar:lock-keyhole-bold" class="align-middle me-1"></iconify-icon>
                                            Upload Invoice First
                                        </button>
                                    @else
                                        <button type="submit" 
                                                form="stock-in-form-{{ $item->id }}" 
                                                class="btn btn-sm btn-primary py-1.5 px-3 w-100 fw-semibold">
                                            <iconify-icon icon="solar:import-bold" class="align-middle me-1"></iconify-icon>
                                            Stock In
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="12" class="text-center py-4 text-muted small">No items to receive in this order.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @php
        $hasUploadedInvoice = !empty($order->received_invoice_number) && !empty($order->received_invoice_file);
        $stockedInItems = $items->where('is_stocked_in', true);
        $pendingStockInCount = $items->where('is_stocked_in', false)->count();
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

    <div class="card border-0 shadow-sm mt-4 border-start border-4 border-warning {{ ($hasDiscrepancy || $hasExcess) ? '' : 'd-none' }}" id="discrepancy-banner">
        <div class="card-body py-3">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                <div>
                    <h6 class="fw-bold text-slate-800 mb-1 d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:shield-warning-bold" class="text-warning fs-4"></iconify-icon>
                        Stock-In Discrepancies Recorded
                    </h6>
                    <p class="text-muted small mb-0">
                        Shortage / Damage / Excess stock recorded for this order. You can generate Debit Notes routed directly to your <strong>{{ $targetLabel }}</strong>.
                    </p>
                </div>
                <div class="d-flex gap-2 flex-wrap" id="discrepancy-buttons-container">
                    @if($hasDiscrepancy)
                        @if($existingDebitNote)
                            <a href="{{ route('adc.debit-notes.show', $existingDebitNote->id) }}" class="btn btn-sm btn-outline-warning fw-semibold">
                                <iconify-icon icon="solar:document-text-bold" class="align-middle me-1"></iconify-icon>
                                View Debit Note #{{ $existingDebitNote->debit_note_number }}
                            </a>
                        @else
                            <a href="{{ route('adc.debit-notes.create', $order->id) }}" class="btn btn-sm btn-danger fw-bold">
                                <iconify-icon icon="solar:document-add-bold" class="align-middle me-1"></iconify-icon>
                                Generate Consolidated Debit Note
                            </a>
                        @endif
                    @endif

                    @if($hasExcess)
                        @if($existingExcessNote)
                            <a href="{{ route('adc.excess-invoices.show', $existingExcessNote->id) }}" class="btn btn-sm btn-outline-primary fw-semibold">
                                <iconify-icon icon="solar:document-text-bold" class="align-middle me-1"></iconify-icon>
                                View Excess Invoice #{{ $existingExcessNote->invoice_number }}
                            </a>
                        @else
                            <a href="{{ route('adc.excess-invoices.create', $order->id) }}" class="btn btn-sm btn-primary fw-bold">
                                <iconify-icon icon="solar:box-minimalistic-bold" class="align-middle me-1"></iconify-icon>
                                Generate Excess Stock Invoice
                            </a>
                        @endif
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($pendingStockInCount === 0 && $items->isNotEmpty())
        <div id="completion-banner" class="card border-0 shadow-sm mt-4 bg-success bg-opacity-10">
            <div class="card-body d-flex align-items-center justify-content-between py-3">
                <div>
                    <h6 class="fw-bold text-success mb-1">All Dispatched Items Stocked In Successfully!</h6>
                    <p class="text-muted small mb-0">Every dispatched product in this list has been stocked in and added to your ADC Inventory. Order status has been updated to "Received".</p>
                </div>
                <div>
                    <a href="{{ route('adc.orders.index') }}" class="btn btn-success fw-bold px-4 py-2">
                        <iconify-icon icon="solar:arrow-left-bold" class="align-middle me-1 fs-18"></iconify-icon>
                        Back to Orders
                    </a>
                </div>
            </div>
        </div>
    @endif

@push('scripts')
<script>
window.adcOrdersIndexUrl = "{{ route('adc.orders.index') }}";

document.addEventListener('DOMContentLoaded', function() {
    const forms = document.querySelectorAll('form[id^="stock-in-form-"]');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();

            const formId = form.id;
            const itemId = formId.replace('stock-in-form-', '');
            const row = document.getElementById(`item-row-${itemId}`);

            const supplyQty = parseInt(form.querySelector('input[name="supply_qty"]')?.value) || 0;

            const mrpInput = document.querySelector(`input[name="received_mrp"][form="${formId}"]`);
            const damageInput = document.querySelector(`input[name="damage_qty"][form="${formId}"]`);
            const shortageInput = document.querySelector(`input[name="shortage_qty"][form="${formId}"]`);
            const excessInput = document.querySelector(`input[name="excess_qty"][form="${formId}"]`);
            const batchInput = document.querySelector(`input[name="batch_number"][form="${formId}"]`);
            const mfgInput = document.querySelector(`input[name="mfg_date"][form="${formId}"]`);
            const expireInput = document.querySelector(`input[name="expire_date"][form="${formId}"]`);

            const damageQty = parseInt(damageInput?.value) || 0;
            const shortageQty = parseInt(shortageInput?.value) || 0;

            if ((damageQty + shortageQty) > supplyQty) {
                alert(`Damage Quantity (${damageQty}) + Shortage Quantity (${shortageQty}) cannot be greater than Supply Quantity (${supplyQty}).`);
                damageInput?.focus();
                return;
            }

            const submitBtn = document.querySelector(`button[type="submit"][form="${formId}"]`) || (row ? row.querySelector('button[type="submit"]') : null);
            let origBtnHtml = '';
            if (submitBtn) {
                origBtnHtml = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = `<span class="spinner-border spinner-border-sm me-1" role="status" aria-hidden="true"></span> Saving...`;
            }

            const formData = new FormData(form);
            if (mrpInput) formData.append('received_mrp', mrpInput.value);
            if (damageInput) formData.append('damage_qty', damageInput.value);
            if (shortageInput) formData.append('shortage_qty', shortageInput.value);
            if (excessInput) formData.append('excess_qty', excessInput.value);
            if (batchInput) formData.append('batch_number', batchInput.value);
            if (mfgInput) formData.append('mfg_date', mfgInput.value);
            if (expireInput) formData.append('expire_date', expireInput.value);

            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || formData.get('_token')
                },
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    if (row) {
                        row.classList.add('table-light', 'text-muted');

                        // 1. MRP & CSP cell (4th column, index 3)
                        const mrpCell = row.cells[3];
                        if (mrpCell) {
                            const purchPrice = parseFloat(form.dataset.price || 0).toFixed(2);
                            mrpCell.innerHTML = `
                                <div class="mb-1">
                                    <span class="text-muted small">Purch. Price:</span>
                                    <span class="fw-semibold text-slate-800 d-block">₹${purchPrice}</span>
                                </div>
                                <div>
                                    <span class="fw-bold text-success">₹${data.received_mrp}</span>
                                    <span class="badge bg-success-subtle text-success fs-10 d-block mt-0.5">CSP: ₹${data.csp} (Margin: ${data.margin}%)</span>
                                </div>
                            `;
                        }

                        // 2. Damage Qty (6th column, index 5)
                        if (row.cells[5]) {
                            const dmg = parseInt(data.damage_qty) || 0;
                            row.cells[5].innerHTML = `<span class="fw-medium ${dmg > 0 ? 'text-danger' : 'text-muted'}">${dmg}</span>`;
                        }

                        // 3. Shortage Qty (7th column, index 6)
                        if (row.cells[6]) {
                            const stg = parseInt(data.shortage_qty) || 0;
                            row.cells[6].innerHTML = `<span class="fw-medium ${stg > 0 ? 'text-warning' : 'text-muted'}">${stg}</span>`;
                        }

                        // 4. Excess Qty (8th column, index 7)
                        if (row.cells[7]) {
                            const exs = parseInt(data.excess_qty) || 0;
                            row.cells[7].innerHTML = `<span class="fw-medium ${exs > 0 ? 'text-primary' : 'text-muted'}">${exs}</span>`;
                        }

                        // 5. Batch No (9th column, index 8)
                        if (row.cells[8]) {
                            row.cells[8].innerHTML = `<span class="small">${data.batch_number}</span>`;
                        }

                        // 6. Mfg Date (10th column, index 9)
                        if (row.cells[9]) {
                            row.cells[9].innerHTML = `<span class="small">${data.mfg_date}</span>`;
                        }

                        // 7. Expire Date (11th column, index 10)
                        if (row.cells[10]) {
                            row.cells[10].innerHTML = `<span class="small">${data.expire_date}</span>`;
                        }

                        // 8. Action (12th column, index 11)
                        if (row.cells[11]) {
                            row.cells[11].innerHTML = `
                                <span class="badge bg-success-subtle text-success px-2 py-1.5 fs-12 w-100">
                                    <iconify-icon icon="solar:check-circle-bold" class="align-middle me-1"></iconify-icon>
                                    Stocked In
                                </span>
                            `;
                        }
                    }

                    showToast(data.message, 'success');

                    // Update discrepancy banner live
                    if (data.has_discrepancy || data.has_excess) {
                        const dispBanner = document.getElementById('discrepancy-banner');
                        const dispBtns = document.getElementById('discrepancy-buttons-container');
                        if (dispBanner) dispBanner.classList.remove('d-none');
                        if (dispBtns) {
                            let btnHtml = '';
                            if (data.has_discrepancy) {
                                btnHtml += `
                                    <a href="${data.debit_note_url}" class="btn btn-sm ${data.debit_note_exists ? 'btn-outline-warning' : 'btn-danger'} fw-bold">
                                        <iconify-icon icon="${data.debit_note_exists ? 'solar:document-text-bold' : 'solar:document-add-bold'}" class="align-middle me-1"></iconify-icon>
                                        ${data.debit_note_label}
                                    </a>
                                `;
                            }
                            if (data.has_excess) {
                                btnHtml += `
                                    <a href="${data.excess_invoice_url}" class="btn btn-sm ${data.excess_invoice_exists ? 'btn-outline-primary' : 'btn-primary'} fw-bold">
                                        <iconify-icon icon="${data.excess_invoice_exists ? 'solar:document-text-bold' : 'solar:box-minimalistic-bold'}" class="align-middle me-1"></iconify-icon>
                                        ${data.excess_invoice_label}
                                    </a>
                                `;
                            }
                            dispBtns.innerHTML = btnHtml;
                        }
                    }

                    if (data.all_stocked_in) {
                        const badgeContainer = document.getElementById('order-status-badge');
                        if (badgeContainer) {
                            badgeContainer.innerHTML = `
                                <span class="fs-14 text-muted">Order Status:</span>
                                <span class="badge bg-success text-white px-2.5 py-1.5 fs-13">Received</span>
                            `;
                        }

                        if (!document.getElementById('completion-banner')) {
                            const bannerHtml = `
                                <div id="completion-banner" class="card border-0 shadow-sm mt-4 bg-success bg-opacity-10">
                                    <div class="card-body d-flex align-items-center justify-content-between py-3">
                                        <div>
                                            <h6 class="fw-bold text-success mb-1">All Dispatched Items Stocked In Successfully!</h6>
                                            <p class="text-muted small mb-0">Every dispatched product in this list has been stocked in and added to your ADC Inventory. Order status has been updated to "Received".</p>
                                        </div>
                                        <div>
                                            <a href="${window.adcOrdersIndexUrl}" class="btn btn-success fw-bold px-4 py-2">
                                                <iconify-icon icon="solar:arrow-left-bold" class="align-middle me-1 fs-18"></iconify-icon>
                                                Back to Orders
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            `;
                            const mainContainer = document.querySelector('.card.border-0.shadow-sm');
                            if (mainContainer) {
                                mainContainer.insertAdjacentHTML('afterend', bannerHtml);
                            }
                        }
                    }
                } else {
                    alert(data.message || 'Something went wrong while stocking in.');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = origBtnHtml;
                    }
                }
            })
            .catch(err => {
                console.error(err);
                alert('An error occurred. Please try again.');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = origBtnHtml;
                }
            });
        });
    });

    function showToast(message, type = 'success') {
        let toastContainer = document.getElementById('ajax-toast-container');
        if (!toastContainer) {
            toastContainer = document.createElement('div');
            toastContainer.id = 'ajax-toast-container';
            toastContainer.style.position = 'fixed';
            toastContainer.style.bottom = '20px';
            toastContainer.style.right = '20px';
            toastContainer.style.zIndex = '9999';
            document.body.appendChild(toastContainer);
        }
        const toast = document.createElement('div');
        toast.className = `alert alert-${type === 'success' ? 'success' : 'danger'} alert-dismissible fade show shadow-lg border-0 mb-2`;
        toast.style.minWidth = '280px';
        toast.innerHTML = `
            <strong>${type === 'success' ? '✓ Success:' : '✕ Error:'}</strong> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        toastContainer.appendChild(toast);
        setTimeout(() => {
            toast.remove();
        }, 4000);
    }
});
</script>
@endpush
@endsection
