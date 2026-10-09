@extends('layouts.admin')

@section('title', 'Seller Wallets Management')
@section('page-title', 'Seller Wallets & Balances')

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
                                Seller Wallets Management
                            </h4>
                            <p class="text-muted small mb-0">View all seller wallet balances and perform manual credit/debit adjustments.</p>
                        </div>
                        <div class="col-md-6 text-md-end">
                            <a href="{{ route('admin.external.vendor.wallet-requests') }}" class="btn btn-outline-primary btn-sm">
                                <iconify-icon icon="solar:document-text-bold-duotone" class="align-middle me-1 fs-16"></iconify-icon>
                                Seller Wallet Requests
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Filter Form -->
    <div class="row mb-3">
        <div class="col-md-12">
            <form action="{{ route('admin.wallets.seller') }}" method="GET" class="d-flex flex-wrap gap-2">
                <div class="input-group" style="max-width: 300px;">
                    <span class="input-group-text bg-white border-end-0"><iconify-icon icon="solar:magnifer-bold-duotone" class="text-muted"></iconify-icon></span>
                    <input type="text" name="search" class="form-control border-start-0" placeholder="Search Seller ID, Name, Phone..." value="{{ $search }}">
                </div>
                <select name="limit" class="form-select w-auto" onchange="this.form.submit()">
                    <option value="15" {{ $limit == 15 ? 'selected' : '' }}>15 per page</option>
                    <option value="25" {{ $limit == 25 ? 'selected' : '' }}>25 per page</option>
                    <option value="50" {{ $limit == 50 ? 'selected' : '' }}>50 per page</option>
                </select>
                <button type="submit" class="btn btn-outline-secondary">Filter</button>
                @if($search)
                    <a href="{{ route('admin.wallets.seller') }}" class="btn btn-outline-danger">Clear</a>
                @endif
            </form>
        </div>
    </div>

    <!-- Seller Wallets Table -->
    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover table-centered mb-0 text-nowrap align-middle">
                    <thead class="bg-light bg-opacity-50 border-top border-bottom fs-13">
                        <tr>
                            <th class="ps-3" style="width: 50px;">#</th>
                            <th>Seller / Company</th>
                            <th>Seller ID</th>
                            <th>Mobile</th>
                            <th>State / District</th>
                            <th>Current Balance</th>
                            <th class="text-end pe-3" style="width: 150px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="fs-13">
                        @forelse($sellers as $index => $seller)
                            @php
                                $profile = $seller->sellerProfile;
                                $balance = $profile->wallet_balance ?? 0.00;
                            @endphp
                            <tr>
                                <td class="ps-3 text-muted">{{ $sellers->firstItem() + $index }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="avatar-sm flex-shrink-0">
                                            <span class="avatar-title bg-primary-subtle text-primary fw-bold rounded-circle">
                                                {{ strtoupper(substr($seller->name, 0, 1)) }}
                                            </span>
                                        </div>
                                        <div>
                                            <h6 class="mb-0 fw-semibold text-slate-800">{{ $seller->name }}</h6>
                                            <span class="text-muted fs-11">{{ $seller->email }}</span>
                                            @if($profile->company_name ?? false)
                                                <span class="text-muted fs-11 d-block">Co: {{ $profile->company_name }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark font-monospace border">
                                        {{ $profile->seller_id ?? 'IMV' . $seller->id }}
                                    </span>
                                </td>
                                <td>{{ $seller->mobile }}</td>
                                <td>{{ $profile->state ?? '—' }} / {{ $profile->district ?? '—' }}</td>
                                <td>
                                    <strong class="fs-15 {{ $balance > 0 ? 'text-success' : 'text-slate-800' }}">
                                        ₹{{ number_format($balance, 2) }}
                                    </strong>
                                </td>
                                <td class="text-end pe-3">
                                    @if(auth()->user()->is_super_admin === '1' || auth()->user()->hasPermission('users.manage'))
                                        <button type="button" class="btn btn-xs btn-primary fw-semibold px-2 py-1" data-bs-toggle="modal" data-bs-target="#rechargeModal{{ $seller->id }}">
                                            <iconify-icon icon="solar:wallet-money-bold" class="align-middle me-1"></iconify-icon> Recharge
                                        </button>
                                    @endif
                                </td>
                            </tr>

                            <!-- Recharge / Adjust Modal -->
                            <div class="modal fade" id="rechargeModal{{ $seller->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-dialog-centered">
                                    <div class="modal-content border-0 shadow">
                                        <div class="modal-header bg-primary text-white py-3">
                                            <h5 class="modal-header-title fw-bold text-white fs-15 mb-0">Adjust {{ $seller->name }}'s Wallet</h5>
                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="{{ route('admin.wallets.seller.recharge', $seller->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-body p-4">
                                                <div class="p-2 bg-light rounded border mb-3">
                                                    <span class="text-muted fs-11 d-block">Current Available Balance:</span>
                                                    <strong class="fs-16 text-success font-monospace">₹{{ number_format($balance, 2) }}</strong>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold fs-13">Action Type <span class="text-danger">*</span></label>
                                                    <select name="type" class="form-select" required>
                                                        <option value="credit">CREDIT (Add Funds to Seller)</option>
                                                        <option value="debit">DEBIT (Deduct Funds from Seller)</option>
                                                    </select>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold fs-13">Amount (₹) <span class="text-danger">*</span></label>
                                                    <div class="input-group">
                                                        <span class="input-group-text bg-light fw-bold">₹</span>
                                                        <input type="number" step="0.01" min="1" class="form-control" name="amount" placeholder="e.g. 1000" required>
                                                    </div>
                                                </div>

                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold fs-13">Remarks / Reason <span class="text-danger">*</span></label>
                                                    <input type="text" name="remarks" class="form-control" placeholder="e.g. Incentive Payout / Adjustment" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light py-2 px-3">
                                                <button type="button" class="btn btn-light btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary btn-sm px-4">
                                                    <iconify-icon icon="solar:check-circle-bold" class="align-middle me-1"></iconify-icon> Save Adjustment
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" class="fs-36 text-muted mb-2 d-block mx-auto"></iconify-icon>
                                    <h5>No Seller Accounts Found</h5>
                                    <p class="mb-0 fs-12">No sellers matching your search criteria.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        <div class="card-footer bg-white border-top py-2 px-3">
            {{ $sellers->appends(request()->query())->links() }}
        </div>
    </div>
@endsection
