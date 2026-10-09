@extends('layouts.admin')

@section('title', ucfirst($type) . ' Wallets')
@section('page-title', ucfirst($type) . ' Wallet Management')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <form action="{{ route('admin.wallets.' . $type) }}" method="GET" class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <h5 class="card-title mb-0">{{ ucfirst($type) }} List & Balances</h5>
                    <div class="d-flex gap-2">
                        <input type="search" name="search" class="form-control form-control-sm" placeholder="Search name/email..." value="{{ request('search') }}" oninput="debounceSearch()">
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0 text-nowrap">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th>Name</th>
                                <th>District</th>
                                <th>Current Balance</th>
                                <th>Daily Charge</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center">
                                        <img src="{{ $user->avatar_url }}" alt="" class="avatar-xs rounded-circle me-2">
                                        <div>
                                            <h6 class="mb-0">{{ $user->name }}</h6>
                                            <p class="text-muted mb-0 small">{{ $user->email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td>{{ $user->district }}</td>
                                <td>
                                    <span class="fw-bold text-{{ $user->wallet_balance < 100 ? 'danger' : 'success' }}">
                                        ₹{{ number_format($user->wallet_balance, 2) }}
                                    </span>
                                </td>
                                <td>₹{{ number_format($user->per_user_daily_charge, 2) }}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#rechargeModal{{ $user->id }}">
                                        <i class="bx bx-plus me-1"></i> Recharge
                                    </button>
                                </td>
                            </tr>

                            <!-- Recharge Modal -->
                            <div class="modal fade" id="rechargeModal{{ $user->id }}" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form action="{{ route('admin.wallets.recharge', ['type' => $type, 'id' => $user->id]) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h5 class="modal-title">Recharge {{ $user->name }}'s Wallet</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label">Amount (₹)</label>
                                                    <input type="number" name="amount" class="form-control" required min="1" step="0.01">
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Description / Remarks</label>
                                                    <input type="text" name="description" class="form-control" placeholder="e.g. Monthly top-up" required>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-light" data-bs-modal="dismiss">Close</button>
                                                <button type="submit" class="btn btn-primary">Add Funds</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                            @empty
                            <tr>
                                <td colspan="5" class="text-center py-4">No users found.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($users->hasPages())
            <div class="card-footer">
                {{ $users->links() }}
            </div>
            @endif
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    let searchTimer;
    function debounceSearch() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(() => {
            document.querySelector('form').submit();
        }, 600);
    }
</script>
@endpush
