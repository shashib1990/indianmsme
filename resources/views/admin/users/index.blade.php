@extends('layouts.admin')

@section('title', 'User Management')
@section('page-title', 'User Management')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <form id="filterForm" action="{{ route('admin.users.index') }}" method="GET" class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                    <div class="d-flex gap-2">
                        @foreach(['vendors' => 'Vendors', 'ddcs' => 'DDC Users', 'adcs' => 'ADC Users'] as $key => $label)
                        <button type="submit" name="type" value="{{ $key }}"
                           class="btn btn-sm {{ $type === $key ? 'btn-primary' : 'btn-outline-primary' }}">
                            {{ $label }}
                        </button>
                        @endforeach
                    </div>

                    <div class="d-flex gap-2 align-items-center">
                        <select name="limit" class="form-select form-select-sm w-auto" onchange="document.getElementById('filterForm').submit()">
                            <option value="15" {{ $limit == 15 ? 'selected' : '' }}>15 Entries</option>
                            <option value="50" {{ $limit == 50 ? 'selected' : '' }}>50 Entries</option>
                            <option value="100" {{ $limit == 100 ? 'selected' : '' }}>100 Entries</option>
                        </select>

                        <select name="sort" class="form-select form-select-sm w-auto" onchange="document.getElementById('filterForm').submit()">
                            <option value="created_at" {{ $sort == 'created_at' ? 'selected' : '' }}>Joined Date</option>
                            <option value="name" {{ $sort == 'name' ? 'selected' : '' }}>Name</option>
                            <option value="email" {{ $sort == 'email' ? 'selected' : '' }}>Email</option>
                        </select>

                        <select name="order" class="form-select form-select-sm w-auto" onchange="document.getElementById('filterForm').submit()">
                            <option value="desc" {{ $order == 'desc' ? 'selected' : '' }}>DESC</option>
                            <option value="asc" {{ $order == 'asc' ? 'selected' : '' }}>ASC</option>
                        </select>

                        <input type="text" name="search" class="form-control form-control-sm"
                               placeholder="Live Search..." value="{{ $search }}" style="width:220px"
                               oninput="clearTimeout(this.delay); this.delay = setTimeout(() => this.form.submit(), 600);">
                        @if($search)
                        <a href="{{ route('admin.users.index', ['type' => $type]) }}" class="btn btn-sm btn-outline-secondary">Clear</a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover table-centered mb-0 text-nowrap">
                        <thead class="bg-light bg-opacity-50">
                            <tr>
                                <th>#</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Status</th>
                                <th>Force Change PW</th>
                                <th>Joined</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($users as $user)
                            <tr>
                                <td>{{ $loop->iteration + (($users->currentPage() - 1) * $users->perPage()) }}</td>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <img src="{{ $user->avatar_url }}" alt="avatar" class="rounded-circle" width="34">
                                        <span class="fw-medium">{{ $user->name }}</span>
                                    </div>
                                </td>
                                <td>{{ $user->email }}</td>
                                <td>{{ $user->phone ?? '—' }}</td>
                                <td>
                                    @if($user->is_active === '1')
                                    <span class="badge bg-success-subtle text-success">Active</span>
                                    @else
                                    <span class="badge bg-danger-subtle text-danger">Inactive</span>
                                    @endif
                                </td>
                                <td>
                                    @if($user->force_change_password === '1')
                                    <span class="badge bg-warning-subtle text-warning">Required</span>
                                    @else
                                    <span class="badge bg-light text-muted">No</span>
                                    @endif
                                </td>
                                <td>{{ $user->created_at->format('d M Y') }}</td>
                                <td>
                                    <div class="d-flex gap-1">
                                        <!-- Reset Password Modal Trigger -->
                                        <button class="btn btn-sm btn-outline-warning"
                                                data-bs-toggle="modal"
                                                data-bs-target="#resetPwModal"
                                                data-user-id="{{ $user->id }}"
                                                data-user-name="{{ $user->name }}"
                                                data-user-type="{{ rtrim($type, 's') }}"
                                                title="Reset Password">
                                            <iconify-icon icon="solar:lock-password-bold-duotone"></iconify-icon>
                                        </button>

                                        <!-- Toggle Status -->
                                        <form action="{{ route('admin.users.toggle-status', [rtrim($type, 's'), $user->id]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="btn btn-sm confirm-action {{ $user->is_active === '1' ? 'btn-outline-danger' : 'btn-outline-success' }}"
                                                    data-confirm-message="Are you sure you want to {{ $user->is_active === '1' ? 'deactivate' : 'activate' }} this user?"
                                                    title="{{ $user->is_active === '1' ? 'Deactivate' : 'Activate' }}">
                                                <iconify-icon icon="{{ $user->is_active === '1' ? 'solar:user-block-rounded-bold-duotone' : 'solar:user-check-rounded-bold-duotone' }}"></iconify-icon>
                                            </button>
                                        </form>

                                        <!-- Impersonate -->
                                        <form action="{{ route('admin.impersonate', [rtrim($type, 's'), $user->id]) }}" method="POST" target="_blank">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-info confirm-action"
                                                    data-confirm-message="Login as {{ $user->name }} in a new tab?"
                                                    title="Login as this user (Opens in new tab)">
                                                <iconify-icon icon="solar:user-check-bold-duotone"></iconify-icon>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    <iconify-icon icon="solar:users-group-two-rounded-bold-duotone" class="fs-40 mb-2"></iconify-icon><br>
                                    No users found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($users->hasPages())
                <div class="d-flex justify-content-center my-3">
                    {{ $users->withQueryString()->links() }}
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPwModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reset Password</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="resetPasswordForm" method="POST">
                @csrf
                <div class="modal-body">
                    <p class="text-muted">Resetting password for: <strong id="resetUserName"></strong></p>
                    <p class="alert alert-info border-0 small">
                        <i class="bx bx-info-circle me-1"></i>
                        The user will be forced to change their password on next login.
                    </p>
                    <div class="mb-3">
                        <label class="form-label">New Password <span class="text-danger">*</span></label>
                        <input type="password" name="new_password" class="form-control" placeholder="Minimum 8 characters" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning confirm-action" data-confirm-message="Are you sure you want to reset this password?">
                        <i class="bx bx-key me-1"></i> Reset Password
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const resetModal = document.getElementById('resetPwModal');
    if(resetModal) {
        resetModal.addEventListener('show.bs.modal', function(event) {
            const btn = event.relatedTarget;
            const userId   = btn.getAttribute('data-user-id');
            const userName = btn.getAttribute('data-user-name');
            const userType = btn.getAttribute('data-user-type');

            document.getElementById('resetUserName').textContent = userName;
            document.getElementById('resetPasswordForm').action = `/users/${userType}/${userId}/reset-password`;
        });
    }

    // Keep focus at the end of text in search input if it just reloaded
    const searchInput = document.querySelector('input[name="search"]');
    if(searchInput) {
        let val = searchInput.value;
        searchInput.focus();
        searchInput.value = '';
        searchInput.value = val;
    }
});
</script>
@endpush
