@extends('layouts.adc')

@section('title', 'Sales History & Invoices')
@section('page-title', 'Sales History & Invoices')

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
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">Sales History & Invoices</h4>
                            <p class="text-muted small mb-0">View all previous ADC Retail POS sales receipts, official tax invoices, and process returns.</p>
                        </div>
                        <div class="col-md-7 text-md-end d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
                            <form method="GET" action="{{ route('adc.pos.orders') }}" class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="input-group input-group-sm" style="width: 260px;">
                                    <input type="text" name="search" class="form-control" placeholder="Search Order / Invoice / Customer..." value="{{ request('search') }}">
                                    <button class="btn btn-primary" type="submit">Search</button>
                                    @if(request('search'))
                                        <a href="{{ route('adc.pos.orders') }}" class="btn btn-outline-secondary" title="Reset Filters">
                                            <iconify-icon icon="solar:restart-bold-duotone" class="align-middle"></iconify-icon>
                                        </a>
                                    @endif
                                </div>
                            </form>
                            <div class="btn-group">
                                <a href="{{ route('adc.pos.returns.index') }}" class="btn btn-sm btn-outline-danger fw-semibold" title="View Processed Sales Returns">
                                    <iconify-icon icon="solar:restart-circle-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Sales Returns
                                </a>
                                <a href="{{ route('adc.pos.retail') }}" class="btn btn-sm btn-success fw-semibold">
                                    <iconify-icon icon="solar:shop-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> POS Terminal
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Orders Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="fs-11 text-muted text-uppercase">
                            <th class="ps-3" style="width: 50px;">#</th>
                            <th style="width: 160px;">Invoice / Order No</th>
                            <th>Date & Time</th>
                            <th>Customer Details</th>
                            <th class="text-center">Items</th>
                            <th class="text-end">Total Amount</th>
                            <th class="text-center">Payment Mode</th>
                            <th class="text-center" style="width: 180px;">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($orders as $ord)
                            <tr>
                                <td class="ps-3 fw-bold text-slate-600">{{ $loop->iteration + ($orders->currentPage() - 1) * $orders->perPage() }}</td>
                                <td>
                                    @if($ord->invoice)
                                        <div class="fw-bold text-dark fs-12">{{ $ord->invoice->invoice_number }}</div>
                                        <div class="text-muted fs-11">Order: <code>{{ $ord->order_no }}</code></div>
                                    @else
                                        <code class="fw-bold text-primary fs-12">{{ $ord->order_no }}</code>
                                    @endif

                                    @php
                                        $rStat = strtolower($ord->return_status ?? 'none');
                                        $isReturned = in_array($rStat, ['partially returned', 'fully returned', 'partial', 'full']);
                                        $isFullyReturned = in_array($rStat, ['fully returned', 'full']);
                                        $isPartiallyReturned = in_array($rStat, ['partially returned', 'partial']);
                                    @endphp
                                    @if($isFullyReturned)
                                        <a href="{{ route('adc.pos.returns.index', ['search' => $ord->order_no]) }}" class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-0.5 fs-10 d-block mt-1 text-decoration-none" title="Click to view Sales Return Receipt">
                                            <iconify-icon icon="solar:restart-bold" class="align-middle me-0.5"></iconify-icon> Fully Returned
                                        </a>
                                    @elseif($isPartiallyReturned)
                                        <a href="{{ route('adc.pos.returns.index', ['search' => $ord->order_no]) }}" class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-0.5 fs-10 d-block mt-1 text-decoration-none" title="Click to view Sales Return Receipt">
                                            <iconify-icon icon="solar:restart-bold" class="align-middle me-0.5"></iconify-icon> Partially Returned
                                        </a>
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-slate-800 fs-13">{{ $ord->created_at->format('d M Y') }}</div>
                                    <div class="text-muted fs-11">{{ $ord->created_at->format('h:i A') }}</div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-slate-800 fs-13">{{ $ord->customer_name ?: 'Walk-in Customer' }}</div>
                                    <div class="text-muted fs-11">{{ $ord->customer_mobile ?: '—' }}</div>
                                </td>
                                <td class="text-center fw-bold fs-13">
                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2.5 py-1">{{ (float)$ord->total_items }}</span>
                                </td>
                                <td class="text-end fw-bold text-slate-800 fs-14">
                                    ₹{{ number_format($ord->total_amount, 2) }}
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-info-subtle text-info px-2.5 py-1 text-uppercase fs-11">{{ $ord->payment_mode }}</span>
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex align-items-center justify-content-center gap-1">
                                        {{-- View Purchased Products --}}
                                        <button type="button" class="btn btn-sm btn-outline-info btn-icon rounded-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="View Purchased Items" onclick="openOrderDetailsModal({{ $ord->id }})">
                                            <iconify-icon icon="solar:eye-bold-duotone" class="fs-16"></iconify-icon>
                                        </button>

                                        {{-- Print Sales Receipt --}}
                                        <a href="{{ route('adc.pos.orders.print', $ord->id) }}" target="_blank" class="btn btn-sm btn-outline-primary btn-icon rounded-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="Print Tax Invoice (80mm)">
                                            <iconify-icon icon="solar:printer-bold-duotone" class="fs-16"></iconify-icon>
                                        </a>

                                        {{-- View Return Details & Receipt (ONLY if returned) --}}
                                        @if($isReturned)
                                            <a href="{{ route('adc.pos.returns.index', ['search' => $ord->order_no]) }}" class="btn btn-sm btn-danger btn-icon rounded-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="View Sales Return Details & Receipt">
                                                <iconify-icon icon="solar:restart-bold-duotone" class="fs-16"></iconify-icon>
                                            </a>
                                        @endif

                                        {{-- Process Return (ONLY if NOT fully returned) --}}
                                        @if(!$isFullyReturned)
                                            <button type="button" class="btn btn-sm btn-outline-danger btn-icon rounded-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="Process Sales Return" onclick="openSalesReturnModal({{ $ord->id }})">
                                                <iconify-icon icon="solar:restart-circle-bold-duotone" class="fs-16"></iconify-icon>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <iconify-icon icon="solar:bill-list-broken" class="fs-48 opacity-50 mb-2"></iconify-icon>
                                    <div>No POS sales history found.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($orders->hasPages())
            <div class="card-footer bg-white border-top">
                {{ $orders->links() }}
            </div>
        @endif
    </div>

    <!-- Order Items Details Modal -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-white py-2.5 px-3" style="background-color: #1e293b !important;">
                    <div class="d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:box-minimalistic-bold-duotone" class="fs-20 text-info"></iconify-icon>
                        <h5 class="modal-title fw-bold text-white fs-15 mb-0" id="orderDetailsModalTitle">Purchased Items Details</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">
                    <!-- Loading State -->
                    <div id="orderDetailsLoader" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted small mt-2">Loading purchased items list...</p>
                    </div>

                    <!-- Details Content -->
                    <div id="orderDetailsContent" class="d-none">
                        <!-- Customer & Invoice Info Bar -->
                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <div class="row g-3">
                                <div class="col-md-3 col-6">
                                    <div class="text-muted fs-11 text-uppercase fw-semibold">Invoice Number</div>
                                    <div class="fw-bold text-dark fs-13" id="modalInvoiceNo">—</div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="text-muted fs-11 text-uppercase fw-semibold">Customer Name</div>
                                    <div class="fw-semibold text-slate-800 fs-13" id="modalCustName">—</div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="text-muted fs-11 text-uppercase fw-semibold">Mobile Number</div>
                                    <div class="fs-12 text-slate-800" id="modalCustMobile">—</div>
                                </div>
                                <div class="col-md-3 col-6">
                                    <div class="text-muted fs-11 text-uppercase fw-semibold">Order Date & Mode</div>
                                    <div class="fs-12 text-slate-800" id="modalOrderDate">—</div>
                                </div>
                            </div>
                        </div>

                        <!-- Items Table -->
                        <div class="table-responsive rounded border mb-3">
                            <table class="table table-hover align-middle mb-0" style="font-size: 12.5px;">
                                <thead class="table-dark">
                                    <tr class="fs-11 text-uppercase">
                                        <th class="text-center ps-3" style="width: 40px;">#</th>
                                        <th>Product Name</th>
                                        <th class="text-center">Product Code</th>
                                        <th class="text-center">Quantity</th>
                                        <th class="text-end">MRP</th>
                                        <th class="text-end">Selling Price</th>
                                        <th class="text-center">GST %</th>
                                        <th class="text-end">GST Amount</th>
                                        <th class="text-end pe-3">Line Total</th>
                                    </tr>
                                </thead>
                                <tbody id="modalItemsTableBody">
                                    <!-- Items rows populated dynamically -->
                                </tbody>
                            </table>
                        </div>

                        <!-- Summary Cards -->
                        <div class="row justify-content-end text-end g-2">
                            <div class="col-md-6 col-lg-5">
                                <div class="bg-light p-3 rounded border fs-12">
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted">Item Subtotal:</span>
                                        <span class="fw-semibold" id="modalSubtotal">₹0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1 text-danger" id="modalSpecialDiscountRow">
                                        <span>Special Discount:</span>
                                        <span class="fw-semibold" id="modalSpecialDiscountTotal">-₹0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted">Taxable Amount:</span>
                                        <span class="fw-semibold text-dark" id="modalTaxable">₹0.00</span>
                                    </div>
                                    <div class="d-flex justify-content-between mb-1">
                                        <span class="text-muted">Total GST Tax:</span>
                                        <span class="fw-semibold text-info" id="modalTaxTotal">+₹0.00</span>
                                    </div>
                                    <hr class="my-2">
                                    <div class="d-flex justify-content-between fs-14 fw-bold text-dark">
                                        <span>Grand Total:</span>
                                        <span class="text-primary" id="modalGrandTotal">₹0.00</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-outline-danger px-3 me-auto" id="modalProcessReturnBtn" onclick="triggerReturnFromDetailsModal()">
                        <iconify-icon icon="solar:restart-bold-duotone" class="align-middle me-1"></iconify-icon> Process Sales Return
                    </button>
                    <button type="button" class="btn btn-sm btn-secondary px-3" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-sm btn-primary px-3" id="modalPrintBtn">
                        <iconify-icon icon="solar:printer-bold-duotone" class="align-middle me-1"></iconify-icon> Print Receipt
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Sales Return Modal -->
    <div class="modal fade" id="salesReturnModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white py-2.5 px-3">
                    <div class="d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:restart-circle-bold-duotone" class="fs-22 text-white"></iconify-icon>
                        <h5 class="modal-title fw-bold text-white fs-15 mb-0" id="returnModalTitle">Process Sales Return</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="salesReturnForm" onsubmit="submitSalesReturn(event)">
                    @csrf
                    <input type="hidden" id="returnPosOrderId" name="pos_order_id">
                    
                    <div class="modal-body p-3">
                        <!-- Loader -->
                        <div id="returnModalLoader" class="text-center py-4">
                            <div class="spinner-border text-danger" role="status">
                                <span class="visually-hidden">Loading...</span>
                            </div>
                            <p class="text-muted small mt-2">Loading returnable items...</p>
                        </div>

                        <div id="returnModalContent" class="d-none">
                            <!-- Order & Customer Header -->
                            <div class="p-3 bg-light rounded-3 mb-3 border">
                                <div class="row g-3">
                                    <div class="col-md-3 col-6">
                                        <div class="text-muted fs-11 text-uppercase fw-semibold">Invoice Number</div>
                                        <div class="fw-bold text-dark fs-13" id="retModalInvNo">—</div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="text-muted fs-11 text-uppercase fw-semibold">Customer</div>
                                        <div class="fw-semibold text-slate-800 fs-13" id="retModalCustName">—</div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="text-muted fs-11 text-uppercase fw-semibold">Payment Mode</div>
                                        <div class="fs-12 text-slate-800" id="retModalPaymentMode">—</div>
                                    </div>
                                    <div class="col-md-3 col-6">
                                        <div class="text-muted fs-11 text-uppercase fw-semibold">Return Status</div>
                                        <div id="retModalReturnStatus"><span class="badge bg-secondary">None</span></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Select Items Table -->
                            <div class="table-responsive rounded border mb-3">
                                <table class="table table-hover align-middle mb-0" style="font-size: 12.5px;">
                                    <thead class="text-white" style="background-color: #0f172a !important;">
                                        <tr class="fs-11 text-uppercase">
                                            <th class="text-center ps-3" style="width: 40px;">
                                                <input type="checkbox" id="selectAllReturnItems" class="form-check-input" onchange="toggleSelectAllReturnItems(this)">
                                            </th>
                                            <th>Product Name</th>
                                            <th class="text-center">Sold Qty</th>
                                            <th class="text-center">Prev. Returned</th>
                                            <th class="text-center" style="width: 140px;">Return Qty</th>
                                            <th class="text-end">Unit Price</th>
                                            <th class="text-center">GST %</th>
                                            <th class="text-end">Tax Refund</th>
                                            <th class="text-end pe-3">Refund Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody id="returnModalItemsTableBody">
                                        <!-- Dynamic rows -->
                                    </tbody>
                                </table>
                            </div>

                            <!-- Refund Controls & Financial Summary -->
                            <div class="row g-3">
                                <div class="col-md-7">
                                    <div class="bg-light p-3 rounded border">
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-slate-700 fs-13 required">Refund / Adjustment Mode</label>
                                            <select name="refund_mode" id="refundModeSelect" class="form-select form-select-sm" required>
                                                <option value="Cash">Cash Refund</option>
                                                <option value="UPI">UPI / Online Transfer</option>
                                                <option value="Card">Card Refund</option>
                                                <option value="Store Credit">Store Credit / Wallet</option>
                                                <option value="Bank Transfer">Bank Transfer</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="form-label fw-semibold text-slate-700 fs-13">Reason for Return (Optional)</label>
                                            <textarea name="return_reason" id="returnReasonText" class="form-control form-control-sm" rows="2" placeholder="Mention reason for returning item(s)..."></textarea>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-5 text-end">
                                    <div class="bg-light p-3 rounded border fs-12">
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Return Subtotal (Excl. Tax):</span>
                                            <span class="fw-semibold" id="calcReturnSubtotal">₹0.00</span>
                                        </div>
                                        <div class="d-flex justify-content-between mb-1">
                                            <span class="text-muted">Returned GST (Tax Reversal):</span>
                                            <span class="fw-semibold text-info" id="calcReturnTax">+₹0.00</span>
                                        </div>
                                        <hr class="my-2">
                                        <div class="d-flex justify-content-between fs-15 fw-bold text-dark">
                                            <span>Total Refund Amount:</span>
                                            <span class="text-danger" id="calcReturnGrandTotal">₹0.00</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-danger px-4" id="btnSubmitSalesReturn">
                            <iconify-icon icon="solar:check-read-bold-duotone" class="align-middle me-1"></iconify-icon> Confirm & Process Return
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let detailsModalInstance = null;
    let returnModalInstance = null;
    let activeCurrentOrderId = null;

    function openOrderDetailsModal(orderId) {
        activeCurrentOrderId = orderId;
        if (!detailsModalInstance) {
            detailsModalInstance = new bootstrap.Modal(document.getElementById('orderDetailsModal'));
        }
        
        document.getElementById('orderDetailsLoader').classList.remove('d-none');
        document.getElementById('orderDetailsContent').classList.add('d-none');
        detailsModalInstance.show();

        fetch("{{ route('adc.pos.orders.show', ['id' => 999999]) }}".replace('999999', orderId))
            .then(res => res.json())
            .then(data => {
                if (!data.status || !data.order) {
                    alert('Failed to load order details.');
                    detailsModalInstance.hide();
                    return;
                }

                let ord = data.order;
                let invNo = ord.invoice ? ord.invoice.invoice_number : ord.order_no;
                document.getElementById('orderDetailsModalTitle').innerText = 'Purchased Items - #' + invNo;
                document.getElementById('modalInvoiceNo').innerText = invNo;
                document.getElementById('modalCustName').innerText = ord.customer_name || (ord.customer ? ord.customer.name : 'Walk-in Customer');
                document.getElementById('modalCustMobile').innerText = ord.customer_mobile || (ord.customer ? ord.customer.mobile : '—');
                
                let dt = ord.created_at ? new Date(ord.created_at).toLocaleString('en-IN', { dateStyle: 'medium', timeStyle: 'short' }) : '—';
                document.getElementById('modalOrderDate').innerText = dt + ' (' + (ord.payment_mode || 'Cash') + ')';
                
                let subtotal = parseFloat(ord.subtotal || 0);
                let specDisc = parseFloat(ord.special_discount_amount || 0);
                let taxTotal = parseFloat(ord.tax_amount || 0);
                let grandTotal = parseFloat(ord.total_amount || (subtotal - specDisc));
                let taxableAmount = Math.max(0, grandTotal - taxTotal);

                document.getElementById('modalSubtotal').innerText = '₹' + subtotal.toFixed(2);
                document.getElementById('modalTaxTotal').innerText = '+₹' + taxTotal.toFixed(2);
                if (document.getElementById('modalTaxable')) {
                    document.getElementById('modalTaxable').innerText = '₹' + taxableAmount.toFixed(2);
                }

                if (specDisc > 0) {
                    document.getElementById('modalSpecialDiscountRow').classList.remove('d-none');
                    document.getElementById('modalSpecialDiscountTotal').innerText = '-₹' + specDisc.toFixed(2);
                } else {
                    document.getElementById('modalSpecialDiscountRow').classList.add('d-none');
                }

                document.getElementById('modalGrandTotal').innerText = '₹' + grandTotal.toFixed(2);

                let btnProcRet = document.getElementById('modalProcessReturnBtn');
                if (ord.return_status === 'Fully Returned') {
                    btnProcRet.classList.add('d-none');
                } else {
                    btnProcRet.classList.remove('d-none');
                }

                let printUrl = "{{ route('adc.pos.orders.print', ['id' => 999999]) }}".replace('999999', ord.id);
                document.getElementById('modalPrintBtn').onclick = function() {
                    window.open(printUrl, '_blank');
                };

                let tbody = document.getElementById('modalItemsTableBody');
                tbody.innerHTML = '';

                let items = ord.items || [];
                if (items.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="9" class="text-center text-muted py-3">No items found for this order.</td></tr>';
                } else {
                    items.forEach((item, index) => {
                        let rate = parseFloat(item.unit_price || 0);
                        let mrp = parseFloat(item.mrp || rate);
                        let qty = parseFloat(item.quantity || 0);
                        let returnedQty = parseFloat(item.returned_quantity || 0);
                        let total = parseFloat(item.total_price || 0);
                        let gstRate = item.item_gst_rate !== undefined ? parseFloat(item.item_gst_rate) : 0;
                        let taxAmt = item.tax_amount !== undefined ? parseFloat(item.tax_amount) : 0;
                        let uom = item.unit_of_measure || 'Units';
                        let code = item.product_code || (item.product ? item.product.product_code : (item.local_product ? item.local_product.local_product_code : '—'));

                        tbody.innerHTML += `
                            <tr>
                                <td class="text-center ps-3 fw-bold text-muted">${index + 1}</td>
                                <td>
                                    <div class="fw-bold text-dark">${item.product_name || '—'}</div>
                                    ${item.batch_number ? `<span class="badge bg-light text-muted border fs-10">Batch: ${item.batch_number}</span>` : ''}
                                    ${returnedQty > 0 ? `<span class="badge bg-danger-subtle text-danger border fs-10 ms-1">Returned: ${returnedQty} ${uom}</span>` : ''}
                                </td>
                                <td class="text-center font-monospace text-muted fs-11">${code}</td>
                                <td class="text-center fw-bold"><span class="badge bg-primary-subtle text-primary fs-11">${qty} ${uom}</span></td>
                                <td class="text-end text-muted fs-12">₹${mrp.toFixed(2)}</td>
                                <td class="text-end fw-semibold">₹${rate.toFixed(2)}</td>
                                <td class="text-center"><span class="badge bg-info-subtle text-info fw-bold fs-11">${gstRate.toFixed(0)}%</span></td>
                                <td class="text-end text-muted">₹${taxAmt.toFixed(2)}</td>
                                <td class="text-end pe-3 fw-bold text-dark">₹${total.toFixed(2)}</td>
                            </tr>
                        `;
                    });
                }

                document.getElementById('orderDetailsLoader').classList.add('d-none');
                document.getElementById('orderDetailsContent').classList.remove('d-none');
            })
            .catch(err => {
                console.error(err);
                alert('Error loading order details.');
                detailsModalInstance.hide();
            });
    }

    function triggerReturnFromDetailsModal() {
        if (detailsModalInstance) {
            detailsModalInstance.hide();
        }
        if (activeCurrentOrderId) {
            setTimeout(() => {
                openSalesReturnModal(activeCurrentOrderId);
            }, 300);
        }
    }

    function isDecimalAllowed(uom) {
        if (!uom) return false;
        let lower = uom.toLowerCase().trim();
        const decimalUnits = ['kg', 'kilo', 'kilogram', 'gm', 'gram', 'gramme', 'liter', 'litre', 'ltr', 'ml', 'milliliter', 'gallon', 'loose', 'meter', 'mtr', 'ton', 'tonne', 'quintal'];
        return decimalUnits.some(unit => lower.includes(unit));
    }

    function openSalesReturnModal(orderId) {
        if (!returnModalInstance) {
            returnModalInstance = new bootstrap.Modal(document.getElementById('salesReturnModal'));
        }

        document.getElementById('returnPosOrderId').value = orderId;
        document.getElementById('returnModalLoader').classList.remove('d-none');
        document.getElementById('returnModalContent').classList.add('d-none');
        returnModalInstance.show();

        fetch("{{ route('adc.pos.orders.show', ['id' => 999999]) }}".replace('999999', orderId))
            .then(res => res.json())
            .then(data => {
                if (!data.status || !data.order) {
                    alert('Failed to load order for return.');
                    returnModalInstance.hide();
                    return;
                }

                let ord = data.order;
                let invNo = ord.invoice ? ord.invoice.invoice_number : ord.order_no;
                document.getElementById('returnModalTitle').innerText = 'Process Sales Return - #' + invNo;
                document.getElementById('retModalInvNo').innerText = invNo;
                document.getElementById('retModalCustName').innerText = ord.customer_name || (ord.customer ? ord.customer.name : 'Walk-in Customer');
                document.getElementById('retModalPaymentMode').innerText = ord.payment_mode || 'Cash';
                
                let retStatusBadge = '<span class="badge bg-secondary">None</span>';
                if (ord.return_status === 'Fully Returned') {
                    retStatusBadge = '<span class="badge bg-danger">Fully Returned</span>';
                } else if (ord.return_status === 'Partially Returned') {
                    retStatusBadge = '<span class="badge bg-warning text-dark">Partially Returned</span>';
                }
                document.getElementById('retModalReturnStatus').innerHTML = retStatusBadge;

                let tbody = document.getElementById('returnModalItemsTableBody');
                tbody.innerHTML = '';

                let items = ord.items || [];
                let hasReturnableItems = false;

                items.forEach((item) => {
                    let rate = parseFloat(item.unit_price || 0);
                    let soldQty = parseFloat(item.quantity || 0);
                    let prevReturned = parseFloat(item.returned_quantity || 0);
                    let maxReturnable = Math.max(0, soldQty - prevReturned);
                    let gstRate = item.item_gst_rate !== undefined ? parseFloat(item.item_gst_rate) : 0;
                    let uom = item.unit_of_measure || 'Units';
                    let isDec = isDecimalAllowed(uom);

                    if (maxReturnable > 0) {
                        hasReturnableItems = true;
                    }

                    let isCheckDisabled = maxReturnable <= 0 ? 'disabled' : '';
                    let isChecked = maxReturnable > 0 ? 'checked' : '';
                    let stepAttr = isDec ? 'step="any" min="0.001"' : 'step="1" min="1"';
                    let initialVal = maxReturnable > 0 ? maxReturnable : 0;

                    tbody.innerHTML += `
                        <tr class="return-item-row" data-item-id="${item.id}" data-rate="${rate}" data-gst="${gstRate}" data-max="${maxReturnable}" data-uom="${uom}" data-is-dec="${isDec ? 1 : 0}" data-prod-name="${(item.product_name || 'Product').replace(/"/g, '&quot;')}">
                            <td class="text-center ps-3">
                                <input type="checkbox" class="form-check-input item-return-check" ${isChecked} ${isCheckDisabled} onchange="toggleItemReturnRow(this)">
                            </td>
                            <td>
                                <div class="fw-bold text-dark">${item.product_name || '—'}</div>
                                <div class="fs-11 text-muted">${item.product_code || '—'} ${item.batch_number ? '· Batch: '+item.batch_number : ''} <span class="badge bg-light text-dark border ms-1">${uom}</span></div>
                            </td>
                            <td class="text-center font-monospace">${soldQty} ${uom}</td>
                            <td class="text-center font-monospace text-danger">${prevReturned} ${uom}</td>
                            <td class="text-center">
                                <input type="number" ${stepAttr} max="${maxReturnable}" 
                                    class="form-control form-control-sm text-center return-qty-input" 
                                    value="${initialVal}" 
                                    ${isCheckDisabled}
                                    oninput="recalculateReturnTotals()">
                                <small class="text-muted fs-10">Max: ${maxReturnable}</small>
                            </td>
                            <td class="text-end fw-semibold">₹${rate.toFixed(2)}</td>
                            <td class="text-center"><span class="badge bg-info-subtle text-info fw-bold fs-11">${gstRate.toFixed(0)}%</span></td>
                            <td class="text-end text-muted row-tax-refund">₹0.00</td>
                            <td class="text-end pe-3 fw-bold text-danger row-total-refund">₹0.00</td>
                        </tr>
                    `;
                });

                if (!hasReturnableItems) {
                    tbody.innerHTML = '<tr><td colspan="9" class="text-center text-danger py-3 fw-bold">All items in this invoice have already been fully returned.</td></tr>';
                    document.getElementById('btnSubmitSalesReturn').disabled = true;
                } else {
                    document.getElementById('btnSubmitSalesReturn').disabled = false;
                }

                document.getElementById('returnModalLoader').classList.add('d-none');
                document.getElementById('returnModalContent').classList.remove('d-none');

                recalculateReturnTotals();
            })
            .catch(err => {
                console.error(err);
                alert('Error loading order for return.');
                returnModalInstance.hide();
            });
    }

    function toggleSelectAllReturnItems(master) {
        let checks = document.querySelectorAll('.item-return-check:not(:disabled)');
        checks.forEach(chk => {
            chk.checked = master.checked;
            toggleItemReturnRow(chk);
        });
        recalculateReturnTotals();
    }

    function toggleItemReturnRow(chk) {
        let row = chk.closest('tr');
        let qtyInput = row.querySelector('.return-qty-input');
        if (chk.checked) {
            qtyInput.disabled = false;
        } else {
            qtyInput.disabled = true;
        }
        recalculateReturnTotals();
    }

    function recalculateReturnTotals() {
        let totalSubtotal = 0;
        let totalTax = 0;
        let grandTotal = 0;

        let rows = document.querySelectorAll('.return-item-row');
        rows.forEach(row => {
            let chk = row.querySelector('.item-return-check');
            let qtyInput = row.querySelector('.return-qty-input');
            let maxQty = parseFloat(row.getAttribute('data-max') || 0);
            let rate = parseFloat(row.getAttribute('data-rate') || 0);
            let gstRate = parseFloat(row.getAttribute('data-gst') || 0);
            let isDec = row.getAttribute('data-is-dec') === '1';

            let taxRefundCell = row.querySelector('.row-tax-refund');
            let totalRefundCell = row.querySelector('.row-total-refund');

            if (chk && chk.checked && !qtyInput.disabled) {
                let returnQty = parseFloat(qtyInput.value) || 0;
                if (returnQty > maxQty) {
                    returnQty = maxQty;
                    qtyInput.value = maxQty;
                }

                if (!isDec && returnQty > 0 && !Number.isInteger(returnQty)) {
                    qtyInput.classList.add('is-invalid');
                } else {
                    qtyInput.classList.remove('is-invalid');
                }

                let lineTotal = rate * returnQty;
                let lineTaxable = lineTotal;
                let lineTax = 0;

                if (gstRate > 0) {
                    lineTaxable = lineTotal / (1 + (gstRate / 100));
                    lineTax = lineTotal - lineTaxable;
                }

                taxRefundCell.innerText = '₹' + lineTax.toFixed(2);
                totalRefundCell.innerText = '₹' + lineTotal.toFixed(2);

                totalSubtotal += lineTaxable;
                totalTax += lineTax;
                grandTotal += lineTotal;
            } else {
                qtyInput.classList.remove('is-invalid');
                taxRefundCell.innerText = '₹0.00';
                totalRefundCell.innerText = '₹0.00';
            }
        });

        document.getElementById('calcReturnSubtotal').innerText = '₹' + totalSubtotal.toFixed(2);
        document.getElementById('calcReturnTax').innerText = '+₹' + totalTax.toFixed(2);
        document.getElementById('calcReturnGrandTotal').innerText = '₹' + grandTotal.toFixed(2);
    }

    function submitSalesReturn(e) {
        e.preventDefault();

        let posOrderId = document.getElementById('returnPosOrderId').value;
        let refundMode = document.getElementById('refundModeSelect').value;
        let returnReason = document.getElementById('returnReasonText').value;

        let returnItemsPayload = [];
        let rows = document.querySelectorAll('.return-item-row');

        let hasValidationError = false;
        rows.forEach(row => {
            if (hasValidationError) return;
            let chk = row.querySelector('.item-return-check');
            let qtyInput = row.querySelector('.return-qty-input');
            let itemId = row.getAttribute('data-item-id');
            let isDec = row.getAttribute('data-is-dec') === '1';
            let uom = row.getAttribute('data-uom') || 'Units';
            let prodName = row.getAttribute('data-prod-name') || 'Product';

            if (chk && chk.checked && !qtyInput.disabled) {
                let returnQty = parseFloat(qtyInput.value) || 0;
                if (returnQty > 0) {
                    if (!isDec && !Number.isInteger(returnQty)) {
                        alert(`Decimal quantity is not allowed for "${prodName}" (UOM: ${uom}). Please enter a whole number.`);
                        qtyInput.focus();
                        hasValidationError = true;
                        return;
                    }
                    returnItemsPayload.push({
                        item_id: itemId,
                        return_qty: returnQty
                    });
                }
            }
        });

        if (hasValidationError) {
            return;
        }

        if (returnItemsPayload.length === 0) {
            alert('Please select at least one product and enter return quantity.');
            return;
        }


        let submitBtn = document.getElementById('btnSubmitSalesReturn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing Return...';

        fetch("{{ route('adc.pos.returns.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                pos_order_id: posOrderId,
                refund_mode: refundMode,
                return_reason: returnReason,
                items: returnItemsPayload
            })
        })
        .then(res => res.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<iconify-icon icon="solar:check-read-bold-duotone" class="align-middle me-1"></iconify-icon> Confirm & Process Return';

            if (data.status) {
                returnModalInstance.hide();
                if (data.print_url) {
                    window.open(data.print_url, '_blank');
                }
                alert(data.message || 'Sales Return processed successfully!');
                window.location.reload();
            } else {
                alert(data.message || 'Failed to process sales return.');
            }
        })
        .catch(err => {
            console.error(err);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<iconify-icon icon="solar:check-read-bold-duotone" class="align-middle me-1"></iconify-icon> Confirm & Process Return';
            alert('An error occurred while processing the sales return.');
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(function (el) {
            return new bootstrap.Tooltip(el);
        });
    });
</script>
@endpush
