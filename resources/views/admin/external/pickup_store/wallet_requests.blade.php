@extends('layouts.admin')

@section('title', 'Pickup Store Wallet Requests')
@section('page-title', 'Pickup Store Wallet Top-Up & Payout Requests')

@section('content')
    <!-- Action Header Card -->
    <div class="row mb-4">
        <div class="col-12">
            <div class="card shadow-sm border-0">
                <div class="card-body">
                    <div class="row align-items-center g-3">
                        <div class="col-md-6">
                            <h4 class="fw-bold text-slate-800 mb-1" style="font-family: 'Outfit', sans-serif;">
                                <iconify-icon icon="solar:wallet-money-bold-duotone" class="text-primary align-middle me-1"></iconify-icon>
                                Pickup Store Wallet & Payout Requests
                            </h4>
                            <p class="text-muted small mb-0">Review, verify, and approve or reject Authorized Pickup Store top-ups and withdrawal requests.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters Section -->
    <div class="row mb-3 g-2">
        <div class="col-md-12">
            <form id="filterForm" action="{{ route('admin.external.pickup_store.wallet-requests') }}" method="GET" class="d-flex flex-wrap gap-2">
                <input type="hidden" name="sort" id="formSort" value="{{ $sort }}">
                <input type="hidden" name="order" id="formOrder" value="{{ $order }}">
                
                <div class="input-group" style="max-width: 250px;">
                    <span class="input-group-text bg-white border-end-0"><iconify-icon icon="solar:magnifer-bold-duotone" class="text-muted"></iconify-icon></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="UTR, Store Code, Name, Acc..." value="{{ $search }}">
                </div>

                <select name="type" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="">All Request Types</option>
                    <option value="topup" {{ $type === 'topup' ? 'selected' : '' }}>Top-Up (Add Funds)</option>
                    <option value="withdrawal" {{ $type === 'withdrawal' ? 'selected' : '' }}>Withdrawal (Payout)</option>
                </select>

                <select name="status" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="PENDING" {{ $status === 'PENDING' ? 'selected' : '' }}>PENDING</option>
                    <option value="TRANSFERRED" {{ $status === 'TRANSFERRED' ? 'selected' : '' }}>TRANSFERRED (Pending Verification)</option>
                    <option value="APPROVED" {{ $status === 'APPROVED' ? 'selected' : '' }}>APPROVED</option>
                    <option value="REJECTED" {{ $status === 'REJECTED' ? 'selected' : '' }}>REJECTED</option>
                </select>

                <select name="limit" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="10" {{ $limit == 10 ? 'selected' : '' }}>10 per page</option>
                    <option value="15" {{ $limit == 15 ? 'selected' : '' }}>15 per page</option>
                    <option value="25" {{ $limit == 25 ? 'selected' : '' }}>25 per page</option>
                    <option value="50" {{ $limit == 50 ? 'selected' : '' }}>50 per page</option>
                </select>

                <button type="submit" class="btn btn-outline-secondary">Filter</button>
                @if($search || $status || $type)
                    <a href="{{ route('admin.external.pickup_store.wallet-requests') }}" class="btn btn-outline-danger">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Requests Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0 text-nowrap align-middle">
                    <thead class="bg-light bg-opacity-50 border-top border-bottom fs-13">
                        <tr>
                            <th class="ps-3" style="width: 50px;">#</th>
                            <th>Pickup Store</th>
                            <th>Type</th>
                            <th>Amount</th>
                            <th>Reference / Details</th>
                            <th>Receipt</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th class="text-end pe-3" style="width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="fs-13">
                        @forelse($requests as $index => $req)
                            <tr>
                                <td class="ps-3 text-muted">{{ $requests->firstItem() + $index }}</td>
                                <td>
                                    @if($req->pickupStore)
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="avatar-sm flex-shrink-0">
                                                <span class="avatar-title bg-primary-subtle text-primary fw-bold rounded-circle">
                                                    {{ strtoupper(substr($req->pickupStore->pickup_store_name, 0, 1)) }}
                                                </span>
                                            </div>
                                            <div>
                                                <h6 class="mb-0 fw-semibold text-slate-800">{{ $req->pickupStore->pickup_store_name }}</h6>
                                                <span class="badge bg-light text-dark font-monospace border fs-10">
                                                    {{ $req->pickupStore->pickup_store_code }}
                                                </span>
                                                @if($req->pickupStore->contact_person)
                                                    <span class="text-muted fs-11 ms-1">({{ $req->pickupStore->contact_person }})</span>
                                                @endif
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-muted">Deleted Store</span>
                                    @endif
                                </td>
                                <td>
                                    @if(strtolower($req->type) === 'topup')
                                        <span class="badge px-2.5 py-1 fw-semibold fs-11" style="color: #0369a1; background-color: #e0f2fe; border: 1px solid #bae6fd;">
                                            <iconify-icon icon="solar:card-send-bold" class="align-middle me-1"></iconify-icon> Top-Up
                                        </span>
                                    @else
                                        <span class="badge px-2.5 py-1 fw-semibold fs-11" style="color: #6b21a8; background-color: #f3e8ff; border: 1px solid #e9d5ff;">
                                            <iconify-icon icon="solar:card-receive-bold" class="align-middle me-1"></iconify-icon> Withdrawal
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    <strong class="fs-14 {{ strtolower($req->type) === 'topup' ? 'text-success' : 'text-danger' }}">
                                        ₹{{ number_format($req->amount, 2) }}
                                    </strong>
                                </td>
                                <td>
                                    @if(strtolower($req->type) === 'topup')
                                        <span class="text-muted fs-11 d-block">UTR Number:</span>
                                        <span class="font-monospace fw-semibold text-slate-800">{{ $req->utr_number ?: 'N/A' }}</span>
                                    @else
                                        <div class="bg-light p-2 rounded border fs-12" style="max-width: 280px;">
                                            <div class="fw-bold text-dark mb-1">
                                                <iconify-icon icon="solar:user-bold" class="align-middle me-1 text-primary"></iconify-icon>{{ $req->account_holder_name ?: ($req->pickupStore->contact_person ?? '—') }}
                                            </div>
                                            <div class="text-secondary small mb-0.5">
                                                <strong>Bank:</strong> {{ $req->bank_name ?: '—' }}
                                            </div>
                                            <div class="font-monospace text-slate-800 fw-semibold mb-0.5">
                                                <strong>A/C:</strong> {{ $req->bank_account_number ?: '—' }}
                                            </div>
                                            <div class="font-monospace text-muted fs-11">
                                                <strong>IFSC:</strong> {{ $req->ifsc_code ?: '—' }}
                                                @if($req->branch_name) | <strong>Branch:</strong> {{ $req->branch_name }} @endif
                                            </div>
                                            @if($req->utr_number)
                                                <div class="mt-1 pt-1 border-top text-success font-monospace fs-11">
                                                    <strong>Payment UTR:</strong> {{ $req->utr_number }}
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    @if($req->payment_receipt)
                                        <a href="{{ filter_var($req->payment_receipt, FILTER_VALIDATE_URL) ? $req->payment_receipt : asset('storage/' . $req->payment_receipt) }}" target="_blank" class="btn btn-xs btn-outline-info py-0.5 px-2">
                                            <iconify-icon icon="solar:eye-bold" class="align-middle me-1"></iconify-icon> Receipt
                                        </a>
                                    @else
                                        <span class="text-muted fs-11">N/A</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-slate-800 d-block">{{ $req->created_at->format('d M Y') }}</span>
                                    <span class="text-muted fs-11">{{ $req->created_at->format('h:i A') }}</span>
                                </td>
                                <td>
                                    @if($req->status === 'APPROVED')
                                        <span class="badge bg-success bg-opacity-10 text-success px-2.5 py-1 fw-bold">APPROVED & COMPLETED</span>
                                    @elseif($req->status === 'TRANSFERRED')
                                        <span class="badge bg-info bg-opacity-10 text-info px-2.5 py-1 fw-bold">TRANSFERRED (Pending Verification)</span>
                                    @elseif($req->status === 'REJECTED')
                                        <span class="badge bg-danger bg-opacity-10 text-danger px-2.5 py-1 fw-bold">REJECTED</span>
                                    @else
                                        <span class="badge bg-warning bg-opacity-10 text-warning px-2.5 py-1 fw-bold">PENDING</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    @if($req->status === 'PENDING')
                                        @if(auth()->user()->is_super_admin === '1' || auth()->user()->hasPermission('users.manage'))
                                            @if(strtolower($req->type) === 'withdrawal')
                                                <!-- Process Payout Modal Trigger -->
                                                <button type="button" class="btn btn-xs btn-success fw-semibold px-2 py-1 me-1" 
                                                    onclick="openPayoutModal({{ json_encode([
                                                        'id' => $req->id,
                                                        'store_name' => $req->pickupStore->pickup_store_name ?? 'Store',
                                                        'contact_person' => $req->pickupStore->contact_person ?? '',
                                                        'store_code' => $req->pickupStore->pickup_store_code ?? 'PSC' . $req->pickup_store_id,
                                                        'amount' => number_format($req->amount, 2),
                                                        'account_holder_name' => $req->account_holder_name ?: ($req->pickupStore->contact_person ?? ''),
                                                        'bank_name' => $req->bank_name ?? '',
                                                        'bank_account_number' => $req->bank_account_number ?? '',
                                                        'ifsc_code' => $req->ifsc_code ?? '',
                                                        'branch_name' => $req->branch_name ?? '',
                                                        'available_balance' => number_format($req->pickupStore->wallet_balance ?? 0, 2)
                                                    ]) }})">
                                                    <iconify-icon icon="solar:card-send-bold" class="align-middle me-0.5"></iconify-icon> Process Payout
                                                </button>
                                            @else
                                                <!-- Approve Topup Form -->
                                                <form action="{{ route('admin.external.pickup_store.wallet-requests.approve', $req->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to APPROVE this top-up request of ₹{{ number_format($req->amount, 2) }}?');">
                                                    @csrf
                                                    <button type="submit" class="btn btn-xs btn-success fw-semibold px-2 py-1 me-1" title="Approve Top-Up">
                                                        <iconify-icon icon="solar:check-circle-bold" class="align-middle me-0.5"></iconify-icon> Approve Top-Up
                                                    </button>
                                                </form>
                                            @endif

                                            <!-- Reject Modal Trigger -->
                                            <button type="button" class="btn btn-xs btn-outline-danger fw-semibold px-2 py-1" onclick="openRejectModal({{ $req->id }}, '{{ $req->pickupStore->pickup_store_name ?? 'Store' }}', '{{ number_format($req->amount, 2) }}', '{{ $req->type }}')">
                                                <iconify-icon icon="solar:close-circle-bold" class="align-middle me-0.5"></iconify-icon> Reject
                                            </button>
                                        @endif
                                    @elseif($req->status === 'TRANSFERRED')
                                        <span class="badge bg-info-subtle text-info border border-info border-opacity-25 px-2 py-1 fs-11" title="Payout transferred by admin, waiting for pickup store confirmation">
                                            <iconify-icon icon="solar:clock-circle-bold" class="align-middle me-0.5"></iconify-icon> Awaiting Store Verification
                                        </span>
                                    @else
                                        <span class="text-muted fs-11">Processed</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <iconify-icon icon="solar:notes-minimalistic-bold-duotone" class="fs-36 text-muted mb-2 d-block mx-auto"></iconify-icon>
                                    <h5>No Store Wallet Requests Found</h5>
                                    <p class="mb-0 fs-12">There are no wallet top-up or withdrawal requests matching the filters.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-2 px-3">
            {{ $requests->appends(request()->query())->links() }}
        </div>
    </div>

    <!-- Process Payout Verification Modal -->
    <div class="modal fade" id="payoutModal" tabindex="-1" aria-labelledby="payoutModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title fw-bold text-white fs-15 mb-0" id="payoutModalLabel">
                        <iconify-icon icon="solar:card-send-bold" class="align-middle me-1 fs-18"></iconify-icon>
                        Process & Verify Pickup Store Payout
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="payoutForm" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="modal-body p-4">
                        <!-- Store & Payout Header -->
                        <div class="card bg-light border mb-3">
                            <div class="card-body p-3">
                                <div class="row align-items-center">
                                    <div class="col-md-7">
                                        <h6 class="mb-1 fw-bold text-dark fs-14" id="payoutStoreName">Pickup Store Name</h6>
                                        <div class="text-muted fs-12 mb-1">
                                            Store Code: <span class="font-monospace fw-semibold text-dark" id="payoutStoreCode">--</span>
                                        </div>
                                        <div class="text-muted fs-12">
                                            Available Wallet Balance: <strong class="text-primary" id="payoutAvailableBalance">₹0.00</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-5 text-md-end border-start border-md-start-0 pt-2 pt-md-0">
                                        <span class="text-muted fs-11 d-block text-uppercase fw-semibold">Payout Requested Amount</span>
                                        <h3 class="text-danger fw-extrabold mb-0" id="payoutAmount">₹0.00</h3>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Target Bank Account Details Card -->
                        <div class="card border-primary border-opacity-25 bg-primary bg-opacity-10 mb-3">
                            <div class="card-header bg-transparent border-bottom border-primary border-opacity-10 py-2 d-flex justify-content-between align-items-center">
                                <span class="fw-bold text-primary fs-13">
                                    <iconify-icon icon="solar:bank-bold-duotone" class="align-middle me-1"></iconify-icon>
                                    Target Bank Account Details for Transfer
                                </span>
                                <span class="badge bg-primary text-white fs-10">Verify Details Before Transfer</span>
                            </div>
                            <div class="card-body p-3">
                                <div class="row g-3 fs-13">
                                    <div class="col-md-6">
                                        <span class="text-muted d-block fs-11">Account Holder Name:</span>
                                        <div class="d-flex align-items-center justify-content-between bg-white p-2 rounded border">
                                            <strong class="text-dark" id="payoutHolderName">--</strong>
                                            <button type="button" class="btn btn-xs btn-link p-0 text-decoration-none ms-1" onclick="copyText('payoutHolderName')" title="Copy Holder Name">
                                                <iconify-icon icon="solar:copy-bold" class="fs-14 text-muted"></iconify-icon>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="text-muted d-block fs-11">Bank Name:</span>
                                        <div class="bg-white p-2 rounded border">
                                            <strong class="text-dark" id="payoutBankName">--</strong>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="text-muted d-block fs-11">Bank Account Number:</span>
                                        <div class="d-flex align-items-center justify-content-between bg-white p-2 rounded border font-monospace">
                                            <strong class="text-dark fs-14" id="payoutAccountNo">--</strong>
                                            <button type="button" class="btn btn-xs btn-link p-0 text-decoration-none ms-1" onclick="copyText('payoutAccountNo')" title="Copy Account Number">
                                                <iconify-icon icon="solar:copy-bold" class="fs-14 text-muted"></iconify-icon>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <span class="text-muted d-block fs-11">IFSC Code:</span>
                                        <div class="d-flex align-items-center justify-content-between bg-white p-2 rounded border font-monospace">
                                            <strong class="text-dark fs-14" id="payoutIfsc">--</strong>
                                            <button type="button" class="btn btn-xs btn-link p-0 text-decoration-none ms-1" onclick="copyText('payoutIfsc')" title="Copy IFSC">
                                                <iconify-icon icon="solar:copy-bold" class="fs-14 text-muted"></iconify-icon>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-12" id="payoutBranchRow">
                                        <span class="text-muted d-block fs-11">Branch Name:</span>
                                        <div class="bg-white p-2 rounded border">
                                            <span class="text-dark" id="payoutBranch">--</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Verification Payment Details Input -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold fs-13">Payment UTR / Reference No. <span class="text-danger">*</span></label>
                                <input type="text" name="utr_number" class="form-control font-monospace" placeholder="e.g. UTR1234567890 / IMPS / NEFT Ref" required>
                                <span class="text-muted fs-11">Enter bank transfer UTR confirmation number.</span>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold fs-13">Upload Transfer Receipt / Screenshot <span class="text-danger">*</span></label>
                                <input type="file" name="payment_receipt" class="form-control" accept="image/*,.pdf" required>
                                <span class="text-muted fs-11">Attach bank transfer confirmation receipt image or PDF (Max 5MB).</span>
                            </div>
                            <div class="col-12">
                                <label class="form-label fw-semibold fs-13">Admin Remarks / Notes</label>
                                <textarea name="remarks" class="form-control" rows="2" placeholder="e.g. Transferred via HDFC NetBanking..."></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2 px-3">
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-success btn-sm px-4 fw-bold">
                            <iconify-icon icon="solar:check-circle-bold" class="align-middle me-1 fs-16"></iconify-icon>
                            Confirm Payment & Approve Payout
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1" aria-labelledby="rejectModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white py-3">
                    <h5 class="modal-header-title fw-bold text-white fs-15 mb-0" id="rejectModalLabel">Reject Wallet Request</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form id="rejectForm" method="POST">
                    @csrf
                    <div class="modal-body p-4">
                        <p class="fs-13 text-slate-800 mb-3">
                            You are rejecting the <strong id="modalReqType">top-up</strong> request of <strong id="modalReqAmount">₹0.00</strong> for store <strong id="modalStoreName">Store</strong>.
                        </p>
                        <div class="mb-3">
                            <label class="form-label fw-semibold fs-13">Reason for Rejection <span class="text-danger">*</span></label>
                            <textarea name="remarks" class="form-control" rows="3" placeholder="Enter reason for rejecting this request..." required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2 px-3">
                        <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-sm px-4">
                            <iconify-icon icon="solar:close-circle-bold" class="align-middle me-1"></iconify-icon>
                            Confirm Rejection
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        function openPayoutModal(data) {
            const form = document.getElementById('payoutForm');
            form.action = "{{ route('admin.external.pickup_store.wallet-requests.approve', ':id') }}".replace(':id', data.id);

            document.getElementById('payoutStoreName').textContent = data.store_name;
            document.getElementById('payoutStoreCode').textContent = data.store_code;
            document.getElementById('payoutAvailableBalance').textContent = '₹' + data.available_balance;
            document.getElementById('payoutAmount').textContent = '₹' + data.amount;

            document.getElementById('payoutHolderName').textContent = data.account_holder_name || '—';
            document.getElementById('payoutBankName').textContent = data.bank_name || '—';
            document.getElementById('payoutAccountNo').textContent = data.bank_account_number || '—';
            document.getElementById('payoutIfsc').textContent = data.ifsc_code || '—';

            if (data.branch_name) {
                document.getElementById('payoutBranchRow').style.display = 'block';
                document.getElementById('payoutBranch').textContent = data.branch_name;
            } else {
                document.getElementById('payoutBranchRow').style.display = 'none';
            }

            const modal = new bootstrap.Modal(document.getElementById('payoutModal'));
            modal.show();
        }

        function openRejectModal(id, storeName, amount, type) {
            const form = document.getElementById('rejectForm');
            form.action = "{{ route('admin.external.pickup_store.wallet-requests.reject', ':id') }}".replace(':id', id);

            document.getElementById('modalStoreName').textContent = storeName;
            document.getElementById('modalReqAmount').textContent = '₹' + amount;
            document.getElementById('modalReqType').textContent = type === 'topup' ? 'top-up' : 'withdrawal';

            const modal = new bootstrap.Modal(document.getElementById('rejectModal'));
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
    </script>
@endpush
