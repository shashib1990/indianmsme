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
            <div class="card shadow-sm border-0 rounded-3">
                <div class="card-body py-3 px-3 px-md-4">
                    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 16px;">
                        <div>
                            <h4 class="fw-bold text-slate-800 mb-1 fs-16 text-nowrap" style="font-family: 'Outfit', sans-serif;">
                                <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" class="text-primary align-middle me-1 fs-19"></iconify-icon>
                                Customers & Udhaar Credit Ledger
                            </h4>
                            <p class="text-muted small mb-0 fs-12 text-nowrap">Manage customer profiles, record udhaar payments, and view detailed credit ledgers.</p>
                        </div>
                        <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                            {{-- 1. Broadcast Message --}}
                            <button type="button" class="btn btn-sm text-white fw-semibold d-inline-flex align-items-center shadow-sm text-nowrap" id="openBroadcastModalBtn" style="background: linear-gradient(135deg, #7c3aed, #5b21b6); border: none; border-radius: 8px; padding: 6.5px 14px; font-size: 12.5px; gap: 6px;" onclick="openBroadcastModal()">
                                <iconify-icon icon="solar:volume-loud-bold-duotone" class="fs-16"></iconify-icon>
                                <span>Broadcast Message</span>
                            </button>

                            {{-- 2. Search Form --}}
                            <form method="GET" action="{{ route('adc.pos.customers') }}" class="d-flex align-items-center text-nowrap m-0">
                                <div class="input-group input-group-sm shadow-sm" style="width: 215px;">
                                    <input type="text" name="search" class="form-control form-control-sm border-end-0" style="font-size: 12px; border-radius: 8px 0 0 8px; padding: 6px 10px;" placeholder="Search customer / phone..." value="{{ $search }}">
                                    <button class="btn btn-primary btn-sm px-2.5" type="submit" style="font-size: 12px; border-radius: 0 8px 8px 0;">
                                        <iconify-icon icon="solar:magnifer-linear" class="align-middle me-0.5"></iconify-icon> Search
                                    </button>
                                    @if($search)
                                        <a href="{{ route('adc.pos.customers') }}" class="btn btn-outline-secondary btn-sm px-2 ms-1" style="border-radius: 8px;" title="Reset Filters">
                                            <iconify-icon icon="solar:restart-bold-duotone" class="align-middle"></iconify-icon>
                                        </a>
                                    @endif
                                </div>
                            </form>

                            {{-- 3. Receive Udhaar Payment --}}
                            <button type="button" class="btn btn-sm btn-success fw-semibold d-inline-flex align-items-center text-nowrap shadow-sm" style="padding: 6.5px 14px; font-size: 12.5px; border-radius: 8px; gap: 6px;" onclick="openReceivePaymentModal()">
                                <iconify-icon icon="solar:card-send-bold-duotone" class="align-middle fs-16"></iconify-icon>
                                <span>Receive Udhaar Payment</span>
                            </button>

                            {{-- 4. POS Terminal --}}
                            <a href="{{ route('adc.pos.retail') }}" class="btn btn-sm btn-outline-primary fw-semibold d-inline-flex align-items-center text-nowrap shadow-sm" style="padding: 6.5px 14px; font-size: 12.5px; border-radius: 8px; gap: 6px;">
                                <iconify-icon icon="solar:shop-bold-duotone" class="align-middle fs-16"></iconify-icon>
                                <span>POS Terminal</span>
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
                            <th class="text-center" style="width: 150px;">Actions</th>
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
                                    <div class="d-inline-flex align-items-center justify-content-center" style="gap: 8px;">
                                        {{-- View Ledger --}}
                                        <button type="button" class="btn btn-sm btn-outline-primary btn-icon rounded-circle shadow-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="View Customer Credit Ledger" onclick="openLedgerModal({{ $cust->id }})">
                                            <iconify-icon icon="solar:notebook-bold-duotone" class="fs-16"></iconify-icon>
                                        </button>

                                        {{-- Receive Udhaar Payment --}}
                                        <button type="button" class="btn btn-sm btn-success btn-icon rounded-circle shadow-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Receive Udhaar Payment" onclick="openReceivePaymentModal({{ $cust->id }}, '{{ addslashes($cust->name) }}', '{{ $cust->mobile }}', {{ $outstanding }})">
                                            <iconify-icon icon="solar:card-send-bold-duotone" class="fs-16"></iconify-icon>
                                        </button>

                                        {{-- Send Message --}}
                                        <button type="button" class="btn btn-sm btn-primary btn-icon rounded-circle shadow-sm" data-bs-toggle="tooltip" data-bs-placement="top" title="Send Message" onclick="openSendMessageModal({{ $cust->id }}, '{{ addslashes($cust->name) }}', '{{ $cust->mobile }}')">
                                            <iconify-icon icon="solar:plain-bold-duotone" class="fs-16"></iconify-icon>
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

    <!-- Modal: Send Message to Customer -->
    <div class="modal fade" id="sendMessageModal" tabindex="-1" aria-labelledby="sendMessageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header text-white py-3 px-3" style="background: linear-gradient(135deg, #0f172a, #1e3a8a);">
                    <div class="d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:plain-bold-duotone" class="fs-22 text-white"></iconify-icon>
                        <h5 class="modal-title fw-bold text-white fs-16 mb-0" id="sendMessageModalLabel">Send Message</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-3">
                    <input type="hidden" id="msgCustomerId">

                    <!-- Customer Name -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-slate-800 fs-13 mb-1">Customer Name</label>
                        <input type="text" class="form-control" id="msgCustomerName" readonly style="background-color: #f1f5f9; color: #1e293b; font-weight: 600; cursor: not-allowed; border-radius: 8px;">
                    </div>

                    <!-- Mobile Number -->
                    <div class="mb-3">
                        <label class="form-label fw-bold text-slate-800 fs-13 mb-1">Mobile Number</label>
                        <input type="text" class="form-control font-monospace" id="msgCustomerMobile" readonly style="background-color: #f1f5f9; color: #1e293b; font-weight: 600; cursor: not-allowed; border-radius: 8px;">
                    </div>

                    <!-- Message Textarea -->
                    <div class="mb-2">
                        <label class="form-label fw-bold text-slate-800 fs-13 mb-1 required">Message</label>
                        <textarea class="form-control" id="msgCustomerText" rows="6" maxlength="2000" placeholder="Type message here..." style="border-radius: 8px; font-size: 13px;"></textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted fs-11">Maximum 2000 characters</small>
                            <small class="text-muted fs-11" id="msgCharCount">0 / 2000</small>
                        </div>
                    </div>

                    <div class="alert alert-danger mt-3 mb-0 py-2 fs-13 d-none" id="msgErrorAlert"></div>
                    <div class="alert alert-success mt-3 mb-0 py-2 fs-13 d-none" id="msgSuccessAlert"></div>
                </div>

                <div class="modal-footer py-2.5 px-3 bg-light border-top d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary px-3" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                    <button type="button" class="btn btn-primary px-4 fw-semibold d-inline-flex align-items-center gap-1.5" id="btnSubmitCustomerMessage" onclick="submitCustomerMessage()" style="background: linear-gradient(135deg, #2563eb, #1d4ed8); border: none; border-radius: 8px;">
                        <iconify-icon icon="solar:plain-bold-duotone" class="fs-16"></iconify-icon>
                        <span>Send Message</span>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal: Broadcast Message to All Customers -->
    <div class="modal fade" id="broadcastMessageModal" tabindex="-1" aria-labelledby="broadcastMessageModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow" style="border-radius: 16px; overflow: hidden;">
                <div class="modal-header text-white py-3 px-3" style="background: linear-gradient(135deg, #5b21b6, #7c3aed);">
                    <div class="d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:volume-loud-bold-duotone" class="fs-22 text-white"></iconify-icon>
                        <h5 class="modal-title fw-bold text-white fs-16 mb-0" id="broadcastMessageModalLabel">Broadcast Message</h5>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-3">
                    <div class="alert py-2.5 px-3 fs-13 d-flex align-items-center gap-2 rounded-3 mb-3 border-0" style="background-color: #f5f3ff; border-left: 4px solid #7c3aed !important; color: #5b21b6;">
                        <iconify-icon icon="solar:info-circle-bold-duotone" class="fs-20 flex-shrink-0"></iconify-icon>
                        <div>This message will be sent to all customers in your directory who have a valid mobile number.</div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label fw-bold text-slate-800 fs-13 mb-1 required">Broadcast Message</label>
                        <textarea class="form-control" id="broadcastMessageText" rows="6" maxlength="2000" placeholder="Type broadcast message here..." style="border-radius: 8px; font-size: 13px;"></textarea>
                        <div class="d-flex justify-content-between mt-1">
                            <small class="text-muted fs-11">Maximum 2000 characters</small>
                            <small class="text-muted fs-11" id="broadcastCharCount">0 / 2000</small>
                        </div>
                    </div>

                    <div class="alert alert-danger mt-3 mb-0 py-2 fs-13 d-none" id="broadcastErrorAlert"></div>
                    <div class="alert alert-success mt-3 mb-0 py-2 fs-13 d-none" id="broadcastSuccessAlert"></div>

                    <!-- Progress Bar Wrap -->
                    <div class="mt-3 p-3 rounded-3 border bg-light d-none" id="broadcastProgressWrap">
                        <!-- Progress Header -->
                        <div class="d-flex justify-content-between align-items-center mb-1.5">
                            <div class="d-flex align-items-center gap-1.5">
                                <span class="spinner-grow spinner-grow-sm text-primary" id="broadcastProgressSpinner" role="status"></span>
                                <small class="fw-bold text-slate-800 fs-12" id="broadcastProgressText">Starting broadcast delivery...</small>
                            </div>
                            <span class="badge fs-12 fw-bold text-white px-2.5 py-1 rounded-pill" id="broadcastProgressPercent" style="background: linear-gradient(135deg, #7c3aed, #5b21b6);">0%</span>
                        </div>

                        <!-- Progress Bar -->
                        <div class="progress my-2" style="height: 12px; border-radius: 999px; background-color: #e2e8f0;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated" id="broadcastProgressBar" role="progressbar" style="width: 0%; background: linear-gradient(135deg, #7c3aed, #5b21b6); transition: width 0.3s ease;"></div>
                        </div>

                        <!-- Live Counters -->
                        <div class="d-flex justify-content-between align-items-center mt-2 fs-12 flex-wrap gap-1">
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                <iconify-icon icon="solar:check-circle-bold" class="align-middle me-1"></iconify-icon> Sent: <strong id="broadcastSentCount">0</strong>
                            </span>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-1">
                                <iconify-icon icon="solar:close-circle-bold" class="align-middle me-1"></iconify-icon> Failed / Invalid: <strong id="broadcastFailedCount">0</strong>
                            </span>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1">
                                <iconify-icon icon="solar:users-group-two-rounded-bold" class="align-middle me-1"></iconify-icon> Total: <strong id="broadcastTotalDisplay">0</strong>
                            </span>
                        </div>

                        <!-- Current Customer Live Status Box -->
                        <div class="mt-2.5 p-2 rounded-2 bg-white border d-flex align-items-center justify-content-between fs-12 shadow-xs" id="broadcastCurrentCustomerBox">
                            <div class="text-truncate me-2">
                                <iconify-icon icon="solar:user-circle-bold-duotone" class="text-primary align-middle fs-15 me-1"></iconify-icon>
                                <span class="text-muted">Target:</span> <strong class="text-slate-800" id="broadcastTargetName">Initializing...</strong>
                                <span class="font-monospace text-muted ms-1" id="broadcastTargetMobile"></span>
                            </div>
                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle" id="broadcastTargetStatus">
                                In Progress...
                            </span>
                        </div>
                    </div>
                </div>

                <div class="modal-footer py-2.5 px-3 bg-light border-top d-flex justify-content-end gap-2">
                    <button type="button" class="btn btn-secondary px-3" id="broadcastCancelBtn" data-bs-dismiss="modal" style="border-radius: 8px;">Cancel</button>
                    <button type="button" class="btn text-white px-4 fw-semibold d-inline-flex align-items-center gap-1.5" id="broadcastSubmitBtn" onclick="submitBroadcastMessage()" style="background: linear-gradient(135deg, #7c3aed, #5b21b6); border: none; border-radius: 8px;">
                        <iconify-icon icon="solar:volume-loud-bold-duotone" class="fs-16"></iconify-icon>
                        <span>Send Broadcast</span>
                    </button>
                </div>
            </div>
        </div>
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
    let sendMessageModalInstance = null;
    let broadcastModalInstance = null;
    let broadcastRunning = false;
    let broadcastSent = 0;
    let broadcastFailed = 0;
    let broadcastProcessed = 0;
    let broadcastTotal = 0;
    let broadcastLastId = 0;

    function openBroadcastModal() {
        if (broadcastRunning) {
            return;
        }

        if (!broadcastModalInstance) {
            broadcastModalInstance = new bootstrap.Modal(document.getElementById('broadcastMessageModal'));
        }

        document.getElementById('broadcastMessageText').value = '';
        document.getElementById('broadcastMessageText').disabled = false;
        document.getElementById('broadcastCharCount').innerText = '0 / 2000';

        document.getElementById('broadcastErrorAlert').classList.add('d-none');
        document.getElementById('broadcastErrorAlert').innerText = '';
        document.getElementById('broadcastSuccessAlert').classList.add('d-none');
        document.getElementById('broadcastSuccessAlert').innerText = '';

        document.getElementById('broadcastProgressWrap').classList.add('d-none');
        document.getElementById('broadcastProgressBar').style.width = '0%';
        document.getElementById('broadcastProgressPercent').innerText = '0%';
        document.getElementById('broadcastSentCount').innerText = '0';
        document.getElementById('broadcastFailedCount').innerText = '0';
        document.getElementById('broadcastTotalDisplay').innerText = '0';
        document.getElementById('broadcastProgressText').innerText = 'Starting broadcast...';
        document.getElementById('broadcastTargetName').innerText = 'Initializing...';
        document.getElementById('broadcastTargetMobile').innerText = '';
        const targetStatus = document.getElementById('broadcastTargetStatus');
        if (targetStatus) {
            targetStatus.className = 'badge bg-warning-subtle text-warning border border-warning-subtle';
            targetStatus.innerHTML = 'In Progress...';
        }
        const spinner = document.getElementById('broadcastProgressSpinner');
        if (spinner) spinner.classList.remove('d-none');

        const submitBtn = document.getElementById('broadcastSubmitBtn');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<iconify-icon icon="solar:volume-loud-bold-duotone" class="fs-16"></iconify-icon> <span>Send Broadcast</span>';

        const cancelBtn = document.getElementById('broadcastCancelBtn');
        cancelBtn.disabled = false;
        cancelBtn.innerText = 'Cancel';

        broadcastModalInstance.show();
    }

    function submitBroadcastMessage() {
        const message = document.getElementById('broadcastMessageText').value.trim();
        const errAlert = document.getElementById('broadcastErrorAlert');
        const succAlert = document.getElementById('broadcastSuccessAlert');

        errAlert.classList.add('d-none');
        errAlert.innerText = '';
        succAlert.classList.add('d-none');
        succAlert.innerText = '';

        if (!message) {
            errAlert.innerText = 'Please enter a broadcast message before sending.';
            errAlert.classList.remove('d-none');
            return;
        }

        broadcastRunning = true;
        broadcastSent = 0;
        broadcastFailed = 0;
        broadcastProcessed = 0;
        broadcastTotal = 0;
        broadcastLastId = 0;

        document.getElementById('broadcastProgressWrap').classList.remove('d-none');
        document.getElementById('broadcastMessageText').disabled = true;
        document.getElementById('broadcastCancelBtn').disabled = true;

        const submitBtn = document.getElementById('broadcastSubmitBtn');
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Broadcasting...';

        sendBroadcastBatch(message);
    }

    function sendBroadcastBatch(message) {
        fetch("{{ route('adc.pos.customers.broadcast-message') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                message: message,
                last_id: broadcastLastId
            })
        })
        .then(res => res.json())
        .then(data => {
            if (!data.status) {
                stopBroadcastWithError(data.message || 'Broadcast could not be sent.');
                return;
            }

            broadcastSent += parseInt(data.batch_sent || 0);
            broadcastFailed += parseInt(data.batch_failed || 0);
            broadcastProcessed += parseInt(data.batch_processed || 0);
            broadcastLastId = parseInt(data.next_last_id || broadcastLastId);

            if (broadcastTotal === 0) {
                broadcastTotal = parseInt(data.total || 0);
            }
            document.getElementById('broadcastTotalDisplay').innerText = broadcastTotal;
            document.getElementById('broadcastSentCount').innerText = broadcastSent;
            document.getElementById('broadcastFailedCount').innerText = broadcastFailed;

            // Update current customer delivery card in real-time
            if (data.customer_name) {
                document.getElementById('broadcastTargetName').innerText = data.customer_name;
                document.getElementById('broadcastTargetMobile').innerText = data.customer_mobile ? `(${data.customer_mobile})` : '';

                const statusBadge = document.getElementById('broadcastTargetStatus');
                if (statusBadge) {
                    if (data.item_status === 'sent') {
                        statusBadge.className = 'badge bg-success-subtle text-success border border-success-subtle';
                        statusBadge.innerHTML = '✔ Sent';
                    } else if (data.item_status === 'invalid_mobile') {
                        statusBadge.className = 'badge bg-warning-subtle text-warning-emphasis border border-warning-subtle';
                        statusBadge.innerHTML = '⚠ Invalid Mobile';
                    } else {
                        statusBadge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle';
                        statusBadge.innerHTML = '✖ Failed';
                    }
                }
            }

            let percent = 100;
            if (broadcastTotal > 0) {
                percent = Math.min(100, Math.round((broadcastProcessed / broadcastTotal) * 100));
            }

            document.getElementById('broadcastProgressBar').style.width = percent + '%';
            document.getElementById('broadcastProgressPercent').innerText = percent + '%';
            document.getElementById('broadcastProgressText').innerText = `Sending: ${broadcastProcessed} of ${broadcastTotal} processed (${broadcastSent} Sent)`;

            if (data.has_more) {
                setTimeout(function () {
                    if (broadcastRunning) {
                        sendBroadcastBatch(message);
                    }
                }, 80);
            } else {
                broadcastRunning = false;
                document.getElementById('broadcastProgressBar').style.width = '100%';
                document.getElementById('broadcastProgressPercent').innerText = '100%';
                document.getElementById('broadcastProgressText').innerText = `🎉 Broadcast Completed (${broadcastSent} Sent)`;

                const spinner = document.getElementById('broadcastProgressSpinner');
                if (spinner) spinner.classList.add('d-none');

                const statusBadge = document.getElementById('broadcastTargetStatus');
                if (statusBadge) {
                    statusBadge.className = 'badge bg-success text-white';
                    statusBadge.innerHTML = 'Done';
                }

                const submitBtn = document.getElementById('broadcastSubmitBtn');
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<iconify-icon icon="solar:check-circle-bold" class="fs-16"></iconify-icon> <span>Completed</span>';

                const cancelBtn = document.getElementById('broadcastCancelBtn');
                cancelBtn.disabled = false;
                cancelBtn.innerText = 'Close';
                document.getElementById('broadcastMessageText').disabled = false;

                const succAlert = document.getElementById('broadcastSuccessAlert');
                succAlert.innerText = `All broadcast messages delivered! Successfully sent: ${broadcastSent}` + (broadcastFailed > 0 ? `, Failed/Skipped: ${broadcastFailed}.` : '.');
                succAlert.classList.remove('d-none');
            }
        })
        .catch(err => {
            console.error(err);
            stopBroadcastWithError('An unexpected network error occurred while broadcasting. Please try again.');
        });
    }

    function stopBroadcastWithError(errorMessage) {
        broadcastRunning = false;
        const errAlert = document.getElementById('broadcastErrorAlert');
        errAlert.innerText = errorMessage;
        errAlert.classList.remove('d-none');

        const submitBtn = document.getElementById('broadcastSubmitBtn');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<iconify-icon icon="solar:restart-bold" class="fs-16"></iconify-icon> <span>Retry Broadcast</span>';

        document.getElementById('broadcastCancelBtn').disabled = false;
        document.getElementById('broadcastMessageText').disabled = false;
    }

    function openSendMessageModal(custId, custName, custMobile) {
        if (!sendMessageModalInstance) {
            sendMessageModalInstance = new bootstrap.Modal(document.getElementById('sendMessageModal'));
        }

        document.getElementById('msgCustomerId').value = custId || '';
        document.getElementById('msgCustomerName').value = custName || 'Customer';
        document.getElementById('msgCustomerMobile').value = custMobile || '';
        document.getElementById('msgCustomerText').value = '';
        document.getElementById('msgCharCount').innerText = '0 / 2000';

        const errAlert = document.getElementById('msgErrorAlert');
        errAlert.classList.add('d-none');
        errAlert.innerText = '';

        const succAlert = document.getElementById('msgSuccessAlert');
        succAlert.classList.add('d-none');
        succAlert.innerText = '';

        const submitBtn = document.getElementById('btnSubmitCustomerMessage');
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<iconify-icon icon="solar:plain-bold-duotone" class="fs-16"></iconify-icon> <span>Send Message</span>';

        sendMessageModalInstance.show();
    }

    function submitCustomerMessage() {
        const custId = document.getElementById('msgCustomerId').value;
        const custName = document.getElementById('msgCustomerName').value;
        const custMobile = document.getElementById('msgCustomerMobile').value;
        const message = document.getElementById('msgCustomerText').value.trim();

        const errAlert = document.getElementById('msgErrorAlert');
        const succAlert = document.getElementById('msgSuccessAlert');
        const submitBtn = document.getElementById('btnSubmitCustomerMessage');

        errAlert.classList.add('d-none');
        errAlert.innerText = '';
        succAlert.classList.add('d-none');
        succAlert.innerText = '';

        if (!custMobile) {
            errAlert.innerText = 'Mobile number is required for this customer.';
            errAlert.classList.remove('d-none');
            return;
        }

        if (!message) {
            errAlert.innerText = 'Please enter a message before sending.';
            errAlert.classList.remove('d-none');
            return;
        }

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';

        fetch("{{ route('adc.pos.customers.send-message') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                customer_id: custId,
                customer_name: custName,
                customer_mobile: custMobile,
                message: message
            })
        })
        .then(res => res.json())
        .then(data => {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<iconify-icon icon="solar:plain-bold-duotone" class="fs-16"></iconify-icon> <span>Send Message</span>';

            if (data.status) {
                succAlert.innerText = data.message || 'Message sent successfully!';
                succAlert.classList.remove('d-none');

                setTimeout(function () {
                    if (sendMessageModalInstance) {
                        sendMessageModalInstance.hide();
                    }
                }, 1200);
            } else {
                errAlert.innerText = data.message || 'Failed to send message. Please try again.';
                errAlert.classList.remove('d-none');
            }
        })
        .catch(err => {
            console.error(err);
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<iconify-icon icon="solar:plain-bold-duotone" class="fs-16"></iconify-icon> <span>Send Message</span>';
            errAlert.innerText = 'An error occurred while sending message. Please try again.';
            errAlert.classList.remove('d-none');
        });
    }

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

        const msgInput = document.getElementById('msgCustomerText');
        if (msgInput) {
            msgInput.addEventListener('input', function() {
                const len = this.value.length;
                const counter = document.getElementById('msgCharCount');
                if (counter) {
                    counter.innerText = len + ' / 2000';
                }
            });
        }

        const broadcastMsgInput = document.getElementById('broadcastMessageText');
        if (broadcastMsgInput) {
            broadcastMsgInput.addEventListener('input', function() {
                const len = this.value.length;
                const counter = document.getElementById('broadcastCharCount');
                if (counter) {
                    counter.innerText = len + ' / 2000';
                }
            });
        }
    });
</script>
@endpush
