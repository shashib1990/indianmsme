@extends('layouts.ddc')

@section('title', 'Sales Returns History')
@section('page-title', 'Sales Returns History')

@push('styles')
    <style>
        .btn-icon.rounded-circle {
            width: 32px;
            height: 32px;
            padding: 0;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            line-height: 1;
        }
    </style>
@endpush

@section('content')
    <!-- Header Card -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center justify-content-between g-3">
                        <div class="col-md-5">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                <iconify-icon icon="solar:restart-circle-bold-duotone" class="text-danger align-middle me-1"></iconify-icon>
                                Sales Returns History
                            </h4>
                            <p class="text-muted small mb-0">View all processed Retail POS product returns, refund modes, and print return receipts.</p>
                        </div>
                        <div class="col-md-7 text-md-end d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
                            <form method="GET" action="{{ route('ddc.pos.returns.index') }}" class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="input-group input-group-sm" style="width: 260px;">
                                    <input type="text" name="search" class="form-control" placeholder="Search Return No / Order No / Customer..." value="{{ request('search') }}">
                                    <button class="btn btn-danger" type="submit">Search</button>
                                    @if(request('search'))
                                        <a href="{{ route('ddc.pos.returns.index') }}" class="btn btn-outline-secondary" title="Reset Search">
                                            <iconify-icon icon="solar:restart-bold-duotone" class="align-middle"></iconify-icon>
                                        </a>
                                    @endif
                                </div>
                            </form>
                            <a href="{{ route('ddc.pos.orders') }}" class="btn btn-sm btn-outline-primary fw-semibold">
                                <iconify-icon icon="solar:bill-list-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Sales History
                            </a>
                            <a href="{{ route('ddc.pos.retail') }}" class="btn btn-sm btn-success fw-semibold">
                                <iconify-icon icon="solar:user-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Retail POS
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Returns Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="fs-11 text-muted text-uppercase">
                            <th class="ps-3" style="width: 140px;">Return No</th>
                            <th style="width: 140px;">Original Order</th>
                            <th>Date & Time</th>
                            <th>Customer Details</th>
                            <th class="text-center">Returned Items</th>
                            <th class="text-end">Refund Amount</th>
                            <th class="text-center">Refund Mode</th>
                            <th class="text-center" style="width: 180px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($returns as $ret)
                            @php
                                $origInvNo = $ret->order?->invoice?->invoice_number ?: ($ret->order?->order_no ?: '—');
                            @endphp
                            <tr>
                                <td class="ps-3">
                                    <code class="fw-bold text-danger fs-12">{{ $ret->return_number }}</code>
                                </td>
                                <td>
                                    <span class="fw-semibold text-dark fs-12">{{ $origInvNo }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-slate-800 fs-13">{{ $ret->created_at->format('d M Y') }}</div>
                                    <div class="text-muted fs-11">{{ $ret->created_at->format('h:i A') }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-slate-800 fs-13">{{ $ret->customer_name ?: 'Walk-in Customer' }}</div>
                                    <div class="text-muted fs-11">{{ $ret->customer_mobile ?: '—' }}</div>
                                </td>
                                <td class="text-center fw-bold fs-13">
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1 fs-11">
                                        {{ (float)$ret->total_qty }} {{ $ret->items->first()?->unit_of_measure ?: 'Qty' }} ({{ $ret->total_items }} items)
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-danger fs-14">
                                    ₹{{ number_format($ret->total_return_amount, 2) }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-dark-subtle text-dark px-2 py-1 fs-11">
                                        {{ $ret->refund_mode }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex align-items-center justify-content-center gap-1">
                                        {{-- View Returned Items --}}
                                        <button type="button" class="btn btn-sm btn-outline-info btn-icon rounded-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="View Returned Items" onclick="openReturnItemsModal({{ $ret->id }})">
                                            <iconify-icon icon="solar:eye-bold-duotone" class="fs-16"></iconify-icon>
                                        </button>

                                        {{-- Print Sales Return Receipt --}}
                                        <a href="{{ route('ddc.pos.returns.print', $ret->id) }}" target="_blank" class="btn btn-sm btn-outline-danger btn-icon rounded-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="Print Sales Return Receipt">
                                            <iconify-icon icon="solar:printer-bold-duotone" class="fs-16"></iconify-icon>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted fs-13">
                                    <iconify-icon icon="solar:restart-square-broken" class="fs-36 text-muted mb-2 d-block mx-auto"></iconify-icon>
                                    No sales returns processed yet.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($returns->hasPages())
            <div class="card-footer bg-white border-top p-2.5">
                {{ $returns->links() }}
            </div>
        @endif
    </div>

    <!-- View Returned Items Modal -->
    <div class="modal fade" id="returnDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white py-2.5 px-3">
                    <div class="d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:restart-circle-bold-duotone" class="fs-20 text-white"></iconify-icon>
                        <h5 class="modal-title fw-bold text-white fs-15 mb-0" id="retDetailsModalTitle">Returned Products Details</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">
                    <!-- Customer & Invoice Info Bar -->
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="row g-3">
                            <div class="col-md-3 col-6">
                                <div class="text-muted fs-11 text-uppercase fw-semibold">Return Number</div>
                                <div class="fw-bold text-danger fs-13" id="modalRetNo">—</div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="text-muted fs-11 text-uppercase fw-semibold">Original Invoice</div>
                                <div class="fw-semibold text-dark fs-13" id="modalOrigInv">—</div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="text-muted fs-11 text-uppercase fw-semibold">Customer</div>
                                <div class="fw-semibold text-slate-800 fs-13" id="modalRetCust">—</div>
                            </div>
                            <div class="col-md-3 col-6">
                                <div class="text-muted fs-11 text-uppercase fw-semibold">Refund Mode</div>
                                <div class="fs-12 text-slate-800 fw-bold" id="modalRetRefundMode">—</div>
                            </div>
                        </div>
                    </div>

                    <!-- Items Table -->
                    <div class="table-responsive rounded border mb-3">
                        <table class="table table-hover align-middle mb-0" style="font-size: 12.5px;">
                            <thead class="bg-dark text-white" style="background-color: #0f172a !important;">
                                <tr class="fs-11 text-uppercase">
                                    <th class="text-center ps-3" style="width: 40px;">#</th>
                                    <th>Product Name</th>
                                    <th class="text-center">Code</th>
                                    <th class="text-center">Returned Qty</th>
                                    <th class="text-end">Unit Price</th>
                                    <th class="text-center">GST %</th>
                                    <th class="text-end pe-3">Refund Amount</th>
                                </tr>
                            </thead>
                            <tbody id="modalReturnItemsBody">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>

                    <!-- Financial Breakdown -->
                    <div class="row justify-content-end text-end g-2">
                        <div class="col-md-6">
                            <div class="bg-light p-3 rounded border fs-12">
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">Taxable Refund Subtotal:</span>
                                    <span class="fw-semibold" id="modalRetSubtotal">₹0.00</span>
                                </div>
                                <div class="d-flex justify-content-between mb-1">
                                    <span class="text-muted">GST Tax Reversal:</span>
                                    <span class="fw-semibold text-info" id="modalRetTax">+₹0.00</span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between fs-15 fw-bold text-dark">
                                    <span>Total Refund Amount:</span>
                                    <span class="text-danger" id="modalRetGrandTotal">₹0.00</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    <a href="#" id="modalPrintRetReceiptBtn" target="_blank" class="btn btn-sm btn-danger px-3">
                        <iconify-icon icon="solar:printer-bold-duotone" class="align-middle me-1"></iconify-icon> Print Sales Return Receipt
                    </a>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let returnsData = @json($returns->items());
    let retModalInstance = null;

    function openReturnItemsModal(returnId) {
        if (!retModalInstance) {
            retModalInstance = new bootstrap.Modal(document.getElementById('returnDetailsModal'));
        }

        let ret = returnsData.find(r => r.id === returnId);
        if (!ret) return;

        let origInv = ret.order && ret.order.invoice ? ret.order.invoice.invoice_number : (ret.order ? ret.order.order_no : '—');
        document.getElementById('modalRetNo').innerText = ret.return_number;
        document.getElementById('modalOrigInv').innerText = origInv;
        document.getElementById('modalRetCust').innerText = ret.customer_name || 'Walk-in Customer';
        document.getElementById('modalRetRefundMode').innerText = ret.refund_mode || 'Cash';

        document.getElementById('modalRetSubtotal').innerText = '₹' + parseFloat(ret.subtotal || 0).toFixed(2);
        document.getElementById('modalRetTax').innerText = '+₹' + parseFloat(ret.tax_amount || 0).toFixed(2);
        document.getElementById('modalRetGrandTotal').innerText = '₹' + parseFloat(ret.total_return_amount || 0).toFixed(2);

        let printUrl = "{{ route('ddc.pos.returns.print', ['id' => 999999]) }}".replace('999999', ret.id);
        document.getElementById('modalPrintRetReceiptBtn').href = printUrl;

        let tbody = document.getElementById('modalReturnItemsBody');
        tbody.innerHTML = '';

        let items = ret.items || [];
        items.forEach((item, index) => {
            let price = parseFloat(item.unit_price || 0);
            let qty = parseFloat(item.returned_quantity || 0);
            let total = parseFloat(item.total_amount || 0);
            let gst = parseFloat(item.gst_rate || 0);

            tbody.innerHTML += `
                <tr>
                    <td class="text-center ps-3 fw-bold text-muted">${index + 1}</td>
                    <td class="fw-bold text-dark">${item.product_name || '—'}</td>
                    <td class="text-center font-monospace text-muted fs-11">${item.product_code || '—'}</td>
                    <td class="text-center fw-bold"><span class="badge bg-danger-subtle text-danger fs-11">${qty} ${item.unit_of_measure || 'Units'}</span></td>
                    <td class="text-end fw-semibold">₹${price.toFixed(2)}</td>
                    <td class="text-center"><span class="badge bg-info-subtle text-info fw-bold fs-11">${gst.toFixed(0)}%</span></td>
                    <td class="text-end pe-3 fw-bold text-danger">₹${total.toFixed(2)}</td>
                </tr>
            `;
        });

        retModalInstance.show();
    }

    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (el) {
            return new bootstrap.Tooltip(el);
        });
    });
</script>
@endpush
