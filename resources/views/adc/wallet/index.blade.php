@extends('layouts.adc')

@section('title', 'My Wallet')
@section('page-title', 'Wallet & Transactions')

@section('content')
    <!-- Action Header -->
    <div class="row mb-3">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body py-3 px-4">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                Wallet & Transactions
                            </h4>
                            <p class="text-muted small mb-0">Track your wallet balance, verify credit top-ups, and review transaction history.</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <a href="#request-topup-section" class="btn btn-primary btn-sm fw-semibold d-lg-none">
                                <iconify-icon icon="solar:card-send-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                                Request Wallet Top-up
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
            <iconify-icon icon="solar:check-circle-bold-duotone" class="fs-20 align-middle me-2"></iconify-icon>
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 shadow-sm alert-dismissible fade show mb-4" role="alert">
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
            <div class="row g-3">
                <!-- Balance Card -->
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100 text-white overflow-hidden position-relative" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); border-radius: 10px;">
                        <div class="card-body p-4 d-flex flex-column justify-content-between text-white">
                            <div>
                                <div class="d-flex justify-content-between align-items-start">
                                    <p class="mb-1 text-white-50 fw-semibold text-uppercase tracking-wider fs-12">Wallet
                                        Balance</p>
                                    <iconify-icon icon="solar:wallet-money-bold-duotone"
                                        class="fs-28 text-white-50"></iconify-icon>
                                </div>
                                <h2 class="mb-0 text-white fw-bold fs-32">₹{{ number_format($user->wallet_balance, 2) }}
                                </h2>
                            </div>
                            <div class="mt-4 pt-2">
                                <span class="badge bg-white-10 text-white px-3 py-1 fs-11 rounded-pill">ADC Wallet</span>
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
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 py-3 d-flex align-items-center gap-2 border-bottom">
                            <iconify-icon icon="solar:card-bold-duotone" class="text-primary fs-20"></iconify-icon>
                            <h6 class="fw-bold text-slate-800 mb-0">
                                @if($assignedDdc)
                                    DDC Bank Details
                                @else
                                    ADPL Bank Details & QR Code
                                @endif
                            </h6>
                        </div>
                        <div class="card-body py-2 fs-13">
                            @if($assignedDdc)
                                @php
                                    $ddcProf = $assignedDdc->ddcProfile;
                                    $bankName = $ddcProf?->bank_name ?? $assignedDdc->bank_name;
                                    $accHolder = $ddcProf?->account_holder_name ?? $assignedDdc->account_holder_name ?? $assignedDdc->name;
                                    $accNo = $ddcProf?->bank_account_number ?? $assignedDdc->bank_account_number;
                                    $ifsc = $ddcProf?->ifsc_code ?? $assignedDdc->ifsc_code;
                                    $branch = $ddcProf?->branch_name ?? $assignedDdc->branch_name;
                                    $hasBankDetails = !empty($bankName) || !empty($accNo);
                                    $rawDdcQr = $ddcProf?->qr_code;
                                    $ddcQr = $adminQrCode;
                                    if (!empty($rawDdcQr)) {
                                        $trimmedDdcQr = trim($rawDdcQr);
                                        if (filter_var($trimmedDdcQr, FILTER_VALIDATE_URL) || \Illuminate\Support\Str::startsWith($trimmedDdcQr, ['http://', 'https://', '//'])) {
                                            $ddcQr = $trimmedDdcQr;
                                        } elseif (\Illuminate\Support\Str::startsWith($trimmedDdcQr, 'storage/')) {
                                            $ddcQr = asset($trimmedDdcQr);
                                        } elseif (\Illuminate\Support\Str::startsWith($trimmedDdcQr, '/storage/')) {
                                            $ddcQr = asset(ltrim($trimmedDdcQr, '/'));
                                        } else {
                                            $ddcQr = asset('storage/' . $trimmedDdcQr);
                                        }
                                    }
                                @endphp

                                @if($hasBankDetails)
                                    <div class="row g-2 align-items-center">
                                        <div class="col-sm-8">
                                            <table class="table table-sm table-borderless mb-0">
                                                <tr>
                                                    <td class="text-muted py-1" style="width: 90px;">DDC Name</td>
                                                    <td class="fw-semibold text-slate-800 py-1">: {{ $assignedDdc->name }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted py-1">Bank Name</td>
                                                    <td class="fw-semibold text-slate-800 py-1">: {{ $bankName ?? '—' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted py-1">Account Name</td>
                                                    <td class="fw-semibold text-slate-800 py-1">: {{ $accHolder ?? '—' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted py-1">Account No.</td>
                                                    <td class="fw-semibold text-slate-800 py-1">: {{ $accNo ?? '—' }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted py-1">IFSC Code</td>
                                                    <td class="fw-semibold text-slate-800 py-1">: {{ $ifsc ?? '—' }}</td>
                                                </tr>
                                                @if($branch)
                                                    <tr>
                                                        <td class="text-muted py-1">Branch</td>
                                                        <td class="text-slate-800 py-1">: {{ $branch }}</td>
                                                    </tr>
                                                @endif
                                            </table>
                                        </div>
                                        <div class="col-sm-4 text-center border-start">
                                            <a href="{{ $ddcQr }}" target="_blank" title="Click to view full QR code">
                                                <img src="{{ $ddcQr }}" alt="UPI QR Code" class="img-fluid rounded border"
                                                    style="max-height: 110px; object-fit: contain;">
                                            </a>
                                            <div class="text-muted small mt-1" style="font-size: 10px;">Scan to Pay</div>
                                        </div>
                                    </div>
                                @else
                                    <!-- Fallback to ADPL Company Details if DDC bank details not set -->
                                    <div class="row g-2 align-items-center">
                                        <div class="col-sm-8">
                                            <table class="table table-sm table-borderless mb-0">
                                                <tr>
                                                    <td class="text-muted py-1" style="width: 90px;">Bank Name</td>
                                                    <td class="fw-semibold text-slate-800 py-1">: {{ $adminBankName }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted py-1">Account Name</td>
                                                    <td class="fw-semibold text-slate-800 py-1">: {{ $adminAccountName }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted py-1">Account No.</td>
                                                    <td class="fw-semibold text-slate-800 py-1">: {{ $adminAccountNumber }}</td>
                                                </tr>
                                                <tr>
                                                    <td class="text-muted py-1">IFSC Code</td>
                                                    <td class="fw-semibold text-slate-800 py-1">: {{ $adminIfscCode }}</td>
                                                </tr>
                                                @if(!empty($adminBankBranch))
                                                    <tr>
                                                        <td class="text-muted py-1">Branch</td>
                                                        <td class="text-slate-800 py-1">: {{ $adminBankBranch }}</td>
                                                    </tr>
                                                @endif
                                                @if(!empty($adminUpiId))
                                                    <tr>
                                                        <td class="text-muted py-1">UPI ID</td>
                                                        <td class="fw-semibold text-primary py-1">: {{ $adminUpiId }}</td>
                                                    </tr>
                                                @endif
                                            </table>
                                        </div>
                                        <div class="col-sm-4 text-center border-start">
                                            <a href="{{ $adminQrCode }}" target="_blank" title="Click to view full QR code">
                                                <img src="{{ $adminQrCode }}" alt="UPI QR Code" class="img-fluid rounded border"
                                                    style="max-height: 110px; object-fit: contain;">
                                            </a>
                                            <div class="text-muted small mt-1" style="font-size: 10px;">Scan to Pay</div>
                                        </div>
                                    </div>
                                @endif
                            @else
                                <!-- ADPL Company Bank Details (Virtual DDC route) -->
                                <div class="row g-2 align-items-center">
                                    <div class="col-sm-8">
                                        <table class="table table-sm table-borderless mb-0">
                                            <tr>
                                                <td class="text-muted py-1" style="width: 90px;">Bank Name</td>
                                                <td class="fw-semibold text-slate-800 py-1">: {{ $adminBankName }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted py-1">Account Name</td>
                                                <td class="fw-semibold text-slate-800 py-1">: {{ $adminAccountName }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted py-1">Account No.</td>
                                                <td class="fw-semibold text-slate-800 py-1">: {{ $adminAccountNumber }}</td>
                                            </tr>
                                            <tr>
                                                <td class="text-muted py-1">IFSC Code</td>
                                                <td class="fw-semibold text-slate-800 py-1">: {{ $adminIfscCode }}</td>
                                            </tr>
                                            @if(!empty($adminBankBranch))
                                                <tr>
                                                    <td class="text-muted py-1">Branch</td>
                                                    <td class="text-slate-800 py-1">: {{ $adminBankBranch }}</td>
                                                </tr>
                                            @endif
                                            @if(!empty($adminUpiId))
                                                <tr>
                                                    <td class="text-muted py-1">UPI ID</td>
                                                    <td class="fw-semibold text-primary py-1">: {{ $adminUpiId }}</td>
                                                </tr>
                                            @endif
                                        </table>
                                    </div>
                                    <div class="col-sm-4 text-center border-start">
                                        <a href="{{ $adminQrCode }}" target="_blank" title="Click to view full QR code">
                                            <img src="{{ $adminQrCode }}" alt="UPI QR Code" class="img-fluid rounded border"
                                                style="max-height: 110px; object-fit: contain;">
                                        </a>
                                        <div class="text-muted small mt-1" style="font-size: 10px;">Scan to Pay</div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabs for History/Requests -->
            <div class="card border-0 shadow-sm mt-3">
                <div
                    class="card-header bg-white border-0 py-3 border-bottom d-flex flex-column flex-md-row align-items-md-center justify-content-md-between gap-3">
                    <ul class="nav nav-tabs card-header-tabs gap-1" id="walletTabs" role="tablist">
                        <li class="nav-item">
                            <button class="nav-link active fw-semibold" id="tx-tab" data-bs-toggle="tab"
                                data-bs-target="#tx-pane" type="button" role="tab">
                                Transaction History
                            </button>
                        </li>
                        <li class="nav-item">
                            <button class="nav-link fw-semibold" id="req-tab" data-bs-toggle="tab"
                                data-bs-target="#req-pane" type="button" role="tab">
                                Top-up Requests
                            </button>
                        </li>
                    </ul>

                    <form id="filterForm" action="{{ route('adc.wallet.index') }}" method="GET"
                        class="d-flex align-items-center gap-2 mb-0">
                        <input type="hidden" name="sort" id="formSort" value="{{ $sort }}">
                        <input type="hidden" name="order" id="formOrder" value="{{ $order }}">
                        <div class="input-group input-group-sm" style="max-width: 200px;">
                            <span class="input-group-text bg-white border-end-0 py-1">
                                <iconify-icon icon="solar:magnifer-bold-duotone" class="text-muted"></iconify-icon>
                            </span>
                            <input type="text" name="search" class="form-control form-control-sm border-start-0 py-1"
                                placeholder="Search..." value="{{ $search }}">
                        </div>
                        <select name="limit" class="form-select form-select-sm w-auto py-1" onchange="this.form.submit()">
                            <option value="10" {{ $limit == 10 ? 'selected' : '' }}>10 / page</option>
                            <option value="15" {{ $limit == 15 ? 'selected' : '' }}>15 / page</option>
                            <option value="25" {{ $limit == 25 ? 'selected' : '' }}>25 / page</option>
                            <option value="50" {{ $limit == 50 ? 'selected' : '' }}>50 / page</option>
                        </select>
                        @if($search)
                            <a href="{{ route('adc.wallet.index') }}" class="btn btn-sm btn-outline-danger py-1 px-2">Clear</a>
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
                                            <th class="ps-3 text-muted">Date</th>
                                            <th class="text-muted">Description</th>
                                            <th class="text-muted">Type</th>
                                            <th class="text-end pe-3 text-muted">Amount</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($transactions as $tx)
                                            <tr>
                                                <td class="ps-3 text-muted small">{{ $tx->created_at->format('d M Y, h:i A') }}
                                                </td>
                                                <td>{{ $tx->description }}</td>
                                                <td>
                                                    <span
                                                        class="badge bg-{{ $tx->type === 'credit' ? 'success' : 'danger' }}-subtle text-{{ $tx->type === 'credit' ? 'success' : 'danger' }} px-2 py-1 fs-11">
                                                        {{ strtoupper($tx->type) }}
                                                    </span>
                                                </td>
                                                <td
                                                    class="text-end pe-3 fw-bold {{ $tx->type === 'credit' ? 'text-success' : 'text-danger' }}">
                                                    {{ $tx->type === 'credit' ? '+' : '-' }}
                                                    ₹{{ number_format($tx->amount, 2) }}
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="4" class="text-center py-4 text-muted small">No transactions yet.
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center p-3 border-top">
                                <span class="text-muted small">Showing {{ $transactions->firstItem() ?? 0 }} to
                                    {{ $transactions->lastItem() ?? 0 }} of {{ $transactions->total() }} entries</span>
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
                                            <th class="ps-3 text-muted">Submitted</th>
                                            <th class="text-muted">Amount</th>
                                            <th class="text-muted">UTR Number</th>
                                            <th class="text-muted">Receipt</th>
                                            <th class="text-muted">Status</th>
                                            <th class="pe-3 text-muted">Remarks</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($requests as $req)
                                            <tr>
                                                <td class="ps-3 text-muted small">{{ $req->created_at->format('d M Y, h:i A') }}
                                                </td>
                                                <td class="fw-semibold text-slate-800">₹{{ number_format($req->amount, 2) }}
                                                </td>
                                                <td><code class="small fw-semibold">{{ $req->utr_number }}</code></td>
                                                <td>
                                                    @if($req->payment_receipt)
                                                        <a href="{{ Str::startsWith($req->payment_receipt, ['http://', 'https://']) ? $req->payment_receipt : asset('storage/' . $req->payment_receipt) }}"
                                                            target="_blank" class="btn btn-xs btn-outline-primary py-0 px-2 fs-11">
                                                            View Receipt
                                                        </a>
                                                    @else
                                                        <span class="text-muted small">—</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($req->status === 'APPROVED')
                                                        <span
                                                            class="badge bg-success-subtle text-success px-2 py-1 fs-11">APPROVED</span>
                                                    @elseif($req->status === 'REJECTED')
                                                        <span
                                                            class="badge bg-danger-subtle text-danger px-2 py-1 fs-11">REJECTED</span>
                                                    @else
                                                        <span
                                                            class="badge bg-warning-subtle text-warning px-2 py-1 fs-11">PENDING</span>
                                                    @endif
                                                </td>
                                                <td class="pe-3 text-muted small">
                                                    {{ $req->admin_remarks ?? ($req->remarks ?: '—') }}</td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4 text-muted small">No requests submitted
                                                    yet.</td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-between align-items-center p-3 border-top">
                                <span class="text-muted small">Showing {{ $requests->firstItem() ?? 0 }} to
                                    {{ $requests->lastItem() ?? 0 }} of {{ $requests->total() }} entries</span>
                                @if($requests->hasPages())
                                    {{ $requests->links() }}
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Request top-up form -->
        <div class="col-xl-4 col-lg-5" id="request-topup-section">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-0 py-3 border-bottom d-flex align-items-center gap-2">
                    <iconify-icon icon="solar:card-send-bold-duotone" class="text-primary fs-20"></iconify-icon>
                    <h5 class="fw-bold text-slate-800 mb-0">Request Wallet Top-up</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        Transfer funds to the bank account on the left via UPI/NetBanking, then upload the receipt and enter
                        the UTR/Txn ID below.
                    </p>

                    <form action="{{ route('adc.wallet.request.store') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-slate-700">Amount Sent (₹) <span
                                    class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">₹</span>
                                <input type="number" name="amount" class="form-control" placeholder="0.00" min="1"
                                    step="any" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-slate-700">UTR / Reference Number <span
                                    class="text-danger">*</span></label>
                            <input type="text" name="utr_number" class="form-control"
                                placeholder="12-digit UTR or Transaction ID" required>
                            <small class="text-muted">Ensure this matches the UTR on your payment receipt.</small>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-slate-700">Payment Receipt File <span
                                    class="text-danger">*</span></label>
                            <input type="file" id="payment_receipt_file" class="form-control"
                                accept="image/*,application/pdf" required>
                            <input type="hidden" name="payment_receipt" id="payment_receipt_url" required>
                            <small class="text-muted">Image or PDF (Max 5MB).</small>

                            <!-- Visual progress/feedback element -->
                            <div id="receiptUploadStatus" class="mt-2 d-none">
                                <div class="p-2 border rounded d-flex align-items-center justify-content-between bg-light">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="spinner-border spinner-border-sm text-primary" role="status"
                                            id="receiptSpinner"></div>
                                        <iconify-icon icon="solar:document-text-bold-duotone"
                                            class="text-success fs-18 d-none" id="receiptFileIcon"></iconify-icon>
                                        <span id="receiptStatusText" class="small fw-semibold text-slate-700">Uploading
                                            receipt...</span>
                                    </div>
                                    <div id="receiptPreviewLinkContainer" class="d-none">
                                        <a href="#" id="receiptPreviewLink" target="_blank"
                                            class="btn btn-xs btn-outline-primary py-0.5 px-2 fs-10">
                                            View
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-slate-700">Remarks (Optional)</label>
                            <textarea class="form-control" name="remarks" rows="2"
                                placeholder="Any additional notes..."></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100 fw-semibold py-2" id="submitTopupBtn" disabled
                            data-originally-disabled="true">
                            <iconify-icon icon="solar:upload-bold-duotone" class="me-1 align-middle fs-16"></iconify-icon>
                            Submit Top-up Request
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            // Preserve tab selection on page reload using URL params or localStorage
            const urlParams = new URLSearchParams(window.location.search);
            let activeTabId = 'tx-tab'; // default

            if (urlParams.has('req_page') || localStorage.getItem('adc_wallet_active_tab') === 'req-tab') {
                activeTabId = 'req-tab';
            } else if (urlParams.has('tx_page') || localStorage.getItem('adc_wallet_active_tab') === 'tx-tab') {
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
                    localStorage.setItem('adc_wallet_active_tab', e.target.id);
                });
            });

            // Handle AJAX file upload with compression compatibility
            const fileInput = document.getElementById('payment_receipt_file');
            const urlInput = document.getElementById('payment_receipt_url');
            const statusContainer = document.getElementById('receiptUploadStatus');
            const spinner = document.getElementById('receiptSpinner');
            const fileIcon = document.getElementById('receiptFileIcon');
            const statusText = document.getElementById('receiptStatusText');
            const previewContainer = document.getElementById('receiptPreviewLinkContainer');
            const previewLink = document.getElementById('receiptPreviewLink');
            const submitBtn = document.getElementById('submitTopupBtn');

            fileInput.addEventListener('change', function () {
                const file = fileInput.files[0];
                if (!file) {
                    urlInput.value = '';
                    submitBtn.disabled = true;
                    return;
                }

                // If file is an image and isCompressing is not 'done', wait for image-compressor.js
                const isImg = file.type.startsWith('image/');
                if (isImg && fileInput.dataset.isCompressing !== 'done') {
                    return;
                }

                // Proceed to upload via AJAX
                urlInput.value = '';
                statusContainer.classList.remove('d-none');
                spinner.classList.remove('d-none');
                fileIcon.classList.add('d-none');
                statusText.className = 'small fw-semibold text-slate-700';
                statusText.textContent = 'Uploading file to service...';
                previewContainer.classList.add('d-none');
                submitBtn.disabled = true;

                const formData = new FormData();
                formData.append('file', file);
                formData.append('_token', '{{ csrf_token() }}');

                fetch('{{ route("adc.wallet.upload-receipt") }}', {
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
                            statusText.className = 'small fw-semibold text-success';
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
                        statusText.className = 'small fw-semibold text-danger';
                        statusText.textContent = err.message || 'Failed to upload to file service. Please retry.';
                        submitBtn.disabled = true;
                    });
            });
        });
    </script>
@endpush