@extends('layouts.adc')

@section('title', 'POS Customers & Udhaar Credit Management')
@section('page-title', 'Customer Udhaar Credit Management')

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
        .stats-card {
            background: #fff;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            padding: 16px;
        }
        .table-ledger th {
            background-color: #0f172a !important;
            color: #fff !important;
            font-size: 11px;
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
                                <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" class="text-primary align-middle me-1"></iconify-icon>
                                Customers & Udhaar Credit Ledger
                            </h4>
                            <p class="text-muted small mb-0">Manage customer profiles, record udhaar payments, and view detailed credit ledgers.</p>
                        </div>
                        <div class="col-md-7 text-md-end d-flex align-items-center justify-content-md-end gap-2 flex-wrap">
                            <form method="GET" action="{{ route('adc.pos.customers') }}" class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="input-group input-group-sm" style="width: 250px;">
                                    <input type="text" name="search" class="form-control" placeholder="Search Customer Name / Mobile..." value="{{ $search }}">
                                    <button class="btn btn-primary" type="submit">Search</button>
                                    @if($search)
                                        <a href="{{ route('adc.pos.customers') }}" class="btn btn-outline-secondary" title="Reset Filters">
                                            <iconify-icon icon="solar:restart-bold-duotone" class="align-middle"></iconify-icon>
                                        </a>
                                    @endif
                                </div>
                            </form>
                            <button type="button" class="btn btn-sm btn-success fw-semibold" onclick="openReceivePaymentModal()">
                                <iconify-icon icon="solar:card-send-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> Receive Udhaar Payment
                            </button>
                            <a href="{{ route('adc.pos.retail') }}" class="btn btn-sm btn-outline-primary fw-semibold">
                                <iconify-icon icon="solar:shop-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon> POS Terminal
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Stats Bar -->
    <div class="row mb-3 g-3">
        <div class="col-md-3">
            <div class="stats-card d-flex align-items-center gap-3">
                <div class="bg-danger-subtle text-danger rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <iconify-icon icon="solar:card-transfer-bold-duotone" class="fs-24"></iconify-icon>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Total Outstanding Udhaar</div>
                    <div class="fs-20 fw-bold text-danger">₹{{ number_format($totalOutstanding, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card d-flex align-items-center gap-3">
                <div class="bg-primary-subtle text-primary rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <iconify-icon icon="solar:bill-list-bold-duotone" class="fs-24"></iconify-icon>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Total Credit Sales</div>
                    <div class="fs-20 fw-bold text-slate-800">₹{{ number_format($totalCreditGiven, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card d-flex align-items-center gap-3">
                <div class="bg-success-subtle text-success rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <iconify-icon icon="solar:wallet-money-bold-duotone" class="fs-24"></iconify-icon>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Total Udhaar Collected</div>
                    <div class="fs-20 fw-bold text-success">₹{{ number_format($totalPaymentReceived, 2) }}</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="stats-card d-flex align-items-center gap-3">
                <div class="bg-warning-subtle text-warning-emphasis rounded-circle p-3 d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                    <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" class="fs-24"></iconify-icon>
                </div>
                <div>
                    <div class="text-muted small fw-semibold text-uppercase">Active Udhaar Accounts</div>
                    <div class="fs-20 fw-bold text-slate-800">{{ $outstandingCustomersCount }} Customers</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Customers Table Card -->
    <div class="card border-0 shadow-sm rounded-3">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr class="fs-11 text-muted text-uppercase">
                            <th class="ps-3" style="width: 50px;">#</th>
                            <th>Customer Name</th>
                            <th>Mobile Number</th>
                            <th>Address / Details</th>
                            <th class="text-end">Total Credit Given</th>
                            <th class="text-end">Total Received</th>
                            <th class="text-end">Current Outstanding</th>
                            <th class="text-center" style="width: 170px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $cust)
                            @php
                                $outstanding = (float) $cust->outstanding_balance;
                            @endphp
                            <tr>
                                <td class="ps-3 fw-bold text-slate-600">{{ $loop->iteration + ($customers->currentPage() - 1) * $customers->perPage() }}</td>
                                <td>
                                    <div class="fw-bold text-dark fs-13 d-flex align-items-center gap-1.5">
                                        <span>{{ $cust->name }}</span>
                                        @if(!empty($cust->abhhyam_code))
                                            <span class="badge fs-10 px-2 py-0.5 rounded-pill font-monospace fw-bold" style="background: #fef3c7; color: #b45309; border: 1px solid #fde68a;" title="AVI Code">
                                                ★ AVI Code: {{ $cust->abhhyam_code }}
                                            </span>
                                        @endif
                                    </div>
                                    @if($cust->email)
                                        <div class="text-muted fs-11">{{ $cust->email }}</div>
                                    @endif
                                </td>
                                <td>
                                    <span class="font-monospace fw-bold text-primary fs-13">{{ $cust->mobile }}</span>
                                </td>
                                <td class="fs-12 text-muted">
                                    {{ $cust->address ?: '—' }}
                                    @if($cust->pincode)
                                        <span class="badge bg-light text-muted border fs-10">Pincode: {{ $cust->pincode }}</span>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold text-slate-700 fs-13">
                                    ₹{{ number_format($cust->total_credit_given, 2) }}
                                </td>
                                <td class="text-end fw-semibold text-success fs-13">
                                    ₹{{ number_format($cust->total_payment_received, 2) }}
                                </td>
                                <td class="text-end">
                                    @if($outstanding > 0)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 fs-12 cursor-pointer" onclick="openLedgerModal({{ $cust->id }})" title="Click to view Customer Credit Ledger">
                                            ₹{{ number_format($outstanding, 2) }}
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 fs-11">
                                            ₹0.00 (Settled)
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-inline-flex align-items-center justify-content-center gap-1">
                                        {{-- View Ledger --}}
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-icon rounded-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="View Customer Credit Ledger" onclick="openLedgerModal({{ $cust->id }})">
                                            <iconify-icon icon="solar:notebook-bold-duotone" class="fs-16"></iconify-icon>
                                        </button>

                                        {{-- Receive Udhaar Payment --}}
                                        <button type="button" class="btn btn-sm btn-success btn-icon rounded-circle" data-bs-toggle="tooltip" data-bs-placement="top" title="Receive Udhaar Payment" onclick="openReceivePaymentModal({{ $cust->id }}, '{{ addslashes($cust->name) }}', '{{ $cust->mobile }}', {{ $outstanding }})">
                                            <iconify-icon icon="solar:card-send-bold-duotone" class="fs-16"></iconify-icon>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <iconify-icon icon="solar:users-group-two-rounded-broken" class="fs-48 opacity-50 mb-2"></iconify-icon>
                                    <div>No POS customers found.</div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($customers->hasPages())
            <div class="card-footer bg-white border-top">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

    <!-- Modal: Receive Udhaar Payment -->
    <div class="modal fade" id="receivePaymentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white py-2.5 px-3">
                    <div class="d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:card-send-bold-duotone" class="fs-22 text-white"></iconify-icon>
                        <h5 class="modal-title fw-bold text-white fs-15 mb-0">Receive Udhaar Payment</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form id="receivePaymentForm" onsubmit="submitReceivePayment(event)">
                    @csrf
                    <div class="modal-body p-3">
                        <!-- Select Customer -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-slate-700 fs-13 required">Select Customer</label>
                            <select name="customer_id" id="recPayCustomerId" class="form-select" required onchange="onPaymentCustomerChange(this)">
                                <option value="">-- Choose Customer --</option>
                                @foreach($customers as $c)
                                    <option value="{{ $c->id }}" data-outstanding="{{ (float)$c->outstanding_balance }}" data-name="{{ $c->name }}" data-mobile="{{ $c->mobile }}">
                                        {{ $c->name }} ({{ $c->mobile }}) — Outstanding: ₹{{ number_format($c->outstanding_balance, 2) }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Current Outstanding Alert Box -->
                        <div class="p-3 bg-light rounded-3 mb-3 border text-center">
                            <div class="text-muted fs-11 text-uppercase fw-semibold mb-1">Current Outstanding Balance</div>
                            <div class="fs-22 fw-bold text-danger" id="recPayOutstandingDisplay">₹0.00</div>
                        </div>

                        <!-- Payment Amount -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-slate-700 fs-13 required">Payment Amount Received (₹)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light font-monospace fw-bold">₹</span>
                                <input type="number" step="0.01" min="0.01" name="amount" id="recPayAmount" class="form-control form-control-lg fw-bold text-success fs-18" placeholder="0.00" required>
                            </div>
                        </div>

                        <!-- Payment Mode -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-slate-700 fs-13 required">Payment Mode</label>
                            <div class="row g-2 text-center">
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="payment_mode" id="recPayModeCash" value="Cash" checked>
                                    <label class="btn btn-outline-secondary w-100 py-2 fs-12 fw-bold" for="recPayModeCash">💵 Cash</label>
                                </div>
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="payment_mode" id="recPayModeCard" value="Card">
                                    <label class="btn btn-outline-secondary w-100 py-2 fs-12 fw-bold" for="recPayModeCard">💳 Card</label>
                                </div>
                                <div class="col-4">
                                    <input type="radio" class="btn-check" name="payment_mode" id="recPayModeUpi" value="UPI">
                                    <label class="btn btn-outline-secondary w-100 py-2 fs-12 fw-bold" for="recPayModeUpi">📱 UPI</label>
                                </div>
                            </div>
                        </div>

                        <!-- Remarks -->
                        <div class="mb-2">
                            <label class="form-label fw-semibold text-slate-700 fs-13">Remarks / Note (Optional)</label>
                            <textarea name="remarks" id="recPayRemarks" class="form-control form-control-sm" rows="2" placeholder="Mention payment note or reference..."></textarea>
                        </div>
                    </div>

                    <div class="modal-footer py-2">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-sm btn-success px-4" id="btnSubmitRecPay">
                            <iconify-icon icon="solar:check-read-bold-duotone" class="align-middle me-1"></iconify-icon> Confirm & Save Payment
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal: Customer Credit Ledger View -->
    <div class="modal fade" id="customerLedgerModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl">
            <div class="modal-content border-0 shadow">
                <div class="modal-header text-white py-2.5 px-3" style="background-color: #0f172a !important;">
                    <div class="d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:notebook-bold-duotone" class="fs-22 text-info"></iconify-icon>
                        <h5 class="modal-title fw-bold text-white fs-15 mb-0" id="ledgerModalTitle">Customer Credit Ledger Statement</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">
                    <!-- Loader -->
                    <div id="ledgerModalLoader" class="text-center py-4">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="text-muted small mt-2">Loading customer ledger transactions...</p>
                    </div>

                    <!-- Ledger Content -->
                    <div id="ledgerModalContent" class="d-none">
                        <!-- Customer Info & Balance Banner -->
                        <div class="p-3 bg-light rounded-3 mb-3 border">
                            <div class="row align-items-center g-3">
                                <div class="col-md-6">
                                    <div class="fw-bold text-dark fs-16" id="ledCustName">—</div>
                                    <div class="text-muted fs-12">Mobile: <strong id="ledCustMobile">—</strong></div>
                                </div>
                                <div class="col-md-6 text-md-end">
                                    <div class="d-inline-flex gap-3">
                                        <div>
                                            <div class="text-muted fs-11 text-uppercase">Total Credit</div>
                                            <div class="fw-bold text-danger fs-14" id="ledTotalCredit">₹0.00</div>
                                        </div>
                                        <div>
                                            <div class="text-muted fs-11 text-uppercase">Total Paid</div>
                                            <div class="fw-bold text-success fs-14" id="ledTotalPaid">₹0.00</div>
                                        </div>
                                        <div>
                                            <div class="text-muted fs-11 text-uppercase">Current Outstanding</div>
                                            <div class="fw-bold text-primary fs-16" id="ledOutstanding">₹0.00</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Ledger Table -->
                        <div class="table-responsive rounded border mb-3" style="max-height: 420px;">
                            <table class="table table-hover align-middle table-ledger mb-0" style="font-size: 12px;">
                                <thead>
                                    <tr>
                                        <th class="text-center ps-3" style="width: 40px;">#</th>
                                        <th>Date & Time</th>
                                        <th>Reference No</th>
                                        <th>Transaction Type</th>
                                        <th class="text-end">Debit (Credit Given)</th>
                                        <th class="text-end">Credit (Paid)</th>
                                        <th class="text-center">Mode</th>
                                        <th class="text-end pe-3">Outstanding Balance</th>
                                    </tr>
                                </thead>
                                <tbody id="ledgerTableBody">
                                    <!-- Dynamic rows -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                    <button type="button" class="btn btn-sm btn-primary px-3" id="btnPrintLedger">
                        <iconify-icon icon="solar:printer-bold-duotone" class="align-middle me-1"></iconify-icon> Print Statement (A4)
                    </button>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    let receivePaymentModalInstance = null;
    let ledgerModalInstance = null;

    function openReceivePaymentModal(custId = null, custName = '', custMobile = '', outstanding = 0) {
        if (!receivePaymentModalInstance) {
            receivePaymentModalInstance = new bootstrap.Modal(document.getElementById('receivePaymentModal'));
        }

        let selectEl = document.getElementById('recPayCustomerId');
        if (custId) {
            selectEl.value = custId;
        } else {
            selectEl.value = '';
        }

        document.getElementById('recPayAmount').value = '';
        document.getElementById('recPayRemarks').value = '';
        updateOutstandingDisplay();

        receivePaymentModalInstance.show();
    }

    function onPaymentCustomerChange(select) {
        updateOutstandingDisplay();
    }

    function updateOutstandingDisplay() {
        let selectEl = document.getElementById('recPayCustomerId');
        let opt = selectEl.options[selectEl.selectedIndex];
        let outstanding = 0;
        if (opt && opt.value) {
            outstanding = parseFloat(opt.getAttribute('data-outstanding') || 0);
        }
        document.getElementById('recPayOutstandingDisplay').innerText = '₹' + outstanding.toFixed(2);
    }

    function submitReceivePayment(e) {
        e.preventDefault();

        let custId = document.getElementById('recPayCustomerId').value;
        let amount = parseFloat(document.getElementById('recPayAmount').value) || 0;
        let mode = document.querySelector('input[name="payment_mode"]:checked')?.value || 'Cash';
        let remarks = document.getElementById('recPayRemarks').value;

        if (!custId) {
            alert('Please select a customer.');
            return;
        }
        if (amount <= 0) {
            alert('Please enter a valid payment amount.');
            return;
        }

        let btn = document.getElementById('btnSubmitRecPay');
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing...';

        fetch("{{ route('adc.pos.customers.receive-payment') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                customer_id: custId,
                amount: amount,
                payment_mode: mode,
                remarks: remarks
            })
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<iconify-icon icon="solar:check-read-bold-duotone" class="align-middle me-1"></iconify-icon> Confirm & Save Payment';

            if (data.status) {
                receivePaymentModalInstance.hide();
                if (data.print_url) {
                    window.open(data.print_url, '_blank');
                }
                alert(data.message || 'Udhaar payment received successfully!');
                window.location.reload();
            } else {
                alert(data.message || 'Failed to record payment.');
            }
        })
        .catch(err => {
            console.error(err);
            btn.disabled = false;
            btn.innerHTML = '<iconify-icon icon="solar:check-read-bold-duotone" class="align-middle me-1"></iconify-icon> Confirm & Save Payment';
            alert('An error occurred while processing payment.');
        });
    }

    function openLedgerModal(customerId) {
        if (!ledgerModalInstance) {
            ledgerModalInstance = new bootstrap.Modal(document.getElementById('customerLedgerModal'));
        }

        document.getElementById('ledgerModalLoader').classList.remove('d-none');
        document.getElementById('ledgerModalContent').classList.add('d-none');
        ledgerModalInstance.show();

        fetch("{{ route('adc.pos.customers.ledger', ['id' => 999999]) }}".replace('999999', customerId))
            .then(res => res.json())
            .then(data => {
                if (!data.status || !data.customer) {
                    alert('Failed to load customer ledger.');
                    ledgerModalInstance.hide();
                    return;
                }

                let cust = data.customer;
                document.getElementById('ledgerModalTitle').innerText = 'Credit Ledger Statement - ' + cust.name;
                document.getElementById('ledCustName').innerText = cust.name;
                document.getElementById('ledCustMobile').innerText = cust.mobile;

                let creditGiven = parseFloat(cust.total_credit_given || 0);
                let paid = parseFloat(cust.total_payment_received || 0);
                let outstanding = parseFloat(cust.outstanding_balance || 0);

                document.getElementById('ledTotalCredit').innerText = '₹' + creditGiven.toFixed(2);
                document.getElementById('ledTotalPaid').innerText = '₹' + paid.toFixed(2);
                document.getElementById('ledOutstanding').innerText = '₹' + outstanding.toFixed(2);

                document.getElementById('btnPrintLedger').onclick = function() {
                    window.open(data.print_url, '_blank');
                };

                let tbody = document.getElementById('ledgerTableBody');
                tbody.innerHTML = '';

                let entries = data.entries || [];
                if (entries.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="8" class="text-center text-muted py-4">No ledger transactions found for this customer.</td></tr>';
                } else {
                    entries.forEach((row, index) => {
                        let dt = row.created_at ? new Date(row.created_at).toLocaleString('en-IN', { dateStyle: 'medium', timeStyle: 'short' }) : '—';
                        let debit = parseFloat(row.debit || 0);
                        let credit = parseFloat(row.credit || 0);
                        let balance = parseFloat(row.balance || 0);
                        let typeBadge = '<span class="badge bg-secondary">' + row.transaction_type + '</span>';

                        if (row.transaction_type === 'Credit Sale') {
                            typeBadge = '<span class="badge bg-danger">Credit Sale</span>';
                        } else if (row.transaction_type === 'Payment Received') {
                            typeBadge = '<span class="badge bg-success">Payment Received</span>';
                        } else if (row.transaction_type === 'Sales Return') {
                            typeBadge = '<span class="badge bg-info text-dark">Sales Return</span>';
                        }

                        tbody.innerHTML += `
                            <tr>
                                <td class="text-center ps-3 fw-bold text-muted">${index + 1}</td>
                                <td>${dt}</td>
                                <td class="fw-bold font-monospace">${row.reference_no}</td>
                                <td>${typeBadge}</td>
                                <td class="text-end fw-bold text-danger">${debit > 0 ? '₹' + debit.toFixed(2) : '—'}</td>
                                <td class="text-end fw-bold text-success">${credit > 0 ? '₹' + credit.toFixed(2) : '—'}</td>
                                <td class="text-center"><span class="badge bg-light text-dark border">${row.payment_mode || 'Credit'}</span></td>
                                <td class="text-end pe-3 fw-bold ${balance > 0 ? 'text-danger' : 'text-success'}">₹${balance.toFixed(2)}</td>
                            </tr>
                        `;
                    });
                }

                document.getElementById('ledgerModalLoader').classList.add('d-none');
                document.getElementById('ledgerModalContent').classList.remove('d-none');
            })
            .catch(err => {
                console.error(err);
                alert('Error loading ledger.');
                ledgerModalInstance.hide();
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
