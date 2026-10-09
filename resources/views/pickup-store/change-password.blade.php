@extends('layouts.pickup_store')

@section('title', 'Change Password')
@section('page-title', 'Change Password')

@section('content')
<div class="row">
    <div class="col-lg-6 mx-auto">
        <div class="card">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="card-title fw-bold text-slate-800 mb-0">Change Password</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('store.profile.change-password.post') }}" method="POST">
                    @csrf

                    <!-- Current Password -->
                    <div class="mb-3">
                        <label class="form-label required fw-medium text-slate-700">Current Password</label>
                        <input type="password" name="current_password" class="form-control" placeholder="Enter current password" required autocomplete="current-password">
                    </div>

                    <!-- New Password -->
                    <div class="mb-3">
                        <label class="form-label required fw-medium text-slate-700">New Password</label>
                        <input type="password" name="password" class="form-control" placeholder="At least 8 characters" required autocomplete="new-password">
                    </div>

                    <!-- Confirm Password -->
                    <div class="mb-4">
                        <label class="form-label required fw-medium text-slate-700">Confirm New Password</label>
                        <input type="password" name="password_confirmation" class="form-control" placeholder="Re-enter new password" required autocomplete="new-password">
                    </div>

                    <div>
                        <button type="submit" class="btn btn-primary btn-theme">
                            <iconify-icon icon="solar:check-circle-bold-duotone" class="align-middle me-1 fs-18"></iconify-icon>
                            Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
