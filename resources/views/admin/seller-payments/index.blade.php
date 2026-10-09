@extends('layouts.admin')

@section('title', 'Seller Payments')
@section('page-title', 'Seller Payments')

@section('content')
    <!-- Page Header -->
    <x-admin.page-header title="Seller Payments"
        subtitle="Verify DDC payments, reconcile amounts, and manage payouts to vendors and sub-sellers." />

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-white">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-primary bg-opacity-10 text-primary rounded p-3 me-3">
                        <iconify-icon icon="solar:wallet-bold" class="fs-24 align-middle"></iconify-icon>
                    </div>
                    <div>
                        <span class="text-muted small d-block">DDC Paid Total</span>
                        <h4 class="fw-bold text-slate-800 mb-0">₹{{ number_format($totalDdcPaid, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-white">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-info bg-opacity-10 text-info rounded p-3 me-3">
                        <iconify-icon icon="solar:bill-list-bold" class="fs-24 align-middle"></iconify-icon>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Due to Sellers</span>
                        <h4 class="fw-bold text-slate-800 mb-0">₹{{ number_format($totalDueToSellers, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-white">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-success bg-opacity-10 text-success rounded p-3 me-3">
                        <iconify-icon icon="solar:check-circle-bold" class="fs-24 align-middle"></iconify-icon>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Paid to Sellers</span>
                        <h4 class="fw-bold text-slate-800 mb-0">₹{{ number_format($totalPaidToSellers, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm bg-white">
                <div class="card-body d-flex align-items-center">
                    <div class="bg-warning bg-opacity-10 text-warning rounded p-3 me-3">
                        <iconify-icon icon="solar:clock-square-bold" class="fs-24 align-middle"></iconify-icon>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Pending Payouts</span>
                        <h4 class="fw-bold text-slate-800 mb-0">₹{{ number_format($totalPendingToSellers, 2) }}</h4>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search & Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form action="{{ route('admin.seller-payments.index') }}" method="GET" class="row g-3">
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-slate-700">Search</label>
                    <input type="text" name="search" class="form-control form-control-sm" placeholder="PO Number, DDC or Seller..." value="{{ request('search') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label small fw-semibold text-slate-700">Seller Payment Status</label>
                    <select name="seller_payment_status" class="form-select form-select-sm">
                        <option value="">All Statuses</option>
                        <option value="Unpaid" {{ request('seller_payment_status') === 'Unpaid' ? 'selected' : '' }}>Unpaid</option>
                        <option value="Paid" {{ request('seller_payment_status') === 'Paid' ? 'selected' : '' }}>Paid</option>
                    </select>
                </div>
                <div class="col-md-2 d-flex align-items-end gap-1">
                    <button type="submit" class="btn btn-sm btn-primary w-100 py-1">Filter</button>
                    <a href="{{ route('admin.seller-payments.index') }}" class="btn btn-sm btn-light py-1" title="Reset Filters">
                        <iconify-icon icon="solar:restart-bold" class="align-middle"></iconify-icon>
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Payments Table Card -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-0 py-3 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="fw-bold text-slate-800 mb-0">Payout Records</h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0 align-middle">
                    <thead class="bg-light bg-opacity-50 border-top border-bottom">
                        <tr>
                            <th class="ps-3" style="width: 120px;">PO Number</th>
                            <th>DDC / Coordinator</th>
                            <th>Seller / Company</th>
                            <th>Payout Recipient & Bank</th>
                            <th class="text-end">DDC Paid</th>
                            <th class="text-end">Due to Seller</th>
                            <th class="text-end">Margin</th>
                            <th class="text-center">Payout Status</th>
                            <th class="text-center">Verification</th>
                            <th class="pe-3 text-center" style="width: 220px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchaseOrders as $po)
                            @php
                                $refText = $po->seller_payment_reference;
                                $decodedRef = json_decode($refText, true);
                                
                                if (json_last_error() === JSON_ERROR_NONE && is_array($decodedRef)) {
                                    $utr = $decodedRef['utr'] ?? '';
                                    $slipUrl = $decodedRef['slip'] ?? '';
                                    $savedBank = [
                                        'account_holder_name' => $decodedRef['account_holder_name'] ?? null,
                                        'bank_account_number' => $decodedRef['account_number'] ?? null,
                                        'ifsc_code' => $decodedRef['ifsc'] ?? null,
                                        'bank_name' => $decodedRef['bank_name'] ?? null,
                                        'branch_name' => $decodedRef['branch_name'] ?? null,
                                    ];
                                    $hasSavedBank = !empty($savedBank['bank_account_number']) || !empty($savedBank['ifsc_code']);
                                } else {
                                    $isCombined = preg_match('/UTR:\s*(.*?)\s*\|\s*Slip:\s*(.*)/', $refText, $matches);
                                    $utr = $isCombined ? $matches[1] : $refText;
                                    $slipUrl = $isCombined ? $matches[2] : (Str::startsWith($refText, ['http://', 'https://', 'seller_payments/', 'uploads/']) ? $refText : null);
                                    $savedBank = null;
                                    $hasSavedBank = false;
                                }

                                $seller = $po->seller;
                                $store = $po->pickupStore;
                                $payToStore = $store && $store->can_receive_payment_and_dispatch;
                                $profile = $seller?->sellerProfile;

                                $isSubSeller = $profile && $profile->parent_id;
                                $parentSeller = $isSubSeller ? \App\Models\User::find($profile->parent_id) : null;

                                if (($po->seller_payment_status === 'Paid' || $po->payment_verification_status === 'Verified') && $hasSavedBank) {
                                    $bankObj = (object)$savedBank;
                                } else {
                                    $bankObj = $payToStore ? $store : $profile;
                                }
                            @endphp
                            <tr>
                                <td class="ps-3 fw-semibold">
                                    <a href="{{ route('admin.purchase-orders.show', $po->id) }}">
                                        #{{ $po->po_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-semibold text-slate-800">{{ $po->ddc?->name }}</div>
                                    <div class="text-muted small">Physical DDC</div>
                                </td>
                                <td>
                                    <div class="fw-semibold text-slate-800">
                                        {{ $po->seller?->company_name ?: $po->seller?->name }}</div>
                                    @if($isSubSeller)
                                        <span class="badge bg-warning-subtle text-warning px-1 py-0 fs-10">Sub-Seller</span>
                                        @if($parentSeller)
                                            <div class="text-muted" style="font-size:10px;">Parent: {{ $parentSeller->name }}</div>
                                        @endif
                                    @else
                                        <span class="badge bg-info-subtle text-info px-1 py-0 fs-10">Main Seller</span>
                                    @endif
                                </td>
                                <td>
                                    @if($payToStore && $store)
                                        <div class="d-flex align-items-center gap-1.5 mb-1.5">
                                            <span class="badge bg-success text-white px-1.5 py-0.5 fs-10" style="font-size: 9.5px; border-radius: 4px;">Store</span>
                                            <span class="fw-bold text-slate-800 fs-12">{{ $store->pickup_store_name }}</span>
                                        </div>
                                    @else
                                        <div class="d-flex align-items-center gap-1.5 mb-1.5">
                                             @if($isSubSeller)
                                                 <span class="badge bg-warning text-dark px-1.5 py-0.5 fs-10" style="font-size: 9.5px; border-radius: 4px;">Sub-Seller</span>
                                             @else
                                                 <span class="badge bg-info text-white px-1.5 py-0.5 fs-10" style="font-size: 9.5px; border-radius: 4px;">Main Seller</span>
                                             @endif
                                            <span class="fw-bold text-slate-800 fs-12">{{ $seller?->name ?? '—' }}</span>
                                        </div>
                                    @endif

                                    @if($bankObj && ($bankObj->bank_account_number || $bankObj->ifsc_code))
                                        <div class="text-slate-700 fs-11" style="line-height: 1.45;">
                                            <div class="mb-0.5 text-truncate" style="max-width: 200px;" title="Holder: {{ $bankObj->account_holder_name ?? '—' }}">
                                                <span class="text-slate-400 fw-semibold">Holder:</span> <span class="fw-semibold text-dark">{{ $bankObj->account_holder_name ?? '—' }}</span>
                                            </div>
                                            <div class="mb-0.5 d-flex align-items-center">
                                                <span class="text-slate-400 fw-semibold me-1">A/C:</span>
                                                <code class="text-dark bg-light px-1.5 py-0 border rounded fs-11" style="font-family: monospace; font-weight: 600;">{{ $bankObj->bank_account_number }}</code>
                                                <button type="button" class="btn btn-link btn-xs p-0 text-muted ms-1.5 d-inline-flex border-0 bg-transparent align-middle"
                                                    onclick="copyToClipboard('{{ $bankObj->bank_account_number }}', 'tblCopyAcc{{ $po->id }}', 'Account Number')"
                                                    title="Copy Account Number">
                                                    <iconify-icon icon="solar:copy-bold-duotone" id="tblCopyAcc{{ $po->id }}" class="fs-12 text-muted"></iconify-icon>
                                                </button>
                                            </div>
                                            <div class="d-flex align-items-center">
                                                <span class="text-slate-400 fw-semibold me-1">IFSC:</span>
                                                <code class="text-primary bg-light px-1.5 py-0 border rounded fs-11" style="font-family: monospace; font-weight: 600;">{{ $bankObj->ifsc_code }}</code>
                                                <button type="button" class="btn btn-link btn-xs p-0 text-muted ms-1.5 d-inline-flex border-0 bg-transparent align-middle"
                                                    onclick="copyToClipboard('{{ $bankObj->ifsc_code }}', 'tblCopyIfsc{{ $po->id }}', 'IFSC Code')"
                                                    title="Copy IFSC Code">
                                                    <iconify-icon icon="solar:copy-bold-duotone" id="tblCopyIfsc{{ $po->id }}" class="fs-12 text-muted"></iconify-icon>
                                                </button>
                                            </div>
                                            @if($bankObj->bank_name)
                                                <div class="text-slate-500 mt-1 small" style="font-size: 10px; font-weight: 500;">
                                                    <iconify-icon icon="solar:card-bold-duotone" class="align-middle me-0.5 text-slate-400" style="font-size: 11px;"></iconify-icon>{{ $bankObj->bank_name }}
                                                </div>
                                            @endif
                                        </div>
                                    @else
                                        <div class="d-inline-flex align-items-center text-danger small mt-1 py-0.5 px-2 bg-danger bg-opacity-10 rounded border border-danger border-opacity-10">
                                            <iconify-icon icon="solar:danger-triangle-bold-duotone" class="me-1 fs-12"></iconify-icon>
                                            No bank details
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end fw-bold text-slate-800">₹{{ number_format($po->accepted_amount, 2) }}</td>
                                <td class="text-end fw-bold text-success">
                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                        <span>₹{{ number_format($po->seller_net_payable, 2) }}</span>
                                        <button type="button" class="btn btn-link btn-xs p-0 text-muted d-inline-flex border-0 bg-transparent align-middle"
                                            data-bs-toggle="popover" 
                                            data-bs-trigger="hover focus"
                                            data-bs-html="true"
                                            title="Payout Calculation"
                                            data-bs-content="
                                                <div class='small'>
                                                    <div class='d-flex justify-content-between mb-1 gap-4'><strong>DDC Paid:</strong> <span>₹{{ number_format($po->accepted_amount, 2) }}</span></div>
                                                    <div class='d-flex justify-content-between mb-1 text-primary gap-4'><strong>Platform Margin:</strong> <span>- ₹{{ number_format($po->accepted_amount - $po->seller_accepted_amount, 2) }}</span></div>
                                                    <hr class='my-1'>
                                                    <div class='d-flex justify-content-between mb-1 gap-4'><strong>Seller Base:</strong> <span>₹{{ number_format($po->seller_accepted_amount, 2) }}</span></div>
                                                    @if($po->ssi_applicable && $po->ssi_amount > 0)
                                                        <div class='d-flex justify-content-between mb-1 text-danger gap-4'><strong>{{ $po->ssi_type }} Hold ({{ $po->ssi_percentage }}%):</strong> <span>- ₹{{ number_format($po->ssi_amount, 2) }}</span></div>
                                                    @endif
                                                    <hr class='my-1'>
                                                    <div class='d-flex justify-content-between fw-bold text-success gap-4'><strong>Net Payout:</strong> <span>₹{{ number_format($po->seller_net_payable, 2) }}</span></div>
                                                </div>
                                            ">
                                            <iconify-icon icon="solar:info-circle-bold-duotone" class="fs-14 text-muted"></iconify-icon>
                                        </button>
                                    </div>
                                    <div class="text-muted fs-10 fw-normal mt-1 text-end" style="line-height: 1.35;">
                                        <div>Base: ₹{{ number_format($po->seller_accepted_amount, 2) }}</div>
                                        @if($po->ssi_applicable && $po->ssi_amount > 0)
                                            <div class="text-danger">
                                                - {{ $po->ssi_type ?? 'Hold' }}: ₹{{ number_format($po->ssi_amount, 2) }} ({{ $po->ssi_percentage }}%)
                                            </div>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-end fw-bold text-primary">₹{{ number_format($po->accepted_amount - $po->seller_accepted_amount, 2) }}</td>
                                <td class="text-center">
                                    @if($po->seller_payment_status === 'Paid')
                                        <span class="badge bg-success text-white px-2 py-1 fs-11">Paid</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-11">Unpaid</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    @if($po->payment_verification_status === 'Pending')
                                        <span class="badge bg-warning-subtle text-warning px-2 py-1 fs-11">Pending</span>
                                    @elseif($po->payment_verification_status === 'Verified')
                                        <span class="badge bg-success-subtle text-success px-2 py-1 fs-11">Verified</span>
                                    @elseif($po->payment_verification_status === 'Rejected')
                                        <span class="badge bg-danger-subtle text-danger px-2 py-1 fs-11">Rejected</span>
                                    @endif
                                </td>
                                <td class="pe-3 text-center">
                                    @if($po->seller_payment_status === 'Unpaid' || $po->payment_verification_status === 'Rejected')
                                        @php
                                            $paymentBlocked = false;
                                            $blockReason = '';

                                            if ($payToStore) {
                                                if (!$store || ($store->status !== \App\Enums\StoreStatus::ACTIVE && $store->status !== 'Active')) {
                                                    $paymentBlocked = true;
                                                    $blockReason = 'Pickup Store Inactive';
                                                } elseif (!$seller || $seller->status !== '1') {
                                                    $paymentBlocked = true;
                                                    $blockReason = 'Seller Inactive';
                                                } else {
                                                    $profile = $seller->sellerProfile;
                                                    if ($profile && $profile->parent_id) {
                                                        $parentSeller = \App\Models\User::find($profile->parent_id);
                                                        if (!$parentSeller || $parentSeller->status !== '1') {
                                                            $paymentBlocked = true;
                                                            $blockReason = 'Parent Seller Inactive';
                                                        }
                                                    }
                                                }
                                            } else {
                                                if (!$seller) {
                                                    $paymentBlocked = true;
                                                    $blockReason = 'Seller not found';
                                                } elseif ($seller->status !== '1') {
                                                    $paymentBlocked = true;
                                                    $blockReason = 'Seller Inactive';
                                                } elseif ($seller->kyc_status !== \App\Enums\KycStatus::APPROVED && $seller->kyc_status !== 'APPROVED') {
                                                    $paymentBlocked = true;
                                                    $blockReason = 'Seller KYC Not Approved';
                                                } else {
                                                    $profile = $seller->sellerProfile;
                                                    if ($profile && $profile->parent_id) {
                                                        $parentSeller = \App\Models\User::find($profile->parent_id);
                                                        if (!$parentSeller) {
                                                            $paymentBlocked = true;
                                                            $blockReason = 'Parent Seller not found';
                                                        } elseif ($parentSeller->status !== '1') {
                                                            $paymentBlocked = true;
                                                            $blockReason = 'Parent Seller Inactive';
                                                        } elseif ($parentSeller->kyc_status !== \App\Enums\KycStatus::APPROVED && $parentSeller->kyc_status !== 'APPROVED') {
                                                            $paymentBlocked = true;
                                                            $blockReason = 'Parent Seller KYC Not Approved';
                                                        }
                                                    }
                                                }
                                            }
                                        @endphp

                                        @if($paymentBlocked)
                                            <button class="btn btn-sm btn-secondary py-1 px-2 fw-semibold" disabled
                                                title="{{ $blockReason }}">
                                                <iconify-icon icon="solar:forbidden-circle-bold"
                                                    class="align-middle me-1"></iconify-icon>
                                                Blocked
                                            </button>
                                            <span class="badge bg-danger-subtle text-danger d-block mt-1 fs-10"
                                                style="width: fit-content; margin: 0 auto;">{{ $blockReason }}</span>
                                        @else
                                            <button class="btn btn-sm btn-primary py-1 px-2 fw-semibold" data-bs-toggle="modal"
                                                data-bs-target="#payModal{{ $po->id }}">
                                                <iconify-icon icon="solar:wallet-money-bold" class="align-middle me-1"></iconify-icon>
                                                {{ $po->payment_verification_status === 'Rejected' ? 'Re-upload Payout' : 'Pay Seller' }}
                                            </button>
                                        @endif

                                        <!-- Payout Reference Modal -->
                                        <div class="modal fade" id="payModal{{ $po->id }}" tabindex="-1"
                                            aria-labelledby="payModalLabel{{ $po->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                                                    <form action="{{ route('admin.seller-payments.pay', $po->id) }}"
                                                        method="POST" enctype="multipart/form-data">
                                                        @csrf
                                                        <div class="modal-header border-0 bg-light py-3 px-4" style="border-radius: 16px 16px 0 0;">
                                                            <h5 class="modal-title fw-bold text-slate-800" id="payModalLabel{{ $po->id }}">
                                                                <iconify-icon icon="solar:wallet-money-bold-duotone"
                                                                    class="align-middle me-2 text-primary fs-20"></iconify-icon>
                                                                Record Seller Payout — <span
                                                                    class="text-primary">#{{ $po->po_number }}</span>
                                                            </h5>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                                aria-label="Close"></button>
                                                        </div>
                                                        <div class="modal-body py-4 px-4 text-start">

                                                            @if($po->payment_verification_status === 'Rejected')
                                                                <div class="alert alert-danger py-2 small mb-3">
                                                                    <strong>Previous Payout Rejected:</strong> "{{ $po->rejection_reason }}"
                                                                </div>
                                                            @endif

                                                            {{-- ── RECIPIENT & BANK DETAILS (6-6 DIVIDE) ── --}}
                                                            <div class="row g-3 mb-4">
                                                                <!-- Left: Payment Recipient (col-md-6) -->
                                                                <div class="col-md-6 d-flex">
                                                                    @if($payToStore)
                                                                        <div class="card border-0 bg-success bg-opacity-10 w-100 shadow-sm"
                                                                            style="border-radius: 12px; border: 1px solid rgba(25, 135, 84, 0.15) !important;">
                                                                            <div
                                                                                class="card-body p-3.5 d-flex flex-column justify-content-between">
                                                                                <div>
                                                                                    <div
                                                                                        class="d-flex align-items-center gap-2 mb-3 pb-1 border-bottom border-success border-opacity-15">
                                                                                        <iconify-icon icon="solar:shop-bold-duotone"
                                                                                            class="fs-20 text-success"></iconify-icon>
                                                                                        <h6 class="fw-bold text-slate-800 mb-0"
                                                                                            style="font-family: 'Outfit', sans-serif; font-size: 13.5px;">
                                                                                            Payment Recipient</h6>
                                                                                        <span
                                                                                            class="badge bg-success bg-opacity-15 border border-success border-opacity-20 ms-auto py-0.5 fs-10">Store</span>
                                                                                    </div>
                                                                                    <div class="small text-slate-700">
                                                                                        <div class="mb-2"><strong>Store Name:</strong>
                                                                                            <span
                                                                                                class="fw-semibold text-slate-800">{{ $store->pickup_store_name }}</span>
                                                                                        </div>
                                                                                        <div class="mb-2"><strong>Contact
                                                                                                Person:</strong> <span
                                                                                                class="fw-semibold text-slate-800">{{ $store->contact_person ?? '—' }}</span>
                                                                                        </div>
                                                                                        <div><strong>Mobile / Phone:</strong> <span
                                                                                                class="fw-semibold text-slate-700">{{ $store->mobile ?? '—' }}</span>
                                                                                        </div>
                                                                                    </div>
                                                                                </div>
                                                                                <div
                                                                                    class="mt-3 pt-2.5 border-top border-success border-opacity-10 d-flex gap-1.5 align-items-start">
                                                                                    <iconify-icon icon="solar:info-circle-bold-duotone"
                                                                                        class="fs-15 text-success mt-0.5"></iconify-icon>
                                                                                    <div class="text-success small fw-semibold"
                                                                                        style="font-size: 10.5px; line-height: 1.3;">
                                                                                        Payment must go to the Store bank account.
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @else
                                                                        <div class="card border-0 w-100 shadow-sm"
                                                                            style="border-radius: 12px; border: 1px solid {{ $isSubSeller ? 'rgba(255, 193, 7, 0.2)' : 'rgba(13, 202, 240, 0.2)' }} !important; background-color: {{ $isSubSeller ? 'rgba(255, 193, 7, 0.05)' : 'rgba(13, 202, 240, 0.05)' }};">
                                                                            <div
                                                                                class="card-body p-3.5 d-flex flex-column justify-content-between">
                                                                                <div>
                                                                                    <div class="d-flex align-items-center gap-2 mb-3 pb-1 border-bottom"
                                                                                        style="border-color: {{ $isSubSeller ? 'rgba(255, 193, 7, 0.15)' : 'rgba(13, 202, 240, 0.15)' }} !important;">
                                                                                        <iconify-icon icon="solar:user-bold-duotone"
                                                                                            class="fs-20 {{ $isSubSeller ? 'text-warning' : 'text-info' }}"></iconify-icon>
                                                                                        <h6 class="fw-bold text-slate-800 mb-0"
                                                                                            style="font-family: 'Outfit', sans-serif; font-size: 13.5px;">
                                                                                            Payment Recipient</h6>
                                                                                        <span
                                                                                            class="badge {{ $isSubSeller ? 'bg-warning text-dark border-warning' : 'bg-info bg-opacity-15 text-info border-info' }} border border-opacity-25 ms-auto py-0.5 fs-10">{{ $isSubSeller ? 'Sub-Seller' : 'Main Seller' }}</span>
                                                                                    </div>
                                                                                    <div class="small text-slate-700">
                                                                                        <div class="mb-2"><strong>Seller Name:</strong>
                                                                                            <span
                                                                                                class="fw-semibold text-slate-800">{{ $seller?->name ?? '—' }}</span>
                                                                                        </div>
                                                                                        <div class="mb-2"><strong>Company Name:</strong>
                                                                                            <span
                                                                                                class="fw-semibold text-slate-800">{{ $profile?->company_name ?: ($seller?->name ?? '—') }}</span>
                                                                                        </div>
                                                                                        @if($isSubSeller && $parentSeller)
                                                                                            <div class="mb-2 text-muted"
                                                                                                style="font-size: 11px;"><strong>Parent
                                                                                                    Seller:</strong> <span
                                                                                                    class="fw-semibold">{{ $parentSeller->name }}</span>
                                                                                            </div>
                                                                                        @endif
                                                                                        @if($store)
                                                                                            <div class="text-muted"
                                                                                                style="font-size: 11px;">
                                                                                                <strong>Store:</strong> <span
                                                                                                    class="fw-semibold">{{ $store->pickup_store_name }}</span>
                                                                                                <span
                                                                                                    class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary border-opacity-15 fs-9 py-0.5 px-1.5 align-middle ms-1">Seller-managed</span>
                                                                                            </div>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    @endif
                                                                </div>

                                                                <!-- Right: Recipient Bank Details (col-md-6) -->
                                                                <div class="col-md-6 d-flex">
                                                                    <div class="card border-0 bg-light w-100 shadow-sm"
                                                                        style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.05) !important;">
                                                                        <div class="card-body p-3.5">
                                                                            <div
                                                                                class="d-flex align-items-center gap-2 mb-3 pb-1 border-bottom border-black border-opacity-5">
                                                                                <iconify-icon icon="solar:card-bold-duotone"
                                                                                    class="fs-20 text-primary"></iconify-icon>
                                                                                <h6 class="fw-bold text-slate-800 mb-0"
                                                                                    style="font-family: 'Outfit', sans-serif; font-size: 13.5px;">
                                                                                    Bank Account Details</h6>
                                                                            </div>
                                                                            @if($bankObj && ($bankObj->bank_account_number || $bankObj->ifsc_code))
                                                                                <div class="small text-slate-700">
                                                                                    <div
                                                                                        class="mb-2 text-truncate d-flex align-items-center">
                                                                                        <strong>Holder Name:</strong>
                                                                                        <span
                                                                                            class="fw-semibold text-slate-800 ms-1">{{ $bankObj->account_holder_name ?? '—' }}</span>
                                                                                        @if($bankObj->account_holder_name)
                                                                                            <button type="button"
                                                                                                class="btn btn-link btn-xs p-0 text-muted ms-1.5 d-inline-flex border-0 bg-transparent align-middle"
                                                                                                onclick="copyToClipboard('{{ $bankObj->account_holder_name }}', 'copyHolder{{ $po->id }}', 'Holder Name')"
                                                                                                title="Copy Holder Name">
                                                                                                <iconify-icon icon="solar:copy-bold-duotone"
                                                                                                    id="copyHolder{{ $po->id }}"
                                                                                                    class="fs-12 text-muted"></iconify-icon>
                                                                                            </button>
                                                                                        @endif
                                                                                    </div>
                                                                                    <div class="mb-2 d-flex align-items-center">
                                                                                        <strong>Account Number:</strong>
                                                                                        <code
                                                                                            class="fw-bold text-dark fs-12 bg-white px-2 py-0.5 border rounded ms-1"
                                                                                            style="letter-spacing: 0.5px;">{{ $bankObj->bank_account_number }}</code>
                                                                                        @if($bankObj->bank_account_number)
                                                                                            <button type="button"
                                                                                                class="btn btn-link btn-xs p-0 text-muted ms-1.5 d-inline-flex border-0 bg-transparent align-middle"
                                                                                                onclick="copyToClipboard('{{ $bankObj->bank_account_number }}', 'copyAcc{{ $po->id }}', 'Account Number')"
                                                                                                title="Copy Account Number">
                                                                                                <iconify-icon icon="solar:copy-bold-duotone"
                                                                                                    id="copyAcc{{ $po->id }}"
                                                                                                    class="fs-12 text-muted"></iconify-icon>
                                                                                            </button>
                                                                                        @endif
                                                                                    </div>
                                                                                    <div class="mb-2 d-flex align-items-center">
                                                                                        <strong>IFSC Code:</strong>
                                                                                        <code
                                                                                            class="fw-bold text-primary fs-12 bg-white px-2 py-0.5 border rounded ms-1">{{ $bankObj->ifsc_code }}</code>
                                                                                        @if($bankObj->ifsc_code)
                                                                                            <button type="button"
                                                                                                class="btn btn-link btn-xs p-0 text-muted ms-1.5 d-inline-flex border-0 bg-transparent align-middle"
                                                                                                onclick="copyToClipboard('{{ $bankObj->ifsc_code }}', 'copyIfsc{{ $po->id }}', 'IFSC Code')"
                                                                                                title="Copy IFSC Code">
                                                                                                <iconify-icon icon="solar:copy-bold-duotone"
                                                                                                    id="copyIfsc{{ $po->id }}"
                                                                                                    class="fs-12 text-muted"></iconify-icon>
                                                                                            </button>
                                                                                        @endif
                                                                                    </div>
                                                                                    <div class="mb-2 d-flex align-items-center">
                                                                                        <strong>Bank Name:</strong>
                                                                                        <span class="fw-semibold text-slate-700 ms-1"
                                                                                            style="font-size: 11.5px;">{{ $bankObj->bank_name ?? '—' }}</span>
                                                                                        @if($bankObj->bank_name)
                                                                                            <button type="button"
                                                                                                class="btn btn-link btn-xs p-0 text-muted ms-1.5 d-inline-flex border-0 bg-transparent align-middle"
                                                                                                onclick="copyToClipboard('{{ $bankObj->bank_name }}', 'copyBank{{ $po->id }}', 'Bank Name')"
                                                                                                title="Copy Bank Name">
                                                                                                <iconify-icon icon="solar:copy-bold-duotone"
                                                                                                    id="copyBank{{ $po->id }}"
                                                                                                    class="fs-12 text-muted"></iconify-icon>
                                                                                            </button>
                                                                                        @endif
                                                                                    </div>
                                                                                    <div class="d-flex align-items-center">
                                                                                        <strong>Branch Name:</strong>
                                                                                        <span class="fw-semibold text-slate-700 ms-1"
                                                                                            style="font-size: 11.5px;">{{ $bankObj->branch_name ?? '—' }}</span>
                                                                                        @if($bankObj->branch_name)
                                                                                            <button type="button"
                                                                                                class="btn btn-link btn-xs p-0 text-muted ms-1.5 d-inline-flex border-0 bg-transparent align-middle"
                                                                                                onclick="copyToClipboard('{{ $bankObj->branch_name }}', 'copyBranch{{ $po->id }}', 'Branch Name')"
                                                                                                title="Copy Branch Name">
                                                                                                <iconify-icon icon="solar:copy-bold-duotone"
                                                                                                    id="copyBranch{{ $po->id }}"
                                                                                                    class="fs-12 text-muted"></iconify-icon>
                                                                                            </button>
                                                                                        @endif
                                                                                    </div>
                                                                                </div>
                                                                            @else
                                                                                <div
                                                                                    class="alert alert-warning border-0 rounded-3 py-2 px-3 small mb-0 d-flex align-items-start gap-2 h-100">
                                                                                    <iconify-icon icon="solar:danger-bold-duotone"
                                                                                        class="fs-16 mt-0.5"></iconify-icon>
                                                                                    <div style="font-size: 11px;">
                                                                                        No bank account details found for this
                                                                                        recipient. Please ask them to update bank
                                                                                        details.
                                                                                    </div>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                                                                                        {{-- ── PAYOUT AMOUNT (BEAUTIFUL HIGH-END CARD) ── --}}
                                                            <div class="card border-0 bg-success bg-opacity-10 mb-4 shadow-sm"
                                                                style="border-radius: 12px; border: 1.5px solid rgba(25, 135, 84, 0.18) !important;">
                                                                <div class="card-body py-3 px-4">
                                                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                                                        <div class="d-flex align-items-center gap-3">
                                                                            <div class="bg-success text-white rounded-circle p-2 d-flex align-items-center justify-content-center shadow-sm"
                                                                                style="width: 40px; height: 40px; box-shadow: 0 4px 10px rgba(25, 135, 84, 0.15);">
                                                                                <iconify-icon icon="solar:wallet-money-bold-duotone"
                                                                                    class="fs-22"></iconify-icon>
                                                                            </div>
                                                                            <div>
                                                                                <span class="text-muted small d-block uppercase fw-bold"
                                                                                    style="font-size: 10px; letter-spacing: 0.5px; line-height: 1.2;">NET PAYOUT AMOUNT</span>
                                                                                <span class="text-success fw-bold small"
                                                                                    style="font-size: 12px;">Ready for bank disbursement</span>
                                                                            </div>
                                                                        </div>
                                                                        <h3 class="fw-bold text-success mb-0"
                                                                            style="font-family: 'Outfit', sans-serif; font-size: 24px; letter-spacing: -0.5px;">
                                                                            ₹{{ number_format($po->seller_net_payable, 2) }}
                                                                        </h3>
                                                                    </div>
                                                                    
                                                                    <!-- Payout Breakdown details -->
                                                                    <div class="pt-2.5 border-top border-success border-opacity-20 text-slate-700 fs-12">
                                                                        <div class="d-flex justify-content-between mb-1">
                                                                            <span>DDC Paid Amount:</span>
                                                                            <span class="fw-semibold">₹{{ number_format($po->accepted_amount, 2) }}</span>
                                                                        </div>
                                                                        <div class="d-flex justify-content-between mb-1 text-primary">
                                                                            <span>Platform Margin Deducted:</span>
                                                                            <span class="fw-semibold">- ₹{{ number_format($po->accepted_amount - $po->seller_accepted_amount, 2) }}</span>
                                                                        </div>
                                                                        <div class="d-flex justify-content-between mb-1 py-1 border-top border-bottom border-success border-opacity-10">
                                                                            <strong>Seller Base Amount:</strong>
                                                                            <strong class="text-slate-800">₹{{ number_format($po->seller_accepted_amount, 2) }}</strong>
                                                                        </div>
                                                                        @if($po->ssi_applicable && $po->ssi_amount > 0)
                                                                            <div class="d-flex justify-content-between mb-1 text-danger">
                                                                                <span>{{ $po->ssi_type }} Incentive Hold ({{ $po->ssi_percentage }}%):</span>
                                                                                <span class="fw-semibold">- ₹{{ number_format($po->ssi_amount, 2) }}</span>
                                                                            </div>
                                                                            <div class="text-muted fs-10 mb-1 text-start" style="margin-top: -3px; font-style: italic;">
                                                                                *Held incentives will be released to advisor upon stock-in reception.
                                                                            </div>
                                                                        @endif
                                                                        <div class="d-flex justify-content-between mt-2 pt-2 border-top border-success border-opacity-20 fs-14">
                                                                            <strong class="text-success">Net Payout to Seller:</strong>
                                                                            <strong class="text-success">₹{{ number_format($po->seller_net_payable, 2) }}</strong>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- ── TRANSACTION DETAILS (6-6 ROW LAYOUT) ── --}}
                                                            <div class="row g-3 mb-3">
                                                                {{-- Left: Transaction ID / Reference --}}
                                                                <div class="col-md-6">
                                                                    <label for="ref{{ $po->id }}"
                                                                        class="form-label fw-bold text-slate-700 small mb-1">Transaction
                                                                        Ref / UTR / Payment ID <span
                                                                            class="text-danger">*</span></label>
                                                                    <div class="input-group">
                                                                        <span
                                                                            class="input-group-text bg-light text-muted border-end-0 py-1.5">
                                                                            <iconify-icon icon="solar:bill-list-bold-duotone"
                                                                                class="fs-18"></iconify-icon>
                                                                        </span>
                                                                        <input type="text" name="seller_payment_reference"
                                                                            id="ref{{ $po->id }}" class="form-control ps-1 py-1.5"
                                                                            placeholder="Enter UTR reference"
                                                                            style="border-radius: 0 6px 6px 0;" required>
                                                                    </div>
                                                                </div>

                                                                {{-- Right: Payout Screenshot/Receipt --}}
                                                                <div class="col-md-6">
                                                                    <label for="slip{{ $po->id }}"
                                                                        class="form-label fw-bold text-slate-700 small mb-1">Upload
                                                                        Receipt (PDF/Image) <span
                                                                            class="text-danger">*</span></label>
                                                                    <div class="input-group">
                                                                        <span
                                                                            class="input-group-text bg-light text-muted border-end-0 py-1.5">
                                                                            <iconify-icon icon="solar:cloud-upload-bold-duotone"
                                                                                class="fs-18"></iconify-icon>
                                                                        </span>
                                                                        <input type="file" name="seller_payment_slip"
                                                                            id="slip{{ $po->id }}" class="form-control"
                                                                            accept="image/*,application/pdf"
                                                                            style="border-radius: 0 6px 6px 0;" required>
                                                                    </div>
                                                                    <div class="form-text text-muted mt-1" style="font-size: 11px;">
                                                                        File format: JPG, PNG or PDF. Max file size: 5MB.</div>
                                                                </div>
                                                            </div>

                                                        </div>
                                                        <div
                                                            class="modal-footer border-0 bg-light py-3 px-4 d-flex justify-content-between">
                                                            <button type="button"
                                                                class="btn btn-sm btn-outline-secondary px-3 py-1.5 fw-semibold"
                                                                data-bs-dismiss="modal">Close</button>
                                                            <button type="submit"
                                                                class="btn btn-sm btn-success px-4 py-1.5 fw-bold shadow-sm">
                                                                <iconify-icon icon="solar:check-circle-bold-duotone"
                                                                    class="align-middle me-1 fs-16"></iconify-icon>
                                                                Confirm &amp; Submit Payout
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>

                                    @elseif($po->seller_payment_status === 'Paid' && $po->payment_verification_status === 'Pending')
                                        <div class="text-center">
                                            <span class="text-warning small fw-semibold d-block mb-1">Awaiting Seller
                                                Approval</span>
                                            @if($utr)
                                                <code class="small d-block mb-1">UTR: {{ $utr }}</code>
                                            @endif
                                            <div class="d-flex flex-column gap-1 align-items-center mt-1">
                                                @if($slipUrl)
                                                    <a href="{{ Str::startsWith($slipUrl, ['http://', 'https://']) ? $slipUrl : asset('storage/' . $slipUrl) }}"
                                                        target="_blank" class="btn btn-xs btn-outline-info py-0.5 px-2 fs-11 w-100 max-w-120">
                                                        View Slip
                                                    </a>
                                                @endif
                                                <button type="button" class="btn btn-xs btn-outline-primary py-0.5 px-2 fs-11 w-100 max-w-120 d-inline-flex align-items-center justify-content-center gap-1 mx-auto"
                                                    data-bs-toggle="modal" data-bs-target="#detailsModal{{ $po->id }}">
                                                    <iconify-icon icon="solar:eye-bold-duotone" class="fs-12"></iconify-icon> Payout Info
                                                </button>
                                            </div>
                                        </div>
                                    @else
                                        @if($po->seller_payment_reference)
                                            <div class="text-center">
                                                @if($utr)
                                                    <code class="small d-block mb-1">UTR: {{ $utr }}</code>
                                                @endif
                                                <div class="d-flex flex-column gap-1 align-items-center mt-1">
                                                    @if($slipUrl)
                                                        <a href="{{ Str::startsWith($slipUrl, ['http://', 'https://']) ? $slipUrl : asset('storage/' . $slipUrl) }}"
                                                             target="_blank" class="btn btn-xs btn-outline-success py-1 px-2 fs-11 w-100 max-w-120 d-inline-flex align-items-center justify-content-center gap-1 mx-auto">
                                                            <iconify-icon icon="solar:document-bold" class="align-middle fs-12"></iconify-icon>
                                                            View Slip
                                                        </a>
                                                    @endif
                                                    <button type="button" class="btn btn-xs btn-outline-primary py-1 px-2 fs-11 w-100 max-w-120 d-inline-flex align-items-center justify-content-center gap-1 mx-auto"
                                                        data-bs-toggle="modal" data-bs-target="#detailsModal{{ $po->id }}">
                                                        <iconify-icon icon="solar:eye-bold-duotone" class="fs-12"></iconify-icon> Payout Info
                                                    </button>
                                                </div>
                                            </div>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    @endif

                                    {{-- ── VIEW PAYOUT DETAILS MODAL (FOR HISTORICAL OR PENDING PAYOUTS) ── --}}
                                    @if($po->seller_payment_status === 'Paid')
                                        <div class="modal fade" id="detailsModal{{ $po->id }}" tabindex="-1"
                                            aria-labelledby="detailsModalLabel{{ $po->id }}" aria-hidden="true">
                                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                                <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
                                                    <div class="modal-header border-0 bg-light py-3 px-4" style="border-radius: 16px 16px 0 0;">
                                                        <h5 class="modal-title fw-bold text-slate-800" id="detailsModalLabel{{ $po->id }}">
                                                            <iconify-icon icon="solar:info-circle-bold-duotone"
                                                                class="align-middle me-2 text-primary fs-20"></iconify-icon>
                                                            Payout Record Details — <span
                                                                class="text-primary">#{{ $po->po_number }}</span>
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal"
                                                            aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body py-4 px-4 text-start">
                                                        
                                                                                                                {{-- Payout Amount Header --}}
                                                        <div class="card border-0 bg-success bg-opacity-10 mb-4 shadow-sm"
                                                            style="border-radius: 12px; border: 1.5px solid rgba(25, 135, 84, 0.18) !important;">
                                                            <div class="card-body py-3 px-4">
                                                                <div class="d-flex align-items-center justify-content-between mb-3">
                                                                    <div class="d-flex align-items-center gap-3">
                                                                        <div class="bg-success text-white rounded-circle p-2 d-flex align-items-center justify-content-center shadow-sm"
                                                                            style="width: 40px; height: 40px;">
                                                                            <iconify-icon icon="solar:wallet-money-bold-duotone"
                                                                                class="fs-22"></iconify-icon>
                                                                        </div>
                                                                        <div>
                                                                            <span class="text-muted small d-block uppercase fw-bold"
                                                                                style="font-size: 10px; letter-spacing: 0.5px; line-height: 1.2;">NET PAYOUT AMOUNT</span>
                                                                            <span class="text-success fw-bold small" style="font-size: 12px;">Disbursed Payout</span>
                                                                        </div>
                                                                    </div>
                                                                    <h3 class="fw-bold text-success mb-0"
                                                                        style="font-family: 'Outfit', sans-serif; font-size: 24px;">
                                                                        ₹{{ number_format($po->seller_net_payable, 2) }}
                                                                    </h3>
                                                                </div>
                                                                
                                                                <!-- Payout Breakdown details -->
                                                                <div class="pt-2.5 border-top border-success border-opacity-20 text-slate-700 fs-12">
                                                                    <div class="d-flex justify-content-between mb-1">
                                                                        <span>DDC Paid Amount:</span>
                                                                        <span class="fw-semibold">₹{{ number_format($po->accepted_amount, 2) }}</span>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between mb-1 text-primary">
                                                                        <span>Platform Margin Deducted:</span>
                                                                        <span class="fw-semibold">- ₹{{ number_format($po->accepted_amount - $po->seller_accepted_amount, 2) }}</span>
                                                                    </div>
                                                                    <div class="d-flex justify-content-between mb-1 py-1 border-top border-bottom border-success border-opacity-10">
                                                                        <strong>Seller Base Amount:</strong>
                                                                        <strong class="text-slate-800">₹{{ number_format($po->seller_accepted_amount, 2) }}</strong>
                                                                    </div>
                                                                    @if($po->ssi_applicable && $po->ssi_amount > 0)
                                                                        <div class="d-flex justify-content-between mb-1 text-danger">
                                                                            <span>{{ $po->ssi_type }} Incentive Hold ({{ $po->ssi_percentage }}%):</span>
                                                                            <span class="fw-semibold">- ₹{{ number_format($po->ssi_amount, 2) }}</span>
                                                                        </div>
                                                                        <div class="text-muted fs-10 mb-1 text-start" style="margin-top: -3px; font-style: italic;">
                                                                            *Held incentives are released to advisor upon stock-in reception.
                                                                        </div>
                                                                    @endif
                                                                    <div class="d-flex justify-content-between mt-2 pt-2 border-top border-success border-opacity-20 fs-14">
                                                                        <strong class="text-success">Net Payout to Seller:</strong>
                                                                        <strong class="text-success">₹{{ number_format($po->seller_net_payable, 2) }}</strong>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- Recipient & Bank Details Row --}}
                                                        <div class="row g-3 mb-4">
                                                            <!-- Recipient Info Card -->
                                                            <div class="col-md-6 d-flex">
                                                                @if($payToStore)
                                                                    <div class="card border-0 bg-success bg-opacity-10 w-100 shadow-sm"
                                                                        style="border-radius: 12px; border: 1px solid rgba(25, 135, 84, 0.15) !important;">
                                                                        <div class="card-body p-3.5 d-flex flex-column justify-content-between">
                                                                            <div>
                                                                                <div class="d-flex align-items-center gap-2 mb-3 pb-1 border-bottom border-success border-opacity-15">
                                                                                    <iconify-icon icon="solar:shop-bold-duotone"
                                                                                        class="fs-20 text-success"></iconify-icon>
                                                                                    <h6 class="fw-bold text-slate-800 mb-0" style="font-size: 13.5px;">
                                                                                        Payment Recipient</h6>
                                                                                    <span class="badge bg-success bg-opacity-15 text-success border border-success border-opacity-20 ms-auto py-0.5 fs-10">Store</span>
                                                                                </div>
                                                                                <div class="small text-slate-700">
                                                                                    <div class="mb-2"><strong>Store Name:</strong>
                                                                                        <span class="fw-semibold text-slate-800">{{ $store->pickup_store_name }}</span>
                                                                                    </div>
                                                                                    <div class="mb-2"><strong>Contact Person:</strong>
                                                                                        <span class="fw-semibold text-slate-800">{{ $store->contact_person ?? '—' }}</span>
                                                                                    </div>
                                                                                    <div><strong>Mobile / Phone:</strong>
                                                                                        <span class="fw-semibold text-slate-700">{{ $store->mobile ?? '—' }}</span>
                                                                                    </div>
                                                                                </div>
                                                                            </div>
                                                                            <div class="mt-3 pt-2.5 border-top border-success border-opacity-10 d-flex gap-1.5 align-items-start">
                                                                                <iconify-icon icon="solar:info-circle-bold-duotone"
                                                                                    class="fs-15 text-success mt-0.5"></iconify-icon>
                                                                                <div class="text-success small fw-semibold" style="font-size: 10.5px; line-height: 1.3;">
                                                                                    Payment was routed to the Pickup Store's designated bank account.
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @else
                                                                    <div class="card border-0 w-100 shadow-sm"
                                                                        style="border-radius: 12px; border: 1px solid {{ $isSubSeller ? 'rgba(255, 193, 7, 0.2)' : 'rgba(13, 202, 240, 0.2)' }} !important; background-color: {{ $isSubSeller ? 'rgba(255, 193, 7, 0.05)' : 'rgba(13, 202, 240, 0.05)' }};">
                                                                        <div class="card-body p-3.5 d-flex flex-column justify-content-between">
                                                                            <div>
                                                                                <div class="d-flex align-items-center gap-2 mb-3 pb-1 border-bottom"
                                                                                    style="border-color: {{ $isSubSeller ? 'rgba(255, 193, 7, 0.15)' : 'rgba(13, 202, 240, 0.15)' }} !important;">
                                                                                    <iconify-icon icon="solar:user-bold-duotone"
                                                                                        class="fs-20 {{ $isSubSeller ? 'text-warning' : 'text-info' }}"></iconify-icon>
                                                                                    <h6 class="fw-bold text-slate-800 mb-0" style="font-size: 13.5px;">
                                                                                        Payment Recipient</h6>
                                                                                    <span class="badge {{ $isSubSeller ? 'bg-warning text-dark border-warning' : 'bg-info bg-opacity-15 text-info border-info' }} border border-opacity-25 ms-auto py-0.5 fs-10">{{ $isSubSeller ? 'Sub-Seller' : 'Main Seller' }}</span>
                                                                                </div>
                                                                                <div class="small text-slate-700">
                                                                                    <div class="mb-2"><strong>Seller Name:</strong>
                                                                                        <span class="fw-semibold text-slate-800">{{ $seller?->name ?? '—' }}</span>
                                                                                    </div>
                                                                                    <div class="mb-2"><strong>Company Name:</strong>
                                                                                        <span class="fw-semibold text-slate-800">{{ $profile?->company_name ?: ($seller?->name ?? '—') }}</span>
                                                                                    </div>
                                                                                    @if($isSubSeller && $parentSeller)
                                                                                        <div class="mb-2 text-muted" style="font-size: 11px;"><strong>Parent Seller:</strong>
                                                                                            <span class="fw-semibold">{{ $parentSeller->name }}</span>
                                                                                        </div>
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                @endif
                                                            </div>

                                                            <!-- Bank Account Details Card -->
                                                            <div class="col-md-6 d-flex">
                                                                <div class="card border-0 bg-light w-100 shadow-sm"
                                                                    style="border-radius: 12px; border: 1px solid rgba(0,0,0,0.05) !important;">
                                                                    <div class="card-body p-3.5">
                                                                        <div class="d-flex align-items-center gap-2 mb-3 pb-1 border-bottom border-black border-opacity-5">
                                                                            <iconify-icon icon="solar:card-bold-duotone"
                                                                                class="fs-20 text-primary"></iconify-icon>
                                                                            <h6 class="fw-bold text-slate-800 mb-0" style="font-size: 13.5px;">
                                                                                Bank Account Details</h6>
                                                                        </div>
                                                                        @if($bankObj && ($bankObj->bank_account_number || $bankObj->ifsc_code))
                                                                            <div class="small text-slate-700">
                                                                                <div class="mb-2 text-truncate d-flex align-items-center">
                                                                                    <strong>Holder Name:</strong>
                                                                                    <span class="fw-semibold text-slate-800 ms-1">{{ $bankObj->account_holder_name ?? '—' }}</span>
                                                                                    @if($bankObj->account_holder_name)
                                                                                        <button type="button"
                                                                                            class="btn btn-link btn-xs p-0 text-muted ms-1.5 d-inline-flex border-0 bg-transparent align-middle"
                                                                                            onclick="copyToClipboard('{{ $bankObj->account_holder_name }}', 'infoHolder{{ $po->id }}', 'Holder Name')"
                                                                                            title="Copy Holder Name">
                                                                                            <iconify-icon icon="solar:copy-bold-duotone"
                                                                                                id="infoHolder{{ $po->id }}"
                                                                                                class="fs-12 text-muted"></iconify-icon>
                                                                                        </button>
                                                                                    @endif
                                                                                </div>
                                                                                <div class="mb-2 d-flex align-items-center">
                                                                                    <strong>Account Number:</strong>
                                                                                    <code class="fw-bold text-dark fs-12 bg-white px-2 py-0.5 border rounded ms-1"
                                                                                        style="letter-spacing: 0.5px;">{{ $bankObj->bank_account_number }}</code>
                                                                                    @if($bankObj->bank_account_number)
                                                                                        <button type="button"
                                                                                            class="btn btn-link btn-xs p-0 text-muted ms-1.5 d-inline-flex border-0 bg-transparent align-middle"
                                                                                            onclick="copyToClipboard('{{ $bankObj->bank_account_number }}', 'infoAcc{{ $po->id }}', 'Account Number')"
                                                                                            title="Copy Account Number">
                                                                                            <iconify-icon icon="solar:copy-bold-duotone"
                                                                                                id="infoAcc{{ $po->id }}"
                                                                                                class="fs-12 text-muted"></iconify-icon>
                                                                                        </button>
                                                                                    @endif
                                                                                </div>
                                                                                <div class="mb-2 d-flex align-items-center">
                                                                                    <strong>IFSC Code:</strong>
                                                                                    <code class="fw-bold text-primary fs-12 bg-white px-2 py-0.5 border rounded ms-1">{{ $bankObj->ifsc_code }}</code>
                                                                                    @if($bankObj->ifsc_code)
                                                                                        <button type="button"
                                                                                            class="btn btn-link btn-xs p-0 text-muted ms-1.5 d-inline-flex border-0 bg-transparent align-middle"
                                                                                            onclick="copyToClipboard('{{ $bankObj->ifsc_code }}', 'infoIfsc{{ $po->id }}', 'IFSC Code')"
                                                                                            title="Copy IFSC Code">
                                                                                            <iconify-icon icon="solar:copy-bold-duotone"
                                                                                                id="infoIfsc{{ $po->id }}"
                                                                                                class="fs-12 text-muted"></iconify-icon>
                                                                                        </button>
                                                                                    @endif
                                                                                </div>
                                                                                <div class="mb-2 d-flex align-items-center">
                                                                                    <strong>Bank Name:</strong>
                                                                                    <span class="fw-semibold text-slate-700 ms-1" style="font-size: 11.5px;">{{ $bankObj->bank_name ?? '—' }}</span>
                                                                                </div>
                                                                                <div class="d-flex align-items-center">
                                                                                    <strong>Branch Name:</strong>
                                                                                    <span class="fw-semibold text-slate-700 ms-1" style="font-size: 11.5px;">{{ $bankObj->branch_name ?? '—' }}</span>
                                                                                </div>
                                                                            </div>
                                                                        @else
                                                                            <div class="alert alert-warning border-0 rounded-3 py-2 px-3 small mb-0 d-flex align-items-start gap-2 h-100">
                                                                                <iconify-icon icon="solar:danger-bold-duotone" class="fs-16 mt-0.5"></iconify-icon>
                                                                                <div style="font-size: 11px;">
                                                                                    No bank account details stashed for this transaction.
                                                                                </div>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>



                                                         {{-- Transaction Reference Section --}}
                                                        <div class="border rounded p-3 bg-light">
                                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                                <iconify-icon icon="solar:bill-list-bold-duotone" class="fs-20 text-slate-600"></iconify-icon>
                                                                <span class="fw-bold text-slate-800 fs-14">Transaction Reference</span>
                                                            </div>
                                                            <div class="row g-2">
                                                                <div class="col-md-6">

                                                                    <span class="text-muted small d-block">UTR / Reference ID (Admin to Seller)</span>

                                                                    <code class="fw-bold text-dark fs-13 bg-white px-2 py-0.5 border rounded" style="font-family: monospace; letter-spacing: 0.5px;">{{ $utr ?: '—' }}</code>

                                                                </div>

                                                                @if($po->payment_reference)

                                                                    <div class="col-md-6">

                                                                        <span class="text-muted small d-block">DDC Txn ID (DDC to Admin)</span>

                                                                        <code class="fw-bold text-dark fs-13 bg-white px-2 py-0.5 border rounded" style="font-family: monospace; letter-spacing: 0.5px;">{{ $po->payment_reference }}</code>

                                                                    </div>

                                                                @endif
                                                                @if($slipUrl)
                                                                    <div class="col-md-6">
                                                                        <span class="text-muted small d-block">Uploaded Receipt</span>
                                                                        <a href="{{ Str::startsWith($slipUrl, ['http://', 'https://']) ? $slipUrl : asset('storage/' . $slipUrl) }}"
                                                                            target="_blank" class="btn btn-xs btn-outline-success py-1 px-3 fs-11 mt-1 d-inline-flex align-items-center gap-1">
                                                                            <iconify-icon icon="solar:document-bold" class="fs-12"></iconify-icon>
                                                                            Download Receipt
                                                                        </a>
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>

                                                    </div>
                                                    <div class="modal-footer border-0 bg-light py-3 px-4">
                                                        <button type="button" class="btn btn-sm btn-secondary px-3 py-1.5 fw-semibold" data-bs-dismiss="modal">Close</button>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-4 text-muted">
                                    <iconify-icon icon="solar:wallet-money-bold-duotone"
                                        class="fs-48 mb-2 text-slate-300"></iconify-icon><br>
                                    No payouts found. Only purchase orders paid by DDC are eligible for vendor payouts.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($purchaseOrders->hasPages())
            <div class="card-footer bg-white border-0 py-3 border-top">
                {{ $purchaseOrders->links() }}
            </div>
        @endif
    </div>

    <style>
        .copy-toast {
            position: fixed;
            bottom: 24px;
            right: 24px;
            background-color: #198754;
            color: #fff;
            padding: 10px 18px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(25, 135, 84, 0.25);
            z-index: 99999;
            opacity: 0;
            transform: translateY(15px);
            transition: opacity 0.25s ease, transform 0.25s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .copy-toast.show {
            opacity: 1;
            transform: translateY(0);
        }
    </style>

    <script>
        function copyToClipboard(text, elementId, fieldName) {
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(function () {
                    showSuccessIcon(elementId);
                    showToast(`${fieldName} copied!`);
                });
            } else {
                let textArea = document.createElement("textarea");
                textArea.value = text;
                textArea.style.position = "fixed";
                document.body.appendChild(textArea);
                textArea.focus();
                textArea.select();
                try {
                    document.execCommand('copy');
                    showSuccessIcon(elementId);
                    showToast(`${fieldName} copied!`);
                } catch (err) {
                    console.error('Fallback: Oops, unable to copy', err);
                }
                document.body.removeChild(textArea);
            }
        }

        function showSuccessIcon(elementId) {
            const icon = document.getElementById(elementId);
            if (icon) {
                const originalIcon = icon.getAttribute('icon');
                icon.setAttribute('icon', 'solar:check-circle-bold-duotone');
                icon.classList.remove('text-muted');
                icon.classList.add('text-success');
                setTimeout(function () {
                    icon.setAttribute('icon', originalIcon);
                    icon.classList.remove('text-success');
                    icon.classList.add('text-muted');
                }, 1500);
            }
        }

        function showToast(message) {
            let existingToast = document.querySelector('.copy-toast');
            if (existingToast) {
                existingToast.remove();
            }

            let toast = document.createElement('div');
            toast.className = 'copy-toast';
            toast.innerHTML = `<iconify-icon icon="solar:check-circle-bold" class="fs-16"></iconify-icon> <span>${message}</span>`;
            document.body.appendChild(toast);

            setTimeout(() => {
                toast.classList.add('show');
            }, 50);

            setTimeout(() => {
                toast.classList.remove('show');
                setTimeout(() => {
                    toast.remove();
                }, 250);
            }, 2000);
        }

        // Initialize Bootstrap Popovers
        document.addEventListener('DOMContentLoaded', function () {
            var popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'))
            var popoverList = popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl)
            })
        });
    </script>
@endsection
