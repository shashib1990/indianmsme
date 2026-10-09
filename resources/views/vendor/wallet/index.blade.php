@extends('layouts.vendor')

@section('title', 'My Wallet')
@section('page-title', 'Wallet & Transactions')

@section('content')
    <!-- Action Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-2.5 px-3">
                    <div class="row align-items-center g-2">
                        <div class="col-md-6">
                            <h5 class="fw-bold text-slate-800 mb-0" style="font-family: 'Outfit', sans-serif;">Wallet & Transactions</h5>
                            <p class="text-muted small mb-0 fs-12">Track your seller wallet balance, verify credit top-ups, and review transaction history.</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <button type="button" class="btn btn-sm btn-primary py-1 px-3 me-1" onclick="selectRequestType('topup')">
                                <iconify-icon icon="solar:card-send-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon>
                                + Top-Up Wallet
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-purple py-1 px-3" style="color: #6b21a8; border-color: #d8b4fe;" onclick="selectRequestType('withdrawal')">
                                <iconify-icon icon="solar:card-receive-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon>
                                ↓ Request Payout / Withdrawal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @php
        $transferredCount = $requests->where('status', 'TRANSFERRED')->count();
    @endphp
    @if($transferredCount > 0)
        <div class="alert alert-info border-0 shadow-sm d-flex align-items-center justify-content-between mb-3" style="background-color: #e0f2fe; border-left: 4px solid #0284c7 !important;">
            <div class="d-flex align-items-center me-2">
                <iconify-icon icon="solar:card-send-bold-duotone" class="fs-24 text-info me-2.5 flex-shrink-0"></iconify-icon>
                <div>
                    <strong class="text-slate-800 fs-14">Payout Transferred by Admin!</strong>
                    <div class="small text-slate-700 fs-12">You have <strong>{{ $transferredCount }}</strong> withdrawal request(s) transferred to your bank account. Please verify UTR & payment receipt and confirm.</div>
                </div>
            </div>
            <button type="button" class="btn btn-sm btn-info text-white fw-bold px-3 py-1 flex-shrink-0" onclick="document.getElementById('req-tab').click();">
                <iconify-icon icon="solar:shield-check-bold" class="align-middle me-1"></iconify-icon> Verify Now
            </button>
        </div>
    @endif

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-3" role="alert">
            <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-20 align-middle me-2"></iconify-icon>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show mb-3" role="alert">
            <ul class="mb-0 ps-3">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="row g-3">
        <!-- Left Column: Balance, Bank Details & History -->
        <div class="col-xl-8 col-lg-7">
            <div class="row g-3 align-items-stretch">
                <!-- Balance Card -->
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm h-100 text-white" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); border-radius: 10px;">
                        <div class="card-body p-3 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <p class="mb-0 text-white-50 fw-semibold text-uppercase tracking-wider fs-10">Wallet Balance</p>
                                    <iconify-icon icon="solar:wallet-money-bold-duotone" class="fs-22 text-white-50"></iconify-icon>
                                </div>
                                <h3 class="mb-0 text-white fw-bold fs-22" style="font-family: 'Outfit', sans-serif;">
                                    ₹{{ number_format($walletBalance, 2) }}
                                </h3>
                            </div>

                            <div class="mt-2 pt-1 border-top border-white border-opacity-10 d-flex align-items-center justify-content-between">
                                <span class="text-white-50 fs-10">
                                    <iconify-icon icon="solar:shield-check-bold" class="align-middle me-1 text-info fs-12"></iconify-icon>
                                    Active Seller Account
                                </span>
                                <a href="#request-topup-section" class="btn btn-xs btn-light text-primary fw-semibold fs-10 px-2 py-0.5 rounded-pill shadow-xs">
                                    + Top-up / Payout
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Bank Transfer Details Card -->
                @php
                    $adminBankName = \App\Models\SystemSetting::getVal('admin_bank_name', 'HDFC Bank');
                    $adminAccountName = \App\Models\SystemSetting::getVal('admin_account_name', 'ASC DISTRIBUTOR PVT LTD');
                    $adminAccountNumber = \App\Models\SystemSetting::getVal('admin_account_number', '50200089652670');
                    $adminIfscCode = \App\Models\SystemSetting::getVal('admin_ifsc_code', 'HDFC0000886');
                    $adminBankBranch = \App\Models\SystemSetting::getVal('admin_bank_branch', 'Naraina Plaza, Sec A, Rohini, Delhi');
                    $adminUpiId = \App\Models\SystemSetting::getVal('admin_upi_id', 'ascdistributor@hdfcbank');

                    $adminQrCodeRaw = \App\Models\SystemSetting::getVal('admin_qr_code');
                    $adminQrCode = asset('admin-assets/images/qr-code.png');
                    if (!empty($adminQrCodeRaw)) {
                        $trimmedQr = trim($adminQrCodeRaw);
                        if (filter_var($trimmedQr, FILTER_VALIDATE_URL) || \Illuminate\Support\Str::startsWith($trimmedQr, ['http://', 'https://', '//'])) {
                            $adminQrCode = $trimmedQr;
                        } elseif (\Illuminate\Support\Str::startsWith($trimmedQr, 'storage/')) {
                            $adminQrCode = asset($trimmedQr);
                        } elseif (\Illuminate\Support\Str::startsWith($trimmedQr, '/storage/')) {
                            $adminQrCode = asset(ltrim($trimmedQr, '/'));
                        } else {
                            $adminQrCode = asset('storage/' . $trimmedQr);
                        }
                    }
                @endphp
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 py-2 px-3 d-flex align-items-center gap-2 border-bottom">
                            <iconify-icon icon="solar:card-bold-duotone" class="text-primary fs-18"></iconify-icon>
                            <h6 class="fw-bold text-slate-800 mb-0 fs-13">ADPL Bank Details & QR Code</h6>
                        </div>
                        <div class="card-body p-2.5 fs-12">
                            <div class="row g-2 align-items-center">
                                <div class="col-8">
                                    <table class="table table-sm table-borderless mb-0">
                                        <tr>
                                            <td class="text-muted py-0.5 pe-1" style="width: 80px;">Bank</td>
                                            <td class="fw-semibold text-slate-800 py-0.5">: {{ $adminBankName }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted py-0.5 pe-1">Account</td>
                                            <td class="fw-semibold text-slate-800 py-0.5 text-truncate" style="max-width: 150px;" title="{{ $adminAccountName }}">: {{ $adminAccountName }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted py-0.5 pe-1">A/C No.</td>
                                            <td class="fw-semibold text-slate-800 py-0.5">: {{ $adminAccountNumber }}</td>
                                        </tr>
                                        <tr>
                                            <td class="text-muted py-0.5 pe-1">IFSC</td>
                                            <td class="fw-semibold text-slate-800 py-0.5">: {{ $adminIfscCode }}</td>
                                        </tr>
                                        @if(!empty($adminUpiId))
                                        <tr>
                                            <td class="text-muted py-0.5 pe-1">UPI ID</td>
                                            <td class="fw-semibold text-primary py-0.5">: {{ $adminUpiId }}</td>
                                        </tr>
                                        @endif
                                    </table>
                                </div>
                                <div class="col-4 text-center border-start ps-2">
                                    <a href="{{ $adminQrCode }}" target="_blank" title="Click to view QR code">
                                        <img src="{{ $adminQrCode }}" alt="ADPL UPI QR Code" class="img-fluid rounded border" style="max-height: 85px; object-fit: contain;">
                                    </a>
                                    <div class="text-muted small mt-1" style="font-size: 9px;">Scan to Pay</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs for History/Requests -->
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-white border-0 py-3 border-bottom d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3">
                    <ul class="nav nav-tabs card-header-tabs gap-1" id="walletTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active fw-semibold" id="tx-tab" data-bs-toggle="tab" data-bs-target="#tx-pane" type="button" role="tab">
                                Transaction History
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-semibold" id="req-tab" data-bs-toggle="tab" data-bs-target="#req-pane" type="button" role="tab">
                                Wallet Requests History
                            </button>
                        </li>
                    </ul>

                    <form id="filterForm" action="{{ route('vendor.wallet.index') }}" method="GET" class="d-flex align-items-center gap-2 mb-0">
                        <input type="hidden" name="sort" id="formSort" value="{{ $sort }}">
                        <input type="hidden" name="order" id="formOrder" value="{{ $order }}">
                        <div class="input-group input-group-sm" style="max-width: 200px;">
                            <span class="input-group-text bg-white border-end-0 py-1">
                                <iconify-icon icon="solar:magnifer-bold-duotone" class="text-muted"></iconify-icon>
                            </span>
                            <input type="text" name="search" class="form-control form-control-sm border-start-0 py-1" placeholder="Search..." value="{{ $search }}">
                        </div>
                        <select name="limit" class="form-select form-select-sm w-auto py-1" onchange="this.form.submit()">
                            <option value="10" {{ $limit == 10 ? 'selected' : '' }}>10 / page</option>
                            <option value="15" {{ $limit == 15 ? 'selected' : '' }}>15 / page</option>
                            <option value="25" {{ $limit == 25 ? 'selected' : '' }}>25 / page</option>
                            <option value="50" {{ $limit == 50 ? 'selected' : '' }}>50 / page</option>
                        </select>
                        @if($search)
                            <a href="{{ route('vendor.wallet.index') }}" class="btn btn-sm btn-outline-danger py-1 px-2">Clear</a>
                        @endif
                    </form>
                </div>

                <div class="card-body p-0">
                    <div class="tab-content">

                        <!-- Tab 1: Transactions -->
                        <div class="tab-pane fade show active" id="tx-pane" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="bg-light bg-opacity-50 border-top border-bottom">
                                        <tr>
                                            <th class="ps-3">Date</th>
                                            <th>Description</th>
                                            <th>Type</th>
                                            <th class="text-end pe-3">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($transactions as $tx)
                                            <tr>
                                                <td class="ps-3 text-muted small">{{ $tx->created_at->format('d M Y, h:i A') }}</td>
                                                <td>{{ $tx->description }}</td>
                                                <td>
                                                    <span class="badge bg-{{ $tx->type === 'credit' ? 'success' : 'danger' }}-subtle text-{{ $tx->type === 'credit' ? 'success' : 'danger' }} px-2 py-1 fs-11">
                                                        {{ strtoupper($tx->type) }}
                                                    </span>
                                                </td>
                                                <td class="text-end pe-3 fw-bold {{ $tx->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                                    {{ $tx->type === 'credit' ? '+' : '-' }} ₹{{ number_format($tx->amount, 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted small">No transactions yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center p-3 border-top">
                                <span class="text-muted small">Showing {{ $transactions->firstItem() ?? 0 }} to {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} entries</span>
                                @if($transactions->hasPages())
                                    {{ $transactions->links() }}
                                @endif
                            </div>
                        </div>

                        <!-- Tab 2: Requests -->
                        <div class="tab-pane fade" id="req-pane" role="tabpanel">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0 align-middle">
                                    <thead class="bg-light bg-opacity-50 border-top border-bottom">
                                        <tr>
                                            <th class="ps-3">Submitted</th>
                                            <th>Type</th>
                                            <th>Amount</th>
                                            <th>Reference / Bank Details</th>
                                            <th>Receipt / Proof</th>
                                            <th>Status</th>
                                            <th>Remarks</th>
                                            <th class="text-end pe-3">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($requests as $req)
                                            <tr>
                                                <td class="ps-3 text-muted small">
                                                    <span class="d-block text-slate-800 fw-medium">{{ $req->created_at->format('d M Y') }}</span>
                                                    <span class="fs-11 text-muted">{{ $req->created_at->format('h:i A') }}</span>
                                                </td>
                                                <td>
                                                    @if($req->type === 'topup')
                                                        <span class="badge px-2 py-1 fs-11 fw-semibold" style="color: #0369a1; background-color: #e0f2fe; border: 1px solid #bae6fd;">
                                                            <iconify-icon icon="solar:card-send-bold" class="align-middle me-0.5"></iconify-icon> TOP-UP
                                                        </span>
                                                    @else
                                                        <span class="badge px-2 py-1 fs-11 fw-semibold" style="color: #6b21a8; background-color: #f3e8ff; border: 1px solid #e9d5ff;">
                                                            <iconify-icon icon="solar:card-receive-bold" class="align-middle me-0.5"></iconify-icon> WITHDRAWAL
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="fw-bold {{ $req->type === 'topup' ? 'text-success' : 'text-danger' }}">
                                                    ₹{{ number_format($req->amount, 2) }}
                                                </td>
                                                <td>
                                                    @if($req->type === 'topup')
                                                        <span class="text-muted fs-11 d-block">Submitted UTR:</span>
                                                        <code class="small fw-semibold text-primary">{{ $req->utr_number ?: 'N/A' }}</code>
                                                    @else
                                                        <div class="small">
                                                            <span class="fw-semibold text-slate-800 d-block">{{ $req->bank_name }} (A/C: {{ $req->bank_account_number }})</span>
                                                            <span class="font-monospace text-muted fs-11 d-block">IFSC: {{ $req->ifsc_code }}</span>
                                                            @if($req->utr_number)
                                                                <div class="mt-0.5 font-monospace text-success fs-11 fw-semibold">
                                                                    <iconify-icon icon="solar:check-circle-bold" class="align-middle me-0.5"></iconify-icon>Payment UTR: {{ $req->utr_number }}
                                                                </div>
                                                            @endif
                                                        </div>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($req->payment_receipt)
                                                        <a href="{{ \Illuminate\Support\Str::startsWith($req->payment_receipt, ['http://', 'https://']) ? $req->payment_receipt : asset('storage/' . $req->payment_receipt) }}" target="_blank" class="btn btn-xs btn-outline-primary py-0.5 px-2 fs-11">
                                                            <iconify-icon icon="solar:file-check-bold" class="align-middle me-1"></iconify-icon> View Receipt
                                                        </a>
                                                    @else
                                                        <span class="text-muted fs-11">N/A</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($req->status === 'APPROVED')
                                                        <span class="badge bg-success-subtle text-success px-2 py-1 fs-11 fw-bold">
                                                            <iconify-icon icon="solar:check-circle-bold" class="align-middle me-0.5"></iconify-icon> APPROVED & PAID
                                                        </span>
                                                    @elseif($req->status === 'TRANSFERRED')
                                                        <span class="badge bg-info-subtle text-info px-2 py-1 fs-11 fw-bold">
                                                            <iconify-icon icon="solar:card-transfer-bold" class="align-middle me-0.5"></iconify-icon> TRANSFERRED - VERIFY RECEIPT
                                                        </span>
                                                    @elseif($req->status === 'REJECTED')
                                                        <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-11 fw-bold">
                                                            <iconify-icon icon="solar:close-circle-bold" class="align-middle me-0.5"></iconify-icon> REJECTED
                                                        </span>
                                                    @else
                                                        <span class="badge bg-warning-subtle text-warning px-2 py-1 fs-11 fw-bold">
                                                            <iconify-icon icon="solar:clock-circle-bold" class="align-middle me-0.5"></iconify-icon> PENDING REVIEW
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-muted small">
                                                    {{ Str::limit($req->remarks ?: '—', 25) }}
                                                </td>
                                                <td class="text-end pe-3">
                                                    @if($req->status === 'TRANSFERRED')
                                                        <button type="button" class="btn btn-xs btn-success fw-bold py-1 px-2 me-1 shadow-xs" onclick="openVerifyModal({{ json_encode([
                                                            'id' => $req->id,
                                                            'amount' => number_format($req->amount, 2),
                                                            'utr_number' => $req->utr_number,
                                                            'payment_receipt' => $req->payment_receipt ? (\Illuminate\Support\Str::startsWith($req->payment_receipt, ['http://', 'https://']) ? $req->payment_receipt : asset('storage/' . $req->payment_receipt)) : null,
                                                            'account_holder_name' => $req->account_holder_name ?: ($user->name ?? ''),
                                                            'bank_name' => $req->bank_name,
                                                            'bank_account_number' => $req->bank_account_number,
                                                            'ifsc_code' => $req->ifsc_code,
                                                            'remarks' => $req->remarks,
                                                            'processed_at' => $req->processed_at ? \Carbon\Carbon::parse($req->processed_at)->format('d M Y, h:i A') : null
                                                        ]) }})">
                                                            <iconify-icon icon="solar:shield-check-bold" class="align-middle me-0.5"></iconify-icon> Verify Payment
                                                        </button>
                                                    @endif
                                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0.5 px-2 fs-11" onclick="openSellerReqModal({{ json_encode([
                                                        'id' => $req->id,
                                                        'type' => $req->type,
                                                        'amount' => number_format($req->amount, 2),
                                                        'status' => $req->status,
                                                        'utr_number' => $req->utr_number,
                                                        'account_holder_name' => $req->account_holder_name ?: ($user->name ?? ''),
                                                        'bank_name' => $req->bank_name,
                                                        'bank_account_number' => $req->bank_account_number,
                                                        'ifsc_code' => $req->ifsc_code,
                                                        'branch_name' => $req->branch_name,
                                                        'payment_receipt' => $req->payment_receipt ? (\Illuminate\Support\Str::startsWith($req->payment_receipt, ['http://', 'https://']) ? $req->payment_receipt : asset('storage/' . $req->payment_receipt)) : null,
                                                        'remarks' => $req->remarks,
                                                        'submitted_at' => $req->created_at->format('d M Y, h:i A'),
                                                        'processed_at' => $req->processed_at ? \Carbon\Carbon::parse($req->processed_at)->format('d M Y, h:i A') : null
                                                    ]) }})">
                                                        <iconify-icon icon="solar:eye-bold" class="align-middle me-0.5"></iconify-icon> Summary
                                                    </button>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="8" class="text-center py-4 text-muted small">No requests submitted yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center p-3 border-top">
                                <span class="text-muted small">Showing {{ $requests->firstItem() ?? 0 }} to {{ $requests->lastItem() ?? 0 }} of {{ $requests->total() }} entries</span>
                                @if($requests->hasPages())
                                    {{ $requests->links() }}
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Request Top-Up or Withdrawal Form -->
        <div class="col-xl-4 col-lg-5" id="request-topup-section">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-2.5 px-3 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <iconify-icon icon="solar:card-send-bold-duotone" class="text-primary fs-20"></iconify-icon>
                        <h6 class="fw-bold text-slate-800 mb-0">Submit Wallet Request</h6>
                    </div>
                    <ul class="nav nav-pills nav-pills-sm card-header-pills" id="requestTypePills" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active py-1 px-2.5 fs-11 fw-semibold" id="pills-topup-tab" data-bs-toggle="pill" data-bs-target="#pills-topup" type="button" role="tab">
                                Top-up
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link py-1 px-2.5 fs-11 fw-semibold text-dark" id="pills-withdrawal-tab" data-bs-toggle="pill" data-bs-target="#pills-withdrawal" type="button" role="tab">
                                Withdrawal
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="card-body p-3">
                    <div class="tab-content" id="requestTypeContent">

                        <!-- FORM 1: Top-Up Request Form -->
                        <div class="tab-pane fade show active" id="pills-topup" role="tabpanel">
                            <p class="text-muted small mb-3 fs-12">
                                Transfer funds to the company bank account on the left via UPI/NetBanking, then upload the receipt and enter the UTR/Txn ID below.
                            </p>

                            <form action="{{ route('vendor.wallet.request.topup') }}" method="POST" enctype="multipart/form-data">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-slate-700 fs-12">Amount Sent (₹) <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light fw-bold">₹</span>
                                        <input type="number" name="amount" class="form-control" placeholder="0.00" min="1" step="any" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-slate-700 fs-12">UTR / Reference Number <span class="text-danger">*</span></label>
                                    <input type="text" name="utr_number" class="form-control form-control-sm font-monospace" placeholder="12-digit UTR or Transaction ID" required>
                                    <small class="text-muted fs-11">Ensure this matches the UTR on your payment receipt.</small>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-slate-700 fs-12">Payment Receipt File <span class="text-danger">*</span></label>
                                    <input type="file" id="payment_receipt_file" class="form-control form-control-sm" accept="image/*,application/pdf" required>
                                    <input type="hidden" name="payment_receipt" id="payment_receipt_url" required>
                                    <small class="text-muted fs-11">Image or PDF (Max 5MB).</small>

                                    <!-- Visual progress/feedback element -->
                                    <div id="receiptUploadStatus" class="mt-2 d-none">
                                        <div class="p-2 border rounded d-flex align-items-center justify-content-between bg-light">
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="spinner-border spinner-border-sm text-primary" role="status" id="receiptSpinner"></div>
                                                <iconify-icon icon="solar:document-text-bold-duotone" class="text-success fs-18 d-none" id="receiptFileIcon"></iconify-icon>
                                                <span id="receiptStatusText" class="small fw-semibold text-slate-700 fs-11">Uploading receipt...</span>
                                            </div>
                                            <div id="receiptPreviewLinkContainer" class="d-none">
                                                <a href="#" id="receiptPreviewLink" target="_blank" class="btn btn-xs btn-outline-primary py-0.5 px-2 fs-10">
                                                    View
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-primary w-100 fw-semibold py-2 fs-13" id="submitTopupBtn" disabled data-originally-disabled="true">
                                    <iconify-icon icon="solar:upload-bold-duotone" class="me-1 align-middle fs-16"></iconify-icon>
                                    Submit Top-up Request
                                </button>
                            </form>
                        </div>

                        <!-- FORM 2: Withdrawal Request Form -->
                        <div class="tab-pane fade" id="pills-withdrawal" role="tabpanel">
                            <div class="p-2 bg-light rounded mb-3 border">
                                <span class="text-muted fs-11 d-block">Available Wallet Balance for Withdrawal:</span>
                                <strong class="fs-16 text-success font-monospace">₹{{ number_format($walletBalance, 2) }}</strong>
                            </div>

                            <form action="{{ route('vendor.wallet.request.withdrawal') }}" method="POST">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-slate-700 fs-12">Withdrawal Amount (₹) <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-light fw-bold">₹</span>
                                        <input type="number" name="amount" class="form-control" placeholder="0.00" min="1" max="{{ $walletBalance }}" step="any" required>
                                    </div>
                                </div>

                                <h6 class="fw-bold text-slate-800 fs-12 border-bottom pb-1 mb-2">Destination Bank Account Details</h6>

                                <div class="mb-2">
                                    <label class="form-label fw-semibold fs-11 mb-1">Account Holder Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control form-control-sm" name="account_holder_name" value="{{ old('account_holder_name', $profile->account_holder_name ?? $user->name) }}" required>
                                </div>

                                <div class="row g-2 mb-2">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold fs-11 mb-1">Bank Name <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" name="bank_name" value="{{ old('bank_name', $profile->bank_name) }}" placeholder="e.g. ICICI Bank" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold fs-11 mb-1">Branch Name</label>
                                        <input type="text" class="form-control form-control-sm" name="branch_name" value="{{ old('branch_name', $profile->branch_name) }}" placeholder="e.g. Connaught Place">
                                    </div>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold fs-11 mb-1">Account Number <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm font-monospace" name="bank_account_number" value="{{ old('bank_account_number', $profile->bank_account_number) }}" placeholder="Account Number" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold fs-11 mb-1">IFSC Code <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm font-monospace text-uppercase" name="ifsc_code" value="{{ old('ifsc_code', $profile->ifsc_code) }}" placeholder="e.g. ICIC0001234" required>
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-dark w-100 fw-semibold py-2 fs-13" {{ $walletBalance < 1 ? 'disabled' : '' }}>
                                    <iconify-icon icon="solar:card-receive-bold-duotone" class="me-1 align-middle fs-16"></iconify-icon>
                                    Submit Withdrawal Request
                                </button>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Seller Request Details Summary Modal -->
    <div class="modal fade" id="sellerReqModal" tabindex="-1" aria-labelledby="sellerReqModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow">
                <div class="modal-header py-3 text-white" id="sellerReqModalHeader">
                    <h5 class="modal-title fw-bold text-white fs-15 mb-0" id="sellerReqModalLabel">
                        <iconify-icon icon="solar:notes-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                        Wallet Request Summary
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4 fs-13">
                    <div class="row g-3 align-items-center mb-3 p-3 bg-light rounded border">
                        <div class="col-md-7">
                            <span class="text-muted fs-11 d-block text-uppercase fw-semibold">Request Type</span>
                            <h5 class="fw-bold text-slate-800 mb-0" id="sReqType">Top-Up</h5>
                            <span class="text-muted fs-11">Submitted: <span id="sReqSubmittedAt">--</span></span>
                        </div>
                        <div class="col-md-5 text-md-end border-start border-md-start-0 pt-2 pt-md-0">
                            <span class="text-muted fs-11 d-block text-uppercase fw-semibold">Requested Amount</span>
                            <h3 class="fw-extrabold text-primary mb-0" id="sReqAmount">₹0.00</h3>
                            <span class="badge px-2 py-0.5 mt-1" id="sReqStatusBadge">PENDING</span>
                        </div>
                    </div>

                    <!-- Details Card -->
                    <div class="card border mb-3">
                        <div class="card-header bg-white py-2 fw-bold text-slate-800 fs-13 border-bottom d-flex align-items-center justify-content-between">
                            <span><iconify-icon icon="solar:bank-bold-duotone" class="align-middle me-1 text-primary"></iconify-icon> Bank & Transfer Details</span>
                            <span class="text-muted fs-11" id="sReqProcessedAt">--</span>
                        </div>
                        <div class="card-body p-3">
                            <div class="row g-3">
                                <div class="col-md-6" id="sReqHolderRow">
                                    <span class="text-muted d-block fs-11">Account Holder Name:</span>
                                    <strong class="text-slate-800" id="sReqHolder">--</strong>
                                </div>
                                <div class="col-md-6" id="sReqBankRow">
                                    <span class="text-muted d-block fs-11">Bank Name:</span>
                                    <strong class="text-slate-800" id="sReqBank">--</strong>
                                </div>
                                <div class="col-md-6" id="sReqAccountRow">
                                    <span class="text-muted d-block fs-11">Account Number:</span>
                                    <strong class="font-monospace text-slate-800" id="sReqAccount">--</strong>
                                </div>
                                <div class="col-md-6" id="sReqIfscRow">
                                    <span class="text-muted d-block fs-11">IFSC Code:</span>
                                    <strong class="font-monospace text-slate-800" id="sReqIfsc">--</strong>
                                </div>
                                <div class="col-12" id="sReqUtrRow">
                                    <span class="text-muted d-block fs-11">Payment UTR / Transaction Reference No:</span>
                                    <span class="badge bg-light text-dark font-monospace fs-13 border border-secondary border-opacity-25" id="sReqUtr">--</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Receipt / Proof -->
                    <div class="mb-3" id="sReqReceiptRow">
                        <label class="form-label fw-semibold text-slate-800 mb-1">Transfer Receipt / Screenshot Proof:</label>
                        <div>
                            <a href="#" id="sReqReceiptLink" target="_blank" class="btn btn-sm btn-outline-primary">
                                <iconify-icon icon="solar:eye-bold" class="align-middle me-1"></iconify-icon>
                                Open Receipt / Confirmation Document
                            </a>
                        </div>
                    </div>

                    <!-- Admin Remarks -->
                    <div class="mb-0" id="sReqRemarksRow">
                        <label class="form-label fw-semibold text-slate-800 mb-1">Admin Remarks / Rejection Notes:</label>
                        <div class="p-2.5 bg-light rounded border text-slate-800" id="sReqRemarks">
                            --
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-3">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Close</button>
                </div>
            </div>
        </div>
    </div>

    <!-- Seller Payout Verification Modal -->
    <div class="modal fade" id="verifyPaymentModal" tabindex="-1" aria-labelledby="verifyPaymentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title fw-bold text-white fs-15 mb-0" id="verifyPaymentModalLabel">
                        <iconify-icon icon="solar:shield-check-bold" class="align-middle me-1 fs-18"></iconify-icon>
                        Verify & Confirm Payment Received
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="verifyPaymentForm" method="POST">
                    @csrf
                    <div class="modal-body p-4 fs-13">
                        <div class="alert alert-success bg-success bg-opacity-10 border-success border-opacity-25 text-dark mb-3">
                            <div class="d-flex align-items-center">
                                <iconify-icon icon="solar:check-read-bold" class="text-success fs-24 me-2.5 flex-shrink-0"></iconify-icon>
                                <div>
                                    Admin has processed and transferred <strong>₹<span id="vAmount">0.00</span></strong> to your bank account. Please review the UTR reference & receipt below and confirm receipt of payment.
                                </div>
                            </div>
                        </div>

                        <!-- Bank & Transfer Info Card -->
                        <div class="card border mb-3">
                            <div class="card-header bg-light py-2 fw-bold text-slate-800 fs-13 border-bottom">
                                <iconify-icon icon="solar:card-bold-duotone" class="align-middle me-1 text-primary"></iconify-icon>
                                Payment Transfer Details
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <span class="text-muted d-block fs-11">Payment UTR / Reference Number:</span>
                                        <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded border font-monospace">
                                            <strong class="text-primary fs-14" id="vUtr">--</strong>
                                            <button type="button" class="btn btn-xs btn-link p-0 text-decoration-none ms-1" onclick="copyText('vUtr')" title="Copy UTR">
                                                <iconify-icon icon="solar:copy-bold" class="fs-14 text-muted"></iconify-icon>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="text-muted d-block fs-11">Admin Transfer Date:</span>
                                        <div class="bg-light p-2 rounded border">
                                            <strong class="text-dark" id="vProcessedAt">--</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="text-muted d-block fs-11">Destination Bank:</span>
                                        <div class="bg-light p-2 rounded border">
                                            <span class="fw-semibold text-dark" id="vBankName">--</span>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="text-muted d-block fs-11">Destination Account Number:</span>
                                        <div class="bg-light p-2 rounded border font-monospace">
                                            <span class="fw-semibold text-dark" id="vAccountNo">--</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Transfer Receipt Preview -->
                        <div class="mb-3" id="vReceiptContainer">
                            <label class="form-label fw-semibold text-slate-800 mb-1">Admin Payment Transfer Receipt:</label>
                            <div>
                                <a href="#" id="vReceiptLink" target="_blank" class="btn btn-sm btn-outline-primary fw-semibold">
                                    <iconify-icon icon="solar:eye-bold" class="align-middle me-1"></iconify-icon> View / Download Payment Receipt Document
                                </a>
                            </div>
                        </div>

                        <!-- Admin Remarks -->
                        <div class="mb-0" id="vRemarksContainer">
                            <label class="form-label fw-semibold text-slate-800 mb-1">Admin Remarks / Note:</label>
                            <div class="p-2.5 bg-light rounded border text-slate-800" id="vRemarks">
                                --
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2 px-3">
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success btn-sm px-4 fw-bold shadow-xs" onclick="return confirm('Are you sure you have received this payment in your bank account? Clicking confirm will debit your wallet balance and mark this payout as completed.');">
                            <iconify-icon icon="solar:check-circle-bold" class="align-middle me-1 fs-16"></iconify-icon>
                            Confirm Payment Received & Complete Withdrawal
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function selectRequestType(type) {
            const section = document.getElementById('request-topup-section');
            if (section) {
                section.scrollIntoView({ behavior: 'smooth' });
            }
            if (type === 'withdrawal') {
                const tabBtn = document.getElementById('pills-withdrawal-tab');
                if (tabBtn) (new bootstrap.Tab(tabBtn)).show();
            } else {
                const tabBtn = document.getElementById('pills-topup-tab');
                if (tabBtn) (new bootstrap.Tab(tabBtn)).show();
            }
        }

        function openVerifyModal(data) {
            const form = document.getElementById('verifyPaymentForm');
            form.action = "{{ route('vendor.wallet.verify-payout', ':id') }}".replace(':id', data.id);

            document.getElementById('vAmount').textContent = data.amount;
            document.getElementById('vUtr').textContent = data.utr_number || 'N/A';
            document.getElementById('vProcessedAt').textContent = data.processed_at || 'Recently';
            document.getElementById('vBankName').textContent = data.bank_name || '—';
            document.getElementById('vAccountNo').textContent = data.bank_account_number || '—';

            if (data.payment_receipt) {
                document.getElementById('vReceiptContainer').style.display = 'block';
                document.getElementById('vReceiptLink').href = data.payment_receipt;
            } else {
                document.getElementById('vReceiptContainer').style.display = 'none';
            }

            if (data.remarks) {
                document.getElementById('vRemarksContainer').style.display = 'block';
                document.getElementById('vRemarks').textContent = data.remarks;
            } else {
                document.getElementById('vRemarksContainer').style.display = 'none';
            }

            const modal = new bootstrap.Modal(document.getElementById('verifyPaymentModal'));
            modal.show();
        }

        function copyText(elementId) {
            const text = document.getElementById(elementId).textContent.trim();
            if (navigator.clipboard) {
                navigator.clipboard.writeText(text).then(() => {
                    alert('Copied to clipboard: ' + text);
                });
            } else {
                const input = document.createElement('input');
                input.value = text;
                document.body.appendChild(input);
                input.select();
                document.execCommand('copy');
                document.body.removeChild(input);
                alert('Copied to clipboard: ' + text);
            }
        }

        function openSellerReqModal(data) {
            const header = document.getElementById('sellerReqModalHeader');
            const typeElem = document.getElementById('sReqType');
            const badge = document.getElementById('sReqStatusBadge');

            if (data.type === 'topup') {
                header.className = 'modal-header py-3 text-white bg-primary';
                typeElem.textContent = 'Wallet Top-Up Request';
            } else {
                header.className = 'modal-header py-3 text-white bg-purple';
                header.style.backgroundColor = '#6b21a8';
                typeElem.textContent = 'Payout / Withdrawal Request';
            }

            document.getElementById('sReqSubmittedAt').textContent = data.submitted_at;
            document.getElementById('sReqAmount').textContent = '₹' + data.amount;

            if (data.status === 'APPROVED') {
                badge.className = 'badge bg-success px-2 py-1 text-white fs-11';
                badge.textContent = 'APPROVED & PAID';
            } else if (data.status === 'TRANSFERRED') {
                badge.className = 'badge bg-info px-2 py-1 text-white fs-11';
                badge.textContent = 'TRANSFERRED (Verification Pending)';
            } else if (data.status === 'REJECTED') {
                badge.className = 'badge bg-danger px-2 py-1 text-white fs-11';
                badge.textContent = 'REJECTED';
            } else {
                badge.className = 'badge bg-warning px-2 py-1 text-dark fs-11';
                badge.textContent = 'PENDING REVIEW';
            }

            document.getElementById('sReqHolder').textContent = data.account_holder_name || '—';
            document.getElementById('sReqBank').textContent = data.bank_name || '—';
            document.getElementById('sReqAccount').textContent = data.bank_account_number || '—';
            document.getElementById('sReqIfsc').textContent = data.ifsc_code || '—';
            document.getElementById('sReqUtr').textContent = data.utr_number || 'N/A';

            if (data.processed_at) {
                document.getElementById('sReqProcessedAt').textContent = 'Processed on: ' + data.processed_at;
            } else {
                document.getElementById('sReqProcessedAt').textContent = 'Pending Admin Action';
            }

            if (data.payment_receipt) {
                document.getElementById('sReqReceiptRow').style.display = 'block';
                document.getElementById('sReqReceiptLink').href = data.payment_receipt;
            } else {
                document.getElementById('sReqReceiptRow').style.display = 'none';
            }

            if (data.remarks) {
                document.getElementById('sReqRemarksRow').style.display = 'block';
                document.getElementById('sReqRemarks').textContent = data.remarks;
            } else {
                document.getElementById('sReqRemarksRow').style.display = 'none';
            }

            const modal = new bootstrap.Modal(document.getElementById('sellerReqModal'));
            modal.show();
        }

        document.addEventListener('DOMContentLoaded', function () {
            // Preserve tab selection on page reload using URL params or localStorage
            const urlParams = new URLSearchParams(window.location.search);
            let activeTabId = 'tx-tab'; // default

            if (urlParams.has('req_page') || localStorage.getItem('vendor_wallet_active_tab') === 'req-tab') {
                activeTabId = 'req-tab';
            } else if (urlParams.has('tx_page') || localStorage.getItem('vendor_wallet_active_tab') === 'tx-tab') {
                activeTabId = 'tx-tab';
            }

            const activeTabButton = document.getElementById(activeTabId);
            if (activeTabButton) {
                const tab = new bootstrap.Tab(activeTabButton);
                tab.show();
            }

            // Save tab selection to localStorage
            const tabButtons = document.querySelectorAll('#walletTabs button');
            tabButtons.forEach(button => {
                button.addEventListener('shown.bs.tab', function (e) {
                    localStorage.setItem('vendor_wallet_active_tab', e.target.id);
                });
            });

            // Handle AJAX file upload for payment receipt
            const fileInput = document.getElementById('payment_receipt_file');
            const urlInput = document.getElementById('payment_receipt_url');
            const statusContainer = document.getElementById('receiptUploadStatus');
            const spinner = document.getElementById('receiptSpinner');
            const fileIcon = document.getElementById('receiptFileIcon');
            const statusText = document.getElementById('receiptStatusText');
            const previewContainer = document.getElementById('receiptPreviewLinkContainer');
            const previewLink = document.getElementById('receiptPreviewLink');
            const submitBtn = document.getElementById('submitTopupBtn');

            if (fileInput) {
                fileInput.addEventListener('change', function () {
                    const file = fileInput.files[0];
                    if (!file) {
                        urlInput.value = '';
                        submitBtn.disabled = true;
                        return;
                    }

                    const isImg = file.type.startsWith('image/');
                    if (isImg && fileInput.dataset.isCompressing !== 'done') {
                        return;
                    }

                    urlInput.value = '';
                    statusContainer.classList.remove('d-none');
                    spinner.classList.remove('d-none');
                    fileIcon.classList.add('d-none');
                    statusText.className = 'small fw-semibold text-slate-700 fs-11';
                    statusText.textContent = 'Uploading file to service...';
                    previewContainer.classList.add('d-none');
                    submitBtn.disabled = true;

                    const formData = new FormData();
                    formData.append('file', file);
                    formData.append('_token', '{{ csrf_token() }}');

                    fetch('{{ route("vendor.wallet.upload-receipt") }}', {
                        method: 'POST',
                        body: formData,
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    })
                    .then(res => {
                        if (!res.ok) {
                            return res.json().then(err => { throw new Error(err.message || 'Upload failed.'); });
                        }
                        return res.json();
                    })
                    .then(data => {
                        if (data.status) {
                            urlInput.value = data.file_url;
                            spinner.classList.add('d-none');
                            fileIcon.classList.remove('d-none');
                            statusText.className = 'small fw-semibold text-success fs-11';
                            statusText.textContent = 'Uploaded successfully!';

                            previewLink.href = data.file_url;
                            previewContainer.classList.remove('d-none');
                            submitBtn.disabled = false;
                        } else {
                            throw new Error(data.message || 'Upload failed.');
                        }
                    })
                    .catch(err => {
                        console.error('File service upload error:', err);
                        spinner.classList.add('d-none');
                        statusText.className = 'small fw-semibold text-danger fs-11';
                        statusText.textContent = err.message || 'Failed to upload receipt. Please retry.';
                        submitBtn.disabled = true;
                    });
                });
            }
        });
    </script>
@endpush
