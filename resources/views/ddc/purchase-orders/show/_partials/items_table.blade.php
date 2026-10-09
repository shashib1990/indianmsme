{{-- Shared PO Items Table Partial --}}
{{-- Variables: $purchaseOrder, $showSellerResponse (bool), $showBatchInfo (bool) --}}
@php
    $showSellerResponse = $showSellerResponse ?? true;
    $showBatchInfo      = $showBatchInfo      ?? false;
    $acceptedTotal      = $purchaseOrder->items->where('status', 'Accepted')->sum('total');
    $rejectedTotal      = $purchaseOrder->items->where('status', 'Rejected')->sum('total');
    $pendingTotal       = $purchaseOrder->items->where('status', 'Pending')->sum('total');
@endphp
<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-0 py-3 border-bottom d-flex align-items-center justify-content-between">
        <h6 class="fw-bold text-slate-800 mb-0">
            <iconify-icon icon="solar:box-bold-duotone" class="align-middle me-1 text-primary fs-18"></iconify-icon>
            PO Items ({{ $purchaseOrder->items->count() }})
        </h6>
        <div class="d-flex gap-2 flex-wrap">
            @if($purchaseOrder->items->where('status','Accepted')->count() > 0)
                <span class="badge bg-success-subtle text-success px-2 py-1 fs-12">
                    ✓ {{ $purchaseOrder->items->where('status','Accepted')->count() }} Accepted
                </span>
            @endif
            @if($purchaseOrder->items->where('status','Rejected')->count() > 0)
                <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-12">
                    ✗ {{ $purchaseOrder->items->where('status','Rejected')->count() }} Rejected
                </span>
            @endif
            @if($purchaseOrder->items->where('status','Pending')->count() > 0)
                <span class="badge bg-warning-subtle text-warning px-2 py-1 fs-12">
                    ⌛ {{ $purchaseOrder->items->where('status','Pending')->count() }} Pending
                </span>
            @endif
        </div>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0 align-middle">
                <thead class="bg-light bg-opacity-50 border-top border-bottom">
                    <tr>
                        <th class="ps-3" style="width:40px;">Sn.</th>
                        <th>Product</th>
                        <th>Seller</th>
                        <th class="text-end">Price</th>
                        <th class="text-center">Qty</th>
                        <th class="text-end">Total</th>
                        @if($showSellerResponse)
                            <th class="pe-3 text-center">Seller Response</th>
                        @endif
                        @if($showBatchInfo)
                            <th class="text-center">Batch / Dates</th>
                            <th class="pe-3 text-center">Stock Status</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($purchaseOrder->items as $item)
                        <tr class="{{ $item->status === 'Rejected' ? 'table-danger bg-opacity-25' : '' }}">
                            <td class="ps-3 text-muted small">{{ $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="bg-light rounded overflow-hidden d-flex align-items-center justify-content-center flex-shrink-0 border"
                                         style="width:40px;height:40px;min-width:40px;">
                                        @if($item->product?->featured_image)
                                            <img src="{{ Str::startsWith($item->product->featured_image, ['http://','https://']) ? $item->product->featured_image : asset('storage/'.$item->product->featured_image) }}"
                                                 alt="img" class="img-fluid object-fit-contain p-0.5" style="max-height:100%;max-width:100%;"
                                                 onerror="this.onerror=null; this.src='{{ asset('admin-assets/images/IndianMSME.png') }}';">
                                        @else
                                            <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="text-muted fs-18"></iconify-icon>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1" style="min-width:0;">
                                        <div class="fw-semibold text-slate-800 text-wrap" style="max-width:240px;word-break:break-word;">
                                            {{ $item->product?->product_name ?? ($item->product_name ?? '—') }}
                                        </div>
                                        <div class="text-muted small">
                                            UOM: {{ $item->unit_of_measure ?? ($item->product?->unit_of_measure ?? '—') }}
                                            @if($item->product?->barcode)
                                                · <span class="font-monospace">{{ $item->product->barcode }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="small fw-semibold text-slate-700">{{ $item->seller?->company_name ?: $item->seller?->name }}</div>
                                <div class="text-muted" style="font-size:11px;">ID: {{ $item->seller?->seller_id }}</div>
                            </td>
                            <td class="text-end fw-semibold">₹{{ number_format($item->price, 2) }}</td>
                            <td class="text-center">{{ $item->quantity }} {{ $item->unit_of_measure ?? ($item->product?->unit_of_measure ?? 'Units') }}</td>
                            <td class="text-end fw-bold text-slate-800">₹{{ number_format($item->total, 2) }}</td>
                            @if($showSellerResponse)
                                <td class="pe-3 text-center">
                                    @if($item->status === 'Pending')
                                        <span class="badge bg-warning-subtle text-warning px-2 py-1 fs-11">⌛ Awaiting</span>
                                    @elseif($item->status === 'Accepted')
                                        <span class="badge bg-success-subtle text-success px-2 py-1 fs-11">✓ Accepted</span>
                                    @elseif($item->status === 'Rejected')
                                        <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-11">✗ Rejected</span>
                                        @if($item->remarks)
                                            <div class="text-danger mt-1" style="font-size:10px;">{{ $item->remarks }}</div>
                                        @endif
                                    @endif
                                </td>
                            @endif
                            @if($showBatchInfo)
                                <td class="text-center small">
                                    @if($item->is_stocked_in)
                                        <div class="text-muted">Batch: <strong>{{ $item->batch_number ?? '—' }}</strong></div>
                                        <div class="text-muted">Mfg: {{ $item->mfg_date?->format('d M Y') ?? '—' }}</div>
                                        <div class="text-muted">Exp: {{ $item->expire_date?->format('d M Y') ?? '—' }}</div>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="pe-3 text-center">
                                    @if($item->is_stocked_in)
                                        <span class="badge bg-success-subtle text-success fs-11 px-2 py-1">
                                            <iconify-icon icon="solar:check-circle-bold" class="align-middle me-1"></iconify-icon>
                                            Stocked In
                                        </span>
                                        <div class="small text-muted mt-1">
                                            Net: {{ $item->quantity - ($item->damage_qty ?? 0) - ($item->shortage_qty ?? 0) + ($item->excess_qty ?? 0) }} {{ $item->unit_of_measure ?? ($item->product?->unit_of_measure ?? 'Units') }}
                                        </div>
                                    @else
                                        <span class="badge bg-secondary-subtle text-secondary fs-11 px-2 py-1">Pending</span>
                                    @endif
                                </td>
                            @endif
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-light border-top">
                    <tr>
                        <td colspan="{{ $showSellerResponse ? 5 : ($showBatchInfo ? 5 : 5) }}" class="ps-3 text-muted small">
                            Total Items: {{ $purchaseOrder->items->count() }}
                        </td>
                        <td class="text-end fw-bold text-slate-800 pe-0">
                            ₹{{ number_format($purchaseOrder->total_amount, 2) }}
                        </td>
                        @if($showSellerResponse)
                            <td class="pe-3 text-end text-muted small">Estimated</td>
                        @endif
                        @if($showBatchInfo)
                            <td colspan="2"></td>
                        @endif
                    </tr>
                    @if($acceptedTotal > 0)
                        <tr class="table-success bg-opacity-25">
                            <td colspan="{{ $showSellerResponse ? 5 : 5 }}" class="ps-3 text-success small fw-semibold">
                                Accepted Items Total
                            </td>
                            <td class="text-end fw-bold text-success pe-0">₹{{ number_format($acceptedTotal, 2) }}</td>
                            @if($showSellerResponse)<td class="pe-3"></td>@endif
                            @if($showBatchInfo)<td colspan="2"></td>@endif
                        </tr>
                    @endif
                </tfoot>
            </table>
        </div>
    </div>
</div>
